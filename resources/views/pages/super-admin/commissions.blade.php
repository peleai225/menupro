<x-layouts.admin-super title="Commissions">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <a href="{{ route('super-admin.finances.index') }}" class="text-neutral-400 hover:text-neutral-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <h1 class="text-2xl font-bold text-neutral-900">Commissions</h1>
            </div>
            <p class="text-neutral-500 mt-1 ml-8">Suivi des commissions prelevees sur les commandes restaurants.</p>
        </div>
    </div>

    <!-- Sub-navigation Tabs -->
    <div class="bg-white border border-neutral-200 rounded-xl mb-8 shadow-sm">
        <nav class="flex border-b border-neutral-200">
            <a href="{{ route('super-admin.finances.index') }}"
               class="px-6 py-3.5 text-sm font-semibold border-b-2 border-transparent text-neutral-500 hover:text-neutral-700 hover:border-neutral-300 transition-colors">
                Vue d'ensemble
            </a>
            <a href="{{ route('super-admin.finances.payouts') }}"
               class="px-6 py-3.5 text-sm font-semibold border-b-2 border-transparent text-neutral-500 hover:text-neutral-700 hover:border-neutral-300 transition-colors">
                Retraits
            </a>
            <a href="{{ route('super-admin.finances.commissions') }}"
               class="px-6 py-3.5 text-sm font-semibold border-b-2 border-primary-500 text-primary-600 transition-colors">
                Commissions
            </a>
        </nav>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
        <div class="bg-white border border-neutral-200 rounded-2xl p-5 shadow-sm">
            <p class="text-sm font-medium text-neutral-500">Total commissions</p>
            <p class="text-2xl font-bold text-violet-600 mt-1">{{ number_format($stats['total'], 0, ',', ' ') }} F</p>
        </div>

        <div class="bg-white border border-neutral-200 rounded-2xl p-5 shadow-sm">
            <p class="text-sm font-medium text-neutral-500">Ce mois</p>
            <p class="text-2xl font-bold text-emerald-600 mt-1">{{ number_format($stats['this_month'], 0, ',', ' ') }} F</p>
        </div>

        <div class="bg-white border border-neutral-200 rounded-2xl p-5 shadow-sm">
            <p class="text-sm font-medium text-neutral-500">Mois dernier</p>
            <p class="text-2xl font-bold text-neutral-900 mt-1">{{ number_format($stats['last_month'], 0, ',', ' ') }} F</p>
        </div>

        <div class="bg-white border border-neutral-200 rounded-2xl p-5 shadow-sm">
            <p class="text-sm font-medium text-neutral-500">Taux moyen</p>
            <p class="text-2xl font-bold text-blue-600 mt-1">{{ number_format($stats['avg_rate'], 1) }}%</p>
        </div>

        <div class="bg-white border border-neutral-200 rounded-2xl p-5 shadow-sm">
            <p class="text-sm font-medium text-neutral-500">Nombre commandes</p>
            <p class="text-2xl font-bold text-neutral-900 mt-1">{{ number_format($stats['total_orders'], 0, ',', ' ') }}</p>
        </div>
    </div>

    <!-- Search Filter -->
    <form method="GET" class="bg-white border border-neutral-200 rounded-xl p-4 mb-6 shadow-sm">
        <div class="flex flex-col lg:flex-row gap-4">
            <div class="flex-1">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher par restaurant..."
                           class="w-full h-10 pl-10 pr-4 bg-white border border-neutral-200 rounded-lg text-neutral-900 placeholder-neutral-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                </div>
            </div>
            <button type="submit" class="h-10 px-6 bg-primary-500 text-white rounded-lg font-medium hover:bg-primary-600 transition-colors shadow-sm">
                Filtrer
            </button>
        </div>
    </form>

    <!-- Commissions Table -->
    <div class="bg-white border border-neutral-200 rounded-2xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-neutral-50 border-b border-neutral-100">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 uppercase tracking-wide">Restaurant</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 uppercase tracking-wide">Commande</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-neutral-500 uppercase tracking-wide">Montant commande</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-neutral-500 uppercase tracking-wide">Commission</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-neutral-500 uppercase tracking-wide">Taux</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 uppercase tracking-wide">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100">
                    @forelse($commissions as $commission)
                        <tr class="hover:bg-neutral-50 transition-colors">
                            <td class="px-6 py-4">
                                <span class="font-medium text-neutral-900">{{ $commission->restaurant->name ?? 'N/A' }}</span>
                            </td>
                            <td class="px-6 py-4">
                                @if($commission->order)
                                    <span class="font-mono text-sm text-neutral-600">#{{ $commission->order->id }}</span>
                                @else
                                    <span class="text-neutral-400 text-sm">N/A</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="text-neutral-900 font-medium">{{ number_format($commission->order_total, 0, ',', ' ') }} F</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="font-semibold text-violet-600">{{ number_format($commission->amount, 0, ',', ' ') }} F</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="inline-flex px-2 py-0.5 rounded-md text-xs font-medium bg-blue-50 text-blue-700 border border-blue-200">
                                    {{ number_format($commission->commission_rate, 1) }}%
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-neutral-500">{{ $commission->created_at->format('d/m/Y H:i') }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-neutral-400">
                                Aucune commission trouvee.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($commissions->hasPages())
            <div class="px-6 py-4 border-t border-neutral-100">
                {{ $commissions->links() }}
            </div>
        @endif
    </div>
</x-layouts.admin-super>
