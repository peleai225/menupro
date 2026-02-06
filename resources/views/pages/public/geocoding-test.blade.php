<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Geoapify - MenuPro</title>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        [x-cloak] { display: none !important; }
    </style>
</head>
<body class="bg-gray-50 min-h-screen py-8">
    <div class="max-w-4xl mx-auto px-4" x-data="geocodingTest()">
        <div class="bg-white rounded-lg shadow-lg p-6">
            <h1 class="text-3xl font-bold text-gray-900 mb-2">Test API Geoapify</h1>
            <p class="text-gray-600 mb-6">Page de test pour l'autocomplétion d'adresses</p>

            <!-- Search Input -->
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-700 mb-2">
                    Rechercher une adresse
                </label>
                <div class="relative">
                    <input 
                        type="text" 
                        id="address-search"
                        x-model="query"
                        @input="searchAddresses"
                        class="w-full px-4 py-3 pr-12 border border-gray-300 rounded-lg focus:ring-2 focus:ring-blue-500 focus:border-transparent"
                        placeholder="Tapez une adresse (ex: Abidjan, Cocody, Marcory...)"
                        autocomplete="off">
                    <svg class="absolute right-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
                
                <!-- Autocomplete Dropdown -->
                <div 
                    id="address-autocomplete" 
                    x-show="showAutocomplete && results.length > 0"
                    x-cloak
                    class="mt-2 bg-white border border-gray-200 rounded-lg shadow-lg max-h-60 overflow-y-auto">
                    <template x-for="(result, index) in results" :key="index">
                        <div 
                            @click="selectAddress(result)"
                            class="px-4 py-3 hover:bg-gray-50 cursor-pointer border-b border-gray-100 last:border-b-0">
                            <div class="flex items-start gap-3">
                                <svg class="w-5 h-5 text-blue-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                </svg>
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-gray-900" x-text="result.display_name.split(',')[0]"></p>
                                    <p class="text-xs text-gray-500 mt-0.5" x-text="result.display_name.split(',').slice(1, 3).join(', ')"></p>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>

                <!-- Use My Location Button -->
                <button 
                    @click="useMyLocation"
                    :disabled="loading"
                    class="mt-3 w-full px-4 py-2.5 bg-blue-50 text-blue-600 rounded-lg font-medium hover:bg-blue-100 transition-colors disabled:opacity-50 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <span x-text="loading ? 'Localisation...' : 'Utiliser ma position actuelle'"></span>
                </button>
            </div>

            <!-- Selected Address Display -->
            <div x-show="selectedAddress" x-cloak class="mb-6 p-4 bg-green-50 border border-green-200 rounded-lg">
                <h3 class="text-sm font-semibold text-green-800 mb-2">Adresse sélectionnée :</h3>
                <div class="text-sm text-green-700 space-y-1">
                    <p><strong>Nom complet :</strong> <span x-text="selectedAddress.display_name"></span></p>
                    <p><strong>Coordonnées :</strong> <span x-text="selectedAddress.lat + ', ' + selectedAddress.lon"></span></p>
                    <p><strong>Ville :</strong> <span x-text="selectedAddress.address.city || 'N/A'"></span></p>
                    <p><strong>Rue :</strong> <span x-text="selectedAddress.address.road || 'N/A'"></span></p>
                    <p><strong>Pays :</strong> <span x-text="selectedAddress.address.country || 'N/A'"></span></p>
                </div>
            </div>

            <!-- Debug Info -->
            <div class="border-t border-gray-200 pt-6">
                <h3 class="text-sm font-semibold text-gray-700 mb-3">Informations de débogage</h3>
                <div class="bg-gray-50 rounded-lg p-4 space-y-2 text-xs font-mono">
                    <div><strong>État :</strong> <span x-text="status"></span></div>
                    <div><strong>Résultats :</strong> <span x-text="results.length"></span></div>
                    <div x-show="error" class="text-red-600"><strong>Erreur :</strong> <span x-text="error"></span></div>
                    <div x-show="lastResponse" class="mt-2">
                        <strong>Dernière réponse :</strong>
                        <pre class="mt-1 text-xs overflow-auto max-h-40" x-text="JSON.stringify(lastResponse, null, 2)"></pre>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function geocodingTest() {
            return {
                query: '',
                results: [],
                selectedAddress: null,
                showAutocomplete: false,
                loading: false,
                status: 'Prêt',
                error: null,
                lastResponse: null,
                searchTimeout: null,

                searchAddresses() {
                    const query = this.query.trim();
                    
                    if (query.length < 3) {
                        this.results = [];
                        this.showAutocomplete = false;
                        return;
                    }

                    if (this.searchTimeout) {
                        clearTimeout(this.searchTimeout);
                    }

                    this.searchTimeout = setTimeout(async () => {
                        this.loading = true;
                        this.status = 'Recherche en cours...';
                        this.error = null;

                        try {
                            const url = `{{ route('api.geocoding.search') }}?q=${encodeURIComponent(query)}`;
                            console.log('[Test] Fetching:', url);

                            const response = await fetch(url, {
                                method: 'GET',
                                headers: {
                                    'Accept': 'application/json',
                                    'X-Requested-With': 'XMLHttpRequest'
                                }
                            });

                            console.log('[Test] Response status:', response.status);

                            if (!response.ok) {
                                throw new Error(`HTTP ${response.status}`);
                            }

                            const data = await response.json();
                            console.log('[Test] Received data:', data);
                            
                            this.lastResponse = data;
                            this.results = Array.isArray(data) ? data : [];
                            this.showAutocomplete = this.results.length > 0;
                            this.status = `${this.results.length} résultat(s) trouvé(s)`;
                        } catch (error) {
                            console.error('[Test] Error:', error);
                            this.error = error.message;
                            this.status = 'Erreur lors de la recherche';
                            this.results = [];
                            this.showAutocomplete = false;
                        } finally {
                            this.loading = false;
                        }
                    }, 500);
                },

                selectAddress(address) {
                    this.selectedAddress = address;
                    this.query = address.display_name;
                    this.showAutocomplete = false;
                    this.status = 'Adresse sélectionnée';
                },

                async useMyLocation() {
                    this.loading = true;
                    this.status = 'Obtention de la position...';
                    this.error = null;

                    if (!navigator.geolocation) {
                        this.error = 'Géolocalisation non supportée';
                        this.loading = false;
                        return;
                    }

                    navigator.geolocation.getCurrentPosition(
                        async (position) => {
                            const lat = position.coords.latitude;
                            const lng = position.coords.longitude;
                            
                            this.status = 'Géocodage inverse...';
                            
                            try {
                                const url = `{{ route('api.geocoding.reverse') }}?lat=${lat}&lon=${lng}`;
                                const response = await fetch(url);
                                
                                if (response.ok) {
                                    const data = await response.json();
                                    this.selectedAddress = {
                                        display_name: `${data.address}, ${data.city}`,
                                        lat: lat,
                                        lon: lng,
                                        address: {
                                            road: data.address,
                                            city: data.city,
                                            country: 'Côte d\'Ivoire'
                                        }
                                    };
                                    this.query = this.selectedAddress.display_name;
                                    this.status = 'Position obtenue';
                                } else {
                                    throw new Error('Erreur de géocodage inverse');
                                }
                            } catch (error) {
                                this.error = error.message;
                                this.status = 'Erreur';
                            } finally {
                                this.loading = false;
                            }
                        },
                        (error) => {
                            this.error = 'Impossible d\'obtenir la position';
                            this.status = 'Erreur';
                            this.loading = false;
                        }
                    );
                }
            }
        }
    </script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
</body>
</html>
