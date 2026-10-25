<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ ($restaurant->name ?? 'Restaurant') . ' - Menu' }}</title>
    <meta name="description" content="{{ $restaurant->description ?? 'Découvrez notre menu et commandez en ligne' }}">
    <link rel="canonical" href="{{ url()->current() }}">

    {{-- PWA & Mobile Web App --}}
    <meta name="theme-color" content="{{ $restaurant->primary_color ?? '#f97316' }}">
    <meta name="apple-mobile-web-app-capable" content="yes">
    <meta name="apple-mobile-web-app-status-bar-style" content="default">
    <meta name="apple-mobile-web-app-title" content="{{ $restaurant->name ?? 'MenuPro' }}">
    <meta name="mobile-web-app-capable" content="yes">
    {{-- Manifest propre au restaurant : l'appli installée ouvre ce menu (et non l'accueil MenuPro) --}}
    <link rel="manifest" href="{{ (isset($restaurant) && !empty($restaurant->slug)) ? route('r.manifest', $restaurant->slug) : '/manifest.json' }}">

    {{-- Open Graph pour partage WhatsApp / réseaux sociaux --}}
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="{{ $restaurant->name ?? 'MenuPro' }} - Menu & Commande en ligne">
    <meta property="og:description" content="{{ $restaurant->description ?? 'Commandez en ligne — paiement Mobile Money' }}">
    <meta property="og:type" content="restaurant.restaurant">
    <meta property="og:locale" content="fr_CI">
    @if($restaurant->banner_path ?? false)
        <meta property="og:image" content="{{ $restaurant->banner_url }}">
    @elseif($restaurant->logo_path ?? false)
        <meta property="og:image" content="{{ Storage::url($restaurant->logo_path) }}">
    @endif

    {{-- Twitter Card --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $restaurant->name ?? 'MenuPro' }} - Menu en ligne">
    <meta name="twitter:description" content="{{ $restaurant->description ?? 'Commandez en ligne — paiement Mobile Money' }}">

    {{-- JSON-LD Structured Data --}}
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "Restaurant",
        "name": "{{ $restaurant->name ?? '' }}",
        "url": "{{ url()->current() }}",
        "description": "{{ $restaurant->description ?? '' }}",
        @if($restaurant->address ?? false)"address": {
            "@@type": "PostalAddress",
            "streetAddress": "{{ $restaurant->address }}",
            "addressLocality": "Abidjan",
            "addressCountry": "CI"
        },@endif
        @if($restaurant->phone ?? false)"telephone": "{{ $restaurant->phone }}",@endif
        @if($restaurant->logo_path ?? false)"image": "{{ Storage::url($restaurant->logo_path) }}",@endif
        "servesCuisine": "{{ $restaurant->cuisine_type ?? 'Cuisine africaine' }}",
        "priceRange": "$$",
        "acceptsReservations": "true",
        "hasMenu": {
            "@@type": "Menu",
            "url": "{{ url()->current() }}"
        }
    }
    </script>
    
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
    @else
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
        <link rel="shortcut icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    @endif
    {{-- Icône d'écran d'accueil iOS : PNG 180×180 opaque (le favicon, SVG ou trop petit, est ignoré/flou) --}}
    <link rel="apple-touch-icon" sizes="180x180" href="{{ asset('apple-touch-icon.png') }}">
    
    <!-- Fonts : preconnect + display=swap pour affichage immédiat du texte (évite FOIT sur réseau lent) -->
    <link rel="preconnect" href="https://fonts.bunny.net" crossorigin>
    <link href="https://fonts.bunny.net/css?family=dm-sans:400,500,600,700|playfair-display:400,500,600,700&display=swap" rel="stylesheet" />
    
    {{-- Service Worker — mode hors ligne + cache menu + notifications push (dans le <head> : l'invite
         d'installation Android doit être capturée avant le démarrage d'Alpine) --}}
    @include('partials.service-worker')

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
    @stack('head')
