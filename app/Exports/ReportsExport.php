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
use PhpOffice\PhpSpreadsheet\Style\Border;

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
            'sales'     => $this->getSalesData(),
            'dishes'    => $this->getDishesData(),
            'customers' => $this->getCustomersData(),
            'financial' => $this->getFinancialData(),
            'waiters'   => $this->getWaitersData(),
            'daily'     => $this->getDailyData(),
            default     => [],
        };
    }

    public function headings(): array
    {
        return match ($this->reportType) {
            'sales'     => ['Date', 'Commandes', 'CA Brut (FCFA)', 'CA Net (FCFA)'],
            'dishes'    => ['Plat', 'Quantité vendue', 'Revenus (FCFA)', 'Prix moyen (FCFA)'],
            'customers' => ['Client', 'Téléphone', 'Email', 'Commandes', 'Total dépensé (FCFA)', 'Panier moyen (FCFA)'],
            'financial' => ['Catégorie', 'Montant (FCFA)', 'Détail'],
            'waiters'   => ['Serveur', 'Commandes', 'CA (FCFA)', 'Ticket moyen (FCFA)', 'Espace principal'],
            'daily'     => ['Tranche horaire', 'Commandes', 'CA Total (FCFA)', 'Espèces (FCFA)', 'Mobile Money (FCFA)'],
            default     => [],
        };
    }

    public function title(): string
    {
        return match ($this->reportType) {
            'sales'     => 'Ventes',
            'dishes'    => 'Plats',
            'customers' => 'Clients',
            'financial' => 'Financier',
            'waiters'   => 'Serveurs',
            'daily'     => 'Bilan journalier',
            default     => 'Rapport',
        };
    }

    public function styles(Worksheet $sheet): array
    {
        $lastRow = $sheet->getHighestRow();
        $lastCol = $sheet->getHighestColumn();

        $styles = [
            1 => [
                'font' => ['bold' => true, 'size' => 11, 'color' => ['rgb' => 'FFFFFF']],
                'fill' => [
                    'fillType'   => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'D45E0C'],
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];

        // Total row style (last row starting with TOTAL)
        if ($lastRow > 1) {
            $firstCell = $sheet->getCell('A' . $lastRow)->getValue();
            if (str_starts_with((string) $firstCell, 'TOTAL')) {
                $styles[$lastRow] = [
                    'font' => ['bold' => true],
                    'fill' => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'FEF3C7'],
                    ],
                ];
            }
        }

        // Borders
        $sheet->getStyle('A1:' . $lastCol . $lastRow)->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color'       => ['rgb' => 'E5E7EB'],
                ],
            ],
        ]);

        // Alternate row shading
        for ($row = 2; $row < $lastRow; $row++) {
            if ($row % 2 === 0) {
                $styles[$row] = [
                    'fill' => [
                        'fillType'   => Fill::FILL_SOLID,
                        'startColor' => ['rgb' => 'F9FAFB'],
                    ],
                ];
            }
        }

        return $styles;
    }

    public function columnWidths(): array
    {
        return ['A' => 28, 'B' => 18, 'C' => 22, 'D' => 22, 'E' => 22, 'F' => 22];
    }

    // ─── Sales ───────────────────────────────────────────────────────────────

    protected function getSalesData(): array
    {
        $data = [];
        $totOrders = 0;
        $totBrut   = 0;
        $totNet    = 0;

        foreach ($this->reportData['sales_by_day'] ?? [] as $day) {
            $orders = (int)   ($day['orders']       ?? 0);
            $brut   = (float) ($day['revenue_brut'] ?? 0);
            $net    = (float) ($day['revenue_net']  ?? 0);

            $totOrders += $orders;
            $totBrut   += $brut;
            $totNet    += $net;

            $data[] = [
                $day['date'] ?? '',
                $orders,
                number_format($brut, 0, ',', ' '),
                number_format($net,  0, ',', ' '),
            ];
        }

        $data[] = ['TOTAL', $totOrders, number_format($totBrut, 0, ',', ' '), number_format($totNet, 0, ',', ' ')];
        return $data;
    }

    // ─── Dishes ──────────────────────────────────────────────────────────────

    protected function getDishesData(): array
    {
        $data = [];
        $totSold = 0;
        $totRev  = 0;

        foreach ($this->reportData['top_dishes'] ?? [] as $dish) {
            $sold    = (int)   ($dish['total_sold']    ?? 0);
            $revenue = (float) ($dish['total_revenue'] ?? 0);
            $avg     = $sold > 0 ? $revenue / $sold : 0;

            $totSold += $sold;
            $totRev  += $revenue;

            $data[] = [
                $dish['name'] ?? '',
                $sold,
                number_format($revenue, 0, ',', ' '),
                number_format($avg,     0, ',', ' '),
            ];
        }

        $data[] = ['TOTAL', $totSold, number_format($totRev, 0, ',', ' '), ''];
        return $data;
    }

    // ─── Customers ───────────────────────────────────────────────────────────

    protected function getCustomersData(): array
    {
        $data = [];

        foreach ($this->reportData['top_customers'] ?? [] as $c) {
            $spent = (float) ($c['total_spent']      ?? 0);
            $count = (int)   ($c['orders_count']     ?? 0);
            $avg   = $count > 0 ? $spent / $count : 0;

            $data[] = [
                $c['customer_name']  ?? '',
                $c['customer_phone'] ?? '',
                $c['customer_email'] ?? '',
                $count,
                number_format($spent, 0, ',', ' '),
                number_format($avg,   0, ',', ' '),
            ];
        }

        return $data;
    }

    // ─── Financial ───────────────────────────────────────────────────────────

    protected function getFinancialData(): array
    {
        $data = [];

        $brut       = (float) ($this->reportData['total_revenue_brut'] ?? $this->reportData['total_revenue'] ?? 0);
        $net        = (float) ($this->reportData['total_revenue_net']  ?? 0);
        $subtotal   = (float) ($this->reportData['total_subtotal']     ?? 0);
        $delivery   = (float) ($this->reportData['total_delivery_fees']?? 0);
        $discounts  = (float) ($this->reportData['total_discounts']    ?? 0);
        $commission = (float) ($this->reportData['total_commission']   ?? 0);
        $cash       = (float) ($this->reportData['cash_total']         ?? 0);
        $mobile     = (float) ($this->reportData['mobile_total']       ?? 0);

        $data[] = ['CA Brut',            number_format($brut,       0, ',', ' '), ''];
        $data[] = ['CA Net',             number_format($net,        0, ',', ' '), ''];
        $data[] = ['Sous-total',         number_format($subtotal,   0, ',', ' '), ''];
        $data[] = ['Frais de livraison', number_format($delivery,   0, ',', ' '), ''];
        $data[] = ['Réductions',         '-' . number_format($discounts,  0, ',', ' '), ''];
        $data[] = ['Commissions',        '-' . number_format($commission, 0, ',', ' '), ''];

        if ($cash > 0 || $mobile > 0) {
            $data[] = ['', '', ''];
            $data[] = ['Espèces à encaisser', number_format($cash,   0, ',', ' '), ''];
            $data[] = ['Mobile Money reçu',   number_format($mobile, 0, ',', ' '), ''];
        }

        // Répartition par moyen de paiement
        $payments = $this->reportData['by_payment_detailed'] ?? $this->reportData['revenue_by_payment'] ?? [];
        if (!empty($payments)) {
            $data[] = ['', '', ''];
            $data[] = ['— RÉPARTITION PAR PAIEMENT —', '', ''];
            foreach ($payments as $p) {
                $label  = $p['label'] ?? ucfirst($p['payment_method'] ?? ($p['method'] ?? '—'));
                $amount = (float) ($p['total_amount'] ?? $p['revenue_brut'] ?? $p['revenue'] ?? 0);
                $count  = (int)   ($p['orders_count'] ?? $p['count'] ?? 0);
                $pct    = $brut > 0 ? round($amount / $brut * 100, 1) : 0;
                $data[] = [$label, number_format($amount, 0, ',', ' '), $count . ' cmd · ' . $pct . '%'];
            }
        }

        // Comparaison période précédente
        if (!empty($this->reportData['vs_previous'])) {
            $vs   = $this->reportData['vs_previous'];
            $sign = ($vs['change_pct'] ?? 0) >= 0 ? '+' : '';
            $data[] = ['', '', ''];
            $data[] = ['— COMPARAISON PÉRIODE PRÉCÉDENTE —', '', ''];
            $data[] = ['CA période actuelle',   number_format($brut, 0, ',', ' '), ''];
            $data[] = ['CA période précédente', number_format((float)($vs['revenue'] ?? 0), 0, ',', ' '), ''];
            $data[] = ['Évolution',             $sign . ($vs['change_pct'] ?? 0) . '%', ''];
        }

        return $data;
    }

    // ─── Waiters ─────────────────────────────────────────────────────────────

    protected function getWaitersData(): array
    {
        $data = [];

        $rows = $this->reportData['rows'] ?? $this->reportData['waiters'] ?? [];
        foreach ($rows as $w) {
            $data[] = [
                $w['waiter_name']   ?? ($w['name']  ?? ''),
                (int)   ($w['orders_count']  ?? 0),
                number_format((float) ($w['total_revenue'] ?? 0), 0, ',', ' '),
                number_format((float) ($w['avg_order']     ?? 0), 0, ',', ' '),
                $w['primary_space'] ?? ($w['space'] ?? ''),
            ];
        }

        if (!empty($data)) {
            $data[] = [
                'TOTAL',
                (int) ($this->reportData['total_orders']  ?? 0),
                number_format((float) ($this->reportData['total_revenue'] ?? 0), 0, ',', ' '),
                '',
                '',
            ];
        }

        return $data;
    }

    // ─── Daily / Bilan journalier ─────────────────────────────────────────────

    protected function getDailyData(): array
    {
        $data = [];

        foreach ($this->reportData['by_hour'] ?? [] as $row) {
            $label = $row['hour_label'] ?? sprintf('%02dh–%02dh', $row['hour'] ?? 0, ($row['hour'] ?? 0) + 1);
            $data[] = [
                $label,
                (int)   ($row['orders_count']  ?? 0),
                number_format((float) ($row['total_amount']  ?? 0), 0, ',', ' '),
                number_format((float) ($row['cash_amount']   ?? 0), 0, ',', ' '),
                number_format((float) ($row['mobile_amount'] ?? 0), 0, ',', ' '),
            ];
        }

        $data[] = [
            'TOTAL',
            (int) ($this->reportData['total_orders']  ?? 0),
            number_format((float) ($this->reportData['total_revenue'] ?? 0), 0, ',', ' '),
            number_format((float) ($this->reportData['cash_total']    ?? 0), 0, ',', ' '),
            number_format((float) ($this->reportData['mobile_total']  ?? 0), 0, ',', ' '),
        ];

        return $data;
    }
}
