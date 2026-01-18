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

        // Summary stats
        $summary = [
            'total_revenue' => Order::withoutGlobalScope('restaurant')
                ->where('payment_status', 'completed')
                ->where('created_at', '>=', now()->subDays($period))
                ->sum('total'),
            'total_orders' => Order::withoutGlobalScope('restaurant')
                ->where('created_at', '>=', now()->subDays($period))
                ->count(),
            'average_order' => Order::withoutGlobalScope('restaurant')
                ->where('payment_status', 'completed')
                ->where('created_at', '>=', now()->subDays($period))
                ->avg('total') ?? 0,
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
            'summary'
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

        // Growth over time (weekly)
        $weeklyGrowth = [];
        $weeks = ceil($period / 7);
        for ($i = 0; $i < $weeks; $i++) {
            $weekStart = $startDate->copy()->addWeeks($i);
            $weekEnd = $weekStart->copy()->addWeek();
            
            $weekStats = [
                'week' => $weekStart->format('Y-m-d'),
                'restaurants' => Restaurant::whereBetween('created_at', [$weekStart, $weekEnd])->count(),
                'orders' => Order::withoutGlobalScope('restaurant')
                    ->whereBetween('created_at', [$weekStart, $weekEnd])
                    ->count(),
                'revenue' => Order::withoutGlobalScope('restaurant')
                    ->where('payment_status', 'completed')
                    ->whereBetween('created_at', [$weekStart, $weekEnd])
                    ->sum('total'),
            ];
            
            $weeklyGrowth[] = $weekStats;
        }

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

