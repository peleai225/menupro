<x-layouts.app :title="($title ?? 'Administration') . ' - MenuPro Admin'">
    <div x-data="sidebar()" class="min-h-screen bg-neutral-950">
        <!-- Sidebar -->
        <aside :class="expanded ? 'w-64' : 'w-20'" 
               class="fixed left-0 top-0 h-full bg-neutral-900 border-r border-neutral-800 z-40 transition-all duration-300 hidden lg:block">
            <div class="flex flex-col h-full">
                <!-- Logo -->
                <div class="h-16 flex items-center justify-center border-b border-neutral-800">
                    <a href="{{ route('super-admin.dashboard') }}" class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-gradient-to-br from-primary-500 to-accent-500 rounded-xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                        <span x-show="expanded" x-transition class="text-lg font-bold text-white">
                            Menu<span class="text-primary-400">Pro</span>
                            <span class="block text-xs text-neutral-500 font-normal">Super Admin</span>
                        </span>
                    </a>
                </div>

                <!-- Navigation -->
                <nav class="flex-1 overflow-y-auto py-6 px-3 space-y-2">
                    <!-- Dashboard -->
                    <a href="{{ route('super-admin.dashboard') }}" 
                       class="sidebar-item {{ request()->routeIs('super-admin.dashboard') ? 'sidebar-item-active' : '' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6zM16 13a1 1 0 011-1h2a1 1 0 011 1v6a1 1 0 01-1 1h-2a1 1 0 01-1-1v-6z"/>
                        </svg>
                        <span x-show="expanded" x-transition class="whitespace-nowrap">Dashboard</span>
                    </a>

                    <!-- Restaurants Section -->
                    <div class="pt-4">
                        <span x-show="expanded" x-transition class="px-4 text-xs font-semibold text-neutral-500 uppercase tracking-wider">
                            Gestion
                        </span>
                    </div>

                    <a href="{{ route('super-admin.restaurants.index') }}" 
                       class="sidebar-item {{ request()->routeIs('super-admin.restaurants*') ? 'sidebar-item-active' : '' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                        </svg>
                        <span x-show="expanded" x-transition class="whitespace-nowrap">Restaurants</span>
                        @if(isset($pendingRestaurants) && $pendingRestaurants > 0)
                            <span class="ml-auto bg-primary-500 text-white text-xs px-2 py-0.5 rounded-full">
                                {{ $pendingRestaurants }}
                            </span>
                        @endif
                    </a>

                    <a href="{{ route('super-admin.plans.index') }}" 
                       class="sidebar-item {{ request()->routeIs('super-admin.plans*') ? 'sidebar-item-active' : '' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                        <span x-show="expanded" x-transition class="whitespace-nowrap">Plans & Abonnements</span>
                    </a>

                    <a href="{{ route('super-admin.utilisateurs.index') }}" 
                       class="sidebar-item {{ request()->routeIs('super-admin.utilisateurs*') ? 'sidebar-item-active' : '' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        <span x-show="expanded" x-transition class="whitespace-nowrap">Utilisateurs</span>
                    </a>

                    <!-- Analytics Section -->
                    <div class="pt-4">
                        <span x-show="expanded" x-transition class="px-4 text-xs font-semibold text-neutral-500 uppercase tracking-wider">
                            Analytics
                        </span>
                    </div>

                    <a href="{{ route('super-admin.stats') }}" 
                       class="sidebar-item {{ request()->routeIs('super-admin.stats*') ? 'sidebar-item-active' : '' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        <span x-show="expanded" x-transition class="whitespace-nowrap">Statistiques</span>
                    </a>

                    <a href="{{ route('super-admin.activity') }}" 
                       class="sidebar-item {{ request()->routeIs('super-admin.activity*') ? 'sidebar-item-active' : '' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span x-show="expanded" x-transition class="whitespace-nowrap">Activité</span>
                    </a>

                    <!-- Settings Section -->
                    <div class="pt-4">
                        <span x-show="expanded" x-transition class="px-4 text-xs font-semibold text-neutral-500 uppercase tracking-wider">
                            Système
                        </span>
                    </div>

                    <a href="{{ route('super-admin.settings') }}" 
                       class="sidebar-item {{ request()->routeIs('super-admin.settings*') ? 'sidebar-item-active' : '' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span x-show="expanded" x-transition class="whitespace-nowrap">Paramètres</span>
                    </a>
                </nav>

                <!-- System Status -->
                <div x-show="expanded" x-transition class="p-4 border-t border-neutral-800">
                    <div class="bg-neutral-800/50 rounded-xl p-4">
                        <div class="flex items-center gap-2 text-secondary-400 text-sm font-medium mb-2">
                            <span class="w-2 h-2 bg-secondary-400 rounded-full animate-pulse"></span>
                            Système opérationnel
                        </div>
                        <div class="grid grid-cols-2 gap-2 text-xs text-neutral-500">
                            <div>API: <span class="text-secondary-400">OK</span></div>
                            <div>DB: <span class="text-secondary-400">OK</span></div>
                            <div>Cache: <span class="text-secondary-400">OK</span></div>
                            <div>Queue: <span class="text-secondary-400">OK</span></div>
                        </div>
                    </div>
                </div>

                <!-- Toggle Button -->
                <button @click="toggle()" 
                        class="hidden lg:flex items-center justify-center h-12 border-t border-neutral-800 text-neutral-400 hover:text-white hover:bg-neutral-800 transition-colors">
                    <svg :class="expanded ? 'rotate-180' : ''" class="w-5 h-5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                    </svg>
                </button>
            </div>
        </aside>

        <!-- Main Content -->
        <div :class="expanded ? 'lg:ml-64' : 'lg:ml-20'" class="transition-all duration-300">
            <!-- Top Bar -->
            <header class="sticky top-0 z-20 bg-neutral-900/80 backdrop-blur-lg border-b border-neutral-800">
                <div class="flex items-center justify-between h-16 px-4 lg:px-8">
                    <!-- Mobile Menu Button -->
                    <button @click="toggleMobile()" class="lg:hidden p-2 rounded-lg hover:bg-neutral-800 text-neutral-400">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>

                    <!-- Page Title -->
                    <div class="hidden lg:block">
                        <h1 class="text-xl font-bold text-white">{{ $title ?? 'Administration' }}</h1>
                    </div>

                    <!-- Right Section -->
                    <div class="flex items-center gap-4">
                        <!-- Search -->
                        <div class="hidden md:block relative">
                            <input type="text" 
                                   placeholder="Rechercher..." 
                                   class="w-64 px-4 py-2 bg-neutral-800 border border-neutral-700 rounded-lg text-white placeholder-neutral-500 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            <svg class="absolute right-3 top-1/2 -translate-y-1/2 w-5 h-5 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </div>

                        <!-- Notifications -->
                        <button class="relative p-2 rounded-lg hover:bg-neutral-800 text-neutral-400">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            <span class="absolute top-1 right-1 w-2 h-2 bg-accent-500 rounded-full"></span>
                        </button>

                        <!-- Profile Dropdown -->
                        <div x-data="dropdown()" class="relative">
                            <button @click="toggle()" class="flex items-center gap-3 p-2 rounded-lg hover:bg-neutral-800">
                                <div class="w-8 h-8 bg-gradient-to-br from-primary-500 to-accent-500 rounded-full flex items-center justify-center text-white font-bold">
                                    {{ substr(auth()->user()->name ?? 'A', 0, 1) }}
                                </div>
                                <span class="hidden md:block text-sm font-medium text-neutral-300">
                                    {{ auth()->user()->name ?? 'Admin' }}
                                </span>
                                <svg class="w-4 h-4 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>

                            <div x-show="open" 
                                 x-transition
                                 @click.outside="close()"
                                 class="absolute right-0 mt-2 w-56 bg-neutral-800 rounded-xl shadow-elevated border border-neutral-700 py-2"
                                 x-cloak>
                                <div class="px-4 py-2 border-b border-neutral-700">
                                    <p class="font-medium text-white">{{ auth()->user()->name ?? 'Admin' }}</p>
                                    <p class="text-sm text-neutral-400">Super Administrateur</p>
                                </div>
                                <a href="{{ route('super-admin.settings') }}" class="flex items-center gap-3 px-4 py-2 text-neutral-300 hover:bg-neutral-700">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    Mon profil
                                </a>
                                <hr class="my-2 border-neutral-700">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="flex items-center gap-3 px-4 py-2 text-red-400 hover:bg-neutral-700 w-full">
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
            <main class="p-4 lg:p-8 min-h-screen">
                {{ $slot }}
            </main>
        </div>
    </div>
</x-layouts.app>

