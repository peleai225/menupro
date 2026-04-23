<x-layouts.public title="Accueil" description="MenuPro : digitalisez votre restaurant, menu en ligne, commandes et paiement Mobile Money. Solution SaaS pour restaurants en Côte d'Ivoire et ailleurs.">
    @push('head')
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "SoftwareApplication",
        "name": "MenuPro",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": "Web",
        "url": "{{ url('/') }}",
        "description": "Plateforme SaaS de commande en ligne pour restaurants en Côte d'Ivoire. Paiement Mobile Money, QR codes, gestion de stock.",
        "offers": {
            "@@type": "Offer",
            "price": "0",
            "priceCurrency": "XOF"
        },
        "author": {
            "@@type": "Organization",
            "name": "MenuPro",
            "url": "{{ url('/') }}"
        },
        "aggregateRating": {
            "@@type": "AggregateRating",
            "ratingValue": "4.8",
            "ratingCount": "150"
        }
    }
    </script>
    @endpush

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
        
        <!-- Animated Background Elements (refined, more fluid) -->
        <div class="absolute inset-0">
            {{-- Soft radial mesh (multi-layer for fluidity) --}}
            <div class="absolute inset-0" style="background:
                radial-gradient(1200px 600px at 10% 10%, rgba(249,115,22,0.18), transparent 55%),
                radial-gradient(900px 600px at 90% 20%, rgba(236,72,153,0.12), transparent 60%),
                radial-gradient(800px 700px at 50% 100%, rgba(59,130,246,0.14), transparent 60%);"></div>

            {{-- Subtle dot pattern (softer than grid) --}}
            <div class="absolute inset-0 opacity-[0.07]">
                <div class="absolute inset-0 bg-[radial-gradient(rgba(255,255,255,0.4)_1px,transparent_1px)] bg-[size:32px_32px] [mask-image:radial-gradient(ellipse_at_center,black_30%,transparent_75%)]"></div>
            </div>

            {{-- Large slow-moving orbs --}}
            <div class="absolute -top-20 -left-20 w-[520px] h-[520px] bg-primary-500/25 rounded-full blur-[130px] animate-float"></div>
            <div class="absolute -bottom-20 -right-20 w-[440px] h-[440px] bg-accent-500/20 rounded-full blur-[120px] animate-float" style="animation-delay: 2s;"></div>
            <div class="absolute top-1/3 left-1/2 -translate-x-1/2 w-[640px] h-[640px] bg-secondary-500/10 rounded-full blur-[160px] animate-float" style="animation-delay: 1s;"></div>

            {{-- SVG flowing waves (decorative) --}}
            <svg class="absolute inset-0 w-full h-full opacity-[0.06] pointer-events-none" preserveAspectRatio="none" viewBox="0 0 1200 600" aria-hidden="true">
                <defs>
                    <linearGradient id="heroWave" x1="0%" y1="0%" x2="100%" y2="100%">
                        <stop offset="0%" stop-color="#f97316"/>
                        <stop offset="100%" stop-color="#fbbf24"/>
                    </linearGradient>
                </defs>
                <path d="M0,300 C200,220 400,380 600,300 C800,220 1000,380 1200,300" stroke="url(#heroWave)" stroke-width="2" fill="none"/>
                <path d="M0,380 C200,300 400,460 600,380 C800,300 1000,460 1200,380" stroke="url(#heroWave)" stroke-width="1.5" fill="none"/>
            </svg>
        </div>

        {{-- Floating Particles (keep existing decorative layer) --}}
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
                    {{-- Badge : Social proof dynamique avec logo-marks (non texte) --}}
                    <div class="inline-flex flex-wrap items-center gap-2 sm:gap-2.5 px-3 sm:px-4 py-2 bg-white/10 backdrop-blur-xl rounded-full text-white text-xs sm:text-sm font-medium mb-8 border border-white/20 shadow-lg"
                         x-show="shown"
                         x-transition:enter="transition ease-out duration-500 delay-100"
                         x-transition:enter-start="opacity-0 translate-y-4"
                         x-transition:enter-end="opacity-100 translate-y-0">
                        <span class="flex -space-x-2">
                            {{-- Logo-mark 1 : Chef hat (Le Maquis du Port) --}}
                            <span class="w-6 h-6 sm:w-7 sm:h-7 rounded-full bg-gradient-to-br from-orange-400 to-red-500 ring-2 ring-neutral-900 flex items-center justify-center shadow-sm" title="Le Maquis du Port">
                                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-white" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M6 14c-2.2 0-4-1.8-4-4s1.8-4 4-4c.4 0 .8.1 1.2.2C7.9 4.9 9.8 4 12 4s4.1.9 4.8 2.2c.4-.1.8-.2 1.2-.2 2.2 0 4 1.8 4 4s-1.8 4-4 4v6c0 .6-.4 1-1 1H7c-.6 0-1-.4-1-1v-6z"/>
                                </svg>
                            </span>
                            {{-- Logo-mark 2 : Flame (Chez Awa - grill) --}}
                            <span class="w-6 h-6 sm:w-7 sm:h-7 rounded-full bg-gradient-to-br from-amber-400 to-orange-500 ring-2 ring-neutral-900 flex items-center justify-center shadow-sm" title="Chez Awa">
                                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-white" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M12 2s-4 6-4 10a4 4 0 008 0c0-4-4-10-4-10zm0 16a2 2 0 01-2-2c0-1 1-3 2-5 1 2 2 4 2 5a2 2 0 01-2 2z"/>
                                </svg>
                            </span>
                            {{-- Logo-mark 3 : Leaf (Le Sahel - végétal) --}}
                            <span class="w-6 h-6 sm:w-7 sm:h-7 rounded-full bg-gradient-to-br from-emerald-400 to-teal-500 ring-2 ring-neutral-900 flex items-center justify-center shadow-sm" title="Le Sahel">
                                <svg class="w-3.5 h-3.5 sm:w-4 sm:h-4 text-white" viewBox="0 0 24 24" fill="currentColor">
                                    <path d="M17 8C8 10 5.9 16.17 3.82 21.34l1.89.66.95-2.3c.48.17.98.3 1.34.3C19 20 22 3 22 3c-1 2-8 2.25-13 3.25S2 11.5 2 13.5s1.75 3.75 1.75 3.75C7 8 17 8 17 8z"/>
                                </svg>
                            </span>
                        </span>
                        <span class="hidden sm:inline-flex items-center gap-1.5">
                            <span class="font-semibold text-white">{{ $stats['raw']['restaurants'] ?? 10 }}+ restaurants actifs</span>
                            <span class="text-white/60">&middot;</span>
                            <span class="text-white/80">Côte d'Ivoire</span>
                        </span>
                        <span class="sm:hidden font-semibold text-white">{{ $stats['raw']['restaurants'] ?? 10 }}+ restaurants en CI</span>
                        <span class="relative flex h-2 w-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-emerald-400 opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 bg-emerald-400"></span>
                        </span>
                    </div>

                    <!-- Titre principal avec animation -->
                    <h1 class="font-display text-4xl sm:text-5xl md:text-6xl lg:text-7xl font-bold text-white leading-[1.05]"
                        x-show="shown"
                        x-transition:enter="transition ease-out duration-700 delay-200"
                        x-transition:enter-start="opacity-0 translate-y-6"
                        x-transition:enter-end="opacity-100 translate-y-0">
                        Votre
                        <span class="relative inline-block">
                            <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary-400 via-primary-300 to-accent-400 animate-gradient">maquis</span>
                            <svg class="absolute -bottom-1 left-0 w-full h-3 text-primary-500/40" viewBox="0 0 200 12" preserveAspectRatio="none">
                                <path d="M0,8 Q50,0 100,8 T200,8" stroke="currentColor" stroke-width="3" fill="none"/>
                            </svg>
                        </span>
                        en ligne,
                        <br class="hidden sm:block">
                        <span class="text-white/90">commandes en direct.</span>
                    </h1>

                    <!-- Description -->
                    <p class="mt-6 sm:mt-8 text-lg sm:text-xl text-neutral-300 max-w-xl mx-auto lg:mx-0 leading-relaxed"
                       x-show="shown"
                       x-transition:enter="transition ease-out duration-700 delay-300"
                       x-transition:enter-start="opacity-0 translate-y-6"
                       x-transition:enter-end="opacity-100 translate-y-0">
                        Site de commande, QR codes sur vos tables, paiements <span class="text-primary-400 font-semibold">Wave, Orange, MTN, Moov</span>.
                        La plateforme pensee pour les restaurateurs ivoiriens, pas une traduction.
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
                            <span>Des 9 900 FCFA/mois</span>
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
                                     width="384" height="768"
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

    <!-- Trusted By — Bandeau de confiance compact -->
    <section class="relative py-12 sm:py-16 bg-gradient-to-b from-white via-neutral-50 to-white border-b border-neutral-100 overflow-hidden">
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_center,rgba(249,115,22,0.04),transparent_60%)]"></div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-center gap-3 mb-8" x-data="{ shown: false }" x-intersect.once="shown = true"
                 x-show="shown" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                <div class="h-px w-12 bg-gradient-to-r from-transparent to-neutral-300"></div>
                <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-white rounded-full border border-neutral-200 shadow-sm">
                    <svg class="w-4 h-4 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    <span class="text-xs font-semibold text-neutral-700 uppercase tracking-wider">Paiements sécurisés</span>
                </div>
                <div class="h-px w-12 bg-gradient-to-l from-transparent to-neutral-300"></div>
            </div>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 sm:gap-4 max-w-4xl mx-auto" x-data="{ shown: false }" x-intersect.once="shown = true">
                <!-- Orange Money -->
                <div class="group relative flex flex-col sm:flex-row items-center gap-2 sm:gap-3 bg-white px-4 py-4 sm:py-3.5 rounded-2xl border border-neutral-200 hover:border-orange-400 hover:shadow-xl hover:shadow-orange-100/50 hover:-translate-y-1 transition-all duration-300"
                     x-show="shown" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" style="transition-delay: 0ms">
                    <div class="absolute inset-0 rounded-2xl bg-gradient-to-br from-orange-500/0 via-orange-500/0 to-orange-500/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    <img src="{{ asset('images/payments/orange-money.png') }}" alt="Orange Money" width="44" height="44" class="relative h-10 w-10 sm:h-11 sm:w-11 object-contain rounded-lg group-hover:scale-110 transition-transform duration-300">
                    <span class="relative font-semibold text-neutral-800 text-xs sm:text-sm text-center sm:text-left">Orange Money</span>
                </div>
                <!-- Wave -->
                <div class="group relative flex flex-col sm:flex-row items-center gap-2 sm:gap-3 bg-white px-4 py-4 sm:py-3.5 rounded-2xl border border-neutral-200 hover:border-blue-400 hover:shadow-xl hover:shadow-blue-100/50 hover:-translate-y-1 transition-all duration-300"
                     x-show="shown" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" style="transition-delay: 100ms">
                    <div class="absolute inset-0 rounded-2xl bg-gradient-to-br from-blue-500/0 via-blue-500/0 to-blue-500/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    <img src="{{ asset('images/payments/wave.png') }}" alt="Wave" width="44" height="44" class="relative h-10 w-10 sm:h-11 sm:w-11 object-contain rounded-lg group-hover:scale-110 transition-transform duration-300">
                    <span class="relative font-semibold text-neutral-800 text-xs sm:text-sm text-center sm:text-left">Wave</span>
                </div>
                <!-- MTN MoMo -->
                <div class="group relative flex flex-col sm:flex-row items-center gap-2 sm:gap-3 bg-white px-4 py-4 sm:py-3.5 rounded-2xl border border-neutral-200 hover:border-yellow-400 hover:shadow-xl hover:shadow-yellow-100/50 hover:-translate-y-1 transition-all duration-300"
                     x-show="shown" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" style="transition-delay: 200ms">
                    <div class="absolute inset-0 rounded-2xl bg-gradient-to-br from-yellow-500/0 via-yellow-500/0 to-yellow-500/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    <img src="{{ asset('images/payments/mtn-momo.png') }}" alt="MTN MoMo" width="44" height="44" class="relative h-10 w-10 sm:h-11 sm:w-11 object-contain rounded-lg group-hover:scale-110 transition-transform duration-300">
                    <span class="relative font-semibold text-neutral-800 text-xs sm:text-sm text-center sm:text-left">MTN MoMo</span>
                </div>
                <!-- Moov Money -->
                <div class="group relative flex flex-col sm:flex-row items-center gap-2 sm:gap-3 bg-white px-4 py-4 sm:py-3.5 rounded-2xl border border-neutral-200 hover:border-emerald-400 hover:shadow-xl hover:shadow-emerald-100/50 hover:-translate-y-1 transition-all duration-300"
                     x-show="shown" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0" style="transition-delay: 300ms">
                    <div class="absolute inset-0 rounded-2xl bg-gradient-to-br from-emerald-500/0 via-emerald-500/0 to-emerald-500/10 opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                    <img src="{{ asset('images/payments/moov-money.png') }}" alt="Moov Money" width="44" height="44" class="relative h-10 w-10 sm:h-11 sm:w-11 object-contain rounded-lg group-hover:scale-110 transition-transform duration-300">
                    <span class="relative font-semibold text-neutral-800 text-xs sm:text-sm text-center sm:text-left">Moov Money</span>
                </div>
            </div>
            <div class="mt-6 flex flex-wrap items-center justify-center gap-4 text-xs text-neutral-500">
                <span class="inline-flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                    </svg>
                    Connexion chiffrée SSL
                </span>
                <span class="hidden sm:inline text-neutral-300">•</span>
                <span class="inline-flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M2.166 4.999A11.954 11.954 0 0010 1.944 11.954 11.954 0 0017.834 5c.11.65.166 1.32.166 2.001 0 5.225-3.34 9.67-8 11.317C5.34 16.67 2 12.225 2 7c0-.682.057-1.35.166-2.001zm11.541 3.708a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    Paiement directement sur votre compte
                </span>
                <span class="hidden sm:inline text-neutral-300">•</span>
                <span class="inline-flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                        <path d="M13 7H7v6h6V7z"/>
                        <path fill-rule="evenodd" d="M7 2a1 1 0 012 0v1h2V2a1 1 0 112 0v1h2a2 2 0 012 2v2h1a1 1 0 110 2h-1v2h1a1 1 0 110 2h-1v2a2 2 0 01-2 2h-2v1a1 1 0 11-2 0v-1H9v1a1 1 0 11-2 0v-1H5a2 2 0 01-2-2v-2H2a1 1 0 110-2h1V9H2a1 1 0 010-2h1V5a2 2 0 012-2h2V2zM5 5h10v10H5V5z" clip-rule="evenodd"/>
                    </svg>
                    Zéro commission cachée
                </span>
            </div>
        </div>
    </section>

    <!-- Pourquoi MenuPro — Hero Features -->
    <section class="py-20 sm:py-28 bg-white relative overflow-hidden">
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_at_top_right,rgba(249,115,22,0.05),transparent_50%),radial-gradient(ellipse_at_bottom_left,rgba(16,185,129,0.05),transparent_50%)]"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-3xl mx-auto mb-16 sm:mb-20" x-data="{ shown: false }" x-intersect.once="shown = true">
                <span class="inline-flex items-center gap-2 px-4 py-2 bg-primary-100 text-primary-700 rounded-full text-sm font-semibold mb-6"
                      x-show="shown" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    Fait pour la Cote d'Ivoire
                </span>
                <h2 class="font-display text-3xl sm:text-4xl md:text-5xl font-bold text-neutral-900 leading-tight"
                    x-show="shown" x-transition:enter="transition ease-out duration-700 delay-100" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                    Pourquoi les restaurants choisissent <span class="text-gradient">MenuPro</span>
                </h2>
                <p class="text-neutral-600 text-lg mt-4"
                   x-show="shown" x-transition:enter="transition ease-out duration-700 delay-200" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                    Une solution pensee 100% pour le marche africain, pas une copie de solutions occidentales.
                </p>
            </div>

            <!-- Hero Feature 1 — Mobile Money -->
            <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center mb-20 sm:mb-28" x-data="{ shown: false }" x-intersect.once="shown = true">
                <div x-show="shown" x-transition:enter="transition ease-out duration-700" x-transition:enter-start="opacity-0 -translate-x-8" x-transition:enter-end="opacity-100 translate-x-0">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-emerald-100 text-emerald-700 rounded-full text-xs font-bold uppercase tracking-wider mb-4">
                        <span class="w-2 h-2 bg-emerald-500 rounded-full"></span>
                        Paiement
                    </div>
                    <h3 class="font-display text-2xl sm:text-3xl lg:text-4xl font-bold text-neutral-900 leading-tight mb-4">
                        Vos clients paient avec <span class="text-primary-500">leur telephone</span>
                    </h3>
                    <p class="text-neutral-600 text-lg leading-relaxed mb-6">
                        Pas besoin de carte bancaire. Orange Money, Wave, MTN MoMo, Moov Money — les moyens de paiement que vos clients utilisent tous les jours.
                    </p>
                    <ul class="space-y-3">
                        <li class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-primary-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <span class="text-neutral-700 font-medium">Paiement recu directement sur votre compte Wave Business</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-primary-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <span class="text-neutral-700 font-medium">Confirmation instantanee par notification</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-primary-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <span class="text-neutral-700 font-medium">Zero frais cache, transparence totale</span>
                        </li>
                    </ul>
                </div>
                <div class="relative" x-show="shown" x-transition:enter="transition ease-out duration-700 delay-200" x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0">
                    <div class="bg-gradient-to-br from-emerald-50 to-green-50 rounded-3xl p-8 sm:p-10 border border-emerald-100">
                        <div class="grid grid-cols-2 gap-4">
                            <div class="bg-white rounded-2xl p-5 shadow-lg shadow-emerald-100/50 border border-emerald-50 text-center hover:-translate-y-1 transition-transform">
                                <img src="{{ asset('images/payments/orange-money.png') }}" alt="Orange Money" width="56" height="56" class="w-14 h-14 object-contain rounded-2xl mx-auto mb-3 shadow-lg">
                                <span class="text-sm font-bold text-neutral-800">Orange Money</span>
                            </div>
                            <div class="bg-white rounded-2xl p-5 shadow-lg shadow-emerald-100/50 border border-emerald-50 text-center hover:-translate-y-1 transition-transform">
                                <img src="{{ asset('images/payments/wave.png') }}" alt="Wave" width="56" height="56" class="w-14 h-14 object-contain rounded-2xl mx-auto mb-3 shadow-lg">
                                <span class="text-sm font-bold text-neutral-800">Wave</span>
                            </div>
                            <div class="bg-white rounded-2xl p-5 shadow-lg shadow-emerald-100/50 border border-emerald-50 text-center hover:-translate-y-1 transition-transform">
                                <img src="{{ asset('images/payments/mtn-momo.png') }}" alt="MTN MoMo" width="56" height="56" class="w-14 h-14 object-contain rounded-2xl mx-auto mb-3 shadow-lg">
                                <span class="text-sm font-bold text-neutral-800">MTN MoMo</span>
                            </div>
                            <div class="bg-white rounded-2xl p-5 shadow-lg shadow-emerald-100/50 border border-emerald-50 text-center hover:-translate-y-1 transition-transform">
                                <img src="{{ asset('images/payments/moov-money.png') }}" alt="Moov Money" width="56" height="56" class="w-14 h-14 object-contain rounded-2xl mx-auto mb-3 shadow-lg">
                                <span class="text-sm font-bold text-neutral-800">Moov Money</span>
                            </div>
                        </div>
                        {{-- Success animation --}}
                        <div class="mt-6 bg-white rounded-xl p-4 border border-emerald-100 flex items-center gap-3">
                            <div class="w-10 h-10 bg-emerald-500 rounded-full flex items-center justify-center flex-shrink-0">
                                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <div>
                                <div class="font-bold text-sm text-emerald-800">Paiement recu !</div>
                                <div class="text-xs text-neutral-500">Wave - 12 500 FCFA - il y a 3s</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Hero Feature 2 — QR Code par table -->
            <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center mb-20 sm:mb-28" x-data="{ shown: false }" x-intersect.once="shown = true">
                <div class="order-2 lg:order-1 relative" x-show="shown" x-transition:enter="transition ease-out duration-700" x-transition:enter-start="opacity-0 -translate-x-8" x-transition:enter-end="opacity-100 translate-x-0">
                    <div class="bg-gradient-to-br from-orange-50 to-amber-50 rounded-3xl p-8 sm:p-10 border border-orange-100">
                        {{-- QR code card mockup --}}
                        <div class="bg-white rounded-2xl p-6 shadow-xl border-l-4 border-primary-500 flex gap-6">
                            <div class="flex-shrink-0">
                                <div class="w-28 h-28 bg-neutral-100 rounded-xl flex items-center justify-center border border-neutral-200">
                                    <svg class="w-20 h-20 text-neutral-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                                    </svg>
                                </div>
                                <p class="text-xs text-neutral-500 mt-2 text-center">Pour consulter le Menu</p>
                            </div>
                            <div class="flex flex-col justify-center text-center">
                                <div class="text-[10px] font-bold tracking-[3px] text-neutral-400 uppercase">T A B L E</div>
                                <div class="text-4xl font-black text-neutral-900 leading-none">N&deg;05</div>
                                <div class="text-lg font-black text-neutral-900 mt-1">SCANNEZ</div>
                                <div class="text-lg font-black text-primary-500">ICI</div>
                                <div class="w-12 h-0.5 bg-primary-500 mx-auto mt-1 rounded-full"></div>
                            </div>
                        </div>
                        {{-- Grid of small table badges --}}
                        <div class="mt-6 flex flex-wrap gap-2 justify-center">
                            @for($i = 1; $i <= 12; $i++)
                                <div class="w-10 h-10 bg-white rounded-lg border border-orange-200 flex items-center justify-center text-xs font-bold text-neutral-600 hover:bg-primary-500 hover:text-white hover:border-primary-500 transition-all cursor-default {{ $i === 5 ? 'bg-primary-500 text-white border-primary-500 ring-2 ring-primary-300' : '' }}">
                                    {{ $i }}
                                </div>
                            @endfor
                        </div>
                    </div>
                </div>
                <div class="order-1 lg:order-2" x-show="shown" x-transition:enter="transition ease-out duration-700 delay-200" x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-orange-100 text-orange-700 rounded-full text-xs font-bold uppercase tracking-wider mb-4">
                        <span class="w-2 h-2 bg-orange-500 rounded-full"></span>
                        Nouveau
                    </div>
                    <h3 class="font-display text-2xl sm:text-3xl lg:text-4xl font-bold text-neutral-900 leading-tight mb-4">
                        Un <span class="text-primary-500">QR code par table</span>, zero confusion
                    </h3>
                    <p class="text-neutral-600 text-lg leading-relaxed mb-6">
                        Generez un QR code unique pour chaque table de votre restaurant. Quand le client scanne, le numero de table est automatiquement detecte. Plus besoin de demander !
                    </p>
                    <ul class="space-y-3">
                        <li class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <span class="text-neutral-700 font-medium">PDF pret a imprimer et decoouper</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <span class="text-neutral-700 font-medium">Numero de table pre-rempli dans la commande</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-orange-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <span class="text-neutral-700 font-medium">Le serveur voit d'ou vient chaque commande</span>
                        </li>
                    </ul>
                </div>
            </div>

            <!-- Hero Feature 3 — WhatsApp Notifications -->
            <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center" x-data="{ shown: false }" x-intersect.once="shown = true">
                <div x-show="shown" x-transition:enter="transition ease-out duration-700" x-transition:enter-start="opacity-0 -translate-x-8" x-transition:enter-end="opacity-100 translate-x-0">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-green-100 text-green-700 rounded-full text-xs font-bold uppercase tracking-wider mb-4">
                        <span class="w-2 h-2 bg-green-500 rounded-full"></span>
                        Communication
                    </div>
                    <h3 class="font-display text-2xl sm:text-3xl lg:text-4xl font-bold text-neutral-900 leading-tight mb-4">
                        Notifications clients via <span class="text-green-600">WhatsApp</span>
                    </h3>
                    <p class="text-neutral-600 text-lg leading-relaxed mb-6">
                        Vos clients recoivent les mises a jour de leur commande directement sur WhatsApp. Pas d'email ignore, pas d'application a telecharger.
                    </p>
                    <ul class="space-y-3">
                        <li class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <span class="text-neutral-700 font-medium">Confirmation de commande automatique</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <span class="text-neutral-700 font-medium">"Votre commande est prete !" en temps reel</span>
                        </li>
                        <li class="flex items-center gap-3">
                            <div class="w-8 h-8 bg-green-100 rounded-lg flex items-center justify-center flex-shrink-0">
                                <svg class="w-4 h-4 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            </div>
                            <span class="text-neutral-700 font-medium">Alerte stock bas pour le restaurateur</span>
                        </li>
                    </ul>
                </div>
                <div class="relative" x-show="shown" x-transition:enter="transition ease-out duration-700 delay-200" x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0">
                    <div class="bg-gradient-to-br from-green-50 to-emerald-50 rounded-3xl p-8 sm:p-10 border border-green-100">
                        {{-- WhatsApp chat mockup --}}
                        <div class="space-y-3 max-w-sm mx-auto">
                            <div class="flex justify-start">
                                <div class="bg-white rounded-2xl rounded-tl-sm p-4 shadow-sm border border-green-100 max-w-[85%]">
                                    <p class="text-sm text-neutral-800 font-medium">Votre commande #1234 a ete confirmee !</p>
                                    <p class="text-xs text-neutral-500 mt-1">Poulet Braise x2, Attieke x1</p>
                                    <p class="text-[10px] text-neutral-400 mt-2 text-right">14:32</p>
                                </div>
                            </div>
                            <div class="flex justify-start">
                                <div class="bg-white rounded-2xl rounded-tl-sm p-4 shadow-sm border border-green-100 max-w-[85%]">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 bg-amber-100 rounded-full flex items-center justify-center flex-shrink-0">
                                            <svg class="w-3.5 h-3.5 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3"/></svg>
                                        </div>
                                        <p class="text-sm text-neutral-800 font-medium">En preparation... ~15 min</p>
                                    </div>
                                    <p class="text-[10px] text-neutral-400 mt-2 text-right">14:35</p>
                                </div>
                            </div>
                            <div class="flex justify-start">
                                <div class="bg-green-100 rounded-2xl rounded-tl-sm p-4 shadow-sm border border-green-200 max-w-[85%]">
                                    <div class="flex items-center gap-2">
                                        <div class="w-6 h-6 bg-green-500 rounded-full flex items-center justify-center flex-shrink-0">
                                            <svg class="w-3.5 h-3.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        </div>
                                        <p class="text-sm text-green-800 font-bold">Votre commande est prete !</p>
                                    </div>
                                    <p class="text-xs text-green-700 mt-1">Rendez-vous au comptoir. Bon appetit !</p>
                                    <p class="text-[10px] text-green-600 mt-2 text-right">14:48</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Comment ça marche Section -->
    <section id="how-it-works" class="py-20 sm:py-28 bg-gradient-to-b from-neutral-50 to-white relative overflow-hidden">
        <!-- Background decoration -->
        <div class="absolute top-0 left-0 w-[500px] h-[500px] bg-primary-100/40 rounded-full blur-[100px] -translate-x-1/2 -translate-y-1/2"></div>
        <div class="absolute bottom-0 right-0 w-[600px] h-[600px] bg-secondary-100/30 rounded-full blur-[120px] translate-x-1/2 translate-y-1/2"></div>
        <!-- Wiggly decorative lines (playful touch) -->
        <svg class="absolute top-20 right-10 w-32 h-32 text-primary-200/40 hidden lg:block" viewBox="0 0 100 100" fill="none">
            <path d="M10,50 Q25,20 50,50 T90,50" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-dasharray="4 6"/>
        </svg>
        <svg class="absolute bottom-24 left-10 w-40 h-20 text-secondary-200/40 hidden lg:block" viewBox="0 0 200 50" fill="none">
            <path d="M5,25 C40,5 80,45 120,25 S185,5 195,25" stroke="currentColor" stroke-width="2" stroke-linecap="round"/>
        </svg>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <!-- Section Header -->
            <div class="text-center max-w-3xl mx-auto mb-16 sm:mb-20" x-data="{ shown: false }" x-intersect.once="shown = true">
                <span class="inline-flex items-center gap-2 px-4 py-2 bg-gradient-to-r from-primary-100 to-orange-100 text-primary-700 rounded-full text-sm font-semibold mb-6 shadow-sm"
                      x-show="shown" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    ~15 minutes, montre en main
                </span>
                <h2 class="font-display text-3xl sm:text-4xl md:text-5xl font-bold text-neutral-900 leading-tight"
                    x-show="shown" x-transition:enter="transition ease-out duration-700 delay-100" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                    De zero au premier client,
                    <br class="hidden sm:block">
                    en <span class="relative inline-block">
                        <span class="text-gradient">3 etapes</span>
                        <svg class="absolute -bottom-2 left-0 w-full h-2 text-primary-400" viewBox="0 0 100 8" preserveAspectRatio="none">
                            <path d="M0,4 Q25,0 50,4 T100,4" stroke="currentColor" stroke-width="2" fill="none" stroke-linecap="round"/>
                        </svg>
                    </span>
                </h2>
                <p class="text-neutral-600 text-lg mt-6 max-w-2xl mx-auto"
                   x-show="shown" x-transition:enter="transition ease-out duration-700 delay-200" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                    Pas besoin d'etre developpeur. Juste votre restaurant, votre menu, et l'envie de recevoir des commandes.
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
                    ✓ Des 9 900 FCFA/mois &nbsp;•&nbsp; ✓ Satisfait ou remboursé 7 jours &nbsp;•&nbsp; ✓ Support WhatsApp
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
    <section id="features" class="py-24 bg-gradient-to-b from-white via-neutral-50/30 to-white relative overflow-hidden">
        <!-- Subtle background pattern -->
        <div class="absolute inset-0 bg-[linear-gradient(rgba(249,115,22,0.02)_1px,transparent_1px),linear-gradient(90deg,rgba(249,115,22,0.02)_1px,transparent_1px)] bg-[size:40px_40px]"></div>
        <!-- Floating accent orbs -->
        <div class="absolute top-20 -left-20 w-96 h-96 bg-primary-500/5 rounded-full blur-[120px] animate-float"></div>
        <div class="absolute bottom-20 -right-20 w-96 h-96 bg-accent-500/5 rounded-full blur-[120px] animate-float" style="animation-delay: 2s;"></div>
        
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

            <!-- Bento Grid Features -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 lg:gap-5 auto-rows-[220px]" x-data="{ shown: false }" x-intersect.once="shown = true">

                <!-- HERO CARD : Site mobile (span 2x2) -->
                <div class="md:col-span-2 lg:col-span-2 lg:row-span-2 group relative overflow-hidden rounded-3xl bg-gradient-to-br from-primary-500 via-primary-600 to-accent-600 p-8 lg:p-10 text-white shadow-xl hover:shadow-2xl transition-all duration-500"
                     x-show="shown" x-transition:enter="transition ease-out duration-700" x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0">
                    <div class="absolute -top-20 -right-20 w-72 h-72 bg-white/10 rounded-full blur-3xl group-hover:scale-110 transition-transform duration-700"></div>
                    <div class="absolute top-1/2 right-4 -translate-y-1/2 w-40 h-72 bg-white/10 backdrop-blur-xl rounded-[2rem] border border-white/30 shadow-2xl hidden lg:flex flex-col items-center justify-center p-3 rotate-6 group-hover:rotate-3 transition-transform duration-500">
                        <div class="w-full h-5 bg-white/20 rounded-lg mb-2"></div>
                        <div class="w-full flex-1 bg-white/30 rounded-2xl overflow-hidden relative">
                            <div class="absolute top-2 left-2 right-2 h-10 bg-white/40 rounded-lg"></div>
                            <div class="absolute top-14 left-2 right-2 h-16 bg-white rounded-lg flex items-center p-2 gap-2">
                                <div class="w-10 h-10 bg-primary-400 rounded-md"></div>
                                <div class="flex-1 space-y-1">
                                    <div class="w-full h-2 bg-neutral-300 rounded"></div>
                                    <div class="w-2/3 h-2 bg-neutral-200 rounded"></div>
                                </div>
                            </div>
                            <div class="absolute top-32 left-2 right-2 h-16 bg-white rounded-lg flex items-center p-2 gap-2">
                                <div class="w-10 h-10 bg-accent-400 rounded-md"></div>
                                <div class="flex-1 space-y-1">
                                    <div class="w-full h-2 bg-neutral-300 rounded"></div>
                                    <div class="w-1/2 h-2 bg-neutral-200 rounded"></div>
                                </div>
                            </div>
                            <div class="absolute bottom-2 left-2 right-2 h-8 bg-primary-500 rounded-lg flex items-center justify-center">
                                <div class="w-16 h-2 bg-white/80 rounded"></div>
                            </div>
                        </div>
                    </div>
                    <div class="relative max-w-[60%] h-full flex flex-col justify-between">
                        <div>
                            <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-white/20 backdrop-blur rounded-full text-xs font-semibold mb-6">
                                <span class="w-2 h-2 bg-green-300 rounded-full animate-pulse"></span>
                                Site de commande en ligne
                            </div>
                            <h3 class="font-display text-2xl lg:text-4xl font-bold leading-tight mb-4">
                                Un site mobile<br>prêt en 5 minutes
                            </h3>
                            <p class="text-white/90 text-sm lg:text-base leading-relaxed">
                                Design responsive, chargement ultra-rapide, PWA installable. Vos clients commandent en 3 clics depuis leur téléphone.
                            </p>
                        </div>
                        <div class="flex flex-wrap gap-2 mt-6">
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-white/15 backdrop-blur rounded-full text-xs font-medium">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                                Ultra-rapide
                            </span>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-white/15 backdrop-blur rounded-full text-xs font-medium">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 18h.01M8 21h8a2 2 0 002-2V5a2 2 0 00-2-2H8a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                Responsive
                            </span>
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-white/15 backdrop-blur rounded-full text-xs font-medium">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.858 15.355-5.858 21.213 0"/></svg>
                                PWA
                            </span>
                        </div>
                    </div>
                </div>

                <!-- WhatsApp Notifications (span 1x1) -->
                <div class="group relative overflow-hidden rounded-3xl bg-gradient-to-br from-green-500 to-emerald-600 p-6 text-white shadow-lg hover:shadow-xl transition-all duration-500"
                     x-show="shown" x-transition:enter="transition ease-out duration-700 delay-100" x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0">
                    <div class="absolute -bottom-8 -right-8 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
                    <div class="relative h-full flex flex-col justify-between">
                        <div class="w-12 h-12 bg-white/20 backdrop-blur rounded-2xl flex items-center justify-center group-hover:scale-110 group-hover:rotate-6 transition-transform duration-300">
                            <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold mb-1">WhatsApp auto</h3>
                            <p class="text-white/90 text-sm leading-snug">Confirmation, statut & alerte "prêt" envoyés automatiquement.</p>
                        </div>
                    </div>
                </div>

                <!-- Commandes temps réel (span 1x1) -->
                <div class="group relative overflow-hidden rounded-3xl bg-white border border-neutral-200 p-6 shadow-sm hover:shadow-xl hover:border-accent-300 transition-all duration-500"
                     x-show="shown" x-transition:enter="transition ease-out duration-700 delay-150" x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0">
                    <div class="h-full flex flex-col justify-between">
                        <div class="flex items-start justify-between">
                            <div class="w-12 h-12 bg-gradient-to-br from-accent-100 to-accent-200 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-6 h-6 text-accent-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                            </div>
                            <span class="flex items-center gap-1 text-xs font-semibold text-accent-600">
                                <span class="w-2 h-2 bg-accent-500 rounded-full animate-pulse"></span>
                                Live
                            </span>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-neutral-900 mb-1 group-hover:text-accent-600 transition-colors">Commandes live</h3>
                            <p class="text-neutral-600 text-sm leading-snug">Dashboard Kanban temps réel, mode Rush pour les coups de feu.</p>
                        </div>
                    </div>
                </div>

                <!-- Stats & Analytics (span 2x1 - wide) -->
                <div class="md:col-span-2 group relative overflow-hidden rounded-3xl bg-gradient-to-br from-neutral-900 via-neutral-800 to-neutral-900 p-6 lg:p-7 text-white shadow-lg hover:shadow-2xl transition-all duration-500"
                     x-show="shown" x-transition:enter="transition ease-out duration-700 delay-200" x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0">
                    <div class="absolute top-0 right-0 w-64 h-full opacity-20">
                        <div class="flex items-end justify-end h-full gap-1.5 p-6">
                            <div class="w-3 bg-gradient-to-t from-primary-500 to-accent-500 rounded-t" style="height: 30%"></div>
                            <div class="w-3 bg-gradient-to-t from-primary-500 to-accent-500 rounded-t" style="height: 55%"></div>
                            <div class="w-3 bg-gradient-to-t from-primary-500 to-accent-500 rounded-t" style="height: 45%"></div>
                            <div class="w-3 bg-gradient-to-t from-primary-500 to-accent-500 rounded-t" style="height: 70%"></div>
                            <div class="w-3 bg-gradient-to-t from-primary-500 to-accent-500 rounded-t" style="height: 60%"></div>
                            <div class="w-3 bg-gradient-to-t from-primary-500 to-accent-500 rounded-t" style="height: 85%"></div>
                            <div class="w-3 bg-gradient-to-t from-primary-500 to-accent-500 rounded-t" style="height: 95%"></div>
                            <div class="w-3 bg-gradient-to-t from-primary-500 to-accent-500 rounded-t" style="height: 75%"></div>
                        </div>
                    </div>
                    <div class="relative h-full flex flex-col justify-between max-w-sm">
                        <div class="w-12 h-12 bg-gradient-to-br from-primary-500 to-accent-500 rounded-2xl flex items-center justify-center shadow-lg group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                            </svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2 mb-2">
                                <span class="text-2xl lg:text-3xl font-bold bg-gradient-to-r from-primary-400 to-accent-400 bg-clip-text text-transparent">+32%</span>
                                <span class="text-xs text-neutral-400">de CA moyen / mois</span>
                            </div>
                            <h3 class="text-lg lg:text-xl font-bold mb-1">Analyse & Rapports</h3>
                            <p class="text-neutral-300 text-sm leading-snug">Ventes, plats populaires, CA journalier. Export Excel en un clic.</p>
                        </div>
                    </div>
                </div>

                <!-- Menu personnalisable (span 1x1) -->
                <div class="group relative overflow-hidden rounded-3xl bg-white border border-neutral-200 p-6 shadow-sm hover:shadow-xl hover:border-blue-300 transition-all duration-500"
                     x-show="shown" x-transition:enter="transition ease-out duration-700 delay-250" x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0">
                    <div class="h-full flex flex-col justify-between">
                        <div class="w-12 h-12 bg-gradient-to-br from-blue-100 to-blue-200 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-neutral-900 mb-1 group-hover:text-blue-600 transition-colors">Menu flexible</h3>
                            <p class="text-neutral-600 text-sm leading-snug">Catégories, photos, options, prix en FCFA. MAJ instantanée.</p>
                        </div>
                    </div>
                </div>

                <!-- Stock Management (span 1x1) -->
                <div class="group relative overflow-hidden rounded-3xl bg-gradient-to-br from-amber-50 to-orange-50 border border-amber-200 p-6 shadow-sm hover:shadow-xl transition-all duration-500"
                     x-show="shown" x-transition:enter="transition ease-out duration-700 delay-300" x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0">
                    <div class="h-full flex flex-col justify-between">
                        <div class="flex items-start justify-between">
                            <div class="w-12 h-12 bg-gradient-to-br from-amber-400 to-orange-500 rounded-2xl flex items-center justify-center shadow-md shadow-amber-500/30 group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                </svg>
                            </div>
                            <span class="px-2 py-0.5 bg-amber-500 text-white text-[10px] font-bold rounded-full uppercase tracking-wider">Pro</span>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-neutral-900 mb-1">Stock temps réel</h3>
                            <p class="text-neutral-700 text-sm leading-snug">Ingrédients, alertes bas, fournisseurs. Zéro rupture.</p>
                        </div>
                    </div>
                </div>

                <!-- Livraison & sur place (span 1x1) -->
                <div class="group relative overflow-hidden rounded-3xl bg-gradient-to-br from-rose-500 to-pink-600 p-6 text-white shadow-lg hover:shadow-xl transition-all duration-500"
                     x-show="shown" x-transition:enter="transition ease-out duration-700 delay-350" x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0">
                    <div class="absolute -top-6 -right-6 w-28 h-28 bg-white/10 rounded-full blur-2xl"></div>
                    <!-- Illustration BG : moto livreur -->
                    <svg class="absolute -bottom-4 -right-4 w-40 h-40 text-white/[0.08] group-hover:text-white/[0.15] group-hover:translate-x-[-8px] transition-all duration-500" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M19.5 13.5c-.83 0-1.5.67-1.5 1.5s.67 1.5 1.5 1.5 1.5-.67 1.5-1.5-.67-1.5-1.5-1.5zM4.5 13.5c-.83 0-1.5.67-1.5 1.5s.67 1.5 1.5 1.5S6 15.83 6 15s-.67-1.5-1.5-1.5z" opacity="0"/>
                        <path d="M19 7h-3V5.5C16 4.67 15.33 4 14.5 4h-5C8.67 4 8 4.67 8 5.5V7H5c-1.1 0-2 .9-2 2v7c0 1.1.9 2 2 2h1c0 1.66 1.34 3 3 3s3-1.34 3-3h0c0 1.66 1.34 3 3 3s3-1.34 3-3h1c1.1 0 2-.9 2-2v-5l-2-3zM9 20c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1zm6 0c-.55 0-1-.45-1-1s.45-1 1-1 1 .45 1 1-.45 1-1 1zm-5-8V9h8v3H10z"/>
                    </svg>
                    <svg class="absolute top-4 right-4 w-16 h-16 text-white/[0.06] group-hover:text-white/[0.12] transition-all duration-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <circle cx="12" cy="11" r="3"/>
                    </svg>
                    <div class="relative h-full flex flex-col justify-between">
                        <div class="w-12 h-12 bg-white/20 backdrop-blur rounded-2xl flex items-center justify-center group-hover:scale-110 group-hover:-rotate-6 transition-transform duration-300">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold mb-1">Livraison</h3>
                            <p class="text-white/90 text-sm leading-snug">Emporter, sur place & livraison. Zones et frais sur mesure.</p>
                        </div>
                    </div>
                </div>

                <!-- Multi-paiements (span 2x1 - wide featured) -->
                <div class="md:col-span-2 group relative overflow-hidden rounded-3xl bg-white border-2 border-primary-200 p-6 lg:p-7 shadow-sm hover:shadow-xl hover:border-primary-400 transition-all duration-500"
                     x-show="shown" x-transition:enter="transition ease-out duration-700 delay-400" x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0">
                    <div class="h-full flex flex-col justify-between">
                        <div class="flex items-start justify-between">
                            <div>
                                <span class="inline-flex items-center gap-2 px-3 py-1 bg-primary-100 text-primary-700 rounded-full text-xs font-bold mb-3">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path d="M10 2a8 8 0 100 16 8 8 0 000-16zm3.707 6.707l-4 4a1 1 0 01-1.414 0l-2-2a1 1 0 011.414-1.414L9 10.586l3.293-3.293a1 1 0 011.414 1.414z"/></svg>
                                    Paiements Côte d'Ivoire
                                </span>
                                <h3 class="text-xl lg:text-2xl font-bold text-neutral-900 mb-1">Mobile Money intégré</h3>
                                <p class="text-neutral-600 text-sm">Wave, Orange, MTN, Moov, CinetPay. Vos clients paient comme ils veulent.</p>
                            </div>
                        </div>
                        <div class="flex flex-wrap items-center gap-2 mt-4">
                            <div class="px-3 py-2 bg-blue-500 text-white rounded-xl text-xs font-bold shadow-sm">Wave</div>
                            <div class="px-3 py-2 bg-orange-500 text-white rounded-xl text-xs font-bold shadow-sm">Orange Money</div>
                            <div class="px-3 py-2 bg-yellow-400 text-neutral-900 rounded-xl text-xs font-bold shadow-sm">MTN Money</div>
                            <div class="px-3 py-2 bg-sky-500 text-white rounded-xl text-xs font-bold shadow-sm">Moov</div>
                            <div class="px-3 py-2 bg-neutral-900 text-white rounded-xl text-xs font-bold shadow-sm">CinetPay</div>
                            <div class="px-3 py-2 bg-white border-2 border-neutral-200 text-neutral-700 rounded-xl text-xs font-bold">+ Cash</div>
                        </div>
                    </div>
                </div>

                <!-- Réservations (span 1x1) -->
                <div class="group relative overflow-hidden rounded-3xl bg-white border border-neutral-200 p-6 shadow-sm hover:shadow-xl hover:border-indigo-300 transition-all duration-500"
                     x-show="shown" x-transition:enter="transition ease-out duration-700 delay-450" x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0">
                    <div class="h-full flex flex-col justify-between">
                        <div class="w-12 h-12 bg-gradient-to-br from-indigo-100 to-indigo-200 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                            <svg class="w-6 h-6 text-indigo-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-neutral-900 mb-1 group-hover:text-indigo-600 transition-colors">Réservations</h3>
                            <p class="text-neutral-600 text-sm leading-snug">Tables en ligne. Créneaux, rappels & confirmation auto.</p>
                        </div>
                    </div>
                </div>

                <!-- Équipe (span 1x1) -->
                <div class="group relative overflow-hidden rounded-3xl bg-gradient-to-br from-purple-500 to-fuchsia-600 p-6 text-white shadow-lg hover:shadow-xl transition-all duration-500"
                     x-show="shown" x-transition:enter="transition ease-out duration-700 delay-500" x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0">
                    <div class="absolute -bottom-8 -left-8 w-32 h-32 bg-white/10 rounded-full blur-2xl"></div>
                    <!-- Illustration BG : silhouettes d'equipe -->
                    <svg class="absolute -bottom-3 -right-3 w-36 h-36 text-white/[0.08] group-hover:text-white/[0.15] transition-all duration-500" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M16 11c1.66 0 2.99-1.34 2.99-3S17.66 5 16 5c-1.66 0-3 1.34-3 3s1.34 3 3 3zm-8 0c1.66 0 2.99-1.34 2.99-3S9.66 5 8 5C6.34 5 5 6.34 5 8s1.34 3 3 3zm0 2c-2.33 0-7 1.17-7 3.5V19h14v-2.5c0-2.33-4.67-3.5-7-3.5zm8 0c-.29 0-.62.02-.97.05 1.16.84 1.97 1.97 1.97 3.45V19h6v-2.5c0-2.33-4.67-3.5-7-3.5z"/>
                    </svg>
                    <svg class="absolute top-3 right-6 w-14 h-14 text-white/[0.06] group-hover:text-white/[0.12] group-hover:rotate-12 transition-all duration-500" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    <div class="relative h-full flex flex-col justify-between">
                        <div class="flex -space-x-2">
                            <div class="w-9 h-9 rounded-full bg-gradient-to-br from-orange-400 to-red-500 border-2 border-white flex items-center justify-center shadow-sm">
                                <svg class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="currentColor"><path d="M6 14c-2.2 0-4-1.8-4-4s1.8-4 4-4c.4 0 .8.1 1.2.2C7.9 4.9 9.8 4 12 4s4.1.9 4.8 2.2c.4-.1.8-.2 1.2-.2 2.2 0 4 1.8 4 4s-1.8 4-4 4v6c0 .6-.4 1-1 1H7c-.6 0-1-.4-1-1v-6z"/></svg>
                            </div>
                            <div class="w-9 h-9 rounded-full bg-gradient-to-br from-amber-400 to-orange-500 border-2 border-white flex items-center justify-center shadow-sm">
                                <svg class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2s-4 6-4 10a4 4 0 008 0c0-4-4-10-4-10zm0 16a2 2 0 01-2-2c0-1 1-3 2-5 1 2 2 4 2 5a2 2 0 01-2 2z"/></svg>
                            </div>
                            <div class="w-9 h-9 rounded-full bg-gradient-to-br from-emerald-400 to-teal-500 border-2 border-white flex items-center justify-center shadow-sm">
                                <svg class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="currentColor"><path d="M17 8C8 10 5.9 16.17 3.82 21.34l1.89.66.95-2.3c.48.17.98.3 1.34.3C19 20 22 3 22 3c-1 2-8 2.25-13 3.25S2 11.5 2 13.5s1.75 3.75 1.75 3.75C7 8 17 8 17 8z"/></svg>
                            </div>
                            <div class="w-9 h-9 rounded-full bg-white/20 backdrop-blur border-2 border-white flex items-center justify-center text-[11px] font-bold">+5</div>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold mb-1">Gestion equipe</h3>
                            <p class="text-white/90 text-sm leading-snug">Invitez employes, gerez les acces par role.</p>
                        </div>
                    </div>
                </div>

                <!-- Codes promo (span 1x1) -->
                <div class="group relative overflow-hidden rounded-3xl bg-gradient-to-br from-emerald-50 to-teal-50 border border-emerald-200 p-6 shadow-sm hover:shadow-xl transition-all duration-500"
                     x-show="shown" x-transition:enter="transition ease-out duration-700 delay-550" x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0">
                    <div class="h-full flex flex-col justify-between">
                        <div class="w-12 h-12 bg-gradient-to-br from-emerald-500 to-teal-600 rounded-2xl flex items-center justify-center shadow-md shadow-emerald-500/30 group-hover:scale-110 group-hover:rotate-12 transition-transform duration-300">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                            </svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-neutral-900 mb-1">Codes promo</h3>
                            <p class="text-neutral-700 text-sm leading-snug">Réductions ciblées pour fidéliser et booster.</p>
                        </div>
                    </div>
                </div>

                <!-- Mode hors ligne (span 1x1) -->
                <div class="group relative overflow-hidden rounded-3xl bg-white border border-neutral-200 p-6 shadow-sm hover:shadow-xl hover:border-cyan-300 transition-all duration-500"
                     x-show="shown" x-transition:enter="transition ease-out duration-700 delay-600" x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0">
                    <div class="h-full flex flex-col justify-between">
                        <div class="flex items-start justify-between">
                            <div class="w-12 h-12 bg-gradient-to-br from-cyan-100 to-cyan-200 rounded-2xl flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                                <svg class="w-6 h-6 text-cyan-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.111 16.404a5.5 5.5 0 017.778 0M12 20h.01m-7.08-7.071c3.904-3.905 10.236-3.905 14.141 0M1.394 9.393c5.857-5.858 15.355-5.858 21.213 0"/>
                                </svg>
                            </div>
                            <span class="text-[10px] font-bold text-cyan-600 uppercase tracking-wider">PWA</span>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold text-neutral-900 mb-1 group-hover:text-cyan-600 transition-colors">Hors ligne</h3>
                            <p class="text-neutral-600 text-sm leading-snug">Menu consultable sans internet. Zones instables couvertes.</p>
                        </div>
                    </div>
                </div>

                <!-- Avis clients (span 1x1) -->
                <div class="group relative overflow-hidden rounded-3xl bg-gradient-to-br from-yellow-400 to-orange-500 p-6 text-white shadow-lg hover:shadow-xl transition-all duration-500"
                     x-show="shown" x-transition:enter="transition ease-out duration-700 delay-650" x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0">
                    <div class="absolute -top-4 -right-4 w-24 h-24 bg-white/10 rounded-full blur-2xl"></div>
                    <!-- Illustration BG : grandes etoiles -->
                    <svg class="absolute -bottom-2 -right-2 w-32 h-32 text-white/[0.1] group-hover:text-white/[0.2] group-hover:rotate-12 transition-all duration-700" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                    </svg>
                    <svg class="absolute top-10 right-16 w-10 h-10 text-white/[0.08] group-hover:text-white/[0.18] transition-all duration-500" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                    </svg>
                    <svg class="absolute bottom-16 right-20 w-6 h-6 text-white/[0.1] group-hover:text-white/[0.2] transition-all duration-500" viewBox="0 0 24 24" fill="currentColor">
                        <path d="M12 17.27L18.18 21l-1.64-7.03L22 9.24l-7.19-.61L12 2 9.19 8.63 2 9.24l5.46 4.73L5.82 21z"/>
                    </svg>
                    <div class="relative h-full flex flex-col justify-between">
                        <div class="flex gap-0.5">
                            <svg class="w-5 h-5 text-white drop-shadow" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            <svg class="w-5 h-5 text-white drop-shadow" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            <svg class="w-5 h-5 text-white drop-shadow" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            <svg class="w-5 h-5 text-white drop-shadow" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                            <svg class="w-5 h-5 text-white drop-shadow" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-bold mb-1">Avis clients</h3>
                            <p class="text-white/90 text-sm leading-snug">Notes & commentaires post-commande. Réputation en hausse.</p>
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    <!-- Supports QR Code Physiques -->
    <section id="qr-supports" class="relative py-16 sm:py-20 lg:py-24 bg-gradient-to-b from-white via-orange-50/30 to-white overflow-hidden">
        <!-- Decorative dots pattern -->
        <div class="absolute inset-0 bg-[radial-gradient(circle,rgba(249,115,22,0.12)_1px,transparent_1px)] bg-[size:24px_24px] [mask-image:radial-gradient(ellipse_at_center,black_40%,transparent_80%)] opacity-50"></div>
        <div class="absolute top-10 right-10 w-64 h-64 bg-primary-200/40 rounded-full blur-3xl"></div>
        <div class="absolute bottom-10 left-10 w-72 h-72 bg-amber-200/40 rounded-full blur-3xl"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8" x-data="qrSupportsPricing()">

            {{-- Section header --}}
            <div class="max-w-3xl mx-auto text-center mb-10 sm:mb-14" x-data="{ shown: false }" x-intersect.once="shown = true">
                <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-gradient-to-r from-primary-100 to-orange-100 text-primary-700 rounded-full text-xs font-bold uppercase tracking-wider mb-5"
                     x-show="shown" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 scale-90" x-transition:enter-end="opacity-100 scale-100">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01"/></svg>
                    Nouveau &middot; Livre a votre restaurant
                </div>

                <h2 class="font-display text-3xl sm:text-4xl md:text-5xl font-bold text-neutral-900 leading-tight mb-5"
                    x-show="shown" x-transition:enter="transition ease-out duration-700 delay-100" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                    Vos QR codes,
                    <span class="relative inline-block">
                        <span class="text-gradient">imprimes et prets a servir.</span>
                        <svg class="absolute -bottom-2 left-0 w-full h-2.5 text-primary-400" viewBox="0 0 200 8" preserveAspectRatio="none">
                            <path d="M0,4 Q50,0 100,4 T200,4" stroke="currentColor" stroke-width="2.5" fill="none" stroke-linecap="round"/>
                        </svg>
                    </span>
                </h2>

                <p class="text-neutral-600 text-base sm:text-lg leading-relaxed"
                   x-show="shown" x-transition:enter="transition ease-out duration-700 delay-200" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                    On vous imprime des QR codes de qualite pour chaque table. Deux formats au choix : <strong class="text-neutral-900">support rigide</strong> pose sur la table, ou <strong class="text-neutral-900">autocollant</strong> colle directement.
                </p>
            </div>

            {{-- 2 Format cards (Support + Sticker) side by side --}}
            <div class="grid md:grid-cols-2 gap-5 sm:gap-6 mb-10 sm:mb-12">
                {{-- FORMAT 1 : Support rigide sur table --}}
                <button type="button"
                        @click="format = 'support'"
                        :class="format === 'support' ? 'border-primary-500 bg-white shadow-2xl shadow-primary-200/40 ring-2 ring-primary-100' : 'border-neutral-200 bg-white/60 hover:border-primary-300 hover:bg-white'"
                        class="relative text-left border-2 rounded-3xl p-6 sm:p-7 transition-all duration-300 focus:outline-none">

                    {{-- Visual on top --}}
                    <div class="flex items-start gap-4 mb-5">
                        <div class="relative flex-shrink-0">
                            {{-- 3D tent card illustration --}}
                            <div class="w-20 h-20 sm:w-24 sm:h-24 bg-gradient-to-br from-primary-500 via-orange-500 to-red-500 rounded-2xl flex items-center justify-center shadow-lg shadow-primary-500/30 transform rotate-[-4deg] group-hover:rotate-0 transition-transform">
                                <svg class="w-10 h-10 sm:w-12 sm:h-12 text-white" viewBox="0 0 48 48" fill="none">
                                    {{-- Tent triangular support --}}
                                    <path d="M8 40 L24 8 L40 40 Z" stroke="currentColor" stroke-width="2.5" fill="white" fill-opacity="0.15" stroke-linejoin="round"/>
                                    <rect x="14" y="18" width="20" height="14" rx="1.5" fill="white"/>
                                    {{-- Mini QR pattern --}}
                                    <rect x="16" y="20" width="3" height="3" fill="currentColor"/>
                                    <rect x="20" y="20" width="2" height="2" fill="currentColor"/>
                                    <rect x="29" y="20" width="3" height="3" fill="currentColor"/>
                                    <rect x="16" y="25" width="2" height="2" fill="currentColor"/>
                                    <rect x="19" y="24" width="3" height="2" fill="currentColor"/>
                                    <rect x="23" y="25" width="2" height="3" fill="currentColor"/>
                                    <rect x="27" y="24" width="2" height="3" fill="currentColor"/>
                                    <rect x="30" y="26" width="2" height="2" fill="currentColor"/>
                                    <rect x="16" y="29" width="3" height="3" fill="currentColor"/>
                                    <rect x="20" y="30" width="2" height="2" fill="currentColor"/>
                                    <rect x="26" y="29" width="2" height="2" fill="currentColor"/>
                                    <rect x="29" y="29" width="3" height="3" fill="currentColor"/>
                                </svg>
                            </div>
                            {{-- Selected check badge --}}
                            <div x-show="format === 'support'" x-transition class="absolute -top-2 -right-2 w-7 h-7 bg-emerald-500 rounded-full flex items-center justify-center ring-4 ring-white shadow-md">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </div>
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="text-[10px] font-bold uppercase tracking-wider text-primary-600 mb-1">Format 1 &middot; Le plus populaire</div>
                            <h3 class="font-display text-xl sm:text-2xl font-bold text-neutral-900 leading-tight mb-1">Support rigide sur table</h3>
                            <p class="text-sm text-neutral-600 leading-snug">Chevalet triangulaire, rigide, pose directement sur la table. Visible des deux cotes.</p>
                        </div>
                    </div>

                    {{-- Price --}}
                    <div class="flex items-baseline gap-2 mb-4 pb-4 border-b border-neutral-100">
                        <span class="text-4xl sm:text-5xl font-black bg-gradient-to-r from-primary-500 to-orange-500 bg-clip-text text-transparent">1 500</span>
                        <span class="text-sm font-semibold text-neutral-500">FCFA / unite</span>
                    </div>

                    {{-- Mini features --}}
                    <ul class="space-y-2">
                        <li class="flex items-start gap-2 text-sm text-neutral-700">
                            <svg class="w-4 h-4 text-emerald-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            <span>PVC rigide, anti-taches et lavable</span>
                        </li>
                        <li class="flex items-start gap-2 text-sm text-neutral-700">
                            <svg class="w-4 h-4 text-emerald-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            <span>Double face : client voit le QR des deux cotes</span>
                        </li>
                        <li class="flex items-start gap-2 text-sm text-neutral-700">
                            <svg class="w-4 h-4 text-emerald-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            <span>Se deplace facilement (changement de table)</span>
                        </li>
                    </ul>
                </button>

                {{-- FORMAT 2 : Autocollant --}}
                <button type="button"
                        @click="format = 'sticker'"
                        :class="format === 'sticker' ? 'border-primary-500 bg-white shadow-2xl shadow-primary-200/40 ring-2 ring-primary-100' : 'border-neutral-200 bg-white/60 hover:border-primary-300 hover:bg-white'"
                        class="relative text-left border-2 rounded-3xl p-6 sm:p-7 transition-all duration-300 focus:outline-none">

                    <div class="flex items-start gap-4 mb-5">
                        <div class="relative flex-shrink-0">
                            {{-- Sticker illustration with peel --}}
                            <div class="w-20 h-20 sm:w-24 sm:h-24 bg-gradient-to-br from-emerald-500 via-teal-500 to-cyan-500 rounded-2xl flex items-center justify-center shadow-lg shadow-emerald-500/30 transform rotate-[4deg] transition-transform relative overflow-hidden">
                                {{-- Peeled corner effect --}}
                                <div class="absolute top-0 right-0 w-6 h-6 bg-white/90 [clip-path:polygon(100%_0,100%_100%,0_0)] rounded-bl"></div>
                                <svg class="w-10 h-10 sm:w-12 sm:h-12 text-white relative" viewBox="0 0 48 48" fill="none">
                                    {{-- Flat sticker (rounded rect) --}}
                                    <rect x="10" y="10" width="28" height="28" rx="4" fill="white"/>
                                    {{-- Mini QR pattern --}}
                                    <rect x="13" y="13" width="4" height="4" fill="currentColor"/>
                                    <rect x="18" y="13" width="2" height="2" fill="currentColor"/>
                                    <rect x="22" y="14" width="3" height="2" fill="currentColor"/>
                                    <rect x="31" y="13" width="4" height="4" fill="currentColor"/>
                                    <rect x="13" y="19" width="2" height="3" fill="currentColor"/>
                                    <rect x="17" y="19" width="3" height="2" fill="currentColor"/>
                                    <rect x="22" y="19" width="2" height="4" fill="currentColor"/>
                                    <rect x="26" y="18" width="3" height="3" fill="currentColor"/>
                                    <rect x="31" y="19" width="2" height="3" fill="currentColor"/>
                                    <rect x="14" y="23" width="3" height="2" fill="currentColor"/>
                                    <rect x="19" y="24" width="2" height="3" fill="currentColor"/>
                                    <rect x="25" y="25" width="3" height="2" fill="currentColor"/>
                                    <rect x="30" y="24" width="4" height="2" fill="currentColor"/>
                                    <rect x="13" y="30" width="4" height="4" fill="currentColor"/>
                                    <rect x="19" y="30" width="2" height="3" fill="currentColor"/>
                                    <rect x="23" y="31" width="3" height="2" fill="currentColor"/>
                                    <rect x="28" y="30" width="2" height="4" fill="currentColor"/>
                                    <rect x="31" y="31" width="4" height="3" fill="currentColor"/>
                                </svg>
                            </div>
                            <div x-show="format === 'sticker'" x-transition class="absolute -top-2 -right-2 w-7 h-7 bg-emerald-500 rounded-full flex items-center justify-center ring-4 ring-white shadow-md">
                                <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </div>
                        </div>

                        <div class="flex-1 min-w-0">
                            <div class="text-[10px] font-bold uppercase tracking-wider text-emerald-600 mb-1">Format 2 &middot; Economique</div>
                            <h3 class="font-display text-xl sm:text-2xl font-bold text-neutral-900 leading-tight mb-1">Autocollant plastifie</h3>
                            <p class="text-sm text-neutral-600 leading-snug">Etiquette adhesive a coller directement sur la table, le menu ou la vitrine.</p>
                        </div>
                    </div>

                    <div class="flex items-baseline gap-2 mb-4 pb-4 border-b border-neutral-100">
                        <span class="text-4xl sm:text-5xl font-black bg-gradient-to-r from-emerald-500 to-teal-500 bg-clip-text text-transparent">300</span>
                        <span class="text-sm font-semibold text-neutral-500">FCFA / unite</span>
                    </div>

                    <ul class="space-y-2">
                        <li class="flex items-start gap-2 text-sm text-neutral-700">
                            <svg class="w-4 h-4 text-emerald-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            <span>Adhesif fort, se colle sans bulles</span>
                        </li>
                        <li class="flex items-start gap-2 text-sm text-neutral-700">
                            <svg class="w-4 h-4 text-emerald-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            <span>Vernis protecteur, resiste a l'eau</span>
                        </li>
                        <li class="flex items-start gap-2 text-sm text-neutral-700">
                            <svg class="w-4 h-4 text-emerald-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            <span>Ideal pour tables fixes, bar, vitrine</span>
                        </li>
                    </ul>
                </button>
            </div>

            {{-- Interactive quantity configurator --}}
            <div class="grid lg:grid-cols-5 gap-6 lg:gap-8 items-start">

                {{-- Left : Configurator (3/5) --}}
                <div class="lg:col-span-3 bg-white rounded-3xl border border-neutral-200 shadow-xl shadow-neutral-200/40 p-6 sm:p-8">
                    <div class="flex items-center gap-2 mb-6">
                        <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/></svg>
                        <h3 class="font-display text-lg sm:text-xl font-bold text-neutral-900">Configurez votre commande</h3>
                    </div>

                    {{-- Quantity input --}}
                    <div class="mb-6">
                        <label class="block text-xs font-bold text-neutral-500 uppercase tracking-wider mb-3">Nombre de tables / emplacements</label>
                        <div class="flex items-stretch gap-3">
                            <button type="button" @click="quantity = Math.max(1, quantity - 1)" class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl bg-neutral-100 hover:bg-neutral-200 flex items-center justify-center text-neutral-700 transition-colors focus:outline-none focus:ring-2 focus:ring-primary-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M20 12H4"/></svg>
                            </button>
                            <div class="flex-1 relative">
                                <input type="number" min="1" max="999" x-model.number="quantity"
                                       class="w-full h-12 sm:h-14 text-center text-2xl sm:text-3xl font-black text-neutral-900 bg-neutral-50 border-2 border-neutral-200 rounded-xl focus:border-primary-400 focus:outline-none focus:ring-4 focus:ring-primary-100 transition-all">
                                <span class="absolute right-4 top-1/2 -translate-y-1/2 text-xs font-medium text-neutral-400 uppercase tracking-wide pointer-events-none hidden sm:block">unites</span>
                            </div>
                            <button type="button" @click="quantity = Math.min(999, quantity + 1)" class="w-12 h-12 sm:w-14 sm:h-14 rounded-xl bg-primary-100 hover:bg-primary-200 flex items-center justify-center text-primary-600 transition-colors focus:outline-none focus:ring-2 focus:ring-primary-200">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                            </button>
                        </div>
                    </div>

                    {{-- Quick quantity presets --}}
                    <div>
                        <label class="block text-xs font-bold text-neutral-500 uppercase tracking-wider mb-3">Packs populaires</label>
                        <div class="grid grid-cols-4 gap-2 sm:gap-3">
                            <template x-for="preset in [10, 20, 50, 100]" :key="preset">
                                <button type="button"
                                        @click="quantity = preset"
                                        :class="quantity === preset ? 'bg-primary-500 text-white border-primary-500 shadow-md shadow-primary-500/25' : 'bg-neutral-50 text-neutral-700 border-neutral-200 hover:border-primary-300 hover:bg-white'"
                                        class="relative py-3 sm:py-4 px-2 border-2 rounded-xl font-bold transition-all focus:outline-none">
                                    <div class="text-lg sm:text-xl" x-text="preset"></div>
                                    <div class="text-[10px] font-medium opacity-80">tables</div>
                                </button>
                            </template>
                        </div>
                    </div>
                </div>

                {{-- Right : Live total price card (2/5) --}}
                <div class="lg:col-span-2">
                    <div class="relative bg-gradient-to-br from-neutral-900 via-neutral-800 to-neutral-900 text-white rounded-3xl p-6 sm:p-8 shadow-2xl overflow-hidden">
                        {{-- Decorative elements --}}
                        <div class="absolute top-0 right-0 w-32 h-32 bg-primary-500/20 rounded-full blur-3xl"></div>
                        <div class="absolute bottom-0 left-0 w-32 h-32 bg-orange-500/10 rounded-full blur-2xl"></div>

                        <div class="relative">
                            <div class="text-[10px] font-bold uppercase tracking-[2px] text-primary-300 mb-2">Votre commande</div>

                            <div class="flex items-baseline gap-2 mb-1">
                                <span class="text-3xl sm:text-4xl font-black" x-text="quantity"></span>
                                <span class="text-sm text-neutral-400">×</span>
                                <span class="text-lg font-bold" x-text="format === 'support' ? '1 500 F' : '300 F'"></span>
                            </div>
                            <div class="text-xs text-neutral-400 mb-6" x-text="format === 'support' ? 'Supports rigides sur table' : 'Autocollants plastifies'"></div>

                            <div class="border-t border-white/10 pt-5">
                                <div class="text-xs uppercase tracking-wider text-neutral-400 mb-1">Total a payer</div>
                                <div class="flex items-baseline gap-2">
                                    <span class="text-4xl sm:text-5xl font-black bg-gradient-to-r from-primary-300 to-orange-300 bg-clip-text text-transparent" x-text="formatPrice(total)"></span>
                                    <span class="text-sm font-medium text-neutral-300">FCFA</span>
                                </div>
                                <div class="text-xs text-neutral-400 mt-1" x-show="quantity >= 20">
                                    <span class="inline-flex items-center gap-1 text-emerald-300 font-medium">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                        Livraison offerte a Abidjan
                                    </span>
                                </div>
                                <div class="text-xs text-neutral-400 mt-1" x-show="quantity < 20">
                                    Livraison : 2 000 FCFA (Abidjan) &middot; Offerte des 20 unites
                                </div>
                            </div>

                            @php
                                $qrWhatsapp = \App\Models\SystemSetting::get('contact_whatsapp', \App\Models\SystemSetting::get('contact_phone', ''));
                            @endphp

                            {{-- Primary CTA : always visible - opens inline order form --}}
                            <button type="button"
                                    @click="showOrderForm = true; $nextTick(() => document.getElementById('qr-order-form')?.scrollIntoView({behavior: 'smooth', block: 'center'}))"
                                    x-show="!showOrderForm"
                                    class="mt-6 w-full inline-flex items-center justify-center gap-2 py-3.5 px-5 bg-gradient-to-r from-primary-500 to-orange-500 hover:from-primary-600 hover:to-orange-600 rounded-xl font-bold text-sm shadow-lg shadow-primary-500/30 transition-all hover:scale-[1.02] active:scale-100 focus:outline-none focus:ring-4 focus:ring-primary-400/40">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                                Passer commande maintenant
                            </button>

                            @if($qrWhatsapp)
                            <a :href="'https://wa.me/{{ preg_replace('/[^0-9]/', '', $qrWhatsapp) }}?text=' + encodeURIComponent('Bonjour MenuPro, je souhaite commander ' + quantity + ' ' + (format === 'support' ? 'supports rigides QR code' : 'autocollants QR code') + ' (' + formatPrice(total) + ' FCFA). Merci de me contacter.')"
                               target="_blank"
                               x-show="!showOrderForm"
                               class="mt-3 w-full inline-flex items-center justify-center gap-2 py-3 px-5 bg-white/5 hover:bg-white/10 border border-white/15 hover:border-emerald-400/50 rounded-xl font-semibold text-xs text-white transition-all">
                                <svg class="w-4 h-4 text-emerald-400" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/></svg>
                                Ou via WhatsApp
                            </a>
                            @endif

                            <div class="mt-3 flex items-center justify-center gap-4 text-[11px] text-neutral-400" x-show="!showOrderForm">
                                <span class="inline-flex items-center gap-1"><svg class="w-3 h-3 text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>Paiement a la livraison</span>
                                <span class="inline-flex items-center gap-1"><svg class="w-3 h-3 text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>5-7 jours ouvres</span>
                            </div>
                        </div>
                    </div>
                </div>

            </div>

            {{-- INLINE ORDER FORM --}}
            <div id="qr-order-form" class="mt-8 sm:mt-10" x-show="showOrderForm" x-cloak
                 x-transition:enter="transition ease-out duration-400"
                 x-transition:enter-start="opacity-0 translate-y-4"
                 x-transition:enter-end="opacity-100 translate-y-0">

                @if(session('qr_success'))
                    <div class="mb-6 p-5 bg-emerald-50 border-2 border-emerald-200 rounded-2xl flex items-start gap-3">
                        <svg class="w-6 h-6 text-emerald-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        <div>
                            <div class="font-bold text-emerald-900 mb-1">Commande envoyee !</div>
                            <div class="text-sm text-emerald-800">{{ session('qr_success') }}</div>
                        </div>
                    </div>
                @endif

                @if(session('qr_error'))
                    <div class="mb-6 p-5 bg-red-50 border-2 border-red-200 rounded-2xl flex items-start gap-3">
                        <svg class="w-6 h-6 text-red-600 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                        <div>
                            <div class="font-bold text-red-900 mb-1">Erreur</div>
                            <div class="text-sm text-red-800">{{ session('qr_error') }}</div>
                        </div>
                    </div>
                @endif

                @if($errors->any())
                    <div class="mb-6 p-5 bg-red-50 border-2 border-red-200 rounded-2xl">
                        <div class="font-bold text-red-900 mb-2">Veuillez corriger les erreurs suivantes :</div>
                        <ul class="list-disc list-inside text-sm text-red-800 space-y-1">
                            @foreach($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <div class="bg-white border-2 border-neutral-200 rounded-3xl shadow-xl p-6 sm:p-10">
                    <div class="flex items-start justify-between gap-4 mb-6 pb-6 border-b border-neutral-100">
                        <div>
                            <div class="text-xs font-bold uppercase tracking-[2px] text-primary-600 mb-2">Etape finale</div>
                            <h3 class="font-display text-2xl sm:text-3xl font-bold text-neutral-900">Vos informations de livraison</h3>
                            <p class="text-sm text-neutral-600 mt-2">Notre equipe vous rappelle sous 24h pour confirmer et organiser la livraison. Aucun paiement en ligne : vous reglez a la reception.</p>
                        </div>
                        <button type="button" @click="showOrderForm = false"
                                class="flex-shrink-0 w-10 h-10 rounded-full bg-neutral-100 hover:bg-neutral-200 flex items-center justify-center text-neutral-600 transition-colors"
                                aria-label="Fermer">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    </div>

                    {{-- Order summary --}}
                    <div class="mb-6 p-4 bg-gradient-to-br from-primary-50 to-orange-50 border border-primary-200/50 rounded-2xl flex items-center gap-4">
                        <div class="flex-shrink-0 w-12 h-12 rounded-xl bg-white shadow-sm flex items-center justify-center">
                            <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="text-xs font-bold text-primary-700 uppercase tracking-wide mb-1">Recapitulatif</div>
                            <div class="text-sm text-neutral-900">
                                <span class="font-bold" x-text="quantity"></span>
                                <span x-text="format === 'support' ? ' supports rigides' : ' autocollants'"></span>
                                &middot;
                                <span class="font-bold text-primary-700" x-text="formatPrice(total) + ' FCFA'"></span>
                                <span class="text-neutral-500" x-show="quantity >= 20">+ livraison offerte</span>
                                <span class="text-neutral-500" x-show="quantity < 20">+ 2 000 F livraison</span>
                            </div>
                        </div>
                        <button type="button" @click="showOrderForm = false"
                                class="hidden sm:inline-flex flex-shrink-0 text-xs font-semibold text-primary-600 hover:text-primary-700 underline">
                            Modifier
                        </button>
                    </div>

                    <form action="{{ route('qr-supports.order') }}" method="POST" class="space-y-5" @submit="submitting = true">
                        @csrf
                        <input type="hidden" name="format" :value="format">
                        <input type="hidden" name="quantity" :value="quantity">

                        <div class="grid sm:grid-cols-2 gap-4 sm:gap-5">
                            <div>
                                <label for="qr-name" class="block text-sm font-semibold text-neutral-800 mb-2">Nom complet <span class="text-red-500">*</span></label>
                                <input id="qr-name" name="name" type="text" required maxlength="100"
                                       value="{{ old('name') }}"
                                       placeholder="Ex: Kouame Yao"
                                       class="w-full px-4 py-3 bg-white border-2 border-neutral-200 rounded-xl text-neutral-900 placeholder-neutral-400 focus:border-primary-400 focus:outline-none focus:ring-4 focus:ring-primary-100 transition-all">
                            </div>
                            <div>
                                <label for="qr-phone" class="block text-sm font-semibold text-neutral-800 mb-2">Telephone <span class="text-red-500">*</span></label>
                                <input id="qr-phone" name="phone" type="tel" required maxlength="30"
                                       value="{{ old('phone') }}"
                                       placeholder="+225 07 00 00 00 00"
                                       class="w-full px-4 py-3 bg-white border-2 border-neutral-200 rounded-xl text-neutral-900 placeholder-neutral-400 focus:border-primary-400 focus:outline-none focus:ring-4 focus:ring-primary-100 transition-all">
                            </div>
                        </div>

                        <div class="grid sm:grid-cols-2 gap-4 sm:gap-5">
                            <div>
                                <label for="qr-email" class="block text-sm font-semibold text-neutral-800 mb-2">Email <span class="text-neutral-400 font-normal">(optionnel)</span></label>
                                <input id="qr-email" name="email" type="email" maxlength="255"
                                       value="{{ old('email') }}"
                                       placeholder="votre@email.com"
                                       class="w-full px-4 py-3 bg-white border-2 border-neutral-200 rounded-xl text-neutral-900 placeholder-neutral-400 focus:border-primary-400 focus:outline-none focus:ring-4 focus:ring-primary-100 transition-all">
                            </div>
                            <div>
                                <label for="qr-city" class="block text-sm font-semibold text-neutral-800 mb-2">Ville <span class="text-red-500">*</span></label>
                                <input id="qr-city" name="city" type="text" required maxlength="100"
                                       value="{{ old('city', 'Abidjan') }}"
                                       placeholder="Ex: Abidjan"
                                       class="w-full px-4 py-3 bg-white border-2 border-neutral-200 rounded-xl text-neutral-900 placeholder-neutral-400 focus:border-primary-400 focus:outline-none focus:ring-4 focus:ring-primary-100 transition-all">
                            </div>
                        </div>

                        <div>
                            <label for="qr-address" class="block text-sm font-semibold text-neutral-800 mb-2">Adresse de livraison <span class="text-neutral-400 font-normal">(commune / quartier / point de repere)</span></label>
                            <input id="qr-address" name="address" type="text" maxlength="500"
                                   value="{{ old('address') }}"
                                   placeholder="Ex: Cocody, Riviera 2, pres de la pharmacie..."
                                   class="w-full px-4 py-3 bg-white border-2 border-neutral-200 rounded-xl text-neutral-900 placeholder-neutral-400 focus:border-primary-400 focus:outline-none focus:ring-4 focus:ring-primary-100 transition-all">
                        </div>

                        <div>
                            <label for="qr-note" class="block text-sm font-semibold text-neutral-800 mb-2">Message / precisions <span class="text-neutral-400 font-normal">(optionnel)</span></label>
                            <textarea id="qr-note" name="note" rows="3" maxlength="1000"
                                      placeholder="Nom de votre restaurant, instructions particulieres..."
                                      class="w-full px-4 py-3 bg-white border-2 border-neutral-200 rounded-xl text-neutral-900 placeholder-neutral-400 focus:border-primary-400 focus:outline-none focus:ring-4 focus:ring-primary-100 transition-all resize-none">{{ old('note') }}</textarea>
                        </div>

                        <div class="pt-2 flex flex-col sm:flex-row gap-3">
                            <button type="submit" :disabled="submitting"
                                    class="flex-1 inline-flex items-center justify-center gap-2 py-4 px-6 bg-gradient-to-r from-primary-500 to-orange-500 hover:from-primary-600 hover:to-orange-600 disabled:opacity-70 disabled:cursor-not-allowed rounded-xl font-bold text-white shadow-lg shadow-primary-500/25 transition-all hover:scale-[1.01] active:scale-100 focus:outline-none focus:ring-4 focus:ring-primary-400/40">
                                <svg x-show="!submitting" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                <svg x-show="submitting" class="w-5 h-5 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>
                                <span x-text="submitting ? 'Envoi en cours...' : 'Confirmer ma commande'"></span>
                            </button>
                            <button type="button" @click="showOrderForm = false"
                                    class="sm:w-auto px-6 py-4 bg-neutral-100 hover:bg-neutral-200 text-neutral-700 rounded-xl font-semibold transition-colors">
                                Annuler
                            </button>
                        </div>

                        <div class="pt-2 flex items-center justify-center gap-5 text-[11px] text-neutral-500">
                            <span class="inline-flex items-center gap-1"><svg class="w-3.5 h-3.5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>Aucun paiement en ligne</span>
                            <span class="inline-flex items-center gap-1"><svg class="w-3.5 h-3.5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>Rappel sous 24h</span>
                            <span class="hidden sm:inline-flex items-center gap-1"><svg class="w-3.5 h-3.5 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>Sans engagement</span>
                        </div>
                    </form>
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
                        ['name' => 'Kouame Yao', 'initials' => 'KY', 'gradient' => 'from-orange-400 to-red-500', 'role' => 'Gerant, Le Maquis du Port', 'location' => 'Abidjan', 'rating' => 5, 'text' => "Depuis que j'utilise MenuPro, mes commandes ont augmente de 40%. Le paiement Mobile Money est un vrai plus pour mes clients.", 'highlight' => '+40% commandes', 'type' => 'Maquis'],
                        ['name' => 'Awa Diallo', 'initials' => 'AD', 'gradient' => 'from-pink-400 to-rose-500', 'role' => 'Proprietaire, Chez Awa', 'location' => 'Cocody', 'rating' => 5, 'text' => "Interface simple et intuitive. J'ai pu creer mon menu en moins d'une heure. Le support WhatsApp est tres reactif !", 'highlight' => 'Menu en 1h', 'type' => 'Restaurant'],
                        ['name' => 'Ibrahim Kone', 'initials' => 'IK', 'gradient' => 'from-emerald-400 to-teal-500', 'role' => 'Directeur, Restaurant Le Sahel', 'location' => 'Marcory', 'rating' => 5, 'text' => "Mes employes gerent les commandes facilement. Les statistiques m'aident a mieux comprendre mes ventes.", 'highlight' => 'Gestion facile', 'type' => 'Restaurant'],
                    ];
                @endphp

                @foreach($testimonials as $index => $testimonial)
                    <div x-show="shown"
                         x-transition:enter="transition ease-out duration-700"
                         x-transition:enter-start="opacity-0 translate-y-8"
                         x-transition:enter-end="opacity-100 translate-y-0"
                         style="transition-delay: {{ $index * 150 }}ms"
                         class="group">
                        <div class="relative bg-white rounded-3xl p-7 lg:p-8 shadow-lg shadow-neutral-200/50 border-2 border-neutral-100 h-full flex flex-col hover:border-primary-200 hover:shadow-2xl hover:shadow-primary-100/30 hover:-translate-y-1.5 transition-all duration-500 overflow-hidden">
                            <!-- Big quote mark SVG (more refined than char) -->
                            <svg class="absolute -top-3 -right-3 w-20 h-20 text-primary-100 group-hover:text-primary-200 transition-colors duration-500" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M6.5 10c-.223 0-.437.034-.65.065.069-.232.14-.468.254-.68.114-.308.292-.575.469-.844.148-.291.409-.488.601-.737.201-.242.475-.403.692-.604.213-.21.492-.315.714-.463.232-.133.434-.28.65-.35l.539-.222.474-.197-.485-1.938-.597.144c-.191.048-.424.104-.689.171-.271.05-.56.187-.882.312-.318.142-.686.238-1.028.466-.344.218-.741.4-1.091.692-.339.301-.748.562-1.05.945-.33.358-.656.734-.909 1.162-.293.408-.492.856-.702 1.299-.19.443-.343.896-.468 1.336-.237.882-.343 1.72-.384 2.437-.034.718-.014 1.315.028 1.747.015.204.043.402.063.539.017.109.025.168.025.168l.026-.006C6.213 16.346 7.215 17 8.5 17c1.933 0 3.5-1.5 3.5-3.5S10.433 10 8.5 10h-2zm10 0c-.223 0-.437.034-.65.065.069-.232.14-.468.254-.68.114-.308.292-.575.469-.844.148-.291.409-.488.601-.737.201-.242.475-.403.692-.604.213-.21.492-.315.714-.463.232-.133.434-.28.65-.35l.539-.222.474-.197-.485-1.938-.597.144c-.191.048-.424.104-.689.171-.271.05-.56.187-.882.312-.317.143-.686.238-1.028.467-.344.218-.741.4-1.091.692-.339.301-.748.562-1.05.944-.33.358-.656.734-.909 1.162-.293.408-.492.856-.702 1.299-.19.443-.343.896-.468 1.336-.237.882-.343 1.72-.384 2.437-.034.718-.014 1.315.028 1.747.015.204.043.402.063.539.017.109.025.168.025.168l.026-.006C16.213 16.346 17.215 17 18.5 17c1.933 0 3.5-1.5 3.5-3.5S20.433 10 18.5 10h-2z"/>
                            </svg>

                            <!-- Top: Stars + Highlight badge -->
                            <div class="flex items-center justify-between mb-5 relative z-10">
                                <div class="flex gap-0.5">
                                    @for($i = 0; $i < $testimonial['rating']; $i++)
                                        <svg class="w-5 h-5 text-yellow-400 drop-shadow-sm" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    @endfor
                                </div>
                                <span class="inline-flex items-center gap-1 text-xs font-bold text-primary-700 bg-gradient-to-r from-primary-50 to-orange-50 px-2.5 py-1 rounded-full border border-primary-100">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                                    {{ $testimonial['highlight'] }}
                                </span>
                            </div>

                            <!-- Quote -->
                            <blockquote class="text-neutral-700 leading-relaxed flex-1 text-[15px] relative z-10">
                                &laquo;&nbsp;{{ $testimonial['text'] }}&nbsp;&raquo;
                            </blockquote>

                            <!-- Author -->
                            <div class="flex items-center gap-4 mt-6 pt-6 border-t border-neutral-100 relative z-10">
                                <div class="relative flex-shrink-0">
                                    <div class="w-14 h-14 bg-gradient-to-br {{ $testimonial['gradient'] }} rounded-2xl flex items-center justify-center text-white font-bold text-lg shadow-lg group-hover:scale-110 group-hover:-rotate-3 transition-transform duration-300">
                                        {{ $testimonial['initials'] }}
                                    </div>
                                    <!-- Verified checkmark -->
                                    <div class="absolute -bottom-1 -right-1 w-5 h-5 bg-white rounded-full flex items-center justify-center shadow-md ring-2 ring-white">
                                        <svg class="w-3 h-3 text-emerald-500" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                </div>
                                <div class="min-w-0 flex-1">
                                    <div class="flex items-center gap-2">
                                        <div class="font-bold text-neutral-900 truncate">{{ $testimonial['name'] }}</div>
                                        <span class="text-[10px] font-bold text-neutral-500 bg-neutral-100 px-1.5 py-0.5 rounded uppercase tracking-wider flex-shrink-0">{{ $testimonial['type'] }}</span>
                                    </div>
                                    <div class="text-sm text-neutral-500 truncate">{{ $testimonial['role'] }}</div>
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
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-orange-400 to-red-500 ring-2 ring-white flex items-center justify-center shadow-sm">
                            <svg class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="currentColor"><path d="M6 14c-2.2 0-4-1.8-4-4s1.8-4 4-4c.4 0 .8.1 1.2.2C7.9 4.9 9.8 4 12 4s4.1.9 4.8 2.2c.4-.1.8-.2 1.2-.2 2.2 0 4 1.8 4 4s-1.8 4-4 4v6c0 .6-.4 1-1 1H7c-.6 0-1-.4-1-1v-6z"/></svg>
                        </div>
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-amber-400 to-orange-500 ring-2 ring-white flex items-center justify-center shadow-sm">
                            <svg class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="currentColor"><path d="M12 2s-4 6-4 10a4 4 0 008 0c0-4-4-10-4-10zm0 16a2 2 0 01-2-2c0-1 1-3 2-5 1 2 2 4 2 5a2 2 0 01-2 2z"/></svg>
                        </div>
                        <div class="w-8 h-8 rounded-full bg-gradient-to-br from-emerald-400 to-teal-500 ring-2 ring-white flex items-center justify-center shadow-sm">
                            <svg class="w-4 h-4 text-white" viewBox="0 0 24 24" fill="currentColor"><path d="M17 8C8 10 5.9 16.17 3.82 21.34l1.89.66.95-2.3c.48.17.98.3 1.34.3C19 20 22 3 22 3c-1 2-8 2.25-13 3.25S2 11.5 2 13.5s1.75 3.75 1.75 3.75C7 8 17 8 17 8z"/></svg>
                        </div>
                        <div class="w-8 h-8 bg-primary-500 rounded-full ring-2 ring-white flex items-center justify-center text-white text-[10px] font-bold">+{{ ($stats['raw']['restaurants'] ?? 10) > 10 ? $stats['raw']['restaurants'] - 3 : '7' }}</div>
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
            <!-- Header -->
            <div class="text-center max-w-3xl mx-auto mb-14" x-data="{ shown: false }" x-intersect.once="shown = true">
                <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-primary-500/10 border border-primary-500/20 rounded-full text-primary-400 text-xs font-semibold mb-5 uppercase tracking-wider"
                     x-show="shown" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    Tarifs transparents
                </div>
                <h2 class="font-display text-4xl md:text-5xl font-bold text-white mt-2"
                    x-show="shown" x-transition:enter="transition ease-out duration-700 delay-100" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                    Deux plans adaptés à <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary-400 to-accent-400">votre activité</span>
                </h2>
                <p class="text-neutral-400 text-lg mt-4"
                   x-show="shown" x-transition:enter="transition ease-out duration-700 delay-200" x-transition:enter-start="opacity-0 translate-y-4" x-transition:enter-end="opacity-100 translate-y-0">
                    Du petit maquis au restaurant établi. Sans engagement, changez quand vous voulez.
                </p>
            </div>

            <!-- 2-Plans Grid -->
            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6 lg:gap-8 max-w-5xl mx-auto">

                <!-- STARTER PLAN -->
                <div class="relative group" x-data="{ shown: false }" x-intersect.once="shown = true"
                     x-show="shown" x-transition:enter="transition ease-out duration-700" x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0">
                    <div class="h-full bg-neutral-900/80 backdrop-blur-xl rounded-3xl p-6 sm:p-8 border border-neutral-800 hover:border-neutral-700 transition-all">
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-neutral-800 rounded-full text-xs font-semibold text-neutral-300 mb-4">
                            <svg class="w-3.5 h-3.5 text-accent-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                            Idéal petits maquis
                        </div>

                        <h3 class="text-2xl font-bold text-white mb-1">Starter</h3>
                        <p class="text-sm text-neutral-500 mb-6">Pour démarrer la digitalisation de votre maquis.</p>

                        <div class="mb-6 pb-6 border-b border-neutral-800">
                            <div class="flex items-baseline gap-2">
                                <span class="text-5xl font-bold text-white">9 900</span>
                                <span class="text-neutral-500 text-sm">FCFA</span>
                            </div>
                            <div class="text-xs text-neutral-500 mt-2">par mois · sans engagement</div>
                        </div>

                        <a href="{{ route('register') }}?plan=starter"
                           class="block w-full text-center py-3 px-6 rounded-xl font-bold text-sm border-2 border-neutral-700 text-white hover:bg-neutral-800 hover:border-neutral-600 transition-all mb-6">
                            Choisir Starter
                        </a>

                        <p class="text-xs font-bold text-neutral-500 uppercase tracking-wider mb-3">Inclus</p>
                        <ul class="space-y-2.5">
                            @foreach([
                                'Jusqu\'à 30 plats',
                                '1 compte employé',
                                '300 commandes/mois',
                                'Menu public + QR codes',
                                'Paiement Mobile Money',
                                'Support WhatsApp',
                            ] as $f)
                                <li class="flex items-start gap-2.5 text-sm text-neutral-300">
                                    <svg class="w-4 h-4 text-primary-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                                    <span>{{ $f }}</span>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                </div>

                <!-- MENUPRO PLAN (Featured) -->
                <div class="relative group" x-data="{ shown: false }" x-intersect.once="shown = true"
                     x-show="shown" x-transition:enter="transition ease-out duration-700 delay-200" x-transition:enter-start="opacity-0 translate-y-8" x-transition:enter-end="opacity-100 translate-y-0">
                    <!-- Featured badge -->
                    <div class="absolute -top-4 left-1/2 -translate-x-1/2 z-10">
                        <div class="bg-gradient-to-r from-primary-500 to-primary-600 text-white px-5 py-1.5 rounded-full text-xs font-bold shadow-lg shadow-primary-500/30 flex items-center gap-1.5 whitespace-nowrap">
                            <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                            LE PLUS POPULAIRE
                        </div>
                    </div>

                    <div class="absolute -inset-1 bg-gradient-to-r from-primary-500 to-accent-500 rounded-3xl opacity-20 blur-xl group-hover:opacity-30 transition-opacity"></div>

                    <div class="relative h-full bg-gradient-to-b from-neutral-900 to-neutral-950 rounded-3xl p-6 sm:p-8 border-2 border-primary-500/50 shadow-2xl shadow-primary-500/10">
                        <div class="inline-flex items-center gap-1.5 px-3 py-1 bg-primary-500/10 border border-primary-500/20 rounded-full text-xs font-semibold text-primary-400 mb-4">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                            Tout inclus
                        </div>

                        <h3 class="text-2xl font-bold text-white mb-1">MenuPro</h3>
                        <p class="text-sm text-neutral-400 mb-6">Le plan complet pour restaurants et maquis établis.</p>

                        <div class="mb-6 pb-6 border-b border-neutral-800">
                            <div class="flex items-baseline gap-2">
                                <span class="text-5xl font-bold bg-gradient-to-r from-primary-400 to-primary-500 bg-clip-text text-transparent">25 000</span>
                                <span class="text-neutral-500 text-sm">FCFA</span>
                            </div>
                            <div class="text-xs text-neutral-500 mt-2">par mois · ou <span class="text-secondary-400 font-medium">21 250 F</span> en annuel <span class="px-1.5 py-0.5 bg-secondary-500/20 text-secondary-400 text-[10px] font-bold rounded">-15%</span></div>
                        </div>

                        <a href="{{ route('register') }}?plan=menupro"
                           class="flex items-center justify-center gap-2 w-full py-3 px-6 rounded-xl font-bold text-sm bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white transition-all shadow-lg shadow-primary-500/20 hover:shadow-primary-500/30 hover:scale-[1.02] mb-6">
                            Choisir MenuPro
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                        </a>

                        <p class="text-xs font-bold text-primary-400 uppercase tracking-wider mb-3">Tout Starter, plus</p>
                        <ul class="space-y-2.5">
                            @foreach([
                                '100 plats + 30 catégories',
                                '5 comptes employés',
                                '2 000 commandes/mois',
                                'Gestion de stock complète',
                                'Livraison intégrée',
                                'Analytics & rapports détaillés',
                                'Portefeuille & retraits Mobile Money',
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

            <!-- Trust + Link -->
            <div class="mt-12 text-center">
                <div class="flex flex-wrap items-center justify-center gap-x-6 gap-y-2 text-sm text-neutral-500 mb-6">
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
                        Sans engagement
                    </span>
                </div>
                <a href="{{ route('pricing') }}" class="inline-flex items-center gap-2 text-primary-400 font-semibold hover:text-primary-300 transition-colors">
                    Voir la comparaison détaillée et les add-ons
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
                    Pas de carte bancaire requise &bull; Support WhatsApp reactif &bull; Des 9 900 FCFA/mois
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

        // QR Supports physiques : configurateur prix (Support rigide 1500 F / Autocollant 300 F)
        function qrSupportsPricing() {
            return {
                format: 'support',  // 'support' (1500 F) | 'sticker' (300 F)
                quantity: 20,
                prices: { support: 1500, sticker: 300 },
                showOrderForm: @json(session()->has('qr_success') || session()->has('qr_error') || ($errors->any() && old('quantity'))),
                submitting: false,
                get unitPrice() {
                    return this.prices[this.format];
                },
                get total() {
                    return this.unitPrice * Math.max(1, Math.min(999, Number(this.quantity) || 1));
                },
                formatPrice(price) {
                    return new Intl.NumberFormat('fr-FR').format(Math.round(price));
                },
                init() {
                    // Re-hydrate form state on validation error / success
                    const oldFormat = @json(old('format'));
                    const oldQty = @json(old('quantity'));
                    if (oldFormat && ['support', 'sticker'].includes(oldFormat)) {
                        this.format = oldFormat;
                    }
                    if (oldQty) {
                        const n = parseInt(oldQty, 10);
                        if (!isNaN(n) && n > 0) this.quantity = Math.min(999, n);
                    }
                    // Auto-scroll to the form/result if shown
                    if (this.showOrderForm) {
                        this.$nextTick(() => {
                            document.getElementById('qr-order-form')?.scrollIntoView({ behavior: 'smooth', block: 'center' });
                        });
                    }
                }
            }
        }
    </script>
    @endpush
</x-layouts.public>
