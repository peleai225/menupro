<div x-data="{ loaded: false, viewMode: 'grid' }" x-init="setTimeout(() => loaded = true, 100)">
    <!-- Header -->
    <div x-show="loaded" x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 transform -translate-y-4"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl lg:text-3xl font-bold text-neutral-900 flex items-center gap-3">
                <span class="w-10 h-10 bg-gradient-to-br from-primary-400 to-primary-600 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </span>
                Plats
            </h1>
            <p class="text-neutral-500 mt-1">
                <span class="font-semibold text-neutral-900">{{ $this->dishes->total() }}</span> plats dans votre menu
            </p>
        </div>
        <div class="flex items-center gap-3">
            <!-- View Mode Toggle -->
            <div class="hidden sm:flex bg-neutral-100 rounded-xl p-1">
                <button @click="viewMode = 'grid'" 
                        :class="viewMode === 'grid' ? 'bg-white shadow-sm text-neutral-900' : 'text-neutral-500 hover:text-neutral-700'"
                        class="p-2 rounded-lg transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"/>
                    </svg>
                </button>
                <button @click="viewMode = 'list'" 
                        :class="viewMode === 'list' ? 'bg-white shadow-sm text-neutral-900' : 'text-neutral-500 hover:text-neutral-700'"
                        class="p-2 rounded-lg transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 10h16M4 14h16M4 18h16"/>
                    </svg>
                </button>
            </div>
            
            @if(auth()->user()->canManageRestaurant())
            <a href="{{ route('restaurant.plats.create') }}" class="btn btn-primary btn-glow px-6 py-3 flex items-center gap-2 group">
                <svg class="w-5 h-5 transition-transform group-hover:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Nouveau plat
            </a>
            @endif
        </div>
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

    <!-- Filters -->
    <div x-show="loaded" x-transition:enter="transition ease-out duration-300 delay-100"
         x-transition:enter-start="opacity-0 transform translate-y-4"
         x-transition:enter-end="opacity-100 transform translate-y-0"
         class="card p-4 mb-6">
        <div class="flex flex-col lg:flex-row gap-4">
            <!-- Search -->
            <div class="flex-1">
                <div class="relative group">
                    <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-neutral-400 group-focus-within:text-primary-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" 
                           wire:model.live.debounce.300ms="search"
                           class="input pl-12 h-12"
                           placeholder="Rechercher un plat...">
                    <div wire:loading wire:target="search" class="absolute right-4 top-1/2 -translate-y-1/2">
                        <svg class="animate-spin h-5 w-5 text-primary-500" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                    </div>
                </div>
            </div>

            <!-- Category Filter -->
            <div class="w-full lg:w-52">
                <select wire:model.live="category" class="input h-12">
                    <option value="">Toutes les catégories</option>
                    @foreach($this->categories as $cat)
                        <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                    @endforeach
                </select>
            </div>

            <!-- Status Filter -->
            <div class="w-full lg:w-44">
                <select wire:model.live="status" class="input h-12">
                    <option value="">Tous les statuts</option>
                    <option value="available">Disponible</option>
                    <option value="unavailable">Indisponible</option>
                    <option value="featured">En vedette</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Dishes -->
    @if($this->dishes->isEmpty())
        <div x-show="loaded" x-transition:enter="transition ease-out duration-500 delay-200"
             x-transition:enter-start="opacity-0 transform scale-95"
             x-transition:enter-end="opacity-100 transform scale-100"
             class="card">
            <div class="empty-state py-16">
                <div class="w-24 h-24 mx-auto bg-gradient-to-br from-primary-100 to-primary-200 rounded-full flex items-center justify-center mb-6">
                    <svg class="w-12 h-12 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <h3 class="empty-state-title text-xl">
                    @if($search || $category || $status)
                        Aucun plat trouvé
                    @else
                        Aucun plat dans votre menu
                    @endif
                </h3>
                <p class="empty-state-description text-base">
                    @if($search || $category || $status)
                        Aucun plat ne correspond à vos critères de recherche.
                    @else
                        Créez votre premier plat pour le proposer à vos clients.
                    @endif
                </p>
                @if($search || $category || $status)
                    <button wire:click="$set('search', ''); $set('category', ''); $set('status', '')" 
                            class="btn btn-secondary px-6 py-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Réinitialiser les filtres
                    </button>
                @else
                    <a href="{{ route('restaurant.plats.create') }}" class="btn btn-primary btn-glow px-8 py-3 text-base">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Créer mon premier plat
                    </a>
                @endif
            </div>
        </div>
    @else
        <!-- Grid View -->
        <div x-show="loaded && viewMode === 'grid'" 
             x-transition:enter="transition ease-out duration-300 delay-200"
             x-transition:enter-start="opacity-0 transform translate-y-4"
             x-transition:enter-end="opacity-100 transform translate-y-0"
             class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
            @foreach($this->dishes as $index => $dish)
                <div class="card-interactive overflow-hidden group {{ !$dish->is_available ? 'ring-2 ring-neutral-300 ring-dashed' : '' }}"
                     style="animation: fade-slide-up 0.4s ease-out {{ min($index * 50, 400) }}ms both;">
                    <!-- Image -->
                    <div class="aspect-square bg-gradient-to-br from-neutral-100 to-neutral-200 relative overflow-hidden">
                        @if($dish->image_path)
                            <img src="{{ Storage::url($dish->image_path) }}" 
                                 alt="{{ $dish->name }}" 
                                 class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-110">
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
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-gradient-to-r from-yellow-400 to-orange-500 text-white text-xs font-bold rounded-lg shadow-lg">
                                    <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                    Vedette
                                </span>
                            @endif
                            @if($dish->is_new)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-gradient-to-r from-secondary-400 to-secondary-600 text-white text-xs font-bold rounded-lg shadow-lg">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                                    Nouveau
                                </span>
                            @endif
                            @if(!$dish->is_available)
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 bg-neutral-800 text-white text-xs font-bold rounded-lg">
                                    Indisponible
                                </span>
                            @endif
                        </div>

                        <!-- Price Badge -->
                        <div class="absolute bottom-3 right-3">
                            <span class="px-3 py-1.5 bg-white/95 backdrop-blur rounded-xl text-lg font-bold text-primary-600 shadow-lg">
                                {{ number_format($dish->price, 0, ',', ' ') }} F
                            </span>
                        </div>

                        <!-- Quick Actions Overlay -->
                        <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/40 to-transparent opacity-0 group-hover:opacity-100 transition-all duration-300 flex items-end justify-center pb-16">
                            <div class="flex items-center gap-2">
                                <a href="{{ route('restaurant.plats.edit', $dish) }}" 
                                   class="p-3 bg-white rounded-xl hover:bg-primary-50 hover:scale-110 transition-all shadow-lg bounce-click"
                                   title="Modifier">
                                    <svg class="w-5 h-5 text-neutral-700" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </a>
                                <button wire:click="toggleAvailability({{ $dish->id }})" 
                                        wire:loading.attr="disabled"
                                        class="p-3 bg-white rounded-xl hover:bg-secondary-50 hover:scale-110 transition-all shadow-lg bounce-click"
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
                                        class="p-3 bg-white rounded-xl hover:bg-red-50 hover:scale-110 transition-all shadow-lg bounce-click"
                                        title="Supprimer">
                                    <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Content -->
                    <div class="p-4">
                        <div class="flex items-start justify-between gap-2 mb-2">
                            <div class="min-w-0">
                                <h3 class="font-bold text-neutral-900 truncate group-hover:text-primary-600 transition-colors">{{ $dish->name }}</h3>
                                @if($dish->category)
                                    <p class="text-sm text-neutral-500 flex items-center gap-1">
                                        <span class="w-2 h-2 rounded-full bg-primary-400"></span>
                                        {{ $dish->category->name }}
                                    </p>
                                @endif
                            </div>
                            <button wire:click="toggleFeatured({{ $dish->id }})" 
                                    wire:loading.attr="disabled"
                                    class="p-1.5 rounded-lg hover:bg-neutral-100 transition-all bounce-click flex-shrink-0"
                                    title="{{ $dish->is_featured ? 'Retirer de la vedette' : 'Mettre en vedette' }}">
                                @if($dish->is_featured)
                                    <svg class="w-5 h-5 text-yellow-500" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                    </svg>
                                @else
                                    <svg class="w-5 h-5 text-neutral-300 hover:text-yellow-400 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                    </svg>
                                @endif
                            </button>
                        </div>
                        
                        @if($dish->description)
                            <p class="text-sm text-neutral-500 line-clamp-2 mb-3">{{ $dish->description }}</p>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>

        <!-- List View -->
        <div x-show="loaded && viewMode === 'list'" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             class="card overflow-hidden">
            <div class="divide-y divide-neutral-100">
                @foreach($this->dishes as $index => $dish)
                    <div class="flex items-center gap-4 p-4 hover:bg-neutral-50 transition-colors group {{ !$dish->is_available ? 'opacity-60' : '' }}"
                         style="animation: fade-slide-up 0.3s ease-out {{ min($index * 30, 300) }}ms both;">
                        <!-- Image -->
                        <div class="w-20 h-20 rounded-xl overflow-hidden flex-shrink-0 bg-neutral-100">
                            @if($dish->image_path)
                                <img src="{{ Storage::url($dish->image_path) }}" 
                                     alt="{{ $dish->name }}" 
                                     class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center">
                                    <svg class="w-8 h-8 text-neutral-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            @endif
                        </div>

                        <!-- Info -->
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <h3 class="font-bold text-neutral-900 truncate">{{ $dish->name }}</h3>
                                @if($dish->is_featured)
                                    <svg class="w-4 h-4 text-yellow-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                                @endif
                                @if($dish->is_new)
                                    <span class="px-2 py-0.5 bg-secondary-100 text-secondary-700 text-xs font-bold rounded">Nouveau</span>
                                @endif
                            </div>
                            @if($dish->category)
                                <p class="text-sm text-neutral-500">{{ $dish->category->name }}</p>
                            @endif
                            @if($dish->description)
                                <p class="text-sm text-neutral-400 truncate mt-1">{{ $dish->description }}</p>
                            @endif
                        </div>

                        <!-- Price -->
                        <div class="text-right flex-shrink-0">
                            <p class="text-lg font-bold text-primary-600">{{ number_format($dish->price, 0, ',', ' ') }} F</p>
                            @if(!$dish->is_available)
                                <span class="text-xs text-neutral-500">Indisponible</span>
                            @endif
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-1 opacity-0 group-hover:opacity-100 transition-opacity flex-shrink-0">
                            <a href="{{ route('restaurant.plats.edit', $dish) }}" 
                               class="p-2 rounded-lg hover:bg-neutral-200 transition-colors"
                               title="Modifier">
                                <svg class="w-5 h-5 text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            <button wire:click="toggleAvailability({{ $dish->id }})" 
                                    class="p-2 rounded-lg hover:bg-neutral-200 transition-colors"
                                    title="{{ $dish->is_available ? 'Marquer indisponible' : 'Marquer disponible' }}">
                                @if($dish->is_available)
                                    <svg class="w-5 h-5 text-secondary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                    </svg>
                                @else
                                    <svg class="w-5 h-5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242"/>
                                    </svg>
                                @endif
                            </button>
                            <button wire:click="toggleFeatured({{ $dish->id }})" 
                                    class="p-2 rounded-lg hover:bg-neutral-200 transition-colors"
                                    title="{{ $dish->is_featured ? 'Retirer vedette' : 'Mettre en vedette' }}">
                                @if($dish->is_featured)
                                    <svg class="w-5 h-5 text-yellow-500" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                    </svg>
                                @else
                                    <svg class="w-5 h-5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                    </svg>
                                @endif
                            </button>
                            <button wire:click="delete({{ $dish->id }})" 
                                    wire:confirm="Êtes-vous sûr de vouloir supprimer ce plat ?"
                                    class="p-2 rounded-lg hover:bg-red-50 transition-colors"
                                    title="Supprimer">
                                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Pagination -->
        <div class="mt-8">
            {{ $this->dishes->links() }}
        </div>
    @endif
</div>
