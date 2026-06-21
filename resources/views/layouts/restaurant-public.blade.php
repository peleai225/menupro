<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
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
    <link rel="manifest" href="/manifest.json">

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
            deferredPrompt: null,
            restaurantName: '',
            init() {
                if (window.matchMedia('(display-mode: standalone)').matches) return;
                if (window.navigator.standalone === true) return;
                const dismissed = localStorage.getItem('pwa_smart_dismissed');
                if (dismissed && Date.now() - parseInt(dismissed) < 14 * 24 * 3600 * 1000) return;
                this.restaurantName = this.$el.dataset.restaurant || '';
                window.addEventListener('beforeinstallprompt', (e) => {
                    e.preventDefault();
                    this.deferredPrompt = e;
                    this._checkTrigger();
                });
                if (/iphone|ipad|ipod/.test(navigator.userAgent.toLowerCase())) this._checkTrigger();
                document.addEventListener('cart-updated', () => this._onEngagement());
                this._trackVisit();
            },
            _trackVisit() {
                const key = 'pwa_visits_' + (this.restaurantName || 'global');
                const count = parseInt(localStorage.getItem(key) || '0') + 1;
                localStorage.setItem(key, count.toString());
                if (count >= 2) this._onEngagement();
            },
            _onEngagement() {
                if (this.showBanner) return;
                if (this.deferredPrompt || /iphone|ipad|ipod/.test(navigator.userAgent.toLowerCase())) {
                    setTimeout(() => { this.showBanner = true; }, 800);
                }
            },
            _checkTrigger() {
                const key = 'pwa_visits_' + (this.restaurantName || 'global');
                if (parseInt(localStorage.getItem(key) || '0') >= 2) this._onEngagement();
            },
            async install() {
                if (this.deferredPrompt) {
                    this.deferredPrompt.prompt();
                    const { outcome } = await this.deferredPrompt.userChoice;
                    if (outcome === 'accepted') this.showBanner = false;
                    this.deferredPrompt = null;
                } else {
                    alert('Pour installer :\n1. Appuyez sur le bouton Partager\n2. « Sur l\'écran d\'accueil »');
                    this.dismiss();
                }
            },
            dismiss() {
                this.showBanner = false;
                localStorage.setItem('pwa_smart_dismissed', Date.now().toString());
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
            <button @click="install()" class="px-3.5 py-2 rounded-xl text-white text-sm font-semibold transition flex-shrink-0" style="background-color: {{ $restaurant->primary_color ?? '#f97316' }};">
                Installer
            </button>
            <button @click="dismiss()" class="p-1 text-neutral-400 hover:text-neutral-600 transition flex-shrink-0">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    </div>

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
