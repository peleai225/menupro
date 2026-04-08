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
            ->view('emails.restaurant-validated', [
                'restaurant' => $this->restaurant,
                'notifiable' => $notifiable,
            ]);
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

