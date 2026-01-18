<x-layouts.admin-restaurant title="Détail ingrédient">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <a href="{{ route('restaurant.stock.ingredients.index') }}" class="text-neutral-500 hover:text-neutral-700 text-sm flex items-center gap-1 mb-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Retour au stock
            </a>
            <h1 class="text-2xl font-bold text-neutral-900">{{ $ingredient->name }}</h1>
            @if($ingredient->sku)
                <p class="text-neutral-500 mt-1">SKU: {{ $ingredient->sku }}</p>
            @endif
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('restaurant.stock.ingredients.edit', $ingredient) }}" class="btn btn-outline">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                </svg>
                Modifier
            </a>
        </div>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="mb-6 p-4 bg-secondary-50 border border-secondary-200 rounded-xl text-secondary-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Stock Info Card -->
            <div class="card p-6">
                <h2 class="text-lg font-bold text-neutral-900 mb-6">Informations du stock</h2>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                    <div>
                        <p class="text-sm text-neutral-500">Quantité actuelle</p>
                        <p class="text-2xl font-bold text-neutral-900 mt-1">
                            {{ number_format($ingredient->current_quantity, 2) }}
                            <span class="text-sm font-normal text-neutral-500">{{ $ingredient->unit->label() ?? '' }}</span>
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-neutral-500">Seuil minimum</p>
                        <p class="text-2xl font-bold text-neutral-900 mt-1">
                            {{ number_format($ingredient->min_quantity, 2) }}
                            <span class="text-sm font-normal text-neutral-500">{{ $ingredient->unit->label() ?? '' }}</span>
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-neutral-500">Coût unitaire</p>
                        <p class="text-2xl font-bold text-neutral-900 mt-1">
                            {{ number_format($ingredient->unit_cost ?? 0, 0, ',', ' ') }}
                            <span class="text-sm font-normal text-neutral-500">F</span>
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-neutral-500">Statut</p>
                        @if($ingredient->current_quantity <= 0)
                            <span class="badge bg-red-100 text-red-700 mt-2">Rupture de stock</span>
                        @elseif($ingredient->current_quantity <= $ingredient->min_quantity)
                            <span class="badge bg-yellow-100 text-yellow-700 mt-2">Stock faible</span>
                        @else
                            <span class="badge badge-success mt-2">En stock</span>
                        @endif
                    </div>
                </div>
            </div>

            <!-- Stock Actions -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                <!-- Add Stock -->
                <div class="card p-6">
                    <h3 class="font-semibold text-neutral-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-secondary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Entrée de stock
                    </h3>
                    <form method="POST" action="{{ route('restaurant.stock.ingredients.entry', $ingredient) }}" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-sm text-neutral-600 mb-1">Quantité</label>
                            <input type="number" name="quantity" step="0.01" min="0.01" required class="input">
                        </div>
                        <div>
                            <label class="block text-sm text-neutral-600 mb-1">Coût unitaire (F)</label>
                            <input type="number" name="unit_cost" step="1" min="0" value="{{ $ingredient->unit_cost ?? 0 }}" class="input">
                        </div>
                        <div>
                            <label class="block text-sm text-neutral-600 mb-1">Fournisseur</label>
                            <select name="supplier_id" class="input">
                                <option value="">Aucun</option>
                                @foreach($suppliers as $supplier)
                                    <option value="{{ $supplier->id }}">{{ $supplier->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <button type="submit" class="btn btn-secondary w-full">Ajouter</button>
                    </form>
                </div>

                <!-- Remove Stock -->
                <div class="card p-6">
                    <h3 class="font-semibold text-neutral-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                        </svg>
                        Sortie de stock
                    </h3>
                    <form method="POST" action="{{ route('restaurant.stock.ingredients.exit', $ingredient) }}" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-sm text-neutral-600 mb-1">Quantité</label>
                            <input type="number" name="quantity" step="0.01" min="0.01" max="{{ $ingredient->current_quantity }}" required class="input">
                        </div>
                        <div>
                            <label class="block text-sm text-neutral-600 mb-1">Raison</label>
                            <input type="text" name="reason" required class="input" placeholder="Ex: Utilisation cuisine">
                        </div>
                        <button type="submit" class="btn btn-outline w-full text-red-600 hover:bg-red-50">Retirer</button>
                    </form>
                </div>

                <!-- Adjust Stock -->
                <div class="card p-6">
                    <h3 class="font-semibold text-neutral-900 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-accent-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Ajustement
                    </h3>
                    <form method="POST" action="{{ route('restaurant.stock.ingredients.adjustment', $ingredient) }}" class="space-y-3">
                        @csrf
                        <div>
                            <label class="block text-sm text-neutral-600 mb-1">Nouvelle quantité</label>
                            <input type="number" name="new_quantity" step="0.01" min="0" value="{{ $ingredient->current_quantity }}" required class="input">
                        </div>
                        <div>
                            <label class="block text-sm text-neutral-600 mb-1">Raison</label>
                            <input type="text" name="reason" required class="input" placeholder="Ex: Inventaire">
                        </div>
                        <button type="submit" class="btn btn-outline w-full">Ajuster</button>
                    </form>
                </div>
            </div>

            <!-- Movements History -->
            <div class="card">
                <div class="p-6 border-b border-neutral-100">
                    <h2 class="text-lg font-bold text-neutral-900">Historique des mouvements</h2>
                </div>
                <div class="divide-y divide-neutral-100">
                    @forelse($ingredient->movements as $movement)
                        <div class="p-4 flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                @php
                                    $typeColors = [
                                        'entry' => 'bg-secondary-100 text-secondary-600',
                                        'exit' => 'bg-red-100 text-red-600',
                                        'adjustment' => 'bg-accent-100 text-accent-600',
                                        'waste' => 'bg-yellow-100 text-yellow-600',
                                    ];
                                    $typeIcons = [
                                        'entry' => 'M12 6v6m0 0v6m0-6h6m-6 0H6',
                                        'exit' => 'M20 12H4',
                                        'adjustment' => 'M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15',
                                        'waste' => 'M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16',
                                    ];
                                @endphp
                                <div class="w-10 h-10 rounded-xl flex items-center justify-center {{ $typeColors[$movement->type->value] ?? 'bg-neutral-100 text-neutral-600' }}">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $typeIcons[$movement->type->value] ?? '' }}"/>
                                    </svg>
                                </div>
                                <div>
                                    <p class="font-medium text-neutral-900">{{ $movement->type->label() }}</p>
                                    <p class="text-sm text-neutral-500">{{ $movement->reason ?? '-' }}</p>
                                </div>
                            </div>
                            <div class="text-right">
                                <p class="font-semibold {{ $movement->quantity > 0 ? 'text-secondary-600' : 'text-red-600' }}">
                                    {{ $movement->quantity > 0 ? '+' : '' }}{{ number_format($movement->quantity, 2) }} {{ $ingredient->unit->label() ?? '' }}
                                </p>
                                <p class="text-sm text-neutral-500">{{ $movement->created_at->format('d/m/Y H:i') }}</p>
                            </div>
                        </div>
                    @empty
                        <div class="p-8 text-center">
                            <p class="text-neutral-500">Aucun mouvement enregistré</p>
                        </div>
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Details Card -->
            <div class="card p-6">
                <h2 class="text-lg font-bold text-neutral-900 mb-4">Détails</h2>
                <dl class="space-y-4">
                    <div>
                        <dt class="text-sm text-neutral-500">Catégorie</dt>
                        <dd class="font-medium text-neutral-900">{{ $ingredient->category?->name ?? 'Non classé' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-neutral-500">Unité de mesure</dt>
                        <dd class="font-medium text-neutral-900">{{ $ingredient->unit->label() ?? '-' }}</dd>
                    </div>
                    @if($ingredient->max_quantity)
                        <div>
                            <dt class="text-sm text-neutral-500">Quantité max</dt>
                            <dd class="font-medium text-neutral-900">{{ number_format($ingredient->max_quantity, 2) }}</dd>
                        </div>
                    @endif
                    <div>
                        <dt class="text-sm text-neutral-500">Valeur du stock</dt>
                        <dd class="font-bold text-neutral-900">{{ number_format($ingredient->current_quantity * ($ingredient->unit_cost ?? 0), 0, ',', ' ') }} F</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-neutral-500">Créé le</dt>
                        <dd class="font-medium text-neutral-900">{{ $ingredient->created_at->format('d/m/Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-neutral-500">Dernière mise à jour</dt>
                        <dd class="font-medium text-neutral-900">{{ $ingredient->updated_at->format('d/m/Y H:i') }}</dd>
                    </div>
                </dl>
            </div>

            <!-- Notes -->
            @if($ingredient->notes)
                <div class="card p-6">
                    <h2 class="text-lg font-bold text-neutral-900 mb-4">Notes</h2>
                    <p class="text-neutral-600">{{ $ingredient->notes }}</p>
                </div>
            @endif

            <!-- Suppliers -->
            @if($ingredient->suppliers && $ingredient->suppliers->isNotEmpty())
                <div class="card p-6">
                    <h2 class="text-lg font-bold text-neutral-900 mb-4">Fournisseurs liés</h2>
                    <div class="space-y-3">
                        @foreach($ingredient->suppliers as $supplier)
                            <div class="flex items-center gap-3 p-3 bg-neutral-50 rounded-xl">
                                <div class="w-8 h-8 bg-primary-100 rounded-lg flex items-center justify-center">
                                    <span class="text-sm font-bold text-primary-600">{{ strtoupper(substr($supplier->name, 0, 1)) }}</span>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <p class="font-medium text-neutral-900 truncate">{{ $supplier->name }}</p>
                                    @if($supplier->phone)
                                        <p class="text-xs text-neutral-500">{{ $supplier->phone }}</p>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            <!-- Danger Zone -->
            <div class="card p-6 border-l-4 border-red-500">
                <h2 class="text-lg font-bold text-neutral-900 mb-4">Zone dangereuse</h2>
                <p class="text-sm text-neutral-500 mb-4">La suppression de cet ingrédient est irréversible.</p>
                <form method="POST" action="{{ route('restaurant.stock.ingredients.destroy', $ingredient) }}" 
                      onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer cet ingrédient ? Cette action est irréversible.')">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="btn w-full bg-red-500 text-white hover:bg-red-600">
                        Supprimer l'ingrédient
                    </button>
                </form>
            </div>
        </div>
    </div>
</x-layouts.admin-restaurant>

