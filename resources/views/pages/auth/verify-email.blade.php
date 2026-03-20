<x-layouts.auth title="Vérification de l'email">
    <div class="animate-fade-in">
        <!-- Header -->
        <div class="text-center lg:text-left mb-8 sm:mb-10">
            <div class="inline-flex items-center gap-2 px-3 py-1.5 bg-blue-50 text-blue-600 rounded-full text-xs font-semibold mb-4 tracking-wide uppercase">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                </svg>
                Vérification
            </div>
            <h1 class="text-2xl sm:text-3xl font-bold text-neutral-900 tracking-tight">Vérifiez votre email</h1>
            <p class="text-neutral-500 mt-2 text-sm sm:text-base">Un lien de vérification a été envoyé à votre adresse email.</p>
        </div>

        @if (session('status') === 'verification-link-sent')
            <div class="mb-6 p-4 bg-secondary-50 border border-secondary-200 rounded-2xl flex items-start gap-3">
                <div class="w-8 h-8 bg-secondary-100 rounded-full flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-secondary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <div class="pt-0.5">
                    <p class="text-sm font-semibold text-secondary-700">Nouveau lien envoyé !</p>
                    <p class="text-xs text-secondary-600 mt-0.5">Un nouveau lien de vérification a été envoyé à <strong>{{ auth()->user()->email }}</strong></p>
                </div>
            </div>
        @endif

        @if (session('warning'))
            <div class="mb-6 p-4 bg-amber-50 border border-amber-200 rounded-2xl flex items-start gap-3">
                <div class="w-8 h-8 bg-amber-100 rounded-full flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <p class="text-sm text-amber-700 pt-1">{{ session('warning') }}</p>
            </div>
        @endif

        <!-- Email Display Card -->
        <div class="bg-neutral-50 border border-neutral-200 rounded-2xl p-6 mb-6">
            <p class="text-neutral-700 text-center text-sm mb-3">
                Veuillez vérifier votre email en cliquant sur le lien envoyé à :
            </p>
            <p class="text-center">
                <span class="inline-flex items-center gap-2 px-4 py-2 bg-primary-50 text-primary-700 rounded-xl font-semibold text-sm">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    {{ auth()->user()->email }}
                </span>
            </p>
        </div>

        <!-- Tips -->
        <div class="bg-neutral-50 border border-neutral-200 rounded-2xl p-5 mb-6">
            <p class="text-sm font-semibold text-neutral-800 flex items-center gap-2 mb-3">
                <svg class="w-4.5 h-4.5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Vous n'avez pas reçu l'email ?
            </p>
            <ul class="text-sm text-neutral-600 space-y-2 ml-6.5">
                <li class="flex items-start gap-2">
                    <span class="text-neutral-400 mt-0.5">-</span>
                    Vérifiez votre dossier spam ou courrier indésirable
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-neutral-400 mt-0.5">-</span>
                    Assurez-vous que l'adresse email est correcte
                </li>
                <li class="flex items-start gap-2">
                    <span class="text-neutral-400 mt-0.5">-</span>
                    Attendez quelques minutes, l'email peut prendre du temps
                </li>
            </ul>
        </div>

        <!-- Resend -->
        <form method="POST" action="{{ route('verification.send') }}">
            @csrf
            <button
                type="submit"
                class="btn btn-primary w-full h-13 text-base font-semibold shadow-lg shadow-primary-500/20"
            >
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Renvoyer le lien de vérification
            </button>
        </form>

        <!-- Logout -->
        <div class="mt-6 text-center">
            <form method="POST" action="{{ route('logout') }}" class="inline">
                @csrf
                <button type="submit" class="text-sm text-neutral-500 hover:text-neutral-700 font-medium transition-colors">
                    Se déconnecter
                </button>
            </form>
        </div>
    </div>
</x-layouts.auth>
