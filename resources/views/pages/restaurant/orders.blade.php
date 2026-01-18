<x-layouts.admin-restaurant title="Commandes">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-neutral-900">Commandes</h1>
            <p class="text-neutral-500 mt-1">Gérez et suivez toutes vos commandes.</p>
        </div>
        <div class="flex items-center gap-3">
            <span class="badge badge-warning">5 en attente</span>
            <button class="btn btn-outline btn-sm">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Exporter
            </button>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="card p-4">
            <p class="text-sm text-neutral-500">Aujourd'hui</p>
            <p class="text-2xl font-bold text-neutral-900">24</p>
        </div>
        <div class="card p-4">
            <p class="text-sm text-neutral-500">Cette semaine</p>
            <p class="text-2xl font-bold text-neutral-900">156</p>
        </div>
        <div class="card p-4">
            <p class="text-sm text-neutral-500">CA du jour</p>
            <p class="text-2xl font-bold text-neutral-900">185K F</p>
        </div>
        <div class="card p-4">
            <p class="text-sm text-neutral-500">Panier moyen</p>
            <p class="text-2xl font-bold text-neutral-900">7 700 F</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="card p-4 mb-6">
        <div class="flex flex-wrap gap-2">
            <button class="px-4 py-2 bg-primary-500 text-white rounded-lg text-sm font-medium">Toutes</button>
            <button class="px-4 py-2 bg-neutral-100 text-neutral-700 rounded-lg text-sm font-medium hover:bg-neutral-200">En attente (5)</button>
            <button class="px-4 py-2 bg-neutral-100 text-neutral-700 rounded-lg text-sm font-medium hover:bg-neutral-200">En préparation (3)</button>
            <button class="px-4 py-2 bg-neutral-100 text-neutral-700 rounded-lg text-sm font-medium hover:bg-neutral-200">Prêtes (2)</button>
            <button class="px-4 py-2 bg-neutral-100 text-neutral-700 rounded-lg text-sm font-medium hover:bg-neutral-200">Terminées</button>
        </div>
    </div>

    <!-- Orders List -->
    <div class="space-y-4">
        @foreach([
            ['id' => 'CMD-2024', 'client' => 'Jean Kouassi', 'phone' => '+225 07 00 00 01', 'items' => 3, 'total' => '12 500', 'status' => 'pending', 'time' => '2 min', 'type' => 'delivery'],
            ['id' => 'CMD-2023', 'client' => 'Marie Bamba', 'phone' => '+225 07 00 00 02', 'items' => 2, 'total' => '8 900', 'status' => 'preparing', 'time' => '15 min', 'type' => 'pickup'],
            ['id' => 'CMD-2022', 'client' => 'Yao Koné', 'phone' => '+225 07 00 00 03', 'items' => 4, 'total' => '15 200', 'status' => 'ready', 'time' => '25 min', 'type' => 'dine_in'],
            ['id' => 'CMD-2021', 'client' => 'Awa Diallo', 'phone' => '+225 07 00 00 04', 'items' => 1, 'total' => '6 500', 'status' => 'completed', 'time' => '1h', 'type' => 'delivery'],
            ['id' => 'CMD-2020', 'client' => 'Moussa Traoré', 'phone' => '+225 07 00 00 05', 'items' => 5, 'total' => '22 000', 'status' => 'completed', 'time' => '2h', 'type' => 'pickup'],
        ] as $order)
        <div class="card p-4 hover:shadow-md transition-shadow">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 bg-neutral-100 rounded-full flex items-center justify-center flex-shrink-0">
                        <span class="text-lg font-bold text-neutral-600">{{ substr($order['client'], 0, 1) }}</span>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <span class="font-bold text-neutral-900">#{{ $order['id'] }}</span>
                            @php
                                $typeColors = ['delivery' => 'bg-blue-100 text-blue-700', 'pickup' => 'bg-purple-100 text-purple-700', 'dine_in' => 'bg-green-100 text-green-700'];
                                $typeLabels = ['delivery' => 'Livraison', 'pickup' => 'À emporter', 'dine_in' => 'Sur place'];
                            @endphp
                            <span class="badge {{ $typeColors[$order['type']] }} text-xs">{{ $typeLabels[$order['type']] }}</span>
                        </div>
                        <p class="text-neutral-700">{{ $order['client'] }}</p>
                        <p class="text-sm text-neutral-500">{{ $order['phone'] }} · {{ $order['items'] }} articles · Il y a {{ $order['time'] }}</p>
                    </div>
                </div>
                <div class="flex items-center gap-4">
                    <div class="text-right">
                        <p class="text-xl font-bold text-neutral-900">{{ $order['total'] }} F</p>
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
                        <span class="badge {{ $statusColors[$order['status']] }}">{{ $statusLabels[$order['status']] }}</span>
                    </div>
                    <a href="{{ route('restaurant.orders.show', $order['id']) }}" class="btn btn-outline btn-sm">
                        Voir
                    </a>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    <!-- Pagination -->
    <div class="flex items-center justify-center mt-8">
        <nav class="flex items-center gap-1">
            <button class="p-2 rounded-lg hover:bg-neutral-100 text-neutral-400">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </button>
            <button class="w-10 h-10 rounded-lg bg-primary-500 text-white font-medium">1</button>
            <button class="w-10 h-10 rounded-lg hover:bg-neutral-100 text-neutral-700 font-medium">2</button>
            <button class="w-10 h-10 rounded-lg hover:bg-neutral-100 text-neutral-700 font-medium">3</button>
            <button class="p-2 rounded-lg hover:bg-neutral-100 text-neutral-700">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                </svg>
            </button>
        </nav>
    </div>
</x-layouts.admin-restaurant>

