<?php

namespace App\Notifications\Admin;

use App\Notifications\Channels\PushChannel;
use App\Models\Subscription;
use Illuminate\Notifications\Notification;

class NewPaymentReceivedNotification extends Notification
{
    public function __construct(
        protected Subscription $subscription
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', PushChannel::class];
    }

    public function toPush(object $notifiable): array
    {
        $restaurant = $this->subscription->restaurant;
        $amount = number_format($this->subscription->amount_paid, 0, ',', ' ');

        return [
            'title' => "💰 Paiement reçu : {$amount} F",
            'body'  => 'Abonnement de ' . ($restaurant?->name ?? 'un restaurant') . '.',
            'data'  => [
                'type'            => 'new_payment',
                'subscription_id' => (string) $this->subscription->id,
            ],
            'link' => route('super-admin.subscriptions.show', $this->subscription),
            'tag'  => 'payment-' . $this->subscription->id,
        ];
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
