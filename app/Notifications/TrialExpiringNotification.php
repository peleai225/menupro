<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TrialExpiringNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Subscription $subscription,
        protected int $daysLeft
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $restaurant = $this->subscription->restaurant;
        $trialEndsAt = $this->subscription->ends_at->locale('fr')->isoFormat('dddd D MMMM YYYY [à] HH:mm');

        $message = (new MailMessage)
            ->subject("⏰ Votre essai gratuit expire dans {$this->daysLeft} jour(s)")
            ->greeting("Bonjour {$notifiable->name},")
            ->line("Votre essai gratuit de MenuPro pour **{$restaurant->name}** expire dans **{$this->daysLeft} jour(s)**.")
            ->line("**Date d'expiration :** {$trialEndsAt}")
            ->line("Pour continuer à profiter de toutes les fonctionnalités de MenuPro, souscrivez à un abonnement dès maintenant.");

        if ($this->daysLeft === 1) {
            $message->line("⚠️ **Attention :** Votre essai expire demain ! N'attendez pas pour souscrire.");
        }

        return $message
            ->action('Souscrire maintenant', route('restaurant.subscription'))
            ->line("Ne manquez pas cette opportunité de continuer à développer votre activité avec MenuPro !")
            ->salutation('L\'équipe MenuPro');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'trial_expiring',
            'subscription_id' => $this->subscription->id,
            'restaurant_id' => $this->subscription->restaurant_id,
            'days_left' => $this->daysLeft,
            'trial_ends_at' => $this->subscription->ends_at->toIso8601String(),
            'message' => "Votre essai gratuit expire dans {$this->daysLeft} jour(s).",
        ];
    }
}
