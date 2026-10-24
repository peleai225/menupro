<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\SubscriptionStatus;
use App\Http\Controllers\Controller;
use App\Models\Plan;
use App\Models\Restaurant;
use App\Models\Subscription;
use App\Services\PlatformMetrics;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class SubscriptionController extends Controller
{
    /**
     * Display a listing of subscriptions.
     */
    public function index(Request $request, PlatformMetrics $metrics): View
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

        // Statistiques — en RESTAURANTS distincts (plus de doublons de lignes d'abonnement),
        // hors démo et supprimés, définitions PlatformMetrics.
        $counts = $metrics->restaurantSubscriptionCounts();
        $latestPaid = $metrics->latestPaidValidSubscriptions();

        $stats = [
            // Restaurants ayant eu au moins un abonnement
            'total' => (int) $metrics->subscriptions()->distinct()->count('subscriptions.restaurant_id'),
            // Abonnement payant/actif valide (ends_at > maintenant) — essais à part
            'active' => $counts['paying'],
            'trial' => $counts['trial'],
            // Ni abonnement ni essai valide (hors restaurants en attente de validation)
            'expired' => $counts['expired'],
            // Restaurants ayant un paiement d'abonnement en attente (lancé, non confirmé)
            'pending' => (int) $metrics->subscriptions()
                ->where('subscriptions.status', SubscriptionStatus::PENDING->value)
                ->distinct()
                ->count('subscriptions.restaurant_id'),
            // Dernier abonnement valide (essais inclus) finissant sous 7 jours
            'expiring_soon' => $metrics->expiringSubscriptions(7)->count(),
            // Encaissé : status active|expired, montant > 0, référence de paiement
            'total_revenue' => $metrics->subscriptionRevenue(),
            'monthly_revenue' => $metrics->subscriptionRevenue(now()->startOfMonth(), now()),
            'mrr' => $metrics->mrr(),
        ];

        // Revenus encaissés par période de facturation
        $revenueByPeriod = $metrics->collectedSubscriptions()
            ->select('subscriptions.billing_period', DB::raw('SUM(subscriptions.amount_paid) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('subscriptions.billing_period')
            ->get();

        // Revenus encaissés par plan
        $revenueByPlan = $metrics->collectedSubscriptions()
            ->join('plans', 'subscriptions.plan_id', '=', 'plans.id')
            ->select('plans.name', 'plans.id', DB::raw('SUM(subscriptions.amount_paid) as total'), DB::raw('COUNT(*) as count'))
            ->groupBy('plans.id', 'plans.name')
            ->get();

        // Rétention (unités : restaurants, fenêtre 30 jours — voir PlatformMetrics::retentionRates)
        $retention = $metrics->retentionRates(30);
        $retentionStats = [
            // Durée moyenne (jours) des abonnements payants en cours, calculée en PHP (DATEDIFF = MySQL)
            'average_duration' => $latestPaid->isEmpty()
                ? null
                : $latestPaid->avg(fn ($s) => ($s->starts_at ?? $s->created_at)->diffInDays($s->ends_at, true)),
            'renewal_rate' => $retention['renewal_rate'],
            'renewal_base' => $retention['renewal_base'],
            'churn_rate' => $retention['churn_rate'],
            'churn_base' => $retention['churn_base'],
            'window_days' => $retention['window_days'],
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
                    $subscription->starts_at?->format('d/m/Y') ?? 'Non démarré',
                    $subscription->ends_at?->format('d/m/Y') ?? 'Non démarré',
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
