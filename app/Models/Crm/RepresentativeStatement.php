<?php

namespace App\Models\Crm;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Relevé mensuel de rémunération d'un représentant de ville (montants en FCFA).
 * Une ligne par représentant et par mois (clé unique) : le calcul est idempotent.
 */
class RepresentativeStatement extends Model
{
    protected $table = 'crm_representative_statements';

    protected $fillable = [
        'representative_profile_id', 'month', 'restaurants_attributed', 'restaurants_active',
        'restaurants_paying', 'subscription_collected', 'delivery_commission',
        'subscription_rate', 'delivery_rate', 'subscription_share', 'delivery_share',
        'total_share', 'credited_cents', 'details', 'computed_at',
    ];

    protected $casts = [
        'restaurants_attributed' => 'integer',
        'restaurants_active' => 'integer',
        'restaurants_paying' => 'integer',
        'subscription_collected' => 'integer',
        'delivery_commission' => 'integer',
        'subscription_rate' => 'float',
        'delivery_rate' => 'float',
        'subscription_share' => 'integer',
        'delivery_share' => 'integer',
        'total_share' => 'integer',
        'credited_cents' => 'integer',
        'details' => 'array',
        'computed_at' => 'datetime',
    ];

    public function profile(): BelongsTo
    {
        return $this->belongsTo(RepresentativeProfile::class, 'representative_profile_id');
    }
}
