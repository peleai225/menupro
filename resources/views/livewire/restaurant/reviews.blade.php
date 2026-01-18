<div>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-neutral-900">Avis Clients</h1>
            <p class="text-neutral-500 mt-1">Gérez les avis et commentaires de vos clients.</p>
        </div>
    </div>

    @php
        $stats = $this->stats;
    @endphp

    <!-- Stats -->
    @if($showStats && !empty($stats))
        <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-8">
            <div class="card p-6">
                <p class="text-sm font-medium text-neutral-500 mb-2">Note moyenne</p>
                <div class="flex items-center gap-2">
                    <p class="text-3xl font-bold text-neutral-900">{{ $stats['average_rating'] ?? 0 }}</p>
                    <div class="flex text-yellow-400">
                        @for($i = 1; $i <= 5; $i++)
                            <svg class="w-5 h-5 {{ $i <= ($stats['average_rating'] ?? 0) ? 'fill-current' : 'text-neutral-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        @endfor
                    </div>
                </div>
            </div>
            <div class="card p-6">
                <p class="text-sm font-medium text-neutral-500 mb-2">Total avis</p>
                <p class="text-3xl font-bold text-neutral-900">{{ $stats['total'] ?? 0 }}</p>
            </div>
            <div class="card p-6">
                <p class="text-sm font-medium text-neutral-500 mb-2">Approuvés</p>
                <p class="text-3xl font-bold text-secondary-600">{{ $stats['approved'] ?? 0 }}</p>
            </div>
            <div class="card p-6">
                <p class="text-sm font-medium text-neutral-500 mb-2">En attente</p>
                <p class="text-3xl font-bold text-accent-600">{{ $stats['pending'] ?? 0 }}</p>
            </div>
        </div>

        <!-- Ratings Distribution -->
        <div class="card p-6 mb-6">
            <h2 class="text-lg font-bold text-neutral-900 mb-4">Répartition des notes</h2>
            <div class="space-y-3">
                @for($i = 5; $i >= 1; $i--)
                    @php
                        $count = $stats['ratings_distribution'][$i] ?? 0;
                        $totalApproved = $stats['approved'] ?? 1;
                        $percentage = $totalApproved > 0 ? round(($count / $totalApproved) * 100) : 0;
                    @endphp
                    <div class="flex items-center gap-4">
                        <div class="flex items-center gap-1 w-20">
                            <span class="text-sm font-medium text-neutral-700">{{ $i }}</span>
                            <svg class="w-4 h-4 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                            </svg>
                        </div>
                        <div class="flex-1 h-2 bg-neutral-100 rounded-full overflow-hidden">
                            <div class="h-full bg-yellow-400 rounded-full transition-all" style="width: {{ $percentage }}%"></div>
                        </div>
                        <span class="text-sm font-medium text-neutral-700 w-16 text-right">{{ $count }} ({{ $percentage }}%)</span>
                    </div>
                @endfor
            </div>
        </div>
    @endif

    <!-- Filters -->
    <div class="card p-6 mb-6">
        <div class="flex flex-col sm:flex-row gap-4">
            <!-- Search -->
            <div class="flex-1">
                <input type="text" wire:model.live.debounce.300ms="search" 
                       placeholder="Rechercher un avis..." 
                       class="w-full h-12 px-4 bg-white border border-neutral-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>
            <!-- Filter -->
            <div>
                <select wire:model.live="filter" class="h-12 px-4 bg-white border border-neutral-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="all">Tous</option>
                    <option value="approved">Approuvés</option>
                    <option value="pending">En attente</option>
                    <option value="rejected">Rejetés</option>
                </select>
            </div>
            <!-- Rating Filter -->
            <div>
                <select wire:model.live="ratingFilter" class="h-12 px-4 bg-white border border-neutral-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="all">Toutes les notes</option>
                    <option value="5">5 étoiles</option>
                    <option value="4">4 étoiles</option>
                    <option value="3">3 étoiles</option>
                    <option value="2">2 étoiles</option>
                    <option value="1">1 étoile</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Reviews List -->
    @if($reviews->count() > 0)
        <div class="space-y-4">
            @foreach($reviews as $review)
                <div class="card p-6 hover:shadow-lg transition-shadow {{ !$review->is_approved ? 'bg-accent-50/30 border-accent-200' : '' }}">
                    <div class="flex items-start justify-between gap-4">
                        <div class="flex-1">
                            <!-- Header -->
                            <div class="flex items-start gap-4 mb-3">
                                <!-- Avatar -->
                                <div class="w-12 h-12 bg-primary-100 rounded-full flex items-center justify-center flex-shrink-0">
                                    <span class="text-lg font-bold text-primary-600">
                                        {{ strtoupper(substr($review->customer_name, 0, 1)) }}
                                    </span>
                                </div>

                                <!-- Info -->
                                <div class="flex-1">
                                    <div class="flex items-center gap-3 mb-1">
                                        <h3 class="font-bold text-neutral-900">{{ $review->customer_name }}</h3>
                                        @if(!$review->is_approved)
                                            <span class="badge bg-accent-500 text-white px-3 py-1 rounded-full text-xs font-medium">En attente</span>
                                        @endif
                                    </div>
                                    <p class="text-sm text-neutral-500 mb-2">{{ $review->customer_email }}</p>
                                    
                                    <!-- Rating -->
                                    <div class="flex items-center gap-2 mb-2">
                                        <div class="flex text-yellow-400">
                                            @for($i = 1; $i <= 5; $i++)
                                                <svg class="w-4 h-4 {{ $i <= $review->rating ? 'fill-current' : 'text-neutral-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                                </svg>
                                            @endfor
                                        </div>
                                        <span class="text-xs text-neutral-500">{{ $review->created_at->locale('fr')->diffForHumans() }}</span>
                                    </div>

                                    <!-- Comment -->
                                    @if($review->comment)
                                        <p class="text-neutral-700 mb-3">{{ $review->comment }}</p>
                                    @endif

                                    <!-- Response -->
                                    @if($review->response)
                                        <div class="mt-4 p-4 bg-primary-50 rounded-xl border-l-4 border-primary-500">
                                            <div class="flex items-center gap-2 mb-2">
                                                <span class="text-sm font-bold text-primary-700">Réponse du restaurant</span>
                                                @if($review->responded_at)
                                                    <span class="text-xs text-primary-500">{{ $review->responded_at->locale('fr')->format('d/m/Y H:i') }}</span>
                                                @endif
                                            </div>
                                            <p class="text-sm text-primary-800">{{ $review->response }}</p>
                                        </div>
                                    @endif
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-2 flex-shrink-0">
                            @if(!$review->is_approved)
                                <button wire:click="approve({{ $review->id }})" 
                                        class="btn btn-secondary px-4 py-2 text-sm hover:bg-green-600 hover:text-white active:scale-95 transition-all"
                                        title="Approuver">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </button>
                                <button wire:click="reject({{ $review->id }})" 
                                        class="btn btn-secondary px-4 py-2 text-sm hover:bg-red-600 hover:text-white active:scale-95 transition-all"
                                        title="Rejeter">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                    </svg>
                                </button>
                            @endif
                            <button wire:click="openResponseModal({{ $review->id }})" 
                                    class="btn btn-secondary px-4 py-2 text-sm hover:bg-neutral-700 active:scale-95 transition-all"
                                    title="Répondre">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h10a8 8 0 018 8v2M3 10l6 6m-6-6l6-6"/>
                                </svg>
                            </button>
                            <button wire:click="delete({{ $review->id }})" 
                                    wire:confirm="Êtes-vous sûr de vouloir supprimer cet avis ?"
                                    class="btn btn-secondary px-4 py-2 text-sm hover:bg-red-600 hover:text-white active:scale-95 transition-all"
                                    title="Supprimer">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $reviews->links() }}
        </div>
    @else
        <div class="card p-12 text-center">
            <svg class="w-16 h-16 text-neutral-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
            </svg>
            <h3 class="text-lg font-semibold text-neutral-900 mb-2">Aucun avis</h3>
            <p class="text-neutral-500">Les avis de vos clients apparaîtront ici.</p>
        </div>
    @endif

    <!-- Response Modal -->
    @if($showResponseModal && $editingReview)
        <div x-data="{ show: @entangle('showResponseModal') }" 
             x-show="show" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @keydown.escape.window="show = false; $wire.closeResponseModal()"
             class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
             x-cloak>
            <div x-show="show"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 @click.away="show = false; $wire.closeResponseModal()"
                 class="bg-white rounded-2xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                
                <!-- Header -->
                <div class="p-6 border-b border-neutral-200">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-bold text-neutral-900">Répondre à l'avis</h2>
                        <button wire:click="closeResponseModal" class="text-neutral-400 hover:text-neutral-600 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Review Info -->
                <div class="p-6 border-b border-neutral-200 bg-neutral-50">
                    <div class="flex items-start gap-4">
                        <div class="w-12 h-12 bg-primary-100 rounded-full flex items-center justify-center flex-shrink-0">
                            <span class="text-lg font-bold text-primary-600">
                                {{ strtoupper(substr($editingReview->customer_name, 0, 1)) }}
                            </span>
                        </div>
                        <div class="flex-1">
                            <h3 class="font-bold text-neutral-900 mb-1">{{ $editingReview->customer_name }}</h3>
                            <div class="flex items-center gap-2 mb-2">
                                <div class="flex text-yellow-400">
                                    @for($i = 1; $i <= 5; $i++)
                                        <svg class="w-4 h-4 {{ $i <= $editingReview->rating ? 'fill-current' : 'text-neutral-300' }}" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                        </svg>
                                    @endfor
                                </div>
                            </div>
                            @if($editingReview->comment)
                                <p class="text-sm text-neutral-700">{{ $editingReview->comment }}</p>
                            @endif
                        </div>
                    </div>
                </div>

                <!-- Form -->
                <form wire:submit.prevent="saveResponse" class="p-6 space-y-6">
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 mb-2">
                            Votre réponse <span class="text-red-500">*</span>
                        </label>
                        <textarea wire:model="response" rows="4" 
                                  placeholder="Merci pour votre avis..."
                                  class="w-full px-4 py-3 bg-white border border-neutral-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 resize-none @error('response') border-red-500 @enderror"></textarea>
                        @error('response')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-neutral-200">
                        <button type="button" wire:click="closeResponseModal" 
                                class="btn btn-secondary px-6 py-3 flex items-center gap-2 hover:bg-neutral-700 active:scale-95 transition-all disabled:opacity-50 shadow-sm hover:shadow-md">
                            Annuler
                        </button>
                        <button type="submit" 
                                class="btn btn-primary px-6 py-3 flex items-center gap-2 hover:bg-primary-600 active:scale-95 transition-all disabled:opacity-50 disabled:cursor-not-allowed shadow-sm hover:shadow-md">
                            Enregistrer la réponse
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Flash Messages -->
    @if(session()->has('success'))
        <div x-data="{ show: true }" 
             x-show="show" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-2"
             x-init="setTimeout(() => show = false, 5000)"
             class="fixed bottom-4 right-4 bg-secondary-500 text-white px-6 py-3 rounded-xl shadow-lg z-50"
             x-cloak>
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if(session()->has('error'))
        <div x-data="{ show: true }" 
             x-show="show" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-2"
             x-init="setTimeout(() => show = false, 5000)"
             class="fixed bottom-4 right-4 bg-red-500 text-white px-6 py-3 rounded-xl shadow-lg z-50"
             x-cloak>
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif
</div>

