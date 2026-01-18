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
    <title>{{ $title ?? $appName }} - Votre Menu Digital</title>
    <meta name="description" content="{{ $description ?? $appName . ' - La solution SaaS pour digitaliser le menu de votre restaurant et recevoir des commandes en ligne.' }}">

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
</body>
</html>

