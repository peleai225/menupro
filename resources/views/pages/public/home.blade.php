<x-layouts.public title="MenuPro — Solution #1 pour restaurants en Côte d'Ivoire" description="MenuPro : digitalisez votre restaurant en 15 minutes. Menu en ligne, QR codes, paiements Wave · Orange · MTN · Moov. Zéro commission.">
@push('head')
<link rel="preconnect" href="https://fonts.googleapis.com">
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
<link href="https://fonts.googleapis.com/css2?family=Bricolage+Grotesque:opsz,wght@12..96,400;12..96,600;12..96,700;12..96,800&display=swap" rel="stylesheet">
<script type="application/ld+json">{"@@context":"https://schema.org","@@type":"SoftwareApplication","name":"MenuPro","applicationCategory":"BusinessApplication","operatingSystem":"Web","url":"{{ url('/') }}","description":"Plateforme SaaS de commande en ligne pour restaurants en Cote d'Ivoire.","offers":{"@@type":"Offer","price":"5000","priceCurrency":"XOF"}}</script>
<style>
:root{--o:#D45E0C;--o2:#FF8C42;--dark:#080808;--dark2:#111111}
.fd{font-family:'Bricolage Grotesque',sans-serif}
.fu{opacity:0;transform:translateY(26px);transition:opacity .65s cubic-bezier(.22,1,.36,1),transform .65s cubic-bezier(.22,1,.36,1)}
.fu.in{opacity:1;transform:none}
.fu.d1{transition-delay:.1s}.fu.d2{transition-delay:.2s}.fu.d3{transition-delay:.3s}.fu.d4{transition-delay:.4s}
.gt{background:linear-gradient(135deg,#D45E0C,#FF8C42);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;padding-right:.08em;display:inline-block}
.gt-gold{background:linear-gradient(135deg,#f6b285,#D45E0C);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text;padding-right:.08em;display:inline-block}
@keyframes fl{0%,100%{transform:translateY(0)}50%{transform:translateY(-10px)}}
@keyframes fl2{0%,100%{transform:translateY(0)}50%{transform:translateY(-6px)}}
@keyframes pr{0%,100%{transform:scale(1);opacity:.5}50%{transform:scale(1.9);opacity:0}}
@keyframes sp{from{transform:rotate(0)}to{transform:rotate(360deg)}}
@keyframes tk{from{transform:translateX(0)}to{transform:translateX(-50%)}}
.fl{animation:fl 5s ease-in-out infinite}
.fl2{animation:fl2 6s ease-in-out 1.5s infinite}
.pr{animation:pr 2.5s ease-in-out infinite}
.sp{animation:sp 14s linear infinite}
.tk-w{overflow:hidden;-webkit-mask-image:linear-gradient(90deg,transparent,#000 8%,#000 92%,transparent);mask-image:linear-gradient(90deg,transparent,#000 8%,#000 92%,transparent)}
.tk-t{display:flex;width:max-content;animation:tk 28s linear infinite}.tk-t:hover{animation-play-state:paused}
.glow{box-shadow:0 0 50px rgba(212,94,12,.3),0 0 100px rgba(212,94,12,.08)}
</style>
@endpush

{{-- ══════════ 1. HERO ══════════ --}}
<section class="relative overflow-hidden" style="background:var(--dark);min-height:100vh">
    <div class="pointer-events-none absolute inset-0">
        <div class="absolute -top-48 -left-48 w-[700px] h-[700px] rounded-full" style="background:radial-gradient(circle,rgba(212,94,12,.14) 0%,transparent 60%)"></div>
        <div class="absolute inset-0 opacity-[0.025]" style="background-image:linear-gradient(rgba(255,255,255,.15) 1px,transparent 1px),linear-gradient(90deg,rgba(255,255,255,.15) 1px,transparent 1px);background-size:56px 56px"></div>
    </div>

    <div class="relative z-10 max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex items-center" style="min-height:100vh">
        <div class="grid lg:grid-cols-2 gap-10 lg:gap-16 items-center w-full py-24 lg:py-0">

            {{-- Copy --}}
            <div class="order-2 lg:order-1 text-center lg:text-left">
                <div class="inline-flex items-center gap-2.5 px-4 py-2 rounded-full text-sm font-bold mb-8 border fu" style="background:rgba(212,94,12,.1);border-color:rgba(212,94,12,.25);color:#FF8C42">
                    <span class="relative flex h-2 w-2 shrink-0">
                        <span class="pr absolute inline-flex h-full w-full rounded-full" style="background:#D45E0C"></span>
                        <span class="relative inline-flex h-2 w-2 rounded-full" style="background:#D45E0C"></span>
                    </span>
                    {{ $stats['restaurants'] }} restaurants actifs en ce moment
                </div>

                <h1 class="fd text-5xl sm:text-6xl lg:text-[4rem] xl:text-[4.6rem] font-extrabold text-white leading-[1.03] tracking-tight fu d1">
                    La solution<br>
                    <span class="gt">#1 pour</span><br>
                    les restaurants
                </h1>

                <p class="mt-6 text-lg text-neutral-400 max-w-lg mx-auto lg:mx-0 leading-relaxed fu d2">
                    Menu en ligne, QR codes, paiements <strong class="text-white">Wave · Orange · MTN · Moov</strong> — l'argent directement sur votre compte en moins de 15 minutes.
                </p>

                <div class="mt-9 flex flex-col sm:flex-row items-stretch sm:items-center gap-3 justify-center lg:justify-start fu d2">
                    <a href="{{ route('register') }}" class="group inline-flex items-center justify-center gap-2 px-8 py-4 font-extrabold rounded-2xl text-white text-base transition-all hover:-translate-y-0.5 glow" style="background:linear-gradient(135deg,#D45E0C,#b84e0a)">
                        Démarrer — C'est gratuit
                        <svg class="w-4 h-4 group-hover:translate-x-0.5 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    </a>
                    <a href="{{ route('r.menu', ['slug' => 'demo']) }}" target="_blank" class="inline-flex items-center justify-center gap-2 px-7 py-4 font-semibold text-white/60 hover:text-white rounded-2xl border transition-all text-base" style="border-color:rgba(255,255,255,.1);background:rgba(255,255,255,.04)">
                        <svg class="w-4 h-4" style="color:#D45E0C" fill="currentColor" viewBox="0 0 24 24"><path d="M8 5v14l11-7z"/></svg>
                        Voir la démo live
                    </a>
                </div>

                @php
                    $proofResto = \App\Models\Restaurant::where('status', \App\Enums\RestaurantStatus::ACTIVE)->whereNotNull('logo_path')->where('logo_path','!=','')->latest()->take(5)->get(['name','logo_path']);
                    $hasLogos = $proofResto->count() >= 3;
                @endphp
                <div class="mt-10 flex flex-wrap items-center gap-6 justify-center lg:justify-start fu d3">
                    <div class="flex items-center gap-3">
                        <div class="flex -space-x-2">
                            @if($hasLogos)
                                @foreach($proofResto as $r)
                                <div class="w-9 h-9 rounded-full overflow-hidden border-2 border-neutral-800"><img src="{{ \Illuminate\Support\Facades\Storage::url($r->logo_path) }}" alt="{{ $r->name }}" class="w-full h-full object-cover"></div>
                                @endforeach
                            @else
                                @php $cols=['#D45E0C','#22c55e','#3b82f6','#a855f7','#f59e0b'];$fb=\App\Models\Restaurant::where('status',\App\Enums\RestaurantStatus::ACTIVE)->latest()->take(5)->get(['name']); @endphp
                                @foreach($fb as $r)<div class="w-9 h-9 rounded-full border-2 border-neutral-800 flex items-center justify-center text-white text-xs font-black" style="background:{{ $cols[$loop->index%5] }}">{{ strtoupper(substr($r->name,0,1)) }}</div>@endforeach
                            @endif
                        </div>
                        <div>
                            <div class="flex gap-0.5 mb-0.5">@for($i=0;$i<5;$i++)<svg class="w-3 h-3 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>@endfor</div>
                            <div class="text-xs font-black text-white">{{ $stats['restaurants'] }}+ restaurants actifs</div>
                        </div>
                    </div>
                    <div class="w-px h-8 bg-neutral-800 hidden sm:block"></div>
                    <div>
                        <div class="text-xl font-extrabold text-white leading-none fd" x-data="counter({{ $stats['raw']['orders'] }})" x-intersect.once="startCount()"><span x-text="displayCount"></span></div>
                        <div class="text-xs text-neutral-500 mt-0.5">Commandes traitées</div>
                    </div>
                    <div class="w-px h-8 bg-neutral-800 hidden sm:block"></div>
                    <div>
                        <div class="text-xl font-extrabold fd leading-none" style="color:#D45E0C">5 000 F</div>
                        <div class="text-xs text-neutral-500 mt-0.5">À partir de / mois</div>
                    </div>
                </div>
            </div>

            {{-- Hero visual: images carousel OR phone mockup --}}
            @php
                $heroRaw = [
                    \App\Models\SystemSetting::get('hero_image', ''),
                    \App\Models\SystemSetting::get('hero_image_2', ''),
                    \App\Models\SystemSetting::get('hero_image_3', ''),
                ];
                $heroImagesUrls = array_values(array_filter(array_map(function($p) {
                    return ($p && \Illuminate\Support\Facades\Storage::disk('public')->exists($p))
                        ? \Illuminate\Support\Facades\Storage::url($p)
                        : null;
                }, $heroRaw)));
                $hasHeroImages = count($heroImagesUrls) > 0;
            @endphp

            <div class="relative flex items-center justify-center order-1 lg:order-2 fu d1">
                <div class="absolute inset-0 pointer-events-none" style="background:radial-gradient(circle at 50% 50%,rgba(212,94,12,.1),transparent 65%)"></div>

            @if($hasHeroImages)
                {{-- Hero images carousel --}}
                <div class="relative w-full max-w-sm lg:max-w-md"
                     x-data="{ active: 0, total: {{ count($heroImagesUrls) }}, timer: null }"
                     x-init="timer = setInterval(() => active = (active + 1) % total, 4500)">
                    @foreach($heroImagesUrls as $i => $url)
                    <div x-show="active === {{ $i }}"
                         x-transition:enter="transition ease-out duration-700"
                         x-transition:enter-start="opacity-0 scale-95"
                         x-transition:enter-end="opacity-100 scale-100"
                         x-transition:leave="transition ease-in duration-300"
                         x-transition:leave-start="opacity-100"
                         x-transition:leave-end="opacity-0"
                         class="rounded-3xl overflow-hidden glow"
                         style="aspect-ratio:3/4;max-height:520px">
                        <img src="{{ $url }}" alt="MenuPro hero {{ $i + 1 }}"
                             class="w-full h-full object-cover" loading="{{ $i === 0 ? 'eager' : 'lazy' }}">
                    </div>
                    @endforeach

                    {{-- Dots --}}
                    @if(count($heroImagesUrls) > 1)
                    <div class="absolute -bottom-6 left-1/2 -translate-x-1/2 flex gap-2">
                        @foreach($heroImagesUrls as $i => $url)
                        <button @click="active = {{ $i }}; clearInterval(timer); timer = setInterval(() => active = (active + 1) % total, 4500)"
                                class="rounded-full transition-all duration-300"
                                :class="active === {{ $i }} ? 'w-6 h-2.5' : 'w-2.5 h-2.5'"
                                :style="active === {{ $i }} ? 'background:#D45E0C' : 'background:rgba(255,255,255,.25)'">
                        </button>
                        @endforeach
                    </div>
                    @endif
                </div>
            @else
                {{-- Rotating badge --}}
                <div class="absolute top-2 right-2 lg:top-4 lg:right-0 w-24 h-24 pointer-events-none z-20">
                    <svg class="w-full h-full sp" viewBox="0 0 100 100">
                        <defs><path id="cb" d="M 50,50 m -35,0 a 35,35 0 1,1 70,0 a 35,35 0 1,1 -70,0"/></defs>
                        <text fill="#D45E0C" font-size="10.5" font-weight="800" letter-spacing="3.5"><textPath href="#cb">MENUPRO · RESTAURANT · </textPath></text>
                    </svg>
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="w-9 h-9 rounded-full flex items-center justify-center" style="background:#D45E0C">
                            <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        </div>
                    </div>
                </div>

                {{-- Phone --}}
                <div class="fl relative" style="width:236px">
                    <div class="rounded-[3rem] overflow-hidden border-[7px]" style="border-color:#1c1c1c;background:#0a0a0a;box-shadow:0 60px 120px rgba(0,0,0,.75),0 0 0 1px rgba(255,255,255,.04)">
                        {{-- Status bar --}}
                        <div class="flex items-center justify-between px-5 py-2.5" style="background:#0a0a0a">
                            <span class="text-[10px] text-white font-bold">9:41</span>
                            <div class="flex items-center gap-1.5">
                                <div class="flex items-end gap-px h-3">
                                    @foreach([4,6,8,10,12] as $h)<div class="w-1 rounded-sm" style="height:{{ $h }}px;background:{{ $h >= 8 ? '#fff' : 'rgba(255,255,255,.3)' }}"></div>@endforeach
                                </div>
                                <div class="w-5 h-2.5 rounded-sm border border-white/40 p-px"><div class="h-full w-4/5 rounded-[1px] bg-white"></div></div>
                            </div>
                        </div>
                        {{-- App --}}
                        <div style="background:#f7f6f4;height:446px;overflow:hidden">
                            {{-- Header --}}
                            <div class="px-4 pt-4 pb-3 bg-white" style="border-bottom:1px solid #f0eeec">
                                <div class="flex items-center justify-between mb-3">
                                    <div>
                                        <p class="text-[9px] text-neutral-400 font-medium">Bonjour 👋</p>
                                        <p class="text-[13px] font-extrabold text-neutral-900" style="font-family:'Bricolage Grotesque',sans-serif">Que manger aujourd'hui ?</p>
                                    </div>
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center text-white text-[10px] font-black shrink-0" style="background:#D45E0C">AK</div>
                                </div>
                                <div class="rounded-xl px-3 py-2 flex items-center gap-2" style="background:#f2f1ef">
                                    <svg class="w-3 h-3 text-neutral-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                                    <span class="text-[10px] text-neutral-400">Rechercher un plat...</span>
                                </div>
                            </div>
                            {{-- Banner --}}
                            <div class="mx-3 mt-3 rounded-2xl p-3.5 flex items-center justify-between" style="background:linear-gradient(135deg,#D45E0C,#a84509)">
                                <div>
                                    <p class="text-[8px] text-orange-200 font-bold uppercase tracking-wider mb-0.5">Offre du jour</p>
                                    <p class="text-[12px] text-white font-extrabold leading-tight">Poulet Braisé<br>+ Alloco</p>
                                    <p class="text-[9px] text-orange-200 mt-1">🕐 Livré en 25 min</p>
                                </div>
                                <div class="w-14 h-14 rounded-2xl flex items-center justify-center text-3xl" style="background:rgba(255,255,255,.12)">🍗</div>
                            </div>
                            {{-- Categories --}}
                            <div class="flex gap-2 px-3 mt-3 overflow-hidden">
                                @foreach([['🍽','Tout',true],['🍗','Poulet',false],['🐟','Poisson',false],['🍕','Pizza',false]] as $c)
                                <div class="shrink-0 flex items-center gap-1 px-2.5 py-1.5 rounded-full text-[9px] font-bold" style="{{ $c[2] ? 'background:#D45E0C;color:#fff' : 'background:#ede9e5;color:#666' }}">{{ $c[0] }} {{ $c[1] }}</div>
                                @endforeach
                            </div>
                            {{-- Cards --}}
                            <div class="px-3 mt-3 space-y-2">
                                @foreach([['Chez Awa','Abidjan · Maquis','25 min','4.9','🍖'],['La Belle Vue','Bouaké · Restaurant','30 min','4.7','🥗'],['Snack Délice','Abidjan · Street','15 min','4.8','🌯']] as $r)
                                <div class="bg-white rounded-2xl p-2.5 flex items-center gap-2.5" style="box-shadow:0 1px 4px rgba(0,0,0,.06)">
                                    <div class="w-11 h-11 rounded-xl flex items-center justify-center text-xl shrink-0" style="background:#fff8f5">{{ $r[4] }}</div>
                                    <div class="flex-1 min-w-0">
                                        <p class="text-[10px] font-extrabold text-neutral-900 truncate">{{ $r[0] }}</p>
                                        <p class="text-[8px] text-neutral-400 mt-0.5">{{ $r[1] }}</p>
                                    </div>
                                    <div class="text-right shrink-0">
                                        <p class="text-[9px] font-bold" style="color:#D45E0C">{{ $r[2] }}</p>
                                        <p class="text-[8px] text-amber-500">★ {{ $r[3] }}</p>
                                    </div>
                                </div>
                                @endforeach
                            </div>
                        </div>
                    </div>

                    {{-- Floating cards --}}
                    <div class="fl2 absolute -right-16 top-16 bg-white rounded-2xl px-3 py-2.5 flex items-center gap-2.5" style="min-width:155px;box-shadow:0 8px 30px rgba(0,0,0,.15);border:1px solid rgba(0,0,0,.05)">
                        <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0" style="background:#e0f2fe">
                            <img src="{{ asset('images/payments/wave.png') }}" class="w-5 h-5 object-contain" alt="Wave">
                        </div>
                        <div>
                            <p class="text-[10px] font-extrabold text-neutral-900">Paiement reçu ✓</p>
                            <p class="text-[9px] text-neutral-400">Wave · 7 500 F</p>
                        </div>
                    </div>
                    <div class="fl absolute -left-16 bottom-32 bg-white rounded-2xl px-3 py-2.5 flex items-center gap-2.5" style="min-width:145px;box-shadow:0 8px 30px rgba(0,0,0,.15);border:1px solid rgba(0,0,0,.05)">
                        <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0" style="background:rgba(212,94,12,.1)">
                            <svg class="w-4 h-4" style="color:#D45E0C" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/></svg>
                        </div>
                        <div>
                            <p class="text-[10px] font-extrabold text-neutral-900">Nouvelle commande !</p>
                            <p class="text-[9px] text-neutral-400">Table 5 · 3 plats</p>
                        </div>
                    </div>
                </div>
            @endif
            </div>
        </div>
    </div>
    <div class="absolute bottom-0 left-0 right-0">
        <svg viewBox="0 0 1440 50" preserveAspectRatio="none" style="width:100%;height:50px;fill:#fff" ><path d="M0,50 C400,10 1040,42 1440,8 L1440,50Z"/></svg>
    </div>
</section>


{{-- ══════════ 2. PAIEMENTS + TICKER ══════════ --}}
<section class="bg-white py-12 border-b border-neutral-100">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 mb-8">
        <p class="text-center text-[11px] font-black text-neutral-300 uppercase tracking-[0.3em] mb-7">Paiements Mobile Money acceptés</p>
        <div class="flex items-center justify-center gap-5 sm:gap-10 flex-wrap fu">
            @foreach([['wave.png','Wave','#e0f2fe'],['orange-money.png','Orange Money','#fff7ed'],['mtn-momo.png','MTN MoMo','#fefce8'],['moov-money.png','Moov Money','#eff6ff']] as $i => $p)
            <div class="flex flex-col items-center gap-2" style="transition-delay:{{ $i*0.08 }}s">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center border border-neutral-100 hover:shadow-md hover:-translate-y-0.5 transition-all" style="background:{{ $p[2] }}">
                    <img src="{{ asset('images/payments/'.$p[0]) }}" alt="{{ $p[1] }}" class="h-8 w-8 object-contain" loading="lazy">
                </div>
                <span class="text-[10px] font-semibold text-neutral-400">{{ $p[1] }}</span>
            </div>
            @endforeach
        </div>
    </div>
    <div class="tk-w border-t border-neutral-100 pt-5">
        <div class="tk-t">
            @foreach(array_fill(0,2,['Poulet Braisé','Attieké Poisson','Jus Naturels','Pizza','Burgers','Maquis','Hôtel','Livraison','Paninis','Tacos','Riz Sauce','Café']) as $items)
            @foreach($items as $item)
            <div class="flex items-center gap-3 px-5">
                <span class="text-neutral-200 font-black">·</span>
                <span class="text-sm font-semibold text-neutral-400 whitespace-nowrap">{{ $item }}</span>
            </div>
            @endforeach
            @endforeach
        </div>
    </div>
</section>


{{-- ══════════ 3. POUR QUI ══════════ --}}
<section class="py-24 sm:py-28 bg-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14 fu">
            <span class="text-xs font-black uppercase tracking-widest" style="color:#D45E0C">Pour qui ?</span>
            <h2 class="fd text-4xl sm:text-5xl font-extrabold text-neutral-900 mt-3 leading-tight">De la vendeuse de panini<br>au grand hôtel</h2>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5 fu">
            @foreach([
                ['🥡','Stand & Street food','Vendeurs de rue, paninis, tacos, jus. MenuPro sur votre téléphone.','#fff8f5','#D45E0C','5 000 F/mois'],
                ['🍽','Maquis & Restaurant','Tables, QR codes, commandes cuisine, alertes sonores.','#fafafa','#525252','15 000 F/mois'],
                ['🏨','Hôtel & Résidence','QR par chambre, room service, voix IA pour le personnel.','#f0f7ff','#3b82f6','Gold'],
                ['🏍','Livraison intégrée','Vos livreurs, suivi temps réel. 0% de commission.','#f0fdf4','#16a34a','Pro'],
            ] as $w)
            <div class="rounded-3xl p-7 border-2 border-transparent hover:border-neutral-200 hover:-translate-y-1 hover:shadow-xl transition-all duration-300" style="background:{{ $w[3] }}">
                <div class="text-3xl mb-5">{{ $w[0] }}</div>
                <h3 class="fd font-extrabold text-neutral-900 text-lg mb-2">{{ $w[1] }}</h3>
                <p class="text-neutral-500 text-sm leading-relaxed mb-5">{{ $w[2] }}</p>
                <span class="inline-block text-xs font-black px-3 py-1.5 rounded-full" style="background:rgba(0,0,0,.06);color:{{ $w[4] }}">À partir de {{ $w[5] }}</span>
            </div>
            @endforeach
        </div>
    </div>
</section>


{{-- ══════════ 4. FEATURES — Dark ══════════ --}}
<section class="py-24 sm:py-28" style="background:#080808">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-16 fu">
            <span class="text-xs font-black uppercase tracking-widest" style="color:#D45E0C">Pourquoi MenuPro</span>
            <h2 class="fd text-4xl sm:text-5xl font-extrabold text-white mt-3 leading-tight">
                Tout ce dont votre<br><span class="gt">restaurant a besoin</span>
            </h2>
        </div>

        {{-- 2 featured --}}
        <div class="grid lg:grid-cols-5 gap-5 mb-5">
            <div class="lg:col-span-3 rounded-3xl p-8 border border-neutral-800 hover:border-orange-900/50 transition-all duration-300 fu" style="background:#111">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center mb-6" style="background:rgba(212,94,12,.15);border:1px solid rgba(212,94,12,.2)">
                    <svg class="w-7 h-7" style="color:#D45E0C" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"/></svg>
                </div>
                <h3 class="fd text-2xl font-extrabold text-white mb-3">Commandes en direct</h3>
                <p class="text-neutral-500 leading-relaxed max-w-sm">Vos clients commandent via QR code ou lien WhatsApp. Alerte sonore instantanée, écran cuisine dédié, impression automatique.</p>
                <div class="mt-6 flex items-center gap-2 flex-wrap">
                    @foreach(['QR Code','WhatsApp','Cuisine','Alertes sonores'] as $tag)
                    <span class="text-xs font-black px-3 py-1.5 rounded-full" style="{{ $loop->first ? 'background:#D45E0C;color:#fff' : 'background:#1e1e1e;color:#666;border:1px solid #2a2a2a' }}">{{ $tag }}</span>
                    @endforeach
                </div>
            </div>
            <div class="lg:col-span-2 rounded-3xl p-8 border border-neutral-800 hover:border-orange-900/50 transition-all duration-300 fu d1" style="background:#111">
                <div class="w-14 h-14 rounded-2xl flex items-center justify-center mb-6" style="background:rgba(212,94,12,.15);border:1px solid rgba(212,94,12,.2)">
                    <svg class="w-7 h-7" style="color:#D45E0C" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <h3 class="fd text-2xl font-extrabold text-white mb-3">Paiement Mobile Money</h3>
                <p class="text-neutral-500 leading-relaxed">Wave, Orange Money, MTN, Moov. L'argent arrive directement sur votre compte — sans délai ni intermédiaire.</p>
                <div class="mt-6 flex -space-x-2">
                    @foreach([['wave.png','#e0f2fe'],['orange-money.png','#fff7ed'],['mtn-momo.png','#fefce8'],['moov-money.png','#eff6ff']] as $p)
                    <div class="w-9 h-9 rounded-full border-2 border-neutral-900 flex items-center justify-center" style="background:{{ $p[1] }}"><img src="{{ asset('images/payments/'.$p[0]) }}" class="w-5 h-5 object-contain" alt=""></div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- 4 small --}}
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
            @foreach([
                ['analytics','Analytics & Rapports','Bilan journalier, CA par heure, plats les plus vendus, taux d\'annulation.'],
                ['stock','Gestion de stock','Alertes rupture automatiques, inventaire, ingrédients. Plus jamais à court.'],
                ['hotel','Mode Hôtel','QR par chambre, room service, voix IA, appel addition, appel ménage.'],
                ['delivery','Livraison intégrée','Gérez vos livreurs avec suivi GPS. Vos clients voient la progression. Zéro commission.'],
            ] as $i => $f)
            <div class="rounded-3xl p-7 border border-neutral-800 hover:border-orange-900/30 hover:-translate-y-0.5 transition-all duration-300 fu" style="background:#111;transition-delay:{{ $i*0.07 }}s">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center mb-5" style="background:rgba(212,94,12,.1)">
                    @if($f[0]==='analytics')<svg class="w-6 h-6" style="color:#D45E0C" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    @elseif($f[0]==='stock')<svg class="w-6 h-6" style="color:#D45E0C" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/></svg>
                    @elseif($f[0]==='hotel')<svg class="w-6 h-6" style="color:#D45E0C" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    @else<svg class="w-6 h-6" style="color:#D45E0C" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/></svg>@endif
                </div>
                <h3 class="fd font-extrabold text-white text-base mb-2">{{ $f[1] }}</h3>
                <p class="text-neutral-500 text-sm leading-relaxed">{{ $f[2] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>


{{-- ══════════ 5. STATS STRIP ══════════ --}}
<section class="py-16" style="background:linear-gradient(135deg,#a84509,#D45E0C,#e8751a)">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-8 text-center fu">
            @foreach([
                ['0%','Commission','Gardez 100% de vos revenus.'],
                ['15 min','Pour être en ligne','Menu, QR code, paiements prêts.'],
                ['4','Moyens de paiement','Wave, Orange, MTN, Moov.'],
                ['24/7','Disponible','Même quand vous dormez.'],
            ] as $k)
            <div>
                <div class="fd text-5xl sm:text-6xl font-extrabold text-white leading-none mb-2">{{ $k[0] }}</div>
                <div class="font-extrabold text-white text-sm mb-1">{{ $k[1] }}</div>
                <div class="text-orange-200 text-xs">{{ $k[2] }}</div>
            </div>
            @endforeach
        </div>
    </div>
</section>


{{-- ══════════ 6. HOW IT WORKS ══════════ --}}
<section id="how-it-works" class="py-24 sm:py-28 bg-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-xl mx-auto mb-16 fu">
            <span class="text-xs font-black uppercase tracking-widest" style="color:#D45E0C">Comment ça marche</span>
            <h2 class="fd text-4xl sm:text-5xl font-extrabold text-neutral-900 mt-3 leading-tight">En ligne en 15 minutes<br>chrono</h2>
        </div>
        <div class="grid md:grid-cols-3 gap-6 fu relative">
            <div class="hidden md:block absolute h-px top-11 left-[calc(16.66%+1rem)] right-[calc(16.66%+1rem)]" style="background:linear-gradient(90deg,transparent,#D45E0C 20%,#D45E0C 80%,transparent)"></div>
            @foreach([
                ['01','Créez votre compte','Nom, email, téléphone. Votre espace est prêt en 2 minutes.','~2 min'],
                ['02','Ajoutez votre menu','Photos, prix, catégories. Configurez horaires et paiements Mobile Money.','~10 min'],
                ['03','Recevez des commandes','Partagez votre lien ou imprimez votre QR code. Commandes et paiements en direct.','Immédiat'],
            ] as $step)
            <div class="bg-neutral-50 rounded-3xl p-8 border border-neutral-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300">
                <div class="flex items-start justify-between mb-6">
                    <div class="w-12 h-12 rounded-2xl flex items-center justify-center fd font-extrabold text-lg text-white" style="background:#D45E0C">{{ $step[0] }}</div>
                    <span class="text-xs font-black px-3 py-1.5 rounded-full text-white" style="background:#161616">{{ $step[3] }}</span>
                </div>
                <h3 class="fd text-xl font-extrabold text-neutral-900 mb-3">{{ $step[1] }}</h3>
                <p class="text-neutral-500 text-sm leading-relaxed">{{ $step[2] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>


{{-- ══════════ 7. APP — Android + iPhone ══════════ --}}
<section id="app" class="py-24 relative overflow-hidden" style="background:#080808">
    <div class="pointer-events-none absolute inset-0" style="background:radial-gradient(ellipse 80% 50% at 50% 100%,rgba(212,94,12,.1),transparent)"></div>
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="text-center mb-14 fu">
            <span class="text-xs font-black uppercase tracking-widest mb-4 block" style="color:#D45E0C">Application mobile</span>
            <h2 class="fd text-4xl sm:text-5xl font-extrabold text-white leading-tight">Commandez où<br><span class="gt">vous voulez</span></h2>
            <p class="text-neutral-400 mt-4 max-w-lg mx-auto text-lg">Disponible sur Android et iPhone.</p>
        </div>
        <div class="grid md:grid-cols-2 gap-6 max-w-3xl mx-auto">
            {{-- Android --}}
            <div class="fu rounded-3xl p-8 border border-neutral-800 flex flex-col items-center text-center hover:border-green-800/60 hover:-translate-y-1 transition-all duration-300" style="background:#0f0f0f">
                <div class="w-16 h-16 rounded-2xl flex items-center justify-center mb-5" style="background:rgba(61,220,132,.1);border:1px solid rgba(61,220,132,.18)">
                    <svg class="w-8 h-8" style="color:#3DDC84" viewBox="0 0 24 24" fill="currentColor"><path d="M6.18 15.64a2.18 2.18 0 0 1-2.18-2.18C4 12.36 4.98 11.38 6.18 11.38c1.2 0 2.18.98 2.18 2.18-.01 1.2-.98 2.08-2.18 2.08m11.64 0a2.18 2.18 0 0 1-2.18-2.18c0-1.2.98-2.18 2.18-2.18 1.2 0 2.18.98 2.18 2.18 0 1.2-.98 2.08-2.18 2.08M18.42 7l1.79-3.1-.9-.52L17.5 6.5A9.7 9.7 0 0 0 12 5a9.7 9.7 0 0 0-5.5 1.5L4.69 3.38l-.9.52L5.58 7A9.82 9.82 0 0 0 2 14h20A9.82 9.82 0 0 0 18.42 7z"/></svg>
                </div>
                <div class="text-xs font-black uppercase tracking-widest mb-2" style="color:#3DDC84">Android</div>
                <h3 class="fd text-xl font-extrabold text-white mb-2">Télécharger l'APK</h3>
                <p class="text-neutral-500 text-sm mb-6 leading-relaxed">Installez l'application directement sur votre téléphone Android.</p>
                <div class="w-36 h-36 rounded-2xl overflow-hidden bg-white flex items-center justify-center mb-5 p-1" style="border:3px solid rgba(61,220,132,.25)">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=144x144&data={{ urlencode('https://www.menupro.ci/downloads/menupro.apk') }}&color=1a1a1a&bgcolor=FFFFFF&margin=6" alt="QR Code APK" class="w-full h-full object-cover rounded-xl" loading="lazy">
                </div>
                <p class="text-xs text-neutral-600 mb-5">Scannez ou cliquez pour télécharger</p>
                <a href="{{ asset('downloads/menupro.apk') }}" download class="w-full inline-flex items-center justify-center gap-2.5 px-6 py-4 rounded-2xl text-neutral-950 font-black text-sm transition-all hover:opacity-90 hover:-translate-y-0.5" style="background:#3DDC84;box-shadow:0 0 28px rgba(61,220,132,.18)">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    Télécharger · Android
                </a>
            </div>
            {{-- iPhone --}}
            <div class="fu d1 rounded-3xl p-8 border border-neutral-800 flex flex-col items-center text-center hover:border-orange-900/50 hover:-translate-y-1 transition-all duration-300" style="background:#0f0f0f">
                <div class="w-16 h-16 rounded-2xl flex items-center justify-center mb-5" style="background:rgba(255,255,255,.07);border:1px solid rgba(255,255,255,.09)">
                    <svg class="w-8 h-8 text-white" viewBox="0 0 24 24" fill="currentColor"><path d="M18.71 19.5c-.83 1.24-1.71 2.45-3.05 2.47-1.34.03-1.77-.79-3.29-.79-1.53 0-2 .77-3.27.82-1.31.05-2.3-1.32-3.14-2.53C4.25 17 2.94 12.45 4.7 9.39c.87-1.52 2.43-2.48 4.12-2.51 1.28-.02 2.5.87 3.29.87.78 0 2.26-1.07 3.8-.91.65.03 2.47.26 3.64 1.98-.09.06-2.17 1.28-2.15 3.81.03 3.02 2.65 4.03 2.68 4.04-.03.07-.42 1.44-1.38 2.83M13 3.5c.73-.83 1.94-1.46 2.94-1.5.13 1.17-.34 2.35-1.04 3.19-.69.85-1.83 1.51-2.95 1.42-.15-1.15.41-2.35 1.05-3.11"/></svg>
                </div>
                <div class="text-xs font-black uppercase tracking-widest mb-2 text-neutral-500">iPhone · iPad</div>
                <h3 class="fd text-xl font-extrabold text-white mb-2">Application Web <span class="inline-block text-[10px] font-black px-2 py-0.5 rounded-md align-middle ml-1" style="background:rgba(212,94,12,.2);color:#D45E0C">PWA</span></h3>
                <p class="text-neutral-500 text-sm mb-6 leading-relaxed">Ouvrez avec <strong class="text-neutral-300">Safari</strong> et ajoutez à l'écran d'accueil — expérience identique à une app native.</p>
                <div class="w-36 h-36 rounded-2xl overflow-hidden bg-white flex items-center justify-center mb-5 p-1" style="border:3px solid rgba(212,94,12,.25)">
                    <img src="https://api.qrserver.com/v1/create-qr-code/?size=144x144&data={{ urlencode('https://mpa-five.vercel.app/') }}&color=1a1a1a&bgcolor=FFFFFF&margin=6" alt="QR Code PWA" class="w-full h-full object-cover rounded-xl" loading="lazy">
                </div>
                <p class="text-xs text-neutral-600 mb-5">Scannez avec Safari</p>
                <a href="https://mpa-five.vercel.app/" target="_blank" class="w-full inline-flex items-center justify-center gap-2.5 px-6 py-4 rounded-2xl text-white font-black text-sm transition-all hover:opacity-90 hover:-translate-y-0.5" style="background:#D45E0C;box-shadow:0 0 28px rgba(212,94,12,.18)">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    Ouvrir · iPhone
                </a>
                <div class="mt-4 w-full rounded-2xl p-4 text-left" style="background:rgba(255,255,255,.03);border:1px solid rgba(255,255,255,.05)">
                    <p class="text-xs font-black text-neutral-600 mb-2.5 uppercase tracking-wider">Installer sur iPhone :</p>
                    @foreach(['Ouvrez le lien dans Safari','Appuyez sur Partager ⬆','Choisissez "Sur l\'écran d\'accueil"','Appuyez sur "Ajouter"'] as $si => $sstep)
                    <div class="flex items-center gap-2.5 text-xs text-neutral-500 {{ $si > 0 ? 'mt-1.5' : '' }}">
                        <span class="w-4 h-4 rounded-full flex items-center justify-center text-[9px] font-black shrink-0" style="background:rgba(212,94,12,.15);color:#D45E0C">{{ $si+1 }}</span>
                        {{ $sstep }}
                    </div>
                    @endforeach
                </div>
            </div>
        </div>
        <div class="mt-12 flex flex-wrap items-center justify-center gap-10 fu d2">
            @foreach([['4.8★','Note moyenne'],[$stats['restaurants'].'+ ','Restaurants'],['100%','Gratuit'],['24/7','Disponible']] as $s)
            <div class="text-center">
                <div class="fd text-2xl font-extrabold text-white leading-none">{{ $s[0] }}</div>
                <div class="text-neutral-600 text-xs mt-1">{{ $s[1] }}</div>
            </div>
            @endforeach
        </div>
    </div>
</section>


{{-- ══════════ 8. DRIVER APP ══════════ --}}
<section class="py-24 bg-white relative overflow-hidden">
    <div class="pointer-events-none absolute inset-0" style="background:radial-gradient(ellipse 50% 60% at 15% 50%,rgba(255,97,0,.05),transparent)"></div>
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 relative z-10">
        <div class="grid lg:grid-cols-2 gap-12 items-center">
            <div class="flex flex-col items-center fu order-2 lg:order-1">
                <div class="rounded-3xl p-8 shadow-2xl flex flex-col items-center gap-5 max-w-xs w-full border border-neutral-900" style="background:#0f0f0f">
                    <p class="text-sm font-extrabold text-white fd">Application Livreurs</p>
                    <div class="w-40 h-40 rounded-2xl overflow-hidden bg-neutral-800 flex items-center justify-center p-1" style="border:3px solid rgba(255,97,0,.25)">
                        <img src="https://api.qrserver.com/v1/create-qr-code/?size=160x160&data={{ urlencode('https://mpa-driver.vercel.app/') }}&color=FF6100&bgcolor=111111&margin=8" alt="QR Code app livreurs" class="w-full h-full object-cover rounded-xl" loading="lazy">
                    </div>
                    <a href="https://mpa-driver.vercel.app/" target="_blank" class="w-full text-center py-3.5 rounded-2xl text-sm font-black text-white transition-all hover:opacity-90" style="background:#FF6100">Ouvrir l'app livreur →</a>
                </div>
            </div>
            <div class="text-center lg:text-left fu order-1 lg:order-2">
                <span class="text-xs font-black uppercase tracking-widest mb-4 block" style="color:#FF6100">Devenir livreur</span>
                <h2 class="fd text-4xl sm:text-5xl font-extrabold text-neutral-900 leading-tight">
                    Livrez avec<br>
                    <span style="background:linear-gradient(120deg,#FF6100,#FF3301);-webkit-background-clip:text;-webkit-text-fill-color:transparent;background-clip:text">MenuPro</span>
                </h2>
                <p class="text-neutral-500 text-lg mt-5 leading-relaxed max-w-lg mx-auto lg:mx-0">Inscrivez-vous comme livreur indépendant. Recevez des courses, gérez vos gains et soyez payé directement sur Wave.</p>
                <div class="mt-7 space-y-4">
                    @foreach([['Inscription rapide','Créez votre compte en 2 minutes avec votre CNI et permis.'],['Courses en temps réel','Recevez les commandes proches de vous avec alerte sonore.'],['Paiement Wave automatique','Vos gains sont virés sur demande, sans délai.'],['Zéro frais','Aucun abonnement. Vous gagnez à chaque course effectuée.']] as $f)
                    <div class="flex items-start gap-3 text-left">
                        <div class="w-6 h-6 rounded-full flex items-center justify-center shrink-0 mt-0.5" style="background:rgba(255,97,0,.1)">
                            <svg class="w-3.5 h-3.5" style="color:#FF6100" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                        </div>
                        <div>
                            <p class="font-extrabold text-neutral-900 text-sm">{{ $f[0] }}</p>
                            <p class="text-neutral-500 text-xs mt-0.5">{{ $f[1] }}</p>
                        </div>
                    </div>
                    @endforeach
                </div>
                <div class="mt-8">
                    <a href="https://mpa-driver.vercel.app/" target="_blank" class="group inline-flex items-center gap-2 px-7 py-4 text-white font-extrabold rounded-2xl transition-all hover:-translate-y-0.5" style="background:#FF6100;box-shadow:0 0 30px rgba(255,97,0,.22)">
                        🏍️ S'inscrire comme livreur
                        <svg class="w-4 h-4 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</section>


{{-- ══════════ VIDÉOS ══════════ --}}
@if(!empty($videos))
<section class="py-24 bg-white">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14 fu">
            <span class="text-xs font-black uppercase tracking-widest" style="color:#D45E0C">Vidéo</span>
            <h2 class="fd text-4xl sm:text-5xl font-extrabold text-neutral-900 mt-3">Voyez MenuPro en action</h2>
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


{{-- ══════════ TÉMOIGNAGES ══════════ --}}
<section class="py-24" style="background:#f7f6f4">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-xl mx-auto mb-16 fu">
            <span class="text-xs font-black uppercase tracking-widest" style="color:#D45E0C">Témoignages</span>
            <h2 class="fd text-4xl sm:text-5xl font-extrabold text-neutral-900 mt-3 leading-tight">Ils en parlent<br>mieux que nous</h2>
        </div>
        @php
            $testimonials = \App\Models\SystemSetting::get('home_testimonials', [
                ['name'=>'Awa Koné','role'=>'Propriétaire, Maquis Chez Awa','city'=>'Daloa','text'=>'Depuis MenuPro, mes clients commandent directement depuis leur téléphone. Mon chiffre d\'affaires a augmenté de 30% en 2 mois.','stars'=>5],
                ['name'=>'Kouamé Jean','role'=>'Gérant, Restaurant Le Délice','city'=>'Abidjan','text'=>'Le QR code a tout changé. Les clients scannent, commandent et paient par Wave. Je reçois l\'argent immédiatement.','stars'=>5],
                ['name'=>'Marie Touré','role'=>'Fondatrice, Saveurs d\'Afrique','city'=>'Bouaké','text'=>'À partir de 5 000 F/mois, c\'est le meilleur investissement. Zéro commission, zéro surprise. Je recommande à 100%.','stars'=>5],
            ]);
        @endphp
        <div class="grid md:grid-cols-3 gap-6 fu">
            @foreach($testimonials as $t)
            <div class="bg-white rounded-3xl p-7 border border-neutral-100 shadow-sm hover:shadow-xl hover:-translate-y-1 transition-all duration-300 flex flex-col">
                <div class="flex gap-1 mb-5">@for($i=0;$i<($t['stars']??5);$i++)<svg class="w-4 h-4 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>@endfor</div>
                <p class="text-neutral-600 leading-relaxed flex-1 text-sm">"{{ $t['text'] }}"</p>
                <div class="flex items-center gap-3 mt-6 pt-5 border-t border-neutral-100">
                    <div class="w-10 h-10 rounded-full flex items-center justify-center text-white font-black shrink-0" style="background:#D45E0C">{{ strtoupper(substr($t['name'],0,1)) }}</div>
                    <div>
                        <div class="font-extrabold text-sm text-neutral-900">{{ $t['name'] }}</div>
                        <div class="text-xs text-neutral-400">{{ $t['role'] }} · {{ $t['city'] }}</div>
                    </div>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>


{{-- ══════════ RESTAURANTS ══════════ --}}
<section class="py-24 bg-white">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-12 fu">
            <span class="text-xs font-black uppercase tracking-widest" style="color:#D45E0C">Ils nous font confiance</span>
            <h2 class="fd text-3xl sm:text-4xl font-extrabold text-neutral-900 mt-3">Découvrez nos restaurants</h2>
            <p class="text-neutral-400 text-sm mt-2">Commandez directement depuis leur menu en ligne</p>
        </div>
        @php
            $trs = \App\Models\Restaurant::where('status', \App\Enums\RestaurantStatus::ACTIVE)->latest()->take(8)->get(['name','slug','logo_path','banner_path','city']);
        @endphp
        <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-5 fu">
            @foreach($trs as $r)
            <a href="{{ route('r.menu', $r->slug) }}" target="_blank" class="group bg-white rounded-2xl overflow-hidden border border-neutral-100 hover:shadow-xl hover:-translate-y-1 transition-all duration-300 block">
                <div class="relative h-28 overflow-hidden bg-neutral-100">
                    @if($r->banner_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($r->banner_path))
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($r->banner_path) }}" alt="{{ $r->name }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500" loading="lazy">
                    @else
                        <div class="w-full h-full" style="background:linear-gradient(135deg,#D45E0C,#a84509)"></div>
                    @endif
                    <div class="absolute inset-0 flex items-center justify-center bg-black/40 opacity-0 group-hover:opacity-100 transition-opacity">
                        <span class="text-white text-xs font-black px-3 py-1.5 rounded-full border-2 border-white">Voir le menu →</span>
                    </div>
                </div>
                <div class="px-4 pb-4">
                    <div class="relative -mt-6 mb-3">
                        <div class="w-12 h-12 rounded-xl border-2 border-white shadow-md overflow-hidden bg-white">
                            @if($r->logo_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($r->logo_path))
                                <img src="{{ \Illuminate\Support\Facades\Storage::url($r->logo_path) }}" alt="{{ $r->name }}" class="w-full h-full object-cover" loading="lazy">
                            @else
                                <div class="w-full h-full flex items-center justify-center text-white font-black text-lg" style="background:#D45E0C">{{ strtoupper(substr($r->name,0,1)) }}</div>
                            @endif
                        </div>
                    </div>
                    <h3 class="font-extrabold text-sm text-neutral-900 truncate">{{ $r->name }}</h3>
                    @if($r->city)<p class="text-xs text-neutral-400 mt-0.5">📍 {{ $r->city }}</p>@endif
                </div>
            </a>
            @endforeach
        </div>
    </div>
</section>


{{-- ══════════ TARIFS — Dark ══════════ --}}
<section id="pricing" class="py-24 sm:py-28" style="background:#080808">
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center max-w-xl mx-auto mb-16 fu">
            <span class="text-xs font-black uppercase tracking-widest" style="color:#D45E0C">Tarifs</span>
            <h2 class="fd text-4xl sm:text-5xl font-extrabold text-white mt-3 leading-tight">Simple. Transparent.<br><span class="gt">Zéro commission.</span></h2>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5 fu">
            {{-- Stand --}}
            <div class="rounded-3xl p-7 border border-neutral-800 hover:border-neutral-700 hover:-translate-y-0.5 hover:shadow-xl transition-all duration-300 flex flex-col" style="background:#111">
                <div class="text-xs font-black uppercase tracking-widest mb-4" style="color:#D45E0C">Stand</div>
                <div class="flex items-baseline gap-1 mb-1"><span class="fd text-4xl font-extrabold text-white">5 000</span><span class="text-neutral-500 text-sm">F/mois</span></div>
                <p class="text-sm text-neutral-500 mb-6">Vendeurs de rue et stands</p>
                <ul class="space-y-2.5 flex-1 mb-8">@foreach(['15 plats','100 cmd/mois','QR code inclus','Wave & Orange Money','Sans PC requis'] as $f)<li class="flex items-center gap-2 text-sm text-neutral-400"><svg class="w-4 h-4 shrink-0" style="color:#D45E0C" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>{{ $f }}</li>@endforeach</ul>
                <a href="{{ route('register') }}?plan=stand" class="block w-full text-center py-3.5 rounded-2xl font-black text-sm transition-all border" style="border-color:#D45E0C;color:#D45E0C;background:rgba(212,94,12,.08)" onmouseover="this.style.background='rgba(212,94,12,.14)'" onmouseout="this.style.background='rgba(212,94,12,.08)'">Essai 7j gratuit</a>
            </div>
            {{-- Essentiel --}}
            <div class="rounded-3xl p-7 border border-neutral-800 hover:border-neutral-700 hover:-translate-y-0.5 hover:shadow-xl transition-all duration-300 flex flex-col" style="background:#111">
                <div class="text-xs font-black text-neutral-500 uppercase tracking-widest mb-4">Essentiel</div>
                <div class="flex items-baseline gap-1 mb-1"><span class="fd text-4xl font-extrabold text-white">15 000</span><span class="text-neutral-500 text-sm">F/mois</span></div>
                <p class="text-sm text-neutral-500 mb-6">Maquis et petits restaurants</p>
                <ul class="space-y-2.5 flex-1 mb-8">@foreach(['25 plats, 8 catégories','200 cmd/mois','Mobile Money + QR','Support WhatsApp'] as $f)<li class="flex items-center gap-2 text-sm text-neutral-400"><svg class="w-4 h-4 text-neutral-700 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>{{ $f }}</li>@endforeach</ul>
                <a href="{{ route('register') }}?plan=essentiel" class="block w-full text-center py-3.5 rounded-2xl font-black text-sm transition-all" style="background:#1a1a1a;color:#888;border:1px solid #2a2a2a" onmouseover="this.style.background='#222'" onmouseout="this.style.background='#1a1a1a'">Essai 7j gratuit</a>
            </div>
            {{-- Pro --}}
            <div class="rounded-3xl p-7 border-2 shadow-2xl relative hover:-translate-y-1 transition-all duration-300 flex flex-col" style="background:#D45E0C;border-color:#D45E0C;box-shadow:0 0 60px rgba(212,94,12,.25)">
                <div class="absolute -top-4 left-1/2 -translate-x-1/2 bg-white text-xs font-black px-5 py-1.5 rounded-full" style="color:#D45E0C">⭐ Populaire</div>
                <div class="text-xs font-black uppercase tracking-widest mb-4 text-orange-200">Pro</div>
                <div class="flex items-baseline gap-1 mb-1"><span class="fd text-4xl font-extrabold text-white">25 000</span><span class="text-orange-200 text-sm">F/mois</span></div>
                <p class="text-sm text-orange-200 mb-6">Stock, livraison, analytics</p>
                <ul class="space-y-2.5 flex-1 mb-8">@foreach(['80 plats, 3 employés','1 000 cmd/mois','Stock complet','Livraison intégrée','Analytics & rapports'] as $f)<li class="flex items-center gap-2 text-sm text-white"><svg class="w-4 h-4 shrink-0 text-orange-200" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>{{ $f }}</li>@endforeach</ul>
                <a href="{{ route('register') }}?plan=pro" class="block w-full text-center py-3.5 rounded-2xl font-black text-sm hover:opacity-90 transition-all bg-white" style="color:#D45E0C">Essai 7j gratuit</a>
            </div>
            {{-- Gold --}}
            <div class="rounded-3xl p-7 border border-neutral-800 hover:border-yellow-900/60 hover:-translate-y-0.5 transition-all duration-300 flex flex-col" style="background:#0c0b08">
                <div class="text-xs font-black uppercase tracking-widest mb-4" style="color:#f6b285">Gold</div>
                <div class="flex items-baseline gap-1 mb-1"><span class="fd text-4xl font-extrabold gt-gold">85 000</span><span class="text-neutral-600 text-sm">F/mois</span></div>
                <p class="text-sm text-neutral-600 mb-6">Multi-espaces, hôtels, VIP</p>
                <ul class="space-y-2.5 flex-1 mb-8">@foreach(['Multi-espaces illimités','PIN serveurs','Rapports par espace','QR chambres hôtel','Formation personnalisée'] as $f)<li class="flex items-center gap-2 text-sm text-neutral-400"><svg class="w-4 h-4 shrink-0" style="color:#f6b285" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>{{ $f }}</li>@endforeach</ul>
                <a href="{{ route('register') }}?plan=gold" class="block w-full text-center py-3.5 rounded-2xl font-black text-sm hover:opacity-90 transition-all text-white" style="background:linear-gradient(135deg,#f6b285,#D45E0C)">Essai 7j gratuit</a>
            </div>
        </div>
        <p class="text-center text-neutral-600 text-sm mt-8">Sans engagement · Sans carte bancaire · <a href="{{ route('pricing') }}" class="font-black hover:underline" style="color:#D45E0C">Comparer tous les plans →</a></p>
    </div>
</section>


{{-- ══════════ FAQ ══════════ --}}
<section class="py-24 bg-white">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-14 fu">
            <span class="text-xs font-black uppercase tracking-widest" style="color:#D45E0C">FAQ</span>
            <h2 class="fd text-4xl sm:text-5xl font-extrabold text-neutral-900 mt-3">Questions fréquentes</h2>
        </div>
        @php
            $faqs = [
                ['Mes clients doivent-ils télécharger une application ?','Non. Vos clients scannent le QR code ou cliquent sur votre lien. Tout fonctionne directement dans le navigateur, sans téléchargement.'],
                ['Comment fonctionne le paiement Mobile Money ?','Vos clients paient depuis leur app Wave, Orange Money, MTN ou Moov. L\'argent est envoyé directement sur votre compte — sans délai, sans intermédiaire.'],
                ['Combien de temps pour être en ligne ?','Moins de 15 minutes. Créez votre compte, ajoutez quelques plats avec photos et prix, et partagez votre lien ou imprimez votre QR code.'],
                ['Est-ce qu\'il y a des commissions sur les commandes ?','Aucune commission. Vous payez un forfait mensuel fixe à partir de 5 000 F/mois et gardez 100% de vos ventes.'],
                ['Que se passe-t-il si j\'ai un problème ?','Support WhatsApp inclus dans tous les plans. Plans Pro et Gold : assistance prioritaire et formation personnalisée.'],
                ['Puis-je annuler à tout moment ?','Oui, sans engagement et sans frais. Annulez depuis votre compte quand vous voulez.'],
            ];
        @endphp
        <div class="space-y-3 fu">
            @foreach($faqs as $i => $faq)
            <div class="rounded-2xl border border-neutral-100 overflow-hidden" x-data="{ open: {{ $i===0?'true':'false' }} }">
                <button @click="open=!open" class="w-full flex items-center justify-between px-6 py-5 text-left font-extrabold text-neutral-900 hover:bg-neutral-50 transition-colors bg-white">
                    <span class="text-sm sm:text-base">{{ $faq[0] }}</span>
                    <span class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 ml-4 transition-transform duration-300" :class="open&&'rotate-45'" style="background:rgba(212,94,12,.08);color:#D45E0C">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/></svg>
                    </span>
                </button>
                <div x-show="open" x-collapse>
                    <p class="px-6 pb-5 text-neutral-500 text-sm leading-relaxed">{{ $faq[1] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>


{{-- ══════════ CTA FINAL ══════════ --}}
<section class="relative py-24 sm:py-32 overflow-hidden" style="background:#080808">
    <div class="pointer-events-none absolute inset-0" style="background:radial-gradient(ellipse 70% 60% at 50% 50%,rgba(212,94,12,.14),transparent)"></div>
    <div class="relative z-10 max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 text-center fu">
        <div class="inline-flex items-center gap-2 px-4 py-1.5 border rounded-full text-sm font-bold mb-8" style="border-color:rgba(212,94,12,.3);background:rgba(212,94,12,.08);color:#FF8C42">
            <span class="relative flex h-2 w-2 shrink-0"><span class="pr absolute inline-flex h-full w-full rounded-full" style="background:#D45E0C"></span><span class="relative inline-flex h-2 w-2 rounded-full" style="background:#D45E0C"></span></span>
            {{ $stats['restaurants'] }} restaurants actifs ce soir
        </div>
        <h2 class="fd text-5xl sm:text-6xl lg:text-[4.5rem] font-extrabold text-white leading-[1.02] tracking-tight">
            Votre prochain<br>client commande<br><span class="gt">dans 15 minutes.</span>
        </h2>
        <p class="text-xl text-white/40 mt-6 max-w-lg mx-auto leading-relaxed">Rejoignez les restaurateurs ivoiriens qui encaissent en direct sur leur Mobile Money.</p>
        <div class="mt-10 flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="{{ route('register') }}" class="group w-full sm:w-auto inline-flex items-center justify-center gap-2.5 px-10 py-4 text-white font-extrabold rounded-2xl transition-all hover:-translate-y-0.5 text-lg glow" style="background:linear-gradient(135deg,#D45E0C,#a84509)">
                Créer mon restaurant — C'est gratuit
                <svg class="w-5 h-5 group-hover:translate-x-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
            </a>
            <a href="{{ route('contact') }}" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-8 py-4 font-semibold rounded-2xl border transition-all text-base" style="color:rgba(255,255,255,.45);border-color:rgba(255,255,255,.09);background:rgba(255,255,255,.03)" onmouseover="this.style.color='rgba(255,255,255,.85)'" onmouseout="this.style.color='rgba(255,255,255,.45)'">
                Parler à un expert
            </a>
        </div>
        <div class="mt-12 flex flex-wrap items-center justify-center gap-6 text-sm text-neutral-700">
            @foreach(['15 min pour être en ligne','Support WhatsApp inclus','À partir de 5 000 F/mois','Annulation libre'] as $t)
            <span class="flex items-center gap-2">
                <svg class="w-4 h-4 shrink-0" style="color:#D45E0C" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
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
