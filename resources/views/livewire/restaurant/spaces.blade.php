<div class="max-w-4xl mx-auto py-8 px-4">

    @if(session('success'))
    <div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm">
        {{ session('success') }}
    </div>
    @endif

    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-neutral-900">Espaces</h1>
            <p class="text-neutral-500 text-sm mt-1">Gérez les espaces de votre établissement (VIP, Salle, Bar...)</p>
        </div>
    </div>

    {{-- Formulaire création/édition --}}
    <div class="bg-white rounded-2xl border border-neutral-200 p-6 mb-8">
        <h2 class="font-bold text-neutral-900 mb-4">
            {{ $editingId ? 'Modifier l\'espace' : 'Nouvel espace' }}
        </h2>
        <form wire:submit="save" class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-neutral-700 mb-1">Nom *</label>
                <input type="text" wire:model="name" placeholder="Ex: VIP, Salle, Bar, Terrasse..."
                    class="w-full border border-neutral-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                @error('name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-neutral-700 mb-1">Couleur (code cuisine)</label>
                <div class="flex items-center gap-3">
                    <input type="color" wire:model="color"
                        class="h-10 w-16 rounded-lg border border-neutral-200 cursor-pointer">
                    <span class="text-sm text-neutral-500">{{ $color }}</span>
                </div>
            </div>

            <div class="sm:col-span-2">
                <label class="block text-sm font-medium text-neutral-700 mb-1">Description (optionnel)</label>
                <input type="text" wire:model="description" placeholder="Description courte..."
                    class="w-full border border-neutral-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent">
            </div>

            <div class="flex items-center gap-2">
                <input type="checkbox" wire:model="is_active" id="is_active" class="rounded">
                <label for="is_active" class="text-sm text-neutral-700">Espace actif</label>
            </div>

            <div class="flex gap-3 justify-end sm:col-span-2">
                @if($editingId)
                <button type="button" wire:click="cancelEdit"
                    class="px-5 py-2.5 text-sm font-medium text-neutral-700 bg-neutral-100 rounded-xl hover:bg-neutral-200 transition">
                    Annuler
                </button>
                @endif
                <button type="submit"
                    class="px-6 py-2.5 text-sm font-bold bg-primary-600 text-white rounded-xl hover:bg-primary-700 transition">
                    {{ $editingId ? 'Mettre à jour' : 'Créer l\'espace' }}
                </button>
            </div>
        </form>
    </div>

    {{-- Liste des espaces --}}
    <div class="space-y-3">
        @forelse($this->spaces as $space)
        <div class="bg-white rounded-xl border border-neutral-200 px-5 py-4 flex items-center gap-4">
            <div class="w-4 h-4 rounded-full shrink-0" style="background-color: {{ $space->color }}"></div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2">
                    <span class="font-semibold text-neutral-900">{{ $space->name }}</span>
                    @if(!$space->is_active)
                    <span class="text-xs bg-neutral-100 text-neutral-500 px-2 py-0.5 rounded-full">Inactif</span>
                    @endif
                </div>
                @if($space->description)
                <p class="text-sm text-neutral-500 mt-0.5">{{ $space->description }}</p>
                @endif
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <button wire:click="edit({{ $space->id }})"
                    class="p-2 text-neutral-500 hover:text-primary-600 hover:bg-primary-50 rounded-lg transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </button>
                <button wire:click="delete({{ $space->id }})" wire:confirm="Supprimer cet espace ?"
                    class="p-2 text-neutral-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            </div>
        </div>
        @empty
        <div class="text-center py-12 text-neutral-400">
            <p class="text-sm">Aucun espace créé. Ajoutez votre premier espace ci-dessus.</p>
        </div>
        @endforelse
    </div>
</div>
