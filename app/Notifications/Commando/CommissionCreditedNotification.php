<?php

namespace App\Notifications\Commando;

use App\Notifications\Channels\PushChannel;
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
        return ['database', PushChannel::class];
    }

    public function toPush(object $notifiable): array
    {
        $amount = number_format($this->amountCents / 100, 0, ',', ' ');

        return [
            'title' => "💰 Commission créditée : {$amount} FCFA",
            'body'  => "Pour le restaurant {$this->restaurantName}.",
            'data'  => ['type' => 'commission_credited'],
            'link'  => route('commando.dashboard') . '#wallet',
        ];
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
