<x-layouts.admin-super title="Statistiques">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-neutral-900">Statistiques</h1>
        <p class="text-neutral-500 mt-1">Vue d'ensemble des performances de la plateforme.</p>
    </div>

    <!-- Period Filter -->
    <form method="GET" class="flex gap-2 mb-8">
        @foreach([
            '7' => '7 jours',
            '30' => '30 jours',
            '90' => '90 jours',
            '365' => '1 an',
        ] as $value => $label)
            <button type="submit"
                    name="period"
                    value="{{ $value }}"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ $period == $value ? 'bg-primary-500 text-white' : 'bg-white border border-neutral-200 text-neutral-600 hover:bg-neutral-50' }}">
                {{ $label }}
            </button>
        @endforeach
    </form>

    <!-- KPIs -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-white border border-neutral-200 shadow-sm rounded-2xl p-6">
            <p class="text-sm text-neutral-500 mb-1">Revenus totaux</p>
            <p class="text-3xl font-bold text-neutral-900">
                @if($summary['total_revenue'] >= 1000000)
                    {{ number_format($summary['total_revenue'] / 1000000, 1, ',', ' ') }}M
                @else
                    {{ number_format($summary['total_revenue'] / 1000, 0, ',', ' ') }}K
                @endif
                <span class="text-lg font-normal text-neutral-500">F</span>
            </p>
            <p class="text-sm text-neutral-500 mt-2">Sur les {{ $period }} derniers jours</p>
        </div>
        <div class="bg-white border border-neutral-200 shadow-sm rounded-2xl p-6">
            <p class="text-sm text-neutral-500 mb-1">Nouveaux restaurants</p>
            <p class="text-3xl font-bold text-neutral-900">{{ number_format($summary['new_restaurants']) }}</p>
            <p class="text-sm text-neutral-500 mt-2">Sur les {{ $period }} derniers jours</p>
        </div>
        <div class="bg-white border border-neutral-200 shadow-sm rounded-2xl p-6">
            <p class="text-sm text-neutral-500 mb-1">Commandes traitées</p>
            <p class="text-3xl font-bold text-neutral-900">
                @if($summary['total_orders'] >= 1000)
                    {{ number_format($summary['total_orders'] / 1000, 1, ',', ' ') }}K
                @else
                    {{ number_format($summary['total_orders']) }}
                @endif
            </p>
            <p class="text-sm text-neutral-500 mt-2">Sur les {{ $period }} derniers jours</p>
        </div>
        <div class="bg-white border border-neutral-200 shadow-sm rounded-2xl p-6">
            <p class="text-sm text-neutral-500 mb-1">Panier moyen</p>
            <p class="text-3xl font-bold text-neutral-900">{{ number_format($summary['average_order'], 0, ',', ' ') }} <span class="text-lg font-normal text-neutral-500">F</span></p>
            <p class="text-sm text-neutral-500 mt-2">Par commande</p>
        </div>
    </div>

    <!-- Charts Row 1: Revenue trend + Weekly trend -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Graphique 1 — Line chart "Évolution des revenus" -->
        <div class="bg-white border border-neutral-200 shadow-sm rounded-2xl p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-neutral-900">Évolution des revenus</h3>
                <span class="text-xs text-neutral-400">{{ $period }} jours</span>
            </div>
            <div class="relative h-64">
                <canvas id="revenueLineChart"></canvas>
            </div>
        </div>

        <!-- Graphique 3 — Line chart "Tendance des commandes" -->
        <div class="bg-white border border-neutral-200 shadow-sm rounded-2xl p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-neutral-900">Tendance des commandes</h3>
                <span class="text-xs text-neutral-400">{{ $period }} jours</span>
            </div>
            <div class="relative h-64">
                <canvas id="trendChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Charts Row 2: Top days + City donut -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Graphique 1 — Bar chart horizontal "Top jours d'activité" -->
        <div class="bg-white border border-neutral-200 shadow-sm rounded-2xl p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-neutral-900">Top jours d'activité</h3>
                <span class="text-xs text-neutral-400">{{ $period }} jours — top 10</span>
            </div>
            <div class="relative h-72">
                <canvas id="topDaysChart"></canvas>
            </div>
        </div>

        <!-- Graphique 2 — Donut "Répartition par ville" -->
        <div class="bg-white border border-neutral-200 shadow-sm rounded-2xl p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-neutral-900">Répartition par ville</h3>
                <span class="text-xs text-neutral-400">restaurants actifs</span>
            </div>
            <div class="relative h-72">
                <canvas id="cityChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Additional Stats Row: Plan distribution + Orders by type + Subscription revenue -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Plan Distribution — donut -->
        <div class="bg-white border border-neutral-200 shadow-sm rounded-2xl p-6">
            <h3 class="text-lg font-semibold text-neutral-900 mb-4">Répartition par plan</h3>
            <div class="relative h-56">
                <canvas id="planChart"></canvas>
            </div>
        </div>

        <!-- Orders by Type — donut -->
        <div class="bg-white border border-neutral-200 shadow-sm rounded-2xl p-6">
            <h3 class="text-lg font-semibold text-neutral-900 mb-4">Commandes par type</h3>
            <div class="relative h-56">
                <canvas id="orderTypeChart"></canvas>
            </div>
        </div>

        <!-- Subscription Revenue — bar chart -->
        <div class="bg-white border border-neutral-200 shadow-sm rounded-2xl p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-lg font-semibold text-neutral-900">Revenus abonnements</h3>
                <span class="text-xl font-bold text-secondary-400">
                    {{ number_format($summary['subscription_revenue'], 0, ',', ' ') }} F
                </span>
            </div>
            <div class="relative h-44">
                <canvas id="subscriptionChart"></canvas>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        // ── Shared defaults ──────────────────────────────────────────────
        const gridColor = 'rgba(209, 213, 219, 0.5)';
        const tickColor = '#6b7280';

        // ── 1. Évolution des revenus (line) ──────────────────────────────
        new Chart(document.getElementById('revenueLineChart'), {
            type: 'line',
            data: {
                labels: @json($revenueData->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d M'))),
                datasets: [{
                    label: 'Revenus (F)',
                    data: @json($revenueData->pluck('revenue')),
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 3,
                    pointHoverRadius: 5,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => ctx.parsed.y.toLocaleString('fr-FR') + ' F'
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { color: tickColor, callback: v => (v / 1000).toFixed(0) + 'K' },
                        grid: { color: gridColor }
                    },
                    x: {
                        ticks: { color: tickColor, maxTicksLimit: 10 },
                        grid: { color: gridColor }
                    }
                }
            }
        });

        // ── 2. Tendance des commandes (line — emerald, fill) ─────────────
        new Chart(document.getElementById('trendChart'), {
            type: 'line',
            data: {
                labels: @json($weeklyTrend->pluck('week_start')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d M'))),
                datasets: [{
                    label: 'Commandes',
                    data: @json($weeklyTrend->pluck('orders')),
                    borderColor: 'rgb(16, 185, 129)',
                    backgroundColor: 'rgba(16, 185, 129, 0.12)',
                    tension: 0.4,
                    fill: true,
                    pointRadius: 3,
                    pointHoverRadius: 5,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => ctx.parsed.y + ' commandes'
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { color: tickColor },
                        grid: { color: gridColor }
                    },
                    x: {
                        ticks: { color: tickColor, maxTicksLimit: 10 },
                        grid: { color: gridColor }
                    }
                }
            }
        });

        // ── 3. Top jours d'activité (horizontal bar) ─────────────────────
        new Chart(document.getElementById('topDaysChart'), {
            type: 'bar',
            data: {
                labels: @json($topDays->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->isoFormat('ddd D MMM'))),
                datasets: [{
                    label: 'Commandes',
                    data: @json($topDays->pluck('orders')),
                    backgroundColor: 'rgba(59, 130, 246, 0.75)',
                    borderColor: 'rgb(59, 130, 246)',
                    borderWidth: 1,
                    borderRadius: 4,
                }]
            },
            options: {
                indexAxis: 'y',
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => ctx.parsed.x + ' commandes'
                        }
                    }
                },
                scales: {
                    x: {
                        beginAtZero: true,
                        ticks: { color: tickColor },
                        grid: { color: gridColor }
                    },
                    y: {
                        ticks: { color: tickColor },
                        grid: { display: false }
                    }
                }
            }
        });

        // ── 4. Répartition par ville (donut) ─────────────────────────────
        @php
            $cityLabels = $topCities->take(8)->pluck('city');
            $cityCounts = $topCities->take(8)->pluck('count');
        @endphp
        new Chart(document.getElementById('cityChart'), {
            type: 'doughnut',
            data: {
                labels: @json($cityLabels),
                datasets: [{
                    data: @json($cityCounts),
                    backgroundColor: [
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(239, 68, 68, 0.8)',
                        'rgba(139, 92, 246, 0.8)',
                        'rgba(236, 72, 153, 0.8)',
                        'rgba(20, 184, 166, 0.8)',
                        'rgba(249, 115, 22, 0.8)',
                    ],
                    borderWidth: 2,
                    borderColor: '#fff',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '60%',
                plugins: {
                    legend: {
                        position: 'right',
                        labels: { color: tickColor, boxWidth: 12, padding: 12 }
                    },
                    tooltip: {
                        callbacks: {
                            label: ctx => ctx.label + ' — ' + ctx.parsed + ' restaurants'
                        }
                    }
                }
            }
        });

        // ── 5. Répartition par plan (donut) ──────────────────────────────
        new Chart(document.getElementById('planChart'), {
            type: 'doughnut',
            data: {
                labels: @json($planDistribution->pluck('name')),
                datasets: [{
                    data: @json($planDistribution->pluck('count')),
                    backgroundColor: [
                        'rgba(59, 130, 246, 0.8)',
                        'rgba(16, 185, 129, 0.8)',
                        'rgba(245, 158, 11, 0.8)',
                        'rgba(139, 92, 246, 0.8)',
                    ],
                    borderWidth: 2,
                    borderColor: '#fff',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '55%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { color: tickColor, boxWidth: 12, padding: 10 }
                    }
                }
            }
        });

        // ── 6. Commandes par type (donut) ─────────────────────────────────
        @php
            $typeLabels = ['dine_in' => 'Sur place', 'takeaway' => 'À emporter', 'delivery' => 'Livraison'];
            $typeColors = [
                'rgba(59, 130, 246, 0.8)',
                'rgba(245, 158, 11, 0.8)',
                'rgba(16, 185, 129, 0.8)',
            ];
            $mappedTypeLabels = $ordersByType->map(fn($t) => $typeLabels[$t->type->value ?? $t->type] ?? ($t->type->value ?? $t->type));
        @endphp
        new Chart(document.getElementById('orderTypeChart'), {
            type: 'doughnut',
            data: {
                labels: @json($mappedTypeLabels->values()),
                datasets: [{
                    data: @json($ordersByType->pluck('count')),
                    backgroundColor: @json($typeColors),
                    borderWidth: 2,
                    borderColor: '#fff',
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                cutout: '55%',
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: { color: tickColor, boxWidth: 12, padding: 10 }
                    }
                }
            }
        });

        // ── 7. Revenus abonnements (bar) ──────────────────────────────────
        new Chart(document.getElementById('subscriptionChart'), {
            type: 'bar',
            data: {
                labels: @json($subscriptionRevenue->pluck('date')->map(fn($d) => \Carbon\Carbon::parse($d)->format('d/m'))),
                datasets: [{
                    label: 'Revenus (F)',
                    data: @json($subscriptionRevenue->pluck('revenue')),
                    backgroundColor: 'rgba(16, 185, 129, 0.7)',
                    borderColor: 'rgb(16, 185, 129)',
                    borderWidth: 1,
                    borderRadius: 4,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        callbacks: {
                            label: ctx => ctx.parsed.y.toLocaleString('fr-FR') + ' F'
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: { color: tickColor, callback: v => (v / 1000).toFixed(0) + 'K' },
                        grid: { color: gridColor }
                    },
                    x: {
                        ticks: { color: tickColor },
                        grid: { display: false }
                    }
                }
            }
        });
    </script>
    @endpush
</x-layouts.admin-super>
