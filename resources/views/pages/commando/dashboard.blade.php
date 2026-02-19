<x-layouts.app title="Mon espace Agent">
    <div class="min-h-screen bg-[#0f172a] text-neutral-100">
        <header class="border-b border-sky-900/50 bg-slate-800/50 backdrop-blur">
            <div class="max-w-2xl mx-auto px-4 py-4 flex items-center justify-between">
                <span class="font-bold text-lg">
                    <span class="text-white">Menu</span><span class="text-orange-500">Pro</span> Commando
                </span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="text-sm text-slate-400 hover:text-white">Déconnexion</button>
                </form>
            </div>
        </header>

        <main class="max-w-2xl mx-auto px-4 py-8">
            @if(session('success'))
                <div class="mb-4 p-4 bg-green-500/20 border border-green-500/50 rounded-xl text-green-400 text-sm">
                    {{ session('success') }}
                </div>
            @endif

            <div class="mb-6">
                <h1 class="text-xl font-bold text-white">Bonjour {{ $agent->first_name }}</h1>
                <p class="text-slate-400 text-sm mt-1">Statut : {{ $agent->status_verification->label() }}</p>
            </div>

            @if($agent->status_verification->value === 'shadow')
                @livewire('commando.complete-profile')
            @endif

            @if($agent->canAccessParrainage())
                <section class="bg-slate-800/50 border border-slate-700 rounded-2xl p-6 mb-6">
                    <h2 class="font-semibold text-white mb-2">Lien de parrainage</h2>
                    <p class="text-slate-400 text-sm mb-3">Partagez ce lien pour inviter des restaurateurs à s'inscrire sur MenuPro.</p>
                    <div class="flex gap-2">
                        <input type="text" readonly value="{{ $agent->parrainage_url }}"
                               class="flex-1 rounded-xl border border-slate-600 bg-slate-900 text-slate-300 text-sm px-4 py-2">
                        <button type="button" onclick="navigator.clipboard.writeText('{{ $agent->parrainage_url }}'); alert('Lien copié !');"
                                class="px-4 py-2 rounded-xl bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium">
                            Copier
                        </button>
                    </div>
                </section>
                <section class="bg-slate-800/50 border border-slate-700 rounded-2xl p-6">
                    <h2 class="font-semibold text-white mb-2">Ma carte agent</h2>
                    <p class="text-slate-400 text-sm mb-4">Téléchargez ou affichez votre carte digitale pour la montrer aux restaurateurs.</p>
                    <a href="{{ route('commando.card') }}" target="_blank" rel="noopener"
                       class="inline-flex items-center gap-2 px-4 py-3 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-medium">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                        Voir / Télécharger ma carte
                    </a>
                </section>
            @elseif($agent->status_verification->value !== 'shadow')
                <section class="bg-amber-500/10 border border-amber-500/30 rounded-2xl p-6">
                    <p class="text-amber-200 text-sm">
                        Votre dossier est en cours de vérification. Vous pourrez accéder à votre lien de parrainage et à votre carte agent une fois votre compte validé par l'équipe.
                    </p>
                </section>
            @endif
        </main>
    </div>
</x-layouts.app>
