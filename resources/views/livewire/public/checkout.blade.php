@push('head')
<link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" integrity="sha256-p4NxAoJBhIIN+hmNHrzRCf9tD/miZyoHS5obTRR9BMY=" crossorigin=""/>
<script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js" integrity="sha256-20nQCchB9co0qIjJZRGuk2/Z9VM+kNiyxNV1lvTlZBo=" crossorigin=""></script>
@endpush

@php
    $primaryColor = $restaurant->primary_color ?? '#f97316';
@endphp

<div class="min-h-screen bg-neutral-50">
    <!-- Header compact avec branding restaurant -->
    <div class="bg-white/95 backdrop-blur-md border-b border-neutral-200 shadow-sm sticky top-0 z-10">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
            <div class="flex items-center gap-4">
                <a href="{{ route('r.menu', $restaurant->slug) }}" class="flex items-center gap-2 text-neutral-500 hover:text-neutral-700 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    <span class="hidden sm:inline">Retour au menu</span>
                </a>
                <div class="flex-1 flex items-center gap-3 min-w-0">
                    @if($restaurant->logo_path)
                        <img src="{{ $restaurant->logo_url }}" alt="{{ $restaurant->name }}" class="w-10 h-10 rounded-lg object-cover flex-shrink-0">
                    @else
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0 text-white font-bold text-sm" style="background-color: {{ $primaryColor }};">
                            {{ strtoupper(substr($restaurant->name, 0, 1)) }}
                        </div>
                    @endif
                    <div class="min-w-0">
                        <h1 class="text-lg font-bold text-neutral-900 truncate">{{ $restaurant->name }}</h1>
                        <p class="text-sm text-neutral-500">Finaliser la commande</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-8">

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
                                class="p-4 rounded-xl border-2 transition-all text-center {{ $order_type === 'dine_in' ? '' : 'border-neutral-200 hover:border-neutral-300' }}"
                                @if($order_type === 'dine_in') style="border-color: {{ $primaryColor }}; background-color: {{ $primaryColor }}15;" @endif>
                            <svg class="w-8 h-8 mx-auto mb-2 {{ $order_type === 'dine_in' ? '' : 'text-neutral-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" @if($order_type === 'dine_in') style="color: {{ $primaryColor }};" @endif>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            <span class="font-medium {{ $order_type === 'dine_in' ? '' : 'text-neutral-700' }}" @if($order_type === 'dine_in') style="color: {{ $primaryColor }};" @endif>Sur place</span>
                        </button>
                        <button type="button"
                                wire:click="$set('order_type', 'takeaway')"
                                class="p-4 rounded-xl border-2 transition-all text-center {{ $order_type === 'takeaway' ? '' : 'border-neutral-200 hover:border-neutral-300' }}"
                                @if($order_type === 'takeaway') style="border-color: {{ $primaryColor }}; background-color: {{ $primaryColor }}15;" @endif>
                            <svg class="w-8 h-8 mx-auto mb-2 {{ $order_type === 'takeaway' ? '' : 'text-neutral-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" @if($order_type === 'takeaway') style="color: {{ $primaryColor }};" @endif>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                            </svg>
                            <span class="font-medium {{ $order_type === 'takeaway' ? '' : 'text-neutral-700' }}" @if($order_type === 'takeaway') style="color: {{ $primaryColor }};" @endif>À emporter</span>
                        </button>
                        <button type="button"
                                wire:click="$set('order_type', 'delivery')"
                                class="p-4 rounded-xl border-2 transition-all text-center {{ $order_type === 'delivery' ? '' : 'border-neutral-200 hover:border-neutral-300' }}"
                                @if($order_type === 'delivery') style="border-color: {{ $primaryColor }}; background-color: {{ $primaryColor }}15;" @endif>
                            <svg class="w-8 h-8 mx-auto mb-2 {{ $order_type === 'delivery' ? '' : 'text-neutral-400' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24" @if($order_type === 'delivery') style="color: {{ $primaryColor }};" @endif>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
                            </svg>
                            <span class="font-medium {{ $order_type === 'delivery' ? '' : 'text-neutral-700' }}" @if($order_type === 'delivery') style="color: {{ $primaryColor }};" @endif>Livraison</span>
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
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 mb-2">Téléphone *</label>
                                <div class="flex gap-2">
                                    <select wire:model="customer_phone_country"
                                            class="w-44 px-3 py-3 border border-neutral-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('customer_phone') border-red-500 @enderror bg-white">
                                        @foreach(\App\Livewire\Public\Checkout::phoneCountryOptions() as $code => $label)
                                            <option value="{{ $code }}">{{ $label }}</option>
                                        @endforeach
                                    </select>
                                    <input type="tel"
                                           wire:model="customer_phone"
                                           class="flex-1 px-4 py-3 border border-neutral-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('customer_phone') border-red-500 @enderror"
                                           placeholder="05 01 86 26 40 (sans indicatif)">
                                </div>
                                <p class="mt-1 text-xs text-neutral-500">Saisissez le numéro sans indicatif pays (ex: 05 01 86 26 40)</p>
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
                        @if($table_number && request()->query('table'))
                            {{-- Auto-detected from QR code --}}
                            <div class="flex items-center gap-3 p-4 bg-emerald-50 border border-emerald-200 rounded-xl">
                                <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center flex-shrink-0">
                                    <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="text-sm font-bold text-emerald-800">Table {{ $table_number }}</p>
                                    <p class="text-xs text-emerald-600">Détectée automatiquement via QR code</p>
                                </div>
                            </div>
                            <input type="hidden" wire:model="table_number">
                        @else
                            <input type="text"
                                   wire:model="table_number"
                                   class="w-full px-4 py-3 border border-neutral-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('table_number') border-red-500 @enderror"
                                   placeholder="Ex: Table 5">
                        @endif
                        @error('table_number')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                @endif

                <!-- Delivery Address (Livraison) — Style Glovo -->
                @if($order_type === 'delivery')
                    <div class="bg-white rounded-2xl p-6 shadow-sm"
                         x-data="deliveryAddress()"
                         x-init="init()"
                         wire:ignore.self>
                        <h2 class="text-lg font-bold text-neutral-900 mb-4">Adresse de livraison</h2>

                        {{-- Étape 1 : Choisir la ville --}}
                        <div class="mb-4">
                            <label class="block text-sm font-medium text-neutral-700 mb-2">Ville de livraison *</label>
                            <div class="relative">
                                <select x-ref="citySelect" x-model="selectedCityId" @change="onCityChange()"
                                        class="w-full px-4 py-3 border border-neutral-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent appearance-none bg-white">
                                    <option value="">-- Choisir votre ville --</option>
                                    <template x-for="city in cities" :key="city.id">
                                        <option :value="city.id" x-text="city.name"></option>
                                    </template>
                                </select>
                                <svg class="absolute right-4 top-1/2 -translate-y-1/2 w-5 h-5 text-neutral-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </div>
                            <p x-show="cities.length === 0 && !loadingCities" x-cloak class="mt-2 text-sm text-red-500">Aucune ville de livraison disponible.</p>
                            <p x-show="loadingCities" class="mt-2 text-sm text-neutral-400">Chargement des villes...</p>
                        </div>

                        {{-- Étape 2 : Carte interactive (cliquable) --}}
                        <div x-show="selectedCityId" x-cloak class="mb-4">
                            <p class="text-sm text-neutral-500 mb-2">Cliquez sur la carte pour indiquer votre position exacte :</p>
                            <div x-ref="mapContainer" class="w-full h-56 rounded-xl overflow-hidden border border-neutral-200 z-0 cursor-crosshair"></div>
                            <p x-show="pinPlaced" x-cloak class="mt-2 text-sm text-emerald-600 flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                <span>Position confirmée</span>
                            </p>
                        </div>

                        {{-- Ou rechercher une adresse --}}
                        <div x-show="selectedCityId" x-cloak class="mb-4 relative">
                            <div class="relative flex items-center mb-2">
                                <div class="flex-1 border-t border-neutral-200"></div>
                                <span class="px-3 text-xs text-neutral-400 uppercase">ou rechercher</span>
                                <div class="flex-1 border-t border-neutral-200"></div>
                            </div>
                            <div class="relative">
                                <input type="text"
                                       x-ref="searchInput"
                                       @input.debounce.500ms="searchAddresses($event.target.value)"
                                       autocomplete="off"
                                       class="w-full px-4 py-3 pr-12 border border-neutral-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                       placeholder="Quartier, rue, repere...">
                                <svg class="absolute right-4 top-1/2 -translate-y-1/2 w-5 h-5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>

                            {{-- Autocomplete dropdown --}}
                            <div x-show="showResults" x-cloak @click.outside="showResults = false"
                                 class="absolute z-50 w-full mt-1 bg-white border border-neutral-200 rounded-xl shadow-lg max-h-60 overflow-y-auto" style="top: 100%; left: 0;">
                                <template x-for="(item, idx) in results" :key="idx">
                                    <div @click="selectAddress(item)" class="px-4 py-3 hover:bg-neutral-50 cursor-pointer border-b border-neutral-100 last:border-b-0">
                                        <div class="flex items-start gap-3">
                                            <svg class="w-5 h-5 text-primary-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            </svg>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-sm font-medium text-neutral-900" x-text="item.display_name.split(',')[0]"></p>
                                                <p class="text-xs text-neutral-500 mt-0.5" x-text="item.display_name.split(',').slice(1,3).join(', ')"></p>
                                            </div>
                                        </div>
                                    </div>
                                </template>
                                <div x-show="searching" class="px-4 py-3 text-sm text-neutral-500 text-center">Recherche...</div>
                                <div x-show="!searching && results.length === 0 && searchDone" class="px-4 py-3 text-sm text-neutral-500 text-center">Aucune adresse trouvée</div>
                            </div>
                        </div>

                        {{-- GPS mobile --}}
                        <div x-show="selectedCityId" x-cloak class="mb-4">
                            <button type="button" @click="useMyLocation()"
                                    class="inline-flex items-center gap-2 text-sm text-primary-600 hover:text-primary-800 transition-colors">
                                <svg x-show="!locating" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-2.21 0-4 1.79-4 4s1.79 4 4 4 4-1.79 4-4-1.79-4-4-4zm8.94 3A8.994 8.994 0 0013 3.06V1h-2v2.06A8.994 8.994 0 003.06 11H1v2h2.06A8.994 8.994 0 0011 20.94V23h2v-2.06A8.994 8.994 0 0020.94 13H23v-2h-2.06zM12 19c-3.87 0-7-3.13-7-7s3.13-7 7-7 7 3.13 7 7-3.13 7-7 7z"/>
                                </svg>
                                <svg x-show="locating" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                <span x-text="locating ? 'Localisation GPS...' : 'Utiliser mon GPS (mobile)'"></span>
                            </button>
                        </div>

                        {{-- Status / Error --}}
                        <div x-show="statusMsg" x-cloak class="mb-4 text-sm text-emerald-600 flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            <span x-text="statusMsg"></span>
                        </div>
                        <div x-show="errorMsg" x-cloak class="mb-4 text-sm text-red-600" x-text="errorMsg"></div>

                        {{-- Champs de détail --}}
                        {{-- Champs cachés pour sync Livewire --}}
                        <input type="hidden" wire:model="delivery_city" x-bind:value="cities.find(c => c.id.toString() === selectedCityId)?.name || ''">
                        <input type="hidden" wire:model="delivery_latitude">
                        <input type="hidden" wire:model="delivery_longitude">

                        <div x-show="selectedCityId" x-cloak class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 mb-2">Adresse / Quartier *</label>
                                <input type="text"
                                       wire:model="delivery_address"
                                       x-ref="addressInput"
                                       class="w-full px-4 py-3 border border-neutral-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('delivery_address') border-red-500 @enderror"
                                       placeholder="Quartier, rue, repere...">
                                @error('delivery_address')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 mb-2">Instructions pour le livreur (optionnel)</label>
                                <textarea wire:model="delivery_instructions"
                                          rows="2"
                                          class="w-full px-4 py-3 border border-neutral-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                          placeholder="Ex: Code portail 1234, 2e etage porte gauche..."></textarea>
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
                <div class="bg-white rounded-2xl p-8 shadow-sm sticky top-4 lg:max-h-[calc(100vh-2rem)] lg:overflow-y-auto">
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

                    <!-- Payment Method -->
                    @if($this->jekoPaymentAvailable || $this->cashOnDeliveryAvailable)
                        <div class="mb-6 pb-6 border-b border-neutral-200">
                            <h3 class="text-sm font-semibold text-neutral-700 mb-3">Mode de paiement</h3>
                            <div class="space-y-2">
                                @if($this->jekoPaymentAvailable)
                                    <label class="flex items-center gap-3 p-3 rounded-xl border-2 cursor-pointer transition-all {{ $payment_method === 'jeko' ? 'border-primary-500 bg-primary-50' : 'border-neutral-200 hover:border-neutral-300' }}">
                                        <input type="radio" wire:model="payment_method" value="jeko" class="text-primary-500 focus:ring-primary-500">
                                        <div class="w-8 h-8 rounded-full bg-emerald-600 flex items-center justify-center flex-shrink-0">
                                            <span class="text-white text-xs font-bold">J</span>
                                        </div>
                                        <div class="flex-1">
                                            <span class="font-medium">Jeko</span>
                                            <span class="text-xs text-neutral-500 ml-2">(Wave, Orange, MTN, Moov, Djamo, Carte)</span>
                                        </div>
                                    </label>
                                @endif
                                @if($this->cashOnDeliveryAvailable)
                                    <label class="flex items-center gap-3 p-3 rounded-xl border-2 cursor-pointer transition-all {{ $payment_method === 'cash_on_delivery' ? 'border-primary-500 bg-primary-50' : 'border-neutral-200 hover:border-neutral-300' }}">
                                        <input type="radio" wire:model="payment_method" value="cash_on_delivery" class="text-primary-500 focus:ring-primary-500">
                                        <x-payment-logo method="cash_on_delivery" />
                                        <span class="font-medium">Paiement à la caisse</span>
                                    </label>
                                @endif
                            </div>
                        </div>
                    @endif

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
                            <span style="color: {{ $primaryColor }};">{{ number_format($this->total, 0, ',', ' ') }} F</span>
                        </div>
                    </div>

                    <!-- Submit -->
                    <button wire:click="placeOrder"
                            wire:loading.attr="disabled"
                            class="w-full mt-6 py-4 text-white font-bold rounded-xl disabled:opacity-50 transition-all duration-200 hover:opacity-90 hover:-translate-y-0.5 active:translate-y-0"
                            style="background-color: {{ $primaryColor }}; box-shadow: 0 6px 20px {{ $primaryColor }}50;">
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

