<x-layouts.public>
    <div class="min-h-[70vh] flex items-center justify-center px-4">
        <div class="max-w-md text-center bg-white rounded-2xl border border-neutral-200 p-8 shadow-sm">
            <div class="w-14 h-14 mx-auto mb-4 rounded-full bg-amber-100 flex items-center justify-center">
                <svg class="w-7 h-7 text-amber-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <h1 class="text-xl font-bold text-neutral-900 mb-2">Inscription reçue</h1>
            <p class="text-sm text-neutral-600 mb-6">
                Votre restaurant est <strong>en attente de validation</strong> par notre équipe.
                Vous recevrez une notification dès qu'il sera activé. Votre essai gratuit démarrera à ce moment-là.
            </p>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="text-sm font-semibold text-primary-600 hover:text-primary-700">Se déconnecter</button>
            </form>
        </div>
    </div>
</x-layouts.public>
