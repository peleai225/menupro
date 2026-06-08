<x-layouts.app title="Inscription envoyée">
    <div class="min-h-screen flex items-center justify-center p-6 bg-[#0f172a]">
        <div class="w-full max-w-sm">
            <div class="text-center mb-8">
                <div class="w-16 h-16 rounded-2xl bg-emerald-500/15 flex items-center justify-center mx-auto mb-5">
                    <svg class="w-8 h-8 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <h1 class="text-2xl font-bold text-white mb-2">Compte créé !</h1>
                <p class="text-slate-400 text-sm leading-relaxed">
                    L'équipe MenuPro va activer votre compte sous 24h.<br>
                    Vous serez contacté sur votre WhatsApp.
                </p>
            </div>

            <div class="space-y-3 mb-8">
                <div class="flex items-center gap-3 px-4 py-3 rounded-2xl border border-slate-700/60 bg-slate-800/40">
                    <div class="w-8 h-8 rounded-xl bg-orange-500/15 flex items-center justify-center shrink-0">
                        <span class="text-orange-400 font-bold text-sm">1</span>
                    </div>
                    <p class="text-slate-300 text-sm">Votre dossier est en cours de vérification</p>
                </div>
                <div class="flex items-center gap-3 px-4 py-3 rounded-2xl border border-slate-700/60 bg-slate-800/40">
                    <div class="w-8 h-8 rounded-xl bg-orange-500/15 flex items-center justify-center shrink-0">
                        <span class="text-orange-400 font-bold text-sm">2</span>
                    </div>
                    <p class="text-slate-300 text-sm">Activation sous 24h · notification WhatsApp</p>
                </div>
                <div class="flex items-center gap-3 px-4 py-3 rounded-2xl border border-slate-700/60 bg-slate-800/40">
                    <div class="w-8 h-8 rounded-xl bg-emerald-500/15 flex items-center justify-center shrink-0">
                        <span class="text-emerald-400 font-bold text-sm">3</span>
                    </div>
                    <p class="text-slate-300 text-sm">Parrainez des restaurants, gagnez des commissions</p>
                </div>
            </div>

            <a href="{{ route('commando.dashboard') }}"
               class="flex items-center justify-center gap-2 w-full h-12 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-bold text-sm transition">
                Aller à mon espace
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
    </div>
</x-layouts.app>