@script
<script>
Alpine.data('deliveryAddress', () => ({
    cities: [],
    loadingCities: true,
    selectedCityId: '',
    locating: false,
    searching: false,
    searchDone: false,
    showResults: false,
    results: [],
    statusMsg: '',
    errorMsg: '',
    pinPlaced: false,
    map: null,
    userMarker: null,
    coverageCircle: null,

    async init() {
        await this.loadCities();
        this.restoreFromStorage();
    },

    async loadCities() {
        try {
            const r = await fetch('/api/geocoding/delivery-cities', {
                headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
            });
            if (r.ok) this.cities = await r.json();
        } catch (e) {}
        this.loadingCities = false;
    },

    restoreFromStorage() {
        const savedCity = localStorage.getItem('menupro_delivery_city');
        const savedAddress = localStorage.getItem('menupro_delivery_address');
        const savedLat = localStorage.getItem('menupro_delivery_lat');
        const savedLng = localStorage.getItem('menupro_delivery_lng');

        if (savedCity && this.cities.length) {
            const match = this.cities.find(c => c.name.toLowerCase() === savedCity.toLowerCase());
            if (match) {
                this.selectedCityId = match.id.toString();
                this.$nextTick(() => {
                    this.initMap(parseFloat(match.center_latitude), parseFloat(match.center_longitude));
                    if (savedLat && savedLng) {
                        const lat = parseFloat(savedLat);
                        const lng = parseFloat(savedLng);
                        this.placePin(lat, lng, false);
                        if (!$wire.get('delivery_address')) {
                            $wire.set('delivery_address', savedAddress || '');
                            $wire.set('delivery_city', savedCity);
                            $wire.set('delivery_latitude', lat);
                            $wire.set('delivery_longitude', lng);
                        }
                        if (this.$refs.addressInput) this.$refs.addressInput.value = savedAddress || '';
                    }
                });
            }
        }
    },

    onCityChange() {
        const city = this.cities.find(c => c.id.toString() === this.selectedCityId);
        if (!city) return;

        $wire.set('delivery_city', city.name);
        localStorage.setItem('menupro_delivery_city', city.name);
        this.pinPlaced = false;
        this.statusMsg = '';
        this.errorMsg = '';

        this.$nextTick(() => {
            this.initMap(parseFloat(city.center_latitude), parseFloat(city.center_longitude));
        });
    },

    initMap(centerLat, centerLng) {
        if (typeof L === 'undefined') return;

        const container = this.$refs.mapContainer;
        if (!container) return;

        if (this.map) {
            this.map.remove();
            this.map = null;
            this.userMarker = null;
            this.coverageCircle = null;
        }

        this.map = L.map(container, { zoomControl: true, attributionControl: false }).setView([centerLat, centerLng], 13);
        L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', { maxZoom: 18 }).addTo(this.map);

        const city = this.cities.find(c => c.id.toString() === this.selectedCityId);
        if (city) {
            this.coverageCircle = L.circle([centerLat, centerLng], {
                radius: city.coverage_radius_km * 1000,
                color: '#10b981', fillColor: '#10b98120', fillOpacity: 0.1, weight: 1, dashArray: '5,5'
            }).addTo(this.map);
        }

        this.map.on('click', (e) => {
            this.placePin(e.latlng.lat, e.latlng.lng, true);
        });
    },

    placePin(lat, lng, doReverse) {
        if (!this.map) return;

        if (this.userMarker) this.map.removeLayer(this.userMarker);
        this.userMarker = L.marker([lat, lng], {
            icon: L.divIcon({
                className: '',
                html: '<div style="background:#3b82f6;width:16px;height:16px;border-radius:50%;border:3px solid white;box-shadow:0 2px 6px rgba(0,0,0,.4)"></div>',
                iconSize: [16, 16], iconAnchor: [8, 8]
            }),
            draggable: true
        }).addTo(this.map);

        this.userMarker.on('dragend', (e) => {
            const pos = e.target.getLatLng();
            this.commitPosition(pos.lat, pos.lng, true);
        });

        this.map.setView([lat, lng], 15);
        this.commitPosition(lat, lng, doReverse);
    },

    async commitPosition(lat, lng, doReverse) {
        this.pinPlaced = true;
        this.errorMsg = '';
        $wire.set('delivery_latitude', lat);
        $wire.set('delivery_longitude', lng);
        localStorage.setItem('menupro_delivery_lat', lat.toString());
        localStorage.setItem('menupro_delivery_lng', lng.toString());

        if (doReverse) {
            try {
                const r = await fetch(`/api/geocoding/reverse?lat=${lat}&lon=${lng}`, {
                    headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                });
                if (r.ok) {
                    const data = await r.json();
                    const addr = data.address || '';
                    if (addr && this.$refs.addressInput) {
                        this.$refs.addressInput.value = addr;
                        $wire.set('delivery_address', addr);
                        localStorage.setItem('menupro_delivery_address', addr);
                    }
                }
            } catch (e) {}
        }
    },

    async searchAddresses(query) {
        query = query.trim();
        if (query.length < 3) { this.showResults = false; this.results = []; return; }
        this.searching = true;
        this.searchDone = false;
        this.showResults = true;
        this.results = [];

        const city = this.cities.find(c => c.id.toString() === this.selectedCityId);
        try {
            let url = `/api/geocoding/search?q=${encodeURIComponent(query)}`;
            if (city) url += `&lat=${city.center_latitude}&lon=${city.center_longitude}`;
            const r = await fetch(url, { headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } });
            if (r.ok) this.results = await r.json() || [];
        } catch (e) { this.results = []; }
        this.searching = false;
        this.searchDone = true;
    },

    selectAddress(item) {
        const lat = parseFloat(item.lat);
        const lng = parseFloat(item.lon || item.lng);
        this.showResults = false;
        this.placePin(lat, lng, false);

        const address = item.address || {};
        const street = address.road || address.pedestrian || item.display_name.split(',')[0];
        const houseNumber = address.house_number || '';
        const finalAddress = houseNumber ? `${houseNumber} ${street}`.trim() : street;

        if (this.$refs.searchInput) this.$refs.searchInput.value = item.display_name;
        if (this.$refs.addressInput) this.$refs.addressInput.value = finalAddress;

        $wire.set('delivery_address', finalAddress);
        $wire.set('delivery_latitude', lat);
        $wire.set('delivery_longitude', lng);

        localStorage.setItem('menupro_delivery_address', finalAddress);
        localStorage.setItem('menupro_delivery_lat', lat.toString());
        localStorage.setItem('menupro_delivery_lng', lng.toString());
    },

    async useMyLocation() {
        if (this.locating) return;
        if (!navigator.geolocation) { this.errorMsg = 'Géolocalisation non supportée.'; return; }
        this.locating = true;
        this.errorMsg = '';

        navigator.geolocation.getCurrentPosition(
            async (position) => {
                const lat = position.coords.latitude;
                const lng = position.coords.longitude;
                const accuracy = position.coords.accuracy;

                if (accuracy > 5000) {
                    this.errorMsg = 'Position GPS imprécise (' + Math.round(accuracy / 1000) + ' km de marge). Cliquez sur la carte pour indiquer votre position.';
                    this.locating = false;
                    return;
                }

                this.placePin(lat, lng, true);
                this.locating = false;
            },
            (error) => {
                if (error.code === 1) this.errorMsg = 'Position bloquée par le navigateur. Cliquez sur la carte.';
                else this.errorMsg = 'GPS indisponible. Cliquez sur la carte pour indiquer votre position.';
                this.locating = false;
            },
            { enableHighAccuracy: true, timeout: 15000, maximumAge: 0 }
        );
    }
}));
</script>
@endscript
</div>

