<div>
    {{-- En-tête --}}
    <div class="mb-8 text-center">
        <div class="w-14 h-14 rounded-2xl bg-orange-500/15 flex items-center justify-center mx-auto mb-4">
            <svg class="w-7 h-7 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
        </div>
        <h1 class="text-xl font-bold text-white">Devenir agent MenuPro</h1>
        <p class="text-slate-400 text-sm mt-1.5">Parrainez des restaurants, gagnez des commissions.</p>
    </div>

    {{-- Formulaire --}}
    <form wire:submit="submit" class="space-y-4">
        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-slate-400 text-xs font-medium mb-1.5">Prénom</label>
                <input type="text" wire:model="first_name" placeholder="ex: Kouassi"
                       class="w-full h-11 px-4 rounded-xl border border-slate-700 bg-slate-800/60 text-white text-sm placeholder-slate-600 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition"
                       autocomplete="given-name">
                @error('first_name') <p class="text-red-400 text-[11px] mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-slate-400 text-xs font-medium mb-1.5">Nom</label>
                <input type="text" wire:model="last_name" placeholder="ex: Brou"
                       class="w-full h-11 px-4 rounded-xl border border-slate-700 bg-slate-800/60 text-white text-sm placeholder-slate-600 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition"
                       autocomplete="family-name">
                @error('last_name') <p class="text-red-400 text-[11px] mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label class="block text-slate-400 text-xs font-medium mb-1.5">Numéro WhatsApp</label>
            <input type="tel" wire:model="whatsapp" placeholder="07 00 00 00 00"
                   class="w-full h-11 px-4 rounded-xl border border-slate-700 bg-slate-800/60 text-white text-sm placeholder-slate-600 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition">
            @error('whatsapp') <p class="text-red-400 text-[11px] mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-slate-400 text-xs font-medium mb-1.5">Ville</label>
            <input type="text" wire:model="city" placeholder="ex: Abidjan"
                   class="w-full h-11 px-4 rounded-xl border border-slate-700 bg-slate-800/60 text-white text-sm placeholder-slate-600 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition">
            @error('city') <p class="text-red-400 text-[11px] mt-1">{{ $message }}</p> @enderror
        </div>

        <div>
            <label class="block text-slate-400 text-xs font-medium mb-1.5">Adresse email</label>
            <input type="email" wire:model="email" placeholder="votre@email.com"
                   class="w-full h-11 px-4 rounded-xl border border-slate-700 bg-slate-800/60 text-white text-sm placeholder-slate-600 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition"
                   autocomplete="email">
            @error('email') <p class="text-red-400 text-[11px] mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-2 gap-3">
            <div>
                <label class="block text-slate-400 text-xs font-medium mb-1.5">Mot de passe</label>
                <input type="password" wire:model="password" placeholder="8 caractères min."
                       class="w-full h-11 px-4 rounded-xl border border-slate-700 bg-slate-800/60 text-white text-sm placeholder-slate-600 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition"
                       autocomplete="new-password">
                @error('password') <p class="text-red-400 text-[11px] mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-slate-400 text-xs font-medium mb-1.5">Confirmation</label>
                <input type="password" wire:model="password_confirmation" placeholder="Répéter"
                       class="w-full h-11 px-4 rounded-xl border border-slate-700 bg-slate-800/60 text-white text-sm placeholder-slate-600 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition"
                       autocomplete="new-password">
            </div>
        </div>

        <button type="submit" wire:loading.attr="disabled"
                class="w-full h-12 rounded-xl font-bold bg-orange-500 hover:bg-orange-600 active:bg-orange-700 text-white text-sm transition disabled:opacity-60 flex items-center justify-center gap-2 mt-2">
            <span wire:loading.remove>Créer mon compte</span>
            <span wire:loading class="inline-flex items-center gap-2">
                <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                </svg>
                Création du compte...
            </span>
        </button>
    </form>

    <p class="text-center text-slate-500 text-sm mt-6">
        Déjà agent ?
        <a href="{{ route('login') }}" class="text-orange-400 hover:text-orange-300 font-medium transition">Se connecter</a>
    </p>

    {{-- Avantages --}}
    <div class="mt-8 pt-6 border-t border-slate-800">
        <div class="grid grid-cols-3 gap-3 text-center">
            <div class="space-y-2">
                <div class="w-10 h-10 rounded-2xl bg-orange-500/10 flex items-center justify-center mx-auto">
                    <svg class="w-5 h-5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <p class="text-white text-xs font-semibold">Commission</p>
                <p class="text-slate-500 text-[11px]">Par restaurant signé</p>
            </div>
            <div class="space-y-2">
                <div class="w-10 h-10 rounded-2xl bg-sky-500/10 flex items-center justify-center mx-auto">
                    <svg class="w-5 h-5 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                </div>
                <p class="text-white text-xs font-semibold">Lien unique</p>
                <p class="text-slate-500 text-[11px]">Suivi automatique</p>
            </div>
            <div class="space-y-2">
                <div class="w-10 h-10 rounded-2xl bg-emerald-500/10 flex items-center justify-center mx-auto">
                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <p class="text-white text-xs font-semibold">Badge officiel</p>
                <p class="text-slate-500 text-[11px]">PDF téléchargeable</p>
            </div>
        </div>
    </div>
</div>
