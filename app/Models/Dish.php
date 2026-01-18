<?php

namespace App\Models;

use App\Models\Traits\BelongsToRestaurant;
use App\Models\Traits\HasSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Dish extends Model
{
    use HasFactory, BelongsToRestaurant, HasSlug, SoftDeletes;

    protected $fillable = [
        'restaurant_id',
        'category_id',
        'name',
        'slug',
        'description',
        'price',
        'compare_price',
        'image_path',
        'gallery',
        'is_active',
        'is_featured',
        'is_new',
        'is_spicy',
        'is_vegetarian',
        'is_vegan',
        'is_gluten_free',
        'track_stock',
        'stock_quantity',
        'allow_out_of_stock_orders',
        'prep_time',
        'calories',
        'allergens',
        'nutritional_info',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'integer',
        'compare_price' => 'integer',
        'gallery' => 'array',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'is_new' => 'boolean',
        'is_spicy' => 'boolean',
        'is_vegetarian' => 'boolean',
        'is_vegan' => 'boolean',
        'is_gluten_free' => 'boolean',
        'track_stock' => 'boolean',
        'stock_quantity' => 'integer',
        'allow_out_of_stock_orders' => 'boolean',
        'prep_time' => 'integer',
        'calories' => 'integer',
        'allergens' => 'array',
        'nutritional_info' => 'array',
        'sort_order' => 'integer',
    ];

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function orderItems(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function optionGroups(): BelongsToMany
    {
        return $this->belongsToMany(DishOptionGroup::class, 'dish_option_group', 'dish_id', 'option_group_id');
    }

    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class, 'dish_ingredients')
            ->withPivot(['quantity', 'unit'])
            ->withTimestamps();
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeNew($query)
    {
        return $query->where('is_new', true);
    }

    public function scopeInStock($query)
    {
        return $query->where(function ($q) {
            $q->where('track_stock', false)
              ->orWhere('stock_quantity', '>', 0)
              ->orWhere('allow_out_of_stock_orders', true);
        });
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function scopeInCategory($query, int $categoryId)
    {
        return $query->where('category_id', $categoryId);
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('description', 'like', "%{$term}%");
        });
    }

    // =========================================================================
    // ACCESSORS
    // =========================================================================

    public function getImageUrlAttribute(): ?string
    {
        if (!$this->image_path) {
            return null;
        }
        return Storage::url($this->image_path);
    }

    public function getGalleryUrlsAttribute(): array
    {
        if (!$this->gallery) {
            return [];
        }
        return array_map(fn($path) => Storage::url($path), $this->gallery);
    }

    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->price, 0, ',', ' ') . ' FCFA';
    }

    public function getFormattedComparePriceAttribute(): ?string
    {
        if (!$this->compare_price) {
            return null;
        }
        return number_format($this->compare_price, 0, ',', ' ') . ' FCFA';
    }

    public function getDiscountPercentageAttribute(): ?int
    {
        if (!$this->compare_price || $this->compare_price <= $this->price) {
            return null;
        }
        return (int) round(($this->compare_price - $this->price) / $this->compare_price * 100);
    }

    public function getIsInStockAttribute(): bool
    {
        if (!$this->track_stock) {
            return true;
        }
        return $this->stock_quantity > 0 || $this->allow_out_of_stock_orders;
    }

    public function getIsAvailableAttribute(): bool
    {
        return $this->is_active && $this->is_in_stock;
    }

    public function getBadgesAttribute(): array
    {
        $badges = [];
        
        if ($this->is_new) $badges[] = ['label' => 'Nouveau', 'color' => 'primary'];
        if ($this->is_featured) $badges[] = ['label' => 'Populaire', 'color' => 'accent'];
        if ($this->is_spicy) $badges[] = ['label' => 'Épicé', 'color' => 'error'];
        if ($this->is_vegetarian) $badges[] = ['label' => 'Végétarien', 'color' => 'success'];
        if ($this->is_vegan) $badges[] = ['label' => 'Végan', 'color' => 'success'];
        if ($this->is_gluten_free) $badges[] = ['label' => 'Sans gluten', 'color' => 'info'];
        if ($this->discount_percentage) $badges[] = ['label' => "-{$this->discount_percentage}%", 'color' => 'accent'];
        
        return $badges;
    }

    /**
     * Get estimated cost from ingredients
     */
    public function getCostAttribute(): int
    {
        $cost = 0;
        
        foreach ($this->ingredients as $ingredient) {
            $quantity = $ingredient->pivot->quantity;
            $unitCost = $ingredient->unit_cost;
            $cost += $quantity * $unitCost;
        }
        
        return $cost;
    }

    /**
     * Get profit margin
     */
    public function getMarginAttribute(): ?float
    {
        if ($this->price <= 0) {
            return null;
        }
        return round(($this->price - $this->cost) / $this->price * 100, 2);
    }

    // =========================================================================
    // METHODS
    // =========================================================================

    /**
     * Decrease stock quantity
     */
    public function decreaseStock(int $quantity = 1): bool
    {
        if (!$this->track_stock) {
            return true;
        }

        $this->stock_quantity = max(0, $this->stock_quantity - $quantity);
        return $this->save();
    }

    /**
     * Increase stock quantity
     */
    public function increaseStock(int $quantity = 1): bool
    {
        if (!$this->track_stock) {
            return true;
        }

        $this->stock_quantity += $quantity;
        return $this->save();
    }

    /**
     * Check if quantity is available
     */
    public function hasStock(int $quantity = 1): bool
    {
        if (!$this->track_stock) {
            return true;
        }
        return $this->stock_quantity >= $quantity || $this->allow_out_of_stock_orders;
    }

    /**
     * Deduct ingredients from stock
     */
    public function deductIngredientsStock(int $quantity = 1): void
    {
        foreach ($this->ingredients as $ingredient) {
            $amountToDeduct = $ingredient->pivot->quantity * $quantity;
            $ingredient->decreaseStock($amountToDeduct);
        }
    }
}

