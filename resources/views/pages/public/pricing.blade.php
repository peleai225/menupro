<x-layouts.public title="Tarifs" description="Tarifs MenuPro : 3 plans adaptés aux restaurants ivoiriens, à partir de 15 000 FCFA/mois. Essai gratuit 7 jours.">
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
                    "text": "Essentiel (15 000 FCFA/mois) pour les petits maquis. Pro (25 000 FCFA/mois) pour les restaurants avec livraison et stock. Business (45 000 FCFA/mois) pour les grands restaurants et chaines."
                }
            },
            {
                "@@type": "Question",
                "name": "Y a-t-il un essai gratuit ?",
                "acceptedAnswer": {
                    "@@type": "Answer",
                    "text": "Oui, 7 jours d'essai gratuit sur tous les plans. Aucune carte bancaire requise."
                }
            },
            {
                "@@type": "Question",
                "name": "Puis-je changer de plan ?",
                "acceptedAnswer": {
                    "@@type": "Answer",
                    "text": "Oui, vous pouvez upgrader ou downgrader à tout moment depuis votre dashboard."
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
                7 jours d'essai gratuit
            </div>
            <h1 class="font-display text-4xl md:text-5xl lg:text-6xl font-bold text-white">
                Un plan pour chaque<br class="sm:hidden"> <span class="text-primary-400">restaurant</span>
            </h1>
            <p class="text-lg sm:text-xl text-neutral-400 mt-6 max-w-2xl mx-auto leading-relaxed">
                Du petit maquis de Daloa au grand restaurant d'Abidjan. Commencez a 15 000 F, evoluez quand vous grandissez.
            </p>

            <!-- Trust badges -->
            <div class="flex flex-wrap items-center justify-center gap-x-6 gap-y-3 mt-10 text-sm text-neutral-500">
                <span class="flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-secondary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    7 jours gratuits
                </span>
                <span class="flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-secondary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    Sans engagement
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

            <!-- Plans Grid - 3 columns -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 lg:gap-8 max-w-6xl mx-auto">

                <!-- ESSENTIEL PLAN -->
                <div class="relative group">
                    <div class="h-full bg-neutral-900 rounded-3xl p-6 sm:p-8 border border-neutral-800 hover:border-neutral-700 transition-all flex flex-col">
                        <!-- Badge -->
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-neutral-800 rounded-full text-xs font-semibold text-neutral-300 mb-4 self-start">
                            <svg class="w-3.5 h-3.5 text-accent-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                            Petits maquis
                        </div>

                        <h3 class="text-2xl font-bold text-white mb-1">Essentiel</h3>
                        <p class="text-sm text-neutral-500 mb-6">Pour demarrer et recevoir vos premieres commandes en ligne.</p>

                        <!-- Price -->
                        <div class="mb-6 pb-6 border-b border-neutral-800">
                            <div class="flex items-baseline gap-2">
                                <span class="text-4xl font-bold text-white" x-text="formatPrice(essentielPrice)"></span>
                                <span class="text-neutral-500 text-sm">F</span>
                            </div>
                            <div class="text-xs text-neutral-500 mt-2">
                                <span x-text="cycleLabel"></span>
                                <template x-if="billingCycle !== 'monthly'">
                                    <span class="text-secondary-400 block mt-1">soit <span x-text="formatPrice(Math.round(essentielPrice / getMonths()))"></span> F/mois</span>
                                </template>
                            </div>
                            <template x-if="billingCycle !== 'monthly'">
                                <div class="mt-2 text-xs text-secondary-400 font-medium">
                                    Vous economisez <span x-text="formatPrice(15000 * getMonths() - essentielPrice)"></span> F
                                </div>
                            </template>
                        </div>

                        <!-- CTA -->
                        <a :href="'{{ route('register') }}?plan=essentiel&cycle=' + billingCycle"
                           class="block w-full text-center py-3 px-6 rounded-xl font-bold text-sm border-2 border-neutral-700 text-white hover:bg-neutral-800 hover:border-neutral-600 transition-all mb-6">
                            Essayer 7 jours gratuit
                        </a>

                        <!-- Features -->
                        <p class="text-xs font-bold text-neutral-500 uppercase tracking-wider mb-3">Inclus</p>
                        <ul class="space-y-2.5 flex-1">
                            @foreach([
                                ['25 plats', true],
                                ['8 categories', true],
                                ['1 compte employe', true],
                                ['200 commandes/mois', true],
                                ['Menu public + QR codes', true],
                                ['Paiement Mobile Money', true],
                                ['Support WhatsApp', true],
                                ['Statistiques basiques', true],
                                ['Gestion de stock', false],
                                ['Livraison integree', false],
                                ['Analytics avances', false],
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

                <!-- PRO PLAN (Featured) -->
                <div class="relative group">
                    <!-- Featured badge -->
                    <div class="absolute -top-4 left-1/2 -translate-x-1/2 z-10">
                        <div class="bg-gradient-to-r from-primary-500 to-primary-600 text-white px-5 py-1.5 rounded-full text-xs font-bold shadow-lg shadow-primary-500/30 flex items-center gap-1.5 whitespace-nowrap">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                            POPULAIRE
                        </div>
                    </div>

                    <!-- Glow effect -->
                    <div class="absolute -inset-1 bg-gradient-to-r from-primary-500 to-accent-500 rounded-3xl opacity-20 blur-xl group-hover:opacity-30 transition-opacity"></div>

                    <div class="relative h-full bg-gradient-to-b from-neutral-900 to-neutral-950 rounded-3xl p-6 sm:p-8 border-2 border-primary-500/50 shadow-2xl shadow-primary-500/10 flex flex-col">
                        <!-- Badge -->
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-primary-500/10 border border-primary-500/20 rounded-full text-xs font-semibold text-primary-400 mb-4 self-start">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                            </svg>
                            Restaurant etabli
                        </div>

                        <h3 class="text-2xl font-bold text-white mb-1">Pro</h3>
                        <p class="text-sm text-neutral-400 mb-6">Stock, livraison et analytiques pour gerer votre restaurant comme un pro.</p>

                        <!-- Price -->
                        <div class="mb-6 pb-6 border-b border-neutral-800">
                            <div class="flex items-baseline gap-2">
                                <span class="text-4xl font-bold bg-gradient-to-r from-primary-400 to-primary-500 bg-clip-text text-transparent" x-text="formatPrice(proPrice)"></span>
                                <span class="text-neutral-500 text-sm">F</span>
                            </div>
                            <div class="text-xs text-neutral-500 mt-2">
                                <span x-text="cycleLabel"></span>
                                <template x-if="billingCycle !== 'monthly'">
                                    <span class="text-secondary-400 block mt-1">soit <span x-text="formatPrice(Math.round(proPrice / getMonths()))"></span> F/mois</span>
                                </template>
                            </div>
                            <template x-if="billingCycle !== 'monthly'">
                                <div class="mt-2 text-xs text-secondary-400 font-medium">
                                    Vous economisez <span x-text="formatPrice(25000 * getMonths() - proPrice)"></span> F
                                </div>
                            </template>
                        </div>

                        <!-- CTA -->
                        <a :href="'{{ route('register') }}?plan=pro&cycle=' + billingCycle"
                           class="flex items-center justify-center gap-2 w-full py-3 px-6 rounded-xl font-bold text-sm bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white transition-all shadow-lg shadow-primary-500/20 hover:shadow-primary-500/30 hover:scale-[1.02] mb-6">
                            Essayer 7 jours gratuit
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                            </svg>
                        </a>

                        <!-- Features -->
                        <p class="text-xs font-bold text-primary-400 uppercase tracking-wider mb-3">Tout Essentiel, plus</p>
                        <ul class="space-y-2.5 flex-1">
                            @foreach([
                                '80 plats + 20 categories',
                                '3 comptes employes',
                                '1 000 commandes/mois',
                                'Gestion de stock complete',
                                'Alertes stock automatiques',
                                'Gestion livraison integree',
                                'Analytics & rapports detailles',
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

                <!-- BUSINESS PLAN -->
                <div class="relative group">
                    <div class="h-full bg-neutral-900 rounded-3xl p-6 sm:p-8 border border-neutral-800 hover:border-neutral-700 transition-all flex flex-col">
                        <!-- Badge -->
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-500/10 border border-amber-500/20 rounded-full text-xs font-semibold text-amber-400 mb-4 self-start">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                            </svg>
                            Grand restaurant
                        </div>

                        <h3 class="text-2xl font-bold text-white mb-1">Business</h3>
                        <p class="text-sm text-neutral-500 mb-6">Tout illimite. Pour les restaurants qui voient grand.</p>

                        <!-- Price -->
                        <div class="mb-6 pb-6 border-b border-neutral-800">
                            <div class="flex items-baseline gap-2">
                                <span class="text-4xl font-bold text-white" x-text="formatPrice(businessPrice)"></span>
                                <span class="text-neutral-500 text-sm">F</span>
                            </div>
                            <div class="text-xs text-neutral-500 mt-2">
                                <span x-text="cycleLabel"></span>
                                <template x-if="billingCycle !== 'monthly'">
                                    <span class="text-secondary-400 block mt-1">soit <span x-text="formatPrice(Math.round(businessPrice / getMonths()))"></span> F/mois</span>
                                </template>
                            </div>
                            <template x-if="billingCycle !== 'monthly'">
                                <div class="mt-2 text-xs text-secondary-400 font-medium">
                                    Vous economisez <span x-text="formatPrice(45000 * getMonths() - businessPrice)"></span> F
                                </div>
                            </template>
                        </div>

                        <!-- CTA -->
                        <a :href="'{{ route('register') }}?plan=business&cycle=' + billingCycle"
                           class="block w-full text-center py-3 px-6 rounded-xl font-bold text-sm border-2 border-amber-500/50 text-amber-400 hover:bg-amber-500/10 hover:border-amber-500 transition-all mb-6">
                            Essayer 7 jours gratuit
                        </a>

                        <!-- Features -->
                        <p class="text-xs font-bold text-amber-400 uppercase tracking-wider mb-3">Tout Pro, plus</p>
                        <ul class="space-y-2.5 flex-1">
                            @foreach([
                                'Plats illimites',
                                'Categories illimitees',
                                '10 comptes employes',
                                'Commandes illimitees',
                                'Support prioritaire (2h)',
                                'Domaine personnalise',
                                'Multi-restaurant',
                                'Cuisine (KDS) avancee',
                                'API & integrations',
                                'Accompagnement dedie',
                            ] as $f)
                                <li class="flex items-start gap-2.5 text-sm text-neutral-200">
                                    <svg class="w-4 h-4 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    <span>{{ $f }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Annual savings highlight -->
            <div class="max-w-6xl mx-auto mt-10">
                <div class="bg-gradient-to-r from-secondary-500/10 to-primary-500/10 border border-secondary-500/20 rounded-2xl p-5 flex flex-col sm:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 bg-secondary-500/20 rounded-full flex items-center justify-center">
                            <svg class="w-5 h-5 text-secondary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-white font-semibold text-sm">Economisez jusqu'a 20% avec l'abonnement annuel</p>
                            <p class="text-neutral-500 text-xs">Soit 2 mois offerts sur le plan Pro</p>
                        </div>
                    </div>
                    <button @click="billingCycle = 'annual'" class="px-5 py-2 bg-secondary-500 hover:bg-secondary-600 text-white text-sm font-bold rounded-xl transition-all whitespace-nowrap">
                        Voir prix annuels
                    </button>
                </div>
            </div>
        </div>
    </section>

    <!-- Comparison Table -->
    <section class="py-20 bg-neutral-900 border-t border-neutral-800">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="font-display text-3xl sm:text-4xl font-bold text-white mb-3">Comparaison detaillee</h2>
                <p class="text-neutral-400">Tout ce que contient chaque plan, en un coup d'oeil</p>
            </div>

            <div class="bg-neutral-950 rounded-2xl border border-neutral-800 overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[600px]">
                        <thead>
                            <tr class="border-b border-neutral-800">
                                <th class="text-left py-4 px-6 text-sm font-semibold text-neutral-400">Fonctionnalite</th>
                                <th class="text-center py-4 px-4 text-sm font-bold text-white">Essentiel</th>
                                <th class="text-center py-4 px-4 text-sm font-bold text-primary-400">Pro</th>
                                <th class="text-center py-4 px-4 text-sm font-bold text-amber-400">Business</th>
                            </tr>
                        </thead>
                        <tbody>
                            @php
                                $compareCategories = [
                                    'Limites' => [
                                        ['Plats', '25', '80', 'Illimite'],
                                        ['Categories', '8', '20', 'Illimite'],
                                        ['Comptes employes', '1', '3', '10'],
                                        ['Commandes / mois', '200', '1 000', 'Illimite'],
                                    ],
                                    'Menu & commandes' => [
                                        ['Page publique + QR codes', true, true, true],
                                        ['Suivi temps reel', true, true, true],
                                        ['Paiement Mobile Money', true, true, true],
                                        ['Promotions & plats du jour', true, true, true],
                                        ['Cuisine (KDS)', true, true, true],
                                    ],
                                    'Gestion' => [
                                        ['Gestion de stock', false, true, true],
                                        ['Alertes stock', false, true, true],
                                        ['Livraison integree', false, true, true],
                                        ['Base clients', false, true, true],
                                        ['Multi-restaurant', false, false, true],
                                    ],
                                    'Analyse & export' => [
                                        ['Statistiques basiques', true, true, true],
                                        ['Analytics avances', false, true, true],
                                        ['Portefeuille & retraits', true, true, true],
                                        ['Export Excel/PDF', false, true, true],
                                    ],
                                    'Support' => [
                                        ['Support WhatsApp', true, true, true],
                                        ['Support prioritaire (2h)', false, false, true],
                                        ['Domaine personnalise', false, false, true],
                                        ['Accompagnement dedie', false, false, true],
                                    ],
                                ];
                            @endphp
                            @foreach($compareCategories as $catName => $items)
                                <tr class="bg-neutral-900/50">
                                    <td colspan="4" class="py-2.5 px-6 text-xs font-bold text-primary-400 uppercase tracking-wider">{{ $catName }}</td>
                                </tr>
                                @foreach($items as $item)
                                    <tr class="border-t border-neutral-800/50 hover:bg-neutral-900/30 transition-colors">
                                        <td class="py-3 px-6 text-sm text-neutral-300">{{ $item[0] }}</td>
                                        @foreach([$item[1], $item[2], $item[3]] as $val)
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
                <h2 class="text-2xl font-bold text-white mb-2">Paiement securise</h2>
                <p class="text-neutral-500">Payez avec votre Mobile Money prefere</p>
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
                Questions frequentes
            </h2>

            <div x-data="{ active: null }" class="space-y-3">
                @php
                    $faqs = [
                        ['q' => 'Quel plan choisir ?', 'a' => 'Essentiel (15 000 F/mois) pour les petits maquis avec un menu simple. Pro (25 000 F/mois) si vous voulez gerer stock, livraison et equipe. Business (45 000 F/mois) pour les grands restaurants qui veulent tout illimite.'],
                        ['q' => 'Comment fonctionne l\'essai gratuit ?', 'a' => 'Vous avez 7 jours pour tester toutes les fonctionnalites de votre plan sans payer. A la fin de l\'essai, vous choisissez de continuer ou non. Aucun paiement automatique.'],
                        ['q' => 'Puis-je changer de plan ?', 'a' => 'Oui ! Passez de Essentiel a Pro ou Business a tout moment depuis votre dashboard. Vous pouvez aussi descendre de plan.'],
                        ['q' => 'Comment fonctionne la reduction annuelle ?', 'a' => 'En payant pour 1 an, vous economisez 20%. Par exemple Pro passe de 300 000 a 240 000 F/an (soit 20 000 F/mois au lieu de 25 000).'],
                        ['q' => 'Comment mes clients me paient-ils ?', 'a' => 'Vos clients paient via Orange Money, MTN MoMo, Wave ou Moov Money. L\'argent arrive sur votre portefeuille MenuPro, retirable a tout moment sur votre Mobile Money.'],
                        ['q' => 'Que se passe-t-il si mon abonnement expire ?', 'a' => 'Votre menu public reste visible mais les nouvelles commandes sont bloquees. Vous recevez un rappel 7 jours avant l\'expiration. Reactivez quand vous voulez.'],
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
                Pret a digitaliser votre restaurant ?
            </h2>
            <p class="text-lg text-neutral-400 mb-8">
                Rejoignez les restaurants ivoiriens qui boostent leurs commandes avec MenuPro.
            </p>

            <div class="flex flex-col sm:flex-row items-center justify-center gap-4 mb-6">
                <a href="{{ route('register') }}?plan=pro" class="flex items-center justify-center gap-2 bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white font-bold py-3.5 px-8 rounded-xl transition-all shadow-lg shadow-primary-500/20 hover:shadow-primary-500/30 hover:scale-[1.02]">
                    Commencer l'essai gratuit
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                    </svg>
                </a>
                <a href="{{ route('contact') }}?type=demo" class="flex items-center justify-center gap-2 border-2 border-neutral-700 hover:border-neutral-600 text-neutral-300 hover:text-white font-semibold py-3.5 px-8 rounded-xl transition-all">
                    Voir une demo
                </a>
            </div>

            <div class="flex flex-wrap items-center justify-center gap-x-6 gap-y-2 text-sm text-neutral-500">
                <span>A partir de 15 000 F/mois</span>
                <span class="text-neutral-700">&middot;</span>
                <span>7 jours gratuits</span>
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
                cycles: [
                    { id: 'monthly', label: 'Mensuel', months: 1, discount: 0 },
                    { id: 'quarterly', label: 'Trimestriel', months: 3, discount: 10 },
                    { id: 'semiannual', label: 'Semestriel', months: 6, discount: 15 },
                    { id: 'annual', label: 'Annuel', months: 12, discount: 20 },
                ],
                basePrices: {
                    essentiel: 15000,
                    pro: 25000,
                    business: 45000,
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
                    return Math.round(discounted / 100) * 100;
                },

                get essentielPrice() {
                    return this.computePrice(this.basePrices.essentiel);
                },

                get proPrice() {
                    return this.computePrice(this.basePrices.pro);
                },

                get businessPrice() {
                    return this.computePrice(this.basePrices.business);
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
