<x-layouts.public>
    <div class="min-h-[70vh] flex items-center justify-center px-4">
        <div class="max-w-md text-center bg-white rounded-2xl border border-neutral-200 p-8 shadow-sm">
            <div class="w-14 h-14 mx-auto mb-4 rounded-full bg-neutral-100 flex items-center justify-center">
                <svg class="w-7 h-7 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 10-8 0v4h8z"/></svg>
            </div>
            <h1 class="text-xl font-bold text-neutral-900 mb-2">Aucun accès attribué</h1>
            <p class="text-sm text-neutral-600 mb-6">
                Votre compte n'a encore accès à aucune section du back-office.
                Contactez votre administrateur pour qu'il vous attribue des droits.
            </p>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button class="text-sm font-semibold text-primary-600 hover:text-primary-700">Se déconnecter</button>
            </form>
        </div>
    </div>
</x-layouts.public>
