<x-layouts.crm title="Inscription réussie">
    <div class="min-h-[70vh] flex items-center justify-center px-4">
        <div class="w-full max-w-lg">

            {{-- Icône de succès --}}
            <div class="flex justify-center mb-6">
                <div class="relative">
                    <div class="w-20 h-20 rounded-full bg-orange-500/15 flex items-center justify-center">
                        <svg class="w-10 h-10 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <span class="absolute -top-1 -right-1 flex h-5 w-5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-orange-400 opacity-50"></span>
                        <span class="relative inline-flex rounded-full h-5 w-5 bg-orange-500 items-center justify-center">
                            <svg class="w-3 h-3 text-white" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                            </svg>
                        </span>
                    </span>
                </div>
            </div>

            {{-- Titre --}}
            <div class="text-center mb-8">
                <h1 class="text-2xl font-bold text-white mb-2">
                    Bienvenue chez les Ambassadeurs MenuPro&nbsp;!
                </h1>
                @auth
                    <p class="text-orange-400 font-semibold text-lg">{{ auth()->user()->name }}</p>
                @endauth
                <p class="text-gray-400 text-sm mt-3 leading-relaxed">
                    Votre compte a été créé avec succès. Notre équipe va examiner votre dossier
                    et vous donner accès à votre espace ambassador dans les plus brefs délais.
                </p>
            </div>

            {{-- Card statut --}}
            <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 mb-6 space-y-4">
                <h2 class="text-sm font-semibold text-gray-400 uppercase tracking-wider mb-4">
                    Prochaines étapes
                </h2>

                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-8 h-8 rounded-full bg-amber-500/15 flex items-center justify-center mt-0.5">
                        <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-white text-sm font-medium">Vérification en cours</p>
                        <p class="text-gray-500 text-xs mt-0.5">Votre dossier est en attente de validation par l'équipe MenuPro.</p>
                    </div>
                </div>

                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-8 h-8 rounded-full bg-sky-500/15 flex items-center justify-center mt-0.5">
                        <svg class="w-4 h-4 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-white text-sm font-medium">Notification WhatsApp</p>
                        <p class="text-gray-500 text-xs mt-0.5">Vous serez contacté sur votre numéro WhatsApp dès la validation.</p>
                    </div>
                </div>

                <div class="flex items-start gap-4">
                    <div class="flex-shrink-0 w-8 h-8 rounded-full bg-emerald-500/15 flex items-center justify-center mt-0.5">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-white text-sm font-medium">Commissions & Badge</p>
                        <p class="text-gray-500 text-xs mt-0.5">Après validation, commencez à parrainer des restaurants et gagner des commissions.</p>
                    </div>
                </div>
            </div>

            {{-- Badge statut --}}
            <div class="flex items-center justify-center gap-2 mb-8">
                <span class="inline-flex items-center gap-1.5 px-3 py-1.5 rounded-full bg-amber-500/10 border border-amber-500/30 text-amber-400 text-xs font-medium">
                    <span class="w-1.5 h-1.5 rounded-full bg-amber-400 animate-pulse"></span>
                    Compte en attente de vérification
                </span>
            </div>

            {{-- CTA --}}
            <div class="flex flex-col sm:flex-row gap-3">
                <a href="/crm"
                   class="flex-1 flex items-center justify-center gap-2 h-12 rounded-xl font-bold bg-orange-500 hover:bg-orange-600 active:bg-orange-700 text-white text-sm transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                    </svg>
                    Accéder à mon espace
                </a>
            </div>

        </div>
    </div>
</x-layouts.crm>
