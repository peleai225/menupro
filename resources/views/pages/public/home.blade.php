<x-layouts.public title="Accueil" description="MenuPro : digitalisez votre restaurant, menu en ligne, commandes et paiement Mobile Money. Solution SaaS pour restaurants en Cote d'Ivoire.">
@push('head')
<script type="application/ld+json">{"@@context":"https://schema.org","@@type":"SoftwareApplication","name":"MenuPro","applicationCategory":"BusinessApplication","operatingSystem":"Web","url":"{{ url('/') }}","description":"Plateforme SaaS de commande en ligne pour restaurants en Cote d'Ivoire.","offers":{"@@type":"Offer","price":"5000","priceCurrency":"XOF"}}</script>
<style>
/* ─── Core ─────────────────────────────────────── */
:root { --brand:#2563eb; --brand-dark:#1d4ed8; }
.fu{opacity:0;transform:translateY(28px);transition:opacity .6s cubic-bezier(.22,1,.36,1),transform .6s cubic-bezier(.22,1,.36,1)}
.fu.in{opacity:1;transform:none}
.fu.d1{transition-delay:.1s}.fu.d2{transition-delay:.2s}.fu.d3{transition-delay:.3s}.fu.d4{transition-delay:.4s}.fu.d5{transition-delay:.5s}

/* ─── Hero ─────────────────────────────────────── */
.hero-bg{background:radial-gradient(ellipse 100% 80% at 60% -20%,rgba(37,99,235,.22) 0%,transparent 60%),radial-gradient(ellipse 60% 60% at 0% 80%,rgba(99,102,241,.14) 0%,transparent 60%),#030712}
.hero-grid{background-image:linear-gradient(rgba(255,255,255,.03) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.03) 1px,transparent 1px);background-size:48px 48px}

/* ─── Gradient text ─────────────────────────────── */
.gt{background:linear-gradient(130deg,#60a5fa 0%,#a78bfa 50%,#34d399 100%);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}
.gt-orange{background:linear-gradient(130deg,#fb923c,#f43f5e);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text}

/* ─── Float anim ────────────────────────────────── */
@keyframes fl{0%,100%{transform:translateY(0) rotate(-1deg)}50%{transform:translateY(-10px) rotate(1deg)}}
.fl{animation:fl 5s ease-in-out infinite}
.fl2{animation:fl 6s ease-in-out infinite .8s}

/* ─── Pulse ─────────────────────────────────────── */
@keyframes ps{0%,100%{transform:scale(1);opacity:.5}50%{transform:scale(1.7);opacity:0}}
.ps{animation:ps 2.2s ease-in-out infinite}

/* ─── Shine on card ─────────────────────────────── */
.shine{position:relative;overflow:hidden}
.shine::after{content:'';position:absolute;inset:0;background:linear-gradient(105deg,transparent 40%,rgba(255,255,255,.06) 50%,transparent 60%);transform:translateX(-100%);transition:transform .5s}
.shine:hover::after{transform:translateX(100%)}

/* ─── Ticker ─────────────────────────────────────── */
@keyframes tk{from{transform:translateX(0)}to{transform:translateX(-50%)}}
.tk-wrap{overflow:hidden;mask-image:linear-gradient(90deg,transparent,black 10%,black 90%,transparent)}
.tk-track{display:flex;width:max-content;animation:tk 35s linear infinite}
.tk-track:hover{animation-play-state:paused}

/* ─── Compare table ─────────────────────────────── */
.compare-yes{color:#16a34a}
.compare-no{color:#dc2626}

/* ─── FAQ accordion ─────────────────────────────── */
.faq-body{display:grid;grid-template-rows:0fr;transition:grid-template-rows .3s ease}
.faq-body.open{grid-template-rows:1fr}
.faq-inner{overflow:hidden}
</style>
@endpush

{{-- ╔════════════════════════════════╗
     ║  1. HERO                       ║
     ╚════════════════════════════════╝ --}}
<section class="hero-bg relative min-h-screen flex flex-col justify-center overflow-hidden">
    <div class="hero-grid absolute inset-0"></div>

    {{-- Glow orbs --}}
    <div class="pointer-events-none absolute -top-32 left-1/4 w-[600px] h-[600px] bg-blue-600/20 rounded-full blur-3xl"></div>
    <div class="pointer-events-none absolute bottom-0 right-0 w-[400px] h-[400px] bg-violet-600/15 rounded-full blur-3xl"></div>

    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-20 pb-24 lg:pt-28 lg:pb-32 w-full">
        <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">

            {{-- ─── Copy ─── --}}
            <div class="text-center lg:text-left">

                {{-- Badge --}}
                <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-4 py-1.5 bg-blue-500/10 border border-blue-500/30 rounded-full text-blue-300 text-sm font-semibold mb-8 hover:bg-blue-500/20 transition-colors">
                    <span class="relative flex h-2 w-2 shrink-0"><span class="ps absolute inline-flex h-full w-full rounded-full bg-emerald-400"></span><span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-400"></span></span>
                    7 jours gratuits — aucune carte requise
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M9 5l7 7-7 7"/></svg>
                </a>

                <h1 class="text-5xl sm:text-6xl lg:text-7xl font-black text-white leading-[1.02] tracking-tight">
                    Vos clients<br>commandent.<br>
                    <span class="gt">Vous encaissez.</span>
                </h1>

                <p class="mt-6 text-xl text-white/55 max-w-lg mx-auto lg:mx-0 leading-relaxed">
                    MenuPro digitalise votre restaurant en <strong class="text-white font-semibold">15 minutes</strong>. Menu en ligne, QR codes, commandes en temps réel, paiements <strong class="text-white font-semibold">Wave · Orange · MTN · Moov</strong> — argent directement sur votre compte.
                </p>

                <div class="mt-10 flex flex-col sm:flex-row items-stretch sm:items-center gap-3 justify-center lg:justify-start">
                    <a href="{{ route('register') }}" class="group inline-flex items-center justify-center gap-2.5 px-8 py-4 bg-blue-600 hover:bg-blue-500 text-white font-extrabold rounded-2xl shadow-2xl shadow-blue-600/30 transition-all duration-200 hover:-translate-y-0.5 text-lg">
                        Créer mon restaurant — Gratuit
                        <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    </a>
                    <a href="{{ route('r.menu', ['slug' => 'demo']) }}" target="_blank" class="inline-flex items-center justify-center gap-2.5 px-8 py-4 bg-white/5 hover:bg-white/10 text-white/80 hover:text-white font-semibold rounded-2xl border border-white/10 transition-all text-lg">
                        <svg class="w-5 h-5 text-blue-400" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                        Voir la démo live
                    </a>
                </div>

                {{-- Social proof --}}
                <div class="mt-12 flex flex-wrap items-center gap-8 justify-center lg:justify-start">
                    @foreach([
                        [$stats['raw']['restaurants'], 'restaurants actifs'],
                        [$stats['raw']['orders'], 'commandes traitées'],
                        ['5 000 F', 'à partir de / mois'],
                    ] as $s)
                    <div class="text-center lg:text-left">
                        <div class="text-3xl font-black text-white leading-none" @if(is_numeric($s[0])) x-data="counter({{ $s[0] }})" x-intersect.once="startCount()" @endif>
                            @if(is_numeric($s[0]))<span x-text="displayCount"></span>@else{{ $s[0] }}@endif
                        </div>
                        <div class="text-white/40 text-xs mt-1 uppercase tracking-widest">{{ $s[1] }}</div>
                    </div>
                    @if(!$loop->last)<div class="w-px h-10 bg-white/10 hidden sm:block"></div>@endif
                    @endforeach
                </div>
            </div>

            {{-- ─── Mockup ─── --}}
            <div class="relative flex justify-center lg:justify-end">
                <div class="absolute inset-0 flex items-center justify-center pointer-events-none">
                    <div class="w-96 h-96 bg-blue-500/15 rounded-full blur-3xl"></div>
                </div>

                @php $heroImage = \App\Models\SystemSetting::get('hero_image',''); @endphp
                @if($heroImage && \Illuminate\Support\Facades\Storage::disk('public')->exists($heroImage))
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($heroImage) }}" alt="MenuPro" class="relative w-full max-w-[320px] sm:max-w-[360px] rounded-3xl shadow-2xl border border-white/10" loading="eager" width="360" height="680">
                @else
                <div class="relative w-[280px] sm:w-[320px]">
                    {{-- Phone --}}
                    <div class="relative bg-neutral-900 rounded-[2.5rem] p-1.5 shadow-2xl border border-white/10 ring-1 ring-white/5">
                        <div class="rounded-[2.2rem] overflow-hidden bg-white">
                            {{-- App header --}}
                            <div style="background:linear-gradient(135deg,#1e3a8a,#2563eb)" class="px-5 pt-10 pb-5 text-white">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center text-xl">🍽️</div>
                                        <div>
                                            <div class="font-black text-sm">Chez Awa</div>
                                            <div class="flex items-center gap-1 text-[11px] text-white/70">
                                                <span class="w-1.5 h-1.5 rounded-full bg-green-300"></span>Ouvert · 08h–22h
                                            </div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="text-[10px] text-white/60">Table 4</div>
                                        <div class="text-xs font-bold">QR Scanné</div>
                                    </div>
                                </div>
                            </div>
                            {{-- Search --}}
                            <div class="px-3 py-2.5 bg-neutral-50 border-b border-neutral-100">
                                <div class="bg-white rounded-xl px-3 py-2 flex items-center gap-2 border border-neutral-200">
                                    <svg class="w-4 h-4 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                    <span class="text-neutral-400 text-xs">Rechercher un plat...</span>
                                </div>
                            </div>
                            {{-- Categories --}}
                            <div class="flex gap-2 px-3 pt-3 pb-1 overflow-x-hidden">
                                <span class="px-3 py-1.5 text-white text-[11px] font-bold rounded-full whitespace-nowrap shrink-0" style="background:#2563eb">🔥 Populaires</span>
                                <span class="px-3 py-1.5 bg-neutral-100 text-neutral-500 text-[11px] rounded-full whitespace-nowrap shrink-0">Plats chauds</span>
                                <span class="px-3 py-1.5 bg-neutral-100 text-neutral-500 text-[11px] rounded-full whitespace-nowrap shrink-0">Boissons</span>
                            </div>
                            {{-- Items --}}
                            <div class="px-3 pt-2 pb-1 space-y-2.5">
                                @foreach([
                                    ['Poulet Braisé', '5 500 F', '4.9', '#fef3c7', '⭐'],
                                    ['Attieké Poisson', '4 500 F', '4.7', '#dcfce7', '⭐'],
                                    ['Riz sauce graine', '3 500 F', '4.8', '#fce7f3', '⭐'],
                                ] as $d)
                                <div class="bg-white rounded-2xl p-2.5 flex gap-2.5 shadow-sm border border-neutral-100">
                                    <div class="w-12 h-12 rounded-xl shrink-0 flex items-center justify-center text-2xl" style="background:{{ $d[3] }}">{{ ['🍗','🐟','🍚'][$loop->index] }}</div>
                                    <div class="flex-1 min-w-0">
                                        <div class="font-bold text-xs text-neutral-800 truncate">{{ $d[0] }}</div>
                                        <div class="text-[10px] text-neutral-400">{{ $d[4] }} {{ $d[2] }} · 20 min</div>
                                        <div class="flex items-center justify-between mt-1">
                                            <span class="font-black text-xs text-blue-600">{{ $d[1] }}</span>
                                        </div>
                                    </div>
                                    <button class="w-7 h-7 rounded-xl flex items-center justify-center font-black text-white text-sm self-center shrink-0" style="background:#2563eb">+</button>
                                </div>
                                @endforeach
                            </div>
                            {{-- Cart --}}
                            <div class="px-3 pb-4 pt-2">
                                <div class="text-white rounded-2xl px-4 py-3 flex items-center justify-between shadow-xl" style="background:linear-gradient(135deg,#1d4ed8,#2563eb)">
                                    <div class="flex items-center gap-2.5">
                                        <span class="w-6 h-6 bg-white/20 rounded-lg flex items-center justify-center text-[11px] font-black">2</span>
                                        <span class="text-xs font-bold">Voir panier</span>
                                    </div>
                                    <span class="font-black text-sm">10 000 F</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Notification Wave --}}
                    <div class="fl absolute -top-6 -right-10 bg-white rounded-2xl shadow-2xl border border-neutral-100 px-4 py-3 flex items-center gap-3 w-56">
                        <div class="w-10 h-10 rounded-xl flex items-center justify-center shrink-0" style="background:#e0f2fe">
                            <img src="{{ asset('images/payments/wave.png') }}" class="w-6 h-6 object-contain" alt="Wave">
                        </div>
                        <div>
                            <div class="text-xs font-black text-neutral-800">Paiement reçu ✓</div>
                            <div class="text-[11px] text-neutral-500">Wave · 5 500 F → votre compte</div>
                        </div>
                    </div>

                    {{-- Notification commande --}}
                    <div class="fl2 absolute -bottom-6 -left-10 bg-white rounded-2xl shadow-2xl border border-neutral-100 px-4 py-3 flex items-center gap-3 w-52">
                        <div class="w-10 h-10 bg-blue-100 rounded-xl flex items-center justify-center shrink-0 text-lg">🔔</div>
                        <div>
                            <div class="text-xs font-black text-neutral-800">Nouvelle commande !</div>
                            <div class="text-[11px] text-neutral-500">Table 7 · Poulet braisé</div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Wave transition --}}
    <div class="absolute bottom-0 left-0 right-0 leading-none">
        <svg viewBox="0 0 1440 80" preserveAspectRatio="none" class="w-full h-16 sm:h-20" fill="white"><path d="M0,80 C360,20 720,60 1080,20 C1260,0 1380,40 1440,30 L1440,80Z"/></svg>
    </div>
</section>


{{-- ╔════════════════════════════════╗
     ║  2. POUR QUI ?                 ║
     ╚════════════════════════════════╝ --}}
<section class="py-20 bg-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12 fu">
            <p class="text-sm font-bold text-blue-600 uppercase tracking-widest mb-2">Pour qui ?</p>
            <h2 class="text-3xl sm:text-4xl font-black text-neutral-900">De la vendeuse de panini<br>au grand hôtel</h2>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-4 fu">
            @foreach([
                ['🥪','Stand & Street food','Vendeurs de rue, paninis, jus, tacos. MenuPro sur votre téléphone, zéro PC requis.','bg-orange-50 border-orange-200','text-orange-600','Stand'],
                ['🍽️','Maquis & Restaurant','Gérez tables, commandes, cuisine. QR codes sur chaque table, alertes sonores.','bg-blue-50 border-blue-200','text-blue-600','Essentiel / Pro'],
                ['🏨','Hôtel & Résidence','QR par chambre, room service, appel personnel avec voix IA.','bg-violet-50 border-violet-200','text-violet-600','Gold'],
                ['🛵','Livraison','Gérez vos propres livreurs, suivi temps réel, clients informés.','bg-emerald-50 border-emerald-200','text-emerald-600','Pro'],
            ] as $who)
            <div class="{{ $who[3] }} border rounded-3xl p-6 hover:-translate-y-1 hover:shadow-lg transition-all duration-300">
                <div class="text-4xl mb-4">{{ $who[0] }}</div>
                <h3 class="font-black text-neutral-900 text-lg mb-2">{{ $who[1] }}</h3>
                <p class="text-neutral-600 text-sm leading-relaxed mb-4">{{ $who[2] }}</p>
                <span class="text-xs font-bold {{ $who[4] }} bg-white/80 px-3 py-1.5 rounded-full border border-current/20">Plan {{ $who[5] }}</span>
            </div>
            @endforeach
        </div>
    </div>
</section>


{{-- ╔════════════════════════════════╗
     ║  3. STATS CHOC                 ║
     ╚════════════════════════════════╝ --}}
<section class="py-16 bg-neutral-950 relative overflow-hidden">
    <div class="pointer-events-none absolute inset-0" style="background:radial-gradient(ellipse 70% 50% at 50% 50%,rgba(37,99,235,.1),transparent)"></div>
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-6 text-center fu">
            @foreach([
                ['15 min','Pour être en ligne','text-blue-400'],
                ['0%','Commission par commande','text-emerald-400'],
                ['4','Paiements Mobile Money','text-amber-400'],
                ['24/7','Commandes reçues','text-violet-400'],
            ] as $s)
            <div>
                <div class="text-5xl sm:text-6xl font-black {{ $s[2] }} leading-none tabular-nums">{{ $s[0] }}</div>
                <div class="text-neutral-500 text-sm mt-3 leading-tight">{{ $s[1] }}</div>
            </div>
            @endforeach
        </div>
    </div>
</section>


{{-- ╔════════════════════════════════╗
     ║  4. PAIEMENTS                  ║
     ╚════════════════════════════════╝ --}}
<section class="py-14 bg-white border-b border-neutral-100">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <p class="text-center text-xs font-black text-neutral-400 uppercase tracking-[0.25em] mb-8">Paiements acceptés</p>
        <div class="flex items-center justify-center gap-6 sm:gap-10 flex-wrap">
            @foreach([['wave.png','Wave','#e0f2fe'],['orange-money.png','Orange Money','#fff7ed'],['mtn-momo.png','MTN MoMo','#fefce8'],['moov-money.png','Moov Money','#eff6ff']] as $p)
            <div class="flex flex-col items-center gap-2 group">
                <div class="w-16 h-16 rounded-2xl border border-neutral-100 flex items-center justify-center transition-all group-hover:shadow-lg group-hover:-translate-y-0.5" style="background:{{ $p[2] }}">
                    <img src="{{ asset('images/payments/'.$p[0]) }}" alt="{{ $p[1] }}" class="h-10 w-10 object-contain" loading="lazy">
                </div>
                <span class="text-xs font-semibold text-neutral-500">{{ $p[1] }}</span>
            </div>
            @endforeach
        </div>
    </div>
</section>


{{-- ╔════════════════════════════════╗
     ║  5. FEATURES                   ║
     ╚════════════════════════════════╝ --}}
<section class="py-24 sm:py-32 bg-white">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-xl mx-auto mb-16 fu">
            <span class="text-xs font-black text-blue-600 uppercase tracking-widest">Fonctionnalités</span>
            <h2 class="text-4xl sm:text-5xl font-black text-neutral-900 mt-3 leading-tight">Tout ce dont votre<br>restaurant a besoin</h2>
        </div>

        {{-- Feature 1 --}}
        <div class="grid lg:grid-cols-2 gap-8 items-center mb-8">
            <div class="shine bg-gradient-to-br from-blue-600 to-indigo-700 rounded-3xl p-10 text-white relative overflow-hidden fu">
                <div class="absolute -right-8 -top-8 w-40 h-40 bg-white/5 rounded-full"></div>
                <div class="absolute right-12 bottom-8 w-24 h-24 bg-white/5 rounded-full"></div>
                <div class="w-14 h-14 bg-white/15 rounded-2xl flex items-center justify-center text-3xl mb-6">📱</div>
                <h3 class="text-2xl font-black mb-3">Commandes en temps réel</h3>
                <p class="text-white/70 leading-relaxed">Notification instantanée, alerte sonore, écran cuisine dédié. Zéro commande ratée.</p>
                <div class="mt-6 flex flex-wrap gap-2">
                    @foreach(['QR code tables','Lien WhatsApp','App mobile','Notif push'] as $t)
                    <span class="bg-white/10 text-white/80 text-xs font-semibold px-3 py-1.5 rounded-full border border-white/10">{{ $t }}</span>
                    @endforeach
                </div>
            </div>
            <div class="grid grid-cols-2 gap-4 fu d2">
                @foreach([
                    ['💳','Paiement Mobile Money','Wave, Orange Money, MTN, Moov. Argent sur votre compte en temps réel.','bg-emerald-50 border-emerald-100'],
                    ['📊','Analytics & Rapports','Bilan journalier par heure, CA espèces vs mobile money, taux d\'annulation.','bg-amber-50 border-amber-100'],
                    ['📦','Gestion de stock','Alertes de rupture, inventaire, mouvements. Plus jamais à court.','bg-violet-50 border-violet-100'],
                    ['👥','Équipe & Serveurs','PIN dédié par employé, accès contrôlé, rapports par serveur.','bg-rose-50 border-rose-100'],
                ] as $f)
                <div class="{{ $f[3] }} border rounded-2xl p-5 hover:-translate-y-0.5 hover:shadow-md transition-all duration-200">
                    <div class="text-2xl mb-3">{{ $f[0] }}</div>
                    <h4 class="font-black text-sm text-neutral-900 mb-1.5">{{ $f[1] }}</h4>
                    <p class="text-xs text-neutral-500 leading-relaxed">{{ $f[2] }}</p>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Feature 2 --}}
        <div class="grid lg:grid-cols-3 gap-4 fu">
            <div class="shine lg:col-span-2 bg-gradient-to-br from-violet-600 to-purple-700 rounded-3xl p-10 text-white relative overflow-hidden">
                <div class="absolute -left-8 -bottom-8 w-40 h-40 bg-white/5 rounded-full"></div>
                <div class="w-14 h-14 bg-white/15 rounded-2xl flex items-center justify-center text-3xl mb-6">🏨</div>
                <h3 class="text-2xl font-black mb-3">Mode Hôtel — QR par chambre</h3>
                <p class="text-white/70 leading-relaxed max-w-lg">Chaque chambre a son propre QR code. Le client commande depuis son lit, la voix IA annonce les commandes en cuisine et au personnel. Appel addition, appel ménage — tout depuis le téléphone.</p>
                <div class="mt-6 flex flex-wrap gap-2">
                    @foreach(['QR par chambre','Voix IA','Appel personnel','Room service'] as $t)
                    <span class="bg-white/10 text-white/80 text-xs font-semibold px-3 py-1.5 rounded-full border border-white/10">{{ $t }}</span>
                    @endforeach
                </div>
            </div>
            <div class="shine bg-gradient-to-br from-emerald-500 to-teal-600 rounded-3xl p-8 text-white">
                <div class="text-3xl mb-5">🛵</div>
                <h3 class="text-xl font-black mb-3">Livraison intégrée</h3>
                <p class="text-white/70 leading-relaxed text-sm">Gérez vos livreurs, suivi temps réel. Vos clients voient leur commande avancer. Comme Glovo mais avec VOS livreurs, zéro commission.</p>
            </div>
        </div>
    </div>
