@use('App\Support\VitrineGrid', 'G')
@php
    // Types de commerce : fournis par RegisterController ($businessTypes, groupes restaurant|stand|shop,
    // lus dans config/business_types.php) ; repli équivalent si la variable est absente.
    $businessTypes = $businessTypes ?? null;
    $businessTypes = is_array($businessTypes) && $businessTypes ? $businessTypes : [
        'restaurant' => ['restaurant' => 'Restaurant', 'bar' => 'Bar', 'brasserie' => 'Brasserie', 'maquis' => 'Maquis', 'traiteur' => 'Traiteur', 'cafe' => 'Café', 'food_truck' => 'Food Truck', 'brunch' => 'Brunch', 'evenementiel' => 'Événementiel'],
        'stand'      => ['stand' => 'Stand de rue', 'kiosque' => 'Kiosque', 'cantine' => 'Cantine / Cafétéria', 'patisserie' => 'Pâtisserie / Boulangerie', 'epicerie' => 'Épicerie', 'jus' => 'Jus & Boissons', 'snack' => 'Snack rapide'],
        'shop'       => ['boutique' => 'Boutique / Superette'],
    ];
    // Groupe de types affiché pour chaque carte d'offre
    $offerGroups = ['restaurant' => 'restaurant', 'stand' => 'stand', 'boutique' => 'shop'];

    // Type présélectionné : $preselectedType (contrôleur, via ?type=…) ; repli sur ?type=boutique / ?type=stand.
    $requestedType = request()->query('type');
    $preselectedType = $preselectedType ?? (in_array($requestedType, ['boutique', 'stand'], true) ? $requestedType : null);
    $preselectedGroup = collect($businessTypes)->search(fn ($types) => array_key_exists((string) $preselectedType, $types));
    $offer = $isStand ? 'stand' : (array_search($preselectedGroup, $offerGroups, true) ?: 'restaurant');
    $offerCards = collect(config('vitrine.offers', []))->map(function ($o, $key) {
        $query = request()->except(['type']);
        if (!empty($o['register_type'])) {
            $query['type'] = $o['register_type'];
        }
        return [
            'key'   => $key,
            'label' => $o['label'],
            'hint'  => ['restaurant' => 'Bar, brasserie, traiteur, café…', 'stand' => 'Garba, jus, kiosque, cantine…', 'boutique' => 'Superette, boutique de quartier…'][$key] ?? '',
            'url'   => route('register', $query),
            'from'  => G::offerFrom($key),
            'stand' => $key === 'stand',
        ];
    })->values();
    $defaultForOffer = ['restaurant' => '', 'stand' => 'stand', 'boutique' => 'boutique'][$offer];
    $initialRestaurantType = old('restaurant_type', array_key_exists((string) $preselectedType, $businessTypes[$offerGroups[$offer]] ?? []) ? $preselectedType : $defaultForOffer);
    $check = '<svg class="w-5 h-5 shrink-0 text-mp-ink" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>';
