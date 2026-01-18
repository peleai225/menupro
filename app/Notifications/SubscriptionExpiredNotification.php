<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SubscriptionExpiredNotification extends Notification implements ShouldQueue
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

        return (new MailMessage)
            ->subject('⚠️ Votre abonnement MenuPro a expiré')
            ->greeting("Bonjour {$notifiable->first_name},")
            ->line("Votre abonnement au plan **{$plan->name}** pour le restaurant **{$restaurant->name}** a expiré.")
            ->line('Votre restaurant est maintenant suspendu et ne peut plus accepter de commandes.')
            ->line('Pour réactiver votre restaurant, veuillez renouveler votre abonnement.')
            ->action('Renouveler maintenant', route('restaurant.subscription'))
            ->line('Si vous avez des questions, n\'hésitez pas à nous contacter.')
            ->salutation('L\'équipe MenuPro');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'subscription_expired',
            'subscription_id' => $this->subscription->id,
            'restaurant_id' => $this->subscription->restaurant_id,
            'plan_name' => $this->subscription->plan->name,
            'expired_at' => $this->subscription->ends_at->toISOString(),
            'message' => 'Votre abonnement a expiré. Renouvelez pour continuer.',
        ];
    }
}

