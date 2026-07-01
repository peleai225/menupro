<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DriverEarning extends Model
{
    protected $fillable = [
        'driver_id',
        'order_id',
        'delivery_id',
        'gross_amount',
        'platform_cut',
        'net_amount',
        'status',
        'paid_at',
        'payment_method',
        'payment_reference',
    ];

    protected $casts = [
        'gross_amount' => 'integer',
        'platform_cut' => 'integer',
        'net_amount' => 'integer',
        'paid_at' => 'datetime',
    ];

    public function driver(): BelongsTo
    {
        return $this->belongsTo(DeliveryDriver::class, 'driver_id');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(Delivery::class);
    }

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeAvailable($query)
    {
        return $query->where('status', 'available');
    }
}
