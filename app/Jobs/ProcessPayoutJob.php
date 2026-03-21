<?php

namespace App\Jobs;

use App\Models\PayoutTransaction;
use App\Services\FusionPayTransferService;
use App\Services\WalletService;
use App\Services\WavePayoutService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ProcessPayoutJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    /**
     * Identifiant de la PayoutTransaction à traiter.
     */
    public function __construct(
        public int $payoutId,
    ) {
        $this->onQueue('payouts');
    }

    /**
     * Unicité du job : un seul job par payoutId en file.
     * Empêche l'exécution concurrente sur le même payout.
     */
    public function uniqueId(): string
    {
        return (string) $this->payoutId;
    }

    /**
     * Durée du verrou d'unicité en secondes (10 min max par tentative).
     */
    public function uniqueFor(): int
    {
        return 600;
    }

    /**
     * Nombre de tentatives.
     */
    public int $tries = 3;

    /**
     * Backoff entre les tentatives.
     *
     * @return array<int,int>
     */
    public function backoff(): array
    {
        return [5, 30, 120];
    }

    public function handle(
        WavePayoutService $wavePayoutService,
        FusionPayTransferService $fusionPayService,
        WalletService $walletService,
    ): void {
        /** @var PayoutTransaction|null $payout */
        $payout = PayoutTransaction::query()->find($this->payoutId);

        if (!$payout) {
            return;
        }

        // Si déjà finalisé, on ne refait rien
        if (in_array($payout->status, ['succeeded', 'failed', 'reversed'], true)) {
            return;
        }

        $gateway = $payout->gateway ?? 'wave';

        try {
            $result = match ($gateway) {
                'wave' => $this->processWavePayout($payout, $wavePayoutService),
                'fusionpay' => $this->processFusionPayPayout($payout, $fusionPayService),
                default => throw new \RuntimeException("Gateway de payout non supporté : {$gateway}"),
            };

            // Finalisation atomique : save + débit wallet dans une transaction
            // avec lockForUpdate pour prévenir un double débit si le job
            // était relancé juste après le retour de l'API.
            DB::transaction(function () use ($payout, $result, $walletService) {
                $locked = PayoutTransaction::lockForUpdate()->find($payout->id);

                // Vérification idempotence : si déjà traité entre-temps, on abandonne
                if (in_array($locked->status, ['succeeded', 'failed', 'reversed'], true)) {
                    return;
                }

                $locked->status = $result['status'];
                if (!empty($result['gateway_id'])) {
                    $locked->wave_payout_id = $result['gateway_id'];
                }
                if (!empty($result['gateway_transaction_id'])) {
                    $locked->gateway_transaction_id = $result['gateway_transaction_id'];
                }
                if (!empty($result['fee'])) {
                    $locked->fee = (float) $result['fee'];
                }
                if (!empty($result['error'])) {
                    $locked->payout_error = $result['error'];
                }
                $locked->save();

                if ($locked->status === 'succeeded') {
                    // Débiter le wallet (montant net reçu par le restaurant)
                    $walletService->debitWallet($locked->restaurant_id, (float) $locked->amount);
                }
            });

            // Synchroniser l'objet local avec ce qui a été persisté
            $payout->refresh();
        } catch (\Throwable $e) {
            $code = $e->getCode();

            // Erreurs système / réseau : on laisse en pending et on déclenche un retry
            if (is_int($code) && (in_array($code, [408, 429, 500, 502, 503, 504], true) || ($code >= 500 && $code <= 599))) {
                Log::warning("Payout {$gateway} temporairement indisponible, retry", [
                    'payout_id' => $payout->id,
                    'error' => $e->getMessage(),
                    'code' => $code,
                ]);

                $this->release($this->backoff()[0]);

                return;
            }

            // Erreurs de validation / limites : échec définitif
            $payout->status = 'failed';
            $payout->payout_error = [
                'error_message' => $e->getMessage(),
                'error_code' => $code,
            ];
            $payout->save();

            Log::error("Payout {$gateway} failed definitively", [
                'payout_id' => $payout->id,
                'error' => $e->getMessage(),
                'code' => $code,
            ]);
        }
    }

    /**
     * Traite un payout via Wave.
     */
    protected function processWavePayout(PayoutTransaction $payout, WavePayoutService $wavePayoutService): array
    {
        $result = $wavePayoutService->sendPayout([
            'restaurant_id' => $payout->restaurant_id,
            'amount' => (int) $payout->amount,
            'mobile' => $payout->mobile,
            'recipient_name' => $payout->recipient_name,
            'client_reference' => $payout->client_reference,
            'payment_reason' => $payout->payment_reason,
            'idempotency_key' => $payout->idempotency_key,
        ]);

        $raw = $result['raw'] ?? [];

        return [
            'status' => $result['status'],
            'gateway_id' => $raw['id'] ?? null,
            'fee' => $raw['fee'] ?? null,
            'error' => $raw['payout_error'] ?? null,
        ];
    }

    /**
     * Traite un payout via FusionPay.
     * Note: On appelle l'API directement car FusionPayTransferService::sendPayout()
     * gère son propre débit wallet et PayoutTransaction, ce qui ferait doublon
     * avec la logique du job.
     */
    protected function processFusionPayPayout(PayoutTransaction $payout, FusionPayTransferService $fusionPayService): array
    {
        if (!$fusionPayService->isPayoutEnabled()) {
            throw new \RuntimeException('FusionPay payout non activé dans les paramètres système.');
        }

        $phone = preg_replace('/\D/', '', $payout->mobile);
        if (str_starts_with($phone, '225') || str_starts_with($phone, '221')) {
            $phone = substr($phone, 3);
        }

        $prefix = '225';
        $countryCode = 'ci';

        // Determine withdraw mode from phone
        $withdrawMode = 'mtn-ci';
        if (str_starts_with($phone, '07')) {
            $withdrawMode = 'mtn-ci';
        } elseif (str_starts_with($phone, '05') && strlen($phone) === 10) {
            $withdrawMode = 'wave-ci';
        } elseif (str_starts_with($phone, '01')) {
            $withdrawMode = 'orange-money-ci';
        }

        $appUrl = rtrim(\App\Models\SystemSetting::get('app_url', config('app.url')), '/');
        $privateKey = \App\Models\SystemSetting::get('fusionpay_private_key', config('fusionpay.private_key'));

        $response = \Illuminate\Support\Facades\Http::withHeaders([
            'Content-Type' => 'application/json',
            'moneyfusion-private-key' => $privateKey,
        ])->post(config('fusionpay.payout_url'), [
            'countryCode' => $countryCode,
            'phone' => $phone,
            'amount' => (int) $payout->amount,
            'withdraw_mode' => $withdrawMode,
            'webhook_url' => $appUrl . '/webhooks/fusionpay/payout',
        ]);

        $data = $response->json();

        if (!$response->successful() || empty($data['statut'])) {
            throw new \RuntimeException('Erreur FusionPay: ' . ($data['message'] ?? $response->body()));
        }

        return [
            'status' => 'processing', // FusionPay confirme via webhook
            'gateway_transaction_id' => $data['tokenPay'] ?? null,
            'fee' => null,
            'error' => null,
        ];
    }
}
