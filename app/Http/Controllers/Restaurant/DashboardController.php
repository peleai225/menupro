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

        // Consolidation : 1 requête au lieu de 5 pour les compteurs du dashboard restaurant
        $orderAgg = \Illuminate\Support\Facades\DB::table('orders')
            ->where('restaurant_id', $restaurant->id)
            ->selectRaw("
                SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as orders_today,
                SUM(CASE WHEN DATE(created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY) THEN 1 ELSE 0 END) as orders_yesterday,
                SUM(CASE WHEN DATE(created_at) = CURDATE() AND payment_status = 'completed' THEN total ELSE 0 END) as revenue_today,
                SUM(CASE WHEN DATE(created_at) = DATE_SUB(CURDATE(), INTERVAL 1 DAY) AND payment_status = 'completed' THEN total ELSE 0 END) as revenue_yesterday,
                SUM(CASE WHEN status IN (?,?,?,?) THEN 1 ELSE 0 END) as pending_orders
            ", [
                OrderStatus::PENDING_PAYMENT->value,
                OrderStatus::PAID->value,
                OrderStatus::CONFIRMED->value,
                OrderStatus::PREPARING->value,
            ])
            ->first();

        $ordersToday     = (int) ($orderAgg->orders_today ?? 0);
        $ordersYesterday = (int) ($orderAgg->orders_yesterday ?? 0);
        $revenueToday    = (float) ($orderAgg->revenue_today ?? 0);
        $revenueYesterday = (float) ($orderAgg->revenue_yesterday ?? 0);
        $pendingOrders   = (int) ($orderAgg->pending_orders ?? 0);

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
        // Eager-load dismissals pour l'utilisateur courant afin d'éviter N requêtes SQL
        // (une par annonce) dans isDismissedBy() — finding N+1
        $userId = $request->user()->id;
        $announcements = \App\Models\Announcement::active()
            ->forDashboard()
            ->with(['dismissals' => fn($q) => $q->where('user_id', $userId)])
            ->latest()
            ->get()
            ->filter(fn($announcement) => $announcement->isVisibleFor($restaurant))
            ->reject(fn($announcement) => $announcement->isDismissedBy($request->user()));

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

