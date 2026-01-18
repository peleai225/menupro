<?php

namespace App\Exports;

use App\Models\Restaurant;
use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ReportsExport implements FromArray, WithHeadings, WithTitle, WithStyles, WithColumnWidths
{
    public function __construct(
        protected Restaurant $restaurant,
        protected array $reportData,
        protected string $reportType,
        protected $startDate,
        protected $endDate
    ) {}

    public function array(): array
    {
        return match ($this->reportType) {
            'sales' => $this->getSalesData(),
            'dishes' => $this->getDishesData(),
            'customers' => $this->getCustomersData(),
            'financial' => $this->getFinancialData(),
            default => [],
        };
    }

    public function headings(): array
    {
        return match ($this->reportType) {
            'sales' => ['Date', 'Commandes', 'Revenus (FCFA)', 'Moyenne (FCFA)'],
            'dishes' => ['Plat', 'Quantité vendue', 'Revenus (FCFA)', 'Pourcentage'],
            'customers' => ['Client', 'Email', 'Commandes', 'Total dépensé (FCFA)', 'Dernière commande'],
            'financial' => ['Type', 'Montant (FCFA)', 'Pourcentage'],
            default => [],
        };
    }

    public function title(): string
    {
        return match ($this->reportType) {
            'sales' => 'Ventes',
            'dishes' => 'Plats',
            'customers' => 'Clients',
            'financial' => 'Financier',
            default => 'Rapport',
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

    protected function getSalesData(): array
    {
        $data = [];
        foreach ($this->reportData['sales_by_day'] ?? [] as $day => $stats) {
            $data[] = [
                $day,
                $stats['count'] ?? 0,
                number_format(($stats['revenue'] ?? 0) / 100, 0, ',', ' '),
                number_format(($stats['average'] ?? 0) / 100, 0, ',', ' '),
            ];
        }
        return $data;
    }

    protected function getDishesData(): array
    {
        $data = [];
        foreach ($this->reportData['top_dishes'] ?? [] as $dish) {
            $data[] = [
                $dish['name'] ?? '',
                $dish['quantity'] ?? 0,
                number_format(($dish['revenue'] ?? 0) / 100, 0, ',', ' '),
                number_format(($dish['percentage'] ?? 0), 2) . '%',
            ];
        }
        return $data;
    }

    protected function getCustomersData(): array
    {
        $data = [];
        foreach ($this->reportData['top_customers'] ?? [] as $customer) {
            $data[] = [
                $customer['name'] ?? '',
                $customer['email'] ?? '',
                $customer['orders_count'] ?? 0,
                number_format(($customer['total_spent'] ?? 0) / 100, 0, ',', ' '),
                $customer['last_order'] ?? '',
            ];
        }
        return $data;
    }

    protected function getFinancialData(): array
    {
        $data = [];
        $total = $this->reportData['total_revenue'] ?? 0;
        
        $data[] = ['Sous-total', number_format(($this->reportData['total_subtotal'] ?? 0) / 100, 0, ',', ' '), ''];
        $data[] = ['Frais de livraison', number_format(($this->reportData['total_delivery_fees'] ?? 0) / 100, 0, ',', ' '), ''];
        $data[] = ['Réductions', '-' . number_format(($this->reportData['total_discounts'] ?? 0) / 100, 0, ',', ' '), ''];
        $data[] = ['TOTAL REVENUS', number_format($total / 100, 0, ',', ' '), '100%'];
        
        foreach ($this->reportData['revenue_by_payment'] ?? [] as $method => $amount) {
            $percentage = $total > 0 ? ($amount / $total) * 100 : 0;
            $data[] = [
                ucfirst($method),
                number_format($amount / 100, 0, ',', ' '),
                number_format($percentage, 2) . '%',
            ];
        }
        
        return $data;
    }
}

