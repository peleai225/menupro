<?php

namespace App\Exports;

use App\Models\Order;
use App\Models\Restaurant;
use App\Models\Subscription;
use Illuminate\Support\Facades\DB;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class SuperAdminStatsExport implements FromArray, WithHeadings, WithTitle, WithStyles, WithColumnWidths
{
    public function __construct(
        protected int $period,
        protected string $type = 'all'
    ) {}

    public function array(): array
    {
        $startDate = now()->subDays($this->period);
        $endDate = now();

        return match ($this->type) {
            'revenue' => $this->getRevenueData($startDate, $endDate),
            'growth' => $this->getGrowthData($startDate, $endDate),
            default => $this->getAllData($startDate, $endDate),
        };
    }

    public function headings(): array
    {
        return match ($this->type) {
            'revenue' => ['Date', 'Revenus (FCFA)', 'Commandes', 'Moyenne (FCFA)'],
            'growth' => ['Période', 'Restaurants', 'Commandes', 'Revenus (FCFA)', 'Abonnements'],
            default => ['Métrique', 'Valeur', 'Période'],
        };
    }

    public function title(): string
    {
        return match ($this->type) {
            'revenue' => 'Revenus',
            'growth' => 'Croissance',
            default => 'Statistiques',
        };
    }

    public function styles(Worksheet $sheet)
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 12],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E5E7EB'],
                ],
                'alignment' => [
                    'horizontal' => Alignment::HORIZONTAL_CENTER,
                ],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 20,
            'B' => 20,
            'C' => 20,
            'D' => 20,
            'E' => 20,
        ];
    }

    protected function getRevenueData($startDate, $endDate): array
    {
        $data = [];
        
        $revenueData = Order::withoutGlobalScope('restaurant')
            ->where('payment_status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->select(
                DB::raw('DATE(created_at) as date'),
                DB::raw('SUM(total) as revenue'),
                DB::raw('COUNT(*) as orders'),
                DB::raw('AVG(total) as average')
            )
            ->groupBy('date')
            ->orderBy('date')
            ->get();

        foreach ($revenueData as $row) {
            $data[] = [
                $row->date,
                number_format($row->revenue / 100, 0, ',', ' '),
                $row->orders,
                number_format($row->average / 100, 0, ',', ' '),
            ];
        }

        return $data;
    }

    protected function getGrowthData($startDate, $endDate): array
    {
        $data = [];
        $weeks = ceil($this->period / 7);

        for ($i = 0; $i < $weeks; $i++) {
            $weekStart = $startDate->copy()->addWeeks($i);
            $weekEnd = $weekStart->copy()->addWeek();

            $restaurants = Restaurant::whereBetween('created_at', [$weekStart, $weekEnd])->count();
            $orders = Order::withoutGlobalScope('restaurant')
                ->whereBetween('created_at', [$weekStart, $weekEnd])
                ->count();
            $revenue = Order::withoutGlobalScope('restaurant')
                ->where('payment_status', 'completed')
                ->whereBetween('created_at', [$weekStart, $weekEnd])
                ->sum('total');
            $subscriptions = Subscription::where('status', 'active')
                ->whereBetween('created_at', [$weekStart, $weekEnd])
                ->count();

            $data[] = [
                $weekStart->format('Y-m-d') . ' / ' . $weekEnd->format('Y-m-d'),
                $restaurants,
                $orders,
                number_format($revenue / 100, 0, ',', ' '),
                $subscriptions,
            ];
        }

        return $data;
    }

    protected function getAllData($startDate, $endDate): array
    {
        $totalRevenue = Order::withoutGlobalScope('restaurant')
            ->where('payment_status', 'completed')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('total');

        $totalOrders = Order::withoutGlobalScope('restaurant')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->count();

        $totalRestaurants = Restaurant::whereBetween('created_at', [$startDate, $endDate])->count();

        $totalSubscriptions = Subscription::where('status', 'active')
            ->whereBetween('created_at', [$startDate, $endDate])
            ->sum('amount_paid');

        return [
            ['Revenus totaux', number_format($totalRevenue / 100, 0, ',', ' ') . ' FCFA', $this->period . ' jours'],
            ['Commandes totales', $totalOrders, $this->period . ' jours'],
            ['Nouveaux restaurants', $totalRestaurants, $this->period . ' jours'],
            ['Revenus abonnements', number_format($totalSubscriptions / 100, 0, ',', ' ') . ' FCFA', $this->period . ' jours'],
        ];
    }
}

