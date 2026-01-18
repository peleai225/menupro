<x-layouts.public title="Accueil">
    <!-- Hero Section -->
    <section class="relative min-h-[95vh] bg-gradient-to-br from-primary-600 via-primary-500 to-accent-500 overflow-hidden flex items-center">
        <!-- Animated Background -->
        <div class="absolute inset-0">
            <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxnIGZpbGw9IiNmZmZmZmYiIGZpbGwtb3BhY2l0eT0iMC4wNSI+PGNpcmNsZSBjeD0iMzAiIGN5PSIzMCIgcj0iMiIvPjwvZz48L2c+PC9zdmc+')] opacity-30"></div>
        </div>
        
        <!-- Floating Elements -->
        <div class="absolute top-20 left-10 w-72 h-72 bg-white/10 rounded-full blur-3xl animate-float"></div>
        <div class="absolute bottom-20 right-10 w-96 h-96 bg-accent-400/20 rounded-full blur-3xl animate-float" style="animation-delay: 1s;"></div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[500px] h-[500px] bg-white/5 rounded-full blur-3xl animate-float" style="animation-delay: 0.5s;"></div>
        
        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20">
            <div class="grid lg:grid-cols-2 gap-16 items-center">
                <!-- Left Content -->
                <div class="text-center lg:text-left">
                    <div class="inline-flex items-center gap-2 px-4 py-2 bg-white/20 backdrop-blur-md rounded-full text-white text-sm font-medium mb-6 animate-slide-down border border-white/30">
                        <span class="w-2 h-2 bg-secondary-300 rounded-full animate-pulse"></span>
                        Nouveau : Paiement Lygos intégré
                    </div>
                    
                    <h1 class="font-display text-5xl md:text-6xl lg:text-7xl font-bold text-white leading-tight animate-slide-up">
                        Votre menu digital en 
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-secondary-200 to-accent-200">quelques clics</span>
                    </h1>
                    
                    <p class="mt-6 text-xl text-white/90 max-w-xl mx-auto lg:mx-0 animate-slide-up stagger-1 leading-relaxed">
                        Créez votre site de commande en ligne, gérez votre menu et recevez des paiements. 
                        La solution SaaS conçue pour les restaurants ivoiriens.
                    </p>
                    
                    <div class="mt-10 flex flex-col sm:flex-row items-center gap-4 justify-center lg:justify-start animate-slide-up stagger-2">
                        <a href="{{ route('register') }}" class="group btn btn-lg bg-white text-primary-600 hover:bg-secondary-50 shadow-xl hover:shadow-2xl transition-all transform hover:scale-105">
                            Commencer gratuitement
                            <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                            </svg>
                        </a>
                        <a href="#features" class="btn btn-lg bg-white/10 backdrop-blur-md text-white border-2 border-white/30 hover:bg-white/20 transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Découvrir
                        </a>
                    </div>
                    
                    <!-- Stats -->
                    <div class="mt-16 grid grid-cols-3 gap-8 animate-slide-up stagger-3">
                        <div class="text-center lg:text-left">
                            <div class="text-4xl md:text-5xl font-bold text-white">{{ $stats['restaurants'] }}</div>
                            <div class="text-white/70 text-sm mt-1 font-medium">Restaurants</div>
                        </div>
                        <div class="text-center lg:text-left">
                            <div class="text-4xl md:text-5xl font-bold text-white">{{ $stats['orders'] }}</div>
                            <div class="text-white/70 text-sm mt-1 font-medium">Commandes</div>
                        </div>
                        <div class="text-center lg:text-left">
                            <div class="text-4xl md:text-5xl font-bold text-white">{{ $stats['uptime'] }}</div>
                            <div class="text-white/70 text-sm mt-1 font-medium">Uptime</div>
                        </div>
                    </div>
                </div>
                
                <!-- Right Content - Hero Image -->
                <div class="relative hidden lg:block animate-slide-up stagger-2">
                    @php
                        $heroImage = \App\Models\SystemSetting::get('hero_image', '');
                    @endphp
                    @if($heroImage && \Illuminate\Support\Facades\Storage::disk('public')->exists($heroImage))
                        <div class="relative">
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($heroImage) }}" 
                                 alt="MenuPro - Votre menu digital" 
                                 class="w-full h-auto max-w-md mx-auto rounded-2xl shadow-2xl transform hover:scale-105 transition-transform duration-300 object-contain">
                        </div>
                    @else
                        <!-- Fallback: Phone Mockup (si aucune image n'est uploadée) -->
                        <div class="relative">
                            <!-- Phone Mockup -->
                            <div class="relative mx-auto w-[280px] h-[580px] bg-neutral-900 rounded-[3rem] border-[8px] border-neutral-800 shadow-2xl overflow-hidden transform hover:scale-105 transition-transform duration-300">
                                <!-- Phone Screen -->
                                <div class="absolute inset-0 bg-white overflow-y-auto">
                                    <!-- Demo App Header -->
                                    <div class="bg-primary-500 text-white p-4">
                                        <div class="flex items-center gap-3">
                                            <div class="w-12 h-12 bg-white/20 rounded-xl"></div>
                                            <div>
                                                <div class="font-bold">Le Délice</div>
                                                <div class="text-xs text-white/70">Restaurant • Ouvert</div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Categories -->
                                    <div class="flex gap-2 p-4 overflow-x-auto">
                                        <span class="px-3 py-1 bg-primary-500 text-white text-sm rounded-full whitespace-nowrap">Entrées</span>
                                        <span class="px-3 py-1 bg-neutral-100 text-neutral-600 text-sm rounded-full whitespace-nowrap">Plats</span>
                                        <span class="px-3 py-1 bg-neutral-100 text-neutral-600 text-sm rounded-full whitespace-nowrap">Desserts</span>
                                    </div>
                                    
                                    <!-- Menu Items -->
                                    <div class="px-4 space-y-3">
                                        <div class="bg-neutral-50 rounded-xl p-3 flex gap-3">
                                            <div class="w-20 h-20 bg-gradient-to-br from-orange-100 to-orange-200 rounded-lg"></div>
                                            <div class="flex-1">
                                                <div class="font-semibold text-sm">Salade César</div>
                                                <div class="text-xs text-neutral-500">Laitue, croûtons, parmesan</div>
                                                <div class="text-primary-500 font-bold mt-1">3 500 FCFA</div>
                                            </div>
                                        </div>
                                        <div class="bg-neutral-50 rounded-xl p-3 flex gap-3">
                                            <div class="w-20 h-20 bg-gradient-to-br from-red-100 to-red-200 rounded-lg"></div>
                                            <div class="flex-1">
                                                <div class="font-semibold text-sm">Poulet Braisé</div>
                                                <div class="text-xs text-neutral-500">Avec alloco et sauce</div>
                                                <div class="text-primary-500 font-bold mt-1">5 000 FCFA</div>
                                            </div>
                                        </div>
                                        <div class="bg-neutral-50 rounded-xl p-3 flex gap-3">
                                            <div class="w-20 h-20 bg-gradient-to-br from-yellow-100 to-yellow-200 rounded-lg"></div>
                                            <div class="flex-1">
                                                <div class="font-semibold text-sm">Attiéké Poisson</div>
                                                <div class="text-xs text-neutral-500">Poisson braisé traditionnel</div>
                                                <div class="text-primary-500 font-bold mt-1">4 500 FCFA</div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <!-- Phone Notch -->
                                <div class="absolute top-0 left-1/2 -translate-x-1/2 w-32 h-6 bg-neutral-900 rounded-b-2xl"></div>
                            </div>
                            
                            <!-- Floating Cards -->
                            <div class="absolute -top-4 -right-8 bg-white rounded-xl shadow-elevated p-4 animate-float">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-secondary-100 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-secondary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="font-semibold text-sm">Nouvelle commande!</div>
                                        <div class="text-xs text-neutral-500">Il y a 2 min</div>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="absolute -bottom-4 -left-8 bg-white rounded-xl shadow-elevated p-4 animate-float" style="animation-delay: 0.5s;">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center">
                                        <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="font-semibold text-sm">+125 000 FCFA</div>
                                        <div class="text-xs text-neutral-500">Aujourd'hui</div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Scroll Indicator -->
        <div class="absolute bottom-8 left-1/2 -translate-x-1/2 animate-bounce">
            <svg class="w-6 h-6 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/>
            </svg>
        </div>
    </section>

    <!-- Features Section -->
    <section id="features" class="py-24 bg-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Section Header -->
            <div class="text-center max-w-3xl mx-auto mb-16">
                <span class="text-primary-500 font-semibold text-sm uppercase tracking-wider">Fonctionnalités</span>
                <h2 class="font-display text-4xl md:text-5xl font-bold text-neutral-900 mt-4">
                    Tout ce qu'il faut pour <span class="text-gradient">réussir en ligne</span>
                </h2>
                <p class="text-neutral-600 text-lg mt-4">
                    Une plateforme complète conçue spécialement pour les restaurants ivoiriens.
                </p>
            </div>

            <!-- Features Grid -->
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-8">
                <!-- Feature 1 -->
                <div class="group card card-hover p-8 bg-white border-2 border-transparent hover:border-primary-200 hover:shadow-xl transition-all duration-300">
                    <div class="w-16 h-16 bg-gradient-to-br from-primary-100 to-primary-200 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <svg class="w-8 h-8 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-neutral-900 mb-4">Site mobile optimisé</h3>
                    <p class="text-neutral-600 leading-relaxed">
                        Un site de commande responsive, rapide et beau sur tous les appareils. Vos clients commandent en quelques clics.
                    </p>
                </div>

                <!-- Feature 2 -->
                <div class="group card card-hover p-8 bg-white border-2 border-transparent hover:border-secondary-200 hover:shadow-xl transition-all duration-300">
                    <div class="w-16 h-16 bg-gradient-to-br from-secondary-100 to-secondary-200 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <svg class="w-8 h-8 text-secondary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-neutral-900 mb-4">Paiement Lygos</h3>
                    <p class="text-neutral-600 leading-relaxed">
                        Intégration native avec Lygos pour recevoir des paiements Mobile Money (Orange, MTN, Wave) en toute sécurité.
                    </p>
                </div>

                <!-- Feature 3 -->
                <div class="group card card-hover p-8 bg-white border-2 border-transparent hover:border-accent-200 hover:shadow-xl transition-all duration-300">
                    <div class="w-16 h-16 bg-gradient-to-br from-accent-100 to-accent-200 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <svg class="w-8 h-8 text-accent-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-neutral-900 mb-4">Gestion des commandes</h3>
                    <p class="text-neutral-600 leading-relaxed">
                        Dashboard en temps réel pour suivre vos commandes, modifier les statuts et ne jamais rater une vente.
                    </p>
                </div>

                <!-- Feature 4 -->
                <div class="group card card-hover p-8 bg-white border-2 border-transparent hover:border-blue-200 hover:shadow-xl transition-all duration-300">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-100 to-blue-200 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-neutral-900 mb-4">Menu personnalisable</h3>
                    <p class="text-neutral-600 leading-relaxed">
                        Créez des catégories, ajoutez des plats avec photos, définissez vos prix. Mise à jour instantanée.
                    </p>
                </div>

                <!-- Feature 5 -->
                <div class="group card card-hover p-8 bg-white border-2 border-transparent hover:border-yellow-200 hover:shadow-xl transition-all duration-300">
                    <div class="w-16 h-16 bg-gradient-to-br from-yellow-100 to-yellow-200 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <svg class="w-8 h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-neutral-900 mb-4">Statistiques détaillées</h3>
                    <p class="text-neutral-600 leading-relaxed">
                        Analysez vos ventes, identifiez vos plats les plus populaires et prenez des décisions éclairées.
                    </p>
                </div>

                <!-- Feature 6 -->
                <div class="group card card-hover p-8 bg-white border-2 border-transparent hover:border-purple-200 hover:shadow-xl transition-all duration-300">
                    <div class="w-16 h-16 bg-gradient-to-br from-purple-100 to-purple-200 rounded-2xl flex items-center justify-center mb-6 group-hover:scale-110 transition-transform">
                        <svg class="w-8 h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <h3 class="text-2xl font-bold text-neutral-900 mb-4">Multi-utilisateurs</h3>
                    <p class="text-neutral-600 leading-relaxed">
                        Invitez vos employés avec des accès limités pour gérer les commandes sans exposer vos données sensibles.
                    </p>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Preview Section -->
    <section id="pricing" class="py-24 bg-slate-900">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16">
                <span class="text-orange-500 font-semibold text-sm uppercase tracking-wider">Tarifs</span>
                <h2 class="font-display text-4xl md:text-5xl font-bold text-white mt-4">
                    Un seul plan, toutes les fonctionnalités
                </h2>
                <p class="text-slate-300 text-lg mt-4">
                    Pas de choix compliqué. Tout est inclus. Économisez jusqu'à 15% avec l'abonnement annuel.
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
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
                                    ['icon' => 'M3 10h18M7 15h1m4 0h1m-6 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v4a3 3 0 003 3z', 'text' => 'Paiement Lygos'],
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
                    <div x-data="pricingCalculator()" 
                         x-init="init()"
                         class="sticky top-8">
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
                                    <span>14 jours d'essai gratuit</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Link to full pricing page -->
            <div class="text-center mt-12">
                <a href="{{ route('pricing') }}" class="text-orange-500 font-semibold hover:text-orange-400 inline-flex items-center gap-2 transition-colors">
                    Voir tous les détails et les add-ons
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section id="faq" class="py-24 bg-white">
        <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-16">
                <span class="text-primary-500 font-semibold text-sm uppercase tracking-wider">FAQ</span>
                <h2 class="font-display text-4xl md:text-5xl font-bold text-neutral-900 mt-4">
                    Questions fréquentes
                </h2>
            </div>

            <div x-data="{ active: 1 }" class="space-y-4">
                <!-- FAQ Item 1 -->
                <div class="card">
                    <button @click="active = active === 1 ? null : 1" class="w-full p-6 text-left flex items-center justify-between">
                        <span class="font-semibold text-neutral-900">Comment fonctionne l'essai gratuit ?</span>
                        <svg :class="{ 'rotate-180': active === 1 }" class="w-5 h-5 text-neutral-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="active === 1" x-collapse class="px-6 pb-6">
                        <p class="text-neutral-600">
                            Vous pouvez créer votre compte et configurer votre restaurant gratuitement. Votre site sera visible après validation par notre équipe. Vous aurez 14 jours d'essai gratuit pour tester toutes les fonctionnalités.
                        </p>
                    </div>
                </div>

                <!-- FAQ Item 2 -->
                <div class="card">
                    <button @click="active = active === 2 ? null : 2" class="w-full p-6 text-left flex items-center justify-between">
                        <span class="font-semibold text-neutral-900">Quels moyens de paiement sont acceptés ?</span>
                        <svg :class="{ 'rotate-180': active === 2 }" class="w-5 h-5 text-neutral-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="active === 2" x-collapse class="px-6 pb-6">
                        <p class="text-neutral-600">
                            Grâce à notre intégration avec Lygos, vos clients peuvent payer via Orange Money, MTN Mobile Money, Wave et carte bancaire. Vous recevez les paiements directement sur votre compte.
                        </p>
                    </div>
                </div>

                <!-- FAQ Item 3 -->
                <div class="card">
                    <button @click="active = active === 3 ? null : 3" class="w-full p-6 text-left flex items-center justify-between">
                        <span class="font-semibold text-neutral-900">Puis-je annuler mon abonnement ?</span>
                        <svg :class="{ 'rotate-180': active === 3 }" class="w-5 h-5 text-neutral-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="active === 3" x-collapse class="px-6 pb-6">
                        <p class="text-neutral-600">
                            Oui, vous pouvez annuler à tout moment depuis votre tableau de bord. Vos données sont conservées pendant 30 jours après l'expiration, vous permettant de réactiver facilement si besoin.
                        </p>
                    </div>
                </div>

                <!-- FAQ Item 4 -->
                <div class="card">
                    <button @click="active = active === 4 ? null : 4" class="w-full p-6 text-left flex items-center justify-between">
                        <span class="font-semibold text-neutral-900">Comment mes clients accèdent-ils à mon menu ?</span>
                        <svg :class="{ 'rotate-180': active === 4 }" class="w-5 h-5 text-neutral-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="active === 4" x-collapse class="px-6 pb-6">
                        <p class="text-neutral-600">
                            Votre restaurant aura une URL unique (ex: menupro.ci/votre-restaurant). Vous pouvez partager ce lien sur vos réseaux sociaux, par QR code ou SMS. Pas d'application à télécharger pour vos clients.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section id="contact" class="py-32 bg-gradient-to-br from-primary-600 via-primary-500 to-accent-500 relative overflow-hidden">
        <div class="absolute inset-0">
            <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNjAiIGhlaWdodD0iNjAiIHZpZXdCb3g9IjAgMCA2MCA2MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48ZyBmaWxsPSJub25lIiBmaWxsLXJ1bGU9ImV2ZW5vZGQiPjxnIGZpbGw9IiNmZmZmZmYiIGZpbGwtb3BhY2l0eT0iMC4xIj48Y2lyY2xlIGN4PSIzMCIgY3k9IjMwIiByPSIyIi8+PC9nPjwvZz48L3N2Zz4=')] opacity-30"></div>
        </div>
        <div class="absolute top-20 left-10 w-72 h-72 bg-white/10 rounded-full blur-3xl animate-float"></div>
        <div class="absolute bottom-20 right-10 w-96 h-96 bg-accent-400/20 rounded-full blur-3xl animate-float" style="animation-delay: 1s;"></div>
        <div class="relative z-10 max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="font-display text-4xl md:text-5xl lg:text-6xl font-bold text-white leading-tight">
                Prêt à digitaliser votre restaurant ?
            </h2>
            <p class="text-xl md:text-2xl text-white/90 mt-6 max-w-2xl mx-auto leading-relaxed">
                Rejoignez les centaines de restaurants qui font confiance à MenuPro pour développer leur activité en ligne.
            </p>
            <div class="mt-12 flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="{{ route('register') }}" class="group btn btn-lg bg-white text-primary-600 hover:bg-secondary-50 shadow-2xl hover:shadow-3xl transition-all transform hover:scale-105">
                    Créer mon restaurant
                    <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                    </svg>
                </a>
                @php
                    $contactEmail = \App\Models\SystemSetting::get('contact_email', 'contact@menupro.ci');
                @endphp
                <a href="mailto:{{ $contactEmail }}" class="btn btn-lg bg-white/10 backdrop-blur-md text-white border-2 border-white/30 hover:bg-white/20 transition-all">
                    Nous contacter
                </a>
            </div>
            <p class="text-white/80 mt-10 text-sm md:text-base">
                Pas de carte bancaire requise • Configuration en 10 minutes • Support réactif
            </p>
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

