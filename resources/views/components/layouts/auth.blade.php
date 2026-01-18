<x-layouts.app :title="$title ?? 'Connexion'">
    @php
        $appName = config('app.name', 'MenuPro');
        $logoUrl = null;
        $restaurantsCount = '0';
        $ordersCount = '0';
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
        try {
            $totalRestaurants = \App\Models\Restaurant::where('status', \App\Enums\RestaurantStatus::ACTIVE)->count();
            $totalOrders = \App\Models\Order::withoutGlobalScope('restaurant')->count();
            $restaurantsCount = $totalRestaurants >= 500
                ? number_format($totalRestaurants, 0, ',', ' ') . '+'
                : number_format($totalRestaurants, 0, ',', ' ');
            $ordersCount = $totalOrders >= 50000
                ? number_format($totalOrders / 1000, 0, ',', ' ') . 'K+'
                : ($totalOrders >= 1000
                    ? number_format($totalOrders / 1000, 1, ',', ' ') . 'K+'
                    : number_format($totalOrders, 0, ',', ' '));
        } catch (\Throwable $e) {
            $restaurantsCount = '0';
            $ordersCount = '0';
        }
        $customAuthImage = null;
        foreach (['custom.jpg', 'custom.png', 'custom.webp'] as $name) {
            if (file_exists(public_path('images/auth/' . $name))) {
                $customAuthImage = asset('images/auth/' . $name);
                break;
            }
        }
    @endphp
    <div class="min-h-screen flex flex-col lg:flex-row">
        <!-- Left Side - Branding (Hidden on mobile) -->
        <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-neutral-950 via-black to-neutral-950 relative overflow-hidden flex-shrink-0">
            <!-- Background Pattern -->
            <div class="absolute inset-0 opacity-10">
                <svg class="w-full h-full" viewBox="0 0 100 100" preserveAspectRatio="none">
                    <defs>
                        <pattern id="grid" width="10" height="10" patternUnits="userSpaceOnUse">
                            <path d="M 10 0 L 0 0 0 10" fill="none" stroke="white" stroke-width="0.5"/>
                        </pattern>
                    </defs>
                    <rect width="100%" height="100%" fill="url(#grid)" />
                </svg>
            </div>
            
            <!-- Image de fond : votre image ou visuels par défaut -->
            @if($customAuthImage)
                {{-- Votre image en plein fond --}}
                <div class="absolute inset-0 z-0">
                    <img src="{{ $customAuthImage }}" alt="" class="w-full h-full object-cover" aria-hidden="true">
                </div>
                <div class="absolute inset-0 z-0 bg-black/55" aria-hidden="true"></div>
            @else
                <div class="absolute inset-0 z-0">
                    <img src="https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=1200&auto=format&fit=crop" alt="" class="w-full h-full object-cover opacity-[0.18]" aria-hidden="true">
                </div>
                <div class="absolute right-0 bottom-0 w-72 h-72 lg:w-96 lg:h-96 z-0">
                    <img src="https://images.unsplash.com/photo-1555396273-367ea4eb4db5?w=600&auto=format&fit=crop" alt="" class="w-full h-full object-cover opacity-[0.12] rounded-tl-[10rem] lg:rounded-tl-[14rem]" aria-hidden="true">
                </div>
                <div class="absolute right-8 top-1/4 w-24 h-24 lg:w-32 lg:h-32 z-0 hidden xl:block">
                    <img src="https://images.unsplash.com/photo-1414235077428-338989a2e8c0?w=400&auto=format&fit=crop" alt="" class="w-full h-full object-cover opacity-20 rounded-2xl" aria-hidden="true">
                </div>
            @endif

            <!-- Decorative Blobs (tons neutres pour fond noir) -->
            <div class="absolute -right-32 -top-32 w-96 h-96 bg-white/10 rounded-full blur-3xl"></div>
            <div class="absolute -left-32 -bottom-32 w-96 h-96 bg-white/5 rounded-full blur-3xl"></div>
            <div class="absolute right-1/4 bottom-1/4 w-64 h-64 bg-neutral-500/10 rounded-full blur-3xl"></div>

            <!-- Content -->
            <div class="relative z-10 flex flex-col justify-between p-8 lg:p-10 xl:p-14 w-full min-h-0">
                <!-- Logo (sans texte "MenuPro" quand le logo est affiché) -->
                <a href="{{ route('home') }}" class="flex items-center gap-3 group flex-shrink-0">
                    @if($logoUrl)
                        <img src="{{ $logoUrl }}" alt="{{ $appName }}" class="h-12 w-auto object-contain max-h-12">
                    @else
                        <div class="w-12 h-12 bg-white/10 backdrop-blur-sm rounded-xl flex items-center justify-center group-hover:bg-white/20 transition-colors">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                        </div>
                        <span class="text-xl lg:text-2xl font-bold text-white">{{ $appName }}</span>
                    @endif
                </a>

                <!-- Main Content -->
                <div class="max-w-lg flex-1 min-h-0 flex flex-col justify-center py-6">
                    <h1 class="font-display text-3xl lg:text-4xl xl:text-5xl font-bold text-white mb-4 lg:mb-6 leading-tight">
                        Digitalisez votre restaurant en 
                        <span class="text-primary-400">quelques clics</span>
                    </h1>
                    <p class="text-base lg:text-lg xl:text-xl text-neutral-300 leading-relaxed">
                        Rejoignez des centaines de restaurants qui utilisent {{ $appName }} pour gérer leurs menus et commandes en ligne.
                    </p>
                    
                    <!-- Stats (données réelles) -->
                    <div class="mt-8 lg:mt-10 xl:mt-12 grid grid-cols-3 gap-4 lg:gap-6 xl:gap-8">
                        <div class="text-center">
                            <div class="text-2xl lg:text-3xl xl:text-4xl font-bold text-white">{{ $restaurantsCount }}</div>
                            <div class="text-neutral-400 text-xs lg:text-sm mt-1">Restaurants</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl lg:text-3xl xl:text-4xl font-bold text-white">{{ $ordersCount }}</div>
                            <div class="text-neutral-400 text-xs lg:text-sm mt-1">Commandes</div>
                        </div>
                        <div class="text-center">
                            <div class="text-2xl lg:text-3xl xl:text-4xl font-bold text-white">99.9%</div>
                            <div class="text-neutral-400 text-xs lg:text-sm mt-1">Uptime</div>
                        </div>
                    </div>
                </div>

                <!-- Testimonial -->
                <div class="bg-white/10 backdrop-blur-sm rounded-2xl p-5 xl:p-6 max-w-lg border border-white/10">
                    <div class="flex items-start gap-4">
                        <img 
                            src="https://ui-avatars.com/api/?name=Koffi+Adjoumani&background=f97316&color=fff&size=96" 
                            alt="Témoignage" 
                            class="w-12 h-12 rounded-full ring-2 ring-white/20"
                        >
                        <div>
                            <p class="text-white/90 italic leading-relaxed">
                                "MenuPro a transformé notre façon de gérer les commandes. Nos clients adorent commander depuis leur téléphone !"
                            </p>
                            <div class="mt-3">
                                <div class="font-semibold text-white">Koffi Adjoumani</div>
                                <div class="text-neutral-400 text-sm">Restaurant Le Délice, Abidjan</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Side - Form -->
        <div class="w-full lg:w-1/2 flex items-center justify-center p-4 sm:p-6 lg:p-8 bg-white overflow-y-auto min-h-screen lg:min-h-0">
            <div class="w-full max-w-md py-4 sm:py-6">
                <!-- Mobile Logo + Données réelles (visible sur mobile/tablette) -->
                <div class="lg:hidden mb-6 text-center">
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-2 sm:gap-3">
                        @if($logoUrl)
                            <img src="{{ $logoUrl }}" alt="{{ $appName }}" class="h-10 sm:h-11 w-auto object-contain">
                        @else
                            <div class="w-10 h-10 sm:w-11 sm:h-11 bg-gradient-to-br from-primary-500 to-primary-600 rounded-xl flex items-center justify-center shadow-lg shadow-primary-500/25 flex-shrink-0">
                                <svg class="w-5 h-5 sm:w-6 sm:h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                            </div>
                            <span class="text-lg sm:text-xl font-bold text-neutral-900">{{ $appName }}</span>
                        @endif
                    </a>
                    <!-- Stats compactes (données réelles) sur mobile -->
                    <div class="flex flex-wrap items-center justify-center gap-x-3 gap-y-1 mt-3 text-sm text-neutral-500">
                        <span><strong class="text-neutral-700">{{ $restaurantsCount }}</strong> Restaurants</span>
                        <span class="text-neutral-300">•</span>
                        <span><strong class="text-neutral-700">{{ $ordersCount }}</strong> Commandes</span>
                        <span class="text-neutral-300">•</span>
                        <span><strong class="text-neutral-700">99.9%</strong> Uptime</span>
                    </div>
                </div>

                <!-- Form Slot -->
                {{ $slot }}

                <!-- Footer Links -->
                <div class="mt-6 sm:mt-8 pt-4 sm:pt-6 border-t border-neutral-100 text-center">
                    <div class="flex flex-wrap items-center justify-center gap-x-3 gap-y-1 text-xs sm:text-sm text-neutral-500">
                        <a href="{{ route('terms') }}" class="hover:text-primary-600 transition-colors">Conditions</a>
                        <span class="text-neutral-300">•</span>
                        <a href="{{ route('privacy') }}" class="hover:text-primary-600 transition-colors">Confidentialité</a>
                        <span class="text-neutral-300">•</span>
                        <a href="{{ route('home') }}" class="hover:text-primary-600 transition-colors">Accueil</a>
                    </div>
                    <p class="text-xs text-neutral-400 mt-2 sm:mt-3">
                        © {{ date('Y') }} {{ $appName }}. Tous droits réservés.
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
