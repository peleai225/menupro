<x-layouts.auth title="Mot de passe oublié">
    <div class="animate-fade-in">
        <!-- Header -->
        <div class="text-center lg:text-left mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 bg-primary-100 rounded-2xl mb-4">
                <svg class="w-7 h-7 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-neutral-900">Mot de passe oublié ?</h1>
            <p class="text-neutral-500 mt-2">Entrez votre email pour recevoir un lien de réinitialisation.</p>
        </div>

        @if (session('status'))
            <div class="mb-6 p-4 bg-secondary-50 border border-secondary-200 rounded-xl flex items-start gap-3">
                <svg class="w-5 h-5 text-secondary-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <p class="text-sm text-secondary-700">{{ session('status') }}</p>
            </div>
        @endif

        <form method="POST" action="{{ route('password.request') }}" class="space-y-5" x-data="{ loading: false }" @submit="loading = true">
            @csrf
            
            <!-- Email -->
            <div>
                <label for="email" class="block text-sm font-medium text-neutral-700 mb-2">
                    Adresse email
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
                        value="{{ old('email') }}"
                        placeholder="vous@exemple.com"
                        required
                        autofocus
                        autocomplete="email"
                        class="w-full h-12 pl-12 pr-4 bg-white border border-neutral-200 rounded-xl text-neutral-900 placeholder:text-neutral-400 transition-all duration-200 focus:outline-none focus:ring-2 focus:ring-primary-500 focus:border-transparent @error('email') border-red-500 focus:ring-red-500 @enderror"
                    >
                </div>
                @error('email')
                    <p class="mt-2 text-sm text-red-600 flex items-center gap-1.5">
                        <svg class="w-4 h-4 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7 4a1 1 0 11-2 0 1 1 0 012 0zm-1-9a1 1 0 00-1 1v4a1 1 0 102 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                        </svg>
                        {{ $message }}
                    </p>
                @enderror
            </div>

            <!-- Submit -->
            <button 
                type="submit" 
                class="btn btn-primary w-full h-12 relative overflow-hidden"
                :disabled="loading"
            >
                <span :class="{ 'opacity-0': loading }">
                    Envoyer le lien de réinitialisation
                </span>
                <span x-show="loading" x-cloak class="absolute inset-0 flex items-center justify-center">
                    <svg class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                </span>
            </button>
        </form>

        <!-- Back to Login -->
        <div class="mt-8 text-center">
            <a href="{{ route('login') }}" class="inline-flex items-center gap-2 text-neutral-600 hover:text-neutral-900 font-medium">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"/>
                </svg>
                Retour à la connexion
            </a>
        </div>
    </div>
</x-layouts.auth>

