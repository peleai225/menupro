<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Services\PlatformMetrics;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\SuperAdminStatsExport;

class StatsController extends Controller
{
    /** Périodes proposées sur la page croissance (inclut 6 mois). */
    private const GROWTH_PERIODS = [7, 30, 90, 180, 365];

    public function __construct(private PlatformMetrics $metrics)
    {
    }

    /**
     * Display statistics dashboard.
     */
    public function index(Request $request): View
    {
        $period = $this->metrics->period($request->get('period'), 30); // days
        $from = now()->subDays($period);

        // Volume payé (GMV) dans le temps : commandes payées non annulées, par date de paiement
        $revenueData = $this->metrics->revenueOrders()
            ->where('orders.paid_at', '>=', $from)
            ->selectRaw('DATE(orders.paid_at) as date, SUM(orders.total) as revenue, COUNT(*) as orders')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Orders by status (commandes passées)
        $ordersByStatus = $this->metrics->placedOrders()
            ->where('orders.created_at', '>=', $from)
            ->select('orders.status', DB::raw('COUNT(*) as count'))
            ->groupBy('orders.status')
            ->get();

        // Orders by type
        $ordersByType = $this->metrics->placedOrders()
            ->where('orders.created_at', '>=', $from)
            ->select('orders.type', DB::raw('COUNT(*) as count'))
            ->groupBy('orders.type')
            ->get();

        // New restaurants over time (hors démo / supprimés)
        $newRestaurants = $this->metrics->restaurants()
            ->where('created_at', '>=', $from)
            ->selectRaw('DATE(created_at) as date, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Commandes passées par jour (sert au top 10 et à la tendance hebdomadaire)
        $ordersPerDay = $this->metrics->placedOrders()
            ->where('orders.created_at', '>=', $from)
            ->selectRaw('DATE(orders.created_at) as date, COUNT(*) as orders')
            ->groupBy('date')
            ->orderBy('date')
            ->toBase()->get();

        // Top 10 days by order count for bar chart
        $topDays = $ordersPerDay->sortByDesc('orders')->take(10)->values();

        // Weekly order trend (semaines ISO commençant le lundi, regroupées en PHP)
        $weeklyTrend = collect($this->metrics->groupDailyRowsByWeek($ordersPerDay, ['orders']))
            ->map(fn ($row, $monday) => (object) ['week_start' => $monday, 'orders' => (int) $row['orders']])
            ->values();

        // Revenus d'abonnements encaissés (date de paiement, repli date de création)
        $subscriptionRevenue = $this->metrics->collectedSubscriptionsBetween($from, now())
            ->selectRaw('DATE(' . $this->metrics->subscriptionPaidAtColumn() . ') as date, SUM(subscriptions.amount_paid) as revenue')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Répartition par plan : dernier abonnement valide de chaque restaurant
        $planDistribution = $this->metrics->planDistribution();

        // Top cities
        $topCities = $this->metrics->restaurants()
            ->whereNotNull('city')
            ->select('city', DB::raw('COUNT(*) as count'))
            ->groupBy('city')
            ->orderByDesc('count')
            ->limit(10)
            ->get();

        $paidOrdersCount = $this->metrics->revenueOrders()->where('orders.paid_at', '>=', $from)->count();
        $gmv = $this->metrics->gmv($from);

        $summary = [
            'total_revenue' => $gmv,
            'total_orders' => $this->metrics->placedOrders()->where('orders.created_at', '>=', $from)->count(),
            'cancelled_orders' => $this->metrics->placedOrders()
                ->where('orders.created_at', '>=', $from)
                ->whereIn('orders.status', [OrderStatus::CANCELLED, OrderStatus::REFUNDED])
                ->count(),
            'average_order' => $paidOrdersCount > 0 ? $gmv / $paidOrdersCount : 0,
            'new_restaurants' => $this->metrics->restaurants()->where('created_at', '>=', $from)->count(),
            'subscription_revenue' => $this->metrics->subscriptionRevenue($from, now()),
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
        $period = $this->metrics->period($request->get('period'), 30); // days
        $startDate = now()->subDays($period);
        $endDate = now();

        // Volume payé par jour (commandes payées non annulées, par date de paiement)
        $dailyRevenue = $this->metrics->revenueOrders()
            ->whereBetween('orders.paid_at', [$startDate, $endDate])
            ->selectRaw('DATE(orders.paid_at) as date, SUM(orders.total) as revenue, COUNT(*) as orders, AVG(orders.total) as average')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        // Revenue by restaurant (agrégé sur orders puis noms chargés : pas de jointure
        // qui rendrait ambiguë la colonne status du scope revenue())
        $byRestaurant = $this->metrics->revenueOrders()
            ->whereBetween('orders.paid_at', [$startDate, $endDate])
            ->select('orders.restaurant_id', DB::raw('SUM(orders.total) as revenue'), DB::raw('COUNT(*) as orders'))
            ->groupBy('orders.restaurant_id')
            ->orderByDesc('revenue')
            ->limit(20)
            ->get();
        $names = Restaurant::whereIn('id', $byRestaurant->pluck('restaurant_id'))->pluck('name', 'id');
        $revenueByRestaurant = $byRestaurant->map(fn ($row) => (object) [
            'id' => $row->restaurant_id,
            'name' => $names[$row->restaurant_id] ?? '—',
            'revenue' => (int) $row->revenue,
            'orders' => (int) $row->orders,
        ]);

        // Revenue by payment method
        $revenueByPayment = $this->metrics->revenueOrders()
            ->whereBetween('orders.paid_at', [$startDate, $endDate])
            ->select('orders.payment_method', DB::raw('SUM(orders.total) as revenue'), DB::raw('COUNT(*) as orders'))
            ->groupBy('orders.payment_method')
            ->get();

        // Revenus d'abonnements encaissés
        $subscriptionRevenue = $this->metrics->collectedSubscriptionsBetween($startDate, $endDate)
            ->selectRaw('DATE(' . $this->metrics->subscriptionPaidAtColumn() . ') as date, SUM(subscriptions.amount_paid) as revenue, COUNT(*) as count')
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        $totalRevenue = (int) $dailyRevenue->sum('revenue');
        $totalOrders = (int) $dailyRevenue->sum('orders');
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
        $period = $this->metrics->period($request->get('period'), 90, self::GROWTH_PERIODS); // days
        $endDate = now();
        $startDate = $endDate->copy()->subDays($period);

        // Période précédente de même durée, juste avant (bornes : [début, fin[ pour ne
        // pas compter deux fois la seconde de jonction)
        $previousStartDate = $startDate->copy()->subDays($period);
        $previousEndDate = $startDate->copy()->subSecond();

        $currentStats = $this->periodStats($startDate, $endDate);
        $previousStats = $this->periodStats($previousStartDate, $previousEndDate);

        // Évolution en % ; null quand la période précédente vaut 0 (affiché « nouveau » / « — »)
        $growth = [];
        foreach ($currentStats as $key => $current) {
            $growth[$key] = $this->metrics->growth($current, $previousStats[$key] ?? 0);
        }

        // Évolution hebdomadaire : semaines ISO (lundi), libellé = date du lundi
        $ordersPerDay = $this->metrics->placedOrders()
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->selectRaw('DATE(orders.created_at) as date, COUNT(*) as orders')
            ->groupBy('date')
            ->toBase()->get();

        $revenuePerDay = $this->metrics->revenueOrders()
            ->whereBetween('orders.paid_at', [$startDate, $endDate])
            ->selectRaw('DATE(orders.paid_at) as date, SUM(orders.total) as revenue')
            ->groupBy('date')
            ->toBase()->get();

        $restaurantsPerDay = $this->metrics->restaurants()
            ->whereBetween('created_at', [$startDate, $endDate])
            ->selectRaw('DATE(created_at) as date, COUNT(*) as restaurants')
            ->groupBy('date')
            ->toBase()->get();

        $weeklyOrders = $this->metrics->groupDailyRowsByWeek($ordersPerDay, ['orders']);
        $weeklyRevenue = $this->metrics->groupDailyRowsByWeek($revenuePerDay, ['revenue']);
        $weeklyRestaurants = $this->metrics->groupDailyRowsByWeek($restaurantsPerDay, ['restaurants']);

        $weeklyGrowth = [];
        $monday = $startDate->copy()->startOfWeek(CarbonInterface::MONDAY);
        while ($monday->lte($endDate)) {
            $key = $monday->toDateString();
            $weeklyGrowth[] = [
                'week' => $key,
                'restaurants' => (int) ($weeklyRestaurants[$key]['restaurants'] ?? 0),
                'orders' => (int) ($weeklyOrders[$key]['orders'] ?? 0),
                'revenue' => (int) ($weeklyRevenue[$key]['revenue'] ?? 0),
            ];
            $monday->addWeek();
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
     * Indicateurs d'une période (mêmes définitions pour la période courante et la précédente).
     */
    private function periodStats(CarbonInterface $from, CarbonInterface $to): array
    {
        return [
            'restaurants' => $this->metrics->restaurants()->whereBetween('created_at', [$from, $to])->count(),
            'orders' => $this->metrics->placedOrders()->whereBetween('orders.created_at', [$from, $to])->count(),
            'revenue' => $this->metrics->gmv($from, $to),
            // Paiements d'abonnement encaissés sur la période
            'subscriptions' => $this->metrics->collectedSubscriptionsBetween($from, $to)->count(),
        ];
    }

    /**
     * Export statistics.
     */
    public function export(Request $request)
    {
        $period = $this->metrics->period($request->get('period'), 30, self::GROWTH_PERIODS);
        $type = in_array($request->get('type'), ['all', 'revenue', 'growth'], true) ? $request->get('type') : 'all';

        return Excel::download(
            new SuperAdminStatsExport($period, $type),
            "statistiques_{$type}_" . now()->format('Ymd_His') . ".xlsx"
        );
    }
}
