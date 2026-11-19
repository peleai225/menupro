<x-layouts.admin-super title="Colis">
    @php
        $input = 'h-10 rounded-lg px-3 text-sm outline-none';
        $inputStyle = 'border:1px solid var(--sa-border);background:var(--sa-card);color:var(--sa-fg);';
        $senderLabels = ['customer' => 'Client', 'restaurant' => 'Commerçant', 'partner' => 'Partenaire'];
    @endphp

    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold" style="color:var(--sa-fg);">Colis</h1>
            <p class="mt-1 text-sm" style="color:var(--sa-muted-fg);">Courses colis envoyées par les clients, les commerçants et les partenaires.</p>
        </div>
        <div class="flex flex-wrap gap-2">
            <a href="{{ route('super-admin.parcels.tariffs') }}" class="inline-flex h-10 items-center rounded-lg border px-4 text-sm font-medium" style="border-color:var(--sa-border);background:var(--sa-card);color:var(--sa-fg);">Tarifs par ville</a>
            <a href="{{ route('super-admin.parcels.billing') }}" class="inline-flex h-10 items-center rounded-lg border px-4 text-sm font-medium" style="border-color:var(--sa-border);background:var(--sa-card);color:var(--sa-fg);">Facturation partenaires</a>
            @if(auth()->user()->isFullAdmin())
                <a href="{{ route('super-admin.partners.index') }}" class="inline-flex h-10 items-center rounded-lg px-4 text-sm font-medium" style="background:var(--sa-primary);color:var(--sa-primary-fg);">Partenaires</a>
            @endif
        </div>
    </div>

    @include('pages.super-admin.parcels._flash')

    <div class="mb-6 grid grid-cols-2 gap-4 xl:grid-cols-4">
        @foreach([
            ['Aujourd\'hui', $stats['today'], 'var(--sa-primary)'],
            ['Sans livreur', $stats['searching'], 'var(--sa-warning)'],
            ['En cours', $stats['in_progress'], 'var(--sa-info)'],
            ['Remboursements à faire', $stats['refunds_pending'], 'var(--sa-danger)'],
        ] as [$label, $value, $color])
            <div class="rounded-2xl border p-5 shadow-sm" style="border-color:var(--sa-border);background:var(--sa-card);">
                <p class="text-3xl font-bold" style="color:{{ $color }};">{{ number_format($value) }}</p>
                <p class="mt-1 text-sm" style="color:var(--sa-muted-fg);">{{ $label }}</p>
            </div>
        @endforeach
    </div>

    <form method="GET" action="{{ route('super-admin.parcels.index') }}" class="mb-4 flex flex-wrap items-center gap-3">
        <input type="text" name="search" value="{{ request('search') }}" placeholder="Référence, téléphone…" class="{{ $input }} sm:w-56" style="{{ $inputStyle }}">
        <select name="status" class="{{ $input }}" style="{{ $inputStyle }}">
            <option value="">Tous les statuts</option>
            @foreach($statuses as $s)
                <option value="{{ $s->value }}" @selected(request('status') === $s->value)>{{ $s->label() }}</option>
            @endforeach
        </select>
        <select name="city" class="{{ $input }}" style="{{ $inputStyle }}">
            <option value="">Toutes les villes</option>
            @foreach($cities as $c)
                <option value="{{ $c }}" @selected(request('city') === $c)>{{ $c }}</option>
            @endforeach
        </select>
        <select name="sender" class="{{ $input }}" style="{{ $inputStyle }}">
            <option value="">Tous les expéditeurs</option>
            @foreach($senderLabels as $k => $l)
                <option value="{{ $k }}" @selected(request('sender') === $k)>{{ $l }}</option>
            @endforeach
        </select>
        <input type="date" name="date_from" value="{{ request('date_from') }}" class="{{ $input }}" style="{{ $inputStyle }}">
        <input type="date" name="date_to" value="{{ request('date_to') }}" class="{{ $input }}" style="{{ $inputStyle }}">
        <label class="inline-flex items-center gap-2 text-sm" style="color:var(--sa-fg);">
            <input type="checkbox" name="refund" value="pending" @checked(request('refund') === 'pending')> À rembourser
        </label>
        <button class="inline-flex h-10 items-center rounded-lg px-4 text-sm font-medium" style="background:var(--sa-primary);color:var(--sa-primary-fg);">Filtrer</button>
        @if(request()->anyFilled(['search', 'status', 'city', 'sender', 'date_from', 'date_to', 'refund']))
            <a href="{{ route('super-admin.parcels.index') }}" class="text-sm" style="color:var(--sa-muted-fg);">Réinitialiser</a>
        @endif
    </form>

    <div class="overflow-x-auto rounded-2xl border shadow-sm" style="border-color:var(--sa-border);background:var(--sa-card);">
        <table class="w-full min-w-[960px] text-sm">
            <thead>
                <tr style="border-bottom:1px solid var(--sa-border);">
                    @foreach(['Référence', 'Expéditeur', 'Ville', 'Trajet', 'Prix', 'Paiement', 'Livreur', 'Statut', 'Créé'] as $h)
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--sa-muted-fg);">{{ $h }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($parcels as $p)
                    <tr style="border-bottom:1px solid var(--sa-border);">
                        <td class="px-4 py-3">
                            <a href="{{ route('super-admin.parcels.show', $p) }}" class="font-mono font-medium" style="color:var(--sa-primary);">{{ $p->reference }}</a>
                            @if($p->external_reference)<p class="text-xs" style="color:var(--sa-muted-fg);">{{ $p->external_reference }}</p>@endif
                        </td>
                        <td class="px-4 py-3" style="color:var(--sa-fg);">
                            <p class="font-medium">{{ $p->partner?->name ?? $p->restaurant?->name ?? $p->sender_name }}</p>
                            <p class="text-xs" style="color:var(--sa-muted-fg);">{{ $senderLabels[$p->sender_type] ?? $p->sender_type }}</p>
                        </td>
                        <td class="px-4 py-3" style="color:var(--sa-fg);">{{ $p->city }}</td>
                        <td class="px-4 py-3 text-xs" style="color:var(--sa-muted-fg);">{{ number_format($p->distance_km, 1, ',', ' ') }} km · {{ $p->sizeLabel() }}</td>
                        <td class="px-4 py-3 font-medium" style="color:var(--sa-fg);">{{ number_format($p->price, 0, ',', ' ') }} F</td>
                        <td class="px-4 py-3 text-xs" style="color:var(--sa-fg);">
                            {{ ['jeko' => 'Jeko', 'cash' => 'Espèces', 'partner_billing' => 'Facturé partenaire'][$p->payment_method] ?? $p->payment_method }}
                            <span style="color:var(--sa-muted-fg);">· {{ $p->payment_status }}</span>
                            @if($p->refund_status === 'pending')<span class="block font-semibold" style="color:var(--sa-danger);">À rembourser</span>@endif
                        </td>
                        <td class="px-4 py-3 text-xs" style="color:var(--sa-fg);">{{ $p->driver?->name ?? '—' }}</td>
                        <td class="px-4 py-3">@include('pages.super-admin.parcels._status', ['status' => $p->status])</td>
                        <td class="px-4 py-3 text-xs" style="color:var(--sa-muted-fg);">{{ $p->created_at->format('d/m H:i') }}</td>
                    </tr>
                @empty
                    <tr><td colspan="9" class="px-4 py-10 text-center" style="color:var(--sa-muted-fg);">Aucun colis.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $parcels->links() }}</div>
</x-layouts.admin-super>
