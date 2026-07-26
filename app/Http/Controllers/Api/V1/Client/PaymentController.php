<?php

namespace App\Http\Controllers\Api\V1\Client;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Services\DriverAssignmentService;
use App\Services\JekoGateway;
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
     * Initier un paiement pour une commande plateforme.
     * Supporte: wave, jeko.
     */
    public function initiate(Request $request, int $orderId): JsonResponse
    {
        $customer = $request->user()->customer;

        $order = Order::where('customer_id', $customer->id)
            ->where('status', OrderStatus::PENDING_PAYMENT->value)
            ->findOrFail($orderId);

        $method = $order->payment_method ?? 'wave';

        return match ($method) {
            'wave'  => $this->initiateWave($order),
            'jeko'  => $this->initiateJeko($order),
            default => response()->json([
                'message' => "Méthode de paiement '{$method}' non supportée.",
            ], 422),
        };
    }

    /**
     * Initier un paiement via Wave.
     */
    private function initiateWave(Order $order): JsonResponse
    {
        try {
            $session = $this->wave->createCheckoutSession(
                $order,
                config('app.url') . '/api/v1/client/payment/success?token=' . $order->tracking_token,
                config('app.url') . '/api/v1/client/payment/error?token=' . $order->tracking_token,
            );

            if (!($session['success'] ?? false)) {
                Log::channel('payments')->error('Wave checkout session creation failed', [
                    'order_id' => $order->id,
                    'error'    => $session['error'] ?? 'unknown',
                ]);

                return response()->json(['message' => 'Impossible d\'initier le paiement Wave. Réessayez.'], 500);
            }

            $checkoutId = $session['checkout_id'] ?? null;

            DB::transaction(function () use ($order, $checkoutId, $session) {
                $order->update([
                    'payment_reference' => $checkoutId,
                    'payment_metadata'  => $session,
                ]);

                PaymentTransaction::create([
                    'order_id'               => $order->id,
                    'restaurant_id'          => $order->restaurant_id,
                    'gateway'                => 'wave',
                    'gateway_transaction_id' => $checkoutId,
                    'wave_checkout_id'       => $checkoutId,
                    'amount'                 => $order->total,
                    'commission'             => 0,
                    'net_amount'             => $order->total,
                    'currency'               => 'XOF',
                    'status'                 => 'pending',
                    'client_reference'       => $order->reference,
                ]);
            });

            return response()->json([
                'payment_url'    => $session['wave_launch_url'] ?? null,
                'session_id'     => $checkoutId,
                'order_id'       => $order->id,
                'amount'         => $order->total,
                'tracking_token' => $order->tracking_token,
            ]);
        } catch (\Throwable $e) {
            Log::channel('payments')->error('Wave payment initiation failed', [
                'order_id' => $order->id,
                'error'    => $e->getMessage(),
            ]);

            return response()->json(['message' => 'Impossible d\'initier le paiement Wave. Réessayez.'], 500);
        }
    }

    /**
     * Initier un paiement via Jeko (Marketplace).
     */
    private function initiateJeko(Order $order): JsonResponse
    {
        try {
            $restaurant  = $order->restaurant;
            $subMerchant = $restaurant?->jekoSubMerchant;

            if (!$subMerchant || !$subMerchant->isIntegrated()) {
                return response()->json([
                    'message' => 'Ce restaurant n\'est pas encore intégré avec Jeko.',
                ], 422);
            }

            $gateway = app(JekoGateway::class)->forMarketplace($restaurant);

            $result = $gateway->createPayment(
                $order,
                config('app.url') . '/api/v1/client/payment/success?token=' . $order->tracking_token,
                config('app.url') . '/api/v1/client/payment/error?token=' . $order->tracking_token,
            );

            if (!$result['success']) {
                Log::channel('payments')->error('Jeko payment initiation failed', [
                    'order_id' => $order->id,
                    'error'    => $result['error'] ?? 'unknown',
                ]);

                return response()->json(['message' => 'Impossible d\'initier le paiement Jeko. Réessayez.'], 500);
            }

            DB::transaction(function () use ($order, $result) {
                $order->update([
                    'payment_reference' => $result['payment_id'],
                    'payment_metadata'  => $result,
                ]);

                PaymentTransaction::create([
                    'order_id'               => $order->id,
                    'restaurant_id'          => $order->restaurant_id,
                    'gateway'                => 'jeko',
                    'gateway_transaction_id' => $result['payment_id'],
                    'jeko_payment_id'        => $result['payment_id'],
                    'jeko_reference'         => $order->reference,
                    'amount'                 => $order->total,
                    'commission'             => 0,
                    'net_amount'             => $order->total,
                    'currency'               => 'XOF',
                    'status'                 => 'pending',
                    'client_reference'       => $order->reference,
                ]);
            });

            return response()->json([
                'payment_url'    => $result['payment_url'],
                'payment_id'     => $result['payment_id'],
                'order_id'       => $order->id,
                'amount'         => $order->total,
                'tracking_token' => $order->tracking_token,
            ]);
        } catch (\Throwable $e) {
            Log::channel('payments')->error('Jeko payment initiation exception', [
                'order_id' => $order->id,
                'error'    => $e->getMessage(),
            ]);

            return response()->json(['message' => 'Impossible d\'initier le paiement Jeko. Réessayez.'], 500);
        }
    }

    /**
     * Callback succès paiement (redirect depuis la passerelle).
     *
     * Ce callback GET sert uniquement à informer le client du statut courant de la commande.
     * La modification d'état (markAsPaid, assignation livreur) est exclusivement gérée par
     * le webhook signé afin d'éviter toute manipulation via un appel GET non authentifié.
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
     * Callback échec paiement (redirect depuis la passerelle).
     *
     * Ce callback GET ne modifie pas l'état de la commande — la mise à jour du statut de
     * paiement est réservée au webhook signé.
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
