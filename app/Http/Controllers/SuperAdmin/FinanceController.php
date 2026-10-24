<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\RestaurantWallet;
use App\Models\PaymentTransaction;
use App\Models\PayoutTransaction;
use App\Models\CommissionLog;
use App\Models\Order;
use App\Jobs\ProcessJekoPayoutJob;
use App\Services\JekoGateway;
use App\Services\PlatformMetrics;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FinanceController extends Controller
{
    public function index(Request $request, PlatformMetrics $metrics)
    {
        // Commissions et reversements calculés depuis orders (platform_commission,
        // payout_status/payout_at) : CommissionLog n'est pas alimenté pour toutes les
        // commandes. Les retraits de wallet (PayoutTransaction) restent affichés tels quels.
        $payoutsDone = $metrics->payouts(PlatformMetrics::PAYOUT_DONE_STATUSES);
        $payoutsTodo = $metrics->payouts(PlatformMetrics::PAYOUT_TODO_STATUSES);

        $stats = [
            'total_wallets_balance' => RestaurantWallet::sum('balance'),
            'total_collected' => PaymentTransaction::where('status', 'completed')->sum('amount'),
            'total_withdrawn' => $payoutsDone['amount'],
            'total_commissions' => $metrics->commissions(),
            'commissions_this_month' => $metrics->commissions(now()->startOfMonth()),
            // Retraits de wallet demandés (onglet « Retraits »)
            'pending_payouts' => PayoutTransaction::where('status', 'pending')->count(),
            'pending_payouts_amount' => PayoutTransaction::where('status', 'pending')->sum('amount'),
            // Reversements de commandes aux restaurants
            'completed_payouts' => $payoutsDone['count'],
            'todo_payouts' => $payoutsTodo['count'],
            'todo_payouts_amount' => $payoutsTodo['amount'],
        ];

        $wallets = RestaurantWallet::with('restaurant')
            ->when($request->search, function ($q) use ($request) {
                $q->whereHas('restaurant', fn($r) => $r->where('name', 'like', "%{$request->search}%"));
            })
            ->when($request->sort === 'balance_asc', fn($q) => $q->orderBy('balance'))
            ->when($request->sort !== 'balance_asc', fn($q) => $q->orderByDesc('balance'))
            ->paginate(20)
            ->withQueryString();

        $recentPayouts = PayoutTransaction::with('restaurant')
            ->latest()
            ->limit(10)
            ->get();

        // Dernières commissions : commandes payées portant une commission plateforme
        $recentCommissions = $metrics->revenueOrders()
            ->where('orders.platform_commission', '>', 0)
            ->with('restaurant:id,name')
            ->latest('orders.paid_at')
            ->limit(10)
            ->get()
            ->map(fn ($order) => (object) [
                'restaurant' => $order->restaurant,
                'order' => $order,
                'amount' => (int) $order->platform_commission,
            ]);

        // Chart data — 6 derniers mois (expression année-mois portable MySQL/SQLite)
        $months = collect(range(5, 0))->map(fn($i) => now()->subMonths($i));
        $monthLabels = $months->map(fn($m) => $m->isoFormat('MMM YY'))->toArray();
        $chartStart = now()->subMonths(5)->startOfMonth();

        $collectByMonth = PaymentTransaction::where('status', 'completed')
            ->where('created_at', '>=', $chartStart)
            ->select(DB::raw($metrics->yearMonthExpression('created_at') . ' as ym'), DB::raw('SUM(amount) as total'))
            ->groupBy('ym')->pluck('total', 'ym');

        // Reversements faits aux restaurants (orders.payout_at)
        $withdrawByMonth = $metrics->revenueOrders()
            ->whereIn('orders.payout_status', PlatformMetrics::PAYOUT_DONE_STATUSES)
            ->where('orders.payout_at', '>=', $chartStart)
            ->select(DB::raw($metrics->yearMonthExpression('orders.payout_at') . ' as ym'), DB::raw('SUM(' . $metrics->payoutAmountExpression() . ') as total'))
            ->groupBy('ym')->pluck('total', 'ym');

        $commissionByMonth = $metrics->revenueOrders()
            ->where('orders.paid_at', '>=', $chartStart)
            ->select(DB::raw($metrics->yearMonthExpression('orders.paid_at') . ' as ym'), DB::raw('SUM(orders.platform_commission) as total'))
            ->groupBy('ym')->pluck('total', 'ym');

        $chartData = [
            'labels'      => $monthLabels,
            'collects'    => $months->map(fn($m) => (float)($collectByMonth[$m->format('Y-m')] ?? 0))->toArray(),
            'withdrawals' => $months->map(fn($m) => (float)($withdrawByMonth[$m->format('Y-m')] ?? 0))->toArray(),
            'commissions' => $months->map(fn($m) => (float)($commissionByMonth[$m->format('Y-m')] ?? 0))->toArray(),
        ];

        return view('pages.super-admin.finance', compact('stats', 'wallets', 'recentPayouts', 'recentCommissions', 'chartData'));
    }

    public function payouts(Request $request)
    {
        $stats = [
            'pending' => PayoutTransaction::where('status', 'pending')->count(),
            'completed' => PayoutTransaction::where('status', 'completed')->count(),
            'failed' => PayoutTransaction::where('status', 'failed')->count(),
            'total_paid' => PayoutTransaction::where('status', 'completed')->sum('amount'),
        ];

        $payouts = PayoutTransaction::with('restaurant')
            ->when($request->status, fn($q) => $q->where('status', $request->status))
            ->when($request->search, function ($q) use ($request) {
                $q->whereHas('restaurant', fn($r) => $r->where('name', 'like', "%{$request->search}%"));
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('pages.super-admin.payouts', compact('stats', 'payouts'));
    }

    /**
     * Commandes payées via Jeko/Wave dont le reversement a échoué ou n'a pas été tenté
     */
    public function failedPayouts(Request $request)
    {
        // Commandes payées via Jeko avec reversement échoué dans les logs
        // = commandes Jeko payées où le restaurant est intégré mais payout non fait
        // Commandes Jeko/Wave : en attente OU échouées OU sans statut payout (anciennes)
        $failedOrders = Order::with(['restaurant.jekoSubMerchant'])
            ->whereIn('payment_method', ['jeko', 'wave'])
            ->whereNotNull('paid_at')
            ->whereNotIn('status', ['cancelled', 'refunded', 'draft'])
            ->where('paid_at', '>=', now()->subDays(30))
            ->where(fn($q) => $q
                ->whereNull('payout_status')
                ->orWhereIn('payout_status', ['pending', 'failed'])
            )
            ->when($request->restaurant, fn($q) => $q->whereHas('restaurant', fn($r) =>
                $r->where('name', 'like', "%{$request->restaurant}%")
            ))
            ->latest('paid_at')
            ->paginate(30)
            ->withQueryString();

        $baseQ = Order::whereIn('payment_method', ['jeko', 'wave'])
            ->whereNotNull('paid_at')
            ->whereNotIn('status', ['cancelled', 'refunded', 'draft'])
            ->where('paid_at', '>=', now()->subDays(30));

        $stats = [
            'total_orders'     => (clone $baseQ)->where(fn($q) => $q->whereNull('payout_status')->orWhereIn('payout_status', ['pending', 'failed']))->count(),
            'total_amount'     => (clone $baseQ)->where(fn($q) => $q->whereNull('payout_status')->orWhereIn('payout_status', ['pending', 'failed']))->sum('total'),
            'completed_today'  => (clone $baseQ)->whereIn('payout_status', ['completed', 'manual'])->where('payout_at', '>=', today())->count(),
        ];

        return view('pages.super-admin.failed-payouts', compact('failedOrders', 'stats'));
    }

    /**
     * Relancer le payout Jeko pour une commande spécifique
     */
    public function retryPayout(Request $request, Order $order)
    {
        $restaurant = $order->restaurant;
        $subMerchant = $restaurant?->jekoSubMerchant;

        if (!$subMerchant || !$subMerchant->isIntegrated()) {
            return back()->with('error', "Le restaurant {$restaurant?->name} n'est pas intégré Jeko.");
        }

        if (!in_array($order->payment_method, ['jeko', 'wave']) || !$order->paid_at) {
            return back()->with('error', "La commande #{$order->reference} n'est pas payée via Jeko/Wave.");
        }

        try {
            ProcessJekoPayoutJob::dispatch($order);
            Log::channel('payments')->info('Manual payout retry dispatched by super-admin', [
                'order_id'      => $order->id,
                'restaurant_id' => $restaurant->id,
                'dispatched_by' => auth()->id(),
            ]);
            return back()->with('success', "Reversement relancé pour la commande #{$order->reference} ({$restaurant->name}).");
        } catch (\Exception $e) {
            return back()->with('error', "Erreur lors du lancement : " . $e->getMessage());
        }
    }

    /**
     * Relancer les payouts pour TOUTES les commandes d'un restaurant (30 derniers jours)
     */
    public function retryAllPayouts(Request $request)
    {
        $request->validate(['restaurant_id' => 'required|integer|exists:restaurants,id']);

        // Ne relancer QUE les commandes non encore reversées (éviter les double-virements)
        $orders = Order::where('restaurant_id', $request->restaurant_id)
            ->whereIn('payment_method', ['jeko', 'wave'])
            ->whereNotNull('paid_at')
            ->whereNotIn('status', ['cancelled', 'refunded', 'draft'])
            ->where('paid_at', '>=', now()->subDays(30))
            ->where(fn($q) => $q->whereNull('payout_status')->orWhereIn('payout_status', ['pending', 'failed']))
            ->get();

        $count = 0;
        foreach ($orders as $order) {
            try {
                ProcessJekoPayoutJob::dispatch($order);
                $count++;
            } catch (\Exception $e) {
                Log::channel('payments')->error('Bulk payout retry failed', ['order_id' => $order->id, 'error' => $e->getMessage()]);
            }
        }

        Log::channel('payments')->info('Bulk payout retry by super-admin', [
            'restaurant_id' => $request->restaurant_id,
            'count'         => $count,
            'by'            => auth()->id(),
        ]);

        return back()->with('success', "{$count} reversements relancés.");
    }

    /**
     * Marquer un payout comme fait manuellement (virement fait hors système)
     */
    public function markPayoutManual(Request $request, Order $order)
    {
        $request->validate(['note' => 'nullable|string|max:255']);

        $restaurant = $order->restaurant;

        Log::channel('payments')->info('Payout marked as manual by super-admin', [
            'order_id'      => $order->id,
            'restaurant_id' => $restaurant?->id,
            'amount'        => $order->total,
            'note'          => $request->note,
            'marked_by'     => auth()->id(),
        ]);

        $order->payout_status    = 'manual';
        $order->payout_at        = now();
        $order->payout_reference = 'MANUAL-' . auth()->id();
        $order->payment_metadata = array_merge($order->payment_metadata ?? [], [
            'manual_payout'      => true,
            'manual_payout_by'   => auth()->id(),
            'manual_payout_at'   => now()->toIso8601String(),
            'manual_payout_note' => $request->note,
        ]);
        $order->save();

        return back()->with('success', "Commande #{$order->reference} marquée comme reversée manuellement.");
    }

    public function commissions(Request $request, PlatformMetrics $metrics)
    {
        // Totaux depuis orders.platform_commission (commandes payées non annulées, paid_at) ;
        // la liste détaillée ci-dessous reste celle de CommissionLog.
        $withCommission = $metrics->revenueOrders()->where('orders.platform_commission', '>', 0);
        $agg = (clone $withCommission)
            ->selectRaw('COUNT(*) as cnt, SUM(orders.platform_commission) as commission, SUM(orders.total) as volume')
            ->toBase()
            ->first();

        $stats = [
            'total' => (int) ($agg->commission ?? 0),
            'this_month' => $metrics->commissions(now()->startOfMonth()),
            'last_month' => $metrics->commissions(now()->subMonthNoOverflow()->startOfMonth(), now()->subMonthNoOverflow()->endOfMonth()),
            // Taux effectif = commissions / volume des commandes concernées
            'avg_rate' => ($agg->volume ?? 0) > 0 ? $agg->commission / $agg->volume * 100 : 0,
            'total_orders' => (int) ($agg->cnt ?? 0),
        ];

        $commissions = CommissionLog::with(['restaurant', 'order'])
            ->when($request->search, function ($q) use ($request) {
                $q->whereHas('restaurant', fn($r) => $r->where('name', 'like', "%{$request->search}%"));
            })
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('pages.super-admin.commissions', compact('stats', 'commissions'));
    }
}
