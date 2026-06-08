<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? 'MenuPro Commando' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="font-sans antialiased bg-[#0f172a] text-neutral-100 min-h-screen">
    <div class="min-h-screen flex flex-col">
        {{-- Header simple --}}
        <header class="border-b border-slate-800 bg-[#0f172a]">
            <div class="max-w-md mx-auto px-4 py-3 flex items-center justify-between">
                <a href="{{ url('/') }}" class="flex items-center gap-2">
                    @php $navLogo = \App\Models\SystemSetting::get('logo', ''); @endphp
                    @if($navLogo && \Illuminate\Support\Facades\Storage::disk('public')->exists($navLogo))
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($navLogo) }}" alt="MenuPro" class="h-7 w-auto">
                    @else
                        <img src="{{ asset('images/logo-menupro.png') }}" alt="MenuPro" class="h-7 w-auto">
                    @endif
                    <span class="font-bold text-sm text-white">Menu<span class="text-orange-500">Pro</span> <span class="text-slate-500 font-normal">Commando</span></span>
                </a>
                <a href="{{ url('/') }}" class="text-xs text-slate-500 hover:text-white transition">Accueil</a>
            </div>
        </header>

        {{-- Contenu --}}
        <main class="flex-1 flex items-center justify-center p-4 py-6">
            <div class="w-full max-w-md">
                {{ $slot }}
            </div>
        </main>
    </div>
    @livewireScripts
</body>
</html>
