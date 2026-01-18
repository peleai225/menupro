<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;

class CustomerController extends Controller
{
    /**
     * Display a listing of customers.
     */
    public function index(Request $request): View
    {
        $restaurant = $request->user()->restaurant;

        $query = Order::query()
            ->select(
                'customer_email',
                'customer_name',
                'customer_phone',
                DB::raw('COUNT(*) as orders_count'),
                DB::raw('SUM(total) as total_spent'),
                DB::raw('MAX(created_at) as last_order_at')
            )
            ->where('restaurant_id', $restaurant->id)
            ->where('payment_status', 'completed')
            ->groupBy('customer_email', 'customer_name', 'customer_phone');

        // Search
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('customer_name', 'like', "%{$search}%")
                  ->orWhere('customer_email', 'like', "%{$search}%")
                  ->orWhere('customer_phone', 'like', "%{$search}%");
            });
        }

        // Sort
        $sortBy = $request->get('sort', 'total_spent');
        $sortDir = $request->get('dir', 'desc');
        $query->orderBy($sortBy, $sortDir);

        $customers = $query->paginate(20)->withQueryString();

        // Stats
        $stats = [
            'total_customers' => Order::where('restaurant_id', $restaurant->id)
                ->where('payment_status', 'completed')
                ->distinct('customer_email')
                ->count('customer_email'),
            'new_this_month' => Order::where('restaurant_id', $restaurant->id)
                ->where('payment_status', 'completed')
                ->whereMonth('created_at', now()->month)
                ->distinct('customer_email')
                ->count('customer_email'),
            'returning_rate' => $this->calculateReturningRate($restaurant->id),
        ];

        return view('pages.restaurant.customers', compact('customers', 'stats', 'restaurant'));
    }

    /**
     * Display customer details.
     */
    public function show(Request $request, string $email): View
    {
        $restaurant = $request->user()->restaurant;

        $customer = Order::query()
            ->select(
                'customer_email',
                'customer_name',
                'customer_phone',
                DB::raw('COUNT(*) as orders_count'),
                DB::raw('SUM(total) as total_spent'),
                DB::raw('AVG(total) as average_order'),
                DB::raw('MIN(created_at) as first_order_at'),
                DB::raw('MAX(created_at) as last_order_at')
            )
            ->where('restaurant_id', $restaurant->id)
            ->where('customer_email', $email)
            ->where('payment_status', 'completed')
            ->groupBy('customer_email', 'customer_name', 'customer_phone')
            ->firstOrFail();

        $orders = Order::where('restaurant_id', $restaurant->id)
            ->where('customer_email', $email)
            ->with('items')
            ->latest()
            ->get();

        // Favorite dishes
        $favoriteDishes = DB::table('order_items')
            ->join('orders', 'order_items.order_id', '=', 'orders.id')
            ->where('orders.restaurant_id', $restaurant->id)
            ->where('orders.customer_email', $email)
            ->select('order_items.dish_name', DB::raw('SUM(order_items.quantity) as total_ordered'))
            ->groupBy('order_items.dish_name')
            ->orderByDesc('total_ordered')
            ->limit(5)
            ->get();

        return view('pages.restaurant.customer-show', compact('customer', 'orders', 'favoriteDishes'));
    }

    /**
     * Export customers to CSV.
     */
    public function export(Request $request)
    {
        $restaurant = $request->user()->restaurant;

        $customers = Order::query()
            ->select(
                'customer_email',
                'customer_name',
                'customer_phone',
                DB::raw('COUNT(*) as orders_count'),
                DB::raw('SUM(total) as total_spent'),
                DB::raw('MAX(created_at) as last_order_at')
            )
            ->where('restaurant_id', $restaurant->id)
            ->where('payment_status', 'completed')
            ->groupBy('customer_email', 'customer_name', 'customer_phone')
            ->orderByDesc('total_spent')
            ->get();

        $filename = 'clients_' . now()->format('Y-m-d') . '.csv';
        
        $headers = [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="' . $filename . '"',
        ];

        $callback = function () use ($customers) {
            $file = fopen('php://output', 'w');
            
            // UTF-8 BOM
            fprintf($file, chr(0xEF) . chr(0xBB) . chr(0xBF));
            
            // Header
            fputcsv($file, ['Nom', 'Email', 'Téléphone', 'Commandes', 'Total dépensé', 'Dernière commande']);
            
            // Data
            foreach ($customers as $customer) {
                fputcsv($file, [
                    $customer->customer_name,
                    $customer->customer_email,
                    $customer->customer_phone,
                    $customer->orders_count,
                    $customer->total_spent,
                    $customer->last_order_at,
                ]);
            }
            
            fclose($file);
        };

        return response()->stream($callback, 200, $headers);
    }

    /**
     * Calculate returning customer rate.
     */
    protected function calculateReturningRate(int $restaurantId): float
    {
        $totalCustomers = Order::where('restaurant_id', $restaurantId)
            ->where('payment_status', 'completed')
            ->distinct('customer_email')
            ->count('customer_email');

        if ($totalCustomers === 0) {
            return 0;
        }

        $returningCustomers = Order::where('restaurant_id', $restaurantId)
            ->where('payment_status', 'completed')
            ->select('customer_email')
            ->groupBy('customer_email')
            ->havingRaw('COUNT(*) > 1')
            ->get()
            ->count();

        return round(($returningCustomers / $totalCustomers) * 100, 1);
    }
}

