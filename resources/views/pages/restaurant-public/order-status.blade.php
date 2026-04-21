<x-layouts.restaurant-public :restaurant="$restaurant" :hide-header="true">
    <div class="min-h-screen bg-neutral-50 py-8">
        <div class="max-w-2xl mx-auto px-4">
            @if(session('error'))
                <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 flex items-start gap-3">
                    <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <p class="font-medium">Erreur de paiement</p>
                        <p class="text-sm mt-1">{{ session('error') }}</p>
                    </div>
                </div>
            @endif
            @if(session('success'))
                <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-700">
                    {{ session('success') }}
                </div>
            @endif
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="w-20 h-20 bg-primary-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-neutral-900">Commande #{{ $order->reference }}</h1>
                <p class="text-neutral-500 mt-1">Merci pour votre commande !</p>
            </div>

            <!-- Status Card -->
            <div class="card p-6 mb-6">
                <div class="flex items-center justify-center gap-3 mb-6">
                    @php
                        $colorMap = [
                            'warning' => 'bg-yellow-500',
                            'primary' => 'bg-primary-500',
                            'success' => 'bg-emerald-500',
                            'error' => 'bg-red-500',
                            'neutral' => 'bg-neutral-500',
                            'info' => 'bg-blue-500',
                        ];
                        $textColorMap = [
                            'warning' => 'text-yellow-600',
                            'primary' => 'text-primary-600',
                            'success' => 'text-emerald-600',
                            'error' => 'text-red-600',
                            'neutral' => 'text-neutral-600',
                            'info' => 'text-blue-600',
                        ];
                        $statusColor = $order->status->color();
                        $bgClass = $colorMap[$statusColor] ?? 'bg-primary-500';
                        $textClass = $textColorMap[$statusColor] ?? 'text-primary-600';
                    @endphp
                    <span class="status-dot w-3 h-3 {{ $bgClass }} rounded-full {{ $order->status->value !== 'completed' && $order->status->value !== 'cancelled' ? 'animate-pulse' : '' }}"></span>
                    <span class="status-text text-lg font-semibold {{ $textClass }}">{{ $order->status->label() }}</span>
                </div>

                <!-- Progress Steps -->
                <div class="relative">
                    <div class="absolute left-6 top-0 bottom-0 w-0.5 bg-neutral-200"></div>
                    
                    <div class="progress-container space-y-8">
                        @foreach($progress as $step)
                            @php
                                $isCompleted = $step['completed'];
                                $isCurrent = $step['current'] ?? false;
                                $isError = $step['error'] ?? false;
                                
                                $bgClass = match(true) {
                                    $isError => 'bg-red-500',
                                    $isCompleted => 'bg-emerald-500',
                                    $isCurrent => 'bg-primary-500',
                                    default => 'bg-neutral-200',
                                };
                                
                                $iconClass = match(true) {
                                    $isError => 'text-white',
                                    $isCompleted => 'text-white',
                                    $isCurrent => 'text-white',
                                    default => 'text-neutral-400',
                                };
                                
                                $textClass = match(true) {
                                    $isError => 'text-red-600',
                                    $isCompleted => 'text-neutral-900',
                                    $isCurrent => 'text-primary-600',
                                    default => 'text-neutral-400',
                                };
                                
                                $timeDisplay = $step['time'] 
                                    ? $step['time']->locale('fr')->isoFormat('HH:mm') 
                                    : null;
                            @endphp
                            <div class="relative flex items-start gap-4">
                                <div class="w-12 h-12 {{ $bgClass }} rounded-full flex items-center justify-center z-10 {{ $isCurrent ? 'animate-pulse' : '' }}">
                                    @if($isCompleted && !$isError)
                                        <svg class="w-6 h-6 {{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    @elseif($isError)
                                        <svg class="w-6 h-6 {{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    @elseif($isCurrent)
                                        <svg class="w-6 h-6 {{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    @else
                                        <svg class="w-6 h-6 {{ $iconClass }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    @endif
                                </div>
                                <div class="flex-1 pt-3">
                                    <p class="font-semibold {{ $textClass }}">{{ $step['label'] }}</p>
                                    @if($timeDisplay)
                                        <p class="text-sm text-neutral-500">{{ $timeDisplay }} - {{ $step['key'] === 'placed' ? 'Votre commande a été reçue' : ($step['key'] === 'paid' ? 'Paiement confirmé' : ($step['key'] === 'confirmed' ? 'Commande confirmée par le restaurant' : ($step['key'] === 'preparing' ? 'Le restaurant prépare votre commande' : ($step['key'] === 'ready' ? 'Votre commande est prête' : ($step['key'] === 'completed' ? 'Commande terminée' : ''))))) }}</p>
                                    @else
                                        <p class="text-sm {{ $isCurrent ? 'text-neutral-500' : 'text-neutral-400' }}">En attente...</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Order Details -->
            <div class="card p-6 mb-6" id="order-details-card">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="font-semibold text-neutral-900">Détails de la commande</h2>
                    @if($order->canBeModifiedByCustomer())
                        <button 
                            onclick="openModifyModal()"
                            class="text-sm text-primary-600 hover:text-primary-700 font-medium flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                            Modifier
                        </button>
                    @endif
                </div>
                
                @if($order->canBeModifiedByCustomer() && $order->remaining_modification_time)
                    <div class="mb-4 p-3 bg-amber-50 border border-amber-200 rounded-lg">
                        <p class="text-sm text-amber-700">
                            ⏱️ Vous pouvez modifier votre commande pendant encore <strong>{{ $order->remaining_modification_time }} minute(s)</strong>.
                        </p>
                    </div>
                @endif
                
                <div class="divide-y divide-neutral-100" id="order-items-list">
                    @forelse($order->items as $item)
                    <div class="py-3 flex items-center justify-between" data-item-id="{{ $item->id }}">
                        <div class="flex-1">
                            <span class="text-neutral-900 font-medium">{{ $item->dish_name }}</span>
                            @if($item->selected_options_summary)
                                <p class="text-xs text-neutral-500 mt-0.5">{{ $item->selected_options_summary }}</p>
                            @endif
                            <span class="text-neutral-500 text-sm ml-2">× {{ $item->quantity }}</span>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="font-medium text-neutral-900">{{ number_format($item->total_price, 0, ',', ' ') }} F</span>
                            @if($order->canBeModifiedByCustomer())
                                <button 
                                    onclick="removeItem({{ $item->id }})"
                                    class="p-1 text-red-600 hover:bg-red-50 rounded transition-colors"
                                    title="Retirer">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            @endif
                        </div>
                    </div>
                    @empty
                    <p class="text-neutral-500 py-4 text-center">Aucun article dans cette commande</p>
                    @endforelse
                </div>

                <div class="border-t border-neutral-200 mt-4 pt-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-neutral-600">Sous-total</span>
                        <span class="text-neutral-900">{{ number_format($order->subtotal, 0, ',', ' ') }} F</span>
                    </div>
                    @if($order->delivery_fee > 0)
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-neutral-600">Livraison</span>
                        <span class="text-neutral-900">{{ number_format($order->delivery_fee, 0, ',', ' ') }} F</span>
                    </div>
                    @endif
                    @if($order->discount_amount > 0)
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-neutral-600">Réduction</span>
                        <span class="text-emerald-600">-{{ number_format($order->discount_amount, 0, ',', ' ') }} F</span>
                    </div>
                    @endif
                    @if($order->tax_amount > 0)
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-neutral-600">{{ $order->restaurant->tax_name ?? 'Taxe' }}</span>
                        <span class="text-neutral-900">{{ number_format($order->tax_amount, 0, ',', ' ') }} F</span>
                    </div>
                    @endif
                    @if($order->service_fee > 0)
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-neutral-600">Frais de service</span>
                        <span class="text-neutral-900">{{ number_format($order->service_fee, 0, ',', ' ') }} F</span>
                    </div>
                    @endif
                    <div class="flex items-center justify-between pt-2 border-t border-neutral-200">
                        <span class="font-semibold text-neutral-900">Total</span>
                        <span class="text-xl font-bold text-primary-600" id="order-total-display">{{ number_format($order->total, 0, ',', ' ') }} F</span>
                    </div>
                </div>
            </div>

            <!-- Modify Order Section (Customer) -->
            @if($order->canBeModifiedByCustomer())
                @include('pages.restaurant-public.order-modify-modal', [
                    'order' => $order, 
                    'restaurant' => $restaurant,
                    'availableDishes' => $availableDishes ?? collect()
                ])
            @endif

            @if($order->type->requiresAddress() && $order->delivery_address)
            <!-- Delivery Info -->
            <div class="card p-6 mb-6">
                <h2 class="font-semibold text-neutral-900 mb-4">Adresse de livraison</h2>
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-neutral-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <div>
                        <p class="text-neutral-900">{{ $order->delivery_address }}</p>
                        @if($order->delivery_city)
                            <p class="text-sm text-neutral-500">{{ $order->delivery_city }}</p>
                        @endif
                        @if($order->delivery_instructions)
                            <p class="text-sm text-neutral-500 mt-1 italic">{{ $order->delivery_instructions }}</p>
                        @endif
                    </div>
                </div>
            </div>
            @elseif($order->table_number)
            <!-- Table Info -->
            <div class="card p-6 mb-6">
                <h2 class="font-semibold text-neutral-900 mb-4">Informations</h2>
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-neutral-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                    <div>
                        <p class="text-neutral-900">Table {{ $order->table_number }}</p>
                        <p class="text-sm text-neutral-500">Commande sur place</p>
                    </div>
                </div>
            </div>
            @endif

            @if($restaurant->phone)
            <!-- Contact -->
            <div class="card p-6">
                <h2 class="font-semibold text-neutral-900 mb-4">Besoin d'aide ?</h2>
                <a href="tel:{{ $restaurant->phone }}" class="btn btn-outline w-full">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                    </svg>
                    Appeler le restaurant
                </a>
            </div>
            @endif

            <!-- Share QR Code -->
            <div class="card p-6 mb-6">
                <h2 class="font-semibold text-neutral-900 mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                    </svg>
                    Partager le suivi
                </h2>
                <div class="flex flex-col items-center gap-4">
                    @php
                        $trackingUrl = route('r.order.status', [$restaurant->slug, $order->tracking_token]);
                        $qrCodeUrl = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($trackingUrl);
                    @endphp
                    <div class="p-4 bg-white rounded-xl border-2 border-neutral-200">
                        <img src="{{ $qrCodeUrl }}" 
                             alt="QR Code pour le suivi de commande" 
                             class="w-48 h-48">
                    </div>
                    <div class="w-full">
                        <div class="flex gap-2 mb-2">
                            <input type="text" 
                                   id="tracking-url" 
                                   value="{{ $trackingUrl }}" 
                                   readonly 
                                   class="flex-1 px-4 py-2 border border-neutral-200 rounded-lg text-sm bg-neutral-50">
                            <button onclick="copyTrackingUrl(this)" 
                                    class="px-4 py-2 bg-primary-500 text-white rounded-lg hover:bg-primary-600 transition-colors font-medium">
                                <svg class="w-5 h-5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                                Copier
                            </button>
                        </div>
                        <p class="text-xs text-neutral-500 text-center">Scannez le QR code ou copiez le lien pour partager le suivi de votre commande</p>
                    </div>
                </div>
            </div>

            <!-- Review Section (if completed and no review) -->
            @if($order->status->value === 'completed' && !$order->review)
                <div class="card p-6 mb-6 bg-gradient-to-r from-primary-50 to-secondary-50 border-2 border-primary-200">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-primary-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                            </svg>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-bold text-neutral-900 mb-2">Partagez votre expérience</h3>
                            <p class="text-sm text-neutral-600 mb-4">
                                Votre avis nous aide à améliorer nos services. Laissez-nous savoir ce que vous avez pensé de votre commande !
                            </p>
                            <a href="{{ route('r.review.create', [$restaurant->slug, $order->tracking_token]) }}" 
                               class="btn btn-primary inline-flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                </svg>
                                Laisser un avis
                            </a>
                        </div>
                    </div>
                </div>
            @endif

            <!-- Back to Menu -->
            <div class="text-center mt-8">
                <a href="{{ route('r.menu', $restaurant->slug) }}" class="text-primary-600 hover:text-primary-700 font-medium">
                    ← Retour au menu
                </a>
            </div>
        </div>
    </div>
