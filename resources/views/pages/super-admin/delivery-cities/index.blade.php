<x-layouts.admin-super title="Villes de livraison">
    <div class="space-y-6" x-data="deliveryCities()">

        {{-- Toast --}}
        <div x-show="flash.message" x-transition
             :class="flash.type === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-red-50 border-red-200 text-red-800'"
             class="flex items-center gap-3 px-4 py-3 rounded-xl border text-sm"
             x-cloak>
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path x-show="flash.type === 'success'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            <span x-text="flash.message"></span>
        </div>

        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold text-neutral-900">Villes de livraison</h1>
                <p class="text-sm text-neutral-500 mt-1">Gérez les villes où la livraison est active et configurez la tarification par ville.</p>
            </div>
            <button @click="showForm = !showForm" class="h-10 px-4 bg-primary-600 text-white rounded-xl text-sm font-medium hover:bg-primary-700 flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Ajouter une ville
            </button>
        </div>

        {{-- Formulaire création --}}
        <div x-show="showForm" x-transition x-cloak class="bg-white rounded-2xl border border-neutral-200 shadow-sm p-6">
            <h2 class="font-semibold text-neutral-900 mb-4">Nouvelle ville</h2>
            <form @submit.prevent="createCity" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium text-neutral-600 mb-1">Nom de la ville *</label>
                    <input type="text" x-model="form.name" required placeholder="ex: Abidjan" class="w-full h-10 px-3 bg-neutral-50 border border-neutral-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-neutral-600 mb-1">Latitude centre *</label>
                    <input type="number" x-model="form.center_latitude" step="0.0000001" required placeholder="5.3600" class="w-full h-10 px-3 bg-neutral-50 border border-neutral-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-neutral-600 mb-1">Longitude centre *</label>
                    <input type="number" x-model="form.center_longitude" step="0.0000001" required placeholder="-4.0083" class="w-full h-10 px-3 bg-neutral-50 border border-neutral-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-neutral-600 mb-1">Rayon de couverture (km) *</label>
                    <input type="number" x-model="form.coverage_radius_km" min="1" max="100" required class="w-full h-10 px-3 bg-neutral-50 border border-neutral-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-neutral-600 mb-1">Frais de base (FCFA) *</label>
                    <input type="number" x-model="form.delivery_base_fee" min="0" required class="w-full h-10 px-3 bg-neutral-50 border border-neutral-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-neutral-600 mb-1">Frais par km (FCFA) *</label>
                    <input type="number" x-model="form.delivery_fee_per_km" min="0" required class="w-full h-10 px-3 bg-neutral-50 border border-neutral-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-neutral-600 mb-1">Distance max resto→client (km) *</label>
                    <input type="number" x-model="form.max_delivery_distance_km" min="1" max="50" required class="w-full h-10 px-3 bg-neutral-50 border border-neutral-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-neutral-600 mb-1">Surcharge heures pointe (%)</label>
                    <input type="number" x-model="form.peak_hour_surcharge_percent" min="0" max="100" class="w-full h-10 px-3 bg-neutral-50 border border-neutral-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>
                <div>
                    <label class="block text-xs font-medium text-neutral-600 mb-1">Commande minimum (FCFA)</label>
                    <input type="number" x-model="form.min_order_amount" min="0" class="w-full h-10 px-3 bg-neutral-50 border border-neutral-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>
                <div class="md:col-span-2 lg:col-span-3 flex gap-3">
                    <button type="submit" :disabled="saving" class="h-10 px-6 bg-primary-600 text-white rounded-xl text-sm font-medium hover:bg-primary-700 disabled:opacity-50 flex items-center gap-2">
                        <svg x-show="saving" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        <span x-text="saving ? 'Création...' : 'Créer la ville'"></span>
                    </button>
                    <button type="button" @click="showForm = false" class="h-10 px-4 bg-neutral-100 text-neutral-700 rounded-xl text-sm hover:bg-neutral-200">Annuler</button>
                </div>
            </form>
        </div>

        {{-- Grille des villes --}}
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            <template x-for="city in cities" :key="city.id">
                <div class="bg-white rounded-2xl border border-neutral-200 shadow-sm p-5 hover:shadow-md transition-shadow">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 rounded-xl flex items-center justify-center"
                                 :class="city.is_active ? 'bg-emerald-100 text-emerald-600' : 'bg-neutral-100 text-neutral-400'">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-neutral-900" x-text="city.name"></h3>
                                <p class="text-xs text-neutral-500">Rayon : <span x-text="city.coverage_radius_km"></span> km</p>
                            </div>
                        </div>
                        <button @click="toggleCity(city)" class="relative w-11 h-6 rounded-full transition-colors duration-200"
                                :class="city.is_active ? 'bg-emerald-500' : 'bg-neutral-300'">
                            <span class="absolute top-0.5 left-0.5 w-5 h-5 bg-white rounded-full shadow transition-transform duration-200"
                                  :class="city.is_active ? 'translate-x-5' : ''"></span>
                        </button>
                    </div>

                    <div class="grid grid-cols-2 gap-2 mb-4 text-xs">
                        <div class="bg-neutral-50 rounded-lg p-2">
                            <span class="text-neutral-500">Base</span>
                            <p class="font-semibold text-neutral-900" x-text="(city.delivery_base_fee / 100) + ' FCFA'"></p>
                        </div>
                        <div class="bg-neutral-50 rounded-lg p-2">
                            <span class="text-neutral-500">Par km</span>
                            <p class="font-semibold text-neutral-900" x-text="(city.delivery_fee_per_km / 100) + ' FCFA'"></p>
                        </div>
                        <div class="bg-neutral-50 rounded-lg p-2">
                            <span class="text-neutral-500">Distance max</span>
                            <p class="font-semibold text-neutral-900" x-text="city.max_delivery_distance_km + ' km'"></p>
                        </div>
                        <div class="bg-neutral-50 rounded-lg p-2">
                            <span class="text-neutral-500">Zones</span>
                            <p class="font-semibold text-neutral-900" x-text="city.zones_count || 0"></p>
                        </div>
                    </div>

                    <a :href="'{{ url('/admin/villes-livraison') }}/' + city.id"
                       class="block w-full text-center h-9 leading-9 bg-neutral-100 text-neutral-700 rounded-xl text-sm font-medium hover:bg-neutral-200 transition-colors">
                        Configurer
                    </a>
                </div>
            </template>
        </div>

        <template x-if="cities.length === 0">
            <div class="bg-white rounded-2xl border border-neutral-200 shadow-sm p-12 text-center">
                <svg class="w-12 h-12 mx-auto text-neutral-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                <p class="text-neutral-500">Aucune ville configurée. Ajoutez votre première ville pour activer la livraison.</p>
            </div>
        </template>
    </div>

    @push('scripts')
    <script>
        function deliveryCities() {
            return {
                cities: @json($cities),
                showForm: false,
                form: {
                    name: '', center_latitude: '', center_longitude: '',
                    coverage_radius_km: 15, delivery_base_fee: 50000, delivery_fee_per_km: 15000,
                    max_delivery_distance_km: 10, peak_hour_surcharge_percent: 20, min_order_amount: 0
                },
                flash: { message: '', type: 'success' },
                saving: false,
                csrf: document.querySelector('meta[name="csrf-token"]')?.content || '',

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

                async createCity() {
                    this.saving = true;
                    try {
                        const res = await this.request('{{ route("super-admin.delivery-cities.store") }}', 'POST', this.form);
                        this.cities.push({ ...res.city, zones_count: 0 });
                        this.form = { name: '', center_latitude: '', center_longitude: '', coverage_radius_km: 15, delivery_base_fee: 50000, delivery_fee_per_km: 15000, max_delivery_distance_km: 10, peak_hour_surcharge_percent: 20, min_order_amount: 0 };
                        this.showForm = false;
                        this.showFlash(res.message, 'success');
                    } catch (e) {
                        const msg = (e.errors ? Object.values(e.errors).flat()[0] : null) || e.message || 'Erreur lors de la création.';
                        this.showFlash(msg, 'error');
                    } finally {
                        this.saving = false;
                    }
                },

                async toggleCity(city) {
                    try {
                        const res = await this.request(`{{ url('/admin/villes-livraison') }}/${city.id}/toggle`, 'POST', {});
                        city.is_active = res.is_active;
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
