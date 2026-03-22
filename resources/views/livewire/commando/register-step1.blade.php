<div>
    {{-- Indicateur d'étapes --}}
    <div class="flex items-center justify-center gap-3 mb-8">
        <div class="flex items-center gap-2">
            <span class="w-8 h-8 rounded-full bg-orange-500 text-white text-sm font-bold flex items-center justify-center">1</span>
            <span class="text-sm font-medium text-white hidden sm:inline">Compte</span>
        </div>
        <div class="w-8 sm:w-12 h-px bg-slate-600"></div>
        <div class="flex items-center gap-2">
            <span class="w-8 h-8 rounded-full bg-slate-700 text-slate-400 text-sm font-bold flex items-center justify-center border border-slate-600">2</span>
            <span class="text-sm font-medium text-slate-500 hidden sm:inline">Vérification</span>
        </div>
        <div class="w-8 sm:w-12 h-px bg-slate-600"></div>
        <div class="flex items-center gap-2">
            <span class="w-8 h-8 rounded-full bg-slate-700 text-slate-400 text-sm font-bold flex items-center justify-center border border-slate-600">3</span>
            <span class="text-sm font-medium text-slate-500 hidden sm:inline">Validation</span>
        </div>
    </div>

    <div class="bg-slate-800/60 border border-slate-700/50 rounded-2xl p-6 sm:p-8 shadow-2xl backdrop-blur-sm">
        <div class="text-center mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 bg-orange-500/15 rounded-2xl mb-4 border border-orange-500/20">
                <svg class="w-7 h-7 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-white mb-2">Devenir agent MenuPro Commando</h1>
            <p class="text-slate-400 text-sm">Créez votre compte. Accédez à votre espace immédiatement.</p>
        </div>

        <form wire:submit="submit" class="space-y-5">
            {{-- Identité : Prénom + Nom --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="first_name" class="block text-sm font-medium text-slate-300 mb-2">Prénom</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 pointer-events-none">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </span>
                        <input type="text" id="first_name" wire:model="first_name"
                               class="w-full h-12 pl-12 pr-4 rounded-xl border border-slate-600 bg-slate-900/80 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all"
                               placeholder="Jean" autocomplete="given-name">
                    </div>
                    @error('first_name')
                        <p class="mt-1.5 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="last_name" class="block text-sm font-medium text-slate-300 mb-2">Nom</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 pointer-events-none">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                        </span>
                        <input type="text" id="last_name" wire:model="last_name"
                               class="w-full h-12 pl-12 pr-4 rounded-xl border border-slate-600 bg-slate-900/80 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all"
                               placeholder="Kouassi" autocomplete="family-name">
                    </div>
                    @error('last_name')
                        <p class="mt-1.5 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Contact : WhatsApp + Ville --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="whatsapp" class="block text-sm font-medium text-slate-300 mb-2">WhatsApp</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 pointer-events-none">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                        </span>
                        <input type="tel" id="whatsapp" wire:model="whatsapp"
                               class="w-full h-12 pl-12 pr-4 rounded-xl border border-slate-600 bg-slate-900/80 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all"
                               placeholder="+225 07 00 00 00 00">
                    </div>
                    @error('whatsapp')
                        <p class="mt-1.5 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="city" class="block text-sm font-medium text-slate-300 mb-2">Ville</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 pointer-events-none">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                        </span>
                        <input type="text" id="city" wire:model="city"
                               class="w-full h-12 pl-12 pr-4 rounded-xl border border-slate-600 bg-slate-900/80 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all"
                               placeholder="Abidjan">
                    </div>
                    @error('city')
                        <p class="mt-1.5 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Email --}}
            <div>
                <label for="email" class="block text-sm font-medium text-slate-300 mb-2">Adresse email</label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 pointer-events-none">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </span>
                    <input type="email" id="email" wire:model="email"
                           class="w-full h-12 pl-12 pr-4 rounded-xl border border-slate-600 bg-slate-900/80 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all"
                           placeholder="vous@exemple.com" autocomplete="email">
                </div>
                @error('email')
                    <p class="mt-1.5 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Mot de passe + Confirmation --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="password" class="block text-sm font-medium text-slate-300 mb-2">Mot de passe</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 pointer-events-none">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                            </svg>
                        </span>
                        <input type="password" id="password" wire:model="password"
                               class="w-full h-12 pl-12 pr-4 rounded-xl border border-slate-600 bg-slate-900/80 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all"
                               placeholder="Min. 8 caractères" autocomplete="new-password">
                    </div>
                    @error('password')
                        <p class="mt-1.5 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <div>
                    <label for="password_confirmation" class="block text-sm font-medium text-slate-300 mb-2">Confirmer</label>
                    <div class="relative">
                        <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 pointer-events-none">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </span>
                        <input type="password" id="password_confirmation" wire:model="password_confirmation"
                               class="w-full h-12 pl-12 pr-4 rounded-xl border border-slate-600 bg-slate-900/80 text-white placeholder-slate-500 focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all"
                               placeholder="Confirmez le mot de passe" autocomplete="new-password">
                    </div>
                </div>
            </div>

            <div class="pt-2">
                <button type="submit"
                        wire:loading.attr="disabled"
                        class="w-full h-12 rounded-xl font-semibold bg-orange-500 hover:bg-orange-600 text-white transition-all focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-slate-800 disabled:opacity-60 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                    <span wire:loading.remove>Créer mon compte</span>
                    <span wire:loading class="inline-flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        Création en cours...
                    </span>
                </button>
            </div>

            <p class="text-center text-slate-500 text-xs pt-2">
                Déjà agent ? <a href="{{ route('login') }}" class="text-orange-400 hover:text-orange-300 transition-colors">Se connecter</a>
            </p>
        </form>
    </div>
</div>
