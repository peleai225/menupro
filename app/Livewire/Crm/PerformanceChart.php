<?php

namespace App\Livewire\Crm;

use App\Enums\Crm\LeadStatus;
use App\Models\Crm\Lead;
use App\Models\Crm\PerformanceSnapshot;
use App\Models\Crm\UserGrade;
use Livewire\Attributes\Computed;
use Livewire\Component;

class PerformanceChart extends Component
{
    public string $period = 'month';

    #[Computed]
    public function stats(): array
    {
        $userId = auth()->id();
        $startDate = match ($this->period) {
            'week' => now()->startOfWeek(),
            'month' => now()->startOfMonth(),
            'quarter' => now()->startOfQuarter(),
            default => now()->startOfMonth(),
        };

        $leads = Lead::where('assigned_to', $userId)
            ->where('created_at', '>=', $startDate);

        $totalLeads = (clone $leads)->count();
        $converted = (clone $leads)->where('status', LeadStatus::ACTIF)->count();
        $conversionRate = $totalLeads > 0 ? round(($converted / $totalLeads) * 100) : 0;

        return [
            'total_leads' => $totalLeads,
            'converted' => $converted,
            'conversion_rate' => $conversionRate,
            'in_pipeline' => (clone $leads)->active()->count(),
            'lost' => (clone $leads)->where('status', LeadStatus::PERDU)->count(),
        ];
    }

    #[Computed]
    public function grade(): ?UserGrade
    {
        return UserGrade::where('user_id', auth()->id())->first();
    }

    #[Computed]
    public function weeklyData(): array
    {
        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $data[] = [
                'label' => $date->format('D'),
                'leads' => Lead::where('assigned_to', auth()->id())
                    ->whereDate('created_at', $date)
                    ->count(),
                'converted' => Lead::where('assigned_to', auth()->id())
                    ->whereDate('converted_at', $date)
                    ->count(),
            ];
        }
        return $data;
    }

    public function render()
    {
        return view('livewire.crm.performance-chart');
    }
}
