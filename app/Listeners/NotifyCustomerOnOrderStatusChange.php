<?php

namespace App\Listeners;

use App\Events\OrderStatusChanged;
use App\Services\CustomerPushService;
use Illuminate\Contracts\Queue\ShouldQueue;

class NotifyCustomerOnOrderStatusChange implements ShouldQueue
{
    public string $queue = 'notifications';

    public function __construct(private CustomerPushService $push) {}

    public function handle(OrderStatusChanged $event): void
    {
        $this->push->notifyOrderStatusChanged($event->order);
    }
}
