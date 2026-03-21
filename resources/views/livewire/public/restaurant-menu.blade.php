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

    <!-- Restaurant Header — Mobile-first immersif -->
    <header class="relative">
        {{-- Bannière : plus grande sur mobile pour un impact visuel fort --}}
        <div class="relative w-full aspect-[16/9] sm:aspect-[21/9] min-h-[220px] sm:min-h-[180px] max-h-[380px] bg-neutral-800 overflow-hidden">
            @if($restaurant->banner_path)
                <img src="{{ $restaurant->banner_url }}"
                     alt="{{ $restaurant->name }}"
                     loading="eager"
                     decoding="async"
                     class="absolute inset-0 w-full h-full object-cover object-center">
            @else
                <div class="absolute inset-0" style="background: linear-gradient(135deg, {{ $primaryColor }} 0%, {{ $secondaryColor }} 100%);"></div>
            @endif
            <div class="absolute inset-0 bg-gradient-to-t from-black/70 via-black/20 to-transparent"></div>

            {{-- Infos en overlay sur la bannière (mobile) --}}
            <div class="absolute bottom-0 left-0 right-0 p-4 sm:hidden">
                <div class="flex items-end gap-3">
                    @if($restaurant->logo_path)
                        <img src="{{ $restaurant->logo_url }}"
                             alt="{{ $restaurant->name }}"
                             class="w-16 h-16 rounded-2xl border-2 border-white/80 shadow-lg object-cover bg-white flex-shrink-0">
                    @else
                        <div class="w-16 h-16 rounded-2xl border-2 border-white/80 shadow-lg flex items-center justify-center flex-shrink-0" style="background: linear-gradient(135deg, {{ $primaryColor }}, {{ $secondaryColor }});">
                            <span class="text-2xl font-bold text-white">{{ strtoupper(substr($restaurant->name, 0, 1)) }}</span>
                        </div>
                    @endif
                    <div class="flex-1 min-w-0 pb-0.5">
                        <div class="flex items-center gap-2">
                            <h1 class="text-xl font-bold text-white font-display tracking-tight truncate">{{ $restaurant->name }}</h1>
                            @if($restaurant->is_verified)
                                <svg class="w-5 h-5 text-blue-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            @endif
                        </div>
                        @if($this->isRestaurantOpen)
                            <span class="inline-flex items-center gap-1.5 text-emerald-400 text-xs font-semibold mt-0.5">
                                <span class="w-1.5 h-1.5 bg-emerald-400 rounded-full animate-pulse"></span>
                                Ouvert maintenant
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 text-red-400 text-xs font-semibold mt-0.5">
                                <span class="w-1.5 h-1.5 bg-red-400 rounded-full"></span>
                                Fermé
                            </span>
                        @endif
                        @if($this->tableNumber)
                            <span class="inline-flex items-center gap-1 bg-white/20 backdrop-blur-sm text-white text-xs font-bold px-2 py-0.5 rounded-full mt-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6z"/></svg>
                                Table {{ $this->tableNumber }}
                            </span>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Bandeau infos rapides mobile --}}
        <div class="sm:hidden bg-white border-b border-neutral-100 px-4 py-3">
            <div class="flex items-center gap-4 overflow-x-auto scrollbar-hide text-xs text-neutral-500">
                @if($this->reviewsCount > 0)
                    <span class="flex items-center gap-1 flex-shrink-0">
                        <svg class="w-3.5 h-3.5 text-yellow-400 fill-current" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        <span class="font-bold text-neutral-800">{{ number_format($this->averageRating, 1) }}</span>
                        <span>({{ $this->reviewsCount }})</span>
                    </span>
                @endif
                @if($restaurant->estimated_prep_time)
                    <span class="flex items-center gap-1 flex-shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        ~{{ $restaurant->estimated_prep_time }} min
                    </span>
                @endif
                @if($restaurant->phone)
                    <a href="tel:{{ $restaurant->phone }}" class="flex items-center gap-1 flex-shrink-0 font-medium" style="color: {{ $primaryColor }};">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        Appeler
                    </a>
                @endif
                @if($restaurant->address)
                    <span class="flex items-center gap-1 flex-shrink-0">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        {{ Str::limit($restaurant->address, 30) }}
                    </span>
                @endif
            </div>
        </div>

        {{-- Carte restaurant desktop (cachée sur mobile) --}}
        <div class="hidden sm:block max-w-5xl mx-auto px-4 -mt-14 relative z-10">
            <div class="bg-white rounded-2xl shadow-xl shadow-neutral-900/10 ring-1 ring-neutral-200/60 p-6 flex flex-row gap-6 items-center overflow-hidden relative">
                <div class="absolute top-0 left-0 right-0 h-1 rounded-t-2xl" style="background: linear-gradient(90deg, {{ $primaryColor }}, {{ $primaryColor }}60, transparent);"></div>
                <div class="flex-shrink-0">
                    @if($restaurant->logo_path)
                        <img src="{{ $restaurant->logo_url }}"
                             alt="{{ $restaurant->name }}"
                             class="w-24 h-24 rounded-2xl border-2 border-white shadow-lg object-cover bg-white">
                    @else
                        <div class="w-24 h-24 rounded-2xl border-2 border-white shadow-lg flex items-center justify-center" style="background: linear-gradient(135deg, {{ $primaryColor }}, {{ $secondaryColor }});">
                            <span class="text-3xl font-bold text-white">{{ strtoupper(substr($restaurant->name, 0, 1)) }}</span>
                        </div>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <div class="flex items-center gap-2 flex-wrap">
                                <h1 class="text-3xl font-bold text-neutral-900 font-display tracking-tight">{{ $restaurant->name }}</h1>
                                @if($restaurant->is_verified)
                                    <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-blue-100 text-blue-700 rounded-full text-xs font-medium" title="Établissement vérifié">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                        Vérifié
                                    </span>
                                @endif
                            </div>
                            @if($restaurant->description)
                                <p class="text-neutral-500 mt-1">{{ $restaurant->description }}</p>
                            @endif
                        </div>
                        @if($this->isRestaurantOpen)
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-emerald-500 text-white rounded-full text-sm font-medium flex-shrink-0">
                                <span class="w-2 h-2 bg-white rounded-full animate-pulse"></span>
                                Ouvert
                            </span>
                        @else
                            <span class="inline-flex items-center gap-1.5 px-3 py-1.5 bg-red-500 text-white rounded-full text-sm font-medium flex-shrink-0">
                                <span class="w-2 h-2 bg-white rounded-full"></span>
                                Fermé
                            </span>
                        @endif
                    </div>
                    <div class="flex flex-wrap items-center gap-4 mt-4 text-sm text-neutral-500">
                        @if($restaurant->address)
                            <span class="flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                {{ $restaurant->address }}
                            </span>
                        @endif
                        @if($restaurant->phone)
                            <a href="tel:{{ $restaurant->phone }}" class="flex items-center gap-1.5 transition-colors" style="color: {{ $primaryColor }};">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                                {{ $restaurant->phone }}
                            </a>
                        @endif
                        @if($restaurant->estimated_prep_time)
                            <span class="flex items-center gap-1.5">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                ~{{ $restaurant->estimated_prep_time }} min
                            </span>
                        @endif
                        @if($this->reviewsCount > 0)
                            <span class="flex items-center gap-1.5">
                                <svg class="w-4 h-4 text-yellow-400 fill-current" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
                                <span class="font-semibold text-neutral-900">{{ number_format($this->averageRating, 1) }}</span>
                                <span class="text-neutral-400">({{ $this->reviewsCount }} avis)</span>
                            </span>
                        @endif
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
                        <h2 class="text-2xl font-bold text-neutral-900 font-display tracking-tight">Avis clients</h2>
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
                    <div class="bg-white rounded-xl border border-neutral-200/80 p-5 hover:shadow-lg hover:border-neutral-300/50 transition-all duration-300">
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

    <!-- Navigation Categories (Sticky) — plus gros touch targets sur mobile -->
    <nav class="sticky top-0 z-30 bg-white/95 backdrop-blur-md border-b border-neutral-200/60 shadow-sm sm:mt-8">
        <div class="max-w-5xl mx-auto px-3 sm:px-4">
            {{-- Barre de recherche intégrée sur mobile --}}
            <div class="pt-3 pb-2 sm:hidden">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text"
                           wire:model.live.debounce.300ms="searchQuery"
                           class="w-full pl-9 pr-4 py-2.5 bg-neutral-100 border-0 rounded-xl text-sm text-neutral-700 placeholder-neutral-400 focus:ring-2 focus:bg-white transition-all outline-none"
                           style="--tw-ring-color: {{ $primaryColor }};"
                           placeholder="Rechercher un plat...">
                    @if($searchQuery)
                        <button wire:click="$set('searchQuery', '')" class="absolute right-3 top-1/2 -translate-y-1/2 w-5 h-5 flex items-center justify-center rounded-full bg-neutral-300 text-white">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                        </button>
                    @endif
                </div>
            </div>

            <div class="flex items-center justify-between gap-3 py-2.5 sm:py-3">
                <div class="flex items-center gap-2 overflow-x-auto scrollbar-hide flex-1 pb-0.5">
                    <button wire:click="setCategory(null)"
                            class="flex-shrink-0 px-4 py-2.5 sm:py-2 rounded-full text-sm font-semibold transition-all duration-200 whitespace-nowrap active:scale-95 {{ !$activeCategory ? 'text-white shadow-md' : 'bg-neutral-100 text-neutral-600 hover:bg-neutral-200' }}"
                            @if(!$activeCategory) style="background-color: {{ $primaryColor }}; box-shadow: 0 4px 12px {{ $primaryColor }}40;" @endif>
                        Tout
                    </button>
                    @foreach($this->categories as $category)
                        <button wire:click="setCategory({{ $category->id }})"
                                class="flex-shrink-0 px-4 py-2.5 sm:py-2 rounded-full text-sm font-semibold transition-all duration-200 whitespace-nowrap active:scale-95 {{ $activeCategory === $category->id ? 'text-white shadow-md' : 'bg-neutral-100 text-neutral-600 hover:bg-neutral-200' }}"
                                @if($activeCategory === $category->id) style="background-color: {{ $primaryColor }}; box-shadow: 0 4px 12px {{ $primaryColor }}40;" @endif>
                            {{ $category->name }}
                        </button>
                    @endforeach
                </div>

                <!-- Cart Button (Desktop) -->
                <button wire:click="toggleCart"
                        class="hidden lg:flex items-center gap-2 px-4 py-2 text-white rounded-xl transition-all duration-200 font-semibold relative hover:opacity-90 hover:-translate-y-0.5 flex-shrink-0"
                        style="background-color: {{ $primaryColor }}; box-shadow: 0 4px 12px {{ $primaryColor }}40;">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                    Panier
                    @if($this->cartItemsCount > 0)
                        <span class="absolute -top-1.5 -right-1.5 bg-white text-xs font-bold rounded-full h-5 w-5 flex items-center justify-center shadow-sm" style="color: {{ $primaryColor }};">
                            {{ $this->cartItemsCount }}
                        </span>
                    @endif
                </button>
            </div>
        </div>
    </nav>

    <!-- Main Content -->
    <main class="max-w-5xl mx-auto px-3 sm:px-4 py-5 sm:py-8">
        <!-- Search Bar (Desktop) -->
        <div class="mb-6 sm:mb-8 hidden sm:block">
            <div class="relative group">
                <svg class="absolute left-4 top-1/2 -translate-y-1/2 w-5 h-5 text-neutral-400 group-focus-within:text-neutral-600 transition-colors duration-200" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text"
                       wire:model.live.debounce.300ms="searchQuery"
                       class="w-full pl-12 pr-5 py-3.5 bg-white border border-neutral-200 rounded-2xl text-neutral-700 placeholder-neutral-400 shadow-sm focus:ring-2 focus:border-transparent focus:shadow-md transition-all duration-200 outline-none"
                       style="--tw-ring-color: {{ $primaryColor }};"
                       placeholder="Rechercher un plat...">
                @if($searchQuery)
                    <button wire:click="$set('searchQuery', '')"
                            class="absolute right-4 top-1/2 -translate-y-1/2 w-6 h-6 flex items-center justify-center rounded-full bg-neutral-200 hover:bg-neutral-300 transition-colors text-neutral-500">
                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>
                @endif
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
                    <button wire:click="$set('searchQuery', '')" class="mt-4 font-medium hover:opacity-80 transition-opacity" style="color: {{ $primaryColor }};">
                        Voir tous les plats
                    </button>
                @endif
            </div>
        @else
            <div class="grid grid-cols-2 lg:grid-cols-3 gap-3 sm:gap-5">
                @foreach($this->dishes as $index => $dish)
                    <div wire:click="openDish({{ $dish->id }})"
                         class="group bg-white rounded-xl sm:rounded-2xl overflow-hidden shadow-sm border border-neutral-200/60 hover:shadow-lg hover:-translate-y-1 transition-all duration-300 cursor-pointer active:scale-[0.98] {{ !$dish->is_available ? 'opacity-60' : '' }}"
                         style="animation: fadeInUp .4s ease-out {{ $index * 0.04 }}s both;">
                        <!-- Image -->
                        <div class="aspect-square sm:aspect-[4/3] relative overflow-hidden">
                            @if($dish->image_path)
                                <img src="{{ Storage::url($dish->image_path) }}"
                                     alt="{{ $dish->name }}"
                                     loading="lazy"
                                     class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105">
                                <div class="absolute inset-0 bg-gradient-to-t from-black/20 via-transparent to-transparent opacity-0 group-hover:opacity-100 transition-opacity duration-300"></div>
                            @else
                                <div class="w-full h-full flex items-center justify-center"
                                     style="background: linear-gradient(135deg, {{ $primaryColor }}12 0%, {{ $primaryColor }}25 100%);">
                                    <svg class="w-10 sm:w-14 h-10 sm:h-14 opacity-30" fill="none" stroke="{{ $primaryColor }}" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                    </svg>
                                </div>
                            @endif
                            @if($dish->is_featured)
                                <div class="absolute top-2 left-2 sm:top-3 sm:left-3 z-20">
                                    <span class="px-2 py-0.5 sm:px-2.5 sm:py-1 text-white rounded-full text-[10px] sm:text-xs font-bold flex items-center gap-1 shadow-md" style="background-color: {{ $primaryColor }};">
                                        <svg class="w-2.5 h-2.5 sm:w-3 sm:h-3" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                        <span class="hidden sm:inline">Populaire</span>
                                    </span>
                                </div>
                            @endif
                            @if(!$dish->is_available)
                                <div class="absolute inset-0 bg-black/50 backdrop-blur-[1px] flex items-center justify-center z-20">
                                    <span class="px-2 sm:px-3 py-1 sm:py-1.5 bg-neutral-900/80 text-white rounded-full text-xs font-medium">
                                        Indisponible
                                    </span>
                                </div>
                            @endif
                            {{-- Bouton "+" en overlay sur l'image (mobile) --}}
                            @if($dish->is_available)
                                <button class="absolute bottom-2 right-2 w-8 h-8 sm:hidden text-white rounded-full flex items-center justify-center shadow-lg active:scale-90 transition-transform z-20"
                                        style="background-color: {{ $primaryColor }};">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v12m6-6H6"/>
                                    </svg>
                                </button>
                            @endif
                        </div>

                        <!-- Content — compact sur mobile -->
                        <div class="p-2.5 sm:p-4">
                            <h3 class="font-semibold text-neutral-900 line-clamp-1 text-sm sm:text-base">{{ $dish->name }}</h3>
                            @if($dish->description)
                                <p class="text-xs sm:text-sm text-neutral-500 line-clamp-1 sm:line-clamp-2 mt-0.5 sm:mt-1 leading-relaxed">{{ $dish->description }}</p>
                            @endif
                            <div class="flex items-center justify-between mt-2 sm:mt-3">
                                <span class="text-sm sm:text-base font-bold" style="color: {{ $primaryColor }};">{{ number_format($dish->price, 0, ',', ' ') }} F</span>
                                @if($dish->is_available)
                                    <button class="hidden sm:flex w-9 h-9 text-white rounded-full items-center justify-center transition-all duration-200 hover:scale-110 active:scale-95"
                                            style="background-color: {{ $primaryColor }}; box-shadow: 0 4px 12px {{ $primaryColor }}50;">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6v12m6-6H6"/>
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

    <!-- Mobile Cart Button — barre fixe en bas style Glovo -->
    @if($this->cartItemsCount > 0)
        <div class="fixed bottom-0 left-0 right-0 z-30 p-3 pb-[max(0.75rem,env(safe-area-inset-bottom))] lg:hidden"
             style="background: linear-gradient(to top, white 70%, transparent);">
            <button wire:click="toggleCart"
                    class="w-full flex items-center justify-between px-5 py-3.5 text-white rounded-2xl shadow-2xl transition-all active:scale-[0.98]"
                    style="background: linear-gradient(135deg, {{ $primaryColor }}, {{ $secondaryColor }});">
                <div class="flex items-center gap-3">
                    <div class="relative">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <span class="font-semibold text-sm">Voir le panier</span>
                    <span class="bg-white/25 px-2 py-0.5 rounded-full text-xs font-bold">{{ $this->cartItemsCount }}</span>
                </div>
                <span class="font-bold text-lg">{{ number_format($this->cartTotal, 0, ',', ' ') }} F</span>
            </button>
        </div>
        
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
                                @if(!$this->isRestaurantOpen) disabled @endif
                                class="w-full py-4 text-white font-bold rounded-xl transition shadow-lg @if(!$this->isRestaurantOpen) opacity-50 cursor-not-allowed @endif" 
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

    <!-- Footer — espace supplémentaire pour la barre panier mobile -->
    <footer class="bg-white border-t border-neutral-200/80 mt-12 sm:mt-20 pb-20 sm:pb-0">
        <div class="max-w-5xl mx-auto px-4 py-8 sm:py-12">
            <div class="flex flex-col items-center gap-3 text-center">
                <div class="flex items-center gap-2 text-sm text-neutral-500">
                    <span>Propulsé par</span>
                    <a href="{{ route('home') }}" class="font-semibold hover:opacity-80 transition-opacity" style="color: {{ $primaryColor }};">
                        MenuPro
                    </a>
                </div>
                <p class="text-xs text-neutral-400">Commande en ligne & paiement Mobile Money</p>
            </div>
        </div>
    </footer>

    <!-- Dish Detail Modal — Bottom sheet natif sur mobile -->
    @if($this->selectedDish)
        <div class="fixed inset-0 z-50 overflow-y-auto"
             x-data="{ quantity: 1, selectedOptions: [], instructions: '' }"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100">
            <div class="fixed inset-0 bg-black/60 backdrop-blur-sm" wire:click="closeDish"></div>

            {{-- Desktop: centré — Mobile: bottom sheet --}}
            <div class="flex min-h-screen items-end sm:items-center justify-center sm:p-4">
                <div class="relative w-full sm:max-w-md bg-white sm:rounded-2xl rounded-t-3xl shadow-2xl overflow-hidden max-h-[92vh] flex flex-col"
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="translate-y-full sm:translate-y-0 sm:scale-95 sm:opacity-0"
                     x-transition:enter-end="translate-y-0 sm:scale-100 sm:opacity-100">

                    {{-- Poignée de fermeture mobile --}}
                    <div class="sm:hidden flex justify-center pt-3 pb-1">
                        <div class="w-10 h-1 bg-neutral-300 rounded-full"></div>
                    </div>

                    <button wire:click="closeDish"
                            class="absolute top-4 right-4 z-10 w-8 h-8 bg-white/90 backdrop-blur-sm rounded-full flex items-center justify-center shadow-md sm:flex hidden">
                        <svg class="w-4 h-4 text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>

                    <div class="overflow-y-auto flex-1">
                        <div class="aspect-[4/3] sm:aspect-video bg-neutral-100">
                            @if($this->selectedDish->image_path)
                                <img src="{{ Storage::url($this->selectedDish->image_path) }}"
                                     alt="{{ $this->selectedDish->name }}"
                                     class="w-full h-full object-cover">
                            @else
                                <div class="w-full h-full flex items-center justify-center bg-gradient-to-br from-neutral-100 to-neutral-200">
                                    <svg class="w-20 h-20 text-neutral-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </div>
                            @endif
                        </div>

                        <div class="p-5 sm:p-6">
                            <div class="flex items-start justify-between gap-3">
                                <h2 class="text-xl font-bold text-neutral-900 font-display">{{ $this->selectedDish->name }}</h2>
                                <span class="text-xl font-bold flex-shrink-0" style="color: {{ $primaryColor }};">{{ number_format($this->selectedDish->price, 0, ',', ' ') }} F</span>
                            </div>
                            @if($this->selectedDish->description)
                                <p class="text-neutral-500 mt-2 text-sm leading-relaxed">{{ $this->selectedDish->description }}</p>
                            @endif

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
                                                               class="rounded border-neutral-300" style="--tw-ring-color: {{ $primaryColor }}; accent-color: {{ $primaryColor }};">
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

                    </div>{{-- fin overflow-y-auto --}}

                    {{-- Barre d'action fixe en bas --}}
                    <div class="p-4 bg-white border-t border-neutral-100 flex items-center gap-3 pb-[max(1rem,env(safe-area-inset-bottom))]">
                        <div class="flex items-center bg-neutral-100 rounded-xl overflow-hidden">
                            <button @click="quantity = Math.max(1, quantity - 1)" class="w-10 h-10 flex items-center justify-center text-neutral-600 hover:bg-neutral-200 active:bg-neutral-300 transition font-bold text-lg">−</button>
                            <span class="w-8 text-center font-bold text-neutral-900" x-text="quantity"></span>
                            <button @click="quantity++" class="w-10 h-10 flex items-center justify-center hover:bg-neutral-200 active:bg-neutral-300 transition font-bold text-lg" style="color: {{ $primaryColor }};">+</button>
                        </div>

                        @if(!$this->isRestaurantOpen)
                            <button disabled class="flex-1 py-3.5 bg-neutral-200 text-neutral-500 font-bold rounded-xl cursor-not-allowed text-sm">
                                Restaurant fermé
                            </button>
                        @elseif($this->selectedDish->is_available)
                            <button @click="$wire.addToCart({{ $this->selectedDish->id }}, quantity, selectedOptions, instructions)"
                                    class="flex-1 py-3.5 text-white font-bold rounded-xl transition-all active:scale-[0.97] text-sm shadow-lg"
                                    style="background-color: {{ $primaryColor }}; box-shadow: 0 4px 16px {{ $primaryColor }}50;">
                                Ajouter au panier
                            </button>
                        @else
                            <button disabled class="flex-1 py-3.5 bg-neutral-200 text-neutral-500 font-bold rounded-xl cursor-not-allowed text-sm">
                                Indisponible
                            </button>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @endif

    <!-- Floating Reservation Button (gauche sur mobile pour éviter chevauchement avec panier, droite sur desktop) -->
    @if($restaurant->reservations_enabled ?? true)
        <div x-data="{ showReservationModal: false }" class="fixed bottom-6 left-6 z-40 lg:left-auto lg:right-6">
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
                                       class="w-full px-4 py-3 border border-neutral-300 rounded-xl focus:outline-none focus:ring-2 focus:border-transparent transition"
                                       style="--tw-ring-color: {{ $primaryColor }};"
                                       placeholder="Votre nom">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 mb-2">Email *</label>
                                <input type="email" 
                                       name="customer_email" 
                                       required
                                       class="w-full px-4 py-3 border border-neutral-300 rounded-xl focus:outline-none focus:ring-2 focus:border-transparent transition"
                                       style="--tw-ring-color: {{ $primaryColor }};"
                                       placeholder="votre@email.com">
                            </div>
                        </div>
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 mb-2">Téléphone *</label>
                                <input type="tel" 
                                       name="customer_phone" 
                                       required
                                       class="w-full px-4 py-3 border border-neutral-300 rounded-xl focus:outline-none focus:ring-2 focus:border-transparent transition"
                                       style="--tw-ring-color: {{ $primaryColor }};"
                                       placeholder="+225 XX XX XX XX XX">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 mb-2">Nombre de personnes *</label>
                                <input type="number" 
                                       name="number_of_guests" 
                                       required
                                       min="1"
                                       max="50"
                                       class="w-full px-4 py-3 border border-neutral-300 rounded-xl focus:outline-none focus:ring-2 focus:border-transparent transition"
                                       style="--tw-ring-color: {{ $primaryColor }};"
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
                                       class="w-full px-4 py-3 border border-neutral-300 rounded-xl focus:outline-none focus:ring-2 focus:border-transparent transition"
                                       style="--tw-ring-color: {{ $primaryColor }};">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 mb-2">Heure *</label>
                                <input type="time" 
                                       name="reservation_time" 
                                       required
                                       class="w-full px-4 py-3 border border-neutral-300 rounded-xl focus:outline-none focus:ring-2 focus:border-transparent transition"
                                       style="--tw-ring-color: {{ $primaryColor }};">
                            </div>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">Demandes spéciales (optionnel)</label>
                            <textarea name="special_requests" 
                                      rows="3"
                                      class="w-full px-4 py-3 border border-neutral-300 rounded-xl focus:outline-none focus:ring-2 focus:border-transparent transition resize-none"
                                      style="--tw-ring-color: {{ $primaryColor }};"
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
