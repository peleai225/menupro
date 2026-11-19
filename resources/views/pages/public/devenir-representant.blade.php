@php
    $subRate = rtrim(rtrim(number_format($rates['subscription_rate_percent'], 2, ',', ''), '0'), ',');
    $delRate = rtrim(rtrim(number_format($rates['delivery_rate_percent'], 2, ',', ''), '0'), ',');
@endphp
<x-layouts.public title="Devenir représentant MenuPro" description="Représentez MenuPro dans votre ville : présentez-le aux restaurants, maquis, stands et boutiques, et touchez une part de chaque abonnement et de chaque livraison.">

{{-- ══════════ EN-TÊTE ══════════ --}}
<section class="vt-pagehead">
    <div class="vt-wrap max-w-4xl">
        <h1 class="vt-h1">Devenez représentant MenuPro dans votre ville</h1>
        <p class="vt-lead mt-5">Faites découvrir MenuPro aux restaurants, maquis, stands et boutiques de votre ville, accompagnez-les au quotidien et touchez une part de chaque abonnement et de chaque livraison, mois après mois.</p>
        <div class="mt-8 grid gap-3 sm:flex sm:justify-center">
            <a href="#candidature" class="vt-btn vt-btn-dark">Déposer ma candidature</a>
            <a href="#remuneration" class="vt-btn vt-btn-ghost">Voir la rémunération</a>
        </div>
    </div>
</section>

{{-- ══════════ RÉMUNÉRATION ══════════ --}}
<section id="remuneration" class="vt-section pt-4 md:pt-6 scroll-mt-16">
    <div class="vt-wrap max-w-5xl">
        <h2 class="vt-h2 text-center">Votre rémunération</h2>
        <div class="mt-8 grid gap-5 md:grid-cols-2">
            <div class="vt-soft p-6 md:p-8">
                <p class="vt-display text-5xl md:text-6xl vt-num text-mp-ink">{{ $subRate }} %</p>
                <p class="mt-3 font-semibold text-lg">des abonnements encaissés</p>
                <p class="mt-1 vt-muted">Sur chaque abonnement payé par les commerces qui vous sont attribués, tant que le commerce reste actif.</p>
            </div>
            <div class="vt-soft p-6 md:p-8">
                <p class="vt-display text-5xl md:text-6xl vt-num text-mp-ink">{{ $delRate }} %</p>
                <p class="mt-3 font-semibold text-lg">de la commission MenuPro sur les livraisons</p>
                <p class="mt-1 vt-muted">Sur les commandes livrées via l'app client par les commerces de votre ville qui vous sont attribués.</p>
            </div>
        </div>
        <ul class="mt-8 grid gap-2 md:grid-cols-3 md:gap-8">
            @foreach([
                ['Exclusivité de la ville', 'Objectifs atteints ? Votre ville devient votre territoire exclusif : tous ses commerces vous sont attribués.'],
                ['Aucune manipulation d\'argent', 'Les commerces paient MenuPro directement. Vous ne collectez jamais d\'argent.'],
                ['Tout est transparent', 'Votre espace affiche vos commerces, les encaissements et votre part, calculée chaque mois.'],
            ] as [$title, $text])
                <li class="vt-benefit">
                    <h3 class="vt-display text-xl">{{ $title }}</h3>
                    <p class="mt-1 vt-muted">{{ $text }}</p>
                </li>
            @endforeach
        </ul>
    </div>
</section>

{{-- ══════════ RÔLE + CONDITIONS ══════════ --}}
<section class="vt-section bg-[#FAFAF9]">
    <div class="vt-wrap max-w-5xl grid gap-5 md:grid-cols-2">
        <div class="vt-card p-6 md:p-8">
            <h2 class="vt-display text-2xl">Votre rôle</h2>
            <ul class="vt-checks-soft mt-4 space-y-3">
                <li>Présenter MenuPro aux restaurants, maquis, hôtels, stands et boutiques de votre ville.</li>
                <li>Les inscrire avec votre lien de parrainage personnel.</li>
                <li>Les accompagner : prise en main du QR code, des commandes et de la livraison.</li>
                <li>Faire remonter les besoins du terrain à l'équipe MenuPro.</li>
            </ul>
        </div>
        <div class="vt-card p-6 md:p-8">
            <h2 class="vt-display text-2xl">Conditions d'entrée</h2>
            <ul class="vt-checks-soft mt-4 space-y-3">
                <li>Être majeur et résider dans la ville que vous souhaitez représenter.</li>
                <li>Avoir un bon réseau local (restauration, commerce, associations…).</li>
                <li>Disposer d'un smartphone et d'un numéro WhatsApp actif.</li>
                <li>Période d'essai de {{ $rates['trial_months'] }} mois, avec des objectifs de commerces payants par trimestre.</li>
                <li>Un seul représentant par ville : les candidatures sont étudiées dans l'ordre d'arrivée.</li>
            </ul>
        </div>
    </div>
</section>

