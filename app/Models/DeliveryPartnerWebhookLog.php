<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryPartnerWebhookLog extends Model
{
    protected $fillable = [
        'delivery_partner_id', 'parcel_id', 'event', 'url', 'payload', 'attempt',
        'success', 'status_code', 'response_excerpt', 'error',
    ];

    protected $casts = [
        'payload' => 'array',
        'success' => 'boolean',
        'attempt' => 'integer',
        'status_code' => 'integer',
    ];

    public function partner(): BelongsTo
    {
        return $this->belongsTo(DeliveryPartner::class, 'delivery_partner_id');
    }

    public function parcel(): BelongsTo
    {
        return $this->belongsTo(Parcel::class);
    }
}
