@php
    $grade = $agent->grade;
    $referredCount = $agent->referredRestaurants()->count();
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <title>Carte Agent - {{ $agent->full_name }}</title>
    <style>
        * { box-sizing: border-box; }
        body { margin: 0; padding: 12px; font-family: DejaVu Sans, sans-serif; background: #0f172a; color: #fff; font-size: 11px; }
        .card { width: 100%; border: 1px solid #475569; border-radius: 12px; overflow: hidden; background: #1e293b; }
        table.layout { width: 100%; border-collapse: collapse; table-layout: fixed; }
        table.layout td { vertical-align: top; padding: 14px; border: none; }
        .col-left { width: 38%; text-align: center; background: #1e293b; border-right: 1px solid #334155; }
        .col-right { width: 62%; }
        .sys-ok { color: #34d399; font-size: 9px; text-transform: uppercase; letter-spacing: 0.08em; margin-bottom: 8px; }
        .photo-wrap { margin: 8px auto 10px; }
        .photo { width: 72px; height: 72px; border-radius: 50%; border: 2px solid #475569; display: block; margin: 0 auto; }
        .badge-id { background: #f97316; color: #fff; padding: 6px 10px; border-radius: 8px; font-size: 10px; font-weight: bold; letter-spacing: 0.08em; margin: 0 auto 8px; display: inline-block; }
        .access-line { font-size: 9px; color: #94a3b8; text-transform: uppercase; margin-bottom: 12px; }
        .access-line strong { color: #fff; }
        .qr-wrap { margin-top: 8px; }
        .qr-wrap img { width: 64px; height: 64px; display: block; margin: 0 auto; }
        .profil-title { color: #94a3b8; font-size: 9px; text-transform: uppercase; letter-spacing: 0.05em; margin-bottom: 4px; }
        .name { font-size: 16px; font-weight: bold; text-transform: uppercase; margin: 0 0 8px; color: #fff; }
        .badges-line { margin-bottom: 10px; }
        .badge-tag { display: inline-block; padding: 4px 8px; border-radius: 6px; font-size: 9px; margin-right: 6px; background: #334155; border: 1px solid #475569; color: #fff; }
        .metrics-table { width: 100%; margin-bottom: 10px; border-collapse: collapse; }
        .metrics-table td { width: 33.33%; text-align: center; padding: 8px; background: #0f172a; border: 1px solid rgba(249,115,22,0.3); border-radius: 6px; }
        .metric-label { font-size: 8px; color: #64748b; text-transform: uppercase; }
        .metric-value { font-size: 12px; font-weight: bold; color: #fff; }
        .metric-value.orange { color: #fb923c; }
        .metric-value.green { color: #34d399; }
        .verified { font-size: 9px; color: #64748b; margin-bottom: 10px; }
        .actions-row { margin-top: 10px; padding-top: 8px; border-top: 1px solid #334155; }
        .btn-row { font-size: 9px; }
        .btn-pill { display: inline-block; background: #f97316; color: #fff; padding: 6px 12px; border-radius: 8px; margin-right: 6px; font-weight: bold; }
        .btn-ico { display: inline-block; background: #334155; color: #fff; padding: 6px; border-radius: 8px; margin-right: 4px; border: 1px solid #475569; }
        .footer { padding: 8px 14px; text-align: center; background: #1e293b; border-top: 1px solid #334155; font-size: 10px; color: #f97316; }
    </style>
</head>
<body>
    <div class="card">
        <table class="layout">
            <tr>
                <td class="col-left">
                    <div class="sys-ok">● Système OK</div>
                    <div class="photo-wrap">
                        <img src="{{ $photoUrl }}" alt="" class="photo">
                    </div>
                    <div class="badge-id">{{ $agent->badge_id_display }}</div>
                    <div class="access-line">Niveau d'accès <strong>Niveau {{ $grade->accessLevel() }}</strong></div>
                    <div class="qr-wrap">
                        <img src="data:image/svg+xml;base64,{{ $qrSvgBase64 }}" alt="QR" width="64" height="64">
                    </div>
                </td>
                <td class="col-right">
                    <div class="profil-title">Profil Commando</div>
                    <h1 class="name">{{ $agent->full_name }}</h1>
                    <div class="badges-line">
                        <span class="badge-tag">⚡ {{ $grade->rankTitle() }}</span>
                        <span class="badge-tag">État vital : En opération</span>
                    </div>
                    <table class="metrics-table">
                        <tr>
                            <td><div class="metric-label">Missions</div><div class="metric-value">{{ $referredCount }}</div></td>
                            <td><div class="metric-label">Rang</div><div class="metric-value orange">{{ $grade->rankLetter() }}</div></td>
                            <td><div class="metric-label">Fiabilité</div><div class="metric-value green">100%</div></td>
                        </tr>
                    </table>
                    <div class="verified">✓ Agent officiel vérifié MenuPro CI</div>
                    <div class="actions-row">
                        <span class="btn-pill">📞 Établir liaison</span>
                        <span class="btn-ico">WhatsApp</span>
                        <span class="btn-ico">Partager</span>
                    </div>
                </td>
            </tr>
        </table>
        <div class="footer">menupro.ci/verify/{{ $agent->uuid }}</div>
    </div>
</body>
</html>
