<x-layouts.auth title="Connexion">
    <div class="animate-fade-in">
        @if (session('success'))
            <div class="mb-6 p-3.5 bg-secondary-50 border border-secondary-200 rounded-2xl flex items-center gap-3 animate-slide-up">
                <div class="w-8 h-8 bg-secondary-100 rounded-full flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-secondary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <p class="text-sm text-secondary-700 font-medium">{{ session('success') }}</p>
            </div>
        @endif

        {{-- Header --}}
        <div class="mb-8 sm:mb-10">
            <div class="inline-flex items-center gap-2 px-3 py-1 bg-primary-50 border border-primary-100 text-primary-600 rounded-full text-[11px] font-bold mb-4 uppercase tracking-wider">
                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                </svg>
                Espace restaurateur
            </div>
            <h1 class="text-2xl sm:text-[28px] font-bold text-neutral-950 tracking-tight leading-tight">
                Bon retour !
            </h1>
            <p class="text-neutral-500 mt-2 text-[15px]">Connectez-vous pour piloter votre restaurant.</p>
        </div>

        <form method="POST" action="{{ route('login.post') }}" class="space-y-5" x-data="{ loading: false, showPassword: false }" @submit="loading = true">
            @csrf

            {{-- Login field --}}
            <div>
                <label for="login" class="block text-[13px] font-semibold text-neutral-700 mb-2">
                    Email ou numero WhatsApp
                </label>
                <div class="relative group">
                    <div class="absolute left-0 top-0 bottom-0 w-12 flex items-center justify-center pointer-events-none">
                        <svg class="w-[18px] h-[18px] text-neutral-400 group-focus-within:text-primary-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </div>
                    <input
                        type="text" id="login" name="login" value="{{ old('login') }}"
                        placeholder="vous@email.com ou 07 00 00 00 00"
                        required autofocus autocomplete="username"
                        class="w-full h-[52px] pl-12 pr-4 bg-neutral-50/80 border border-neutral-200 rounded-2xl text-neutral-900 text-[15px] placeholder:text-neutral-400 transition-all duration-200 focus:outline-none focus:bg-white focus:border-primary-400 focus:ring-[3px] focus:ring-primary-500/10 @error('login') border-error-500/50 bg-error-500/5 @enderror"
                    >
                </div>
                @error('login')
                    <p class="mt-2 text-[13px] text-error-600 flex items-center gap-1.5 font-medium">
                        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Password field --}}
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label for="password" class="block text-[13px] font-semibold text-neutral-700">Mot de passe</label>
                    <a href="{{ route('password.request') }}" class="text-[12px] text-primary-600 hover:text-primary-700 font-semibold hover:underline underline-offset-2 transition-colors">
                        Mot de passe oublie ?
                    </a>
                </div>
                <div class="relative group">
                    <div class="absolute left-0 top-0 bottom-0 w-12 flex items-center justify-center pointer-events-none">
                        <svg class="w-[18px] h-[18px] text-neutral-400 group-focus-within:text-primary-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <input
                        :type="showPassword ? 'text' : 'password'"
                        id="password" name="password" placeholder="Votre mot de passe"
                        required autocomplete="current-password"
                        class="w-full h-[52px] pl-12 pr-12 bg-neutral-50/80 border border-neutral-200 rounded-2xl text-neutral-900 text-[15px] placeholder:text-neutral-400 transition-all duration-200 focus:outline-none focus:bg-white focus:border-primary-400 focus:ring-[3px] focus:ring-primary-500/10 @error('password') border-error-500/50 bg-error-500/5 @enderror"
                    >
                    <button type="button" @click="showPassword = !showPassword"
                            class="absolute right-3.5 top-1/2 -translate-y-1/2 w-8 h-8 flex items-center justify-center text-neutral-400 hover:text-neutral-600 rounded-xl hover:bg-neutral-100 transition-all">
                        <svg x-show="!showPassword" class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg x-show="showPassword" x-cloak class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                        </svg>
                    </button>
                </div>
                @error('password')
                    <p class="mt-2 text-[13px] text-error-600 flex items-center gap-1.5 font-medium">
                        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            {{-- Remember me --}}
            <label for="remember" class="flex items-center gap-3 cursor-pointer select-none py-1 group">
                <div class="relative">
                    <input type="checkbox" id="remember" name="remember"
                           class="peer w-[18px] h-[18px] rounded-md border-neutral-300 text-primary-500 focus:ring-primary-500/20 focus:ring-offset-0 transition-all cursor-pointer"
                           {{ old('remember') ? 'checked' : '' }}>
                </div>
                <span class="text-[13px] text-neutral-600 group-hover:text-neutral-800 transition-colors font-medium">Se souvenir de moi</span>
            </label>

            {{-- Submit --}}
            <button type="submit"
                    class="w-full h-[52px] bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white font-semibold text-[15px] rounded-2xl relative overflow-hidden shadow-lg shadow-primary-500/25 hover:shadow-primary-500/40 active:scale-[0.98] transition-all duration-200"
                    :disabled="loading">
                <span :class="{ 'opacity-0': loading }" class="flex items-center justify-center gap-2.5">
                    Se connecter
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                </span>
                <span x-show="loading" x-cloak class="absolute inset-0 flex items-center justify-center">
                    <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </span>
            </button>
        </form>

        {{-- Divider --}}
        <div class="relative my-8">
            <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-neutral-200"></div></div>
            <div class="relative flex justify-center">
                <span class="px-4 bg-white text-[12px] text-neutral-400 font-medium uppercase tracking-wider">Nouveau ici ?</span>
            </div>
        </div>

        {{-- Register CTA --}}
        <a href="{{ route('register') }}"
           class="flex items-center justify-center gap-2.5 w-full h-[52px] border-2 border-neutral-200 rounded-2xl text-neutral-700 font-semibold text-[15px] hover:border-primary-300 hover:text-primary-600 hover:bg-primary-50/50 active:scale-[0.98] transition-all duration-200 group">
            <svg class="w-5 h-5 text-neutral-400 group-hover:text-primary-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Creer mon restaurant
        </a>

        {{-- Trust --}}
        <div class="mt-8 flex items-center justify-center gap-5">
            @foreach([
                ['icon' => 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z', 'text' => 'SSL 256-bit'],
                ['icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', 'text' => 'RGPD'],
                ['icon' => 'M13 10V3L4 14h7v7l9-11h-7z', 'text' => '99.9% uptime'],
            ] as $trust)
            <div class="flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5 text-secondary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $trust['icon'] }}"/>
                </svg>
                <span class="text-[11px] text-neutral-500 font-medium">{{ $trust['text'] }}</span>
            </div>
            @endforeach
        </div>
    </div>
</x-layouts.auth>
