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

    {{-- ══════════ EN-TÊTE ══════════ --}}
    <section class="pt-10 pb-8 md:pt-16">
        <div class="vt-wrap">
            <h1 class="vt-h1 max-w-3xl">Un plan pour chaque commerce</h1>
            <p class="vt-lead mt-5">Du stand de rue au complexe hôtelier, en passant par la boutique de quartier. Dès {{ $fmt($standWeekly) }} F la semaine, livraison incluse.</p>
            <ul class="vt-checks mt-6 grid gap-2 md:grid-cols-3 md:gap-6">
                <li>Livraison incluse — commission de {{ $rate }} uniquement sur les commandes livrées</li>
                <li>{{ "Aucune commande n'est jamais bloquée" }}</li>
                <li>{{ $trialDays }} jours d'essai avec toutes les fonctions Pro</li>
            </ul>
        </div>
    </section>

    {{-- ══════════ GRILLE ══════════ --}}
    <section class="pb-14 md:pb-20" x-data="pricingCalculator()" x-init="init()">
        <div class="vt-wrap">
            {{-- Durée de facturation --}}
            <fieldset class="mb-8">
                <legend class="vt-label">Payer pour</legend>
                <div class="grid grid-cols-2 sm:inline-grid sm:grid-cols-4 gap-2">
                    <template x-for="cycle in cycles" :key="cycle.id">
                        <button type="button" @click="billingCycle = cycle.id" :aria-pressed="(billingCycle === cycle.id).toString()"
                                :class="billingCycle === cycle.id ? 'bg-mp-black text-white border-mp-black' : 'bg-white text-mp-black border-mp-line hover:border-mp-black'"
                                class="min-h-12 px-4 rounded-xl border-2 font-bold text-base">
                            <span x-text="cycle.label"></span>
                            <template x-if="cycle.discount > 0"><span class="ml-1 text-mp-orange" x-text="'-' + cycle.discount + ' %'"></span></template>
                        </button>
                    </template>
                </div>
            </fieldset>

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
            @endphp

            <div class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
                @foreach($cards as $slug => $card)
                    @php
                        $planModel = $gridPlans[$slug];
                        $isFeatured = $slug === 'essentiel';
                        $isGold = $slug === 'gold';
                    @endphp
                    <article class="flex flex-col rounded-2xl p-6 {{ $isFeatured ? 'bg-mp-orange border-2 border-mp-black' : 'bg-white border-2 border-mp-line' }}">
                        <p class="font-semibold {{ $isFeatured ? '' : 'vt-muted' }}">{{ $card['badge'] }}</p>
                        <h2 class="vt-h3 text-3xl mt-1">{{ $planModel->name }}</h2>
                        <p class="mt-2 {{ $isFeatured ? '' : 'vt-muted' }}">{{ $card['tagline'] }}</p>

                        <div class="mt-5 pb-5 border-b-2 {{ $isFeatured ? 'border-mp-black/25' : 'border-mp-line' }}">
                            @if($isGold)
                                <p class="font-semibold">Sur devis, à partir de</p>
                                <p><span class="text-4xl font-bold vt-num whitespace-nowrap">{{ $fmt($planModel->price) }}</span> <span class="font-semibold">F/mois</span></p>
                            @else
                                <p><span class="text-4xl font-bold vt-num whitespace-nowrap" x-text="formatPrice(computePrice({{ (int) $planModel->price }}))">{{ $fmt($planModel->price) }}</span> <span class="font-semibold">F</span> <span class="font-semibold" x-text="cycleLabel">par mois</span></p>
                                <p x-show="billingCycle !== 'monthly'" x-cloak class="font-semibold">soit <span x-text="formatPrice(computePrice({{ (int) $planModel->price }}) / getMonths())"></span> F/mois</p>
                                @if($slug === 'stand' && $standWeekly > 0)
                                    <p class="mt-1 font-bold">ou {{ $fmt($standWeekly) }} F / semaine</p>
                                @endif
                            @endif
                        </div>

                        @if($isGold)
                            <a href="{{ route('contact') }}?type=gold" class="vt-btn vt-btn-ghost vt-btn-block mt-5">Demander un devis</a>
                        @else
                            <a href="{{ route('register', $slug === 'stand' ? ['type' => 'stand'] : ['plan' => $slug]) }}"
                               :href="'{{ route('register') }}?{{ $slug === 'stand' ? 'type=stand' : 'plan=' . $slug }}&cycle=' + billingCycle"
                               class="vt-btn {{ $isFeatured ? 'vt-btn-dark' : 'vt-btn-primary' }} vt-btn-block mt-5">Essayer {{ $trialDays }} jours gratuits</a>
                        @endif

                        <p class="mt-6 font-bold">{{ $card['intro'] }}</p>
                        <ul class="vt-checks mt-3 space-y-2 flex-1">
                            @foreach($card['features'] as $f)<li>{{ $f }}</li>@endforeach
                        </ul>
                        @include('partials.plan-pack', ['slug' => $slug, 'compact' => true])
                    </article>
                @endforeach
            </div>

            {{-- Supports imprimés offerts (config/plans.php : welcome_pack + packs) --}}
            <div class="mt-8 rounded-2xl border-2 border-mp-black bg-mp-paper p-5 md:p-6" data-welcome-pack>
                <p class="font-bold text-lg">{{ config('plans.welcome_pack') }}.</p>
                <p class="mt-1 vt-muted">Et selon votre plan : autocollants QR de table, affiche vitrine, autocollant de porte, chevalets de comptoir (voir le pack de chaque offre).</p>
            </div>

            {{-- Règles communes --}}
            <div class="mt-8 grid gap-6 md:grid-cols-3">
                @foreach([
                    ['Livraison incluse', 'Commission de ' . $rate . ' uniquement sur les commandes livrées par MenuPro (' . $reducedRate . ' avec le plan Pro). Rien sur vos autres commandes.'],
                    ['Aucune commande bloquée', 'Les commandes indiquées par plan sont un repère : au-delà, vos clients commandent normalement et nous vous proposons simplement le plan supérieur.'],
                    [$trialDays . ' jours pour tout essayer', 'Pendant l\'essai, vous avez toutes les fonctions du plan Pro. Ensuite, vous choisissez votre plan et payez en Mobile Money.'],
                ] as [$title, $text])
                    <div class="border-t-2 border-mp-black pt-4">
                        <h2 class="vt-h3">{{ $title }}</h2>
                        <p class="mt-1 vt-muted">{{ $text }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ══════════ COMPARAISON ══════════ --}}
    <section class="vt-section bg-mp-paper">
        <div class="vt-wrap">
            <h2 class="vt-h2">Comparaison détaillée</h2>
            <p class="vt-lead mt-3">Faites glisser le tableau sur téléphone pour voir tous les plans.</p>

            <div class="mt-8 rounded-2xl border-2 border-mp-line bg-white overflow-x-auto" tabindex="0" role="region" aria-label="Tableau de comparaison des plans">
                <table class="w-full min-w-[40rem] text-left">
                    <thead>
                        <tr class="border-b-2 border-mp-black">
                            <th scope="col" class="sticky left-0 bg-white py-4 px-4 font-bold">Fonction</th>
                            <th scope="col" class="py-4 px-3 text-center font-bold">Stand</th>
                            <th scope="col" class="py-4 px-3 text-center font-bold bg-mp-orange">{{ $restaurantName }}</th>
                            <th scope="col" class="py-4 px-3 text-center font-bold">Pro</th>
                            <th scope="col" class="py-4 px-3 text-center font-bold">Gold</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php
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
                        @foreach($compareCategories as $catName => $items)
                            <tr class="bg-mp-paper">
                                <th scope="colgroup" colspan="5" class="py-2.5 px-4 font-bold">{{ $catName }}</th>
                            </tr>
                            @foreach($items as $item)
                                <tr class="border-t border-mp-line">
                                    <th scope="row" class="sticky left-0 bg-white py-3 px-4 font-medium max-w-[11rem]">{{ $item[0] }}</th>
                                    @foreach([$item[1], $item[2], $item[3], $item[4]] as $val)
                                        <td class="py-3 px-3 text-center">
                                            @if(is_bool($val))
                                                @if($val)
                                                    <svg class="w-6 h-6 inline-block text-mp-ink" fill="none" stroke="currentColor" stroke-width="3" viewBox="0 0 24 24" role="img" aria-label="Inclus"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                                                @else
                                                    <span class="text-[#857C74] text-xl" aria-label="Non inclus">–</span>
                                                @endif
                                            @else
                                                <span class="font-semibold whitespace-nowrap">{{ $val }}</span>
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
        <div class="vt-wrap">
            <h2 class="vt-h2">Payez votre abonnement en Mobile Money</h2>
            <ul class="mt-8 flex flex-wrap gap-3">
                @foreach([['Wave', 'wave.png'], ['Orange Money', 'orange-money.png'], ['MTN MoMo', 'mtn-momo.png'], ['Moov Money', 'moov-money.png']] as [$name, $img])
                    <li class="flex items-center gap-3 rounded-xl border-2 border-mp-line px-4 py-2">
                        <img src="{{ asset('images/payments/' . $img) }}" alt="" width="32" height="32" class="h-8 w-8 object-contain" loading="lazy" decoding="async">
                        <span class="font-semibold">{{ $name }}</span>
                    </li>
                @endforeach
            </ul>
        </div>
    </section>

    {{-- ══════════ QUESTIONS ══════════ --}}
    <section class="vt-section bg-mp-paper">
        <div class="vt-wrap max-w-3xl">
            <h2 class="vt-h2">Questions fréquentes</h2>
            @php
                $faqs = [
                    ['q' => 'Quel plan choisir ?', 'a' => 'Stand (' . $fmt($price('stand')) . ' F/mois ou ' . $fmt($standWeekly) . ' F/semaine) pour les stands, kiosques et la street food. ' . $restaurantName . ' (' . $fmt($price('essentiel')) . ' F/mois) pour les maquis, restaurants et boutiques : caisse, écran cuisine, stock et statistiques. Pro (' . $fmt($price('pro')) . ' F/mois) pour suivre votre stock ingrédient par ingrédient, la rentabilité de chaque produit et les chambres d\'hôtel. Gold sur devis pour les complexes multi-espaces et multi-sites.'],
                    ['q' => 'Comment fonctionne l\'essai gratuit ?', 'a' => 'Vous avez ' . $trialDays . ' jours pour tester gratuitement toutes les fonctions du plan Pro. À la fin de l\'essai, vous choisissez votre plan et payez en Mobile Money. Aucun paiement automatique.'],
                    ['q' => 'La livraison est-elle vraiment incluse ?', 'a' => 'Oui, dans tous les plans. MenuPro prend une commission de ' . $rate . ' uniquement sur les commandes livrées par nos livreurs (' . $reducedRate . ' avec le plan Pro). Les commandes sur place et à emporter ne sont pas concernées.'],
                    ['q' => 'Que se passe-t-il si je dépasse le nombre de commandes de mon plan ?', 'a' => 'Rien de bloquant : aucune commande n\'est jamais refusée. Vous recevez simplement une notification vous proposant le plan supérieur.'],
                    ['q' => 'Puis-je changer de plan ?', 'a' => 'Oui, à tout moment depuis votre tableau de bord, vers le haut comme vers le bas.'],
                    ['q' => 'Comment fonctionnent les réductions ?', 'a' => 'En payant 3, 6 ou 12 mois d\'avance, vous économisez ' . collect(config('plans.billing_periods'))->only(['quarterly', 'semiannual', 'annual'])->pluck('discount')->map(fn ($d) => $d . ' %')->join(', ', ' ou ') . '. Le plan Stand peut aussi se payer à la semaine (' . $fmt($standWeekly) . ' F).'],
                    ['q' => 'Comment mes clients me paient-ils ?', 'a' => 'Vos clients paient via Orange Money, MTN MoMo, Wave ou Moov Money, ou en espèces si vous l\'acceptez. L\'argent arrive sur votre portefeuille MenuPro, retirable sur votre Mobile Money.'],
                ];
            @endphp
            <div class="mt-8 border-t-2 border-mp-black">
                @foreach($faqs as $faq)
                    <details class="group border-b-2 border-mp-line">
                        <summary class="flex items-center justify-between gap-4 py-5 cursor-pointer list-none text-lg font-bold [&::-webkit-details-marker]:hidden">
                            {{ $faq['q'] }}
                            <span class="flex-none w-9 h-9 rounded-full bg-white flex items-center justify-center group-open:rotate-45 transition-transform" aria-hidden="true">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                            </span>
                        </summary>
                        <p class="pb-5 vt-muted">{{ $faq['a'] }}</p>
                    </details>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ══════════ APPEL FINAL ══════════ --}}
    <section class="bg-mp-black text-white">
        <div class="vt-wrap py-14 md:py-20 grid gap-8 md:grid-cols-[1.5fr_1fr] md:items-center">
            <div>
                <h2 class="vt-h2">Essayez {{ $trialDays }} jours, sans engagement.</h2>
                <p class="mt-3 text-lg text-neutral-200">Dès {{ $fmt($standWeekly) }} F/semaine ensuite. Vous ne payez que si vous continuez.</p>
            </div>
            <div class="grid gap-3">
                <a href="{{ route('register') }}" class="vt-btn vt-btn-primary vt-btn-block">Créer mon commerce</a>
                <a href="{{ route('contact') }}?type=demo" class="vt-btn vt-btn-light vt-btn-block">Voir une démo</a>
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
