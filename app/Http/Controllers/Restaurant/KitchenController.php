<?php

namespace App\Http\Controllers\Restaurant;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Restaurant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class KitchenController extends Controller
{
    /**
     * Generate or regenerate kitchen token (from settings).
     */
    public function generateToken(Request $request): JsonResponse
    {
        $restaurant = auth()->user()->restaurant;
        $restaurant->kitchen_token = Str::random(32);
        $restaurant->save();

        return response()->json([
            'token' => $restaurant->kitchen_token,
            'url' => route('kitchen.display', $restaurant->kitchen_token),
        ]);
    }

    /**
     * Kitchen display screen (no auth required, token-secured).
     */
    public function display(string $token): View
    {
        $restaurant = Restaurant::where('kitchen_token', $token)->firstOrFail();

        $orders = Order::where('restaurant_id', $restaurant->id)
            ->whereIn('status', [
                OrderStatus::PAID,
                OrderStatus::CONFIRMED,
                OrderStatus::PREPARING,
            ])
            ->with('items.dish')
            ->oldest()
            ->get();

        $ordersJson = $orders->map(fn($order) => [
            'id' => $order->id,
            'reference' => $order->reference,
            'status' => $order->status->value,
            'status_label' => $order->status->label(),
            'customer_name' => $order->customer_name,
            'type' => $order->type?->label() ?? '',
            'table_number' => $order->table_number,
            'created_at' => $order->created_at->format('H:i'),
            'minutes_ago' => $order->created_at->diffInMinutes(now()),
            'items' => $order->items->map(fn($item) => [
                'quantity' => $item->quantity,
                'name' => $item->dish?->name ?? $item->dish_name ?? 'Plat',
                'options' => $item->selected_options ?? [],
                'instructions' => $item->special_instructions,
            ]),
        ])->values();

        return view('pages.kitchen.display', compact('restaurant', 'ordersJson', 'token'));
    }

    /**
     * Get orders data (AJAX polling from kitchen display).
     */
    public function data(string $token): JsonResponse
    {
        $restaurant = Restaurant::where('kitchen_token', $token)->firstOrFail();

        $orders = Order::where('restaurant_id', $restaurant->id)
            ->whereIn('status', [
                OrderStatus::PAID,
                OrderStatus::CONFIRMED,
                OrderStatus::PREPARING,
            ])
            ->with('items.dish')
            ->oldest()
            ->get()
            ->map(fn($order) => [
                'id' => $order->id,
                'reference' => $order->reference,
                'status' => $order->status->value,
                'status_label' => $order->status->label(),
                'customer_name' => $order->customer_name,
                'type' => $order->type?->label() ?? '',
                'table_number' => $order->table_number,
                'created_at' => $order->created_at->format('H:i'),
                'minutes_ago' => $order->created_at->diffInMinutes(now()),
                'items' => $order->items->map(fn($item) => [
                    'quantity' => $item->quantity,
                    'name' => $item->dish?->name ?? $item->dish_name ?? 'Plat',
                    'options' => $item->selected_options ?? [],
                    'instructions' => $item->special_instructions,
                ]),
            ]);

        return response()->json([
            'orders' => $orders,
            'counts' => [
                'new' => $orders->where('status', 'paid')->count() + $orders->where('status', 'confirmed')->count(),
                'preparing' => $orders->where('status', 'preparing')->count(),
            ],
        ]);
    }

    /**
     * Update order status from kitchen display.
     */
    public function updateStatus(string $token, Order $order, Request $request): JsonResponse
    {
        $restaurant = Restaurant::where('kitchen_token', $token)->firstOrFail();

        if ((int) $order->restaurant_id !== (int) $restaurant->id) {
            abort(403);
        }

        $action = $request->input('action');

        $newStatus = match ($action) {
            'confirm' => OrderStatus::CONFIRMED,
            'prepare' => OrderStatus::PREPARING,
            'ready' => OrderStatus::READY,
            default => null,
        };

        if (!$newStatus) {
            return response()->json(['error' => 'Action invalide'], 400);
        }

        if (!$order->transitionTo($newStatus)) {
            return response()->json(['error' => 'Transition impossible'], 422);
        }

        return response()->json(['success' => true, 'new_status' => $newStatus->value]);
    }
}
