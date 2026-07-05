<div class="mb-5 rounded-xl border border-amber-500/30 bg-amber-500/10 p-4">
    <p class="text-amber-200 text-sm font-medium mb-1">{{ $isResubmit ? "Nouvelle pièce d'identité" : 'Complétez votre profil' }}</p>
    <p class="text-slate-400 text-xs mb-3">Pièce d'identité (CNI, passeport) pour activer votre carte. JPG, PNG ou PDF, max 5 Mo.</p>

    <form wire:submit="submit" class="space-y-3">
        <select wire:model="statut_metier"
                class="w-full h-10 px-3 rounded-lg border border-slate-600 bg-slate-900/60 text-white text-sm focus:ring-1 focus:ring-orange-500 transition">
            <option value="">Statut professionnel...</option>
            <option value="etudiant">Étudiant</option>
            <option value="auto_entrepreneur">Auto-entrepreneur</option>
            <option value="salarie">Salarié</option>
            <option value="autre">Autre</option>
        </select>
        @error('statut_metier') <p class="text-red-400 text-[11px]">{{ $message }}</p> @enderror

        <div class="relative">
            <input type="file" wire:model="id_document" accept=".jpg,.jpeg,.png,.pdf"
                   class="w-full h-10 px-3 rounded-lg border border-slate-600 bg-slate-900/60 text-sm text-slate-400 file:mr-2 file:py-1 file:px-3 file:rounded file:border-0 file:bg-orange-500 file:text-white file:text-xs file:font-medium file:cursor-pointer focus:ring-1 focus:ring-orange-500">
            <span wire:loading wire:target="id_document" class="absolute right-3 top-1/2 -translate-y-1/2">
                <svg class="animate-spin h-4 w-4 text-orange-400" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"/></svg>
            </span>
        </div>
        @error('id_document') <p class="text-red-400 text-[11px]">{{ $message }}</p> @enderror

        <button type="submit" wire:loading.attr="disabled" wire:target="id_document,submit"
                class="w-full h-10 rounded-lg bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium transition disabled:opacity-50 flex items-center justify-center gap-2">
            <span wire:loading.remove wire:target="submit">{{ $isResubmit ? 'Renvoyer' : 'Envoyer' }}</span>
            <span wire:loading wire:target="submit">Envoi...</span>
        </button>
    </form>
</div>
