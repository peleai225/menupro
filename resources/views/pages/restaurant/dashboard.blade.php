<x-layouts.admin-restaurant title="Dashboard">
    {{-- Initialize Alpine data for animations --}}
    <div x-data="{ 
        loaded: false,
        stats: {
            orders: {{ $stats['ordersToday'] ?? 0 }},
            revenue: {{ $stats['revenueToday'] ?? 0 }},
            pending: {{ $stats['pendingOrders'] ?? 0 }},
            dishes: {{ $stats['totalDishes'] ?? 0 }}
        },
        animatedStats: { orders: 0, revenue: 0, pending: 0, dishes: 0 }
    }" x-init="
        loaded = true;
        // Animate numbers on load
        setTimeout(() => {
            const duration = 1000;
            const steps = 30;
            const interval = duration / steps;
            let step = 0;
            const timer = setInterval(() => {
                step++;
                const progress = step / steps;
                const eased = 1 - Math.pow(1 - progress, 3);
                animatedStats.orders = Math.round(stats.orders * eased);
                animatedStats.revenue = Math.round(stats.revenue * eased);
                animatedStats.pending = Math.round(stats.pending * eased);
                animatedStats.dishes = Math.round(stats.dishes * eased);
                if (step >= steps) clearInterval(timer);
            }, interval);
        }, 300);
    " class="space-y-8">

        <!-- Flash Messages with better animations -->
        @if(session('success'))
            <div x-data="{ show: true }" x-show="show" x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform -translate-y-2"
                 x-transition:enter-end="opacity-100 transform translate-y-0"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100"
                 x-transition:leave-end="opacity-0"
                 x-init="setTimeout(() => show = false, 5000)"
                 class="p-4 bg-gradient-to-r from-secondary-50 to-secondary-100 border border-secondary-200 rounded-2xl shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-secondary-500 rounded-xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <p class="text-secondary-800 font-medium flex-1">{{ session('success') }}</p>
                    <button @click="show = false" class="p-1 hover:bg-secondary-200 rounded-lg transition-colors">
                        <svg class="w-5 h-5 text-secondary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
        @endif

        @if(session('error'))
            <div x-data="{ show: true }" x-show="show" x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 transform -translate-y-2"
                 x-transition:enter-end="opacity-100 transform translate-y-0"
                 class="p-4 bg-gradient-to-r from-red-50 to-red-100 border border-red-200 rounded-2xl shadow-sm">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 bg-red-500 rounded-xl flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <p class="text-red-800 font-medium flex-1">{{ session('error') }}</p>
                    <button @click="show = false" class="p-1 hover:bg-red-200 rounded-lg transition-colors">
                        <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
            </div>
        @endif

        <!-- Trial Alert -->
        @php
            $subscription = $restaurant->activeSubscription ?? null;
            $isTrial = $subscription && $subscription->isTrial();
            $daysLeft = $isTrial && $subscription->ends_at ? max(0, now()->diffInDays($subscription->ends_at, false)) : 0;
        @endphp

        @if($isTrial && $subscription)
            <div x-show="loaded" x-transition:enter="transition ease-out duration-500 delay-100"
                 x-transition:enter-start="opacity-0 transform -translate-y-4"
                 x-transition:enter-end="opacity-100 transform translate-y-0"
                 class="relative overflow-hidden bg-gradient-to-r from-blue-500 via-indigo-500 to-purple-500 rounded-2xl p-6 text-white shadow-lg">
                <!-- Background decoration -->
                <div class="absolute top-0 right-0 w-64 h-64 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/2"></div>
                <div class="absolute bottom-0 left-0 w-32 h-32 bg-white/10 rounded-full translate-y-1/2 -translate-x-1/2"></div>
                
                <div class="relative flex flex-col md:flex-row md:items-center md:justify-between gap-4">
                    <div class="flex items-start gap-4">
                        <div class="w-14 h-14 bg-white/20 backdrop-blur rounded-2xl flex items-center justify-center flex-shrink-0">
                            <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold mb-1">Essai gratuit en cours</h3>
                            <p class="text-white/90">
                                @if($daysLeft > 0)
                                    Il vous reste <span class="font-bold text-yellow-300">{{ $daysLeft }} jour(s)</span> pour profiter de toutes les fonctionnalités.
                                @else
                                    Votre essai expire aujourd'hui !
                                @endif
                            </p>
                        </div>
                    </div>
                    <a href="{{ route('restaurant.subscription') }}" 
                       class="inline-flex items-center gap-2 px-6 py-3 bg-white text-indigo-600 rounded-xl font-semibold hover:bg-indigo-50 transition-all shadow-lg hover:shadow-xl hover:-translate-y-0.5">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                        Passer au plan payant
                    </a>
                </div>
            </div>
        @endif

        <!-- Platform Announcements -->
        @if(isset($announcements) && $announcements->isNotEmpty())
            <div class="space-y-3">
                @foreach($announcements as $announcement)
                    @php
                        $colors = [
                            'info' => ['bg' => 'from-blue-50 to-blue-100', 'border' => 'border-blue-200', 'icon' => 'bg-blue-500', 'text' => 'text-blue-800', 'dismiss' => 'hover:bg-blue-200 text-blue-600'],
                            'warning' => ['bg' => 'from-yellow-50 to-amber-100', 'border' => 'border-yellow-200', 'icon' => 'bg-yellow-500', 'text' => 'text-yellow-800', 'dismiss' => 'hover:bg-yellow-200 text-yellow-600'],
                            'success' => ['bg' => 'from-green-50 to-emerald-100', 'border' => 'border-green-200', 'icon' => 'bg-green-500', 'text' => 'text-green-800', 'dismiss' => 'hover:bg-green-200 text-green-600'],
                            'danger' => ['bg' => 'from-red-50 to-rose-100', 'border' => 'border-red-200', 'icon' => 'bg-red-500', 'text' => 'text-red-800', 'dismiss' => 'hover:bg-red-200 text-red-600'],
                        ];
                        $style = $colors[$announcement->type] ?? $colors['info'];
                    @endphp
                    <div x-data="{ show: true }" 
                         x-show="show" 
                         x-transition:enter="transition ease-out duration-300"
                         x-transition:enter-start="opacity-0 transform -translate-y-2"
                         x-transition:enter-end="opacity-100 transform translate-y-0"
                         x-transition:leave="transition ease-in duration-200"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0"
                         class="p-4 bg-gradient-to-r {{ $style['bg'] }} border {{ $style['border'] }} rounded-2xl shadow-sm">
                        <div class="flex items-start gap-3">
                            <div class="w-10 h-10 {{ $style['icon'] }} rounded-xl flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $announcement->type_icon }}"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <h4 class="font-semibold {{ $style['text'] }}">{{ $announcement->title }}</h4>
                                <p class="{{ $style['text'] }} opacity-80 text-sm mt-1">{{ $announcement->content }}</p>
                            </div>
                            @if($announcement->is_dismissible)
                                <form action="{{ route('restaurant.dismiss-announcement', $announcement) }}" method="POST">
                                    @csrf
                                    <button type="submit" @click.prevent="show = false; $el.closest('form').submit()" 
                                            class="p-1.5 {{ $style['dismiss'] }} rounded-lg transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </form>
                            @endif
                        </div>
                    </div>
                @endforeach
            </div>
        @endif

        <!-- Welcome Banner with enhanced design -->
        <div x-show="loaded" x-transition:enter="transition ease-out duration-500 delay-200"
             x-transition:enter-start="opacity-0 transform -translate-y-4"
             x-transition:enter-end="opacity-100 transform translate-y-0"
             class="relative overflow-hidden bg-gradient-to-br from-primary-500 via-primary-600 to-primary-700 rounded-3xl p-8 text-white shadow-xl">
            <!-- Background patterns -->
            <div class="absolute inset-0 opacity-10">
                <svg class="w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                    <defs>
                        <pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse">
                            <path d="M 10 0 L 0 0 0 10" fill="none" stroke="white" stroke-width="0.5"/>
                        </pattern>
                    </defs>
                    <rect width="100%" height="100%" fill="url(#grid)"/>
                </svg>
            </div>
            <div class="absolute top-0 right-0 w-96 h-96 bg-white/10 rounded-full -translate-y-1/2 translate-x-1/3 blur-3xl"></div>
            <div class="absolute bottom-0 left-0 w-64 h-64 bg-primary-400/30 rounded-full translate-y-1/2 -translate-x-1/3 blur-2xl"></div>

            <div class="relative flex flex-col lg:flex-row lg:items-center lg:justify-between gap-6">
                <div>
                    <div class="flex items-center gap-3 mb-2">
                        <span class="px-3 py-1 bg-white/20 backdrop-blur rounded-full text-sm font-medium">
                            {{ now()->locale('fr')->isoFormat('dddd D MMMM YYYY') }}
                        </span>
                    </div>
                    <h1 class="text-3xl lg:text-4xl font-bold mb-2">
                        Bonjour, {{ auth()->user()->name ?? 'Restaurateur' }}
                    </h1>
                    <p class="text-primary-100 text-lg">
                        Voici un résumé de l'activité de <span class="font-semibold">{{ $restaurant->name ?? 'votre restaurant' }}</span> aujourd'hui.
                    </p>
                </div>
                
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('restaurant.orders') }}" 
                       class="inline-flex items-center gap-2 px-5 py-3 bg-white/20 backdrop-blur hover:bg-white/30 rounded-xl font-medium transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        Voir les commandes
                    </a>
                    @if($restaurant->slug)
                        <a href="{{ route('r.menu', $restaurant->slug) }}" target="_blank"
                           class="inline-flex items-center gap-2 px-5 py-3 bg-white text-primary-600 rounded-xl font-semibold hover:bg-primary-50 transition-all shadow-lg">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                            Voir mon menu
                        </a>
                    @endif
                </div>
            </div>
        </div>

        <!-- Stats Cards with animations -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
            <!-- Orders Today -->
            <div x-show="loaded" x-transition:enter="transition ease-out duration-500 delay-300"
                 x-transition:enter-start="opacity-0 transform translate-y-4"
                 x-transition:enter-end="opacity-100 transform translate-y-0"
                 class="stat-card group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="stat-card-label">Commandes aujourd'hui</p>
                        <p class="stat-card-value" x-text="animatedStats.orders.toLocaleString('fr-FR')">0</p>
                        @if(($stats['ordersGrowth'] ?? 0) != 0)
                            <p class="stat-card-trend {{ ($stats['ordersGrowth'] ?? 0) >= 0 ? 'stat-card-trend-up' : 'stat-card-trend-down' }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    @if(($stats['ordersGrowth'] ?? 0) >= 0)
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                    @else
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                                    @endif
                                </svg>
                                {{ ($stats['ordersGrowth'] ?? 0) >= 0 ? '+' : '' }}{{ $stats['ordersGrowth'] ?? 0 }}% vs hier
                            </p>
                        @else
                            <p class="stat-card-trend text-neutral-500">Pas de données hier</p>
                        @endif
                    </div>
                    <div class="stat-card-icon bg-primary-100 group-hover:bg-primary-200">
                        <svg class="w-7 h-7 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Revenue Today -->
            <div x-show="loaded" x-transition:enter="transition ease-out duration-500 delay-400"
                 x-transition:enter-start="opacity-0 transform translate-y-4"
                 x-transition:enter-end="opacity-100 transform translate-y-0"
                 class="stat-card group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="stat-card-label">Chiffre d'affaires</p>
                        <p class="stat-card-value">
                            <span x-text="animatedStats.revenue.toLocaleString('fr-FR')">0</span>
                            <span class="text-lg font-normal text-neutral-500">F</span>
                        </p>
                        @if(($stats['revenueGrowth'] ?? 0) != 0)
                            <p class="stat-card-trend {{ ($stats['revenueGrowth'] ?? 0) >= 0 ? 'stat-card-trend-up' : 'stat-card-trend-down' }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    @if(($stats['revenueGrowth'] ?? 0) >= 0)
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                    @else
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                                    @endif
                                </svg>
                                {{ ($stats['revenueGrowth'] ?? 0) >= 0 ? '+' : '' }}{{ $stats['revenueGrowth'] ?? 0 }}% vs hier
                            </p>
                        @else
                            <p class="stat-card-trend text-neutral-500">Pas de données hier</p>
                        @endif
                    </div>
                    <div class="stat-card-icon bg-secondary-100 group-hover:bg-secondary-200">
                        <svg class="w-7 h-7 text-secondary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Pending Orders -->
            <div x-show="loaded" x-transition:enter="transition ease-out duration-500 delay-500"
                 x-transition:enter-start="opacity-0 transform translate-y-4"
                 x-transition:enter-end="opacity-100 transform translate-y-0"
                 class="stat-card group {{ ($stats['pendingOrders'] ?? 0) > 0 ? 'ring-2 ring-accent-500/50' : '' }}">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="stat-card-label">En attente</p>
                        <p class="stat-card-value" x-text="animatedStats.pending.toLocaleString('fr-FR')">0</p>
                        @if(($stats['pendingOrders'] ?? 0) > 0)
                            <p class="stat-card-trend text-accent-600">
                                <span class="w-2 h-2 bg-accent-500 rounded-full animate-pulse inline-block mr-1"></span>
                                À traiter maintenant
                            </p>
                        @else
                            <p class="stat-card-trend text-secondary-600">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Tout est à jour !
                            </p>
                        @endif
                    </div>
                    <div class="stat-card-icon {{ ($stats['pendingOrders'] ?? 0) > 0 ? 'bg-accent-100 group-hover:bg-accent-200' : 'bg-neutral-100 group-hover:bg-neutral-200' }}">
                        <svg class="w-7 h-7 {{ ($stats['pendingOrders'] ?? 0) > 0 ? 'text-accent-600' : 'text-neutral-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Total Dishes -->
            <div x-show="loaded" x-transition:enter="transition ease-out duration-500 delay-600"
                 x-transition:enter-start="opacity-0 transform translate-y-4"
                 x-transition:enter-end="opacity-100 transform translate-y-0"
                 class="stat-card group">
                <div class="flex items-center justify-between">
                    <div>
                        <p class="stat-card-label">Plats au menu</p>
                        <p class="stat-card-value" x-text="animatedStats.dishes.toLocaleString('fr-FR')">0</p>
                        @php
                            $maxDishes = $restaurant->currentPlan->max_dishes ?? 50;
                            $dishPercent = $maxDishes > 0 ? min(100, round(($stats['totalDishes'] ?? 0) / $maxDishes * 100)) : 0;
                        @endphp
                        <div class="mt-2">
                            <div class="progress-bar w-24">
                                <div class="progress-bar-fill bg-primary-500" style="width: {{ $dishPercent }}%"></div>
                            </div>
                            <p class="text-xs text-neutral-500 mt-1">{{ $stats['totalDishes'] ?? 0 }} / {{ $maxDishes }} autorisés</p>
                        </div>
                    </div>
                    <div class="stat-card-icon bg-neutral-100 group-hover:bg-neutral-200">
                        <svg class="w-7 h-7 text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                </div>
            </div>
        </div>

        <!-- Main Content Grid -->
        <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
            <!-- Recent Orders -->
            <div class="xl:col-span-2" x-show="loaded" x-transition:enter="transition ease-out duration-500 delay-700"
                 x-transition:enter-start="opacity-0 transform translate-y-4"
                 x-transition:enter-end="opacity-100 transform translate-y-0">
                <div class="card overflow-hidden">
                    <div class="p-6 border-b border-neutral-100 bg-gradient-to-r from-neutral-50 to-white">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-xl font-bold text-neutral-900">Commandes récentes</h2>
                                <p class="text-sm text-neutral-500 mt-1">Les dernières commandes de votre restaurant</p>
                            </div>
                            <a href="{{ route('restaurant.orders') }}" 
                               class="inline-flex items-center gap-2 px-4 py-2 text-primary-600 hover:bg-primary-50 rounded-xl transition-colors font-medium">
                                Voir tout
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </a>
                        </div>
                    </div>

                    @if(isset($recentOrders) && $recentOrders->count() > 0)
                        <div class="divide-y divide-neutral-100">
                            @foreach($recentOrders as $index => $order)
                                <a href="{{ route('restaurant.orders.show', $order) }}" 
                                   class="list-item-interactive flex items-center justify-between gap-4"
                                   style="animation-delay: {{ ($index + 1) * 100 }}ms">
                                    <div class="flex items-center gap-4 min-w-0">
                                        <div class="avatar avatar-md bg-gradient-to-br from-primary-400 to-primary-600 text-white flex-shrink-0">
                                            {{ strtoupper(substr($order->customer_name ?? 'C', 0, 1)) }}
                                        </div>
                                        <div class="min-w-0">
                                            <p class="font-semibold text-neutral-900 truncate">{{ $order->customer_name ?? 'Client anonyme' }}</p>
                                            <p class="text-sm text-neutral-500">
                                                #{{ $order->order_number }} · {{ $order->created_at->locale('fr')->diffForHumans() }}
                                            </p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-4 flex-shrink-0">
                                        <span class="font-bold text-neutral-900 tabular-nums">{{ number_format($order->total, 0, ',', ' ') }} F</span>
                                        @php
                                            $statusConfig = [
                                                'pending' => ['bg' => 'bg-yellow-100', 'text' => 'text-yellow-700', 'label' => 'En attente', 'dot' => 'bg-yellow-500'],
                                                'confirmed' => ['bg' => 'bg-blue-100', 'text' => 'text-blue-700', 'label' => 'Confirmée', 'dot' => 'bg-blue-500'],
                                                'preparing' => ['bg' => 'bg-indigo-100', 'text' => 'text-indigo-700', 'label' => 'En préparation', 'dot' => 'bg-indigo-500'],
                                                'ready' => ['bg' => 'bg-primary-100', 'text' => 'text-primary-700', 'label' => 'Prêt', 'dot' => 'bg-primary-500'],
                                                'delivering' => ['bg' => 'bg-purple-100', 'text' => 'text-purple-700', 'label' => 'En livraison', 'dot' => 'bg-purple-500'],
                                                'completed' => ['bg' => 'bg-secondary-100', 'text' => 'text-secondary-700', 'label' => 'Terminée', 'dot' => 'bg-secondary-500'],
                                                'cancelled' => ['bg' => 'bg-red-100', 'text' => 'text-red-700', 'label' => 'Annulée', 'dot' => 'bg-red-500'],
                                            ];
                                            $status = $statusConfig[$order->status->value] ?? $statusConfig['pending'];
                                        @endphp
                                        <span class="badge-dot {{ $status['bg'] }} {{ $status['text'] }}">
                                            <span class="w-2 h-2 rounded-full {{ $status['dot'] }} {{ in_array($order->status->value, ['pending', 'preparing']) ? 'animate-pulse' : '' }}"></span>
                                            {{ $status['label'] }}
                                        </span>
                                    </div>
                                </a>
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state py-16">
                            <div class="w-20 h-20 mx-auto bg-neutral-100 rounded-full flex items-center justify-center mb-4">
                                <svg class="w-10 h-10 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                            </div>
                            <h3 class="empty-state-title">Aucune commande pour le moment</h3>
                            <p class="empty-state-description">
                                Les commandes de vos clients apparaîtront ici. Partagez votre menu pour commencer à recevoir des commandes !
                            </p>
                            <a href="{{ route('restaurant.qrcode') }}" class="btn btn-primary">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                                </svg>
                                Obtenir mon QR Code
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Right Sidebar -->
            <div class="space-y-6">
                <!-- Quick Actions -->
                <div x-show="loaded" x-transition:enter="transition ease-out duration-500 delay-800"
                     x-transition:enter-start="opacity-0 transform translate-y-4"
                     x-transition:enter-end="opacity-100 transform translate-y-0"
                     class="card p-6">
                    <h2 class="text-lg font-bold text-neutral-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        Actions rapides
                    </h2>
                    <div class="space-y-3">
                        <a href="{{ route('restaurant.plats.create') }}" class="action-item-primary bounce-click">
                            <div class="action-item-icon">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                            </div>
                            <span class="font-medium text-neutral-900">Ajouter un plat</span>
                            <svg class="w-4 h-4 text-neutral-400 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                        <a href="{{ route('restaurant.categories.index') }}" class="action-item-secondary bounce-click">
                            <div class="action-item-icon">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                </svg>
                            </div>
                            <span class="font-medium text-neutral-700">Gérer les catégories</span>
                            <svg class="w-4 h-4 text-neutral-400 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                        <a href="{{ route('restaurant.promo-codes') }}" class="action-item-secondary bounce-click">
                            <div class="action-item-icon">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                                </svg>
                            </div>
                            <span class="font-medium text-neutral-700">Créer un code promo</span>
                            <svg class="w-4 h-4 text-neutral-400 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                        <a href="{{ route('restaurant.settings') }}" class="action-item-secondary bounce-click">
                            <div class="action-item-icon">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                            </div>
                            <span class="font-medium text-neutral-700">Paramètres</span>
                            <svg class="w-4 h-4 text-neutral-400 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>
                </div>

                <!-- Best Sellers -->
                <div x-show="loaded" x-transition:enter="transition ease-out duration-500 delay-900"
                     x-transition:enter-start="opacity-0 transform translate-y-4"
                     x-transition:enter-end="opacity-100 transform translate-y-0"
                     class="card p-6">
                    <h2 class="text-lg font-bold text-neutral-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-yellow-500" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                        </svg>
                        Plats populaires
                    </h2>
                    
                    @if(isset($topDishes) && $topDishes->count() > 0)
                        <div class="space-y-4">
                            @foreach($topDishes as $index => $dish)
                                <div class="flex items-center gap-4 p-3 rounded-xl hover:bg-neutral-50 transition-colors group">
                                    <div class="relative flex-shrink-0">
                                        @if($dish->image)
                                            <img src="{{ Storage::url($dish->image) }}" 
                                                 alt="{{ $dish->name }}"
                                                 class="w-14 h-14 rounded-xl object-cover ring-2 ring-neutral-100 group-hover:ring-primary-200 transition-all">
                                        @else
                                            <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-neutral-100 to-neutral-200 flex items-center justify-center">
                                                <svg class="w-6 h-6 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                                </svg>
                                            </div>
                                        @endif
                                        @if($index < 3)
                                            <div class="absolute -top-1 -right-1 w-5 h-5 bg-gradient-to-br {{ $index == 0 ? 'from-yellow-400 to-yellow-500' : ($index == 1 ? 'from-neutral-300 to-neutral-400' : 'from-amber-600 to-amber-700') }} rounded-full flex items-center justify-center shadow">
                                                <span class="text-[10px] font-bold text-white">{{ $index + 1 }}</span>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-semibold text-neutral-900 truncate">{{ $dish->name }}</p>
                                        <p class="text-sm text-neutral-500">{{ $dish->orders_count ?? 0 }} commandes</p>
                                    </div>
                                    <div class="text-right flex-shrink-0">
                                        <p class="font-bold text-primary-600">{{ number_format($dish->price, 0, ',', ' ') }} F</p>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-8">
                            <div class="w-14 h-14 mx-auto bg-neutral-100 rounded-full flex items-center justify-center mb-3">
                                <svg class="w-7 h-7 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                            </div>
                            <p class="text-neutral-500 text-sm">Pas encore de plats populaires</p>
                        </div>
                    @endif
                </div>

                <!-- Restaurant Rating -->
                @if(isset($restaurant->average_rating) && $restaurant->average_rating > 0)
                    <div x-show="loaded" x-transition:enter="transition ease-out duration-500 delay-1000"
                         x-transition:enter-start="opacity-0 transform translate-y-4"
                         x-transition:enter-end="opacity-100 transform translate-y-0"
                         class="card p-6 bg-gradient-to-br from-yellow-50 to-orange-50 border border-yellow-200">
                        <div class="flex items-center gap-4">
                            <div class="w-16 h-16 bg-gradient-to-br from-yellow-400 to-orange-500 rounded-2xl flex items-center justify-center shadow-lg">
                                <svg class="w-8 h-8 text-white" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-yellow-800">Note moyenne</p>
                                <p class="text-3xl font-bold text-yellow-900">{{ number_format($restaurant->average_rating, 1) }}<span class="text-lg">/5</span></p>
                                <p class="text-sm text-yellow-700">{{ $restaurant->reviews_count ?? 0 }} avis</p>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
        </div>

    </div>
</x-layouts.admin-restaurant>
