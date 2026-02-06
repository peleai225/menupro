<x-layouts.admin-restaurant title="Commande #{{ $order->reference }}">
    @php
        $statusColor = match($order->status->color()) {
            'warning' => 'bg-yellow-100 text-yellow-700',
            'info' => 'bg-blue-100 text-blue-700',
            'primary' => 'bg-primary-100 text-primary-700',
            'success' => 'bg-green-100 text-green-700',
            'error' => 'bg-red-100 text-red-700',
            default => 'bg-neutral-100 text-neutral-700',
        };
    @endphp

    <!-- Header -->
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('restaurant.orders') }}" class="p-2 hover:bg-neutral-100 rounded-lg">
            <svg class="w-5 h-5 text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"/>
            </svg>
        </a>
        <div class="flex-1">
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-neutral-900">Commande #{{ $order->reference }}</h1>
                <span class="badge {{ $statusColor }}">{{ $order->status->label() }}</span>
            </div>
            <p class="text-neutral-500 mt-1">
                Reçue {{ $order->created_at->locale('fr')->diffForHumans() }}
            </p>
        </div>
        @if($order->can_be_modified_by_manager)
            <button 
                onclick="openModifyModal()"
                class="btn btn-primary flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Modifier la commande
            </button>
        @endif
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-lg text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Order Details -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Items -->
            <div class="card" id="order-items-card">
                <div class="p-6 border-b border-neutral-100 flex items-center justify-between">
                    <h2 class="text-lg font-semibold text-neutral-900">Articles commandés</h2>
                    @if($order->can_be_modified_by_manager)
                        <button 
                            onclick="openModifyModal()"
                            class="text-sm text-primary-600 hover:text-primary-700 font-medium">
                            Modifier
                        </button>
                    @endif
                </div>
                <div class="divide-y divide-neutral-100" id="order-items-list">
                    @foreach($order->items as $item)
                        <div class="p-4 flex items-center justify-between" data-item-id="{{ $item->id }}">
                            <div class="flex items-center gap-4 flex-1">
                                @if($item->dish && $item->dish->image_path)
                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($item->dish->image_path) }}" 
                                         alt="{{ $item->dish_name }}"
                                         class="w-16 h-16 object-cover rounded-xl">
                                @else
                                    <div class="w-16 h-16 bg-neutral-200 rounded-xl flex items-center justify-center">
                                        <svg class="w-8 h-8 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                        </svg>
                                    </div>
                                @endif
                                <div class="flex-1">
                                    <p class="font-medium text-neutral-900">{{ $item->dish_name }}</p>
                                    <p class="text-sm text-neutral-500">
                                        {{ number_format($item->unit_price, 0, ',', ' ') }} F × {{ $item->quantity }}
                                    </p>
                                    @if($item->selected_options_summary)
                                        <p class="text-xs text-neutral-400 mt-1">{{ $item->selected_options_summary }}</p>
                                    @endif
                                    @if($item->special_instructions)
                                        <p class="text-xs text-amber-600 mt-1 italic">{{ $item->special_instructions }}</p>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center gap-4">
                                <span class="font-bold text-neutral-900">{{ number_format($item->total_price, 0, ',', ' ') }} F</span>
                                @if($order->can_be_modified_by_manager)
                                    <button 
                                        onclick="removeItem({{ $item->id }})"
                                        class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                        title="Retirer cet article">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
                <div class="p-6 bg-neutral-50 border-t border-neutral-100" id="order-totals">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-neutral-600">Sous-total</span>
                        <span class="font-medium text-neutral-900" id="order-subtotal">
                            {{ number_format($order->subtotal, 0, ',', ' ') }} F
                        </span>
                    </div>
                    @if($order->delivery_fee > 0)
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-neutral-600">Frais de livraison</span>
                            <span class="font-medium text-neutral-900">
                                {{ number_format($order->delivery_fee, 0, ',', ' ') }} F
                            </span>
                        </div>
                    @endif
                    @if($order->discount_amount > 0)
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-neutral-600">Réduction</span>
                            <span class="font-medium text-green-600">
                                -{{ number_format($order->discount_amount, 0, ',', ' ') }} F
                            </span>
                        </div>
                    @endif
                    @if($order->tax_amount > 0)
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-neutral-600">TVA</span>
                            <span class="font-medium text-neutral-900">
                                {{ number_format($order->tax_amount, 0, ',', ' ') }} F
                            </span>
                        </div>
                    @endif
                    @if($order->service_fee > 0)
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-neutral-600">Frais de service</span>
                            <span class="font-medium text-neutral-900">
                                {{ number_format($order->service_fee, 0, ',', ' ') }} F
                            </span>
                        </div>
                    @endif
                    <div class="flex items-center justify-between pt-2 border-t border-neutral-200">
                        <span class="font-bold text-neutral-900">Total</span>
                        <span class="text-xl font-bold text-primary-600" id="order-total">
                            {{ number_format($order->total, 0, ',', ' ') }} F
                        </span>
                    </div>
                </div>
            </div>

            <!-- Status Timeline -->
            <div class="card p-6">
                <h2 class="text-lg font-semibold text-neutral-900 mb-4">Historique</h2>
                <div class="space-y-4">
                    @php
                        $steps = [
                            ['key' => 'created', 'label' => 'Commande reçue', 'time' => $order->created_at],
                            ['key' => 'paid', 'label' => 'Paiement confirmé', 'time' => $order->paid_at],
                            ['key' => 'confirmed', 'label' => 'Commande confirmée', 'time' => $order->confirmed_at],
                            ['key' => 'preparing', 'label' => 'En préparation', 'time' => $order->preparing_at],
                            ['key' => 'ready', 'label' => 'Prête', 'time' => $order->ready_at],
                            ['key' => 'completed', 'label' => 'Terminée', 'time' => $order->completed_at],
                        ];
                    @endphp
                    @foreach($steps as $step)
                        @if($step['time'])
                            <div class="flex items-center gap-4">
                                <div class="w-4 h-4 rounded-full bg-primary-500"></div>
                                <div class="flex-1">
                                    <p class="font-medium text-neutral-900">{{ $step['label'] }}</p>
                                </div>
                                <span class="text-sm text-neutral-500">
                                    {{ $step['time']->locale('fr')->format('d/m/Y H:i') }}
                                </span>
                            </div>
                        @else
                            <div class="flex items-center gap-4">
                                <div class="w-4 h-4 rounded-full bg-neutral-200"></div>
                                <div class="flex-1">
                                    <p class="text-neutral-400">{{ $step['label'] }}</p>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Customer -->
            <div class="card p-6">
                <h2 class="text-lg font-semibold text-neutral-900 mb-4">Client</h2>
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 bg-primary-100 rounded-full flex items-center justify-center">
                        <span class="text-lg font-bold text-primary-600">
                            {{ strtoupper(substr($order->customer_name, 0, 1)) }}
                        </span>
                    </div>
                    <div>
                        <p class="font-medium text-neutral-900">{{ $order->customer_name }}</p>
                    </div>
                </div>
                <div class="space-y-3">
                    <div class="flex items-center gap-3 text-neutral-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        <a href="tel:{{ $order->customer_phone }}" class="hover:text-primary-600">
                            {{ $order->customer_phone }}
                        </a>
                    </div>
                    @if($order->customer_email)
                        <div class="flex items-center gap-3 text-neutral-600">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                            <a href="mailto:{{ $order->customer_email }}" class="hover:text-primary-600">
                                {{ $order->customer_email }}
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Delivery Info -->
            @if($order->type->value === 'delivery' && $order->delivery_address)
                <div class="card p-6">
                    <h2 class="text-lg font-semibold text-neutral-900 mb-4">Livraison</h2>
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-neutral-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <div>
                            <p class="text-neutral-900">{{ $order->delivery_address }}</p>
                            <p class="text-sm text-neutral-500">{{ $order->delivery_city }}</p>
                            @if($order->delivery_instructions)
                                <p class="text-sm text-amber-600 mt-2 italic">{{ $order->delivery_instructions }}</p>
                            @endif
                        </div>
                    </div>
                </div>
            @endif

            @if($order->type->value === 'dine_in' && $order->table_number)
                <div class="card p-6">
                    <h2 class="text-lg font-semibold text-neutral-900 mb-4">Table</h2>
                    <p class="text-neutral-900 font-medium">{{ $order->table_number }}</p>
                </div>
            @endif

            <!-- Notes -->
            @if($order->customer_notes)
                <div class="card p-6">
                    <h2 class="text-lg font-semibold text-neutral-900 mb-4">Note du client</h2>
                    <p class="text-neutral-600 italic">{{ $order->customer_notes }}</p>
                </div>
            @endif

            <!-- Actions -->
            <div class="space-y-3">
                @if(count($allowedTransitions) > 0)
                    <form action="{{ route('restaurant.orders.status', $order) }}" method="POST" class="space-y-2">
                        @csrf
                        @method('PATCH')
                        <select name="status" 
                                onchange="this.form.submit()"
                                class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            <option value="">Changer le statut...</option>
                            @foreach($allowedTransitions as $transition)
                                <option value="{{ $transition->value }}">{{ $transition->label() }}</option>
                            @endforeach
                        </select>
                    </form>
                @endif

                <a href="{{ route('restaurant.orders.print', $order) }}" 
                   target="_blank"
                   class="btn btn-outline w-full flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Imprimer le ticket
                </a>

                @if($order->can_be_cancelled)
                    <form action="{{ route('restaurant.orders.cancel', $order) }}" 
                          method="POST"
                          onsubmit="return confirm('Êtes-vous sûr de vouloir annuler cette commande ?')">
                        @csrf
                        <button type="submit" class="btn btn-ghost w-full text-red-600 hover:bg-red-50 flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Annuler la commande
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    <!-- Modify Order Modal -->
    @if($order->can_be_modified_by_manager)
        @include('pages.restaurant.order-modify-modal', ['order' => $order, 'availableDishes' => $availableDishes ?? collect()])
    @endif

    <script>
        const orderId = {{ $order->id }};
        const csrfToken = '{{ csrf_token() }}';

        function openModifyModal() {
            document.getElementById('modify-order-modal').classList.remove('hidden');
        }

        function closeModifyModal() {
            document.getElementById('modify-order-modal').classList.add('hidden');
        }

        async function removeItem(itemId) {
            if (!confirm('Êtes-vous sûr de vouloir retirer cet article de la commande ?')) {
                return;
            }

            try {
                const response = await fetch(`/dashboard/commandes/${orderId}/items/${itemId}`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                    },
                });

                const data = await response.json();

                if (data.success) {
                    // Remove item from DOM
                    document.querySelector(`[data-item-id="${itemId}"]`).remove();
                    
                    // Update totals
                    updateTotals(data.order);
                    
                    // Show success message
                    showMessage('Article retiré avec succès.', 'success');
                    
                    // Reload page after 1 second to ensure consistency
                    setTimeout(() => window.location.reload(), 1000);
                } else {
                    showMessage(data.message || 'Erreur lors de la suppression.', 'error');
                }
            } catch (error) {
                showMessage('Erreur lors de la suppression.', 'error');
                console.error(error);
            }
        }

        function updateTotals(order) {
            document.getElementById('order-subtotal').textContent = 
                new Intl.NumberFormat('fr-FR').format(order.subtotal) + ' F';
            document.getElementById('order-total').textContent = 
                new Intl.NumberFormat('fr-FR').format(order.total) + ' F';
        }

        function showMessage(message, type) {
            const bgColor = type === 'success' ? 'bg-green-50 border-green-200 text-green-700' : 'bg-red-50 border-red-200 text-red-700';
            const messageDiv = document.createElement('div');
            messageDiv.className = `mb-6 p-4 border rounded-lg ${bgColor}`;
            messageDiv.textContent = message;
            
            const container = document.querySelector('.grid');
            container.insertBefore(messageDiv, container.firstChild);
            
            setTimeout(() => messageDiv.remove(), 5000);
        }
    </script>
</x-layouts.admin-restaurant>
