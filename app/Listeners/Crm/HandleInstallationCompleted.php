<?php

namespace App\Listeners\Crm;

use App\Enums\Crm\LeadStatus;
use App\Events\Crm\InstallationCompleted;
use App\Services\Crm\CommissionEngine;
use App\Services\Crm\LeadPipelineService;

class HandleInstallationCompleted
{
    public function __construct(
        private CommissionEngine $commissionEngine,
        private LeadPipelineService $pipelineService,
    ) {}

    public function handle(InstallationCompleted $event): void
    {
        $installation = $event->installation;

        $this->commissionEngine->creditForInstallation($installation);

        $lead = $installation->lead;
        if ($lead && $lead->status !== LeadStatus::ACTIF) {
            $this->pipelineService->changeStatus($lead, LeadStatus::ACTIF);
        }
    }
}
