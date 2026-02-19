<?php

namespace App\Notifications;

use App\Models\CommandoAgent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CommandoWithdrawalRequestNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        protected CommandoAgent $agent,
        protected int $amountCents
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $amount = number_format($this->amountCents / 100, 0, ',', ' ') . ' FCFA';
        return (new MailMessage)
            ->subject('[MenuPro Commando] Demande de retrait - ' . $this->agent->full_name)
            ->line("L'agent **{$this->agent->full_name}** (Badge {$this->agent->badge_id_display}) demande un retrait de **{$amount}**.")
            ->action('Voir la demande', route('super-admin.commando.agents.show', $this->agent));
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'commando_withdrawal_request',
            'agent_id' => $this->agent->id,
            'agent_name' => $this->agent->full_name,
            'badge_id' => $this->agent->badge_id_display,
            'amount_cents' => $this->amountCents,
            'url' => route('super-admin.commando.agents.show', $this->agent),
        ];
    }
}
