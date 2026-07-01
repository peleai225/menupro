<x-layouts.admin-super title="Zones de livraison">
    <div class="space-y-6" x-data="deliveryZones()">

        {{-- Toast / feedback --}}
        <div x-show="flash.message" x-transition
             :class="flash.type === 'success' ? 'bg-emerald-50 border-emerald-200 text-emerald-800' : 'bg-red-50 border-red-200 text-red-800'"
             class="flex items-center gap-3 px-4 py-3 rounded-xl border text-sm"
             x-cloak>
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path x-show="flash.type === 'success'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                <path x-show="flash.type === 'error'" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span x-text="flash.message"></span>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

            {{-- Formulaire création --}}
            <div class="bg-white rounded-2xl border border-neutral-200 shadow-sm p-6">
                <h2 class="font-semibold text-neutral-900 mb-4">Nouvelle zone</h2>
                <form @submit.prevent="createZone" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-neutral-600 mb-1">Nom de la zone *</label>
                        <input type="text" x-model="form.name" required placeholder="ex: Cocody" class="w-full h-10 px-3 bg-neutral-50 border border-neutral-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-neutral-600 mb-1">Ville *</label>
                        <input type="text" x-model="form.city" required placeholder="ex: Abidjan" class="w-full h-10 px-3 bg-neutral-50 border border-neutral-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-neutral-600 mb-1">Rayon (km) *</label>
                        <input type="number" x-model="form.radius_km" value="5" min="1" max="50" required class="w-full h-10 px-3 bg-neutral-50 border border-neutral-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-neutral-600 mb-1">Latitude centre</label>
                            <input type="number" x-model="form.center_latitude" step="0.0000001" placeholder="5.3542" class="w-full h-10 px-3 bg-neutral-50 border border-neutral-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-neutral-600 mb-1">Longitude centre</label>
                            <input type="number" x-model="form.center_longitude" step="0.0000001" placeholder="-3.9827" class="w-full h-10 px-3 bg-neutral-50 border border-neutral-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                    </div>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" x-model="form.is_active" class="w-4 h-4 rounded text-primary-500">
                        <span class="text-sm text-neutral-700">Zone active</span>
                    </label>
                    <button type="submit" :disabled="saving" class="w-full h-10 bg-primary-600 text-white rounded-xl text-sm font-medium hover:bg-primary-700 disabled:opacity-50 flex items-center justify-center gap-2">
                        <svg x-show="saving" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        <span x-text="saving ? 'Création...' : 'Créer la zone'"></span>
                    </button>
                </form>
            </div>

            {{-- Liste des zones --}}
            <div class="xl:col-span-2 bg-white rounded-2xl border border-neutral-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-neutral-100 flex items-center justify-between">
                    <h2 class="font-semibold text-neutral-900"><span x-text="zones.length"></span> zone(s)</h2>
                </div>
                <div class="divide-y divide-neutral-100">
                    <template x-if="zones.length === 0">
                        <div class="px-5 py-12 text-center text-neutral-400">Aucune zone configurée. Créez votre première zone.</div>
                    </template>

                    <template x-for="zone in zones" :key="zone.id">
                        <div class="px-5 py-4">
                            {{-- Vue normale --}}
                            <div x-show="zone._editing !== true" class="flex items-center justify-between gap-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 rounded-xl flex items-center justify-center"
                                         :class="zone.is_active ? 'bg-emerald-100 text-emerald-600' : 'bg-neutral-100 text-neutral-400'">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    </div>
                                    <div>
                                        <p class="font-semibold text-neutral-900" x-text="zone.name"></p>
                                        <p class="text-xs text-neutral-500"><span x-text="zone.city"></span> · Rayon : <span x-text="zone.radius_km"></span> km</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2 shrink-0">
                                    <span x-show="zone.is_active" class="px-2 py-0.5 text-xs rounded-full bg-emerald-100 text-emerald-700 font-medium">Active</span>
                                    <span x-show="!zone.is_active" class="px-2 py-0.5 text-xs rounded-full bg-neutral-100 text-neutral-500 font-medium">Inactive</span>
                                    <button @click="startEdit(zone)" class="p-1.5 rounded-lg hover:bg-neutral-100 text-neutral-500" title="Modifier">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </button>
                                    <button @click="toggleZone(zone)" class="p-1.5 rounded-lg hover:bg-amber-50 text-amber-600" :title="zone.is_active ? 'Désactiver' : 'Activer'">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                    </button>
                                    <button @click="deleteZone(zone)" class="p-1.5 rounded-lg hover:bg-red-50 text-red-500" title="Supprimer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </div>
                            </div>

                            {{-- Formulaire édition inline --}}
                            <div x-show="zone._editing === true" class="space-y-3">
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-medium text-neutral-600 mb-1">Nom</label>
                                        <input type="text" x-model="zone._edit.name" required class="w-full h-9 px-3 bg-neutral-50 border border-neutral-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-neutral-600 mb-1">Ville</label>
                                        <input type="text" x-model="zone._edit.city" required class="w-full h-9 px-3 bg-neutral-50 border border-neutral-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-neutral-600 mb-1">Rayon (km)</label>
                                        <input type="number" x-model="zone._edit.radius_km" min="1" max="50" class="w-full h-9 px-3 bg-neutral-50 border border-neutral-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-neutral-600 mb-1">Latitude centre</label>
                                        <input type="number" x-model="zone._edit.center_latitude" step="0.0000001" class="w-full h-9 px-3 bg-neutral-50 border border-neutral-200 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                                    </div>
                                </div>
                                <div class="flex gap-2">
                                    <button @click="saveEdit(zone)" :disabled="zone._saving"
                                            class="px-4 h-9 bg-primary-600 text-white rounded-lg text-sm font-medium hover:bg-primary-700 disabled:opacity-50 flex items-center gap-1.5">
                                        <svg x-show="zone._saving" class="animate-spin w-3.5 h-3.5" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                                        <span x-text="zone._saving ? 'Enregistrement...' : 'Enregistrer'"></span>
                                    </button>
                                    <button type="button" @click="zone._editing = false" class="px-4 h-9 bg-neutral-100 text-neutral-700 rounded-lg text-sm hover:bg-neutral-200">Annuler</button>
                                </div>
                            </div>
                        </div>
                    </template>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script>
        function deliveryZones() {
            return {
                zones: @json($zones).map(z => ({ ...z, _editing: false, _edit: {}, _saving: false })),
                form: { name: '', city: '', radius_km: 5, center_latitude: '', center_longitude: '', is_active: true },
                flash: { message: '', type: 'success' },
                saving: false,
                csrf: document.querySelector('meta[name="csrf-token"]') ? document.querySelector('meta[name="csrf-token"]').content : '',

                showFlash(msg, type) {
                    this.flash = { message: msg, type: type || 'success' };
                    clearTimeout(this._flashTimer);
                    this._flashTimer = setTimeout(() => { this.flash.message = ''; }, 4000);
                    adminToast(msg, type || 'success');
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

                async createZone() {
                    this.saving = true;
                    try {
                        const res = await this.request('{{ route('super-admin.delivery-zones.store') }}', 'POST', {
                            ...this.form,
                            is_active: this.form.is_active ? 1 : 0
                        });
                        this.zones.push({ ...res.zone, _editing: false, _edit: {}, _saving: false });
                        this.form = { name: '', city: '', radius_km: 5, center_latitude: '', center_longitude: '', is_active: true };
                        this.showFlash(res.message, 'success');
                    } catch (e) {
                        const msg = (e.errors ? Object.values(e.errors).flat()[0] : null) || e.message || 'Erreur lors de la création.';
                        this.showFlash(msg, 'error');
                    } finally {
                        this.saving = false;
                    }
                },

                startEdit(zone) {
                    zone._edit = { name: zone.name, city: zone.city, radius_km: zone.radius_km, center_latitude: zone.center_latitude, center_longitude: zone.center_longitude };
                    zone._editing = true;
                    zone._saving = false;
                },

                async saveEdit(zone) {
                    zone._saving = true;
                    try {
                        const res = await this.request(`{{ url('/admin/zones-livraison') }}/${zone.id}`, 'PUT', zone._edit);
                        Object.assign(zone, res.zone, { _editing: false, _edit: {}, _saving: false });
                        this.showFlash(res.message, 'success');
                    } catch (e) {
                        const msg = (e.errors ? Object.values(e.errors).flat()[0] : null) || e.message || 'Erreur lors de la mise à jour.';
                        this.showFlash(msg, 'error');
                        zone._saving = false;
                    }
                },

                async toggleZone(zone) {
                    try {
                        const res = await this.request(`{{ url('/admin/zones-livraison') }}/${zone.id}/toggle`, 'POST', {});
                        zone.is_active = res.is_active;
                        this.showFlash(res.message, 'success');
                    } catch (e) {
                        this.showFlash(e.message || 'Erreur.', 'error');
                    }
                },

                async deleteZone(zone) {
                    if (!confirm('Supprimer la zone "' + zone.name + '" ?')) return;
                    try {
                        const res = await this.request(`{{ url('/admin/zones-livraison') }}/${zone.id}`, 'DELETE', {});
                        this.zones = this.zones.filter(z => z.id !== zone.id);
                        this.showFlash(res.message, 'success');
                    } catch (e) {
                        this.showFlash(e.message || 'Erreur lors de la suppression.', 'error');
                    }
                }
            }
        }
    </script>
    @endpush
</x-layouts.admin-super>
