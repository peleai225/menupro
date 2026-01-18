<?php

namespace App\Livewire\Restaurant;

use App\Enums\OrderStatus;
use App\Models\Order;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Attributes\Url;
use Livewire\Component;
use Livewire\WithPagination;

class Orders extends Component
{
    use WithPagination;

    #[Url]
    public string $search = '';

    #[Url]
    public string $status = '';

    #[Url]
    public string $type = '';

    #[Url]
    public string $date = '';

    public ?Order $selectedOrder = null;
    
    public ?string $cancellationReason = null;
    
    public bool $showCancelModal = false;

    public function updatingSearch(): void
    {
        $this->resetPage();
    }

    #[Computed]
    public function orders()
    {
        $restaurantId = auth()->user()->restaurant_id;
        
        if (!$restaurantId) {
            return \Illuminate\Contracts\Pagination\LengthAwarePaginator::make([], 0, 15);
        }
        
        return Order::where('restaurant_id', $restaurantId)
            ->with('items')
            ->when($this->search, function ($q) {
                $q->where(function ($query) {
                    $query->where('reference', 'like', "%{$this->search}%")
                        ->orWhere('customer_name', 'like', "%{$this->search}%")
                        ->orWhere('customer_phone', 'like', "%{$this->search}%");
                });
            })
            ->when($this->status, fn($q) => $q->where('status', $this->status))
            ->when($this->type, fn($q) => $q->where('type', $this->type))
            ->when($this->date === 'today', fn($q) => $q->whereDate('created_at', today()))
            ->when($this->date === 'yesterday', fn($q) => $q->whereDate('created_at', today()->subDay()))
            ->when($this->date === 'week', fn($q) => $q->where('created_at', '>=', now()->startOfWeek()))
            ->when($this->date === 'month', fn($q) => $q->where('created_at', '>=', now()->startOfMonth()))
            ->latest()
            ->paginate(15);
    }

    #[Computed]
    public function statusCounts(): array
    {
        $restaurantId = auth()->user()->restaurant_id;
        
        if (!$restaurantId) {
            return [
                'all' => 0,
                'pending' => 0,
                'confirmed' => 0,
                'preparing' => 0,
                'ready' => 0,
            ];
        }
        
        return [
            'all' => Order::where('restaurant_id', $restaurantId)->count(),
            'pending' => Order::where('restaurant_id', $restaurantId)->where('status', OrderStatus::PENDING_PAYMENT)->count(),
            'confirmed' => Order::where('restaurant_id', $restaurantId)->where('status', OrderStatus::CONFIRMED)->count(),
            'preparing' => Order::where('restaurant_id', $restaurantId)->where('status', OrderStatus::PREPARING)->count(),
            'ready' => Order::where('restaurant_id', $restaurantId)->where('status', OrderStatus::READY)->count(),
        ];
    }

    public function viewOrder(int $orderId): void
    {
        try {
            $order = Order::with('items')->findOrFail($orderId);
            
            // Check if order belongs to user's restaurant
            if ($order->restaurant_id !== auth()->user()->restaurant_id) {
                session()->flash('error', 'Vous n\'avez pas accès à cette commande.');
                return;
            }
            
            $this->selectedOrder = $order;
            $this->dispatch('order-selected');
        } catch (\Exception $e) {
            session()->flash('error', 'Commande introuvable : ' . $e->getMessage());
        }
    }

    public function closeOrderModal(): void
    {
        $this->selectedOrder = null;
    }

