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
        'auto_payout_enabled',
        'min_payout_amount',
        'payout_gateway',
        'recipient_name',
    ];

    protected $casts = [
        'balance' => 'decimal:2',
        'total_collected' => 'decimal:2',
        'total_withdrawn' => 'decimal:2',
        'auto_payout_enabled' => 'boolean',
        'min_payout_amount' => 'integer',
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
