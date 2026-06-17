<?php

namespace App\Notifications\Admin;

use App\Models\Subscription;
use Illuminate\Notifications\Notification;

class NewPaymentReceivedNotification extends Notification
{
    public function __construct(
        protected Subscription $subscription
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $restaurant = $this->subscription->restaurant;
        $amount = number_format($this->subscription->amount_paid, 0, ',', ' ');

        return [
            'type' => 'new_payment',
            'subscription_id' => $this->subscription->id,
            'restaurant_id' => $restaurant?->id,
            'restaurant_name' => $restaurant?->name ?? 'N/A',
            'amount' => $this->subscription->amount_paid,
            'message' => "Paiement reçu : {$amount} F de {$restaurant?->name}",
            'url' => route('super-admin.subscriptions.show', $this->subscription),
        ];
    }
}
