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

    <!-- Welcome Banner -->
    <div class="bg-gradient-to-r from-primary-500 to-primary-600 rounded-2xl p-4 sm:p-6 mb-6 sm:mb-8 text-white">
        <div class="flex flex-wrap items-center justify-between gap-y-2">
            <div class="min-w-0">
                <h1 class="text-xl sm:text-2xl font-bold truncate">Bonjour, {{ auth()->user()->name }}</h1>
                <p class="text-primary-100 mt-1">Voici un résumé de l'activité de votre restaurant aujourd'hui.</p>
            </div>
            <div class="hidden md:block">
                <span class="text-sm text-primary-200">{{ now()->locale('fr')->isoFormat('dddd D MMMM YYYY') }}</span>
            </div>
        </div>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <!-- Orders Today -->
        <div class="card p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-neutral-500">Commandes aujourd'hui</p>
                    <p class="text-3xl font-bold text-neutral-900 mt-1">{{ $this->stats['orders_today'] }}</p>
                    <p class="text-sm mt-2 flex items-center gap-1 {{ $this->stats['orders_change'] >= 0 ? 'text-secondary-600' : 'text-red-600' }}">
                        @if($this->stats['orders_change'] >= 0)
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                            </svg>
                        @else
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                            </svg>
                        @endif
                        {{ $this->stats['orders_change'] >= 0 ? '+' : '' }}{{ $this->stats['orders_change'] }}% vs hier
                    </p>
                </div>
                <div class="w-14 h-14 bg-primary-100 rounded-2xl flex items-center justify-center">
                    <svg class="w-7 h-7 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Revenue Today -->
        <div class="card p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-neutral-500">Chiffre d'affaires</p>
                    <p class="text-3xl font-bold text-neutral-900 mt-1">{{ number_format($this->stats['revenue_today'], 0, ',', ' ') }} <span class="text-lg font-normal">F</span></p>
                    <p class="text-sm mt-2 flex items-center gap-1 {{ $this->stats['revenue_change'] >= 0 ? 'text-secondary-600' : 'text-red-600' }}">
                        @if($this->stats['revenue_change'] >= 0)
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                            </svg>
                        @else
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                            </svg>
                        @endif
                        {{ $this->stats['revenue_change'] >= 0 ? '+' : '' }}{{ $this->stats['revenue_change'] }}% vs hier
                    </p>
                </div>
                <div class="w-14 h-14 bg-secondary-100 rounded-2xl flex items-center justify-center">
                    <svg class="w-7 h-7 text-secondary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Pending Orders -->
        <div class="card p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-neutral-500">En attente</p>
                    <p class="text-3xl font-bold text-neutral-900 mt-1">{{ $this->stats['pending_orders'] }}</p>
                    @if($this->stats['pending_orders'] > 0)
                        <p class="text-sm text-accent-600 mt-2 flex items-center gap-1">
                            <span class="w-2 h-2 bg-accent-500 rounded-full animate-pulse"></span>
                            À traiter maintenant
                        </p>
                    @else
                        <p class="text-sm text-neutral-500 mt-2">Aucune commande en attente</p>
                    @endif
                </div>
                <div class="w-14 h-14 bg-accent-100 rounded-2xl flex items-center justify-center">
                    <svg class="w-7 h-7 text-accent-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Total Dishes -->
        <div class="card p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-neutral-500">Plats au menu</p>
                    <p class="text-3xl font-bold text-neutral-900 mt-1">{{ $this->stats['dishes_count'] }}</p>
                    <p class="text-sm text-neutral-500 mt-2">Sur {{ $this->stats['max_dishes'] }} autorisés</p>
                </div>
                <div class="w-14 h-14 bg-neutral-100 rounded-2xl flex items-center justify-center">
                    <svg class="w-7 h-7 text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
            </div>
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
            <div class="card p-6 border-l-4 border-accent-500">
                <h2 class="text-lg font-bold text-neutral-900 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-accent-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    Alertes stock
                </h2>
                <div class="space-y-3">
                    @foreach($this->stockAlerts as $ingredient)
                        <div class="flex items-center justify-between p-2 bg-accent-50 rounded-lg">
                            <span class="text-sm font-medium text-neutral-700">{{ $ingredient->name }}</span>
                            <span class="text-sm text-accent-600 font-bold">{{ $ingredient->current_quantity }} {{ $ingredient->unit }}</span>
                        </div>
                    @endforeach
                </div>
                <a href="{{ route('restaurant.stock.alerts') }}" class="mt-4 text-sm text-accent-600 hover:text-accent-700 font-medium block text-center">
                    Voir toutes les alertes →
                </a>
            </div>
            @endif
        </div>
    </div>
</div>
