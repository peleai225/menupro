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

{{-- ══════════ 1. ACCUEIL : la file de commandes (signature) ══════════ --}}
@php
    $tickets = [
        ['n' => '1186', 'who' => 'Awa K.',     'mode' => 'Livraison',  'items' => '2 × Garba thon, 1 × Bissap', 'total' => '2 700', 'pay' => 'wave.png',         'payName' => 'Wave',         'state' => 'new'],
        ['n' => '1185', 'who' => 'Table 4',    'mode' => 'Sur place',  'items' => '1 × Poulet braisé attiéké',  'total' => '3 500', 'pay' => 'orange-money.png', 'payName' => 'Orange Money', 'state' => 'cooking'],
        ['n' => '1184', 'who' => 'Moussa D.',  'mode' => 'À emporter', 'items' => '3 × Alloco poisson',         'total' => '4 500', 'pay' => 'mtn-momo.png',     'payName' => 'MTN MoMo',     'state' => 'done'],
    ];
@endphp
<section class="vt-hero">
    <div class="vt-wrap grid grid-cols-[minmax(0,1fr)] gap-12 lg:grid-cols-[minmax(0,1.15fr)_minmax(0,1fr)] lg:items-center lg:gap-16">
        <div>
            <h1 class="vt-h1">Les commandes arrivent. Vous n'avez plus à décrocher.</h1>
            <p class="vt-lead mt-6">MenuPro met votre restaurant, votre stand ou votre boutique en ligne. Vos clients commandent et paient en Mobile Money, vous acceptez d'un geste, un livreur s'occupe du reste.</p>
            <div class="mt-8 grid gap-3 sm:flex sm:flex-wrap">
                <a href="{{ route('register') }}" class="vt-btn vt-btn-primary">Créer mon commerce</a>
                <a href="{{ route('pricing') }}" class="vt-btn vt-btn-ghost">Voir les tarifs</a>
            </div>
            <p class="mt-4 vt-muted">{{ $trial }} jours gratuits, sans carte bancaire ni engagement.</p>

            <div class="mt-10 flex flex-wrap items-center gap-x-5 gap-y-3">
                <p class="text-[0.9375rem] font-semibold vt-muted w-full sm:w-auto">Paiements acceptés</p>
                <ul class="flex items-center gap-2.5" aria-label="Paiements acceptés">
                    @foreach([['wave.png','Wave'],['orange-money.png','Orange Money'],['mtn-momo.png','MTN MoMo'],['moov-money.png','Moov Money']] as [$img, $name])
                        <li><img src="{{ asset('images/payments/'.$img) }}" alt="{{ $name }}" title="{{ $name }}" width="40" height="40" class="w-10 h-10 rounded-xl object-contain bg-white ring-1 ring-black/5" loading="lazy" decoding="async"></li>
                    @endforeach
                </ul>
            </div>
        </div>

        {{-- File de commandes du commerçant (HTML pur) --}}
        <figure class="vt-rail" role="img" aria-label="Exemple : trois commandes arrivent sur le tableau de bord MenuPro, payées par Wave, Orange Money et MTN MoMo. La plus récente attend d'être acceptée.">
            <div aria-hidden="true">
                <div class="flex items-center justify-between px-1 pb-4 text-white">
                    <p class="vt-display text-xl">Commandes</p>
                    <p class="flex items-center gap-2 text-[0.9375rem] font-semibold"><span class="vt-live"></span>En direct</p>
                </div>
                <ol class="grid gap-3">
                    @foreach($tickets as $i => $t)
                        <li class="vt-ticket {{ $t['state'] === 'new' ? 'is-new' : '' }}" style="--i: {{ $i }}">
                            <div class="flex items-start justify-between gap-3">
                                <div class="min-w-0">
                                    <p class="font-bold">#{{ $t['n'] }} · {{ $t['who'] }}</p>
                                    <p class="text-[0.9375rem] vt-muted truncate">{{ $t['items'] }}</p>
                                </div>
                                <p class="vt-display text-xl vt-num whitespace-nowrap">{{ $t['total'] }} F</p>
                            </div>
                            <div class="mt-3 flex items-center justify-between gap-3">
                                <p class="flex items-center gap-2 text-[0.9375rem] font-semibold">
                                    <img src="{{ asset('images/payments/'.$t['pay']) }}" alt="" width="22" height="22" class="w-[22px] h-[22px] rounded-md object-contain" loading="lazy" decoding="async">
                                    Payé · {{ $t['mode'] }}
                                </p>
                                @if($t['state'] === 'new')
                                    <span class="rounded-lg bg-mp-orange px-4 py-2 font-bold text-[0.9375rem]">Accepter</span>
                                @elseif($t['state'] === 'cooking')
                                    <span class="rounded-lg bg-amber-100 text-amber-900 px-3 py-1.5 font-semibold text-sm">En cuisine</span>
                                @else
                                    <span class="rounded-lg bg-green-100 text-green-800 px-3 py-1.5 font-semibold text-sm">Prête</span>
                                @endif
                            </div>
                        </li>
                    @endforeach
                </ol>
            </div>
        </figure>
    </div>
</section>

