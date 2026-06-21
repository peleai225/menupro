<?php

namespace App\Events;

use App\Models\Order;
use Illuminate\Broadcasting\Channel;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Contracts\Broadcasting\ShouldBroadcast;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

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
        return [
            new Channel("restaurant.{$this->order->restaurant_id}.orders"),
        ];
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
