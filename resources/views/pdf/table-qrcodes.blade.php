<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>QR Codes Tables — {{ $restaurant->name }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 6mm 8mm;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            color: #111;
            background: #fff;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
        }

        /* ── Grid: 2 columns using table ── */
        .grid-table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 4mm;
            table-layout: fixed;
        }

        .grid-table td {
            width: 50%;
            vertical-align: top;
            padding: 0;
        }

        /* ── Card ── */
        .qr-card {
            border: 1.5px solid #ddd;
            border-left: 3px solid #f97316;
            border-radius: 6px;
            overflow: hidden;
            background: #fff;
            page-break-inside: avoid;
            width: 100%;
        }

        .qr-card-inner {
            width: 100%;
            border-collapse: collapse;
        }

        .qr-card-inner td {
            vertical-align: middle;
            border: none;
        }

        /* ── Left: QR code ── */
        .qr-col {
            width: 45%;
            text-align: center;
            padding: 3mm 2mm 3mm 3mm;
        }

        .qr-image-box {
            border: 1px solid #eee;
            border-radius: 5px;
            padding: 2mm;
            display: inline-block;
            background: #fff;
        }

        .qr-image-box img {
            width: 34mm;
            height: 34mm;
        }

        .qr-caption {
            font-size: 6pt;
            color: #999;
            font-weight: 500;
            text-align: center;
            margin-top: 1.5mm;
            letter-spacing: 0.3px;
        }

        /* ── Right: Info ── */
        .info-col {
            width: 55%;
            text-align: center;
            padding: 3mm 4mm 3mm 2mm;
        }

        .table-label {
            font-size: 7pt;
            font-weight: 700;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: #aaa;
            margin-bottom: 0;
        }

        .table-number {
            font-size: 36pt;
            font-weight: 900;
            color: #111;
            line-height: 1.1;
            margin-bottom: 2mm;
        }

        .scan-cta {
            display: inline-block;
            background: #f97316;
            color: #fff;
            font-size: 7.5pt;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            padding: 1.5mm 5mm;
            border-radius: 3px;
            margin-bottom: 2mm;
        }

        .restaurant-name {
            font-size: 7pt;
            font-weight: 700;
            color: #333;
            margin-bottom: 1mm;
            max-width: 100%;
            overflow: hidden;
        }

        .logo-area {
            margin-bottom: 1mm;
        }

        .logo-area img {
            height: 7mm;
            width: auto;
            max-width: 30mm;
        }

        .footer-text {
            font-size: 5.5pt;
            color: #ccc;
            letter-spacing: 0.3px;
        }

        .footer-text strong {
            color: #f97316;
            font-weight: 700;
        }

        /* ── Page break ── */
        .page-break {
            page-break-after: always;
        }

        /* ── Empty cell ── */
        .empty-cell {
            border: none !important;
        }
    </style>
</head>
<body>
@php
    $chunks = array_chunk($tables, 8); // 8 cards per page (2 cols × 4 rows)
@endphp

@foreach($chunks as $chunkIndex => $chunk)
    @php
        $rows = array_chunk($chunk, 2); // 2 per row
    @endphp
    <table class="grid-table">
        @foreach($rows as $row)
            <tr>
                @foreach($row as $table)
                    <td>
                        <table class="qr-card">
                            <tr>
                                <td class="qr-card-inner">
                                    <table class="qr-card-inner">
                                        <tr>
                                            {{-- Left: QR Code --}}
                                            <td class="qr-col">
                                                <div class="qr-image-box">
                                                    <img src="data:image/svg+xml;base64,{{ $table['qr_base64'] }}" alt="QR Table {{ $table['label'] }}">
                                                </div>
                                                <div class="qr-caption">Scannez pour commander</div>
                                            </td>

                                            {{-- Right: Table Info --}}
                                            <td class="info-col">
                                                <div class="table-label">T A B L E</div>
                                                <div class="table-number">{{ str_pad($table['label'], 2, '0', STR_PAD_LEFT) }}</div>
                                                <div class="scan-cta">Scanner ici</div>

                                                <div class="logo-area">
                                                    @if($logoBase64)
                                                        <img src="data:image/png;base64,{{ $logoBase64 }}" alt="{{ $restaurant->name }}">
                                                    @else
                                                        <div class="restaurant-name">{{ $restaurant->name }}</div>
                                                    @endif
                                                </div>

                                                <div class="footer-text">
                                                    Propulsé par <strong>menupro.ci</strong>
                                                </div>
                                            </td>
                                        </tr>
                                    </table>
                                </td>
                            </tr>
                        </table>
                    </td>
                @endforeach

                {{-- Fill empty cell if odd number in row --}}
                @if(count($row) < 2)
                    <td class="empty-cell"></td>
                @endif
            </tr>
        @endforeach
    </table>

    @if($chunkIndex < count($chunks) - 1)
        <div class="page-break"></div>
    @endif
@endforeach
</body>
</html>
