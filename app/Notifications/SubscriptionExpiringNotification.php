<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionExpiringNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected Subscription $subscription
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', 'database'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $restaurant = $this->subscription->restaurant;
        $plan = $this->subscription->plan;
        $daysRemaining = $this->subscription->days_remaining;

        return (new MailMessage)
            ->subject("⏰ Votre abonnement expire dans {$daysRemaining} jour(s)")
            ->greeting("Bonjour {$notifiable->first_name},")
            ->line("Votre abonnement au plan **{$plan->name}** pour **{$restaurant->name}** expire dans **{$daysRemaining} jour(s)**.")
            ->line('Pour éviter toute interruption de service, nous vous recommandons de renouveler votre abonnement dès maintenant.')
            ->action('Renouveler mon abonnement', route('restaurant.subscription'))
            ->line('Continuez à profiter de toutes les fonctionnalités de MenuPro !')
            ->salutation('L\'équipe MenuPro');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'subscription_expiring',
            'subscription_id' => $this->subscription->id,
            'restaurant_id' => $this->subscription->restaurant_id,
            'plan_name' => $this->subscription->plan->name,
            'expires_at' => $this->subscription->ends_at->toISOString(),
            'days_remaining' => $this->subscription->days_remaining,
            'message' => "Votre abonnement expire dans {$this->subscription->days_remaining} jour(s).",
        ];
    }
}

