<x-layouts.admin-super title="Ville : {{ $city->name }}">
    <div class="space-y-6" x-data="cityDetail()">

        {{-- Toast --}}
        <div x-show="flash.message" x-transition
             :class="flash.type === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-red-50 border-red-200 text-red-800'"
             class="flex items-center gap-3 px-4 py-3 rounded-xl border text-sm" x-cloak>
            <span x-text="flash.message"></span>
        </div>

        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('super-admin.delivery-cities.index') }}" class="p-2 rounded-lg hover:bg-neutral-100 text-neutral-500">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <div>
                    <h1 class="text-xl font-bold text-neutral-900">{{ $city->name }}</h1>
                    <p class="text-sm text-neutral-500">{{ $restaurantCount }} restaurant(s) actif(s) en livraison</p>
                </div>
            </div>
            <div class="flex items-center gap-3">
                <span class="text-sm font-medium" :class="city.is_active ? 'text-emerald-600' : 'text-neutral-500'" x-text="city.is_active ? 'Active' : 'Inactive'"></span>
                <button @click="toggleCity()" class="relative w-11 h-6 rounded-full transition-colors duration-200"
                        :class="city.is_active ? 'bg-emerald-500' : 'bg-neutral-300'">
                    <span class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform duration-200"
                          :class="city.is_active ? 'translate-x-5' : ''"></span>
                </button>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

            {{-- Colonne gauche : Tarification --}}
            <div class="space-y-6">
                <div class="bg-white rounded-2xl border border-neutral-200 shadow-sm p-6">
                    <h2 class="font-semibold text-neutral-900 mb-4">Tarification</h2>
                    <form @submit.prevent="updateCity" class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium text-neutral-600 mb-1">Frais de base (centimes XOF)</label>
                            <input type="number" x-model="city.delivery_base_fee" min="0" class="w-full h-10 px-3 bg-neutral-50 border border-neutral-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <p class="text-xs text-neutral-400 mt-1" x-text="(city.delivery_base_fee / 100) + ' FCFA'"></p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-neutral-600 mb-1">Frais par km (centimes XOF)</label>
                            <input type="number" x-model="city.delivery_fee_per_km" min="0" class="w-full h-10 px-3 bg-neutral-50 border border-neutral-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <p class="text-xs text-neutral-400 mt-1" x-text="(city.delivery_fee_per_km / 100) + ' FCFA/km'"></p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-neutral-600 mb-1">Distance max resto→client (km)</label>
                            <input type="number" x-model="city.max_delivery_distance_km" min="1" max="50" class="w-full h-10 px-3 bg-neutral-50 border border-neutral-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-neutral-600 mb-1">Rayon de couverture (km)</label>
                            <input type="number" x-model="city.coverage_radius_km" min="1" max="100" class="w-full h-10 px-3 bg-neutral-50 border border-neutral-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-neutral-600 mb-1">Surcharge heures pointe (%)</label>
                            <input type="number" x-model="city.peak_hour_surcharge_percent" min="0" max="100" class="w-full h-10 px-3 bg-neutral-50 border border-neutral-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-neutral-600 mb-1">Commande minimum (centimes XOF)</label>
                            <input type="number" x-model="city.min_order_amount" min="0" class="w-full h-10 px-3 bg-neutral-50 border border-neutral-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                            <p class="text-xs text-neutral-400 mt-1" x-text="(city.min_order_amount / 100) + ' FCFA'"></p>
                        </div>
                        <button type="submit" :disabled="saving" class="w-full h-10 bg-primary-600 text-white rounded-xl text-sm font-medium hover:bg-primary-700 disabled:opacity-50 flex items-center justify-center gap-2">
                            <svg x-show="saving" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            <span x-text="saving ? 'Enregistrement...' : 'Enregistrer'"></span>
                        </button>
                    </form>
                </div>

                {{-- Simulateur --}}
                <div class="bg-white rounded-2xl border border-neutral-200 shadow-sm p-6">
                    <h2 class="font-semibold text-neutral-900 mb-3">Simulateur de tarif</h2>
                    <div class="space-y-3">
                        <div>
                            <label class="block text-xs font-medium text-neutral-600 mb-1">Distance (km)</label>
                            <input type="number" x-model="simDistance" min="0" step="0.5" class="w-full h-10 px-3 bg-neutral-50 border border-neutral-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                        <div class="bg-neutral-50 rounded-xl p-3">
                            <p class="text-xs text-neutral-500">Tarif estimé :</p>
                            <p class="text-lg font-bold text-neutral-900" x-text="simulatedFee() + ' FCFA'"></p>
                            <p class="text-xs text-neutral-400" x-text="'(Pointe +' + city.peak_hour_surcharge_percent + '% : ' + simulatedFeePeak() + ' FCFA)'"></p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Colonne droite : Carte + Zones --}}
            <div class="xl:col-span-2 space-y-6">

                {{-- Carte --}}
                <div class="bg-white rounded-2xl border border-neutral-200 shadow-sm overflow-hidden">
                    <div class="px-5 py-3 border-b border-neutral-100">
                        <h2 class="font-semibold text-neutral-900">Couverture</h2>
                    </div>
                    <div id="city-map" class="h-80"></div>
                </div>

                {{-- Zones / Quartiers --}}
                <div class="bg-white rounded-2xl border border-neutral-200 shadow-sm overflow-hidden">
                    <div class="px-5 py-4 border-b border-neutral-100 flex items-center justify-between">
                        <h2 class="font-semibold text-neutral-900">Zones / Quartiers <span class="text-neutral-400 font-normal" x-text="'(' + zones.length + ')'"></span></h2>
                        <button @click="showZoneForm = !showZoneForm" class="h-8 px-3 bg-neutral-100 text-neutral-700 rounded-lg text-xs font-medium hover:bg-neutral-200 flex items-center gap-1.5">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                            Ajouter
                        </button>
                    </div>

                    {{-- Formulaire nouvelle zone --}}
                    <div x-show="showZoneForm" x-transition x-cloak class="px-5 py-4 border-b border-neutral-100 bg-neutral-50">
                        <form @submit.prevent="createZone" class="flex flex-wrap gap-3 items-end">
                            <div class="flex-1 min-w-[150px]">
                                <label class="block text-xs font-medium text-neutral-600 mb-1">Nom *</label>
                                <input type="text" x-model="zoneForm.name" required placeholder="ex: Cocody" class="w-full h-9 px-3 bg-white border border-neutral-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                            <div class="w-24">
                                <label class="block text-xs font-medium text-neutral-600 mb-1">Rayon (km)</label>
                                <input type="number" x-model="zoneForm.radius_km" min="1" max="50" class="w-full h-9 px-3 bg-white border border-neutral-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                            <div class="w-28">
                                <label class="block text-xs font-medium text-neutral-600 mb-1">Latitude</label>
                                <input type="number" x-model="zoneForm.center_latitude" step="0.0000001" class="w-full h-9 px-3 bg-white border border-neutral-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                            <div class="w-28">
                                <label class="block text-xs font-medium text-neutral-600 mb-1">Longitude</label>
                                <input type="number" x-model="zoneForm.center_longitude" step="0.0000001" class="w-full h-9 px-3 bg-white border border-neutral-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                            </div>
                            <button type="submit" :disabled="savingZone" class="h-9 px-4 bg-primary-600 text-white rounded-lg text-sm font-medium hover:bg-primary-700 disabled:opacity-50">
                                <span x-text="savingZone ? '...' : 'Ajouter'"></span>
                            </button>
                        </form>
                    </div>

                    {{-- Liste des zones --}}
                    <div class="divide-y divide-neutral-100">
                        <template x-if="zones.length === 0">
                            <div class="px-5 py-8 text-center text-neutral-400 text-sm">Aucune zone. Ajoutez des quartiers pour l'affichage client.</div>
                        </template>
                        <template x-for="zone in zones" :key="zone.id">
                            <div class="px-5 py-3 flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-8 h-8 rounded-lg flex items-center justify-center"
                                         :class="zone.is_active ? 'bg-emerald-50 text-emerald-500' : 'bg-neutral-50 text-neutral-400'">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                                    </div>
                                    <div>
                                        <p class="text-sm font-medium text-neutral-900" x-text="zone.name"></p>
                                        <p class="text-xs text-neutral-500">Rayon : <span x-text="zone.radius_km"></span> km</p>
                                    </div>
                                </div>
                                <button @click="toggleZone(zone)" class="relative w-10 h-5 rounded-full transition-colors duration-200"
                                        :class="zone.is_active ? 'bg-emerald-500' : 'bg-neutral-300'">
                                    <span class="absolute top-0.5 left-0.5 w-4 h-4 bg-white rounded-full shadow transition-transform duration-200"
                                          :class="zone.is_active ? 'translate-x-5' : ''"></span>
                                </button>
                            </div>
                        </template>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('styles')
    <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
    @endpush

    @push('scripts')
    <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>
    <script>
        function cityDetail() {
            return {
                city: @json($city),
                zones: @json($zones),
                showZoneForm: false,
                zoneForm: { name: '', radius_km: 5, center_latitude: '', center_longitude: '' },
                flash: { message: '', type: 'success' },
                saving: false,
                savingZone: false,
                simDistance: 3,
                map: null,
                csrf: document.querySelector('meta[name="csrf-token"]')?.content || '',

                init() {
                    this.$nextTick(() => this.initMap());
                },

                initMap() {
                    const lat = parseFloat(this.city.center_latitude);
                    const lng = parseFloat(this.city.center_longitude);

                    this.map = L.map('city-map').setView([lat, lng], 12);
                    L.tileLayer('https://{s}.tile.openstreetmap.org/{z}/{x}/{y}.png', {
                        attribution: '&copy; OpenStreetMap'
                    }).addTo(this.map);

                    L.circle([lat, lng], {
                        radius: this.city.coverage_radius_km * 1000,
                        color: '#3b82f6',
                        fillColor: '#3b82f6',
                        fillOpacity: 0.08,
                        weight: 2
                    }).addTo(this.map);

                    L.marker([lat, lng]).addTo(this.map).bindPopup(this.city.name + ' (centre)');

                    this.zones.forEach(zone => {
                        if (zone.center_latitude && zone.center_longitude) {
                            L.circle([parseFloat(zone.center_latitude), parseFloat(zone.center_longitude)], {
                                radius: zone.radius_km * 1000,
                                color: zone.is_active ? '#10b981' : '#9ca3af',
                                fillColor: zone.is_active ? '#10b981' : '#9ca3af',
                                fillOpacity: 0.1,
                                weight: 1.5
                            }).addTo(this.map).bindPopup(zone.name);
                        }
                    });
                },

                simulatedFee() {
                    const base = parseInt(this.city.delivery_base_fee) || 0;
                    const perKm = parseInt(this.city.delivery_fee_per_km) || 0;
                    const dist = parseFloat(this.simDistance) || 0;
                    return Math.round((base + dist * perKm) / 100);
                },

                simulatedFeePeak() {
                    const fee = this.simulatedFee() * 100;
                    const surcharge = parseInt(this.city.peak_hour_surcharge_percent) || 0;
                    return Math.round((fee * (1 + surcharge / 100)) / 100);
                },

                showFlash(msg, type) {
                    this.flash = { message: msg, type: type || 'success' };
                    clearTimeout(this._flashTimer);
                    this._flashTimer = setTimeout(() => { this.flash.message = ''; }, 4000);
                    if (typeof adminToast === 'function') adminToast(msg, type || 'success');
                },

                async request(url, method, data) {
                    const fd = new FormData();
                    fd.append('_token', this.csrf);
                    if (method !== 'POST') fd.append('_method', method);
                    Object.entries(data || {}).forEach(([k, v]) => {
                        if (v !== null && v !== undefined) fd.append(k, v);
                    });
                    const res = await fetch(url, {
                        method: 'POST',
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest', 'X-CSRF-TOKEN': this.csrf },
                        body: fd
                    });
                    const body = await res.json();
                    if (!res.ok) throw body;
                    return body;
                },

                async updateCity() {
                    this.saving = true;
                    try {
                        const res = await this.request(`{{ route('super-admin.delivery-cities.update', $city->id) }}`, 'PUT', {
                            name: this.city.name,
                            center_latitude: this.city.center_latitude,
                            center_longitude: this.city.center_longitude,
                            coverage_radius_km: this.city.coverage_radius_km,
                            delivery_base_fee: this.city.delivery_base_fee,
                            delivery_fee_per_km: this.city.delivery_fee_per_km,
                            max_delivery_distance_km: this.city.max_delivery_distance_km,
                            peak_hour_surcharge_percent: this.city.peak_hour_surcharge_percent,
                            min_order_amount: this.city.min_order_amount,
                        });
                        this.showFlash(res.message, 'success');
                    } catch (e) {
                        const msg = (e.errors ? Object.values(e.errors).flat()[0] : null) || e.message || 'Erreur.';
                        this.showFlash(msg, 'error');
                    } finally {
                        this.saving = false;
                    }
                },

                async toggleCity() {
                    try {
                        const res = await this.request(`{{ route('super-admin.delivery-cities.toggle', $city->id) }}`, 'POST', {});
                        this.city.is_active = res.is_active;
                        this.showFlash(res.message, 'success');
                    } catch (e) {
                        this.showFlash(e.message || 'Erreur.', 'error');
                    }
                },

                async createZone() {
                    this.savingZone = true;
                    try {
                        const res = await this.request(`{{ route('super-admin.delivery-cities.zones.store', $city->id) }}`, 'POST', this.zoneForm);
                        this.zones.push(res.zone);
                        this.zoneForm = { name: '', radius_km: 5, center_latitude: '', center_longitude: '' };
                        this.showZoneForm = false;
                        this.showFlash(res.message, 'success');
                    } catch (e) {
                        const msg = (e.errors ? Object.values(e.errors).flat()[0] : null) || e.message || 'Erreur.';
                        this.showFlash(msg, 'error');
                    } finally {
                        this.savingZone = false;
                    }
                },

                async toggleZone(zone) {
                    try {
                        const res = await this.request(`{{ url('/admin/villes-livraison/zones') }}/${zone.id}/toggle`, 'POST', {});
                        zone.is_active = res.is_active;
                        this.showFlash(res.message, 'success');
                    } catch (e) {
                        this.showFlash(e.message || 'Erreur.', 'error');
                    }
                }
            }
        }
    </script>
    @endpush
</x-layouts.admin-super>
