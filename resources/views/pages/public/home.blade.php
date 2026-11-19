@use('App\Support\VitrineGrid', 'G')
@php
    $trial = G::trialDays();
    $rate = G::commission();
    $reducedRate = G::reducedCommission();
    $offers = [
        'restaurant' => [
            'title' => 'Restaurant & maquis',
            'text'  => 'Menu en ligne, QR code sur les tables, commandes envoyées en cuisine avec alerte sonore, caisse et stock.',
            'icon'  => 'M3 11h18M5 11V8a7 7 0 0114 0v3M4 15h16l-1.5 5h-13L4 15z',
        ],
        'stand' => [
            'title' => 'Stand de rue',
            'text'  => 'Garba, alloco, jus, paninis : vos clients commandent depuis leur téléphone et paient en Mobile Money. Tout se gère depuis le vôtre.',
            'icon'  => 'M4 10l2-6h12l2 6M4 10h16M4 10v10h16V10M9 20v-5h6v5',
        ],
        'boutique' => [
            'title' => 'Boutique',
            'text'  => 'Vendez vos articles en ligne : catalogue, paiement Mobile Money, livraison. Pour les superettes et boutiques de quartier.',
            'icon'  => 'M6 7h12l1 13H5L6 7zm3 0a3 3 0 016 0',
            'new'   => true,
        ],
    ];
    $registerUrl = fn (string $offer) => route('register', array_filter(['type' => config("vitrine.offers.{$offer}.register_type")]));
    $testimonials = collect(config('vitrine.testimonials', []))->filter(fn ($t) => ! empty($t['name']) && ! empty($t['text']));
    $faqs = [
        ['Mes clients doivent-ils installer une application ?', 'Non. Ils commandent depuis le navigateur de leur téléphone sur app.menupro.ci, en scannant votre QR code ou en ouvrant votre lien. Une application Android existe aussi pour ceux qui la préfèrent.'],
        ['Comment mes clients paient-ils ?', 'Par Wave, Orange Money, MTN MoMo ou Moov Money, ou en espèces à la livraison ou au comptoir si vous l\'acceptez.'],
        ['Y a-t-il une commission ?', 'Uniquement sur les commandes livrées par MenuPro : ' . $rate . ' (' . $reducedRate . ' avec le plan Pro). Les commandes sur place et à emporter sont sans commission, et aucune commande n\'est jamais bloquée.'],
        ['Je tiens une boutique, pas un restaurant. MenuPro me convient ?', 'Oui. L\'offre Boutique vous donne un catalogue d\'articles en ligne, le paiement Mobile Money et la livraison, comme pour un restaurant.'],
        ['Comment se passe l\'essai gratuit ?', $trial . ' jours avec toutes les fonctions du plan Pro, sans carte bancaire. Ensuite vous choisissez votre plan et payez en Mobile Money. Aucun prélèvement automatique.'],
    ];
@endphp
<x-layouts.public title="Commandes en ligne et Mobile Money pour restaurants, stands et boutiques" description="MenuPro : vos clients commandent sur app.menupro.ci et paient par Wave, Orange, MTN ou Moov. Restaurants, maquis, stands et boutiques. Livraison incluse, essai gratuit {{ $trial }} jours.">
@push('head')
<script type="application/ld+json">{"@@context":"https://schema.org","@@type":"SoftwareApplication","name":"MenuPro","applicationCategory":"BusinessApplication","operatingSystem":"Web","url":"{{ url('/') }}","description":"Commandes en ligne, paiement Mobile Money et livraison pour restaurants, maquis, stands et boutiques en Côte d'Ivoire.","offers":{"@@type":"Offer","price":"{{ G::price('stand') }}","priceCurrency":"XOF"}}</script>
@endpush

{{-- ══════════ 1. ACCUEIL (centré, halo, cartes flottantes) ══════════ --}}
@php
    $check = '<svg class="w-5 h-5 flex-none" viewBox="0 0 20 20" fill="none" aria-hidden="true"><circle cx="10" cy="10" r="10" fill="#FFEDD5"/><path d="M6 10.2l2.6 2.6L14 7.4" stroke="#EA580C" stroke-width="2" stroke-linecap="round" stroke-linejoin="round"/></svg>';
