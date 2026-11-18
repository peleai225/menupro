<x-layouts.auth title="Mot de passe oublié">
    <h1 class="vt-h2">Mot de passe oublié</h1>
    <p class="mt-2 vt-muted">Saisissez votre numéro WhatsApp : nous vous envoyons un code pour choisir un nouveau mot de passe.</p>

    @if(session('status'))
        <div class="mt-6 p-4 rounded-2xl border-2 border-[#15803D] bg-[#F0FDF4] text-[#14532D] font-medium" role="status">{{ session('status') }}</div>
    @endif

    <form method="POST" action="{{ route('password.otp.send') }}" class="mt-8 space-y-6" x-data="{ loading: false }" @submit="loading = true">
        @csrf
        <div>
            <label for="phone" class="vt-label">Numéro WhatsApp</label>
            <input type="tel" id="phone" name="phone" value="{{ old('phone') }}" placeholder="07 00 00 00 00"
                   required autofocus inputmode="tel" autocomplete="tel"
                   @error('phone') aria-invalid="true" aria-describedby="phone-error" @enderror
                   class="vt-input @error('phone') !border-[#B91C1C] @enderror">
            @error('phone')
                <p id="phone-error" class="mt-2 font-semibold text-[#B91C1C]" role="alert">{{ $message }}</p>
            @enderror
        </div>

        <button type="submit" class="vt-btn vt-btn-primary vt-btn-block" :disabled="loading" :aria-busy="loading.toString()">
            <span x-text="loading ? 'Envoi en cours…' : 'Recevoir le code WhatsApp'">Recevoir le code WhatsApp</span>
        </button>
    </form>

    <div class="mt-8 rounded-2xl bg-mp-paper p-5">
        <p class="font-semibold">Vous n'avez plus accès à ce numéro ?</p>
        <p class="mt-1 vt-muted">Écrivez-nous, nous vérifions votre identité et rétablissons l'accès.</p>
        <a href="{{ route('contact') }}?type=support" class="vt-link mt-2 inline-flex min-h-11 items-center">Contacter l'assistance</a>
    </div>

    <p class="mt-6"><a href="{{ route('login') }}" class="vt-link inline-flex min-h-11 items-center">Retour à la connexion</a></p>
</x-layouts.auth>
