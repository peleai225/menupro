<?php

namespace App\Notifications;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ReservationCancelledNotification extends Notification
{
    use Queueable;

    public function __construct(
        protected Reservation $reservation,
        protected ?string $reason = null
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $reservation = $this->reservation;
        $restaurant = $reservation->restaurant;

        $mail = (new MailMessage)
            ->subject("Réservation annulée - {$restaurant->name}")
            ->greeting("Bonjour {$reservation->customer_name},")
            ->line("Nous sommes désolés de vous informer que votre réservation a été **annulée**.")
            ->line("**Restaurant :** {$restaurant->name}")
            ->line("**Date prévue :** {$reservation->reservation_date->locale('fr')->isoFormat('dddd D MMMM YYYY [à] HH:mm')}")
            ->line("**Nombre de personnes :** {$reservation->number_of_guests}");

        if ($this->reason) {
            $mail->line("**Raison :** {$this->reason}");
        }

        $mail->line("N'hésitez pas à effectuer une nouvelle réservation pour une autre date.")
            ->when($restaurant->phone, function ($m) use ($restaurant) {
                return $m->line("Pour toute question, contactez-nous au {$restaurant->phone}.");
            })
            ->salutation("Cordialement,\nL'équipe {$restaurant->name}");

        return $mail;
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'reservation_cancelled',
            'reservation_id' => $this->reservation->id,
            'restaurant_name' => $this->reservation->restaurant->name,
            'reservation_date' => $this->reservation->reservation_date->toIso8601String(),
            'reason' => $this->reason,
            'message' => "Votre réservation pour le {$this->reservation->reservation_date->format('d/m/Y à H:i')} a été annulée.",
        ];
    }
}
