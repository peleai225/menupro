<?php

namespace App\Notifications;

use App\Models\CommandoAgent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
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
        return ['database'];
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
