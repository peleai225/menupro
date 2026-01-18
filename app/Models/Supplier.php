<?php

namespace App\Models;

use App\Models\Traits\BelongsToRestaurant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Supplier extends Model
{
    use HasFactory, BelongsToRestaurant;

    protected $fillable = [
        'restaurant_id',
        'name',
        'contact_name',
        'email',
        'phone',
        'address',
        'city',
        'min_order_amount',
        'delivery_days',
        'payment_terms',
        'notes',
        'is_active',
    ];

    protected $casts = [
        'min_order_amount' => 'integer',
        'delivery_days' => 'integer',
        'is_active' => 'boolean',
    ];

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class, 'ingredient_supplier')
            ->withPivot(['unit_price', 'supplier_sku', 'is_preferred'])
            ->withTimestamps();
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('contact_name', 'like', "%{$term}%")
              ->orWhere('email', 'like', "%{$term}%");
        });
    }

    // =========================================================================
    // ACCESSORS
    // =========================================================================

    public function getIngredientsCountAttribute(): int
    {
        return $this->ingredients()->count();
    }

    public function getFormattedMinOrderAttribute(): ?string
    {
        if (!$this->min_order_amount) {
            return null;
        }
        return number_format($this->min_order_amount, 0, ',', ' ') . ' FCFA';
    }

    public function getDeliveryTimeAttribute(): ?string
    {
        if (!$this->delivery_days) {
            return null;
        }
        return $this->delivery_days . ' jour(s)';
    }
}

