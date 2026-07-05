<?php

namespace App\Models\Crm;

use App\Enums\Crm\ActivityType;
use App\Enums\Crm\LeadSource;
use App\Enums\Crm\LeadStatus;
use App\Enums\Crm\SubscriptionPlan;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Lead extends Model
{
    protected $table = 'crm_leads';

    protected $fillable = [
        'uuid',
        'restaurant_name',
        'manager_name',
        'phone',
        'email',
        'address',
        'city',
        'latitude',
        'longitude',
        'status',
        'lost_reason',
        'source',
        'assigned_to',
        'team_id',
        'restaurant_id',
        'score',
        'next_action_at',
        'converted_at',
        'subscription_plan',
        'recurring_starts_month',
    ];

    protected function casts(): array
    {
        return [
            'status' => LeadStatus::class,
            'source' => LeadSource::class,
            'latitude' => 'decimal:7',
            'longitude' => 'decimal:7',
            'score' => 'integer',
            'next_action_at' => 'datetime',
            'converted_at' => 'datetime',
            'subscription_plan' => SubscriptionPlan::class,
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $lead) {
            if (!$lead->uuid) {
                $lead->uuid = Str::uuid()->toString();
            }
        });
    }

    public function assignedUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'assigned_to');
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function activities(): HasMany
    {
        return $this->hasMany(LeadActivity::class, 'lead_id')->latest();
    }

    public function installation(): HasOne
    {
        return $this->hasOne(Installation::class, 'lead_id');
    }

    // Scopes

    public function scopeStatus($query, LeadStatus $status)
    {
        return $query->where('status', $status);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('assigned_to', $userId);
    }

    public function scopeForTeam($query, int $teamId)
    {
        return $query->where('team_id', $teamId);
    }

    public function scopeActive($query)
    {
        return $query->whereNotIn('status', [
            LeadStatus::PERDU->value,
            LeadStatus::ACTIF->value,
        ]);
    }

    public function scopeStale($query, int $hours = 48)
    {
        return $query->active()
            ->where(function ($q) use ($hours) {
                $q->whereDoesntHave('activities', function ($aq) use ($hours) {
                    $aq->where('created_at', '>=', now()->subHours($hours));
                });
            });
    }

    // Helpers

    public function transitionTo(LeadStatus $newStatus, ?int $userId = null, ?string $reason = null): bool
    {
        if (!$this->status->canTransitionTo($newStatus)) {
            return false;
        }

        $oldStatus = $this->status;
        $this->status = $newStatus;

        if ($newStatus === LeadStatus::PERDU) {
            $this->lost_reason = $reason;
        }

        if ($newStatus === LeadStatus::ACTIF) {
            $this->converted_at = now();
        }

        $this->save();

        $this->activities()->create([
            'user_id' => $userId ?? auth()->id(),
            'type' => ActivityType::STATUS_CHANGE,
            'description' => "Statut changé de {$oldStatus->label()} à {$newStatus->label()}",
            'metadata' => [
                'from' => $oldStatus->value,
                'to' => $newStatus->value,
                'reason' => $reason,
            ],
        ]);

        return true;
    }

    public function getDaysInPipelineAttribute(): int
    {
        return (int) $this->created_at->diffInDays(now());
    }

    public function getLastActivityAttribute(): ?LeadActivity
    {
        return $this->activities()->first();
    }
}
