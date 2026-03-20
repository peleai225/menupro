<x-layouts.public title="Tarifs" description="Tarifs et formules MenuPro : un seul plan tout inclus pour digitaliser votre restaurant et accepter les commandes en ligne.">
    <!-- Hero Section -->
    <section class="py-20 sm:py-28 bg-gradient-to-br from-neutral-950 via-neutral-900 to-neutral-950 relative overflow-hidden">
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_30%_50%,rgba(249,115,22,0.08),transparent_60%)]"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center relative z-10">
            <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-primary-500/10 border border-primary-500/20 rounded-full text-primary-400 text-sm font-semibold mb-6">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Tarifs transparents
            </div>
            <h1 class="font-display text-4xl md:text-5xl lg:text-6xl font-bold text-white">
                Un seul plan,<br class="sm:hidden"> <span class="text-primary-400">tout inclus</span>
            </h1>
            <p class="text-lg sm:text-xl text-neutral-400 mt-6 max-w-2xl mx-auto leading-relaxed">
                Pas de choix compliqué. Toutes les fonctionnalités incluses dans un plan unique. Économisez jusqu'à 15% en annuel.
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
    <section class="py-20 bg-neutral-950">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8" x-data="pricingCalculator()" x-init="init()">
                <!-- Left Column: Features & Add-ons -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Features List -->
                    <div class="bg-neutral-900 rounded-2xl p-6 sm:p-8 border border-neutral-800">
                        <h2 class="text-xl sm:text-2xl font-bold text-white mb-2 flex items-center gap-3">
                            <div class="w-8 h-8 bg-primary-500/10 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            Tout est inclus
                        </h2>
                        <p class="text-neutral-500 mb-6 ml-11">Aucune fonctionnalité cachée ou en supplément</p>

                        <!-- Feature Categories -->
                        <div class="space-y-6">
                            <!-- Menu & Catalogue -->
                            <div>
                                <h3 class="text-xs font-bold text-neutral-500 uppercase tracking-wider mb-3 ml-1">Menu & Catalogue</h3>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    @foreach([
                                        'Jusqu\'à 100 plats',
                                        '30 catégories de plats',
                                        'Photos HD par plat',
                                        'Options & variantes (taille, extras)',
                                        'Menu visible sur votre page publique',
                                        'Promotions & plats du jour',
                                    ] as $f)
                                    <div class="flex items-center gap-2.5 p-2.5 rounded-lg hover:bg-neutral-800/50 transition-colors">
                                        <svg class="w-4 h-4 text-primary-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        <span class="text-sm text-neutral-300">{{ $f }}</span>
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Commandes -->
                            <div>
                                <h3 class="text-xs font-bold text-neutral-500 uppercase tracking-wider mb-3 ml-1">Commandes & Paiements</h3>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    @foreach([
                                        '2 000 commandes/mois',
                                        'QR codes par table',
                                        'Suivi commande en temps réel',
                                        'Tableau kanban des commandes',
                                        'Impression ticket de caisse',
                                        'Paiement Mobile Money (Orange, MTN, Wave, Moov)',
                                    ] as $f)
                                    <div class="flex items-center gap-2.5 p-2.5 rounded-lg hover:bg-neutral-800/50 transition-colors">
                                        <svg class="w-4 h-4 text-primary-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        <span class="text-sm text-neutral-300">{{ $f }}</span>
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Gestion -->
                            <div>
                                <h3 class="text-xs font-bold text-neutral-500 uppercase tracking-wider mb-3 ml-1">Gestion & Équipe</h3>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    @foreach([
                                        '5 comptes employés',
                                        'Gestion de stock & ingrédients',
                                        'Alertes stock automatiques',
                                        'Gestion livraison intégrée',
                                        'Réservations en ligne',
                                        'Base clients & historique',
                                    ] as $f)
                                    <div class="flex items-center gap-2.5 p-2.5 rounded-lg hover:bg-neutral-800/50 transition-colors">
                                        <svg class="w-4 h-4 text-primary-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        <span class="text-sm text-neutral-300">{{ $f }}</span>
                                    </div>
                                    @endforeach
                                </div>
                            </div>

                            <!-- Analytics -->
                            <div>
                                <h3 class="text-xs font-bold text-neutral-500 uppercase tracking-wider mb-3 ml-1">Statistiques & Insights</h3>
                                <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                    @foreach([
                                        'Dashboard avec KPIs en temps réel',
                                        'Rapports de vente détaillés',
                                        'Portefeuille & suivi revenus',
                                        'Avis clients & réputation',
                                        'Export données (Excel/PDF)',
                                        'Rapports de stock',
                                    ] as $f)
                                    <div class="flex items-center gap-2.5 p-2.5 rounded-lg hover:bg-neutral-800/50 transition-colors">
                                        <svg class="w-4 h-4 text-primary-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        <span class="text-sm text-neutral-300">{{ $f }}</span>
                                    </div>
                                    @endforeach
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Add-ons Section -->
                    <div class="bg-neutral-900 rounded-2xl p-6 sm:p-8 border border-neutral-800">
                        <h2 class="text-xl sm:text-2xl font-bold text-white mb-2 flex items-center gap-3">
                            <div class="w-8 h-8 bg-primary-500/10 rounded-lg flex items-center justify-center">
                                <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                            </div>
                            Add-ons optionnels
                        </h2>
                        <p class="text-neutral-500 mb-6 ml-11">Personnalisez selon vos besoins</p>
                        <div class="space-y-3">
                            @php
                                $addons = [
                                    ['id' => 'support', 'name' => 'Support Prioritaire', 'price' => 5000, 'description' => 'Réponse garantie sous 2h, assistance technique dédiée', 'icon' => 'M18.364 5.636l-3.536 3.536m0 5.656l3.536 3.536M9.172 9.172L5.636 5.636m3.536 9.192l-3.536 3.536M21 12a9 9 0 11-18 0 9 9 0 0118 0zm-5 0a4 4 0 11-8 0 4 4 0 018 0z'],
                                    ['id' => 'domain', 'name' => 'Domaine Personnalisé', 'price' => 3000, 'description' => 'votre-restaurant.com au lieu de menupro.ci/r/...', 'icon' => 'M21 12a9 9 0 01-9 9m9-9a9 9 0 00-9-9m9 9H3m9 9a9 9 0 01-9-9m9 9c1.657 0 3-4.03 3-9s-1.343-9-3-9m0 18c-1.657 0-3-4.03-3-9s1.343-9 3-9m-9 9a9 9 0 019-9'],
                                    ['id' => 'employees', 'name' => 'Employés Supplémentaires', 'price' => 2000, 'description' => 'Par employé au-delà des 5 inclus', 'icon' => 'M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z'],
                                    ['id' => 'dishes', 'name' => 'Plats Supplémentaires', 'price' => 500, 'description' => 'Lot de 10 plats au-delà des 100 inclus', 'icon' => 'M12 6v6m0 0v6m0-6h6m-6 0H6'],
                                ];
                            @endphp
                            @foreach($addons as $addon)
                                <label class="flex items-center justify-between p-4 rounded-xl border-2 border-neutral-800 hover:border-primary-500/30 cursor-pointer transition-all group"
                                       :class="{ 'border-primary-500 bg-primary-500/5': selectedAddons.includes('{{ $addon['id'] }}') }">
                                    <div class="flex items-center gap-4 flex-1">
                                        <input type="checkbox"
                                               x-model="selectedAddons"
                                               value="{{ $addon['id'] }}"
                                               class="w-5 h-5 rounded-md border-neutral-700 bg-neutral-800 text-primary-500 focus:ring-primary-500 focus:ring-2 cursor-pointer">
                                        <div class="flex-1">
                                            <div class="font-semibold text-white text-sm sm:text-base">{{ $addon['name'] }}</div>
                                            <div class="text-xs sm:text-sm text-neutral-500">{{ $addon['description'] }}</div>
                                        </div>
                                    </div>
                                    <div class="text-right ml-4">
                                        <div class="text-base sm:text-lg font-bold text-primary-400">{{ number_format($addon['price'], 0, ',', ' ') }} F</div>
                                        <div class="text-xs text-neutral-500">/mois</div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Right Column: Sticky Price Card -->
                <div class="lg:col-span-1">
                    <div class="sticky top-8">
                        <div class="relative bg-gradient-to-b from-neutral-900 to-neutral-950 rounded-2xl p-6 sm:p-8 border-2 border-primary-500/20 shadow-2xl shadow-primary-500/5">
                            <!-- Badge MEILLEUR pour Annuel -->
                            <div x-show="billingCycle === 'annual'"
                                 x-transition
                                 class="absolute -top-3.5 left-1/2 -translate-x-1/2 z-10">
                                <div class="bg-gradient-to-r from-primary-500 to-primary-600 text-white px-5 py-1.5 rounded-full text-xs font-bold shadow-lg flex items-center gap-1.5">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                    MEILLEURE OFFRE
                                </div>
                            </div>

                            <div class="text-center mb-6 pt-2">
                                <h3 class="text-2xl sm:text-3xl font-bold text-white mb-1">MenuPro</h3>
                                <p class="text-neutral-500 text-sm">Plan unique &middot; Tout inclus</p>
                            </div>

                            <!-- Billing Cycle Toggle -->
                            <div class="mb-6">
                                <div class="grid grid-cols-2 gap-1.5 bg-neutral-800/60 p-1 rounded-xl">
                                    <template x-for="cycle in cycles" :key="cycle.id">
                                        <button @click="billingCycle = cycle.id"
                                                :class="{
                                                    'bg-primary-500 text-white shadow-md': billingCycle === cycle.id,
                                                    'text-neutral-400 hover:text-white': billingCycle !== cycle.id
                                                }"
                                                class="px-3 py-2 rounded-lg text-xs font-semibold transition-all">
                                            <span x-text="cycle.label"></span>
                                            <template x-if="cycle.discount > 0">
                                                <span class="block text-[10px] opacity-80" x-text="'-' + cycle.discount + '%'"></span>
                                            </template>
                                        </button>
                                    </template>
                                </div>
                            </div>

                            <!-- Price Display -->
                            <div class="text-center mb-6">
                                <div class="mb-1">
                                    <span class="text-4xl sm:text-5xl font-bold text-white" x-text="formatPrice(basePrice)"></span>
                                    <span class="text-neutral-500 text-base ml-1">FCFA</span>
                                </div>
                                <div class="text-sm text-neutral-500" x-show="billingCycle !== 'monthly'">
                                    <span class="text-secondary-400 font-semibold" x-text="'Économisez ' + formatPrice(discountAmount) + ' F'"></span>
                                </div>
                                <div class="text-xs text-neutral-600 mt-1">
                                    <span x-text="'soit ' + formatPrice(basePrice / getMonths()) + ' FCFA/mois'"></span>
                                </div>
                            </div>

                            <!-- Add-ons Total -->
                            <div x-show="addonsTotal > 0"
                                 x-transition
                                 class="mb-4 pt-4 border-t border-neutral-800">
                                <div class="flex justify-between text-sm">
                                    <span class="text-neutral-500">Add-ons</span>
                                    <span class="text-neutral-300 font-medium" x-text="'+ ' + formatPrice(addonsTotal) + ' F'"></span>
                                </div>
                            </div>

                            <!-- Total Price -->
                            <div class="mb-6 pt-4 border-t-2 border-primary-500/30">
                                <div class="flex justify-between items-baseline mb-1">
                                    <span class="text-base font-semibold text-neutral-400">Total</span>
                                    <span class="text-2xl sm:text-3xl font-bold text-primary-400" x-text="formatPrice(totalPrice) + ' F'"></span>
                                </div>
                                <div class="text-xs text-neutral-600 text-right" x-show="billingCycle !== 'monthly'">
                                    <span x-text="'soit ' + formatPrice(totalPrice / getMonths()) + ' FCFA/mois'"></span>
                                </div>
                            </div>

                            <!-- CTA Button -->
                            <a :href="'{{ route('register') }}?plan=menupro&cycle=' + billingCycle + '&addons=' + selectedAddons.join(',')"
                               class="flex items-center justify-center gap-2 w-full bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white font-bold py-3.5 px-6 rounded-xl transition-all shadow-lg shadow-primary-500/20 hover:shadow-primary-500/30 hover:scale-[1.02]">
                                Commencer maintenant
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                                </svg>
                            </a>

                            <div class="flex items-center justify-center gap-4 mt-4 text-xs text-neutral-500">
                                <span class="flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-secondary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Remboursé 7j
                                </span>
                                <span class="flex items-center gap-1">
                                    <svg class="w-3.5 h-3.5 text-secondary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Sans engagement
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Payment Methods Section -->
    <section class="py-16 bg-neutral-900 border-t border-neutral-800">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10">
                <h2 class="text-2xl font-bold text-white mb-2">Paiement sécurisé</h2>
                <p class="text-neutral-500">Payez avec votre Mobile Money préféré</p>
            </div>

            <div class="flex flex-wrap items-center justify-center gap-4 sm:gap-6">
                <div class="flex items-center gap-3 bg-neutral-800/50 px-5 py-3 rounded-xl border border-neutral-700">
                    <img src="{{ asset('images/payments/orange-money.svg') }}" alt="Orange Money" class="h-10 w-10 object-contain rounded">
                    <span class="text-white font-medium hidden sm:block">Orange Money</span>
                </div>
                <div class="flex items-center gap-3 bg-neutral-800/50 px-5 py-3 rounded-xl border border-neutral-700">
                    <img src="{{ asset('images/payments/mtn-momo.svg') }}" alt="MTN MoMo" class="h-10 w-10 object-contain rounded">
                    <span class="text-white font-medium hidden sm:block">MTN MoMo</span>
                </div>
                <div class="flex items-center gap-3 bg-neutral-800/50 px-5 py-3 rounded-xl border border-neutral-700">
                    <img src="{{ asset('images/payments/wave.svg') }}" alt="Wave" class="h-10 w-10 object-contain rounded">
                    <span class="text-white font-medium hidden sm:block">Wave</span>
                </div>
                <div class="flex items-center gap-3 bg-neutral-800/50 px-5 py-3 rounded-xl border border-neutral-700">
                    <img src="{{ asset('images/payments/moov-money.svg') }}" alt="Moov Money" class="h-10 w-10 object-contain rounded">
                    <span class="text-white font-medium hidden sm:block">Moov Money</span>
                </div>
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
    <section class="py-20 bg-neutral-950">
        <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="font-display text-2xl sm:text-3xl font-bold text-white text-center mb-10">
                Questions fréquentes
            </h2>

            <div x-data="{ active: null }" class="space-y-3">
                @php
                    $faqs = [
                        ['q' => 'Pourquoi un seul plan ?', 'a' => 'Nous avons choisi la simplicité ! Un seul plan avec toutes les fonctionnalités incluses. Pas de choix compliqué, pas de fonctionnalités bloquées. Si vous avez besoin de plus, des add-ons sont disponibles.'],
                        ['q' => 'Comment fonctionne la garantie satisfait ou remboursé ?', 'a' => 'Après votre inscription et paiement, votre restaurant est activé sous 24h. Si vous n\'êtes pas satisfait dans les 7 premiers jours, contactez-nous et nous vous remboursons intégralement.'],
                        ['q' => 'Comment fonctionne la réduction annuelle ?', 'a' => 'En choisissant l\'abonnement annuel, vous bénéficiez de 15% de réduction. Au lieu de 300 000 FCFA, vous payez 255 000 FCFA, soit 45 000 FCFA d\'économie.'],
                        ['q' => 'Puis-je changer de cycle de facturation ?', 'a' => 'Oui, vous pouvez passer à un cycle plus long à tout moment. La différence sera calculée au prorata de votre période restante. Contactez-nous pour effectuer le changement.'],
                        ['q' => 'Comment mes clients me paient-ils ?', 'a' => 'Vos clients paient via Orange Money, MTN MoMo, Wave ou Moov Money. Les paiements sont sécurisés et l\'argent est versé directement sur votre portefeuille MenuPro, que vous pouvez retirer à tout moment.'],
                    ];
                @endphp
                @foreach($faqs as $i => $faq)
                <div class="bg-neutral-900 rounded-xl border border-neutral-800 overflow-hidden">
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
                <a href="{{ route('home') }}#demo" class="flex items-center justify-center gap-2 border-2 border-neutral-700 hover:border-neutral-600 text-neutral-300 hover:text-white font-semibold py-3.5 px-8 rounded-xl transition-all">
                    Voir la démo
                </a>
            </div>

            <div class="flex flex-wrap items-center justify-center gap-x-6 gap-y-2 text-sm text-neutral-500">
                <span>25 000 FCFA/mois</span>
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
                selectedAddons: [],
                cycles: [
                    { id: 'monthly', label: 'Mensuel', months: 1, price: 25000, discount: 0 },
                    { id: 'quarterly', label: 'Trimestriel', months: 3, price: 69750, discount: 7, original: 75000 },
                    { id: 'semiannual', label: 'Semestriel', months: 6, price: 130500, discount: 13, original: 150000 },
                    { id: 'annual', label: 'Annuel', months: 12, price: 255000, discount: 15, original: 300000 },
                ],
                addonPrices: {
                    support: 5000,
                    domain: 3000,
                    employees: 2000,
                    dishes: 500,
                },

                init() {
                    this.billingCycle = 'monthly';
                    this.selectedAddons = [];
                },

                getCurrentCycle() {
                    return this.cycles.find(c => c.id === this.billingCycle);
                },

                getMonths() {
                    return this.getCurrentCycle().months;
                },

                get basePrice() {
                    return this.getCurrentCycle().price;
                },

                get discountAmount() {
                    const cycle = this.getCurrentCycle();
                    return cycle.original ? cycle.original - cycle.price : 0;
                },

                get addonsTotal() {
                    const months = this.getMonths();
                    return this.selectedAddons.reduce((total, addonId) => {
                        return total + (this.addonPrices[addonId] * months);
                    }, 0);
                },

                get totalPrice() {
                    return this.basePrice + this.addonsTotal;
                },

                formatPrice(price) {
                    return new Intl.NumberFormat('fr-FR').format(Math.round(price));
                }
            }
        }
    </script>
    @endpush
</x-layouts.public>