@endphp
<section class="vt-hero3">
    <div class="vt-wrap relative">
        {{-- Carte flottante gauche : ventes du jour --}}
        <div class="vt-float vt-float-left" aria-hidden="true">
            <p class="text-sm vt-muted">Ventes du jour</p>
            <p class="vt-display text-2xl vt-num mt-0.5">87 500 F <span class="text-sm font-semibold text-green-700 align-middle">+18 %</span></p>
            <svg class="mt-3 w-full h-14" viewBox="0 0 200 56" fill="none" preserveAspectRatio="none"><path d="M0 46 C20 44 30 30 50 32 S80 48 100 36 130 12 150 18 180 30 200 8" stroke="#F97316" stroke-width="2.5" stroke-linecap="round"/><path d="M0 46 C20 44 30 30 50 32 S80 48 100 36 130 12 150 18 180 30 200 8 V56 H0Z" fill="url(#vtg)"/><defs><linearGradient id="vtg" x1="0" y1="0" x2="0" y2="1"><stop offset="0" stop-color="#F97316" stop-opacity=".18"/><stop offset="1" stop-color="#F97316" stop-opacity="0"/></linearGradient></defs></svg>
            <p class="text-xs vt-muted mt-1">24 commandes · 19 livrées</p>
        </div>
        {{-- Carte flottante droite : nouvelle commande --}}
        <div class="vt-float vt-float-right" aria-hidden="true">
            <div class="flex items-center justify-between gap-3">
                <p class="text-sm font-semibold text-orange-700">Nouvelle commande</p>
                <span class="vt-live"></span>
            </div>
            <p class="font-bold mt-1">#1186 · Awa K.</p>
            <div class="mt-2 flex items-center justify-between">
                <p class="flex items-center gap-2 text-sm"><img src="{{ asset('images/payments/wave.png') }}" alt="" width="20" height="20" class="w-5 h-5 rounded object-contain"> Payé par Wave</p>
                <p class="vt-display text-lg vt-num">2 700 F</p>
            </div>
            <div class="mt-3 grid grid-cols-2 gap-2 text-sm font-bold">
                <span class="rounded-lg bg-mp-black text-white text-center py-2">Accepter</span>
                <span class="rounded-lg bg-neutral-100 text-center py-2">Refuser</span>
            </div>
        </div>

        <div class="relative mx-auto max-w-[42rem] text-center">
            <a href="{{ $registerUrl('boutique') }}" class="inline-flex items-center gap-2 rounded-full bg-white/80 ring-1 ring-black/5 shadow-sm pl-1.5 pr-4 py-1.5 text-[0.9375rem] font-semibold">
                <span class="rounded-full bg-mp-black text-white px-2.5 py-0.5 text-sm">Nouveau</span>
                Les boutiques arrivent sur MenuPro
            </a>
            <h1 class="vt-h1 mt-6">Vos commandes arrivent toutes seules, payées en Mobile Money.</h1>
            <p class="vt-lead mx-auto mt-6">Restaurant, maquis, stand ou boutique : vos clients commandent sur MenuPro, vous acceptez d'un geste, un livreur s'occupe du reste.</p>
            <ul class="mt-7 flex flex-wrap justify-center gap-x-6 gap-y-3 text-[0.9375rem] font-semibold">
                <li class="flex items-center gap-2">{!! $check !!} Wave, Orange, MTN, Moov</li>
                <li class="flex items-center gap-2">{!! $check !!} Livraison incluse</li>
                <li class="flex items-center gap-2">{!! $check !!} {{ $trial }} jours gratuits</li>
            </ul>
            <div class="mt-9 grid gap-3 sm:flex sm:justify-center">
                <a href="{{ route('register') }}" class="vt-btn vt-btn-dark">Créer mon commerce</a>
                <a href="{{ route('pricing') }}" class="vt-btn vt-btn-ghost">Voir les tarifs</a>
            </div>
        </div>

        {{-- Bande « paiements acceptés » --}}
        <div class="relative mt-16 text-center">
            <p class="text-[0.9375rem] vt-muted">Vos clients paient avec</p>
            <ul class="mt-4 flex flex-wrap items-center justify-center gap-x-8 gap-y-4">
                @foreach([['wave.png','Wave'],['orange-money.png','Orange Money'],['mtn-momo.png','MTN MoMo'],['moov-money.png','Moov Money']] as [$img, $name])
                    <li class="flex items-center gap-2.5 text-lg font-semibold text-neutral-500">
                        <img src="{{ asset('images/payments/'.$img) }}" alt="" width="32" height="32" class="w-8 h-8 rounded-lg object-contain" loading="lazy" decoding="async">{{ $name }}
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</section>

