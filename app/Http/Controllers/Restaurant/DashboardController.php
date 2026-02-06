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

        // Today's orders
        $ordersToday = Order::where('restaurant_id', $restaurant->id)
            ->whereDate('created_at', today())
            ->count();

        // Yesterday's orders for comparison
        $ordersYesterday = Order::where('restaurant_id', $restaurant->id)
            ->whereDate('created_at', today()->subDay())
            ->count();

        // Revenue today
        $revenueToday = Order::where('restaurant_id', $restaurant->id)
            ->whereDate('created_at', today())
            ->where('payment_status', 'completed')
            ->sum('total');

        // Revenue yesterday
        $revenueYesterday = Order::where('restaurant_id', $restaurant->id)
            ->whereDate('created_at', today()->subDay())
            ->where('payment_status', 'completed')
            ->sum('total');

        // Pending orders
        $pendingOrders = Order::where('restaurant_id', $restaurant->id)
            ->whereIn('status', [
                OrderStatus::PENDING,
                OrderStatus::PAID,
                OrderStatus::CONFIRMED,
                OrderStatus::PREPARING,
            ])
            ->count();

        // Total dishes
        $totalDishes = $restaurant->dishes()->count();

        // Calculate growth percentages
        $ordersGrowth = $ordersYesterday > 0 
            ? round((($ordersToday - $ordersYesterday) / $ordersYesterday) * 100) 
            : 0;
        
        $revenueGrowth = $revenueYesterday > 0 
            ? round((($revenueToday - $revenueYesterday) / $revenueYesterday) * 100) 
            : 0;

        // Stats array for the view
        $stats = [
            'ordersToday' => $ordersToday,
            'revenueToday' => $revenueToday,
            'pendingOrders' => $pendingOrders,
            'totalDishes' => $totalDishes,
            'ordersGrowth' => $ordersGrowth,
            'revenueGrowth' => $revenueGrowth,
        ];

        // Recent orders with items
        $recentOrders = Order::where('restaurant_id', $restaurant->id)
            ->with('items.dish')
            ->latest()
            ->take(5)
            ->get();

        // Top dishes (most ordered)
        $topDishes = $restaurant->dishes()
            ->withCount(['orderItems as orders_count' => function ($query) {
                $query->whereHas('order', function ($q) {
                    $q->where('created_at', '>=', now()->subDays(30));
                });
            }])
            ->orderByDesc('orders_count')
            ->take(5)
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

        // Get active subscription for trial display
        $subscription = $restaurant->activeSubscription;

        // Get active announcements for this restaurant
        $announcements = \App\Models\Announcement::active()
            ->forDashboard()
            ->latest()
            ->get()
            ->filter(function ($announcement) use ($restaurant) {
                return $announcement->isVisibleFor($restaurant);
            })
            ->reject(function ($announcement) use ($request) {
                return $announcement->isDismissedBy($request->user());
            });

        return view('pages.restaurant.dashboard', compact(
            'restaurant',
            'subscription',
            'stats',
            'recentOrders',
            'topDishes',
            'quotas',
            'lowStockCount',
            'announcements'
        ));
    }
}

