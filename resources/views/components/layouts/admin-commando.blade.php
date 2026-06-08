@props(['title' => null, 'agent' => null])

@php
    if (!$agent && auth()->check()) {
        $agent = auth()->user()->commandoAgent;
    }
@endphp

<x-layouts.app :title="($title ?? 'Mon espace') . ' - MenuPro Commando'">
    <div class="min-h-screen bg-[#0f172a] text-neutral-100">
        {{-- Header --}}
        @if($agent)
        <header class="sticky top-0 z-30 border-b border-slate-800/80 bg-[#0f172a]/95 backdrop-blur-xl">
            <div class="max-w-lg mx-auto px-4 py-3.5 flex items-center justify-between">
                <div class="flex items-center gap-3 min-w-0">
                    <img src="{{ $agent->photo_url }}" alt="{{ $agent->full_name }}"
                         class="w-9 h-9 rounded-xl object-cover border border-slate-700 shrink-0">
                    <div class="min-w-0">
                        <p class="text-sm font-semibold text-white truncate">{{ $agent->full_name }}</p>
                        <p class="text-[11px] text-slate-500">
                            {{ $agent->city ?? 'Agent MenuPro' }}
                            @if($agent->referredRestaurants()->count() > 0)
                                · {{ $agent->referredRestaurants()->count() }} restaurant{{ $agent->referredRestaurants()->count() > 1 ? 's' : '' }}
                            @endif
                        </p>
                    </div>
                </div>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit"
                            class="flex items-center gap-1.5 px-3 py-1.5 rounded-xl border border-slate-700 text-slate-400 hover:text-white hover:bg-slate-800 text-xs transition">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        Déconnexion
                    </button>
                </form>
            </div>
        </header>
        @endif

        {{-- Contenu --}}
        <main class="max-w-lg mx-auto px-4 py-6 pb-28 lg:pb-10">
            {{ $slot }}
        </main>

        {{-- Bottom Nav Mobile --}}
        @if($agent)
        <nav class="fixed bottom-0 left-0 right-0 z-50 lg:hidden bg-[#0f172a]/95 backdrop-blur-xl border-t border-slate-800">
            <div class="flex items-center justify-around h-16 max-w-sm mx-auto px-2">
                {{-- Accueil --}}
                <a href="{{ route('commando.dashboard') }}"
                   class="flex flex-col items-center gap-1 py-2 px-4 rounded-xl transition {{ request()->routeIs('commando.dashboard') ? 'text-orange-400' : 'text-slate-500 hover:text-slate-300' }}">
                    <svg class="w-5 h-5" fill="{{ request()->routeIs('commando.dashboard') ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="{{ request()->routeIs('commando.dashboard') ? '0' : '1.8' }}" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span class="text-[10px] font-medium">Accueil</span>
                </a>

                {{-- Wallet --}}
                <a href="#wallet"
                   class="flex flex-col items-center gap-1 py-2 px-4 rounded-xl text-slate-500 hover:text-slate-300 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <span class="text-[10px] font-medium">Solde</span>
                </a>

                {{-- Parrainage (bouton central si actif) --}}
                @if($agent->canAccessParrainage())
                <a href="#lien" class="flex flex-col items-center -mt-4">
                    <span class="w-14 h-14 rounded-2xl bg-orange-500 shadow-lg shadow-orange-500/30 flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/></svg>
                    </span>
                    <span class="text-[10px] font-medium text-orange-400 mt-1">Partager</span>
                </a>
                @endif

                {{-- Badge PDF (seulement si agent validé) --}}
                @if($agent->canGenerateCard())
                <a href="{{ route('commando.card.download.pdf') }}" target="_blank"
                   class="flex flex-col items-center gap-1 py-2 px-4 rounded-xl text-slate-500 hover:text-slate-300 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4z"/></svg>
                    <span class="text-[10px] font-medium">Badge</span>
                </a>
                @else
                <div class="flex flex-col items-center gap-1 py-2 px-4 rounded-xl text-slate-700 cursor-not-allowed">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4z"/></svg>
                    <span class="text-[10px] font-medium">Badge</span>
                </div>
                @endif
            </div>
        </nav>
        @endif
    </div>
</x-layouts.app>
