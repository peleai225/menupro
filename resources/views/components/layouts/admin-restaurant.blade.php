@props(['title' => null, 'restaurant' => null, 'subscription' => null])

@php
    // Get restaurant from auth if not provided
    if (!$restaurant && auth()->check()) {
        $restaurant = auth()->user()->restaurant;
    }
    // Get subscription if not provided
    if (!$subscription && $restaurant) {
        $subscription = $restaurant->activeSubscription;
    }
    $isAdmin = auth()->user()?->canManageRestaurant() ?? false;

    // Étapes du tutoriel guidé (selon le rôle et les fonctionnalités)
    $hasStock = isset($restaurant) && $restaurant?->hasFeature('stock');
    if ($isAdmin) {
        $tourSteps = [
            ['popover' => ['title' => 'Bienvenue sur MenuPro', 'description' => 'Ce tutoriel vous présente les onglets du tableau de bord. Cliquez sur Suivant pour découvrir chaque section.']],
            ['element' => '#tour-dashboard', 'popover' => ['title' => 'Dashboard', 'description' => 'Vue d\'ensemble : commandes du jour, chiffre d\'affaires et statistiques.']],
            ['element' => '#tour-categories', 'popover' => ['title' => 'Catégories', 'description' => 'Organisez votre menu (Entrées, Plats, Desserts, Boissons...).']],
            ['element' => '#tour-plats', 'popover' => ['title' => 'Plats', 'description' => 'Ajoutez des plats, modifiez les prix, photos et disponibilité.']],
            ['element' => '#tour-qrcode', 'popover' => ['title' => 'QR Code', 'description' => 'Téléchargez votre QR Code pour que les clients accèdent à votre menu.']],
            ['element' => '#tour-commandes', 'popover' => ['title' => 'Commandes', 'description' => 'Gérez les commandes : statuts, impression des tickets.']],
            ['element' => '#tour-clients', 'popover' => ['title' => 'Clients', 'description' => 'Liste des clients ayant commandé chez vous.']],
            ['element' => '#tour-codes-promo', 'popover' => ['title' => 'Codes Promo', 'description' => 'Créez des réductions et promotions.']],
            ['element' => '#tour-statistiques', 'popover' => ['title' => 'Statistiques', 'description' => 'Graphiques et analyses de votre activité.']],
        ];
        if ($hasStock) {
            $tourSteps[] = ['element' => '#tour-stock', 'popover' => ['title' => 'Stock', 'description' => 'Ingrédients, fournisseurs et alertes de rupture.']];
        }
        $tourSteps[] = ['element' => '#tour-parametres', 'popover' => ['title' => 'Paramètres', 'description' => 'Configurez : infos, paiement, horaires, couleurs du site.']];
    } else {
        $tourSteps = [
            ['popover' => ['title' => 'Bienvenue sur MenuPro', 'description' => 'Ce tutoriel vous présente les onglets auxquels vous avez accès.']],
            ['element' => '#tour-dashboard', 'popover' => ['title' => 'Dashboard', 'description' => 'Vue d\'ensemble de l\'activité du restaurant.']],
            ['element' => '#tour-commandes', 'popover' => ['title' => 'Commandes', 'description' => 'Gérez les commandes : changez les statuts, imprimez les tickets.']],
            ['element' => '#tour-qrcode', 'popover' => ['title' => 'QR Code', 'description' => 'Accédez au QR Code du restaurant.']],
        ];
        if ($hasStock) {
            $tourSteps[] = ['element' => '#tour-stock', 'popover' => ['title' => 'Ingrédients', 'description' => 'Consultez le stock et ajustez les quantités.']];
        }
    }
@endphp

