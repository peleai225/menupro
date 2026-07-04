<?php

namespace App\Jobs\Crm;

use App\Enums\Crm\LeadStatus;
use App\Enums\UserRole;
use App\Models\Crm\Commission;
use App\Models\Crm\Lead;
use App\Models\Crm\PerformanceSnapshot;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class CalculateMonthlyPerformance implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $start = now()->startOfMonth();
        $end = now()->endOfMonth();

        $users = User::whereIn('role', [
            UserRole::COMMERCIAL->value,
            UserRole::TECHNICIAN->value,
            UserRole::TEAM_LEADER->value,
        ])->get();

        foreach ($users as $user) {
            $leadsCreated = Lead::where('assigned_to', $user->id)
                ->whereBetween('created_at', [$start, $end])
                ->count();

            $leadsConverted = Lead::where('assigned_to', $user->id)
                ->where('status', LeadStatus::ACTIF)
                ->whereBetween('converted_at', [$start, $end])
                ->count();

            $commissionsEarned = Commission::where('user_id', $user->id)
                ->whereBetween('created_at', [$start, $end])
                ->sum('amount_cents');

            $conversionRate = $leadsCreated > 0
                ? round(($leadsConverted / $leadsCreated) * 100, 2)
                : 0;

            PerformanceSnapshot::updateOrCreate(
                [
                    'user_id' => $user->id,
                    'period_type' => 'monthly',
                    'period_start' => $start->toDateString(),
                ],
                [
                    'period_end' => $end->toDateString(),
                    'leads_created' => $leadsCreated,
                    'leads_converted' => $leadsConverted,
                    'commissions_earned_cents' => $commissionsEarned,
                    'conversion_rate' => $conversionRate,
                ]
            );
        }
    }
}
