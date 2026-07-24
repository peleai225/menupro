<?php

namespace App\Services;

use App\Enums\StockMovementType;
use App\Events\LowStockAlert;
use App\Models\Dish;
use App\Models\Ingredient;
use App\Models\NotificationSetting;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\StockMovement;
use App\Notifications\RealTimeLowStockNotification;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class StockManager
{
    protected Restaurant $restaurant;

    /**
     * Set the restaurant context
     */
    public function forRestaurant(Restaurant $restaurant): static
    {
        $this->restaurant = $restaurant;
        return $this;
    }

    /**
     * Record stock entry
     */
    public function entry(
        Ingredient $ingredient,
        float $quantity,
        int $unitCost = null,
        array $options = []
    ): StockMovement {
        return DB::transaction(function () use ($ingredient, $quantity, $unitCost, $options) {
            $quantityBefore = $ingredient->current_quantity;
            
            // Update ingredient
            $ingredient->current_quantity += $quantity;
            
            if ($unitCost !== null && $ingredient->current_quantity > 0) {
                // Update average cost using weighted average (avoid division by zero)
                $totalValue = ($quantityBefore * $ingredient->unit_cost) + ($quantity * $unitCost);
                $ingredient->unit_cost = (int) round($totalValue / $ingredient->current_quantity);
                $ingredient->last_purchase_cost = $unitCost;
            }
            
            $ingredient->save();

            // Create movement
            return StockMovement::create([
                'restaurant_id' => $ingredient->restaurant_id,
                'ingredient_id' => $ingredient->id,
                'user_id' => auth()->id(),
                'type' => StockMovementType::ENTRY,
                'quantity' => abs($quantity),
                'quantity_before' => $quantityBefore,
                'quantity_after' => $ingredient->current_quantity,
                'unit_cost' => $unitCost,
                'reference_type' => $options['reference_type'] ?? null,
                'reference_id' => $options['reference_id'] ?? null,
                'expiry_date' => $options['expiry_date'] ?? null,
                'batch_number' => $options['batch_number'] ?? null,
                'reason' => $options['reason'] ?? 'Entrée de stock',
            ]);
        });
    }

    /**
     * Record stock exit (manual)
     */
    public function exit(
        Ingredient $ingredient,
        float $quantity,
        string $reason = null,
        StockMovementType $type = StockMovementType::EXIT_MANUAL
    ): StockMovement {
        return DB::transaction(function () use ($ingredient, $quantity, $reason, $type) {
            $quantityBefore = $ingredient->current_quantity;
            
            $ingredient->current_quantity = max(0, $ingredient->current_quantity - $quantity);
            $ingredient->save();

            return StockMovement::create([
                'restaurant_id' => $ingredient->restaurant_id,
                'ingredient_id' => $ingredient->id,
                'user_id' => auth()->id(),
                'type' => $type,
                'quantity' => -abs($quantity),
                'quantity_before' => $quantityBefore,
                'quantity_after' => $ingredient->current_quantity,
                'reason' => $reason,
            ]);
        });
    }

    /**
     * Record waste/loss
     */
    public function waste(Ingredient $ingredient, float $quantity, string $reason = null): StockMovement
    {
        return $this->exit($ingredient, $quantity, $reason ?? 'Perte/Gaspillage', StockMovementType::EXIT_WASTE);
    }

    /**
     * Adjust stock (inventory)
     */
    public function adjust(Ingredient $ingredient, float $newQuantity, string $reason): StockMovement
    {
        return DB::transaction(function () use ($ingredient, $newQuantity, $reason) {
            $quantityBefore = $ingredient->current_quantity;
            $difference = $newQuantity - $quantityBefore;
            
            $ingredient->current_quantity = $newQuantity;
            $ingredient->save();

            return StockMovement::create([
                'restaurant_id' => $ingredient->restaurant_id,
                'ingredient_id' => $ingredient->id,
                'user_id' => auth()->id(),
                'type' => StockMovementType::ADJUSTMENT,
                'quantity' => $difference,
                'quantity_before' => $quantityBefore,
                'quantity_after' => $newQuantity,
                'reason' => $reason,
            ]);
        });
    }

    /**
     * Deduct stock for an order (based on dish ingredients)
     */
    public function deductForOrder(Order $order): Collection
    {
        $movements = collect();

        return DB::transaction(function () use ($order, $movements) {
            foreach ($order->items as $item) {
                if (!$item->dish) {
                    continue;
                }

                $dishMovements = $this->deductForDish($item->dish, $item->quantity, $order);
                $movements = $movements->merge($dishMovements);
            }

            return $movements;
        });
    }

    /**
     * Deduct stock for a single dish
     */
    public function deductForDish(Dish $dish, int $quantity, $reference = null): Collection
    {
        $movements = collect();

        foreach ($dish->ingredients as $ingredient) {
            $requiredQuantity = $ingredient->pivot->quantity * $quantity;

            // Lock row to prevent race conditions on concurrent orders
            $lockedIngredient = \App\Models\Ingredient::lockForUpdate()->find($ingredient->id);
            if (!$lockedIngredient) {
                continue;
            }

            $quantityBefore = (float) $lockedIngredient->current_quantity;

            $movement = StockMovement::create([
                'restaurant_id' => $lockedIngredient->restaurant_id,
                'ingredient_id' => $lockedIngredient->id,
                'user_id' => auth()->id(),
                'type' => StockMovementType::EXIT_SALE,
                'quantity' => -abs($requiredQuantity),
                'quantity_before' => $quantityBefore,
                'quantity_after' => max(0, $quantityBefore - $requiredQuantity),
                'reference_type' => $reference ? get_class($reference) : null,
                'reference_id' => $reference?->id,
                'reason' => "Vente: {$dish->name} x{$quantity}",
            ]);

            $lockedIngredient->current_quantity = max(0, $quantityBefore - $requiredQuantity);
            $lockedIngredient->save();

            $movements->push($movement);

            // Fire real-time alert only when stock JUST crosses below min_quantity
            $this->dispatchLowStockAlertIfNeeded($lockedIngredient, $quantityBefore);
        }

        return $movements;
    }

    /**
     * Dispatch a real-time low-stock alert if the ingredient just crossed below its threshold.
     * Fires only on threshold crossing (was above, now at/below) to avoid notification spam.
     */
    protected function dispatchLowStockAlertIfNeeded(Ingredient $ingredient, float $quantityBefore): void
    {
        $minQuantity = (float) $ingredient->min_quantity;

        // Was above threshold before, now at/below — threshold just crossed
        if ($quantityBefore > $minQuantity && $ingredient->is_low_stock) {
            // Broadcast real-time event
            event(new LowStockAlert($ingredient));

            // Send database notification (always) + email (if restaurant setting allows)
            $restaurant = $ingredient->restaurant()->withoutGlobalScope('restaurant')->first();
            if (!$restaurant) {
                return;
            }

            $settings = NotificationSetting::withoutGlobalScope('restaurant')
                ->where('restaurant_id', $restaurant->id)
                ->first();
            $sendEmail = $settings ? (bool) $settings->email_low_stock : true;

            $owner = $restaurant->owner;
            if ($owner) {
                $owner->notify(new RealTimeLowStockNotification($ingredient, $sendEmail));
            }
        }
    }

    /**
     * Restore stock for cancelled order
     */
    public function restoreForOrder(Order $order): Collection
    {
        $movements = collect();

        return DB::transaction(function () use ($order, $movements) {
            foreach ($order->items as $item) {
                if (!$item->dish) {
                    continue;
                }

                foreach ($item->dish->ingredients as $ingredient) {
                    $quantityToRestore = $ingredient->pivot->quantity * $item->quantity;
                    
                    $movement = $this->entry($ingredient, $quantityToRestore, null, [
                        'reference_type' => get_class($order),
                        'reference_id' => $order->id,
                        'reason' => "Annulation commande {$order->reference}",
                    ]);

                    $movements->push($movement);
                }
            }

            return $movements;
        });
    }

    /**
     * Get low stock ingredients
     */
    public function getLowStock(): Collection
    {
        return Ingredient::where('restaurant_id', $this->restaurant->id)
            ->where('is_active', true)
            ->whereColumn('current_quantity', '<=', 'min_quantity')
            ->get();
    }

    /**
     * Get out of stock ingredients
     */
    public function getOutOfStock(): Collection
    {
        return Ingredient::where('restaurant_id', $this->restaurant->id)
            ->where('is_active', true)
            ->where('current_quantity', '<=', 0)
            ->get();
    }

    /**
     * Get total stock value
     */
    public function getTotalStockValue(): int
    {
        return (int) Ingredient::where('restaurant_id', $this->restaurant->id)
            ->where('is_active', true)
            ->sum(DB::raw('current_quantity * unit_cost'));
    }

    /**
     * Check if dish can be prepared (has enough stock)
     */
    public function canPrepareDish(Dish $dish, int $quantity = 1): bool
    {
        foreach ($dish->ingredients as $ingredient) {
            $required = $ingredient->pivot->quantity * $quantity;
            
            if ($ingredient->current_quantity < $required) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get missing ingredients for a dish
     */
    public function getMissingIngredients(Dish $dish, int $quantity = 1): Collection
    {
        $missing = collect();

        foreach ($dish->ingredients as $ingredient) {
            $required = $ingredient->pivot->quantity * $quantity;
            
            if ($ingredient->current_quantity < $required) {
                $missing->push([
                    'ingredient' => $ingredient,
                    'required' => $required,
                    'available' => $ingredient->current_quantity,
                    'shortage' => $required - $ingredient->current_quantity,
                ]);
            }
        }

        return $missing;
    }
}

