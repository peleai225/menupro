<x-layouts.admin-super title="Facturation partenaires">
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <a href="{{ route('super-admin.parcels.index') }}" class="text-sm" style="color:var(--sa-muted-fg);">← Colis</a>
            <h1 class="mt-1 text-2xl font-bold" style="color:var(--sa-fg);">Courses à facturer aux partenaires</h1>
            <p class="mt-1 text-sm" style="color:var(--sa-muted-fg);">Colis livrés dans le mois, payés sur facture (statut « billed_to_partner »).</p>
        </div>
        <form method="GET" class="flex items-center gap-2">
            <input type="month" name="month" value="{{ $month }}" class="h-10 rounded-lg px-3 text-sm" style="border:1px solid var(--sa-border);background:var(--sa-card);color:var(--sa-fg);">
            <button class="h-10 rounded-lg px-4 text-sm font-medium" style="background:var(--sa-primary);color:var(--sa-primary-fg);">Afficher</button>
        </form>
    </div>

    <div class="overflow-x-auto rounded-2xl border shadow-sm" style="border-color:var(--sa-border);background:var(--sa-card);">
        <table class="w-full min-w-[640px] text-sm">
            <thead>
                <tr style="border-bottom:1px solid var(--sa-border);">
                    @foreach(['Partenaire', 'Courses livrées', 'À facturer', 'Part livreurs', 'Marge MenuPro', ''] as $h)
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase" style="color:var(--sa-muted-fg);">{{ $h }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($rows as $r)
                    <tr style="border-bottom:1px solid var(--sa-border);">
                        <td class="px-4 py-3 font-medium" style="color:var(--sa-fg);">{{ $partners[$r->delivery_partner_id]->name ?? '—' }}</td>
                        <td class="px-4 py-3" style="color:var(--sa-fg);">{{ $r->parcels_count }}</td>
                        <td class="px-4 py-3 font-semibold" style="color:var(--sa-fg);">{{ number_format($r->total_price, 0, ',', ' ') }} F</td>
                        <td class="px-4 py-3" style="color:var(--sa-fg);">{{ number_format($r->total_driver_share, 0, ',', ' ') }} F</td>
                        <td class="px-4 py-3" style="color:var(--sa-fg);">{{ number_format($r->total_commission, 0, ',', ' ') }} F</td>
                        <td class="px-4 py-3 text-right"><a href="?month={{ $month }}&partner={{ $r->delivery_partner_id }}" style="color:var(--sa-primary);">Détail</a></td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-4 py-10 text-center" style="color:var(--sa-muted-fg);">Aucune course partenaire livrée ce mois-ci.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($selectedPartner)
        <div class="mt-6 overflow-x-auto rounded-2xl border shadow-sm" style="border-color:var(--sa-border);background:var(--sa-card);">
            <table class="w-full min-w-[640px] text-sm">
                <thead>
                    <tr style="border-bottom:1px solid var(--sa-border);">
                        @foreach(['Livré le', 'Référence', 'Réf. partenaire', 'Ville', 'Km', 'Taille', 'Prix'] as $h)
                            <th class="px-4 py-3 text-left text-xs font-semibold uppercase" style="color:var(--sa-muted-fg);">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($details as $d)
                        <tr style="border-bottom:1px solid var(--sa-border);">
                            <td class="px-4 py-2 text-xs" style="color:var(--sa-muted-fg);">{{ $d->delivered_at?->format('d/m/Y H:i') }}</td>
                            <td class="px-4 py-2 font-mono" style="color:var(--sa-fg);">{{ $d->reference }}</td>
                            <td class="px-4 py-2" style="color:var(--sa-fg);">{{ $d->external_reference }}</td>
                            <td class="px-4 py-2" style="color:var(--sa-fg);">{{ $d->city }}</td>
                            <td class="px-4 py-2" style="color:var(--sa-fg);">{{ number_format($d->distance_km, 1, ',', ' ') }}</td>
                            <td class="px-4 py-2" style="color:var(--sa-fg);">{{ $d->size }}</td>
                            <td class="px-4 py-2" style="color:var(--sa-fg);">{{ number_format($d->price, 0, ',', ' ') }} F</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</x-layouts.admin-super>
