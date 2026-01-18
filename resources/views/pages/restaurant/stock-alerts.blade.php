<x-layouts.admin-restaurant title="Alertes Stock">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <a href="{{ route('restaurant.stock.ingredients.index') }}" class="text-neutral-500 hover:text-neutral-700 text-sm flex items-center gap-1 mb-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Retour au stock
            </a>
            <h1 class="text-2xl font-bold text-neutral-900">Alertes Stock</h1>
            <p class="text-neutral-500 mt-1">Ingrédients nécessitant une attention.</p>
        </div>
    </div>

    <!-- Out of Stock -->
    @if($outOfStock->isNotEmpty())
        <div class="card mb-6 border-l-4 border-red-500">
            <div class="p-6 border-b border-neutral-100">
                <h2 class="text-lg font-bold text-red-600 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                    </svg>
                    Rupture de stock ({{ $outOfStock->count() }})
                </h2>
            </div>
            <div class="divide-y divide-neutral-100">
                @foreach($outOfStock as $ingredient)
                    <div class="p-4 flex items-center justify-between hover:bg-red-50 transition-colors">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 bg-red-100 rounded-xl flex items-center justify-center">
                                <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-neutral-900">{{ $ingredient->name }}</p>
                                <p class="text-sm text-neutral-500">{{ $ingredient->category?->name ?? 'Sans catégorie' }}</p>
                            </div>
                        </div>
                        <a href="{{ route('restaurant.stock.ingredients.show', $ingredient) }}" class="btn btn-sm btn-primary">
                            Réapprovisionner
                        </a>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Low Stock -->
    @if($lowStock->isNotEmpty())
        <div class="card border-l-4 border-yellow-500">
            <div class="p-6 border-b border-neutral-100">
                <h2 class="text-lg font-bold text-yellow-600 flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    Stock faible ({{ $lowStock->count() }})
                </h2>
            </div>
            <div class="divide-y divide-neutral-100">
                @foreach($lowStock as $ingredient)
                    <div class="p-4 flex items-center justify-between hover:bg-yellow-50 transition-colors">
                        <div class="flex items-center gap-4">
                            <div class="w-10 h-10 bg-yellow-100 rounded-xl flex items-center justify-center">
                                <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                            </div>
                            <div>
                                <p class="font-medium text-neutral-900">{{ $ingredient->name }}</p>
                                <p class="text-sm text-neutral-500">{{ $ingredient->category?->name ?? 'Sans catégorie' }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-4">
                            <div class="text-right">
                                <p class="font-semibold text-yellow-600">{{ number_format($ingredient->current_quantity, 2) }} {{ $ingredient->unit->label() }}</p>
                                <p class="text-xs text-neutral-500">Min: {{ number_format($ingredient->min_quantity, 2) }}</p>
                            </div>
                            <a href="{{ route('restaurant.stock.ingredients.show', $ingredient) }}" class="btn btn-sm btn-outline">
                                Voir
                            </a>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- No Alerts -->
    @if($outOfStock->isEmpty() && $lowStock->isEmpty())
        <div class="card p-12 text-center">
            <div class="w-16 h-16 bg-secondary-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-secondary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-neutral-900 mb-2">Tout est en ordre !</h3>
            <p class="text-neutral-500">Aucun ingrédient en rupture ou en stock faible.</p>
        </div>
    @endif
</x-layouts.admin-restaurant>

