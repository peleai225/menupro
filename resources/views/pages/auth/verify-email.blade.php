<x-layouts.auth title="Vérification de l'email">
    <div class="animate-fade-in">
        <!-- Header -->
        <div class="text-center lg:text-left mb-8">
            <div class="inline-flex items-center justify-center w-14 h-14 bg-primary-100 rounded-2xl mb-4">
                <svg class="w-7 h-7 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
            </div>
            <h1 class="text-3xl font-bold text-neutral-900">Vérifiez votre adresse email</h1>
            <p class="text-neutral-500 mt-2">Un lien de vérification a été envoyé à votre adresse email.</p>
        </div>

        @if (session('status') === 'verification-link-sent')
            <div class="mb-6 p-4 bg-secondary-50 border border-secondary-200 rounded-xl flex items-start gap-3">
                <svg class="w-5 h-5 text-secondary-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <p class="text-sm font-medium text-secondary-700">Nouveau lien envoyé !</p>
                    <p class="text-xs text-secondary-600 mt-1">Un nouveau lien de vérification a été envoyé à <strong>{{ auth()->user()->email }}</strong></p>
                </div>
            </div>
        @endif

        @if (session('warning'))
            <div class="mb-6 p-4 bg-yellow-50 border border-yellow-200 rounded-xl flex items-start gap-3">
                <svg class="w-5 h-5 text-yellow-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <p class="text-sm text-yellow-700">{{ session('warning') }}</p>
            </div>
        @endif

        <div class="bg-white border border-neutral-200 rounded-xl p-6 space-y-4">
            <div class="text-center">
                <p class="text-neutral-700 mb-4">
                    Avant de continuer, veuillez vérifier votre adresse email en cliquant sur le lien que nous vous avons envoyé à :
                </p>
                <p class="text-lg font-semibold text-primary-600 mb-6">{{ auth()->user()->email }}</p>
            </div>

            <div class="bg-neutral-50 border border-neutral-200 rounded-lg p-4 space-y-3">
                <p class="text-sm font-medium text-neutral-900 flex items-center gap-2">
                    <svg class="w-5 h-5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Vous n'avez pas reçu l'email ?
                </p>
                <ul class="text-sm text-neutral-600 space-y-2 ml-7">
                    <li>• Vérifiez votre dossier spam ou courrier indésirable</li>
                    <li>• Assurez-vous que l'adresse email est correcte</li>
                    <li>• Attendez quelques minutes, l'email peut prendre du temps à arriver</li>
                </ul>
            </div>

            <form method="POST" action="{{ route('verification.send') }}" class="space-y-4">
                @csrf
                <button 
                    type="submit" 
                    class="btn btn-primary w-full h-12"
                >
                    Renvoyer le lien de vérification
                </button>
            </form>
        </div>

        <!-- Logout -->
        <div class="mt-6 text-center">
            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit" class="text-sm text-neutral-600 hover:text-neutral-900 font-medium">
                    Se déconnecter
                </button>
            </form>
        </div>
    </div>
</x-layouts.auth>

