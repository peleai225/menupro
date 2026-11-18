@use('App\Support\VitrineGrid', 'G')
@php
    $fmt = fn ($n) => G::money($n);
    $trial = G::trialDays();
    $rate = G::commission();
    $reducedRate = G::reducedCommission();
    $plans = G::plans();
    $discounts = collect(config('plans.billing_periods'))->only(['quarterly', 'semiannual', 'annual'])->pluck('discount')->map(fn ($d) => $d . ' %');

    $groups = [
        'general' => ['Général', [
            ["Qu'est-ce que MenuPro ?", "MenuPro met votre commerce en ligne : vos clients commandent depuis leur téléphone sur app.menupro.ci, paient en Mobile Money ou en espèces, et suivent leur commande en direct. Vous gérez tout depuis votre tableau de bord : commandes, livraisons, caisse, stock et statistiques."],
            ["Pour quels commerces ?", "Restaurants et maquis, stands de rue (garba, jus, paninis…), boutiques et superettes de quartier, hôtels et complexes. Chaque type de commerce a son offre."],
            ["Je tiens une boutique. Comment ça marche ?", "Avec l'offre Boutique, vous publiez votre catalogue d'articles avec photos et prix. Vos clients commandent en ligne, paient par Mobile Money et se font livrer par les livreurs MenuPro, ou passent récupérer leur commande."],
            ["Comment créer mon commerce sur MenuPro ?", "Cliquez sur « Créer mon commerce », choisissez votre type de commerce, puis renseignez votre compte et votre établissement. Vous ajoutez ensuite vos produits depuis votre téléphone."],
            ["Dois-je avoir des compétences techniques ?", "Non. Si vous savez utiliser WhatsApp, vous saurez utiliser MenuPro. Votre représentant local ou notre équipe peut aussi vous aider à démarrer."],
            ["Mes clients doivent-ils installer une application ?", "Non. Ils commandent depuis le navigateur de leur téléphone, en scannant votre QR code ou en ouvrant votre lien. Une application Android existe aussi pour ceux qui la préfèrent."],
        ]],
        'pricing' => ['Tarifs', [
            ["Combien coûte MenuPro ?", "Stand : " . $fmt($plans['stand']->price) . " F/mois ou " . $fmt(G::weeklyPrice()) . " F/semaine. " . $plans['essentiel']->name . " (restaurants, maquis, boutiques) : " . $fmt($plans['essentiel']->price) . " F/mois. Pro : " . $fmt($plans['pro']->price) . " F/mois. Gold : sur devis, à partir de " . $fmt($plans['gold']->price) . " F/mois. Essai gratuit de " . $trial . " jours avec toutes les fonctions du plan Pro."],
            ["Y a-t-il des frais cachés ?", "Non. La livraison est incluse : MenuPro prend " . $rate . " uniquement sur les commandes livrées (" . $reducedRate . " avec le plan Pro). Pas de frais de mise en service ni de résiliation, et aucune commande n'est jamais bloquée."],
            ["Puis-je payer pour plusieurs mois ?", "Oui : au mois, au trimestre, au semestre ou à l'année (et à la semaine pour le plan Stand). Vous économisez " . $discounts->join(', ', ' ou ') . " selon la durée."],
            ["Comment payer mon abonnement ?", "En Mobile Money : Wave, Orange Money, MTN MoMo ou Moov Money. Aucun prélèvement automatique : vous payez quand vous renouvelez."],
            ["Que se passe-t-il si je dépasse les commandes prévues par mon plan ?", "Rien de bloquant : vos clients continuent de commander. Vous recevez simplement une proposition de passer au plan supérieur."],
        ]],
        'features' => ['Fonctions', [
            ["Comment mes clients passent-ils commande ?", "Ils scannent le QR code sur la table ou la vitrine, ou ouvrent le lien que vous partagez sur WhatsApp. Ils choisissent leurs produits, paient et suivent la commande. Vous recevez une alerte sonore à chaque commande."],
            ["Comment mes clients paient-ils ?", "Par Wave, Orange Money, MTN MoMo ou Moov Money, ou en espèces si vous l'acceptez. L'argent arrive sur votre portefeuille MenuPro, retirable sur votre Mobile Money."],
            ["Sur place, à emporter, en livraison ?", "Les trois. Vous activez ou désactivez chaque option. La livraison est assurée par les livreurs MenuPro."],
            ["Puis-je gérer mon stock ?", "Oui : stock simple à partir du plan " . $plans['essentiel']->name . ", et stock par ingrédient avec la rentabilité de chaque produit dans le plan Pro."],
            ["Puis-je avoir plusieurs employés ?", "Oui, selon le plan : " . $plans['essentiel']->max_employees . " employés avec " . $plans['essentiel']->name . ", " . $plans['pro']->max_employees . " avec Pro, illimité avec Gold. Chacun a son propre accès."],
            ["Comment créer des codes promo ?", "Dans votre tableau de bord, section « Codes promo » : réduction en pourcentage ou en montant fixe, avec limite d'utilisation et dates de validité."],
            ["Comment imprimer mon QR code ?", "Dans votre tableau de bord, section « QR Code » : téléchargez vos QR codes prêts à imprimer. Selon votre plan, des autocollants et une affiche vitrine vous sont fournis."],
        ]],
        'technical' => ['Sécurité et aide', [
            ["Mes données sont-elles protégées ?", "Oui. Les échanges sont chiffrés (HTTPS) et vos données sont traitées conformément à la loi ivoirienne n° 2013-450 relative à la protection des données à caractère personnel."],
            ["Les paiements sont-ils sécurisés ?", "Les paiements passent par des opérateurs Mobile Money et des partenaires de paiement agréés. MenuPro ne voit jamais le code secret de vos clients."],
            ["Que faire si j'ai un problème ?", "Écrivez-nous sur WhatsApp ou via la page Contact. Les plans Pro et Gold bénéficient d'un support prioritaire."],
            ["MenuPro fonctionne-t-il avec une connexion faible ?", "Une connexion internet est nécessaire, mais les pages de commande sont pensées pour se charger vite, même sur une connexion faible."],
        ]],
    ];
