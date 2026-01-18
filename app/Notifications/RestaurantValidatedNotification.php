<?php

namespace App\Notifications;

use App\Models\Restaurant;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class RestaurantValidatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Restaurant $restaurant
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('🎉 Votre restaurant a été validé !')
            ->greeting("Félicitations {$notifiable->first_name} !")
            ->line("Votre restaurant **{$this->restaurant->name}** a été validé et est maintenant en ligne.")
            ->line('Vos clients peuvent désormais passer des commandes directement depuis votre page.')
            ->line('**Votre lien public :**')
            ->line($this->restaurant->public_url)
            ->action('Accéder à mon dashboard', route('restaurant.dashboard'))
            ->line('N\'hésitez pas à partager ce lien sur vos réseaux sociaux !')
            ->salutation('Bienvenue dans la famille MenuPro !');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'restaurant_validated',
            'restaurant_id' => $this->restaurant->id,
            'restaurant_name' => $this->restaurant->name,
            'public_url' => $this->restaurant->public_url,
            'message' => "Votre restaurant {$this->restaurant->name} a été validé !",
        ];
    }
}

