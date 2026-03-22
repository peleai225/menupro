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
    <div class="min-h-screen flex flex-col relative overflow-hidden">
        {{-- Fond décoratif subtil --}}
        <div class="absolute inset-0 pointer-events-none" aria-hidden="true">
            <div class="absolute top-0 left-1/2 -translate-x-1/2 w-[600px] h-[600px] bg-orange-500/5 rounded-full blur-3xl"></div>
            <div class="absolute bottom-0 right-0 w-[400px] h-[400px] bg-sky-500/5 rounded-full blur-3xl"></div>
        </div>

        <header class="relative z-10 border-b border-slate-700/50 bg-slate-900/80 backdrop-blur-xl">
            <div class="max-w-lg mx-auto px-4 py-4 flex items-center justify-between">
                @php
                    $navLogo = \App\Models\SystemSetting::get('logo', '');
                    $navAppName = \App\Models\SystemSetting::get('app_name', 'MenuPro');
                @endphp
                <a href="{{ url('/') }}" class="flex items-center gap-2.5">
                    @if($navLogo && \Illuminate\Support\Facades\Storage::disk('public')->exists($navLogo))
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($navLogo) }}" alt="{{ $navAppName }}" class="h-8 w-auto object-contain">
                    @else
                        <img src="{{ asset('images/logo-menupro.png') }}" alt="{{ $navAppName }}" class="h-8 w-auto object-contain">
                    @endif
                    <span class="font-bold text-lg tracking-tight">
                        <span class="text-white">Menu</span><span class="text-orange-500">Pro</span>
                        <span class="text-slate-400 font-medium text-sm ml-1">Commando</span>
                    </span>
                </a>
                <a href="{{ url('/') }}" class="text-sm text-slate-400 hover:text-white transition-colors flex items-center gap-1.5">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Accueil
                </a>
            </div>
        </header>

        <main class="relative z-10 flex-1 flex items-center justify-center p-4 py-8 sm:py-12">
            <div class="w-full max-w-lg">
                {{ $slot }}
            </div>
        </main>

        <footer class="relative z-10 text-center py-4 text-slate-500 text-xs border-t border-slate-700/50">
            &copy; {{ date('Y') }} MenuPro. Tous droits r&eacute;serv&eacute;s.
        </footer>
    </div>
    @livewireScripts
    @stack('scripts')
</body>
</html>
