<div class="bg-slate-800/50 border border-slate-700 rounded-2xl p-6 sm:p-8 shadow-xl">
    <div class="text-center mb-6">
        <h1 class="text-xl font-bold text-white mb-1">Étape 2 – Vérification</h1>
        <p class="text-slate-400 text-sm">{{ $agent->full_name }} – Envoyez votre pièce d'identité.</p>
    </div>

    <form wire:submit="submit" class="space-y-4">
        <div>
            <label class="block text-sm font-medium text-slate-300 mb-1">Statut</label>
            <select wire:model="statut_metier"
                    class="w-full rounded-xl border border-slate-600 bg-slate-800/80 text-white px-4 py-3 focus:ring-2 focus:ring-orange-500 focus:border-orange-500">
                <option value="">Choisir...</option>
                <option value="etudiant">Étudiant</option>
                <option value="auto_entrepreneur">Auto-entrepreneur</option>
                <option value="salarie">Salarié</option>
                <option value="autre">Autre</option>
            </select>
            @error('statut_metier')
                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
            @enderror
        </div>
        <div>
            <label class="block text-sm font-medium text-slate-300 mb-1">Pièce d'identité (CNI, passeport)</label>
            <input type="file" wire:model="id_document" accept=".jpg,.jpeg,.png,.pdf"
                   class="w-full text-sm text-slate-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-orange-500 file:text-white file:font-medium">
            @error('id_document')
                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
            @enderror
            <p class="mt-1 text-xs text-slate-500">JPG, PNG ou PDF. Max 5 Mo.</p>
        </div>
        <button type="submit" class="w-full py-3 rounded-xl font-semibold bg-orange-500 hover:bg-orange-600 text-white transition-colors">
            Envoyer mon dossier
        </button>
    </form>
</div>
