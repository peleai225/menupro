<x-layouts.public title="Devenir livreur — MenuPro">

{{-- ══════════ HERO ══════════ --}}
<section class="font-grotesk py-20 sm:py-28" style="background:#FAF8F5">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <span class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-widest px-4 py-2 rounded-full mb-6" style="background:rgba(255,97,0,.1);color:#FF6100">
            🏍️ Rejoignez l'équipe MenuPro
        </span>
        <h1 class="font-display text-5xl sm:text-6xl font-normal leading-tight mb-6" style="color:#1A1614">
            Livrez avec<br>
            <span style="color:#FF6100">MenuPro</span>
        </h1>
        <p class="text-xl leading-relaxed max-w-2xl mx-auto" style="color:#7C6F65">
            Devenez livreur indépendant. Recevez des courses, gérez votre temps et soyez payé directement sur Wave ou Orange Money.
        </p>

        {{-- Avantages --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-12 text-left">
            @foreach([
                ['🕐', 'Horaires libres', 'Travaillez quand vous voulez. Vous êtes votre propre patron.'],
                ['💸', 'Paiement rapide', 'Vos gains virés sur demande via Wave ou Orange Money.'],
                ['📍', 'Courses proches', 'Recevez uniquement les commandes dans votre zone.'],
            ] as $a)
            <div class="bg-white rounded-2xl p-6 border" style="border-color:#E8E0D5;box-shadow:0 4px 20px rgba(26,22,20,.04)">
                <span class="text-2xl mb-3 block">{{ $a[0] }}</span>
                <p class="font-semibold mb-1" style="color:#1A1614">{{ $a[1] }}</p>
                <p class="text-sm leading-relaxed" style="color:#7C6F65">{{ $a[2] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ══════════ PROCESSUS ══════════ --}}
<section class="font-grotesk py-16 bg-white" style="border-top:1px solid #E8E0D5;border-bottom:1px solid #E8E0D5">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8">
        <p class="text-center text-xs font-semibold uppercase tracking-widest mb-10" style="color:#FF6100">Comment ça marche</p>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-8 text-center">
            @foreach([
                ['01', 'Remplissez le formulaire', 'Quelques infos sur vous et votre véhicule. Moins de 3 minutes.'],
                ['02', 'Validation de votre dossier', 'Notre équipe examine votre candidature sous 24–48h.'],
                ['03', 'Recevez vos accès', 'Vos identifiants vous sont envoyés sur WhatsApp. Prêt à livrer !'],
            ] as $s)
            <div class="flex flex-col items-center gap-3">
                <div class="w-12 h-12 rounded-2xl flex items-center justify-center font-bold text-lg" style="background:rgba(255,97,0,.1);color:#FF6100">{{ $s[0] }}</div>
                <p class="font-semibold" style="color:#1A1614">{{ $s[1] }}</p>
                <p class="text-sm leading-relaxed" style="color:#7C6F65">{{ $s[2] }}</p>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ══════════ FORMULAIRE ══════════ --}}
<section class="font-grotesk py-20" style="background:#FAF8F5">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10">
            <h2 class="font-display text-3xl sm:text-4xl font-normal mb-4" style="color:#1A1614">Postuler maintenant</h2>
            <p class="text-base" style="color:#7C6F65">Remplissez le formulaire ci-dessous. Notre équipe vous contactera sous 48h.</p>
        </div>

        <div class="bg-white rounded-3xl overflow-hidden border" style="border-color:#E8E0D5;box-shadow:0 8px 40px rgba(26,22,20,.06)">
            <div class="px-8 pt-8 pb-2 border-b" style="border-color:#F0EBE5">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl flex items-center justify-center" style="background:rgba(255,97,0,.1)">
                        <svg class="w-5 h-5" style="color:#FF6100" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="font-semibold text-sm" style="color:#1A1614">Formulaire de candidature livreur</p>
                        <p class="text-xs" style="color:#7C6F65">Toutes les informations sont traitées de façon confidentielle</p>
                    </div>
                </div>
            </div>
            <div class="p-2">
                <iframe
                    class="airtable-embed w-full"
                    src="https://airtable.com/embed/appS90crztrYcVrO7/pagm2kSJO8utW9hSA/form"
                    frameborder="0"
                    onmousewheel=""
                    style="height:640px;background:transparent;border:none">
                </iframe>
            </div>
        </div>

        <p class="text-center text-xs mt-6" style="color:#B5A99A">
            En soumettant ce formulaire, vous acceptez que vos informations soient utilisées pour traiter votre candidature.
            <a href="{{ route('privacy') }}" class="underline hover:opacity-70 transition-opacity">Politique de confidentialité</a>
        </p>
    </div>
</section>

{{-- ══════════ FAQ MINI ══════════ --}}
<section class="font-grotesk py-20 bg-white" style="border-top:1px solid #E8E0D5">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8">
        <p class="text-center text-xs font-semibold uppercase tracking-widest mb-10" style="color:#FF6100">Questions fréquentes</p>
        <div class="space-y-4" x-data="{ open: null }">
            @foreach([
                ['Quels sont les critères pour devenir livreur ?', 'Vous devez être majeur, disposer d\'un véhicule (moto, vélo ou voiture), avoir une pièce d\'identité valide et être disponible dans l\'une de nos zones de livraison.'],
                ['Combien puis-je gagner par course ?', 'La rémunération varie selon la distance. Vous êtes payé à chaque course effectuée, sans abonnement ni frais.'],
                ['Combien de temps prend la validation du dossier ?', 'Notre équipe traite les candidatures sous 24 à 48 heures ouvrables. Vous recevrez une réponse sur WhatsApp.'],
                ['Comment recevrai-je mes gains ?', 'Vos gains sont disponibles dans l\'app livreur. Vous pouvez demander un virement à tout moment vers votre compte Wave ou Orange Money.'],
            ] as $i => $q)
            <div class="border rounded-2xl overflow-hidden" style="border-color:#E8E0D5" x-data>
                <button
                    class="w-full flex items-center justify-between px-6 py-5 text-left transition-colors hover:bg-neutral-50"
                    @click="open === {{ $i }} ? open = null : open = {{ $i }}"
                >
                    <span class="font-semibold text-sm" style="color:#1A1614">{{ $q[0] }}</span>
                    <svg class="w-5 h-5 shrink-0 transition-transform" :class="open === {{ $i }} ? 'rotate-180' : ''" style="color:#7C6F65" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="open === {{ $i }}" x-collapse class="px-6 pb-5">
                    <p class="text-sm leading-relaxed" style="color:#7C6F65">{{ $q[1] }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ══════════ CTA FINAL ══════════ --}}
<section class="font-grotesk py-20" style="background:#1A1614">
    <div class="max-w-2xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <h2 class="font-display text-3xl sm:text-4xl font-normal mb-4 text-white">Prêt à commencer ?</h2>
        <p class="text-lg mb-8" style="color:#9A8E85">Rejoignez les livreurs MenuPro et gagnez votre indépendance.</p>
        <a href="#" onclick="document.querySelector('.airtable-embed').scrollIntoView({behavior:'smooth'});return false;"
           class="inline-flex items-center gap-2 px-8 py-4 text-white font-semibold rounded-2xl transition hover:-translate-y-0.5"
           style="background:#FF6100;box-shadow:0 4px 20px rgba(255,97,0,.35)">
            🏍️ Postuler maintenant
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
        </a>
    </div>
</section>

</x-layouts.public>
