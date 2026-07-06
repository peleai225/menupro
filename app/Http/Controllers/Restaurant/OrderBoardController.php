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

        // Stats — single query
        $stats = \Illuminate\Support\Facades\DB::table('orders')
            ->where('restaurant_id', $restaurant->id)
            ->selectRaw("SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as total")
            ->selectRaw("SUM(CASE WHEN status IN ('paid','confirmed') THEN 1 ELSE 0 END) as pending")
            ->selectRaw("SUM(CASE WHEN status = 'preparing' THEN 1 ELSE 0 END) as preparing")
            ->selectRaw("SUM(CASE WHEN status = 'ready' THEN 1 ELSE 0 END) as ready")
            ->first();

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

            // Deduct stock BEFORE transition (PAID → CONFIRMED) so both succeed or both fail
            if ($newStatus === OrderStatus::CONFIRMED && $order->status === OrderStatus::PAID && $order->restaurant->hasFeature('stock')) {
                app(\App\Services\StockManager::class)
                    ->forRestaurant($order->restaurant)
                    ->deductForOrder($order);
            }

            $order->transitionTo($newStatus);

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
        // Sélection stricte des colonnes pour éviter de charger les colonnes JSON lourdes
        // (allergens, nutritional_info, gallery, payment_metadata) sur chaque refresh Kanban
        $query = Order::where('restaurant_id', $restaurantId)
            ->select([
                'id', 'restaurant_id', 'reference', 'customer_name', 'customer_phone',
                'type', 'status', 'total', 'created_at', 'table_number',
                'delivery_address', 'customer_notes', 'internal_notes',
            ])
            ->with([
                'items' => fn($q) => $q->select('id', 'order_id', 'dish_id', 'dish_name', 'quantity', 'unit_price', 'total_price', 'selected_options', 'special_instructions'),
                'items.dish' => fn($q) => $q->select('id', 'name', 'image_path'),
            ])
            ->whereNotIn('status', [OrderStatus::CANCELLED, OrderStatus::REFUNDED])
            ->where('created_at', '>=', now()->subHours(24));

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

        $allOrders = $query->latest()->limit(200)->get();

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
