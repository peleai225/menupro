<div>
    {{-- En-tête --}}
    <div class="mb-8 text-center">
        <div class="w-14 h-14 rounded-2xl bg-orange-500/15 flex items-center justify-center mx-auto mb-4">
            <svg class="w-7 h-7 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
        </div>
        <h1 class="text-xl font-bold text-white">Devenir agent MenuPro</h1>
        <p class="text-slate-400 text-sm mt-1.5">Inscrivez-vous en 30 secondes. Parrainez des restaurants, gagnez des commissions.</p>
    </div>

    {{-- Formulaire simplifié --}}
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
            <div class="relative">
                <span class="absolute left-3 top-1/2 -translate-y-1/2 text-slate-500">
                    <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                </span>
                <input type="tel" wire:model="whatsapp" placeholder="07 00 00 00 00"
                       class="w-full h-11 pl-10 pr-4 rounded-xl border border-slate-700 bg-slate-800/60 text-white text-sm placeholder-slate-600 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition">
            </div>
            @error('whatsapp') <p class="text-red-400 text-[11px] mt-1">{{ $message }}</p> @enderror
            <p class="text-slate-600 text-[11px] mt-1">C'est votre identifiant de connexion et de contact.</p>
        </div>

        <div>
            <label class="block text-slate-400 text-xs font-medium mb-1.5">Ville</label>
            <input type="text" wire:model="city" placeholder="ex: Abidjan, Daloa, Bouaké..."
                   class="w-full h-11 px-4 rounded-xl border border-slate-700 bg-slate-800/60 text-white text-sm placeholder-slate-600 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition">
            @error('city') <p class="text-red-400 text-[11px] mt-1">{{ $message }}</p> @enderror
        </div>

        <button type="submit" wire:loading.attr="disabled"
                class="w-full h-12 rounded-xl font-bold bg-orange-500 hover:bg-orange-600 active:bg-orange-700 text-white text-sm transition disabled:opacity-60 flex items-center justify-center gap-2 mt-2">
            <span wire:loading.remove>M'inscrire comme agent</span>
            <span wire:loading class="inline-flex items-center gap-2">
                <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/>
                </svg>
                Inscription...
            </span>
        </button>
    </form>

    <p class="text-center text-slate-500 text-sm mt-6">
        Déjà agent ?
        <a href="{{ route('login') }}" class="text-orange-400 hover:text-orange-300 font-medium transition">Se connecter</a>
    </p>

    {{-- Avantages --}}
    <div class="mt-8 pt-6 border-t border-slate-800">
        <p class="text-center text-slate-500 text-xs mb-4">Après validation par l'équipe, vous recevrez :</p>
        <div class="grid grid-cols-3 gap-3 text-center">
            <div class="space-y-2">
                <div class="w-10 h-10 rounded-2xl bg-orange-500/10 flex items-center justify-center mx-auto">
                    <svg class="w-5 h-5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <p class="text-white text-xs font-semibold">Commissions</p>
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
                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4z"/></svg>
                </div>
                <p class="text-white text-xs font-semibold">Badge PDF</p>
                <p class="text-slate-500 text-[11px]">Téléchargeable</p>
            </div>
        </div>
    </div>
</div>
