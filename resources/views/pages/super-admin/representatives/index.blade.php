@php
    $fmt = fn ($v) => number_format((int) $v, 0, ',', ' ') . ' F';
@endphp
<x-layouts.admin-super title="Représentants de ville">
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-4">
        <div>
            <h1 class="text-2xl font-bold" style="color:var(--sa-fg);">Représentants de ville</h1>
            <p class="text-sm" style="color:var(--sa-muted-fg);">
                Part du mois en cours ({{ \Illuminate\Support\Carbon::createFromFormat('Y-m', $month)->translatedFormat('F Y') }}) calculée en direct depuis le journal des transactions.
                Le versement au wallet est fait par <code>representants:calculer-mois</code> (le 2 de chaque mois).
            </p>
        </div>
        <a href="{{ route('super-admin.representatives.export') }}" class="h-10 px-4 inline-flex items-center gap-2 rounded-xl border text-sm font-medium self-start" style="background:var(--sa-card);border-color:var(--sa-border);color:var(--sa-fg);">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Exporter les relevés (CSV)
        </a>
    </div>

    @include('pages.super-admin.representatives._nav')

    <div class="border rounded-xl shadow-sm overflow-x-auto" style="background:var(--sa-card);border-color:var(--sa-border);">
        <table class="min-w-full text-sm">
            <thead style="background:var(--sa-bg);">
                <tr>
                    @foreach(['Représentant', 'Ville', 'Exclusivité', 'Essai', 'Restaurants', 'Actifs', 'Abonnements (mois)', 'Comm. livraisons (mois)', 'Part (mois)', 'Objectif trimestre'] as $h)
                        <th class="px-4 py-2 text-left text-xs font-bold uppercase whitespace-nowrap" style="color:var(--sa-muted-fg);">{{ $h }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($profiles as $p)
                    <tr class="border-t" style="border-color:var(--sa-border);">
                        <td class="px-4 py-3">
                            <a href="{{ route('super-admin.representatives.show', $p) }}" class="font-semibold hover:underline" style="color:var(--sa-fg);">{{ $p->user?->name }}</a>
                            <p class="text-xs" style="color:var(--sa-muted-fg);">{{ $p->user?->phone }}</p>
                        </td>
                        <td class="px-4 py-3" style="color:var(--sa-fg);">{{ $p->city }}</td>
                        <td class="px-4 py-3">{!! $p->exclusivity_active ? '<span class="px-2 py-0.5 rounded-full text-xs font-semibold" style="background:#dcfce7;color:#166534;">Active</span>' : '<span class="text-xs" style="color:var(--sa-muted-fg);">—</span>' !!}</td>
                        <td class="px-4 py-3 whitespace-nowrap text-xs" style="color:var(--sa-fg);">
                            @if($p->trial_ends_at){{ $p->isInTrial() ? 'jusqu\'au ' : 'terminé le ' }}{{ $p->trial_ends_at->format('d/m/Y') }}@else — @endif
                        </td>
                        <td class="px-4 py-3 tabular-nums" style="color:var(--sa-fg);">{{ $p->current['restaurants_attributed'] }}</td>
                        <td class="px-4 py-3 tabular-nums" style="color:var(--sa-fg);">{{ $p->current['restaurants_active'] }}</td>
                        <td class="px-4 py-3 tabular-nums" style="color:var(--sa-fg);">{{ $fmt($p->current['subscription_collected']) }}</td>
                        <td class="px-4 py-3 tabular-nums" style="color:var(--sa-fg);">{{ $fmt($p->current['delivery_commission']) }}</td>
                        <td class="px-4 py-3 tabular-nums font-bold" style="color:#16a34a;">{{ $fmt($p->current['total_share']) }}</td>
                        <td class="px-4 py-3 min-w-[160px]">
                            <p class="text-xs mb-1" style="color:var(--sa-fg);">{{ $p->quarter['achieved'] }} / {{ $p->quarter['target'] }} payants · {{ $p->quarter['label'] }}</p>
                            <div class="h-2 rounded-full" style="background:var(--sa-bg);">
                                <div class="h-2 rounded-full" style="width:{{ $p->quarter['percent'] }}%;background:#f97316;"></div>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="10" class="px-4 py-10 text-center" style="color:var(--sa-muted-fg);">Aucun représentant. Créez-en un depuis une candidature acceptée.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>
</x-layouts.admin-super>
