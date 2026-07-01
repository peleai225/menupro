<?php

namespace App\Http\Controllers\Api\V1\Client;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\DriverAssignmentService;
use App\Services\WaveGateway;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentController extends Controller
{
    public function __construct(
        private WaveGateway $wave,
        private DriverAssignmentService $driverAssignment,
    ) {}

    /**
     * Initier un paiement Wave pour une commande plateforme.
     */
    public function initiate(Request $request, int $orderId): JsonResponse
    {
        $customer = $request->user()->customer;

        $order = Order::where('customer_id', $customer->id)
            ->where('status', OrderStatus::PENDING_PAYMENT->value)
            ->findOrFail($orderId);

        if ($order->payment_method !== 'wave') {
            return response()->json([
                'message' => 'Méthode de paiement non supportée pour le moment. Utilisez Wave.',
            ], 422);
        }

        try {
            $session = $this->wave->createCheckoutSession(
                amount: $order->total / 100, // Wave attend les FCFA, pas les centimes
                currency: 'XOF',
                reference: $order->reference,
                successUrl: config('app.url') . '/api/v1/client/payment/success?token=' . $order->tracking_token,
                errorUrl: config('app.url') . '/api/v1/client/payment/error?token=' . $order->tracking_token,
            );

            $order->update([
                'payment_reference' => $session['id'] ?? null,
                'payment_metadata'  => $session,
            ]);

            return response()->json([
                'payment_url'   => $session['wave_launch_url'] ?? $session['checkout_url'] ?? null,
                'session_id'    => $session['id'] ?? null,
                'order_id'      => $order->id,
                'amount'        => $order->total,
                'tracking_token' => $order->tracking_token,
            ]);
        } catch (\Throwable $e) {
            Log::error('Platform payment initiation failed', [
                'order_id' => $order->id,
                'error'    => $e->getMessage(),
            ]);

            return response()->json(['message' => 'Impossible d\'initier le paiement. Réessayez.'], 500);
        }
    }

    /**
     * Callback succès paiement Wave (redirect depuis Wave).
     */
    public function success(Request $request): JsonResponse
    {
        $order = Order::where('tracking_token', $request->token)->firstOrFail();

        if ($order->payment_status === PaymentStatus::COMPLETED->value) {
            return response()->json(['message' => 'Paiement déjà validé.', 'order' => $order->reference]);
        }

        DB::transaction(function () use ($order) {
            $order->update([
                'payment_status' => PaymentStatus::COMPLETED->value,
                'status'         => OrderStatus::PAID->value,
                'paid_at'        => now(),
            ]);

            // Déclencher la recherche d'un livreur
            $delivery = $order->delivery;
            if ($delivery) {
                $this->driverAssignment->assign($delivery);
            }
        });

        Log::info('Platform order paid', ['order_id' => $order->id, 'reference' => $order->reference]);

        return response()->json([
            'message'        => 'Paiement confirmé. Recherche d\'un livreur en cours.',
            'tracking_token' => $order->tracking_token,
            'order_status'   => $order->fresh()->status,
        ]);
    }

    /**
     * Callback échec paiement.
     */
    public function error(Request $request): JsonResponse
    {
        $order = Order::where('tracking_token', $request->token)->first();

        if ($order) {
            $order->update(['payment_status' => PaymentStatus::FAILED->value]);
        }

        return response()->json([
            'message' => 'Le paiement a échoué. Veuillez réessayer.',
            'tracking_token' => $request->token,
        ], 400);
    }

    /**
     * Vérification manuelle du statut d'un paiement.
     */
    public function status(Request $request, int $orderId): JsonResponse
    {
        $customer = $request->user()->customer;

        $order = Order::where('customer_id', $customer->id)->findOrFail($orderId);

        return response()->json([
            'order_id'       => $order->id,
            'payment_status' => $order->payment_status,
            'order_status'   => $order->status,
            'paid_at'        => $order->paid_at,
        ]);
    }
}
