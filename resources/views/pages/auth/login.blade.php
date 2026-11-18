<x-layouts.auth title="Connexion">
    @if (session('success'))
        <div class="mb-6 p-4 rounded-2xl border-2 border-[#15803D] bg-[#F0FDF4] text-[#14532D] font-medium" role="status">
            {{ session('success') }}
        </div>
    @endif

    <h1 class="vt-h2">Connexion</h1>
    <p class="mt-2 vt-muted">Accédez à l'espace de gestion de votre commerce.</p>

    <form method="POST" action="{{ route('login.post') }}" class="mt-8 space-y-6" x-data="{ loading: false, showPassword: false }" @submit="loading = true">
        @csrf

        {{-- Identifiant : le contrôleur accepte le numéro WhatsApp OU l'adresse e-mail (LoginRequest) --}}
        <div>
            <label for="login" class="vt-label">Numéro WhatsApp ou e-mail</label>
            <input type="text" id="login" name="login" value="{{ old('login') }}"
                   placeholder="07 00 00 00 00"
                   required autofocus autocomplete="username" autocapitalize="none" spellcheck="false"
                   aria-describedby="login-hint @error('login') login-error @enderror"
                   @error('login') aria-invalid="true" @enderror
                   class="vt-input @error('login') !border-[#B91C1C] @enderror">
            <p id="login-hint" class="mt-2 vt-hint">Le numéro utilisé à l'inscription. Inscrit avec un e-mail ? Saisissez-le ici.</p>
            @error('login')
                <p id="login-error" class="mt-2 font-semibold text-[#B91C1C]" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <div>
            <div class="flex flex-wrap items-baseline justify-between gap-x-4 gap-y-1 mb-2">
                <label for="password" class="vt-label !mb-0">Mot de passe</label>
                <a href="{{ route('password.request') }}" class="vt-link text-[0.9375rem]">Mot de passe oublié ?</a>
            </div>
            <div class="relative">
                <input :type="showPassword ? 'text' : 'password'" type="password"
                       id="password" name="password" required autocomplete="current-password"
                       @error('password') aria-invalid="true" aria-describedby="password-error" @enderror
                       class="vt-input !pr-14 @error('password') !border-[#B91C1C] @enderror">
                <button type="button" @click="showPassword = !showPassword"
                        class="absolute right-1 top-1/2 -translate-y-1/2 w-12 h-12 flex items-center justify-center rounded-xl text-mp-text hover:text-mp-black"
                        :aria-label="showPassword ? 'Masquer le mot de passe' : 'Afficher le mot de passe'" aria-label="Afficher le mot de passe">
                    <svg x-show="!showPassword" class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                    <svg x-show="showPassword" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/></svg>
                </button>
            </div>
            @error('password')
                <p id="password-error" class="mt-2 font-semibold text-[#B91C1C]" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <label for="remember" class="flex items-center gap-3 min-h-11 cursor-pointer select-none">
            <input type="checkbox" id="remember" name="remember" class="w-6 h-6 rounded-md border-2 border-[#857C74] text-mp-black focus:ring-mp-orange" {{ old('remember') ? 'checked' : '' }}>
            <span>Rester connecté sur ce téléphone</span>
        </label>

        <button type="submit" class="vt-btn vt-btn-primary vt-btn-block relative" :disabled="loading" :aria-busy="loading.toString()">
            <span :class="{ 'opacity-0': loading }">Se connecter</span>
            <span x-show="loading" x-cloak class="absolute inset-0 flex items-center justify-center" aria-hidden="true">
                <svg class="animate-spin h-6 w-6" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
            </span>
        </button>
    </form>

    <div class="mt-10 rounded-2xl bg-mp-paper p-5">
        <h2 class="vt-h3">Pas encore de compte ?</h2>
        <p class="mt-1 vt-muted">Restaurant, maquis, stand ou boutique : {{ \App\Support\VitrineGrid::trialDays() }} jours d'essai gratuit.</p>
        <a href="{{ route('register') }}" class="vt-btn vt-btn-dark vt-btn-block mt-4">Créer mon commerce</a>
    </div>

    <ul class="mt-6 space-y-1 text-[0.9375rem] vt-muted">
        <li>Connexion sécurisée (HTTPS).</li>
        <li>Données protégées (loi ivoirienne n° 2013-450).</li>
    </ul>
</x-layouts.auth>
