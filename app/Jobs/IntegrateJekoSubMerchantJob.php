<?php

namespace App\Jobs;

use App\Enums\JekoSubMerchantStatus;
use App\Models\JekoSubMerchant;
use App\Services\JekoGateway;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class IntegrateJekoSubMerchantJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 120;

    public function __construct(public readonly JekoSubMerchant $subMerchant)
    {
        $this->onQueue('payments');
    }

    public function handle(JekoGateway $gateway): void
    {
        if (!$this->subMerchant->isApproved()) {
            Log::channel('payments')->warning('IntegrateJekoSubMerchantJob: skipped, not APPROVED', [
                'id'     => $this->subMerchant->id,
                'status' => $this->subMerchant->status->value,
            ]);
            return;
        }

        $result = $gateway->forPlatform()->integrateSubMerchant($this->subMerchant);

        if ($result['success']) {
            $this->subMerchant->update([
                'status'               => JekoSubMerchantStatus::INTEGRATED,
                'jeko_merchant_id'     => $result['merchant_id'],
                'jeko_store_id'        => $result['store_id'] ?? null,
                'jeko_wallet_id'       => $result['wallet_id'] ?? null,
                'integration_metadata' => $result,
            ]);

            Log::channel('payments')->info('Jeko sub-merchant integrated successfully', [
                'restaurant_id' => $this->subMerchant->restaurant_id,
                'merchant_id'   => $result['merchant_id'],
            ]);
        } else {
            Log::channel('payments')->error('Jeko sub-merchant integration failed', [
                'restaurant_id' => $this->subMerchant->restaurant_id,
                'error'         => $result['error'] ?? 'unknown',
            ]);

            throw new \RuntimeException('Jeko integration failed: ' . ($result['error'] ?? 'unknown'));
        }
    }
}
