<?php

namespace App\Livewire\Restaurant;

use App\Models\Order;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Analytics extends Component
{
    public string $period = '30'; // days
    public string $chartType = 'revenue'; // revenue, orders, dishes

    /** Périodes autorisées — protège contre un $period=36500 qui ferait un full table scan */
    private const ALLOWED_PERIODS = ['7', '30', '90', '365'];

    public function mount(): void
    {
        //
    }

    public function updatedPeriod(): void
    {
        // Valider la période pour éviter les full table scans sur des plages arbitraires
        if (!in_array($this->period, self::ALLOWED_PERIODS)) {
            $this->period = '30';
        }
    }

    public function getStatsProperty(): array
    {
        $restaurant = auth()->user()->restaurant;

        if (!$restaurant) {
            return [];
        }

        // Cache 5 minutes par restaurant + période pour éviter les re-calculs
        // à chaque render Livewire en mode multi-restaurant simultané
        $cacheKey = "analytics.{$restaurant->id}.{$this->period}";
        return Cache::remember($cacheKey, 300, fn() => $this->computeStats($restaurant));
    }

    private function computeStats($restaurant): array
    {
        $startDate = now()->subDays((int) $this->period)->startOfDay();
        $endDate = now()->endOfDay();

        // Total revenue
        $totalRevenue = Order::where('restaurant_id', $restaurant->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('paid_at')
            ->sum('total');

        // Total orders
        $totalOrders = Order::where('restaurant_id', $restaurant->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        // Average order value
        $averageOrder = $totalOrders > 0 
            ? round($totalRevenue / $totalOrders) 
            : 0;

        // Orders by status
        $ordersByStatus = Order::where('restaurant_id', $restaurant->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->status->value => $item->count];
            })
            ->toArray();

        // Orders by type
        $ordersByType = Order::where('restaurant_id', $restaurant->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select('type', DB::raw('COUNT(*) as count'))
            ->groupBy('type')
            ->get()
            ->mapWithKeys(function ($item) {
                return [$item->type->value => $item->count];
            })
            ->toArray();

        // Revenue by day
        $revenueByDay = Order::where('restaurant_id', $restaurant->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->whereNotNull('paid_at')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total) as revenue'),
                DB::raw('COUNT(*) as orders')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Top dishes
        $topDishes = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('dishes', 'order_items.dish_id', '=', 'dishes.id')
            ->where('orders.restaurant_id', $restaurant->id)
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->whereNotNull('orders.paid_at')
            ->select(
                'dishes.id',
                'dishes.name',
                'dishes.price',
                DB::raw('SUM(order_items.quantity) as total_sold'),
                DB::raw('SUM(order_items.total_price) as total_revenue')
            )
            ->groupBy('dishes.id', 'dishes.name', 'dishes.price')
            ->orderByDesc('total_sold')
            ->limit(10)
            ->get();

        // Peak hours
        $peakHours = Order::where('restaurant_id', $restaurant->id)
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw('HOUR(created_at) as hour'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('hour')
            ->orderByDesc('count')
            ->limit(5)
            ->get();

        // Comparison with previous period
        $previousStartDate = $startDate->copy()->subDays((int) $this->period);
        $previousEndDate = $startDate->copy()->subSecond();

        $previousRevenue = Order::where('restaurant_id', $restaurant->id)
            ->whereBetween('created_at', [$previousStartDate, $previousEndDate])
            ->whereNotNull('paid_at')
            ->sum('total');

        $previousOrders = Order::where('restaurant_id', $restaurant->id)
            ->whereBetween('created_at', [$previousStartDate, $previousEndDate])
            ->count();

        $revenueChange = $previousRevenue > 0 
            ? round((($totalRevenue - $previousRevenue) / $previousRevenue) * 100) 
            : ($totalRevenue > 0 ? 100 : 0);

        $ordersChange = $previousOrders > 0 
            ? round((($totalOrders - $previousOrders) / $previousOrders) * 100) 
            : ($totalOrders > 0 ? 100 : 0);

        return [
            'total_revenue' => $totalRevenue,
            'total_orders' => $totalOrders,
            'average_order' => $averageOrder,
            'orders_by_status' => $ordersByStatus,
            'orders_by_type' => $ordersByType,
            'revenue_by_day' => $revenueByDay,
            'top_dishes' => $topDishes,
            'peak_hours' => $peakHours,
            'revenue_change' => $revenueChange,
            'orders_change' => $ordersChange,
        ];
    }

    public function render()
    {
        $restaurant = auth()->user()->restaurant;
        $subscription = $restaurant?->activeSubscription;

        return view('livewire.restaurant.analytics')
            ->layout('components.layouts.admin-restaurant', [
                'title' => 'Statistiques',
                'restaurant' => $restaurant,
                'subscription' => $subscription,
            ]);
    }
}

