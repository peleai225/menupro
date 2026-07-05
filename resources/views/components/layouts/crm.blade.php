@props(['title' => null])

<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, viewport-fit=cover">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="theme-color" content="#030712">
    <title>{{ ($title ?? 'CRM') . ' - MenuPro' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full bg-gray-950 text-white antialiased overscroll-none" x-data="{ sidebarOpen: false }">
    <div class="flex h-full">
        {{-- Desktop Sidebar --}}
        <aside class="hidden lg:flex lg:flex-col lg:w-64 lg:fixed lg:inset-y-0 bg-gray-900/95 backdrop-blur-xl border-r border-gray-800/50 z-30">
            {{-- Logo --}}
            <div class="flex items-center h-16 px-4 border-b border-gray-800/50">
                <img src="{{ asset('images/logo-crm-ambassadeurs.png') }}" alt="MenuPro Ambassadeurs" class="h-9 w-auto">
            </div>

            {{-- Navigation --}}
            @php $role = auth()->user()->role->value; @endphp
            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto scrollbar-thin">
                <x-crm-nav-link href="{{ route('crm.dashboard') }}" icon="home" :active="request()->routeIs('crm.dashboard')">
                    Dashboard
                </x-crm-nav-link>

                @if(in_array($role, ['super_admin', 'team_leader', 'commercial']))
                <x-crm-nav-link href="{{ route('crm.leads.index') }}" icon="funnel" :active="request()->routeIs('crm.leads.*')">
                    Pipeline
                </x-crm-nav-link>
                @endif

                @if(in_array($role, ['super_admin', 'technician', 'team_leader']))
                <x-crm-nav-link href="{{ route('crm.installations.index') }}" icon="wrench-screwdriver" :active="request()->routeIs('crm.installations.*')">
                    Installations
                </x-crm-nav-link>
                @endif

                @if(in_array($role, ['super_admin', 'team_leader']))
                <x-crm-nav-link href="{{ route('crm.teams.index') }}" icon="user-group" :active="request()->routeIs('crm.teams.*')">
                    Equipes
                </x-crm-nav-link>
                @endif

                <x-crm-nav-link href="{{ route('crm.wallet') }}" icon="banknotes" :active="request()->routeIs('crm.wallet')">
                    Wallet
                </x-crm-nav-link>

                <x-crm-nav-link href="{{ route('crm.performance') }}" icon="chart-bar" :active="request()->routeIs('crm.performance')">
                    Performance
                </x-crm-nav-link>

                @if(in_array($role, ['commercial', 'technician']))
                <x-crm-nav-link href="{{ route('crm.report') }}" icon="document-text" :active="request()->routeIs('crm.report')">
                    Rapport terrain
                </x-crm-nav-link>
                @endif

                <x-crm-nav-link href="{{ route('crm.profile') }}" icon="user-circle" :active="request()->routeIs('crm.profile')">
                    Mon profil
                </x-crm-nav-link>

                @if($role === 'super_admin')
                <div class="pt-4 mt-4 border-t border-gray-800/50">
                    <p class="px-3 text-[10px] uppercase tracking-wider text-gray-600 font-semibold mb-2">Admin</p>
                    <x-crm-nav-link href="{{ route('crm.admin.agents') }}" icon="shield-check" :active="request()->routeIs('crm.admin.agents*')">
                        Agents
                    </x-crm-nav-link>
                    <x-crm-nav-link href="{{ route('crm.admin.teams') }}" icon="user-group" :active="request()->routeIs('crm.admin.teams*')">
                        Équipes
                    </x-crm-nav-link>
                    <x-crm-nav-link href="{{ route('crm.admin.withdrawals') }}" icon="arrow-up-tray" :active="request()->routeIs('crm.admin.withdrawals')">
                        Retraits
                    </x-crm-nav-link>
                    <x-crm-nav-link href="{{ route('crm.admin.reports') }}" icon="clipboard-document-list" :active="request()->routeIs('crm.admin.reports')">
                        Rapports terrain
                    </x-crm-nav-link>
                </div>
                @endif
            </nav>

            {{-- User footer --}}
            <div class="p-4 border-t border-gray-800/50">
                <div class="flex items-center gap-3">
                    <a href="{{ route('crm.profile') }}" class="shrink-0">
                        <img src="{{ auth()->user()->avatar_url }}" class="w-8 h-8 rounded-lg object-cover ring-2 ring-gray-800 hover:ring-orange-500 transition" alt="">
                    </a>
                    <div class="min-w-0 flex-1">
                        <a href="{{ route('crm.profile') }}" class="block text-sm font-medium text-gray-200 truncate hover:text-orange-400 transition">{{ auth()->user()->name }}</a>
                        <p class="text-xs text-gray-500">{{ auth()->user()->role->label() }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" title="Se déconnecter" class="p-1.5 text-gray-500 hover:text-red-400 transition rounded-lg hover:bg-red-500/10">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- Mobile sidebar overlay --}}
        <div x-show="sidebarOpen" x-transition:enter="transition-opacity duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-40 bg-black/70 backdrop-blur-sm lg:hidden" @click="sidebarOpen = false" x-cloak></div>

        {{-- Mobile sidebar slide-in --}}
        <aside x-show="sidebarOpen"
               x-transition:enter="transition ease-out duration-300" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0"
               x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full"
               class="fixed inset-y-0 left-0 z-50 w-72 flex flex-col bg-gray-900 shadow-2xl lg:hidden" x-cloak>
            {{-- Logo + close --}}
            <div class="flex items-center justify-between h-16 px-4 border-b border-gray-800/50">
                <img src="{{ asset('images/logo-crm-ambassadeurs.png') }}" alt="MenuPro" class="h-8 w-auto">
                <button @click="sidebarOpen = false" class="p-2 text-gray-400 hover:text-white rounded-lg hover:bg-gray-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Mobile nav --}}
            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto" @click="sidebarOpen = false">
                <x-crm-nav-link href="{{ route('crm.dashboard') }}" icon="home" :active="request()->routeIs('crm.dashboard')">
                    Dashboard
                </x-crm-nav-link>

                @if(in_array($role, ['super_admin', 'team_leader', 'commercial']))
                <x-crm-nav-link href="{{ route('crm.leads.index') }}" icon="funnel" :active="request()->routeIs('crm.leads.*')">
                    Pipeline
                </x-crm-nav-link>
                @endif

                @if(in_array($role, ['super_admin', 'technician', 'team_leader']))
                <x-crm-nav-link href="{{ route('crm.installations.index') }}" icon="wrench-screwdriver" :active="request()->routeIs('crm.installations.*')">
                    Installations
                </x-crm-nav-link>
                @endif

                @if(in_array($role, ['super_admin', 'team_leader']))
                <x-crm-nav-link href="{{ route('crm.teams.index') }}" icon="user-group" :active="request()->routeIs('crm.teams.*')">
                    Equipes
                </x-crm-nav-link>
                @endif

                <x-crm-nav-link href="{{ route('crm.wallet') }}" icon="banknotes" :active="request()->routeIs('crm.wallet')">
                    Wallet
                </x-crm-nav-link>

                <x-crm-nav-link href="{{ route('crm.performance') }}" icon="chart-bar" :active="request()->routeIs('crm.performance')">
                    Performance
                </x-crm-nav-link>

                @if(in_array($role, ['commercial', 'technician']))
                <x-crm-nav-link href="{{ route('crm.report') }}" icon="document-text" :active="request()->routeIs('crm.report')">
                    Rapport terrain
                </x-crm-nav-link>
                @endif

                <x-crm-nav-link href="{{ route('crm.profile') }}" icon="user-circle" :active="request()->routeIs('crm.profile')">
                    Mon profil
                </x-crm-nav-link>

                @if($role === 'super_admin')
                <div class="pt-4 mt-4 border-t border-gray-800/50">
                    <p class="px-3 text-[10px] uppercase tracking-wider text-gray-600 font-semibold mb-2">Admin</p>
                    <x-crm-nav-link href="{{ route('crm.admin.agents') }}" icon="shield-check" :active="request()->routeIs('crm.admin.agents*')">
                        Agents
                    </x-crm-nav-link>
                    <x-crm-nav-link href="{{ route('crm.admin.teams') }}" icon="user-group" :active="request()->routeIs('crm.admin.teams*')">
                        Équipes
                    </x-crm-nav-link>
                    <x-crm-nav-link href="{{ route('crm.admin.withdrawals') }}" icon="arrow-up-tray" :active="request()->routeIs('crm.admin.withdrawals')">
                        Retraits
                    </x-crm-nav-link>
                    <x-crm-nav-link href="{{ route('crm.admin.reports') }}" icon="clipboard-document-list" :active="request()->routeIs('crm.admin.reports')">
                        Rapports terrain
                    </x-crm-nav-link>
                </div>
                @endif
            </nav>

            {{-- Mobile user --}}
            <div class="p-4 border-t border-gray-800/50">
                <div class="flex items-center gap-3">
                    <img src="{{ auth()->user()->avatar_url }}" class="w-9 h-9 rounded-xl object-cover ring-2 ring-gray-800" alt="">
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-gray-200 truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-gray-500">{{ auth()->user()->role->label() }}</p>
                    </div>
                </div>
            </div>
        </aside>

        {{-- Main content --}}
        <div class="flex-1 lg:pl-64 flex flex-col min-h-screen pb-16 lg:pb-0">
            {{-- Top bar --}}
            <header class="sticky top-0 z-20 h-14 lg:h-16 flex items-center justify-between px-4 lg:px-8 bg-gray-950/90 backdrop-blur-xl border-b border-gray-800/50">
                <div class="flex items-center gap-3">
                    <button @click="sidebarOpen = true" class="lg:hidden p-2 -ml-2 text-gray-400 hover:text-white rounded-xl hover:bg-gray-800/50 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    @if($title)
                    <h1 class="text-base lg:text-lg font-semibold text-white truncate">{{ $title }}</h1>
                    @endif
                </div>

                <div class="flex items-center gap-2">
                    {{-- Real-time notification bell --}}
                    <div x-data="{ unread: 0 }" class="relative"
                         @commission-credited.window="unread++"
                         @lead-created.window="unread++">
                        <button class="relative p-2 text-gray-400 hover:text-white transition rounded-xl hover:bg-gray-800/50">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                            <span x-show="unread > 0" x-text="unread"
                                  class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] bg-orange-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center px-1"
                                  x-transition></span>
                        </button>
                    </div>
                    {{-- User avatar mobile --}}
                    <img src="{{ auth()->user()->avatar_url }}" class="w-8 h-8 rounded-xl object-cover ring-2 ring-gray-800 lg:hidden" alt="">
                </div>
            </header>

            {{-- Page content --}}
            <main class="flex-1 p-4 lg:p-8">
                {{ $slot }}
            </main>
        </div>

        {{-- Mobile bottom nav --}}
        <nav class="fixed bottom-0 inset-x-0 z-30 lg:hidden bg-gray-900/95 backdrop-blur-xl border-t border-gray-800/50 safe-area-pb">
            <div class="flex items-center justify-around h-16 px-2">
                <a href="{{ route('crm.dashboard') }}" class="flex flex-col items-center gap-0.5 px-3 py-1.5 rounded-xl transition {{ request()->routeIs('crm.dashboard') ? 'text-orange-400' : 'text-gray-500' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    <span class="text-[10px] font-medium">Home</span>
                </a>
                @if(in_array($role, ['super_admin', 'team_leader', 'commercial']))
                <a href="{{ route('crm.leads.index') }}" class="flex flex-col items-center gap-0.5 px-3 py-1.5 rounded-xl transition {{ request()->routeIs('crm.leads.*') ? 'text-orange-400' : 'text-gray-500' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    <span class="text-[10px] font-medium">Pipeline</span>
                </a>
                @endif
                <a href="{{ route('crm.wallet') }}" class="flex flex-col items-center gap-0.5 px-3 py-1.5 rounded-xl transition {{ request()->routeIs('crm.wallet') ? 'text-orange-400' : 'text-gray-500' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    <span class="text-[10px] font-medium">Wallet</span>
                </a>
                <a href="{{ route('crm.performance') }}" class="flex flex-col items-center gap-0.5 px-3 py-1.5 rounded-xl transition {{ request()->routeIs('crm.performance') ? 'text-orange-400' : 'text-gray-500' }}">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    <span class="text-[10px] font-medium">Stats</span>
                </a>
            </div>
        </nav>
    </div>

    @livewireScripts
    <script>
        document.addEventListener('livewire:init', () => {
            if (window.Echo) {
                const userId = {{ auth()->id() }};
                window.Echo.channel(`crm.user.${userId}`)
                    .listen('.commission.credited', (e) => {
                        window.dispatchEvent(new CustomEvent('commission-credited', { detail: e }));
                        showToast(`+${e.amount_formatted} — ${e.description}`, 'success');
                    })
                    .listen('.grade.changed', (e) => {
                        showToast(`Grade ${e.new_grade_label} atteint !`, 'success');
                    });

                window.Echo.channel('crm.leads')
                    .listen('.lead.created', (e) => {
                        window.dispatchEvent(new CustomEvent('lead-created', { detail: e }));
                    })
                    .listen('.lead.status_changed', (e) => {
                        window.dispatchEvent(new CustomEvent('lead-status-changed', { detail: e }));
                    });
            }
        });

        function showToast(message, type = 'info') {
            const toast = document.createElement('div');
            toast.className = `fixed top-4 right-4 z-[100] max-w-xs px-4 py-3 rounded-2xl shadow-2xl text-sm font-medium transition-all duration-500 transform translate-y-[-8px] opacity-0 ${
                type === 'success' ? 'bg-emerald-500/95 text-white backdrop-blur' : 'bg-gray-800/95 text-gray-100 backdrop-blur'
            }`;
            toast.textContent = message;
            document.body.appendChild(toast);
            requestAnimationFrame(() => {
                toast.classList.remove('translate-y-[-8px]', 'opacity-0');
                toast.classList.add('translate-y-0', 'opacity-100');
            });
            setTimeout(() => {
                toast.classList.add('translate-y-[-8px]', 'opacity-0');
                setTimeout(() => toast.remove(), 500);
            }, 4000);
        }
    </script>

    <style>
        .safe-area-pb { padding-bottom: env(safe-area-inset-bottom, 0px); }
        .scrollbar-thin::-webkit-scrollbar { width: 4px; }
        .scrollbar-thin::-webkit-scrollbar-track { background: transparent; }
        .scrollbar-thin::-webkit-scrollbar-thumb { background: rgba(75,85,99,0.3); border-radius: 4px; }
    </style>
</body>
</html>
