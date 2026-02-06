<x-layouts.app :title="$title ?? null">
    <!-- Navigation -->
    <nav class="navbar" x-data="{ mobileOpen: false, scrolled: false }" 
         @scroll.window="scrolled = window.scrollY > 20"
         :class="{ 'shadow-soft': scrolled }">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 h-full">
            <div class="flex items-center justify-between h-full">
                <!-- Logo -->
                @php
                    $logo = \App\Models\SystemSetting::get('logo', '');
                    $appName = \App\Models\SystemSetting::get('app_name', 'MenuPro');
                @endphp
                <a href="{{ route('home') }}" class="flex items-center gap-3">
                    @if($logo)
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($logo) }}" alt="{{ $appName }}" class="h-10 w-auto object-contain">
                    @else
                        <img src="{{ asset('images/logo-menupro.png') }}" alt="{{ $appName }}" class="h-9 w-auto object-contain">
                    @endif
                </a>

                <!-- Desktop Navigation -->
                <div class="hidden md:flex items-center gap-8">
                    <a href="{{ route('home') }}#features" class="text-neutral-600 hover:text-neutral-900 font-medium transition-colors">
                        Fonctionnalités
                    </a>
                    <a href="{{ route('pricing') }}" class="text-neutral-600 hover:text-neutral-900 font-medium transition-colors">
                        Tarifs
                    </a>
                    <a href="{{ route('faq') }}" class="text-neutral-600 hover:text-neutral-900 font-medium transition-colors">
                        FAQ
                    </a>
                    <a href="{{ route('contact') }}" class="text-neutral-600 hover:text-neutral-900 font-medium transition-colors">
                        Contact
                    </a>
                </div>

                <!-- CTA Buttons -->
                <div class="hidden md:flex items-center gap-4">
                    @auth
                        <a href="{{ route(auth()->user()->getDashboardRoute()) }}" class="btn btn-primary btn-sm flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                            </svg>
                            Dashboard
                        </a>
                        <form method="POST" action="{{ route('logout') }}" class="inline">
                            @csrf
                            <button type="submit" class="btn btn-ghost btn-sm flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                                </svg>
                                Déconnexion
                            </button>
                        </form>
                    @else
                        <a href="{{ route('login') }}" class="btn btn-ghost btn-sm">
                            Connexion
                        </a>
                        <a href="{{ route('register') }}" class="btn btn-primary btn-sm">
                            Créer mon restaurant
                        </a>
                    @endauth
                </div>

                <!-- Mobile Menu Button -->
                <button @click="mobileOpen = !mobileOpen" class="md:hidden p-2.5 rounded-lg hover:bg-neutral-100 min-h-[44px] min-w-[44px] flex items-center justify-center" aria-label="Menu">
                    <svg x-show="!mobileOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                    <svg x-show="mobileOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Mobile Menu -->
        <div x-show="mobileOpen" 
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 -translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 -translate-y-4"
             class="md:hidden absolute top-16 left-0 right-0 bg-white border-b border-neutral-200 shadow-lg"
             x-cloak>
            <div class="px-4 py-6 space-y-4">
                <a href="{{ route('home') }}#features" class="block py-2 text-neutral-600 hover:text-primary-500 font-medium">
                    Fonctionnalités
                </a>
                <a href="{{ route('pricing') }}" class="block py-2 text-neutral-600 hover:text-primary-500 font-medium">
                    Tarifs
                </a>
                <a href="{{ route('faq') }}" class="block py-2 text-neutral-600 hover:text-primary-500 font-medium">
                    FAQ
                </a>
                <a href="{{ route('contact') }}" class="block py-2 text-neutral-600 hover:text-primary-500 font-medium">
                    Contact
                </a>
                <hr class="border-neutral-200">
                @auth
                    <a href="{{ route(auth()->user()->getDashboardRoute()) }}" class="btn btn-primary w-full flex items-center justify-center gap-2">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                        </svg>
                        Dashboard
                    </a>
                    <form method="POST" action="{{ route('logout') }}" class="w-full">
                        @csrf
                        <button type="submit" class="btn btn-ghost w-full flex items-center justify-center gap-2 text-neutral-600 hover:text-primary-500">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/>
                            </svg>
                            Déconnexion
                        </button>
                    </form>
                @else
                    <a href="{{ route('login') }}" class="block py-2 text-neutral-600 hover:text-primary-500 font-medium">
                        Connexion
                    </a>
                    <a href="{{ route('register') }}" class="btn btn-primary w-full">
                        Créer mon restaurant
                    </a>
                @endauth
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="pt-16">
        {{ $slot }}
    </main>

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
                @endphp
                <div class="lg:col-span-1">
                    <div class="flex items-center gap-3 mb-6">
                        @if($logo)
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($logo) }}" alt="{{ $appName }}" class="h-10 w-auto object-contain">
                        @else
                            <img src="{{ asset('images/logo-menupro.png') }}" alt="{{ $appName }}" class="h-9 w-auto object-contain">
                        @endif
                    </div>
                    <p class="text-neutral-400 mb-6">
                        La solution SaaS pour digitaliser votre restaurant et booster vos commandes en ligne.
                    </p>
                    @if($socialFacebook || $socialTwitter || $socialInstagram || $socialLinkedin)
                        <div class="flex items-center gap-4">
                            @if($socialFacebook)
                                <a href="{{ $socialFacebook }}" target="_blank" rel="noopener noreferrer" class="w-10 h-10 bg-neutral-800 rounded-full flex items-center justify-center hover:bg-primary-500 transition-colors">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M22.675 0h-21.35c-.732 0-1.325.593-1.325 1.325v21.351c0 .731.593 1.324 1.325 1.324h11.495v-9.294h-3.128v-3.622h3.128v-2.671c0-3.1 1.893-4.788 4.659-4.788 1.325 0 2.463.099 2.795.143v3.24l-1.918.001c-1.504 0-1.795.715-1.795 1.763v2.313h3.587l-.467 3.622h-3.12v9.293h6.116c.73 0 1.323-.593 1.323-1.325v-21.35c0-.732-.593-1.325-1.325-1.325z"/>
                                    </svg>
                                </a>
                            @endif
                            @if($socialTwitter)
                                <a href="{{ $socialTwitter }}" target="_blank" rel="noopener noreferrer" class="w-10 h-10 bg-neutral-800 rounded-full flex items-center justify-center hover:bg-primary-500 transition-colors">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M24 4.557c-.883.392-1.832.656-2.828.775 1.017-.609 1.798-1.574 2.165-2.724-.951.564-2.005.974-3.127 1.195-.897-.957-2.178-1.555-3.594-1.555-3.179 0-5.515 2.966-4.797 6.045-4.091-.205-7.719-2.165-10.148-5.144-1.29 2.213-.669 5.108 1.523 6.574-.806-.026-1.566-.247-2.229-.616-.054 2.281 1.581 4.415 3.949 4.89-.693.188-1.452.232-2.224.084.626 1.956 2.444 3.379 4.6 3.419-2.07 1.623-4.678 2.348-7.29 2.04 2.179 1.397 4.768 2.212 7.548 2.212 9.142 0 14.307-7.721 13.995-14.646.962-.695 1.797-1.562 2.457-2.549z"/>
                                    </svg>
                                </a>
                            @endif
                            @if($socialInstagram)
                                <a href="{{ $socialInstagram }}" target="_blank" rel="noopener noreferrer" class="w-10 h-10 bg-neutral-800 rounded-full flex items-center justify-center hover:bg-primary-500 transition-colors">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2.163c3.204 0 3.584.012 4.85.07 3.252.148 4.771 1.691 4.919 4.919.058 1.265.069 1.645.069 4.849 0 3.205-.012 3.584-.069 4.849-.149 3.225-1.664 4.771-4.919 4.919-1.266.058-1.644.07-4.85.07-3.204 0-3.584-.012-4.849-.07-3.26-.149-4.771-1.699-4.919-4.92-.058-1.265-.07-1.644-.07-4.849 0-3.204.013-3.583.07-4.849.149-3.227 1.664-4.771 4.919-4.919 1.266-.057 1.645-.069 4.849-.069zm0-2.163c-3.259 0-3.667.014-4.947.072-4.358.2-6.78 2.618-6.98 6.98-.059 1.281-.073 1.689-.073 4.948 0 3.259.014 3.668.072 4.948.2 4.358 2.618 6.78 6.98 6.98 1.281.058 1.689.072 4.948.072 3.259 0 3.668-.014 4.948-.072 4.354-.2 6.782-2.618 6.979-6.98.059-1.28.073-1.689.073-4.948 0-3.259-.014-3.667-.072-4.947-.196-4.354-2.617-6.78-6.979-6.98-1.281-.059-1.69-.073-4.949-.073z"/>
                                    </svg>
                                </a>
                            @endif
                            @if($socialLinkedin)
                                <a href="{{ $socialLinkedin }}" target="_blank" rel="noopener noreferrer" class="w-10 h-10 bg-neutral-800 rounded-full flex items-center justify-center hover:bg-primary-500 transition-colors">
                                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
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
                        <li><a href="{{ route('home') }}#features" class="text-neutral-400 hover:text-primary-400 transition-colors">Fonctionnalités</a></li>
                        <li><a href="{{ route('pricing') }}" class="text-neutral-400 hover:text-primary-400 transition-colors">Tarifs</a></li>
                        <li><a href="#" class="text-neutral-400 hover:text-primary-400 transition-colors">Démo</a></li>
                        <li><a href="#" class="text-neutral-400 hover:text-primary-400 transition-colors">Témoignages</a></li>
                    </ul>
                </div>

                <!-- Support -->
                <div>
                    <h4 class="font-bold text-lg mb-6">Support</h4>
                    <ul class="space-y-4">
                        <li><a href="{{ route('home') }}#faq" class="text-neutral-400 hover:text-primary-400 transition-colors">FAQ</a></li>
                        <li><a href="{{ route('home') }}#contact" class="text-neutral-400 hover:text-primary-400 transition-colors">Contact</a></li>
                        <li><a href="#" class="text-neutral-400 hover:text-primary-400 transition-colors">Documentation</a></li>
                        <li><a href="#" class="text-neutral-400 hover:text-primary-400 transition-colors">Statut</a></li>
                    </ul>
                </div>

                <!-- Légal -->
                <div>
                    <h4 class="font-bold text-lg mb-6">Légal</h4>
                    <ul class="space-y-4">
                        <li><a href="{{ route('terms') }}" class="text-neutral-400 hover:text-primary-400 transition-colors">Conditions d'utilisation</a></li>
                        <li><a href="{{ route('privacy') }}" class="text-neutral-400 hover:text-primary-400 transition-colors">Politique de confidentialité</a></li>
                        <li><a href="#" class="text-neutral-400 hover:text-primary-400 transition-colors">Mentions légales</a></li>
                    </ul>
                </div>
            </div>

            <hr class="border-neutral-800 my-12">

            @php
                $footerText = \App\Models\SystemSetting::get('footer_text', '© ' . date('Y') . ' MenuPro. Tous droits réservés.');
            @endphp
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                <p class="text-neutral-500 text-sm">
                    {!! $footerText !!}
                </p>
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

