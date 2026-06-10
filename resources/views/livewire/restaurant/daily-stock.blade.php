<div x-data="{ confirmReset: false }">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl lg:text-3xl font-bold text-neutral-900 flex items-center gap-3">
                <span class="w-10 h-10 bg-gradient-to-br from-emerald-400 to-emerald-600 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </span>
                Stock Journalier
            </h1>
            <p class="text-neutral-500 mt-1">Definissez les portions disponibles pour chaque plat aujourd'hui.</p>
        </div>
        <div class="flex items-center gap-3">
            <button wire:click="saveAll"
                    wire:loading.attr="disabled"
                    class="btn btn-primary px-5 py-2.5 flex items-center gap-2">
                <svg wire:loading.remove wire:target="saveAll" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <svg wire:loading wire:target="saveAll" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                Sauvegarder tout
            </button>
            <button @click="confirmReset = true"
                    class="btn btn-outline text-red-600 border-red-200 hover:bg-red-50 hover:border-red-300 px-4 py-2.5 flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                <span class="hidden sm:inline">RAZ</span>
            </button>
        </div>
    </div>

    <!-- Flash Messages -->
    @if(session('stock_success'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 4000)"
             x-transition:leave="transition ease-in duration-200"
             class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-xl flex items-center justify-between">
            <div class="flex items-center gap-3">
                <div class="w-8 h-8 bg-emerald-500 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <span class="font-medium text-emerald-800">{{ session('stock_success') }}</span>
            </div>
            <button @click="show = false" class="text-emerald-500 hover:text-emerald-700">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    @endif

    <!-- Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="card p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-neutral-500 uppercase tracking-wide">Plats actifs</p>
                    <p class="text-2xl font-bold text-neutral-900 mt-1">{{ $this->stats['total_dishes'] }}</p>
                </div>
                <div class="w-10 h-10 bg-primary-100 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
            </div>
        </div>
        <div class="card p-4">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-neutral-500 uppercase tracking-wide">Stock suivi</p>
                    <p class="text-2xl font-bold text-neutral-900 mt-1">{{ $this->stats['tracked'] }}</p>
                </div>
                <div class="w-10 h-10 bg-emerald-100 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
        <div class="card p-4 {{ $this->stats['low_stock'] > 0 ? 'border-l-4 border-yellow-400' : '' }}">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-neutral-500 uppercase tracking-wide">Stock faible</p>
                    <p class="text-2xl font-bold {{ $this->stats['low_stock'] > 0 ? 'text-yellow-600' : 'text-neutral-900' }} mt-1">{{ $this->stats['low_stock'] }}</p>
                </div>
                <div class="w-10 h-10 bg-yellow-100 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
            </div>
        </div>
        <div class="card p-4 {{ $this->stats['out_of_stock'] > 0 ? 'border-l-4 border-red-400' : '' }}">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-xs font-medium text-neutral-500 uppercase tracking-wide">Epuise</p>
                    <p class="text-2xl font-bold {{ $this->stats['out_of_stock'] > 0 ? 'text-red-600' : 'text-neutral-900' }} mt-1">{{ $this->stats['out_of_stock'] }}</p>
                </div>
                <div class="w-10 h-10 bg-red-100 rounded-xl flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Info Banner -->
    <div class="mb-6 p-4 bg-blue-50 border border-blue-200 rounded-xl text-blue-700 text-sm flex items-start gap-3">
        <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <div>
            <strong>Comment ca marche :</strong> Activez le suivi pour un plat, puis entrez le nombre de portions disponibles.
            Quand un client commande, le stock diminue automatiquement. A 0, le plat est marque "Epuise" sur votre menu.
        </div>
    </div>

    <!-- Filters -->
    <div class="card p-4 mb-6">
        <div class="flex flex-col sm:flex-row gap-4">
            <div class="flex-1 relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text"
                       wire:model.live.debounce.300ms="search"
                       placeholder="Rechercher un plat..."
                       class="w-full h-10 pl-10 pr-4 bg-neutral-50 border border-neutral-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent">
            </div>
            <select wire:model.live="filterCategory"
                    class="h-10 px-4 bg-neutral-50 border border-neutral-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="">Toutes categories</option>
                @foreach($this->categories as $cat)
                    <option value="{{ $cat->id }}">{{ $cat->name }}</option>
                @endforeach
            </select>
            <label class="flex items-center gap-2 cursor-pointer px-3 py-2 bg-neutral-50 border border-neutral-200 rounded-lg hover:bg-neutral-100 transition-colors">
                <input type="checkbox" wire:model.live="showOnlyTracked" class="w-4 h-4 rounded border-neutral-300 text-primary-500 focus:ring-primary-500">
                <span class="text-sm text-neutral-700 whitespace-nowrap">Suivis uniquement</span>
            </label>
        </div>
    </div>

    <!-- Dishes Grid -->
    @if($this->dishes->isEmpty())
        <div class="card p-12 text-center">
            <div class="w-16 h-16 bg-neutral-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
            <p class="text-neutral-500 font-medium">Aucun plat trouve</p>
            <p class="text-sm text-neutral-400 mt-1">Ajoutez des plats a votre menu pour gerer le stock.</p>
        </div>
    @else
        <div class="space-y-3">
            @foreach($this->dishes->groupBy('category_id') as $categoryId => $categoryDishes)
                @php $categoryName = $categoryDishes->first()->category?->name ?? 'Sans categorie'; @endphp
                <div class="mb-6">
                    <h2 class="text-sm font-bold text-neutral-500 uppercase tracking-wide mb-3 flex items-center gap-2">
                        <span class="w-1.5 h-1.5 rounded-full bg-primary-500"></span>
                        {{ $categoryName }}
                        <span class="text-neutral-400 font-normal">({{ $categoryDishes->count() }})</span>
                    </h2>
                    <div class="space-y-2">
                        @foreach($categoryDishes as $dish)
                            @php
                                $isTracked = $this->quantities[$dish->id]['track'] ?? false;
                                $qty = $this->quantities[$dish->id]['qty'] ?? 0;
                                $statusClass = !$isTracked ? 'border-neutral-200' : ($qty <= 0 ? 'border-red-200 bg-red-50/30' : ($qty <= 5 ? 'border-yellow-200 bg-yellow-50/30' : 'border-emerald-200 bg-emerald-50/30'));
                            @endphp
                            <div class="card border {{ $statusClass }} p-4 transition-all duration-200" wire:key="dish-{{ $dish->id }}">
                                <div class="flex items-center gap-4">
                                    <!-- Image -->
                                    <div class="flex-shrink-0">
                                        @if($dish->image_url)
                                            <img src="{{ $dish->image_url }}" alt="{{ $dish->name }}" class="w-12 h-12 rounded-xl object-cover">
                                        @else
                                            <div class="w-12 h-12 bg-neutral-100 rounded-xl flex items-center justify-center">
                                                <svg class="w-6 h-6 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                                </svg>
                                            </div>
                                        @endif
                                    </div>

                                    <!-- Name & Price -->
                                    <div class="flex-1 min-w-0">
                                        <h3 class="font-semibold text-neutral-900 truncate">{{ $dish->name }}</h3>
                                        <p class="text-sm text-neutral-500">{{ number_format($dish->price, 0, ',', ' ') }} F</p>
                                    </div>

                                    <!-- Stock Controls -->
                                    <div class="flex items-center gap-3">
                                        <!-- Toggle tracking -->
                                        <button wire:click="toggleTrack({{ $dish->id }})"
                                                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors focus:outline-none focus:ring-2 focus:ring-primary-500 focus:ring-offset-2 {{ $isTracked ? 'bg-emerald-500' : 'bg-neutral-300' }}"
                                                title="{{ $isTracked ? 'Desactiver le suivi' : 'Activer le suivi' }}">
                                            <span class="inline-block h-4 w-4 transform rounded-full bg-white shadow transition-transform {{ $isTracked ? 'translate-x-6' : 'translate-x-1' }}"></span>
                                        </button>

                                        @if($isTracked)
                                            <!-- Quantity input -->
                                            <div class="flex items-center gap-1.5">
                                                <button wire:click="updateQuantity({{ $dish->id }}, {{ max(0, $qty - 1) }})"
                                                        class="w-8 h-8 rounded-lg bg-neutral-100 hover:bg-neutral-200 flex items-center justify-center text-neutral-600 transition-colors"
                                                        {{ $qty <= 0 ? 'disabled' : '' }}>
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                                    </svg>
                                                </button>
                                                <input type="number"
                                                       wire:model.lazy="quantities.{{ $dish->id }}.qty"
                                                       wire:change="updateQuantity({{ $dish->id }}, $event.target.value)"
                                                       min="0"
                                                       class="w-16 h-8 text-center text-sm font-bold border border-neutral-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent {{ $qty <= 0 ? 'text-red-600 bg-red-50' : ($qty <= 5 ? 'text-yellow-600 bg-yellow-50' : 'text-emerald-600 bg-emerald-50') }}">
                                                <button wire:click="updateQuantity({{ $dish->id }}, {{ $qty + 1 }})"
                                                        class="w-8 h-8 rounded-lg bg-neutral-100 hover:bg-neutral-200 flex items-center justify-center text-neutral-600 transition-colors">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                                    </svg>
                                                </button>
                                            </div>

                                            <!-- Quick presets -->
                                            <div class="hidden md:flex items-center gap-1">
                                                @foreach([10, 20, 50] as $preset)
                                                    <button wire:click="quickSet({{ $dish->id }}, {{ $preset }})"
                                                            class="px-2 py-1 text-xs font-medium rounded-md {{ $qty === $preset ? 'bg-primary-100 text-primary-700' : 'bg-neutral-100 text-neutral-600 hover:bg-neutral-200' }} transition-colors">
                                                        {{ $preset }}
                                                    </button>
                                                @endforeach
                                            </div>

                                            <!-- Status badge -->
                                            @if($qty <= 0)
                                                <span class="badge bg-red-100 text-red-700 text-xs px-2 py-0.5">Epuise</span>
                                            @elseif($qty <= 5)
                                                <span class="badge bg-yellow-100 text-yellow-700 text-xs px-2 py-0.5">Faible</span>
                                            @endif
                                        @else
                                            <span class="text-xs text-neutral-400 italic">Non suivi</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <!-- Reset Confirmation Modal -->
    <div x-show="confirmReset" x-cloak
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0"
         x-transition:enter-end="opacity-100"
         class="fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/50" @click="confirmReset = false"></div>
            <div class="relative w-full max-w-sm bg-white rounded-2xl shadow-xl p-6 text-center"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100">
                <div class="w-14 h-14 bg-red-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-7 h-7 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-bold text-neutral-900 mb-2">Remise a zero</h3>
                <p class="text-neutral-600 text-sm mb-6">
                    Cela remettra a zero le stock de <strong>tous les plats suivis</strong>. Cette action est irreversible.
                </p>
                <div class="flex gap-3">
                    <button @click="confirmReset = false" class="btn btn-outline flex-1">Annuler</button>
                    <button wire:click="resetAll" @click="confirmReset = false"
                            class="btn flex-1 bg-red-500 text-white hover:bg-red-600">
                        Confirmer
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
