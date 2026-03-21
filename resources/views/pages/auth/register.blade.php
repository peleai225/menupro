<x-layouts.auth title="Inscription">
    <div 
        x-data="registerForm()" 
        class="animate-fade-in"
    >
        <!-- Progress Steps -->
        <div class="mb-8 sm:mb-10">
            <div class="flex items-center justify-between">
                <!-- Step 1 -->
                <div class="flex items-center gap-2.5">
                    <div
                        class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl flex items-center justify-center font-bold text-sm transition-all duration-300 shadow-sm"
                        :class="step >= 1 ? 'bg-primary-500 text-white shadow-primary-500/25' : 'bg-neutral-100 text-neutral-400'"
                    >
                        <template x-if="step > 1">
                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                        </template>
                        <template x-if="step <= 1">
                            <span>1</span>
                        </template>
                    </div>
                    <span class="text-xs sm:text-sm font-semibold hidden sm:block" :class="step >= 1 ? 'text-neutral-900' : 'text-neutral-400'">Compte</span>
                </div>

                <!-- Connector -->
                <div class="flex-1 h-0.5 mx-3 sm:mx-4 rounded-full overflow-hidden bg-neutral-200">
                    <div class="h-full bg-primary-500 transition-all duration-500 rounded-full" :style="step > 1 ? 'width: 100%' : 'width: 0%'"></div>
                </div>

                <!-- Step 2 -->
                <div class="flex items-center gap-2.5">
                    <div
                        class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl flex items-center justify-center font-bold text-sm transition-all duration-300 shadow-sm"
                        :class="step >= 2 ? 'bg-primary-500 text-white shadow-primary-500/25' : 'bg-neutral-100 text-neutral-400'"
                    >
                        <template x-if="step > 2">
                            <svg class="w-4.5 h-4.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                            </svg>
                        </template>
                        <template x-if="step <= 2">
                            <span>2</span>
                        </template>
                    </div>
                    <span class="text-xs sm:text-sm font-semibold hidden sm:block" :class="step >= 2 ? 'text-neutral-900' : 'text-neutral-400'">Restaurant</span>
                </div>

                <!-- Connector -->
                <div class="flex-1 h-0.5 mx-3 sm:mx-4 rounded-full overflow-hidden bg-neutral-200">
                    <div class="h-full bg-primary-500 transition-all duration-500 rounded-full" :style="step > 2 ? 'width: 100%' : 'width: 0%'"></div>
                </div>

                <!-- Step 3 -->
                <div class="flex items-center gap-2.5">
                    <div
                        class="w-9 h-9 sm:w-10 sm:h-10 rounded-xl flex items-center justify-center font-bold text-sm transition-all duration-300 shadow-sm"
                        :class="step >= 3 ? 'bg-primary-500 text-white shadow-primary-500/25' : 'bg-neutral-100 text-neutral-400'"
                    >
                        <span>3</span>
                    </div>
                    <span class="text-xs sm:text-sm font-semibold hidden sm:block" :class="step >= 3 ? 'text-neutral-900' : 'text-neutral-400'">Plan</span>
                </div>
            </div>
            <!-- Step label for mobile -->
            <div class="sm:hidden mt-3 text-center">
                <span class="text-xs font-semibold text-primary-600 bg-primary-50 px-3 py-1 rounded-full" x-text="step === 1 ? 'Étape 1 : Compte' : (step === 2 ? 'Étape 2 : Restaurant' : 'Étape 3 : Plan')"></span>
            </div>
        </div>

        <!-- Messages d'erreur globaux -->
        @if ($errors->any())
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-red-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="flex-1">
                        <h3 class="text-sm font-semibold text-red-800 mb-2">Erreurs de validation</h3>
                        <ul class="list-disc list-inside space-y-1 text-sm text-red-700">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('register.post') }}" enctype="multipart/form-data" @submit.prevent="submitForm()" id="register-form">
            @csrf

            <!-- ============================================== -->
            <!-- STEP 1: Account Information -->
            <!-- ============================================== -->
            <div x-show="step === 1" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0">
                <div class="text-center lg:text-left mb-5 sm:mb-6">
                    <h1 class="text-xl sm:text-2xl font-bold text-neutral-900 tracking-tight">Créez votre compte</h1>
                    <p class="text-neutral-500 mt-1.5 text-sm sm:text-base">Commencez par vos informations personnelles.</p>
                </div>

                <div class="space-y-4">
                    <!-- Name -->
                    <div>
                        <label for="name" class="block text-sm font-medium text-neutral-700 mb-2">
                            Nom complet <span class="text-red-500">*</span>
                        </label>
                        <div class="relative flex items-center">
                            <span class="absolute left-4 text-neutral-400 pointer-events-none">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </span>
                            <input 
                                type="text" 
                                id="name"
                                name="name" 
                                x-model="formData.name"
                                placeholder="Jean Kouassi"
                                required
                                autocomplete="name"
                                class="w-full h-12 pl-12 pr-4 bg-neutral-50 border border-neutral-200 rounded-xl text-neutral-900 placeholder:text-neutral-400 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 focus:bg-white"
                            >
                        </div>
                    </div>

                    <!-- Email -->
                    <div>
                        <label for="email" class="block text-sm font-medium text-neutral-700 mb-2">
                            Adresse email <span class="text-red-500">*</span>
                        </label>
                        <div class="relative flex items-center">
                            <span class="absolute left-4 text-neutral-400 pointer-events-none">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </span>
                            <input 
                                type="email" 
                                id="email"
                                name="email" 
                                x-model="formData.email"
                                placeholder="vous@exemple.com"
                                required
                                autocomplete="email"
                                class="w-full h-12 pl-12 pr-4 bg-neutral-50 border border-neutral-200 rounded-xl text-neutral-900 placeholder:text-neutral-400 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 focus:bg-white"
                            >
                        </div>
                    </div>

                    <!-- Phone -->
                    <div>
                        <label for="phone" class="block text-sm font-medium text-neutral-700 mb-2">
                            Téléphone <span class="text-red-500">*</span>
                        </label>
                        <div class="relative flex items-center">
                            <span class="absolute left-4 text-neutral-400 pointer-events-none">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                            </span>
                            <input 
                                type="tel" 
                                id="phone"
                                name="phone" 
                                x-model="formData.phone"
                                placeholder="+225 07 00 00 00 00"
                                required
                                autocomplete="tel"
                                class="w-full h-12 pl-12 pr-4 bg-neutral-50 border border-neutral-200 rounded-xl text-neutral-900 placeholder:text-neutral-400 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 focus:bg-white"
                            >
                        </div>
                    </div>

                    <!-- Password -->
                    <div>
                        <label for="password" class="block text-sm font-medium text-neutral-700 mb-2">
                            Mot de passe <span class="text-red-500">*</span>
                        </label>
                        <div class="relative flex items-center">
                            <span class="absolute left-4 text-neutral-400 pointer-events-none">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </span>
                            <input 
                                :type="showPassword ? 'text' : 'password'"
                                id="password"
                                name="password"
                                x-model="formData.password"
                                placeholder="Minimum 8 caractères"
                                required
                                minlength="8"
                                autocomplete="new-password"
                                class="w-full h-12 pl-12 pr-12 bg-neutral-50 border border-neutral-200 rounded-xl text-neutral-900 placeholder:text-neutral-400 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 focus:bg-white"
                            >
                            <button 
                                type="button" 
                                @click="showPassword = !showPassword" 
                                class="absolute right-4 text-neutral-400 hover:text-neutral-600 transition-colors"
                            >
                                <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg x-show="showPassword" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                </svg>
                            </button>
                        </div>
                        <!-- Password Strength Indicator -->
                        <div class="mt-2 flex gap-1">
                            <div class="h-1 flex-1 rounded-full transition-colors" :class="formData.password.length >= 1 ? 'bg-red-400' : 'bg-neutral-200'"></div>
                            <div class="h-1 flex-1 rounded-full transition-colors" :class="formData.password.length >= 4 ? 'bg-yellow-400' : 'bg-neutral-200'"></div>
                            <div class="h-1 flex-1 rounded-full transition-colors" :class="formData.password.length >= 8 ? 'bg-secondary-400' : 'bg-neutral-200'"></div>
                            <div class="h-1 flex-1 rounded-full transition-colors" :class="formData.password.length >= 12 ? 'bg-secondary-500' : 'bg-neutral-200'"></div>
                        </div>
                        <p class="text-xs text-neutral-500 mt-1">Minimum 8 caractères</p>
                    </div>
                </div>

                <button
                    type="button"
                    @click="if(validateStep1()) step = 2"
                    class="btn btn-primary w-full h-13 mt-6 text-base font-semibold shadow-lg shadow-primary-500/20"
                    :class="{ 'opacity-50 cursor-not-allowed': !validateStep1() }"
                >
                    Continuer
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                    </svg>
                </button>
            </div>

            <!-- ============================================== -->
            <!-- STEP 2: Restaurant Information -->
            <!-- ============================================== -->
            <div x-show="step === 2" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0">
                <div class="text-center lg:text-left mb-5 sm:mb-6">
                    <h1 class="text-xl sm:text-2xl font-bold text-neutral-900 tracking-tight">Votre restaurant</h1>
                    <p class="text-neutral-500 mt-1.5 text-sm sm:text-base">Personnalisez l'identité de votre établissement.</p>
                </div>

                <div class="space-y-5">
                    <!-- Restaurant Name -->
                    <div>
                        <label for="restaurant_name" class="block text-sm font-medium text-neutral-700 mb-2">
                            Nom de l'établissement <span class="text-red-500">*</span>
                        </label>
                        <div class="relative flex items-center">
                            <span class="absolute left-4 text-neutral-400 pointer-events-none">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            </span>
                            <input 
                                type="text" 
                                id="restaurant_name"
                                name="restaurant_name" 
                                x-model="formData.restaurant_name"
                                placeholder="Le Délice"
                                required
                                class="w-full h-12 pl-12 pr-4 bg-neutral-50 border border-neutral-200 rounded-xl text-neutral-900 placeholder:text-neutral-400 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 focus:bg-white"
                            >
                        </div>
                    </div>

                    <!-- Restaurant Type -->
                    <div>
                        <label for="restaurant_type" class="block text-sm font-medium text-neutral-700 mb-2">
                            Type d'établissement <span class="text-red-500">*</span>
                        </label>
                        <div class="relative">
                            <select 
                                id="restaurant_type"
                                name="restaurant_type" 
                                x-model="formData.restaurant_type"
                                required
                                class="w-full h-12 pl-4 pr-10 bg-neutral-50 border border-neutral-200 rounded-xl text-neutral-900 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 focus:bg-white appearance-none"
                            >
                                <option value="">Sélectionnez un type</option>
                                <option value="restaurant">Restaurant</option>
                                <option value="bar">Bar</option>
                                <option value="brasserie">Brasserie</option>
                                <option value="maquis">Maquis</option>
                                <option value="traiteur">Traiteur</option>
                                <option value="cafe">Café</option>
                                <option value="food_truck">Food Truck</option>
                                <option value="brunch">Brunch / Petit-déjeuner</option>
                                <option value="evenementiel">Événementiel (mariages, anniversaires, etc.)</option>
                            </select>
                            <svg class="absolute right-4 top-1/2 -translate-y-1/2 w-5 h-5 text-neutral-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                        <p class="text-xs text-neutral-500 mt-1">Choisissez le type d'établissement qui correspond le mieux à votre activité</p>
                    </div>

                    <!-- Company Name -->
                    <div>
                        <label for="company_name" class="block text-sm font-medium text-neutral-700 mb-2">
                            Nom de l'entreprise <span class="text-neutral-400 text-xs">(optionnel)</span>
                        </label>
                        <div class="relative flex items-center">
                            <span class="absolute left-4 text-neutral-400 pointer-events-none">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            </span>
                            <input 
                                type="text" 
                                id="company_name"
                                name="company_name" 
                                x-model="formData.company_name"
                                placeholder="SARL Le Délice (optionnel)"
                                class="w-full h-12 pl-12 pr-4 bg-neutral-50 border border-neutral-200 rounded-xl text-neutral-900 placeholder:text-neutral-400 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 focus:bg-white @error('company_name') border-red-400 bg-red-50 @enderror"
                            >
                        </div>
                        @error('company_name')
                            <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- RCCM Section (Optional) -->
                    <div class="bg-neutral-50 rounded-xl p-4 border border-neutral-200">
                        <div class="flex items-center gap-2 mb-3">
                            <svg class="w-5 h-5 text-secondary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                            <span class="font-medium text-neutral-700">Vérification entreprise</span>
                            <span class="text-xs bg-secondary-100 text-secondary-700 px-2 py-0.5 rounded-full">Optionnel</span>
                        </div>
                        <p class="text-xs text-neutral-500 mb-4">Fournissez votre RCCM pour obtenir le badge "Vérifié" sur votre page publique. Vous pouvez l'ajouter plus tard.</p>
                        
                        <!-- RCCM -->
                        <div class="mb-3">
                            <label for="rccm" class="block text-sm font-medium text-neutral-700 mb-2">
                                Numéro RCCM
                            </label>
                            <div class="relative flex items-center">
                                <span class="absolute left-4 text-neutral-400 pointer-events-none">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                                    </svg>
                                </span>
                                <input 
                                    type="text" 
                                    id="rccm"
                                    name="rccm" 
                                    x-model="formData.rccm"
                                    placeholder="CI-ABJ-XX-2024-XXXXX"
                                    class="w-full h-12 pl-12 pr-4 bg-neutral-50 border border-neutral-200 rounded-xl text-neutral-900 placeholder:text-neutral-400 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 focus:bg-white @error('rccm') border-red-400 bg-red-50 @enderror"
                                >
                            </div>
                            @error('rccm')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                        </div>

                        <!-- RCCM Document Upload -->
                        <div>
                            <label for="rccm_document" class="block text-sm font-medium text-neutral-700 mb-2">
                                Extrait RCCM (document)
                            </label>
                            <div class="relative">
                                <input 
                                    type="file" 
                                    id="rccm_document"
                                    name="rccm_document" 
                                    accept=".pdf,.jpg,.jpeg,.png"
                                    class="w-full h-12 px-4 bg-neutral-50 border border-neutral-200 rounded-xl text-neutral-900 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-primary-500 file:text-white hover:file:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 @error('rccm_document') border-red-400 bg-red-50 @enderror"
                                >
                            </div>
                            @error('rccm_document')
                                <p class="mt-1 text-sm text-red-600">{{ $message }}</p>
                            @enderror
                            <p class="text-xs text-neutral-500 mt-1">PDF, JPEG ou PNG. Max 5 Mo</p>
                        </div>
                    </div>

                    <!-- Logo Upload -->
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 mb-2">
                            Logo <span class="text-neutral-400 font-normal">(optionnel)</span>
                        </label>
                        <div class="flex items-center gap-4">
                            <div class="w-20 h-20 rounded-2xl bg-neutral-100 border-2 border-dashed border-neutral-300 flex items-center justify-center overflow-hidden transition-all hover:border-primary-400 hover:bg-primary-50">
                                <template x-if="logoPreview">
                                    <img :src="logoPreview" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!logoPreview">
                                    <svg class="w-8 h-8 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                </template>
                            </div>
                            <div class="flex-1">
                                <input 
                                    type="file" 
                                    name="logo" 
                                    id="logo-upload"
                                    accept="image/png,image/jpeg,image/webp" 
                                    class="hidden"
                                    @change="logoPreview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null"
                                >
                                <label for="logo-upload" class="btn btn-outline btn-sm cursor-pointer inline-flex">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12"/>
                                    </svg>
                                    Choisir une image
                                </label>
                                <p class="text-xs text-neutral-500 mt-2">PNG, JPG ou WEBP • Max 2 Mo</p>
                            </div>
                        </div>
                    </div>

                    <!-- Banner Upload -->
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 mb-2">
                            Bannière de couverture <span class="text-neutral-400 font-normal">(optionnel)</span>
                        </label>
                        <div class="relative">
                            <div class="w-full h-32 rounded-2xl bg-neutral-100 border-2 border-dashed border-neutral-300 flex items-center justify-center overflow-hidden transition-all hover:border-primary-400 hover:bg-primary-50 cursor-pointer">
                                <template x-if="bannerPreview">
                                    <img :src="bannerPreview" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!bannerPreview">
                                    <div class="text-center">
                                        <svg class="w-10 h-10 text-neutral-400 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <p class="text-sm text-neutral-500 mt-2">Cliquez pour ajouter une bannière</p>
                                    </div>
                                </template>
                            </div>
                            <input 
                                type="file" 
                                name="banner" 
                                accept="image/png,image/jpeg,image/webp" 
                                class="absolute inset-0 opacity-0 cursor-pointer"
                                @change="bannerPreview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null"
                            >
                        </div>
                        <p class="text-xs text-neutral-500 mt-2">Recommandé : 1200×400px • Max 5 Mo</p>
                    </div>
                </div>

                <div class="flex gap-3 mt-6">
                    <button type="button" @click="step = 1" class="btn btn-ghost flex-1 h-12">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"/>
                        </svg>
                        Retour
                    </button>
                    <button 
                        type="button" 
                        @click="if(validateStep2()) step = 3" 
                        class="btn btn-primary flex-1 h-12"
                        :class="{ 'opacity-50 cursor-not-allowed': !validateStep2() }"
                    >
                        Continuer
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                    </button>
                </div>
            </div>

            <!-- ============================================== -->
            <!-- STEP 3: Plan Selection (MenuPro Unique) -->
            <!-- ============================================== -->
            <div x-show="step === 3" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0"
                 x-data="pricingCalculator()" 
                 x-init="init(); formData.plan = 'menupro';">
                <div class="text-center lg:text-left mb-5 sm:mb-6">
                    <h1 class="text-xl sm:text-2xl font-bold text-neutral-900 tracking-tight">Choisissez votre abonnement</h1>
                    <p class="text-neutral-500 mt-1.5 text-sm sm:text-base">Un seul plan, toutes les fonctionnalités. Économisez jusqu'à 15%.</p>
                </div>

                <!-- Hidden inputs for form submission -->
                <input type="hidden" name="plan" value="menupro" x-model="formData.plan">
                <input type="hidden" name="billing_period" :value="billingCycle">
                <template x-for="addon in selectedAddons" :key="addon">
                    <input type="hidden" :name="'addons[]'" :value="addon">
                </template>
                <input type="hidden" name="billing_period" :value="billingCycle">
                <template x-for="addon in selectedAddons" :key="addon">
                    <input type="hidden" name="addons[]" :value="addon">
                </template>

                <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                    <!-- Left Column: Features & Add-ons -->
                    <div class="lg:col-span-2 space-y-6">
                        <!-- Features List -->
                        <div class="bg-neutral-50 rounded-xl p-5 sm:p-6 border border-neutral-200">
                            <h2 class="text-lg sm:text-xl font-bold text-neutral-900 mb-4 flex items-center gap-2">
                                <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Tout est inclus
                            </h2>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                @foreach([
                                    '100 plats & 30 catégories',
                                    '2 000 commandes/mois',
                                    '5 comptes employés',
                                    'QR codes par table',
                                    'Gestion de stock & alertes',
                                    'Gestion livraison',
                                    'Statistiques & rapports',
                                    'Réservations en ligne',
                                    'Avis clients',
                                    'Paiement Mobile Money',
                                    'Dashboard temps réel',
                                    'Export Excel/PDF',
                                ] as $f)
                                <div class="flex items-center gap-2 text-sm text-neutral-700">
                                    <svg class="w-4 h-4 text-primary-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span>{{ $f }}</span>
                                </div>
                                @endforeach
                            </div>
                        </div>

                        <!-- Add-ons Section -->
                        <div class="bg-neutral-50 rounded-xl p-6 border border-neutral-200">
                            <h2 class="text-xl font-bold text-neutral-900 mb-2 flex items-center gap-2">
                                <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                                Add-ons optionnels
                            </h2>
                            <p class="text-sm text-neutral-600 mb-4">Personnalisez votre plan selon vos besoins</p>
                            <div class="space-y-3">
                                <label class="flex items-center justify-between p-4 rounded-lg border-2 border-neutral-200 hover:border-primary-300 cursor-pointer transition-all group"
                                       :class="{ 'border-primary-500 bg-primary-50': selectedAddons.includes('priority_support') }">
                                    <div class="flex items-center gap-3 flex-1">
                                        <input type="checkbox" 
                                               x-model="selectedAddons" 
                                               value="priority_support"
                                               class="w-5 h-5 rounded border-neutral-300 text-primary-500 focus:ring-primary-500 cursor-pointer">
                                        <div class="flex-1">
                                            <div class="font-semibold text-neutral-900">Support Prioritaire</div>
                                            <div class="text-sm text-neutral-600">Réponse garantie sous 2h</div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-bold text-primary-600">5 000 F</div>
                                        <div class="text-xs text-neutral-500">/mois</div>
                                    </div>
                                </label>

                                <label class="flex items-center justify-between p-4 rounded-lg border-2 border-neutral-200 hover:border-primary-300 cursor-pointer transition-all group"
                                       :class="{ 'border-primary-500 bg-primary-50': selectedAddons.includes('custom_domain') }">
                                    <div class="flex items-center gap-3 flex-1">
                                        <input type="checkbox" 
                                               x-model="selectedAddons" 
                                               value="custom_domain"
                                               class="w-5 h-5 rounded border-neutral-300 text-primary-500 focus:ring-primary-500 cursor-pointer">
                                        <div class="flex-1">
                                            <div class="font-semibold text-neutral-900">Domaine Personnalisé</div>
                                            <div class="text-sm text-neutral-600">Votre propre nom de domaine</div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-bold text-primary-600">3 000 F</div>
                                        <div class="text-xs text-neutral-500">/mois</div>
                                    </div>
                                </label>

                                <label class="flex items-center justify-between p-4 rounded-lg border-2 border-neutral-200 hover:border-primary-300 cursor-pointer transition-all group"
                                       :class="{ 'border-primary-500 bg-primary-50': selectedAddons.includes('extra_employees') }">
                                    <div class="flex items-center gap-3 flex-1">
                                        <input type="checkbox" 
                                               x-model="selectedAddons" 
                                               value="extra_employees"
                                               class="w-5 h-5 rounded border-neutral-300 text-primary-500 focus:ring-primary-500 cursor-pointer">
                                        <div class="flex-1">
                                            <div class="font-semibold text-neutral-900">Employés Supplémentaires</div>
                                            <div class="text-sm text-neutral-600">Par employé supplémentaire</div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-bold text-primary-600">2 000 F</div>
                                        <div class="text-xs text-neutral-500">/mois</div>
                                    </div>
                                </label>

                                <label class="flex items-center justify-between p-4 rounded-lg border-2 border-neutral-200 hover:border-primary-300 cursor-pointer transition-all group"
                                       :class="{ 'border-primary-500 bg-primary-50': selectedAddons.includes('extra_dishes') }">
                                    <div class="flex items-center gap-3 flex-1">
                                        <input type="checkbox" 
                                               x-model="selectedAddons" 
                                               value="extra_dishes"
                                               class="w-5 h-5 rounded border-neutral-300 text-primary-500 focus:ring-primary-500 cursor-pointer">
                                        <div class="flex-1">
                                            <div class="font-semibold text-neutral-900">Plats Supplémentaires</div>
                                            <div class="text-sm text-neutral-600">Par lot de 10 plats</div>
                                        </div>
                                    </div>
                                    <div class="text-right">
                                        <div class="font-bold text-primary-600">500 F</div>
                                        <div class="text-xs text-neutral-500">/mois</div>
                                    </div>
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Right Column: Sticky Price Card -->
                    <div class="lg:col-span-1">
                        <div class="sticky top-8">
                            <div class="bg-gradient-to-br from-primary-50 to-primary-100 rounded-xl p-6 border-2 border-primary-200 shadow-lg">
                                <!-- Badge MEILLEUR pour Annuel -->
                                <div x-show="billingCycle === 'annual'" 
                                     x-transition
                                     class="absolute -top-3 left-1/2 -translate-x-1/2 z-10">
                                    <div class="bg-primary-500 text-white px-4 py-1 rounded-full text-xs font-bold shadow-md">
                                        MEILLEUR
                                    </div>
                                </div>

                                <div class="text-center mb-6 pt-2">
                                    <h3 class="text-2xl font-bold text-neutral-900 mb-1">MenuPro</h3>
                                    <p class="text-neutral-600 text-sm">Plan unique • Toutes les fonctionnalités</p>
                                </div>

                                <!-- Billing Cycle Toggle -->
                                <div class="mb-6">
                                    <div class="grid grid-cols-2 gap-2 bg-white p-1 rounded-lg">
                                        <button @click="billingCycle = 'monthly'"
                                                :class="{ 'bg-primary-500 text-white': billingCycle === 'monthly', 'text-neutral-700 hover:bg-neutral-100': billingCycle !== 'monthly' }"
                                                class="px-3 py-2 rounded-md text-xs font-semibold transition-all">
                                            Mensuel
                                        </button>
                                        <button @click="billingCycle = 'quarterly'"
                                                :class="{ 'bg-primary-500 text-white': billingCycle === 'quarterly', 'text-neutral-700 hover:bg-neutral-100': billingCycle !== 'quarterly' }"
                                                class="px-3 py-2 rounded-md text-xs font-semibold transition-all">
                                            Trim.
                                        </button>
                                        <button @click="billingCycle = 'semiannual'"
                                                :class="{ 'bg-primary-500 text-white': billingCycle === 'semiannual', 'text-neutral-700 hover:bg-neutral-100': billingCycle !== 'semiannual' }"
                                                class="px-3 py-2 rounded-md text-xs font-semibold transition-all">
                                            Sem.
                                        </button>
                                        <button @click="billingCycle = 'annual'"
                                                :class="{ 'bg-primary-500 text-white': billingCycle === 'annual', 'text-neutral-700 hover:bg-neutral-100': billingCycle !== 'annual' }"
                                                class="px-3 py-2 rounded-md text-xs font-semibold transition-all">
                                            Annuel
                                        </button>
                                    </div>
                                </div>

                                <!-- Price Display -->
                                <div class="text-center mb-4">
                                    <div class="mb-2">
                                        <span class="text-4xl font-bold text-neutral-900" x-text="formatPrice(basePrice)"></span>
                                        <span class="text-neutral-600 text-sm ml-1">FCFA</span>
                                    </div>
                                    <div class="text-xs text-neutral-600" x-show="billingCycle !== 'monthly'">
                                        <span x-text="'Économisez ' + formatPrice(discountAmount) + ' FCFA'"></span>
                                    </div>
                                </div>

                                <!-- Add-ons Total -->
                                <div x-show="addonsTotal > 0" 
                                     x-transition
                                     class="mb-4 pt-4 border-t border-primary-200">
                                    <div class="space-y-1 text-sm">
                                        <div class="flex justify-between text-neutral-700">
                                            <span>Add-ons</span>
                                            <span x-text="formatPrice(addonsTotal) + ' FCFA'"></span>
                                        </div>
                                    </div>
                                </div>

                                <!-- Total Price -->
                                <div class="mb-6 pt-4 border-t-2 border-primary-300">
                                    <div class="flex justify-between items-baseline mb-1">
                                        <span class="text-lg font-semibold text-neutral-900">Total</span>
                                        <span class="text-2xl font-bold text-primary-600" x-text="formatPrice(totalPrice)"></span>
                                    </div>
                                    <div class="text-xs text-neutral-600 text-right">
                                        <span x-text="'Soit ' + formatPrice(totalPrice / getMonths()) + ' FCFA/mois'"></span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Terms -->
                <div class="mt-6 p-4 bg-neutral-50 rounded-xl" :class="submitError && !document.querySelector('input[name=terms]')?.checked ? 'border-2 border-red-300' : ''">
                    <label class="flex items-start gap-3 cursor-pointer">
                        <input 
                            type="checkbox" 
                            name="terms" 
                            required 
                            class="w-5 h-5 mt-0.5 rounded border-neutral-300 text-primary-500 focus:ring-primary-500 focus:ring-offset-0"
                        >
                        <span class="text-sm text-neutral-600 leading-relaxed">
                            J'accepte les 
                            <a href="{{ route('terms') }}" target="_blank" class="text-primary-600 hover:underline font-medium">conditions d'utilisation</a> 
                            et la 
                            <a href="{{ route('privacy') }}" target="_blank" class="text-primary-600 hover:underline font-medium">politique de confidentialité</a>.
                        </span>
                    </label>
                    @error('terms')
                        <p class="mt-2 text-sm text-red-600">{{ $message }}</p>
                    @enderror
                </div>
                
                <!-- Message d'erreur de soumission -->
                <div x-show="submitError" x-cloak class="mt-4 p-4 bg-red-50 border border-red-200 rounded-xl">
                    <div class="flex items-start gap-3">
                        <svg class="w-5 h-5 text-red-600 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <p class="text-sm text-red-700" x-text="submitError"></p>
                    </div>
                </div>

                <div class="flex gap-3 mt-6">
                    <button type="button" @click="step = 2" class="btn btn-ghost flex-1 h-12">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"/>
                        </svg>
                        Retour
                    </button>
                    <button 
                        type="submit" 
                        class="btn btn-primary flex-1 h-12 relative overflow-hidden"
                        :disabled="loading || submitted"
                    >
                        <span :class="{ 'opacity-0': loading }">
                            Créer mon restaurant
                        </span>
                        <span x-show="loading" x-cloak class="absolute inset-0 flex items-center justify-center">
                            <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                    </button>
                </div>

                <p class="text-center text-sm text-neutral-500 mt-4">
                    <svg class="w-4 h-4 text-secondary-500 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg> <strong>Satisfait ou remboursé 7 jours</strong> • Activation sous 24h
                </p>
            </div>
        </form>

        <!-- Login Link -->
        <div class="relative my-8">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-neutral-200"></div>
            </div>
            <div class="relative flex justify-center text-xs sm:text-sm">
                <span class="px-4 bg-white text-neutral-400 font-medium">Déjà inscrit ?</span>
            </div>
        </div>

        <a href="{{ route('login') }}" class="flex items-center justify-center gap-2 w-full h-13 border-2 border-neutral-200 rounded-xl text-neutral-700 font-semibold hover:border-primary-300 hover:text-primary-600 hover:bg-primary-50/50 transition-all duration-200">
            <svg class="w-5 h-5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
            </svg>
            Se connecter à mon compte
        </a>
    </div>

    @push('scripts')
    <script>
        // Fonction Alpine.js pour le formulaire d'inscription
        function registerForm() {
            return {
                step: 1,
                totalSteps: 3,
                loading: false,
                submitted: false,
                submitError: null,
                formData: {
                    name: '',
                    email: '',
                    phone: '',
                    password: '',
                    restaurant_name: '',
                    restaurant_type: '',
                    company_name: '',
                    rccm: '',
                    plan: 'menupro'
                },
                showPassword: false,
                logoPreview: null,
                bannerPreview: null,
                validateStep1() {
                    return this.formData.name && this.formData.email && this.formData.phone && this.formData.password.length >= 8;
                },
                validateStep2() {
                    return this.formData.restaurant_name && this.formData.restaurant_type;
                },
                submitForm() {
                    // Réinitialiser l'erreur
                    this.submitError = null;
                    
                    // Vérifier que tous les champs requis sont remplis
                    if (!this.validateStep1() || !this.validateStep2()) {
                        this.submitError = 'Veuillez remplir tous les champs obligatoires.';
                        // Retourner à l'étape avec des erreurs
                        if (!this.validateStep1()) {
                            this.step = 1;
                        } else if (!this.validateStep2()) {
                            this.step = 2;
                        }
                        this.loading = false;
                        this.submitted = false;
                        return;
                    }
                    
                    // Vérifier que les conditions sont acceptées
                    const termsCheckbox = document.querySelector('input[name=terms]');
                    if (!termsCheckbox || !termsCheckbox.checked) {
                        this.submitError = 'Vous devez accepter les conditions d\'utilisation.';
                        this.step = 3;
                        this.loading = false;
                        this.submitted = false;
                        return;
                    }
                    
                    // Désactiver le bouton et afficher le loader
                    this.loading = true;
                    this.submitted = true;
                    this.submitError = null;
                    
                    // Soumettre le formulaire
                    const form = document.getElementById('register-form');
                    if (form) {
                        form.submit();
                    }
                }
            };
        }
        
        // Rafraîchir le token CSRF périodiquement pour éviter l'expiration (erreur 419)
        (function() {
            const form = document.getElementById('register-form');
            if (form) {
                const tokenInput = form.querySelector('input[name="_token"]');
                if (tokenInput) {
                    // Rafraîchir le token toutes les 3 minutes (avant expiration de session)
                    setInterval(function() {
                        fetch('{{ route("csrf.token") }}', {
                            method: 'GET',
                            credentials: 'same-origin',
                            headers: {
                                'Accept': 'application/json',
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.token) {
                                tokenInput.value = data.token;
                                // Mettre à jour aussi le meta tag si présent
                                const metaToken = document.querySelector('meta[name="csrf-token"]');
                                if (metaToken) {
                                    metaToken.setAttribute('content', data.token);
                                }
                            }
                        })
                        .catch(() => {
                            // Ignorer silencieusement les erreurs
                        });
                    }, 3 * 60 * 1000); // 3 minutes
                }
            }
        })();
        
        // Afficher les erreurs de validation si présentes
        document.addEventListener('DOMContentLoaded', function() {
            const form = document.getElementById('register-form');
            if (form) {
                // Si des erreurs sont présentes, scroller vers le haut
                const errors = form.querySelector('.bg-red-50');
                if (errors) {
                    errors.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }
        });
        
        function pricingCalculator() {
            return {
                billingCycle: 'monthly',
                selectedAddons: [],
                cycles: [
                    { id: 'monthly', label: 'Mensuel', months: 1, price: 25000, discount: 0 },
                    { id: 'quarterly', label: 'Trimestriel', months: 3, price: 69750, discount: 7, original: 75000 },
                    { id: 'semiannual', label: 'Semestriel', months: 6, price: 130500, discount: 13, original: 150000 },
                    { id: 'annual', label: 'Annuel', months: 12, price: 255000, discount: 15, original: 300000 },
                ],
                addonPrices: {
                    priority_support: 5000,
                    custom_domain: 3000,
                    extra_employees: 2000,
                    extra_dishes: 500,
                },
                
                init() {
                    // Initialize with monthly
                    this.billingCycle = 'monthly';
                    this.selectedAddons = [];
                },
                
                getCurrentCycle() {
                    return this.cycles.find(c => c.id === this.billingCycle);
                },
                
                getMonths() {
                    return this.getCurrentCycle().months;
                },
                
                get basePrice() {
                    return this.getCurrentCycle().price;
                },
                
                get discountAmount() {
                    const cycle = this.getCurrentCycle();
                    if (cycle.original) {
                        return cycle.original - cycle.price;
                    }
                    return 0;
                },
                
                get addonsTotal() {
                    const months = this.getMonths();
                    return this.selectedAddons.reduce((total, addonId) => {
                        return total + (this.addonPrices[addonId] * months);
                    }, 0);
                },
                
                get totalPrice() {
                    return this.basePrice + this.addonsTotal;
                },
                
                formatPrice(price) {
                    return new Intl.NumberFormat('fr-FR').format(Math.round(price));
                }
            }
        }
    </script>
    @endpush
</x-layouts.auth>
