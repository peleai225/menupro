<x-layouts.auth title="Inscriptions fermées">
    <div class="text-center">
        <!-- Icon -->
        <div class="w-20 h-20 bg-yellow-100 rounded-full flex items-center justify-center mx-auto mb-6">
            <svg class="w-10 h-10 text-yellow-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
        </div>

        <h1 class="text-2xl font-bold text-neutral-900 mb-3">Inscriptions temporairement fermées</h1>
        
        <p class="text-neutral-600 mb-8 max-w-md mx-auto">
            Les nouvelles inscriptions sont actuellement désactivées. Veuillez réessayer plus tard ou nous contacter pour plus d'informations.
        </p>

        <div class="flex flex-col sm:flex-row items-center justify-center gap-4">
            <a href="{{ route('home') }}" class="btn btn-outline w-full sm:w-auto">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour à l'accueil
            </a>
            <a href="{{ route('contact') }}" class="btn btn-primary w-full sm:w-auto">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                Nous contacter
            </a>
        </div>

        <p class="text-sm text-neutral-500 mt-8">
            Vous avez déjà un compte ? 
            <a href="{{ route('login') }}" class="text-primary-600 hover:text-primary-700 font-medium">
                Connectez-vous
            </a>
        </p>
    </div>
</x-layouts.auth>
