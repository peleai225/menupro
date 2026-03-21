<x-layouts.admin-super title="Restaurants">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-neutral-900">Restaurants</h1>
            <p class="text-neutral-500 mt-1">Gérez tous les restaurants de la plateforme.</p>
        </div>
        <div class="flex items-center gap-3">
            @if($stats['pending'] > 0)
                <span class="badge bg-amber-50 text-amber-700 border border-amber-200">{{ $stats['pending'] }} en attente</span>
            @endif
            @if(isset($stats['pending_verification']) && $stats['pending_verification'] > 0)
                <a href="{{ route('super-admin.restaurants.index', ['verification' => 'pending_verification']) }}"
                   class="badge bg-orange-50 text-orange-700 border border-orange-200 hover:bg-orange-100 transition-colors">
                    {{ $stats['pending_verification'] }} RCCM à vérifier
                </a>
            @endif
            <a href="{{ route('super-admin.restaurants.export', request()->only(['search', 'status', 'plan'])) }}"
               class="inline-flex items-center gap-2 h-9 px-4 bg-emerald-600 hover:bg-emerald-700 text-white text-sm font-medium rounded-lg transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Exporter Excel
            </a>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-4 mb-6">
        <div class="bg-white border border-neutral-200 rounded-xl p-4 shadow-sm">
            <p class="text-sm text-neutral-500">Total</p>
            <p class="text-2xl font-bold text-neutral-900">{{ number_format($stats['total']) }}</p>
        </div>
        <div class="bg-white border border-neutral-200 rounded-xl p-4 shadow-sm">
            <p class="text-sm text-neutral-500">Actifs</p>
            <p class="text-2xl font-bold text-emerald-600">{{ number_format($stats['active']) }}</p>
        </div>
        <div class="bg-white border border-neutral-200 rounded-xl p-4 shadow-sm">
            <p class="text-sm text-neutral-500">En attente</p>
            <p class="text-2xl font-bold text-amber-600">{{ number_format($stats['pending']) }}</p>
        </div>
        <div class="bg-white border border-neutral-200 rounded-xl p-4 shadow-sm">
            <p class="text-sm text-neutral-500">Suspendus</p>
            <p class="text-2xl font-bold text-red-600">{{ number_format($stats['suspended']) }}</p>
        </div>
        <div class="bg-white border border-neutral-200 rounded-xl p-4 shadow-sm">
            <p class="text-sm text-neutral-500">Expirés</p>
            <p class="text-2xl font-bold text-neutral-600">{{ number_format($stats['expired']) }}</p>
        </div>
    </div>

    <!-- Filters -->
    <form method="GET" class="bg-white border border-neutral-200 rounded-xl p-4 mb-6 shadow-sm">
        <div class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}"
                           placeholder="Rechercher un restaurant..." 
                           class="w-full h-10 pl-10 pr-4 bg-white border border-neutral-200 rounded-lg text-neutral-900 placeholder-neutral-400 focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>
            </div>
            <select name="plan" class="h-10 px-4 bg-white border border-neutral-200 rounded-lg text-neutral-900 focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="">Tous les plans</option>
                @foreach($plans as $plan)
                    <option value="{{ $plan->id }}" {{ request('plan') == $plan->id ? 'selected' : '' }}>{{ $plan->name }}</option>
                @endforeach
            </select>
            <select name="status" class="h-10 px-4 bg-white border border-neutral-200 rounded-lg text-neutral-900 focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="">Tous les statuts</option>
                <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Actif</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En attente</option>
                <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Suspendu</option>
                <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expiré</option>
            </select>
            <select name="verification" class="h-10 px-4 bg-white border border-neutral-200 rounded-lg text-neutral-900 focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="">Vérification RCCM</option>
                <option value="verified" {{ request('verification') === 'verified' ? 'selected' : '' }}>Vérifié</option>
                <option value="pending_verification" {{ request('verification') === 'pending_verification' ? 'selected' : '' }}>À vérifier</option>
                <option value="no_rccm" {{ request('verification') === 'no_rccm' ? 'selected' : '' }}>Sans RCCM</option>
            </select>
            <button type="submit" class="h-10 px-6 bg-primary-500 text-white rounded-lg font-medium hover:bg-primary-600 transition-colors">
                Filtrer
            </button>
        </div>
    </form>

    <!-- Restaurants Table -->
    <div class="bg-white border border-neutral-200 rounded-xl overflow-hidden shadow-sm">
        <div class="table-responsive">
            <table class="w-full min-w-[700px]">
                <thead class="bg-neutral-50 border-b border-neutral-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-600 uppercase tracking-wider">Restaurant</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-600 uppercase tracking-wider">Propriétaire</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-600 uppercase tracking-wider">Plan</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-600 uppercase tracking-wider">Statut</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-600 uppercase tracking-wider">Créé le</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-neutral-600 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100">
                    @forelse($restaurants as $restaurant)
                        <tr class="hover:bg-neutral-50">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-4">
                                    @if($restaurant->logo_path)
                                        <img src="{{ Storage::url($restaurant->logo_path) }}" alt="{{ $restaurant->name }}" class="w-10 h-10 rounded-xl object-cover border border-neutral-200">
                                    @else
                                        <div class="w-10 h-10 bg-gradient-to-br from-primary-500 to-accent-500 rounded-xl flex items-center justify-center text-white font-bold">
                                            {{ strtoupper(substr($restaurant->name, 0, 2)) }}
                                        </div>
                                    @endif
                                    <div>
                                        <div class="flex items-center gap-2">
                                            <span class="font-medium text-neutral-900">{{ $restaurant->name }}</span>
                                            @if($restaurant->is_verified)
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs bg-blue-50 text-blue-700 border border-blue-200" title="RCCM vérifié">
                                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                    </svg>
                                                </span>
                                            @elseif($restaurant->has_pending_verification)
                                                <span class="inline-flex items-center px-1.5 py-0.5 rounded text-xs bg-orange-50 text-orange-700 border border-orange-200" title="RCCM à vérifier">
                                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                                    </svg>
                                                </span>
                                            @endif
                                        </div>
                                        @if($restaurant->city)
                                            <p class="text-xs text-neutral-500">{{ $restaurant->city }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div>
                                    <p class="text-neutral-900">{{ $restaurant->owner?->name ?? 'N/A' }}</p>
                                    <p class="text-sm text-neutral-500">{{ $restaurant->owner?->email ?? '-' }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="badge bg-neutral-100 text-neutral-700 border border-neutral-200">{{ $restaurant->currentPlan?->name ?? 'Aucun' }}</span>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $statusColors = [
                                        'active' => 'bg-secondary-500/20 text-secondary-400',
                                        'pending' => 'bg-yellow-500/20 text-yellow-400',
                                        'suspended' => 'bg-red-500/20 text-red-400',
                                        'expired' => 'bg-neutral-500/20 text-neutral-400',
                                    ];
                                    $statusLabels = [
                                        'active' => 'Actif',
                                        'pending' => 'En attente',
                                        'suspended' => 'Suspendu',
                                        'expired' => 'Expiré',
                                    ];
                                @endphp
                                <span class="badge {{ $statusColors[$restaurant->status->value] ?? 'bg-neutral-100 text-neutral-700 border border-neutral-200' }}">
                                    {{ $statusLabels[$restaurant->status->value] ?? $restaurant->status->value }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-neutral-600">{{ $restaurant->created_at->format('d M Y') }}</td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-3">
                                    <a href="{{ route('super-admin.restaurants.show', $restaurant) }}" class="text-primary-600 hover:text-primary-700 font-medium">
                                        Voir →
                                    </a>
                                    <form method="POST" action="{{ route('super-admin.restaurants.destroy', $restaurant) }}" class="inline"
                                          onsubmit="return confirm('Supprimer « {{ addslashes($restaurant->name) }} » ? Cette action est irréversible.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-700 text-sm font-medium">
                                            Supprimer
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="text-neutral-500">
                                    <svg class="w-12 h-12 mx-auto mb-4 text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/>
                                    </svg>
                                    <p>Aucun restaurant trouvé</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    @if($restaurants->hasPages())
        <div class="mt-6">
            {{ $restaurants->links() }}
        </div>
    @endif
</x-layouts.admin-super>
