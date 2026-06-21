<?php

namespace App\Notifications\Admin;

use App\Models\Restaurant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class NewRestaurantRegisteredNotification extends Notification implements ShouldQueue
{
    use Queueable;

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
            'type' => 'new_restaurant',
            'restaurant_id' => $this->restaurant->id,
            'restaurant_name' => $this->restaurant->name,
            'message' => "Nouveau restaurant inscrit : {$this->restaurant->name}",
            'url' => route('super-admin.restaurants.show', $this->restaurant),
        ];
    }
}
