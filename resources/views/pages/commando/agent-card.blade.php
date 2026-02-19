@php
    $embed = request()->query('embed');
    $downloadPng = request()->query('download') === 'png';
    $grade = $agent->grade;
    $glow = $grade->glowColor();
    $referredCount = $agent->referredRestaurants()->count();
@endphp
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Carte Agent - {{ $agent->full_name }}</title>
    @if($embed)
        {{-- En mode iframe (embed) : aucun @vite (injecte des scripts en dev), styles inline uniquement --}}
        <style>
            *, *::before, *::after { box-sizing: border-box; }
            body { margin: 0; padding: 0.5rem; background: #0f172a; color: #fff; font-family: system-ui, sans-serif; font-size: 16px; display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 100vh; }
            .badge-card {
                background: linear-gradient(165deg, #0f172a 0%, #1e293b 40%, #0f172a 100%);
                border: 1px solid rgba(71, 85, 105, 0.4);
                box-shadow: 0 0 0 1px rgba(249, 115, 22, 0.15), 0 25px 50px -12px rgba(0,0,0,0.5);
                border-radius: 1.5rem; overflow: hidden; width: 100%; min-width: 320px; max-width: 56rem;
                display: flex; flex-direction: column;
                aspect-ratio: 16/11; min-height: 400px; max-height: 90vh; color: #fff;
            }
            .badge-card > .card-inner { display: flex; flex-direction: row; flex: 1 1 0%; min-height: 280px; }
            .badge-card .photo-glow { box-shadow: 0 0 24px {{ $glow }}, 0 0 48px {{ $glow }}, inset 0 0 0 3px {{ $glow }}; }
            .badge-qr { filter: invert(1); }
            .badge-card .flex { display: flex; }
            .badge-card .flex-row { flex-direction: row; }
            .badge-card .flex-col { flex-direction: column; }
            .badge-card .flex-1 { flex: 1 1 0%; }
            .badge-card .flex-wrap { flex-wrap: wrap; }
            .badge-card .items-center { align-items: center; }
            .badge-card .justify-between { justify-content: space-between; }
            .badge-card .justify-center { justify-content: center; }
            .badge-card .w-\[40\%\] { width: 40%; min-width: 140px; max-width: 200px; }
            .badge-card .min-w-0 { min-width: 0; }
            .badge-card .min-w-\[120px\] { min-width: 120px; }
            .badge-card .p-4 { padding: 1rem; }
            .badge-card .shrink-0 { flex-shrink: 0; }
            .badge-card .border-r { border-right: 1px solid rgba(71, 85, 105, 0.5); }
            .badge-card .border-t { border-top: 1px solid rgba(71, 85, 105, 0.5); }
            .badge-card .rounded-xl { border-radius: 0.75rem; }
            .badge-card .rounded-full { border-radius: 9999px; }
            .badge-card .bg-slate-800\/30 { background-color: rgba(30, 41, 59, 0.3); }
            .badge-card .bg-orange-500 { background-color: #f97316; }
            .badge-card .bg-slate-800\/80 { background-color: rgba(30, 41, 59, 0.8); }
            .badge-card .bg-white { background-color: #fff; }
            .badge-card .bg-white\/10 { background-color: rgba(255, 255, 255, 0.1); }
            .badge-card .bg-slate-700\/80 { background-color: rgba(51, 65, 85, 0.8); }
            .badge-card .border-slate-500 { border-color: #64748b; }
            .badge-card .border-slate-600\/50 { border-color: rgba(71, 85, 105, 0.5); }
            .badge-card .border-slate-600 { border: 1px solid #475569; }
            .badge-card .p-2 { padding: 8px; }
            .badge-card .border-orange-500\/30 { border: 1px solid rgba(249, 115, 22, 0.3); }
            .badge-card .border-slate-700\/50 { border-color: rgba(51, 65, 85, 0.5); }
            .badge-card .text-white { color: #fff; }
            .badge-card .text-slate-400 { color: #94a3b8; }
            .badge-card .text-slate-500 { color: #64748b; }
            .badge-card .text-emerald-400 { color: #34d399; }
            .badge-card .text-emerald-500 { color: #10b981; }
            .badge-card .text-orange-400 { color: #fb923c; }
            .badge-card .text-orange-500 { color: #f97316; }
            .badge-card .text-\[10px\] { font-size: 10px; }
            .badge-card .text-\[9px\] { font-size: 9px; }
            .badge-card .text-xs { font-size: 12px; }
            .badge-card .text-sm { font-size: 14px; }
            .badge-card .text-lg { font-size: 18px; }
            .badge-card .font-bold { font-weight: 700; }
            .badge-card .font-semibold { font-weight: 600; }
            .badge-card .uppercase { text-transform: uppercase; }
            .badge-card .tracking-wider { letter-spacing: 0.05em; }
            .badge-card .tracking-widest { letter-spacing: 0.1em; }
            .badge-card .w-20 { width: 5rem; }
            .badge-card .h-20 { height: 5rem; }
            .badge-card .w-\[72px\] { width: 72px; }
            .badge-card .h-\[72px\] { height: 72px; }
            .badge-card .w-\[80px\] { width: 80px; }
            .badge-card .h-\[80px\] { height: 80px; }
            .badge-card .grid { display: grid; }
            .badge-card .grid-cols-3 { grid-template-columns: repeat(3, 1fr); }
            .badge-card .w-full { width: 100%; }
            .badge-card .gap-1 { gap: 0.25rem; }
            .badge-card .gap-2 { gap: 0.5rem; }
            .badge-card .px-2 { padding-left: 0.5rem; padding-right: 0.5rem; }
            .badge-card .py-1\.5 { padding-top: 0.375rem; padding-bottom: 0.375rem; }
            .badge-card .py-2 { padding-top: 0.5rem; padding-bottom: 0.5rem; }
            .badge-card .py-2\.5 { padding-top: 0.625rem; padding-bottom: 0.625rem; }
            .badge-card .py-3 { padding-top: 0.75rem; padding-bottom: 0.75rem; }
            .badge-card .rounded-lg { border-radius: 0.5rem; }
            .badge-card .mb-2 { margin-bottom: 0.5rem; }
            .badge-card .mb-1 { margin-bottom: 0.25rem; }
            .badge-card .mt-2 { margin-top: 0.5rem; }
            .badge-card .pt-2 { padding-top: 0.5rem; }
            .badge-card .text-center { text-align: center; }
            .badge-card .truncate { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
            .badge-card .name-card { word-break: break-word; overflow-wrap: break-word; line-height: 1.25; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
            .badge-card .block { display: block; }
            .badge-card .object-cover { object-fit: cover; }
            .badge-card .object-top { object-position: top; }
            .badge-card .min-h-photo { min-height: 80px; }
            .badge-card .hidden { display: none !important; }
            /* Taille des icônes SVG (évite la silhouette géante en embed) */
            .badge-card .w-3\.5, .badge-card .h-3\.5 { width: 14px; height: 14px; }
            .badge-card .w-3, .badge-card .h-3 { width: 12px; height: 12px; }
            .badge-card .w-4, .badge-card .h-4 { width: 16px; height: 16px; }
            .badge-card .w-5, .badge-card .h-5 { width: 20px; height: 20px; }
            .badge-card .border-2 { border-width: 2px; }
            .badge-card .border-orange-400\/50 { border-color: rgba(251, 146, 60, 0.5); }
            .badge-card .shadow-md { box-shadow: 0 4px 6px -1px rgba(0,0,0,0.3), 0 2px 4px -2px rgba(0,0,0,0.2); }
            .badge-card .shadow-lg { box-shadow: 0 10px 15px -3px rgba(0,0,0,0.3), 0 4px 6px -4px rgba(0,0,0,0.2); }
            .badge-card svg { flex-shrink: 0; vertical-align: middle; }
            /* Colonne droite : pas de débordement, contenu lisible */
            .badge-card .flex-col.justify-between { overflow: visible; }
            .badge-card .mb-0\.5 { margin-bottom: 0.125rem; }
            .badge-card .py-0\.5 { padding-top: 0.125rem; padding-bottom: 0.125rem; }
            .badge-card .inline-flex { display: inline-flex; }
            .badge-card .gap-1 { gap: 0.25rem; }
            .badge-card .tracking-tight { letter-spacing: -0.025em; }
            .badge-card a { color: inherit; text-decoration: none; }
            .badge-card button { font: inherit; color: inherit; cursor: pointer; border: none; background: none; }
        </style>
    @elseif($downloadPng)
        {{-- Mode capture PNG : pas de Tailwind (oklab) pour que html2canvas fonctionne --}}
        <style>
            *, *::before, *::after { box-sizing: border-box; }
            body { margin: 0; padding: 1rem; background: #0f172a; color: #fff; font-family: system-ui, sans-serif; font-size: 16px; display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 100vh; }
            .badge-card {
                background: linear-gradient(165deg, #0f172a 0%, #1e293b 40%, #0f172a 100%);
                border: 1px solid rgba(71, 85, 105, 0.4);
                box-shadow: 0 0 0 1px rgba(249, 115, 22, 0.15), 0 25px 50px -12px rgba(0,0,0,0.5);
                border-radius: 1.5rem; overflow: hidden; width: 100%; min-width: 320px; max-width: 56rem;
                display: flex; flex-direction: column;
                aspect-ratio: 16/11; min-height: 400px; max-height: 90vh; color: #fff;
            }
            .badge-card > .card-inner { display: flex; flex-direction: row; flex: 1 1 0%; min-height: 280px; }
            .badge-card .photo-glow { box-shadow: 0 0 24px {{ $glow }}, 0 0 48px {{ $glow }}, inset 0 0 0 3px {{ $glow }}; }
            .badge-qr { filter: invert(1); }
            .badge-card .flex { display: flex; }
            .badge-card .flex-row { flex-direction: row; }
            .badge-card .flex-col { flex-direction: column; }
            .badge-card .flex-1 { flex: 1 1 0%; }
            .badge-card .flex-wrap { flex-wrap: wrap; }
            .badge-card .items-center { align-items: center; }
            .badge-card .justify-between { justify-content: space-between; }
            .badge-card .justify-center { justify-content: center; }
            .badge-card .w-\[40\%\] { width: 40%; min-width: 140px; max-width: 200px; }
            .badge-card .min-w-0 { min-width: 0; }
            .badge-card .min-w-\[120px\] { min-width: 120px; }
            .badge-card .p-4 { padding: 1rem; }
            .badge-card .shrink-0 { flex-shrink: 0; }
            .badge-card .border-r { border-right: 1px solid rgba(71, 85, 105, 0.5); }
            .badge-card .border-t { border-top: 1px solid rgba(71, 85, 105, 0.5); }
            .badge-card .rounded-xl { border-radius: 0.75rem; }
            .badge-card .rounded-full { border-radius: 9999px; }
            .badge-card .bg-slate-800\/30 { background-color: rgba(30, 41, 59, 0.3); }
            .badge-card .bg-orange-500 { background-color: #f97316; }
            .badge-card .bg-slate-800\/80 { background-color: rgba(30, 41, 59, 0.8); }
            .badge-card .bg-white { background-color: #fff; }
            .badge-card .bg-white\/10 { background-color: rgba(255, 255, 255, 0.1); }
            .badge-card .bg-slate-700\/80 { background-color: rgba(51, 65, 85, 0.8); }
            .badge-card .border-slate-500 { border-color: #64748b; }
            .badge-card .border-slate-600\/50 { border-color: rgba(71, 85, 105, 0.5); }
            .badge-card .border-slate-600 { border: 1px solid #475569; }
            .badge-card .p-2 { padding: 8px; }
            .badge-card .border-orange-500\/30 { border: 1px solid rgba(249, 115, 22, 0.3); }
            .badge-card .border-slate-700\/50 { border-color: rgba(51, 65, 85, 0.5); }
            .badge-card .text-white { color: #fff; }
            .badge-card .text-slate-400 { color: #94a3b8; }
            .badge-card .text-slate-500 { color: #64748b; }
            .badge-card .text-emerald-400 { color: #34d399; }
            .badge-card .text-emerald-500 { color: #10b981; }
            .badge-card .text-orange-400 { color: #fb923c; }
            .badge-card .text-orange-500 { color: #f97316; }
            .badge-card .text-\[10px\] { font-size: 10px; }
            .badge-card .text-\[9px\] { font-size: 9px; }
            .badge-card .text-xs { font-size: 12px; }
            .badge-card .text-sm { font-size: 14px; }
            .badge-card .text-lg { font-size: 18px; }
            .badge-card .font-bold { font-weight: 700; }
            .badge-card .font-semibold { font-weight: 600; }
            .badge-card .uppercase { text-transform: uppercase; }
            .badge-card .tracking-wider { letter-spacing: 0.05em; }
            .badge-card .tracking-widest { letter-spacing: 0.1em; }
            .badge-card .w-20 { width: 5rem; }
            .badge-card .h-20 { height: 5rem; }
            .badge-card .w-\[72px\] { width: 72px; }
            .badge-card .h-\[72px\] { height: 72px; }
            .badge-card .w-\[80px\] { width: 80px; }
            .badge-card .h-\[80px\] { height: 80px; }
            .badge-card .grid { display: grid; }
            .badge-card .grid-cols-3 { grid-template-columns: repeat(3, 1fr); }
            .badge-card .w-full { width: 100%; }
            .badge-card .gap-1 { gap: 0.25rem; }
            .badge-card .gap-2 { gap: 0.5rem; }
            .badge-card .px-2 { padding-left: 0.5rem; padding-right: 0.5rem; }
            .badge-card .py-1\.5 { padding-top: 0.375rem; padding-bottom: 0.375rem; }
            .badge-card .py-2 { padding-top: 0.5rem; padding-bottom: 0.5rem; }
            .badge-card .py-2\.5 { padding-top: 0.625rem; padding-bottom: 0.625rem; }
            .badge-card .py-3 { padding-top: 0.75rem; padding-bottom: 0.75rem; }
            .badge-card .rounded-lg { border-radius: 0.5rem; }
            .badge-card .mb-2 { margin-bottom: 0.5rem; }
            .badge-card .mb-1 { margin-bottom: 0.25rem; }
            .badge-card .mt-2 { margin-top: 0.5rem; }
            .badge-card .pt-2 { padding-top: 0.5rem; }
            .badge-card .text-center { text-align: center; }
            .badge-card .truncate { overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
            .badge-card .name-card { word-break: break-word; overflow-wrap: break-word; line-height: 1.25; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
            .badge-card .block { display: block; }
            .badge-card .object-cover { object-fit: cover; }
            .badge-card .object-top { object-position: top; }
            .badge-card .min-h-photo { min-height: 80px; }
            .badge-card .hidden { display: none !important; }
            .badge-card .w-3\.5, .badge-card .h-3\.5 { width: 14px; height: 14px; }
            .badge-card .w-3, .badge-card .h-3 { width: 12px; height: 12px; }
            .badge-card .w-4, .badge-card .h-4 { width: 16px; height: 16px; }
            .badge-card .w-5, .badge-card .h-5 { width: 20px; height: 20px; }
            .badge-card .border-2 { border-width: 2px; }
            .badge-card .border-orange-400\/50 { border-color: rgba(251, 146, 60, 0.5); }
            .badge-card .shadow-md { box-shadow: 0 4px 6px -1px rgba(0,0,0,0.3), 0 2px 4px -2px rgba(0,0,0,0.2); }
            .badge-card .shadow-lg { box-shadow: 0 10px 15px -3px rgba(0,0,0,0.3), 0 4px 6px -4px rgba(0,0,0,0.2); }
            .badge-card svg { flex-shrink: 0; vertical-align: middle; }
            .badge-card .mb-0\.5 { margin-bottom: 0.125rem; }
            .badge-card .py-0\.5 { padding-top: 0.125rem; padding-bottom: 0.125rem; }
            .badge-card .inline-flex { display: inline-flex; }
            .badge-card .gap-1 { gap: 0.25rem; }
            .badge-card .tracking-tight { letter-spacing: -0.025em; }
            .badge-card a { color: inherit; text-decoration: none; }
            .badge-card button { font: inherit; color: inherit; cursor: pointer; border: none; background: none; }
        </style>
    @else
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    @if(!$downloadPng)
    <style>
        .badge-card {
            background: linear-gradient(165deg, #0f172a 0%, #1e293b 40%, #0f172a 100%);
            border: 1px solid rgba(71, 85, 105, 0.4);
            box-shadow: 0 0 0 1px rgba(249, 115, 22, 0.15), 0 25px 50px -12px rgba(0,0,0,0.5);
            display: flex;
            flex-direction: column;
            min-width: 320px;
            min-height: 400px;
            aspect-ratio: 16 / 11;
        }
        .badge-card > .card-inner {
            flex: 1 1 0%;
            min-height: 280px;
        }
        .photo-glow {
            box-shadow: 0 0 24px {{ $glow }}, 0 0 48px {{ $glow }}, inset 0 0 0 3px {{ $glow }};
        }
        .badge-qr { filter: invert(1); }
        .name-card { word-break: break-word; overflow-wrap: break-word; line-height: 1.25; display: -webkit-box; -webkit-line-clamp: 2; -webkit-box-orient: vertical; overflow: hidden; }
        @media print {
            @page { size: landscape; margin: 12mm; }
            body { padding: 0; }
        }
    </style>
    @endif
</head>
<body class="{{ $downloadPng ? '' : 'bg-[#0f172a] min-h-screen flex flex-col ' . ($embed ? 'p-2' : 'p-4') }} print:p-0 print:bg-slate-900 print:min-h-0">
    {{-- Barre d'actions : masquée en mode iframe (embed), visible en plein écran --}}
    @if(!$embed)
    <header class="mb-4 print:hidden">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 rounded-xl bg-sky-500/20 flex items-center justify-center shrink-0">
                <svg class="w-5 h-5 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
            </div>
            <div>
                <h1 class="font-semibold text-white">Ma carte agent</h1>
                <p class="text-slate-400 text-sm">Carte digitale vérifiable (QR)</p>
            </div>
        </div>
        <div class="mt-3 flex flex-wrap items-center gap-2">
            <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-lg bg-emerald-500/20 text-emerald-400 border border-emerald-500/40 text-xs font-medium">
                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                Agent vérifié
            </span>
            <a href="{{ route('commando.dashboard') }}" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl border border-slate-600 bg-slate-800 hover:bg-slate-700 text-slate-200 text-sm font-medium transition whitespace-nowrap">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                Retour au tableau de bord
            </a>
            <a href="{{ route('commando.card') }}" target="_blank" rel="noopener" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium transition whitespace-nowrap">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                Ouvrir en plein écran
            </a>
        </div>
    </header>
    @endif

    {{-- Format paysage : ratio 16/10, disposition fixe --}}
    <div class="flex-1 flex items-center justify-center min-h-0">
        <div class="badge-card w-full min-w-[320px] max-w-4xl rounded-3xl overflow-hidden text-white print:max-w-none print:shadow-none print:rounded-xl flex flex-col" style="aspect-ratio: 16/11; min-height: 400px; max-height: 90vh;">
        <div class="card-inner flex flex-row flex-1 min-h-0 min-h-[280px]">
            {{-- Colonne gauche : identité + QR (40%) --}}
            <div class="w-[40%] min-w-[140px] max-w-[200px] p-4 flex flex-col items-center justify-between border-r border-slate-700/50 bg-slate-800/30 shrink-0">
                <div class="w-full flex flex-col items-center">
                    <div class="flex items-center gap-2 self-start mb-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                        <span class="text-emerald-400 text-[10px] font-semibold uppercase tracking-wider">Système OK</span>
                    </div>
                    <div class="relative my-2">
                        <img src="{{ $agent->photo_url }}" alt="{{ $agent->full_name }}" class="w-20 h-20 rounded-full object-cover object-top photo-glow border-2 border-slate-600/50 block">
                    </div>
                    <div class="w-full rounded-xl bg-orange-500 px-2 py-1.5 text-center mb-2">
                        <span class="font-mono font-bold text-xs tracking-widest text-white">{{ $agent->badge_id_display }}</span>
                    </div>
                    <div class="w-full flex justify-between items-center text-[10px] text-slate-400">
                        <span class="uppercase tracking-wider">Niveau d'accès</span>
                        <span class="text-white font-bold">Niveau {{ $grade->accessLevel() }}</span>
                    </div>
                </div>
                <div class="w-full flex justify-center mt-2">
                    <div class="bg-white p-2 rounded-xl border-2 border-slate-500 shadow-lg w-[80px] h-[80px] flex items-center justify-center overflow-hidden">
                        <div class="[&_svg]:w-full [&_svg]:h-full [&_svg]:block" style="color: #0f172a;">
                            {!! $qrSvg !!}
                        </div>
                    </div>
                </div>
            </div>

            {{-- Colonne droite : profil + métriques + actions (60%) --}}
            <div class="flex-1 min-w-0 p-4 flex flex-col justify-between">
                <div>
                    <div class="flex items-center gap-2 text-slate-400 mb-0.5">
                        <svg class="w-3.5 h-3.5 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        <span class="text-[10px] font-semibold uppercase tracking-wider">Profil Commando</span>
                    </div>
                    <h1 class="text-lg font-bold text-white uppercase tracking-tight mb-1 name-card">{{ $agent->full_name }}</h1>
                    <div class="flex flex-wrap items-center gap-2 mb-2">
                        <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-slate-700/80 border border-slate-600 text-[10px] font-semibold text-white shrink-0">
                            <svg class="w-3 h-3 text-orange-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            {{ $grade->rankTitle() }}
                        </span>
                        <span class="rounded-lg bg-slate-700/80 border border-slate-600 px-2 py-1 shrink-0">
                            <span class="text-slate-500 text-[9px] uppercase block">État vital</span>
                            <span class="text-emerald-400 text-[10px] font-bold">En opération</span>
                        </span>
                    </div>

                    <div class="grid grid-cols-3 gap-2 mb-2">
                        <div class="rounded-xl bg-slate-800/80 border border-orange-500/30 p-2 text-center min-w-0">
                            <span class="text-slate-500 text-[9px] uppercase block">Missions</span>
                            <span class="text-sm font-bold text-white">{{ $referredCount }}</span>
                        </div>
                        <div class="rounded-xl bg-slate-800/80 border border-orange-500/30 p-2 text-center min-w-0">
                            <span class="text-slate-500 text-[9px] uppercase block">Rang</span>
                            <span class="text-sm font-bold text-orange-400">{{ $grade->rankLetter() }}</span>
                        </div>
                        <div class="rounded-xl bg-slate-800/80 border border-orange-500/30 p-2 text-center min-w-0">
                            <span class="text-slate-500 text-[9px] uppercase block">Fiabilité</span>
                            <span class="text-sm font-bold text-emerald-400">100%</span>
                        </div>
                    </div>

                    <p class="text-slate-500 text-[10px] flex items-center gap-1">
                        <svg class="w-3 h-3 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                        Agent officiel vérifié MenuPro CI
                    </p>
                </div>

                {{-- Boutons d'action --}}
                <div class="flex flex-wrap items-center gap-2 pt-2 border-t border-slate-700/50 mt-2">
                    <a href="tel:{{ $agent->whatsapp }}" class="flex-1 min-w-[120px] inline-flex items-center justify-center gap-2 py-3 rounded-xl bg-orange-500 hover:bg-orange-600 text-white text-sm font-bold shadow-md border-2 border-orange-400/50 transition">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        Établir liaison
                    </a>
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $agent->whatsapp) }}" target="_blank" rel="noopener"
                       class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-slate-700 hover:bg-slate-600 text-white border border-slate-600 transition">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                    </a>
                    <button type="button" onclick="navigator.share && navigator.share({ title: '{{ addslashes($agent->full_name) }} - MenuPro Commando', url: '{{ $agent->verify_url }}', text: 'Carte agent MenuPro' })" class="inline-flex items-center justify-center w-10 h-10 rounded-xl bg-slate-700 hover:bg-slate-600 text-white border border-slate-600 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                    </button>
                </div>
            </div>
        </div>
        <div class="shrink-0 px-4 py-2.5 bg-slate-800/80 border-t border-slate-600 text-center">
            <span class="text-orange-500 font-mono text-xs">menupro.ci/verify/{{ $agent->uuid }}</span>
        </div>
        </div>
    </div>
    @if(request()->query('print'))
        <script>
            window.addEventListener('load', function() {
                window.print();
            });
        </script>
    @endif
    @if(request()->query('download') === 'png')
        <div id="png-download-zone" class="fixed bottom-4 left-1/2 -translate-x-1/2 z-50 flex flex-col items-center gap-2 print:hidden">
            <p class="text-slate-400 text-sm">Cliquez pour télécharger l'image de la carte :</p>
            <button type="button" id="png-download-btn" class="px-5 py-3 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-semibold shadow-lg transition flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Télécharger l'image (PNG)
            </button>
        </div>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js" crossorigin="anonymous"></script>
        <script>
            (function() {
                var filename = 'carte-agent-{{ \Illuminate\Support\Str::slug($agent->full_name) }}.png';
                function doCapture() {
                    var el = document.querySelector('.badge-card');
                    if (!el) { alert('Carte introuvable.'); return; }
                    if (typeof html2canvas === 'undefined') { alert('Chargement en cours, réessayez.'); return; }
                    var btn = document.getElementById('png-download-btn');
                    if (btn) { btn.disabled = true; btn.textContent = 'Génération…'; }
                    html2canvas(el, { scale: 2, useCORS: true, allowTaint: true, backgroundColor: '#0f172a', logging: false })
                        .then(function(canvas) {
                            var link = document.createElement('a');
                            link.download = filename;
                            link.href = canvas.toDataURL('image/png');
                            link.click();
                            if (btn) { btn.disabled = false; btn.innerHTML = '<svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg> Télécharger l\'image (PNG)'; }
                        })
                        .catch(function(err) {
                            if (btn) { btn.disabled = false; btn.textContent = 'Télécharger l\'image (PNG)'; }
                            console.error(err);
                            alert('Erreur lors de la génération. Réessayez ou utilisez le PDF.');
                        });
                }
                document.getElementById('png-download-btn').addEventListener('click', doCapture);
                window.addEventListener('load', function() {
                    setTimeout(doCapture, 400);
                });
            })();
        </script>
    @endif
</body>
</html>
