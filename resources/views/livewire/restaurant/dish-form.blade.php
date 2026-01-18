<div>
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <div class="flex items-center gap-4">
            <a href="{{ route('restaurant.plats.index') }}" class="p-2 hover:bg-neutral-100 rounded-lg transition-colors">
                <svg class="w-6 h-6 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-neutral-900">
                    {{ $dish ? 'Modifier le plat' : 'Nouveau plat' }}
                </h1>
                <p class="text-neutral-500 mt-1">
                    {{ $dish ? 'Modifiez les informations de votre plat' : 'Ajoutez un nouveau plat à votre menu' }}
                </p>
            </div>
        </div>
    </div>

    <!-- Flash Messages -->
    @if(session('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700">
            {{ session('error') }}
        </div>
    @endif

    @if(!$this->canAddDish && !$dish)
        <div class="mb-6 p-4 bg-accent-50 border border-accent-200 rounded-xl text-accent-700">
            <strong>Limite atteinte !</strong> Passez à un forfait supérieur pour ajouter plus de plats.
            <a href="{{ route('restaurant.subscription.plans') }}" class="underline ml-2">Voir les forfaits</a>
        </div>
    @endif

    <form wire:submit="save" class="space-y-8">
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Main Content -->
            <div class="lg:col-span-2 space-y-6">
                <!-- Basic Info -->
                <div class="card p-6">
                    <h2 class="text-lg font-bold text-neutral-900 mb-6">Informations générales</h2>
                    
                    <div class="space-y-6">
                        <!-- Name -->
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">Nom du plat *</label>
                            <input type="text" 
                                   wire:model="name"
                                   class="input @error('name') border-red-500 @enderror"
                                   placeholder="Ex: Poulet braisé">
                            @error('name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- Description -->
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">Description</label>
                            <textarea wire:model="description"
                                      rows="4"
                                      class="input @error('description') border-red-500 @enderror"
                                      placeholder="Décrivez votre plat..."></textarea>
                            @error('description')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <div class="grid grid-cols-2 gap-6">
                            <!-- Price -->
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 mb-2">Prix (FCFA) *</label>
                                <div class="relative">
                                    <input type="number" 
                                           wire:model="price"
                                           min="0"
                                           step="100"
                                           class="input pr-16 @error('price') border-red-500 @enderror"
                                           placeholder="0">
                                    <span class="absolute right-4 top-1/2 -translate-y-1/2 text-neutral-500 font-medium">F CFA</span>
                                </div>
                                @error('price')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Category -->
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 mb-2">Catégorie *</label>
                                <select wire:model="category_id" class="input @error('category_id') border-red-500 @enderror">
                                    <option value="">Sélectionner...</option>
                                    @foreach($this->categories as $cat)
                                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                                    @endforeach
                                </select>
                                @error('category_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                        </div>

                        <!-- Prep Time -->
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">Temps de préparation (minutes)</label>
                            <input type="number" 
                                   wire:model="prep_time"
                                   min="1"
                                   class="input w-32"
                                   placeholder="15">
                        </div>

                        <!-- Allergens -->
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">Allergènes</label>
                            <input type="text" 
                                   wire:model="allergens"
                                   class="input"
                                   placeholder="Ex: Gluten, Arachides, Lait...">
                            <p class="mt-1 text-xs text-neutral-500">Séparez les allergènes par des virgules</p>
                        </div>
                    </div>
                </div>

                <!-- Options -->
                <div class="card p-6">
                    <div class="flex items-center justify-between mb-6">
                        <div>
                            <h2 class="text-lg font-bold text-neutral-900">Options et variantes</h2>
                            <p class="text-sm text-neutral-500 mt-1">Proposez des personnalisations à vos clients</p>
                        </div>
                        <button type="button" wire:click="addOptionGroup" class="btn btn-secondary px-4 py-2 flex items-center gap-2 shadow-sm hover:shadow-md transition-all">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                            </svg>
                            Ajouter un groupe
                        </button>
                    </div>

                    @if(empty($optionGroups))
                        <div class="text-center py-8 text-neutral-500">
                            <p>Aucun groupe d'options</p>
                            <p class="text-sm mt-1">Ex: Taille (Petit, Moyen, Grand), Suppléments (Fromage, Bacon...)</p>
                        </div>
                    @else
                        <div class="space-y-6">
                            @foreach($optionGroups as $groupIndex => $group)
                                <div class="border border-neutral-200 rounded-xl p-4">
                                    <div class="flex items-start justify-between mb-4">
                                        <div class="flex-1 grid grid-cols-3 gap-4">
                                            <div>
                                                <label class="block text-xs font-medium text-neutral-500 mb-1">Nom du groupe</label>
                                                <input type="text" 
                                                       wire:model="optionGroups.{{ $groupIndex }}.name"
                                                       class="input"
                                                       placeholder="Ex: Taille">
                                            </div>
                                            <div>
                                                <label class="block text-xs font-medium text-neutral-500 mb-1">Max sélections</label>
                                                <input type="number" 
                                                       wire:model="optionGroups.{{ $groupIndex }}.max_selections"
                                                       class="input"
                                                       min="1">
                                            </div>
                                            <div class="flex items-end">
                                                <label class="flex items-center gap-2 cursor-pointer">
                                                    <input type="checkbox" 
                                                           wire:model="optionGroups.{{ $groupIndex }}.is_required"
                                                           class="w-4 h-4 text-primary-500 rounded focus:ring-primary-500">
                                                    <span class="text-sm text-neutral-700">Obligatoire</span>
                                                </label>
                                            </div>
                                        </div>
                                        <button type="button" 
                                                wire:click="removeOptionGroup({{ $groupIndex }})"
                                                class="ml-4 p-2 text-red-500 hover:bg-red-50 rounded-lg">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </div>

                                    <!-- Options -->
                                    <div class="space-y-2 mt-4">
                                        @foreach($group['options'] ?? [] as $optionIndex => $option)
                                            <div class="flex items-center gap-3 p-3 bg-neutral-50 rounded-lg">
                                                <input type="text" 
                                                       wire:model="optionGroups.{{ $groupIndex }}.options.{{ $optionIndex }}.name"
                                                       class="flex-1 input"
                                                       placeholder="Nom de l'option">
                                                <div class="w-32">
                                                    <input type="number" 
                                                           wire:model="optionGroups.{{ $groupIndex }}.options.{{ $optionIndex }}.price_adjustment"
                                                           class="input text-right"
                                                           placeholder="+0 F">
                                                </div>
                                                <label class="flex items-center">
                                                    <input type="checkbox" 
                                                           wire:model="optionGroups.{{ $groupIndex }}.options.{{ $optionIndex }}.is_available"
                                                           class="w-4 h-4 text-primary-500 rounded">
                                                </label>
                                                <button type="button" 
                                                        wire:click="removeOption({{ $groupIndex }}, {{ $optionIndex }})"
                                                        class="p-1 text-red-500 hover:bg-red-100 rounded">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                                    </svg>
                                                </button>
                                            </div>
                                        @endforeach
                                        
                                        <button type="button" 
                                                wire:click="addOption({{ $groupIndex }})"
                                                class="w-full p-2 border-2 border-dashed border-neutral-200 rounded-lg text-neutral-500 hover:border-primary-300 hover:text-primary-500 transition-colors">
                                            + Ajouter une option
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>

                <!-- Ingredients (if stock management) -->
                @if($this->ingredients->isNotEmpty())
                    <div class="card p-6">
                        <div class="flex items-center justify-between mb-6">
                            <div>
                                <h2 class="text-lg font-bold text-neutral-900">Ingrédients</h2>
                                <p class="text-sm text-neutral-500 mt-1">Liez ce plat aux ingrédients pour le suivi du stock</p>
                            </div>
                            <button type="button" wire:click="addIngredient" class="btn btn-secondary px-4 py-2 flex items-center gap-2 shadow-sm hover:shadow-md transition-all">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                                Ajouter
                            </button>
                        </div>

                        <div class="space-y-3">
                            @foreach($selectedIngredients as $index => $item)
                                <div class="flex items-center gap-3">
                                    <select wire:model="selectedIngredients.{{ $index }}.id" class="flex-1 input">
                                        <option value="">Sélectionner un ingrédient...</option>
                                        @foreach($this->ingredients as $ingredient)
                                            <option value="{{ $ingredient->id }}">
                                                {{ $ingredient->name }} ({{ $ingredient->unit->value }})
                                            </option>
                                        @endforeach
                                    </select>
                                    <input type="number" 
                                           wire:model="selectedIngredients.{{ $index }}.quantity"
                                           class="w-24 input"
                                           placeholder="Qté"
                                           min="0.01"
                                           step="0.01">
                                    <button type="button" 
                                            wire:click="removeIngredient({{ $index }})"
                                            class="p-2 text-red-500 hover:bg-red-50 rounded-lg">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif

                <!-- Nutrition -->
                <div class="card p-6">
                    <h2 class="text-lg font-bold text-neutral-900 mb-6">Informations nutritionnelles</h2>
                    <p class="text-sm text-neutral-500 mb-4">Optionnel - Aidez vos clients à faire des choix éclairés</p>
                    
                    <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-neutral-500 mb-1">Calories (kcal)</label>
                            <input type="number" wire:model="calories" class="input" placeholder="0">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-neutral-500 mb-1">Protéines (g)</label>
                            <input type="number" wire:model="proteins" step="0.1" class="input" placeholder="0">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-neutral-500 mb-1">Glucides (g)</label>
                            <input type="number" wire:model="carbs" step="0.1" class="input" placeholder="0">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-neutral-500 mb-1">Lipides (g)</label>
                            <input type="number" wire:model="fats" step="0.1" class="input" placeholder="0">
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sidebar -->
            <div class="space-y-6">
                <!-- Image -->
                <div class="card p-6">
                    <h2 class="text-lg font-bold text-neutral-900 mb-4">Image</h2>
                    
                    <div class="space-y-4">
                        <!-- Preview -->
                        <div class="aspect-square bg-neutral-100 rounded-xl overflow-hidden">
                            @if($image)
                                <img src="{{ $image->temporaryUrl() }}" class="w-full h-full object-cover">
                            @elseif($existingImage)
                                <img src="{{ Storage::url($existingImage) }}" class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <svg class="w-16 h-16 text-neutral-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            @endif
                        </div>

                        <label class="btn btn-secondary w-full cursor-pointer justify-center px-6 py-3 flex items-center gap-2 shadow-sm hover:shadow-md transition-all">
                            <input type="file" wire:model="image" accept="image/*" class="hidden">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                            </svg>
                            Changer l'image
                        </label>
                        
                        <p class="text-xs text-neutral-500 text-center">JPG, PNG ou WebP. Max 5 Mo</p>
                        
                        @error('image')
                            <p class="text-sm text-red-600 text-center">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Status -->
                <div class="card p-6">
                    <h2 class="text-lg font-bold text-neutral-900 mb-4">Statut</h2>
                    
                    <div class="space-y-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium text-neutral-900">Disponible</p>
                                <p class="text-sm text-neutral-500">Afficher sur le menu</p>
                            </div>
                            <button type="button"
                                    wire:click="$toggle('is_available')"
                                    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors {{ $is_available ? 'bg-primary-500' : 'bg-neutral-200' }}">
                                <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $is_available ? 'translate-x-6' : 'translate-x-1' }}"></span>
                            </button>
                        </div>

                        <div class="flex items-center justify-between">
                            <div>
                                <p class="font-medium text-neutral-900">En vedette</p>
                                <p class="text-sm text-neutral-500">Mettre en avant</p>
                            </div>
                            <button type="button"
                                    wire:click="$toggle('is_featured')"
                                    class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors {{ $is_featured ? 'bg-accent-500' : 'bg-neutral-200' }}">
                                <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $is_featured ? 'translate-x-6' : 'translate-x-1' }}"></span>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Actions -->
                <div class="card p-6">
                    <div class="space-y-3">
                        <button type="submit" 
                                class="w-full btn btn-primary px-6 py-3 flex items-center justify-center gap-2 shadow-sm hover:shadow-md transition-all"
                                wire:loading.attr="disabled"
                                @if(!$this->canAddDish && !$dish) disabled @endif>
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <span wire:loading.remove wire:target="save">
                                {{ $dish ? 'Enregistrer les modifications' : 'Créer le plat' }}
                            </span>
                            <span wire:loading wire:target="save" class="flex items-center justify-center gap-2">
                                <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                Enregistrement...
                            </span>
                        </button>
                        
                        <a href="{{ route('restaurant.plats.index') }}" class="block w-full btn btn-secondary px-6 py-3 text-center flex items-center justify-center gap-2 shadow-sm hover:shadow-md transition-all">
                            Annuler
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </form>
</div>

