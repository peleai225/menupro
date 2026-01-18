<?php

namespace App\Livewire\Restaurant;

use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Livewire\Component;

class Analytics extends Component
{
    public string $period = '30'; // days
    public string $chartType = 'revenue'; // revenue, orders, dishes

    public function mount(): void
    {
        //
    }

    public function updatedPeriod(): void
    {
        // Refresh data when period changes
    }

    public function getStatsProperty(): array
    {
        $restaurant = auth()->user()->restaurant;

        if (!$restaurant) {
            return [];
        }

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

