<?php

namespace App\Exports;

use App\Models\ActivityLog;
use Illuminate\Support\Collection;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Alignment;

class ActivityLogsExport implements FromCollection, WithHeadings, WithStyles, WithColumnWidths
{
    public function __construct(
        protected Collection $activities
    ) {}

    public function collection()
    {
        return $this->activities->map(function ($activity) {
            return [
                $activity->created_at->format('Y-m-d H:i:s'),
                $activity->user?->name ?? 'Système',
                $activity->user?->email ?? '-',
                $activity->restaurant?->name ?? '-',
                $activity->action,
                $activity->description ?? '-',
                $activity->ip_address ?? '-',
            ];
        });
    }

    public function headings(): array
    {
        return [
            'Date',
            'Utilisateur',
            'Email',
            'Restaurant',
            'Action',
            'Description',
            'Adresse IP',
        ];
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
            'C' => 25,
            'D' => 25,
            'E' => 20,
            'F' => 40,
            'G' => 15,
        ];
    }
}

