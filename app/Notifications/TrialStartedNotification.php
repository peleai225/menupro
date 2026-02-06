<?php

namespace App\Notifications;

use App\Models\Subscription;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TrialStartedNotification extends Notification implements ShouldQueue
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
        $trialDays = $this->subscription->trial_days ?? 14;
        $trialEndsAt = $this->subscription->ends_at->locale('fr')->isoFormat('dddd D MMMM YYYY [à] HH:mm');

        return (new MailMessage)
            ->subject("🎉 Bienvenue sur MenuPro - Essai gratuit de {$trialDays} jours")
            ->greeting("Bienvenue {$notifiable->name} !")
            ->line("Votre compte **{$restaurant->name}** a été créé avec succès !")
            ->line("**Votre essai gratuit de {$trialDays} jours a commencé.**")
            ->line("Vous avez accès à toutes les fonctionnalités de MenuPro jusqu'au **{$trialEndsAt}**.")
            ->line("**Fonctionnalités disponibles :**")
            ->line("✅ Gestion complète du menu")
            ->line("✅ Gestion des commandes en temps réel")
            ->line("✅ Système de paiement intégré")
            ->line("✅ Gestion du stock (selon votre plan)")
            ->line("✅ Statistiques et rapports")
            ->line("✅ Support client dédié")
            ->line("")
            ->line("**N'oubliez pas :**")
            ->line("Pour continuer à utiliser MenuPro après votre essai, vous devrez souscrire à un abonnement. Vous pouvez le faire à tout moment depuis votre tableau de bord.")
            ->action('Accéder à mon tableau de bord', route('restaurant.dashboard'))
            ->line("Besoin d'aide ? N'hésitez pas à nous contacter !")
            ->salutation('L\'équipe MenuPro');
    }

    public function toArray(object $notifiable): array
    {
        $restaurant = $this->subscription->restaurant;
        return [
            'type' => 'trial_started',
            'restaurant_id' => $restaurant->id,
            'restaurant_name' => $restaurant->name,
            'subscription_id' => $this->subscription->id,
            'trial_days' => $this->subscription->trial_days ?? 14,
            'trial_ends_at' => $this->subscription->ends_at->toIso8601String(),
            'message' => "Votre essai gratuit de {$this->subscription->trial_days} jours a commencé !",
        ];
    }
}
