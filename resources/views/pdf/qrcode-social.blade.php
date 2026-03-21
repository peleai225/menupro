<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <title>QR Code Social — {{ $restaurant->name }}</title>
    <style>
        @page {
            size: 1200px 630px;
            margin: 0;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            width: 1200px;
            height: 630px;
            background: #111;
            -webkit-print-color-adjust: exact;
            print-color-adjust: exact;
            overflow: hidden;
        }

        .card {
            width: 1200px;
            height: 630px;
            position: relative;
            overflow: hidden;
        }

        /* ── Background gradient ── */
        .bg-layer {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #1a1a1a 0%, #111111 50%, #0d0d0d 100%);
        }

        /* ── Decorative accent circle ── */
        .accent-circle {
            position: absolute;
            width: 500px;
            height: 500px;
            border-radius: 50%;
            background: rgba(249, 115, 22, 0.06);
            top: -150px;
            right: -100px;
        }

        .accent-circle-2 {
            position: absolute;
            width: 300px;
            height: 300px;
            border-radius: 50%;
            background: rgba(249, 115, 22, 0.04);
            bottom: -100px;
            left: -50px;
        }

        /* ── Main layout using table ── */
        .layout {
            width: 100%;
            height: 100%;
            border-collapse: collapse;
            position: relative;
            z-index: 10;
        }

        .layout td {
            vertical-align: middle;
            border: none;
        }

        /* ── Left side: Info ── */
        .left-col {
            width: 55%;
            padding: 60px 40px 60px 70px;
        }

        .badge {
            display: inline-block;
            background: rgba(249, 115, 22, 0.15);
            border: 1px solid rgba(249, 115, 22, 0.3);
            color: #f97316;
            font-size: 12px;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 2px;
            padding: 6px 16px;
            border-radius: 20px;
            margin-bottom: 20px;
        }

        .restaurant-name {
            font-size: 42px;
            font-weight: 900;
            color: #ffffff;
            line-height: 1.15;
            margin-bottom: 16px;
            max-width: 500px;
        }

        .tagline {
            font-size: 18px;
            color: #999;
            line-height: 1.5;
            margin-bottom: 30px;
            max-width: 440px;
        }

        .cta-box {
            display: inline-block;
            background: #f97316;
            color: #fff;
            font-size: 16px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 1.5px;
            padding: 14px 32px;
            border-radius: 10px;
            margin-bottom: 24px;
        }

        .url-text {
            font-size: 14px;
            color: #666;
            font-family: 'Courier New', monospace;
            letter-spacing: 0.5px;
        }

        .url-text strong {
            color: #f97316;
        }

        /* ── Right side: QR ── */
        .right-col {
            width: 45%;
            padding: 60px 70px 60px 20px;
            text-align: center;
        }

        .qr-container {
            display: inline-block;
            background: #ffffff;
            padding: 24px;
            border-radius: 20px;
        }

        .qr-container img {
            width: 280px;
            height: 280px;
        }

        .scan-text {
            font-size: 14px;
            color: #888;
            margin-top: 16px;
            letter-spacing: 0.5px;
        }

        .scan-text strong {
            color: #fff;
            font-weight: 700;
        }

        /* ── Bottom bar ── */
        .bottom-bar {
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 5px;
            background: linear-gradient(90deg, #f97316 0%, #fb923c 50%, #f97316 100%);
            z-index: 20;
        }

        /* ── Logo area ── */
        .logo-area {
            position: absolute;
            bottom: 24px;
            right: 70px;
            z-index: 20;
        }

        .logo-area img {
            height: 28px;
            width: auto;
        }

        .powered-by {
            position: absolute;
            bottom: 28px;
            right: 70px;
            z-index: 20;
            font-size: 11px;
            color: #555;
            letter-spacing: 0.5px;
        }

        .powered-by strong {
            color: #f97316;
            font-weight: 700;
        }
    </style>
</head>
<body>
    <div class="card">
        <div class="bg-layer"></div>
        <div class="accent-circle"></div>
        <div class="accent-circle-2"></div>
        <div class="bottom-bar"></div>

        <table class="layout">
            <tr>
                {{-- Left: Restaurant info --}}
                <td class="left-col">
                    <div class="badge">Menu en ligne</div>
                    <div class="restaurant-name">{{ $restaurant->name }}</div>
                    <div class="tagline">
                        Scannez le QR code pour découvrir notre menu et commander directement depuis votre téléphone.
                    </div>
                    <div class="cta-box">Commander maintenant</div>
                    <br>
                    <span class="url-text">
                        <strong>menupro.ci</strong>/r/{{ $restaurant->slug }}
                    </span>
                </td>

                {{-- Right: QR Code --}}
                <td class="right-col">
                    <div class="qr-container">
                        <img src="data:image/svg+xml;base64,{{ $qrBase64 }}" alt="QR Code {{ $restaurant->name }}">
                    </div>
                    <div class="scan-text">
                        <strong>Scannez</strong> avec votre appareil photo
                    </div>
                </td>
            </tr>
        </table>

        <div class="powered-by">
            Propulsé par <strong>MenuPro</strong>
        </div>
    </div>
</body>
</html>
