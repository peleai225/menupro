<div class="max-w-4xl mx-auto py-8 px-4">

    @if(session('success'))
    <div class="mb-4 p-4 bg-emerald-50 border border-emerald-200 text-emerald-700 rounded-xl text-sm">
        {{ session('success') }}
    </div>
    @endif

    <div class="flex items-center justify-between mb-8">
        <div>
            <h1 class="text-2xl font-bold text-neutral-900">Serveurs</h1>
            <p class="text-neutral-500 text-sm mt-1">Chaque serveur s'identifie avec un PIN à 4 chiffres sur sa tablette</p>
        </div>
        @php $waiterUrl = ($restaurant->waiter_token && \Illuminate\Support\Facades\Route::has('waiter.display'))
            ? route('waiter.display', $restaurant->waiter_token)
            : null @endphp
        @if($waiterUrl)
        <a href="{{ $waiterUrl }}" target="_blank"
            class="flex items-center gap-2 px-4 py-2 bg-neutral-900 text-white text-sm font-semibold rounded-xl hover:bg-neutral-800 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            Ouvrir l'interface serveur
        </a>
        @else
        <form method="POST" action="{{ route('restaurant.waiter.generate-token') }}">
            @csrf
            <button type="submit"
                class="flex items-center gap-2 px-4 py-2 bg-neutral-900 text-white text-sm font-semibold rounded-xl hover:bg-neutral-800 transition">
                Générer le lien interface serveur
            </button>
        </form>
        @endif
    </div>

    {{-- Formulaire --}}
    <div class="bg-white rounded-2xl border border-neutral-200 p-6 mb-8">
        <h2 class="font-bold text-neutral-900 mb-4">{{ $editingId ? 'Modifier le serveur' : 'Nouveau serveur' }}</h2>
        <form wire:submit="save" class="grid sm:grid-cols-2 gap-4">
            <div>
                <label class="block text-sm font-medium text-neutral-700 mb-1">Nom *</label>
                <input type="text" wire:model="name" placeholder="Ex: Koffi, Aya, Jean..."
                    class="w-full border border-neutral-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                @error('name') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-neutral-700 mb-1">
                    PIN (4 chiffres) {{ $editingId ? '— laisser vide pour ne pas changer' : '*' }}
                </label>
                <input type="password" wire:model="pin" placeholder="••••" maxlength="4" pattern="[0-9]{4}"
                    class="w-full border border-neutral-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                @error('pin') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>

            @if(!$editingId)
            <div>
                <label class="block text-sm font-medium text-neutral-700 mb-1">Confirmer le PIN *</label>
                <input type="password" wire:model="pinConfirm" placeholder="••••" maxlength="4" pattern="[0-9]{4}"
                    class="w-full border border-neutral-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                @error('pinConfirm') <span class="text-red-500 text-xs mt-1">{{ $message }}</span> @enderror
            </div>
            @endif

            @if($this->spaces->isNotEmpty())
            <div>
                <label class="block text-sm font-medium text-neutral-700 mb-1">Espace assigné (optionnel)</label>
                <select wire:model="spaceId"
                    class="w-full border border-neutral-200 rounded-xl px-4 py-2.5 text-sm focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    <option value="">Tous les espaces</option>
                    @foreach($this->spaces as $space)
                    <option value="{{ $space->id }}">{{ $space->name }}</option>
                    @endforeach
                </select>
            </div>
            @endif

            <div class="flex items-center gap-2">
                <input type="checkbox" wire:model="isActive" id="isActive" class="rounded">
                <label for="isActive" class="text-sm text-neutral-700">Serveur actif</label>
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
                    {{ $editingId ? 'Mettre à jour' : 'Créer le serveur' }}
                </button>
            </div>
        </form>
    </div>

    {{-- Liste des serveurs --}}
    <div class="space-y-3">
        @forelse($this->waiters as $waiter)
        <div class="bg-white rounded-xl border border-neutral-200 px-5 py-4 flex items-center gap-4">
            <div class="w-10 h-10 rounded-full bg-gradient-to-br from-primary-100 to-primary-200 flex items-center justify-center text-primary-700 font-bold shrink-0">
                {{ strtoupper(substr($waiter->name, 0, 1)) }}
            </div>
            <div class="flex-1 min-w-0">
                <div class="flex items-center gap-2 flex-wrap">
                    <span class="font-semibold text-neutral-900">{{ $waiter->name }}</span>
                    @if($waiter->space)
                    <span class="text-xs px-2 py-0.5 rounded-full text-white font-medium" style="background-color: {{ $waiter->space->color }}">
                        {{ $waiter->space->name }}
                    </span>
                    @endif
                    @if(!$waiter->is_active)
                    <span class="text-xs bg-neutral-100 text-neutral-500 px-2 py-0.5 rounded-full">Inactif</span>
                    @endif
                    @if($waiter->isLocked())
                    <span class="text-xs bg-red-100 text-red-600 px-2 py-0.5 rounded-full">PIN bloqué</span>
                    @endif
                </div>
                <p class="text-xs text-neutral-400 mt-0.5">PIN ••••</p>
            </div>
            <div class="flex items-center gap-2 shrink-0">
                <button wire:click="edit({{ $waiter->id }})"
                    class="p-2 text-neutral-500 hover:text-primary-600 hover:bg-primary-50 rounded-lg transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                </button>
                <button wire:click="delete({{ $waiter->id }})" wire:confirm="Supprimer ce serveur ?"
                    class="p-2 text-neutral-500 hover:text-red-600 hover:bg-red-50 rounded-lg transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                </button>
            </div>
        </div>
        @empty
        <div class="text-center py-12 text-neutral-400">
            <p class="text-sm">Aucun serveur créé. Ajoutez votre premier serveur ci-dessus.</p>
        </div>
        @endforelse
    </div>
</div>
