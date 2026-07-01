<x-layouts.admin-super title="Clients">
    <div class="space-y-6">

        {{-- Stats --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="bg-white rounded-2xl p-4 border border-neutral-200 shadow-sm">
                <p class="text-xs text-neutral-500">Total clients</p>
                <p class="text-2xl font-bold text-neutral-900 mt-1">{{ $stats['total'] }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 border border-neutral-200 shadow-sm">
                <p class="text-xs text-neutral-500">Actifs</p>
                <p class="text-2xl font-bold text-emerald-600 mt-1">{{ $stats['active'] }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 border border-neutral-200 shadow-sm">
                <p class="text-xs text-neutral-500">Commandes aujourd'hui</p>
                <p class="text-2xl font-bold text-primary-600 mt-1">{{ $stats['ordered_today'] }}</p>
            </div>
            <div class="bg-white rounded-2xl p-4 border border-neutral-200 shadow-sm">
                <p class="text-xs text-neutral-500">Nouveaux ce mois</p>
                <p class="text-2xl font-bold text-indigo-600 mt-1">{{ $stats['new_this_month'] }}</p>
            </div>
        </div>

        {{-- Filtres --}}
        <form method="GET" class="flex flex-wrap gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Nom, email, téléphone..."
                   class="flex-1 min-w-[200px] h-10 px-4 bg-white border border-neutral-200 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
            <select name="city" class="h-10 px-3 bg-white border border-neutral-200 rounded-xl text-sm">
                <option value="">Toutes les villes</option>
                @foreach($cities as $city)
                    <option value="{{ $city }}" {{ request('city') === $city ? 'selected' : '' }}>{{ $city }}</option>
                @endforeach
            </select>
            <select name="status" class="h-10 px-3 bg-white border border-neutral-200 rounded-xl text-sm">
                <option value="">Tous</option>
                <option value="active"   {{ request('status') === 'active'   ? 'selected' : '' }}>Actifs</option>
                <option value="inactive" {{ request('status') === 'inactive' ? 'selected' : '' }}>Inactifs</option>
            </select>
            <button class="h-10 px-5 bg-primary-600 text-white rounded-xl text-sm font-medium hover:bg-primary-700">Filtrer</button>
        </form>

        {{-- Tableau --}}
        <div class="bg-white rounded-2xl border border-neutral-200 shadow-sm overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-neutral-50 border-b border-neutral-200">
                        <tr>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-500 uppercase">Client</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-500 uppercase">Téléphone</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-500 uppercase">Ville</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-500 uppercase">Commandes</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-500 uppercase">Dernière commande</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-500 uppercase">Statut</th>
                            <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-500 uppercase">Inscrit le</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100">
                        @forelse($customers as $customer)
                        <tr class="hover:bg-neutral-50/50">
                            <td class="px-4 py-3">
                                <div class="flex items-center gap-3">
                                    <div class="w-9 h-9 rounded-xl bg-gradient-to-br from-indigo-400 to-indigo-600 flex items-center justify-center text-white font-bold text-sm flex-shrink-0">
                                        {{ strtoupper(substr($customer->user->name ?? '?', 0, 1)) }}
                                    </div>
                                    <div>
                                        <p class="font-semibold text-neutral-900">{{ $customer->user->name ?? '—' }}</p>
                                        <p class="text-xs text-neutral-500">{{ $customer->user->email ?? '—' }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="px-4 py-3 text-neutral-600">{{ $customer->phone ?? '—' }}</td>
                            <td class="px-4 py-3 text-neutral-600">{{ $customer->city ?? '—' }}</td>
                            <td class="px-4 py-3 font-medium text-neutral-900">{{ $customer->total_orders ?? 0 }}</td>
                            <td class="px-4 py-3 text-neutral-500 text-xs">
                                {{ $customer->last_order_at ? $customer->last_order_at->diffForHumans() : '—' }}
                            </td>
                            <td class="px-4 py-3">
                                @if($customer->is_active)
                                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">Actif</span>
                                @else
                                    <span class="px-2 py-1 rounded-full text-xs font-medium bg-neutral-100 text-neutral-500">Inactif</span>
                                @endif
                            </td>
                            <td class="px-4 py-3 text-neutral-500 text-xs">{{ $customer->created_at->format('d/m/Y') }}</td>
                        </tr>
                        @empty
                        <tr><td colspan="7" class="px-4 py-12 text-center text-neutral-400">Aucun client trouvé.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if($customers->hasPages())
                <div class="px-4 py-3 border-t border-neutral-100">{{ $customers->links() }}</div>
            @endif
        </div>
    </div>
</x-layouts.admin-super>
