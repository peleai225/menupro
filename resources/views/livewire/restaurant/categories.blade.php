<div x-data="{ loaded: false }" x-init="setTimeout(() => loaded = true, 100)">
    <!-- Header with modern design -->
    <div x-show="loaded" x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform -translate-y-4"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl lg:text-3xl font-bold text-neutral-900 flex items-center gap-3">
                <span class="w-10 h-10 bg-gradient-to-br from-primary-400 to-primary-600 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </span>
                Catégories
            </h1>
            <p class="text-neutral-500 mt-1">Organisez vos plats par catégorie pour un menu clair et attractif</p>
        </div>
        <button wire:click="openModal" 
                class="btn btn-primary btn-glow px-6 py-3 flex items-center gap-2 group">
            <svg class="w-5 h-5 transition-transform group-hover:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
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
             x-transition:enter-start="opacity-0 transform -translate-y-2"
             x-transition:enter-end="opacity-100 transform translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             class="mb-6 p-4 bg-gradient-to-r from-secondary-50 to-secondary-100 border border-secondary-200 rounded-2xl shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-secondary-500 rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <span class="font-medium text-secondary-800 flex-1">{{ session('success') }}</span>
                <button @click="show = false" class="p-1.5 hover:bg-secondary-200 rounded-lg transition-colors">
                    <svg class="w-5 h-5 text-secondary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    @endif

    @if(session('error'))
        <div x-data="{ show: true }" 
             x-show="show"
             x-init="setTimeout(() => show = false, 5000)"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 transform -translate-y-2"
             x-transition:enter-end="opacity-100 transform translate-y-0"
             class="mb-6 p-4 bg-gradient-to-r from-red-50 to-red-100 border border-red-200 rounded-2xl shadow-sm">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-red-500 rounded-xl flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </div>
                <span class="font-medium text-red-800 flex-1">{{ session('error') }}</span>
                <button @click="show = false" class="p-1.5 hover:bg-red-200 rounded-lg transition-colors">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    @endif

    <!-- Categories Grid -->
    @if($this->categories->isEmpty())
        <div x-show="loaded" x-transition:enter="transition ease-out duration-500 delay-200"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100"
             class="card">
            <div class="empty-state py-16">
                <div class="w-24 h-24 mx-auto bg-gradient-to-br from-primary-100 to-primary-200 rounded-full flex items-center justify-center mb-6">
                    <svg class="w-12 h-12 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
                <h3 class="empty-state-title text-xl">Aucune catégorie</h3>
                <p class="empty-state-description text-base">
                    Créez votre première catégorie pour organiser vos plats et offrir une meilleure expérience à vos clients.
                </p>
                <button wire:click="openModal" class="btn btn-primary btn-glow px-8 py-3 text-base">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Créer ma première catégorie
                </button>
            </div>
        </div>
    @else
        <!-- Info banner -->
        <div x-show="loaded" x-transition:enter="transition ease-out duration-300 delay-100"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             class="hidden lg:flex items-center gap-3 px-4 py-3 bg-blue-50 border border-blue-200 rounded-xl text-blue-700 text-sm mb-6">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <span><strong>Astuce :</strong> Glissez-déposez les cartes pour réorganiser l'ordre d'affichage des catégories sur votre menu.</span>
        </div>

        <div x-show="loaded" x-transition:enter="transition ease-out duration-300 delay-200"
             x-transition:enter-start="opacity-0 transform translate-y-4"
             x-transition:enter-end="opacity-100 transform translate-y-0"
             class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6" 
             x-data 
             x-init="(() => {
                 if (window.innerWidth >= 1024) {
                     Sortable.create($el, { 
                         animation: 200,
                         easing: 'cubic-bezier(0.25, 1, 0.5, 1)',
                         ghostClass: 'opacity-30',
                         dragClass: 'shadow-2xl',
                         chosenClass: 'ring-2 ring-primary-500 ring-offset-2',
                         onEnd: function(evt) {
                             const items = [...evt.from.children].map(el => el.dataset.id);
                             $wire.updateOrder(items);
                         }
                     });
                 }
             })()">
            @foreach($this->categories as $index => $category)
                <div data-id="{{ $category->id }}" 
                     class="card-interactive overflow-hidden group {{ !$category->is_active ? 'ring-2 ring-neutral-300 ring-dashed' : '' }}"
                     style="animation: fade-slide-up 0.4s ease-out {{ $index * 100 }}ms both;">
                    <!-- Content -->
                    <div class="p-5">
                        <!-- Header with icon, drag handle and status -->
                        <div class="flex items-start justify-between mb-3">
                            <div class="flex items-start gap-3 flex-1 min-w-0">
                                <!-- Category Icon -->
                                <div class="w-12 h-12 rounded-xl bg-gradient-to-br from-primary-100 to-primary-200 flex items-center justify-center flex-shrink-0 group-hover:scale-110 transition-transform">
                                    <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                    </svg>
                                </div>
                                <div class="flex-1 min-w-0">
                                    <h3 class="font-bold text-lg text-neutral-900 truncate group-hover:text-primary-600 transition-colors">
                                        {{ $category->name }}
                                    </h3>
                                    @if($category->description)
                                        <p class="text-sm text-neutral-500 mt-1 line-clamp-2">{{ $category->description }}</p>
                                    @endif
                                </div>
                            </div>
                            <div class="flex items-center gap-2 flex-shrink-0">
                                @if(!$category->is_active)
                                    <span class="badge bg-neutral-800 text-white">Masqué</span>
                                @endif
                                <!-- Drag handle -->
                                <div class="hidden lg:flex items-center gap-1 px-2 py-1 bg-neutral-100 rounded-lg text-neutral-400 text-xs opacity-0 group-hover:opacity-100 transition-opacity cursor-grab">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8h16M4 16h16"/>
                                    </svg>
                                </div>
                            </div>
                        </div>

                        <!-- Stats -->
                        <div class="flex items-center gap-4 mb-4">
                            <div class="flex items-center gap-2 text-sm">
                                <div class="w-8 h-8 rounded-lg bg-primary-100 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                    </svg>
                                </div>
                                <span class="font-semibold text-neutral-900">{{ $category->dishes_count }}</span>
                                <span class="text-neutral-500">{{ Str::plural('plat', $category->dishes_count) }}</span>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-2 pt-4 border-t border-neutral-100">
                            <button wire:click="openModal({{ $category->id }})" 
                                    wire:loading.attr="disabled"
                                    class="flex-1 btn btn-secondary py-2.5 text-sm group/btn">
                                <svg wire:loading.remove wire:target="openModal({{ $category->id }})" class="w-4 h-4 transition-transform group-hover/btn:rotate-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                                <svg wire:loading wire:target="openModal({{ $category->id }})" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                <span wire:loading.remove wire:target="openModal({{ $category->id }})">Modifier</span>
                            </button>
                            
                            <button wire:click="toggleActive({{ $category->id }})" 
                                    wire:loading.attr="disabled"
                                    class="p-2.5 rounded-xl hover:bg-neutral-100 transition-all bounce-click"
                                    title="{{ $category->is_active ? 'Masquer la catégorie' : 'Afficher la catégorie' }}">
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
                                    wire:confirm="Êtes-vous sûr de vouloir supprimer cette catégorie ? Les plats associés ne seront pas supprimés."
                                    wire:loading.attr="disabled"
                                    class="p-2.5 rounded-xl hover:bg-red-50 text-red-500 transition-all bounce-click"
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

    <!-- Modal - Enhanced -->
    @if($showModal)
        <div class="fixed inset-0 z-50 overflow-y-auto" 
             x-data="{ show: @entangle('showModal') }"
             x-show="show"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-init="document.body.classList.add('overflow-hidden')"
             x-on:remove="document.body.classList.remove('overflow-hidden')"
             @keydown.escape.window="document.body.classList.remove('overflow-hidden'); $wire.closeModal()"
            <div class="flex min-h-screen items-center justify-center p-4">
                <!-- Backdrop -->
                <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" 
                     @click="document.body.classList.remove('overflow-hidden'); $wire.closeModal()"></div>

                <!-- Modal Content -->
                <div class="relative w-full max-w-lg bg-white rounded-3xl shadow-2xl overflow-hidden"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0 scale-95 translate-y-4"
                     x-transition:enter-end="opacity-100 scale-100 translate-y-0">
                    <form wire:submit.prevent="save">
                        <!-- Header with gradient -->
                        <div class="bg-gradient-to-r from-neutral-900 to-neutral-800 p-6 text-white">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div class="w-10 h-10 bg-white/10 rounded-xl flex items-center justify-center">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                                        </svg>
                                    </div>
                                    <h2 class="text-xl font-bold">
                                        {{ $editingId ? 'Modifier la catégorie' : 'Nouvelle catégorie' }}
                                    </h2>
                                </div>
                                <button type="button" @click="document.body.classList.remove('overflow-hidden'); $wire.closeModal()" 
                                        class="p-2 hover:bg-white/10 rounded-xl transition-colors">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            </div>
                        </div>

                        <!-- Body -->
                        <div class="p-6 space-y-6">
                            <!-- Name -->
                            <div>
                                <label for="name" class="block text-sm font-semibold text-neutral-700 mb-2">
                                    Nom de la catégorie <span class="text-red-500">*</span>
                                </label>
                                <input type="text" 
                                       id="name" 
                                       wire:model.blur="name"
                                       class="input h-12 @error('name') border-red-500 focus:ring-red-500 @enderror"
                                       placeholder="Ex: Entrées, Plats principaux, Desserts...">
                                @error('name')
                                    <p class="mt-2 text-sm text-red-600 flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <!-- Description -->
                            <div>
                                <label for="description" class="block text-sm font-semibold text-neutral-700 mb-2">
                                    Description <span class="text-neutral-400 font-normal">(optionnel)</span>
                                </label>
                                <textarea id="description" 
                                          wire:model.blur="description"
                                          rows="3"
                                          class="input @error('description') border-red-500 focus:ring-red-500 @enderror"
                                          placeholder="Une brève description de cette catégorie..."></textarea>
                                @error('description')
                                    <p class="mt-2 text-sm text-red-600 flex items-center gap-1">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                        {{ $message }}
                                    </p>
                                @enderror
                            </div>

                            <!-- Active Toggle -->
                            <div class="flex items-center justify-between p-4 bg-neutral-50 rounded-2xl">
                                <div>
                                    <p class="font-semibold text-neutral-900">Visible sur le menu</p>
                                    <p class="text-sm text-neutral-500">Les clients peuvent voir cette catégorie</p>
                                </div>
                                <button type="button"
                                        wire:click="$toggle('is_active')"
                                        class="relative inline-flex h-7 w-12 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 {{ $is_active ? 'bg-primary-500' : 'bg-neutral-300' }}">
                                    <span class="inline-block h-5 w-5 transform rounded-full bg-white shadow-md transition-transform {{ $is_active ? 'translate-x-6' : 'translate-x-1' }}"></span>
                                </button>
                            </div>
                        </div>

                        <!-- Footer -->
                        <div class="p-6 border-t border-neutral-100 flex items-center justify-end gap-3 bg-neutral-50">
                            <button type="button" 
                                    wire:click="closeModal"
                                    class="btn btn-ghost px-6 py-2.5">
                                Annuler
                            </button>
                            <button type="submit" 
                                    wire:loading.attr="disabled"
                                    class="btn btn-primary btn-glow px-6 py-2.5">
                                <svg wire:loading.remove wire:target="save" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                <svg wire:loading wire:target="save" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                <span wire:loading.remove wire:target="save">{{ $editingId ? 'Enregistrer' : 'Créer la catégorie' }}</span>
                                <span wire:loading wire:target="save">Enregistrement...</span>
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
