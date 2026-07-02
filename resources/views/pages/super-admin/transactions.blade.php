<x-layouts.admin-super title="Transactions">
    <!-- Header -->
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold" style="color:var(--sa-fg);">Transactions</h1>
            <p class="mt-1 text-sm" style="color:var(--sa-muted-fg);">Suivi des paiements et commissions perçues</p>
        </div>
        <a href="{{ route('super-admin.transactions.export') }}"
           class="inline-flex h-10 items-center gap-2 rounded-lg px-4 text-sm font-medium shadow-sm transition-colors"
           style="border:1px solid var(--sa-border);background:var(--sa-card);color:var(--sa-fg);">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Exporter CSV
        </a>
    </div>

    <!-- 4 StatCards -->
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Volume traité (primary) -->
        <div class="rounded-2xl border p-5 shadow-sm" style="border-color:var(--sa-border);background:var(--sa-card);">
            <div class="flex w-10 h-10 items-center justify-center rounded-xl" style="background:rgba(194,98,31,0.12);">
                <svg class="w-5 h-5" style="color:var(--sa-primary);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                </svg>
            </div>
            <p class="mt-3 text-2xl font-bold" style="color:var(--sa-fg);">{{ number_format($stats['total_revenue'], 0, ',', ' ') }} F</p>
            <p class="mt-0.5 text-sm font-medium" style="color:var(--sa-muted-fg);">Volume traité</p>
            <p class="mt-1 text-xs" style="color:var(--sa-muted-fg);">Abonnements + Commandes</p>
        </div>

        <!-- Ce mois (success) -->
        <div class="rounded-2xl border p-5 shadow-sm" style="border-color:var(--sa-border);background:var(--sa-card);">
            <div class="flex w-10 h-10 items-center justify-center rounded-xl" style="background:rgba(61,158,98,0.12);">
                <svg class="w-5 h-5" style="color:var(--sa-success);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="mt-3 text-2xl font-bold" style="color:var(--sa-fg);">{{ number_format($stats['this_month_revenue'], 0, ',', ' ') }} F</p>
            <p class="mt-0.5 text-sm font-medium" style="color:var(--sa-muted-fg);">Revenus ce mois</p>
            <p class="mt-1 text-xs" style="color:var(--sa-muted-fg);">Transactions réussies</p>
        </div>

        <!-- En attente (warning) -->
        <div class="rounded-2xl border p-5 shadow-sm" style="border-color:var(--sa-border);background:var(--sa-card);">
            <div class="flex w-10 h-10 items-center justify-center rounded-xl" style="background:rgba(217,119,6,0.12);">
                <svg class="w-5 h-5" style="color:var(--sa-warning);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="mt-3 text-2xl font-bold" style="color:var(--sa-fg);">{{ number_format($stats['pending_payments'], 0, ',', ' ') }} F</p>
            <p class="mt-0.5 text-sm font-medium" style="color:var(--sa-muted-fg);">En attente</p>
            <p class="mt-1 text-xs" style="color:var(--sa-muted-fg);">Paiements en cours</p>
        </div>

        <!-- Commissions (info) -->
        <div class="rounded-2xl border p-5 shadow-sm" style="border-color:var(--sa-border);background:var(--sa-card);">
            <div class="flex w-10 h-10 items-center justify-center rounded-xl" style="background:rgba(59,111,212,0.12);">
                <svg class="w-5 h-5" style="color:var(--sa-info);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                </svg>
            </div>
            <p class="mt-3 text-2xl font-bold" style="color:var(--sa-fg);">{{ number_format($stats['orders_revenue'], 0, ',', ' ') }} F</p>
            <p class="mt-0.5 text-sm font-medium" style="color:var(--sa-muted-fg);">Revenus commandes</p>
            <p class="mt-1 text-xs" style="color:var(--sa-muted-fg);">Hors abonnements</p>
        </div>
    </div>

    <!-- Revenue Chart -->
    <div class="mb-6 rounded-2xl border p-5 shadow-sm" style="border-color:var(--sa-border);background:var(--sa-card);">
        <div class="mb-5 flex items-start justify-between gap-4">
            <div>
                <h2 class="text-lg font-semibold" style="color:var(--sa-fg);">Revenus des 30 derniers jours</h2>
                <p class="text-sm" style="color:var(--sa-muted-fg);">Commandes + abonnements</p>
            </div>
        </div>
        <div style="height:260px;" x-data="revenueChart()" x-init="init()">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>

    <!-- FilterTabs + Search + Type/Status filters -->
    <div class="mb-4">
        <!-- Status tabs -->
        <div class="mb-3 flex flex-wrap items-center gap-2">
            @php
                $tabDefs = [
                    ''          => 'Toutes',
                    'completed' => 'Réussies',
                    'pending'   => 'En attente',
                    'failed'    => 'Échouées',
                    'refund'    => 'Remboursements',
                ];
            @endphp
            @foreach($tabDefs as $tabStatus => $tabLabel)
            <a href="?{{ http_build_query(array_merge($filters, ['status' => $tabStatus, 'page' => 1])) }}"
               class="rounded-lg px-3.5 py-1.5 text-sm font-medium transition-colors"
               style="{{ $filters['status'] === $tabStatus
                    ? 'background:var(--sa-primary);color:var(--sa-primary-fg);'
                    : 'background:var(--sa-card);color:var(--sa-muted-fg);border:1px solid var(--sa-border);' }}">
                {{ $tabLabel }}
            </a>
            @endforeach
        </div>

        <!-- Search + Type + Date filters -->
        <form method="GET" class="flex flex-wrap gap-3">
            <input type="hidden" name="status" value="{{ $filters['status'] }}">
            <div class="relative flex-1 min-w-48">
                <svg class="absolute left-3 top-1/2 w-4 h-4 -translate-y-1/2" style="color:var(--sa-muted-fg);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Référence, restaurant..."
                       class="h-10 w-full rounded-lg pl-9 pr-3 text-sm outline-none transition"
                       style="border:1px solid var(--sa-border);background:var(--sa-bg);color:var(--sa-fg);">
            </div>
            <select name="type" class="h-10 rounded-lg px-3 text-sm outline-none transition"
                    style="border:1px solid var(--sa-border);background:var(--sa-bg);color:var(--sa-fg);">
                <option value="all" {{ $filters['type'] === 'all' ? 'selected' : '' }}>Tous les types</option>
                <option value="subscription" {{ $filters['type'] === 'subscription' ? 'selected' : '' }}>Abonnements</option>
                <option value="order" {{ $filters['type'] === 'order' ? 'selected' : '' }}>Commandes</option>
                <option value="refund" {{ $filters['type'] === 'refund' ? 'selected' : '' }}>Remboursements</option>
            </select>
            <input type="date" name="date_from" value="{{ $filters['date_from'] }}"
                   class="h-10 rounded-lg px-3 text-sm outline-none transition"
                   style="border:1px solid var(--sa-border);background:var(--sa-bg);color:var(--sa-fg);">
            <input type="date" name="date_to" value="{{ $filters['date_to'] }}"
                   class="h-10 rounded-lg px-3 text-sm outline-none transition"
                   style="border:1px solid var(--sa-border);background:var(--sa-bg);color:var(--sa-fg);">
            <button type="submit"
                    class="h-10 rounded-lg px-5 text-sm font-medium transition-colors"
                    style="background:var(--sa-primary);color:var(--sa-primary-fg);">
                Filtrer
            </button>
        </form>
    </div>

    <!-- Transactions Table -->
    <div class="rounded-2xl border shadow-sm" style="border-color:var(--sa-border);background:var(--sa-card);">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead>
                    <tr style="border-bottom:1px solid var(--sa-border);">
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--sa-muted-fg);">Référence</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--sa-muted-fg);">Restaurant</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--sa-muted-fg);">Type</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--sa-muted-fg);">Méthode</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--sa-muted-fg);">Montant</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--sa-muted-fg);">Date</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--sa-muted-fg);">Statut</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($transactions as $transaction)
                    @php
                        $statusStyles = [
                            'active'    => 'background:rgba(61,158,98,0.15);color:var(--sa-success);',
                            'completed' => 'background:rgba(61,158,98,0.15);color:var(--sa-success);',
                            'pending'   => 'background:rgba(217,119,6,0.15);color:var(--sa-warning);',
                            'failed'    => 'background:rgba(220,38,38,0.15);color:var(--sa-danger);',
                            'cancelled' => 'background:rgba(220,38,38,0.15);color:var(--sa-danger);',
                            'expired'   => 'background:rgba(107,101,96,0.15);color:var(--sa-muted-fg);',
                        ];
                        $typeStyles = [
                            'subscription' => 'background:rgba(59,111,212,0.15);color:var(--sa-info);',
                            'order'        => 'background:rgba(194,98,31,0.15);color:var(--sa-primary);',
                            'refund'       => 'background:rgba(220,38,38,0.15);color:var(--sa-danger);',
                        ];
                    @endphp
                    <tr style="border-bottom:1px solid var(--sa-border);">
                        <td class="px-5 py-3.5">
                            <span class="font-mono text-sm font-medium" style="color:var(--sa-fg);">{{ $transaction['id'] }}</span>
                        </td>
                        <td class="px-5 py-3.5">
                            @if($transaction['restaurant'])
                                <a href="{{ route('super-admin.restaurants.show', $transaction['restaurant']) }}"
                                   class="font-medium transition-colors hover:underline" style="color:var(--sa-fg);">
                                    {{ $transaction['restaurant_name'] }}
                                </a>
                            @else
                                <span style="color:var(--sa-muted-fg);">N/A</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="rounded-md px-2 py-0.5 text-xs font-medium"
                                  style="{{ $typeStyles[$transaction['type']] ?? 'background:rgba(107,101,96,0.15);color:var(--sa-muted-fg);' }}">
                                {{ $transaction['type_label'] }}
                            </span>
                        </td>
                        <td class="px-5 py-3.5">
                            <span style="color:var(--sa-muted-fg);">{{ $transaction['payment_method'] }}</span>
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="font-semibold" style="color:{{ $transaction['amount'] < 0 ? 'var(--sa-danger)' : 'var(--sa-fg)' }};">
                                {{ $transaction['amount'] < 0 ? '−' : '+' }}{{ number_format(abs($transaction['amount']), 0, ',', ' ') }} F
                            </span>
                        </td>
                        <td class="px-5 py-3.5">
                            <span style="color:var(--sa-muted-fg);">{{ $transaction['created_at']->format('d/m/Y H:i') }}</span>
                        </td>
                        <td class="px-5 py-3.5">
                            <span class="rounded-md px-2 py-0.5 text-xs font-medium"
                                  style="{{ $statusStyles[$transaction['status']] ?? 'background:rgba(107,101,96,0.15);color:var(--sa-muted-fg);' }}">
                                {{ $transaction['status_label'] }}
                            </span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center text-sm" style="color:var(--sa-muted-fg);">
                            Aucune transaction trouvée.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($pagination['last_page'] > 1)
        <div class="flex items-center justify-between px-6 py-4" style="border-top:1px solid var(--sa-border);">
            <span class="text-sm" style="color:var(--sa-muted-fg);">
                {{ $pagination['total'] }} transaction(s)
            </span>
            <div class="flex items-center gap-2">
                @if($pagination['current_page'] > 1)
                    <a href="?page={{ $pagination['current_page'] - 1 }}&{{ http_build_query($filters) }}"
                       class="rounded-lg px-3 py-1.5 text-sm font-medium transition-colors"
                       style="border:1px solid var(--sa-border);background:var(--sa-card);color:var(--sa-fg);">
                        Précédent
                    </a>
                @endif
                <span class="text-sm" style="color:var(--sa-muted-fg);">
                    Page {{ $pagination['current_page'] }} / {{ $pagination['last_page'] }}
                </span>
                @if($pagination['current_page'] < $pagination['last_page'])
                    <a href="?page={{ $pagination['current_page'] + 1 }}&{{ http_build_query($filters) }}"
                       class="rounded-lg px-3 py-1.5 text-sm font-medium transition-colors"
                       style="border:1px solid var(--sa-border);background:var(--sa-card);color:var(--sa-fg);">
                        Suivant
                    </a>
                @endif
            </div>
        </div>
        @endif
    </div>

    @push('scripts')
    <script src="https://unpkg.com/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        function revenueChart() {
            return {
                init() {
                    const ctx = document.getElementById('revenueChart').getContext('2d');
                    const chartData = @json($chartData);
                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: chartData.map(d => d.date),
                            datasets: [
                                {
                                    label: 'Total',
                                    data: chartData.map(d => d.total),
                                    borderColor: 'rgb(34, 197, 94)',
                                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                                    fill: true,
                                    tension: 0.4,
                                    pointRadius: 2,
                                },
                                {
                                    label: 'Commandes',
                                    data: chartData.map(d => d.orders),
                                    borderColor: 'rgb(139, 92, 246)',
                                    borderDash: [5, 5],
                                    tension: 0.4,
                                    pointRadius: 2,
                                },
                                {
                                    label: 'Abonnements',
                                    data: chartData.map(d => d.subscriptions),
                                    borderColor: 'rgb(59, 130, 246)',
                                    borderDash: [5, 5],
                                    tension: 0.4,
                                    pointRadius: 2,
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { labels: { color: '#6b7280', boxWidth: 12 } },
                                tooltip: { callbacks: { label: ctx => ctx.dataset.label + ' : ' + ctx.parsed.y.toLocaleString('fr-FR') + ' F' } }
                            },
                            scales: {
                                x: {
                                    ticks: { color: '#6b7280', maxTicksLimit: 10 },
                                    grid: { color: 'rgba(209,213,219,0.5)' }
                                },
                                y: {
                                    beginAtZero: true,
                                    ticks: { color: '#6b7280', callback: v => (v / 1000).toFixed(0) + 'K' },
                                    grid: { color: 'rgba(209,213,219,0.5)' }
                                }
                            }
                        }
                    });
                }
            }
        }
    </script>
    @endpush
</x-layouts.admin-super>
