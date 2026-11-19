@use('App\Support\VitrineGrid', 'G')
@php
    // Grille lue en base, repli sur PlanSeeder ; règles dans config/plans.php (jamais de prix en dur)
    $gridPlans = G::plans();
    $price = fn ($slug) => G::price($slug);
    $fmt = fn ($n) => G::money($n);
    $trialDays = G::trialDays();
    $standWeekly = G::weeklyPrice('stand');
    $restaurantName = $gridPlans['essentiel']->name;
    $rate = G::commission();
    $reducedRate = G::reducedCommission();
@endphp
<x-layouts.public title="Tarifs" description="Tarifs MenuPro : à partir de {{ $fmt($standWeekly) }} F/semaine. Restaurants, maquis, stands et boutiques. Livraison incluse, aucune commande jamais bloquée. Essai gratuit {{ $trialDays }} jours.">
    @push('head')
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "FAQPage",
        "mainEntity": [
            {
                "@@type": "Question",
                "name": "Quel plan MenuPro choisir ?",
                "acceptedAnswer": {
                    "@@type": "Answer",
                    "text": "Stand ({{ $fmt($price('stand')) }} FCFA/mois ou {{ $fmt($standWeekly) }} FCFA/semaine) pour les stands et kiosques. {{ $restaurantName }} ({{ $fmt($price('essentiel')) }} FCFA/mois) pour les maquis, restaurants et boutiques. Pro ({{ $fmt($price('pro')) }} FCFA/mois) pour le stock par ingrédient, la rentabilité et les hôtels. Gold sur devis pour les complexes multi-espaces."
                }
            },
            {
                "@@type": "Question",
                "name": "Y a-t-il un essai gratuit ?",
                "acceptedAnswer": {
                    "@@type": "Answer",
                    "text": "Oui, {{ $trialDays }} jours d'essai gratuit avec toutes les fonctions du plan Pro. Aucune carte bancaire requise."
                }
            },
            {
                "@@type": "Question",
                "name": "La livraison est-elle incluse ?",
                "acceptedAnswer": {
                    "@@type": "Answer",
                    "text": "Oui, la livraison MenuPro est incluse dans tous les plans. Une commission de {{ $rate }} s'applique uniquement sur les commandes livrées ({{ $reducedRate }} avec le plan Pro)."
                }
            }
        ]
    }
    </script>
    @endpush

    @php
        $cards = [
            'stand' => [
                'badge' => 'Stands, kiosques et petites boutiques',
                'tagline' => 'Recevez vos commandes et vos paiements Mobile Money, livraison comprise.',
                'intro' => 'Inclus',
                'features' => [
                    'Menu QR + commandes en ligne',
                    'Paiement Mobile Money',
                    'Livraison MenuPro incluse',
                    '30 plats · 8 catégories',
                    '1 utilisateur',
                    'Jusqu\'à ~150 commandes/mois',
                ],
            ],
            'essentiel' => [
                'badge' => 'Maquis, restaurants & boutiques',
                'tagline' => 'Caisse, écran cuisine, stock et statistiques pour gérer votre commerce.',
                'intro' => 'Tout Stand, plus',
                'features' => [
                    'Caisse (POS)',
                    'Écran cuisine',
                    'Stock simple',
                    'Statistiques',
                    'Réservations',
                    '3 employés · 100 produits',
                    'Jusqu\'à ~600 commandes/mois',
                ],
            ],
            'pro' => [
                'badge' => 'Commerces établis & hôtels',
                'tagline' => 'Maîtrisez vos coûts et votre marge, commission réduite à ' . $reducedRate . '.',
                'intro' => 'Tout ' . $restaurantName . ', plus',
                'features' => [
                    'Stock par ingrédient',
                    'Rentabilité par produit',
                    'Chambres d\'hôtel (QR par chambre)',
                    '10 employés',
                    'Produits et commandes illimités',
                    'Support prioritaire',
                    'Commission réduite à ' . $reducedRate,
                ],
            ],
            'gold' => [
                'badge' => 'Complexes & multi-sites',
                'tagline' => 'Multi-espaces, multi-sites, accompagnement dédié. Tout illimité.',
                'intro' => 'Tout Pro, plus',
                'features' => [
                    'Multi-espaces (terrasse, VIP…)',
                    'Multi-sites',
                    'Serveurs avec PIN dédié',
                    'Rapports par espace et par serveur',
                    'Tout illimité',
                    'Accompagnement et formation',
                ],
            ],
        ];
        $featuredSlug = 'essentiel';
        $softCheck = '<svg class="w-6 h-6 inline-block" viewBox="0 0 20 20" fill="none" role="img" aria-label="Inclus"><circle cx="10" cy="10" r="10" fill="#FFEDD5"/><path d="M6 10.2l2.6 2.6L14 7.4" stroke="#EA580C" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
        // Prix d'entrée par type de commerce : mêmes montants que l'accueil (config/vitrine.php → from_plan)
        $offerFrom = collect(config('vitrine.offers', []))->map(fn ($o, $key) => ['label' => $o['label']] + G::offerFrom($key));
    @endphp

    <div x-data="pricingCalculator()" x-init="init()">
    {{-- ══════════ EN-TÊTE (centré, halo) ══════════ --}}
    <section class="vt-pagehead">
        <div class="vt-wrap">
            <h1 class="vt-h1 max-w-3xl mx-auto">Un plan pour chaque commerce</h1>
            <p class="vt-lead mt-5">Du stand de rue au complexe hôtelier, en passant par la boutique de quartier. Dès {{ $fmt($standWeekly) }} F la semaine, livraison incluse.</p>
            <ul class="vt-checks-soft mt-7 grid gap-2.5 text-left max-w-md mx-auto md:max-w-none md:flex md:flex-wrap md:justify-center md:gap-x-7 text-[0.9375rem] font-semibold">
                <li>Livraison incluse — commission de {{ $rate }} uniquement sur les commandes livrées</li>
                <li>{{ "Aucune commande n'est jamais bloquée" }}</li>
                <li>{{ $trialDays }} jours d'essai avec toutes les fonctions Pro</li>
            </ul>

            {{-- Durée de facturation --}}
            <fieldset class="mt-10">
                <legend class="sr-only">Payer pour</legend>
                <p class="text-[0.9375rem] vt-muted mb-3" aria-hidden="true">Payer pour</p>
                <div class="vt-seg grid-cols-2 sm:grid-cols-4">
                    <template x-for="cycle in cycles" :key="cycle.id">
                        <button type="button" @click="billingCycle = cycle.id" :aria-pressed="(billingCycle === cycle.id).toString()" class="text-[0.9375rem] whitespace-nowrap">
                            <span x-text="cycle.label"></span>
                            <template x-if="cycle.discount > 0"><span class="ml-1 text-mp-ink" x-text="'-' + cycle.discount + ' %'"></span></template>
                        </button>
                    </template>
                </div>
            </fieldset>
        </div>
    </section>

    {{-- ══════════ GRILLE ══════════ --}}
    <section class="pb-14 md:pb-20">
        <div class="vt-wrap">
            {{-- Prix d'entrée par type de commerce (identiques à l'accueil et à l'inscription) --}}
            @if($offerFrom->isNotEmpty())
                <ul class="mb-8 grid gap-2 sm:grid-cols-3" data-offer-from>
                    @foreach($offerFrom as $key => $o)
                        <li class="vt-soft !rounded-2xl px-4 py-3 flex items-baseline justify-between gap-3 text-[0.9375rem]">
                            <span class="font-semibold">{{ $o['label'] }}</span>
                            <span class="whitespace-nowrap vt-muted">dès <span class="font-bold text-mp-black vt-num">{{ $o['amount'] }} {{ $o['unit'] }}</span>@if($key === 'boutique') <span class="text-sm">(plan Boutique)</span>@endif</span>
                        </li>
                    @endforeach
                </ul>
            @endif

            <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-4 items-start">
                @foreach($cards as $slug => $card)
                    @php
                        $planModel = $gridPlans[$slug];
                        $isFeatured = $slug === $featuredSlug;
                        $isGold = $slug === 'gold';
                    @endphp
                    <article class="relative flex flex-col h-full vt-card p-6 {{ $isFeatured ? '!shadow-[0_0_0_2px_#EA580C,0_24px_48px_-24px_rgba(234,88,12,.35)]' : '' }}">
                        <div class="flex items-center justify-between gap-2">
                            <h2 class="vt-display text-2xl">{{ $planModel->name }}</h2>
                            @if($isFeatured)<span class="vt-badge bg-[#FFEDD5] text-[#9A3412]">Recommandé</span>@endif
                        </div>
                        <p class="mt-1 text-[0.9375rem] font-semibold">{{ $card['badge'] }}</p>
                        <p class="mt-2 text-[0.9375rem] vt-muted">{{ $card['tagline'] }}</p>

                        <div class="mt-5 pb-5 border-b border-neutral-100 min-h-[6.5rem]">
                            @if($isGold)
                                <p class="text-[0.9375rem] font-semibold vt-muted">Sur devis, à partir de</p>
                                <p><span class="vt-display text-4xl vt-num whitespace-nowrap">{{ $fmt($planModel->price) }}</span> <span class="font-semibold">F/mois</span></p>
                            @else
                                <p><span class="vt-display text-4xl vt-num whitespace-nowrap" x-text="formatPrice(computePrice({{ (int) $planModel->price }}))">{{ $fmt($planModel->price) }}</span> <span class="font-semibold">F</span> <span class="font-semibold vt-muted" x-text="cycleLabel">par mois</span></p>
                                <p x-show="billingCycle !== 'monthly'" x-cloak class="text-[0.9375rem] vt-muted">soit <span x-text="formatPrice(computePrice({{ (int) $planModel->price }}) / getMonths())"></span> F/mois</p>
                                @if($slug === 'stand' && $standWeekly > 0)
                                    <p class="mt-1 font-semibold">ou {{ $fmt($standWeekly) }} F / semaine</p>
                                @endif
                            @endif
                        </div>

                        @if($isGold)
                            <a href="{{ route('contact') }}?type=gold" class="vt-btn vt-btn-ghost vt-btn-block mt-5">Demander un devis</a>
                        @else
                            <a href="{{ route('register', $slug === 'stand' ? ['type' => 'stand'] : ['plan' => $slug]) }}"
                               :href="'{{ route('register') }}?{{ $slug === 'stand' ? 'type=stand' : 'plan=' . $slug }}&cycle=' + billingCycle"
                               class="vt-btn {{ $isFeatured ? 'vt-btn-dark' : 'vt-btn-ghost' }} vt-btn-block mt-5">Essayer {{ $trialDays }} jours gratuits</a>
                        @endif
                        @if($slug === 'stand')
                            <a href="{{ route('register', ['type' => 'boutique']) }}" class="mt-3 text-center text-[0.9375rem] font-semibold text-mp-ink underline-offset-4 hover:underline">Boutique : dès {{ $fmt($planModel->price) }} F/mois (plan Boutique)</a>
                        @endif

                        <p class="mt-6 text-[0.9375rem] font-semibold">{{ $card['intro'] }}</p>
                        <ul class="vt-checks-soft mt-3 space-y-2.5 flex-1 text-[0.9375rem]">
                            @foreach($card['features'] as $f)<li>{{ $f }}</li>@endforeach
                        </ul>
                        @include('partials.plan-pack', ['slug' => $slug, 'compact' => true])
                    </article>
                @endforeach
            </div>

            {{-- Supports imprimés offerts (config/plans.php : welcome_pack + packs) --}}
            <div class="mt-8 vt-soft p-5 md:p-6 flex gap-4 items-start" data-welcome-pack>
                <span class="flex-none w-10 h-10 rounded-xl bg-white flex items-center justify-center text-mp-ink shadow-sm" aria-hidden="true">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/></svg>
                </span>
                <div>
                    <p class="font-semibold text-lg">{{ config('plans.welcome_pack') }}.</p>
                    <p class="mt-1 vt-muted">Et selon votre plan : autocollants QR de table, affiche vitrine, autocollant de porte, chevalets de comptoir (voir le pack de chaque offre).</p>
                </div>
            </div>

            {{-- Règles communes --}}
            <ul class="mt-10 grid gap-2 md:grid-cols-3 md:gap-8">
                @foreach([
                    ['Livraison incluse', 'Commission de ' . $rate . ' uniquement sur les commandes livrées par MenuPro (' . $reducedRate . ' avec le plan Pro). Rien sur vos autres commandes.'],
                    ['Aucune commande bloquée', 'Les commandes indiquées par plan sont un repère : au-delà, vos clients commandent normalement et nous vous proposons simplement le plan supérieur.'],
                    [$trialDays . ' jours pour tout essayer', 'Pendant l\'essai, vous avez toutes les fonctions du plan Pro. Ensuite, vous choisissez votre plan et payez en Mobile Money.'],
                ] as [$title, $text])
                    <li class="vt-benefit">
                        <h2 class="vt-display text-xl">{{ $title }}</h2>
                        <p class="mt-1.5 vt-muted">{{ $text }}</p>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>
    </div>

    {{-- ══════════ COMPARAISON (sur téléphone : un plan à la fois) ══════════ --}}
    @php
        $planCols = ['stand' => 'Stand', 'essentiel' => $restaurantName, 'pro' => 'Pro', 'gold' => 'Gold'];
        $compareCategories = [
            'Prix' => [
                ['Mensuel', $fmt($price('stand')) . ' F', $fmt($price('essentiel')) . ' F', $fmt($price('pro')) . ' F', 'Sur devis (dès ' . $fmt($price('gold')) . ' F)'],
                ['Hebdomadaire', $fmt($standWeekly) . ' F', false, false, false],
                ['Commission sur les commandes livrées', G::commission('stand'), G::commission('essentiel'), G::commission('pro'), G::commission('gold')],
            ],
            'Limites' => [
                ['Produits', '30', '100', 'Illimité', 'Illimité'],
                ['Catégories', '8', '30', 'Illimité', 'Illimité'],
                ['Utilisateurs', '1', '1 + 3 employés', '1 + 10 employés', 'Illimité'],
                ['Commandes / mois (repère, jamais bloquant)', '~150', '~600', 'Illimité', 'Illimité'],
            ],
            'Commandes' => [
                ['Menu ou catalogue QR + commandes en ligne', true, true, true, true],
                ['Paiement Mobile Money', true, true, true, true],
                ['Livraison MenuPro', true, true, true, true],
                ['Caisse (POS)', false, true, true, true],
                ['Écran cuisine', false, true, true, true],
                ['Réservations', false, true, true, true],
            ],
            'Gestion' => [
                ['Stock simple', false, true, true, true],
                ['Statistiques', false, true, true, true],
                ['Stock par ingrédient', false, false, true, true],
                ['Rentabilité par produit', false, false, true, true],
                ['Chambres d\'hôtel', false, false, true, true],
                ['Multi-espaces & multi-sites', false, false, false, true],
            ],
            'Support' => [
                ['Support WhatsApp', true, true, true, true],
                ['Support prioritaire', false, false, true, true],
                ['Accompagnement dédié', false, false, false, true],
            ],
        ];
    @endphp
    <section class="vt-section bg-[#FAFAF9]" x-data="{ cmp: '{{ $featuredSlug }}' }">
        <div class="vt-wrap max-w-5xl">
            <div class="text-center">
                <h2 class="vt-h2">Comparaison détaillée</h2>
                <p class="vt-lead mx-auto mt-3">Toutes les fonctions, plan par plan.</p>
            </div>

            {{-- Choix du plan affiché (téléphone uniquement) --}}
            <div class="md:hidden mt-8">
                <p id="cmp-label" class="text-[0.9375rem] vt-muted mb-2">Plan affiché</p>
                <div class="vt-seg grid-cols-4 w-full" role="group" aria-labelledby="cmp-label">
                    @foreach($planCols as $slug => $label)
                        <button type="button" @click="cmp = '{{ $slug }}'" :aria-pressed="(cmp === '{{ $slug }}').toString()" aria-pressed="{{ $slug === $featuredSlug ? 'true' : 'false' }}" class="!px-1 text-[0.9375rem] truncate">{{ $label }}</button>
                    @endforeach
                </div>
            </div>

            <div class="mt-4 md:mt-10 vt-card overflow-hidden">
                <table class="w-full text-left text-[0.9375rem]">
                    <caption class="sr-only">Comparaison des plans MenuPro</caption>
                    <thead>
                        <tr class="border-b border-neutral-200">
                            <th scope="col" class="py-4 px-4 font-semibold vt-muted">Fonction</th>
                            @foreach($planCols as $slug => $label)
                                <th scope="col" class="py-4 px-3 text-center font-semibold {{ $slug === $featuredSlug ? 'text-mp-ink' : 'max-md:hidden' }}"
                                    :class="{ 'max-md:hidden': cmp !== '{{ $slug }}' }">{{ $label }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($compareCategories as $catName => $items)
                            <tr class="bg-[#FAFAF9]">
                                <th scope="colgroup" colspan="5" class="py-2.5 px-4 text-sm font-semibold">{{ $catName }}</th>
                            </tr>
                            @foreach($items as $item)
                                <tr class="border-t border-neutral-100">
                                    <th scope="row" class="py-3 px-4 font-normal">{{ $item[0] }}</th>
                                    @foreach(array_keys($planCols) as $i => $slug)
                                        @php $val = $item[$i + 1]; @endphp
                                        <td class="py-3 px-3 text-center {{ $slug === $featuredSlug ? 'bg-[#FFF7ED]/60' : 'max-md:hidden' }}"
                                            :class="{ 'max-md:hidden': cmp !== '{{ $slug }}' }">
                                            @if(is_bool($val))
                                                @if($val)
                                                    {!! $softCheck !!}
                                                @else
                                                    <span class="text-neutral-500" aria-label="Non inclus">–</span>
                                                @endif
                                            @else
                                                <span class="font-semibold">{{ $val }}</span>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </section>

    {{-- ══════════ PAIEMENT ══════════ --}}
    <section class="vt-section">
        <div class="vt-wrap text-center">
            <h2 class="vt-h2">Payez votre abonnement en Mobile Money</h2>
            <p class="vt-lead mx-auto mt-3">Aucun prélèvement automatique : vous payez quand vous renouvelez.</p>
            <ul class="mt-8 flex flex-wrap items-center justify-center gap-x-8 gap-y-4">
                @foreach([['Wave', 'wave.png'], ['Orange Money', 'orange-money.png'], ['MTN MoMo', 'mtn-momo.png'], ['Moov Money', 'moov-money.png']] as [$name, $img])
                    <li class="flex items-center gap-2.5 text-lg font-semibold text-neutral-600">
                        <img src="{{ asset('images/payments/' . $img) }}" alt="" width="32" height="32" class="h-8 w-8 rounded-lg object-contain" loading="lazy" decoding="async">
                        {{ $name }}
                    </li>
                @endforeach
            </ul>
        </div>
    </section>

    {{-- ══════════ QUESTIONS ══════════ --}}
    @php
        $faqs = [
            ['q' => 'Quel plan choisir ?', 'a' => 'Stand (' . $fmt($price('stand')) . ' F/mois ou ' . $fmt($standWeekly) . ' F/semaine) pour les stands, kiosques et la street food. Boutique : dès ' . $fmt($price('stand')) . ' F/mois (plan Boutique). ' . $restaurantName . ' (' . $fmt($price('essentiel')) . ' F/mois) pour les maquis et restaurants : caisse, écran cuisine, stock et statistiques. Pro (' . $fmt($price('pro')) . ' F/mois) pour suivre votre stock ingrédient par ingrédient, la rentabilité de chaque produit et les chambres d\'hôtel. Gold sur devis pour les complexes multi-espaces et multi-sites.'],
            ['q' => 'Comment fonctionne l\'essai gratuit ?', 'a' => 'Vous avez ' . $trialDays . ' jours pour tester gratuitement toutes les fonctions du plan Pro. À la fin de l\'essai, vous choisissez votre plan et payez en Mobile Money. Aucun paiement automatique.'],
            ['q' => 'La livraison est-elle vraiment incluse ?', 'a' => 'Oui, dans tous les plans. MenuPro prend une commission de ' . $rate . ' uniquement sur les commandes livrées par nos livreurs (' . $reducedRate . ' avec le plan Pro). Les commandes sur place et à emporter ne sont pas concernées.'],
            ['q' => 'Que se passe-t-il si je dépasse le nombre de commandes de mon plan ?', 'a' => 'Rien de bloquant : aucune commande n\'est jamais refusée. Vous recevez simplement une notification vous proposant le plan supérieur.'],
            ['q' => 'Puis-je changer de plan ?', 'a' => 'Oui, à tout moment depuis votre tableau de bord, vers le haut comme vers le bas.'],
            ['q' => 'Comment fonctionnent les réductions ?', 'a' => 'En payant 3, 6 ou 12 mois d\'avance, vous économisez ' . collect(config('plans.billing_periods'))->only(['quarterly', 'semiannual', 'annual'])->pluck('discount')->map(fn ($d) => $d . ' %')->join(', ', ' ou ') . '. Le plan Stand peut aussi se payer à la semaine (' . $fmt($standWeekly) . ' F).'],
            ['q' => 'Comment mes clients me paient-ils ?', 'a' => 'Vos clients paient via Orange Money, MTN MoMo, Wave ou Moov Money, ou en espèces si vous l\'acceptez. L\'argent arrive sur votre portefeuille MenuPro, retirable sur votre Mobile Money.'],
        ];
    @endphp
    <section class="vt-section bg-[#FAFAF9]">
        <div class="vt-wrap max-w-3xl">
            <h2 class="vt-h2 text-center">Questions sur les tarifs</h2>
            <div class="vt-faq mt-8">
                @foreach($faqs as $faq)
                    <details @if($loop->first) open @endif>
                        <summary>
                            {{ $faq['q'] }}
                            <span class="vt-faq-icon" aria-hidden="true"><svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2.4" stroke-linecap="round" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg></span>
                        </summary>
                        <p>{{ $faq['a'] }}</p>
                    </details>
                @endforeach
            </div>
            <p class="mt-6 text-center"><a href="{{ route('faq') }}" class="vt-link">Toutes les questions</a></p>
        </div>
    </section>

    {{-- ══════════ APPEL FINAL ══════════ --}}
    <section class="py-14 md:py-20">
        <div class="vt-wrap">
            <div class="vt-pagehead !py-12 md:!py-16 rounded-[1.75rem] px-5">
                <h2 class="vt-h2">Essayez {{ $trialDays }} jours, sans engagement.</h2>
                <p class="vt-lead mt-3">Dès {{ $fmt($standWeekly) }} F/semaine ensuite. Vous ne payez que si vous continuez.</p>
                <div class="mt-8 grid gap-3 sm:flex sm:justify-center">
                    <a href="{{ route('register') }}" class="vt-btn vt-btn-dark">Créer mon commerce</a>
                    <a href="{{ route('contact') }}?type=demo" class="vt-btn vt-btn-ghost">Demander une démo</a>
                </div>
            </div>
        </div>
    </section>

    @push('scripts')
    <script>
        function pricingCalculator() {
            return {
                billingCycle: 'monthly',
                cycles: @js(collect(config('plans.billing_periods'))->except('weekly')->map(fn ($p, $id) => ['id' => $id, 'label' => $p['label'], 'months' => $p['months'], 'discount' => $p['discount']])->values()),
                init() { this.billingCycle = 'monthly'; },
                getCurrentCycle() { return this.cycles.find(c => c.id === this.billingCycle); },
                getMonths() { return this.getCurrentCycle().months; },
                // Même calcul que Subscription::calculatePriceWithDiscount()
                computePrice(monthlyPrice) {
                    const cycle = this.getCurrentCycle();
                    return Math.floor(monthlyPrice * cycle.months * (1 - cycle.discount / 100));
                },
                get cycleLabel() {
                    return { monthly: 'par mois', quarterly: 'par trimestre', semiannual: 'par semestre', annual: 'par an' }[this.billingCycle];
                },
                formatPrice(price) { return new Intl.NumberFormat('fr-FR').format(Math.round(price)); }
            }
        }
    </script>
    @endpush
</x-layouts.public>
