<x-layouts.public title="Accueil" description="MenuPro : digitalisez votre restaurant, menu en ligne, commandes et paiement Mobile Money. Solution SaaS pour restaurants en Cote d'Ivoire.">
@push('head')
<script type="application/ld+json">{"@@context":"https://schema.org","@@type":"SoftwareApplication","name":"MenuPro","applicationCategory":"BusinessApplication","operatingSystem":"Web","url":"{{ url('/') }}","description":"Plateforme SaaS de commande en ligne pour restaurants en Cote d'Ivoire.","offers":{"@@type":"Offer","price":"5000","priceCurrency":"XOF"}}</script>
<style>
/* Animations */
.fu{opacity:0;transform:translateY(24px);transition:opacity .55s cubic-bezier(.22,1,.36,1),transform .55s cubic-bezier(.22,1,.36,1)}
.fu.in{opacity:1;transform:none}
.fu.d1{transition-delay:.1s}.fu.d2{transition-delay:.18s}.fu.d3{transition-delay:.26s}.fu.d4{transition-delay:.34s}

/* Gradient text — padding-right pour éviter la coupure en italic */
.gt{background:linear-gradient(120deg,#D45E0C,#ef8a4d);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;padding-right:.1em;display:inline-block}
.gt-light{background:linear-gradient(120deg,#f6b285,#fad2b5);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;padding-right:.1em;display:inline-block}

/* Hero bg — blanc pur, aucun fond parasite */
.hero-bg{background:#ffffff !important;background-color:#ffffff !important}
.hero-blob,.hero-blob2,.hero-shape{display:none}
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
<section class="hero-bg relative overflow-hidden" style="min-height:100vh;background-color:#ffffff">

    {{-- Décoration : ligne orange subtile en haut --}}
    <div class="pointer-events-none absolute top-0 left-0 right-0 h-1" style="background:linear-gradient(90deg,transparent,#D45E0C 30%,#ef8a4d 70%,transparent)"></div>
    {{-- Cercle décoratif bas-droite --}}
    <div class="pointer-events-none absolute -bottom-32 -right-32 w-96 h-96 rounded-full" style="background:radial-gradient(circle,rgba(212,94,12,.08),transparent 70%)"></div>


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
                @php
                    $proofRestaurants = \App\Models\Restaurant::where('status', \App\Enums\RestaurantStatus::ACTIVE)
                        ->whereNotNull('logo_path')
                        ->where('logo_path', '!=', '')
                        ->latest()
                        ->take(5)
                        ->get(['name', 'logo_path']);
                    $hasLogos = $proofRestaurants->count() >= 3;
                @endphp
                <div class="mt-10 flex flex-wrap items-center gap-5 justify-center lg:justify-start">

                    {{-- Logos ou initiales --}}
                    <div class="flex items-center gap-3">
                        <div class="flex -space-x-2.5">
                            @if($hasLogos)
                                @foreach($proofRestaurants as $r)
                                <div class="w-9 h-9 rounded-full border-2 border-white shadow-sm overflow-hidden bg-white">
                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($r->logo_path) }}"
                                         alt="{{ $r->name }}"
                                         class="w-full h-full object-cover"
                                         loading="lazy">
                                </div>
                                @endforeach
                            @else
                                @php
                                    $fallbackRestaurants = \App\Models\Restaurant::where('status', \App\Enums\RestaurantStatus::ACTIVE)->latest()->take(5)->get(['name']);
                                    $colors = ['#D45E0C','#22c55e','#3b82f6','#a855f7','#f59e0b'];
                                @endphp
                                @foreach($fallbackRestaurants as $r)
                                <div class="w-9 h-9 rounded-full border-2 border-white flex items-center justify-center text-white text-xs font-black shadow-sm"
                                     style="background:{{ $colors[$loop->index % 5] }}">
                                    {{ strtoupper(substr($r->name, 0, 1)) }}
                                </div>
                                @endforeach
                            @endif
                        </div>
                        <div class="text-left">
                            <div class="flex items-center gap-0.5 mb-0.5">
                                @for($i=0;$i<5;$i++)<svg class="w-3 h-3 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>@endfor
                            </div>
                            <div class="text-xs font-black text-neutral-800">{{ $stats['restaurants'] }}+ restaurants actifs</div>
                        </div>
                    </div>

                    <div class="w-px h-8 bg-neutral-200 hidden sm:block"></div>

                    {{-- Commandes --}}
                    <div class="text-center sm:text-left">
                        <div class="text-xl font-black text-neutral-900 leading-none"
                             x-data="counter({{ $stats['raw']['orders'] }})"
                             x-intersect.once="startCount()">
                            <span x-text="displayCount"></span>
                        </div>
                        <div class="text-xs text-neutral-500 mt-0.5">Commandes traitées</div>
                    </div>

                    <div class="w-px h-8 bg-neutral-200 hidden sm:block"></div>

                    {{-- Prix --}}
                    <div class="text-center sm:text-left">
                        <div class="text-xl font-black leading-none" style="color:#D45E0C">5 000 F</div>
                        <div class="text-xs text-neutral-500 mt-0.5">À partir de / mois</div>
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
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($heroImage) }}"
                         alt="MenuPro"
                         class="relative w-full max-w-[400px] sm:max-w-[460px] rounded-3xl shadow-2xl"
                         loading="eager" width="460" height="500">
                @else
                {{-- Illustration fallback : cartes flottantes sans téléphone --}}
                <div class="relative w-full max-w-[420px]">

                    {{-- Fond cercle orange --}}
                    <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                        <div class="w-72 h-72 rounded-full" style="background:radial-gradient(circle,rgba(212,94,12,.1),transparent 70%)"></div>
                    </div>

                    {{-- Carte principale : commande en cours --}}
                    <div class="relative bg-white rounded-3xl shadow-2xl border border-neutral-100 p-5 mx-auto max-w-sm">
                        <div class="flex items-center justify-between mb-4">
                            <div>
                                <p class="text-xs text-neutral-400 font-semibold uppercase tracking-wide">Commande en cours</p>
                                <p class="font-black text-neutral-900 text-base">#CMD-260805-AWA3</p>
                            </div>
                            <span class="px-3 py-1.5 rounded-full text-xs font-black text-white" style="background:#D45E0C">En préparation</span>
                        </div>
                        <div class="space-y-3">
                            @foreach([['Poulet Braisé + Alloco','5 500 F','#fef3c7'],['Jus Bissap naturel','1 500 F','#fce7f3']] as $item)
                            <div class="flex items-center gap-3">
                                <div class="w-10 h-10 rounded-xl shrink-0" style="background:{{ $item[2] }}"></div>
                                <div class="flex-1">
                                    <p class="text-sm font-bold text-neutral-800">{{ $item[0] }}</p>
                                </div>
                                <span class="font-black text-sm" style="color:#D45E0C">{{ $item[1] }}</span>
                            </div>
                            @endforeach
                        </div>
                        <div class="mt-4 pt-4 border-t border-neutral-100 flex items-center justify-between">
                            <span class="text-sm text-neutral-500">Total</span>
                            <span class="font-black text-lg text-neutral-900">7 000 F</span>
                        </div>
                    </div>

                    {{-- Carte flottante : paiement Wave --}}
                    <div class="fl absolute -top-6 -right-4 bg-white rounded-2xl shadow-xl border border-neutral-100 px-4 py-3 flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0" style="background:#e0f2fe">
                            <img src="{{ asset('images/payments/wave.png') }}" class="w-5 h-5 object-contain" alt="Wave">
                        </div>
                        <div>
                            <p class="text-xs font-black text-neutral-800">Paiement reçu ✓</p>
                            <p class="text-[11px] text-neutral-500">Wave · 7 000 F</p>
                        </div>
                    </div>

                    {{-- Carte flottante : nouvelle commande --}}
                    <div class="fl2 absolute -bottom-5 -left-4 bg-white rounded-2xl shadow-xl border border-neutral-100 px-4 py-3 flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0" style="background:rgba(212,94,12,.1)">
                            <svg class="w-5 h-5" style="color:#D45E0C" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        </div>
                        <div>
                            <p class="text-xs font-black text-neutral-800">Nouvelle commande !</p>
                            <p class="text-[11px] text-neutral-500">Table 4 · 2 plats</p>
                        </div>
                    </div>

                    {{-- Stat flottante : restaurants --}}
                    <div class="absolute top-1/2 -left-8 bg-white rounded-2xl shadow-lg border border-neutral-100 px-3 py-2.5 text-center">
                        <p class="font-black text-xl" style="color:#D45E0C">{{ $stats['restaurants'] }}+</p>
                        <p class="text-[10px] text-neutral-500 leading-tight">Restaurants<br>actifs</p>
                    </div>
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
     7. CHIFFRES CLÉS
══════════════════════════════════════ --}}
<section class="py-20 bg-white">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12 fu">
            <span class="text-xs font-black uppercase tracking-widest" style="color:#D45E0C">Nos chiffres</span>
            <h2 class="text-4xl sm:text-5xl font-black text-neutral-900 mt-3 leading-tight">
                Tout ce dont votre<br><span class="gt">restaurant a besoin</span>
            </h2>
        </div>
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-5 fu">
            @foreach([
                ['0%','Commission par commande','Gardez 100% de vos revenus.','border-primary-200','text-primary-500'],
                ['15 min','Pour être en ligne','Menu, QR code, paiements prêts.','border-neutral-200','text-neutral-900'],
                ['4','Moyens de paiement','Wave, Orange, MTN, Moov.','border-secondary-200','text-secondary-600'],
                ['24/7','Commandes reçues','Même quand vous dormez.','border-neutral-200','text-neutral-900'],
            ] as $kpi)
            <div class="rounded-3xl p-6 border-2 {{ $kpi[3] }} bg-white text-center hover:-translate-y-1 hover:shadow-lg transition-all duration-300">
                <div class="text-4xl sm:text-5xl font-black {{ $kpi[4] }} leading-none mb-3">{{ $kpi[0] }}</div>
                <div class="font-black text-neutral-900 text-sm mb-1.5">{{ $kpi[1] }}</div>
                <div class="text-neutral-400 text-xs leading-relaxed">{{ $kpi[2] }}</div>
            </div>
            @endforeach
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
     11. RESTAURANTS — Cartes bannière+logo