</section>


{{-- ╔════════════════════════════════╗
     ║  6. COMPARAISON CONCURRENCE    ║
     ╚════════════════════════════════╝ --}}
<section class="py-24 sm:py-28 bg-neutral-950 relative overflow-hidden">
    <div class="pointer-events-none absolute inset-0" style="background:radial-gradient(ellipse 80% 60% at 50% 0%,rgba(37,99,235,.08),transparent)"></div>
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="text-center mb-14 fu">
            <span class="text-xs font-black text-blue-500 uppercase tracking-widest">Pourquoi MenuPro ?</span>
            <h2 class="text-4xl sm:text-5xl font-black text-white mt-3 leading-tight">Glovo prend 30%.<br><span class="gt">Nous, zéro.</span></h2>
            <p class="text-neutral-500 text-lg mt-4">Comparez par vous-même.</p>
        </div>

        <div class="overflow-x-auto rounded-3xl border border-white/10 fu">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-white/10" style="background:rgba(37,99,235,.1)">
                        <th class="text-left py-5 px-6 text-sm font-bold text-neutral-400 w-1/3">Critère</th>
                        <th class="py-5 px-6 text-center text-sm font-black text-blue-400 w-1/4">MenuPro</th>
                        <th class="py-5 px-6 text-center text-sm font-bold text-neutral-500 w-1/4">Glovo / Yango</th>
                        <th class="py-5 px-6 text-center text-sm font-bold text-neutral-500 w-1/4">WhatsApp manuel</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach([
                        ['Commission par commande', '0%', '20-30%', '0%'],
                        ['Paiement mobile money', '✓ 4 opérateurs', '✓ partiel', '✗ manuel'],
                        ['Vos propres clients', '✓ vous les gardez', '✗ base Glovo', '✓'],
                        ['Analytics & rapports', '✓ complet', '✓ partiel', '✗'],
                        ['Stock & inventaire', '✓', '✗', '✗'],
                        ['Livraison propres livreurs', '✓', '✗ leurs livreurs', '✗'],
                        ['Mode hôtel QR chambre', '✓', '✗', '✗'],
                        ['Prix mensuel', '5 000 F/mois', '0 F + 20-30%/cmd', '0 F + pertes'],
                    ] as $row)
                    <tr class="border-b border-white/5 hover:bg-white/2 transition-colors">
                        <td class="py-4 px-6 text-sm text-neutral-400 font-medium">{{ $row[0] }}</td>
                        <td class="py-4 px-6 text-center text-sm font-bold {{ str_starts_with($row[1],'✓') || $row[1]==='0%' ? 'compare-yes' : 'text-white' }}">{{ $row[1] }}</td>
                        <td class="py-4 px-6 text-center text-sm {{ str_starts_with($row[2],'✗') || str_contains($row[2],'20') ? 'compare-no' : 'text-neutral-400' }}">{{ $row[2] }}</td>
                        <td class="py-4 px-6 text-center text-sm {{ str_starts_with($row[3],'✗') || str_contains($row[3],'pertes') ? 'compare-no' : 'text-neutral-400' }}">{{ $row[3] }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        <div class="mt-10 text-center fu d2">
            <a href="{{ route('register') }}" class="inline-flex items-center gap-2 px-8 py-4 bg-blue-600 hover:bg-blue-500 text-white font-black rounded-2xl shadow-xl shadow-blue-600/20 transition-all hover:-translate-y-0.5 text-lg">
                Passer à MenuPro maintenant
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
        </div>
    </div>
</section>


{{-- ╔════════════════════════════════╗
     ║  7. HOW IT WORKS               ║
     ╚════════════════════════════════╝ --}}
<section id="how-it-works" class="py-24 sm:py-32 bg-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-xl mx-auto mb-16 fu">
            <span class="text-xs font-black text-blue-600 uppercase tracking-widest">Comment ça marche</span>
            <h2 class="text-4xl sm:text-5xl font-black text-neutral-900 mt-3 leading-tight">En ligne en<br>15 minutes chrono</h2>
        </div>
        <div class="grid md:grid-cols-3 gap-6 fu">
            @foreach([
                ['01','Créez votre compte','Nom, email, téléphone. Votre espace est prêt en 2 minutes.','~2 min','bg-blue-600','border-blue-100'],
                ['02','Ajoutez votre menu','Photos, prix, catégories. Configurez horaires et paiements.','~10 min','bg-violet-600','border-violet-100'],
                ['03','Recevez des commandes','Partagez votre lien ou QR code. Commandes et paiements en direct.','Immédiat','bg-emerald-600','border-emerald-100'],
            ] as $i => $step)
            <div class="relative bg-white rounded-3xl p-8 border-2 {{ $step[5] }} hover:shadow-2xl hover:-translate-y-1 transition-all duration-300">
                <div class="flex items-start justify-between mb-6">
                    <span class="text-7xl font-black text-neutral-100 leading-none">{{ $step[0] }}</span>
                    <span class="text-xs font-black px-3 py-1.5 rounded-full text-white {{ $step[4] }}">{{ $step[3] }}</span>
                </div>
                <h3 class="text-xl font-black text-neutral-900 mb-3">{{ $step[1] }}</h3>
                <p class="text-neutral-500 text-sm leading-relaxed">{{ $step[2] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>


{{-- ╔════════════════════════════════╗
     ║  8. VIDÉO                      ║
     ╚════════════════════════════════╝ --}}
@if(!empty($videos))
<section class="py-24 bg-neutral-50">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14 fu">
            <span class="text-xs font-black text-red-500 uppercase tracking-widest">Vidéo</span>
            <h2 class="text-4xl sm:text-5xl font-black text-neutral-900 mt-3">Voyez MenuPro en action</h2>
        </div>
        <div class="grid md:grid-cols-{{ count($videos)>1?'2':'1' }} gap-8 fu">
            @foreach($videos as $v)
            <div>
                <div class="aspect-video bg-neutral-900 rounded-3xl overflow-hidden shadow-2xl border border-neutral-200">
                    <iframe src="{{ $v['url'] }}" title="{{ $v['title'] }}" class="w-full h-full" frameborder="0" allow="accelerometer;autoplay;clipboard-write;encrypted-media;gyroscope;picture-in-picture" allowfullscreen loading="lazy"></iframe>
                </div>
                @if($v['title'])<p class="mt-3 text-center font-bold text-neutral-700">{{ $v['title'] }}</p>@endif
            </div>
            @endforeach
        </div>
    </div>
</section>
@endif


{{-- ╔════════════════════════════════╗
     ║  9. TÉMOIGNAGES                ║
     ╚════════════════════════════════╝ --}}
<section class="py-24 sm:py-32 bg-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-xl mx-auto mb-16 fu">
            <span class="text-xs font-black text-blue-600 uppercase tracking-widest">Témoignages</span>
            <h2 class="text-4xl sm:text-5xl font-black text-neutral-900 mt-3 leading-tight">Ils en parlent<br>mieux que nous</h2>
        </div>
        @php
            $testimonials = \App\Models\SystemSetting::get('home_testimonials', [
                ['name'=>'Awa Koné','role'=>'Propriétaire, Maquis Chez Awa','city'=>'Daloa','text'=>'Depuis que j\'utilise MenuPro, mes clients commandent directement avec leur téléphone. Mon chiffre d\'affaires a augmenté de 30% en 2 mois.','avatar'=>'','stars'=>5],
                ['name'=>'Kouamé Jean','role'=>'Gérant, Restaurant Le Délice','city'=>'Abidjan','text'=>'Le QR code sur les tables a tout changé. Les clients scannent, commandent et paient par Wave. Je reçois l\'argent immédiatement sur mon compte.','avatar'=>'','stars'=>5],
                ['name'=>'Marie Touré','role'=>'Fondatrice, Saveurs d\'Afrique','city'=>'Bouaké','text'=>'À 15 000 F par mois, c\'est le meilleur investissement pour mon restaurant. Zéro commission, zéro surprise. Je recommande à 100%.','avatar'=>'','stars'=>5],
            ]);
        @endphp
        <div class="grid md:grid-cols-3 gap-6 fu">
            @foreach($testimonials as $t)
            <div class="bg-neutral-50 rounded-3xl p-7 border border-neutral-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col">
                <div class="flex gap-0.5 mb-5">
                    @for($i=0;$i<($t['stars']??5);$i++)<svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>@endfor
                </div>
                <p class="text-neutral-700 leading-relaxed flex-1 text-sm">"{{ $t['text'] }}"</p>
                <div class="flex items-center gap-3 mt-6 pt-5 border-t border-neutral-200">
                    @if(!empty($t['avatar']) && file_exists(public_path($t['avatar'])))<img src="{{ asset($t['avatar']) }}" alt="{{ $t['name'] }}" class="w-10 h-10 rounded-full object-cover">
                    @else<div class="w-10 h-10 rounded-full bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-black">{{ strtoupper(substr($t['name'],0,1)) }}</div>@endif
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


{{-- ╔════════════════════════════════╗
     ║  10. RESTAURANTS               ║
     ╚════════════════════════════════╝ --}}
<section class="py-16 bg-neutral-50 border-y border-neutral-100">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <p class="text-center text-xs font-black text-neutral-400 uppercase tracking-[0.25em] mb-10">Ils nous font confiance</p>
        @php
            $trs = \App\Models\Restaurant::where('status',\App\Enums\RestaurantStatus::ACTIVE)->whereNotNull('logo_path')->where('logo_path','!=','')->latest()->take(12)->get(['name','slug','logo_path','city']);
            if($trs->isEmpty()) $trs=\App\Models\Restaurant::where('status',\App\Enums\RestaurantStatus::ACTIVE)->latest()->take(8)->get(['name','slug','city']);
        @endphp
        <div class="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 gap-3 fu">
            @foreach($trs as $r)
            <a href="{{ route('r.menu',$r->slug) }}" target="_blank" class="group flex flex-col items-center gap-1.5 p-3 rounded-2xl bg-white border border-neutral-100 hover:border-blue-200 hover:shadow-md transition-all">
                @if(isset($r->logo_path) && $r->logo_path)<img src="{{ Storage::url($r->logo_path) }}" alt="{{ $r->name }}" class="w-10 h-10 rounded-xl object-cover group-hover:scale-110 transition-transform" loading="lazy">
                @else<div class="w-10 h-10 rounded-xl bg-gradient-to-br from-blue-500 to-indigo-600 flex items-center justify-center text-white font-black text-base group-hover:scale-110 transition-transform">{{ strtoupper(substr($r->name,0,1)) }}</div>@endif
                <span class="text-[10px] font-semibold text-neutral-600 truncate w-full text-center">{{ Str::limit($r->name,10) }}</span>
            </a>
            @endforeach
        </div>
    </div>
</section>


{{-- ╔════════════════════════════════╗
     ║  11. TARIFS                    ║
     ╚════════════════════════════════╝ --}}
<section id="pricing" class="py-24 sm:py-32 bg-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-xl mx-auto mb-16 fu">
            <span class="text-xs font-black text-blue-600 uppercase tracking-widest">Tarifs</span>
            <h2 class="text-4xl sm:text-5xl font-black text-neutral-900 mt-3 leading-tight">Simple. Transparent.<br>Sans commission.</h2>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5 fu">
            {{-- Stand --}}
            <div class="rounded-3xl p-7 border-2 border-orange-200 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 flex flex-col bg-white">
                <div class="text-2xl mb-3">🥪</div>
                <div class="text-xs font-black text-orange-500 uppercase tracking-widest mb-3">Stand</div>
                <div class="flex items-baseline gap-1 mb-1"><span class="text-4xl font-black text-neutral-900">5 000</span><span class="text-neutral-400 text-sm">F/mois</span></div>
                <p class="text-sm text-neutral-400 mb-6">Vendeurs de rue et stands</p>
                <ul class="space-y-2.5 flex-1 mb-8">@foreach(['15 plats','100 cmd/mois','QR code','Wave & Orange Money','Sans PC requis'] as $f)<li class="flex items-center gap-2 text-sm text-neutral-700"><svg class="w-4 h-4 text-orange-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>{{ $f }}</li>@endforeach</ul>
                <a href="{{ route('register') }}?plan=stand" class="block w-full text-center py-3.5 rounded-2xl font-black text-sm bg-orange-50 text-orange-700 hover:bg-orange-100 border border-orange-200 transition-all">Essai 7j gratuit</a>
            </div>
            {{-- Essentiel --}}
            <div class="rounded-3xl p-7 border-2 border-neutral-200 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 flex flex-col bg-white">
                <div class="text-2xl mb-3">🍽️</div>
                <div class="text-xs font-black text-neutral-400 uppercase tracking-widest mb-3">Essentiel</div>
                <div class="flex items-baseline gap-1 mb-1"><span class="text-4xl font-black text-neutral-900">15 000</span><span class="text-neutral-400 text-sm">F/mois</span></div>
                <p class="text-sm text-neutral-400 mb-6">Petits maquis et restaurants</p>
                <ul class="space-y-2.5 flex-1 mb-8">@foreach(['25 plats, 8 catégories','200 cmd/mois','Mobile Money + QR','Support WhatsApp'] as $f)<li class="flex items-center gap-2 text-sm text-neutral-700"><svg class="w-4 h-4 text-emerald-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>{{ $f }}</li>@endforeach</ul>
                <a href="{{ route('register') }}?plan=essentiel" class="block w-full text-center py-3.5 rounded-2xl font-black text-sm bg-neutral-100 text-neutral-800 hover:bg-neutral-200 transition-all">Essai 7j gratuit</a>
            </div>
            {{-- Pro --}}
            <div class="rounded-3xl p-7 border-2 border-blue-500 shadow-2xl shadow-blue-100 relative hover:shadow-3xl hover:-translate-y-1 transition-all duration-300 flex flex-col" style="background:linear-gradient(160deg,#1d4ed8,#2563eb)">
                <div class="absolute -top-4 left-1/2 -translate-x-1/2 bg-white text-blue-600 text-xs font-black px-4 py-1.5 rounded-full shadow-lg">⭐ Populaire</div>
                <div class="text-2xl mb-3">🚀</div>
                <div class="text-xs font-black text-blue-200 uppercase tracking-widest mb-3">Pro</div>
                <div class="flex items-baseline gap-1 mb-1"><span class="text-4xl font-black text-white">25 000</span><span class="text-blue-200 text-sm">F/mois</span></div>
                <p class="text-sm text-blue-200 mb-6">Stock, livraison, analytics</p>
                <ul class="space-y-2.5 flex-1 mb-8">@foreach(['80 plats, 3 employés','1 000 cmd/mois','Stock complet','Livraison intégrée','Analytics & rapports'] as $f)<li class="flex items-center gap-2 text-sm text-white"><svg class="w-4 h-4 text-blue-200 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>{{ $f }}</li>@endforeach</ul>
                <a href="{{ route('register') }}?plan=pro" class="block w-full text-center py-3.5 rounded-2xl font-black text-sm bg-white text-blue-700 hover:bg-blue-50 shadow-lg transition-all">Essai 7j gratuit</a>
            </div>
            {{-- Gold --}}
            <div class="rounded-3xl p-7 border-2 border-purple-800/50 shadow-xl relative hover:shadow-2xl hover:-translate-y-0.5 transition-all duration-300 flex flex-col" style="background:linear-gradient(160deg,#0f172a,#1e1b4b)">
                <div class="text-2xl mb-3">✨</div>
                <div class="text-xs font-black text-purple-400 uppercase tracking-widest mb-3">Gold</div>
                <div class="flex items-baseline gap-1 mb-1"><span class="text-4xl font-black" style="background:linear-gradient(135deg,#c084fc,#818cf8);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text">85 000</span><span class="text-neutral-500 text-sm">F/mois</span></div>
                <p class="text-sm text-neutral-500 mb-6">Multi-espaces, hôtels, VIP</p>
                <ul class="space-y-2.5 flex-1 mb-8">@foreach(['Multi-espaces illimités','PIN serveurs','Rapports par espace','QR chambres hôtel','Formation perso'] as $f)<li class="flex items-center gap-2 text-sm text-neutral-300"><svg class="w-4 h-4 text-purple-400 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>{{ $f }}</li>@endforeach</ul>
                <a href="{{ route('register') }}?plan=gold" class="block w-full text-center py-3.5 rounded-2xl font-black text-sm text-white hover:opacity-90 transition-all" style="background:linear-gradient(135deg,#7c3aed,#4f46e5)">Essai 7j gratuit</a>
            </div>
        </div>
        <p class="text-center text-neutral-400 text-sm mt-8">Sans engagement · Sans carte bancaire · <a href="{{ route('pricing') }}" class="text-blue-500 font-semibold hover:text-blue-400">Comparer tous les plans →</a></p>
    </div>
</section>


{{-- ╔════════════════════════════════╗
     ║  12. FAQ                       ║
     ╚════════════════════════════════╝ --}}
<section class="py-24 sm:py-28 bg-neutral-50">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14 fu">
            <span class="text-xs font-black text-blue-600 uppercase tracking-widest">FAQ</span>
            <h2 class="text-4xl sm:text-5xl font-black text-neutral-900 mt-3">Questions fréquentes</h2>
        </div>
        @php
            $faqs = [
                ['Est-ce que mes clients ont besoin de télécharger une app ?', 'Non. Vos clients scannent simplement le QR code ou cliquent sur votre lien. Tout fonctionne directement dans le navigateur de leur téléphone, sans installation.'],
                ['Comment fonctionne le paiement Mobile Money ?', 'Vos clients paient directement depuis leur app Wave, Orange Money, MTN ou Moov. L\'argent est envoyé directement sur votre numéro de téléphone mobile money — pas de délai, pas d\'intermédiaire.'],
                ['Combien de temps pour être en ligne ?', 'En moins de 15 minutes. Créez votre compte, ajoutez quelques plats avec photos et prix, et partagez votre lien ou imprimez votre QR code. C\'est tout.'],
                ['Est-ce qu\'il y a des commissions sur les commandes ?', 'Aucune commission. Vous payez un forfait mensuel fixe (à partir de 5 000 F/mois) et vous gardez 100% de vos ventes. Contrairement à Glovo ou Yango qui prennent 20 à 30%.'],
                ['Que se passe-t-il si j\'ai des problèmes ?', 'Vous avez accès au support WhatsApp inclus dans tous les plans. Pour les plans Pro et Gold, vous bénéficiez d\'une assistance prioritaire et d\'une formation personnalisée.'],
                ['Puis-je annuler à tout moment ?', 'Oui, sans engagement et sans frais de résiliation. Vous pouvez annuler votre abonnement quand vous voulez depuis votre compte.'],
            ];
        @endphp
        <div class="space-y-3 fu">
            @foreach($faqs as $i => $faq)
            <div class="bg-white rounded-2xl border border-neutral-100 overflow-hidden" x-data="{ open: {{ $i === 0 ? 'true' : 'false' }} }">
                <button @click="open = !open" class="w-full flex items-center justify-between px-6 py-5 text-left font-black text-neutral-900 hover:bg-neutral-50 transition-colors">
                    <span>{{ $faq[0] }}</span>
                    <svg class="w-5 h-5 text-neutral-400 shrink-0 transition-transform duration-300" :class="open && 'rotate-45'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                </button>
                <div x-show="open" x-collapse>
                    <p class="px-6 pb-5 text-neutral-500 leading-relaxed text-sm">{{ $faq[1] }}</p>
                </div>
            </div>
            @endforeach
        </div>
        <div class="text-center mt-10 fu d2">
            <a href="{{ route('faq') }}" class="text-blue-600 hover:text-blue-700 font-bold text-sm">Voir toutes les questions →</a>
        </div>
    </div>
</section>


{{-- ╔════════════════════════════════╗
     ║  13. CTA FINAL                 ║
     ╚════════════════════════════════╝ --}}
<section class="relative py-24 sm:py-32 overflow-hidden" style="background:#030712">
    <div class="pointer-events-none absolute inset-0" style="background:radial-gradient(ellipse 80% 70% at 50% 50%,rgba(37,99,235,.2),transparent)"></div>
    <div class="pointer-events-none absolute inset-0 hero-grid opacity-50"></div>

    <div class="relative z-10 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center fu">
        <div class="inline-flex items-center gap-2 px-4 py-1.5 bg-blue-500/10 border border-blue-500/20 rounded-full text-blue-400 text-sm font-semibold mb-8">
            <span class="relative flex h-2 w-2 shrink-0"><span class="ps absolute inline-flex h-full w-full rounded-full bg-emerald-400"></span><span class="relative inline-flex h-2 w-2 rounded-full bg-emerald-400"></span></span>
            {{ $stats['raw']['restaurants'] ?? '0' }} restaurants actifs ce soir
        </div>

        <h2 class="text-5xl sm:text-6xl lg:text-7xl font-black text-white leading-[1.02] tracking-tight">
            Votre prochain<br>client commande<br><span class="gt">dans 15 minutes.</span>
        </h2>
        <p class="text-xl text-white/50 mt-6 max-w-lg mx-auto leading-relaxed">
            Rejoignez les restaurateurs ivoiriens qui encaissent en direct sur leur Mobile Money.
        </p>

        <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="{{ route('register') }}" class="group w-full sm:w-auto inline-flex items-center justify-center gap-2.5 px-10 py-4.5 bg-blue-600 hover:bg-blue-500 text-white font-black rounded-2xl shadow-2xl shadow-blue-600/30 transition-all duration-200 hover:-translate-y-0.5 text-lg py-4">
                Créer mon restaurant — C'est gratuit
                <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
            <a href="{{ route('contact') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-4 text-white/70 hover:text-white font-semibold rounded-2xl border border-white/10 hover:bg-white/5 transition-all text-base">
                Parler à un expert WhatsApp
            </a>
        </div>

        <div class="mt-12 flex flex-wrap items-center justify-center gap-6 text-sm text-neutral-600">
            @foreach(['Configuration en 15 min','Support WhatsApp inclus','A partir de 5 000 F/mois','Annulation libre à tout moment'] as $t)
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
function counter(target){return{count:0,target:target,displayCount:'0',startCount(){const s=40,d=1600,sv=this.target/s,sd=d/s;const iv=setInterval(()=>{this.count+=sv;if(this.count>=this.target){this.count=this.target;clearInterval(iv);}this.displayCount=this.count>=1000?Math.round(this.count/1000)+'K+':Math.round(this.count).toString();},sd);}}}
const io=new IntersectionObserver(e=>{e.forEach(x=>{if(x.isIntersecting){x.target.classList.add('in');io.unobserve(x.target);}});},{threshold:.08});
document.querySelectorAll('.fu').forEach(el=>io.observe(el));
</script>
@endpush
</x-layouts.public>
