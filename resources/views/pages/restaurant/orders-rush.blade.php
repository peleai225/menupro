@php
    $restaurant = auth()->user()->restaurant;
    $subscription = $restaurant?->activeSubscription;
@endphp

<x-layouts.admin-restaurant title="Mode Rush - Commandes" :restaurant="$restaurant" :subscription="$subscription">
    <div class="space-y-4" x-data="rushMode()" x-init="init()">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <div class="flex items-center gap-2">
                    <span class="px-3 py-1 bg-red-100 text-red-700 rounded-full text-xs font-bold">RUSH</span>
                    <h1 class="text-2xl font-bold text-neutral-900">Mode Rush</h1>
                </div>
                <p class="text-neutral-500 mt-1">Vue simplifiée pour gérer rapidement les commandes en période de pic</p>
            </div>
            <div class="flex items-center gap-3">
                <!-- Quick Stats -->
                <div class="flex items-center gap-4 text-sm">
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-emerald-500"></span>
                        <span class="font-semibold text-neutral-900">{{ $stats['new'] ?? 0 }}</span>
                        <span class="text-neutral-600">nouvelles</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-amber-500"></span>
                        <span class="font-semibold text-neutral-900">{{ $stats['preparing'] ?? 0 }}</span>
                        <span class="text-neutral-600">en prép.</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-3 h-3 rounded-full bg-blue-500"></span>
                        <span class="font-semibold text-neutral-900">{{ $stats['ready'] ?? 0 }}</span>
                        <span class="text-neutral-600">prêtes</span>
                    </div>
                </div>
                <!-- View Switcher -->
                <div class="flex items-center gap-2">
                    <a href="{{ route('restaurant.orders') }}" class="btn-ghost btn-sm">Liste</a>
                    <a href="{{ route('restaurant.orders.kanban') }}" class="btn-ghost btn-sm">Kanban</a>
                </div>
            </div>
        </div>

        <!-- Quick Filters -->
        <div class="flex items-center gap-3">
            <button @click="toggleNewOnly()" 
                    class="px-4 py-2 rounded-lg border transition-colors"
                    :class="filters.newOnly ? 'bg-emerald-100 border-emerald-300 text-emerald-700 font-medium' : 'bg-white border-neutral-200 text-neutral-700 hover:bg-neutral-50'">
                <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Nouvelles uniquement
            </button>
            <button @click="toggleAutoRefresh()" 
                    class="px-4 py-2 rounded-lg border transition-colors"
                    :class="autoRefresh ? 'bg-blue-100 border-blue-300 text-blue-700 font-medium' : 'bg-white border-neutral-200 text-neutral-700 hover:bg-neutral-50'">
                <svg class="w-4 h-4 inline-block mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                <span x-text="autoRefresh ? 'Auto ON' : 'Auto OFF'"></span>
            </button>
        </div>

        <!-- Orders List -->
        <div class="space-y-3" id="orders-list">
            @forelse($orders as $order)
                @include('pages.restaurant.orders-rush-card', ['order' => $order])
            @empty
                <div class="card p-12 text-center">
                    <svg class="w-16 h-16 text-neutral-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <p class="text-neutral-500 text-lg">Aucune commande active</p>
                    <p class="text-neutral-400 text-sm mt-2">Toutes les commandes sont terminées ou annulées</p>
                </div>
            @endforelse
        </div>
    </div>

    @push('scripts')
    <script>
        function rushMode() {
            return {
                orders: @json($ordersData ?? []),
                filters: {
                    newOnly: false,
                },
                autoRefresh: true,
                refreshInterval: null,

                init() {
                    if (this.autoRefresh) {
                        this.startAutoRefresh();
                    }
                },

                async refreshData() {
                    try {
                        const params = new URLSearchParams();
                        if (this.filters.newOnly) {
                            params.append('new_only', '1');
                        }
                        const response = await fetch(`{{ route('restaurant.orders.rush.data') }}?${params}`);
                        const data = await response.json();

                        if (data.orders) {
                            this.orders = data.orders;
                            this.updateDOM();
                        }
                    } catch (error) {
                        console.error('Error refreshing data:', error);
                    }
                },

                updateDOM() {
                    const container = document.getElementById('orders-list');
                    if (!container) return;

                    container.innerHTML = '';

                    if (this.orders.length === 0) {
                        container.innerHTML = `
                            <div class="card p-12 text-center">
                                <svg class="w-16 h-16 text-neutral-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                </svg>
                                <p class="text-neutral-500 text-lg">Aucune commande active</p>
                            </div>
                        `;
                        return;
                    }

                    this.orders.forEach(order => {
                        const card = this.createOrderCard(order);
                        container.appendChild(card);
                    });
                },

                createOrderCard(order) {
                    const div = document.createElement('div');
                    div.className = 'order-card';
                    div.id = `order-${order.id}`;
                    div.innerHTML = this.getOrderCardHTML(order);
                    return div;
                },

                getOrderCardHTML(order) {
                    const statusColors = {
                        'paid': 'bg-emerald-100 text-emerald-700 border-emerald-200',
                        'confirmed': 'bg-blue-100 text-blue-700 border-blue-200',
                        'preparing': 'bg-amber-100 text-amber-700 border-amber-200',
                        'ready': 'bg-purple-100 text-purple-700 border-purple-200',
                    };
                    const statusColor = statusColors[order.status] || 'bg-neutral-100 text-neutral-700 border-neutral-200';

                    return `
                        <div class="card p-4 hover:shadow-md transition-shadow">
                            <div class="flex items-start justify-between gap-4">
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-center gap-3 mb-2">
                                        <span class="font-mono text-sm font-bold text-neutral-900">#${order.reference}</span>
                                        <span class="px-2 py-0.5 rounded text-xs font-medium border ${statusColor}">
                                            ${order.status_label}
                                        </span>
                                        <span class="text-xs text-neutral-500">${order.created_at_human}</span>
                                    </div>
                                    <div class="text-sm font-medium text-neutral-900 mb-1">
                                        ${order.customer_name || 'Client'} • ${order.items_count} article(s)
                                    </div>
                                    <div class="text-xs text-neutral-500 line-clamp-2 mb-2">
                                        ${order.items.map(i => `${i.quantity}x ${i.dish_name}`).join(', ')}
                                    </div>
                                    <div class="text-lg font-bold text-primary-600">
                                        ${parseInt(order.total).toLocaleString('fr-FR')} F
                                    </div>
                                </div>
                                <div class="flex flex-col gap-2">
                                    ${this.getActionButtons(order)}
                                </div>
                            </div>
                        </div>
                    `;
                },

                getActionButtons(order) {
                    const buttons = [];
                    const self = this;
                    
                    if (order.status === 'paid') {
                        buttons.push(`
                            <button onclick="window.rushQuickAction('confirm', ${order.id})" 
                                    class="btn-primary btn-sm whitespace-nowrap">
                                ✓ Confirmer
                            </button>
                            <button onclick="window.rushQuickAction('prepare', ${order.id})" 
                                    class="btn-secondary btn-sm whitespace-nowrap">
                                🍳 Préparer
                            </button>
                        `);
                    } else if (order.status === 'confirmed') {
                        buttons.push(`
                            <button onclick="window.rushQuickAction('prepare', ${order.id})" 
                                    class="btn-primary btn-sm whitespace-nowrap">
                                🍳 Préparer
                            </button>
                        `);
                    } else if (order.status === 'preparing') {
                        buttons.push(`
                            <button onclick="window.rushQuickAction('ready', ${order.id})" 
                                    class="btn-primary btn-sm whitespace-nowrap">
                                ✓ Prête
                            </button>
                        `);
                    } else if (order.status === 'ready') {
                        buttons.push(`
                            <button onclick="window.rushQuickAction('complete', ${order.id})" 
                                    class="btn-primary btn-sm whitespace-nowrap">
                                ✓ Terminer
                            </button>
                        `);
                    }

                    buttons.push(`
                        <a href="/dashboard/commandes/${order.id}" 
                           class="btn-ghost btn-sm text-center whitespace-nowrap">
                            Détails
                        </a>
                    `);

                    return buttons.join('');
                },

                async quickAction(action, orderId) {
                    const actions = {
                        'confirm': '{{ route("restaurant.orders.rush.confirm", ":id") }}',
                        'prepare': '{{ route("restaurant.orders.rush.prepare", ":id") }}',
                        'ready': '{{ route("restaurant.orders.rush.ready", ":id") }}',
                        'complete': '{{ route("restaurant.orders.rush.complete", ":id") }}',
                    };

                    const url = actions[action]?.replace(':id', orderId);
                    if (!url) return;

                    try {
                        const response = await fetch(url, {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                            },
                        });

                        const data = await response.json();

                        if (data.success) {
                            await this.refreshData();
                        } else {
                            alert(data.message || 'Erreur lors de l\'action.');
                        }
                    } catch (error) {
                        console.error('Error:', error);
                        alert('Erreur lors de l\'action.');
                    }
                },

                toggleNewOnly() {
                    this.filters.newOnly = !this.filters.newOnly;
                    this.refreshData();
                },

                toggleAutoRefresh() {
                    this.autoRefresh = !this.autoRefresh;
                    if (this.autoRefresh) {
                        this.startAutoRefresh();
                    } else {
                        this.stopAutoRefresh();
                    }
                },

                startAutoRefresh() {
                    this.refreshInterval = setInterval(() => {
                        this.refreshData();
                    }, 8000); // Every 8 seconds
                },

                stopAutoRefresh() {
                    if (this.refreshInterval) {
                        clearInterval(this.refreshInterval);
                        this.refreshInterval = null;
                    }
                }
            };
        }

        // Make quickAction globally available
        window.rushQuickAction = async function(action, orderId) {
            const actions = {
                'confirm': '{{ route("restaurant.orders.rush.confirm", ":id") }}',
                'prepare': '{{ route("restaurant.orders.rush.prepare", ":id") }}',
                'ready': '{{ route("restaurant.orders.rush.ready", ":id") }}',
                'complete': '{{ route("restaurant.orders.rush.complete", ":id") }}',
            };

            const url = actions[action]?.replace(':id', orderId);
            if (!url) return;

            try {
                const response = await fetch(url, {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json',
                    },
                });

                const data = await response.json();

                if (data.success) {
                    // Find Alpine component and refresh
                    const element = document.querySelector('[x-data*="rushMode"]');
                    if (element && element._x_dataStack && element._x_dataStack[0]) {
                        await element._x_dataStack[0].refreshData();
                    } else {
                        location.reload();
                    }
                } else {
                    alert(data.message || 'Erreur lors de l\'action.');
                }
            } catch (error) {
                console.error('Error:', error);
                alert('Erreur lors de l\'action.');
            }
        };
    </script>
    @endpush
</x-layouts.admin-restaurant>
