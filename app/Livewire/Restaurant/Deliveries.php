<?php

namespace App\Livewire\Restaurant;

use App\Enums\DeliveryStatus;
use App\Enums\OrderStatus;
use App\Models\Delivery;
use App\Models\DeliveryDriver;
use App\Models\Order;
use Livewire\Component;

class Deliveries extends Component
{
    public string $filter = 'active';
    public ?int $assignDriverId = null;
    public ?int $assignOrderId = null;
    public bool $showAssignModal = false;

    public function getDeliveriesProperty()
    {
        $restaurantId = auth()->user()->restaurant_id;

        $query = Delivery::where('restaurant_id', $restaurantId)
            ->with(['order', 'driver'])
            ->latest();

        if ($this->filter === 'active') {
            $query->whereIn('status', [
                DeliveryStatus::PENDING,
                DeliveryStatus::ASSIGNED,
                DeliveryStatus::HEADING_TO_RESTAURANT,
                DeliveryStatus::PICKED_UP,
                DeliveryStatus::DELIVERING,
            ]);
        } elseif ($this->filter === 'completed') {
            $query->where('status', DeliveryStatus::DELIVERED);
        }

        return $query->take(50)->get();
    }

    public function getPendingOrdersProperty()
    {
        $restaurantId = auth()->user()->restaurant_id;

        return Order::where('restaurant_id', $restaurantId)
            ->where('status', OrderStatus::READY)
            ->where(function ($q) {
                $q->whereDoesntHave('delivery')
                  ->orWhereHas('delivery', fn($sub) => $sub->where('status', DeliveryStatus::CANCELLED));
            })
            ->latest()
            ->take(20)
            ->get();
    }

    public function getAvailableDriversProperty()
    {
        return DeliveryDriver::where('restaurant_id', auth()->user()->restaurant_id)
            ->available()
            ->get();
    }

    public function openAssign(int $orderId): void
    {
        $this->assignOrderId = $orderId;
        $this->assignDriverId = null;
        $this->showAssignModal = true;
    }

    public function assignDriver(): void
    {
        if (!$this->assignOrderId || !$this->assignDriverId) {
            return;
        }

        $restaurantId = auth()->user()->restaurant_id;
        $order = Order::where('restaurant_id', $restaurantId)->findOrFail($this->assignOrderId);
        $driver = DeliveryDriver::where('restaurant_id', $restaurantId)->findOrFail($this->assignDriverId);

        $delivery = Delivery::create([
            'order_id' => $order->id,
            'restaurant_id' => $restaurantId,
            'driver_id' => $driver->id,
            'status' => DeliveryStatus::ASSIGNED,
            'delivery_address' => $order->delivery_address ?? '',
            'delivery_phone' => $order->customer_phone,
            'assigned_at' => now(),
            'pickup_latitude' => $order->restaurant->latitude,
            'pickup_longitude' => $order->restaurant->longitude,
        ]);

        $order->transitionTo(OrderStatus::DELIVERING);

        $this->showAssignModal = false;
        $this->reset(['assignOrderId', 'assignDriverId']);
    }

    public function render()
    {
        return view('livewire.restaurant.deliveries')
            ->layout('components.layouts.admin-restaurant', [
                'title' => 'Livraisons',
                'restaurant' => auth()->user()->restaurant,
                'subscription' => auth()->user()->restaurant?->activeSubscription,
            ]);
    }
}
