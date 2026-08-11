<?php

namespace App\Livewire\Restaurant;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Enums\PaymentStatus;
use App\Models\DeliveryDriver;
use App\Models\DriverCashRemittance;
use App\Models\DriverEarning;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
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
            ->with('items.dish')
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

        // Cache 30s pour éviter un COUNT GROUP BY complet à chaque interaction Livewire
        // (changement de filtre, pagination, checkNewOrders poll, etc.)
        // Limité aux 30 derniers jours pour réduire le scan sur les gros restaurants
        $counts = \Illuminate\Support\Facades\Cache::remember(
            "status_counts.{$restaurantId}",
            30,
            fn() => Order::where('restaurant_id', $restaurantId)
                ->where('created_at', '>=', now()->subDays(30))
                ->selectRaw('status, COUNT(*) as total')
                ->groupBy('status')
                ->pluck('total', 'status')
        );

        return [
            'all'       => $counts->sum(),
            'pending'   => $counts->get(OrderStatus::PENDING_PAYMENT->value, 0),
            'confirmed' => $counts->get(OrderStatus::CONFIRMED->value, 0),
            'preparing' => $counts->get(OrderStatus::PREPARING->value, 0),
            'ready'     => $counts->get(OrderStatus::READY->value, 0),
        ];
    }

    public function viewOrder(int $orderId): void
    {
        try {
            $order = Order::with(['items.dish'])->findOrFail($orderId);
            
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

            // WhatsApp notifications are sent automatically via OrderWhatsAppObserver

            // Refresh selected order if it's the one we just updated
            if ($this->selectedOrder?->id === $orderId) {
                $this->selectedOrder = $order->fresh(['items.dish']);
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

    public int $lastOrderId = 0;

    public function mount(): void
    {
        $restaurantId = auth()->user()->restaurant_id;
        $this->lastOrderId = Order::where('restaurant_id', $restaurantId)->max('id') ?? 0;
    }

    public function checkNewOrders(): void
    {
        $restaurantId = auth()->user()->restaurant_id;
        $latestId = Order::where('restaurant_id', $restaurantId)->max('id') ?? 0;

        if ($latestId > $this->lastOrderId && $this->lastOrderId > 0) {
            $this->dispatch('new-order-received');
        }

        $this->lastOrderId = $latestId;

        unset($this->orders);
        unset($this->statusCounts);
    }

    /**
     * Mode Restaurant : le restaurant marque la commande comme livrée.
     * Uniquement pour les commandes delivery dont les livreurs sont gérés par le restaurant (source != platform_app).
     */
    public function markDelivered(int $orderId): void
    {
        $order = Order::where('restaurant_id', auth()->user()->restaurant_id)
            ->where('type', OrderType::DELIVERY)
            ->where('status', OrderStatus::DELIVERING)
            ->findOrFail($orderId);

        if ($order->source === 'platform_app') {
            session()->flash('error', 'Cette commande est gérée par les livreurs MenuPro.');
            return;
        }

        if (!$order->transitionTo(OrderStatus::COMPLETED)) {
            session()->flash('error', 'Impossible de marquer cette commande comme livrée.');
            return;
        }

        unset($this->orders);
        unset($this->statusCounts);

        $this->selectedOrder = $order->fresh(['items.dish']);
        session()->flash('success', 'Commande marquée comme livrée.');
    }

    /**
     * Mode Restaurant : le restaurant confirme avoir reçu l'argent cash.
     * Uniquement pour cash_on_delivery géré par le restaurant lui-même.
     */
    public function markCashReceived(int $orderId): void
    {
        $order = Order::where('restaurant_id', auth()->user()->restaurant_id)
            ->where('type', OrderType::DELIVERY)
            ->where('payment_method', 'cash_on_delivery')
            ->findOrFail($orderId);

        if ($order->source === 'platform_app') {
            session()->flash('error', 'Utilisez la section Reversements pour les livraisons MenuPro.');
            return;
        }

        $order->update([
            'payment_status' => PaymentStatus::COMPLETED,
            'paid_at'        => now(),
            'payout_status'  => 'manual',
        ]);

        unset($this->orders);
        unset($this->statusCounts);

        $this->selectedOrder = $order->fresh(['items.dish']);
        session()->flash('success', 'Paiement cash confirmé.');
    }

    #[Computed]
    public function pendingRemittances()
    {
        $restaurantId = auth()->user()->restaurant_id;

        return DriverCashRemittance::where('restaurant_id', $restaurantId)
            ->where('status', 'pending')
            ->with(['driver.user:id,name', 'debt.order:id,reference,total,delivery_fee', 'debt.delivery'])
            ->latest()
            ->get();
    }

    /**
     * Restaurant confirme avoir reçu le reversement cash du livreur.
     */
    public function confirmRemittance(int $remittanceId): void
    {
        $restaurantId = auth()->user()->restaurant_id;

        DB::transaction(function () use ($restaurantId, $remittanceId) {
            $remittance = DriverCashRemittance::where('restaurant_id', $restaurantId)
                ->where('status', 'pending')
                ->lockForUpdate()
                ->findOrFail($remittanceId);
            // Confirmer le reversement
            $remittance->update([
                'status'       => 'confirmed',
                'confirmed_by' => auth()->id(),
                'confirmed_at' => now(),
            ]);

            // Solder la dette
            $debt = $remittance->debt;
            $debt->update([
                'status'     => 'settled',
                'settled_at' => now(),
            ]);

            // Marquer la commande comme soldée
            $order = $debt->order;
            $order->update(['payout_status' => 'completed']);

            // Créditer les gains du livreur (différé depuis DELIVERED)
            $delivery = $debt->delivery;
            if ($delivery && $delivery->driver_id) {
                $gross       = (int) $order->delivery_fee;
                $platformCut = (int) round($gross * \App\Services\DriverAssignmentService::PLATFORM_CUT_RATE);
                $net         = $gross - $platformCut;

                // firstOrCreate évite le double-earning si confirmRemittance est appelé deux fois
                if ($gross > 0 && !DriverEarning::where('delivery_id', $delivery->id)->exists()) {
                    DriverEarning::create([
                        'driver_id'    => $delivery->driver_id,
                        'order_id'     => $order->id,
                        'delivery_id'  => $delivery->id,
                        'gross_amount' => $gross,
                        'platform_cut' => $platformCut,
                        'net_amount'   => $net,
                        'status'       => 'available',
                    ]);

                    DeliveryDriver::where('id', $delivery->driver_id)
                        ->increment('total_earnings_xof', $net);
                }
            }
        });

        unset($this->pendingRemittances);

        session()->flash('success', 'Reversement confirmé. Les gains du livreur ont été crédités.');
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

