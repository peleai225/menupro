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

        // Visuel facultatif du panneau gauche (public/images/auth/custom.*) : affiché dans un cadre,
        // jamais derrière le texte, et uniquement sur grand écran (lazy : rien n'est chargé sur téléphone).
        $customAuthImage = null;
        foreach (['custom.webp', 'custom.jpg', 'custom.png'] as $name) {
            if (file_exists(public_path('images/auth/' . $name))) {
                $customAuthImage = asset('images/auth/' . $name);
                break;
            }
        }
        try {
            $authOffers = collect(config('vitrine.offers', []))->map(fn ($o, $key) => ['label' => $o['label']] + G::offerFrom($key));
        } catch (\Throwable $e) {
            $authOffers = collect();
        }
    @endphp
    <div class="vt min-h-screen flex flex-col lg:flex-row bg-white">
        {{-- ═══ Panneau gauche : fond noir uni, texte lisible, rien ne chevauche le visuel ═══ --}}
        <aside class="hidden lg:flex lg:w-[44%] xl:w-[46%] shrink-0 bg-mp-black text-white">
            <div class="flex flex-col w-full p-10 xl:p-14 gap-10">
                <a href="{{ route('home') }}" class="self-start bg-white rounded-xl px-3 py-2" aria-label="{{ $appName }} — accueil">
                    <img src="{{ $logoUrl }}" alt="{{ $appName }}" width="120" height="42" class="h-9 w-auto object-contain">
                </a>

                <div class="max-w-md">
                    <p class="text-4xl xl:text-5xl font-bold leading-[1.08] tracking-tight">Votre commerce en ligne, payé en Mobile Money.</p>
                    <p class="mt-5 text-lg text-neutral-200">Vos clients commandent sur app.menupro.ci et paient par Wave, Orange, MTN ou Moov. Livraison incluse.</p>

                    @if($authOffers->isNotEmpty())
                        <ul class="mt-8 divide-y divide-white/15 border-y border-white/15">
                            @foreach($authOffers as $offer)
                                <li class="flex items-baseline justify-between gap-4 py-3">
                                    <span class="font-semibold">{{ $offer['label'] }}</span>
                                    <span class="text-neutral-200 whitespace-nowrap">dès <span class="font-bold text-mp-orange">{{ $offer['amount'] }}</span> {{ $offer['unit'] }}</span>
                                </li>
                            @endforeach
                        </ul>
                    @endif
                </div>

                @if($customAuthImage)
                    <img src="{{ $customAuthImage }}" alt="" class="w-full max-w-md rounded-2xl object-cover aspect-[16/9]" loading="lazy" decoding="async">
                @endif

                <p class="mt-auto text-neutral-300">Données protégées (loi ivoirienne n° 2013-450).</p>
            </div>
        </aside>

        {{-- ═══ Formulaire ═══ --}}
        <div class="flex-1 flex flex-col min-h-screen lg:min-h-0">
            {{-- En-tête mobile : logo sur fond blanc --}}
            <div class="lg:hidden border-b border-mp-line">
                <div class="px-4 h-16 flex items-center justify-between">
                    <a href="{{ route('home') }}" aria-label="{{ $appName }} — accueil">
                        <img src="{{ $logoUrl }}" alt="{{ $appName }}" width="108" height="38" class="h-8 w-auto object-contain">
                    </a>
                    <a href="{{ route('home') }}" class="inline-flex items-center min-h-11 px-2 font-semibold underline underline-offset-4">Accueil</a>
                </div>
            </div>

            <main class="flex-1 flex items-start lg:items-center justify-center px-4 sm:px-8 lg:px-14">
                <div class="w-full max-w-[30rem] mx-auto py-8 lg:py-12">
                    {{ $slot }}
                </div>
            </main>

            <footer class="px-4 sm:px-8 lg:px-14 pb-4">
                <ul class="max-w-[30rem] mx-auto pt-3 border-t border-mp-line flex flex-wrap justify-center gap-x-6 text-[0.9375rem] text-mp-text">
                    <li><a href="{{ route('terms') }}" class="inline-flex min-h-11 items-center hover:text-mp-black underline-offset-4 hover:underline">Conditions</a></li>
                    <li><a href="{{ route('privacy') }}" class="inline-flex min-h-11 items-center hover:text-mp-black underline-offset-4 hover:underline">Confidentialité</a></li>
                    <li><a href="{{ route('contact') }}" class="inline-flex min-h-11 items-center hover:text-mp-black underline-offset-4 hover:underline">Aide</a></li>
                </ul>
            </footer>
        </div>
    </div>
</x-layouts.app>
