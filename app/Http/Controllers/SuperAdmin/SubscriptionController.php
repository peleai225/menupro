<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\SubscriptionStatus;
use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Restaurant;
use App\Models\Subscription;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class SubscriptionController extends Controller
{
    /**
     * Display a listing of subscriptions.
     */
    public function index(Request $request): View
    {
        $query = Subscription::with(['restaurant.owner', 'plan', 'addons']);

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('plan')) {
            $query->where('plan_id', $request->plan);
        }

        if ($request->filled('restaurant')) {
            $query->where('restaurant_id', $request->restaurant);
        }

        if ($request->filled('billing_period')) {
            $query->where('billing_period', $request->billing_period);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->whereHas('restaurant', function ($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('email', 'like', "%{$search}%");
                })
                ->orWhere('payment_reference', 'like', "%{$search}%");
            });
        }

        // Date filters
        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        // Expiring filter
        if ($request->filled('expiring')) {
            if ($request->expiring === 'soon') {
                $query->where('status', SubscriptionStatus::ACTIVE)
                      ->where('ends_at', '<=', now()->addDays(7))
                      ->where('ends_at', '>', now());
            } elseif ($request->expiring === 'expired') {
                $query->where(function ($q) {
                    $q->where('status', SubscriptionStatus::EXPIRED)
                      ->orWhere('ends_at', '<', now());
                });
            }
        }

        $subscriptions = $query->latest('created_at')->paginate(25)->withQueryString();

        // Statistics
        $stats = [
            'total' => Subscription::count(),
            'active' => Subscription::where('status', SubscriptionStatus::ACTIVE)->count(),
            'expired' => Subscription::where('status', SubscriptionStatus::EXPIRED)->count(),
            'pending' => Subscription::where('status', SubscriptionStatus::PENDING)->count(),
            'expiring_soon' => Subscription::where('status', SubscriptionStatus::ACTIVE)
                ->where('ends_at', '<=', now()->addDays(7))
                ->where('ends_at', '>', now())
                ->count(),
            'total_revenue' => Subscription::where('status', SubscriptionStatus::ACTIVE)
                ->sum('amount_paid'),
            'monthly_revenue' => Subscription::where('status', SubscriptionStatus::ACTIVE)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('amount_paid'),
        ];

        // Revenue by billing period
        $revenueByPeriod = Subscription::where('status', SubscriptionStatus::ACTIVE)
            ->select('billing_period', DB::raw('SUM(amount_paid) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('billing_period')
            ->get();

        // Revenue by plan
        $revenueByPlan = Subscription::where('status', SubscriptionStatus::ACTIVE)
            ->join('plans', 'subscriptions.plan_id', '=', 'plans.id')
            ->select('plans.name', 'plans.id', DB::raw('SUM(subscriptions.amount_paid) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('plans.id', 'plans.name')
            ->get();

        // Retention stats
        $retentionStats = [
            'average_duration' => Subscription::where('status', SubscriptionStatus::ACTIVE)
                ->selectRaw('AVG(DATEDIFF(ends_at, starts_at)) as avg_days')
                ->value('avg_days'),
            'renewal_rate' => $this->calculateRenewalRate(),
            'churn_rate' => $this->calculateChurnRate(),
        ];

        $plans = Plan::active()->ordered()->get();
        $restaurants = Restaurant::active()->orderBy('name')->get(['id', 'name']);

        return view('pages.super-admin.subscriptions', compact(
            'subscriptions',
            'stats',
            'revenueByPeriod',
            'revenueByPlan',
            'retentionStats',
            'plans',
            'restaurants'
        ));
    }

    /**
     * Display the specified subscription.
     */
    public function show(Subscription $subscription): View
    {
        $subscription->load(['restaurant.owner', 'plan', 'addons']);

        // Payment history for this restaurant
        $paymentHistory = Subscription::where('restaurant_id', $subscription->restaurant_id)
            ->with('plan')
            ->latest()
            ->get();

        return view('pages.super-admin.subscription-show', compact(
            'subscription',
            'paymentHistory'
        ));
    }

    /**
     * Calculate renewal rate
     */
    private function calculateRenewalRate(): float
    {
        $totalExpired = Subscription::where('status', SubscriptionStatus::EXPIRED)->count();
        
        if ($totalExpired === 0) {
            return 0;
        }

        // Count restaurants that have renewed (have multiple subscriptions)
        $renewedCount = DB::table('subscriptions')
            ->select('restaurant_id')
            ->where('status', SubscriptionStatus::EXPIRED)
            ->groupBy('restaurant_id')
            ->havingRaw('COUNT(*) > 1')
            ->count();

        return round(($renewedCount / $totalExpired) * 100, 2);
    }

    /**
     * Calculate churn rate
     */
    private function calculateChurnRate(): float
    {
        $totalActive = Subscription::where('status', SubscriptionStatus::ACTIVE)->count();
        $expiredThisMonth = Subscription::where('status', SubscriptionStatus::EXPIRED)
            ->whereMonth('ends_at', now()->month)
            ->whereYear('ends_at', now()->year)
            ->count();

        if ($totalActive === 0) {
            return 0;
        }

        return round(($expiredThisMonth / $totalActive) * 100, 2);
    }

    /**
     * Export subscriptions to CSV
     */
    public function export(Request $request)
    {
        $query = Subscription::with(['restaurant', 'plan']);

        // Apply same filters as index
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('plan')) {
            $query->where('plan_id', $request->plan);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $subscriptions = $query->latest()->get();

        $filename = 'abonnements_' . now()->format('Y-m-d_H-i-s') . '.csv';

        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"{$filename}\"",
        ];

        $callback = function() use ($subscriptions) {
            $file = fopen('php://output', 'w');
            
            // Headers
            fputcsv($file, [
                'ID',
                'Restaurant',
                'Plan',
                'Statut',
                'Période',
                'Montant',
                'Réduction',
                'Date début',
                'Date fin',
                'Référence paiement',
                'Méthode paiement',
                'Créé le'
            ]);

            // Data
            foreach ($subscriptions as $subscription) {
                fputcsv($file, [
                    $subscription->id,
                    $subscription->restaurant->name,
                    $subscription->plan->name,
                    $subscription->status->value,
                    $subscription->billing_period ?? 'N/A',
                    number_format($subscription->amount_paid, 0, ',', ' ') . ' FCFA',
                    ($subscription->discount_percentage ?? 0) . '%',
                    $subscription->starts_at->format('d/m/Y'),
                    $subscription->ends_at->format('d/m/Y'),
                    $subscription->payment_reference ?? 'N/A',
                    $subscription->payment_method ?? 'N/A',
                    $subscription->created_at->format('d/m/Y H:i'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
