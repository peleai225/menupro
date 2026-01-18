<div>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-neutral-900">Statistiques & Analytics</h1>
            <p class="text-neutral-500 mt-1">Analysez les performances de votre restaurant en détail.</p>
        </div>
        <div class="flex items-center gap-3">
            <select wire:model.live="period" class="h-12 px-4 bg-white border border-neutral-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="7">7 derniers jours</option>
                <option value="30">30 derniers jours</option>
                <option value="90">3 derniers mois</option>
                <option value="365">1 an</option>
            </select>
        </div>
    </div>

    @php
        $stats = $this->stats;
    @endphp

    <!-- Key Metrics -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <!-- Total Revenue -->
        <div class="card p-6">
            <div class="flex items-center justify-between mb-4">
                <p class="text-sm font-medium text-neutral-500">Chiffre d'affaires</p>
                <div class="w-12 h-12 bg-secondary-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-secondary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-neutral-900 mb-2">
                {{ number_format($stats['total_revenue'] ?? 0, 0, ',', ' ') }} <span class="text-lg font-normal">F</span>
            </p>
            <p class="text-sm flex items-center gap-1 {{ ($stats['revenue_change'] ?? 0) >= 0 ? 'text-secondary-600' : 'text-red-600' }}">
                @if(($stats['revenue_change'] ?? 0) >= 0)
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                @else
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                    </svg>
                @endif
                {{ ($stats['revenue_change'] ?? 0) >= 0 ? '+' : '' }}{{ $stats['revenue_change'] ?? 0 }}% vs période précédente
            </p>
        </div>

        <!-- Total Orders -->
        <div class="card p-6">
            <div class="flex items-center justify-between mb-4">
                <p class="text-sm font-medium text-neutral-500">Commandes</p>
                <div class="w-12 h-12 bg-primary-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-neutral-900 mb-2">{{ $stats['total_orders'] ?? 0 }}</p>
            <p class="text-sm flex items-center gap-1 {{ ($stats['orders_change'] ?? 0) >= 0 ? 'text-secondary-600' : 'text-red-600' }}">
                @if(($stats['orders_change'] ?? 0) >= 0)
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                @else
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 17h8m0 0V9m0 8l-8-8-4 4-6-6"/>
                    </svg>
                @endif
                {{ ($stats['orders_change'] ?? 0) >= 0 ? '+' : '' }}{{ $stats['orders_change'] ?? 0 }}% vs période précédente
            </p>
        </div>

        <!-- Average Order -->
        <div class="card p-6">
            <div class="flex items-center justify-between mb-4">
                <p class="text-sm font-medium text-neutral-500">Panier moyen</p>
                <div class="w-12 h-12 bg-accent-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-accent-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                </div>
            </div>
            <p class="text-3xl font-bold text-neutral-900 mb-2">
                {{ number_format($stats['average_order'] ?? 0, 0, ',', ' ') }} <span class="text-lg font-normal">F</span>
            </p>
            <p class="text-sm text-neutral-500">Par commande</p>
        </div>
    </div>

    <!-- Charts Grid -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Revenue Chart -->
        <div class="card p-6">
            <h2 class="text-lg font-bold text-neutral-900 mb-4">Évolution du chiffre d'affaires</h2>
            <div class="relative h-64">
                <canvas id="revenueChart"></canvas>
            </div>
        </div>

        <!-- Orders by Status -->
        <div class="card p-6">
            <h2 class="text-lg font-bold text-neutral-900 mb-4">Commandes par statut</h2>
            <div class="relative h-64">
                <canvas id="statusChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Additional Stats -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Orders by Type -->
        <div class="card p-6">
            <h2 class="text-lg font-bold text-neutral-900 mb-4">Commandes par type</h2>
            <div class="space-y-4">
                @php
                    $types = [
                        'dine_in' => ['label' => 'Sur place', 'color' => 'bg-primary-500'],
                        'takeaway' => ['label' => 'À emporter', 'color' => 'bg-secondary-500'],
                        'delivery' => ['label' => 'Livraison', 'color' => 'bg-accent-500'],
                    ];
                    $totalTypeOrders = array_sum($stats['orders_by_type'] ?? []);
                @endphp
                @foreach($types as $type => $info)
                    @php
                        $count = $stats['orders_by_type'][$type] ?? 0;
                        $percentage = $totalTypeOrders > 0 ? round(($count / $totalTypeOrders) * 100) : 0;
                    @endphp
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm font-medium text-neutral-700">{{ $info['label'] }}</span>
                            <span class="text-sm font-bold text-neutral-900">{{ $count }} ({{ $percentage }}%)</span>
                        </div>
                        <div class="h-2 bg-neutral-100 rounded-full overflow-hidden">
                            <div class="h-full {{ $info['color'] }} rounded-full transition-all" style="width: {{ $percentage }}%"></div>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Peak Hours -->
        <div class="card p-6">
            <h2 class="text-lg font-bold text-neutral-900 mb-4">Heures de pointe</h2>
            <div class="space-y-3">
                @forelse($stats['peak_hours'] ?? [] as $peak)
                    <div class="flex items-center justify-between">
                        <span class="text-sm font-medium text-neutral-700">{{ $peak->hour }}h - {{ $peak->hour + 1 }}h</span>
                        <div class="flex items-center gap-3">
                            <div class="w-32 h-2 bg-neutral-100 rounded-full overflow-hidden">
                                @php
                                    $maxCount = ($stats['peak_hours'] ?? collect())->max('count') ?? 1;
                                    $width = ($peak->count / $maxCount) * 100;
                                @endphp
                                <div class="h-full bg-primary-500 rounded-full transition-all" style="width: {{ $width }}%"></div>
                            </div>
                            <span class="text-sm font-bold text-neutral-900 w-12 text-right">{{ $peak->count }}</span>
                        </div>
                    </div>
                @empty
                    <p class="text-neutral-500 text-sm text-center py-4">Aucune donnée disponible</p>
                @endforelse
            </div>
        </div>
    </div>

    <!-- Top Dishes -->
    <div class="card p-6">
        <h2 class="text-lg font-bold text-neutral-900 mb-4">Plats les plus vendus</h2>
        <div class="table-responsive">
            <table class="w-full min-w-[600px]">
                <thead>
                    <tr class="border-b border-neutral-200">
                        <th class="text-left py-3 px-4 text-sm font-semibold text-neutral-700">Plat</th>
                        <th class="text-right py-3 px-4 text-sm font-semibold text-neutral-700">Quantité</th>
                        <th class="text-right py-3 px-4 text-sm font-semibold text-neutral-700">Revenus</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100">
                    @forelse($stats['top_dishes'] ?? [] as $dish)
                        <tr class="hover:bg-neutral-50 transition-colors">
                            <td class="py-3 px-4">
                                <div class="font-medium text-neutral-900">{{ $dish->name }}</div>
                                <div class="text-sm text-neutral-500">{{ number_format($dish->price, 0, ',', ' ') }} F</div>
                            </td>
                            <td class="py-3 px-4 text-right font-medium text-neutral-900">{{ $dish->total_sold }}</td>
                            <td class="py-3 px-4 text-right font-bold text-neutral-900">{{ number_format($dish->total_revenue, 0, ',', ' ') }} F</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="py-8 text-center text-neutral-500">Aucune donnée disponible</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @push('scripts')
        <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
        <script>
            document.addEventListener('DOMContentLoaded', function() {
                // Revenue Chart
                const revenueCtx = document.getElementById('revenueChart');
                if (revenueCtx) {
                    const revenueData = @json($stats['revenue_by_day'] ?? []);
                    new Chart(revenueCtx, {
                        type: 'line',
                        data: {
                            labels: revenueData.map(item => new Date(item.date).toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' })),
                            datasets: [{
                                label: 'Revenus (FCFA)',
                                data: revenueData.map(item => item.revenue),
                                borderColor: 'rgb(249, 115, 22)',
                                backgroundColor: 'rgba(249, 115, 22, 0.1)',
                                tension: 0.4,
                                fill: true
                            }]
                        },
                            options: {
                                responsive: true,
                                maintainAspectRatio: true,
                                aspectRatio: 2,
                                plugins: {
                                    legend: {
                                        display: false
                                    }
                                },
                                scales: {
                                    y: {
                                        beginAtZero: true,
                                        ticks: {
                                            callback: function(value) {
                                                return new Intl.NumberFormat('fr-FR').format(value) + ' F';
                                            }
                                        }
                                    }
                                }
                            }
                    });
                }

                // Status Chart
                const statusCtx = document.getElementById('statusChart');
                if (statusCtx) {
                    const statusData = @json($stats['orders_by_status'] ?? []);
                    const statusLabels = {
                        'pending_payment': 'En attente',
                        'paid': 'Payée',
                        'confirmed': 'Confirmée',
                        'preparing': 'En préparation',
                        'ready': 'Prête',
                        'completed': 'Terminée',
                        'cancelled': 'Annulée'
                    };
                    new Chart(statusCtx, {
                        type: 'doughnut',
                        data: {
                            labels: Object.keys(statusData).map(key => statusLabels[key] || key),
                            datasets: [{
                                data: Object.values(statusData),
                                backgroundColor: [
                                    'rgb(239, 68, 68)',
                                    'rgb(249, 115, 22)',
                                    'rgb(59, 130, 246)',
                                    'rgb(16, 185, 129)',
                                    'rgb(139, 92, 246)',
                                    'rgb(34, 197, 94)',
                                    'rgb(107, 114, 128)'
                                ]
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: true,
                            aspectRatio: 2,
                            plugins: {
                                legend: {
                                    position: 'bottom'
                                }
                            }
                        }
                    });
                }
            });
        </script>
    @endpush
</div>

