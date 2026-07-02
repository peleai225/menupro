<x-layouts.admin-super title="Retraits">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <a href="{{ route('super-admin.finances.index') }}" class="transition-colors" style="color:var(--sa-muted-fg);">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <h1 class="text-2xl font-bold" style="color:var(--sa-fg);">Retraits</h1>
            </div>
            <p class="mt-1 ml-8" style="color:var(--sa-muted-fg);">Historique et suivi de tous les retraits restaurants.</p>
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
               class="px-6 py-3.5 text-sm font-semibold border-b-2 transition-colors"
               style="border-color:var(--sa-primary);color:var(--sa-primary);">
                Retraits
            </a>
            <a href="{{ route('super-admin.finances.commissions') }}"
               class="px-6 py-3.5 text-sm font-semibold border-b-2 border-transparent transition-colors"
               style="color:var(--sa-muted-fg);">
                Commissions
            </a>
        </nav>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="border rounded-2xl p-5 shadow-sm" style="background:var(--sa-card);border-color:var(--sa-border);">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium" style="color:var(--sa-muted-fg);">En attente</p>
                    <p class="text-2xl font-bold text-amber-600 mt-1">{{ $stats['pending'] }}</p>
                </div>
                <div class="w-12 h-12 bg-amber-50 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="border rounded-2xl p-5 shadow-sm" style="background:var(--sa-card);border-color:var(--sa-border);">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium" style="color:var(--sa-muted-fg);">Completes</p>
                    <p class="text-2xl font-bold text-emerald-600 mt-1">{{ $stats['completed'] }}</p>
                </div>
                <div class="w-12 h-12 bg-emerald-50 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="border rounded-2xl p-5 shadow-sm" style="background:var(--sa-card);border-color:var(--sa-border);">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium" style="color:var(--sa-muted-fg);">Echoues</p>
                    <p class="text-2xl font-bold text-red-600 mt-1">{{ $stats['failed'] }}</p>
                </div>
                <div class="w-12 h-12 bg-red-50 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="border rounded-2xl p-5 shadow-sm" style="background:var(--sa-card);border-color:var(--sa-border);">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium" style="color:var(--sa-muted-fg);">Total paye</p>
                    <p class="text-2xl font-bold mt-1" style="color:var(--sa-fg);">{{ number_format($stats['total_paid'], 0, ',', ' ') }} F</p>
                </div>
                <div class="w-12 h-12 rounded-xl flex items-center justify-center" style="background:var(--sa-muted);">
                    <svg class="w-6 h-6" style="color:var(--sa-muted-fg);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
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
            <select name="status" class="h-10 px-4 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500" style="background:var(--sa-card);border-color:var(--sa-border);color:var(--sa-fg);">
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
    <div class="border rounded-2xl overflow-hidden shadow-sm" style="background:var(--sa-card);border-color:var(--sa-border);">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead style="background:var(--sa-muted);border-bottom:1px solid var(--sa-border);">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--sa-muted-fg);">Restaurant</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide" style="color:var(--sa-muted-fg);">Montant</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--sa-muted-fg);">Gateway</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--sa-muted-fg);">Telephone</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wide" style="color:var(--sa-muted-fg);">Statut</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--sa-muted-fg);">Date</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($payouts as $payout)
                        <tr style="border-bottom:1px solid var(--sa-border);">
                            <td class="px-6 py-4">
                                <span class="font-medium" style="color:var(--sa-fg);">{{ $payout->restaurant->name ?? 'N/A' }}</span>
                            </td>
                            <td class="px-6 py-4 text-right">
                                <span class="font-semibold" style="color:var(--sa-fg);">{{ number_format($payout->amount, 0, ',', ' ') }} F</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm" style="color:var(--sa-muted-fg);">{{ ucfirst($payout->gateway ?? '--') }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-sm font-mono" style="color:var(--sa-muted-fg);">{{ $payout->phone ?? '--' }}</span>
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
                                <span class="text-sm" style="color:var(--sa-muted-fg);">{{ $payout->created_at->format('d/m/Y H:i') }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center" style="color:var(--sa-muted-fg);">
                                Aucun retrait trouvé.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($payouts->hasPages())
            <div class="px-6 py-4" style="border-top:1px solid var(--sa-border);">
                {{ $payouts->links() }}
            </div>
        @endif
    </div>
</x-layouts.admin-super>
