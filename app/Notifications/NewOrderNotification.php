<?php

namespace App\Notifications;

use App\Models\Order;
use Illuminate\Notifications\Notification;

class NewOrderNotification extends Notification
{
    public function __construct(
        protected Order $order
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'new_order',
            'order_id' => $this->order->id,
            'order_reference' => $this->order->reference,
            'customer_name' => $this->order->customer_name,
            'total' => $this->order->total,
            'items_count' => $this->order->items_count,
            'order_type' => $this->order->type->value,
            'message' => "Nouvelle commande #{$this->order->reference} de {$this->order->customer_name}.",
        ];
    }
}
