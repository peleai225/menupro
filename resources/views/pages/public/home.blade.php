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

{{-- ══════════ 1. ACCUEIL ══════════ --}}
<section class="pt-8 pb-14 md:pt-16 md:pb-20">
    <div class="vt-wrap grid gap-12 lg:grid-cols-[1.15fr_1fr] lg:items-center">
        <div>
            <h1 class="vt-h1">Vos clients commandent. Vous encaissez en Mobile Money.</h1>
            <p class="vt-lead mt-5">Restaurant, maquis, stand de rue ou boutique&nbsp;: MenuPro met votre commerce en ligne, encaisse les paiements Wave, Orange, MTN et Moov, et livre vos clients.</p>
            <div class="mt-8 grid gap-3 sm:flex sm:flex-wrap">
                <a href="{{ route('register') }}" class="vt-btn vt-btn-primary">Créer mon commerce</a>
                <a href="{{ route('pricing') }}" class="vt-btn vt-btn-ghost">Voir les tarifs</a>
            </div>
            <p class="mt-4 vt-muted">Essai gratuit de {{ $trial }} jours, sans carte bancaire ni engagement.</p>

            <ul class="mt-8 flex flex-wrap items-center gap-3" aria-label="Paiements acceptés">
                @foreach([['wave.png','Wave'],['orange-money.png','Orange Money'],['mtn-momo.png','MTN MoMo'],['moov-money.png','Moov Money']] as [$img, $name])
                    <li class="flex items-center gap-2 rounded-full border border-mp-line pl-1 pr-3 py-1 bg-white">
                        <img src="{{ asset('images/payments/'.$img) }}" alt="" width="28" height="28" class="w-7 h-7 rounded-full object-contain" loading="lazy" decoding="async">
                        <span class="text-[0.9375rem] font-semibold">{{ $name }}</span>
                    </li>
                @endforeach
            </ul>
        </div>

        {{-- Aperçu de l'écran client (HTML pur : aucune image à télécharger) --}}
        <figure class="relative mx-auto w-full max-w-[19rem]">
            <div class="vt-phone" role="img" aria-label="Exemple d'écran de commande sur app.menupro.ci : un panier de deux plats payé par Wave, avec suivi de livraison.">
                <div class="vt-phone-screen" aria-hidden="true">
                    <img src="{{ asset('images/home/offre-restaurant.jpg') }}" alt="" width="800" height="533" class="w-full h-28 object-cover" fetchpriority="high" decoding="async">
                    <div class="bg-mp-orange px-4 pt-5 pb-4">
                        <p class="text-sm font-semibold">app.menupro.ci</p>
                        <p class="text-xl font-bold leading-tight mt-1">Maquis du Carrefour</p>
                        <p class="text-sm">Daloa · ouvert jusqu'à 23 h</p>
                    </div>
                    <ul class="px-4 py-3 space-y-3 text-[0.9375rem]">
                        <li class="flex justify-between gap-3"><span>2 × Garba poisson thon</span><span class="font-bold vt-num whitespace-nowrap">2 000 F</span></li>
                        <li class="flex justify-between gap-3"><span>1 × Poulet braisé + attiéké</span><span class="font-bold vt-num whitespace-nowrap">3 500 F</span></li>
                        <li class="flex justify-between gap-3 border-t border-mp-line pt-3 font-bold"><span>Total</span><span class="vt-num whitespace-nowrap">5 500 F</span></li>
                    </ul>
                    <div class="px-4 pb-4">
                        <div class="rounded-xl bg-mp-black text-white text-center py-3 font-bold">Payer avec Wave</div>
                        <div class="mt-3 rounded-xl border-2 border-mp-black p-3">
                            <p class="text-sm font-bold">Commande en route</p>
                            <div class="mt-2 h-2 rounded-full bg-mp-line overflow-hidden"><div class="h-full w-2/3 bg-mp-orange"></div></div>
                            <p class="text-sm mt-2 vt-muted">Le livreur arrive dans 12 min</p>
                        </div>
                    </div>
                </div>
            </div>
            {{-- Notification côté commerçant --}}
            <div class="relative mt-4 ml-auto mr-2 sm:-mr-8 max-w-[14rem] rounded-2xl bg-white border-2 border-mp-black p-3 shadow-[6px_6px_0_0_#F97316]" aria-hidden="true">
                <p class="text-sm font-bold">Nouvelle commande</p>
                <p class="text-sm vt-muted">5 500 F payés par Wave</p>
            </div>
            <figcaption class="mt-4 text-center text-[0.9375rem] vt-muted">Exemple d'écran client</figcaption>
        </figure>
    </div>
</section>

{{-- ══════════ 2. LES TROIS OFFRES ══════════ --}}
<section id="offres" class="vt-section bg-mp-paper scroll-mt-16">
    <div class="vt-wrap">
        <h2 class="vt-h2 max-w-2xl">Un outil pour chaque commerce</h2>
        <p class="vt-lead mt-4">Choisissez votre type de commerce&nbsp;: les fonctions et le prix s'adaptent.</p>

        <div class="mt-10 grid gap-6 md:grid-cols-3">
            @foreach($offers as $key => $offer)
                @php $from = G::offerFrom($key); @endphp
                <article class="vt-tag flex flex-col {{ !empty($offer['new']) ? 'vt-tag-new' : '' }}">
                    <img src="{{ asset('images/home/offre-'.$key.'.jpg') }}" alt="" width="800" height="533" class="w-full aspect-[3/2] object-cover rounded-xl mb-5" loading="lazy" decoding="async">
                    <div class="flex items-start justify-between gap-3 pl-8">
                        <svg class="w-9 h-9 shrink-0" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round" viewBox="0 0 24 24" aria-hidden="true"><path d="{{ $offer['icon'] }}"/></svg>
                        @if(!empty($offer['new']))
                            <span class="vt-pill bg-mp-black text-white">Nouveau</span>
                        @endif
                    </div>
                    <h3 class="vt-h3 mt-5 text-2xl">{{ $offer['title'] }}</h3>
                    <p class="mt-2 flex-1 {{ !empty($offer['new']) ? 'text-mp-black' : 'vt-muted' }}">{{ $offer['text'] }}</p>
                    <p class="mt-5">
                        <span class="block text-[0.9375rem] font-semibold">À partir de</span>
                        <span class="text-4xl font-bold vt-num whitespace-nowrap">{{ $from['amount'] }}</span>
                        <span class="font-semibold">{{ $from['unit'] }}</span>
                    </p>
                    <a href="{{ $registerUrl($key) }}" class="vt-btn {{ !empty($offer['new']) ? 'vt-btn-dark' : 'vt-btn-primary' }} vt-btn-block mt-5">
                        {{ ['restaurant' => 'Inscrire mon restaurant', 'stand' => 'Inscrire mon stand', 'boutique' => 'Inscrire ma boutique'][$key] }}
                    </a>
                </article>
            @endforeach
        </div>
    </div>
</section>

{{-- ══════════ 3. CÔTÉ CLIENTS (vraie séquence) ══════════ --}}
<section id="how-it-works" class="vt-section scroll-mt-16">
    <div class="vt-wrap grid gap-10 lg:grid-cols-[1fr_1.3fr]">
        <div>
            <h2 class="vt-h2">Pour vos clients, c'est simple</h2>
            <p class="vt-lead mt-4">Pas de compte compliqué, pas d'application obligatoire. Ils ouvrent app.menupro.ci, ou scannent votre QR code.</p>
            <div class="mt-8 grid gap-3 sm:flex sm:flex-wrap">
                <a href="https://app.menupro.ci" target="_blank" rel="noopener" class="vt-btn vt-btn-dark">Ouvrir app.menupro.ci</a>
                <a href="{{ route('download.apk') }}" class="vt-btn vt-btn-ghost">Application Android</a>
            </div>
        </div>
        <ol class="grid gap-4">
            @foreach([
                ['Ils commandent', 'Depuis leur téléphone, sur votre page MenuPro : plats ou articles, quantités, sur place, à emporter ou en livraison.'],
                ['Ils paient', 'Par Wave, Orange Money, MTN MoMo ou Moov Money. Ou en espèces si vous l\'acceptez.'],
                ['Ils suivent leur commande', 'En direct : acceptée, en préparation, en route avec le livreur, livrée.'],
            ] as $i => [$title, $text])
                <li class="flex gap-4 rounded-2xl border-2 border-mp-line p-5">
                    <span class="flex-none w-11 h-11 rounded-full bg-mp-orange text-mp-black font-bold text-xl flex items-center justify-center" aria-hidden="true">{{ $i + 1 }}</span>
                    <div>
                        <h3 class="vt-h3">{{ $title }}</h3>
                        <p class="mt-1 vt-muted">{{ $text }}</p>
                    </div>
                </li>
            @endforeach
        </ol>
    </div>
</section>

{{-- ══════════ 4. CE QUE VOUS GÉREZ ══════════ --}}
<section class="vt-section bg-mp-black text-white">
    <div class="vt-wrap">
        <h2 class="vt-h2 max-w-2xl">Tout se pilote depuis votre téléphone</h2>
        <ul class="mt-10 grid gap-x-10 sm:grid-cols-2 lg:grid-cols-3">
            @foreach([
                ['Commandes en direct', 'Alerte sonore à chaque commande, écran cuisine, impression du ticket.'],
                ['Livraison incluse', 'Les livreurs MenuPro récupèrent et livrent. ' . $rate . ' uniquement sur les commandes livrées.'],
                ['Mobile Money', 'Les paiements arrivent dans votre portefeuille MenuPro, retirables sur votre Mobile Money.'],
                ['Caisse et stock', 'Encaissez au comptoir, suivez votre stock du jour et les ruptures.'],
                ['Statistiques', 'Ventes du jour, produits qui marchent le mieux, heures de pointe.'],
                ['Supports imprimés', 'Autocollants QR et affiche vitrine fournis selon votre plan.'],
            ] as [$title, $text])
                <li class="border-t border-white/20 py-5">
                    <h3 class="vt-h3 text-mp-orange">{{ $title }}</h3>
                    <p class="mt-1 text-neutral-200">{{ $text }}</p>
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
