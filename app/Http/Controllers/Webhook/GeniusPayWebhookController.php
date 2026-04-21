<?php

namespace App\Http\Controllers\Webhook;

use App\Enums\SubscriptionStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GeniusPayWebhookController extends Controller
{
    /**
     * Handle GeniusPay payment webhook.
     * Documentation: https://pay.genius.ci/docs/api
     */
    public function handle(Request $request)
    {
        $payload = $request->getContent();
        $signature = $request->header('X-Webhook-Signature');
        $timestamp = $request->header('X-Webhook-Timestamp');
        $event = $request->header('X-Webhook-Event');

        Log::channel('payments')->info('GeniusPay webhook received', [
            'event' => $event,
            'signature' => $signature ? '***' : null,
        ]);

        $data = $request->all();
        $webhookData = $data['data'] ?? $data;
        $metadata = $webhookData['metadata'] ?? [];
        $type = $metadata['type'] ?? null;

        // Determine webhook secret: restaurant (orders) or platform (subscriptions)
        $webhookSecret = '';
        if ($type === 'order' && !empty($metadata['restaurant_id'])) {
            $restaurant = \App\Models\Restaurant::find($metadata['restaurant_id']);
            $webhookSecret = $restaurant?->getGeniusPayWebhookSecret() ?? '';
        }
        if (empty($webhookSecret)) {
            $webhookSecret = \App\Models\SystemSetting::get('geniuspay_webhook_secret', '')
                ?: config('services.geniuspay.webhook_secret', '');
        }

        if ($webhookSecret && $signature && $timestamp) {
            $expectedSignature = hash_hmac('sha256', $timestamp . '.' . $payload, $webhookSecret);
            if (!hash_equals($expectedSignature, $signature)) {
                Log::channel('payments')->warning('GeniusPay webhook: invalid signature');
                return response()->json(['error' => 'Invalid signature'], 401);
            }
            if (abs(time() - (int) $timestamp) > 300) {
                Log::channel('payments')->warning('GeniusPay webhook: timestamp too old');
                return response()->json(['error' => 'Timestamp too old'], 400);
            }
        } elseif ($webhookSecret && app()->environment('production')) {
            Log::channel('payments')->warning('GeniusPay webhook: missing signature in production');
            return response()->json(['error' => 'Missing signature'], 401);
        }

        // Handle ORDER payments (commandes clients)
        if ($type === 'order') {
            $order = null;
            if (!empty($metadata['order_id'])) {
                $order = Order::find($metadata['order_id']);
            }
            if (!$order && !empty($webhookData['reference'])) {
                $order = Order::where('payment_reference', $webhookData['reference'])->first();
            }

            if ($order) {
                switch ($event) {
                    case 'payment.success':
                        return $this->handleOrderPaymentSuccess($order, $webhookData);
                    case 'payment.failed':
                    case 'payment.cancelled':
                        Log::channel('payments')->info('GeniusPay webhook: order payment failed/cancelled', ['order_id' => $order->id]);
                        break;
                }
                return response()->json(['success' => true]);
            }
        }

        // Handle SUBSCRIPTION payments
        $subscription = null;
        if (!empty($metadata['subscription_id'])) {
            $subscription = Subscription::find($metadata['subscription_id']);
        }
        if (!$subscription && !empty($webhookData['reference'])) {
            $subscription = Subscription::where('payment_reference', $webhookData['reference'])->first();
        }
        if (!$subscription && !empty($metadata['order_id']) && preg_match('/^SUB-(\d+)-/', $metadata['order_id'], $m)) {
            $subscription = Subscription::find($m[1]);
        }

        if (!$subscription) {
            Log::channel('payments')->error('GeniusPay webhook: subscription/order not found', [
                'metadata' => $metadata,
                'reference' => $webhookData['reference'] ?? null,
            ]);
            return response()->json(['error' => 'Subscription/order not found'], 404);
        }

        switch ($event) {
            case 'payment.success':
                return $this->handlePaymentSuccess($subscription, $webhookData);
            case 'payment.failed':
                return $this->handlePaymentFailed($subscription);
            case 'payment.cancelled':
                return $this->handlePaymentCancelled($subscription);
            case 'payment.refunded':
                return $this->handlePaymentRefunded($subscription);
            case 'payment.expired':
                Log::channel('payments')->info('GeniusPay webhook: payment expired', ['subscription_id' => $subscription->id]);
                break;
            default:
                Log::channel('payments')->info('GeniusPay webhook: unhandled event', ['event' => $event]);
        }

        return response()->json(['success' => true]);
    }

    protected function handlePaymentSuccess(Subscription $subscription, array $data): \Illuminate\Http\JsonResponse
    {
        if ($subscription->status === SubscriptionStatus::ACTIVE) {
            Log::channel('payments')->info('GeniusPay webhook: subscription already active', ['subscription_id' => $subscription->id]);
            return response()->json(['success' => true]);
        }

        try {
            $restaurant = $subscription->restaurant;

            $subscription->update([
                'status' => SubscriptionStatus::ACTIVE,
                'payment_method' => 'geniuspay',
                'payment_reference' => $data['reference'] ?? $subscription->payment_reference,
            ]);

            $restaurant->update([
                'current_plan_id' => $subscription->plan_id,
                'subscription_ends_at' => $subscription->ends_at,
                'orders_blocked' => false,
            ]);

            // Commission agent Commando si restaurant parrainé
            try {
                app(\App\Services\CommandoCommissionService::class)
                    ->creditAgentForRestaurantSubscription($restaurant, $subscription);
            } catch (\Throwable $e) {
                Log::channel('payments')->error('Commando commission error', [
                    'subscription_id' => $subscription->id,
                    'message' => $e->getMessage(),
                ]);
            }

            Log::channel('payments')->info('GeniusPay webhook: subscription activated', [
                'subscription_id' => $subscription->id,
            ]);
        } catch (\Exception $e) {
            Log::channel('payments')->error('GeniusPay webhook: activation error', [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage(),
            ]);
            return response()->json(['error' => 'Activation failed'], 500);
        }

        return response()->json(['success' => true]);
    }

    protected function handlePaymentFailed(Subscription $subscription): \Illuminate\Http\JsonResponse
    {
        $subscription->update(['status' => SubscriptionStatus::CANCELLED]);
        Log::channel('payments')->info('GeniusPay webhook: subscription cancelled (failed)', ['subscription_id' => $subscription->id]);
        return response()->json(['success' => true]);
    }

    protected function handlePaymentCancelled(Subscription $subscription): \Illuminate\Http\JsonResponse
    {
        $subscription->update(['status' => SubscriptionStatus::CANCELLED]);
        Log::channel('payments')->info('GeniusPay webhook: subscription cancelled', ['subscription_id' => $subscription->id]);
        return response()->json(['success' => true]);
    }

    protected function handleOrderPaymentSuccess(Order $order, array $data): \Illuminate\Http\JsonResponse
    {
        if ($order->is_paid) {
            Log::channel('payments')->info('GeniusPay webhook: order already paid', ['order_id' => $order->id]);
            return response()->json(['success' => true]);
        }

        $order->markAsPaid([
            'reference' => $data['reference'] ?? $order->payment_reference,
            'method' => 'geniuspay',
            'transaction_id' => $data['id'] ?? null,
            'metadata' => $data,
        ]);

        $restaurant = $order->restaurant;
        $restaurant->users()
            ->whereIn('role', [\App\Enums\UserRole::RESTAURANT_ADMIN, \App\Enums\UserRole::EMPLOYEE])
            ->each(fn ($user) => $user->notify(new \App\Notifications\NewOrderNotification($order)));

        Log::channel('payments')->info('GeniusPay webhook: order payment confirmed', ['order_id' => $order->id]);
        return response()->json(['success' => true]);
    }

    protected function handlePaymentRefunded(Subscription $subscription): \Illuminate\Http\JsonResponse
    {
        // Optionnel: gérer le remboursement (annuler l'abonnement, etc.)
        Log::channel('payments')->info('GeniusPay webhook: payment refunded', ['subscription_id' => $subscription->id]);
        return response()->json(['success' => true]);
    }
}
