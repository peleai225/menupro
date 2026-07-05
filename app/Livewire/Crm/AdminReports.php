<?php

namespace App\Livewire\Crm;

use App\Models\Crm\DailyReport;
use App\Models\User;
use Livewire\Attributes\Computed;
use Livewire\Component;
use Livewire\WithPagination;

class AdminReports extends Component
{
    use WithPagination;

    public string $filterAgent = '';
    public string $filterDate = '';
    public string $filterStatus = ''; // 'pending', 'reviewed', ''
    public ?int $expandedReport = null;

    public function updatedFilterAgent(): void
    {
        $this->resetPage();
    }

    public function updatedFilterDate(): void
    {
        $this->resetPage();
    }

    public function updatedFilterStatus(): void
    {
        $this->resetPage();
    }

    public function toggleExpand(int $reportId): void
    {
        $this->expandedReport = $this->expandedReport === $reportId ? null : $reportId;
    }

    public function markReviewed(int $reportId): void
    {
        $report = DailyReport::findOrFail($reportId);
        $report->update([
            'reviewed_by' => auth()->id(),
            'reviewed_at' => now(),
        ]);

        unset($this->stats);
        $this->dispatch('toast', message: 'Rapport validé', type: 'success');
    }

    #[Computed]
    public function reports()
    {
        $query = DailyReport::with(['user', 'reviewedBy'])
            ->latest('report_date')
            ->latest('submitted_at');

        if ($this->filterAgent) {
            $query->whereHas('user', function ($q) {
                $q->where('name', 'like', '%' . $this->filterAgent . '%');
            });
        }

        if ($this->filterDate) {
            $query->whereDate('report_date', $this->filterDate);
        }

        if ($this->filterStatus === 'pending') {
            $query->pending();
        } elseif ($this->filterStatus === 'reviewed') {
            $query->whereNotNull('reviewed_by');
        }

        return $query->paginate(20);
    }

    #[Computed]
    public function stats(): array
    {
        $todayReports = DailyReport::today()->count();
        $activeAgents = User::whereIn('role', ['commercial', 'technician'])
            ->where('is_active', true)
            ->count();
        $pendingReview = DailyReport::pending()->whereNotNull('submitted_at')->count();
        $submissionRate = $activeAgents > 0 ? round(($todayReports / $activeAgents) * 100) : 0;

        return [
            'today_submitted' => $todayReports,
            'active_agents' => $activeAgents,
            'submission_rate' => $submissionRate,
            'pending_review' => $pendingReview,
        ];
    }

    #[Computed]
    public function agentsWithoutReport()
    {
        $agentsWithReport = DailyReport::today()->pluck('user_id');

        return User::whereIn('role', ['commercial', 'technician'])
            ->where('is_active', true)
            ->whereNotIn('id', $agentsWithReport)
            ->select('id', 'name', 'role')
            ->get();
    }

    public function render()
    {
        return view('livewire.crm.admin-reports');
    }
}
