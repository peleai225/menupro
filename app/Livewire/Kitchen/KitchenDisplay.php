<?php

namespace App\Livewire\Kitchen;

use App\Enums\OrderStatus;
use App\Models\Order;
use App\Models\Restaurant;
use App\Services\StockManager;
use Livewire\Attributes\Locked;
use Livewire\Component;

class KitchenDisplay extends Component
{
    #[Locked]
    public string $token;

    #[Locked]
    public int $restaurantId;

    public string $restaurantName = '';

    public array $orders = [];

    public function mount(string $token): void
    {
        $restaurant = Restaurant::where('kitchen_token', $token)->firstOrFail();
        $this->token        = $token;
        $this->restaurantId = $restaurant->id;
        $this->restaurantName = $restaurant->name;
        $this->loadOrders();
    }

    public function loadOrders(): void
    {
        $rows = Order::withoutGlobalScope('restaurant')
            ->where('restaurant_id', $this->restaurantId)
            ->whereIn('status', [
                OrderStatus::PAID,
                OrderStatus::CONFIRMED,
                OrderStatus::PREPARING,
                OrderStatus::READY,
            ])
            ->with('items.dish')
            ->oldest()
            ->get();

        $this->orders = $rows->map(fn($o) => $this->serialize($o))->values()->all();
    }

    public function confirm(int $orderId): void  { $this->transition($orderId, 'confirm'); }
    public function prepare(int $orderId): void  { $this->transition($orderId, 'prepare'); }
    public function ready(int $orderId): void    { $this->transition($orderId, 'ready'); }

    private function transition(int $orderId, string $action): void
    {
        $order = Order::withoutGlobalScope('restaurant')->findOrFail($orderId);

        if ((int) $order->restaurant_id !== $this->restaurantId) {
            return;
        }

        $newStatus = match ($action) {
            'confirm' => OrderStatus::CONFIRMED,
            'prepare' => OrderStatus::PREPARING,
            'ready'   => OrderStatus::READY,
            default   => null,
        };

        if (!$newStatus || !$order->status->canTransitionTo($newStatus)) {
            return;
        }

        if ($newStatus === OrderStatus::CONFIRMED
            && $order->status === OrderStatus::PAID
        ) {
            $restaurant = Restaurant::find($this->restaurantId);
            if ($restaurant?->hasFeature('stock')) {
                app(StockManager::class)->forRestaurant($restaurant)->deductForOrder($order);
            }
        }

        $order->transitionTo($newStatus);
        $this->loadOrders();
    }

    private function serialize(Order $order): array
    {
        return [
            'id'            => $order->id,
            'reference'     => $order->reference,
            'status'        => $order->status->value,
            'customer_name' => $order->customer_name ?? 'Client',
            'type'          => $order->type?->label() ?? '',
            'table_number'  => $order->table_number,
            'created_at'    => $order->created_at->format('H:i'),
            'minutes_ago'   => (int) $order->created_at->diffInMinutes(now()),
            'ready_at'      => $order->ready_at?->format('H:i'),
            'items'         => $order->items->map(fn($item) => [
                'quantity'     => $item->quantity,
                'name'         => $item->dish?->name ?? $item->dish_name ?? 'Plat',
                'options'      => $item->selected_options ?? [],
                'instructions' => $item->special_instructions ?? '',
            ])->values()->all(),
        ];
    }

    public function render()
    {
        return view('livewire.kitchen.kitchen-display');
    }
}
