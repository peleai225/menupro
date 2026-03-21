<div class="p-4 lg:p-6" x-data="{ showCart: window.innerWidth >= 1024 }">
    {{-- Header --}}
    <div class="flex items-center justify-between mb-6">
        <div>
            <h1 class="text-2xl font-bold text-neutral-900">Mode Caisse (POS)</h1>
            <p class="text-neutral-500 text-sm">Créez une commande pour un client</p>
        </div>
        <div class="flex items-center gap-3">
            {{-- Cart toggle mobile --}}
            <button @click="showCart = !showCart" class="lg:hidden relative btn btn-primary">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>
                </svg>
                @if($this->cartItemsCount > 0)
                    <span class="absolute -top-2 -right-2 bg-accent-500 text-white text-xs w-5 h-5 rounded-full flex items-center justify-center font-bold">{{ $this->cartItemsCount }}</span>
                @endif
            </button>
        </div>
    </div>

    {{-- Success message --}}
    @if(session('pos_success'))
        <div class="mb-6 p-4 bg-secondary-50 border border-secondary-200 rounded-xl text-secondary-800 flex items-center gap-3">
            <svg class="w-5 h-5 text-secondary-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span class="font-medium">{{ session('pos_success') }}</span>
            @if($lastOrder)
                <span class="ml-auto text-sm text-secondary-600">Réf: {{ $lastOrder->reference }}</span>
            @endif
        </div>
    @endif

    @error('cart') <div class="mb-4 text-red-600 text-sm font-medium">{{ $message }}</div> @enderror
    @error('submit') <div class="mb-4 text-red-600 text-sm font-medium">{{ $message }}</div> @enderror

    <div class="grid lg:grid-cols-3 gap-6">
        {{-- ═══════════════════════════════════════════ --}}
        {{-- LEFT: Menu & Dishes (2 cols) --}}
        {{-- ═══════════════════════════════════════════ --}}
        <div class="lg:col-span-2 space-y-4">

            {{-- Search --}}
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" wire:model.live.debounce.300ms="searchDish" placeholder="Rechercher un plat..."
                       class="input pl-10">
            </div>

            {{-- Categories filter --}}
            <div class="flex flex-wrap gap-2">
                <button wire:click="selectCategory(null)"
                        class="px-4 py-2 rounded-full text-sm font-medium transition-colors {{ !$selectedCategory ? 'bg-primary-500 text-white' : 'bg-white text-neutral-700 border border-neutral-200 hover:bg-neutral-50' }}">
                    Tous
                </button>
                @foreach($this->categories as $cat)
                    <button wire:click="selectCategory({{ $cat->id }})"
                            class="px-4 py-2 rounded-full text-sm font-medium transition-colors {{ $selectedCategory === $cat->id ? 'bg-primary-500 text-white' : 'bg-white text-neutral-700 border border-neutral-200 hover:bg-neutral-50' }}">
                        {{ $cat->name }}
                    </button>
                @endforeach
            </div>

            {{-- Dishes grid --}}
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-3">
                @forelse($this->dishes as $dish)
                    <button wire:click="addToCart({{ $dish->id }})"
                            wire:key="dish-{{ $dish->id }}"
                            class="bg-white rounded-xl border border-neutral-200 p-3 text-left hover:border-primary-300 hover:shadow-md transition-all group">
                        @if($dish->image_url)
                            <div class="aspect-square rounded-lg overflow-hidden mb-2 bg-neutral-100">
                                <img src="{{ $dish->image_url }}" alt="{{ $dish->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform">
                            </div>
                        @else
                            <div class="aspect-square rounded-lg mb-2 bg-neutral-100 flex items-center justify-center">
                                <svg class="w-8 h-8 text-neutral-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                            </div>
                        @endif
                        <h3 class="font-semibold text-neutral-900 text-sm leading-tight line-clamp-2">{{ $dish->name }}</h3>
                        <p class="text-primary-600 font-bold text-sm mt-1">{{ number_format($dish->price, 0, ',', ' ') }} F</p>
                        @if($dish->category)
                            <p class="text-neutral-400 text-xs mt-0.5">{{ $dish->category->name }}</p>
                        @endif
                    </button>
                @empty
                    <div class="col-span-full text-center py-12 text-neutral-500">
                        <svg class="w-12 h-12 mx-auto mb-3 text-neutral-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                        </svg>
                        <p>Aucun plat trouvé</p>
                    </div>
                @endforelse
            </div>
        </div>

        {{-- ═══════════════════════════════════════════ --}}
        {{-- RIGHT: Cart & Customer (1 col) --}}
        {{-- ═══════════════════════════════════════════ --}}
        <div x-show="showCart" x-transition
             class="fixed inset-0 z-50 bg-white lg:relative lg:inset-auto lg:z-auto lg:bg-transparent overflow-y-auto lg:overflow-visible">

            {{-- Mobile close button --}}
            <div class="lg:hidden flex items-center justify-between p-4 border-b">
                <h2 class="font-bold text-lg">Panier</h2>
                <button @click="showCart = false" class="p-2 hover:bg-neutral-100 rounded-lg">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="p-4 lg:p-0 space-y-4 lg:sticky lg:top-4">
                {{-- Customer info --}}
                <div class="bg-white rounded-xl border border-neutral-200 p-4 space-y-3">
                    <h3 class="font-semibold text-neutral-900 flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        Client
                    </h3>

                    <div>
                        <input type="text" wire:model="customerName" placeholder="Nom du client *" class="input text-sm">
                        @error('customerName') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>
                    <input type="tel" wire:model="customerPhone" placeholder="Téléphone (optionnel)" class="input text-sm">
                    <input type="email" wire:model="customerEmail" placeholder="Email (optionnel)" class="input text-sm">

                    {{-- Order type --}}
                    <div class="grid grid-cols-3 gap-2">
                        @foreach([
                            'dine_in' => ['label' => 'Sur place', 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
                            'takeaway' => ['label' => 'Emporter', 'icon' => 'M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z'],
                            'delivery' => ['label' => 'Livraison', 'icon' => 'M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0'],
                        ] as $type => $info)
                            <button wire:click="$set('orderType', '{{ $type }}')" type="button"
                                    class="flex flex-col items-center gap-1 p-2 rounded-lg border-2 text-xs font-medium transition-colors {{ $orderType === $type ? 'border-primary-500 bg-primary-50 text-primary-700' : 'border-neutral-200 text-neutral-600 hover:border-neutral-300' }}">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $info['icon'] }}"/>
                                </svg>
                                {{ $info['label'] }}
                            </button>
                        @endforeach
                    </div>

                    @if($orderType === 'dine_in')
                        <div>
                            <input type="text" wire:model="tableNumber" placeholder="N° Table *" class="input text-sm">
                            @error('tableNumber') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                        </div>
                    @endif

                    <textarea wire:model="customerNotes" placeholder="Notes (optionnel)" rows="2" class="input text-sm resize-none"></textarea>
                </div>

                {{-- Cart items --}}
                <div class="bg-white rounded-xl border border-neutral-200 p-4">
                    <h3 class="font-semibold text-neutral-900 mb-3 flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>
                        </svg>
                        Panier
                        @if($this->cartItemsCount > 0)
                            <span class="text-xs bg-primary-100 text-primary-700 px-2 py-0.5 rounded-full">{{ $this->cartItemsCount }}</span>
                        @endif
                    </h3>

                    @if(empty($cart))
                        <div class="text-center py-8 text-neutral-400">
                            <svg class="w-10 h-10 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 100 4 2 2 0 000-4z"/>
                            </svg>
                            <p class="text-sm">Cliquez sur un plat pour l'ajouter</p>
                        </div>
                    @else
                        <div class="space-y-2 max-h-64 overflow-y-auto">
                            @foreach($cart as $key => $item)
                                <div class="flex items-center gap-2 p-2 bg-neutral-50 rounded-lg" wire:key="cart-{{ $key }}">
                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium text-sm text-neutral-900 truncate">{{ $item['dish_name'] }}</p>
                                        <p class="text-xs text-primary-600 font-semibold">{{ number_format($item['unit_price'], 0, ',', ' ') }} F</p>
                                    </div>
                                    <div class="flex items-center gap-1">
                                        <button wire:click="decrementItem('{{ $key }}')" class="w-7 h-7 flex items-center justify-center rounded-lg bg-neutral-200 hover:bg-neutral-300 text-neutral-700 text-sm font-bold">−</button>
                                        <span class="w-7 text-center text-sm font-semibold">{{ $item['quantity'] }}</span>
                                        <button wire:click="incrementItem('{{ $key }}')" class="w-7 h-7 flex items-center justify-center rounded-lg bg-primary-100 hover:bg-primary-200 text-primary-700 text-sm font-bold">+</button>
                                    </div>
                                    <span class="text-sm font-bold text-neutral-900 w-20 text-right">{{ number_format($item['unit_price'] * $item['quantity'], 0, ',', ' ') }} F</span>
                                    <button wire:click="removeFromCart('{{ $key }}')" class="text-red-400 hover:text-red-600 p-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                        </svg>
                                    </button>
                                </div>
                            @endforeach
                        </div>

                        {{-- Totals --}}
                        <div class="mt-4 pt-3 border-t border-neutral-200 space-y-1">
                            <div class="flex justify-between text-sm text-neutral-600">
                                <span>Sous-total</span>
                                <span>{{ number_format($this->cartSubtotal, 0, ',', ' ') }} F</span>
                            </div>
                            @if($this->cartTax > 0)
                                <div class="flex justify-between text-sm text-neutral-600">
                                    <span>Taxes</span>
                                    <span>{{ number_format($this->cartTax, 0, ',', ' ') }} F</span>
                                </div>
                            @endif
                            @if($this->cartServiceFee > 0)
                                <div class="flex justify-between text-sm text-neutral-600">
                                    <span>Frais de service</span>
                                    <span>{{ number_format($this->cartServiceFee, 0, ',', ' ') }} F</span>
                                </div>
                            @endif
                            <div class="flex justify-between text-lg font-bold text-neutral-900 pt-2 border-t border-neutral-200">
                                <span>Total</span>
                                <span class="text-primary-600">{{ number_format($this->cartTotal, 0, ',', ' ') }} F</span>
                            </div>
                        </div>

                        {{-- Payment method --}}
                        <div class="mt-4 space-y-2">
                            <label class="text-sm font-medium text-neutral-700">Paiement</label>
                            <div class="grid grid-cols-2 gap-2">
                                <button wire:click="$set('paymentMethod', 'cash')" type="button"
                                        class="flex items-center justify-center gap-2 p-2.5 rounded-lg border-2 text-sm font-medium transition-colors {{ $paymentMethod === 'cash' ? 'border-primary-500 bg-primary-50 text-primary-700' : 'border-neutral-200 text-neutral-600' }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                    Espèces
                                </button>
                                <button wire:click="$set('paymentMethod', 'mobile_money')" type="button"
                                        class="flex items-center justify-center gap-2 p-2.5 rounded-lg border-2 text-sm font-medium transition-colors {{ $paymentMethod === 'mobile_money' ? 'border-primary-500 bg-primary-50 text-primary-700' : 'border-neutral-200 text-neutral-600' }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                                    </svg>
                                    Mobile Money
                                </button>
                            </div>
                        </div>

                        {{-- Action buttons --}}
                        <div class="mt-4 space-y-2">
                            <button wire:click="confirmOrder" wire:loading.attr="disabled"
                                    class="w-full btn btn-primary py-3 text-base font-bold">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Valider la commande
                            </button>
                            <button wire:click="clearCart" class="w-full btn btn-outline text-sm">
                                Vider le panier
                            </button>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    {{-- ═══════════════════════════════════════════ --}}
    {{-- Confirmation Modal --}}
    {{-- ═══════════════════════════════════════════ --}}
    @if($showConfirmModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center bg-black/50" wire:click.self="$set('showConfirmModal', false)">
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full mx-4 p-6">
                <h3 class="text-xl font-bold text-neutral-900 mb-4">Confirmer la commande</h3>

                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-neutral-600">Client</span>
                        <span class="font-semibold">{{ $customerName }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-neutral-600">Type</span>
                        <span class="font-semibold">{{ $orderType === 'dine_in' ? 'Sur place' : ($orderType === 'takeaway' ? 'À emporter' : 'Livraison') }}</span>
                    </div>
                    @if($orderType === 'dine_in' && $tableNumber)
                        <div class="flex justify-between">
                            <span class="text-neutral-600">Table</span>
                            <span class="font-semibold">#{{ $tableNumber }}</span>
                        </div>
                    @endif
                    <div class="flex justify-between">
                        <span class="text-neutral-600">Articles</span>
                        <span class="font-semibold">{{ $this->cartItemsCount }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-neutral-600">Paiement</span>
                        <span class="font-semibold">{{ $paymentMethod === 'cash' ? 'Espèces' : 'Mobile Money' }}</span>
                    </div>

                    <div class="pt-3 border-t">
                        @foreach($cart as $item)
                            <div class="flex justify-between py-1">
                                <span class="text-neutral-600">{{ $item['quantity'] }}× {{ $item['dish_name'] }}</span>
                                <span>{{ number_format($item['unit_price'] * $item['quantity'], 0, ',', ' ') }} F</span>
                            </div>
                        @endforeach
                    </div>

                    <div class="flex justify-between text-lg font-bold pt-3 border-t">
                        <span>Total</span>
                        <span class="text-primary-600">{{ number_format($this->cartTotal, 0, ',', ' ') }} F</span>
                    </div>
                </div>

                <div class="flex gap-3 mt-6">
                    <button wire:click="$set('showConfirmModal', false)" class="flex-1 btn btn-outline">
                        Annuler
                    </button>
                    <button wire:click="submitOrder" wire:loading.attr="disabled" class="flex-1 btn btn-primary">
                        <span wire:loading.remove wire:target="submitOrder">Confirmer</span>
                        <span wire:loading wire:target="submitOrder">Création...</span>
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
