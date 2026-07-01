<x-layouts.admin-super title="Livreurs">
    <div class="space-y-6">

        {{-- Stats --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl p-4 border border-neutral-200 shadow-sm">
                <p class="text-xs text-neutral-500">Total livreurs</p>
                <p class="text-2xl font-bold text-neutral-900 mt-1">{{ $stats['total'] }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 border border-neutral-200 shadow-sm">
                <p class="text-xs text-neutral-500">Approuvés</p>
                <p class="text-2xl font-bold text-emerald-600 mt-1">{{ $stats['approved'] }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 border border-neutral-200 shadow-sm">
                <p class="text-xs text-neutral-500">En attente</p>
                <p class="text-2xl font-bold text-amber-500 mt-1">{{ $stats['pending'] }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 border border-neutral-200 shadow-sm">
                <p class="text-xs text-neutral-500">En ligne maintenant</p>
                <p class="text-2xl font-bold text-primary-600 mt-1">{{ $stats['online'] }}</p>
            </div>
        </div>

        {{-- Filtres --}}
        <form method="GET" class="flex flex-wrap gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Nom, téléphone, email..."
                   class="flex-1 min-w-[200px] h-10 px-4 bg-white border border-neutral-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
            <select name="status" class="h-10 px-3 bg-white border border-neutral-200 rounded-xl text-sm">
                <option value="">Tous les statuts</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approuvés</option>
                <option value="pending"  {{ request('status') === 'pending'  ? 'selected' : '' }}>En attente</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejetés</option>
                <option value="suspended"{{ request('status') === 'suspended'? 'selected' : '' }}>Suspendus</option>
                <option value="online"   {{ request('status') === 'online'   ? 'selected' : '' }}>En ligne</option>
            </select>
            <select name="city" class="h-10 px-3 bg-white border border-neutral-200 rounded-xl text-sm">
                <option value="">Toutes les villes</option>
                @foreach($cities as $city)
                    <option value="{{ $city }}" {{ request('city') === $city ? 'selected' : '' }}>{{ $city }}</option>
                @endforeach
            </select>
            <button class="h-10 px-5 bg-primary-600 text-white rounded-xl text-sm font-medium hover:bg-primary-700">Filtrer</button>
            @if(request()->anyFilled(['search','status','city']))
                <a href="{{ route('super-admin.drivers.index') }}" class="h-10 px-4 flex items-center text-sm text-neutral-500 hover:text-neutral-700 border border-neutral-200 rounded-xl bg-white">Réinitialiser</a>
            @endif
        </form>

        {{-- Tableau --}}
        <div class="bg-white rounded-2xl border border-neutral-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-neutral-50 border-b border-neutral-200">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-500 uppercase">Livreur</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-500 uppercase">Ville</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-500 uppercase">Véhicule</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-500 uppercase">Statut</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-500 uppercase">Livraisons</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-500 uppercase">Note</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-500 uppercase">Gains</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-500 uppercase">Actions</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100">
                        @forelse($drivers as $driver)
                        <tr class="hover:bg-neutral-50/50">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                                        {{ strtoupper(substr($driver->name, 0, 1)) }}
                                    </div>
                                    <div>
                                        <a href="{{ route('super-admin.drivers.show', $driver) }}" class="font-semibold text-neutral-900 hover:text-primary-600">{{ $driver->name }}</a>
                                        <p class="text-xs text-neutral-500">{{ $driver->phone }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-neutral-600">{{ $driver->city ?? '—' }}</td>
                            <td class="px-4 py-3 text-neutral-600">{{ $driver->vehicle_type ?? '—' }}</td>
                            <td class="px-4 py-3">
                                @if($driver->verification_status === 'approved' && $driver->is_active && $driver->is_available)
                                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">
                                        <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full animate-pulse"></span> En ligne
                                    </span>
                                @elseif($driver->verification_status === 'approved')
                                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-blue-100 text-blue-700">Approuvé</span>
                                @elseif($driver->verification_status === 'pending')
                                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-amber-100 text-amber-700">En attente</span>
                                @elseif($driver->verification_status === 'rejected')
                                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-red-100 text-red-700">Rejeté</span>
                                @else
                                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-neutral-100 text-neutral-600">Suspendu</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-neutral-600">{{ $driver->total_deliveries ?? 0 }}</td>
                            <td class="px-4 py-3">
                                @if($driver->rating)
                                    <span class="text-amber-500 font-medium">★ {{ number_format($driver->rating, 1) }}</span>
                                @else
                                    <span class="text-neutral-400">—</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 font-medium text-neutral-900">{{ number_format($driver->total_earnings_xof ?? 0) }} F</td>
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-1">
                                    <a href="{{ route('super-admin.drivers.show', $driver) }}" class="p-1.5 rounded-lg hover:bg-neutral-100 text-neutral-500" title="Voir détail">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    </a>
                                    @if($driver->verification_status === 'pending')
                                        <form method="POST" action="{{ route('super-admin.drivers.approve', $driver) }}">@csrf
                                            <button class="p-1.5 rounded-lg hover:bg-emerald-50 text-emerald-600" title="Approuver">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                            </button>
                                        </form>
                                        <form method="POST" action="{{ route('super-admin.drivers.reject', $driver) }}">@csrf
                                            <button class="p-1.5 rounded-lg hover:bg-red-50 text-red-500" title="Rejeter">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                            </button>
                                        </form>
                                    @elseif($driver->is_active)
                                        <form method="POST" action="{{ route('super-admin.drivers.suspend', $driver) }}">@csrf
                                            <button class="p-1.5 rounded-lg hover:bg-amber-50 text-amber-600" title="Suspendre">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6"/></svg>
                                            </button>
                                        </form>
                                    @else
                                        <form method="POST" action="{{ route('super-admin.drivers.reactivate', $driver) }}">@csrf
                                            <button class="p-1.5 rounded-lg hover:bg-emerald-50 text-emerald-600" title="Réactiver">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/></svg>
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                        @empty
                        <tr><td colspan="8" class="px-4 py-12 text-center text-neutral-400">Aucun livreur trouvé.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($drivers->hasPages())
                <div class="px-4 py-3 border-t border-neutral-100">{{ $drivers->links() }}</div>
            @endif
        </div>
    </div>
</x-layouts.admin-super>
