<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\DeliveryStatus;
use App\Http\Controllers\Controller;
use App\Models\Delivery;
use App\Models\Restaurant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DeliveryDashboardController extends Controller
{
    public function index(Request $request): View
    {
        $query = Delivery::withoutGlobalScope('restaurant')
            ->with(['restaurant:id,name', 'driver:id,name', 'order:id,reference,customer_name'])
            ->whereIn('status', [
                DeliveryStatus::PENDING,
                DeliveryStatus::ASSIGNED,
                DeliveryStatus::HEADING_TO_RESTAURANT,
                DeliveryStatus::PICKED_UP,
                DeliveryStatus::DELIVERING,
            ]);

        if ($request->filled('restaurant_id')) {
            $query->where('restaurant_id', $request->restaurant_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $deliveries = $query->latest()->paginate(30)->withQueryString();

        $restaurants = Restaurant::active()
            ->where('delivery_enabled', true)
            ->orderBy('name')
            ->get(['id', 'name']);

        $statusCounts = Delivery::withoutGlobalScope('restaurant')
            ->whereIn('status', [
                DeliveryStatus::PENDING,
                DeliveryStatus::ASSIGNED,
                DeliveryStatus::HEADING_TO_RESTAURANT,
                DeliveryStatus::PICKED_UP,
                DeliveryStatus::DELIVERING,
            ])
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        $todayDelivered = Delivery::withoutGlobalScope('restaurant')
            ->where('status', DeliveryStatus::DELIVERED)
            ->whereDate('delivered_at', today())
            ->count();

        return view('pages.super-admin.deliveries', compact(
            'deliveries', 'restaurants', 'statusCounts', 'todayDelivered'
        ));
    }

    public function liveDeliveries(): JsonResponse
    {
        $deliveries = Delivery::withoutGlobalScope('restaurant')
            ->with(['restaurant:id,name', 'driver:id,name', 'order:id,reference,customer_name'])
            ->whereIn('status', [
                DeliveryStatus::PENDING,
                DeliveryStatus::ASSIGNED,
                DeliveryStatus::HEADING_TO_RESTAURANT,
                DeliveryStatus::PICKED_UP,
                DeliveryStatus::DELIVERING,
            ])
            ->latest()
            ->limit(50)
            ->get()
            ->map(fn($d) => [
                'id' => $d->id,
                'order_reference' => $d->order?->reference ?? '-',
                'customer_name' => $d->order?->customer_name ?? '-',
                'restaurant' => $d->restaurant?->name ?? '-',
                'driver' => $d->driver?->name ?? 'Non assigné',
                'status' => $d->status->value,
                'status_label' => $d->status->label(),
                'delivery_address' => $d->delivery_address,
                'assigned_at' => $d->assigned_at?->format('H:i'),
                'elapsed' => $d->created_at->diffForHumans(short: true),
            ]);

        return response()->json(['deliveries' => $deliveries]);
    }
}
