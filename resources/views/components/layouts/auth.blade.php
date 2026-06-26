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
        $customAuthImage = null;
        foreach (['custom.jpg', 'custom.png', 'custom.webp'] as $name) {
            if (file_exists(public_path('images/auth/' . $name))) {
                $customAuthImage = asset('images/auth/' . $name);
                break;
            }
        }
    @endphp
    <div class="min-h-screen flex flex-col lg:flex-row bg-white">
        {{-- Left Side - Visual Branding --}}
        <div class="hidden lg:flex lg:w-[45%] xl:w-1/2 relative overflow-hidden flex-shrink-0">
            {{-- Background --}}
            <div class="absolute inset-0 bg-gradient-to-br from-neutral-950 via-neutral-900 to-neutral-950"></div>

            {{-- Image --}}
            @if($customAuthImage)
                <div class="absolute inset-0">
                    <img src="{{ $customAuthImage }}" alt="" class="w-full h-full object-cover opacity-40" aria-hidden="true">
                </div>
            @else
                <div class="absolute inset-0">
                    <img src="https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=1200&auto=format&fit=crop" alt="" class="w-full h-full object-cover opacity-20" aria-hidden="true">
                </div>
            @endif

            {{-- Gradient overlay --}}
            <div class="absolute inset-0 bg-gradient-to-t from-neutral-950 via-neutral-950/60 to-neutral-950/40"></div>

            {{-- Decorative elements --}}
            <div class="absolute -top-24 -right-24 w-80 h-80 bg-primary-500/20 rounded-full blur-[100px]"></div>
            <div class="absolute -bottom-32 -left-16 w-72 h-72 bg-primary-500/10 rounded-full blur-[80px]"></div>

            {{-- Content --}}
            <div class="relative z-10 flex flex-col justify-between p-10 xl:p-14 w-full">
                {{-- Logo --}}
                <a href="{{ route('home') }}" class="inline-flex items-center gap-3">
                    @if($logoUrl)
                        <img src="{{ $logoUrl }}" alt="{{ $appName }}" class="h-10 w-auto object-contain">
                    @else
                        <img src="{{ asset('images/logo-menupro.png') }}" alt="{{ $appName }}" class="h-9 w-auto object-contain">
                    @endif
                </a>

                {{-- Hero text --}}
                <div class="max-w-md">
                    <h1 class="text-3xl xl:text-4xl font-bold text-white leading-tight">
                        Gerez votre restaurant
                        <span class="block text-primary-400 mt-1">simplement.</span>
                    </h1>
                    <p class="text-neutral-400 mt-4 text-base xl:text-lg leading-relaxed">
                        Menu digital, commandes en ligne, paiements mobile — tout en une seule plateforme.
                    </p>

                    {{-- Features --}}
                    <div class="mt-8 space-y-3">
                        @foreach([
                            'Menu QR code + commande en ligne',
                            'Paiement Wave, Orange Money, MTN',
                            'Tableau de bord temps reel',
                        ] as $feature)
                        <div class="flex items-center gap-3">
                            <div class="w-5 h-5 rounded-full bg-primary-500/20 flex items-center justify-center flex-shrink-0">
                                <svg class="w-3 h-3 text-primary-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                </svg>
                            </div>
                            <span class="text-neutral-300 text-sm">{{ $feature }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Testimonial --}}
                <div class="bg-white/5 backdrop-blur-sm rounded-2xl p-5 border border-white/10 max-w-md">
                    <div class="flex items-center gap-1 mb-3">
                        @for($i = 0; $i < 5; $i++)
                        <svg class="w-4 h-4 text-primary-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        @endfor
                    </div>
                    <p class="text-white/80 text-sm italic leading-relaxed">
                        "Depuis qu'on utilise MenuPro, nos commandes ont augmente de 40%. Les clients adorent commander depuis leur telephone."
                    </p>
                    <div class="mt-3 flex items-center gap-3">
                        <div class="w-8 h-8 rounded-full bg-primary-500/30 flex items-center justify-center">
                            <span class="text-xs font-bold text-primary-300">KA</span>
                        </div>
                        <div>
                            <div class="text-sm font-medium text-white">Koffi A.</div>
                            <div class="text-xs text-neutral-500">Restaurant Le Delice, Abidjan</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Side - Form --}}
        <div class="w-full lg:w-[55%] xl:w-1/2 flex flex-col min-h-screen lg:min-h-0">
            {{-- Mobile Header --}}
            <div class="lg:hidden bg-gradient-to-br from-neutral-900 to-neutral-950 px-5 py-5">
                <div class="flex items-center justify-between">
                    <a href="{{ route('home') }}" class="inline-flex">
                        @if($logoUrl)
                            <img src="{{ $logoUrl }}" alt="{{ $appName }}" class="h-8 w-auto object-contain">
                        @else
                            <img src="{{ asset('images/logo-menupro.png') }}" alt="{{ $appName }}" class="h-7 w-auto object-contain brightness-0 invert">
                        @endif
                    </a>
                    <div class="flex items-center gap-2">
                        <div class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></div>
                        <span class="text-xs text-neutral-400 font-medium">En ligne</span>
                    </div>
                </div>
            </div>

            {{-- Form Content --}}
            <div class="flex-1 flex items-center justify-center px-5 sm:px-8 lg:px-12 xl:px-16 overflow-y-auto">
                <div class="w-full max-w-[440px] py-8 sm:py-10 lg:py-12">
                    {{ $slot }}
                </div>
            </div>

            {{-- Footer --}}
            <div class="px-5 sm:px-8 lg:px-12 pb-5">
                <div class="max-w-[440px] mx-auto pt-4 border-t border-neutral-100 text-center">
                    <div class="flex flex-wrap items-center justify-center gap-x-4 gap-y-1 text-xs text-neutral-400">
                        <a href="{{ route('terms') }}" class="hover:text-primary-600 transition-colors">Conditions</a>
                        <span class="text-neutral-200">·</span>
                        <a href="{{ route('privacy') }}" class="hover:text-primary-600 transition-colors">Confidentialite</a>
                        <span class="text-neutral-200">·</span>
                        <a href="{{ route('home') }}" class="hover:text-primary-600 transition-colors">Accueil</a>
                    </div>
                    <p class="text-[11px] text-neutral-400 mt-2">
                        &copy; {{ date('Y') }} {{ $appName }}
                    </p>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
