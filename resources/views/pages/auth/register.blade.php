<x-layouts.auth title="Inscription">
    <div 
        x-data="{ 
            step: 1, 
            totalSteps: 3,
            loading: false,
            formData: {
                name: '',
                email: '',
                phone: '',
                password: '',
                restaurant_name: '',
                restaurant_type: '',
                company_name: '',
                rccm: '',
                plan: 'pro'
            },
            showPassword: false,
            logoPreview: null,
            bannerPreview: null,
            validateStep1() {
                return this.formData.name && this.formData.email && this.formData.phone && this.formData.password.length >= 8;
            },
            validateStep2() {
                return this.formData.restaurant_name && this.formData.restaurant_type;
            }
        }" 
        class="animate-fade-in"
    >
        <!-- Progress Steps -->
        <div class="mb-6 sm:mb-8">
            <div class="flex items-center justify-between mb-3 sm:mb-4">
                <!-- Step 1 -->
                <div class="flex items-center">
                    <div 
                        class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm transition-all duration-300"
                        :class="step >= 1 ? 'bg-primary-500 text-white' : 'bg-neutral-100 text-neutral-400'"
                    >
                        <template x-if="step > 1">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </template>
                        <template x-if="step <= 1">
                            <span>1</span>
                        </template>
                    </div>
                    <span class="ml-2 text-sm font-medium hidden sm:block" :class="step >= 1 ? 'text-neutral-900' : 'text-neutral-400'">Compte</span>
                </div>
                
                <!-- Connector -->
                <div class="flex-1 h-0.5 mx-4 rounded transition-colors duration-300" :class="step > 1 ? 'bg-primary-500' : 'bg-neutral-200'"></div>
                
                <!-- Step 2 -->
                <div class="flex items-center">
                    <div 
                        class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm transition-all duration-300"
                        :class="step >= 2 ? 'bg-primary-500 text-white' : 'bg-neutral-100 text-neutral-400'"
                    >
                        <template x-if="step > 2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </template>
                        <template x-if="step <= 2">
                            <span>2</span>
                        </template>
                    </div>
                    <span class="ml-2 text-sm font-medium hidden sm:block" :class="step >= 2 ? 'text-neutral-900' : 'text-neutral-400'">Restaurant</span>
                </div>
                
                <!-- Connector -->
                <div class="flex-1 h-0.5 mx-4 rounded transition-colors duration-300" :class="step > 2 ? 'bg-primary-500' : 'bg-neutral-200'"></div>
                
                <!-- Step 3 -->
                <div class="flex items-center">
                    <div 
                        class="w-10 h-10 rounded-full flex items-center justify-center font-bold text-sm transition-all duration-300"
                        :class="step >= 3 ? 'bg-primary-500 text-white' : 'bg-neutral-100 text-neutral-400'"
                    >
                        <span>3</span>
                    </div>
                    <span class="ml-2 text-sm font-medium hidden sm:block" :class="step >= 3 ? 'text-neutral-900' : 'text-neutral-400'">Plan</span>
                </div>
            </div>
        </div>

        <form method="POST" action="{{ route('register.post') }}" enctype="multipart/form-data" @submit="loading = true">
            @csrf

            <!-- ============================================== -->
            <!-- STEP 1: Account Information -->
            <!-- ============================================== -->
            <div x-show="step === 1" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0">
                <div class="text-center lg:text-left mb-4 sm:mb-6">
                    <h1 class="text-xl sm:text-2xl font-bold text-neutral-900">Créez votre compte</h1>
                    <p class="text-neutral-500 mt-1 text-sm sm:text-base">Commencez par vos informations personnelles.</p>
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
                                class="w-full h-12 pl-12 pr-4 bg-white border border-neutral-200 rounded-xl text-neutral-900 placeholder:text-neutral-400 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
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
                                class="w-full h-12 pl-12 pr-4 bg-white border border-neutral-200 rounded-xl text-neutral-900 placeholder:text-neutral-400 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
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
                                class="w-full h-12 pl-12 pr-4 bg-white border border-neutral-200 rounded-xl text-neutral-900 placeholder:text-neutral-400 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
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
                                class="w-full h-12 pl-12 pr-12 bg-white border border-neutral-200 rounded-xl text-neutral-900 placeholder:text-neutral-400 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
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
                    class="btn btn-primary w-full h-12 mt-6"
                    :class="{ 'opacity-50 cursor-not-allowed': !validateStep1() }"
                >
                    Continuer
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                    </svg>
                </button>
            </div>

            <!-- ============================================== -->
            <!-- STEP 2: Restaurant Information -->
            <!-- ============================================== -->
            <div x-show="step === 2" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0">
                <div class="text-center lg:text-left mb-6">
                    <h1 class="text-2xl font-bold text-neutral-900">Votre restaurant</h1>
                    <p class="text-neutral-500 mt-1">Personnalisez l'identité de votre établissement.</p>
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
                                class="w-full h-12 pl-12 pr-4 bg-white border border-neutral-200 rounded-xl text-neutral-900 placeholder:text-neutral-400 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent"
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
                                class="w-full h-12 pl-4 pr-10 bg-white border border-neutral-200 rounded-xl text-neutral-900 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent appearance-none"
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
                                class="w-full h-12 pl-12 pr-4 bg-white border border-neutral-200 rounded-xl text-neutral-900 placeholder:text-neutral-400 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('company_name') border-red-500 @enderror"
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
                                    class="w-full h-12 pl-12 pr-4 bg-white border border-neutral-200 rounded-xl text-neutral-900 placeholder:text-neutral-400 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('rccm') border-red-500 @enderror"
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
                                    class="w-full h-12 px-4 bg-white border border-neutral-200 rounded-xl text-neutral-900 file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-primary-500 file:text-white hover:file:bg-primary-600 focus:outline-none focus:ring-2 focus:ring-primary-500 @error('rccm_document') border-red-500 @enderror"
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
            <!-- STEP 3: Plan Selection -->
            <!-- ============================================== -->
            <div x-show="step === 3" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-8" x-transition:enter-end="opacity-100 translate-x-0">
                <div class="text-center lg:text-left mb-6">
                    <h1 class="text-2xl font-bold text-neutral-900">Choisissez votre plan</h1>
                    <p class="text-neutral-500 mt-1">Satisfait ou remboursé pendant 7 jours.</p>
                </div>

                <div class="space-y-3">
                    <!-- Starter Plan -->
                    <label class="block cursor-pointer group">
                        <input type="radio" name="plan" value="starter" x-model="formData.plan" class="hidden">
                        <div 
                            class="p-4 rounded-xl border-2 transition-all duration-200"
                            :class="formData.plan === 'starter' ? 'border-primary-500 bg-primary-50 shadow-sm' : 'border-neutral-200 group-hover:border-neutral-300 group-hover:bg-neutral-50'"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div 
                                        class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-colors"
                                        :class="formData.plan === 'starter' ? 'border-primary-500 bg-primary-500' : 'border-neutral-300'"
                                    >
                                        <svg x-show="formData.plan === 'starter'" class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <span class="font-semibold text-neutral-900">Starter</span>
                                        <span class="text-neutral-500 text-sm ml-2 hidden sm:inline">• 20 plats, 5 catégories</span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="font-bold text-neutral-900">9 900</span>
                                    <span class="text-neutral-500 text-sm"> F/mois</span>
                                </div>
                            </div>
                        </div>
                    </label>

                    <!-- Pro Plan -->
                    <label class="block cursor-pointer group">
                        <input type="radio" name="plan" value="pro" x-model="formData.plan" class="hidden">
                        <div 
                            class="p-4 rounded-xl border-2 transition-all duration-200 relative"
                            :class="formData.plan === 'pro' ? 'border-primary-500 bg-primary-50 shadow-sm' : 'border-neutral-200 group-hover:border-neutral-300 group-hover:bg-neutral-50'"
                        >
                            <span class="absolute -top-2.5 left-4 bg-primary-500 text-white text-xs px-2 py-0.5 rounded-full font-medium">
                                ⭐ Recommandé
                            </span>
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div 
                                        class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-colors"
                                        :class="formData.plan === 'pro' ? 'border-primary-500 bg-primary-500' : 'border-neutral-300'"
                                    >
                                        <svg x-show="formData.plan === 'pro'" class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <span class="font-semibold text-neutral-900">Pro</span>
                                        <span class="text-neutral-500 text-sm ml-2 hidden sm:inline">• 50 plats, 3 employés</span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="font-bold text-neutral-900">19 900</span>
                                    <span class="text-neutral-500 text-sm"> F/mois</span>
                                </div>
                            </div>
                        </div>
                    </label>

                    <!-- Premium Plan -->
                    <label class="block cursor-pointer group">
                        <input type="radio" name="plan" value="premium" x-model="formData.plan" class="hidden">
                        <div 
                            class="p-4 rounded-xl border-2 transition-all duration-200"
                            :class="formData.plan === 'premium' ? 'border-primary-500 bg-primary-50 shadow-sm' : 'border-neutral-200 group-hover:border-neutral-300 group-hover:bg-neutral-50'"
                        >
                            <div class="flex items-center justify-between">
                                <div class="flex items-center gap-3">
                                    <div 
                                        class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-colors"
                                        :class="formData.plan === 'premium' ? 'border-primary-500 bg-primary-500' : 'border-neutral-300'"
                                    >
                                        <svg x-show="formData.plan === 'premium'" class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                        </svg>
                                    </div>
                                    <div>
                                        <span class="font-semibold text-neutral-900">Premium</span>
                                        <span class="text-neutral-500 text-sm ml-2 hidden sm:inline">• Illimité, support VIP</span>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <span class="font-bold text-neutral-900">39 900</span>
                                    <span class="text-neutral-500 text-sm"> F/mois</span>
                                </div>
                            </div>
                        </div>
                    </label>
                </div>

                <!-- Terms -->
                <div class="mt-6 p-4 bg-neutral-50 rounded-xl">
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
                        :disabled="loading"
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
                    ✅ <strong>Satisfait ou remboursé 7 jours</strong> • Activation sous 24h
                </p>
            </div>
        </form>

        <!-- Login Link -->
        <div class="relative my-6">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-neutral-200"></div>
            </div>
            <div class="relative flex justify-center text-sm">
                <span class="px-4 bg-white text-neutral-500">Déjà inscrit ?</span>
            </div>
        </div>

        <a href="{{ route('login') }}" class="btn btn-ghost w-full h-12">
            Se connecter à mon compte
        </a>
    </div>
</x-layouts.auth>
