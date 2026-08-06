<x-layouts.public title="Accueil" description="MenuPro : digitalisez votre restaurant, menu en ligne, commandes et paiement Mobile Money. Solution SaaS pour restaurants en Cote d'Ivoire.">
@push('head')
<script type="application/ld+json">{"@@context":"https://schema.org","@@type":"SoftwareApplication","name":"MenuPro","applicationCategory":"BusinessApplication","operatingSystem":"Web","url":"{{ url('/') }}","description":"Plateforme SaaS de commande en ligne pour restaurants en Cote d'Ivoire.","offers":{"@@type":"Offer","price":"5000","priceCurrency":"XOF"}}</script>
<style>
/* Animations */
.fu{opacity:0;transform:translateY(24px);transition:opacity .55s cubic-bezier(.22,1,.36,1),transform .55s cubic-bezier(.22,1,.36,1)}
.fu.in{opacity:1;transform:none}
.fu.d1{transition-delay:.1s}.fu.d2{transition-delay:.18s}.fu.d3{transition-delay:.26s}.fu.d4{transition-delay:.34s}

/* Gradient text */
.gt{background:linear-gradient(120deg,#D45E0C,#ef8a4d);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.gt-light{background:linear-gradient(120deg,#f6b285,#fad2b5);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}

/* Hero bg — blanc pur */
.hero-bg{background:#ffffff !important}
/* Blobs orange décoratifs */
.hero-blob{position:absolute;bottom:-20%;left:-10%;width:65%;padding-bottom:65%;border-radius:50% 50% 60% 40% / 40% 50% 50% 60%;background:rgba(212,94,12,.07);pointer-events:none;z-index:0}
.hero-blob2{position:absolute;top:-15%;right:-8%;width:45%;padding-bottom:45%;border-radius:40% 60% 50% 50% / 50% 40% 60% 50%;background:rgba(212,94,12,.05);pointer-events:none;z-index:0}
/* Forme orange vif bas-droite pour contraste */
.hero-shape{position:absolute;bottom:0;right:0;width:40%;height:55%;background:linear-gradient(135deg,rgba(212,94,12,.12),rgba(212,94,12,.04));border-radius:80% 0 0 0;pointer-events:none;z-index:0}
/* Badge rotatif */
@keyframes spin-slow{from{transform:rotate(0deg)}to{transform:rotate(360deg)}}
.spin-slow{animation:spin-slow 12s linear infinite}

/* Ticker */
@keyframes tk{from{transform:translateX(0)}to{transform:translateX(-50%)}}
.tk-wrap{overflow:hidden;-webkit-mask-image:linear-gradient(90deg,transparent,black 8%,black 92%,transparent);mask-image:linear-gradient(90deg,transparent,black 8%,black 92%,transparent)}
.tk-track{display:flex;width:max-content;animation:tk 30s linear infinite;gap:0}
.tk-track:hover{animation-play-state:paused}

/* Float */
@keyframes fl{0%,100%{transform:translateY(0)}50%{transform:translateY(-8px)}}
.fl{animation:fl 4s ease-in-out infinite}
.fl2{animation:fl 5.5s ease-in-out 1s infinite}

/* Pulse */
@keyframes ps{0%,100%{transform:scale(1);opacity:.4}50%{transform:scale(1.8);opacity:0}}
.ps{animation:ps 2.5s ease-in-out infinite}

/* Orange glow */
.glow-orange{box-shadow:0 0 40px rgba(212,94,12,.25),0 0 80px rgba(212,94,12,.1)}

/* Compare */
.yes{color:#22c55e;font-weight:800}
.no{color:#ef4444}
</style>
@endpush

{{-- ══════════════════════════════════════
     1. HERO — Fond blanc + accent orange
     Inspiré du design Food Delivery ref
══════════════════════════════════════ --}}
<section class="hero-bg relative overflow-hidden" style="min-height:100vh;background:#ffffff !important;background-color:#ffffff !important">

    {{-- Formes décoratives oranges --}}
    <div class="hero-blob"></div>
    <div class="hero-blob2"></div>
    <div class="hero-shape"></div>

    {{-- Grain texture overlay --}}
    <div class="pointer-events-none absolute inset-0 opacity-[0.015]" style="background-image:url('data:image/svg+xml,%3Csvg viewBox=%220 0 200 200%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Cfilter id=%22n%22%3E%3CfeTurbulence type=%22fractalNoise%22 baseFrequency=%220.9%22 numOctaves=%224%22 stitchTiles=%22stitch%22/%3E%3C/filter%3E%3Crect width=%22100%25%22 height=%22100%25%22 filter=%22url(%23n)%22/%3E%3C/svg%3E')"></div>

    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 w-full">
        <div class="grid lg:grid-cols-2 gap-0 lg:gap-8 items-center min-h-screen py-20 lg:py-0">

            {{-- ─── Left: Copy ─── --}}
            <div class="text-center lg:text-left order-2 lg:order-1 pb-10 lg:pb-0">

                {{-- Badge #Top Rated --}}
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full text-sm font-bold mb-8 border" style="background:rgba(212,94,12,.08);border-color:rgba(212,94,12,.2);color:#D45E0C">
                    <span class="relative flex h-2 w-2 shrink-0">
                        <span class="ps absolute inline-flex h-full w-full rounded-full" style="background:#D45E0C"></span>
                        <span class="relative inline-flex h-2 w-2 rounded-full" style="background:#D45E0C"></span>
                    </span>
                    #1 Solution Restaurants en Côte d'Ivoire
                </div>

                {{-- Headline --}}
                <h1 class="text-5xl sm:text-6xl lg:text-[4rem] xl:text-[4.5rem] font-black text-neutral-900 leading-[1.05] tracking-tight">
                    La meilleure<br>
                    <span class="gt italic">expérience</span><br>
                    <span class="text-neutral-900">restaurant !</span>
                </h1>

                <p class="mt-5 text-lg text-neutral-500 max-w-lg mx-auto lg:mx-0 leading-relaxed">
                    Digitalisez votre restaurant en <strong class="text-neutral-900">15 minutes</strong>. Menu en ligne, QR codes, commandes temps réel, paiements <strong class="text-neutral-900">Wave · Orange · MTN · Moov</strong> — argent directement sur votre compte.
                </p>

                {{-- CTAs --}}
                <div class="mt-8 flex flex-col sm:flex-row items-stretch sm:items-center gap-3 justify-center lg:justify-start">
                    <a href="{{ route('register') }}" class="group inline-flex items-center justify-center gap-2 px-8 py-4 font-black rounded-2xl text-white text-base transition-all hover:-translate-y-0.5 glow-orange" style="background:#D45E0C">
                        Créer mon restaurant — Gratuit
                        <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    </a>
                    <a href="{{ route('r.menu', ['slug' => 'demo']) }}" target="_blank" class="inline-flex items-center justify-center gap-2 px-8 py-4 font-semibold text-neutral-700 rounded-2xl border-2 border-neutral-200 hover:border-primary-300 hover:text-primary-600 transition-all text-base bg-white">
                        <svg class="w-5 h-5" style="color:#D45E0C" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                        Voir la démo live
                    </a>
                </div>

                {{-- Social proof --}}
                <div class="mt-10 flex flex-col sm:flex-row items-center lg:items-start gap-6 justify-center lg:justify-start">
                    {{-- Avatars + note --}}
                    <div class="flex items-center gap-3">
                        <div class="flex -space-x-2.5">
                            @foreach(['A','K','M','F','S'] as $l)
                            <div class="w-9 h-9 rounded-full border-2 border-white flex items-center justify-center text-white text-xs font-black shadow-sm" style="background:{{ ['#D45E0C','#22c55e','#3b82f6','#a855f7','#f59e0b'][$loop->index] }}">{{ $l }}</div>
                            @endforeach
                        </div>
                        <div class="text-left">
                            <div class="flex items-center gap-1">
                                @for($i=0;$i<5;$i++)<svg class="w-3.5 h-3.5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>@endfor
                            </div>
                            <div class="text-xs text-neutral-600 font-semibold">{{ $stats['restaurants'] }}+ restaurants actifs</div>
                        </div>
                    </div>

                    {{-- Stats --}}
                    <div class="hidden sm:flex items-center gap-6">
                        <div class="w-px h-10 bg-neutral-200"></div>
                        <div class="text-center">
                            <div class="text-2xl font-black text-neutral-900" x-data="counter({{ $stats['raw']['orders'] }})" x-intersect.once="startCount()"><span x-text="displayCount"></span></div>
                            <div class="text-xs text-neutral-500">Commandes traitées</div>
                        </div>
                        <div class="w-px h-10 bg-neutral-200"></div>
                        <div class="text-center">
                            <div class="text-2xl font-black text-neutral-900">5 000 F</div>
                            <div class="text-xs text-neutral-500">À partir de / mois</div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- ─── Right: Phone mockup centré ─── --}}
            <div class="relative flex justify-center items-center order-1 lg:order-2 pt-8 lg:pt-0">

                {{-- Cercle orange décoratif derrière le téléphone --}}
                <div class="absolute w-[340px] h-[340px] sm:w-[420px] sm:h-[420px] rounded-full pointer-events-none" style="background:radial-gradient(circle,rgba(212,94,12,.12) 0%,rgba(212,94,12,.04) 60%,transparent 80%)"></div>

                {{-- Badge rotatif "Quick · MenuPro ·" --}}
                <div class="absolute top-4 right-4 lg:top-8 lg:right-0 w-24 h-24 pointer-events-none">
                    <svg class="w-full h-full spin-slow" viewBox="0 0 100 100">
                        <defs><path id="circle" d="M 50,50 m -35,0 a 35,35 0 1,1 70,0 a 35,35 0 1,1 -70,0"/></defs>
                        <text class="text-[11px]" fill="#D45E0C" font-size="11" font-weight="700" letter-spacing="3">
                            <textPath href="#circle">MENUPRO · RESTAURANT · COMMANDE ·</textPath>
                        </text>
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="w-10 h-10 rounded-full flex items-center justify-center" style="background:#D45E0C">
                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/></svg>
                        </div>
                    </div>
                </div>

                @php $heroImage = \App\Models\SystemSetting::get('hero_image',''); @endphp
                @if($heroImage && \Illuminate\Support\Facades\Storage::disk('public')->exists($heroImage))
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($heroImage) }}" alt="MenuPro" class="relative w-full max-w-[280px] sm:max-w-[320px] rounded-3xl shadow-2xl" loading="eager" width="320" height="640">
                @else
                <div class="relative w-[265px] sm:w-[295px]">

                    {{-- Téléphone --}}
                    <div class="bg-neutral-900 rounded-[2.8rem] p-1.5 shadow-[0_30px_60px_rgba(0,0,0,0.2)] ring-1 ring-neutral-800">
                        <div class="rounded-[2.5rem] overflow-hidden bg-white">

                            {{-- Status bar --}}
                            <div class="px-5 pt-3 pb-1 flex items-center justify-between bg-white">
                                <span class="text-[10px] font-black text-neutral-800">9:41</span>
                                <div class="flex items-center gap-1">
                                    <svg class="w-3 h-3 text-neutral-700" fill="currentColor" viewBox="0 0 24 24"><path d="M1.5 8.5C4.5 5.5 7.9 4 12 4s7.5 1.5 10.5 4.5L21 11c-2.5-2.5-5.3-3.8-9-3.8S6.5 8.5 4 11L1.5 8.5z"/><path d="M4.5 11.5C7 9 9.4 8 12 8s5 1 7.5 3.5l-1.5 1.5c-2-2-4-3-6-3s-4 1-6 3l-1.5-1.5z"/><circle cx="12" cy="17" r="2"/></svg>
                                    <div class="flex gap-0.5 items-end">
                                        <div class="w-0.5 h-1 bg-neutral-800 rounded-sm"></div>
                                        <div class="w-0.5 h-1.5 bg-neutral-800 rounded-sm"></div>
                                        <div class="w-0.5 h-2 bg-neutral-800 rounded-sm"></div>
                                        <div class="w-0.5 h-2.5 bg-neutral-800 rounded-sm"></div>
                                    </div>
                                </div>
                            </div>

                            {{-- App header --}}
                            <div class="px-4 pt-2 pb-4 text-white" style="background:#D45E0C">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-10 h-10 bg-white/20 rounded-2xl flex items-center justify-center">
                                            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                        </div>
                                        <div>
                                            <div class="font-black text-sm leading-tight">Maquis Chez Awa</div>
                                            <div class="flex items-center gap-1 text-[11px] text-white/80">
                                                <span class="w-1.5 h-1.5 rounded-full bg-green-300"></span>
                                                Ouvert · 08h–22h
                                            </div>
                                        </div>
                                    </div>
                                    <div class="w-9 h-9 bg-white/15 rounded-xl flex items-center justify-center">
                                        <svg class="w-4.5 h-4.5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                                    </div>
                                </div>
                                {{-- Search in header --}}
                                <div class="mt-3 bg-white/15 backdrop-blur rounded-xl px-3 py-2 flex items-center gap-2">
                                    <svg class="w-3.5 h-3.5 text-white/70" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                    <span class="text-white/70 text-[11px]">Rechercher un plat, restaurant...</span>
                                </div>
                            </div>

                            {{-- Banner promo --}}
                            <div class="mx-3 mt-3 rounded-2xl overflow-hidden" style="background:linear-gradient(120deg,#161616,#2d1608)">
                                <div class="px-4 py-3 flex items-center justify-between">
                                    <div>
                                        <div class="text-[10px] text-white/60 font-semibold uppercase tracking-wide">Offre du jour</div>
                                        <div class="text-white font-black text-sm leading-tight">Commandez & économisez</div>
                                        <div class="text-white/50 text-[10px]">Livraison offerte ce soir</div>
                                    </div>
                                    <div class="w-12 h-12 rounded-xl flex items-center justify-center" style="background:#D45E0C">
                                        <span class="text-white font-black text-lg">30%</span>
                                    </div>
                                </div>
                            </div>

                            {{-- Categories --}}
                            <div class="flex gap-2 px-3 pt-3 pb-1 overflow-x-hidden">
                                @foreach([['Populaires',true],['Plats',false],['Boissons',false],['Desserts',false]] as $c)
                                <span class="px-3 py-1.5 text-[10px] font-black rounded-full whitespace-nowrap shrink-0 {{ $c[1] ? 'text-white' : 'bg-neutral-100 text-neutral-500' }}" @if($c[1]) style="background:#D45E0C" @endif>{{ $c[0] }}</span>
                                @endforeach
                            </div>

                            {{-- Items --}}
                            <div class="px-3 pt-2 pb-1 space-y-2">
                                @foreach([
                                    ['Poulet Braisé','Alloco + sauce tomate','5 500 F','#fef3c7','⭐ 4.9'],
                                    ['Attieké Poisson','Légumes frais + sauce','4 500 F','#dcfce7','⭐ 4.7'],
                                ] as $d)
                                <div class="bg-white rounded-2xl p-2.5 flex gap-2.5 shadow-sm border border-neutral-100">
                                    <div class="w-12 h-12 rounded-xl shrink-0 flex items-center justify-center border border-neutral-200" style="background:{{ $d[4+1] ?? $d[3] }}">
                                        <svg class="w-5 h-5 text-neutral-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <div class="font-black text-xs text-neutral-800 leading-tight">{{ $d[0] }}</div>
                                        <div class="text-[10px] text-neutral-400 truncate">{{ $d[1] }}</div>
                                        <div class="flex items-center justify-between mt-1">
                                            <span class="font-black text-xs" style="color:#D45E0C">{{ $d[2] }}</span>
                                            <span class="text-[10px] text-amber-500">{{ $d[4] }}</span>
                                        </div>
                                    </div>
                                    <button class="w-7 h-7 rounded-xl flex items-center justify-center text-white font-black text-sm self-center shrink-0" style="background:#D45E0C">+</button>
                                </div>
                                @endforeach
                            </div>

                            {{-- Cart button --}}
                            <div class="px-3 pb-4 pt-2">
                                <div class="text-white rounded-2xl px-4 py-3 flex items-center justify-between" style="background:linear-gradient(135deg,#b84e0a,#D45E0C)">
                                    <div class="flex items-center gap-2">
                                        <span class="w-6 h-6 bg-white/20 rounded-lg flex items-center justify-center text-[11px] font-black">2</span>
                                        <span class="text-xs font-bold">Voir mon panier</span>
                                    </div>
                                    <span class="font-black text-sm">10 000 F</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Notif paiement Wave (flottante haut droite) --}}
                    <div class="fl absolute -top-4 -right-12 bg-white rounded-2xl shadow-xl border border-neutral-100 px-3 py-2.5 flex items-center gap-2.5" style="width:200px">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0" style="background:#e0f2fe">
                            <img src="{{ asset('images/payments/wave.png') }}" class="w-5 h-5 object-contain" alt="Wave">
                        </div>
                        <div>
                            <div class="text-xs font-black text-neutral-800">Paiement reçu ✓</div>
                            <div class="text-[10px] text-neutral-500">Wave · 5 500 F</div>
                        </div>
                    </div>

                    {{-- Notif nouvelle commande (flottante bas gauche) --}}
                    <div class="fl2 absolute -bottom-4 -left-12 bg-white rounded-2xl shadow-xl border border-neutral-100 px-3 py-2.5 flex items-center gap-2.5" style="width:185px">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0" style="background:rgba(212,94,12,.1)">
                            <svg class="w-5 h-5" style="color:#D45E0C" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        </div>
                        <div>
                            <div class="text-xs font-black text-neutral-800">Nouvelle commande</div>
                            <div class="text-[10px] text-neutral-500">Table 7 · Poulet braisé</div>
                        </div>
                    </div>

                    {{-- Tag flottant --}}
                    <div class="absolute top-1/3 -left-8 bg-neutral-900 text-white text-[11px] font-black px-3 py-1.5 rounded-full shadow-lg">#Délicieux</div>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Séparateur bas vers section blanche --}}
    <div class="absolute bottom-0 left-0 right-0">
        <svg viewBox="0 0 1440 40" preserveAspectRatio="none" class="w-full h-10" fill="#f5f5f5"><path d="M0,40 C480,0 960,30 1440,0 L1440,40Z"/></svg>
    </div>
</section>


{{-- ══════════════════════════════════════
     2. TICKER — Catégories populaires
══════════════════════════════════════ --}}
<section class="py-5 bg-white border-b border-neutral-100 overflow-hidden">
    <div class="tk-wrap">
        <div class="tk-track">
            @foreach(array_fill(0, 2, ['Poulet Braisé','Attieké Poisson','Jus Naturels','Pizza','Burgers','Maquis','Hôtel','Livraison','Paninis','Tacos','Riz Sauce','Café Restaurant']) as $items)
            @foreach($items as $item)
            <div class="flex items-center gap-3 px-6">
                <span class="text-neutral-200 font-black text-lg">+</span>
                <span class="text-sm font-bold text-neutral-500 whitespace-nowrap">{{ $item }}</span>
            </div>
            @endforeach
            @endforeach
        </div>
    </div>
</section>


{{-- ══════════════════════════════════════
     3. PAIEMENTS
══════════════════════════════════════ --}}
<section class="py-14 bg-white">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <p class="text-center text-xs font-black text-neutral-400 uppercase tracking-[0.25em] mb-8 fu">Paiements Mobile Money acceptés</p>
        <div class="flex items-center justify-center gap-4 sm:gap-8 flex-wrap fu">
            @foreach([['wave.png','Wave','#e0f2fe'],['orange-money.png','Orange Money','#fff7ed'],['mtn-momo.png','MTN MoMo','#fefce8'],['moov-money.png','Moov Money','#eff6ff']] as $i => $p)
            <div class="flex flex-col items-center gap-2 group fu" style="transition-delay:{{ $i*0.08 }}s">
                <div class="w-16 h-16 rounded-2xl flex items-center justify-center border border-neutral-100 hover:shadow-lg hover:-translate-y-0.5 transition-all" style="background:{{ $p[2] }}">
                    <img src="{{ asset('images/payments/'.$p[0]) }}" alt="{{ $p[1] }}" class="h-10 w-10 object-contain" loading="lazy">
                </div>
                <span class="text-xs font-semibold text-neutral-500">{{ $p[1] }}</span>
            </div>
            @endforeach
        </div>
    </div>
</section>


{{-- ══════════════════════════════════════
     4. STATS CHOC — Fond noir + orange
══════════════════════════════════════ --}}
<section class="py-16 bg-neutral-950">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 text-center fu">
            @foreach([
                ['15 min','Pour être en ligne'],
                ['0%','Commission / commande'],
                ['4','Moyens de paiement'],
                ['24/7','Commandes reçues'],
            ] as $s)
            <div>
                <div class="text-5xl sm:text-6xl font-black leading-none tabular-nums gt">{{ $s[0] }}</div>
                <div class="text-neutral-500 text-sm mt-3 leading-tight">{{ $s[1] }}</div>
            </div>
            @endforeach
        </div>
    </div>
</section>


{{-- ══════════════════════════════════════
     5. POUR QUI ?
══════════════════════════════════════ --}}
<section class="py-24 bg-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12 fu">
            <span class="text-xs font-black uppercase tracking-widest" style="color:#D45E0C">Pour qui ?</span>
            <h2 class="text-4xl sm:text-5xl font-black text-neutral-900 mt-3 leading-tight">De la vendeuse de panini<br>au grand hôtel</h2>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 fu">
            @foreach([
                ['stand','Stand & Street food','Vendeurs de rue, paninis, tacos, jus. MenuPro sur votre téléphone.','border-orange-200 bg-orange-50','text-orange-600','5 000 F/mois'],
                ['restaurant','Maquis & Restaurant','Tables, QR codes, commandes cuisine, alertes sonores.','border-neutral-200 bg-white','text-neutral-600','15 000 F/mois'],
                ['hotel','Hôtel & Résidence','QR par chambre, room service, voix IA pour le personnel.','border-primary-200','text-primary-600','Gold'],
                ['delivery','Livraison intégrée','Vos livreurs, suivi temps réel. 0% de commission.','border-secondary-200 bg-secondary-50','text-secondary-600','Pro'],
            ] as $who)
            <div class="rounded-3xl p-6 border-2 {{ $who[3] }} hover:-translate-y-1 hover:shadow-xl transition-all duration-300">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center mb-4 {{ $who[4] }}" style="background:currentColor;background:rgba(0,0,0,0.06)">
                    @if($who[0]==='stand')
                    <svg class="w-6 h-6 {{ $who[4] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    @elseif($who[0]==='restaurant')
                    <svg class="w-6 h-6 {{ $who[4] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 3h18v18H3zM3 9h18M9 21V9"/></svg>
                    @elseif($who[0]==='hotel')
                    <svg class="w-6 h-6 {{ $who[4] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    @else
                    <svg class="w-6 h-6 {{ $who[4] }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                    @endif
                </div>
                <h3 class="font-black text-neutral-900 text-lg mb-2">{{ $who[1] }}</h3>
                <p class="text-neutral-500 text-sm leading-relaxed mb-5">{{ $who[2] }}</p>
                <span class="inline-block text-xs font-black {{ $who[4] }} border border-current/20 bg-white px-3 py-1.5 rounded-full">À partir de {{ $who[5] }}</span>
            </div>
            @endforeach
        </div>
    </div>
</section>


{{-- ══════════════════════════════════════
     6. FEATURES — Fond noir style design ref
══════════════════════════════════════ --}}
<section class="py-24 sm:py-28 bg-neutral-950">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14 fu">
            <span class="text-xs font-black uppercase tracking-widest" style="color:#D45E0C">— Pourquoi choisir MenuPro</span>
            <h2 class="text-4xl sm:text-5xl font-black text-white mt-3 leading-tight">
                Profitez d'<span class="gt">avantages exclusifs</span><br>avec MenuPro
            </h2>
        </div>

        <div class="grid md:grid-cols-3 gap-5 fu">
            @foreach([
                ['orders','Commandes en direct','Vos clients commandent depuis leur téléphone. QR code sur les tables, lien WhatsApp. Alerte sonore instantanée, écran cuisine dédié.'],
                ['payment','Paiement Mobile Money','Wave, Orange Money, MTN, Moov. L\'argent arrive directement sur votre compte sans délai ni intermédiaire.'],
                ['analytics','Analytics & Rapports','Bilan journalier par heure de caisse, CA espèces vs mobile money, plats les plus vendus, taux d\'annulation.'],
                ['stock','Gestion de stock','Alertes de rupture automatiques, mouvements d\'inventaire, gestion des ingrédients. Plus jamais à court.'],
                ['hotel','Mode Hôtel','QR par chambre, room service, voix IA qui annonce les commandes. Appel addition, appel ménage.'],
                ['delivery','Livraison intégrée','Gérez vos propres livreurs avec suivi temps réel. Vos clients voient leur commande avancer. Zéro commission.'],
            ] as $i => $f)
            <div class="bg-neutral-900 rounded-3xl p-7 border border-neutral-800 hover:border-primary-500/40 hover:-translate-y-1 hover:shadow-2xl transition-all duration-300 group fu" style="transition-delay:{{ $i*0.08 }}s">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center mb-5 group-hover:scale-110 transition-transform" style="background:rgba(212,94,12,.15);border:1px solid rgba(212,94,12,.25)">
                    @if($f[0]==='orders')
                    <svg class="w-7 h-7" style="color:#D45E0C" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                    @elseif($f[0]==='payment')
                    <svg class="w-7 h-7" style="color:#D45E0C" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    @elseif($f[0]==='analytics')
                    <svg class="w-7 h-7" style="color:#D45E0C" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    @elseif($f[0]==='stock')
                    <svg class="w-7 h-7" style="color:#D45E0C" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    @elseif($f[0]==='hotel')
                    <svg class="w-7 h-7" style="color:#D45E0C" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    @else
                    <svg class="w-7 h-7" style="color:#D45E0C" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>
                    @endif
                </div>
                <h3 class="font-black text-white text-lg mb-3">{{ $f[1] }}</h3>
                <p class="text-neutral-500 text-sm leading-relaxed">{{ $f[2] }}</p>
                <div class="mt-5 flex items-center gap-1.5 font-bold text-sm" style="color:#D45E0C">
                    En savoir plus
                    <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>


{{-- ══════════════════════════════════════
     7. COMPARAISON
══════════════════════════════════════ --}}
<section class="py-24 bg-white">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14 fu">
            <span class="text-xs font-black uppercase tracking-widest" style="color:#D45E0C">Pourquoi MenuPro ?</span>
            <h2 class="text-4xl sm:text-5xl font-black text-neutral-900 mt-3 leading-tight">
                Glovo prend 30%.<br><span class="gt">Nous, zéro.</span>
            </h2>
        </div>
        <div class="rounded-3xl border-2 border-neutral-100 overflow-hidden shadow-xl fu">
            <table class="w-full">
                <thead>
                    <tr class="border-b-2 border-neutral-100">
                        <th class="text-left py-5 px-6 text-sm font-bold text-neutral-500">Critère</th>
                        <th class="py-5 px-6 text-center text-sm font-black w-1/4" style="background:rgba(212,94,12,.06);color:#D45E0C">MenuPro ✓</th>
                        <th class="py-5 px-6 text-center text-sm font-bold text-neutral-400 w-1/4">Glovo / Yango</th>
                        <th class="py-5 px-6 text-center text-sm font-bold text-neutral-400 w-1/4">WhatsApp seul</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach([
                        ['Commission par commande','0%','20 – 30%','0% (pertes cachées)'],
                        ['Paiement mobile money','✓ 4 opérateurs','✓ partiel','✗ manuel'],
                        ['Vos propres clients','✓ vous les gardez','✗ base Glovo','✓'],
                        ['Analytics & rapports','✓ complet','✓ partiel','✗ aucun'],
                        ['Gestion stock','✓','✗','✗'],
                        ['Livraison propres livreurs','✓','✗ leurs livreurs','✗'],
                        ['Mode hôtel QR chambre','✓','✗','✗'],
                        ['Prix mensuel','5 000 F fixe','0 F + 20-30%/cmd','0 F + désorganisation'],
                    ] as $row)
                    <tr class="border-b border-neutral-100 hover:bg-neutral-50 transition-colors">
                        <td class="py-4 px-6 text-sm text-neutral-700 font-medium">{{ $row[0] }}</td>
                        <td class="py-4 px-6 text-center text-sm font-black yes" style="background:rgba(212,94,12,.04)">{{ $row[1] }}</td>
                        <td class="py-4 px-6 text-center text-sm {{ str_starts_with($row[2],'✗') || str_contains($row[2],'20') ? 'no' : 'text-neutral-500' }}">{{ $row[2] }}</td>
                        <td class="py-4 px-6 text-center text-sm {{ str_starts_with($row[3],'✗') || str_contains($row[3],'désorg') || str_contains($row[3],'pertes') ? 'no' : 'text-neutral-500' }}">{{ $row[3] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        <div class="text-center mt-10 fu d2">
            <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-8 py-4 text-white font-black rounded-2xl transition-all hover:-translate-y-0.5 hover:shadow-xl text-lg" style="background:#D45E0C">
                Passer à MenuPro maintenant
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
        </div>
    </div>
</section>


{{-- ══════════════════════════════════════
     8. HOW IT WORKS
══════════════════════════════════════ --}}
<section id="how-it-works" class="py-24 bg-neutral-50">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-xl mx-auto mb-16 fu">
            <span class="text-xs font-black uppercase tracking-widest" style="color:#D45E0C">Comment ça marche</span>
            <h2 class="text-4xl sm:text-5xl font-black text-neutral-900 mt-3 leading-tight">En ligne en 15 minutes<br>chrono</h2>
        </div>
        <div class="grid md:grid-cols-3 gap-6 fu">
            @foreach([
                ['01','Créez votre compte','Nom, email, téléphone. Votre espace est prêt en 2 minutes.','~2 min'],
                ['02','Ajoutez votre menu','Photos, prix, catégories. Configurez horaires et paiements Mobile Money.','~10 min'],
                ['03','Recevez des commandes','Partagez votre lien ou imprimez votre QR code. Commandes et paiements en direct.','Immédiat'],
            ] as $step)
            <div class="bg-white rounded-3xl p-8 border border-neutral-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="flex items-start justify-between mb-6">
                    <span class="text-7xl font-black text-neutral-100 leading-none">{{ $step[0] }}</span>
                    <span class="text-xs font-black px-3 py-1.5 rounded-full text-white" style="background:#D45E0C">{{ $step[3] }}</span>
                </div>
                <h3 class="text-xl font-black text-neutral-900 mb-3">{{ $step[1] }}</h3>
                <p class="text-neutral-500 text-sm leading-relaxed">{{ $step[2] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>


{{-- ══════════════════════════════════════
     9. VIDÉO
══════════════════════════════════════ --}}
@if(!empty($videos))
<section class="py-24 bg-white">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14 fu">
            <span class="text-xs font-black uppercase tracking-widest" style="color:#D45E0C">Vidéo</span>
            <h2 class="text-4xl sm:text-5xl font-black text-neutral-900 mt-3">Voyez MenuPro en action</h2>
        </div>
        <div class="grid md:grid-cols-{{ count($videos)>1?'2':'1' }} gap-8 fu">
            @foreach($videos as $v)
            <div>
                <div class="aspect-video bg-neutral-900 rounded-3xl overflow-hidden shadow-2xl">
                    <iframe src="{{ $v['url'] }}" title="{{ $v['title'] }}" class="w-full h-full" frameborder="0" allow="accelerometer;autoplay;clipboard-write;encrypted-media;gyroscope;picture-in-picture" allowfullscreen loading="lazy"></iframe>
                </div>
                @if($v['title'])<p class="mt-3 text-center font-bold text-neutral-700">{{ $v['title'] }}</p>@endif
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif


{{-- ══════════════════════════════════════
     10. TÉMOIGNAGES
══════════════════════════════════════ --}}
<section class="py-24 bg-neutral-50">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-xl mx-auto mb-16 fu">
            <span class="text-xs font-black uppercase tracking-widest" style="color:#D45E0C">Témoignages</span>
            <h2 class="text-4xl sm:text-5xl font-black text-neutral-900 mt-3 leading-tight">Ils en parlent<br>mieux que nous</h2>
        </div>
        @php
            $testimonials = \App\Models\SystemSetting::get('home_testimonials', [
                ['name'=>'Awa Koné','role'=>'Propriétaire, Maquis Chez Awa','city'=>'Daloa','text'=>'Depuis MenuPro, mes clients commandent directement depuis leur téléphone. Mon chiffre d\'affaires a augmenté de 30% en 2 mois.','avatar'=>'','stars'=>5],
                ['name'=>'Kouamé Jean','role'=>'Gérant, Restaurant Le Délice','city'=>'Abidjan','text'=>'Le QR code a tout changé. Les clients scannent, commandent et paient par Wave. Je reçois l\'argent immédiatement.','avatar'=>'','stars'=>5],
                ['name'=>'Marie Touré','role'=>'Fondatrice, Saveurs d\'Afrique','city'=>'Bouaké','text'=>'À partir de 5 000 F/mois, c\'est le meilleur investissement. Zéro commission, zéro surprise. Je recommande à 100%.','avatar'=>'','stars'=>5],
            ]);
        @endphp
        <div class="grid md:grid-cols-3 gap-6 fu">
            @foreach($testimonials as $t)
            <div class="bg-white rounded-3xl p-7 border border-neutral-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col">
                <div class="flex gap-1 mb-5">
                    @for($i=0;$i<($t['stars']??5);$i++)<svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>@endfor
                </div>
                <p class="text-neutral-600 leading-relaxed flex-1 text-sm">"{{ $t['text'] }}"</p>
                <div class="flex items-center gap-3 mt-6 pt-5 border-t border-neutral-100">
                    @if(!empty($t['avatar']) && file_exists(public_path($t['avatar'])))<img src="{{ asset($t['avatar']) }}" alt="{{ $t['name'] }}" class="w-10 h-10 rounded-full object-cover">
                    @else<div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-black" style="background:#D45E0C">{{ strtoupper(substr($t['name'],0,1)) }}</div>@endif
                    <div>
                        <div class="font-black text-sm text-neutral-900">{{ $t['name'] }}</div>
                        <div class="text-xs text-neutral-400">{{ $t['role'] }} · {{ $t['city'] }}</div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>


{{-- ══════════════════════════════════════
     11. RESTAURANTS
══════════════════════════════════════ --}}
<section class="py-16 bg-white border-y border-neutral-100">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <p class="text-center text-xs font-black text-neutral-400 uppercase tracking-[0.2em] mb-10 fu">Ils nous font confiance</p>
        @php
            $trs = \App\Models\Restaurant::where('status',\App\Enums\RestaurantStatus::ACTIVE)->whereNotNull('logo_path')->where('logo_path','!=','')->latest()->take(12)->get(['name','slug','logo_path','city']);
            if($trs->isEmpty()) $trs=\App\Models\Restaurant::where('status',\App\Enums\RestaurantStatus::ACTIVE)->latest()->take(8)->get(['name','slug','city']);
        @endphp
        <div class="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 gap-3 fu">
            @foreach($trs as $r)
            <a href="{{ route('r.menu',$r->slug) }}" target="_blank" class="group flex flex-col items-center gap-1.5 p-3 rounded-2xl bg-neutral-50 border border-neutral-100 hover:border-primary-200 hover:bg-primary-50/50 hover:shadow-md transition-all">
                @if(isset($r->logo_path) && $r->logo_path)<img src="{{ Storage::url($r->logo_path) }}" alt="{{ $r->name }}" class="w-10 h-10 rounded-xl object-cover group-hover:scale-110 transition-transform" loading="lazy">
                @else<div class="w-10 h-10 rounded-xl flex items-center justify-center text-white font-black text-base group-hover:scale-110 transition-transform" style="background:#D45E0C">{{ strtoupper(substr($r->name,0,1)) }}</div>@endif
                <span class="text-[10px] font-semibold text-neutral-600 truncate w-full text-center">{{ Str::limit($r->name,10) }}</span>
            </a>
            @endforeach
        </div>
    </div>
</section>


{{-- ══════════════════════════════════════
     12. TARIFS
══════════════════════════════════════ --}}
<section id="pricing" class="py-24 sm:py-28 bg-neutral-50">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-xl mx-auto mb-16 fu">
            <span class="text-xs font-black uppercase tracking-widest" style="color:#D45E0C">Tarifs</span>
            <h2 class="text-4xl sm:text-5xl font-black text-neutral-900 mt-3 leading-tight">Simple. Transparent.<br>Zéro commission.</h2>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5 fu">
            {{-- Stand --}}
            <div class="bg-white rounded-3xl p-7 border-2 border-neutral-200 hover:border-primary-300 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 flex flex-col">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center mb-4" style="background:rgba(212,94,12,.1)">
                    <svg class="w-6 h-6" style="color:#D45E0C" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <div class="text-xs font-black uppercase tracking-widest mb-3" style="color:#D45E0C">Stand</div>
                <div class="flex items-baseline gap-1 mb-1"><span class="text-4xl font-black text-neutral-900">5 000</span><span class="text-neutral-400 text-sm">F/mois</span></div>
                <p class="text-sm text-neutral-400 mb-6">Vendeurs de rue et stands</p>
                <ul class="space-y-2.5 flex-1 mb-8">@foreach(['15 plats','100 cmd/mois','QR code inclus','Wave & Orange Money','Sans PC requis'] as $f)<li class="flex items-center gap-2 text-sm text-neutral-700"><svg class="w-4 h-4 shrink-0" style="color:#D45E0C" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>{{ $f }}</li>@endforeach</ul>
                <a href="{{ route('register') }}?plan=stand" class="block w-full text-center py-3.5 rounded-2xl font-black text-sm transition-all border-2" style="border-color:#D45E0C;color:#D45E0C;background:rgba(212,94,12,.06)" onmouseover="this.style.background='rgba(212,94,12,.12)'" onmouseout="this.style.background='rgba(212,94,12,.06)'">Essai 7j gratuit</a>
            </div>
            {{-- Essentiel --}}
            <div class="bg-white rounded-3xl p-7 border-2 border-neutral-200 hover:border-primary-300 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 flex flex-col">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center mb-4 bg-neutral-100">
                    <svg class="w-6 h-6 text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M3 3h18v18H3zM3 9h18M9 21V9"/></svg>
                </div>
                <div class="text-xs font-black text-neutral-400 uppercase tracking-widest mb-3">Essentiel</div>
                <div class="flex items-baseline gap-1 mb-1"><span class="text-4xl font-black text-neutral-900">15 000</span><span class="text-neutral-400 text-sm">F/mois</span></div>
                <p class="text-sm text-neutral-400 mb-6">Maquis et petits restaurants</p>
                <ul class="space-y-2.5 flex-1 mb-8">@foreach(['25 plats, 8 catégories','200 cmd/mois','Mobile Money + QR','Support WhatsApp'] as $f)<li class="flex items-center gap-2 text-sm text-neutral-700"><svg class="w-4 h-4 text-secondary-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>{{ $f }}</li>@endforeach</ul>
                <a href="{{ route('register') }}?plan=essentiel" class="block w-full text-center py-3.5 rounded-2xl font-black text-sm bg-neutral-100 text-neutral-800 hover:bg-neutral-200 transition-all">Essai 7j gratuit</a>
            </div>
            {{-- Pro (featured) --}}
            <div class="rounded-3xl p-7 border-2 shadow-2xl relative hover:-translate-y-1 transition-all duration-300 flex flex-col text-white" style="background:#161616;border-color:#D45E0C;box-shadow:0 0 40px rgba(212,94,12,.2)">
                <div class="absolute -top-4 left-1/2 -translate-x-1/2 text-white text-xs font-black px-5 py-1.5 rounded-full shadow-lg" style="background:#D45E0C">⭐ Populaire</div>
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center mb-4" style="background:rgba(212,94,12,.2)">
                    <svg class="w-6 h-6" style="color:#D45E0C" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <div class="text-xs font-black uppercase tracking-widest mb-3" style="color:#D45E0C">Pro</div>
                <div class="flex items-baseline gap-1 mb-1"><span class="text-4xl font-black text-white">25 000</span><span class="text-neutral-400 text-sm">F/mois</span></div>
                <p class="text-sm text-neutral-400 mb-6">Stock, livraison, analytics</p>
                <ul class="space-y-2.5 flex-1 mb-8">@foreach(['80 plats, 3 employés','1 000 cmd/mois','Stock complet','Livraison intégrée','Analytics & rapports'] as $f)<li class="flex items-center gap-2 text-sm text-white"><svg class="w-4 h-4 shrink-0" style="color:#D45E0C" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>{{ $f }}</li>@endforeach</ul>
                <a href="{{ route('register') }}?plan=pro" class="block w-full text-center py-3.5 rounded-2xl font-black text-sm text-white transition-all hover:opacity-90" style="background:#D45E0C">Essai 7j gratuit</a>
            </div>
            {{-- Gold --}}
            <div class="rounded-3xl p-7 border-2 border-neutral-800 shadow-xl hover:-translate-y-0.5 hover:shadow-2xl transition-all duration-300 flex flex-col" style="background:#0f172a">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center mb-4" style="background:rgba(246,178,133,.15)">
                    <svg class="w-6 h-6" style="color:#f6b285" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                </div>
                <div class="text-xs font-black uppercase tracking-widest mb-3" style="color:#f6b285">Gold</div>
                <div class="flex items-baseline gap-1 mb-1"><span class="text-4xl font-black gt-light">85 000</span><span class="text-neutral-500 text-sm">F/mois</span></div>
                <p class="text-sm text-neutral-500 mb-6">Multi-espaces, hôtels, VIP</p>
                <ul class="space-y-2.5 flex-1 mb-8">@foreach(['Multi-espaces illimités','PIN serveurs','Rapports par espace','QR chambres hôtel','Formation personnalisée'] as $f)<li class="flex items-center gap-2 text-sm text-neutral-300"><svg class="w-4 h-4 shrink-0" style="color:#f6b285" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>{{ $f }}</li>@endforeach</ul>
                <a href="{{ route('register') }}?plan=gold" class="block w-full text-center py-3.5 rounded-2xl font-black text-sm text-neutral-900 hover:opacity-90 transition-all" style="background:linear-gradient(135deg,#f6b285,#D45E0C)">Essai 7j gratuit</a>
            </div>
        </div>
        <p class="text-center text-neutral-400 text-sm mt-8">Sans engagement · Sans carte bancaire · <a href="{{ route('pricing') }}" class="font-black hover:underline" style="color:#D45E0C">Comparer tous les plans →</a></p>
    </div>
</section>


{{-- ══════════════════════════════════════
     13. FAQ
══════════════════════════════════════ --}}
<section class="py-24 bg-white">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14 fu">
            <span class="text-xs font-black uppercase tracking-widest" style="color:#D45E0C">FAQ</span>
            <h2 class="text-4xl sm:text-5xl font-black text-neutral-900 mt-3">Questions fréquentes</h2>
        </div>
        @php
            $faqs = [
                ['Mes clients doivent-ils télécharger une application ?', 'Non. Vos clients scannent le QR code ou cliquent sur votre lien. Tout fonctionne directement dans le navigateur de leur téléphone, sans téléchargement.'],
                ['Comment fonctionne le paiement Mobile Money ?', 'Vos clients paient depuis leur app Wave, Orange Money, MTN ou Moov. L\'argent est envoyé directement sur votre compte — sans délai, sans intermédiaire.'],
                ['Combien de temps pour être en ligne ?', 'Moins de 15 minutes. Créez votre compte, ajoutez quelques plats avec photos et prix, et partagez votre lien ou imprimez votre QR code.'],
                ['Est-ce qu\'il y a des commissions sur les commandes ?', 'Aucune commission. Vous payez un forfait mensuel fixe à partir de 5 000 F/mois et gardez 100% de vos ventes. Contrairement à Glovo qui prend 20-30%.'],
                ['Que se passe-t-il si j\'ai un problème ?', 'Support WhatsApp inclus dans tous les plans. Plans Pro et Gold : assistance prioritaire et formation personnalisée.'],
                ['Puis-je annuler à tout moment ?', 'Oui, sans engagement et sans frais. Annulez depuis votre compte quand vous voulez.'],
            ];
        @endphp
        <div class="space-y-3 fu">
            @foreach($faqs as $i => $faq)
            <div class="rounded-2xl border border-neutral-100 overflow-hidden bg-neutral-50" x-data="{ open: {{ $i===0?'true':'false' }} }">
                <button @click="open=!open" class="w-full flex items-center justify-between px-6 py-5 text-left font-black text-neutral-900 hover:bg-white transition-colors">
                    <span class="text-sm sm:text-base">{{ $faq[0] }}</span>
                    <span class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 ml-4 transition-transform duration-300" :class="open&&'rotate-45'" style="background:rgba(212,94,12,.1);color:#D45E0C">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    </span>
                </button>
                <div x-show="open" x-collapse>
                    <p class="px-6 pb-5 text-neutral-500 text-sm leading-relaxed">{{ $faq[1] }}</p>
                </div>
            </div>
            @endforeach
        </div>
        <div class="text-center mt-10 fu d2">
            <a href="{{ route('faq') }}" class="font-black text-sm hover:underline" style="color:#D45E0C">Voir toutes les questions →</a>
        </div>
    </div>
</section>


{{-- ══════════════════════════════════════
     14. CTA FINAL — Fond noir + orange
══════════════════════════════════════ --}}
<section class="relative py-24 sm:py-32 overflow-hidden" style="background:#161616">
    <div class="pointer-events-none absolute inset-0" style="background:radial-gradient(ellipse 70% 60% at 50% 50%,rgba(212,94,12,.18),transparent)"></div>

    <div class="relative z-10 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center fu">
        <div class="inline-flex items-center gap-2 px-4 py-1.5 border border-primary-500/30 bg-primary-500/10 rounded-full text-primary-300 text-sm font-bold mb-8">
            <span class="relative flex h-2 w-2 shrink-0"><span class="ps absolute inline-flex h-full w-full rounded-full bg-primary-400"></span><span class="relative inline-flex h-2 w-2 rounded-full bg-primary-400"></span></span>
            {{ $stats['restaurants'] }} restaurants actifs ce soir
        </div>

        <h2 class="text-5xl sm:text-6xl lg:text-7xl font-black text-white leading-[1.02] tracking-tight">
            Votre prochain<br>client commande<br><span class="gt">dans 15 minutes.</span>
        </h2>

        <p class="text-xl text-white/50 mt-6 max-w-lg mx-auto leading-relaxed">
            Rejoignez les restaurateurs ivoiriens qui encaissent en direct sur leur Mobile Money.
        </p>

        <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="{{ route('register') }}" class="group w-full sm:w-auto inline-flex items-center justify-center gap-2.5 px-10 py-4 text-white font-black rounded-2xl transition-all duration-200 hover:-translate-y-0.5 text-lg" style="background:#D45E0C;box-shadow:0 0 40px rgba(212,94,12,.3)">
                Créer mon restaurant — C'est gratuit
                <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
            <a href="{{ route('contact') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-4 text-white/70 hover:text-white font-semibold rounded-2xl border border-white/15 hover:bg-white/5 transition-all text-base">
                Parler à un expert
            </a>
        </div>

        <div class="mt-12 flex flex-wrap items-center justify-center gap-6 text-sm text-neutral-600">
            @foreach(['15 min pour être en ligne','Support WhatsApp inclus','À partir de 5 000 F/mois','Annulation libre'] as $t)
            <span class="flex items-center gap-2">
                <svg class="w-4 h-4 text-secondary-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                {{ $t }}
            </span>
            @endforeach
        </div>
    </div>
</section>

@push('scripts')
<script>
function counter(target){return{count:0,target:target,displayCount:'0',startCount(){const s=40,d=1600,sv=this.target/s,sd=d/s;const iv=setInterval(()=>{this.count+=sv;if(this.count>=this.target){this.count=this.target;clearInterval(iv);}this.displayCount=this.count>=1000?Math.round(this.count/1000)+'K+':Math.round(this.count).toString();},sd);}}}
const io=new IntersectionObserver(e=>{e.forEach(x=>{if(x.isIntersecting){x.target.classList.add('in');io.unobserve(x.target);}});},{threshold:.08});
document.querySelectorAll('.fu').forEach(el=>io.observe(el));
</script>
@endpush
</x-layouts.public>