{{-- ══════════ 2. FONCTIONNALITÉS (bento avec aperçus de l'interface) ══════════ --}}
<section id="fonctions" class="vt-section scroll-mt-16">
    <div class="vt-wrap">
        <div class="text-center max-w-2xl mx-auto">
            <h2 class="vt-h2">Tout votre commerce, dans un seul outil</h2>
            <p class="vt-lead mx-auto mt-4">Commandes, paiements, livraison, caisse et chiffres : tout se gère depuis votre téléphone.</p>
        </div>

        <div class="mt-12 grid gap-4 md:grid-cols-6">
            {{-- Commandes en direct --}}
            <article class="vt-bento md:col-span-2">
                <h3 class="vt-display text-xl">Commandes en direct</h3>
                <p class="mt-1.5 vt-muted">Alerte sonore et notification, même téléphone verrouillé.</p>
                <div class="vt-mini mt-6" aria-hidden="true">
                    @foreach([['#1186', 'Awa K.', '2 700 F', 'Nouvelle', 'bg-orange-100 text-orange-800'], ['#1185', 'Table 4', '3 500 F', 'En cuisine', 'bg-amber-100 text-amber-900'], ['#1184', 'Moussa D.', '4 500 F', 'Prête', 'bg-green-100 text-green-800']] as [$n, $who, $tot, $st, $cls])
                        <div class="flex items-center justify-between gap-2 py-2 {{ ! $loop->last ? 'border-b border-neutral-100' : '' }}">
                            <p class="text-sm"><span class="font-bold">{{ $n }}</span> {{ $who }}</p>
                            <span class="rounded-md px-2 py-0.5 text-xs font-semibold {{ $cls }}">{{ $st }}</span>
                        </div>
                    @endforeach
                </div>
            </article>
            {{-- Mobile Money --}}
            <article class="vt-bento md:col-span-2">
                <h3 class="vt-display text-xl">Paiement Mobile Money</h3>
                <p class="mt-1.5 vt-muted">L'argent arrive sur votre compte, retirable sur votre Mobile Money.</p>
                <div class="vt-mini mt-6" aria-hidden="true">
                    <p class="text-sm vt-muted">Solde disponible</p>
                    <p class="vt-display text-2xl vt-num">142 300 F</p>
                    <div class="mt-3 flex items-center justify-between rounded-lg bg-neutral-50 px-3 py-2 text-sm">
                        <span class="flex items-center gap-2"><img src="{{ asset('images/payments/orange-money.png') }}" alt="" width="20" height="20" class="w-5 h-5 rounded object-contain"> Reversement</span>
                        <span class="font-semibold text-green-700">Envoyé</span>
                    </div>
                </div>
            </article>
            {{-- Livraison --}}
            <article class="vt-bento md:col-span-2">
                <h3 class="vt-display text-xl">Livraison incluse</h3>
                <p class="mt-1.5 vt-muted">Un livreur MenuPro récupère et livre. {{ $rate }} seulement sur les commandes livrées.</p>
                <div class="vt-mini mt-6" aria-hidden="true">
                    <div class="flex items-center justify-between text-sm"><span class="font-bold">#1184 en route</span><span class="vt-muted">12 min</span></div>
                    <div class="mt-3 flex items-center gap-1.5">
                        @foreach(['Acceptée', 'Prête', 'En route', 'Livrée'] as $k => $s)
                            <span class="h-1.5 flex-1 rounded-full {{ $k < 3 ? 'bg-mp-orange' : 'bg-neutral-200' }}"></span>
                        @endforeach
                    </div>
                    <div class="mt-2 grid grid-cols-4 text-[0.6875rem] vt-muted"><span>Acceptée</span><span>Prête</span><span class="font-semibold text-mp-black">En route</span><span class="text-right">Livrée</span></div>
                </div>
            </article>
            {{-- Caisse et stock --}}
            <article class="vt-bento md:col-span-3">
                <h3 class="vt-display text-xl">Caisse et stock</h3>
                <p class="mt-1.5 vt-muted">Encaissez au comptoir, suivez les ruptures du jour.</p>
                <div class="vt-mini mt-6" aria-hidden="true">
                    @foreach([['Poulet braisé', '12 restants', 'w-2/3', 'bg-green-500'], ['Poisson thon', '3 restants', 'w-1/6', 'bg-amber-500'], ['Bissap 50 cl', 'Rupture', 'w-0', 'bg-red-500']] as [$p, $q, $w, $c])
                        <div class="py-2">
                            <div class="flex justify-between text-sm"><span class="font-semibold">{{ $p }}</span><span class="vt-muted">{{ $q }}</span></div>
                            <div class="mt-1.5 h-1.5 rounded-full bg-neutral-100"><div class="h-full rounded-full {{ $w }} {{ $c }}"></div></div>
                        </div>
                    @endforeach
                </div>
            </article>
            {{-- Statistiques --}}
            <article class="vt-bento md:col-span-3">
                <h3 class="vt-display text-xl">Statistiques</h3>
                <p class="mt-1.5 vt-muted">Ventes du jour, produits phares, heures de pointe.</p>
                <div class="vt-mini mt-6" aria-hidden="true">
                    <div class="flex items-baseline justify-between"><p class="text-sm vt-muted">Cette semaine</p><p class="vt-display text-xl vt-num">612 000 F</p></div>
                    <div class="mt-3 flex items-end gap-2 h-24">
                        @foreach([['L', 45], ['M', 60], ['M', 40], ['J', 70], ['V', 85], ['S', 100], ['D', 75]] as [$d, $h])
                            <div class="flex-1 flex flex-col items-center gap-1 h-full justify-end">
                                <span class="w-full rounded-md {{ $h === 100 ? 'bg-mp-orange' : 'bg-orange-100' }}" style="height: {{ $h }}%"></span>
                                <span class="text-[0.6875rem] vt-muted">{{ $d }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </article>
        </div>
    </div>
</section>

{{-- ══════════ 3. LES TROIS OFFRES ══════════ --}}
<section id="offres" class="vt-section vt-surface scroll-mt-16">
    <div class="vt-wrap">
        <div class="text-center max-w-2xl mx-auto">
            <h2 class="vt-h2">Une offre pour chaque commerce</h2>
            <p class="vt-lead mx-auto mt-4">Choisissez votre type de commerce : les fonctions et le prix s'adaptent.</p>
        </div>
        <div class="mt-12 grid gap-4 md:grid-cols-3">
            @foreach($offers as $key => $offer)
                @php $from = G::offerFrom($key); @endphp
                <article class="vt-offer">
                    <div class="relative p-2 pb-0">
                        <img src="{{ asset('images/home/offre-'.$key.'.jpg') }}" alt="" width="800" height="533" class="w-full aspect-[4/3] object-cover rounded-[1.1rem]" loading="lazy" decoding="async">
                        @if(!empty($offer['new']))
                            <span class="vt-pill absolute top-5 left-5 bg-white text-mp-black shadow-sm">Nouveau</span>
                        @endif
                    </div>
                    <div class="flex flex-1 flex-col p-5 sm:p-6">
                        <h3 class="vt-display text-2xl">{{ $offer['title'] }}</h3>
                        <p class="mt-2 flex-1 vt-muted">{{ $offer['text'] }}</p>
                        <p class="mt-5 flex items-baseline gap-1.5">
                            <span class="text-[0.9375rem] vt-muted">dès</span>
                            <span class="vt-display text-3xl vt-num whitespace-nowrap">{{ $from['amount'] }}</span>
                            <span class="font-semibold">{{ $from['unit'] }}</span>
                        </p>
                        <a href="{{ $registerUrl($key) }}" class="vt-btn {{ !empty($offer['new']) ? 'vt-btn-dark' : 'vt-btn-ghost' }} vt-btn-block mt-5">
                            {{ ['restaurant' => 'Inscrire mon restaurant', 'stand' => 'Inscrire mon stand', 'boutique' => 'Inscrire ma boutique'][$key] }}
                        </a>
                    </div>
                </article>
            @endforeach
        </div>
    </div>
</section>

{{-- ══════════ 4. AVANTAGES (titre à gauche, liste à droite) ══════════ --}}
<section class="vt-section">
    <div class="vt-wrap grid gap-12 lg:grid-cols-2 lg:gap-20">
        <div class="lg:sticky lg:top-24 lg:self-start">
            <h2 class="vt-h2">Pensé pour les commerces de l'intérieur</h2>
            <p class="vt-lead mt-4">Daloa, Bouaké, Yamoussoukro, San-Pédro : MenuPro fonctionne sur tous les téléphones, même avec peu de réseau.</p>
            <ul class="mt-6 flex flex-wrap gap-2">
                @foreach(['Sans application à installer', 'Support WhatsApp', 'Sans engagement', 'Paiement sécurisé'] as $chip)
                    <li class="rounded-full bg-neutral-100 px-4 py-1.5 text-[0.9375rem] font-semibold">{{ $chip }}</li>
                @endforeach
            </ul>
            <div class="mt-8 grid gap-3 sm:flex sm:flex-wrap">
                <a href="https://app.menupro.ci" target="_blank" rel="noopener" class="vt-btn vt-btn-dark">Voir l'app client</a>
                <a href="{{ route('download.apk') }}" class="vt-btn vt-btn-ghost">Application Android</a>
            </div>
        </div>
        <ul class="grid gap-2">
            @foreach([
                ['Fini les appels pour confirmer', 'Chaque commande arrive avec son paiement. Vous acceptez ou refusez en un geste, le client est prévenu tout de suite.'],
                ['Vos clients commandent sans compte compliqué', 'Ils scannent votre QR code ou ouvrent app.menupro.ci, choisissent, paient. C\'est tout.'],
                ['Un livreur sans embaucher', 'Les livreurs MenuPro récupèrent la commande chez vous et la livrent. Vous ne payez qu\'une commission sur les commandes livrées.'],
                ['Vos chiffres, chaque soir', 'Ce que vous avez vendu, ce qui marche, ce qui manque en stock : tout est calculé pour vous.'],
                ['Un accompagnement près de chez vous', 'Là où MenuPro a un représentant, il vous aide à démarrer : menu, photos, QR codes.'],
            ] as [$title, $text])
                <li class="vt-benefit">
                    <h3 class="vt-display text-xl">{{ $title }}</h3>
                    <p class="mt-1.5 vt-muted">{{ $text }}</p>
                </li>
            @endforeach
        </ul>
    </div>
</section>

{{-- ══════════ 5. TARIFS (grille lue en base / config, jamais en dur) ══════════ --}}
@php
    $homePlans = [
        'stand'     => ['for' => 'Stands, kiosques, petites boutiques',          'items' => ['Menu QR et commandes', 'Mobile Money', 'Livraison incluse', '1 utilisateur']],
        'essentiel' => ['for' => 'Maquis, restaurants, boutiques', 'items' => ['Caisse et écran cuisine', 'Stock simple', 'Statistiques', '3 employés']],
        'pro'       => ['for' => 'Commerces établis et hôtels',    'items' => ['Stock par ingrédient', 'Rentabilité par produit', 'Chambres d\'hôtel', 'Commission réduite à ' . $reducedRate]],
        'gold'      => ['for' => 'Complexes et multi-sites',       'items' => ['Multi-espaces', 'Multi-sites', 'Tout illimité', 'Accompagnement dédié']],
    ];
@endphp
<section id="pricing" class="vt-section scroll-mt-16">
    <div class="vt-wrap">
        <h2 class="vt-h2">Un prix fixe. Livraison incluse.</h2>
        <p class="vt-lead mt-4">{{ $rate }} uniquement sur les commandes livrées ({{ $reducedRate }} en Pro). Aucune commande n'est jamais bloquée.</p>

        <div class="mt-10 grid gap-5 sm:grid-cols-2 lg:grid-cols-4">
            @foreach($homePlans as $slug => $card)
                @php $p = G::plan($slug); $featured = $slug === 'essentiel'; $isGold = $slug === 'gold'; @endphp
                <article class="flex flex-col rounded-2xl p-6 {{ $featured ? 'bg-mp-orange border-2 border-mp-black' : 'border-2 border-mp-line' }}">
                    <h3 class="vt-h3 text-2xl">{{ $p->name }}</h3>
                    <p class="{{ $featured ? 'text-mp-black' : 'vt-muted' }}">{{ $card['for'] }}</p>
                    <p class="mt-5">
                        @if($isGold)<span class="block text-[0.9375rem] font-semibold">Sur devis, à partir de</span>@endif
                        <span class="text-4xl font-bold vt-num whitespace-nowrap">{{ G::money($p->price) }}</span>
                        <span class="font-semibold">F/mois</span>
                    </p>
                    @if($slug === 'stand' && G::weeklyPrice() > 0)
                        <p class="font-semibold">ou {{ G::money(G::weeklyPrice()) }} F/semaine</p>
                    @endif
                    <ul class="vt-checks mt-5 space-y-2 flex-1">
                        @foreach($card['items'] as $item)<li>{{ $item }}</li>@endforeach
                    </ul>
                    @if($isGold)
                        <a href="{{ route('contact') }}?type=gold" class="vt-btn vt-btn-ghost vt-btn-block mt-6">Demander un devis</a>
                    @else
                        <a href="{{ route('register', $slug === 'stand' ? ['type' => 'stand'] : ['plan' => $slug]) }}" class="vt-btn {{ $featured ? 'vt-btn-dark' : 'vt-btn-ghost' }} vt-btn-block mt-6">Essayer {{ $trial }} jours</a>
                    @endif
                </article>
            @endforeach
        </div>
        <p class="mt-6 vt-muted">Pendant l'essai, toutes les fonctions du plan Pro sont ouvertes. {{ config('plans.welcome_pack') }}.</p>
        <p class="mt-2"><a href="{{ route('pricing') }}" class="vt-link">Comparer les plans en détail</a></p>
    </div>
</section>

{{-- ══════════ 6. TÉMOIGNAGES (masqué tant qu'aucun vrai témoignage n'est configuré : config/vitrine.php) ══════════ --}}
@if($testimonials->isNotEmpty())
<section class="vt-section bg-mp-paper">
    <div class="vt-wrap">
        <h2 class="vt-h2">Ils utilisent MenuPro</h2>
        <div class="mt-10 grid gap-6 md:grid-cols-2 lg:grid-cols-3">
            @foreach($testimonials as $t)
                <figure class="rounded-2xl bg-white border-2 border-mp-line p-6 flex flex-col">
                    <blockquote class="text-lg flex-1">« {{ $t['text'] }} »</blockquote>
                    <figcaption class="mt-5 flex items-center gap-3">
                        @if(!empty($t['photo']))
                            <img src="{{ asset($t['photo']) }}" alt="" width="48" height="48" class="w-12 h-12 rounded-full object-cover" loading="lazy" decoding="async">
                        @endif
                        <span><span class="block font-bold">{{ $t['name'] }}</span><span class="vt-muted">{{ $t['role'] }}@if(!empty($t['city'])), {{ $t['city'] }}@endif</span></span>
                    </figcaption>
                </figure>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ══════════ 7. COMMERCES EN LIGNE (données réelles) ══════════ --}}
@php
    $liveShops = \App\Models\Restaurant::where('status', \App\Enums\RestaurantStatus::ACTIVE)->latest()->take(6)->get(['name', 'slug', 'logo_path', 'city']);
@endphp
@if($liveShops->isNotEmpty())
<section class="vt-section {{ $testimonials->isNotEmpty() ? '' : 'bg-mp-paper' }}">
    <div class="vt-wrap">
        <h2 class="vt-h2">Déjà en ligne sur MenuPro</h2>
        <p class="vt-lead mt-4">Ouvrez leur page pour voir ce que vos clients verront.</p>
        <ul class="mt-8 grid gap-3 sm:grid-cols-2 lg:grid-cols-3">
            @foreach($liveShops as $shop)
                <li>
                    <a href="{{ route('r.menu', $shop->slug) }}" class="flex items-center gap-4 rounded-2xl bg-white border-2 border-mp-line p-3 hover:border-mp-black min-h-[4.5rem]">
                        @if($shop->logo_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($shop->logo_path))
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($shop->logo_path) }}" alt="" width="48" height="48" class="w-12 h-12 rounded-xl object-cover shrink-0" loading="lazy" decoding="async">
                        @else
                            <span class="w-12 h-12 rounded-xl bg-mp-orange text-mp-black font-bold text-lg flex items-center justify-center shrink-0" aria-hidden="true">{{ mb_strtoupper(mb_substr($shop->name, 0, 1)) }}</span>
                        @endif
                        <span class="min-w-0">
                            <span class="block font-bold truncate">{{ $shop->name }}</span>
                            @if($shop->city)<span class="block vt-muted truncate">{{ $shop->city }}</span>@endif
                        </span>
                    </a>
                </li>
            @endforeach
        </ul>
    </div>
