<?php

namespace App\Observers;

use App\Enums\OrderStatus;
use App\Jobs\SendOrderWhatsAppNotification;
use App\Models\Order;

class OrderWhatsAppObserver
{
    public function updated(Order $order): void
    {
        if (!$order->wasChanged('status')) {
            return;
        }

        if (!$order->customer_phone) {
            return;
        }

        if ($order->status === OrderStatus::PAID) {
            SendOrderWhatsAppNotification::dispatch($order, 'confirmation');
        }

        if (in_array($order->status, [
            OrderStatus::CONFIRMED,
            OrderStatus::PREPARING,
            OrderStatus::READY,
            OrderStatus::DELIVERING,
            OrderStatus::COMPLETED,
            OrderStatus::CANCELLED,
        ])) {
            SendOrderWhatsAppNotification::dispatch($order, 'status_update');
        }
    }
}
