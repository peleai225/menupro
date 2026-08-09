<?php
// app/Models/DeliveryZonePrice.php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryZonePrice extends Model
{
    protected $fillable = [
        'from_zone_id',
        'to_zone_id',
        'price_xof',
        'is_active',
    ];

    protected $casts = [
        'price_xof' => 'integer',
        'is_active' => 'boolean',
    ];

    public function fromZone(): BelongsTo
    {
        return $this->belongsTo(DeliveryZone::class, 'from_zone_id');
    }

    public function toZone(): BelongsTo
    {
        return $this->belongsTo(DeliveryZone::class, 'to_zone_id');
    }

    /**
     * Cherche le prix entre deux zones.
     * Si toZoneId est null ou introuvable, cherche le fallback (to_zone_id IS NULL).
     */
    public static function findPrice(int $fromZoneId, ?int $toZoneId): ?self
    {
        // 1. Prix exact from→to
        if ($toZoneId !== null) {
            $exact = static::where('from_zone_id', $fromZoneId)
                ->where('to_zone_id', $toZoneId)
                ->where('is_active', true)
                ->first();
            if ($exact) return $exact;
        }

        // 2. Fallback hors-zone (to_zone_id IS NULL)
        return static::where('from_zone_id', $fromZoneId)
            ->whereNull('to_zone_id')
            ->where('is_active', true)
            ->first();
    }
}
