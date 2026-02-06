<?php

namespace App\Livewire\Restaurant;

use App\Enums\OrderStatus;
use App\Models\Dish;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Component;

class Dashboard extends Component
{
    /**
     * Get statistics for today.
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
                'revenue_change' => 0,
                'pending_orders' => 0,
                'dishes_count' => 0,
                'max_dishes' => 0,
            ];
        }
        
        $today = now()->startOfDay();
        $yesterday = now()->subDay()->startOfDay();

        // Orders today
        $ordersToday = Order::where('restaurant_id', $restaurant->id)
            ->whereDate('created_at', $today)
            ->count();

        $ordersYesterday = Order::where('restaurant_id', $restaurant->id)
            ->whereDate('created_at', $yesterday)
            ->count();

        $ordersChange = $ordersYesterday > 0 
            ? round((($ordersToday - $ordersYesterday) / $ordersYesterday) * 100) 
            : ($ordersToday > 0 ? 100 : 0);

        // Revenue today
        $revenueToday = Order::where('restaurant_id', $restaurant->id)
            ->whereDate('created_at', $today)
            ->whereNotNull('paid_at')
            ->sum('total');

        $revenueYesterday = Order::where('restaurant_id', $restaurant->id)
            ->whereDate('created_at', $yesterday)
            ->whereNotNull('paid_at')
            ->sum('total');

        $revenueChange = $revenueYesterday > 0 
            ? round((($revenueToday - $revenueYesterday) / $revenueYesterday) * 100) 
            : ($revenueToday > 0 ? 100 : 0);

        // Pending orders
        $pendingOrders = Order::where('restaurant_id', $restaurant->id)
            ->whereIn('status', [OrderStatus::PENDING_PAYMENT, OrderStatus::CONFIRMED, OrderStatus::PREPARING])
            ->count();

        // Dishes count
        $dishesCount = Dish::where('restaurant_id', $restaurant->id)->count();
        $maxDishes = $restaurant->currentPlan?->max_dishes ?? 50;

        return [
            'orders_today' => $ordersToday,
            'orders_change' => $ordersChange,
            'revenue_today' => $revenueToday,
            'revenue_change' => $revenueChange,
            'pending_orders' => $pendingOrders,
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
            ->with('items')
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

        return Dish::where('restaurant_id', $restaurant->id)
            ->withCount(['orderItems as orders_count' => function ($query) {
                $query->whereHas('order', function ($q) {
                    $q->whereNotNull('paid_at')
                        ->where('created_at', '>=', now()->subDays(30));
                });
            }])
            ->orderByDesc('orders_count')
            ->limit(5)
            ->get();
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

        return \App\Models\Announcement::active()
            ->forDashboard()
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

