<?php

namespace App\Models;

use App\Enums\Unit;
use App\Models\Traits\BelongsToRestaurant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Ingredient extends Model
{
    use HasFactory, BelongsToRestaurant;

    protected $fillable = [
        'restaurant_id',
        'ingredient_category_id',
        'name',
        'sku',
        'unit',
        'current_quantity',
        'min_quantity',
        'max_quantity',
        'unit_cost',
        'last_purchase_cost',
        'track_expiry',
        'default_expiry_days',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'unit' => Unit::class,
        'current_quantity' => 'decimal:3',
        'min_quantity' => 'decimal:3',
        'max_quantity' => 'decimal:3',
        'unit_cost' => 'integer',
        'last_purchase_cost' => 'integer',
        'track_expiry' => 'boolean',
        'default_expiry_days' => 'integer',
        'is_active' => 'boolean',
    ];

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function category(): BelongsTo
    {
        return $this->belongsTo(IngredientCategory::class, 'ingredient_category_id');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function dishes(): BelongsToMany
    {
        return $this->belongsToMany(Dish::class, 'dish_ingredients')
            ->withPivot(['quantity', 'unit'])
            ->withTimestamps();
    }

    public function suppliers(): BelongsToMany
    {
        return $this->belongsToMany(Supplier::class, 'ingredient_supplier')
            ->withPivot(['unit_price', 'supplier_sku', 'is_preferred'])
            ->withTimestamps();
    }

    public function preferredSupplier(): BelongsTo
    {
        return $this->belongsTo(Supplier::class)
            ->whereHas('ingredients', function ($query) {
                $query->where('ingredient_id', $this->id)
                    ->where('is_preferred', true);
            });
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeLowStock($query)
    {
        return $query->whereColumn('current_quantity', '<=', 'min_quantity');
    }

    public function scopeOutOfStock($query)
    {
        return $query->where('current_quantity', '<=', 0);
    }

    public function scopeInStock($query)
    {
        return $query->where('current_quantity', '>', 0);
    }

    public function scopeInCategory($query, int $categoryId)
    {
        return $query->where('ingredient_category_id', $categoryId);
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('sku', 'like', "%{$term}%");
        });
    }

    // =========================================================================
    // ACCESSORS
    // =========================================================================

    public function getFormattedQuantityAttribute(): string
    {
        return number_format($this->current_quantity, 2, ',', ' ') . ' ' . $this->unit->shortLabel();
    }

    public function getFormattedUnitCostAttribute(): string
    {
        return number_format($this->unit_cost, 0, ',', ' ') . ' FCFA/' . $this->unit->shortLabel();
    }

    public function getStockValueAttribute(): int
    {
        return (int) round($this->current_quantity * $this->unit_cost);
    }

    public function getFormattedStockValueAttribute(): string
    {
        return number_format($this->stock_value, 0, ',', ' ') . ' FCFA';
    }

    public function getIsLowStockAttribute(): bool
    {
        return $this->current_quantity <= $this->min_quantity;
    }

    public function getIsOutOfStockAttribute(): bool
    {
        return $this->current_quantity <= 0;
    }

    public function getStockStatusAttribute(): string
    {
        if ($this->is_out_of_stock) {
            return 'out_of_stock';
        }
        if ($this->is_low_stock) {
            return 'low_stock';
        }
        return 'in_stock';
    }

    public function getStockStatusLabelAttribute(): string
    {
        return match ($this->stock_status) {
            'out_of_stock' => 'Rupture',
            'low_stock' => 'Stock bas',
            'in_stock' => 'En stock',
        };
    }

    public function getStockStatusColorAttribute(): string
    {
        return match ($this->stock_status) {
            'out_of_stock' => 'error',
            'low_stock' => 'warning',
            'in_stock' => 'success',
        };
    }

    public function getStockPercentageAttribute(): float
    {
        if (!$this->max_quantity || $this->max_quantity <= 0) {
            return 100;
        }
        return min(100, ($this->current_quantity / $this->max_quantity) * 100);
    }

    // =========================================================================
    // METHODS
    // =========================================================================

    /**
     * Decrease stock
     */
    public function decreaseStock(float $quantity, string $reason = null, $reference = null): StockMovement
    {
        $quantityBefore = $this->current_quantity;
        $this->current_quantity = max(0, $this->current_quantity - $quantity);
        $this->save();

        return $this->movements()->create([
            'restaurant_id' => $this->restaurant_id,
            'user_id' => auth()->id(),
            'type' => 'exit_manual',
            'quantity' => -abs($quantity),
            'quantity_before' => $quantityBefore,
            'quantity_after' => $this->current_quantity,
            'reason' => $reason,
            'reference_type' => $reference ? get_class($reference) : null,
            'reference_id' => $reference?->id,
        ]);
    }

    /**
     * Increase stock
     */
    public function increaseStock(float $quantity, int $unitCost = null, string $reason = null, $reference = null): StockMovement
    {
        $quantityBefore = $this->current_quantity;
        $this->current_quantity += $quantity;
        
        if ($unitCost !== null) {
            $this->last_purchase_cost = $unitCost;
            // Update average cost
            $totalValue = ($quantityBefore * $this->unit_cost) + ($quantity * $unitCost);
            $this->unit_cost = (int) round($totalValue / $this->current_quantity);
        }
        
        $this->save();

        return $this->movements()->create([
            'restaurant_id' => $this->restaurant_id,
            'user_id' => auth()->id(),
            'type' => 'entry',
            'quantity' => abs($quantity),
            'quantity_before' => $quantityBefore,
            'quantity_after' => $this->current_quantity,
            'unit_cost' => $unitCost,
            'reason' => $reason,
            'reference_type' => $reference ? get_class($reference) : null,
            'reference_id' => $reference?->id,
        ]);
    }

    /**
     * Adjust stock (inventory)
     */
    public function adjustStock(float $newQuantity, string $reason = null): StockMovement
    {
        $quantityBefore = $this->current_quantity;
        $difference = $newQuantity - $quantityBefore;
        $this->current_quantity = $newQuantity;
        $this->save();

        return $this->movements()->create([
            'restaurant_id' => $this->restaurant_id,
            'user_id' => auth()->id(),
            'type' => 'adjustment',
            'quantity' => $difference,
            'quantity_before' => $quantityBefore,
            'quantity_after' => $newQuantity,
            'reason' => $reason ?? 'Ajustement inventaire',
        ]);
    }

    /**
     * Record waste/loss
     */
    public function recordWaste(float $quantity, string $reason = null): StockMovement
    {
        $quantityBefore = $this->current_quantity;
        $this->current_quantity = max(0, $this->current_quantity - $quantity);
        $this->save();

        return $this->movements()->create([
            'restaurant_id' => $this->restaurant_id,
            'user_id' => auth()->id(),
            'type' => 'exit_waste',
            'quantity' => -abs($quantity),
            'quantity_before' => $quantityBefore,
            'quantity_after' => $this->current_quantity,
            'reason' => $reason ?? 'Perte/Gaspillage',
        ]);
    }
}

