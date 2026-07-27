<x-layouts.admin-super title="Paramètres de paiement">

    {{-- Page Header --}}
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold" style="color:var(--sa-fg);">Paramètres de paiement</h1>
            <p class="mt-1 text-sm" style="color:var(--sa-muted-fg);">Configurez les clés API et options des gateways de paiement.</p>
        </div>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="mb-5 flex items-center gap-3 rounded-xl border px-4 py-3 text-sm"
             style="background:rgba(61,158,98,0.08);border-color:rgba(61,158,98,0.25);color:var(--sa-success);">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-5 flex items-center gap-3 rounded-xl border px-4 py-3 text-sm"
             style="background:rgba(220,38,38,0.08);border-color:rgba(220,38,38,0.25);color:var(--sa-danger);">
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('error') }}
        </div>
    @endif

    @if($errors->any())
        <div class="mb-5 rounded-xl border px-4 py-3 text-sm"
             style="background:rgba(220,38,38,0.08);border-color:rgba(220,38,38,0.25);color:var(--sa-danger);">
            <ul class="list-inside list-disc space-y-1">
                @foreach($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Gateway Cards --}}
    <div class="grid grid-cols-1 gap-6 xl:grid-cols-2">

        @foreach($settings as $setting)
            @php
                $gatewayLabels = [
                    'jeko_marketplace' => 'Jeko Marketplace',
                    'jeko_normal'      => 'Jeko Normal',
                    'wave'             => 'Wave CI',
                    'cinetpay'         => 'CinetPay',
                ];
                $label = $gatewayLabels[$setting->gateway] ?? ucfirst(str_replace('_', ' ', $setting->gateway));
            @endphp

            <div class="rounded-2xl border shadow-sm" style="border-color:var(--sa-border);background:var(--sa-card);">

                {{-- Card Header --}}
                <div class="flex items-center justify-between border-b px-5 py-4"
                     style="border-color:var(--sa-border);">
                    <div class="flex items-center gap-3">
                        <span class="rounded-lg px-3 py-1 text-sm font-semibold"
                              style="background:rgba(var(--sa-primary-rgb,99,102,241),0.1);color:var(--sa-primary);">
                            {{ $label }}
                        </span>
                        @if($setting->is_active)
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-medium"
                                  style="background:rgba(61,158,98,0.12);color:var(--sa-success);">
                                Actif
                            </span>
                        @else
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-medium"
                                  style="background:rgba(156,163,175,0.15);color:var(--sa-muted-fg);">
                                Inactif
                            </span>
                        @endif
                        @if($setting->isSandbox())
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-medium"
                                  style="background:rgba(245,158,11,0.12);color:var(--sa-warning);">
                                Sandbox
                            </span>
                        @else
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-medium"
                                  style="background:rgba(16,185,129,0.12);color:var(--sa-success);">
                                Production
                            </span>
                        @endif
                    </div>
                </div>

                {{-- Card Body / Form --}}
                <form method="POST"
                      action="{{ route('super-admin.payment-settings.update', $setting) }}"
                      class="space-y-4 p-5">
                    @csrf
                    @method('PUT')

                    {{-- Active toggle --}}
                    <div class="flex items-center justify-between">
                        <label class="text-sm font-medium" style="color:var(--sa-fg);">
                            Activer ce gateway
                        </label>
                        <label class="relative inline-flex cursor-pointer items-center">
                            <input type="hidden" name="is_active" value="0">
                            <input type="checkbox" name="is_active" value="1"
                                   class="sr-only peer"
                                   {{ $setting->is_active ? 'checked' : '' }}>
                            <div class="peer h-6 w-11 rounded-full after:absolute after:left-[2px] after:top-[2px] after:h-5 after:w-5 after:rounded-full after:border after:bg-white after:transition-all after:content-[''] peer-checked:after:translate-x-full peer-checked:after:border-white peer-checked:bg-[color:var(--sa-primary)] bg-[color:var(--sa-border)]"
                                 style="border:1px solid var(--sa-border);">
                            </div>
                        </label>
                    </div>

                    {{-- Mode --}}
                    <div>
                        <label class="mb-1.5 block text-xs font-medium" style="color:var(--sa-muted-fg);">
                            Mode
                        </label>
                        <select name="mode"
                                class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2"
                                style="border-color:var(--sa-border);background:var(--sa-card);color:var(--sa-fg);focus-ring-color:var(--sa-primary);">
                            <option value="sandbox" {{ $setting->mode === 'sandbox' ? 'selected' : '' }}>Sandbox</option>
                            <option value="production" {{ $setting->mode === 'production' ? 'selected' : '' }}>Production</option>
                        </select>
                    </div>

                    {{-- API Key --}}
                    <div>
                        <label class="mb-1.5 block text-xs font-medium" style="color:var(--sa-muted-fg);">
                            Clé API
                            @if(!empty($setting->api_key))
                                <span class="ml-2 rounded-full px-2 py-0.5 text-xs"
                                      style="background:rgba(61,158,98,0.12);color:var(--sa-success);">
                                    Configurée
                                </span>
                            @else
                                <span class="ml-2 rounded-full px-2 py-0.5 text-xs"
                                      style="background:rgba(220,38,38,0.08);color:var(--sa-danger);">
                                    Non configurée
                                </span>
                            @endif
                        </label>
                        <input type="password"
                               name="api_key"
                               placeholder="&#x2022;&#x2022;&#x2022;&#x2022;&#x2022;&#x2022;&#x2022;&#x2022;"
                               autocomplete="new-password"
                               class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2"
                               style="border-color:var(--sa-border);background:var(--sa-card);color:var(--sa-fg);">
                        <p class="mt-1 text-xs" style="color:var(--sa-muted-fg);">
                            Laissez vide pour conserver la valeur actuelle.
                        </p>
                    </div>

                    {{-- Webhook Secret --}}
                    <div>
                        <label class="mb-1.5 block text-xs font-medium" style="color:var(--sa-muted-fg);">
                            Webhook Secret
                            @if(!empty($setting->webhook_secret))
                                <span class="ml-2 rounded-full px-2 py-0.5 text-xs"
                                      style="background:rgba(61,158,98,0.12);color:var(--sa-success);">
                                    Configuré
                                </span>
                            @else
                                <span class="ml-2 rounded-full px-2 py-0.5 text-xs"
                                      style="background:rgba(220,38,38,0.08);color:var(--sa-danger);">
                                    Non configuré
                                </span>
                            @endif
                        </label>
                        <input type="password"
                               name="webhook_secret"
                               placeholder="&#x2022;&#x2022;&#x2022;&#x2022;&#x2022;&#x2022;&#x2022;&#x2022;"
                               autocomplete="new-password"
                               class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2"
                               style="border-color:var(--sa-border);background:var(--sa-card);color:var(--sa-fg);">
                        <p class="mt-1 text-xs" style="color:var(--sa-muted-fg);">
                            Laissez vide pour conserver la valeur actuelle.
                        </p>
                    </div>

                    {{-- Champs spécifiques Jeko (API Key ID + Store ID) --}}
                    @if(str_starts_with($setting->gateway, 'jeko'))
                    <div>
                        <label class="mb-1.5 block text-xs font-medium" style="color:var(--sa-muted-fg);">
                            API Key ID <span class="text-[10px]" style="color:var(--sa-muted-fg);">(X-API-KEY-ID — UUID)</span>
                        </label>
                        <input type="text"
                               name="merchant_id"
                               value="{{ old('merchant_id', $setting->merchant_id) }}"
                               placeholder="ex: 59ae202a-f583-4a15-970f-9e99bd1e0baa"
                               class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 font-mono"
                               style="border-color:var(--sa-border);background:var(--sa-card);color:var(--sa-fg);">
                        <p class="mt-1 text-xs" style="color:var(--sa-muted-fg);">Identifiant de votre clé API (cockpit.jeko.africa → Paramètres → API & Webhooks).</p>
                    </div>

                    <div>
                        <label class="mb-1.5 block text-xs font-medium" style="color:var(--sa-muted-fg);">
                            Store ID <span class="text-[10px]" style="color:var(--sa-muted-fg);">(UUID du magasin)</span>
                        </label>
                        <input type="text"
                               name="store_id"
                               value="{{ old('store_id', $setting->config['store_id'] ?? '') }}"
                               placeholder="ex: 59ae202a-f583-4a15-970f-9e99bd1e0baa"
                               class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 font-mono"
                               style="border-color:var(--sa-border);background:var(--sa-card);color:var(--sa-fg);">
                        <p class="mt-1 text-xs" style="color:var(--sa-muted-fg);">ID du magasin Jeko (cockpit.jeko.africa → Magasins).</p>
                    </div>
                    @endif

                    {{-- Champ Merchant ID générique (non-Jeko : Wave, MoneyFusion, etc.) --}}
                    @if(!str_starts_with($setting->gateway, 'jeko') && $setting->gateway !== 'wave')
                    <div>
                        <label class="mb-1.5 block text-xs font-medium" style="color:var(--sa-muted-fg);">
                            Merchant ID
                        </label>
                        <input type="text"
                               name="merchant_id"
                               value="{{ old('merchant_id', $setting->merchant_id) }}"
                               placeholder="Identifiant marchand"
                               class="w-full rounded-lg border px-3 py-2 text-sm focus:outline-none focus:ring-2 font-mono"
                               style="border-color:var(--sa-border);background:var(--sa-card);color:var(--sa-fg);">
                    </div>
                    @endif

                    {{-- Submit --}}
                    <div class="flex justify-end pt-2">
                        <button type="submit"
                                class="rounded-lg px-5 py-2 text-sm font-medium text-white transition hover:opacity-90"
                                style="background:var(--sa-primary);">
                            Enregistrer
                        </button>
                    </div>

                </form>
            </div>
        @endforeach

    </div>

</x-layouts.admin-super>
