<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ ($restaurant->name ?? 'Restaurant') . ' - Menu' }}</title>
    <meta name="description" content="{{ $restaurant->description ?? 'Découvrez notre menu et commandez en ligne' }}">

    {{-- PWA & Mobile Web App --}}
    <meta name="theme-color" content="{{ $restaurant->primary_color ?? '#f97316' }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="{{ $restaurant->name ?? 'MenuPro' }}">
    <meta name="mobile-web-app-capable" content="yes">
    <link rel="manifest" href="/manifest.json">

    {{-- Open Graph pour partage WhatsApp / réseaux sociaux --}}
    <meta property="og:title" content="{{ $restaurant->name ?? 'MenuPro' }}">
    <meta property="og:description" content="{{ $restaurant->description ?? 'Commandez en ligne — paiement Mobile Money' }}">
    <meta property="og:type" content="restaurant.restaurant">
    @if($restaurant->banner_path ?? false)
        <meta property="og:image" content="{{ $restaurant->banner_url }}">
    @endif
    
    @php
        $favicon = \App\Models\SystemSetting::get('favicon', '');
        $faviconUrl = null;
        $faviconType = 'image/png';
        
        if (!empty($favicon)) {
            try {
                $storage = \Illuminate\Support\Facades\Storage::disk('public');
                
                if ($storage->exists($favicon)) {
                    // Use request URL to get current scheme and host (works with any domain/IP)
                    $baseUrl = request()->getSchemeAndHttpHost();
                    $faviconUrl = $baseUrl . '/storage/' . $favicon;
                    
                    $extension = strtolower(pathinfo($favicon, PATHINFO_EXTENSION));
                    $faviconType = match($extension) {
                        'ico' => 'image/x-icon',
                        'svg' => 'image/svg+xml',
                        'jpg', 'jpeg' => 'image/jpeg',
                        'gif' => 'image/gif',
                        default => 'image/png'
                    };
                    
                    $faviconUrl .= '?v=' . $storage->lastModified($favicon);
                }
            } catch (\Exception $e) {
                \Log::error('Favicon error: ' . $e->getMessage());
            }
        }
    @endphp
    
    @if($faviconUrl)
        <link rel="icon" type="{{ $faviconType }}" href="{{ $faviconUrl }}">
        <link rel="shortcut icon" type="{{ $faviconType }}" href="{{ $faviconUrl }}">
        <link rel="apple-touch-icon" href="{{ $faviconUrl }}">
    @else
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
        <link rel="shortcut icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    @endif
    
    <!-- Fonts : preconnect + display=swap pour affichage immédiat du texte (évite FOIT sur réseau lent) -->
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link href="https://fonts.bunny.net/css?family=dm-sans:400,500,600,700|playfair-display:400,500,600,700&display=swap" rel="stylesheet" />
    
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    @livewireStyles
    
    <style>
        [x-cloak] { display: none !important; }
        .font-display { font-family: 'Playfair Display', serif; }
        .font-sans { font-family: 'DM Sans', sans-serif; }
        .scrollbar-hide::-webkit-scrollbar { display: none; }
        .scrollbar-hide { -ms-overflow-style: none; scrollbar-width: none; }

        /* Animations fluides */
        @media (prefers-reduced-motion: no-preference) {
            .animate-fade-in {
                animation: fadeIn .3s ease-out both;
            }
            @keyframes fadeIn {
                from { opacity: 0; transform: translateY(8px); }
                to   { opacity: 1; transform: translateY(0);   }
            }
            @keyframes fadeInUp {
                from { opacity: 0; transform: translateY(16px); }
                to   { opacity: 1; transform: translateY(0);    }
            }
        }
        @media (prefers-reduced-motion: reduce) {
            @keyframes fadeInUp {
                from { opacity: 1; }
                to   { opacity: 1; }
            }
        }

        /* Smooth scroll natif */
        html { scroll-behavior: smooth; }

        /* Tap highlight supprimé sur mobile */
        * { -webkit-tap-highlight-color: transparent; }
    </style>
</head>
<body class="antialiased font-sans">
    {{ $slot }}
    
    @livewireScripts

    {{-- Service Worker — mode hors ligne + cache menu --}}
    <script>
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js', { scope: '/' })
                    .then(reg => console.debug('[SW] Enregistré', reg.scope))
                    .catch(err => console.debug('[SW] Échec', err));
            });
        }
    </script>

    @stack('scripts')
</body>
</html>
