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

    #[Computed]
    public function todayKpi(): array
    {
        $restaurant = auth()->user()->restaurant;

        if (!$restaurant) {
            return ['cash_today' => 0, 'mobile_today' => 0, 'cancelled_today' => 0, 'cancelled_lost' => 0, 'peak_hour' => '—', 'vs_yesterday_pct' => 0];
        }

        $calc      = RevenueCalculator::for($restaurant->id, today()->startOfDay(), now());
        $byPayment = $calc->revenueByPaymentMethodDetailed();
        $cancelled = $calc->cancellationStats();
        $byHour    = $calc->revenueByHourWithPayment();
        $peak      = $byHour->sortByDesc('orders_count')->first();

        $yesterday    = RevenueCalculator::for($restaurant->id, now()->subDay()->startOfDay(), now()->subDay()->endOfDay());
        $revYesterday = $yesterday->grossRevenue();
        $revToday     = $calc->grossRevenue();
        $changePct    = $revYesterday > 0
            ? (int) round((($revToday - $revYesterday) / $revYesterday) * 100)
            : ($revToday > 0 ? 100 : 0);

        return [
            'cash_today'       => (int) $byPayment->where('is_cash', true)->sum('total_amount'),
            'mobile_today'     => (int) $byPayment->where('is_cash', false)->sum('total_amount'),
            'cancelled_today'  => $cancelled['count'],
            'cancelled_lost'   => $cancelled['total_lost'],
            'peak_hour'        => $peak ? $peak['hour_label'] : '—',
            'vs_yesterday_pct' => $changePct,
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
     * Get revenue trend for the last 30 days (for chart).
     */
    #[Computed]
    public function revenueTrend(): array
    {
        $restaurant = auth()->user()->restaurant;

        if (!$restaurant) {
            return ['labels' => [], 'data' => []];
        }

        $data = Order::where('restaurant_id', $restaurant->id)
            ->whereBetween('created_at', [now()->subDays(30), now()])
            ->whereNotNull('paid_at')
            ->validForReporting()
            ->selectRaw('DATE(created_at) as date')
            ->selectRaw('SUM(total - COALESCE(platform_commission, 0) - COALESCE(delivery_fee, 0)) as revenue_net')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $labels = $data->pluck('date')->map(fn($date) => \Carbon\Carbon::parse($date)->format('d/m'))->toArray();
        $values = $data->pluck('revenue_net')->map(fn($v) => (float) $v)->toArray();

        return ['labels' => $labels, 'data' => $values];
    }

    /**
     * Get orders distribution by type (for pie chart).
     */
    #[Computed]
    public function ordersByType(): array
    {
        $restaurant = auth()->user()->restaurant;

        if (!$restaurant) {
            return ['labels' => [], 'data' => []];
        }

        $data = Order::where('restaurant_id', $restaurant->id)
            ->whereBetween('created_at', [now()->subDays(30), now()])
            ->whereNotNull('paid_at')
            ->validForReporting()
            ->selectRaw('type, COUNT(*) as count')
            ->groupBy('type')
            ->get();

        $labels = $data->pluck('type')->map(function($type) {
            // Convert enum to string first
            $typeValue = is_object($type) ? $type->value : (string) $type;

            return match($typeValue) {
                'delivery' => 'Livraison',
                'takeaway' => 'À emporter',
                'dine_in' => 'Sur place',
                default => ucfirst($typeValue)
            };
        })->toArray();

        $values = $data->pluck('count')->map(fn($v) => (int) $v)->toArray();

        return ['labels' => $labels, 'data' => $values];
    }

    /**
     * Get top 5 dishes (for bar chart).
     */
    #[Computed]
    public function topDishes(): array
    {
        $restaurant = auth()->user()->restaurant;

        if (!$restaurant) {
            return ['labels' => [], 'data' => []];
        }

        $data = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('dishes', 'order_items.dish_id', '=', 'dishes.id')
            ->where('orders.restaurant_id', $restaurant->id)
            ->whereBetween('orders.created_at', [now()->subDays(30), now()])
            ->whereNotNull('orders.paid_at')
            ->whereNotIn('orders.status', ['draft', 'cancelled', 'refunded'])
            ->select('dishes.name', DB::raw('SUM(order_items.quantity) as total_sold'))
            ->groupBy('dishes.id', 'dishes.name')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();

        $labels = $data->pluck('name')->toArray();
        $values = $data->pluck('total_sold')->map(fn($v) => (int) $v)->toArray();

        return ['labels' => $labels, 'data' => $values];
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

