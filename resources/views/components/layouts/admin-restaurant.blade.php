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

    // Onglets selon le plan et l'état de l'abonnement — source unique : App\Support\RestaurantNavigation
    $navUser = auth()->user();
    $navItems = \App\Support\RestaurantNavigation::items($restaurant, $navUser);
    $navSections = \App\Support\RestaurantNavigation::sections($restaurant, $navUser);
    $navOpen = collect($navItems)->where('state', \App\Support\RestaurantNavigation::STATE_OK)->keyBy('key');
    // Barre du bas : 4 raccourcis fixes, le reste dans « Plus »
    $navPrimaryKeys = ['dashboard', 'orders', 'dishes', 'qrcode'];
    $navMoreItems = collect($navItems)->reject(fn ($i) => in_array($i['key'], $navPrimaryKeys, true))->values()->all();

    $navLimiter = $restaurant ? (clone app(\App\Services\PlanLimiter::class))->forRestaurant($restaurant) : null;
    $accessState = $navLimiter?->accessState();
    $isAccessLimited = $accessState === \App\Services\PlanLimiter::ACCESS_LIMITED;
    $trialDaysLeft = $accessState === \App\Services\PlanLimiter::ACCESS_TRIAL ? $navLimiter->trialDaysLeft() : null;
    $pendingPayment = $isAccessLimited ? $navLimiter->pendingSubscription() : null;

    $hasStock = $navOpen->has('stock-daily');
    $planName = $restaurant?->effectivePlan()?->slug ?? 'essentiel';
    $isStand = $planName === 'stand';

    // Tutoriel guidé : uniquement les onglets réellement affichés (et ouverts)
    $tourLabels = [
        'tour-dashboard' => ['Dashboard', 'Vue d\'ensemble : commandes du jour, chiffre d\'affaires et activité.'],
        'tour-categories' => ['Catégories', 'Organisez votre menu (Entrées, Plats, Boissons…).'],
        'tour-plats' => ['Plats', 'Ajoutez des plats, modifiez les prix, photos et disponibilité.'],
        'tour-qrcode' => ['QR Code', 'Téléchargez votre QR Code pour que les clients accèdent à votre menu.'],
        'tour-commandes' => ['Commandes', 'Gérez les commandes : statuts, impression des tickets.'],
        'tour-clients' => ['Clients', 'Liste des clients ayant commandé chez vous.'],
        'tour-codes-promo' => ['Codes Promo', 'Créez des réductions et promotions.'],
        'tour-statistiques' => ['Statistiques', 'Graphiques et analyses de votre activité.'],
        'tour-stock' => ['Stock', 'Suivez votre stock et vos ruptures.'],
        'tour-abonnement' => ['Abonnement', 'Votre plan, vos paiements Mobile Money et le renouvellement.'],
        'tour-parametres' => ['Paramètres', 'Configurez : infos, horaires, paiement, couleurs du site.'],
    ];
    $tourSteps = [['popover' => ['title' => 'Bienvenue sur MenuPro', 'description' => 'Ce tutoriel vous présente les onglets de votre tableau de bord. Cliquez sur Suivant pour découvrir chaque section.']]];
    foreach ($navOpen as $navItem) {
        if ($navItem['id'] && isset($tourLabels[$navItem['id']])) {
            [$tourTitle, $tourDescription] = $tourLabels[$navItem['id']];
            $tourSteps[] = ['element' => '#' . $navItem['id'], 'popover' => ['title' => $tourTitle, 'description' => $tourDescription]];
        }
    }
@endphp

