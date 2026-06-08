<?php

namespace App\Models;

use App\Enums\DeliveryStatus;
use App\Models\Traits\BelongsToRestaurant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Delivery extends Model
{
    use BelongsToRestaurant;

    protected $fillable = [
        'order_id',
        'restaurant_id',
        'driver_id',
        'status',
        'delivery_address',
        'delivery_phone',
        'delivery_instructions',
        'pickup_latitude',
        'pickup_longitude',
        'delivery_latitude',
        'delivery_longitude',
        'driver_latitude',
        'driver_longitude',
        'driver_location_at',
        'assigned_at',
        'picked_up_at',
        'delivered_at',
        'cancelled_at',
        'cancellation_reason',
        'estimated_minutes',
    ];

    protected $casts = [
        'status' => DeliveryStatus::class,
        'pickup_latitude' => 'decimal:7',
        'pickup_longitude' => 'decimal:7',
        'delivery_latitude' => 'decimal:7',
        'delivery_longitude' => 'decimal:7',
        'driver_latitude' => 'decimal:7',
        'driver_longitude' => 'decimal:7',
        'driver_location_at' => 'datetime',
        'assigned_at' => 'datetime',
        'picked_up_at' => 'datetime',
        'delivered_at' => 'datetime',
        'cancelled_at' => 'datetime',
    ];

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(DeliveryDriver::class, 'driver_id');
    }

    public function transitionTo(DeliveryStatus $newStatus): bool
    {
        $allowed = match ($this->status) {
            DeliveryStatus::PENDING => [DeliveryStatus::ASSIGNED, DeliveryStatus::CANCELLED],
            DeliveryStatus::ASSIGNED => [DeliveryStatus::HEADING_TO_RESTAURANT, DeliveryStatus::CANCELLED],
            DeliveryStatus::HEADING_TO_RESTAURANT => [DeliveryStatus::PICKED_UP, DeliveryStatus::CANCELLED],
            DeliveryStatus::PICKED_UP => [DeliveryStatus::DELIVERING],
            DeliveryStatus::DELIVERING => [DeliveryStatus::DELIVERED],
            default => [],
        };

        if (!in_array($newStatus, $allowed)) {
            return false;
        }

        $this->status = $newStatus;

        match ($newStatus) {
            DeliveryStatus::ASSIGNED => $this->assigned_at = now(),
            DeliveryStatus::PICKED_UP => $this->picked_up_at = now(),
            DeliveryStatus::DELIVERED => $this->delivered_at = now(),
            DeliveryStatus::CANCELLED => $this->cancelled_at = now(),
            default => null,
        };

        return $this->save();
    }
}
