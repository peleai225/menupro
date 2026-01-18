<?php

namespace App\Http\Controllers\Restaurant;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\PlanLimiter;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function __construct(
        protected PlanLimiter $planLimiter
    ) {}

    public function __invoke(Request $request): View
    {
        $restaurant = $request->user()->restaurant;
        $this->planLimiter->forRestaurant($restaurant);

        // Today's stats
        $todayStats = [
            'orders' => Order::today()->count(),
            'revenue' => Order::today()->where('payment_status', 'completed')->sum('total'),
            'pending' => Order::today()->whereIn('status', [
                OrderStatus::PAID,
                OrderStatus::CONFIRMED,
                OrderStatus::PREPARING,
            ])->count(),
        ];

        // This month's stats
        $monthStats = [
            'orders' => Order::thisMonth()->count(),
            'revenue' => Order::thisMonth()->where('payment_status', 'completed')->sum('total'),
            'average_order' => Order::thisMonth()->where('payment_status', 'completed')->avg('total') ?? 0,
        ];

        // Recent orders
        $recentOrders = Order::with('items')
            ->latest()
            ->take(10)
            ->get();

        // Active orders (need attention)
        $activeOrders = Order::active()
            ->with('items')
            ->latest()
            ->get();

        // Popular dishes this month
        $popularDishes = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->join('dishes', 'order_items.dish_id', '=', 'dishes.id')
            ->where('orders.restaurant_id', $restaurant->id)
            ->where('orders.created_at', '>=', now()->startOfMonth())
            ->select('dishes.id', 'dishes.name', 'dishes.price', DB::raw('SUM(order_items.quantity) as total_sold'))
            ->groupBy('dishes.id', 'dishes.name', 'dishes.price')
            ->orderByDesc('total_sold')
            ->limit(5)
            ->get();

        // Quotas
        $quotas = $this->planLimiter->getQuotasSummary();

        // Low stock alerts (if feature available)
        $lowStockCount = 0;
        if ($restaurant->hasFeature('stock')) {
            $lowStockCount = $restaurant->ingredients()
                ->whereColumn('current_quantity', '<=', 'min_quantity')
                ->count();
        }

        return view('pages.restaurant.dashboard', compact(
            'restaurant',
            'todayStats',
            'monthStats',
            'recentOrders',
            'activeOrders',
            'popularDishes',
            'quotas',
            'lowStockCount'
        ));
    }
}

