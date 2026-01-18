<div>
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-neutral-900">Taxes & Frais</h1>
        <p class="text-neutral-500 mt-1">Configurez les taxes et frais de service pour vos commandes.</p>
    </div>

    <form wire:submit.prevent="save" class="space-y-8">
        <!-- Tax Section -->
        <div class="card p-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-12 h-12 bg-primary-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 7h6m0 10v-5m-3 5h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-neutral-900">Configuration des Taxes</h2>
                    <p class="text-sm text-neutral-500">Configurez les taxes applicables à vos commandes</p>
                </div>
            </div>

            <div class="space-y-6">
                <!-- Tax Name -->
                <div>
                    <label class="block text-sm font-medium text-neutral-700 mb-2">
                        Nom de la taxe
                    </label>
                    <input type="text" wire:model="tax_name" 
                           placeholder="TVA, Tax, etc."
                           class="w-full h-12 px-4 bg-white border border-neutral-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 @error('tax_name') border-red-500 @enderror">
                    @error('tax_name')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <!-- Tax Rate -->
                <div>
                    <label class="block text-sm font-medium text-neutral-700 mb-2">
                        Taux de taxe (%)
                    </label>
                    <input type="number" wire:model="tax_rate" step="0.01" min="0" max="100"
                           placeholder="18.00"
                           class="w-full h-12 px-4 bg-white border border-neutral-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 @error('tax_rate') border-red-500 @enderror">
                    @error('tax_rate')
                        <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                    @enderror
                    <p class="text-xs text-neutral-500 mt-1">Exemple : 18 pour 18% de TVA</p>
                </div>

                <!-- Tax Included -->
                <div class="flex items-center gap-3 p-4 bg-neutral-50 rounded-xl">
                    <input type="checkbox" wire:model="tax_included" id="tax_included" 
                           class="w-5 h-5 text-primary-600 border-neutral-300 rounded focus:ring-primary-500">
                    <label for="tax_included" class="flex-1">
                        <span class="text-sm font-medium text-neutral-700">Taxe incluse dans les prix</span>
                        <p class="text-xs text-neutral-500 mt-1">
                            Si activé, la taxe est déjà incluse dans les prix affichés. Sinon, elle sera ajoutée au total.
                        </p>
                    </label>
                </div>
            </div>
        </div>

        <!-- Service Fee Section -->
        <div class="card p-6">
            <div class="flex items-center gap-3 mb-6">
                <div class="w-12 h-12 bg-secondary-100 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-secondary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <h2 class="text-lg font-bold text-neutral-900">Frais de Service</h2>
                    <p class="text-sm text-neutral-500">Configurez les frais de service optionnels</p>
                </div>
            </div>

            <div class="space-y-6">
                <!-- Service Fee Enabled -->
                <div class="flex items-center gap-3 p-4 bg-neutral-50 rounded-xl">
                    <input type="checkbox" wire:model="service_fee_enabled" id="service_fee_enabled" 
                           class="w-5 h-5 text-primary-600 border-neutral-300 rounded focus:ring-primary-500">
                    <label for="service_fee_enabled" class="flex-1">
                        <span class="text-sm font-medium text-neutral-700">Activer les frais de service</span>
                        <p class="text-xs text-neutral-500 mt-1">
                            Les frais de service seront ajoutés au total de chaque commande
                        </p>
                    </label>
                </div>

                @if($service_fee_enabled)
                    <!-- Service Fee Rate -->
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 mb-2">
                            Frais de service en pourcentage (%)
                        </label>
                        <input type="number" wire:model="service_fee_rate" step="0.01" min="0" max="100"
                               placeholder="10.00"
                               class="w-full h-12 px-4 bg-white border border-neutral-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 @error('service_fee_rate') border-red-500 @enderror">
                        @error('service_fee_rate')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-neutral-500 mt-1">Calculé sur le montant après réduction</p>
                    </div>

                    <!-- Service Fee Fixed -->
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 mb-2">
                            Frais de service fixe (FCFA)
                        </label>
                        <input type="number" wire:model="service_fee_fixed" min="0"
                               placeholder="500"
                               class="w-full h-12 px-4 bg-white border border-neutral-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 @error('service_fee_fixed') border-red-500 @enderror">
                        @error('service_fee_fixed')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-neutral-500 mt-1">Montant fixe ajouté à chaque commande (en centimes)</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Example Calculation -->
        @if($tax_rate || $service_fee_enabled)
            <div class="card p-6 bg-primary-50/30 border border-primary-200">
                <h3 class="text-lg font-bold text-neutral-900 mb-4">Exemple de calcul</h3>
                <div class="space-y-2 text-sm">
                    <div class="flex justify-between">
                        <span class="text-neutral-600">Sous-total (exemple)</span>
                        <span class="font-medium">{{ number_format($example['subtotal'], 0, ',', ' ') }} F</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-neutral-600">Frais de livraison</span>
                        <span class="font-medium">{{ number_format($example['delivery_fee'], 0, ',', ' ') }} F</span>
                    </div>
                    @if($example['tax_amount'] > 0)
                        <div class="flex justify-between">
                            <span class="text-neutral-600">{{ $tax_name ?? 'Taxe' }} 
                                @if($tax_included)
                                    (incluse)
                                @else
                                    ({{ number_format($tax_rate ?? 0, 2) }}%)
                                @endif
                            </span>
                            <span class="font-medium">{{ number_format($example['tax_amount'], 0, ',', ' ') }} F</span>
                        </div>
                    @endif
                    @if($example['service_fee'] > 0)
                        <div class="flex justify-between">
                            <span class="text-neutral-600">Frais de service</span>
                            <span class="font-medium">{{ number_format($example['service_fee'], 0, ',', ' ') }} F</span>
                        </div>
                    @endif
                    <div class="flex justify-between pt-2 border-t border-primary-200">
                        <span class="font-bold text-neutral-900">Total</span>
                        <span class="font-bold text-primary-600 text-lg">{{ number_format($example['total'], 0, ',', ' ') }} F</span>
                    </div>
                </div>
            </div>
        @endif

        <!-- Actions -->
        <div class="flex items-center justify-end gap-3">
            <button type="submit" 
                    class="btn btn-primary px-6 py-3 flex items-center gap-2 hover:bg-primary-600 active:scale-95 transition-all disabled:opacity-50 disabled:cursor-not-allowed shadow-sm hover:shadow-md">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                Enregistrer les paramètres
            </button>
        </div>
    </form>

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

