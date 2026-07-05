<x-layouts.app title="Inscription envoyée">
    <div class="min-h-screen flex items-center justify-center p-6 bg-[#0f172a]">
        <div class="w-full max-w-sm">
            <div class="text-center mb-8">
                <div class="w-16 h-16 rounded-2xl bg-emerald-500/15 flex items-center justify-center mx-auto mb-5">
                    <svg class="w-8 h-8 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-white mb-2">Inscription reçue !</h1>
                <p class="text-slate-400 text-sm leading-relaxed">
                    Votre dossier a bien été reçu.<br>
                    L'équipe MenuPro va vérifier votre identité et activer votre compte sous 24h.<br>
                    Vous pourrez ensuite vous connecter avec votre mot de passe.
                </p>
            </div>

            <div class="space-y-3 mb-8">
                <div class="flex items-center gap-3 px-4 py-3 rounded-2xl border border-emerald-500/20 bg-emerald-500/5">
                    <div class="w-8 h-8 rounded-xl bg-emerald-500/15 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </div>
                    <p class="text-slate-300 text-sm">Inscription enregistrée</p>
                </div>
                <div class="flex items-center gap-3 px-4 py-3 rounded-2xl border border-slate-700/60 bg-slate-800/40">
                    <div class="w-8 h-8 rounded-xl bg-orange-500/15 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-orange-400 animate-pulse" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <p class="text-slate-300 text-sm">Activation sous 24h par l'équipe</p>
                </div>
                <div class="flex items-center gap-3 px-4 py-3 rounded-2xl border border-slate-700/60 bg-slate-800/40">
                    <div class="w-8 h-8 rounded-xl bg-slate-700/60 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/></svg>
                    </div>
                    <p class="text-slate-300 text-sm">Connexion avec votre mot de passe après validation</p>
                </div>
                <div class="flex items-center gap-3 px-4 py-3 rounded-2xl border border-slate-700/60 bg-slate-800/40">
                    <div class="w-8 h-8 rounded-xl bg-slate-700/60 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-slate-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </div>
                    <p class="text-slate-300 text-sm">Parrainez des restaurants, gagnez des commissions</p>
                </div>
            </div>

            <a href="{{ url('/') }}"
               class="flex items-center justify-center gap-2 w-full h-12 rounded-xl border border-slate-700 text-slate-300 hover:bg-slate-800 font-medium text-sm transition">
                Retour à l'accueil
            </a>
        </div>
    </div>
</x-layouts.app>
