<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommissionLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'restaurant_id',
        'order_id',
        'amount',
        'order_total',
        'commission_rate',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'order_total' => 'integer',
        'commission_rate' => 'decimal:2',
    ];

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }
}
