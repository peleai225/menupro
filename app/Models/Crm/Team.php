<?php

namespace App\Models\Crm;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Team extends Model
{
    protected $table = 'crm_teams';

    protected $fillable = [
        'name',
        'leader_id',
        'zone',
        'monthly_target',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'monthly_target' => 'integer',
        ];
    }

    public function leader(): BelongsTo
    {
        return $this->belongsTo(User::class, 'leader_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'crm_team_members')
            ->withPivot('role_in_team', 'joined_at');
    }

    public function commercials(): BelongsToMany
    {
        return $this->members()->wherePivot('role_in_team', 'commercial');
    }

    public function technicians(): BelongsToMany
    {
        return $this->members()->wherePivot('role_in_team', 'technician');
    }

    public function leads(): HasMany
    {
        return $this->hasMany(Lead::class, 'team_id');
    }

    public function commercialProfiles(): HasMany
    {
        return $this->hasMany(CommercialProfile::class, 'team_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function getConversionsThisMonthAttribute(): int
    {
        return $this->leads()
            ->where('status', 'actif')
            ->where('converted_at', '>=', now()->startOfMonth())
            ->count();
    }

    public function getProgressPercentAttribute(): int
    {
        if ($this->monthly_target === 0) return 0;
        return min(100, (int) round(($this->conversions_this_month / $this->monthly_target) * 100));
    }
}
