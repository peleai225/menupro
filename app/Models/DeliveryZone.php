<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryZone extends Model
{
    protected $fillable = [
        'delivery_city_id',
        'name',
        'city',
        'country',
        'center_latitude',
        'center_longitude',
        'radius_km',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'delivery_city_id' => 'integer',
        'center_latitude' => 'decimal:7',
        'center_longitude' => 'decimal:7',
        'radius_km' => 'integer',
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    public function deliveryCity(): BelongsTo
    {
        return $this->belongsTo(DeliveryCity::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCity($query, string $city)
    {
        return $query->where('city', $city);
    }

    public function scopeByCityId($query, int $cityId)
    {
        return $query->where('delivery_city_id', $cityId);
    }

    public function containsPoint(float $lat, float $lng): bool
    {
        if (!$this->center_latitude || !$this->center_longitude) {
            return false;
        }

        $distance = DeliveryCity::haversineKm(
            (float) $this->center_latitude,
            (float) $this->center_longitude,
            $lat,
            $lng
        );

        return $distance <= $this->radius_km;
    }
}
