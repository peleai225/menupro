<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;
use App\Models\Restaurant;

class OrderCreated implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Order $order
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
        return 'order.created';
    }

    public function broadcastWith(): array
    {
        return [
            'id' => $this->order->id,
            'reference' => $this->order->reference,
            'customer_name' => $this->order->customer_name,
            'status' => $this->order->status->value,
            'status_label' => $this->order->status->label(),
            'total' => $this->order->total,
            'type' => $this->order->type?->value,
            'items_count' => $this->order->items->count(),
            'created_at' => $this->order->created_at->toIso8601String(),
        ];
    }
}
