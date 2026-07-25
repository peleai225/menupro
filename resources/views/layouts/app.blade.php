<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $appName = config('app.name', 'MenuPro');
        $faviconUrl = null;
        $faviconType = 'image/png';
        try {
            $appName = \App\Models\SystemSetting::get('app_name', $appName);
            $favicon = \App\Models\SystemSetting::get('favicon', '');
            if (!empty($favicon)) {
                $storage = \Illuminate\Support\Facades\Storage::disk('public');
                if ($storage->exists($favicon)) {
                    $baseUrl = request()->getSchemeAndHttpHost();
                    $faviconUrl = $baseUrl . '/storage/' . ltrim($favicon, '/');
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
            }
        } catch (\Throwable $e) {
            $appName = config('app.name', 'MenuPro');
            $faviconUrl = null;
        }
    @endphp
    <title>{{ $title ? $title . ' — ' . $appName : $appName . ' — Menu digital & commandes en ligne Côte d\'Ivoire' }}</title>
    <meta name="description" content="{{ $description ?? $appName . ' — La solution SaaS pour digitaliser le menu de votre restaurant et recevoir des commandes en ligne en Côte d\'Ivoire.' }}">
    <link rel="canonical" href="{{ url()->current() }}">

    <!-- Open Graph -->
    <meta property="og:type" content="website">
    <meta property="og:locale" content="fr_CI">
    <meta property="og:url" content="{{ url()->current() }}">
    <meta property="og:title" content="{{ $title ? $title . ' — ' . $appName : $appName }}">
    <meta property="og:description" content="{{ $description ?? $appName . ' — La solution SaaS pour digitaliser le menu de votre restaurant en Côte d\'Ivoire.' }}">
    <meta property="og:image" content="{{ asset('images/logo-menupro.png') }}">
    <meta name="twitter:card" content="summary">
    <meta name="twitter:title" content="{{ $title ? $title . ' — ' . $appName : $appName }}">
    <meta name="twitter:description" content="{{ $description ?? $appName . ' — La solution SaaS pour digitaliser le menu de votre restaurant en Côte d\'Ivoire.' }}">
    <meta name="twitter:image" content="{{ asset('images/logo-menupro.png') }}">

    <!-- Favicon -->
    @if($faviconUrl)
        <!-- Custom Favicon URL: {{ $faviconUrl }} -->
        <link rel="icon" type="{{ $faviconType }}" href="{{ $faviconUrl }}">
        <link rel="shortcut icon" type="{{ $faviconType }}" href="{{ $faviconUrl }}">
        <link rel="apple-touch-icon" href="{{ $faviconUrl }}">
    @else
        <!-- No custom favicon, using default -->
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
        <link rel="shortcut icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
    @endif

    <!-- Styles -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Livewire Styles -->
    @livewireStyles

    @stack('styles')
</head>
<body class="font-sans antialiased bg-neutral-50 text-neutral-900 overflow-x-hidden">
    <!-- Alpine.js Root -->
    <div x-data="{ 
        darkMode: false,
        notification: { show: false, message: '', type: 'success' }
    }" 
    @notify.window="notification = { show: true, message: $event.detail.message, type: $event.detail.type }; setTimeout(() => notification.show = false, 3000)"
    :class="{ 'dark': darkMode }">
        
        {{ $slot }}

        <!-- Global Notification Toast -->
        <div x-show="notification.show"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-2"
             class="fixed inset-x-4 bottom-4 sm:inset-x-auto sm:right-6 sm:left-auto sm:bottom-6 z-[100] max-w-[calc(100vw-2rem)] sm:max-w-sm"
             x-cloak>
            <div :class="{
                'bg-secondary-500': notification.type === 'success',
                'bg-red-500': notification.type === 'error',
                'bg-yellow-500': notification.type === 'warning',
                'bg-blue-500': notification.type === 'info'
            }" class="flex items-center gap-3 px-6 py-4 rounded-xl text-white shadow-elevated">
                <!-- Icon -->
                <template x-if="notification.type === 'success'">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </template>
                <template x-if="notification.type === 'error'">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </template>
                <span x-text="notification.message" class="font-medium"></span>
                <button @click="notification.show = false" class="ml-2 hover:opacity-70">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Livewire Scripts -->
    @livewireScripts

    @stack('scripts')

    {{-- DEBUG CONDITIONS --}}
    @if(!auth()->check())
        <div class="fixed top-20 left-4 bg-green-500 text-white px-3 py-1 rounded text-xs z-[9999]">
            ✓ Non connecté
        </div>
    @else
        <div class="fixed top-20 left-4 bg-red-500 text-white px-3 py-1 rounded text-xs z-[9999]">
            ✗ Connecté ({{ auth()->user()->name }})
        </div>
    @endif

    @if(request()->routeIs('home') || request()->routeIs('pricing'))
        <div class="fixed top-32 left-4 bg-green-500 text-white px-3 py-1 rounded text-xs z-[9999]">
            ✓ Route OK ({{ request()->route()->getName() }})
        </div>
    @else
        <div class="fixed top-32 left-4 bg-red-500 text-white px-3 py-1 rounded text-xs z-[9999]">
            ✗ Route incorrecte ({{ request()->route()?->getName() ?? 'null' }})
        </div>
    @endif

    {{-- Bottom Navigation Mobile (PWA) - Uniquement pages publiques --}}
    @if(!auth()->check() && (request()->routeIs('home') || request()->routeIs('pricing')))
    {{-- DEBUG: Nav va s'afficher --}}
    <nav class="md:hidden fixed bottom-0 left-0 right-0 bg-white border-t-4 border-primary-500 shadow-2xl z-[9999]" style="min-height: 80px !important;">
        <div class="flex items-center justify-around px-2 py-3">
            {{-- Accueil --}}
            <a href="{{ route('home') }}"
               class="flex flex-col items-center gap-1 px-3 py-1 rounded-xl {{ request()->routeIs('home') ? 'text-primary-600' : 'text-neutral-500' }} hover:text-primary-500 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span class="text-[10px] font-medium">Accueil</span>
            </a>

            {{-- Fonctionnalités --}}
            <a href="{{ route('home') }}#features"
               class="flex flex-col items-center gap-1 px-3 py-1 rounded-xl text-neutral-500 hover:text-primary-500 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                </svg>
                <span class="text-[10px] font-medium">Features</span>
            </a>

            {{-- Tarifs --}}
            <a href="{{ route('pricing') }}"
               class="flex flex-col items-center gap-1 px-3 py-1 rounded-xl {{ request()->routeIs('pricing') ? 'text-primary-600' : 'text-neutral-500' }} hover:text-primary-500 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
                <span class="text-[10px] font-medium">Tarifs</span>
            </a>

            {{-- Contact --}}
            <a href="{{ route('home') }}#contact"
               class="flex flex-col items-center gap-1 px-3 py-1 rounded-xl text-neutral-500 hover:text-primary-500 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                <span class="text-[10px] font-medium">Contact</span>
            </a>

            {{-- Démarrer (CTA principal) --}}
            <a href="{{ route('register') }}"
               class="flex flex-col items-center gap-1 px-3 py-1 rounded-xl bg-primary-500 text-white shadow-md shadow-primary-500/30 hover:bg-primary-600 transition-all">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                <span class="text-[10px] font-bold">Démarrer</span>
            </a>
        </div>
    </nav>

    {{-- Spacer pour éviter que le contenu soit caché par la bottom nav --}}
    <div class="md:hidden h-20"></div>
    @endif
</body>
</html>


