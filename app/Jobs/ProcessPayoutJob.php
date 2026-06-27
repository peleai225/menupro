<?php

namespace App\Jobs;

use App\Models\PayoutTransaction;
use App\Services\WalletService;
use App\Services\WaveGateway;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessPayoutJob implements ShouldQueue, ShouldBeUnique
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function __construct(
        public int $payoutId,
    ) {
        $this->onQueue('payouts');
    }

    public function uniqueId(): string
    {
        return (string) $this->payoutId;
    }

    public function uniqueFor(): int
    {
        return 600;
    }

    public int $tries = 3;

    public array $backoff = [30, 120, 300];

    public function handle(WaveGateway $wave, WalletService $walletService): void
    {
        $payout = PayoutTransaction::find($this->payoutId);

        if (!$payout) {
            return;
        }

        if (in_array($payout->status, ['succeeded', 'failed', 'reversed'], true)) {
            return;
        }

        if (!$wave->isConfigured()) {
            $payout->update([
                'status' => 'failed',
                'payout_error' => ['error_message' => 'Wave API non configurée'],
            ]);
            return;
        }

        $payout->update(['status' => 'processing']);

        $result = $wave->payout(
            mobile: $payout->mobile,
            amount: (int) $payout->amount,
            recipientName: $payout->recipient_name ?? '',
            reason: $payout->payment_reason ?? 'Paiement MenuPro',
            clientReference: $payout->client_reference ?? $payout->idempotency_key,
        );

        if ($result['success']) {
            $payout->update([
                'status' => 'succeeded',
                'gateway_transaction_id' => $result['payout_id'],
                'wave_payout_id' => $result['payout_id'],
                'fee' => $result['fee'] ?? 0,
            ]);

            $walletService->debitWallet($payout->restaurant_id, (float) $payout->amount);

            Log::channel('payments')->info('Payout succeeded via Wave', [
                'payout_id' => $payout->id,
                'wave_payout_id' => $result['payout_id'],
                'amount' => $payout->amount,
                'mobile' => $payout->mobile,
            ]);
        } else {
            $shouldRetry = $this->attempts() < $this->tries;

            if (!$shouldRetry) {
                $payout->update([
                    'status' => 'failed',
                    'payout_error' => ['error_message' => $result['error'] ?? 'Erreur inconnue'],
                ]);
            }

            Log::channel('payments')->warning('Payout failed via Wave', [
                'payout_id' => $payout->id,
                'attempt' => $this->attempts(),
                'error' => $result['error'] ?? 'Unknown',
            ]);

            if ($shouldRetry) {
                throw new \RuntimeException("Wave payout failed: " . ($result['error'] ?? 'Unknown'));
            }
        }
    }
}
