@php
    use App\Models\PartnerApplication;
    $statusStyles = [
        'nouvelle' => 'background:#dbeafe;color:#1e40af;',
        'entretien' => 'background:#fef9c3;color:#854d0e;',
        'acceptee' => 'background:#dcfce7;color:#166534;',
        'refusee' => 'background:#fee2e2;color:#991b1b;',
    ];
    $inputStyle = 'background:var(--sa-bg);border-color:var(--sa-border);color:var(--sa-fg);';
@endphp
<x-layouts.admin-super title="Candidatures représentants">
    <div class="mb-4">
        <h1 class="text-2xl font-bold" style="color:var(--sa-fg);">Programme « Représentant de ville »</h1>
        <p class="text-sm" style="color:var(--sa-muted-fg);">
            Candidatures reçues depuis la page publique
            <a href="{{ route('representative.program') }}" target="_blank" class="underline">/devenir-representant</a>.
        </p>
    </div>

    @include('pages.super-admin.representatives._nav')

    <form method="GET" class="flex flex-wrap items-end gap-3 mb-4">
        <div>
            <label class="block text-xs font-medium mb-1" style="color:var(--sa-muted-fg);">Statut</label>
            <select name="status" class="h-10 px-3 rounded-xl border text-sm" style="{{ $inputStyle }}">
                <option value="">Tous ({{ $counts->sum() }})</option>
                @foreach(PartnerApplication::STATUS_LABELS as $value => $label)
                    <option value="{{ $value }}" @selected($status === $value)>{{ $label }} ({{ $counts[$value] ?? 0 }})</option>
                @endforeach
            </select>
        </div>
        <div>
            <label class="block text-xs font-medium mb-1" style="color:var(--sa-muted-fg);">Recherche</label>
            <input type="search" name="q" value="{{ $search }}" placeholder="Nom, téléphone, ville" class="h-10 px-3 rounded-xl border text-sm" style="{{ $inputStyle }}">
        </div>
        <button class="h-10 px-4 rounded-xl text-sm font-medium text-white" style="background:#f97316;">Filtrer</button>
    </form>

    <div class="border rounded-xl shadow-sm overflow-x-auto" style="background:var(--sa-card);border-color:var(--sa-border);">
        <table class="min-w-full text-sm">
            <thead style="background:var(--sa-bg);">
                <tr>
                    @foreach(['Reçue le', 'Candidat', 'Téléphone', 'Ville', 'Statut légal', 'Disponibilité', 'Statut'] as $h)
                        <th class="px-4 py-2 text-left text-xs font-bold uppercase" style="color:var(--sa-muted-fg);">{{ $h }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody>
                @forelse($applications as $a)
                    <tr class="border-t" style="border-color:var(--sa-border);">
                        <td class="px-4 py-3 whitespace-nowrap" style="color:var(--sa-muted-fg);">{{ $a->created_at->format('d/m/Y H:i') }}</td>
                        <td class="px-4 py-3">
                            <a href="{{ route('super-admin.partner-applications.show', $a) }}" class="font-semibold hover:underline" style="color:var(--sa-fg);">{{ $a->full_name }}</a>
                            @if($a->attachment_path)<span class="ml-1 text-xs" title="Pièce jointe">📎</span>@endif
                        </td>
                        <td class="px-4 py-3 whitespace-nowrap" style="color:var(--sa-fg);">{{ $a->phone }}</td>
                        <td class="px-4 py-3" style="color:var(--sa-fg);">{{ $a->city }}</td>
                        <td class="px-4 py-3" style="color:var(--sa-fg);">{{ $a->legal_status_label }}</td>
                        <td class="px-4 py-3" style="color:var(--sa-fg);">{{ $a->availability_label }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-0.5 rounded-full text-xs font-semibold" style="{{ $statusStyles[$a->status] ?? '' }}">{{ $a->status_label }}</span>
                            @if($a->user_id)<span class="ml-1 text-xs" style="color:#16a34a;">✔ représentant créé</span>@endif
                        </td>
                    </tr>
                @empty
                    <tr><td colspan="7" class="px-4 py-10 text-center" style="color:var(--sa-muted-fg);">Aucune candidature.</td></tr>
                @endforelse
            </tbody>
        </table>
        @if($applications->hasPages())
            <div class="px-4 py-3 border-t" style="border-color:var(--sa-border);">{{ $applications->links() }}</div>
        @endif
    </div>
</x-layouts.admin-super>
