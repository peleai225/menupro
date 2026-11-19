<x-layouts.admin-super title="Tarifs colis">
    @php
        $in = 'w-full rounded-lg px-2 py-1.5 text-sm';
        $inStyle = 'border:1px solid var(--sa-border);background:var(--sa-card);color:var(--sa-fg);';
    @endphp

    <div class="mb-6">
        <a href="{{ route('super-admin.parcels.index') }}" class="text-sm" style="color:var(--sa-muted-fg);">← Colis</a>
        <h1 class="mt-1 text-2xl font-bold" style="color:var(--sa-fg);">Tarifs colis par ville</h1>
        <p class="mt-1 text-sm" style="color:var(--sa-muted-fg);">
            Prix = base + prix/km × distance (vol d'oiseau × {{ config('parcels.road_factor') }}), arrondi au {{ config('parcels.rounding_step') }} F supérieur, + supplément taille.
            Laisser un champ vide = valeur par défaut :
            base {{ $defaults['base_fee'] }} F, {{ $defaults['per_km_fee'] }} F/km, moyen +{{ $defaults['size_supplements']['medium'] }} F, grand +{{ $defaults['size_supplements']['large'] }} F,
            plafond {{ $defaults['max_distance_km'] }} km, part livreur {{ $defaults['driver_share_percent'] }} %.
        </p>
    </div>

    @include('pages.super-admin.parcels._flash')

    <div class="space-y-4">
        @forelse($rows as $row)
            @php($c = $row['city'])
            @php($o = $row['override'])
            <form method="POST" action="{{ route('super-admin.parcels.tariffs.update', $c) }}" class="rounded-2xl border p-4 shadow-sm" style="border-color:var(--sa-border);background:var(--sa-card);">
                @csrf @method('PUT')
                <div class="mb-3 flex flex-wrap items-center justify-between gap-2">
                    <h2 class="font-semibold" style="color:var(--sa-fg);">{{ $c->name }} @unless($c->is_active)<span class="text-xs" style="color:var(--sa-muted-fg);">(ville inactive)</span>@endunless</h2>
                    <label class="inline-flex items-center gap-2 text-sm" style="color:var(--sa-fg);">
                        <input type="checkbox" name="enabled" value="1" @checked($row['effective']['enabled'])> Service colis actif
                    </label>
                </div>
                <div class="grid grid-cols-2 gap-3 sm:grid-cols-4 lg:grid-cols-8">
                    @foreach([
                        ['base_fee', 'Base (F)', $o['base_fee'] ?? null],
                        ['per_km_fee', 'Prix/km (F)', $o['per_km_fee'] ?? null],
                        ['small', 'Petit +F', $o['size_supplements']['small'] ?? null],
                        ['medium', 'Moyen +F', $o['size_supplements']['medium'] ?? null],
                        ['large', 'Grand +F', $o['size_supplements']['large'] ?? null],
                        ['max_distance_km', 'Plafond (km)', $o['max_distance_km'] ?? null],
                        ['driver_share_percent', 'Part livreur %', $o['driver_share_percent'] ?? null],
                    ] as [$name, $label, $value])
                        <label class="text-xs" style="color:var(--sa-muted-fg);">{{ $label }}
                            <input type="number" step="any" min="0" name="{{ $name }}" value="{{ $value }}" placeholder="défaut" class="{{ $in }}" style="{{ $inStyle }}">
                        </label>
                    @endforeach
                    <div class="flex items-end">
                        <button class="w-full rounded-lg px-3 py-2 text-sm font-medium" style="background:var(--sa-primary);color:var(--sa-primary-fg);">Enregistrer</button>
                    </div>
                </div>
                <p class="mt-2 text-xs" style="color:var(--sa-muted-fg);">
                    En vigueur : {{ $row['effective']['base_fee'] }} F + {{ $row['effective']['per_km_fee'] }} F/km · plafond {{ $row['effective']['max_distance_km'] }} km · livreur {{ $row['effective']['driver_share_percent'] }} %
                </p>
            </form>
        @empty
            <p class="text-sm" style="color:var(--sa-muted-fg);">Aucune ville de livraison configurée.</p>
        @endforelse
    </div>
</x-layouts.admin-super>
