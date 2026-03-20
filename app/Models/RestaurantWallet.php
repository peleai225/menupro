<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RestaurantWallet extends Model
{
    protected $fillable = [
        'restaurant_id',
        'balance',
        'total_collected',
        'total_withdrawn',
        'phone',
        'prefix',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'total_collected' => 'decimal:2',
        'total_withdrawn' => 'decimal:2',
    ];

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function payoutTransactions(): HasMany
    {
        return $this->hasMany(PayoutTransaction::class);
    }
}
