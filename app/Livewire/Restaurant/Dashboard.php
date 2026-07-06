<?php

namespace App\Livewire\Restaurant;

use App\Enums\OrderStatus;
use App\Models\Dish;
use App\Models\Order;
use App\Services\RevenueCalculator;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Dashboard extends Component
{
    /**
     * Get statistics for today using the centralized RevenueCalculator.
     */
    #[Computed]
    public function stats(): array
    {
        $restaurant = auth()->user()->restaurant;

        if (!$restaurant) {
            return [
                'orders_today' => 0,
                'orders_change' => 0,
                'revenue_today' => 0,
                'revenue_net_today' => 0,
                'revenue_change' => 0,
                'pending_orders' => 0,
                'average_ticket' => 0,
                'dishes_count' => 0,
                'max_dishes' => 0,
            ];
        }

        $today = RevenueCalculator::for($restaurant->id, today()->startOfDay(), now());
        $yesterday = RevenueCalculator::for(
            $restaurant->id,
            now()->subDay()->startOfDay(),
            now()->subDay()->endOfDay()
        );

        $revenueToday = $today->grossRevenue();
        $revenueYesterday = $yesterday->grossRevenue();

        $revenueChange = $revenueYesterday > 0
            ? round((($revenueToday - $revenueYesterday) / $revenueYesterday) * 100)
            : ($revenueToday > 0 ? 100 : 0);

        $ordersToday = $today->validOrdersCount();
        $ordersYesterday = $yesterday->validOrdersCount();

        $ordersChange = $ordersYesterday > 0
            ? round((($ordersToday - $ordersYesterday) / $ordersYesterday) * 100)
            : ($ordersToday > 0 ? 100 : 0);

        $pendingOrders = Order::where('restaurant_id', $restaurant->id)
            ->whereIn('status', [OrderStatus::PENDING_PAYMENT, OrderStatus::CONFIRMED, OrderStatus::PREPARING])
            ->count();

        $dishesCount = Dish::where('restaurant_id', $restaurant->id)->count();
        $maxDishes = $restaurant->currentPlan?->max_dishes ?? 50;

        return [
            'orders_today' => $ordersToday,
            'orders_change' => $ordersChange,
            'revenue_today' => $revenueToday,
            'revenue_net_today' => $today->netRevenue(),
            'revenue_change' => $revenueChange,
            'pending_orders' => $pendingOrders,
            'average_ticket' => $today->averageTicket(),
            'dishes_count' => $dishesCount,
            'max_dishes' => $maxDishes,
        ];
    }

    /**
     * Get recent orders.
     */
    #[Computed]
    public function recentOrders()
    {
        $restaurantId = auth()->user()->restaurant_id;
        
        if (!$restaurantId) {
            return collect();
        }
        
        return Order::where('restaurant_id', $restaurantId)
            ->with('items.dish:id,name,price') // eager load items.dish pour éviter le N+1 en vue
            ->latest()
            ->limit(5)
            ->get();
    }

    /**
     * Get popular dishes.
     */
    #[Computed]
    public function popularDishes()
    {
        $restaurant = auth()->user()->restaurant;

        if (!$restaurant) {
            return collect();
        }

        return Cache::remember(
            "restaurant.{$restaurant->id}.popular_dishes",
            300,
            fn() => Dish::where('restaurant_id', $restaurant->id)
                ->withCount(['orderItems as orders_count' => function ($query) {
                    $query->whereHas('order', function ($q) {
                        $q->whereNotNull('paid_at')
                            ->where('created_at', '>=', now()->subDays(30));
                    });
                }])
                ->orderByDesc('orders_count')
                ->limit(5)
                ->get()
        );
    }

    /**
     * Get low stock alerts.
     */
    #[Computed]
    public function stockAlerts()
    {
        $restaurant = auth()->user()->restaurant;
        
        if (!$restaurant || !$restaurant->currentPlan?->has_stock_management) {
            return collect();
        }

        return $restaurant->ingredients()
            ->whereColumn('current_quantity', '<=', 'min_quantity')
            ->where('min_quantity', '>', 0)
            ->limit(5)
            ->get();
    }

    /**
     * Get active announcements for this restaurant.
     */
    #[Computed]
    public function announcements()
    {
        $restaurant = auth()->user()->restaurant;
        $user = auth()->user();
        
        if (!$restaurant) {
            return collect();
        }

        // Eager-load dismissals pour l'utilisateur courant — évite N requêtes SQL dans isDismissedBy()
        $userId = $user->id;
        return \App\Models\Announcement::active()
            ->forDashboard()
            ->with(['dismissals' => fn($q) => $q->where('user_id', $userId)])
            ->latest()
            ->get()
            ->filter(fn($announcement) => $announcement->isVisibleFor($restaurant))
            ->reject(fn($announcement) => $announcement->isDismissedBy($user));
    }

    /**
     * Dismiss an announcement.
     */
    public function dismissAnnouncement($announcementId)
    {
        $user = auth()->user();
        
        \App\Models\AnnouncementDismissal::firstOrCreate([
            'announcement_id' => $announcementId,
            'user_id' => $user->id,
        ]);

        // Clear the computed property cache
        unset($this->announcements);
    }

    public function render()
    {
        $restaurant = auth()->user()->restaurant;
        $subscription = $restaurant?->activeSubscription;
        
        return view('livewire.restaurant.dashboard')
            ->layout('components.layouts.admin-restaurant', [
                'title' => 'Dashboard',
                'restaurant' => $restaurant,
                'subscription' => $subscription,
            ]);
    }
}

