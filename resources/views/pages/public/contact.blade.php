@php
    $contactEmail = \App\Models\SystemSetting::get('contact_email', 'contact@menupro.com');
    $contactPhone = \App\Models\SystemSetting::get('contact_phone', '');
    $contactWhatsapp = \App\Models\SystemSetting::get('contact_whatsapp', $contactPhone);
    $socials = array_filter([
        'Facebook'  => \App\Models\SystemSetting::get('social_facebook', ''),
        'Instagram' => \App\Models\SystemSetting::get('social_instagram', ''),
        'LinkedIn'  => \App\Models\SystemSetting::get('social_linkedin', ''),
        'X'         => \App\Models\SystemSetting::get('social_twitter', ''),
    ]);
    // Type présélectionné depuis les liens du site (?type=demo, ?type=gold pour un devis Gold)
    $types = ['general' => 'Question', 'support' => 'Support', 'partnership' => 'Partenariat', 'demo' => 'Démo'];
    $requested = request()->query('type');
    $defaultType = $requested === 'gold' ? 'general' : (array_key_exists((string) $requested, $types) ? $requested : 'general');
    $defaultSubject = $requested === 'gold' ? 'Devis plan Gold' : '';
@endphp
<x-layouts.public title="Contact" description="Contactez l'équipe MenuPro : question, démo, devis Gold ou aide pour votre restaurant, maquis, stand ou boutique.">
    <section class="vt-pagehead">
        <div class="vt-wrap max-w-3xl">
            <h1 class="vt-h1">Contactez-nous</h1>
            <p class="vt-lead mt-4">Une question, une démo, un devis ? Écrivez-nous : nous vous répondons dès que possible, par e-mail ou sur WhatsApp.</p>
        </div>
    </section>

    <section class="pb-16 md:pb-24">
        <div class="vt-wrap grid gap-6 lg:grid-cols-[1fr_1.7fr] lg:items-start">
            {{-- Coordonnées --}}
            <aside class="grid gap-3">
                @if($contactWhatsapp)
                    <a href="https://wa.me/{{ preg_replace('/[^0-9]/', '', $contactWhatsapp) }}?text={{ urlencode('Bonjour, je souhaite en savoir plus sur MenuPro.') }}" target="_blank" rel="noopener noreferrer"
                       class="vt-soft !rounded-2xl p-5 flex items-start gap-4 hover:bg-neutral-200/60">
                        <span class="flex-none w-10 h-10 rounded-xl bg-white flex items-center justify-center text-[#15803D] shadow-sm" aria-hidden="true">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0C5.373 0 0 5.373 0 12c0 2.625.846 5.059 2.284 7.034L.789 23.492a.5.5 0 00.611.611l4.458-1.495A11.952 11.952 0 0012 24c6.627 0 12-5.373 12-12S18.627 0 12 0zm0 22c-2.34 0-4.508-.768-6.258-2.066l-.438-.338-2.652.889.889-2.652-.338-.438A9.964 9.964 0 012 12C2 6.477 6.477 2 12 2s10 4.477 10 10-4.477 10-10 10z"/></svg>
                        </span>
                        <span class="min-w-0">
                            <span class="block font-semibold">WhatsApp</span>
                            <span class="block vt-muted">{{ $contactWhatsapp }}</span>
                            <span class="block text-sm vt-muted mt-0.5">Le plus simple pour une question rapide.</span>
                        </span>
                    </a>
                @endif
                <a href="mailto:{{ $contactEmail }}" class="vt-soft !rounded-2xl p-5 flex items-start gap-4 hover:bg-neutral-200/60">
                    <span class="flex-none w-10 h-10 rounded-xl bg-white flex items-center justify-center text-mp-ink shadow-sm" aria-hidden="true">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                    </span>
                    <span class="min-w-0">
                        <span class="block font-semibold">E-mail</span>
                        <span class="block vt-muted break-all">{{ $contactEmail }}</span>
                    </span>
                </a>
                @if($contactPhone)
                    <a href="tel:{{ preg_replace('/[^0-9+]/', '', $contactPhone) }}" class="vt-soft !rounded-2xl p-5 flex items-start gap-4 hover:bg-neutral-200/60">
                        <span class="flex-none w-10 h-10 rounded-xl bg-white flex items-center justify-center text-mp-ink shadow-sm" aria-hidden="true">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                        </span>
                        <span class="min-w-0">
                            <span class="block font-semibold">Téléphone</span>
                            <span class="block vt-muted">{{ $contactPhone }}</span>
                            <span class="block text-sm vt-muted mt-0.5">Du lundi au vendredi, de 9 h à 18 h.</span>
                        </span>
                    </a>
                @endif
                @if($socials)
                    <ul class="mt-2 flex flex-wrap gap-x-5 gap-y-1 px-1">
                        @foreach($socials as $network => $url)
                            <li><a href="{{ $url }}" target="_blank" rel="noopener noreferrer" class="inline-flex min-h-11 items-center font-semibold text-neutral-700 hover:text-mp-black">{{ $network }}</a></li>
                        @endforeach
                    </ul>
                @endif
            </aside>

            {{-- Formulaire --}}
            <div class="vt-card p-5 sm:p-8">
                <h2 class="vt-display text-2xl">Envoyez-nous un message</h2>
                <p class="mt-1 vt-muted">Tous les champs sont obligatoires.</p>

                @if(session('success'))
                    <div class="vt-alert vt-alert-ok mt-6 font-medium" role="status">{{ session('success') }}</div>
                @endif
                @if(session('error'))
                    <div class="vt-alert vt-alert-error mt-6 font-medium" role="alert">{{ session('error') }}</div>
                @endif

                <form action="{{ route('contact.send') }}" method="POST" class="mt-6 space-y-5">
                    @csrf

                    <fieldset>
                        <legend class="vt-label">Type de demande</legend>
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                            @foreach($types as $value => $label)
                                <label class="relative cursor-pointer">
                                    <input type="radio" name="type" value="{{ $value }}" class="peer sr-only" @checked(old('type', $defaultType) === $value)>
                                    <span class="flex items-center justify-center min-h-12 px-3 rounded-xl bg-white font-semibold text-[0.9375rem] shadow-[0_0_0_1px_#E7E5E4] hover:shadow-[0_0_0_1px_#A8A29E] peer-checked:bg-[#FFF7ED] peer-checked:shadow-[0_0_0_2px_#EA580C] peer-focus-visible:ring-4 peer-focus-visible:ring-orange-200">{{ $label }}</span>
                                </label>
                            @endforeach
                        </div>
                        @error('type')<p class="vt-error" role="alert">{{ $message }}</p>@enderror
                    </fieldset>

                    <div class="grid gap-5 sm:grid-cols-2">
                        <div>
                            <label for="name" class="vt-label">Nom complet</label>
                            <input type="text" id="name" name="name" value="{{ old('name') }}" required maxlength="100" autocomplete="name"
                                   @error('name') aria-invalid="true" aria-describedby="name-error" @enderror class="vt-input" placeholder="Awa Koné">
                            @error('name')<p id="name-error" class="vt-error" role="alert">{{ $message }}</p>@enderror
                        </div>
                        <div>
                            <label for="email" class="vt-label">E-mail</label>
                            <input type="email" id="email" name="email" value="{{ old('email') }}" required maxlength="255" autocomplete="email" autocapitalize="none"
                                   @error('email') aria-invalid="true" aria-describedby="email-error" @enderror class="vt-input" placeholder="vous@exemple.com">
                            @error('email')<p id="email-error" class="vt-error" role="alert">{{ $message }}</p>@enderror
                        </div>
                    </div>

                    <div>
                        <label for="subject" class="vt-label">Sujet</label>
                        <input type="text" id="subject" name="subject" value="{{ old('subject', $defaultSubject) }}" required maxlength="200"
                               @error('subject') aria-invalid="true" aria-describedby="subject-error" @enderror class="vt-input" placeholder="Comment pouvons-nous vous aider ?">
                        @error('subject')<p id="subject-error" class="vt-error" role="alert">{{ $message }}</p>@enderror
                    </div>

                    <div>
                        <label for="message" class="vt-label">Message</label>
                        <textarea id="message" name="message" rows="5" required minlength="20" maxlength="2000" aria-describedby="message-hint @error('message') message-error @enderror"
                                  @error('message') aria-invalid="true" @enderror class="vt-input resize-y" placeholder="Votre commerce, votre ville, votre question…">{{ old('message') }}</textarea>
                        <p id="message-hint" class="mt-2 vt-hint">20 caractères minimum.</p>
                        @error('message')<p id="message-error" class="vt-error" role="alert">{{ $message }}</p>@enderror
                    </div>

                    <div class="flex flex-col-reverse sm:flex-row sm:items-center sm:justify-between gap-4 pt-1">
                        <p class="text-sm vt-muted">Vos données servent uniquement à vous répondre.</p>
                        <button type="submit" class="vt-btn vt-btn-dark">Envoyer le message</button>
                    </div>
                </form>
            </div>
        </div>
    </section>

    <section class="pb-14 md:pb-20">
        <div class="vt-wrap">
            <div class="vt-soft px-5 py-12 md:py-14 text-center">
                <h2 class="vt-h2">Mettez votre commerce en ligne.</h2>
                <p class="vt-lead mx-auto mt-3">Restaurant, maquis, stand ou boutique : essai gratuit, sans engagement.</p>
                <div class="mt-8 grid gap-3 sm:flex sm:justify-center">
                    <a href="{{ route('register') }}" class="vt-btn vt-btn-dark">Créer mon commerce</a>
                    <a href="{{ route('pricing') }}" class="vt-btn vt-btn-ghost">Voir les tarifs</a>
                </div>
            </div>
        </div>
    </section>
</x-layouts.public>
