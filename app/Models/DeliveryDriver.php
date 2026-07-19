<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DeliveryDriver extends Model
{
    protected $fillable = [
        'user_id',
        'name',
        'phone',
        'email',
        'city',
        'zone',
        'vehicle_type',
        'vehicle_plate',
        'token',
        'cni_number',
        'photo_path',
        'cni_photo_path',
        'license_photo_path',
        'vehicle_photo_path',
        'verification_status',
        'is_active',
        'is_available',
        'latitude',
        'longitude',
        'location_updated_at',
        'total_deliveries',
        'rating',
        'total_ratings',
        'total_cancelled',
        'total_earnings_xof',
        'fcm_token',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_available' => 'boolean',
        'latitude' => 'decimal:7',
        'longitude' => 'decimal:7',
        'location_updated_at' => 'datetime',
        'total_deliveries' => 'integer',
        'rating' => 'decimal:2',
        'total_ratings' => 'integer',
        'total_cancelled' => 'integer',
        'total_earnings_xof' => 'integer',
    ];

    protected $hidden = ['token', 'fcm_token'];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function deliveries(): HasMany
    {
        return $this->hasMany(Delivery::class, 'driver_id');
    }

    public function earnings(): HasMany
    {
        return $this->hasMany(DriverEarning::class, 'driver_id');
    }

    public function locations(): HasMany
    {
        return $this->hasMany(DriverLocation::class, 'driver_id');
    }

    public function activeDelivery()
    {
        return $this->deliveries()
            ->whereIn('status', ['assigned', 'heading_to_restaurant', 'picked_up', 'delivering'])
            ->first();
    }

    public function isApproved(): bool
    {
        return $this->verification_status === 'approved';
    }

    public function isOnline(): bool
    {
        return $this->is_active && $this->is_available && $this->isApproved();
    }

    public function updateLocation(float $lat, float $lng, array $extra = []): void
    {
        $this->update([
            'latitude' => $lat,
            'longitude' => $lng,
            'location_updated_at' => now(),
        ]);

        $this->locations()->create([
            'latitude' => $lat,
            'longitude' => $lng,
            'accuracy' => $extra['accuracy'] ?? null,
            'speed' => $extra['speed'] ?? null,
            'heading' => $extra['heading'] ?? null,
            'recorded_at' => now(),
        ]);
    }

    public function scopeAvailable($query)
    {
        return $query->where('is_active', true)
            ->where('is_available', true)
            ->where('verification_status', 'approved');
    }

    public function scopeInCity($query, string $city)
    {
        return $query->where('city', $city);
    }
}
