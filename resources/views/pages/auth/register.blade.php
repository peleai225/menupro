<x-layouts.auth title="Inscription">
    <div x-data="registerForm()" class="animate-fade-in">
        {{-- ═══ Stepper ═══ --}}
        <div class="mb-8 sm:mb-10">
            {{-- Mobile --}}
            <div class="sm:hidden flex items-center justify-between">
                <div>
                    <span class="text-[10px] font-bold text-primary-600 uppercase tracking-widest">Étape <span x-text="step"></span>/3</span>
                    <div class="text-sm font-bold text-neutral-900 mt-0.5" x-text="step === 1 ? 'Votre compte' : (step === 2 ? 'Votre restaurant' : 'Votre abonnement')"></div>
                </div>
                <div class="flex gap-1">
                    <template x-for="i in 3" :key="i">
                        <div class="h-1.5 rounded-full transition-all duration-500"
                             :class="i <= step ? 'w-7 bg-gradient-to-r from-primary-400 to-primary-600' : 'w-4 bg-neutral-200'"></div>
                    </template>
                </div>
            </div>

            {{-- Desktop --}}
            <div class="hidden sm:flex items-center gap-2">
                @foreach([['n' => 1, 'label' => 'Compte'], ['n' => 2, 'label' => 'Restaurant'], ['n' => 3, 'label' => 'Abonnement']] as $s)
                    @if($s['n'] > 1)
                        <div class="flex-1 h-[2px] rounded-full bg-neutral-200 overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-primary-400 to-primary-600 transition-all duration-700 ease-out"
                                 :style="step > {{ $s['n'] - 1 }} ? 'width: 100%' : 'width: 0%'"></div>
                        </div>
                    @endif
                    <button type="button" @click="step > {{ $s['n'] }} && goToStep({{ $s['n'] }})"
                            :disabled="step < {{ $s['n'] }}"
                            class="flex items-center gap-2 focus:outline-none transition-all"
                            :class="step < {{ $s['n'] }} ? 'opacity-40 cursor-not-allowed' : 'cursor-pointer'">
                        <div class="w-7 h-7 rounded-full flex items-center justify-center text-[11px] font-bold transition-all duration-300"
                             :class="step > {{ $s['n'] }}
                                ? 'bg-primary-500 text-white shadow-md shadow-primary-500/30'
                                : (step === {{ $s['n'] }}
                                    ? 'bg-gradient-to-br from-primary-500 to-primary-600 text-white shadow-lg shadow-primary-500/40 scale-110'
                                    : 'bg-neutral-100 text-neutral-400 border border-neutral-200')">
                            <template x-if="step > {{ $s['n'] }}">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                            </template>
                            <template x-if="step <= {{ $s['n'] }}"><span>{{ $s['n'] }}</span></template>
                        </div>
                        <span class="text-[13px] font-semibold transition-colors" :class="step >= {{ $s['n'] }} ? 'text-neutral-800' : 'text-neutral-400'">{{ $s['label'] }}</span>
                    </button>
                @endforeach
            </div>
        </div>

        {{-- Agent badge --}}
        @if(session('register_ref_agent'))
            @php $refAgent = \App\Models\CommandoAgent::where('uuid', session('register_ref_agent'))->first(); @endphp
            @if($refAgent)
            <div class="mb-5 p-3 bg-gradient-to-r from-primary-50/80 to-orange-50/80 border border-primary-200/60 rounded-2xl flex items-center gap-3 animate-slide-up">
                <div class="w-9 h-9 rounded-full bg-gradient-to-br from-primary-400 to-primary-600 flex items-center justify-center shrink-0 shadow-md shadow-primary-500/20">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <div>
                    <p class="text-[13px] font-semibold text-neutral-800">Parrainé par {{ $refAgent->full_name }}</p>
                    <p class="text-[11px] text-neutral-500">Agent MenuPro — {{ $refAgent->city }}</p>
                </div>
            </div>
            @endif
        @endif

        {{-- Errors --}}
        @if ($errors->any())
            <div class="mb-6 p-4 bg-error-500/5 border border-error-500/20 rounded-2xl animate-slide-up">
                <div class="flex items-start gap-3">
                    <div class="w-6 h-6 rounded-full bg-error-500/10 flex items-center justify-center flex-shrink-0 mt-0.5">
                        <svg class="w-3.5 h-3.5 text-error-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <div>
                        <h3 class="text-[13px] font-bold text-error-600 mb-1">Corrigez les erreurs</h3>
                        <ul class="space-y-0.5 text-[13px] text-error-600/90">
                            @foreach ($errors->all() as $error)<li>• {{ $error }}</li>@endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

        <form method="POST" action="{{ route('register.post') }}" enctype="multipart/form-data" @submit.prevent="submitForm()" id="register-form">
            @csrf

            {{-- ═══════════════════════════════════════
                 STEP 1 — Account
            ═══════════════════════════════════════ --}}
            <div x-show="step === 1" x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
                <div class="mb-8">
                    <h1 class="text-[22px] sm:text-2xl font-bold text-neutral-950 tracking-tight">Créez votre compte</h1>
                    <p class="text-neutral-500 mt-2 text-[15px]">Remplissez vos informations personnelles pour commencer.</p>
                </div>

                <div class="space-y-5">
                    {{-- Name --}}
                    <div>
                        <label for="name" class="block text-[13px] font-semibold text-neutral-700 mb-2">Nom complet <span class="text-error-500">*</span></label>
                        <div class="relative group">
                            <div class="absolute left-0 top-0 bottom-0 w-12 flex items-center justify-center pointer-events-none">
                                <svg class="w-[18px] h-[18px] text-neutral-400 group-focus-within:text-primary-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            </div>
                            <input type="text" id="name" name="name" x-model="formData.name" placeholder="Jean Kouassi" required autocomplete="name"
                                   class="w-full h-[52px] pl-12 pr-4 bg-neutral-50/80 border border-neutral-200 rounded-2xl text-neutral-900 text-[15px] placeholder:text-neutral-400 transition-all duration-200 focus:outline-none focus:bg-white focus:border-primary-400 focus:ring-[3px] focus:ring-primary-500/10">
                        </div>
                    </div>

                    {{-- Email --}}
                    <div>
                        <label for="email" class="block text-[13px] font-semibold text-neutral-700 mb-2">Adresse email <span class="text-error-500">*</span></label>
                        <div class="relative group">
                            <div class="absolute left-0 top-0 bottom-0 w-12 flex items-center justify-center pointer-events-none">
                                <svg class="w-[18px] h-[18px] text-neutral-400 group-focus-within:text-primary-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                            </div>
                            <input type="email" id="email" name="email" x-model="formData.email" placeholder="vous@exemple.com" required autocomplete="email"
                                   class="w-full h-[52px] pl-12 pr-4 bg-neutral-50/80 border border-neutral-200 rounded-2xl text-neutral-900 text-[15px] placeholder:text-neutral-400 transition-all duration-200 focus:outline-none focus:bg-white focus:border-primary-400 focus:ring-[3px] focus:ring-primary-500/10">
                        </div>
                    </div>

                    {{-- Phone --}}
                    <div>
                        <label for="phone" class="block text-[13px] font-semibold text-neutral-700 mb-2">Téléphone WhatsApp <span class="text-error-500">*</span></label>
                        <div class="relative group">
                            <div class="absolute left-0 top-0 bottom-0 w-12 flex items-center justify-center pointer-events-none">
                                <svg class="w-[18px] h-[18px] text-neutral-400 group-focus-within:text-primary-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            </div>
                            <input type="tel" id="phone" name="phone" x-model="formData.phone" placeholder="+225 07 00 00 00 00" required autocomplete="tel"
                                   class="w-full h-[52px] pl-12 pr-4 bg-neutral-50/80 border border-neutral-200 rounded-2xl text-neutral-900 text-[15px] placeholder:text-neutral-400 transition-all duration-200 focus:outline-none focus:bg-white focus:border-primary-400 focus:ring-[3px] focus:ring-primary-500/10">
                        </div>
                    </div>

                    {{-- Password --}}
                    <div>
                        <label for="reg-password" class="block text-[13px] font-semibold text-neutral-700 mb-2">Mot de passe <span class="text-error-500">*</span></label>
                        <div class="relative group">
                            <div class="absolute left-0 top-0 bottom-0 w-12 flex items-center justify-center pointer-events-none">
                                <svg class="w-[18px] h-[18px] text-neutral-400 group-focus-within:text-primary-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                            </div>
                            <input :type="showPassword ? 'text' : 'password'" id="reg-password" name="password" x-model="formData.password"
                                   placeholder="Minimum 8 caractères" required minlength="8" autocomplete="new-password"
                                   class="w-full h-[52px] pl-12 pr-12 bg-neutral-50/80 border border-neutral-200 rounded-2xl text-neutral-900 text-[15px] placeholder:text-neutral-400 transition-all duration-200 focus:outline-none focus:bg-white focus:border-primary-400 focus:ring-[3px] focus:ring-primary-500/10">
                            <button type="button" @click="showPassword = !showPassword"
                                    class="absolute right-3.5 top-1/2 -translate-y-1/2 w-8 h-8 flex items-center justify-center text-neutral-400 hover:text-neutral-600 rounded-xl hover:bg-neutral-100 transition-all">
                                <svg x-show="!showPassword" class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                <svg x-show="showPassword" x-cloak class="w-[18px] h-[18px]" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                            </button>
                        </div>
                        <div class="mt-2.5 flex gap-1.5">
                            <div class="h-1.5 flex-1 rounded-full transition-all duration-300" :class="formData.password.length >= 1 ? (formData.password.length >= 8 ? 'bg-secondary-400' : 'bg-warning-500') : 'bg-neutral-200'"></div>
                            <div class="h-1.5 flex-1 rounded-full transition-all duration-300" :class="formData.password.length >= 4 ? (formData.password.length >= 8 ? 'bg-secondary-400' : 'bg-warning-500') : 'bg-neutral-200'"></div>
                            <div class="h-1.5 flex-1 rounded-full transition-all duration-300" :class="formData.password.length >= 8 ? 'bg-secondary-500' : 'bg-neutral-200'"></div>
                            <div class="h-1.5 flex-1 rounded-full transition-all duration-300" :class="formData.password.length >= 12 ? 'bg-secondary-600' : 'bg-neutral-200'"></div>
                        </div>
                    </div>
                </div>

                <button type="button" @click="if(validateStep1()) { step = 2; window.scrollTo({top:0, behavior:'smooth'}) }"
                        class="w-full h-[52px] mt-8 bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white font-semibold text-[15px] rounded-2xl shadow-lg shadow-primary-500/25 hover:shadow-primary-500/40 active:scale-[0.98] transition-all duration-200"
                        :class="{ 'opacity-50 cursor-not-allowed': !validateStep1() }">
                    <span class="flex items-center justify-center gap-2">
                        Continuer
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                    </span>
                </button>

                <p class="text-center text-[13px] text-neutral-400 mt-5">
                    Déjà inscrit ? <a href="{{ route('login') }}" class="text-primary-600 font-semibold hover:underline underline-offset-2">Se connecter</a>
                </p>
            </div>

            {{-- ═══════════════════════════════════════
                 STEP 2 — Restaurant
            ═══════════════════════════════════════ --}}
            <div x-show="step === 2" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
                <div class="mb-8">
                    <h1 class="text-[22px] sm:text-2xl font-bold text-neutral-950 tracking-tight">Votre restaurant</h1>
                    <p class="text-neutral-500 mt-2 text-[15px]">Décrivez votre établissement — vous pourrez tout modifier après.</p>
                </div>

                <div class="space-y-5">
                    {{-- Restaurant Name --}}
                    <div>
                        <label for="restaurant_name" class="block text-[13px] font-semibold text-neutral-700 mb-2">Nom de l'établissement <span class="text-error-500">*</span></label>
                        <div class="relative group">
                            <div class="absolute left-0 top-0 bottom-0 w-12 flex items-center justify-center pointer-events-none">
                                <svg class="w-[18px] h-[18px] text-neutral-400 group-focus-within:text-primary-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            </div>
                            <input type="text" id="restaurant_name" name="restaurant_name" x-model="formData.restaurant_name" placeholder="Le Délice" required
                                   class="w-full h-[52px] pl-12 pr-4 bg-neutral-50/80 border border-neutral-200 rounded-2xl text-neutral-900 text-[15px] placeholder:text-neutral-400 transition-all duration-200 focus:outline-none focus:bg-white focus:border-primary-400 focus:ring-[3px] focus:ring-primary-500/10">
                        </div>
                    </div>

                    {{-- Type --}}
                    <div>
                        <label for="restaurant_type" class="block text-[13px] font-semibold text-neutral-700 mb-2">Type <span class="text-error-500">*</span></label>
                        <div class="relative group">
                            <select id="restaurant_type" name="restaurant_type" x-model="formData.restaurant_type" required
                                    class="w-full h-[52px] pl-4 pr-10 bg-neutral-50/80 border border-neutral-200 rounded-2xl text-neutral-900 text-[15px] transition-all duration-200 focus:outline-none focus:bg-white focus:border-primary-400 focus:ring-[3px] focus:ring-primary-500/10 appearance-none cursor-pointer">
                                <option value="">Sélectionnez un type</option>
                                @foreach(['restaurant' => 'Restaurant', 'bar' => 'Bar', 'brasserie' => 'Brasserie', 'maquis' => 'Maquis', 'traiteur' => 'Traiteur', 'cafe' => 'Café', 'food_truck' => 'Food Truck', 'brunch' => 'Brunch', 'evenementiel' => 'Événementiel'] as $val => $label)
                                    <option value="{{ $val }}">{{ $label }}</option>
                                @endforeach
                            </select>
                            <svg class="absolute right-4 top-1/2 -translate-y-1/2 w-4 h-4 text-neutral-400 pointer-events-none" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </div>
                    </div>

                    {{-- Company (optional) --}}
                    <div>
                        <label for="company_name" class="block text-[13px] font-semibold text-neutral-700 mb-2">Entreprise <span class="text-neutral-400 font-normal">(optionnel)</span></label>
                        <input type="text" id="company_name" name="company_name" x-model="formData.company_name" placeholder="SARL Le Délice"
                               class="w-full h-[52px] px-4 bg-neutral-50/80 border border-neutral-200 rounded-2xl text-neutral-900 text-[15px] placeholder:text-neutral-400 transition-all duration-200 focus:outline-none focus:bg-white focus:border-primary-400 focus:ring-[3px] focus:ring-primary-500/10">
                    </div>

                    {{-- RCCM (collapsible) --}}
                    <div x-data="{ open: false }">
                        <button type="button" @click="open = !open" class="flex items-center gap-2 text-[13px] font-medium text-primary-600 hover:text-primary-700 transition-colors py-1">
                            <svg class="w-3.5 h-3.5 transition-transform duration-200" :class="open && 'rotate-90'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                            Ajouter un RCCM (badge vérifié)
                        </button>
                        <div x-show="open" x-cloak x-transition class="mt-2.5 p-4 bg-neutral-50 rounded-2xl border border-neutral-200 space-y-3">
                            <input type="text" id="rccm" name="rccm" x-model="formData.rccm" placeholder="CI-ABJ-XX-2024-XXXXX"
                                   class="w-full h-[44px] px-4 bg-white border border-neutral-200 rounded-xl text-neutral-900 text-[14px] placeholder:text-neutral-400 transition-all focus:outline-none focus:border-primary-400 focus:ring-[3px] focus:ring-primary-500/10">
                            <div>
                                <input type="file" id="rccm_document" name="rccm_document" accept=".pdf,.jpg,.jpeg,.png"
                                       class="w-full text-[13px] text-neutral-600 file:mr-3 file:py-1.5 file:px-3 file:rounded-lg file:border-0 file:text-[11px] file:font-bold file:bg-primary-500 file:text-white hover:file:bg-primary-600 file:cursor-pointer">
                                <p class="text-[11px] text-neutral-400 mt-1">PDF, JPEG ou PNG — Max 5 Mo</p>
                            </div>
                        </div>
                    </div>

                    {{-- Logo & Banner --}}
                    <div>
                        <label class="block text-[13px] font-semibold text-neutral-700 mb-2">Images <span class="text-neutral-400 font-normal">(optionnel)</span></label>
                        <div class="grid grid-cols-2 gap-3">
                            <label for="logo-upload" class="group relative aspect-square rounded-2xl bg-neutral-50 border-2 border-dashed border-neutral-200 flex flex-col items-center justify-center overflow-hidden cursor-pointer hover:border-primary-300 hover:bg-primary-50/30 transition-all">
                                <template x-if="logoPreview"><img :src="logoPreview" class="absolute inset-0 w-full h-full object-cover"></template>
                                <template x-if="!logoPreview">
                                    <div class="text-center">
                                        <div class="w-10 h-10 rounded-full bg-neutral-100 group-hover:bg-primary-100 flex items-center justify-center mx-auto transition-colors">
                                            <svg class="w-5 h-5 text-neutral-400 group-hover:text-primary-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        </div>
                                        <p class="text-[11px] text-neutral-500 font-medium mt-2">Logo</p>
                                    </div>
                                </template>
                            </label>
                            <input type="file" name="logo" id="logo-upload" accept="image/png,image/jpeg,image/webp" class="hidden"
                                   @change="logoPreview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null">

                            <label class="group relative aspect-square rounded-2xl bg-neutral-50 border-2 border-dashed border-neutral-200 flex flex-col items-center justify-center overflow-hidden cursor-pointer hover:border-primary-300 hover:bg-primary-50/30 transition-all">
                                <template x-if="bannerPreview"><img :src="bannerPreview" class="absolute inset-0 w-full h-full object-cover"></template>
                                <template x-if="!bannerPreview">
                                    <div class="text-center">
                                        <div class="w-10 h-10 rounded-full bg-neutral-100 group-hover:bg-primary-100 flex items-center justify-center mx-auto transition-colors">
                                            <svg class="w-5 h-5 text-neutral-400 group-hover:text-primary-500 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                                        </div>
                                        <p class="text-[11px] text-neutral-500 font-medium mt-2">Bannière</p>
                                    </div>
                                </template>
                                <input type="file" name="banner" accept="image/png,image/jpeg,image/webp" class="absolute inset-0 opacity-0 cursor-pointer"
                                       @change="bannerPreview = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null">
                            </label>
                        </div>
                    </div>
                </div>

                <div class="flex gap-3 mt-8">
                    <button type="button" @click="step = 1; window.scrollTo({top:0, behavior:'smooth'})"
                            class="w-12 h-[52px] flex items-center justify-center border-2 border-neutral-200 rounded-2xl text-neutral-500 hover:bg-neutral-50 hover:border-neutral-300 transition-all flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <button type="button" @click="if(validateStep2()) { step = 3; window.scrollTo({top:0, behavior:'smooth'}) }"
                            class="flex-1 h-[52px] bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white font-semibold text-[15px] rounded-2xl shadow-lg shadow-primary-500/25 hover:shadow-primary-500/40 active:scale-[0.98] transition-all duration-200"
                            :class="{ 'opacity-50 cursor-not-allowed': !validateStep2() }">
                        <span class="flex items-center justify-center gap-2">
                            Continuer
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M13 7l5 5m0 0l-5 5m5-5H6"/></svg>
                        </span>
                    </button>
                </div>
            </div>

            {{-- ═══════════════════════════════════════
                 STEP 3 — Plan
            ═══════════════════════════════════════ --}}
            <div x-show="step === 3" x-cloak x-transition:enter="transition ease-out duration-300" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0"
                 x-data="pricingCalculator()"
                 x-init="init(); formData.plan = '{{ $plan->slug ?? 'essentiel' }}';">
                <div class="mb-6">
                    <h1 class="text-[22px] sm:text-2xl font-bold text-neutral-950 tracking-tight">Choisissez votre plan</h1>
                    <p class="text-neutral-500 mt-1.5 text-[14px]">7 jours d'essai gratuit inclus, sans engagement.</p>
                </div>

                {{-- Hidden inputs --}}
                <input type="hidden" name="plan" x-model="formData.plan">
                <input type="hidden" name="billing_period" :value="billingCycle">

                {{-- Plan cards --}}
                @if(isset($availablePlans) && $availablePlans->count() > 1)
                <div class="space-y-2.5 mb-6">
                    @foreach($availablePlans as $p)
                    <button type="button"
                            @click="formData.plan = '{{ $p->slug }}'; basePriceMonthly = {{ (int) $p->price }}; currentPlanName = '{{ $p->name }}';"
                            :class="formData.plan === '{{ $p->slug }}'
                                ? 'border-primary-400 bg-primary-50/60 shadow-md shadow-primary-500/10'
                                : 'border-neutral-200 hover:border-neutral-300 hover:bg-neutral-50/50'"
                            class="relative w-full p-4 rounded-2xl border-2 text-left transition-all duration-200 group">
                        @if($p->is_featured)
                            <span class="absolute -top-2.5 right-4 bg-gradient-to-r from-primary-500 to-primary-600 text-white text-[9px] font-bold px-2.5 py-0.5 rounded-full uppercase tracking-wider shadow-md shadow-primary-500/20">Populaire</span>
                        @endif
                        <div class="flex items-center gap-3.5">
                            {{-- Radio indicator --}}
                            <div class="w-5 h-5 rounded-full border-2 flex items-center justify-center transition-all flex-shrink-0"
                                 :class="formData.plan === '{{ $p->slug }}' ? 'border-primary-500' : 'border-neutral-300'">
                                <div class="w-2.5 h-2.5 rounded-full bg-primary-500 transition-all"
                                     :class="formData.plan === '{{ $p->slug }}' ? 'scale-100' : 'scale-0'"></div>
                            </div>
                            {{-- Plan info --}}
                            <div class="flex-1 min-w-0">
                                <div class="flex items-baseline gap-2">
                                    <span class="font-bold text-neutral-900 text-[15px]">{{ $p->name }}</span>
                                    <span class="text-[12px] text-neutral-500">{{ $p->max_dishes >= 9999 ? 'Illimité' : $p->max_dishes . ' plats' }}</span>
                                </div>
                                <div class="text-[12px] text-neutral-500 mt-0.5">
                                    {{ $p->max_orders_per_month >= 9999 ? 'Commandes illimitées' : number_format($p->max_orders_per_month) . ' cmd/mois' }}
                                    · {{ $p->max_employees }} compte{{ $p->max_employees > 1 ? 's' : '' }}
                                    @if($p->has_delivery) · Livraison @endif
                                </div>
                            </div>
                            {{-- Price --}}
                            <div class="text-right flex-shrink-0">
                                <div class="font-bold text-neutral-900 text-[15px]">{{ number_format($p->price, 0, ',', '.') }}<span class="text-[12px] font-medium text-neutral-500 ml-0.5">F</span></div>
                                <div class="text-[11px] text-neutral-400">/mois</div>
                            </div>
                        </div>
                    </button>
                    @endforeach
                </div>
                @endif

                {{-- Billing toggle --}}
                <div class="mb-5">
                    <label class="block text-[13px] font-semibold text-neutral-700 mb-2">Facturation</label>
                    <div class="grid grid-cols-4 gap-1 p-1 bg-neutral-100 rounded-xl">
                        @foreach([['id' => 'monthly', 'label' => '1 mois', 'badge' => null], ['id' => 'quarterly', 'label' => '3 mois', 'badge' => '-10%'], ['id' => 'semiannual', 'label' => '6 mois', 'badge' => '-15%'], ['id' => 'annual', 'label' => '1 an', 'badge' => '-20%']] as $cycle)
                        <button type="button" @click="billingCycle = '{{ $cycle['id'] }}'"
                                :class="billingCycle === '{{ $cycle['id'] }}' ? 'bg-white shadow-sm text-neutral-900 font-bold' : 'text-neutral-500 hover:text-neutral-700'"
                                class="relative py-2.5 rounded-lg text-[11px] font-medium transition-all duration-200">
                            {{ $cycle['label'] }}
                            @if($cycle['badge'])
                            <span class="absolute -top-1 -right-0.5 bg-secondary-500 text-white text-[7px] font-bold px-1 py-px rounded leading-tight"
                                  x-show="billingCycle !== '{{ $cycle['id'] }}'">{{ $cycle['badge'] }}</span>
                            @endif
                        </button>
                        @endforeach
                    </div>
                </div>

                {{-- Features --}}
                <div class="mb-5 p-4 rounded-2xl border border-neutral-200 bg-neutral-50/50">
                    <div class="text-[12px] font-bold text-neutral-600 uppercase tracking-wider mb-2.5">Inclus</div>
                    <div class="grid grid-cols-1 sm:grid-cols-2 gap-1.5">
                        @foreach($availablePlans as $p)
                        <template x-if="formData.plan === '{{ $p->slug }}'">
                            <div class="contents">
                                @foreach($p->features as $f)
                                <div class="flex items-center gap-2">
                                    <svg class="w-3.5 h-3.5 text-secondary-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                    <span class="text-[12px] text-neutral-700">{{ $f }}</span>
                                </div>
                                @endforeach
                                <div class="flex items-center gap-2">
                                    <svg class="w-3.5 h-3.5 text-secondary-500 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                    <span class="text-[12px] text-neutral-700">QR codes + Mobile Money</span>
                                </div>
                            </div>
                        </template>
                        @endforeach
                    </div>
                </div>

                {{-- Price card --}}
                <div class="p-4 rounded-2xl bg-gradient-to-br from-neutral-900 to-neutral-950 text-white mb-5 relative overflow-hidden">
                    <div class="absolute top-0 right-0 w-32 h-32 bg-primary-500/10 rounded-full blur-[40px]"></div>
                    <div class="relative">
                        <div class="flex items-baseline justify-between mb-1">
                            <span class="text-[13px] font-medium text-neutral-400" x-text="currentPlanName + ' — ' + (billingCycle === 'monthly' ? '1 mois' : billingCycle === 'quarterly' ? '3 mois' : billingCycle === 'semiannual' ? '6 mois' : '12 mois')"></span>
                        </div>
                        <div class="flex items-baseline gap-1.5">
                            <span class="text-3xl font-bold" x-text="formatPrice(totalPrice)"></span>
                            <span class="text-[13px] text-neutral-400">FCFA</span>
                        </div>
                        <div class="flex items-center justify-between mt-2">
                            <span class="text-[12px] text-neutral-500" x-text="'Soit ' + formatPrice(totalPrice / getMonths()) + ' FCFA/mois'"></span>
                            <span x-show="billingCycle !== 'monthly'" class="text-[12px] font-semibold text-secondary-400" x-text="'-' + formatPrice(discountAmount) + ' F économisés'"></span>
                        </div>
                    </div>
                </div>

                {{-- Terms --}}
                <label class="flex items-start gap-3 cursor-pointer mb-5">
                    <input type="checkbox" name="terms" required
                           class="w-[18px] h-[18px] mt-0.5 rounded-md border-neutral-300 text-primary-500 focus:ring-primary-500/20 focus:ring-offset-0 cursor-pointer">
                    <span class="text-[12px] text-neutral-500 leading-relaxed">
                        J'accepte les <a href="{{ route('terms') }}" target="_blank" class="text-primary-600 font-medium hover:underline">conditions</a>
                        et la <a href="{{ route('privacy') }}" target="_blank" class="text-primary-600 font-medium hover:underline">politique de confidentialité</a>.
                    </span>
                </label>

                {{-- Error --}}
                <div x-show="submitError" x-cloak class="mb-4 p-3 bg-error-500/5 border border-error-500/20 rounded-xl">
                    <p class="text-[13px] text-error-600 font-medium flex items-center gap-2">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <span x-text="submitError"></span>
                    </p>
                </div>

                {{-- Buttons --}}
                <div class="flex gap-3">
                    <button type="button" @click="step = 2; window.scrollTo({top:0, behavior:'smooth'})"
                            class="w-12 h-[52px] flex items-center justify-center border-2 border-neutral-200 rounded-2xl text-neutral-500 hover:bg-neutral-50 hover:border-neutral-300 transition-all flex-shrink-0">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    </button>
                    <button type="submit"
                            class="flex-1 h-[52px] bg-gradient-to-r from-primary-500 to-primary-600 hover:from-primary-600 hover:to-primary-700 text-white font-semibold text-[15px] rounded-2xl relative overflow-hidden shadow-lg shadow-primary-500/25 hover:shadow-primary-500/40 active:scale-[0.98] transition-all duration-200"
                            :disabled="loading || submitted">
                        <span :class="{ 'opacity-0': loading }" class="flex items-center justify-center gap-2">
                            Démarrer l'essai gratuit
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                        </span>
                        <span x-show="loading" x-cloak class="absolute inset-0 flex items-center justify-center">
                            <svg class="animate-spin h-5 w-5 text-white" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        </span>
                    </button>
                </div>

                <p class="text-center text-[11px] text-neutral-400 mt-4 flex items-center justify-center gap-1.5">
                    <svg class="w-3.5 h-3.5 text-secondary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    7 jours gratuits · Sans carte bancaire · Annulation libre
                </p>
            </div>
        </form>

        {{-- Login (step 3) --}}
        <div x-show="step === 3" x-cloak class="mt-6 text-center">
            <span class="text-[13px] text-neutral-400">Déjà inscrit ?</span>
            <a href="{{ route('login') }}" class="text-[13px] text-primary-600 font-semibold hover:underline underline-offset-2 ml-1">Se connecter</a>
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
                    name: '', email: '', phone: '', password: '',
                    restaurant_name: '', restaurant_type: '', company_name: '', rccm: '',
                    plan: '{{ $plan->slug ?? "essentiel" }}'
                },
                showPassword: false,
                logoPreview: null,
                bannerPreview: null,
                validateStep1() { return this.formData.name && this.formData.email && this.formData.phone && this.formData.password.length >= 8; },
                validateStep2() { return this.formData.restaurant_name && this.formData.restaurant_type; },
                goToStep(t) { if (t >= 1 && t <= this.step) { this.step = t; this.submitError = null; } },
                submitForm() {
                    this.submitError = null;
                    if (!this.validateStep1() || !this.validateStep2()) {
                        this.submitError = 'Veuillez remplir tous les champs obligatoires.';
                        this.step = !this.validateStep1() ? 1 : 2;
                        return;
                    }
                    const terms = document.querySelector('input[name=terms]');
                    if (!terms || !terms.checked) { this.submitError = 'Acceptez les conditions d\'utilisation.'; return; }
                    this.loading = true;
                    this.submitted = true;
                    document.getElementById('register-form').submit();
                }
            };
        }

        (function() {
            const form = document.getElementById('register-form');
            if (!form) return;
            const token = form.querySelector('input[name="_token"]');
            if (token) setInterval(() => {
                fetch('{{ route("csrf.token") }}', { credentials: 'same-origin', headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' } })
                    .then(r => r.json()).then(d => { if (d.token) token.value = d.token; }).catch(() => {});
            }, 180000);
        })();

        document.addEventListener('DOMContentLoaded', () => {
            const e = document.querySelector('[class*="bg-error"]');
            if (e) e.scrollIntoView({ behavior: 'smooth', block: 'start' });
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
                init() { this.billingCycle = 'monthly'; },
                getCurrentCycle() {
                    const m = this.cycleMeta.find(c => c.id === this.billingCycle);
                    const orig = this.basePriceMonthly * m.months;
                    return { ...m, original: orig, price: Math.round(orig * (1 - m.discount / 100)) };
                },
                getMonths() { return this.getCurrentCycle().months; },
                get basePrice() { return this.getCurrentCycle().price; },
                get discountAmount() { const c = this.getCurrentCycle(); return c.original - c.price; },
                get totalPrice() { return this.basePrice; },
                formatPrice(p) { return new Intl.NumberFormat('fr-FR').format(Math.round(p)); }
            }
        }
    </script>
    @endpush
</x-layouts.auth>
