<x-layouts.app title="Inscription enregistrée">
    <div class="min-h-screen flex items-center justify-center p-4 bg-[#0f172a] relative overflow-hidden">
        {{-- Fond décoratif --}}
        <div class="absolute inset-0 pointer-events-none" aria-hidden="true">
            <div class="absolute top-1/4 left-1/2 -translate-x-1/2 w-[500px] h-[500px] bg-emerald-500/5 rounded-full blur-3xl"></div>
        </div>

        <div class="relative max-w-md w-full">
            {{-- Indicateur d'étapes --}}
            <div class="flex items-center justify-center gap-3 mb-8">
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-full bg-emerald-500 text-white text-sm font-bold flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                    </span>
                    <span class="text-sm font-medium text-emerald-400 hidden sm:inline">Compte</span>
                </div>
                <div class="w-8 sm:w-12 h-px bg-emerald-500/50"></div>
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-full bg-emerald-500 text-white text-sm font-bold flex items-center justify-center">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                    </span>
                    <span class="text-sm font-medium text-emerald-400 hidden sm:inline">Vérification</span>
                </div>
                <div class="w-8 sm:w-12 h-px bg-emerald-500/50"></div>
                <div class="flex items-center gap-2">
                    <span class="w-8 h-8 rounded-full bg-orange-500 text-white text-sm font-bold flex items-center justify-center animate-pulse">3</span>
                    <span class="text-sm font-medium text-orange-400 hidden sm:inline">Validation</span>
                </div>
            </div>

            <div class="bg-slate-800/60 border border-slate-700/50 rounded-2xl p-8 text-center shadow-2xl backdrop-blur-sm">
                <div class="w-16 h-16 rounded-2xl bg-emerald-500/15 flex items-center justify-center mx-auto mb-5 border border-emerald-500/20">
                    <svg class="w-8 h-8 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-white mb-2">Dossier envoy&eacute; avec succ&egrave;s</h1>
                <p class="text-slate-400 text-sm mb-6 leading-relaxed">
                    Votre inscription est en cours de v&eacute;rification par notre &eacute;quipe.<br>
                    Nous vous contacterons par <span class="text-emerald-400 font-medium">WhatsApp</span> d&egrave;s que votre compte sera valid&eacute;.
                </p>

                <div class="bg-slate-900/60 rounded-xl p-4 mb-6 border border-slate-700/50">
                    <div class="flex items-center gap-3 text-left">
                        <div class="w-10 h-10 rounded-xl bg-orange-500/15 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        </div>
                        <div>
                            <p class="text-white text-sm font-medium">D&eacute;lai de validation</p>
                            <p class="text-slate-400 text-xs">G&eacute;n&eacute;ralement sous 24 &agrave; 48 heures ouvr&eacute;es</p>
                        </div>
                    </div>
                </div>

                <a href="{{ url('/') }}" class="inline-flex items-center justify-center gap-2 px-6 py-3 rounded-xl font-semibold bg-orange-500 hover:bg-orange-600 text-white transition-all">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
                    Retour &agrave; l'accueil
                </a>
            </div>
        </div>
    </div>
</x-layouts.app>
