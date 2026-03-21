<?php

namespace App\Jobs;

use App\Models\PayoutTransaction;
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

    public function handle(WavePayoutService $wavePayoutService, WalletService $walletService): void
    {
        /** @var PayoutTransaction|null $payout */
        $payout = PayoutTransaction::query()->find($this->payoutId);

        if (!$payout) {
            return;
        }

        // Si déjà finalisé, on ne refait rien
        if (in_array($payout->status, ['succeeded', 'failed', 'reversed'], true)) {
            return;
        }

        try {
            $result = $wavePayoutService->sendPayout([
                'restaurant_id' => $payout->restaurant_id,
                'amount' => (int) $payout->amount,
                'mobile' => $payout->mobile,
                'recipient_name' => $payout->recipient_name,
                'client_reference' => $payout->client_reference,
                'payment_reason' => $payout->payment_reason,
                'idempotency_key' => $payout->idempotency_key,
            ]);

            $raw = $result['raw'];

            if (!empty($raw['id'])) {
                $payout->wave_payout_id = $raw['id'];
            }

            if (!empty($raw['fee'])) {
                $payout->fee = (float) $raw['fee'];
            }

            if (!empty($raw['payout_error'])) {
                $payout->payout_error = $raw['payout_error'];
            }

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
                if ($payout->wave_payout_id) {
                    $locked->wave_payout_id = $payout->wave_payout_id;
                }
                if ($payout->fee) {
                    $locked->fee = $payout->fee;
                }
                if ($payout->payout_error) {
                    $locked->payout_error = $payout->payout_error;
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
            if (in_array($code, [408, 429, 500, 502, 503, 504], true) || ($code >= 500 && $code <= 599)) {
                Log::warning('Wave payout temporairement indisponible, retry', [
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

            Log::error('Wave payout failed definitively', [
                'payout_id' => $payout->id,
                'error' => $e->getMessage(),
                'code' => $code,
            ]);
        }
    }
}

