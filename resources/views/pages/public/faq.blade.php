<x-layouts.public title="FAQ - Questions Fréquentes">
    <!-- Hero Section -->
    <section class="relative bg-gradient-to-br from-neutral-900 via-neutral-800 to-neutral-900 py-20 overflow-hidden">
        <div class="absolute inset-0 pattern-dots opacity-30"></div>
        
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center max-w-3xl mx-auto">
                <span class="inline-flex items-center gap-2 px-4 py-2 bg-primary-500/10 border border-primary-500/20 rounded-full text-primary-400 text-sm font-medium mb-6">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Centre d'aide
                </span>
                <h1 class="text-4xl sm:text-5xl font-bold text-white mb-6">
                    Questions Fréquentes
                </h1>
                <p class="text-xl text-neutral-300">
                    Trouvez rapidement les réponses à vos questions sur MenuPro.
                </p>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section class="py-16 lg:py-24 bg-neutral-50">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            
            <!-- Search Box -->
            <div class="mb-12" x-data="{ search: '' }">
                <div class="relative">
                    <svg class="absolute left-4 top-1/2 transform -translate-y-1/2 w-5 h-5 text-neutral-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" 
                           x-model="search"
                           placeholder="Rechercher une question..." 
                           class="block w-full pl-12 pr-4 py-4 text-lg bg-white border border-neutral-200 rounded-xl text-neutral-900 placeholder-neutral-400 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent shadow-sm">
                </div>
            </div>

            <!-- FAQ Categories -->
            <div x-data="{ activeTab: 'general' }" class="space-y-8">
                
                <!-- Category Tabs -->
                <div class="flex flex-wrap gap-2 justify-center">
                    @foreach([
                        'general' => ['label' => 'Général', 'icon' => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                        'pricing' => ['label' => 'Tarifs', 'icon' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z'],
                        'features' => ['label' => 'Fonctionnalités', 'icon' => 'M9.663 17h4.673M12 3v1m6.364 1.636l-.707.707M21 12h-1M4 12H3m3.343-5.657l-.707-.707m2.828 9.9a5 5 0 117.072 0l-.548.547A3.374 3.374 0 0014 18.469V19a2 2 0 11-4 0v-.531c0-.895-.356-1.754-.988-2.386l-.548-.547z'],
                        'technical' => ['label' => 'Technique', 'icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z'],
                    ] as $key => $tab)
                        <button @click="activeTab = '{{ $key }}'"
                                :class="activeTab === '{{ $key }}' ? 'bg-primary-500 text-white' : 'bg-white text-neutral-700 hover:bg-neutral-100'"
                                class="flex items-center gap-2 px-5 py-2.5 rounded-full font-medium transition-colors border border-neutral-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $tab['icon'] }}"/>
                            </svg>
                            {{ $tab['label'] }}
                        </button>
                    @endforeach
                </div>

                <!-- General Questions -->
                <div x-show="activeTab === 'general'" x-transition class="space-y-4">
                    <h2 class="text-2xl font-bold text-neutral-900 mb-6">Questions générales</h2>
                    
                    @foreach([
                        ['q' => "Qu'est-ce que MenuPro ?", 'a' => "MenuPro est une plateforme SaaS qui permet aux restaurants de digitaliser leur activité. Créez votre menu en ligne, recevez des commandes, gérez vos réservations et analysez vos performances depuis un seul tableau de bord."],
                        ['q' => "Comment créer mon restaurant sur MenuPro ?", 'a' => "C'est simple ! Cliquez sur \"Créer mon restaurant\", remplissez le formulaire d'inscription avec les informations de votre établissement, et votre menu sera en ligne en quelques minutes. Vous bénéficiez d'une période d'essai gratuite pour tester toutes les fonctionnalités."],
                        ['q' => "Dois-je avoir des compétences techniques ?", 'a' => "Absolument pas ! MenuPro est conçu pour être utilisé par tous. L'interface est intuitive et ne nécessite aucune connaissance en programmation. Si vous savez utiliser un smartphone, vous saurez utiliser MenuPro."],
                        ['q' => "Puis-je personnaliser l'apparence de mon menu ?", 'a' => "Oui ! Vous pouvez ajouter votre logo, personnaliser les couleurs, ajouter des photos de vos plats, et bien plus. Votre menu reflétera parfaitement l'identité de votre restaurant."],
                        ['q' => "MenuPro fonctionne-t-il sur mobile ?", 'a' => "Oui, MenuPro est entièrement responsive. Vos clients peuvent commander depuis leur smartphone, tablette ou ordinateur. Le tableau de bord de gestion est également accessible sur tous les appareils."],
                    ] as $index => $faq)
                        <div x-data="{ open: false }" class="bg-white rounded-xl border border-neutral-200 overflow-hidden">
                            <button @click="open = !open" class="w-full flex items-center justify-between p-5 text-left hover:bg-neutral-50 transition-colors">
                                <span class="font-semibold text-neutral-900">{{ $faq['q'] }}</span>
                                <svg class="w-5 h-5 text-neutral-500 transition-transform" :class="open && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div x-show="open" x-collapse>
                                <div class="px-5 pb-5 text-neutral-600">
                                    {{ $faq['a'] }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Pricing Questions -->
                <div x-show="activeTab === 'pricing'" x-transition class="space-y-4">
                    <h2 class="text-2xl font-bold text-neutral-900 mb-6">Questions sur les tarifs</h2>
                    
                    @foreach([
                        ['q' => "Combien coûte MenuPro ?", 'a' => "MenuPro propose différents plans adaptés à vos besoins. Consultez notre page Tarifs pour découvrir nos offres. Nous proposons également une période d'essai gratuite pour tester la plateforme."],
                        ['q' => "Y a-t-il des frais cachés ?", 'a' => "Non, absolument aucun frais caché. Le prix affiché est le prix que vous payez. Pas de commission sur les commandes, pas de frais de mise en service, pas de frais de résiliation."],
                        ['q' => "Puis-je changer de plan à tout moment ?", 'a' => "Oui ! Vous pouvez upgrader ou downgrader votre plan à tout moment. Le changement prend effet immédiatement et la facturation est ajustée au prorata."],
                        ['q' => "Quels moyens de paiement acceptez-vous ?", 'a' => "Nous acceptons les paiements par carte bancaire et mobile money (Orange Money, MTN Money, Wave, etc.) via notre partenaire de paiement sécurisé."],
                        ['q' => "Proposez-vous des réductions pour un engagement annuel ?", 'a' => "Oui ! Nous offrons des réductions significatives pour les engagements trimestriels, semestriels et annuels. Plus la durée est longue, plus la réduction est importante."],
                    ] as $faq)
                        <div x-data="{ open: false }" class="bg-white rounded-xl border border-neutral-200 overflow-hidden">
                            <button @click="open = !open" class="w-full flex items-center justify-between p-5 text-left hover:bg-neutral-50 transition-colors">
                                <span class="font-semibold text-neutral-900">{{ $faq['q'] }}</span>
                                <svg class="w-5 h-5 text-neutral-500 transition-transform" :class="open && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div x-show="open" x-collapse>
                                <div class="px-5 pb-5 text-neutral-600">
                                    {{ $faq['a'] }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Features Questions -->
                <div x-show="activeTab === 'features'" x-transition class="space-y-4">
                    <h2 class="text-2xl font-bold text-neutral-900 mb-6">Questions sur les fonctionnalités</h2>
                    
                    @foreach([
                        ['q' => "Comment mes clients passent-ils commande ?", 'a' => "Vos clients scannent le QR code sur leur table ou accèdent à votre menu via le lien que vous partagez. Ils peuvent ensuite parcourir le menu, ajouter des articles au panier et passer commande. Vous recevez une notification instantanée."],
                        ['q' => "Puis-je gérer les commandes en livraison et sur place ?", 'a' => "Oui ! MenuPro supporte tous les types de commandes : sur place, à emporter et en livraison. Vous pouvez activer ou désactiver chaque option selon vos besoins."],
                        ['q' => "Comment fonctionne le système de réservation ?", 'a' => "Les clients peuvent réserver une table directement depuis votre page. Vous recevez une notification et pouvez accepter ou refuser la réservation depuis votre tableau de bord."],
                        ['q' => "Puis-je gérer mon stock avec MenuPro ?", 'a' => "Oui ! Le module de gestion des stocks vous permet de suivre vos ingrédients, définir des alertes de stock bas, et même désactiver automatiquement un plat quand un ingrédient est épuisé."],
                        ['q' => "Comment créer des codes promo ?", 'a' => "Dans votre tableau de bord, accédez à la section \"Codes Promo\". Vous pouvez créer des réductions en pourcentage ou en montant fixe, définir des limites d'utilisation et des dates de validité."],
                        ['q' => "Puis-je avoir plusieurs utilisateurs pour mon restaurant ?", 'a' => "Oui ! Selon votre plan, vous pouvez inviter des membres de votre équipe avec différents niveaux d'accès (admin, employé). Chacun peut gérer les commandes depuis son propre compte."],
                    ] as $faq)
                        <div x-data="{ open: false }" class="bg-white rounded-xl border border-neutral-200 overflow-hidden">
                            <button @click="open = !open" class="w-full flex items-center justify-between p-5 text-left hover:bg-neutral-50 transition-colors">
                                <span class="font-semibold text-neutral-900">{{ $faq['q'] }}</span>
                                <svg class="w-5 h-5 text-neutral-500 transition-transform" :class="open && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div x-show="open" x-collapse>
                                <div class="px-5 pb-5 text-neutral-600">
                                    {{ $faq['a'] }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Technical Questions -->
                <div x-show="activeTab === 'technical'" x-transition class="space-y-4">
                    <h2 class="text-2xl font-bold text-neutral-900 mb-6">Questions techniques</h2>
                    
                    @foreach([
                        ['q' => "Comment imprimer mon QR code ?", 'a' => "Dans votre tableau de bord, accédez à la section \"QR Code\". Vous pouvez télécharger votre QR code en format PNG ou SVG, choisir la taille, et même imprimer un modèle prêt à l'emploi avec le nom de votre restaurant."],
                        ['q' => "Les paiements en ligne sont-ils sécurisés ?", 'a' => "Absolument ! Nous utilisons des partenaires de paiement certifiés et conformes aux normes de sécurité les plus strictes (PCI-DSS). Vos données et celles de vos clients sont protégées par un chiffrement SSL."],
                        ['q' => "Que se passe-t-il si j'ai un problème technique ?", 'a' => "Notre équipe de support est disponible par email et via le formulaire de contact. Nous nous engageons à répondre sous 24h. Les clients des plans Premium bénéficient d'un support prioritaire."],
                        ['q' => "Puis-je exporter mes données ?", 'a' => "Oui ! Vous pouvez exporter vos commandes, clients et statistiques au format CSV. Vos données vous appartiennent et vous pouvez les récupérer à tout moment."],
                        ['q' => "MenuPro est-il disponible hors connexion ?", 'a' => "MenuPro nécessite une connexion internet pour fonctionner. Cependant, le menu public de vos clients est optimisé pour charger rapidement même avec une connexion lente."],
                    ] as $faq)
                        <div x-data="{ open: false }" class="bg-white rounded-xl border border-neutral-200 overflow-hidden">
                            <button @click="open = !open" class="w-full flex items-center justify-between p-5 text-left hover:bg-neutral-50 transition-colors">
                                <span class="font-semibold text-neutral-900">{{ $faq['q'] }}</span>
                                <svg class="w-5 h-5 text-neutral-500 transition-transform" :class="open && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div x-show="open" x-collapse>
                                <div class="px-5 pb-5 text-neutral-600">
                                    {{ $faq['a'] }}
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    <!-- Still have questions -->
    <section class="py-16 bg-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="bg-gradient-to-br from-primary-50 to-orange-50 rounded-2xl p-8 md:p-12 text-center border border-primary-100">
                <div class="w-16 h-16 bg-primary-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-8 h-8 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </div>
                <h2 class="text-2xl font-bold text-neutral-900 mb-4">Vous n'avez pas trouvé votre réponse ?</h2>
                <p class="text-neutral-600 mb-8 max-w-xl mx-auto">
                    Notre équipe est là pour vous aider. Contactez-nous et nous vous répondrons dans les plus brefs délais.
                </p>
                <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
                    <a href="{{ route('contact') }}" class="btn btn-primary">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        Nous contacter
                    </a>
                    <a href="{{ route('register') }}" class="btn btn-outline">
                        Créer mon restaurant
                    </a>
                </div>
            </div>
        </div>
    </section>
</x-layouts.public>
