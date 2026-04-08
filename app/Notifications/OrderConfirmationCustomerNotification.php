<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class OrderConfirmationCustomerNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Order $order
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $order = $this->order;
        $restaurant = $order->restaurant;

        $trackingUrl = null;
        if ($order->tracking_token && $restaurant) {
            try {
                $trackingUrl = route('r.order.status', [$restaurant->slug, $order->tracking_token]);
            } catch (\Throwable $e) {
                $trackingUrl = null;
            }
        }

        return (new MailMessage)
            ->subject("✅ Commande #{$order->reference} confirmée — {$restaurant->name}")
            ->view('emails.order-customer-confirmation', [
                'order' => $order->load('items'),
                'restaurant' => $restaurant,
                'trackingUrl' => $trackingUrl,
            ]);
    }
}
