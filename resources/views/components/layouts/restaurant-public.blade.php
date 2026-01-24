@props(['restaurant', 'hideHeader' => false])

@php
    $primaryColor = $restaurant->primary_color ?? '#f97316';
    $secondaryColor = $restaurant->secondary_color ?? '#1c1917';
@endphp

<x-layouts.app :title="($restaurant->name ?? 'Restaurant') . ' - Menu'">
    <style>
        :root {
            --restaurant-primary: {{ $primaryColor }};
            --restaurant-secondary: {{ $secondaryColor }};
        }
        .restaurant-primary { color: {{ $primaryColor }}; }
        .restaurant-primary-bg { background-color: {{ $primaryColor }}; }
        .restaurant-primary-border { border-color: {{ $primaryColor }}; }
        .restaurant-secondary { color: {{ $secondaryColor }}; }
        .restaurant-secondary-bg { background-color: {{ $secondaryColor }}; }
        .restaurant-hover-primary:hover { background-color: {{ $primaryColor }}; opacity: 0.9; }
    </style>
    <div x-data="cart()" class="min-h-screen bg-neutral-50">
        {{-- Demo banner for demo restaurant --}}
        @if(($restaurant->slug ?? '') === 'demo')
        <div class="bg-gradient-to-r from-primary-600 via-primary-500 to-accent-500 text-white py-2.5 px-4 text-center relative overflow-hidden">
            {{-- Animated background --}}
            <div class="absolute inset-0 bg-[url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iNDAiIGhlaWdodD0iNDAiIHZpZXdCb3g9IjAgMCA0MCA0MCIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIj48Y2lyY2xlIGN4PSIyMCIgY3k9IjIwIiByPSIxIiBmaWxsPSJyZ2JhKDI1NSwyNTUsMjU1LDAuMSkiLz48L3N2Zz4=')] opacity-30"></div>
            <div class="relative flex items-center justify-center gap-3 flex-wrap text-sm">
                <span class="inline-flex items-center gap-1.5 font-semibold">
                    <svg class="w-4 h-4 animate-pulse" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/>
                    </svg>
                    MODE DÉMONSTRATION
                </span>
                <span class="text-white/80">—</span>
                <span class="text-white/90">Explorez les fonctionnalités de MenuPro</span>
                <span class="text-white/80">—</span>
                <a href="{{ route('register') }}" class="inline-flex items-center gap-1.5 bg-white/20 hover:bg-white/30 px-3 py-1 rounded-full font-medium transition-colors">
                    Créer mon restaurant
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                    </svg>
                </a>
            </div>
        </div>
        @endif
        
        @unless($hideHeader)
        <!-- Restaurant Header -->
        <header class="relative">
            <!-- Banner -->
            <div class="h-48 md:h-64 bg-neutral-900 relative overflow-hidden">
                @if($restaurant->banner_path ?? false)
                    <img src="{{ Storage::url($restaurant->banner_path) }}" 
                         alt="{{ $restaurant->name }}" 
                         class="w-full h-full object-cover opacity-80">
                @else
                    <div class="absolute inset-0 bg-gradient-mesh"></div>
                @endif
                <div class="absolute inset-0 bg-gradient-to-t from-neutral-900/80 to-transparent"></div>
            </div>

            <!-- Restaurant Info -->
            <div class="max-w-5xl mx-auto px-4 relative -mt-16 md:-mt-20">
                <div class="bg-white rounded-2xl shadow-elevated p-6 md:p-8">
                    <div class="flex flex-col md:flex-row md:items-end gap-6">
                        <!-- Logo -->
                        <div class="flex-shrink-0 -mt-16 md:-mt-20">
                            @if($restaurant->logo_path ?? false)
                                <img src="{{ Storage::url($restaurant->logo_path) }}" 
                                     alt="{{ $restaurant->name }}" 
                                     class="w-24 h-24 md:w-32 md:h-32 rounded-2xl border-4 border-white shadow-lg object-cover bg-white">
                            @else
                                <div class="w-24 h-24 md:w-32 md:h-32 rounded-2xl border-4 border-white shadow-lg bg-gradient-primary flex items-center justify-center">
                                    <span class="text-4xl md:text-5xl font-bold text-white">
                                        {{ substr($restaurant->name ?? 'R', 0, 1) }}
                                    </span>
                                </div>
                            @endif
                        </div>

                        <!-- Info -->
                        <div class="flex-1">
                            <div class="flex items-start justify-between gap-4">
                                <div>
                                    <h1 class="text-2xl md:text-3xl font-bold text-neutral-900">
                                        {{ $restaurant->name ?? 'Restaurant' }}
                                    </h1>
                                    <p class="text-neutral-500 mt-1">
                                        {{ $restaurant->description ?? 'Bienvenue dans notre restaurant' }}
                                    </p>
                                </div>
                                
                                <!-- Status Badge -->
                                @if($restaurant->isOpen ?? true)
                                    <span class="badge badge-success flex items-center gap-1.5">
                                        <span class="w-2 h-2 bg-secondary-500 rounded-full animate-pulse"></span>
                                        Ouvert
                                    </span>
                                @else
                                    <span class="badge badge-error flex items-center gap-1.5">
                                        <span class="w-2 h-2 bg-red-500 rounded-full"></span>
                                        Fermé
                                    </span>
                                @endif
                            </div>

                            <!-- Meta -->
                            <div class="flex flex-wrap items-center gap-4 mt-4 text-sm text-neutral-500">
                                @if($restaurant->address ?? false)
                                    <span class="flex items-center gap-1.5">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                        </svg>
                                        {{ $restaurant->address }}
                                    </span>
                                @endif
                                @if($restaurant->phone ?? false)
                                    <a href="tel:{{ $restaurant->phone }}" class="flex items-center gap-1.5 hover:text-primary-500">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                        </svg>
                                        {{ $restaurant->phone }}
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </header>
        @endunless

        @unless($hideHeader)
        <!-- Navigation Categories (Sticky) -->
        <nav class="sticky top-0 z-30 bg-white border-b border-neutral-200 shadow-sm mt-6">
            <div class="max-w-5xl mx-auto px-4">
                <div class="flex items-center gap-2 py-3 overflow-x-auto scrollbar-hide">
                    @foreach($categories ?? [] as $category)
                        <a href="#category-{{ $category->id }}" 
                           class="flex-shrink-0 px-4 py-2 rounded-full text-sm font-medium transition-colors whitespace-nowrap
                                  {{ $loop->first ? 'bg-primary-500 text-white' : 'bg-neutral-100 text-neutral-600 hover:bg-neutral-200' }}">
                            {{ $category->name }}
                        </a>
                    @endforeach
                </div>
            </div>
        </nav>
        @endunless

        <!-- Main Content -->
        <main class="max-w-5xl mx-auto px-4 py-8">
            {{ $slot }}
        </main>

        <!-- Cart Drawer -->
        <div x-show="open" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click="open = false"
             class="fixed inset-0 bg-black/50 z-40"
             x-cloak></div>

        <aside x-show="open"
               x-transition:enter="transition ease-out duration-300"
               x-transition:enter-start="translate-x-full"
               x-transition:enter-end="translate-x-0"
               x-transition:leave="transition ease-in duration-200"
               x-transition:leave-start="translate-x-0"
               x-transition:leave-end="translate-x-full"
               class="fixed right-0 top-0 h-full w-full max-w-md bg-white shadow-2xl z-50 flex flex-col"
               x-cloak>
            <!-- Cart Header -->
            <div class="flex items-center justify-between p-6 border-b border-neutral-200">
                <h2 class="text-xl font-bold text-neutral-900">Votre panier</h2>
                <button @click="open = false" class="p-2 rounded-lg hover:bg-neutral-100">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <!-- Cart Items -->
            <div class="flex-1 overflow-y-auto p-6">
                <template x-if="items.length === 0">
                    <div class="text-center py-12">
                        <div class="w-20 h-20 bg-neutral-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-10 h-10 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <p class="text-neutral-500">Votre panier est vide</p>
                        <p class="text-sm text-neutral-400 mt-1">Ajoutez des plats pour commencer</p>
                    </div>
                </template>

                <template x-for="item in items" :key="item.id">
                    <div class="cart-item">
                        <img :src="item.image || 'https://via.placeholder.com/80'" 
                             :alt="item.name"
                             class="w-16 h-16 rounded-lg object-cover bg-neutral-100">
                        <div class="flex-1 min-w-0">
                            <h4 class="font-medium text-neutral-900 truncate" x-text="item.name"></h4>
                            <p class="text-primary-500 font-semibold" x-text="formatCurrency(item.price)"></p>
                        </div>
                        <div class="cart-quantity">
                            <button @click="updateQuantity(item.id, item.quantity - 1)" class="cart-quantity-btn">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                </svg>
                            </button>
                            <span class="w-8 text-center font-medium" x-text="item.quantity"></span>
                            <button @click="updateQuantity(item.id, item.quantity + 1)" class="cart-quantity-btn">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </template>
            </div>

            <!-- Cart Footer -->
            <div class="p-6 border-t border-neutral-200 bg-neutral-50">
                <div class="flex items-center justify-between mb-4">
                    <span class="text-neutral-600">Sous-total</span>
                    <span class="text-xl font-bold text-neutral-900" x-text="formatCurrency(total)"></span>
                </div>
                <a href="{{ route('r.checkout', $restaurant->slug ?? 'demo') }}" 
                   class="btn btn-primary w-full"
                   :class="{ 'opacity-50 pointer-events-none': items.length === 0 }">
                    Passer la commande
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                    </svg>
                </a>
            </div>
        </aside>

        <!-- Floating Cart Button (Mobile) -->
        <button @click="toggle()" 
                x-show="count > 0"
                x-transition
                class="fixed bottom-6 right-6 z-30 flex items-center gap-3 px-5 py-4 bg-primary-500 text-white rounded-2xl shadow-elevated hover:bg-primary-600 transition-all md:hidden pb-safe min-h-[48px]">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            <span class="font-semibold" x-text="count + ' article' + (count > 1 ? 's' : '')"></span>
            <span class="font-bold" x-text="formatCurrency(total)"></span>
        </button>

        <!-- Desktop Cart Preview -->
        <button @click="toggle()" 
                x-show="count > 0"
                x-transition
                class="hidden md:flex fixed bottom-6 right-6 z-30 items-center gap-4 px-6 py-4 bg-neutral-900 text-white rounded-2xl shadow-elevated hover:bg-neutral-800 transition-all">
            <div class="relative">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <span class="absolute -top-2 -right-2 w-5 h-5 bg-primary-500 text-xs font-bold rounded-full flex items-center justify-center" x-text="count"></span>
            </div>
            <div class="border-l border-neutral-700 pl-4">
                <div class="text-xs text-neutral-400">Total</div>
                <div class="font-bold" x-text="formatCurrency(total)"></div>
            </div>
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
            </svg>
        </button>

        <!-- Footer -->
        <footer class="bg-white border-t border-neutral-200 mt-12">
            <div class="max-w-5xl mx-auto px-4 py-8">
                <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                    <div class="flex items-center gap-2 text-sm text-neutral-500">
                        <span>Propulsé par</span>
                        <a href="{{ route('home') }}" class="font-semibold text-primary-500 hover:text-primary-600">
                            MenuPro
                        </a>
                    </div>
                    <div class="flex items-center gap-4 text-sm text-neutral-500">
                        <a href="{{ route('terms') }}" class="hover:text-primary-500">CGU</a>
                        <a href="{{ route('privacy') }}" class="hover:text-primary-500">Confidentialité</a>
                    </div>
                </div>
            </div>
        </footer>
    </div>
</x-layouts.app>

