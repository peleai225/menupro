<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\OrderRefund;
use App\Models\Subscription;
use App\Services\PlatformMetrics;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class TransactionController extends Controller
{
    public function index(Request $request, PlatformMetrics $metrics): View
    {
        $type = $request->get('type', 'all');
        $status = $request->get('status', '');
        $dateFrom = $request->get('date_from');
        $dateTo = $request->get('date_to');
        $search = $request->get('search');

        // Stats — deux totaux SÉPARÉS : le volume des commandes appartient aux restaurants
        // (GMV), seuls les abonnements encaissés sont des revenus MenuPro. Hors démo.
        $monthStart = now()->startOfMonth();
        $stats = [
            // Abonnements réellement encaissés (status active|expired, montant > 0, référence)
            'subscriptions_revenue' => $metrics->subscriptionRevenue(),
            'subscriptions_this_month' => $metrics->subscriptionRevenue($monthStart, now()),
            // Commandes payées non annulées/remboursées (sur paid_at)
            'orders_revenue' => $metrics->gmv(),
            'orders_this_month' => $metrics->gmv($monthStart),
            'refunds_total' => OrderRefund::where('status', 'completed')
                ->whereIn('order_id', $metrics->orders()->select('orders.id'))
                ->sum('amount'),
            // Paiements de commandes en cours (hors brouillons abandonnés)
            'pending_payments' => $metrics->placedOrders()
                ->where('orders.payment_status', 'pending')
                ->whereNotIn('orders.status', [\App\Enums\OrderStatus::CANCELLED, \App\Enums\OrderStatus::REFUNDED])
                ->sum('orders.total'),
        ];

        // Build transactions query - combine subscriptions and orders
        $transactions = collect();

        if ($type === 'all' || $type === 'subscription') {
            $subscriptionsQuery = Subscription::with(['restaurant', 'plan'])
                ->whereNotNull('amount_paid')
                ->where('amount_paid', '>', 0);

            if ($dateFrom) {
                $subscriptionsQuery->whereDate('created_at', '>=', $dateFrom);
            }
            if ($dateTo) {
                $subscriptionsQuery->whereDate('created_at', '<=', $dateTo);
            }
            if ($search) {
                // Regroupé : un orWhere libre annulait les autres filtres (montant, dates)
                $subscriptionsQuery->where(function ($q) use ($search) {
                    $q->whereHas('restaurant', fn ($r) => $r->where('name', 'like', "%{$search}%"))
                      ->orWhere('payment_reference', 'like', "%{$search}%");
                });
            }

            $subscriptions = $subscriptionsQuery->get()->map(function ($sub) {
                return [
                    'id' => 'SUB-' . $sub->id,
                    'type' => 'subscription',
                    'type_label' => 'Abonnement',
                    'reference' => $sub->payment_reference ?? 'N/A',
                    'restaurant' => $sub->restaurant,
                    'restaurant_name' => $sub->restaurant->name ?? 'N/A',
                    'amount' => $sub->amount_paid,
                    'status' => $sub->status->value,
                    'status_label' => $sub->status->label(),
                    'payment_method' => $sub->payment_method ?? 'N/A',
                    'description' => 'Plan ' . ($sub->plan->name ?? 'N/A'),
                    'created_at' => $sub->created_at,
                    'model' => $sub,
                ];
            });

            $transactions = $transactions->merge($subscriptions);
        }

        if ($type === 'all' || $type === 'order') {
            $ordersQuery = Order::withoutGlobalScope('restaurant')
                ->with('restaurant')
                ->whereIn('payment_status', ['completed', 'pending', 'failed']);

            if ($status === 'completed') {
                $ordersQuery->where('payment_status', 'completed');
            } elseif ($status === 'pending') {
                $ordersQuery->where('payment_status', 'pending');
            } elseif ($status === 'failed') {
                $ordersQuery->where('payment_status', 'failed');
            }

            if ($dateFrom) {
                $ordersQuery->whereDate('created_at', '>=', $dateFrom);
            }
            if ($dateTo) {
                $ordersQuery->whereDate('created_at', '<=', $dateTo);
            }
            if ($search) {
                $ordersQuery->where(function ($q) use ($search) {
                    $q->where('reference', 'like', "%{$search}%")
                      ->orWhere('customer_name', 'like', "%{$search}%")
                      ->orWhere('payment_reference', 'like', "%{$search}%");
                });
            }

            $orders = $ordersQuery->get()->map(function ($order) {
                return [
                    'id' => 'ORD-' . $order->id,
                    'type' => 'order',
                    'type_label' => 'Commande',
                    'reference' => $order->reference,
                    'restaurant' => $order->restaurant,
                    'restaurant_name' => $order->restaurant->name ?? 'N/A',
                    'amount' => $order->total,
                    'status' => $order->payment_status->value ?? 'pending',
                    'status_label' => $order->payment_status?->label() ?? 'En attente',
                    'payment_method' => $order->payment_method ?? 'N/A',
                    'description' => 'Commande #' . $order->reference,
                    'created_at' => $order->created_at,
                    'model' => $order,
                ];
            });

            $transactions = $transactions->merge($orders);
        }

        if ($type === 'all' || $type === 'refund') {
            $refundsQuery = OrderRefund::with(['order.restaurant']);

            if ($status === 'completed') {
                $refundsQuery->where('status', 'completed');
            } elseif ($status === 'pending') {
                $refundsQuery->where('status', 'pending');
            }

            if ($dateFrom) {
                $refundsQuery->whereDate('created_at', '>=', $dateFrom);
            }
            if ($dateTo) {
                $refundsQuery->whereDate('created_at', '<=', $dateTo);
            }

            $refunds = $refundsQuery->get()->map(function ($refund) {
                return [
                    'id' => 'REF-' . $refund->id,
                    'type' => 'refund',
                    'type_label' => 'Remboursement',
                    'reference' => $refund->reference ?? 'N/A',
                    'restaurant' => $refund->order?->restaurant,
                    'restaurant_name' => $refund->order?->restaurant?->name ?? 'N/A',
                    'amount' => -$refund->amount, // Negative for refunds
                    'status' => $refund->status,
                    'status_label' => ucfirst($refund->status),
                    'payment_method' => $refund->method ?? 'N/A',
                    'description' => 'Remboursement commande #' . ($refund->order?->reference ?? 'N/A'),
                    'created_at' => $refund->created_at,
                    'model' => $refund,
                ];
            });

            $transactions = $transactions->merge($refunds);
        }

        // Sort by date descending and paginate manually
        $transactions = $transactions->sortByDesc('created_at')->values();
        
        $page = $request->get('page', 1);
        $perPage = 20;
        $total = $transactions->count();
        $transactions = $transactions->forPage($page, $perPage);

        // Courbes des 30 derniers jours — 2 requêtes groupées au lieu de 60.
        // Commandes : payées non annulées (paid_at) ; abonnements : encaissés (paid_at, repli created_at).
        $chartStart = now()->subDays(29)->startOfDay();
        $ordersByDay = $metrics->revenueOrders()
            ->where('orders.paid_at', '>=', $chartStart)
            ->selectRaw('DATE(orders.paid_at) as day, SUM(orders.total) as total')
            ->groupBy('day')
            ->pluck('total', 'day');
        $subsByDay = $metrics->collectedSubscriptionsBetween($chartStart, now())
            ->selectRaw('DATE(' . $metrics->subscriptionPaidAtColumn() . ') as day, SUM(subscriptions.amount_paid) as total')
            ->groupBy('day')
            ->pluck('total', 'day');

        $chartData = [];
        for ($i = 29; $i >= 0; $i--) {
            $date = now()->subDays($i)->format('Y-m-d');
            $chartData[] = [
                'date' => now()->subDays($i)->format('d/m'),
                'orders' => (int) ($ordersByDay[$date] ?? 0),
                'subscriptions' => (int) ($subsByDay[$date] ?? 0),
            ];
        }

        return view('pages.super-admin.transactions', [
            'transactions' => $transactions,
            'stats' => $stats,
            'chartData' => $chartData,
            'filters' => [
                'type' => $type,
                'status' => $status,
                'date_from' => $dateFrom,
                'date_to' => $dateTo,
                'search' => $search,
            ],
            'pagination' => [
                'total' => $total,
                'per_page' => $perPage,
                'current_page' => $page,
                'last_page' => ceil($total / $perPage),
            ],
        ]);
    }

    public function export(Request $request)
    {
        // Similar logic to index but return CSV
        $filename = 'transactions_' . now()->format('Y-m-d_His') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => "attachment; filename=\"$filename\"",
        ];

        $callback = function () use ($request) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['ID', 'Type', 'Référence', 'Restaurant', 'Montant', 'Statut', 'Méthode', 'Date']);

            // Get all transactions (simplified)
            $subscriptions = Subscription::with('restaurant')->whereNotNull('amount_paid')->get();
            foreach ($subscriptions as $sub) {
                fputcsv($file, [
                    'SUB-' . $sub->id,
                    'Abonnement',
                    $sub->payment_reference ?? 'N/A',
                    $sub->restaurant->name ?? 'N/A',
                    $sub->amount_paid,
                    $sub->status->label(),
                    $sub->payment_method ?? 'N/A',
                    $sub->created_at->format('d/m/Y H:i'),
                ]);
            }

            $orders = Order::withoutGlobalScope('restaurant')
                ->with('restaurant')
                ->whereIn('payment_status', ['completed', 'pending'])
                ->get();
            foreach ($orders as $order) {
                fputcsv($file, [
                    'ORD-' . $order->id,
                    'Commande',
                    $order->reference,
                    $order->restaurant->name ?? 'N/A',
                    $order->total,
                    $order->payment_status?->label() ?? 'N/A',
                    $order->payment_method ?? 'N/A',
                    $order->created_at->format('d/m/Y H:i'),
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }
}
