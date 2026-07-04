<?php

namespace App\Livewire\Crm;

use App\Enums\Crm\LeadStatus;
use App\Models\Crm\Commission;
use App\Models\Crm\Installation;
use App\Models\Crm\Lead;
use App\Models\Crm\Team;
use App\Models\Crm\Withdrawal;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class AdminDashboard extends Component
{
    #[On('echo:crm.leads,lead.created')]
    #[On('echo:crm.leads,lead.status_changed')]
    public function refresh(): void {}

    #[Computed]
    public function kpis(): array
    {
        $thisMonth = now()->startOfMonth();

        return [
            'total_leads' => Lead::where('created_at', '>=', $thisMonth)->count(),
            'converted' => Lead::where('status', LeadStatus::ACTIF)->where('converted_at', '>=', $thisMonth)->count(),
            'in_pipeline' => Lead::active()->count(),
            'revenue_cents' => Commission::where('created_at', '>=', $thisMonth)->sum('amount_cents'),
            'active_agents' => User::where('role', 'commercial')->where('is_active', true)->count(),
            'pending_withdrawals' => Withdrawal::pending()->count(),
            'installations_pending' => Installation::where('status', 'planifiee')->count(),
        ];
    }

    #[Computed]
    public function funnel(): array
    {
        $counts = [];
        foreach (LeadStatus::pipelineStatuses() as $status) {
            $counts[] = [
                'status' => $status,
                'count' => Lead::where('status', $status)->count(),
            ];
        }
        return $counts;
    }

    #[Computed]
    public function topPerformers()
    {
        return User::where('role', 'commercial')
            ->withCount(['crmLeadsAssigned as conversions_count' => fn ($q) => $q->where('status', 'actif')->where('converted_at', '>=', now()->startOfMonth())])
            ->orderByDesc('conversions_count')
            ->limit(10)
            ->get();
    }

    #[Computed]
    public function teams()
    {
        return Team::active()->withCount(['leads as active_leads_count' => fn ($q) => $q->active()])->get();
    }

    public function render()
    {
        return view('livewire.crm.admin-dashboard');
    }
}
