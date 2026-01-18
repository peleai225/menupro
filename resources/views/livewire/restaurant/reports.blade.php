<div>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-neutral-900">Rapports Détaillés</h1>
            <p class="text-neutral-500 mt-1">Analysez vos performances en détail et exportez vos données.</p>
        </div>
        <div class="flex items-center gap-3">
            {{-- PDF export temporairement désactivé --}}
            {{-- <button wire:click="export('pdf')" 
                    class="btn btn-secondary px-4 py-2 flex items-center gap-2 hover:bg-neutral-700 active:scale-95 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
                Export PDF
            </button> --}}
            <button wire:click="export('excel')" 
                    class="btn btn-secondary px-4 py-2 flex items-center gap-2 hover:bg-neutral-700 active:scale-95 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Export Excel
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="card p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <!-- Report Type -->
            <div>
                <label class="block text-sm font-medium text-neutral-700 mb-2">Type de rapport</label>
                <select wire:model.live="reportType" 
                        class="w-full h-12 px-4 bg-white border border-neutral-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="sales">Ventes</option>
                    <option value="dishes">Plats</option>
                    <option value="customers">Clients</option>
                    <option value="financial">Financier</option>
                </select>
            </div>

            <!-- Period -->
            <div>
                <label class="block text-sm font-medium text-neutral-700 mb-2">Période</label>
                <select wire:model.live="period" 
                        class="w-full h-12 px-4 bg-white border border-neutral-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="7">7 derniers jours</option>
                    <option value="30">30 derniers jours</option>
                    <option value="90">3 derniers mois</option>
                    <option value="365">1 an</option>
                    <option value="custom">Personnalisé</option>
                </select>
            </div>

            <!-- Start Date -->
            <div>
                <label class="block text-sm font-medium text-neutral-700 mb-2">Date de début</label>
                <input type="date" wire:model.live="startDate" 
                       class="w-full h-12 px-4 bg-white border border-neutral-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>

            <!-- End Date -->
            <div>
                <label class="block text-sm font-medium text-neutral-700 mb-2">Date de fin</label>
                <input type="date" wire:model.live="endDate" 
                       class="w-full h-12 px-4 bg-white border border-neutral-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>
        </div>
    </div>

    @php
        $data = $reportData;
    @endphp

    <!-- Sales Report -->
    @if($reportType === 'sales' && !empty($data))
        <div class="space-y-6">
            <!-- Key Metrics -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="card p-6">
                    <p class="text-sm font-medium text-neutral-500 mb-2">Chiffre d'affaires total</p>
                    <p class="text-3xl font-bold text-neutral-900">{{ number_format($data['total_revenue'] ?? 0, 0, ',', ' ') }} F</p>
                </div>
                <div class="card p-6">
                    <p class="text-sm font-medium text-neutral-500 mb-2">Nombre de commandes</p>
                    <p class="text-3xl font-bold text-neutral-900">{{ $data['total_orders'] ?? 0 }}</p>
                </div>
                <div class="card p-6">
                    <p class="text-sm font-medium text-neutral-500 mb-2">Panier moyen</p>
                    <p class="text-3xl font-bold text-neutral-900">{{ number_format($data['average_order'] ?? 0, 0, ',', ' ') }} F</p>
                </div>
            </div>

            <!-- Sales by Day Chart -->
            <div class="card p-6">
                <h2 class="text-lg font-bold text-neutral-900 mb-4">Évolution des ventes</h2>
                <div class="relative h-64">
                    <canvas id="salesChart"></canvas>
                </div>
            </div>

            <!-- Sales by Type -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="card p-6">
                    <h2 class="text-lg font-bold text-neutral-900 mb-4">Ventes par type</h2>
                    <div class="space-y-3">
                        @foreach($data['sales_by_type'] ?? [] as $type)
                            @php
                                $typeLabels = [
                                    'dine_in' => 'Sur place',
                                    'takeaway' => 'À emporter',
                                    'delivery' => 'Livraison',
                                ];
                                $typeValue = $type['type'] ?? '';
                            @endphp
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-neutral-700">{{ $typeLabels[$typeValue] ?? $typeValue }}</span>
                                <div class="flex items-center gap-4">
                                    <span class="text-sm text-neutral-500">{{ $type['count'] ?? 0 }} commandes</span>
                                    <span class="font-bold text-neutral-900">{{ number_format($type['revenue'] ?? 0, 0, ',', ' ') }} F</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="card p-6">
                    <h2 class="text-lg font-bold text-neutral-900 mb-4">Commandes par statut</h2>
                    <div class="space-y-3">
                        @foreach($data['sales_by_status'] ?? [] as $status)
                            <div class="flex items-center justify-between">
                                <span class="text-sm font-medium text-neutral-700">{{ $status['status'] ?? '' }}</span>
                                <span class="font-bold text-neutral-900">{{ $status['count'] ?? 0 }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <!-- Recent Orders -->
            <div class="card p-6">
                <h2 class="text-lg font-bold text-neutral-900 mb-4">Commandes récentes</h2>
                <div class="table-responsive">
                    <table class="w-full min-w-[400px]">
                        <thead>
                            <tr class="border-b border-neutral-200">
                                <th class="text-left py-3 px-4 text-sm font-semibold text-neutral-700">Date</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-neutral-700">Client</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-neutral-700">Type</th>
                                <th class="text-right py-3 px-4 text-sm font-semibold text-neutral-700">Montant</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-100">
                            @forelse($data['orders'] ?? [] as $order)
                                <tr class="hover:bg-neutral-50 transition-colors">
                                    <td class="py-3 px-4 text-sm text-neutral-700">{{ $order['created_at'] ? \Carbon\Carbon::parse($order['created_at'])->format('d/m/Y H:i') : '' }}</td>
                                    <td class="py-3 px-4 text-sm text-neutral-700">{{ $order['reference'] ?? 'N/A' }}</td>
                                    <td class="py-3 px-4 text-sm text-neutral-700">
                                        @php
                                            $typeLabels = [
                                                'dine_in' => 'Sur place',
                                                'takeaway' => 'À emporter',
                                                'delivery' => 'Livraison',
                                            ];
                                            $typeValue = $order['status'] ?? '';
                                        @endphp
                                        {{ $typeLabels[$typeValue] ?? $typeValue }}
                                    </td>
                                    <td class="py-3 px-4 text-right font-bold text-neutral-900">{{ number_format($order['total'] ?? 0, 0, ',', ' ') }} F</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-8 text-center text-neutral-500">Aucune commande</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- Dishes Report -->
    @if($reportType === 'dishes' && !empty($data))
        <div class="space-y-6">
            <!-- Top Dishes -->
            <div class="card p-6">
                <h2 class="text-lg font-bold text-neutral-900 mb-4">Plats les plus vendus</h2>
                <div class="table-responsive">
                    <table class="w-full min-w-[400px]">
                        <thead>
                            <tr class="border-b border-neutral-200">
                                <th class="text-left py-3 px-4 text-sm font-semibold text-neutral-700">Plat</th>
                                <th class="text-right py-3 px-4 text-sm font-semibold text-neutral-700">Quantité vendue</th>
                                <th class="text-right py-3 px-4 text-sm font-semibold text-neutral-700">Revenus</th>
                                <th class="text-right py-3 px-4 text-sm font-semibold text-neutral-700">Prix moyen</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-100">
                            @forelse($data['top_dishes'] ?? [] as $dish)
                                <tr class="hover:bg-neutral-50 transition-colors">
                                    <td class="py-3 px-4">
                                        <div class="font-medium text-neutral-900">{{ $dish['name'] ?? '' }}</div>
                                        <div class="text-xs text-neutral-500">{{ number_format($dish['price'] ?? 0, 0, ',', ' ') }} F</div>
                                    </td>
                                    <td class="py-3 px-4 text-right font-medium text-neutral-900">{{ $dish['total_sold'] ?? 0 }}</td>
                                    <td class="py-3 px-4 text-right font-bold text-neutral-900">{{ number_format($dish['total_revenue'] ?? 0, 0, ',', ' ') }} F</td>
                                    <td class="py-3 px-4 text-right text-sm text-neutral-600">{{ number_format($dish['avg_price'] ?? 0, 0, ',', ' ') }} F</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="py-8 text-center text-neutral-500">Aucune donnée</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Dishes by Category -->
            <div class="card p-6">
                <h2 class="text-lg font-bold text-neutral-900 mb-4">Ventes par catégorie</h2>
                <div class="space-y-4">
                    @forelse($data['dishes_by_category'] ?? [] as $category)
                        <div class="flex items-center justify-between p-4 bg-neutral-50 rounded-xl">
                            <div>
                                <p class="font-medium text-neutral-900">{{ $category['name'] ?? '' }}</p>
                                <p class="text-sm text-neutral-500">{{ $category['total_sold'] ?? 0 }} plats vendus</p>
                            </div>
                            <p class="font-bold text-neutral-900">{{ number_format($category['total_revenue'] ?? 0, 0, ',', ' ') }} F</p>
                        </div>
                    @empty
                        <p class="text-center text-neutral-500 py-8">Aucune donnée</p>
                    @endforelse
                </div>
            </div>
        </div>
    @endif

    <!-- Customers Report -->
    @if($reportType === 'customers' && !empty($data))
        <div class="space-y-6">
            <!-- Key Metrics -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="card p-6">
                    <p class="text-sm font-medium text-neutral-500 mb-2">Clients uniques</p>
                    <p class="text-3xl font-bold text-neutral-900">{{ $data['total_customers'] ?? 0 }}</p>
                </div>
                <div class="card p-6">
                    <p class="text-sm font-medium text-neutral-500 mb-2">Top clients</p>
                    <p class="text-3xl font-bold text-neutral-900">{{ count($data['top_customers'] ?? []) }}</p>
                </div>
            </div>

            <!-- Top Customers -->
            <div class="card p-6">
                <h2 class="text-lg font-bold text-neutral-900 mb-4">Meilleurs clients</h2>
                <div class="table-responsive">
                    <table class="w-full min-w-[500px]">
                        <thead>
                            <tr class="border-b border-neutral-200">
                                <th class="text-left py-3 px-4 text-sm font-semibold text-neutral-700">Client</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-neutral-700">Email</th>
                                <th class="text-right py-3 px-4 text-sm font-semibold text-neutral-700">Commandes</th>
                                <th class="text-right py-3 px-4 text-sm font-semibold text-neutral-700">Total dépensé</th>
                                <th class="text-right py-3 px-4 text-sm font-semibold text-neutral-700">Panier moyen</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-100">
                            @forelse($data['top_customers'] ?? [] as $customer)
                                <tr class="hover:bg-neutral-50 transition-colors">
                                    <td class="py-3 px-4 font-medium text-neutral-900">{{ $customer['customer_name'] ?? '' }}</td>
                                    <td class="py-3 px-4 text-sm text-neutral-600">{{ $customer['customer_email'] ?? '' }}</td>
                                    <td class="py-3 px-4 text-right font-medium text-neutral-900">{{ $customer['orders_count'] ?? 0 }}</td>
                                    <td class="py-3 px-4 text-right font-bold text-neutral-900">{{ number_format($customer['total_spent'] ?? 0, 0, ',', ' ') }} F</td>
                                    <td class="py-3 px-4 text-right text-sm text-neutral-600">{{ number_format($customer['avg_order_value'] ?? 0, 0, ',', ' ') }} F</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-neutral-500">Aucun client</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    @endif

    <!-- Financial Report -->
    @if($reportType === 'financial' && !empty($data))
        <div class="space-y-6">
            <!-- Key Metrics -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6">
                <div class="card p-6">
                    <p class="text-sm font-medium text-neutral-500 mb-2">Revenus totaux</p>
                    <p class="text-2xl font-bold text-neutral-900">{{ number_format($data['total_revenue'] ?? 0, 0, ',', ' ') }} F</p>
                </div>
                <div class="card p-6">
                    <p class="text-sm font-medium text-neutral-500 mb-2">Sous-total</p>
                    <p class="text-2xl font-bold text-neutral-900">{{ number_format($data['total_subtotal'] ?? 0, 0, ',', ' ') }} F</p>
                </div>
                <div class="card p-6">
                    <p class="text-sm font-medium text-neutral-500 mb-2">Frais de livraison</p>
                    <p class="text-2xl font-bold text-neutral-900">{{ number_format($data['total_delivery_fees'] ?? 0, 0, ',', ' ') }} F</p>
                </div>
                <div class="card p-6">
                    <p class="text-sm font-medium text-neutral-500 mb-2">Réductions</p>
                    <p class="text-2xl font-bold text-red-600">-{{ number_format($data['total_discounts'] ?? 0, 0, ',', ' ') }} F</p>
                </div>
            </div>

            <!-- Revenue by Payment Method -->
            <div class="card p-6">
                <h2 class="text-lg font-bold text-neutral-900 mb-4">Revenus par méthode de paiement</h2>
                <div class="space-y-3">
                    @foreach($data['revenue_by_payment'] ?? [] as $payment)
                        <div class="flex items-center justify-between p-4 bg-neutral-50 rounded-xl">
                            <div>
                                <p class="font-medium text-neutral-900">{{ ucfirst($payment['payment_method'] ?? '') }}</p>
                                <p class="text-sm text-neutral-500">{{ $payment['count'] ?? 0 }} transactions</p>
                            </div>
                            <p class="font-bold text-neutral-900">{{ number_format($payment['revenue'] ?? 0, 0, ',', ' ') }} F</p>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif

    @push('scripts')
        @if($reportType === 'sales' && !empty($data['sales_by_day']))
            <script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
            <script>
                document.addEventListener('DOMContentLoaded', function() {
                    const ctx = document.getElementById('salesChart');
                    if (ctx) {
                        const salesData = @json($data['sales_by_day'] ?? []);
                        new Chart(ctx, {
                            type: 'line',
                            data: {
                                labels: salesData.map(item => new Date(item.date).toLocaleDateString('fr-FR', { day: 'numeric', month: 'short' })),
                                datasets: [{
                                    label: 'Revenus (FCFA)',
                                    data: salesData.map(item => item.revenue),
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
                });
            </script>
        @endif
    @endpush
</div>

