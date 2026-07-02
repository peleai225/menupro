<x-layouts.admin-super title="Client : {{ $customer->user->name ?? '—' }}">
    <div class="space-y-6 max-w-6xl">

        {{-- Breadcrumb --}}
        <div class="flex items-center gap-2 text-sm" style="color:var(--sa-muted-fg);">
            <a href="{{ route('super-admin.customers.index') }}"
               onmouseover="this.style.color='var(--sa-fg)'" onmouseout="this.style.color='var(--sa-muted-fg)'">Clients</a>
            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            <span class="font-medium" style="color:var(--sa-fg);">{{ $customer->user->name ?? '—' }}</span>
        </div>

        {{-- Header --}}
        <div class="rounded-2xl border shadow-sm p-6" style="background:var(--sa-card);border-color:var(--sa-border);">
            <div class="flex flex-wrap items-center justify-between gap-4">
                <div class="flex items-center gap-4">
                    <div class="w-16 h-16 rounded-2xl bg-gradient-to-br from-indigo-400 to-indigo-600 flex items-center justify-center text-white font-bold text-2xl flex-shrink-0">
                        {{ strtoupper(substr($customer->user->name ?? '?', 0, 1)) }}
                    </div>
                    <div>
                        <h1 class="text-xl font-bold" style="color:var(--sa-fg);">{{ $customer->user->name ?? '—' }}</h1>
                        <p class="text-sm" style="color:var(--sa-muted-fg);">{{ $customer->user->email ?? '—' }}</p>
                        <div class="mt-1">
                            @if($customer->is_active)
                                <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium bg-emerald-100 text-emerald-700">
                                    <span class="w-1.5 h-1.5 bg-emerald-500 rounded-full"></span> Actif
                                </span>
                            @else
                                <span class="px-2 py-1 rounded-full text-xs font-medium" style="background:var(--sa-muted);color:var(--sa-muted-fg);">Inactif</span>
                            @endif
                        </div>
                    </div>
                </div>
                <a href="{{ route('super-admin.customers.index') }}"
                   class="inline-flex items-center gap-2 h-9 px-4 rounded-xl text-sm font-medium transition"
                   style="background:var(--sa-card);border:1px solid var(--sa-border);color:var(--sa-muted-fg);"
                   onmouseover="this.style.background='var(--sa-muted)';this.style.color='var(--sa-fg)'" onmouseout="this.style.background='var(--sa-card)';this.style.color='var(--sa-muted-fg)'">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                    Retour aux clients
                </a>
            </div>
        </div>

        {{-- 4 stat cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="rounded-2xl p-4 border shadow-sm" style="background:var(--sa-card);border-color:var(--sa-border);">
                <p class="text-xs" style="color:var(--sa-muted-fg);">Total commandes</p>
                <p class="text-2xl font-bold mt-1" style="color:var(--sa-fg);">{{ $stats['total_orders'] }}</p>
            </div>
            <div class="rounded-2xl p-4 border shadow-sm" style="background:var(--sa-card);border-color:var(--sa-border);">
                <p class="text-xs" style="color:var(--sa-muted-fg);">Total dépensé</p>
                <p class="text-2xl font-bold text-emerald-600 mt-1">{{ number_format($stats['total_spent']) }} F</p>
            </div>
            <div class="rounded-2xl p-4 border shadow-sm" style="background:var(--sa-card);border-color:var(--sa-border);">
                <p class="text-xs" style="color:var(--sa-muted-fg);">Dernière commande</p>
                <p class="text-lg font-bold mt-1" style="color:var(--sa-fg);">
                    @if($stats['last_order_at'])
                        {{ \Carbon\Carbon::parse($stats['last_order_at'])->diffForHumans() }}
                    @else
                        <span class="text-base font-normal" style="color:var(--sa-muted-fg);">Aucune</span>
                    @endif
                </p>
            </div>
            <div class="rounded-2xl p-4 border shadow-sm" style="background:var(--sa-card);border-color:var(--sa-border);">
                <p class="text-xs" style="color:var(--sa-muted-fg);">Restaurant favori</p>
                <p class="text-base font-bold text-indigo-600 mt-1 truncate">{{ $stats['favourite_restaurant'] ?? '—' }}</p>
            </div>
        </div>

        {{-- 2 colonnes --}}
        <div class="grid grid-cols-1 lg:grid-cols-5 gap-6">

            {{-- Colonne gauche : historique commandes (60% ≈ 3/5) --}}
            <div class="lg:col-span-3">
                <div class="rounded-2xl border shadow-sm overflow-hidden" style="background:var(--sa-card);border-color:var(--sa-border);">
                    <div class="px-5 py-4 flex items-center justify-between border-b" style="border-color:var(--sa-border);">
                        <h3 class="font-semibold" style="color:var(--sa-fg);">Historique des commandes</h3>
                        <span class="text-xs" style="color:var(--sa-muted-fg);">20 dernières</span>
                    </div>

                    @if($customer->orders->count())
                        <div class="overflow-x-auto">
                            <table class="w-full text-sm">
                                <thead style="background:var(--sa-muted);border-bottom:1px solid var(--sa-border);">
                                    <tr>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase" style="color:var(--sa-muted-fg);">Réf.</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase" style="color:var(--sa-muted-fg);">Restaurant</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase" style="color:var(--sa-muted-fg);">Statut</th>
                                        <th class="px-4 py-3 text-right text-xs font-semibold uppercase" style="color:var(--sa-muted-fg);">Montant</th>
                                        <th class="px-4 py-3 text-left text-xs font-semibold uppercase" style="color:var(--sa-muted-fg);">Date</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($customer->orders as $order)
                                    @php
                                        $statusMap = [
                                            'draft'           => ['label' => 'Brouillon',   'class' => 'bg-neutral-100 text-neutral-600'],
                                            'pending_payment' => ['label' => 'En attente',  'class' => 'bg-amber-100 text-amber-700'],
                                            'paid'            => ['label' => 'Payée',        'class' => 'bg-blue-100 text-blue-700'],
                                            'confirmed'       => ['label' => 'Confirmée',   'class' => 'bg-primary-100 text-primary-700'],
                                            'preparing'       => ['label' => 'En prépa.',   'class' => 'bg-primary-100 text-primary-700'],
                                            'ready'           => ['label' => 'Prête',        'class' => 'bg-emerald-100 text-emerald-700'],
                                            'delivering'      => ['label' => 'En livraison','class' => 'bg-blue-100 text-blue-700'],
                                            'completed'       => ['label' => 'Terminée',    'class' => 'bg-emerald-100 text-emerald-700'],
                                            'cancelled'       => ['label' => 'Annulée',     'class' => 'bg-red-100 text-red-700'],
                                            'refunded'        => ['label' => 'Remboursée',  'class' => 'bg-neutral-100 text-neutral-600'],
                                        ];
                                        $statusValue = $order->status instanceof \App\Enums\OrderStatus
                                            ? $order->status->value
                                            : (string) $order->status;
                                        $s = $statusMap[$statusValue] ?? ['label' => $statusValue, 'class' => 'bg-neutral-100 text-neutral-600'];
                                    @endphp
                                    <tr class="border-b transition-colors"
                                        style="border-color:var(--sa-border);"
                                        onmouseover="this.style.background='var(--sa-muted)'" onmouseout="this.style.background='transparent'">
                                        <td class="px-4 py-3 font-mono text-xs" style="color:var(--sa-fg);">{{ $order->reference }}</td>
                                        <td class="px-4 py-3 text-xs" style="color:var(--sa-muted-fg);">{{ $order->restaurant?->name ?? '—' }}</td>
                                        <td class="px-4 py-3">
                                            <span class="px-2 py-0.5 rounded-full text-xs font-medium {{ $s['class'] }}">{{ $s['label'] }}</span>
                                        </td>
                                        <td class="px-4 py-3 text-right font-medium" style="color:var(--sa-fg);">{{ number_format($order->total) }} F</td>
                                        <td class="px-4 py-3 text-xs" style="color:var(--sa-muted-fg);">{{ $order->created_at->format('d/m/Y H:i') }}</td>
                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="px-5 py-14 flex flex-col items-center gap-3 text-center">
                            <svg class="w-10 h-10" fill="none" stroke="currentColor" viewBox="0 0 24 24" style="color:var(--sa-muted-fg);opacity:.4;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <p class="text-sm" style="color:var(--sa-muted-fg);">Aucune commande enregistrée.</p>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Colonne droite (40% ≈ 2/5) --}}
            <div class="lg:col-span-2 space-y-4">

                {{-- Informations --}}
                <div class="rounded-2xl border shadow-sm p-5" style="background:var(--sa-card);border-color:var(--sa-border);">
                    <h3 class="font-semibold mb-3" style="color:var(--sa-fg);">Informations</h3>
                    <div class="text-sm">
                        <div class="py-2 flex justify-between gap-2 border-b" style="border-color:var(--sa-border);">
                            <span style="color:var(--sa-muted-fg);">Téléphone</span>
                            <span class="font-medium" style="color:var(--sa-fg);">{{ $customer->phone ?? '—' }}</span>
                        </div>
                        <div class="py-2 flex justify-between gap-2 border-b" style="border-color:var(--sa-border);">
                            <span style="color:var(--sa-muted-fg);">Ville</span>
                            <span class="font-medium" style="color:var(--sa-fg);">{{ $customer->city ?? '—' }}</span>
                        </div>
                        <div class="py-2 flex justify-between gap-2 border-b" style="border-color:var(--sa-border);">
                            <span style="color:var(--sa-muted-fg);">Inscrit le</span>
                            <span class="font-medium" style="color:var(--sa-fg);">{{ $customer->created_at->format('d/m/Y') }}</span>
                        </div>
                        <div class="py-2 flex justify-between gap-2 border-b" style="border-color:var(--sa-border);">
                            <span style="color:var(--sa-muted-fg);">Adresses sauvegardées</span>
                            <span class="font-medium" style="color:var(--sa-fg);">{{ $customer->addresses->count() }}</span>
                        </div>
                        <div class="py-2 flex justify-between gap-2">
                            <span style="color:var(--sa-muted-fg);">Token push</span>
                            @if($customer->fcm_token)
                                <span class="px-1.5 py-0.5 text-xs rounded bg-emerald-100 text-emerald-700">Actif</span>
                            @else
                                <span class="text-xs" style="color:var(--sa-muted-fg);">Aucun</span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Adresses enregistrées --}}
                <div class="rounded-2xl border shadow-sm overflow-hidden" style="background:var(--sa-card);border-color:var(--sa-border);">
                    <div class="px-5 py-4 border-b" style="border-color:var(--sa-border);">
                        <h3 class="font-semibold" style="color:var(--sa-fg);">Adresses enregistrées</h3>
                    </div>
                    @if($customer->addresses->count())
                        <div class="flex flex-col">
                            @foreach($customer->addresses as $address)
                            <div class="px-5 py-3 border-b" style="border-color:var(--sa-border);">
                                <div class="flex items-center justify-between gap-2 mb-0.5">
                                    <span class="text-xs font-semibold uppercase tracking-wide" style="color:var(--sa-fg);">
                                        {{ $address->label ?? 'Adresse' }}
                                    </span>
                                    @if($address->is_default)
                                        <span class="px-1.5 py-0.5 text-xs rounded bg-indigo-100 text-indigo-700">Par défaut</span>
                                    @endif
                                </div>
                                <p class="text-sm" style="color:var(--sa-muted-fg);">{{ $address->address }}</p>
                                @if($address->city)
                                    <p class="text-xs mt-0.5" style="color:var(--sa-muted-fg);">{{ $address->city }}{{ $address->zone ? ' · ' . $address->zone : '' }}</p>
                                @endif
                                @if($address->instructions)
                                    <p class="text-xs mt-0.5 italic" style="color:var(--sa-muted-fg);">{{ $address->instructions }}</p>
                                @endif
                            </div>
                            @endforeach
                        </div>
                    @else
                        <div class="px-5 py-8 text-center text-sm" style="color:var(--sa-muted-fg);">Aucune adresse enregistrée.</div>
                    @endif
                </div>
            </div>
        </div>

    </div>
</x-layouts.admin-super>
