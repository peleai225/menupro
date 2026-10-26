@php
    use App\Models\QrCampaign;
    $inputStyle = 'background:var(--sa-bg);border-color:var(--sa-border);color:var(--sa-fg);';
@endphp
<x-layouts.admin-super title="Campagnes QR">
    <div class="mb-4">
        <h1 class="text-2xl font-bold" style="color:var(--sa-fg);">Campagnes QR</h1>
        <p class="text-sm" style="color:var(--sa-muted-fg);">Liens courts suivis pour bâches et affiches : chaque scan est compté (date, mobile/ordinateur, aucune donnée personnelle) puis redirigé.</p>
    </div>

    @include('pages.super-admin.representatives._nav')

    <form method="POST" action="{{ route('super-admin.qr-campaigns.store') }}" x-data="{ target: '{{ old('target_type', QrCampaign::TARGET_CLIENT_APP) }}' }"
          class="rounded-xl p-5 border shadow-sm mb-6 grid grid-cols-1 md:grid-cols-6 gap-3 items-end" style="background:var(--sa-card);border-color:var(--sa-border);">
        @csrf
        <label class="block text-xs font-medium md:col-span-2" style="color:var(--sa-muted-fg);">Nom
            <input name="name" value="{{ old('name') }}" required maxlength="120" placeholder="Bâche gare Daloa" class="mt-1 w-full h-10 px-3 rounded-xl border text-sm" style="{{ $inputStyle }}">
        </label>
        <label class="block text-xs font-medium" style="color:var(--sa-muted-fg);">Cible
            <select name="target_type" x-model="target" class="mt-1 w-full h-10 px-3 rounded-xl border text-sm" style="{{ $inputStyle }}">
                @foreach(QrCampaign::TARGET_LABELS as $value => $label)
                    <option value="{{ $value }}">{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <label class="block text-xs font-medium" style="color:var(--sa-muted-fg);">Ville
            <input name="city" value="{{ old('city') }}" maxlength="80" list="qr-cities" class="mt-1 w-full h-10 px-3 rounded-xl border text-sm" style="{{ $inputStyle }}">
            <datalist id="qr-cities">@foreach($cities as $c)<option value="{{ $c }}">@endforeach</datalist>
        </label>
        <label class="block text-xs font-medium" style="color:var(--sa-muted-fg);">Emplacement
            <input name="location" value="{{ old('location') }}" maxlength="160" placeholder="Entrée gare routière" class="mt-1 w-full h-10 px-3 rounded-xl border text-sm" style="{{ $inputStyle }}">
        </label>
        <button class="h-10 px-4 rounded-xl text-sm font-medium text-white" style="background:#f97316;">Créer</button>
        <label class="block text-xs font-medium md:col-span-3" style="color:var(--sa-muted-fg);" x-show="target === '{{ QrCampaign::TARGET_INTERNAL_URL }}'" x-cloak>Chemin interne
            <input name="target_path" value="{{ old('target_path') }}" maxlength="255" placeholder="/tarifs" class="mt-1 w-full h-10 px-3 rounded-xl border text-sm" style="{{ $inputStyle }}">
        </label>
    </form>

    <div class="border rounded-xl shadow-sm overflow-x-auto" style="background:var(--sa-card);border-color:var(--sa-border);">
        <table class="min-w-full text-sm">
            <thead style="background:var(--sa-bg);">
                <tr>
                    @foreach(['Campagne', 'Lien court', 'Cible', 'Ville / emplacement', 'Scans', 'Inscriptions', 'État'] as $h)
                        <th class="px-4 py-2 text-left text-xs font-bold uppercase" style="color:var(--sa-muted-fg);">{{ $h }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($campaigns as $c)
                    <tr class="border-t" style="border-color:var(--sa-border);">
                        <td class="px-4 py-3"><a href="{{ route('super-admin.qr-campaigns.show', $c) }}" class="font-semibold hover:underline" style="color:var(--sa-fg);">{{ $c->name }}</a></td>
                        <td class="px-4 py-3 font-mono text-xs" style="color:var(--sa-fg);">{{ $c->short_url }}</td>
                        <td class="px-4 py-3" style="color:var(--sa-fg);">{{ $c->target_label }}</td>
                        <td class="px-4 py-3" style="color:var(--sa-fg);">{{ $c->city ?: '—' }}@if($c->location)<span class="block text-xs" style="color:var(--sa-muted-fg);">{{ $c->location }}</span>@endif</td>
                        <td class="px-4 py-3 tabular-nums font-bold" style="color:var(--sa-fg);">{{ number_format($c->scans_count, 0, ',', ' ') }}</td>
                        <td class="px-4 py-3 tabular-nums" style="color:var(--sa-fg);">{{ $c->target_type === QrCampaign::TARGET_RESTAURANT_SIGNUP ? $c->restaurants_count : '—' }}</td>
                        <td class="px-4 py-3">{!! $c->is_active ? '<span class="px-2 py-0.5 rounded-full text-xs font-semibold" style="background:#dcfce7;color:#166534;">Actif</span>' : '<span class="px-2 py-0.5 rounded-full text-xs font-semibold" style="background:#f1f5f9;color:#475569;">Désactivé</span>' !!}</td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-10 text-center" style="color:var(--sa-muted-fg);">Aucune campagne.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($campaigns->hasPages())
            <div class="px-4 py-3 border-t" style="border-color:var(--sa-border);">{{ $campaigns->links() }}</div>
        @endif
    </div>
</x-layouts.admin-super>
