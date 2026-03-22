@props(['title' => null, 'agent' => null])

@php
    if (!$agent && auth()->check()) {
        $agent = auth()->user()->commandoAgent;
    }
    $grade = $agent ? $agent->grade : null;
@endphp

<x-layouts.app :title="($title ?? 'Centre d\'opérations') . ' - MenuPro Commando'">
    <div x-data="sidebar()" class="min-h-screen bg-[#0f172a] text-neutral-100">
        {{-- Sidebar desktop --}}
        <aside :class="expanded ? 'w-64' : 'w-[70px]'" class="fixed left-0 top-0 h-full border-r border-slate-700/50 bg-slate-900/95 backdrop-blur-xl z-40 transition-all duration-300 hidden lg:block overflow-hidden">
            <div class="flex flex-col h-full">
                {{-- Logo --}}
                <div class="h-16 flex items-center border-b border-slate-700/50 shrink-0 px-4">
                    @php $navLogo = \App\Models\SystemSetting::get('logo', ''); $navAppName = \App\Models\SystemSetting::get('app_name', 'MenuPro'); @endphp
                    <a href="{{ route('commando.dashboard') }}" class="flex items-center gap-2.5 min-w-0">
                        @if($navLogo && \Illuminate\Support\Facades\Storage::disk('public')->exists($navLogo))
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($navLogo) }}" alt="{{ $navAppName }}" class="h-8 w-8 object-contain shrink-0">
                        @else
                            <img src="{{ asset('images/logo-menupro.png') }}" alt="{{ $navAppName }}" class="h-8 w-8 object-contain shrink-0">
                        @endif
                        <span x-show="expanded" x-transition.opacity.duration.200ms class="text-white font-bold whitespace-nowrap">Menu<span class="text-orange-500">Pro</span></span>
                    </a>
                </div>

                {{-- Navigation --}}
                <nav class="flex-1 overflow-y-auto py-4 px-2 space-y-1">
                    {{-- Dashboard --}}
                    <a href="{{ route('commando.dashboard') }}"
                       :title="!expanded ? 'Dashboard' : ''"
                       class="sidebar-item-commando {{ request()->routeIs('commando.dashboard') ? 'sidebar-item-commando-active' : '' }}">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/></svg>
                        <span x-show="expanded" x-transition.opacity.duration.200ms class="whitespace-nowrap">Dashboard</span>
                    </a>

                    {{-- Séparateur --}}
                    <div class="pt-2 pb-1">
                        <div x-show="expanded" x-transition.opacity.duration.200ms class="text-[10px] font-semibold uppercase tracking-widest text-slate-500 px-3">Activité</div>
                        <div x-show="!expanded" class="border-t border-slate-700/50 mx-2"></div>
                    </div>

                    {{-- Performance --}}
                    <a href="#performance"
                       :title="!expanded ? 'Performance' : ''"
                       class="sidebar-item-commando">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        <span x-show="expanded" x-transition.opacity.duration.200ms class="whitespace-nowrap">Performance</span>
                    </a>

                    {{-- Déploiement --}}
                    <a href="#deploiement"
                       :title="!expanded ? 'Déploiement' : ''"
                       class="sidebar-item-commando">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        <span x-show="expanded" x-transition.opacity.duration.200ms class="whitespace-nowrap">Déploiement</span>
                    </a>

                    {{-- Séparateur --}}
                    <div class="pt-2 pb-1">
                        <div x-show="expanded" x-transition.opacity.duration.200ms class="text-[10px] font-semibold uppercase tracking-widest text-slate-500 px-3">Outils</div>
                        <div x-show="!expanded" class="border-t border-slate-700/50 mx-2"></div>
                    </div>

                    {{-- Wallet --}}
                    <a href="#wallet"
                       :title="!expanded ? 'Portefeuille' : ''"
                       class="sidebar-item-commando">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        <span x-show="expanded" x-transition.opacity.duration.200ms class="whitespace-nowrap">Portefeuille</span>
                    </a>

                    {{-- Ma carte --}}
                    <a href="{{ route('commando.card') }}" target="_blank" rel="noopener"
                       :title="!expanded ? 'Ma carte' : ''"
                       class="sidebar-item-commando">
                        <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4z"/></svg>
                        <span x-show="expanded" x-transition.opacity.duration.200ms class="whitespace-nowrap">Ma carte</span>
                    </a>
                </nav>

                {{-- Déconnexion --}}
                <div class="border-t border-slate-700/50 px-2 py-2">
                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <button type="submit"
                                :title="!expanded ? 'Déconnexion' : ''"
                                class="sidebar-item-commando w-full text-red-400 hover:bg-red-500/10 hover:text-red-300">
                            <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                            <span x-show="expanded" x-transition.opacity.duration.200ms class="whitespace-nowrap">Déconnexion</span>
                        </button>
                    </form>
                </div>

                {{-- Bouton collapse --}}
                <button @click="toggle()" class="hidden lg:flex items-center justify-center h-11 border-t border-slate-700/50 text-slate-400 hover:text-white hover:bg-slate-800/50 transition-colors shrink-0">
                    <svg :class="expanded ? 'rotate-180' : ''" class="w-5 h-5 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/></svg>
                </button>
            </div>
        </aside>

        {{-- Mobile overlay --}}
        <div x-show="mobileOpen" x-transition.opacity @click="mobileOpen = false" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-30 lg:hidden" x-cloak></div>

        {{-- Mobile sidebar --}}
        <aside x-show="mobileOpen"
               x-transition:enter="transition ease-out duration-300"
               x-transition:enter-start="-translate-x-full"
               x-transition:enter-end="translate-x-0"
               x-transition:leave="transition ease-in duration-200"
               x-transition:leave-start="translate-x-0"
               x-transition:leave-end="-translate-x-full"
               class="fixed left-0 top-0 h-full w-72 bg-slate-900 border-r border-slate-700/50 z-40 lg:hidden flex flex-col" x-cloak>
            <div class="h-16 flex items-center justify-between border-b border-slate-700/50 px-4">
                <a href="{{ route('commando.dashboard') }}" class="flex items-center gap-2.5" @click="mobileOpen = false">
                    <img src="{{ asset('images/logo-menupro.png') }}" alt="MenuPro" class="h-8 w-auto object-contain">
                    <span class="text-white font-bold">Menu<span class="text-orange-500">Pro</span></span>
                </a>
                <button @click="mobileOpen = false" class="p-2 rounded-xl text-slate-400 hover:text-white hover:bg-slate-800 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <nav class="flex-1 py-4 px-3 space-y-1 overflow-y-auto" @click="mobileOpen = false">
                <a href="{{ route('commando.dashboard') }}" class="sidebar-item-commando {{ request()->routeIs('commando.dashboard') ? 'sidebar-item-commando-active' : '' }}">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/></svg>
                    <span>Dashboard</span>
                </a>
                <div class="text-[10px] font-semibold uppercase tracking-widest text-slate-500 px-3 pt-3 pb-1">Activité</div>
                <a href="#performance" class="sidebar-item-commando">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    <span>Performance</span>
                </a>
                <a href="#deploiement" class="sidebar-item-commando">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    <span>Déploiement</span>
                </a>
                <div class="text-[10px] font-semibold uppercase tracking-widest text-slate-500 px-3 pt-3 pb-1">Outils</div>
                <a href="#wallet" class="sidebar-item-commando">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    <span>Portefeuille</span>
                </a>
                <a href="{{ route('commando.card') }}" target="_blank" class="sidebar-item-commando">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4z"/></svg>
                    <span>Ma carte</span>
                </a>
            </nav>
            <form method="POST" action="{{ route('logout') }}" class="p-3 border-t border-slate-700/50">
                @csrf
                <button type="submit" class="sidebar-item-commando w-full text-red-400 hover:bg-red-500/10 hover:text-red-300">
                    <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                    <span>Déconnexion</span>
                </button>
            </form>
        </aside>

        {{-- Zone principale --}}
        <div :class="expanded ? 'lg:ml-64' : 'lg:ml-[70px]'" class="transition-all duration-300 ml-0">
            @if($agent)
                <header class="sticky top-0 z-20 border-b border-slate-700/50 bg-slate-900/80 backdrop-blur-xl px-4 lg:px-6 py-3">
                    <div class="max-w-6xl mx-auto flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                        <div class="flex items-center gap-3">
                            <button @click="toggleMobile()" type="button" class="lg:hidden p-2.5 -ml-1 rounded-xl hover:bg-slate-700/50 text-slate-400 min-h-[44px] min-w-[44px] flex items-center justify-center" aria-label="Menu">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                            </button>
                            <div class="flex items-center gap-3">
                                <div class="relative shrink-0">
                                    <img src="{{ $agent->photo_url }}" alt="{{ $agent->full_name }}" class="w-12 h-12 rounded-xl object-cover border-2 border-slate-600">
                                    @if($grade)
                                        <span class="absolute -bottom-1 -right-1 px-1.5 py-0.5 rounded text-[10px] font-bold uppercase
                                            @if($grade->value === 'elite') bg-amber-500 text-white
                                            @elseif($grade->value === 'commando') bg-orange-500 text-white
                                            @else bg-slate-500 text-white
                                            @endif">
                                            {{ $grade->value === 'elite' ? 'Élite' : ($grade->value === 'commando' ? 'Commando' : 'Rookie') }}
                                        </span>
                                    @endif
                                </div>
                                <div class="min-w-0">
                                    <h1 class="text-base font-bold text-white truncate">Agent {{ $agent->full_name }}</h1>
                                    <p class="text-slate-400 text-sm truncate">{{ $agent->city ?? 'Secteur non défini' }}</p>
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="px-3 py-1.5 rounded-lg bg-orange-500/15 border border-orange-500/30 text-sm font-semibold text-orange-400">
                                {{ $agent->referredRestaurants()->count() }} mission{{ $agent->referredRestaurants()->count() > 1 ? 's' : '' }}
                            </span>
                            @if($grade)
                                <span class="px-3 py-1.5 rounded-lg bg-slate-800 border border-slate-600 text-sm font-semibold text-slate-300">
                                    Rang {{ $grade->rankLetter() }}
                                </span>
                            @endif
                        </div>
                    </div>
                </header>
            @endif

            {{-- Contenu --}}
            <main class="p-3 sm:p-4 lg:p-6 xl:p-8 overflow-x-hidden pb-24 max-w-6xl mx-auto w-full">
                {{ $slot }}
            </main>

            {{-- Footer --}}
            @if($agent)
                <footer class="border-t border-slate-700/50 bg-slate-900/50 px-4 lg:px-6 py-3">
                    <div class="max-w-6xl mx-auto flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2 text-xs text-slate-500">
                        <span class="inline-flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0" title="Système opérationnel"></span>
                            SYSTÈME OPÉRATIONNEL <span class="font-mono text-slate-400">{{ $agent->badge_id_display }}</span>
                        </span>
                        <span>&copy; {{ date('Y') }} MENUPRO CI - UNITÉ COMMANDO</span>
                    </div>
                </footer>

                @if($agent->canAccessParrainage())
                    <a href="#deploiement" class="fixed bottom-6 right-6 z-20 w-14 h-14 rounded-2xl bg-orange-500 hover:bg-orange-600 text-white shadow-lg shadow-orange-500/25 flex items-center justify-center transition-all hover:scale-105 hover:shadow-xl hover:shadow-orange-500/30 focus:outline-none focus:ring-2 focus:ring-orange-400 focus:ring-offset-2 focus:ring-offset-[#0f172a]" title="Nouveau déploiement">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4"/></svg>
                    </a>
                @endif
            @endif
        </div>
    </div>
</x-layouts.app>
