<?php

namespace App\Livewire\Crm;

use App\Enums\Crm\ActivityType;
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
    public ?string $filterPlan = null;

    public ?int $viewingLeadId = null;
    public string $newActivityType = 'note';
    public ?string $newActivityContent = null;

    public function viewLead(int $leadId): void
    {
        $this->viewingLeadId = $leadId;
        $this->newActivityContent = '';
        unset($this->viewingLead);
    }

    public function closeLeadDetail(): void
    {
        $this->viewingLeadId = null;
        unset($this->viewingLead);
    }

    #[Computed]
    public function viewingLead(): ?Lead
    {
        if (!$this->viewingLeadId) return null;

        return Lead::with([
            'activities' => fn ($q) => $q->latest()->limit(20),
            'assignedUser',
            'team',
        ])->find($this->viewingLeadId);
    }

    public function addActivity(): void
    {
        $this->validate([
            'newActivityContent' => 'required|string|min:3|max:1000',
            'newActivityType'    => 'required|in:note,call,visit,demo,email,whatsapp,assignment',
        ]);

        if (!$this->viewingLeadId) return;

        $lead = Lead::find($this->viewingLeadId);
        if (!$lead) return;

        $user = auth()->user();
        $canEdit = $user->isSuperAdmin()
            || $lead->assigned_to === $user->id
            || ($lead->team && $lead->team->leader_id === $user->id);

        if (!$canEdit) return;

        $lead->activities()->create([
            'user_id'     => $user->id,
            'type'        => $this->newActivityType,
            'description' => $this->newActivityContent,
        ]);

        $this->newActivityContent = '';
        unset($this->viewingLead);
        $this->dispatch('activity-added');
    }

    public function moveToStatus(int $leadId, string $status): void
    {
        $lead = Lead::findOrFail($leadId);
        $user = auth()->user();

        $canMove = match ($user->role->value) {
            'super_admin' => true,
            'team_leader' => $user->ledTeams()->where('id', $lead->team_id)->exists(),
            'commercial' => $lead->assigned_to === $user->id,
            default => false,
        };

        if (!$canMove) {
            abort(403, 'Vous ne pouvez déplacer que vos propres leads.');
        }

        $newStatus = LeadStatus::from($status);
        app(LeadPipelineService::class)->changeStatus($lead, $newStatus, $user);
    }

    #[On('echo:crm.leads,lead.created')]
    #[On('echo:crm.leads,lead.status_changed')]
    #[On('lead-created')]
    #[On('lead-updated')]
    public function refreshLeads(): void
    {
        unset($this->columns);
        unset($this->viewingLead);
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

        if ($this->filterPlan) {
            $query->where('subscription_plan', $this->filterPlan);
        }

        $leads = $query->with(['assignedUser', 'activities' => fn ($q) => $q->latest()])
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
