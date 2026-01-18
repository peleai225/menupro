<x-layouts.admin-super title="Revenus">
    <!-- Header -->
    <div class="mb-8">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-white">Revenus détaillés</h1>
                <p class="text-neutral-400 mt-1">Analyse détaillée des revenus de la plateforme.</p>
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
            '7' => '7 jours',
            '30' => '30 jours',
            '90' => '90 jours',
            '365' => '1 an',
        ] as $value => $label)
            <button type="submit" 
                    name="period" 
                    value="{{ $value }}"
                    class="px-4 py-2 rounded-lg text-sm font-medium transition-colors {{ $period == $value ? 'bg-primary-500 text-white' : 'bg-neutral-800 text-neutral-300 hover:bg-neutral-700' }}">
                {{ $label }}
            </button>
        @endforeach
    </form>

    <!-- Summary Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        <div class="bg-neutral-800/50 border border-neutral-700 rounded-xl p-6">
            <p class="text-sm text-neutral-400">Revenus totaux</p>
            <p class="text-2xl font-bold text-white mt-1">{{ number_format($totalRevenue / 100, 0, ',', ' ') }} FCFA</p>
        </div>
        <div class="bg-neutral-800/50 border border-neutral-700 rounded-xl p-6">
            <p class="text-sm text-neutral-400">Commandes totales</p>
            <p class="text-2xl font-bold text-white mt-1">{{ number_format($totalOrders) }}</p>
        </div>
        <div class="bg-neutral-800/50 border border-neutral-700 rounded-xl p-6">
            <p class="text-sm text-neutral-400">Panier moyen</p>
            <p class="text-2xl font-bold text-white mt-1">{{ number_format($averageOrder / 100, 0, ',', ' ') }} FCFA</p>
        </div>
    </div>

    <!-- Revenue Chart -->
    <div class="bg-neutral-800/50 border border-neutral-700 rounded-xl p-6 mb-6">
        <h2 class="text-lg font-semibold text-white mb-4">Évolution des revenus</h2>
        <div class="relative h-64">
            <canvas id="revenueChart"></canvas>
        </div>
    </div>

    <!-- Revenue by Restaurant -->
    <div class="bg-neutral-800/50 border border-neutral-700 rounded-xl p-6 mb-6">
        <h2 class="text-lg font-semibold text-white mb-4">Revenus par restaurant</h2>
        <div class="table-responsive">
            <table class="w-full min-w-[500px]">
                <thead>
                    <tr class="border-b border-neutral-700">
                        <th class="text-left py-3 px-4 text-neutral-400 font-medium">Restaurant</th>
                        <th class="text-right py-3 px-4 text-neutral-400 font-medium">Revenus</th>
                        <th class="text-right py-3 px-4 text-neutral-400 font-medium">Commandes</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($revenueByRestaurant as $restaurant)
                        <tr class="border-b border-neutral-700/50 hover:bg-neutral-700/30">
                            <td class="py-3 px-4 text-white">{{ $restaurant->name }}</td>
                            <td class="py-3 px-4 text-right text-white font-medium">{{ number_format($restaurant->revenue / 100, 0, ',', ' ') }} FCFA</td>
                            <td class="py-3 px-4 text-right text-neutral-300">{{ $restaurant->orders }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="3" class="py-8 text-center text-neutral-400">Aucun revenu pour cette période</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Revenue by Payment Method -->
    <div class="bg-neutral-800/50 border border-neutral-700 rounded-xl p-6">
        <h2 class="text-lg font-semibold text-white mb-4">Revenus par méthode de paiement</h2>
        <div class="space-y-3">
            @forelse($revenueByPayment as $payment)
                <div class="flex items-center justify-between p-4 bg-neutral-700/50 rounded-lg">
                    <span class="text-white font-medium">{{ ucfirst($payment->payment_method ?? 'Non spécifié') }}</span>
                    <div class="text-right">
                        <p class="text-white font-bold">{{ number_format($payment->revenue / 100, 0, ',', ' ') }} FCFA</p>
                        <p class="text-sm text-neutral-400">{{ $payment->orders }} commande(s)</p>
                    </div>
                </div>
            @empty
                <p class="text-neutral-400 text-center py-8">Aucun revenu pour cette période</p>
            @endforelse
        </div>
    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const revenueCtx = document.getElementById('revenueChart');
        new Chart(revenueCtx, {
            type: 'line',
            data: {
                labels: {!! json_encode($dailyRevenue->pluck('date')) !!},
                datasets: [{
                    label: 'Revenus (FCFA)',
                    data: {!! json_encode($dailyRevenue->pluck('revenue')->map(fn($v) => $v / 100)) !!},
                    borderColor: 'rgb(59, 130, 246)',
                    backgroundColor: 'rgba(59, 130, 246, 0.1)',
                    tension: 0.4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        display: true,
                        labels: {
                            color: '#e5e7eb'
                        }
                    }
                },
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            color: '#9ca3af',
                            callback: function(value) {
                                return value.toLocaleString() + ' FCFA';
                            }
                        },
                        grid: {
                            color: 'rgba(75, 85, 99, 0.3)'
                        }
                    },
                    x: {
                        ticks: {
                            color: '#9ca3af'
                        },
                        grid: {
                            color: 'rgba(75, 85, 99, 0.3)'
                        }
                    }
                }
            }
        });
    </script>
    @endpush
</x-layouts.admin-super>

