@php
    // Prix lus en base (grille appliquée par plans:appliquer-grille) ; repli sur la grille du seeder
    $dbPlans = \App\Models\Plan::whereIn('slug', \Database\Seeders\PlanSeeder::ACTIVE_SLUGS)->get()->keyBy('slug');
    $gridPlans = collect(\Database\Seeders\PlanSeeder::definitions())
        ->only(\Database\Seeders\PlanSeeder::ACTIVE_SLUGS)
        ->map(fn ($def, $slug) => $dbPlans[$slug] ?? new \App\Models\Plan($def));
    $price = fn ($slug) => (int) $gridPlans[$slug]->price;
    $fmt = fn ($n) => number_format((int) $n, 0, ',', ' ');
    $trialDays = (int) config('plans.trial_days', 30);
    $standWeekly = (int) ($gridPlans['stand']->weekly_price ?? 1500);
    $restaurantName = $gridPlans['essentiel']->name;
@endphp
<x-layouts.public title="Tarifs" description="Tarifs MenuPro : à partir de {{ $fmt($standWeekly) }} F/semaine. Livraison incluse, aucune commande jamais bloquée. Essai gratuit {{ $trialDays }} jours avec toutes les fonctions Pro.">
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
                    "text": "Stand ({{ $fmt($price('stand')) }} FCFA/mois ou {{ $fmt($standWeekly) }} FCFA/semaine) pour les stands et kiosques. {{ $restaurantName }} ({{ $fmt($price('essentiel')) }} FCFA/mois) pour les maquis et restaurants. Pro ({{ $fmt($price('pro')) }} FCFA/mois) pour le stock par ingrédient, la rentabilité et les hôtels. Gold sur devis pour les complexes multi-espaces."
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
                    "text": "Oui, la livraison MenuPro est incluse dans tous les plans. Une commission de 12 % s'applique uniquement sur les commandes livrées (10 % avec le plan Pro)."
                }
            }
        ]
    }
    </script>
    @endpush

    <!-- Hero Section -->
    <section class="py-20 sm:py-28 bg-gradient-to-br from-neutral-950 via-neutral-900 to-neutral-950 relative overflow-hidden">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_30%_50%,rgba(249,115,22,0.08),transparent_60%)]"></div>
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_70%_80%,rgba(16,185,129,0.06),transparent_60%)]"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-secondary-500/10 border border-secondary-500/20 rounded-full text-secondary-400 text-sm font-semibold mb-6">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                {{ $trialDays }} jours d'essai gratuit avec toutes les fonctions Pro
            </div>
            <h1 class="font-display text-4xl md:text-5xl lg:text-6xl font-bold text-white">
                Un plan pour chaque<br class="sm:hidden"> <span class="text-primary-400">restaurant</span>
            </h1>
            <p class="text-lg sm:text-xl text-neutral-400 mt-6 max-w-2xl mx-auto leading-relaxed">
                Du stand de rue au complexe hôtelier. Dès {{ $fmt($standWeekly) }} F la semaine, livraison incluse.
            </p>

            <!-- Trust badges -->
            <div class="flex flex-wrap items-center justify-center gap-x-6 gap-y-3 mt-10 text-sm text-neutral-500">
                @foreach([
                    'Livraison incluse — commission de 12 % uniquement sur les commandes livrées',
                    'Aucune commande n\'est jamais bloquée',
                    'Paiement Mobile Money, sans engagement',
                ] as $badge)
                <span class="flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-secondary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    {{ $badge }}
                </span>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Main Pricing Section -->
    <section class="py-20 bg-neutral-950" x-data="pricingCalculator()" x-init="init()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Billing Cycle Toggle -->
            <div class="flex justify-center mb-12">
                <div class="inline-flex flex-wrap justify-center bg-neutral-900 border border-neutral-800 p-1.5 rounded-2xl max-w-full">
                    <template x-for="cycle in cycles" :key="cycle.id">
                        <button @click="billingCycle = cycle.id"
                                :class="billingCycle === cycle.id ? 'bg-gradient-to-r from-primary-500 to-primary-600 text-white shadow-lg shadow-primary-500/20' : 'text-neutral-400 hover:text-white'"
                                class="relative px-2.5 sm:px-5 py-2 sm:py-2.5 rounded-xl text-[11px] sm:text-sm font-semibold transition-all">
                            <span x-text="cycle.label"></span>
                            <template x-if="cycle.discount > 0">
                                <span class="ml-0.5 sm:ml-1 inline-block px-1 sm:px-1.5 py-0.5 rounded-md bg-secondary-500 text-white text-[9px] sm:text-[10px] font-bold"
                                      x-text="'-' + cycle.discount + '%'"></span>
                            </template>
                        </button>
                    </template>
                </div>
            </div>

            @php
                $cards = [
                    'stand' => [
                        'badge' => 'Stands & kiosques',
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
                        'badge' => 'Maquis & restaurants',
                        'tagline' => 'Caisse, écran cuisine, stock et statistiques pour gérer votre salle.',
                        'intro' => 'Tout Stand, plus',
                        'features' => [
                            'Caisse (POS)',
                            'Écran cuisine',
                            'Stock simple',
                            'Statistiques',
                            'Réservations',
                            '3 employés · 100 plats',
                            'Jusqu\'à ~600 commandes/mois',
                        ],
                    ],
                    'pro' => [
                        'badge' => 'Restaurants établis & hôtels',
                        'tagline' => 'Maîtrisez vos coûts et votre marge, commission réduite à 10 %.',
                        'intro' => 'Tout ' . $restaurantName . ', plus',
                        'features' => [
                            'Stock par ingrédient',
                            'Rentabilité par plat',
                            'Chambres d\'hôtel (QR par chambre)',
                            '10 employés',
                            'Plats et commandes illimités',
                            'Support prioritaire',
                            'Commission réduite à 10 %',
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

            <!-- Plans Grid - 4 columns -->
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-4 gap-6 lg:gap-8 max-w-[90rem] mx-auto">
                @foreach($cards as $slug => $card)
                    @php
                        $planModel = $gridPlans[$slug];
                        $isFeatured = $slug === 'essentiel';
                        $isGold = $slug === 'gold';
                    @endphp
                    <div class="relative group">
                        @if($isFeatured)
                            <div class="absolute -top-4 left-1/2 -translate-x-1/2 z-10">
                                <div class="bg-gradient-to-r from-primary-500 to-primary-600 text-white px-5 py-1.5 rounded-full text-xs font-bold shadow-lg shadow-primary-500/30 whitespace-nowrap">POPULAIRE</div>
                            </div>
                            <div class="absolute -inset-1 bg-gradient-to-r from-primary-500 to-accent-500 rounded-3xl opacity-20 blur-xl group-hover:opacity-30 transition-opacity"></div>
                        @endif

                        <div class="relative h-full rounded-3xl p-6 sm:p-8 flex flex-col {{ $isFeatured ? 'bg-gradient-to-b from-neutral-900 to-neutral-950 border-2 border-primary-500/50 shadow-2xl shadow-primary-500/10' : ($isGold ? 'bg-gradient-to-br from-primary-950 via-neutral-900 to-primary-950 border-2 border-primary-500/60' : 'bg-neutral-900 border border-neutral-800 hover:border-neutral-700 transition-all') }}">
                            <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-neutral-800 rounded-full text-xs font-semibold text-neutral-300 mb-4 self-start">
                                {{ $card['badge'] }}
                            </div>

                            <h3 class="text-2xl font-bold text-white mb-1">{{ $planModel->name }}</h3>
                            <p class="text-sm text-neutral-500 mb-6">{{ $card['tagline'] }}</p>

                            <!-- Price -->
                            <div class="mb-6 pb-6 border-b border-neutral-800">
                                @if($isGold)
                                    <div class="text-sm text-primary-300 font-semibold">Sur devis</div>
                                    <div class="flex items-baseline gap-2">
                                        <span class="text-sm text-neutral-500">à partir de</span>
                                        <span class="text-4xl font-bold bg-gradient-to-r from-primary-400 to-primary-600 bg-clip-text text-transparent">{{ $fmt($planModel->price) }}</span>
                                        <span class="text-neutral-500 text-sm">F/mois</span>
                                    </div>
                                @else
                                    <div class="flex items-baseline gap-2">
                                        <span class="text-4xl font-bold text-white" x-text="formatPrice(computePrice({{ (int) $planModel->price }}))"></span>
                                        <span class="text-neutral-500 text-sm">F</span>
                                    </div>
                                    <div class="text-xs text-neutral-500 mt-2">
                                        <span x-text="cycleLabel"></span>
                                        <template x-if="billingCycle !== 'monthly'">
                                            <span class="text-secondary-400 block mt-1">soit <span x-text="formatPrice(computePrice({{ (int) $planModel->price }}) / getMonths())"></span> F/mois</span>
                                        </template>
                                    </div>
                                    @if($slug === 'stand')
                                        <div class="mt-2 text-sm text-secondary-400 font-semibold">ou {{ $fmt($standWeekly) }} F / semaine</div>
                                    @endif
                                @endif
                            </div>

                            <!-- CTA -->
                            @if($isGold)
                                <a href="{{ route('contact') }}?type=gold"
                                   class="flex items-center justify-center gap-2 w-full py-3 px-6 rounded-xl font-bold text-sm bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white transition-all mb-6">
                                    Demander un devis
                                </a>
                            @else
                                <a :href="'{{ route('register') }}?{{ $slug === 'stand' ? 'type=stand' : 'plan=' . $slug }}&cycle=' + billingCycle"
                                   class="block w-full text-center py-3 px-6 rounded-xl font-bold text-sm transition-all mb-6 {{ $isFeatured ? 'bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white shadow-lg shadow-primary-500/20' : 'border-2 border-neutral-700 text-white hover:bg-neutral-800 hover:border-neutral-600' }}">
                                    Essayer {{ $trialDays }} jours gratuits
                                </a>
                            @endif

                            <!-- Features -->
                            <p class="text-xs font-bold text-primary-400 uppercase tracking-wider mb-3">{{ $card['intro'] }}</p>
                            <ul class="space-y-2.5 flex-1">
                                @foreach($card['features'] as $f)
                                    <li class="flex items-start gap-2.5 text-sm text-neutral-200">
                                        <svg class="w-4 h-4 text-primary-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                        <span>{{ $f }}</span>
                                    </li>
                                @endforeach
                            </ul>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Règles communes -->
            <div class="max-w-6xl mx-auto mt-10 grid grid-cols-1 md:grid-cols-3 gap-4">
                @foreach([
                    ['Livraison incluse', 'Commission de 12 % uniquement sur les commandes livrées par MenuPro (10 % avec le plan Pro). Rien sur vos autres commandes.'],
                    ['Aucune commande n\'est jamais bloquée', 'Les commandes indiquées par plan sont un repère : au-delà, vos clients commandent normalement et nous vous proposons simplement le plan supérieur.'],
                    [$trialDays . ' jours pour tout essayer', 'Pendant l\'essai, vous avez toutes les fonctions du plan Pro. Ensuite, vous choisissez votre plan et payez en Mobile Money.'],
                ] as [$title, $text])
                    <div class="bg-gradient-to-r from-secondary-500/10 to-primary-500/10 border border-secondary-500/20 rounded-2xl p-5">
                        <p class="text-white font-semibold text-sm">{{ $title }}</p>
                        <p class="text-neutral-400 text-xs mt-1 leading-relaxed">{{ $text }}</p>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- Comparison Table -->
    <section class="py-20 bg-neutral-900 border-t border-neutral-800">
        <div class="max-w-[90rem] mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="font-display text-3xl sm:text-4xl font-bold text-white mb-3">Comparaison détaillée</h2>
                <p class="text-neutral-400">Tout ce que contient chaque plan, en un coup d'œil</p>
            </div>

            <div class="bg-neutral-950 rounded-2xl border border-neutral-800 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[800px]">
                        <thead>
                            <tr class="border-b border-neutral-800">
                                <th class="text-left py-4 px-6 text-sm font-semibold text-neutral-400">Fonctionnalité</th>
                                <th class="text-center py-4 px-4 text-sm font-bold text-white">Stand</th>
                                <th class="text-center py-4 px-4 text-sm font-bold text-primary-400">{{ $restaurantName }}</th>
                                <th class="text-center py-4 px-4 text-sm font-bold text-amber-400">Pro</th>
                                <th class="text-center py-4 px-4 text-sm font-bold bg-gradient-to-r from-primary-500 to-primary-600 bg-clip-text text-transparent">Gold</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $compareCategories = [
                                    'Prix' => [
                                        ['Mensuel', $fmt($price('stand')) . ' F', $fmt($price('essentiel')) . ' F', $fmt($price('pro')) . ' F', 'Sur devis (dès ' . $fmt($price('gold')) . ' F)'],
                                        ['Hebdomadaire', $fmt($standWeekly) . ' F', false, false, false],
                                        ['Commission sur les commandes livrées', '12 %', '12 %', '10 %', '10 %'],
                                    ],
                                    'Limites' => [
                                        ['Plats', '30', '100', 'Illimité', 'Illimité'],
                                        ['Catégories', '8', '30', 'Illimité', 'Illimité'],
                                        ['Utilisateurs', '1', '1 + 3 employés', '1 + 10 employés', 'Illimité'],
                                        ['Commandes / mois (repère, jamais bloquant)', '~150', '~600', 'Illimité', 'Illimité'],
                                    ],
                                    'Menu & commandes' => [
                                        ['Menu QR + commandes en ligne', true, true, true, true],
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
                                        ['Rentabilité par plat', false, false, true, true],
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
                                <tr class="bg-neutral-900/50">
                                    <td colspan="5" class="py-2.5 px-6 text-xs font-bold text-primary-400 uppercase tracking-wider">{{ $catName }}</td>
                                </tr>
                                @foreach($items as $item)
                                    <tr class="border-t border-neutral-800/50 hover:bg-neutral-900/30 transition-colors">
                                        <td class="py-3 px-6 text-sm text-neutral-300">{{ $item[0] }}</td>
                                        @foreach([$item[1], $item[2], $item[3], $item[4]] as $val)
                                        <td class="py-3 px-4 text-center">
                                            @if(is_bool($val))
                                                @if($val)
                                                    <svg class="w-5 h-5 text-secondary-500 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                @else
                                                    <svg class="w-5 h-5 text-neutral-700 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                                @endif
                                            @else
                                                <span class="text-sm font-semibold text-white">{{ $val }}</span>
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
        </div>
    </section>

    <!-- Payment Methods Section -->
    <section class="py-16 bg-neutral-950 border-t border-neutral-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10">
                <h2 class="text-2xl font-bold text-white mb-2">Paiement sécurisé</h2>
                <p class="text-neutral-500">Payez avec votre Mobile Money préféré</p>
            </div>

            <div class="flex flex-wrap items-center justify-center gap-4 sm:gap-6">
                @foreach([
                    ['Orange Money', 'orange-money.png'],
                    ['MTN MoMo', 'mtn-momo.png'],
                    ['Wave', 'wave.png'],
                    ['Moov Money', 'moov-money.png'],
                ] as $p)
                    <div class="flex items-center gap-3 bg-neutral-900 hover:bg-neutral-800 px-5 py-3 rounded-xl border border-neutral-800 transition-colors">
                        <img src="{{ asset('images/payments/' . $p[1]) }}" alt="{{ $p[0] }}" class="h-10 w-10 object-contain rounded">
                        <span class="text-white font-medium hidden sm:block">{{ $p[0] }}</span>
                    </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="py-20 bg-neutral-900">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="font-display text-2xl sm:text-3xl font-bold text-white text-center mb-10">
                Questions fréquentes
            </h2>

            <div x-data="{ active: null }" class="space-y-3">
                @php
                    $faqs = [
                        ['q' => 'Quel plan choisir ?', 'a' => 'Stand (' . $fmt($price('stand')) . ' F/mois ou ' . $fmt($standWeekly) . ' F/semaine) pour les stands, kiosques et la street food. ' . $restaurantName . ' (' . $fmt($price('essentiel')) . ' F/mois) pour les maquis et restaurants avec salle : caisse, écran cuisine, stock et statistiques. Pro (' . $fmt($price('pro')) . ' F/mois) pour suivre votre stock ingrédient par ingrédient, la rentabilité de chaque plat et les chambres d\'hôtel. Gold sur devis pour les complexes multi-espaces et multi-sites.'],
                        ['q' => 'Comment fonctionne l\'essai gratuit ?', 'a' => 'Vous avez ' . $trialDays . ' jours pour tester gratuitement toutes les fonctions du plan Pro. À la fin de l\'essai, vous choisissez votre plan et payez en Mobile Money. Aucun paiement automatique.'],
                        ['q' => 'La livraison est-elle vraiment incluse ?', 'a' => 'Oui, dans tous les plans. MenuPro prend une commission de 12 % uniquement sur les commandes livrées par nos livreurs (10 % avec le plan Pro). Les commandes sur place et à emporter ne sont pas concernées.'],
                        ['q' => 'Que se passe-t-il si je dépasse le nombre de commandes de mon plan ?', 'a' => 'Rien de bloquant : aucune commande n\'est jamais refusée. Vous recevez simplement une notification vous proposant le plan supérieur.'],
                        ['q' => 'Puis-je changer de plan ?', 'a' => 'Oui, à tout moment depuis votre tableau de bord, vers le haut comme vers le bas.'],
                        ['q' => 'Comment fonctionnent les réductions ?', 'a' => 'En payant 3, 6 ou 12 mois d\'avance, vous économisez 10 %, 15 % ou 20 %. Le plan Stand peut aussi se payer à la semaine (' . $fmt($standWeekly) . ' F).'],
                        ['q' => 'Comment mes clients me paient-ils ?', 'a' => 'Vos clients paient via Orange Money, MTN MoMo, Wave ou Moov Money. L\'argent arrive sur votre portefeuille MenuPro, retirable à tout moment sur votre Mobile Money.'],
                    ];
                @endphp
                @foreach($faqs as $i => $faq)
                <div class="bg-neutral-950 rounded-xl border border-neutral-800 overflow-hidden">
                    <button @click="active = active === {{ $i }} ? null : {{ $i }}" class="w-full text-left flex items-center justify-between p-5">
                        <span class="font-semibold text-white text-sm sm:text-base pr-4">{{ $faq['q'] }}</span>
                        <svg :class="{ 'rotate-180': active === {{ $i }} }" class="w-5 h-5 text-neutral-500 transition-transform flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="active === {{ $i }}" x-collapse>
                        <p class="px-5 pb-5 text-sm text-neutral-400 leading-relaxed">{{ $faq['a'] }}</p>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 bg-gradient-to-br from-neutral-900 to-neutral-950 border-t border-neutral-800">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="font-display text-3xl sm:text-4xl font-bold text-white mb-4">
                Prêt à digitaliser votre restaurant ?
            </h2>
            <p class="text-lg text-neutral-400 mb-8">
                Rejoignez les restaurants ivoiriens qui boostent leurs commandes avec MenuPro.
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mb-6">
                <a href="{{ route('register') }}?plan=essentiel" class="flex items-center justify-center gap-2 bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white font-bold py-3.5 px-8 rounded-xl transition-all shadow-lg shadow-primary-500/20 hover:shadow-primary-500/30 hover:scale-[1.02]">
                    Commencer l'essai gratuit
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                    </svg>
                </a>
                <a href="{{ route('contact') }}?type=demo" class="flex items-center justify-center gap-2 border-2 border-neutral-700 hover:border-neutral-600 text-neutral-300 hover:text-white font-semibold py-3.5 px-8 rounded-xl transition-all">
                    Voir une démo
                </a>
            </div>

            <div class="flex flex-wrap items-center justify-center gap-x-6 gap-y-2 text-sm text-neutral-500">
                <span>Dès {{ $fmt($standWeekly) }} F/semaine</span>
                <span class="text-neutral-700">&middot;</span>
                <span>{{ $trialDays }} jours gratuits</span>
                <span class="text-neutral-700">&middot;</span>
                <span>Sans engagement</span>
            </div>
        </div>
    </section>

    @push('scripts')
    <script>
        function pricingCalculator() {
            return {
                billingCycle: 'monthly',
                cycles: @js(collect(config('plans.billing_periods'))->except('weekly')->map(fn ($p, $id) => ['id' => $id, 'label' => $p['label'], 'months' => $p['months'], 'discount' => $p['discount']])->values()),

                init() {
                    this.billingCycle = 'monthly';
                },

                getCurrentCycle() {
                    return this.cycles.find(c => c.id === this.billingCycle);
                },

                getMonths() {
                    return this.getCurrentCycle().months;
                },

                // Même calcul que Subscription::calculatePriceWithDiscount()
                computePrice(monthlyPrice) {
                    const cycle = this.getCurrentCycle();
                    return Math.floor(monthlyPrice * cycle.months * (1 - cycle.discount / 100));
                },

                get cycleLabel() {
                    const labels = {
                        monthly: 'par mois',
                        quarterly: 'par trimestre',
                        semiannual: 'par semestre',
                        annual: 'par an',
                    };
                    return labels[this.billingCycle];
                },

                formatPrice(price) {
                    return new Intl.NumberFormat('fr-FR').format(Math.round(price));
                }
            }
        }
    </script>
    @endpush
</x-layouts.public>
