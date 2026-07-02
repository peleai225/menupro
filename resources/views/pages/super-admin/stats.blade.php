<x-layouts.admin-super title="Statistiques">
    <!-- Header + Period Filter -->
    <div class="mb-6 flex flex-col gap-4 sm:flex-row sm:items-center sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold" style="color:var(--sa-fg);">Statistiques</h1>
            <p class="mt-1 text-sm" style="color:var(--sa-muted-fg);">Analyse de la performance globale de la plateforme</p>
        </div>
        <form method="GET" class="flex flex-wrap gap-2">
            @foreach([
                '7'   => '7 jours',
                '30'  => '30 jours',
                '90'  => '90 jours',
                '365' => '1 an',
            ] as $value => $label)
                <button type="submit" name="period" value="{{ $value }}"
                        class="rounded-lg px-4 py-2 text-sm font-medium transition-colors"
                        style="{{ $period == $value
                            ? 'background:var(--sa-primary);color:var(--sa-primary-fg);'
                            : 'background:var(--sa-card);color:var(--sa-muted-fg);border:1px solid var(--sa-border);' }}">
                    {{ $label }}
                </button>
            @endforeach
        </form>
    </div>

    <!-- 4 StatCards -->
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <!-- Revenus totaux -->
        <div class="rounded-2xl border p-5 shadow-sm" style="border-color:var(--sa-border);background:var(--sa-card);">
            <div class="flex size-10 items-center justify-center rounded-xl" style="background:color-mix(in oklch,var(--sa-primary) 12%,transparent);">
                <svg class="size-5" style="color:var(--sa-primary);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <p class="mt-3 text-2xl font-bold" style="color:var(--sa-fg);">
                @if($summary['total_revenue'] >= 1000000)
                    {{ number_format($summary['total_revenue'] / 1000000, 1, ',', ' ') }}M F
                @else
                    {{ number_format($summary['total_revenue'] / 1000, 0, ',', ' ') }}K F
                @endif
            </p>
            <p class="mt-0.5 text-sm font-medium" style="color:var(--sa-muted-fg);">Revenus totaux</p>
            <p class="mt-1 text-xs" style="color:var(--sa-muted-fg);">Sur {{ $period }} jours</p>
        </div>

        <!-- Commandes traitées -->
        <div class="rounded-2xl border p-5 shadow-sm" style="border-color:var(--sa-border);background:var(--sa-card);">
            <div class="flex size-10 items-center justify-center rounded-xl" style="background:color-mix(in oklch,var(--sa-info) 12%,transparent);">
                <svg class="size-5" style="color:var(--sa-info);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </div>
            <p class="mt-3 text-2xl font-bold" style="color:var(--sa-fg);">
                @if($summary['total_orders'] >= 1000)
                    {{ number_format($summary['total_orders'] / 1000, 1, ',', ' ') }}K
                @else
                    {{ number_format($summary['total_orders']) }}
                @endif
            </p>
            <p class="mt-0.5 text-sm font-medium" style="color:var(--sa-muted-fg);">Commandes traitées</p>
            <p class="mt-1 text-xs" style="color:var(--sa-muted-fg);">Sur {{ $period }} jours</p>
        </div>

        <!-- Revenus abonnements -->
        <div class="rounded-2xl border p-5 shadow-sm" style="border-color:var(--sa-border);background:var(--sa-card);">
            <div class="flex size-10 items-center justify-center rounded-xl" style="background:color-mix(in oklch,var(--sa-success) 12%,transparent);">
                <svg class="size-5" style="color:var(--sa-success);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                </svg>
            </div>
            <p class="mt-3 text-2xl font-bold" style="color:var(--sa-fg);">{{ number_format($summary['subscription_revenue'], 0, ',', ' ') }} F</p>
            <p class="mt-0.5 text-sm font-medium" style="color:var(--sa-muted-fg);">Revenus abonnements</p>
            <p class="mt-1 text-xs" style="color:var(--sa-muted-fg);">Sur {{ $period }} jours</p>
        </div>

        <!-- Panier moyen -->
        <div class="rounded-2xl border p-5 shadow-sm" style="border-color:var(--sa-border);background:var(--sa-card);">
            <div class="flex size-10 items-center justify-center rounded-xl" style="background:color-mix(in oklch,var(--sa-warning) 12%,transparent);">
                <svg class="size-5" style="color:var(--sa-warning);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                </svg>
            </div>
            <p class="mt-3 text-2xl font-bold" style="color:var(--sa-fg);">{{ number_format($summary['average_order'], 0, ',', ' ') }} F</p>
            <p class="mt-0.5 text-sm font-medium" style="color:var(--sa-muted-fg);">Panier moyen</p>
            <p class="mt-1 text-xs" style="color:var(--sa-muted-fg);">Par commande</p>
        </div>
    </div>

    <!-- Row 1: OrdersTrend (2/3) + PaymentSplit donut (1/3) -->
    <div class="mb-6 grid grid-cols-1 gap-6 lg:grid-cols-3">
        <div class="rounded-2xl border p-5 shadow-sm lg:col-span-2" style="border-color:var(--sa-border);background:var(--sa-card);">
            <div class="mb-5 flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold" style="color:var(--sa-fg);">Tendance des commandes</h2>
                    <p class="text-sm" style="color:var(--sa-muted-fg);">{{ $period }} derniers jours</p>
                </div>
            </div>
            <div style="height:280px;">
                <canvas id="trendChart"></canvas>
            </div>
        </div>

        <div class="rounded-2xl border p-5 shadow-sm" style="border-color:var(--sa-border);background:var(--sa-card);">
            <div class="mb-5 flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold" style="color:var(--sa-fg);">Modes de commande</h2>
                    <p class="text-sm" style="color:var(--sa-muted-fg);">Répartition</p>
                </div>
            </div>
            <div style="height:280px;">
                <canvas id="orderTypeChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Row 2: RevenueByPlan + Top villes ranking -->
    <div class="mb-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="rounded-2xl border p-5 shadow-sm" style="border-color:var(--sa-border);background:var(--sa-card);">
            <div class="mb-5 flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold" style="color:var(--sa-fg);">Répartition par plan</h2>
                    <p class="text-sm" style="color:var(--sa-muted-fg);">Restaurants actifs par abonnement</p>
                </div>
            </div>
            <div style="height:280px;">
                <canvas id="planChart"></canvas>
            </div>
        </div>

        <div class="rounded-2xl border p-5 shadow-sm" style="border-color:var(--sa-border);background:var(--sa-card);">
            <div class="mb-5 flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold" style="color:var(--sa-fg);">Top villes</h2>
                    <p class="text-sm" style="color:var(--sa-muted-fg);">Par nombre de restaurants</p>
                </div>
            </div>
            @php $maxCityCount = $topCities->max('count') ?: 1; @endphp
            <ul class="flex flex-col gap-4">
                @foreach($topCities->take(6) as $city)
                @php $pct = round(($city->count / $maxCityCount) * 100); @endphp
                <li class="flex items-center gap-3">
                    <span class="flex size-7 shrink-0 items-center justify-center rounded-lg text-sm font-bold"
                          style="background:color-mix(in oklch,var(--sa-primary) 10%,transparent);color:var(--sa-primary);">
                        {{ $loop->iteration }}
                    </span>
                    <div class="min-w-0 flex-1">
                        <div class="flex items-center justify-between">
                            <span class="truncate font-medium" style="color:var(--sa-fg);">{{ $city->city }}</span>
                            <span class="text-sm font-semibold" style="color:var(--sa-fg);">{{ $city->count }} rest.</span>
                        </div>
                        <div class="mt-1.5 h-2 overflow-hidden rounded-full" style="background:var(--sa-muted);">
                            <div class="h-full rounded-full" style="background:var(--sa-primary);width:{{ $pct }}%;"></div>
                        </div>
                    </div>
                </li>
                @endforeach
            </ul>
        </div>
    </div>

    <!-- Row 3: Évolution des revenus + Répartition par ville -->
    <div class="mb-6 grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="rounded-2xl border p-5 shadow-sm" style="border-color:var(--sa-border);background:var(--sa-card);">
            <div class="mb-5 flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold" style="color:var(--sa-fg);">Évolution des revenus</h2>
                    <p class="text-sm" style="color:var(--sa-muted-fg);">{{ $period }} derniers jours</p>
                </div>
            </div>
            <div style="height:280px;">
                <canvas id="revenueLineChart"></canvas>
            </div>
        </div>

        <div class="rounded-2xl border p-5 shadow-sm" style="border-color:var(--sa-border);background:var(--sa-card);">
            <div class="mb-5 flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold" style="color:var(--sa-fg);">Répartition par ville</h2>
                    <p class="text-sm" style="color:var(--sa-muted-fg);">Restaurants actifs</p>
                </div>
            </div>
            <div style="height:280px;">
                <canvas id="cityChart"></canvas>
            </div>
        </div>
    </div>

    <!-- Row 4: Top jours d'activité + Revenus abonnements -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
        <div class="rounded-2xl border p-5 shadow-sm" style="border-color:var(--sa-border);background:var(--sa-card);">
            <div class="mb-5 flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold" style="color:var(--sa-fg);">Top jours d'activité</h2>
                    <p class="text-sm" style="color:var(--sa-muted-fg);">{{ $period }} jours — top 10</p>
                </div>
            </div>
            <div style="height:280px;">
                <canvas id="topDaysChart"></canvas>
            </div>
        </div>

        <div class="rounded-2xl border p-5 shadow-sm" style="border-color:var(--sa-border);background:var(--sa-card);">
            <div class="mb-5 flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold" style="color:var(--sa-fg);">Revenus abonnements</h2>
                    <p class="text-sm" style="color:var(--sa-muted-fg);">Évolution sur la période</p>
                </div>
                <span class="text-xl font-bold" style="color:var(--sa-success);">
                    {{ number_format($summary['subscription_revenue'], 0, ',', ' ') }} F
                </span>
            </div>
            <div style="height:240px;">
                <canvas id="subscriptionChart"></canvas>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://unpkg.com/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        // ── Shared defaults ──────────────────────────────────────────────
        const cs = getComputedStyle(document.documentElement);
        const gridColor = cs.getPropertyValue('--sa-border').trim() || 'rgba(209,213,219,0.5)';
        const tickColor = cs.getPropertyValue('--sa-muted-fg').trim() || '#6b7280';
        const primaryColor = cs.getPropertyValue('--sa-primary').trim() || 'rgb(59,130,246)';
        const successColor = cs.getPropertyValue('--sa-success').trim() || 'rgb(16,185,129)';

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
                    tooltip: { callbacks: { label: ctx => ctx.parsed.y.toLocaleString('fr-FR') + ' F' } }
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
                    tooltip: { callbacks: { label: ctx => ctx.parsed.y + ' commandes' } }
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
                    tooltip: { callbacks: { label: ctx => ctx.parsed.x + ' commandes' } }
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
                    tooltip: { callbacks: { label: ctx => ctx.label + ' — ' + ctx.parsed + ' restaurants' } }
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
                    tooltip: { callbacks: { label: ctx => ctx.parsed.y.toLocaleString('fr-FR') + ' F' } }
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
