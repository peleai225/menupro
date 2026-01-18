<x-layouts.admin-restaurant title="Clients" :restaurant="$restaurant" :subscription="$restaurant->activeSubscription ?? null">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-neutral-900">Clients</h1>
            <p class="text-neutral-500 mt-1">Historique de vos clients et leurs commandes.</p>
        </div>
        <a href="{{ route('restaurant.customers.export') }}" class="btn btn-outline">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
            </svg>
            Exporter
        </a>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="card p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-neutral-500">Total clients</p>
                    <p class="text-3xl font-bold text-neutral-900 mt-1">{{ number_format($stats['total_customers']) }}</p>
                </div>
                <div class="w-12 h-12 bg-primary-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
        </div>
        <div class="card p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-neutral-500">Nouveaux ce mois</p>
                    <p class="text-3xl font-bold text-neutral-900 mt-1">{{ number_format($stats['new_this_month']) }}</p>
                </div>
                <div class="w-12 h-12 bg-secondary-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-secondary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                    </svg>
                </div>
            </div>
        </div>
        <div class="card p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-neutral-500">Taux de fidélité</p>
                    <p class="text-3xl font-bold text-neutral-900 mt-1">{{ $stats['returning_rate'] }}%</p>
                </div>
                <div class="w-12 h-12 bg-accent-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-accent-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Search -->
    <form method="GET" class="card p-4 mb-6">
        <div class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1 relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}"
                       placeholder="Rechercher un client..." 
                       class="w-full h-10 pl-10 pr-4 bg-neutral-50 border border-neutral-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
            </div>
            <select name="sort" class="h-10 px-4 bg-neutral-50 border border-neutral-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="total_spent" {{ request('sort', 'total_spent') === 'total_spent' ? 'selected' : '' }}>Plus gros acheteurs</option>
                <option value="orders_count" {{ request('sort') === 'orders_count' ? 'selected' : '' }}>Plus de commandes</option>
                <option value="last_order_at" {{ request('sort') === 'last_order_at' ? 'selected' : '' }}>Commande récente</option>
            </select>
            <button type="submit" class="btn-primary">Filtrer</button>
        </div>
    </form>

    <!-- Customers Table -->
    <div class="card overflow-hidden">
        <div class="table-responsive">
            <table class="w-full min-w-[600px]">
                <thead class="bg-neutral-50 border-b border-neutral-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-500 uppercase tracking-wider">Client</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-500 uppercase tracking-wider">Contact</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-500 uppercase tracking-wider">Commandes</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-500 uppercase tracking-wider">Total dépensé</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-500 uppercase tracking-wider">Dernière commande</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100">
                    @forelse($customers as $customer)
                        <tr class="hover:bg-neutral-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-4">
                                    <div class="w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center">
                                        <span class="font-bold text-primary-600">{{ strtoupper(substr($customer->customer_name, 0, 1)) }}</span>
                                    </div>
                                    <span class="font-medium text-neutral-900">{{ $customer->customer_name }}</span>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div>
                                    <p class="text-neutral-900">{{ $customer->customer_phone }}</p>
                                    <p class="text-sm text-neutral-500">{{ $customer->customer_email }}</p>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-medium text-neutral-900">{{ number_format($customer->orders_count) }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-semibold text-neutral-900">{{ number_format($customer->total_spent, 0, ',', ' ') }} F</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-neutral-500">{{ \Carbon\Carbon::parse($customer->last_order_at)->locale('fr')->diffForHumans() }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="w-16 h-16 bg-neutral-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-8 h-8 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                </div>
                                <p class="text-neutral-500">Aucun client trouvé</p>
                                <p class="text-sm text-neutral-400 mt-1">Les clients apparaîtront ici après leurs premières commandes.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    @if($customers->hasPages())
        <div class="mt-6">
            {{ $customers->links() }}
        </div>
    @endif
</x-layouts.admin-restaurant>
