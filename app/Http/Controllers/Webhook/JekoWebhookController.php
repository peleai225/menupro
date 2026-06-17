<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Subscription;
use App\Models\SystemSetting;
use App\Services\JekoGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class JekoWebhookController extends Controller
{
    /**
     * Handle Jeko webhook.
     *
     * Payload structure:
     * {
     *   "event": "transaction.completed",
     *   "data": {
     *     "id": "txn_...",
     *     "amount": { "amount": 10000, "currency": "XOF" },
     *     "fees": { "amount": 100, "currency": "XOF" },
     *     "status": "success" | "error",
     *     "transactionType": "payment" | "transfer",
     *     "paymentMethod": "wave" | "orange" | "mtn" | "moov" | "djamo" | "bank",
     *     "transactionDetails": {
     *       "id": "...",
     *       "reference": "...",
     *       "paymentLinkId": "..."
     *     }
     *   },
     *   "timestamp": "2024-01-15T14:30:25.000Z"
     * }
     */
    public function handle(Request $request)
    {
        $rawPayload = $request->getContent();
        $signature = $request->header('Jeko-Signature');

        Log::channel('payments')->info('Jeko webhook received', [
            'event' => $request->input('event'),
            'has_signature' => !empty($signature),
        ]);

        $data = $request->all();
        $event = $data['event'] ?? null;
        $txnId = $data['data']['id'] ?? null;

        if ($event !== 'transaction.completed') {
            Log::channel('payments')->info('Jeko webhook: unhandled event', ['event' => $event]);
            return response()->json(['success' => true]);
        }

        $txData = $data['data'] ?? [];
        $status = $txData['status'] ?? null;
        $transactionType = $txData['transactionType'] ?? 'payment';
        $paymentLinkId = $txData['transactionDetails']['paymentLinkId'] ?? null;
        $reference = $txData['transactionDetails']['reference'] ?? null;

        // Identify the record from paymentLinkId (stored as payment_reference) or reference
        if ($transactionType === 'payment') {
            return $this->handlePaymentEvent($rawPayload, $signature, $txData, $status, $paymentLinkId, $reference, $txnId);
        }

        if ($transactionType === 'transfer') {
            // Transfers are platform-level payouts; log only
            Log::channel('payments')->info('Jeko webhook: transfer event received', [
                'status' => $status,
                'reference' => $reference,
            ]);
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => true]);
    }

    protected function handlePaymentEvent(
        string $rawPayload,
        ?string $signature,
        array $txData,
        ?string $status,
        ?string $paymentLinkId,
        ?string $reference,
        ?string $txnId
    ) {
        // Try to find order by payment_reference (paymentLinkId) or reference
        $order = null;
        if ($paymentLinkId) {
            $order = Order::where('payment_reference', $paymentLinkId)->first();
        }
        if (!$order && $reference) {
            $order = Order::where('reference', $reference)->first();
        }

        // Try subscription
        $subscription = null;
        if (!$order) {
            if ($paymentLinkId) {
                $subscription = Subscription::where('payment_reference', $paymentLinkId)->first();
            }
            if (!$subscription && $reference && str_starts_with($reference, 'SUB-')) {
                if (preg_match('/^SUB-(\d+)-/', $reference, $matches)) {
                    $subscription = Subscription::find($matches[1]);
                }
            }
        }

        if (!$order && !$subscription) {
            Log::channel('payments')->error('Jeko webhook: record not found', [
                'payment_link_id' => $paymentLinkId,
                'reference' => $reference,
            ]);
            return response()->json(['error' => 'Record not found'], 404);
        }

        // Verify webhook signature
        if ($order) {
            $webhookSecret = $order->restaurant->getJekoWebhookSecret();
        } else {
            $webhookSecret = SystemSetting::get('jeko_webhook_secret', '');
        }

        if (!$webhookSecret || !$signature) {
            Log::channel('payments')->warning('Jeko webhook: missing secret or signature', [
                'order_id' => $order?->id,
                'subscription_id' => $subscription?->id,
            ]);
            return response()->json(['error' => 'Missing signature'], 401);
        }

        $expected = hash_hmac('sha256', $rawPayload, $webhookSecret);
        if (!hash_equals($expected, $signature)) {
            Log::channel('payments')->warning('Jeko webhook: invalid signature', [
                'order_id' => $order?->id,
                'subscription_id' => $subscription?->id,
            ]);
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        if ($order) {
            return $this->processOrderPayment($order, $status, $txData, $txnId);
        }

        return $this->processSubscriptionPayment($subscription, $status, $txData);
    }

    protected function processOrderPayment(Order $order, ?string $status, array $txData, ?string $txnId)
    {
        if ($status === 'success') {
            // Idempotence : déjà traité si payé ou si même transaction_id déjà enregistré
            if ($order->is_paid) {
                Log::channel('payments')->info('Jeko webhook: order already paid (idempotent)', ['order_id' => $order->id]);
                return response()->json(['success' => true]);
            }
            $existingTxnId = $order->payment_metadata['transaction_id'] ?? null;
            if ($txnId && $existingTxnId && $existingTxnId === $txnId) {
                Log::channel('payments')->info('Jeko webhook: duplicate transaction_id, skipping', ['txn_id' => $txnId]);
                return response()->json(['success' => true]);
            }

            $order->markAsPaid([
                'reference' => $txnId ?? $order->payment_reference ?? $order->reference,
                'method' => 'jeko',
                'transaction_id' => $txnId,
                'metadata' => $txData,
            ]);

            Log::channel('payments')->info('Jeko webhook: order payment confirmed', [
                'order_id' => $order->id,
                'total' => $order->total,
                'payment_method' => $txData['paymentMethod'] ?? 'unknown',
            ]);
        } elseif ($status === 'error') {
            $order->update([
                'payment_metadata' => array_merge(
                    $order->payment_metadata ?? [],
                    ['jeko_failure' => $txData]
                ),
            ]);

            Log::channel('payments')->warning('Jeko webhook: order payment failed', [
                'order_id' => $order->id,
            ]);
        }

        return response()->json(['success' => true]);
    }

    protected function processSubscriptionPayment(Subscription $subscription, ?string $status, array $txData)
    {
        $restaurant = $subscription->restaurant;

        if ($status === 'success') {
            // Idempotence : déjà activé
            if ($subscription->status->value === 'active') {
                Log::channel('payments')->info('Jeko webhook: subscription already active (idempotent)', ['subscription_id' => $subscription->id]);
                return response()->json(['success' => true]);
            }
            if ($subscription->status->value !== 'active') {
                $subscription->update([
                    'status' => \App\Enums\SubscriptionStatus::ACTIVE,
                    'payment_method' => 'jeko',
                ]);
                $restaurant->update([
                    'current_plan_id' => $subscription->plan_id,
                    'subscription_ends_at' => $subscription->ends_at,
                    'orders_blocked' => false,
                ]);

                try {
                    app(\App\Services\CommandoCommissionService::class)
                        ->creditAgentForRestaurantSubscription($restaurant, $subscription);
                } catch (\Throwable $e) {
                    Log::channel('payments')->error('Jeko webhook: commando commission error', [
                        'subscription_id' => $subscription->id,
                        'message' => $e->getMessage(),
                    ]);
                }

                Log::channel('payments')->info('Jeko webhook: subscription activated', [
                    'subscription_id' => $subscription->id,
                ]);
            }
        } elseif ($status === 'error') {
            $subscription->update([
                'status' => \App\Enums\SubscriptionStatus::CANCELLED,
            ]);

            Log::channel('payments')->warning('Jeko webhook: subscription payment failed', [
                'subscription_id' => $subscription->id,
            ]);
        }

        return response()->json(['success' => true]);
    }
}
