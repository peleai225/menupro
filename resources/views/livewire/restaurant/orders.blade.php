<div wire:poll.5s x-data="{ loaded: false }" x-init="setTimeout(() => loaded = true, 100)">
    <!-- Header with gradient background -->
    <div x-show="loaded" x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform -translate-y-4"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl lg:text-3xl font-bold text-neutral-900 flex items-center gap-3">
                Commandes
                @if($this->statusCounts['pending'] > 0)
                    <span class="relative flex h-6 w-6">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-accent-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-6 w-6 bg-accent-500 text-white text-xs items-center justify-center font-bold">
                            {{ $this->statusCounts['pending'] }}
                        </span>
                    </span>
                @endif
            </h1>
            <p class="text-neutral-500 mt-1">
                Gérez les commandes de vos clients en temps réel.
            </p>
        </div>
        <div class="flex flex-wrap items-center gap-2">
            <a href="{{ route('restaurant.orders.rush') }}" 
               class="btn btn-primary btn-glow px-4 py-2.5 flex items-center gap-2 group">
                <span class="px-2 py-0.5 bg-white/20 text-white rounded text-xs font-bold">RUSH</span>
                <span class="hidden sm:inline">Mode Rush</span>
                <svg class="w-4 h-4 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                </svg>
            </a>
            <a href="{{ route('restaurant.orders.kanban') }}" 
               class="btn btn-secondary px-4 py-2.5 flex items-center gap-2 hover:shadow-md transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                </svg>
                <span class="hidden sm:inline">Kanban</span>
            </a>
            <a href="{{ route('restaurant.orders.board') }}" 
               target="_blank"
               class="btn btn-ghost px-4 py-2.5 flex items-center gap-2 hover:shadow-md transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                </svg>
                <span class="hidden sm:inline">Board</span>
            </a>
        </div>
    </div>

    <!-- Flash Messages with modern design -->
    @if(session('success'))
        <div x-data="{ show: true }" 
             x-show="show"
             x-init="setTimeout(() => show = false, 5000)"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform -translate-y-2"
             x-transition:enter-end="opacity-100 transform translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             class="mb-6 p-4 bg-gradient-to-r from-secondary-50 to-secondary-100 border border-secondary-200 rounded-2xl shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-secondary-500 rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <span class="font-medium text-secondary-800 flex-1">{{ session('success') }}</span>
                <button @click="show = false" class="p-1.5 hover:bg-secondary-200 rounded-lg transition-colors">
                    <svg class="w-5 h-5 text-secondary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div x-data="{ show: true }" 
             x-show="show"
             x-init="setTimeout(() => show = false, 5000)"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform -translate-y-2"
             x-transition:enter-end="opacity-100 transform translate-y-0"
             class="mb-6 p-4 bg-gradient-to-r from-red-50 to-red-100 border border-red-200 rounded-2xl shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-red-500 rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>
                <span class="font-medium text-red-800 flex-1">{{ session('error') }}</span>
                <button @click="show = false" class="p-1.5 hover:bg-red-200 rounded-lg transition-colors">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    @endif

    <!-- Status Tabs with modern pills design -->
    <div x-show="loaded" x-transition:enter="transition ease-out duration-300 delay-100"
         x-transition:enter-start="opacity-0 transform translate-y-4"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         class="bg-white rounded-2xl p-2 shadow-sm border border-neutral-100 mb-6">
        <div class="flex flex-wrap gap-2">
            <button wire:click="$set('status', '')" 
                    wire:loading.attr="disabled"
                    class="relative px-5 py-2.5 rounded-xl font-medium transition-all duration-200 {{ !$status ? 'bg-primary-500 text-white shadow-lg shadow-primary-500/30' : 'text-neutral-600 hover:bg-neutral-100' }}">
                <span class="relative z-10">Toutes</span>
                <span class="ml-1.5 px-2 py-0.5 rounded-full text-xs font-bold {{ !$status ? 'bg-white/20' : 'bg-neutral-200' }}">
                    {{ $this->statusCounts['all'] }}
                </span>
            </button>
            <button wire:click="$set('status', 'pending_payment')" 
                    class="relative px-5 py-2.5 rounded-xl font-medium transition-all duration-200 {{ $status === 'pending_payment' ? 'bg-yellow-500 text-white shadow-lg shadow-yellow-500/30' : 'text-neutral-600 hover:bg-yellow-50' }}">
                @if($this->statusCounts['pending'] > 0)
                    <span class="absolute -top-1 -right-1 w-2 h-2 bg-yellow-500 rounded-full animate-ping"></span>
                    <span class="absolute -top-1 -right-1 w-2 h-2 bg-yellow-500 rounded-full"></span>
                @endif
                <span>En attente</span>
                <span class="ml-1.5 px-2 py-0.5 rounded-full text-xs font-bold {{ $status === 'pending_payment' ? 'bg-white/20' : 'bg-yellow-100 text-yellow-700' }}">
                    {{ $this->statusCounts['pending'] }}
                </span>
            </button>
            <button wire:click="$set('status', 'confirmed')" 
                    class="px-5 py-2.5 rounded-xl font-medium transition-all duration-200 {{ $status === 'confirmed' ? 'bg-blue-500 text-white shadow-lg shadow-blue-500/30' : 'text-neutral-600 hover:bg-blue-50' }}">
                <span>Confirmées</span>
                <span class="ml-1.5 px-2 py-0.5 rounded-full text-xs font-bold {{ $status === 'confirmed' ? 'bg-white/20' : 'bg-blue-100 text-blue-700' }}">
                    {{ $this->statusCounts['confirmed'] }}
                </span>
            </button>
            <button wire:click="$set('status', 'preparing')" 
                    class="px-5 py-2.5 rounded-xl font-medium transition-all duration-200 {{ $status === 'preparing' ? 'bg-indigo-500 text-white shadow-lg shadow-indigo-500/30' : 'text-neutral-600 hover:bg-indigo-50' }}">
                <span>En préparation</span>
                <span class="ml-1.5 px-2 py-0.5 rounded-full text-xs font-bold {{ $status === 'preparing' ? 'bg-white/20' : 'bg-indigo-100 text-indigo-700' }}">
                    {{ $this->statusCounts['preparing'] }}
                </span>
            </button>
            <button wire:click="$set('status', 'ready')" 
                    class="px-5 py-2.5 rounded-xl font-medium transition-all duration-200 {{ $status === 'ready' ? 'bg-secondary-500 text-white shadow-lg shadow-secondary-500/30' : 'text-neutral-600 hover:bg-secondary-50' }}">
                <span>Prêtes</span>
                <span class="ml-1.5 px-2 py-0.5 rounded-full text-xs font-bold {{ $status === 'ready' ? 'bg-white/20' : 'bg-secondary-100 text-secondary-700' }}">
                    {{ $this->statusCounts['ready'] }}
                </span>
            </button>
        </div>
    </div>

    <!-- Filters with enhanced design -->
    <div x-show="loaded" x-transition:enter="transition ease-out duration-300 delay-200"
         x-transition:enter-start="opacity-0 transform translate-y-4"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         class="card p-4 mb-6">
        <div class="flex flex-col lg:flex-row gap-4">
            <!-- Search -->
            <div class="flex-1">
                <div class="relative group">
                    <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-neutral-400 group-focus-within:text-primary-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" 
                           wire:model.live.debounce.300ms="search"
                           class="input pl-12 h-12"
                           placeholder="Rechercher par n°, nom ou téléphone...">
                    <div wire:loading wire:target="search" class="absolute right-4 top-1/2 -translate-y-1/2">
                        <svg class="animate-spin h-5 w-5 text-primary-500" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Type Filter -->
            <div class="w-full lg:w-44">
                <select wire:model.live="type" class="input h-12">
                    <option value="">Tous les types</option>
                    <option value="dine_in">Sur place</option>
                    <option value="takeaway">À emporter</option>
                    <option value="delivery">Livraison</option>
                </select>
            </div>

            <!-- Date Filter -->
            <div class="w-full lg:w-44">
                <select wire:model.live="date" class="input h-12">
                    <option value="">Toutes dates</option>
                    <option value="today">Aujourd'hui</option>
                    <option value="yesterday">Hier</option>
                    <option value="week">Cette semaine</option>
                    <option value="month">Ce mois</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Orders List/Table -->
    @if($this->orders->isEmpty())
        <div x-show="loaded" x-transition:enter="transition ease-out duration-500 delay-300"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100"
             class="card">
            <div class="empty-state py-16">
                <div class="w-20 h-20 mx-auto bg-gradient-to-br from-neutral-100 to-neutral-200 rounded-full flex items-center justify-center mb-6">
                    <svg class="w-10 h-10 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <h3 class="empty-state-title">Aucune commande</h3>
                <p class="empty-state-description">
                    @if($search || $status || $type || $date)
                        Aucune commande ne correspond à vos critères de recherche.
                        <button wire:click="$set('search', '')" class="text-primary-600 hover:underline">Réinitialiser les filtres</button>
                    @else
                        Les commandes de vos clients apparaîtront ici dès qu'elles seront passées.
                    @endif
                </p>
            </div>
        </div>
    @else
        <div x-show="loaded" x-transition:enter="transition ease-out duration-300 delay-300"
             x-transition:enter-start="opacity-0 transform translate-y-4"
             x-transition:enter-end="opacity-100 transform translate-y-0"
             class="card overflow-hidden">
            <div class="table-responsive">
                <table class="w-full min-w-[700px]">
                    <thead class="bg-gradient-to-r from-neutral-50 to-neutral-100 border-b border-neutral-200">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-bold text-neutral-500 uppercase tracking-wider">Commande</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-neutral-500 uppercase tracking-wider">Client</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-neutral-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-neutral-500 uppercase tracking-wider">Total</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-neutral-500 uppercase tracking-wider">Statut</th>
                            <th class="px-6 py-4 text-left text-xs font-bold text-neutral-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-4 w-16"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100">
                        @foreach($this->orders as $index => $order)
                            <tr class="table-row-interactive cursor-pointer group" 
                                wire:click="viewOrder({{ $order->id }})"
                                wire:loading.class="opacity-50"
                                wire:target="viewOrder"
                                style="animation: fade-slide-up 0.3s ease-out {{ $index * 50 }}ms both;">
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 rounded-xl {{ $order->status->value === 'pending_payment' || $order->status->value === 'pending' ? 'bg-yellow-100 text-yellow-600' : 'bg-primary-100 text-primary-600' }} flex items-center justify-center font-mono font-bold text-sm">
                                            {{ substr($order->reference, -3) }}
                                        </div>
                                        <span class="font-mono font-semibold text-neutral-900">#{{ $order->reference }}</span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div class="avatar avatar-sm bg-gradient-to-br from-neutral-300 to-neutral-400 text-white">
                                            {{ strtoupper(substr($order->customer_name ?? 'C', 0, 1)) }}
                                        </div>
                                        <div>
                                            <p class="font-medium text-neutral-900">{{ $order->customer_name }}</p>
                                            <p class="text-sm text-neutral-500">{{ $order->customer_phone }}</p>
                                        </div>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $typeConfig = match($order->type) {
                                            \App\Enums\OrderType::DINE_IN => ['bg' => 'bg-blue-100', 'text' => 'text-blue-700'],
                                            \App\Enums\OrderType::TAKEAWAY => ['bg' => 'bg-purple-100', 'text' => 'text-purple-700'],
                                            \App\Enums\OrderType::DELIVERY => ['bg' => 'bg-orange-100', 'text' => 'text-orange-700'],
                                        };
                                    @endphp
                                    <div class="flex flex-col gap-1">
                                        <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-xl text-sm font-medium {{ $typeConfig['bg'] }} {{ $typeConfig['text'] }}">
                                            @if($order->type === \App\Enums\OrderType::DINE_IN)
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h18v18H3zM12 8v8m-4-4h8"/></svg>
                                            @elseif($order->type === \App\Enums\OrderType::TAKEAWAY)
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                                            @else
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                                            @endif
                                            {{ $order->type->label() }}
                                        </span>
                                        @if($order->type === \App\Enums\OrderType::DINE_IN && $order->table_number)
                                            <span class="inline-flex items-center gap-1 px-2 py-1 rounded-lg text-xs font-bold bg-amber-100 text-amber-700">
                                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5z"/>
                                                </svg>
                                                Table {{ $order->table_number }}
                                            </span>
                                        @endif
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="font-bold text-neutral-900 tabular-nums">{{ number_format($order->total, 0, ',', ' ') }} F</span>
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $statusClasses = [
                                            'pending_payment' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                                            'pending' => 'bg-yellow-100 text-yellow-700 border-yellow-200',
                                            'paid' => 'bg-green-100 text-green-700 border-green-200',
                                            'confirmed' => 'bg-blue-100 text-blue-700 border-blue-200',
                                            'preparing' => 'bg-indigo-100 text-indigo-700 border-indigo-200',
                                            'ready' => 'bg-primary-100 text-primary-700 border-primary-200',
                                            'delivering' => 'bg-purple-100 text-purple-700 border-purple-200',
                                            'completed' => 'bg-secondary-100 text-secondary-700 border-secondary-200',
                                            'cancelled' => 'bg-red-100 text-red-700 border-red-200',
                                        ];
                                        $statusClass = $statusClasses[$order->status->value] ?? 'bg-neutral-100 text-neutral-700';
                                        $isPending = in_array($order->status->value, ['pending', 'pending_payment', 'preparing']);
                                    @endphp
                                    <span class="inline-flex items-center gap-2 px-3 py-1.5 rounded-xl text-sm font-medium border {{ $statusClass }}">
                                        @if($isPending)
                                            <span class="w-2 h-2 rounded-full bg-current animate-pulse"></span>
                                        @endif
                                        {{ $order->status->label() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div>
                                        <p class="font-medium text-neutral-900">{{ $order->created_at->format('d/m/Y') }}</p>
                                        <p class="text-sm text-neutral-500">{{ $order->created_at->format('H:i') }}</p>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <button class="p-2 rounded-xl hover:bg-primary-50 text-neutral-400 group-hover:text-primary-500 transition-all">
                                        <svg class="w-5 h-5 transition-transform group-hover:translate-x-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                        </svg>
                                    </button>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $this->orders->links() }}
        </div>
    @endif

    <!-- Order Detail Modal - Modern Design -->
    @if($selectedOrder)
        <div class="fixed inset-0 z-50" 
             wire:key="order-modal-{{ $selectedOrder->id }}"
             x-data="{ show: true }"
             x-show="show"
             x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-init="document.body.classList.add('overflow-hidden')"
             x-on:remove="document.body.classList.remove('overflow-hidden')"
             @keydown.escape.window="document.body.classList.remove('overflow-hidden'); $wire.closeOrderModal()">
            
            <!-- Backdrop -->
            <div class="fixed inset-0" style="background: rgba(0,0,0,0.6); backdrop-filter: blur(4px);" 
                 @click="document.body.classList.remove('overflow-hidden'); $wire.closeOrderModal()"></div>

            <!-- Modal Container -->
            <div class="fixed inset-0 flex items-center justify-center p-2 sm:p-4">
                <!-- Modal Content -->
                <div class="relative w-full max-w-2xl bg-white rounded-2xl shadow-2xl flex flex-col overflow-hidden"
                     style="max-height: calc(100vh - 2rem);"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                     x-transition:enter-end="opacity-100 scale-100 translate-y-0">
                    
                    <!-- Header -->
                    <div class="flex-shrink-0 px-4 sm:px-6 py-4 border-b border-gray-200" style="background: #f8fafc;">
                        <div class="flex items-center justify-between gap-4">
                            <!-- Title & Info -->
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2 flex-wrap">
                                    <h2 class="text-xl sm:text-2xl font-bold" style="color: #1f2937;">
                                        #{{ $selectedOrder->reference }}
                                    </h2>
                                    @if($selectedOrder->is_paid)
                                        <span class="px-2.5 py-1 rounded-full text-xs font-bold" style="background: #22c55e; color: white;">
                                            PAYÉ
                                        </span>
                                    @endif
                                    <span class="px-2.5 py-1 rounded-full text-xs font-semibold" style="background-color: {{ $selectedOrder->status->color() }}20; color: {{ $selectedOrder->status->color() }};">
                                        {{ $selectedOrder->status->label() }}
                                    </span>
                                </div>
                                <p class="text-sm mt-1" style="color: #6b7280;">
                                    {{ $selectedOrder->created_at->locale('fr')->isoFormat('ddd D MMM YYYY [à] HH:mm') }}
                                </p>
                            </div>
                            
                            <!-- Actions -->
                            <div class="flex items-center gap-1 sm:gap-2 flex-shrink-0">
                                <a href="{{ route('r.order.status', [$selectedOrder->restaurant->slug, $selectedOrder->tracking_token]) }}" 
                                   target="_blank"
                                   class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors"
                                   style="background: #e5e7eb; color: #374151;"
                                   title="Voir suivi">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                    <span class="hidden sm:inline">Voir</span>
                                </a>
                                <a href="{{ route('restaurant.orders.print', $selectedOrder) }}"
                                   target="_blank"
                                   onclick="window.open(this.href, '_blank', 'width=400,height=600'); return false;"
                                   class="inline-flex items-center gap-1.5 px-3 py-2 rounded-lg text-sm font-medium transition-colors"
                                   style="background: #e5e7eb; color: #374151;"
                                   title="Imprimer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                    </svg>
                                    <span class="hidden sm:inline">Imprimer</span>
                                </a>
                                <button @click="document.body.classList.remove('overflow-hidden'); $wire.closeOrderModal()" 
                                        class="p-2 rounded-lg transition-colors"
                                        style="background: #fee2e2; color: #dc2626;"
                                        title="Fermer">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Body - Scrollable -->
                    <div class="flex-1 overflow-y-auto px-4 sm:px-6 py-4 space-y-4" style="background: #ffffff;">
                        
                        <!-- Type Badge & Table -->
                        <div class="flex flex-wrap items-center gap-3">
                            @php
                                $typeConfig = match($selectedOrder->type) {
                                    \App\Enums\OrderType::DINE_IN => ['bg' => '#dbeafe', 'text' => '#1d4ed8', 'icon' => 'M3 3h18v18H3zM12 8v8m-4-4h8'],
                                    \App\Enums\OrderType::TAKEAWAY => ['bg' => '#f3e8ff', 'text' => '#7c3aed', 'icon' => 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z'],
                                    \App\Enums\OrderType::DELIVERY => ['bg' => '#ffedd5', 'text' => '#c2410c', 'icon' => 'M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0'],
                                };
                            @endphp
                            <span class="inline-flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-semibold" 
                                  style="background: {{ $typeConfig['bg'] }}; color: {{ $typeConfig['text'] }};">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $typeConfig['icon'] }}"/>
                                </svg>
                                {{ $selectedOrder->type->label() }}
                            </span>
                            @if($selectedOrder->table_number)
                                <span class="inline-flex items-center gap-2 px-3 py-2 rounded-lg text-sm font-bold" 
                                      style="background: #fef3c7; color: #b45309;">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 5a1 1 0 011-1h14a1 1 0 011 1v2a1 1 0 01-1 1H5a1 1 0 01-1-1V5zM4 13a1 1 0 011-1h6a1 1 0 011 1v6a1 1 0 01-1 1H5a1 1 0 01-1-1v-6z"/>
                                    </svg>
                                    Table {{ $selectedOrder->table_number }}
                                </span>
                            @endif
                        </div>

                        <!-- Customer Card -->
                        <div class="rounded-xl p-4" style="background: #f9fafb; border: 1px solid #e5e7eb;">
                            <div class="flex items-center gap-3">
                                <div class="w-11 h-11 rounded-full flex items-center justify-center text-white font-bold text-lg flex-shrink-0" 
                                     style="background: linear-gradient(135deg, #f97316, #ea580c);">
                                    {{ strtoupper(substr($selectedOrder->customer_name ?? 'C', 0, 1)) }}
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold truncate" style="color: #1f2937;">{{ $selectedOrder->customer_name }}</p>
                                    <p class="text-sm" style="color: #6b7280;">{{ $selectedOrder->customer_phone }}</p>
                                </div>
                            </div>
                            @if($selectedOrder->customer_email || $selectedOrder->delivery_address)
                                <div class="mt-3 pt-3 space-y-2 text-sm" style="border-top: 1px solid #e5e7eb;">
                                    @if($selectedOrder->customer_email)
                                        <p style="color: #6b7280;">
                                            <span class="font-medium" style="color: #374151;">Email:</span> {{ $selectedOrder->customer_email }}
                                        </p>
                                    @endif
                                    @if($selectedOrder->delivery_address)
                                        <p style="color: #6b7280;">
                                            <span class="font-medium" style="color: #374151;">Adresse:</span> {{ $selectedOrder->delivery_address }}, {{ $selectedOrder->delivery_city }}
                                        </p>
                                    @endif
                                </div>
                            @endif
                        </div>

                        <!-- Order Items -->
                        <div>
                            <h3 class="font-semibold mb-3 flex items-center gap-2" style="color: #374151;">
                                <svg class="w-5 h-5" style="color: #f97316;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                Articles ({{ $selectedOrder->items->count() }})
                            </h3>
                            <div class="space-y-3">
                                @foreach($selectedOrder->items as $item)
                                    <div class="flex items-start gap-3 p-3 rounded-xl" style="background: #ffffff; border: 1px solid #e5e7eb;">
                                        <!-- Image du plat -->
                                        <div class="relative flex-shrink-0">
                                            @if($item->dish && $item->dish->image_url)
                                                <img src="{{ $item->dish->image_url }}" 
                                                     alt="{{ $item->dish_name }}"
                                                     class="w-16 h-16 sm:w-20 sm:h-20 rounded-lg object-cover"
                                                     style="border: 2px solid #f3f4f6;">
                                            @else
                                                <div class="w-16 h-16 sm:w-20 sm:h-20 rounded-lg flex items-center justify-center" 
                                                     style="background: linear-gradient(135deg, #f3f4f6, #e5e7eb);">
                                                    <svg class="w-8 h-8" style="color: #9ca3af;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                                    </svg>
                                                </div>
                                            @endif
                                            <!-- Badge quantité -->
                                            <span class="absolute -top-2 -right-2 w-6 h-6 rounded-full flex items-center justify-center text-white font-bold text-xs shadow-md" 
                                                  style="background: linear-gradient(135deg, #f97316, #ea580c);">
                                                {{ $item->quantity }}
                                            </span>
                                        </div>
                                        
                                        <!-- Détails du plat -->
                                        <div class="flex-1 min-w-0">
                                            <p class="font-semibold" style="color: #1f2937;">{{ $item->dish_name }}</p>
                                            <p class="text-sm mt-0.5" style="color: #6b7280;">
                                                {{ number_format($item->unit_price, 0, ',', ' ') }} F x {{ $item->quantity }}
                                            </p>
                                            @if($item->selected_options)
                                                <div class="flex flex-wrap gap-1 mt-2">
                                                    @foreach(collect($item->selected_options)->pluck('name') as $option)
                                                        <span class="inline-block px-2 py-0.5 rounded text-xs" 
                                                              style="background: #fef3c7; color: #92400e;">
                                                            {{ $option }}
                                                        </span>
                                                    @endforeach
                                                </div>
                                            @endif
                                            @if($item->special_instructions)
                                                <p class="text-xs mt-2 italic flex items-start gap-1" style="color: #f97316;">
                                                    <svg class="w-3.5 h-3.5 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                                                    </svg>
                                                    {{ $item->special_instructions }}
                                                </p>
                                            @endif
                                        </div>
                                        
                                        <!-- Prix total -->
                                        <div class="text-right flex-shrink-0">
                                            <span class="font-bold tabular-nums" style="color: #1f2937;">
                                                {{ number_format($item->total_price, 0, ',', ' ') }} F
                                            </span>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Notes -->
                        @if($selectedOrder->customer_notes)
                            <div class="rounded-xl p-4" style="background: #fefce8; border: 1px solid #fde047;">
                                <div class="flex items-start gap-3">
                                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" style="color: #ca8a04;" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 8h10M7 12h4m1 8l-4-4H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-3l-4 4z"/>
                                    </svg>
                                    <div>
                                        <h4 class="font-semibold text-sm" style="color: #a16207;">Note du client</h4>
                                        <p class="text-sm mt-1" style="color: #854d0e;">{{ $selectedOrder->customer_notes }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>

                    <!-- Footer - Totals & Actions -->
                    <div class="flex-shrink-0 border-t" style="border-color: #e5e7eb;">
                        <!-- Totals -->
                        <div class="px-4 sm:px-6 py-4" style="background: #1f2937;">
                            <div class="space-y-2 text-sm">
                                <div class="flex justify-between" style="color: #9ca3af;">
                                    <span>Sous-total</span>
                                    <span class="tabular-nums">{{ number_format($selectedOrder->subtotal, 0, ',', ' ') }} F</span>
                                </div>
                                @if($selectedOrder->delivery_fee > 0)
                                    <div class="flex justify-between" style="color: #9ca3af;">
                                        <span>Frais de livraison</span>
                                        <span class="tabular-nums">{{ number_format($selectedOrder->delivery_fee, 0, ',', ' ') }} F</span>
                                    </div>
                                @endif
                                @if($selectedOrder->discount_amount > 0)
                                    <div class="flex justify-between" style="color: #22c55e;">
                                        <span>Réduction</span>
                                        <span class="tabular-nums">-{{ number_format($selectedOrder->discount_amount, 0, ',', ' ') }} F</span>
                                    </div>
                                @endif
                                @if($selectedOrder->tax_amount > 0)
                                    <div class="flex justify-between" style="color: #9ca3af;">
                                        <span>{{ $selectedOrder->restaurant->tax_name ?? 'Taxe' }}</span>
                                        <span class="tabular-nums">{{ number_format($selectedOrder->tax_amount, 0, ',', ' ') }} F</span>
                                    </div>
                                @endif
                                @if($selectedOrder->service_fee > 0)
                                    <div class="flex justify-between" style="color: #9ca3af;">
                                        <span>Frais de service</span>
                                        <span class="tabular-nums">{{ number_format($selectedOrder->service_fee, 0, ',', ' ') }} F</span>
                                    </div>
                                @endif
                                <div class="flex justify-between text-lg font-bold pt-2" style="border-top: 1px solid #374151;">
                                    <span style="color: #ffffff;">Total</span>
                                    <span class="tabular-nums" style="color: #f97316;">{{ number_format($selectedOrder->total, 0, ',', ' ') }} F</span>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        @unless($selectedOrder->is_final)
                            <div class="px-4 sm:px-6 py-4 flex flex-wrap gap-2" style="background: #f9fafb;">
                                @if($selectedOrder->status === \App\Enums\OrderStatus::PENDING_PAYMENT || $selectedOrder->status === \App\Enums\OrderStatus::PAID)
                                    <button wire:click="updateStatus({{ $selectedOrder->id }}, 'confirmed')" 
                                            wire:loading.attr="disabled"
                                            class="flex-1 min-w-[140px] inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg font-semibold text-white transition-all"
                                            style="background: linear-gradient(135deg, #f97316, #ea580c);">
                                        <svg wire:loading.remove wire:target="updateStatus" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        <svg wire:loading wire:target="updateStatus" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                        </svg>
                                        <span wire:loading.remove wire:target="updateStatus">Confirmer</span>
                                        <span wire:loading wire:target="updateStatus">...</span>
                                    </button>
                                @elseif($selectedOrder->status === \App\Enums\OrderStatus::CONFIRMED)
                                    <button wire:click="updateStatus({{ $selectedOrder->id }}, 'preparing')" 
                                            wire:loading.attr="disabled"
                                            class="flex-1 min-w-[140px] inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg font-semibold text-white transition-all"
                                            style="background: linear-gradient(135deg, #f97316, #ea580c);">
                                        <svg wire:loading.remove wire:target="updateStatus" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3"/>
                                        </svg>
                                        <svg wire:loading wire:target="updateStatus" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                        </svg>
                                        <span wire:loading.remove wire:target="updateStatus">Préparer</span>
                                        <span wire:loading wire:target="updateStatus">...</span>
                                    </button>
                                @elseif($selectedOrder->status === \App\Enums\OrderStatus::PREPARING)
                                    <button wire:click="updateStatus({{ $selectedOrder->id }}, 'ready')" 
                                            wire:loading.attr="disabled"
                                            class="flex-1 min-w-[140px] inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg font-semibold text-white transition-all"
                                            style="background: linear-gradient(135deg, #22c55e, #16a34a);">
                                        <svg wire:loading.remove wire:target="updateStatus" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        <svg wire:loading wire:target="updateStatus" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                        </svg>
                                        <span wire:loading.remove wire:target="updateStatus">Prête</span>
                                        <span wire:loading wire:target="updateStatus">...</span>
                                    </button>
                                @elseif($selectedOrder->status === \App\Enums\OrderStatus::READY)
                                    <button wire:click="updateStatus({{ $selectedOrder->id }}, 'completed')" 
                                            wire:loading.attr="disabled"
                                            class="flex-1 min-w-[140px] inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg font-semibold text-white transition-all"
                                            style="background: linear-gradient(135deg, #22c55e, #16a34a);">
                                        <svg wire:loading.remove wire:target="updateStatus" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                        <svg wire:loading wire:target="updateStatus" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                        </svg>
                                        <span wire:loading.remove wire:target="updateStatus">Terminée</span>
                                        <span wire:loading wire:target="updateStatus">...</span>
                                    </button>
                                @endif
                                
                                <button wire:click="openCancelModal({{ $selectedOrder->id }})" 
                                        class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-lg font-semibold transition-all"
                                        style="background: #fee2e2; color: #dc2626;">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                    Annuler
                                </button>
                            </div>
                        @endunless
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Cancel Order Modal -->
    @if($showCancelModal && $selectedOrder)
        <div class="fixed inset-0 z-[60] overflow-y-auto" 
             x-data="{ show: true }"
             x-show="show"
             x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             @keydown.escape.window="$wire.closeCancelModal()">
            <div class="flex min-h-screen items-center justify-center p-4">
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" 
                     @click="$wire.closeCancelModal()"></div>

                <!-- Modal Content -->
                <div class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100">
                    
                    <div class="p-6">
                        <div class="w-16 h-16 mx-auto bg-red-100 rounded-full flex items-center justify-center mb-4">
                            <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </div>
                        
                        <h3 class="text-xl font-bold text-neutral-900 text-center mb-2">Annuler la commande</h3>
                        <p class="text-neutral-600 text-center mb-6">
                            Êtes-vous sûr de vouloir annuler la commande <strong class="text-neutral-900">#{{ $selectedOrder->reference }}</strong> ?
                        </p>
                        
                        <div class="mb-6">
                            <label for="cancellation_reason" class="block text-sm font-medium text-neutral-700 mb-2">
                                Motif d'annulation <span class="text-neutral-400">(optionnel)</span>
                            </label>
                            <textarea 
                                wire:model="cancellationReason"
                                id="cancellation_reason"
                                rows="3"
                                class="input"
                                placeholder="Ex: Client a annulé, stock épuisé, etc."
                            ></textarea>
                        </div>

                        <div class="flex gap-3">
                            <button wire:click="closeCancelModal" 
                                    class="btn btn-secondary flex-1 py-3">
                                Retour
                            </button>
                            <button wire:click="cancelOrder({{ $selectedOrder->id }})" 
                                    wire:loading.attr="disabled"
                                    class="btn flex-1 py-3 bg-red-600 hover:bg-red-700 text-white">
                                <svg wire:loading.remove class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                                <svg wire:loading class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                <span wire:loading.remove>Confirmer</span>
                                <span wire:loading>Annulation...</span>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>
