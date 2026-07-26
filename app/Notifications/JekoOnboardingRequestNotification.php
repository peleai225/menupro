<?php

namespace App\Notifications;

use App\Models\Restaurant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class JekoOnboardingRequestNotification extends Notification implements ShouldQueue
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
            'type'            => 'jeko_onboarding_request',
            'restaurant_id'   => $this->restaurant->id,
            'restaurant_name' => $this->restaurant->name,
            'message'         => "Nouvelle demande d'intégration Jeko de {$this->restaurant->name}",
        ];
    }
}
