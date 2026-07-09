<x-layouts.auth title="Vérification du code">
    <div class="animate-fade-in">
        <div class="mb-8">
            <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-primary-50 text-primary-600 rounded-full text-xs font-semibold mb-4 tracking-wide uppercase">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                Code WhatsApp
            </div>
            <h1 class="text-2xl sm:text-[28px] font-bold text-neutral-950 tracking-tight">Entrez votre code</h1>
            <p class="text-neutral-500 mt-2 text-[15px]">
                Code envoyé sur WhatsApp
                @if($phone)
                    au <span class="font-semibold text-neutral-700">{{ $phone }}</span>
                @endif
                — valable 10 minutes.
            </p>
        </div>

        @if($errors->any())
            <div class="mb-6 p-4 bg-error-500/5 border border-error-500/20 rounded-2xl">
                @foreach($errors->all() as $error)
                    <p class="text-[13px] text-error-600 font-medium flex items-center gap-2">
                        <svg class="w-3.5 h-3.5 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        {{ $error }}
                    </p>
                @endforeach
            </div>
        @endif

        <form method="POST" action="{{ route('password.otp.verify') }}"
              class="space-y-5"
              x-data="{ loading: false, showPassword: false, showConfirm: false }"
              @submit="loading = true">
            @csrf

            <input type="hidden" name="phone" value="{{ $phone }}">

            {{-- Code OTP --}}
            <div>
                <label for="otp" class="block text-[13px] font-semibold text-neutral-700 mb-2">
                    Code à 6 chiffres
                </label>
                <input type="text" id="otp" name="otp"
                       value="{{ old('otp') }}"
                       placeholder="000000"
                       required maxlength="6" inputmode="numeric" autocomplete="one-time-code"
                       autofocus
                       class="w-full h-[60px] px-4 text-center bg-neutral-50/80 border border-neutral-200 rounded-2xl text-neutral-900 text-[28px] font-bold tracking-[0.5em] placeholder:text-neutral-300 transition-all duration-200 focus:outline-none focus:bg-white focus:border-primary-400 focus:ring-[3px] focus:ring-primary-500/10 @error('otp') border-error-500/50 @enderror">
            </div>

            {{-- Nouveau mot de passe --}}
            <div>
                <label for="password" class="block text-[13px] font-semibold text-neutral-700 mb-2">
                    Nouveau mot de passe
                </label>
                <div class="relative group">
                    <div class="absolute left-0 top-0 bottom-0 w-12 flex items-center justify-center pointer-events-none">
                        <svg class="w-[18px] h-[18px] text-neutral-400 group-focus-within:text-primary-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </div>
                    <input :type="showPassword ? 'text' : 'password'"
                           id="password" name="password"
                           placeholder="Minimum 8 caractères"
                           required minlength="8" autocomplete="new-password"
                           class="w-full h-[52px] pl-12 pr-12 bg-neutral-50/80 border border-neutral-200 rounded-2xl text-neutral-900 text-[15px] placeholder:text-neutral-400 transition-all duration-200 focus:outline-none focus:bg-white focus:border-primary-400 focus:ring-[3px] focus:ring-primary-500/10 @error('password') border-error-500/50 @enderror">
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
            </div>

            {{-- Confirmation --}}
            <div>
                <label for="password_confirmation" class="block text-[13px] font-semibold text-neutral-700 mb-2">
                    Confirmer le mot de passe
                </label>
                <div class="relative group">
                    <div class="absolute left-0 top-0 bottom-0 w-12 flex items-center justify-center pointer-events-none">
                        <svg class="w-[18px] h-[18px] text-neutral-400 group-focus-within:text-primary-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <input :type="showConfirm ? 'text' : 'password'"
                           id="password_confirmation" name="password_confirmation"
                           placeholder="Répétez le mot de passe"
                           required autocomplete="new-password"
                           class="w-full h-[52px] pl-12 pr-12 bg-neutral-50/80 border border-neutral-200 rounded-2xl text-neutral-900 text-[15px] placeholder:text-neutral-400 transition-all duration-200 focus:outline-none focus:bg-white focus:border-primary-400 focus:ring-[3px] focus:ring-primary-500/10">
                    <button type="button" @click="showConfirm = !showConfirm"
                            class="absolute right-3.5 top-1/2 -translate-y-1/2 w-8 h-8 flex items-center justify-center text-neutral-400 hover:text-neutral-600 rounded-xl hover:bg-neutral-100 transition-all">
                        <svg x-show="!showConfirm" class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg x-show="showConfirm" x-cloak class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                        </svg>
                    </button>
                </div>
            </div>

            <button type="submit"
                    class="w-full h-[52px] bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white font-semibold text-[15px] rounded-2xl relative overflow-hidden shadow-lg shadow-primary-500/25 active:scale-[0.98] transition-all duration-200"
                    :disabled="loading">
                <span :class="{ 'opacity-0': loading }" class="flex items-center justify-center gap-2">
                    Réinitialiser le mot de passe
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
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

        <div class="mt-6 text-center">
            <a href="{{ route('password.otp.request') }}" class="text-[13px] text-primary-600 font-medium hover:underline underline-offset-2">
                Renvoyer le code
            </a>
            <span class="text-neutral-300 mx-2">·</span>
            <a href="{{ route('login') }}" class="text-[13px] text-neutral-500 hover:text-neutral-700 font-medium">
                Retour à la connexion
            </a>
        </div>
    </div>
</x-layouts.auth>
