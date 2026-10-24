<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Restaurant;
use App\Services\PlatformMetrics;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LiveOrderController extends Controller
{
    public function index(Request $request, PlatformMetrics $metrics): View
    {
        // En cours = payée → en livraison, limitées aux 48 dernières heures (au-delà : bloquées,
        // comptées à part) ; restaurants démo exclus.
        $query = $metrics->inProgressOrders()
            ->with('restaurant:id,name,is_demo');

        if ($request->filled('restaurant_id')) {
            $query->where('restaurant_id', $request->restaurant_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->latest()->paginate(30)->withQueryString();

        $restaurants = Restaurant::active()->orderBy('name')->get(['id', 'name']);

        $statusCounts = $metrics->inProgressOrders()
            ->selectRaw('orders.status as status_value, COUNT(*) as count')
            ->groupBy('orders.status')
            ->pluck('count', 'status_value');

        // Commandes restées « en cours » depuis plus de 48 h (oubliées / non clôturées)
        $stuckCount = $metrics->stuckOrders()->count();

        return view('pages.super-admin.live-orders', compact('orders', 'restaurants', 'statusCounts', 'stuckCount'));
    }

    public function liveOrders(PlatformMetrics $metrics): JsonResponse
    {
        $orders = $metrics->inProgressOrders()
            ->with('restaurant:id,name')
            ->latest()
            ->limit(50)
            ->get()
            ->map(fn($order) => [
                'id' => $order->id,
                'reference' => $order->reference,
                'restaurant' => $order->restaurant?->name ?? 'N/A',
                'customer_name' => $order->customer_name,
                'total' => $order->total,
                'status' => $order->status->value,
                'status_label' => $order->status->label(),
                'status_color' => $order->status->color(),
                'type' => $order->type->value,
                'created_at' => $order->created_at->format('H:i'),
                'elapsed' => $order->created_at->diffForHumans(short: true),
            ]);

        return response()->json(['orders' => $orders]);
    }
}
