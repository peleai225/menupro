<x-layouts.admin-restaurant title="Modifier {{ $ingredient->name }}">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <a href="{{ route('restaurant.stock.ingredients.index') }}" class="text-neutral-500 hover:text-neutral-700 text-sm flex items-center gap-1 mb-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Retour au stock
            </a>
            <h1 class="text-2xl font-bold text-neutral-900">Modifier l'ingrédient</h1>
            <p class="text-neutral-500 mt-1">{{ $ingredient->name }}</p>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-secondary-50 border border-secondary-200 rounded-xl text-secondary-700">
            {{ session('success') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700">
            <ul class="list-disc list-inside space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="POST" action="{{ route('restaurant.stock.ingredients.update', $ingredient) }}" class="card p-6 max-w-2xl" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="space-y-6">
            <!-- Image -->
            <div x-data="{
                preview: '{{ $ingredient->image_url }}',
                removeImage: false,
                handleFile(e) {
                    const file = e.target.files[0];
                    if (file) {
                        this.preview = URL.createObjectURL(file);
                        this.removeImage = false;
                    }
                }
            }">
                <label class="block text-sm font-medium text-neutral-700 mb-2">Photo de l'ingrédient</label>
                <div class="flex items-center gap-4">
                    <div class="w-20 h-20 rounded-xl overflow-hidden border-2 border-dashed border-neutral-300 flex items-center justify-center bg-neutral-50 flex-shrink-0">
                        <template x-if="preview && !removeImage">
                            <img :src="preview" alt="Aperçu de l'ingrédient" class="w-full h-full object-cover">
                        </template>
                        <template x-if="!preview || removeImage">
                            <svg class="w-8 h-8 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                        </template>
                    </div>
                    <div class="flex-1 space-y-2">
                        <input type="file" name="image" accept="image/*" @change="handleFile($event)"
                               class="block w-full text-sm text-neutral-500 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-primary-50 file:text-primary-700 hover:file:bg-primary-100">
                        <p class="text-xs text-neutral-400">JPG, PNG ou WebP. Max 2 Mo. Redimensionné en 400×400.</p>
                        @if($ingredient->image_path)
                            <label class="flex items-center gap-2 cursor-pointer text-sm text-red-600" @click="removeImage = !removeImage; preview = removeImage ? null : '{{ $ingredient->image_url }}'">
                                <input type="checkbox" name="remove_image" value="1" x-model="removeImage" class="w-4 h-4 text-red-500 rounded">
                                Supprimer la photo actuelle
                            </label>
                        @endif
                    </div>
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-neutral-700 mb-2">Nom *</label>
                <input type="text" name="name" required class="input" value="{{ old('name', $ingredient->name) }}" placeholder="Ex: Tomates">
            </div>

            <div>
                <label class="block text-sm font-medium text-neutral-700 mb-2">Code article (SKU)</label>
                <input type="text" name="sku" class="input" value="{{ old('sku', $ingredient->sku) }}" placeholder="Ex: TOM-001">
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div>
                    <label class="block text-sm font-medium text-neutral-700 mb-2">Catégorie</label>
                    <select name="ingredient_category_id" class="input">
                        <option value="">Aucune</option>
                        @foreach($categories as $category)
                            <option value="{{ $category->id }}" {{ old('ingredient_category_id', $ingredient->ingredient_category_id) == $category->id ? 'selected' : '' }}>
                                {{ $category->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label class="block text-sm font-medium text-neutral-700 mb-2">Unité *</label>
                    <select name="unit" required class="input">
                        @foreach(\App\Enums\Unit::cases() as $unit)
                            <option value="{{ $unit->value }}" {{ old('unit', $ingredient->unit->value) == $unit->value ? 'selected' : '' }}>
                                {{ $unit->label() }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div>
                    <label class="block text-sm font-medium text-neutral-700 mb-2">Quantité actuelle *</label>
                    <input type="number" name="current_quantity" step="0.01" min="0" required class="input" value="{{ old('current_quantity', $ingredient->current_quantity) }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-neutral-700 mb-2">Seuil minimum *</label>
                    <input type="number" name="min_quantity" step="0.01" min="0" required class="input" value="{{ old('min_quantity', $ingredient->min_quantity) }}">
                </div>
                <div>
                    <label class="block text-sm font-medium text-neutral-700 mb-2">Quantité max</label>
                    <input type="number" name="max_quantity" step="0.01" min="0" class="input" value="{{ old('max_quantity', $ingredient->max_quantity) }}" placeholder="Optionnel">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-neutral-700 mb-2">Coût unitaire (FCFA) *</label>
                <input type="number" name="unit_cost" step="1" min="0" required class="input" value="{{ old('unit_cost', $ingredient->unit_cost ?? 0) }}">
            </div>

            <div class="flex items-center gap-4">
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="hidden" name="track_expiry" value="0">
                    <input type="checkbox" name="track_expiry" value="1" class="w-4 h-4 text-primary-500 rounded"
                           {{ old('track_expiry', $ingredient->track_expiry) ? 'checked' : '' }}>
                    <span class="text-sm text-neutral-700">Suivi de péremption</span>
                </label>
                <div class="flex-1">
                    <label class="block text-sm font-medium text-neutral-700 mb-2">Durée péremption (jours)</label>
                    <input type="number" name="default_expiry_days" min="1" class="input w-32" value="{{ old('default_expiry_days', $ingredient->default_expiry_days) }}" placeholder="Ex: 7">
                </div>
            </div>

            <div>
                <label class="block text-sm font-medium text-neutral-700 mb-2">Notes</label>
                <textarea name="notes" rows="3" class="input" placeholder="Notes internes...">{{ old('notes', $ingredient->notes) }}</textarea>
            </div>

            <div>
                <label class="flex items-center gap-2 cursor-pointer">
                    <input type="hidden" name="is_active" value="0">
                    <input type="checkbox" name="is_active" value="1" class="w-4 h-4 text-primary-500 rounded"
                           {{ old('is_active', $ingredient->is_active) ? 'checked' : '' }}>
                    <span class="text-sm text-neutral-700">Ingrédient actif</span>
                </label>
            </div>
        </div>

        <div class="flex gap-3 mt-8 pt-6 border-t border-neutral-100">
            <a href="{{ route('restaurant.stock.ingredients.index') }}" class="btn btn-outline">
                Annuler
            </a>
            <button type="submit" class="btn btn-primary">
                Enregistrer les modifications
            </button>
        </div>
    </form>
</x-layouts.admin-restaurant>
