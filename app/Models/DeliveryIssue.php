<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DeliveryIssue extends Model
{
    protected $fillable = [
        'delivery_id',
        'driver_id',
        'issue_type',
        'issue_details',
        'resolution_status',
        'resolution_notes',
        'reported_at',
        'resolved_at',
    ];

    protected $casts = [
        'reported_at' => 'datetime',
        'resolved_at' => 'datetime',
    ];

    public function delivery(): BelongsTo
    {
        return $this->belongsTo(Delivery::class);
    }

    public function driver(): BelongsTo
    {
        return $this->belongsTo(DeliveryDriver::class, 'driver_id');
    }
}
