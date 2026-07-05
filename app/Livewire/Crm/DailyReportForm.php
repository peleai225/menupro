<?php

namespace App\Livewire\Crm;

use App\Enums\Crm\LeadStatus;
use App\Models\Crm\DailyReport;
use App\Models\Crm\Lead;
use Livewire\Attributes\Computed;
use Livewire\Component;

class DailyReportForm extends Component
{
    public int $visits_count = 0;
    public int $demos_count = 0;
    public string $zone_covered = '';
    public string $obstacles = '';
    public string $notes = '';

    public ?DailyReport $todayReport = null;
    public bool $editing = false;

    protected function rules(): array
    {
        return [
            'visits_count' => ['required', 'integer', 'min:0', 'max:999'],
            'demos_count' => ['required', 'integer', 'min:0', 'max:999'],
            'zone_covered' => ['nullable', 'string', 'max:255'],
            'obstacles' => ['nullable', 'string', 'max:2000'],
            'notes' => ['nullable', 'string', 'max:2000'],
        ];
    }

    protected function validationAttributes(): array
    {
        return [
            'visits_count' => 'nombre de visites',
            'demos_count' => 'nombre de demos',
            'zone_covered' => 'zone couverte',
            'obstacles' => 'obstacles',
            'notes' => 'notes',
        ];
    }

    public function mount(): void
    {
        $this->loadTodayReport();
    }

    private function loadTodayReport(): void
    {
        $this->todayReport = DailyReport::forUser(auth()->id())
            ->today()
            ->first();

        if ($this->todayReport) {
            $this->visits_count = $this->todayReport->visits_count;
            $this->demos_count = $this->todayReport->demos_count;
            $this->zone_covered = $this->todayReport->zone_covered ?? '';
            $this->obstacles = $this->todayReport->obstacles ?? '';
            $this->notes = $this->todayReport->notes ?? '';
        }
    }

    #[Computed]
    public function newLeadsCount(): int
    {
        return Lead::forUser(auth()->id())
            ->whereDate('created_at', today())
            ->count();
    }

    #[Computed]
    public function conversionsCount(): int
    {
        return Lead::forUser(auth()->id())
            ->where('status', LeadStatus::ACTIF)
            ->whereDate('converted_at', today())
            ->count();
    }

    public function incrementVisits(): void { $this->visits_count = min(999, $this->visits_count + 1); }
    public function decrementVisits(): void { $this->visits_count = max(0, $this->visits_count - 1); }
    public function incrementDemos(): void  { $this->demos_count  = min(999, $this->demos_count  + 1); }
    public function decrementDemos(): void  { $this->demos_count  = max(0, $this->demos_count  - 1); }

    public function startEditing(): void
    {
        $this->editing = true;
    }

    public function submit(): void
    {
        $this->validate();

        $report = DailyReport::updateOrCreate(
            [
                'user_id' => auth()->id(),
                'report_date' => today(),
            ],
            [
                'visits_count' => $this->visits_count,
                'new_leads_count' => $this->newLeadsCount,
                'demos_count' => $this->demos_count,
                'conversions_count' => $this->conversionsCount,
                'zone_covered' => $this->zone_covered ?: null,
                'obstacles' => $this->obstacles ?: null,
                'notes' => $this->notes ?: null,
                'submitted_at' => now(),
            ]
        );

        $this->todayReport = $report;
        $this->editing = false;

        $this->dispatch('reportSubmitted');
        $this->dispatch('toast', message: 'Rapport soumis avec succès !', type: 'success');
    }

    public function render()
    {
        return view('livewire.crm.daily-report-form');
    }
}
