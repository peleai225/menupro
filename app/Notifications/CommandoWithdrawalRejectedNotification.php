<?php

namespace App\Notifications;

use App\Models\CommandoAgent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class CommandoWithdrawalRejectedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected CommandoAgent $agent,
        protected int $amountCents,
        protected ?string $reason = null
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'commando_withdrawal_rejected',
            'agent_id' => $this->agent->id,
            'amount_cents' => $this->amountCents,
            'reason' => $this->reason,
            'message' => 'Votre demande de retrait n\'a pas été retenue.',
            'url' => route('commando.dashboard') . '#wallet',
        ];
    }
}
