<?php

namespace App\Notifications;

use App\Models\Order;
use App\Notifications\Channels\PushChannel;
use App\Support\OrderActionLink;
use Illuminate\Notifications\Notification;

/**
 * Relance du personnel : une commande attend toujours d'être acceptée ou refusée.
 * Push uniquement (la cloche a déjà la commande ; l'écran des commandes sonne en boucle).
 */
class OrderAwaitingAcceptanceNotification extends Notification
{
    public function __construct(
        protected Order $order,
        protected int $minutes,
    ) {}

    public function via(object $notifiable): array
    {
        return [PushChannel::class];
    }

    public function toPush(object $notifiable): array
    {
        $total = number_format($this->order->total, 0, ',', ' ');

        return [
            'title' => "⏰ Commande #{$this->order->reference} en attente depuis {$this->minutes} min",
            'body'  => "{$this->order->customer_name} · {$total} F CFA. Touchez pour accepter ou refuser.",
            'data'  => [
                'type'     => 'order_awaiting_acceptance',
                'order_id' => (string) $this->order->id,
            ],
            'link'                => OrderActionLink::for($this->order),
            'tag'                 => 'order-' . $this->order->id,
            'require_interaction' => true,
            'ttl'                 => 1800,
        ];
    }
}
