@use('App\Support\VitrineGrid', 'G')
<x-layouts.app :title="$title ?? 'Connexion'">
    @php
        $appName = config('app.name', 'MenuPro');
        $logoUrl = null;
        try {
            $appName = \App\Models\SystemSetting::get('app_name', $appName);
            $logo = \App\Models\SystemSetting::get('logo', '');
            if (!empty($logo)) {
                $storage = \Illuminate\Support\Facades\Storage::disk('public');
                if ($storage->exists($logo)) {
                    $logoUrl = asset('storage/' . ltrim($logo, '/'));
                }
            }
        } catch (\Throwable $e) {
            $appName = config('app.name', 'MenuPro');
            $logoUrl = null;
        }
        $logoUrl ??= asset('images/logo-menupro.png');
        try {
            $authOffers = collect(config('vitrine.offers', []))->map(fn ($o, $key) => ['label' => $o['label']] + G::offerFrom($key));
        } catch (\Throwable $e) {
            $authOffers = collect();
        }
    @endphp
    {{-- Fond clair à halo orange, formulaire centré dans une carte. Les cartes flottantes (grand écran)
         sont un aperçu décoratif du produit, comme sur l'accueil : aucune image à charger. --}}
    <div class="vt vt-auth min-h-screen flex flex-col">
        <header class="vt-wrap h-16 flex items-center justify-between gap-3">
            <a href="{{ route('home') }}" class="inline-flex items-center" aria-label="{{ $appName }} — accueil">
                <img src="{{ $logoUrl }}" alt="{{ $appName }}" width="108" height="38" class="h-8 sm:h-9 w-auto object-contain">
            </a>
            <a href="{{ route('home') }}" class="inline-flex items-center min-h-11 px-3 rounded-xl text-[0.9375rem] font-semibold text-neutral-700 hover:text-mp-black hover:bg-black/[.04]">Retour à l'accueil</a>
        </header>

        <main class="flex-1">
            <div class="vt-wrap relative py-4 sm:py-8 lg:py-12">
                {{-- Carte flottante gauche : nouvelle commande --}}
                <div class="vt-float vt-float-left !top-16" aria-hidden="true">
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
                {{-- Carte flottante droite : offres (prix lus dans la grille) --}}
                @if($authOffers->isNotEmpty())
                    <div class="vt-float vt-float-right !top-40" aria-hidden="true">
                        <p class="text-sm vt-muted">Offres MenuPro</p>
                        <ul class="mt-2 divide-y divide-neutral-100">
                            @foreach($authOffers as $offer)
                                <li class="flex items-baseline justify-between gap-2 py-1.5 text-sm">
                                    <span class="font-semibold">{{ $offer['label'] }}</span>
                                    <span class="whitespace-nowrap vt-muted">dès <span class="font-bold text-mp-black vt-num">{{ $offer['amount'] }}</span></span>
                                </li>
                            @endforeach
                        </ul>
                        <p class="mt-2 text-xs vt-muted">Livraison incluse · Mobile Money</p>
                    </div>
                @endif

                <div class="relative mx-auto w-full max-w-[32rem] vt-card px-5 py-7 sm:p-9">
                    {{ $slot }}
                </div>
            </div>
        </main>

        <footer class="vt-wrap pb-4">
            <ul class="flex flex-wrap justify-center gap-x-6 text-sm text-neutral-600">
                <li><a href="{{ route('terms') }}" class="inline-flex min-h-11 items-center hover:text-mp-black">Conditions</a></li>
                <li><a href="{{ route('privacy') }}" class="inline-flex min-h-11 items-center hover:text-mp-black">Confidentialité</a></li>
                <li><a href="{{ route('contact') }}" class="inline-flex min-h-11 items-center hover:text-mp-black">Aide</a></li>
            </ul>
        </footer>
    </div>
</x-layouts.app>
