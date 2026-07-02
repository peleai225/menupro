<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Restaurant;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function index(Request $request): View
    {
        // Stats — all restaurants, excluding demo
        $demoIds = Restaurant::where('is_demo', true)->pluck('id');

        $stats = [
            'total'         => Order::withoutGlobalScope('restaurant')->whereNotIn('restaurant_id', $demoIds)->count(),
            'today'         => Order::withoutGlobalScope('restaurant')->whereNotIn('restaurant_id', $demoIds)->whereDate('created_at', today())->count(),
            'revenue_month' => Order::withoutGlobalScope('restaurant')
                ->whereNotIn('restaurant_id', $demoIds)
                ->where('payment_status', PaymentStatus::COMPLETED)
                ->whereMonth('created_at', now()->month)
                ->whereYear('created_at', now()->year)
                ->sum('total'),
            'pending'       => Order::withoutGlobalScope('restaurant')
                ->whereNotIn('restaurant_id', $demoIds)
                ->whereIn('status', [
                    OrderStatus::PAID,
                    OrderStatus::CONFIRMED,
                    OrderStatus::PREPARING,
                    OrderStatus::READY,
                    OrderStatus::DELIVERING,
                ])
                ->count(),
        ];

        // Orders query with filters
        $query = Order::withoutGlobalScope('restaurant')
            ->with(['restaurant:id,name', 'delivery.driver:id,name,phone'])
            ->whereNotIn('restaurant_id', $demoIds);

        $query->when($request->filled('search'), function ($q) use ($request) {
            $term = $request->search;
            $q->where(function ($inner) use ($term) {
                $inner->where('reference', 'like', "%{$term}%")
                      ->orWhere('customer_name', 'like', "%{$term}%")
                      ->orWhere('customer_phone', 'like', "%{$term}%")
                      ->orWhere('customer_email', 'like', "%{$term}%");
            });
        });

        $query->when($request->filled('status'), function ($q) use ($request) {
            $q->where('status', $request->status);
        });

        $query->when($request->filled('restaurant_id'), function ($q) use ($request) {
            $q->where('restaurant_id', $request->restaurant_id);
        });

        $query->when($request->filled('date_from'), function ($q) use ($request) {
            $q->whereDate('created_at', '>=', $request->date_from);
        });

        $query->when($request->filled('date_to'), function ($q) use ($request) {
            $q->whereDate('created_at', '<=', $request->date_to);
        });

        $orders = $query->latest()->paginate(25)->withQueryString();

        $restaurants = Restaurant::orderBy('name')->get(['id', 'name']);

        $statuses = OrderStatus::cases();

        return view('pages.super-admin.orders.index', compact('stats', 'orders', 'restaurants', 'statuses'));
    }

    public function show(int $id): View
    {
        $order = Order::withoutGlobalScope('restaurant')
            ->with([
                'restaurant',
                'items',
                'delivery.driver',
            ])
            ->findOrFail($id);

        return view('pages.super-admin.orders.show', compact('order'));
    }
}
