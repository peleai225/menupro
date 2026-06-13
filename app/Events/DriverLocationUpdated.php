<?php

namespace App\Events;

use App\Models\Delivery;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class DriverLocationUpdated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public int $deliveryId,
        public float $latitude,
        public float $longitude,
        public string $driverName,
        public string $status,
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel("delivery.{$this->deliveryId}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'driver.location';
    }

    public function broadcastWith(): array
    {
        return [
            'lat' => $this->latitude,
            'lng' => $this->longitude,
            'driver' => $this->driverName,
            'status' => $this->status,
        ];
    }
}
