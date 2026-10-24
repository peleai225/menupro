<?php

namespace App\Notifications;

use App\Models\CommandoAgent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class CommandoWithdrawalPaidNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected CommandoAgent $agent,
        protected int $amountCents
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', PushChannel::class];
    }

    public function toPush(object $notifiable): array
    {
        $amount = number_format($this->amountCents / 100, 0, ',', ' ');

        return [
            'title' => '💸 Retrait effectué',
            'body'  => "Votre retrait de {$amount} FCFA a été payé.",
            'data'  => ['type' => 'commando_withdrawal_paid'],
            'link'  => route('commando.dashboard') . '#wallet',
            'tag'   => 'commando-withdrawal-' . $this->agent->id,
        ];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'commando_withdrawal_paid',
            'agent_id' => $this->agent->id,
            'amount_cents' => $this->amountCents,
            'message' => 'Votre retrait a été effectué.',
            'url' => route('commando.dashboard') . '#wallet',
        ];
    }
}
