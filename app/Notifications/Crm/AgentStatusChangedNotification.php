<?php

namespace App\Notifications\Crm;

use App\Enums\Crm\AgentStatus;
use Illuminate\Notifications\Notification;

class AgentStatusChangedNotification extends Notification
{
    public function __construct(
        public AgentStatus $newStatus,
        public ?string $message = null,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'       => 'status_changed',
            'icon'       => 'user',
            'title'      => 'Statut mis à jour',
            'body'       => $this->message ?? "Votre statut est maintenant : {$this->newStatus->label()}",
            'new_status' => $this->newStatus->value,
        ];
    }
}
