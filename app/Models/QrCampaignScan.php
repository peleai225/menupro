<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Scan d'un QR de campagne : date + type d'appareil grossier, aucune donnée personnelle.
 */
class QrCampaignScan extends Model
{
    public $timestamps = false;

    protected $fillable = ['qr_campaign_id', 'device', 'scanned_at'];

    protected $casts = [
        'scanned_at' => 'datetime',
    ];

    public function campaign(): BelongsTo
    {
        return $this->belongsTo(QrCampaign::class, 'qr_campaign_id');
    }

    /** Appareil grossier depuis le User-Agent (jamais stocké). */
    public static function deviceFromUserAgent(?string $userAgent): string
    {
        $ua = strtolower((string) $userAgent);

        return preg_match('/mobile|android|iphone|ipad|ipod|tablet|opera mini|iemobile|kaios/', $ua)
            ? 'mobile'
            : 'desktop';
    }
}
