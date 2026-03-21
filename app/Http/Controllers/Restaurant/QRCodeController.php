<?php

namespace App\Http\Controllers\Restaurant;

use App\Http\Controllers\Controller;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use SimpleSoftwareIO\QrCode\Facades\QrCode;

class QRCodeController extends Controller
{
    /**
     * Display the QR code page for the restaurant
     */
    public function index(Request $request)
    {
        $restaurant = $request->user()->restaurant;

        if (!$restaurant) {
            abort(404, 'Restaurant non trouvé');
        }

        $publicUrl = route('r.menu', ['slug' => $restaurant->slug]);

        return view('pages.restaurant.qrcode', [
            'restaurant' => $restaurant,
            'publicUrl' => $publicUrl,
        ]);
    }

    /**
     * Update the number of tables for the restaurant.
     */
    public function updateTables(Request $request)
    {
        $request->validate([
            'number_of_tables' => 'required|integer|min:1|max:200',
        ]);

        $restaurant = $request->user()->restaurant;

        if (!$restaurant) {
            abort(404, 'Restaurant non trouvé');
        }

        $restaurant->update([
            'number_of_tables' => $request->number_of_tables,
        ]);

        return back()->with('success', 'Nombre de tables mis à jour.');
    }

    /**
     * Generate PDF with QR codes for all tables (or a specific range).
     */
    public function downloadTableQR(Request $request)
    {
        $request->validate([
            'from_table' => 'nullable|integer|min:1',
            'to_table'   => 'nullable|integer|min:1',
        ]);

        $restaurant = $request->user()->restaurant;

        if (!$restaurant) {
            abort(404, 'Restaurant non trouvé');
        }

        $fromTable = (int) ($request->from_table ?? 1);
        $toTable   = (int) ($request->to_table ?? ($restaurant->number_of_tables ?? 1));

        // Safety cap
        if ($toTable > 200) $toTable = 200;
        if ($fromTable > $toTable) $fromTable = $toTable;

        $baseUrl = route('r.menu', ['slug' => $restaurant->slug]);

        // Generate QR codes for each table as base64 SVG
        $tables = [];
        for ($i = $fromTable; $i <= $toTable; $i++) {
            $tableUrl = $baseUrl . '?table=' . $i;
            $qrSvg = QrCode::format('svg')
                ->size(280)
                ->margin(1)
                ->errorCorrection('H')
                ->generate($tableUrl);

            $tables[] = [
                'number'     => $i,
                'label'      => str_pad($i, 2, '0', STR_PAD_LEFT),
                'url'        => $tableUrl,
                'qr_base64'  => base64_encode((string) $qrSvg),
            ];
        }

        // Get the MenuPro logo as base64
        $logoPath = public_path('images/logo-menupro.png');
        $logoBase64 = null;
        if (file_exists($logoPath)) {
            $logoBase64 = base64_encode(file_get_contents($logoPath));
        }

        $pdf = Pdf::loadView('pdf.table-qrcodes', [
            'restaurant' => $restaurant,
            'tables'     => $tables,
            'logoBase64' => $logoBase64,
        ])
        ->setPaper('a4', 'landscape')
        ->setOption('isRemoteEnabled', true);

        $filename = 'qrcodes-tables-' . \Illuminate\Support\Str::slug($restaurant->name) . '.pdf';

        return $pdf->download($filename);
    }

    /**
     * Preview a single table QR code card (browser view).
     */
    public function previewTableQR(Request $request, int $tableNumber)
    {
        $restaurant = $request->user()->restaurant;

        if (!$restaurant) {
            abort(404, 'Restaurant non trouvé');
        }

        $baseUrl  = route('r.menu', ['slug' => $restaurant->slug]);
        $tableUrl = $baseUrl . '?table=' . $tableNumber;

        $qrSvg = QrCode::format('svg')
            ->size(280)
            ->margin(1)
            ->errorCorrection('H')
            ->generate($tableUrl);

        $logoPath = public_path('images/logo-menupro.png');
        $logoBase64 = null;
        if (file_exists($logoPath)) {
            $logoBase64 = base64_encode(file_get_contents($logoPath));
        }

        return view('pdf.table-qrcodes', [
            'restaurant' => $restaurant,
            'tables'     => [[
                'number'    => $tableNumber,
                'label'     => str_pad($tableNumber, 2, '0', STR_PAD_LEFT),
                'url'       => $tableUrl,
                'qr_base64' => base64_encode((string) $qrSvg),
            ]],
            'logoBase64' => $logoBase64,
            'preview'    => true,
        ]);
    }
}
