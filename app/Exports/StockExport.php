<?php

namespace App\Exports;

use App\Models\Restaurant;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithTitle;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithMultipleSheets;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class StockExport implements WithMultipleSheets
{
    public function __construct(
        protected Restaurant $restaurant,
        protected Collection $ingredients,
        protected Collection $movements
    ) {}

    public function sheets(): array
    {
        return [
            new StockInventorySheet($this->ingredients),
            new StockMovementsSheet($this->movements),
        ];
    }
}

class StockInventorySheet implements FromCollection, WithHeadings, WithTitle, WithStyles, WithColumnWidths
{
    public function __construct(protected Collection $ingredients) {}

    public function collection(): Collection
    {
        return $this->ingredients->map(fn ($ingredient) => [
            $ingredient->name,
            $ingredient->sku ?? '',
            $ingredient->category?->name ?? '',
            $ingredient->unit?->value ?? '',
            number_format($ingredient->current_quantity, 3, ',', ' '),
            number_format($ingredient->min_quantity, 3, ',', ' '),
            number_format($ingredient->unit_cost, 0, ',', ' '),
            number_format($ingredient->current_quantity * $ingredient->unit_cost, 0, ',', ' '),
            match (true) {
                $ingredient->current_quantity <= 0 => 'Rupture',
                $ingredient->current_quantity <= $ingredient->min_quantity => 'Stock bas',
                default => 'OK',
            },
            $ingredient->is_active ? 'Actif' : 'Inactif',
        ]);
    }

    public function headings(): array
    {
        return [
            'Ingrédient / Produit',
            'Référence (SKU)',
            'Catégorie',
            'Unité',
            'Qté actuelle',
            'Qté minimum',
            'Coût unitaire (FCFA)',
            'Valeur stock (FCFA)',
            'Statut',
            'Actif',
        ];
    }

    public function title(): string
    {
        return 'Inventaire';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 11],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E5E7EB'],
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 28,
            'B' => 16,
            'C' => 20,
            'D' => 10,
            'E' => 14,
            'F' => 14,
            'G' => 20,
            'H' => 22,
            'I' => 12,
            'J' => 10,
        ];
    }
}

class StockMovementsSheet implements FromCollection, WithHeadings, WithTitle, WithStyles, WithColumnWidths
{
    public function __construct(protected Collection $movements) {}

    public function collection(): Collection
    {
        return $this->movements->map(fn ($m) => [
            $m->created_at->format('d/m/Y H:i'),
            $m->ingredient?->name ?? '',
            $m->type->label(),
            number_format(abs($m->quantity), 3, ',', ' '),
            number_format($m->quantity_before, 3, ',', ' '),
            number_format($m->quantity_after, 3, ',', ' '),
            $m->unit_cost ? number_format($m->unit_cost, 0, ',', ' ') : '',
            $m->reason ?? '',
            $m->user?->name ?? 'Système',
        ]);
    }

    public function headings(): array
    {
        return [
            'Date',
            'Ingrédient / Produit',
            'Type de mouvement',
            'Quantité',
            'Stock avant',
            'Stock après',
            'Coût unitaire (FCFA)',
            'Motif',
            'Utilisateur',
        ];
    }

    public function title(): string
    {
        return 'Mouvements';
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font' => ['bold' => true, 'size' => 11],
                'fill' => [
                    'fillType' => Fill::FILL_SOLID,
                    'startColor' => ['rgb' => 'E5E7EB'],
                ],
                'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
            ],
        ];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 18,
            'B' => 28,
            'C' => 22,
            'D' => 12,
            'E' => 12,
            'F' => 12,
            'G' => 20,
            'H' => 30,
            'I' => 18,
        ];
    }
}
