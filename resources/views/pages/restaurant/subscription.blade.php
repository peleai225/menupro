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

    <!-- Pending Subscription Alert -->
    @php
        $pendingSubscription = $restaurant->subscriptions()
            ->where('status', \App\Enums\SubscriptionStatus::PENDING)
            ->latest()
            ->first();
    @endphp
    @if($pendingSubscription && $restaurant->status === \App\Enums\RestaurantStatus::PENDING)
        <div class="mb-6 p-6 bg-gradient-to-r from-yellow-50 to-orange-50 border-2 border-yellow-300 rounded-xl shadow-lg">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0">
                    <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-bold text-yellow-900 mb-2">Paiement en attente</h3>
                    <p class="text-yellow-800 mb-4">
                        Votre compte a été créé mais votre abonnement est en attente de paiement. 
                        Complétez votre paiement pour activer votre restaurant et commencer à recevoir des commandes.
                    </p>
                    <div class="flex items-center gap-4">
                        <div class="flex-1">
                            <p class="text-sm text-yellow-700 mb-1">Montant à payer :</p>
                            <p class="text-2xl font-bold text-yellow-900">
                                {{ number_format($pendingSubscription->amount_paid, 0, ',', ' ') }} FCFA
                            </p>
                        </div>
                        <form method="POST" action="{{ route('restaurant.subscription.retry', $pendingSubscription) }}">
                            @csrf
                            <button type="submit" class="btn btn-primary px-6 py-3 font-semibold shadow-lg hover:shadow-xl">
                                Compléter le paiement
                            </button>
                        </form>
                    </div>
                    <p class="text-xs text-yellow-600 mt-4">
                        ⚠️ Votre compte sera supprimé automatiquement si le paiement n'est pas complété dans les 48 heures.
                    </p>
                </div>
            </div>
        </div>
    @endif

    <!-- Trial Alert -->
    @if($subscription && $subscription->isTrial())
        @php
            $daysLeft = $restaurant->days_until_expiration ?? 0;
            $trialEndsAt = $subscription->ends_at;
        @endphp
        <div class="mb-6 p-6 bg-gradient-to-r from-blue-50 to-indigo-50 border-2 border-blue-300 rounded-xl shadow-lg">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0">
                    <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-bold text-blue-900 mb-2">Essai gratuit de 14 jours</h3>
                    <p class="text-blue-800 mb-4">
                        Vous profitez actuellement de votre essai gratuit. 
                        @if($daysLeft > 0)
                            Il vous reste <strong>{{ $daysLeft }} jour(s)</strong> pour tester toutes les fonctionnalités de MenuPro.
                        @else
                            Votre essai expire aujourd'hui !
                        @endif
                    </p>
                    <div class="flex items-center gap-4">
                        <div class="flex-1">
                            <p class="text-sm text-blue-700 mb-1">Expire le :</p>
                            <p class="text-lg font-bold text-blue-900">
                                {{ $trialEndsAt->locale('fr')->isoFormat('dddd D MMMM YYYY [à] HH:mm') }}
                            </p>
                        </div>
                        <a href="{{ route('restaurant.subscription.plans') }}" class="btn btn-primary px-6 py-3 font-semibold shadow-lg hover:shadow-xl">
                            Convertir en abonnement payant
                        </a>
                    </div>
                    @if($daysLeft <= 3)
                        <p class="text-xs text-blue-600 mt-4 font-medium">
                            ⚠️ Votre essai expire bientôt ! Souscrivez maintenant pour continuer à utiliser MenuPro sans interruption.
                        </p>
                    @endif
                </div>
            </div>
        </div>
    @endif

    <!-- Expired Trial Alert -->
    @if($restaurant->is_subscription_expired && $subscription && $subscription->isTrial())
        <div class="mb-6 p-6 bg-gradient-to-r from-red-50 to-orange-50 border-2 border-red-400 rounded-xl shadow-lg">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0">
                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <h3 class="text-lg font-bold text-red-900 mb-2">🔒 Votre essai gratuit a expiré</h3>
                    <p class="text-red-800 mb-4 font-medium">
                        Votre essai gratuit de 14 jours est terminé. Pour continuer à utiliser MenuPro, vous devez souscrire à un abonnement payant.
                    </p>
                    <div class="bg-white rounded-lg p-4 mb-4 border border-red-200">
                        <p class="text-sm font-semibold text-red-900 mb-2">⚠️ Fonctionnalités actuellement bloquées :</p>
                        <ul class="text-sm text-red-700 space-y-1">
                            <li class="flex items-center gap-2"><svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg> Réception de nouvelles commandes</li>
                            <li class="flex items-center gap-2"><svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg> Accès complet au tableau de bord</li>
                            <li class="flex items-center gap-2"><svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg> Gestion des commandes</li>
                            <li class="flex items-center gap-2"><svg class="w-4 h-4 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg> Statistiques et rapports</li>
                        </ul>
                    </div>
                    <div class="flex items-center gap-4">
                        <div class="flex-1">
                            <p class="text-sm text-red-700 mb-1">Pour débloquer votre compte :</p>
                            <p class="text-lg font-bold text-red-900">
                                Souscrivez à un abonnement maintenant
                            </p>
                        </div>
                        <a href="{{ route('restaurant.subscription.plans') }}" class="btn btn-primary px-6 py-3 font-semibold shadow-lg hover:shadow-xl bg-red-600 hover:bg-red-700">
                            Souscrire maintenant
                        </a>
                    </div>
                    <p class="text-xs text-red-600 mt-4 font-medium">
                        💡 Une fois le paiement effectué, votre compte sera immédiatement débloqué et vous pourrez continuer à utiliser MenuPro.
                    </p>
                </div>
            </div>
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
                    @if($subscription && $subscription->isTrial())
                        <span class="badge bg-blue-100 text-blue-700">Essai gratuit</span>
                    @elseif($subscription && $subscription->isActive())
                        <span class="badge badge-success">Actif</span>
                    @elseif($restaurant->subscription_ends_at && $restaurant->subscription_ends_at->isFuture())
                        <span class="badge badge-success">Actif</span>
                    @else
                        <span class="badge bg-red-100 text-red-700">Expiré</span>
                    @endif
                </div>
                @if($subscription && $subscription->isTrial())
                    <p class="text-blue-600 font-medium">
                        Essai gratuit • Expire le {{ $subscription->ends_at->locale('fr')->isoFormat('D MMMM YYYY') }}
                    </p>
                @elseif($restaurant->subscription_ends_at)
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
                @if($subscription && $subscription->isTrial())
                    <p class="text-3xl font-bold text-blue-600">Gratuit</p>
                    <p class="text-sm text-blue-500">{{ $restaurant->days_until_expiration ?? 0 }} jour(s) restant(s)</p>
                @elseif($currentPlan)
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
                                <div class="flex items-center gap-3">
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
                                    @if($sub->status->value === 'pending')
                                        <form method="POST" action="{{ route('restaurant.subscription.retry', $sub) }}" class="inline">
                                            @csrf
                                            <button type="submit" class="btn btn-sm btn-primary">
                                                Reprendre le paiement
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
            </div>
        </div>
    @endif
</x-layouts.admin-restaurant>
