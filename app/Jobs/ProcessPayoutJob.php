<?php

namespace App\Jobs;

use App\Models\PayoutTransaction;
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

    public int $tries = 1;

    public function handle(): void
    {
        $payout = PayoutTransaction::query()->find($this->payoutId);

        if (!$payout) {
            return;
        }

        if (in_array($payout->status, ['succeeded', 'failed', 'reversed'], true)) {
            return;
        }

        // Payouts temporairement indisponibles — marquer en échec
        $payout->status = 'failed';
        $payout->payout_error = ['error_message' => 'Service de retrait temporairement indisponible. Contactez le support.'];
        $payout->save();

        Log::warning('Payout failed: no payout gateway available', ['payout_id' => $payout->id]);
    }
}
