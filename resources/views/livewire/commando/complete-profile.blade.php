<div class="bg-amber-500/10 border border-amber-500/40 rounded-2xl p-6 mb-6">
    <div class="flex items-center gap-3 mb-4">
        <div class="w-10 h-10 rounded-xl bg-amber-500/20 flex items-center justify-center">
            <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
        </div>
        <div>
            <h2 class="font-semibold text-amber-200">Complétez votre inscription</h2>
            <p class="text-slate-400 text-sm">Pièce d'identité (CNI, passeport) ou justificatif de domicile. JPG, PNG ou PDF — max 5 Mo.</p>
        </div>
    </div>

    <form wire:submit="submit" class="space-y-5">
        <div>
            <label class="block text-sm font-medium text-slate-300 mb-2">Statut</label>
            <select wire:model="statut_metier"
                    class="w-full h-12 px-4 rounded-xl border border-slate-600 bg-slate-900/80 text-white focus:outline-none focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition-all">
                <option value="">Choisir...</option>
                <option value="etudiant">Étudiant</option>
                <option value="auto_entrepreneur">Auto-entrepreneur</option>
                <option value="salarie">Salarié</option>
                <option value="autre">Autre</option>
            </select>
            @error('statut_metier')
                <p class="mt-1.5 text-sm text-red-400">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-300 mb-2">Pièce d'identité ou justificatif de domicile</label>
            <div class="relative flex items-center">
                <span class="absolute left-4 text-slate-500 pointer-events-none">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"/>
                    </svg>
                </span>
                <input type="file" wire:model="id_document" accept=".jpg,.jpeg,.png,.pdf"
                       class="w-full h-12 pl-12 pr-4 rounded-xl border border-slate-600 bg-slate-900/80 text-white file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-orange-500 file:text-white file:font-medium file:cursor-pointer text-sm text-slate-400 focus:outline-none focus:ring-2 focus:ring-orange-500">
            </div>
            @error('id_document')
                <p class="mt-1.5 text-sm text-red-400">{{ $message }}</p>
            @enderror
        </div>
        <button type="submit"
                class="w-full h-12 rounded-xl font-semibold bg-orange-500 hover:bg-orange-600 text-white transition-all focus:outline-none focus:ring-2 focus:ring-orange-500 focus:ring-offset-2 focus:ring-offset-slate-800">
            Envoyer et demander la validation
        </button>
    </form>
</div>
