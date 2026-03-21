<x-layouts.admin-super title="Retraits">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <a href="{{ route('super-admin.finances.index') }}" class="text-neutral-400 hover:text-neutral-600 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <h1 class="text-2xl font-bold text-neutral-900">Retraits</h1>
            </div>
            <p class="text-neutral-500 mt-1 ml-8">Historique et suivi de tous les retraits restaurants.</p>
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
               class="px-6 py-3.5 text-sm font-semibold border-b-2 border-primary-500 text-primary-600 transition-colors">
                Retraits
            </a>
            <a href="{{ route('super-admin.finances.commissions') }}"
               class="px-6 py-3.5 text-sm font-semibold border-b-2 border-transparent text-neutral-500 hover:text-neutral-700 hover:border-neutral-300 transition-colors">
                Commissions
            </a>
        </nav>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white border border-neutral-200 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-neutral-500">En attente</p>
                    <p class="text-2xl font-bold text-amber-600 mt-1">{{ $stats['pending'] }}</p>
                </div>
                <div class="w-12 h-12 bg-amber-50 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white border border-neutral-200 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-neutral-500">Completes</p>
                    <p class="text-2xl font-bold text-emerald-600 mt-1">{{ $stats['completed'] }}</p>
                </div>
                <div class="w-12 h-12 bg-emerald-50 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white border border-neutral-200 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-neutral-500">Echoues</p>
                    <p class="text-2xl font-bold text-red-600 mt-1">{{ $stats['failed'] }}</p>
                </div>
                <div class="w-12 h-12 bg-red-50 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white border border-neutral-200 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-neutral-500">Total paye</p>
                    <p class="text-2xl font-bold text-neutral-900 mt-1">{{ number_format($stats['total_paid'], 0, ',', ' ') }} F</p>
                </div>
                <div class="w-12 h-12 bg-neutral-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
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
            <select name="status" class="h-10 px-4 bg-white border border-neutral-200 rounded-lg text-neutral-700 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500">
                <option value="" {{ !request('status') ? 'selected' : '' }}>Tous les statuts</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En attente</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Complete</option>
                <option value="failed" {{ request('status') === 'failed' ? 'selected' : '' }}>Echoue</option>
            </select>
            <button type="submit" class="h-10 px-6 bg-primary-500 text-white rounded-lg font-medium hover:bg-primary-600 transition-colors shadow-sm">
                Filtrer
            </button>
        </div>
    </form>

    <!-- Payouts Table -->
    <div class="bg-white border border-neutral-200 rounded-2xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-neutral-50 border-b border-neutral-100">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 uppercase tracking-wide">Restaurant</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold text-neutral-500 uppercase tracking-wide">Montant</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 uppercase tracking-wide">Gateway</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 uppercase tracking-wide">Telephone</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold text-neutral-500 uppercase tracking-wide">Statut</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 uppercase tracking-wide">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100">
                    @forelse($payouts as $payout)
                        <tr class="hover:bg-neutral-50 transition-colors">
                            <td class="px-6 py-4">
                                <span class="font-medium text-neutral-900">{{ $payout->restaurant->name ?? 'N/A' }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="font-semibold text-neutral-900">{{ number_format($payout->amount, 0, ',', ' ') }} F</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-neutral-600">{{ ucfirst($payout->gateway ?? '--') }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-neutral-600 font-mono">{{ $payout->phone ?? '--' }}</span>
                            </td>
                            <td class="px-6 py-4 text-center">
                                @php
                                    $statusStyles = [
                                        'pending' => 'bg-amber-50 text-amber-700 border-amber-200',
                                        'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        'failed' => 'bg-red-50 text-red-700 border-red-200',
                                    ];
                                    $statusLabels = ['pending' => 'En attente', 'completed' => 'Complété', 'failed' => 'Échoué'];
                                @endphp
                                <span class="inline-flex px-2.5 py-1 rounded-lg text-xs font-semibold border {{ $statusStyles[$payout->status] ?? 'bg-neutral-50 text-neutral-600 border-neutral-200' }}">
                                    {{ $statusLabels[$payout->status] ?? ucfirst($payout->status) }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm text-neutral-500">{{ $payout->created_at->format('d/m/Y H:i') }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-neutral-400">
                                Aucun retrait trouvé.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($payouts->hasPages())
            <div class="px-6 py-4 border-t border-neutral-100">
                {{ $payouts->links() }}
            </div>
        @endif
    </div>
</x-layouts.admin-super>
