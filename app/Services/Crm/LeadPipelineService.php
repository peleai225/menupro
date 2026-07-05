<?php

namespace App\Services\Crm;

use App\Enums\Crm\ActivityType;
use App\Enums\Crm\LeadSource;
use App\Enums\Crm\LeadStatus;
use App\Events\Crm\LeadCreated;
use App\Events\Crm\LeadStatusChanged;
use App\Models\Crm\Lead;
use App\Models\Crm\Team;
use App\Models\User;

class LeadPipelineService
{
    public function createLead(array $data, ?User $creator = null): Lead
    {
        $lead = Lead::create([
            'restaurant_name' => $data['restaurant_name'],
            'manager_name' => $data['manager_name'] ?? null,
            'phone' => $data['phone'] ?? null,
            'email' => $data['email'] ?? null,
            'address' => $data['address'] ?? null,
            'city' => $data['city'] ?? null,
            'latitude' => $data['latitude'] ?? null,
            'longitude' => $data['longitude'] ?? null,
            'source' => $data['source'] ?? LeadSource::TERRAIN,
            'assigned_to' => $data['assigned_to'] ?? $creator?->id,
            'team_id' => $data['team_id'] ?? null,
            'status' => LeadStatus::NOUVEAU,
        ]);

        $lead->activities()->create([
            'user_id' => $creator?->id ?? auth()->id(),
            'type' => ActivityType::NOTE,
            'description' => 'Lead créé',
        ]);

        event(new LeadCreated($lead));

        return $lead;
    }

    public function changeStatus(Lead $lead, LeadStatus $newStatus, ?User $user = null, ?string $reason = null): bool
    {
        $oldStatus = $lead->status;

        if (!$lead->transitionTo($newStatus, $user?->id, $reason)) {
            return false;
        }

        event(new LeadStatusChanged($lead, $oldStatus, $newStatus));

        return true;
    }

    public function addActivity(Lead $lead, ActivityType $type, string $description, ?User $user = null, ?array $metadata = null): void
    {
        $lead->activities()->create([
            'user_id' => $user?->id ?? auth()->id(),
            'type' => $type,
            'description' => $description,
            'metadata' => $metadata,
        ]);

        $lead->touch();
    }

    public function assignTo(Lead $lead, User $user, ?User $assignedBy = null): void
    {
        $oldUser = $lead->assignedUser;

        $lead->update(['assigned_to' => $user->id]);

        $lead->activities()->create([
            'user_id' => $assignedBy?->id ?? auth()->id(),
            'type' => ActivityType::ASSIGNMENT,
            'description' => "Assigné à {$user->name}",
            'metadata' => [
                'from_user_id' => $oldUser?->id,
                'to_user_id' => $user->id,
            ],
        ]);
    }

    public function autoAssign(Lead $lead): ?User
    {
        $team = $lead->team;
        if (!$team) return null;

        $commercial = $team->commercials()
            ->withCount(['crmLeadsAssigned as active_leads_count' => function ($q) {
                $q->active();
            }])
            ->orderBy('active_leads_count')
            ->first();

        if ($commercial) {
            $this->assignTo($lead, $commercial);
            return $commercial;
        }

        return null;
    }
}
