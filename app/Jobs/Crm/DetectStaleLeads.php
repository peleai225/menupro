<?php

namespace App\Jobs\Crm;

use App\Models\Crm\Lead;
use App\Models\Crm\Team;
use App\Models\User;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Notification;

class DetectStaleLeads implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle(): void
    {
        $warningHours = config('crm.pipeline.stale_warning_hours', 48);
        $alertDays = config('crm.pipeline.stale_alert_days', 7);
        $reassignDays = config('crm.pipeline.stale_reassign_days', 14);

        // Warning: 48h sans activité → notifier l'agent
        Lead::stale($warningHours)
            ->whereHas('activities', function ($q) use ($alertDays) {
                $q->where('created_at', '>=', now()->subDays($alertDays));
            })
            ->with('assignedUser')
            ->each(function (Lead $lead) {
                if ($lead->assignedUser) {
                    // notification handled via CRM alerts
                }
            });

        // Alert: 7j sans activité → notifier le team leader
        Lead::stale($alertDays * 24)
            ->with(['team.leader'])
            ->each(function (Lead $lead) {
                // alert team leader
            });
    }
}
