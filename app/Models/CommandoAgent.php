<?php

namespace App\Models;

use App\Enums\AgentGrade;
use App\Enums\AgentVerificationStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class CommandoAgent extends Model
{
    use HasFactory;

    protected $table = 'commando_agents';

    protected $fillable = [
        'uuid',
        'badge_id',
        'first_name',
        'last_name',
        'whatsapp',
        'city',
        'statut_metier',
        'status_verification',
        'id_document_path',
        'photo_path',
        'user_id',
        'banned_at',
        'rejection_reason',
        'approved_at',
        'balance_cents',
    ];

    protected function casts(): array
    {
        return [
            'status_verification' => AgentVerificationStatus::class,
            'banned_at' => 'datetime',
            'approved_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (CommandoAgent $agent) {
            if (empty($agent->uuid)) {
                $agent->uuid = (string) Str::uuid();
            }
        });

        static::created(function (CommandoAgent $agent) {
            if (empty($agent->badge_id)) {
                $agent->updateQuietly([
                    'badge_id' => 'MP-' . $agent->id . '-' . strtoupper(Str::random(4)),
                ]);
            }
        });
    }

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function verifyScans(): HasMany
    {
        return $this->hasMany(CommandoVerifyScan::class);
    }

    public function referredRestaurants(): HasMany
    {
        return $this->hasMany(Restaurant::class, 'referred_by_agent_id');
    }

    public function commissionTransactions(): HasMany
    {
        return $this->hasMany(CommandoCommissionTransaction::class);
    }

    public function deployments(): HasMany
    {
        return $this->hasMany(CommandoDeployment::class);
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    public function scopePendingReview($query)
    {
        return $query->where('status_verification', AgentVerificationStatus::PENDING_REVIEW);
    }

    public function scopeValide($query)
    {
        return $query->where('status_verification', AgentVerificationStatus::VALIDE);
    }

    public function scopeBanni($query)
    {
        return $query->where('status_verification', AgentVerificationStatus::BANNI)
            ->orWhereNotNull('banned_at');
    }

    public function scopeShadow($query)
    {
        return $query->where('status_verification', AgentVerificationStatus::SHADOW);
    }

    // =========================================================================
    // ACCESSORS
    // =========================================================================

    public function getFullNameAttribute(): string
    {
        return trim("{$this->first_name} {$this->last_name}");
    }

    public function getVerifyUrlAttribute(): string
    {
        return url('/verify/' . $this->uuid);
    }

    public function getParrainageUrlAttribute(): string
    {
        return url('/inscription?ref=' . $this->uuid);
    }

    public function getPhotoUrlAttribute(): ?string
    {
        if ($this->photo_path && Storage::disk('public')->exists($this->photo_path)) {
            return Storage::disk('public')->url($this->photo_path);
        }
        $name = urlencode($this->full_name);
        return "https://ui-avatars.com/api/?name={$name}&background=f97316&color=fff&size=200";
    }

    public function getIdDocumentUrlAttribute(): ?string
    {
        if (!$this->id_document_path) {
            return null;
        }
        return Storage::disk('public')->exists($this->id_document_path)
            ? Storage::disk('public')->url($this->id_document_path)
            : null;
    }

    public function getBalanceAttribute(): float
    {
        return ($this->balance_cents ?? 0) / 100;
    }

    /** Grade selon le nombre de restaurants parrainés (actifs). */
    public function getGradeAttribute(): AgentGrade
    {
        $count = $this->referredRestaurants()->count();
        return AgentGrade::fromReferredCount($count);
    }

    /** Badge ID formaté pour affichage (ex: MP-225-XXXX). */
    public function getBadgeIdDisplayAttribute(): string
    {
        return $this->badge_id ?? 'MP-' . $this->id . '-----';
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    public function isValide(): bool
    {
        return $this->status_verification === AgentVerificationStatus::VALIDE && !$this->banned_at;
    }

    public function isBanni(): bool
    {
        return $this->status_verification === AgentVerificationStatus::BANNI || $this->banned_at !== null;
    }

    public function canAccessParrainage(): bool
    {
        return $this->status_verification->canAccessParrainage() && !$this->isBanni();
    }

    public function canGenerateCard(): bool
    {
        return $this->status_verification->canGenerateCard() && !$this->isBanni();
    }
}
