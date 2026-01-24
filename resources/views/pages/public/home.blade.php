<x-layouts.public title="Accueil">
    <!-- Hero Section - Amélioré avec effet 3D -->
    <section class="relative min-h-[100vh] bg-gradient-to-br from-neutral-950 via-neutral-900 to-neutral-950 overflow-hidden flex items-center" 
             x-data="hero3D()" 
             @mousemove.window="handleMouseMove($event)"
             @mouseleave.window="resetRotation()">
        
        <!-- Animated Background Mesh -->
        <div class="absolute inset-0 bg-gradient-mesh"></div>
        
        <!-- Animated Grid Pattern -->
        <div class="absolute inset-0 opacity-20">
            <div class="absolute inset-0 bg-[linear-gradient(rgba(249,115,22,0.03)_1px,transparent_1px),linear-gradient(90deg,rgba(249,115,22,0.03)_1px,transparent_1px)] bg-[size:60px_60px]"></div>
        </div>
        
        <!-- Floating Orbs -->
        <div class="absolute top-20 left-[10%] w-96 h-96 bg-primary-500/20 rounded-full blur-[100px] animate-float"></div>
        <div class="absolute bottom-20 right-[10%] w-80 h-80 bg-accent-500/15 rounded-full blur-[80px] animate-float" style="animation-delay: 1.5s;"></div>
        <div class="absolute top-1/3 right-1/4 w-64 h-64 bg-secondary-500/10 rounded-full blur-[60px] animate-float" style="animation-delay: 0.8s;"></div>
        
        <!-- Floating Particles -->
        <div class="particle top-[20%] left-[15%]" style="animation-delay: 0s;"></div>
        <div class="particle top-[40%] left-[25%]" style="animation-delay: 1s; width: 6px; height: 6px;"></div>
        <div class="particle top-[60%] left-[10%]" style="animation-delay: 2s;"></div>
        <div class="particle top-[30%] right-[20%]" style="animation-delay: 0.5s; width: 10px; height: 10px;"></div>
        <div class="particle top-[70%] right-[15%]" style="animation-delay: 1.5s;"></div>
        
        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24">
            <div class="grid lg:grid-cols-2 gap-12 lg:gap-20 items-center">
                
                <!-- Left Content -->
                <div class="text-center lg:text-left order-2 lg:order-1">
                    <!-- Badge Nouveau -->
                    <div class="inline-flex items-center gap-2 px-5 py-2.5 bg-white/10 backdrop-blur-xl rounded-full text-white text-sm font-medium mb-8 animate-slide-down border border-white/20 badge-pulse">
                        <span class="relative flex h-2.5 w-2.5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-secondary-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-secondary-400"></span>
                        </span>
                        <span>Nouveau : Paiement Mobile Money intégré</span>
                    </div>
                    
                    <!-- Titre principal -->
                    <h1 class="font-display text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-bold text-white leading-[1.1] animate-slide-up">
                        Digitalisez votre 
                        <span class="relative inline-block">
                            <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary-400 via-primary-300 to-accent-400">restaurant</span>
                            <svg class="absolute -bottom-2 left-0 w-full h-3 text-primary-500/30" viewBox="0 0 200 12" preserveAspectRatio="none">
                                <path d="M0,8 Q50,0 100,8 T200,8" stroke="currentColor" stroke-width="4" fill="none"/>
                            </svg>
                        </span>
                        <br class="hidden sm:block">
                        <span class="text-white/90">en quelques clics</span>
                    </h1>
                    
                    <!-- Description -->
                    <p class="mt-6 sm:mt-8 text-lg sm:text-xl text-neutral-300 max-w-xl mx-auto lg:mx-0 animate-slide-up stagger-1 leading-relaxed">
                        Menu en ligne, commandes et paiements <span class="text-primary-400 font-semibold">Orange Money, MTN, Wave</span>. 
                        La solution SaaS pensée pour les restaurants ivoiriens.
                    </p>
                    
                    <!-- CTA Buttons -->
                    <div class="mt-10 flex flex-col sm:flex-row items-center gap-4 justify-center lg:justify-start animate-slide-up stagger-2">
                        <a href="{{ route('register') }}" class="group relative btn btn-lg bg-gradient-to-r from-primary-500 to-primary-600 text-white shadow-xl shadow-primary-500/25 hover:shadow-2xl hover:shadow-primary-500/40 transition-all transform hover:scale-105 hover:-translate-y-0.5 overflow-hidden">
                            <span class="relative z-10 flex items-center gap-2">
                                Démarrer gratuitement
                                <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                                </svg>
                            </span>
                            <div class="absolute inset-0 bg-gradient-to-r from-primary-600 to-accent-500 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                        </a>
                        <a href="{{ route('r.menu', ['slug' => 'demo']) }}" target="_blank" class="group btn btn-lg bg-white/5 backdrop-blur-xl text-white border border-white/20 hover:bg-white/10 hover:border-white/30 transition-all">
                            <svg class="w-5 h-5 text-primary-400 group-hover:scale-110 transition-transform" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M8 5v14l11-7z"/>
                            </svg>
                            <span>Voir la démo</span>
                            <svg class="w-4 h-4 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                            </svg>
                        </a>
                    </div>
                    
                    <!-- Stats améliorées -->
                    <div class="mt-14 sm:mt-16 grid grid-cols-3 gap-4 sm:gap-8 animate-slide-up stagger-3">
                        <div class="text-center lg:text-left group">
                            <div class="text-3xl sm:text-4xl md:text-5xl font-bold text-white group-hover:text-primary-400 transition-colors">
                                {{ $stats['restaurants'] }}
                            </div>
                            <div class="text-neutral-400 text-xs sm:text-sm mt-1 font-medium uppercase tracking-wider">Restaurants</div>
                        </div>
                        <div class="text-center lg:text-left group">
                            <div class="text-3xl sm:text-4xl md:text-5xl font-bold text-white group-hover:text-secondary-400 transition-colors">
                                {{ $stats['orders'] }}
                            </div>
                            <div class="text-neutral-400 text-xs sm:text-sm mt-1 font-medium uppercase tracking-wider">Commandes</div>
                        </div>
                        <div class="text-center lg:text-left group">
                            <div class="text-3xl sm:text-4xl md:text-5xl font-bold text-white group-hover:text-accent-400 transition-colors">
                                {{ $stats['uptime'] }}
                            </div>
                            <div class="text-neutral-400 text-xs sm:text-sm mt-1 font-medium uppercase tracking-wider">Uptime</div>
                        </div>
                    </div>
                    
                    <!-- Trust badges -->
                    <div class="mt-10 flex flex-wrap items-center justify-center lg:justify-start gap-6 animate-slide-up stagger-4">
                        <div class="flex items-center gap-2 text-neutral-400 text-sm">
                            <svg class="w-5 h-5 text-secondary-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>Satisfait ou remboursé</span>
                        </div>
                        <div class="flex items-center gap-2 text-neutral-400 text-sm">
                            <svg class="w-5 h-5 text-secondary-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>25 000 FCFA/mois</span>
                        </div>
                        <div class="flex items-center gap-2 text-neutral-400 text-sm">
                            <svg class="w-5 h-5 text-secondary-500" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>Support WhatsApp</span>
                        </div>
                    </div>
                </div>
                
                <!-- Right Content - Phone 3D Mockup -->
                <div class="relative order-1 lg:order-2 flex justify-center lg:justify-end">
                    @php
                        $heroImage = \App\Models\SystemSetting::get('hero_image', '');
                    @endphp
                    @if($heroImage && \Illuminate\Support\Facades\Storage::disk('public')->exists($heroImage))
                        <div class="relative phone-3d-container">
                            <div class="phone-3d phone-3d-auto" :class="{ 'phone-3d-auto': !isHovering }" :style="phoneStyle">
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($heroImage) }}" 
                                     alt="MenuPro - Votre menu digital" 
                                     class="w-full h-auto max-w-sm rounded-3xl shadow-2xl shadow-black/50 object-contain">
                            </div>
                        </div>
                    @else
                        <!-- Phone Mockup 3D -->
                        <div class="relative phone-3d-container">
                            <!-- Phone avec rotation 3D -->
                            <div class="phone-3d phone-3d-auto" :class="{ 'phone-3d-auto': !isHovering }" :style="phoneStyle">
                                <!-- Cadre du téléphone -->
                                <div class="relative w-[260px] sm:w-[300px] h-[520px] sm:h-[600px] bg-gradient-to-b from-neutral-800 to-neutral-900 rounded-[2.5rem] sm:rounded-[3rem] p-[6px] sm:p-2 shadow-2xl shadow-black/60">
                                    <!-- Boutons latéraux -->
                                    <div class="absolute -left-[3px] top-24 w-[3px] h-8 bg-neutral-700 rounded-l-full"></div>
                                    <div class="absolute -left-[3px] top-36 w-[3px] h-12 bg-neutral-700 rounded-l-full"></div>
                                    <div class="absolute -left-[3px] top-52 w-[3px] h-12 bg-neutral-700 rounded-l-full"></div>
                                    <div class="absolute -right-[3px] top-32 w-[3px] h-16 bg-neutral-700 rounded-r-full"></div>
                                    
                                    <!-- Écran intérieur -->
                                    <div class="relative w-full h-full bg-white rounded-[2rem] sm:rounded-[2.5rem] overflow-hidden">
                                        <!-- Status bar -->
                                        <div class="absolute top-0 left-0 right-0 h-7 bg-primary-500 flex items-center justify-between px-6 text-white text-[10px] z-20">
                                            <span>9:41</span>
                                            <div class="flex items-center gap-1">
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M12 3c-4.97 0-9 4.03-9 9s4.03 9 9 9c.83 0 1.5-.67 1.5-1.5 0-.39-.15-.74-.39-1.01-.23-.26-.38-.61-.38-.99 0-.83.67-1.5 1.5-1.5H16c2.76 0 5-2.24 5-5 0-4.42-4.03-8-9-8z"/></svg>
                                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M1 9l2 2c4.97-4.97 13.03-4.97 18 0l2-2C16.93 2.93 7.08 2.93 1 9zm8 8l3 3 3-3c-1.65-1.66-4.34-1.66-6 0zm-4-4l2 2c2.76-2.76 7.24-2.76 10 0l2-2C15.14 9.14 8.87 9.14 5 13z"/></svg>
                                                <svg class="w-4 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M17 4h-3V2h-4v2H7v18h10V4z"/></svg>
                                            </div>
                                        </div>
                                        
                                        <!-- Notch / Dynamic Island -->
                                        <div class="absolute top-1 left-1/2 -translate-x-1/2 w-20 sm:w-24 h-6 sm:h-7 bg-black rounded-full z-30 flex items-center justify-center gap-2">
                                            <div class="w-2 h-2 bg-neutral-800 rounded-full ring-1 ring-neutral-700"></div>
                                            <div class="w-1 h-1 bg-neutral-700 rounded-full"></div>
                                        </div>
                                        
                                        <!-- Contenu de l'app -->
                                        <div class="pt-8 h-full overflow-hidden bg-neutral-50">
                                            <!-- Header restaurant -->
                                            <div class="bg-gradient-to-r from-primary-500 to-primary-600 text-white p-3 sm:p-4">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-10 h-10 sm:w-12 sm:h-12 bg-white/20 rounded-xl flex items-center justify-center backdrop-blur-sm">
                                                        <span class="text-lg sm:text-xl">🍽️</span>
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <div class="font-bold text-sm sm:text-base truncate">Le Maquis d'Abidjan</div>
                                                        <div class="flex items-center gap-2 text-[10px] sm:text-xs text-white/80">
                                                            <span class="flex items-center gap-1">
                                                                <span class="w-1.5 h-1.5 bg-green-400 rounded-full"></span>
                                                                Ouvert
                                                            </span>
                                                            <span>• 4.8 ⭐</span>
                                                        </div>
                                                    </div>
                                                    <button class="p-2 bg-white/10 rounded-full">
                                                        <svg class="w-4 h-4 sm:w-5 sm:h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                            
                                            <!-- Categories -->
                                            <div class="flex gap-2 p-3 overflow-x-auto scrollbar-hide bg-white border-b border-neutral-100">
                                                <span class="px-3 py-1.5 bg-primary-500 text-white text-xs font-medium rounded-full whitespace-nowrap shadow-sm">🔥 Populaires</span>
                                                <span class="px-3 py-1.5 bg-neutral-100 text-neutral-600 text-xs rounded-full whitespace-nowrap">Entrées</span>
                                                <span class="px-3 py-1.5 bg-neutral-100 text-neutral-600 text-xs rounded-full whitespace-nowrap">Plats</span>
                                                <span class="px-3 py-1.5 bg-neutral-100 text-neutral-600 text-xs rounded-full whitespace-nowrap">Boissons</span>
                                            </div>
                                            
                                            <!-- Menu Items -->
                                            <div class="p-3 space-y-2.5 overflow-y-auto" style="max-height: calc(100% - 160px);">
                                                <!-- Item 1 -->
                                                <div class="bg-white rounded-xl p-2.5 flex gap-2.5 shadow-sm border border-neutral-100 hover:shadow-md transition-shadow">
                                                    <div class="w-16 h-16 sm:w-20 sm:h-20 bg-gradient-to-br from-amber-100 to-orange-200 rounded-lg flex items-center justify-center text-2xl sm:text-3xl flex-shrink-0">
                                                        🍗
                                                    </div>
                                                    <div class="flex-1 min-w-0 flex flex-col justify-between py-0.5">
                                                        <div>
                                                            <div class="font-semibold text-xs sm:text-sm text-neutral-800 truncate">Poulet Braisé</div>
                                                            <div class="text-[10px] sm:text-xs text-neutral-500 line-clamp-2">Avec alloco et sauce piment</div>
                                                        </div>
                                                        <div class="flex items-center justify-between mt-1">
                                                            <div class="text-primary-600 font-bold text-xs sm:text-sm">5 500 F</div>
                                                            <button class="w-6 h-6 bg-primary-500 text-white rounded-full flex items-center justify-center text-sm font-bold shadow-sm">+</button>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <!-- Item 2 -->
                                                <div class="bg-white rounded-xl p-2.5 flex gap-2.5 shadow-sm border border-neutral-100">
                                                    <div class="w-16 h-16 sm:w-20 sm:h-20 bg-gradient-to-br from-yellow-100 to-amber-200 rounded-lg flex items-center justify-center text-2xl sm:text-3xl flex-shrink-0">
                                                        🐟
                                                    </div>
                                                    <div class="flex-1 min-w-0 flex flex-col justify-between py-0.5">
                                                        <div>
                                                            <div class="font-semibold text-xs sm:text-sm text-neutral-800 truncate">Attiéké Poisson</div>
                                                            <div class="text-[10px] sm:text-xs text-neutral-500 line-clamp-2">Poisson braisé, légumes frais</div>
                                                        </div>
                                                        <div class="flex items-center justify-between mt-1">
                                                            <div class="text-primary-600 font-bold text-xs sm:text-sm">4 500 F</div>
                                                            <button class="w-6 h-6 bg-primary-500 text-white rounded-full flex items-center justify-center text-sm font-bold shadow-sm">+</button>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <!-- Item 3 -->
                                                <div class="bg-white rounded-xl p-2.5 flex gap-2.5 shadow-sm border border-neutral-100">
                                                    <div class="w-16 h-16 sm:w-20 sm:h-20 bg-gradient-to-br from-green-100 to-emerald-200 rounded-lg flex items-center justify-center text-2xl sm:text-3xl flex-shrink-0">
                                                        🥗
                                                    </div>
                                                    <div class="flex-1 min-w-0 flex flex-col justify-between py-0.5">
                                                        <div>
                                                            <div class="font-semibold text-xs sm:text-sm text-neutral-800 truncate">Salade Africaine</div>
                                                            <div class="text-[10px] sm:text-xs text-neutral-500 line-clamp-2">Légumes frais, vinaigrette</div>
                                                        </div>
                                                        <div class="flex items-center justify-between mt-1">
                                                            <div class="text-primary-600 font-bold text-xs sm:text-sm">2 500 F</div>
                                                            <button class="w-6 h-6 bg-primary-500 text-white rounded-full flex items-center justify-center text-sm font-bold shadow-sm">+</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Bottom cart bar -->
                                            <div class="absolute bottom-0 left-0 right-0 p-3 bg-gradient-to-t from-neutral-50 via-neutral-50 to-transparent">
                                                <div class="bg-primary-500 text-white rounded-2xl p-3 flex items-center justify-between shadow-lg shadow-primary-500/30">
                                                    <div class="flex items-center gap-2">
                                                        <div class="w-6 h-6 bg-white/20 rounded-full flex items-center justify-center text-xs font-bold">3</div>
                                                        <span class="text-xs sm:text-sm font-medium">Voir le panier</span>
                                                    </div>
                                                    <span class="font-bold text-sm sm:text-base">12 500 F</span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Reflet sur l'écran -->
                                        <div class="phone-screen-glare"></div>
                                        <div class="phone-screen-glare-animated"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Ombre 3D sous le téléphone -->
                            <div class="phone-3d-shadow"></div>
                            
                            <!-- Floating Cards autour du téléphone -->
                            <!-- Notification commande -->
                            <div class="absolute -top-2 -right-4 sm:-top-4 sm:-right-12 bg-white rounded-xl shadow-xl p-3 sm:p-4 badge-float-1 z-20 border border-neutral-100">
                                <div class="flex items-center gap-2 sm:gap-3">
                                    <div class="w-9 h-9 sm:w-11 sm:h-11 bg-gradient-to-br from-secondary-400 to-secondary-500 rounded-full flex items-center justify-center shadow-lg shadow-secondary-500/30">
                                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="font-bold text-xs sm:text-sm text-neutral-800">Nouvelle commande!</div>
                                        <div class="text-[10px] sm:text-xs text-neutral-500">Il y a 2 min • Table 5</div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- CA du jour -->
                            <div class="absolute -bottom-2 -left-4 sm:-bottom-6 sm:-left-12 bg-white rounded-xl shadow-xl p-3 sm:p-4 badge-float-2 z-20 border border-neutral-100">
                                <div class="flex items-center gap-2 sm:gap-3">
                                    <div class="w-9 h-9 sm:w-11 sm:h-11 bg-gradient-to-br from-primary-400 to-primary-500 rounded-full flex items-center justify-center shadow-lg shadow-primary-500/30">
                                        <svg class="w-4 h-4 sm:w-5 sm:h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="font-bold text-xs sm:text-sm text-neutral-800">+125 000 F</div>
                                        <div class="text-[10px] sm:text-xs text-neutral-500">CA du jour</div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Paiement Mobile Money -->
                            <div class="absolute top-1/2 -right-2 sm:-right-16 translate-y-4 bg-white rounded-xl shadow-xl p-2.5 sm:p-3 badge-float-3 z-20 border border-neutral-100">
                                <div class="flex items-center gap-2">
                                    <div class="flex -space-x-1">
                                        <div class="w-6 h-6 sm:w-7 sm:h-7 bg-orange-500 rounded-full flex items-center justify-center text-white text-[10px] font-bold ring-2 ring-white">OM</div>
                                        <div class="w-6 h-6 sm:w-7 sm:h-7 bg-yellow-500 rounded-full flex items-center justify-center text-white text-[10px] font-bold ring-2 ring-white">MTN</div>
                                        <div class="w-6 h-6 sm:w-7 sm:h-7 bg-blue-500 rounded-full flex items-center justify-center text-white text-[10px] font-bold ring-2 ring-white">W</div>
                                    </div>
                                    <span class="text-[10px] sm:text-xs font-medium text-neutral-600 whitespace-nowrap">Paiement<br>intégré</span>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Scroll Indicator amélioré -->
        <div class="absolute bottom-6 sm:bottom-8 left-1/2 -translate-x-1/2 flex flex-col items-center gap-2 animate-fade-in" style="animation-delay: 1s;">
            <span class="text-neutral-500 text-xs uppercase tracking-widest">Découvrir</span>
            <div class="w-6 h-10 border-2 border-neutral-600 rounded-full flex justify-center pt-2">
                <div class="w-1.5 h-3 bg-neutral-400 rounded-full animate-bounce"></div>
            </div>
        </div>
    </section>
    
    <!-- Script Alpine.js pour l'effet 3D -->
    @push('scripts')
    <script>
        function hero3D() {
            return {
                rotateX: 0,
                rotateY: 0,
                isHovering: false,
                
                get phoneStyle() {
                    if (!this.isHovering) return {};
                    return {
                        transform: `rotateY(${this.rotateY}deg) rotateX(${this.rotateX}deg)`
                    };
                },
                
                handleMouseMove(e) {
                    // Calculer la position relative de la souris
                    const rect = this.$el.getBoundingClientRect();
                    const centerX = rect.left + rect.width / 2;
                    const centerY = rect.top + rect.height / 2;
                    
                    // Distance depuis le centre (normalisée entre -1 et 1)
                    const mouseX = (e.clientX - centerX) / (rect.width / 2);
                    const mouseY = (e.clientY - centerY) / (rect.height / 2);
                    
                    // Appliquer une rotation (max ±15 degrés)
                    this.rotateY = mouseX * 12;
                    this.rotateX = -mouseY * 8;
                    this.isHovering = true;
                },
                
                resetRotation() {
                    this.isHovering = false;
                    this.rotateX = 0;
                    this.rotateY = 0;
                }
            }
        }
    </script>
    @endpush

    <!-- Comment ça marche Section -->
    <section id="how-it-works" class="py-20 sm:py-28 bg-gradient-to-b from-neutral-50 to-white relative overflow-hidden">
        <!-- Background decoration -->
        <div class="absolute top-0 left-0 w-72 h-72 bg-primary-100/50 rounded-full blur-3xl -translate-x-1/2 -translate-y-1/2"></div>
        <div class="absolute bottom-0 right-0 w-96 h-96 bg-secondary-100/30 rounded-full blur-3xl translate-x-1/2 translate-y-1/2"></div>
        
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Section Header -->
            <div class="text-center max-w-3xl mx-auto mb-16 sm:mb-20">
                <span class="inline-flex items-center gap-2 px-4 py-2 bg-primary-100 text-primary-700 rounded-full text-sm font-semibold mb-6">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    Simple & Rapide
                </span>
                <h2 class="font-display text-3xl sm:text-4xl md:text-5xl font-bold text-neutral-900 leading-tight">
                    Lancez-vous en <span class="text-gradient">3 étapes</span>
                </h2>
                <p class="text-neutral-600 text-lg mt-4 max-w-2xl mx-auto">
                    Pas besoin d'être expert en technologie. En quelques minutes, votre restaurant est en ligne et prêt à recevoir des commandes.
                </p>
            </div>

            <!-- Steps -->
            <div class="grid md:grid-cols-3 gap-8 lg:gap-12 relative">
                <!-- Connection line (desktop only) -->
                <div class="hidden md:block absolute top-24 left-[20%] right-[20%] h-0.5 bg-gradient-to-r from-primary-200 via-secondary-200 to-accent-200"></div>
                
                <!-- Step 1 -->
                <div class="relative group">
                    <div class="bg-white rounded-3xl p-8 shadow-lg border border-neutral-100 hover:shadow-xl hover:border-primary-200 transition-all duration-300 h-full">
                        <!-- Step number -->
                        <div class="absolute -top-5 left-8 w-10 h-10 bg-gradient-to-br from-primary-500 to-primary-600 rounded-xl flex items-center justify-center text-white font-bold text-lg shadow-lg shadow-primary-500/30 group-hover:scale-110 transition-transform">
                            1
                        </div>
                        
                        <!-- Icon -->
                        <div class="w-16 h-16 bg-primary-100 rounded-2xl flex items-center justify-center mb-6 mt-4 group-hover:bg-primary-200 transition-colors">
                            <svg class="w-8 h-8 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                            </svg>
                        </div>
                        
                        <h3 class="text-xl font-bold text-neutral-900 mb-3">Inscrivez-vous</h3>
                        <p class="text-neutral-600 leading-relaxed">
                            Créez votre compte en 2 minutes. Renseignez le nom de votre restaurant et vos coordonnées. Activation sous 24h.
                        </p>
                        
                        <!-- Mini features -->
                        <div class="mt-6 pt-6 border-t border-neutral-100 flex flex-wrap gap-2">
                            <span class="px-3 py-1 bg-neutral-100 text-neutral-600 rounded-full text-xs font-medium">Email</span>
                            <span class="px-3 py-1 bg-neutral-100 text-neutral-600 rounded-full text-xs font-medium">Téléphone</span>
                            <span class="px-3 py-1 bg-neutral-100 text-neutral-600 rounded-full text-xs font-medium">Nom du resto</span>
                        </div>
                    </div>
                </div>
                
                <!-- Step 2 -->
                <div class="relative group">
                    <div class="bg-white rounded-3xl p-8 shadow-lg border border-neutral-100 hover:shadow-xl hover:border-secondary-200 transition-all duration-300 h-full">
                        <!-- Step number -->
                        <div class="absolute -top-5 left-8 w-10 h-10 bg-gradient-to-br from-secondary-500 to-secondary-600 rounded-xl flex items-center justify-center text-white font-bold text-lg shadow-lg shadow-secondary-500/30 group-hover:scale-110 transition-transform">
                            2
                        </div>
                        
                        <!-- Icon -->
                        <div class="w-16 h-16 bg-secondary-100 rounded-2xl flex items-center justify-center mb-6 mt-4 group-hover:bg-secondary-200 transition-colors">
                            <svg class="w-8 h-8 text-secondary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                        </div>
                        
                        <h3 class="text-xl font-bold text-neutral-900 mb-3">Créez votre menu</h3>
                        <p class="text-neutral-600 leading-relaxed">
                            Ajoutez vos catégories (Entrées, Plats, Boissons...) et vos plats avec photos, descriptions et prix. Simple comme bonjour.
                        </p>
                        
                        <!-- Mini features -->
                        <div class="mt-6 pt-6 border-t border-neutral-100 flex flex-wrap gap-2">
                            <span class="px-3 py-1 bg-neutral-100 text-neutral-600 rounded-full text-xs font-medium">Photos</span>
                            <span class="px-3 py-1 bg-neutral-100 text-neutral-600 rounded-full text-xs font-medium">Prix en FCFA</span>
                            <span class="px-3 py-1 bg-neutral-100 text-neutral-600 rounded-full text-xs font-medium">Catégories</span>
                        </div>
                    </div>
                </div>
                
                <!-- Step 3 -->
                <div class="relative group">
                    <div class="bg-white rounded-3xl p-8 shadow-lg border border-neutral-100 hover:shadow-xl hover:border-accent-200 transition-all duration-300 h-full">
                        <!-- Step number -->
                        <div class="absolute -top-5 left-8 w-10 h-10 bg-gradient-to-br from-accent-500 to-accent-600 rounded-xl flex items-center justify-center text-white font-bold text-lg shadow-lg shadow-accent-500/30 group-hover:scale-110 transition-transform">
                            3
                        </div>
                        
                        <!-- Icon -->
                        <div class="w-16 h-16 bg-accent-100 rounded-2xl flex items-center justify-center mb-6 mt-4 group-hover:bg-accent-200 transition-colors">
                            <svg class="w-8 h-8 text-accent-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                            </svg>
                        </div>
                        
                        <h3 class="text-xl font-bold text-neutral-900 mb-3">Partagez et vendez</h3>
                        <p class="text-neutral-600 leading-relaxed">
                            Partagez votre lien sur WhatsApp, Facebook, ou affichez un QR code. Vos clients commandent et paient directement.
                        </p>
                        
                        <!-- Mini features -->
                        <div class="mt-6 pt-6 border-t border-neutral-100 flex flex-wrap gap-2">
                            <span class="px-3 py-1 bg-orange-100 text-orange-600 rounded-full text-xs font-medium">Orange Money</span>
                            <span class="px-3 py-1 bg-yellow-100 text-yellow-700 rounded-full text-xs font-medium">MTN</span>
                            <span class="px-3 py-1 bg-blue-100 text-blue-600 rounded-full text-xs font-medium">Wave</span>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- CTA -->
            <div class="text-center mt-16">
                <div class="inline-flex flex-col sm:flex-row items-center gap-4">
                    <a href="{{ route('r.menu', ['slug' => 'demo']) }}" target="_blank" class="group btn btn-lg bg-white border-2 border-neutral-200 text-neutral-700 hover:border-primary-500 hover:text-primary-600 shadow-sm hover:shadow-md transition-all">
                        <svg class="w-5 h-5 text-primary-500" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M8 5v14l11-7z"/>
                        </svg>
                        Voir un exemple réel
                        <svg class="w-4 h-4 opacity-50 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                    </a>
                    <a href="{{ route('register') }}" class="btn btn-lg btn-primary shadow-lg shadow-primary-500/25 hover:shadow-xl hover:shadow-primary-500/30 transition-all">
                        Créer mon restaurant
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                    </a>
                </div>
                <p class="text-neutral-500 text-sm mt-4">
                    ✓ 25 000 FCFA/mois tout inclus &nbsp;•&nbsp; ✓ Satisfait ou remboursé &nbsp;•&nbsp; ✓ Support WhatsApp
                </p>
            </div>
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
                    <h3 class="text-2xl font-bold text-neutral-900 mb-4">Paiement Mobile Money</h3>
                    <p class="text-neutral-600 leading-relaxed">
                        Recevez des paiements via Orange Money, MTN MoMo, Wave et Moov Money en toute sécurité.
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
                        <span class="font-semibold text-neutral-900">Comment fonctionne la garantie satisfait ou remboursé ?</span>
                        <svg :class="{ 'rotate-180': active === 1 }" class="w-5 h-5 text-neutral-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                    </button>
                    <div x-show="active === 1" x-collapse class="px-6 pb-6">
                        <p class="text-neutral-600">
                            Après votre inscription et paiement, votre restaurant sera activé sous 24h. Si vous n'êtes pas satisfait dans les 7 premiers jours, nous vous remboursons intégralement, sans questions. Testez d'abord notre démo pour voir les fonctionnalités.
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
                            Vos clients peuvent payer via Orange Money, MTN MoMo, Wave et Moov Money. Les paiements sont sécurisés et vous recevez l'argent directement sur votre compte Mobile Money.
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

