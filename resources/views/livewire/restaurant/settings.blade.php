<div>
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-neutral-900">Paramètres</h1>
        <p class="text-neutral-500 mt-1">Configurez votre restaurant</p>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
        <div x-data="{ show: true }" 
             x-show="show"
             x-init="setTimeout(() => show = false, 5000)"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-2"
             class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-700 flex items-center justify-between gap-3 shadow-sm">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-emerald-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span class="font-medium">{{ session('success') }}</span>
            </div>
            <button @click="show = false" class="text-emerald-600 hover:text-emerald-800">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    @endif

    @if(session('error'))
        <div x-data="{ show: true }" 
             x-show="show"
             x-init="setTimeout(() => show = false, 5000)"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-2"
             class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 flex items-center justify-between gap-3 shadow-sm">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-red-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
                <span class="font-medium">{{ session('error') }}</span>
            </div>
            <button @click="show = false" class="text-red-600 hover:text-red-800">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>
    @endif

    <!-- Tabs -->
    <div x-data="{ activeTab: '{{ $activeTab }}' }" class="space-y-6">
        <div class="flex overflow-x-auto gap-1.5 p-1.5 bg-neutral-100/80 rounded-2xl mb-8">
            @foreach([
                'general' => ['label' => 'Général', 'icon' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4'],
                'verification' => ['label' => 'Vérification', 'icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z'],
                'appearance' => ['label' => 'Apparence', 'icon' => 'M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01'],
                'delivery' => ['label' => 'Livraison', 'icon' => 'M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0'],
                'payment' => ['label' => 'Paiement', 'icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z'],
                'wallet' => ['label' => 'Wallet', 'icon' => 'M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z'],
                'hours' => ['label' => 'Horaires', 'icon' => 'M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z'],
            ] as $key => $tab)
                <button @click="activeTab = '{{ $key }}'"
                        :class="activeTab === '{{ $key }}' ? 'bg-white text-neutral-900 shadow-sm' : 'text-neutral-500 hover:text-neutral-700 hover:bg-white/50'"
                        class="flex items-center gap-2 px-4 py-2.5 text-sm font-medium whitespace-nowrap rounded-xl transition-all duration-200">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $tab['icon'] }}"/>
                    </svg>
                    {{ $tab['label'] }}
                </button>
            @endforeach
        </div>

        <!-- General Tab -->
        <div x-show="activeTab === 'general'">
            <form wire:submit="saveGeneral" class="space-y-6">
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
                <div class="lg:col-span-2 space-y-6">
                    <!-- Public Link -->
                    <div class="card p-6 bg-gradient-to-r from-primary-50 to-secondary-50 border-2 border-primary-200">
                        <h2 class="text-lg font-bold text-neutral-900 mb-2 flex items-center gap-2">
                            <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                            </svg>
                            Lien public de votre menu
                        </h2>
                        <p class="text-sm text-neutral-600 mb-4">Partagez ce lien avec vos clients pour qu'ils puissent voir votre menu et commander en ligne.</p>
                        
                        <div class="flex items-center gap-2">
                            <div class="flex-1 bg-white rounded-lg border border-primary-200 p-3 flex items-center gap-2">
                                <svg class="w-5 h-5 text-primary-600 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                                </svg>
                                <input type="text" 
                                       id="public-link"
                                       value="{{ $restaurant->slug ? route('r.menu', $restaurant->slug) : 'Slug en cours de génération...' }}" 
                                       readonly
                                       class="flex-1 bg-transparent border-none outline-none text-sm font-mono text-neutral-700">
                            </div>
                            <button type="button"
                                    onclick="copyLink()"
                                    class="px-4 py-3 bg-primary-500 text-white rounded-lg hover:bg-primary-600 transition-colors font-medium flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                                Copier
                            </button>
                            @if($restaurant->slug)
                                <a href="{{ route('r.menu', $restaurant->slug) }}" 
                                   target="_blank"
                                   class="px-4 py-3 bg-white border border-primary-200 text-primary-600 rounded-lg hover:bg-primary-50 transition-colors font-medium flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                    </svg>
                                    Ouvrir
                                </a>
                            @endif
                        </div>
                        <p class="text-xs text-neutral-500 mt-3 flex items-center gap-1">
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Ce lien est accessible publiquement. Assurez-vous que votre restaurant est activé.
                        </p>
                    </div>

                    <!-- Basic Info -->
                    <div class="card p-6">
                        <h2 class="text-lg font-bold text-neutral-900 mb-6">Informations générales</h2>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 mb-2">Nom du restaurant *</label>
                                <input type="text" wire:model="name" class="input @error('name') border-red-500 @enderror">
                                @error('name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 mb-2">Slogan</label>
                                <input type="text" wire:model="tagline" class="input" placeholder="Ex: Le goût de l'authenticité">
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-neutral-700 mb-2">Description</label>
                                <textarea wire:model="description" rows="4" class="input" placeholder="Décrivez votre restaurant..."></textarea>
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-neutral-700 mb-2">Téléphone</label>
                                    <input type="tel" wire:model="phone" class="input" placeholder="+225 07 00 00 00 00">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-neutral-700 mb-2">Email</label>
                                    <input type="email" wire:model="email" class="input" placeholder="contact@restaurant.ci">
                                </div>
                            </div>

                            <div>
                                <label class="block text-sm font-medium text-neutral-700 mb-2">Site web</label>
                                <input type="url" wire:model="website" class="input" placeholder="https://...">
                            </div>
                        </div>
                    </div>

                    <!-- Address -->
                    <div class="card p-6">
                        <h2 class="text-lg font-bold text-neutral-900 mb-6">Adresse</h2>
                        
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 mb-2">Adresse</label>
                                <input type="text" wire:model="address" class="input" placeholder="Rue, quartier...">
                            </div>

                            <div class="grid grid-cols-2 gap-4">
                                <div>
                                    <label class="block text-sm font-medium text-neutral-700 mb-2">Ville</label>
                                    <input type="text" wire:model="city" class="input" placeholder="Abidjan">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-neutral-700 mb-2">Code postal</label>
                                    <input type="text" wire:model="postal_code" class="input" placeholder="00000">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sidebar -->
                <div class="space-y-6">
                    <!-- Logo -->
                    <div class="card p-6">
                        <h2 class="text-lg font-bold text-neutral-900 mb-4">Logo</h2>
                        
                        <div class="space-y-4">
                            <div class="w-32 h-32 mx-auto bg-neutral-100 rounded-xl overflow-hidden">
                                @if($logo)
                                    <img src="{{ $logo->temporaryUrl() }}" class="w-full h-full object-cover">
                                @elseif($existingLogo)
                                    <img src="{{ Storage::url($existingLogo) }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-neutral-300">
                                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                @endif
                            </div>

                            <label class="btn btn-secondary w-full cursor-pointer justify-center px-6 py-3 flex items-center gap-2 shadow-sm hover:shadow-md transition-all">
                                <input type="file" wire:model="logo" accept="image/*" class="hidden">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                </svg>
                                Changer le logo
                            </label>

                            @if($existingLogo)
                                <button type="button" wire:click="deleteMedia('logo')" class="w-full text-sm text-red-500 hover:text-red-600">
                                    Supprimer
                                </button>
                            @endif
                        </div>
                    </div>

                    <!-- Banner -->
                    <div class="card p-6">
                        <h2 class="text-lg font-bold text-neutral-900 mb-4">Bannière</h2>
                        
                        <div class="space-y-4">
                            <div class="aspect-video bg-neutral-100 rounded-xl overflow-hidden">
                                @if($banner)
                                    <img src="{{ $banner->temporaryUrl() }}" class="w-full h-full object-cover">
                                @elseif($existingBanner)
                                    <img src="{{ Storage::url($existingBanner) }}" class="w-full h-full object-cover">
                                @else
                                    <div class="w-full h-full flex items-center justify-center text-neutral-300">
                                        <svg class="w-12 h-12" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                    </div>
                                @endif
                            </div>

                            <label class="btn btn-secondary w-full cursor-pointer justify-center px-6 py-3 flex items-center gap-2 shadow-sm hover:shadow-md transition-all">
                                <input type="file" wire:model="banner" accept="image/*" class="hidden">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                </svg>
                                Changer la bannière
                            </label>

                            @if($existingBanner)
                                <button type="button" wire:click="deleteMedia('banner')" class="w-full text-sm text-red-500 hover:text-red-600">
                                    Supprimer
                                </button>
                            @endif
                        </div>
                    </div>

                    <!-- Save -->
                    <button type="submit" class="w-full btn btn-primary px-6 py-3 flex items-center justify-center gap-2 shadow-sm hover:shadow-md transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        <span wire:loading.remove wire:target="saveGeneral">Enregistrer</span>
                        <span wire:loading wire:target="saveGeneral" class="flex items-center gap-2">
                            <svg class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            Enregistrement...
                        </span>
                    </button>
                </div>
            </div>
            </form>
        </div>

        <!-- Verification Tab -->
        <div x-show="activeTab === 'verification'" x-cloak>
            <div class="max-w-2xl space-y-6">
                <!-- Status Banner -->
                @if($restaurant->is_verified)
                    <div class="p-4 bg-blue-50 border border-blue-200 rounded-xl">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-blue-100 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-blue-600" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-blue-900">Établissement vérifié</h3>
                                <p class="text-sm text-blue-700">Votre badge "Vérifié" est affiché sur votre page publique.</p>
                            </div>
                        </div>
                    </div>
                @elseif($restaurant->has_pending_verification)
                    <div class="p-4 bg-orange-50 border border-orange-200 rounded-xl">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-orange-100 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-orange-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-orange-900">Vérification en cours</h3>
                                <p class="text-sm text-orange-700">Vos documents sont en attente de validation par notre équipe.</p>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="p-4 bg-neutral-50 border border-neutral-200 rounded-xl">
                        <div class="flex items-center gap-3">
                            <div class="w-10 h-10 bg-neutral-100 rounded-full flex items-center justify-center">
                                <svg class="w-6 h-6 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                                </svg>
                            </div>
                            <div>
                                <h3 class="font-semibold text-neutral-900">Non vérifié</h3>
                                <p class="text-sm text-neutral-600">Fournissez votre RCCM pour obtenir le badge "Vérifié" et renforcer la confiance de vos clients.</p>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Form -->
                <form wire:submit="saveVerification" class="card p-6">
                    <h2 class="text-lg font-bold text-neutral-900 mb-2">Documents d'entreprise</h2>
                    <p class="text-neutral-600 mb-6">Ces informations permettent de vérifier votre établissement et d'afficher le badge "Vérifié" sur votre page publique.</p>

                    <div class="space-y-5">
                        <!-- Company Name -->
                        <div>
                            <label for="company_name" class="block text-sm font-medium text-neutral-700 mb-2">
                                Nom de l'entreprise
                            </label>
                            <input 
                                type="text" 
                                id="company_name"
                                wire:model="company_name"
                                placeholder="SARL Le Délice"
                                class="input w-full @error('company_name') border-red-500 @enderror"
                            >
                            @error('company_name')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- RCCM Number -->
                        <div>
                            <label for="rccm" class="block text-sm font-medium text-neutral-700 mb-2">
                                Numéro RCCM
                            </label>
                            <input 
                                type="text" 
                                id="rccm"
                                wire:model="rccm"
                                placeholder="CI-ABJ-XX-2024-XXXXX"
                                class="input w-full font-mono @error('rccm') border-red-500 @enderror"
                            >
                            @error('rccm')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-neutral-500 mt-1">Format: CI-ABJ-XX-2024-XXXXX</p>
                        </div>

                        <!-- RCCM Document -->
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">
                                Extrait RCCM (document)
                            </label>
                            
                            @if($existingRccmDocument)
                                <div class="mb-3 p-3 bg-neutral-50 border border-neutral-200 rounded-lg flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <svg class="w-8 h-8 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                        </svg>
                                        <div>
                                            <span class="text-sm font-medium text-neutral-700">Document actuel</span>
                                            <p class="text-xs text-neutral-500">Cliquez pour voir</p>
                                        </div>
                                    </div>
                                    <div class="flex items-center gap-2">
                                        <a href="{{ Storage::url($existingRccmDocument) }}" target="_blank" class="btn btn-outline btn-sm">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            Voir
                                        </a>
                                    </div>
                                </div>
                            @endif

                            <input 
                                type="file" 
                                wire:model="rccm_document"
                                accept=".pdf,.jpg,.jpeg,.png"
                                class="w-full h-12 px-4 bg-white border border-neutral-200 rounded-xl text-neutral-900 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-primary-500 file:text-white hover:file:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-primary-500 @error('rccm_document') border-red-500 @enderror"
                            >
                            @error('rccm_document')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-neutral-500 mt-1">Format: PDF, JPEG ou PNG. Taille max: 5 Mo</p>

                            <div wire:loading wire:target="rccm_document" class="mt-2 text-sm text-primary-600">
                                <svg class="animate-spin inline w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                Téléchargement en cours...
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 pt-6 border-t border-neutral-200">
                        <button type="submit" class="btn btn-primary px-6 py-3 flex items-center gap-2" wire:loading.attr="disabled">
                            <span wire:loading.remove wire:target="saveVerification">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Enregistrer
                            </span>
                            <span wire:loading wire:target="saveVerification" class="flex items-center gap-2">
                                <svg class="animate-spin w-5 h-5" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                Enregistrement...
                            </span>
                        </button>
                    </div>
                </form>

                <!-- Info Box -->
                <div class="p-4 bg-blue-50 border border-blue-100 rounded-xl">
                    <h3 class="font-medium text-blue-900 mb-2">Pourquoi se faire vérifier ?</h3>
                    <ul class="text-sm text-blue-800 space-y-1">
                        <li class="flex items-start gap-2">
                            <svg class="w-4 h-4 mt-0.5 text-blue-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>Badge "Vérifié" visible sur votre page publique</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="w-4 h-4 mt-0.5 text-blue-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>Renforce la confiance de vos clients</span>
                        </li>
                        <li class="flex items-start gap-2">
                            <svg class="w-4 h-4 mt-0.5 text-blue-600 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span>Distingue votre établissement des autres</span>
                        </li>
                    </ul>
                </div>
            </div>
        </div>

        <!-- Appearance Tab -->
        <div x-show="activeTab === 'appearance'" x-cloak>
            <form wire:submit="saveAppearance" class="space-y-6">
            <div class="card p-6">
                <h2 class="text-lg font-bold text-neutral-900 mb-6">Personnalisation des couleurs</h2>
                <p class="text-neutral-600 mb-6">Personnalisez les couleurs de votre site public pour qu'il corresponde à votre identité visuelle.</p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <!-- Primary Color -->
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 mb-2">
                            Couleur principale
                        </label>
                        <div class="flex items-center gap-3">
                            <input type="color" 
                                   wire:model.live="primary_color" 
                                   class="w-16 h-16 rounded-lg border-2 border-neutral-200 cursor-pointer"
                                   title="Couleur principale">
                            <div class="flex-1">
                                <input type="text" 
                                       wire:model="primary_color" 
                                       class="input font-mono text-sm"
                                       placeholder="#f97316"
                                       pattern="^#[0-9A-Fa-f]{6}$">
                                <p class="text-xs text-neutral-500 mt-1">Utilisée pour les boutons, liens et éléments importants</p>
                            </div>
                        </div>
                        @error('primary_color')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Secondary Color -->
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 mb-2">
                            Couleur secondaire
                        </label>
                        <div class="flex items-center gap-3">
                            <input type="color" 
                                   wire:model.live="secondary_color" 
                                   class="w-16 h-16 rounded-lg border-2 border-neutral-200 cursor-pointer"
                                   title="Couleur secondaire">
                            <div class="flex-1">
                                <input type="text" 
                                       wire:model="secondary_color" 
                                       class="input font-mono text-sm"
                                       placeholder="#1c1917"
                                       pattern="^#[0-9A-Fa-f]{6}$">
                                <p class="text-xs text-neutral-500 mt-1">Utilisée pour les textes et éléments de fond</p>
                            </div>
                        </div>
                        @error('secondary_color')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <!-- Preview -->
                <div class="mt-8 p-6 bg-neutral-50 rounded-xl border-2 border-dashed border-neutral-200">
                    <h3 class="text-sm font-semibold text-neutral-700 mb-4">Aperçu</h3>
                    <div class="space-y-3">
                        <div class="flex items-center gap-3">
                            <button type="button" 
                                    class="px-4 py-2 rounded-lg text-white font-medium transition-all"
                                    style="background-color: {{ $primary_color ?? '#f97316' }};"
                                    onmouseover="this.style.opacity='0.9'" 
                                    onmouseout="this.style.opacity='1'">
                                Bouton principal
                            </button>
                            <a href="#" 
                               class="text-sm font-medium transition-colors"
                               style="color: {{ $primary_color ?? '#f97316' }};"
                               onmouseover="this.style.opacity='0.8'" 
                               onmouseout="this.style.opacity='1'">
                                Lien
                            </a>
                        </div>
                        <div class="p-4 rounded-lg text-white"
                             style="background-color: {{ $secondary_color ?? '#1c1917' }};">
                            <p class="text-sm">Zone avec couleur secondaire</p>
                        </div>
                    </div>
                </div>

                <div class="mt-6 flex items-center justify-end gap-3">
                    <a href="{{ route('r.menu', $restaurant->slug) }}" 
                       target="_blank"
                       class="btn btn-secondary px-4 py-2 flex items-center gap-2 shadow-sm hover:shadow-md transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                        </svg>
                        Voir le site
                    </a>
                    <button type="submit" class="btn btn-primary px-6 py-3 flex items-center gap-2 shadow-sm hover:shadow-md transition-all">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Enregistrer les couleurs
                    </button>
                </div>
            </div>
            </form>
        </div>

        <!-- Delivery Tab -->
        <div x-show="activeTab === 'delivery'" x-cloak>
            <form wire:submit="saveDelivery" class="max-w-2xl">
            <div class="card p-6 space-y-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-semibold text-neutral-900">Activer la livraison</h3>
                        <p class="text-sm text-neutral-500">Proposez la livraison à vos clients</p>
                    </div>
                    <button type="button" wire:click="$toggle('delivery_enabled')"
                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors {{ $delivery_enabled ? 'bg-primary-500' : 'bg-neutral-200' }}">
                        <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $delivery_enabled ? 'translate-x-6' : 'translate-x-1' }}"></span>
                    </button>
                </div>

                @if($delivery_enabled)
                    <div class="border-t border-neutral-200 pt-6 space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">Frais de livraison (FCFA)</label>
                            <input type="number" wire:model="delivery_fee" class="input w-48" min="0" step="100">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">Commande minimum (FCFA)</label>
                            <input type="number" wire:model="min_order_amount" class="input w-48" min="0" step="100">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">Temps de préparation estimé (minutes)</label>
                            <input type="number" wire:model="estimated_prep_time" class="input w-32" min="1">
                        </div>

                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">Zones de livraison</label>
                            <textarea wire:model="delivery_zones" rows="3" class="input" 
                                      placeholder="Ex: Cocody, Plateau, Marcory..."></textarea>
                            <p class="text-xs text-neutral-500 mt-1">Listez les zones que vous desservez</p>
                        </div>
                    </div>
                @endif

                <button type="submit" class="btn btn-primary px-6 py-3 flex items-center gap-2 shadow-sm hover:shadow-md transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Enregistrer
                </button>
            </div>
            </form>
        </div>

        <!-- Payment Tab -->
        <div x-show="activeTab === 'payment'" x-cloak>
            <form wire:submit="savePayment" class="max-w-2xl">
            <div class="card p-6 space-y-6">
                {{-- Modes configurés par la plateforme (Super Admin) --}}
                @if($this->fusionpayPaymentAvailable)
                    <div class="p-4 bg-neutral-50 border border-neutral-200 rounded-xl mb-6">
                        <h3 class="font-semibold text-neutral-900 mb-2">Modes de paiement activés par la plateforme</h3>
                        <p class="text-sm text-neutral-500 mb-4">Ces options sont proposées à vos clients au checkout. Configuration dans Super Admin.</p>
                        <div class="flex flex-wrap gap-2">
                            <span class="inline-flex items-center gap-2 px-3 py-1.5 bg-emerald-100 text-emerald-700 rounded-lg text-sm font-medium">
                                <x-payment-logo method="fusionpay" />
                                FusionPay
                            </span>
                        </div>
                    </div>
                @endif

                <div class="flex items-center justify-between">
                    <div>
                        <h3 class="font-semibold text-neutral-900">Paiement à la livraison</h3>
                        <p class="text-sm text-neutral-500">Accepter les paiements en espèces</p>
                    </div>
                    <button type="button" wire:click="$toggle('cash_on_delivery')"
                            class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors {{ $cash_on_delivery ? 'bg-primary-500' : 'bg-neutral-200' }}">
                        <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $cash_on_delivery ? 'translate-x-6' : 'translate-x-1' }}"></span>
                    </button>
                </div>

                <div class="border-t border-neutral-200 pt-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="font-semibold text-neutral-900">Paiement mobile (Lygos)</h3>
                            <p class="text-sm text-neutral-500">Orange Money, MTN MoMo, Wave...</p>
                        </div>
                        <button type="button" wire:click="$toggle('lygos_enabled')"
                                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors {{ $lygos_enabled ? 'bg-primary-500' : 'bg-neutral-200' }}">
                            <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $lygos_enabled ? 'translate-x-6' : 'translate-x-1' }}"></span>
                        </button>
                    </div>

                    @if($lygos_enabled)
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 mb-2">
                                    Clé API Lygos <span class="text-red-500">*</span>
                                </label>
                                <input type="password" wire:model="lygos_api_key" class="input" placeholder="Votre clé API Lygos">
                                <p class="text-xs text-neutral-500 mt-1">
                                    Clé API obtenue sur votre compte Lygos (lygos.ci)
                                </p>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 mb-2">
                                    Clé Secrète Lygos <span class="text-xs text-neutral-400 font-normal">(Optionnel)</span>
                                </label>
                                <input type="password" wire:model="lygos_api_secret" class="input" placeholder="Optionnel - pour webhooks sécurisés">
                                <p class="text-xs text-neutral-500 mt-1">
                                    Optionnel : Utilisée uniquement pour la vérification des signatures de webhooks (si fournie par Lygos)
                                </p>
                            </div>
                            
                            <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                <p class="text-xs text-blue-800">
                                    <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Obtenez votre clé API sur <a href="https://lygos.ci" target="_blank" class="text-blue-600 hover:underline font-medium">lygos.ci</a>
                                </p>
                            </div>
                        </div>
                    @endif
                </div>

                <div class="border-t border-neutral-200 pt-6 mt-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="font-semibold text-neutral-900">Paiement mobile (GeniusPay)</h3>
                            <p class="text-sm text-neutral-500">Wave, Orange Money, MTN Money via GeniusPay</p>
                        </div>
                        <button type="button" wire:click="$toggle('geniuspay_enabled')"
                                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors {{ $geniuspay_enabled ? 'bg-primary-500' : 'bg-neutral-200' }}">
                            <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $geniuspay_enabled ? 'translate-x-6' : 'translate-x-1' }}"></span>
                        </button>
                    </div>

                    @if($geniuspay_enabled)
                        <div class="space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 mb-2">
                                    API Key GeniusPay <span class="text-red-500">*</span>
                                </label>
                                <input type="password" wire:model="geniuspay_api_key" class="input" placeholder="pk_sandbox_... ou pk_live_...">
                                <p class="text-xs text-neutral-500 mt-1">
                                    Clé publique obtenue sur votre compte GeniusPay (pay.genius.ci)
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 mb-2">
                                    API Secret GeniusPay <span class="text-red-500">*</span>
                                </label>
                                <input type="password" wire:model="geniuspay_api_secret" class="input" placeholder="sk_sandbox_... ou sk_live_...">
                                <p class="text-xs text-neutral-500 mt-1">
                                    Clé secrète de votre compte marchand GeniusPay
                                </p>
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 mb-2">
                                    Webhook Secret <span class="text-xs text-neutral-400 font-normal">(Optionnel)</span>
                                </label>
                                <input type="password" wire:model="geniuspay_webhook_secret" class="input" placeholder="whsec_sandbox_... ou whsec_live_...">
                                <p class="text-xs text-neutral-500 mt-1">
                                    URL webhook à configurer : <code class="bg-neutral-100 px-1 rounded">{{ url('/webhooks/geniuspay') }}</code>
                                </p>
                            </div>
                            <div class="p-3 bg-blue-50 border border-blue-200 rounded-lg">
                                <p class="text-xs text-blue-800">
                                    <svg class="w-4 h-4 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Obtenez vos clés API sur <a href="https://pay.genius.ci" target="_blank" class="text-blue-600 hover:underline font-medium">pay.genius.ci</a>
                                </p>
                            </div>
                        </div>
                    @endif
                </div>

                <!-- MenuPro Hub -->
                <div class="border-t border-neutral-200 pt-6 mt-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <h3 class="font-semibold text-neutral-900">MenuPro Hub</h3>
                            <p class="text-sm text-neutral-500">Paiement direct sur vos comptes Wave, Orange Money, MTN, Moov (0,5% de commission)</p>
                        </div>
                        <button type="button" wire:click="$toggle('menupo_hub_enabled')"
                                class="relative inline-flex h-6 w-11 items-center rounded-full transition-colors {{ $menupo_hub_enabled ? 'bg-primary-500' : 'bg-neutral-200' }}">
                            <span class="inline-block h-4 w-4 transform rounded-full bg-white transition-transform {{ $menupo_hub_enabled ? 'translate-x-6' : 'translate-x-1' }}"></span>
                        </button>
                    </div>

                    @if($menupo_hub_enabled)
                        <div class="space-y-4 p-4 bg-neutral-50 rounded-xl">
                            <p class="text-sm text-neutral-600">Renseignez vos numéros de comptes marchands. Le solde de commission doit être crédité par l'admin.</p>
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 mb-2">ID Marchand Wave</label>
                                <input type="text" wire:model="wave_merchant_id" class="input font-mono uppercase" placeholder="CI12345678" maxlength="10">
                                <p class="text-xs text-neutral-500 mt-1">Format : code pays (2 lettres) + 8 chiffres. Ex : CI12345678 (Côte d'Ivoire), SN12345678 (Sénégal)</p>
                                @error('wave_merchant_id')
                                    <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                                @enderror
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 mb-2">Numéro Orange Money</label>
                                <input type="tel" wire:model="orange_money_number" class="input" placeholder="07 XX XX XX XX">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 mb-2">Numéro MTN MoMo</label>
                                <input type="tel" wire:model="mtn_money_number" class="input" placeholder="07 XX XX XX XX">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 mb-2">Numéro Moov Money</label>
                                <input type="tel" wire:model="moov_money_number" class="input" placeholder="07 XX XX XX XX">
                            </div>
                            <p class="text-xs text-neutral-500">Solde commission actuel : <strong>{{ number_format($restaurant->commission_wallet_balance ?? 0, 0, ',', ' ') }} F</strong>. Contactez le support pour recharger.</p>
                        </div>
                    @endif
                </div>

                <button type="submit" class="btn btn-primary px-6 py-3 flex items-center gap-2 shadow-sm hover:shadow-md transition-all mt-6">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Enregistrer
                </button>
            </div>
            </form>
        </div>

        <!-- Wallet Tab -->
        <div x-show="activeTab === 'wallet'" x-cloak>
            <div class="max-w-2xl space-y-6">
                <div class="card p-6 bg-gradient-to-r from-violet-50 to-purple-50 border-2 border-violet-200">
                    <h2 class="text-lg font-bold text-neutral-900 mb-4 flex items-center gap-2">
                        <svg class="w-6 h-6 text-violet-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        Wallet MenuPro
                    </h2>
                    <p class="text-sm text-neutral-600 mb-6">Solde des paiements reçus sur vos commandes. Demandez un retrait vers votre Mobile Money (Wave, Orange, MTN).</p>
                    
                    <div class="flex items-center gap-4 p-4 bg-white rounded-xl border border-violet-200">
                        <div class="w-14 h-14 bg-violet-100 rounded-xl flex items-center justify-center">
                            <span class="text-2xl font-bold text-violet-600">F</span>
                        </div>
                        <div>
                            <p class="text-sm text-neutral-500">Solde disponible</p>
                            <p class="text-2xl font-bold text-neutral-900">{{ number_format($this->walletBalance, 0, ',', ' ') }} F</p>
                        </div>
                    </div>

                    @if(!$this->hasWallet)
                        <div class="mt-4 p-4 bg-amber-50 border border-amber-200 rounded-xl">
                            <p class="text-sm text-amber-800">
                                <svg class="w-5 h-5 inline-block mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                Votre wallet n'est pas encore configuré. Assurez-vous que le numéro de téléphone du restaurant est renseigné dans l'onglet Général. Contactez le support si le problème persiste.
                            </p>
                        </div>
                    @elseif($this->payoutAvailable && $this->walletBalance >= 500)
                        <form wire:submit="requestPayout" class="mt-6 space-y-4">
                            <div>
                                <label class="block text-sm font-medium text-neutral-700 mb-2">Montant du retrait (FCFA) *</label>
                                <input type="number" 
                                       wire:model="payoutAmount" 
                                       min="500" 
                                       step="5"
                                       class="input @error('payoutAmount') border-red-500 @enderror" 
                                       placeholder="Montant minimum : 500">
                                <p class="text-xs text-neutral-500 mt-1">Montant minimum : 500 F. Doit être un multiple de 5.</p>
                                @error('payoutAmount')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                            </div>
                            <button type="submit" 
                                    wire:loading.attr="disabled"
                                    class="btn px-6 py-3 flex items-center gap-2"
                                    style="background: linear-gradient(135deg, #8b5cf6, #7c3aed); color: white;">
                                <svg wire:loading.remove wire:target="requestPayout" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                </svg>
                                <svg wire:loading wire:target="requestPayout" class="animate-spin h-5 w-5" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                </svg>
                                <span wire:loading.remove wire:target="requestPayout">Demander un retrait</span>
                                <span wire:loading wire:target="requestPayout">Demande en cours...</span>
                            </button>
                        </form>
                    @elseif($this->payoutAvailable && $this->walletBalance > 0 && $this->walletBalance < 500)
                        <p class="mt-4 text-sm text-neutral-600">Solde insuffisant pour un retrait (minimum 500 F).</p>
                    @elseif(!$this->payoutAvailable)
                        <p class="mt-4 text-sm text-neutral-600">Les retraits ne sont pas disponibles. Contactez le support.</p>
                    @endif
                </div>

                <!-- Auto-Payout Settings -->
                <form wire:submit="saveWallet" class="card p-6">
                    <h2 class="text-lg font-bold text-neutral-900 mb-1 flex items-center gap-2">
                        <svg class="w-5 h-5 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        Paiement automatique
                    </h2>
                    <p class="text-sm text-neutral-500 mb-5">Recevez automatiquement vos revenus sur votre Mobile Money après chaque commande payée.</p>

                    <div class="space-y-5">
                        <!-- Toggle auto-payout -->
                        <label class="flex items-center justify-between p-4 bg-neutral-50 rounded-xl cursor-pointer">
                            <div>
                                <span class="font-medium text-neutral-900">Activer le paiement automatique</span>
                                <p class="text-sm text-neutral-500">Le montant net (après commission) sera transféré automatiquement sur votre Mobile Money.</p>
                            </div>
                            <input type="checkbox" wire:model="auto_payout_enabled" class="w-5 h-5 rounded border-neutral-400 text-emerald-500 focus:ring-emerald-500">
                        </label>

                        <!-- Numéro Mobile Money -->
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">Numéro Mobile Money (Wave) *</label>
                            <input type="tel" wire:model="wallet_phone" class="input @error('wallet_phone') border-red-500 @enderror" placeholder="07 XX XX XX XX">
                            <p class="text-xs text-neutral-500 mt-1">Le numéro Wave sur lequel vous recevrez les paiements.</p>
                            @error('wallet_phone')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <!-- Nom du destinataire -->
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">Nom du destinataire</label>
                            <input type="text" wire:model="wallet_recipient_name" class="input @error('wallet_recipient_name') border-red-500 @enderror" placeholder="Nom complet du titulaire du compte">
                            <p class="text-xs text-neutral-500 mt-1">Le nom associé au compte Mobile Money. Si vide, le nom du propriétaire du restaurant sera utilisé.</p>
                            @error('wallet_recipient_name')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>

                        <!-- Montant minimum -->
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">Montant minimum pour auto-payout (FCFA)</label>
                            <input type="number" wire:model="min_payout_amount" min="500" step="100" class="input @error('min_payout_amount') border-red-500 @enderror" placeholder="1000">
                            <p class="text-xs text-neutral-500 mt-1">Le transfert ne sera déclenché que si le solde atteint ce montant. Minimum : 500 F.</p>
                            @error('min_payout_amount')<p class="mt-1 text-sm text-red-600">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div class="flex justify-end mt-6">
                        <button type="submit" class="btn btn-primary flex items-center gap-2">
                            <svg wire:loading.remove wire:target="saveWallet" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            <svg wire:loading wire:target="saveWallet" class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                            </svg>
                            Enregistrer
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Hours Tab -->
        <div x-show="activeTab === 'hours'" x-cloak>
            <form wire:submit="saveHours" class="max-w-2xl">
            <div class="card p-6">
                <h2 class="text-lg font-bold text-neutral-900 mb-6">Horaires d'ouverture</h2>

                <div class="space-y-4">
                    @php
                        $dayLabels = [
                            'monday' => 'Lundi',
                            'tuesday' => 'Mardi',
                            'wednesday' => 'Mercredi',
                            'thursday' => 'Jeudi',
                            'friday' => 'Vendredi',
                            'saturday' => 'Samedi',
                            'sunday' => 'Dimanche',
                        ];
                    @endphp

                    @foreach($dayLabels as $day => $label)
                        <div class="flex items-center gap-4 p-4 bg-neutral-50 rounded-xl">
                            <div class="w-24">
                                <span class="font-medium text-neutral-900">{{ $label }}</span>
                            </div>
                            
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" 
                                       wire:model="opening_hours.{{ $day }}.is_open"
                                       class="w-4 h-4 text-primary-500 rounded focus:ring-primary-500">
                                <span class="text-sm text-neutral-600">Ouvert</span>
                            </label>

                            @if($opening_hours[$day]['is_open'] ?? true)
                                <div class="flex items-center gap-2 ml-auto">
                                    <input type="time" 
                                           wire:model="opening_hours.{{ $day }}.open"
                                           class="input w-32 text-sm">
                                    <span class="text-neutral-400">à</span>
                                    <input type="time" 
                                           wire:model="opening_hours.{{ $day }}.close"
                                           class="input w-32 text-sm">
                                </div>
                            @else
                                <span class="ml-auto text-sm text-neutral-500">Fermé</span>
                            @endif
                        </div>
                    @endforeach
                </div>

                <button type="submit" class="mt-6 btn btn-primary px-6 py-3 flex items-center gap-2 shadow-sm hover:shadow-md transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Enregistrer les horaires
                    </button>
            </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function copyLink() {
    const input = document.getElementById('public-link');
    input.select();
    input.setSelectionRange(0, 99999); // For mobile devices
    
    navigator.clipboard.writeText(input.value).then(function() {
        // Show success message
        const button = event.target.closest('button');
        const originalText = button.innerHTML;
        button.innerHTML = '<svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> Copié !';
        button.classList.add('bg-emerald-500', 'hover:bg-emerald-600');
        button.classList.remove('bg-primary-500', 'hover:bg-primary-600');
        
        setTimeout(() => {
            button.innerHTML = originalText;
            button.classList.remove('bg-emerald-500', 'hover:bg-emerald-600');
            button.classList.add('bg-primary-500', 'hover:bg-primary-600');
        }, 2000);
    }).catch(function(err) {
        console.error('Erreur lors de la copie:', err);
        alert('Erreur lors de la copie du lien');
    });
}
</script>
@endpush

