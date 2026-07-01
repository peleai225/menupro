<x-layouts.app :title="($title ?? 'Administration') . ' - MenuPro Admin'">
    <div x-data="sidebar()" class="min-h-screen bg-neutral-100">
        <!-- Sidebar Desktop -->
        <aside :class="expanded ? 'w-64' : 'w-[72px]'"
               class="fixed left-0 top-0 h-full bg-neutral-900 z-40 transition-all duration-300 hidden lg:flex flex-col shadow-xl">
            <!-- Logo -->
            <div class="h-16 flex items-center px-4 border-b border-neutral-800">
                <a href="{{ route('super-admin.dashboard') }}" class="flex items-center gap-3 w-full">
                    <div class="w-9 h-9 bg-gradient-to-br from-primary-500 to-primary-600 rounded-xl flex items-center justify-center flex-shrink-0 shadow-lg shadow-primary-500/20">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <div x-show="expanded" x-transition.opacity class="flex items-center gap-2 min-w-0">
                        <span class="font-bold text-white text-sm tracking-wide">MenuPro</span>
                        <span class="text-[10px] font-semibold text-primary-400 bg-primary-500/15 px-1.5 py-0.5 rounded">ADMIN</span>
                    </div>
                </a>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1 scrollbar-thin">
                <!-- Dashboard -->
                <a href="{{ route('super-admin.dashboard') }}"
                   class="nav-item {{ request()->routeIs('super-admin.dashboard') ? 'nav-active' : '' }}" title="Dashboard">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                    </svg>
                    <span x-show="expanded" x-transition.opacity class="whitespace-nowrap text-sm">Dashboard</span>
                </a>

                <!-- Section: Gestion -->
                <div class="pt-5 pb-1">
                    <span x-show="expanded" x-transition.opacity class="px-3 text-[10px] font-bold text-neutral-500 uppercase tracking-[0.15em]">
                        Gestion
                    </span>
                    <div x-show="!expanded" class="h-px bg-neutral-800 mx-2"></div>
                </div>

                <a href="{{ route('super-admin.restaurants.index') }}"
                   class="nav-item {{ request()->routeIs('super-admin.restaurants*') ? 'nav-active' : '' }}" title="Restaurants">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    <span x-show="expanded" x-transition.opacity class="whitespace-nowrap text-sm flex-1">Restaurants</span>
                    @if(($pendingRestaurants ?? 0) > 0)
                        <span class="sidebar-badge-restaurants bg-amber-500 text-white text-[10px] font-bold min-w-[20px] h-5 px-1.5 flex items-center justify-center rounded-full" x-show="expanded">{{ $pendingRestaurants ?? 0 }}</span>
                    @endif
                </a>

                <a href="{{ route('super-admin.orders.index') }}"
                   class="nav-item {{ request()->routeIs('super-admin.orders*') ? 'nav-active' : '' }}" title="Commandes">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <span x-show="expanded" x-transition.opacity class="whitespace-nowrap text-sm">Commandes</span>
                </a>

                <a href="{{ route('super-admin.deliveries.index') }}"
                   class="nav-item {{ request()->routeIs('super-admin.deliveries*') ? 'nav-active' : '' }}" title="Livraisons">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
                    </svg>
                    <span x-show="expanded" x-transition.opacity class="whitespace-nowrap text-sm">Livraisons</span>
                </a>

                <a href="{{ route('super-admin.plans.index') }}"
                   class="nav-item {{ request()->routeIs('super-admin.plans*') ? 'nav-active' : '' }}" title="Plans">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                    <span x-show="expanded" x-transition.opacity class="whitespace-nowrap text-sm">Plans</span>
                </a>

                <a href="{{ route('super-admin.subscriptions.index') }}"
                   class="nav-item {{ request()->routeIs('super-admin.subscriptions*') ? 'nav-active' : '' }}" title="Abonnements">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span x-show="expanded" x-transition.opacity class="whitespace-nowrap text-sm">Abonnements</span>
                </a>

                <a href="{{ route('super-admin.utilisateurs.index') }}"
                   class="nav-item {{ request()->routeIs('super-admin.utilisateurs*') ? 'nav-active' : '' }}" title="Utilisateurs">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <span x-show="expanded" x-transition.opacity class="whitespace-nowrap text-sm">Utilisateurs</span>
                </a>

                <a href="{{ route('super-admin.commando.agents.index') }}"
                   class="nav-item {{ request()->routeIs('super-admin.commando*') ? 'nav-active' : '' }}" title="Commando">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span x-show="expanded" x-transition.opacity class="whitespace-nowrap text-sm flex-1">Commando</span>
                    @if(($pendingCommandoAgents ?? 0) > 0)
                        <span class="sidebar-badge-commando bg-orange-500 text-white text-[10px] font-bold min-w-[20px] h-5 px-1.5 flex items-center justify-center rounded-full" x-show="expanded">{{ $pendingCommandoAgents ?? 0 }}</span>
                    @endif
                </a>

                <!-- Section: Livraison -->
                <div class="pt-5 pb-1">
                    <span x-show="expanded" x-transition.opacity class="px-3 text-[10px] font-bold text-neutral-500 uppercase tracking-[0.15em]">
                        Livraison
                    </span>
                    <div x-show="!expanded" class="h-px bg-neutral-800 mx-2"></div>
                </div>

                <a href="{{ route('super-admin.drivers.index') }}"
                   class="nav-item {{ request()->routeIs('super-admin.drivers*') ? 'nav-active' : '' }}" title="Livreurs">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
                    </svg>
                    <span x-show="expanded" x-transition.opacity class="whitespace-nowrap text-sm flex-1">Livreurs</span>
                    @if(($pendingDrivers ?? 0) > 0)
                        <span class="sidebar-badge-drivers bg-amber-500 text-white text-[10px] font-bold min-w-[20px] h-5 px-1.5 flex items-center justify-center rounded-full" x-show="expanded">{{ $pendingDrivers ?? 0 }}</span>
                    @endif
                </a>

                <a href="{{ route('super-admin.customers.index') }}"
                   class="nav-item {{ request()->routeIs('super-admin.customers*') ? 'nav-active' : '' }}" title="Clients">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span x-show="expanded" x-transition.opacity class="whitespace-nowrap text-sm">Clients</span>
                </a>

                <a href="{{ route('super-admin.delivery-zones.index') }}"
                   class="nav-item {{ request()->routeIs('super-admin.delivery-zones*') ? 'nav-active' : '' }}" title="Zones de livraison">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span x-show="expanded" x-transition.opacity class="whitespace-nowrap text-sm">Zones livraison</span>
                </a>

                <a href="{{ route('super-admin.push.index') }}"
                   class="nav-item {{ request()->routeIs('super-admin.push*') ? 'nav-active' : '' }}" title="Notifications Push">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <span x-show="expanded" x-transition.opacity class="whitespace-nowrap text-sm">Push Notifs</span>
                </a>

                <!-- Section: Analytics -->
                <div class="pt-5 pb-1">
                    <span x-show="expanded" x-transition.opacity class="px-3 text-[10px] font-bold text-neutral-500 uppercase tracking-[0.15em]">
                        Analytics
                    </span>
                    <div x-show="!expanded" class="h-px bg-neutral-800 mx-2"></div>
                </div>

                <a href="{{ route('super-admin.stats') }}"
                   class="nav-item {{ request()->routeIs('super-admin.stats*') ? 'nav-active' : '' }}" title="Statistiques">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <span x-show="expanded" x-transition.opacity class="whitespace-nowrap text-sm">Statistiques</span>
                </a>

                <a href="{{ route('super-admin.transactions.index') }}"
                   class="nav-item {{ request()->routeIs('super-admin.transactions*') ? 'nav-active' : '' }}" title="Transactions">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span x-show="expanded" x-transition.opacity class="whitespace-nowrap text-sm">Transactions</span>
                </a>

                <a href="{{ route('super-admin.finances.index') }}"
                   class="nav-item {{ request()->routeIs('super-admin.finances*') ? 'nav-active' : '' }}" title="Finances">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span x-show="expanded" x-transition.opacity class="whitespace-nowrap text-sm">Finances</span>
                </a>

                <a href="{{ route('super-admin.activity') }}"
                   class="nav-item {{ request()->routeIs('super-admin.activity*') ? 'nav-active' : '' }}" title="Activité">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span x-show="expanded" x-transition.opacity class="whitespace-nowrap text-sm">Activité</span>
                </a>

                <!-- Section: Communication -->
                <div class="pt-5 pb-1">
                    <span x-show="expanded" x-transition.opacity class="px-3 text-[10px] font-bold text-neutral-500 uppercase tracking-[0.15em]">
                        Communication
                    </span>
                    <div x-show="!expanded" class="h-px bg-neutral-800 mx-2"></div>
                </div>

                <a href="{{ route('super-admin.announcements.index') }}"
                   class="nav-item {{ request()->routeIs('super-admin.announcements*') ? 'nav-active' : '' }}" title="Annonces">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                    </svg>
                    <span x-show="expanded" x-transition.opacity class="whitespace-nowrap text-sm">Annonces</span>
                </a>

                <!-- Section: Système -->
                <div class="pt-5 pb-1">
                    <span x-show="expanded" x-transition.opacity class="px-3 text-[10px] font-bold text-neutral-500 uppercase tracking-[0.15em]">
                        Système
                    </span>
                    <div x-show="!expanded" class="h-px bg-neutral-800 mx-2"></div>
                </div>

                <a href="{{ route('super-admin.settings') }}"
                   class="nav-item {{ request()->routeIs('super-admin.settings*') ? 'nav-active' : '' }}" title="Paramètres">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span x-show="expanded" x-transition.opacity class="whitespace-nowrap text-sm">Paramètres</span>
                </a>
            </nav>

            <!-- System Status -->
            <div x-show="expanded" x-transition.opacity class="p-3 border-t border-neutral-800">
                <div class="bg-neutral-800/80 rounded-xl p-3">
                    <div class="flex items-center gap-2 mb-2">
                        <span class="w-2 h-2 bg-emerald-400 rounded-full animate-pulse"></span>
                        <span class="text-xs font-medium text-emerald-400">Système opérationnel</span>
                    </div>
                    <div class="grid grid-cols-2 gap-1.5 text-[10px]">
                        <div class="text-neutral-500">API <span class="text-emerald-400">OK</span></div>
                        <div class="text-neutral-500">DB <span class="text-emerald-400">OK</span></div>
                        <div class="text-neutral-500">Cache <span class="text-emerald-400">OK</span></div>
                        <div class="text-neutral-500">Queue <span class="text-emerald-400">OK</span></div>
                    </div>
                </div>
            </div>

            <!-- Toggle Button -->
            <button @click="toggle()"
                    class="hidden lg:flex items-center justify-center h-11 border-t border-neutral-800 text-neutral-500 hover:text-white hover:bg-neutral-800 transition-colors">
                <svg :class="expanded ? 'rotate-180' : ''" class="w-4 h-4 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                </svg>
            </button>
        </aside>

        <!-- Overlay sidebar mobile -->
        <div x-show="mobileOpen" x-transition:enter="transition-opacity ease-out duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-in duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" @click="mobileOpen = false" class="fixed inset-0 bg-black/60 backdrop-blur-sm z-30 lg:hidden" x-cloak></div>

        <!-- Sidebar mobile -->
        <aside x-show="mobileOpen" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="-translate-x-full" x-transition:enter-end="translate-x-0" x-transition:leave="transition ease-in duration-200" x-transition:leave-start="translate-x-0" x-transition:leave-end="-translate-x-full" class="fixed left-0 top-0 h-full w-72 bg-neutral-900 z-40 lg:hidden flex flex-col shadow-2xl" x-cloak>
            <div class="h-16 flex items-center justify-between px-4 border-b border-neutral-800">
                <a href="{{ route('super-admin.dashboard') }}" class="flex items-center gap-3" @click="mobileOpen = false">
                    <div class="w-9 h-9 bg-gradient-to-br from-primary-500 to-primary-600 rounded-xl flex items-center justify-center shadow-lg shadow-primary-500/20">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <span class="font-bold text-white text-sm">MenuPro <span class="text-primary-400">Admin</span></span>
                </a>
                <button @click="mobileOpen = false" class="p-2 rounded-lg text-neutral-400 hover:text-white hover:bg-neutral-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1" @click="mobileOpen = false">
                <a href="{{ route('super-admin.dashboard') }}" class="nav-item {{ request()->routeIs('super-admin.dashboard') ? 'nav-active' : '' }}"><svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/></svg><span class="text-sm">Dashboard</span></a>

                <div class="pt-4 pb-1"><span class="px-3 text-[10px] font-bold text-neutral-500 uppercase tracking-[0.15em]">Gestion</span></div>
                <a href="{{ route('super-admin.restaurants.index') }}" class="nav-item {{ request()->routeIs('super-admin.restaurants*') ? 'nav-active' : '' }}"><svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg><span class="text-sm flex-1">Restaurants</span>@if(($pendingRestaurants ?? 0) > 0)<span class="sidebar-badge-restaurants bg-amber-500 text-white text-[10px] font-bold min-w-[20px] h-5 px-1.5 flex items-center justify-center rounded-full">{{ $pendingRestaurants ?? 0 }}</span>@endif</a>
                <a href="{{ route('super-admin.orders.index') }}" class="nav-item {{ request()->routeIs('super-admin.orders*') ? 'nav-active' : '' }}"><svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg><span class="text-sm">Commandes</span></a>
                <a href="{{ route('super-admin.deliveries.index') }}" class="nav-item {{ request()->routeIs('super-admin.deliveries*') ? 'nav-active' : '' }}"><svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg><span class="text-sm">Livraisons</span></a>
                <a href="{{ route('super-admin.plans.index') }}" class="nav-item {{ request()->routeIs('super-admin.plans*') ? 'nav-active' : '' }}"><svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg><span class="text-sm">Plans</span></a>
                <a href="{{ route('super-admin.subscriptions.index') }}" class="nav-item {{ request()->routeIs('super-admin.subscriptions*') ? 'nav-active' : '' }}"><svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg><span class="text-sm">Abonnements</span></a>
                <a href="{{ route('super-admin.utilisateurs.index') }}" class="nav-item {{ request()->routeIs('super-admin.utilisateurs*') ? 'nav-active' : '' }}"><svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg><span class="text-sm">Utilisateurs</span></a>
                <a href="{{ route('super-admin.commando.agents.index') }}" class="nav-item {{ request()->routeIs('super-admin.commando*') ? 'nav-active' : '' }}"><svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 12l2 2 4-4M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span class="text-sm flex-1">Commando</span>@if(($pendingCommandoAgents ?? 0) > 0)<span class="sidebar-badge-commando bg-orange-500 text-white text-[10px] font-bold min-w-[20px] h-5 px-1.5 flex items-center justify-center rounded-full">{{ $pendingCommandoAgents ?? 0 }}</span>@endif</a>

                <div class="pt-4 pb-1"><span class="px-3 text-[10px] font-bold text-neutral-500 uppercase tracking-[0.15em]">Livraison</span></div>
                <a href="{{ route('super-admin.drivers.index') }}" class="nav-item {{ request()->routeIs('super-admin.drivers*') ? 'nav-active' : '' }}"><svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg><span class="text-sm flex-1">Livreurs</span>@if(($pendingDrivers ?? 0) > 0)<span class="sidebar-badge-drivers bg-amber-500 text-white text-[10px] font-bold min-w-[20px] h-5 px-1.5 flex items-center justify-center rounded-full">{{ $pendingDrivers ?? 0 }}</span>@endif</a>
                <a href="{{ route('super-admin.customers.index') }}" class="nav-item {{ request()->routeIs('super-admin.customers*') ? 'nav-active' : '' }}"><svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg><span class="text-sm">Clients</span></a>
                <a href="{{ route('super-admin.delivery-zones.index') }}" class="nav-item {{ request()->routeIs('super-admin.delivery-zones*') ? 'nav-active' : '' }}"><svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg><span class="text-sm">Zones livraison</span></a>
                <a href="{{ route('super-admin.push.index') }}" class="nav-item {{ request()->routeIs('super-admin.push*') ? 'nav-active' : '' }}"><svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg><span class="text-sm">Push Notifs</span></a>

                <div class="pt-4 pb-1"><span class="px-3 text-[10px] font-bold text-neutral-500 uppercase tracking-[0.15em]">Analytics</span></div>
                <a href="{{ route('super-admin.stats') }}" class="nav-item {{ request()->routeIs('super-admin.stats*') ? 'nav-active' : '' }}"><svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg><span class="text-sm">Statistiques</span></a>
                <a href="{{ route('super-admin.transactions.index') }}" class="nav-item {{ request()->routeIs('super-admin.transactions*') ? 'nav-active' : '' }}"><svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg><span class="text-sm">Transactions</span></a>
                <a href="{{ route('super-admin.finances.index') }}" class="nav-item {{ request()->routeIs('super-admin.finances*') ? 'nav-active' : '' }}"><svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span class="text-sm">Finances</span></a>
                <a href="{{ route('super-admin.activity') }}" class="nav-item {{ request()->routeIs('super-admin.activity*') ? 'nav-active' : '' }}"><svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span class="text-sm">Activité</span></a>

                <div class="pt-4 pb-1"><span class="px-3 text-[10px] font-bold text-neutral-500 uppercase tracking-[0.15em]">Système</span></div>
                <a href="{{ route('super-admin.announcements.index') }}" class="nav-item {{ request()->routeIs('super-admin.announcements*') ? 'nav-active' : '' }}"><svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg><span class="text-sm">Annonces</span></a>
                <a href="{{ route('super-admin.settings') }}" class="nav-item {{ request()->routeIs('super-admin.settings*') ? 'nav-active' : '' }}"><svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg><span class="text-sm">Paramètres</span></a>
            </nav>
        </aside>

        <!-- Main Content -->
        <div :class="expanded ? 'lg:ml-64' : 'lg:ml-[72px]'" class="transition-all duration-300 ml-0">
            <!-- Top Bar -->
            <header class="sticky top-0 z-20 bg-white border-b border-neutral-200/80 shadow-sm">
                <div class="flex items-center justify-between h-14 sm:h-16 px-4 lg:px-8 gap-3">
                    <!-- Mobile Menu Button -->
                    <button @click="toggleMobile()" class="lg:hidden p-2.5 -ml-1 rounded-xl hover:bg-neutral-100 text-neutral-600 min-h-[44px] min-w-[44px] flex items-center justify-center">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>

                    <!-- Page Title -->
                    <div class="flex-1 min-w-0">
                        <h1 class="text-lg sm:text-xl font-bold text-neutral-900 truncate">{{ $title ?? 'Administration' }}</h1>
                    </div>

                    <!-- Right Section -->
                    <div class="flex items-center gap-2 sm:gap-3">
                        <!-- Search (desktop) -->
                        <div class="hidden lg:block relative">
                            <input type="text"
                                   placeholder="Rechercher..."
                                   class="w-56 pl-9 pr-4 py-2 bg-neutral-100 border border-transparent rounded-xl text-sm text-neutral-900 placeholder-neutral-400 focus:outline-none focus:bg-white focus:border-neutral-300 focus:ring-2 focus:ring-primary-500/10 transition-all">
                            <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>

                        <!-- Notifications -->
                        <div class="relative" x-data="notificationBell()" @click.outside="open = false">
                            <button type="button" @click="open = !open; if(open) loadNotifications()" class="relative p-2 rounded-xl hover:bg-neutral-100 text-neutral-500 transition-colors">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </svg>
                                <span x-show="unreadCount > 0" x-text="unreadCount" class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] px-1 flex items-center justify-center bg-red-500 text-white text-[10px] font-bold rounded-full ring-2 ring-white" x-cloak></span>
                            </button>
                            <div x-show="open" x-transition x-cloak class="absolute right-0 mt-2 w-80 max-h-[70vh] overflow-hidden bg-white rounded-2xl shadow-xl border border-neutral-200 flex flex-col z-50">
                                <div class="px-4 py-3 border-b border-neutral-100 flex items-center justify-between">
                                    <span class="font-semibold text-neutral-900 text-sm">Notifications</span>
                                    <span x-show="unreadCount > 0" class="text-xs text-primary-600 font-medium" x-text="unreadCount + ' non lue(s)'"></span>
                                </div>
                                <div class="overflow-y-auto flex-1">
                                    <template x-if="loading">
                                        <div class="p-6 text-center text-neutral-400 text-sm">Chargement...</div>
                                    </template>
                                    <template x-if="!loading && items.length === 0">
                                        <div class="p-6 text-center">
                                            <svg class="w-8 h-8 text-neutral-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                            <p class="text-neutral-400 text-sm">Aucune notification</p>
                                        </div>
                                    </template>
                                    <template x-if="!loading && items.length > 0">
                                        <ul class="py-1">
                                            <template x-for="n in items" :key="n.id">
                                                <li>
                                                    <a :href="n.url" class="block px-4 py-3 hover:bg-neutral-50 transition-colors border-b border-neutral-50 last:border-0" :class="n.read_at ? 'opacity-60' : ''">
                                                        <p class="text-sm text-neutral-800" x-text="n.message"></p>
                                                        <p class="text-xs text-neutral-400 mt-1" x-text="formatDate(n.created_at)"></p>
                                                    </a>
                                                </li>
                                            </template>
                                        </ul>
                                    </template>
                                </div>
                            </div>
                        </div>

                        <!-- Profile Dropdown -->
                        <div x-data="dropdown()" class="relative">
                            <button @click="toggle()" class="flex items-center gap-2.5 p-1.5 pr-3 rounded-xl hover:bg-neutral-100 transition-colors">
                                <div class="w-8 h-8 bg-gradient-to-br from-neutral-800 to-neutral-900 rounded-lg flex items-center justify-center text-white text-sm font-bold shadow-sm">
                                    {{ substr(auth()->user()->name ?? 'A', 0, 1) }}
                                </div>
                                <span class="hidden md:block text-sm font-medium text-neutral-700">
                                    {{ auth()->user()->name ?? 'Admin' }}
                                </span>
                                <svg class="w-3.5 h-3.5 text-neutral-400 hidden md:block" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>

                            <div x-show="open" x-transition @click.outside="close()" class="absolute right-0 mt-2 w-56 bg-white rounded-2xl shadow-xl border border-neutral-200 py-2 z-50" x-cloak>
                                <div class="px-4 py-3 border-b border-neutral-100">
                                    <p class="font-semibold text-neutral-900 text-sm">{{ auth()->user()->name ?? 'Admin' }}</p>
                                    <p class="text-xs text-neutral-500 mt-0.5">Super Administrateur</p>
                                </div>
                                <a href="{{ route('super-admin.settings') }}" class="flex items-center gap-3 px-4 py-2.5 text-sm text-neutral-600 hover:bg-neutral-50 hover:text-neutral-900 transition-colors">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    Mon profil
                                </a>
                                <hr class="my-1.5 border-neutral-100">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="flex items-center gap-3 px-4 py-2.5 text-sm text-red-600 hover:bg-red-50 w-full transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                        </svg>
                                        Déconnexion
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </header>

            <!-- Page Content -->
            <main class="p-4 lg:p-6 xl:p-8 min-h-screen overflow-x-hidden pb-24 lg:pb-8">
                {{ $slot }}
            </main>
        </div>

        {{-- Bottom Navigation Bar (Mobile) — Style app mobile --}}
        <nav class="fixed bottom-0 left-0 right-0 z-50 lg:hidden bg-white border-t border-neutral-200 shadow-[0_-4px_20px_rgba(0,0,0,0.08)]"
             x-data="{ more: false }">
            <div class="flex items-center justify-around h-16 px-1 max-w-lg mx-auto">
                {{-- Dashboard --}}
                <a href="{{ route('super-admin.dashboard') }}"
                   class="flex flex-col items-center justify-center gap-0.5 py-1 px-2 rounded-lg min-w-[56px] {{ request()->routeIs('super-admin.dashboard') ? 'text-primary-600' : 'text-neutral-500' }}">
                    <svg class="w-6 h-6" fill="{{ request()->routeIs('super-admin.dashboard') ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="{{ request()->routeIs('super-admin.dashboard') ? '0' : '1.5' }}" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                    </svg>
                    <span class="text-[10px] font-medium">Accueil</span>
                </a>

                {{-- Restaurants --}}
                <a href="{{ route('super-admin.restaurants.index') }}"
                   class="relative flex flex-col items-center justify-center gap-0.5 py-1 px-2 rounded-lg min-w-[56px] {{ request()->routeIs('super-admin.restaurants*') ? 'text-primary-600' : 'text-neutral-500' }}">
                    <svg class="w-6 h-6" fill="{{ request()->routeIs('super-admin.restaurants*') ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="{{ request()->routeIs('super-admin.restaurants*') ? '0' : '1.5' }}" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    <span class="text-[10px] font-medium">Restos</span>
                    @if(($pendingRestaurants ?? 0) > 0)
                    <span class="absolute top-0 right-1 w-5 h-5 bg-amber-500 text-white text-[9px] font-bold rounded-full flex items-center justify-center">{{ ($pendingRestaurants ?? 0) > 9 ? '9+' : $pendingRestaurants }}</span>
                    @endif
                </a>

                {{-- Finances (centre, surélevé) --}}
                <a href="{{ route('super-admin.finances.index') }}"
                   class="relative flex flex-col items-center justify-center gap-0.5 -mt-4">
                    <span class="flex items-center justify-center w-14 h-14 rounded-2xl shadow-lg {{ request()->routeIs('super-admin.finances*') || request()->routeIs('super-admin.transactions*') ? 'bg-primary-500 text-white' : 'bg-neutral-900 text-white' }}">
                        <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </span>
                    <span class="text-[10px] font-medium mt-0.5 {{ request()->routeIs('super-admin.finances*') || request()->routeIs('super-admin.transactions*') ? 'text-primary-600' : 'text-neutral-500' }}">Finances</span>
                </a>

                {{-- Stats --}}
                <a href="{{ route('super-admin.stats') }}"
                   class="flex flex-col items-center justify-center gap-0.5 py-1 px-2 rounded-lg min-w-[56px] {{ request()->routeIs('super-admin.stats*') ? 'text-primary-600' : 'text-neutral-500' }}">
                    <svg class="w-6 h-6" fill="{{ request()->routeIs('super-admin.stats*') ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="{{ request()->routeIs('super-admin.stats*') ? '0' : '1.5' }}" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <span class="text-[10px] font-medium">Stats</span>
                </a>

                {{-- Plus --}}
                <button @click="more = !more"
                        :class="more ? 'text-primary-600' : 'text-neutral-500'"
                        class="flex flex-col items-center justify-center gap-0.5 py-1 px-2 rounded-lg min-w-[56px]">
                    <svg class="w-6 h-6 transition-transform" :class="more && 'rotate-45'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <span class="text-[10px] font-medium">Plus</span>
                </button>
            </div>

            {{-- Menu "Plus" expandable --}}
            <div x-show="more"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 translate-y-4"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 translate-y-4"
                 @click.outside="more = false"
                 class="absolute bottom-full left-0 right-0 bg-white border-t border-neutral-200 shadow-[0_-8px_30px_rgba(0,0,0,0.12)] rounded-t-2xl px-4 pt-4 pb-3"
                 x-cloak>
                <div class="w-10 h-1 bg-neutral-200 rounded-full mx-auto mb-4"></div>
                <div class="grid grid-cols-4 gap-3 mb-3">
                    <a href="{{ route('super-admin.orders.index') }}" @click="more = false" class="flex flex-col items-center gap-1.5 p-3 rounded-xl hover:bg-neutral-50 {{ request()->routeIs('super-admin.orders*') ? 'bg-primary-50' : '' }}">
                        <span class="w-10 h-10 rounded-xl bg-indigo-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        </span>
                        <span class="text-[10px] font-medium text-neutral-700">Commandes</span>
                    </a>
                    <a href="{{ route('super-admin.deliveries.index') }}" @click="more = false" class="flex flex-col items-center gap-1.5 p-3 rounded-xl hover:bg-neutral-50 {{ request()->routeIs('super-admin.deliveries*') ? 'bg-primary-50' : '' }}">
                        <span class="w-10 h-10 rounded-xl bg-purple-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                        </span>
                        <span class="text-[10px] font-medium text-neutral-700">Livraisons</span>
                    </a>
                    <a href="{{ route('super-admin.plans.index') }}" @click="more = false" class="flex flex-col items-center gap-1.5 p-3 rounded-xl hover:bg-neutral-50 {{ request()->routeIs('super-admin.plans*') ? 'bg-primary-50' : '' }}">
                        <span class="w-10 h-10 rounded-xl bg-blue-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                        </span>
                        <span class="text-[10px] font-medium text-neutral-700">Plans</span>
                    </a>
                    <a href="{{ route('super-admin.subscriptions.index') }}" @click="more = false" class="flex flex-col items-center gap-1.5 p-3 rounded-xl hover:bg-neutral-50 {{ request()->routeIs('super-admin.subscriptions*') ? 'bg-primary-50' : '' }}">
                        <span class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        </span>
                        <span class="text-[10px] font-medium text-neutral-700">Abonnements</span>
                    </a>
                    <a href="{{ route('super-admin.utilisateurs.index') }}" @click="more = false" class="flex flex-col items-center gap-1.5 p-3 rounded-xl hover:bg-neutral-50 {{ request()->routeIs('super-admin.utilisateurs*') ? 'bg-primary-50' : '' }}">
                        <span class="w-10 h-10 rounded-xl bg-violet-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                        </span>
                        <span class="text-[10px] font-medium text-neutral-700">Users</span>
                    </a>
                    <a href="{{ route('super-admin.commando.agents.index') }}" @click="more = false" class="flex flex-col items-center gap-1.5 p-3 rounded-xl hover:bg-neutral-50 {{ request()->routeIs('super-admin.commando*') ? 'bg-primary-50' : '' }}">
                        <span class="w-10 h-10 rounded-xl bg-orange-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </span>
                        <span class="text-[10px] font-medium text-neutral-700">Commando</span>
                    </a>
                    <a href="{{ route('super-admin.transactions.index') }}" @click="more = false" class="flex flex-col items-center gap-1.5 p-3 rounded-xl hover:bg-neutral-50 {{ request()->routeIs('super-admin.transactions*') ? 'bg-primary-50' : '' }}">
                        <span class="w-10 h-10 rounded-xl bg-cyan-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        </span>
                        <span class="text-[10px] font-medium text-neutral-700">Transactions</span>
                    </a>
                    <a href="{{ route('super-admin.activity') }}" @click="more = false" class="flex flex-col items-center gap-1.5 p-3 rounded-xl hover:bg-neutral-50 {{ request()->routeIs('super-admin.activity*') ? 'bg-primary-50' : '' }}">
                        <span class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </span>
                        <span class="text-[10px] font-medium text-neutral-700">Activite</span>
                    </a>
                    <a href="{{ route('super-admin.announcements.index') }}" @click="more = false" class="flex flex-col items-center gap-1.5 p-3 rounded-xl hover:bg-neutral-50 {{ request()->routeIs('super-admin.announcements*') ? 'bg-primary-50' : '' }}">
                        <span class="w-10 h-10 rounded-xl bg-pink-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                        </span>
                        <span class="text-[10px] font-medium text-neutral-700">Annonces</span>
                    </a>
                    <a href="{{ route('super-admin.drivers.index') }}" @click="more = false" class="flex flex-col items-center gap-1.5 p-3 rounded-xl hover:bg-neutral-50 {{ request()->routeIs('super-admin.drivers*') ? 'bg-primary-50' : '' }}">
                        <span class="w-10 h-10 rounded-xl bg-teal-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-teal-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                        </span>
                        <span class="text-[10px] font-medium text-neutral-700">Livreurs</span>
                    </a>
                    <a href="{{ route('super-admin.customers.index') }}" @click="more = false" class="flex flex-col items-center gap-1.5 p-3 rounded-xl hover:bg-neutral-50 {{ request()->routeIs('super-admin.customers*') ? 'bg-primary-50' : '' }}">
                        <span class="w-10 h-10 rounded-xl bg-rose-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </span>
                        <span class="text-[10px] font-medium text-neutral-700">Clients</span>
                    </a>
                    <a href="{{ route('super-admin.delivery-zones.index') }}" @click="more = false" class="flex flex-col items-center gap-1.5 p-3 rounded-xl hover:bg-neutral-50 {{ request()->routeIs('super-admin.delivery-zones*') ? 'bg-primary-50' : '' }}">
                        <span class="w-10 h-10 rounded-xl bg-lime-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-lime-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </span>
                        <span class="text-[10px] font-medium text-neutral-700">Zones</span>
                    </a>
                    <a href="{{ route('super-admin.push.index') }}" @click="more = false" class="flex flex-col items-center gap-1.5 p-3 rounded-xl hover:bg-neutral-50 {{ request()->routeIs('super-admin.push*') ? 'bg-primary-50' : '' }}">
                        <span class="w-10 h-10 rounded-xl bg-sky-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-sky-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        </span>
                        <span class="text-[10px] font-medium text-neutral-700">Push</span>
                    </a>
                    <a href="{{ route('super-admin.settings') }}" @click="more = false" class="flex flex-col items-center gap-1.5 p-3 rounded-xl hover:bg-neutral-50 {{ request()->routeIs('super-admin.settings*') ? 'bg-primary-50' : '' }}">
                        <span class="w-10 h-10 rounded-xl bg-neutral-100 flex items-center justify-center">
                            <svg class="w-5 h-5 text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        </span>
                        <span class="text-[10px] font-medium text-neutral-700">Reglages</span>
                    </a>
                </div>
            </div>
        </nav>
    </div>

    @push('scripts')
    <script>
        (function() {
            var url = @json(route('super-admin.api.sidebar-badges'));
            function updateBadges() {
                fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(function(r) { return r.json(); })
                    .then(function(data) {
                        var r = data.pending_restaurants || 0;
                        var c = data.pending_commando_agents || 0;
                        var d = data.pending_drivers || 0;
                        document.querySelectorAll('.sidebar-badge-restaurants').forEach(function(el) {
                            el.textContent = r;
                            el.classList.toggle('hidden', r === 0);
                        });
                        document.querySelectorAll('.sidebar-badge-commando').forEach(function(el) {
                            el.textContent = c;
                            el.classList.toggle('hidden', c === 0);
                        });
                        document.querySelectorAll('.sidebar-badge-drivers').forEach(function(el) {
                            el.textContent = d;
                            el.classList.toggle('hidden', d === 0);
                        });
                    })
                    .catch(function() {});
            }
            updateBadges();
            setInterval(updateBadges, 45000);
        })();
    </script>
    @endpush
</x-layouts.app>
