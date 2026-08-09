<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use App\Livewire\Restaurant\Reports;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;

class ReportExportController extends Controller
{
    public function pdf(Request $request)
    {
        $restaurant = auth()->user()->restaurant;

        if (!$restaurant) {
            abort(403);
        }

        $reportType = $request->get('type', 'sales');
        $startDate  = $request->get('start')
            ? Carbon::parse($request->get('start'))->startOfDay()
            : now()->subDays(30)->startOfDay();
        $endDate    = $request->get('end')
            ? Carbon::parse($request->get('end'))->endOfDay()
            : now()->endOfDay();

        // Utilise le même service de données que le composant Livewire
        $reportsComponent = new Reports();
        $reportsComponent->reportType = $reportType;
        $reportsComponent->startDate  = $startDate->format('Y-m-d');
        $reportsComponent->endDate    = $endDate->format('Y-m-d');
        // Force l'utilisateur courant pour getReportData
        auth()->setUser(auth()->user());

        $reportData = $reportsComponent->getReportDataPublic($restaurant->id, $startDate, $endDate);

        $pdf = Pdf::loadView('livewire.restaurant.reports-pdf', [
            'restaurant' => $restaurant,
            'reportData' => $reportData,
            'reportType' => $reportType,
            'startDate'  => $startDate,
            'endDate'    => $endDate,
        ])
        ->setPaper('a4', 'portrait')
        ->setOptions([
            'defaultFont'     => 'DejaVu Sans',
            'isRemoteEnabled' => false,
            'isHtml5ParserEnabled' => true,
        ]);

        $filename = 'rapport-' . $reportType . '-' . $startDate->format('Y-m-d') . '.pdf';

        return $pdf->download($filename);
    }
}
