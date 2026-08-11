<x-layouts.admin-super title="Reversements à traiter">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <div class="flex items-center gap-3 mb-1">
                <a href="{{ route('super-admin.finances.index') }}" class="transition-colors" style="color:var(--sa-muted-fg);">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                </a>
                <h1 class="text-2xl font-bold" style="color:var(--sa-fg);">Reversements à traiter</h1>
            </div>
            <p class="mt-1 ml-8" style="color:var(--sa-muted-fg);">Commandes Jeko/Wave payées (30 derniers jours) — reversements à vérifier et relancer.</p>
        </div>
        <a href="{{ route('super-admin.finances.failed-payouts') }}" class="h-10 px-4 flex items-center gap-2 rounded-xl border text-sm font-medium transition-colors" style="background:var(--sa-card);border-color:var(--sa-border);color:var(--sa-fg);">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
            </svg>
            Actualiser
        </a>
    </div>

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="mb-6 p-4 rounded-xl border" style="background:#f0fdf4;border-color:#86efac;color:#166534;">
            ✅ {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 p-4 rounded-xl border" style="background:#fef2f2;border-color:#fca5a5;color:#991b1b;">
            ❌ {{ session('error') }}
        </div>
    @endif

    {{-- Stats --}}
    <div class="grid grid-cols-3 gap-4 mb-8">
        <div class="rounded-xl p-5 border shadow-sm" style="background:var(--sa-card);border-color:var(--sa-border);">
            <p class="text-sm" style="color:var(--sa-muted-fg);">En attente / échoués (30j)</p>
            <p class="text-3xl font-bold mt-1" style="color:#dc2626;">{{ $stats['total_orders'] }}</p>
        </div>
        <div class="rounded-xl p-5 border shadow-sm" style="background:var(--sa-card);border-color:var(--sa-border);">
            <p class="text-sm" style="color:var(--sa-muted-fg);">Montant à reverser</p>
            <p class="text-3xl font-bold mt-1" style="color:#dc2626;">{{ number_format($stats['total_amount'], 0, ',', ' ') }} F</p>
        </div>
        <div class="rounded-xl p-5 border shadow-sm" style="background:var(--sa-card);border-color:var(--sa-border);">
            <p class="text-sm" style="color:var(--sa-muted-fg);">Reversés aujourd'hui</p>
            <p class="text-3xl font-bold mt-1" style="color:#16a34a;">{{ $stats['completed_today'] }}</p>
        </div>
    </div>

    {{-- Filtre par restaurant --}}
    <form method="GET" class="border rounded-xl p-4 mb-6 shadow-sm flex gap-3" style="background:var(--sa-card);border-color:var(--sa-border);">
        <input type="text" name="restaurant" value="{{ request('restaurant') }}"
               placeholder="Filtrer par restaurant..."
               class="flex-1 h-10 px-4 rounded-lg border text-sm" style="background:var(--sa-bg);border-color:var(--sa-border);color:var(--sa-fg);">
        <button type="submit" class="h-10 px-6 rounded-lg text-white text-sm font-medium" style="background:#6366f1;">Filtrer</button>
        @if(request('restaurant'))
            <a href="{{ route('super-admin.finances.failed-payouts') }}" class="h-10 px-4 flex items-center rounded-lg border text-sm" style="border-color:var(--sa-border);color:var(--sa-muted-fg);">Reset</a>
        @endif
    </form>

    {{-- Table des commandes --}}
    <div class="border rounded-xl shadow-sm overflow-hidden" style="background:var(--sa-card);border-color:var(--sa-border);">
        <div class="overflow-x-auto">
            <table class="min-w-full">
                <thead class="border-b" style="background:var(--sa-bg);border-color:var(--sa-border);">
                    <tr>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase" style="color:var(--sa-muted-fg);">Commande</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase" style="color:var(--sa-muted-fg);">Restaurant</th>
                        <th class="px-4 py-3 text-right text-xs font-bold uppercase" style="color:var(--sa-muted-fg);">Montant</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase" style="color:var(--sa-muted-fg);">Méthode</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase" style="color:var(--sa-muted-fg);">Payé le</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase" style="color:var(--sa-muted-fg);">Intégration</th>
                        <th class="px-4 py-3 text-left text-xs font-bold uppercase" style="color:var(--sa-muted-fg);">Statut reversement</th>
                        <th class="px-4 py-3 text-center text-xs font-bold uppercase" style="color:var(--sa-muted-fg);">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y" style="divide-color:var(--sa-border);">
                    @forelse($failedOrders as $order)
                        @php
                            $sub = $order->restaurant?->jekoSubMerchant;
                            $isIntegrated = $sub?->isIntegrated() ?? false;
                            $payoutStatus = $order->payout_status;
                            $isManual = $payoutStatus === 'manual';
                            $isDone = in_array($payoutStatus, ['completed', 'manual']);
                            $isFailed = $payoutStatus === 'failed';
                            $operator = $order->payment_metadata['jeko_operator'] ?? $order->payment_metadata['operator'] ?? '';
                        @endphp
                        <tr style="background:{{ $isManual ? '#f0fdf4' : ($isIntegrated ? 'var(--sa-card)' : '#fef9c3') }}">
                            <td class="px-4 py-3">
                                <span class="font-mono text-sm font-semibold" style="color:var(--sa-fg);">{{ $order->reference }}</span>
                            </td>
                            <td class="px-4 py-3">
                                <div>
                                    <p class="font-medium text-sm" style="color:var(--sa-fg);">{{ $order->restaurant?->name }}</p>
                                    @if($sub?->mobile_money)
                                        <p class="text-xs" style="color:var(--sa-muted-fg);">{{ $sub->mobile_money }}</p>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-3 text-right">
                                <span class="font-bold tabular-nums" style="color:var(--sa-fg);">{{ number_format($order->total, 0, ',', ' ') }} F</span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-sm" style="color:var(--sa-fg);">
                                    {{ strtoupper($order->payment_method) }}
                                    @if($operator) <span style="color:var(--sa-muted-fg);">({{ $operator }})</span> @endif
                                </span>
                            </td>
                            <td class="px-4 py-3">
                                <span class="text-sm" style="color:var(--sa-muted-fg);">{{ $order->paid_at->format('d/m/Y H:i') }}</span>
                            </td>
                            <td class="px-4 py-3">
                                @if($isIntegrated)
                                    <span class="inline-flex px-2 py-1 rounded-lg text-xs font-semibold" style="background:#dcfce7;color:#166534;">✅ Intégré</span>
                                @else
                                    <span class="inline-flex px-2 py-1 rounded-lg text-xs font-semibold" style="background:#fef9c3;color:#92400e;">⚠️ Non intégré</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if($payoutStatus === 'completed')
                                    <span class="inline-flex px-2 py-1 rounded-lg text-xs font-semibold" style="background:#dcfce7;color:#166534;">
                                        ✅ Reversé {{ $order->payout_at?->format('d/m H:i') }}
                                    </span>
                                @elseif($payoutStatus === 'manual')
                                    <span class="inline-flex px-2 py-1 rounded-lg text-xs font-semibold" style="background:#dcfce7;color:#166534;">
                                        ✅ Manuel {{ $order->payout_at?->format('d/m H:i') }}
                                    </span>
                                @elseif($payoutStatus === 'failed')
                                    <span class="inline-flex px-2 py-1 rounded-lg text-xs font-semibold" style="background:#fee2e2;color:#991b1b;">❌ Échoué</span>
                                @elseif($payoutStatus === 'pending')
                                    <span class="inline-flex px-2 py-1 rounded-lg text-xs font-semibold" style="background:#fef9c3;color:#92400e;">⏳ En cours...</span>
                                @else
                                    <span class="inline-flex px-2 py-1 rounded-lg text-xs font-semibold" style="background:#fee2e2;color:#991b1b;">❌ À reverser</span>
                                @endif
                            </td>
                            <td class="px-4 py-3">
                                @if(!$isDone)
                                <div class="flex items-center justify-center gap-2">
                                    {{-- Relancer via API Jeko --}}
                                    @if($isIntegrated)
                                    <form method="POST" action="{{ route('super-admin.finances.retry-payout', $order) }}" onsubmit="return confirm('Relancer le reversement Jeko pour #{{ $order->reference }} ?')">
                                        @csrf
                                        <button type="submit" class="h-8 px-3 rounded-lg text-xs font-medium text-white transition-colors" style="background:#6366f1;" title="Relancer via Jeko API">
                                            🔄 Relancer
                                        </button>
                                    </form>
                                    @endif

                                    {{-- Marquer comme fait manuellement --}}
                                    <form method="POST" action="{{ route('super-admin.finances.mark-manual', $order) }}"
                                          onsubmit="var n=prompt('Note (optionnel) :','Virement manuel Wave');if(n===null)return false;this.querySelector('[name=note]').value=n;return true;">
                                        @csrf
                                        <input type="hidden" name="note" value="">
                                        <button type="submit" class="h-8 px-3 rounded-lg text-xs font-medium transition-colors" style="background:#f0fdf4;border:1px solid #86efac;color:#166534;" title="Marquer comme reversé manuellement">
                                            ✓ Manuel
                                        </button>
                                    </form>
                                </div>
                                @else
                                    <span class="text-xs" style="color:var(--sa-muted-fg);">—</span>
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-4 py-12 text-center" style="color:var(--sa-muted-fg);">
                                ✅ Aucune commande Jeko/Wave en attente de reversement sur les 30 derniers jours.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Pagination --}}
        @if($failedOrders->hasPages())
            <div class="px-6 py-4 border-t" style="border-color:var(--sa-border);">
                {{ $failedOrders->links() }}
            </div>
        @endif
    </div>

    {{-- Relancer TOUS pour un restaurant --}}
    <div class="mt-8 border rounded-xl p-6 shadow-sm" style="background:var(--sa-card);border-color:var(--sa-border);">
        <h2 class="text-base font-bold mb-4" style="color:var(--sa-fg);">🔄 Relancer tous les reversements d'un restaurant</h2>
        <form method="POST" action="{{ route('super-admin.finances.retry-all') }}"
              onsubmit="return confirm('Relancer TOUS les reversements des 30 derniers jours pour ce restaurant ?')">
            @csrf
            <div class="flex gap-3">
                <select name="restaurant_id" required class="flex-1 h-10 px-4 rounded-lg border text-sm" style="background:var(--sa-bg);border-color:var(--sa-border);color:var(--sa-fg);">
                    <option value="">-- Sélectionner un restaurant --</option>
                    @foreach($failedOrders->unique('restaurant_id') as $order)
                        <option value="{{ $order->restaurant_id }}">{{ $order->restaurant?->name }}</option>
                    @endforeach
                </select>
                <button type="submit" class="h-10 px-6 rounded-lg text-white text-sm font-medium" style="background:#dc2626;">
                    Relancer tous
                </button>
            </div>
        </form>
    </div>
</x-layouts.admin-super>
