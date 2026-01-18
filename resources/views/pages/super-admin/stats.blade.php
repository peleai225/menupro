<x-layouts.admin-super title="Statistiques">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-white">Statistiques</h1>
        <p class="text-neutral-400 mt-1">Vue d'ensemble des performances de la plateforme.</p>
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

    <!-- KPIs -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6 mb-8">
        <div class="bg-neutral-800/50 border border-neutral-700 rounded-2xl p-6">
            <p class="text-sm text-neutral-400 mb-1">Revenus totaux</p>
            <p class="text-3xl font-bold text-white">
                @if($summary['total_revenue'] >= 1000000)
                    {{ number_format($summary['total_revenue'] / 1000000, 1, ',', ' ') }}M
                @else
                    {{ number_format($summary['total_revenue'] / 1000, 0, ',', ' ') }}K
                @endif
                <span class="text-lg font-normal text-neutral-400">F</span>
            </p>
            <p class="text-sm text-neutral-500 mt-2">Sur les {{ $period }} derniers jours</p>
        </div>
        <div class="bg-neutral-800/50 border border-neutral-700 rounded-2xl p-6">
            <p class="text-sm text-neutral-400 mb-1">Nouveaux restaurants</p>
            <p class="text-3xl font-bold text-white">{{ number_format($summary['new_restaurants']) }}</p>
            <p class="text-sm text-neutral-500 mt-2">Sur les {{ $period }} derniers jours</p>
        </div>
        <div class="bg-neutral-800/50 border border-neutral-700 rounded-2xl p-6">
            <p class="text-sm text-neutral-400 mb-1">Commandes traitées</p>
            <p class="text-3xl font-bold text-white">
                @if($summary['total_orders'] >= 1000)
                    {{ number_format($summary['total_orders'] / 1000, 1, ',', ' ') }}K
                @else
                    {{ number_format($summary['total_orders']) }}
                @endif
            </p>
            <p class="text-sm text-neutral-500 mt-2">Sur les {{ $period }} derniers jours</p>
        </div>
        <div class="bg-neutral-800/50 border border-neutral-700 rounded-2xl p-6">
            <p class="text-sm text-neutral-400 mb-1">Panier moyen</p>
            <p class="text-3xl font-bold text-white">{{ number_format($summary['average_order'], 0, ',', ' ') }} <span class="text-lg font-normal text-neutral-400">F</span></p>
            <p class="text-sm text-neutral-500 mt-2">Par commande</p>
        </div>
    </div>

    <!-- Charts Section -->
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 mb-8">
        <!-- Revenue Chart -->
        <div class="bg-neutral-800/50 border border-neutral-700 rounded-2xl p-6">
            <h3 class="text-lg font-semibold text-white mb-4">Évolution des revenus</h3>
            @if($revenueData->isNotEmpty())
                <div class="space-y-3">
                    @foreach($revenueData->take(10) as $data)
                        @php
                            $maxRevenue = $revenueData->max('revenue') ?: 1;
                            $percent = ($data->revenue / $maxRevenue) * 100;
                        @endphp
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-neutral-400">{{ \Carbon\Carbon::parse($data->date)->format('d M') }}</span>
                                <span class="text-white font-medium">{{ number_format($data->revenue, 0, ',', ' ') }} F</span>
                            </div>
                            <div class="h-2 bg-neutral-700 rounded-full overflow-hidden">
                                <div class="h-full bg-primary-500 rounded-full" style="width: {{ $percent }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="h-64 bg-neutral-700/30 rounded-xl flex items-center justify-center">
                    <p class="text-neutral-500">Aucune donnée disponible</p>
                </div>
            @endif
        </div>

        <!-- New Restaurants Chart -->
        <div class="bg-neutral-800/50 border border-neutral-700 rounded-2xl p-6">
            <h3 class="text-lg font-semibold text-white mb-4">Inscriptions par jour</h3>
            @if($newRestaurants->isNotEmpty())
                <div class="space-y-3">
                    @foreach($newRestaurants->take(10) as $data)
                        @php
                            $maxCount = $newRestaurants->max('count') ?: 1;
                            $percent = ($data->count / $maxCount) * 100;
                        @endphp
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-neutral-400">{{ \Carbon\Carbon::parse($data->date)->format('d M') }}</span>
                                <span class="text-white font-medium">{{ $data->count }} inscriptions</span>
                            </div>
                            <div class="h-2 bg-neutral-700 rounded-full overflow-hidden">
                                <div class="h-full bg-secondary-500 rounded-full" style="width: {{ $percent }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="h-64 bg-neutral-700/30 rounded-xl flex items-center justify-center">
                    <p class="text-neutral-500">Aucune donnée disponible</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Additional Stats -->
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-8">
        <!-- Plan Distribution -->
        <div class="bg-neutral-800/50 border border-neutral-700 rounded-2xl p-6">
            <h3 class="text-lg font-semibold text-white mb-4">Répartition par plan</h3>
            @if($planDistribution->isNotEmpty())
                <div class="space-y-4">
                    @php
                        $colors = ['bg-primary-500', 'bg-secondary-500', 'bg-accent-500', 'bg-blue-500'];
                        $total = $planDistribution->sum('count');
                    @endphp
                    @foreach($planDistribution as $index => $plan)
                        @php $percent = $total > 0 ? ($plan->count / $total) * 100 : 0; @endphp
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-neutral-300">{{ $plan->name }}</span>
                                <span class="text-neutral-400">{{ $plan->count }} ({{ round($percent) }}%)</span>
                            </div>
                            <div class="h-2 bg-neutral-700 rounded-full overflow-hidden">
                                <div class="h-full {{ $colors[$index % count($colors)] }} rounded-full" style="width: {{ $percent }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-neutral-500 text-center py-8">Aucune donnée</p>
            @endif
        </div>

        <!-- Orders by Type -->
        <div class="bg-neutral-800/50 border border-neutral-700 rounded-2xl p-6">
            <h3 class="text-lg font-semibold text-white mb-4">Commandes par type</h3>
            @if($ordersByType->isNotEmpty())
                <div class="space-y-4">
                    @php
                        $typeLabels = ['dine_in' => 'Sur place', 'takeaway' => 'À emporter', 'delivery' => 'Livraison'];
                        $typeColors = ['dine_in' => 'bg-blue-500', 'takeaway' => 'bg-accent-500', 'delivery' => 'bg-primary-500'];
                        $total = $ordersByType->sum('count');
                    @endphp
                    @foreach($ordersByType as $type)
                        @php $percent = $total > 0 ? ($type->count / $total) * 100 : 0; @endphp
                        <div>
                            <div class="flex justify-between text-sm mb-1">
                                <span class="text-neutral-300">{{ $typeLabels[$type->type->value ?? $type->type] ?? $type->type }}</span>
                                <span class="text-neutral-400">{{ number_format($type->count) }}</span>
                            </div>
                            <div class="h-2 bg-neutral-700 rounded-full overflow-hidden">
                                <div class="h-full {{ $typeColors[$type->type->value ?? $type->type] ?? 'bg-neutral-500' }} rounded-full" style="width: {{ $percent }}%"></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-neutral-500 text-center py-8">Aucune donnée</p>
            @endif
        </div>

        <!-- Top Cities -->
        <div class="bg-neutral-800/50 border border-neutral-700 rounded-2xl p-6">
            <h3 class="text-lg font-semibold text-white mb-4">Top villes</h3>
            @if($topCities->isNotEmpty())
                <div class="space-y-3">
                    @foreach($topCities->take(5) as $index => $city)
                        <div class="flex items-center gap-3">
                            <span class="w-6 h-6 bg-neutral-700 rounded-full flex items-center justify-center text-xs font-bold text-neutral-300">
                                {{ $index + 1 }}
                            </span>
                            <span class="flex-1 text-neutral-300">{{ $city->city }}</span>
                            <span class="text-neutral-400 text-sm">{{ $city->count }} restaurants</span>
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-neutral-500 text-center py-8">Aucune donnée</p>
            @endif
        </div>
    </div>

    <!-- Subscription Revenue -->
    <div class="bg-neutral-800/50 border border-neutral-700 rounded-2xl p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-lg font-semibold text-white">Revenus abonnements</h3>
            <span class="text-2xl font-bold text-secondary-400">
                {{ number_format($summary['subscription_revenue'], 0, ',', ' ') }} F
            </span>
        </div>
        @if($subscriptionRevenue->isNotEmpty())
            <div class="grid grid-cols-7 gap-2">
                @foreach($subscriptionRevenue->take(7) as $data)
                    @php
                        $maxRevenue = $subscriptionRevenue->max('revenue') ?: 1;
                        $percent = ($data->revenue / $maxRevenue) * 100;
                    @endphp
                    <div class="text-center">
                        <div class="h-24 bg-neutral-700 rounded-lg relative overflow-hidden">
                            <div class="absolute bottom-0 left-0 right-0 bg-secondary-500 rounded-b-lg" style="height: {{ $percent }}%"></div>
                        </div>
                        <p class="text-xs text-neutral-500 mt-2">{{ \Carbon\Carbon::parse($data->date)->format('d/m') }}</p>
                    </div>
                @endforeach
            </div>
        @else
            <p class="text-neutral-500 text-center py-8">Aucune donnée d'abonnement</p>
        @endif
    </div>
</x-layouts.admin-super>
