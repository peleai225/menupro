<x-layouts.app title="Inscription enregistrée">
    <div class="min-h-screen flex items-center justify-center p-4 bg-[#0f172a]">
        <div class="max-w-md w-full bg-slate-800/50 border border-slate-700 rounded-2xl p-8 text-center">
            <div class="w-14 h-14 rounded-full bg-green-500/20 flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
            </div>
            <h1 class="text-xl font-bold text-white mb-2">Dossier reçu</h1>
            <p class="text-slate-400 text-sm mb-6">
                Votre inscription est en cours de vérification. Nous vous contacterons par WhatsApp dès que votre compte sera validé.
            </p>
            <a href="{{ url('/') }}" class="inline-block px-6 py-3 rounded-xl font-semibold bg-orange-500 hover:bg-orange-600 text-white">
                Retour à l'accueil
            </a>
        </div>
    </div>
</x-layouts.app>
