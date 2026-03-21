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
            <div class="flex items-center gap-4">
                @if($ingredient->image_url)
                    <img src="{{ $ingredient->image_url }}" alt="{{ $ingredient->name }}"
                         class="w-14 h-14 rounded-xl object-cover border border-neutral-200">
                @else
                    <div class="w-14 h-14 bg-primary-100 rounded-xl flex items-center justify-center">
                        <svg class="w-7 h-7 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                        </svg>
                    </div>
                @endif
                <div>
                    <h1 class="text-2xl font-bold text-neutral-900">{{ $ingredient->name }}</h1>
                    @if($ingredient->sku)
                        <p class="text-neutral-500 mt-0.5">SKU: {{ $ingredient->sku }}</p>
                    @endif
                </div>
            </div>
        </div>
        @can('update', $ingredient)
        <a href="{{ route('restaurant.stock.ingredients.edit', $ingredient) }}" class="btn btn-outline">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            Modifier
        </a>
        @endcan
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="mb-6 p-4 bg-secondary-50 border border-secondary-200 rounded-xl text-secondary-700">
            {{ session('success') }}
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main: Livewire stock operations -->
        <div class="lg:col-span-2">
            @can('adjustStock', $ingredient)
                @livewire('restaurant.ingredient-stock', ['ingredient' => $ingredient])
            @else
                <!-- Read-only view -->
                <div class="card p-6 mb-6">
                    <h2 class="text-lg font-bold text-neutral-900 mb-6">Informations du stock</h2>
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
                        <div>
                            <p class="text-sm text-neutral-500">Quantité actuelle</p>
                            <p class="text-2xl font-bold text-neutral-900 mt-1">
                                {{ number_format($ingredient->current_quantity, 2) }}
                                <span class="text-sm font-normal text-neutral-500">{{ $ingredient->unit->label() }}</span>
                            </p>
                        </div>
                        <div>
                            <p class="text-sm text-neutral-500">Seuil minimum</p>
                            <p class="text-2xl font-bold text-neutral-900 mt-1">
                                {{ number_format($ingredient->min_quantity, 2) }}
                                <span class="text-sm font-normal text-neutral-500">{{ $ingredient->unit->label() }}</span>
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
            @endcan
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
                        <dd class="font-medium text-neutral-900">{{ $ingredient->unit->label() }}</dd>
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
            @can('delete', $ingredient)
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
            @endcan
        </div>
    </div>
</x-layouts.admin-restaurant>
