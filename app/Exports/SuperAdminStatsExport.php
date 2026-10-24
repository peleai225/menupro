<?php

namespace App\Exports;

use App\Services\PlatformMetrics;
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
            'revenue' => ['Date', 'Volume payé (FCFA)', 'Commandes payées', 'Panier moyen (FCFA)'],
            'growth' => ['Période', 'Nouveaux restaurants', 'Commandes passées', 'Volume payé (FCFA)', 'Abonnements encaissés'],
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

        // Montants en FCFA (aucune division) ; CA = commandes payées non annulées, par date de paiement
        $revenueData = $this->metrics()->revenueOrders()
            ->whereBetween('orders.paid_at', [$startDate, $endDate])
            ->selectRaw('DATE(orders.paid_at) as date, SUM(orders.total) as revenue, COUNT(*) as orders, AVG(orders.total) as average')
            ->groupBy('date')
            ->orderBy('date')
            ->toBase()
            ->get();

        foreach ($revenueData as $row) {
            $data[] = [
                $row->date,
                number_format((float) $row->revenue, 0, ',', ' '),
                $row->orders,
                number_format((float) $row->average, 0, ',', ' '),
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
            $weekEnd = $weekStart->copy()->addWeek()->subSecond();

            $restaurants = $this->metrics()->restaurants()->whereBetween('created_at', [$weekStart, $weekEnd])->count();
            $orders = $this->metrics()->placedOrders()
                ->whereBetween('orders.created_at', [$weekStart, $weekEnd])
                ->count();
            $revenue = $this->metrics()->gmv($weekStart, $weekEnd);
            $subscriptions = $this->metrics()->collectedSubscriptionsBetween($weekStart, $weekEnd)->count();

            $data[] = [
                $weekStart->format('Y-m-d') . ' / ' . $weekEnd->format('Y-m-d'),
                $restaurants,
                $orders,
                number_format($revenue, 0, ',', ' '),
                $subscriptions,
            ];
        }

        return $data;
    }

    protected function getAllData($startDate, $endDate): array
    {
        $totalRevenue = $this->metrics()->gmv($startDate, $endDate);

        $totalOrders = $this->metrics()->placedOrders()
            ->whereBetween('orders.created_at', [$startDate, $endDate])
            ->count();

        $totalRestaurants = $this->metrics()->restaurants()->whereBetween('created_at', [$startDate, $endDate])->count();

        $totalSubscriptions = $this->metrics()->subscriptionRevenue($startDate, $endDate);

        return [
            ['Volume payé (GMV)', number_format($totalRevenue, 0, ',', ' ') . ' FCFA', $this->period . ' jours'],
            ['Commandes passées', $totalOrders, $this->period . ' jours'],
            ['Nouveaux restaurants', $totalRestaurants, $this->period . ' jours'],
            ['Revenus abonnements encaissés', number_format($totalSubscriptions, 0, ',', ' ') . ' FCFA', $this->period . ' jours'],
            ['MRR (à date)', number_format($this->metrics()->mrr(), 0, ',', ' ') . ' FCFA', 'aujourd\'hui'],
        ];
    }

    protected function metrics(): PlatformMetrics
    {
        return app(PlatformMetrics::class);
    }
}