    public function updateStatus(int $orderId, string $status): void
    {
        try {
            $order = Order::findOrFail($orderId);
            
            // Check if order belongs to user's restaurant
            if ($order->restaurant_id !== auth()->user()->restaurant_id) {
                session()->flash('error', 'Vous n\'avez pas la permission de modifier cette commande.');
                return;
            }
            
            $newStatus = OrderStatus::from($status);
            
            // Special handling: if order is PENDING_PAYMENT and we want to confirm it,
            // first mark it as paid (if not already paid), then confirm
            if ($order->status === OrderStatus::PENDING_PAYMENT && $newStatus === OrderStatus::CONFIRMED) {
                // Mark as paid first if not already paid
                if (!$order->is_paid) {
                    $order->markAsPaid([
                        'method' => 'on_site',
                        'metadata' => ['note' => 'Paiement confirmé manuellement par le restaurant'],
                    ]);
                    $order->refresh();
                }
                // Now transition to confirmed
                if (!$order->transitionTo($newStatus)) {
                    session()->flash('error', 'Impossible de confirmer cette commande.');
                    return;
                }
            } else {
                // Use transitionTo method if available, otherwise update directly
                if (method_exists($order, 'transitionTo')) {
                    if (!$order->transitionTo($newStatus)) {
                        session()->flash('error', 'Cette transition de statut n\'est pas autorisée. Statut actuel: ' . $order->status->label() . ', Statut demandé: ' . $newStatus->label());
                        return;
                    }
                } else {
                    $order->update(['status' => $newStatus]);
                    
                    // Update timestamps based on status
                    match ($newStatus) {
                        OrderStatus::CONFIRMED => $order->update(['confirmed_at' => now()]),
                        OrderStatus::PREPARING => $order->update(['preparing_at' => now()]),
                        OrderStatus::READY => $order->update(['ready_at' => now()]),
                        OrderStatus::COMPLETED => $order->update(['completed_at' => now()]),
                        OrderStatus::CANCELLED => $order->update(['cancelled_at' => now()]),
                        default => null,
                    };
                }
            }

            // Refresh selected order if it's the one we just updated
            if ($this->selectedOrder?->id === $orderId) {
                $this->selectedOrder = $order->fresh(['items']);
            }

            session()->flash('success', 'Statut mis à jour avec succès.');
        } catch (\Exception $e) {
            session()->flash('error', 'Une erreur est survenue : ' . $e->getMessage());
        }
    }

    public function openCancelModal(int $orderId): void
    {
        if ($this->selectedOrder?->id === $orderId) {
            $this->showCancelModal = true;
            $this->cancellationReason = null;
        }
    }
    
    public function cancelOrder(int $orderId): void
    {
        try {
            $order = Order::findOrFail($orderId);
            
            // Check if order belongs to user's restaurant
            if ($order->restaurant_id !== auth()->user()->restaurant_id) {
                session()->flash('error', 'Vous n\'avez pas la permission d\'annuler cette commande.');
                $this->showCancelModal = false;
                return;
            }
            
            // Use cancel method if available
            if (method_exists($order, 'cancel')) {
                if (!$order->cancel($this->cancellationReason)) {
                    session()->flash('error', 'Cette commande ne peut pas être annulée.');
                    $this->showCancelModal = false;
                    return;
                }
            } else {
                $order->update([
                    'status' => OrderStatus::CANCELLED,
                    'cancelled_at' => now(),
                    'cancellation_reason' => $this->cancellationReason,
                ]);
            }

            if ($this->selectedOrder?->id === $orderId) {
                $this->selectedOrder->refresh();
            }

            $this->showCancelModal = false;
            $this->cancellationReason = null;
            session()->flash('success', 'Commande annulée avec succès.');
        } catch (\Exception $e) {
            session()->flash('error', 'Une erreur est survenue : ' . $e->getMessage());
            $this->showCancelModal = false;
        }
    }
    
    public function closeCancelModal(): void
    {
        $this->showCancelModal = false;
        $this->cancellationReason = null;
    }

    public function render()
    {
        $restaurant = auth()->user()->restaurant;
        $subscription = $restaurant?->activeSubscription;
        
        return view('livewire.restaurant.orders')
            ->layout('components.layouts.admin-restaurant', [
                'title' => 'Commandes',
                'restaurant' => $restaurant,
                'subscription' => $subscription,
            ]);
    }
}

