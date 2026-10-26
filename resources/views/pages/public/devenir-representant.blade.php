@php
    $subRate = rtrim(rtrim(number_format($rates['subscription_rate_percent'], 2, ',', ''), '0'), ',');
    $delRate = rtrim(rtrim(number_format($rates['delivery_rate_percent'], 2, ',', ''), '0'), ',');
    $field = 'w-full rounded-xl border px-4 py-3 text-sm focus:outline-none focus:ring-2';
    $fieldStyle = 'border-color:#E8E0D5;color:#1A1614;background:#fff;--tw-ring-color:rgba(255,97,0,.35)';
    $labelCls = 'block text-sm font-semibold mb-1.5';
@endphp
<x-layouts.public title="Devenir représentant MenuPro">

{{-- ══════════ HERO ══════════ --}}
<section class="font-grotesk py-20 sm:py-28" style="background:#FAF8F5">
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
        <span class="inline-flex items-center gap-2 text-xs font-semibold uppercase tracking-widest px-4 py-2 rounded-full mb-6" style="background:rgba(255,97,0,.1);color:#FF6100">
            🤝 Programme partenaires
        </span>
        <h1 class="font-display text-5xl sm:text-6xl font-normal leading-tight mb-6" style="color:#1A1614">
            Devenez représentant<br>
            <span style="color:#FF6100">MenuPro</span> dans votre ville
        </h1>
        <p class="text-xl leading-relaxed max-w-2xl mx-auto" style="color:#7C6F65">
            Faites découvrir MenuPro aux restaurants, maquis et stands de votre ville, accompagnez-les au quotidien
            et touchez une part de chaque abonnement et de chaque livraison, mois après mois.
        </p>
        <a href="#candidature" class="inline-flex items-center gap-2 mt-10 px-8 py-4 text-white font-semibold rounded-2xl transition hover:-translate-y-0.5" style="background:#FF6100;box-shadow:0 4px 20px rgba(255,97,0,.25)">
            Déposer ma candidature
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M17 8l4 4m0 0l-4 4m4-4H3"/></svg>
        </a>
    </div>
</section>

{{-- ══════════ RÉMUNÉRATION ══════════ --}}
<section class="font-grotesk py-16 bg-white" style="border-top:1px solid #E8E0D5;border-bottom:1px solid #E8E0D5">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8">
        <p class="text-center text-xs font-semibold uppercase tracking-widest mb-10" style="color:#FF6100">Votre rémunération</p>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div class="rounded-3xl p-8 border" style="border-color:#E8E0D5;background:#FAF8F5">
                <p class="font-display text-5xl" style="color:#FF6100">{{ $subRate }} %</p>
                <p class="font-semibold mt-3" style="color:#1A1614">des abonnements encaissés</p>
                <p class="text-sm leading-relaxed mt-2" style="color:#7C6F65">Sur chaque abonnement payé par les restaurants qui vous sont attribués, tant que le restaurant reste actif.</p>
            </div>
            <div class="rounded-3xl p-8 border" style="border-color:#E8E0D5;background:#FAF8F5">
                <p class="font-display text-5xl" style="color:#FF6100">{{ $delRate }} %</p>
                <p class="font-semibold mt-3" style="color:#1A1614">de la commission MenuPro sur les livraisons</p>
                <p class="text-sm leading-relaxed mt-2" style="color:#7C6F65">Sur les commandes livrées via l'app client par les restaurants de votre ville qui vous sont attribués.</p>
            </div>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mt-6">
            @foreach([
                ['🏙️', 'Exclusivité de la ville', 'Objectifs atteints ? Votre ville devient votre territoire exclusif : tous ses restaurants vous sont attribués.'],
                ['🔒', 'Aucune manipulation d\'argent', 'Les restaurants paient MenuPro directement (Jeko). Vous ne collectez jamais d\'argent.'],
                ['📊', 'Tout est transparent', 'Votre espace affiche vos restaurants, les encaissements et votre part, calculée chaque mois.'],
            ] as [$icon, $title, $text])
                <div class="rounded-2xl p-6 border" style="border-color:#E8E0D5">
                    <span class="text-2xl block mb-3">{{ $icon }}</span>
                    <p class="font-semibold mb-1" style="color:#1A1614">{{ $title }}</p>
                    <p class="text-sm leading-relaxed" style="color:#7C6F65">{{ $text }}</p>
                </div>
            @endforeach
        </div>
    </div>
