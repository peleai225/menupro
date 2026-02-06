<?php

namespace App\Http\Controllers\Restaurant;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Services\StockManager;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderRushController extends Controller
{
    public function __construct(
        protected StockManager $stockManager
    ) {}

    /**
     * Display Rush mode view (simplified for peak hours)
     */
    public function index(Request $request): View
    {
        $restaurant = auth()->user()->restaurant;
        
        // Get only active orders (pending, confirmed, preparing, ready)
        $query = Order::where('restaurant_id', $restaurant->id)
            ->with(['items.dish'])
            ->whereIn('status', [
                OrderStatus::PAID,
                OrderStatus::CONFIRMED,
                OrderStatus::PREPARING,
                OrderStatus::READY,
            ])
            ->orderByRaw("CASE 
                WHEN status = 'paid' THEN 1
                WHEN status = 'confirmed' THEN 2
                WHEN status = 'preparing' THEN 3
                WHEN status = 'ready' THEN 4
                ELSE 5
            END")
            ->orderBy('created_at', 'asc');

        // Filter: show only new orders if requested
        if ($request->boolean('new_only')) {
            $query->whereIn('status', [OrderStatus::PAID, OrderStatus::CONFIRMED]);
        }

        $orders = $query->get();

        // Prepare orders data for JavaScript
        $ordersData = $orders->map(function ($order) {
            return [
                'id' => $order->id,
                'reference' => $order->reference,
                'customer_name' => $order->customer_name,
                'status' => $order->status->value,
                'status_label' => $order->status->label(),
                'total' => $order->total,
                'items_count' => $order->items->count(),
                'items' => $order->items->map(function ($item) {
                    return [
                        'quantity' => $item->quantity,
                        'dish_name' => $item->dish_name,
                    ];
                })->toArray(),
                'created_at' => $order->created_at->toIso8601String(),
                'created_at_human' => $order->created_at->diffForHumans(),
            ];
        })->toArray();

        // Quick stats
        $stats = [
            'new' => Order::where('restaurant_id', $restaurant->id)
                ->whereIn('status', [OrderStatus::PAID, OrderStatus::CONFIRMED])
                ->count(),
            'preparing' => Order::where('restaurant_id', $restaurant->id)
                ->where('status', OrderStatus::PREPARING)
                ->count(),
            'ready' => Order::where('restaurant_id', $restaurant->id)
                ->where('status', OrderStatus::READY)
                ->count(),
        ];

        return view('pages.restaurant.orders-rush', compact('orders', 'ordersData', 'stats'));
    }

    /**
     * Get orders data (AJAX for auto-refresh)
     */
    public function data(Request $request): JsonResponse
    {
        $restaurant = auth()->user()->restaurant;
        
        $query = Order::where('restaurant_id', $restaurant->id)
            ->with(['items.dish'])
            ->whereIn('status', [
                OrderStatus::PAID,
                OrderStatus::CONFIRMED,
                OrderStatus::PREPARING,
                OrderStatus::READY,
            ])
            ->orderByRaw("CASE 
                WHEN status = 'paid' THEN 1
                WHEN status = 'confirmed' THEN 2
                WHEN status = 'preparing' THEN 3
                WHEN status = 'ready' THEN 4
                ELSE 5
            END")
            ->orderBy('created_at', 'asc');

        if ($request->boolean('new_only')) {
            $query->whereIn('status', [OrderStatus::PAID, OrderStatus::CONFIRMED]);
        }

        $orders = $query->get();

        return response()->json([
            'orders' => $orders->map(function ($order) {
                return [
                    'id' => $order->id,
                    'reference' => $order->reference,
                    'customer_name' => $order->customer_name,
                    'status' => $order->status->value,
                    'status_label' => $order->status->label(),
                    'total' => $order->total,
                    'items_count' => $order->items->count(),
                    'items' => $order->items->map(fn($item) => [
                        'quantity' => $item->quantity,
                        'dish_name' => $item->dish_name,
                    ]),
                    'created_at' => $order->created_at->toIso8601String(),
                    'created_at_human' => $order->created_at->diffForHumans(),
                ];
            }),
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Quick action: Confirm order
     */
    public function confirm(Order $order): JsonResponse
    {
        $this->authorize('update', $order);

        if ($order->status !== OrderStatus::PAID) {
            return response()->json([
                'success' => false,
                'message' => 'Cette commande ne peut pas être confirmée.',
            ], 400);
        }

        try {
            $order->transitionTo(OrderStatus::CONFIRMED);

            // Deduct stock if enabled
            if ($order->restaurant->hasFeature('stock')) {
                $this->stockManager->forRestaurant($order->restaurant)->deductForOrder($order);
            }

            return response()->json([
                'success' => true,
                'message' => 'Commande confirmée.',
                'order' => [
                    'id' => $order->id,
                    'status' => $order->status->value,
                    'status_label' => $order->status->label(),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Quick action: Start preparing
     */
    public function startPreparing(Order $order): JsonResponse
    {
        $this->authorize('update', $order);

        if (!in_array($order->status, [OrderStatus::CONFIRMED, OrderStatus::PAID])) {
            return response()->json([
                'success' => false,
                'message' => 'Cette commande ne peut pas être mise en préparation.',
            ], 400);
        }

        try {
            // Auto-confirm if still PAID
            if ($order->status === OrderStatus::PAID) {
                $order->transitionTo(OrderStatus::CONFIRMED);
                if ($order->restaurant->hasFeature('stock')) {
                    $this->stockManager->forRestaurant($order->restaurant)->deductForOrder($order);
                }
            }

            $order->transitionTo(OrderStatus::PREPARING);

            return response()->json([
                'success' => true,
                'message' => 'Commande en préparation.',
                'order' => [
                    'id' => $order->id,
                    'status' => $order->status->value,
                    'status_label' => $order->status->label(),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Quick action: Mark as ready
     */
    public function markReady(Order $order): JsonResponse
    {
        $this->authorize('update', $order);

        if ($order->status !== OrderStatus::PREPARING) {
            return response()->json([
                'success' => false,
                'message' => 'Cette commande doit être en préparation.',
            ], 400);
        }

        try {
            $order->transitionTo(OrderStatus::READY);

            return response()->json([
                'success' => true,
                'message' => 'Commande marquée comme prête.',
                'order' => [
                    'id' => $order->id,
                    'status' => $order->status->value,
                    'status_label' => $order->status->label(),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Quick action: Complete order
     */
    public function complete(Order $order): JsonResponse
    {
        $this->authorize('update', $order);

        if (!in_array($order->status, [OrderStatus::READY, OrderStatus::DELIVERING])) {
            return response()->json([
                'success' => false,
                'message' => 'Cette commande ne peut pas être complétée.',
            ], 400);
        }

        try {
            $order->transitionTo(OrderStatus::COMPLETED);

            return response()->json([
                'success' => true,
                'message' => 'Commande complétée.',
                'order' => [
                    'id' => $order->id,
                    'status' => $order->status->value,
                    'status_label' => $order->status->label(),
                ],
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }
}
