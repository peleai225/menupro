<?php

namespace App\Jobs;

use App\Models\Order;
use App\Services\JekoGateway;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ProcessJekoPayoutJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $backoff = 60;

    public function __construct(public readonly Order $order)
    {
        $this->onQueue('payouts');
    }

    public function handle(JekoGateway $gateway): void
    {
        $restaurant = $this->order->restaurant;

        if (!$restaurant) {
            Log::channel('payments')->warning('ProcessJekoPayoutJob: restaurant not found', ['order_id' => $this->order->id]);
            return;
        }

        $subMerchant = $restaurant->jekoSubMerchant;

        if (!$subMerchant || !$subMerchant->isIntegrated()) {
            Log::channel('payments')->warning('ProcessJekoPayoutJob: restaurant not Jeko integrated', [
                'order_id'      => $this->order->id,
                'restaurant_id' => $restaurant->id,
            ]);
            return;
        }

        if (!$this->order->payment_status->isSuccessful()) {
            Log::channel('payments')->warning('ProcessJekoPayoutJob: order not paid', [
                'order_id' => $this->order->id,
            ]);
            return;
        }

        $reference = 'PAYOUT-ORDER-' . $this->order->id . '-' . $this->order->payment_reference;
        $amount    = $this->order->total;
        $phone     = $subMerchant->mobile_money;

        if (empty($phone)) {
            Log::channel('payments')->error('ProcessJekoPayoutJob: no mobile_money number on JekoSubMerchant', [
                'order_id' => $this->order->id,
                'sub_merchant_id' => $subMerchant->id,
            ]);
            return;
        }

        $paymentMethod = $subMerchant->mobile_money_operator?->value ?? 'wave';

        $result = $gateway->forMarketplace($restaurant)->payout(
            $phone,
            $amount,
            $restaurant->name,
            "Reversement commande #{$this->order->id}",
            $reference,
            $paymentMethod
        );

        if ($result['success']) {
            Log::channel('payments')->info('Jeko payout dispatched', [
                'order_id'   => $this->order->id,
                'amount'     => $amount,
                'payout_id'  => $result['transfer_id'] ?? null,
            ]);
        } else {
            Log::channel('payments')->error('Jeko payout failed', [
                'order_id' => $this->order->id,
                'error'    => $result['error'] ?? 'unknown',
            ]);
            throw new \RuntimeException('Jeko payout failed: ' . ($result['error'] ?? 'unknown'));
        }
    }
}
