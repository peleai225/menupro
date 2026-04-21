<x-layouts.admin-restaurant title="Nouveau plat">
    <!-- Header -->
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('restaurant.plats.index') }}" class="p-2 hover:bg-neutral-100 rounded-lg">
            <svg class="w-5 h-5 text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-neutral-900">Nouveau plat</h1>
            <p class="text-neutral-500 mt-1">Ajoutez un nouveau plat à votre menu.</p>
        </div>
    </div>

    <form class="max-w-3xl">
        <div class="card p-6 mb-6">
            <h2 class="text-lg font-semibold text-neutral-900 mb-4">Informations générales</h2>
            
            <div class="space-y-5">
                <!-- Name -->
                <div>
                    <label class="block text-sm font-medium text-neutral-700 mb-2">
                        Nom du plat <span class="text-red-500">*</span>
                    </label>
                    <input type="text" name="name" placeholder="Ex: Poulet braisé" required
                           class="w-full h-12 px-4 bg-white border border-neutral-200 rounded-xl text-neutral-900 placeholder:text-neutral-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                </div>

                <!-- Description -->
                <div>
                    <label class="block text-sm font-medium text-neutral-700 mb-2">
                        Description
                    </label>
                    <textarea name="description" rows="3" placeholder="Décrivez votre plat..."
                              class="w-full px-4 py-3 bg-white border border-neutral-200 rounded-xl text-neutral-900 placeholder:text-neutral-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent resize-none"></textarea>
                </div>

                <!-- Category & Price -->
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 mb-2">
                            Catégorie <span class="text-red-500">*</span>
                        </label>
                        <select name="category_id" required
                                class="w-full h-12 px-4 bg-white border border-neutral-200 rounded-xl text-neutral-900 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                            <option value="">Sélectionner...</option>
                            <option value="1">Entrées</option>
                            <option value="2">Plats principaux</option>
                            <option value="3">Grillades</option>
                            <option value="4">Accompagnements</option>
                            <option value="5">Boissons</option>
                            <option value="6">Desserts</option>
                        </select>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 mb-2">
                            Prix (FCFA) <span class="text-red-500">*</span>
                        </label>
                        <input type="number" name="price" placeholder="0" min="0" required
                               class="w-full h-12 px-4 bg-white border border-neutral-200 rounded-xl text-neutral-900 placeholder:text-neutral-400 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
                    </div>
                </div>
            </div>
        </div>

        <!-- Image -->
        <div class="card p-6 mb-6">
            <h2 class="text-lg font-semibold text-neutral-900 mb-4">Image du plat</h2>
            
            <div x-data="{ preview: null }">
                <div class="border-2 border-dashed border-neutral-300 rounded-2xl p-8 text-center hover:border-primary-400 hover:bg-primary-50/50 transition-colors">
                    <template x-if="preview">
                        <div>
                            <img :src="preview" alt="Aperçu du plat" class="w-48 h-48 object-cover rounded-xl mx-auto mb-4">
                            <button type="button" @click="preview = null" class="text-red-600 text-sm font-medium">
                                Supprimer l'image
                            </button>
                        </div>
                    </template>
                    <template x-if="!preview">
                        <div>
                            <svg class="w-12 h-12 text-neutral-400 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            <p class="text-neutral-600 mb-2">Glissez une image ou cliquez pour sélectionner</p>
                            <p class="text-sm text-neutral-400">PNG, JPG ou WEBP • Max 2 Mo</p>
                        </div>
                    </template>
                    <input type="file" name="image" accept="image/png,image/jpeg,image/webp" class="absolute inset-0 opacity-0 cursor-pointer"
                           @change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null">
                </div>
            </div>
        </div>

        <!-- Options -->
        <div class="card p-6 mb-6">
            <h2 class="text-lg font-semibold text-neutral-900 mb-4">Options</h2>
            
            <div class="space-y-4">
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="is_available" checked class="w-5 h-5 rounded border-neutral-300 text-primary-500 focus:ring-primary-500">
                    <div>
                        <span class="font-medium text-neutral-900">Disponible à la vente</span>
                        <p class="text-sm text-neutral-500">Le plat sera visible sur votre menu public.</p>
                    </div>
                </label>
                <label class="flex items-center gap-3 cursor-pointer">
                    <input type="checkbox" name="is_featured" class="w-5 h-5 rounded border-neutral-300 text-primary-500 focus:ring-primary-500">
                    <div>
                        <span class="font-medium text-neutral-900">Plat vedette</span>
                        <p class="text-sm text-neutral-500">Mettre en avant sur la page d'accueil.</p>
                    </div>
                </label>
            </div>
        </div>

        <!-- Actions -->
        <div class="flex items-center justify-end gap-4">
            <a href="{{ route('restaurant.plats.index') }}" class="btn btn-ghost">Annuler</a>
            <button type="submit" class="btn btn-primary">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Créer le plat
            </button>
        </div>
    </form>
</x-layouts.admin-restaurant>

