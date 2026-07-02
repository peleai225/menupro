<x-layouts.admin-super title="Commandes">

    {{-- Page Header --}}
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold" style="color:var(--sa-fg);">Commandes</h1>
            <p class="mt-1 text-sm" style="color:var(--sa-muted-fg);">Historique et gestion de toutes les commandes de la plateforme.</p>
        </div>
        <div class="flex items-center gap-3">
            @if($stats['pending'] > 0)
                <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs font-medium whitespace-nowrap"
                      style="background:color-mix(in oklch,var(--sa-warning) 10%,transparent);color:var(--sa-warning);border-color:color-mix(in oklch,var(--sa-warning) 20%,transparent);">
                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                    {{ $stats['pending'] }} en cours
                </span>
            @endif
        </div>
    </div>

    {{-- StatCards --}}
    <div class="mb-6 grid grid-cols-2 gap-4 xl:grid-cols-4">

        {{-- Total commandes --}}
        <div class="rounded-2xl border p-5 shadow-sm transition hover:shadow-md" style="border-color:var(--sa-border);background:var(--sa-card);">
            <span class="flex w-11 h-11 items-center justify-center rounded-xl" style="background:color-mix(in oklch,var(--sa-primary) 10%,transparent);color:var(--sa-primary);">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
            </span>
            <p class="mt-4 text-3xl font-bold" style="color:var(--sa-fg);">{{ number_format($stats['total']) }}</p>
            <p class="mt-1 text-sm" style="color:var(--sa-muted-fg);">Total commandes</p>
        </div>

        {{-- Aujourd'hui --}}
        <div class="rounded-2xl border p-5 shadow-sm transition hover:shadow-md" style="border-color:var(--sa-border);background:var(--sa-card);">
            <span class="flex w-11 h-11 items-center justify-center rounded-xl" style="background:color-mix(in oklch,var(--sa-success) 10%,transparent);color:var(--sa-success);">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </span>
            <p class="mt-4 text-3xl font-bold" style="color:var(--sa-fg);">{{ number_format($stats['today']) }}</p>
            <p class="mt-1 text-sm" style="color:var(--sa-muted-fg);">Aujourd'hui</p>
        </div>

        {{-- En cours --}}
        <div class="rounded-2xl border p-5 shadow-sm transition hover:shadow-md" style="border-color:var(--sa-border);background:var(--sa-card);">
            <span class="flex w-11 h-11 items-center justify-center rounded-xl" style="background:color-mix(in oklch,var(--sa-info) 10%,transparent);color:var(--sa-info);">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17a2 2 0 11-4 0 2 2 0 014 0zM19 17a2 2 0 11-4 0 2 2 0 014 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10l1 1h2m10-11h2l3 4v4h-5V5z"/>
                </svg>
            </span>
            <p class="mt-4 text-3xl font-bold" style="color:var(--sa-fg);">{{ number_format($stats['pending']) }}</p>
            <p class="mt-1 text-sm" style="color:var(--sa-muted-fg);">En cours</p>
        </div>

        {{-- Revenus du mois --}}
        <div class="rounded-2xl border p-5 shadow-sm transition hover:shadow-md" style="border-color:var(--sa-border);background:var(--sa-card);">
            <span class="flex w-11 h-11 items-center justify-center rounded-xl" style="background:color-mix(in oklch,var(--sa-warning) 10%,transparent);color:var(--sa-warning);">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </span>
            <p class="mt-4 text-3xl font-bold" style="color:var(--sa-fg);">{{ number_format($stats['revenue_month'], 0, ',', ' ') }}</p>
            <p class="mt-1 text-sm" style="color:var(--sa-muted-fg);">Revenus ce mois (FCFA)</p>
        </div>
    </div>

    {{-- Filters --}}
    {{-- Status Badge Map (for table rows) --}}
    @php
        $orderStatusMap = [
            'draft'           => ['label' => 'Brouillon',      'bg' => 'color-mix(in oklch,var(--sa-muted) 50%,transparent)',   'color' => 'var(--sa-muted-fg)', 'border' => 'var(--sa-border)'],
            'pending_payment' => ['label' => 'Paiement att.',  'bg' => 'color-mix(in oklch,var(--sa-warning) 10%,transparent)', 'color' => 'var(--sa-warning)',  'border' => 'color-mix(in oklch,var(--sa-warning) 20%,transparent)'],
            'paid'            => ['label' => 'Payée',          'bg' => 'color-mix(in oklch,var(--sa-info) 10%,transparent)',    'color' => 'var(--sa-info)',     'border' => 'color-mix(in oklch,var(--sa-info) 20%,transparent)'],
            'confirmed'       => ['label' => 'Confirmée',      'bg' => 'color-mix(in oklch,var(--sa-info) 10%,transparent)',    'color' => 'var(--sa-info)',     'border' => 'color-mix(in oklch,var(--sa-info) 20%,transparent)'],
            'preparing'       => ['label' => 'En préparation', 'bg' => 'color-mix(in oklch,var(--sa-warning) 10%,transparent)', 'color' => 'var(--sa-warning)',  'border' => 'color-mix(in oklch,var(--sa-warning) 20%,transparent)'],
            'ready'           => ['label' => 'Prête',          'bg' => 'color-mix(in oklch,var(--sa-info) 10%,transparent)',    'color' => 'var(--sa-info)',     'border' => 'color-mix(in oklch,var(--sa-info) 20%,transparent)'],
            'delivering'      => ['label' => 'En livraison',   'bg' => 'color-mix(in oklch,var(--sa-info) 10%,transparent)',    'color' => 'var(--sa-info)',     'border' => 'color-mix(in oklch,var(--sa-info) 20%,transparent)'],
            'completed'       => ['label' => 'Livrée',         'bg' => 'color-mix(in oklch,var(--sa-success) 10%,transparent)', 'color' => 'var(--sa-success)', 'border' => 'color-mix(in oklch,var(--sa-success) 20%,transparent)'],
            'cancelled'       => ['label' => 'Annulée',        'bg' => 'color-mix(in oklch,var(--sa-danger) 10%,transparent)',  'color' => 'var(--sa-danger)',  'border' => 'color-mix(in oklch,var(--sa-danger) 20%,transparent)'],
            'refunded'        => ['label' => 'Remboursée',     'bg' => 'color-mix(in oklch,var(--sa-muted) 50%,transparent)',   'color' => 'var(--sa-muted-fg)', 'border' => 'var(--sa-border)'],
        ];
        $orderBadgeFallback = ['label' => '—', 'bg' => 'color-mix(in oklch,var(--sa-muted) 50%,transparent)', 'color' => 'var(--sa-muted-fg)', 'border' => 'var(--sa-border)'];

        // "En cours" statuses for tab highlight
        $enCoursStatuses = ['paid','confirmed','preparing','ready','delivering'];
        $currentStatus = request('status', '');
        $isEnCoursTab = in_array($currentStatus, $enCoursStatuses) || ($currentStatus === '' && false);
    @endphp

    <form method="GET" action="{{ route('super-admin.orders.index') }}" class="mb-4">
        <div class="flex flex-wrap items-center gap-3">

            {{-- Search --}}
            <div class="relative">
                <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4" style="color:var(--sa-muted-fg);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                <input type="text"
                       name="search"
                       value="{{ request('search') }}"
                       placeholder="Référence, client, téléphone..."
                       class="h-10 rounded-lg pl-9 pr-3 text-sm outline-none transition sm:w-64"
                       style="border:1px solid var(--sa-border);background:var(--sa-card);color:var(--sa-fg);"
                       onfocus="this.style.borderColor='var(--sa-primary)'" onblur="this.style.borderColor='var(--sa-border)'">
            </div>

            {{-- Status select --}}
            <select name="status"
                    class="h-10 rounded-lg px-3 text-sm outline-none"
                    style="border:1px solid var(--sa-border);background:var(--sa-card);color:var(--sa-fg);">
                <option value="">Tous les statuts</option>
                @foreach($statuses as $status)
                    <option value="{{ $status->value }}" {{ request('status') === $status->value ? 'selected' : '' }}>
                        {{ $status->label() }}
                    </option>
                @endforeach
            </select>

            {{-- Restaurant select --}}
            <select name="restaurant_id"
                    class="h-10 rounded-lg px-3 text-sm outline-none"
                    style="border:1px solid var(--sa-border);background:var(--sa-card);color:var(--sa-fg);">
                <option value="">Tous les restaurants</option>
                @foreach($restaurants as $restaurant)
                    <option value="{{ $restaurant->id }}" {{ request('restaurant_id') == $restaurant->id ? 'selected' : '' }}>
                        {{ $restaurant->name }}
                    </option>
                @endforeach
            </select>

            {{-- Date range --}}
            <input type="date"
                   name="date_from"
                   value="{{ request('date_from') }}"
                   class="h-10 rounded-lg px-3 text-sm outline-none"
                   style="border:1px solid var(--sa-border);background:var(--sa-card);color:var(--sa-fg);">
            <input type="date"
                   name="date_to"
                   value="{{ request('date_to') }}"
                   class="h-10 rounded-lg px-3 text-sm outline-none"
                   style="border:1px solid var(--sa-border);background:var(--sa-card);color:var(--sa-fg);">

            <button type="submit"
                    class="inline-flex h-10 items-center justify-center gap-2 rounded-lg px-4 text-sm font-medium shadow-sm transition"
                    style="background:var(--sa-primary);color:var(--sa-primary-fg);">
                Filtrer
            </button>

            @if(request()->anyFilled(['search', 'status', 'restaurant_id', 'date_from', 'date_to']))
                <a href="{{ route('super-admin.orders.index') }}"
                   class="inline-flex h-10 items-center justify-center rounded-lg border px-3 text-sm transition"
                   style="border-color:var(--sa-border);background:var(--sa-card);color:var(--sa-muted-fg);">
                    Réinitialiser
                </a>
            @endif
        </div>
    </form>

    {{-- Table --}}
    <div class="overflow-x-auto rounded-2xl border shadow-sm" style="border-color:var(--sa-border);background:var(--sa-card);">
        <table class="w-full min-w-[900px] text-sm">
            <thead>
                <tr style="border-bottom:1px solid var(--sa-border);">
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--sa-muted-fg);">Référence</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--sa-muted-fg);">Restaurant</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--sa-muted-fg);">Client</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--sa-muted-fg);">Livreur</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--sa-muted-fg);">Total</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--sa-muted-fg);">Date</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--sa-muted-fg);">Statut</th>
                    <th class="px-5 py-3.5 text-right text-xs font-semibold uppercase tracking-wide" style="color:var(--sa-muted-fg);">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($orders as $order)
                    @php
                        $sv    = $order->status->value;
                        $badge = $orderStatusMap[$sv] ?? $orderBadgeFallback;
                    @endphp
                    <tr class="transition-colors" style="border-bottom:1px solid var(--sa-border);"
                        onmouseover="this.style.background='color-mix(in oklch,var(--sa-muted) 50%,transparent)'"
                        onmouseout="this.style.background='transparent'">

                        {{-- Référence --}}
                        <td class="px-5 py-4">
                            <span class="font-mono font-medium" style="color:var(--sa-primary);">{{ $order->reference }}</span>
                        </td>

                        {{-- Restaurant --}}
                        <td class="px-5 py-4 font-medium" style="color:var(--sa-fg);">
                            {{ $order->restaurant?->name ?? '—' }}
                        </td>

                        {{-- Client --}}
                        <td class="px-5 py-4">
                            <p class="font-medium" style="color:var(--sa-fg);">{{ $order->customer_name ?? '—' }}</p>
                            @if($order->customer_phone)
                                <p class="text-xs" style="color:var(--sa-muted-fg);">{{ $order->customer_phone }}</p>
                            @endif
                        </td>

                        {{-- Livreur --}}
                        <td class="px-5 py-4 text-sm" style="color:var(--sa-muted-fg);">
                            {{ $order->delivery?->driver?->name ?? '—' }}
                        </td>

                        {{-- Total --}}
                        <td class="px-5 py-4 font-semibold" style="color:var(--sa-fg);">
                            {{ number_format($order->total, 0, ',', ' ') }} FCFA
                        </td>

                        {{-- Date --}}
                        <td class="px-5 py-4" style="color:var(--sa-muted-fg);">
                            <span>{{ $order->created_at->format('d M Y') }}</span>
                            <span class="block text-xs">{{ $order->created_at->format('H:i') }}</span>
                        </td>

                        {{-- Status Badge --}}
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs font-medium whitespace-nowrap"
                                  style="background:{{ $badge['bg'] }};color:{{ $badge['color'] }};border-color:{{ $badge['border'] }};">
                                <span class="w-1.5 h-1.5 rounded-full bg-current"></span>
                                {{ $badge['label'] }}
                            </span>
                        </td>

                        {{-- Actions --}}
                        <td class="px-5 py-4 text-right">
                            <a href="{{ route('super-admin.orders.show', $order->id) }}"
                               class="text-sm font-medium transition"
                               style="color:var(--sa-primary);">
                                Voir →
                            </a>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="8" class="px-5 py-16 text-center">
                            <svg class="mx-auto mb-3 w-12 h-12" style="color:color-mix(in oklch,var(--sa-muted-fg) 40%,transparent);"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                            <p class="text-sm" style="color:var(--sa-muted-fg);">Aucune commande trouvée</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($orders->hasPages())
        <div class="mt-6">
            {{ $orders->withQueryString()->links() }}
        </div>
    @endif

</x-layouts.admin-super>
