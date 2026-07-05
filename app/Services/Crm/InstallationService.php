<?php

namespace App\Services\Crm;

use App\Enums\Crm\InstallationStatus;
use App\Events\Crm\InstallationCompleted;
use App\Models\Crm\Installation;
use App\Models\Crm\Lead;
use App\Models\Crm\TechnicianProfile;
use App\Models\User;

class InstallationService
{
    public function createFromLead(Lead $lead, ?User $technician = null, ?string $scheduledAt = null): Installation
    {
        if (!$technician) {
            $technician = $this->findAvailableTechnician($lead->city);
        }

        return Installation::create([
            'lead_id' => $lead->id,
            'restaurant_id' => $lead->restaurant_id,
            'technician_id' => $technician?->id,
            'scheduled_at' => $scheduledAt ? \Carbon\Carbon::parse($scheduledAt) : now()->addDay(),
            'status' => InstallationStatus::PLANIFIEE,
        ]);
    }

    public function start(Installation $installation): void
    {
        $installation->start();
    }

    public function complete(Installation $installation, ?int $rating = null, ?array $photos = null): void
    {
        if ($photos) {
            $installation->update(['photos' => $photos]);
        }

        $installation->complete($rating);

        event(new InstallationCompleted($installation));
    }

    public function assignTechnician(Installation $installation, User $technician): void
    {
        $installation->update(['technician_id' => $technician->id]);
    }

    private function findAvailableTechnician(?string $city): ?User
    {
        $profile = TechnicianProfile::disponible()
            ->when($city, fn ($q) => $q->inZone($city))
            ->withCount(['user as active_count' => fn ($q) =>
                $q->whereHas('crmInstallations', fn ($i) =>
                    $i->whereIn('status', [
                        InstallationStatus::PLANIFIEE->value,
                        InstallationStatus::EN_COURS->value,
                    ])
                )
            ])
            ->orderBy('active_count')
            ->first();

        return $profile?->user;
    }
}
