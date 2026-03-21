<x-layouts.admin-super title="Dashboard">
    <!-- Dashboard Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-neutral-900">Dashboard</h1>
            <p class="text-neutral-500 text-sm mt-1">Vue d'ensemble de la plateforme MenuPro</p>
        </div>
        <div class="flex items-center gap-3" x-data="liveDashboard()" x-init="startPolling()">
            <div class="flex items-center gap-2 px-3 py-1.5 bg-emerald-50 border border-emerald-200 rounded-lg">
                <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></span>
                <span class="text-xs font-medium text-emerald-700">Live</span>
                <span class="text-[10px] text-emerald-600/70" x-text="lastUpdate"></span>
            </div>
            <button @click="toggleLive()" :class="isLive ? 'bg-emerald-500 text-white shadow-sm' : 'bg-neutral-200 text-neutral-600'"
                    class="p-2 rounded-lg transition-all" title="Mode live">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
            </button>
        </div>
    </div>

    <!-- KPI Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Restaurants actifs -->
        <div class="bg-white rounded-2xl border border-neutral-200/80 p-5 hover:shadow-md transition-shadow group">
            <div class="flex items-start justify-between mb-3">
                <div class="w-10 h-10 bg-primary-50 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </div>
                <span class="text-xs font-medium text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full">+{{ $stats['restaurants']['total'] }}</span>
            </div>
            <p class="text-2xl font-bold text-neutral-900">{{ number_format($stats['restaurants']['active']) }}</p>
            <p class="text-xs text-neutral-500 mt-1">Restaurants actifs</p>
        </div>

        <!-- Revenus mensuels -->
        <div class="bg-white rounded-2xl border border-neutral-200/80 p-5 hover:shadow-md transition-shadow group">
            <div class="flex items-start justify-between mb-3">
                <div class="w-10 h-10 bg-emerald-50 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <span class="text-xs font-medium text-emerald-600 bg-emerald-50 px-2 py-0.5 rounded-full flex items-center gap-1">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                    MRR
                </span>
            </div>
            <p class="text-2xl font-bold text-neutral-900">
                @if($stats['revenue']['this_month'] >= 1000000)
                    {{ number_format($stats['revenue']['this_month'] / 1000000, 1, ',', ' ') }}M
                @else
                    {{ number_format($stats['revenue']['this_month'] / 1000, 0, ',', ' ') }}K
                @endif
                <span class="text-sm font-normal text-neutral-400">FCFA</span>
            </p>
            <p class="text-xs text-neutral-500 mt-1">Revenus ce mois</p>
        </div>

        <!-- Commandes -->
        <div class="bg-white rounded-2xl border border-neutral-200/80 p-5 hover:shadow-md transition-shadow group">
            <div class="flex items-start justify-between mb-3">
                <div class="w-10 h-10 bg-blue-50 rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <span class="text-xs font-medium text-blue-600 bg-blue-50 px-2 py-0.5 rounded-full">+{{ number_format($stats['orders']['this_month']) }}</span>
            </div>
            <p class="text-2xl font-bold text-neutral-900">
                @if($stats['orders']['total'] >= 1000)
                    {{ number_format($stats['orders']['total'] / 1000, 1, ',', ' ') }}K
                @else
                    {{ number_format($stats['orders']['total']) }}
                @endif
            </p>
            <p class="text-xs text-neutral-500 mt-1">Commandes totales</p>
        </div>

        <!-- En attente -->
        <div class="bg-white rounded-2xl border border-neutral-200/80 p-5 hover:shadow-md transition-shadow group {{ $stats['restaurants']['pending'] > 0 ? 'border-amber-200 bg-amber-50/30' : '' }}">
            <div class="flex items-start justify-between mb-3">
                <div class="w-10 h-10 {{ $stats['restaurants']['pending'] > 0 ? 'bg-amber-100' : 'bg-neutral-100' }} rounded-xl flex items-center justify-center group-hover:scale-110 transition-transform">
                    <svg class="w-5 h-5 {{ $stats['restaurants']['pending'] > 0 ? 'text-amber-600' : 'text-neutral-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                @if($stats['restaurants']['pending'] > 0)
                    <span class="w-2 h-2 bg-amber-400 rounded-full animate-pulse"></span>
                @endif
            </div>
            <p class="text-2xl font-bold text-neutral-900">{{ $stats['restaurants']['pending'] }}</p>
            <p class="text-xs {{ $stats['restaurants']['pending'] > 0 ? 'text-amber-600 font-medium' : 'text-neutral-500' }} mt-1">
                {{ $stats['restaurants']['pending'] > 0 ? 'En attente de validation' : 'Aucun en attente' }}
            </p>
        </div>
    </div>

    <!-- Main Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Recent Restaurants -->
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-white rounded-2xl border border-neutral-200/80 overflow-hidden">
                <div class="px-5 py-4 border-b border-neutral-100 flex items-center justify-between">
                    <h2 class="font-semibold text-neutral-900">Restaurants récents</h2>
                    <a href="{{ route('super-admin.restaurants.index') }}" class="text-primary-600 hover:text-primary-700 text-xs font-medium flex items-center gap-1">
                        Voir tout
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>
                <div class="divide-y divide-neutral-100">
                    @forelse($recentRestaurants as $restaurant)
                        <a href="{{ route('super-admin.restaurants.show', $restaurant) }}" class="flex flex-wrap items-center justify-between gap-3 px-5 py-3.5 hover:bg-neutral-50 transition-colors">
                            <div class="flex items-center gap-3 min-w-0">
                                @if($restaurant->logo_path)
                                    <img src="{{ Storage::url($restaurant->logo_path) }}" alt="{{ $restaurant->name }}" class="w-10 h-10 rounded-xl object-cover border border-neutral-200 flex-shrink-0">
                                @else
                                    <div class="w-10 h-10 bg-gradient-to-br from-primary-500 to-primary-600 rounded-xl flex items-center justify-center text-white text-sm font-bold flex-shrink-0 shadow-sm">
                                        {{ strtoupper(substr($restaurant->name, 0, 2)) }}
                                    </div>
                                @endif
                                <div class="min-w-0">
                                    <p class="text-sm font-medium text-neutral-900 truncate">{{ $restaurant->name }}</p>
                                    <p class="text-xs text-neutral-500">{{ $restaurant->owner?->name ?? 'N/A' }} · {{ $restaurant->created_at->format('d M') }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2">
                                <span class="text-xs font-medium text-neutral-600 bg-neutral-100 px-2.5 py-1 rounded-lg">
                                    {{ $restaurant->currentPlan?->name ?? 'Aucun' }}
                                </span>
                                @php
                                    $statusColors = [
                                        'active' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        'pending' => 'bg-amber-50 text-amber-700 border-amber-200',
                                        'suspended' => 'bg-red-50 text-red-700 border-red-200',
                                        'expired' => 'bg-neutral-100 text-neutral-600 border-neutral-200',
                                    ];
                                    $statusLabels = [
                                        'active' => 'Actif',
                                        'pending' => 'En attente',
                                        'suspended' => 'Suspendu',
                                        'expired' => 'Expiré',
                                    ];
                                @endphp
                                <span class="text-[10px] font-semibold px-2 py-0.5 rounded-md border {{ $statusColors[$restaurant->status->value] ?? 'bg-neutral-100 text-neutral-600 border-neutral-200' }}">
                                    {{ $statusLabels[$restaurant->status->value] ?? $restaurant->status->value }}
                                </span>
                            </div>
                        </a>
                    @empty
                        <div class="p-8 text-center">
                            <svg class="w-10 h-10 text-neutral-300 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/></svg>
                            <p class="text-sm text-neutral-400">Aucun restaurant récent</p>
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Pending Restaurants -->
            @if($pendingRestaurants->isNotEmpty())
                <div class="bg-white rounded-2xl border border-amber-200 overflow-hidden">
                    <div class="px-5 py-4 border-b border-amber-100 bg-amber-50/50 flex items-center gap-2">
                        <span class="w-2 h-2 bg-amber-400 rounded-full animate-pulse"></span>
                        <h2 class="font-semibold text-neutral-900 text-sm">{{ $pendingRestaurants->count() }} restaurant(s) en attente de validation</h2>
                    </div>
                    <div class="divide-y divide-neutral-100">
                        @foreach($pendingRestaurants as $restaurant)
                            <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-3.5 hover:bg-amber-50/40 transition-colors">
                                <div class="flex items-center gap-3 min-w-0">
                                    @if($restaurant->logo_path)
                                        <img src="{{ Storage::url($restaurant->logo_path) }}" alt="{{ $restaurant->name }}" class="w-9 h-9 rounded-lg object-cover border border-amber-200 flex-shrink-0">
                                    @else
                                        <div class="w-9 h-9 bg-amber-100 rounded-lg flex items-center justify-center text-amber-600 text-sm font-bold flex-shrink-0">
                                            {{ strtoupper(substr($restaurant->name, 0, 2)) }}
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium text-neutral-900 truncate">{{ $restaurant->name }}</p>
                                        <p class="text-xs text-neutral-500 truncate">{{ $restaurant->owner?->email ?? 'N/A' }}</p>
                                    </div>
                                </div>
                                <a href="{{ route('super-admin.restaurants.show', $restaurant) }}"
                                   class="px-3 py-1.5 bg-amber-500 text-white rounded-lg text-xs font-semibold hover:bg-amber-600 transition-colors shadow-sm">
                                    Examiner
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        <!-- Right Column -->
        <div class="space-y-6">
            <!-- Revenue by Plan -->
            <div class="bg-white rounded-2xl border border-neutral-200/80 p-5">
                <h2 class="font-semibold text-neutral-900 text-sm mb-4">Revenus par plan</h2>
                <div class="space-y-4">
                    @forelse($revenueByPlan as $plan)
                        @php
                            $maxRevenue = $revenueByPlan->max('total') ?: 1;
                            $percent = ($plan->total / $maxRevenue) * 100;
                            $colors = ['bg-primary-500', 'bg-emerald-500', 'bg-blue-500'];
                        @endphp
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="text-xs font-medium text-neutral-700">{{ $plan->name }}</span>
                                <span class="text-xs font-semibold text-neutral-900">{{ number_format($plan->total, 0, ',', ' ') }} F</span>
                            </div>
                            <div class="h-1.5 bg-neutral-100 rounded-full overflow-hidden">
                                <div class="h-full {{ $colors[$loop->index % 3] }} rounded-full transition-all duration-500" style="width: {{ $percent }}%"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-neutral-400 text-xs text-center py-4">Aucune donnée ce mois</p>
                    @endforelse
                </div>
            </div>

            <!-- Expiring Subscriptions -->
            @if($expiringSubscriptions->isNotEmpty())
                <div class="bg-white rounded-2xl border border-red-200 p-5">
                    <div class="flex items-center gap-2 mb-4">
                        <div class="w-8 h-8 bg-red-50 rounded-lg flex items-center justify-center">
                            <svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        <h2 class="font-semibold text-neutral-900 text-sm">Expirent bientôt</h2>
                    </div>
                    <div class="space-y-2.5">
                        @foreach($expiringSubscriptions as $subscription)
                            <div class="flex items-center justify-between p-3 bg-red-50/50 border border-red-100 rounded-xl">
                                <div class="min-w-0">
                                    <p class="text-xs font-medium text-neutral-900 truncate">{{ $subscription->restaurant->name }}</p>
                                    <p class="text-[10px] text-neutral-500">{{ $subscription->plan->name }}</p>
                                </div>
                                <span class="text-[10px] text-red-600 font-semibold whitespace-nowrap ml-2">
                                    {{ $subscription->ends_at->diffForHumans() }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Top Restaurants -->
            @if($topRestaurants->isNotEmpty())
                <div class="bg-white rounded-2xl border border-neutral-200/80 p-5">
                    <h2 class="font-semibold text-neutral-900 text-sm mb-4">Top restaurants ce mois</h2>
                    <div class="space-y-2.5">
                        @foreach($topRestaurants as $index => $restaurant)
                            <div class="flex items-center gap-3 p-2.5 rounded-xl {{ $index === 0 ? 'bg-amber-50/50 border border-amber-100' : 'hover:bg-neutral-50' }}">
                                <span class="w-7 h-7 rounded-lg flex items-center justify-center font-bold text-xs flex-shrink-0
                                    {{ $index === 0 ? 'bg-amber-100 text-amber-700' : ($index === 1 ? 'bg-neutral-200 text-neutral-700' : ($index === 2 ? 'bg-orange-100 text-orange-700' : 'bg-neutral-100 text-neutral-500')) }}">
                                    {{ $index + 1 }}
                                </span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-medium text-neutral-900 truncate">{{ $restaurant->name }}</p>
                                    <p class="text-[10px] text-neutral-500">{{ $restaurant->orders_count }} cmd</p>
                                </div>
                                <span class="text-xs font-bold text-emerald-600 whitespace-nowrap">
                                    {{ number_format($restaurant->revenue, 0, ',', ' ') }} F
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Quick Actions -->
            <div class="bg-white rounded-2xl border border-neutral-200/80 p-5">
                <h2 class="font-semibold text-neutral-900 text-sm mb-3">Actions rapides</h2>
                <div class="grid grid-cols-2 gap-2">
                    <a href="{{ route('super-admin.restaurants.index') }}" class="flex flex-col items-center gap-1.5 p-3 bg-neutral-50 hover:bg-primary-50 rounded-xl transition-colors border border-transparent hover:border-primary-200 group">
                        <svg class="w-5 h-5 text-neutral-400 group-hover:text-primary-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/>
                        </svg>
                        <span class="text-[10px] font-medium text-neutral-600 group-hover:text-primary-700">Restaurants</span>
                    </a>
                    <a href="{{ route('super-admin.plans.index') }}" class="flex flex-col items-center gap-1.5 p-3 bg-neutral-50 hover:bg-emerald-50 rounded-xl transition-colors border border-transparent hover:border-emerald-200 group">
                        <svg class="w-5 h-5 text-neutral-400 group-hover:text-emerald-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                        <span class="text-[10px] font-medium text-neutral-600 group-hover:text-emerald-700">Plans</span>
                    </a>
                    <a href="{{ route('super-admin.utilisateurs.index') }}" class="flex flex-col items-center gap-1.5 p-3 bg-neutral-50 hover:bg-blue-50 rounded-xl transition-colors border border-transparent hover:border-blue-200 group">
                        <svg class="w-5 h-5 text-neutral-400 group-hover:text-blue-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197"/>
                        </svg>
                        <span class="text-[10px] font-medium text-neutral-600 group-hover:text-blue-700">Utilisateurs</span>
                    </a>
                    <a href="{{ route('super-admin.stats') }}" class="flex flex-col items-center gap-1.5 p-3 bg-neutral-50 hover:bg-purple-50 rounded-xl transition-colors border border-transparent hover:border-purple-200 group">
                        <svg class="w-5 h-5 text-neutral-400 group-hover:text-purple-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        <span class="text-[10px] font-medium text-neutral-600 group-hover:text-purple-700">Statistiques</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    <!-- Live Orders Section -->
    <div class="mt-6" x-data="liveOrders()" x-init="startPolling()">
        <div class="bg-neutral-900 rounded-2xl overflow-hidden shadow-lg">
            <div class="px-5 py-4 border-b border-neutral-800 flex flex-wrap items-center justify-between gap-3">
                <h2 class="font-semibold text-white text-sm flex items-center gap-2.5">
                    <span class="relative flex h-2.5 w-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-emerald-500"></span>
                    </span>
                    Commandes en temps réel
                </h2>
                <div class="flex items-center gap-4 text-xs">
                    <span class="text-neutral-400"><span class="text-white font-semibold" x-text="stats.orders_today"></span> commandes</span>
                    <span class="text-emerald-400 font-semibold"><span x-text="formatCurrency(stats.revenue_today)"></span> F</span>
                </div>
            </div>

            <!-- Live Orders Feed -->
            <div class="divide-y divide-neutral-800/50 max-h-80 overflow-y-auto">
                <template x-for="order in orders" :key="order.id">
                    <div class="px-5 py-3 hover:bg-neutral-800/40 transition-colors">
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0"
                                     :class="{
                                        'bg-amber-500/15 text-amber-400': order.status === 'pending',
                                        'bg-blue-500/15 text-blue-400': order.status === 'confirmed',
                                        'bg-primary-500/15 text-primary-400': order.status === 'preparing',
                                        'bg-emerald-500/15 text-emerald-400': order.status === 'ready' || order.status === 'completed',
                                        'bg-red-500/15 text-red-400': order.status === 'cancelled',
                                     }">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm text-white truncate">
                                        <span class="text-neutral-500 font-mono text-xs">#</span><span x-text="order.reference"></span>
                                    </p>
                                    <p class="text-xs text-neutral-500 truncate" x-text="order.restaurant"></p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 flex-shrink-0">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold"
                                      :class="{
                                        'bg-amber-500/15 text-amber-400': order.status === 'pending',
                                        'bg-blue-500/15 text-blue-400': order.status === 'confirmed',
                                        'bg-primary-500/15 text-primary-400': order.status === 'preparing',
                                        'bg-emerald-500/15 text-emerald-400': order.status === 'ready' || order.status === 'completed',
                                        'bg-red-500/15 text-red-400': order.status === 'cancelled',
                                      }"
                                      x-text="order.status_label"></span>
                                <span class="text-xs font-semibold text-white" x-text="formatCurrency(order.total) + ' F'"></span>
                                <span class="text-[10px] text-neutral-600" x-text="order.created_at"></span>
                            </div>
                        </div>
                    </div>
                </template>

                <div x-show="orders.length === 0" class="p-8 text-center">
                    <svg class="w-8 h-8 text-neutral-700 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                    <p class="text-neutral-500 text-xs">Les nouvelles commandes apparaîtront ici</p>
                </div>
            </div>

            <!-- Stats Footer -->
            <div class="px-5 py-3 border-t border-neutral-800 bg-neutral-950/50">
                <div class="grid grid-cols-4 gap-4 text-center">
                    <div>
                        <p class="text-lg font-bold text-white" x-text="stats.pending_orders || 0"></p>
                        <p class="text-[10px] text-neutral-500">En attente</p>
                    </div>
                    <div>
                        <p class="text-lg font-bold text-emerald-400" x-text="stats.active_restaurants || 0"></p>
                        <p class="text-[10px] text-neutral-500">Actifs</p>
                    </div>
                    <div>
                        <p class="text-lg font-bold text-blue-400" x-text="stats.new_registrations_today || 0"></p>
                        <p class="text-[10px] text-neutral-500">Nouveaux</p>
                    </div>
                    <div>
                        <p class="text-lg font-bold text-primary-400" x-text="stats.orders_today || 0"></p>
                        <p class="text-[10px] text-neutral-500">Aujourd'hui</p>
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
                    this.lastUpdate = new Date().toLocaleTimeString('fr-FR', {hour:'2-digit',minute:'2-digit'});
                    this.interval = setInterval(() => {
                        if (this.isLive) {
                            this.lastUpdate = new Date().toLocaleTimeString('fr-FR', {hour:'2-digit',minute:'2-digit'});
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
                    this.interval = setInterval(() => this.fetchData(), 10000);
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
    @endpush
</x-layouts.admin-super>
