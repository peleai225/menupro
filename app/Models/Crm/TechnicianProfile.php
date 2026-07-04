<?php

namespace App\Models\Crm;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TechnicianProfile extends Model
{
    protected $table = 'crm_technician_profiles';

    protected $fillable = [
        'user_id',
        'speciality',
        'zone_geographique',
        'disponible',
        'team_id',
        'certifications',
    ];

    protected function casts(): array
    {
        return [
            'disponible' => 'boolean',
            'certifications' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function team(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team_id');
    }

    public function scopeDisponible($query)
    {
        return $query->where('disponible', true);
    }

    public function scopeInZone($query, string $zone)
    {
        return $query->where('zone_geographique', $zone);
    }
}
