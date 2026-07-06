<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Restaurant;

class OrderStatusChanged implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Order $order,
        public string $oldStatus,
        public string $newStatus
    ) {}

    public function broadcastOn(): array
    {
        $channels = [
            new PrivateChannel("restaurant.{$this->order->restaurant_id}.orders"),
        ];

        // Canal public pour le KDS cuisine (sécurisé par token URL, pas de session auth)
        $kitchenToken = $this->order->relationLoaded('restaurant')
            ? $this->order->restaurant?->kitchen_token
            : Restaurant::find($this->order->restaurant_id)?->kitchen_token;

        if ($kitchenToken) {
            $channels[] = new Channel("kitchen.{$kitchenToken}");
        }

        return $channels;
    }

    public function broadcastAs(): string
    {
        return 'order.status_changed';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->order->id,
            'reference' => $this->order->reference,
            'old_status' => $this->oldStatus,
            'new_status' => $this->newStatus,
            'status_label' => $this->order->status->label(),
            'customer_name' => $this->order->customer_name,
        ];
    }
}
