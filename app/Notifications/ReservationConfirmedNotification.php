<?php

namespace App\Notifications;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReservationConfirmedNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected Reservation $reservation
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $reservation = $this->reservation;
        $restaurant = $reservation->restaurant;

        return (new MailMessage)
            ->subject("Réservation confirmée - {$restaurant->name}")
            ->greeting("Bonjour {$reservation->customer_name},")
            ->line("Bonne nouvelle ! Votre réservation a été **confirmée**.")
            ->line("**Restaurant :** {$restaurant->name}")
            ->line("**Date :** {$reservation->reservation_date->locale('fr')->isoFormat('dddd D MMMM YYYY [à] HH:mm')}")
            ->line("**Nombre de personnes :** {$reservation->number_of_guests}")
            ->when($restaurant->address, function ($mail) use ($restaurant) {
                return $mail->line("**Adresse :** {$restaurant->address}, {$restaurant->city}");
            })
            ->when($restaurant->phone, function ($mail) use ($restaurant) {
                return $mail->line("**Téléphone :** {$restaurant->phone}");
            })
            ->line("Nous avons hâte de vous accueillir !")
            ->salutation("À bientôt,\nL'équipe {$restaurant->name}");
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'reservation_confirmed',
            'reservation_id' => $this->reservation->id,
            'restaurant_name' => $this->reservation->restaurant->name,
            'reservation_date' => $this->reservation->reservation_date->toIso8601String(),
            'message' => "Votre réservation pour le {$this->reservation->reservation_date->format('d/m/Y à H:i')} a été confirmée.",
        ];
    }
}
