<?php

namespace App\Listeners\Crm;

use App\Enums\Crm\LeadStatus;
use App\Events\Crm\LeadStatusChanged;
use App\Services\Crm\CommissionEngine;
use App\Services\Crm\GradingService;
use App\Services\Crm\InstallationService;

class HandleLeadStatusChange
{
    public function __construct(
        private CommissionEngine $commissionEngine,
        private GradingService $gradingService,
        private InstallationService $installationService,
    ) {}

    public function handle(LeadStatusChanged $event): void
    {
        $lead = $event->lead;

        if ($event->newStatus === LeadStatus::SIGNATURE) {
            $this->installationService->createFromLead($lead);
        }

        if ($event->newStatus === LeadStatus::ACTIF) {
            // Récurrente démarre le 2ème mois après conversion
            if (!$lead->recurring_starts_month) {
                $lead->update([
                    'recurring_starts_month' => now()->addMonthNoOverflow()->format('Y-m'),
                ]);
            }

            $this->commissionEngine->creditForSignature($lead);
            $this->commissionEngine->creditLeaderOverride($lead);

            if ($lead->assigned_to) {
                $this->gradingService->recalculateForUser($lead->assignedUser);
            }
        }
    }
}
