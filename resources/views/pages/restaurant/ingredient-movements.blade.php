<x-layouts.admin-restaurant title="Mouvements - {{ $ingredient->name }}">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <a href="{{ route('restaurant.stock.ingredients.show', $ingredient) }}" class="text-neutral-500 hover:text-neutral-700 text-sm flex items-center gap-1 mb-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Retour à {{ $ingredient->name }}
            </a>
            <h1 class="text-2xl font-bold text-neutral-900">Historique des mouvements</h1>
            <p class="text-neutral-500 mt-1">{{ $ingredient->name }}</p>
        </div>
    </div>

    <!-- Movements Table -->
    <div class="card overflow-hidden">
        <div class="p-6 border-b border-neutral-100">
            <h2 class="text-lg font-bold text-neutral-900">Tous les mouvements</h2>
            <p class="text-sm text-neutral-500 mt-1">Historique complet des entrées et sorties de stock</p>
        </div>
        <div class="divide-y divide-neutral-100">
            @forelse($movements as $movement)
                <div class="p-6 flex items-center justify-between hover:bg-neutral-50">
                    <div class="flex items-center gap-4">
                        @php
                            $typeColors = [
                                'entry' => 'bg-secondary-100 text-secondary-600',
                                'exit_sale' => 'bg-red-100 text-red-600',
                                'exit_manual' => 'bg-orange-100 text-orange-600',
                                'exit_waste' => 'bg-yellow-100 text-yellow-600',
                                'adjustment' => 'bg-accent-100 text-accent-600',
                                'transfer' => 'bg-primary-100 text-primary-600',
                            ];
                            $typeColor = $typeColors[$movement->type->value] ?? 'bg-neutral-100 text-neutral-600';
                        @endphp
                        <div class="w-12 h-12 rounded-xl flex items-center justify-center {{ $typeColor }}">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                @if($movement->type->isPositive())
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                @else
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                @endif
                            </svg>
                        </div>
                        <div>
                            <p class="font-medium text-neutral-900">{{ $movement->type->label() }}</p>
                            <p class="text-sm text-neutral-500">{{ $movement->reason ?? '-' }}</p>
                            @if($movement->user)
                                <p class="text-xs text-neutral-400 mt-1">Par {{ $movement->user->name }}</p>
                            @endif
                        </div>
                    </div>
                    <div class="text-right">
                        <p class="font-semibold text-lg {{ $movement->quantity > 0 ? 'text-secondary-600' : 'text-red-600' }}">
                            {{ $movement->formatted_quantity }} {{ $ingredient->unit->shortLabel() }}
                        </p>
                        <p class="text-sm text-neutral-500">{{ $movement->created_at->format('d/m/Y H:i') }}</p>
                        @if($movement->quantity_before !== null && $movement->quantity_after !== null)
                            <p class="text-xs text-neutral-400 mt-1">Avant: {{ number_format($movement->quantity_before, 2) }} → Après: {{ number_format($movement->quantity_after, 2) }}</p>
                        @endif
                    </div>
                </div>
            @empty
                <div class="p-12 text-center">
                    <svg class="w-16 h-16 text-neutral-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    <p class="text-neutral-500 mt-2">Aucun mouvement enregistré</p>
                    <a href="{{ route('restaurant.stock.ingredients.show', $ingredient) }}" class="btn btn-outline mt-4 inline-block">Retour à l'ingrédient</a>
                </div>
            @endforelse
        </div>
        @if($movements->hasPages())
            <div class="p-6 border-t border-neutral-100">
                {{ $movements->links() }}
            </div>
        @endif
    </div>
</x-layouts.admin-restaurant>
