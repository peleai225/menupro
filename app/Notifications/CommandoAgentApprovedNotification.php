<?php

namespace App\Notifications;

use App\Models\CommandoAgent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CommandoAgentApprovedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(protected CommandoAgent $agent) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('[MenuPro Commando] Votre dossier est validé')
            ->greeting('Bonjour ' . $this->agent->first_name . ',')
            ->line('Votre dossier agent Commando a été **validé**. Vous avez désormais accès à votre carte digitale, au lien de parrainage et au portefeuille de commissions.')
            ->action('Accéder à mon centre d\'opérations', route('commando.dashboard'));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'commando_agent_approved',
            'agent_id' => $this->agent->id,
            'message' => 'Votre dossier agent a été validé. Accédez à votre carte et au parrainage.',
            'url' => route('commando.dashboard'),
        ];
    }
}
