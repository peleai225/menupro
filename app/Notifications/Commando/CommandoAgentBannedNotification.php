<?php

namespace App\Notifications\Commando;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\CommandoAgent;

class CommandoAgentBannedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private CommandoAgent $agent,
        private ?string $reason = null
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'account_banned',
            'title' => 'Compte suspendu',
            'message' => 'Votre compte agent a été suspendu.' . ($this->reason ? ' Motif : ' . $this->reason : ''),
            'reason' => $this->reason,
        ];
    }
}
