<x-layouts.admin-restaurant title="Dashboard">
    <!-- Welcome Banner -->
    <div class="bg-gradient-to-r from-primary-500 to-primary-600 rounded-2xl p-6 mb-8 text-white">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold">Bonjour, {{ auth()->user()->name ?? 'Restaurateur' }} ! 👋</h1>
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
                    <p class="text-3xl font-bold text-neutral-900 mt-1">24</p>
                    <p class="text-sm text-secondary-600 mt-2 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                        +12% vs hier
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
                    <p class="text-3xl font-bold text-neutral-900 mt-1">185 000 <span class="text-lg font-normal">F</span></p>
                    <p class="text-sm text-secondary-600 mt-2 flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                        +8% vs hier
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
                    <p class="text-3xl font-bold text-neutral-900 mt-1">5</p>
                    <p class="text-sm text-accent-600 mt-2 flex items-center gap-1">
                        <span class="w-2 h-2 bg-accent-500 rounded-full animate-pulse"></span>
                        À traiter maintenant
                    </p>
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
                    <p class="text-3xl font-bold text-neutral-900 mt-1">32</p>
                    <p class="text-sm text-neutral-500 mt-2">Sur 50 autorisés</p>
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
                    @foreach([
                        ['id' => '#CMD-2024', 'client' => 'Jean Kouassi', 'total' => '12 500', 'status' => 'pending', 'time' => '2 min'],
                        ['id' => '#CMD-2023', 'client' => 'Marie Bamba', 'total' => '8 900', 'status' => 'preparing', 'time' => '15 min'],
                        ['id' => '#CMD-2022', 'client' => 'Yao Koné', 'total' => '15 200', 'status' => 'ready', 'time' => '25 min'],
                        ['id' => '#CMD-2021', 'client' => 'Awa Diallo', 'total' => '6 500', 'status' => 'completed', 'time' => '1h'],
                        ['id' => '#CMD-2020', 'client' => 'Moussa Traoré', 'total' => '22 000', 'status' => 'completed', 'time' => '2h'],
                    ] as $order)
                    <div class="p-4 hover:bg-neutral-50 transition-colors">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <div class="w-10 h-10 bg-neutral-100 rounded-full flex items-center justify-center">
                                    <span class="text-sm font-bold text-neutral-600">{{ substr($order['client'], 0, 1) }}</span>
                                </div>
                                <div>
                                    <p class="font-medium text-neutral-900">{{ $order['client'] }}</p>
                                    <p class="text-sm text-neutral-500">{{ $order['id'] }} · Il y a {{ $order['time'] }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <span class="font-bold text-neutral-900">{{ $order['total'] }} F</span>
                                @php
                                    $statusColors = [
                                        'pending' => 'bg-yellow-100 text-yellow-700',
                                        'preparing' => 'bg-blue-100 text-blue-700',
                                        'ready' => 'bg-primary-100 text-primary-700',
                                        'completed' => 'bg-secondary-100 text-secondary-700',
                                    ];
                                    $statusLabels = [
                                        'pending' => 'En attente',
                                        'preparing' => 'En préparation',
                                        'ready' => 'Prêt',
                                        'completed' => 'Terminé',
                                    ];
                                @endphp
                                <span class="badge {{ $statusColors[$order['status']] }}">
                                    {{ $statusLabels[$order['status']] }}
                                </span>
                            </div>
                        </div>
                    </div>
                    @endforeach
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
                    @foreach([
                        ['name' => 'Poulet braisé', 'orders' => 48, 'trend' => 'up'],
                        ['name' => 'Attiéké poisson', 'orders' => 35, 'trend' => 'up'],
                        ['name' => 'Alloco garni', 'orders' => 28, 'trend' => 'down'],
                    ] as $dish)
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 bg-neutral-200 rounded-xl"></div>
                            <div>
                                <p class="font-medium text-neutral-900">{{ $dish['name'] }}</p>
                                <p class="text-sm text-neutral-500">{{ $dish['orders'] }} commandes</p>
                            </div>
                        </div>
                        @if($dish['trend'] === 'up')
                            <svg class="w-5 h-5 text-secondary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                            </svg>
                        @else
                            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                            </svg>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin-restaurant>

