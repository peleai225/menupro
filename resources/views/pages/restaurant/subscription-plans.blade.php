<x-layouts.admin-restaurant title="Changer de plan">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-neutral-900">Changer de plan</h1>
        <p class="text-neutral-600 mt-2">Choisissez le plan adapté à votre restaurant. Paiement par Mobile Money.</p>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700">
            {{ session('error') }}
        </div>
    @endif

    @php
        $isTrial = $subscription && $subscription->isTrial();
        $periods = \App\Models\Subscription::billingPeriods();
        // Prix calculés côté serveur (remises, prix hebdo Stand, prix verrouillé des anciens abonnés)
        $quotes = $plans->mapWithKeys(fn ($plan) => [
            $plan->slug => collect($plan->availableBillingPeriods())->mapWithKeys(fn ($p) => [
                $p => \App\Models\Subscription::quote($plan, $p, $restaurant),
            ]),
        ]);
        $hasWeekly = $plans->contains(fn ($plan) => $plan->supportsWeeklyBilling());
        $periodSuffix = ['weekly' => '/semaine', 'monthly' => '/mois', 'quarterly' => '/trimestre', 'semiannual' => '/semestre', 'annual' => '/an'];
    @endphp

    @if($isTrial)
        <div class="mb-6 p-5 bg-gradient-to-r from-blue-50 to-indigo-50 border-2 border-blue-300 rounded-xl">
            <div class="flex items-start gap-4">
                <svg class="w-7 h-7 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <h3 class="text-base font-bold text-blue-900 mb-1">Essai gratuit en cours — toutes les fonctions du plan Pro</h3>
                    <p class="text-sm text-blue-800">
                        @if($subscription->ends_at?->isFuture())
                            Il vous reste <strong>{{ $restaurant->days_until_expiration ?? 0 }} jour(s)</strong> d'essai.
                        @endif
                        Choisissez le plan que vous paierez à la fin de l'essai.
                    </p>
                </div>
            </div>
        </div>
    @endif

    <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-sm text-emerald-800">
        <strong>Livraison incluse</strong> — commission de 12 % uniquement sur les commandes livrées (10 % avec le plan Pro).
        <strong>Aucune commande n'est jamais bloquée</strong>, même au-delà du nombre de commandes prévu par votre plan.
    </div>

    <!-- Billing period selector -->
    <div x-data="{ period: 'monthly', quotes: @js($quotes) }" class="space-y-6">
        <div class="card p-4">
            <p class="text-sm font-medium text-neutral-700 mb-3">Période de facturation :</p>
            <div class="grid grid-cols-2 {{ $hasWeekly ? 'sm:grid-cols-5' : 'sm:grid-cols-4' }} gap-2">
                @foreach($periods as $key => $p)
                    @continue($key === 'weekly' && !$hasWeekly)
                    <button type="button" @click="period = '{{ $key }}'" :class="period === '{{ $key }}' ? 'border-primary-500 bg-primary-50 text-primary-700' : 'border-neutral-200 text-neutral-600 hover:border-neutral-300'" class="px-3 py-2 rounded-lg border text-sm font-medium transition-all text-center">
                        {{ $p['label'] }}
                        @if($key === 'weekly')
                            <span class="text-neutral-400 font-normal">(Stand)</span>
                        @elseif($p['discount'] > 0)
                            <span class="text-secondary-600 font-bold">-{{ $p['discount'] }}%</span>
                        @endif
                    </button>
                @endforeach
            </div>
        </div>

        <!-- Plans list -->
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4">
            @foreach($plans as $plan)
                @php
                    $isCurrentPlan = $currentPlan && $currentPlan->id === $plan->id && !$isTrial;
                    $planColors = match($plan->slug) {
                        'stand' => ['border' => 'border-neutral-200 hover:border-primary-300', 'price' => 'text-neutral-900'],
                        'essentiel' => ['border' => 'border-primary-200 hover:border-primary-400 ring-2 ring-primary-100', 'price' => 'text-primary-600'],
                        'pro' => ['border' => 'border-amber-200 hover:border-amber-400', 'price' => 'text-amber-600'],
                        'gold' => ['border' => 'border-primary-400 hover:border-primary-500 ring-2 ring-primary-200 bg-gradient-to-br from-primary-50 to-accent-50', 'price' => 'bg-gradient-to-r from-primary-600 to-accent-600 bg-clip-text text-transparent'],
                        default => ['border' => 'border-neutral-200', 'price' => 'text-neutral-900'],
                    };
                    $lockedApplied = $quotes[$plan->slug]['monthly']['locked_price_applied'] ?? false;
                @endphp
                <div class="card p-5 {{ $planColors['border'] }} transition-all relative flex flex-col {{ $isCurrentPlan ? 'opacity-90' : '' }}">
                    @if($plan->is_featured)
                        <div class="absolute -top-2.5 left-4">
                            <span class="bg-primary-500 text-white text-[10px] font-bold px-2.5 py-1 rounded-full">POPULAIRE</span>
                        </div>
                    @elseif($plan->slug === 'gold')
                        <div class="absolute -top-2.5 left-4">
                            <span class="bg-gradient-to-r from-primary-500 to-primary-600 text-white text-[10px] font-bold px-2.5 py-1 rounded-full shadow-md">SUR DEVIS</span>
                        </div>
                    @endif

                    <div class="mb-4">
                        <h3 class="text-lg font-bold text-neutral-900">{{ $plan->name }}</h3>
                        <p class="text-xs text-neutral-500 mt-1">{{ $plan->description }}</p>
                    </div>

                    <!-- Prix (calculé côté serveur) -->
                    <div class="mb-4">
                        @if($plan->is_on_quote)
                            <p class="text-xs text-neutral-500">Sur devis, à partir de</p>
                        @endif
                        <template x-if="quotes['{{ $plan->slug }}'][period]">
                            <div>
                                <div class="flex items-baseline gap-1">
                                    <span class="text-2xl font-bold {{ $planColors['price'] }}"
                                          x-text="new Intl.NumberFormat('fr-FR').format(quotes['{{ $plan->slug }}'][period].final_price) + ' F'"></span>
                                    <span class="text-xs text-neutral-500" x-text="@js($periodSuffix)[period]"></span>
                                </div>
                                <template x-if="period !== 'monthly' && period !== 'weekly'">
                                    <p class="text-xs text-secondary-600 mt-1" x-text="'soit ' + new Intl.NumberFormat('fr-FR').format(quotes['{{ $plan->slug }}'][period].monthly_equivalent) + ' F/mois'"></p>
                                </template>
                            </div>
                        </template>
                        <template x-if="!quotes['{{ $plan->slug }}'][period]">
                            <p class="text-sm text-neutral-400">Non disponible à la semaine</p>
                        </template>
                        @if($lockedApplied)
                            <p class="text-xs text-emerald-700 font-medium mt-1">Votre tarif d'abonné est conservé</p>
                        @endif
                    </div>

                    <!-- Contenu du plan -->
                    <ul class="space-y-1.5 mb-4 text-sm text-neutral-600 flex-1">
                        @foreach($plan->features as $feature)
                            <li class="flex items-start gap-2">
                                <svg class="w-3.5 h-3.5 text-primary-500 mt-1 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                <span>{{ $feature }}</span>
                            </li>
                        @endforeach
                        @if($plan->has_delivery)
                            <li class="flex items-start gap-2 text-xs text-neutral-500">
                                <span class="pl-5">Commission de {{ rtrim(rtrim(number_format($plan->commissionRate(), 2, ',', ''), '0'), ',') }} % uniquement sur les commandes livrées</span>
                            </li>
                        @endif
                    </ul>

                    <!-- Action -->
                    @if($plan->is_on_quote)
                        <a href="{{ route('contact') }}?type=gold" class="w-full py-2.5 rounded-lg text-sm font-bold text-center bg-neutral-900 hover:bg-neutral-800 text-white transition-all">
                            Demander un devis
                        </a>
                    @else
                        <form method="POST"
                              action="{{ $isTrial ? route('restaurant.subscription.convertTrial') : route('restaurant.subscription.change') }}">
                            @csrf
                            <input type="hidden" name="plan" value="{{ $plan->slug }}">
                            <input type="hidden" name="billing_period" x-bind:value="period">
                            <button type="submit"
                                    x-bind:disabled="{{ $isCurrentPlan ? 'true' : 'false' }} || !quotes['{{ $plan->slug }}'][period]"
                                    class="w-full py-2.5 rounded-lg text-sm font-bold transition-all disabled:opacity-40 disabled:cursor-not-allowed {{ $plan->is_featured ? 'bg-primary-500 hover:bg-primary-600 text-white' : 'bg-neutral-900 hover:bg-neutral-800 text-white' }}">
                                @if($isCurrentPlan)
                                    Plan actuel
                                @elseif($isTrial)
                                    Activer ce plan
                                @else
                                    Choisir ce plan
                                @endif
                            </button>
                        </form>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
</x-layouts.admin-restaurant>
