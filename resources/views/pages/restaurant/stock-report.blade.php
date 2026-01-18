<x-layouts.admin-restaurant title="Rapport de Stock">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <a href="{{ route('restaurant.stock.ingredients.index') }}" class="text-neutral-500 hover:text-neutral-700 text-sm flex items-center gap-1 mb-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Retour au stock
            </a>
            <h1 class="text-2xl font-bold text-neutral-900">Rapport de Stock</h1>
            <p class="text-neutral-500 mt-1">Vue d'ensemble de votre inventaire.</p>
        </div>
        <button onclick="window.print()" class="btn btn-outline">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
            </svg>
            Imprimer
        </button>
    </div>

    <!-- Summary Cards -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="card p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-neutral-500">Total ingrédients</p>
                    <p class="text-3xl font-bold text-neutral-900 mt-1">{{ $ingredients->count() }}</p>
                </div>
                <div class="w-12 h-12 bg-primary-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
            </div>
        </div>
        <div class="card p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-neutral-500">Valeur totale</p>
                    <p class="text-3xl font-bold text-neutral-900 mt-1">{{ number_format($totalValue, 0, ',', ' ') }} <span class="text-lg font-normal">F</span></p>
                </div>
                <div class="w-12 h-12 bg-secondary-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-secondary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
        <div class="card p-6 {{ $lowStockCount > 0 ? 'border-l-4 border-yellow-500' : '' }}">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-neutral-500">Stock faible</p>
                    <p class="text-3xl font-bold {{ $lowStockCount > 0 ? 'text-yellow-600' : 'text-neutral-900' }} mt-1">{{ $lowStockCount }}</p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
            </div>
        </div>
        <div class="card p-6 {{ $outOfStockCount > 0 ? 'border-l-4 border-red-500' : '' }}">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-neutral-500">Rupture</p>
                    <p class="text-3xl font-bold {{ $outOfStockCount > 0 ? 'text-red-600' : 'text-neutral-900' }} mt-1">{{ $outOfStockCount }}</p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Inventory Table -->
    <div class="card overflow-hidden">
        <div class="p-6 border-b border-neutral-100">
            <h2 class="text-lg font-bold text-neutral-900">Inventaire complet</h2>
            <p class="text-sm text-neutral-500 mt-1">Généré le {{ now()->locale('fr')->isoFormat('D MMMM YYYY [à] HH:mm') }}</p>
        </div>
        <div class="table-responsive">
            <table class="w-full min-w-[600px]">
                <thead class="bg-neutral-50 border-b border-neutral-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-500 uppercase tracking-wider">Ingrédient</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-500 uppercase tracking-wider">Catégorie</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-neutral-500 uppercase tracking-wider">Quantité</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-neutral-500 uppercase tracking-wider">Coût unitaire</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-neutral-500 uppercase tracking-wider">Valeur</th>
                        <th class="px-6 py-4 text-center text-xs font-semibold text-neutral-500 uppercase tracking-wider">Statut</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100">
                    @foreach($ingredients as $ingredient)
                        @php
                            $value = $ingredient->current_quantity * ($ingredient->unit_cost ?? 0);
                        @endphp
                        <tr class="hover:bg-neutral-50">
                            <td class="px-6 py-4">
                                <p class="font-medium text-neutral-900">{{ $ingredient->name }}</p>
                                @if($ingredient->sku)
                                    <p class="text-xs text-neutral-500">SKU: {{ $ingredient->sku }}</p>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-neutral-600">{{ $ingredient->category?->name ?? '-' }}</td>
                            <td class="px-6 py-4 text-right font-semibold text-neutral-900">
                                {{ number_format($ingredient->current_quantity, 2) }} {{ $ingredient->unit->label() }}
                            </td>
                            <td class="px-6 py-4 text-right text-neutral-600">
                                {{ number_format($ingredient->unit_cost ?? 0, 0, ',', ' ') }} F
                            </td>
                            <td class="px-6 py-4 text-right font-semibold text-neutral-900">
                                {{ number_format($value, 0, ',', ' ') }} F
                            </td>
                            <td class="px-6 py-4 text-center">
                                @if($ingredient->current_quantity <= 0)
                                    <span class="badge bg-red-100 text-red-700">Rupture</span>
                                @elseif($ingredient->current_quantity <= $ingredient->min_quantity)
                                    <span class="badge bg-yellow-100 text-yellow-700">Faible</span>
                                @else
                                    <span class="badge badge-success">OK</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
                <tfoot class="bg-neutral-50 border-t-2 border-neutral-200">
                    <tr>
                        <td colspan="4" class="px-6 py-4 text-right font-bold text-neutral-900">Total</td>
                        <td class="px-6 py-4 text-right font-bold text-neutral-900 text-lg">{{ number_format($totalValue, 0, ',', ' ') }} F</td>
                        <td></td>
                    </tr>
                </tfoot>
            </table>
        </div>
    </div>
</x-layouts.admin-restaurant>

