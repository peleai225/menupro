<?php

namespace App\Notifications;

use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class TeamInvitationNotification extends Notification
{
    public function __construct(
        public Restaurant $restaurant,
        public string $temporaryPassword
    ) {}

    /**
     * Get the notification's delivery channels.
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        $loginUrl = route('login');
        $roleName = $notifiable->role->value === 'restaurant_admin' ? 'Administrateur' : 'Employé';

        return (new MailMessage)
            ->subject("Invitation à rejoindre l'équipe de {$this->restaurant->name}")
            ->greeting("Bonjour {$notifiable->name},")
            ->line("Vous avez été invité(e) à rejoindre l'équipe de **{$this->restaurant->name}** sur MenuPro.")
            ->line("Votre rôle : **{$roleName}**")
            ->line('Voici vos identifiants de connexion :')
            ->line("**Email :** {$notifiable->email}")
            ->line("**Mot de passe temporaire :** {$this->temporaryPassword}")
            ->action('Se connecter', $loginUrl)
            ->line('Pour des raisons de sécurité, nous vous recommandons de changer votre mot de passe après votre première connexion.')
            ->salutation("Cordialement,\nL'équipe MenuPro");
    }

    /**
     * Get the array representation of the notification.
     */
    public function toArray(object $notifiable): array
    {
        return [
            'restaurant_id' => $this->restaurant->id,
            'restaurant_name' => $this->restaurant->name,
            'message' => "Invitation à rejoindre l'équipe de {$this->restaurant->name}",
        ];
    }
}
