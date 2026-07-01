<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\Customer;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\View\View;

class CustomerController extends Controller
{
    public function index(Request $request): View
    {
        // Les clients peuvent être dans la table customers OU être des users avec des commandes
        $query = Customer::with('user')
            ->when($request->search, fn($q) => $q->whereHas('user', function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            })->orWhere('phone', 'like', "%{$request->search}%"))
            ->when($request->city, fn($q) => $q->where('city', $request->city))
            ->when($request->status === 'active', fn($q) => $q->where('is_active', true))
            ->when($request->status === 'inactive', fn($q) => $q->where('is_active', false))
            ->orderByDesc('last_order_at');

        $customers = $query->paginate(25)->withQueryString();

        $stats = [
            'total'         => Customer::count(),
            'active'        => Customer::where('is_active', true)->count(),
            'ordered_today' => Customer::whereDate('last_order_at', today())->count(),
            'new_this_month'=> Customer::whereMonth('created_at', now()->month)->count(),
        ];

        $cities = Customer::select('city')->distinct()->whereNotNull('city')->pluck('city');

        return view('pages.super-admin.customers.index', compact('customers', 'stats', 'cities'));
    }
}
