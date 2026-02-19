<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'MenuPro Commando' }} - Devenez Agent</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
    @stack('styles')
</head>
<body class="font-sans antialiased bg-[#0f172a] text-neutral-100 min-h-screen">
    <div class="min-h-screen flex flex-col">
        <header class="border-b border-sky-900/50 bg-[#0f172a]/95 backdrop-blur">
            <div class="max-w-lg mx-auto px-4 py-4 flex items-center justify-between">
                @php
                    $navLogo = \App\Models\SystemSetting::get('logo', '');
                    $navAppName = \App\Models\SystemSetting::get('app_name', 'MenuPro');
                @endphp
                <a href="{{ url('/') }}" class="flex items-center gap-2">
                    @if($navLogo && \Illuminate\Support\Facades\Storage::disk('public')->exists($navLogo))
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($navLogo) }}" alt="{{ $navAppName }}" class="h-8 w-auto object-contain">
                    @else
                        <img src="{{ asset('images/logo-menupro.png') }}" alt="{{ $navAppName }}" class="h-8 w-auto object-contain">
                    @endif
                    <span class="font-bold text-lg tracking-tight">
                        <span class="text-white">Menu</span><span class="text-orange-500">Pro</span> Commando
                    </span>
                </a>
                <a href="{{ url('/') }}" class="text-sm text-sky-300 hover:text-white">Retour</a>
            </div>
        </header>
        <main class="flex-1 flex items-center justify-center p-4 py-8">
            <div class="w-full max-w-lg">
                {{ $slot }}
            </div>
        </main>
        <footer class="text-center py-4 text-neutral-500 text-sm border-t border-sky-900/50">
            &copy; {{ date('Y') }} MenuPro. Tous droits réservés.
        </footer>
    </div>
    @livewireScripts
    @stack('scripts')
</body>
</html>
