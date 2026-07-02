<x-layouts.admin-super title="Notifications Push">
    <!-- Header -->
    <div class="mb-6">
        <h1 class="text-2xl font-bold" style="color:var(--sa-fg);">Notifications push</h1>
        <p class="mt-1 text-sm" style="color:var(--sa-muted-fg);">Envoyez des notifications ciblées à vos utilisateurs</p>
    </div>

    <!-- 3 StatCards -->
    <div class="mb-6 grid grid-cols-1 gap-4 sm:grid-cols-3">
        <!-- Tokens livreurs (primary) -->
        <div class="rounded-2xl border p-5 shadow-sm" style="border-color:var(--sa-border);background:var(--sa-card);">
            <div class="flex size-10 items-center justify-center rounded-xl" style="background:color-mix(in oklch,var(--sa-primary) 12%,transparent);">
                <svg class="size-5" style="color:var(--sa-primary);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
            </div>
            <p class="mt-3 text-2xl font-bold" style="color:var(--sa-fg);">{{ $stats['drivers_with_token'] + $stats['customers_with_token'] }}</p>
            <p class="mt-0.5 text-sm font-medium" style="color:var(--sa-muted-fg);">Tokens push actifs</p>
            <p class="mt-1 text-xs" style="color:var(--sa-muted-fg);">Livreurs + Clients</p>
        </div>

        <!-- Livreurs en ligne (info) -->
        <div class="rounded-2xl border p-5 shadow-sm" style="border-color:var(--sa-border);background:var(--sa-card);">
            <div class="flex size-10 items-center justify-center rounded-xl" style="background:color-mix(in oklch,var(--sa-info) 12%,transparent);">
                <svg class="size-5" style="color:var(--sa-info);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <p class="mt-3 text-2xl font-bold" style="color:var(--sa-fg);">{{ $stats['customers_with_token'] }}</p>
            <p class="mt-0.5 text-sm font-medium" style="color:var(--sa-muted-fg);">Clients avec token</p>
            <p class="mt-1 text-xs" style="color:var(--sa-muted-fg);">App client enregistrés</p>
        </div>

        <!-- FCM Status (success / warning) -->
        <div class="rounded-2xl border p-5 shadow-sm" style="border-color:var(--sa-border);background:var(--sa-card);">
            <div class="flex size-10 items-center justify-center rounded-xl"
                 style="background:color-mix(in oklch,{{ $stats['fcm_configured'] ? 'var(--sa-success)' : 'var(--sa-warning)' }} 12%,transparent);">
                <svg class="size-5" style="color:{{ $stats['fcm_configured'] ? 'var(--sa-success)' : 'var(--sa-warning)' }};" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.636 18.364a9 9 0 010-12.728m12.728 0a9 9 0 010 12.728M12 12a1 1 0 110-2 1 1 0 010 2z"/>
                </svg>
            </div>
            <p class="mt-3 text-2xl font-bold" style="color:var(--sa-fg);">{{ $stats['drivers_online'] }}</p>
            <p class="mt-0.5 text-sm font-medium" style="color:var(--sa-muted-fg);">Livreurs en ligne</p>
            <p class="mt-1 text-xs" style="color:{{ $stats['fcm_configured'] ? 'var(--sa-success)' : 'var(--sa-warning)' }};">
                Firebase {{ $stats['fcm_configured'] ? 'configuré' : 'non configuré' }}
            </p>
        </div>
    </div>

    @if(!$stats['fcm_configured'])
    <!-- Firebase warning banner -->
    <div class="mb-6 flex items-start gap-3 rounded-xl border p-4"
         style="border-color:color-mix(in oklch,var(--sa-warning) 40%,transparent);background:color-mix(in oklch,var(--sa-warning) 8%,transparent);">
        <svg class="mt-0.5 size-5 shrink-0" style="color:var(--sa-warning);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        <div>
            <p class="text-sm font-semibold" style="color:var(--sa-warning);">Firebase non configuré</p>
            <p class="mt-0.5 text-sm" style="color:var(--sa-muted-fg);">
                L'envoi push est désactivé. Ajoutez la clé serveur dans
                <a href="{{ route('super-admin.settings') }}#delivery" class="font-medium underline" style="color:var(--sa-warning);">Paramètres → Livraison & Cartes</a>.
            </p>
        </div>
    </div>
    @endif

    <!-- Split layout: Composer (2/5) + History (3/5) -->
    <div class="grid grid-cols-1 gap-6 lg:grid-cols-5">

        <!-- Left: Composer card -->
        <div class="rounded-2xl border p-5 shadow-sm lg:col-span-2" style="border-color:var(--sa-border);background:var(--sa-card);">
            <h2 class="mb-1 text-lg font-semibold" style="color:var(--sa-fg);">Nouvelle notification</h2>
            <p class="mb-5 text-sm" style="color:var(--sa-muted-fg);">Composez et envoyez</p>

            <form id="push-send-form" method="POST" action="{{ route('super-admin.push.send') }}" class="flex flex-col gap-4">
                @csrf

                <!-- Audience toggle buttons -->
                <div>
                    <label class="mb-1.5 block text-sm font-medium" style="color:var(--sa-fg);">Audience</label>
                    <div class="flex flex-wrap gap-2" x-data="{ audience: 'all_drivers' }">
                        @foreach([
                            'all_drivers'    => 'Livreurs',
                            'online_drivers' => 'En ligne',
                            'all_customers'  => 'Clients',
                            'all'            => 'Tous',
                        ] as $aud => $audLabel)
                        <button type="button"
                                x-on:click="audience = '{{ $aud }}'; document.getElementById('audienceInput').value = '{{ $aud }}'"
                                class="audience-btn rounded-lg border px-3 py-1.5 text-sm font-medium transition-colors"
                                :style="audience === '{{ $aud }}'
                                    ? 'border-color:var(--sa-primary);background:color-mix(in oklch,var(--sa-primary) 10%,transparent);color:var(--sa-primary);'
                                    : 'border-color:var(--sa-border);color:var(--sa-muted-fg);'">
                            {{ $audLabel }}
                        </button>
                        @endforeach
                        <input type="hidden" name="audience" id="audienceInput" value="all_drivers">
                    </div>
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium" style="color:var(--sa-fg);">Titre *</label>
                    <input type="text" name="title" required maxlength="100"
                           placeholder="ex : Nouvelle zone disponible !"
                           class="h-10 w-full rounded-lg px-3 text-sm outline-none transition"
                           style="border:1px solid var(--sa-border);background:var(--sa-bg);color:var(--sa-fg);">
                </div>

                <div>
                    <label class="mb-1.5 block text-sm font-medium" style="color:var(--sa-fg);">Message *</label>
                    <textarea name="body" required maxlength="500" rows="4"
                              placeholder="Contenu de la notification..."
                              class="w-full resize-none rounded-lg px-3 py-2 text-sm outline-none transition"
                              style="border:1px solid var(--sa-border);background:var(--sa-bg);color:var(--sa-fg);"></textarea>
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="mb-1.5 block text-sm font-medium" style="color:var(--sa-fg);">Clé data <span style="color:var(--sa-muted-fg);">(optionnel)</span></label>
                        <input type="text" name="data_key" maxlength="50" placeholder="ex : type"
                               class="h-10 w-full rounded-lg px-3 text-sm outline-none transition"
                               style="border:1px solid var(--sa-border);background:var(--sa-bg);color:var(--sa-fg);">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-sm font-medium" style="color:var(--sa-fg);">Valeur <span style="color:var(--sa-muted-fg);">(optionnel)</span></label>
                        <input type="text" name="data_val" maxlength="255" placeholder="ex : new_delivery"
                               class="h-10 w-full rounded-lg px-3 text-sm outline-none transition"
                               style="border:1px solid var(--sa-border);background:var(--sa-bg);color:var(--sa-fg);">
                    </div>
                </div>

                <div class="flex gap-2 pt-1">
                    <button type="submit"
                            {{ !$stats['fcm_configured'] ? 'disabled' : '' }}
                            class="inline-flex flex-1 h-10 items-center justify-center gap-2 rounded-lg text-sm font-medium shadow-sm transition disabled:opacity-40 disabled:cursor-not-allowed"
                            style="background:var(--sa-primary);color:var(--sa-primary-fg);">
                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                        </svg>
                        Envoyer maintenant
                    </button>
                </div>
            </form>
        </div>

        <!-- Right: History list -->
        <div class="rounded-2xl border p-5 shadow-sm lg:col-span-3" style="border-color:var(--sa-border);background:var(--sa-card);">
            <div class="mb-5">
                <h2 class="text-lg font-semibold" style="color:var(--sa-fg);">Historique</h2>
                <p class="text-sm" style="color:var(--sa-muted-fg);">Notifications récentes</p>
            </div>

            <!-- Firebase status card (in history column) -->
            <div class="mb-4 flex flex-wrap gap-3">
                <div class="flex items-center gap-2.5 rounded-xl px-4 py-3 text-sm"
                     style="border:1px solid {{ $stats['fcm_configured'] ? 'color-mix(in oklch,var(--sa-success) 40%,transparent)' : 'color-mix(in oklch,var(--sa-warning) 40%,transparent)' }};background:{{ $stats['fcm_configured'] ? 'color-mix(in oklch,var(--sa-success) 8%,transparent)' : 'color-mix(in oklch,var(--sa-warning) 8%,transparent)' }};">
                    <span class="size-2.5 rounded-full {{ $stats['fcm_configured'] ? 'animate-pulse' : '' }}"
                          style="background:{{ $stats['fcm_configured'] ? 'var(--sa-success)' : 'var(--sa-warning)' }};"></span>
                    <span class="font-medium" style="color:{{ $stats['fcm_configured'] ? 'var(--sa-success)' : 'var(--sa-warning)' }};">
                        Firebase {{ $stats['fcm_configured'] ? 'actif' : 'inactif' }}
                    </span>
                </div>
                <div class="flex items-center gap-2 rounded-xl px-4 py-3 text-sm"
                     style="border:1px solid var(--sa-border);background:var(--sa-muted);">
                    <span class="text-xl font-bold" style="color:var(--sa-primary);">{{ $stats['drivers_with_token'] }}</span>
                    <span style="color:var(--sa-muted-fg);">livreurs avec token</span>
                </div>
                <div class="flex items-center gap-2 rounded-xl px-4 py-3 text-sm"
                     style="border:1px solid var(--sa-border);background:var(--sa-muted);">
                    <span class="text-xl font-bold" style="color:var(--sa-success);">{{ $stats['drivers_online'] }}</span>
                    <span style="color:var(--sa-muted-fg);">en ligne</span>
                </div>
            </div>

            <!-- Notification history items (placeholder — no history model yet) -->
            <ul class="flex flex-col gap-3">
                {{-- If push notification history model exists, loop here --}}
                {{-- Example static/empty state --}}
                <li class="flex items-start gap-3 rounded-xl border p-4" style="border-color:var(--sa-border);">
                    <span class="flex size-10 shrink-0 items-center justify-center rounded-lg"
                          style="background:color-mix(in oklch,var(--sa-primary) 10%,transparent);color:var(--sa-primary);">
                        <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                        </svg>
                    </span>
                    <div class="min-w-0 flex-1">
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <p class="font-medium" style="color:var(--sa-fg);">Système prêt</p>
                            <span class="rounded-full px-2.5 py-0.5 text-xs font-medium"
                                  style="background:color-mix(in oklch,var(--sa-info) 15%,transparent);color:var(--sa-info);">
                                Actif
                            </span>
                        </div>
                        <p class="mt-0.5 truncate text-sm" style="color:var(--sa-muted-fg);">
                            Le système de notifications push est opérationnel. Composez votre première notification.
                        </p>
                        <div class="mt-2 flex flex-wrap gap-4 text-xs" style="color:var(--sa-muted-fg);">
                            <span>Audience : Tous</span>
                            <span>Tokens : {{ $stats['drivers_with_token'] + $stats['customers_with_token'] }}</span>
                        </div>
                    </div>
                </li>
            </ul>

            <p class="mt-4 text-center text-xs" style="color:var(--sa-muted-fg);">
                L'historique complet des notifications sera affiché ici une fois les envois effectués.
            </p>
        </div>
    </div>

    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var form = document.getElementById('push-send-form');
            if (form) {
                ajaxForm(form, {
                    onSuccess: function () {
                        form.reset();
                        // Reset audience display
                        document.getElementById('audienceInput').value = 'all_drivers';
                    }
                });
            }
        });
    </script>
    @endpush
</x-layouts.admin-super>
