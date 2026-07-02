<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryCity extends Model
{
    protected $fillable = [
        'name',
        'slug',
        'country',
        'center_latitude',
        'center_longitude',
        'coverage_radius_km',
        'is_active',
        'delivery_base_fee',
        'delivery_fee_per_km',
        'peak_hour_surcharge_percent',
        'max_delivery_distance_km',
        'min_order_amount',
        'currency',
    ];

    protected $casts = [
        'center_latitude' => 'decimal:7',
        'center_longitude' => 'decimal:7',
        'coverage_radius_km' => 'integer',
        'is_active' => 'boolean',
        'delivery_base_fee' => 'integer',
        'delivery_fee_per_km' => 'integer',
        'peak_hour_surcharge_percent' => 'integer',
        'max_delivery_distance_km' => 'integer',
        'min_order_amount' => 'integer',
    ];

    public function zones(): HasMany
    {
        return $this->hasMany(DeliveryZone::class);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCountry($query, string $country)
    {
        return $query->where('country', $country);
    }

    public function isWithinCoverage(float $lat, float $lng): bool
    {
        return $this->distanceToCenter($lat, $lng) <= $this->coverage_radius_km;
    }

    public function distanceToCenter(float $lat, float $lng): float
    {
        return self::haversineKm(
            (float) $this->center_latitude,
            (float) $this->center_longitude,
            $lat,
            $lng
        );
    }

    public function calculateFee(float $distanceKm): int
    {
        $baseFee = $this->delivery_base_fee;
        $distanceFee = (int) round($distanceKm * $this->delivery_fee_per_km);
        $rawFee = $baseFee + $distanceFee;

        if ($this->isPeakHour()) {
            $rawFee = (int) round($rawFee * (1 + $this->peak_hour_surcharge_percent / 100));
        }

        return $rawFee;
    }

    public function isPeakHour(): bool
    {
        $hour = (int) now()->format('G');
        return ($hour >= 11 && $hour < 14) || ($hour >= 18 && $hour < 21);
    }

    public function getFormattedBaseFeeAttribute(): string
    {
        return number_format($this->delivery_base_fee / 100, 0, ',', ' ') . ' FCFA';
    }

    public function getFormattedFeePerKmAttribute(): string
    {
        return number_format($this->delivery_fee_per_km / 100, 0, ',', ' ') . ' FCFA/km';
    }

    public static function haversineKm(float $lat1, float $lng1, float $lat2, float $lng2): float
    {
        $earthRadius = 6371;
        $dLat = deg2rad($lat2 - $lat1);
        $dLng = deg2rad($lng2 - $lng1);

        $a = sin($dLat / 2) ** 2
            + cos(deg2rad($lat1)) * cos(deg2rad($lat2)) * sin($dLng / 2) ** 2;

        return $earthRadius * 2 * asin(sqrt($a));
    }
}
