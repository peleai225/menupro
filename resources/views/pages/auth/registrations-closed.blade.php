<x-layouts.auth title="Inscriptions fermées">
    <div class="animate-fade-in text-center">
        <!-- Icon -->
        <div class="inline-flex items-center justify-center w-16 h-16 bg-amber-50 rounded-2xl mb-5">
            <svg class="w-8 h-8 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>

        <h1 class="text-2xl sm:text-3xl font-bold text-neutral-900 tracking-tight mb-3">Inscriptions temporairement fermées</h1>

        <p class="text-neutral-500 mb-8 max-w-sm mx-auto text-sm sm:text-base leading-relaxed">
            Les nouvelles inscriptions sont actuellement désactivées. Veuillez réessayer plus tard ou nous contacter.
        </p>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-3">
            <a href="{{ route('home') }}" class="flex items-center justify-center gap-2 w-full sm:w-auto h-13 px-6 border-2 border-neutral-200 rounded-xl text-neutral-700 font-semibold hover:border-neutral-300 hover:bg-neutral-50 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour à l'accueil
            </a>
            <a href="{{ route('contact') }}" class="btn btn-primary w-full sm:w-auto h-13 px-6 font-semibold shadow-lg shadow-primary-500/20">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                Nous contacter
            </a>
        </div>

        <p class="text-sm text-neutral-500 mt-8">
            Vous avez déjà un compte ?
            <a href="{{ route('login') }}" class="text-primary-600 hover:text-primary-700 font-semibold hover:underline underline-offset-2">
                Connectez-vous
            </a>
        </p>
    </div>
</x-layouts.auth>
