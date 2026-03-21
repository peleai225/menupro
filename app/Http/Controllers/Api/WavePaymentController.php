<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Services\WaveCheckoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class WavePaymentController extends Controller
{
    public function __construct(
        protected WaveCheckoutService $checkoutService,
    ) {
    }

    /**
     * Initier un paiement Wave pour une commande.
     */
    public function initiatePayment(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'restaurant_id' => ['required', 'integer', 'exists:restaurants,id'],
            'order_id' => ['required', 'integer', 'exists:orders,id'],
            'amount' => ['required', 'integer', 'min:200', 'max:70000'],
            'currency' => ['nullable', 'string', 'size:3'],
        ]);

        $user = $request->user();
        if (!$user || (int) $user->restaurant_id !== (int) $validated['restaurant_id']) {
            return response()->json(['error' => 'Accès refusé pour ce restaurant.'], 403);
        }

        /** @var Order $order */
        $order = Order::query()
            ->where('id', $validated['order_id'])
            ->where('restaurant_id', $validated['restaurant_id'])
            ->firstOrFail();

        $currency = strtoupper($validated['currency'] ?? 'XOF');
        $amount = (int) $validated['amount'];

        $clientReference = sprintf(
            'ORDER-%d-%d-%s',
            $validated['restaurant_id'],
            $validated['order_id'],
            Str::uuid()->toString()
        );

        // Créer la transaction de paiement en base (pending)
        $payment = PaymentTransaction::create([
            'restaurant_id' => $validated['restaurant_id'],
            'order_id' => $validated['order_id'],
            'gateway' => 'wave',
            'amount' => $amount,
            'commission' => 0,
            'net_amount' => 0,
            'currency' => $currency,
            'status' => 'pending',
            'client_reference' => $clientReference,
            'metadata' => [
                'order_reference' => $order->reference ?? null,
                'source' => 'api.wave',
            ],
        ]);

        try {
            $session = $this->checkoutService->createSession([
                'amount' => $amount,
                'restaurant_id' => $validated['restaurant_id'],
                'order_id' => $validated['order_id'],
                'currency' => $currency,
                'client_reference' => $clientReference,
            ]);

            if (!empty($session['checkout_id'])) {
                $payment->wave_checkout_id = $session['checkout_id'];
                $payment->save();
            }

            return response()->json([
                'wave_launch_url' => $session['wave_launch_url'],
                'payment_id' => $payment->id,
                'client_reference' => $clientReference,
            ]);
        } catch (\Throwable $e) {
            $payment->status = 'failed';
            $payment->metadata = array_merge($payment->metadata ?? [], [
                'wave_error' => $e->getMessage(),
            ]);
            $payment->save();

            return response()->json([
                'error' => 'Erreur lors de la création du paiement Wave : ' . $e->getMessage(),
            ], 500);
        }
    }
}

