<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class OrderCreated implements ShouldBroadcast
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Order $order
    ) {}

    public function broadcastOn(): array
    {
        return [
            new Channel("restaurant.{$this->order->restaurant_id}.orders"),
        ];
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
