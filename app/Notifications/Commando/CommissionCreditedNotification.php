<?php

namespace App\Notifications\Commando;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;
use App\Models\CommandoAgent;

class CommissionCreditedNotification extends Notification
{
    use Queueable;

    public function __construct(
        private CommandoAgent $agent,
        private int $amountCents,
        private string $restaurantName
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        $amount = number_format($this->amountCents / 100, 0, ',', ' ');
        return [
            'type' => 'commission_credited',
            'title' => 'Commission créditée',
            'message' => "{$amount} FCFA crédités pour le restaurant {$this->restaurantName}",
            'amount_fcfa' => $this->amountCents / 100,
            'restaurant_name' => $this->restaurantName,
        ];
    }
}
