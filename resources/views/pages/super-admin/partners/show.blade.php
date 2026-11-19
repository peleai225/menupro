<x-layouts.admin-super :title="'Partenaire ' . $partner->name">
    @php
        $card = 'rounded-2xl border p-5 shadow-sm';
        $cardStyle = 'border-color:var(--sa-border);background:var(--sa-card);';
    @endphp

    <div class="mb-6">
        <a href="{{ route('super-admin.partners.index') }}" class="text-sm" style="color:var(--sa-muted-fg);">← Partenaires</a>
        <h1 class="mt-1 text-2xl font-bold" style="color:var(--sa-fg);">{{ $partner->name }}</h1>
    </div>

    @include('pages.super-admin.parcels._flash')

    @if(session('plain_api_key') || session('plain_webhook_secret'))
        <div class="mb-6 rounded-2xl border p-5" style="border-color:rgba(217,119,6,0.35);background:rgba(217,119,6,0.08);">
            <p class="font-semibold" style="color:var(--sa-warning);">À copier maintenant : ces valeurs ne seront plus jamais affichées.</p>
            @if(session('plain_api_key'))
                <p class="mt-2 text-sm" style="color:var(--sa-fg);">Clé d'API : <code class="select-all break-all font-mono">{{ session('plain_api_key') }}</code></p>
            @endif
            @if(session('plain_webhook_secret'))
                <p class="mt-2 text-sm" style="color:var(--sa-fg);">Secret de signature webhook : <code class="select-all break-all font-mono">{{ session('plain_webhook_secret') }}</code></p>
            @endif
        </div>
    @endif

    <div class="grid gap-6 lg:grid-cols-3">
        <form method="POST" action="{{ route('super-admin.partners.update', $partner) }}" class="{{ $card }} lg:col-span-2" style="{{ $cardStyle }}">
            @csrf @method('PUT')
            @include('pages.super-admin.partners._fields')
            <button class="mt-5 rounded-lg px-5 py-2 text-sm font-medium" style="background:var(--sa-primary);color:var(--sa-primary-fg);">Enregistrer</button>
        </form>

        <div class="space-y-4">
            <div class="{{ $card }} space-y-3" style="{{ $cardStyle }}">
                <h2 class="font-semibold" style="color:var(--sa-fg);">Accès API</h2>
                <p class="text-sm" style="color:var(--sa-muted-fg);">Clé actuelle : <span class="font-mono">{{ $partner->api_key_prefix ? $partner->api_key_prefix . '…' : 'aucune' }}</span>
                    @if($partner->api_key_generated_at)<br>générée le {{ $partner->api_key_generated_at->format('d/m/Y H:i') }}@endif</p>
                <form method="POST" action="{{ route('super-admin.partners.regenerate-key', $partner) }}" onsubmit="return confirm('L\'ancienne clé cessera immédiatement de fonctionner. Continuer ?')">
                    @csrf
                    <button class="w-full rounded-lg border px-4 py-2 text-sm font-medium" style="border-color:var(--sa-border);color:var(--sa-fg);">Régénérer la clé d'API</button>
                </form>
                <form method="POST" action="{{ route('super-admin.partners.regenerate-secret', $partner) }}" onsubmit="return confirm('Le partenaire devra mettre à jour son secret de vérification. Continuer ?')">
                    @csrf
                    <button class="w-full rounded-lg border px-4 py-2 text-sm font-medium" style="border-color:var(--sa-border);color:var(--sa-fg);">Régénérer le secret webhook</button>
                </form>
                <form method="POST" action="{{ route('super-admin.partners.test-webhook', $partner) }}">
                    @csrf
                    <button class="w-full rounded-lg px-4 py-2 text-sm font-medium" style="background:var(--sa-primary);color:var(--sa-primary-fg);">Tester le webhook (ping)</button>
                </form>
            </div>

            <div class="{{ $card }}" style="{{ $cardStyle }}">
                <h2 class="mb-3 font-semibold" style="color:var(--sa-fg);">Derniers colis</h2>
                @forelse($recentParcels as $p)
                    <a href="{{ route('super-admin.parcels.show', $p) }}" class="flex justify-between py-1 text-sm" style="color:var(--sa-fg);">
                        <span class="font-mono">{{ $p->reference }}</span><span style="color:var(--sa-muted-fg);">{{ $p->status->label() }}</span>
                    </a>
                @empty
                    <p class="text-sm" style="color:var(--sa-muted-fg);">Aucun colis.</p>
                @endforelse
            </div>
        </div>
    </div>

    <div class="mt-6 {{ $card }}" style="{{ $cardStyle }}">
        <h2 class="mb-3 font-semibold" style="color:var(--sa-fg);">Journal des webhooks (50 derniers envois)</h2>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[700px] text-sm">
                <thead>
                    <tr style="border-bottom:1px solid var(--sa-border);">
                        @foreach(['Date', 'Événement', 'Colis', 'Essai', 'Résultat', 'Détail'] as $h)
                            <th class="px-3 py-2 text-left text-xs font-semibold uppercase" style="color:var(--sa-muted-fg);">{{ $h }}</th>
                        @endforeach
                    </tr>
                </thead>
                <tbody>
                    @forelse($logs as $log)
                        <tr style="border-bottom:1px solid var(--sa-border);">
                            <td class="px-3 py-2 text-xs" style="color:var(--sa-muted-fg);">{{ $log->created_at->format('d/m H:i:s') }}</td>
                            <td class="px-3 py-2" style="color:var(--sa-fg);">{{ $log->event }}</td>
                            <td class="px-3 py-2 font-mono text-xs" style="color:var(--sa-fg);">{{ $log->parcel?->reference ?? '—' }}</td>
                            <td class="px-3 py-2" style="color:var(--sa-fg);">{{ $log->attempt }}</td>
                            <td class="px-3 py-2 font-medium" style="color:{{ $log->success ? 'var(--sa-success)' : 'var(--sa-danger)' }};">{{ $log->success ? 'OK' : 'Échec' }} {{ $log->status_code ? '(' . $log->status_code . ')' : '' }}</td>
                            <td class="px-3 py-2 text-xs" style="color:var(--sa-muted-fg);">{{ \Illuminate\Support\Str::limit($log->error ?: $log->response_excerpt, 120) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="6" class="px-3 py-6 text-center" style="color:var(--sa-muted-fg);">Aucun envoi.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-layouts.admin-super>
