<div>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-neutral-900">Plats</h1>
            <p class="text-neutral-500 mt-1">{{ $this->dishes->total() }} plats dans votre menu</p>
        </div>
        <a href="{{ route('restaurant.plats.create') }}" class="btn btn-primary px-6 py-3 flex items-center gap-2 hover:scale-105 active:scale-95 transition-all shadow-sm hover:shadow-md">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Nouveau plat
        </a>
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

    <!-- Filters -->
    <div class="card p-4 mb-6">
        <div class="flex flex-col md:flex-row gap-4">
            <!-- Search -->
            <div class="flex-1">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" 
                           wire:model.live.debounce.300ms="search"
                           class="input pl-10"
                           placeholder="Rechercher un plat...">
                </div>
            </div>

            <!-- Category Filter -->
            <div class="w-full md:w-48">
                <select wire:model.live="category" class="input">
                    <option value="">Toutes les catégories</option>
                    @foreach($this->categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Status Filter -->
            <div class="w-full md:w-40">
                <select wire:model.live="status" class="input">
                    <option value="">Tous les statuts</option>
                    <option value="available">Disponible</option>
                    <option value="unavailable">Indisponible</option>
                    <option value="featured">En vedette</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Dishes Grid -->
    @if($this->dishes->isEmpty())
        <div class="card p-12 text-center">
            <div class="w-16 h-16 bg-neutral-100 rounded-2xl flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <h3 class="text-lg font-semibold text-neutral-900 mb-2">Aucun plat trouvé</h3>
            <p class="text-neutral-500 mb-6">
                @if($search || $category || $status)
                    Aucun plat ne correspond à vos critères de recherche.
                @else
                    Créez votre premier plat pour le proposer à vos clients.
                @endif
            </p>
            @if($search || $category || $status)
                <button wire:click="$set('search', ''); $set('category', ''); $set('status', '')" class="btn btn-secondary px-4 py-2 flex items-center gap-2 shadow-sm hover:shadow-md transition-all">
                    Réinitialiser les filtres
                </button>
            @else
                <a href="{{ route('restaurant.plats.create') }}" class="btn btn-primary px-6 py-3 flex items-center gap-2 shadow-sm hover:shadow-md transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Créer un plat
                </a>
            @endif
        </div>
    @else
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($this->dishes as $dish)
                <div class="card overflow-hidden group {{ !$dish->is_available ? 'opacity-60' : '' }}">
                    <!-- Image -->
                    <div class="aspect-square bg-neutral-100 relative overflow-hidden">
                        @if($dish->image_path)
                            <img src="{{ Storage::url($dish->image_path) }}" 
                                 alt="{{ $dish->name }}" 
                                 class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        @else
                            <div class="w-full h-full flex items-center justify-center">
                                <svg class="w-16 h-16 text-neutral-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        @endif

                        <!-- Badges -->
                        <div class="absolute top-3 left-3 flex flex-col gap-2">
                            @if($dish->is_featured)
                                <span class="badge bg-accent-500 text-white">⭐ En vedette</span>
                            @endif
                            @if($dish->is_new)
                                <span class="badge bg-secondary-500 text-white">Nouveau</span>
                            @endif
                            @if(!$dish->is_available)
                                <span class="badge bg-neutral-500 text-white">Indisponible</span>
                            @endif
                        </div>

                        <!-- Quick Actions Overlay -->
                        <div class="absolute inset-0 bg-black/60 opacity-0 group-hover:opacity-100 transition-opacity flex items-center justify-center gap-3">
                            <a href="{{ route('restaurant.plats.edit', $dish) }}" 
                               class="p-3 bg-white rounded-xl hover:bg-neutral-100 transition-colors"
                               title="Modifier">
                                <svg class="w-5 h-5 text-neutral-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            <button wire:click="toggleAvailability({{ $dish->id }})" 
                                    wire:loading.attr="disabled"
                                    wire:target="toggleAvailability({{ $dish->id }})"
                                    class="p-3 bg-white rounded-xl hover:bg-neutral-100 active:scale-95 transition-all disabled:opacity-50"
                                    title="{{ $dish->is_available ? 'Marquer indisponible' : 'Marquer disponible' }}">
                                @if($dish->is_available)
                                    <svg class="w-5 h-5 text-secondary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                @else
                                    <svg class="w-5 h-5 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                    </svg>
                                @endif
                            </button>
                            <button wire:click="delete({{ $dish->id }})" 
                                    wire:confirm="Êtes-vous sûr de vouloir supprimer ce plat ?"
                                    wire:loading.attr="disabled"
                                    wire:target="delete({{ $dish->id }})"
                                    class="p-3 bg-white rounded-xl hover:bg-red-50 active:scale-95 transition-all disabled:opacity-50"
                                    title="Supprimer">
                                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="p-4">
                        <div class="flex items-start justify-between gap-2 mb-2">
                            <div>
                                <h3 class="font-semibold text-neutral-900 line-clamp-1">{{ $dish->name }}</h3>
                                @if($dish->category)
                                    <p class="text-sm text-neutral-500">{{ $dish->category->name }}</p>
                                @endif
                            </div>
                            <button wire:click="toggleFeatured({{ $dish->id }})" 
                                    wire:loading.attr="disabled"
                                    wire:target="toggleFeatured({{ $dish->id }})"
                                    class="p-1 rounded hover:bg-neutral-100 active:scale-95 transition-all disabled:opacity-50"
                                    title="{{ $dish->is_featured ? 'Retirer de la vedette' : 'Mettre en vedette' }}">
                                @if($dish->is_featured)
                                    <svg class="w-5 h-5 text-accent-500" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                    </svg>
                                @else
                                    <svg class="w-5 h-5 text-neutral-300 hover:text-accent-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                    </svg>
                                @endif
                            </button>
                        </div>
                        
                        @if($dish->description)
                            <p class="text-sm text-neutral-500 line-clamp-2 mb-3">{{ $dish->description }}</p>
                        @endif

                        <div class="flex items-center justify-between pt-3 border-t border-neutral-100">
                            <span class="text-lg font-bold text-primary-600">{{ number_format($dish->price, 0, ',', ' ') }} F</span>
                            <a href="{{ route('restaurant.plats.edit', $dish) }}" class="text-sm text-neutral-500 hover:text-primary-600">
                                Modifier →
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $this->dishes->links() }}
        </div>
    @endif
</div>

