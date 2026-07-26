<?php

namespace App\Notifications;

use App\Models\Restaurant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class JekoIntegrationApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected Restaurant $restaurant) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'            => 'jeko_integration_approved',
            'restaurant_id'   => $this->restaurant->id,
            'restaurant_name' => $this->restaurant->name,
            'message'         => "Votre intégration Jeko pour {$this->restaurant->name} a été approuvée ! L'intégration automatique est en cours.",
        ];
    }
}
