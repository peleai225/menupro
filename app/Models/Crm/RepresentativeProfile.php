<?php

namespace App\Models\Crm;

use App\Models\PartnerApplication;
use App\Models\Restaurant;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Profil « représentant de ville » : un utilisateur CRM (rôle representative)
 * rattaché à UNE ville, rémunéré en pourcentage via le wallet CRM.
 *
 * Attribution d'un restaurant :
 *  - inscrit via son lien de parrainage (/inscription?ref={uuid}) → restaurants.referred_by_user_id ;
 *  - OU situé dans sa ville lorsque l'exclusivité est active.
 */
class RepresentativeProfile extends Model
{
    protected $table = 'crm_representative_profiles';

    protected $fillable = [
        'user_id', 'partner_application_id', 'uuid', 'city',
        'exclusivity_active', 'exclusivity_granted_at',
        'subscription_rate', 'delivery_rate', 'trial_ends_at', 'quarterly_target',
    ];

    protected $casts = [
        'exclusivity_active' => 'boolean',
        'exclusivity_granted_at' => 'datetime',
        'subscription_rate' => 'float',
        'delivery_rate' => 'float',
        'trial_ends_at' => 'date',
        'quarterly_target' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $profile) {
            if (!$profile->uuid) {
                $profile->uuid = (string) Str::uuid();
            }
        });
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function application(): BelongsTo
    {
        return $this->belongsTo(PartnerApplication::class, 'partner_application_id');
    }

    public function statements(): HasMany
    {
        return $this->hasMany(RepresentativeStatement::class, 'representative_profile_id');
    }

    public function getReferralUrlAttribute(): string
    {
        return url('/inscription?ref=' . $this->uuid);
    }

    public function isInTrial(): bool
    {
        return $this->trial_ends_at !== null && $this->trial_ends_at->isFuture();
    }

    /** Normalisation de ville pour la comparaison (casse et espaces). */
    public static function normalizeCity(?string $city): string
    {
        return mb_strtolower(trim((string) $city));
    }

    /**
     * Restaurants attribués à ce représentant (lien de parrainage OU ville exclusive).
     */
    public function attributedRestaurantsQuery(): Builder
    {
        $city = self::normalizeCity($this->city);

        return Restaurant::query()
            ->where(function (Builder $q) use ($city) {
                $q->where('referred_by_user_id', $this->user_id);
                if ($this->exclusivity_active && $city !== '') {
                    $q->orWhereRaw('LOWER(TRIM(city)) = ?', [$city]);
                }
            });
    }

    /** Le restaurant a-t-il été attribué via le lien de parrainage ? */
    public function isReferred(Restaurant $restaurant): bool
    {
        return (int) $restaurant->referred_by_user_id === (int) $this->user_id;
    }
}
