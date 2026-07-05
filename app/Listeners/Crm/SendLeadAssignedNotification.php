<?php

namespace App\Listeners\Crm;

use App\Events\Crm\LeadCreated;
use App\Notifications\Crm\LeadAssignedNotification;

class SendLeadAssignedNotification
{
    public function handle(LeadCreated $event): void
    {
        $lead = $event->lead;

        if ($lead->assigned_to && $lead->assignedUser) {
            $lead->assignedUser->notify(new LeadAssignedNotification($lead));
        }
    }
}
