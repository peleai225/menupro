<?php

namespace App\Livewire\Crm;

use App\Enums\Crm\CommissionStatus;
use App\Enums\Crm\LeadStatus;
use App\Models\Crm\Commission;
use App\Models\Crm\Installation;
use App\Models\Crm\Lead;
use App\Models\Crm\Team;
use App\Models\Crm\Withdrawal;
use App\Models\User;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class AdminDashboard extends Component
{
    public string $period = 'month';

    #[On('echo:crm.leads,lead.created')]
    #[On('echo:crm.leads,lead.status_changed')]
    public function refresh(): void
    {
        unset($this->kpis, $this->funnel, $this->topPerformers, $this->teams, $this->chartData);
    }

    public function setPeriod(string $period): void
    {
        $this->period = $period;
        unset($this->kpis, $this->funnel, $this->topPerformers, $this->chartData);
    }

    private function periodStart(): Carbon
    {
        return match ($this->period) {
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'quarter' => now()->startOfQuarter(),
            'year' => now()->startOfYear(),
            default => now()->startOfMonth(),
        };
    }

    #[Computed]
    public function kpis(): array
    {
        $start = $this->periodStart();

        $totalLeads = Lead::where('created_at', '>=', $start)->count();
        $converted = Lead::where('status', LeadStatus::ACTIF)->where('converted_at', '>=', $start)->count();
        $conversionRate = $totalLeads > 0 ? round(($converted / $totalLeads) * 100, 1) : 0;

        return [
            'total_leads' => $totalLeads,
            'converted' => $converted,
            'conversion_rate' => $conversionRate,
            'in_pipeline' => Lead::active()->count(),
            'revenue_cents' => Commission::where('status', CommissionStatus::VALIDATED)->where('created_at', '>=', $start)->sum('amount_cents'),
            'active_agents' => User::where('role', 'commercial')->where('is_active', true)->count(),
            'pending_withdrawals' => Withdrawal::pending()->count(),
            'installations_pending' => Installation::where('status', 'planifiee')->count(),
            'installations_done' => Installation::where('status', 'terminee')->where('completed_at', '>=', $start)->count(),
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
    public function chartData(): array
    {
        $start = $this->periodStart();
        $days = $this->period === 'week' ? 7 : ($this->period === 'month' ? 30 : ($this->period === 'quarter' ? 90 : 365));
        $interval = $days <= 30 ? '1 day' : ($days <= 90 ? '1 week' : '1 month');

        $period = CarbonPeriod::create($start, $interval, now());
        $labels = [];
        $leadsData = [];
        $conversionsData = [];
        $revenueData = [];

        foreach ($period as $date) {
            $end = match ($interval) {
                '1 day' => $date->copy()->endOfDay(),
                '1 week' => $date->copy()->endOfWeek(),
                '1 month' => $date->copy()->endOfMonth(),
            };

            $labels[] = $days <= 30 ? $date->format('d/m') : ($days <= 90 ? $date->format('d/m') : $date->format('M'));
            $leadsData[] = Lead::whereBetween('created_at', [$date, $end])->count();
            $conversionsData[] = Lead::where('status', LeadStatus::ACTIF)->whereBetween('converted_at', [$date, $end])->count();
            $revenueData[] = (int) round(Commission::where('status', CommissionStatus::VALIDATED)->whereBetween('created_at', [$date, $end])->sum('amount_cents') / 100);
        }

        return [
            'labels' => $labels,
            'leads' => $leadsData,
            'conversions' => $conversionsData,
            'revenue' => $revenueData,
        ];
    }

    #[Computed]
    public function topPerformers()
    {
        $start = $this->periodStart();

        return User::where('role', 'commercial')
            ->withCount(['crmLeadsAssigned as conversions_count' => fn ($q) => $q->where('status', 'actif')->where('converted_at', '>=', $start)])
            ->orderByDesc('conversions_count')
            ->limit(10)
            ->get();
    }

    #[Computed]
    public function teams()
    {
        return Team::active()
            ->with('leader')
            ->withCount(['leads as active_leads_count' => fn ($q) => $q->active()])
            ->withCount(['leads as converted_count' => fn ($q) => $q->where('status', LeadStatus::ACTIF)->where('converted_at', '>=', $this->periodStart())])
            ->get();
    }

    #[Computed]
    public function recentActivity()
    {
        return Lead::with('assignedUser')
            ->latest()
            ->limit(8)
            ->get();
    }

    public function render()
    {
        return view('livewire.crm.admin-dashboard');
    }
}
