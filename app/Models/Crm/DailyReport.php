<?php

namespace App\Models\Crm;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DailyReport extends Model
{
    protected $table = 'crm_daily_reports';

    protected $fillable = [
        'user_id',
        'report_date',
        'visits_count',
        'new_leads_count',
        'demos_count',
        'conversions_count',
        'zone_covered',
        'obstacles',
        'notes',
        'photos',
        'submitted_at',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'report_date' => 'date',
            'photos' => 'array',
            'submitted_at' => 'datetime',
            'reviewed_at' => 'datetime',
            'visits_count' => 'integer',
            'new_leads_count' => 'integer',
            'demos_count' => 'integer',
            'conversions_count' => 'integer',
        ];
    }

    // Relations

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function reviewedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }

    // Scopes

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopePending($query)
    {
        return $query->whereNull('reviewed_by');
    }

    public function scopeToday($query)
    {
        return $query->where('report_date', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('report_date', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereBetween('report_date', [now()->startOfMonth(), now()->endOfMonth()]);
    }

    // Accessors

    public function getIsSubmittedAttribute(): bool
    {
        return $this->submitted_at !== null;
    }

    public function getIsReviewedAttribute(): bool
    {
        return $this->reviewed_by !== null;
    }
}
