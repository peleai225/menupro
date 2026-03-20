<?php

namespace App\Exports;

use App\Models\Restaurant;
use Illuminate\Http\Request;
use Maatwebsite\Excel\Concerns\FromQuery;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithTitle;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class RestaurantsExport implements FromQuery, WithHeadings, WithMapping, ShouldAutoSize, WithStyles, WithTitle
{
    public function __construct(protected Request $request) {}

    public function title(): string
    {
        return 'Restaurants';
    }

    public function query()
    {
        $query = Restaurant::with('owner')->orderBy('name');

        if ($this->request->filled('status')) {
            $query->where('status', $this->request->status);
        }

        if ($this->request->filled('plan')) {
            $query->where('current_plan_id', $this->request->plan);
        }

        if ($this->request->filled('search')) {
            $search = $this->request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('city', 'like', "%{$search}%");
            });
        }

        return $query;
    }

    public function headings(): array
    {
        return [
            'Nom du restaurant',
            'Ville',
            'Email restaurant',
            'Téléphone restaurant',
            'Nom du gérant',
            'Prénom du gérant',
            'Email du gérant',
            'Téléphone du gérant',
            'Statut',
            'Date d\'inscription',
        ];
    }

    public function map($restaurant): array
    {
        $ownerName  = $restaurant->owner?->name ?? '';
        $nameParts  = explode(' ', trim($ownerName), 2);
        $firstName  = $nameParts[0] ?? '';
        $lastName   = $nameParts[1] ?? '';

        return [
            $restaurant->name,
            $restaurant->city ?? '',
            $restaurant->email ?? '',
            $restaurant->phone ?? '',
            $lastName,
            $firstName,
            $restaurant->owner?->email ?? '',
            $restaurant->owner?->phone ?? '',
            $restaurant->status->value,
            $restaurant->created_at->format('d/m/Y'),
        ];
    }

    public function styles(Worksheet $sheet): array
    {
        return [
            1 => [
                'font'      => ['bold' => true, 'color' => ['argb' => 'FFFFFFFF']],
                'fill'      => ['fillType' => 'solid', 'startColor' => ['argb' => 'FF4F46E5']],
                'alignment' => ['horizontal' => 'center'],
            ],
        ];
    }
}
