<?php

namespace App\Notifications;

use App\Models\CommandoAgent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CommandoAgentRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected CommandoAgent $agent,
        protected ?string $reason = null
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $mail = (new MailMessage)
            ->subject('[MenuPro Commando] Votre dossier n\'a pas été retenu')
            ->greeting('Bonjour ' . $this->agent->first_name . ',')
            ->line('Après examen, votre dossier agent Commando n\'a pas été retenu.');

        if ($this->reason) {
            $mail->line('**Motif :** ' . $this->reason);
        }

        return $mail->line('Pour toute question, contactez le support MenuPro.');
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'commando_agent_rejected',
            'agent_id' => $this->agent->id,
            'reason' => $this->reason,
            'message' => 'Votre dossier agent n\'a pas été retenu.',
        ];
    }
}
