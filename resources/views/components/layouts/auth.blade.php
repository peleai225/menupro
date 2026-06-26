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
    <div class="min-h-screen flex flex-col lg:flex-row">
        {{-- ═══════════════════════════════════════════════════
             LEFT — Immersive Brand Panel
        ═══════════════════════════════════════════════════ --}}
        <div class="hidden lg:flex lg:w-[48%] xl:w-[52%] relative overflow-hidden flex-shrink-0">
            {{-- Mesh background --}}
            <div class="absolute inset-0 bg-gradient-mesh"></div>

            {{-- Photo layer --}}
            @if($customAuthImage)
                <div class="absolute inset-0 z-[1]">
                    <img src="{{ $customAuthImage }}" alt="" class="w-full h-full object-cover opacity-30 mix-blend-luminosity" aria-hidden="true">
                </div>
            @else
                <div class="absolute inset-0 z-[1]">
                    <img src="https://images.unsplash.com/photo-1517248135467-4c7edcad34c4?w=1200&auto=format&fit=crop" alt="" class="w-full h-full object-cover opacity-15 mix-blend-luminosity" aria-hidden="true">
                </div>
            @endif

            {{-- Radial glow overlays --}}
            <div class="absolute -top-32 -left-32 w-[500px] h-[500px] bg-primary-500/15 rounded-full blur-[120px] z-[2]"></div>
            <div class="absolute -bottom-40 -right-20 w-[400px] h-[400px] bg-accent-500/10 rounded-full blur-[100px] z-[2]"></div>
            <div class="absolute top-1/2 left-1/3 w-[300px] h-[300px] bg-primary-600/8 rounded-full blur-[80px] z-[2]"></div>

            {{-- Dot pattern --}}
            <div class="absolute inset-0 z-[3] pattern-dots opacity-30"></div>

            {{-- Content --}}
            <div class="relative z-10 flex flex-col justify-between p-10 xl:p-14 w-full">
                {{-- Logo --}}
                <div>
                    <a href="{{ route('home') }}" class="inline-flex items-center gap-3 group">
                        @if($logoUrl)
                            <img src="{{ $logoUrl }}" alt="{{ $appName }}" class="h-10 xl:h-11 w-auto object-contain drop-shadow-lg">
                        @else
                            <img src="{{ asset('images/logo-menupro.png') }}" alt="{{ $appName }}" class="h-9 xl:h-10 w-auto object-contain drop-shadow-lg">
                        @endif
                    </a>
                </div>

                {{-- Hero --}}
                <div class="max-w-lg">
                    <div class="inline-flex items-center gap-2 px-3 py-1.5 glass rounded-full mb-6">
                        <div class="w-2 h-2 rounded-full bg-secondary-400 animate-pulse"></div>
                        <span class="text-xs font-medium text-neutral-300 tracking-wide">Plateforme N°1 en Cote d'Ivoire</span>
                    </div>

                    <h1 class="text-4xl xl:text-5xl font-bold text-white leading-[1.15] tracking-tight">
                        Votre restaurant,
                        <span class="text-gradient-light block mt-1">100% digital.</span>
                    </h1>

                    <p class="text-neutral-400 mt-5 text-base xl:text-lg leading-relaxed max-w-md">
                        Menu QR code, commandes en ligne, paiement Mobile Money — gerez tout depuis une seule plateforme.
                    </p>

                    {{-- Features pills --}}
                    <div class="mt-8 flex flex-wrap gap-2.5">
                        @foreach(['Menu QR Code', 'Wave & Orange Money', 'Cuisine temps reel', 'Livraison integree'] as $f)
                        <div class="glass px-3.5 py-2 rounded-xl flex items-center gap-2">
                            <svg class="w-3.5 h-3.5 text-primary-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-xs font-medium text-neutral-200">{{ $f }}</span>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Testimonial card --}}
                <div class="glass rounded-2xl p-5 max-w-md">
                    <div class="flex items-center gap-1.5 mb-3">
                        @for($i = 0; $i < 5; $i++)
                        <svg class="w-3.5 h-3.5 text-primary-400" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        @endfor
                    </div>
                    <p class="text-white/85 text-sm leading-relaxed italic">
                        "Depuis MenuPro, nos commandes ont augmente de 40%. Les clients adorent commander depuis leur telephone !"
                    </p>
                    <div class="mt-4 flex items-center gap-3">
                        <div class="w-9 h-9 rounded-full bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center shadow-lg">
                            <span class="text-xs font-bold text-white">KA</span>
                        </div>
                        <div>
                            <div class="text-sm font-semibold text-white">Koffi Adjoumani</div>
                            <div class="text-xs text-neutral-400">Le Delice — Abidjan Cocody</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- ═══════════════════════════════════════════════════
             RIGHT — Form Panel
        ═══════════════════════════════════════════════════ --}}
        <div class="w-full lg:w-[52%] xl:w-[48%] flex flex-col bg-white min-h-screen lg:min-h-0">
            {{-- Mobile Header --}}
            <div class="lg:hidden relative overflow-hidden">
                <div class="bg-gradient-mesh px-5 pt-6 pb-8">
                    <div class="pattern-dots absolute inset-0 opacity-20"></div>
                    <div class="absolute -top-16 -right-16 w-48 h-48 bg-primary-500/20 rounded-full blur-[60px]"></div>
                    <div class="relative z-10">
                        <a href="{{ route('home') }}" class="inline-flex">
                            @if($logoUrl)
                                <img src="{{ $logoUrl }}" alt="{{ $appName }}" class="h-8 w-auto object-contain">
                            @else
                                <img src="{{ asset('images/logo-menupro.png') }}" alt="{{ $appName }}" class="h-7 w-auto object-contain brightness-0 invert">
                            @endif
                        </a>
                        <p class="text-neutral-400 text-xs mt-3 font-medium">Digitalisez votre restaurant en quelques clics</p>
                    </div>
                </div>
                {{-- Curved bottom --}}
                <div class="absolute -bottom-1 left-0 right-0 h-4 bg-white rounded-t-[20px]"></div>
            </div>

            {{-- Form Content --}}
            <div class="flex-1 flex items-start lg:items-center justify-center px-6 sm:px-10 lg:px-16 xl:px-20 overflow-y-auto">
                <div class="w-full max-w-[440px] mx-auto py-8 sm:py-10 lg:py-12">
                    {{ $slot }}
                </div>
            </div>

            {{-- Footer --}}
            <div class="px-6 sm:px-10 lg:px-16 xl:px-20 pb-4 sm:pb-5 flex-shrink-0">
                <div class="max-w-[440px] mx-auto pt-4 border-t border-neutral-100">
                    <div class="flex flex-wrap items-center justify-center gap-x-4 text-[11px] text-neutral-400">
                        <a href="{{ route('terms') }}" class="hover:text-primary-600 transition-colors">Conditions</a>
                        <span class="text-neutral-200">&bull;</span>
                        <a href="{{ route('privacy') }}" class="hover:text-primary-600 transition-colors">Confidentialite</a>
                        <span class="text-neutral-200">&bull;</span>
                        <a href="{{ route('home') }}" class="hover:text-primary-600 transition-colors">Accueil</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-layouts.app>
