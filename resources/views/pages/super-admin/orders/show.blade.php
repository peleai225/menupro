<x-layouts.admin-super title="Commande {{ $order->reference }}">
    @php
        $statusColors = [
            'draft'           => 'bg-neutral-100 text-neutral-600 border-neutral-200',
            'pending_payment' => 'bg-amber-50 text-amber-700 border-amber-200',
            'paid'            => 'bg-blue-50 text-blue-700 border-blue-200',
            'confirmed'       => 'bg-indigo-50 text-indigo-700 border-indigo-200',
            'preparing'       => 'bg-violet-50 text-violet-700 border-violet-200',
            'ready'           => 'bg-cyan-50 text-cyan-700 border-cyan-200',
            'delivering'      => 'bg-orange-50 text-orange-700 border-orange-200',
            'completed'       => 'bg-emerald-50 text-emerald-700 border-emerald-200',
            'cancelled'       => 'bg-red-50 text-red-700 border-red-200',
            'refunded'        => 'bg-neutral-100 text-neutral-600 border-neutral-200',
        ];
    @endphp

    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div class="flex items-center gap-4">
            <a href="{{ route('super-admin.orders.index') }}"
               class="inline-flex items-center gap-1.5 text-sm text-neutral-500 hover:text-neutral-700 transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour aux commandes
            </a>
        </div>
        <div class="flex items-center gap-3">
            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium border {{ $statusColors[$order->status->value] ?? 'bg-neutral-100 text-neutral-600 border-neutral-200' }}">
                {{ $order->status->label() }}
            </span>
        </div>
    </div>

    <div class="mb-6">
        <h1 class="text-2xl font-bold text-neutral-900">Commande {{ $order->reference }}</h1>
        <p class="text-neutral-500 mt-1">Passée le {{ $order->created_at->format('d M Y à H:i') }}</p>
    </div>

    <!-- Two-column layout -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        <!-- Left column — Order details (2/3) -->
        <div class="lg:col-span-2 space-y-6">

            <!-- Restaurant & Client info -->
            <div class="bg-white border border-neutral-200 rounded-xl p-6 shadow-sm">
                <h2 class="text-base font-semibold text-neutral-900 mb-4">Informations générales</h2>
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    <div>
                        <p class="text-xs font-medium text-neutral-500 uppercase tracking-wider mb-1">Restaurant</p>
                        <p class="text-neutral-900 font-medium">{{ $order->restaurant?->name ?? '—' }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-neutral-500 uppercase tracking-wider mb-1">Type</p>
                        <p class="text-neutral-900">{{ $order->type->value === 'delivery' ? 'Livraison' : ($order->type->value === 'pickup' ? 'À emporter' : 'Sur place') }}</p>
                    </div>
                    <div>
                        <p class="text-xs font-medium text-neutral-500 uppercase tracking-wider mb-1">Client</p>
                        <p class="text-neutral-900 font-medium">{{ $order->customer_name ?? '—' }}</p>
                        @if($order->customer_phone)
                            <p class="text-sm text-neutral-500">{{ $order->customer_phone }}</p>
                        @endif
                        @if($order->customer_email)
                            <p class="text-sm text-neutral-500">{{ $order->customer_email }}</p>
                        @endif
                    </div>
                    @if($order->delivery_address)
                        <div>
                            <p class="text-xs font-medium text-neutral-500 uppercase tracking-wider mb-1">Adresse de livraison</p>
                            <p class="text-neutral-900">{{ $order->delivery_address }}</p>
                            @if($order->delivery_city)
                                <p class="text-sm text-neutral-500">{{ $order->delivery_city }}</p>
                            @endif
                            @if($order->delivery_instructions)
                                <p class="text-xs text-neutral-400 italic mt-1">{{ $order->delivery_instructions }}</p>
                            @endif
                        </div>
                    @endif
                </div>
            </div>

            <!-- Order items -->
            <div class="bg-white border border-neutral-200 rounded-xl overflow-hidden shadow-sm">
                <div class="px-6 py-4 border-b border-neutral-100">
                    <h2 class="text-base font-semibold text-neutral-900">Articles commandés</h2>
                </div>
                <div class="divide-y divide-neutral-100">
                    @forelse($order->items as $item)
                        <div class="px-6 py-4 flex items-start justify-between gap-4">
                            <div class="flex-1">
                                <p class="font-medium text-neutral-900">
                                    <span class="text-neutral-500 mr-2">{{ $item->quantity }}×</span>{{ $item->dish_name }}
                                </p>
                                @if($item->selected_options_summary)
                                    <p class="text-xs text-neutral-500 mt-0.5">{{ $item->selected_options_summary }}</p>
                                @endif
                                @if($item->special_instructions)
                                    <p class="text-xs text-neutral-400 italic mt-0.5">{{ $item->special_instructions }}</p>
                                @endif
                            </div>
                            <div class="text-right flex-shrink-0">
                                <p class="font-semibold text-neutral-900">{{ number_format($item->total_price, 0, ',', ' ') }} FCFA</p>
                                <p class="text-xs text-neutral-500">{{ number_format($item->unit_price, 0, ',', ' ') }} / unité</p>
                            </div>
                        </div>
                    @empty
                        <div class="px-6 py-8 text-center text-neutral-400">Aucun article</div>
                    @endforelse
                </div>
            </div>

            @if($order->customer_notes)
                <div class="bg-amber-50 border border-amber-200 rounded-xl p-4">
                    <p class="text-xs font-medium text-amber-700 uppercase tracking-wider mb-1">Note du client</p>
                    <p class="text-neutral-800">{{ $order->customer_notes }}</p>
                </div>
            @endif
        </div>

        <!-- Right column — Timeline, driver, amounts (1/3) -->
        <div class="space-y-6">

            <!-- Amounts -->
            <div class="bg-white border border-neutral-200 rounded-xl p-6 shadow-sm">
                <h2 class="text-base font-semibold text-neutral-900 mb-4">Récapitulatif</h2>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between text-neutral-600">
                        <span>Sous-total</span>
                        <span>{{ number_format($order->subtotal, 0, ',', ' ') }} FCFA</span>
                    </div>
                    @if($order->delivery_fee > 0)
                        <div class="flex justify-between text-neutral-600">
                            <span>Frais de livraison</span>
                            <span>{{ number_format($order->delivery_fee, 0, ',', ' ') }} FCFA</span>
                        </div>
                    @endif
                    @if($order->discount_amount > 0)
                        <div class="flex justify-between text-emerald-600">
                            <span>Réduction</span>
                            <span>-{{ number_format($order->discount_amount, 0, ',', ' ') }} FCFA</span>
                        </div>
                    @endif
                    @if($order->tax_amount > 0)
                        <div class="flex justify-between text-neutral-600">
                            <span>Taxes</span>
                            <span>{{ number_format($order->tax_amount, 0, ',', ' ') }} FCFA</span>
                        </div>
                    @endif
                    @if($order->service_fee > 0)
                        <div class="flex justify-between text-neutral-600">
                            <span>Frais de service</span>
                            <span>{{ number_format($order->service_fee, 0, ',', ' ') }} FCFA</span>
                        </div>
                    @endif
                    <div class="pt-2 border-t border-neutral-200 flex justify-between font-bold text-neutral-900 text-base">
                        <span>Total</span>
                        <span>{{ number_format($order->total, 0, ',', ' ') }} FCFA</span>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-neutral-100">
                    <p class="text-xs font-medium text-neutral-500 uppercase tracking-wider mb-1">Paiement</p>
                    <p class="text-sm text-neutral-700">{{ $order->payment_status->label() }}</p>
                    @if($order->payment_method)
                        <p class="text-xs text-neutral-500 mt-0.5">{{ $order->payment_method }}</p>
                    @endif
                </div>
            </div>

            <!-- Delivery driver -->
            @if($order->delivery)
                <div class="bg-white border border-neutral-200 rounded-xl p-6 shadow-sm">
                    <h2 class="text-base font-semibold text-neutral-900 mb-4">Livreur assigné</h2>
                    @if($order->delivery->driver)
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white font-bold flex-shrink-0">
                                {{ strtoupper(substr($order->delivery->driver->name, 0, 1)) }}
                            </div>
                            <div>
                                <p class="font-medium text-neutral-900">{{ $order->delivery->driver->name }}</p>
                                @if($order->delivery->driver->phone)
                                    <p class="text-sm text-neutral-500">{{ $order->delivery->driver->phone }}</p>
                                @endif
                            </div>
                        </div>
                        <div class="mt-3 pt-3 border-t border-neutral-100 text-sm text-neutral-600 space-y-1">
                            <div class="flex justify-between">
                                <span>Statut livraison</span>
                                <span>{{ $order->delivery->status->value }}</span>
                            </div>
                            @if($order->delivery->assigned_at)
                                <div class="flex justify-between">
                                    <span>Assigné le</span>
                                    <span>{{ $order->delivery->assigned_at->format('d M H:i') }}</span>
                                </div>
                            @endif
                            @if($order->delivery->picked_up_at)
                                <div class="flex justify-between">
                                    <span>Pris en charge</span>
                                    <span>{{ $order->delivery->picked_up_at->format('d M H:i') }}</span>
                                </div>
                            @endif
                            @if($order->delivery->delivered_at)
                                <div class="flex justify-between">
                                    <span>Livré le</span>
                                    <span>{{ $order->delivery->delivered_at->format('d M H:i') }}</span>
                                </div>
                            @endif
                        </div>
                    @else
                        <p class="text-neutral-400 text-sm">Aucun livreur assigné</p>
                    @endif
                </div>
            @endif

            <!-- Status timeline -->
            <div class="bg-white border border-neutral-200 rounded-xl p-6 shadow-sm">
                <h2 class="text-base font-semibold text-neutral-900 mb-4">Historique de statuts</h2>
                <ol class="relative border-l border-neutral-200 space-y-4 ml-2">
                    <li class="ml-4">
                        <span class="absolute -left-1.5 w-3 h-3 bg-neutral-300 rounded-full border border-white"></span>
                        <p class="text-sm font-medium text-neutral-700">Commande créée</p>
                        <p class="text-xs text-neutral-400">{{ $order->created_at->format('d M Y à H:i') }}</p>
                    </li>
                    @if($order->confirmed_at)
                        <li class="ml-4">
                            <span class="absolute -left-1.5 w-3 h-3 bg-indigo-400 rounded-full border border-white"></span>
                            <p class="text-sm font-medium text-neutral-700">Confirmée</p>
                            <p class="text-xs text-neutral-400">{{ $order->confirmed_at->format('d M Y à H:i') }}</p>
                        </li>
                    @endif
                    @if($order->preparing_at)
                        <li class="ml-4">
                            <span class="absolute -left-1.5 w-3 h-3 bg-violet-400 rounded-full border border-white"></span>
                            <p class="text-sm font-medium text-neutral-700">En préparation</p>
                            <p class="text-xs text-neutral-400">{{ $order->preparing_at->format('d M Y à H:i') }}</p>
                        </li>
                    @endif
                    @if($order->ready_at)
                        <li class="ml-4">
                            <span class="absolute -left-1.5 w-3 h-3 bg-cyan-400 rounded-full border border-white"></span>
                            <p class="text-sm font-medium text-neutral-700">Prête</p>
                            <p class="text-xs text-neutral-400">{{ $order->ready_at->format('d M Y à H:i') }}</p>
                        </li>
                    @endif
                    @if($order->completed_at)
                        <li class="ml-4">
                            <span class="absolute -left-1.5 w-3 h-3 bg-emerald-400 rounded-full border border-white"></span>
                            <p class="text-sm font-medium text-neutral-700">Terminée</p>
                            <p class="text-xs text-neutral-400">{{ $order->completed_at->format('d M Y à H:i') }}</p>
                        </li>
                    @endif
                    @if($order->cancelled_at)
                        <li class="ml-4">
                            <span class="absolute -left-1.5 w-3 h-3 bg-red-400 rounded-full border border-white"></span>
                            <p class="text-sm font-medium text-neutral-700">Annulée</p>
                            <p class="text-xs text-neutral-400">{{ $order->cancelled_at->format('d M Y à H:i') }}</p>
                            @if($order->cancellation_reason)
                                <p class="text-xs text-neutral-400 italic mt-0.5">{{ $order->cancellation_reason }}</p>
                            @endif
                        </li>
                    @endif
                </ol>
            </div>

        </div>
    </div>
</x-layouts.admin-super>
