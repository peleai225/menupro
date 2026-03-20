<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PaymentTransaction extends Model
{
    protected $fillable = [
        'order_id',
        'restaurant_id',
        'gateway',
        'gateway_transaction_id',
        'cinetpay_transaction_id',
        'wave_checkout_id',
        'wave_payment_id',
        'amount',
        'commission',
        'net_amount',
        'currency',
        'status',
        'client_reference',
        'metadata',
    ];

    protected $casts = [
        'amount' => 'decimal:2',
        'commission' => 'decimal:2',
        'net_amount' => 'decimal:2',
        'metadata' => 'array',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }
}
