<div>
    {{-- Indicateur d'étapes --}}
    <div class="flex items-center justify-center gap-3 mb-8">
        <div class="flex items-center gap-2">
            <span class="w-8 h-8 rounded-full bg-emerald-500 text-white text-sm font-bold flex items-center justify-center">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
            </span>
            <span class="text-sm font-medium text-emerald-400 hidden sm:inline">Compte</span>
        </div>
        <div class="w-8 sm:w-12 h-px bg-emerald-500/50"></div>
        <div class="flex items-center gap-2">
            <span class="w-8 h-8 rounded-full bg-orange-500 text-white text-sm font-bold flex items-center justify-center">2</span>
            <span class="text-sm font-medium text-white hidden sm:inline">Vérification</span>
        </div>
        <div class="w-8 sm:w-12 h-px bg-slate-600"></div>
        <div class="flex items-center gap-2">
            <span class="w-8 h-8 rounded-full bg-slate-700 text-slate-400 text-sm font-bold flex items-center justify-center border border-slate-600">3</span>
            <span class="text-sm font-medium text-slate-500 hidden sm:inline">Validation</span>
        </div>
    </div>

    <div class="bg-slate-800/60 border border-slate-700/50 rounded-2xl p-6 sm:p-8 shadow-2xl backdrop-blur-sm">
        <div class="text-center mb-6">
            <div class="inline-flex items-center justify-center w-14 h-14 bg-orange-500/15 rounded-2xl mb-4 border border-orange-500/20">
                <svg class="w-7 h-7 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
            </div>
            <h1 class="text-xl font-bold text-white mb-1">Vérification d'identité</h1>
            <p class="text-slate-400 text-sm">{{ $agent->full_name }} — Envoyez votre pièce d'identité pour valider votre inscription.</p>
        </div>

        <form wire:submit="submit" class="space-y-5">
            <div>
                <label class="block text-sm font-medium text-slate-300 mb-2">Statut professionnel</label>
                <div class="relative">
                    <span class="absolute left-4 top-1/2 -translate-y-1/2 text-slate-500 pointer-events-none">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                    </span>
                    <select wire:model="statut_metier"
                            class="w-full h-12 pl-12 pr-4 rounded-xl border border-slate-600 bg-slate-900/80 text-white focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all appearance-none">
                        <option value="">Choisir...</option>
                        <option value="etudiant">Étudiant</option>
                        <option value="auto_entrepreneur">Auto-entrepreneur</option>
                        <option value="salarie">Salarié</option>
                        <option value="autre">Autre</option>
                    </select>
                </div>
                @error('statut_metier')
                    <p class="mt-1.5 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-slate-300 mb-2">Pièce d'identité (CNI, passeport)</label>
                <div class="relative">
                    <div class="flex items-center">
                        <span class="absolute left-4 text-slate-500 pointer-events-none z-10">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                            </svg>
                        </span>
                        <input type="file" wire:model="id_document" accept=".jpg,.jpeg,.png,.pdf"
                               class="w-full h-12 pl-12 pr-4 rounded-xl border border-slate-600 bg-slate-900/80 text-white file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-orange-500 file:text-white file:font-medium file:cursor-pointer text-sm text-slate-400 focus:outline-none focus:ring-2 focus:ring-orange-500">
                        <span wire:loading wire:target="id_document" class="absolute right-4 flex items-center gap-2 text-orange-400 text-sm">
                            <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        </span>
                    </div>
                </div>
                @error('id_document')
                    <p class="mt-1.5 text-sm text-red-400">{{ $message }}</p>
                @enderror
                <p class="mt-1.5 text-xs text-slate-500">JPG, PNG ou PDF. Max 5 Mo.</p>
            </div>

            <button type="submit"
                    wire:loading.attr="disabled"
                    wire:target="id_document,submit"
                    class="w-full h-12 rounded-xl font-semibold bg-orange-500 hover:bg-orange-600 text-white transition-all focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-slate-800 disabled:opacity-60 disabled:cursor-not-allowed flex items-center justify-center gap-2">
                <span wire:loading.remove wire:target="submit">Envoyer mon dossier</span>
                <span wire:loading wire:target="submit" class="inline-flex items-center gap-2">
                    <svg class="animate-spin h-4 w-4" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                    Envoi en cours...
                </span>
            </button>
        </form>
    </div>
</div>
