<x-layouts.admin-super title="Commandes">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-neutral-900">Commandes</h1>
            <p class="text-neutral-500 mt-1">Historique et gestion de toutes les commandes de la plateforme.</p>
        </div>
        @if($stats['pending'] > 0)
            <span class="badge bg-amber-50 text-amber-700 border border-amber-200">{{ $stats['pending'] }} en cours</span>
        @endif
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="bg-white border border-neutral-200 rounded-xl p-4 shadow-sm">
            <p class="text-sm text-neutral-500">Total commandes</p>
            <p class="text-2xl font-bold text-neutral-900 mt-1">{{ number_format($stats['total']) }}</p>
        </div>
        <div class="bg-white border border-neutral-200 rounded-xl p-4 shadow-sm">
            <p class="text-sm text-neutral-500">Aujourd'hui</p>
            <p class="text-2xl font-bold text-primary-600 mt-1">{{ number_format($stats['today']) }}</p>
        </div>
        <div class="bg-white border border-neutral-200 rounded-xl p-4 shadow-sm">
            <p class="text-sm text-neutral-500">Revenus ce mois (FCFA)</p>
            <p class="text-2xl font-bold text-emerald-600 mt-1">{{ number_format($stats['revenue_month'], 0, ',', ' ') }}</p>
        </div>
        <div class="bg-white border border-neutral-200 rounded-xl p-4 shadow-sm">
            <p class="text-sm text-neutral-500">En cours</p>
            <p class="text-2xl font-bold {{ $stats['pending'] > 0 ? 'text-amber-600' : 'text-neutral-600' }} mt-1">
                {{ number_format($stats['pending']) }}
            </p>
        </div>
    </div>

    <!-- Filters -->
    <form method="GET" class="bg-white border border-neutral-200 rounded-xl p-4 mb-6 shadow-sm">
        <div class="flex flex-wrap gap-3">
            <div class="flex-1 min-w-[200px]">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text"
                           name="search"
                           value="{{ request('search') }}"
                           placeholder="Référence, client, téléphone..."
                           class="w-full h-10 pl-10 pr-4 bg-white border border-neutral-200 rounded-lg text-neutral-900 placeholder-neutral-400 focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>
            </div>
            <select name="status" class="h-10 px-4 bg-white border border-neutral-200 rounded-lg text-neutral-900 focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="">Tous les statuts</option>
                @foreach($statuses as $status)
                    <option value="{{ $status->value }}" {{ request('status') === $status->value ? 'selected' : '' }}>
                        {{ $status->label() }}
                    </option>
                @endforeach
            </select>
            <select name="restaurant_id" class="h-10 px-4 bg-white border border-neutral-200 rounded-lg text-neutral-900 focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="">Tous les restaurants</option>
                @foreach($restaurants as $restaurant)
                    <option value="{{ $restaurant->id }}" {{ request('restaurant_id') == $restaurant->id ? 'selected' : '' }}>
                        {{ $restaurant->name }}
                    </option>
                @endforeach
            </select>
            <input type="date"
                   name="date_from"
                   value="{{ request('date_from') }}"
                   class="h-10 px-4 bg-white border border-neutral-200 rounded-lg text-neutral-900 focus:outline-none focus:ring-2 focus:ring-primary-500">
            <input type="date"
                   name="date_to"
                   value="{{ request('date_to') }}"
                   class="h-10 px-4 bg-white border border-neutral-200 rounded-lg text-neutral-900 focus:outline-none focus:ring-2 focus:ring-primary-500">
            <button type="submit" class="h-10 px-6 bg-primary-500 text-white rounded-lg font-medium hover:bg-primary-600 transition-colors">
                Filtrer
            </button>
            @if(request()->anyFilled(['search', 'status', 'restaurant_id', 'date_from', 'date_to']))
                <a href="{{ route('super-admin.orders.index') }}"
                   class="h-10 px-4 flex items-center text-sm text-neutral-500 hover:text-neutral-700 border border-neutral-200 rounded-lg bg-white transition-colors">
                    Réinitialiser
                </a>
            @endif
        </div>
    </form>

    <!-- Orders Table -->
    <div class="bg-white border border-neutral-200 rounded-xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[900px] text-sm">
                <thead class="bg-neutral-50 border-b border-neutral-200">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-600 uppercase tracking-wider">Référence</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-600 uppercase tracking-wider">Restaurant</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-600 uppercase tracking-wider">Client</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-600 uppercase tracking-wider">Statut</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-600 uppercase tracking-wider">Montant</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-600 uppercase tracking-wider">Livreur</th>
                        <th class="px-4 py-3 text-left text-xs font-semibold text-neutral-600 uppercase tracking-wider">Date</th>
                        <th class="px-4 py-3 text-right text-xs font-semibold text-neutral-600 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100">
                    @forelse($orders as $order)
                        @php
                            $statusColors = [
                                'draft'           => 'bg-neutral-100 text-neutral-600 border-neutral-200',
                                'pending_payment' => 'bg-amber-50 text-amber-700 border-amber-200',
                                'paid'            => 'bg-blue-50 text-blue-700 border-blue-200',
                                'confirmed'       => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                'preparing'       => 'bg-violet-50 text-violet-700 border-violet-200',
                                'ready'           => 'bg-cyan-50 text-cyan-700 border-cyan-200',
                                'delivering'      => 'bg-orange-50 text-orange-700 border-orange-200',
                                'completed'       => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                'cancelled'       => 'bg-red-50 text-red-700 border-red-200',
                                'refunded'        => 'bg-neutral-100 text-neutral-600 border-neutral-200',
                            ];
                        @endphp
                        <tr class="hover:bg-neutral-50 transition-colors">
                            <td class="px-4 py-3">
                                <span class="font-mono font-medium text-neutral-900">{{ $order->reference }}</span>
                            </td>
                            <td class="px-4 py-3 text-neutral-700">
                                {{ $order->restaurant?->name ?? '—' }}
                            </td>
                            <td class="px-4 py-3">
                                <div>
                                    <p class="text-neutral-900 font-medium">{{ $order->customer_name ?? '—' }}</p>
                                    @if($order->customer_phone)
                                        <p class="text-xs text-neutral-500">{{ $order->customer_phone }}</p>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3">
                                <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $statusColors[$order->status->value] ?? 'bg-neutral-100 text-neutral-600 border-neutral-200' }}">
                                    {{ $order->status->label() }}
                                </span>
                            </td>
                            <td class="px-4 py-3 font-semibold text-neutral-900">
                                {{ number_format($order->total, 0, ',', ' ') }} FCFA
                            </td>
                            <td class="px-4 py-3 text-neutral-600">
                                {{ $order->delivery?->driver?->name ?? '—' }}
                            </td>
                            <td class="px-4 py-3 text-neutral-500">
                                {{ $order->created_at->format('d M Y') }}
                                <span class="block text-xs text-neutral-400">{{ $order->created_at->format('H:i') }}</span>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <a href="{{ route('super-admin.orders.show', $order->id) }}"
                                   class="text-primary-600 hover:text-primary-700 font-medium">
                                    Voir →
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <div class="text-neutral-500">
                                    <svg class="w-12 h-12 mx-auto mb-4 text-neutral-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                    </svg>
                                    <p>Aucune commande trouvée</p>
                                </div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    @if($orders->hasPages())
        <div class="mt-6">
            {{ $orders->withQueryString()->links() }}
        </div>
    @endif
</x-layouts.admin-super>
