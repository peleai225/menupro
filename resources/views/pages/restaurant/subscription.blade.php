<x-layouts.admin-restaurant title="Abonnement">
    <!-- Flash Messages -->
    @if(session('success'))
        <div class="mb-6 p-4 bg-secondary-50 border border-secondary-200 rounded-xl text-secondary-700">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700">
            {{ session('error') }}
        </div>
    @endif
    @if(session('info'))
        <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-xl text-blue-700">
            {{ session('info') }}
        </div>
    @endif
    @if(session('warning'))
        <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-xl text-yellow-700">
            {{ session('warning') }}
        </div>
    @endif

    <!-- Current Plan -->
    <div class="card p-6 mb-8">
        <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-6">
            <div>
                <div class="flex items-center gap-3 mb-2">
                    <h1 class="text-2xl font-bold text-neutral-900">
                        Plan {{ $currentPlan?->name ?? 'Aucun' }}
                    </h1>
                    @if($subscription && $subscription->isActive())
                        <span class="badge badge-success">Actif</span>
                    @elseif($restaurant->subscription_ends_at && $restaurant->subscription_ends_at->isFuture())
                        <span class="badge badge-success">Actif</span>
                    @else
                        <span class="badge bg-red-100 text-red-700">Expiré</span>
                    @endif
                </div>
                @if($restaurant->subscription_ends_at)
                    @if($restaurant->subscription_ends_at->isFuture())
                        <p class="text-neutral-500">Votre abonnement expire le {{ $restaurant->subscription_ends_at->locale('fr')->isoFormat('D MMMM YYYY') }}</p>
                    @else
                        <p class="text-red-500">Votre abonnement a expiré le {{ $restaurant->subscription_ends_at->locale('fr')->isoFormat('D MMMM YYYY') }}</p>
                    @endif
                @else
                    <p class="text-neutral-500">Aucun abonnement actif</p>
                @endif
            </div>
            <div class="text-right">
                @if($currentPlan)
                    <p class="text-3xl font-bold text-neutral-900">{{ number_format($currentPlan->price, 0, ',', ' ') }} <span class="text-lg font-normal">F/mois</span></p>
                    @if($restaurant->subscription_ends_at && $restaurant->subscription_ends_at->isFuture())
                        <p class="text-sm text-neutral-500">Expire le {{ $restaurant->subscription_ends_at->format('d M Y') }}</p>
                    @endif
                @else
                    <p class="text-neutral-500">Choisissez un plan ci-dessous</p>
                @endif
            </div>
        </div>
    </div>

    <!-- Usage -->
    @if($currentPlan)
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
            <div class="card p-6">
                @php
                    $dishesCount = $restaurant->dishes()->count();
                    $maxDishes = $currentPlan->max_dishes ?? 999;
                    $dishesPercent = $maxDishes > 0 ? min(100, ($dishesCount / $maxDishes) * 100) : 0;
                @endphp
                <div class="flex items-center justify-between mb-4">
                    <span class="text-neutral-600">Plats</span>
                    <span class="font-bold text-neutral-900">{{ $dishesCount }} / {{ $maxDishes == 999 ? '∞' : $maxDishes }}</span>
                </div>
                <div class="h-2 bg-neutral-100 rounded-full overflow-hidden">
                    <div class="h-full bg-primary-500 rounded-full transition-all" style="width: {{ $dishesPercent }}%"></div>
                </div>
            </div>
            <div class="card p-6">
                @php
                    $categoriesCount = $restaurant->categories()->count();
                    $maxCategories = $currentPlan->max_categories ?? 999;
                    $categoriesPercent = $maxCategories > 0 ? min(100, ($categoriesCount / $maxCategories) * 100) : 0;
                @endphp
                <div class="flex items-center justify-between mb-4">
                    <span class="text-neutral-600">Catégories</span>
                    <span class="font-bold text-neutral-900">{{ $categoriesCount }} / {{ $maxCategories == 999 ? '∞' : $maxCategories }}</span>
                </div>
                <div class="h-2 bg-neutral-100 rounded-full overflow-hidden">
                    <div class="h-full bg-secondary-500 rounded-full transition-all" style="width: {{ $categoriesPercent }}%"></div>
                </div>
            </div>
            <div class="card p-6">
                @php
                    $teamCount = $restaurant->users()->where('role', 'employee')->count();
                    $maxTeam = $currentPlan->max_employees ?? 1;
                    $teamPercent = $maxTeam > 0 ? min(100, ($teamCount / $maxTeam) * 100) : 0;
                @endphp
                <div class="flex items-center justify-between mb-4">
                    <span class="text-neutral-600">Équipe</span>
                    <span class="font-bold text-neutral-900">{{ $teamCount }} / {{ $maxTeam }}</span>
                </div>
                <div class="h-2 bg-neutral-100 rounded-full overflow-hidden">
                    <div class="h-full bg-accent-500 rounded-full transition-all" style="width: {{ $teamPercent }}%"></div>
                </div>
            </div>
        </div>
    @endif

    <!-- Plan MenuPro -->
    @php
        $menuProPlan = $plans->firstWhere('slug', 'menupro') ?? $plans->first();
        $isCurrentPlan = $currentPlan && $currentPlan->id === $menuProPlan?->id;
        $availableAddons = \App\Models\SubscriptionAddon::getAvailableAddons();
    @endphp

    @if($menuProPlan)
        <div class="mb-6">
            <h2 class="text-2xl font-bold text-neutral-900 mb-2">{{ $currentPlan ? 'Renouveler votre abonnement' : 'Choisir votre abonnement' }}</h2>
            <p class="text-neutral-500">Un seul plan, toutes les fonctionnalités. Choisissez votre période de facturation.</p>
        </div>

        <div class="card p-8 mb-8 border-2 {{ $isCurrentPlan ? 'border-primary-500 bg-primary-50/30' : 'border-neutral-200' }} relative overflow-visible">
            @if($isCurrentPlan)
                <span class="absolute -top-3 left-1/2 -translate-x-1/2 bg-primary-500 text-white text-sm px-4 py-2 rounded-full font-bold shadow-lg whitespace-nowrap z-10 border-2 border-white">Plan actuel</span>
            @endif
            <span class="absolute -top-3 right-4 bg-secondary-500 text-white text-sm px-3 py-2 rounded-full font-bold shadow-lg z-10 border-2 border-white">Populaire</span>
            
            <div class="mb-6">
                <h3 class="text-3xl font-bold text-neutral-900 mb-2">{{ $menuProPlan->name }}</h3>
                <p class="text-neutral-600">{{ $menuProPlan->description }}</p>
            </div>

            <!-- Billing Period Selection -->
            <div class="mb-8" x-data="{ billingPeriod: 'monthly' }">
                <label class="block text-sm font-medium text-neutral-700 mb-4">Période de facturation</label>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    @foreach([
                        'monthly' => ['label' => 'Mensuel', 'months' => 1, 'discount' => 0],
                        'quarterly' => ['label' => 'Trimestriel', 'months' => 3, 'discount' => 7],
                        'semiannual' => ['label' => 'Semestriel', 'months' => 6, 'discount' => 13],
                        'annual' => ['label' => 'Annuel', 'months' => 12, 'discount' => 15],
                    ] as $period => $data)
                        @php
                            $calc = \App\Models\Subscription::calculatePriceWithDiscount($menuProPlan->price, $period);
                        @endphp
                        <label class="relative cursor-pointer">
                            <input type="radio" name="billing_period" value="{{ $period }}" x-model="billingPeriod" class="sr-only peer">
                            <div class="p-4 border-2 border-neutral-200 rounded-xl peer-checked:border-primary-500 peer-checked:bg-primary-50 transition-all hover:border-primary-300">
                                <div class="font-bold text-neutral-900 mb-1">{{ $data['label'] }}</div>
                                <div class="text-2xl font-bold text-primary-600 mb-1">{{ number_format($calc['final_price'], 0, ',', ' ') }} F</div>
                                @if($data['discount'] > 0)
                                    <div class="text-xs text-emerald-600 font-semibold">-{{ $data['discount'] }}%</div>
                                    <div class="text-xs text-neutral-500 line-through">{{ number_format($calc['total_before_discount'], 0, ',', ' ') }} F</div>
                                @else
                                    <div class="text-xs text-neutral-500">{{ number_format($calc['monthly_equivalent'], 0, ',', ' ') }} F/mois</div>
                                @endif
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Features List -->
            <div class="mb-8">
                <h4 class="text-lg font-semibold text-neutral-900 mb-4">Toutes les fonctionnalités incluses :</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <ul class="space-y-3">
                        <li class="flex items-center gap-2 text-neutral-600">
                            <svg class="w-5 h-5 text-secondary-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            {{ $menuProPlan->max_dishes }} plats maximum
                        </li>
                        <li class="flex items-center gap-2 text-neutral-600">
                            <svg class="w-5 h-5 text-secondary-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            {{ $menuProPlan->max_categories }} catégories
                        </li>
                        <li class="flex items-center gap-2 text-neutral-600">
                            <svg class="w-5 h-5 text-secondary-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            {{ $menuProPlan->max_employees }} employés
                        </li>
                        <li class="flex items-center gap-2 text-neutral-600">
                            <svg class="w-5 h-5 text-secondary-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            {{ number_format($menuProPlan->max_orders_per_month ?? 0, 0, ',', ' ') }} commandes/mois
                        </li>
                    </ul>
                    <ul class="space-y-3">
                        @if($menuProPlan->has_delivery)
                            <li class="flex items-center gap-2 text-neutral-600">
                                <svg class="w-5 h-5 text-secondary-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Gestion de la livraison
                            </li>
                        @endif
                        @if($menuProPlan->has_stock_management)
                            <li class="flex items-center gap-2 text-neutral-600">
                                <svg class="w-5 h-5 text-secondary-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Gestion du stock
                            </li>
                        @endif
                        @if($menuProPlan->has_analytics)
                            <li class="flex items-center gap-2 text-neutral-600">
                                <svg class="w-5 h-5 text-secondary-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Statistiques avancées
                            </li>
                        @endif
                        <li class="flex items-center gap-2 text-neutral-600">
                            <svg class="w-5 h-5 text-secondary-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Réservations de tables
                        </li>
                        <li class="flex items-center gap-2 text-neutral-600">
                            <svg class="w-5 h-5 text-secondary-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Avis clients
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Add-ons Section -->
            <div class="mb-8 border-t border-neutral-200 pt-6">
                <h4 class="text-lg font-semibold text-neutral-900 mb-4">Add-ons optionnels</h4>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4" x-data="{ selectedAddons: [] }">
                    @foreach($availableAddons as $addonType => $addon)
                        <label class="flex items-start gap-3 p-4 border-2 border-neutral-200 rounded-xl hover:border-primary-300 transition-all cursor-pointer">
                            <input type="checkbox" name="addons[]" value="{{ $addonType }}" x-model="selectedAddons" class="mt-1 w-5 h-5 text-primary-600 border-neutral-300 rounded focus:ring-primary-500">
                            <div class="flex-1">
                                <div class="font-semibold text-neutral-900 mb-1">{{ $addon['name'] }}</div>
                                <div class="text-sm text-neutral-600 mb-2">{{ $addon['description'] }}</div>
                                <div class="text-lg font-bold text-primary-600">{{ number_format($addon['price'], 0, ',', ' ') }} F/mois</div>
                            </div>
                        </label>
                    @endforeach
                </div>
            </div>

            <!-- Subscribe Button -->
            @if($isCurrentPlan)
                <button class="btn btn-primary w-full px-6 py-3 flex items-center justify-center gap-2 shadow-sm" disabled>Plan actuel</button>
            @else
                <form method="POST" action="{{ route('restaurant.subscription.change') }}" x-data="{ billingPeriod: 'monthly' }" @submit.prevent="submit">
                    @csrf
                    <input type="hidden" name="plan" value="{{ $menuProPlan->slug }}">
                    <input type="hidden" name="billing_period" :value="billingPeriod">
                    <div class="flex items-center justify-center gap-4 mb-4">
                        <button type="submit" class="btn btn-primary px-8 py-4 text-lg font-semibold shadow-lg hover:shadow-xl transition-all">
                            {{ $currentPlan ? 'Renouveler l\'abonnement' : 'S\'abonner maintenant' }}
                        </button>
                    </div>
                    <p class="text-center text-sm text-neutral-500">Vous serez redirigé vers la page de paiement sécurisée</p>
                </form>
            @endif
        </div>
    @endif

    <!-- Payment History -->
    @if($history->isNotEmpty())
        <h2 class="text-xl font-bold text-neutral-900 mb-4">Historique des paiements</h2>
        <div class="card overflow-hidden">
            <div class="table-responsive">
            <table class="w-full min-w-[500px]">
                <thead class="bg-neutral-50 border-b border-neutral-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-500 uppercase">Date</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-500 uppercase">Description</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-500 uppercase">Montant</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-500 uppercase">Statut</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100">
                    @foreach($history as $sub)
                        <tr>
                            <td class="px-6 py-4 text-neutral-900">{{ $sub->created_at->format('d M Y') }}</td>
                            <td class="px-6 py-4 text-neutral-600">
                                Abonnement {{ $sub->plan->name }}
                                @if($sub->billing_period && $sub->billing_period !== 'monthly')
                                    <span class="text-xs text-neutral-500">({{ ucfirst($sub->billing_period) }})</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 font-semibold text-neutral-900">
                                {{ number_format($sub->amount_paid, 0, ',', ' ') }} F
                                @if($sub->discount_percentage > 0)
                                    <span class="text-xs text-emerald-600">(-{{ $sub->discount_percentage }}%)</span>
                                @endif
                            </td>
                            <td class="px-6 py-4">
                                @php
                                    $statusColors = [
                                        'active' => 'badge-success',
                                        'pending' => 'bg-yellow-100 text-yellow-700',
                                        'cancelled' => 'bg-red-100 text-red-700',
                                        'expired' => 'bg-neutral-100 text-neutral-700',
                                    ];
                                    $statusLabels = [
                                        'active' => 'Actif',
                                        'pending' => 'En attente',
                                        'cancelled' => 'Annulé',
                                        'expired' => 'Expiré',
                                    ];
                                @endphp
                                <span class="badge {{ $statusColors[$sub->status->value] ?? 'bg-neutral-100 text-neutral-700' }}">
                                    {{ $statusLabels[$sub->status->value] ?? $sub->status->value }}
                                </span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        </div>
    @endif
</x-layouts.admin-restaurant>
