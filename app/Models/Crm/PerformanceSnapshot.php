<?php

namespace App\Models\Crm;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PerformanceSnapshot extends Model
{
    protected $table = 'crm_performance_snapshots';

    protected $fillable = [
        'user_id',
        'period_type',
        'period_start',
        'period_end',
        'leads_created',
        'leads_converted',
        'revenue_generated_cents',
        'commissions_earned_cents',
        'installations_completed',
        'conversion_rate',
        'avg_cycle_days',
    ];

    protected function casts(): array
    {
        return [
            'period_start' => 'date',
            'period_end' => 'date',
            'conversion_rate' => 'decimal:2',
            'avg_cycle_days' => 'decimal:1',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function scopeMonthly($query)
    {
        return $query->where('period_type', 'monthly');
    }

    public function scopeWeekly($query)
    {
        return $query->where('period_type', 'weekly');
    }

    public function scopeForPeriod($query, string $type, $start)
    {
        return $query->where('period_type', $type)
            ->where('period_start', $start);
    }
}
