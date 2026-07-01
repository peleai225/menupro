<x-layouts.admin-super title="Zones de livraison">
    <div class="space-y-6">

        @if(session('success'))
            <div class="flex items-center gap-3 px-4 py-3 rounded-xl bg-emerald-50 border border-emerald-200 text-emerald-800 text-sm">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                {{ session('success') }}
            </div>
        @endif

        <div class="grid grid-cols-1 xl:grid-cols-3 gap-6">

            {{-- Formulaire création --}}
            <div class="bg-white rounded-2xl border border-neutral-200 shadow-sm p-6">
                <h2 class="font-semibold text-neutral-900 mb-4">Nouvelle zone</h2>
                <form method="POST" action="{{ route('super-admin.delivery-zones.store') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-xs font-medium text-neutral-600 mb-1">Nom de la zone *</label>
                        <input type="text" name="name" required placeholder="ex: Cocody" class="w-full h-10 px-3 bg-neutral-50 border border-neutral-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-neutral-600 mb-1">Ville *</label>
                        <input type="text" name="city" required placeholder="ex: Abidjan" class="w-full h-10 px-3 bg-neutral-50 border border-neutral-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-neutral-600 mb-1">Rayon (km) *</label>
                        <input type="number" name="radius_km" value="5" min="1" max="50" required class="w-full h-10 px-3 bg-neutral-50 border border-neutral-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                    </div>
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-xs font-medium text-neutral-600 mb-1">Latitude centre</label>
                            <input type="number" name="center_latitude" step="0.0000001" placeholder="5.3542" class="w-full h-10 px-3 bg-neutral-50 border border-neutral-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-neutral-600 mb-1">Longitude centre</label>
                            <input type="number" name="center_longitude" step="0.0000001" placeholder="-3.9827" class="w-full h-10 px-3 bg-neutral-50 border border-neutral-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                    </div>
                    <label class="flex items-center gap-2 cursor-pointer">
                        <input type="checkbox" name="is_active" value="1" checked class="w-4 h-4 rounded text-primary-500">
                        <span class="text-sm text-neutral-700">Zone active</span>
                    </label>
                    <button type="submit" class="w-full h-10 bg-primary-600 text-white rounded-xl text-sm font-medium hover:bg-primary-700">
                        Créer la zone
                    </button>
                </form>
            </div>

            {{-- Liste des zones --}}
            <div class="xl:col-span-2 bg-white rounded-2xl border border-neutral-200 shadow-sm overflow-hidden">
                <div class="px-5 py-4 border-b border-neutral-100 flex items-center justify-between">
                    <h2 class="font-semibold text-neutral-900">{{ $zones->count() }} zone(s)</h2>
                </div>
                <div class="divide-y divide-neutral-100">
                    @forelse($zones as $zone)
                    <div class="px-5 py-4" x-data="{ editing: false }">
                        <div x-show="!editing" class="flex items-center justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center {{ $zone->is_active ? 'bg-emerald-100 text-emerald-600' : 'bg-neutral-100 text-neutral-400' }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                </div>
                                <div>
                                    <p class="font-semibold text-neutral-900">{{ $zone->name }}</p>
                                    <p class="text-xs text-neutral-500">{{ $zone->city }} · Rayon : {{ $zone->radius_km }} km</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                @if($zone->is_active)
                                    <span class="px-2 py-0.5 text-xs rounded-full bg-emerald-100 text-emerald-700 font-medium">Active</span>
                                @else
                                    <span class="px-2 py-0.5 text-xs rounded-full bg-neutral-100 text-neutral-500 font-medium">Inactive</span>
                                @endif
                                <button @click="editing = true" class="p-1.5 rounded-lg hover:bg-neutral-100 text-neutral-500">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                </button>
                                <form method="POST" action="{{ route('super-admin.delivery-zones.toggle', $zone) }}">@csrf
                                    <button class="p-1.5 rounded-lg hover:bg-amber-50 text-amber-600" title="{{ $zone->is_active ? 'Désactiver' : 'Activer' }}">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                    </button>
                                </form>
                                <form method="POST" action="{{ route('super-admin.delivery-zones.destroy', $zone) }}" onsubmit="return confirm('Supprimer cette zone ?')">
                                    @csrf @method('DELETE')
                                    <button class="p-1.5 rounded-lg hover:bg-red-50 text-red-500">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                    </button>
                                </form>
                            </div>
                        </div>

                        {{-- Formulaire d'édition inline --}}
                        <div x-show="editing" x-cloak class="space-y-3">
                            <form method="POST" action="{{ route('super-admin.delivery-zones.update', $zone) }}" class="space-y-3">
                                @csrf @method('PUT')
                                <div class="grid grid-cols-2 gap-3">
                                    <div>
                                        <label class="block text-xs font-medium text-neutral-600 mb-1">Nom</label>
                                        <input type="text" name="name" value="{{ $zone->name }}" required class="w-full h-9 px-3 bg-neutral-50 border border-neutral-200 rounded-lg text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-neutral-600 mb-1">Ville</label>
                                        <input type="text" name="city" value="{{ $zone->city }}" required class="w-full h-9 px-3 bg-neutral-50 border border-neutral-200 rounded-lg text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-neutral-600 mb-1">Rayon (km)</label>
                                        <input type="number" name="radius_km" value="{{ $zone->radius_km }}" min="1" max="50" class="w-full h-9 px-3 bg-neutral-50 border border-neutral-200 rounded-lg text-sm">
                                    </div>
                                    <div>
                                        <label class="block text-xs font-medium text-neutral-600 mb-1">Latitude</label>
                                        <input type="number" name="center_latitude" step="0.0000001" value="{{ $zone->center_latitude }}" class="w-full h-9 px-3 bg-neutral-50 border border-neutral-200 rounded-lg text-sm">
                                    </div>
                                </div>
                                <div class="flex gap-2">
                                    <button type="submit" class="px-4 h-9 bg-primary-600 text-white rounded-lg text-sm font-medium hover:bg-primary-700">Enregistrer</button>
                                    <button type="button" @click="editing = false" class="px-4 h-9 bg-neutral-100 text-neutral-700 rounded-lg text-sm hover:bg-neutral-200">Annuler</button>
                                </div>
                            </form>
                        </div>
                    </div>
                    @empty
                    <div class="px-5 py-12 text-center text-neutral-400">Aucune zone configurée. Créez votre première zone.</div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin-super>
