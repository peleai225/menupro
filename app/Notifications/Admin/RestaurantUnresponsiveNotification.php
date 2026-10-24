<?php

namespace App\Notifications\Admin;

use App\Models\Order;
use App\Notifications\Channels\PushChannel;
use Illuminate\Notifications\Notification;

/**
 * Le restaurant ne répond pas à une commande (ni acceptée ni refusée après plusieurs relances) :
 * un admin doit le joindre. Pas d'annulation automatique, l'alerte reste dans la cloche.
 */
class RestaurantUnresponsiveNotification extends Notification
{
    public function __construct(
        protected Order $order,
        protected int $minutes,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database', PushChannel::class];
    }

    public function toPush(object $notifiable): array
    {
        return [
            'title' => '🚨 ' . $this->restaurantName() . ' ne répond pas',
            'body'  => "Commande #{$this->order->reference} en attente depuis {$this->minutes} min. Appelez le restaurant.",
            'data'  => [
                'type'     => 'restaurant_unresponsive',
                'order_id' => (string) $this->order->id,
            ],
            'link'                => route('super-admin.orders.show', $this->order->id),
            'tag'                 => 'unresponsive-' . $this->order->id,
            'require_interaction' => true,
        ];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type'            => 'restaurant_unresponsive',
            'order_id'        => $this->order->id,
            'order_reference' => $this->order->reference,
            'restaurant_id'   => $this->order->restaurant_id,
            'restaurant_name' => $this->restaurantName(),
            'restaurant_phone' => $this->order->restaurant?->phone,
            'minutes'         => $this->minutes,
            'message'         => 'Restaurant ' . $this->restaurantName() . " ne répond pas à la commande #{$this->order->reference}",
            'url'             => route('super-admin.orders.show', $this->order->id),
        ];
    }

    private function restaurantName(): string
    {
        return $this->order->restaurant?->name ?? 'Un restaurant';
    }
}
