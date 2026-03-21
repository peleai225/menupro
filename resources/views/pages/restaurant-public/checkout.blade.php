@php
    $restaurant = (object) [
        'name' => 'Le Délice',
        'slug' => $slug ?? 'le-delice',
        'logo_path' => null,
    ];
@endphp

<x-layouts.app :title="'Commander - ' . $restaurant->name">
    <div class="min-h-screen bg-neutral-50 py-8">
        <div class="max-w-3xl mx-auto px-4">
            <!-- Header -->
            <div class="flex items-center gap-4 mb-8">
                <a href="{{ route('r.menu', $restaurant->slug) }}" class="p-2 rounded-lg bg-white shadow hover:bg-neutral-50 transition-colors">
                    <svg class="w-6 h-6 text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <div>
                    <h1 class="text-2xl font-bold text-neutral-900">Finaliser la commande</h1>
                    <p class="text-neutral-500">{{ $restaurant->name }}</p>
                </div>
            </div>

            <div x-data="cart()" class="grid lg:grid-cols-5 gap-8">
                <!-- Form -->
                <div class="lg:col-span-3">
                    <form action="{{ route('r.checkout', $restaurant->slug) }}" method="POST" class="space-y-6">
                        @csrf
                        
                        <!-- Order Type -->
                        <x-card>
                            <h3 class="font-semibold text-neutral-900 mb-4">Type de commande</h3>
                            <div x-data="{ orderType: 'delivery' }" class="grid grid-cols-2 gap-4">
                                <label class="cursor-pointer">
                                    <input type="radio" name="order_type" value="delivery" x-model="orderType" class="hidden">
                                    <div :class="orderType === 'delivery' ? 'border-primary-500 bg-primary-50' : 'border-neutral-200'" 
                                         class="p-4 rounded-xl border-2 text-center transition-all">
                                        <svg class="w-8 h-8 mx-auto mb-2" :class="orderType === 'delivery' ? 'text-primary-500' : 'text-neutral-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        <span class="font-medium" :class="orderType === 'delivery' ? 'text-primary-700' : 'text-neutral-600'">Livraison</span>
                                    </div>
                                </label>
                                <label class="cursor-pointer">
                                    <input type="radio" name="order_type" value="onsite" x-model="orderType" class="hidden">
                                    <div :class="orderType === 'onsite' ? 'border-primary-500 bg-primary-50' : 'border-neutral-200'" 
                                         class="p-4 rounded-xl border-2 text-center transition-all">
                                        <svg class="w-8 h-8 mx-auto mb-2" :class="orderType === 'onsite' ? 'text-primary-500' : 'text-neutral-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                        </svg>
                                        <span class="font-medium" :class="orderType === 'onsite' ? 'text-primary-700' : 'text-neutral-600'">Sur place</span>
                                    </div>
                                </label>
                            </div>
                        </x-card>

                        <!-- Contact Info -->
                        <x-card>
                            <h3 class="font-semibold text-neutral-900 mb-4">Vos coordonnées</h3>
                            <div class="space-y-4">
                                <x-input 
                                    type="text" 
                                    name="client_name" 
                                    label="Votre nom" 
                                    placeholder="Jean Kouassi"
                                    required
                                />
                                <x-input 
                                    type="tel" 
                                    name="client_phone" 
                                    label="Numéro de téléphone" 
                                    placeholder="+225 07 00 00 00 00"
                                    required
                                />
                            </div>
                        </x-card>

                        <!-- Delivery Address (conditional) -->
                        <x-card x-show="orderType === 'delivery'" x-transition>
                            <h3 class="font-semibold text-neutral-900 mb-4">Adresse de livraison</h3>
                            <div class="space-y-4">
                                <x-input 
                                    type="text" 
                                    name="delivery_address" 
                                    label="Adresse complète" 
                                    placeholder="Cocody, Riviera 3, près de la pharmacie..."
                                />
                                <x-input 
                                    type="text" 
                                    name="delivery_instructions" 
                                    label="Instructions (optionnel)" 
                                    placeholder="Bâtiment bleu, 2e étage, appartement 5..."
                                />
                            </div>
                        </x-card>

                        <!-- On-site Info (conditional) -->
                        <x-card x-show="orderType === 'onsite'" x-transition x-cloak>
                            <h3 class="font-semibold text-neutral-900 mb-4">Informations sur place</h3>
                            <div class="grid grid-cols-2 gap-4">
                                <x-input 
                                    type="number" 
                                    name="table_number" 
                                    label="Numéro de table" 
                                    placeholder="Ex: 5"
                                    min="1"
                                />
                                <x-input 
                                    type="number" 
                                    name="guests_count" 
                                    label="Nombre de personnes" 
                                    placeholder="Ex: 2"
                                    min="1"
                                />
                            </div>
                        </x-card>

                        <!-- Notes -->
                        <x-card>
                            <h3 class="font-semibold text-neutral-900 mb-4">Notes (optionnel)</h3>
                            <textarea 
                                name="notes" 
                                rows="3" 
                                class="input" 
                                placeholder="Allergies, préférences, instructions spéciales..."
                            ></textarea>
                        </x-card>

                        <!-- Submit (mobile) -->
                        <div class="lg:hidden">
                            <button type="submit" class="btn btn-primary w-full btn-lg">
                                Payer <span x-text="formatCurrency(total)"></span>
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                                </svg>
                            </button>
                            <p class="text-center text-sm text-neutral-500 mt-3">
                                Paiement sécurisé par Mobile Money
                            </p>
                        </div>
                    </form>
                </div>

                <!-- Order Summary (desktop) -->
                <div class="lg:col-span-2 hidden lg:block">
                    <div class="sticky top-8">
                        <x-card>
                            <h3 class="font-semibold text-neutral-900 mb-4">Récapitulatif</h3>
                            
                            <!-- Items -->
                            <div class="space-y-3 max-h-64 overflow-y-auto mb-4">
                                <template x-for="item in items" :key="item.id">
                                    <div class="flex items-center justify-between text-sm">
                                        <div class="flex items-center gap-2">
                                            <span class="w-6 h-6 bg-neutral-100 rounded flex items-center justify-center text-xs font-medium" x-text="item.quantity"></span>
                                            <span class="text-neutral-700" x-text="item.name"></span>
                                        </div>
                                        <span class="text-neutral-900 font-medium" x-text="formatCurrency(item.price * item.quantity)"></span>
                                    </div>
                                </template>
                            </div>

                            <hr class="border-neutral-200 my-4">

                            <!-- Subtotal -->
                            <div class="flex items-center justify-between text-sm mb-2">
                                <span class="text-neutral-500">Sous-total</span>
                                <span class="text-neutral-700" x-text="formatCurrency(total)"></span>
                            </div>

                            <!-- Delivery Fee -->
                            <div class="flex items-center justify-between text-sm mb-4" x-show="orderType === 'delivery'">
                                <span class="text-neutral-500">Livraison</span>
                                <span class="text-neutral-700">1 000 FCFA</span>
                            </div>

                            <hr class="border-neutral-200 my-4">

                            <!-- Total -->
                            <div class="flex items-center justify-between">
                                <span class="font-semibold text-neutral-900">Total</span>
                                <span class="text-2xl font-bold text-primary-500" x-text="formatCurrency(total + (orderType === 'delivery' ? 1000 : 0))"></span>
                            </div>

                            <button type="submit" form="checkout-form" class="btn btn-primary w-full mt-6">
                                Payer maintenant
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                                </svg>
                            </button>

                            <div class="flex items-center justify-center gap-2 mt-4 text-sm text-neutral-500">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                                Paiement sécurisé
                            </div>

                            <!-- Payment Methods -->
                            <div class="flex items-center justify-center gap-3 mt-4">
                                <img src="{{ asset('images/payments/orange-money.png') }}" alt="Orange Money" class="h-6 w-6 rounded opacity-80">
                                <img src="{{ asset('images/payments/mtn-momo.png') }}" alt="MTN MoMo" class="h-6 w-6 rounded opacity-80">
                                <img src="{{ asset('images/payments/wave.png') }}" alt="Wave" class="h-6 w-6 rounded opacity-80">
                                <img src="{{ asset('images/payments/moov-money.png') }}" alt="Moov Money" class="h-6 w-6 rounded opacity-80">
                            </div>
                        </x-card>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>

