<x-layouts.auth title="Mot de passe oublié">
    <h1 class="vt-h2">Mot de passe oublié</h1>
    <p class="mt-2 vt-muted">Saisissez l'adresse e-mail de votre compte : nous vous envoyons un lien pour choisir un nouveau mot de passe.</p>

    @if (session('status'))
        <div class="mt-6 p-4 rounded-2xl border-2 border-[#15803D] bg-[#F0FDF4] text-[#14532D] font-medium" role="status">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.request') }}" class="mt-8 space-y-6" x-data="{ loading: false }" @submit="loading = true">
        @csrf
        <div>
            <label for="email" class="vt-label">Adresse e-mail</label>
            <input type="email" id="email" name="email" value="{{ old('email') }}" placeholder="vous@exemple.com"
                   required autofocus autocomplete="email" autocapitalize="none"
                   @error('email') aria-invalid="true" aria-describedby="email-error" @enderror
                   class="vt-input @error('email') !border-[#B91C1C] @enderror">
            @error('email')
                <p id="email-error" class="mt-2 font-semibold text-[#B91C1C]" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="vt-btn vt-btn-primary vt-btn-block" :disabled="loading" :aria-busy="loading.toString()">
            <span x-text="loading ? 'Envoi en cours…' : 'Envoyer le lien'">Envoyer le lien</span>
        </button>
    </form>

    <div class="mt-8 rounded-2xl bg-mp-paper p-5">
        <p class="font-semibold">Inscrit avec votre numéro WhatsApp ?</p>
        <p class="mt-1 vt-muted">Recevez plutôt un code sur WhatsApp.</p>
        <a href="{{ route('password.otp.request') }}" class="vt-link mt-2 inline-flex min-h-11 items-center">Recevoir un code WhatsApp</a>
    </div>

    <p class="mt-6"><a href="{{ route('login') }}" class="vt-link inline-flex min-h-11 items-center">Retour à la connexion</a></p>
</x-layouts.auth>
