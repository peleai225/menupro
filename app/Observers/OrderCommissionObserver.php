<?php

namespace App\Observers;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\MenuProHubService;

class OrderCommissionObserver
{
    public function __construct(
        protected MenuProHubService $hubService
    ) {}

    /**
     * When order transitions to CONFIRMED, deduct MenuPro Hub commission if applicable.
     */
    public function updated(Order $order): void
    {
        if ($order->wasChanged('status') && $order->status === OrderStatus::CONFIRMED) {
            $this->hubService->deductCommission($order);
        }
    }
}
