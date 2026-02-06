<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Models\Dish;
use App\Models\Order;
use App\Models\OrderItem;
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
     * Add item to order
     */
    public function addItem(Request $request, Order $order): JsonResponse
    {
        $this->authorize('update', $order);

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
     * Remove item from order
     */
    public function removeItem(Request $request, Order $order, OrderItem $item): JsonResponse
    {
        $this->authorize('update', $order);

        // Verify item belongs to order
        if ($item->order_id !== $order->id) {
            return response()->json([
                'success' => false,
                'message' => 'Cet article n\'appartient pas à cette commande.',
            ], 403);
        }

        try {
            $this->orderModifier->removeItem($order, $item);

            $order->refresh();

            return response()->json([
                'success' => true,
                'message' => 'Article retiré avec succès.',
                'order' => [
                    'subtotal' => $order->subtotal,
                    'total' => $order->total,
                    'formatted_total' => $order->formatted_total,
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
     * Update item quantity
     */
    public function updateItem(Request $request, Order $order, OrderItem $item): JsonResponse
    {
        $this->authorize('update', $order);

        // Verify item belongs to order
        if ($item->order_id !== $order->id) {
            return response()->json([
                'success' => false,
                'message' => 'Cet article n\'appartient pas à cette commande.',
            ], 403);
        }

        try {
            $request->validate([
                'quantity' => ['required', 'integer', 'min:1', 'max:99'],
            ]);

            $this->orderModifier->updateItem($order, $item, $request->quantity);

            $order->refresh();
            $item->refresh();

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
}
