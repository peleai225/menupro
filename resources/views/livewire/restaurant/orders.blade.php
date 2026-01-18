<div>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-neutral-900">Commandes</h1>
            <p class="text-neutral-500 mt-1">
                Gérez les commandes de vos clients.
                <span class="hidden md:inline">Cliquez sur une ligne de la liste pour voir le détail et changer le statut.</span>
            </p>
            <p class="text-xs text-neutral-400 md:hidden mt-1">
                Astuce : touchez une commande dans la liste pour ouvrir le détail et accéder aux boutons de statut.
            </p>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('restaurant.orders.board') }}" 
               target="_blank"
               class="btn-secondary px-4 py-2 flex items-center gap-2 hover:bg-neutral-100 active:scale-95 transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17V7m0 10a2 2 0 01-2 2H5a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2m0 10a2 2 0 002 2h2a2 2 0 002-2M9 7a2 2 0 012-2h2a2 2 0 012 2m0 10V7m0 10a2 2 0 002 2h2a2 2 0 002-2V7a2 2 0 00-2-2h-2a2 2 0 00-2 2"/>
                </svg>
                <span class="hidden sm:inline">Board Cuisine</span>
                <span class="sm:hidden">Board</span>
            </a>
        </div>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
        <div x-data="{ show: true }" 
             x-show="show"
             x-init="setTimeout(() => show = false, 5000)"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-2"
             class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-700 flex items-center justify-between gap-3 shadow-sm">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
            <button @click="show = false" class="text-emerald-600 hover:text-emerald-800">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div x-data="{ show: true }" 
             x-show="show"
             x-init="setTimeout(() => show = false, 5000)"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-2"
             class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 flex items-center justify-between gap-3 shadow-sm">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                <span class="font-medium">{{ session('error') }}</span>
            </div>
            <button @click="show = false" class="text-red-600 hover:text-red-800">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    @endif

    <!-- Status Tabs -->
    <div class="flex flex-wrap gap-2 mb-6">
        <button wire:click="$set('status', '')" 
                wire:loading.attr="disabled"
                class="px-4 py-2 rounded-xl font-medium transition-all active:scale-95 {{ !$status ? 'bg-primary-500 text-white shadow-md' : 'bg-neutral-100 text-neutral-700 hover:bg-neutral-200' }} disabled:opacity-50">
            Toutes ({{ $this->statusCounts['all'] }})
        </button>
        <button wire:click="$set('status', 'pending_payment')" 
                class="px-4 py-2 rounded-xl font-medium transition-colors {{ $status === 'pending_payment' ? 'bg-yellow-500 text-white' : 'bg-yellow-100 text-yellow-700 hover:bg-yellow-200' }}">
            En attente ({{ $this->statusCounts['pending'] }})
        </button>
        <button wire:click="$set('status', 'confirmed')" 
                class="px-4 py-2 rounded-xl font-medium transition-colors {{ $status === 'confirmed' ? 'bg-blue-500 text-white' : 'bg-blue-100 text-blue-700 hover:bg-blue-200' }}">
            Confirmées ({{ $this->statusCounts['confirmed'] }})
        </button>
        <button wire:click="$set('status', 'preparing')" 
                class="px-4 py-2 rounded-xl font-medium transition-colors {{ $status === 'preparing' ? 'bg-purple-500 text-white' : 'bg-purple-100 text-purple-700 hover:bg-purple-200' }}">
            En préparation ({{ $this->statusCounts['preparing'] }})
        </button>
        <button wire:click="$set('status', 'ready')" 
                class="px-4 py-2 rounded-xl font-medium transition-colors {{ $status === 'ready' ? 'bg-secondary-500 text-white' : 'bg-secondary-100 text-secondary-700 hover:bg-secondary-200' }}">
            Prêtes ({{ $this->statusCounts['ready'] }})
        </button>
    </div>

    <!-- Filters -->
    <div class="card p-4 mb-6">
        <div class="flex flex-col md:flex-row gap-4">
            <!-- Search -->
            <div class="flex-1">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" 
                           wire:model.live.debounce.300ms="search"
                           class="input pl-10"
                           placeholder="Rechercher par n°, nom ou téléphone...">
                </div>
            </div>

            <!-- Type Filter -->
            <div class="w-full md:w-40">
                <select wire:model.live="type" class="input">
                    <option value="">Tous les types</option>
                    <option value="dine_in">Sur place</option>
                    <option value="takeaway">À emporter</option>
                    <option value="delivery">Livraison</option>
                </select>
            </div>

            <!-- Date Filter -->
            <div class="w-full md:w-40">
                <select wire:model.live="date" class="input">
                    <option value="">Toutes dates</option>
                    <option value="today">Aujourd'hui</option>
                    <option value="yesterday">Hier</option>
                    <option value="week">Cette semaine</option>
                    <option value="month">Ce mois</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Orders Table -->
    @if($this->orders->isEmpty())
        <div class="card p-12 text-center">
            <div class="w-16 h-16 bg-neutral-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-neutral-900 mb-2">Aucune commande</h3>
            <p class="text-neutral-500">
                @if($search || $status || $type || $date)
                    Aucune commande ne correspond à vos critères.
                @else
                    Les commandes de vos clients apparaîtront ici.
                @endif
            </p>
        </div>
    @else
        <div class="card overflow-hidden">
            <div class="table-responsive">
                <table class="w-full min-w-[600px]">
                    <thead class="bg-neutral-50 border-b border-neutral-100">
                        <tr>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-500 uppercase tracking-wider">Commande</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-500 uppercase tracking-wider">Client</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-500 uppercase tracking-wider">Type</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-500 uppercase tracking-wider">Total</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-500 uppercase tracking-wider">Statut</th>
                            <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-500 uppercase tracking-wider">Date</th>
                            <th class="px-6 py-4"></th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100">
                        @foreach($this->orders as $order)
                            <tr class="hover:bg-neutral-50 transition-colors cursor-pointer group" 
                                wire:click="viewOrder({{ $order->id }})"
                                wire:loading.class="opacity-50"
                                wire:target="viewOrder">
                                <td class="px-6 py-4">
                                    <span class="font-mono font-semibold text-neutral-900">#{{ $order->reference }}</span>
                                </td>
                                <td class="px-6 py-4">
                                    <div>
                                        <p class="font-medium text-neutral-900">{{ $order->customer_name }}</p>
                                        <p class="text-sm text-neutral-500">{{ $order->customer_phone }}</p>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    @php
                                        $typeColor = match($order->type) {
                                            \App\Enums\OrderType::DINE_IN => 'bg-blue-100 text-blue-700',
                                            \App\Enums\OrderType::TAKEAWAY => 'bg-purple-100 text-purple-700',
                                            \App\Enums\OrderType::DELIVERY => 'bg-orange-100 text-orange-700',
                                        };
                                    @endphp
                                    <span class="badge {{ $typeColor }}">
                                        {{ $order->type->label() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="font-bold text-neutral-900">{{ number_format($order->total, 0, ',', ' ') }} F</span>
                                </td>
                                <td class="px-6 py-4">
                                    <span class="badge" style="background-color: {{ $order->status->color() }}20; color: {{ $order->status->color() }}">
                                        {{ $order->status->label() }}
                                    </span>
                                </td>
                                <td class="px-6 py-4">
                                    <div>
                                        <p class="text-neutral-900">{{ $order->created_at->format('d/m/Y') }}</p>
                                        <p class="text-sm text-neutral-500">{{ $order->created_at->format('H:i') }}</p>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <button wire:click="viewOrder({{ $order->id }})" 
                                            wire:loading.attr="disabled"
                                            class="p-2 hover:bg-neutral-100 rounded-lg transition-colors group-hover:text-primary-500 disabled:opacity-50">
                                        <svg class="w-5 h-5 text-neutral-400 group-hover:text-primary-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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

    <!-- Order Detail Modal -->
    @if($selectedOrder)
        <div class="fixed inset-0 z-50 overflow-y-auto" 
             wire:key="order-modal-{{ $selectedOrder->id }}"
             x-data="{ show: true }"
             x-show="show"
             x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             x-init="document.body.classList.add('overflow-hidden')"
             @keydown.escape.window="$wire.closeOrderModal()"
             @click.away="if ($event.target.classList.contains('fixed')) { $wire.closeOrderModal(); }">
            <div class="flex min-h-screen items-center justify-center p-4">
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity" 
                     wire:click="closeOrderModal"
                     @click.self="$wire.closeOrderModal()"></div>

                <!-- Modal Content -->
                <div class="relative w-full max-w-2xl bg-white rounded-2xl shadow-2xl max-h-[90vh] overflow-y-auto"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95">
                    <!-- Header -->
                    <div class="sticky top-0 bg-white p-6 border-b border-neutral-100 z-10">
                        <div class="flex items-center justify-between">
                            <div>
                                <h2 class="text-xl font-bold text-neutral-900">Commande #{{ $selectedOrder->reference }}</h2>
                                <p class="text-neutral-500 mt-1">{{ $selectedOrder->created_at->locale('fr')->isoFormat('D MMMM YYYY [à] HH:mm') }}</p>
                            </div>
                            <div class="flex items-center gap-2">
                                <a href="{{ route('r.order.status', [$selectedOrder->restaurant->slug, $selectedOrder]) }}" 
                                   target="_blank"
                                   class="p-2 hover:bg-neutral-100 active:scale-95 rounded-xl transition-all text-neutral-600 hover:text-primary-600"
                                   title="Voir la page de suivi client">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                </a>
                                <a href="{{ route('restaurant.orders.print', $selectedOrder) }}"
                                   target="_blank"
                                   onclick="window.open(this.href, '_blank', 'width=400,height=600'); return false;"
                                   class="p-2 hover:bg-neutral-100 active:scale-95 rounded-xl transition-all text-neutral-600 hover:text-primary-600"
                                   title="Imprimer le ticket">
                                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                                    </svg>
                                </a>
                                <button wire:click="closeOrderModal" 
                                        class="p-2 hover:bg-neutral-100 active:scale-95 rounded-xl transition-all">
                                    <svg class="w-6 h-6 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Body -->
                    <div class="p-6 space-y-6">
                        <!-- Status & Type -->
                        <div class="flex flex-wrap gap-3">
                            <span class="badge text-lg" style="background-color: {{ $selectedOrder->status->color() }}20; color: {{ $selectedOrder->status->color() }}">
                                {{ $selectedOrder->status->label() }}
                            </span>
                            @php
                                $typeColor = match($selectedOrder->type) {
                                    \App\Enums\OrderType::DINE_IN => 'bg-blue-100 text-blue-700',
                                    \App\Enums\OrderType::TAKEAWAY => 'bg-purple-100 text-purple-700',
                                    \App\Enums\OrderType::DELIVERY => 'bg-orange-100 text-orange-700',
                                };
                            @endphp
                            <span class="badge text-lg {{ $typeColor }}">
                                {{ $selectedOrder->type->label() }}
                            </span>
                            @if($selectedOrder->is_paid)
                                <span class="badge text-lg bg-secondary-100 text-secondary-700">✓ Payé</span>
                            @else
                                <span class="badge text-lg bg-red-100 text-red-700">Non payé</span>
                            @endif
                        </div>

                        <!-- Customer Info -->
                        <div class="bg-neutral-50 rounded-xl p-4">
                            <h3 class="font-semibold text-neutral-900 mb-3">Client</h3>
                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <p class="text-sm text-neutral-500">Nom</p>
                                    <p class="font-medium text-neutral-900">{{ $selectedOrder->customer_name }}</p>
                                </div>
                                <div>
                                    <p class="text-sm text-neutral-500">Téléphone</p>
                                    <p class="font-medium text-neutral-900">{{ $selectedOrder->customer_phone }}</p>
                                </div>
                                @if($selectedOrder->customer_email)
                                    <div class="col-span-2">
                                        <p class="text-sm text-neutral-500">Email</p>
                                        <p class="font-medium text-neutral-900">{{ $selectedOrder->customer_email }}</p>
                                    </div>
                                @endif
                                @if($selectedOrder->delivery_address)
                                    <div class="col-span-2">
                                        <p class="text-sm text-neutral-500">Adresse de livraison</p>
                                        <p class="font-medium text-neutral-900">{{ $selectedOrder->delivery_address }}, {{ $selectedOrder->delivery_city }}</p>
                                    </div>
                                @endif
                                @if($selectedOrder->table_number)
                                    <div>
                                        <p class="text-sm text-neutral-500">Table</p>
                                        <p class="font-medium text-neutral-900">{{ $selectedOrder->table_number }}</p>
                                    </div>
                                @endif
                            </div>
                        </div>

                        <!-- Order Items -->
                        <div>
                            <h3 class="font-semibold text-neutral-900 mb-3">Articles ({{ $selectedOrder->items->count() }})</h3>
                            <div class="space-y-3">
                                @foreach($selectedOrder->items as $item)
                                    <div class="flex items-center justify-between p-3 bg-neutral-50 rounded-xl">
                                        <div class="flex items-center gap-3">
                                            <span class="w-8 h-8 bg-primary-100 rounded-lg flex items-center justify-center text-primary-600 font-bold">
                                                {{ $item->quantity }}
                                            </span>
                                            <div>
                                                <p class="font-medium text-neutral-900">{{ $item->dish_name }}</p>
                                                @if($item->selected_options)
                                                    <p class="text-sm text-neutral-500">
                                                        {{ collect($item->selected_options)->pluck('name')->join(', ') }}
                                                    </p>
                                                @endif
                                                @if($item->special_instructions)
                                                    <p class="text-sm text-accent-600 italic">{{ $item->special_instructions }}</p>
                                                @endif
                                            </div>
                                        </div>
                                        <span class="font-semibold text-neutral-900">{{ number_format($item->total_price, 0, ',', ' ') }} F</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Notes -->
                        @if($selectedOrder->customer_notes)
                            <div class="bg-yellow-50 rounded-xl p-4">
                                <h3 class="font-semibold text-yellow-800 mb-2">Note du client</h3>
                                <p class="text-yellow-700">{{ $selectedOrder->customer_notes }}</p>
                            </div>
                        @endif

                        <!-- Totals -->
                        <div class="border-t border-neutral-200 pt-4 space-y-2">
                            <div class="flex justify-between text-neutral-600">
                                <span>Sous-total</span>
                                <span>{{ number_format($selectedOrder->subtotal, 0, ',', ' ') }} F</span>
                            </div>
                            @if($selectedOrder->delivery_fee > 0)
                                <div class="flex justify-between text-neutral-600">
                                    <span>Frais de livraison</span>
                                    <span>{{ number_format($selectedOrder->delivery_fee, 0, ',', ' ') }} F</span>
                                </div>
                            @endif
                            @if($selectedOrder->discount_amount > 0)
                                <div class="flex justify-between text-secondary-600">
                                    <span>Réduction</span>
                                    <span>-{{ number_format($selectedOrder->discount_amount, 0, ',', ' ') }} F</span>
                                </div>
                            @endif
                            @if($selectedOrder->tax_amount > 0)
                                <div class="flex justify-between text-neutral-600">
                                    <span>{{ $selectedOrder->restaurant->tax_name ?? 'Taxe' }}</span>
                                    <span>{{ number_format($selectedOrder->tax_amount, 0, ',', ' ') }} F</span>
                                </div>
                            @endif
                            @if($selectedOrder->service_fee > 0)
                                <div class="flex justify-between text-neutral-600">
                                    <span>Frais de service</span>
                                    <span>{{ number_format($selectedOrder->service_fee, 0, ',', ' ') }} F</span>
                                </div>
                            @endif
                            <div class="flex justify-between text-lg font-bold text-neutral-900 pt-2 border-t border-neutral-200">
                                <span>Total</span>
                                <span>{{ number_format($selectedOrder->total, 0, ',', ' ') }} F</span>
                            </div>
                        </div>

                        <!-- Status Actions -->
                        @unless($selectedOrder->is_final)
                            <div class="border-t border-neutral-200 pt-4">
                                <h3 class="font-semibold text-neutral-900 mb-3">Mettre à jour le statut</h3>
                                <div class="flex flex-wrap gap-2">
                                    @if($selectedOrder->status === \App\Enums\OrderStatus::PENDING_PAYMENT)
                                        <button wire:click="updateStatus({{ $selectedOrder->id }}, 'confirmed')" 
                                                wire:loading.attr="disabled"
                                                class="btn btn-primary px-6 py-3 flex items-center gap-2 hover:bg-primary-600 active:scale-95 transition-all disabled:opacity-50 shadow-sm hover:shadow-md">
                                            <span wire:loading.remove>Confirmer la commande</span>
                                            <span wire:loading class="flex items-center gap-2">
                                                <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                Mise à jour...
                                            </span>
                                        </button>
                                    @elseif($selectedOrder->status === \App\Enums\OrderStatus::PAID)
                                        <button wire:click="updateStatus({{ $selectedOrder->id }}, 'confirmed')" 
                                                wire:loading.attr="disabled"
                                                class="btn btn-primary px-6 py-3 flex items-center gap-2 hover:bg-primary-600 active:scale-95 transition-all disabled:opacity-50 shadow-sm hover:shadow-md">
                                            <span wire:loading.remove>Confirmer la commande</span>
                                            <span wire:loading class="flex items-center gap-2">
                                                <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                Mise à jour...
                                            </span>
                                        </button>
                                    @elseif($selectedOrder->status === \App\Enums\OrderStatus::CONFIRMED)
                                        <button wire:click="updateStatus({{ $selectedOrder->id }}, 'preparing')" 
                                                wire:loading.attr="disabled"
                                                class="btn btn-primary px-6 py-3 flex items-center gap-2 hover:bg-primary-600 active:scale-95 transition-all disabled:opacity-50 shadow-sm hover:shadow-md">
                                            <span wire:loading.remove>Commencer la préparation</span>
                                            <span wire:loading class="flex items-center gap-2">
                                                <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                Mise à jour...
                                            </span>
                                        </button>
                                    @elseif($selectedOrder->status === \App\Enums\OrderStatus::PREPARING)
                                        <button wire:click="updateStatus({{ $selectedOrder->id }}, 'ready')" 
                                                wire:loading.attr="disabled"
                                                class="btn btn-primary px-6 py-3 flex items-center gap-2 hover:bg-primary-600 active:scale-95 transition-all disabled:opacity-50 shadow-sm hover:shadow-md">
                                            <span wire:loading.remove>Marquer prête</span>
                                            <span wire:loading class="flex items-center gap-2">
                                                <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                Mise à jour...
                                            </span>
                                        </button>
                                    @elseif($selectedOrder->status === \App\Enums\OrderStatus::READY)
                                        <button wire:click="updateStatus({{ $selectedOrder->id }}, 'completed')" 
                                                wire:loading.attr="disabled"
                                                class="btn btn-primary px-6 py-3 flex items-center gap-2 hover:bg-primary-600 active:scale-95 transition-all disabled:opacity-50 shadow-sm hover:shadow-md">
                                            <span wire:loading.remove>Commande récupérée / livrée</span>
                                            <span wire:loading class="flex items-center gap-2">
                                                <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                                </svg>
                                                Mise à jour...
                                            </span>
                                        </button>
                                    @endif
                                    
                                    <button wire:click="openCancelModal({{ $selectedOrder->id }})" 
                                            wire:loading.attr="disabled"
                                            class="btn btn-secondary px-6 py-3 flex items-center gap-2 text-red-600 hover:bg-red-50 active:scale-95 transition-all disabled:opacity-50 shadow-sm hover:shadow-md">
                                        Annuler la commande
                                    </button>
                                </div>
                            </div>
                        @endunless
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Cancel Order Modal -->
    @if($showCancelModal && $selectedOrder)
        <div class="fixed inset-0 z-50 overflow-y-auto" 
             x-data="{ show: true }"
             x-show="show"
             x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @keydown.escape.window="$wire.closeCancelModal()">
            <div class="flex min-h-screen items-center justify-center p-4">
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity" 
                     @click="$wire.closeCancelModal()"></div>

                <!-- Modal Content -->
                <div class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95">
                    <!-- Header -->
                    <div class="p-6 border-b border-neutral-100">
                        <div class="flex items-center justify-between">
                            <h3 class="text-lg font-bold text-neutral-900">Annuler la commande</h3>
                            <button wire:click="closeCancelModal" 
                                    class="p-2 hover:bg-neutral-100 active:scale-95 rounded-xl transition-all">
                                <svg class="w-5 h-5 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Body -->
                    <div class="p-6 space-y-4">
                        <p class="text-neutral-600">
                            Êtes-vous sûr de vouloir annuler la commande <strong>#{{ $selectedOrder->reference }}</strong> ?
                        </p>
                        
                        <div>
                            <label for="cancellation_reason" class="block text-sm font-medium text-neutral-700 mb-2">
                                Motif d'annulation <span class="text-neutral-400">(optionnel)</span>
                            </label>
                            <textarea 
                                wire:model="cancellationReason"
                                id="cancellation_reason"
                                rows="3"
                                class="w-full px-4 py-2 border border-neutral-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                placeholder="Ex: Client a annulé, stock épuisé, etc."
                            ></textarea>
                        </div>
                    </div>

                    <!-- Footer -->
                    <div class="p-6 border-t border-neutral-100 flex items-center justify-end gap-3">
                        <button wire:click="closeCancelModal" 
                                class="btn btn-secondary px-6 py-3 flex items-center gap-2 shadow-sm hover:shadow-md transition-all">
                            Annuler
                        </button>
                        <button wire:click="cancelOrder({{ $selectedOrder->id }})" 
                                wire:loading.attr="disabled"
                                class="btn btn-primary px-6 py-3 flex items-center gap-2 bg-red-600 hover:bg-red-700 text-white shadow-sm hover:shadow-md transition-all">
                            <span wire:loading.remove>Confirmer l'annulation</span>
                            <span wire:loading class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Annulation...
                            </span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    @endif
</div>

