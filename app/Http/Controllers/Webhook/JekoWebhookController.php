<?php

namespace App\Http\Controllers\Webhook;

use App\Enums\PaymentStatus;
use App\Enums\SubscriptionStatus;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessJekoPayoutJob;
use App\Models\Order;
use App\Models\Subscription;
use App\Services\JekoGateway;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class JekoWebhookController extends Controller
{
    public function handle(Request $request): JsonResponse
    {
        $rawPayload = $request->getContent();
        // Header Jeko: "Jeko-Signature" (Laravel normalise en HTTP_JEKO_SIGNATURE)
        $signature  = $request->header('Jeko-Signature', '');

        $valid = app(JekoGateway::class)->forPlatform()->verifyWebhookSignature($rawPayload, $signature);

        if (!$valid) {
            Log::channel('payments')->warning('Jeko webhook: invalid signature');
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $payload = json_decode($rawPayload, true) ?? [];
        $event   = $payload['event'] ?? '';

        // Jeko n'a qu'un seul event: transaction.completed
        if ($event !== 'transaction.completed') {
            return response()->json(['status' => 'ignored'], 200);
        }

        $data            = $payload['data'] ?? [];
        $status          = $data['status'] ?? '';
        $transactionType = $data['transactionType'] ?? '';

        if ($transactionType === 'payment') {
            if ($status === 'success') {
                $this->handlePaymentSuccess($data);
            } else {
                $this->handlePaymentFailed($data);
            }
        } elseif ($transactionType === 'transfer') {
            $this->handleTransferUpdate($data);
        }

        return response()->json(['status' => 'ok'], 200);
    }

    protected function handlePaymentSuccess(array $data): void
    {
        $transactionId = $data['id'] ?? null;
        $details       = $data['transactionDetails'] ?? [];
        $reference     = $details['reference'] ?? null;
        $orderToPayout = null;

        DB::transaction(function () use ($transactionId, $reference, &$orderToPayout) {
            // ─── Order ────────────────────────────────────────────────────────
            $order = $this->findOrder($reference);

            if ($order) {
                $order = Order::withoutGlobalScope('restaurant')
                    ->where('id', $order->id)
                    ->lockForUpdate()
                    ->first();

                if ($order && !$order->is_paid) {
                    $order->markAsPaid([
                        'method'    => 'jeko',
                        'reference' => $transactionId,
                        'metadata'  => ['jeko_transaction_id' => $transactionId],
                    ]);
                    $orderToPayout = $order;
                }
            }

            // ─── Subscription ─────────────────────────────────────────────────
            $subscription = $this->findSubscription($reference);

            if ($subscription) {
                $subscription = Subscription::withoutGlobalScope('restaurant')
                    ->where('id', $subscription->id)
                    ->lockForUpdate()
                    ->first();

                if ($subscription && $subscription->status !== SubscriptionStatus::ACTIVE) {
                    $subscription->convertToPaid([
                        'method'    => 'jeko',
                        'reference' => $transactionId,
                    ]);

                    $restaurant = $subscription->restaurant;
                    if ($restaurant) {
                        $restaurant->update([
                            'current_plan_id'      => $subscription->plan_id,
                            'subscription_ends_at' => $subscription->ends_at,
                            'orders_blocked'       => false,
                        ]);
                    }
                }
            }
        });

        if ($orderToPayout) {
            ProcessJekoPayoutJob::dispatch($orderToPayout);
        }

        Log::channel('payments')->info('Jeko webhook transaction.completed (payment)', [
            'transaction_id' => $transactionId,
            'reference'      => $reference,
        ]);
    }

    protected function handleTransferUpdate(array $data): void
    {
        // Les virements sont traités par polling dans ProcessJekoPayoutJob.
        // On logue simplement pour traçabilité.
        Log::channel('payments')->info('Jeko webhook transaction.completed (transfer)', [
            'transaction_id' => $data['id'] ?? null,
            'status'         => $data['status'] ?? null,
            'reference'      => $data['transactionDetails']['reference'] ?? null,
        ]);
    }

    protected function handlePaymentFailed(array $data): void
    {
        $reference = $data['transactionDetails']['reference'] ?? null;

        if (!$reference) {
            Log::channel('payments')->warning('Jeko webhook payment failed: no reference in payload');
            return;
        }

        DB::transaction(function () use ($reference) {
            $order = $this->findOrder($reference);
            if (!$order) {
                return;
            }

            $order = Order::withoutGlobalScope('restaurant')
                ->where('id', $order->id)
                ->lockForUpdate()
                ->first();

            if ($order && $order->payment_status === PaymentStatus::PENDING) {
                $order->update(['payment_status' => PaymentStatus::FAILED]);
            }
        });

        Log::channel('payments')->warning('Jeko webhook payment failed', ['reference' => $reference]);
    }

    protected function findOrder(?string $reference): ?Order
    {
        if (!$reference) {
            return null;
        }

        // Format: ORDER-{id}-{reference}
        if (preg_match('/^ORDER-(\d+)-/', $reference, $matches)) {
            return Order::withoutGlobalScope('restaurant')->find($matches[1]);
        }

        return Order::withoutGlobalScope('restaurant')
            ->where('payment_reference', $reference)
            ->first();
    }

    protected function findSubscription(?string $reference): ?Subscription
    {
        if (!$reference) {
            return null;
        }

        // Format: SUB-{id}-{timestamp}
        if (preg_match('/^SUB-(\d+)-/', $reference, $matches)) {
            return Subscription::withoutGlobalScope('restaurant')->find($matches[1]);
        }

        return Subscription::withoutGlobalScope('restaurant')
            ->where('payment_reference', $reference)
            ->first();
    }
}
