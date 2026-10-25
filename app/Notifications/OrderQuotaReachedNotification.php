<?php

namespace App\Notifications;

use App\Models\Restaurant;
use App\Notifications\Channels\PushChannel;
use Illuminate\Notifications\Notification;

/**
 * Le restaurant atteint 80 % / 100 % de son quota mensuel de commandes.
 * Information uniquement : ses commandes continuent d'arriver normalement.
 * Envoyée une seule fois par mois et par palier (OrderQuotaMonitor).
 */
class OrderQuotaReachedNotification extends Notification
{
    public function __construct(
        protected Restaurant $restaurant,
        protected int $threshold,
        protected int $ordersCount,
        protected int $ordersLimit,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', PushChannel::class];
    }

    public function toPush(object $notifiable): array
    {
        return [
            'title' => $this->title(),
            'body'  => $this->message(),
            'data'  => [
                'type'      => 'order_quota_reached',
                'threshold' => (string) $this->threshold,
            ],
            'link' => route('restaurant.subscription.plans'),
            'tag'  => 'order-quota-' . now()->format('Y-m') . '-' . $this->threshold,
        ];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'          => 'order_quota_reached',
            'restaurant_id' => $this->restaurant->id,
            'threshold'     => $this->threshold,
            'orders_count'  => $this->ordersCount,
            'orders_limit'  => $this->ordersLimit,
            'title'         => $this->title(),
            'message'       => $this->message(),
            'url'           => route('restaurant.subscription.plans'),
        ];
    }

    private function title(): string
    {
        return $this->threshold >= 100
            ? '🚀 Quota de commandes dépassé ce mois-ci'
            : '📈 Vous approchez de votre quota de commandes';
    }

    private function message(): string
    {
        $count = number_format($this->ordersCount, 0, ',', ' ');
        $limit = number_format($this->ordersLimit, 0, ',', ' ');

        return $this->threshold >= 100
            ? "Bravo, {$count} commandes ce mois-ci (quota : {$limit}). Vos commandes continuent d'arriver normalement : passez au plan supérieur pour suivre votre croissance."
            : "{$count} commandes sur {$limit} ce mois-ci. Aucune commande ne sera bloquée, mais le plan supérieur est fait pour vous.";
    }
}