<x-layouts.app :title="($title ?? 'Dashboard') . ' - ' . ($restaurant?->name ?? 'Restaurant')">
    <div x-data="sidebar()" class="min-h-screen bg-neutral-50">
        <!-- Sidebar -->
        <aside :class="expanded ? 'w-64' : 'w-20'" 
               class="sidebar transition-all duration-300 hidden lg:block">
            <div class="flex flex-col h-full">
                <!-- Logo -->
                <div class="h-16 flex items-center justify-center border-b border-neutral-800 px-3">
                    <a href="{{ route('restaurant.dashboard') }}" class="flex items-center gap-2">
                        <img src="{{ asset('images/logo-menupro.png') }}" 
                             alt="MenuPro" 
                             class="h-8 w-auto object-contain transition-all duration-300"
                             :class="expanded ? 'h-8' : 'h-7'">
                    </a>
                </div>

                <!-- Navigation -->
                <nav class="flex-1 overflow-y-auto py-6 px-3 space-y-2">
                    <!-- Dashboard -->
                    <a href="{{ route('restaurant.dashboard') }}" 
                       id="tour-dashboard"
                       class="sidebar-item {{ request()->routeIs('restaurant.dashboard') ? 'sidebar-item-active' : '' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        <span x-show="expanded" x-transition class="whitespace-nowrap">Dashboard</span>
                    </a>

                    <!-- Menu Section (admin uniquement) -->
                    @if($isAdmin)
                    <div class="pt-4">
                        <span x-show="expanded" x-transition class="px-4 text-xs font-semibold text-neutral-500 uppercase tracking-wider">
                            Menu
                        </span>
                    </div>

                    <a href="{{ route('restaurant.categories.index') }}" 
                       id="tour-categories"
                       class="sidebar-item {{ request()->routeIs('restaurant.categories*') ? 'sidebar-item-active' : '' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                        <span x-show="expanded" x-transition class="whitespace-nowrap">Catégories</span>
                    </a>

                    <a href="{{ route('restaurant.plats.index') }}" 
                       id="tour-plats"
                       class="sidebar-item {{ request()->routeIs('restaurant.plats*') ? 'sidebar-item-active' : '' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                        <span x-show="expanded" x-transition class="whitespace-nowrap">Plats</span>
                    </a>

                    @endif
                    <a href="{{ route('restaurant.qrcode') }}" 
                       id="tour-qrcode"
                       class="sidebar-item {{ request()->routeIs('restaurant.qrcode*') ? 'sidebar-item-active' : '' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                        </svg>
                        <span x-show="expanded" x-transition class="whitespace-nowrap">QR Code</span>
                    </a>

                    <!-- Commerce Section -->
                    <div class="pt-4">
                        <span x-show="expanded" x-transition class="px-4 text-xs font-semibold text-neutral-500 uppercase tracking-wider">
                            Commerce
                        </span>
                    </div>

                    <a href="{{ route('restaurant.orders') }}"
                       id="tour-commandes"
                       class="sidebar-item {{ request()->routeIs('restaurant.orders*') ? 'sidebar-item-active' : '' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                        <span x-show="expanded" x-transition class="whitespace-nowrap">Commandes</span>
                        @if(isset($pendingOrders) && $pendingOrders > 0)
                            <span class="ml-auto bg-accent-500 text-white text-xs px-2 py-0.5 rounded-full">
                                {{ $pendingOrders }}
                            </span>
                        @endif
                    </a>

                    <a href="{{ route('restaurant.pos') }}"
                       id="tour-pos"
                       class="sidebar-item {{ request()->routeIs('restaurant.pos*') ? 'sidebar-item-active' : '' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <span x-show="expanded" x-transition class="whitespace-nowrap">Caisse (POS)</span>
                    </a>

                    @if($isAdmin)
                    <a href="{{ route('restaurant.customers') }}" 
                       id="tour-clients"
                       class="sidebar-item {{ request()->routeIs('restaurant.customers*') ? 'sidebar-item-active' : '' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <span x-show="expanded" x-transition class="whitespace-nowrap">Clients</span>
                    </a>

                    <a href="{{ route('restaurant.promo-codes') }}" 
                       id="tour-codes-promo"
                       class="sidebar-item {{ request()->routeIs('restaurant.promo-codes*') ? 'sidebar-item-active' : '' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span x-show="expanded" x-transition class="whitespace-nowrap">Codes Promo</span>
                    </a>

                    <a href="{{ route('restaurant.analytics') }}" 
                       id="tour-statistiques"
                       class="sidebar-item {{ request()->routeIs('restaurant.analytics*') ? 'sidebar-item-active' : '' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        <span x-show="expanded" x-transition class="whitespace-nowrap">Statistiques</span>
                    </a>

                    <a href="{{ route('restaurant.reports') }}" 
                       class="sidebar-item {{ request()->routeIs('restaurant.reports*') ? 'sidebar-item-active' : '' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        <span x-show="expanded" x-transition class="whitespace-nowrap">Rapports</span>
                    </a>

                    <a href="{{ route('restaurant.reservations.index') }}" 
                       class="sidebar-item {{ request()->routeIs('restaurant.reservations*') ? 'sidebar-item-active' : '' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span x-show="expanded" x-transition class="whitespace-nowrap">Réservations</span>
                        @php
                            $pendingReservations = isset($restaurant) ? $restaurant->reservations()->pending()->count() : 0;
                        @endphp
                        @if($pendingReservations > 0)
                            <span class="ml-auto bg-accent-500 text-white text-xs px-2 py-0.5 rounded-full">
                                {{ $pendingReservations }}
                            </span>
                        @endif
                    </a>

                    <a href="{{ route('restaurant.reviews') }}" 
                       class="sidebar-item {{ request()->routeIs('restaurant.reviews*') ? 'sidebar-item-active' : '' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                        </svg>
                        <span x-show="expanded" x-transition class="whitespace-nowrap">Avis</span>
                    </a>
                    @endif

                    @if(isset($restaurant) && $restaurant?->hasFeature('stock'))
                        <!-- Stock Section -->
                        <div class="pt-4">
                            <span x-show="expanded" x-transition class="px-4 text-xs font-semibold text-neutral-500 uppercase tracking-wider">
                                Stock
                            </span>
                        </div>

                        <a href="{{ route('restaurant.stock.ingredients.index') }}" 
                           id="tour-stock"
                           class="sidebar-item {{ request()->routeIs('restaurant.stock.ingredients*') ? 'sidebar-item-active' : '' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                            <span x-show="expanded" x-transition class="whitespace-nowrap">Ingrédients</span>
                        </a>

                        @if($isAdmin)
                        <a href="{{ route('restaurant.stock.fournisseurs.index') }}" 
                           class="sidebar-item {{ request()->routeIs('restaurant.stock.fournisseurs*') ? 'sidebar-item-active' : '' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            <span x-show="expanded" x-transition class="whitespace-nowrap">Fournisseurs</span>
                        </a>
                        @endif

                        <a href="{{ route('restaurant.stock.alerts') }}" 
                           class="sidebar-item {{ request()->routeIs('restaurant.stock.alerts*') ? 'sidebar-item-active' : '' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <span x-show="expanded" x-transition class="whitespace-nowrap">Alertes Stock</span>
                            @php
                                $lowStockCount = isset($restaurant) && $restaurant->hasFeature('stock') 
                                    ? $restaurant->ingredients()->whereColumn('current_quantity', '<=', 'min_quantity')->count() 
                                    : 0;
                            @endphp
                            @if($lowStockCount > 0)
                                <span class="ml-auto bg-accent-500 text-white text-xs px-2 py-0.5 rounded-full">
                                    {{ $lowStockCount }}
                                </span>
                            @endif
                        </a>

                        @if($isAdmin)
                        <a href="{{ route('restaurant.stock.report') }}" 
                           class="sidebar-item {{ request()->routeIs('restaurant.stock.report*') ? 'sidebar-item-active' : '' }}">
                            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                            </svg>
                            <span x-show="expanded" x-transition class="whitespace-nowrap">Rapport Stock</span>
                        </a>
                        @endif
                    @endif

                    @if($isAdmin)
                    <!-- Gestion d'équipe Section -->
                    <div class="pt-4">
                        <span x-show="expanded" x-transition class="px-4 text-xs font-semibold text-neutral-500 uppercase tracking-wider">
                            Équipe
                        </span>
                    </div>

                    <a href="{{ route('restaurant.team') }}" 
                       class="sidebar-item {{ request()->routeIs('restaurant.team*') ? 'sidebar-item-active' : '' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <span x-show="expanded" x-transition class="whitespace-nowrap">Équipe</span>
                    </a>

                    <!-- Settings Section -->
                    <div class="pt-4">
                        <span x-show="expanded" x-transition class="px-4 text-xs font-semibold text-neutral-500 uppercase tracking-wider">
                            Paramètres
                        </span>
                    </div>

                    <a href="{{ route('restaurant.subscription') }}" 
                       class="sidebar-item {{ request()->routeIs('restaurant.subscription*') ? 'sidebar-item-active' : '' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                        <span x-show="expanded" x-transition class="whitespace-nowrap">Abonnement</span>
                    </a>

                    <a href="{{ route('restaurant.taxes-fees') }}" 
                       class="sidebar-item {{ request()->routeIs('restaurant.taxes-fees*') ? 'sidebar-item-active' : '' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-5m-3 5h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        <span x-show="expanded" x-transition class="whitespace-nowrap">Taxes & Frais</span>
                    </a>

                    <a href="{{ route('restaurant.settings') }}" 
                       id="tour-parametres"
                       class="sidebar-item {{ request()->routeIs('restaurant.settings*') ? 'sidebar-item-active' : '' }}">
                        <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span x-show="expanded" x-transition class="whitespace-nowrap">Paramètres</span>
                    </a>
                    @endif
                </nav>

                <!-- Subscription Status -->
                <div x-show="expanded" x-transition class="p-4 border-t border-neutral-800">
                    @if(isset($subscription) && $subscription && $subscription->daysRemaining <= 7)
                        <div class="bg-accent-500/10 border border-accent-500/20 rounded-xl p-4">
                            <div class="flex items-center gap-2 text-accent-400 text-sm font-medium mb-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                Abonnement
                            </div>
                            <p class="text-neutral-400 text-xs">
                                Expire dans {{ $subscription->daysRemaining }} jour(s)
                            </p>
                            <a href="{{ route('restaurant.subscription') }}" class="btn btn-accent btn-sm w-full mt-3">
                                Renouveler
                            </a>
                        </div>
                    @elseif(isset($restaurant) && $restaurant?->currentPlan)
                        <div class="bg-secondary-500/10 border border-secondary-500/20 rounded-xl p-4">
                            <div class="flex items-center gap-2 text-secondary-400 text-sm font-medium">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Plan {{ $restaurant->currentPlan->name ?? 'Actif' }}
                            </div>
                        </div>
                    @else
                        <div class="bg-neutral-500/10 border border-neutral-500/20 rounded-xl p-4">
                            <div class="flex items-center gap-2 text-neutral-400 text-sm font-medium">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Abonnement inactif
                            </div>
                        </div>
                    @endif
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
               class="fixed left-0 top-0 h-full w-64 bg-neutral-900 border-r border-neutral-800 z-40 lg:hidden flex flex-col"
               x-cloak>
            <div class="h-16 flex items-center justify-center border-b border-neutral-800 flex-shrink-0 px-4">
                <a href="{{ route('restaurant.dashboard') }}" class="flex items-center" @click="mobileOpen = false">
                    <img src="{{ asset('images/logo-menupro.png') }}" alt="MenuPro" class="h-8 w-auto object-contain">
                </a>
            </div>
            <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1" @click="mobileOpen = false">
                <a href="{{ route('restaurant.dashboard') }}" class="sidebar-item {{ request()->routeIs('restaurant.dashboard') ? 'sidebar-item-active' : '' }}"><svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg><span>Dashboard</span></a>
                @if($isAdmin)<a href="{{ route('restaurant.categories.index') }}" class="sidebar-item {{ request()->routeIs('restaurant.categories*') ? 'sidebar-item-active' : '' }}"><svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/></svg><span>Catégories</span></a>@endif
                <a href="{{ route('restaurant.plats.index') }}" class="sidebar-item {{ request()->routeIs('restaurant.plats*') ? 'sidebar-item-active' : '' }}"><svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg><span>Plats</span></a>
                <a href="{{ route('restaurant.qrcode') }}" class="sidebar-item {{ request()->routeIs('restaurant.qrcode*') ? 'sidebar-item-active' : '' }}"><svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg><span>QR Code</span></a>
                <a href="{{ route('restaurant.orders') }}" class="sidebar-item {{ request()->routeIs('restaurant.orders*') ? 'sidebar-item-active' : '' }}"><svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg><span>Commandes</span>@if(isset($pendingOrders) && $pendingOrders > 0)<span class="ml-auto bg-accent-500 text-white text-xs px-2 py-0.5 rounded-full">{{ $pendingOrders }}</span>@endif</a>
                <a href="{{ route('restaurant.pos') }}" class="sidebar-item {{ request()->routeIs('restaurant.pos*') ? 'sidebar-item-active' : '' }}"><svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg><span>Caisse (POS)</span></a>
                @if($isAdmin)<a href="{{ route('restaurant.customers') }}" class="sidebar-item {{ request()->routeIs('restaurant.customers*') ? 'sidebar-item-active' : '' }}"><svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg><span>Clients</span></a>
                <a href="{{ route('restaurant.promo-codes') }}" class="sidebar-item {{ request()->routeIs('restaurant.promo-codes*') ? 'sidebar-item-active' : '' }}"><svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg><span>Codes Promo</span></a>
                <a href="{{ route('restaurant.analytics') }}" class="sidebar-item {{ request()->routeIs('restaurant.analytics*') ? 'sidebar-item-active' : '' }}"><svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg><span>Statistiques</span></a>
                <a href="{{ route('restaurant.reports') }}" class="sidebar-item {{ request()->routeIs('restaurant.reports*') ? 'sidebar-item-active' : '' }}"><svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg><span>Rapports</span></a>
                <a href="{{ route('restaurant.reservations.index') }}" class="sidebar-item {{ request()->routeIs('restaurant.reservations*') ? 'sidebar-item-active' : '' }}"><svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg><span>Réservations</span>@if(isset($restaurant) && $restaurant->reservations()->pending()->count() > 0)<span class="ml-auto bg-accent-500 text-white text-xs px-2 py-0.5 rounded-full">{{ $restaurant->reservations()->pending()->count() }}</span>@endif</a>
                <a href="{{ route('restaurant.reviews') }}" class="sidebar-item {{ request()->routeIs('restaurant.reviews*') ? 'sidebar-item-active' : '' }}"><svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg><span>Avis</span></a>@endif
                @if(isset($restaurant) && $restaurant?->hasFeature('stock'))
                <a href="{{ route('restaurant.stock.ingredients.index') }}" class="sidebar-item {{ request()->routeIs('restaurant.stock.ingredients*') ? 'sidebar-item-active' : '' }}"><svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg><span>Ingrédients</span></a>
                @if($isAdmin)<a href="{{ route('restaurant.stock.fournisseurs.index') }}" class="sidebar-item {{ request()->routeIs('restaurant.stock.fournisseurs*') ? 'sidebar-item-active' : '' }}"><svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg><span>Fournisseurs</span></a>@endif
                <a href="{{ route('restaurant.stock.alerts') }}" class="sidebar-item {{ request()->routeIs('restaurant.stock.alerts*') ? 'sidebar-item-active' : '' }}"><svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg><span>Alertes Stock</span></a>
                @if($isAdmin)<a href="{{ route('restaurant.stock.report') }}" class="sidebar-item {{ request()->routeIs('restaurant.stock.report*') ? 'sidebar-item-active' : '' }}"><svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg><span>Rapport Stock</span></a>@endif
                @endif
                @if($isAdmin)<a href="{{ route('restaurant.team') }}" class="sidebar-item {{ request()->routeIs('restaurant.team*') ? 'sidebar-item-active' : '' }}"><svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg><span>Équipe</span></a>
                <a href="{{ route('restaurant.subscription') }}" class="sidebar-item {{ request()->routeIs('restaurant.subscription*') ? 'sidebar-item-active' : '' }}"><svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h2m2 0h.01M5 21h14a2 2 0 002-2V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg><span>Abonnement</span></a>
                <a href="{{ route('restaurant.taxes-fees') }}" class="sidebar-item {{ request()->routeIs('restaurant.taxes-fees*') ? 'sidebar-item-active' : '' }}"><svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-5m-3 5h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg><span>Taxes & Frais</span></a>
                <a href="{{ route('restaurant.settings') }}" class="sidebar-item {{ request()->routeIs('restaurant.settings*') ? 'sidebar-item-active' : '' }}"><svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg><span>Paramètres</span></a>@endif
            </nav>
            @if(isset($subscription) && $subscription && $subscription->daysRemaining <= 7)
            <div class="p-4 border-t border-neutral-800 flex-shrink-0">
                <div class="bg-accent-500/10 border border-accent-500/20 rounded-xl p-4">
                    <div class="flex items-center gap-2 text-accent-400 text-sm font-medium mb-1"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>Abonnement</div>
                    <p class="text-neutral-400 text-xs">Expire dans {{ $subscription->daysRemaining }} jour(s)</p>
                    <a href="{{ route('restaurant.subscription') }}" class="btn btn-accent btn-sm w-full mt-3" @click="mobileOpen = false">Renouveler</a>
                </div>
            </div>
            @elseif(isset($restaurant) && $restaurant?->currentPlan)
            <div class="p-4 border-t border-neutral-800 flex-shrink-0">
                <div class="bg-secondary-500/10 border border-secondary-500/20 rounded-xl p-4">
                    <div class="flex items-center gap-2 text-secondary-400 text-sm font-medium"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>Plan {{ $restaurant->currentPlan->name ?? 'Actif' }}</div>
                </div>
            </div>
            @else
            <div class="p-4 border-t border-neutral-800 flex-shrink-0">
                <div class="bg-neutral-500/10 border border-neutral-500/20 rounded-xl p-4">
                    <div class="flex items-center gap-2 text-neutral-400 text-sm font-medium"><svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>Abonnement inactif</div>
                </div>
            </div>
            @endif
        </aside>

        <!-- Main Content -->
        <div :class="expanded ? 'lg:ml-64' : 'lg:ml-20'" class="transition-all duration-300 ml-0">
            <!-- Top Bar -->
            <header class="sticky top-0 z-20 bg-white/95 backdrop-blur-md border-b border-neutral-200 shadow-sm">
                <div class="flex items-center justify-between h-14 sm:h-16 px-3 sm:px-4 lg:px-8 gap-2">
                    <!-- Mobile Menu Button -->
                    <button @click="toggleMobile()" class="lg:hidden p-2.5 -ml-1 rounded-lg hover:bg-neutral-100 min-h-[44px] min-w-[44px] flex items-center justify-center">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                        </svg>
                    </button>

                    <!-- Logo MenuPro (navbar) -->
                    <a href="{{ route('restaurant.dashboard') }}" class="hidden sm:flex items-center shrink-0 mr-3">
                        <img src="{{ asset('images/logo-menupro.png') }}" alt="MenuPro" class="h-7 w-auto object-contain">
                    </a>

                    <!-- Page Title (visible mobile + desktop) -->
                    <div class="flex-1 min-w-0">
                        <h1 class="text-base sm:text-xl font-bold text-neutral-900 truncate">{{ $title ?? 'Dashboard' }}</h1>
                    </div>

                    <!-- Right Section -->
                    <div class="flex items-center gap-4">
                        <!-- Tutoriel guidé -->
                        <button type="button" 
                                onclick="window.runRestaurantTour && window.runRestaurantTour()"
                                class="flex items-center justify-center w-9 h-9 rounded-lg border border-neutral-200 text-neutral-500 hover:bg-neutral-100 hover:text-primary-600 hover:border-primary-200 transition-colors"
                                title="Découvrir le tutoriel">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </button>
                        <!-- View Site Button -->
                        @if($restaurant?->slug)
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
                        @livewire('restaurant.notifications')

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
                                @if($isAdmin)
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
                                @endif
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
            <main class="p-3 sm:p-4 lg:p-6 xl:p-8 overflow-x-hidden">
                {{ $slot }}
            </main>
        </div>
    </div>

    @push('scripts')
    <script>
        window.__restaurantTourSteps = @json($tourSteps ?? []);
    </script>
    @endpush
</x-layouts.app>

