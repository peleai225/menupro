<?php

namespace App\Observers;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Services\WhatsAppService;
use Illuminate\Support\Facades\Log;

class OrderWhatsAppObserver
{
    public function __construct(
        protected WhatsAppService $whatsAppService
    ) {}

    /**
     * When order status changes, send WhatsApp notification to customer.
     */
    public function updated(Order $order): void
    {
        if (!$order->wasChanged('status')) {
            return;
        }

        try {
            // Send confirmation when order is paid
            if ($order->status === OrderStatus::PAID && $order->customer_phone) {
                $this->whatsAppService->sendOrderConfirmation($order);
            }

            // Send status updates for key transitions
            if (in_array($order->status, [
                OrderStatus::CONFIRMED,
                OrderStatus::PREPARING,
                OrderStatus::READY,
                OrderStatus::DELIVERING,
                OrderStatus::COMPLETED,
                OrderStatus::CANCELLED,
            ]) && $order->customer_phone) {
                $this->whatsAppService->sendOrderStatusUpdate($order);
            }
        } catch (\Throwable $e) {
            Log::warning('WhatsApp observer notification failed for order ' . $order->reference . ': ' . $e->getMessage());
        }
    }
}
