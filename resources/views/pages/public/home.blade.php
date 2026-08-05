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
        "offers": { "@@type": "Offer", "price": "15000", "priceCurrency": "XOF" },
        "author": { "@@type": "Organization", "name": "MenuPro", "url": "{{ url('/') }}" }
    }
    </script>
    <style>
        /* Animations */
        .fade-up { opacity: 0; transform: translateY(32px); transition: opacity .65s cubic-bezier(.22,1,.36,1), transform .65s cubic-bezier(.22,1,.36,1); }
        .fade-up.visible { opacity: 1; transform: none; }
        .fade-up.delay-1 { transition-delay: .1s; }
        .fade-up.delay-2 { transition-delay: .2s; }
        .fade-up.delay-3 { transition-delay: .3s; }
        .fade-up.delay-4 { transition-delay: .4s; }

        /* Hero gradient mesh */
        .hero-mesh {
            background:
                radial-gradient(ellipse 80% 60% at 20% 0%, rgba(59,130,246,.18) 0%, transparent 60%),
                radial-gradient(ellipse 60% 50% at 80% 100%, rgba(99,102,241,.15) 0%, transparent 60%),
                radial-gradient(ellipse 40% 40% at 50% 50%, rgba(16,185,129,.08) 0%, transparent 70%),
                #0f172a;
        }

        /* Ticker */
        @keyframes ticker { from { transform: translateX(0); } to { transform: translateX(-50%); } }
        .ticker-track { display: flex; width: max-content; animation: ticker 28s linear infinite; }
        .ticker-track:hover { animation-play-state: paused; }

        /* Card glow on hover */
        .card-glow:hover { box-shadow: 0 0 0 1px rgba(59,130,246,.3), 0 8px 32px rgba(59,130,246,.12); }

        /* Gradient text */
        .text-gradient { background: linear-gradient(135deg, #38bdf8, #818cf8); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }
        .text-gradient-orange { background: linear-gradient(135deg, #fb923c, #f43f5e); -webkit-background-clip: text; -webkit-text-fill-color: transparent; background-clip: text; }

        /* Pulse ring */
        @keyframes ping-slow { 0%,100%{transform:scale(1);opacity:.4} 50%{transform:scale(1.6);opacity:0} }
        .ping-slow { animation: ping-slow 2.5s ease-in-out infinite; }

        /* Notification float */
        @keyframes float { 0%,100%{transform:translateY(0)} 50%{transform:translateY(-8px)} }
        .float { animation: float 4s ease-in-out infinite; }

        /* Step connector */
        .step-line::after {
            content: '';
            position: absolute;
            top: 22px;
            left: calc(100% + 16px);
            width: calc(100% - 16px);
            height: 2px;
            background: linear-gradient(90deg, #3b82f6, transparent);
        }
    </style>
    @endpush

    {{-- ══════════════════════════════════════
         HERO — Dark gradient mesh
    ══════════════════════════════════════ --}}
    <section class="hero-mesh relative min-h-screen flex items-center overflow-hidden">

        {{-- Grid pattern overlay --}}
        <div class="absolute inset-0 opacity-[0.04]" style="background-image:linear-gradient(rgba(255,255,255,.5) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.5) 1px,transparent 1px);background-size:40px 40px"></div>

        <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-20 lg:py-28 w-full">
            <div class="grid lg:grid-cols-2 gap-14 lg:gap-20 items-center">

                {{-- Left: Copy --}}
                <div class="text-center lg:text-left">

                    {{-- Badge --}}
                    <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-white/10 backdrop-blur border border-white/20 rounded-full text-white/90 text-sm font-medium mb-8 fade-up visible">
                        <span class="relative flex h-2 w-2">
                            <span class="ping-slow absolute inline-flex h-full w-full rounded-full bg-emerald-400"></span>
                            <span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-400"></span>
                        </span>
                        7 jours d'essai gratuit — sans carte bancaire
                    </div>

                    <h1 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-white leading-[1.08] tracking-tight fade-up visible">
                        Votre restaurant<br>
                        commande et paye<br>
                        <span class="text-gradient">en 15 minutes.</span>
                    </h1>

                    <p class="mt-6 text-lg sm:text-xl text-white/60 max-w-lg mx-auto lg:mx-0 leading-relaxed fade-up visible delay-1">
                        De la vendeuse de panini au grand hotel, MenuPro digitalise votre activite. Vos clients paient par <span class="text-white font-semibold">Wave, Orange Money, MTN, Moov</span> — l'argent arrive directement sur votre compte.
                    </p>

                    <div class="mt-10 flex flex-col sm:flex-row items-center gap-4 justify-center lg:justify-start fade-up visible delay-2">
                        <a href="{{ route('register') }}" class="group w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-4 bg-white text-neutral-900 font-bold rounded-2xl hover:bg-blue-50 shadow-2xl shadow-white/20 transition-all duration-200 hover:-translate-y-0.5 text-base">
                            Créer mon restaurant gratuit
                            <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                        </a>
                        <a href="{{ route('r.menu', ['slug' => 'demo']) }}" target="_blank" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-4 text-white font-semibold rounded-2xl border border-white/20 hover:bg-white/10 transition-all text-base backdrop-blur">
                            <svg class="w-5 h-5 text-blue-400" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                            Voir la démo
                        </a>
                    </div>

                    {{-- Trust stats --}}
                    <div class="mt-12 flex flex-wrap items-center gap-6 sm:gap-10 justify-center lg:justify-start fade-up visible delay-3">
                        <div class="text-center lg:text-left">
                            <div class="text-3xl font-extrabold text-white" x-data="counter({{ $stats['raw']['restaurants'] }})" x-intersect.once="startCount()">
                                <span x-text="displayCount"></span>
                            </div>
                            <div class="text-white/50 text-xs mt-0.5 uppercase tracking-widest">Restaurants</div>
                        </div>
                        <div class="w-px h-10 bg-white/10 hidden sm:block"></div>
                        <div class="text-center lg:text-left">
                            <div class="text-3xl font-extrabold text-white" x-data="counter({{ $stats['raw']['orders'] }})" x-intersect.once="startCount()">
                                <span x-text="displayCount"></span>
                            </div>
                            <div class="text-white/50 text-xs mt-0.5 uppercase tracking-widest">Commandes</div>
                        </div>
                        <div class="w-px h-10 bg-white/10 hidden sm:block"></div>
                        <div class="text-center lg:text-left">
                            <div class="text-3xl font-extrabold text-white">5 000 F</div>
                            <div class="text-white/50 text-xs mt-0.5 uppercase tracking-widest">A partir de</div>
                        </div>
                    </div>
                </div>

                {{-- Right: App mockup + notification --}}
                <div class="relative flex justify-center lg:justify-end fade-up visible delay-2">

                    {{-- Glow behind mockup --}}
                    <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                        <div class="w-80 h-80 bg-blue-500 rounded-full blur-3xl opacity-20"></div>
                    </div>

                    {{-- Phone mockup --}}
                    @php $heroImage = \App\Models\SystemSetting::get('hero_image', ''); @endphp
                    @if($heroImage && \Illuminate\Support\Facades\Storage::disk('public')->exists($heroImage))
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($heroImage) }}"
                             alt="MenuPro - Interface de commande" width="360" height="680"
                             class="relative w-full max-w-[300px] sm:max-w-[340px] rounded-3xl shadow-2xl border border-white/10"
                             loading="eager">
                    @else
                        <div class="relative w-[290px] sm:w-[330px]">
                            {{-- Phone frame --}}
                            <div class="bg-neutral-900 rounded-[2.5rem] p-1.5 shadow-2xl border border-white/10">
                                <div class="rounded-[2.2rem] overflow-hidden bg-white">
                                    {{-- Status bar --}}
                                    <div class="bg-blue-600 text-white px-5 pt-8 pb-5">
                                        <div class="flex items-center justify-between mb-3">
                                            <div class="flex items-center gap-2.5">
                                                <div class="w-9 h-9 bg-white/20 rounded-xl flex items-center justify-center">
                                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                                </div>
                                                <div>
                                                    <div class="font-bold text-sm">Le Maquis d'Abidjan</div>
                                                    <div class="text-[11px] text-white/70 flex items-center gap-1"><span class="w-1.5 h-1.5 bg-green-300 rounded-full"></span> Ouvert maintenant</div>
                                                </div>
                                            </div>
                                            <div class="text-right">
                                                <div class="text-xs text-white/70">Livraison</div>
                                                <div class="text-xs font-bold">30 min</div>
                                            </div>
                                        </div>
                                    </div>
                                    {{-- Categories --}}
                                    <div class="flex gap-2 px-3 py-2.5 border-b border-neutral-100 overflow-x-auto">
                                        <span class="px-3 py-1.5 bg-blue-600 text-white text-[11px] font-bold rounded-full whitespace-nowrap">🔥 Populaires</span>
                                        <span class="px-3 py-1.5 bg-neutral-100 text-neutral-500 text-[11px] rounded-full whitespace-nowrap">Plats</span>
                                        <span class="px-3 py-1.5 bg-neutral-100 text-neutral-500 text-[11px] rounded-full whitespace-nowrap">Boissons</span>
                                    </div>
                                    {{-- Dishes --}}
                                    <div class="p-3 space-y-2.5">
                                        @foreach([
                                            ['Poulet Braisé', 'Avec alloco et sauce', '5 500 F', 'from-amber-200 to-orange-200', '⭐4.9'],
                                            ['Attieke Poisson', 'Légumes & sauce tomate', '4 500 F', 'from-yellow-200 to-amber-200', '⭐4.7'],
                                            ['Jus de Bissap', 'Frais & naturel', '1 500 F', 'from-rose-200 to-pink-200', '⭐4.8'],
                                        ] as $d)
                                        <div class="bg-white rounded-xl p-2.5 flex gap-2.5 shadow-sm border border-neutral-100">
                                            <div class="w-12 h-12 bg-gradient-to-br {{ $d[3] }} rounded-lg shrink-0 flex items-center justify-center text-xl">{{ explode(' ', $d[0])[0] === 'Poulet' ? '🍗' : (explode(' ', $d[0])[0] === 'Attieke' ? '🍲' : '🥤') }}</div>
                                            <div class="flex-1 min-w-0">
                                                <div class="font-bold text-xs text-neutral-800 truncate">{{ $d[0] }}</div>
                                                <div class="text-[10px] text-neutral-400 truncate">{{ $d[1] }}</div>
                                                <div class="flex items-center justify-between mt-1.5">
                                                    <span class="text-blue-600 font-bold text-xs">{{ $d[2] }}</span>
                                                    <span class="text-[10px] text-amber-500">{{ $d[4] }}</span>
                                                </div>
                                            </div>
                                            <button class="w-6 h-6 bg-blue-600 text-white rounded-lg flex items-center justify-center text-base font-bold self-center">+</button>
                                        </div>
                                        @endforeach
                                    </div>
                                    {{-- Cart bar --}}
                                    <div class="px-3 pb-4">
                                        <div class="bg-blue-600 text-white rounded-xl px-4 py-2.5 flex items-center justify-between shadow-lg">
                                            <div class="flex items-center gap-2">
                                                <span class="w-6 h-6 bg-white/20 rounded-lg flex items-center justify-center text-[11px] font-bold">3</span>
                                                <span class="text-xs font-medium">Voir mon panier</span>
                                            </div>
                                            <span class="font-bold text-sm">11 500 F</span>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            {{-- Floating notification --}}
                            <div class="float absolute -top-4 -right-8 bg-white rounded-2xl shadow-xl border border-neutral-100 p-3 flex items-center gap-3 w-52">
                                <div class="w-9 h-9 bg-emerald-100 rounded-xl flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                </div>
                                <div>
                                    <div class="text-xs font-bold text-neutral-800">Paiement reçu !</div>
                                    <div class="text-[11px] text-neutral-500">Wave · 5 500 F</div>
                                </div>
                            </div>

                            {{-- Floating order card --}}
                            <div class="float absolute -bottom-4 -left-8 bg-white rounded-2xl shadow-xl border border-neutral-100 p-3 flex items-center gap-3 w-48" style="animation-delay:.8s">
                                <div class="w-9 h-9 bg-blue-100 rounded-xl flex items-center justify-center shrink-0">
                                    <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                </div>
                                <div>
                                    <div class="text-xs font-bold text-neutral-800">Nouvelle commande</div>
                                    <div class="text-[11px] text-neutral-500">Table 4 · 2 plats</div>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Bottom wave --}}
        <div class="absolute bottom-0 left-0 right-0">
            <svg viewBox="0 0 1440 60" fill="none" xmlns="http://www.w3.org/2000/svg" preserveAspectRatio="none" class="w-full h-14">
                <path d="M0 60L1440 60L1440 20C1200 50 960 10 720 30C480 50 240 10 0 20L0 60Z" fill="white"/>
            </svg>
        </div>
    </section>

    {{-- ══════════════════════════════════════
         TICKER — Paiements acceptés
    ══════════════════════════════════════ --}}
    <section class="py-8 bg-white border-b border-neutral-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col sm:flex-row items-center justify-center gap-6 sm:gap-12">
                <p class="text-xs font-bold text-neutral-400 uppercase tracking-[0.2em] whitespace-nowrap shrink-0">Paiements acceptés</p>
                <div class="flex items-center gap-6 sm:gap-10 flex-wrap justify-center">
                    @foreach([
                        ['wave.png', 'Wave', 'bg-sky-50'],
                        ['orange-money.png', 'Orange Money', 'bg-orange-50'],
                        ['mtn-momo.png', 'MTN MoMo', 'bg-yellow-50'],
                        ['moov-money.png', 'Moov Money', 'bg-blue-50'],
                    ] as $p)
                    <div class="flex flex-col items-center gap-2">
                        <div class="w-14 h-14 {{ $p[2] }} rounded-2xl flex items-center justify-center border border-neutral-100">
                            <img src="{{ asset('images/payments/'.$p[0]) }}" alt="{{ $p[1] }}" class="h-9 w-9 object-contain" loading="lazy">
                        </div>
                        <span class="text-xs text-neutral-500 font-medium">{{ $p[1] }}</span>
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
    </section>

    {{-- ══════════════════════════════════════
         FEATURES — Bento Grid amélioré
    ══════════════════════════════════════ --}}
    <section class="py-24 sm:py-32 bg-white relative overflow-hidden">
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_80%_50%_at_50%_100%,rgba(59,130,246,.05),transparent)]"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center max-w-2xl mx-auto mb-16 fade-up">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-blue-50 border border-blue-100 rounded-full text-blue-600 text-xs font-bold tracking-wider uppercase mb-5">Fonctionnalités</span>
                <h2 class="text-4xl sm:text-5xl font-extrabold text-neutral-900 leading-tight tracking-tight">
                    Tout pour gérer votre<br>restaurant en ligne
                </h2>
                <p class="text-neutral-500 text-lg mt-4 leading-relaxed">
                    Une plateforme unique, pensée pour les restaurateurs africains.
                </p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-6 gap-4 sm:gap-5">

                {{-- Large: Commandes temps réel --}}
                <div class="md:col-span-4 bg-gradient-to-br from-blue-600 to-indigo-700 rounded-3xl p-8 text-white relative overflow-hidden group hover:shadow-2xl hover:shadow-blue-500/20 transition-all duration-300 fade-up">
                    <div class="absolute -right-10 -top-10 w-48 h-48 bg-white/5 rounded-full"></div>
                    <div class="absolute right-8 bottom-8 w-32 h-32 bg-white/5 rounded-full"></div>
                    <div class="relative z-10">
                        <div class="w-14 h-14 bg-white/20 backdrop-blur rounded-2xl flex items-center justify-center mb-5">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        </div>
                        <h3 class="text-2xl font-extrabold mb-3">Commandes en temps réel</h3>
                        <p class="text-white/75 leading-relaxed max-w-lg">Vos clients commandent depuis leur téléphone. QR code sur les tables ou lien WhatsApp. Notification instantanée sur votre dashboard avec alerte sonore.</p>
                    </div>
                </div>

                {{-- Paiement Mobile Money --}}
                <div class="md:col-span-2 bg-emerald-50 border border-emerald-100 rounded-3xl p-6 hover:border-emerald-300 card-glow transition-all duration-300 group fade-up delay-1">
                    <div class="w-12 h-12 bg-emerald-500 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <h3 class="text-lg font-extrabold text-neutral-900 mb-2">Paiement Mobile Money</h3>
                    <p class="text-neutral-600 text-sm leading-relaxed">Wave, Orange Money, MTN, Moov. L'argent arrive directement sur votre compte.</p>
                </div>

                {{-- Zéro commission --}}
                <div class="md:col-span-2 bg-amber-50 border border-amber-100 rounded-3xl p-6 hover:border-amber-300 card-glow transition-all duration-300 group fade-up">
                    <div class="w-12 h-12 bg-amber-500 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <h3 class="text-lg font-extrabold text-neutral-900 mb-2">0% de commission</h3>
                    <p class="text-neutral-600 text-sm leading-relaxed">Forfait fixe à partir de 5 000 F/mois. Pas de 20% par commande.</p>
                </div>

                {{-- Gestion complète --}}
                <div class="md:col-span-2 bg-violet-50 border border-violet-100 rounded-3xl p-6 hover:border-violet-300 card-glow transition-all duration-300 group fade-up delay-1">
                    <div class="w-12 h-12 bg-violet-500 rounded-2xl flex items-center justify-center mb-4 group-hover:scale-110 transition-transform">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/></svg>
                    </div>
                    <h3 class="text-lg font-extrabold text-neutral-900 mb-2">Gestion complète</h3>
                    <p class="text-neutral-600 text-sm leading-relaxed">Menu, stock, commandes, équipe, statistiques. Un seul dashboard pour tout piloter.</p>
                </div>

                {{-- Livraison --}}
                <div class="md:col-span-3 bg-gradient-to-br from-rose-50 to-pink-50 border border-rose-100 rounded-3xl p-7 hover:border-rose-300 card-glow transition-all duration-300 group fade-up">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-rose-500 rounded-2xl flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-extrabold text-neutral-900 mb-2">Livraison intégrée</h3>
                            <p class="text-neutral-600 text-sm leading-relaxed">Gérez vos livreurs, suivez en temps réel, vos clients voient leur commande avancer. Comme Glovo mais avec VOS livreurs.</p>
                        </div>
                    </div>
                </div>

                {{-- Hotel --}}
                <div class="md:col-span-3 bg-gradient-to-br from-sky-50 to-blue-50 border border-sky-100 rounded-3xl p-7 hover:border-sky-300 card-glow transition-all duration-300 group fade-up delay-1">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-sky-500 rounded-2xl flex items-center justify-center shrink-0 group-hover:scale-110 transition-transform">
                            <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                        </div>
                        <div>
                            <h3 class="text-lg font-extrabold text-neutral-900 mb-2">Mode Hôtel — QR par chambre</h3>
                            <p class="text-neutral-600 text-sm leading-relaxed">Chaque chambre a son propre QR code. Le client commande depuis son lit, la voix IA annonce les commandes en cuisine.</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ══════════════════════════════════════
         STATS — Impact chiffré
    ══════════════════════════════════════ --}}
    <section class="py-20 bg-neutral-950 relative overflow-hidden">
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_60%_50%_at_50%_0%,rgba(59,130,246,.12),transparent)]"></div>
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="grid grid-cols-2 lg:grid-cols-4 gap-8 text-center fade-up">
                @foreach([
                    ['15 min', 'Pour être en ligne', 'text-blue-400'],
                    ['0%', 'Commission par commande', 'text-emerald-400'],
                    ['4', 'Moyens de paiement', 'text-amber-400'],
                    ['24/7', 'Commandes sans interruption', 'text-violet-400'],
                ] as $s)
                <div>
                    <div class="text-5xl sm:text-6xl font-extrabold {{ $s[2] }} tabular-nums leading-none">{{ $s[0] }}</div>
                    <div class="text-neutral-500 text-sm mt-3 leading-snug">{{ $s[1] }}</div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ══════════════════════════════════════
         HOW IT WORKS — 3 étapes
    ══════════════════════════════════════ --}}
    <section id="how-it-works" class="py-24 sm:py-32 bg-white relative overflow-hidden">
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_60%_40%_at_0%_50%,rgba(59,130,246,.06),transparent)]"></div>
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center max-w-2xl mx-auto mb-16 fade-up">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 border border-emerald-100 rounded-full text-emerald-600 text-xs font-bold tracking-wider uppercase mb-5">Comment ça marche</span>
                <h2 class="text-4xl sm:text-5xl font-extrabold text-neutral-900 leading-tight tracking-tight">
                    Du zéro au premier client<br>en 15 minutes
                </h2>
                <p class="text-neutral-500 text-lg mt-4">
                    Pas besoin d'être développeur. Juste votre restaurant et l'envie de recevoir des commandes.
                </p>
            </div>

            <div class="grid md:grid-cols-3 gap-6 sm:gap-8 fade-up">
                @foreach([
                    ['01', 'Créez votre compte', 'Nom du restaurant, email, téléphone. En 2 minutes, votre espace est prêt.', '~2 min', 'bg-blue-600', 'text-blue-100', 'bg-blue-50 border-blue-100'],
                    ['02', 'Ajoutez votre menu', 'Photos, prix, catégories. Configurez vos horaires et moyens de paiement.', '~10 min', 'bg-violet-600', 'text-violet-100', 'bg-violet-50 border-violet-100'],
                    ['03', 'Recevez des commandes', 'Partagez votre lien ou QR code. Les commandes arrivent en temps réel.', 'Immédiat', 'bg-emerald-600', 'text-emerald-100', 'bg-emerald-50 border-emerald-100'],
                ] as $i => $step)
                <div class="relative bg-white rounded-3xl p-8 border border-neutral-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                    <div class="flex items-center justify-between mb-6">
                        <span class="text-5xl font-black text-neutral-100">{{ $step[0] }}</span>
                        <span class="text-xs font-bold px-3 py-1.5 rounded-full {{ $step[2] }} border {{ explode(' ', $step[7] ?? $step[6])[1] ?? 'border-neutral-100' }}" style="background: {{ str_contains($step[6], 'blue') ? '#eff6ff' : (str_contains($step[6], 'violet') ? '#f5f3ff' : '#f0fdf4') }}; color: {{ str_contains($step[6], 'blue') ? '#2563eb' : (str_contains($step[6], 'violet') ? '#7c3aed' : '#16a34a') }}">{{ $step[3] }}</span>
                    </div>
                    <div class="w-12 h-12 {{ $step[4] }} rounded-2xl flex items-center justify-center mb-5 shadow-lg">
                        <span class="text-white font-black text-lg">{{ $i + 1 }}</span>
                    </div>
                    <h3 class="text-xl font-extrabold text-neutral-900 mb-3">{{ $step[1] }}</h3>
                    <p class="text-neutral-500 leading-relaxed text-sm">{{ $step[2] }}</p>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ══════════════════════════════════════
         QR CODE — Parcours client
    ══════════════════════════════════════ --}}
    <section class="py-24 sm:py-32 bg-neutral-50 relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center max-w-2xl mx-auto mb-16 fade-up">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-indigo-50 border border-indigo-100 rounded-full text-indigo-600 text-xs font-bold tracking-wider uppercase mb-5">QR Code</span>
                <h2 class="text-4xl sm:text-5xl font-extrabold text-neutral-900 leading-tight tracking-tight">
                    Du scan au paiement<br>en 2 minutes
                </h2>
                <p class="text-neutral-500 text-lg mt-4">
                    Vos clients scannent, commandent et paient — sans télécharger d'application.
                </p>
            </div>

            @php
                $qrSteps = [
                    ['Scanner le QR', 'Le client scanne le QR code sur la table avec son téléphone. Aucune app à télécharger.', '🔲', 'bg-indigo-500'],
                    ['Choisir son plat', 'Il parcourt votre menu complet avec photos, prix et catégories.', '🍽️', 'bg-blue-500'],
                    ['Commande en cuisine', 'Transmission instantanée à votre écran cuisine. Zéro erreur, zéro papier.', '⚡', 'bg-amber-500'],
                    ['Payer par Mobile Money', 'Wave, Orange Money, MTN ou Moov. L\'argent arrive directement sur votre compte.', '💳', 'bg-emerald-500'],
                ];
            @endphp

            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5 fade-up">
                @foreach($qrSteps as $i => $step)
                <div class="bg-white rounded-3xl p-6 border border-neutral-100 shadow-sm hover:shadow-lg hover:-translate-y-1 transition-all duration-300">
                    <div class="flex items-center gap-3 mb-5">
                        <div class="w-10 h-10 {{ $step[3] }} rounded-2xl flex items-center justify-center text-white font-black text-sm shadow-md">{{ $i + 1 }}</div>
                        <div class="h-px flex-1 bg-neutral-100"></div>
                        <span class="text-2xl">{{ $step[2] }}</span>
                    </div>
                    <h3 class="font-extrabold text-neutral-900 mb-2">{{ $step[0] }}</h3>
                    <p class="text-sm text-neutral-500 leading-relaxed">{{ $step[1] }}</p>
                    @if($i === 3)
                    <div class="mt-4 flex items-center gap-2 flex-wrap">
                        @foreach(['wave.png','orange-money.png','mtn-momo.png','moov-money.png'] as $logo)
                        <img src="{{ asset('images/payments/'.$logo) }}" class="h-7 w-7 rounded-lg object-contain" alt="">
                        @endforeach
                    </div>
                    @endif
                </div>
                @endforeach
            </div>

            <div class="mt-10 text-center fade-up">
                <a href="{{ url('/supports-qr') }}" class="inline-flex items-center gap-2 px-6 py-3.5 bg-indigo-600 text-white font-bold rounded-2xl hover:bg-indigo-700 shadow-lg shadow-indigo-500/25 transition-all hover:-translate-y-0.5">
                    Commander vos supports QR imprimés
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </a>
                <p class="text-sm text-neutral-400 mt-2">Supports rigides ou autocollants — livrés à votre restaurant</p>
            </div>
        </div>
    </section>

    {{-- ══════════════════════════════════════
         VIDEO DEMO
    ══════════════════════════════════════ --}}
    @if(!empty($videos))
    <section class="py-24 sm:py-32 bg-white relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-16 fade-up">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-red-50 border border-red-100 rounded-full text-red-600 text-xs font-bold tracking-wider uppercase mb-5">Vidéo</span>
                <h2 class="text-4xl sm:text-5xl font-extrabold text-neutral-900 leading-tight tracking-tight">
                    Voyez MenuPro en action
                </h2>
                <p class="text-neutral-500 text-lg mt-4">
                    Découvrez comment nos restaurateurs utilisent MenuPro au quotidien.
                </p>
            </div>
            <div class="grid md:grid-cols-{{ count($videos) > 1 ? '2' : '1' }} gap-8 max-w-5xl mx-auto fade-up">
                @foreach($videos as $video)
                <div>
                    <div class="relative aspect-video bg-neutral-900 rounded-3xl overflow-hidden shadow-2xl border border-neutral-200">
                        <iframe src="{{ $video['url'] }}" title="{{ $video['title'] }}" class="w-full h-full" frameborder="0" allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen loading="lazy"></iframe>
                    </div>
                    <div class="mt-4 text-center">
                        <h3 class="font-bold text-neutral-900">{{ $video['title'] }}</h3>
                        @if($video['description'])<p class="text-sm text-neutral-500 mt-1">{{ $video['description'] }}</p>@endif
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    {{-- ══════════════════════════════════════
         TÉMOIGNAGES
    ══════════════════════════════════════ --}}
    <section class="py-24 sm:py-32 bg-neutral-50 relative overflow-hidden">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-16 fade-up">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-50 border border-emerald-100 rounded-full text-emerald-600 text-xs font-bold tracking-wider uppercase mb-5">Témoignages</span>
                <h2 class="text-4xl sm:text-5xl font-extrabold text-neutral-900 leading-tight tracking-tight">
                    Ils utilisent MenuPro<br>au quotidien
                </h2>
                <p class="text-neutral-500 text-lg mt-4">
                    Des restaurateurs comme vous qui ont fait le pas.
                </p>
            </div>

            @php
                $testimonials = \App\Models\SystemSetting::get('home_testimonials', [
                    ['name'=>'Awa Koné','role'=>'Propriétaire, Maquis Chez Awa','city'=>'Daloa','text'=>'Depuis que j\'utilise MenuPro, mes clients commandent directement avec leur téléphone. Plus besoin d\'attendre le serveur. Mon chiffre d\'affaires a augmenté de 30%.','avatar'=>'','stars'=>5],
                    ['name'=>'Kouamé Jean','role'=>'Gérant, Restaurant Le Délice','city'=>'Abidjan','text'=>'Le QR code sur les tables a tout changé. Les clients scannent, commandent et paient par Wave. Je reçois l\'argent immédiatement.','avatar'=>'','stars'=>5],
                    ['name'=>'Marie Touré','role'=>'Fondatrice, Saveurs d\'Afrique','city'=>'Bouaké','text'=>'À 15 000 F par mois, c\'est le meilleur investissement pour mon restaurant. Pas de commission, pas de surprise.','avatar'=>'','stars'=>5],
                ]);
            @endphp

            <div class="grid md:grid-cols-3 gap-6 fade-up">
                @foreach($testimonials as $t)
                <div class="bg-white rounded-3xl p-7 border border-neutral-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col">
                    <div class="flex gap-1 mb-5">
                        @for($i=0;$i<($t['stars']??5);$i++)
                        <svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                        @endfor
                    </div>
                    <p class="text-neutral-700 leading-relaxed mb-6 flex-1 text-sm">"{{ $t['text'] }}"</p>
                    <div class="flex items-center gap-3 pt-5 border-t border-neutral-100">
                        @if(!empty($t['avatar']) && file_exists(public_path($t['avatar'])))
                            <img src="{{ asset($t['avatar']) }}" alt="{{ $t['name'] }}" class="w-10 h-10 rounded-full object-cover border border-neutral-100">
                        @else
                            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-sm">{{ strtoupper(substr($t['name'],0,1)) }}</div>
                        @endif
                        <div>
                            <div class="font-bold text-sm text-neutral-900">{{ $t['name'] }}</div>
                            <div class="text-xs text-neutral-400">{{ $t['role'] }} — {{ $t['city'] }}</div>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ══════════════════════════════════════
         RESTAURANTS QUI NOUS FONT CONFIANCE
    ══════════════════════════════════════ --}}
    <section class="py-16 bg-white border-y border-neutral-100">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center mb-10 fade-up">
                <h3 class="text-lg font-bold text-neutral-900 mb-1">Ils nous font confiance</h3>
                <p class="text-sm text-neutral-400">Restaurants et maquis actifs sur MenuPro</p>
            </div>
            @php
                $trustedRestaurants = \App\Models\Restaurant::where('status', \App\Enums\RestaurantStatus::ACTIVE)
                    ->whereNotNull('logo_path')->where('logo_path','!=','')->latest()->take(12)->get(['name','slug','logo_path','city']);
                if($trustedRestaurants->isEmpty()) {
                    $trustedRestaurants = \App\Models\Restaurant::where('status', \App\Enums\RestaurantStatus::ACTIVE)->latest()->take(6)->get(['name','slug','city']);
                }
            @endphp
            <div class="grid grid-cols-3 sm:grid-cols-4 md:grid-cols-6 gap-4 fade-up">
                @foreach($trustedRestaurants as $resto)
                <a href="{{ route('r.menu', $resto->slug) }}" target="_blank" class="group flex flex-col items-center justify-center gap-2 p-4 bg-neutral-50 rounded-2xl border border-neutral-100 hover:border-blue-200 hover:bg-blue-50/50 hover:shadow-md transition-all">
                    @if($resto->logo_path)
                        <img src="{{ Storage::url($resto->logo_path) }}" alt="{{ $resto->name }}" class="w-12 h-12 rounded-xl object-cover border border-neutral-100 group-hover:scale-110 transition-transform" loading="lazy">
                    @else
                        <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-bold text-lg group-hover:scale-110 transition-transform">{{ strtoupper(substr($resto->name,0,1)) }}</div>
                    @endif
                    <span class="text-[11px] font-semibold text-neutral-700 truncate max-w-[80px] text-center">{{ $resto->name }}</span>
                </a>
                @endforeach
            </div>
        </div>
    </section>

    {{-- ══════════════════════════════════════
         TARIFS
    ══════════════════════════════════════ --}}
    <section id="pricing" class="py-24 sm:py-32 bg-neutral-50 relative overflow-hidden">
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_60%_40%_at_50%_100%,rgba(99,102,241,.07),transparent)]"></div>
        <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
            <div class="text-center max-w-2xl mx-auto mb-16 fade-up">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-amber-50 border border-amber-100 rounded-full text-amber-600 text-xs font-bold tracking-wider uppercase mb-5">Tarifs</span>
                <h2 class="text-4xl sm:text-5xl font-extrabold text-neutral-900 leading-tight tracking-tight">
                    Un prix simple,<br>pas de surprise
                </h2>
                <p class="text-neutral-500 text-lg mt-4">
                    Du stand de rue au grand restaurant. Pas de commission par commande.
                </p>
            </div>

            <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5 fade-up">
                {{-- Stand --}}
                <div class="bg-white rounded-3xl p-7 border border-orange-200 hover:border-orange-400 hover:shadow-xl transition-all duration-300 flex flex-col">
                    <div class="text-xs font-extrabold text-orange-500 uppercase tracking-widest mb-4">Stand</div>
                    <div class="flex items-baseline gap-1 mb-1">
                        <span class="text-4xl font-black text-neutral-900">5 000</span>
                        <span class="text-neutral-400 text-sm">F/mois</span>
                    </div>
                    <p class="text-sm text-neutral-400 mb-6">Vendeurs de rue, stands, tacos, jus...</p>
                    <ul class="space-y-3 mb-8 flex-1">
                        @foreach(['15 plats','100 commandes/mois','QR code personnalisé','Wave & Orange Money','Sans ordinateur requis'] as $f)
                        <li class="flex items-center gap-2.5 text-sm text-neutral-700">
                            <svg class="w-4 h-4 text-orange-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>{{ $f }}
                        </li>
                        @endforeach
                    </ul>
                    <a href="{{ route('register') }}?plan=stand" class="block w-full text-center py-3.5 px-6 rounded-2xl font-bold text-sm bg-orange-50 text-orange-700 hover:bg-orange-100 border border-orange-200 transition-all">Essai gratuit 7j</a>
                </div>

                {{-- Essentiel --}}
                <div class="bg-white rounded-3xl p-7 border border-neutral-200 hover:border-neutral-300 hover:shadow-xl transition-all duration-300 flex flex-col">
                    <div class="text-xs font-extrabold text-neutral-400 uppercase tracking-widest mb-4">Essentiel</div>
                    <div class="flex items-baseline gap-1 mb-1">
                        <span class="text-4xl font-black text-neutral-900">15 000</span>
                        <span class="text-neutral-400 text-sm">F/mois</span>
                    </div>
                    <p class="text-sm text-neutral-400 mb-6">Pour les petits maquis qui démarrent</p>
                    <ul class="space-y-3 mb-8 flex-1">
                        @foreach(['25 plats, 8 catégories','200 commandes/mois','Mobile Money + QR codes','Support WhatsApp'] as $f)
                        <li class="flex items-center gap-2.5 text-sm text-neutral-700">
                            <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>{{ $f }}
                        </li>
                        @endforeach
                    </ul>
                    <a href="{{ route('register') }}?plan=essentiel" class="block w-full text-center py-3.5 px-6 rounded-2xl font-bold text-sm bg-neutral-100 text-neutral-800 hover:bg-neutral-200 border border-neutral-200 transition-all">Essai gratuit 7j</a>
                </div>

                {{-- Pro (featured) --}}
                <div class="bg-blue-600 rounded-3xl p-7 border-2 border-blue-500 shadow-2xl shadow-blue-500/30 relative hover:shadow-3xl hover:scale-[1.02] transition-all duration-300 flex flex-col">
                    <div class="absolute -top-4 left-1/2 -translate-x-1/2 bg-white text-blue-600 text-xs font-extrabold px-4 py-1.5 rounded-full shadow-lg border border-blue-100">⭐ Populaire</div>
                    <div class="text-xs font-extrabold text-blue-200 uppercase tracking-widest mb-4">Pro</div>
                    <div class="flex items-baseline gap-1 mb-1">
                        <span class="text-4xl font-black text-white">25 000</span>
                        <span class="text-blue-200 text-sm">F/mois</span>
                    </div>
                    <p class="text-sm text-blue-200 mb-6">Stock, livraison et analytics inclus</p>
                    <ul class="space-y-3 mb-8 flex-1">
                        @foreach(['80 plats, 3 employés','1 000 commandes/mois','Gestion stock complète','Livraison intégrée','Analytics & rapports'] as $f)
                        <li class="flex items-center gap-2.5 text-sm text-white">
                            <svg class="w-4 h-4 text-blue-200 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>{{ $f }}
                        </li>
                        @endforeach
                    </ul>
                    <a href="{{ route('register') }}?plan=pro" class="block w-full text-center py-3.5 px-6 rounded-2xl font-extrabold text-sm bg-white text-blue-700 hover:bg-blue-50 shadow-lg transition-all">Essai gratuit 7j</a>
                </div>

                {{-- Gold --}}
                <div class="bg-gradient-to-br from-neutral-900 to-indigo-950 rounded-3xl p-7 border border-indigo-500/30 shadow-xl relative hover:shadow-2xl hover:scale-[1.01] transition-all duration-300 flex flex-col">
                    <div class="text-xs font-extrabold text-indigo-300 uppercase tracking-widest mb-4">Gold ✨</div>
                    <div class="flex items-baseline gap-1 mb-1">
                        <span class="text-4xl font-black text-transparent bg-clip-text bg-gradient-to-r from-indigo-300 to-purple-300">85 000</span>
                        <span class="text-neutral-400 text-sm">F/mois</span>
                    </div>
                    <p class="text-sm text-neutral-400 mb-6">Multi-espaces pour complexes VIP & hotels</p>
                    <ul class="space-y-3 mb-8 flex-1">
                        @foreach(['Multi-espaces illimités','Serveurs avec PIN dédié','Rapports par espace','QR chambres hotel','Formation personnalisée'] as $f)
                        <li class="flex items-center gap-2.5 text-sm text-neutral-300">
                            <svg class="w-4 h-4 text-indigo-400 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>{{ $f }}
                        </li>
                        @endforeach
                    </ul>
                    <a href="{{ route('register') }}?plan=gold" class="block w-full text-center py-3.5 px-6 rounded-2xl font-extrabold text-sm bg-gradient-to-r from-indigo-500 to-purple-600 text-white hover:from-indigo-600 hover:to-purple-700 shadow-lg transition-all">Essai gratuit 7j</a>
                </div>
            </div>

            <p class="text-center text-neutral-400 text-sm mt-8">
                7 jours d'essai gratuit. Sans engagement. Sans carte bancaire.
                <a href="{{ route('pricing') }}" class="text-blue-600 hover:text-blue-700 font-semibold ml-1">Voir la comparaison détaillée →</a>
            </p>
        </div>
    </section>

    {{-- ══════════════════════════════════════
         CTA FINAL
    ══════════════════════════════════════ --}}
    <section class="py-24 sm:py-32 bg-neutral-950 relative overflow-hidden">
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_60%_60%_at_50%_50%,rgba(59,130,246,.15),transparent)]"></div>
        <div class="absolute inset-0 bg-[radial-gradient(ellipse_40%_40%_at_20%_80%,rgba(99,102,241,.1),transparent)]"></div>
        <div class="relative z-10 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
            <span class="inline-flex items-center gap-2 px-4 py-1.5 bg-white/10 border border-white/20 rounded-full text-white/70 text-sm font-medium mb-8">
                <span class="relative flex h-2 w-2"><span class="ping-slow absolute inline-flex h-full w-full rounded-full bg-emerald-400"></span><span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-400"></span></span>
                {{ $stats['raw']['restaurants'] ?? '100' }}+ restaurants actifs
            </span>

            <h2 class="text-4xl sm:text-5xl lg:text-6xl font-extrabold text-white leading-tight tracking-tight">
                Prêt à digitaliser<br>votre restaurant ?
            </h2>
            <p class="text-lg sm:text-xl text-neutral-400 mt-6 max-w-xl mx-auto leading-relaxed">
                Rejoignez les restaurateurs ivoiriens qui reçoivent des commandes en ligne et sont payés directement sur leur Mobile Money.
            </p>

            <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
                <a href="{{ route('register') }}" class="group w-full sm:w-auto inline-flex items-center justify-center gap-2 px-10 py-4 bg-white text-neutral-900 font-extrabold rounded-2xl hover:bg-blue-50 shadow-2xl shadow-white/10 transition-all duration-200 hover:-translate-y-0.5 text-base">
                    Créer mon restaurant — C'est gratuit
                    <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </a>
                <a href="{{ route('r.menu', ['slug' => 'demo']) }}" target="_blank" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-4 text-white font-semibold rounded-2xl border border-white/20 hover:bg-white/10 transition-all text-base">
                    <svg class="w-5 h-5 text-blue-400" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                    Voir la démo
                </a>
            </div>

            <div class="mt-10 flex flex-wrap items-center justify-center gap-6 sm:gap-10 text-sm text-neutral-600">
                @foreach(['Configuration en 15 min', 'Support WhatsApp', 'A partir de 5 000 F/mois', 'Sans engagement'] as $t)
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    {{ $t }}
                </span>
                @endforeach
            </div>
        </div>
    </section>

    @push('scripts')
    <script>
        function counter(target) {
            return {
                count: 0, target: target, displayCount: '0',
                startCount() {
                    const steps = 40, duration = 1600;
                    const sv = this.target / steps, sd = duration / steps;
                    const iv = setInterval(() => {
                        this.count += sv;
                        if (this.count >= this.target) { this.count = this.target; clearInterval(iv); }
                        this.displayCount = this.count >= 1000 ? Math.round(this.count/1000)+'K+' : Math.round(this.count).toString();
                    }, sd);
                }
            }
        }
        const io = new IntersectionObserver(entries => {
            entries.forEach(e => { if (e.isIntersecting) { e.target.classList.add('visible'); io.unobserve(e.target); } });
        }, { threshold: 0.1 });
        document.querySelectorAll('.fade-up').forEach(el => io.observe(el));
    </script>
    @endpush
</x-layouts.public>
