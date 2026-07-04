@props(['title' => null])

<!DOCTYPE html>
<html lang="fr" class="h-full">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ ($title ?? 'CRM') . ' - MenuPro' }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    @livewireStyles
</head>
<body class="h-full bg-gray-950 text-white antialiased" x-data="{ sidebarOpen: false }">
    <div class="flex h-full">
        {{-- Sidebar --}}
        <aside class="hidden lg:flex lg:flex-col lg:w-64 lg:fixed lg:inset-y-0 bg-gray-900 border-r border-gray-800/50">
            {{-- Logo --}}
            <div class="flex items-center h-16 px-4 border-b border-gray-800/50">
                <img src="{{ asset('images/logo-crm-ambassadeurs.png') }}" alt="MenuPro Ambassadeurs" class="h-9 w-auto">
            </div>

            {{-- Navigation --}}
            <nav class="flex-1 px-3 py-4 space-y-1 overflow-y-auto">
                @php $role = auth()->user()->role->value; @endphp

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
                    Équipes
                </x-crm-nav-link>
                @endif

                <x-crm-nav-link href="{{ route('crm.wallet') }}" icon="banknotes" :active="request()->routeIs('crm.wallet')">
                    Wallet
                </x-crm-nav-link>

                <x-crm-nav-link href="{{ route('crm.performance') }}" icon="chart-bar" :active="request()->routeIs('crm.performance')">
                    Performance
                </x-crm-nav-link>

                @if($role === 'super_admin')
                <div class="pt-4 mt-4 border-t border-gray-800/50">
                    <p class="px-3 text-[10px] uppercase tracking-wider text-gray-600 font-semibold mb-2">Admin</p>
                    <x-crm-nav-link href="{{ route('crm.admin.agents') }}" icon="shield-check" :active="request()->routeIs('crm.admin.agents*')">
                        Agents
                    </x-crm-nav-link>
                    <x-crm-nav-link href="{{ route('crm.admin.withdrawals') }}" icon="arrow-up-tray" :active="request()->routeIs('crm.admin.withdrawals')">
                        Retraits
                    </x-crm-nav-link>
                </div>
                @endif
            </nav>

            {{-- User footer --}}
            <div class="p-4 border-t border-gray-800/50">
                <div class="flex items-center gap-3">
                    <img src="{{ auth()->user()->avatar_url }}" class="w-8 h-8 rounded-lg object-cover" alt="">
                    <div class="min-w-0 flex-1">
                        <p class="text-sm font-medium text-gray-200 truncate">{{ auth()->user()->name }}</p>
                        <p class="text-xs text-gray-500">{{ auth()->user()->role->label() }}</p>
                    </div>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-gray-500 hover:text-gray-300 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        </button>
                    </form>
                </div>
            </div>
        </aside>

        {{-- Mobile sidebar overlay --}}
        <div x-show="sidebarOpen" x-transition:enter="transition-opacity duration-200" x-transition:leave="transition-opacity duration-200"
             class="fixed inset-0 z-40 bg-black/60 lg:hidden" @click="sidebarOpen = false"></div>

        {{-- Main content --}}
        <div class="flex-1 lg:pl-64 flex flex-col min-h-screen">
            {{-- Top bar --}}
            <header class="sticky top-0 z-30 h-16 flex items-center justify-between px-4 lg:px-8 bg-gray-950/80 backdrop-blur-xl border-b border-gray-800/50">
                <div class="flex items-center gap-4">
                    <button @click="sidebarOpen = true" class="lg:hidden text-gray-400 hover:text-white">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                    @if($title)
                    <h1 class="text-lg font-semibold text-white">{{ $title }}</h1>
                    @endif
                </div>

                <div class="flex items-center gap-3">
                    {{-- Real-time notification bell --}}
                    <div x-data="{ unread: 0 }" class="relative"
                         @commission-credited.window="unread++"
                         @lead-created.window="unread++">
                        <button class="relative p-2 text-gray-400 hover:text-white transition rounded-lg hover:bg-gray-800">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                            <span x-show="unread > 0" x-text="unread"
                                  class="absolute -top-0.5 -right-0.5 w-4 h-4 bg-orange-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center"
                                  x-transition></span>
                        </button>
                    </div>
                </div>
            </header>

            {{-- Page content --}}
            <main class="flex-1 p-4 lg:p-8">
                {{ $slot }}
            </main>
        </div>
    </div>

    @livewireScripts
    <script>
        document.addEventListener('livewire:init', () => {
            if (window.Echo) {
                const userId = {{ auth()->id() }};
                window.Echo.channel(`crm.user.${userId}`)
                    .listen('.commission.credited', (e) => {
                        window.dispatchEvent(new CustomEvent('commission-credited', { detail: e }));
                        showToast(`💰 ${e.amount_formatted} — ${e.description}`, 'success');
                    })
                    .listen('.grade.changed', (e) => {
                        showToast(`🎖️ Félicitations ! Grade ${e.new_grade_label} atteint !`, 'success');
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
            toast.className = `fixed bottom-4 right-4 z-[100] px-4 py-3 rounded-xl shadow-2xl text-sm font-medium transition-all duration-500 transform translate-y-2 opacity-0 ${
                type === 'success' ? 'bg-emerald-500/90 text-white' : 'bg-gray-800 text-gray-100'
            }`;
            toast.textContent = message;
            document.body.appendChild(toast);
            requestAnimationFrame(() => {
                toast.classList.remove('translate-y-2', 'opacity-0');
            });
            setTimeout(() => {
                toast.classList.add('translate-y-2', 'opacity-0');
                setTimeout(() => toast.remove(), 500);
            }, 4000);
        }
    </script>
</body>
</html>