</section>
@endif

{{-- ══════════ VIDÉOS (réglées par le super admin) ══════════ --}}
@if(!empty($videos))
<section class="vt-section">
    <div class="vt-wrap">
        <h2 class="vt-h2">MenuPro en vidéo</h2>
        <div class="mt-8 grid gap-6 {{ count($videos) > 1 ? 'md:grid-cols-2' : 'max-w-3xl' }}">
            @foreach($videos as $v)
                <div class="rounded-2xl overflow-hidden border-2 border-mp-line">
                    <div class="relative aspect-video">
                        <iframe class="absolute inset-0 w-full h-full" src="{{ $v['url'] }}" title="{{ $v['title'] }}" allow="accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture" allowfullscreen loading="lazy"></iframe>
                    </div>
                    @if($v['title'])<p class="px-4 py-3 font-semibold">{{ $v['title'] }}</p>@endif
                </div>
            @endforeach
        </div>
    </div>
</section>
@endif

{{-- ══════════ 8. QUESTIONS ══════════ --}}
<section id="faq" class="vt-section scroll-mt-16">
    <div class="vt-wrap max-w-3xl">
        <h2 class="vt-h2">Questions fréquentes</h2>
        <div class="mt-8 border-t-2 border-mp-black">
            @foreach($faqs as [$q, $a])
                <details class="group border-b-2 border-mp-line" @if($loop->first) open @endif>
                    <summary class="flex items-center justify-between gap-4 py-5 cursor-pointer list-none text-lg font-bold [&::-webkit-details-marker]:hidden">
                        {{ $q }}
                        <span class="flex-none w-9 h-9 rounded-full bg-mp-paper flex items-center justify-center group-open:rotate-45 transition-transform" aria-hidden="true">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.6" stroke-linecap="round" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg>
                        </span>
                    </summary>
                    <p class="pb-5 vt-muted">{{ $a }}</p>
                </details>
            @endforeach
        </div>
        <p class="mt-6"><a href="{{ route('faq') }}" class="vt-link">Toutes les questions</a></p>
    </div>
