<x-layouts.app :title="($title ?? 'Dashboard') . ' - ' . ($restaurant->name ?? 'Restaurant')">
    <div x-data="sidebar()" class="min-h-screen bg-neutral-100">
        <!-- Sidebar -->
        <aside :class="expanded ? 'w-64' : 'w-[70px]'"
               class="sidebar transition-all duration-300 ease-in-out hidden lg:flex flex-col"
               :class="{ 'sidebar-collapsed': !expanded }">
            <!-- Logo -->
            <div class="h-16 flex items-center border-b border-neutral-800 overflow-hidden"
                 :class="expanded ? 'justify-start px-5' : 'justify-center px-0'">
                <a href="{{ route('restaurant.dashboard') }}" class="flex items-center gap-2 flex-shrink-0">
                    <img src="{{ asset('images/logo-menupro.png') }}"
                         alt="MenuPro"
                         class="object-contain transition-all duration-300"
                         :class="expanded ? 'h-8' : 'h-7'">
                </a>
            </div>

            <!-- Navigation -->
            <nav class="flex-1 overflow-y-auto overflow-x-hidden py-4 space-y-1"
                 :class="expanded ? 'px-3' : 'px-2'">

                <!-- Dashboard -->
                <a href="{{ route('restaurant.dashboard') }}"
                   class="sidebar-item {{ request()->routeIs('restaurant.dashboard') ? 'sidebar-item-active' : '' }}"
                   :title="!expanded ? 'Dashboard' : ''">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span x-show="expanded" x-transition.opacity.duration.200ms class="truncate">Dashboard</span>
                </a>

                <!-- Section: Menu -->
                <div class="pt-3 pb-1">
                    <span x-show="expanded" x-transition.opacity.duration.200ms class="px-4 text-xs font-semibold text-neutral-500 uppercase tracking-wider">Menu</span>
                    <span x-show="!expanded" class="block border-t border-neutral-700 mx-2"></span>
                </div>

                <a href="{{ route('restaurant.categories.index') }}"
                   class="sidebar-item {{ request()->routeIs('restaurant.categories*') ? 'sidebar-item-active' : '' }}"
                   :title="!expanded ? 'Catégories' : ''">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                    <span x-show="expanded" x-transition.opacity.duration.200ms class="truncate">Catégories</span>
                </a>

                <a href="{{ route('restaurant.plats.index') }}"
                   class="sidebar-item {{ request()->routeIs('restaurant.plats*') ? 'sidebar-item-active' : '' }}"
                   :title="!expanded ? 'Plats' : ''">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                    <span x-show="expanded" x-transition.opacity.duration.200ms class="truncate">Plats</span>
                </a>

                <a href="{{ route('restaurant.stock.ingredients.index') }}"
                   class="sidebar-item {{ request()->routeIs('restaurant.stock.*') ? 'sidebar-item-active' : '' }}"
                   :title="!expanded ? 'Stock & Ingrédients' : ''">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    <span x-show="expanded" x-transition.opacity.duration.200ms class="truncate">Stock</span>
                </a>

                <!-- Section: Commerce -->
                <div class="pt-3 pb-1">
                    <span x-show="expanded" x-transition.opacity.duration.200ms class="px-4 text-xs font-semibold text-neutral-500 uppercase tracking-wider">Commerce</span>
                    <span x-show="!expanded" class="block border-t border-neutral-700 mx-2"></span>
                </div>

                <a href="{{ route('restaurant.orders') }}"
                   class="sidebar-item {{ request()->routeIs('restaurant.orders') ? 'sidebar-item-active' : '' }}"
                   :title="!expanded ? 'Commandes' : ''">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                    <span x-show="expanded" x-transition.opacity.duration.200ms class="truncate">Commandes</span>
                    @if(isset($pendingOrders) && $pendingOrders > 0)
                        <span x-show="expanded" class="ml-auto bg-accent-500 text-white text-xs px-2 py-0.5 rounded-full flex-shrink-0">
                            {{ $pendingOrders }}
                        </span>
                        <span x-show="!expanded" class="absolute top-1 right-1 w-2 h-2 bg-accent-500 rounded-full"></span>
                    @endif
                </a>

                <a href="{{ route('restaurant.orders.board') }}"
                   class="sidebar-item {{ request()->routeIs('restaurant.orders.board') ? 'sidebar-item-active' : '' }}"
                   :title="!expanded ? 'Board Cuisine' : ''">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                    </svg>
                    <span x-show="expanded" x-transition.opacity.duration.200ms class="truncate">Board Cuisine</span>
                </a>

                <a href="{{ route('restaurant.customers') }}"
                   class="sidebar-item {{ request()->routeIs('restaurant.customers*') ? 'sidebar-item-active' : '' }}"
                   :title="!expanded ? 'Clients' : ''">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    <span x-show="expanded" x-transition.opacity.duration.200ms class="truncate">Clients</span>
                </a>

                <!-- Section: Gestion -->
                <div class="pt-3 pb-1">
                    <span x-show="expanded" x-transition.opacity.duration.200ms class="px-4 text-xs font-semibold text-neutral-500 uppercase tracking-wider">Gestion</span>
                    <span x-show="!expanded" class="block border-t border-neutral-700 mx-2"></span>
                </div>

                <a href="{{ route('restaurant.team') }}"
                   class="sidebar-item {{ request()->routeIs('restaurant.team*') ? 'sidebar-item-active' : '' }}"
                   :title="!expanded ? 'Équipe' : ''">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                    <span x-show="expanded" x-transition.opacity.duration.200ms class="truncate">Equipe</span>
                </a>

                <a href="{{ route('restaurant.qrcode') }}"
                   class="sidebar-item {{ request()->routeIs('restaurant.qrcode*') ? 'sidebar-item-active' : '' }}"
                   :title="!expanded ? 'QR Code' : ''">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                    </svg>
                    <span x-show="expanded" x-transition.opacity.duration.200ms class="truncate">QR Code</span>
                </a>

                <a href="{{ route('restaurant.subscription') }}"
                   class="sidebar-item {{ request()->routeIs('restaurant.subscription*') ? 'sidebar-item-active' : '' }}"
                   :title="!expanded ? 'Abonnement' : ''">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                    <span x-show="expanded" x-transition.opacity.duration.200ms class="truncate">Abonnement</span>
                </a>

                <a href="{{ route('restaurant.settings') }}"
                   class="sidebar-item {{ request()->routeIs('restaurant.settings*') ? 'sidebar-item-active' : '' }}"
                   :title="!expanded ? 'Paramètres' : ''">
                    <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span x-show="expanded" x-transition.opacity.duration.200ms class="truncate">Paramètres</span>
                </a>
            </nav>

            <!-- Subscription Status -->
            <div x-show="expanded" x-transition.opacity.duration.200ms class="p-4 border-t border-neutral-800">
                @if(isset($subscription) && $subscription->daysRemaining <= 7)
                    <div class="bg-accent-500/10 border border-accent-500/20 rounded-xl p-3">
                        <div class="flex items-center gap-2 text-accent-400 text-sm font-medium mb-1">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            Abonnement
                        </div>
                        <p class="text-neutral-400 text-xs">
                            Expire dans {{ $subscription->daysRemaining }} jour(s)
                        </p>
                        <a href="{{ route('restaurant.subscription') }}" class="btn btn-accent btn-sm w-full mt-2">
                            Renouveler
                        </a>
                    </div>
                @else
                    <div class="bg-secondary-500/10 border border-secondary-500/20 rounded-xl p-3">
                        <div class="flex items-center gap-2 text-secondary-400 text-sm font-medium">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Plan {{ $subscription->plan->name ?? 'Actif' }}
                        </div>
                    </div>
                @endif
            </div>

            <!-- Toggle Button -->
            <button @click="toggle()"
                    class="hidden lg:flex items-center justify-center h-12 border-t border-neutral-800 text-neutral-400 hover:text-white hover:bg-neutral-800 transition-colors flex-shrink-0">
                <svg :class="expanded ? '' : 'rotate-180'" class="w-5 h-5 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 19l-7-7 7-7m8 14l-7-7 7-7"/>
                </svg>
            </button>
        </aside>

        <!-- Mobile Sidebar Overlay -->
        <div x-show="mobileOpen" 
             x-transition:enter="transition-opacity ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition-opacity ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="mobileOpen = false"
             class="fixed inset-0 bg-black/50 z-30 lg:hidden"
             x-cloak></div>

        <!-- Mobile Sidebar -->
        <aside x-show="mobileOpen"
               x-transition:enter="transition ease-out duration-300"
               x-transition:enter-start="-translate-x-full"
               x-transition:enter-end="translate-x-0"
               x-transition:leave="transition ease-in duration-200"
               x-transition:leave-start="translate-x-0"
               x-transition:leave-end="-translate-x-full"
               class="fixed left-0 top-0 h-full w-64 bg-neutral-900 border-r border-neutral-800 z-40 lg:hidden"
               x-cloak>
            <!-- Same content as desktop sidebar but always expanded -->
            <!-- ... (simplified for brevity, same navigation items) ... -->
        </aside>

        <!-- Main Content -->
        <div :class="expanded ? 'lg:ml-64' : 'lg:ml-[70px]'" class="transition-all duration-300 ease-in-out">
            <!-- Top Bar -->
            <header class="sticky top-0 z-20 bg-white border-b border-neutral-200">
                <div class="flex items-center justify-between h-16 px-4 lg:px-8">
                    <!-- Mobile Menu Button -->
                    <button @click="toggleMobile()" class="lg:hidden p-2 rounded-lg hover:bg-neutral-100">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>

                    <!-- Page Title -->
                    <div class="hidden lg:block">
                        <h1 class="text-xl font-bold text-neutral-900">{{ $title ?? 'Dashboard' }}</h1>
                    </div>

                    <!-- Right Section -->
                    <div class="flex items-center gap-4">
                        <!-- View Site Button -->
                        @if($restaurant->slug)
                            <a href="{{ route('r.menu', $restaurant->slug) }}" 
                               target="_blank"
                               class="hidden sm:flex items-center gap-2 px-4 py-2 bg-primary-500 text-white rounded-lg hover:bg-primary-600 transition-colors font-medium">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                </svg>
                                Voir le menu
                            </a>
                        @endif

                        <!-- Notifications -->
                        <button class="relative p-2 rounded-lg hover:bg-neutral-100">
                            <svg class="w-6 h-6 text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                            </svg>
                            <span class="absolute top-1 right-1 w-2 h-2 bg-accent-500 rounded-full"></span>
                        </button>

                        <!-- Profile Dropdown -->
                        <div x-data="dropdown()" class="relative">
                            <button @click="toggle()" class="flex items-center gap-3 p-2 rounded-lg hover:bg-neutral-100">
                                <img src="{{ auth()->user()->avatar ?? 'https://ui-avatars.com/api/?name=' . urlencode(auth()->user()->name ?? 'User') . '&background=f97316&color=fff' }}" 
                                     alt="Avatar" 
                                     class="w-8 h-8 rounded-full">
                                <span class="hidden md:block text-sm font-medium text-neutral-700">
                                    {{ auth()->user()->name ?? 'Utilisateur' }}
                                </span>
                                <svg class="w-4 h-4 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>

                            <div x-show="open" 
                                 x-transition
                                 @click.outside="close()"
                                 class="absolute right-0 mt-2 w-56 bg-white rounded-xl shadow-elevated border border-neutral-200 py-2"
                                 x-cloak>
                                <div class="px-4 py-2 border-b border-neutral-100">
                                    <p class="font-medium text-neutral-900">{{ auth()->user()->name ?? 'Utilisateur' }}</p>
                                    <p class="text-sm text-neutral-500">{{ auth()->user()->email ?? '' }}</p>
                                </div>
                                <a href="{{ route('restaurant.settings') }}" class="flex items-center gap-3 px-4 py-2 text-neutral-700 hover:bg-neutral-50">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    Mon profil
                                </a>
                                <a href="{{ route('restaurant.settings') }}" class="flex items-center gap-3 px-4 py-2 text-neutral-700 hover:bg-neutral-50">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    Paramètres
                                </a>
                                <hr class="my-2 border-neutral-100">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="flex items-center gap-3 px-4 py-2 text-red-600 hover:bg-red-50 w-full">
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
            <main class="p-4 lg:p-8">
                {{ $slot }}
            </main>
        </div>
    </div>
    
    @push('scripts')
    <script>
        // Convertir les messages flash en notifications
        document.addEventListener('DOMContentLoaded', function() {
            @if(session('success'))
                window.dispatchEvent(new CustomEvent('notify', {
                    detail: {
                        message: @json(session('success')),
                        type: 'success'
                    }
                }));
            @endif
            
            @if(session('error'))
                window.dispatchEvent(new CustomEvent('notify', {
                    detail: {
                        message: @json(session('error')),
                        type: 'error'
                    }
                }));
            @endif
            
            @if(session('warning'))
                window.dispatchEvent(new CustomEvent('notify', {
                    detail: {
                        message: @json(session('warning')),
                        type: 'warning'
                    }
                }));
            @endif
            
            @if(session('info'))
                window.dispatchEvent(new CustomEvent('notify', {
                    detail: {
                        message: @json(session('info')),
                        type: 'info'
                    }
                }));
            @endif
        });
    </script>
    @endpush
</x-layouts.app>

