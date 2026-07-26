<x-layouts.admin-super title="Demande Jeko — Détail">

    {{-- Page Header --}}
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <a href="{{ route('super-admin.jeko.index') }}"
               class="mb-2 inline-flex items-center gap-1 text-sm"
               style="color:var(--sa-muted-fg);">
                &larr; Retour aux demandes
            </a>
            <h1 class="text-2xl font-bold" style="color:var(--sa-fg);">Demande Jeko</h1>
            <p class="mt-1 text-sm" style="color:var(--sa-muted-fg);">{{ $jekoSubMerchant->restaurant?->name ?? $jekoSubMerchant->legal_name }}</p>
        </div>
        @php
            $color = match($jekoSubMerchant->status->color()) {
                'yellow' => ['bg' => 'rgba(217,119,6,0.10)', 'fg' => 'var(--sa-warning)', 'border' => 'rgba(217,119,6,0.25)'],
                'blue'   => ['bg' => 'rgba(59,130,246,0.10)', 'fg' => '#3b82f6', 'border' => 'rgba(59,130,246,0.25)'],
                'green'  => ['bg' => 'rgba(61,158,98,0.10)', 'fg' => 'var(--sa-success)', 'border' => 'rgba(61,158,98,0.25)'],
                'red'    => ['bg' => 'rgba(220,38,38,0.10)', 'fg' => 'var(--sa-danger)', 'border' => 'rgba(220,38,38,0.25)'],
                default  => ['bg' => 'transparent', 'fg' => 'var(--sa-muted-fg)', 'border' => 'var(--sa-border)'],
            };
        @endphp
        <span class="inline-flex items-center rounded-full border px-3 py-1 text-sm font-medium"
              style="background:{{ $color['bg'] }};color:{{ $color['fg'] }};border-color:{{ $color['border'] }};">
            {{ $jekoSubMerchant->status->label() }}
        </span>
    </div>

    {{-- Flash Messages --}}
    @if(session('success'))
        <div class="mb-4 rounded-lg border px-4 py-3 text-sm" style="background:rgba(61,158,98,0.10);border-color:rgba(61,158,98,0.30);color:var(--sa-success);">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-4 rounded-lg border px-4 py-3 text-sm" style="background:rgba(220,38,38,0.10);border-color:rgba(220,38,38,0.30);color:var(--sa-danger);">
            {{ session('error') }}
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-2">
        {{-- Sub-Merchant Info --}}
        <div class="rounded-2xl border p-6 shadow-sm" style="border-color:var(--sa-border);background:var(--sa-card);">
            <h2 class="mb-4 text-base font-semibold" style="color:var(--sa-fg);">Informations de la demande</h2>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between gap-4">
                    <dt style="color:var(--sa-muted-fg);">Raison sociale</dt>
                    <dd class="font-medium text-right" style="color:var(--sa-fg);">{{ $jekoSubMerchant->legal_name }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt style="color:var(--sa-muted-fg);">Type d'activité</dt>
                    <dd class="font-medium text-right" style="color:var(--sa-fg);">{{ $jekoSubMerchant->business_type ?? '—' }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt style="color:var(--sa-muted-fg);">Numéro Mobile Money</dt>
                    <dd class="font-medium text-right font-mono" style="color:var(--sa-fg);">{{ $jekoSubMerchant->mobile_money }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt style="color:var(--sa-muted-fg);">Opérateur</dt>
                    <dd class="font-medium text-right" style="color:var(--sa-fg);">{{ $jekoSubMerchant->mobile_money_operator?->value ?? '—' }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt style="color:var(--sa-muted-fg);">Email</dt>
                    <dd class="font-medium text-right" style="color:var(--sa-fg);">{{ $jekoSubMerchant->email }}</dd>
                </div>
                <div class="flex justify-between gap-4">
                    <dt style="color:var(--sa-muted-fg);">Soumis le</dt>
                    <dd class="font-medium text-right" style="color:var(--sa-fg);">{{ $jekoSubMerchant->created_at->format('d/m/Y H:i') }}</dd>
                </div>
                @if($jekoSubMerchant->isRejected())
                    <div class="rounded-lg border p-3 mt-2" style="border-color:rgba(220,38,38,0.30);background:rgba(220,38,38,0.05);">
                        <p class="text-xs font-semibold mb-1" style="color:var(--sa-danger);">Motif du rejet</p>
                        <p class="text-xs" style="color:var(--sa-fg);">{{ $jekoSubMerchant->rejected_reason }}</p>
                    </div>
                @endif
            </dl>
        </div>

        {{-- Restaurant Info --}}
        <div class="rounded-2xl border p-6 shadow-sm" style="border-color:var(--sa-border);background:var(--sa-card);">
            <h2 class="mb-4 text-base font-semibold" style="color:var(--sa-fg);">Restaurant</h2>
            @if($jekoSubMerchant->restaurant)
                <dl class="space-y-3 text-sm">
                    <div class="flex justify-between gap-4">
                        <dt style="color:var(--sa-muted-fg);">Nom</dt>
                        <dd class="font-medium text-right" style="color:var(--sa-fg);">{{ $jekoSubMerchant->restaurant->name }}</dd>
                    </div>
                    @if($jekoSubMerchant->restaurant->owner)
                        <div class="flex justify-between gap-4">
                            <dt style="color:var(--sa-muted-fg);">Propriétaire</dt>
                            <dd class="font-medium text-right" style="color:var(--sa-fg);">{{ $jekoSubMerchant->restaurant->owner->name }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt style="color:var(--sa-muted-fg);">Email propriétaire</dt>
                            <dd class="font-medium text-right" style="color:var(--sa-fg);">{{ $jekoSubMerchant->restaurant->owner->email }}</dd>
                        </div>
                    @endif
                    @if($jekoSubMerchant->approver)
                        <div class="flex justify-between gap-4">
                            <dt style="color:var(--sa-muted-fg);">Approuvé par</dt>
                            <dd class="font-medium text-right" style="color:var(--sa-fg);">{{ $jekoSubMerchant->approver->name }}</dd>
                        </div>
                        <div class="flex justify-between gap-4">
                            <dt style="color:var(--sa-muted-fg);">Approuvé le</dt>
                            <dd class="font-medium text-right" style="color:var(--sa-fg);">{{ $jekoSubMerchant->approved_at?->format('d/m/Y H:i') }}</dd>
                        </div>
                    @endif
                    @if($jekoSubMerchant->isIntegrated())
                        <div class="mt-2 rounded-lg border p-3" style="border-color:rgba(61,158,98,0.30);background:rgba(61,158,98,0.05);">
                            <p class="text-xs font-semibold mb-2" style="color:var(--sa-success);">Identifiants Jeko</p>
                            <p class="text-xs font-mono" style="color:var(--sa-fg);">Merchant ID: {{ $jekoSubMerchant->jeko_merchant_id }}</p>
                            @if($jekoSubMerchant->jeko_store_id)
                                <p class="text-xs font-mono" style="color:var(--sa-fg);">Store ID: {{ $jekoSubMerchant->jeko_store_id }}</p>
                            @endif
                            @if($jekoSubMerchant->jeko_wallet_id)
                                <p class="text-xs font-mono" style="color:var(--sa-fg);">Wallet ID: {{ $jekoSubMerchant->jeko_wallet_id }}</p>
                            @endif
                        </div>
                    @endif
                </dl>
            @else
                <p class="text-sm" style="color:var(--sa-muted-fg);">Restaurant introuvable.</p>
            @endif
        </div>
    </div>

    {{-- Actions --}}
    @if($jekoSubMerchant->isPending())
        <div class="mt-6 flex flex-wrap gap-3">
            <form method="POST" action="{{ route('super-admin.jeko.approve', $jekoSubMerchant) }}"
                  onsubmit="return confirm('Approuver cette demande ?')">
                @csrf
                <button type="submit"
                        class="inline-flex h-10 items-center gap-2 rounded-lg px-5 text-sm font-medium shadow-sm"
                        style="background:var(--sa-success);color:#fff;">
                    Approuver la demande
                </button>
            </form>

            <button type="button"
                    onclick="document.getElementById('reject-modal').classList.remove('hidden');document.getElementById('reject-modal').classList.add('flex');"
                    class="inline-flex h-10 items-center gap-2 rounded-lg px-5 text-sm font-medium shadow-sm"
                    style="background:var(--sa-danger);color:#fff;">
                Rejeter la demande
            </button>
        </div>

        {{-- Reject Modal --}}
        <div id="reject-modal"
             class="fixed inset-0 z-50 hidden items-center justify-center"
             style="background:rgba(0,0,0,0.5);">
            <div class="w-full max-w-md rounded-2xl border p-6 shadow-xl"
                 style="border-color:var(--sa-border);background:var(--sa-card);">
                <h2 class="mb-1 text-lg font-semibold" style="color:var(--sa-fg);">Rejeter la demande</h2>
                <p class="mb-4 text-sm" style="color:var(--sa-muted-fg);">
                    Restaurant : <strong>{{ $jekoSubMerchant->restaurant?->name ?? $jekoSubMerchant->legal_name }}</strong>
                </p>
                <form method="POST" action="{{ route('super-admin.jeko.reject', $jekoSubMerchant) }}">
                    @csrf
                    <div class="mb-4">
                        <label class="mb-1 block text-sm font-medium" style="color:var(--sa-fg);">
                            Motif du rejet <span style="color:var(--sa-danger);">*</span>
                        </label>
                        <textarea name="rejected_reason"
                                  rows="4"
                                  maxlength="1000"
                                  required
                                  placeholder="Expliquez la raison du rejet..."
                                  class="w-full rounded-lg border px-3 py-2 text-sm outline-none focus:ring-2"
                                  style="border-color:var(--sa-border);background:var(--sa-card);color:var(--sa-fg);resize:vertical;"></textarea>
                    </div>
                    <div class="flex justify-end gap-3">
                        <button type="button"
                                onclick="document.getElementById('reject-modal').classList.add('hidden');document.getElementById('reject-modal').classList.remove('flex');"
                                class="inline-flex h-9 items-center gap-2 rounded-lg border px-4 text-sm font-medium"
                                style="border-color:var(--sa-border);color:var(--sa-muted-fg);">
                            Annuler
                        </button>
                        <button type="submit"
                                class="inline-flex h-9 items-center gap-2 rounded-lg px-4 text-sm font-medium shadow-sm"
                                style="background:var(--sa-danger);color:#fff;">
                            Confirmer le rejet
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

</x-layouts.admin-super>
