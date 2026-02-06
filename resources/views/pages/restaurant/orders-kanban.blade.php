@php
    $restaurant = auth()->user()->restaurant;
    $subscription = $restaurant?->activeSubscription;
@endphp

<x-layouts.admin-restaurant title="Vue Kanban - Commandes" :restaurant="$restaurant" :subscription="$subscription">
    <div class="space-y-6" x-data="kanbanBoard()" x-init="init()">
        <!-- Header -->
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold text-neutral-900">Vue Kanban</h1>
                <p class="text-neutral-500 mt-1">Gérez vos commandes avec une vue d'ensemble visuelle</p>
            </div>
            <div class="flex items-center gap-3">
                <!-- Stats -->
                <div class="flex items-center gap-4 text-sm">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <span class="text-neutral-600">{{ $stats['pending'] ?? 0 }} en attente</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                        <span class="text-neutral-600">{{ $stats['preparing'] ?? 0 }} en préparation</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                        <span class="text-neutral-600">{{ $stats['ready'] ?? 0 }} prêtes</span>
                    </div>
                </div>
                <!-- Auto-refresh indicator -->
                <div class="flex items-center gap-2 text-xs text-neutral-500">
                    <span class="w-2 h-2 rounded-full bg-emerald-500" :class="{ 'animate-pulse': autoRefresh }"></span>
                    <span x-text="autoRefresh ? 'Auto-refresh activé' : 'Auto-refresh désactivé'"></span>
                </div>
            </div>
        </div>

        <!-- Filters -->
        <div class="card p-4">
            <div class="flex flex-wrap items-center gap-4">
                <div class="flex-1 min-w-[200px]">
                    <input type="text" 
                           x-model="filters.search" 
                           @input.debounce.300ms="applyFilters()"
                           placeholder="Rechercher (référence, client, téléphone)..." 
                           class="form-input w-full">
                </div>
                <select x-model="filters.type" @change="applyFilters()" class="form-select">
                    <option value="">Tous les types</option>
                    <option value="dine_in">Sur place</option>
                    <option value="takeaway">À emporter</option>
                    <option value="delivery">Livraison</option>
                </select>
                <button @click="toggleAutoRefresh()" 
                        class="btn-secondary px-4 py-2"
                        :class="{ 'bg-emerald-100 text-emerald-700': autoRefresh }">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    <span x-text="autoRefresh ? 'Désactiver' : 'Activer'"></span>
                </button>
                <a href="{{ route('restaurant.orders') }}" class="btn-ghost px-4 py-2">
                    Vue liste
                </a>
            </div>
        </div>

        <!-- Kanban Board -->
        <div class="grid grid-cols-1 lg:grid-cols-7 gap-4 overflow-x-auto pb-4">
            <!-- Pending Payment -->
            <div class="bg-white rounded-xl shadow-sm border border-neutral-200 flex flex-col min-w-[280px] max-h-[calc(100vh-250px)]">
                <div class="px-4 py-3 border-b border-neutral-200 bg-neutral-50 rounded-t-xl">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-gray-500"></span>
                            <h3 class="font-semibold text-neutral-900">En attente de paiement</h3>
                        </div>
                        <span class="text-xs text-neutral-500 bg-white px-2 py-1 rounded-full" 
                              x-text="getOrderCount('pending_payment')"></span>
                    </div>
                </div>
                <div class="p-3 space-y-3 overflow-y-auto flex-1" 
                     data-status="pending_payment"
                     id="column-pending_payment">
                    @foreach($ordersByStatus['pending_payment'] ?? [] as $order)
                        @include('pages.restaurant.orders-kanban-card', ['order' => $order])
                    @endforeach
                </div>
            </div>

            <!-- Paid -->
            <div class="bg-white rounded-xl shadow-sm border border-neutral-200 flex flex-col min-w-[280px] max-h-[calc(100vh-250px)]">
                <div class="px-4 py-3 border-b border-neutral-200 bg-emerald-50 rounded-t-xl">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                            <h3 class="font-semibold text-neutral-900">Payées</h3>
                        </div>
                        <span class="text-xs text-neutral-500 bg-white px-2 py-1 rounded-full" 
                              x-text="getOrderCount('paid')"></span>
                    </div>
                </div>
                <div class="p-3 space-y-3 overflow-y-auto flex-1" 
                     data-status="paid"
                     id="column-paid">
                    @foreach($ordersByStatus['paid'] ?? [] as $order)
                        @include('pages.restaurant.orders-kanban-card', ['order' => $order])
                    @endforeach
                </div>
            </div>

            <!-- Confirmed -->
            <div class="bg-white rounded-xl shadow-sm border border-neutral-200 flex flex-col min-w-[280px] max-h-[calc(100vh-250px)]">
                <div class="px-4 py-3 border-b border-neutral-200 bg-blue-50 rounded-t-xl">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                            <h3 class="font-semibold text-neutral-900">Confirmées</h3>
                        </div>
                        <span class="text-xs text-neutral-500 bg-white px-2 py-1 rounded-full" 
                              x-text="getOrderCount('confirmed')"></span>
                    </div>
                </div>
                <div class="p-3 space-y-3 overflow-y-auto flex-1" 
                     data-status="confirmed"
                     id="column-confirmed">
                    @foreach($ordersByStatus['confirmed'] ?? [] as $order)
                        @include('pages.restaurant.orders-kanban-card', ['order' => $order])
                    @endforeach
                </div>
            </div>

            <!-- Preparing -->
            <div class="bg-white rounded-xl shadow-sm border border-neutral-200 flex flex-col min-w-[280px] max-h-[calc(100vh-250px)]">
                <div class="px-4 py-3 border-b border-neutral-200 bg-amber-50 rounded-t-xl">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                            <h3 class="font-semibold text-neutral-900">En préparation</h3>
                        </div>
                        <span class="text-xs text-neutral-500 bg-white px-2 py-1 rounded-full" 
                              x-text="getOrderCount('preparing')"></span>
                    </div>
                </div>
                <div class="p-3 space-y-3 overflow-y-auto flex-1" 
                     data-status="preparing"
                     id="column-preparing">
                    @foreach($ordersByStatus['preparing'] ?? [] as $order)
                        @include('pages.restaurant.orders-kanban-card', ['order' => $order])
                    @endforeach
                </div>
            </div>

            <!-- Ready -->
            <div class="bg-white rounded-xl shadow-sm border border-neutral-200 flex flex-col min-w-[280px] max-h-[calc(100vh-250px)]">
                <div class="px-4 py-3 border-b border-neutral-200 bg-purple-50 rounded-t-xl">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-purple-500"></span>
                            <h3 class="font-semibold text-neutral-900">Prêtes</h3>
                        </div>
                        <span class="text-xs text-neutral-500 bg-white px-2 py-1 rounded-full" 
                              x-text="getOrderCount('ready')"></span>
                    </div>
                </div>
                <div class="p-3 space-y-3 overflow-y-auto flex-1" 
                     data-status="ready"
                     id="column-ready">
                    @foreach($ordersByStatus['ready'] ?? [] as $order)
                        @include('pages.restaurant.orders-kanban-card', ['order' => $order])
                    @endforeach
                </div>
            </div>

            <!-- Delivering -->
            <div class="bg-white rounded-xl shadow-sm border border-neutral-200 flex flex-col min-w-[280px] max-h-[calc(100vh-250px)]">
                <div class="px-4 py-3 border-b border-neutral-200 bg-indigo-50 rounded-t-xl">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-indigo-500"></span>
                            <h3 class="font-semibold text-neutral-900">En livraison</h3>
                        </div>
                        <span class="text-xs text-neutral-500 bg-white px-2 py-1 rounded-full" 
                              x-text="getOrderCount('delivering')"></span>
                    </div>
                </div>
                <div class="p-3 space-y-3 overflow-y-auto flex-1" 
                     data-status="delivering"
                     id="column-delivering">
                    @foreach($ordersByStatus['delivering'] ?? [] as $order)
                        @include('pages.restaurant.orders-kanban-card', ['order' => $order])
                    @endforeach
                </div>
            </div>

            <!-- Completed -->
            <div class="bg-white rounded-xl shadow-sm border border-neutral-200 flex flex-col min-w-[280px] max-h-[calc(100vh-250px)]">
                <div class="px-4 py-3 border-b border-neutral-200 bg-neutral-50 rounded-t-xl">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-2">
                            <span class="w-2 h-2 rounded-full bg-neutral-500"></span>
                            <h3 class="font-semibold text-neutral-900">Terminées</h3>
                        </div>
                        <span class="text-xs text-neutral-500 bg-white px-2 py-1 rounded-full" 
                              x-text="getOrderCount('completed')"></span>
                    </div>
                </div>
                <div class="p-3 space-y-3 overflow-y-auto flex-1" 
                     data-status="completed"
                     id="column-completed">
                    @foreach($ordersByStatus['completed'] ?? [] as $order)
                        @include('pages.restaurant.orders-kanban-card', ['order' => $order])
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/sortablejs@latest/Sortable.min.js"></script>
    <script>
        function kanbanBoard() {
            return {
                orders: @json($ordersByStatus),
                filters: {
                    search: '',
                    type: '',
                },
                autoRefresh: true,
                refreshInterval: null,
                sortables: {},

                init() {
                    this.initializeSortable();
                    if (this.autoRefresh) {
                        this.startAutoRefresh();
                    }
                },

                initializeSortable() {
                    const columns = ['pending_payment', 'paid', 'confirmed', 'preparing', 'ready', 'delivering', 'completed'];
                    
                    columns.forEach(status => {
                        const el = document.getElementById(`column-${status}`);
                        if (el) {
                            this.sortables[status] = new Sortable(el, {
                                group: 'kanban',
                                animation: 150,
                                ghostClass: 'opacity-50',
                                dragClass: 'shadow-lg',
                                onEnd: (evt) => {
                                    this.handleDrop(evt);
                                }
                            });
                        }
                    });
                },

                async handleDrop(evt) {
                    const orderId = evt.item.dataset.orderId;
                    const newStatus = evt.to.dataset.status;
                    const oldStatus = evt.from.dataset.status;

                    if (newStatus === oldStatus) {
                        return; // No change
                    }

                    try {
                        const response = await fetch(`/dashboard/commandes/${orderId}/kanban/status`, {
                            method: 'PATCH',
                            headers: {
                                'Content-Type': 'application/json',
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                                'Accept': 'application/json',
                            },
                            body: JSON.stringify({ status: newStatus }),
                        });

                        const data = await response.json();

                        if (data.success) {
                            // Remove from old column
                            const oldColumn = document.getElementById(`column-${oldStatus}`);
                            if (evt.oldIndex !== undefined) {
                                const item = oldColumn.children[evt.oldIndex];
                                if (item) item.remove();
                            }

                            // Refresh data
                            await this.refreshData();
                        } else {
                            // Revert on error
                            evt.item.remove();
                            const oldColumn = document.getElementById(`column-${oldStatus}`);
                            oldColumn.insertBefore(evt.item, oldColumn.children[evt.oldIndex] || null);
                            alert(data.message || 'Erreur lors du changement de statut.');
                        }
                    } catch (error) {
                        console.error('Error updating status:', error);
                        // Revert on error
                        evt.item.remove();
                        const oldColumn = document.getElementById(`column-${oldStatus}`);
                        oldColumn.insertBefore(evt.item, oldColumn.children[evt.oldIndex] || null);
                        alert('Erreur lors du changement de statut.');
                    }
                },

                async refreshData() {
                    try {
                        const params = new URLSearchParams(this.filters);
                        const response = await fetch(`{{ route('restaurant.orders.kanban.data') }}?${params}`);
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
                    const statuses = ['pending_payment', 'paid', 'confirmed', 'preparing', 'ready', 'delivering', 'completed'];
                    
                    statuses.forEach(status => {
                        const column = document.getElementById(`column-${status}`);
                        if (!column) return;

                        // Clear column
                        column.innerHTML = '';

                        // Add orders
                        const orders = this.orders[status] || [];
                        orders.forEach(order => {
                            const card = this.createOrderCard(order);
                            column.appendChild(card);
                        });
                    });

                    // Reinitialize sortable
                    this.initializeSortable();
                },

                createOrderCard(order) {
                    const div = document.createElement('div');
                    div.className = 'order-card bg-white border border-neutral-200 rounded-lg p-3 cursor-move hover:shadow-md transition-shadow';
                    div.dataset.orderId = order.id;
                    div.innerHTML = `
                        <div class="flex items-center justify-between mb-2">
                            <span class="font-mono text-xs font-semibold text-neutral-900">#${order.reference}</span>
                            <span class="text-xs text-neutral-500">${new Date(order.created_at).toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' })}</span>
                        </div>
                        <div class="text-sm font-medium text-neutral-900 mb-1">
                            ${order.customer_name || 'Client'} • ${order.items_count || 0} article(s)
                        </div>
                        <div class="text-xs text-neutral-500 mb-2">
                            ${order.items?.map(i => `${i.quantity}x ${i.dish_name}`).join(', ') || ''}
                        </div>
                        <div class="flex items-center justify-between">
                            <span class="text-sm font-bold text-primary-600">${parseInt(order.total).toLocaleString('fr-FR')} F</span>
                            <a href="/dashboard/commandes/${order.id}" class="text-xs text-primary-600 hover:underline">Voir</a>
                        </div>
                    `;
                    return div;
                },

                getOrderCount(status) {
                    return (this.orders[status] || []).length;
                },

                applyFilters() {
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
                    }, 10000); // Every 10 seconds
                },

                stopAutoRefresh() {
                    if (this.refreshInterval) {
                        clearInterval(this.refreshInterval);
                        this.refreshInterval = null;
                    }
                }
            };
        }
    </script>
    @endpush
</x-layouts.admin-restaurant>