<x-layouts.app :title="($title ?? 'Dashboard') . ' - ' . ($restaurant?->name ?? 'Restaurant')" manifestUrl="manifest-admin.json">
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

                <!-- Navigation : onglets selon le plan et l'état de l'abonnement (App\Support\RestaurantNavigation) -->
                <nav class="flex-1 overflow-y-auto py-6 px-3 space-y-2" data-sidebar="desktop">
                    @foreach($navSections as $section)
                        @if($section['title'])
                        <div class="pt-4">
                            <span x-show="expanded" x-transition class="px-4 text-xs font-semibold text-neutral-500 uppercase tracking-wider">{{ $section['title'] }}</span>
                        </div>
                        @endif
                        @foreach($section['items'] as $item)
                            <x-restaurant-nav.link :item="$item" :collapsible="true" />
                        @endforeach
                    @endforeach
                </nav>

                <!-- Subscription Status -->
                <div x-show="expanded" x-transition class="p-4 border-t border-neutral-800">
                    @if($isAccessLimited)
                        <div class="bg-red-500/10 border border-red-500/30 rounded-xl p-4">
                            <p class="text-red-300 text-sm font-medium">Abonnement expiré</p>
                            <p class="text-neutral-400 text-xs">Renouvelez pour réactiver votre espace.</p>
                            @if($isAdmin)<a href="{{ route('restaurant.subscription') }}" class="btn btn-accent btn-sm w-full mt-3">Renouveler</a>@endif
                        </div>
                    @elseif($trialDaysLeft !== null)
                        <div class="bg-primary-500/10 border border-primary-500/20 rounded-xl p-4">
                            <p class="text-primary-300 text-sm font-medium">Essai : {{ $trialDaysLeft }} j restant{{ $trialDaysLeft > 1 ? 's' : '' }}</p>
                            <p class="text-neutral-400 text-xs">Toutes les fonctions Pro sont ouvertes.</p>
                        </div>
                    @elseif(isset($subscription) && $subscription && $subscription->daysRemaining <= 7)
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
               class="fixed left-0 top-0 h-full w-64 bg-neutral-900 border-r border-neutral-800 z-40 lg:hidden flex flex-col pt-safe pb-safe pl-safe"
               x-cloak>
            <div class="h-16 flex items-center justify-center border-b border-neutral-800 flex-shrink-0 px-4">
                <a href="{{ route('restaurant.dashboard') }}" class="flex items-center" @click="mobileOpen = false">
                    <img src="{{ asset('images/logo-menupro.png') }}" alt="MenuPro" class="h-8 w-auto object-contain">
                </a>
            </div>
            <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1" @click="mobileOpen = false" data-sidebar="mobile">
                @foreach($navSections as $section)
                    @if($section['title'])
                        <p class="pt-3 pb-1 px-4 text-[11px] font-semibold text-neutral-500 uppercase tracking-wider">{{ $section['title'] }}</p>
                    @endif
                    @foreach($section['items'] as $item)
                        <x-restaurant-nav.link :item="$item" :with-id="false" />
                    @endforeach
                @endforeach
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
            <header class="sticky top-0 z-20 bg-white/95 backdrop-blur-md border-b border-neutral-200 shadow-sm header-safe">
                <div class="flex items-center justify-between h-14 sm:h-16 px-4 sm:px-6 lg:px-8 gap-2">
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
                        <!-- Sélecteur d'espace actif (Plan GOLD) -->
                        <x-space-selector />

                        <!-- Tutoriel guidé -->
                        <button type="button" 
                                onclick="window.runRestaurantTour && window.runRestaurantTour()"
                                class="flex items-center justify-center w-11 h-11 sm:w-9 sm:h-9 rounded-lg border border-neutral-200 text-neutral-500 hover:bg-neutral-100 hover:text-primary-600 hover:border-primary-200 transition-colors"
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
                                <a href="{{ route('restaurant.profile') }}" class="flex items-center gap-3 px-4 py-2 text-neutral-700 hover:bg-neutral-50">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                    Mon profil
                                </a>
                                @if($isAdmin)
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
            <main class="p-3 sm:p-4 lg:p-6 xl:p-8 overflow-x-hidden pb-bottom-nav">
                {{-- État de l'abonnement : expiré / en attente de paiement → accès limité ; essai → jours restants --}}
                @if($isAccessLimited)
                    <div class="mb-4 flex flex-col sm:flex-row sm:items-center gap-3 rounded-2xl border-2 border-red-300 bg-red-50 p-4" data-subscription-banner="limited" role="alert">
                        <div class="flex items-start gap-3 flex-1">
                            <svg class="w-6 h-6 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            <div class="text-sm">
                                <p class="font-bold text-red-900">Renouvelez pour réactiver votre espace</p>
                                <p class="text-red-800">
                                    @if($pendingPayment)
                                        Un paiement de {{ number_format($pendingPayment->amount_paid, 0, ',', ' ') }} F est en attente. Dès qu'il est confirmé, toutes vos fonctions reviennent automatiquement.
                                    @else
                                        Votre abonnement a expiré : seuls l'abonnement et les paramètres restent accessibles, et vos clients ne peuvent plus commander.
                                    @endif
                                </p>
                            </div>
                        </div>
                        @if($isAdmin && !request()->routeIs('restaurant.subscription'))
                            <a href="{{ route('restaurant.subscription') }}" class="inline-flex items-center justify-center min-h-[44px] px-5 rounded-xl bg-red-600 hover:bg-red-700 text-white text-sm font-bold shrink-0">Renouveler maintenant</a>
                        @endif
                    </div>
                @elseif($trialDaysLeft !== null)
                    <div class="mb-4 flex flex-col sm:flex-row sm:items-center gap-3 rounded-2xl border border-primary-200 bg-primary-50 p-3 sm:p-4" data-subscription-banner="trial">
                        <p class="flex-1 text-sm text-primary-900">
                            <strong>Essai : {{ $trialDaysLeft }} jour{{ $trialDaysLeft > 1 ? 's' : '' }} restant{{ $trialDaysLeft > 1 ? 's' : '' }}</strong>
                            — toutes les fonctions du plan Pro sont ouvertes pendant l'essai.
                        </p>
                        @if($isAdmin && !request()->routeIs('restaurant.subscription*'))
                            <a href="{{ route('restaurant.subscription') }}" class="inline-flex items-center justify-center min-h-[44px] px-4 rounded-xl bg-primary-600 hover:bg-primary-700 text-white text-sm font-semibold shrink-0">Choisir mon plan</a>
                        @endif
                    </div>
                @endif

                {{-- Notifications push : indispensable pour être alerté des commandes appli fermée --}}
                <div x-data="pushOptin()" x-cloak>
                    <div x-show="visible" class="mb-4 flex flex-col sm:flex-row sm:items-center gap-3 rounded-xl border border-orange-200 bg-orange-50 p-4">
                        <div class="flex-1 text-sm text-neutral-800">
                            <p class="font-semibold">🔔 Activez les notifications sur cet appareil</p>
                            <p class="text-neutral-600">Vous serez alerté de chaque nouvelle commande, même quand MenuPro est fermé.</p>
                        </div>
                        <button type="button" @click="enable()" :disabled="loading"
                                class="px-4 py-2 rounded-lg bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold disabled:opacity-60 shrink-0">
                            <span x-text="loading ? 'Activation…' : 'Activer'"></span>
                        </button>
                    </div>
                    <div x-show="denied" class="mb-4 rounded-xl border border-red-200 bg-red-50 p-4 text-sm text-red-800">
                        <p class="font-semibold">Notifications bloquées sur cet appareil</p>
                        <p>Vous ne serez pas alerté des commandes quand MenuPro est fermé. Autorisez les notifications dans les réglages du navigateur (icône 🔒 à gauche de l'adresse) ou du téléphone (Réglages › Notifications › MenuPro), puis rechargez la page.</p>
                    </div>
                    {{-- iPhone / iPad : le push web n'existe que dans l'appli installée sur l'écran d'accueil --}}
                    <div x-show="iosInstall" class="mb-4 rounded-xl border border-orange-200 bg-orange-50 p-4 text-sm text-neutral-800">
                        <p class="font-semibold">📲 Sur iPhone, installez MenuPro pour être alerté des commandes</p>
                        <p class="text-neutral-600 mt-1">Les notifications ne fonctionnent que depuis l'icône MenuPro de l'écran d'accueil :</p>
                        <ol class="mt-2 space-y-1.5 text-neutral-700">
                            <li class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-orange-500 text-white text-xs font-bold flex items-center justify-center shrink-0">1</span>
                                <span>Dans Safari, appuyez sur
                                    <svg class="inline w-5 h-5 -mt-1 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-label="Partager"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v12m0-12L8 7m4-4l4 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/></svg>
                                    <strong>Partager</strong></span>
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-orange-500 text-white text-xs font-bold flex items-center justify-center shrink-0">2</span>
                                <span>Choisissez <strong>« Sur l'écran d'accueil »</strong>, puis <strong>Ajouter</strong></span>
                            </li>
                            <li class="flex items-center gap-2">
                                <span class="w-6 h-6 rounded-full bg-orange-500 text-white text-xs font-bold flex items-center justify-center shrink-0">3</span>
                                <span>Ouvrez MenuPro depuis l'icône et appuyez sur <strong>Activer</strong></span>
                            </li>
                        </ol>
                    </div>
                </div>

                {{ $slot }}
            </main>
        </div>

        {{-- Bottom Navigation Bar (Mobile) --}}
        <nav class="fixed bottom-0 left-0 right-0 z-50 lg:hidden bg-white/95 backdrop-blur-lg border-t border-neutral-200 shadow-[0_-4px_20px_rgba(0,0,0,0.08)] bottom-nav-safe"
             data-bottom-nav
             x-data="{ more: false }">
            @php
                $menuActive = request()->routeIs('restaurant.plats*') || request()->routeIs('restaurant.categories*');
            @endphp
            <div class="flex items-center px-2 py-1 max-w-lg mx-auto">

                @if($isAccessLimited)
                {{-- Abonnement expiré : seuls l'abonnement et les paramètres restent ouverts --}}
                @foreach($navItems as $item)
                    <a href="{{ $item['url'] }}" data-nav="{{ $item['key'] }}"
                       class="relative flex-1 flex flex-col items-center justify-center min-h-[52px] py-2 rounded-xl {{ $item['is_active'] ? 'text-primary-600' : 'text-neutral-500' }} active:scale-95 transition-all touch-manipulation">
                        <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon_path'] }}"/></svg>
                        <span class="text-[11px] font-semibold leading-tight">{{ $item['label'] }}</span>
                    </a>
                @endforeach
                <a href="{{ route('restaurant.profile') }}" class="relative flex-1 flex flex-col items-center justify-center min-h-[52px] py-2 rounded-xl {{ request()->routeIs('restaurant.profile') ? 'text-primary-600' : 'text-neutral-500' }}">
                    <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ \App\Support\RestaurantNavigation::ICONS['user'] }}"/></svg>
                    <span class="text-[11px] font-semibold leading-tight">Profil</span>
                </a>
                @else
                {{-- Dashboard --}}
                <a href="{{ route('restaurant.dashboard') }}"
                   class="relative flex-1 flex flex-col items-center justify-center py-2 rounded-xl {{ request()->routeIs('restaurant.dashboard') ? 'text-primary-600' : 'text-neutral-500' }} active:scale-95 transition-all touch-manipulation">
                    @if(request()->routeIs('restaurant.dashboard'))
                        <span class="absolute inset-x-2 top-0 h-0.5 bg-primary-500 rounded-full"></span>
                    @endif
                    <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    <span class="text-[10px] font-semibold leading-tight">Dashboard</span>
                </a>

                {{-- Commandes --}}
                <a href="{{ route('restaurant.orders') }}"
                   class="relative flex-1 flex flex-col items-center justify-center py-2 rounded-xl {{ request()->routeIs('restaurant.orders*') ? 'text-primary-600' : 'text-neutral-500' }} active:scale-95 transition-all touch-manipulation">
                    @if(request()->routeIs('restaurant.orders*'))
                        <span class="absolute inset-x-2 top-0 h-0.5 bg-primary-500 rounded-full"></span>
                    @endif
                    <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                    </svg>
                    <span class="text-[10px] font-semibold leading-tight">Commandes</span>
                    @if(isset($pendingOrders) && $pendingOrders > 0)
                        <span class="absolute top-1 right-2 w-4 h-4 text-white text-[9px] font-bold rounded-full flex items-center justify-center" style="background:#ef4444">{{ $pendingOrders > 9 ? '9+' : $pendingOrders }}</span>
                    @endif
                </a>

                {{-- Menu FAB (centre) --}}
                <a href="{{ route('restaurant.plats.index') }}"
                   class="relative flex flex-col items-center justify-center -mt-5 px-2 flex-shrink-0 active:scale-95 transition-all touch-manipulation">
                    <span class="flex items-center justify-center w-14 h-14 rounded-2xl text-white transition-all"
                          style="{{ $menuActive ? 'background:#D45E0C;box-shadow:0 4px 16px rgba(212,94,12,0.4)' : 'background:#1a1614;box-shadow:0 4px 14px rgba(0,0,0,0.25)' }}">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </span>
                    <span class="text-[10px] font-semibold mt-1 leading-tight {{ $menuActive ? 'text-primary-600' : 'text-neutral-500' }}">Menu</span>
                </a>

                {{-- QR Code --}}
                <a href="{{ route('restaurant.qrcode') }}"
                   class="relative flex-1 flex flex-col items-center justify-center py-2 rounded-xl {{ request()->routeIs('restaurant.qrcode*') ? 'text-primary-600' : 'text-neutral-500' }} active:scale-95 transition-all touch-manipulation">
                    @if(request()->routeIs('restaurant.qrcode*'))
                        <span class="absolute inset-x-2 top-0 h-0.5 bg-primary-500 rounded-full"></span>
                    @endif
                    <svg class="w-5 h-5 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                    </svg>
                    <span class="text-[10px] font-semibold leading-tight">QR Code</span>
                </a>

                {{-- Plus --}}
                <button @click="more = !more"
                        class="relative flex-1 flex flex-col items-center justify-center py-2 rounded-xl active:scale-95 transition-all touch-manipulation"
                        :class="more ? 'text-primary-600' : 'text-neutral-500'">
                    <span x-show="more" class="absolute inset-x-2 top-0 h-0.5 bg-primary-500 rounded-full" x-cloak></span>
                    <svg class="w-5 h-5 mb-1 transition-transform duration-200" :class="more ? 'rotate-45' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zm10 0a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                    </svg>
                    <span class="text-[10px] font-semibold leading-tight">Plus</span>
                </button>
                @endif
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
                <div class="grid grid-cols-3 sm:grid-cols-4 gap-2 mb-3 max-h-[60vh] overflow-y-auto" data-bottom-more>
                    @foreach($navMoreItems as $item)
                        @php $locked = $item['state'] === \App\Support\RestaurantNavigation::STATE_LOCKED; @endphp
                        <a href="{{ $item['url'] }}" @click="more = false" data-nav="{{ $item['key'] }}" data-nav-state="{{ $item['state'] }}"
                           class="relative flex flex-col items-center gap-1.5 p-2 min-h-[72px] rounded-xl hover:bg-neutral-50 {{ $item['is_active'] ? 'bg-primary-50' : '' }} {{ $locked ? 'opacity-75' : '' }}">
                            <span class="w-10 h-10 rounded-xl {{ $locked ? 'bg-neutral-100' : 'bg-primary-50' }} flex items-center justify-center relative">
                                <svg class="w-5 h-5 {{ $locked ? 'text-neutral-400' : 'text-primary-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $item['icon_path'] }}"/></svg>
                                @if($locked)
                                    <span class="absolute -top-1 -right-1 w-5 h-5 rounded-full bg-amber-500 text-white flex items-center justify-center" aria-hidden="true">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/></svg>
                                    </span>
                                @elseif($item['count'] > 0)
                                    <span class="absolute -top-1 -right-1 min-w-[18px] h-[18px] px-1 rounded-full bg-accent-500 text-white text-[10px] font-bold flex items-center justify-center">{{ $item['count'] }}</span>
                                @endif
                            </span>
                            <span class="text-[11px] font-medium text-neutral-700 text-center leading-tight">{{ $item['label'] }}</span>
                            @if($locked)
                                <span class="text-[9px] font-bold text-amber-600 leading-tight">Plan {{ $item['upgrade_plan']?->name }}</span>
                            @endif
                        </a>
                    @endforeach
                    <button @click="more = false; $dispatch('open-notifications')" class="flex flex-col items-center gap-1.5 p-2 min-h-[72px] rounded-xl hover:bg-neutral-50 w-full">
                        <span class="w-10 h-10 rounded-xl bg-primary-100 flex items-center justify-center relative">
                            <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        </span>
                        <span class="text-[11px] font-medium text-neutral-700">Notifications</span>
                    </button>
                </div>
            </div>
        </nav>
    </div>

    @push('scripts')
    <script>
        window.__restaurantTourSteps = @json($tourSteps ?? []);

        // Pause all Livewire polls when tab is hidden, resume when visible
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                if (typeof Livewire !== 'undefined' && Livewire.pausePolling) {
                    Livewire.pausePolling();
                }
            } else {
                if (typeof Livewire !== 'undefined' && Livewire.resumePolling) {
                    Livewire.resumePolling();
                }
            }
        });
    </script>
    @endpush
</x-layouts.app>

