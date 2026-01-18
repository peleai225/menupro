<div class="min-h-screen bg-neutral-50 py-8">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <!-- Header -->
        <div class="mb-8">
            <a href="{{ route('r.menu', $restaurant->slug) }}" class="inline-flex items-center gap-2 text-neutral-500 hover:text-neutral-700 mb-4">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Retour au menu
            </a>
            <h1 class="text-2xl md:text-3xl font-bold text-neutral-900">Finaliser la commande</h1>
            <p class="text-neutral-500 mt-2">{{ $restaurant->name }}</p>
        </div>

        <!-- Flash Messages -->
        @if(session('error'))
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700">
                {{ session('error') }}
            </div>
        @endif

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Form Section -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Order Type -->
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-neutral-900 mb-4">Type de commande</h2>
                    <div class="grid grid-cols-3 gap-4">
                        <button type="button" 
                                wire:click="$set('order_type', 'dine_in')"
                                class="p-4 rounded-xl border-2 transition-all text-center {{ $order_type === 'dine_in' ? 'border-primary-500 bg-primary-50' : 'border-neutral-200 hover:border-neutral-300' }}">
                            <svg class="w-8 h-8 mx-auto mb-2 {{ $order_type === 'dine_in' ? 'text-primary-600' : 'text-neutral-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            <span class="font-medium {{ $order_type === 'dine_in' ? 'text-primary-700' : 'text-neutral-700' }}">Sur place</span>
                        </button>
                        <button type="button"
                                wire:click="$set('order_type', 'takeaway')"
                                class="p-4 rounded-xl border-2 transition-all text-center {{ $order_type === 'takeaway' ? 'border-primary-500 bg-primary-50' : 'border-neutral-200 hover:border-neutral-300' }}">
                            <svg class="w-8 h-8 mx-auto mb-2 {{ $order_type === 'takeaway' ? 'text-primary-600' : 'text-neutral-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                            <span class="font-medium {{ $order_type === 'takeaway' ? 'text-primary-700' : 'text-neutral-700' }}">À emporter</span>
                        </button>
                        <button type="button"
                                wire:click="$set('order_type', 'delivery')"
                                class="p-4 rounded-xl border-2 transition-all text-center {{ $order_type === 'delivery' ? 'border-primary-500 bg-primary-50' : 'border-neutral-200 hover:border-neutral-300' }}">
                            <svg class="w-8 h-8 mx-auto mb-2 {{ $order_type === 'delivery' ? 'text-primary-600' : 'text-neutral-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
                            </svg>
                            <span class="font-medium {{ $order_type === 'delivery' ? 'text-primary-700' : 'text-neutral-700' }}">Livraison</span>
                        </button>
                    </div>
                </div>

                <!-- Customer Info -->
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-neutral-900 mb-4">Vos informations</h2>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">Nom complet *</label>
                            <input type="text" 
                                   wire:model="customer_name"
                                   class="w-full px-4 py-3 border border-neutral-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('customer_name') border-red-500 @enderror"
                                   placeholder="Ex: Jean Dupont">
                            @error('customer_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 mb-2">Email *</label>
                                <input type="email" 
                                       wire:model="customer_email"
                                       class="w-full px-4 py-3 border border-neutral-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('customer_email') border-red-500 @enderror"
                                       placeholder="jean@email.com">
                                @error('customer_email')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 mb-2">Téléphone *</label>
                                <input type="tel" 
                                       wire:model="customer_phone"
                                       class="w-full px-4 py-3 border border-neutral-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('customer_phone') border-red-500 @enderror"
                                       placeholder="+225 07 00 00 00 00">
                                @error('customer_phone')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Table Number (Sur place) -->
                @if($order_type === 'dine_in')
                    <div class="bg-white rounded-2xl p-6 shadow-sm">
                        <h2 class="text-lg font-bold text-neutral-900 mb-4">Numéro de table</h2>
                        <input type="text" 
                               wire:model="table_number"
                               class="w-full px-4 py-3 border border-neutral-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('table_number') border-red-500 @enderror"
                               placeholder="Ex: Table 5">
                        @error('table_number')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                <!-- Delivery Address (Livraison) -->
                @if($order_type === 'delivery')
                    <div class="bg-white rounded-2xl p-6 shadow-sm">
                        <h2 class="text-lg font-bold text-neutral-900 mb-4">Adresse de livraison</h2>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 mb-2">Adresse *</label>
                                <input type="text" 
                                       wire:model="delivery_address"
                                       class="w-full px-4 py-3 border border-neutral-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('delivery_address') border-red-500 @enderror"
                                       placeholder="Rue, quartier, repère...">
                                @error('delivery_address')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 mb-2">Ville *</label>
                                <input type="text" 
                                       wire:model="delivery_city"
                                       class="w-full px-4 py-3 border border-neutral-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('delivery_city') border-red-500 @enderror"
                                       placeholder="Ex: Abidjan">
                                @error('delivery_city')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 mb-2">Instructions (optionnel)</label>
                                <textarea wire:model="delivery_instructions"
                                          rows="2"
                                          class="w-full px-4 py-3 border border-neutral-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                          placeholder="Ex: Code portail, étage..."></textarea>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Notes -->
                <div class="bg-white rounded-2xl p-6 shadow-sm">
                    <h2 class="text-lg font-bold text-neutral-900 mb-4">Note (optionnel)</h2>
                    <textarea wire:model="customer_notes"
                              rows="3"
                              class="w-full px-4 py-3 border border-neutral-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                              placeholder="Allergies, préférences particulières..."></textarea>
                </div>
            </div>

            <!-- Order Summary -->
            <div class="lg:col-span-1">
                <div class="bg-white rounded-2xl p-8 shadow-sm sticky top-4">
                    <h2 class="text-xl font-bold text-neutral-900 mb-6 flex items-center gap-2">
                        <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                        </svg>
                        Récapitulatif
                    </h2>
                    
                    <!-- Items -->
                    <div class="space-y-4 max-h-80 overflow-y-auto mb-6 pb-4 border-b border-neutral-200">
                        @foreach($cart as $key => $item)
                            <div class="flex items-start gap-4 p-4 bg-neutral-50 rounded-xl hover:bg-neutral-100 transition-colors">
                                <div class="flex-shrink-0">
                                    @if(!empty($item['image']))
                                        <img src="{{ Storage::url($item['image']) }}" 
                                             alt="{{ $item['name'] }}" 
                                             class="w-16 h-16 object-cover rounded-lg">
                                    @else
                                        <div class="w-16 h-16 bg-neutral-200 rounded-lg flex items-center justify-center">
                                            <svg class="w-8 h-8 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                            </svg>
                                        </div>
                                    @endif
                                </div>
                                <div class="flex-1 min-w-0">
                                    <div class="flex items-start justify-between gap-2 mb-1">
                                        <p class="font-semibold text-neutral-900 text-sm line-clamp-1">{{ $item['name'] }}</p>
                                        <span class="flex-shrink-0 w-8 h-8 bg-primary-100 text-primary-600 rounded-lg flex items-center justify-center font-bold text-xs">
                                            ×{{ $item['quantity'] }}
                                        </span>
                                    </div>
                                    @if(!empty($item['options']))
                                        <p class="text-xs text-neutral-500 mb-2">{{ collect($item['options'])->pluck('name')->join(', ') }}</p>
                                    @endif
                                    <div class="flex items-center justify-between">
                                        <p class="font-bold text-primary-600 text-sm">{{ number_format($item['total'], 0, ',', ' ') }} F</p>
                                        <button wire:click="removeCartItem('{{ $key }}')" 
                                                class="p-1.5 text-red-500 hover:bg-red-50 rounded-lg transition-colors"
                                                title="Retirer">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>

                    <!-- Promo Code -->
                    <div class="mb-6">
                        @if($appliedPromo)
                            <div class="flex items-center justify-between p-3 bg-secondary-50 rounded-xl">
                                <div>
                                    <p class="text-sm font-medium text-secondary-700">{{ $appliedPromo['code'] }}</p>
                                    <p class="text-xs text-secondary-600">{{ $appliedPromo['label'] }}</p>
                                </div>
                                <button wire:click="removePromoCode" class="text-secondary-500 hover:text-secondary-600">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        @else
                            <div class="flex gap-2">
                                <input type="text" 
                                       wire:model="promo_code"
                                       class="flex-1 px-3 py-2 border border-neutral-200 rounded-lg text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                       placeholder="Code promo">
                                <button wire:click="applyPromoCode" class="px-4 py-2 bg-neutral-100 text-neutral-700 rounded-lg text-sm font-medium hover:bg-neutral-200">
                                    Appliquer
                                </button>
                            </div>
                            @if($promoError)
                                <p class="mt-1 text-xs text-red-600">{{ $promoError }}</p>
                            @endif
                        @endif
                    </div>

                    <!-- Totals -->
                    <div class="border-t border-neutral-200 pt-6 space-y-3">
                        <div class="flex justify-between items-center text-neutral-700">
                            <span class="flex items-center gap-2">
                                <svg class="w-4 h-4 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                </svg>
                                Sous-total
                            </span>
                            <span class="font-semibold">{{ number_format($this->subtotal, 0, ',', ' ') }} F</span>
                        </div>
                        @if($this->deliveryFee > 0)
                            <div class="flex justify-between items-center text-neutral-700">
                                <span class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
                                    </svg>
                                    Livraison
                                </span>
                                <span class="font-semibold">{{ number_format($this->deliveryFee, 0, ',', ' ') }} F</span>
                            </div>
                        @endif
                        @if($this->discount > 0)
                            <div class="flex justify-between items-center text-emerald-600">
                                <span class="flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Réduction
                                </span>
                                <span class="font-semibold">-{{ number_format($this->discount, 0, ',', ' ') }} F</span>
                            </div>
                        @endif
                        @if($this->taxAmount > 0 && !$restaurant->tax_included)
                            <div class="flex justify-between items-center text-neutral-700">
                                <span class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-5m-3 5h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                    </svg>
                                    {{ $restaurant->tax_name ?? 'Taxe' }} ({{ number_format($restaurant->tax_rate ?? 0, 2) }}%)
                                </span>
                                <span class="font-semibold">{{ number_format($this->taxAmount, 0, ',', ' ') }} F</span>
                            </div>
                        @endif
                        @if($this->serviceFee > 0)
                            <div class="flex justify-between items-center text-neutral-700">
                                <span class="flex items-center gap-2">
                                    <svg class="w-4 h-4 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Frais de service
                                </span>
                                <span class="font-semibold">{{ number_format($this->serviceFee, 0, ',', ' ') }} F</span>
                            </div>
                        @endif
                        <div class="flex justify-between items-center text-xl font-bold text-neutral-900 pt-4 border-t-2 border-neutral-300">
                            <span>Total</span>
                            <span class="text-primary-600">{{ number_format($this->total, 0, ',', ' ') }} F</span>
                        </div>
                    </div>

                    <!-- Submit -->
                    <button wire:click="placeOrder" 
                            wire:loading.attr="disabled"
                            class="w-full mt-6 btn-primary py-4 disabled:opacity-50">
                        <span wire:loading.remove wire:target="placeOrder">
                            Passer la commande
                        </span>
                        <span wire:loading wire:target="placeOrder" class="flex items-center justify-center gap-2">
                            <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Traitement...
                        </span>
                    </button>

                    <p class="text-xs text-neutral-500 text-center mt-4">
                        En passant commande, vous acceptez nos 
                        <a href="{{ route('terms') }}" class="text-primary-500 hover:underline">conditions</a>.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

