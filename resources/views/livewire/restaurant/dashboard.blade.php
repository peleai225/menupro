<div>
    <!-- Platform Announcements -->
    @if($this->announcements->isNotEmpty())
        <div class="space-y-3 mb-6">
            @foreach($this->announcements as $announcement)
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
                            <button wire:click="dismissAnnouncement({{ $announcement->id }})" 
                                    @click="show = false"
                                    class="p-1.5 {{ $style['dismiss'] }} rounded-lg transition-colors">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Welcome Banner - Mobile optimized -->
    <div class="relative bg-gradient-to-r from-primary-500 via-primary-600 to-primary-700 rounded-2xl p-4 sm:p-6 mb-6 text-white overflow-hidden">
        <!-- Decorative elements - hidden on mobile -->
        <div class="absolute -right-12 -top-12 w-48 h-48 bg-white/10 rounded-full blur-2xl pointer-events-none hidden sm:block"></div>
        <div class="absolute right-8 top-4 opacity-10 pointer-events-none hidden sm:block">
            <svg width="60" height="60" viewBox="0 0 60 60" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle cx="30" cy="30" r="24" stroke="white" stroke-width="2"/>
                <circle cx="30" cy="30" r="16" stroke="white" stroke-width="2"/>
            </svg>
        </div>
        <div class="relative z-10">
            <p class="text-primary-200 text-xs font-medium mb-1 hidden sm:block">{{ now()->locale('fr')->isoFormat('dddd D MMMM YYYY') }}</p>
            <h1 class="text-lg sm:text-xl font-bold truncate">Bonjour, {{ auth()->user()->name }} 👋</h1>
            <p class="text-primary-100 mt-1 text-sm hidden sm:block">Voici un résumé de l'activité de votre restaurant aujourd'hui.</p>
        </div>
    </div>

    <!-- Stats Cards - Mobile optimized -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4 mb-6">
        <!-- Orders Today -->
        <div class="group relative bg-white rounded-xl p-3 sm:p-4 border border-neutral-200/60 hover:border-primary-200 hover:shadow-lg transition-all overflow-hidden">
            <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-br from-primary-500/10 to-transparent rounded-full blur-xl -translate-y-6 translate-x-6 hidden sm:block"></div>
            <div class="relative">
                <div class="flex items-start justify-between mb-2">
                    <div class="w-9 h-9 sm:w-10 sm:h-10 bg-gradient-to-br from-primary-500 to-primary-600 rounded-lg flex items-center justify-center shadow-md">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                    </div>
                    <span class="text-[10px] sm:text-[11px] font-bold px-1.5 py-0.5 rounded {{ $this->stats['orders_change'] >= 0 ? 'bg-secondary-50 text-secondary-700' : 'bg-red-50 text-red-700' }}">
                        {{ $this->stats['orders_change'] >= 0 ? '+' : '' }}{{ $this->stats['orders_change'] }}%
                    </span>
                </div>
                <p class="text-[10px] sm:text-xs font-semibold text-neutral-500 uppercase tracking-wide mb-0.5">Commandes</p>
                <p class="text-2xl sm:text-3xl font-bold text-neutral-900 leading-none">{{ $this->stats['orders_today'] }}</p>
                <p class="text-[10px] sm:text-xs text-neutral-400 mt-1">vs hier</p>
            </div>
        </div>

        <!-- Revenue Today -->
        <div class="group relative bg-white rounded-xl p-3 sm:p-4 border border-neutral-200/60 hover:border-secondary-200 hover:shadow-lg transition-all overflow-hidden">
            <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-br from-secondary-500/10 to-transparent rounded-full blur-xl -translate-y-6 translate-x-6 hidden sm:block"></div>
            <div class="relative">
                <div class="flex items-start justify-between mb-2">
                    <div class="w-9 h-9 sm:w-10 sm:h-10 bg-gradient-to-br from-secondary-500 to-secondary-600 rounded-lg flex items-center justify-center shadow-md">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <span class="text-[10px] sm:text-[11px] font-bold px-1.5 py-0.5 rounded {{ $this->stats['revenue_change'] >= 0 ? 'bg-secondary-50 text-secondary-700' : 'bg-red-50 text-red-700' }}">
                        {{ $this->stats['revenue_change'] >= 0 ? '+' : '' }}{{ $this->stats['revenue_change'] }}%
                    </span>
                </div>
                <p class="text-[10px] sm:text-xs font-semibold text-neutral-500 uppercase tracking-wide mb-0.5">CA brut</p>
                <p class="text-xl sm:text-2xl font-bold text-neutral-900 leading-none">{{ number_format($this->stats['revenue_today'], 0, ',', ' ') }}<span class="text-sm text-neutral-500 font-medium ml-0.5">F</span></p>
                <p class="text-[10px] sm:text-xs text-neutral-400 mt-1 truncate">Net: {{ number_format($this->stats['revenue_net_today'], 0, ',', ' ') }}F</p>
            </div>
        </div>

        <!-- Pending Orders -->
        <div class="group relative bg-white rounded-xl p-3 sm:p-4 border border-neutral-200/60 hover:border-accent-200 hover:shadow-lg transition-all overflow-hidden">
            <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-br from-accent-500/10 to-transparent rounded-full blur-xl -translate-y-6 translate-x-6 hidden sm:block"></div>
            <div class="relative">
                <div class="flex items-start justify-between mb-2">
                    <div class="w-9 h-9 sm:w-10 sm:h-10 bg-gradient-to-br from-accent-500 to-accent-600 rounded-lg flex items-center justify-center shadow-md">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    @if($this->stats['pending_orders'] > 0)
                        <span class="text-[10px] font-bold px-1.5 py-0.5 rounded-full bg-accent-50 text-accent-700 flex items-center gap-1">
                            <span class="w-1 h-1 bg-accent-500 rounded-full animate-pulse"></span>
                            <span class="hidden sm:inline">Urgent</span>
                        </span>
                    @endif
                </div>
                <p class="text-[10px] sm:text-xs font-semibold text-neutral-500 uppercase tracking-wide mb-0.5">En attente</p>
                <p class="text-2xl sm:text-3xl font-bold text-neutral-900 leading-none">{{ $this->stats['pending_orders'] }}</p>
                <p class="text-[10px] sm:text-xs {{ $this->stats['pending_orders'] > 0 ? 'text-accent-600 font-medium' : 'text-neutral-400' }} mt-1">
                    {{ $this->stats['pending_orders'] > 0 ? 'À traiter' : 'Rien' }}
                </p>
            </div>
        </div>

        <!-- Total Dishes -->
        <div class="group relative bg-white rounded-xl p-3 sm:p-4 border border-neutral-200/60 hover:border-neutral-300 hover:shadow-lg transition-all overflow-hidden">
            <div class="absolute top-0 right-0 w-24 h-24 bg-gradient-to-br from-neutral-500/10 to-transparent rounded-full blur-xl -translate-y-6 translate-x-6 hidden sm:block"></div>
            <div class="relative">
                <div class="flex items-start justify-between mb-2">
                    <div class="w-9 h-9 sm:w-10 sm:h-10 bg-gradient-to-br from-neutral-700 to-neutral-900 rounded-lg flex items-center justify-center shadow-md">
                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                    @php $dishUsage = $this->stats['max_dishes'] > 0 ? round(($this->stats['dishes_count'] / $this->stats['max_dishes']) * 100) : 0; @endphp
                    <span class="text-[10px] sm:text-[11px] font-bold px-1.5 py-0.5 rounded {{ $dishUsage > 80 ? 'bg-red-50 text-red-700' : ($dishUsage > 50 ? 'bg-amber-50 text-amber-700' : 'bg-neutral-100 text-neutral-700') }}">{{ $dishUsage }}%</span>
                </div>
                <p class="text-[10px] sm:text-xs font-semibold text-neutral-500 uppercase tracking-wide mb-0.5">Plats</p>
                <p class="text-2xl sm:text-3xl font-bold text-neutral-900 leading-none">{{ $this->stats['dishes_count'] }}<span class="text-sm text-neutral-400 font-medium ml-0.5">/{{ $this->stats['max_dishes'] }}</span></p>
                <div class="h-1 bg-neutral-100 rounded-full mt-2 overflow-hidden">
                    <div class="h-full bg-gradient-to-r from-primary-500 to-primary-600 rounded-full transition-all" style="width: {{ min($dishUsage, 100) }}%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Revenue Trend Chart -->
        <div class="lg:col-span-2 bg-white rounded-2xl p-6 border border-neutral-200/60 shadow-sm">
            <h3 class="text-lg font-bold text-neutral-900 mb-4">Évolution du CA Net (30 derniers jours)</h3>
            <div class="h-64">
                <canvas id="revenueTrendChart"></canvas>
            </div>
        </div>

        <!-- Orders by Type Chart -->
        <div class="bg-white rounded-2xl p-6 border border-neutral-200/60 shadow-sm">
            <h3 class="text-lg font-bold text-neutral-900 mb-4">Types de commandes</h3>
            <div class="h-64 flex items-center justify-center">
                <canvas id="ordersByTypeChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Top Dishes Chart -->
    <div class="bg-white rounded-2xl p-6 border border-neutral-200/60 shadow-sm mb-8">
        <h3 class="text-lg font-bold text-neutral-900 mb-4">Top 5 des plats vendus (30 derniers jours)</h3>
        <div class="h-80">
            <canvas id="topDishesChart"></canvas>
        </div>
    </div>

    <!-- Main Content Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Recent Orders -->
        <div class="lg:col-span-2">
            <div class="card">
                <div class="p-6 border-b border-neutral-100">
                    <div class="flex items-center justify-between">
                        <h2 class="text-lg font-bold text-neutral-900">Commandes récentes</h2>
                        <a href="{{ route('restaurant.orders') }}" class="text-primary-600 hover:text-primary-700 text-sm font-medium">
                            Voir tout →
                        </a>
                    </div>
                </div>
                <div class="divide-y divide-neutral-100">
                    @forelse($this->recentOrders as $order)
                        <div class="p-4 hover:bg-neutral-50 transition-colors">
                            <div class="flex flex-wrap items-center justify-between gap-y-2 gap-x-4">
                                <div class="flex items-center gap-4 min-w-0">
                                    <div class="w-10 h-10 bg-neutral-100 rounded-full flex items-center justify-center flex-shrink-0">
                                        <span class="text-sm font-bold text-neutral-600">{{ strtoupper(substr($order->customer_name, 0, 1)) }}</span>
                                    </div>
                                    <div class="min-w-0">
                                        <p class="font-medium text-neutral-900 truncate">{{ $order->customer_name ?? 'Client' }}</p>
                                        <p class="text-sm text-neutral-500">#{{ $order->reference }} · {{ $order->created_at->locale('fr')->diffForHumans() }}</p>
                                    </div>
                                </div>
                                <div class="flex flex-wrap items-center gap-2">
                                    <span class="font-bold text-neutral-900">{{ number_format($order->total, 0, ',', ' ') }} F</span>
                                    @php
                                        $statusColor = match($order->status->color()) {
                                            'primary' => '#3b82f6',
                                            'success' => '#10b981',
                                            'warning' => '#f59e0b',
                                            'error' => '#ef4444',
                                            'info' => '#06b6d4',
                                            default => '#6b7280',
                                        };
                                    @endphp
                                    <span class="badge px-3 py-1 rounded-full text-xs font-medium" style="background-color: {{ $statusColor }}20; color: {{ $statusColor }}">
                                        {{ $order->status->label() }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center">
                            <svg class="w-12 h-12 text-neutral-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <p class="text-neutral-500">Aucune commande récente</p>
                            <p class="text-sm text-neutral-400 mt-1">Les commandes apparaîtront ici</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Right Sidebar -->
        <div class="space-y-6">
            <!-- Quick Actions -->
            <div class="card p-6">
                <h2 class="text-lg font-bold text-neutral-900 mb-4">Actions rapides</h2>
                <div class="space-y-3">
                    <a href="{{ route('restaurant.plats.create') }}" class="flex items-center gap-3 p-3 bg-primary-50 hover:bg-primary-100 rounded-xl transition-colors">
                        <div class="w-10 h-10 bg-primary-500 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                        </div>
                        <span class="font-medium text-neutral-900">Ajouter un plat</span>
                    </a>
                    <a href="{{ route('restaurant.categories.index') }}" class="flex items-center gap-3 p-3 bg-neutral-50 hover:bg-neutral-100 rounded-xl transition-colors">
                        <div class="w-10 h-10 bg-neutral-200 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                            </svg>
                        </div>
                        <span class="font-medium text-neutral-700">Gérer les catégories</span>
                    </a>
                    <a href="{{ route('restaurant.settings') }}" class="flex items-center gap-3 p-3 bg-neutral-50 hover:bg-neutral-100 rounded-xl transition-colors">
                        <div class="w-10 h-10 bg-neutral-200 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </div>
                        <span class="font-medium text-neutral-700">Paramètres</span>
                    </a>
                </div>
            </div>

            <!-- Best Sellers -->
            <div class="card p-6">
                <h2 class="text-lg font-bold text-neutral-900 mb-4">Plats populaires</h2>
                <div class="space-y-4">
                    @forelse($this->popularDishes as $dish)
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                @if($dish->image)
                                    <img src="{{ Storage::url($dish->image) }}" alt="{{ $dish->name }}" class="w-12 h-12 object-cover rounded-xl">
                                @else
                                    <div class="w-12 h-12 bg-neutral-200 rounded-xl flex items-center justify-center">
                                        <svg class="w-6 h-6 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                @endif
                                <div>
                                    <p class="font-medium text-neutral-900">{{ $dish->name }}</p>
                                    <p class="text-sm text-neutral-500">{{ $dish->orders_count ?? 0 }} commandes</p>
                                </div>
                            </div>
                            <span class="font-semibold text-neutral-700">{{ number_format($dish->price, 0, ',', ' ') }} F</span>
                        </div>
                    @empty
                        <p class="text-neutral-500 text-sm text-center py-4">Aucune donnée disponible</p>
                    @endforelse
                </div>
            </div>

            <!-- Stock Alerts -->
            @if($this->stockAlerts->isNotEmpty())
            <div class="card p-6 border-l-4 border-red-500">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-bold text-neutral-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        Stock critique
                    </h2>
                    <span class="px-2.5 py-1 bg-red-100 text-red-700 text-xs font-bold rounded-full">{{ $this->stockAlerts->count() }}</span>
                </div>
                <div class="space-y-2">
                    @foreach($this->stockAlerts as $ingredient)
                        <a href="{{ route('restaurant.stock.ingredients.show', $ingredient) }}" class="flex items-center justify-between p-3 rounded-xl transition-colors hover:bg-red-50 group">
                            <div class="flex items-center gap-3">
                                @if($ingredient->current_quantity <= 0)
                                    <span class="w-2.5 h-2.5 rounded-full bg-red-500 shrink-0"></span>
                                @else
                                    <span class="w-2.5 h-2.5 rounded-full bg-yellow-400 shrink-0"></span>
                                @endif
                                <span class="text-sm font-medium text-neutral-700 group-hover:text-neutral-900">{{ $ingredient->name }}</span>
                            </div>
                            <div class="text-right">
                                <span class="text-sm font-bold {{ $ingredient->current_quantity <= 0 ? 'text-red-600' : 'text-yellow-600' }}">
                                    {{ number_format($ingredient->current_quantity, 1) }} {{ $ingredient->unit->shortLabel() }}
                                </span>
                                <p class="text-xs text-neutral-400">min: {{ number_format($ingredient->min_quantity, 1) }}</p>
                            </div>
                        </a>
                    @endforeach
                </div>
                <div class="flex items-center gap-3 mt-4 pt-3 border-t border-neutral-100">
                    <a href="{{ route('restaurant.stock.bulk-update') }}" class="flex-1 btn btn-primary text-sm py-2 text-center">
                        Mettre à jour le stock
                    </a>
                    <a href="{{ route('restaurant.stock.alerts') }}" class="flex-1 btn btn-outline text-sm py-2 text-center">
                        Voir tout
                    </a>
                </div>
            </div>
            @endif
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.5.1/dist/chart.umd.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Revenue Trend Chart (Line)
            const revenueTrendData = @js($this->revenueTrend);
            if (revenueTrendData.labels.length > 0) {
                const ctxRevenue = document.getElementById('revenueTrendChart');
                new Chart(ctxRevenue, {
                    type: 'line',
                    data: {
                        labels: revenueTrendData.labels,
                        datasets: [{
                            label: 'CA Net (FCFA)',
                            data: revenueTrendData.data,
                            borderColor: 'rgb(249, 115, 22)',
                            backgroundColor: 'rgba(249, 115, 22, 0.1)',
                            borderWidth: 3,
                            fill: true,
                            tension: 0.4,
                            pointRadius: 4,
                            pointHoverRadius: 6,
                            pointBackgroundColor: 'rgb(249, 115, 22)',
                            pointBorderColor: '#fff',
                            pointBorderWidth: 2,
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                padding: 12,
                                titleFont: { size: 13, weight: 'bold' },
                                bodyFont: { size: 12 },
                                callbacks: {
                                    label: function(context) {
                                        return 'CA Net: ' + context.parsed.y.toLocaleString('fr-FR') + ' F';
                                    }
                                }
                            }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                ticks: {
                                    callback: function(value) {
                                        return value.toLocaleString('fr-FR') + ' F';
                                    }
                                },
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.05)'
                                }
                            },
                            x: {
                                grid: {
                                    display: false
                                }
                            }
                        }
                    }
                });
            }

            // Orders by Type Chart (Pie)
            const ordersByTypeData = @js($this->ordersByType);
            if (ordersByTypeData.labels.length > 0) {
                const ctxType = document.getElementById('ordersByTypeChart');
                new Chart(ctxType, {
                    type: 'doughnut',
                    data: {
                        labels: ordersByTypeData.labels,
                        datasets: [{
                            data: ordersByTypeData.data,
                            backgroundColor: [
                                'rgba(249, 115, 22, 0.8)',
                                'rgba(34, 197, 94, 0.8)',
                                'rgba(59, 130, 246, 0.8)',
                                'rgba(168, 85, 247, 0.8)'
                            ],
                            borderColor: '#fff',
                            borderWidth: 3,
                            hoverOffset: 10
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                position: 'bottom',
                                labels: {
                                    padding: 15,
                                    font: { size: 12, weight: '500' },
                                    usePointStyle: true,
                                    pointStyle: 'circle'
                                }
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                padding: 12,
                                titleFont: { size: 13, weight: 'bold' },
                                bodyFont: { size: 12 },
                                callbacks: {
                                    label: function(context) {
                                        const total = context.dataset.data.reduce((a, b) => a + b, 0);
                                        const percentage = ((context.parsed / total) * 100).toFixed(1);
                                        return context.label + ': ' + context.parsed + ' (' + percentage + '%)';
                                    }
                                }
                            }
                        }
                    }
                });
            }

            // Top Dishes Chart (Horizontal Bar)
            const topDishesData = @js($this->topDishes);
            if (topDishesData.labels.length > 0) {
                const ctxDishes = document.getElementById('topDishesChart');
                new Chart(ctxDishes, {
                    type: 'bar',
                    data: {
                        labels: topDishesData.labels,
                        datasets: [{
                            label: 'Quantité vendue',
                            data: topDishesData.data,
                            backgroundColor: [
                                'rgba(249, 115, 22, 0.8)',
                                'rgba(251, 146, 60, 0.8)',
                                'rgba(253, 186, 116, 0.8)',
                                'rgba(254, 215, 170, 0.8)',
                                'rgba(255, 237, 213, 0.8)'
                            ],
                            borderColor: [
                                'rgb(249, 115, 22)',
                                'rgb(251, 146, 60)',
                                'rgb(253, 186, 116)',
                                'rgb(254, 215, 170)',
                                'rgb(255, 237, 213)'
                            ],
                            borderWidth: 2,
                            borderRadius: 8,
                            borderSkipped: false
                        }]
                    },
                    options: {
                        indexAxis: 'y',
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: {
                                display: false
                            },
                            tooltip: {
                                backgroundColor: 'rgba(0, 0, 0, 0.8)',
                                padding: 12,
                                titleFont: { size: 13, weight: 'bold' },
                                bodyFont: { size: 12 },
                                callbacks: {
                                    label: function(context) {
                                        return 'Vendu: ' + context.parsed.x + ' fois';
                                    }
                                }
                            }
                        },
                        scales: {
                            x: {
                                beginAtZero: true,
                                ticks: {
                                    precision: 0
                                },
                                grid: {
                                    color: 'rgba(0, 0, 0, 0.05)'
                                }
                            },
                            y: {
                                grid: {
                                    display: false
                                },
                                ticks: {
                                    font: { size: 12, weight: '500' }
                                }
                            }
                        }
                    }
                });
            }
        });
    </script>
    @endpush
</div>
