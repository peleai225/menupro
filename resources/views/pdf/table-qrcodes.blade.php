<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>QR Codes Tables — {{ $restaurant->name }}</title>
    <style>
        @page {
            size: A4 portrait;
            margin: 8mm;
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

        .page-grid {
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
            gap: 5mm;
        }

        /* ── Carte compacte : 2 colonnes × 4 rangées = 8 par page A4 portrait ── */
        .qr-card {
            width: 93mm;
            height: 66mm;
            border: 1.5px solid #ddd;
            border-radius: 8px;
            display: flex;
            align-items: center;
            overflow: hidden;
            background: #fff;
            page-break-inside: avoid;
            position: relative;
        }

        /* Accent bar left */
        .qr-card::before {
            content: '';
            position: absolute;
            left: 0;
            top: 10%;
            bottom: 10%;
            width: 3px;
            background: #f97316;
            border-radius: 0 3px 3px 0;
        }

        /* ── Left: QR code ── */
        .qr-left {
            width: 48%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 4mm 3mm 3mm 5mm;
        }

        .qr-image-box {
            border: 1px solid #eee;
            border-radius: 6px;
            padding: 2.5mm;
            display: inline-block;
            background: #fff;
        }

        .qr-image-box img,
        .qr-image-box svg {
            width: 36mm;
            height: 36mm;
            display: block;
        }

        .qr-caption {
            font-size: 6.5pt;
            color: #888;
            font-weight: 500;
            text-align: center;
            margin-top: 1.5mm;
            letter-spacing: 0.3px;
        }

        /* ── Right: Table info ── */
        .qr-right {
            width: 52%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 3mm 4mm 3mm 2mm;
            text-align: center;
        }

        .table-label {
            font-size: 7pt;
            font-weight: 700;
            letter-spacing: 3px;
            text-transform: uppercase;
            color: #999;
            margin-bottom: 0.5mm;
        }

        .table-number {
            font-size: 32pt;
            font-weight: 900;
            color: #111;
            line-height: 1;
            margin-bottom: 2mm;
        }

        .scan-cta {
            display: inline-block;
            background: #f97316;
            color: #fff;
            font-size: 8pt;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            padding: 1.5mm 5mm;
            border-radius: 4px;
            margin-bottom: 2.5mm;
        }

        .restaurant-name {
            font-size: 7pt;
            font-weight: 700;
            color: #333;
            margin-bottom: 1mm;
            max-width: 100%;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .logo-area {
            margin-bottom: 1mm;
        }

        .logo-area img {
            height: 8mm;
            width: auto;
            max-width: 35mm;
            object-fit: contain;
        }

        .logo-text {
            font-size: 10pt;
            font-weight: 800;
        }

        .logo-text .menu {
            color: #111;
        }

        .logo-text .pro {
            color: #f97316;
        }

        .footer-text {
            font-size: 5.5pt;
            color: #bbb;
            letter-spacing: 0.5px;
        }

        .footer-text strong {
            color: #f97316;
            font-weight: 700;
        }

        /* ── Page break ── */
        .page-break {
            page-break-after: always;
        }

        /* ── Cut guides (tirets discrets pour le découpage) ── */
        .cut-guide {
            width: 100%;
            text-align: center;
            padding: 1mm 0;
        }

        .cut-guide::before {
            content: '✂ - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - - -';
            font-size: 5pt;
            color: #ddd;
            letter-spacing: 1px;
        }
    </style>
</head>
<body>
    <div class="page-grid">
        @foreach($tables as $index => $table)
            <div class="qr-card">
                {{-- Left: QR Code --}}
                <div class="qr-left">
                    <div class="qr-image-box">
                        <img src="data:image/svg+xml;base64,{{ $table['qr_base64'] }}" alt="QR Table {{ $table['label'] }}">
                    </div>
                    <div class="qr-caption">Scannez pour commander</div>
                </div>

                {{-- Right: Table Info --}}
                <div class="qr-right">
                    <div class="table-label">TABLE</div>
                    <div class="table-number">{{ $table['label'] }}</div>
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
                </div>
            </div>

            {{-- Page break after every 8 cards (2 cols × 4 rows per A4 portrait) --}}
            @if(($index + 1) % 8 === 0 && ($index + 1) < count($tables))
                </div>
                <div class="page-break"></div>
                <div class="page-grid">
            @endif
        @endforeach
    </div>
</body>
</html>
