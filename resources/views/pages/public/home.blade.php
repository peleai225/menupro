<x-layouts.public title="Accueil" description="MenuPro : digitalisez votre restaurant, menu en ligne, commandes et paiement Mobile Money. Solution SaaS pour restaurants en Cote d'Ivoire.">
    @push('head')
    <script type="application/ld+json">
    {
        "@@context": "https://schema.org",
        "@@type": "SoftwareApplication",
        "name": "MenuPro",
        "applicationCategory": "BusinessApplication",
        "operatingSystem": "Web",
        "url": "{{ url('/') }}",
        "description": "Plateforme SaaS de commande en ligne pour restaurants en Cote d'Ivoire. Paiement Mobile Money, QR codes, gestion de stock.",
        "offers": {
            "@@type": "Offer",
            "price": "15000",
            "priceCurrency": "XOF"
        },
        "author": {
            "@@type": "Organization",
            "name": "MenuPro",
            "url": "{{ url('/') }}"
        }
    }
    </script>
    <style>
        .animate-on-scroll { opacity: 0; transform: translateY(30px); transition: opacity 0.6s ease, transform 0.6s ease; }
        .animate-on-scroll.visible { opacity: 1; transform: translateY(0); }
        .blob { position: absolute; border-radius: 50%; filter: blur(80px); opacity: 0.15; pointer-events: none; }
    </style>
    @endpush

    {{-- HERO — Fond clair avec blobs --}}
    <section class="relative min-h-[92vh] flex items-center overflow-hidden bg-gradient-to-br from-white via-blue-50/40 to-indigo-50/30">
        <div class="blob w-[500px] h-[500px] bg-primary-400 -top-40 -left-40"></div>
        <div class="blob w-[400px] h-[400px] bg-indigo-400 bottom-0 right-0"></div>
        <div class="blob w-[300px] h-[300px] bg-emerald-300 top-1/2 left-1/3"></div>

        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16 lg:py-24">
            <div class="grid lg:grid-cols-2 gap-12 lg:gap-20 items-center">
                {{-- Left: Copy --}}
                <div class="text-center lg:text-left">
                    <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-white border border-neutral-200 rounded-full text-neutral-700 text-sm font-medium mb-6 shadow-sm">
                        <span class="w-2 h-2 bg-emerald-500 rounded-full animate-pulse"></span>
                        <span>7 jours d'essai gratuit</span>
                    </div>

                    <h1 class="text-4xl sm:text-5xl lg:text-[3.5rem] font-bold text-neutral-900 leading-[1.1] tracking-tight">
                        Votre restaurant en ligne,
                        <span class="text-transparent bg-clip-text bg-gradient-to-r from-primary-500 to-indigo-600">commandes et paiements</span>
                        en direct.
                    </h1>

                    <p class="mt-6 text-lg text-neutral-600 max-w-xl mx-auto lg:mx-0 leading-relaxed">
                        Creez votre site de commande en 15 minutes. Vos clients paient par <strong class="text-neutral-900">Wave, Orange Money, MTN, Moov</strong> — l'argent arrive directement sur votre compte.
                    </p>

                    <div class="mt-8 flex flex-col sm:flex-row items-center gap-4 justify-center lg:justify-start">
                        <a href="{{ route('register') }}" class="group inline-flex items-center gap-2 px-7 py-3.5 bg-neutral-900 text-white font-bold rounded-xl hover:bg-neutral-800 shadow-lg shadow-neutral-900/20 transition-all duration-200 hover:-translate-y-0.5 w-full sm:w-auto justify-center">
                            Creer mon restaurant
                            <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                        </a>
                        <a href="{{ route('r.menu', ['slug' => 'demo']) }}" target="_blank" class="inline-flex items-center gap-2 px-7 py-3.5 bg-white text-neutral-800 font-semibold rounded-xl border border-neutral-200 hover:border-neutral-300 hover:shadow-md transition-all w-full sm:w-auto justify-center">
                            <svg class="w-5 h-5 text-primary-500" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                            Voir la demo
                        </a>
                    </div>

                    {{-- Trust stats --}}
                    <div class="mt-10 flex flex-wrap items-center gap-4 sm:gap-8 justify-center lg:justify-start">
                        <div>
                            <div class="text-2xl font-bold text-neutral-900" x-data="counter({{ $stats['raw']['restaurants'] }})" x-intersect.once="startCount()">
                                <span x-text="displayCount"></span>
                            </div>
                            <div class="text-neutral-500 text-xs mt-0.5">Restaurants</div>
                        </div>
                        <div class="w-px h-10 bg-neutral-200 hidden sm:block"></div>
                        <div>
                            <div class="text-2xl font-bold text-neutral-900" x-data="counter({{ $stats['raw']['orders'] }})" x-intersect.once="startCount()">
                                <span x-text="displayCount"></span>
                            </div>
                            <div class="text-neutral-500 text-xs mt-0.5">Commandes</div>
                        </div>
                        <div class="w-px h-10 bg-neutral-200 hidden sm:block"></div>
                        <div>
                            <div class="text-2xl font-bold text-neutral-900">15 000 F</div>
                            <div class="text-neutral-500 text-xs mt-0.5">A partir de</div>
                        </div>
                    </div>
                </div>

                {{-- Right: App mockup --}}
                <div class="relative flex justify-center lg:justify-end">
                    @php
                        $heroImage = \App\Models\SystemSetting::get('hero_image', '');
                    @endphp
                    @if($heroImage && \Illuminate\Support\Facades\Storage::disk('public')->exists($heroImage))
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($heroImage) }}"
                             alt="MenuPro - Interface de commande"
                             width="380" height="700"
                             class="w-full max-w-[320px] sm:max-w-[360px] rounded-3xl shadow-2xl shadow-neutral-900/20 border border-neutral-200"
                             loading="eager">
                    @else
                        <div class="w-[300px] sm:w-[340px] bg-white rounded-[2rem] p-1.5 shadow-2xl shadow-neutral-400/30 border border-neutral-200">
                            <div class="rounded-[1.7rem] overflow-hidden bg-neutral-50">
                                <div class="bg-gradient-to-r from-primary-500 to-primary-600 text-white px-5 pt-10 pb-5">
                                    <div class="flex items-center gap-3">
                                        <div class="w-11 h-11 bg-white/20 rounded-xl flex items-center justify-center">
                                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                        </div>
                                        <div>
                                            <div class="font-bold text-sm">Le Maquis d'Abidjan</div>
                                            <div class="text-xs text-white/80 flex items-center gap-1">
                                                <span class="w-1.5 h-1.5 bg-green-300 rounded-full"></span> Ouvert
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="flex gap-2 p-3 border-b border-neutral-100">
                                    <span class="px-3 py-1.5 bg-primary-500 text-white text-xs font-semibold rounded-full">Populaires</span>
                                    <span class="px-3 py-1.5 bg-neutral-100 text-neutral-600 text-xs rounded-full">Plats</span>
                                    <span class="px-3 py-1.5 bg-neutral-100 text-neutral-600 text-xs rounded-full">Boissons</span>
                                </div>
                                <div class="p-3 space-y-3">
                                    @foreach([
                                        ['Poulet Braise', 'Avec alloco et sauce', '5 500 F', 'from-amber-100 to-orange-100'],
                                        ['Attieke Poisson', 'Poisson braise, legumes', '4 500 F', 'from-yellow-100 to-amber-100'],
                                        ['Salade Africaine', 'Legumes frais, vinaigrette', '2 500 F', 'from-green-100 to-emerald-100'],
                                    ] as $dish)
                                    <div class="bg-white rounded-xl p-3 flex gap-3 shadow-sm border border-neutral-100">
                                        <div class="w-14 h-14 bg-gradient-to-br {{ $dish[3] }} rounded-lg shrink-0"></div>
                                        <div class="flex-1 min-w-0">
                                            <div class="font-bold text-sm text-neutral-800">{{ $dish[0] }}</div>
                                            <div class="text-xs text-neutral-500">{{ $dish[1] }}</div>
                                            <div class="flex items-center justify-between mt-1.5">
                                                <span class="text-primary-600 font-bold text-sm">{{ $dish[2] }}</span>
                                                <span class="w-6 h-6 bg-primary-500 text-white rounded-full flex items-center justify-center text-xs font-bold">+</span>
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                <div class="p-3">
                                    <div class="bg-primary-500 text-white rounded-xl p-3 flex items-center justify-between">
                                        <div class="flex items-center gap-2">
                                            <span class="w-7 h-7 bg-white/20 rounded-full flex items-center justify-center text-xs font-bold">3</span>
                                            <span class="text-sm font-medium">Voir le panier</span>
                                        </div>
                                        <span class="font-bold">12 500 F</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>

    {{-- LOGO TICKER — Payment methods --}}
    <section class="py-10 bg-white border-y border-neutral-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row items-center justify-center gap-6 sm:gap-10">
                <span class="text-sm text-neutral-500 font-semibold whitespace-nowrap uppercase tracking-wide">Paiements acceptes</span>
                <div class="flex items-center gap-4 sm:gap-6 md:gap-10">
                    <div class="flex flex-col items-center gap-1.5">
                        <img src="{{ asset('images/payments/wave.png') }}" alt="Wave" class="h-11 w-11 sm:h-14 sm:w-14 md:h-16 md:w-16 object-contain hover:scale-110 transition-transform" loading="lazy">
                        <span class="text-xs text-neutral-500 font-medium">Wave</span>
                    </div>
                    <div class="flex flex-col items-center gap-1.5">
                        <img src="{{ asset('images/payments/orange-money.png') }}" alt="Orange Money" class="h-11 w-11 sm:h-14 sm:w-14 md:h-16 md:w-16 object-contain hover:scale-110 transition-transform" loading="lazy">
                        <span class="text-xs text-neutral-500 font-medium">Orange Money</span>
                    </div>
                    <div class="flex flex-col items-center gap-1.5">
                        <img src="{{ asset('images/payments/mtn-momo.png') }}" alt="MTN MoMo" class="h-11 w-11 sm:h-14 sm:w-14 md:h-16 md:w-16 object-contain hover:scale-110 transition-transform" loading="lazy">
                        <span class="text-xs text-neutral-500 font-medium">MTN MoMo</span>
                    </div>
                    <div class="flex flex-col items-center gap-1.5">
                        <img src="{{ asset('images/payments/moov-money.png') }}" alt="Moov Money" class="h-11 w-11 sm:h-14 sm:w-14 md:h-16 md:w-16 object-contain hover:scale-110 transition-transform" loading="lazy">
                        <span class="text-xs text-neutral-500 font-medium">Moov Money</span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- FEATURES — Bento Grid --}}
    <section class="py-20 sm:py-28 bg-gradient-to-b from-white to-blue-50/30 relative overflow-hidden">
        <div class="blob w-[600px] h-[600px] bg-blue-300 -bottom-60 -right-40"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center max-w-2xl mx-auto mb-16 animate-on-scroll">
                <div class="inline-flex items-center gap-2 px-3 py-1 bg-primary-50 border border-primary-100 rounded-full text-primary-700 text-xs font-semibold mb-4">FONCTIONNALITES</div>
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-neutral-900 leading-tight">
                    Tout pour gerer votre restaurant en ligne
                </h2>
                <p class="text-neutral-600 text-lg mt-4">
                    Une plateforme unique, pensee pour les restaurateurs ivoiriens.
                </p>
            </div>

            {{-- Bento Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-5 animate-on-scroll">
                {{-- Large card --}}
                <div class="lg:col-span-2 bg-white rounded-2xl p-8 border border-neutral-100 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
                    <div class="flex items-start gap-5">
                        <div class="w-14 h-14 bg-gradient-to-br from-primary-100 to-primary-200 rounded-2xl flex items-center justify-center shrink-0">
                            <svg class="w-7 h-7 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-neutral-900 mb-2">Commandes en temps reel</h3>
                            <p class="text-neutral-600 leading-relaxed">Vos clients commandent depuis leur telephone. QR code sur les tables ou lien partage par WhatsApp. Notification instantanee sur votre dashboard et ecran cuisine.</p>
                        </div>
                    </div>
                </div>

                {{-- Card --}}
                <div class="bg-white rounded-2xl p-6 border border-neutral-100 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
                    <div class="w-12 h-12 bg-gradient-to-br from-emerald-100 to-emerald-200 rounded-xl flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-neutral-900 mb-2">Paiement Mobile Money</h3>
                    <p class="text-neutral-600 text-sm leading-relaxed">Wave, Orange Money, MTN, Moov. L'argent arrive directement sur votre compte.</p>
                </div>

                {{-- Card --}}
                <div class="bg-white rounded-2xl p-6 border border-neutral-100 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
                    <div class="w-12 h-12 bg-gradient-to-br from-blue-100 to-blue-200 rounded-xl flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-neutral-900 mb-2">Gestion complete</h3>
                    <p class="text-neutral-600 text-sm leading-relaxed">Menu, stock, commandes, equipe, statistiques. Un seul dashboard pour tout piloter.</p>
                </div>

                {{-- Card --}}
                <div class="bg-white rounded-2xl p-6 border border-neutral-100 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
                    <div class="w-12 h-12 bg-gradient-to-br from-amber-100 to-amber-200 rounded-xl flex items-center justify-center mb-4">
                        <svg class="w-6 h-6 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <h3 class="text-lg font-bold text-neutral-900 mb-2">Zero commission</h3>
                    <p class="text-neutral-600 text-sm leading-relaxed">Pas de 20% par commande. Un forfait fixe a partir de 15 000 F/mois.</p>
                </div>

                {{-- Large card --}}
                <div class="lg:col-span-2 bg-white rounded-2xl p-8 border border-neutral-100 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
                    <div class="flex items-start gap-5">
                        <div class="w-14 h-14 bg-gradient-to-br from-violet-100 to-violet-200 rounded-2xl flex items-center justify-center shrink-0">
                            <svg class="w-7 h-7 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                        </div>
                        <div>
                            <h3 class="text-xl font-bold text-neutral-900 mb-2">Livraison integree</h3>
                            <p class="text-neutral-600 leading-relaxed">Gerez vos livreurs, suivez en temps reel, et vos clients voient leur commande avancer. Comme Glovo mais avec VOS livreurs.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- STATS — 4 colonnes --}}
    <section class="py-16 bg-white border-y border-neutral-100">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-8 text-center animate-on-scroll">
                <div>
                    <div class="text-4xl sm:text-5xl font-bold text-neutral-900">15 min</div>
                    <div class="text-neutral-500 text-sm mt-2">Pour etre en ligne</div>
                </div>
                <div>
                    <div class="text-4xl sm:text-5xl font-bold text-neutral-900">0%</div>
                    <div class="text-neutral-500 text-sm mt-2">Commission par commande</div>
                </div>
                <div>
                    <div class="text-4xl sm:text-5xl font-bold text-primary-600">4</div>
                    <div class="text-neutral-500 text-sm mt-2">Moyens de paiement</div>
                </div>
                <div>
                    <div class="text-4xl sm:text-5xl font-bold text-neutral-900">24/7</div>
                    <div class="text-neutral-500 text-sm mt-2">Commandes sans interruption</div>
                </div>
            </div>
        </div>
    </section>

    {{-- HOW IT WORKS — 3 steps --}}
    <section id="how-it-works" class="py-20 sm:py-28 bg-gradient-to-b from-blue-50/30 to-white relative overflow-hidden">
        <div class="blob w-[400px] h-[400px] bg-primary-300 top-20 -left-40"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center max-w-2xl mx-auto mb-16 animate-on-scroll">
                <div class="inline-flex items-center gap-2 px-3 py-1 bg-emerald-50 border border-emerald-100 rounded-full text-emerald-700 text-xs font-semibold mb-4">COMMENT CA MARCHE</div>
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-neutral-900 leading-tight">
                    De zero au premier client en 15 minutes
                </h2>
                <p class="text-neutral-600 text-lg mt-4">
                    Pas besoin d'etre developpeur. Juste votre restaurant et l'envie de recevoir des commandes.
                </p>
            </div>

            <div class="grid md:grid-cols-3 gap-8 animate-on-scroll">
                @foreach([
                    ['1', 'Creez votre compte', 'Nom du restaurant, email, telephone. En 2 minutes, votre espace est pret.', '~2 minutes', 'primary'],
                    ['2', 'Ajoutez votre menu', 'Photos, prix, categories. Configurez vos horaires et moyens de paiement.', '~10 minutes', 'primary'],
                    ['3', 'Recevez des commandes', 'Partagez votre lien ou QR code. Les commandes arrivent en temps reel.', 'Immediat', 'emerald'],
                ] as $step)
                <div class="relative bg-white rounded-2xl p-8 border border-neutral-100 shadow-sm hover:shadow-lg transition-all duration-300">
                    <div class="w-12 h-12 bg-{{ $step[4] }}-500 rounded-xl flex items-center justify-center text-white font-bold text-lg mb-5">{{ $step[0] }}</div>
                    <h3 class="text-xl font-bold text-neutral-900 mb-3">{{ $step[1] }}</h3>
                    <p class="text-neutral-600 leading-relaxed">{{ $step[2] }}</p>
                    <span class="inline-block mt-4 text-xs font-semibold text-{{ $step[4] }}-600 bg-{{ $step[4] }}-50 px-3 py-1 rounded-full">{{ $step[3] }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- QR CODE — Parcours client visuel --}}
    <section class="py-20 sm:py-28 bg-gradient-to-b from-indigo-50/40 to-white relative overflow-hidden">
        <div class="blob w-[500px] h-[500px] bg-indigo-300 -top-40 -right-60"></div>
        <div class="blob w-[300px] h-[300px] bg-primary-200 bottom-20 -left-20"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center max-w-2xl mx-auto mb-14 animate-on-scroll">
                <div class="inline-flex items-center gap-2 px-3 py-1 bg-indigo-50 border border-indigo-100 rounded-full text-indigo-700 text-xs font-semibold mb-4">QR CODE</div>
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-neutral-900 leading-tight">
                    Du scan au paiement en 2 minutes
                </h2>
                <p class="text-neutral-600 text-lg mt-4">
                    Vos clients scannent le QR code sur la table, commandent et paient — sans telecharger d'application.
                </p>
            </div>

            @php
                $qrSteps = [
                    ['title' => 'Scanner le QR code', 'desc' => 'Le client scanne le QR code sur la table ou le support avec son telephone. Aucune application a telecharger.', 'image' => 'images/home/qr-step-1.png', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>', 'color' => 'indigo'],
                    ['title' => 'Choisir avec photos et prix', 'desc' => 'Il parcourt votre menu complet avec photos appetissantes, prix clairs et categories intuitives.', 'image' => 'images/home/qr-step-2.png', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>', 'color' => 'primary'],
                    ['title' => 'La commande part en cuisine', 'desc' => 'Transmission instantanee a votre ecran cuisine. Zero erreur, zero delai, zero papier.', 'image' => 'images/home/qr-step-3.png', 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>', 'color' => 'amber'],
                    ['title' => 'Payer par Mobile Money', 'desc' => 'Le client paie directement depuis son telephone : Wave, Orange Money, MTN ou Moov. L\'argent arrive sur votre compte.', 'image' => null, 'icon' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>', 'color' => 'emerald'],
                ];
            @endphp

            {{-- Stepper horizontal (desktop) --}}
            <div class="hidden lg:flex items-center justify-center gap-0 mb-10 animate-on-scroll">
                @foreach($qrSteps as $i => $step)
                    <div class="flex items-center">
                        <div class="flex items-center gap-2 px-4 py-2 rounded-full {{ $i === 0 ? 'bg-indigo-100 text-indigo-700' : ($i === 3 ? 'bg-emerald-100 text-emerald-700' : 'bg-neutral-100 text-neutral-600') }}">
                            <span class="w-6 h-6 rounded-full bg-{{ $step['color'] }}-500 text-white text-xs font-bold flex items-center justify-center">{{ $i + 1 }}</span>
                            <span class="text-sm font-semibold whitespace-nowrap">{{ $step['title'] }}</span>
                        </div>
                        @if($i < 3)
                        <svg class="w-6 h-6 text-neutral-300 mx-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                        @endif
                    </div>
                @endforeach
            </div>

            {{-- Grille 2x2 --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5 lg:gap-6 animate-on-scroll">
                @foreach($qrSteps as $i => $step)
                <div class="group relative bg-white rounded-2xl border border-neutral-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 overflow-hidden">
                    @if($step['image'])
                    {{-- Carte avec image --}}
                    <div class="grid grid-cols-1 sm:grid-rows-[180px_1fr]">
                        <div class="w-full h-44 sm:h-full overflow-hidden bg-neutral-50">
                            <img src="{{ asset($step['image']) }}" alt="{{ $step['title'] }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
                        </div>
                        <div class="p-5 sm:p-6">
                            <div class="flex items-center gap-3 mb-3">
                                <span class="w-9 h-9 bg-{{ $step['color'] }}-500 rounded-xl flex items-center justify-center text-white text-sm font-bold shrink-0 shadow-sm">{{ $i + 1 }}</span>
                                <h3 class="font-bold text-neutral-900 text-lg">{{ $step['title'] }}</h3>
                            </div>
                            <p class="text-sm text-neutral-500 leading-relaxed">{{ $step['desc'] }}</p>
                        </div>
                    </div>
                    @else
                    {{-- Carte sans image (paiement) — design enrichi --}}
                    <div class="p-6 sm:p-8 h-full flex flex-col justify-between">
                        <div>
                            <div class="flex items-center gap-3 mb-4">
                                <span class="w-9 h-9 bg-{{ $step['color'] }}-500 rounded-xl flex items-center justify-center text-white text-sm font-bold shrink-0 shadow-sm">{{ $i + 1 }}</span>
                                <h3 class="font-bold text-neutral-900 text-lg">{{ $step['title'] }}</h3>
                            </div>
                            <p class="text-sm text-neutral-500 leading-relaxed mb-6">{{ $step['desc'] }}</p>
                        </div>
                        {{-- Logos paiement --}}
                        <div class="bg-gradient-to-br from-emerald-50 to-emerald-100/50 rounded-xl p-5 border border-emerald-100">
                            <div class="flex items-center justify-center gap-4 flex-wrap">
                                <img src="{{ asset('images/payments/wave.png') }}" alt="Wave" class="h-10 w-10 object-contain" loading="lazy">
                                <img src="{{ asset('images/payments/orange-money.png') }}" alt="Orange Money" class="h-10 w-10 object-contain" loading="lazy">
                                <img src="{{ asset('images/payments/mtn-momo.png') }}" alt="MTN MoMo" class="h-10 w-10 object-contain" loading="lazy">
                                <img src="{{ asset('images/payments/moov-money.png') }}" alt="Moov Money" class="h-10 w-10 object-contain" loading="lazy">
                            </div>
                            <p class="text-center text-xs text-emerald-600 font-medium mt-3">Paiement instantane — argent sur votre compte</p>
                        </div>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>

            {{-- CTA vers commande supports QR --}}
            <div class="mt-10 text-center animate-on-scroll">
                <a href="{{ url('/supports-qr') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 text-white font-semibold rounded-xl hover:bg-indigo-700 shadow-md shadow-indigo-500/20 transition-all hover:-translate-y-0.5">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                    Commander vos supports QR
                </a>
                <p class="text-sm text-neutral-500 mt-2">Supports rigides ou autocollants — livres a votre restaurant</p>
            </div>
        </div>
    </section>

    {{-- VIDEO DEMO --}}
    @if(!empty($videos))
    <section class="py-20 sm:py-28 bg-gradient-to-b from-blue-50/40 to-white relative overflow-hidden">
        <div class="blob w-[400px] h-[400px] bg-primary-300 bottom-0 left-0"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center max-w-2xl mx-auto mb-16 animate-on-scroll">
                <div class="inline-flex items-center gap-2 px-3 py-1 bg-red-50 border border-red-100 rounded-full text-red-600 text-xs font-semibold mb-4">VIDEO</div>
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-neutral-900 leading-tight">
                    Voyez MenuPro en action
                </h2>
                <p class="text-neutral-600 text-lg mt-4">
                    Decouvrez comment nos restaurateurs utilisent MenuPro au quotidien.
                </p>
            </div>

            <div class="grid md:grid-cols-{{ count($videos) > 1 ? '2' : '1' }} gap-8 max-w-5xl mx-auto animate-on-scroll">
                @foreach($videos as $video)
                <div class="group">
                    <div class="relative aspect-video bg-neutral-900 rounded-2xl overflow-hidden shadow-xl border border-neutral-200">
                        <iframe
                            src="{{ $video['url'] }}"
                            title="{{ $video['title'] }}"
                            class="w-full h-full"
                            frameborder="0"
                            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                            allowfullscreen
                            loading="lazy">
                        </iframe>
                    </div>
                    <div class="mt-4 text-center">
                        <h3 class="font-bold text-neutral-900">{{ $video['title'] }}</h3>
                        @if($video['description'])
                        <p class="text-sm text-neutral-500 mt-1">{{ $video['description'] }}</p>
                        @endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- TEMOIGNAGES --}}
    <section class="py-20 sm:py-28 bg-white relative overflow-hidden">
        <div class="blob w-[400px] h-[400px] bg-emerald-200 -top-40 -left-40"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center max-w-2xl mx-auto mb-16 animate-on-scroll">
                <div class="inline-flex items-center gap-2 px-3 py-1 bg-emerald-50 border border-emerald-100 rounded-full text-emerald-700 text-xs font-semibold mb-4">TEMOIGNAGES</div>
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-neutral-900 leading-tight">
                    Ils utilisent MenuPro au quotidien
                </h2>
                <p class="text-neutral-600 text-lg mt-4">
                    Des restaurateurs comme vous qui ont fait le pas vers la digitalisation.
                </p>
            </div>

            @php
                $testimonials = \App\Models\SystemSetting::get('home_testimonials', [
                    [
                        'name' => 'Awa Kone',
                        'role' => 'Proprietaire, Maquis Chez Awa',
                        'city' => 'Daloa',
                        'text' => 'Depuis que j\'utilise MenuPro, mes clients commandent directement avec leur telephone. Plus besoin d\'attendre le serveur. Mon chiffre d\'affaires a augmente de 30%.',
                        'avatar' => '',
                        'stars' => 5,
                    ],
                    [
                        'name' => 'Kouame Jean',
                        'role' => 'Gerant, Restaurant Le Delice',
                        'city' => 'Abidjan',
                        'text' => 'Le QR code sur les tables a tout change. Les clients scannent, commandent et paient par Wave. C\'est rapide et je recois l\'argent immediatement.',
                        'avatar' => '',
                        'stars' => 5,
                    ],
                    [
                        'name' => 'Marie Toure',
                        'role' => 'Fondatrice, Saveurs d\'Afrique',
                        'city' => 'Bouake',
                        'text' => 'A 15 000 F par mois, c\'est le meilleur investissement pour mon restaurant. Pas de commission, pas de surprise. Je recommande a tous les restaurateurs.',
                        'avatar' => '',
                        'stars' => 5,
                    ],
                ]);
            @endphp

            <div class="grid md:grid-cols-3 gap-6 animate-on-scroll">
                @foreach($testimonials as $t)
                <div class="bg-white rounded-2xl p-7 border border-neutral-100 shadow-sm hover:shadow-lg transition-all duration-300">
                    {{-- Stars --}}
                    <div class="flex gap-0.5 mb-4">
                        @for($i = 0; $i < ($t['stars'] ?? 5); $i++)
                        <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        @endfor
                    </div>

                    {{-- Quote --}}
                    <p class="text-neutral-700 leading-relaxed mb-6 text-sm">"{{ $t['text'] }}"</p>

                    {{-- Author --}}
                    <div class="flex items-center gap-3">
                        @if(!empty($t['avatar']) && file_exists(public_path($t['avatar'])))
                            <img src="{{ asset($t['avatar']) }}" alt="{{ $t['name'] }}" class="w-10 h-10 rounded-full object-cover border border-neutral-100">
                        @else
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary-100 to-primary-200 flex items-center justify-center text-primary-700 font-bold text-sm">
                                {{ strtoupper(substr($t['name'], 0, 1)) }}
                            </div>
                        @endif
                        <div>
                            <div class="font-bold text-sm text-neutral-900">{{ $t['name'] }}</div>
                            <div class="text-xs text-neutral-500">{{ $t['role'] }} — {{ $t['city'] }}</div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- LOGOS CLIENTS — Vrais restaurants de la plateforme --}}
    <section class="py-16 bg-neutral-50 border-y border-neutral-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10 animate-on-scroll">
                <h3 class="text-lg font-bold text-neutral-900 mb-2">Ils nous font confiance</h3>
                <p class="text-sm text-neutral-500">Restaurants et maquis qui utilisent MenuPro au quotidien</p>
            </div>

            @php
                $trustedRestaurants = \App\Models\Restaurant::where('status', \App\Enums\RestaurantStatus::ACTIVE)
                    ->whereNotNull('logo_path')
                    ->where('logo_path', '!=', '')
                    ->latest()
                    ->take(12)
                    ->get(['name', 'slug', 'logo_path', 'city']);
            @endphp

            @if($trustedRestaurants->count() > 0)
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-6 items-center justify-items-center animate-on-scroll">
                @foreach($trustedRestaurants as $resto)
                <a href="{{ route('r.menu', $resto->slug) }}" target="_blank" class="group flex flex-col items-center justify-center gap-2 p-4 bg-white rounded-2xl border border-neutral-100 hover:border-primary-200 hover:shadow-md transition-all w-full">
                    <img src="{{ Storage::url($resto->logo_path) }}"
                         alt="{{ $resto->name }}"
                         class="w-14 h-14 rounded-xl object-cover border border-neutral-100 group-hover:scale-105 transition-transform"
                         loading="lazy">
                    <div class="text-center">
                        <span class="text-xs font-semibold text-neutral-700 block truncate max-w-[100px]">{{ $resto->name }}</span>
                        @if($resto->city)
                        <span class="text-[10px] text-neutral-400">{{ $resto->city }}</span>
                        @endif
                    </div>
                </a>
                @endforeach
            </div>
            @else
            <div class="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 lg:grid-cols-6 gap-6 items-center justify-items-center animate-on-scroll">
                @php
                    $placeholderRestaurants = \App\Models\Restaurant::where('status', \App\Enums\RestaurantStatus::ACTIVE)
                        ->latest()
                        ->take(6)
                        ->get(['name', 'slug', 'city']);
                @endphp
                @foreach($placeholderRestaurants as $resto)
                <a href="{{ route('r.menu', $resto->slug) }}" target="_blank" class="group flex flex-col items-center justify-center gap-2 p-4 bg-white rounded-2xl border border-neutral-100 hover:border-primary-200 hover:shadow-md transition-all w-full">
                    <div class="w-14 h-14 rounded-xl bg-gradient-to-br from-primary-100 to-primary-200 flex items-center justify-center text-primary-700 font-bold text-lg group-hover:scale-105 transition-transform">
                        {{ strtoupper(substr($resto->name, 0, 1)) }}
                    </div>
                    <div class="text-center">
                        <span class="text-xs font-semibold text-neutral-700 block truncate max-w-[100px]">{{ $resto->name }}</span>
                        @if($resto->city)
                        <span class="text-[10px] text-neutral-400">{{ $resto->city }}</span>
                        @endif
                    </div>
                </a>
                @endforeach
            </div>
            @endif
        </div>
    </section>

    {{-- PRICING — 3 plans --}}
    <section id="pricing" class="py-20 sm:py-28 bg-white relative">
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-16 animate-on-scroll">
                <div class="inline-flex items-center gap-2 px-3 py-1 bg-amber-50 border border-amber-100 rounded-full text-amber-700 text-xs font-semibold mb-4">TARIFS</div>
                <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-neutral-900 leading-tight">
                    Un prix simple, pas de surprise
                </h2>
                <p class="text-neutral-600 text-lg mt-4">
                    Pas de commission par commande. Un forfait mensuel fixe. Economisez jusqu'a 20% sur l'annee.
                </p>
            </div>

            <div class="grid md:grid-cols-3 gap-6 animate-on-scroll">
                {{-- Essentiel --}}
                <div class="bg-white rounded-2xl p-7 border border-neutral-200 hover:border-neutral-300 hover:shadow-lg transition-all duration-300">
                    <div class="text-xs font-semibold text-neutral-500 uppercase tracking-wider mb-3">Essentiel</div>
                    <div class="flex items-baseline gap-1 mb-2">
                        <span class="text-4xl font-bold text-neutral-900">15 000</span>
                        <span class="text-neutral-500 text-sm">F/mois</span>
                    </div>
                    <p class="text-sm text-neutral-500 mb-6">Pour les petits maquis qui demarrent</p>
                    <ul class="space-y-3 mb-8">
                        @foreach(['25 plats, 8 categories', '200 commandes/mois', 'Mobile Money + QR codes', 'Support WhatsApp'] as $f)
                        <li class="flex items-center gap-2.5 text-sm text-neutral-700">
                            <svg class="w-4.5 h-4.5 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            {{ $f }}
                        </li>
                        @endforeach
                    </ul>
                    <a href="{{ route('register') }}?plan=essentiel" class="block w-full text-center py-3 px-6 rounded-xl font-bold text-sm bg-neutral-100 text-neutral-900 hover:bg-neutral-200 transition-all">
                        Essai gratuit 7j
                    </a>
                </div>

                {{-- Pro (featured) --}}
                <div class="bg-white rounded-2xl p-7 border-2 border-primary-500 shadow-xl shadow-primary-100/50 relative hover:shadow-2xl transition-all duration-300 -mt-2 mb-[-8px]">
                    <div class="absolute -top-3 left-1/2 -translate-x-1/2 bg-primary-500 text-white text-xs font-bold px-4 py-1 rounded-full">Populaire</div>
                    <div class="text-xs font-semibold text-primary-600 uppercase tracking-wider mb-3">Pro</div>
                    <div class="flex items-baseline gap-1 mb-2">
                        <span class="text-4xl font-bold text-neutral-900">25 000</span>
                        <span class="text-neutral-500 text-sm">F/mois</span>
                    </div>
                    <p class="text-sm text-neutral-500 mb-6">Stock, livraison et analytics inclus</p>
                    <ul class="space-y-3 mb-8">
                        @foreach(['80 plats, 3 employes', '1 000 commandes/mois', 'Gestion stock complete', 'Livraison integree', 'Analytics & rapports'] as $f)
                        <li class="flex items-center gap-2.5 text-sm text-neutral-700">
                            <svg class="w-4.5 h-4.5 text-primary-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            {{ $f }}
                        </li>
                        @endforeach
                    </ul>
                    <a href="{{ route('register') }}?plan=pro" class="group block w-full text-center py-3 px-6 rounded-xl font-bold text-sm bg-primary-500 text-white hover:bg-primary-600 shadow-md shadow-primary-500/20 transition-all">
                        Essai gratuit 7j
                    </a>
                </div>

                {{-- Business --}}
                <div class="bg-white rounded-2xl p-7 border border-neutral-200 hover:border-amber-300 hover:shadow-lg transition-all duration-300">
                    <div class="text-xs font-semibold text-amber-600 uppercase tracking-wider mb-3">Business</div>
                    <div class="flex items-baseline gap-1 mb-2">
                        <span class="text-4xl font-bold text-neutral-900">45 000</span>
                        <span class="text-neutral-500 text-sm">F/mois</span>
                    </div>
                    <p class="text-sm text-neutral-500 mb-6">Tout illimite pour les grands restaurants</p>
                    <ul class="space-y-3 mb-8">
                        @foreach(['Plats et commandes illimites', '10 employes', 'Support prioritaire (2h)', 'Domaine personnalise', 'Multi-restaurant'] as $f)
                        <li class="flex items-center gap-2.5 text-sm text-neutral-700">
                            <svg class="w-4.5 h-4.5 text-amber-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                            {{ $f }}
                        </li>
                        @endforeach
                    </ul>
                    <a href="{{ route('register') }}?plan=business" class="block w-full text-center py-3 px-6 rounded-xl font-bold text-sm bg-neutral-100 text-neutral-900 hover:bg-neutral-200 transition-all">
                        Essai gratuit 7j
                    </a>
                </div>
            </div>

            <p class="text-center text-neutral-500 text-sm mt-8">
                7 jours d'essai gratuit. Sans engagement.
                <a href="{{ route('pricing') }}" class="text-primary-600 hover:text-primary-700 font-medium">Voir comparaison detaillee &rarr;</a>
            </p>
        </div>
    </section>

    {{-- CTA FINAL --}}
    <section class="py-20 sm:py-28 bg-gradient-to-br from-neutral-900 via-neutral-900 to-indigo-950 relative overflow-hidden">
        <div class="blob w-[500px] h-[500px] bg-primary-500 opacity-10 top-0 right-0"></div>
        <div class="relative z-10 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <h2 class="text-3xl sm:text-4xl lg:text-5xl font-bold text-white leading-tight">
                Pret a digitaliser votre restaurant ?
            </h2>
            <p class="text-lg text-neutral-400 mt-4 max-w-xl mx-auto">
                Rejoignez les restaurateurs ivoiriens qui recoivent des commandes en ligne et sont payes directement sur leur Mobile Money.
            </p>

            <div class="mt-8 flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="{{ route('register') }}" class="group inline-flex items-center gap-2 px-8 py-4 bg-white text-neutral-900 font-bold rounded-xl hover:bg-neutral-100 shadow-lg transition-all duration-200 hover:-translate-y-0.5">
                    Creer mon restaurant
                    <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </a>
                <a href="{{ route('r.menu', ['slug' => 'demo']) }}" target="_blank" class="inline-flex items-center gap-2 px-8 py-4 text-white font-semibold rounded-xl border border-white/20 hover:bg-white/5 transition-all">
                    Voir la demo
                </a>
            </div>

            <div class="mt-10 flex flex-wrap items-center justify-center gap-6 text-sm text-neutral-500">
                <span class="flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    Configuration en 15 min
                </span>
                <span class="flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    Support WhatsApp
                </span>
                <span class="flex items-center gap-1.5">
                    <svg class="w-4 h-4 text-emerald-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    A partir de 15 000 F/mois
                </span>
            </div>
        </div>
    </section>

    @push('scripts')
    <script>
        function counter(target) {
            return {
                count: 0,
                target: target,
                displayCount: '0',
                startCount() {
                    const duration = 1500;
                    const steps = 40;
                    const stepValue = this.target / steps;
                    const stepDuration = duration / steps;
                    const interval = setInterval(() => {
                        this.count += stepValue;
                        if (this.count >= this.target) {
                            this.count = this.target;
                            clearInterval(interval);
                        }
                        if (this.count >= 1000) {
                            this.displayCount = Math.round(this.count / 1000) + 'K+';
                        } else {
                            this.displayCount = Math.round(this.count).toString();
                        }
                    }, stepDuration);
                }
            }
        }

        // Scroll animations
        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                }
            });
        }, { threshold: 0.1 });

        document.querySelectorAll('.animate-on-scroll').forEach(el => observer.observe(el));
    </script>
    @endpush
</x-layouts.public>