</head>
<body class="antialiased font-sans">
    {{ $slot }}
    
    @livewireScripts

    {{-- Smart PWA Install Banner — register component inline to avoid timing issues with Vite modules --}}
    <script>
    (function() {
        function registerPwa(Alpine) {
            Alpine.data('pwaSmartInstall', () => ({
            showBanner: false,
            iosSteps: false,
            showIosSteps: false,
            restaurantName: '',
            isIos() {
                return /iphone|ipad|ipod/i.test(navigator.userAgent)
                    || (navigator.platform === 'MacIntel' && navigator.maxTouchPoints > 1);
            },
            init() {
                if (window.matchMedia('(display-mode: standalone)').matches) return;
                if (window.navigator.standalone === true) return;
                let dismissed = null;
                try { dismissed = localStorage.getItem('pwa_smart_dismissed'); } catch (e) {}
                if (dismissed && Date.now() - parseInt(dismissed) < 14 * 24 * 3600 * 1000) return;
                this.restaurantName = this.$el.dataset.restaurant || '';
                this.iosSteps = this.isIos();
                // Invite Android capturée dès le <head> (partials/service-worker) : rien n'est manqué
                if (!window.__mpInstallPrompt) {
                    window.addEventListener('mp:installable', () => this._onEngagement(), { once: true });
                }
                window.addEventListener('mp:installed', () => { this.showBanner = false; });
                document.addEventListener('cart-updated', () => this._onEngagement());
                this._onEngagement();
            },
            _onEngagement() {
                if (this.showBanner) return;
                if (window.__mpInstallPrompt || this.iosSteps) {
                    setTimeout(() => { this.showBanner = true; }, 3000);
                }
            },
            async install() {
                const prompt = window.__mpInstallPrompt;
                if (prompt) {
                    try {
                        prompt.prompt();
                        const { outcome } = await prompt.userChoice;
                        if (outcome === 'accepted') this.showBanner = false; else this.dismiss();
                    } catch (e) { this.dismiss(); }
                    window.__mpInstallPrompt = null;
                } else if (this.iosSteps) {
                    this.showIosSteps = !this.showIosSteps;
                } else {
                    this.dismiss();
                }
            },
            dismiss() {
                this.showBanner = false;
                try { localStorage.setItem('pwa_smart_dismissed', Date.now().toString()); } catch (e) {}
            }
        }));
        }
        if (window.Alpine) { registerPwa(window.Alpine); }
        else { document.addEventListener('alpine:init', () => { registerPwa(window.Alpine); }); }
    })();
    </script>
    <div x-data="pwaSmartInstall()" x-show="showBanner" x-cloak
         data-restaurant="{{ $restaurant->name ?? '' }}"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="translate-y-full opacity-0"
         x-transition:enter-end="translate-y-0 opacity-100"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="translate-y-0 opacity-100"
         x-transition:leave-end="translate-y-full opacity-0"
         class="fixed bottom-0 inset-x-0 z-[100] p-3 pb-[max(0.75rem,env(safe-area-inset-bottom))]">
        <div class="max-w-md mx-auto bg-white rounded-2xl shadow-2xl border border-neutral-200/80 p-4 flex items-center gap-3">
            @if(isset($restaurant) && $restaurant->logo_path)
                <img src="{{ $restaurant->logo_url }}" alt="{{ $restaurant->name }}" class="w-12 h-12 rounded-xl object-cover flex-shrink-0">
            @else
                <div class="w-12 h-12 rounded-xl flex items-center justify-center flex-shrink-0" style="background: linear-gradient(135deg, {{ $restaurant->primary_color ?? '#f97316' }}, {{ $restaurant->secondary_color ?? '#1c1917' }});">
                    <span class="text-xl font-bold text-white">{{ strtoupper(substr($restaurant->name ?? 'M', 0, 1)) }}</span>
                </div>
            @endif
            <div class="flex-1 min-w-0">
                <p class="text-sm font-semibold text-neutral-900 truncate">Installer {{ $restaurant->name ?? 'ce restaurant' }}</p>
                <p class="text-xs text-neutral-500">Commander plus vite depuis l'accueil</p>
            </div>
            <button @click="install()" class="min-h-[44px] px-3.5 rounded-xl text-white text-sm font-semibold transition flex-shrink-0" style="background-color: {{ $restaurant->primary_color ?? '#f97316' }};">
                Installer
            </button>
            <button @click="dismiss()" aria-label="Plus tard" class="min-w-[44px] min-h-[44px] flex items-center justify-center text-neutral-400 hover:text-neutral-600 transition flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
        <div x-show="showIosSteps" x-cloak class="max-w-md mx-auto mt-2 bg-white rounded-2xl shadow-2xl border border-neutral-200/80 p-4 text-sm text-neutral-700">
            <p>1. Appuyez sur
                <svg class="inline w-5 h-5 -mt-1 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-label="Partager"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v12m0-12L8 7m4-4l4 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/></svg>
                <strong>Partager</strong> en bas de Safari</p>
            <p class="mt-1">2. Choisissez <strong>« Sur l'écran d'accueil »</strong> puis <strong>Ajouter</strong></p>
        </div>
    </div>


    @stack('scripts')
</body>
</html>
