<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Customer extends Model
{
    protected $fillable = [
        'user_id',
        'phone',
        'city',
        'default_delivery_address',
        'default_latitude',
        'default_longitude',
        'avatar_path',
        'is_active',
        'total_orders',
        'last_order_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'default_latitude' => 'decimal:7',
        'default_longitude' => 'decimal:7',
        'total_orders' => 'integer',
        'last_order_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function addresses(): HasMany
    {
        return $this->hasMany(CustomerAddress::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function defaultAddress()
    {
        return $this->addresses()->where('is_default', true)->first();
    }
}
