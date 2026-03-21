@props(['title' => null, 'description' => null, 'canonical' => null])

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

    <title>{{ $title ? $title . ' - ' . $appName : $appName . ' - Votre Menu Digital' }}</title>
    <meta name="description" content="{{ $description ?? $appName . ' - La solution SaaS pour digitaliser le menu de votre restaurant et recevoir des commandes en ligne.' }}">

    @php
        $canonicalUrl = $canonical ?? request()->url();
    @endphp
    <link rel="canonical" href="{{ $canonicalUrl }}">

    <!-- Favicon -->
    @if($faviconUrl)
        <link rel="icon" type="{{ $faviconType }}" href="{{ $faviconUrl }}">
        <link rel="shortcut icon" type="{{ $faviconType }}" href="{{ $faviconUrl }}">
        <link rel="apple-touch-icon" href="{{ $faviconUrl }}">
    @else
        <link rel="icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
        <link rel="shortcut icon" type="image/svg+xml" href="{{ asset('favicon.svg') }}">
        <link rel="apple-touch-icon" href="{{ asset('favicon.svg') }}">
    @endif

    <!-- Open Graph / Social -->
    <meta property="og:type" content="website">
    <meta property="og:url" content="{{ $canonicalUrl }}">
    <meta property="og:title" content="{{ $title ? $title . ' - ' . $appName : $appName }}">
    <meta property="og:description" content="{{ $description ?? 'La solution SaaS pour digitaliser votre restaurant et recevoir des commandes en ligne.' }}">
    <meta property="og:image" content="{{ asset('og-image.png') }}">
    <meta property="og:locale" content="{{ str_replace('_', '-', app()->getLocale()) }}">

    <!-- Twitter Card -->
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $title ?? 'MenuPro — Votre restaurant en ligne' }}">
    <meta name="twitter:description" content="{{ $description ?? 'Créez le site de commande de votre restaurant en quelques minutes.' }}">
    <meta name="twitter:image" content="{{ asset('og-image.png') }}">

    @stack('head')

    <!-- Styles & Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    
    <!-- Livewire Styles -->
    @livewireStyles

    @stack('styles')
</head>
<body class="font-sans antialiased">
    <!-- Alpine.js Root -->
    <div x-data="{ 
        darkMode: localStorage.getItem('darkMode') === 'true',
        notification: { show: false, message: '', type: 'success' }
    }" 
    x-init="$watch('darkMode', val => localStorage.setItem('darkMode', val))"
    @notify.window="notification = { show: true, message: $event.detail.message, type: $event.detail.type }; setTimeout(() => notification.show = false, 4000)"
    :class="{ 'dark': darkMode }">
        
        {{ $slot }}

        <!-- Cookie Consent Banner -->
        <x-cookie-consent />

        <!-- Global Notification Toast -->
        <div x-show="notification.show"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-2 scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 scale-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0 scale-100"
             x-transition:leave-end="opacity-0 translate-y-2 scale-95"
             class="fixed inset-x-4 bottom-4 sm:inset-x-auto sm:right-6 sm:left-auto sm:bottom-6 z-[100] max-w-[calc(100vw-2rem)] sm:max-w-sm"
             x-cloak>
            <div :class="{
                'bg-secondary-500': notification.type === 'success',
                'bg-red-500': notification.type === 'error',
                'bg-yellow-500': notification.type === 'warning',
                'bg-blue-500': notification.type === 'info'
            }" class="flex items-start gap-3 px-5 py-4 rounded-xl text-white shadow-elevated">
                <!-- Icon -->
                <div class="flex-shrink-0 mt-0.5">
                    <template x-if="notification.type === 'success'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </template>
                    <template x-if="notification.type === 'error'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </template>
                    <template x-if="notification.type === 'warning'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                    </template>
                    <template x-if="notification.type === 'info'">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </template>
                </div>
                <p class="flex-1 text-sm font-medium" x-text="notification.message"></p>
                <button @click="notification.show = false" class="flex-shrink-0 hover:opacity-70 transition-opacity">
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

    <!-- Suppress external script errors (browser extensions, etc.) -->
    <script>
        // Suppress errors from external scripts (browser extensions, etc.)
        window.addEventListener('error', function(e) {
            // Ignore errors from external scripts (kins_stabilizer, vendor.js from extensions)
            if (e.filename && (
                e.filename.includes('kins_stabilizer') || 
                e.filename.includes('vendor.js') ||
                e.filename.includes('chrome-extension') ||
                e.filename.includes('moz-extension')
            )) {
                e.preventDefault();
                return false;
            }
        }, true);

        // Suppress unhandled promise rejections from external scripts
        window.addEventListener('unhandledrejection', function(e) {
            // Ignore errors that don't have useful information (likely from extensions)
            if (e.reason && typeof e.reason === 'object' && 
                e.reason.status === null && 
                e.reason.body === null && 
                e.reason.json === null) {
                e.preventDefault();
                return false;
            }
        });
    </script>
</body>
</html>

