<?php

namespace App\Events;

use App\Models\Delivery;
use App\Models\DeliveryDriver;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DriverAssigned implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Delivery $delivery,
        public DeliveryDriver $driver,
    ) {}

    public function broadcastOn(): array
    {
        return [
            // Client suit sa commande
            new Channel("order.{$this->delivery->order->tracking_token}"),
            // Restaurant voit le livreur assigné
            new PrivateChannel("restaurant.{$this->delivery->restaurant_id}.deliveries"),
            // Le livreur reçoit la notification de course
            new PrivateChannel("driver.{$this->driver->id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'driver.assigned';
    }

    public function broadcastWith(): array
    {
        return [
            'delivery_id'   => $this->delivery->id,
            'order_ref'     => $this->delivery->order->reference,
            'driver' => [
                'id'       => $this->driver->id,
                'name'     => $this->driver->name,
                'phone'    => $this->driver->phone,
                'vehicle'  => $this->driver->vehicle_type,
                'rating'   => $this->driver->rating,
                'lat'      => $this->driver->latitude,
                'lng'      => $this->driver->longitude,
            ],
            'pickup_address'   => $this->delivery->restaurant->address ?? '',
            'delivery_address' => $this->delivery->delivery_address,
            'estimated_minutes' => $this->delivery->estimated_minutes,
            'assigned_at'      => $this->delivery->assigned_at,
        ];
    }
}
