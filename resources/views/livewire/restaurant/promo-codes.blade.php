<div>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-neutral-900">Codes Promo</h1>
            <p class="text-neutral-500 mt-1">Créez et gérez vos codes promotionnels pour attirer et fidéliser vos clients.</p>
        </div>
        <button wire:click="openCreateModal" class="btn btn-primary px-6 py-3 flex items-center gap-2 hover:scale-105 active:scale-95 transition-all shadow-sm hover:shadow-md">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Nouveau code promo
        </button>
    </div>

    <!-- Filters -->
    <div class="card p-6 mb-6">
        <div class="flex flex-col sm:flex-row gap-4">
            <!-- Search -->
            <div class="flex-1">
                <input type="text" wire:model.live.debounce.300ms="search" 
                       placeholder="Rechercher un code promo..." 
                       class="w-full h-12 px-4 bg-white border border-neutral-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>
            <!-- Filter -->
            <div>
                <select wire:model.live="filter" class="h-12 px-4 bg-white border border-neutral-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="all">Tous</option>
                    <option value="active">Actifs</option>
                    <option value="expired">Expirés</option>
                    <option value="inactive">Inactifs</option>
                </select>
            </div>
        </div>
    </div>

    <!-- Promo Codes List -->
    @if($promoCodes->count() > 0)
        <div class="grid grid-cols-1 gap-4">
            @foreach($promoCodes as $promo)
                <div class="card p-6 hover:shadow-lg transition-shadow">
                    <div class="flex items-start justify-between">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <span class="text-2xl font-bold text-primary-600">{{ $promo->code }}</span>
                                @if($promo->is_valid)
                                    <span class="badge bg-secondary-500 text-white px-3 py-1 rounded-full text-xs font-medium">Valide</span>
                                @else
                                    <span class="badge bg-neutral-400 text-white px-3 py-1 rounded-full text-xs font-medium">Invalide</span>
                                @endif
                                @if(!$promo->is_active)
                                    <span class="badge bg-red-500 text-white px-3 py-1 rounded-full text-xs font-medium">Inactif</span>
                                @endif
                            </div>
                            
                            @if($promo->description)
                                <p class="text-neutral-600 mb-3">{{ $promo->description }}</p>
                            @endif

                            <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                                <div>
                                    <p class="text-xs text-neutral-500 mb-1">Réduction</p>
                                    <p class="font-bold text-neutral-900">{{ $promo->discount_label }}</p>
                                </div>
                                <div>
                                    <p class="text-xs text-neutral-500 mb-1">Utilisations</p>
                                    <p class="font-bold text-neutral-900">
                                        {{ $promo->current_uses }}
                                        @if($promo->max_uses)
                                            / {{ $promo->max_uses }}
                                        @else
                                            / ∞
                                        @endif
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs text-neutral-500 mb-1">Commande min.</p>
                                    <p class="font-bold text-neutral-900">
                                        @if($promo->min_order_amount)
                                            {{ number_format($promo->min_order_amount, 0, ',', ' ') }} F
                                        @else
                                            Aucune
                                        @endif
                                    </p>
                                </div>
                                <div>
                                    <p class="text-xs text-neutral-500 mb-1">Validité</p>
                                    <p class="font-bold text-neutral-900 text-sm">
                                        @if($promo->starts_at && $promo->expires_at)
                                            {{ $promo->starts_at->format('d/m/Y') }} - {{ $promo->expires_at->format('d/m/Y') }}
                                        @elseif($promo->expires_at)
                                            Jusqu'au {{ $promo->expires_at->format('d/m/Y') }}
                                        @else
                                            Illimitée
                                        @endif
                                    </p>
                                </div>
                            </div>
                        </div>

                        <div class="flex items-center gap-2 ml-4">
                            <button wire:click="openEditModal({{ $promo->id }})" 
                                    class="btn btn-secondary px-4 py-2 text-sm hover:bg-neutral-700 active:scale-95 transition-all disabled:opacity-50">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </button>
                            <button wire:click="toggleActive({{ $promo->id }})" 
                                    class="btn btn-secondary px-4 py-2 text-sm hover:bg-neutral-700 active:scale-95 transition-all disabled:opacity-50">
                                @if($promo->is_active)
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                    </svg>
                                @else
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                @endif
                            </button>
                            <button wire:click="delete({{ $promo->id }})" 
                                    wire:confirm="Êtes-vous sûr de vouloir supprimer ce code promo ?"
                                    class="btn btn-secondary px-4 py-2 text-sm hover:bg-red-600 hover:text-white active:scale-95 transition-all disabled:opacity-50">
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
            {{ $promoCodes->links() }}
        </div>
    @else
        <div class="card p-12 text-center">
            <svg class="w-16 h-16 text-neutral-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <h3 class="text-lg font-semibold text-neutral-900 mb-2">Aucun code promo</h3>
            <p class="text-neutral-500 mb-6">Créez votre premier code promo pour attirer de nouveaux clients.</p>
            <button wire:click="openCreateModal" class="btn btn-primary px-6 py-3 flex items-center gap-2 mx-auto shadow-sm hover:shadow-md transition-all">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Créer un code promo
            </button>
        </div>
    @endif

    <!-- Create/Edit Modal -->
    @if($showModal)
        <div x-data="{ show: @entangle('showModal') }" 
             x-show="show" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @keydown.escape.window="show = false; $wire.closeModal()"
             class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
             x-cloak>
            <div x-show="show"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 @click.away="show = false; $wire.closeModal()"
                 class="bg-white rounded-2xl shadow-xl max-w-2xl w-full max-h-[90vh] overflow-y-auto">
                
                <!-- Header -->
                <div class="p-6 border-b border-neutral-200">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-bold text-neutral-900">
                            {{ $editingPromo ? 'Modifier le code promo' : 'Nouveau code promo' }}
                        </h2>
                        <button wire:click="closeModal" class="text-neutral-400 hover:text-neutral-600 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Form -->
                <form wire:submit.prevent="save" class="p-6 space-y-6">
                    <!-- Code -->
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 mb-2">
                            Code promo <span class="text-red-500">*</span>
                        </label>
                        <input type="text" wire:model="code" 
                               placeholder="EXEMPLE2024" 
                               class="w-full h-12 px-4 bg-white border border-neutral-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 @error('code') border-red-500 @enderror"
                               style="text-transform: uppercase;">
                        @error('code')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-neutral-500 mt-1">Lettres majuscules et chiffres uniquement</p>
                    </div>

                    <!-- Description -->
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 mb-2">
                            Description
                        </label>
                        <textarea wire:model="description" rows="2" 
                                  placeholder="Description du code promo..."
                                  class="w-full px-4 py-3 bg-white border border-neutral-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 resize-none @error('description') border-red-500 @enderror"></textarea>
                        @error('description')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Discount Type & Value -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">
                                Type de réduction <span class="text-red-500">*</span>
                            </label>
                            <select wire:model="discount_type" 
                                    class="w-full h-12 px-4 bg-white border border-neutral-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 @error('discount_type') border-red-500 @enderror">
                                <option value="percentage">Pourcentage (%)</option>
                                <option value="fixed">Montant fixe (FCFA)</option>
                            </select>
                            @error('discount_type')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">
                                Valeur <span class="text-red-500">*</span>
                            </label>
                            <input type="number" wire:model="discount_value" min="1" 
                                   placeholder="{{ $discount_type === 'percentage' ? '10' : '1000' }}"
                                   class="w-full h-12 px-4 bg-white border border-neutral-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 @error('discount_value') border-red-500 @enderror">
                            @error('discount_value')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-neutral-500 mt-1">
                                {{ $discount_type === 'percentage' ? 'Pourcentage de réduction' : 'Montant en FCFA' }}
                            </p>
                        </div>
                    </div>

                    <!-- Max Discount Amount (for percentage) -->
                    @if($discount_type === 'percentage')
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">
                                Réduction maximale (FCFA)
                            </label>
                            <input type="number" wire:model="max_discount_amount" min="0" 
                                   placeholder="5000"
                                   class="w-full h-12 px-4 bg-white border border-neutral-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 @error('max_discount_amount') border-red-500 @enderror">
                            @error('max_discount_amount')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-neutral-500 mt-1">Limite la réduction pour les codes en pourcentage</p>
                        </div>
                    @endif

                    <!-- Min Order Amount -->
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 mb-2">
                            Commande minimum (FCFA)
                        </label>
                        <input type="number" wire:model="min_order_amount" min="0" 
                               placeholder="10000"
                               class="w-full h-12 px-4 bg-white border border-neutral-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 @error('min_order_amount') border-red-500 @enderror">
                        @error('min_order_amount')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-neutral-500 mt-1">Montant minimum de commande requis</p>
                    </div>

                    <!-- Usage Limits -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">
                                Nombre d'utilisations max.
                            </label>
                            <input type="number" wire:model="max_uses" min="1" 
                                   placeholder="100"
                                   class="w-full h-12 px-4 bg-white border border-neutral-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 @error('max_uses') border-red-500 @enderror">
                            @error('max_uses')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-neutral-500 mt-1">Laisser vide pour illimité</p>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">
                                Utilisations max. par client
                            </label>
                            <input type="number" wire:model="max_uses_per_customer" min="1" 
                                   placeholder="1"
                                   class="w-full h-12 px-4 bg-white border border-neutral-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 @error('max_uses_per_customer') border-red-500 @enderror">
                            @error('max_uses_per_customer')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-neutral-500 mt-1">Laisser vide pour illimité</p>
                        </div>
                    </div>

                    <!-- Dates -->
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">
                                Date de début
                            </label>
                            <input type="datetime-local" wire:model="starts_at" 
                                   class="w-full h-12 px-4 bg-white border border-neutral-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 @error('starts_at') border-red-500 @enderror">
                            @error('starts_at')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">
                                Date d'expiration
                            </label>
                            <input type="datetime-local" wire:model="expires_at" 
                                   class="w-full h-12 px-4 bg-white border border-neutral-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 @error('expires_at') border-red-500 @enderror">
                            @error('expires_at')
                                <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>

                    <!-- Active -->
                    <div class="flex items-center gap-3">
                        <input type="checkbox" wire:model="is_active" id="is_active" 
                               class="w-5 h-5 text-primary-600 border-neutral-300 rounded focus:ring-primary-500">
                        <label for="is_active" class="text-sm font-medium text-neutral-700">
                            Code promo actif
                        </label>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-neutral-200">
                        <button type="button" wire:click="closeModal" 
                                class="btn btn-secondary px-6 py-3 flex items-center gap-2 hover:bg-neutral-700 active:scale-95 transition-all disabled:opacity-50 shadow-sm hover:shadow-md">
                            Annuler
                        </button>
                        <button type="submit" 
                                class="btn btn-primary px-6 py-3 flex items-center gap-2 hover:bg-primary-600 active:scale-95 transition-all disabled:opacity-50 disabled:cursor-not-allowed shadow-sm hover:shadow-md">
                            {{ $editingPromo ? 'Enregistrer' : 'Créer' }}
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

