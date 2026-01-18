<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DishOption extends Model
{
    use HasFactory;

    protected $fillable = [
        'option_group_id',
        'name',
        'price_adjustment',
        'sort_order',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'price_adjustment' => 'integer',
        'sort_order' => 'integer',
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function optionGroup(): BelongsTo
    {
        return $this->belongsTo(DishOptionGroup::class, 'option_group_id');
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

    // =========================================================================
    // ACCESSORS
    // =========================================================================

    public function getFormattedPriceAdjustmentAttribute(): string
    {
        if ($this->price_adjustment === 0) {
            return '';
        }
        
        $prefix = $this->price_adjustment > 0 ? '+' : '';
        return $prefix . number_format($this->price_adjustment, 0, ',', ' ') . ' FCFA';
    }
}

