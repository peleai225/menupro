<x-layouts.app title="Accès Administrateur">
<div class="min-h-screen flex items-center justify-center bg-gray-950 px-4 py-12" x-data="{ loading: false, showPassword: false }">

    {{-- Ambient glow --}}
    <div class="fixed inset-0 pointer-events-none overflow-hidden">
        <div class="absolute -top-40 left-1/2 -translate-x-1/2 w-[600px] h-[400px] bg-orange-500/5 rounded-full blur-[100px]"></div>
        <div class="absolute bottom-0 left-1/2 -translate-x-1/2 w-[400px] h-[300px] bg-orange-600/3 rounded-full blur-[80px]"></div>
    </div>

    <div class="w-full max-w-[380px] relative z-10">

        {{-- Logo --}}
        <div class="flex flex-col items-center mb-10">
            <div class="relative mb-6">
                <div class="absolute inset-0 bg-orange-500/20 rounded-2xl blur-xl scale-150"></div>
                <div class="relative w-14 h-14 rounded-2xl bg-gradient-to-br from-orange-500/90 to-orange-700 flex items-center justify-center shadow-xl">
                    <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
            </div>
            <h1 class="text-xl font-bold text-white tracking-tight">Panneau d'administration</h1>
            <p class="text-gray-600 text-[13px] mt-1.5 font-medium">Accès sécurisé — identifiez-vous</p>
        </div>

        {{-- Error alert --}}
        @if (session('error'))
            <div class="mb-5 p-3.5 bg-red-500/8 border border-red-500/20 rounded-2xl flex items-start gap-3">
                <div class="w-5 h-5 rounded-full bg-red-500/20 flex items-center justify-center flex-shrink-0 mt-0.5">
                    <svg class="w-3 h-3 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                    </svg>
                </div>
                <p class="text-[13px] text-red-400 font-medium leading-snug">{{ session('error') }}</p>
            </div>
        @endif

        {{-- Card --}}
        <div class="bg-gray-900/60 backdrop-blur-2xl border border-white/5 rounded-3xl p-7 shadow-2xl ring-1 ring-inset ring-white/3">

            <form method="POST" action="{{ route('admin.login.post') }}" @submit="loading = true" class="space-y-5">
                @csrf

                {{-- Email --}}
                <div>
                    <label for="login" class="block text-[11px] font-bold text-gray-500 uppercase tracking-widest mb-2.5">
                        Identifiant
                    </label>
                    <div class="relative group">
                        <div class="absolute left-0 top-0 bottom-0 w-11 flex items-center justify-center pointer-events-none z-10">
                            <svg class="w-[17px] h-[17px] text-gray-600 group-focus-within:text-orange-500/70 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                            </svg>
                        </div>
                        <input
                            type="email" id="login" name="login" value="{{ old('login') }}"
                            required autofocus autocomplete="username"
                            class="w-full h-[50px] pl-11 pr-4 bg-gray-800/50 border border-gray-700/50 rounded-2xl text-white text-[14px] transition-all duration-200 focus:outline-none focus:bg-gray-800/80 focus:border-orange-500/40 focus:ring-2 focus:ring-orange-500/10 @error('login') border-red-500/40 bg-red-500/5 @enderror"
                        >
                    </div>
                    @error('login')
                        <p class="mt-2 text-[12px] text-red-400 flex items-center gap-1.5 font-medium">
                            <svg class="w-3 h-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Password --}}
                <div>
                    <label for="password" class="block text-[11px] font-bold text-gray-500 uppercase tracking-widest mb-2.5">
                        Mot de passe
                    </label>
                    <div class="relative group">
                        <div class="absolute left-0 top-0 bottom-0 w-11 flex items-center justify-center pointer-events-none z-10">
                            <svg class="w-[17px] h-[17px] text-gray-600 group-focus-within:text-orange-500/70 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </div>
                        <input
                            :type="showPassword ? 'text' : 'password'"
                            id="password" name="password"
                            required autocomplete="current-password"
                            class="w-full h-[50px] pl-11 pr-11 bg-gray-800/50 border border-gray-700/50 rounded-2xl text-white text-[14px] transition-all duration-200 focus:outline-none focus:bg-gray-800/80 focus:border-orange-500/40 focus:ring-2 focus:ring-orange-500/10 @error('password') border-red-500/40 bg-red-500/5 @enderror"
                        >
                        <button type="button" @click="showPassword = !showPassword"
                                class="absolute right-3 top-1/2 -translate-y-1/2 w-7 h-7 flex items-center justify-center text-gray-600 hover:text-gray-400 rounded-lg transition">
                            <svg x-show="!showPassword" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                            </svg>
                            <svg x-show="showPassword" x-cloak class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                            </svg>
                        </button>
                    </div>
                    @error('password')
                        <p class="mt-2 text-[12px] text-red-400 flex items-center gap-1.5 font-medium">
                            <svg class="w-3 h-3 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                            </svg>
                            {{ $message }}
                        </p>
                    @enderror
                </div>

                {{-- Submit --}}
                <div class="pt-2">
                    <button type="submit"
                            class="w-full h-[50px] bg-gradient-to-r from-orange-500 to-orange-600 hover:from-orange-400 hover:to-orange-500 text-white font-semibold text-[14px] rounded-2xl relative overflow-hidden shadow-lg shadow-orange-500/20 hover:shadow-orange-500/35 active:scale-[0.98] transition-all duration-200 disabled:opacity-50"
                            :disabled="loading">
                        <span :class="{ 'opacity-0': loading }" class="flex items-center justify-center gap-2.5">
                            Connexion
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
                </div>
            </form>
        </div>

        {{-- Back link --}}
        <div class="mt-6 text-center">
            <a href="{{ route('home') }}" class="inline-flex items-center gap-1.5 text-[12px] text-gray-700 hover:text-gray-500 transition-colors">
                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour au site
            </a>
        </div>

        <p class="mt-6 text-center text-[11px] text-gray-800 tracking-wide">
            MENUPRO &mdash; ZONE RESTREINTE
        </p>
    </div>
</div>
</x-layouts.app>
