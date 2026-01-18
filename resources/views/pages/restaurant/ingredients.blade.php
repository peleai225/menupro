<x-layouts.admin-restaurant title="Gestion du Stock">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-neutral-900">Gestion du Stock</h1>
            <p class="text-neutral-500 mt-1">Gérez vos ingrédients et suivez votre inventaire.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('restaurant.stock.categories-ingredients.index') }}" class="btn btn-outline">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
                Catégories
            </a>
            <button onclick="document.getElementById('addIngredientModal').classList.remove('hidden')" class="btn btn-primary">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Ajouter un ingrédient
            </button>
        </div>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="mb-6 p-4 bg-secondary-50 border border-secondary-200 rounded-xl text-secondary-700">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
        <div class="card p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-neutral-500">Total ingrédients</p>
                    <p class="text-3xl font-bold text-neutral-900 mt-1">{{ number_format($stats['total']) }}</p>
                </div>
                <div class="w-12 h-12 bg-primary-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
            </div>
        </div>
        <div class="card p-6 {{ $stats['low_stock'] > 0 ? 'border-l-4 border-yellow-500' : '' }}">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-neutral-500">Stock faible</p>
                    <p class="text-3xl font-bold {{ $stats['low_stock'] > 0 ? 'text-yellow-600' : 'text-neutral-900' }} mt-1">{{ number_format($stats['low_stock']) }}</p>
                </div>
                <div class="w-12 h-12 bg-yellow-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
            </div>
        </div>
        <div class="card p-6 {{ $stats['out_of_stock'] > 0 ? 'border-l-4 border-red-500' : '' }}">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-neutral-500">Rupture</p>
                    <p class="text-3xl font-bold {{ $stats['out_of_stock'] > 0 ? 'text-red-600' : 'text-neutral-900' }} mt-1">{{ number_format($stats['out_of_stock']) }}</p>
                </div>
                <div class="w-12 h-12 bg-red-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                    </svg>
                </div>
            </div>
        </div>
        <div class="card p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-neutral-500">Valeur stock</p>
                    <p class="text-3xl font-bold text-neutral-900 mt-1">{{ number_format($stats['total_value'], 0, ',', ' ') }} <span class="text-lg font-normal">F</span></p>
                </div>
                <div class="w-12 h-12 bg-secondary-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-secondary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <form method="GET" class="card p-4 mb-6">
        <div class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1 relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" 
                       name="search" 
                       value="{{ request('search') }}"
                       placeholder="Rechercher un ingrédient..." 
                       class="w-full h-10 pl-10 pr-4 bg-neutral-50 border border-neutral-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
            </div>
            <select name="category" class="h-10 px-4 bg-neutral-50 border border-neutral-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="">Toutes catégories</option>
                @foreach($categories as $category)
                    <option value="{{ $category->id }}" {{ request('category') == $category->id ? 'selected' : '' }}>{{ $category->name }}</option>
                @endforeach
            </select>
            <select name="status" class="h-10 px-4 bg-neutral-50 border border-neutral-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="">Tous statuts</option>
                <option value="in" {{ request('status') === 'in' ? 'selected' : '' }}>En stock</option>
                <option value="low" {{ request('status') === 'low' ? 'selected' : '' }}>Stock faible</option>
                <option value="out" {{ request('status') === 'out' ? 'selected' : '' }}>Rupture</option>
            </select>
            <button type="submit" class="btn-primary">Filtrer</button>
        </div>
    </form>

    <!-- Ingredients Table -->
    <div class="card overflow-hidden">
        <div class="table-responsive">
            <table class="w-full min-w-[600px]">
                <thead class="bg-neutral-50 border-b border-neutral-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-500 uppercase tracking-wider">Ingrédient</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-500 uppercase tracking-wider">Catégorie</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-500 uppercase tracking-wider">Quantité</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-500 uppercase tracking-wider">Seuil min</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-500 uppercase tracking-wider">Statut</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-neutral-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100">
                    @forelse($ingredients as $ingredient)
                        <tr class="hover:bg-neutral-50 transition-colors">
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-primary-100 rounded-xl flex items-center justify-center">
                                        <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <p class="font-medium text-neutral-900">{{ $ingredient->name }}</p>
                                        @if($ingredient->sku)
                                            <p class="text-xs text-neutral-500">SKU: {{ $ingredient->sku }}</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-neutral-600">{{ $ingredient->category?->name ?? '-' }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="font-semibold text-neutral-900">{{ number_format($ingredient->current_quantity, 2) }} {{ $ingredient->unit->value ?? '' }}</span>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-neutral-600">{{ number_format($ingredient->min_quantity, 2) }} {{ $ingredient->unit->value ?? '' }}</span>
                            </td>
                            <td class="px-6 py-4">
                                @if($ingredient->current_quantity <= 0)
                                    <span class="badge bg-red-100 text-red-700">Rupture</span>
                                @elseif($ingredient->current_quantity <= $ingredient->min_quantity)
                                    <span class="badge bg-yellow-100 text-yellow-700">Stock faible</span>
                                @else
                                    <span class="badge badge-success">En stock</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('restaurant.stock.ingredients.show', $ingredient) }}" 
                                       class="p-2 hover:bg-neutral-100 rounded-lg transition-colors"
                                       title="Voir détails">
                                        <svg class="w-5 h-5 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>
                                    <a href="{{ route('restaurant.stock.ingredients.edit', $ingredient) }}" 
                                       class="p-2 hover:bg-neutral-100 rounded-lg transition-colors"
                                       title="Modifier">
                                        <svg class="w-5 h-5 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </a>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center">
                                <div class="w-16 h-16 bg-neutral-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-8 h-8 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                                    </svg>
                                </div>
                                <p class="text-neutral-500">Aucun ingrédient trouvé</p>
                                <p class="text-sm text-neutral-400 mt-1">Commencez par ajouter vos premiers ingrédients.</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Pagination -->
    @if($ingredients->hasPages())
        <div class="mt-6">
            {{ $ingredients->links() }}
        </div>
    @endif

    <!-- Add Ingredient Modal -->
    <div id="addIngredientModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/50" onclick="document.getElementById('addIngredientModal').classList.add('hidden')"></div>
            <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-xl">
                <div class="p-6 border-b border-neutral-100">
                    <h2 class="text-xl font-bold text-neutral-900">Ajouter un ingrédient</h2>
                </div>
                <form method="POST" action="{{ route('restaurant.stock.ingredients.store') }}" class="p-6 space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 mb-2">Nom *</label>
                        <input type="text" name="name" required class="input" placeholder="Ex: Tomates">
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">Catégorie</label>
                            <select name="ingredient_category_id" class="input">
                                <option value="">Aucune</option>
                                @foreach($categories as $category)
                                    <option value="{{ $category->id }}">{{ $category->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">Unité *</label>
                            <select name="unit" required class="input">
                                @foreach(\App\Enums\Unit::cases() as $unit)
                                    <option value="{{ $unit->value }}">{{ $unit->label() }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">Quantité initiale</label>
                            <input type="number" name="current_quantity" step="0.01" min="0" value="0" class="input">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">Seuil minimum</label>
                            <input type="number" name="min_quantity" step="0.01" min="0" value="0" class="input">
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 mb-2">Coût unitaire (FCFA)</label>
                        <input type="number" name="unit_cost" step="1" min="0" value="0" class="input">
                    </div>
                    <div class="flex gap-3 pt-4">
                        <button type="button" onclick="document.getElementById('addIngredientModal').classList.add('hidden')" class="btn btn-outline flex-1">
                            Annuler
                        </button>
                        <button type="submit" class="btn btn-primary flex-1">
                            Ajouter
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.admin-restaurant>

