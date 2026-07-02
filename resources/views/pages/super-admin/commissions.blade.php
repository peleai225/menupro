<x-layouts.admin-super title="Commissions">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <a href="{{ route('super-admin.finances.index') }}" class="transition-colors" style="color:var(--sa-muted-fg);">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <h1 class="text-2xl font-bold" style="color:var(--sa-fg);">Commissions</h1>
            </div>
            <p class="mt-1 ml-8" style="color:var(--sa-muted-fg);">Suivi des commissions prelevees sur les commandes restaurants.</p>
        </div>
    </div>

    <!-- Sub-navigation Tabs -->
    <div class="border rounded-xl mb-8 shadow-sm" style="background:var(--sa-card);border-color:var(--sa-border);">
        <nav class="flex border-b" style="border-color:var(--sa-border);">
            <a href="{{ route('super-admin.finances.index') }}"
               class="px-6 py-3.5 text-sm font-semibold border-b-2 border-transparent transition-colors"
               style="color:var(--sa-muted-fg);">
                Vue d'ensemble
            </a>
            <a href="{{ route('super-admin.finances.payouts') }}"
               class="px-6 py-3.5 text-sm font-semibold border-b-2 border-transparent transition-colors"
               style="color:var(--sa-muted-fg);">
                Retraits
            </a>
            <a href="{{ route('super-admin.finances.commissions') }}"
               class="px-6 py-3.5 text-sm font-semibold border-b-2 transition-colors"
               style="border-color:var(--sa-primary);color:var(--sa-primary);">
                Commissions
            </a>
        </nav>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-5 gap-4 mb-8">
        <div class="border rounded-2xl p-5 shadow-sm" style="background:var(--sa-card);border-color:var(--sa-border);">
            <p class="text-sm font-medium" style="color:var(--sa-muted-fg);">Total commissions</p>
            <p class="text-2xl font-bold text-violet-600 mt-1">{{ number_format($stats['total'], 0, ',', ' ') }} F</p>
        </div>

        <div class="border rounded-2xl p-5 shadow-sm" style="background:var(--sa-card);border-color:var(--sa-border);">
            <p class="text-sm font-medium" style="color:var(--sa-muted-fg);">Ce mois</p>
            <p class="text-2xl font-bold text-emerald-600 mt-1">{{ number_format($stats['this_month'], 0, ',', ' ') }} F</p>
        </div>

        <div class="border rounded-2xl p-5 shadow-sm" style="background:var(--sa-card);border-color:var(--sa-border);">
            <p class="text-sm font-medium" style="color:var(--sa-muted-fg);">Mois dernier</p>
            <p class="text-2xl font-bold mt-1" style="color:var(--sa-fg);">{{ number_format($stats['last_month'], 0, ',', ' ') }} F</p>
        </div>

        <div class="border rounded-2xl p-5 shadow-sm" style="background:var(--sa-card);border-color:var(--sa-border);">
            <p class="text-sm font-medium" style="color:var(--sa-muted-fg);">Taux moyen</p>
            <p class="text-2xl font-bold text-blue-600 mt-1">{{ number_format($stats['avg_rate'], 1) }}%</p>
        </div>

        <div class="border rounded-2xl p-5 shadow-sm" style="background:var(--sa-card);border-color:var(--sa-border);">
            <p class="text-sm font-medium" style="color:var(--sa-muted-fg);">Nombre commandes</p>
            <p class="text-2xl font-bold mt-1" style="color:var(--sa-fg);">{{ number_format($stats['total_orders'], 0, ',', ' ') }}</p>
        </div>
    </div>

    <!-- Search Filter -->
    <form method="GET" class="border rounded-xl p-4 mb-6 shadow-sm" style="background:var(--sa-card);border-color:var(--sa-border);">
        <div class="flex flex-col lg:flex-row gap-4">
            <div class="flex-1">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5" style="color:var(--sa-muted-fg);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher par restaurant..."
                           class="w-full h-10 pl-10 pr-4 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500"
                           style="background:var(--sa-card);border-color:var(--sa-border);color:var(--sa-fg);">
                </div>
            </div>
            <button type="submit" class="h-10 px-6 bg-primary-500 text-white rounded-lg font-medium hover:bg-primary-600 transition-colors shadow-sm">
                Filtrer
            </button>
        </div>
    </form>

    <!-- Commissions Table -->
    <div class="border rounded-2xl overflow-hidden shadow-sm" style="background:var(--sa-card);border-color:var(--sa-border);">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead style="background:var(--sa-muted);border-bottom:1px solid var(--sa-border);">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--sa-muted-fg);">Restaurant</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--sa-muted-fg);">Commande</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide" style="color:var(--sa-muted-fg);">Montant commande</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide" style="color:var(--sa-muted-fg);">Commission</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide" style="color:var(--sa-muted-fg);">Taux</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--sa-muted-fg);">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($commissions as $commission)
                        <tr style="border-bottom:1px solid var(--sa-border);">
                            <td class="px-6 py-4">
                                <span class="font-medium" style="color:var(--sa-fg);">{{ $commission->restaurant->name ?? 'N/A' }}</span>
                            </td>
                            <td class="px-6 py-4">
                                @if($commission->order)
                                    <span class="font-mono text-sm" style="color:var(--sa-muted-fg);">#{{ $commission->order->id }}</span>
                                @else
                                    <span class="text-sm" style="color:var(--sa-muted-fg);">N/A</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="font-medium" style="color:var(--sa-fg);">{{ number_format($commission->order_total, 0, ',', ' ') }} F</span>
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
                                <span class="text-sm" style="color:var(--sa-muted-fg);">{{ $commission->created_at->format('d/m/Y H:i') }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center" style="color:var(--sa-muted-fg);">
                                Aucune commission trouvee.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($commissions->hasPages())
            <div class="px-6 py-4" style="border-top:1px solid var(--sa-border);">
                {{ $commissions->links() }}
            </div>
        @endif
    </div>
</x-layouts.admin-super>
