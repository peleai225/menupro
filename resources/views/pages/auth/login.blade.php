<x-layouts.auth title="Connexion">
    @php
        try { $authAppName = \App\Models\SystemSetting::get('app_name', config('app.name', 'MenuPro')); } catch (\Throwable $e) { $authAppName = config('app.name', 'MenuPro'); }
    @endphp
    <div class="animate-fade-in">
        <!-- Success Message -->
        @if (session('success'))
            <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-start gap-3 shadow-sm">
                <div class="w-8 h-8 bg-emerald-100 rounded-full flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <p class="text-sm text-emerald-700 pt-1 font-medium">{{ session('success') }}</p>
            </div>
        @endif

        <!-- Header -->
        <div class="text-center lg:text-left mb-8 sm:mb-10">
            <div class="inline-flex items-center gap-2 px-3.5 py-1.5 bg-gradient-to-r from-primary-50 to-orange-50 text-primary-600 rounded-full text-xs font-bold mb-4 tracking-wide uppercase border border-primary-100">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
                Espace restaurateur
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold text-neutral-900 tracking-tight">
                Bon retour parmi nous !
            </h1>
            <p class="text-neutral-500 mt-2 text-sm sm:text-base">Connectez-vous pour piloter votre restaurant.</p>
        </div>

        <form method="POST" action="{{ route('login.post') }}" class="space-y-5" x-data="{ loading: false, showPassword: false }" @submit="loading = true">
            @csrf

            <!-- Email ou WhatsApp -->
            <div>
                <label for="login" class="block text-sm font-semibold text-neutral-700 mb-2">
                    Email ou numero WhatsApp
                </label>
                <div class="relative group">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-neutral-400 group-focus-within:text-primary-500 transition-colors pointer-events-none">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                    </span>
                    <input
                        type="text"
                        id="login"
                        name="login"
                        value="{{ old('login') }}"
                        placeholder="vous@email.com ou 07 00 00 00 00"
                        required
                        autofocus
                        autocomplete="username"
                        class="w-full h-13 pl-12 pr-4 bg-white border-2 border-neutral-200 rounded-xl text-neutral-900 placeholder:text-neutral-400 transition-all duration-200 focus:outline-none focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 @error('login') border-red-400 bg-red-50/50 focus:ring-red-500/10 focus:border-red-500 @enderror"
                    >
                </div>
                @error('login')
                    <p class="mt-2 text-sm text-red-600 flex items-center gap-1.5">
                        <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <!-- Password -->
            <div>
                <div class="flex items-center justify-between mb-2">
                    <label for="password" class="block text-sm font-semibold text-neutral-700">
                        Mot de passe
                    </label>
                    <a href="{{ route('password.request') }}" class="text-xs text-primary-600 hover:text-primary-700 font-semibold hover:underline underline-offset-2 transition-colors">
                        Oublie ?
                    </a>
                </div>
                <div class="relative group">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-neutral-400 group-focus-within:text-primary-500 transition-colors pointer-events-none">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                        </svg>
                    </span>
                    <input
                        :type="showPassword ? 'text' : 'password'"
                        id="password"
                        name="password"
                        placeholder="••••••••"
                        required
                        autocomplete="current-password"
                        class="w-full h-13 pl-12 pr-12 bg-white border-2 border-neutral-200 rounded-xl text-neutral-900 placeholder:text-neutral-400 transition-all duration-200 focus:outline-none focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 @error('password') border-red-400 bg-red-50/50 focus:ring-red-500/10 focus:border-red-500 @enderror"
                    >
                    <button
                        type="button"
                        @click="showPassword = !showPassword"
                        class="absolute right-4 top-1/2 -translate-y-1/2 text-neutral-400 hover:text-neutral-600 transition-colors p-1 rounded-lg hover:bg-neutral-100"
                        :aria-label="showPassword ? 'Masquer le mot de passe' : 'Afficher le mot de passe'"
                    >
                        <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        <svg x-show="showPassword" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                        </svg>
                    </button>
                </div>
                @error('password')
                    <p class="mt-2 text-sm text-red-600 flex items-center gap-1.5">
                        <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <!-- Remember Me -->
            <label for="remember" class="flex items-center gap-3 p-3 -mx-3 rounded-xl hover:bg-neutral-50 transition-colors cursor-pointer select-none group">
                <input
                    type="checkbox"
                    id="remember"
                    name="remember"
                    class="w-4.5 h-4.5 rounded-md border-neutral-300 text-primary-600 focus:ring-primary-500 focus:ring-offset-0 transition-colors"
                    {{ old('remember') ? 'checked' : '' }}
                >
                <span class="text-sm text-neutral-600 group-hover:text-neutral-800 transition-colors">Se souvenir de moi pendant 30 jours</span>
            </label>

            <!-- Submit -->
            <button
                type="submit"
                class="btn btn-primary w-full h-13 text-base font-semibold relative overflow-hidden shadow-lg shadow-primary-500/25 hover:shadow-primary-500/40 hover:scale-[1.01] active:scale-[0.99] transition-all duration-200"
                :disabled="loading"
            >
                <span :class="{ 'opacity-0': loading }" class="flex items-center justify-center gap-2">
                    Se connecter
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                    </svg>
                </span>
                <span x-show="loading" x-cloak class="absolute inset-0 flex items-center justify-center">
                    <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </span>
            </button>
        </form>

        <!-- Divider -->
        <div class="relative my-8">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-neutral-200"></div>
            </div>
            <div class="relative flex justify-center text-xs sm:text-sm">
                <span class="px-4 bg-white text-neutral-400 font-medium">Nouveau sur {{ $authAppName }} ?</span>
            </div>
        </div>

        <!-- Register CTA -->
        <a href="{{ route('register') }}" class="flex items-center justify-center gap-2.5 w-full h-13 border-2 border-neutral-200 rounded-xl text-neutral-700 font-semibold hover:border-primary-400 hover:text-primary-600 hover:bg-primary-50/50 hover:shadow-sm active:scale-[0.99] transition-all duration-200 group">
            <svg class="w-5 h-5 text-neutral-400 group-hover:text-primary-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Creer mon restaurant gratuitement
        </a>

        <!-- Trust Indicators -->
        <div class="mt-8 pt-6 border-t border-neutral-100">
            <div class="flex items-center justify-center gap-6 text-xs text-neutral-400">
                <span class="flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                    </svg>
                    <span class="text-neutral-500">SSL securise</span>
                </span>
                <span class="flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    <span class="text-neutral-500">Donnees protegees</span>
                </span>
                <span class="flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    <span class="text-neutral-500">Activation rapide</span>
                </span>
            </div>
        </div>

        <!-- Help Text -->
        <p class="text-center text-sm text-neutral-500 mt-5">
            Besoin d'aide ?
            <a href="mailto:support@menupro.ci" class="text-primary-600 hover:text-primary-700 font-medium hover:underline underline-offset-2 transition-colors">
                Contactez-nous
            </a>
        </p>
    </div>
</x-layouts.auth>
