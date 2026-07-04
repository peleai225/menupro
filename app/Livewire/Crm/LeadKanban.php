<?php

namespace App\Livewire\Crm;

use App\Enums\Crm\LeadStatus;
use App\Models\Crm\Lead;
use App\Services\Crm\LeadPipelineService;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class LeadKanban extends Component
{
    public ?string $filterCity = null;
    public ?string $filterSource = null;
    public ?int $filterTeam = null;

    public function moveToStatus(int $leadId, string $status): void
    {
        $lead = Lead::findOrFail($leadId);
        $newStatus = LeadStatus::from($status);

        app(LeadPipelineService::class)->changeStatus($lead, $newStatus, auth()->user());
    }

    #[On('echo:crm.leads,lead.created')]
    #[On('echo:crm.leads,lead.status_changed')]
    public function refreshLeads(): void
    {
        // Livewire re-renders automatically
    }

    #[Computed]
    public function columns(): array
    {
        $statuses = LeadStatus::pipelineStatuses();
        $query = Lead::query();

        if (auth()->user()->role->value === 'commercial') {
            $query->forUser(auth()->id());
        } elseif (auth()->user()->role->value === 'team_leader') {
            $teamIds = auth()->user()->ledTeams()->pluck('id');
            $query->whereIn('team_id', $teamIds);
        }

        if ($this->filterCity) {
            $query->where('city', $this->filterCity);
        }

        if ($this->filterSource) {
            $query->where('source', $this->filterSource);
        }

        if ($this->filterTeam) {
            $query->forTeam($this->filterTeam);
        }

        $leads = $query->with(['assignedUser', 'activities' => fn ($q) => $q->latest()->limit(1)])
            ->get()
            ->groupBy(fn ($lead) => $lead->status->value);

        $columns = [];
        foreach ($statuses as $status) {
            $columns[] = [
                'status' => $status,
                'leads' => $leads->get($status->value, collect()),
            ];
        }

        return $columns;
    }

    public function render()
    {
        return view('livewire.crm.lead-kanban');
    }
}
