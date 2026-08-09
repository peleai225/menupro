<div>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-neutral-900">Rapports Détaillés</h1>
            <p class="text-xs sm:text-sm text-neutral-500 mt-1 hidden sm:block">Analysez vos performances en détail et exportez vos données.</p>
        </div>
        <div class="flex items-center gap-2">
            <button wire:click="export('pdf')"
                    wire:loading.attr="disabled"
                    wire:target="export"
                    class="btn btn-secondary min-h-[52px] px-4 py-3.5 flex items-center gap-2 active:scale-95 transition-all touch-manipulation">
                <svg wire:loading.remove wire:target="export" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
                <svg wire:loading wire:target="export" class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                <span wire:loading.remove wire:target="export">Export PDF</span>
                <span wire:loading wire:target="export">Génération...</span>
            </button>
            <button wire:click="export('excel')"
                    class="btn btn-secondary min-h-[52px] px-4 py-3.5 flex items-center gap-2 hover:bg-neutral-700 active:scale-95 transition-all touch-manipulation">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Export Excel
            </button>
        </div>
    </div>

    <!-- Filters -->
    <div class="card p-4 sm:p-6 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-4 gap-3">
            <!-- Report Type -->
            <div>
                <label class="block text-sm font-medium text-neutral-700 mb-1.5">Type de rapport</label>
                <select wire:model.live="reportType"
                        class="w-full h-12 px-4 bg-white border border-neutral-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="daily">Bilan Journalier</option>
                    <option value="sales">Ventes</option>
                    <option value="dishes">Plats</option>
                    <option value="customers">Clients</option>
                    <option value="financial">Financier</option>
                    @if(auth()->user()->restaurant?->hasMultiSpaces())
                        <option value="waiters">Serveurs</option>
                    @endif
                </select>
            </div>

            <!-- Period -->
            <div>
                <label class="block text-sm font-medium text-neutral-700 mb-1.5">Période</label>
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
                <label class="block text-sm font-medium text-neutral-700 mb-1.5">Date de début</label>
                <input type="date" wire:model.live="startDate" 
                       class="w-full h-12 px-4 bg-white border border-neutral-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>

            <!-- End Date -->
            <div>
                <label class="block text-sm font-medium text-neutral-700 mb-1.5">Date de fin</label>
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
            <div class="card p-4 sm:p-6">
                <h2 class="text-base sm:text-lg font-bold text-neutral-900 mb-4">Commandes récentes</h2>
                <div class="overflow-x-auto -mx-4 sm:mx-0">
                    <div class="inline-block min-w-full align-middle">
                        <table class="min-w-full">
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
        </div>
    @endif

    <!-- Dishes Report -->
    @if($reportType === 'dishes' && !empty($data))
        <div class="space-y-6">
            <!-- Top Dishes -->
            <div class="card p-4 sm:p-6">
                <h2 class="text-base sm:text-lg font-bold text-neutral-900 mb-4">Plats les plus vendus</h2>
                <div class="overflow-x-auto -mx-4 sm:mx-0">
                    <div class="inline-block min-w-full align-middle">
                        <table class="min-w-full">
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
            </div>

            <!-- Dishes by Category -->
            <div class="card p-4 sm:p-6">
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
            <div class="card p-4 sm:p-6">
                <h2 class="text-base sm:text-lg font-bold text-neutral-900 mb-4">Meilleurs clients</h2>
                <div class="overflow-x-auto -mx-4 sm:mx-0">
                    <div class="inline-block min-w-full align-middle">
                        <table class="min-w-full">
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
        </div>
    @endif

    {{-- Rapport Journalier --}}
    @if($reportType === 'daily' && !empty($data))
        <div class="space-y-4 sm:space-y-6">

            {{-- KPI du jour --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                <div class="card p-3 sm:p-4">
                    <p class="text-xs text-neutral-500 mb-1">CA du jour</p>
                    <p class="text-xl sm:text-2xl font-bold text-neutral-900 tabular-nums">{{ number_format($data['total_revenue'] ?? 0, 0, ',', ' ') }} F</p>
                    <p class="text-xs text-neutral-400 mt-1">{{ $data['total_orders'] ?? 0 }} commandes · moy. {{ number_format($data['average_ticket'] ?? 0, 0, ',', ' ') }} F</p>
                </div>
                <div class="card p-3 sm:p-4 border-l-4 border-green-400">
                    <p class="text-xs text-neutral-500 mb-1">Espèces à encaisser</p>
                    <p class="text-xl sm:text-2xl font-bold text-green-700 tabular-nums">{{ number_format($data['cash_total'] ?? 0, 0, ',', ' ') }} F</p>
                </div>
                <div class="card p-3 sm:p-4 border-l-4 border-blue-400">
                    <p class="text-xs text-neutral-500 mb-1">Mobile Money</p>
                    <p class="text-xl sm:text-2xl font-bold text-blue-700 tabular-nums">{{ number_format($data['mobile_total'] ?? 0, 0, ',', ' ') }} F</p>
                </div>
                <div class="card p-3 sm:p-4 border-l-4 border-amber-400">
                    <p class="text-xs text-neutral-500 mb-1">Heure de pointe</p>
                    <p class="text-xl sm:text-2xl font-bold text-amber-700">{{ $data['peak_hour'] ?? '—' }}</p>
                </div>
            </div>

            {{-- Annulations --}}
            @if(($data['cancelled_count'] ?? 0) > 0)
            <div class="card p-3 sm:p-4 flex items-center gap-3 border-l-4 border-red-400">
                <div class="w-10 h-10 rounded-xl bg-red-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>
                <div>
                    <p class="text-xs text-neutral-500">Commandes annulées</p>
                    <p class="text-lg font-bold text-red-600">
                        {{ $data['cancelled_count'] }} annulée(s)
                        <span class="text-sm font-normal text-neutral-500">— {{ number_format($data['cancelled_lost'] ?? 0, 0, ',', ' ') }} F perdus</span>
                    </p>
                </div>
            </div>
            @endif

            {{-- Tableau horaire --}}
            <div class="card overflow-hidden">
                <div class="p-3 sm:p-4 border-b border-neutral-100">
                    <h2 class="text-base sm:text-lg font-bold text-neutral-900">Détail par heure de caisse</h2>
                    <p class="text-xs text-neutral-500 mt-0.5">Basé sur l'heure de paiement effectif</p>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full">
                        <thead class="bg-neutral-50 border-b border-neutral-200">
                            <tr>
                                <th class="px-4 py-3 text-left text-xs font-bold text-neutral-500 uppercase">Tranche</th>
                                <th class="px-4 py-3 text-right text-xs font-bold text-neutral-500 uppercase">Commandes</th>
                                <th class="px-4 py-3 text-right text-xs font-bold text-neutral-500 uppercase">CA Total</th>
                                <th class="px-4 py-3 text-right text-xs font-bold text-green-600 uppercase">Espèces</th>
                                <th class="px-4 py-3 text-right text-xs font-bold text-blue-600 uppercase">Mobile</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-100">
                            @forelse($data['by_hour'] ?? [] as $row)
                                <tr class="hover:bg-neutral-50 transition-colors">
                                    <td class="px-4 py-3 font-semibold text-sm text-neutral-900">{{ $row['hour_label'] }}</td>
                                    <td class="px-4 py-3 text-right text-sm text-neutral-700 tabular-nums">{{ $row['orders_count'] }}</td>
                                    <td class="px-4 py-3 text-right font-bold text-sm text-neutral-900 tabular-nums">{{ number_format($row['total_amount'], 0, ',', ' ') }} F</td>
                                    <td class="px-4 py-3 text-right text-sm text-green-700 tabular-nums">{{ number_format($row['cash_amount'], 0, ',', ' ') }} F</td>
                                    <td class="px-4 py-3 text-right text-sm text-blue-700 tabular-nums">{{ number_format($row['mobile_amount'], 0, ',', ' ') }} F</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-4 py-8 text-center text-neutral-400 text-sm">Aucune vente sur cette journée.</td>
                                </tr>
                            @endforelse
                        </tbody>
                        @if(!empty($data['by_hour']))
                        <tfoot class="bg-neutral-50 border-t-2 border-neutral-300">
                            <tr>
                                <td class="px-4 py-3 font-bold text-sm text-neutral-900 uppercase">Total</td>
                                <td class="px-4 py-3 text-right font-bold text-sm text-neutral-900 tabular-nums">{{ $data['total_orders'] ?? 0 }}</td>
                                <td class="px-4 py-3 text-right font-bold text-sm text-neutral-900 tabular-nums">{{ number_format($data['total_revenue'] ?? 0, 0, ',', ' ') }} F</td>
                                <td class="px-4 py-3 text-right font-bold text-sm text-green-700 tabular-nums">{{ number_format($data['cash_total'] ?? 0, 0, ',', ' ') }} F</td>
                                <td class="px-4 py-3 text-right font-bold text-sm text-blue-700 tabular-nums">{{ number_format($data['mobile_total'] ?? 0, 0, ',', ' ') }} F</td>
                            </tr>
                        </tfoot>
                        @endif
                    </table>
                </div>
            </div>

            {{-- Répartition paiements --}}
            @if(!empty($data['by_payment']))
            <div class="card p-3 sm:p-4">
                <h2 class="text-base font-bold text-neutral-900 mb-3">Répartition par moyen de paiement</h2>
                <div class="space-y-2">
                    @foreach($data['by_payment'] as $pay)
                        <div class="flex items-center justify-between p-3 rounded-xl {{ $pay['is_cash'] ? 'bg-green-50' : 'bg-blue-50' }}">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full {{ $pay['is_cash'] ? 'bg-green-500' : 'bg-blue-500' }}"></span>
                                <span class="font-medium text-sm text-neutral-800">{{ $pay['label'] }}</span>
                                <span class="text-xs text-neutral-500">{{ $pay['orders_count'] }} cmd</span>
                            </div>
                            <span class="font-bold text-sm tabular-nums {{ $pay['is_cash'] ? 'text-green-700' : 'text-blue-700' }}">
                                {{ number_format($pay['total_amount'], 0, ',', ' ') }} F
                            </span>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif

        </div>
    @endif

    {{-- Rapport Financier --}}
    @if($reportType === 'financial' && !empty($data))
        <div class="space-y-4 sm:space-y-6">

            {{-- KPI principaux --}}
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-3 sm:gap-4">
                <div class="card p-3 sm:p-4">
                    <p class="text-xs text-neutral-500 mb-1">Revenus totaux</p>
                    <p class="text-xl sm:text-2xl font-bold text-neutral-900 tabular-nums">{{ number_format($data['total_revenue'] ?? 0, 0, ',', ' ') }} F</p>
                    @if(isset($data['vs_previous']['change_pct']))
                        <p class="text-xs mt-1 {{ ($data['vs_previous']['change_pct'] ?? 0) >= 0 ? 'text-green-600' : 'text-red-600' }}">
                            {{ ($data['vs_previous']['change_pct'] ?? 0) >= 0 ? '+' : '' }}{{ $data['vs_previous']['change_pct'] ?? 0 }}% vs période préc.
                        </p>
                    @endif
                </div>
                <div class="card p-3 sm:p-4 border-l-4 border-green-400">
                    <p class="text-xs text-neutral-500 mb-1">Espèces à encaisser</p>
                    <p class="text-xl sm:text-2xl font-bold text-green-700 tabular-nums">{{ number_format($data['cash_total'] ?? 0, 0, ',', ' ') }} F</p>
                </div>
                <div class="card p-3 sm:p-4 border-l-4 border-blue-400">
                    <p class="text-xs text-neutral-500 mb-1">Mobile Money reçu</p>
                    <p class="text-xl sm:text-2xl font-bold text-blue-700 tabular-nums">{{ number_format($data['mobile_total'] ?? 0, 0, ',', ' ') }} F</p>
                </div>
                <div class="card p-3 sm:p-4 border-l-4 border-red-400">
                    <p class="text-xs text-neutral-500 mb-1">Annulées (pertes)</p>
                    <p class="text-xl sm:text-2xl font-bold text-red-600">{{ $data['cancelled_count'] ?? 0 }}</p>
                    <p class="text-xs text-neutral-400 tabular-nums">{{ number_format($data['cancelled_lost'] ?? 0, 0, ',', ' ') }} F perdus</p>
                </div>
            </div>

            {{-- Totaux secondaires --}}
            <div class="grid grid-cols-3 gap-3">
                <div class="card p-3 sm:p-4">
                    <p class="text-xs text-neutral-500 mb-1">Sous-total</p>
                    <p class="text-base font-bold text-neutral-900 tabular-nums">{{ number_format($data['total_subtotal'] ?? 0, 0, ',', ' ') }} F</p>
                </div>
                <div class="card p-3 sm:p-4">
                    <p class="text-xs text-neutral-500 mb-1">Frais livraison</p>
                    <p class="text-base font-bold text-neutral-900 tabular-nums">{{ number_format($data['total_delivery_fees'] ?? 0, 0, ',', ' ') }} F</p>
                </div>
                <div class="card p-3 sm:p-4">
                    <p class="text-xs text-neutral-500 mb-1">Réductions</p>
                    <p class="text-base font-bold text-red-600 tabular-nums">-{{ number_format($data['total_discounts'] ?? 0, 0, ',', ' ') }} F</p>
                </div>
            </div>

            {{-- Comparaison période précédente --}}
            @if(isset($data['vs_previous']))
            <div class="card p-3 sm:p-4 bg-neutral-50 border border-neutral-200">
                <h2 class="text-sm font-bold text-neutral-700 mb-3">Comparaison avec la période précédente</h2>
                <div class="flex flex-wrap items-center gap-4 sm:gap-6">
                    <div>
                        <p class="text-xs text-neutral-500">Période actuelle</p>
                        <p class="text-base font-bold text-neutral-900 tabular-nums">{{ number_format($data['total_revenue'] ?? 0, 0, ',', ' ') }} F</p>
                    </div>
                    <div class="text-neutral-300 text-xl hidden sm:block">vs</div>
                    <div>
                        <p class="text-xs text-neutral-500">Période précédente</p>
                        <p class="text-base font-bold text-neutral-500 tabular-nums">{{ number_format($data['vs_previous']['revenue'] ?? 0, 0, ',', ' ') }} F</p>
                    </div>
                    <div class="ml-auto px-3 py-1.5 rounded-xl text-sm font-bold {{ ($data['vs_previous']['change_pct'] ?? 0) >= 0 ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700' }}">
                        {{ ($data['vs_previous']['change_pct'] ?? 0) >= 0 ? '+' : '' }}{{ $data['vs_previous']['change_pct'] ?? 0 }}%
                    </div>
                </div>
            </div>
            @endif

            {{-- Détail par moyen de paiement --}}
            <div class="card p-3 sm:p-4">
                <h2 class="text-base font-bold text-neutral-900 mb-3">Détail par moyen de paiement</h2>
                <div class="space-y-2">
                    @forelse($data['by_payment_detailed'] ?? $data['revenue_by_payment'] ?? [] as $pay)
                        @php
                            $isCash = $pay['is_cash'] ?? false;
                            $label  = $pay['label'] ?? ucfirst($pay['payment_method'] ?? 'Inconnu');
                            $amount = $pay['total_amount'] ?? $pay['revenue'] ?? 0;
                            $count  = $pay['orders_count'] ?? $pay['count'] ?? 0;
                        @endphp
                        <div class="flex items-center justify-between p-3 rounded-xl {{ $isCash ? 'bg-green-50' : 'bg-blue-50' }}">
                            <div class="flex items-center gap-2">
                                <span class="w-2 h-2 rounded-full {{ $isCash ? 'bg-green-500' : 'bg-blue-500' }}"></span>
                                <div>
                                    <p class="font-medium text-sm text-neutral-800">{{ $label }}</p>
                                    <p class="text-xs text-neutral-500">{{ $count }} transaction(s)</p>
                                </div>
                            </div>
                            <span class="font-bold text-sm tabular-nums {{ $isCash ? 'text-green-700' : 'text-blue-700' }}">
                                {{ number_format($amount, 0, ',', ' ') }} F
                            </span>
                        </div>
                    @empty
                        <p class="text-sm text-neutral-400 text-center py-4">Aucun paiement sur cette période.</p>
                    @endforelse
                </div>
            </div>

        </div>
    @endif

    <!-- Waiters Report -->
    @if($reportType === 'waiters' && auth()->user()->restaurant?->hasMultiSpaces())
        <div class="space-y-6">
            <!-- Key Metrics -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="card p-6">
                    <p class="text-sm font-medium text-neutral-500 mb-2">Chiffre d'affaires total</p>
                    <p class="text-3xl font-bold text-neutral-900">{{ number_format($data['total_revenue'] ?? 0, 0, ',', ' ') }} F</p>
                </div>
                <div class="card p-6">
                    <p class="text-sm font-medium text-neutral-500 mb-2">Commandes totales</p>
                    <p class="text-3xl font-bold text-neutral-900">{{ $data['total_orders'] ?? 0 }}</p>
                </div>
            </div>

            <!-- Waiters Table -->
            <div class="card p-4 sm:p-6">
                <h2 class="text-base sm:text-lg font-bold text-neutral-900 mb-4">Performance par serveur</h2>
                <div class="overflow-x-auto -mx-4 sm:mx-0">
                    <div class="inline-block min-w-full align-middle">
                        <table class="min-w-full">
                        <thead>
                            <tr class="border-b border-neutral-200">
                                <th class="text-left py-3 px-4 text-sm font-semibold text-neutral-700">Serveur</th>
                                <th class="text-right py-3 px-4 text-sm font-semibold text-neutral-700">Commandes</th>
                                <th class="text-right py-3 px-4 text-sm font-semibold text-neutral-700">Chiffre d'affaires</th>
                                <th class="text-right py-3 px-4 text-sm font-semibold text-neutral-700">Ticket moyen</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-neutral-700">Espace principal</th>
                                <th class="text-left py-3 px-4 text-sm font-semibold text-neutral-700">Heures actives</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-100">
                            @forelse($data['waiters'] ?? [] as $waiter)
                                <tr class="hover:bg-neutral-50 transition-colors">
                                    <td class="py-3 px-4 font-medium text-neutral-900">{{ $waiter['waiter_name'] ?? '' }}</td>
                                    <td class="py-3 px-4 text-right text-neutral-700">{{ $waiter['orders_count'] ?? 0 }}</td>
                                    <td class="py-3 px-4 text-right font-bold text-neutral-900">{{ number_format($waiter['total_revenue'] ?? 0, 0, ',', ' ') }} F</td>
                                    <td class="py-3 px-4 text-right text-neutral-600">{{ number_format($waiter['avg_order'] ?? 0, 0, ',', ' ') }} F</td>
                                    <td class="py-3 px-4 text-sm text-neutral-600">{{ $waiter['primary_space'] ?? '—' }}</td>
                                    <td class="py-3 px-4 text-sm text-neutral-500">{{ $waiter['heures_actives'] ?? '' }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-neutral-500">Aucun serveur avec des commandes sur cette période</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                    </div>
                </div>
            </div>
        </div>
    @endif

    @push('scripts')
        @if($reportType === 'sales' && !empty($data['sales_by_day']))
            <script src="https://unpkg.com/chart.js@4.4.0/dist/chart.umd.min.js"></script>
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

