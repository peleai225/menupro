<x-layouts.admin-super title="Nouvelle bannière">
    <!-- Header -->
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('super-admin.promo-banners.index') }}" class="p-2 rounded-lg transition-colors" style="color:var(--sa-muted-fg);">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold" style="color:var(--sa-fg);">Nouvelle bannière</h1>
            <p class="mt-1" style="color:var(--sa-muted-fg);">Créez une bannière promotionnelle pour l'app client.</p>
        </div>
    </div>

    <form action="{{ route('super-admin.promo-banners.store') }}" method="POST"
          enctype="multipart/form-data" class="max-w-3xl">
        @csrf

        <div class="space-y-6">

            <!-- Image -->
            <div class="border shadow-sm rounded-xl p-6" style="background:var(--sa-card);border-color:var(--sa-border);">
                <label class="block text-sm font-medium mb-4" style="color:var(--sa-fg);">
                    Image <span class="text-red-600">*</span>
                    <span class="text-xs font-normal ml-2" style="color:var(--sa-muted-fg);">JPG, PNG, WebP — max 2 Mo — ratio 16:7 recommandé</span>
                </label>

                <div x-data="{ preview: null }" class="space-y-4">
                    <div class="relative border-2 border-dashed rounded-xl p-8 text-center cursor-pointer hover:border-primary-400 transition-colors"
                         style="border-color:var(--sa-border);"
                         @click="$refs.fileInput.click()"
                         @dragover.prevent
                         @drop.prevent="
                            const file = $event.dataTransfer.files[0];
                            if (file) { preview = URL.createObjectURL(file); $refs.fileInput.files = $event.dataTransfer.files; }
                         ">
                        <template x-if="!preview">
                            <div>
                                <svg class="w-12 h-12 mx-auto mb-3" style="color:var(--sa-muted-fg);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                                <p class="text-sm" style="color:var(--sa-muted-fg);">Cliquez ou glissez une image ici</p>
                            </div>
                        </template>
                        <template x-if="preview">
                            <img :src="preview" class="max-h-40 mx-auto rounded-lg object-cover">
                        </template>
                    </div>

                    <input type="file" name="image" accept="image/*" class="hidden" required
                           x-ref="fileInput"
                           @change="preview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null">
                </div>
                @error('image')
                    <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                @enderror
            </div>

            <!-- Textes -->
            <div class="border shadow-sm rounded-xl p-6 space-y-5" style="background:var(--sa-card);border-color:var(--sa-border);">
                <h3 class="text-sm font-medium" style="color:var(--sa-fg);">Textes <span class="text-xs font-normal" style="color:var(--sa-muted-fg);">(optionnel — s'affichent en overlay sur l'image)</span></h3>
                <div>
                    <label class="block text-sm mb-2" style="color:var(--sa-muted-fg);">Titre</label>
                    <input type="text" name="title" value="{{ old('title') }}" maxlength="100"
                           class="w-full h-12 px-4 border rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500"
                           style="background:var(--sa-muted);border-color:var(--sa-border);color:var(--sa-fg);"
                           placeholder="Ex: -20% sur les pizzas ce weekend">
                    @error('title')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm mb-2" style="color:var(--sa-muted-fg);">Sous-titre</label>
                    <input type="text" name="subtitle" value="{{ old('subtitle') }}" maxlength="150"
                           class="w-full h-12 px-4 border rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500"
                           style="background:var(--sa-muted);border-color:var(--sa-border);color:var(--sa-fg);"
                           placeholder="Ex: Valable du 18 au 20 juillet">
                    @error('subtitle')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                </div>
            </div>

            <!-- Restaurant -->
            <div class="border shadow-sm rounded-xl p-6" style="background:var(--sa-card);border-color:var(--sa-border);">
                <label class="block text-sm font-medium mb-2" style="color:var(--sa-fg);">Restaurant ciblé</label>
                <p class="text-xs mb-4" style="color:var(--sa-muted-fg);">Laissez vide pour afficher sur tous les restaurants (bannière globale).</p>
                <select name="restaurant_id"
                        class="w-full h-12 px-4 border rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500"
                        style="background:var(--sa-muted);border-color:var(--sa-border);color:var(--sa-fg);">
                    <option value="">— Global (tous les restaurants) —</option>
                    @foreach($restaurants as $restaurant)
                        <option value="{{ $restaurant->id }}" {{ old('restaurant_id') == $restaurant->id ? 'selected' : '' }}>
                            {{ $restaurant->name }}
                        </option>
                    @endforeach
                </select>
                @error('restaurant_id')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
            </div>

            <!-- Lien -->
            <div class="border shadow-sm rounded-xl p-6 space-y-5" style="background:var(--sa-card);border-color:var(--sa-border);"
                 x-data="{ linkType: '{{ old('link_type', 'none') }}' }">
                <h3 class="text-sm font-medium" style="color:var(--sa-fg);">Action au clic</h3>

                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    @foreach(['none' => 'Aucune', 'dish' => 'Vers un plat', 'promo_code' => 'Code promo', 'url' => 'URL externe'] as $val => $label)
                        <label class="cursor-pointer">
                            <input type="radio" name="link_type" value="{{ $val }}" class="peer sr-only"
                                   x-model="linkType" {{ old('link_type', 'none') === $val ? 'checked' : '' }}>
                            <div class="p-3 rounded-xl text-center text-sm peer-checked:border-primary-500 peer-checked:bg-primary-500/10 hover:border-neutral-500 transition-colors"
                                 style="border:2px solid var(--sa-border);color:var(--sa-fg);">
                                {{ $label }}
                            </div>
                        </label>
                    @endforeach
                </div>

                <div x-show="linkType !== 'none'" x-transition class="grid md:grid-cols-2 gap-5">
                    <div>
                        <label class="block text-sm mb-2" style="color:var(--sa-muted-fg);">
                            <span x-show="linkType === 'dish'">ID du plat</span>
                            <span x-show="linkType === 'promo_code'">Code promo</span>
                            <span x-show="linkType === 'url'">URL</span>
                            <span x-show="linkType === 'none'">Valeur</span>
                        </label>
                        <input type="text" name="link_value" value="{{ old('link_value') }}" maxlength="500"
                               class="w-full h-12 px-4 border rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500"
                               style="background:var(--sa-muted);border-color:var(--sa-border);color:var(--sa-fg);">
                        @error('link_value')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                    <div>
                        <label class="block text-sm mb-2" style="color:var(--sa-muted-fg);">Libellé bouton CTA</label>
                        <input type="text" name="cta_label" value="{{ old('cta_label') }}" maxlength="100"
                               placeholder="Ex: Voir l'offre"
                               class="w-full h-12 px-4 border rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500"
                               style="background:var(--sa-muted);border-color:var(--sa-border);color:var(--sa-fg);">
                        @error('cta_label')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                    </div>
                </div>
            </div>

            <!-- Planification + ordre -->
            <div class="border shadow-sm rounded-xl p-6" style="background:var(--sa-card);border-color:var(--sa-border);">
                <h3 class="text-sm font-medium mb-4" style="color:var(--sa-fg);">Planification et ordre</h3>
                <div class="grid md:grid-cols-3 gap-5">
                    <div>
                        <label class="block text-sm mb-2" style="color:var(--sa-muted-fg);">Date de début</label>
                        <input type="datetime-local" name="starts_at" value="{{ old('starts_at') }}"
                               class="w-full h-12 px-4 border rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500"
                               style="background:var(--sa-muted);border-color:var(--sa-border);color:var(--sa-fg);">
                    </div>
                    <div>
                        <label class="block text-sm mb-2" style="color:var(--sa-muted-fg);">Date de fin</label>
                        <input type="datetime-local" name="ends_at" value="{{ old('ends_at') }}"
                               class="w-full h-12 px-4 border rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500"
                               style="background:var(--sa-muted);border-color:var(--sa-border);color:var(--sa-fg);">
                    </div>
                    <div>
                        <label class="block text-sm mb-2" style="color:var(--sa-muted-fg);">Ordre d'affichage</label>
                        <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0" max="9999"
                               class="w-full h-12 px-4 border rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500"
                               style="background:var(--sa-muted);border-color:var(--sa-border);color:var(--sa-fg);">
                        <p class="mt-1 text-xs" style="color:var(--sa-muted-fg);">0 = prioritaire</p>
                    </div>
                </div>
            </div>

            <!-- Options -->
            <div class="border shadow-sm rounded-xl p-6" style="background:var(--sa-card);border-color:var(--sa-border);">
                <label class="flex items-center justify-between p-4 rounded-xl cursor-pointer" style="background:var(--sa-muted);">
                    <div>
                        <span class="font-medium" style="color:var(--sa-fg);">Activer la bannière</span>
                        <p class="text-sm" style="color:var(--sa-muted-fg);">La bannière sera visible dans l'application</p>
                    </div>
                    <input type="checkbox" name="is_active" value="1" {{ old('is_active', true) ? 'checked' : '' }}
                           class="w-5 h-5 rounded border-neutral-500 text-primary-500 focus:ring-primary-500 bg-neutral-600">
                </label>
            </div>

            <!-- Submit -->
            <div class="flex items-center justify-end gap-4">
                <a href="{{ route('super-admin.promo-banners.index') }}" class="btn btn-ghost text-neutral-500">Annuler</a>
                <button type="submit" class="btn btn-primary">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Créer la bannière
                </button>
            </div>
        </div>
    </form>
</x-layouts.admin-super>
