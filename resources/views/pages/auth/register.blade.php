<x-layouts.auth title="Inscription">
    <div
        x-data="registerForm()"
        class="animate-fade-in"
    >
        {{-- ===== Stepper ===== --}}
        <div class="mb-8">
            {{-- Mobile --}}
            <div class="sm:hidden">
                <div class="flex items-center justify-between mb-3">
                    <div>
                        <span class="text-[10px] font-bold text-primary-600 uppercase tracking-wider">Etape <span x-text="step"></span>/3</span>
                        <div class="text-sm font-bold text-neutral-900 mt-0.5" x-text="step === 1 ? 'Votre compte' : (step === 2 ? 'Votre restaurant' : 'Votre abonnement')"></div>
                    </div>
                    <div class="flex gap-1.5">
                        <template x-for="i in 3" :key="i">
                            <div class="w-8 h-1.5 rounded-full transition-all duration-300"
                                 :class="i <= step ? 'bg-primary-500' : 'bg-neutral-200'"></div>
                        </template>
                    </div>
                </div>
            </div>

            {{-- Desktop stepper --}}
            <div class="hidden sm:block">
                <div class="flex items-center gap-3">
                    @foreach([
                        ['n' => 1, 'title' => 'Compte'],
                        ['n' => 2, 'title' => 'Restaurant'],
                        ['n' => 3, 'title' => 'Abonnement']
                    ] as $s)
                        @if($s['n'] > 1)
                            <div class="flex-1 h-0.5 rounded-full bg-neutral-200 overflow-hidden">
                                <div class="h-full bg-primary-500 transition-all duration-500 rounded-full"
                                     :style="step > {{ $s['n'] - 1 }} ? 'width: 100%' : (step === {{ $s['n'] - 1 }} ? 'width: 50%' : 'width: 0%')"></div>
                            </div>
                        @endif
                        <button type="button"
                                @click="if (step > {{ $s['n'] }}) goToStep({{ $s['n'] }})"
                                :disabled="step < {{ $s['n'] }}"
                                class="flex items-center gap-2 focus:outline-none"
                                :class="step < {{ $s['n'] }} ? 'opacity-50 cursor-not-allowed' : 'cursor-pointer'">
                            <div class="w-8 h-8 rounded-full flex items-center justify-center text-xs font-bold transition-all duration-300 border-2"
                                 :class="step > {{ $s['n'] }}
                                    ? 'bg-primary-500 border-primary-500 text-white'
                                    : (step === {{ $s['n'] }}
                                        ? 'bg-primary-500 border-primary-500 text-white shadow-md shadow-primary-500/30'
                                        : 'bg-white border-neutral-300 text-neutral-400')">
                                <template x-if="step > {{ $s['n'] }}">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </template>
                                <template x-if="step <= {{ $s['n'] }}">
                                    <span>{{ $s['n'] }}</span>
                                </template>
                            </div>
                            <span class="text-sm font-semibold transition-colors"
                                  :class="step >= {{ $s['n'] }} ? 'text-neutral-900' : 'text-neutral-400'">{{ $s['title'] }}</span>
                        </button>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Agent referral badge --}}
        @if(session('register_ref_agent'))
            @php $refAgent = \App\Models\CommandoAgent::where('uuid', session('register_ref_agent'))->first(); @endphp
            @if($refAgent)
            <div class="mb-5 p-3 bg-gradient-to-r from-primary-50 to-orange-50 border border-primary-200 rounded-xl flex items-center gap-3">
                <div class="w-8 h-8 rounded-full bg-primary-100 flex items-center justify-center shrink-0">
                    <svg class="w-4 h-4 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <div>
                    <p class="text-sm font-semibold text-neutral-800">Parraine par {{ $refAgent->full_name }}</p>
                    <p class="text-xs text-neutral-500">Agent MenuPro — {{ $refAgent->city }}</p>
                </div>
            </div>
            @endif
        @endif

        {{-- Global errors --}}
        @if ($errors->any())
            <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-red-500 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div>
                        <h3 class="text-sm font-semibold text-red-800 mb-1">Corrigez les erreurs suivantes</h3>
                        <ul class="list-disc list-inside space-y-0.5 text-sm text-red-700">
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

            {{-- ============================================== --}}
            {{-- STEP 1: Account --}}
            {{-- ============================================== --}}
            <div x-show="step === 1" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
                <div class="mb-6">
                    <h1 class="text-xl sm:text-2xl font-bold text-neutral-900">Creez votre compte</h1>
                    <p class="text-neutral-500 mt-1 text-sm">Commencez par vos informations personnelles.</p>
                </div>

                <div class="space-y-4">
                    {{-- Name --}}
                    <div>
                        <label for="name" class="block text-sm font-semibold text-neutral-700 mb-1.5">
                            Nom complet <span class="text-red-500">*</span>
                        </label>
                        <div class="relative group">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-neutral-400 group-focus-within:text-primary-500 transition-colors pointer-events-none">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                            </span>
                            <input type="text" id="name" name="name" x-model="formData.name"
                                   placeholder="Jean Kouassi" required autocomplete="name"
                                   class="w-full h-12 pl-11 pr-4 bg-neutral-50 border-2 border-neutral-200 rounded-xl text-neutral-900 placeholder:text-neutral-400 transition-all duration-200 focus:outline-none focus:bg-white focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500">
                        </div>
                    </div>

                    {{-- Email --}}
                    <div>
                        <label for="email" class="block text-sm font-semibold text-neutral-700 mb-1.5">
                            Adresse email <span class="text-red-500">*</span>
                        </label>
                        <div class="relative group">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-neutral-400 group-focus-within:text-primary-500 transition-colors pointer-events-none">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                                </svg>
                            </span>
                            <input type="email" id="email" name="email" x-model="formData.email"
                                   placeholder="vous@exemple.com" required autocomplete="email"
                                   class="w-full h-12 pl-11 pr-4 bg-neutral-50 border-2 border-neutral-200 rounded-xl text-neutral-900 placeholder:text-neutral-400 transition-all duration-200 focus:outline-none focus:bg-white focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500">
                        </div>
                    </div>

                    {{-- Phone --}}
                    <div>
                        <label for="phone" class="block text-sm font-semibold text-neutral-700 mb-1.5">
                            Telephone <span class="text-red-500">*</span>
                        </label>
                        <div class="relative group">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-neutral-400 group-focus-within:text-primary-500 transition-colors pointer-events-none">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                </svg>
                            </span>
                            <input type="tel" id="phone" name="phone" x-model="formData.phone"
                                   placeholder="+225 07 00 00 00 00" required autocomplete="tel"
                                   class="w-full h-12 pl-11 pr-4 bg-neutral-50 border-2 border-neutral-200 rounded-xl text-neutral-900 placeholder:text-neutral-400 transition-all duration-200 focus:outline-none focus:bg-white focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500">
                        </div>
                    </div>

                    {{-- Password --}}
                    <div>
                        <label for="reg-password" class="block text-sm font-semibold text-neutral-700 mb-1.5">
                            Mot de passe <span class="text-red-500">*</span>
                        </label>
                        <div class="relative group">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-neutral-400 group-focus-within:text-primary-500 transition-colors pointer-events-none">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                </svg>
                            </span>
                            <input :type="showPassword ? 'text' : 'password'" id="reg-password" name="password"
                                   x-model="formData.password" placeholder="Minimum 8 caracteres"
                                   required minlength="8" autocomplete="new-password"
                                   class="w-full h-12 pl-11 pr-12 bg-neutral-50 border-2 border-neutral-200 rounded-xl text-neutral-900 placeholder:text-neutral-400 transition-all duration-200 focus:outline-none focus:bg-white focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500">
                            <button type="button" @click="showPassword = !showPassword"
                                    class="absolute right-3.5 top-1/2 -translate-y-1/2 text-neutral-400 hover:text-neutral-600 transition-colors p-1 rounded-lg hover:bg-neutral-100">
                                <svg x-show="!showPassword" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                </svg>
                                <svg x-show="showPassword" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                </svg>
                            </button>
                        </div>
                        {{-- Strength --}}
                        <div class="mt-2 flex gap-1">
                            <div class="h-1 flex-1 rounded-full transition-all" :class="formData.password.length >= 1 ? 'bg-red-400' : 'bg-neutral-200'"></div>
                            <div class="h-1 flex-1 rounded-full transition-all" :class="formData.password.length >= 4 ? 'bg-orange-400' : 'bg-neutral-200'"></div>
                            <div class="h-1 flex-1 rounded-full transition-all" :class="formData.password.length >= 8 ? 'bg-emerald-400' : 'bg-neutral-200'"></div>
                            <div class="h-1 flex-1 rounded-full transition-all" :class="formData.password.length >= 12 ? 'bg-emerald-500' : 'bg-neutral-200'"></div>
                        </div>
                        <p class="text-xs text-neutral-400 mt-1" x-text="formData.password.length === 0 ? '8 caracteres minimum' : (formData.password.length < 8 ? 'Encore ' + (8 - formData.password.length) + ' caractere(s)' : 'Mot de passe valide')"></p>
                    </div>
                </div>

                <button type="button" @click="if(validateStep1()) step = 2"
                        class="btn btn-primary w-full h-12 mt-6 font-semibold shadow-lg shadow-primary-500/20 hover:shadow-primary-500/30 hover:scale-[1.005] active:scale-[0.995] transition-all"
                        :class="{ 'opacity-50 cursor-not-allowed': !validateStep1() }">
                    <span class="flex items-center justify-center gap-2">
                        Continuer
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                        </svg>
                    </span>
                </button>

                <p class="text-center text-sm text-neutral-400 mt-5">
                    Deja inscrit ?
                    <a href="{{ route('login') }}" class="text-primary-600 font-semibold hover:underline underline-offset-2">Se connecter</a>
                </p>
            </div>

            {{-- ============================================== --}}
            {{-- STEP 2: Restaurant --}}
            {{-- ============================================== --}}
            <div x-show="step === 2" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
                <div class="mb-6">
                    <h1 class="text-xl sm:text-2xl font-bold text-neutral-900">Votre restaurant</h1>
                    <p class="text-neutral-500 mt-1 text-sm">Personnalisez l'identite de votre etablissement.</p>
                </div>

                <div class="space-y-4">
                    {{-- Restaurant Name --}}
                    <div>
                        <label for="restaurant_name" class="block text-sm font-semibold text-neutral-700 mb-1.5">
                            Nom de l'etablissement <span class="text-red-500">*</span>
                        </label>
                        <div class="relative group">
                            <span class="absolute left-3.5 top-1/2 -translate-y-1/2 text-neutral-400 group-focus-within:text-primary-500 transition-colors pointer-events-none">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                                </svg>
                            </span>
                            <input type="text" id="restaurant_name" name="restaurant_name" x-model="formData.restaurant_name"
                                   placeholder="Le Delice" required
                                   class="w-full h-12 pl-11 pr-4 bg-neutral-50 border-2 border-neutral-200 rounded-xl text-neutral-900 placeholder:text-neutral-400 transition-all duration-200 focus:outline-none focus:bg-white focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500">
                        </div>
                    </div>

                    {{-- Restaurant Type --}}
                    <div>
                        <label for="restaurant_type" class="block text-sm font-semibold text-neutral-700 mb-1.5">
                            Type d'etablissement <span class="text-red-500">*</span>
                        </label>
                        <div class="relative group">
                            <select id="restaurant_type" name="restaurant_type" x-model="formData.restaurant_type" required
                                    class="w-full h-12 pl-4 pr-10 bg-neutral-50 border-2 border-neutral-200 rounded-xl text-neutral-900 transition-all duration-200 focus:outline-none focus:bg-white focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500 appearance-none">
                                <option value="">Selectionnez un type</option>
                                <option value="restaurant">Restaurant</option>
                                <option value="bar">Bar</option>
                                <option value="brasserie">Brasserie</option>
                                <option value="maquis">Maquis</option>
                                <option value="traiteur">Traiteur</option>
                                <option value="cafe">Cafe</option>
                                <option value="food_truck">Food Truck</option>
                                <option value="brunch">Brunch / Petit-dejeuner</option>
                                <option value="evenementiel">Evenementiel</option>
                            </select>
                            <svg class="absolute right-3.5 top-1/2 -translate-y-1/2 w-5 h-5 text-neutral-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                        </div>
                    </div>

                    {{-- Company Name --}}
                    <div>
                        <label for="company_name" class="block text-sm font-semibold text-neutral-700 mb-1.5">
                            Nom de l'entreprise <span class="text-neutral-400 text-xs font-normal">(optionnel)</span>
                        </label>
                        <input type="text" id="company_name" name="company_name" x-model="formData.company_name"
                               placeholder="SARL Le Delice"
                               class="w-full h-12 px-4 bg-neutral-50 border-2 border-neutral-200 rounded-xl text-neutral-900 placeholder:text-neutral-400 transition-all duration-200 focus:outline-none focus:bg-white focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500">
                    </div>

                    {{-- RCCM (collapsible) --}}
                    <div x-data="{ showRccm: false }">
                        <button type="button" @click="showRccm = !showRccm"
                                class="flex items-center gap-2 text-sm font-medium text-primary-600 hover:text-primary-700 transition-colors">
                            <svg class="w-4 h-4 transition-transform" :class="showRccm ? 'rotate-90' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                            Ajouter RCCM (badge verifie)
                        </button>
                        <div x-show="showRccm" x-cloak x-collapse class="mt-3 p-4 bg-neutral-50 rounded-xl border border-neutral-200 space-y-3">
                            <div>
                                <label for="rccm" class="block text-sm font-medium text-neutral-700 mb-1">Numero RCCM</label>
                                <input type="text" id="rccm" name="rccm" x-model="formData.rccm"
                                       placeholder="CI-ABJ-XX-2024-XXXXX"
                                       class="w-full h-11 px-4 bg-white border-2 border-neutral-200 rounded-xl text-neutral-900 placeholder:text-neutral-400 text-sm transition-all focus:outline-none focus:ring-4 focus:ring-primary-500/10 focus:border-primary-500">
                            </div>
                            <div>
                                <label for="rccm_document" class="block text-sm font-medium text-neutral-700 mb-1">Extrait RCCM</label>
                                <input type="file" id="rccm_document" name="rccm_document" accept=".pdf,.jpg,.jpeg,.png"
                                       class="w-full text-sm text-neutral-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-xs file:font-semibold file:bg-primary-500 file:text-white hover:file:bg-primary-600">
                                <p class="text-xs text-neutral-400 mt-1">PDF, JPEG ou PNG. Max 5 Mo</p>
                            </div>
                        </div>
                    </div>

                    {{-- Logo + Banner --}}
                    <div class="grid grid-cols-2 gap-3">
                        <div>
                            <label class="block text-sm font-semibold text-neutral-700 mb-1.5">Logo</label>
                            <label for="logo-upload" class="block w-full aspect-square rounded-xl bg-neutral-50 border-2 border-dashed border-neutral-300 flex items-center justify-center overflow-hidden cursor-pointer hover:border-primary-400 hover:bg-primary-50/30 transition-all">
                                <template x-if="logoPreview">
                                    <img :src="logoPreview" alt="Logo" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!logoPreview">
                                    <div class="text-center p-3">
                                        <svg class="w-8 h-8 text-neutral-300 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <p class="text-[10px] text-neutral-400 mt-1">Logo</p>
                                    </div>
                                </template>
                            </label>
                            <input type="file" name="logo" id="logo-upload" accept="image/png,image/jpeg,image/webp" class="hidden"
                                   @change="logoPreview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null">
                        </div>
                        <div>
                            <label class="block text-sm font-semibold text-neutral-700 mb-1.5">Banniere</label>
                            <label class="block w-full aspect-square rounded-xl bg-neutral-50 border-2 border-dashed border-neutral-300 flex items-center justify-center overflow-hidden cursor-pointer hover:border-primary-400 hover:bg-primary-50/30 transition-all relative">
                                <template x-if="bannerPreview">
                                    <img :src="bannerPreview" alt="Banniere" class="w-full h-full object-cover">
                                </template>
                                <template x-if="!bannerPreview">
                                    <div class="text-center p-3">
                                        <svg class="w-8 h-8 text-neutral-300 mx-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                        </svg>
                                        <p class="text-[10px] text-neutral-400 mt-1">Couverture</p>
                                    </div>
                                </template>
                                <input type="file" name="banner" accept="image/png,image/jpeg,image/webp" class="absolute inset-0 opacity-0 cursor-pointer"
                                       @change="bannerPreview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null">
                            </label>
                        </div>
                    </div>
                    <p class="text-xs text-neutral-400 -mt-1">Optionnel — PNG, JPG ou WEBP</p>
                </div>

                <div class="flex gap-3 mt-6">
                    <button type="button" @click="step = 1"
                            class="flex items-center justify-center h-12 px-5 border-2 border-neutral-200 rounded-xl text-neutral-600 font-semibold hover:bg-neutral-50 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"/>
                        </svg>
                    </button>
                    <button type="button" @click="if(validateStep2()) step = 3"
                            class="btn btn-primary flex-1 h-12 font-semibold shadow-lg shadow-primary-500/20 hover:scale-[1.005] active:scale-[0.995] transition-all"
                            :class="{ 'opacity-50 cursor-not-allowed': !validateStep2() }">
                        <span class="flex items-center justify-center gap-2">
                            Continuer
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                            </svg>
                        </span>
                    </button>
                </div>
            </div>

            {{-- ============================================== --}}
            {{-- STEP 3: Plan Selection --}}
            {{-- ============================================== --}}
            <div x-show="step === 3" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0"
                 x-data="pricingCalculator()"
                 x-init="init(); formData.plan = '{{ $plan->slug ?? 'essentiel' }}';">
                <div class="mb-5">
                    <h1 class="text-xl sm:text-2xl font-bold text-neutral-900">Choisissez votre plan</h1>
                    <p class="text-neutral-500 mt-1 text-sm">Jusqu'a 20% d'economie sur l'abonnement annuel.</p>
                </div>

                {{-- Hidden inputs --}}
                <input type="hidden" name="plan" x-model="formData.plan">
                <input type="hidden" name="billing_period" :value="billingCycle">
                <template x-for="addon in selectedAddons" :key="addon">
                    <input type="hidden" :name="'addons[]'" :value="addon">
                </template>

                {{-- Plan Cards --}}
                @if(isset($availablePlans) && $availablePlans->count() > 1)
                <div class="space-y-2.5 mb-5">
                    @foreach($availablePlans as $p)
                    <button type="button"
                            @click="formData.plan = '{{ $p->slug }}'; basePriceMonthly = {{ (int) $p->price }}; currentPlanName = '{{ $p->name }}';"
                            :class="formData.plan === '{{ $p->slug }}'
                                ? 'border-primary-500 bg-primary-50/50 ring-1 ring-primary-200'
                                : 'border-neutral-200 hover:border-neutral-300'"
                            class="relative w-full p-3.5 rounded-xl border-2 text-left transition-all duration-200">
                        @if($p->is_featured)
                            <span class="absolute -top-2 right-3 bg-primary-500 text-white text-[9px] font-bold px-2 py-0.5 rounded-full uppercase tracking-wide">Populaire</span>
                        @endif
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <div class="w-3 h-3 rounded-full border-2 transition-all"
                                     :class="formData.plan === '{{ $p->slug }}' ? 'border-primary-500 bg-primary-500' : 'border-neutral-300'"></div>
                                <div>
                                    <span class="font-bold text-neutral-900 text-sm">{{ $p->name }}</span>
                                    <span class="text-xs text-neutral-500 ml-2">{{ $p->max_dishes >= 9999 ? 'Illimite' : $p->max_dishes . ' plats' }} · {{ $p->max_orders_per_month >= 9999 ? 'Cmd illimitees' : $p->max_orders_per_month . ' cmd/mois' }}</span>
                                </div>
                            </div>
                            <div class="text-right">
                                <span class="font-bold text-neutral-900">{{ number_format($p->price, 0, ',', ' ') }}</span>
                                <span class="text-xs text-neutral-500">F/mois</span>
                            </div>
                        </div>
                    </button>
                    @endforeach
                </div>
                @endif

                {{-- Billing Cycle --}}
                <div class="mb-5">
                    <label class="block text-sm font-semibold text-neutral-700 mb-2">Periode de facturation</label>
                    <div class="grid grid-cols-4 gap-1.5 bg-neutral-100 p-1 rounded-xl">
                        <button type="button" @click="billingCycle = 'monthly'"
                                :class="billingCycle === 'monthly' ? 'bg-white shadow-sm text-neutral-900 font-bold' : 'text-neutral-500 hover:text-neutral-700'"
                                class="py-2 rounded-lg text-xs font-medium transition-all">
                            1 mois
                        </button>
                        <button type="button" @click="billingCycle = 'quarterly'"
                                :class="billingCycle === 'quarterly' ? 'bg-white shadow-sm text-neutral-900 font-bold' : 'text-neutral-500 hover:text-neutral-700'"
                                class="py-2 rounded-lg text-xs font-medium transition-all">
                            3 mois
                        </button>
                        <button type="button" @click="billingCycle = 'semiannual'"
                                :class="billingCycle === 'semiannual' ? 'bg-white shadow-sm text-neutral-900 font-bold' : 'text-neutral-500 hover:text-neutral-700'"
                                class="py-2 rounded-lg text-xs font-medium transition-all">
                            6 mois
                        </button>
                        <button type="button" @click="billingCycle = 'annual'"
                                :class="billingCycle === 'annual' ? 'bg-white shadow-sm text-neutral-900 font-bold' : 'text-neutral-500 hover:text-neutral-700'"
                                class="py-2 rounded-lg text-xs font-medium transition-all relative">
                            12 mois
                            <span class="absolute -top-1.5 -right-1 bg-emerald-500 text-white text-[8px] font-bold px-1 py-0 rounded">-20%</span>
                        </button>
                    </div>
                </div>

                {{-- Features (dynamic based on selected plan) --}}
                <div class="mb-5 p-4 bg-neutral-50 rounded-xl border border-neutral-200">
                    <h3 class="text-sm font-bold text-neutral-800 mb-2.5">Inclus dans votre plan</h3>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5">
                        @foreach($availablePlans as $p)
                        <template x-if="formData.plan === '{{ $p->slug }}'">
                            <div class="contents">
                                @foreach($p->features as $f)
                                <div class="flex items-center gap-2 text-xs text-neutral-600">
                                    <svg class="w-3.5 h-3.5 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span>{{ $f }}</span>
                                </div>
                                @endforeach
                                <div class="flex items-center gap-2 text-xs text-neutral-600">
                                    <svg class="w-3.5 h-3.5 text-emerald-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    <span>QR codes + Paiement Mobile Money</span>
                                </div>
                            </div>
                        </template>
                        @endforeach
                    </div>
                </div>

                {{-- Price Summary --}}
                <div class="p-4 bg-gradient-to-br from-primary-50 to-orange-50 rounded-xl border border-primary-200 mb-5">
                    <div class="flex items-baseline justify-between mb-1">
                        <span class="text-sm font-medium text-neutral-700" x-text="currentPlanName"></span>
                        <div>
                            <span class="text-2xl font-bold text-neutral-900" x-text="formatPrice(totalPrice)"></span>
                            <span class="text-sm text-neutral-600 ml-1">FCFA</span>
                        </div>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-neutral-500" x-text="'Soit ' + formatPrice(totalPrice / getMonths()) + ' F/mois'"></span>
                        <span x-show="billingCycle !== 'monthly'" class="text-xs font-medium text-emerald-600" x-text="'Economie : ' + formatPrice(discountAmount) + ' F'"></span>
                    </div>
                </div>

                {{-- Terms --}}
                <label class="flex items-start gap-3 cursor-pointer mb-5" :class="submitError && !document.querySelector('input[name=terms]')?.checked ? 'text-red-700' : ''">
                    <input type="checkbox" name="terms" required
                           class="w-4.5 h-4.5 mt-0.5 rounded border-neutral-300 text-primary-600 focus:ring-primary-500 focus:ring-offset-0">
                    <span class="text-xs text-neutral-500 leading-relaxed">
                        J'accepte les
                        <a href="{{ route('terms') }}" target="_blank" class="text-primary-600 hover:underline font-medium">conditions d'utilisation</a>
                        et la
                        <a href="{{ route('privacy') }}" target="_blank" class="text-primary-600 hover:underline font-medium">politique de confidentialite</a>.
                    </span>
                </label>
                @error('terms')
                    <p class="mb-4 text-sm text-red-600">{{ $message }}</p>
                @enderror

                {{-- Submit error --}}
                <div x-show="submitError" x-cloak class="mb-4 p-3 bg-red-50 border border-red-200 rounded-xl">
                    <p class="text-sm text-red-700 flex items-center gap-2">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span x-text="submitError"></span>
                    </p>
                </div>

                <div class="flex gap-3">
                    <button type="button" @click="step = 2"
                            class="flex items-center justify-center h-12 px-5 border-2 border-neutral-200 rounded-xl text-neutral-600 font-semibold hover:bg-neutral-50 transition-all">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"/>
                        </svg>
                    </button>
                    <button type="submit"
                            class="btn btn-primary flex-1 h-12 font-semibold relative overflow-hidden shadow-lg shadow-primary-500/20 hover:scale-[1.005] active:scale-[0.995] transition-all"
                            :disabled="loading || submitted">
                        <span :class="{ 'opacity-0': loading }" class="flex items-center justify-center gap-2">
                            Creer mon restaurant
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                        </span>
                        <span x-show="loading" x-cloak class="absolute inset-0 flex items-center justify-center">
                            <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                        </span>
                    </button>
                </div>

                <p class="text-center text-xs text-neutral-400 mt-4 flex items-center justify-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                    Satisfait ou rembourse 7 jours — Activation sous 24h
                </p>
            </div>
        </form>

        {{-- Login link (visible on step 3) --}}
        <div x-show="step === 3" x-cloak class="mt-6">
            <div class="relative mb-5">
                <div class="absolute inset-0 flex items-center"><div class="w-full border-t border-neutral-200"></div></div>
                <div class="relative flex justify-center text-xs"><span class="px-3 bg-white text-neutral-400">Deja inscrit ?</span></div>
            </div>
            <a href="{{ route('login') }}" class="flex items-center justify-center gap-2 w-full h-11 border-2 border-neutral-200 rounded-xl text-neutral-600 text-sm font-semibold hover:border-primary-400 hover:text-primary-600 hover:bg-primary-50/30 transition-all">
                Se connecter
            </a>
        </div>
    </div>

    @push('scripts')
    <script>
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
                    plan: '{{ $plan->slug ?? "essentiel" }}'
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
                goToStep(targetStep) {
                    if (targetStep < 1 || targetStep > this.totalSteps) return;
                    if (targetStep <= this.step) {
                        this.step = targetStep;
                        this.submitError = null;
                        window.scrollTo({ top: 0, behavior: 'smooth' });
                    }
                },
                submitForm() {
                    this.submitError = null;
                    if (!this.validateStep1() || !this.validateStep2()) {
                        this.submitError = 'Veuillez remplir tous les champs obligatoires.';
                        if (!this.validateStep1()) this.step = 1;
                        else if (!this.validateStep2()) this.step = 2;
                        return;
                    }
                    const termsCheckbox = document.querySelector('input[name=terms]');
                    if (!termsCheckbox || !termsCheckbox.checked) {
                        this.submitError = 'Vous devez accepter les conditions d\'utilisation.';
                        return;
                    }
                    this.loading = true;
                    this.submitted = true;
                    document.getElementById('register-form').submit();
                }
            };
        }

        // CSRF token refresh
        (function() {
            const form = document.getElementById('register-form');
            if (form) {
                const tokenInput = form.querySelector('input[name="_token"]');
                if (tokenInput) {
                    setInterval(function() {
                        fetch('{{ route("csrf.token") }}', {
                            method: 'GET',
                            credentials: 'same-origin',
                            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                        })
                        .then(r => r.json())
                        .then(data => { if (data.token) tokenInput.value = data.token; })
                        .catch(() => {});
                    }, 3 * 60 * 1000);
                }
            }
        })();

        // Scroll to errors on load
        document.addEventListener('DOMContentLoaded', function() {
            const errors = document.querySelector('.bg-red-50');
            if (errors) errors.scrollIntoView({ behavior: 'smooth', block: 'start' });
        });

        function pricingCalculator() {
            return {
                billingCycle: 'monthly',
                selectedAddons: [],
                basePriceMonthly: {{ (int) ($plan->price ?? 15000) }},
                currentPlanName: '{{ $plan->name ?? "Essentiel" }}',
                cycleMeta: [
                    { id: 'monthly', months: 1, discount: 0 },
                    { id: 'quarterly', months: 3, discount: 10 },
                    { id: 'semiannual', months: 6, discount: 15 },
                    { id: 'annual', months: 12, discount: 20 },
                ],
                addonPrices: {
                    priority_support: 5000,
                    custom_domain: 3000,
                    extra_employees: 2000,
                    extra_dishes: 500,
                },
                init() {
                    this.billingCycle = 'monthly';
                    this.selectedAddons = [];
                },
                getCurrentCycle() {
                    const meta = this.cycleMeta.find(c => c.id === this.billingCycle);
                    const original = this.basePriceMonthly * meta.months;
                    const price = Math.round(original * (1 - meta.discount / 100));
                    return { ...meta, original, price };
                },
                getMonths() { return this.getCurrentCycle().months; },
                get basePrice() { return this.getCurrentCycle().price; },
                get discountAmount() {
                    const cycle = this.getCurrentCycle();
                    return cycle.discount > 0 ? cycle.original - cycle.price : 0;
                },
                get addonsTotal() {
                    const months = this.getMonths();
                    return this.selectedAddons.reduce((total, id) => total + (this.addonPrices[id] * months), 0);
                },
                get totalPrice() { return this.basePrice + this.addonsTotal; },
                formatPrice(price) { return new Intl.NumberFormat('fr-FR').format(Math.round(price)); }
            }
        }
    </script>
    @endpush
</x-layouts.auth>
