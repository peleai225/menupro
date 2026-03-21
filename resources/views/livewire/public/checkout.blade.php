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

                <!-- Delivery Address (Livraison) -->
                @if($order_type === 'delivery')
                    <div class="bg-white rounded-2xl p-6 shadow-sm">
                        <h2 class="text-lg font-bold text-neutral-900 mb-4">Adresse de livraison</h2>
                        
                        <!-- Address Search (like Glovo) -->
                        <div class="mb-4 relative">
                            <div class="relative">
                                <input type="text" 
                                       id="address-search"
                                       autocomplete="off"
                                       class="w-full px-4 py-3 pr-12 border border-neutral-200 rounded-xl focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                       placeholder="Rechercher une adresse en Côte d'Ivoire... (ex: Abidjan, Cocody, Marcory)">
                                <svg class="absolute right-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                </svg>
                            </div>
                            
                            <!-- Autocomplete dropdown -->
                            <div id="address-autocomplete" class="hidden absolute z-50 w-full mt-1 bg-white border border-neutral-200 rounded-xl shadow-lg max-h-60 overflow-y-auto" style="top: 100%; left: 0;">
                                <!-- Results will be inserted here -->
                            </div>
                            
                            <!-- Use my location button -->
                            <button type="button" 
                                    id="use-my-location"
                                    class="mt-3 w-full px-4 py-2.5 bg-primary-50 text-primary-600 rounded-xl font-medium hover:bg-primary-100 transition-colors flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                Utiliser ma position actuelle
                            </button>
                            
                            <!-- Debug info (remove in production) -->
                            <div id="address-debug" class="mt-2 text-xs text-gray-500 hidden"></div>
                        </div>
                        
                        <!-- Status message -->
                        <div id="address-status" class="mb-4 text-sm text-neutral-600 hidden"></div>
                        <div id="address-error" class="mb-4 text-sm text-red-600 hidden"></div>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 mb-2">Adresse complète *</label>
                                <input type="text" 
                                       wire:model="delivery_address"
                                       id="delivery_address_input"
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
                                       id="delivery_city_input"
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
                    
                    @push('scripts')
                    <script>
                        (function() {
                            let searchTimeout = null;
                            let currentSearchQuery = '';
                            
                            // Restaurant coordinates
                            const restaurantLat = {{ $restaurant->latitude ?? 5.3600 }};
                            const restaurantLng = {{ $restaurant->longitude ?? -4.0083 }};
                            const deliveryRadius = {{ $restaurant->delivery_radius_km ?? 10 }};
                            
                            console.log('[Address] Script loaded, restaurant:', restaurantLat, restaurantLng);
                            
                            // Function to calculate distance
                            function getDistance(lat1, lon1, lat2, lon2) {
                                const R = 6371; // Radius of the earth in km
                                const dLat = deg2rad(lat2 - lat1);
                                const dLon = deg2rad(lon2 - lon1);
                                const a = 
                                    Math.sin(dLat/2) * Math.sin(dLat/2) +
                                    Math.cos(deg2rad(lat1)) * Math.cos(deg2rad(lat2)) * 
                                    Math.sin(dLon/2) * Math.sin(dLon/2);
                                const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
                                return R * c; // Distance in km
                            }
                            
                            function deg2rad(deg) {
                                return deg * (Math.PI/180);
                            }
                            
                            // Function to validate delivery distance
                            function validateDeliveryDistance(lat, lng) {
                                const distance = getDistance(restaurantLat, restaurantLng, lat, lng);
                                const isInRadius = deliveryRadius === 0 || distance <= deliveryRadius;
                                
                                const statusEl = document.getElementById('address-status');
                                const errorEl = document.getElementById('address-error');
                                
                                if (isInRadius) {
                                    statusEl.textContent = `Distance: ${distance.toFixed(2)} km (dans la zone de livraison)`;
                                    statusEl.className = 'mb-4 text-sm text-emerald-600';
                                    statusEl.classList.remove('hidden');
                                    errorEl.classList.add('hidden');
                                    return true;
                                } else {
                                    statusEl.classList.add('hidden');
                                    errorEl.textContent = `Cette zone est hors de notre zone de livraison (max: ${deliveryRadius} km). Veuillez choisir une adresse plus proche.`;
                                    errorEl.classList.remove('hidden');
                                    return false;
                                }
                            }
                            
                            // Address autocomplete function (like Glovo)
                            async function searchAddresses(query) {
                                console.log('[Address] Searching for:', query);
                                
                                if (!query || query.length < 3) {
                                    const autocompleteEl = document.getElementById('address-autocomplete');
                                    if (autocompleteEl) {
                                        autocompleteEl.classList.add('hidden');
                                    }
                                    return;
                                }
                                
                                currentSearchQuery = query;
                                
                                const autocompleteEl = document.getElementById('address-autocomplete');
                                if (!autocompleteEl) {
                                    console.error('[Address] Autocomplete element not found');
                                    return;
                                }
                                
                                // Show loading state
                                autocompleteEl.innerHTML = '<div class="px-4 py-3 text-sm text-neutral-500 text-center">Recherche en cours...</div>';
                                autocompleteEl.classList.remove('hidden');
                                
                                try {
                                    // Use our Laravel API endpoint (avoids CORS issues)
                                    const url = `{{ route('api.geocoding.search') }}?q=${encodeURIComponent(query)}`;
                                    console.log('[Address] ====== SEARCH START ======');
                                    console.log('[Address] Query:', query);
                                    console.log('[Address] Fetching from:', url);
                                    console.log('[Address] Timestamp:', new Date().toISOString());
                                    
                                    const startTime = Date.now();
                                    const response = await fetch(url, {
                                        method: 'GET',
                                        headers: {
                                            'Accept': 'application/json',
                                            'X-Requested-With': 'XMLHttpRequest'
                                        }
                                    });
                                    const endTime = Date.now();
                                    
                                    console.log('[Address] Response received in', (endTime - startTime), 'ms');
                                    console.log('[Address] Response status:', response.status);
                                    console.log('[Address] Response headers:', Object.fromEntries(response.headers.entries()));
                                    
                                    if (!response.ok) {
                                        const errorText = await response.text();
                                        console.error('[Address] HTTP error response body:', errorText);
                                        throw new Error(`HTTP error! status: ${response.status}, body: ${errorText.substring(0, 200)}`);
                                    }
                                    
                                    const data = await response.json();
                                    console.log('[Address] Received data type:', typeof data);
                                    console.log('[Address] Received data:', data);
                                    console.log('[Address] Data is array?', Array.isArray(data));
                                    console.log('[Address] Data length:', Array.isArray(data) ? data.length : 'N/A');
                                    
                                    // Only process if query hasn't changed
                                    if (query !== currentSearchQuery) {
                                        console.log('[Address] Query changed, ignoring results');
                                        return;
                                    }
                                    
                                    if (data && data.length > 0) {
                                        autocompleteEl.innerHTML = '';
                                        
                                        data.forEach((item, index) => {
                                            const div = document.createElement('div');
                                            div.className = 'px-4 py-3 hover:bg-neutral-50 cursor-pointer border-b border-neutral-100 last:border-b-0';
                                            div.innerHTML = `
                                                <div class="flex items-start gap-3">
                                                    <svg class="w-5 h-5 text-primary-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                    </svg>
                                                    <div class="flex-1 min-w-0">
                                                        <p class="text-sm font-medium text-neutral-900">${item.display_name.split(',')[0]}</p>
                                                        <p class="text-xs text-neutral-500 mt-0.5">${item.display_name.split(',').slice(1, 3).join(', ')}</p>
                                                    </div>
                                                </div>
                                            `;
                                            
                                            div.addEventListener('click', function() {
                                                selectAddress(item);
                                            });
                                            
                                            autocompleteEl.appendChild(div);
                                        });
                                        
                                        autocompleteEl.classList.remove('hidden');
                                        console.log('[Address] Displayed', data.length, 'results');
                                    } else {
                                        autocompleteEl.innerHTML = `
                                            <div class="px-4 py-3 text-sm text-neutral-500 text-center">
                                                Aucune adresse trouvée
                                            </div>
                                        `;
                                        autocompleteEl.classList.remove('hidden');
                                        console.log('[Address] No results found');
                                    }
                                } catch (error) {
                                    console.error('[Address] Search error:', error);
                                    autocompleteEl.innerHTML = `
                                        <div class="px-4 py-3 text-sm text-red-500 text-center">
                                            Erreur lors de la recherche. Veuillez réessayer.
                                        </div>
                                    `;
                                    autocompleteEl.classList.remove('hidden');
                                }
                            }
                            
                            // Select address from autocomplete
                            function selectAddress(item) {
                                const lat = parseFloat(item.lat);
                                const lng = parseFloat(item.lon);
                                
                                // Update search input
                                document.getElementById('address-search').value = item.display_name;
                                document.getElementById('address-autocomplete').classList.add('hidden');
                                
                                // Validate delivery distance
                                const isValid = validateDeliveryDistance(lat, lng);
                                
                                // Extract address components
                                const address = item.address || {};
                                const street = address.road || address.pedestrian || item.display_name.split(',')[0];
                                const houseNumber = address.house_number || '';
                                const city = address.city || address.town || address.village || address.municipality || address.state || 'Côte d\'Ivoire';
                                
                                // Update form fields
                                const addressInput = document.getElementById('delivery_address_input');
                                const cityInput = document.getElementById('delivery_city_input');
                                
                                if (addressInput) {
                                    addressInput.value = houseNumber ? `${houseNumber} ${street}`.trim() : street;
                                }
                                if (cityInput) {
                                    cityInput.value = city;
                                }
                                
                                // Update Livewire
                                if (window.Livewire) {
                                    const component = window.Livewire.find('{{ $this->getId() }}');
                                    if (component) {
                                        component.set('delivery_address', houseNumber ? `${houseNumber} ${street}`.trim() : street);
                                        component.set('delivery_city', city);
                                        component.set('delivery_latitude', lat);
                                        component.set('delivery_longitude', lng);
                                    }
                                }
                            }
                            
                            // Function to reverse geocode (get address from coordinates)
                            async function reverseGeocode(lat, lng) {
                                try {
                                    console.log('[Address] Reverse geocoding...');
                                    // Use our Laravel API endpoint
                                    const url = `{{ route('api.geocoding.reverse') }}?lat=${lat}&lon=${lng}`;
                                    console.log('[Address] Reverse URL:', url);
                                    
                                    const response = await fetch(url, {
                                        method: 'GET',
                                        headers: {
                                            'Accept': 'application/json',
                                            'X-Requested-With': 'XMLHttpRequest'
                                        }
                                    });
                                    
                                    if (response.ok) {
                                        const data = await response.json();
                                        console.log('[Address] Reverse geocoding result:', data);
                                        
                                        if (data.address || data.city) {
                                            return {
                                                address: data.address || '',
                                                city: data.city || 'Côte d\'Ivoire'
                                            };
                                        }
                                    } else {
                                        console.error('[Address] Reverse geocoding failed:', response.status);
                                    }
                                } catch (error) {
                                    console.error('[Address] Reverse geocoding error:', error);
                                }
                                return null;
                            }
                            
                            // Use my location
                            function useMyLocation() {
                                console.log('[Address] Use my location clicked');
                                const button = document.getElementById('use-my-location');
                                if (!button) {
                                    console.error('[Address] Button not found');
                                    return;
                                }
                                
                                const originalText = button.innerHTML;
                                
                                button.disabled = true;
                                button.innerHTML = '<svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg> Localisation...';
                                
                                if (navigator.geolocation) {
                                    console.log('[Address] Requesting geolocation...');
                                    navigator.geolocation.getCurrentPosition(
                                        function(position) {
                                            console.log('[Address] Position received:', position.coords.latitude, position.coords.longitude);
                                            const lat = position.coords.latitude;
                                            const lng = position.coords.longitude;
                                            
                                            // Validate delivery distance
                                            const isValid = validateDeliveryDistance(lat, lng);
                                            
                                            // Reverse geocode to get address
                                            console.log('[Address] Reverse geocoding...');
                                            reverseGeocode(lat, lng).then(addressData => {
                                                console.log('[Address] Address data:', addressData);
                                                if (addressData) {
                                                    const addressInput = document.getElementById('delivery_address_input');
                                                    const cityInput = document.getElementById('delivery_city_input');
                                                    const searchInput = document.getElementById('address-search');
                                                    
                                                    if (addressInput) addressInput.value = addressData.address;
                                                    if (cityInput) cityInput.value = addressData.city;
                                                    if (searchInput) searchInput.value = addressData.address + ', ' + addressData.city;
                                                    
                                                    // Update Livewire
                                                    if (window.Livewire) {
                                                        const component = window.Livewire.find('{{ $this->getId() }}');
                                                        if (component) {
                                                            component.set('delivery_address', addressData.address);
                                                            component.set('delivery_city', addressData.city);
                                                            component.set('delivery_latitude', lat);
                                                            component.set('delivery_longitude', lng);
                                                        }
                                                    }
                                                } else {
                                                    console.warn('[Address] No address data from reverse geocoding');
                                                }
                                                
                                                button.disabled = false;
                                                button.innerHTML = originalText;
                                            }).catch(error => {
                                                console.error('[Address] Reverse geocoding error:', error);
                                                button.disabled = false;
                                                button.innerHTML = originalText;
                                            });
                                        },
                                        function(error) {
                                            console.error('[Address] Geolocation error:', error);
                                            let errorMessage = 'Impossible d\'obtenir votre position. ';
                                            if (error.code === error.PERMISSION_DENIED) {
                                                errorMessage += 'Veuillez autoriser l\'accès à la localisation dans les paramètres de votre navigateur.';
                                            } else if (error.code === error.POSITION_UNAVAILABLE) {
                                                errorMessage += 'Votre position n\'est pas disponible.';
                                            } else if (error.code === error.TIMEOUT) {
                                                errorMessage += 'La demande de localisation a expiré.';
                                            } else {
                                                errorMessage += 'Veuillez rechercher une adresse manuellement.';
                                            }
                                            alert(errorMessage);
                                            button.disabled = false;
                                            button.innerHTML = originalText;
                                        },
                                        {
                                            enableHighAccuracy: true,
                                            timeout: 10000,
                                            maximumAge: 0
                                        }
                                    );
                                } else {
                                    alert('La géolocalisation n\'est pas supportée par votre navigateur.');
                                    button.disabled = false;
                                    button.innerHTML = originalText;
                                }
                            }
                            
                            // Initialize when DOM is ready
                            let initialized = false;
                            
                            function initializeAddressSearch() {
                                if (initialized) {
                                    console.log('[Address] Already initialized, skipping...');
                                    return;
                                }
                                
                                console.log('[Address] Initializing address search...');
                                
                                // Setup address search
                                const addressSearchInput = document.getElementById('address-search');
                                const useMyLocationBtn = document.getElementById('use-my-location');
                                const debugEl = document.getElementById('address-debug');
                                
                                if (!addressSearchInput) {
                                    console.error('[Address] Address search input not found');
                                    if (debugEl) {
                                        debugEl.textContent = 'Erreur: Champ de recherche non trouvé';
                                        debugEl.classList.remove('hidden');
                                    }
                                    return;
                                }
                                
                                if (!useMyLocationBtn) {
                                    console.error('[Address] Use my location button not found');
                                    return;
                                }
                                
                                console.log('[Address] Both elements found, setting up listeners...');
                                
                                // Remove existing listeners by cloning
                                const newInput = addressSearchInput.cloneNode(true);
                                addressSearchInput.parentNode.replaceChild(newInput, addressSearchInput);
                                
                                // Setup address search
                                newInput.addEventListener('input', function(e) {
                                    const query = e.target.value.trim();
                                    
                                    if (searchTimeout) {
                                        clearTimeout(searchTimeout);
                                    }
                                    
                                    if (query.length >= 3) {
                                        searchTimeout = setTimeout(() => {
                                            searchAddresses(query);
                                        }, 500); // Debounce 500ms
                                    } else {
                                        const autocompleteEl = document.getElementById('address-autocomplete');
                                        if (autocompleteEl) {
                                            autocompleteEl.classList.add('hidden');
                                        }
                                    }
                                });
                                
                                // Setup use my location button
                                useMyLocationBtn.addEventListener('click', useMyLocation);
                                
                                // Close autocomplete when clicking outside
                                document.addEventListener('click', function(e) {
                                    const autocompleteEl = document.getElementById('address-autocomplete');
                                    if (autocompleteEl && !newInput.contains(e.target) && !autocompleteEl.contains(e.target)) {
                                        autocompleteEl.classList.add('hidden');
                                    }
                                });
                                
                                initialized = true;
                                console.log('[Address] Initialization complete!');
                                
                                if (debugEl) {
                                    debugEl.textContent = '✓ Système de recherche initialisé';
                                    debugEl.classList.remove('hidden');
                                    setTimeout(() => debugEl.classList.add('hidden'), 3000);
                                }
                            }
                            
                            // Initialize immediately and also after Livewire updates
                            function tryInitialize() {
                                const addressSearchInput = document.getElementById('address-search');
                                const useMyLocationBtn = document.getElementById('use-my-location');
                                
                                if (addressSearchInput && useMyLocationBtn) {
                                    initializeAddressSearch();
                                    return true;
                                }
                                return false;
                            }
                            
                            // Try immediately
                            if (!tryInitialize()) {
                                // Wait for DOM
                                if (document.readyState === 'loading') {
                                    document.addEventListener('DOMContentLoaded', () => {
                                        setTimeout(() => tryInitialize(), 200);
                                    });
                                } else {
                                    setTimeout(() => tryInitialize(), 200);
                                }
                            }
                            
                            // Also initialize after Livewire updates
                            if (typeof Livewire !== 'undefined') {
                                document.addEventListener('livewire:init', () => {
                                    setTimeout(() => tryInitialize(), 300);
                                    
                                    Livewire.hook('morph.updated', () => {
                                        setTimeout(() => tryInitialize(), 300);
                                    });
                                });
                            }
                            
                            // Also try after a delay (for Livewire components that load later)
                            setTimeout(() => tryInitialize(), 1000);
                                
                        })();
                    </script>
                    @endpush
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
                    @if($this->fusionpayPaymentAvailable || $this->waveCheckoutAvailable || $this->onlinePaymentAvailable || $this->menupoHubPaymentAvailable || $this->cashOnDeliveryAvailable)
                        <div class="mb-6 pb-6 border-b border-neutral-200">
                            <h3 class="text-sm font-semibold text-neutral-700 mb-3">Mode de paiement</h3>
                            <div class="space-y-2">
                                {{-- Wave Checkout direct — le restaurant reçoit l'argent instantanément --}}
                                @if($this->waveCheckoutAvailable)
                                    <label class="flex items-center gap-3 p-3 rounded-xl border-2 cursor-pointer transition-all {{ $payment_method === 'wave_checkout' ? 'border-primary-500 bg-primary-50' : 'border-neutral-200 hover:border-neutral-300' }}">
                                        <input type="radio" wire:model="payment_method" value="wave_checkout" class="text-primary-500 focus:ring-primary-500">
                                        <x-payment-logo method="wave" />
                                        <div class="flex-1">
                                            <span class="font-medium">Wave</span>
                                            <span class="ml-2 text-xs px-1.5 py-0.5 bg-emerald-100 text-emerald-700 rounded-full font-medium">Paiement direct</span>
                                        </div>
                                        <span class="text-xs text-neutral-500">Rapide & sécurisé</span>
                                    </label>
                                @endif
                                @if($this->fusionpayPaymentAvailable)
                                    <label class="flex items-center gap-3 p-3 rounded-xl border-2 cursor-pointer transition-all {{ $payment_method === 'fusionpay' ? 'border-primary-500 bg-primary-50' : 'border-neutral-200 hover:border-neutral-300' }}">
                                        <input type="radio" wire:model="payment_method" value="fusionpay" class="text-primary-500 focus:ring-primary-500">
                                        <x-payment-logo method="fusionpay" />
                                        <span class="font-medium">FusionPay</span>
                                        <span class="text-xs text-neutral-500">(Wave, Orange, MTN)</span>
                                    </label>
                                @endif
                                @if($this->onlinePaymentAvailable)
                                    <label class="flex items-center gap-3 p-3 rounded-xl border-2 cursor-pointer transition-all {{ in_array($payment_method, ['lygos', 'geniuspay']) ? 'border-primary-500 bg-primary-50' : 'border-neutral-200 hover:border-neutral-300' }}">
                                        <input type="radio" wire:model="payment_method" value="{{ $this->onlinePaymentMethod }}" class="text-primary-500 focus:ring-primary-500">
                                        <x-payment-logo method="online" />
                                        <span class="font-medium">Paiement en ligne</span>
                                        <span class="text-xs text-neutral-500">(Lygos / GeniusPay)</span>
                                    </label>
                                @endif
                                @if($this->menupoHubPaymentAvailable)
                                    @foreach($this->menupoHubMethods as $method)
                                        <label class="flex items-center gap-3 p-3 rounded-xl border-2 cursor-pointer transition-all {{ $payment_method === $method ? 'border-primary-500 bg-primary-50' : 'border-neutral-200 hover:border-neutral-300' }}">
                                            <input type="radio" wire:model="payment_method" value="{{ $method }}" class="text-primary-500 focus:ring-primary-500">
                                            <x-payment-logo :method="$method" />
                                            <span class="font-medium">
                                                @if($method === 'wave')
                                                    <span class="inline-flex items-center gap-1.5">Wave</span>
                                                @elseif($method === 'orange')
                                                    <span class="inline-flex items-center gap-1.5">Orange Money</span>
                                                @elseif($method === 'mtn')
                                                    <span class="inline-flex items-center gap-1.5">MTN MoMo</span>
                                                @else
                                                    <span class="inline-flex items-center gap-1.5">Moov Money</span>
                                                @endif
                                            </span>
                                            <span class="text-xs text-neutral-500">(Paiement direct · PC et mobile)</span>
                                        </label>
                                    @endforeach
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
</div>

