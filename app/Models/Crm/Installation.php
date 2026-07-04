<?php

namespace App\Models\Crm;

use App\Enums\Crm\InstallationStatus;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Installation extends Model
{
    protected $table = 'crm_installations';

    protected $fillable = [
        'lead_id',
        'restaurant_id',
        'technician_id',
        'scheduled_at',
        'started_at',
        'completed_at',
        'status',
        'notes',
        'equipment',
        'photos',
        'rating',
    ];

    protected function casts(): array
    {
        return [
            'status' => InstallationStatus::class,
            'scheduled_at' => 'datetime',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
            'equipment' => 'array',
            'photos' => 'array',
            'rating' => 'integer',
        ];
    }

    public function lead(): BelongsTo
    {
        return $this->belongsTo(Lead::class, 'lead_id');
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function technician(): BelongsTo
    {
        return $this->belongsTo(User::class, 'technician_id');
    }

    public function commissions(): MorphMany
    {
        return $this->morphMany(Commission::class, 'source');
    }

    public function scopeForTechnician($query, int $userId)
    {
        return $query->where('technician_id', $userId);
    }

    public function scopeUpcoming($query)
    {
        return $query->where('status', InstallationStatus::PLANIFIEE)
            ->where('scheduled_at', '>=', now())
            ->orderBy('scheduled_at');
    }

    public function scopeToday($query)
    {
        return $query->whereDate('scheduled_at', today());
    }

    public function start(): void
    {
        $this->update([
            'status' => InstallationStatus::EN_COURS,
            'started_at' => now(),
        ]);
    }

    public function complete(?int $rating = null): void
    {
        $this->update([
            'status' => InstallationStatus::TERMINEE,
            'completed_at' => now(),
            'rating' => $rating,
        ]);
    }
}
