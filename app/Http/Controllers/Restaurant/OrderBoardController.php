<?php

namespace App\Http\Controllers\Restaurant;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderBoardController extends Controller
{
    /**
     * Display Kanban board view
     */
    public function index(Request $request): View
    {
        $restaurant = auth()->user()->restaurant;
        
        // Get orders grouped by status
        $ordersByStatus = $this->getOrdersByStatus($restaurant->id, $request);
        
        // Stats
        $stats = [
            'total' => Order::where('restaurant_id', $restaurant->id)
                ->whereDate('created_at', today())
                ->count(),
            'pending' => Order::where('restaurant_id', $restaurant->id)
                ->whereIn('status', [OrderStatus::PAID, OrderStatus::CONFIRMED])
                ->count(),
            'preparing' => Order::where('restaurant_id', $restaurant->id)
                ->where('status', OrderStatus::PREPARING)
                ->count(),
            'ready' => Order::where('restaurant_id', $restaurant->id)
                ->where('status', OrderStatus::READY)
                ->count(),
        ];

        return view('pages.restaurant.orders-kanban', compact('ordersByStatus', 'stats'));
    }

    /**
     * Get orders grouped by status (AJAX)
     */
    public function data(Request $request): JsonResponse
    {
        $restaurant = auth()->user()->restaurant;
        $ordersByStatus = $this->getOrdersByStatus($restaurant->id, $request);

        return response()->json([
            'orders' => $ordersByStatus,
            'timestamp' => now()->toIso8601String(),
        ]);
    }

    /**
     * Update order status via drag & drop
     */
    public function updateStatus(Request $request, Order $order): JsonResponse
    {
        $this->authorize('update', $order);

        $request->validate([
            'status' => ['required', 'string', 'in:' . implode(',', array_map(fn($s) => $s->value, OrderStatus::cases()))],
        ]);

        try {
            $newStatus = OrderStatus::from($request->status);
            
            if (!$order->status->canTransitionTo($newStatus)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Transition de statut non autorisée.',
                ], 400);
            }

            $order->transitionTo($newStatus);

            // Handle stock if needed
            if ($newStatus === OrderStatus::CONFIRMED && $order->restaurant->hasFeature('stock')) {
                app(\App\Services\StockManager::class)
                    ->forRestaurant($order->restaurant)
                    ->deductForOrder($order);
            }

            return response()->json([
                'success' => true,
                'message' => 'Statut mis à jour.',
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
     * Get orders grouped by status
     */
    protected function getOrdersByStatus(int $restaurantId, Request $request): array
    {
        $query = Order::where('restaurant_id', $restaurantId)
            ->with(['items.dish', 'restaurant'])
            ->whereNotIn('status', [OrderStatus::CANCELLED, OrderStatus::REFUNDED]);

        // Filters
        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('search')) {
            $query->where(function ($q) use ($request) {
                $q->where('reference', 'like', "%{$request->search}%")
                  ->orWhere('customer_name', 'like', "%{$request->search}%")
                  ->orWhere('customer_phone', 'like', "%{$request->search}%");
            });
        }

        // Group by status
        $allOrders = $query->get();

        return [
            'pending_payment' => $allOrders->where('status', OrderStatus::PENDING_PAYMENT)->values(),
            'paid' => $allOrders->where('status', OrderStatus::PAID)->values(),
            'confirmed' => $allOrders->where('status', OrderStatus::CONFIRMED)->values(),
            'preparing' => $allOrders->where('status', OrderStatus::PREPARING)->values(),
            'ready' => $allOrders->where('status', OrderStatus::READY)->values(),
            'delivering' => $allOrders->where('status', OrderStatus::DELIVERING)->values(),
            'completed' => $allOrders->where('status', OrderStatus::COMPLETED)->values(),
        ];
    }
}
