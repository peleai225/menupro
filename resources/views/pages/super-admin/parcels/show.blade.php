<x-layouts.admin-super :title="'Colis ' . $parcel->reference">
    @php
        $card = 'rounded-2xl border p-5 shadow-sm';
        $cardStyle = 'border-color:var(--sa-border);background:var(--sa-card);';
        $muted = 'color:var(--sa-muted-fg);';
        $fg = 'color:var(--sa-fg);';
        $steps = \App\Support\Parcels\ParcelPresenter::steps($parcel);
        $senderLabels = ['customer' => 'Client (app)', 'restaurant' => 'Commerçant', 'partner' => 'Partenaire API'];
    @endphp

    <div class="mb-6 flex flex-wrap items-center justify-between gap-4">
        <div>
            <a href="{{ route('super-admin.parcels.index') }}" class="text-sm" style="{{ $muted }}">← Colis</a>
            <h1 class="mt-1 text-2xl font-bold font-mono" style="{{ $fg }}">{{ $parcel->reference }}</h1>
            <div class="mt-2">@include('pages.super-admin.parcels._status', ['status' => $parcel->status])</div>
        </div>
    </div>

    @include('pages.super-admin.parcels._flash')

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            <div class="{{ $card }}" style="{{ $cardStyle }}">
                <h2 class="mb-4 font-semibold" style="{{ $fg }}">Trajet</h2>
                <dl class="grid gap-4 sm:grid-cols-2 text-sm">
                    <div>
                        <dt class="text-xs uppercase" style="{{ $muted }}">Ramassage</dt>
                        <dd style="{{ $fg }}">{{ $parcel->pickup_address }}</dd>
                        <dd class="text-xs" style="{{ $muted }}">{{ $parcel->sender_name }} · {{ $parcel->sender_phone }}</dd>
                        @if($parcel->pickup_contact)
                            <dd class="text-xs" style="{{ $muted }}">Contact : {{ $parcel->pickup_contact['name'] ?? '' }} {{ $parcel->pickup_contact['phone'] ?? '' }}</dd>
                        @endif
                    </div>
                    <div>
                        <dt class="text-xs uppercase" style="{{ $muted }}">Remise</dt>
                        <dd style="{{ $fg }}">{{ $parcel->dropoff_address }}</dd>
                        <dd class="text-xs" style="{{ $muted }}">{{ $parcel->recipient_name }} · {{ $parcel->recipient_phone }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs uppercase" style="{{ $muted }}">Colis</dt>
                        <dd style="{{ $fg }}">{{ $parcel->description }} — {{ $parcel->sizeLabel() }}</dd>
                        @if($parcel->note)<dd class="text-xs" style="{{ $muted }}">Note : {{ $parcel->note }}</dd>@endif
                    </div>
                    <div>
                        <dt class="text-xs uppercase" style="{{ $muted }}">Ville · distance</dt>
                        <dd style="{{ $fg }}">{{ $parcel->city }} · {{ number_format($parcel->distance_km, 2, ',', ' ') }} km (route estimée)</dd>
                    </div>
                </dl>
            </div>

            <div class="{{ $card }}" style="{{ $cardStyle }}">
                <h2 class="mb-4 font-semibold" style="{{ $fg }}">Étapes</h2>
                <ol class="space-y-2 text-sm">
                    @foreach($steps as $s)
                        <li class="flex items-center justify-between">
                            <span style="{{ $s['done'] ? $fg : $muted }}">{{ $s['done'] ? '●' : '○' }} {{ $s['label'] }}</span>
                            <span class="text-xs" style="{{ $muted }}">{{ $s['at'] ? \Illuminate\Support\Carbon::parse($s['at'])->timezone(config('app.timezone'))->format('d/m/Y H:i') : '' }}</span>
                        </li>
                    @endforeach
                </ol>
                @if($parcel->cancellation_reason)<p class="mt-3 text-sm" style="color:var(--sa-danger);">Annulation ({{ $parcel->cancelled_by }}) : {{ $parcel->cancellation_reason }}</p>@endif
                @if($parcel->failure_reason)<p class="mt-3 text-sm" style="color:var(--sa-danger);">Échec : {{ $parcel->failure_reason }}</p>@endif
                <p class="mt-3 text-xs" style="{{ $muted }}">Appels livreurs : {{ $parcel->dispatch_attempts }} · Essais de code : {{ $parcel->handover_attempts }}{{ $parcel->isHandoverLocked() ? ' (BLOQUÉ)' : '' }}</p>
            </div>

            @if($parcel->pickupPhotoUrl())
                <div class="{{ $card }}" style="{{ $cardStyle }}">
                    <h2 class="mb-4 font-semibold" style="{{ $fg }}">Photo de prise en charge</h2>
                    <a href="{{ $parcel->pickupPhotoUrl() }}" target="_blank" rel="noopener">
                        <img src="{{ $parcel->pickupPhotoUrl() }}" alt="Photo du colis {{ $parcel->reference }}" class="max-h-80 rounded-xl">
                    </a>
                </div>
            @endif
        </div>

        <div class="space-y-6">
            <div class="{{ $card }}" style="{{ $cardStyle }}">
                <h2 class="mb-4 font-semibold" style="{{ $fg }}">Paiement</h2>
                <dl class="space-y-2 text-sm">
                    <div class="flex justify-between"><dt style="{{ $muted }}">Prix</dt><dd class="font-semibold" style="{{ $fg }}">{{ number_format($parcel->price, 0, ',', ' ') }} F</dd></div>
                    <div class="flex justify-between"><dt style="{{ $muted }}">Part livreur</dt><dd style="{{ $fg }}">{{ number_format($parcel->driver_share, 0, ',', ' ') }} F</dd></div>
                    <div class="flex justify-between"><dt style="{{ $muted }}">Commission MenuPro</dt><dd style="{{ $fg }}">{{ number_format($parcel->platform_commission, 0, ',', ' ') }} F</dd></div>
                    <div class="flex justify-between"><dt style="{{ $muted }}">Mode</dt><dd style="{{ $fg }}">{{ $parcel->payment_method }}</dd></div>
                    <div class="flex justify-between"><dt style="{{ $muted }}">Statut</dt><dd style="{{ $fg }}">{{ $parcel->payment_status }}</dd></div>
                    @if($parcel->payment_reference)<div class="flex justify-between gap-2"><dt style="{{ $muted }}">Réf.</dt><dd class="font-mono text-xs break-all" style="{{ $fg }}">{{ $parcel->payment_reference }}</dd></div>@endif
                    @if($parcel->paid_at)<div class="flex justify-between"><dt style="{{ $muted }}">Payé le</dt><dd style="{{ $fg }}">{{ $parcel->paid_at->format('d/m/Y H:i') }}</dd></div>@endif
                    @if($parcel->refund_status)<div class="flex justify-between"><dt style="{{ $muted }}">Remboursement</dt><dd class="font-semibold" style="color:var(--sa-danger);">{{ $parcel->refund_status === 'pending' ? 'À effectuer' : 'Effectué' }}</dd></div>@endif
                    @if($cashDebt)<div class="flex justify-between"><dt style="{{ $muted }}">Commission due par le livreur</dt><dd style="{{ $fg }}">{{ number_format($cashDebt->amount_xof, 0, ',', ' ') }} F ({{ $cashDebt->status === 'pending' ? 'à recevoir' : 'reçue' }})</dd></div>@endif
                </dl>
            </div>

            <div class="{{ $card }}" style="{{ $cardStyle }}">
                <h2 class="mb-4 font-semibold" style="{{ $fg }}">Acteurs</h2>
                <p class="text-sm" style="{{ $fg }}">Expéditeur : {{ $senderLabels[$parcel->sender_type] ?? $parcel->sender_type }}
                    @if($parcel->partner) — {{ $parcel->partner->name }} @if($parcel->external_reference)(réf. {{ $parcel->external_reference }})@endif @endif
                    @if($parcel->restaurant) — {{ $parcel->restaurant->name }} @endif
                </p>
                <p class="mt-2 text-sm" style="{{ $fg }}">Livreur : {{ $parcel->driver ? $parcel->driver->name . ' · ' . $parcel->driver->phone : '—' }}</p>
                <p class="mt-2 text-xs break-all" style="{{ $muted }}">Suivi public : {{ $parcel->publicTrackingUrl() }}</p>
            </div>

            <div class="{{ $card }} space-y-3" style="{{ $cardStyle }}">
                <h2 class="font-semibold" style="{{ $fg }}">Actions</h2>

                @if($parcel->refund_status === 'pending')
                    <form method="POST" action="{{ route('super-admin.parcels.refunded', $parcel) }}" onsubmit="return confirm('Confirmez-vous avoir remboursé {{ $parcel->price }} F à l\'expéditeur ?')">
                        @csrf
                        <button class="w-full rounded-lg px-4 py-2 text-sm font-medium" style="background:var(--sa-primary);color:var(--sa-primary-fg);">Marquer comme remboursé</button>
                    </form>
                @endif

                @if($parcel->isHandoverLocked())
                    <form method="POST" action="{{ route('super-admin.parcels.unlock', $parcel) }}">
                        @csrf
                        <button class="w-full rounded-lg border px-4 py-2 text-sm font-medium" style="border-color:var(--sa-border);{{ $fg }}">Débloquer le code de remise</button>
                    </form>
                @endif

                @if($cashDebt && $cashDebt->status === 'pending')
                    <form method="POST" action="{{ route('super-admin.parcels.settle-cash', $parcel) }}">
                        @csrf
                        <button class="w-full rounded-lg border px-4 py-2 text-sm font-medium" style="border-color:var(--sa-border);{{ $fg }}">Commission reçue du livreur</button>
                    </form>
                @endif

                @unless($parcel->status->isFinal())
                    <form method="POST" action="{{ route('super-admin.parcels.cancel', $parcel) }}" class="space-y-2">
                        @csrf
                        <input name="reason" required minlength="3" maxlength="300" placeholder="Motif de l'annulation" class="w-full rounded-lg px-3 py-2 text-sm" style="border:1px solid var(--sa-border);background:var(--sa-card);{{ $fg }}">
                        <button class="w-full rounded-lg px-4 py-2 text-sm font-medium" style="background:var(--sa-danger);color:#fff;" onclick="return confirm('Annuler ce colis ?')">Annuler le colis</button>
                    </form>
                @endunless
            </div>
        </div>
    </div>
</x-layouts.admin-super>
