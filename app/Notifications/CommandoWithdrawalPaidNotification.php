<?php

namespace App\Notifications;

use App\Models\CommandoAgent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
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
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $amount = number_format($this->amountCents / 100, 0, ',', ' ') . ' FCFA';
        return (new MailMessage)
            ->subject('[MenuPro Commando] Votre retrait a été effectué')
            ->greeting('Bonjour ' . $this->agent->first_name . ',')
            ->line('Votre demande de retrait de **' . $amount . '** a été traitée et le paiement a été effectué.')
            ->action('Voir mon portefeuille', route('commando.dashboard') . '#wallet');
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
