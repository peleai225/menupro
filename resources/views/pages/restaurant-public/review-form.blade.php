<x-layouts.restaurant-public :restaurant="$restaurant">
    <div class="min-h-screen bg-neutral-50 py-8">
        <div class="max-w-2xl mx-auto px-4">
            <!-- Header -->
            <div class="text-center mb-8">
                <div class="w-20 h-20 bg-primary-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-10 h-10 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-neutral-900">Laissez votre avis</h1>
                <p class="text-neutral-500 mt-1">Commande #{{ $order->reference }}</p>
            </div>

            <!-- Form -->
            <div class="card p-6">
                <form action="{{ route('r.review.store', [$restaurant->slug, $order]) }}" method="POST">
                    @csrf

                    <!-- Customer Info -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">
                                Nom complet *
                            </label>
                            <input type="text" 
                                   name="customer_name" 
                                   value="{{ old('customer_name', $order->customer_name) }}"
                                   required
                                   class="w-full h-12 px-4 bg-white border border-neutral-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 @error('customer_name') border-red-500 @enderror">
                            @error('customer_name')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">
                                Email *
                            </label>
                            <input type="email" 
                                   name="customer_email" 
                                   value="{{ old('customer_email', $order->customer_email) }}"
                                   required
                                   class="w-full h-12 px-4 bg-white border border-neutral-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 @error('customer_email') border-red-500 @enderror">
                            @error('customer_email')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Rating -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-neutral-700 mb-3">
                            Note *
                        </label>
                        <div class="flex items-center gap-2" x-data="{ rating: {{ old('rating', 0) }} }">
                            <input type="hidden" name="rating" x-model="rating" required>
                            @for($i = 5; $i >= 1; $i--)
                                <button type="button"
                                        @click="rating = {{ $i }}"
                                        class="w-12 h-12 transition-all hover:scale-110"
                                        :class="rating >= {{ $i }} ? 'text-yellow-400' : 'text-neutral-300'">
                                    <svg class="w-full h-full" fill="currentColor" viewBox="0 0 20 20">
                                        <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                    </svg>
                                </button>
                            @endfor
                            <span class="ml-4 text-sm text-neutral-600" x-show="rating > 0">
                                <span x-text="rating"></span>/5 étoiles
                            </span>
                        </div>
                        @error('rating')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Comment -->
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-neutral-700 mb-2">
                            Commentaire (optionnel)
                        </label>
                        <textarea name="comment" 
                                  rows="5"
                                  maxlength="1000"
                                  placeholder="Partagez votre expérience..."
                                  class="w-full px-4 py-3 bg-white border border-neutral-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 @error('comment') border-red-500 @enderror">{{ old('comment') }}</textarea>
                        <p class="text-xs text-neutral-500 mt-1">Maximum 1000 caractères</p>
                        @error('comment')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center gap-3">
                        <a href="{{ route('r.order.status', [$restaurant->slug, $order]) }}" 
                           class="btn btn-outline flex-1">
                            Annuler
                        </a>
                        <button type="submit" 
                                class="btn btn-primary flex-1 flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Envoyer l'avis
                        </button>
                    </div>
                </form>
            </div>

            <!-- Info -->
            <div class="mt-6 p-4 bg-blue-50 border border-blue-200 rounded-xl">
                <p class="text-sm text-blue-800">
                    <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Votre avis sera publié après modération par le restaurant.
                </p>
            </div>
        </div>
    </div>
</x-layouts.restaurant-public>

