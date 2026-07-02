<x-layouts.admin-super title="Finances">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold" style="color:var(--sa-fg);">Finances</h1>
        <p class="mt-1 text-sm" style="color:var(--sa-muted-fg);">Revenus, commissions et reversements aux restaurants</p>
    </div>

    <!-- 4 StatCards -->
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Revenus du mois (primary) -->
        <div class="rounded-2xl border p-5 shadow-sm" style="border-color:var(--sa-border);background:var(--sa-card);">
            <div class="flex w-10 h-10 items-center justify-center rounded-xl" style="background:rgba(194,98,31,0.12);">
                <svg class="w-5 h-5" style="color:var(--sa-primary);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                </svg>
            </div>
            <p class="mt-3 text-2xl font-bold" style="color:var(--sa-fg);">{{ number_format($stats['commissions_this_month'], 0, ',', ' ') }} F</p>
            <p class="mt-0.5 text-sm font-medium" style="color:var(--sa-muted-fg);">Commissions ce mois</p>
            <p class="mt-1 text-xs" style="color:var(--sa-muted-fg);">Plateforme</p>
        </div>

        <!-- Total collecté (success) -->
        <div class="rounded-2xl border p-5 shadow-sm" style="border-color:var(--sa-border);background:var(--sa-card);">
            <div class="flex w-10 h-10 items-center justify-center rounded-xl" style="background:rgba(61,158,98,0.12);">
                <svg class="w-5 h-5" style="color:var(--sa-success);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                </svg>
            </div>
            <p class="mt-3 text-2xl font-bold" style="color:var(--sa-fg);">{{ number_format($stats['total_collected'], 0, ',', ' ') }} F</p>
            <p class="mt-0.5 text-sm font-medium" style="color:var(--sa-muted-fg);">Total collecté</p>
            <p class="mt-1 text-xs" style="color:var(--sa-muted-fg);">Paiements complétés</p>
        </div>

        <!-- Solde wallets (info) -->
        <div class="rounded-2xl border p-5 shadow-sm" style="border-color:var(--sa-border);background:var(--sa-card);">
            <div class="flex w-10 h-10 items-center justify-center rounded-xl" style="background:rgba(59,111,212,0.12);">
                <svg class="w-5 h-5" style="color:var(--sa-info);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
            </div>
            <p class="mt-3 text-2xl font-bold" style="color:var(--sa-fg);">{{ number_format($stats['total_wallets_balance'], 0, ',', ' ') }} F</p>
            <p class="mt-0.5 text-sm font-medium" style="color:var(--sa-muted-fg);">Solde total wallets</p>
            <p class="mt-1 text-xs" style="color:var(--sa-muted-fg);">Tous restaurants</p>
        </div>

        <!-- Total commissions (warning) -->
        <div class="rounded-2xl border p-5 shadow-sm" style="border-color:var(--sa-border);background:var(--sa-card);">
            <div class="flex w-10 h-10 items-center justify-center rounded-xl" style="background:rgba(217,119,6,0.12);">
                <svg class="w-5 h-5" style="color:var(--sa-warning);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="mt-3 text-2xl font-bold" style="color:var(--sa-fg);">{{ number_format($stats['total_commissions'], 0, ',', ' ') }} F</p>
            <p class="mt-0.5 text-sm font-medium" style="color:var(--sa-muted-fg);">Commissions totales</p>
            <p class="mt-1 text-xs" style="color:var(--sa-muted-fg);">Toutes périodes</p>
        </div>
    </div>

    <!-- Sub-navigation Tabs -->
    <div class="mb-6 rounded-xl border shadow-sm" style="border-color:var(--sa-border);background:var(--sa-card);">
        <nav class="flex border-b" style="border-color:var(--sa-border);">
            <a href="{{ route('super-admin.finances.index') }}"
               class="px-6 py-3.5 text-sm font-semibold border-b-2 transition-colors"
               style="{{ request()->routeIs('super-admin.finances.index')
                    ? 'border-color:var(--sa-primary);color:var(--sa-primary);'
                    : 'border-color:transparent;color:var(--sa-muted-fg);' }}">
                Vue d'ensemble
            </a>
            <a href="{{ route('super-admin.finances.payouts') }}"
               class="px-6 py-3.5 text-sm font-semibold border-b-2 transition-colors"
               style="{{ request()->routeIs('super-admin.finances.payouts')
                    ? 'border-color:var(--sa-primary);color:var(--sa-primary);'
                    : 'border-color:transparent;color:var(--sa-muted-fg);' }}">
                Retraits
                @if($stats['pending_payouts'] > 0)
                    <span class="ml-1.5 inline-flex w-5 h-5 items-center justify-center rounded-full text-xs font-bold"
                          style="background:var(--sa-warning);color:#fff;">{{ $stats['pending_payouts'] }}</span>
                @endif
            </a>
            <a href="{{ route('super-admin.finances.commissions') }}"
               class="px-6 py-3.5 text-sm font-semibold border-b-2 transition-colors"
               style="{{ request()->routeIs('super-admin.finances.commissions')
                    ? 'border-color:var(--sa-primary);color:var(--sa-primary);'
                    : 'border-color:transparent;color:var(--sa-muted-fg);' }}">
                Commissions
            </a>
        </nav>
    </div>

    <!-- Charts Row 1: FinanceArea (2/3) + PaymentSplit donut (1/3) -->
    <div class="mb-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="rounded-2xl border p-5 shadow-sm lg:col-span-2" style="border-color:var(--sa-border);background:var(--sa-card);">
            <div class="mb-5 flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold" style="color:var(--sa-fg);">Flux financiers</h2>
                    <p class="text-sm" style="color:var(--sa-muted-fg);">Collectes et retraits dans le temps</p>
                </div>
            </div>
            <div style="height:280px;">
                <canvas id="financeAreaChart"></canvas>
            </div>
        </div>

        <div class="rounded-2xl border p-5 shadow-sm" style="border-color:var(--sa-border);background:var(--sa-card);">
            <div class="mb-5 flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold" style="color:var(--sa-fg);">Statut des retraits</h2>
                    <p class="text-sm" style="color:var(--sa-muted-fg);">Répartition</p>
                </div>
            </div>
            <div style="height:280px;">
                <canvas id="payoutSplitChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Charts Row 2: Commissions bar + Monthly detail table -->
    <div class="mb-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="rounded-2xl border p-5 shadow-sm" style="border-color:var(--sa-border);background:var(--sa-card);">
            <div class="mb-5 flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold" style="color:var(--sa-fg);">Commissions mensuelles</h2>
                    <p class="text-sm" style="color:var(--sa-muted-fg);">Évolution des commissions perçues</p>
                </div>
            </div>
            <div style="height:280px;">
                <canvas id="commissionsBarChart"></canvas>
            </div>
        </div>

        <div class="rounded-2xl border p-5 shadow-sm" style="border-color:var(--sa-border);background:var(--sa-card);">
            <div class="mb-5 flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold" style="color:var(--sa-fg);">Activité récente</h2>
                    <p class="text-sm" style="color:var(--sa-muted-fg);">Retraits et commissions</p>
                </div>
            </div>

            <!-- Stat mini-cards -->
            <div class="mb-5 grid grid-cols-2 gap-3">
                <div class="rounded-xl p-3" style="background:var(--sa-muted);">
                    <p class="text-xs font-medium uppercase tracking-wide" style="color:var(--sa-muted-fg);">En attente</p>
                    <p class="mt-1 text-lg font-bold" style="color:var(--sa-warning);">{{ $stats['pending_payouts'] }} retrait(s)</p>
                    <p class="text-xs" style="color:var(--sa-muted-fg);">{{ number_format($stats['pending_payouts_amount'], 0, ',', ' ') }} F</p>
                </div>
                <div class="rounded-xl p-3" style="background:var(--sa-muted);">
                    <p class="text-xs font-medium uppercase tracking-wide" style="color:var(--sa-muted-fg);">Complétés</p>
                    <p class="mt-1 text-lg font-bold" style="color:var(--sa-success);">{{ $stats['completed_payouts'] }}</p>
                    <p class="text-xs" style="color:var(--sa-muted-fg);">{{ number_format($stats['total_withdrawn'], 0, ',', ' ') }} F reversés</p>
                </div>
            </div>

            <!-- Recent commissions list -->
            <p class="mb-2 text-xs font-semibold uppercase tracking-wide" style="color:var(--sa-muted-fg);">Dernières commissions</p>
            <div class="flex flex-col gap-2">
                @forelse($recentCommissions->take(5) as $commission)
                <div class="flex items-center justify-between rounded-lg px-3 py-2" style="background:var(--sa-muted);">
                    <div class="min-w-0">
                        <span class="block truncate text-sm font-medium" style="color:var(--sa-fg);">{{ $commission->restaurant->name ?? 'N/A' }}</span>
                        <span class="text-xs" style="color:var(--sa-muted-fg);">Commande #{{ $commission->order->id ?? 'N/A' }}</span>
                    </div>
                    <span class="ml-3 shrink-0 text-sm font-semibold" style="color:var(--sa-success);">{{ number_format($commission->amount, 0, ',', ' ') }} F</span>
                </div>
                @empty
                <p class="py-4 text-center text-sm" style="color:var(--sa-muted-fg);">Aucune commission récente.</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Wallets Table -->
    <div class="rounded-2xl border shadow-sm" style="border-color:var(--sa-border);background:var(--sa-card);">
        <div class="flex items-center justify-between px-6 py-4" style="border-bottom:1px solid var(--sa-border);">
            <h3 class="font-semibold" style="color:var(--sa-fg);">Wallets Restaurants</h3>
            <form method="GET" class="flex items-center gap-2">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 w-4 h-4 -translate-y-1/2" style="color:var(--sa-muted-fg);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher..."
                           class="h-9 w-56 rounded-lg pl-9 pr-3 text-sm outline-none transition"
                           style="border:1px solid var(--sa-border);background:var(--sa-bg);color:var(--sa-fg);">
                </div>
                <div class="flex gap-1">
                    <a href="?sort=balance_desc{{ request('search') ? '&search=' . request('search') : '' }}"
                       class="rounded-lg px-2.5 py-1 text-xs font-medium transition-colors"
                       style="{{ request('sort', 'balance_desc') === 'balance_desc'
                            ? 'background:rgba(194,98,31,0.10);color:var(--sa-primary);'
                            : 'color:var(--sa-muted-fg);' }}">Solde ↓</a>
                    <a href="?sort=balance_asc{{ request('search') ? '&search=' . request('search') : '' }}"
                       class="rounded-lg px-2.5 py-1 text-xs font-medium transition-colors"
                       style="{{ request('sort') === 'balance_asc'
                            ? 'background:rgba(194,98,31,0.10);color:var(--sa-primary);'
                            : 'color:var(--sa-muted-fg);' }}">Solde ↑</a>
                </div>
            </form>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr style="border-bottom:1px solid var(--sa-border);">
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--sa-muted-fg);">Restaurant</th>
                        <th class="px-6 py-3 text-right text-xs font-semibold uppercase tracking-wide" style="color:var(--sa-muted-fg);">Solde</th>
                        <th class="px-6 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--sa-muted-fg);">Téléphone</th>
                        <th class="px-6 py-3 text-center text-xs font-semibold uppercase tracking-wide" style="color:var(--sa-muted-fg);">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($wallets as $wallet)
                    <tr style="border-bottom:1px solid var(--sa-border);">
                        <td class="px-6 py-3.5">
                            <span class="font-medium" style="color:var(--sa-fg);">{{ $wallet->restaurant->name ?? 'N/A' }}</span>
                        </td>
                        <td class="px-6 py-3.5 text-right">
                            <span class="font-semibold" style="color:var(--sa-fg);">{{ number_format($wallet->balance, 0, ',', ' ') }} F</span>
                        </td>
                        <td class="px-6 py-3.5">
                            @if($wallet->phone)
                                <span class="text-sm" style="color:var(--sa-muted-fg);">{{ $wallet->prefix }}{{ $wallet->phone }}</span>
                            @else
                                <span class="text-sm" style="color:var(--sa-muted-fg);">—</span>
                            @endif
                        </td>
                        <td class="px-6 py-3.5 text-center">
                            @if($wallet->restaurant)
                                <a href="{{ route('super-admin.restaurants.show', $wallet->restaurant) }}"
                                   class="text-sm font-medium transition-colors"
                                   style="color:var(--sa-primary);">Voir</a>
                            @endif
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-6 py-12 text-center text-sm" style="color:var(--sa-muted-fg);">
                            Aucun wallet trouvé.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($wallets->hasPages())
        <div class="px-6 py-4" style="border-top:1px solid var(--sa-border);">
            {{ $wallets->links() }}
        </div>
        @endif
    </div>

    @push('scripts')
    <script src="https://unpkg.com/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        const saGridColor = 'rgba(209,213,219,0.5)';
        const saTickColor = '#6b7280';

        // ── FinanceArea: line chart (wallets balance vs withdrawn) ────────
        // We approximate 6 monthly data points from available stats
        new Chart(document.getElementById('financeAreaChart'), {
            type: 'line',
            data: {
                labels: ['M-5', 'M-4', 'M-3', 'M-2', 'M-1', 'Ce mois'],
                datasets: [
                    {
                        label: 'Collectes (F)',
                        data: [0, 0, 0, 0, 0, {{ $stats['total_collected'] }}],
                        borderColor: 'rgb(59, 130, 246)',
                        backgroundColor: 'rgba(59, 130, 246, 0.1)',
                        tension: 0.4,
                        fill: true,
                        pointRadius: 3,
                    },
                    {
                        label: 'Retraits (F)',
                        data: [0, 0, 0, 0, 0, {{ $stats['total_withdrawn'] }}],
                        borderColor: 'rgb(245, 158, 11)',
                        backgroundColor: 'rgba(245, 158, 11, 0.08)',
                        tension: 0.4,
                        fill: true,
                        pointRadius: 3,
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { labels: { color: saTickColor, boxWidth: 12 } },
                    tooltip: { callbacks: { label: ctx => ctx.dataset.label + ' : ' + ctx.parsed.y.toLocaleString('fr-FR') + ' F' } }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { color: saTickColor, callback: v => (v / 1000).toFixed(0) + 'K' },
                        grid: { color: saGridColor }
                    },
                    x: { ticks: { color: saTickColor }, grid: { color: saGridColor } }
                }
            }
        });

        // ── PayoutSplit: doughnut (pending / completed / failed) ──────────
        new Chart(document.getElementById('payoutSplitChart'), {
            type: 'doughnut',
            data: {
                labels: ['En attente', 'Complétés'],
                datasets: [{
                    data: [{{ $stats['pending_payouts'] }}, {{ $stats['completed_payouts'] }}],
                    backgroundColor: ['rgba(245,158,11,0.8)', 'rgba(16,185,129,0.8)'],
                    borderWidth: 2,
                    borderColor: '#fff',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '60%',
                plugins: {
                    legend: { position: 'bottom', labels: { color: saTickColor, boxWidth: 12, padding: 12 } }
                }
            }
        });

        // ── CommissionsBar: monthly commissions approximation ─────────────
        new Chart(document.getElementById('commissionsBarChart'), {
            type: 'bar',
            data: {
                labels: ['M-5', 'M-4', 'M-3', 'M-2', 'M-1', 'Ce mois'],
                datasets: [{
                    label: 'Commissions (F)',
                    data: [0, 0, 0, 0, 0, {{ $stats['commissions_this_month'] }}],
                    backgroundColor: 'rgba(139, 92, 246, 0.75)',
                    borderColor: 'rgb(139, 92, 246)',
                    borderWidth: 1,
                    borderRadius: 6,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: { callbacks: { label: ctx => ctx.parsed.y.toLocaleString('fr-FR') + ' F' } }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { color: saTickColor, callback: v => (v / 1000).toFixed(0) + 'K' },
                        grid: { color: saGridColor }
                    },
                    x: { ticks: { color: saTickColor }, grid: { display: false } }
                }
            }
        });
    </script>
    @endpush
</x-layouts.admin-super>