</x-layouts.restaurant-public>

<script>
    // Copy USSD code
    function copyUssdCode(button) {
        const input = document.getElementById('ussd-code');
        if (!input) return;
        input.select();
        navigator.clipboard.writeText(input.value).then(function() {
            const orig = button.textContent;
            button.textContent = 'Copié !';
            button.classList.add('bg-emerald-500');
            setTimeout(() => { button.textContent = orig; button.classList.remove('bg-emerald-500'); }, 2000);
        });
    }

    // Copy URL function
    function copyTrackingUrl(button) {
        const urlInput = document.getElementById('tracking-url');
        urlInput.select();
        urlInput.setSelectionRange(0, 99999); // For mobile devices
        
        navigator.clipboard.writeText(urlInput.value).then(function() {
            // Show success feedback
            const originalText = button.innerHTML;
            button.innerHTML = '<svg class="w-5 h-5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>Copié !';
            button.classList.add('bg-emerald-500', 'hover:bg-emerald-600');
            button.classList.remove('bg-primary-500', 'hover:bg-primary-600');
            
            setTimeout(function() {
                button.innerHTML = originalText;
                button.classList.remove('bg-emerald-500', 'hover:bg-emerald-600');
                button.classList.add('bg-primary-500', 'hover:bg-primary-600');
            }, 2000);
        }).catch(function(err) {
            console.error('Erreur lors de la copie:', err);
            alert('Impossible de copier le lien. Veuillez le sélectionner manuellement.');
        });
    }

    // Auto-refresh order status
    (function() {
        const orderId = {{ $order->id }};
        const restaurantSlug = '{{ $restaurant->slug }}';
        const statusUrl = `{{ route('r.order.status.json', [$restaurant->slug, $order->tracking_token]) }}`;
        const isOrderFinal = {{ $order->is_final ? 'true' : 'false' }};
        let currentStatus = '{{ $order->status->value }}';
        let pollingInterval = null;
        let isPolling = !isOrderFinal;

        // Color mapping
        const colorMap = {
            'warning': { bg: 'bg-yellow-500', text: 'text-yellow-600' },
            'primary': { bg: 'bg-primary-500', text: 'text-primary-600' },
            'success': { bg: 'bg-emerald-500', text: 'text-emerald-600' },
            'error': { bg: 'bg-red-500', text: 'text-red-600' },
            'neutral': { bg: 'bg-neutral-500', text: 'text-neutral-600' },
            'info': { bg: 'bg-blue-500', text: 'text-blue-600' },
        };

        function updateStatusDisplay(data) {
            // Hide MenuPro Hub payment instructions when payment is completed
            if (data.payment_status === 'completed' && document.getElementById('hub-payment-instructions')) {
                document.getElementById('hub-payment-instructions').style.display = 'none';
            }

            // Update status badge
            const statusBadge = document.querySelector('.status-badge');
            const statusText = document.querySelector('.status-text');
            const statusDot = document.querySelector('.status-dot');
            
            if (statusBadge && statusText && statusDot) {
                const color = colorMap[data.status_color] || colorMap.primary;
                
                // Update dot
                statusDot.className = `w-3 h-3 ${color.bg} rounded-full ${data.is_final ? '' : 'animate-pulse'}`;
                
                // Update text
                statusText.textContent = data.status_label;
                statusText.className = `text-lg font-semibold ${color.text}`;
            }

            // Update progress steps if needed
            if (data.progress && Array.isArray(data.progress)) {
                updateProgressSteps(data.progress);
            }

            // Stop polling if order is final
            if (data.is_final) {
                stopPolling();
            }
        }

        function updateProgressSteps(progress) {
            const progressContainer = document.querySelector('.progress-container');
            if (!progressContainer) return;

            // Re-render progress steps
            progressContainer.innerHTML = progress.map(step => {
                const isCompleted = step.completed;
                const isCurrent = step.current || false;
                const isError = step.error || false;
                
                const bgClass = isError ? 'bg-red-500' : 
                               isCompleted ? 'bg-emerald-500' : 
                               isCurrent ? 'bg-primary-500' : 'bg-neutral-200';
                
                const iconClass = (isError || isCompleted || isCurrent) ? 'text-white' : 'text-neutral-400';
                const textClass = isError ? 'text-red-600' : 
                                 isCompleted ? 'text-neutral-900' : 
                                 isCurrent ? 'text-primary-600' : 'text-neutral-400';
                
                const timeDisplay = step.time ? new Date(step.time).toLocaleTimeString('fr-FR', { hour: '2-digit', minute: '2-digit' }) : null;
                
                let icon = '';
                if (isCompleted && !isError) {
                    icon = '<svg class="w-6 h-6 ' + iconClass + '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>';
                } else if (isError) {
                    icon = '<svg class="w-6 h-6 ' + iconClass + '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>';
                } else if (isCurrent) {
                    icon = '<svg class="w-6 h-6 ' + iconClass + '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
                } else {
                    icon = '<svg class="w-6 h-6 ' + iconClass + '" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>';
                }
                
                const description = step.key === 'placed' ? 'Votre commande a été reçue' :
                                   step.key === 'paid' ? 'Paiement confirmé' :
                                   step.key === 'confirmed' ? 'Commande confirmée par le restaurant' :
                                   step.key === 'preparing' ? 'Le restaurant prépare votre commande' :
                                   step.key === 'ready' ? 'Votre commande est prête' :
                                   step.key === 'completed' ? 'Commande terminée' : '';
                
                return `
                    <div class="relative flex items-start gap-4">
                        <div class="w-12 h-12 ${bgClass} rounded-full flex items-center justify-center z-10 ${isCurrent ? 'animate-pulse' : ''}">
                            ${icon}
                        </div>
                        <div class="flex-1 pt-3">
                            <p class="font-semibold ${textClass}">${step.label}</p>
                            ${timeDisplay ? `<p class="text-sm text-neutral-500">${timeDisplay} - ${description}</p>` : `<p class="text-sm ${isCurrent ? 'text-neutral-500' : 'text-neutral-400'}">En attente...</p>`}
                        </div>
                    </div>
                `;
            }).join('');
        }

        function checkStatus() {
            if (!isPolling) return;

            fetch(statusUrl, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                },
                cache: 'no-cache'
            })
            .then(response => {
                if (!response.ok) {
                    throw new Error('Network response was not ok');
                }
                return response.json();
            })
            .then(data => {
                if (data.status !== currentStatus) {
                    currentStatus = data.status;
                    updateStatusDisplay(data);
                    
                    // Show a subtle notification
                    if (data.status_label) {
                        showStatusUpdate(data.status_label);
                    }
                }
            })
            .catch(error => {
                console.error('Error checking order status:', error);
                // Don't stop polling on error, just log it
            });
        }

        function showStatusUpdate(statusLabel) {
            // Create a subtle notification
            const notification = document.createElement('div');
            notification.className = 'fixed top-4 right-4 bg-primary-500 text-white px-4 py-2 rounded-lg shadow-lg z-50 animate-slide-in';
            notification.textContent = 'Statut mis à jour : ' + statusLabel;
            
            document.body.appendChild(notification);
            
            setTimeout(() => {
                notification.classList.add('animate-fade-out');
                setTimeout(() => notification.remove(), 300);
            }, 3000);
        }

        function stopPolling() {
            isPolling = false;
            if (pollingInterval) {
                clearInterval(pollingInterval);
                pollingInterval = null;
            }
        }

        // Start polling every 5 seconds if order is not final
        if (!isOrderFinal) {
            pollingInterval = setInterval(checkStatus, 5000);
            
            // Also check immediately on page load
            setTimeout(checkStatus, 1000);
        }

        // Stop polling when page is hidden (to save resources)
        document.addEventListener('visibilitychange', function() {
            if (document.hidden) {
                // Page is hidden, could pause polling
            } else {
                // Page is visible again, resume polling if needed
                if (!pollingInterval && !isOrderFinal) {
                    pollingInterval = setInterval(checkStatus, 5000);
                    checkStatus();
                }
            }
        });
    })();
</script>

<style>
    @keyframes slide-in {
        from {
            transform: translateX(100%);
            opacity: 0;
        }
        to {
            transform: translateX(0);
            opacity: 1;
        }
    }
    
    @keyframes fade-out {
        from {
            opacity: 1;
        }
        to {
            opacity: 0;
        }
    }
    
    .animate-slide-in {
        animation: slide-in 0.3s ease-out;
    }
    
    .animate-fade-out {
        animation: fade-out 0.3s ease-out;
    }
</style>

