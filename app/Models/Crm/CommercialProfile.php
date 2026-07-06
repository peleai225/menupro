<?php

namespace App\Models\Crm;

use App\Enums\Crm\Grade;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;

class CommercialProfile extends Model
{
    protected $table = 'crm_commercial_profiles';

    protected $fillable = [
        'user_id',
        'uuid',
        'badge_id',
        'city',
        'specialty',
        'statut_metier',
        'id_document_path',
        'verification_status',
        'approved_at',
        'banned_at',
        'rejection_reason',
        'team_id',
        'monthly_target',
    ];

    protected function casts(): array
    {
        return [
            'approved_at' => 'datetime',
            'banned_at' => 'datetime',
            'monthly_target' => 'integer',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (self $profile) {
            if (!$profile->uuid) {
                $profile->uuid = Str::uuid()->toString();
            }
        });

        static::created(function (self $profile) {
            if (!$profile->badge_id) {
                $profile->update([
                    'badge_id' => 'MP-' . $profile->id . '-' . strtoupper(Str::random(4)),
                ]);
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    public function isValide(): bool
    {
        return $this->verification_status === 'valide';
    }

    public function isBanned(): bool
    {
        return $this->verification_status === 'banni';
    }

    public function isPendingReview(): bool
    {
        return $this->verification_status === 'pending_review';
    }

    public function getVerifyUrlAttribute(): string
    {
        return route('crm.verify', $this->uuid);
    }

    public function getReferralUrlAttribute(): string
    {
        return url('/inscription?ref=' . $this->uuid);
    }
}
