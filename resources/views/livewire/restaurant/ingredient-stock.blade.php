<div>
    <!-- Flash -->
    @if(session('stock_success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
             x-transition:leave="transition ease-in duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
             class="mb-6 p-4 bg-secondary-50 border border-secondary-200 rounded-xl text-secondary-700 flex items-center justify-between">
            <span>{{ session('stock_success') }}</span>
            <button @click="show = false" class="text-secondary-500 hover:text-secondary-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    @endif

    <!-- Current Stock Display -->
    <div class="card p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm text-neutral-500">Stock actuel</p>
                <p class="text-4xl font-bold text-neutral-900 mt-1">
                    {{ number_format($ingredient->current_quantity, 2) }}
                    <span class="text-lg font-normal text-neutral-500">{{ $ingredient->unit->label() }}</span>
                </p>
            </div>
            @if($ingredient->current_quantity <= 0)
                <span class="badge bg-red-100 text-red-700 text-sm px-3 py-1">Rupture de stock</span>
            @elseif($ingredient->current_quantity <= $ingredient->min_quantity)
                <span class="badge bg-yellow-100 text-yellow-700 text-sm px-3 py-1">Stock faible</span>
            @else
                <span class="badge badge-success text-sm px-3 py-1">En stock</span>
            @endif
        </div>
        @if($ingredient->max_quantity)
            <div class="mt-4">
                <div class="flex justify-between text-xs text-neutral-500 mb-1">
                    <span>{{ number_format($ingredient->min_quantity, 2) }} (min)</span>
                    <span>{{ number_format($ingredient->max_quantity, 2) }} (max)</span>
                </div>
                <div class="h-2 bg-neutral-200 rounded-full overflow-hidden">
                    @php $pct = min(100, ($ingredient->current_quantity / $ingredient->max_quantity) * 100); @endphp
                    <div class="h-full rounded-full transition-all duration-500 {{ $pct <= 20 ? 'bg-red-500' : ($pct <= 50 ? 'bg-yellow-400' : 'bg-secondary-500') }}"
                         style="width: {{ $pct }}%"></div>
                </div>
            </div>
        @endif
    </div>

    <!-- Operation Tabs -->
    <div class="card mb-6">
        <!-- Tab buttons -->
        <div class="flex border-b border-neutral-200">
            <button wire:click="$set('activeTab', 'entry')"
                    class="flex-1 py-3 px-4 text-sm font-medium transition-colors {{ $activeTab === 'entry' ? 'text-secondary-600 border-b-2 border-secondary-500 bg-secondary-50' : 'text-neutral-500 hover:text-neutral-700' }}">
                <span class="flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Entrée
                </span>
            </button>
            <button wire:click="$set('activeTab', 'exit')"
                    class="flex-1 py-3 px-4 text-sm font-medium transition-colors {{ $activeTab === 'exit' ? 'text-red-600 border-b-2 border-red-500 bg-red-50' : 'text-neutral-500 hover:text-neutral-700' }}">
                <span class="flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                    </svg>
                    Sortie
                </span>
            </button>
            <button wire:click="$set('activeTab', 'adjustment')"
                    class="flex-1 py-3 px-4 text-sm font-medium transition-colors {{ $activeTab === 'adjustment' ? 'text-accent-600 border-b-2 border-accent-500 bg-accent-50' : 'text-neutral-500 hover:text-neutral-700' }}">
                <span class="flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Ajustement
                </span>
            </button>
            <button wire:click="$set('activeTab', 'waste')"
                    class="flex-1 py-3 px-4 text-sm font-medium transition-colors {{ $activeTab === 'waste' ? 'text-yellow-600 border-b-2 border-yellow-500 bg-yellow-50' : 'text-neutral-500 hover:text-neutral-700' }}">
                <span class="flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Perte
                </span>
            </button>
        </div>

        <!-- Tab content -->
        <div class="p-6">

            {{-- ENTRY --}}
            @if($activeTab === 'entry')
                <form wire:submit="addStock" class="space-y-4">
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-1">Quantité *</label>
                            <input type="number" wire:model="entryQuantity" step="0.01" min="0.01"
                                   placeholder="Ex: 10" class="input" required>
                            @error('entryQuantity') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-1">Coût unitaire (FCFA)</label>
                            <input type="number" wire:model="entryUnitCost" step="1" min="0" class="input">
                        </div>
                    </div>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-1">Fournisseur</label>
                            <select wire:model="entrySupplierId" class="input">
                                <option value="">Aucun</option>
                                @foreach($this->suppliers as $supplier)
                                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-1">Note</label>
                            <input type="text" wire:model="entryReason" class="input" placeholder="Ex: Livraison hebdomadaire">
                        </div>
                    </div>
                    <button type="submit" class="btn btn-secondary w-full" wire:loading.attr="disabled" wire:target="addStock">
                        <span wire:loading.remove wire:target="addStock">Ajouter au stock</span>
                        <span wire:loading wire:target="addStock" class="flex items-center justify-center gap-2">
                            <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            Enregistrement...
                        </span>
                    </button>
                </form>
            @endif

            {{-- EXIT --}}
            @if($activeTab === 'exit')
                <form wire:submit="removeStock" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 mb-1">Quantité *</label>
                        <input type="number" wire:model="exitQuantity" step="0.01" min="0.01"
                               max="{{ $ingredient->current_quantity }}" placeholder="Ex: 2.5" class="input" required>
                        @error('exitQuantity') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        <p class="text-xs text-neutral-400 mt-1">Stock disponible : {{ number_format($ingredient->current_quantity, 2) }} {{ $ingredient->unit->shortLabel() }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 mb-1">Raison *</label>
                        <input type="text" wire:model="exitReason" class="input" placeholder="Ex: Utilisation cuisine" required>
                        @error('exitReason') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <button type="submit" class="btn w-full bg-red-500 text-white hover:bg-red-600" wire:loading.attr="disabled" wire:target="removeStock">
                        <span wire:loading.remove wire:target="removeStock">Retirer du stock</span>
                        <span wire:loading wire:target="removeStock" class="flex items-center justify-center gap-2">
                            <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            Enregistrement...
                        </span>
                    </button>
                </form>
            @endif

            {{-- ADJUSTMENT --}}
            @if($activeTab === 'adjustment')
                <form wire:submit="adjustStock" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 mb-1">Nouvelle quantité *</label>
                        <input type="number" wire:model="adjustQuantity" step="0.01" min="0" class="input" required>
                        @error('adjustQuantity') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                        <p class="text-xs text-neutral-400 mt-1">Quantité actuelle : {{ number_format($ingredient->current_quantity, 2) }} {{ $ingredient->unit->shortLabel() }}</p>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 mb-1">Raison *</label>
                        <input type="text" wire:model="adjustReason" class="input" placeholder="Ex: Inventaire physique" required>
                        @error('adjustReason') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <button type="submit" class="btn btn-outline w-full" wire:loading.attr="disabled" wire:target="adjustStock">
                        <span wire:loading.remove wire:target="adjustStock">Ajuster le stock</span>
                        <span wire:loading wire:target="adjustStock" class="flex items-center justify-center gap-2">
                            <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            Enregistrement...
                        </span>
                    </button>
                </form>
            @endif

            {{-- WASTE --}}
            @if($activeTab === 'waste')
                <form wire:submit="recordWaste" class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 mb-1">Quantité perdue *</label>
                        <input type="number" wire:model="wasteQuantity" step="0.01" min="0.01"
                               max="{{ $ingredient->current_quantity }}" placeholder="Ex: 1" class="input" required>
                        @error('wasteQuantity') <p class="text-red-500 text-xs mt-1">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 mb-1">Raison</label>
                        <input type="text" wire:model="wasteReason" class="input" placeholder="Ex: Périmé, cassé...">
                    </div>
                    <button type="submit" class="btn w-full bg-yellow-500 text-white hover:bg-yellow-600" wire:loading.attr="disabled" wire:target="recordWaste">
                        <span wire:loading.remove wire:target="recordWaste">Enregistrer la perte</span>
                        <span wire:loading wire:target="recordWaste" class="flex items-center justify-center gap-2">
                            <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                            </svg>
                            Enregistrement...
                        </span>
                    </button>
                </form>
            @endif
        </div>
    </div>

    <!-- Recent Movements -->
    <div class="card">
        <div class="p-6 border-b border-neutral-100 flex items-center justify-between">
            <h2 class="text-lg font-bold text-neutral-900">Historique récent</h2>
            <a href="{{ route('restaurant.stock.ingredients.movements', $ingredient) }}"
               class="text-sm text-primary-600 hover:text-primary-700 font-medium">
                Voir tout
            </a>
        </div>
        <div class="divide-y divide-neutral-100">
            @forelse($this->recentMovements as $movement)
                @php
                    $typeColors = [
                        'entry' => 'bg-secondary-100 text-secondary-600',
                        'exit_manual' => 'bg-red-100 text-red-600',
                        'exit' => 'bg-red-100 text-red-600',
                        'adjustment' => 'bg-accent-100 text-accent-600',
                        'exit_waste' => 'bg-yellow-100 text-yellow-600',
                        'waste' => 'bg-yellow-100 text-yellow-600',
                    ];
                    $typeColor = $typeColors[$movement->type->value] ?? 'bg-neutral-100 text-neutral-600';
                @endphp
                <div class="p-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <div class="w-9 h-9 rounded-xl flex items-center justify-center {{ $typeColor }}">
                            @if(str_starts_with($movement->type->value, 'entry'))
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                            @elseif(str_starts_with($movement->type->value, 'exit'))
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/></svg>
                            @elseif($movement->type->value === 'adjustment')
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            @else
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            @endif
                        </div>
                        <div>
                            <p class="text-sm font-medium text-neutral-900">{{ $movement->type->label() }}</p>
                            <p class="text-xs text-neutral-500">{{ $movement->reason ?? '-' }} · {{ $movement->created_at->diffForHumans() }}</p>
                        </div>
                    </div>
                    <p class="font-semibold text-sm {{ $movement->quantity > 0 ? 'text-secondary-600' : 'text-red-600' }}">
                        {{ $movement->quantity > 0 ? '+' : '' }}{{ number_format($movement->quantity, 2) }}
                        {{ $ingredient->unit->shortLabel() }}
                    </p>
                </div>
            @empty
                <div class="p-8 text-center text-neutral-500 text-sm">Aucun mouvement enregistré</div>
            @endforelse
        </div>
    </div>
</div>
