<x-layouts.admin-super title="Villes de livraison">
    <div class="space-y-6" x-data="deliveryCities()">

        {{-- Toast --}}
        <div x-show="flash.message" x-transition
             class="flex items-center gap-3 px-4 py-3 rounded-xl border text-sm"
             :style="flash.type === 'success'
                ? 'background:color-mix(in oklch,var(--sa-success) 10%,transparent);border-color:color-mix(in oklch,var(--sa-success) 20%,transparent);color:var(--sa-success);'
                : 'background:color-mix(in oklch,var(--sa-danger) 10%,transparent);border-color:color-mix(in oklch,var(--sa-danger) 20%,transparent);color:var(--sa-danger);'"
             x-cloak>
            <svg class="size-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path x-show="flash.type === 'success'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                <path x-show="flash.type !== 'success'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
            </svg>
            <span x-text="flash.message"></span>
        </div>

        {{-- Stats --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Villes actives --}}
            <div class="rounded-2xl border p-5 shadow-sm transition hover:shadow-md" style="border-color:var(--sa-border);background:var(--sa-card);">
                <div class="flex items-start justify-between">
                    <span class="flex size-11 items-center justify-center rounded-xl" style="background:color-mix(in oklch,var(--sa-primary) 10%,transparent);color:var(--sa-primary);">
                        <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    </span>
                </div>
                <p class="mt-4 text-3xl font-bold" style="color:var(--sa-fg);" x-text="cities.filter(c=>c.is_active).length"></p>
                <p class="mt-1 text-sm" style="color:var(--sa-muted-fg);">Villes actives</p>
            </div>
            {{-- Livreurs déployés --}}
            <div class="rounded-2xl border p-5 shadow-sm transition hover:shadow-md" style="border-color:var(--sa-border);background:var(--sa-card);">
                <div class="flex items-start justify-between">
                    <span class="flex size-11 items-center justify-center rounded-xl" style="background:color-mix(in oklch,var(--sa-success) 10%,transparent);color:var(--sa-success);">
                        <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="1" y="3" width="15" height="13" rx="1"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8h4l3 5v3h-7V8zM5.5 21a1.5 1.5 0 100-3 1.5 1.5 0 000 3zM18.5 21a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"/></svg>
                    </span>
                </div>
                <p class="mt-4 text-3xl font-bold" style="color:var(--sa-fg);" x-text="cities.reduce((s,c)=>s+(c.zones_count||0),0)"></p>
                <p class="mt-1 text-sm" style="color:var(--sa-muted-fg);">Livreurs déployés</p>
            </div>
            {{-- Restaurants --}}
            <div class="rounded-2xl border p-5 shadow-sm transition hover:shadow-md" style="border-color:var(--sa-border);background:var(--sa-card);">
                <div class="flex items-start justify-between">
                    <span class="flex size-11 items-center justify-center rounded-xl" style="background:color-mix(in oklch,var(--sa-info) 10%,transparent);color:var(--sa-info);">
                        <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </span>
                </div>
                <p class="mt-4 text-3xl font-bold" style="color:var(--sa-fg);">{{ count($cities) }}</p>
                <p class="mt-1 text-sm" style="color:var(--sa-muted-fg);">Restaurants</p>
            </div>
            {{-- Commandes livrées --}}
            <div class="rounded-2xl border p-5 shadow-sm transition hover:shadow-md" style="border-color:var(--sa-border);background:var(--sa-card);">
                <div class="flex items-start justify-between">
                    <span class="flex size-11 items-center justify-center rounded-xl" style="background:color-mix(in oklch,var(--sa-warning) 10%,transparent);color:var(--sa-warning);">
                        <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><polyline points="12 6 12 12 16 14"/></svg>
                    </span>
                </div>
                <p class="mt-4 text-3xl font-bold" style="color:var(--sa-fg);">{{ count($cities) }}</p>
                <p class="mt-1 text-sm" style="color:var(--sa-muted-fg);">Commandes livrées</p>
            </div>
        </div>

        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-xl font-bold" style="color:var(--sa-fg);">Villes de livraison</h1>
                <p class="text-sm mt-1" style="color:var(--sa-muted-fg);">Gérez les villes où la livraison est active et configurez la tarification par ville.</p>
            </div>
            <button @click="showForm = !showForm"
                    class="h-10 px-4 rounded-xl text-sm font-medium flex items-center gap-2 transition-opacity hover:opacity-90"
                    style="background:var(--sa-primary);color:var(--sa-primary-fg);">
                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Ajouter une ville
            </button>
        </div>

        {{-- Formulaire création --}}
        <div x-show="showForm" x-transition x-cloak
             class="rounded-2xl border shadow-sm p-6"
             style="border-color:var(--sa-border);background:var(--sa-card);">
            <h2 class="font-semibold mb-4" style="color:var(--sa-fg);">Nouvelle ville</h2>
            <form @submit.prevent="createCity" class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div>
                    <label class="block text-xs font-medium mb-1" style="color:var(--sa-muted-fg);">Nom de la ville *</label>
                    <input type="text" x-model="form.name" required placeholder="ex: Abidjan"
                           class="w-full h-10 px-3 rounded-xl border text-sm focus:outline-none"
                           style="background:color-mix(in oklch,var(--sa-muted) 30%,transparent);border-color:var(--sa-border);color:var(--sa-fg);">
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1" style="color:var(--sa-muted-fg);">Latitude centre *</label>
                    <input type="number" x-model="form.center_latitude" step="0.0000001" required placeholder="5.3600"
                           class="w-full h-10 px-3 rounded-xl border text-sm focus:outline-none"
                           style="background:color-mix(in oklch,var(--sa-muted) 30%,transparent);border-color:var(--sa-border);color:var(--sa-fg);">
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1" style="color:var(--sa-muted-fg);">Longitude centre *</label>
                    <input type="number" x-model="form.center_longitude" step="0.0000001" required placeholder="-4.0083"
                           class="w-full h-10 px-3 rounded-xl border text-sm focus:outline-none"
                           style="background:color-mix(in oklch,var(--sa-muted) 30%,transparent);border-color:var(--sa-border);color:var(--sa-fg);">
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1" style="color:var(--sa-muted-fg);">Rayon de couverture (km) *</label>
                    <input type="number" x-model="form.coverage_radius_km" min="1" max="100" required
                           class="w-full h-10 px-3 rounded-xl border text-sm focus:outline-none"
                           style="background:color-mix(in oklch,var(--sa-muted) 30%,transparent);border-color:var(--sa-border);color:var(--sa-fg);">
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1" style="color:var(--sa-muted-fg);">Frais de base (FCFA) *</label>
                    <input type="number" x-model="form.delivery_base_fee" min="0" required
                           class="w-full h-10 px-3 rounded-xl border text-sm focus:outline-none"
                           style="background:color-mix(in oklch,var(--sa-muted) 30%,transparent);border-color:var(--sa-border);color:var(--sa-fg);">
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1" style="color:var(--sa-muted-fg);">Frais par km (FCFA) *</label>
                    <input type="number" x-model="form.delivery_fee_per_km" min="0" required
                           class="w-full h-10 px-3 rounded-xl border text-sm focus:outline-none"
                           style="background:color-mix(in oklch,var(--sa-muted) 30%,transparent);border-color:var(--sa-border);color:var(--sa-fg);">
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1" style="color:var(--sa-muted-fg);">Distance max resto→client (km) *</label>
                    <input type="number" x-model="form.max_delivery_distance_km" min="1" max="50" required
                           class="w-full h-10 px-3 rounded-xl border text-sm focus:outline-none"
                           style="background:color-mix(in oklch,var(--sa-muted) 30%,transparent);border-color:var(--sa-border);color:var(--sa-fg);">
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1" style="color:var(--sa-muted-fg);">Surcharge heures pointe (%)</label>
                    <input type="number" x-model="form.peak_hour_surcharge_percent" min="0" max="100"
                           class="w-full h-10 px-3 rounded-xl border text-sm focus:outline-none"
                           style="background:color-mix(in oklch,var(--sa-muted) 30%,transparent);border-color:var(--sa-border);color:var(--sa-fg);">
                </div>
                <div>
                    <label class="block text-xs font-medium mb-1" style="color:var(--sa-muted-fg);">Commande minimum (FCFA)</label>
                    <input type="number" x-model="form.min_order_amount" min="0"
                           class="w-full h-10 px-3 rounded-xl border text-sm focus:outline-none"
                           style="background:color-mix(in oklch,var(--sa-muted) 30%,transparent);border-color:var(--sa-border);color:var(--sa-fg);">
                </div>
                <div class="md:col-span-2 lg:col-span-3 flex gap-3">
                    <button type="submit" :disabled="saving"
                            class="h-10 px-6 rounded-xl text-sm font-medium flex items-center gap-2 disabled:opacity-50"
                            style="background:var(--sa-primary);color:var(--sa-primary-fg);">
                        <svg x-show="saving" class="animate-spin size-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        <span x-text="saving ? 'Création...' : 'Créer la ville'"></span>
                    </button>
                    <button type="button" @click="showForm = false"
                            class="h-10 px-4 rounded-xl text-sm"
                            style="background:color-mix(in oklch,var(--sa-muted) 50%,transparent);color:var(--sa-fg);">Annuler</button>
                </div>
            </form>
        </div>

        {{-- Grille des villes --}}
        <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-4">
            <template x-for="city in cities" :key="city.id">
                <div class="rounded-2xl border flex flex-col gap-4 p-5 shadow-sm transition hover:shadow-md" style="border-color:var(--sa-border);background:var(--sa-card);">
                    <div class="flex items-start justify-between">
                        <div class="flex items-center gap-3">
                            <span class="flex size-11 items-center justify-center rounded-xl" style="background:color-mix(in oklch,var(--sa-primary) 10%,transparent);color:var(--sa-primary);">
                                <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            </span>
                            <div>
                                <h3 class="font-semibold" style="color:var(--sa-fg);" x-text="city.name"></h3>
                                <p class="text-sm" style="color:var(--sa-muted-fg);">Rayon : <span x-text="city.coverage_radius_km"></span> km</p>
                            </div>
                        </div>
                        {{-- Status badge + toggle --}}
                        <div class="flex items-center gap-2">
                            <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs font-medium whitespace-nowrap"
                                  :style="city.is_active
                                    ? 'background:color-mix(in oklch,var(--sa-success) 10%,transparent);color:var(--sa-success);border-color:color-mix(in oklch,var(--sa-success) 20%,transparent);'
                                    : 'background:color-mix(in oklch,var(--sa-muted-fg) 10%,transparent);color:var(--sa-muted-fg);border-color:color-mix(in oklch,var(--sa-muted-fg) 20%,transparent);'">
                                <span class="size-1.5 rounded-full bg-current"></span>
                                <span x-text="city.is_active ? 'Active' : 'Inactive'"></span>
                            </span>
                            <button @click="toggleCity(city)"
                                    class="relative w-11 h-6 rounded-full transition-colors duration-200"
                                    :style="city.is_active ? 'background:var(--sa-success);' : 'background:color-mix(in oklch,var(--sa-muted-fg) 40%,transparent);'">
                                <span class="absolute top-0.5 left-0.5 size-5 bg-white rounded-full shadow transition-transform duration-200"
                                      :class="city.is_active ? 'translate-x-5' : ''"></span>
                            </button>
                        </div>
                    </div>

                    <div class="grid grid-cols-3 gap-2 rounded-xl p-3 text-center" style="background:color-mix(in oklch,var(--sa-muted) 50%,transparent);">
                        <div>
                            <p class="text-lg font-bold" style="color:var(--sa-fg);" x-text="city.zones_count || 0"></p>
                            <p class="text-xs" style="color:var(--sa-muted-fg);">Zones</p>
                        </div>
                        <div>
                            <p class="text-lg font-bold" style="color:var(--sa-fg);" x-text="(city.delivery_base_fee / 100).toFixed(0)"></p>
                            <p class="text-xs" style="color:var(--sa-muted-fg);">Base FCFA</p>
                        </div>
                        <div>
                            <p class="text-lg font-bold" style="color:var(--sa-fg);" x-text="(city.delivery_fee_per_km / 100).toFixed(0)"></p>
                            <p class="text-xs" style="color:var(--sa-muted-fg);">/km FCFA</p>
                        </div>
                    </div>

                    <div class="flex items-center justify-between pt-3" style="border-top:1px solid var(--sa-border);">
                        <span class="text-sm" style="color:var(--sa-muted-fg);">Frais de livraison</span>
                        <span class="font-semibold" style="color:var(--sa-fg);" x-text="(city.delivery_base_fee / 100).toFixed(0) + ' FCFA'"></span>
                    </div>

                    <a :href="'{{ url('/admin/villes-livraison') }}/' + city.id"
                       class="block w-full text-center h-9 leading-9 rounded-xl text-sm font-medium transition-colors"
                       style="background:color-mix(in oklch,var(--sa-muted) 50%,transparent);color:var(--sa-fg);"
                       onmouseover="this.style.background='color-mix(in oklch,var(--sa-primary) 10%,transparent)';this.style.color='var(--sa-primary)';"
                       onmouseout="this.style.background='color-mix(in oklch,var(--sa-muted) 50%,transparent)';this.style.color='var(--sa-fg)';">
                        Configurer
                    </a>
                </div>
            </template>
        </div>

        <template x-if="cities.length === 0">
            <div class="rounded-2xl border shadow-sm p-12 text-center" style="border-color:var(--sa-border);background:var(--sa-card);">
                <svg class="size-12 mx-auto mb-4" style="color:var(--sa-muted-fg);opacity:.4;" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/></svg>
                <p style="color:var(--sa-muted-fg);">Aucune ville configurée. Ajoutez votre première ville pour activer la livraison.</p>
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
