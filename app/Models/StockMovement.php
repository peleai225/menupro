<?php

namespace App\Models;

use App\Enums\StockMovementType;
use App\Models\Traits\BelongsToRestaurant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class StockMovement extends Model
{
    use HasFactory, BelongsToRestaurant;

    protected $fillable = [
        'restaurant_id',
        'ingredient_id',
        'user_id',
        'type',
        'quantity',
        'quantity_before',
        'quantity_after',
        'unit_cost',
        'reference_type',
        'reference_id',
        'expiry_date',
        'batch_number',
        'reason',
    ];

    protected $casts = [
        'type' => StockMovementType::class,
        'quantity' => 'decimal:3',
        'quantity_before' => 'decimal:3',
        'quantity_after' => 'decimal:3',
        'unit_cost' => 'integer',
        'expiry_date' => 'date',
    ];

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reference(): MorphTo
    {
        return $this->morphTo();
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    public function scopeEntries($query)
    {
        return $query->where('type', StockMovementType::ENTRY);
    }

    public function scopeExits($query)
    {
        return $query->whereIn('type', [
            StockMovementType::EXIT_SALE,
            StockMovementType::EXIT_MANUAL,
            StockMovementType::EXIT_WASTE,
        ]);
    }

    public function scopeForIngredient($query, int $ingredientId)
    {
        return $query->where('ingredient_id', $ingredientId);
    }

    public function scopeByType($query, StockMovementType $type)
    {
        return $query->where('type', $type);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);
    }

    public function scopeDateRange($query, $start, $end)
    {
        return $query->whereBetween('created_at', [$start, $end]);
    }

    // =========================================================================
    // ACCESSORS
    // =========================================================================

    public function getFormattedQuantityAttribute(): string
    {
        $prefix = $this->quantity > 0 ? '+' : '';
        return $prefix . number_format($this->quantity, 2, ',', ' ');
    }

    public function getTotalValueAttribute(): int
    {
        if (!$this->unit_cost) {
            return 0;
        }
        return (int) abs($this->quantity * $this->unit_cost);
    }

    public function getFormattedTotalValueAttribute(): string
    {
        return number_format($this->total_value, 0, ',', ' ') . ' FCFA';
    }

    public function getIsPositiveAttribute(): bool
    {
        return $this->quantity > 0;
    }

    public function getIsNegativeAttribute(): bool
    {
        return $this->quantity < 0;
    }
}

