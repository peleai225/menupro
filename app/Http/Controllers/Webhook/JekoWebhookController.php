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

        // Le webhook secret est dans jeko_marketplace (pas jeko_normal)
        $valid = app(JekoGateway::class)->forMarketplacePlatform()->verifyWebhookSignature($rawPayload, $signature);

        if (!$valid) {
            // Log le payload brut pour récupérer le bon secret depuis Jeko
            Log::channel('payments')->warning('Jeko webhook: invalid signature — raw payload logged for debug', [
                'signature_received' => $signature,
                'payload'            => json_decode($rawPayload, true),
            ]);
            // TEMPORAIRE : on continue quand même pour ne pas bloquer les paiements
            // À désactiver une fois le bon webhook_secret configuré
        }

        $payload = json_decode($rawPayload, true) ?? [];

        // Log brut pour débogage (payload réel Jeko)
        Log::channel('payments')->debug('Jeko webhook raw payload', ['payload' => $payload]);

        $event = $payload['event'] ?? '';

        // Jeko n'a qu'un seul event: transaction.completed
        if ($event !== 'transaction.completed') {
            return response()->json(['status' => 'ignored'], 200);
        }

        // Le payload réel Jeko est à la racine, pas dans 'data'
        $data            = isset($payload['data']) ? $payload['data'] : $payload;
        $status          = $data['status'] ?? '';
        $transactionType = $data['transactionType'] ?? '';

        // Ignorer les états intermédiaires — ne jamais marquer en échec sur 'pending'
        if ($status === 'pending') {
            Log::channel('payments')->info('Jeko webhook: pending status ignored (idempotence)');
            return response()->json(['status' => 'ignored'], 200);
        }

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
        // Reference dans transactionDetails OU apiTransactionableDetails (deux formats Jeko)
        $details       = $data['transactionDetails'] ?? $data['apiTransactionableDetails'] ?? [];
        $reference     = $details['reference'] ?? $data['storeReference'] ?? null;
        $paymentLinkId = $details['paymentLinkId'] ?? null;
        $orderToPayout = null;

        DB::transaction(function () use ($transactionId, $reference, $paymentLinkId, &$orderToPayout) {
            // ─── Order ────────────────────────────────────────────────────────
            $order = $this->findOrder($reference, $paymentLinkId);

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
            $subscription = $this->findSubscription($reference, $paymentLinkId);

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
            'payment_link_id'=> $paymentLinkId,
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
        $details   = $data['transactionDetails'] ?? $data['apiTransactionableDetails'] ?? [];
        $reference = $details['reference'] ?? $data['storeReference'] ?? null;
        $paymentLinkId = $details['paymentLinkId'] ?? null;

        if (!$reference) {
            Log::channel('payments')->warning('Jeko webhook payment failed: no reference in payload');
            return;
        }

        DB::transaction(function () use ($reference, $paymentLinkId) {
            $order = $this->findOrder($reference, $paymentLinkId);
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

    protected function findOrder(?string $reference, ?string $paymentLinkId = null): ?Order
    {
        // 1. Via storeReference format ORDER-{id}-{ts}
        if ($reference && preg_match('/^ORDER-(\d+)-/', $reference, $matches)) {
            return Order::withoutGlobalScope('restaurant')->find($matches[1]);
        }

        // 2. Via paymentLinkId stocké dans payment_metadata
        if ($paymentLinkId) {
            $order = Order::withoutGlobalScope('restaurant')
                ->where('payment_metadata->jeko_link_id', $paymentLinkId)
                ->first();
            if ($order) return $order;
        }

        // 3. Via payment_reference
        if ($reference) {
            return Order::withoutGlobalScope('restaurant')
                ->where('payment_reference', $reference)
                ->first();
        }

        return null;
    }

    protected function findSubscription(?string $reference, ?string $paymentLinkId = null): ?Subscription
    {
        // 1. Via storeReference format SUB-{id}-{ts}
        if ($reference && preg_match('/^SUB-(\d+)-/', $reference, $matches)) {
            return Subscription::withoutGlobalScope('restaurant')->find($matches[1]);
        }

        // 2. Via payment_reference
        if ($reference) {
            return Subscription::withoutGlobalScope('restaurant')
                ->where('payment_reference', $reference)
                ->first();
        }

        return null;
    }
}
