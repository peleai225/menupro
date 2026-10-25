<x-layouts.admin-restaurant title="Abonnement">

    {{-- Flash messages --}}
    @foreach(['success' => 'emerald', 'error' => 'red', 'info' => 'blue', 'warning' => 'amber'] as $type => $color)
        @if(session($type))
            <div class="mb-6 p-4 bg-{{ $color }}-50 border border-{{ $color }}-200 rounded-2xl flex items-start gap-3">
                <svg class="w-5 h-5 text-{{ $color }}-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm text-{{ $color }}-700 font-medium">{{ session($type) }}</p>
            </div>
        @endif
    @endforeach

    @php
        $isTrial        = $subscription && $subscription->isTrial();
        $isActive       = $subscription && $subscription->isActive() && !$isTrial;
        $isExpired      = $restaurant->is_subscription_expired;
        $daysLeft       = $restaurant->days_until_expiration ?? 0;
        $trialDays      = $subscription?->trial_days ?? config('plans.trial_days', 30);
        $availableAddons = \App\Models\SubscriptionAddon::getAvailableAddons();
        $isStand        = ($restaurant->currentPlan?->slug ?? '') === 'stand';

        // Prix calculés côté serveur (remises, prix hebdo Stand, tarif conservé des anciens abonnés)
        $quotes = $plans->mapWithKeys(fn ($plan) => [
            $plan->slug => collect($plan->availableBillingPeriods())->mapWithKeys(fn ($p) => [
                $p => \App\Models\Subscription::quote($plan, $p, $restaurant),
            ]),
        ]);
        $hasWeekly = $plans->contains(fn ($plan) => $plan->supportsWeeklyBilling());
        $periodSuffix = ['weekly' => '/semaine', 'monthly' => '/mois', 'quarterly' => '/trimestre', 'semiannual' => '/semestre', 'annual' => '/an'];
        $fmt = fn ($n) => number_format((int) $n, 0, ',', ' ');

        $pendingSubscription = $restaurant->subscriptions()
            ->where('status', \App\Enums\SubscriptionStatus::PENDING)
            ->latest()->first();
    @endphp

    {{-- ═══ BANNIÈRE PAIEMENT EN ATTENTE ═══ --}}
    @if($pendingSubscription && $pendingSubscription->status === \App\Enums\SubscriptionStatus::PENDING)
        <div class="mb-6 p-5 rounded-2xl border-2 border-amber-300 bg-gradient-to-r from-amber-50 to-orange-50 flex items-start gap-4">
            <div class="w-10 h-10 rounded-xl bg-amber-100 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
            </div>
            <div class="flex-1">
                <h3 class="font-bold text-amber-900 mb-1">Paiement en attente</h3>
                <p class="text-sm text-amber-800 mb-3">Votre abonnement <strong>{{ $pendingSubscription->plan->name }}</strong> est en attente de paiement — <strong>{{ number_format($pendingSubscription->amount_paid, 0, ',', ' ') }} FCFA</strong>.</p>
                <form method="POST" action="{{ route('restaurant.subscription.retry', $pendingSubscription) }}">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-amber-500 hover:bg-amber-600 text-white text-sm font-semibold rounded-xl transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                        Reprendre le paiement
                    </button>
                </form>
            </div>
        </div>
    @endif

    {{-- ═══ STATUT ACTUEL ═══ --}}
    @if($isTrial && !$isExpired)
        {{-- Essai en cours --}}
        <div class="mb-8 p-6 rounded-2xl border-2 border-primary-200 bg-gradient-to-r from-primary-50 to-blue-50">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-2xl bg-primary-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <div class="flex items-center gap-2 mb-1">
                            <h2 class="text-lg font-bold text-neutral-900">Essai gratuit</h2>
                            <span class="px-2 py-0.5 text-xs font-bold bg-primary-100 text-primary-700 rounded-full">
                                {{ $daysLeft }} jour{{ $daysLeft > 1 ? 's' : '' }} restant{{ $daysLeft > 1 ? 's' : '' }}
                            </span>
                        </div>
                        <p class="text-sm text-neutral-600">
                            Expire le <strong>{{ $subscription->ends_at?->locale('fr')->isoFormat('D MMMM YYYY') }}</strong> —
                            accès complet à toutes les fonctions du plan Pro. Choisissez ensuite votre plan, payé en Mobile Money.
                        </p>
                        @if($daysLeft <= 2)
                            <p class="text-xs text-red-600 font-semibold mt-1">⚠️ Votre essai expire très bientôt, abonnez-vous pour ne pas perdre l'accès.</p>
                        @endif
                    </div>
                </div>
                {{-- Barre de progression --}}
                <div class="sm:w-48 flex-shrink-0">
                    @php $progress = $trialDays > 0 ? max(0, min(100, ($daysLeft / $trialDays) * 100)) : 0; @endphp
                    <div class="flex justify-between text-xs text-neutral-500 mb-1">
                        <span>Progression</span>
                        <span>{{ $daysLeft }}/{{ $trialDays }}j</span>
                    </div>
                    <div class="h-2 bg-primary-100 rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all {{ $daysLeft <= 2 ? 'bg-red-500' : 'bg-primary-500' }}"
                             style="width: {{ $progress }}%"></div>
                    </div>
                </div>
            </div>
        </div>

    @elseif($isTrial && $isExpired)
        {{-- Essai expiré --}}
        <div class="mb-8 p-6 rounded-2xl border-2 border-red-300 bg-gradient-to-r from-red-50 to-orange-50">
            <div class="flex items-start gap-4">
                <div class="w-12 h-12 rounded-2xl bg-red-100 flex items-center justify-center flex-shrink-0">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-red-900 mb-1">Votre essai gratuit est terminé</h2>
                    <p class="text-sm text-red-800 mb-3">Les commandes et l'accès complet sont bloqués. Choisissez un plan ci-dessous pour réactiver votre restaurant.</p>
                    <div class="flex flex-wrap gap-2 text-xs text-red-700">
                        @foreach(['Réception de commandes', 'Tableau de bord complet', 'Gestion des commandes', 'Statistiques'] as $f)
                            <span class="flex items-center gap-1"><svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>{{ $f }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

    @elseif($isActive)
        {{-- Abonnement actif --}}
        <div class="mb-8 p-5 rounded-2xl border border-emerald-200 bg-emerald-50 flex items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 rounded-xl bg-emerald-100 flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="font-bold text-emerald-900">Plan {{ $currentPlan?->name }} — Actif</p>
                    <p class="text-sm text-emerald-700">
                        Expire le {{ $restaurant->subscription_ends_at?->locale('fr')->isoFormat('D MMMM YYYY') }}
                        @if($daysLeft <= 7) — <span class="font-semibold text-amber-600">{{ $daysLeft }} jours restants</span> @endif
                    </p>
                </div>
            </div>
            <span class="px-3 py-1 bg-emerald-100 text-emerald-700 text-xs font-bold rounded-full">{{ $daysLeft }}j restants</span>
        </div>
    @endif

    {{-- ═══ PLANS ═══ --}}
    @if($isStand)
    {{-- Récapitulatif simplifié pour le plan Stand : paiement à la semaine ou au mois --}}
    @php
        $standPlan = $plans->firstWhere('slug', 'stand') ?? $currentPlan;
        $standQuotes = $quotes[$standPlan->slug] ?? collect();
        $canPayStand = !$isActive || $daysLeft <= 7;
    @endphp
    <div class="mb-8" x-data="{ gateway: 'jeko', jeko_operator: 'wave' }">
        <h2 class="text-xl font-bold text-neutral-900 mb-4">Votre plan</h2>
        <div class="p-6 rounded-2xl border-2 border-primary-200 bg-gradient-to-br from-primary-50 to-white">
            <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-2 mb-4">
                <div>
                    <h3 class="text-lg font-bold text-neutral-900">Plan {{ $standPlan->name }}</h3>
                    <p class="text-sm text-neutral-500">Pour les stands, kiosques et street food</p>
                </div>
                <div class="sm:text-right">
                    <span class="text-2xl font-bold text-primary-600">{{ $fmt($standQuotes['monthly']['final_price'] ?? $standPlan->price) }} F</span>
                    <span class="text-sm text-neutral-500">/mois</span>
                    @if(isset($standQuotes['weekly']))
                        <p class="text-sm text-neutral-500">ou {{ $fmt($standQuotes['weekly']['final_price']) }} F/semaine</p>
                    @endif
                </div>
            </div>
            <ul class="grid grid-cols-1 sm:grid-cols-2 gap-2 mt-4">
                @foreach($standPlan->features as $feature)
                    <li class="flex items-start gap-2 text-sm text-neutral-700">
                        <svg class="w-4 h-4 text-emerald-500 mt-0.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        {{ $feature }}
                    </li>
                @endforeach
            </ul>
            <p class="text-xs text-neutral-500 mt-3">Livraison incluse — commission de 12 % uniquement sur les commandes livrées. Aucune commande n'est jamais bloquée.</p>

            @if($canPayStand)
            <div class="mt-5 pt-4 border-t border-neutral-200 flex flex-col sm:flex-row gap-3">
                @foreach(['weekly' => 'semaine', 'monthly' => 'mois'] as $period => $unit)
                    @continue(!isset($standQuotes[$period]))
                    <form method="POST" action="{{ $isTrial ? route('restaurant.subscription.convertTrial') : route('restaurant.subscription.change') }}" class="flex-1">
                        @csrf
                        <input type="hidden" name="plan" value="{{ $standPlan->slug }}">
                        <input type="hidden" name="billing_period" value="{{ $period }}">
                        <input type="hidden" name="payment_gateway" x-bind:value="gateway">
                        <input type="hidden" name="jeko_operator" x-bind:value="jeko_operator">
                        <button type="submit" class="w-full px-6 py-3 rounded-xl text-sm font-bold shadow-sm hover:shadow transition-all {{ $period === 'monthly' ? 'text-white bg-primary-600 hover:bg-primary-700' : 'text-primary-700 bg-white border-2 border-primary-300 hover:border-primary-500' }}">
                            @if($isTrial) Activer @elseif($isActive) Renouveler @else Réactiver @endif
                            — {{ $fmt($standQuotes[$period]['final_price']) }} F / {{ $unit }}
                        </button>
                    </form>
                @endforeach
            </div>
            @endif
            <div class="mt-4 pt-4 border-t border-neutral-200">
                <p class="text-sm text-neutral-600">Besoin de plus ? Le plan <strong>Restaurant</strong> ajoute la caisse, l'écran cuisine, le stock, les statistiques et les réservations.</p>
                <a href="{{ route('restaurant.subscription.plans') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-primary-600 hover:text-primary-700 mt-2">
                    Voir les plans supérieurs
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>
    </div>
    @else
    @php
        // MoneyFusion désactivé — Jeko est l'unique passerelle d'abonnement.
        // Remettre à la détection par SystemSetting pour le réactiver.
        $moneyFusionActive = false;
        $moneyFusionConfigured = !empty(\App\Models\SystemSetting::get('moneyfusion_api_url', ''));
        $jekoConfigured = \App\Models\SystemPaymentSetting::where('gateway', 'jeko_marketplace')->where('is_active', true)->exists();
    @endphp
    <div x-data="{ period: 'monthly', gateway: 'jeko', jeko_operator: 'wave', quotes: @js($quotes) }">

        {{-- Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
            <div>
                <h2 class="text-xl font-bold text-neutral-900">
                    @if($isTrial) Choisissez votre plan
                    @elseif($isActive) Modifier votre abonnement
                    @else Réactiver votre abonnement
                    @endif
                </h2>
                <p class="text-sm text-neutral-500 mt-0.5">Sans engagement · Changez de plan à tout moment</p>
            </div>
            {{-- Toggle période --}}
            <div class="flex bg-neutral-100 rounded-xl p-1 gap-1 text-xs font-semibold shrink-0">
                @foreach(array_filter(['weekly' => ['1 semaine', null], 'monthly' => ['1 mois', null], 'quarterly' => ['3 mois', '-10%'], 'semiannual' => ['6 mois', '-15%'], 'annual' => ['1 an', '-20%']], fn ($k) => $k !== 'weekly' || $hasWeekly, ARRAY_FILTER_USE_KEY) as $p => [$label, $badge])
                    <button type="button" @click="period = '{{ $p }}'"
                            :class="period === '{{ $p }}' ? 'bg-white shadow text-neutral-900' : 'text-neutral-500 hover:text-neutral-700'"
                            class="relative px-3 py-1.5 rounded-lg transition-all">
                        {{ $label }}
                        @if($badge)
                            <span class="absolute -top-1.5 -right-1 bg-emerald-500 text-white text-[8px] font-bold px-1 rounded leading-tight"
                                  x-show="period !== '{{ $p }}'">{{ $badge }}</span>
                        @endif
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Sélecteur moyen de paiement --}}
        @if(($moneyFusionActive && $moneyFusionConfigured) || $jekoConfigured)
        <div class="mb-6 p-4 bg-white rounded-2xl border border-neutral-200 shadow-sm">
            <h3 class="text-sm font-semibold text-neutral-700 mb-3">Moyen de paiement</h3>
            <div class="flex flex-col sm:flex-row gap-3">
                @if($moneyFusionActive && $moneyFusionConfigured)
                <label class="flex items-center gap-3 p-3 rounded-xl border-2 cursor-pointer transition-all flex-1"
                       :class="gateway === 'moneyfusion' ? 'border-blue-500 bg-blue-50' : 'border-neutral-200 hover:border-neutral-300'">
                    <input type="radio" x-model="gateway" value="moneyfusion" class="sr-only">
                    <span class="w-9 h-9 rounded-lg flex items-center justify-center flex-shrink-0" style="background:#2563eb;">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    </span>
                    <div>
                        <p class="font-medium text-neutral-900 text-sm">MoneyFusion</p>
                        <p class="text-xs text-neutral-500">Orange, MTN, Wave, Moov</p>
                    </div>
                </label>
                @endif
                @if($jekoConfigured)
                <label class="flex items-center gap-3 p-3 rounded-xl border-2 cursor-pointer transition-all flex-1"
                       :class="gateway === 'jeko' ? 'border-purple-500 bg-purple-50' : 'border-neutral-200 hover:border-neutral-300'">
                    <input type="radio" x-model="gateway" value="jeko" class="sr-only">
                    <span class="w-9 h-9 rounded-xl flex items-center justify-center flex-shrink-0 overflow-hidden" style="background: linear-gradient(135deg, #4F46E5, #7C3AED);">
                        <svg viewBox="0 0 40 40" class="w-6 h-6" fill="none"><rect x="20" y="8" width="7" height="18" rx="3.5" fill="white"/><path d="M20 22 Q20 30 13 30 Q10 30 9 27" stroke="white" stroke-width="3" stroke-linecap="round" fill="none"/><circle cx="13" cy="14" r="5" fill="#38BDF8"/></svg>
                    </span>
                    <div>
                        <p class="font-medium text-neutral-900 text-sm">Jeko</p>
                        <p class="text-xs text-neutral-500">Wave, Orange, MTN, Moov</p>
                    </div>
                </label>
                @endif
            </div>
            {{-- Sélecteur opérateur Jeko --}}
            <div x-show="gateway === 'jeko'" x-transition class="mt-3">
                <p class="text-xs text-neutral-500 mb-2">Choisissez votre opérateur :</p>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                    @foreach(['wave' => ['label' => 'Wave', 'logo' => 'wave.png'], 'orange' => ['label' => 'Orange Money', 'logo' => 'orange-money.png'], 'mtn' => ['label' => 'MTN MoMo', 'logo' => 'mtn-momo.png'], 'moov' => ['label' => 'Moov Money', 'logo' => 'moov-money.png']] as $op => $info)
                    <label class="flex flex-col items-center gap-2 p-3 rounded-xl border-2 cursor-pointer transition-all"
                           :class="jeko_operator === '{{ $op }}' ? 'border-purple-500 bg-purple-50' : 'border-neutral-200 hover:border-neutral-300'">
                        <input type="radio" x-model="jeko_operator" value="{{ $op }}" class="sr-only">
                        <img src="{{ asset('images/payments/' . $info['logo']) }}" alt="{{ $info['label'] }}" class="h-8 w-auto object-contain">
                        <span class="text-xs font-medium" :class="jeko_operator === '{{ $op }}' ? 'text-purple-700' : 'text-neutral-600'">{{ $info['label'] }}</span>
                    </label>
                    @endforeach
                </div>
            </div>
        </div>
        @endif

        {{-- Grille des plans --}}
        <div class="mb-4 p-3 rounded-xl bg-emerald-50 border border-emerald-200 text-xs text-emerald-800">
            <strong>Livraison incluse</strong> — commission de 12 % uniquement sur les commandes livrées (10 % avec Pro).
            <strong>Aucune commande n'est jamais bloquée.</strong>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-2 xl:grid-cols-4 gap-4 mb-8">
            @foreach($plans as $plan)
                @php
                    $isCurrentPlan = $currentPlan && $currentPlan->id === $plan->id && $isActive;
                    $canRenew = $isExpired || ($daysLeft !== null && $daysLeft <= 7);
                    $isDisabled = $isCurrentPlan && !$canRenew;
                    $lockedApplied = $quotes[$plan->slug]['monthly']['locked_price_applied'] ?? false;

                    $accent = match($plan->slug) {
                        'stand'     => ['ring' => '', 'btn' => 'bg-neutral-900 hover:bg-neutral-800 text-white', 'price' => 'text-neutral-900'],
                        'essentiel' => ['ring' => 'ring-2 ring-primary-400', 'btn' => 'bg-primary-500 hover:bg-primary-600 text-white', 'price' => 'text-primary-600'],
                        'pro'       => ['ring' => 'ring-2 ring-amber-400', 'btn' => 'bg-amber-500 hover:bg-amber-600 text-white', 'price' => 'text-amber-600'],
                        default     => ['ring' => '', 'btn' => 'bg-neutral-900 hover:bg-neutral-800 text-white', 'price' => 'text-neutral-900'],
                    };
                @endphp

                <div class="relative bg-white rounded-2xl border {{ $isCurrentPlan ? 'border-primary-400' : 'border-neutral-200' }} {{ $accent['ring'] }} p-5 flex flex-col transition-all hover:shadow-md">

                    {{-- Badge populaire --}}
                    @if($plan->is_featured)
                        <div class="absolute -top-3 left-1/2 -translate-x-1/2">
                            <span class="bg-primary-500 text-white text-[10px] font-bold px-3 py-1 rounded-full shadow-sm whitespace-nowrap">⭐ POPULAIRE</span>
                        </div>
                    @endif

                    @if($isCurrentPlan)
                        <div class="absolute -top-3 right-4">
                            <span class="bg-emerald-500 text-white text-[10px] font-bold px-3 py-1 rounded-full shadow-sm">Plan actuel</span>
                        </div>
                    @endif

                    {{-- Nom + description --}}
                    <div class="mb-4 {{ $plan->is_featured ? 'mt-2' : '' }}">
                        <h3 class="text-lg font-bold text-neutral-900">{{ $plan->name }}</h3>
                        <p class="text-xs text-neutral-500 mt-0.5 leading-relaxed">{{ $plan->description }}</p>
                    </div>

                    {{-- Prix (calculé côté serveur) --}}
                    <div class="mb-5">
                        @if($plan->is_on_quote)
                            <p class="text-xs text-neutral-500">Sur devis, à partir de</p>
                        @endif
                        <template x-if="quotes['{{ $plan->slug }}'][period]">
                            <div>
                                <div class="flex items-baseline gap-1">
                                    <span class="text-3xl font-bold {{ $accent['price'] }}"
                                          x-text="new Intl.NumberFormat('fr-FR').format(quotes['{{ $plan->slug }}'][period].final_price) + ' F'"></span>
                                    <span class="text-sm text-neutral-400" x-text="@js($periodSuffix)[period]"></span>
                                </div>
                                <p class="text-xs text-neutral-400 mt-0.5"
                                   x-show="period !== 'monthly' && period !== 'weekly'"
                                   x-text="'soit ' + new Intl.NumberFormat('fr-FR').format(quotes['{{ $plan->slug }}'][period].monthly_equivalent) + ' FCFA/mois'"></p>
                                <p class="text-xs text-emerald-600 font-semibold mt-0.5"
                                   x-show="quotes['{{ $plan->slug }}'][period].discount_amount > 0"
                                   x-text="'économie : ' + new Intl.NumberFormat('fr-FR').format(quotes['{{ $plan->slug }}'][period].discount_amount) + ' FCFA'"></p>
                            </div>
                        </template>
                        <template x-if="!quotes['{{ $plan->slug }}'][period]">
                            <p class="text-sm text-neutral-400">Non disponible à la semaine</p>
                        </template>
                        @if($lockedApplied)
                            <p class="text-xs text-emerald-700 font-medium mt-1">Votre tarif d'abonné est conservé</p>
                        @endif
                    </div>

                    {{-- Fonctionnalités --}}
                    <ul class="space-y-2 mb-6 flex-1 text-sm text-neutral-600">
                        @foreach($plan->features as $feature)
                        <li class="flex items-start gap-2">
                            <svg class="w-4 h-4 text-emerald-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            <span>{{ $feature }}</span>
                        </li>
                        @endforeach
                    </ul>

                    {{-- Bouton --}}
                    @if($plan->is_on_quote)
                        <a href="{{ route('contact') }}?type=gold"
                           class="block w-full py-2.5 rounded-xl text-sm font-semibold text-center transition-all {{ $accent['btn'] }} shadow-sm hover:shadow">
                            Demander un devis
                        </a>
                    @else
                    <form method="POST"
                          action="{{ $isTrial ? route('restaurant.subscription.convertTrial') : route('restaurant.subscription.change') }}">
                        @csrf
                        <input type="hidden" name="plan" value="{{ $plan->slug }}">
                        <input type="hidden" name="billing_period" x-bind:value="quotes['{{ $plan->slug }}'][period] ? period : 'monthly'">
                        <input type="hidden" name="payment_gateway" x-bind:value="gateway">
                        <input type="hidden" name="jeko_operator" x-bind:value="jeko_operator">
                        @if($isDisabled)
                            <button type="button" disabled
                                    class="w-full py-2.5 rounded-xl text-sm font-semibold bg-neutral-100 text-neutral-400 cursor-not-allowed">
                                Plan actuel
                            </button>
                        @else
                            <button type="submit"
                                    x-bind:disabled="!quotes['{{ $plan->slug }}'][period]"
                                    class="w-full py-2.5 rounded-xl text-sm font-semibold transition-all disabled:opacity-40 disabled:cursor-not-allowed {{ $accent['btn'] }} shadow-sm hover:shadow">
                                @if($isTrial || $isExpired)
                                    Activer ce plan
                                @elseif($isCurrentPlan && $canRenew)
                                    Renouveler
                                @else
                                    Choisir ce plan
                                @endif
                            </button>
                        @endif
                    </form>
                    @endif
                </div>
            @endforeach
        </div>

        {{-- Add-ons --}}
        @if($availableAddons)
        <div class="mb-8">
            <h3 class="text-base font-bold text-neutral-900 mb-1">Options supplémentaires</h3>
            <p class="text-sm text-neutral-500 mb-4">Ajoutez des fonctionnalités à votre plan.</p>
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                @foreach($availableAddons as $addonType => $addon)
                    <div class="flex items-start gap-3 p-4 rounded-2xl border border-neutral-200 bg-neutral-50">
                        <div class="w-8 h-8 rounded-lg bg-primary-100 flex items-center justify-center flex-shrink-0">
                            <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-semibold text-neutral-900">{{ $addon['name'] }}</p>
                            <p class="text-xs text-neutral-500 mt-0.5">{{ $addon['description'] }}</p>
                        </div>
                        <span class="text-sm font-bold text-primary-600 whitespace-nowrap">{{ number_format($addon['price'], 0, ',', ' ') }} F/mois</span>
                    </div>
                @endforeach
            </div>
            <p class="text-xs text-neutral-400 mt-3">Les add-ons sont sélectionnables lors du choix d'un plan. Contactez le support pour en ajouter à un abonnement existant.</p>
        </div>
        @endif

    </div>
    @endif

    {{-- ═══ UTILISATION ═══ --}}
    @php $usagePlan = $restaurant->effectivePlan(); /* pendant l'essai : limites Pro */ @endphp
    @if($usagePlan)
    <div class="mb-8">
        <h3 class="text-base font-bold text-neutral-900 mb-4">Utilisation actuelle</h3>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4">
            @php
                $orderQuota = (clone app(\App\Services\PlanLimiter::class))->forRestaurant($restaurant)->getQuotasSummary()['orders_this_month'];
                $usages = [
                    ['label' => 'Plats', 'count' => $restaurant->dishes()->count(), 'max' => $usagePlan->max_dishes ?? 9999, 'color' => 'primary', 'soft' => false],
                    ['label' => 'Catégories', 'count' => $restaurant->categories()->count(), 'max' => $usagePlan->max_categories ?? 9999, 'color' => 'secondary', 'soft' => false],
                    ['label' => 'Équipe', 'count' => $restaurant->users()->where('role', 'employee')->count(), 'max' => $usagePlan->max_employees ?? 9999, 'color' => 'amber', 'soft' => false],
                    ['label' => 'Commandes ce mois', 'count' => $orderQuota['current'], 'max' => $orderQuota['limit'] ?? 9999, 'color' => 'emerald', 'soft' => true],
                ];
            @endphp
            @foreach($usages as $u)
                @php
                    $pct = $u['max'] < 9999 ? min(100, ($u['count'] / max(1, $u['max'])) * 100) : 0;
                    $warn = $pct >= 90 && !$u['soft'];
                @endphp
                <div class="p-4 rounded-2xl border border-neutral-200 bg-white">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-sm font-medium text-neutral-600">{{ $u['label'] }}</span>
                        <span class="text-sm font-bold {{ $warn ? 'text-red-600' : 'text-neutral-900' }}">
                            {{ $u['count'] }} / {{ $u['max'] >= 9999 ? '∞' : $u['max'] }}
                        </span>
                    </div>
                    @if($u['max'] < 9999)
                    <div class="h-1.5 bg-neutral-100 rounded-full overflow-hidden">
                        <div class="h-full rounded-full transition-all {{ $warn ? 'bg-red-500' : 'bg-' . $u['color'] . '-500' }}"
                             style="width: {{ $pct }}%"></div>
                    </div>
                    @if($warn)
                        <p class="text-[10px] text-red-500 font-medium mt-1">Limite presque atteinte</p>
                    @endif
                    @endif
                    @if($u['soft'])
                        <p class="text-[10px] text-neutral-500 mt-1">Indicatif : aucune commande n'est jamais bloquée</p>
                    @endif
                </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- ═══ HISTORIQUE ═══ --}}
    @if($history->isNotEmpty())
    <div>
        <h3 class="text-base font-bold text-neutral-900 mb-4">Historique des paiements</h3>
        <div class="bg-white rounded-2xl border border-neutral-200 overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[500px]">
                    <thead class="bg-neutral-50 border-b border-neutral-100">
                        <tr>
                            @foreach(['Date', 'Plan', 'Période', 'Montant', 'Statut'] as $h)
                                <th class="px-4 py-3 text-left text-[11px] font-semibold text-neutral-500 uppercase tracking-wide">{{ $h }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-50">
                        @foreach($history as $sub)
                            @php
                                $statusMap = [
                                    'active'  => ['Actif',       'bg-emerald-100 text-emerald-700'],
                                    'trial'   => ['Essai',       'bg-blue-100 text-blue-700'],
                                    'pending' => ['En attente',  'bg-amber-100 text-amber-700'],
                                    'expired' => ['Expiré',      'bg-neutral-100 text-neutral-500'],
                                    'cancelled'=> ['Annulé',     'bg-red-100 text-red-600'],
                                ];
                                [$statusLabel, $statusClass] = $statusMap[$sub->status->value] ?? [$sub->status->value, 'bg-neutral-100 text-neutral-500'];
                                $periodLabels = ['weekly' => 'Hebdomadaire', 'monthly' => 'Mensuel', 'quarterly' => 'Trimestriel', 'semiannual' => 'Semestriel', 'annual' => 'Annuel'];
                            @endphp
                            <tr class="hover:bg-neutral-50 transition-colors">
                                <td class="px-4 py-3 text-sm text-neutral-600">{{ $sub->created_at->format('d M Y') }}</td>
                                <td class="px-4 py-3 text-sm font-medium text-neutral-900">{{ $sub->plan->name }}</td>
                                <td class="px-4 py-3 text-sm text-neutral-500">{{ $periodLabels[$sub->billing_period] ?? 'Mensuel' }}</td>
                                <td class="px-4 py-3 text-sm font-semibold text-neutral-900">
                                    {{ $sub->amount_paid > 0 ? number_format($sub->amount_paid, 0, ',', ' ') . ' F' : 'Gratuit' }}
                                    @if($sub->discount_percentage > 0)
                                        <span class="text-xs text-emerald-600 font-normal">(-{{ $sub->discount_percentage }}%)</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3">
                                    <div class="flex items-center gap-2">
                                        <span class="px-2 py-0.5 rounded-full text-xs font-semibold {{ $statusClass }}">{{ $statusLabel }}</span>
                                        @if($sub->status->value === 'pending')
                                            <form method="POST" action="{{ route('restaurant.subscription.retry', $sub) }}" class="inline">
                                                @csrf
                                                <button type="submit" class="text-xs text-primary-600 hover:underline font-medium">Payer</button>
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
    </div>
    @endif


</x-layouts.admin-restaurant>