@endphp
<x-layouts.auth title="Inscription">
    <div x-data="registerForm()">
        {{-- ═══ Type de commerce (avant tout le reste) ═══ --}}
        <fieldset x-show="step === 1" class="mb-8">
            <legend class="vt-h2 mb-2">Quel est votre commerce ?</legend>
            <p class="vt-muted mb-5">Choisissez votre offre. Vous pourrez changer de plan plus tard.</p>
            <div class="grid gap-3">
                @foreach($offerCards as $card)
                    <a href="{{ $card['url'] }}"
                       :aria-current="offer === '{{ $card['key'] }}' ? 'true' : 'false'"
                       aria-current="{{ $offer === $card['key'] ? 'true' : 'false' }}"
                       :class="offer === '{{ $card['key'] }}' ? 'border-mp-black bg-mp-orange' : 'border-mp-line bg-white hover:border-mp-black'"
                       class="flex items-center gap-4 rounded-2xl border-2 p-4 min-h-[4.5rem]">
                        <span class="flex-none w-6 h-6 rounded-full border-2 border-mp-black flex items-center justify-center bg-white" aria-hidden="true">
                            <span class="w-3 h-3 rounded-full bg-mp-black" x-show="offer === '{{ $card['key'] }}'" @if($offer !== $card['key']) style="display:none" @endif></span>
                        </span>
                        <span class="flex-1 min-w-0">
                            <span class="flex flex-wrap items-center gap-2">
                                <span class="font-bold text-lg">{{ $card['label'] }}</span>
                                @if($card['key'] === 'boutique')<span class="vt-pill bg-mp-black text-white !text-sm !py-0.5">Nouveau</span>@endif
                            </span>
                            <span class="block text-[0.9375rem]" :class="offer === '{{ $card['key'] }}' ? 'text-mp-black' : 'text-mp-text'">{{ $card['hint'] }}</span>
                        </span>
                        <span class="text-right whitespace-nowrap">
                            <span class="block text-sm">dès</span>
                            <span class="font-bold vt-num">{{ $card['from']['amount'] }} F</span>
                            <span class="block text-sm">{{ $card['from']['unit'] === 'F/semaine' ? '/semaine' : '/mois' }}</span>
                        </span>
                    </a>
                @endforeach
            </div>
        </fieldset>

        {{-- ═══ Étapes ═══ --}}
        <nav aria-label="Étapes de l'inscription" class="mb-8">
            <p class="font-semibold">Étape <span x-text="step">1</span> sur 3 : <span x-text="['Votre compte', offerTitle, 'Votre abonnement'][step - 1]">Votre compte</span></p>
            <ol class="mt-3 grid grid-cols-3 gap-2">
                @foreach([1 => 'Compte', 2 => 'Commerce', 3 => 'Abonnement'] as $n => $label)
                    <li>
                        <button type="button" @click="goToStep({{ $n }})" :disabled="step < {{ $n }}"
                                class="w-full text-left disabled:cursor-default" :aria-current="step === {{ $n }} ? 'step' : 'false'">
                            <span class="block h-2 rounded-full" :class="step >= {{ $n }} ? 'bg-mp-orange' : 'bg-mp-line'"></span>
                            <span class="block mt-1.5 text-[0.9375rem]" :class="step >= {{ $n }} ? 'font-bold text-mp-black' : 'text-mp-text'">{{ $label }}</span>
                        </button>
                    </li>
                @endforeach
            </ol>
        </nav>

        {{-- Parrainage (agent Commando ou représentant) --}}
        @if(session('register_ref_agent'))
            @php $refAgent = \App\Models\CommandoAgent::where('uuid', session('register_ref_agent'))->first(); @endphp
            @if($refAgent)
                <div class="mb-6 p-4 rounded-2xl border-2 border-mp-line bg-mp-paper">
                    <p class="font-semibold">Parrainé par {{ $refAgent->full_name }}</p>
                    <p class="vt-muted">Agent MenuPro, {{ $refAgent->city }}</p>
                </div>
            @endif
        @endif

        @if ($errors->any())
            <div class="mb-6 p-4 rounded-2xl border-2 border-[#B91C1C] bg-[#FEF2F2] text-[#7F1D1D]" role="alert" data-register-errors>
                <h2 class="font-bold">Corrigez les points suivants</h2>
                <ul class="mt-1 list-disc pl-5 space-y-0.5">
                    @foreach ($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('register.post') }}" enctype="multipart/form-data" @submit.prevent="submitForm()" id="register-form">
            @csrf

            {{-- ═══════════ ÉTAPE 1 — Compte ═══════════ --}}
            <section x-show="step === 1" aria-labelledby="step1-title">
                <h1 id="step1-title" class="vt-h3 text-2xl mb-6">Créez votre compte</h1>

                <div class="space-y-6">
                    <div>
                        <label for="name" class="vt-label">Nom complet <span class="text-[#B91C1C]" aria-hidden="true">*</span></label>
                        <input type="text" id="name" name="name" x-model="formData.name" placeholder="Jean Kouassi" required autocomplete="name" class="vt-input">
                    </div>

                    <div>
                        <label for="phone" class="vt-label">Numéro WhatsApp <span class="text-[#B91C1C]" aria-hidden="true">*</span></label>
                        <input type="tel" id="phone" name="phone" x-model="formData.phone" placeholder="07 00 00 00 00" required autocomplete="tel" inputmode="tel" aria-describedby="phone-hint" class="vt-input">
                        <p id="phone-hint" class="mt-2 vt-hint">Il vous servira à vous connecter.</p>
                    </div>

                    <div>
                        <label for="email" class="vt-label">Adresse e-mail <span class="vt-hint">(facultatif)</span></label>
                        <input type="email" id="email" name="email" x-model="formData.email" placeholder="vous@exemple.com" autocomplete="email" autocapitalize="none" class="vt-input">
                    </div>

                    <div>
                        <label for="reg-password" class="vt-label">Mot de passe <span class="text-[#B91C1C]" aria-hidden="true">*</span></label>
                        <div class="relative">
                            <input :type="showPassword ? 'text' : 'password'" type="password" id="reg-password" name="password" x-model="formData.password"
                                   required minlength="8" autocomplete="new-password" aria-describedby="password-hint" class="vt-input !pr-14">
                            <button type="button" @click="showPassword = !showPassword"
                                    class="absolute right-1 top-1/2 -translate-y-1/2 w-12 h-12 flex items-center justify-center rounded-xl text-mp-text hover:text-mp-black"
                                    :aria-label="showPassword ? 'Masquer le mot de passe' : 'Afficher le mot de passe'" aria-label="Afficher le mot de passe">
                                <svg x-show="!showPassword" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg x-show="showPassword" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                            </button>
                        </div>
                        <div class="mt-3 flex gap-1.5" aria-hidden="true">
                            <div class="h-2 flex-1 rounded-full" :class="formData.password.length >= 1 ? (formData.password.length >= 8 ? 'bg-[#15803D]' : 'bg-mp-orange') : 'bg-mp-line'"></div>
                            <div class="h-2 flex-1 rounded-full" :class="formData.password.length >= 4 ? (formData.password.length >= 8 ? 'bg-[#15803D]' : 'bg-mp-orange') : 'bg-mp-line'"></div>
                            <div class="h-2 flex-1 rounded-full" :class="formData.password.length >= 8 ? 'bg-[#15803D]' : 'bg-mp-line'"></div>
                            <div class="h-2 flex-1 rounded-full" :class="formData.password.length >= 12 ? 'bg-[#15803D]' : 'bg-mp-line'"></div>
                        </div>
                        <p id="password-hint" class="mt-2 vt-hint">8 caractères minimum.</p>
                    </div>
                </div>

                <p x-show="stepError" x-cloak x-text="stepError" class="mt-6 font-semibold text-[#B91C1C]" role="alert"></p>

                <button type="button" @click="next(1)" class="vt-btn vt-btn-primary vt-btn-block mt-8">Continuer</button>

                <p class="mt-6 text-center">Déjà inscrit ? <a href="{{ route('login') }}" class="vt-link">Se connecter</a></p>
            </section>

            {{-- ═══════════ ÉTAPE 2 — Commerce ═══════════ --}}
            <section x-show="step === 2" x-cloak aria-labelledby="step2-title">
                <h1 id="step2-title" class="vt-h3 text-2xl" x-text="offerTitle">Votre commerce</h1>
                <p class="mt-1 mb-6 vt-muted">Vous pourrez tout modifier après.</p>

                <div class="space-y-6">
                    <div>
                        <label for="restaurant_name" class="vt-label">Nom du commerce <span class="text-[#B91C1C]" aria-hidden="true">*</span></label>
                        <input type="text" id="restaurant_name" name="restaurant_name" x-model="formData.restaurant_name" placeholder="Ex. Maquis du Carrefour" required class="vt-input">
                    </div>

                    <div>
                        <label for="restaurant_type" class="vt-label">Type <span class="text-[#B91C1C]" aria-hidden="true">*</span></label>
                        <div class="relative">
                            <select id="restaurant_type" name="restaurant_type" x-model="formData.restaurant_type" required class="vt-input appearance-none !pr-12 cursor-pointer">
                                <option value="">Choisissez</option>
                                @foreach($offerGroups as $offerKey => $group)
                                    <template x-if="offer === '{{ $offerKey }}'">
                                        <optgroup label="{{ config("vitrine.offers.{$offerKey}.label") }}">
                                            @foreach($businessTypes[$group] ?? [] as $val => $label)
                                                <option value="{{ $val }}">{{ $label }}</option>
                                            @endforeach
                                        </optgroup>
                                    </template>
                                @endforeach
                            </select>
                            <svg class="absolute right-4 top-1/2 -translate-y-1/2 w-5 h-5 pointer-events-none" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>

                    <div>
                        <label for="company_name" class="vt-label">Entreprise <span class="vt-hint">(facultatif)</span></label>
                        <input type="text" id="company_name" name="company_name" x-model="formData.company_name" placeholder="SARL …" class="vt-input">
                    </div>

                    <details class="rounded-2xl border-2 border-mp-line">
                        <summary class="flex items-center min-h-12 px-4 cursor-pointer font-semibold">Ajouter un RCCM (badge « vérifié »)</summary>
                        <div class="px-4 pb-4 space-y-4">
                            <div>
                                <label for="rccm" class="vt-label">Numéro RCCM</label>
                                <input type="text" id="rccm" name="rccm" x-model="formData.rccm" placeholder="CI-DAL-XX-2024-XXXXX" class="vt-input">
                            </div>
                            <div>
                                <label for="rccm_document" class="vt-label">Extrait RCCM</label>
                                <input type="file" id="rccm_document" name="rccm_document" accept=".pdf,.jpg,.jpeg,.png" aria-describedby="rccm-hint"
                                       class="block w-full text-base file:mr-3 file:min-h-11 file:px-4 file:rounded-xl file:border-0 file:font-bold file:bg-mp-black file:text-white file:cursor-pointer">
                                <p id="rccm-hint" class="mt-1 vt-hint">PDF, JPEG ou PNG, 5 Mo maximum.</p>
                            </div>
                        </div>
                    </details>

                    <fieldset>
                        <legend class="vt-label">Images <span class="vt-hint">(facultatif)</span></legend>
                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label for="logo-upload" class="relative aspect-square rounded-2xl border-2 border-dashed border-[#A89F96] bg-mp-paper flex flex-col items-center justify-center overflow-hidden cursor-pointer hover:border-mp-black">
                                    <template x-if="logoPreview"><img :src="logoPreview" alt="Aperçu du logo" class="absolute inset-0 w-full h-full object-cover"></template>
                                    <span x-show="!logoPreview" class="font-semibold">Logo</span>
                                    <span x-show="!logoPreview" class="vt-hint">Carré</span>
                                </label>
                                <input type="file" name="logo" id="logo-upload" accept="image/png,image/jpeg,image/webp" class="sr-only"
                                       @change="logoPreview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null">
                            </div>
                            <div>
                                <label for="banner-upload" class="relative aspect-square rounded-2xl border-2 border-dashed border-[#A89F96] bg-mp-paper flex flex-col items-center justify-center overflow-hidden cursor-pointer hover:border-mp-black">
                                    <template x-if="bannerPreview"><img :src="bannerPreview" alt="Aperçu de la bannière" class="absolute inset-0 w-full h-full object-cover"></template>
                                    <span x-show="!bannerPreview" class="font-semibold">Bannière</span>
                                    <span x-show="!bannerPreview" class="vt-hint">Paysage</span>
                                </label>
                                <input type="file" name="banner" id="banner-upload" accept="image/png,image/jpeg,image/webp" class="sr-only"
                                       @change="bannerPreview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null">
                            </div>
                        </div>
                    </fieldset>
                </div>

                <p x-show="stepError" x-cloak x-text="stepError" class="mt-6 font-semibold text-[#B91C1C]" role="alert"></p>

                <div class="flex gap-3 mt-8">
                    <button type="button" @click="goToStep(1)" class="vt-btn vt-btn-ghost !px-4" aria-label="Revenir à l'étape précédente">Retour</button>
                    <button type="button" @click="next(2)" class="vt-btn vt-btn-primary flex-1">Continuer</button>
                </div>
            </section>

            {{-- ═══════════ ÉTAPE 3 — Abonnement ═══════════ --}}
            <section x-show="step === 3" x-cloak aria-labelledby="step3-title"
                     x-data="pricingCalculator()"
                     x-init="init(); formData.plan = '{{ $plan->slug ?? 'essentiel' }}';">
                <h1 id="step3-title" class="vt-h3 text-2xl">Choisissez votre plan</h1>
                <p class="mt-1 mb-6 vt-muted">{{ config('plans.trial_days') }} jours d'essai gratuit avec toutes les fonctions du plan Pro, sans engagement. Ensuite, vous payez votre plan en Mobile Money.</p>

                <input type="hidden" name="plan" x-model="formData.plan">
                <input type="hidden" name="billing_period" :value="billingCycle">

                {{-- Stand : plan unique --}}
                @if($isStand)
                    <div class="mb-6 p-5 rounded-2xl border-2 border-mp-black bg-mp-paper">
                        <div class="flex flex-wrap items-baseline justify-between gap-2">
                            <p class="font-bold text-xl">Plan Stand</p>
                            <p class="text-right">
                                <span class="font-bold text-xl vt-num whitespace-nowrap">{{ number_format($plan->price, 0, ',', ' ') }} F/mois</span>
                                @if($plan->supportsWeeklyBilling())
                                    <span class="block vt-muted whitespace-nowrap">ou {{ number_format($plan->weekly_price, 0, ',', ' ') }} F/semaine</span>
                                @endif
                            </p>
                        </div>
                        <ul class="mt-4 grid gap-2">
                            @foreach(array_merge($plan->features, ['QR Code + Mobile Money', config('plans.trial_days') . ' jours gratuits (fonctions Pro)']) as $f)
                                <li class="flex items-start gap-2">{!! $check !!}<span>{{ $f }}</span></li>
                            @endforeach
                        </ul>
                        @include('partials.plan-pack', ['slug' => $plan->slug, 'compact' => true])
                    </div>
                @endif

                {{-- Restaurant / Boutique : choix du plan --}}
                @if(!$isStand && isset($availablePlans) && $availablePlans->count() > 1)
                    <fieldset class="mb-6">
                        <legend class="sr-only">Plan</legend>
                        <div class="space-y-3">
                            @foreach($availablePlans as $p)
                                <button type="button"
                                        @click="formData.plan = '{{ $p->slug }}'; basePriceMonthly = {{ (int) $p->price }}; currentPlanName = '{{ $p->name }}';"
                                        :aria-pressed="(formData.plan === '{{ $p->slug }}').toString()"
                                        :class="formData.plan === '{{ $p->slug }}' ? 'border-mp-black bg-mp-paper' : 'border-mp-line hover:border-mp-black'"
                                        class="w-full p-4 rounded-2xl border-2 text-left flex items-center gap-4">
                                    <span class="flex-none w-6 h-6 rounded-full border-2 border-mp-black flex items-center justify-center bg-white" aria-hidden="true">
                                        <span class="w-3 h-3 rounded-full bg-mp-black" x-show="formData.plan === '{{ $p->slug }}'"></span>
                                    </span>
                                    <span class="flex-1 min-w-0">
                                        <span class="flex flex-wrap items-center gap-2">
                                            <span class="font-bold text-lg">{{ $p->name }}</span>
                                            @if($p->is_featured)<span class="vt-pill bg-mp-orange !text-sm !py-0.5">Le plus choisi</span>@endif
                                        </span>
                                        <span class="block text-[0.9375rem] vt-muted">
                                            {{ $p->max_dishes >= 9999 ? 'Produits illimités' : $p->max_dishes . ' produits' }},
                                            {{ \App\Models\Plan::isUnlimitedValue($p->max_employees) ? 'employés illimités' : $p->max_employees . ' employé' . ($p->max_employees > 1 ? 's' : '') }}@if($p->has_delivery), livraison incluse @endif
                                        </span>
                                    </span>
                                    <span class="text-right whitespace-nowrap">
                                        @if($p->is_on_quote)<span class="block text-sm">Sur devis, dès</span>@endif
                                        <span class="font-bold vt-num">{{ number_format($p->price, 0, ',', ' ') }} F</span>
                                        <span class="block text-sm">/mois</span>
                                    </span>
                                </button>
                            @endforeach
                        </div>
                        <p class="mt-3 vt-hint">Commandes jamais bloquées, quel que soit le plan.</p>
                    </fieldset>
                @endif

                @if(!$isStand)
                    <fieldset class="mb-6">
                        <legend class="vt-label">Durée de paiement</legend>
                        <div class="grid grid-cols-2 gap-2">
                            @foreach([['id' => 'monthly', 'label' => '1 mois', 'badge' => null], ['id' => 'quarterly', 'label' => '3 mois', 'badge' => '-10 %'], ['id' => 'semiannual', 'label' => '6 mois', 'badge' => '-15 %'], ['id' => 'annual', 'label' => '1 an', 'badge' => '-20 %']] as $cycle)
                                <button type="button" @click="billingCycle = '{{ $cycle['id'] }}'" :aria-pressed="(billingCycle === '{{ $cycle['id'] }}').toString()"
                                        :class="billingCycle === '{{ $cycle['id'] }}' ? 'bg-mp-black text-white border-mp-black' : 'bg-white border-mp-line hover:border-mp-black'"
                                        class="min-h-12 px-3 rounded-xl border-2 font-bold text-base">
                                    {{ $cycle['label'] }}@if($cycle['badge']) <span :class="billingCycle === '{{ $cycle['id'] }}' ? 'text-mp-orange' : 'text-mp-ink'">{{ $cycle['badge'] }}</span>@endif
                                </button>
                            @endforeach
                        </div>
                    </fieldset>

                    <div class="mb-6 p-4 rounded-2xl border-2 border-mp-line">
                        <p class="font-bold">Inclus</p>
                        @foreach($availablePlans as $p)
                            <ul x-show="formData.plan === '{{ $p->slug }}'" x-cloak class="mt-2 grid gap-2 sm:grid-cols-2">
                                @foreach($p->features as $f)
                                    <li class="flex items-start gap-2">{!! $check !!}<span>{{ $f }}</span></li>
                                @endforeach
                                <li class="flex items-start gap-2">{!! $check !!}<span>QR codes + Mobile Money</span></li>
                            </ul>
                            <div x-show="formData.plan === '{{ $p->slug }}'" x-cloak>@include('partials.plan-pack', ['slug' => $p->slug, 'compact' => true])</div>
                        @endforeach
                    </div>
                @endif

                {{-- Supports offerts à tous dès l'inscription --}}
                <p class="mb-6 p-4 rounded-2xl bg-mp-paper font-medium" data-welcome-pack>{{ config('plans.welcome_pack') }}</p>

                @if($isStand)
                    @if($plan->supportsWeeklyBilling())
                        <fieldset class="mb-4">
                            <legend class="vt-label">Après l'essai, je paierai</legend>
                            <div class="grid grid-cols-2 gap-2">
                                @foreach(['weekly' => 'À la semaine', 'monthly' => 'Au mois'] as $cycleId => $cycleLabel)
                                    <button type="button" @click="billingCycle = '{{ $cycleId }}'" :aria-pressed="(billingCycle === '{{ $cycleId }}').toString()"
                                            :class="billingCycle === '{{ $cycleId }}' ? 'bg-mp-black text-white border-mp-black' : 'bg-white border-mp-line hover:border-mp-black'"
                                            class="min-h-12 px-3 rounded-xl border-2 font-bold text-base">{{ $cycleLabel }}</button>
                                @endforeach
                            </div>
                        </fieldset>
                    @endif
                    <div class="p-5 rounded-2xl bg-mp-black text-white mb-6">
                        <p class="text-neutral-200" x-text="'Plan Stand, ' + (billingCycle === 'weekly' ? '1 semaine' : '1 mois')">Plan Stand</p>
                        <p class="mt-1"><span class="text-3xl font-bold vt-num" x-text="billingCycle === 'weekly' ? '{{ number_format((int) $plan->weekly_price, 0, ',', ' ') }}' : '{{ number_format($plan->price, 0, ',', ' ') }}'"></span> <span class="text-neutral-200">FCFA</span></p>
                        <p class="mt-1 text-neutral-200">{{ config('plans.trial_days') }} jours gratuits. Livraison incluse ({{ G::commission('stand') }} uniquement sur les commandes livrées).</p>
                    </div>
                @else
                    <div class="p-5 rounded-2xl bg-mp-black text-white mb-6">
                        <p class="text-neutral-200" x-text="currentPlanName + ', ' + (billingCycle === 'monthly' ? '1 mois' : billingCycle === 'quarterly' ? '3 mois' : billingCycle === 'semiannual' ? '6 mois' : '12 mois')"></p>
                        <p class="mt-1"><span class="text-3xl font-bold vt-num" x-text="formatPrice(totalPrice)"></span> <span class="text-neutral-200">FCFA</span></p>
                        <p class="mt-1 text-neutral-200" x-text="'Soit ' + formatPrice(totalPrice / getMonths()) + ' FCFA/mois'"></p>
                        <p x-show="billingCycle !== 'monthly'" class="mt-1 font-semibold text-mp-orange" x-text="formatPrice(discountAmount) + ' F économisés'"></p>
                    </div>
                @endif

                <label class="flex items-start gap-3 mb-6 cursor-pointer">
                    <input type="checkbox" name="terms" required class="mt-0.5 w-6 h-6 rounded-md border-2 border-[#857C74] text-mp-black focus:ring-mp-orange cursor-pointer">
                    <span>J'accepte les <a href="{{ route('terms') }}" target="_blank" class="vt-link">conditions</a> et la <a href="{{ route('privacy') }}" target="_blank" class="vt-link">politique de confidentialité</a>.</span>
                </label>

                <p x-show="submitError" x-cloak x-text="submitError" class="mb-4 font-semibold text-[#B91C1C]" role="alert"></p>

                <div class="flex gap-3">
                    <button type="button" @click="goToStep(2)" class="vt-btn vt-btn-ghost !px-4" aria-label="Revenir à l'étape précédente">Retour</button>
                    <button type="submit" class="vt-btn vt-btn-primary flex-1 relative" :disabled="loading || submitted" :aria-busy="loading.toString()">
                        <span :class="{ 'opacity-0': loading }">Démarrer l'essai gratuit</span>
                        <span x-show="loading" x-cloak class="absolute inset-0 flex items-center justify-center" aria-hidden="true">
                            <svg class="animate-spin h-6 w-6" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        </span>
                    </button>
                </div>

                <p class="mt-4 text-center vt-hint">{{ config('plans.trial_days') }} jours gratuits, sans carte bancaire. Aucune commande n'est jamais bloquée.</p>
            </section>
        </form>

        <p x-show="step === 3" x-cloak class="mt-6 text-center">Déjà inscrit ? <a href="{{ route('login') }}" class="vt-link">Se connecter</a></p>
    </div>

    @push('scripts')
    <script>
        function registerForm() {
            return {
                step: 1,
                totalSteps: 3,
                loading: false,
                submitted: false,
                submitError: null,
                stepError: null,
                isStand: {{ $isStand ? 'true' : 'false' }},
                offer: @js($offer),
                formData: {
                    name: @js(old('name', '')), email: @js(old('email', '')), phone: @js(old('phone', '')), password: '',
                    restaurant_name: @js(old('restaurant_name', '')), restaurant_type: @js($initialRestaurantType), company_name: @js(old('company_name', '')), rccm: @js(old('rccm', '')),
                    plan: '{{ $plan->slug ?? "essentiel" }}'
                },
                showPassword: false,
                logoPreview: null,
                bannerPreview: null,
                get offerTitle() { return { restaurant: 'Votre restaurant', stand: 'Votre stand', boutique: 'Votre boutique' }[this.offer] || 'Votre commerce'; },
                validateStep1() { return this.formData.name && this.formData.phone && this.formData.password.length >= 8; },
                validateStep2() { return this.formData.restaurant_name && this.formData.restaurant_type; },
                next(from) {
                    const ok = from === 1 ? this.validateStep1() : this.validateStep2();
                    if (!ok) {
                        this.stepError = from === 1
                            ? 'Renseignez votre nom, votre numéro WhatsApp et un mot de passe de 8 caractères minimum.'
                            : 'Renseignez le nom et le type de votre commerce.';
                        return;
                    }
                    this.stepError = null;
                    this.step = from + 1;
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                },
                goToStep(t) { if (t >= 1 && t <= this.step) { this.step = t; this.submitError = null; this.stepError = null; window.scrollTo({ top: 0, behavior: 'smooth' }); } },
                submitForm() {
                    this.submitError = null;
                    if (!this.validateStep1() || !this.validateStep2()) {
                        this.submitError = 'Veuillez remplir tous les champs obligatoires.';
                        this.step = !this.validateStep1() ? 1 : 2;
                        return;
                    }
                    const terms = document.querySelector('input[name=terms]');
                    if (!terms || !terms.checked) { this.submitError = 'Acceptez les conditions d\'utilisation.'; return; }
                    this.loading = true;
                    this.submitted = true;
                    document.getElementById('register-form').submit();
                }
            };
        }

        (function() {
            const form = document.getElementById('register-form');
            if (!form) return;
            const token = form.querySelector('input[name="_token"]');
            if (token) setInterval(() => {
                fetch('{{ route("csrf.token") }}', { credentials: 'same-origin', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(r => r.json()).then(d => { if (d.token) token.value = d.token; }).catch(() => {});
            }, 180000);
        })();

        document.addEventListener('DOMContentLoaded', () => {
            const e = document.querySelector('[data-register-errors]');
            if (e) e.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });

        function pricingCalculator() {
            return {
                billingCycle: 'monthly',
                selectedAddons: [],
                basePriceMonthly: {{ (int) ($plan->price ?? 15000) }},
                currentPlanName: '{{ $plan->name ?? "Essentiel" }}',
                cycleMeta: [
                    { id: 'weekly', months: 1, discount: 0 }, // Stand uniquement (prix affiché côté serveur)
                    { id: 'monthly', months: 1, discount: 0 },
                    { id: 'quarterly', months: 3, discount: 10 },
                    { id: 'semiannual', months: 6, discount: 15 },
                    { id: 'annual', months: 12, discount: 20 },
                ],
                init() { this.billingCycle = 'monthly'; },
                getCurrentCycle() {
                    const m = this.cycleMeta.find(c => c.id === this.billingCycle);
                    const orig = this.basePriceMonthly * m.months;
                    return { ...m, original: orig, price: Math.round(orig * (1 - m.discount / 100)) };
                },
                getMonths() { return this.getCurrentCycle().months; },
                get basePrice() { return this.getCurrentCycle().price; },
                get discountAmount() { const c = this.getCurrentCycle(); return c.original - c.price; },
                get totalPrice() { return this.basePrice; },
                formatPrice(p) { return new Intl.NumberFormat('fr-FR').format(Math.round(p)); }
            }
        }
    </script>
    @endpush
</x-layouts.auth>
