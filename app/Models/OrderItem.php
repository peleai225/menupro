<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id',
        'dish_id',
        'dish_name',
        'unit_price',
        'quantity',
        'total_price',
        'selected_options',
        'options_price',
        'special_instructions',
    ];

    protected $casts = [
        'unit_price' => 'integer',
        'quantity' => 'integer',
        'total_price' => 'integer',
        'selected_options' => 'array',
        'options_price' => 'integer',
    ];

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function dish(): BelongsTo
    {
        return $this->belongsTo(Dish::class);
    }

    // =========================================================================
    // ACCESSORS
    // =========================================================================

    public function getFormattedUnitPriceAttribute(): string
    {
        return number_format($this->unit_price, 0, ',', ' ') . ' FCFA';
    }

    public function getFormattedTotalPriceAttribute(): string
    {
        return number_format($this->total_price, 0, ',', ' ') . ' FCFA';
    }

    public function getSelectedOptionsListAttribute(): array
    {
        if (!$this->selected_options) {
            return [];
        }

        return collect($this->selected_options)->pluck('name')->toArray();
    }

    public function getSelectedOptionsSummaryAttribute(): string
    {
        $list = $this->selected_options_list;
        
        if (empty($list)) {
            return '';
        }

        return implode(', ', $list);
    }

    // =========================================================================
    // METHODS
    // =========================================================================

    /**
     * Update quantity and recalculate
     */
    public function updateQuantity(int $quantity): bool
    {
        $this->quantity = $quantity;
        $this->total_price = $this->unit_price * $quantity;
        
        if ($this->save()) {
            $this->order->calculateTotals();
            $this->order->save();
            return true;
        }
        
        return false;
    }
}

