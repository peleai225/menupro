@php
    use App\Models\LedgerEntry;
    $fmt = fn ($v) => number_format((int) $v, 0, ',', ' ') . ' F';
    $statusStyles = [
        'pending'   => 'background:#fef9c3;color:#854d0e;',
        'succeeded' => 'background:#dcfce7;color:#166534;',
        'failed'    => 'background:#fee2e2;color:#991b1b;',
        'refunded'  => 'background:#ffedd5;color:#9a3412;',
        'paid_out'  => 'background:#e0e7ff;color:#3730a3;',
    ];
    $query = request()->query();
    $inputStyle = 'background:var(--sa-bg);border-color:var(--sa-border);color:var(--sa-fg);';
@endphp
<x-layouts.admin-super title="Journal des transactions">
    {{-- En-tête --}}
    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4 mb-6">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <a href="{{ route('super-admin.finances.index') }}" style="color:var(--sa-muted-fg);" aria-label="Retour aux finances">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
                <h1 class="text-2xl font-bold" style="color:var(--sa-fg);">Journal des transactions</h1>
            </div>
            <p class="ml-8 text-sm" style="color:var(--sa-muted-fg);">
                Chaque mouvement d'argent de la plateforme : qui a payé, pour quoi, frais Jeko, commission MenuPro et part du restaurant.
            </p>
        </div>
        <a href="{{ route('super-admin.finances.ledger.export', $query) }}"
           class="h-10 px-4 inline-flex items-center gap-2 rounded-xl border text-sm font-medium self-start"
           style="background:var(--sa-card);border-color:var(--sa-border);color:var(--sa-fg);">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
            Exporter (CSV Excel)
        </a>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 rounded-xl border text-sm" style="background:#f0fdf4;border-color:#86efac;color:#166534;">{{ session('success') }}</div>
    @endif
    @if($errors->any())
        <div class="mb-6 p-4 rounded-xl border text-sm" style="background:#fef2f2;border-color:#fca5a5;color:#991b1b;">{{ $errors->first() }}</div>
    @endif

    {{-- Bandeau de totaux --}}
    <p class="text-xs font-semibold uppercase tracking-wide mb-2" style="color:var(--sa-muted-fg);">
        Du {{ $filters['from']->format('d/m/Y') }} au {{ $filters['to']->format('d/m/Y') }}
        · {{ number_format($totals['lines_count'], 0, ',', ' ') }} ligne(s)
        @unless($filters['demo']) · hors compte démo @endunless
    </p>
    <div class="grid grid-cols-2 md:grid-cols-4 xl:grid-cols-7 gap-3 mb-4">
        @foreach([
            ['Encaissé brut', $totals['collected'], 'En ligne ' . $fmt($totals['collected_online']) . ' · espèces ' . $fmt($totals['collected_cash']), '#0f172a'],
            ['Frais Jeko', $totals['fees'], 'Encaissements ' . $fmt($totals['fees_collection']) . ' · sorties ' . $fmt($totals['fees_payout']), '#dc2626'],
            ['Commissions MenuPro', $totals['commissions'], 'Commandes et livraisons (remboursements déduits)', '#7c3aed'],
            ['Abonnements', $totals['subscriptions'], 'Payés par les restaurants et stands', '#0891b2'],
            ['Reversé aux restaurants', $totals['paid_out'], 'Remboursé aux clients ' . $fmt($totals['refunded']), '#4f46e5'],
            ['À reverser', $totals['to_pay_out'], 'Part restaurant des paiements en ligne non reversés', '#d97706'],
            ['Net MenuPro', $totals['net'], 'Commissions + abonnements − frais', $totals['net'] < 0 ? '#dc2626' : '#16a34a'],
        ] as [$label, $value, $hint, $color])
            <div class="rounded-xl p-4 border shadow-sm" style="background:var(--sa-card);border-color:var(--sa-border);">
                <p class="text-xs font-medium" style="color:var(--sa-muted-fg);">{{ $label }}</p>
                <p class="text-xl font-bold mt-1 tabular-nums" style="color:{{ $color }};">{{ $fmt($value) }}</p>
                <p class="text-[11px] mt-1 leading-snug" style="color:var(--sa-muted-fg);">{{ $hint }}</p>
            </div>
        @endforeach
    </div>

    {{-- Totaux par type --}}
    @if(count($byType))
        <div class="border rounded-xl shadow-sm mb-6 overflow-x-auto" style="background:var(--sa-card);border-color:var(--sa-border);">
            <table class="min-w-full text-sm">
                <thead style="background:var(--sa-bg);">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-bold uppercase" style="color:var(--sa-muted-fg);">Par type</th>
                        <th class="px-4 py-2 text-right text-xs font-bold uppercase" style="color:var(--sa-muted-fg);">Lignes</th>
                        <th class="px-4 py-2 text-right text-xs font-bold uppercase" style="color:var(--sa-muted-fg);">Montant brut</th>
                        <th class="px-4 py-2 text-right text-xs font-bold uppercase" style="color:var(--sa-muted-fg);">Frais</th>
                        <th class="px-4 py-2 text-right text-xs font-bold uppercase" style="color:var(--sa-muted-fg);">Net MenuPro</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($byType as $row)
                        <tr class="border-t" style="border-color:var(--sa-border);">
                            <td class="px-4 py-2" style="color:var(--sa-fg);">
                                <a href="{{ route('super-admin.finances.ledger.index', array_merge($query, ['type' => $row['type'], 'page' => null])) }}" class="hover:underline">{{ $row['label'] }}</a>
                            </td>
                            <td class="px-4 py-2 text-right tabular-nums" style="color:var(--sa-muted-fg);">{{ $row['count'] }}</td>
                            <td class="px-4 py-2 text-right tabular-nums font-semibold" style="color:var(--sa-fg);">{{ $fmt($row['gross']) }}</td>
                            <td class="px-4 py-2 text-right tabular-nums" style="color:#dc2626;">{{ $fmt($row['fees']) }}</td>
                            <td class="px-4 py-2 text-right tabular-nums" style="color:{{ $row['net'] < 0 ? '#dc2626' : '#16a34a' }};">{{ $fmt($row['net']) }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

    {{-- Filtres --}}
    <form method="GET" action="{{ route('super-admin.finances.ledger.index') }}" class="border rounded-xl p-4 mb-6 shadow-sm grid grid-cols-2 md:grid-cols-4 xl:grid-cols-8 gap-3 items-end" style="background:var(--sa-card);border-color:var(--sa-border);">
        <label class="text-xs" style="color:var(--sa-muted-fg);">Du
            <input type="date" name="du" value="{{ $filters['from']->format('Y-m-d') }}" class="mt-1 w-full h-10 px-3 rounded-lg border text-sm" style="{{ $inputStyle }}">
        </label>
        <label class="text-xs" style="color:var(--sa-muted-fg);">Au
            <input type="date" name="au" value="{{ $filters['to']->format('Y-m-d') }}" class="mt-1 w-full h-10 px-3 rounded-lg border text-sm" style="{{ $inputStyle }}">
        </label>
        <label class="text-xs" style="color:var(--sa-muted-fg);">Type
            <select name="type" class="mt-1 w-full h-10 px-2 rounded-lg border text-sm" style="{{ $inputStyle }}">
                <option value="">Tous</option>
                <option value="orders" @selected($filters['type'] === 'orders')>Toutes les commandes</option>
                <option value="subscriptions" @selected($filters['type'] === 'subscriptions')>Tous les abonnements</option>
                @foreach(LedgerEntry::TYPE_LABELS as $key => $label)
                    <option value="{{ $key }}" @selected($filters['type'] === $key)>{{ $label }}</option>
                @endforeach
                <option value="{{ LedgerEntry::FILTER_JEKO_FEES }}" @selected($filters['type'] === LedgerEntry::FILTER_JEKO_FEES)>Frais Jeko (lignes avec frais)</option>
            </select>
        </label>
        <label class="text-xs" style="color:var(--sa-muted-fg);">Restaurant
            <select name="restaurant" class="mt-1 w-full h-10 px-2 rounded-lg border text-sm" style="{{ $inputStyle }}">
                <option value="">Tous</option>
                @foreach($restaurants as $id => $name)
                    <option value="{{ $id }}" @selected($filters['restaurant'] === $id)>{{ $name }}</option>
                @endforeach
            </select>
        </label>
        <label class="text-xs" style="color:var(--sa-muted-fg);">Moyen
            <select name="moyen" class="mt-1 w-full h-10 px-2 rounded-lg border text-sm" style="{{ $inputStyle }}">
                <option value="">Tous</option>
                @foreach(LedgerEntry::METHOD_LABELS as $key => $label)
                    <option value="{{ $key }}" @selected($filters['method'] === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <label class="text-xs" style="color:var(--sa-muted-fg);">Statut
            <select name="statut" class="mt-1 w-full h-10 px-2 rounded-lg border text-sm" style="{{ $inputStyle }}">
                <option value="">Tous</option>
                @foreach(LedgerEntry::STATUS_LABELS as $key => $label)
                    <option value="{{ $key }}" @selected($filters['status'] === $key)>{{ $label }}</option>
                @endforeach
            </select>
        </label>
        <label class="text-xs col-span-2" style="color:var(--sa-muted-fg);">Recherche
            <input type="search" name="q" value="{{ $filters['q'] }}" placeholder="Référence Jeko, téléphone, n° de commande…" class="mt-1 w-full h-10 px-3 rounded-lg border text-sm" style="{{ $inputStyle }}">
        </label>
        <label class="col-span-2 md:col-span-2 flex items-center gap-2 text-sm h-10" style="color:var(--sa-fg);">
            <input type="checkbox" name="demo" value="1" @checked($filters['demo']) class="rounded">
            Inclure le compte démo
        </label>
        <div class="col-span-2 flex gap-2">
            <button type="submit" class="h-10 px-5 rounded-lg text-white text-sm font-medium" style="background:#6366f1;">Filtrer</button>
            <a href="{{ route('super-admin.finances.ledger.index') }}" class="h-10 px-4 inline-flex items-center rounded-lg border text-sm" style="border-color:var(--sa-border);color:var(--sa-muted-fg);">Réinitialiser</a>
        </div>
    </form>

    {{-- Tableau --}}
    <div class="border rounded-xl shadow-sm overflow-hidden mb-8" style="background:var(--sa-card);border-color:var(--sa-border);">
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead class="border-b" style="background:var(--sa-bg);border-color:var(--sa-border);">
                    <tr>
                        @foreach(['Date', 'Type', 'Payé par', 'Bénéficiaire', 'Restaurant', 'Canal / moyen', 'Réf. passerelle'] as $h)
                            <th class="px-3 py-3 text-left text-xs font-bold uppercase whitespace-nowrap" style="color:var(--sa-muted-fg);">{{ $h }}</th>
                        @endforeach
                        @foreach(['Brut', 'Frais', 'Commission', 'Part resto', 'Net MenuPro'] as $h)
                            <th class="px-3 py-3 text-right text-xs font-bold uppercase whitespace-nowrap" style="color:var(--sa-muted-fg);">{{ $h }}</th>
                        @endforeach
                        <th class="px-3 py-3 text-left text-xs font-bold uppercase" style="color:var(--sa-muted-fg);">Statut</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($entries as $e)
                        <tr class="border-t align-top hover:bg-black/[0.02] cursor-pointer" style="border-color:var(--sa-border);"
                            onclick="window.location='{{ route('super-admin.finances.ledger.show', $e) }}'">
                            <td class="px-3 py-3 whitespace-nowrap tabular-nums" style="color:var(--sa-muted-fg);">{{ $e->occurred_at?->format('d/m/Y H:i') }}</td>
                            <td class="px-3 py-3" style="color:var(--sa-fg);">
                                <a href="{{ route('super-admin.finances.ledger.show', $e) }}" class="font-medium hover:underline">{{ $e->type_label }}</a>
                                @if($e->order_reference)<div class="text-xs font-mono" style="color:var(--sa-muted-fg);">#{{ $e->order_reference }}</div>@endif
                            </td>
                            <td class="px-3 py-3" style="color:var(--sa-fg);">
                                {{ $e->payer_name ?: '—' }}
                                @if($e->payer_phone)<div class="text-xs tabular-nums" style="color:var(--sa-muted-fg);">{{ $e->payer_phone }}</div>@endif
                            </td>
                            <td class="px-3 py-3" style="color:var(--sa-fg);">{{ $e->beneficiary_name ?: '—' }}</td>
                            <td class="px-3 py-3" style="color:var(--sa-fg);">
                                {{ $e->restaurant_name ?: '—' }}
                                @if($e->restaurant_plan_label)<div class="text-xs" style="color:var(--sa-muted-fg);">{{ $e->restaurant_plan_label }}</div>@endif
                            </td>
                            <td class="px-3 py-3 whitespace-nowrap" style="color:var(--sa-fg);">
                                {{ $e->channel_label }}
                                <div class="text-xs" style="color:var(--sa-muted-fg);">{{ $e->method_label }}</div>
                            </td>
                            <td class="px-3 py-3 font-mono text-xs break-all max-w-[12rem]" style="color:var(--sa-fg);">{{ $e->gateway_reference ?: '—' }}</td>
                            <td class="px-3 py-3 text-right tabular-nums whitespace-nowrap font-semibold" style="color:var(--sa-fg);">{{ $e->direction === 'out' ? '−' : '' }}{{ $fmt($e->gross_amount) }}</td>
                            <td class="px-3 py-3 text-right tabular-nums whitespace-nowrap" style="color:#dc2626;">{{ $e->gateway_fee ? $fmt($e->gateway_fee) : '—' }}</td>
                            <td class="px-3 py-3 text-right tabular-nums whitespace-nowrap" style="color:#7c3aed;">{{ $e->menupro_commission ? $fmt($e->menupro_commission) : '—' }}</td>
                            <td class="px-3 py-3 text-right tabular-nums whitespace-nowrap" style="color:var(--sa-fg);">{{ $e->restaurant_share ? $fmt($e->restaurant_share) : '—' }}</td>
                            <td class="px-3 py-3 text-right tabular-nums whitespace-nowrap font-semibold" style="color:{{ $e->net_menupro < 0 ? '#dc2626' : '#16a34a' }};">{{ $fmt($e->net_menupro) }}</td>
                            <td class="px-3 py-3 whitespace-nowrap">
                                <span class="inline-flex px-2 py-0.5 rounded-md text-xs font-semibold" style="{{ $statusStyles[$e->status] ?? '' }}">{{ $e->status_label }}</span>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="13" class="px-4 py-12 text-center text-sm" style="color:var(--sa-muted-fg);">
                                Aucun mouvement sur cette période avec ces filtres.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($entries->hasPages())
            <div class="px-4 py-3 border-t" style="border-color:var(--sa-border);">{{ $entries->links() }}</div>
        @endif
    </div>

    {{-- Paramètres de frais --}}
    <form method="POST" action="{{ route('super-admin.finances.ledger.fees') }}" class="border rounded-xl p-5 shadow-sm" style="background:var(--sa-card);border-color:var(--sa-border);">
        @csrf
        <h2 class="font-semibold mb-1" style="color:var(--sa-fg);">Frais Jeko</h2>
        <p class="text-sm mb-4" style="color:var(--sa-muted-fg);">
            Jeko prélève des frais à chaque transfert. Le journal les applique séparément aux <strong>encaissements</strong> Jeko
            (commande ou abonnement payé par Jeko) et aux <strong>sorties</strong> Jeko (reversement au restaurant, remboursement).
            Mettez 0 si l'un des deux ne s'applique pas. Le taux est figé sur chaque ligne au moment où elle est écrite :
            un changement ne modifie pas l'historique. Les espèces n'ont jamais de frais.
        </p>
        <div class="flex flex-wrap items-end gap-4">
            <label class="text-xs" style="color:var(--sa-muted-fg);">Frais sur encaissement (%)
                <input type="number" step="0.01" min="0" max="20" name="collection_percent" value="{{ old('collection_percent', $fees['collection']) }}" class="mt-1 block w-36 h-10 px-3 rounded-lg border text-sm" style="{{ $inputStyle }}">
            </label>
            <label class="text-xs" style="color:var(--sa-muted-fg);">Frais sur reversement / remboursement (%)
                <input type="number" step="0.01" min="0" max="20" name="payout_percent" value="{{ old('payout_percent', $fees['payout']) }}" class="mt-1 block w-36 h-10 px-3 rounded-lg border text-sm" style="{{ $inputStyle }}">
            </label>
            <button type="submit" class="h-10 px-5 rounded-lg text-white text-sm font-medium" style="background:#0f172a;">Enregistrer</button>
        </div>
    </form>
</x-layouts.admin-super>
