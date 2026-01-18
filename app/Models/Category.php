<?php

namespace App\Models;

use App\Models\Traits\BelongsToRestaurant;
use App\Models\Traits\HasSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Category extends Model
{
    use HasFactory, BelongsToRestaurant, HasSlug;

    protected $fillable = [
        'restaurant_id',
        'name',
        'slug',
        'description',
        'image_path',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function dishes(): HasMany
    {
        return $this->hasMany(Dish::class);
    }

    public function activeDishes(): HasMany
    {
        return $this->hasMany(Dish::class)->where('is_active', true);
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    public function scopeWithActiveDishes($query)
    {
        return $query->whereHas('dishes', fn($q) => $q->where('is_active', true));
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

    public function getDishesCountAttribute(): int
    {
        return $this->dishes()->count();
    }

    public function getActiveDishesCountAttribute(): int
    {
        return $this->dishes()->where('is_active', true)->count();
    }

    // =========================================================================
    // METHODS
    // =========================================================================

    /**
     * Move category up in sort order
     */
    public function moveUp(): void
    {
        $previous = static::where('restaurant_id', $this->restaurant_id)
            ->where('sort_order', '<', $this->sort_order)
            ->orderByDesc('sort_order')
            ->first();

        if ($previous) {
            $tempOrder = $this->sort_order;
            $this->sort_order = $previous->sort_order;
            $previous->sort_order = $tempOrder;
            
            $this->save();
            $previous->save();
        }
    }

    /**
     * Move category down in sort order
     */
    public function moveDown(): void
    {
        $next = static::where('restaurant_id', $this->restaurant_id)
            ->where('sort_order', '>', $this->sort_order)
            ->orderBy('sort_order')
            ->first();

        if ($next) {
            $tempOrder = $this->sort_order;
            $this->sort_order = $next->sort_order;
            $next->sort_order = $tempOrder;
            
            $this->save();
            $next->save();
        }
    }
}

