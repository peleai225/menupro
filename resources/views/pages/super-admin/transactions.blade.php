<x-layouts.admin-super title="Transactions">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-neutral-900">Transactions & Paiements</h1>
            <p class="text-neutral-500 mt-1">Suivi de tous les flux financiers de la plateforme.</p>
        </div>
        <a href="{{ route('super-admin.transactions.export') }}" class="btn btn-outline border-neutral-200 text-neutral-700 hover:bg-neutral-50">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            Exporter CSV
        </a>
    </div>

    <!-- Stats Cards -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-gradient-to-br from-emerald-50 to-emerald-50 border border-emerald-200 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-emerald-700">Revenus totaux</p>
                    <p class="text-2xl font-bold text-neutral-900 mt-1">{{ number_format($stats['total_revenue'], 0, ',', ' ') }} F</p>
                </div>
                <div class="w-12 h-12 bg-emerald-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white border border-neutral-200 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-neutral-500">Ce mois</p>
                    <p class="text-2xl font-bold text-neutral-900 mt-1">{{ number_format($stats['this_month_revenue'], 0, ',', ' ') }} F</p>
                </div>
                <div class="w-12 h-12 bg-primary-50 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white border border-neutral-200 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-neutral-500">Abonnements</p>
                    <p class="text-2xl font-bold text-blue-600 mt-1">{{ number_format($stats['subscriptions_revenue'], 0, ',', ' ') }} F</p>
                </div>
                <div class="w-12 h-12 bg-blue-50 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                    </svg>
                </div>
            </div>
        </div>

        <div class="bg-white border border-neutral-200 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-neutral-500">Commandes</p>
                    <p class="text-2xl font-bold text-accent-600 mt-1">{{ number_format($stats['orders_revenue'], 0, ',', ' ') }} F</p>
                </div>
                <div class="w-12 h-12 bg-accent-50 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-accent-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Chart -->
    <div class="bg-white border border-neutral-200 rounded-2xl p-6 mb-8 shadow-sm">
        <h3 class="text-lg font-semibold text-neutral-900 mb-4">Revenus des 30 derniers jours</h3>
        <div class="h-64" x-data="revenueChart()" x-init="init()">
            <canvas id="revenueChart"></canvas>
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
                    <input type="text" name="search" value="{{ $filters['search'] }}" placeholder="Rechercher par référence, restaurant..." 
                           class="w-full h-10 pl-10 pr-4 bg-white border border-neutral-200 rounded-lg text-neutral-900 placeholder-neutral-400 focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>
            </div>
            <select name="type" class="h-10 px-4 bg-white border border-neutral-200 rounded-lg text-neutral-900 focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="all" {{ $filters['type'] === 'all' ? 'selected' : '' }}>Tous les types</option>
                <option value="subscription" {{ $filters['type'] === 'subscription' ? 'selected' : '' }}>Abonnements</option>
                <option value="order" {{ $filters['type'] === 'order' ? 'selected' : '' }}>Commandes</option>
                <option value="refund" {{ $filters['type'] === 'refund' ? 'selected' : '' }}>Remboursements</option>
            </select>
            <select name="status" class="h-10 px-4 bg-white border border-neutral-200 rounded-lg text-neutral-900 focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="" {{ empty($filters['status']) ? 'selected' : '' }}>Tous les statuts</option>
                <option value="completed" {{ $filters['status'] === 'completed' ? 'selected' : '' }}>Complété</option>
                <option value="pending" {{ $filters['status'] === 'pending' ? 'selected' : '' }}>En attente</option>
                <option value="failed" {{ $filters['status'] === 'failed' ? 'selected' : '' }}>Échoué</option>
            </select>
            <input type="date" name="date_from" value="{{ $filters['date_from'] }}" 
                   class="h-10 px-4 bg-white border border-neutral-200 rounded-lg text-neutral-900 focus:outline-none focus:ring-2 focus:ring-primary-500">
            <input type="date" name="date_to" value="{{ $filters['date_to'] }}" 
                   class="h-10 px-4 bg-white border border-neutral-200 rounded-lg text-neutral-900 focus:outline-none focus:ring-2 focus:ring-primary-500">
            <button type="submit" class="h-10 px-6 bg-primary-500 text-white rounded-lg font-medium hover:bg-primary-600 transition-colors">
                Filtrer
            </button>
        </div>
    </form>

    <!-- Transactions Table -->
    <div class="bg-white border border-neutral-200 rounded-2xl overflow-hidden shadow-sm">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-neutral-50 border-b border-neutral-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-600 uppercase">ID</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-600 uppercase">Type</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-600 uppercase">Restaurant</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-600 uppercase">Description</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-600 uppercase">Montant</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-600 uppercase">Méthode</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-600 uppercase">Statut</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-600 uppercase">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100">
                    @forelse($transactions as $transaction)
                        <tr class="hover:bg-neutral-50 transition-colors">
                            <td class="px-6 py-4">
                                <span class="font-mono text-sm text-neutral-700">{{ $transaction['id'] }}</span>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $typeColors = [
                                        'subscription' => 'bg-blue-500/20 text-blue-400',
                                        'order' => 'bg-accent-500/20 text-accent-400',
                                        'refund' => 'bg-red-500/20 text-red-400',
                                    ];
                                @endphp
                                <span class="px-2.5 py-1 rounded-lg text-xs font-medium {{ $typeColors[$transaction['type']] ?? 'bg-neutral-500/20 text-neutral-400' }}">
                                    {{ $transaction['type_label'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                @if($transaction['restaurant'])
                                    <a href="{{ route('super-admin.restaurants.show', $transaction['restaurant']) }}" class="text-neutral-900 hover:text-primary-700 transition-colors">
                                        {{ $transaction['restaurant_name'] }}
                                    </a>
                                @else
                                    <span class="text-neutral-500">N/A</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-neutral-700">{{ $transaction['description'] }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-semibold {{ $transaction['amount'] < 0 ? 'text-red-400' : 'text-secondary-400' }}">
                                    {{ $transaction['amount'] < 0 ? '-' : '+' }}{{ number_format(abs($transaction['amount']), 0, ',', ' ') }} F
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-neutral-600 text-sm">{{ $transaction['payment_method'] }}</span>
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $statusColors = [
                                        'active' => 'bg-secondary-500/20 text-secondary-400',
                                        'completed' => 'bg-secondary-500/20 text-secondary-400',
                                        'pending' => 'bg-yellow-500/20 text-yellow-400',
                                        'failed' => 'bg-red-500/20 text-red-400',
                                        'cancelled' => 'bg-red-500/20 text-red-400',
                                        'expired' => 'bg-neutral-500/20 text-neutral-400',
                                    ];
                                @endphp
                                <span class="px-2.5 py-1 rounded-lg text-xs font-medium {{ $statusColors[$transaction['status']] ?? 'bg-neutral-500/20 text-neutral-400' }}">
                                    {{ $transaction['status_label'] }}
                                </span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-neutral-600 text-sm">{{ $transaction['created_at']->format('d/m/Y H:i') }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center text-neutral-500">
                                Aucune transaction trouvée.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        @if($pagination['last_page'] > 1)
            <div class="px-6 py-4 border-t border-neutral-200 flex items-center justify-between">
                <span class="text-sm text-neutral-600">
                    {{ $pagination['total'] }} transaction(s) au total
                </span>
                <div class="flex items-center gap-2">
                    @if($pagination['current_page'] > 1)
                        <a href="?page={{ $pagination['current_page'] - 1 }}&{{ http_build_query($filters) }}" 
                           class="px-3 py-1.5 bg-neutral-100 text-neutral-800 rounded-lg hover:bg-neutral-200 transition-colors border border-neutral-200">
                            Précédent
                        </a>
                    @endif
                    <span class="text-neutral-600">
                        Page {{ $pagination['current_page'] }} / {{ $pagination['last_page'] }}
                    </span>
                    @if($pagination['current_page'] < $pagination['last_page'])
                        <a href="?page={{ $pagination['current_page'] + 1 }}&{{ http_build_query($filters) }}" 
                           class="px-3 py-1.5 bg-neutral-100 text-neutral-800 rounded-lg hover:bg-neutral-200 transition-colors border border-neutral-200">
                            Suivant
                        </a>
                    @endif
                </div>
            </div>
        @endif
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
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
                                },
                                {
                                    label: 'Commandes',
                                    data: chartData.map(d => d.orders),
                                    borderColor: 'rgb(139, 92, 246)',
                                    borderDash: [5, 5],
                                    tension: 0.4,
                                },
                                {
                                    label: 'Abonnements',
                                    data: chartData.map(d => d.subscriptions),
                                    borderColor: 'rgb(59, 130, 246)',
                                    borderDash: [5, 5],
                                    tension: 0.4,
                                }
                            ]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: {
                                    labels: { color: '#374151' }
                                }
                            },
                            scales: {
                                x: {
                                    ticks: { color: '#6b7280' },
                                    grid: { color: 'rgba(229, 231, 235, 1)' }
                                },
                                y: {
                                    ticks: { 
                                        color: '#6b7280',
                                        callback: function(value) {
                                            return value.toLocaleString() + ' F';
                                        }
                                    },
                                    grid: { color: 'rgba(229, 231, 235, 1)' }
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