══════════════════════════════════════ --}}
<section class="py-20 bg-neutral-50">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12 fu">
            <span class="text-xs font-black uppercase tracking-widest" style="color:#D45E0C">Ils nous font confiance</span>
            <h2 class="text-3xl sm:text-4xl font-black text-neutral-900 mt-3">Découvrez nos restaurants</h2>
            <p class="text-neutral-500 text-sm mt-2">Commandez directement depuis leur menu en ligne</p>
        </div>
        @php
            $trs = \App\Models\Restaurant::where('status', \App\Enums\RestaurantStatus::ACTIVE)
                ->latest()
                ->take(8)
                ->get(['name', 'slug', 'logo_path', 'banner_path', 'city']);
        @endphp
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-5 fu">
            @foreach($trs as $r)
            <a href="{{ route('r.menu', $r->slug) }}"
               target="_blank"
               class="group bg-white rounded-2xl overflow-hidden border border-neutral-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 block">

                {{-- Bannière --}}
                <div class="relative h-28 overflow-hidden bg-neutral-200">
                    @if($r->banner_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($r->banner_path))
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($r->banner_path) }}"
                             alt="Bannière {{ $r->name }}"
                             class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                             loading="lazy">
                    @else
                        {{-- Bannière placeholder avec dégradé --}}
                        <div class="w-full h-full" style="background:linear-gradient(135deg,#D45E0C,#b84e0a)">
                            <div class="w-full h-full flex items-center justify-center opacity-20">
                                <svg class="w-16 h-16 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                            </div>
                        </div>
                    @endif

                    {{-- Badge "Voir le menu" au hover --}}
                    <div class="absolute inset-0 flex items-center justify-center bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity duration-300">
                        <span class="text-white text-xs font-black px-3 py-1.5 rounded-full border-2 border-white">Voir le menu →</span>
                    </div>
                </div>

                {{-- Logo chevauchant la bannière --}}
                <div class="px-4 pb-4">
                    <div class="relative -mt-6 mb-3">
                        <div class="w-12 h-12 rounded-xl border-2 border-white shadow-md overflow-hidden bg-white">
                            @if($r->logo_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($r->logo_path))
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($r->logo_path) }}"
                                     alt="{{ $r->name }}"
                                     class="w-full h-full object-cover"
                                     loading="lazy">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-white font-black text-lg" style="background:#D45E0C">
                                    {{ strtoupper(substr($r->name, 0, 1)) }}
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Infos --}}
                    <h3 class="font-black text-sm text-neutral-900 truncate">{{ $r->name }}</h3>
                    @if($r->city)
                    <p class="text-xs text-neutral-400 mt-0.5 flex items-center gap-1">
                        <svg class="w-3 h-3 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        {{ $r->city }}
                    </p>
                    @endif
                </div>
            </a>
            @endforeach
        </div>

        <div class="text-center mt-10 fu">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-2 text-sm font-black hover:underline" style="color:#D45E0C">
                Voir tous les restaurants
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
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
