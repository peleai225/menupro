<?php

namespace App\Http\Controllers\Restaurant;

use App\Enums\OrderStatus;
use App\Http\Controllers\Controller;
use App\Http\Requests\Restaurant\UpdateOrderStatusRequest;
use App\Models\Order;
use App\Services\StockManager;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class OrderController extends Controller
{
    public function __construct(
        protected StockManager $stockManager
    ) {}

    /**
     * Display a listing of orders.
     */
    public function index(Request $request): View
    {
        $query = Order::with('items.dish');

        // Filters
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('date')) {
            $query->whereDate('created_at', $request->date);
        }

        if ($request->filled('search')) {
            $query->search($request->search);
        }

        // Default: show recent first, active orders highlighted
        $orders = $query->latest()->paginate(20)->withQueryString();

        // Stats
        $stats = [
            'pending' => Order::whereIn('status', [OrderStatus::PAID, OrderStatus::CONFIRMED])->count(),
            'preparing' => Order::where('status', OrderStatus::PREPARING)->count(),
            'ready' => Order::where('status', OrderStatus::READY)->count(),
            'today_total' => Order::today()->where('payment_status', 'completed')->sum('total'),
        ];

        return view('pages.restaurant.orders', compact('orders', 'stats'));
    }

    /**
     * Display the specified order.
     */
    public function show(Order $order): View
    {
        $this->authorize('view', $order);

        $order->load('items.dish');

        // Get allowed status transitions
        $allowedTransitions = $order->status->allowedTransitions();

        // Get available dishes for modification (if order can be modified)
        $availableDishes = collect();
        if ($order->can_be_modified_by_manager) {
            $availableDishes = \App\Models\Dish::where('restaurant_id', $order->restaurant_id)
                ->active()
                ->orderBy('name')
                ->get();
        }

        return view('pages.restaurant.order-show', compact('order', 'allowedTransitions', 'availableDishes'));
    }

    /**
     * Update order status.
     */
    public function updateStatus(UpdateOrderStatusRequest $request, Order $order): RedirectResponse
    {
        $newStatus = OrderStatus::from($request->status);

        // Handle stock deduction when order is confirmed
        if ($newStatus === OrderStatus::CONFIRMED && $order->status === OrderStatus::PAID) {
            $restaurant = $order->restaurant;
            
            if ($restaurant->hasFeature('stock')) {
                $this->stockManager->forRestaurant($restaurant)->deductForOrder($order);
            }
        }

        // Update estimated prep time if provided
        if ($request->filled('estimated_prep_time')) {
            $order->estimated_prep_time = $request->estimated_prep_time;
        }

        // Update internal notes if provided
        if ($request->filled('internal_notes')) {
            $order->internal_notes = $request->internal_notes;
        }

        // Transition status
        $order->transitionTo($newStatus);

        return back()->with('success', 'Statut de la commande mis à jour.');
    }

    /**
     * Cancel an order.
     */
    public function cancel(Request $request, Order $order): RedirectResponse
    {
        $this->authorize('cancel', $order);

        $request->validate([
            'reason' => ['nullable', 'string', 'max:500'],
        ]);

        // Restore stock only if it was already deducted (order was CONFIRMED or beyond)
        $statusesWithStockDeducted = [
            \App\Enums\OrderStatus::CONFIRMED,
            \App\Enums\OrderStatus::PREPARING,
            \App\Enums\OrderStatus::READY,
            \App\Enums\OrderStatus::DELIVERING,
        ];
        if (in_array($order->status, $statusesWithStockDeducted)) {
            $restaurant = $order->restaurant;
            
            if ($restaurant->hasFeature('stock')) {
                $this->stockManager->forRestaurant($restaurant)->restoreForOrder($order);
            }
        }

        $order->cancel($request->reason);

        return back()->with('success', 'Commande annulée.');
    }

    /**
     * Print order receipt.
     */
    public function print(Order $order)
    {
        // Check if order belongs to user's restaurant
        if ($order->restaurant_id !== auth()->user()->restaurant_id) {
            abort(403, 'Vous n\'avez pas accès à cette commande.');
        }

        $order->load('items', 'restaurant');

        return view('pages.restaurant.order-print', compact('order'));
    }

    /**
     * Live orders board (for kitchen display).
     */
    public function board(): View
    {
        $orders = [
            'new' => Order::whereIn('status', [OrderStatus::PAID, OrderStatus::CONFIRMED])
                ->with('items')
                ->oldest()
                ->get(),
            'preparing' => Order::where('status', OrderStatus::PREPARING)
                ->with('items')
                ->oldest()
                ->get(),
            'ready' => Order::where('status', OrderStatus::READY)
                ->with('items')
                ->oldest()
                ->get(),
        ];

        return view('pages.restaurant.orders-board', compact('orders'));
    }
}

