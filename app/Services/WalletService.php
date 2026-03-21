<?php

namespace App\Services;

use App\Jobs\ProcessPayoutJob;
use App\Models\CommissionLog;
use App\Models\PaymentTransaction;
use App\Models\PayoutTransaction;
use App\Models\RestaurantWallet;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WalletService
{
    /**
     * Crédite le wallet d’un restaurant à partir d’un paiement complété.
     *
     * - calcule la commission MenuPro
     * - met à jour balance et total_collected
     */
    public function creditWallet(int $restaurantId, int $paymentId): RestaurantWallet
    {
        return DB::transaction(function () use ($restaurantId, $paymentId) {
            /** @var PaymentTransaction $payment */
            $payment = PaymentTransaction::query()
                ->where('id', $paymentId)
                ->where('restaurant_id', $restaurantId)
                ->where('status', 'completed')
                ->lockForUpdate()
                ->firstOrFail();

            // Taux de commission : priorité au backoffice, sinon config/wave.php
            $commissionRate = (float) \App\Models\SystemSetting::get(
                'wave_commission_rate',
                config('wave.commission_rate', 0.02)
            );

            $amount = (float) $payment->amount;
            $commission = round($amount * $commissionRate, 2);
            $netAmount = max(0.0, $amount - $commission);

            $payment->commission = $commission;
            $payment->net_amount = $netAmount;
            $payment->save();

            // Enregistrer la commission plateforme dans CommissionLog (vue unifiée avec Hub)
            if ($commission > 0 && $payment->order_id) {
                CommissionLog::firstOrCreate(
                    ['order_id' => $payment->order_id],
                    [
                        'restaurant_id'   => $restaurantId,
                        'amount'          => $commission,
                        'order_total'     => (int) $amount,
                        'commission_rate' => $commissionRate * 100, // stocker en %
                    ]
                );
            }

            /** @var RestaurantWallet $wallet */
            $wallet = RestaurantWallet::query()
                ->where('restaurant_id', $restaurantId)
                ->lockForUpdate()
                ->firstOrCreate(
                    ['restaurant_id' => $restaurantId],
                    ['balance' => 0, 'total_collected' => 0, 'total_withdrawn' => 0]
                );

            $wallet->balance = (float) $wallet->balance + $netAmount;
            $wallet->total_collected = (float) $wallet->total_collected + $netAmount;
            $wallet->save();

            // Déclencher l'auto-payout si activé
            $this->triggerAutoPayout($wallet, $netAmount);

            return $wallet;
        });
    }

    /**
     * Déclenche un payout automatique si les conditions sont remplies :
     * - auto_payout activé sur le wallet
     * - numéro de téléphone configuré
     * - solde >= montant minimum de payout
     */
    protected function triggerAutoPayout(RestaurantWallet $wallet, float $creditedAmount): void
    {
        try {
            // Recharger pour avoir les dernières valeurs (après le save dans la transaction)
            $wallet->refresh();

            if (!$wallet->auto_payout_enabled) {
                return;
            }

            if (empty($wallet->phone)) {
                Log::info('Auto-payout ignoré : pas de numéro de téléphone configuré', [
                    'restaurant_id' => $wallet->restaurant_id,
                ]);
                return;
            }

            $minAmount = $wallet->min_payout_amount ?? 1000;
            $balance = (float) $wallet->balance;

            if ($balance < $minAmount) {
                Log::info('Auto-payout ignoré : solde insuffisant', [
                    'restaurant_id' => $wallet->restaurant_id,
                    'balance' => $balance,
                    'min_amount' => $minAmount,
                ]);
                return;
            }

            // Montant à transférer = tout le solde disponible
            $payoutAmount = (int) floor($balance);

            if ($payoutAmount <= 0) {
                return;
            }

            $restaurant = $wallet->restaurant;
            $recipientName = $restaurant?->name ?? 'Restaurant #' . $wallet->restaurant_id;

            // Créer la PayoutTransaction
            $payout = PayoutTransaction::create([
                'restaurant_id' => $wallet->restaurant_id,
                'restaurant_wallet_id' => $wallet->id,
                'gateway' => $wallet->payout_gateway ?? 'wave',
                'amount' => $payoutAmount,
                'currency' => 'XOF',
                'mobile' => $wallet->prefix . $wallet->phone,
                'recipient_name' => $recipientName,
                'payment_reason' => 'Auto-payout commande',
                'client_reference' => 'AP-' . now()->format('ymdHis') . '-' . $wallet->restaurant_id,
                'idempotency_key' => 'auto-' . Str::uuid()->toString(),
                'status' => 'pending',
            ]);

            // Dispatcher le job de payout
            ProcessPayoutJob::dispatch($payout->id);

            Log::info('Auto-payout déclenché', [
                'restaurant_id' => $wallet->restaurant_id,
                'payout_id' => $payout->id,
                'amount' => $payoutAmount,
                'mobile' => $wallet->phone,
                'gateway' => $wallet->payout_gateway,
            ]);
        } catch (\Throwable $e) {
            // Ne jamais bloquer le flux principal si l'auto-payout échoue
            Log::error('Auto-payout failed', [
                'restaurant_id' => $wallet->restaurant_id,
                'error' => $e->getMessage(),
            ]);
        }
    }

    /**
     * Débite le wallet d’un restaurant (sans déclencher le payout lui-même).
     *
     * Cette méthode est à appeler depuis le job de payout une fois Wave confirmé.
     */
    public function debitWallet(int $restaurantId, float $amount): RestaurantWallet
    {
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Le montant à débiter doit être strictement positif.');
        }

        return DB::transaction(function () use ($restaurantId, $amount) {
            /** @var RestaurantWallet $wallet */
            $wallet = RestaurantWallet::query()
                ->where('restaurant_id', $restaurantId)
                ->lockForUpdate()
                ->firstOrFail();

            if ((float) $wallet->balance < $amount) {
                throw new \DomainException('Solde insuffisant sur le wallet du restaurant.');
            }

            $wallet->balance = (float) $wallet->balance - $amount;
            $wallet->total_withdrawn = (float) $wallet->total_withdrawn + $amount;
            $wallet->save();

            return $wallet;
        });
    }

    /**
     * Retourne les infos de solde pour un restaurant.
     *
     * @return array{balance:float,total_collected:float,total_withdrawn:float}
     */
    public function getBalance(int $restaurantId): array
    {
        /** @var RestaurantWallet|null $wallet */
        $wallet = RestaurantWallet::query()
            ->where('restaurant_id', $restaurantId)
            ->first();

        if (!$wallet) {
            return [
                'balance' => 0.0,
                'total_collected' => 0.0,
                'total_withdrawn' => 0.0,
            ];
        }

        return [
            'balance' => (float) $wallet->balance,
            'total_collected' => (float) $wallet->total_collected,
            'total_withdrawn' => (float) $wallet->total_withdrawn,
        ];
    }
}