</section>

{{-- ══════════ 9. REJOINDRE ══════════ --}}
<section class="pb-14 md:pb-20">
    <div class="vt-wrap grid gap-4 md:grid-cols-2">
        <a href="{{ route('representative.program') }}" class="rounded-2xl border-2 border-mp-line p-6 hover:border-mp-black block">
            <h2 class="vt-h3">Devenir représentant dans votre ville</h2>
            <p class="mt-1 vt-muted">Présentez MenuPro aux commerces de votre ville et touchez une part des abonnements.</p>
        </a>
        <a href="{{ route('devenir-livreur') }}" class="rounded-2xl border-2 border-mp-line p-6 hover:border-mp-black block">
            <h2 class="vt-h3">Devenir livreur</h2>
            <p class="mt-1 vt-muted">Recevez des courses près de vous et soyez payé sur votre Mobile Money.</p>
        </a>
    </div>
</section>

{{-- ══════════ 10. APPEL FINAL ══════════ --}}
<section class="bg-mp-orange">
    <div class="vt-wrap py-14 md:py-20 grid gap-8 md:grid-cols-[1.5fr_1fr] md:items-center">
        <div>
            <h2 class="vt-h2">Mettez votre commerce en ligne aujourd'hui.</h2>
            <p class="mt-4 text-lg">{{ $trial }} jours gratuits. Vous ne payez que si vous continuez.</p>
        </div>
        <div class="grid gap-3">
            <a href="{{ route('register') }}" class="vt-btn vt-btn-dark vt-btn-block">Créer mon commerce</a>
            <a href="{{ route('contact') }}?type=demo" class="vt-btn vt-btn-light vt-btn-block">Être rappelé par l'équipe</a>
        </div>
    </div>
</section>
</x-layouts.public>
