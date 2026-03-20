<x-layouts.public title="Accueil" description="MenuPro : digitalisez votre restaurant, menu en ligne, commandes et paiement Mobile Money. Solution SaaS pour restaurants en Côte d'Ivoire et ailleurs.">
    <!-- Hero Section - Design moderne avec glassmorphism -->
    <section class="relative min-h-[100vh] bg-gradient-to-br from-neutral-950 via-neutral-900 to-neutral-950 overflow-hidden flex items-center" 
             x-data="{ 
                 rotateX: 0, 
                 rotateY: 0, 
                 isHovering: false,
                 handleMouseMove(e) {
                     const rect = this.$el.getBoundingClientRect();
                     const centerX = rect.left + rect.width / 2;
                     const centerY = rect.top + rect.height / 2;
                     const mouseX = (e.clientX - centerX) / (rect.width / 2);
                     const mouseY = (e.clientY - centerY) / (rect.height / 2);
                     this.rotateY = mouseX * 12;
                     this.rotateX = -mouseY * 8;
                     this.isHovering = true;
                 },
                 resetRotation() {
                     this.isHovering = false;
                     this.rotateX = 0;
                     this.rotateY = 0;
                 }
             }" 
             @mousemove.window="handleMouseMove($event)"
             @mouseleave.window="resetRotation()">
        
        <!-- Animated Background Elements -->
        <div class="absolute inset-0">
            <!-- Gradient Mesh Background -->
            <div class="absolute inset-0 bg-gradient-mesh opacity-80"></div>
            
            <!-- Animated Grid -->
            <div class="absolute inset-0 opacity-10">
                <div class="absolute inset-0 bg-[linear-gradient(rgba(249,115,22,0.1)_1px,transparent_1px),linear-gradient(90deg,rgba(249,115,22,0.1)_1px,transparent_1px)] bg-[size:80px_80px] [mask-image:radial-gradient(ellipse_at_center,black_20%,transparent_70%)]"></div>
            </div>
            
            <!-- Floating Orbs with blur -->
            <div class="absolute top-10 left-[5%] w-[500px] h-[500px] bg-primary-500/30 rounded-full blur-[120px] animate-float"></div>
            <div class="absolute bottom-10 right-[5%] w-[400px] h-[400px] bg-accent-500/20 rounded-full blur-[100px] animate-float" style="animation-delay: 2s;"></div>
            <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[600px] h-[600px] bg-secondary-500/10 rounded-full blur-[150px] animate-float" style="animation-delay: 1s;"></div>
        </div>
        
        <!-- Floating Particles -->
        <div class="absolute inset-0 overflow-hidden pointer-events-none">
            <div class="particle top-[15%] left-[10%]" style="animation-delay: 0s;"></div>
            <div class="particle top-[35%] left-[20%]" style="animation-delay: 1s; width: 6px; height: 6px;"></div>
            <div class="particle top-[55%] left-[8%]" style="animation-delay: 2s;"></div>
            <div class="particle top-[25%] right-[15%]" style="animation-delay: 0.5s; width: 10px; height: 10px;"></div>
            <div class="particle top-[65%] right-[12%]" style="animation-delay: 1.5s;"></div>
            <div class="particle top-[80%] left-[30%]" style="animation-delay: 2.5s; width: 5px; height: 5px;"></div>
            <div class="particle top-[45%] right-[25%]" style="animation-delay: 3s;"></div>
        </div>
        
        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12 lg:py-20">
            <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">
                
                <!-- Left Content -->
                <div class="text-center lg:text-left order-2 lg:order-1" x-data="{ shown: false }" x-intersect.once="shown = true">
                    <!-- Badge Nouveau avec animation -->
                    <div class="inline-flex items-center gap-2.5 px-5 py-2.5 bg-white/10 backdrop-blur-xl rounded-full text-white text-sm font-medium mb-8 border border-white/20 shadow-lg"
                         x-show="shown" 
                         x-transition:enter="transition ease-out duration-500 delay-100"
                         x-transition:enter-start="opacity-0 translate-y-4"
                         x-transition:enter-end="opacity-100 translate-y-0">
                        <span class="relative flex h-2.5 w-2.5">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-secondary-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2.5 w-2.5 bg-secondary-400"></span>
                        </span>
                        <span>Nouveau : Paiement Mobile Money intégré</span>
                    </div>
                    
                    <!-- Titre principal avec animation -->
                    <h1 class="font-display text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-bold text-white leading-[1.05]"
                        x-show="shown" 
                        x-transition:enter="transition ease-out duration-700 delay-200"
                        x-transition:enter-start="opacity-0 translate-y-6"
                        x-transition:enter-end="opacity-100 translate-y-0">
                        Digitalisez votre 
                        <span class="relative inline-block">
                            <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary-400 via-primary-300 to-accent-400 animate-gradient">restaurant</span>
                            <svg class="absolute -bottom-1 left-0 w-full h-3 text-primary-500/40" viewBox="0 0 200 12" preserveAspectRatio="none">
                                <path d="M0,8 Q50,0 100,8 T200,8" stroke="currentColor" stroke-width="3" fill="none"/>
                            </svg>
                        </span>
                        <br class="hidden sm:block">
                        <span class="text-white/90">en quelques clics</span>
                    </h1>
                    
                    <!-- Description -->
                    <p class="mt-6 sm:mt-8 text-lg sm:text-xl text-neutral-300 max-w-xl mx-auto lg:mx-0 leading-relaxed"
                       x-show="shown" 
                       x-transition:enter="transition ease-out duration-700 delay-300"
                       x-transition:enter-start="opacity-0 translate-y-6"
                       x-transition:enter-end="opacity-100 translate-y-0">
                        Menu en ligne, commandes et paiements <span class="text-primary-400 font-semibold">Orange Money, MTN, Wave</span>. 
                        La solution SaaS pensée pour les restaurants ivoiriens.
                    </p>
                    
                    <!-- CTA Buttons -->
                    <div class="mt-10 flex flex-col sm:flex-row items-center gap-4 justify-center lg:justify-start flex-wrap"
                         x-show="shown" 
                         x-transition:enter="transition ease-out duration-700 delay-400"
                         x-transition:enter-start="opacity-0 translate-y-6"
                         x-transition:enter-end="opacity-100 translate-y-0">
                        <a href="{{ route('register') }}" class="group relative inline-flex items-center justify-center gap-2 btn btn-lg bg-gradient-to-r from-primary-500 to-primary-600 text-white shadow-xl shadow-primary-500/30 hover:shadow-2xl hover:shadow-primary-500/40 transition-all duration-300 transform hover:scale-105 hover:-translate-y-0.5 overflow-hidden w-full sm:w-auto sm:flex-shrink-0 whitespace-nowrap">
                            <span class="relative z-10 inline-flex items-center justify-center gap-2">
                                <span>Démarrer gratuitement</span>
                                <svg class="w-5 h-5 shrink-0 group-hover:translate-x-1 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                                </svg>
                            </span>
                            <div class="absolute inset-0 bg-gradient-to-r from-primary-600 to-accent-500 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                        </a>
                        <a href="{{ route('commando.register.step1') }}" class="group inline-flex items-center justify-center gap-2 btn btn-lg bg-white/5 backdrop-blur-xl text-white border border-orange-500/40 hover:bg-orange-500/10 hover:border-orange-500/60 transition-all duration-300 w-full sm:w-auto sm:flex-shrink-0">
                            <span class="text-orange-400 group-hover:text-orange-300 whitespace-nowrap">Devenir agent Commando</span>
                        </a>
                        <a href="{{ route('r.menu', ['slug' => 'demo']) }}" target="_blank" class="group inline-flex items-center justify-center gap-2 btn btn-lg bg-white/5 backdrop-blur-xl text-white border border-white/20 hover:bg-white/10 hover:border-white/40 transition-all duration-300 w-full sm:w-auto sm:flex-shrink-0 whitespace-nowrap">
                            <svg class="w-5 h-5 shrink-0 text-primary-400 group-hover:scale-110 transition-transform" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M8 5v14l11-7z"/>
                            </svg>
                            <span>Voir la démo</span>
                        </a>
                    </div>
                    
                    <!-- Stats améliorées -->
                    <div class="mt-12 sm:mt-14 grid grid-cols-3 gap-4 sm:gap-8"
                         x-show="shown" 
                         x-transition:enter="transition ease-out duration-700 delay-500"
                         x-transition:enter-start="opacity-0 translate-y-6"
                         x-transition:enter-end="opacity-100 translate-y-0">
                        <div class="text-center lg:text-left group cursor-default">
                            <div class="text-3xl sm:text-4xl md:text-5xl font-bold text-white group-hover:text-primary-400 transition-colors duration-300" x-data="counter({{ $stats['raw']['restaurants'] }})" x-intersect.once="startCount()">
                                <span x-text="displayCount"></span>
                            </div>
                            <div class="text-neutral-400 text-xs sm:text-sm mt-1.5 font-medium uppercase tracking-wider">Restaurants</div>
                        </div>
                        <div class="text-center lg:text-left group cursor-default">
                            <div class="text-3xl sm:text-4xl md:text-5xl font-bold text-white group-hover:text-secondary-400 transition-colors duration-300" x-data="counter({{ $stats['raw']['orders'] }})" x-intersect.once="startCount()">
                                <span x-text="displayCount"></span>
                            </div>
                            <div class="text-neutral-400 text-xs sm:text-sm mt-1.5 font-medium uppercase tracking-wider">Commandes</div>
                        </div>
                        <div class="text-center lg:text-left group cursor-default">
                            <div class="text-3xl sm:text-4xl md:text-5xl font-bold text-white group-hover:text-accent-400 transition-colors duration-300">
                                {{ $stats['uptime'] }}
                            </div>
                            <div class="text-neutral-400 text-xs sm:text-sm mt-1.5 font-medium uppercase tracking-wider">Uptime</div>
                        </div>
                    </div>
                    
                    <!-- Trust badges -->
                    <div class="mt-10 flex flex-wrap items-center justify-center lg:justify-start gap-x-6 gap-y-3"
                         x-show="shown" 
                         x-transition:enter="transition ease-out duration-700 delay-600"
                         x-transition:enter-start="opacity-0"
                         x-transition:enter-end="opacity-100">
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
                            <div class="phone-3d" :class="{ 'phone-3d-auto': !isHovering }" :style="isHovering ? { transform: `rotateY(${rotateY}deg) rotateX(${rotateX}deg)` } : {}">
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($heroImage) }}" 
                                     alt="MenuPro - Votre menu digital" 
                                     class="w-full h-auto max-w-sm rounded-3xl shadow-2xl shadow-black/50 object-contain">
                            </div>
                        </div>
                    @else
                        <!-- Phone Mockup 3D amélioré -->
                        <div class="relative phone-3d-container">
                            <div class="phone-3d" :class="{ 'phone-3d-auto': !isHovering }" :style="isHovering ? { transform: `rotateY(${rotateY}deg) rotateX(${rotateX}deg)` } : {}">
                                <!-- Cadre du téléphone -->
                                <div class="relative w-[280px] sm:w-[320px] h-[560px] sm:h-[640px] bg-gradient-to-b from-neutral-800 via-neutral-850 to-neutral-900 rounded-[2.5rem] sm:rounded-[3rem] p-[6px] sm:p-2 shadow-2xl shadow-black/60 ring-1 ring-white/10">
                                    <!-- Boutons latéraux -->
                                    <div class="absolute -left-[3px] top-24 w-[3px] h-8 bg-neutral-700 rounded-l-full"></div>
                                    <div class="absolute -left-[3px] top-36 w-[3px] h-14 bg-neutral-700 rounded-l-full"></div>
                                    <div class="absolute -left-[3px] top-56 w-[3px] h-14 bg-neutral-700 rounded-l-full"></div>
                                    <div class="absolute -right-[3px] top-36 w-[3px] h-20 bg-neutral-700 rounded-r-full"></div>
                                    
                                    <!-- Écran intérieur -->
                                    <div class="relative w-full h-full bg-white rounded-[2rem] sm:rounded-[2.5rem] overflow-hidden">
                                        <!-- Dynamic Island -->
                                        <div class="absolute top-2 left-1/2 -translate-x-1/2 w-28 sm:w-32 h-7 sm:h-8 bg-black rounded-full z-30 flex items-center justify-center gap-3 px-4">
                                            <div class="w-2.5 h-2.5 bg-neutral-800 rounded-full ring-1 ring-neutral-700"></div>
                                            <div class="w-1.5 h-1.5 bg-neutral-700 rounded-full"></div>
                                        </div>
                                        
                                        <!-- Contenu de l'app -->
                                        <div class="h-full overflow-hidden bg-neutral-50">
                                            <!-- Header restaurant avec gradient -->
                                            <div class="bg-gradient-to-br from-primary-500 via-primary-600 to-primary-700 text-white pt-12 pb-4 px-4">
                                                <div class="flex items-center gap-3">
                                                    <div class="w-12 h-12 sm:w-14 sm:h-14 bg-white/20 rounded-2xl flex items-center justify-center backdrop-blur-sm shadow-lg">
                                                        <svg class="w-6 h-6 sm:w-7 sm:h-7 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                                    </div>
                                                    <div class="flex-1 min-w-0">
                                                        <div class="font-bold text-sm sm:text-base truncate">Le Maquis d'Abidjan</div>
                                                        <div class="flex items-center gap-2 text-[10px] sm:text-xs text-white/80">
                                                            <span class="flex items-center gap-1">
                                                                <span class="w-1.5 h-1.5 bg-green-400 rounded-full animate-pulse"></span>
                                                                Ouvert
                                                            </span>
                                                            <span>•</span>
                                                            <span class="flex items-center gap-0.5">
                                                                4.8 
                                                                <svg class="w-3 h-3 text-yellow-300" fill="currentColor" viewBox="0 0 20 20">
                                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                                </svg>
                                                            </span>
                                                        </div>
                                                    </div>
                                                    <button class="p-2.5 bg-white/10 rounded-xl backdrop-blur-sm hover:bg-white/20 transition-colors">
                                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                                                        </svg>
                                                    </button>
                                                </div>
                                            </div>
                                            
                                            <!-- Categories avec scroll -->
                                            <div class="flex gap-2 p-3 overflow-x-auto scrollbar-hide bg-white border-b border-neutral-100 shadow-sm">
                                                <span class="px-4 py-2 bg-primary-500 text-white text-xs font-semibold rounded-full whitespace-nowrap shadow-md shadow-primary-500/30 flex items-center gap-1.5">
                                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                                        <path d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 005.05 6.05 6.981 6.981 0 003 11a7 7 0 1011.95-4.95c-.592-.591-.98-.985-1.348-1.467-.363-.476-.724-1.063-1.207-2.03zM12.12 15.12A3 3 0 017 13s.879.5 2.5.5c0-1 .5-4 1.25-4.5.5 1 .786 1.293 1.371 1.879A2.99 2.99 0 0113 13a2.99 2.99 0 01-.879 2.121z"/>
                                                    </svg>
                                                    Populaires
                                                </span>
                                                <span class="px-4 py-2 bg-neutral-100 text-neutral-600 text-xs font-medium rounded-full whitespace-nowrap hover:bg-neutral-200 transition-colors">Entrées</span>
                                                <span class="px-4 py-2 bg-neutral-100 text-neutral-600 text-xs font-medium rounded-full whitespace-nowrap hover:bg-neutral-200 transition-colors">Plats</span>
                                                <span class="px-4 py-2 bg-neutral-100 text-neutral-600 text-xs font-medium rounded-full whitespace-nowrap hover:bg-neutral-200 transition-colors">Boissons</span>
                                            </div>
                                            
                                            <!-- Menu Items -->
                                            <div class="p-3 space-y-3 overflow-y-auto pb-24" style="max-height: calc(100% - 180px);">
                                                <!-- Item 1 - Featured -->
                                                <div class="bg-white rounded-2xl p-3 flex gap-3 shadow-md border border-neutral-100 hover:shadow-lg transition-shadow relative overflow-hidden">
                                                    <div class="absolute top-2 left-2 px-2 py-0.5 bg-accent-500 text-white text-[9px] font-bold rounded-full">BEST</div>
                                                    <div class="w-20 h-20 sm:w-24 sm:h-24 bg-gradient-to-br from-amber-100 to-orange-200 rounded-xl flex items-center justify-center text-3xl sm:text-4xl flex-shrink-0 shadow-inner">
                                                        🍗
                                                    </div>
                                                    <div class="flex-1 min-w-0 flex flex-col justify-between py-0.5">
                                                        <div>
                                                            <div class="font-bold text-sm text-neutral-800 truncate">Poulet Braisé</div>
                                                            <div class="text-[11px] text-neutral-500 line-clamp-2 mt-0.5">Avec alloco et sauce piment maison</div>
                                                        </div>
                                                        <div class="flex items-center justify-between mt-2">
                                                            <div class="text-primary-600 font-bold text-sm">5 500 F</div>
                                                            <button class="w-7 h-7 bg-primary-500 text-white rounded-full flex items-center justify-center text-lg font-bold shadow-md shadow-primary-500/30 hover:bg-primary-600 transition-colors">+</button>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <!-- Item 2 -->
                                                <div class="bg-white rounded-2xl p-3 flex gap-3 shadow-sm border border-neutral-100 hover:shadow-md transition-shadow">
                                                    <div class="w-20 h-20 sm:w-24 sm:h-24 bg-gradient-to-br from-yellow-100 to-amber-200 rounded-xl flex items-center justify-center text-3xl sm:text-4xl flex-shrink-0 shadow-inner">
                                                        🐟
                                                    </div>
                                                    <div class="flex-1 min-w-0 flex flex-col justify-between py-0.5">
                                                        <div>
                                                            <div class="font-bold text-sm text-neutral-800 truncate">Attiéké Poisson</div>
                                                            <div class="text-[11px] text-neutral-500 line-clamp-2 mt-0.5">Poisson braisé, légumes frais</div>
                                                        </div>
                                                        <div class="flex items-center justify-between mt-2">
                                                            <div class="text-primary-600 font-bold text-sm">4 500 F</div>
                                                            <button class="w-7 h-7 bg-primary-500 text-white rounded-full flex items-center justify-center text-lg font-bold shadow-md shadow-primary-500/30 hover:bg-primary-600 transition-colors">+</button>
                                                        </div>
                                                    </div>
                                                </div>
                                                
                                                <!-- Item 3 -->
                                                <div class="bg-white rounded-2xl p-3 flex gap-3 shadow-sm border border-neutral-100 hover:shadow-md transition-shadow">
                                                    <div class="w-20 h-20 sm:w-24 sm:h-24 bg-gradient-to-br from-green-100 to-emerald-200 rounded-xl flex items-center justify-center text-3xl sm:text-4xl flex-shrink-0 shadow-inner">
                                                        🥗
                                                    </div>
                                                    <div class="flex-1 min-w-0 flex flex-col justify-between py-0.5">
                                                        <div>
                                                            <div class="font-bold text-sm text-neutral-800 truncate">Salade Africaine</div>
                                                            <div class="text-[11px] text-neutral-500 line-clamp-2 mt-0.5">Légumes frais, vinaigrette</div>
                                                        </div>
                                                        <div class="flex items-center justify-between mt-2">
                                                            <div class="text-primary-600 font-bold text-sm">2 500 F</div>
                                                            <button class="w-7 h-7 bg-primary-500 text-white rounded-full flex items-center justify-center text-lg font-bold shadow-md shadow-primary-500/30 hover:bg-primary-600 transition-colors">+</button>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Bottom cart bar amélioré -->
                                            <div class="absolute bottom-0 left-0 right-0 p-3 bg-gradient-to-t from-neutral-50 via-neutral-50/95 to-transparent pt-8">
                                                <div class="bg-gradient-to-r from-primary-500 to-primary-600 text-white rounded-2xl p-3.5 flex items-center justify-between shadow-xl shadow-primary-500/40">
                                                    <div class="flex items-center gap-3">
                                                        <div class="w-8 h-8 bg-white/20 rounded-full flex items-center justify-center text-sm font-bold backdrop-blur-sm">3</div>
                                                        <span class="text-sm font-semibold">Voir le panier</span>
                                                    </div>
                                                    <span class="font-bold text-base">12 500 F</span>
                                                </div>
                                            </div>
                                        </div>
                                        
                                        <!-- Reflets sur l'écran -->
                                        <div class="phone-screen-glare"></div>
                                        <div class="phone-screen-glare-animated"></div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Ombre 3D -->
                            <div class="phone-3d-shadow"></div>
                            
                            <!-- Floating Cards autour du téléphone -->
                            <!-- Notification commande -->
                            <div class="absolute -top-4 -right-4 sm:-top-6 sm:-right-14 bg-white rounded-2xl shadow-2xl p-3 sm:p-4 badge-float-1 z-20 border border-neutral-100/50">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-secondary-400 to-secondary-500 rounded-xl flex items-center justify-center shadow-lg shadow-secondary-500/30">
                                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
                            <div class="absolute -bottom-4 -left-4 sm:-bottom-8 sm:-left-14 bg-white rounded-2xl shadow-2xl p-3 sm:p-4 badge-float-2 z-20 border border-neutral-100/50">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 sm:w-12 sm:h-12 bg-gradient-to-br from-primary-400 to-primary-500 rounded-xl flex items-center justify-center shadow-lg shadow-primary-500/30">
                                        <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <div class="font-bold text-xs sm:text-sm text-neutral-800">+125 000 F</div>
                                        <div class="text-[10px] sm:text-xs text-neutral-500">CA du jour</div>
                                    </div>
                                </div>
                            </div>
                            
                            <!-- Paiement Mobile Money -->
                            <div class="absolute top-1/2 -right-2 sm:-right-16 translate-y-6 bg-white rounded-2xl shadow-2xl p-2.5 sm:p-3 badge-float-3 z-20 border border-neutral-100/50">
                                <div class="flex items-center gap-2">
                                    <div class="flex -space-x-1.5">
                                        <div class="w-7 h-7 sm:w-8 sm:h-8 bg-orange-500 rounded-full flex items-center justify-center text-white text-[9px] sm:text-[10px] font-bold ring-2 ring-white shadow-sm">OM</div>
                                        <div class="w-7 h-7 sm:w-8 sm:h-8 bg-yellow-400 rounded-full flex items-center justify-center text-neutral-800 text-[9px] sm:text-[10px] font-bold ring-2 ring-white shadow-sm">MTN</div>
                                        <div class="w-7 h-7 sm:w-8 sm:h-8 bg-blue-500 rounded-full flex items-center justify-center text-white text-[9px] sm:text-[10px] font-bold ring-2 ring-white shadow-sm">W</div>
                                    </div>
                                    <span class="text-[10px] sm:text-xs font-semibold text-neutral-600 whitespace-nowrap">Paiement<br>intégré</span>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
        
        <!-- Scroll Indicator -->
        <div class="absolute bottom-6 sm:bottom-8 left-1/2 -translate-x-1/2 flex flex-col items-center gap-2" x-data="{ visible: true }" x-intersect:leave="visible = false" x-show="visible" x-transition.opacity.duration.500ms>
            <span class="text-neutral-500 text-xs uppercase tracking-widest font-medium">Découvrir</span>
            <div class="w-6 h-10 border-2 border-neutral-600 rounded-full flex justify-center pt-2">
                <div class="w-1.5 h-3 bg-primary-400 rounded-full animate-bounce"></div>
            </div>
        </div>
    </section>

    <!-- Logos de confiance / Moyens de paiement -->
    <section class="py-12 sm:py-16 bg-white border-b border-neutral-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <p class="text-center text-neutral-500 text-sm font-medium mb-8 uppercase tracking-wider">Paiements sécurisés acceptés</p>
            <div class="flex flex-wrap items-center justify-center gap-6 sm:gap-8" x-data="{ shown: false }" x-intersect.once="shown = true">
                <!-- Orange Money -->
                <div class="flex items-center gap-3 bg-neutral-50 px-5 py-3 rounded-xl border border-neutral-200 hover:border-orange-300 hover:shadow-lg transition-all duration-300"
                     x-show="shown" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" style="transition-delay: 0ms">
                    <img src="{{ asset('images/payments/orange-money.svg') }}" alt="Orange Money" class="h-10 w-10 object-contain rounded">
                    <span class="font-semibold text-neutral-700 hidden sm:block">Orange Money</span>
                </div>
                <!-- MTN MoMo -->
                <div class="flex items-center gap-3 bg-neutral-50 px-5 py-3 rounded-xl border border-neutral-200 hover:border-yellow-300 hover:shadow-lg transition-all duration-300"
                     x-show="shown" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" style="transition-delay: 100ms">
                    <img src="{{ asset('images/payments/mtn-momo.svg') }}" alt="MTN MoMo" class="h-10 w-10 object-contain rounded">
                    <span class="font-semibold text-neutral-700 hidden sm:block">MTN MoMo</span>
                </div>
                <!-- Wave -->
                <div class="flex items-center gap-3 bg-neutral-50 px-5 py-3 rounded-xl border border-neutral-200 hover:border-blue-300 hover:shadow-lg transition-all duration-300"
                     x-show="shown" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" style="transition-delay: 200ms">
                    <img src="{{ asset('images/payments/wave.svg') }}" alt="Wave" class="h-10 w-10 object-contain rounded">
                    <span class="font-semibold text-neutral-700 hidden sm:block">Wave</span>
                </div>
                <!-- Moov Money -->
                <div class="flex items-center gap-3 bg-neutral-50 px-5 py-3 rounded-xl border border-neutral-200 hover:border-green-300 hover:shadow-lg transition-all duration-300"
                     x-show="shown" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" style="transition-delay: 300ms">
                    <img src="{{ asset('images/payments/moov-money.svg') }}" alt="Moov Money" class="h-10 w-10 object-contain rounded">
                    <span class="font-semibold text-neutral-700 hidden sm:block">Moov Money</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Comment ça marche Section -->
    <section id="how-it-works" class="py-20 sm:py-28 bg-gradient-to-b from-neutral-50 to-white relative overflow-hidden">
        <!-- Background decoration -->
        <div class="absolute top-0 left-0 w-[500px] h-[500px] bg-primary-100/40 rounded-full blur-[100px] -translate-x-1/2 -translate-y-1/2"></div>
        <div class="absolute bottom-0 right-0 w-[600px] h-[600px] bg-secondary-100/30 rounded-full blur-[120px] translate-x-1/2 translate-y-1/2"></div>
        
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Section Header -->
            <div class="text-center max-w-3xl mx-auto mb-16 sm:mb-20" x-data="{ shown: false }" x-intersect.once="shown = true">
                <span class="inline-flex items-center gap-2 px-4 py-2 bg-primary-100 text-primary-700 rounded-full text-sm font-semibold mb-6"
                      x-show="shown" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    Simple & Rapide
                </span>
                <h2 class="font-display text-3xl sm:text-4xl md:text-5xl font-bold text-neutral-900 leading-tight"
                    x-show="shown" x-transition:enter="transition ease-out duration-700 delay-100" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                    Lancez-vous en <span class="text-gradient">3 étapes</span>
                </h2>
                <p class="text-neutral-600 text-lg mt-4 max-w-2xl mx-auto"
                   x-show="shown" x-transition:enter="transition ease-out duration-700 delay-200" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                    Pas besoin d'être expert en technologie. En quelques minutes, votre restaurant est en ligne et prêt à recevoir des commandes.
                </p>
            </div>

            <!-- Steps - Version améliorée -->
            <div class="relative" x-data="{ shown: false, activeStep: 0 }" x-intersect.once="shown = true; $nextTick(() => { setInterval(() => { activeStep = (activeStep + 1) % 3 }, 3000) })">
                
                <!-- Timeline Progress Bar (Desktop) -->
                <div class="hidden md:block mb-16">
                    <div class="relative max-w-3xl mx-auto">
                        <!-- Background line -->
                        <div class="absolute top-6 left-0 right-0 h-1 bg-neutral-200 rounded-full"></div>
                        <!-- Animated progress line -->
                        <div class="absolute top-6 left-0 h-1 bg-gradient-to-r from-primary-500 via-secondary-500 to-accent-500 rounded-full transition-all duration-1000"
                             :style="'width: ' + ((activeStep + 1) * 33.33) + '%'"></div>
                        
                        <!-- Step indicators -->
                        <div class="relative flex justify-between">
                            @foreach([
                                ['num' => 1, 'label' => 'Inscription', 'time' => '2 min', 'color' => 'primary'],
                                ['num' => 2, 'label' => 'Configuration', 'time' => '10 min', 'color' => 'secondary'],
                                ['num' => 3, 'label' => 'Lancement', 'time' => 'Immédiat', 'color' => 'accent'],
                            ] as $index => $step)
                                <div class="flex flex-col items-center">
                                    <div class="w-12 h-12 rounded-full flex items-center justify-center text-lg font-bold transition-all duration-500 cursor-pointer"
                                         :class="activeStep >= {{ $index }} ? 'bg-{{ $step['color'] }}-500 text-white shadow-lg shadow-{{ $step['color'] }}-500/40 scale-110' : 'bg-white text-neutral-400 border-2 border-neutral-200'"
                                         @click="activeStep = {{ $index }}">
                                        <span x-show="activeStep > {{ $index }}">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </span>
                                        <span x-show="activeStep <= {{ $index }}">{{ $step['num'] }}</span>
                                    </div>
                                    <span class="mt-3 text-sm font-semibold transition-colors duration-300"
                                          :class="activeStep >= {{ $index }} ? 'text-{{ $step['color'] }}-600' : 'text-neutral-400'">
                                        {{ $step['label'] }}
                                    </span>
                                    <span class="text-xs px-2 py-0.5 rounded-full mt-1 transition-all duration-300"
                                          :class="activeStep >= {{ $index }} ? 'bg-{{ $step['color'] }}-100 text-{{ $step['color'] }}-600' : 'bg-neutral-100 text-neutral-400'">
                                        {{ $step['time'] }}
                                    </span>
                                </div>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Steps Cards -->
                <div class="grid md:grid-cols-3 gap-6 lg:gap-8">
                    
                    <!-- Step 1: Inscription -->
                    <div class="relative group" 
                         x-show="shown" 
                         x-transition:enter="transition ease-out duration-700" 
                         x-transition:enter-start="opacity-0 translate-y-8" 
                         x-transition:enter-end="opacity-100 translate-y-0"
                         @mouseenter="activeStep = 0">
                        <div class="relative bg-white rounded-3xl p-6 lg:p-8 border-2 transition-all duration-500 h-full overflow-hidden"
                             :class="activeStep === 0 ? 'border-primary-400 shadow-2xl shadow-primary-500/20 -translate-y-2' : 'border-neutral-100 shadow-lg shadow-neutral-200/50 hover:border-primary-200'">
                            
                            <!-- Glow effect -->
                            <div class="absolute inset-0 bg-gradient-to-br from-primary-500/5 to-transparent opacity-0 transition-opacity duration-500"
                                 :class="activeStep === 0 && 'opacity-100'"></div>
                            
                            <!-- Mobile step indicator -->
                            <div class="md:hidden flex items-center gap-3 mb-4">
                                <div class="w-8 h-8 bg-primary-500 rounded-lg flex items-center justify-center text-white font-bold text-sm">1</div>
                                <span class="text-xs font-medium text-primary-600 bg-primary-50 px-2 py-1 rounded-full">~2 minutes</span>
                            </div>
                            
                            <!-- Header with icon -->
                            <div class="relative flex items-start gap-4 mb-5">
                                <div class="w-14 h-14 bg-gradient-to-br from-primary-400 to-primary-600 rounded-2xl flex items-center justify-center flex-shrink-0 shadow-lg shadow-primary-500/30 group-hover:scale-110 transition-transform duration-300">
                                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-neutral-900">Créez votre compte</h3>
                                    <p class="text-sm text-primary-600 font-medium">Gratuit et sans engagement</p>
                                </div>
                            </div>
                            
                            <!-- Checklist -->
                            <ul class="relative space-y-3 mb-6">
                                <li class="flex items-start gap-3">
                                    <span class="w-5 h-5 bg-primary-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                        <svg class="w-3 h-3 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </span>
                                    <span class="text-neutral-600">Entrez le <strong class="text-neutral-800">nom de votre restaurant</strong></span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <span class="w-5 h-5 bg-primary-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                        <svg class="w-3 h-3 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </span>
                                    <span class="text-neutral-600">Ajoutez votre <strong class="text-neutral-800">email et téléphone</strong></span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <span class="w-5 h-5 bg-primary-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                        <svg class="w-3 h-3 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </span>
                                    <span class="text-neutral-600">Choisissez votre <strong class="text-neutral-800">mot de passe</strong></span>
                                </li>
                            </ul>
                            
                            <!-- Result badge -->
                            <div class="relative flex items-center gap-2 p-3 bg-gradient-to-r from-primary-50 to-orange-50 rounded-xl border border-primary-100">
                                <div class="w-8 h-8 bg-primary-500 rounded-lg flex items-center justify-center">
                                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                </div>
                                <span class="text-sm font-medium text-primary-700">Accès immédiat au tableau de bord</span>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Step 2: Menu -->
                    <div class="relative group" 
                         x-show="shown" 
                         x-transition:enter="transition ease-out duration-700 delay-150" 
                         x-transition:enter-start="opacity-0 translate-y-8" 
                         x-transition:enter-end="opacity-100 translate-y-0"
                         @mouseenter="activeStep = 1">
                        <div class="relative bg-white rounded-3xl p-6 lg:p-8 border-2 transition-all duration-500 h-full overflow-hidden"
                             :class="activeStep === 1 ? 'border-secondary-400 shadow-2xl shadow-secondary-500/20 -translate-y-2' : 'border-neutral-100 shadow-lg shadow-neutral-200/50 hover:border-secondary-200'">
                            
                            <!-- Glow effect -->
                            <div class="absolute inset-0 bg-gradient-to-br from-secondary-500/5 to-transparent opacity-0 transition-opacity duration-500"
                                 :class="activeStep === 1 && 'opacity-100'"></div>
                            
                            <!-- Mobile step indicator -->
                            <div class="md:hidden flex items-center gap-3 mb-4">
                                <div class="w-8 h-8 bg-secondary-500 rounded-lg flex items-center justify-center text-white font-bold text-sm">2</div>
                                <span class="text-xs font-medium text-secondary-600 bg-secondary-50 px-2 py-1 rounded-full">~10 minutes</span>
                            </div>
                            
                            <!-- Header with icon -->
                            <div class="relative flex items-start gap-4 mb-5">
                                <div class="w-14 h-14 bg-gradient-to-br from-secondary-400 to-secondary-600 rounded-2xl flex items-center justify-center flex-shrink-0 shadow-lg shadow-secondary-500/30 group-hover:scale-110 transition-transform duration-300">
                                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-neutral-900">Créez votre menu</h3>
                                    <p class="text-sm text-secondary-600 font-medium">Interface intuitive glisser-déposer</p>
                                </div>
                            </div>
                            
                            <!-- Checklist -->
                            <ul class="relative space-y-3 mb-6">
                                <li class="flex items-start gap-3">
                                    <span class="w-5 h-5 bg-secondary-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                        <svg class="w-3 h-3 text-secondary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </span>
                                    <span class="text-neutral-600">Créez vos <strong class="text-neutral-800">catégories</strong> (Entrées, Plats...)</span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <span class="w-5 h-5 bg-secondary-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                        <svg class="w-3 h-3 text-secondary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </span>
                                    <span class="text-neutral-600">Ajoutez <strong class="text-neutral-800">photos, descriptions, prix</strong></span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <span class="w-5 h-5 bg-secondary-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                        <svg class="w-3 h-3 text-secondary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </span>
                                    <span class="text-neutral-600">Configurez <strong class="text-neutral-800">horaires et livraison</strong></span>
                                </li>
                            </ul>
                            
                            <!-- Visual preview mockup -->
                            <div class="relative p-3 bg-gradient-to-r from-secondary-50 to-green-50 rounded-xl border border-secondary-100">
                                <div class="flex items-center gap-3">
                                    <div class="flex -space-x-2">
                                        <div class="w-8 h-8 bg-orange-200 rounded-lg flex items-center justify-center text-xs">🍔</div>
                                        <div class="w-8 h-8 bg-green-200 rounded-lg flex items-center justify-center text-xs">🥗</div>
                                        <div class="w-8 h-8 bg-yellow-200 rounded-lg flex items-center justify-center text-xs">🍟</div>
                                    </div>
                                    <span class="text-sm font-medium text-secondary-700">Menu visible en temps réel</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Step 3: Lancement -->
                    <div class="relative group" 
                         x-show="shown" 
                         x-transition:enter="transition ease-out duration-700 delay-300" 
                         x-transition:enter-start="opacity-0 translate-y-8" 
                         x-transition:enter-end="opacity-100 translate-y-0"
                         @mouseenter="activeStep = 2">
                        <div class="relative bg-white rounded-3xl p-6 lg:p-8 border-2 transition-all duration-500 h-full overflow-hidden"
                             :class="activeStep === 2 ? 'border-accent-400 shadow-2xl shadow-accent-500/20 -translate-y-2' : 'border-neutral-100 shadow-lg shadow-neutral-200/50 hover:border-accent-200'">
                            
                            <!-- Glow effect -->
                            <div class="absolute inset-0 bg-gradient-to-br from-accent-500/5 to-transparent opacity-0 transition-opacity duration-500"
                                 :class="activeStep === 2 && 'opacity-100'"></div>
                            
                            <!-- Mobile step indicator -->
                            <div class="md:hidden flex items-center gap-3 mb-4">
                                <div class="w-8 h-8 bg-accent-500 rounded-lg flex items-center justify-center text-white font-bold text-sm">3</div>
                                <span class="text-xs font-medium text-accent-600 bg-accent-50 px-2 py-1 rounded-full">Immédiat</span>
                            </div>
                            
                            <!-- Header with icon -->
                            <div class="relative flex items-start gap-4 mb-5">
                                <div class="w-14 h-14 bg-gradient-to-br from-accent-400 to-accent-600 rounded-2xl flex items-center justify-center flex-shrink-0 shadow-lg shadow-accent-500/30 group-hover:scale-110 transition-transform duration-300">
                                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 15l-2 5L9 9l11 4-5 2zm0 0l5 5M7.188 2.239l.777 2.897M5.136 7.965l-2.898-.777M13.95 4.05l-2.122 2.122m-5.657 5.656l-2.12 2.122"/>
                                    </svg>
                                </div>
                                <div>
                                    <h3 class="text-xl font-bold text-neutral-900">Recevez des commandes</h3>
                                    <p class="text-sm text-accent-600 font-medium">Votre restaurant est en ligne !</p>
                                </div>
                            </div>
                            
                            <!-- Sharing options -->
                            <ul class="relative space-y-3 mb-6">
                                <li class="flex items-start gap-3">
                                    <span class="w-5 h-5 bg-green-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                        <svg class="w-3.5 h-3.5 text-green-600" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
                                        </svg>
                                    </span>
                                    <span class="text-neutral-600">Partagez sur <strong class="text-neutral-800">WhatsApp</strong></span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <span class="w-5 h-5 bg-blue-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                        <svg class="w-3 h-3 text-blue-600" fill="currentColor" viewBox="0 0 24 24">
                                            <path d="M22.675 0h-21.35c-.732 0-1.325.593-1.325 1.325v21.351c0 .731.593 1.324 1.325 1.324h11.495v-9.294h-3.128v-3.622h3.128v-2.671c0-3.1 1.893-4.788 4.659-4.788 1.325 0 2.463.099 2.795.143v3.24l-1.918.001c-1.504 0-1.795.715-1.795 1.763v2.313h3.587l-.467 3.622h-3.12v9.293h6.116c.73 0 1.323-.593 1.323-1.325v-21.35c0-.732-.593-1.325-1.325-1.325z"/>
                                        </svg>
                                    </span>
                                    <span class="text-neutral-600">Publiez sur <strong class="text-neutral-800">Facebook</strong></span>
                                </li>
                                <li class="flex items-start gap-3">
                                    <span class="w-5 h-5 bg-neutral-100 rounded-full flex items-center justify-center flex-shrink-0 mt-0.5">
                                        <svg class="w-3 h-3 text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                                        </svg>
                                    </span>
                                    <span class="text-neutral-600">Imprimez votre <strong class="text-neutral-800">QR Code</strong></span>
                                </li>
                            </ul>
                            
                            <!-- Payment methods -->
                            <div class="relative p-3 bg-gradient-to-r from-accent-50 to-purple-50 rounded-xl border border-accent-100">
                                <div class="flex items-center justify-between">
                                    <span class="text-sm font-medium text-accent-700">Paiements acceptés</span>
                                    <div class="flex items-center gap-1.5">
                                        <span class="px-2 py-1 bg-orange-100 text-orange-600 rounded text-xs font-bold">Orange</span>
                                        <span class="px-2 py-1 bg-yellow-100 text-yellow-700 rounded text-xs font-bold">MTN</span>
                                        <span class="px-2 py-1 bg-blue-100 text-blue-600 rounded text-xs font-bold">Wave</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Total time indicator -->
                <div class="text-center mt-10" x-show="shown" x-transition:enter="transition ease-out duration-700 delay-500" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                    <div class="inline-flex items-center gap-3 px-6 py-3 bg-gradient-to-r from-neutral-900 to-neutral-800 rounded-full text-white shadow-xl">
                        <svg class="w-5 h-5 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="font-medium">Temps total estimé : <strong class="text-primary-400">~15 minutes</strong></span>
                    </div>
                </div>
            </div>
            
            <!-- CTA -->
            <div class="text-center mt-16" x-data="{ shown: false }" x-intersect.once="shown = true">
                <div class="inline-flex flex-col sm:flex-row items-center gap-4"
                     x-show="shown" x-transition:enter="transition ease-out duration-700" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                    <a href="{{ route('r.menu', ['slug' => 'demo']) }}" target="_blank" class="group btn btn-lg bg-white border-2 border-neutral-200 text-neutral-700 hover:border-primary-500 hover:text-primary-600 shadow-sm hover:shadow-lg transition-all duration-300">
                        <svg class="w-5 h-5 text-primary-500" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M8 5v14l11-7z"/>
                        </svg>
                        Voir un exemple réel
                        <svg class="w-4 h-4 opacity-50 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                    </a>
                    <a href="{{ route('register') }}" class="btn btn-lg btn-primary shadow-xl shadow-primary-500/30 hover:shadow-2xl hover:shadow-primary-500/40 transition-all duration-300 hover:scale-105">
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

    @if(!empty($videos))
    <!-- Section Vidéos - Dynamique (SystemSetting: home_videos) -->
    <section id="videos" class="py-20 sm:py-28 bg-white relative overflow-hidden">
        <div class="absolute inset-0 bg-[linear-gradient(rgba(249,115,22,0.02)_1px,transparent_1px),linear-gradient(90deg,rgba(249,115,22,0.02)_1px,transparent_1px)] bg-[size:40px_40px]"></div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-12 sm:mb-16" x-data="{ shown: false }" x-intersect.once="shown = true">
                <span class="inline-flex items-center gap-2 px-4 py-2 bg-secondary-100 text-secondary-700 rounded-full text-sm font-semibold mb-6"
                      x-show="shown" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Tutoriels vidéo
                </span>
                <h2 class="font-display text-3xl sm:text-4xl md:text-5xl font-bold text-neutral-900 leading-tight"
                    x-show="shown" x-transition:enter="transition ease-out duration-700 delay-100" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                    Découvrez <span class="text-gradient">MenuPro en vidéo</span>
                </h2>
                <p class="text-neutral-600 text-lg mt-4"
                   x-show="shown" x-transition:enter="transition ease-out duration-700 delay-200" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                    Apprenez à utiliser MenuPro grâce à nos tutoriels explicatifs.
                </p>
            </div>

            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8" x-data="{ shown: false }" x-intersect.once="shown = true">
                @foreach($videos as $index => $video)
                <div class="group" 
                     x-show="shown" 
                     x-transition:enter="transition ease-out duration-700" 
                     x-transition:enter-start="opacity-0 translate-y-8" 
                     x-transition:enter-end="opacity-100 translate-y-0"
                     style="transition-delay: {{ $index * 80 }}ms">
                    <div class="bg-white rounded-2xl overflow-hidden border-2 border-neutral-100 hover:border-primary-200 shadow-lg hover:shadow-xl transition-all duration-500 h-full flex flex-col">
                        <div class="relative aspect-video bg-neutral-900">
                            <iframe src="{{ $video['url'] }}?rel=0&modestbranding=1" 
                                    class="absolute inset-0 w-full h-full"
                                    title="{{ $video['title'] }}"
                                    allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                    allowfullscreen></iframe>
                        </div>
                        <div class="p-5 lg:p-6 flex-1 flex flex-col">
                            <h3 class="text-lg font-bold text-neutral-900 mb-2 group-hover:text-primary-600 transition-colors">
                                {{ $video['title'] }}
                            </h3>
                            @if(!empty($video['description']))
                            <p class="text-neutral-600 text-sm leading-relaxed flex-1">
                                {{ $video['description'] }}
                            </p>
                            @endif
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    <!-- Features Section -->
    <section id="features" class="py-24 bg-white relative overflow-hidden">
        <!-- Subtle background pattern -->
        <div class="absolute inset-0 bg-[linear-gradient(rgba(249,115,22,0.02)_1px,transparent_1px),linear-gradient(90deg,rgba(249,115,22,0.02)_1px,transparent_1px)] bg-[size:40px_40px]"></div>
        
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Section Header -->
            <div class="text-center max-w-3xl mx-auto mb-16 sm:mb-20" x-data="{ shown: false }" x-intersect.once="shown = true">
                <span class="inline-flex items-center gap-2 px-4 py-2 bg-primary-100 text-primary-700 rounded-full text-sm font-semibold mb-6"
                      x-show="shown" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                    </svg>
                    Fonctionnalites
                </span>
                <h2 class="font-display text-3xl sm:text-4xl md:text-5xl font-bold text-neutral-900 leading-tight"
                    x-show="shown" x-transition:enter="transition ease-out duration-700 delay-100" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                    Tout ce qu'il faut pour <span class="text-gradient">reussir en ligne</span>
                </h2>
                <p class="text-neutral-600 text-lg mt-4"
                   x-show="shown" x-transition:enter="transition ease-out duration-700 delay-200" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                    Une plateforme complete concue specialement pour les restaurants ivoiriens.
                </p>
            </div>

            <!-- Features Grid - Principales -->
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8" x-data="{ shown: false }" x-intersect.once="shown = true">
                <!-- Feature 1: Site mobile -->
                <div class="group" x-show="shown" x-transition:enter="transition ease-out duration-700" x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0" style="transition-delay: 0ms">
                    <div class="card card-hover p-6 lg:p-8 bg-white border-2 border-transparent hover:border-primary-200 hover:shadow-xl transition-all duration-500 h-full">
                        <div class="w-14 h-14 lg:w-16 lg:h-16 bg-gradient-to-br from-primary-100 to-primary-200 rounded-2xl flex items-center justify-center mb-5 group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-7 h-7 lg:w-8 lg:h-8 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg lg:text-xl font-bold text-neutral-900 mb-2 group-hover:text-primary-600 transition-colors">Site mobile optimisé</h3>
                        <p class="text-neutral-600 text-sm lg:text-base leading-relaxed">
                            Un site de commande responsive, rapide et beau sur tous les appareils. Vos clients commandent en quelques clics.
                        </p>
                    </div>
                </div>

                <!-- Feature 2: Paiement Mobile Money -->
                <div class="group" x-show="shown" x-transition:enter="transition ease-out duration-700" x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0" style="transition-delay: 50ms">
                    <div class="card card-hover p-6 lg:p-8 bg-white border-2 border-transparent hover:border-secondary-200 hover:shadow-xl transition-all duration-500 h-full">
                        <div class="w-14 h-14 lg:w-16 lg:h-16 bg-gradient-to-br from-secondary-100 to-secondary-200 rounded-2xl flex items-center justify-center mb-5 group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-7 h-7 lg:w-8 lg:h-8 text-secondary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg lg:text-xl font-bold text-neutral-900 mb-2 group-hover:text-secondary-600 transition-colors">Paiement Mobile Money</h3>
                        <p class="text-neutral-600 text-sm lg:text-base leading-relaxed">
                            Recevez des paiements via Orange Money, MTN MoMo, Wave et Moov Money en toute sécurité.
                        </p>
                    </div>
                </div>

                <!-- Feature 3: Gestion des commandes -->
                <div class="group" x-show="shown" x-transition:enter="transition ease-out duration-700" x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0" style="transition-delay: 100ms">
                    <div class="card card-hover p-6 lg:p-8 bg-white border-2 border-transparent hover:border-accent-200 hover:shadow-xl transition-all duration-500 h-full">
                        <div class="w-14 h-14 lg:w-16 lg:h-16 bg-gradient-to-br from-accent-100 to-accent-200 rounded-2xl flex items-center justify-center mb-5 group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-7 h-7 lg:w-8 lg:h-8 text-accent-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/>
                            </svg>
                        </div>
                        <h3 class="text-lg lg:text-xl font-bold text-neutral-900 mb-2 group-hover:text-accent-600 transition-colors">Gestion des commandes</h3>
                        <p class="text-neutral-600 text-sm lg:text-base leading-relaxed">
                            Dashboard temps réel, vue Kanban et mode Rush pour gérer vos commandes efficacement.
                        </p>
                    </div>
                </div>

                <!-- Feature 4: Menu personnalisable -->
                <div class="group" x-show="shown" x-transition:enter="transition ease-out duration-700" x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0" style="transition-delay: 150ms">
                    <div class="card card-hover p-6 lg:p-8 bg-white border-2 border-transparent hover:border-blue-200 hover:shadow-xl transition-all duration-500 h-full">
                        <div class="w-14 h-14 lg:w-16 lg:h-16 bg-gradient-to-br from-blue-100 to-blue-200 rounded-2xl flex items-center justify-center mb-5 group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-7 h-7 lg:w-8 lg:h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                        </div>
                        <h3 class="text-lg lg:text-xl font-bold text-neutral-900 mb-2 group-hover:text-blue-600 transition-colors">Menu personnalisable</h3>
                        <p class="text-neutral-600 text-sm lg:text-base leading-relaxed">
                            Catégories, plats avec photos, options, prix en FCFA. Mise à jour instantanée de votre carte.
                        </p>
                    </div>
                </div>

                <!-- Feature 5: Gestion du stock -->
                <div class="group" x-show="shown" x-transition:enter="transition ease-out duration-700" x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0" style="transition-delay: 200ms">
                    <div class="card card-hover p-6 lg:p-8 bg-white border-2 border-transparent hover:border-amber-200 hover:shadow-xl transition-all duration-500 h-full">
                        <div class="w-14 h-14 lg:w-16 lg:h-16 bg-gradient-to-br from-amber-100 to-amber-200 rounded-2xl flex items-center justify-center mb-5 group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-7 h-7 lg:w-8 lg:h-8 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                        <h3 class="text-lg lg:text-xl font-bold text-neutral-900 mb-2 group-hover:text-amber-600 transition-colors">Gestion du stock</h3>
                        <p class="text-neutral-600 text-sm lg:text-base leading-relaxed">
                            Suivez vos ingrédients en temps réel, alertes de stock bas et gestion des fournisseurs.
                        </p>
                    </div>
                </div>

                <!-- Feature 6: Réservations -->
                <div class="group" x-show="shown" x-transition:enter="transition ease-out duration-700" x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0" style="transition-delay: 250ms">
                    <div class="card card-hover p-6 lg:p-8 bg-white border-2 border-transparent hover:border-indigo-200 hover:shadow-xl transition-all duration-500 h-full">
                        <div class="w-14 h-14 lg:w-16 lg:h-16 bg-gradient-to-br from-indigo-100 to-indigo-200 rounded-2xl flex items-center justify-center mb-5 group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-7 h-7 lg:w-8 lg:h-8 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg lg:text-xl font-bold text-neutral-900 mb-2 group-hover:text-indigo-600 transition-colors">Réservations en ligne</h3>
                        <p class="text-neutral-600 text-sm lg:text-base leading-relaxed">
                            Vos clients réservent une table directement depuis votre site. Gérez les créneaux facilement.
                        </p>
                    </div>
                </div>

                <!-- Feature 7: Statistiques -->
                <div class="group" x-show="shown" x-transition:enter="transition ease-out duration-700" x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0" style="transition-delay: 300ms">
                    <div class="card card-hover p-6 lg:p-8 bg-white border-2 border-transparent hover:border-yellow-200 hover:shadow-xl transition-all duration-500 h-full">
                        <div class="w-14 h-14 lg:w-16 lg:h-16 bg-gradient-to-br from-yellow-100 to-yellow-200 rounded-2xl flex items-center justify-center mb-5 group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-7 h-7 lg:w-8 lg:h-8 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg lg:text-xl font-bold text-neutral-900 mb-2 group-hover:text-yellow-600 transition-colors">Statistiques & Rapports</h3>
                        <p class="text-neutral-600 text-sm lg:text-base leading-relaxed">
                            Analysez vos ventes, plats populaires, CA journalier. Exportez vos données en Excel.
                        </p>
                    </div>
                </div>

                <!-- Feature 8: Avis clients -->
                <div class="group" x-show="shown" x-transition:enter="transition ease-out duration-700" x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0" style="transition-delay: 350ms">
                    <div class="card card-hover p-6 lg:p-8 bg-white border-2 border-transparent hover:border-pink-200 hover:shadow-xl transition-all duration-500 h-full">
                        <div class="w-14 h-14 lg:w-16 lg:h-16 bg-gradient-to-br from-pink-100 to-pink-200 rounded-2xl flex items-center justify-center mb-5 group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-7 h-7 lg:w-8 lg:h-8 text-pink-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg lg:text-xl font-bold text-neutral-900 mb-2 group-hover:text-pink-600 transition-colors">Avis clients</h3>
                        <p class="text-neutral-600 text-sm lg:text-base leading-relaxed">
                            Collectez les avis après chaque commande. Répondez et améliorez votre réputation.
                        </p>
                    </div>
                </div>

                <!-- Feature 9: Multi-utilisateurs -->
                <div class="group" x-show="shown" x-transition:enter="transition ease-out duration-700" x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0" style="transition-delay: 400ms">
                    <div class="card card-hover p-6 lg:p-8 bg-white border-2 border-transparent hover:border-purple-200 hover:shadow-xl transition-all duration-500 h-full">
                        <div class="w-14 h-14 lg:w-16 lg:h-16 bg-gradient-to-br from-purple-100 to-purple-200 rounded-2xl flex items-center justify-center mb-5 group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-7 h-7 lg:w-8 lg:h-8 text-purple-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg lg:text-xl font-bold text-neutral-900 mb-2 group-hover:text-purple-600 transition-colors">Gestion d'équipe</h3>
                        <p class="text-neutral-600 text-sm lg:text-base leading-relaxed">
                            Invitez vos employés avec des accès personnalisés pour gérer les commandes en toute sécurité.
                        </p>
                    </div>
                </div>

                <!-- Feature 10: Codes promo -->
                <div class="group" x-show="shown" x-transition:enter="transition ease-out duration-700" x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0" style="transition-delay: 450ms">
                    <div class="card card-hover p-6 lg:p-8 bg-white border-2 border-transparent hover:border-emerald-200 hover:shadow-xl transition-all duration-500 h-full">
                        <div class="w-14 h-14 lg:w-16 lg:h-16 bg-gradient-to-br from-emerald-100 to-emerald-200 rounded-2xl flex items-center justify-center mb-5 group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-7 h-7 lg:w-8 lg:h-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg lg:text-xl font-bold text-neutral-900 mb-2 group-hover:text-emerald-600 transition-colors">Codes promo</h3>
                        <p class="text-neutral-600 text-sm lg:text-base leading-relaxed">
                            Créez des codes de réduction pour fidéliser vos clients et booster vos ventes.
                        </p>
                    </div>
                </div>

                <!-- Feature 11: QR Code -->
                <div class="group" x-show="shown" x-transition:enter="transition ease-out duration-700" x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0" style="transition-delay: 500ms">
                    <div class="card card-hover p-6 lg:p-8 bg-white border-2 border-transparent hover:border-cyan-200 hover:shadow-xl transition-all duration-500 h-full">
                        <div class="w-14 h-14 lg:w-16 lg:h-16 bg-gradient-to-br from-cyan-100 to-cyan-200 rounded-2xl flex items-center justify-center mb-5 group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-7 h-7 lg:w-8 lg:h-8 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h2M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg lg:text-xl font-bold text-neutral-900 mb-2 group-hover:text-cyan-600 transition-colors">QR Code personnalisé</h3>
                        <p class="text-neutral-600 text-sm lg:text-base leading-relaxed">
                            Générez un QR code unique pour votre restaurant. Imprimez-le et placez-le sur vos tables.
                        </p>
                    </div>
                </div>

                <!-- Feature 12: Livraison -->
                <div class="group" x-show="shown" x-transition:enter="transition ease-out duration-700" x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0" style="transition-delay: 550ms">
                    <div class="card card-hover p-6 lg:p-8 bg-white border-2 border-transparent hover:border-rose-200 hover:shadow-xl transition-all duration-500 h-full">
                        <div class="w-14 h-14 lg:w-16 lg:h-16 bg-gradient-to-br from-rose-100 to-rose-200 rounded-2xl flex items-center justify-center mb-5 group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-7 h-7 lg:w-8 lg:h-8 text-rose-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
                            </svg>
                        </div>
                        <h3 class="text-lg lg:text-xl font-bold text-neutral-900 mb-2 group-hover:text-rose-600 transition-colors">Livraison & Sur place</h3>
                        <p class="text-neutral-600 text-sm lg:text-base leading-relaxed">
                            Gérez les commandes à emporter, sur place et en livraison. Définissez vos zones et frais.
                        </p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Testimonials Section -->
    <section class="py-24 sm:py-28 bg-gradient-to-b from-neutral-50 via-white to-neutral-50 relative overflow-hidden">
        <div class="absolute top-0 right-0 w-[500px] h-[500px] bg-primary-100/20 rounded-full blur-[120px]"></div>
        <div class="absolute bottom-0 left-0 w-[400px] h-[400px] bg-secondary-100/20 rounded-full blur-[100px]"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Section Header -->
            <div class="text-center max-w-3xl mx-auto mb-16 sm:mb-20" x-data="{ shown: false }" x-intersect.once="shown = true">
                <span class="inline-flex items-center gap-2 px-4 py-2 bg-yellow-100 text-yellow-700 rounded-full text-sm font-semibold mb-6"
                      x-show="shown" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                    </svg>
                    Avis clients
                </span>
                <h2 class="font-display text-3xl sm:text-4xl md:text-5xl font-bold text-neutral-900 leading-tight"
                    x-show="shown" x-transition:enter="transition ease-out duration-700 delay-100" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                    Ils nous font <span class="text-gradient">confiance</span>
                </h2>
                <p class="text-neutral-600 text-lg mt-4"
                   x-show="shown" x-transition:enter="transition ease-out duration-700 delay-200" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                    Des restaurateurs ivoiriens racontent leur experience avec MenuPro
                </p>
            </div>

            <!-- Testimonials Grid -->
            <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6 lg:gap-8" x-data="{ shown: false }" x-intersect.once="shown = true">
                @php
                    $testimonials = [
                        ['name' => 'Kouame Yao', 'role' => 'Gerant, Le Maquis du Port', 'location' => 'Abidjan', 'rating' => 5, 'text' => 'Depuis que j\'utilise MenuPro, mes commandes ont augmente de 40%. Le paiement Mobile Money est un vrai plus pour mes clients.', 'avatar' => '👨‍🍳', 'highlight' => '+40% commandes'],
                        ['name' => 'Awa Diallo', 'role' => 'Proprietaire, Chez Awa', 'location' => 'Cocody', 'rating' => 5, 'text' => 'Interface simple et intuitive. J\'ai pu creer mon menu en moins d\'une heure. Le support WhatsApp est tres reactif !', 'avatar' => '👩‍🍳', 'highlight' => 'Menu en 1h'],
                        ['name' => 'Ibrahim Kone', 'role' => 'Directeur, Restaurant Le Sahel', 'location' => 'Marcory', 'rating' => 5, 'text' => 'Mes employes gerent les commandes facilement. Les statistiques m\'aident a mieux comprendre mes ventes.', 'avatar' => '👨‍💼', 'highlight' => 'Gestion facile'],
                    ];
                @endphp

                @foreach($testimonials as $index => $testimonial)
                    <div x-show="shown"
                         x-transition:enter="transition ease-out duration-700"
                         x-transition:enter-start="opacity-0 translate-y-8"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         style="transition-delay: {{ $index * 150 }}ms"
                         class="group">
                        <div class="relative bg-white rounded-3xl p-8 shadow-lg shadow-neutral-200/50 border-2 border-neutral-100 h-full flex flex-col hover:border-primary-200 hover:shadow-xl hover:-translate-y-1 transition-all duration-500 overflow-hidden">
                            <!-- Decorative quote mark -->
                            <div class="absolute -top-2 -right-2 text-8xl font-serif text-primary-100/60 leading-none select-none pointer-events-none group-hover:text-primary-200/60 transition-colors">"</div>

                            <!-- Top: Stars + Highlight badge -->
                            <div class="flex items-center justify-between mb-5 relative z-10">
                                <div class="flex gap-0.5">
                                    @for($i = 0; $i < $testimonial['rating']; $i++)
                                        <svg class="w-5 h-5 text-yellow-400 drop-shadow-sm" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    @endfor
                                </div>
                                <span class="text-xs font-bold text-primary-600 bg-primary-50 px-2.5 py-1 rounded-full">{{ $testimonial['highlight'] }}</span>
                            </div>

                            <!-- Quote -->
                            <blockquote class="text-neutral-700 leading-relaxed flex-1 text-[15px] relative z-10">
                                "{{ $testimonial['text'] }}"
                            </blockquote>

                            <!-- Author -->
                            <div class="flex items-center gap-4 mt-6 pt-6 border-t border-neutral-100 relative z-10">
                                <div class="w-14 h-14 bg-gradient-to-br from-primary-100 to-primary-200 rounded-2xl flex items-center justify-center text-2xl shadow-md shadow-primary-100/50 group-hover:scale-110 transition-transform duration-300">
                                    {{ $testimonial['avatar'] }}
                                </div>
                                <div>
                                    <div class="font-bold text-neutral-900">{{ $testimonial['name'] }}</div>
                                    <div class="text-sm text-neutral-500">{{ $testimonial['role'] }}</div>
                                    <div class="text-xs text-primary-500 flex items-center gap-1 mt-0.5 font-medium">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.05 4.05a7 7 0 119.9 9.9L10 18.9l-4.95-4.95a7 7 0 010-9.9zM10 11a2 2 0 100-4 2 2 0 000 4z" clip-rule="evenodd"/>
                                        </svg>
                                        {{ $testimonial['location'] }}
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            <!-- Social proof bar -->
            <div class="mt-12 flex flex-col sm:flex-row items-center justify-center gap-6 sm:gap-10" x-data="{ shown: false }" x-intersect.once="shown = true"
                 x-show="shown" x-transition:enter="transition ease-out duration-700 delay-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                <div class="flex items-center gap-3">
                    <div class="flex -space-x-2">
                        <div class="w-8 h-8 bg-primary-200 rounded-full ring-2 ring-white flex items-center justify-center text-xs">👨‍🍳</div>
                        <div class="w-8 h-8 bg-secondary-200 rounded-full ring-2 ring-white flex items-center justify-center text-xs">👩‍🍳</div>
                        <div class="w-8 h-8 bg-accent-200 rounded-full ring-2 ring-white flex items-center justify-center text-xs">👨‍💼</div>
                        <div class="w-8 h-8 bg-primary-500 rounded-full ring-2 ring-white flex items-center justify-center text-white text-[10px] font-bold">+{{ $stats['raw']['restaurants'] > 10 ? $stats['raw']['restaurants'] - 3 : '7' }}</div>
                    </div>
                    <span class="text-sm text-neutral-500 font-medium">restaurants satisfaits</span>
                </div>
                <div class="hidden sm:block w-px h-6 bg-neutral-200"></div>
                <div class="flex items-center gap-2">
                    <div class="flex gap-0.5">
                        @for($i = 0; $i < 5; $i++)
                            <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        @endfor
                    </div>
                    <span class="text-sm text-neutral-500 font-medium">4.9/5 de satisfaction</span>
                </div>
            </div>
        </div>
    </section>

    <!-- Pricing Preview Section -->
    <section id="pricing" class="py-24 bg-neutral-950 relative overflow-hidden">
        <!-- Background elements -->
        <div class="absolute inset-0">
            <div class="absolute top-0 left-1/4 w-[500px] h-[500px] bg-primary-500/10 rounded-full blur-[120px]"></div>
            <div class="absolute bottom-0 right-1/4 w-[400px] h-[400px] bg-accent-500/10 rounded-full blur-[100px]"></div>
        </div>
        
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16" x-data="{ shown: false }" x-intersect.once="shown = true">
                <span class="text-primary-400 font-semibold text-sm uppercase tracking-wider"
                      x-show="shown" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">Tarifs</span>
                <h2 class="font-display text-4xl md:text-5xl font-bold text-white mt-4"
                    x-show="shown" x-transition:enter="transition ease-out duration-700 delay-100" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                    Un seul plan, toutes les fonctionnalités
                </h2>
                <p class="text-neutral-400 text-lg mt-4"
                   x-show="shown" x-transition:enter="transition ease-out duration-700 delay-200" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                    Pas de choix compliqué. Tout est inclus. Économisez jusqu'à 15% avec l'abonnement annuel.
                </p>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8" x-data="pricingCalculator()" x-init="init()">
                <!-- Left Column: Features & Add-ons -->
                <div class="lg:col-span-2 space-y-8">
                    <!-- Features List -->
                    <div class="bg-neutral-900/80 backdrop-blur-xl rounded-3xl p-8 border border-neutral-800" x-data="{ shown: false }" x-intersect.once="shown = true"
                         x-show="shown" x-transition:enter="transition ease-out duration-700" x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0">
                        <h2 class="text-2xl font-bold text-white mb-6 flex items-center gap-3">
                            <div class="w-10 h-10 bg-primary-500/20 rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            Fonctionnalités incluses
                        </h2>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                            @php
                                $features = [
                                    ['icon' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253', 'text' => '100 plats max'],
                                    ['icon' => 'M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z', 'text' => '30 catégories'],
                                    ['icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z', 'text' => '5 employés'],
                                    ['icon' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01', 'text' => '2 000 commandes/mois'],
                                    ['icon' => 'M13 10V3L4 14h7v7l9-11h-7z', 'text' => 'Gestion livraison'],
                                    ['icon' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z', 'text' => 'Stock en temps réel'],
                                    ['icon' => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z', 'text' => 'Avis clients'],
                                    ['icon' => 'M3 10h18M7 15h1m4 0h1m-6 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v4a3 3 0 003 3z', 'text' => 'Paiement Mobile Money'],
                                ];
                            @endphp
                            @foreach($features as $feature)
                                <div class="flex items-center gap-3 p-3 rounded-xl hover:bg-neutral-800/50 transition-colors group">
                                    <div class="flex-shrink-0 w-8 h-8 rounded-lg bg-primary-500/10 flex items-center justify-center group-hover:bg-primary-500 transition-colors">
                                        <svg class="w-4 h-4 text-primary-400 group-hover:text-white transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $feature['icon'] }}"/>
                                        </svg>
                                    </div>
                                    <span class="text-neutral-300 group-hover:text-white transition-colors font-medium text-sm">{{ $feature['text'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    </div>

                    <!-- Add-ons Section -->
                    <div class="bg-neutral-900/80 backdrop-blur-xl rounded-3xl p-8 border border-neutral-800" x-data="{ shown: false }" x-intersect.once="shown = true"
                         x-show="shown" x-transition:enter="transition ease-out duration-700 delay-150" x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0">
                        <h2 class="text-2xl font-bold text-white mb-4 flex items-center gap-3">
                            <div class="w-10 h-10 bg-primary-500/20 rounded-xl flex items-center justify-center">
                                <svg class="w-6 h-6 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                            </div>
                            Add-ons optionnels
                        </h2>
                        <p class="text-neutral-500 mb-6">Personnalisez votre plan selon vos besoins</p>
                        <div class="space-y-3">
                            @php
                                $addons = [
                                    ['id' => 'support', 'name' => 'Support Prioritaire', 'price' => 5000, 'description' => 'Réponse garantie sous 2h'],
                                    ['id' => 'domain', 'name' => 'Domaine Personnalisé', 'price' => 3000, 'description' => 'Votre propre nom de domaine'],
                                    ['id' => 'employees', 'name' => 'Employés Supplémentaires', 'price' => 2000, 'description' => 'Par employé supplémentaire'],
                                    ['id' => 'dishes', 'name' => 'Plats Supplémentaires', 'price' => 500, 'description' => 'Par lot de 10 plats'],
                                ];
                            @endphp
                            @foreach($addons as $addon)
                                <label class="flex items-center justify-between p-4 rounded-xl border-2 border-neutral-800 hover:border-primary-500/50 cursor-pointer transition-all group bg-neutral-800/30 hover:bg-neutral-800/50"
                                       :class="{ 'border-primary-500 bg-neutral-800/70': selectedAddons.includes('{{ $addon['id'] }}') }">
                                    <div class="flex items-center gap-4 flex-1">
                                        <input type="checkbox" 
                                               x-model="selectedAddons" 
                                               value="{{ $addon['id'] }}"
                                               class="w-5 h-5 rounded border-neutral-600 bg-neutral-700 text-primary-500 focus:ring-primary-500 focus:ring-2 cursor-pointer">
                                        <div class="flex-1">
                                            <div class="font-semibold text-white group-hover:text-primary-400 transition-colors">{{ $addon['name'] }}</div>
                                            <div class="text-sm text-neutral-500">{{ $addon['description'] }}</div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-lg font-bold text-primary-400">{{ number_format($addon['price'], 0, ',', ' ') }} F</div>
                                        <div class="text-xs text-neutral-500">/mois</div>
                                    </div>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>

                <!-- Right Column: Sticky Price Card -->
                <div class="lg:col-span-1">
                    <div class="sticky top-24" x-data="{ shown: false }" x-intersect.once="shown = true"
                         x-show="shown" x-transition:enter="transition ease-out duration-700 delay-300" x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0">
                        <div class="relative bg-gradient-to-br from-neutral-900 to-neutral-950 rounded-3xl p-8 border-2 border-primary-500/30 shadow-2xl shadow-primary-500/10">
                            <!-- Badge MEILLEUR pour Annuel -->
                            <div x-show="billingCycle === 'annual'" 
                                 x-transition
                                 class="absolute -top-4 left-1/2 -translate-x-1/2 z-10">
                                <div class="bg-gradient-to-r from-primary-500 to-primary-600 text-white px-6 py-2 rounded-full text-sm font-bold shadow-lg flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                    <span>MEILLEUR</span>
                                </div>
                            </div>

                            <div class="text-center mb-8 pt-4">
                                <h3 class="text-3xl font-bold text-white mb-2">MenuPro</h3>
                                <p class="text-neutral-400 text-sm">Plan unique • Toutes les fonctionnalités</p>
                            </div>

                            <!-- Billing Cycle Toggle -->
                            <div class="mb-8">
                                <div class="grid grid-cols-2 gap-2 bg-neutral-800/50 p-1.5 rounded-xl">
                                    <template x-for="cycle in cycles" :key="cycle.id">
                                        <button @click="billingCycle = cycle.id"
                                                :class="{
                                                    'bg-primary-500 text-white shadow-lg shadow-primary-500/30': billingCycle === cycle.id,
                                                    'text-neutral-400 hover:text-white': billingCycle !== cycle.id
                                                }"
                                                class="px-4 py-2.5 rounded-lg text-sm font-semibold transition-all">
                                            <span x-text="cycle.label"></span>
                                        </button>
                                    </template>
                                </div>
                            </div>

                            <!-- Price Display -->
                            <div class="text-center mb-6">
                                <div class="mb-2">
                                    <span class="text-5xl font-bold text-white" x-text="formatPrice(basePrice)"></span>
                                    <span class="text-neutral-400 text-lg ml-2">FCFA</span>
                                </div>
                                <div class="text-sm text-secondary-400 font-medium" x-show="billingCycle !== 'monthly'">
                                    <span x-text="'Économisez ' + formatPrice(discountAmount) + ' FCFA'"></span>
                                </div>
                                <div class="text-xs text-neutral-500 mt-1" x-show="billingCycle === 'monthly'">
                                    <span x-text="formatPrice(basePrice) + ' FCFA/mois'"></span>
                                </div>
                            </div>

                            <!-- Add-ons Total -->
                            <div x-show="addonsTotal > 0" 
                                 x-transition
                                 class="mb-6 pt-6 border-t border-neutral-800">
                                <div class="space-y-2">
                                    <div class="flex justify-between text-sm text-neutral-400">
                                        <span>Add-ons sélectionnés</span>
                                        <span x-text="formatPrice(addonsTotal) + ' FCFA'"></span>
                                    </div>
                                </div>
                            </div>

                            <!-- Total Price -->
                            <div class="mb-8 pt-6 border-t-2 border-primary-500/30">
                                <div class="flex justify-between items-baseline mb-2">
                                    <span class="text-lg font-semibold text-neutral-300">Total</span>
                                    <span class="text-3xl font-bold text-primary-400" x-text="formatPrice(totalPrice)"></span>
                                </div>
                                <div class="text-xs text-neutral-500 text-right">
                                    <span x-text="'Soit ' + formatPrice(totalPrice / getMonths()) + ' FCFA/mois'"></span>
                                </div>
                            </div>

                            <!-- CTA Button -->
                            <a :href="'{{ route('register') }}?plan=menupro&cycle=' + billingCycle + '&addons=' + selectedAddons.join(',')"
                               class="block w-full bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white font-bold py-4 px-6 rounded-xl text-center transition-all transform hover:scale-105 shadow-lg shadow-primary-500/30 hover:shadow-xl mb-4">
                                Commencer maintenant
                            </a>

                            <div class="text-center">
                                <div class="inline-flex items-center gap-2 text-xs text-neutral-500">
                                    <svg class="w-4 h-4 text-secondary-500" fill="currentColor" viewBox="0 0 20 20">
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
                <a href="{{ route('pricing') }}" class="text-primary-400 font-semibold hover:text-primary-300 inline-flex items-center gap-2 transition-colors">
                    Voir tous les détails et les add-ons
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                    </svg>
                </a>
            </div>
        </div>
    </section>

    <!-- FAQ Section -->
    <section id="faq" class="py-24 sm:py-28 bg-gradient-to-b from-neutral-50 to-white relative overflow-hidden">
        <div class="absolute top-0 left-0 w-[400px] h-[400px] bg-primary-100/20 rounded-full blur-[120px]"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid lg:grid-cols-5 gap-12 lg:gap-16">
                <!-- Left Column: Header + CTA -->
                <div class="lg:col-span-2" x-data="{ shown: false }" x-intersect.once="shown = true">
                    <div class="lg:sticky lg:top-28">
                        <span class="inline-flex items-center gap-2 px-4 py-2 bg-primary-100 text-primary-700 rounded-full text-sm font-semibold mb-6"
                              x-show="shown" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            FAQ
                        </span>
                        <h2 class="font-display text-3xl sm:text-4xl md:text-5xl font-bold text-neutral-900 leading-tight"
                            x-show="shown" x-transition:enter="transition ease-out duration-700 delay-100" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                            Questions <span class="text-gradient">frequentes</span>
                        </h2>
                        <p class="text-neutral-600 text-lg mt-4 leading-relaxed"
                           x-show="shown" x-transition:enter="transition ease-out duration-700 delay-200" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                            Vous avez des questions ? Nous avons les reponses. Si vous ne trouvez pas ce que vous cherchez, contactez-nous.
                        </p>

                        <!-- Contact CTA -->
                        <div class="mt-8 p-6 bg-gradient-to-br from-primary-50 to-orange-50 rounded-2xl border border-primary-100"
                             x-show="shown" x-transition:enter="transition ease-out duration-700 delay-300" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                            <div class="flex items-center gap-3 mb-3">
                                <div class="w-10 h-10 bg-green-500 rounded-xl flex items-center justify-center shadow-lg shadow-green-500/30">
                                    <svg class="w-5 h-5 text-white" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
                                    </svg>
                                </div>
                                <div>
                                    <div class="font-bold text-neutral-900 text-sm">Besoin d'aide ?</div>
                                    <div class="text-xs text-neutral-500">Reponse en moins de 2h</div>
                                </div>
                            </div>
                            @php
                                $faqWhatsapp = \App\Models\SystemSetting::get('contact_whatsapp', \App\Models\SystemSetting::get('contact_phone', ''));
                            @endphp
                            @if($faqWhatsapp)
                                <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $faqWhatsapp) }}?text=Bonjour%20MenuPro,%20j'ai%20une%20question" target="_blank" class="inline-flex items-center gap-2 text-sm font-semibold text-green-700 hover:text-green-800 transition-colors">
                                    Ecrire sur WhatsApp
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Right Column: FAQ Accordion -->
                <div class="lg:col-span-3" x-data="{ active: 1 }">
                    @php
                        $faqs = [
                            ['q' => 'Comment fonctionne la garantie satisfait ou rembourse ?', 'a' => 'Apres votre inscription et paiement, votre restaurant sera active sous 24h. Si vous n\'etes pas satisfait dans les 7 premiers jours, nous vous remboursons integralement, sans questions. Testez d\'abord notre demo pour voir les fonctionnalites.', 'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
                            ['q' => 'Quels moyens de paiement sont acceptes ?', 'a' => 'Vos clients peuvent payer via Orange Money, MTN MoMo, Wave et Moov Money. Les paiements sont securises et vous recevez l\'argent directement sur votre compte Mobile Money.', 'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'],
                            ['q' => 'Puis-je annuler mon abonnement ?', 'a' => 'Oui, vous pouvez annuler a tout moment depuis votre tableau de bord. Vos donnees sont conservees pendant 30 jours apres l\'expiration, vous permettant de reactiver facilement si besoin.', 'icon' => 'M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z'],
                            ['q' => 'Comment mes clients accedent-ils a mon menu ?', 'a' => 'Votre restaurant aura une URL unique (ex: menupro.ci/votre-restaurant). Vous pouvez partager ce lien sur vos reseaux sociaux, par QR code ou SMS. Pas d\'application a telecharger pour vos clients.', 'icon' => 'M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1'],
                            ['q' => 'Combien de temps pour configurer mon restaurant ?', 'a' => 'En moyenne 15 minutes ! Creez votre compte (2 min), ajoutez vos plats avec photos et prix (10 min), configurez vos horaires (3 min). Votre menu est immediatement en ligne et pret a recevoir des commandes.', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
                            ['q' => 'Est-ce que MenuPro fonctionne hors ligne ?', 'a' => 'Oui ! Votre menu est accessible meme sans connexion internet grace a notre technologie PWA. Les commandes seront synchronisees automatiquement des que la connexion revient. Ideal pour les zones a couverture instable.', 'icon' => 'M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.858 15.355-5.858 21.213 0'],
                        ];
                    @endphp

                    <div class="space-y-4" x-data="{ shown: false }" x-intersect.once="shown = true">
                        @foreach($faqs as $index => $faq)
                            <div class="group"
                                 x-show="shown" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0"
                                 style="transition-delay: {{ $index * 80 }}ms">
                                <div class="bg-white rounded-2xl border-2 transition-all duration-300 shadow-sm overflow-hidden"
                                     :class="active === {{ $index + 1 }} ? 'border-primary-300 shadow-lg shadow-primary-100/50' : 'border-neutral-100 hover:border-neutral-200'">
                                    <button @click="active = active === {{ $index + 1 }} ? null : {{ $index + 1 }}" class="w-full p-5 sm:p-6 text-left flex items-center gap-4">
                                        <div class="w-10 h-10 rounded-xl flex items-center justify-center flex-shrink-0 transition-all duration-300"
                                             :class="active === {{ $index + 1 }} ? 'bg-primary-500 shadow-lg shadow-primary-500/30' : 'bg-neutral-100 group-hover:bg-primary-50'">
                                            <svg class="w-5 h-5 transition-colors duration-300"
                                                 :class="active === {{ $index + 1 }} ? 'text-white' : 'text-neutral-400 group-hover:text-primary-500'"
                                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $faq['icon'] }}"/>
                                            </svg>
                                        </div>
                                        <span class="font-semibold text-neutral-900 flex-1 text-[15px]">{{ $faq['q'] }}</span>
                                        <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0 transition-all duration-300"
                                             :class="active === {{ $index + 1 }} ? 'bg-primary-100 rotate-180' : 'bg-neutral-50'">
                                            <svg class="w-4 h-4 transition-colors" :class="active === {{ $index + 1 }} ? 'text-primary-600' : 'text-neutral-400'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                            </svg>
                                        </div>
                                    </button>
                                    <div x-show="active === {{ $index + 1 }}"
                                         x-transition:enter="transition ease-out duration-300"
                                         x-transition:enter-start="opacity-0"
                                         x-transition:enter-end="opacity-100"
                                         x-transition:leave="transition ease-in duration-200"
                                         x-transition:leave-start="opacity-100"
                                         x-transition:leave-end="opacity-0"
                                         class="overflow-hidden">
                                        <div class="px-5 sm:px-6 pb-6 pl-[4.5rem]">
                                            <p class="text-neutral-600 leading-relaxed">{{ $faq['a'] }}</p>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- CTA Section -->
    <section id="contact" class="py-28 sm:py-32 bg-gradient-to-br from-neutral-950 via-neutral-900 to-neutral-950 relative overflow-hidden">
        <!-- Background Effects -->
        <div class="absolute inset-0">
            <div class="absolute inset-0 bg-[linear-gradient(rgba(249,115,22,0.05)_1px,transparent_1px),linear-gradient(90deg,rgba(249,115,22,0.05)_1px,transparent_1px)] bg-[size:60px_60px] [mask-image:radial-gradient(ellipse_at_center,black_30%,transparent_70%)]"></div>
        </div>
        <div class="absolute top-1/2 left-1/2 -translate-x-1/2 -translate-y-1/2 w-[800px] h-[800px] bg-primary-500/15 rounded-full blur-[200px]"></div>
        <div class="absolute top-10 right-[10%] w-72 h-72 bg-accent-500/10 rounded-full blur-[100px] animate-float"></div>
        <div class="absolute bottom-10 left-[10%] w-96 h-96 bg-primary-600/10 rounded-full blur-[120px] animate-float" style="animation-delay: 2s;"></div>

        <div class="relative z-10 max-w-5xl mx-auto px-4 sm:px-6 lg:px-8" x-data="{ shown: false }" x-intersect.once="shown = true">
            <!-- Centered Content -->
            <div class="text-center">
                <div class="inline-flex items-center gap-2 px-4 py-2 bg-primary-500/10 backdrop-blur-sm border border-primary-500/20 rounded-full text-primary-400 text-sm font-semibold mb-8"
                     x-show="shown" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100">
                    <span class="relative flex h-2 w-2">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-primary-400 opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-2 w-2 bg-primary-400"></span>
                    </span>
                    Commencez des aujourd'hui
                </div>

                <h2 class="font-display text-4xl md:text-5xl lg:text-6xl font-bold text-white leading-tight"
                    x-show="shown" x-transition:enter="transition ease-out duration-700 delay-100" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                    Pret a digitaliser
                    <br class="hidden sm:block">
                    <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary-400 via-primary-300 to-accent-400">votre restaurant ?</span>
                </h2>
                <p class="text-lg md:text-xl text-neutral-400 mt-6 max-w-2xl mx-auto leading-relaxed"
                   x-show="shown" x-transition:enter="transition ease-out duration-700 delay-200" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                    Rejoignez les restaurants qui font confiance a MenuPro. Configuration en 15 minutes, resultats immediats.
                </p>

                <!-- Stats mini-bar -->
                <div class="mt-10 inline-flex flex-wrap items-center justify-center gap-6 sm:gap-10 px-8 py-4 bg-white/5 backdrop-blur-sm rounded-2xl border border-white/10"
                     x-show="shown" x-transition:enter="transition ease-out duration-700 delay-300" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100">
                    <div class="text-center">
                        <div class="text-2xl sm:text-3xl font-bold text-white">15 min</div>
                        <div class="text-xs text-neutral-500 font-medium uppercase tracking-wider mt-1">Configuration</div>
                    </div>
                    <div class="w-px h-10 bg-white/10 hidden sm:block"></div>
                    <div class="text-center">
                        <div class="text-2xl sm:text-3xl font-bold text-white">0 F</div>
                        <div class="text-xs text-neutral-500 font-medium uppercase tracking-wider mt-1">Frais caches</div>
                    </div>
                    <div class="w-px h-10 bg-white/10 hidden sm:block"></div>
                    <div class="text-center">
                        <div class="text-2xl sm:text-3xl font-bold text-white">7 jours</div>
                        <div class="text-xs text-neutral-500 font-medium uppercase tracking-wider mt-1">Satisfait ou rembourse</div>
                    </div>
                </div>

                <!-- CTA Buttons -->
                <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4"
                     x-show="shown" x-transition:enter="transition ease-out duration-700 delay-400" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                    <a href="{{ route('register') }}" class="group relative inline-flex items-center justify-center gap-2 btn btn-lg bg-gradient-to-r from-primary-500 to-primary-600 text-white shadow-xl shadow-primary-500/30 hover:shadow-2xl hover:shadow-primary-500/40 transition-all transform hover:scale-105 hover:-translate-y-0.5 overflow-hidden whitespace-nowrap">
                        <span class="relative z-10 inline-flex items-center gap-2">
                            <span>Creer mon restaurant</span>
                            <svg class="w-5 h-5 shrink-0 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                            </svg>
                        </span>
                        <div class="absolute inset-0 bg-gradient-to-r from-primary-600 to-accent-500 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    </a>
                    <a href="{{ route('r.menu', ['slug' => 'demo']) }}" target="_blank" class="group inline-flex items-center justify-center gap-2 btn btn-lg bg-white/5 backdrop-blur-xl text-white border border-white/20 hover:bg-white/10 hover:border-white/40 transition-all whitespace-nowrap">
                        <svg class="w-5 h-5 shrink-0 text-primary-400 group-hover:scale-110 transition-transform" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M8 5v14l11-7z"/>
                        </svg>
                        <span>Voir la demo</span>
                    </a>
                    @php
                        $contactEmail = \App\Models\SystemSetting::get('contact_email', 'contact@menupro.ci');
                    @endphp
                    <a href="mailto:{{ $contactEmail }}" class="inline-flex items-center justify-center gap-2 btn btn-lg bg-white/5 backdrop-blur-xl text-white border border-white/20 hover:bg-white/10 hover:border-white/40 transition-all whitespace-nowrap">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        Nous contacter
                    </a>
                </div>

                <p class="text-neutral-500 mt-8 text-sm"
                   x-show="shown" x-transition:enter="transition ease-out duration-700 delay-500" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                    Pas de carte bancaire requise &bull; Support WhatsApp reactif &bull; 25 000 FCFA/mois tout inclus
                </p>
            </div>
        </div>
    </section>

    @push('scripts')
    <script>
        // Counter animation
        function counter(target) {
            return {
                count: 0,
                target: target,
                displayCount: '0',
                startCount() {
                    const duration = 2000;
                    const steps = 60;
                    const stepValue = this.target / steps;
                    const stepDuration = duration / steps;
                    
                    const interval = setInterval(() => {
                        this.count += stepValue;
                        if (this.count >= this.target) {
                            this.count = this.target;
                            clearInterval(interval);
                        }
                        
                        // Format the number
                        if (this.count >= 1000) {
                            this.displayCount = Math.round(this.count / 1000) + 'K+';
                        } else {
                            this.displayCount = Math.round(this.count).toString();
                        }
                    }, stepDuration);
                }
            }
        }

        // Pricing calculator
        function pricingCalculator() {
            return {
                billingCycle: 'monthly',
                selectedAddons: [],
                cycles: [
                    { id: 'monthly', label: 'Mensuel', months: 1, price: 25000, discount: 0 },
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
