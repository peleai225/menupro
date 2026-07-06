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
    public function monthlyTarget(): int
    {
        $user = auth()->user();
        return $user->commercialProfile?->monthly_target
            ?? $user->technicianProfile?->monthly_target
            ?? 5;
    }

    #[Computed]
    public function weeklyData(): array
    {
        $userId = auth()->id();
        $startDate = now()->subDays(6)->startOfDay();

        $leads = Lead::where('assigned_to', $userId)
            ->where('created_at', '>=', $startDate)
            ->selectRaw('DATE(created_at) as day, COUNT(*) as count')
            ->groupByRaw('DATE(created_at)')
            ->pluck('count', 'day');

        $conversions = Lead::where('assigned_to', $userId)
            ->where('converted_at', '>=', $startDate)
            ->selectRaw('DATE(converted_at) as day, COUNT(*) as count')
            ->groupByRaw('DATE(converted_at)')
            ->pluck('count', 'day');

        $data = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i);
            $key = $date->format('Y-m-d');
            $data[] = [
                'label' => $date->format('D'),
                'leads' => $leads[$key] ?? 0,
                'converted' => $conversions[$key] ?? 0,
            ];
        }
        return $data;
    }

    public function render()
    {
        return view('livewire.crm.performance-chart');
    }
}
