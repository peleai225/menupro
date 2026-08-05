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

/* Hero bg */
.hero-bg{background:linear-gradient(160deg,#161616 0%,#1a1a1a 60%,#1f1208 100%)}

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
     1. HERO — Fond noir, accent orange
══════════════════════════════════════ --}}
<section class="hero-bg relative min-h-screen flex flex-col justify-center overflow-hidden">

    {{-- Déco orbs --}}
    <div class="pointer-events-none absolute top-0 right-0 w-[500px] h-[500px] rounded-full blur-3xl opacity-20" style="background:radial-gradient(circle,#D45E0C,transparent 70%)"></div>
    <div class="pointer-events-none absolute bottom-0 left-0 w-[300px] h-[300px] rounded-full blur-3xl opacity-10" style="background:radial-gradient(circle,#D45E0C,transparent 70%)"></div>

    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pt-20 pb-28 w-full">
        <div class="grid lg:grid-cols-2 gap-12 lg:gap-16 items-center">

            {{-- Copy --}}
            <div class="text-center lg:text-left">
                <div class="inline-flex items-center gap-2 px-4 py-1.5 border border-primary-500/40 bg-primary-500/10 rounded-full text-primary-300 text-sm font-bold mb-8">
                    <span class="relative flex h-2 w-2 shrink-0"><span class="ps absolute inline-flex h-full w-full rounded-full bg-primary-400"></span><span class="relative inline-flex h-2 w-2 rounded-full bg-primary-400"></span></span>
                    #Top Solution Restaurants CI · 7 jours gratuits
                </div>

                <h1 class="text-5xl sm:text-6xl lg:text-7xl font-black text-white leading-[1.02] tracking-tight">
                    The Ultimate<br>
                    <span class="gt">Restaurant</span><br>
                    Experience!
                </h1>

                <p class="mt-6 text-xl text-white/55 max-w-xl mx-auto lg:mx-0 leading-relaxed">
                    Digitalisez votre restaurant en <strong class="text-white">15 minutes</strong>. Menu en ligne, QR codes, commandes temps réel, paiements <strong class="text-white">Wave · Orange · MTN · Moov</strong> — argent sur votre compte.
                </p>

                <div class="mt-10 flex flex-col sm:flex-row items-stretch sm:items-center gap-3 justify-center lg:justify-start">
                    <a href="{{ route('register') }}" class="group inline-flex items-center justify-center gap-2.5 px-8 py-4 font-black rounded-2xl text-white transition-all duration-200 hover:-translate-y-0.5 hover:shadow-xl text-lg glow-orange" style="background:#D45E0C">
                        Créer mon restaurant — Gratuit
                        <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    </a>
                    <a href="{{ route('r.menu', ['slug' => 'demo']) }}" target="_blank" class="inline-flex items-center justify-center gap-2.5 px-8 py-4 text-white/70 hover:text-white font-semibold rounded-2xl border border-white/15 hover:bg-white/8 transition-all text-lg">
                        <svg class="w-5 h-5 text-primary-400" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                        Voir la démo live
                    </a>
                </div>

                {{-- Social proof avatars --}}
                <div class="mt-10 flex items-center gap-4 justify-center lg:justify-start">
                    <div class="flex -space-x-3">
                        @foreach(['A','K','M','F','S'] as $l)
                        <div class="w-9 h-9 rounded-full border-2 border-neutral-900 flex items-center justify-center text-white text-xs font-black" style="background:{{ ['#D45E0C','#22c55e','#3b82f6','#a855f7','#f59e0b'][$loop->index] }}">{{ $l }}</div>
                        @endforeach
                    </div>
                    <div>
                        <div class="text-white font-black text-sm">{{ $stats['restaurants'] }}+ restaurants actifs</div>
                        <div class="text-white/40 text-xs">{{ $stats['orders'] }} commandes traitées</div>
                    </div>
                </div>
            </div>

            {{-- Mockup --}}
            <div class="relative flex justify-center lg:justify-end">
                <div class="pointer-events-none absolute inset-0 flex items-center justify-center">
                    <div class="w-80 h-80 rounded-full blur-3xl opacity-25" style="background:#D45E0C"></div>
                </div>

                @php $heroImage = \App\Models\SystemSetting::get('hero_image',''); @endphp
                @if($heroImage && \Illuminate\Support\Facades\Storage::disk('public')->exists($heroImage))
                    <img src="{{ \Illuminate\Support\Facades\Storage::url($heroImage) }}" alt="MenuPro" class="relative w-full max-w-[310px] sm:max-w-[350px] rounded-3xl shadow-2xl" loading="eager" width="350" height="680">
                @else
                <div class="relative w-[280px] sm:w-[310px]">
                    {{-- Phone --}}
                    <div class="bg-neutral-950 rounded-[2.5rem] p-1.5 shadow-2xl ring-1 ring-white/5">
                        <div class="rounded-[2.2rem] overflow-hidden bg-white">
                            {{-- Header app --}}
                            <div class="px-4 pt-10 pb-4 text-white" style="background:#D45E0C">
                                <div class="flex items-center justify-between mb-1">
                                    <div class="flex items-center gap-2.5">
                                        <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center text-lg">🍽️</div>
                                        <div>
                                            <div class="font-black text-sm">Maquis Chez Awa</div>
                                            <div class="flex items-center gap-1 text-[11px] text-white/75"><span class="w-1.5 h-1.5 rounded-full bg-green-300"></span>Ouvert · 08h–22h</div>
                                        </div>
                                    </div>
                                    <div class="w-9 h-9 bg-white/15 rounded-xl flex items-center justify-center">
                                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
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
                            {{-- Cats --}}
                            <div class="flex gap-2 px-3 pt-2.5 pb-1 overflow-x-hidden">
                                @foreach([['🔥 Populaires',true],['Plats',false],['Boissons',false]] as $c)
                                <span class="px-3 py-1.5 text-[11px] font-black rounded-full whitespace-nowrap shrink-0 {{ $c[1] ? 'text-white' : 'bg-neutral-100 text-neutral-500' }}" @if($c[1]) style="background:#D45E0C" @endif>{{ $c[0] }}</span>
                                @endforeach
                            </div>
                            {{-- Items --}}
                            <div class="px-3 py-2 space-y-2">
                                @foreach([['🍗','Poulet Braisé','Avec alloco et sauce','5 500 F','#fef3c7'],['🐟','Attieké Poisson','Légumes frais','4 500 F','#dcfce7'],['🥤','Jus Bissap','Naturel & frais','1 500 F','#fce7f3']] as $d)
                                <div class="bg-white rounded-2xl p-2.5 flex gap-2.5 shadow-sm border border-neutral-100">
                                    <div class="w-12 h-12 rounded-xl shrink-0 flex items-center justify-center text-2xl" style="background:{{ $d[4] }}">{{ $d[0] }}</div>
                                    <div class="flex-1 min-w-0">
                                        <div class="font-black text-xs text-neutral-800">{{ $d[1] }}</div>
                                        <div class="text-[10px] text-neutral-400">{{ $d[2] }}</div>
                                        <div class="font-black text-xs mt-1" style="color:#D45E0C">{{ $d[3] }}</div>
                                    </div>
                                    <button class="w-7 h-7 rounded-xl flex items-center justify-center text-white font-black text-base self-center shrink-0" style="background:#D45E0C">+</button>
                                </div>
                                @endforeach
                            </div>
                            {{-- Cart --}}
                            <div class="px-3 pb-4 pt-1">
                                <div class="text-white rounded-2xl px-4 py-3 flex items-center justify-between shadow-lg" style="background:#D45E0C">
                                    <div class="flex items-center gap-2">
                                        <span class="w-6 h-6 bg-white/20 rounded-lg flex items-center justify-center text-[11px] font-black">3</span>
                                        <span class="text-xs font-bold">Voir panier</span>
                                    </div>
                                    <span class="font-black text-sm">11 500 F</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Notif Wave --}}
                    <div class="fl absolute -top-5 -right-10 bg-white rounded-2xl shadow-2xl border border-neutral-100 px-3.5 py-3 flex items-center gap-2.5 w-52">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 bg-sky-50">
                            <img src="{{ asset('images/payments/wave.png') }}" class="w-5 h-5 object-contain" alt="Wave">
                        </div>
                        <div>
                            <div class="text-xs font-black text-neutral-800">Paiement reçu ✓</div>
                            <div class="text-[11px] text-neutral-500">Wave · 5 500 F → votre compte</div>
                        </div>
                    </div>

                    {{-- Notif commande --}}
                    <div class="fl2 absolute -bottom-5 -left-10 bg-white rounded-2xl shadow-2xl border border-neutral-100 px-3.5 py-3 flex items-center gap-2.5 w-48">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center shrink-0 text-lg" style="background:#fef3c7">🔔</div>
                        <div>
                            <div class="text-xs font-black text-neutral-800">Nouvelle commande</div>
                            <div class="text-[11px] text-neutral-500">Table 7 · Poulet braisé</div>
                        </div>
                    </div>
                </div>
                @endif
            </div>
        </div>
    </div>

    {{-- Wave --}}
    <div class="absolute bottom-0 left-0 right-0 leading-none">
        <svg viewBox="0 0 1440 60" preserveAspectRatio="none" class="w-full h-14" fill="white"><path d="M0,60 C400,10 800,50 1200,15 C1340,0 1400,30 1440,20 L1440,60Z"/></svg>
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
                ['🥪','Stand & Street food','Vendeurs de rue, paninis, tacos, jus. MenuPro sur votre téléphone.','border-orange-200 bg-orange-50','text-orange-600','5 000 F/mois'],
                ['🍽️','Maquis & Restaurant','Tables, QR codes, commandes cuisine, alertes sonores.','border-neutral-200 bg-white','text-neutral-600','15 000 F/mois'],
                ['🏨','Hôtel & Résidence','QR par chambre, room service, voix IA pour le personnel.','border-primary-200','text-primary-600','Gold'],
                ['🛵','Livraison intégrée','Vos livreurs, suivi temps réel. 0% de commission.','border-secondary-200 bg-secondary-50','text-secondary-600','Pro'],
            ] as $who)
            <div class="rounded-3xl p-6 border-2 {{ $who[3] }} hover:-translate-y-1 hover:shadow-xl transition-all duration-300">
                <div class="text-4xl mb-4">{{ $who[0] }}</div>
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
                Enjoy <span class="gt">Exclusive Benefits</span><br>with MenuPro
            </h2>
        </div>

        <div class="grid md:grid-cols-3 gap-5 fu">
            @foreach([
                ['📱','Commandes en direct','Vos clients commandent depuis leur téléphone. QR code sur les tables, lien WhatsApp. Alerte sonore instantanée, écran cuisine dédié.'],
                ['💳','Paiement Mobile Money','Wave, Orange Money, MTN, Moov. L\'argent arrive directement sur votre compte sans délai ni intermédiaire.'],
                ['📊','Analytics & Rapports','Bilan journalier par heure de caisse, CA espèces vs mobile money, plats les plus vendus, taux d\'annulation.'],
                ['📦','Gestion de stock','Alertes de rupture automatiques, mouvements d\'inventaire, gestion des ingrédients. Plus jamais à court.'],
                ['🏨','Mode Hôtel','QR par chambre, room service, voix IA qui annonce les commandes. Appel addition, appel ménage.'],
                ['🛵','Livraison intégrée','Gérez vos propres livreurs avec suivi temps réel. Vos clients voient leur commande avancer. Zéro commission.'],
            ] as $i => $f)
            <div class="bg-neutral-900 rounded-3xl p-7 border border-neutral-800 hover:border-primary-500/40 hover:-translate-y-1 hover:shadow-2xl transition-all duration-300 group fu" style="transition-delay:{{ $i*0.08 }}s">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-3xl mb-5 group-hover:scale-110 transition-transform" style="background:rgba(212,94,12,.15);border:1px solid rgba(212,94,12,.2)">{{ $f[0] }}</div>
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
                <div class="text-3xl mb-4">🥪</div>
                <div class="text-xs font-black uppercase tracking-widest mb-3" style="color:#D45E0C">Stand</div>
                <div class="flex items-baseline gap-1 mb-1"><span class="text-4xl font-black text-neutral-900">5 000</span><span class="text-neutral-400 text-sm">F/mois</span></div>
                <p class="text-sm text-neutral-400 mb-6">Vendeurs de rue et stands</p>
                <ul class="space-y-2.5 flex-1 mb-8">@foreach(['15 plats','100 cmd/mois','QR code inclus','Wave & Orange Money','Sans PC requis'] as $f)<li class="flex items-center gap-2 text-sm text-neutral-700"><svg class="w-4 h-4 shrink-0" style="color:#D45E0C" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>{{ $f }}</li>@endforeach</ul>
                <a href="{{ route('register') }}?plan=stand" class="block w-full text-center py-3.5 rounded-2xl font-black text-sm transition-all border-2" style="border-color:#D45E0C;color:#D45E0C;background:rgba(212,94,12,.06)" onmouseover="this.style.background='rgba(212,94,12,.12)'" onmouseout="this.style.background='rgba(212,94,12,.06)'">Essai 7j gratuit</a>
            </div>
            {{-- Essentiel --}}
            <div class="bg-white rounded-3xl p-7 border-2 border-neutral-200 hover:border-primary-300 hover:shadow-xl hover:-translate-y-0.5 transition-all duration-300 flex flex-col">
                <div class="text-3xl mb-4">🍽️</div>
                <div class="text-xs font-black text-neutral-400 uppercase tracking-widest mb-3">Essentiel</div>
                <div class="flex items-baseline gap-1 mb-1"><span class="text-4xl font-black text-neutral-900">15 000</span><span class="text-neutral-400 text-sm">F/mois</span></div>
                <p class="text-sm text-neutral-400 mb-6">Maquis et petits restaurants</p>
                <ul class="space-y-2.5 flex-1 mb-8">@foreach(['25 plats, 8 catégories','200 cmd/mois','Mobile Money + QR','Support WhatsApp'] as $f)<li class="flex items-center gap-2 text-sm text-neutral-700"><svg class="w-4 h-4 text-secondary-500 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>{{ $f }}</li>@endforeach</ul>
                <a href="{{ route('register') }}?plan=essentiel" class="block w-full text-center py-3.5 rounded-2xl font-black text-sm bg-neutral-100 text-neutral-800 hover:bg-neutral-200 transition-all">Essai 7j gratuit</a>
            </div>
            {{-- Pro (featured) --}}
            <div class="rounded-3xl p-7 border-2 shadow-2xl relative hover:-translate-y-1 transition-all duration-300 flex flex-col text-white" style="background:#161616;border-color:#D45E0C;box-shadow:0 0 40px rgba(212,94,12,.2)">
                <div class="absolute -top-4 left-1/2 -translate-x-1/2 text-white text-xs font-black px-5 py-1.5 rounded-full shadow-lg" style="background:#D45E0C">⭐ Populaire</div>
                <div class="text-3xl mb-4">🚀</div>
                <div class="text-xs font-black uppercase tracking-widest mb-3" style="color:#D45E0C">Pro</div>
                <div class="flex items-baseline gap-1 mb-1"><span class="text-4xl font-black text-white">25 000</span><span class="text-neutral-400 text-sm">F/mois</span></div>
                <p class="text-sm text-neutral-400 mb-6">Stock, livraison, analytics</p>
                <ul class="space-y-2.5 flex-1 mb-8">@foreach(['80 plats, 3 employés','1 000 cmd/mois','Stock complet','Livraison intégrée','Analytics & rapports'] as $f)<li class="flex items-center gap-2 text-sm text-white"><svg class="w-4 h-4 shrink-0" style="color:#D45E0C" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>{{ $f }}</li>@endforeach</ul>
                <a href="{{ route('register') }}?plan=pro" class="block w-full text-center py-3.5 rounded-2xl font-black text-sm text-white transition-all hover:opacity-90" style="background:#D45E0C">Essai 7j gratuit</a>
            </div>
            {{-- Gold --}}
            <div class="rounded-3xl p-7 border-2 border-neutral-800 shadow-xl hover:-translate-y-0.5 hover:shadow-2xl transition-all duration-300 flex flex-col" style="background:#0f172a">
                <div class="text-3xl mb-4">✨</div>
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
