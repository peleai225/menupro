<x-layouts.admin-super title="Dashboard">
    <!-- Live Dashboard Header -->
    <div class="flex items-center justify-between mb-8" x-data="liveDashboard()" x-init="startPolling()">
        <div>
            <h1 class="text-2xl font-bold text-white">Dashboard</h1>
            <p class="text-neutral-400 mt-1">Vue d'ensemble de la plateforme</p>
        </div>
        <div class="flex items-center gap-4">
            <div class="flex items-center gap-2 px-4 py-2 bg-secondary-500/20 border border-secondary-500/30 rounded-xl">
                <span class="w-2 h-2 bg-secondary-400 rounded-full animate-pulse"></span>
                <span class="text-sm font-medium text-secondary-400">Live</span>
                <span class="text-xs text-neutral-400" x-text="lastUpdate"></span>
            </div>
            <button @click="toggleLive()" :class="isLive ? 'bg-secondary-500' : 'bg-neutral-600'" 
                    class="p-2 rounded-lg text-white transition-colors" title="Activer/Désactiver le mode live">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- Header Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Total Restaurants -->
        <div class="bg-neutral-800/50 backdrop-blur border border-neutral-700 rounded-2xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-neutral-400">Restaurants actifs</p>
                    <p class="text-3xl font-bold text-white mt-1">{{ number_format($stats['restaurants']['active']) }}</p>
                    <p class="text-sm text-secondary-400 mt-2 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                        {{ $stats['restaurants']['total'] }} au total
                    </p>
                </div>
                <div class="w-14 h-14 bg-primary-500/20 rounded-2xl flex items-center justify-center">
                    <svg class="w-7 h-7 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- MRR -->
        <div class="bg-neutral-800/50 backdrop-blur border border-neutral-700 rounded-2xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-neutral-400">Revenus mensuels</p>
                    <p class="text-3xl font-bold text-white mt-1">
                        @if($stats['revenue']['this_month'] >= 1000000)
                            {{ number_format($stats['revenue']['this_month'] / 1000000, 1, ',', ' ') }}M
                        @else
                            {{ number_format($stats['revenue']['this_month'] / 1000, 0, ',', ' ') }}K
                        @endif
                        <span class="text-lg font-normal text-neutral-400">F</span>
                    </p>
                    <p class="text-sm text-secondary-400 mt-2 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                        {{ number_format($stats['revenue']['total'], 0, ',', ' ') }} F total
                    </p>
                </div>
                <div class="w-14 h-14 bg-secondary-500/20 rounded-2xl flex items-center justify-center">
                    <svg class="w-7 h-7 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Total Orders -->
        <div class="bg-neutral-800/50 backdrop-blur border border-neutral-700 rounded-2xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-neutral-400">Commandes totales</p>
                    <p class="text-3xl font-bold text-white mt-1">
                        @if($stats['orders']['total'] >= 1000)
                            {{ number_format($stats['orders']['total'] / 1000, 1, ',', ' ') }}K
                        @else
                            {{ number_format($stats['orders']['total']) }}
                        @endif
                    </p>
                    <p class="text-sm text-secondary-400 mt-2 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                        +{{ number_format($stats['orders']['this_month']) }} ce mois
                    </p>
                </div>
                <div class="w-14 h-14 bg-accent-500/20 rounded-2xl flex items-center justify-center">
                    <svg class="w-7 h-7 text-accent-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Pending Approvals -->
        <div class="bg-neutral-800/50 backdrop-blur border border-neutral-700 rounded-2xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-neutral-400">En attente</p>
                    <p class="text-3xl font-bold text-white mt-1">{{ $stats['restaurants']['pending'] }}</p>
                    @if($stats['restaurants']['pending'] > 0)
                        <p class="text-sm text-yellow-400 mt-2 flex items-center gap-1">
                            <span class="w-2 h-2 bg-yellow-400 rounded-full animate-pulse"></span>
                            Restaurants à valider
                        </p>
                    @else
                        <p class="text-sm text-neutral-500 mt-2">Aucun en attente</p>
                    @endif
                </div>
                <div class="w-14 h-14 bg-yellow-500/20 rounded-2xl flex items-center justify-center">
                    <svg class="w-7 h-7 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Recent Restaurants -->
        <div class="lg:col-span-2">
            <div class="bg-neutral-800/50 backdrop-blur border border-neutral-700 rounded-2xl">
                <div class="p-6 border-b border-neutral-700">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-bold text-white">Restaurants récents</h2>
                        <a href="{{ route('super-admin.restaurants.index') }}" class="text-primary-400 hover:text-primary-300 text-sm font-medium">
                            Voir tout →
                        </a>
                    </div>
                </div>
                <div class="divide-y divide-neutral-700">
                    @forelse($recentRestaurants as $restaurant)
                        <a href="{{ route('super-admin.restaurants.show', $restaurant) }}" class="block p-4 hover:bg-neutral-700/30 transition-colors">
                            <div class="flex flex-wrap items-center justify-between gap-y-2 gap-x-4">
                                <div class="flex items-center gap-4 min-w-0">
                                    @if($restaurant->logo_path)
                                        <img src="{{ Storage::url($restaurant->logo_path) }}" alt="{{ $restaurant->name }}" class="w-12 h-12 rounded-xl object-cover border border-neutral-600 flex-shrink-0">
                                    @else
                                        <div class="w-12 h-12 bg-gradient-to-br from-primary-500 to-accent-500 rounded-xl flex items-center justify-center text-white font-bold flex-shrink-0">
                                            {{ strtoupper(substr($restaurant->name, 0, 2)) }}
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <p class="font-medium text-white truncate">{{ $restaurant->name }}</p>
                                        <p class="text-sm text-neutral-400">{{ $restaurant->owner?->name ?? 'N/A' }} · {{ $restaurant->created_at->format('d M') }}</p>
                                    </div>
                                </div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="text-sm font-medium text-neutral-300 bg-neutral-700 px-3 py-1 rounded-full">
                                        {{ $restaurant->currentPlan?->name ?? 'Aucun' }}
                                    </span>
                                    @php
                                        $statusColors = [
                                            'active' => 'bg-secondary-500/20 text-secondary-400',
                                            'pending' => 'bg-yellow-500/20 text-yellow-400',
                                            'suspended' => 'bg-red-500/20 text-red-400',
                                            'expired' => 'bg-neutral-500/20 text-neutral-400',
                                        ];
                                        $statusLabels = [
                                            'active' => 'Actif',
                                            'pending' => 'En attente',
                                            'suspended' => 'Suspendu',
                                            'expired' => 'Expiré',
                                        ];
                                    @endphp
                                    <span class="badge {{ $statusColors[$restaurant->status->value] ?? 'bg-neutral-500/20 text-neutral-400' }}">
                                        {{ $statusLabels[$restaurant->status->value] ?? $restaurant->status->value }}
                                    </span>
                                </div>
                            </div>
                        </a>
                    @empty
                        <div class="p-8 text-center">
                            <p class="text-neutral-500">Aucun restaurant récent</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Pending Restaurants -->
            @if($pendingRestaurants->isNotEmpty())
                <div class="bg-neutral-800/50 backdrop-blur border border-yellow-500/30 rounded-2xl mt-6">
                    <div class="p-6 border-b border-neutral-700">
                        <h2 class="text-lg font-bold text-white flex items-center gap-2">
                            <span class="w-2 h-2 bg-yellow-400 rounded-full animate-pulse"></span>
                            Restaurants en attente de validation
                        </h2>
                    </div>
                    <div class="divide-y divide-neutral-700">
                        @foreach($pendingRestaurants as $restaurant)
                            <div class="p-4 hover:bg-neutral-700/30 transition-colors">
                                <div class="flex flex-wrap items-center justify-between gap-y-2 gap-x-4">
                                    <div class="flex items-center gap-4 min-w-0">
                                        @if($restaurant->logo_path)
                                            <img src="{{ Storage::url($restaurant->logo_path) }}" alt="{{ $restaurant->name }}" class="w-10 h-10 rounded-xl object-cover border border-yellow-500/30 flex-shrink-0">
                                        @else
                                            <div class="w-10 h-10 bg-yellow-500/20 rounded-xl flex items-center justify-center text-yellow-400 font-bold flex-shrink-0">
                                                {{ strtoupper(substr($restaurant->name, 0, 2)) }}
                                            </div>
                                        @endif
                                        <div class="min-w-0">
                                            <p class="font-medium text-white truncate">{{ $restaurant->name }}</p>
                                            <p class="text-sm text-neutral-400 truncate">{{ $restaurant->owner?->email ?? 'N/A' }}</p>
                                        </div>
                                    </div>
                                    <a href="{{ route('super-admin.restaurants.show', $restaurant) }}" 
                                       class="px-4 py-2 bg-yellow-500/20 text-yellow-400 rounded-lg text-sm font-medium hover:bg-yellow-500/30 transition-colors">
                                        Examiner
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Right Column -->
        <div class="space-y-6">
            <!-- Revenue by Plan -->
            <div class="bg-neutral-800/50 backdrop-blur border border-neutral-700 rounded-2xl p-6">
                <h2 class="text-lg font-bold text-white mb-4">Revenus par plan</h2>
                <div class="space-y-4">
                    @forelse($revenueByPlan as $plan)
                        @php
                            $maxRevenue = $revenueByPlan->max('total') ?: 1;
                            $percent = ($plan->total / $maxRevenue) * 100;
                            $colors = ['bg-primary-500', 'bg-secondary-500', 'bg-accent-500'];
                        @endphp
                        <div>
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-sm font-medium text-neutral-300">{{ $plan->name }}</span>
                                <span class="text-sm text-neutral-400">{{ number_format($plan->total, 0, ',', ' ') }} F</span>
                            </div>
                            <div class="h-2 bg-neutral-700 rounded-full overflow-hidden">
                                <div class="h-full {{ $colors[$loop->index % 3] }} rounded-full" style="width: {{ $percent }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-neutral-500 text-sm text-center">Aucune donnée ce mois</p>
                    @endforelse
                </div>
            </div>

            <!-- Expiring Subscriptions -->
            @if($expiringSubscriptions->isNotEmpty())
                <div class="bg-neutral-800/50 backdrop-blur border border-red-500/30 rounded-2xl p-6">
                    <h2 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        Abonnements expirés bientôt
                    </h2>
                    <div class="space-y-3">
                        @foreach($expiringSubscriptions as $subscription)
                            <div class="flex items-center justify-between p-3 bg-red-500/10 border border-red-500/20 rounded-xl">
                                <div>
                                    <p class="text-sm font-medium text-white">{{ $subscription->restaurant->name }}</p>
                                    <p class="text-xs text-neutral-400">{{ $subscription->plan->name }}</p>
                                </div>
                                <span class="text-xs text-red-400 font-medium">
                                    {{ $subscription->ends_at->diffForHumans() }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Top Restaurants -->
            @if($topRestaurants->isNotEmpty())
                <div class="bg-neutral-800/50 backdrop-blur border border-neutral-700 rounded-2xl p-6">
                    <h2 class="text-lg font-bold text-white mb-4">Top restaurants ce mois</h2>
                    <div class="space-y-3">
                        @foreach($topRestaurants as $index => $restaurant)
                            <div class="flex items-center gap-3 p-3 bg-neutral-700/30 rounded-xl">
                                <span class="w-8 h-8 rounded-lg flex items-center justify-center font-bold text-sm
                                    {{ $index === 0 ? 'bg-yellow-500/20 text-yellow-400' : ($index === 1 ? 'bg-neutral-400/20 text-neutral-300' : ($index === 2 ? 'bg-orange-500/20 text-orange-400' : 'bg-neutral-600/20 text-neutral-400')) }}">
                                    {{ $index + 1 }}
                                </span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-white truncate">{{ $restaurant->name }}</p>
                                    <p class="text-xs text-neutral-400">{{ $restaurant->orders_count }} commandes</p>
                                </div>
                                <span class="text-sm font-semibold text-secondary-400">
                                    {{ number_format($restaurant->revenue, 0, ',', ' ') }} F
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Quick Actions -->
            <div class="bg-neutral-800/50 backdrop-blur border border-neutral-700 rounded-2xl p-6">
                <h2 class="text-lg font-bold text-white mb-4">Actions rapides</h2>
                <div class="grid grid-cols-2 gap-3">
                    <a href="{{ route('super-admin.restaurants.index') }}" class="flex flex-col items-center gap-2 p-4 bg-neutral-700/50 hover:bg-neutral-700 rounded-xl transition-colors">
                        <svg class="w-6 h-6 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/>
                        </svg>
                        <span class="text-xs text-neutral-300">Restaurants</span>
                    </a>
                    <a href="{{ route('super-admin.plans.index') }}" class="flex flex-col items-center gap-2 p-4 bg-neutral-700/50 hover:bg-neutral-700 rounded-xl transition-colors">
                        <svg class="w-6 h-6 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                        <span class="text-xs text-neutral-300">Plans</span>
                    </a>
                    <a href="{{ route('super-admin.utilisateurs.index') }}" class="flex flex-col items-center gap-2 p-4 bg-neutral-700/50 hover:bg-neutral-700 rounded-xl transition-colors">
                        <svg class="w-6 h-6 text-accent-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197"/>
                        </svg>
                        <span class="text-xs text-neutral-300">Utilisateurs</span>
                    </a>
                    <a href="{{ route('super-admin.stats') }}" class="flex flex-col items-center gap-2 p-4 bg-neutral-700/50 hover:bg-neutral-700 rounded-xl transition-colors">
                        <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        <span class="text-xs text-neutral-300">Statistiques</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Live Orders Section -->
    <div class="mt-8" x-data="liveOrders()" x-init="startPolling()">
        <div class="bg-neutral-800/50 backdrop-blur border border-neutral-700 rounded-2xl">
            <div class="p-6 border-b border-neutral-700">
                <div class="flex items-center justify-between">
                    <h2 class="text-lg font-bold text-white flex items-center gap-3">
                        <span class="relative flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-secondary-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 bg-secondary-500"></span>
                        </span>
                        Commandes en temps réel
                    </h2>
                    <div class="flex items-center gap-4">
                        <div class="text-sm text-neutral-400">
                            <span x-text="stats.orders_today"></span> commandes aujourd'hui
                        </div>
                        <div class="text-sm font-semibold text-secondary-400">
                            <span x-text="formatCurrency(stats.revenue_today)"></span> F
                        </div>
                    </div>
                </div>
            </div>

            <!-- Live Orders Feed -->
            <div class="divide-y divide-neutral-700/50 max-h-96 overflow-y-auto" id="live-orders-feed">
                <template x-for="order in orders" :key="order.id">
                    <div class="p-4 hover:bg-neutral-700/30 transition-colors animate-fade-in">
                        <div class="flex items-center justify-between gap-4">
                            <div class="flex items-center gap-4 min-w-0">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center"
                                     :class="{
                                        'bg-yellow-500/20 text-yellow-400': order.status === 'pending',
                                        'bg-blue-500/20 text-blue-400': order.status === 'confirmed',
                                        'bg-accent-500/20 text-accent-400': order.status === 'preparing',
                                        'bg-secondary-500/20 text-secondary-400': order.status === 'ready' || order.status === 'completed',
                                        'bg-red-500/20 text-red-400': order.status === 'cancelled',
                                     }">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="font-medium text-white truncate">
                                        <span class="font-mono text-sm text-neutral-400">#</span><span x-text="order.reference"></span>
                                    </p>
                                    <p class="text-sm text-neutral-400 truncate" x-text="order.restaurant"></p>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <span class="px-2.5 py-1 rounded-lg text-xs font-medium"
                                      :class="{
                                        'bg-yellow-500/20 text-yellow-400': order.status === 'pending',
                                        'bg-blue-500/20 text-blue-400': order.status === 'confirmed',
                                        'bg-accent-500/20 text-accent-400': order.status === 'preparing',
                                        'bg-secondary-500/20 text-secondary-400': order.status === 'ready' || order.status === 'completed',
                                        'bg-red-500/20 text-red-400': order.status === 'cancelled',
                                      }"
                                      x-text="order.status_label">
                                </span>
                                <span class="text-sm font-semibold text-white" x-text="formatCurrency(order.total) + ' F'"></span>
                                <span class="text-xs text-neutral-500" x-text="order.created_at"></span>
                            </div>
                        </div>
                    </div>
                </template>

                <!-- Empty State -->
                <div x-show="orders.length === 0" class="p-8 text-center">
                    <div class="w-12 h-12 bg-neutral-700 rounded-full flex items-center justify-center mx-auto mb-3">
                        <svg class="w-6 h-6 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <p class="text-neutral-500">Aucune commande récente</p>
                    <p class="text-xs text-neutral-600 mt-1">Les nouvelles commandes apparaîtront ici</p>
                </div>
            </div>

            <!-- Stats Footer -->
            <div class="p-4 border-t border-neutral-700 bg-neutral-900/30">
                <div class="grid grid-cols-4 gap-4 text-center">
                    <div>
                        <p class="text-2xl font-bold text-white" x-text="stats.pending_orders || 0"></p>
                        <p class="text-xs text-neutral-500">En attente</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-secondary-400" x-text="stats.active_restaurants || 0"></p>
                        <p class="text-xs text-neutral-500">Restaurants actifs</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-blue-400" x-text="stats.new_registrations_today || 0"></p>
                        <p class="text-xs text-neutral-500">Nouveaux aujourd'hui</p>
                    </div>
                    <div>
                        <p class="text-2xl font-bold text-accent-400" x-text="stats.orders_today || 0"></p>
                        <p class="text-xs text-neutral-500">Commandes aujourd'hui</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function liveDashboard() {
            return {
                isLive: true,
                lastUpdate: '',
                interval: null,
                
                startPolling() {
                    this.lastUpdate = new Date().toLocaleTimeString('fr-FR');
                    this.interval = setInterval(() => {
                        if (this.isLive) {
                            this.lastUpdate = new Date().toLocaleTimeString('fr-FR');
                        }
                    }, 5000);
                },
                
                toggleLive() {
                    this.isLive = !this.isLive;
                }
            }
        }

        function liveOrders() {
            return {
                orders: [],
                stats: {
                    orders_today: {{ $stats['orders']['today'] ?? 0 }},
                    revenue_today: 0,
                    pending_orders: 0,
                    active_restaurants: {{ $stats['restaurants']['active'] ?? 0 }},
                    new_registrations_today: 0,
                },
                interval: null,
                
                startPolling() {
                    this.fetchData();
                    this.interval = setInterval(() => {
                        this.fetchData();
                    }, 10000); // Every 10 seconds
                },
                
                async fetchData() {
                    try {
                        const response = await fetch('{{ route("super-admin.api.live-stats") }}');
                        const data = await response.json();
                        
                        this.orders = data.recent_orders || [];
                        this.stats = data.stats || this.stats;
                    } catch (error) {
                        console.error('Error fetching live data:', error);
                    }
                },
                
                formatCurrency(amount) {
                    return new Intl.NumberFormat('fr-FR').format(amount || 0);
                }
            }
        }
    </script>

    <style>
        @keyframes fade-in {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }
        .animate-fade-in {
            animation: fade-in 0.3s ease-out;
        }
    </style>
    @endpush
</x-layouts.admin-super>
