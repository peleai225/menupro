<x-layouts.admin-restaurant title="Client — {{ $customer->customer_name }}">
    <!-- Header -->
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('restaurant.customers') }}" class="p-2 hover:bg-neutral-100 rounded-lg">
            <svg class="w-5 h-5 text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-neutral-900">{{ $customer->customer_name }}</h1>
            <p class="text-neutral-500 text-sm mt-0.5">{{ $customer->customer_email }}</p>
        </div>
    </div>

    <!-- Flash -->
    @if(session('success'))
        <div class="mb-6 p-4 bg-secondary-50 border border-secondary-200 rounded-xl text-secondary-700">{{ session('success') }}</div>
    @endif

    <!-- Stats -->
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-8">
        <div class="card p-4 text-center">
            <p class="text-2xl font-bold text-neutral-900">{{ $customer->orders_count }}</p>
            <p class="text-sm text-neutral-500 mt-1">Commandes</p>
        </div>
        <div class="card p-4 text-center">
            <p class="text-2xl font-bold text-primary-600">{{ number_format($customer->total_spent, 0, ',', ' ') }} FCFA</p>
            <p class="text-sm text-neutral-500 mt-1">Total dépensé</p>
        </div>
        <div class="card p-4 text-center">
            <p class="text-2xl font-bold text-neutral-900">{{ number_format($customer->average_order, 0, ',', ' ') }} FCFA</p>
            <p class="text-sm text-neutral-500 mt-1">Panier moyen</p>
        </div>
        <div class="card p-4 text-center">
            <p class="text-2xl font-bold text-neutral-900">
                {{ $customer->last_order_at ? \Carbon\Carbon::parse($customer->last_order_at)->locale('fr')->diffForHumans() : '—' }}
            </p>
            <p class="text-sm text-neutral-500 mt-1">Dernière commande</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Colonne gauche : infos + plats favoris -->
        <div class="space-y-6">
            <!-- Infos contact -->
            <div class="card p-5">
                <h3 class="font-semibold text-neutral-800 mb-4">Informations</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4 text-neutral-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <span class="text-neutral-700">{{ $customer->customer_email }}</span>
                    </div>
                    @if($customer->customer_phone)
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4 text-neutral-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        <span class="text-neutral-700">{{ $customer->customer_phone }}</span>
                    </div>
                    @endif
                    <div class="flex items-center gap-3">
                        <svg class="w-4 h-4 text-neutral-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        <span class="text-neutral-500">
                            Client depuis {{ $customer->first_order_at ? \Carbon\Carbon::parse($customer->first_order_at)->locale('fr')->isoFormat('D MMM YYYY') : '—' }}
                        </span>
                    </div>
                </div>
            </div>

            <!-- Plats favoris -->
            @if($favoriteDishes->isNotEmpty())
            <div class="card p-5">
                <h3 class="font-semibold text-neutral-800 mb-4">Plats préférés</h3>
                <div class="space-y-2">
                    @foreach($favoriteDishes as $dish)
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-neutral-700">{{ $dish->dish_name }}</span>
                        <span class="badge badge-neutral">{{ $dish->total_ordered }}×</span>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <!-- Historique commandes -->
        <div class="lg:col-span-2">
            <div class="card overflow-hidden">
                <div class="px-5 py-4 border-b border-neutral-100">
                    <h3 class="font-semibold text-neutral-800">Historique des commandes ({{ $orders->count() }})</h3>
                </div>
                @if($orders->isEmpty())
                    <div class="p-8 text-center text-neutral-400">Aucune commande.</div>
                @else
                    <div class="divide-y divide-neutral-100">
                        @foreach($orders as $order)
                        <div class="px-5 py-4 flex items-center justify-between gap-4">
                            <div class="min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <a href="{{ route('restaurant.orders.show', $order) }}"
                                       class="font-medium text-neutral-800 hover:text-primary-600 text-sm">
                                        #{{ $order->reference }}
                                    </a>
                                    @php
                                        $sc = match($order->status->color()) {
                                            'success' => 'badge-success',
                                            'warning' => 'badge-warning',
                                            'error'   => 'badge-error',
                                            default   => 'badge-neutral',
                                        };
                                    @endphp
                                    <span class="badge {{ $sc }} text-xs">{{ $order->status->label() }}</span>
                                </div>
                                <p class="text-xs text-neutral-400 mt-0.5">
                                    {{ $order->created_at->locale('fr')->isoFormat('D MMM YYYY, HH:mm') }}
                                    • {{ $order->items->count() }} article(s)
                                </p>
                            </div>
                            <span class="text-sm font-semibold text-neutral-800 whitespace-nowrap">
                                {{ number_format($order->total, 0, ',', ' ') }} FCFA
                            </span>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-layouts.admin-restaurant>
