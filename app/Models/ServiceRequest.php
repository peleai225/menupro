<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServiceRequest extends Model
{
    protected $fillable = [
        'restaurant_id',
        'table_number',
        'type',
        'notes',
        'status',
        'done_at',
    ];

    protected $casts = [
        'done_at' => 'datetime',
    ];

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            'cleaning'   => 'Nettoyage / Ménage',
            'assistance' => 'Appel du personnel',
            'checkout'   => 'Addition / Check-out',
            'other'      => 'Autre demande',
            default      => $this->type,
        };
    }

    public function typeIcon(): string
    {
        return match ($this->type) {
            'cleaning'   => '🧹',
            'assistance' => '🔔',
            'checkout'   => '💳',
            'other'      => '💬',
            default      => '📣',
        };
    }
}
