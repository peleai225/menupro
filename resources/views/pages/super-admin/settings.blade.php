<x-layouts.admin-super title="Paramètres">

    {{-- ── Page Intro ─────────────────────────────────────────────────────── --}}
    <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold" style="color:var(--sa-fg);">Paramètres système</h1>
            <p class="mt-1 text-sm" style="color:var(--sa-muted-fg);">Configuration générale de la plateforme MenuPro</p>
        </div>
    </div>

    {{-- ── Flash Messages ───────────────────────────────────────────────────── --}}
    @if(session('success'))
        <div class="mb-5 flex items-center gap-3 rounded-xl border px-4 py-3 text-sm"
             style="background:rgba(61,158,98,0.08);border-color:rgba(61,158,98,0.25);color:var(--sa-success);">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-5 flex items-center gap-3 rounded-xl border px-4 py-3 text-sm"
             style="background:rgba(220,38,38,0.08);border-color:rgba(220,38,38,0.25);color:var(--sa-danger);">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            {{ session('error') }}
        </div>
    @endif
    @if($errors->any())
        <div class="mb-5 rounded-xl border px-4 py-3 text-sm"
             style="background:rgba(220,38,38,0.08);border-color:rgba(220,38,38,0.25);color:var(--sa-danger);">
            <ul class="space-y-1 list-disc list-inside">
                @foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach
            </ul>
        </div>
    @endif

    {{-- ── Tabs + Content ──────────────────────────────────────────────────── --}}
    <div x-data="{ tab: '{{ old('_tab', 'general') }}' }">

        {{-- Tab Bar --}}
        <div class="flex gap-1 overflow-x-auto mb-6 rounded-xl border p-1"
             style="border-color:var(--sa-border);background:var(--sa-muted);">
            @php
                $tabs = [
                    'general'    => ['icon' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z', 'label' => 'Général'],
                    'payment'    => ['icon' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z', 'label' => 'Paiements'],
                    'email'      => ['icon' => 'M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z', 'label' => 'Emails'],
                    'security'   => ['icon' => 'M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z', 'label' => 'Sécurité'],
                    'appearance' => ['icon' => 'M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z', 'label' => 'Apparence'],
                    'marketing'  => ['icon' => 'M11 3.055A9.001 9.001 0 1020.945 13H11V3.055z M20.488 9H15V3.512A9.025 9.025 0 0120.488 9z', 'label' => 'Marketing'],
                    'commando'   => ['icon' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z', 'label' => 'Commando'],
                    'whatsapp'   => ['icon' => 'M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z', 'label' => 'WhatsApp'],
                    'delivery'   => ['icon' => 'M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7', 'label' => 'Livraison & Cartes'],
                ];
            @endphp
            @foreach($tabs as $key => $tab)
                <button @click="tab = '{{ $key }}'"
                        :class="tab === '{{ $key }}' ? 'shadow-sm' : 'hover:opacity-80'"
                        :style="tab === '{{ $key }}' ? 'background:var(--sa-card);color:var(--sa-primary);' : 'background:transparent;color:var(--sa-muted-fg);'"
                        class="flex items-center gap-1.5 whitespace-nowrap rounded-lg px-3 py-2 text-sm font-medium transition-all">
                    <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.8" d="{{ $tab['icon'] }}"/>
                    </svg>
                    <span>{{ $tab['label'] }}</span>
                </button>
            @endforeach
        </div>

        {{-- ─── GÉNÉRAL ────────────────────────────────────────────────────── --}}
        <div x-show="tab === 'general'" x-cloak>
            <form method="POST" action="{{ route('super-admin.settings.update') }}" class="space-y-5">
                @csrf
                <input type="hidden" name="_tab" value="general">

                {{-- Informations plateforme --}}
                <div class="rounded-2xl border p-5 shadow-sm" style="border-color:var(--sa-border);background:var(--sa-card);">
                    <h2 class="text-base font-semibold mb-4" style="color:var(--sa-fg);">Informations plateforme</h2>
                    <div class="space-y-4">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium mb-1.5" style="color:var(--sa-muted-fg);">Nom de la plateforme <span style="color:var(--sa-danger);">*</span></label>
                                <input type="text" name="app_name" value="{{ old('app_name', $settings['app_name'] ?? 'MenuPro') }}" required
                                       class="w-full h-10 px-3 rounded-xl border text-sm outline-none transition focus:ring-2"
                                       style="background:var(--sa-muted);border-color:var(--sa-border);color:var(--sa-fg);" placeholder="MenuPro">
                            </div>
                            <div>
                                <label class="block text-xs font-medium mb-1.5" style="color:var(--sa-muted-fg);">URL de base <span style="color:var(--sa-danger);">*</span></label>
                                <input type="url" name="app_url" value="{{ old('app_url', $settings['app_url'] ?? '') }}" required
                                       class="w-full h-10 px-3 rounded-xl border text-sm outline-none transition focus:ring-2"
                                       style="background:var(--sa-muted);border-color:var(--sa-border);color:var(--sa-fg);" placeholder="https://menupro.ci">
                            </div>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium mb-1.5" style="color:var(--sa-muted-fg);">Email de contact <span style="color:var(--sa-danger);">*</span></label>
                                <input type="email" name="contact_email" value="{{ old('contact_email', $settings['contact_email'] ?? '') }}" required
                                       class="w-full h-10 px-3 rounded-xl border text-sm outline-none transition focus:ring-2"
                                       style="background:var(--sa-muted);border-color:var(--sa-border);color:var(--sa-fg);" placeholder="contact@menupro.ci">
                            </div>
                            <div>
                                <label class="block text-xs font-medium mb-1.5" style="color:var(--sa-muted-fg);">Téléphone de contact</label>
                                <input type="text" name="contact_phone" value="{{ old('contact_phone', $settings['contact_phone'] ?? '') }}"
                                       class="w-full h-10 px-3 rounded-xl border text-sm outline-none transition focus:ring-2"
                                       style="background:var(--sa-muted);border-color:var(--sa-border);color:var(--sa-fg);" placeholder="+225 07 00 00 00 00">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Réseaux sociaux --}}
                <div class="rounded-2xl border p-5 shadow-sm" style="border-color:var(--sa-border);background:var(--sa-card);">
                    <h2 class="text-base font-semibold mb-1" style="color:var(--sa-fg);">Réseaux sociaux</h2>
                    <p class="text-xs mb-4" style="color:var(--sa-muted-fg);">Affichés sur la page Contact et dans le footer du site public.</p>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach([
                            ['name' => 'social_facebook',  'label' => 'Facebook',    'ph' => 'https://facebook.com/menupro'],
                            ['name' => 'social_instagram', 'label' => 'Instagram',   'ph' => 'https://instagram.com/menupro'],
                            ['name' => 'social_twitter',   'label' => 'Twitter / X', 'ph' => 'https://twitter.com/menupro'],
                            ['name' => 'social_linkedin',  'label' => 'LinkedIn',    'ph' => 'https://linkedin.com/company/menupro'],
                        ] as $s)
                        <div>
                            <label class="block text-xs font-medium mb-1.5" style="color:var(--sa-muted-fg);">{{ $s['label'] }}</label>
                            <input type="url" name="{{ $s['name'] }}" value="{{ old($s['name'], $settings[$s['name']] ?? '') }}"
                                   class="w-full h-10 px-3 rounded-xl border text-sm outline-none transition focus:ring-2"
                                   style="background:var(--sa-muted);border-color:var(--sa-border);color:var(--sa-fg);" placeholder="{{ $s['ph'] }}">
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Options --}}
                <div class="rounded-2xl border p-5 shadow-sm" style="border-color:var(--sa-border);background:var(--sa-card);">
                    <h2 class="text-base font-semibold mb-4" style="color:var(--sa-fg);">Options</h2>
                    <div class="space-y-3">
                        @foreach([
                            ['name' => 'maintenance_mode',   'label' => 'Mode maintenance',     'desc' => 'Désactiver temporairement l\'accès public'],
                            ['name' => 'registrations_open', 'label' => 'Inscriptions ouvertes', 'desc' => 'Autoriser les nouvelles inscriptions'],
                        ] as $opt)
                        <label class="flex items-center justify-between p-3.5 rounded-xl cursor-pointer transition"
                               style="background:var(--sa-muted);border:1px solid var(--sa-border);">
                            <div>
                                <p class="text-sm font-medium" style="color:var(--sa-fg);">{{ $opt['label'] }}</p>
                                <p class="text-xs mt-0.5" style="color:var(--sa-muted-fg);">{{ $opt['desc'] }}</p>
                            </div>
                            <div class="relative flex-shrink-0 ml-4">
                                <input type="hidden" name="{{ $opt['name'] }}" value="0">
                                <input type="checkbox" name="{{ $opt['name'] }}" value="1"
                                       {{ $settings[$opt['name']] ? 'checked' : '' }}
                                       class="sr-only peer">
                                <div class="w-10 h-5 rounded-full peer transition-colors"
                                     style="background:var(--sa-border);"
                                     x-bind:style="$el.previousElementSibling.checked ? 'background:var(--sa-primary)' : 'background:var(--sa-border)'"></div>
                                <div class="absolute top-0.5 left-0.5 w-4 h-4 rounded-full bg-white shadow transition-transform peer-checked:translate-x-5"></div>
                            </div>
                        </label>
                        @endforeach
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="inline-flex h-9 items-center gap-2 rounded-lg px-4 text-sm font-semibold shadow-sm transition"
                            style="background:var(--sa-primary);color:var(--sa-primary-fg);">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>

        {{-- ─── PAIEMENTS ──────────────────────────────────────────────────── --}}
        <div x-show="tab === 'payment'" x-cloak>
            <form method="POST" action="{{ route('super-admin.settings.update') }}" class="space-y-5">
                @csrf
                <input type="hidden" name="_tab" value="payment">
                <input type="hidden" name="app_name" value="{{ $settings['app_name'] ?? 'MenuPro' }}">
                <input type="hidden" name="app_url" value="{{ $settings['app_url'] ?? '' }}">
                <input type="hidden" name="contact_email" value="{{ $settings['contact_email'] ?? '' }}">

                <div class="grid grid-cols-1 xl:grid-cols-2 gap-5">
                    {{-- MoneyFusion --}}
                    <div class="rounded-2xl border p-5 shadow-sm" style="border-color:var(--sa-border);background:var(--sa-card);">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="w-2 h-2 rounded-full" style="background:#2563eb;"></span>
                            <h2 class="text-base font-semibold" style="color:var(--sa-fg);">MoneyFusion</h2>
                        </div>
                        <p class="text-xs mb-4" style="color:var(--sa-muted-fg);">
                            Paiements abonnements (Orange Money, MTN, Wave, Moov).
                            <a href="https://docs.moneyfusion.net" target="_blank" class="underline" style="color:var(--sa-primary);">Documentation</a>
                        </p>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-medium mb-1.5" style="color:var(--sa-muted-fg);">URL API <span style="color:var(--sa-danger);">*</span></label>
                                <input type="url" name="moneyfusion_api_url" value="{{ old('moneyfusion_api_url', $settings['moneyfusion_api_url'] ?? '') }}"
                                       class="w-full h-10 px-3 rounded-xl border text-sm outline-none transition"
                                       style="background:var(--sa-muted);border-color:var(--sa-border);color:var(--sa-fg);" placeholder="https://www.pay.moneyfusion.net/...">
                            </div>
                            <div>
                                <label class="block text-xs font-medium mb-1.5" style="color:var(--sa-muted-fg);">Clé API</label>
                                <input type="password" name="moneyfusion_api_key" value="{{ old('moneyfusion_api_key', $settings['moneyfusion_api_key'] ?? '') }}"
                                       class="w-full h-10 px-3 rounded-xl border text-sm outline-none transition"
                                       style="background:var(--sa-muted);border-color:var(--sa-border);color:var(--sa-fg);" placeholder="Votre clé API">
                            </div>
                            <div class="rounded-lg border px-3 py-2.5 text-xs"
                                 style="background:rgba(59,111,212,0.06);border-color:rgba(59,111,212,0.20);color:var(--sa-info);">
                                Webhook : <code class="font-mono">{{ url('/webhooks/moneyfusion') }}</code>
                            </div>
                        </div>
                    </div>

                    {{-- Wave CI --}}
                    <div class="rounded-2xl border p-5 shadow-sm" style="border-color:var(--sa-border);background:var(--sa-card);">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="w-2 h-2 rounded-full" style="background:#0ea5e9;"></span>
                            <h2 class="text-base font-semibold" style="color:var(--sa-fg);">Wave CI</h2>
                        </div>
                        <p class="text-xs mb-4" style="color:var(--sa-muted-fg);">
                            Paiements commandes clients. Les restaurants avec Wave Business reçoivent leurs fonds automatiquement.
                        </p>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-medium mb-1.5" style="color:var(--sa-muted-fg);">Clé API Wave <span style="color:var(--sa-danger);">*</span></label>
                                <input type="password" name="wave_api_key" value="{{ old('wave_api_key', $settings['wave_api_key'] ?? '') }}"
                                       class="w-full h-10 px-3 rounded-xl border text-sm outline-none transition"
                                       style="background:var(--sa-muted);border-color:var(--sa-border);color:var(--sa-fg);" placeholder="wave_sn_prod_...">
                            </div>
                            <div>
                                <label class="block text-xs font-medium mb-1.5" style="color:var(--sa-muted-fg);">Secret Webhook</label>
                                <input type="password" name="wave_webhook_secret" value="{{ old('wave_webhook_secret', $settings['wave_webhook_secret'] ?? '') }}"
                                       class="w-full h-10 px-3 rounded-xl border text-sm outline-none transition"
                                       style="background:var(--sa-muted);border-color:var(--sa-border);color:var(--sa-fg);" placeholder="Secret HMAC-SHA256">
                            </div>
                            <div class="rounded-lg border px-3 py-2.5 text-xs"
                                 style="background:rgba(14,165,233,0.06);border-color:rgba(14,165,233,0.20);color:var(--sa-info);">
                                Webhook : <code class="font-mono">{{ url('/webhooks/wave') }}</code>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- ElevenLabs TTS --}}
                <div class="rounded-2xl border p-5 shadow-sm" style="border-color:var(--sa-border);background:var(--sa-card);">
                    <div class="flex items-center gap-2 mb-1">
                        <span class="w-2 h-2 rounded-full" style="background:#a855f7;"></span>
                        <h2 class="text-base font-semibold" style="color:var(--sa-fg);">ElevenLabs — Synthèse vocale KDS</h2>
                    </div>
                    <p class="text-xs mb-4" style="color:var(--sa-muted-fg);">
                        Voix africaine pour annoncer les nouvelles commandes sur l'écran cuisine.
                        Clé API et ID de voix disponibles sur <strong>elevenlabs.io</strong>.
                    </p>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium mb-1.5" style="color:var(--sa-muted-fg);">Clé API <span style="color:var(--sa-danger);">*</span></label>
                            <input type="password" name="elevenlabs_api_key"
                                   value="{{ old('elevenlabs_api_key', $settings['elevenlabs_api_key'] ?? '') }}"
                                   class="w-full h-10 px-3 rounded-xl border text-sm outline-none transition"
                                   style="background:var(--sa-muted);border-color:var(--sa-border);color:var(--sa-fg);"
                                   placeholder="sk_...">
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1.5" style="color:var(--sa-muted-fg);">ID de la voix</label>
                            <input type="text" name="elevenlabs_voice_id"
                                   value="{{ old('elevenlabs_voice_id', $settings['elevenlabs_voice_id'] ?? '') }}"
                                   class="w-full h-10 px-3 rounded-xl border text-sm outline-none transition"
                                   style="background:var(--sa-muted);border-color:var(--sa-border);color:var(--sa-fg);"
                                   placeholder="ex: pNInz6obpgDQGcFmaJgB">
                            <p class="text-xs mt-1.5" style="color:var(--sa-muted-fg);">
                                Copier l'ID depuis ElevenLabs → Voices → clic sur la voix choisie → l'ID est dans l'URL.
                            </p>
                        </div>
                        @if(!empty($settings['elevenlabs_api_key']))
                        <div class="rounded-lg border px-3 py-2.5 text-xs flex items-center gap-2"
                             style="background:rgba(168,85,247,0.06);border-color:rgba(168,85,247,0.20);color:#a855f7;">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Clé API configurée — synthèse vocale active sur le KDS.
                        </div>
                        @endif
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="inline-flex h-9 items-center gap-2 rounded-lg px-4 text-sm font-semibold shadow-sm transition"
                            style="background:var(--sa-primary);color:var(--sa-primary-fg);">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>

        {{-- ─── EMAILS ─────────────────────────────────────────────────────── --}}
        <div x-show="tab === 'email'" x-cloak>
            <form method="POST" action="{{ route('super-admin.settings.update') }}" class="space-y-5">
                @csrf
                <input type="hidden" name="_tab" value="email">
                <div class="rounded-2xl border p-5 shadow-sm" style="border-color:var(--sa-border);background:var(--sa-card);">
                    <h2 class="text-base font-semibold mb-1" style="color:var(--sa-fg);">Configuration SMTP</h2>
                    <p class="text-xs mb-4" style="color:var(--sa-muted-fg);">Ces paramètres remplaceront ceux du fichier .env.</p>
                    <div class="space-y-4">
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium mb-1.5" style="color:var(--sa-muted-fg);">Serveur SMTP <span style="color:var(--sa-danger);">*</span></label>
                                <input type="text" name="smtp_host" value="{{ $settings['smtp_host'] ?? '' }}" required
                                       class="w-full h-10 px-3 rounded-xl border text-sm outline-none"
                                       style="background:var(--sa-muted);border-color:var(--sa-border);color:var(--sa-fg);" placeholder="smtp.gmail.com">
                            </div>
                            <div>
                                <label class="block text-xs font-medium mb-1.5" style="color:var(--sa-muted-fg);">Port <span style="color:var(--sa-danger);">*</span></label>
                                <input type="number" name="smtp_port" value="{{ $settings['smtp_port'] ?? 587 }}" required
                                       class="w-full h-10 px-3 rounded-xl border text-sm outline-none"
                                       style="background:var(--sa-muted);border-color:var(--sa-border);color:var(--sa-fg);" placeholder="587">
                            </div>
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1.5" style="color:var(--sa-muted-fg);">Chiffrement</label>
                            <select name="smtp_encryption" class="w-full h-10 px-3 rounded-xl border text-sm outline-none"
                                    style="background:var(--sa-muted);border-color:var(--sa-border);color:var(--sa-fg);">
                                <option value="tls" {{ ($settings['smtp_encryption'] ?? 'tls') === 'tls' ? 'selected' : '' }}>TLS (recommandé)</option>
                                <option value="ssl" {{ ($settings['smtp_encryption'] ?? '') === 'ssl' ? 'selected' : '' }}>SSL</option>
                                <option value="" {{ empty($settings['smtp_encryption'] ?? '') ? 'selected' : '' }}>Aucun</option>
                            </select>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium mb-1.5" style="color:var(--sa-muted-fg);">Nom d'utilisateur <span style="color:var(--sa-danger);">*</span></label>
                                <input type="text" name="smtp_username" value="{{ $settings['smtp_username'] ?? '' }}" required
                                       class="w-full h-10 px-3 rounded-xl border text-sm outline-none"
                                       style="background:var(--sa-muted);border-color:var(--sa-border);color:var(--sa-fg);" placeholder="votre-email@gmail.com">
                            </div>
                            <div>
                                <label class="block text-xs font-medium mb-1.5" style="color:var(--sa-muted-fg);">Mot de passe</label>
                                <input type="password" name="smtp_password" value=""
                                       class="w-full h-10 px-3 rounded-xl border text-sm outline-none"
                                       style="background:var(--sa-muted);border-color:var(--sa-border);color:var(--sa-fg);" placeholder="Laisser vide pour ne pas modifier">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium mb-1.5" style="color:var(--sa-muted-fg);">Email expéditeur <span style="color:var(--sa-danger);">*</span></label>
                                <input type="email" name="smtp_from_address" value="{{ $settings['smtp_from_address'] ?? '' }}" required
                                       class="w-full h-10 px-3 rounded-xl border text-sm outline-none"
                                       style="background:var(--sa-muted);border-color:var(--sa-border);color:var(--sa-fg);" placeholder="noreply@menupro.ci">
                            </div>
                            <div>
                                <label class="block text-xs font-medium mb-1.5" style="color:var(--sa-muted-fg);">Nom expéditeur <span style="color:var(--sa-danger);">*</span></label>
                                <input type="text" name="smtp_from_name" value="{{ $settings['smtp_from_name'] ?? '' }}" required
                                       class="w-full h-10 px-3 rounded-xl border text-sm outline-none"
                                       style="background:var(--sa-muted);border-color:var(--sa-border);color:var(--sa-fg);" placeholder="MenuPro">
                            </div>
                        </div>
                    </div>
                </div>
                <div class="flex justify-end gap-3">
                    <button type="button" onclick="if(confirm('Envoyer un email de test ?')) alert('Enregistrez d\'abord la configuration.');"
                            class="inline-flex h-9 items-center gap-2 rounded-lg border px-4 text-sm font-medium transition"
                            style="border-color:var(--sa-border);color:var(--sa-muted-fg);">
                        Tester l'envoi
                    </button>
                    <button type="submit" class="inline-flex h-9 items-center gap-2 rounded-lg px-4 text-sm font-semibold shadow-sm transition"
                            style="background:var(--sa-primary);color:var(--sa-primary-fg);">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>

        {{-- ─── SÉCURITÉ ───────────────────────────────────────────────────── --}}
        <div x-show="tab === 'security'" x-cloak>
            <form method="POST" action="{{ route('super-admin.settings.update') }}" class="space-y-5">
                @csrf
                <input type="hidden" name="_tab" value="security">
                <div class="rounded-2xl border p-5 shadow-sm" style="border-color:var(--sa-border);background:var(--sa-card);">
                    <h2 class="text-base font-semibold mb-4" style="color:var(--sa-fg);">Sécurité</h2>
                    <div class="space-y-3">
                        @foreach([
                            ['name' => 'require_2fa', 'label' => 'Double authentification obligatoire', 'desc' => 'Forcer le 2FA pour les admins'],
                            ['name' => 'log_logins',  'label' => 'Log des connexions',                  'desc' => 'Enregistrer toutes les connexions'],
                        ] as $opt)
                        <label class="flex items-center justify-between p-3.5 rounded-xl cursor-pointer"
                               style="background:var(--sa-muted);border:1px solid var(--sa-border);">
                            <div>
                                <p class="text-sm font-medium" style="color:var(--sa-fg);">{{ $opt['label'] }}</p>
                                <p class="text-xs mt-0.5" style="color:var(--sa-muted-fg);">{{ $opt['desc'] }}</p>
                            </div>
                            <input type="hidden" name="{{ $opt['name'] }}" value="0">
                            <input type="checkbox" name="{{ $opt['name'] }}" value="1"
                                   {{ $settings[$opt['name']] ? 'checked' : '' }}
                                   class="w-4 h-4 rounded border"
                                   style="accent-color:var(--sa-primary);">
                        </label>
                        @endforeach
                    </div>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="inline-flex h-9 items-center gap-2 rounded-lg px-4 text-sm font-semibold shadow-sm transition"
                            style="background:var(--sa-primary);color:var(--sa-primary-fg);">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>

        {{-- ─── APPARENCE ──────────────────────────────────────────────────── --}}
        <div x-show="tab === 'appearance'" x-cloak>
            <form method="POST" action="{{ route('super-admin.settings.update') }}" enctype="multipart/form-data" class="space-y-5">
                @csrf
                <input type="hidden" name="_tab" value="appearance">
                <input type="hidden" name="social_facebook" value="{{ $settings['social_facebook'] ?? '' }}">
                <input type="hidden" name="social_twitter" value="{{ $settings['social_twitter'] ?? '' }}">
                <input type="hidden" name="social_instagram" value="{{ $settings['social_instagram'] ?? '' }}">
                <input type="hidden" name="social_linkedin" value="{{ $settings['social_linkedin'] ?? '' }}">
                <input type="hidden" name="contact_phone" value="{{ $settings['contact_phone'] ?? '' }}">

                {{-- Logo & Favicon --}}
                <div class="rounded-2xl border p-5 shadow-sm" style="border-color:var(--sa-border);background:var(--sa-card);">
                    <h2 class="text-base font-semibold mb-4" style="color:var(--sa-fg);">Logo et Favicon</h2>
                    <div class="space-y-5">
                        @foreach([
                            ['name' => 'logo',       'label' => 'Logo',    'accept' => 'image/*',       'hint' => 'PNG, SVG. Max 2 MB'],
                            ['name' => 'favicon',    'label' => 'Favicon', 'accept' => 'image/*',       'hint' => 'ICO, PNG. Max 512 KB'],
                            ['name' => 'hero_image', 'label' => 'Image Hero (page d\'accueil)', 'accept' => 'image/*', 'hint' => 'PNG, JPG, WebP. Max 5 MB'],
                        ] as $f)
                        <div>
                            <label class="block text-xs font-medium mb-2" style="color:var(--sa-muted-fg);">{{ $f['label'] }}</label>
                            <div class="flex items-center gap-3">
                                @if($f['name'] === 'logo' && !empty($settings['logo'] ?? '') && \Illuminate\Support\Facades\Storage::disk('public')->exists($settings['logo']))
                                    <img src="{{ asset('storage/' . ltrim($settings['logo'], '/')) }}" alt="Logo" class="h-12 w-auto object-contain rounded-lg p-1" style="background:var(--sa-muted);border:1px solid var(--sa-border);">
                                @elseif($f['name'] === 'favicon' && !empty($settings['favicon'] ?? '') && \Illuminate\Support\Facades\Storage::disk('public')->exists($settings['favicon']))
                                    <img src="{{ asset('storage/' . ltrim($settings['favicon'], '/')) }}" alt="Favicon" class="h-8 w-8 object-contain rounded p-1" style="background:var(--sa-muted);border:1px solid var(--sa-border);">
                                @elseif($f['name'] === 'hero_image' && !empty($settings['hero_image'] ?? '') && \Illuminate\Support\Facades\Storage::disk('public')->exists($settings['hero_image']))
                                    <img src="{{ \Illuminate\Support\Facades\Storage::url($settings['hero_image']) }}" alt="Hero" class="h-20 w-auto object-contain rounded-lg p-1" style="background:var(--sa-muted);border:1px solid var(--sa-border);">
                                @endif
                                <input type="file" name="{{ $f['name'] }}" accept="{{ $f['accept'] }}"
                                       class="flex-1 h-10 rounded-xl border text-sm px-3 py-2"
                                       style="background:var(--sa-muted);border-color:var(--sa-border);color:var(--sa-fg);">
                            </div>
                            <p class="text-[10px] mt-1" style="color:var(--sa-muted-fg);">{{ $f['hint'] }}</p>
                        </div>
                        @endforeach
                    </div>
                </div>

                {{-- Footer --}}
                <div class="rounded-2xl border p-5 shadow-sm" style="border-color:var(--sa-border);background:var(--sa-card);">
                    <h2 class="text-base font-semibold mb-4" style="color:var(--sa-fg);">Footer</h2>
                    <div>
                        <label class="block text-xs font-medium mb-1.5" style="color:var(--sa-muted-fg);">Texte du footer</label>
                        <input type="text" name="footer_text" value="{{ old('footer_text', $settings['footer_text'] ?? '© ' . date('Y') . ' MenuPro. Tous droits réservés.') }}"
                               class="w-full h-10 px-3 rounded-xl border text-sm outline-none"
                               style="background:var(--sa-muted);border-color:var(--sa-border);color:var(--sa-fg);" placeholder="© 2026 MenuPro.">
                    </div>
                </div>

                {{-- Vidéos page d'accueil --}}
                <div class="rounded-2xl border p-5 shadow-sm" style="border-color:var(--sa-border);background:var(--sa-card);">
                    <h2 class="text-base font-semibold mb-1" style="color:var(--sa-fg);">Vidéos page d'accueil</h2>
                    <p class="text-xs mb-4" style="color:var(--sa-muted-fg);">Tutoriels YouTube. Laissez l'URL vide pour masquer un slot.</p>
                    @php
                        $homeVideos  = $settings['home_videos'] ?? [];
                        $videoSlots  = array_pad($homeVideos, 5, ['title' => '', 'url' => '', 'description' => '']);
                    @endphp
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        @foreach($videoSlots as $i => $video)
                        <div class="rounded-xl border p-4 space-y-3" style="background:var(--sa-muted);border-color:var(--sa-border);">
                            <p class="text-xs font-semibold" style="color:var(--sa-fg);">Vidéo {{ $i + 1 }}</p>
                            <input type="text" name="home_videos[{{ $i }}][title]" value="{{ old("home_videos.{$i}.title", $video['title'] ?? '') }}"
                                   class="w-full h-9 px-3 rounded-lg border text-sm outline-none"
                                   style="background:var(--sa-card);border-color:var(--sa-border);color:var(--sa-fg);" placeholder="Titre">
                            <input type="url" name="home_videos[{{ $i }}][url]" value="{{ old("home_videos.{$i}.url", $video['url'] ?? '') }}"
                                   class="w-full h-9 px-3 rounded-lg border text-sm outline-none"
                                   style="background:var(--sa-card);border-color:var(--sa-border);color:var(--sa-fg);" placeholder="https://youtube.com/watch?v=...">
                            <textarea name="home_videos[{{ $i }}][description]" rows="2"
                                   class="w-full px-3 py-2 rounded-lg border text-sm outline-none resize-none"
                                   style="background:var(--sa-card);border-color:var(--sa-border);color:var(--sa-fg);" placeholder="Description courte">{{ old("home_videos.{$i}.description", $video['description'] ?? '') }}</textarea>
                        </div>
                        @endforeach
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="inline-flex h-9 items-center gap-2 rounded-lg px-4 text-sm font-semibold shadow-sm transition"
                            style="background:var(--sa-primary);color:var(--sa-primary-fg);">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>

        {{-- ─── MARKETING ──────────────────────────────────────────────────── --}}
        <div x-show="tab === 'marketing'" x-cloak>
            <form method="POST" action="{{ route('super-admin.settings.update') }}" class="space-y-5">
                @csrf
                <input type="hidden" name="_tab" value="marketing">
                <input type="hidden" name="app_name" value="{{ $settings['app_name'] ?? 'MenuPro' }}">
                <input type="hidden" name="app_url" value="{{ $settings['app_url'] ?? '' }}">
                <input type="hidden" name="contact_email" value="{{ $settings['contact_email'] ?? '' }}">

                {{-- Bannière --}}
                <div class="rounded-2xl border p-5 shadow-sm" style="border-color:var(--sa-border);background:var(--sa-card);">
                    <h2 class="text-base font-semibold mb-1" style="color:var(--sa-fg);">Bannière promotionnelle</h2>
                    <p class="text-xs mb-4" style="color:var(--sa-muted-fg);">Affiche une bannière en haut du site public.</p>
                    <div class="space-y-4">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="hidden" name="banner_enabled" value="0">
                            <input type="checkbox" name="banner_enabled" value="1" {{ ($settings['banner_enabled'] ?? false) ? 'checked' : '' }}
                                   class="w-4 h-4 rounded" style="accent-color:var(--sa-primary);">
                            <span class="text-sm font-medium" style="color:var(--sa-fg);">Activer la bannière</span>
                        </label>
                        <div>
                            <label class="block text-xs font-medium mb-1.5" style="color:var(--sa-muted-fg);">Texte</label>
                            <input type="text" name="banner_text" value="{{ old('banner_text', $settings['banner_text'] ?? '') }}"
                                   class="w-full h-10 px-3 rounded-xl border text-sm outline-none"
                                   style="background:var(--sa-muted);border-color:var(--sa-border);color:var(--sa-fg);" placeholder="Nouveau : Commandez en ligne avec Mobile Money !">
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium mb-1.5" style="color:var(--sa-muted-fg);">Lien (optionnel)</label>
                                <input type="url" name="banner_link" value="{{ old('banner_link', $settings['banner_link'] ?? '') }}"
                                       class="w-full h-10 px-3 rounded-xl border text-sm outline-none"
                                       style="background:var(--sa-muted);border-color:var(--sa-border);color:var(--sa-fg);" placeholder="https://...">
                            </div>
                            <div>
                                <label class="block text-xs font-medium mb-1.5" style="color:var(--sa-muted-fg);">Couleur</label>
                                <select name="banner_color" class="w-full h-10 px-3 rounded-xl border text-sm outline-none"
                                        style="background:var(--sa-muted);border-color:var(--sa-border);color:var(--sa-fg);">
                                    <option value="primary" {{ ($settings['banner_color'] ?? 'primary') === 'primary' ? 'selected' : '' }}>Orange</option>
                                    <option value="success" {{ ($settings['banner_color'] ?? '') === 'success' ? 'selected' : '' }}>Vert</option>
                                    <option value="warning" {{ ($settings['banner_color'] ?? '') === 'warning' ? 'selected' : '' }}>Jaune</option>
                                    <option value="dark"    {{ ($settings['banner_color'] ?? '') === 'dark'    ? 'selected' : '' }}>Noir</option>
                                </select>
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Facebook Pixel --}}
                <div class="rounded-2xl border p-5 shadow-sm" style="border-color:var(--sa-border);background:var(--sa-card);">
                    <h2 class="text-base font-semibold mb-1" style="color:var(--sa-fg);">Facebook Pixel</h2>
                    <p class="text-xs mb-4" style="color:var(--sa-muted-fg);">Suivi conversions campagnes Facebook Ads.</p>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium mb-1.5" style="color:var(--sa-muted-fg);">ID Pixel</label>
                            <input type="text" name="facebook_pixel_id" value="{{ old('facebook_pixel_id', $settings['facebook_pixel_id'] ?? '') }}"
                                   class="w-full h-10 px-3 rounded-xl border text-sm outline-none font-mono"
                                   style="background:var(--sa-muted);border-color:var(--sa-border);color:var(--sa-fg);" placeholder="123456789012345">
                        </div>
                        <div class="rounded-lg border px-3 py-2.5 text-xs" style="background:rgba(59,111,212,0.06);border-color:rgba(59,111,212,0.20);color:var(--sa-info);">
                            <strong>Événements trackés :</strong> PageView · Lead · AddToCart · InitiateCheckout · Purchase
                        </div>
                    </div>
                </div>

                {{-- GA4 --}}
                <div class="rounded-2xl border p-5 shadow-sm" style="border-color:var(--sa-border);background:var(--sa-card);">
                    <h2 class="text-base font-semibold mb-1" style="color:var(--sa-fg);">Google Analytics 4</h2>
                    <p class="text-xs mb-4" style="color:var(--sa-muted-fg);">Suivez le trafic de votre site.</p>
                    <div>
                        <label class="block text-xs font-medium mb-1.5" style="color:var(--sa-muted-fg);">ID de mesure GA4</label>
                        <input type="text" name="google_analytics_id" value="{{ old('google_analytics_id', $settings['google_analytics_id'] ?? '') }}"
                               class="w-full h-10 px-3 rounded-xl border text-sm outline-none font-mono"
                               style="background:var(--sa-muted);border-color:var(--sa-border);color:var(--sa-fg);" placeholder="G-XXXXXXXXXX">
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="inline-flex h-9 items-center gap-2 rounded-lg px-4 text-sm font-semibold shadow-sm transition"
                            style="background:var(--sa-primary);color:var(--sa-primary-fg);">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>

        {{-- ─── COMMANDO ───────────────────────────────────────────────────── --}}
        <div x-show="tab === 'commando'" x-cloak>
            <form method="POST" action="{{ route('super-admin.settings.update') }}" class="space-y-5">
                @csrf
                <input type="hidden" name="_tab" value="commando">
                <input type="hidden" name="app_name" value="{{ $settings['app_name'] ?? 'MenuPro' }}">
                <input type="hidden" name="app_url" value="{{ $settings['app_url'] ?? '' }}">
                <input type="hidden" name="contact_email" value="{{ $settings['contact_email'] ?? '' }}">

                <div class="rounded-2xl border p-5 shadow-sm" style="border-color:var(--sa-border);background:var(--sa-card);">
                    <h2 class="text-base font-semibold mb-1" style="color:var(--sa-fg);">Commissions agents Commando</h2>
                    <p class="text-xs mb-4" style="color:var(--sa-muted-fg);">Montant crédité à l'agent quand un restaurant inscrit via son lien paie son premier abonnement.</p>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium mb-1.5" style="color:var(--sa-muted-fg);">Commission au premier paiement (FCFA)</label>
                            <input type="number" name="commando_commission_fcfa_first_payment"
                                   value="{{ old('commando_commission_fcfa_first_payment', ($settings['commando_commission_cents_first_payment'] ?? 500000) / 100) }}"
                                   min="0" step="1"
                                   class="w-full h-10 px-3 rounded-xl border text-sm outline-none"
                                   style="background:var(--sa-muted);border-color:var(--sa-border);color:var(--sa-fg);" placeholder="5000">
                        </div>
                        {{-- Commissions par grade --}}
                        <div class="grid grid-cols-3 gap-4 mt-4">
                            <div>
                                <label class="block text-xs font-medium mb-1.5" style="color:var(--sa-muted-fg);">Commission ROOKIE (FCFA)</label>
                                <input type="number" name="commando_commission_rookie_fcfa"
                                       value="{{ old('commando_commission_rookie_fcfa', isset($settings['commando_commission_rookie_cents']) ? $settings['commando_commission_rookie_cents']/100 : 3000) }}"
                                       min="0" step="1"
                                       class="w-full h-10 px-3 rounded-xl border text-sm outline-none"
                                       style="background:var(--sa-muted);border-color:var(--sa-border);color:var(--sa-fg);" placeholder="3000">
                            </div>
                            <div>
                                <label class="block text-xs font-medium mb-1.5" style="color:var(--sa-muted-fg);">Commission COMMANDO (FCFA)</label>
                                <input type="number" name="commando_commission_commando_fcfa"
                                       value="{{ old('commando_commission_commando_fcfa', isset($settings['commando_commission_commando_cents']) ? $settings['commando_commission_commando_cents']/100 : 5000) }}"
                                       min="0" step="1"
                                       class="w-full h-10 px-3 rounded-xl border text-sm outline-none"
                                       style="background:var(--sa-muted);border-color:var(--sa-border);color:var(--sa-fg);" placeholder="5000">
                            </div>
                            <div>
                                <label class="block text-xs font-medium mb-1.5" style="color:var(--sa-muted-fg);">Commission ELITE (FCFA)</label>
                                <input type="number" name="commando_commission_elite_fcfa"
                                       value="{{ old('commando_commission_elite_fcfa', isset($settings['commando_commission_elite_cents']) ? $settings['commando_commission_elite_cents']/100 : 7000) }}"
                                       min="0" step="1"
                                       class="w-full h-10 px-3 rounded-xl border text-sm outline-none"
                                       style="background:var(--sa-muted);border-color:var(--sa-border);color:var(--sa-fg);" placeholder="7000">
                            </div>
                        </div>
                        <label class="flex items-center justify-between p-3.5 rounded-xl cursor-pointer"
                               style="background:var(--sa-muted);border:1px solid var(--sa-border);">
                            <div>
                                <p class="text-sm font-medium" style="color:var(--sa-fg);">Commission uniquement au 1er paiement</p>
                                <p class="text-xs mt-0.5" style="color:var(--sa-muted-fg);">Si coché, une seule commission par restaurant parrainé.</p>
                            </div>
                            <input type="hidden" name="commando_commission_only_first_payment" value="0">
                            <input type="checkbox" name="commando_commission_only_first_payment" value="1"
                                   {{ ($settings['commando_commission_only_first_payment'] ?? true) ? 'checked' : '' }}
                                   class="w-4 h-4 rounded" style="accent-color:var(--sa-primary);">
                        </label>
                    </div>
                </div>
                <div class="flex justify-end">
                    <button type="submit" class="inline-flex h-9 items-center gap-2 rounded-lg px-4 text-sm font-semibold shadow-sm transition"
                            style="background:var(--sa-primary);color:var(--sa-primary-fg);">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>

        {{-- ─── WHATSAPP ───────────────────────────────────────────────────── --}}
        <div x-show="tab === 'whatsapp'" x-cloak>
            <form method="POST" action="{{ route('super-admin.settings.update') }}" class="space-y-5">
                @csrf
                <input type="hidden" name="_tab" value="whatsapp">
                <input type="hidden" name="app_name" value="{{ $settings['app_name'] ?? 'MenuPro' }}">
                <input type="hidden" name="app_url" value="{{ $settings['app_url'] ?? '' }}">
                <input type="hidden" name="contact_email" value="{{ $settings['contact_email'] ?? '' }}">

                <div class="rounded-2xl border p-5 shadow-sm" style="border-color:var(--sa-border);background:var(--sa-card);">
                    <h2 class="text-base font-semibold mb-1" style="color:var(--sa-fg);">WhatsApp Business API</h2>
                    <p class="text-xs mb-4" style="color:var(--sa-muted-fg);">Notifications automatiques aux clients et restaurateurs (confirmation, changement de statut…).</p>
                    <div class="space-y-4">
                        <label class="flex items-center justify-between p-3.5 rounded-xl cursor-pointer"
                               style="background:var(--sa-muted);border:1px solid var(--sa-border);">
                            <div>
                                <p class="text-sm font-medium" style="color:var(--sa-fg);">Activer les notifications WhatsApp</p>
                                <p class="text-xs mt-0.5" style="color:var(--sa-muted-fg);">Envoi automatique lors des changements de statut.</p>
                            </div>
                            <input type="hidden" name="whatsapp_enabled" value="0">
                            <input type="checkbox" name="whatsapp_enabled" value="1"
                                   {{ ($settings['whatsapp_enabled'] ?? false) ? 'checked' : '' }}
                                   class="w-4 h-4 rounded" style="accent-color:var(--sa-primary);">
                        </label>
                        <div>
                            <label class="block text-xs font-medium mb-1.5" style="color:var(--sa-muted-fg);">Phone Number ID</label>
                            <input type="text" name="whatsapp_phone_id" value="{{ old('whatsapp_phone_id', $settings['whatsapp_phone_id'] ?? '') }}"
                                   class="w-full h-10 px-3 rounded-xl border text-sm outline-none"
                                   style="background:var(--sa-muted);border-color:var(--sa-border);color:var(--sa-fg);" placeholder="123456789012345">
                            <p class="text-[10px] mt-1" style="color:var(--sa-muted-fg);">Meta Business Suite &rarr; WhatsApp &rarr; Paramètres du compte</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1.5" style="color:var(--sa-muted-fg);">Token d'accès permanent</label>
                            <input type="password" name="whatsapp_api_key" value="{{ old('whatsapp_api_key', $settings['whatsapp_api_key'] ?? '') }}"
                                   class="w-full h-10 px-3 rounded-xl border text-sm outline-none"
                                   style="background:var(--sa-muted);border-color:var(--sa-border);color:var(--sa-fg);" placeholder="EAAxxxxxxx...">
                            <p class="text-[10px] mt-1" style="color:var(--sa-muted-fg);">Meta for Developers &rarr; Votre App &rarr; WhatsApp &rarr; Configuration</p>
                        </div>
                    </div>
                </div>

                {{-- Twilio WhatsApp --}}
                <div class="rounded-2xl border p-5 shadow-sm" style="border-color:var(--sa-border);background:var(--sa-card);">
                    <h2 class="text-base font-semibold mb-1" style="color:var(--sa-fg);">Twilio WhatsApp</h2>
                    <p class="text-xs mb-4" style="color:var(--sa-muted-fg);">Utilisé pour les codes OTP (récupération de mot de passe). Prioritaire sur Meta si configuré.</p>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium mb-1.5" style="color:var(--sa-muted-fg);">Account SID</label>
                            <input type="text" name="twilio_sid"
                                   value="{{ old('twilio_sid', $settings['twilio_sid'] ?? '') }}"
                                   class="w-full h-10 px-3 rounded-xl border text-sm outline-none font-mono"
                                   style="background:var(--sa-muted);border-color:var(--sa-border);color:var(--sa-fg);"
                                   placeholder="ACxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx">
                            <p class="text-[10px] mt-1" style="color:var(--sa-muted-fg);">Twilio Console → Account Info</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1.5" style="color:var(--sa-muted-fg);">Auth Token</label>
                            <input type="password" name="twilio_auth_token"
                                   value="{{ old('twilio_auth_token', $settings['twilio_auth_token'] ?? '') }}"
                                   class="w-full h-10 px-3 rounded-xl border text-sm outline-none font-mono"
                                   style="background:var(--sa-muted);border-color:var(--sa-border);color:var(--sa-fg);"
                                   placeholder="••••••••••••••••••••••••••••••••">
                            <p class="text-[10px] mt-1" style="color:var(--sa-muted-fg);">Twilio Console → cliquez l'icône œil pour révéler</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1.5" style="color:var(--sa-muted-fg);">Numéro expéditeur</label>
                            <input type="text" name="twilio_whatsapp_from"
                                   value="{{ old('twilio_whatsapp_from', $settings['twilio_whatsapp_from'] ?? 'whatsapp:+14155238886') }}"
                                   class="w-full h-10 px-3 rounded-xl border text-sm outline-none font-mono"
                                   style="background:var(--sa-muted);border-color:var(--sa-border);color:var(--sa-fg);"
                                   placeholder="whatsapp:+14155238886">
                            <p class="text-[10px] mt-1" style="color:var(--sa-muted-fg);">Sandbox : whatsapp:+14155238886 · Production : votre numéro approuvé</p>
                        </div>

                        {{-- Bouton test --}}
                        <div x-data="{ phone: '', loading: false, result: null }"
                             class="pt-2 border-t" style="border-color:var(--sa-border);">
                            <p class="text-xs font-medium mb-2" style="color:var(--sa-fg);">Tester l'envoi</p>
                            <div class="flex gap-2">
                                <input type="text" x-model="phone"
                                       placeholder="07 00 00 00 00"
                                       inputmode="tel"
                                       class="flex-1 h-9 px-3 rounded-xl border text-sm outline-none"
                                       style="background:var(--sa-muted);border-color:var(--sa-border);color:var(--sa-fg);">
                                <button type="button"
                                        :disabled="loading || !phone"
                                        @click="
                                            loading = true; result = null;
                                            fetch('{{ route('super-admin.settings.test-whatsapp') }}', {
                                                method: 'POST',
                                                headers: {
                                                    'Content-Type': 'application/json',
                                                    'X-CSRF-TOKEN': '{{ csrf_token() }}',
                                                    'Accept': 'application/json'
                                                },
                                                body: JSON.stringify({ phone })
                                            })
                                            .then(r => r.json())
                                            .then(d => { result = d; loading = false; })
                                            .catch(e => { result = { success: false, message: e.message }; loading = false; })
                                        "
                                        class="h-9 px-4 rounded-xl text-xs font-semibold flex items-center gap-1.5 transition"
                                        style="background:var(--sa-primary);color:var(--sa-primary-fg);">
                                    <svg x-show="!loading" class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                    </svg>
                                    <svg x-show="loading" x-cloak class="w-3.5 h-3.5 animate-spin" fill="none" viewBox="0 0 24 24">
                                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                                    </svg>
                                    <span x-text="loading ? 'Envoi...' : 'Tester'"></span>
                                </button>
                            </div>
                            <div x-show="result" x-cloak class="mt-2 p-2.5 rounded-lg text-xs font-medium"
                                 :style="result?.success
                                    ? 'background:rgba(16,185,129,0.1);color:#059669;border:1px solid rgba(16,185,129,0.3)'
                                    : 'background:rgba(239,68,68,0.1);color:#dc2626;border:1px solid rgba(239,68,68,0.3)'"
                                 x-text="result?.message">
                            </div>
                        </div>
                    </div>
                </div>

                {{-- Guide de configuration --}}
                <div class="rounded-2xl border p-5" style="background:rgba(217,119,6,0.06);border-color:rgba(217,119,6,0.25);">
                    <h3 class="text-sm font-semibold mb-3" style="color:var(--sa-warning);">Comment configurer WhatsApp Business API ?</h3>
                    <ol class="text-xs space-y-2 list-decimal list-inside" style="color:var(--sa-fg);">
                        <li>Créez une app sur <strong>developers.facebook.com</strong> (type : Business)</li>
                        <li>Ajoutez le produit <strong>WhatsApp</strong> à votre app</li>
                        <li>Copiez le <strong>Phone Number ID</strong> dans WhatsApp → Configuration</li>
                        <li>Générez un <strong>token permanent</strong> (System User dans Business Settings)</li>
                        <li>Collez les valeurs ci-dessus et activez les notifications</li>
                    </ol>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="inline-flex h-9 items-center gap-2 rounded-lg px-4 text-sm font-semibold shadow-sm transition"
                            style="background:var(--sa-primary);color:var(--sa-primary-fg);">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>

        {{-- ─── LIVRAISON & CARTES ─────────────────────────────────────────── --}}
        <div x-show="tab === 'delivery'" x-cloak>
            <form method="POST" action="{{ route('super-admin.settings.update') }}" class="space-y-5">
                @csrf
                <input type="hidden" name="_tab" value="delivery">
                <input type="hidden" name="app_name" value="{{ $settings['app_name'] ?? 'MenuPro' }}">
                <input type="hidden" name="app_url" value="{{ $settings['app_url'] ?? '' }}">
                <input type="hidden" name="contact_email" value="{{ $settings['contact_email'] ?? '' }}">

                <div class="grid grid-cols-1 xl:grid-cols-2 gap-5">
                    {{-- Mapbox --}}
                    <div class="rounded-2xl border p-5 shadow-sm" style="border-color:var(--sa-border);background:var(--sa-card);">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="w-2 h-2 rounded-full" style="background:#6366f1;"></span>
                            <h2 class="text-base font-semibold" style="color:var(--sa-fg);">Mapbox — Cartes interactives</h2>
                        </div>
                        <p class="text-xs mb-4" style="color:var(--sa-muted-fg);">
                            Cartes pour l'app livraison. Gratuit jusqu'à 50 000 chargements/mois.
                            <a href="https://account.mapbox.com/access-tokens/" target="_blank" class="underline" style="color:var(--sa-primary);">Créer un token</a>
                        </p>
                        <div class="space-y-4">
                            <div>
                                <label class="block text-xs font-medium mb-1.5" style="color:var(--sa-muted-fg);">Access Token public <span class="opacity-60">(commence par pk.)</span></label>
                                <input type="text" name="mapbox_public_token" value="{{ old('mapbox_public_token', $settings['mapbox_public_token'] ?? '') }}"
                                       class="w-full h-10 px-3 rounded-xl border text-sm outline-none font-mono"
                                       style="background:var(--sa-muted);border-color:var(--sa-border);color:var(--sa-fg);" placeholder="pk.eyJ1Ijoixxxxx...">
                            </div>
                            <div>
                                <label class="block text-xs font-medium mb-1.5" style="color:var(--sa-muted-fg);">Style de carte</label>
                                <select name="mapbox_style" class="w-full h-10 px-3 rounded-xl border text-sm outline-none"
                                        style="background:var(--sa-muted);border-color:var(--sa-border);color:var(--sa-fg);">
                                    @php $currentStyle = $settings['mapbox_style'] ?? 'streets-v12'; @endphp
                                    @foreach(['streets-v12' => 'Streets (défaut)', 'light-v11' => 'Light', 'dark-v11' => 'Dark', 'satellite-v9' => 'Satellite', 'navigation-day-v1' => 'Navigation (jour)', 'navigation-night-v1' => 'Navigation (nuit)'] as $val => $lbl)
                                        <option value="{{ $val }}" {{ $currentStyle === $val ? 'selected' : '' }}>{{ $lbl }}</option>
                                    @endforeach
                                </select>
                            </div>
                            @if(!empty($settings['mapbox_public_token']))
                                <div class="flex items-center gap-2 rounded-lg border px-3 py-2 text-xs"
                                     style="background:rgba(61,158,98,0.06);border-color:rgba(61,158,98,0.25);color:var(--sa-success);">
                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Token configuré — cartes actives.
                                </div>
                            @else
                                <div class="flex items-center gap-2 rounded-lg border px-3 py-2 text-xs"
                                     style="background:rgba(217,119,6,0.06);border-color:rgba(217,119,6,0.25);color:var(--sa-warning);">
                                    <svg class="w-3.5 h-3.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Aucun token — fallback OpenStreetMap.
                                </div>
                            @endif
                        </div>
                    </div>

                    {{-- Pusher --}}
                    @php $pusherOk = !empty($settings['pusher_key'] ?? '') && !empty($settings['pusher_secret'] ?? ''); @endphp
                    <div class="rounded-2xl border p-5 shadow-sm" style="border-color:var(--sa-border);background:var(--sa-card);">
                        <div class="flex items-start justify-between gap-4 mb-4">
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="w-2 h-2 rounded-full" style="background:#6366f1;"></span>
                                    <h2 class="text-base font-semibold" style="color:var(--sa-fg);">Pusher — Temps réel (app livreur)</h2>
                                </div>
                                <p class="text-xs" style="color:var(--sa-muted-fg);">
                                    Notifications en direct : nouvelle commande, mise à jour statut, position GPS.
                                    <a href="https://dashboard.pusher.com/" target="_blank" class="underline" style="color:var(--sa-primary);">Dashboard Pusher</a>
                                </p>
                            </div>
                            @if($pusherOk)
                                <span class="flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold shrink-0"
                                      style="background:rgba(61,158,98,0.10);color:var(--sa-success);">
                                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Configuré
                                </span>
                            @else
                                <span class="flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold shrink-0"
                                      style="background:rgba(217,119,6,0.10);color:var(--sa-warning);">
                                    Non configuré
                                </span>
                            @endif
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block text-xs font-medium mb-1.5" style="color:var(--sa-muted-fg);">App ID <span style="color:var(--sa-danger);">*</span></label>
                                <input type="text" name="pusher_app_id" value="{{ old('pusher_app_id', $settings['pusher_app_id'] ?? '') }}"
                                       class="w-full h-10 px-3 rounded-xl border text-sm outline-none font-mono"
                                       style="background:var(--sa-muted);border-color:var(--sa-border);color:var(--sa-fg);" placeholder="1234567">
                                <p class="text-[10px] mt-1" style="color:var(--sa-muted-fg);">Pusher Dashboard → App Keys</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium mb-1.5" style="color:var(--sa-muted-fg);">Cluster</label>
                                <input type="text" name="pusher_cluster" value="{{ old('pusher_cluster', $settings['pusher_cluster'] ?? 'ap2') }}"
                                       class="w-full h-10 px-3 rounded-xl border text-sm outline-none font-mono"
                                       style="background:var(--sa-muted);border-color:var(--sa-border);color:var(--sa-fg);" placeholder="ap2">
                                <p class="text-[10px] mt-1" style="color:var(--sa-muted-fg);">ap2 = Asie Pacifique Mumbai — recommandé pour Côte d'Ivoire</p>
                            </div>
                            <div>
                                <label class="block text-xs font-medium mb-1.5" style="color:var(--sa-muted-fg);">App Key <span class="opacity-60">(public)</span> <span style="color:var(--sa-danger);">*</span></label>
                                <input type="text" name="pusher_key" value="{{ old('pusher_key', $settings['pusher_key'] ?? '') }}"
                                       class="w-full h-10 px-3 rounded-xl border text-sm outline-none font-mono"
                                       style="background:var(--sa-muted);border-color:var(--sa-border);color:var(--sa-fg);" placeholder="abc123def456…">
                            </div>
                            <div>
                                <label class="block text-xs font-medium mb-1.5" style="color:var(--sa-muted-fg);">App Secret <span class="opacity-60">(privé)</span> <span style="color:var(--sa-danger);">*</span></label>
                                <input type="password" name="pusher_secret" value="{{ old('pusher_secret', $settings['pusher_secret'] ?? '') }}"
                                       class="w-full h-10 px-3 rounded-xl border text-sm outline-none font-mono"
                                       style="background:var(--sa-muted);border-color:var(--sa-border);color:var(--sa-fg);" placeholder="••••••••••••••••">
                            </div>
                        </div>
                        @if($pusherOk)
                        <div class="mt-4 rounded-lg border px-3 py-2.5 text-xs flex items-center gap-2"
                             style="background:rgba(61,158,98,0.06);border-color:rgba(61,158,98,0.25);color:var(--sa-success);">
                            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Pusher actif — cluster <strong>{{ $settings['pusher_cluster'] ?? 'ap2' }}</strong>, app key <code class="font-mono">{{ substr($settings['pusher_key'], 0, 8) }}…</code>
                        </div>
                        @endif
                    </div>

                    {{-- Geoapify --}}
                    <div class="rounded-2xl border p-5 shadow-sm" style="border-color:var(--sa-border);background:var(--sa-card);">
                        <div class="flex items-center gap-2 mb-1">
                            <span class="w-2 h-2 rounded-full" style="background:#0ea5e9;"></span>
                            <h2 class="text-base font-semibold" style="color:var(--sa-fg);">Geoapify — Géocodage</h2>
                        </div>
                        <p class="text-xs mb-4" style="color:var(--sa-muted-fg);">
                            Autocomplétion d'adresses dans le dashboard restaurant.
                            <a href="https://www.geoapify.com/get-started-with-maps-api" target="_blank" class="underline" style="color:var(--sa-primary);">Clé gratuite (3 000 req/jour)</a>
                        </p>
                        <div>
                            <label class="block text-xs font-medium mb-1.5" style="color:var(--sa-muted-fg);">API Key Geoapify</label>
                            <input type="text" name="geoapify_api_key" value="{{ old('geoapify_api_key', $settings['geoapify_api_key'] ?? '') }}"
                                   class="w-full h-10 px-3 rounded-xl border text-sm outline-none"
                                   style="background:var(--sa-muted);border-color:var(--sa-border);color:var(--sa-fg);" placeholder="Votre clé API">
                            <p class="text-[10px] mt-1" style="color:var(--sa-muted-fg);">Si non configuré, Nominatim/OpenStreetMap est utilisé en fallback.</p>
                        </div>
                    </div>
                </div>

                {{-- Firebase FCM --}}
                <div class="rounded-2xl border p-5 shadow-sm" style="border-color:var(--sa-border);background:var(--sa-card);">
                    @php $fcmV1Ok = !empty($settings['firebase_project_id'] ?? '') && !empty($settings['firebase_service_account_json'] ?? ''); @endphp
                    <div class="flex items-start justify-between gap-4 mb-4">
                        <div>
                            <div class="flex items-center gap-2 mb-1">
                                <span class="w-2 h-2 rounded-full" style="background:#f59e0b;"></span>
                                <h2 class="text-base font-semibold" style="color:var(--sa-fg);">Firebase — Notifications Push (FCM v1)</h2>
                            </div>
                            <p class="text-xs" style="color:var(--sa-muted-fg);">
                                Envoi push aux livreurs et clients.
                                <a href="https://console.firebase.google.com/" target="_blank" class="underline" style="color:var(--sa-primary);">Firebase Console</a>
                            </p>
                        </div>
                        @if($fcmV1Ok)
                            <span class="flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold shrink-0"
                                  style="background:rgba(61,158,98,0.10);color:var(--sa-success);">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                Configuré
                            </span>
                        @else
                            <span class="flex items-center gap-1.5 rounded-full px-2.5 py-1 text-xs font-semibold shrink-0"
                                  style="background:rgba(217,119,6,0.10);color:var(--sa-warning);">
                                Non configuré
                            </span>
                        @endif
                    </div>
                    <div class="space-y-4">
                        <div>
                            <label class="block text-xs font-medium mb-1.5" style="color:var(--sa-muted-fg);">Project ID</label>
                            <input type="text" name="firebase_project_id" value="{{ old('firebase_project_id', $settings['firebase_project_id'] ?? '') }}"
                                   class="w-full h-10 px-3 rounded-xl border text-sm outline-none font-mono"
                                   style="background:var(--sa-muted);border-color:var(--sa-border);color:var(--sa-fg);" placeholder="mon-projet-firebase">
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1.5" style="color:var(--sa-muted-fg);">Service Account JSON</label>
                            <textarea name="firebase_service_account_json" rows="4"
                                   class="w-full px-3 py-2 rounded-xl border text-xs outline-none font-mono resize-none"
                                   style="background:var(--sa-muted);border-color:var(--sa-border);color:var(--sa-fg);"
                                   placeholder='{"type":"service_account","project_id":"..."}'>{{ old('firebase_service_account_json', $settings['firebase_service_account_json'] ?? '') }}</textarea>
                            <p class="text-[10px] mt-1" style="color:var(--sa-muted-fg);">Contenu complet du fichier JSON depuis Firebase → Comptes de service → Générer une clé privée.</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1.5" style="color:var(--sa-muted-fg);">Firebase API Key (Web)</label>
                            <input type="text" name="firebase_api_key" value="{{ old('firebase_api_key', $settings['firebase_api_key'] ?? '') }}"
                                   class="w-full h-10 px-3 rounded-xl border text-sm outline-none font-mono"
                                   style="background:var(--sa-muted);border-color:var(--sa-border);color:var(--sa-fg);" placeholder="AIzaSy...">
                            <p class="text-[10px] mt-1" style="color:var(--sa-muted-fg);">Clé API Web depuis Firebase Console → Paramètres du projet → Applications Web.</p>
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1.5" style="color:var(--sa-muted-fg);">Firebase App ID</label>
                            <input type="text" name="firebase_app_id" value="{{ old('firebase_app_id', $settings['firebase_app_id'] ?? '') }}"
                                   class="w-full h-10 px-3 rounded-xl border text-sm outline-none font-mono"
                                   style="background:var(--sa-muted);border-color:var(--sa-border);color:var(--sa-fg);" placeholder="1:123456789:web:abc123...">
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1.5" style="color:var(--sa-muted-fg);">Firebase Sender ID (Messaging)</label>
                            <input type="text" name="firebase_sender_id" value="{{ old('firebase_sender_id', $settings['firebase_sender_id'] ?? '') }}"
                                   class="w-full h-10 px-3 rounded-xl border text-sm outline-none font-mono"
                                   style="background:var(--sa-muted);border-color:var(--sa-border);color:var(--sa-fg);" placeholder="123456789012">
                        </div>
                        <div>
                            <label class="block text-xs font-medium mb-1.5" style="color:var(--sa-muted-fg);">VAPID Public Key (Web Push)</label>
                            <input type="text" name="firebase_vapid_key" value="{{ old('firebase_vapid_key', $settings['firebase_vapid_key'] ?? '') }}"
                                   class="w-full h-10 px-3 rounded-xl border text-sm outline-none font-mono"
                                   style="background:var(--sa-muted);border-color:var(--sa-border);color:var(--sa-fg);" placeholder="BNxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx...">
                            <p class="text-[10px] mt-1" style="color:var(--sa-muted-fg);">Depuis Firebase Console → Cloud Messaging → Configuration Web → Paire de clés Web Push.</p>
                        </div>
                        <details class="text-xs">
                            <summary class="cursor-pointer" style="color:var(--sa-muted-fg);">Ancienne clé serveur Legacy (désactivée par Google depuis juin 2024)</summary>
                            <div class="mt-3">
                                <input type="password" name="firebase_server_key" value="{{ old('firebase_server_key', $settings['firebase_server_key'] ?? '') }}"
                                       class="w-full h-10 px-3 rounded-xl border text-sm outline-none font-mono"
                                       style="background:var(--sa-muted);border-color:var(--sa-border);color:var(--sa-fg);" placeholder="AAAAxxxxxxxxxxxxxxxx...">
                                <p class="mt-1" style="color:var(--sa-danger);">Cette API est désactivée. Migrez vers FCM v1 ci-dessus.</p>
                            </div>
                        </details>
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="submit" class="inline-flex h-9 items-center gap-2 rounded-lg px-4 text-sm font-semibold shadow-sm transition"
                            style="background:var(--sa-primary);color:var(--sa-primary-fg);">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Enregistrer
                    </button>
                </div>
            </form>
        </div>

    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('form[action*="parametres"]').forEach(function (form) {
                ajaxForm(form, {
                    onSuccess: function (data) {
                        if (form.enctype && form.enctype.includes('multipart')) {
                            setTimeout(function () { window.location.reload(); }, 1500);
                        }
                    }
                });
            });
        });
    </script>
    @endpush

</x-layouts.admin-super>
