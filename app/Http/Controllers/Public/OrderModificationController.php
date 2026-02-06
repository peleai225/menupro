<?php

namespace App\Http\Controllers\Public;

use App\Http\Controllers\Controller;
use App\Models\Dish;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Restaurant;
use App\Services\OrderModifier;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;

class OrderModificationController extends Controller
{
    public function __construct(
        protected OrderModifier $orderModifier
    ) {}

    /**
     * Add item to order (customer)
     */
    public function addItem(Request $request, string $slug, string $token): JsonResponse
    {
        $restaurant = Restaurant::where('slug', $slug)->firstOrFail();
        $order = Order::where('tracking_token', $token)->firstOrFail();

        // Verify order belongs to restaurant
        if ($order->restaurant_id !== $restaurant->id) {
            return response()->json([
                'success' => false,
                'message' => 'Commande introuvable.',
            ], 404);
        }

        // Check if customer can modify
        if (!$order->canBeModifiedByCustomer()) {
            return response()->json([
                'success' => false,
                'message' => 'Cette commande ne peut plus être modifiée. Le délai de modification est expiré.',
            ], 403);
        }

        try {
            $request->validate([
                'dish_id' => ['required', 'exists:dishes,id'],
                'quantity' => ['required', 'integer', 'min:1', 'max:99'],
                'options' => ['nullable', 'array'],
                'special_instructions' => ['nullable', 'string', 'max:200'],
            ]);

            $dish = Dish::with('ingredients')->findOrFail($request->dish_id);

            // Verify dish belongs to restaurant
            if ($dish->restaurant_id !== $order->restaurant_id) {
                return response()->json([
                    'success' => false,
                    'message' => 'Ce plat n\'appartient pas à ce restaurant.',
                ], 403);
            }

            $item = $this->orderModifier->addItem(
                $order,
                $dish,
                $request->quantity,
                $request->options ?? [],
                $request->special_instructions
            );

            $order->refresh();

            // Notify restaurant of customer modification
            $this->notifyRestaurant($order, 'add', $item);

            return response()->json([
                'success' => true,
                'message' => 'Article ajouté avec succès.',
                'item' => [
                    'id' => $item->id,
                    'dish_name' => $item->dish_name,
                    'quantity' => $item->quantity,
                    'total_price' => $item->total_price,
                ],
                'order' => [
                    'subtotal' => $order->subtotal,
                    'total' => $order->total,
                    'formatted_total' => $order->formatted_total,
                ],
                'remaining_time' => $order->remaining_modification_time,
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides.',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Remove item from order (customer)
     */
    public function removeItem(Request $request, string $slug, string $token, OrderItem $item): JsonResponse
    {
        $restaurant = Restaurant::where('slug', $slug)->firstOrFail();
        $order = Order::where('tracking_token', $token)->firstOrFail();

        // Verify order belongs to restaurant
        if ($order->restaurant_id !== $restaurant->id) {
            return response()->json([
                'success' => false,
                'message' => 'Commande introuvable.',
            ], 404);
        }

        // Verify item belongs to order
        if ($item->order_id !== $order->id) {
            return response()->json([
                'success' => false,
                'message' => 'Cet article n\'appartient pas à cette commande.',
            ], 403);
        }

        // Check if customer can modify
        if (!$order->canBeModifiedByCustomer()) {
            return response()->json([
                'success' => false,
                'message' => 'Cette commande ne peut plus être modifiée. Le délai de modification est expiré.',
            ], 403);
        }

        try {
            $this->orderModifier->removeItem($order, $item);

            $order->refresh();

            // Notify restaurant of customer modification
            $this->notifyRestaurant($order, 'remove', $item);

            // Handle partial refund if paid
            $refundInfo = null;
            if ($order->is_paid && $order->payment_method === 'lygos') {
                $refundInfo = $this->processPartialRefund($order, $item);
            }

            return response()->json([
                'success' => true,
                'message' => 'Article retiré avec succès.',
                'order' => [
                    'subtotal' => $order->subtotal,
                    'total' => $order->total,
                    'formatted_total' => $order->formatted_total,
                ],
                'remaining_time' => $order->remaining_modification_time,
                'refund' => $refundInfo,
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Update item quantity (customer)
     */
    public function updateItem(Request $request, string $slug, string $token, OrderItem $item): JsonResponse
    {
        $restaurant = Restaurant::where('slug', $slug)->firstOrFail();
        $order = Order::where('tracking_token', $token)->firstOrFail();

        // Verify order belongs to restaurant
        if ($order->restaurant_id !== $restaurant->id) {
            return response()->json([
                'success' => false,
                'message' => 'Commande introuvable.',
            ], 404);
        }

        // Verify item belongs to order
        if ($item->order_id !== $order->id) {
            return response()->json([
                'success' => false,
                'message' => 'Cet article n\'appartient pas à cette commande.',
            ], 403);
        }

        // Check if customer can modify
        if (!$order->canBeModifiedByCustomer()) {
            return response()->json([
                'success' => false,
                'message' => 'Cette commande ne peut plus être modifiée. Le délai de modification est expiré.',
            ], 403);
        }

        try {
            $request->validate([
                'quantity' => ['required', 'integer', 'min:1', 'max:99'],
            ]);

            $oldQuantity = $item->quantity;
            $this->orderModifier->updateItem($order, $item, $request->quantity);

            $order->refresh();
            $item->refresh();

            // Notify restaurant of customer modification
            $this->notifyRestaurant($order, 'update', $item, $oldQuantity);

            // Handle partial refund if quantity decreased and paid
            $refundInfo = null;
            if ($order->is_paid && $order->payment_method === 'lygos' && $request->quantity < $oldQuantity) {
                $refundAmount = ($oldQuantity - $request->quantity) * $item->unit_price;
                $refundInfo = $this->processPartialRefund($order, $item, $refundAmount);
            }

            return response()->json([
                'success' => true,
                'message' => 'Quantité mise à jour avec succès.',
                'item' => [
                    'id' => $item->id,
                    'quantity' => $item->quantity,
                    'total_price' => $item->total_price,
                ],
                'order' => [
                    'subtotal' => $order->subtotal,
                    'total' => $order->total,
                    'formatted_total' => $order->formatted_total,
                ],
                'remaining_time' => $order->remaining_modification_time,
                'refund' => $refundInfo,
            ]);

        } catch (ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Données invalides.',
                'errors' => $e->errors(),
            ], 422);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage(),
            ], 400);
        }
    }

    /**
     * Process partial refund via Lygos
     */
    protected function processPartialRefund(Order $order, OrderItem $item, ?int $amount = null): ?array
    {
        if (!$order->payment_reference) {
            return null;
        }

        $refundAmount = $amount ?? $item->total_price;

        try {
            $lygos = app(\App\Services\LygosGateway::class)->forRestaurant($order->restaurant);
            
            $result = $lygos->refund(
                $order->payment_reference,
                $refundAmount,
                "Modification commande {$order->reference} - Retrait/modification d'article"
            );

            if ($result['success']) {
                // Create refund record
                \App\Models\OrderRefund::create([
                    'order_id' => $order->id,
                    'amount' => $refundAmount,
                    'reason' => "Modification commande - Retrait/modification d'article",
                    'payment_reference' => $result['refund_id'] ?? null,
                    'status' => 'pending',
                    'metadata' => $result,
                ]);

                return [
                    'amount' => $refundAmount,
                    'formatted_amount' => number_format($refundAmount, 0, ',', ' ') . ' F',
                    'status' => 'pending',
                    'message' => 'Remboursement partiel initié. Il sera traité sous 24-48h.',
                ];
            }

            return [
                'error' => true,
                'message' => 'Erreur lors du remboursement. Veuillez contacter le restaurant.',
            ];

        } catch (\Exception $e) {
            \Log::error('Partial refund error', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'error' => true,
                'message' => 'Erreur lors du remboursement. Veuillez contacter le restaurant.',
            ];
        }
    }

    /**
     * Notify restaurant of customer modification
     */
    protected function notifyRestaurant(Order $order, string $action, OrderItem $item, ?int $oldQuantity = null): void
    {
        $actionLabels = [
            'add' => 'ajouté',
            'remove' => 'retiré',
            'update' => 'modifié',
        ];

        $actionLabel = $actionLabels[$action] ?? 'modifié';
        $message = "Le client a {$actionLabel} un article dans la commande #{$order->reference}";
        
        if ($action === 'update' && $oldQuantity) {
            $message .= " (quantité: {$oldQuantity} → {$item->quantity})";
        }

        // Send notification to restaurant users
        $order->restaurant->users()
            ->whereIn('role', [\App\Enums\UserRole::RESTAURANT_ADMIN, \App\Enums\UserRole::EMPLOYEE])
            ->each(function ($user) use ($order, $message) {
                $user->notify(new \App\Notifications\OrderModifiedNotification($order, $message));
            });
    }
}
