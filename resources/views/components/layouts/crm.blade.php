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
                    {{-- In-app notification bell --}}
                    @livewire('crm.crm-notifications-bell')
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
        // --- Web Audio helpers (no external file needed) ---
        function playTone(freq, duration, type = 'sine', gain = 0.18) {
            try {
                const ctx = new (window.AudioContext || window.webkitAudioContext)();
                const osc = ctx.createOscillator();
                const gainNode = ctx.createGain();
                osc.connect(gainNode);
                gainNode.connect(ctx.destination);
                osc.type = type;
                osc.frequency.setValueAtTime(freq, ctx.currentTime);
                gainNode.gain.setValueAtTime(gain, ctx.currentTime);
                gainNode.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + duration);
                osc.start(ctx.currentTime);
                osc.stop(ctx.currentTime + duration);
            } catch (_) {}
        }

        function playNotifSound() {
            // Two-tone ding: 880 Hz then 1100 Hz
            playTone(880, 0.12);
            setTimeout(() => playTone(1100, 0.18), 110);
        }

        function playWelcomeSound() {
            // Ascending chord: C5 → E5 → G5
            playTone(523, 0.2, 'sine', 0.15);
            setTimeout(() => playTone(659, 0.2, 'sine', 0.15), 150);
            setTimeout(() => playTone(784, 0.35, 'sine', 0.15), 300);
        }

        // --- Toast factory ---
        function showToast(message, type = 'info', withSound = false) {
            if (withSound) playNotifSound();
            const icons = {
                success: '✓',
                commission: '💰',
                lead: '📋',
                status: '👤',
                info: 'ℹ',
            };
            const colors = {
                success: 'bg-emerald-500/95',
                commission: 'bg-emerald-600/95',
                lead: 'bg-blue-500/95',
                status: 'bg-orange-500/95',
                info: 'bg-gray-800/95',
            };
            const toast = document.createElement('div');
            const existingToasts = document.querySelectorAll('.crm-toast');
            const offset = existingToasts.length * 70;

            toast.className = `crm-toast fixed right-4 z-[200] flex items-center gap-2.5 max-w-xs px-4 py-3 rounded-2xl shadow-2xl text-sm font-medium text-white backdrop-blur transition-all duration-400 opacity-0 translate-x-8 ${colors[type] || colors.info}`;
            toast.style.top = `${80 + offset}px`;
            toast.innerHTML = `<span class="text-base">${icons[type] || icons.info}</span><span class="flex-1">${message}</span>`;
            document.body.appendChild(toast);

            requestAnimationFrame(() => {
                toast.classList.remove('opacity-0', 'translate-x-8');
                toast.classList.add('opacity-100', 'translate-x-0');
            });

            setTimeout(() => {
                toast.classList.add('opacity-0', 'translate-x-8');
                setTimeout(() => toast.remove(), 400);
            }, 4500);
        }

        // --- Login welcome animation (one-shot via session flash) ---
        @if(session('crm_login_success'))
        document.addEventListener('DOMContentLoaded', () => {
            // Small delay so the page renders first
            setTimeout(() => {
                playWelcomeSound();
                showToast("Bienvenue, {{ auth()->user()->name }} !", 'success');

                // Flash the whole page briefly
                const flash = document.createElement('div');
                flash.className = 'fixed inset-0 z-[300] pointer-events-none bg-orange-500/10';
                flash.style.transition = 'opacity 0.6s';
                document.body.appendChild(flash);
                setTimeout(() => { flash.style.opacity = '0'; setTimeout(() => flash.remove(), 600); }, 300);
            }, 400);
        });
        @endif

        // --- Livewire commission toast (from Livewire event) ---
        document.addEventListener('livewire:init', () => {
            Livewire.on('crm-notify', (event) => {
                showToast(event[0]?.message || event.message, event[0]?.type || event.type || 'info', true);
                // Refresh bell badge
                Livewire.dispatch('refresh-notifications');
            });

            if (window.Echo) {
                const userId = {{ auth()->id() }};
                window.Echo.channel(`crm.user.${userId}`)
                    .listen('.commission.credited', (e) => {
                        showToast(`+${e.amount_formatted} — ${e.description}`, 'commission', true);
                        Livewire.dispatch('refresh-notifications');
                    })
                    .listen('.grade.changed', (e) => {
                        showToast(`Grade ${e.new_grade_label} atteint !`, 'status', true);
                    });

                window.Echo.channel('crm.leads')
                    .listen('.lead.created', (e) => {
                        window.dispatchEvent(new CustomEvent('lead-created', { detail: e }));
                        if (e.assigned_to === userId) {
                            showToast(`Nouveau lead : ${e.restaurant_name}`, 'lead', true);
                            Livewire.dispatch('refresh-notifications');
                        }
                    });
            }
        });
    </script>

    <style>
        .safe-area-pb { padding-bottom: env(safe-area-inset-bottom, 0px); }
        .scrollbar-thin::-webkit-scrollbar { width: 4px; }
        .scrollbar-thin::-webkit-scrollbar-track { background: transparent; }
        .scrollbar-thin::-webkit-scrollbar-thumb { background: rgba(75,85,99,0.3); border-radius: 4px; }
    </style>
</body>
</html>
