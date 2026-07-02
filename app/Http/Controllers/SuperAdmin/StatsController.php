<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SuperAdminStatsExport;

class StatsController extends Controller
{
    /**
     * Display statistics dashboard.
     */
    public function index(Request $request): View
    {
        $period = $request->get('period', '30'); // days

        // Revenue over time
        $revenueData = Order::withoutGlobalScope('restaurant')
            ->where('payment_status', 'completed')
            ->where('created_at', '>=', now()->subDays($period))
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total) as revenue'),
                DB::raw('COUNT(*) as orders')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Orders by status
        $ordersByStatus = Order::withoutGlobalScope('restaurant')
            ->where('created_at', '>=', now()->subDays($period))
            ->select('status', DB::raw('COUNT(*) as count'))
            ->groupBy('status')
            ->get();

        // Orders by type
        $ordersByType = Order::withoutGlobalScope('restaurant')
            ->where('created_at', '>=', now()->subDays($period))
            ->select('type', DB::raw('COUNT(*) as count'))
            ->groupBy('type')
            ->get();

        // New restaurants over time
        $newRestaurants = Restaurant::where('created_at', '>=', now()->subDays($period))
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Top 10 days by order count (all statuses) for bar chart
        $topDays = Order::withoutGlobalScope('restaurant')
            ->where('created_at', '>=', now()->subDays($period))
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('COUNT(*) as orders')
            )
            ->groupBy('date')
            ->orderByDesc('orders')
            ->limit(10)
            ->get();

        // Weekly order trend for line chart
        $weeklyTrend = DB::table('orders')
            ->where('created_at', '>=', now()->subDays($period))
            ->selectRaw("YEARWEEK(created_at, 1) as yw")
            ->selectRaw("MIN(DATE(created_at)) as week_start")
            ->selectRaw("COUNT(*) as orders")
            ->groupBy('yw')
            ->orderBy('yw')
            ->get();

        // Subscription revenue
        $subscriptionRevenue = Subscription::where('created_at', '>=', now()->subDays($period))
            ->where('status', 'active')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(amount_paid) as revenue')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Plan distribution
        $planDistribution = Restaurant::join('plans', 'restaurants.current_plan_id', '=', 'plans.id')
            ->where('restaurants.status', 'active')
            ->select('plans.name', DB::raw('COUNT(*) as count'))
            ->groupBy('plans.name')
            ->get();

        // Top cities
        $topCities = Restaurant::whereNotNull('city')
            ->select('city', DB::raw('COUNT(*) as count'))
            ->groupBy('city')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        // Summary stats — single query for orders
        $orderSummary = DB::table('orders')
            ->where('created_at', '>=', now()->subDays($period))
            ->selectRaw("COUNT(*) as total_orders")
            ->selectRaw("SUM(CASE WHEN payment_status = 'completed' THEN total ELSE 0 END) as total_revenue")
            ->selectRaw("AVG(CASE WHEN payment_status = 'completed' THEN total ELSE NULL END) as average_order")
            ->first();

        $summary = [
            'total_revenue' => (int) ($orderSummary->total_revenue ?? 0),
            'total_orders' => (int) ($orderSummary->total_orders ?? 0),
            'average_order' => (float) ($orderSummary->average_order ?? 0),
            'new_restaurants' => Restaurant::where('created_at', '>=', now()->subDays($period))->count(),
            'subscription_revenue' => Subscription::where('created_at', '>=', now()->subDays($period))
                ->where('status', 'active')
                ->sum('amount_paid'),
        ];

        return view('pages.super-admin.stats', compact(
            'period',
            'revenueData',
            'ordersByStatus',
            'ordersByType',
            'newRestaurants',
            'subscriptionRevenue',
            'planDistribution',
            'topCities',
            'summary',
            'topDays',
            'weeklyTrend'
        ));
    }

    /**
     * Display detailed revenue statistics.
     */
    public function revenue(Request $request): View
    {
        $period = $request->get('period', '30'); // days
        $startDate = now()->subDays($period);
        $endDate = now();

        // Daily revenue
        $dailyRevenue = Order::withoutGlobalScope('restaurant')
            ->where('payment_status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total) as revenue'),
                DB::raw('COUNT(*) as orders'),
                DB::raw('AVG(total) as average')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Revenue by restaurant
        $revenueByRestaurant = Order::withoutGlobalScope('restaurant')
            ->join('restaurants', 'orders.restaurant_id', '=', 'restaurants.id')
            ->where('orders.payment_status', 'completed')
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->select(
                'restaurants.id',
                'restaurants.name',
                DB::raw('SUM(orders.total) as revenue'),
                DB::raw('COUNT(*) as orders')
            )
            ->groupBy('restaurants.id', 'restaurants.name')
            ->orderByDesc('revenue')
            ->limit(20)
            ->get();

        // Revenue by payment method
        $revenueByPayment = Order::withoutGlobalScope('restaurant')
            ->where('payment_status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select('payment_method', DB::raw('SUM(total) as revenue'), DB::raw('COUNT(*) as orders'))
            ->groupBy('payment_method')
            ->get();

        // Subscription revenue
        $subscriptionRevenue = Subscription::whereBetween('created_at', [$startDate, $endDate])
            ->where('status', 'active')
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(amount_paid) as revenue'),
                DB::raw('COUNT(*) as count')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $totalRevenue = $dailyRevenue->sum('revenue');
        $totalOrders = $dailyRevenue->sum('orders');
        $averageOrder = $totalOrders > 0 ? $totalRevenue / $totalOrders : 0;

        return view('pages.super-admin.stats-revenue', compact(
            'period',
            'dailyRevenue',
            'revenueByRestaurant',
            'revenueByPayment',
            'subscriptionRevenue',
            'totalRevenue',
            'totalOrders',
            'averageOrder',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Display growth statistics.
     */
    public function growth(Request $request): View
    {
        $period = $request->get('period', '90'); // days
        $startDate = now()->subDays($period);
        $endDate = now();

        // Previous period for comparison
        $previousStartDate = $startDate->copy()->subDays($period);
        $previousEndDate = $startDate;

        // Current period stats
        $currentStats = [
            'restaurants' => Restaurant::whereBetween('created_at', [$startDate, $endDate])->count(),
            'orders' => Order::withoutGlobalScope('restaurant')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count(),
            'revenue' => Order::withoutGlobalScope('restaurant')
                ->where('payment_status', 'completed')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->sum('total'),
            'subscriptions' => Subscription::where('status', 'active')
                ->whereBetween('created_at', [$startDate, $endDate])
                ->count(),
        ];

        // Previous period stats
        $previousStats = [
            'restaurants' => Restaurant::whereBetween('created_at', [$previousStartDate, $previousEndDate])->count(),
            'orders' => Order::withoutGlobalScope('restaurant')
                ->whereBetween('created_at', [$previousStartDate, $previousEndDate])
                ->count(),
            'revenue' => Order::withoutGlobalScope('restaurant')
                ->where('payment_status', 'completed')
                ->whereBetween('created_at', [$previousStartDate, $previousEndDate])
                ->sum('total'),
            'subscriptions' => Subscription::where('status', 'active')
                ->whereBetween('created_at', [$previousStartDate, $previousEndDate])
                ->count(),
        ];

        // Calculate growth percentages
        $growth = [];
        foreach ($currentStats as $key => $current) {
            $previous = $previousStats[$key] ?? 0;
            $growth[$key] = $previous > 0 
                ? (($current - $previous) / $previous) * 100 
                : ($current > 0 ? 100 : 0);
        }

        // Growth over time (weekly) — 2 queries instead of 3 per week
        $weeklyOrders = DB::table('orders')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw("YEARWEEK(created_at, 1) as yw")
            ->selectRaw("MIN(DATE(created_at)) as week")
            ->selectRaw("COUNT(*) as orders")
            ->selectRaw("SUM(CASE WHEN payment_status = 'completed' THEN total ELSE 0 END) as revenue")
            ->groupBy('yw')
            ->orderBy('yw')
            ->get()
            ->keyBy('yw');

        $weeklyRestaurants = DB::table('restaurants')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw("YEARWEEK(created_at, 1) as yw")
            ->selectRaw("COUNT(*) as restaurants")
            ->groupBy('yw')
            ->get()
            ->keyBy('yw');

        $weeklyGrowth = [];
        foreach ($weeklyOrders as $yw => $row) {
            $weeklyGrowth[] = [
                'week' => $row->week,
                'restaurants' => (int) ($weeklyRestaurants[$yw]->restaurants ?? 0),
                'orders' => (int) $row->orders,
                'revenue' => (int) $row->revenue,
            ];
        }
        // Add weeks with only restaurants but no orders
        foreach ($weeklyRestaurants as $yw => $row) {
            if (!isset($weeklyOrders[$yw])) {
                $weeklyGrowth[] = [
                    'week' => $startDate->copy()->addWeeks(array_search($yw, array_keys($weeklyRestaurants->toArray())))->format('Y-m-d'),
                    'restaurants' => (int) $row->restaurants,
                    'orders' => 0,
                    'revenue' => 0,
                ];
            }
        }
        usort($weeklyGrowth, fn($a, $b) => strcmp($a['week'], $b['week']));

        return view('pages.super-admin.stats-growth', compact(
            'period',
            'currentStats',
            'previousStats',
            'growth',
            'weeklyGrowth',
            'startDate',
            'endDate'
        ));
    }

    /**
     * Export statistics.
     */
    public function export(Request $request)
    {
        $period = $request->get('period', '30');
        $type = $request->get('type', 'all'); // all, revenue, growth

        return Excel::download(
            new SuperAdminStatsExport($period, $type),
            "statistiques_{$type}_" . now()->format('Ymd_His') . ".xlsx"
        );
    }
}

