<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\ProcessPayoutJob;
use App\Models\PayoutTransaction;
use App\Models\RestaurantWallet;
use App\Services\WalletService;
use App\Services\WavePayoutService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PayoutController extends Controller
{
    public function __construct(
        protected WavePayoutService $wavePayoutService,
        protected WalletService $walletService,
    ) {
    }

    /**
     * Demander un payout Wave pour un restaurant.
     */
    public function requestPayout(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'restaurant_id' => ['required', 'integer', 'exists:restaurants,id'],
            'amount' => ['required', 'integer', 'min:500'],
            'mobile' => ['required', 'regex:/^\+225\d{8}$/'],
            'recipient_name' => ['required', 'string', 'max:255'],
            'payment_reason' => ['nullable', 'string', 'max:40'],
        ], [
            'mobile.regex' => 'Le numéro doit être au format +225XXXXXXXX.',
        ]);

        $user = $request->user();
        if (!$user || (int) $user->restaurant_id !== (int) $validated['restaurant_id']) {
            return response()->json(['error' => 'Accès refusé pour ce restaurant.'], 403);
        }

        /** @var RestaurantWallet|null $wallet */
        $wallet = RestaurantWallet::query()
            ->where('restaurant_id', $validated['restaurant_id'])
            ->first();

        if (!$wallet) {
            return response()->json(['error' => 'Wallet non configuré pour ce restaurant.'], 400);
        }

        $amount = (int) $validated['amount'];

        // Vérifier solde suffisant
        $balance = $this->walletService->getBalance($validated['restaurant_id']);
        if ($balance['balance'] < $amount) {
            return response()->json([
                'error' => 'Solde insuffisant sur le wallet virtuel.',
            ], 422);
        }

        // Vérification Wave avant payout
        try {
            $verification = $this->wavePayoutService->verifyRecipient(
                $validated['mobile'],
                $validated['recipient_name'],
                $amount
            );
        } catch (\Throwable $e) {
            return response()->json([
                'error' => 'Impossible de vérifier le bénéficiaire Wave : ' . $e->getMessage(),
            ], 502);
        }

        if (isset($verification['within_limits']) && $verification['within_limits'] === false) {
            return response()->json([
                'error' => 'Le bénéficiaire ne peut pas recevoir ce montant (limite atteinte).',
                'verification' => $verification,
            ], 422);
        }

        $idempotencyKey = sprintf(
            'PAYOUT-%d-%s',
            $validated['restaurant_id'],
            Str::uuid()->toString()
        );

        $clientReference = sprintf(
            'PAYOUT-%d-%s',
            $validated['restaurant_id'],
            Str::uuid()->toString()
        );

        /** @var PayoutTransaction $payout */
        $payout = PayoutTransaction::create([
            'restaurant_id' => $validated['restaurant_id'],
            'restaurant_wallet_id' => $wallet->id,
            'gateway' => 'wave',
            'amount' => $amount,
            'fee' => 0,
            'currency' => 'XOF',
            'wave_payout_id' => null,
            'client_reference' => $clientReference,
            'mobile' => $validated['mobile'],
            'recipient_name' => $validated['recipient_name'],
            'payment_reason' => $validated['payment_reason'] ?? null,
            'status' => 'pending',
            'idempotency_key' => $idempotencyKey,
            'payout_error' => null,
        ]);

        // Dispatch du job de traitement du payout
        ProcessPayoutJob::dispatch($payout->id)
            ->onQueue('payouts');

        return response()->json([
            'payout_id' => $payout->id,
            'status' => $payout->status,
            'verification' => $verification,
        ]);
    }

    /**
     * Récupérer le statut d’un payout.
     */
    public function getPayoutStatus(string $payoutId): JsonResponse
    {
        /** @var PayoutTransaction $payout */
        $payout = PayoutTransaction::query()->findOrFail($payoutId);

        // Si processing/pending avec un id Wave, on rafraîchit l'état depuis l’API
        if (in_array($payout->status, ['pending', 'processing'], true) && $payout->wave_payout_id) {
            try {
                $result = $this->wavePayoutService->getPayout($payout->wave_payout_id);
                $raw = $result['raw'];

                $payout->status = $result['status'];
                if (!empty($raw['payout_error'])) {
                    $payout->payout_error = $raw['payout_error'];
                }
                $payout->save();
            } catch (\Throwable $e) {
                // On ne bloque pas la réponse au client sur une erreur transitoire
            }
        }

        return response()->json([
            'id' => $payout->id,
            'status' => $payout->status,
            'wave_payout_id' => $payout->wave_payout_id,
            'payout_error' => $payout->payout_error,
        ]);
    }

    /**
     * Récupérer le solde du wallet pour un restaurant.
     */
    public function getWalletBalance(int $restaurantId): JsonResponse
    {
        $user = request()->user();
        if (!$user || (int) $user->restaurant_id !== (int) $restaurantId) {
            return response()->json(['error' => 'Accès refusé pour ce restaurant.'], 403);
        }

        $balance = $this->walletService->getBalance($restaurantId);

        return response()->json($balance);
    }
}

