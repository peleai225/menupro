<?php

namespace App\Notifications;

use App\Models\CommandoAgent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
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
        return ['database', 'mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $amount = number_format($this->amountCents / 100, 0, ',', ' ') . ' FCFA';
        $mail = (new MailMessage)
            ->subject('[MenuPro Commando] Demande de retrait non retenue')
            ->greeting('Bonjour ' . $this->agent->first_name . ',')
            ->line('Votre demande de retrait de **' . $amount . '** n\'a pas été retenue. Le montant a été réintégré à votre solde.');

        if ($this->reason) {
            $mail->line('**Motif :** ' . $this->reason);
        }

        return $mail->action('Voir mon portefeuille', route('commando.dashboard') . '#wallet');
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
