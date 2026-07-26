<?php

namespace App\Notifications;

use App\Models\Restaurant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class JekoIntegrationRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected Restaurant $restaurant, protected string $reason) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'            => 'jeko_integration_rejected',
            'restaurant_id'   => $this->restaurant->id,
            'restaurant_name' => $this->restaurant->name,
            'reason'          => $this->reason,
            'message'         => "Votre demande d'intégration Jeko pour {$this->restaurant->name} a été refusée.",
        ];
    }
}
