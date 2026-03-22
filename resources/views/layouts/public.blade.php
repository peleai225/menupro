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
                    <a href="{{ route('home') }}#faq" class="text-neutral-600 hover:text-neutral-900 font-medium transition-colors">
                        FAQ
                    </a>
                    <a href="{{ route('home') }}#contact" class="text-neutral-600 hover:text-neutral-900 font-medium transition-colors">
                        Contact
                    </a>
                </div>

                <!-- CTA Buttons -->
                <div class="hidden md:flex items-center gap-4">
                    <a href="{{ route('login') }}" class="btn btn-ghost btn-sm">
                        Connexion
                    </a>
                    <a href="{{ route('register') }}" class="btn btn-primary btn-sm">
                        Créer mon restaurant
                    </a>
                </div>

                <!-- Mobile Menu Button -->
                <button @click="mobileOpen = !mobileOpen" class="md:hidden p-2 rounded-lg hover:bg-neutral-100">
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
                <a href="{{ route('home') }}#faq" class="block py-2 text-neutral-600 hover:text-primary-500 font-medium">
                    FAQ
                </a>
                <a href="{{ route('home') }}#contact" class="block py-2 text-neutral-600 hover:text-primary-500 font-medium">
                    Contact
                </a>
                <hr class="border-neutral-200">
                <a href="{{ route('login') }}" class="block py-2 text-neutral-600 hover:text-primary-500 font-medium">
                    Connexion
                </a>
                <a href="{{ route('register') }}" class="btn btn-primary w-full">
                    Créer mon restaurant
                </a>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="pt-16">
        {{ $slot }}
    </main>

    <!-- Footer -->
    <footer class="bg-neutral-900 text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-12">
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
                        <x-social-icons
                            :facebook="$socialFacebook"
                            :twitter="$socialTwitter"
                            :instagram="$socialInstagram"
                            :linkedin="$socialLinkedin"
                            variant="footer"
                        />
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
                <div>
                    <p class="text-neutral-500 text-sm">
                        {!! $footerText !!}
                    </p>
                    <p class="text-neutral-600 text-xs mt-1">
                        Un produit <a href="https://pelegroup.com" target="_blank" rel="noopener" class="text-neutral-400 hover:text-primary-400 transition-colors">PeleGroup</a> — Made in Côte d'Ivoire 🇨🇮
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

