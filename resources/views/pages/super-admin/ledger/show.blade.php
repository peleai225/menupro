@php
    $fmt = fn ($v) => number_format((int) $v, 0, ',', ' ') . ' F';
    $statusStyles = [
        'pending'   => 'background:#fef9c3;color:#854d0e;',
        'succeeded' => 'background:#dcfce7;color:#166534;',
        'failed'    => 'background:#fee2e2;color:#991b1b;',
        'refunded'  => 'background:#ffedd5;color:#9a3412;',
        'paid_out'  => 'background:#e0e7ff;color:#3730a3;',
    ];
    $rows = [
        'Date' => $entry->occurred_at?->format('d/m/Y H:i:s'),
        'Type' => $entry->type_label,
        'Payé par' => trim(($entry->payer_name ?? '') . ($entry->payer_phone ? ' — ' . $entry->payer_phone : '')) ?: '—',
        'Bénéficiaire' => $entry->beneficiary_name ?: '—',
        'Restaurant' => trim(($entry->restaurant_name ?? '—') . ($entry->restaurant_plan_label ? ' (' . $entry->restaurant_plan_label . ')' : '')),
        'Canal d\'origine' => $entry->channel_label,
        'Enregistré via' => $entry->via_label,
        'Moyen' => $entry->method_label . ($entry->method_detail ? ' (' . $entry->method_detail . ')' : ''),
        'Référence passerelle' => $entry->gateway_reference ?: '—',
        'Référence secondaire' => $entry->gateway_reference_alt ?: '—',
        'N° de commande' => $entry->order_reference ?: '—',
        'Description' => $entry->description ?: '—',
    ];
