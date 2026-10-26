<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Candidature au programme « Représentant de ville ».
 */
class PartnerApplication extends Model
{
    public const STATUS_NEW = 'nouvelle';
    public const STATUS_INTERVIEW = 'entretien';
    public const STATUS_ACCEPTED = 'acceptee';
    public const STATUS_REFUSED = 'refusee';

    public const STATUS_LABELS = [
        self::STATUS_NEW => 'Nouvelle',
        self::STATUS_INTERVIEW => 'Entretien',
        self::STATUS_ACCEPTED => 'Acceptée',
        self::STATUS_REFUSED => 'Refusée',
    ];

    public const LEGAL_STATUS_LABELS = [
        'particulier' => 'Particulier',
        'auto_entrepreneur' => 'Auto-entrepreneur / entreprenant',
        'entreprise' => 'Entreprise (SARL, SAS…)',
        'association' => 'Association / GIE',
    ];

    public const AVAILABILITY_LABELS = [
        'temps_plein' => 'Temps plein',
        'temps_partiel' => 'Temps partiel',
        'week_end' => 'Soirs et week-ends',
    ];

    protected $fillable = [
        'full_name', 'phone', 'email', 'city', 'legal_status', 'experience',
        'availability', 'motivation', 'attachment_path', 'attachment_name',
        'status', 'internal_notes', 'reviewed_by', 'reviewed_at', 'user_id',
    ];

    protected $casts = [
        'reviewed_at' => 'datetime',
    ];

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    /** Compte représentant créé depuis cette candidature. */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function getStatusLabelAttribute(): string
    {
        return self::STATUS_LABELS[$this->status] ?? $this->status;
    }

    public function getLegalStatusLabelAttribute(): string
    {
        return self::LEGAL_STATUS_LABELS[$this->legal_status] ?? $this->legal_status;
    }

    public function getAvailabilityLabelAttribute(): string
    {
        return self::AVAILABILITY_LABELS[$this->availability] ?? $this->availability;
    }

    public function isAccepted(): bool
    {
        return $this->status === self::STATUS_ACCEPTED;
    }
}
