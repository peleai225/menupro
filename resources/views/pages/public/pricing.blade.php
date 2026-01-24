<x-layouts.public title="Tarifs">
    <!-- Hero Section -->
    <section class="py-20 bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <span class="text-orange-500 font-semibold text-sm uppercase tracking-wider">Tarifs transparents</span>
            <h1 class="font-display text-4xl md:text-5xl lg:text-6xl font-bold text-white mt-4">
                Un seul plan, toutes les fonctionnalités
            </h1>
            <p class="text-xl text-slate-300 mt-6 max-w-2xl mx-auto">
                Pas de choix compliqué. Tout est inclus dans un seul plan à prix unique. Économisez jusqu'à 15% avec l'abonnement annuel.
            </p>
        </div>
    </section>

    <!-- Main Pricing Section -->
    <section class="py-20 bg-slate-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8" x-data="pricingCalculator()" x-init="init()">
                <!-- Left Column: Features & Add-ons -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Features List -->
                    <div class="bg-slate-800 rounded-2xl p-8 border border-slate-700">
                        <h2 class="text-2xl font-bold text-white mb-6 flex items-center gap-3">
                            <svg class="w-6 h-6 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Fonctionnalités incluses
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            @php
                                $features = [
                                    ['icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253', 'text' => '100 plats max'],
                                    ['icon' => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z', 'text' => '30 catégories'],
                                    ['icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z', 'text' => '5 employés'],
                                    ['icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01', 'text' => '2 000 commandes/mois'],
                                    ['icon' => 'M13 10V3L4 14h7v7l9-11h-7z', 'text' => 'Gestion livraison'],
                                    ['icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', 'text' => 'Stock en temps réel'],
                                    ['icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', 'text' => 'Statistiques avancées'],
                                    ['icon' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z', 'text' => 'Réservations en ligne'],
                                    ['icon' => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z', 'text' => 'Avis clients'],
                                    ['icon' => 'M3 10h18M7 15h1m4 0h1m-6 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v4a3 3 0 003 3z', 'text' => 'Paiement Mobile Money'],
                                ];
                            @endphp
                            @foreach($features as $feature)
                                <div class="flex items-center gap-3 p-3 rounded-lg hover:bg-slate-700/50 transition-colors group">
                                    <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-orange-500/10 flex items-center justify-center group-hover:bg-orange-500 transition-colors">
                                        <svg class="w-5 h-5 text-orange-500 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $feature['icon'] }}"/>
                                        </svg>
                                    </div>
                                    <span class="text-slate-300 group-hover:text-white transition-colors font-medium">{{ $feature['text'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Add-ons Section -->
                    <div class="bg-slate-800 rounded-2xl p-8 border border-slate-700">
                        <h2 class="text-2xl font-bold text-white mb-6 flex items-center gap-3">
                            <svg class="w-6 h-6 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            Add-ons optionnels
                        </h2>
                        <p class="text-slate-400 mb-6">Personnalisez votre plan selon vos besoins</p>
                        <div class="space-y-4">
                            @php
                                $addons = [
                                    ['id' => 'support', 'name' => 'Support Prioritaire', 'price' => 5000, 'description' => 'Réponse garantie sous 2h'],
                                    ['id' => 'domain', 'name' => 'Domaine Personnalisé', 'price' => 3000, 'description' => 'Votre propre nom de domaine'],
                                    ['id' => 'employees', 'name' => 'Employés Supplémentaires', 'price' => 2000, 'description' => 'Par employé supplémentaire'],
                                    ['id' => 'dishes', 'name' => 'Plats Supplémentaires', 'price' => 500, 'description' => 'Par lot de 10 plats'],
                                ];
                            @endphp
                            @foreach($addons as $addon)
                                <label class="flex items-center justify-between p-4 rounded-lg border-2 border-slate-700 hover:border-orange-500/50 cursor-pointer transition-all group bg-slate-700/30 hover:bg-slate-700/50"
                                       :class="{ 'border-orange-500 bg-slate-700/70': selectedAddons.includes('{{ $addon['id'] }}') }">
                                    <div class="flex items-center gap-4 flex-1">
                                        <input type="checkbox" 
                                               x-model="selectedAddons" 
                                               value="{{ $addon['id'] }}"
                                               class="w-5 h-5 rounded border-slate-600 bg-slate-700 text-orange-500 focus:ring-orange-500 focus:ring-2 cursor-pointer">
                                        <div class="flex-1">
                                            <div class="font-semibold text-white group-hover:text-orange-400 transition-colors">{{ $addon['name'] }}</div>
                                            <div class="text-sm text-slate-400">{{ $addon['description'] }}</div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-lg font-bold text-orange-500">{{ number_format($addon['price'], 0, ',', ' ') }} F</div>
                                        <div class="text-xs text-slate-400">/mois</div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Right Column: Sticky Price Card -->
                <div class="lg:col-span-1">
                    <div class="sticky top-8">
                        <div class="bg-gradient-to-br from-slate-800 to-slate-900 rounded-2xl p-8 border-2 border-orange-500/20 shadow-2xl">
                            <!-- Badge MEILLEUR pour Annuel -->
                            <div x-show="billingCycle === 'annual'" 
                                 x-transition
                                 class="absolute -top-4 left-1/2 -translate-x-1/2 z-10">
                                <div class="bg-gradient-to-r from-orange-500 to-orange-600 text-white px-6 py-2 rounded-full text-sm font-bold shadow-lg flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                    <span>MEILLEUR</span>
                                </div>
                            </div>

                            <div class="text-center mb-8 pt-4">
                                <h3 class="text-3xl font-bold text-white mb-2">MenuPro</h3>
                                <p class="text-slate-400 text-sm">Plan unique • Toutes les fonctionnalités</p>
                            </div>

                            <!-- Billing Cycle Toggle -->
                            <div class="mb-8">
                                <div class="grid grid-cols-2 gap-2 bg-slate-700/50 p-1 rounded-lg">
                                    <template x-for="cycle in cycles" :key="cycle.id">
                                        <button @click="billingCycle = cycle.id"
                                                :class="{
                                                    'bg-orange-500 text-white': billingCycle === cycle.id,
                                                    'text-slate-300 hover:text-white': billingCycle !== cycle.id
                                                }"
                                                class="px-4 py-2 rounded-md text-sm font-semibold transition-all">
                                            <span x-text="cycle.label"></span>
                                        </button>
                                    </template>
                                </div>
                            </div>

                            <!-- Price Display -->
                            <div class="text-center mb-6">
                                <div class="mb-2">
                                    <span class="text-5xl font-bold text-white" x-text="formatPrice(basePrice)"></span>
                                    <span class="text-slate-400 text-lg ml-2">FCFA</span>
                                </div>
                                <div class="text-sm text-slate-400" x-show="billingCycle !== 'monthly'">
                                    <span x-text="'Économisez ' + formatPrice(discountAmount) + ' FCFA'"></span>
                                </div>
                                <div class="text-xs text-slate-500 mt-1" x-show="billingCycle === 'monthly'">
                                    <span x-text="formatPrice(basePrice) + ' FCFA/mois'"></span>
                                </div>
                            </div>

                            <!-- Add-ons Total -->
                            <div x-show="addonsTotal > 0" 
                                 x-transition
                                 class="mb-6 pt-6 border-t border-slate-700">
                                <div class="space-y-2">
                                    <div class="flex justify-between text-sm text-slate-400">
                                        <span>Add-ons sélectionnés</span>
                                        <span x-text="formatPrice(addonsTotal) + ' FCFA'"></span>
                                    </div>
                                    <div class="text-xs text-slate-500" x-show="billingCycle !== 'monthly'">
                                        <span x-text="'(' + formatPrice(addonsTotal / getMonths()) + ' FCFA/mois × ' + getMonths() + ' mois)'"></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Total Price -->
                            <div class="mb-8 pt-6 border-t-2 border-orange-500/30">
                                <div class="flex justify-between items-baseline mb-2">
                                    <span class="text-lg font-semibold text-slate-300">Total</span>
                                    <span class="text-3xl font-bold text-orange-500" x-text="formatPrice(totalPrice)"></span>
                                </div>
                                <div class="text-xs text-slate-400 text-right">
                                    <span x-text="'Soit ' + formatPrice(totalPrice / getMonths()) + ' FCFA/mois'"></span>
                                </div>
                            </div>

                            <!-- CTA Button -->
                            <a :href="'{{ route('register') }}?plan=menupro&cycle=' + billingCycle + '&addons=' + selectedAddons.join(',')"
                               class="block w-full bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white font-bold py-4 px-6 rounded-xl text-center transition-all transform hover:scale-105 shadow-lg hover:shadow-xl mb-4">
                                Commencer maintenant
                            </a>

                            <div class="text-center">
                                <div class="inline-flex items-center gap-2 text-xs text-slate-400">
                                    <svg class="w-4 h-4 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                    <span>Satisfait ou remboursé 7 jours</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Reassurance Section -->
    <section class="py-16 bg-slate-800 border-t border-slate-700">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-12">
                <h2 class="text-2xl font-bold text-white mb-4">Paiement sécurisé</h2>
                <p class="text-slate-400">Vos transactions sont protégées et sécurisées</p>
            </div>
            
            <!-- Payment Methods -->
            <div class="flex flex-wrap items-center justify-center gap-6">
                <!-- Orange Money -->
                <div class="flex items-center gap-3 bg-slate-700/50 px-5 py-3 rounded-xl border border-slate-600">
                    <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcS_vfRbApK454_NJJitH8Cjm4tm6FcBHbYQdA&s" alt="Orange Money" class="h-10 w-auto object-contain rounded">
                    <span class="text-white font-medium">Orange Money</span>
                </div>
                
                <!-- MTN Mobile Money -->
                <div class="flex items-center gap-3 bg-slate-700/50 px-5 py-3 rounded-xl border border-slate-600">
                    <div class="w-10 h-10 bg-yellow-500 rounded-lg flex items-center justify-center">
                        <span class="text-black font-bold text-sm">MTN</span>
                    </div>
                    <span class="text-white font-medium">MTN MoMo</span>
                </div>
                
                <!-- Wave -->
                <div class="flex items-center gap-3 bg-slate-700/50 px-5 py-3 rounded-xl border border-slate-600">
                    <img src="https://play-lh.googleusercontent.com/-Mp3XW7uhwn3KGQxUKGPoc4MbA5ti-3-q23TgoVi9ujBgHWW5n4IySvlG5Exwrxsjw=w240-h480-rw" alt="Wave" class="h-10 w-auto object-contain rounded">
                    <span class="text-white font-medium">Wave</span>
                </div>
                
                <!-- Moov Money -->
                <div class="flex items-center gap-3 bg-slate-700/50 px-5 py-3 rounded-xl border border-slate-600">
                    <img src="https://encrypted-tbn0.gstatic.com/images?q=tbn:ANd9GcR_oO0OKHN4zOlbVzs6iXrmSuZVV-UrqvmGUg&s" alt="Moov Money" class="h-10 w-auto object-contain rounded">
                    <span class="text-white font-medium">Moov Money</span>
                </div>
            </div>
            
            <!-- Security badges -->
            <div class="flex flex-wrap items-center justify-center gap-8 mt-10 pt-8 border-t border-slate-700">
                <div class="flex items-center gap-2 text-slate-400 text-sm">
                    <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>Transactions sécurisées</span>
                </div>
                <div class="flex items-center gap-2 text-slate-400 text-sm">
                    <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                    </svg>
                    <span>Données cryptées</span>
                </div>
                <div class="flex items-center gap-2 text-slate-400 text-sm">
                    <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>Confirmation instantanée</span>
                </div>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="py-20 bg-slate-900">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <h2 class="font-display text-3xl font-bold text-white text-center mb-12">
                Questions sur les tarifs
            </h2>

            <div x-data="{ active: null }" class="space-y-4">
                <div class="bg-slate-800 rounded-xl p-6 border border-slate-700">
                    <button @click="active = active === 1 ? null : 1" class="w-full text-left flex items-center justify-between">
                        <span class="font-semibold text-white">Pourquoi un seul plan ?</span>
                        <svg :class="{ 'rotate-180': active === 1 }" class="w-5 h-5 text-slate-400 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="active === 1" x-collapse class="mt-4">
                        <p class="text-slate-400">
                            Nous avons choisi la simplicité ! Un seul plan avec toutes les fonctionnalités incluses, 
                            sans choix compliqué. Si vous avez besoin de fonctionnalités supplémentaires, des add-ons sont disponibles.
                        </p>
                    </div>
                </div>

                <div class="bg-slate-800 rounded-xl p-6 border border-slate-700">
                    <button @click="active = active === 2 ? null : 2" class="w-full text-left flex items-center justify-between">
                        <span class="font-semibold text-white">Comment fonctionne la réduction annuelle ?</span>
                        <svg :class="{ 'rotate-180': active === 2 }" class="w-5 h-5 text-slate-400 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="active === 2" x-collapse class="mt-4">
                        <p class="text-slate-400">
                            En choisissant l'abonnement annuel, vous bénéficiez d'une réduction de 15% sur le prix total. 
                            Au lieu de payer 300 000 FCFA (25 000 × 12), vous payez seulement 255 000 FCFA, soit une économie de 45 000 FCFA.
                        </p>
                    </div>
                </div>

                <div class="bg-slate-800 rounded-xl p-6 border border-slate-700">
                    <button @click="active = active === 3 ? null : 3" class="w-full text-left flex items-center justify-between">
                        <span class="font-semibold text-white">Comment fonctionne la garantie satisfait ou remboursé ?</span>
                        <svg :class="{ 'rotate-180': active === 3 }" class="w-5 h-5 text-slate-400 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="active === 3" x-collapse class="mt-4">
                        <p class="text-slate-400">
                            Après votre inscription et paiement, votre restaurant est activé sous 24h. Si vous n'êtes pas satisfait dans les 7 premiers jours, contactez-nous et nous vous remboursons intégralement. Testez d'abord notre démo pour découvrir toutes les fonctionnalités.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section class="py-20 bg-gradient-to-br from-slate-800 to-slate-900">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="font-display text-3xl md:text-4xl font-bold text-white mb-6">
                Prêt à digitaliser votre restaurant ?
            </h2>
            <p class="text-xl text-slate-300 mb-8">
                25 000 FCFA/mois • Satisfait ou remboursé 7 jours
            </p>
            <a href="{{ route('register') }}?plan=menupro" class="inline-flex items-center gap-3 bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white font-bold py-4 px-8 rounded-xl transition-all transform hover:scale-105 shadow-lg hover:shadow-xl">
                Créer mon restaurant maintenant
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                </svg>
            </a>
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
                    // Initialize with monthly
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
                    if (cycle.original) {
                        return cycle.original - cycle.price;
                    }
                    return 0;
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
