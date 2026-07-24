<?php

namespace App\Events;

use App\Models\Ingredient;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Broadcasting\PrivateChannel;
use Illuminate\Contracts\Broadcasting\ShouldBroadcastNow;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class LowStockAlert implements ShouldBroadcastNow
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public function __construct(
        public Ingredient $ingredient
    ) {}

    public function broadcastOn(): array
    {
        return [
            new PrivateChannel("restaurant.{$this->ingredient->restaurant_id}"),
        ];
    }

    public function broadcastAs(): string
    {
        return 'stock.low_stock_alert';
    }

    public function broadcastWith(): array
    {
        return [
            'ingredient_id'    => $this->ingredient->id,
            'ingredient_name'  => $this->ingredient->name,
            'current_quantity' => (float) $this->ingredient->current_quantity,
            'min_quantity'     => (float) $this->ingredient->min_quantity,
            'unit'             => $this->ingredient->unit?->value,
        ];
    }
}
