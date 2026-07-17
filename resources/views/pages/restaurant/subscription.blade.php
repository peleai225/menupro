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
        $trialDays      = $subscription?->trial_days ?? 7;
        $availableAddons = \App\Models\SubscriptionAddon::getAvailableAddons();
        $isStand        = ($restaurant->currentPlan?->slug ?? '') === 'stand';

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
                            Expire le <strong>{{ $subscription->ends_at->locale('fr')->isoFormat('D MMMM YYYY') }}</strong> —
                            accès complet à toutes les fonctionnalités.
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
    {{-- Récapitulatif simplifié pour le plan Stand --}}
    <div class="mb-8">
        <h2 class="text-xl font-bold text-neutral-900 mb-4">Votre plan</h2>
        <div class="p-6 rounded-2xl border-2 border-primary-200 bg-gradient-to-br from-primary-50 to-white">
            <div class="flex items-center justify-between mb-4">
                <div>
                    <h3 class="text-lg font-bold text-neutral-900">Plan Stand</h3>
                    <p class="text-sm text-neutral-500">Pour les stands, kiosques et micro-commerces</p>
                </div>
                <div class="text-right">
                    <span class="text-2xl font-bold text-primary-600">5 000 F</span>
                    <span class="text-sm text-neutral-500">/mois</span>
                </div>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-3 mt-4">
                <div class="flex items-center gap-2 text-sm text-neutral-700">
                    <svg class="w-4 h-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    15 plats max
                </div>
                <div class="flex items-center gap-2 text-sm text-neutral-700">
                    <svg class="w-4 h-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    5 catégories
                </div>
                <div class="flex items-center gap-2 text-sm text-neutral-700">
                    <svg class="w-4 h-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    100 commandes/mois
                </div>
                <div class="flex items-center gap-2 text-sm text-neutral-700">
                    <svg class="w-4 h-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    QR Code inclus
                </div>
                <div class="flex items-center gap-2 text-sm text-neutral-700">
                    <svg class="w-4 h-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    Commandes WhatsApp
                </div>
                <div class="flex items-center gap-2 text-sm text-neutral-700">
                    <svg class="w-4 h-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    1 compte
                </div>
            </div>
            @if(!$isActive || $daysLeft <= 7)
            <div class="mt-5 pt-4 border-t border-neutral-200">
                <form method="POST" action="{{ $isTrial ? route('restaurant.subscription.convertTrial') : route('restaurant.subscription.change') }}">
                    @csrf
                    <input type="hidden" name="plan" value="stand">
                    <input type="hidden" name="billing_period" value="monthly">
                    <button type="submit" class="w-full sm:w-auto px-6 py-3 rounded-xl text-sm font-bold text-white bg-primary-600 hover:bg-primary-700 shadow-sm hover:shadow transition-all">
                        @if($isTrial)
                            Activer mon plan — 5 000 F/mois
                        @elseif($isActive && $daysLeft <= 7)
                            Renouveler — 5 000 F/mois
                        @else
                            Réactiver mon plan — 5 000 F/mois
                        @endif
                    </button>
                </form>
            </div>
            @endif
            <div class="mt-4 pt-4 {{ !$isActive ? '' : 'border-t border-neutral-200' }}">
                <p class="text-sm text-neutral-600">Besoin de plus ? Passez au plan <strong>Essentiel</strong> pour débloquer la livraison, les statistiques et plus encore.</p>
                <a href="{{ route('restaurant.subscription.plans') }}" class="inline-flex items-center gap-1 text-sm font-semibold text-primary-600 hover:text-primary-700 mt-2">
                    Voir les plans supérieurs
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>
    </div>
    @else
    <div x-data="{ period: 'monthly' }">

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
                @foreach(['monthly' => ['1 mois', null], 'quarterly' => ['3 mois', '-10%'], 'semiannual' => ['6 mois', '-15%'], 'annual' => ['1 an', '-20%']] as $p => [$label, $badge])
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

        {{-- Grille des plans --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-8">
            @foreach($plans as $plan)
                @php
                    $isCurrentPlan = $currentPlan && $currentPlan->id === $plan->id && $isActive;
                    $canRenew = $isExpired || ($daysLeft !== null && $daysLeft <= 7);
                    $isDisabled = $isCurrentPlan && !$canRenew;

                    $accent = match($plan->slug) {
                        'essentiel' => ['ring' => '', 'btn' => 'bg-neutral-900 hover:bg-neutral-800 text-white', 'price' => 'text-neutral-900'],
                        'pro'       => ['ring' => 'ring-2 ring-primary-400', 'btn' => 'bg-primary-500 hover:bg-primary-600 text-white', 'price' => 'text-primary-600'],
                        'business'  => ['ring' => 'ring-2 ring-amber-400', 'btn' => 'bg-amber-500 hover:bg-amber-600 text-white', 'price' => 'text-amber-600'],
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

                    {{-- Prix dynamique --}}
                    <div class="mb-5">
                        <div class="flex items-baseline gap-1">
                            <span class="text-3xl font-bold {{ $accent['price'] }}"
                                  x-text="calcPrice({{ (int)$plan->price }}, period)"></span>
                            <span class="text-sm text-neutral-400" x-text="periodSuffix(period)"></span>
                        </div>
                        <p class="text-xs text-neutral-400 mt-0.5"
                           x-show="period !== 'monthly'"
                           x-text="'soit ' + calcMonthly({{ (int)$plan->price }}, period) + ' FCFA/mois'"></p>
                        <p class="text-xs text-emerald-600 font-semibold mt-0.5"
                           x-show="period !== 'monthly'"
                           x-text="'économie : ' + calcSaving({{ (int)$plan->price }}, period) + ' FCFA'"></p>
                    </div>

                    {{-- Fonctionnalités --}}
                    <ul class="space-y-2 mb-6 flex-1 text-sm text-neutral-600">
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            <span>{{ $plan->max_dishes >= 9999 ? 'Plats illimités' : $plan->max_dishes . ' plats' }}</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            <span>{{ $plan->max_employees }} compte{{ $plan->max_employees > 1 ? 's' : '' }} employé</span>
                        </li>
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            <span>{{ $plan->max_orders_per_month >= 9999 ? 'Commandes illimitées' : number_format($plan->max_orders_per_month, 0, ',', ' ') . ' cmd/mois' }}</span>
                        </li>
                        @if($plan->has_delivery)
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            <span>Gestion de la livraison</span>
                        </li>
                        @endif
                        @if($plan->has_stock_management)
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            <span>Gestion du stock</span>
                        </li>
                        @endif
                        @if($plan->has_analytics)
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            <span>Statistiques avancées</span>
                        </li>
                        @endif
                        @foreach($plan->features ?? [] as $feature)
                        <li class="flex items-center gap-2">
                            <svg class="w-4 h-4 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            <span>{{ $feature }}</span>
                        </li>
                        @endforeach
                    </ul>

                    {{-- Bouton --}}
                    <form method="POST"
                          action="{{ $isTrial ? route('restaurant.subscription.convertTrial') : route('restaurant.subscription.change') }}">
                        @csrf
                        <input type="hidden" name="plan" value="{{ $plan->slug }}">
                        <input type="hidden" name="billing_period" x-bind:value="period">
                        @if($isDisabled)
                            <button type="button" disabled
                                    class="w-full py-2.5 rounded-xl text-sm font-semibold bg-neutral-100 text-neutral-400 cursor-not-allowed">
                                Plan actuel
                            </button>
                        @else
                            <button type="submit"
                                    class="w-full py-2.5 rounded-xl text-sm font-semibold transition-all {{ $accent['btn'] }} shadow-sm hover:shadow">
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
    @if($currentPlan)
    <div class="mb-8">
        <h3 class="text-base font-bold text-neutral-900 mb-4">Utilisation actuelle</h3>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            @php
                $usages = [
                    ['label' => 'Plats', 'count' => $restaurant->dishes()->count(), 'max' => $currentPlan->max_dishes, 'color' => 'primary'],
                    ['label' => 'Catégories', 'count' => $restaurant->categories()->count(), 'max' => $currentPlan->max_categories ?? 999, 'color' => 'secondary'],
                    ['label' => 'Équipe', 'count' => $restaurant->users()->where('role', 'employee')->count(), 'max' => $currentPlan->max_employees, 'color' => 'amber'],
                ];
            @endphp
            @foreach($usages as $u)
                @php
                    $pct = $u['max'] < 9999 ? min(100, ($u['count'] / max(1, $u['max'])) * 100) : 0;
                    $warn = $pct >= 90;
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
                                $periodLabels = ['monthly' => 'Mensuel', 'quarterly' => 'Trimestriel', 'semiannual' => 'Semestriel', 'annual' => 'Annuel'];
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

    @push('scripts')
    <script>
        function calcPrice(price, period) {
            const m = {monthly:1, quarterly:3, semiannual:6, annual:12}[period] || 1;
            const d = {monthly:0, quarterly:10, semiannual:15, annual:20}[period] || 0;
            const final = Math.round(price * m * (1 - d/100) / 100) * 100;
            return new Intl.NumberFormat('fr-FR').format(final) + ' F';
        }
        function calcMonthly(price, period) {
            const m = {monthly:1, quarterly:3, semiannual:6, annual:12}[period] || 1;
            const d = {monthly:0, quarterly:10, semiannual:15, annual:20}[period] || 0;
            const final = Math.round(price * m * (1 - d/100) / 100) * 100;
            return new Intl.NumberFormat('fr-FR').format(Math.round(final / m));
        }
        function calcSaving(price, period) {
            const m = {monthly:1, quarterly:3, semiannual:6, annual:12}[period] || 1;
            const d = {monthly:0, quarterly:10, semiannual:15, annual:20}[period] || 0;
            const saving = Math.round(price * m * (d/100) / 100) * 100;
            return new Intl.NumberFormat('fr-FR').format(saving);
        }
        function periodSuffix(period) {
            return {monthly:'/mois', quarterly:'/trimestre', semiannual:'/semestre', annual:'/an'}[period] || '/mois';
        }
    </script>
    @endpush

</x-layouts.admin-restaurant>
