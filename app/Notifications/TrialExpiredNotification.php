<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TrialExpiredNotification extends Notification implements ShouldQueue
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

        return (new MailMessage)
            ->subject("🔒 Votre essai gratuit a expiré")
            ->greeting("Bonjour {$notifiable->name},")
            ->line("Votre essai gratuit de MenuPro pour **{$restaurant->name}** a expiré.")
            ->line("**Votre compte est maintenant limité.**")
            ->line("Pour continuer à utiliser MenuPro et accéder à toutes les fonctionnalités, veuillez souscrire à un abonnement.")
            ->line("**Fonctionnalités bloquées :**")
            ->line("❌ Réception de nouvelles commandes")
            ->line("❌ Accès au tableau de bord complet")
            ->line("❌ Gestion des commandes")
            ->line("")
            ->line("**Souscrivez maintenant pour :**")
            ->line("✅ Débloquer toutes les fonctionnalités")
            ->line("✅ Continuer à recevoir des commandes")
            ->line("✅ Accéder à vos statistiques")
            ->line("✅ Profiter du support client")
            ->action('Souscrire maintenant', route('restaurant.subscription'))
            ->line("Nous espérons vous revoir bientôt !")
            ->salutation('L\'équipe MenuPro');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'trial_expired',
            'subscription_id' => $this->subscription->id,
            'restaurant_id' => $this->subscription->restaurant_id,
            'message' => 'Votre essai gratuit a expiré. Veuillez souscrire à un abonnement pour continuer.',
        ];
    }
}
