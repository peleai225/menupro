<x-layouts.public title="Tarifs" description="Tarifs et formules MenuPro : plan Starter à 9 900 FCFA ou plan MenuPro complet pour digitaliser votre restaurant.">
    @push('head')
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "FAQPage",
        "mainEntity": [
            {
                "@@type": "Question",
                "name": "Quel plan choisir entre Starter et MenuPro ?",
                "acceptedAnswer": {
                    "@@type": "Answer",
                    "text": "Starter (9 900 FCFA/mois) convient aux petits maquis et kiosques avec un menu simple. MenuPro (25 000 FCFA/mois) est idéal pour les restaurants qui veulent gérer stock, livraison, équipe et analyser leurs ventes."
                }
            },
            {
                "@@type": "Question",
                "name": "Comment fonctionne la garantie satisfait ou remboursé ?",
                "acceptedAnswer": {
                    "@@type": "Answer",
                    "text": "Votre restaurant est activé sous 24h après paiement. Si vous n'êtes pas satisfait dans les 7 premiers jours, nous vous remboursons intégralement."
                }
            },
            {
                "@@type": "Question",
                "name": "Puis-je changer de plan plus tard ?",
                "acceptedAnswer": {
                    "@@type": "Answer",
                    "text": "Oui, vous pouvez passer du plan Starter au plan MenuPro à tout moment. La différence sera calculée au prorata de votre période restante."
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
            <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-primary-500/10 border border-primary-500/20 rounded-full text-primary-400 text-sm font-semibold mb-6">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Tarifs transparents · Sans engagement
            </div>
            <h1 class="font-display text-4xl md:text-5xl lg:text-6xl font-bold text-white">
                Deux plans adaptés à<br class="sm:hidden"> <span class="text-primary-400">votre activité</span>
            </h1>
            <p class="text-lg sm:text-xl text-neutral-400 mt-6 max-w-2xl mx-auto leading-relaxed">
                Du petit maquis au restaurant établi. Choisissez le plan qui vous correspond, changez quand vous voulez.
            </p>

            <!-- Trust badges -->
            <div class="flex flex-wrap items-center justify-center gap-x-6 gap-y-3 mt-10 text-sm text-neutral-500">
                <span class="flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-secondary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Satisfait ou remboursé 7 jours
                </span>
                <span class="flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-secondary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Activation sous 24h
                </span>
                <span class="flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-secondary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Support WhatsApp
                </span>
            </div>
        </div>
    </section>

    <!-- Main Pricing Section -->
    <section class="py-20 bg-neutral-950" x-data="pricingCalculator()" x-init="init()">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">

            <!-- Billing Cycle Toggle -->
            <div class="flex justify-center mb-12">
                <div class="inline-flex bg-neutral-900 border border-neutral-800 p-1.5 rounded-2xl">
                    <template x-for="cycle in cycles" :key="cycle.id">
                        <button @click="billingCycle = cycle.id"
                                :class="billingCycle === cycle.id ? 'bg-gradient-to-r from-primary-500 to-primary-600 text-white shadow-lg shadow-primary-500/20' : 'text-neutral-400 hover:text-white'"
                                class="relative px-4 sm:px-6 py-2.5 rounded-xl text-sm font-semibold transition-all">
                            <span x-text="cycle.label"></span>
                            <template x-if="cycle.discount > 0">
                                <span class="ml-1.5 inline-block px-1.5 py-0.5 rounded-md bg-secondary-500 text-white text-[10px] font-bold"
                                      x-text="'-' + cycle.discount + '%'"></span>
                            </template>
                        </button>
                    </template>
                </div>
            </div>

            <!-- Plans Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-8 max-w-5xl mx-auto">

                <!-- STARTER PLAN -->
                <div class="relative group">
                    <div class="h-full bg-neutral-900 rounded-3xl p-6 sm:p-8 border border-neutral-800 hover:border-neutral-700 transition-all">
                        <!-- Badge -->
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-neutral-800 rounded-full text-xs font-semibold text-neutral-300 mb-4">
                            <svg class="w-3.5 h-3.5 text-accent-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            Idéal petits maquis
                        </div>

                        <!-- Plan name & description -->
                        <h3 class="text-2xl font-bold text-white mb-1">Starter</h3>
                        <p class="text-sm text-neutral-500 mb-6">Pour démarrer la digitalisation de votre maquis.</p>

                        <!-- Price -->
                        <div class="mb-6 pb-6 border-b border-neutral-800">
                            <div class="flex items-baseline gap-2">
                                <span class="text-5xl font-bold text-white" x-text="formatPrice(starterPrice)"></span>
                                <span class="text-neutral-500 text-sm">FCFA</span>
                            </div>
                            <div class="text-xs text-neutral-500 mt-2">
                                <span x-text="cycleLabel"></span>
                                <template x-if="billingCycle !== 'monthly'">
                                    <span class="text-secondary-400">· soit <span x-text="formatPrice(starterPrice / getMonths())"></span> FCFA/mois</span>
                                </template>
                            </div>
                            <template x-if="billingCycle !== 'monthly'">
                                <div class="mt-2 text-xs text-secondary-400 font-medium">
                                    Économie: <span x-text="formatPrice(9900 * getMonths() - starterPrice)"></span> FCFA
                                </div>
                            </template>
                        </div>

                        <!-- CTA -->
                        <a :href="'{{ route('register') }}?plan=starter&cycle=' + billingCycle"
                           class="block w-full text-center py-3 px-6 rounded-xl font-bold text-sm border-2 border-neutral-700 text-white hover:bg-neutral-800 hover:border-neutral-600 transition-all mb-6">
                            Choisir Starter
                        </a>

                        <!-- Features -->
                        <p class="text-xs font-bold text-neutral-500 uppercase tracking-wider mb-3">Inclus</p>
                        <ul class="space-y-2.5">
                            @foreach([
                                ['Jusqu\'à 30 plats', true],
                                ['10 catégories', true],
                                ['1 compte employé', true],
                                ['300 commandes/mois', true],
                                ['Menu public + QR codes', true],
                                ['Paiement Mobile Money', true],
                                ['Support WhatsApp', true],
                                ['Gestion de stock', false],
                                ['Livraison intégrée', false],
                                ['Analytics avancées', false],
                            ] as $f)
                                <li class="flex items-start gap-2.5 text-sm {{ $f[1] ? 'text-neutral-300' : 'text-neutral-600' }}">
                                    @if($f[1])
                                        <svg class="w-4 h-4 text-primary-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    @else
                                        <svg class="w-4 h-4 text-neutral-700 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                    @endif
                                    <span>{{ $f[0] }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <!-- MENUPRO PLAN (Featured) -->
                <div class="relative group">
                    <!-- Featured badge -->
                    <div class="absolute -top-4 left-1/2 -translate-x-1/2 z-10">
                        <div class="bg-gradient-to-r from-primary-500 to-primary-600 text-white px-5 py-1.5 rounded-full text-xs font-bold shadow-lg shadow-primary-500/30 flex items-center gap-1.5 whitespace-nowrap">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                            LE PLUS POPULAIRE
                        </div>
                    </div>

                    <!-- Glow effect -->
                    <div class="absolute -inset-1 bg-gradient-to-r from-primary-500 to-accent-500 rounded-3xl opacity-20 blur-xl group-hover:opacity-30 transition-opacity"></div>

                    <div class="relative h-full bg-gradient-to-b from-neutral-900 to-neutral-950 rounded-3xl p-6 sm:p-8 border-2 border-primary-500/50 shadow-2xl shadow-primary-500/10">
                        <!-- Badge -->
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-primary-500/10 border border-primary-500/20 rounded-full text-xs font-semibold text-primary-400 mb-4">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                            </svg>
                            Tout inclus
                        </div>

                        <!-- Plan name & description -->
                        <h3 class="text-2xl font-bold text-white mb-1">MenuPro</h3>
                        <p class="text-sm text-neutral-400 mb-6">Le plan complet pour restaurants et maquis établis.</p>

                        <!-- Price -->
                        <div class="mb-6 pb-6 border-b border-neutral-800">
                            <div class="flex items-baseline gap-2">
                                <span class="text-5xl font-bold bg-gradient-to-r from-primary-400 to-primary-500 bg-clip-text text-transparent" x-text="formatPrice(menuproPrice)"></span>
                                <span class="text-neutral-500 text-sm">FCFA</span>
                            </div>
                            <div class="text-xs text-neutral-500 mt-2">
                                <span x-text="cycleLabel"></span>
                                <template x-if="billingCycle !== 'monthly'">
                                    <span class="text-secondary-400">· soit <span x-text="formatPrice(menuproPrice / getMonths())"></span> FCFA/mois</span>
                                </template>
                            </div>
                            <template x-if="billingCycle !== 'monthly'">
                                <div class="mt-2 text-xs text-secondary-400 font-medium">
                                    Économie: <span x-text="formatPrice(25000 * getMonths() - menuproPrice)"></span> FCFA
                                </div>
                            </template>
                        </div>

                        <!-- CTA -->
                        <a :href="'{{ route('register') }}?plan=menupro&cycle=' + billingCycle"
                           class="flex items-center justify-center gap-2 w-full py-3 px-6 rounded-xl font-bold text-sm bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white transition-all shadow-lg shadow-primary-500/20 hover:shadow-primary-500/30 hover:scale-[1.02] mb-6">
                            Choisir MenuPro
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                            </svg>
                        </a>

                        <!-- Features -->
                        <p class="text-xs font-bold text-primary-400 uppercase tracking-wider mb-3">Tout Starter, plus</p>
                        <ul class="space-y-2.5">
                            @foreach([
                                '100 plats + 30 catégories',
                                '5 comptes employés',
                                '2 000 commandes/mois',
                                'Gestion de stock complète',
                                'Alertes stock automatiques',
                                'Bibliothèque ingrédients ivoiriens',
                                'Gestion livraison intégrée',
                                'Analytics & rapports détaillés',
                                'Portefeuille & retraits Mobile Money',
                                'Base clients & historique',
                                'Export Excel / PDF',
                            ] as $f)
                                <li class="flex items-start gap-2.5 text-sm text-neutral-200">
                                    <svg class="w-4 h-4 text-primary-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    <span>{{ $f }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Add-ons Section (only for MenuPro plan) -->
            <div class="max-w-5xl mx-auto mt-16">
                <div class="bg-neutral-900 rounded-2xl p-6 sm:p-8 border border-neutral-800">
                    <div class="flex items-start justify-between mb-6">
                        <div>
                            <h2 class="text-xl sm:text-2xl font-bold text-white mb-1">Add-ons optionnels</h2>
                            <p class="text-sm text-neutral-500">Disponibles avec le plan MenuPro</p>
                        </div>
                        <div class="w-10 h-10 bg-primary-500/10 rounded-xl flex items-center justify-center">
                            <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                        </div>
                    </div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                        @foreach([
                            ['Support Prioritaire', 5000, 'Réponse sous 2h, assistance dédiée', 'M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                            ['Domaine Personnalisé', 3000, 'votre-restaurant.com', 'M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3'],
                            ['Employé supplémentaire', 2000, 'Par employé au-delà des 5', 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857'],
                            ['Lot 10 plats', 500, 'Au-delà des 100 inclus', 'M12 6v6m0 0v6m0-6h6m-6 0H6'],
                        ] as $addon)
                            <div class="flex items-center justify-between p-4 rounded-xl border border-neutral-800 hover:border-primary-500/30 transition-all bg-neutral-800/30">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-primary-500/10 rounded-lg flex items-center justify-center flex-shrink-0">
                                        <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $addon[3] }}"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="font-semibold text-white text-sm">{{ $addon[0] }}</div>
                                        <div class="text-xs text-neutral-500">{{ $addon[2] }}</div>
                                    </div>
                                </div>
                                <div class="text-right ml-4">
                                    <div class="text-sm font-bold text-primary-400">+{{ number_format($addon[1], 0, ',', ' ') }} F</div>
                                    <div class="text-[10px] text-neutral-600">/mois</div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Comparison Table -->
    <section class="py-20 bg-neutral-900 border-t border-neutral-800">
        <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="font-display text-3xl sm:text-4xl font-bold text-white mb-3">Comparaison détaillée</h2>
                <p class="text-neutral-400">Tout ce que contient chaque plan, en un coup d'œil</p>
            </div>

            <div class="bg-neutral-950 rounded-2xl border border-neutral-800 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-neutral-800">
                                <th class="text-left py-4 px-6 text-sm font-semibold text-neutral-400">Fonctionnalité</th>
                                <th class="text-center py-4 px-6 text-sm font-bold text-white">Starter</th>
                                <th class="text-center py-4 px-6 text-sm font-bold text-primary-400">MenuPro</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $compareCategories = [
                                    'Limites' => [
                                        ['Plats', '30', '100'],
                                        ['Catégories', '10', '30'],
                                        ['Comptes employés', '1', '5'],
                                        ['Commandes / mois', '300', '2 000'],
                                    ],
                                    'Menu & commandes' => [
                                        ['Page publique + QR codes', true, true],
                                        ['Suivi temps réel', true, true],
                                        ['Paiement Mobile Money', true, true],
                                        ['Promotions & plats du jour', true, true],
                                        ['Réservations en ligne', false, true],
                                        ['Impression ticket caisse', false, true],
                                    ],
                                    'Gestion' => [
                                        ['Gestion de stock', false, true],
                                        ['Alertes stock', false, true],
                                        ['Livraison intégrée', false, true],
                                        ['Base clients', false, true],
                                    ],
                                    'Analyse' => [
                                        ['Statistiques de base', true, true],
                                        ['Analytics avancés', false, true],
                                        ['Portefeuille & retraits', false, true],
                                        ['Export Excel/PDF', false, true],
                                    ],
                                ];
                            @endphp
                            @foreach($compareCategories as $catName => $items)
                                <tr class="bg-neutral-900/50">
                                    <td colspan="3" class="py-2.5 px-6 text-xs font-bold text-primary-400 uppercase tracking-wider">{{ $catName }}</td>
                                </tr>
                                @foreach($items as $item)
                                    <tr class="border-t border-neutral-800/50 hover:bg-neutral-900/30 transition-colors">
                                        <td class="py-3 px-6 text-sm text-neutral-300">{{ $item[0] }}</td>
                                        <td class="py-3 px-6 text-center">
                                            @if(is_bool($item[1]))
                                                @if($item[1])
                                                    <svg class="w-5 h-5 text-secondary-500 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                @else
                                                    <svg class="w-5 h-5 text-neutral-700 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                                @endif
                                            @else
                                                <span class="text-sm font-semibold text-white">{{ $item[1] }}</span>
                                            @endif
                                        </td>
                                        <td class="py-3 px-6 text-center">
                                            @if(is_bool($item[2]))
                                                @if($item[2])
                                                    <svg class="w-5 h-5 text-primary-500 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                                @else
                                                    <svg class="w-5 h-5 text-neutral-700 inline-block" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                                                @endif
                                            @else
                                                <span class="text-sm font-semibold text-primary-400">{{ $item[2] }}</span>
                                            @endif
                                        </td>
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

            <div class="flex flex-wrap items-center justify-center gap-6 sm:gap-8 mt-10 pt-8 border-t border-neutral-800">
                <div class="flex items-center gap-2 text-neutral-500 text-sm">
                    <svg class="w-5 h-5 text-secondary-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    Transactions sécurisées
                </div>
                <div class="flex items-center gap-2 text-neutral-500 text-sm">
                    <svg class="w-5 h-5 text-secondary-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                    </svg>
                    Données chiffrées
                </div>
                <div class="flex items-center gap-2 text-neutral-500 text-sm">
                    <svg class="w-5 h-5 text-secondary-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    Confirmation instantanée
                </div>
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
                        ['q' => 'Quel plan choisir ?', 'a' => 'Si vous gérez un petit maquis avec un menu simple, commencez avec Starter (9 900 FCFA/mois). Pour un restaurant établi qui veut gérer stock, livraison et équipe, choisissez MenuPro (25 000 FCFA/mois).'],
                        ['q' => 'Puis-je passer de Starter à MenuPro ?', 'a' => 'Oui ! Vous pouvez passer au plan MenuPro à tout moment depuis votre dashboard. La différence est calculée au prorata de votre période restante.'],
                        ['q' => 'Comment fonctionne la garantie 7 jours ?', 'a' => 'Après votre inscription et paiement, votre restaurant est activé sous 24h. Si vous n\'êtes pas satisfait dans les 7 premiers jours, contactez-nous et nous vous remboursons intégralement.'],
                        ['q' => 'Comment fonctionne la réduction annuelle ?', 'a' => 'En choisissant l\'abonnement annuel, vous bénéficiez de 15% de réduction. MenuPro passe de 300 000 à 255 000 FCFA/an (45 000 FCFA d\'économie). Starter passe de 118 800 à 100 980 FCFA/an.'],
                        ['q' => 'Comment mes clients me paient-ils ?', 'a' => 'Vos clients paient via Orange Money, MTN MoMo, Wave ou Moov Money. Les paiements sont sécurisés et l\'argent est versé directement sur votre portefeuille MenuPro, que vous pouvez retirer à tout moment.'],
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
                Rejoignez les restaurants qui utilisent MenuPro pour booster leurs commandes.
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mb-6">
                <a href="{{ route('register') }}?plan=menupro" class="flex items-center justify-center gap-2 bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white font-bold py-3.5 px-8 rounded-xl transition-all shadow-lg shadow-primary-500/20 hover:shadow-primary-500/30 hover:scale-[1.02]">
                    Créer mon restaurant
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                    </svg>
                </a>
                <a href="{{ route('contact') }}?type=demo" class="flex items-center justify-center gap-2 border-2 border-neutral-700 hover:border-neutral-600 text-neutral-300 hover:text-white font-semibold py-3.5 px-8 rounded-xl transition-all">
                    Voir la démo
                </a>
            </div>

            <div class="flex flex-wrap items-center justify-center gap-x-6 gap-y-2 text-sm text-neutral-500">
                <span>À partir de 9 900 FCFA/mois</span>
                <span class="text-neutral-700">&middot;</span>
                <span>Satisfait ou remboursé 7 jours</span>
                <span class="text-neutral-700">&middot;</span>
                <span>Activation sous 24h</span>
            </div>
        </div>
    </section>

    @push('scripts')
    <script>
        function pricingCalculator() {
            return {
                billingCycle: 'monthly',
                cycles: [
                    { id: 'monthly', label: 'Mensuel', months: 1, discount: 0 },
                    { id: 'quarterly', label: 'Trimestriel', months: 3, discount: 7 },
                    { id: 'semiannual', label: 'Semestriel', months: 6, discount: 13 },
                    { id: 'annual', label: 'Annuel', months: 12, discount: 15 },
                ],
                basePrices: {
                    starter: 9900,
                    menupro: 25000,
                },

                init() {
                    this.billingCycle = 'monthly';
                },

                getCurrentCycle() {
                    return this.cycles.find(c => c.id === this.billingCycle);
                },

                getMonths() {
                    return this.getCurrentCycle().months;
                },

                computePrice(monthlyPrice) {
                    const cycle = this.getCurrentCycle();
                    const total = monthlyPrice * cycle.months;
                    const discounted = total * (1 - cycle.discount / 100);
                    // round to nearest 100 FCFA
                    return Math.round(discounted / 100) * 100;
                },

                get starterPrice() {
                    return this.computePrice(this.basePrices.starter);
                },

                get menuproPrice() {
                    return this.computePrice(this.basePrices.menupro);
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