</section>

{{-- ══════════ RÔLE + CONDITIONS ══════════ --}}
<section class="font-grotesk py-16" style="background:#FAF8F5">
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 grid grid-cols-1 md:grid-cols-2 gap-10">
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest mb-4" style="color:#FF6100">Votre rôle</p>
            <ul class="space-y-3 text-sm leading-relaxed" style="color:#3D3530">
                <li>✔ Présenter MenuPro aux restaurants, maquis, hôtels et stands de votre ville.</li>
                <li>✔ Les inscrire avec votre lien de parrainage personnel.</li>
                <li>✔ Les accompagner : prise en main du menu QR, des commandes et de la livraison.</li>
                <li>✔ Faire remonter les besoins du terrain à l'équipe MenuPro.</li>
            </ul>
        </div>
        <div>
            <p class="text-xs font-semibold uppercase tracking-widest mb-4" style="color:#FF6100">Conditions d'entrée</p>
            <ul class="space-y-3 text-sm leading-relaxed" style="color:#3D3530">
                <li>• Être majeur et résider dans la ville que vous souhaitez représenter.</li>
                <li>• Avoir un bon réseau local (restauration, commerce, associations…).</li>
                <li>• Disposer d'un smartphone et d'un numéro WhatsApp actif.</li>
                <li>• Période d'essai de {{ $rates['trial_months'] }} mois, avec des objectifs de restaurants payants par trimestre.</li>
                <li>• Un seul représentant par ville : les candidatures sont étudiées dans l'ordre d'arrivée.</li>
            </ul>
        </div>
    </div>
</section>

