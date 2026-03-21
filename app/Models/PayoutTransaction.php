<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PayoutTransaction extends Model
{
    protected $fillable = [
        'restaurant_id',
        'restaurant_wallet_id',
        'gateway',
        'gateway_transaction_id',
        'cinetpay_transaction_id',
        'wave_payout_id',
        'client_reference',
        'amount',
        'fee',
        'currency',
        'status',
        'mobile',
        'recipient_name',
        'payment_reason',
        'idempotency_key',
        'payout_error',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'fee' => 'decimal:2',
        'payout_error' => 'array',
    ];

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function restaurantWallet(): BelongsTo
    {
        return $this->belongsTo(RestaurantWallet::class);
    }
}
