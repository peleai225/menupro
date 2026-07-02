<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\RestaurantWallet;
use App\Models\PaymentTransaction;
use App\Models\PayoutTransaction;
use App\Models\CommissionLog;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class FinanceController extends Controller
{
    public function index(Request $request)
    {
        $stats = [
            'total_wallets_balance' => RestaurantWallet::sum('balance'),
            'total_collected' => PaymentTransaction::where('status', 'completed')->sum('amount'),
            'total_withdrawn' => PayoutTransaction::where('status', 'completed')->sum('amount'),
            'total_commissions' => CommissionLog::sum('amount'),
            'commissions_this_month' => CommissionLog::where('created_at', '>=', now()->startOfMonth())->sum('amount'),
            'pending_payouts' => PayoutTransaction::where('status', 'pending')->count(),
            'pending_payouts_amount' => PayoutTransaction::where('status', 'pending')->sum('amount'),
            'completed_payouts' => PayoutTransaction::where('status', 'completed')->count(),
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

        $recentCommissions = CommissionLog::with(['restaurant', 'order'])
            ->latest()
            ->limit(10)
            ->get();

        // Chart data — 6 derniers mois
        $months = collect(range(5, 0))->map(fn($i) => now()->subMonths($i));
        $monthLabels = $months->map(fn($m) => $m->isoFormat('MMM YY'))->toArray();

        $collectByMonth = PaymentTransaction::where('status', 'completed')
            ->where('created_at', '>=', now()->subMonths(5)->startOfMonth())
            ->select(DB::raw("DATE_FORMAT(created_at,'%Y-%m') as ym"), DB::raw('SUM(amount) as total'))
            ->groupBy('ym')->pluck('total', 'ym');

        $withdrawByMonth = PayoutTransaction::where('status', 'completed')
            ->where('created_at', '>=', now()->subMonths(5)->startOfMonth())
            ->select(DB::raw("DATE_FORMAT(created_at,'%Y-%m') as ym"), DB::raw('SUM(amount) as total'))
            ->groupBy('ym')->pluck('total', 'ym');

        $commissionByMonth = CommissionLog::where('created_at', '>=', now()->subMonths(5)->startOfMonth())
            ->select(DB::raw("DATE_FORMAT(created_at,'%Y-%m') as ym"), DB::raw('SUM(amount) as total'))
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

    public function commissions(Request $request)
    {
        $stats = [
            'total' => CommissionLog::sum('amount'),
            'this_month' => CommissionLog::where('created_at', '>=', now()->startOfMonth())->sum('amount'),
            'last_month' => CommissionLog::whereBetween('created_at', [now()->subMonth()->startOfMonth(), now()->subMonth()->endOfMonth()])->sum('amount'),
            'avg_rate' => CommissionLog::avg('commission_rate') ?? 0,
            'total_orders' => CommissionLog::count(),
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