{{-- ══════════ FORMULAIRE ══════════ --}}
<section id="candidature" class="font-grotesk py-20 bg-white" style="border-top:1px solid #E8E0D5">
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="text-center mb-10">
            <h2 class="font-display text-3xl sm:text-4xl font-normal mb-4" style="color:#1A1614">Déposer ma candidature</h2>
            <p class="text-base" style="color:#7C6F65">Notre équipe étudie chaque dossier et vous recontacte sur WhatsApp pour un entretien.</p>
        </div>

        @if(session('application_sent'))
            <div class="rounded-2xl p-6 border text-center mb-8" style="background:#f0fdf4;border-color:#86efac;color:#166534" role="status">
                <p class="font-semibold">Merci, votre candidature a bien été envoyée !</p>
                <p class="text-sm mt-1">Nous vous recontactons sous quelques jours.</p>
            </div>
        @endif

        @if($errors->any())
            <div class="rounded-2xl p-4 border text-sm mb-6" style="background:#fef2f2;border-color:#fca5a5;color:#991b1b" role="alert">
                <p class="font-semibold mb-1">Merci de corriger les points suivants :</p>
                <ul class="list-disc pl-5 space-y-0.5">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('representative.apply') }}" enctype="multipart/form-data"
              class="rounded-3xl border p-6 sm:p-8 space-y-5" style="border-color:#E8E0D5;box-shadow:0 8px 40px rgba(26,22,20,.06)">
            @csrf

            {{-- Champ piège anti-robots : invisible pour les humains --}}
            <div aria-hidden="true" style="position:absolute;left:-10000px;top:auto;width:1px;height:1px;overflow:hidden">
                <label for="website">Site web</label>
                <input type="text" id="website" name="website" value="" tabindex="-1" autocomplete="off">
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                <div>
                    <label for="full_name" class="{{ $labelCls }}" style="color:#1A1614">Nom complet *</label>
                    <input id="full_name" name="full_name" type="text" required maxlength="120" value="{{ old('full_name') }}" class="{{ $field }}" style="{{ $fieldStyle }}">
                </div>
                <div>
                    <label for="phone" class="{{ $labelCls }}" style="color:#1A1614">Téléphone (WhatsApp) *</label>
                    <input id="phone" name="phone" type="tel" required maxlength="30" value="{{ old('phone') }}" placeholder="+225 07 00 00 00 00" class="{{ $field }}" style="{{ $fieldStyle }}">
                </div>
                <div>
                    <label for="email" class="{{ $labelCls }}" style="color:#1A1614">E-mail</label>
                    <input id="email" name="email" type="email" maxlength="150" value="{{ old('email') }}" class="{{ $field }}" style="{{ $fieldStyle }}">
                </div>
                <div>
                    <label for="city" class="{{ $labelCls }}" style="color:#1A1614">Ville à représenter *</label>
                    <input id="city" name="city" type="text" required maxlength="80" list="rep-cities" value="{{ old('city') }}" placeholder="Ex. Daloa" class="{{ $field }}" style="{{ $fieldStyle }}">
                    <datalist id="rep-cities">
                        @foreach($cities as $c)<option value="{{ $c }}">@endforeach
                    </datalist>
                </div>
                <div>
                    <label for="legal_status" class="{{ $labelCls }}" style="color:#1A1614">Statut légal *</label>
                    <select id="legal_status" name="legal_status" required class="{{ $field }}" style="{{ $fieldStyle }}">
                        <option value="">Choisir…</option>
                        @foreach(\App\Models\PartnerApplication::LEGAL_STATUS_LABELS as $value => $label)
                            <option value="{{ $value }}" @selected(old('legal_status') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="availability" class="{{ $labelCls }}" style="color:#1A1614">Disponibilité *</label>
                    <select id="availability" name="availability" required class="{{ $field }}" style="{{ $fieldStyle }}">
                        <option value="">Choisir…</option>
                        @foreach(\App\Models\PartnerApplication::AVAILABILITY_LABELS as $value => $label)
                            <option value="{{ $value }}" @selected(old('availability') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label for="experience" class="{{ $labelCls }}" style="color:#1A1614">Expérience et réseau *</label>
                <textarea id="experience" name="experience" rows="4" required maxlength="3000" placeholder="Vente, restauration, commerce… Quels restaurants ou réseaux connaissez-vous dans votre ville ?" class="{{ $field }}" style="{{ $fieldStyle }}">{{ old('experience') }}</textarea>
            </div>

            <div>
                <label for="motivation" class="{{ $labelCls }}" style="color:#1A1614">Motivation *</label>
                <textarea id="motivation" name="motivation" rows="4" required maxlength="3000" placeholder="Pourquoi souhaitez-vous représenter MenuPro ?" class="{{ $field }}" style="{{ $fieldStyle }}">{{ old('motivation') }}</textarea>
            </div>

            <div>
                <label for="attachment" class="{{ $labelCls }}" style="color:#1A1614">Pièce jointe (facultatif)</label>
                <input id="attachment" name="attachment" type="file" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" class="block w-full text-sm" style="color:#7C6F65">
                <p class="text-xs mt-1" style="color:#B5A99A">CV ou présentation — PDF, Word, JPG ou PNG, 5 Mo maximum.</p>
            </div>

            <label class="flex items-start gap-3 text-sm" style="color:#3D3530">
                <input type="checkbox" name="accept_terms" value="1" required class="mt-1" @checked(old('accept_terms'))>
                <span>J'accepte que MenuPro utilise ces informations pour étudier ma candidature et me recontacter.
                    <a href="{{ route('privacy') }}" class="underline">Politique de confidentialité</a></span>
            </label>

            <button type="submit" class="w-full inline-flex items-center justify-center gap-2 px-8 py-4 text-white font-semibold rounded-2xl transition hover:-translate-y-0.5" style="background:#FF6100;box-shadow:0 4px 20px rgba(255,97,0,.25)">
                Envoyer ma candidature
            </button>
        </form>
    </div>
</section>

</x-layouts.public>