@endphp
<x-layouts.admin-super title="Transaction #{{ $entry->id }}">
    <div class="flex items-center gap-3 mb-6">
        <a href="{{ url()->previous() !== url()->current() ? url()->previous() : route('super-admin.finances.ledger.index') }}" style="color:var(--sa-muted-fg);" aria-label="Retour au journal">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <h1 class="text-2xl font-bold" style="color:var(--sa-fg);">{{ $entry->type_label }}</h1>
        <span class="inline-flex px-2 py-0.5 rounded-md text-xs font-semibold" style="{{ $statusStyles[$entry->status] ?? '' }}">{{ $entry->status_label }}</span>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
        {{-- Informations --}}
        <div class="lg:col-span-2 border rounded-xl shadow-sm" style="background:var(--sa-card);border-color:var(--sa-border);">
            <dl class="divide-y" style="border-color:var(--sa-border);">
                @foreach($rows as $label => $value)
                    <div class="px-5 py-3 grid grid-cols-3 gap-4 text-sm">
                        <dt style="color:var(--sa-muted-fg);">{{ $label }}</dt>
                        <dd class="col-span-2 break-all {{ str_contains($label, 'Référence') ? 'font-mono' : '' }}" style="color:var(--sa-fg);">{{ $value }}</dd>
                    </div>
                @endforeach
            </dl>
        </div>

        {{-- Montants --}}
        <div class="border rounded-xl shadow-sm p-5 self-start" style="background:var(--sa-card);border-color:var(--sa-border);">
            <h2 class="font-semibold mb-3" style="color:var(--sa-fg);">Montants</h2>
            <dl class="space-y-2 text-sm">
                <div class="flex justify-between"><dt style="color:var(--sa-muted-fg);">Montant brut</dt><dd class="font-bold tabular-nums" style="color:var(--sa-fg);">{{ $entry->direction === 'out' ? '−' : '' }}{{ $fmt($entry->gross_amount) }}</dd></div>
                <div class="flex justify-between"><dt style="color:var(--sa-muted-fg);">{{ $entry->fee_label }} @if($entry->gateway_fee_rate)({{ str_replace('.', ',', (string) $entry->gateway_fee_rate) }} %)@endif</dt><dd class="tabular-nums" style="color:#dc2626;">{{ $fmt($entry->gateway_fee) }}</dd></div>
                <div class="flex justify-between"><dt style="color:var(--sa-muted-fg);">Commission MenuPro</dt><dd class="tabular-nums" style="color:#7c3aed;">{{ $fmt($entry->menupro_commission) }}</dd></div>
                <div class="flex justify-between"><dt style="color:var(--sa-muted-fg);">Part restaurant</dt><dd class="tabular-nums" style="color:var(--sa-fg);">{{ $fmt($entry->restaurant_share) }}</dd></div>
                @if($entry->driver_share)
                    <div class="flex justify-between"><dt style="color:var(--sa-muted-fg);">Part livreur</dt><dd class="tabular-nums" style="color:var(--sa-fg);">{{ $fmt($entry->driver_share) }}</dd></div>
                @endif
                <div class="flex justify-between border-t pt-2" style="border-color:var(--sa-border);"><dt class="font-semibold" style="color:var(--sa-fg);">Net MenuPro</dt><dd class="font-bold tabular-nums" style="color:{{ $entry->net_menupro < 0 ? '#dc2626' : '#16a34a' }};">{{ $fmt($entry->net_menupro) }}</dd></div>
            </dl>

            <div class="mt-5 space-y-2 text-sm">
                @if($entry->order_id)
                    <a href="{{ route('super-admin.orders.show', $entry->order_id) }}" class="block hover:underline" style="color:#4f46e5;">Voir la commande →</a>
                @endif
                @if($entry->subscription_id)
                    <a href="{{ route('super-admin.subscriptions.show', $entry->subscription_id) }}" class="block hover:underline" style="color:#4f46e5;">Voir l'abonnement →</a>
                @endif
                @if($entry->restaurant_id)
                    <a href="{{ route('super-admin.restaurants.show', $entry->restaurant_id) }}" class="block hover:underline" style="color:#4f46e5;">Voir le restaurant →</a>
                    <a href="{{ route('super-admin.finances.ledger.index', ['restaurant' => $entry->restaurant_id, 'du' => now()->subYear()->format('Y-m-d')]) }}" class="block hover:underline" style="color:#4f46e5;">Tous les mouvements de ce restaurant →</a>
                @endif
            </div>
        </div>
    </div>

    {{-- Chaîne liée --}}
    <div class="border rounded-xl shadow-sm overflow-hidden" style="background:var(--sa-card);border-color:var(--sa-border);">
        <div class="px-5 py-3 border-b" style="border-color:var(--sa-border);">
            <h2 class="font-semibold" style="color:var(--sa-fg);">Chaîne complète</h2>
            <p class="text-xs" style="color:var(--sa-muted-fg);">Paiement → frais → commission → reversement / remboursement pour {{ $entry->order_id ? 'cette commande' : ($entry->subscription_id ? 'cet abonnement' : 'ce mouvement') }}.</p>
        </div>
        <div class="overflow-x-auto">
            <table class="min-w-full text-sm">
                <thead style="background:var(--sa-bg);">
                    <tr>
                        @foreach(['Date', 'Mouvement', 'Moyen', 'Référence', 'Brut', 'Frais', 'Commission', 'Part resto', 'Net MenuPro', 'Statut'] as $h)
                            <th class="px-3 py-2 text-xs font-bold uppercase {{ in_array($h, ['Brut', 'Frais', 'Commission', 'Part resto', 'Net MenuPro']) ? 'text-right' : 'text-left' }}" style="color:var(--sa-muted-fg);">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @foreach($chain as $c)
                        <tr class="border-t" style="border-color:var(--sa-border);{{ $c->id === $entry->id ? 'background:rgba(99,102,241,0.06);' : '' }}">
                            <td class="px-3 py-2 whitespace-nowrap tabular-nums" style="color:var(--sa-muted-fg);">{{ $c->occurred_at?->format('d/m/Y H:i') }}</td>
                            <td class="px-3 py-2" style="color:var(--sa-fg);"><a href="{{ route('super-admin.finances.ledger.show', $c) }}" class="hover:underline">{{ $c->type_label }}</a></td>
                            <td class="px-3 py-2" style="color:var(--sa-fg);">{{ $c->method_label }}</td>
                            <td class="px-3 py-2 font-mono text-xs break-all" style="color:var(--sa-fg);">{{ $c->gateway_reference ?: '—' }}</td>
                            <td class="px-3 py-2 text-right tabular-nums" style="color:var(--sa-fg);">{{ $c->direction === 'out' ? '−' : '' }}{{ $fmt($c->gross_amount) }}</td>
                            <td class="px-3 py-2 text-right tabular-nums" style="color:#dc2626;">{{ $fmt($c->gateway_fee) }}</td>
                            <td class="px-3 py-2 text-right tabular-nums" style="color:#7c3aed;">{{ $fmt($c->menupro_commission) }}</td>
                            <td class="px-3 py-2 text-right tabular-nums" style="color:var(--sa-fg);">{{ $fmt($c->restaurant_share) }}</td>
                            <td class="px-3 py-2 text-right tabular-nums font-semibold" style="color:{{ $c->net_menupro < 0 ? '#dc2626' : '#16a34a' }};">{{ $fmt($c->net_menupro) }}</td>
                            <td class="px-3 py-2"><span class="inline-flex px-2 py-0.5 rounded-md text-xs font-semibold" style="{{ $statusStyles[$c->status] ?? '' }}">{{ $c->status_label }}</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.admin-super>
