<x-layouts.admin-restaurant title="Fournisseur — {{ $supplier->name }}">
    <!-- Header -->
    <div class="flex items-center justify-between gap-4 mb-8">
        <div class="flex items-center gap-4">
            <a href="{{ route('restaurant.suppliers') }}" class="p-2 hover:bg-neutral-100 rounded-lg">
                <svg class="w-5 h-5 text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"/>
                </svg>
            </a>
            <div>
                <div class="flex items-center gap-3">
                    <h1 class="text-2xl font-bold text-neutral-900">{{ $supplier->name }}</h1>
                    @if(!$supplier->is_active)
                        <span class="badge badge-neutral text-xs">Inactif</span>
                    @endif
                </div>
                @if($supplier->city)
                    <p class="text-neutral-500 text-sm mt-0.5">{{ $supplier->city }}</p>
                @endif
            </div>
        </div>
        <!-- Actions -->
        <div class="flex items-center gap-2">
            <button onclick="document.getElementById('editModal').classList.remove('hidden')"
                    class="btn btn-outline text-sm">
                Modifier
            </button>
            <form action="{{ route('restaurant.suppliers.destroy', $supplier) }}" method="POST"
                  onsubmit="return confirm('Supprimer ce fournisseur ?')">
                @csrf @method('DELETE')
                <button type="submit" class="btn btn-outline text-sm text-red-600 border-red-200 hover:bg-red-50">
                    Supprimer
                </button>
            </form>
        </div>
    </div>

    <!-- Flash -->
    @if(session('success'))
        <div class="mb-6 p-4 bg-secondary-50 border border-secondary-200 rounded-xl text-secondary-700">{{ session('success') }}</div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Infos fournisseur -->
        <div class="space-y-6">
            <div class="card p-5">
                <h3 class="font-semibold text-neutral-800 mb-4">Informations</h3>
                <div class="space-y-3 text-sm">
                    @if($supplier->contact_name)
                    <div class="flex gap-3">
                        <svg class="w-4 h-4 text-neutral-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        <span class="text-neutral-700">{{ $supplier->contact_name }}</span>
                    </div>
                    @endif
                    @if($supplier->phone)
                    <div class="flex gap-3">
                        <svg class="w-4 h-4 text-neutral-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        <span class="text-neutral-700">{{ $supplier->phone }}</span>
                    </div>
                    @endif
                    @if($supplier->email)
                    <div class="flex gap-3">
                        <svg class="w-4 h-4 text-neutral-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <span class="text-neutral-700">{{ $supplier->email }}</span>
                    </div>
                    @endif
                    @if($supplier->address)
                    <div class="flex gap-3">
                        <svg class="w-4 h-4 text-neutral-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        </svg>
                        <span class="text-neutral-700">{{ $supplier->address }}{{ $supplier->city ? ', ' . $supplier->city : '' }}</span>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Conditions -->
            <div class="card p-5">
                <h3 class="font-semibold text-neutral-800 mb-4">Conditions commerciales</h3>
                <div class="space-y-3 text-sm">
                    @if($supplier->min_order_amount)
                    <div class="flex justify-between">
                        <span class="text-neutral-500">Commande min.</span>
                        <span class="font-medium">{{ number_format($supplier->min_order_amount, 0, ',', ' ') }} FCFA</span>
                    </div>
                    @endif
                    @if($supplier->delivery_days !== null)
                    <div class="flex justify-between">
                        <span class="text-neutral-500">Délai livraison</span>
                        <span class="font-medium">{{ $supplier->delivery_days }} jour(s)</span>
                    </div>
                    @endif
                    @if($supplier->payment_terms)
                    <div class="flex justify-between">
                        <span class="text-neutral-500">Conditions paiement</span>
                        <span class="font-medium">{{ $supplier->payment_terms }}</span>
                    </div>
                    @endif
                </div>
                @if($supplier->notes)
                    <p class="mt-4 text-sm text-neutral-500 border-t border-neutral-100 pt-3">{{ $supplier->notes }}</p>
                @endif
            </div>
        </div>

        <!-- Ingrédients liés -->
        <div class="lg:col-span-2">
            <div class="card overflow-hidden">
                <div class="px-5 py-4 border-b border-neutral-100 flex items-center justify-between">
                    <h3 class="font-semibold text-neutral-800">
                        Ingrédients liés ({{ $supplier->ingredients->count() }})
                    </h3>
                    <button onclick="document.getElementById('linkIngredientModal').classList.remove('hidden')"
                            class="btn btn-outline text-sm">
                        + Associer un ingrédient
                    </button>
                </div>

                @if($supplier->ingredients->isEmpty())
                    <div class="p-8 text-center">
                        <p class="text-neutral-400 text-sm">Aucun ingrédient associé à ce fournisseur.</p>
                    </div>
                @else
                    <div class="divide-y divide-neutral-100">
                        @foreach($supplier->ingredients as $ingredient)
                        <div class="px-5 py-3 flex items-center justify-between gap-4">
                            <div class="min-w-0">
                                <p class="text-sm font-medium text-neutral-800">{{ $ingredient->name }}</p>
                                <p class="text-xs text-neutral-400">
                                    Stock : {{ $ingredient->current_stock }} {{ $ingredient->unit?->value ?? '' }}
                                    @if($ingredient->pivot->supplier_sku)
                                        · Réf : {{ $ingredient->pivot->supplier_sku }}
                                    @endif
                                </p>
                            </div>
                            <div class="flex items-center gap-3">
                                @if($ingredient->pivot->unit_price)
                                    <span class="text-sm font-medium text-neutral-700">
                                        {{ number_format($ingredient->pivot->unit_price, 0, ',', ' ') }} FCFA
                                    </span>
                                @endif
                                @if($ingredient->pivot->is_preferred)
                                    <span class="badge badge-success text-xs">Préféré</span>
                                @endif
                                <form action="{{ route('restaurant.suppliers.unlink-ingredient', [$supplier, $ingredient->id]) }}"
                                      method="POST" onsubmit="return confirm('Supprimer cette association ?')">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="text-red-400 hover:text-red-600 p-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Modal édition fournisseur -->
    <div id="editModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl p-6 w-full max-w-lg">
            <h2 class="text-lg font-semibold mb-4">Modifier le fournisseur</h2>
            <form action="{{ route('restaurant.suppliers.update', $supplier) }}" method="POST">
                @csrf @method('PUT')
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-neutral-700 mb-1">Nom *</label>
                        <input type="text" name="name" value="{{ old('name', $supplier->name) }}" required
                               class="input w-full">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 mb-1">Contact</label>
                        <input type="text" name="contact_name" value="{{ old('contact_name', $supplier->contact_name) }}"
                               class="input w-full">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 mb-1">Téléphone</label>
                        <input type="text" name="phone" value="{{ old('phone', $supplier->phone) }}"
                               class="input w-full">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 mb-1">Email</label>
                        <input type="email" name="email" value="{{ old('email', $supplier->email) }}"
                               class="input w-full">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 mb-1">Ville</label>
                        <input type="text" name="city" value="{{ old('city', $supplier->city) }}"
                               class="input w-full">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 mb-1">Commande minimum (FCFA)</label>
                        <input type="number" name="min_order_amount" value="{{ old('min_order_amount', $supplier->min_order_amount) }}"
                               min="0" class="input w-full">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 mb-1">Délai livraison (jours)</label>
                        <input type="number" name="delivery_days" value="{{ old('delivery_days', $supplier->delivery_days) }}"
                               min="0" class="input w-full">
                    </div>
                    <div class="col-span-2">
                        <label class="block text-sm font-medium text-neutral-700 mb-1">Notes</label>
                        <textarea name="notes" rows="2" class="input w-full">{{ old('notes', $supplier->notes) }}</textarea>
                    </div>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('editModal').classList.add('hidden')"
                            class="btn btn-outline">Annuler</button>
                    <button type="submit" class="btn btn-primary">Enregistrer</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Modal association ingrédient -->
    <div id="linkIngredientModal" class="hidden fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4">
        <div class="bg-white rounded-2xl p-6 w-full max-w-md">
            <h2 class="text-lg font-semibold mb-4">Associer un ingrédient</h2>
            <form action="{{ route('restaurant.suppliers.link-ingredient', $supplier) }}" method="POST">
                @csrf
                <div class="space-y-4 mb-4">
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 mb-1">Ingrédient *</label>
                        <select name="ingredient_id" required class="input w-full">
                            <option value="">-- Sélectionner --</option>
                            @foreach(auth()->user()->restaurant->ingredients()->orderBy('name')->get() as $ing)
                                <option value="{{ $ing->id }}">{{ $ing->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 mb-1">Prix unitaire (FCFA) *</label>
                        <input type="number" name="unit_price" min="0" required class="input w-full">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 mb-1">Référence fournisseur</label>
                        <input type="text" name="supplier_sku" class="input w-full">
                    </div>
                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="is_preferred" id="is_preferred" value="1"
                               class="rounded border-neutral-300 text-primary-600">
                        <label for="is_preferred" class="text-sm text-neutral-700">Fournisseur préféré pour cet ingrédient</label>
                    </div>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="document.getElementById('linkIngredientModal').classList.add('hidden')"
                            class="btn btn-outline">Annuler</button>
                    <button type="submit" class="btn btn-primary">Associer</button>
                </div>
            </form>
        </div>
    </div>
</x-layouts.admin-restaurant>
