@props([
    'dish' => null,
    'showAddButton' => true,
])

<div {{ $attributes->merge(['class' => 'menu-card group']) }}>
    <!-- Image -->
    <div class="relative overflow-hidden">
        @if($dish->image_path ?? false)
            <img src="{{ Storage::url($dish->image_path) }}" 
                 alt="{{ $dish->name }}"
                 class="menu-card-image transition-transform duration-500 group-hover:scale-110">
        @else
            <div class="menu-card-image bg-gradient-to-br from-neutral-100 to-neutral-200 flex items-center justify-center">
                <svg class="w-16 h-16 text-neutral-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                </svg>
            </div>
        @endif

        <!-- Badge (ex: Nouveau, Populaire) -->
        @if($dish->badge ?? false)
            <span class="menu-card-badge">{{ $dish->badge }}</span>
        @endif

        <!-- Unavailable Overlay -->
        @if(!($dish->available ?? true))
            <div class="absolute inset-0 bg-neutral-900/60 flex items-center justify-center">
                <span class="bg-neutral-800 text-white px-4 py-2 rounded-full text-sm font-medium">
                    Indisponible
                </span>
            </div>
        @endif
    </div>

    <!-- Content -->
    <div class="p-5">
        <div class="flex items-start justify-between gap-3">
            <div class="flex-1 min-w-0">
                <h3 class="font-semibold text-neutral-900 truncate text-lg">
                    {{ $dish->name ?? 'Nom du plat' }}
                </h3>
                @if($dish->description ?? false)
                    <p class="text-neutral-500 text-sm mt-1 line-clamp-2">
                        {{ $dish->description }}
                    </p>
                @endif
            </div>
            <div class="menu-card-price flex-shrink-0">
                {{ number_format($dish->price ?? 0, 0, ',', ' ') }} <span class="text-sm font-normal">FCFA</span>
            </div>
        </div>

        <!-- Add to Cart Button -->
        @if($showAddButton && ($dish->available ?? true))
            <button 
                @click="add({ id: {{ $dish->id ?? 0 }}, name: '{{ $dish->name ?? '' }}', price: {{ $dish->price ?? 0 }}, image: '{{ $dish->image_path ? Storage::url($dish->image_path) : '' }}' })"
                class="mt-4 w-full btn btn-primary btn-sm group-hover:bg-primary-600">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Ajouter au panier
            </button>
        @endif
    </div>
</div>

