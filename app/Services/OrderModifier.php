<?php

namespace App\Services;

use App\Models\Dish;
use App\Models\Order;
use App\Models\OrderItem;
use App\Services\StockManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderModifier
{
    public function __construct(
        protected StockManager $stockManager
    ) {}

    /**
     * Add item to order with stock management
     */
    public function addItem(Order $order, Dish $dish, int $quantity = 1, array $options = [], string $instructions = null): OrderItem
    {
        if (!$order->can_be_modified_by_manager) {
            throw new \Exception('Cette commande ne peut pas être modifiée.');
        }

        return DB::transaction(function () use ($order, $dish, $quantity, $options, $instructions) {
            // Check dish availability
            if (!$dish->is_active || !$dish->is_available) {
                throw new \Exception("Le plat \"{$dish->name}\" n'est plus disponible.");
            }

            // Check stock if restaurant has stock management
            $restaurant = $order->restaurant;
            if ($restaurant->hasFeature('stock')) {
                $this->stockManager->forRestaurant($restaurant);
                
                // Check if dish can be prepared
                if (!$this->stockManager->canPrepareDish($dish, $quantity)) {
                    $missing = $this->stockManager->getMissingIngredients($dish, $quantity);
                    $missingList = $missing->map(fn($m) => $m['ingredient']->name)->implode(', ');
                    throw new \Exception("Stock insuffisant pour \"{$dish->name}\". Ingrédients manquants : {$missingList}");
                }
            }

            // Add item to order
            $item = $order->addItem($dish, $quantity, $options, $instructions);

            // Deduct stock only if order is already CONFIRMED+ (stock was already deducted at confirmation)
            // If PAID, deduction will happen when restaurant confirms the order
            $statusesWithStockDeducted = [
                \App\Enums\OrderStatus::CONFIRMED,
                \App\Enums\OrderStatus::PREPARING,
                \App\Enums\OrderStatus::READY,
                \App\Enums\OrderStatus::DELIVERING,
            ];
            if ($restaurant->hasFeature('stock') && in_array($order->status, $statusesWithStockDeducted)) {
                $this->stockManager->deductForDish($dish, $quantity, $order);
            }

            Log::info('Order item added', [
                'order_id' => $order->id,
                'dish_id' => $dish->id,
                'quantity' => $quantity,
                'user_id' => auth()->id(),
            ]);

            return $item;
        });
    }

    /**
     * Remove item from order with stock management
     */
    public function removeItem(Order $order, OrderItem $item): bool
    {
        if (!$order->can_be_modified_by_manager) {
            throw new \Exception('Cette commande ne peut pas être modifiée.');
        }

        return DB::transaction(function () use ($order, $item) {
            // Load dish with ingredients if needed
            $dish = $item->dish;
            if (!$dish && $item->dish_id) {
                $dish = \App\Models\Dish::with('ingredients')->find($item->dish_id);
            }
            
            $quantity = $item->quantity;

            // Restore stock only if it was already deducted (order was CONFIRMED or beyond)
            $restaurant = $order->restaurant;
            $statusesWithStockDeducted = [
                \App\Enums\OrderStatus::CONFIRMED,
                \App\Enums\OrderStatus::PREPARING,
                \App\Enums\OrderStatus::READY,
                \App\Enums\OrderStatus::DELIVERING,
            ];
            if ($restaurant->hasFeature('stock') && $dish && in_array($order->status, $statusesWithStockDeducted)) {
                $this->stockManager->forRestaurant($restaurant);
                
                // Restore stock for this dish
                foreach ($dish->ingredients as $ingredient) {
                    $quantityToRestore = $ingredient->pivot->quantity * $quantity;
                    
                    $this->stockManager->entry($ingredient, $quantityToRestore, null, [
                        'reference_type' => get_class($order),
                        'reference_id' => $order->id,
                        'reason' => "Modification commande {$order->reference} - Retrait de {$dish->name}",
                    ]);
                }
            }

            // Remove item
            $result = $order->removeItem($item);

            if ($result) {
                Log::info('Order item removed', [
                    'order_id' => $order->id,
                    'item_id' => $item->id,
                    'dish_id' => $dish?->id,
                    'quantity' => $quantity,
                    'user_id' => auth()->id(),
                ]);
            }

            return $result;
        });
    }

    /**
     * Update item quantity with stock management
     */
    public function updateItem(Order $order, OrderItem $item, int $newQuantity): bool
    {
        if (!$order->can_be_modified_by_manager) {
            throw new \Exception('Cette commande ne peut pas être modifiée.');
        }

        if ($newQuantity <= 0) {
            return $this->removeItem($order, $item);
        }

        return DB::transaction(function () use ($order, $item, $newQuantity) {
            // Load dish with ingredients if needed
            $dish = $item->dish;
            if (!$dish && $item->dish_id) {
                $dish = \App\Models\Dish::with('ingredients')->find($item->dish_id);
            }
            
            $oldQuantity = $item->quantity;
            $difference = $newQuantity - $oldQuantity;

            if ($difference === 0) {
                return true; // No change
            }

            $restaurant = $order->restaurant;

            // Handle stock if restaurant has stock management
            if ($restaurant->hasFeature('stock') && $dish) {
                $this->stockManager->forRestaurant($restaurant);

                if ($difference > 0) {
                    // Adding quantity - check and deduct stock
                    if (!$this->stockManager->canPrepareDish($dish, $difference)) {
                        $missing = $this->stockManager->getMissingIngredients($dish, $difference);
                        $missingList = $missing->map(fn($m) => $m['ingredient']->name)->implode(', ');
                        throw new \Exception("Stock insuffisant pour augmenter la quantité. Ingrédients manquants : {$missingList}");
                    }

                    // Deduct only if order is already CONFIRMED+ (avoids double deduction at confirmation)
                    $statusesWithStockDeducted = [
                        \App\Enums\OrderStatus::CONFIRMED,
                        \App\Enums\OrderStatus::PREPARING,
                        \App\Enums\OrderStatus::READY,
                        \App\Enums\OrderStatus::DELIVERING,
                    ];
                    if (in_array($order->status, $statusesWithStockDeducted)) {
                        $this->stockManager->deductForDish($dish, $difference, $order);
                    }
                } else {
                    // Reducing quantity - restore stock
                    $quantityToRestore = abs($difference);
                    
                    foreach ($dish->ingredients as $ingredient) {
                        $ingredientQuantityToRestore = $ingredient->pivot->quantity * $quantityToRestore;
                        
                        $this->stockManager->entry($ingredient, $ingredientQuantityToRestore, null, [
                            'reference_type' => get_class($order),
                            'reference_id' => $order->id,
                            'reason' => "Modification commande {$order->reference} - Réduction quantité de {$dish->name}",
                        ]);
                    }
                }
            }

            // Update item
            $result = $order->updateItem($item, $newQuantity);

            if ($result) {
                Log::info('Order item updated', [
                    'order_id' => $order->id,
                    'item_id' => $item->id,
                    'dish_id' => $dish?->id,
                    'old_quantity' => $oldQuantity,
                    'new_quantity' => $newQuantity,
                    'user_id' => auth()->id(),
                ]);
            }

            return $result;
        });
    }
}
