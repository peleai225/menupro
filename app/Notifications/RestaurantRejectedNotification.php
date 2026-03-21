<?php

namespace App\Notifications;

use App\Models\Restaurant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RestaurantRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Restaurant $restaurant,
        protected string $reason
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Votre demande d\'inscription n\'a pas été acceptée')
            ->greeting("Bonjour {$notifiable->first_name},")
            ->line("Nous avons examiné votre demande d'inscription pour le restaurant **{$this->restaurant->name}** et nous ne sommes pas en mesure de la valider pour le moment.")
            ->line('**Motif du rejet :**')
            ->line($this->reason)
            ->line('Si vous pensez que cette décision est une erreur ou si vous souhaitez soumettre une nouvelle demande avec des informations corrigées, n\'hésitez pas à nous contacter.')
            ->action('Contacter le support', config('app.url'))
            ->salutation('L\'équipe MenuPro');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'              => 'restaurant_rejected',
            'restaurant_id'     => $this->restaurant->id,
            'restaurant_name'   => $this->restaurant->name,
            'reason'            => $this->reason,
            'message'           => "Votre demande pour {$this->restaurant->name} n'a pas été acceptée.",
        ];
    }
}
