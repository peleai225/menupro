<div>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-neutral-900">Catégories</h1>
            <p class="text-neutral-500 mt-1">Organisez vos plats par catégorie</p>
        </div>
        <button wire:click="openModal" 
                class="btn btn-primary px-6 py-3 flex items-center gap-2 hover:scale-105 active:scale-95 transition-all shadow-sm hover:shadow-md">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Nouvelle catégorie
        </button>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
        <div x-data="{ show: true }" 
             x-show="show"
             x-init="setTimeout(() => show = false, 5000)"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-2"
             class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-700 flex items-center justify-between gap-3 shadow-sm">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
            <button @click="show = false" class="text-emerald-600 hover:text-emerald-800">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div x-data="{ show: true }" 
             x-show="show"
             x-init="setTimeout(() => show = false, 5000)"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-2"
             class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 flex items-center justify-between gap-3 shadow-sm">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                <span class="font-medium">{{ session('error') }}</span>
            </div>
            <button @click="show = false" class="text-red-600 hover:text-red-800">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    @endif

    <!-- Categories Grid -->
    @if($this->categories->isEmpty())
        <div class="card p-12 text-center">
            <div class="w-16 h-16 bg-neutral-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-neutral-900 mb-2">Aucune catégorie</h3>
            <p class="text-neutral-500 mb-6">Créez votre première catégorie pour organiser vos plats</p>
            <button wire:click="openModal" class="btn btn-primary px-6 py-3 flex items-center gap-2 mx-auto shadow-sm hover:shadow-md transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Créer une catégorie
            </button>
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6" 
             x-data 
             x-init="Sortable.create($el, { 
                 animation: 150, 
                 ghostClass: 'opacity-50',
                 onEnd: function(evt) {
                     const items = [...evt.from.children].map(el => el.dataset.id);
                     $wire.updateOrder(items);
                 }
             })">
            @foreach($this->categories as $category)
                <div data-id="{{ $category->id }}" 
                     class="card overflow-hidden cursor-move hover:shadow-lg transition-shadow {{ !$category->is_active ? 'opacity-60' : '' }}">
                    <!-- Image -->
                    <div class="aspect-video bg-neutral-100 relative">
                        @if($category->image_path)
                            <img src="{{ Storage::url($category->image_path) }}" 
                                 alt="{{ $category->name }}" 
                                 class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center">
                                <svg class="w-12 h-12 text-neutral-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        @endif

                        <!-- Status Badge -->
                        @if(!$category->is_active)
                            <div class="absolute top-3 right-3">
                                <span class="badge bg-neutral-500 text-white">Masqué</span>
                            </div>
                        @endif
                    </div>

                    <!-- Content -->
                    <div class="p-4">
                        <div class="flex items-start justify-between">
                            <div>
                                <h3 class="font-semibold text-neutral-900">{{ $category->name }}</h3>
                                @if($category->description)
                                    <p class="text-sm text-neutral-500 mt-1 line-clamp-2">{{ $category->description }}</p>
                                @endif
                                <p class="text-sm text-primary-600 font-medium mt-2">
                                    {{ $category->dishes_count }} {{ Str::plural('plat', $category->dishes_count) }}
                                </p>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-2 mt-4 pt-4 border-t border-neutral-100">
                            <button wire:click="openModal({{ $category->id }})" 
                                    wire:loading.attr="disabled"
                                    class="flex-1 btn btn-secondary px-4 py-2 text-sm hover:bg-neutral-700 active:scale-95 transition-all disabled:opacity-50 shadow-sm hover:shadow-md">
                                <span wire:loading.remove wire:target="openModal({{ $category->id }})">Modifier</span>
                                <span wire:loading wire:target="openModal({{ $category->id }})">
                                    <svg class="animate-spin h-4 w-4 mx-auto" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                </span>
                            </button>
                            <button wire:click="toggleActive({{ $category->id }})" 
                                    wire:loading.attr="disabled"
                                    class="p-2 rounded-lg hover:bg-neutral-100 active:scale-95 transition-all disabled:opacity-50"
                                    title="{{ $category->is_active ? 'Masquer' : 'Afficher' }}">
                                @if($category->is_active)
                                    <svg class="w-5 h-5 text-secondary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                @else
                                    <svg class="w-5 h-5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                    </svg>
                                @endif
                            </button>
                            <button wire:click="delete({{ $category->id }})" 
                                    wire:confirm="Êtes-vous sûr de vouloir supprimer cette catégorie ?"
                                    wire:loading.attr="disabled"
                                    class="p-2 rounded-lg hover:bg-red-50 active:scale-95 text-red-500 transition-all disabled:opacity-50"
                                    title="Supprimer">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Modal -->
    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" 
             x-data="{ show: @entangle('showModal') }"
             x-show="show"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             x-init="document.body.classList.add('overflow-hidden')"
             x-on:remove="document.body.classList.remove('overflow-hidden')"
             @keydown.escape.window="$wire.closeModal()">
            <div class="flex min-h-screen items-center justify-center p-4">
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity" 
                     wire:click="closeModal"
                     @click.self="$wire.closeModal()"></div>

                <!-- Modal Content -->
                <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-2xl"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-95"
                     x-transition:enter-end="opacity-100 scale-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100 scale-100"
                     x-transition:leave-end="opacity-0 scale-95">
                    <form wire:submit.prevent="save">
                        <!-- Header -->
                        <div class="p-6 border-b border-neutral-100">
                            <h2 class="text-xl font-bold text-neutral-900">
                                {{ $editingId ? 'Modifier la catégorie' : 'Nouvelle catégorie' }}
                            </h2>
                        </div>

                        <!-- Body -->
                        <div class="p-6 space-y-6">
                            <!-- Name -->
                            <div>
                                <label for="name" class="block text-sm font-medium text-neutral-700 mb-2">
                                    Nom de la catégorie *
                                </label>
                                <input type="text" 
                                       id="name" 
                                       wire:model.blur="name"
                                       class="input @error('name') border-red-500 focus:ring-red-500 @enderror"
                                       placeholder="Ex: Entrées, Plats principaux, Desserts...">
                                @error('name')
                                    <p class="mt-1 text-sm text-red-600 flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <!-- Description -->
                            <div>
                                <label for="description" class="block text-sm font-medium text-neutral-700 mb-2">
                                    Description
                                </label>
                                <textarea id="description" 
                                          wire:model.blur="description"
                                          rows="3"
                                          class="input @error('description') border-red-500 focus:ring-red-500 @enderror"
                                          placeholder="Description optionnelle de la catégorie..."></textarea>
                                @error('description')
                                    <p class="mt-1 text-sm text-red-600 flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <!-- Image -->
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 mb-2">
                                    Image
                                </label>
                                <div class="flex items-center gap-4">
                                    @if($image)
                                        <img src="{{ $image->temporaryUrl() }}" class="w-20 h-20 object-cover rounded-xl">
                                    @elseif($existingImage)
                                        <img src="{{ Storage::url($existingImage) }}" class="w-20 h-20 object-cover rounded-xl">
                                    @else
                                        <div class="w-20 h-20 bg-neutral-100 rounded-xl flex items-center justify-center">
                                            <svg class="w-8 h-8 text-neutral-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                    @endif
                                    <div class="flex-1">
                                        <label class="btn btn-secondary cursor-pointer inline-flex px-4 py-2 items-center gap-2 shadow-sm hover:shadow-md transition-all">
                                            <input type="file" wire:model="image" accept="image/*" class="hidden">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                            </svg>
                                            Changer l'image
                                        </label>
                                        <p class="text-xs text-neutral-500 mt-2">JPG, PNG. Max 2 Mo</p>
                                    </div>
                                </div>
                                @error('image')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>

                            <!-- Active Toggle -->
                            <div class="flex items-center justify-between">
                                <div>
                                    <p class="font-medium text-neutral-900">Visible sur le menu</p>
                                    <p class="text-sm text-neutral-500">Afficher cette catégorie aux clients</p>
                                </div>
                                <button type="button"
                                        wire:click="$toggle('is_active')"
                                        class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors {{ $is_active ? 'bg-primary-500' : 'bg-neutral-200' }}">
                                    <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                                </button>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="p-6 border-t border-neutral-100 flex items-center justify-end gap-3">
                            <button type="button" 
                                    wire:click="closeModal"
                                    wire:loading.attr="disabled"
                                    class="btn btn-secondary px-6 py-3 flex items-center gap-2 hover:bg-neutral-700 active:scale-95 transition-all disabled:opacity-50 shadow-sm hover:shadow-md">
                                Annuler
                            </button>
                            <button type="submit" 
                                    wire:loading.attr="disabled"
                                    class="btn btn-primary px-6 py-3 flex items-center gap-2 hover:bg-primary-600 active:scale-95 transition-all disabled:opacity-50 disabled:cursor-not-allowed shadow-sm hover:shadow-md">
                                <span wire:loading.remove wire:target="save">
                                    {{ $editingId ? 'Enregistrer' : 'Créer' }}
                                </span>
                                <span wire:loading wire:target="save" class="flex items-center gap-2">
                                    <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                    </svg>
                                    <span>Enregistrement...</span>
                                </span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
@endpush

