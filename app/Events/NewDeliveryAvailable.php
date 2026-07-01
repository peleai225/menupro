<?php

namespace App\Events;

use App\Models\Delivery;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class NewDeliveryAvailable implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Delivery $delivery,
        public string $city,
    ) {}

    public function broadcastOn(): array
    {
        // Canal par ville — tous les livreurs en ligne dans cette ville écoutent
        return [
            new Channel("drivers.city.{$this->city}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'delivery.available';
    }

    public function broadcastWith(): array
    {
        $order = $this->delivery->order;

        return [
            'delivery_id'      => $this->delivery->id,
            'restaurant_name'  => $this->delivery->restaurant->name ?? '',
            'pickup_address'   => $this->delivery->restaurant->address ?? '',
            'pickup_lat'       => $this->delivery->pickup_latitude,
            'pickup_lng'       => $this->delivery->pickup_longitude,
            'delivery_address' => $this->delivery->delivery_address,
            'delivery_lat'     => $this->delivery->delivery_latitude,
            'delivery_lng'     => $this->delivery->delivery_longitude,
            'delivery_fee'     => $order->delivery_fee,
            'driver_earning'   => (int) round($order->delivery_fee * 0.80),
            'items_count'      => $order->items()->count(),
            'estimated_minutes' => $this->delivery->estimated_minutes,
            'city'             => $this->city,
        ];
    }
}
