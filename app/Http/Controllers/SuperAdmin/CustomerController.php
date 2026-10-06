<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\Order;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function show(int $id): View
    {
        $customer = Customer::with([
            'user',
            'addresses',
            'orders' => fn($q) => $q->withoutGlobalScope('restaurant')
                ->latest()
                ->limit(20)
                ->with(['restaurant:id,name']),
        ])->findOrFail($id);

        $stats = [
            'total_orders'  => $customer->orders()->withoutGlobalScope('restaurant')->count(),
            'total_spent'   => $customer->orders()->withoutGlobalScope('restaurant')
                ->where('status', 'completed')->sum('total'),
            'last_order_at' => $customer->orders()->withoutGlobalScope('restaurant')
                ->latest()->value('created_at'),
            'favourite_restaurant' => $customer->orders()
                ->withoutGlobalScope('restaurant')
                ->selectRaw('restaurant_id, count(*) as cnt')
                ->groupBy('restaurant_id')
                ->orderByDesc('cnt')
                ->with('restaurant:id,name')
                ->first()?->restaurant?->name,
        ];

        return view('pages.super-admin.customers.show', compact('customer', 'stats'));
    }

    public function index(Request $request): View
    {
        $sort = $request->get('sort', 'recent');

        // total_orders/last_order_at sur Customer ne sont jamais maintenus à jour
        // (aucun observer/job ne les écrit) — on calcule les vrais chiffres en
        // agrégeant directement depuis les commandes à chaque affichage.
        $query = Customer::with('user')
            ->withCount(['orders as orders_count' => fn($q) => $q->withoutGlobalScope('restaurant')])
            ->withSum(['orders as orders_revenue' => fn($q) => $q->withoutGlobalScope('restaurant')
                ->where('status', 'completed')], 'total')
            ->withMax(['orders as last_order_date' => fn($q) => $q->withoutGlobalScope('restaurant')], 'created_at')
            ->when($request->search, fn($q) => $q->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            })->orWhere('phone', 'like', "%{$request->search}%"))
            ->when($request->city, fn($q) => $q->where('city', $request->city))
            ->when($request->status === 'active', fn($q) => $q->where('is_active', true))
            ->when($request->status === 'inactive', fn($q) => $q->where('is_active', false));

        match ($sort) {
            'oldest'       => $query->oldest('created_at'),
            'last_order'   => $query->orderByDesc('last_order_date'),
            'top_spenders' => $query->orderByDesc('orders_revenue'),
            'most_orders'  => $query->orderByDesc('orders_count'),
            default        => $query->latest('created_at'), // 'recent' — date d'inscription, plus récents d'abord
        };

        $customers = $query->paginate(25)->withQueryString();

        $stats = [
            'total'          => Customer::count(),
            'new_this_month' => Customer::whereYear('created_at', now()->year)
                ->whereMonth('created_at', now()->month)->count(),
            'orders_total'   => Order::withoutGlobalScope('restaurant')->whereNotNull('customer_id')->count(),
            'revenue_total'  => Order::withoutGlobalScope('restaurant')->whereNotNull('customer_id')
                ->where('status', 'completed')->sum('total'),
        ];

        $cities = Customer::select('city')->distinct()->whereNotNull('city')->pluck('city');

        return view('pages.super-admin.customers.index', compact('customers', 'stats', 'cities', 'sort'));
    }
}
