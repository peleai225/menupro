<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\Subscription;
use App\Notifications\NewOrderNotification;
use App\Services\WalletService;
use App\Services\WaveGateway;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class WaveWebhookController extends Controller
{
    public function handle(Request $request, WaveGateway $wave, WalletService $walletService)
    {
        $rawPayload = $request->getContent();
        $signatureHeader = $request->header('Wave-Signature', '');

        Log::channel('payments')->info('Wave webhook received', [
            'type' => $request->input('type'),
            'has_signature' => !empty($signatureHeader),
        ]);

        if (!$wave->verifyWebhookSignature($rawPayload, $signatureHeader)) {
            Log::channel('payments')->warning('Wave webhook: invalid signature');
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $payload = $request->all();
        $type = $payload['type'] ?? null;
        $data = $payload['data'] ?? [];

        return match ($type) {
            'checkout.session.completed' => $this->handleCheckoutCompleted($data, $walletService),
            'checkout.session.payment_failed' => $this->handleCheckoutFailed($data),
            default => response()->json(['success' => true]),
        };
    }

    protected function handleCheckoutCompleted(array $data, WalletService $walletService)
    {
        $checkoutId = $data['id'] ?? null;
        $clientReference = $data['client_reference'] ?? null;
        $transactionId = $data['transaction_id'] ?? null;
        $amount = (int) ($data['amount'] ?? 0);

        Log::channel('payments')->info('Wave checkout completed', [
            'checkout_id' => $checkoutId,
            'client_reference' => $clientReference,
            'transaction_id' => $transactionId,
            'amount' => $amount,
        ]);

        $order = $this->findOrder($checkoutId, $clientReference);

        if (!$order) {
            $subscription = $this->findSubscription($checkoutId, $clientReference);
            if ($subscription) {
                return $this->processSubscriptionPayment($subscription, $data);
            }

            Log::channel('payments')->error('Wave webhook: record not found', [
                'checkout_id' => $checkoutId,
                'client_reference' => $clientReference,
            ]);
            return response()->json(['error' => 'Record not found'], 404);
        }

        // Idempotence
        if ($order->is_paid) {
            Log::channel('payments')->info('Wave webhook: order already paid', ['order_id' => $order->id]);
            return response()->json(['success' => true]);
        }

        $order->markAsPaid([
            'reference' => $transactionId ?? $checkoutId,
            'method' => 'wave',
            'transaction_id' => $transactionId,
            'metadata' => $data,
        ]);

        // Créer la PaymentTransaction et créditer le wallet
        $payment = PaymentTransaction::create([
            'order_id' => $order->id,
            'restaurant_id' => $order->restaurant_id,
            'gateway' => 'wave',
            'gateway_transaction_id' => $transactionId,
            'wave_checkout_id' => $checkoutId,
            'wave_payment_id' => $transactionId,
            'amount' => $order->total,
            'currency' => 'XOF',
            'status' => 'completed',
            'client_reference' => $clientReference,
            'metadata' => $data,
        ]);

        $walletService->creditWallet($order->restaurant_id, $payment->id);

        // Notifier le restaurant
        $order->restaurant->users()
            ->whereIn('role', [\App\Enums\UserRole::RESTAURANT_ADMIN, \App\Enums\UserRole::EMPLOYEE])
            ->each(fn ($user) => $user->notify(new NewOrderNotification($order)));

        Log::channel('payments')->info('Wave webhook: order payment confirmed + wallet credited', [
            'order_id' => $order->id,
            'amount' => $order->total,
        ]);

        return response()->json(['success' => true]);
    }

    protected function handleCheckoutFailed(array $data)
    {
        $checkoutId = $data['id'] ?? null;
        $clientReference = $data['client_reference'] ?? null;

        $order = $this->findOrder($checkoutId, $clientReference);

        if ($order && !$order->is_paid) {
            $order->update([
                'payment_metadata' => array_merge(
                    $order->payment_metadata ?? [],
                    ['wave_failure' => $data]
                ),
            ]);
        }

        Log::channel('payments')->warning('Wave checkout payment failed', [
            'checkout_id' => $checkoutId,
            'order_id' => $order?->id,
        ]);

        return response()->json(['success' => true]);
    }

    protected function processSubscriptionPayment(Subscription $subscription, array $data)
    {
        if ($subscription->status->value === 'active') {
            return response()->json(['success' => true]);
        }

        $subscription->update([
            'status' => \App\Enums\SubscriptionStatus::ACTIVE,
            'payment_method' => 'wave',
        ]);

        $restaurant = $subscription->restaurant;
        $restaurant->update([
            'current_plan_id' => $subscription->plan_id,
            'subscription_ends_at' => $subscription->ends_at,
            'orders_blocked' => false,
        ]);

        try {
            app(\App\Services\CommandoCommissionService::class)
                ->creditAgentForRestaurantSubscription($restaurant, $subscription);
        } catch (\Throwable $e) {
            Log::channel('payments')->error('Wave webhook: commando commission error', [
                'subscription_id' => $subscription->id,
                'message' => $e->getMessage(),
            ]);
        }

        Log::channel('payments')->info('Wave webhook: subscription activated', [
            'subscription_id' => $subscription->id,
        ]);

        return response()->json(['success' => true]);
    }

    protected function findOrder(?string $checkoutId, ?string $clientReference): ?Order
    {
        if ($checkoutId) {
            $order = Order::where('payment_reference', $checkoutId)->first();
            if ($order) return $order;
        }

        if ($clientReference && preg_match('/^ORDER-(\d+)-/', $clientReference, $matches)) {
            return Order::find($matches[1]);
        }

        return null;
    }

    protected function findSubscription(?string $checkoutId, ?string $clientReference): ?Subscription
    {
        if ($checkoutId) {
            $sub = Subscription::where('payment_reference', $checkoutId)->first();
            if ($sub) return $sub;
        }

        if ($clientReference && preg_match('/^SUB-(\d+)-/', $clientReference, $matches)) {
            return Subscription::find($matches[1]);
        }

        return null;
    }
}
