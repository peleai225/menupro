@props(['title' => null, 'description' => null, 'canonical' => null])
<x-layouts.app :title="$title" :description="$description" :canonical="$canonical">
    <!-- Navigation — Inspirée design Food Delivery ref -->
    <nav class="fixed top-0 left-0 right-0 z-50 transition-all duration-300"
         x-data="{ mobileOpen: false, scrolled: false }"
         @scroll.window="scrolled = window.scrollY > 10"
         :style="scrolled ? 'background:rgba(255,255,255,0.97);box-shadow:0 2px 20px rgba(0,0,0,0.08);backdrop-filter:blur(12px)' : 'background:rgba(255,255,255,0.95);backdrop-filter:blur(8px)'">

        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16 sm:h-18">

                <!-- Logo -->
                @php
                    $logo = \App\Models\SystemSetting::get('logo', '');
                    $appName = \App\Models\SystemSetting::get('app_name', 'MenuPro');
                    $logoUrl = ($logo && \Illuminate\Support\Facades\Storage::disk('public')->exists($logo))
                        ? \Illuminate\Support\Facades\Storage::url($logo)
                        : null;
                @endphp
                <a href="{{ route('home') }}" class="flex items-center gap-2.5 shrink-0">
                    @if($logoUrl)
                        <img src="{{ $logoUrl }}" alt="{{ $appName }}" width="120" height="40" class="h-9 w-auto object-contain">
                    @else
                        <img src="{{ asset('images/logo-menupro.png') }}" alt="{{ $appName }}" width="108" height="36" class="h-9 w-auto object-contain">
                    @endif
                </a>

                <!-- Desktop Nav Links -->
                <div class="hidden md:flex items-center gap-1">
                    @php
                        $navLinks = [
                            ['Fonctionnalités', route('home').'#how-it-works', false],
                            ['Tarifs', route('pricing'), request()->routeIs('pricing')],
                            ['L\'App', 'https://mpa-five.vercel.app/', false, true],
                            ['Contact', route('contact'), request()->routeIs('contact')],
                        ];
                    @endphp
                    @foreach($navLinks as $link)
                    <a href="{{ $link[1] }}"
                       @if(!empty($link[3])) target="_blank" @endif
                       class="px-4 py-2 rounded-lg text-sm font-semibold transition-all duration-200 flex items-center gap-1.5 {{ $link[2] ? 'text-primary-500' : 'text-neutral-600 hover:text-primary-500 hover:bg-primary-50' }}">
                        {{ $link[0] }}
                        @if(!empty($link[3]))
                        <svg class="w-3 h-3 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        @endif
                    </a>
                    @endforeach
                </div>

                <!-- Desktop CTA -->
                <div class="hidden md:flex items-center gap-3">
                    @auth
                        <a href="{{ route(auth()->user()->getDashboardRoute()) }}"
                           class="px-5 py-2.5 text-sm font-black text-white rounded-full transition-all hover:-translate-y-0.5 hover:shadow-lg"
                           style="background:#D45E0C">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                           class="px-4 py-2 text-sm font-semibold text-neutral-600 hover:text-primary-500 rounded-lg transition-colors">
                            Connexion
                        </a>
                        <a href="{{ route('register') }}"
                           class="px-5 py-2.5 text-sm font-black text-white rounded-full transition-all hover:-translate-y-0.5 hover:shadow-lg whitespace-nowrap"
                           style="background:#D45E0C">
                            Démarrer gratuitement
                        </a>
                    @endauth
                </div>

                <!-- Mobile Menu Button -->
                <button @click="mobileOpen = !mobileOpen"
                        class="md:hidden w-10 h-10 flex items-center justify-center rounded-xl transition-colors hover:bg-neutral-100"
                        aria-label="Menu">
                    <svg x-show="!mobileOpen" class="w-5 h-5 text-neutral-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <svg x-show="mobileOpen" class="w-5 h-5 text-neutral-700" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div x-show="mobileOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-2"
             class="md:hidden bg-white border-t border-neutral-100 shadow-xl"
             x-cloak>
            <div class="px-4 py-5 space-y-1">
                <a href="{{ route('home') }}" class="flex items-center px-4 py-3 rounded-xl text-sm font-semibold {{ request()->routeIs('home') ? 'text-primary-500 bg-primary-50' : 'text-neutral-700 hover:bg-neutral-50' }}">
                    Accueil
                </a>
                <a href="{{ route('home') }}#how-it-works" @click="mobileOpen=false" class="flex items-center px-4 py-3 rounded-xl text-sm font-semibold text-neutral-700 hover:bg-neutral-50">
                    Fonctionnalités
                </a>
                <a href="{{ route('pricing') }}" class="flex items-center px-4 py-3 rounded-xl text-sm font-semibold {{ request()->routeIs('pricing') ? 'text-primary-500 bg-primary-50' : 'text-neutral-700 hover:bg-neutral-50' }}">
                    Tarifs
                </a>
                <a href="{{ route('faq') }}" class="flex items-center px-4 py-3 rounded-xl text-sm font-semibold {{ request()->routeIs('faq') ? 'text-primary-500 bg-primary-50' : 'text-neutral-700 hover:bg-neutral-50' }}">
                    FAQ
                </a>
                <a href="{{ route('contact') }}" class="flex items-center px-4 py-3 rounded-xl text-sm font-semibold {{ request()->routeIs('contact') ? 'text-primary-500 bg-primary-50' : 'text-neutral-700 hover:bg-neutral-50' }}">
                    Contact
                </a>

                <div class="pt-3 mt-2 border-t border-neutral-100 space-y-2">
                    @auth
                        <a href="{{ route(auth()->user()->getDashboardRoute()) }}"
                           class="flex items-center justify-center w-full py-3.5 text-sm font-black text-white rounded-2xl"
                           style="background:#D45E0C">
                            Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}"
                           class="flex items-center justify-center w-full py-3.5 text-sm font-semibold text-neutral-700 rounded-2xl border-2 border-neutral-200 hover:border-primary-300">
                            Connexion
                        </a>
                        <a href="{{ route('register') }}"
                           class="flex items-center justify-center w-full py-3.5 text-sm font-black text-white rounded-2xl"
                           style="background:#D45E0C">
                            Démarrer gratuitement
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    <!-- Promotional Banner (dynamic) -->
    @php
        $bannerEnabled = \App\Models\SystemSetting::get('banner_enabled', false);
        $bannerText = \App\Models\SystemSetting::get('banner_text', '');
        $bannerLink = \App\Models\SystemSetting::get('banner_link', '');
        $bannerColor = \App\Models\SystemSetting::get('banner_color', 'primary');

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
    @if($bannerEnabled && $bannerText)
        <div class="fixed top-16 left-0 right-0 z-40 {{ $bannerColor === 'primary' ? 'bg-gradient-to-r from-primary-600 via-primary-500 to-primary-600' : ($bannerColor === 'success' ? 'bg-gradient-to-r from-secondary-600 via-secondary-500 to-secondary-600' : ($bannerColor === 'warning' ? 'bg-gradient-to-r from-yellow-600 via-yellow-500 to-yellow-600' : 'bg-gradient-to-r from-neutral-900 via-neutral-800 to-neutral-900')) }} text-white text-sm font-medium shadow-md overflow-hidden"
             x-data="{ showBanner: !sessionStorage.getItem('banner_closed_{{ md5($bannerText) }}') }" x-show="showBanner" x-cloak
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="-translate-y-full opacity-0"
             x-transition:enter-end="translate-y-0 opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="translate-y-0 opacity-100"
             x-transition:leave-end="-translate-y-full opacity-0">
            <div class="relative py-2.5 px-4 sm:px-8 md:px-12">
                <div class="flex items-center justify-center">
                    <div class="animate-marquee sm:animate-none whitespace-nowrap sm:whitespace-normal flex items-center gap-2">
                        <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/></svg>
                        @if($bannerLink)
                            <a href="{{ $bannerLink }}" class="hover:underline underline-offset-2 flex items-center gap-2">
                                <span>{{ $bannerText }}</span>
                                <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
                            </a>
                        @else
                            <span>{{ $bannerText }}</span>
                        @endif
                    </div>
                </div>
                <button @click="showBanner = false; sessionStorage.setItem('banner_closed_{{ md5($bannerText) }}', '1')" class="absolute right-2 top-1/2 -translate-y-1/2 p-2.5 rounded-full hover:bg-white/20 transition" aria-label="Fermer la bannière">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        </div>
    @endif

    <!-- Main Content -->
    <main class="{{ ($bannerEnabled && $bannerText) ? 'pt-[104px]' : 'pt-16' }} pb-20 md:pb-0">
        {{ $slot }}
    </main>

    <!-- Floating WhatsApp Button -->
    @php
        $whatsappNumber = \App\Models\SystemSetting::get('contact_whatsapp', \App\Models\SystemSetting::get('contact_phone', ''));
    @endphp
    @if($whatsappNumber)
        <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $whatsappNumber) }}?text={{ urlencode('Bonjour, je souhaite en savoir plus sur MenuPro.') }}"
           target="_blank"
           rel="noopener noreferrer"
           class="fixed bottom-24 md:bottom-6 right-6 z-40 group"
           aria-label="Nous contacter sur WhatsApp">
            <div class="relative">
                {{-- Pulse ring --}}
                <span class="absolute inset-0 rounded-full bg-green-500 animate-ping opacity-25"></span>
                {{-- Button --}}
                <div class="relative w-14 h-14 bg-green-500 rounded-full flex items-center justify-center shadow-lg shadow-green-500/30 group-hover:scale-110 group-hover:shadow-xl group-hover:shadow-green-500/40 transition-all duration-300">
                    <svg class="w-7 h-7 text-white" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347z"/>
                        <path d="M12 0C5.373 0 0 5.373 0 12c0 2.625.846 5.059 2.284 7.034L.789 23.492a.5.5 0 00.611.611l4.458-1.495A11.952 11.952 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 22c-2.34 0-4.508-.768-6.258-2.066l-.438-.338-2.652.889.889-2.652-.338-.438A9.964 9.964 0 012 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z"/>
                    </svg>
                </div>
                {{-- Tooltip --}}
                <div class="absolute bottom-full right-0 mb-3 opacity-0 group-hover:opacity-100 transition-opacity duration-300 pointer-events-none">
                    <div class="bg-neutral-900 text-white text-xs font-medium px-3 py-2 rounded-lg shadow-lg whitespace-nowrap">
                        Besoin d'aide ? Contactez-nous !
                        <div class="absolute top-full right-5 w-2 h-2 bg-neutral-900 rotate-45 -translate-y-1"></div>
                    </div>
                </div>
            </div>
        </a>
    @endif

    <!-- Bottom Navigation Mobile (PWA) - Toujours visible sur mobile -->
    @guest
    <nav class="md:hidden fixed bottom-0 left-0 right-0 bg-white/95 backdrop-blur-lg border-t border-neutral-200 shadow-[0_-4px_20px_rgba(0,0,0,0.08)] z-50 safe-area-inset-bottom">
        <div class="flex items-center justify-around px-1 py-2.5 max-w-md mx-auto">
            {{-- Accueil --}}
            <a href="{{ route('home') }}"
               class="relative flex flex-col items-center justify-center min-w-[64px] py-2 px-2 rounded-2xl {{ request()->routeIs('home') ? 'text-primary-600' : 'text-neutral-600' }} hover:text-primary-500 active:scale-95 transition-all touch-manipulation">
                @if(request()->routeIs('home'))
                    <span class="absolute inset-x-2 top-0 h-1 bg-primary-500 rounded-full"></span>
                @endif
                <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                <span class="text-[10px] font-semibold leading-tight">Accueil</span>
            </a>

            {{-- Fonctionnalités --}}
            <a href="{{ route('home') }}#how-it-works"
               class="flex flex-col items-center justify-center min-w-[64px] py-2 px-2 rounded-2xl text-neutral-600 hover:text-primary-500 active:scale-95 transition-all touch-manipulation">
                <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                </svg>
                <span class="text-[10px] font-semibold leading-tight">Features</span>
            </a>

            {{-- Tarifs --}}
            <a href="{{ route('pricing') }}"
               class="relative flex flex-col items-center justify-center min-w-[64px] py-2 px-2 rounded-2xl {{ request()->routeIs('pricing') ? 'text-primary-600' : 'text-neutral-600' }} hover:text-primary-500 active:scale-95 transition-all touch-manipulation">
                @if(request()->routeIs('pricing'))
                    <span class="absolute inset-x-2 top-0 h-1 bg-primary-500 rounded-full"></span>
                @endif
                <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/>
                </svg>
                <span class="text-[10px] font-semibold leading-tight">Tarifs</span>
            </a>

            {{-- Contact --}}
            <a href="{{ route('contact') }}"
               class="relative flex flex-col items-center justify-center min-w-[64px] py-2 px-2 rounded-2xl {{ request()->routeIs('contact') ? 'text-primary-600' : 'text-neutral-600' }} hover:text-primary-500 active:scale-95 transition-all touch-manipulation">
                @if(request()->routeIs('contact'))
                    <span class="absolute inset-x-2 top-0 h-1 bg-primary-500 rounded-full"></span>
                @endif
                <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                </svg>
                <span class="text-[10px] font-semibold leading-tight">Contact</span>
            </a>

            {{-- Démarrer (CTA principal) --}}
            <a href="{{ route('register') }}"
               class="flex flex-col items-center justify-center min-w-[64px] py-2 px-3 rounded-2xl bg-gradient-to-br from-primary-500 to-primary-600 text-white shadow-lg shadow-primary-500/25 hover:shadow-xl hover:shadow-primary-500/30 hover:from-primary-600 hover:to-primary-700 active:scale-95 transition-all touch-manipulation">
                <svg class="w-6 h-6 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24" stroke-width="2.5">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                <span class="text-[10px] font-bold leading-tight">Démarrer</span>
            </a>
        </div>
    </nav>
    @endguest

    <!-- Footer -->
    <footer class="bg-neutral-900 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 sm:py-12 lg:py-16">
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-8 lg:gap-12">
                <!-- Brand -->
                @php
                    $logo = \App\Models\SystemSetting::get('logo', '');
                    $appName = \App\Models\SystemSetting::get('app_name', 'MenuPro');
                    $socialFacebook = \App\Models\SystemSetting::get('social_facebook', '');
                    $socialTwitter = \App\Models\SystemSetting::get('social_twitter', '');
                    $socialInstagram = \App\Models\SystemSetting::get('social_instagram', '');
                    $socialLinkedin = \App\Models\SystemSetting::get('social_linkedin', '');
                    $logoUrlFooter = ($logo && \Illuminate\Support\Facades\Storage::disk('public')->exists($logo))
                        ? \Illuminate\Support\Facades\Storage::url($logo)
                        : null;
                @endphp
                <div class="lg:col-span-1">
                    <div class="flex items-center gap-3 mb-6">
                        @if($logoUrlFooter)
                            <img src="{{ $logoUrlFooter }}" alt="{{ $appName }}" width="120" height="40" class="h-10 w-auto object-contain">
                        @else
                            <img src="{{ asset('images/logo-menupro.png') }}" alt="{{ $appName }}" width="108" height="36" class="h-9 w-auto object-contain">
                        @endif
                    </div>
                    <p class="text-neutral-400 mb-6">
                        La solution SaaS pour digitaliser votre restaurant et booster vos commandes en ligne.
                    </p>
                    @if($socialFacebook || $socialTwitter || $socialInstagram || $socialLinkedin)
                        <div class="flex items-center gap-4">
                            @if($socialFacebook)
                                <a href="{{ $socialFacebook }}" target="_blank" rel="noopener noreferrer" aria-label="Facebook" class="w-10 h-10 bg-neutral-800 rounded-full flex items-center justify-center hover:bg-primary-500 transition-colors">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M22.675 0h-21.35c-.732 0-1.325.593-1.325 1.325v21.351c0 .731.593 1.324 1.325 1.324h11.495v-9.294h-3.128v-3.622h3.128v-2.671c0-3.1 1.893-4.788 4.659-4.788 1.325 0 2.463.099 2.795.143v3.24l-1.918.001c-1.504 0-1.795.715-1.795 1.763v2.313h3.587l-.467 3.622h-3.12v9.293h6.116c.73 0 1.323-.593 1.323-1.325v-21.35c0-.732-.593-1.325-1.325-1.325z"/>
                                    </svg>
                                </a>
                            @endif
                            @if($socialTwitter)
                                <a href="{{ $socialTwitter }}" target="_blank" rel="noopener noreferrer" aria-label="Twitter" class="w-10 h-10 bg-neutral-800 rounded-full flex items-center justify-center hover:bg-primary-500 transition-colors">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/>
                                    </svg>
                                </a>
                            @endif
                            @if($socialInstagram)
                                <a href="{{ $socialInstagram }}" target="_blank" rel="noopener noreferrer" aria-label="Instagram" class="w-10 h-10 bg-neutral-800 rounded-full flex items-center justify-center hover:bg-primary-500 transition-colors">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073z"/>
                                    </svg>
                                </a>
                            @endif
                            @if($socialLinkedin)
                                <a href="{{ $socialLinkedin }}" target="_blank" rel="noopener noreferrer" aria-label="LinkedIn" class="w-10 h-10 bg-neutral-800 rounded-full flex items-center justify-center hover:bg-primary-500 transition-colors">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24" aria-hidden="true">
                                        <path d="M20.447 20.452h-3.554v-5.569c0-1.328-.027-3.037-1.852-3.037-1.853 0-2.136 1.445-2.136 2.939v5.667H9.351V9h3.414v1.561h.046c.477-.9 1.637-1.85 3.37-1.85 3.601 0 4.267 2.37 4.267 5.455v6.286zM5.337 7.433c-1.144 0-2.063-.926-2.063-2.065 0-1.138.92-2.063 2.063-2.063 1.14 0 2.064.925 2.064 2.063 0 1.139-.925 2.065-2.064 2.065zm1.782 13.019H3.555V9h3.564v11.452zM22.225 0H1.771C.792 0 0 .774 0 1.729v20.542C0 23.227.792 24 1.771 24h20.451C23.2 24 24 23.227 24 22.271V1.729C24 .774 23.2 0 22.222 0h.003z"/>
                                    </svg>
                                </a>
                            @endif
                        </div>
                    @endif
                </div>

                <!-- Produit -->
                <div>
                    <h4 class="font-bold text-lg mb-6">Produit</h4>
                    <ul class="space-y-4">
                        <li><a href="{{ route('home') }}#how-it-works" class="text-neutral-400 hover:text-primary-400 transition-colors">Fonctionnalités</a></li>
                        <li><a href="{{ route('pricing') }}" class="text-neutral-400 hover:text-primary-400 transition-colors">Tarifs</a></li>
                        <li><a href="{{ route('commando.register.step1') }}" class="text-neutral-400 hover:text-orange-400 transition-colors">Devenir agent Commando</a></li>
                        <li><a href="{{ route('contact') }}?type=demo" class="text-neutral-400 hover:text-primary-400 transition-colors">Démo</a></li>
                        <li><a href="{{ route('home') }}#testimonials" class="text-neutral-400 hover:text-primary-400 transition-colors">Témoignages</a></li>
                    </ul>
                </div>

                <!-- Support -->
                <div>
                    <h4 class="font-bold text-lg mb-6">Support</h4>
                    <ul class="space-y-4">
                        <li><a href="{{ route('home') }}#faq" class="text-neutral-400 hover:text-primary-400 transition-colors">FAQ</a></li>
                        <li><a href="{{ route('home') }}#contact" class="text-neutral-400 hover:text-primary-400 transition-colors">Contact</a></li>
                        <li><a href="{{ route('faq') }}" class="text-neutral-400 hover:text-primary-400 transition-colors">Documentation</a></li>
                        <li><a href="{{ route('contact') }}?type=support" class="text-neutral-400 hover:text-primary-400 transition-colors">Statut</a></li>
                    </ul>
                </div>

                <!-- Légal -->
                <div>
                    <h4 class="font-bold text-lg mb-6">Légal</h4>
                    <ul class="space-y-4">
                        <li><a href="{{ route('terms') }}" class="text-neutral-400 hover:text-primary-400 transition-colors">Conditions d'utilisation</a></li>
                        <li><a href="{{ route('privacy') }}" class="text-neutral-400 hover:text-primary-400 transition-colors">Politique de confidentialité</a></li>
                        <li><a href="{{ route('privacy') }}#cookies" class="text-neutral-400 hover:text-primary-400 transition-colors">Cookies</a></li>
                        <li><a href="{{ route('mentions-legales') }}" class="text-neutral-400 hover:text-primary-400 transition-colors">Mentions légales</a></li>
                    </ul>
                </div>
            </div>

            <hr class="border-neutral-800 my-12">

            @php
                $footerText = \App\Models\SystemSetting::get('footer_text', '© ' . date('Y') . ' MenuPro. Tous droits réservés.');
            @endphp
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                <div>
                    <p class="text-neutral-500 text-sm">
                        {{ $footerText }}
                    </p>
                    <p class="text-neutral-600 text-xs mt-1">
                        Un produit <a href="https://peleai.online" target="_blank" rel="noopener" class="text-neutral-400 hover:text-primary-400 transition-colors">PeleAI</a> — Made in Côte d'Ivoire 🇨🇮
                    </p>
                </div>
                <div class="flex items-center gap-6">
                    <span class="text-neutral-500 text-sm flex items-center gap-2">
                        <span class="status-dot status-dot-success"></span>
                        Tous les systèmes opérationnels
                    </span>
                </div>
            </div>
        </div>
    </footer>
</x-layouts.app>

