@props(['title' => null, 'description' => null, 'canonical' => null])
@php
    $logo = \App\Models\SystemSetting::get('logo', '');
    $appName = \App\Models\SystemSetting::get('app_name', 'MenuPro');
    $logoUrl = ($logo && \Illuminate\Support\Facades\Storage::disk('public')->exists($logo))
        ? \Illuminate\Support\Facades\Storage::url($logo)
        : asset('images/logo-menupro.png');

    // Liens de navigation (bureau et menu mobile)
    $navLinks = [
        ['label' => 'Offres',    'href' => route('home') . '#offres', 'active' => false],
        ['label' => 'Tarifs',    'href' => route('pricing'),          'active' => request()->routeIs('pricing')],
        ['label' => 'Questions', 'href' => route('faq'),              'active' => request()->routeIs('faq')],
        ['label' => 'Contact',   'href' => route('contact'),          'active' => request()->routeIs('contact')],
    ];
@endphp
<x-layouts.app :title="$title" :description="$description" :canonical="$canonical">
<div class="vt bg-white">
    <a href="#contenu" class="sr-only focus:not-sr-only focus:fixed focus:top-2 focus:left-2 focus:z-[60] vt-btn vt-btn-dark">Aller au contenu</a>

    {{-- ══════════ EN-TÊTE ══════════ --}}
    <header x-data="{ open: false }" @keydown.escape.window="open = false"
            class="fixed top-0 inset-x-0 z-50 bg-white border-b border-mp-line">
        <div class="vt-wrap flex items-center justify-between gap-3 h-16">
            <a href="{{ route('home') }}" class="shrink-0 flex items-center" aria-label="{{ $appName }} — accueil">
                <img src="{{ $logoUrl }}" alt="{{ $appName }}" width="108" height="38" class="h-8 sm:h-9 w-auto object-contain">
            </a>

            <nav class="hidden lg:flex items-center gap-1" aria-label="Navigation principale">
                @foreach($navLinks as $link)
                    <a href="{{ $link['href'] }}" @if($link['active']) aria-current="page" @endif
                       class="px-3 py-2 rounded-lg text-base font-semibold {{ $link['active'] ? 'text-mp-ink underline underline-offset-8 decoration-2' : 'text-mp-black hover:bg-mp-paper' }}">{{ $link['label'] }}</a>
                @endforeach
                <a href="https://app.menupro.ci" target="_blank" rel="noopener" class="px-3 py-2 rounded-lg text-base font-semibold text-mp-black hover:bg-mp-paper">Commander un repas</a>
            </nav>

            <div class="flex items-center gap-2">
                @auth
                    <a href="{{ route(auth()->user()->getDashboardRoute()) }}" class="vt-btn vt-btn-dark !min-h-11 !px-4 !text-base">Mon espace</a>
                @else
                    <a href="{{ route('login') }}" class="hidden lg:inline-flex px-3 py-2 rounded-lg text-base font-semibold text-mp-black hover:bg-mp-paper">Connexion</a>
                    <a href="{{ route('register') }}" class="vt-btn vt-btn-primary !min-h-11 !px-4 !text-base whitespace-nowrap">
                        <span class="sm:hidden">Essai gratuit</span><span class="hidden sm:inline">Créer mon commerce</span>
                    </a>
                @endauth
                <button type="button" @click="open = !open" class="lg:hidden w-12 h-12 -mr-2 flex items-center justify-center rounded-xl text-mp-black hover:bg-mp-paper"
                        :aria-expanded="open.toString()" aria-controls="menu-mobile" aria-label="Ouvrir le menu">
                    <svg x-show="!open" class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" d="M4 7h16M4 12h16M4 17h16"/></svg>
                    <svg x-show="open" x-cloak class="w-7 h-7" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
                </button>
            </div>
        </div>

        {{-- Menu mobile : une colonne de grands liens --}}
        <nav id="menu-mobile" x-show="open" x-cloak @click.outside="open = false"
             class="lg:hidden border-t border-mp-line bg-white max-h-[calc(100dvh-4rem)] overflow-y-auto" aria-label="Menu">
            <ul class="vt-wrap py-2">
                @foreach($navLinks as $link)
                    <li><a href="{{ $link['href'] }}" @click="open = false" @if($link['active']) aria-current="page" @endif
                           class="flex items-center min-h-14 text-lg font-semibold border-b border-mp-line {{ $link['active'] ? 'text-mp-ink' : 'text-mp-black' }}">{{ $link['label'] }}</a></li>
                @endforeach
                <li><a href="https://app.menupro.ci" target="_blank" rel="noopener" class="flex items-center min-h-14 text-lg font-semibold border-b border-mp-line">Commander un repas (app.menupro.ci)</a></li>
                <li><a href="{{ route('representative.program') }}" class="flex items-center min-h-14 text-lg font-semibold border-b border-mp-line">Devenir représentant</a></li>
                @guest
                    <li class="pt-4 pb-3 grid gap-3">
                        <a href="{{ route('register') }}" class="vt-btn vt-btn-primary vt-btn-block">Créer mon commerce</a>
                        <a href="{{ route('login') }}" class="vt-btn vt-btn-ghost vt-btn-block">Connexion</a>
                    </li>
                @endguest
            </ul>
        </nav>
    </header>

    {{-- Bannière promotionnelle (réglée par le super admin) --}}
    @php
        $bannerEnabled = \App\Models\SystemSetting::get('banner_enabled', false);
        $bannerText = \App\Models\SystemSetting::get('banner_text', '');
        $bannerLink = \App\Models\SystemSetting::get('banner_link', '');
        if ($bannerEnabled && $bannerText) {
            $bannerDynamicData = [
                '{restaurants}' => \App\Models\Restaurant::where('status', \App\Enums\RestaurantStatus::ACTIVE)->count(),
                '{commandes}' => \App\Models\Order::count(),
                '{villes}' => \App\Models\Restaurant::where('status', \App\Enums\RestaurantStatus::ACTIVE)->whereNotNull('city')->distinct('city')->count('city'),
                '{date}' => now()->translatedFormat('d M Y'),
            ];
            $bannerText = str_replace(array_keys($bannerDynamicData), array_values($bannerDynamicData), $bannerText);
        }
    @endphp

    <div class="pt-16">
        @if($bannerEnabled && $bannerText)
            <div x-data="{ show: !sessionStorage.getItem('banner_closed_{{ md5($bannerText) }}') }" x-show="show" x-cloak
                 class="bg-mp-black text-white">
                <div class="vt-wrap flex items-center gap-3 py-2.5">
                    <p class="flex-1 text-base font-medium">
                        @if($bannerLink)
                            <a href="{{ $bannerLink }}" class="underline underline-offset-4 decoration-mp-orange decoration-2">{{ $bannerText }}</a>
                        @else
                            {{ $bannerText }}
                        @endif
                    </p>
                    <button type="button" @click="show = false; sessionStorage.setItem('banner_closed_{{ md5($bannerText) }}', '1')"
                            class="w-11 h-11 shrink-0 flex items-center justify-center rounded-full hover:bg-white/15" aria-label="Fermer l'annonce">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" d="M6 6l12 12M18 6L6 18"/></svg>
                    </button>
                </div>
            </div>
        @endif

        <main id="contenu">
            {{ $slot }}
        </main>
    </div>

    {{-- Bouton WhatsApp --}}
    @php $whatsappNumber = \App\Models\SystemSetting::get('contact_whatsapp', \App\Models\SystemSetting::get('contact_phone', '')); @endphp
    @if($whatsappNumber)
        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $whatsappNumber) }}?text={{ urlencode('Bonjour, je souhaite en savoir plus sur MenuPro.') }}"
           target="_blank" rel="noopener noreferrer"
           class="fixed bottom-4 right-4 z-40 w-14 h-14 rounded-full bg-[#1DA851] text-white flex items-center justify-center shadow-lg hover:bg-[#178F44]"
           aria-label="Écrire à MenuPro sur WhatsApp">
            <svg class="w-7 h-7" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
                <path d="M12 0C5.373 0 0 5.373 0 12c0 2.625.846 5.059 2.284 7.034L.789 23.492a.5.5 0 00.611.611l4.458-1.495A11.952 11.952 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 22c-2.34 0-4.508-.768-6.258-2.066l-.438-.338-2.652.889.889-2.652-.338-.438A9.964 9.964 0 012 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z"/>
            </svg>
        </a>
    @endif

    {{-- ══════════ PIED DE PAGE ══════════ --}}
    @php
        $socials = array_filter([
            'Facebook'  => \App\Models\SystemSetting::get('social_facebook', ''),
            'Instagram' => \App\Models\SystemSetting::get('social_instagram', ''),
            'LinkedIn'  => \App\Models\SystemSetting::get('social_linkedin', ''),
            'X'         => \App\Models\SystemSetting::get('social_twitter', ''),
        ]);
        $footerText = \App\Models\SystemSetting::get('footer_text', '© ' . date('Y') . ' MenuPro. Tous droits réservés.');
        $footerColumns = [
            'Commerçants' => [
                ['Créer mon commerce', route('register')],
                ['Tarifs', route('pricing')],
                ['Supports QR imprimés', route('qr-supports')],
                ['Connexion', route('login')],
            ],
            'Clients' => [
                ['Commander sur app.menupro.ci', 'https://app.menupro.ci'],
                ['Application Android', route('download.apk')],
            ],
            'Travailler avec nous' => [
                ['Devenir représentant', route('representative.program')],
                ['Devenir livreur', route('devenir-livreur')],
                ['Devenir agent Commando', route('commando.register.step1')],
            ],
            'Aide' => [
                ['Questions fréquentes', route('faq')],
                ['Contact', route('contact')],
                ['Demander une démo', route('contact') . '?type=demo'],
            ],
        ];
    @endphp
    <footer class="bg-mp-black text-white">
        <div class="vt-wrap py-12 lg:py-16">
            <div class="grid gap-10 lg:grid-cols-[1.2fr_3fr]">
                <div>
                    <a href="{{ route('home') }}" class="inline-flex bg-white rounded-xl px-3 py-2" aria-label="{{ $appName }} — accueil">
                        <img src="{{ $logoUrl }}" alt="{{ $appName }}" width="108" height="38" class="h-8 w-auto object-contain" loading="lazy">
                    </a>
                    <p class="mt-5 text-neutral-300 max-w-xs">Commandes en ligne, paiement Mobile Money et livraison pour les restaurants, maquis, stands et boutiques de Côte d'Ivoire.</p>
                    @if($socials)
                        <ul class="mt-5 flex flex-wrap gap-x-5 gap-y-2">
                            @foreach($socials as $network => $url)
                                <li><a href="{{ $url }}" target="_blank" rel="noopener noreferrer" class="underline underline-offset-4 text-neutral-200 hover:text-mp-orange">{{ $network }}</a></li>
                            @endforeach
                        </ul>
                    @endif
                </div>
                <div class="grid grid-cols-1 min-[420px]:grid-cols-2 md:grid-cols-4 gap-8">
                    @foreach($footerColumns as $heading => $links)
                        <div>
                            <h2 class="font-bold text-lg mb-3">{{ $heading }}</h2>
                            <ul class="space-y-1">
                                @foreach($links as [$label, $href])
                                    <li><a href="{{ $href }}" @if(str_starts_with($href, 'https://app.')) target="_blank" rel="noopener" @endif
                                           class="inline-flex min-h-11 items-center text-neutral-300 hover:text-mp-orange">{{ $label }}</a></li>
                                @endforeach
                            </ul>
                        </div>
                    @endforeach
                </div>
            </div>

            <div class="mt-12 pt-6 border-t border-white/15 flex flex-col md:flex-row md:items-center md:justify-between gap-4 text-[0.9375rem] text-neutral-300">
                <div>
                    <p>{{ $footerText }}</p>
                    <p class="mt-1">Données protégées (loi ivoirienne n° 2013-450). Un produit <a href="https://peleai.online" target="_blank" rel="noopener" class="underline underline-offset-4 hover:text-mp-orange">PeleAI</a>, fait en Côte d'Ivoire.</p>
                </div>
                <ul class="flex flex-wrap gap-x-5 gap-y-1">
                    <li><a href="{{ route('terms') }}" class="inline-flex min-h-11 items-center hover:text-mp-orange">Conditions</a></li>
                    <li><a href="{{ route('privacy') }}" class="inline-flex min-h-11 items-center hover:text-mp-orange">Confidentialité</a></li>
                    <li><a href="{{ route('mentions-legales') }}" class="inline-flex min-h-11 items-center hover:text-mp-orange">Mentions légales</a></li>
                </ul>
            </div>
        </div>
    </footer>
</div>
</x-layouts.app>