{{-- ══════════ 2. LES TROIS OFFRES ══════════ --}}
<section id="offres" class="vt-section vt-surface scroll-mt-16">
    <div class="vt-wrap">
        <h2 class="vt-h2 max-w-2xl">Un outil pour chaque commerce</h2>
        <p class="vt-lead mt-4">Choisissez votre type de commerce : les fonctions et le prix s'adaptent.</p>

        <div class="mt-10 grid gap-5 md:grid-cols-3">
            @foreach($offers as $key => $offer)
                @php $from = G::offerFrom($key); @endphp
                <article class="vt-offer">
                    <div class="relative">
                        <img src="{{ asset('images/home/offre-'.$key.'.jpg') }}" alt="" width="800" height="533" class="w-full aspect-[4/3] object-cover" loading="lazy" decoding="async">
                        @if(!empty($offer['new']))
                            <span class="vt-pill absolute top-3 left-3 bg-mp-orange text-mp-black">Nouveau</span>
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
                        <a href="{{ $registerUrl($key) }}" class="vt-btn {{ !empty($offer['new']) ? 'vt-btn-primary' : 'vt-btn-dark' }} vt-btn-block mt-5">
                            {{ ['restaurant' => 'Inscrire mon restaurant', 'stand' => 'Inscrire mon stand', 'boutique' => 'Inscrire ma boutique'][$key] }}
                        </a>
                    </div>
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
        <ol class="vt-steps">
            @foreach([
                ['Ils commandent', 'Depuis leur téléphone, sur votre page MenuPro : plats ou articles, quantités, sur place, à emporter ou en livraison.'],
                ['Ils paient', 'Par Wave, Orange Money, MTN MoMo ou Moov Money. Ou en espèces si vous l\'acceptez.'],
                ['Ils suivent leur commande', 'En direct : acceptée, en préparation, en route avec le livreur, livrée.'],
            ] as $i => [$title, $text])
                <li class="vt-step">
                    <span class="vt-step-n" aria-hidden="true">{{ $i + 1 }}</span>
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
        <div class="grid gap-10 lg:grid-cols-[1fr_1.1fr] lg:items-center">
        <div>
        <h2 class="vt-h2 max-w-2xl">Tout se pilote depuis votre téléphone</h2>
        <p class="vt-lead vt-on-dark mt-4">Votre tableau de bord MenuPro&nbsp;: chaque commande arrive avec une alerte, vous l'acceptez en un geste, et vous voyez vos ventes du jour en direct.</p>
        </div>

        {{-- Aperçu du tableau de bord commerçant (HTML pur, aucune image) --}}
        <figure class="mx-auto w-full max-w-md text-mp-black">
            <div class="rounded-3xl bg-white p-4 sm:p-5 shadow-[0_40px_80px_-30px_rgba(249,115,22,.45)]" role="img" aria-label="Exemple de tableau de bord MenuPro : ventes du jour, nouvelle commande à accepter et graphique des ventes.">
                <div aria-hidden="true">
                    <div class="flex items-center justify-between">
                        <p class="font-bold text-lg">Aujourd'hui</p>
                        <span class="vt-pill bg-green-100 text-green-800 text-sm">● Ouvert</span>
                    </div>
                    <div class="mt-3 grid grid-cols-3 gap-2">
                        @foreach([['Ventes', '87 500 F'], ['Commandes', '24'], ['Livrées', '19']] as [$l, $v])
                            <div class="rounded-xl bg-mp-paper p-3">
                                <p class="text-xs font-semibold text-neutral-600">{{ $l }}</p>
                                <p class="font-bold vt-num text-base sm:text-lg whitespace-nowrap">{{ $v }}</p>
                            </div>
                        @endforeach
                    </div>
                    <div class="mt-3 rounded-2xl ring-2 ring-mp-orange p-3">
                        <div class="flex items-start justify-between gap-2">
                            <div>
                                <p class="text-sm font-bold text-orange-700">Nouvelle commande · il y a 30 s</p>
                                <p class="font-bold mt-0.5">#1184 · Awa K. · Livraison</p>
                                <p class="text-sm text-neutral-600">2 × Garba thon, 1 × Jus de bissap</p>
                            </div>
                            <p class="font-bold vt-num whitespace-nowrap">2 700 F</p>
                        </div>
                        <p class="mt-1 text-sm font-semibold text-green-700">Payée par Wave</p>
                        <div class="mt-3 grid grid-cols-2 gap-2">
                            <span class="rounded-xl bg-mp-orange text-mp-black text-center py-2.5 font-bold">Accepter</span>
                            <span class="rounded-xl border-2 border-mp-line text-center py-2.5 font-bold">Refuser</span>
                        </div>
                    </div>
                    <div class="mt-3">
                        <p class="text-sm font-semibold text-neutral-600">Ventes par heure</p>
                        <div class="mt-2 flex items-end gap-1.5 h-20">
                            @foreach([20, 35, 70, 95, 55, 30, 45, 80, 100, 60] as $h)
                                <span class="flex-1 rounded-t {{ $h >= 90 ? 'bg-mp-orange' : 'bg-neutral-300' }}" style="height: {{ $h }}%"></span>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>
            <figcaption class="mt-5 text-center text-[0.9375rem] text-neutral-300">Exemple de tableau de bord commerçant</figcaption>
        </figure>
        </div>

        <ul class="mt-12 grid gap-x-10 sm:grid-cols-2 lg:grid-cols-3">
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