@endphp
<x-layouts.public title="Questions fréquentes" description="Réponses aux questions sur MenuPro : tarifs, commandes en ligne, paiement Mobile Money, livraison, restaurants, stands et boutiques.">
    <section class="pt-10 pb-6 md:pt-16">
        <div class="vt-wrap max-w-3xl">
            <h1 class="vt-h1">Questions fréquentes</h1>
            <p class="vt-lead mt-4">Vous ne trouvez pas votre réponse ? <a href="{{ route('contact') }}" class="vt-link">Écrivez-nous</a>.</p>

            <div class="mt-8" x-data="{ search: '' }">
                <label for="faq-search" class="vt-label">Rechercher une question</label>
                <input type="search" id="faq-search" x-model="search" placeholder="Ex. livraison, boutique, paiement"
                       @input="
                           const s = search.toLowerCase();
                           document.querySelectorAll('[data-faq-item]').forEach(el => { el.hidden = !!s && !el.textContent.toLowerCase().includes(s); });
                           document.querySelectorAll('[data-faq-group]').forEach(g => { g.hidden = !!s && !g.querySelector('[data-faq-item]:not([hidden])'); });
                       "
                       class="vt-input">
            </div>

            <nav class="mt-6 flex flex-wrap gap-2" aria-label="Thèmes">
                @foreach($groups as $key => [$label])
                    <a href="#{{ $key }}" class="inline-flex items-center min-h-11 px-4 rounded-full border-2 border-mp-line font-semibold hover:border-mp-black">{{ $label }}</a>
                @endforeach
            </nav>
        </div>
    </section>

    <section class="pb-16 md:pb-24">
        <div class="vt-wrap max-w-3xl space-y-12">
            @foreach($groups as $key => [$label, $items])
                <div id="{{ $key }}" class="scroll-mt-20" data-faq-group>
                    <h2 class="vt-h2 text-[1.75rem]">{{ $label }}</h2>
                    <div class="mt-4 border-t-2 border-mp-black">
                        @foreach($items as [$q, $a])
                            <details class="group border-b-2 border-mp-line" data-faq-item>
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
                </div>
            @endforeach
        </div>
    </section>

    <section class="bg-mp-orange">
        <div class="vt-wrap py-12 md:py-16 grid gap-6 md:grid-cols-[1.5fr_1fr] md:items-center">
            <h2 class="vt-h2">Prêt à essayer ? {{ $trial }} jours gratuits.</h2>
            <div class="grid gap-3">
                <a href="{{ route('register') }}" class="vt-btn vt-btn-dark vt-btn-block">Créer mon commerce</a>
                <a href="{{ route('contact') }}" class="vt-btn vt-btn-light vt-btn-block">Poser une question</a>
            </div>
        </div>
    </section>
</x-layouts.public>
