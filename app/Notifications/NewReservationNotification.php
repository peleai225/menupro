<?php

namespace App\Notifications;

use App\Models\Reservation;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class NewReservationNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Reservation $reservation
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $reservation = $this->reservation;
        $restaurant = $reservation->restaurant;

        return (new MailMessage)
            ->subject("📅 Nouvelle réservation - {$restaurant->name}")
            ->greeting("Nouvelle réservation !")
            ->line("Vous avez reçu une nouvelle réservation pour **{$restaurant->name}**.")
            ->line("**Client :** {$reservation->customer_name}")
            ->line("**Email :** {$reservation->customer_email}")
            ->line("**Téléphone :** {$reservation->customer_phone}")
            ->line("**Date :** {$reservation->reservation_date->format('d/m/Y à H:i')}")
            ->line("**Nombre de personnes :** {$reservation->number_of_guests}")
            ->when($reservation->special_requests, function ($mail) use ($reservation) {
                return $mail->line("**Demandes spéciales :**")
                    ->line($reservation->special_requests);
            })
            ->action('Voir la réservation', route('restaurant.reservations.show', $reservation))
            ->salutation('MenuPro');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'new_reservation',
            'reservation_id' => $this->reservation->id,
            'customer_name' => $this->reservation->customer_name,
            'customer_email' => $this->reservation->customer_email,
            'reservation_date' => $this->reservation->reservation_date->toIso8601String(),
            'number_of_guests' => $this->reservation->number_of_guests,
            'message' => "Nouvelle réservation de {$this->reservation->customer_name} pour le {$this->reservation->reservation_date->format('d/m/Y à H:i')} ({$this->reservation->number_of_guests} personne" . ($this->reservation->number_of_guests > 1 ? 's' : '') . ").",
        ];
    }
}

