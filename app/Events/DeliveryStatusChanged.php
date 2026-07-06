<?php

namespace App\Events;

use App\Enums\DeliveryStatus;
use App\Models\Delivery;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DeliveryStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Delivery $delivery,
        public string $oldStatus,
        public string $newStatus,
    ) {}

    public function broadcastOn(): array
    {
        return [
            // Canal suivi client (par tracking_token)
            new Channel("order.{$this->delivery->order->tracking_token}"),
            // Canal restaurant
            new PrivateChannel("restaurant.{$this->delivery->restaurant_id}.deliveries"),
            // Canal livreur
            new PrivateChannel("driver.{$this->delivery->driver_id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'delivery.status_changed';
    }

    public function broadcastWith(): array
    {
        $driver = $this->delivery->driver;

        return [
            'delivery_id'  => $this->delivery->id,
            'order_ref'    => $this->delivery->order->reference,
            'old_status'   => $this->oldStatus,
            'new_status'   => $this->newStatus,
            'status_label' => DeliveryStatus::from($this->newStatus)->label(),
            'driver'       => $driver ? [
                'name'     => $driver->name,
                'phone'    => $driver->phone,
                'lat'      => $driver->latitude,
                'lng'      => $driver->longitude,
                'vehicle'  => $driver->vehicle_type,
                'rating'   => $driver->rating,
            ] : null,
            'estimated_minutes' => $this->delivery->estimated_minutes,
            'picked_up_at'      => $this->delivery->picked_up_at,
            'delivered_at'      => $this->delivery->delivered_at,
        ];
    }
}
