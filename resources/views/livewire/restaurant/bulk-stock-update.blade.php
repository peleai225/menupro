<div>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <a href="{{ route('restaurant.stock.ingredients.index') }}" class="text-neutral-500 hover:text-neutral-700 text-sm flex items-center gap-1 mb-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Retour au stock
            </a>
            <h1 class="text-2xl font-bold text-neutral-900">Mise à jour en masse</h1>
            <p class="text-neutral-500 mt-1">Mettez à jour toutes les quantités d'un coup, comme un inventaire.</p>
        </div>
    </div>

    <!-- Success Message -->
    @if($showSuccess)
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl text-green-700 flex items-center justify-between">
            <span>{{ $updatedCount }} ingrédient(s) mis à jour avec succès.</span>
            <button wire:click="$set('showSuccess', false)" class="text-green-500 hover:text-green-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    @endif

    <!-- Reason + Save -->
    <div class="card p-4 mb-6">
        <div class="flex flex-col sm:flex-row items-start sm:items-center gap-4">
            <div class="flex-1">
                <label class="block text-sm font-medium text-neutral-700 mb-1">Raison de la mise à jour</label>
                <div class="flex flex-wrap gap-2">
                    @foreach(['Inventaire physique', 'Réception marchandise', 'Correction', 'Fin de journée'] as $r)
                        <button type="button" wire:click="$set('reason', '{{ $r }}')"
                                class="px-3 py-1.5 text-sm rounded-full border transition-colors {{ $reason === $r ? 'bg-primary-100 border-primary-300 text-primary-700 font-medium' : 'border-neutral-200 text-neutral-600 hover:border-primary-200' }}">
                            {{ $r }}
                        </button>
                    @endforeach
                </div>
            </div>
            <button wire:click="saveAll"
                    wire:loading.attr="disabled"
                    class="btn btn-primary px-6 py-3 flex items-center gap-2 shrink-0 {{ $this->changedCount === 0 ? 'opacity-50 cursor-not-allowed' : '' }}"
                    {{ $this->changedCount === 0 ? 'disabled' : '' }}>
                <span wire:loading.remove wire:target="saveAll">
                    Enregistrer ({{ $this->changedCount }} modif.)
                </span>
                <span wire:loading wire:target="saveAll" class="flex items-center gap-2">
                    <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                    </svg>
                    Enregistrement...
                </span>
            </button>
        </div>
    </div>

    <!-- Stock Table -->
    <div class="card overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="bg-neutral-50 border-b border-neutral-200">
                        <th class="text-left text-xs font-semibold text-neutral-500 uppercase tracking-wider px-4 py-3">Ingrédient</th>
                        <th class="text-left text-xs font-semibold text-neutral-500 uppercase tracking-wider px-4 py-3">Catégorie</th>
                        <th class="text-center text-xs font-semibold text-neutral-500 uppercase tracking-wider px-4 py-3">Stock actuel</th>
                        <th class="text-center text-xs font-semibold text-neutral-500 uppercase tracking-wider px-4 py-3">Seuil min</th>
                        <th class="text-center text-xs font-semibold text-neutral-500 uppercase tracking-wider px-4 py-3 bg-primary-50">Nouvelle qté</th>
                        <th class="text-center text-xs font-semibold text-neutral-500 uppercase tracking-wider px-4 py-3">Statut</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100">
                    @forelse($quantities as $id => $data)
                        <tr class="{{ $data['changed'] ? 'bg-amber-50' : '' }} hover:bg-neutral-50 transition-colors">
                            <td class="px-4 py-3">
                                <span class="font-medium text-neutral-900 text-sm">{{ $data['name'] }}</span>
                                <span class="text-xs text-neutral-400 ml-1">({{ $data['unit'] }})</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-sm text-neutral-500">{{ $data['category'] }}</span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="text-sm font-medium text-neutral-700">{{ number_format($data['current'], 1) }}</span>
                            </td>
                            <td class="px-4 py-3 text-center">
                                <span class="text-sm text-neutral-400">{{ number_format($data['min'], 1) }}</span>
                            </td>
                            <td class="px-4 py-3 text-center bg-primary-50/50">
                                <input type="number"
                                       wire:model.blur="quantities.{{ $id }}.new_qty"
                                       step="0.1"
                                       min="0"
                                       class="w-24 mx-auto text-center text-sm border rounded-lg px-2 py-1.5 focus:ring-2 focus:ring-primary-500 focus:border-primary-500 {{ $data['changed'] ? 'border-amber-400 bg-amber-50 font-bold text-amber-800' : 'border-neutral-200' }}">
                            </td>
                            <td class="px-4 py-3 text-center">
                                @php $qty = (float) $data['new_qty']; @endphp
                                @if($qty <= 0)
                                    <span class="inline-block w-3 h-3 rounded-full bg-red-500" title="Rupture"></span>
                                @elseif($data['min'] > 0 && $qty <= $data['min'])
                                    <span class="inline-block w-3 h-3 rounded-full bg-yellow-400" title="Stock faible"></span>
                                @else
                                    <span class="inline-block w-3 h-3 rounded-full bg-green-500" title="En stock"></span>
                                @endif
                                @if($data['changed'])
                                    <span class="ml-1 text-xs text-amber-600 font-medium">modifié</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-4 py-12 text-center text-neutral-400">
                                <p class="text-lg mb-2">Aucun ingrédient</p>
                                <p class="text-sm">Créez d'abord des ingrédients ou importez-en depuis la bibliothèque.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
