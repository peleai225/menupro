<?php
namespace App\Notifications\Crm;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class WithdrawalStatusNotification extends Notification
{
    use Queueable;

    public function __construct(
        private $withdrawal,
        private string $status
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $amount = number_format($this->withdrawal->amount_cents / 100, 0, ',', ' ');
        $messages = [
            'approved' => [
                'title'   => 'Retrait approuvé',
                'message' => "Votre demande de retrait de {$amount} FCFA a été approuvée. Paiement en cours.",
            ],
            'paid' => [
                'title'   => 'Retrait payé',
                'message' => "Votre retrait de {$amount} FCFA a été effectué."
                    . ($this->withdrawal->payment_reference ? " Réf: {$this->withdrawal->payment_reference}" : ''),
            ],
            'rejected' => [
                'title'   => 'Retrait refusé',
                'message' => "Votre demande de retrait de {$amount} FCFA a été refusée."
                    . ($this->withdrawal->rejection_reason ? " Motif: {$this->withdrawal->rejection_reason}" : ''),
            ],
        ];

        $msg = $messages[$this->status] ?? [
            'title'   => 'Mise à jour retrait',
            'message' => "Statut de votre retrait : {$this->status}",
        ];

        return array_merge($msg, [
            'type'        => 'withdrawal_' . $this->status,
            'amount_fcfa' => $this->withdrawal->amount_cents / 100,
            'withdrawal_id' => $this->withdrawal->id,
        ]);
    }
}
