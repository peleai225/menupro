<x-layouts.auth title="Mot de passe oublié">
    <div class="animate-fade-in">
        <div class="mb-8 sm:mb-10">
            <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-amber-50 text-amber-600 rounded-full text-xs font-semibold mb-4 tracking-wide uppercase">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                </svg>
                Récupération
            </div>
            <h1 class="text-2xl sm:text-[28px] font-bold text-neutral-950 tracking-tight">Mot de passe oublié ?</h1>
            <p class="text-neutral-500 mt-2 text-[15px]">Entrez votre numéro WhatsApp — nous vous envoyons un code.</p>
        </div>

        @if(session('status'))
            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-start gap-3">
                <svg class="w-5 h-5 text-emerald-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <p class="text-sm text-emerald-700">{{ session('status') }}</p>
            </div>
        @endif

        <form method="POST" action="{{ route('password.otp.send') }}"
              class="space-y-5" x-data="{ loading: false }" @submit="loading = true">
            @csrf

            <div>
                <label for="phone" class="block text-[13px] font-semibold text-neutral-700 mb-2">
                    Numéro WhatsApp
                </label>
                <div class="relative group">
                    <div class="absolute left-0 top-0 bottom-0 w-12 flex items-center justify-center pointer-events-none">
                        <svg class="w-[18px] h-[18px] text-neutral-400 group-focus-within:text-primary-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                    </div>
                    <input type="tel" id="phone" name="phone"
                           value="{{ old('phone') }}"
                           placeholder="07 00 00 00 00"
                           required autofocus inputmode="tel" autocomplete="tel"
                           class="w-full h-[52px] pl-12 pr-4 bg-neutral-50/80 border border-neutral-200 rounded-2xl text-neutral-900 text-[15px] placeholder:text-neutral-400 transition-all duration-200 focus:outline-none focus:bg-white focus:border-primary-400 focus:ring-[3px] focus:ring-primary-500/10 @error('phone') border-error-500/50 bg-error-500/5 @enderror">
                </div>
                @error('phone')
                    <p class="mt-2 text-[13px] text-error-600 flex items-center gap-1.5 font-medium">
                        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <button type="submit"
                    class="w-full h-[52px] bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white font-semibold text-[15px] rounded-2xl relative overflow-hidden shadow-lg shadow-primary-500/25 hover:shadow-primary-500/40 active:scale-[0.98] transition-all duration-200"
                    :disabled="loading">
                <span :class="{ 'opacity-0': loading }" class="flex items-center justify-center gap-2">
                    Recevoir le code WhatsApp
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

        <div class="mt-8 text-center">
            <a href="{{ route('login') }}" class="inline-flex items-center gap-2 text-[13px] text-neutral-500 hover:text-primary-600 font-medium transition-colors">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"/>
                </svg>
                Retour à la connexion
            </a>
        </div>
    </div>
</x-layouts.auth>
