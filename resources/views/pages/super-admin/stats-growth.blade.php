<x-layouts.admin-super title="Croissance">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold" style="color:var(--sa-fg);">Analyse de croissance</h1>
                <p class="mt-1" style="color:var(--sa-muted-fg);">Comparaison des performances entre périodes.</p>
            </div>
            <a href="{{ route('super-admin.stats') }}" class="btn btn-neutral">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour
            </a>
        </div>
    </div>

    <!-- Period Filter -->
    <form method="GET" class="flex gap-2 mb-8">
        @foreach([
            '30' => '30 jours',
            '90' => '90 jours',
            '180' => '6 mois',
            '365' => '1 an',
        ] as $value => $label)
            <button type="submit"
                    name="period"
                    value="{{ $value }}"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ $period == $value ? 'bg-primary-500 text-neutral-900' : '' }}"
                    @if($period != $value) style="background:var(--sa-muted);color:var(--sa-muted-fg);" @endif>
                {{ $label }}
            </button>
        @endforeach
    </form>

    <!-- Comparison Stats -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="border shadow-sm rounded-xl p-6" style="background:var(--sa-card);border-color:var(--sa-border);">
            <p class="text-sm" style="color:var(--sa-muted-fg);">Restaurants</p>
            <div class="flex items-end justify-between mt-2">
                <p class="text-2xl font-bold" style="color:var(--sa-fg);">{{ number_format($currentStats['restaurants']) }}</p>
                @if(isset($growth['restaurants']))
                    <span class="text-sm {{ $growth['restaurants'] >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                        {{ $growth['restaurants'] >= 0 ? '+' : '' }}{{ number_format($growth['restaurants'], 1) }}%
                    </span>
                @endif
            </div>
            <p class="text-xs mt-1" style="color:var(--sa-muted-fg);">vs période précédente: {{ $previousStats['restaurants'] }}</p>
        </div>
        <div class="border shadow-sm rounded-xl p-6" style="background:var(--sa-card);border-color:var(--sa-border);">
            <p class="text-sm" style="color:var(--sa-muted-fg);">Commandes</p>
            <div class="flex items-end justify-between mt-2">
                <p class="text-2xl font-bold" style="color:var(--sa-fg);">{{ number_format($currentStats['orders']) }}</p>
                @if(isset($growth['orders']))
                    <span class="text-sm {{ $growth['orders'] >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                        {{ $growth['orders'] >= 0 ? '+' : '' }}{{ number_format($growth['orders'], 1) }}%
                    </span>
                @endif
            </div>
            <p class="text-xs mt-1" style="color:var(--sa-muted-fg);">vs période précédente: {{ $previousStats['orders'] }}</p>
        </div>
        <div class="border shadow-sm rounded-xl p-6" style="background:var(--sa-card);border-color:var(--sa-border);">
            <p class="text-sm" style="color:var(--sa-muted-fg);">Revenus</p>
            <div class="flex items-end justify-between mt-2">
                <p class="text-2xl font-bold" style="color:var(--sa-fg);">{{ number_format($currentStats['revenue'] / 100, 0, ',', ' ') }} F</p>
                @if(isset($growth['revenue']))
                    <span class="text-sm {{ $growth['revenue'] >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                        {{ $growth['revenue'] >= 0 ? '+' : '' }}{{ number_format($growth['revenue'], 1) }}%
                    </span>
                @endif
            </div>
            <p class="text-xs mt-1" style="color:var(--sa-muted-fg);">vs période précédente: {{ number_format($previousStats['revenue'] / 100, 0, ',', ' ') }} F</p>
        </div>
        <div class="border shadow-sm rounded-xl p-6" style="background:var(--sa-card);border-color:var(--sa-border);">
            <p class="text-sm" style="color:var(--sa-muted-fg);">Abonnements</p>
            <div class="flex items-end justify-between mt-2">
                <p class="text-2xl font-bold" style="color:var(--sa-fg);">{{ number_format($currentStats['subscriptions']) }}</p>
                @if(isset($growth['subscriptions']))
                    <span class="text-sm {{ $growth['subscriptions'] >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                        {{ $growth['subscriptions'] >= 0 ? '+' : '' }}{{ number_format($growth['subscriptions'], 1) }}%
                    </span>
                @endif
            </div>
            <p class="text-xs mt-1" style="color:var(--sa-muted-fg);">vs période précédente: {{ $previousStats['subscriptions'] }}</p>
        </div>
    </div>

    <!-- Growth Chart -->
    <div class="border shadow-sm rounded-xl p-6 mb-6" style="background:var(--sa-card);border-color:var(--sa-border);">
        <h2 class="text-lg font-semibold mb-4" style="color:var(--sa-fg);">Évolution hebdomadaire</h2>
        <div class="relative h-64">
            <canvas id="growthChart"></canvas>
        </div>
    </div>

    <!-- Weekly Growth Table -->
    <div class="border shadow-sm rounded-xl p-6" style="background:var(--sa-card);border-color:var(--sa-border);">
        <h2 class="text-lg font-semibold mb-4" style="color:var(--sa-fg);">Détail par semaine</h2>
        <div class="table-responsive">
            <table class="w-full min-w-[600px]">
                <thead>
                    <tr style="border-bottom:1px solid var(--sa-border);">
                        <th class="text-left py-3 px-4 font-medium" style="color:var(--sa-muted-fg);">Semaine</th>
                        <th class="text-right py-3 px-4 font-medium" style="color:var(--sa-muted-fg);">Restaurants</th>
                        <th class="text-right py-3 px-4 font-medium" style="color:var(--sa-muted-fg);">Commandes</th>
                        <th class="text-right py-3 px-4 font-medium" style="color:var(--sa-muted-fg);">Revenus</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($weeklyGrowth as $week)
                        <tr style="border-bottom:1px solid var(--sa-border);">
                            <td class="py-3 px-4" style="color:var(--sa-fg);">{{ $week['week'] }}</td>
                            <td class="py-3 px-4 text-right" style="color:var(--sa-fg);">{{ $week['restaurants'] }}</td>
                            <td class="py-3 px-4 text-right" style="color:var(--sa-fg);">{{ $week['orders'] }}</td>
                            <td class="py-3 px-4 text-right font-medium" style="color:var(--sa-fg);">{{ number_format($week['revenue'] / 100, 0, ',', ' ') }} FCFA</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-8 text-center" style="color:var(--sa-muted-fg);">Aucune donnée pour cette période</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @push('scripts')
    @vite('resources/js/super-admin.js')
    <script>
        window.addEventListener('chartjs:ready', function () {
        const growthCtx = document.getElementById('growthChart');
        new Chart(growthCtx, {
            type: 'bar',
            data: {
                labels: {!! json_encode(array_column($weeklyGrowth, 'week')) !!},
                datasets: [
                    {
                        label: 'Restaurants',
                        data: {!! json_encode(array_column($weeklyGrowth, 'restaurants')) !!},
                        backgroundColor: 'rgba(59, 130, 246, 0.5)',
                        borderColor: 'rgb(59, 130, 246)',
                    },
                    {
                        label: 'Commandes',
                        data: {!! json_encode(array_column($weeklyGrowth, 'orders')) !!},
                        backgroundColor: 'rgba(16, 185, 129, 0.5)',
                        borderColor: 'rgb(16, 185, 129)',
                    },
                    {
                        label: 'Revenus (K FCFA)',
                        data: {!! json_encode(array_map(fn($v) => $v / 100000, array_column($weeklyGrowth, 'revenue'))) !!},
                        backgroundColor: 'rgba(245, 158, 11, 0.5)',
                        borderColor: 'rgb(245, 158, 11)',
                        yAxisID: 'y1',
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        labels: {
                            color: '#374151'
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: '#6b7280'
                        },
                        grid: {
                            color: 'rgba(209, 213, 219, 0.5)'
                        }
                    },
                    y1: {
                        type: 'linear',
                        display: true,
                        position: 'right',
                        beginAtZero: true,
                        ticks: {
                            color: '#6b7280'
                        },
                        grid: {
                            drawOnChartArea: false,
                        },
                    },
                    x: {
                        ticks: {
                            color: '#6b7280'
                        },
                        grid: {
                            color: 'rgba(209, 213, 219, 0.5)'
                        }
                    }
                }
            }
        });
        }); // chartjs:ready
    </script>
    @endpush
</x-layouts.admin-super>
