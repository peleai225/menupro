<x-layouts.admin-super title="Partenaires livraison">
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <a href="{{ route('super-admin.parcels.index') }}" class="text-sm" style="color:var(--sa-muted-fg);">← Colis</a>
            <h1 class="mt-1 text-2xl font-bold" style="color:var(--sa-fg);">Partenaires livraison</h1>
            <p class="mt-1 text-sm" style="color:var(--sa-muted-fg);">Plateformes externes qui créent des colis par API (courses facturées au partenaire).</p>
        </div>
        <div class="flex gap-2">
            <a href="{{ route('super-admin.parcels.billing') }}" class="inline-flex h-10 items-center rounded-lg border px-4 text-sm font-medium" style="border-color:var(--sa-border);background:var(--sa-card);color:var(--sa-fg);">Facturation</a>
            <a href="{{ route('super-admin.partners.create') }}" class="inline-flex h-10 items-center rounded-lg px-4 text-sm font-medium" style="background:var(--sa-primary);color:var(--sa-primary-fg);">Nouveau partenaire</a>
        </div>
    </div>

    @include('pages.super-admin.parcels._flash')

    <div class="overflow-x-auto rounded-2xl border shadow-sm" style="border-color:var(--sa-border);background:var(--sa-card);">
        <table class="w-full min-w-[720px] text-sm">
            <thead>
                <tr style="border-bottom:1px solid var(--sa-border);">
                    @foreach(['Nom', 'Statut', 'Villes', 'Clé', 'Webhook', 'Colis', 'Dernier appel'] as $h)
                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase" style="color:var(--sa-muted-fg);">{{ $h }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($partners as $p)
                    <tr style="border-bottom:1px solid var(--sa-border);">
                        <td class="px-4 py-3"><a href="{{ route('super-admin.partners.show', $p) }}" class="font-medium" style="color:var(--sa-primary);">{{ $p->name }}</a></td>
                        <td class="px-4 py-3" style="color:{{ $p->is_active ? 'var(--sa-success)' : 'var(--sa-danger)' }};">{{ $p->is_active ? 'Actif' : 'Désactivé' }}</td>
                        <td class="px-4 py-3 text-xs" style="color:var(--sa-fg);">{{ $p->cities ? implode(', ', $p->cities) : 'Toutes' }}</td>
                        <td class="px-4 py-3 font-mono text-xs" style="color:var(--sa-muted-fg);">{{ $p->api_key_prefix ? $p->api_key_prefix . '…' : '—' }}</td>
                        <td class="px-4 py-3 text-xs" style="color:var(--sa-muted-fg);">{{ $p->webhook_url ? 'Oui' : 'Non' }}</td>
                        <td class="px-4 py-3" style="color:var(--sa-fg);">{{ $p->parcels_count }}</td>
                        <td class="px-4 py-3 text-xs" style="color:var(--sa-muted-fg);">{{ $p->last_used_at?->diffForHumans() ?? 'Jamais' }}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-10 text-center" style="color:var(--sa-muted-fg);">Aucun partenaire.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-layouts.admin-super>
