<?php

namespace App\Http\Controllers\Webhooks;

use App\Enums\PaymentStatus;
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
        $signature = $request->header('X-Jeko-Signature', '');

        $valid = app(JekoGateway::class)->forPlatform()->verifyWebhookSignature($rawPayload, $signature);

        if (!$valid) {
            Log::channel('payments')->warning('Jeko webhook: invalid signature');
            return response()->json(['error' => 'Invalid signature'], 401);
        }

        $payload = json_decode($rawPayload, true) ?? [];
        $event = $payload['event'] ?? '';

        switch ($event) {
            case 'payment.success':
                $this->handlePaymentSuccess($payload);
                break;
            case 'payment.failed':
                $this->handlePaymentFailed($payload);
                break;
            default:
                return response()->json(['status' => 'ignored'], 200);
        }

        return response()->json(['status' => 'ok'], 200);
    }

    protected function handlePaymentSuccess(array $payload): void
    {
        $paymentId = $payload['payment_id'] ?? $payload['id'] ?? null;
        $clientReference = $payload['reference_client'] ?? $payload['client_reference'] ?? '';

        DB::transaction(function () use ($paymentId, $clientReference) {
            // ─── Order ────────────────────────────────────────────────────────
            $order = $this->findOrder($paymentId, $clientReference);

            if ($order) {
                $order = Order::withoutGlobalScope('restaurant')
                    ->where('id', $order->id)
                    ->lockForUpdate()
                    ->first();

                if ($order && !$order->is_paid) {
                    $order->markAsPaid([
                        'method'    => 'jeko',
                        'reference' => $paymentId,
                        'metadata'  => ['jeko_payment_id' => $paymentId],
                    ]);

                    ProcessJekoPayoutJob::dispatch($order);
                }
            }

            // ─── Subscription ─────────────────────────────────────────────────
            $subscription = $this->findSubscription($paymentId, $clientReference);

            if ($subscription) {
                $subscription = Subscription::withoutGlobalScope('restaurant')
                    ->where('id', $subscription->id)
                    ->lockForUpdate()
                    ->first();

                if (
                    $subscription
                    && $subscription->status !== \App\Enums\SubscriptionStatus::ACTIVE
                    && $subscription->status !== \App\Enums\SubscriptionStatus::TRIAL
                ) {
                    $subscription->convertToPaid([
                        'method'    => 'jeko',
                        'reference' => $paymentId,
                    ]);
                }
            }
        });

        Log::channel('payments')->info('Jeko webhook payment.success', [
            'payment_id' => $paymentId,
            'reference'  => $clientReference,
        ]);
    }

    protected function handlePaymentFailed(array $payload): void
    {
        $paymentId = $payload['payment_id'] ?? $payload['id'] ?? null;
        $clientReference = $payload['reference_client'] ?? $payload['client_reference'] ?? '';

        $order = $this->findOrder($paymentId, $clientReference);

        if ($order && $order->payment_status === PaymentStatus::PENDING) {
            Order::withoutGlobalScope('restaurant')
                ->where('id', $order->id)
                ->update(['payment_status' => PaymentStatus::FAILED]);
        }

        Log::channel('payments')->warning('Jeko webhook payment.failed', [
            'payment_id' => $paymentId,
            'reference'  => $clientReference,
        ]);
    }

    protected function findOrder(?string $paymentId, string $clientReference): ?Order
    {
        if ($paymentId) {
            $order = Order::withoutGlobalScope('restaurant')
                ->where('payment_reference', $paymentId)
                ->first();

            if ($order) {
                return $order;
            }
        }

        if ($clientReference && preg_match('/ORDER-(\d+)/', $clientReference, $matches)) {
            return Order::withoutGlobalScope('restaurant')->find($matches[1]);
        }

        return null;
    }

    protected function findSubscription(?string $paymentId, string $clientReference): ?Subscription
    {
        if ($paymentId) {
            $sub = Subscription::withoutGlobalScope('restaurant')
                ->where('payment_reference', $paymentId)
                ->first();

            if ($sub) {
                return $sub;
            }
        }

        if ($clientReference && preg_match('/SUB-(\d+)/', $clientReference, $matches)) {
            return Subscription::withoutGlobalScope('restaurant')->find($matches[1]);
        }

        return null;
    }
}
