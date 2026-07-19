<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Models\DeliveryDriver;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DeliveryDriverController extends Controller
{
    public function index(Request $request): View
    {
        $query = DeliveryDriver::with('user')
            ->when($request->search, fn($q) => $q->where(function ($q) use ($request) {
                $q->where('name', 'like', "%{$request->search}%")
                  ->orWhere('phone', 'like', "%{$request->search}%")
                  ->orWhere('email', 'like', "%{$request->search}%");
            }))
            ->when($request->status, function ($q) use ($request) {
                match ($request->status) {
                    'approved'  => $q->where('verification_status', 'approved'),
                    'pending'   => $q->where('verification_status', 'pending'),
                    'rejected'  => $q->where('verification_status', 'rejected'),
                    'suspended' => $q->where('is_active', false),
                    'online'    => $q->where('is_active', true)->where('is_available', true)->where('verification_status', 'approved'),
                    default => null,
                };
            })
            ->when($request->city, fn($q) => $q->where('city', $request->city))
            ->latest();

        $drivers = $query->paginate(20)->withQueryString();

        $stats = [
            'total'    => DeliveryDriver::count(),
            'approved' => DeliveryDriver::where('verification_status', 'approved')->count(),
            'pending'  => DeliveryDriver::where('verification_status', 'pending')->count(),
            'online'   => DeliveryDriver::where('is_active', true)->where('is_available', true)->where('verification_status', 'approved')->count(),
        ];

        $cities = DeliveryDriver::select('city')->distinct()->whereNotNull('city')->pluck('city');

        return view('pages.super-admin.drivers.index', compact('drivers', 'stats', 'cities'));
    }

    public function show(DeliveryDriver $driver): View
    {
        $recentDeliveries = $driver->deliveries()->latest()->limit(20)->get();
        $totalEarnings = $driver->earnings()->sum('net_amount');
        $deliveriesThisMonth = $driver->deliveries()->whereMonth('created_at', now()->month)->count();

        return view('pages.super-admin.drivers.show', compact('driver', 'recentDeliveries', 'totalEarnings', 'deliveriesThisMonth'));
    }

    public function approve(DeliveryDriver $driver): RedirectResponse
    {
        $driver->update(['verification_status' => 'approved', 'is_active' => true]);
        return back()->with('success', "Livreur {$driver->name} approuvé.");
    }

    public function reject(DeliveryDriver $driver): RedirectResponse
    {
        $driver->update(['verification_status' => 'rejected', 'is_active' => false]);
        return back()->with('success', "Livreur {$driver->name} rejeté.");
    }

    public function suspend(DeliveryDriver $driver): RedirectResponse
    {
        $driver->update(['is_active' => false, 'is_available' => false]);
        return back()->with('success', "Livreur {$driver->name} suspendu.");
    }

    public function reactivate(DeliveryDriver $driver): RedirectResponse
    {
        $driver->update(['is_active' => true]);
        return back()->with('success', "Livreur {$driver->name} réactivé.");
    }
}