{{-- ══════════ FORMULAIRE ══════════ --}}
<section id="candidature" class="vt-section scroll-mt-16">
    <div class="vt-wrap max-w-3xl">
        <h2 class="vt-h2 text-center">Déposer ma candidature</h2>
        <p class="vt-lead mx-auto text-center mt-3">Notre équipe étudie chaque dossier et vous recontacte sur WhatsApp pour un entretien.</p>

        @if(session('application_sent'))
            <div class="mt-6 vt-alert vt-alert-ok" role="status">
                <p class="font-bold">Merci, votre candidature a bien été envoyée.</p>
                <p class="mt-1">Nous vous recontactons sous quelques jours.</p>
            </div>
        @endif

        @if($errors->any())
            <div class="mt-6 vt-alert vt-alert-error" role="alert">
                <p class="font-bold">Merci de corriger les points suivants :</p>
                <ul class="list-disc pl-5 mt-1 space-y-0.5">
                    @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
                </ul>
            </div>
        @endif

        <form method="POST" action="{{ route('representative.apply') }}" enctype="multipart/form-data"
              class="mt-8 vt-card p-5 sm:p-8 space-y-6 relative">
            @csrf

            {{-- Champ piège anti-robots : invisible pour les humains --}}
            <div aria-hidden="true" style="position:absolute;left:-10000px;top:auto;width:1px;height:1px;overflow:hidden">
                <label for="website">Site web</label>
                <input type="text" id="website" name="website" value="" tabindex="-1" autocomplete="off">
            </div>

            <div class="grid gap-6 sm:grid-cols-2">
                <div>
                    <label for="full_name" class="vt-label">Nom complet <span aria-hidden="true">*</span></label>
                    <input id="full_name" name="full_name" type="text" required maxlength="120" value="{{ old('full_name') }}" autocomplete="name" class="vt-input">
                </div>
                <div>
                    <label for="phone" class="vt-label">Téléphone (WhatsApp) <span aria-hidden="true">*</span></label>
                    <input id="phone" name="phone" type="tel" required maxlength="30" value="{{ old('phone') }}" placeholder="+225 07 00 00 00 00" inputmode="tel" autocomplete="tel" class="vt-input">
                </div>
                <div>
                    <label for="email" class="vt-label">E-mail <span class="vt-hint">(facultatif)</span></label>
                    <input id="email" name="email" type="email" maxlength="150" value="{{ old('email') }}" autocomplete="email" class="vt-input">
                </div>
                <div>
                    <label for="city" class="vt-label">Ville à représenter <span aria-hidden="true">*</span></label>
                    <input id="city" name="city" type="text" required maxlength="80" list="rep-cities" value="{{ old('city') }}" placeholder="Ex. Daloa" class="vt-input">
                    <datalist id="rep-cities">
                        @foreach($cities as $c)<option value="{{ $c }}">@endforeach
                    </datalist>
                </div>
                <div>
                    <label for="legal_status" class="vt-label">Statut légal <span aria-hidden="true">*</span></label>
                    <select id="legal_status" name="legal_status" required class="vt-input">
                        <option value="">Choisir…</option>
                        @foreach(\App\Models\PartnerApplication::LEGAL_STATUS_LABELS as $value => $label)
                            <option value="{{ $value }}" @selected(old('legal_status') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <label for="availability" class="vt-label">Disponibilité <span aria-hidden="true">*</span></label>
                    <select id="availability" name="availability" required class="vt-input">
                        <option value="">Choisir…</option>
                        @foreach(\App\Models\PartnerApplication::AVAILABILITY_LABELS as $value => $label)
                            <option value="{{ $value }}" @selected(old('availability') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label for="experience" class="vt-label">Expérience et réseau <span aria-hidden="true">*</span></label>
                <textarea id="experience" name="experience" rows="4" required maxlength="3000" placeholder="Vente, restauration, commerce… Quels commerces ou réseaux connaissez-vous dans votre ville ?" class="vt-input">{{ old('experience') }}</textarea>
            </div>

            <div>
                <label for="motivation" class="vt-label">Motivation <span aria-hidden="true">*</span></label>
                <textarea id="motivation" name="motivation" rows="4" required maxlength="3000" placeholder="Pourquoi souhaitez-vous représenter MenuPro ?" class="vt-input">{{ old('motivation') }}</textarea>
            </div>

            <div>
                <label for="attachment" class="vt-label">Pièce jointe <span class="vt-hint">(facultatif)</span></label>
                <input id="attachment" name="attachment" type="file" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" aria-describedby="attachment-hint"
                       class="block w-full text-base file:mr-3 file:min-h-11 file:px-4 file:rounded-xl file:border-0 file:font-semibold file:bg-neutral-100 file:text-mp-black file:cursor-pointer">
                <p id="attachment-hint" class="mt-1 vt-hint">CV ou présentation : PDF, Word, JPG ou PNG, 5 Mo maximum.</p>
            </div>

            <label class="flex items-start gap-3 cursor-pointer">
                <input type="checkbox" name="accept_terms" value="1" required class="vt-checkbox mt-0.5" @checked(old('accept_terms'))>
                <span>J'accepte que MenuPro utilise ces informations pour étudier ma candidature et me recontacter. <a href="{{ route('privacy') }}" class="vt-link">Politique de confidentialité</a></span>
            </label>

            <button type="submit" class="vt-btn vt-btn-dark vt-btn-block">Envoyer ma candidature</button>
        </form>
    </div>
</section>

</x-layouts.public>
