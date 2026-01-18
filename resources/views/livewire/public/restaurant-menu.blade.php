@php
    $primaryColor = $restaurant->primary_color ?? '#f97316';
    $secondaryColor = $restaurant->secondary_color ?? '#1c1917';
@endphp

<div class="min-h-screen bg-neutral-50" wire:poll.10s="refreshRestaurant">
    <!-- Warning Message -->
    @if(session('warning'))
        <div x-data="{ show: true }" 
             x-show="show"
             x-init="setTimeout(() => show = false, 8000)"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-2"
             class="fixed top-4 left-1/2 -translate-x-1/2 z-50 max-w-md w-full mx-4">
            <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 text-amber-700 flex items-center justify-between gap-3 shadow-lg">
                <div class="flex items-center gap-3">
                    <svg class="w-5 h-5 text-amber-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                    <span class="font-medium text-sm">{{ session('warning') }}</span>
                </div>
                <button @click="show = false" class="text-amber-600 hover:text-amber-800">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>
    @endif

    <!-- Closed Popup Modal - Disparaît automatiquement quand le restaurant s'ouvre -->
    @if(!$this->isRestaurantOpen)
        <div class="fixed inset-0 bg-black/60 z-50 flex items-center justify-center p-4"
             style="pointer-events: auto;"
             x-cloak>
            <div class="bg-white rounded-2xl shadow-2xl max-w-md w-full p-6 md:p-8 relative">
                <!-- Icon -->
                <div class="flex justify-center mb-4">
                    <div class="w-16 h-16 bg-red-100 rounded-full flex items-center justify-center">
                        <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </div>
                </div>

                <!-- Content -->
                <div class="text-center mb-6">
                    <h3 class="text-2xl font-bold text-neutral-900 mb-2">
                        Restaurant fermé
                    </h3>
                    <p class="text-neutral-600 mb-4">
                        Le restaurant est actuellement fermé. Le site sera accessible dès la réouverture.
                    </p>
                    @if($this->restaurant->getNextOpeningTime())
                        <div class="bg-neutral-50 rounded-xl p-4 border border-neutral-200">
                            <p class="text-sm text-neutral-500 mb-1">Prochaine ouverture</p>
                            <p class="text-lg font-semibold text-neutral-900">
                                {{ $this->restaurant->getNextOpeningTime() }}
                            </p>
                        </div>
                    @endif
                </div>

                <!-- Actions -->
                <div class="flex flex-col gap-3">
                    <button onclick="window.location.href = '{{ route('home') }}'" 
                            class="w-full py-3 px-4 text-white font-medium rounded-xl transition-colors" 
                            style="background-color: {{ $primaryColor }};"
                            onmouseover="this.style.opacity='0.9'" 
                            onmouseout="this.style.opacity='1'">
                        Retour à l'accueil
                    </button>
                    <p class="text-xs text-center text-neutral-400">
                        Le popup disparaîtra automatiquement à l'ouverture
                    </p>
                </div>
            </div>
        </div>
    @endif

    <!-- Restaurant Header -->
    <header class="relative">
        <div class="h-48 md:h-64 bg-neutral-900 relative overflow-hidden">
            @if($restaurant->banner_path)
                <img src="{{ $restaurant->banner_url }}" 
                     alt="{{ $restaurant->name }}" 
                     class="w-full h-full object-cover opacity-80">
            @else
                <div class="absolute inset-0" style="background: linear-gradient(to bottom right, {{ $primaryColor }}, {{ $secondaryColor }});"></div>
            @endif
            <div class="absolute inset-0 bg-gradient-to-t from-neutral-900/80 to-transparent"></div>
        </div>

        <!-- Restaurant Info Card -->
        <div class="max-w-5xl mx-auto px-4 relative -mt-16 md:-mt-20">
            <div class="bg-white rounded-2xl shadow-lg p-6 md:p-8">
                <div class="flex flex-col md:flex-row md:items-end gap-6">
                    <!-- Logo -->
                    <div class="flex-shrink-0 -mt-16 md:-mt-20">
                        @if($restaurant->logo_path)
                            <img src="{{ $restaurant->logo_url }}" 
                                 alt="{{ $restaurant->name }}" 
                                 class="w-24 h-24 md:w-32 md:h-32 rounded-2xl border-4 border-white shadow-lg object-cover bg-white">
                        @else
                            <div class="w-24 h-24 md:w-32 md:h-32 rounded-2xl border-4 border-white shadow-lg flex items-center justify-center" style="background-color: {{ $primaryColor }};">
                                <span class="text-4xl md:text-5xl font-bold text-white">
                                    {{ strtoupper(substr($restaurant->name, 0, 1)) }}
                                </span>
                            </div>
                        @endif
                    </div>

                    <!-- Info -->
                    <div class="flex-1">
                        <div class="flex items-start justify-between gap-4">
                            <div>
                                <h1 class="text-2xl md:text-3xl font-bold text-neutral-900">
                                    {{ $restaurant->name }}
                                </h1>
                                @if($restaurant->description)
                                    <p class="text-neutral-500 mt-1">{{ $restaurant->description }}</p>
                                @endif
                            </div>
                            
                            @if($this->isRestaurantOpen)
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-emerald-500 text-white rounded-full text-sm font-medium">
                                    <span class="w-2 h-2 bg-white rounded-full animate-pulse"></span>
                                    Ouvert
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 px-3 py-1 bg-red-500 text-white rounded-full text-sm font-medium">
                                    <span class="w-2 h-2 bg-white rounded-full"></span>
                                    Fermé
                                </span>
                            @endif
                        </div>

                        <!-- Meta -->
                        <div class="flex flex-wrap items-center gap-4 mt-4 text-sm text-neutral-500">
                            @if($restaurant->address)
                                <span class="flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    {{ $restaurant->address }}
                                </span>
                            @endif
                            @if($restaurant->phone)
                                <a href="tel:{{ $restaurant->phone }}" class="flex items-center gap-1.5 transition-colors" style="color: {{ $primaryColor }};" onmouseover="this.style.opacity='0.8'" onmouseout="this.style.opacity='1'">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                    {{ $restaurant->phone }}
                                </a>
                            @endif
                            @if($restaurant->estimated_prep_time)
                                <span class="flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    ~{{ $restaurant->estimated_prep_time }} min
                                </span>
                            @endif
                            @if($this->reviewsCount > 0)
                                <span class="flex items-center gap-1.5">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                    </svg>
                                    <span class="font-semibold text-neutral-900">{{ number_format($this->averageRating, 1) }}</span>
                                    <span class="text-neutral-400">({{ $this->reviewsCount }} avis)</span>
                                </span>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </header>


    <!-- Reviews Section -->
    @if($this->reviewsCount > 0)
        <section class="max-w-5xl mx-auto px-4 py-8">
            <div class="mb-6">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
                    <div>
                        <h2 class="text-2xl font-bold text-neutral-900">Avis clients</h2>
                        <p class="text-sm text-neutral-500 mt-1">
                            Note moyenne : <span class="font-semibold text-neutral-900">{{ number_format($this->averageRating, 1) }}/5</span>
                            sur {{ $this->reviewsCount }} {{ $this->reviewsCount > 1 ? 'avis' : 'avis' }}
                        </p>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="flex items-center gap-1">
                            @for($i = 1; $i <= 5; $i++)
                                <svg class="w-6 h-6 {{ $i <= round($this->averageRating) ? 'text-yellow-400 fill-current' : 'text-neutral-300' }}" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                            @endfor
                        </div>
                    </div>
                </div>
            </div>

            <div class="grid md:grid-cols-2 gap-4">
                @foreach($this->reviews as $review)
                    <div class="bg-white rounded-xl border border-neutral-200 p-5 hover:shadow-md transition-shadow">
                        <div class="flex items-start gap-4 mb-3">
                            <div class="w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0 shadow-sm" style="background: linear-gradient(135deg, {{ $primaryColor }}, {{ $secondaryColor }});">
                                <span class="text-lg font-bold text-white">
                                    {{ strtoupper(substr($review->customer_name, 0, 1)) }}
                                </span>
                            </div>
                            <div class="flex-1 min-w-0">
                                <div class="flex items-start justify-between gap-2 mb-2">
                                    <h3 class="font-semibold text-neutral-900 truncate">{{ $review->customer_name }}</h3>
                                    <span class="text-xs text-neutral-400 whitespace-nowrap">{{ $review->created_at->locale('fr')->diffForHumans() }}</span>
                                </div>
                                <div class="flex items-center gap-1 mb-2">
                                    @for($i = 1; $i <= 5; $i++)
                                        <svg class="w-4 h-4 {{ $i <= $review->rating ? 'text-yellow-400 fill-current' : 'text-neutral-300' }}" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    @endfor
                                </div>
                            </div>
                        </div>
                        @if($review->comment)
                            <p class="text-sm text-neutral-700 leading-relaxed">{{ $review->comment }}</p>
                        @else
                            <p class="text-sm text-neutral-400 italic">Aucun commentaire</p>
                        @endif
                        @if($review->response)
                            <div class="mt-4 pt-4 border-t border-neutral-100">
                                <div class="flex items-start gap-2">
                                    <div class="w-8 h-8 rounded-full flex items-center justify-center flex-shrink-0" style="background-color: {{ $primaryColor }}20;">
                                        <svg class="w-4 h-4" style="color: {{ $primaryColor }};" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                                        </svg>
                                    </div>
                                    <div class="flex-1">
                                        <p class="text-xs font-semibold mb-1" style="color: {{ $primaryColor }};">Réponse du restaurant</p>
                                        <p class="text-sm text-neutral-600">{{ $review->response }}</p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </section>
    @endif

    <!-- Navigation Categories (Sticky) -->
    <nav class="sticky top-0 z-30 bg-white border-b border-neutral-200 shadow-sm mt-6">
        <div class="max-w-5xl mx-auto px-4">
            <div class="flex items-center justify-between gap-4 py-3">
                <div class="flex items-center gap-2 overflow-x-auto scrollbar-hide flex-1">
                <button wire:click="setCategory(null)" 
                        class="flex-shrink-0 px-4 py-2 rounded-full text-sm font-medium transition-colors whitespace-nowrap {{ !$activeCategory ? 'text-white' : 'bg-neutral-100 text-neutral-600 hover:bg-neutral-200' }}"
                        @if(!$activeCategory) style="background-color: {{ $primaryColor }};" @endif>
                    Tout
                </button>
                @foreach($this->categories as $category)
                    <button wire:click="setCategory({{ $category->id }})" 
                            class="flex-shrink-0 px-4 py-2 rounded-full text-sm font-medium transition-colors whitespace-nowrap {{ $activeCategory === $category->id ? 'text-white' : 'bg-neutral-100 text-neutral-600 hover:bg-neutral-200' }}"
                            @if($activeCategory === $category->id) style="background-color: {{ $primaryColor }};" @endif>
                        {{ $category->name }}
                    </button>
                @endforeach
                </div>
                
                <!-- Cart Button (Desktop) -->
                <button wire:click="toggleCart"
                        class="hidden lg:flex items-center gap-2 px-4 py-2  text-white rounded-xl  transition-colors font-medium relative" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'" style="background-color: {{ $primaryColor }};">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    Panier
                    @if($this->cartItemsCount > 0)
                        <span class="absolute -top-1 -right-1 bg-white  text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center" style="color: {{ $primaryColor }};">
                            {{ $this->cartItemsCount }}
                        </span>
                    @endif
                </button>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-5xl mx-auto px-4 py-8">
        <!-- Search Bar -->
        <div class="mb-6">
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text" 
                       wire:model.live.debounce.300ms="searchQuery"
                       class="w-full pl-10 pr-4 py-3 bg-white border border-neutral-200 rounded-xl text-neutral-700 placeholder-neutral-400 focus:ring-2  focus:border-transparent" style="--tw-ring-color: {{ $primaryColor }};"
                       placeholder="Rechercher un plat...">
            </div>
        </div>

        <!-- Dishes Grid -->
        @if($this->dishes->isEmpty())
            <div class="text-center py-20">
                <div class="w-24 h-24 bg-neutral-100 rounded-full flex items-center justify-center mx-auto mb-6">
                    <svg class="w-12 h-12 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <h3 class="text-xl font-semibold text-neutral-900 mb-2">Aucun plat trouvé</h3>
                <p class="text-neutral-500">@if($searchQuery) Essayez une autre recherche @else Le menu sera bientôt disponible @endif</p>
                @if($searchQuery)
                    <button wire:click="$set('searchQuery', '')" class="mt-4  hover: font-medium" style="color: {{ $primaryColor }};" style="color: {{ $primaryColor }};">
                        Voir tous les plats
                    </button>
                @endif
            </div>
        @else
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($this->dishes as $dish)
                    <div wire:click="openDish({{ $dish->id }})" 
                         class="bg-white rounded-2xl overflow-hidden shadow-sm hover:shadow-lg transition-all cursor-pointer {{ !$dish->is_available ? 'opacity-60' : '' }}">
                        <!-- Image -->
                        <div class="aspect-square relative overflow-hidden bg-neutral-100 group"
                             x-data="{ showImage: false }"
                             @mouseenter="showImage = true"
                             @mouseleave="showImage = false">
                            @if($dish->image_path)
                                <img src="{{ Storage::url($dish->image_path) }}" 
                                     alt="{{ $dish->name }}" 
                                     class="w-full h-full object-cover transition-transform duration-300"
                                     :class="showImage ? 'scale-110' : 'scale-100'">
                                <!-- Hover Image Preview -->
                                <div x-show="showImage"
                                     x-transition:enter="transition ease-out duration-200"
                                     x-transition:enter-start="opacity-0"
                                     x-transition:enter-end="opacity-100"
                                     x-transition:leave="transition ease-in duration-150"
                                     x-transition:leave-start="opacity-100"
                                     x-transition:leave-end="opacity-0"
                                     class="absolute inset-0 pointer-events-none z-10"
                                     x-cloak>
                                    <img src="{{ Storage::url($dish->image_path) }}" 
                                         alt="{{ $dish->name }}" 
                                         class="w-full h-full object-cover blur-sm brightness-75">
                                </div>
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-neutral-100 to-neutral-200">
                                    <svg class="w-16 h-16 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            @endif
                            @if($dish->is_featured)
                                <div class="absolute top-3 left-3 z-20">
                                    <span class="px-2.5 py-1  text-white rounded-full text-xs font-bold flex items-center gap-1" style="background-color: {{ $primaryColor }};">
                                        <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                        Populaire
                                    </span>
                                </div>
                            @endif
                            @if(!$dish->is_available)
                                <div class="absolute inset-0 bg-black/50 flex items-center justify-center z-20">
                                    <span class="px-3 py-1.5 bg-neutral-800 text-white rounded-full text-sm font-medium flex items-center gap-1.5">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                        Indisponible
                                    </span>
                                </div>
                            @endif
                        </div>

                        <!-- Content -->
                        <div class="p-4">
                            <h3 class="font-semibold text-neutral-900 line-clamp-1">{{ $dish->name }}</h3>
                            @if($dish->description)
                                <p class="text-sm text-neutral-500 line-clamp-2 mt-1">{{ $dish->description }}</p>
                            @endif
                            <div class="flex items-center justify-between mt-3">
                                <span class="text-lg font-bold " style="color: {{ $primaryColor }};">{{ number_format($dish->price, 0, ',', ' ') }} F</span>
                                @if($dish->is_available)
                                    <button class="w-10 h-10  text-white rounded-full flex items-center justify-center  transition" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'" style="background-color: {{ $primaryColor }};">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6"/>
                                        </svg>
                                    </button>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </main>

    <!-- Cart Sidebar (Desktop) -->
    <aside x-data="{ showCart: @entangle('showCart') }"
            x-show="showCart"
            x-transition:enter="transition ease-out duration-300"
            x-transition:enter-start="opacity-0 translate-x-full"
            x-transition:enter-end="opacity-100 translate-x-0"
            x-transition:leave="transition ease-in duration-200"
            x-transition:leave-start="opacity-100 translate-x-0"
            x-transition:leave-end="opacity-0 translate-x-full"
            class="hidden lg:block fixed right-0 top-0 h-full w-80 bg-white shadow-2xl z-40"
            x-cloak>
        <div class="h-full flex flex-col">
            <div class=" p-6 text-white flex items-center justify-between" style="background-color: {{ $primaryColor }};">
                <h2 class="text-lg font-bold flex items-center gap-2">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    Votre panier
                    @if($this->cartItemsCount > 0)
                        <span class="ml-2 bg-white/20 px-2 py-0.5 rounded-full text-sm">{{ $this->cartItemsCount }}</span>
                    @endif
                </h2>
                <button @click="showCart = false" 
                        class="p-2 rounded-lg hover:bg-white/20 transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            <div class="flex-1 overflow-y-auto p-6">
                @if(empty($cart))
                    <div class="text-center py-12">
                        <div class="w-20 h-20 bg-neutral-100 rounded-full flex items-center justify-center mx-auto mb-4">
                            <svg class="w-10 h-10 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <p class="text-neutral-500 font-medium">Panier vide</p>
                        <p class="text-sm text-neutral-400 mt-1">Ajoutez des plats pour commander</p>
                    </div>
                @else
                    <div class="space-y-3">
                        @foreach($cart as $key => $item)
                            <div class="flex gap-3 p-3 bg-white border border-neutral-200 rounded-xl  hover:shadow-sm transition-all" onmouseover="this.style.borderColor='{{ $primaryColor }}'" onmouseout="this.style.borderColor=''">
                                @if(!empty($item['image']))
                                    <div class="flex-shrink-0">
                                        <img src="{{ Storage::url($item['image']) }}" 
                                             alt="{{ $item['name'] }}" 
                                             class="w-16 h-16 object-cover rounded-lg">
                                    </div>
                                @else
                                    <div class="flex-shrink-0 w-16 h-16 bg-neutral-100 rounded-lg flex items-center justify-center">
                                        <svg class="w-8 h-8 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                @endif
                                <div class="flex-1 min-w-0">
                                    <p class="font-semibold text-neutral-900 text-sm line-clamp-1">{{ $item['name'] }}</p>
                                    @if(!empty($item['options']))
                                        <p class="text-xs text-neutral-500 mt-0.5 line-clamp-1">{{ collect($item['options'])->pluck('name')->join(', ') }}</p>
                                    @endif
                                    <p class=" font-bold text-sm mt-1.5" style="color: {{ $primaryColor }};">{{ number_format($item['total'], 0, ',', ' ') }} F</p>
                                </div>
                                <div class="flex flex-col items-center gap-1">
                                    <button wire:click="updateCartQuantity('{{ $key }}', {{ $item['quantity'] + 1 }})" 
                                            class="w-7 h-7 rounded-lg  text-white flex items-center justify-center  transition-colors" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'" style="background-color: {{ $primaryColor }};">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6"/>
                                        </svg>
                                    </button>
                                    <span class="w-8 text-center font-bold text-sm text-neutral-900">{{ $item['quantity'] }}</span>
                                    <button wire:click="updateCartQuantity('{{ $key }}', {{ $item['quantity'] - 1 }})" 
                                            class="w-7 h-7 rounded-lg bg-neutral-100 border border-neutral-200 flex items-center justify-center hover:bg-neutral-200 text-neutral-600 transition-colors">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>

            @if(!empty($cart))
                <div class="p-6 border-t border-neutral-200 bg-neutral-50">
                    <div class="flex items-center justify-between mb-4">
                        <span class="text-neutral-600">Total</span>
                        <span class="text-xl font-bold text-neutral-900">{{ number_format($this->cartTotal, 0, ',', ' ') }} F</span>
                    </div>
                    <button wire:click="proceedToCheckout" 
                            @if(!$this->isRestaurantOpen) disabled @endif
                            class="w-full py-3 text-white font-bold rounded-xl transition @if(!$this->isRestaurantOpen) opacity-50 cursor-not-allowed @endif" 
                            onmouseover="@if($this->isRestaurantOpen) this.style.opacity='0.9' @endif"
                            onmouseout="@if($this->isRestaurantOpen) this.style.opacity='1' @endif"
                            style="background-color: {{ $primaryColor }};">
                        @if($this->isRestaurantOpen)
                            Commander
                        @else
                            Restaurant fermé
                        @endif
                    </button>
                    <button wire:click="clearCart" class="w-full mt-2 text-sm text-neutral-400 hover:text-red-500 transition">
                        Vider le panier
                    </button>
                </div>
            @endif
        </div>
    </aside>

    <!-- Mobile Cart Button -->
    @if($this->cartItemsCount > 0)
        <button wire:click="toggleCart" 
                class="fixed bottom-6 right-6 z-30 flex items-center gap-3 px-5 py-4 bg-gradient-to-r from-primary-500 to-primary-600 text-white rounded-2xl shadow-2xl hover:shadow-primary-500/50 hover:from-primary-600 hover:to-primary-700 transition-all transform hover:scale-105 active:scale-95 lg:hidden">
            <div class="relative">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                </svg>
                <span class="absolute -top-2 -right-2 bg-white  text-xs font-bold rounded-full w-5 h-5 flex items-center justify-center" style="color: {{ $primaryColor }};">
                    {{ $this->cartItemsCount }}
                </span>
            </div>
            <div class="flex flex-col items-start">
                <span class="text-xs opacity-90">Panier</span>
                <span class="font-bold text-lg leading-tight">{{ number_format($this->cartTotal, 0, ',', ' ') }} F</span>
            </div>
        </button>
        
        <!-- Mobile Cart Modal -->
        <div x-data="{ showCart: @entangle('showCart') }"
             x-show="showCart"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed inset-0 z-50 lg:hidden"
             x-cloak>
            <div class="fixed inset-0 bg-black/50" @click="showCart = false"></div>
            <div class="fixed bottom-0 left-0 right-0 bg-white rounded-t-3xl shadow-2xl max-h-[85vh] flex flex-col">
                <div class=" p-6 text-white flex items-center justify-between rounded-t-3xl" style="background-color: {{ $primaryColor }};">
                    <h2 class="text-lg font-bold flex items-center gap-2">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        Votre panier
                        <span class="ml-2 bg-white/20 px-2 py-0.5 rounded-full text-sm">{{ $this->cartItemsCount }}</span>
                    </h2>
                    <button @click="showCart = false" class="p-2 rounded-lg hover:bg-white/20 transition-colors">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                </div>
                
                <div class="flex-1 overflow-y-auto p-6">
                    @if(empty($cart))
                        <div class="text-center py-12">
                            <div class="w-20 h-20 bg-neutral-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                <svg class="w-10 h-10 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                            </div>
                            <p class="text-neutral-500 font-medium">Panier vide</p>
                            <p class="text-sm text-neutral-400 mt-1">Ajoutez des plats pour commander</p>
                        </div>
                    @else
                        <div class="space-y-3">
                            @foreach($cart as $key => $item)
                                <div class="flex gap-3 p-3 bg-white border border-neutral-200 rounded-xl">
                                    @if(!empty($item['image']))
                                        <div class="flex-shrink-0">
                                            <img src="{{ Storage::url($item['image']) }}" 
                                                 alt="{{ $item['name'] }}" 
                                                 class="w-16 h-16 object-cover rounded-lg">
                                        </div>
                                    @else
                                        <div class="flex-shrink-0 w-16 h-16 bg-neutral-100 rounded-lg flex items-center justify-center">
                                            <svg class="w-8 h-8 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                    @endif
                                    <div class="flex-1 min-w-0">
                                        <p class="font-semibold text-neutral-900 text-sm line-clamp-1">{{ $item['name'] }}</p>
                                        @if(!empty($item['options']))
                                            <p class="text-xs text-neutral-500 mt-0.5 line-clamp-1">{{ collect($item['options'])->pluck('name')->join(', ') }}</p>
                                        @endif
                                        <p class=" font-bold text-sm mt-1.5" style="color: {{ $primaryColor }};">{{ number_format($item['total'], 0, ',', ' ') }} F</p>
                                    </div>
                                    <div class="flex flex-col items-center gap-1">
                                        <button wire:click="updateCartQuantity('{{ $key }}', {{ $item['quantity'] + 1 }})" 
                                                class="w-7 h-7 rounded-lg  text-white flex items-center justify-center  transition-colors" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'" style="background-color: {{ $primaryColor }};">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6"/>
                                            </svg>
                                        </button>
                                        <span class="w-8 text-center font-bold text-sm text-neutral-900">{{ $item['quantity'] }}</span>
                                        <button wire:click="updateCartQuantity('{{ $key }}', {{ $item['quantity'] - 1 }})" 
                                                class="w-7 h-7 rounded-lg bg-neutral-100 border border-neutral-200 flex items-center justify-center hover:bg-neutral-200 text-neutral-600 transition-colors">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 12H4"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
                
                @if(!empty($cart))
                    <div class="p-6 border-t border-neutral-200 bg-neutral-50">
                        <div class="flex items-center justify-between mb-4">
                            <span class="text-neutral-600 font-medium">Total</span>
                            <span class="text-xl font-bold text-neutral-900">{{ number_format($this->cartTotal, 0, ',', ' ') }} F</span>
                        </div>
                        <button wire:click="proceedToCheckout" 
                                @if(!$restaurant->isOpenNow()) disabled @endif
                                class="w-full py-4 text-white font-bold rounded-xl transition shadow-lg @if(!$restaurant->isOpenNow()) opacity-50 cursor-not-allowed @endif" 
                            onmouseover="@if($this->isRestaurantOpen) this.style.opacity='0.9' @endif"
                            onmouseout="@if($this->isRestaurantOpen) this.style.opacity='1' @endif"
                                style="background-color: {{ $primaryColor }};">
                            @if($this->isRestaurantOpen)
                                Commander
                            @else
                                Restaurant fermé
                            @endif
                        </button>
                        <button wire:click="clearCart" class="w-full mt-2 text-sm text-neutral-400 hover:text-red-500 transition font-medium">
                            Vider le panier
                        </button>
                    </div>
                @endif
            </div>
        </div>
    @endif

    <!-- Footer -->
    <footer class="bg-white border-t border-neutral-200 mt-12">
        <div class="max-w-5xl mx-auto px-4 py-8">
            <div class="flex flex-col md:flex-row items-center justify-between gap-4">
                <div class="flex items-center gap-2 text-sm text-neutral-500">
                    <span>Propulsé par</span>
                    <a href="{{ route('home') }}" class="font-semibold  hover:" style="color: {{ $primaryColor }};" style="color: {{ $primaryColor }};">
                        MenuPro
                    </a>
                </div>
            </div>
        </div>
    </footer>

    <!-- Dish Detail Modal -->
    @if($this->selectedDish)
        <div class="fixed inset-0 z-50 overflow-y-auto"
             x-data="{ quantity: 1, selectedOptions: [], instructions: '' }">
            <div class="fixed inset-0 bg-black/50" wire:click="closeDish"></div>

            <div class="flex min-h-screen items-center justify-center p-4">
                <div class="relative w-full max-w-md bg-white rounded-2xl shadow-2xl overflow-hidden">
                    <button wire:click="closeDish" 
                            class="absolute top-4 right-4 z-10 w-8 h-8 bg-white/90 rounded-full flex items-center justify-center shadow">
                        <svg class="w-4 h-4 text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>

                    <div class="aspect-video bg-neutral-100">
                        @if($this->selectedDish->image_path)
                            <img src="{{ Storage::url($this->selectedDish->image_path) }}" 
                                 alt="{{ $this->selectedDish->name }}" 
                                 class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-neutral-100 to-neutral-200">
                                <svg class="w-24 h-24 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                        @endif
                    </div>

                    <div class="p-6">
                        <h2 class="text-xl font-bold text-neutral-900">{{ $this->selectedDish->name }}</h2>
                        @if($this->selectedDish->description)
                            <p class="text-neutral-500 mt-2 text-sm">{{ $this->selectedDish->description }}</p>
                        @endif
                        <p class="text-2xl font-bold  mt-4" style="color: {{ $primaryColor }};">{{ number_format($this->selectedDish->price, 0, ',', ' ') }} F</p>

                        @if($this->selectedDish->optionGroups && $this->selectedDish->optionGroups->isNotEmpty())
                            <div class="mt-6 space-y-4">
                                @foreach($this->selectedDish->optionGroups as $group)
                                    <div>
                                        <p class="font-semibold text-neutral-800 text-sm mb-2">
                                            {{ $group->name }}
                                            @if($group->is_required)<span class="text-red-500">*</span>@endif
                                        </p>
                                        <div class="space-y-2">
                                            @foreach($group->activeOptions ?? $group->options as $option)
                                                <label class="flex items-center justify-between p-3 bg-neutral-50 rounded-lg cursor-pointer hover:bg-neutral-100 transition"
                                                       :class="{ 'ring-2 ring-primary-500 bg-primary-50': selectedOptions.includes({{ $option->id }}) }">
                                                    <div class="flex items-center gap-2">
                                                        <input type="{{ $group->max_selections === 1 ? 'radio' : 'checkbox' }}"
                                                               name="option_{{ $group->id }}"
                                                               value="{{ $option->id }}"
                                                               @change="selectedOptions = selectedOptions.includes({{ $option->id }}) ? selectedOptions.filter(id => id != {{ $option->id }}) : [...selectedOptions, {{ $option->id }}]"
                                                               class=" " style="--tw-ring-color: {{ $primaryColor }};" style="color: {{ $primaryColor }};">
                                                        <span class="text-sm text-neutral-700">{{ $option->name }}</span>
                                                    </div>
                                                    @if($option->price_adjustment > 0)
                                                        <span class=" text-sm font-medium" style="color: {{ $primaryColor }};">+{{ number_format($option->price_adjustment, 0, ',', ' ') }} F</span>
                                                    @endif
                                                </label>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        <div class="mt-4">
                            <label class="block text-sm font-semibold text-neutral-700 mb-2">Instructions spéciales</label>
                            <textarea x-model="instructions"
                                      class="w-full p-3 bg-neutral-50 border-0 rounded-lg focus:ring-2  resize-none text-sm" style="--tw-ring-color: {{ $primaryColor }};"
                                      rows="2"
                                      placeholder="Ex: Sans oignons..."></textarea>
                        </div>
                    </div>

                    <div class="p-4 bg-neutral-50 border-t flex items-center gap-3">
                        <div class="flex items-center gap-2 bg-white rounded-lg border px-2">
                            <button @click="quantity = Math.max(1, quantity - 1)" class="w-8 h-8 flex items-center justify-center hover:bg-neutral-100 rounded">−</button>
                            <span class="w-6 text-center font-bold" x-text="quantity"></span>
                            <button @click="quantity++" class="w-8 h-8 flex items-center justify-center hover:bg-neutral-100 rounded">+</button>
                        </div>

                        @if(!$this->isRestaurantOpen)
                            <button disabled class="flex-1 py-3 bg-neutral-300 text-neutral-500 font-bold rounded-xl cursor-not-allowed">
                                Restaurant fermé
                            </button>
                        @elseif($this->selectedDish->is_available)
                            <button @click="$wire.addToCart({{ $this->selectedDish->id }}, quantity, selectedOptions, instructions)" 
                                    class="flex-1 py-3 text-white font-bold rounded-xl transition" onmouseover="this.style.opacity='0.9'" onmouseout="this.style.opacity='1'" style="background-color: {{ $primaryColor }};">
                                Ajouter au panier
                            </button>
                        @else
                            <button disabled class="flex-1 py-3 bg-neutral-300 text-neutral-500 font-bold rounded-xl cursor-not-allowed">
                                Indisponible
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Floating Reservation Button -->
    @if($restaurant->reservations_enabled ?? true)
        <div x-data="{ showReservationModal: false }" class="fixed bottom-6 right-6 z-40">
            <!-- Floating Button -->
            <button @click="showReservationModal = true" 
                    class="w-16 h-16 rounded-full shadow-2xl flex items-center justify-center text-white font-bold transition-all hover:scale-110 hover:shadow-3xl"
                    style="background: linear-gradient(135deg, {{ $primaryColor }}, {{ $secondaryColor }});">
                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </button>

            <!-- Reservation Modal -->
            <div x-show="showReservationModal" 
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 @click.away="showReservationModal = false"
                 class="fixed inset-0 z-50 flex items-center justify-center p-4"
                 x-cloak>
                <!-- Backdrop -->
                <div class="absolute inset-0 bg-black/50" @click="showReservationModal = false"></div>
                
                <!-- Modal Content -->
                <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-2xl max-h-[90vh] overflow-y-auto">
                    <!-- Header -->
                    <div class="sticky top-0 bg-white border-b border-neutral-200 px-6 py-4 flex items-center justify-between rounded-t-2xl z-10">
                        <div>
                            <h3 class="text-xl font-bold text-neutral-900">Réserver une table</h3>
                            <p class="text-sm text-neutral-500 mt-1">Remplissez le formulaire ci-dessous</p>
                        </div>
                        <button @click="showReservationModal = false" 
                                class="w-8 h-8 rounded-full hover:bg-neutral-100 flex items-center justify-center transition-colors">
                            <svg class="w-5 h-5 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Form -->
                    <form method="POST" 
                          action="{{ route('r.reservations.store', $restaurant->slug) }}"
                          class="p-6 space-y-5">
                        @csrf
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 mb-2">Nom complet *</label>
                                <input type="text" 
                                       name="customer_name" 
                                       required
                                       class="w-full px-4 py-3 border border-neutral-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition"
                                       placeholder="Votre nom">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 mb-2">Email *</label>
                                <input type="email" 
                                       name="customer_email" 
                                       required
                                       class="w-full px-4 py-3 border border-neutral-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition"
                                       placeholder="votre@email.com">
                            </div>
                        </div>
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 mb-2">Téléphone *</label>
                                <input type="tel" 
                                       name="customer_phone" 
                                       required
                                       class="w-full px-4 py-3 border border-neutral-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition"
                                       placeholder="+225 XX XX XX XX XX">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 mb-2">Nombre de personnes *</label>
                                <input type="number" 
                                       name="number_of_guests" 
                                       required
                                       min="1"
                                       max="50"
                                       class="w-full px-4 py-3 border border-neutral-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition"
                                       placeholder="2">
                            </div>
                        </div>
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 mb-2">Date *</label>
                                <input type="date" 
                                       name="reservation_date" 
                                       required
                                       min="{{ date('Y-m-d', strtotime('+1 day')) }}"
                                       class="w-full px-4 py-3 border border-neutral-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 mb-2">Heure *</label>
                                <input type="time" 
                                       name="reservation_time" 
                                       required
                                       class="w-full px-4 py-3 border border-neutral-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">Demandes spéciales (optionnel)</label>
                            <textarea name="special_requests" 
                                      rows="3"
                                      class="w-full px-4 py-3 border border-neutral-300 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent transition resize-none"
                                      placeholder="Allergies, anniversaire, demande spéciale..."></textarea>
                        </div>
                        <div class="flex gap-3 pt-2">
                            <button type="button" 
                                    @click="showReservationModal = false"
                                    class="flex-1 px-6 py-3 border border-neutral-300 text-neutral-700 rounded-xl font-medium hover:bg-neutral-50 transition">
                                Annuler
                            </button>
                            <button type="submit" 
                                    class="flex-1 px-6 py-3 text-white rounded-xl font-medium transition shadow-lg hover:shadow-xl"
                                    style="background: linear-gradient(135deg, {{ $primaryColor }}, {{ $secondaryColor }});">
                                Confirmer la réservation
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
