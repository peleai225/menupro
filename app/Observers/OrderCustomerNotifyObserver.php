<?php

namespace App\Observers;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Notifications\OrderConfirmationCustomerNotification;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;

class OrderCustomerNotifyObserver
{
    /**
     * Send confirmation email to customer when order is paid.
     */
    public function updated(Order $order): void
    {
        if (!$order->wasChanged('status') || $order->status !== OrderStatus::PAID) {
            return;
        }

        $email = $order->customer_email;
        if (empty($email)) {
            return;
        }

        try {
            Notification::route('mail', $email)
                ->notify(new OrderConfirmationCustomerNotification($order));

            Log::info('Order customer confirmation email sent', [
                'order_id' => $order->id,
                'email' => $email,
            ]);
        } catch (\Throwable $e) {
            Log::warning('Failed to send order confirmation to customer', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
