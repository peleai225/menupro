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
     *
     * Ce callback GET sert uniquement à informer le client du statut courant de la commande.
     * La modification d'état (markAsPaid, assignation livreur) est exclusivement gérée par
     * le webhook Wave signé (WaveWebhookController) afin d'éviter toute manipulation via
     * un appel GET non authentifié avec le tracking_token.
     */
    public function success(Request $request): JsonResponse
    {
        $order = Order::where('tracking_token', $request->token)->firstOrFail();

        return response()->json([
            'message'        => 'Redirection paiement reçue. Statut en cours de vérification.',
            'tracking_token' => $order->tracking_token,
            'order_status'   => $order->status,
            'payment_status' => $order->payment_status,
        ]);
    }

    /**
     * Callback échec paiement Wave (redirect depuis Wave).
     *
     * Ce callback GET ne modifie pas l'état de la commande — la mise à jour du statut de
     * paiement est réservée au webhook Wave signé.
     */
    public function error(Request $request): JsonResponse
    {
        $order = Order::where('tracking_token', $request->token)->first();

        return response()->json([
            'message'        => 'Le paiement a échoué ou été annulé. Veuillez réessayer.',
            'tracking_token' => $request->token,
            'order_status'   => $order?->status,
            'payment_status' => $order?->payment_status,
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
