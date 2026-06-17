<?php

namespace App\Notifications\Admin;

use App\Models\Restaurant;
use Illuminate\Notifications\Notification;

class SubscriptionExpiredAdminNotification extends Notification
{
    public function __construct(
        protected Restaurant $restaurant
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'subscription_expired',
            'restaurant_id' => $this->restaurant->id,
            'restaurant_name' => $this->restaurant->name,
            'message' => "Abonnement expiré : {$this->restaurant->name}",
            'url' => route('super-admin.restaurants.show', $this->restaurant),
        ];
    }
}
