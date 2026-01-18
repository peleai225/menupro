<x-layouts.app :title="$title ?? 'Connexion'">
    <div class="min-h-screen flex">
        <!-- Left Side - Branding (Hidden on mobile) -->
        <div class="hidden lg:flex lg:w-1/2 bg-gradient-to-br from-neutral-950 via-black to-neutral-950 relative overflow-hidden">
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
            @php
                $customAuthImage = null;
                foreach (['custom.jpg', 'custom.png', 'custom.webp'] as $name) {
                    if (file_exists(public_path('images/auth/' . $name))) {
                        $customAuthImage = asset('images/auth/' . $name);
                        break;
                    }
                }
            @endphp
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
            <div class="relative z-10 flex flex-col justify-between p-10 xl:p-14 w-full">
                <!-- Logo -->
                <a href="{{ route('home') }}" class="flex items-center gap-3 group">
                    @php
                        $logo = \App\Models\SystemSetting::get('logo', '');
                        $appName = \App\Models\SystemSetting::get('app_name', 'MenuPro');
                    @endphp
                    @if(!empty($logo))
                        <img src="{{ \Illuminate\Support\Facades\Storage::url($logo) }}" 
                             alt="{{ $appName }}" 
                             class="h-12 w-auto object-contain">
                    @else
                        <div class="w-12 h-12 bg-white/10 backdrop-blur-sm rounded-xl flex items-center justify-center group-hover:bg-white/20 transition-colors">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                            </svg>
                        </div>
                        <span class="text-2xl font-bold text-white">{{ $appName }}</span>
                    @endif
                </a>

                <!-- Main Content -->
                <div class="max-w-lg">
                    <h1 class="font-display text-4xl xl:text-5xl font-bold text-white mb-6 leading-tight">
                        Digitalisez votre restaurant en 
                        <span class="text-primary-400">quelques clics</span>
                    </h1>
                    <p class="text-lg xl:text-xl text-neutral-300 leading-relaxed">
                        Rejoignez des centaines de restaurants qui utilisent MenuPro pour gérer leurs menus et commandes en ligne.
                    </p>
                    
                    <!-- Stats -->
                    @php
                        $totalRestaurants = 0;
                        $totalOrders = 0;
                        $restaurantsCount = '0';
                        $ordersCount = '0';
                        
                        try {
                            $totalRestaurants = \App\Models\Restaurant::where('status', \App\Enums\RestaurantStatus::ACTIVE)->count();
                            $totalOrders = \App\Models\Order::withoutGlobalScope('restaurant')->count();
                            
                            // Format restaurants
                            if ($totalRestaurants >= 500) {
                                $restaurantsCount = number_format($totalRestaurants, 0, ',', ' ') . '+';
                            } else {
                                $restaurantsCount = number_format($totalRestaurants, 0, ',', ' ');
                            }
                            
                            // Format orders
                            if ($totalOrders >= 50000) {
                                $ordersCount = number_format($totalOrders / 1000, 0, ',', ' ') . 'K+';
                            } elseif ($totalOrders >= 1000) {
                                $ordersCount = number_format($totalOrders / 1000, 1, ',', ' ') . 'K+';
                            } else {
                                $ordersCount = number_format($totalOrders, 0, ',', ' ');
                            }
                        } catch (\Exception $e) {
                            // Fallback values if there's an error
                            $restaurantsCount = '0';
                            $ordersCount = '0';
                        }
                    @endphp
                    <div class="mt-10 xl:mt-12 grid grid-cols-3 gap-6 xl:gap-8">
                        <div class="text-center">
                            <div class="text-3xl xl:text-4xl font-bold text-white">{{ $restaurantsCount }}</div>
                            <div class="text-neutral-400 text-sm mt-1">Restaurants</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl xl:text-4xl font-bold text-white">{{ $ordersCount }}</div>
                            <div class="text-neutral-400 text-sm mt-1">Commandes</div>
                        </div>
                        <div class="text-center">
                            <div class="text-3xl xl:text-4xl font-bold text-white">99.9%</div>
                            <div class="text-neutral-400 text-sm mt-1">Uptime</div>
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
        <div class="w-full lg:w-1/2 flex items-center justify-center p-6 sm:p-8 bg-white">
            <div class="w-full max-w-md">
                <!-- Mobile Logo (Visible only on mobile) -->
                <div class="lg:hidden mb-8 text-center">
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-3">
                        @php
                            $logo = \App\Models\SystemSetting::get('logo', '');
                            $appName = \App\Models\SystemSetting::get('app_name', 'MenuPro');
                        @endphp
                        @if(!empty($logo))
                            <img src="{{ \Illuminate\Support\Facades\Storage::url($logo) }}" 
                                 alt="{{ $appName }}" 
                                 class="h-11 w-auto object-contain">
                        @else
                            <div class="w-11 h-11 bg-gradient-to-br from-primary-500 to-primary-600 rounded-xl flex items-center justify-center shadow-lg shadow-primary-500/25">
                                <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                </svg>
                            </div>
                            <span class="text-xl font-bold text-neutral-900">{{ $appName }}</span>
                        @endif
                    </a>
                </div>

                <!-- Form Slot -->
                {{ $slot }}

                <!-- Footer Links -->
                <div class="mt-8 pt-6 border-t border-neutral-100 text-center">
                    <div class="flex items-center justify-center gap-4 text-sm text-neutral-500">
                        <a href="{{ route('terms') }}" class="hover:text-primary-600 transition-colors">Conditions</a>
                        <span class="text-neutral-300">•</span>
                        <a href="{{ route('privacy') }}" class="hover:text-primary-600 transition-colors">Confidentialité</a>
                        <span class="text-neutral-300">•</span>
                        <a href="{{ route('home') }}" class="hover:text-primary-600 transition-colors">Accueil</a>
                    </div>
                    <p class="text-xs text-neutral-400 mt-3">
                        © {{ date('Y') }} MenuPro. Tous droits réservés.
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
