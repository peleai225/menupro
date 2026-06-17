<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Restaurant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LiveOrderController extends Controller
{
    public function index(Request $request): View
    {
        $query = Order::withoutGlobalScope('restaurant')
            ->with('restaurant:id,name,is_demo')
            ->whereIn('status', [
                OrderStatus::PAID,
                OrderStatus::CONFIRMED,
                OrderStatus::PREPARING,
                OrderStatus::READY,
                OrderStatus::DELIVERING,
            ]);

        if ($request->filled('restaurant_id')) {
            $query->where('restaurant_id', $request->restaurant_id);
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $orders = $query->latest()->paginate(30)->withQueryString();

        $restaurants = Restaurant::active()->orderBy('name')->get(['id', 'name']);

        $statusCounts = Order::withoutGlobalScope('restaurant')
            ->whereIn('status', [
                OrderStatus::PAID,
                OrderStatus::CONFIRMED,
                OrderStatus::PREPARING,
                OrderStatus::READY,
                OrderStatus::DELIVERING,
            ])
            ->selectRaw('status, COUNT(*) as count')
            ->groupBy('status')
            ->pluck('count', 'status');

        return view('pages.super-admin.live-orders', compact('orders', 'restaurants', 'statusCounts'));
    }

    public function liveOrders(): JsonResponse
    {
        $orders = Order::withoutGlobalScope('restaurant')
            ->with('restaurant:id,name')
            ->whereIn('status', [
                OrderStatus::PAID,
                OrderStatus::CONFIRMED,
                OrderStatus::PREPARING,
                OrderStatus::READY,
                OrderStatus::DELIVERING,
            ])
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
