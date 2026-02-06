<?php

namespace App\Notifications;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReservationReceivedNotification extends Notification
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
            ->subject("Demande de réservation reçue - {$restaurant->name}")
            ->greeting("Bonjour {$reservation->customer_name},")
            ->line("Nous avons bien reçu votre demande de réservation.")
            ->line("**Restaurant :** {$restaurant->name}")
            ->line("**Date demandée :** {$reservation->reservation_date->locale('fr')->isoFormat('dddd D MMMM YYYY [à] HH:mm')}")
            ->line("**Nombre de personnes :** {$reservation->number_of_guests}")
            ->when($reservation->special_requests, function ($mail) use ($reservation) {
                return $mail->line("**Demandes spéciales :** {$reservation->special_requests}");
            })
            ->line("Votre réservation est actuellement **en attente de confirmation**.")
            ->line("Vous recevrez un email dès que le restaurant aura confirmé votre réservation.")
            ->when($restaurant->phone, function ($mail) use ($restaurant) {
                return $mail->line("Pour toute question, contactez le restaurant au {$restaurant->phone}.");
            })
            ->salutation("À bientôt,\nL'équipe MenuPro");
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'reservation_received',
            'reservation_id' => $this->reservation->id,
            'restaurant_name' => $this->reservation->restaurant->name,
            'reservation_date' => $this->reservation->reservation_date->toIso8601String(),
            'message' => "Votre demande de réservation pour le {$this->reservation->reservation_date->format('d/m/Y à H:i')} a été reçue.",
        ];
    }
}
