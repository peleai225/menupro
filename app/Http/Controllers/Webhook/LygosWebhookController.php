<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Restaurant;
use App\Notifications\NewOrderNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class LygosWebhookController extends Controller
{
    /**
     * Handle Lygos payment webhook.
     */
    public function handle(Request $request)
    {
        // Verify webhook signature
        $signature = $request->header('X-Lygos-Signature');
        $payload = $request->getContent();
        
        Log::channel('payments')->info('Lygos webhook received', [
            'payload' => $payload,
            'signature' => $signature,
        ]);

        // Parse payload
        $data = $request->all();
        
        // Lygos webhook format: should contain order_id to identify the order/subscription
        // According to documentation, webhooks may contain order_id field
        $orderId = $data['order_id'] ?? $data['orderId'] ?? null;
        $paymentId = $data['payment_id'] ?? $data['id'] ?? $data['gateway_id'] ?? null;
        $metadata = $data['metadata'] ?? [];
        $type = $metadata['type'] ?? 'order';
        
        if (!$orderId && !$paymentId) {
            Log::channel('payments')->error('Lygos webhook: missing order_id or payment_id');
            return response()->json(['error' => 'Missing order_id or payment_id'], 400);
        }

        // Handle subscriptions
        if ($type === 'subscription') {
            // Try to find subscription by order_id (reference) or payment_reference
            $subscription = null;
            if ($orderId) {
                // Extract subscription ID from reference like "SUB-123-20240116"
                if (preg_match('/^SUB-(\d+)-/', $orderId, $matches)) {
                    $subscription = \App\Models\Subscription::find($matches[1]);
                }
            }
            if (!$subscription && $paymentId) {
                $subscription = \App\Models\Subscription::where('payment_reference', $paymentId)->first();
            }
            
            if (!$subscription) {
                Log::channel('payments')->error('Lygos webhook: subscription not found', [
                    'order_id' => $orderId,
                    'payment_id' => $paymentId
                ]);
                return response()->json(['error' => 'Subscription not found'], 404);
            }
            
            return $this->handleSubscriptionWebhook($subscription, $data);
        }

        // Handle orders - Lygos uses order_id to identify orders
        $order = null;
        if ($orderId) {
            $order = Order::where('reference', $orderId)->first();
        }
        if (!$order && $paymentId) {
            $order = Order::where('payment_reference', $paymentId)->first();
        }

        if (!$order) {
            Log::channel('payments')->error('Lygos webhook: order not found', [
                'order_id' => $orderId,
                'payment_id' => $paymentId
            ]);
            return response()->json(['error' => 'Order not found'], 404);
        }

        // Verify signature for this restaurant (if secret is configured)
        $restaurant = $order->restaurant;
        $webhookSecret = $restaurant->getLygosApiSecret();

        if ($webhookSecret && $signature) {
            // Only verify signature if secret is configured
            $expectedSignature = $this->calculateSignature($payload, $webhookSecret);

            if (!hash_equals($expectedSignature, $signature)) {
                Log::channel('payments')->warning('Lygos webhook: invalid signature', [
                    'order_id' => $order->id,
                    'payment_id' => $paymentId,
                ]);
                // Continue anyway in development, but log warning
                if (app()->environment('production')) {
                    return response()->json(['error' => 'Invalid signature'], 401);
                }
            } else {
                Log::channel('payments')->info('Lygos webhook: signature verified', [
                    'order_id' => $order->id,
                ]);
            }
        } else {
            // No secret configured - log but continue (Lygos may not sign webhooks)
            Log::channel('payments')->info('Lygos webhook: no secret configured, skipping signature verification', [
                'order_id' => $order->id,
                'has_signature' => !empty($signature),
            ]);
        }

        // Process webhook event
        $event = $data['event'] ?? 'payment.unknown';
        
        switch ($event) {
            case 'payment.success':
            case 'payment.completed':
                $this->handlePaymentSuccess($order, $data);
                break;

            case 'payment.failed':
                $this->handlePaymentFailed($order, $data);
                break;

            case 'payment.cancelled':
                $this->handlePaymentCancelled($order, $data);
                break;

            case 'payment.refunded':
                $this->handlePaymentRefunded($order, $data);
                break;

            default:
                Log::channel('payments')->info('Lygos webhook: unhandled event', [
                    'event' => $event,
                    'order_id' => $order->id,
                ]);
        }

        return response()->json(['success' => true]);
    }

    /**
     * Handle successful payment.
     */
    protected function handlePaymentSuccess(Order $order, array $data): void
    {
        // Skip if already paid
        if ($order->is_paid) {
            Log::channel('payments')->info('Lygos webhook: order already paid', ['order_id' => $order->id]);
            return;
        }

        $order->markAsPaid([
            'reference' => $data['id'] ?? $data['gateway_id'] ?? $order->payment_reference ?? $order->reference,
            'method' => 'lygos',
            'transaction_id' => $data['transaction_id'] ?? $data['id'] ?? null,
            'metadata' => $data,
        ]);

        // Note: Notification is already sent when order is created (in Checkout component)
        // No need to send again here to avoid duplicates

        Log::channel('payments')->info('Lygos webhook: payment marked as successful', [
            'order_id' => $order->id,
            'total' => $order->total,
        ]);
    }

    /**
     * Handle failed payment.
     */
    protected function handlePaymentFailed(Order $order, array $data): void
    {
        $order->update([
            'payment_metadata' => array_merge(
                $order->payment_metadata ?? [],
                ['failure_reason' => $data['reason'] ?? 'Unknown', 'failure_data' => $data]
            ),
        ]);

        Log::channel('payments')->warning('Lygos webhook: payment failed', [
            'order_id' => $order->id,
            'reason' => $data['reason'] ?? 'Unknown',
        ]);
    }

    /**
     * Handle cancelled payment.
     */
    protected function handlePaymentCancelled(Order $order, array $data): void
    {
        // Don't cancel the order - customer might retry
        $order->update([
            'payment_metadata' => array_merge(
                $order->payment_metadata ?? [],
                ['cancelled_data' => $data]
            ),
        ]);

        Log::channel('payments')->info('Lygos webhook: payment cancelled', ['order_id' => $order->id]);
    }

    /**
     * Handle refunded payment.
     */
    protected function handlePaymentRefunded(Order $order, array $data): void
    {
        $order->markAsRefunded();

        Log::channel('payments')->info('Lygos webhook: payment refunded', ['order_id' => $order->id]);
    }

    /**
     * Handle subscription webhook events.
     */
    protected function handleSubscriptionWebhook(\App\Models\Subscription $subscription, array $data): \Illuminate\Http\JsonResponse
    {
        $event = $data['event'] ?? 'payment.unknown';
        $restaurant = $subscription->restaurant;
        
        // Verify signature (if secret is configured)
        // For subscriptions, use platform (super admin) webhook secret
        $payload = request()->getContent();
        $signature = request()->header('X-Lygos-Signature');
        $webhookSecret = \App\Models\SystemSetting::get('lygos_webhook_secret', '');
        
        if ($webhookSecret && $signature) {
            $expectedSignature = $this->calculateSignature($payload, $webhookSecret);
            
            if (!hash_equals($expectedSignature, $signature)) {
                Log::channel('payments')->warning('Lygos webhook: invalid signature for subscription', [
                    'subscription_id' => $subscription->id,
                ]);
                if (app()->environment('production')) {
                    return response()->json(['error' => 'Invalid signature'], 401);
                }
            } else {
                Log::channel('payments')->info('Lygos webhook: signature verified for subscription', [
                    'subscription_id' => $subscription->id,
                ]);
            }
        } else {
            // No secret configured - log but continue
            Log::channel('payments')->info('Lygos webhook: no secret configured for subscription, skipping signature verification', [
                'subscription_id' => $subscription->id,
                'has_signature' => !empty($signature),
            ]);
        }
        
        switch ($event) {
            case 'payment.success':
            case 'payment.completed':
                $restaurant = $subscription->restaurant;
                if ($subscription->status->value !== 'active') {
                    $subscription->update([
                        'status' => \App\Enums\SubscriptionStatus::ACTIVE,
                        'payment_method' => 'lygos',
                    ]);
                    $restaurant->update([
                        'current_plan_id' => $subscription->plan_id,
                        'subscription_ends_at' => $subscription->ends_at,
                        'orders_blocked' => false,
                    ]);
                    Log::channel('payments')->info('Lygos webhook: subscription activated', [
                        'subscription_id' => $subscription->id,
                    ]);
                }

                // Commission agent Commando si restaurant parrainé (1er paiement ou à chaque paiement si option décochée)
                try {
                    app(\App\Services\CommandoCommissionService::class)
                        ->creditAgentForRestaurantSubscription($restaurant, $subscription);
                } catch (\Throwable $e) {
                    Log::channel('payments')->error('Commando commission error', [
                        'subscription_id' => $subscription->id,
                        'message' => $e->getMessage(),
                    ]);
                }
                break;

            case 'payment.failed':
                $subscription->update([
                    'status' => \App\Enums\SubscriptionStatus::CANCELLED,
                ]);
                break;

            case 'payment.cancelled':
                $subscription->update([
                    'status' => \App\Enums\SubscriptionStatus::CANCELLED,
                ]);
                break;
        }

        return response()->json(['success' => true]);
    }

    /**
     * Calculate webhook signature.
     */
    protected function calculateSignature(string $payload, ?string $secret): string
    {
        if (!$secret) {
            return '';
        }

        return hash_hmac('sha256', $payload, $secret);
    }
}

