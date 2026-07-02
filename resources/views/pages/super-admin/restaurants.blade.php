<x-layouts.admin-super title="Restaurants">

    {{-- Page Header --}}
    <div class="mb-6 flex flex-wrap items-end justify-between gap-4">
        <div>
            <h1 class="text-2xl font-bold" style="color:var(--sa-fg);">Restaurants</h1>
            <p class="mt-1 text-sm" style="color:var(--sa-muted-fg);">Gérez tous les restaurants de la plateforme.</p>
        </div>
        <div class="flex flex-wrap items-center gap-3">
            @if(isset($stats['pending_verification']) && $stats['pending_verification'] > 0)
                <a href="{{ route('super-admin.restaurants.index', array_merge(request()->only(['search', 'plan', 'status']), ['verification' => 'pending_verification'])) }}"
                   class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs font-medium whitespace-nowrap"
                   style="background:color-mix(in oklch,var(--sa-warning) 10%,transparent);color:var(--sa-warning);border-color:color-mix(in oklch,var(--sa-warning) 20%,transparent);">
                    <span class="size-1.5 rounded-full bg-current"></span>
                    {{ $stats['pending_verification'] }} RCCM à vérifier
                </a>
            @endif
            <a href="{{ route('super-admin.restaurants.export', request()->only(['search', 'status', 'plan'])) }}"
               class="inline-flex h-10 items-center justify-center gap-2 rounded-lg px-4 text-sm font-medium shadow-sm transition"
               style="background:var(--sa-success);color:#fff;">
                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Exporter Excel
            </a>
        </div>
    </div>

    {{-- StatCards --}}
    <div class="mb-6 grid grid-cols-2 gap-4 xl:grid-cols-4">

        {{-- Total --}}
        <div class="rounded-2xl border p-5 shadow-sm transition hover:shadow-md" style="border-color:var(--sa-border);background:var(--sa-card);">
            <span class="flex size-11 items-center justify-center rounded-xl" style="background:color-mix(in oklch,var(--sa-primary) 10%,transparent);color:var(--sa-primary);">
                <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9l9-7 9 7v11a2 2 0 01-2 2H5a2 2 0 01-2-2z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 22V12h6v10"/>
                </svg>
            </span>
            <p class="mt-4 text-3xl font-bold" style="color:var(--sa-fg);">{{ number_format($stats['total']) }}</p>
            <p class="mt-1 text-sm" style="color:var(--sa-muted-fg);">Total restaurants</p>
        </div>

        {{-- Actifs --}}
        <div class="rounded-2xl border p-5 shadow-sm transition hover:shadow-md" style="border-color:var(--sa-border);background:var(--sa-card);">
            <span class="flex size-11 items-center justify-center rounded-xl" style="background:color-mix(in oklch,var(--sa-success) 10%,transparent);color:var(--sa-success);">
                <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </span>
            <p class="mt-4 text-3xl font-bold" style="color:var(--sa-fg);">{{ number_format($stats['active']) }}</p>
            <p class="mt-1 text-sm" style="color:var(--sa-muted-fg);">Actifs</p>
        </div>

        {{-- Suspendus --}}
        <div class="rounded-2xl border p-5 shadow-sm transition hover:shadow-md" style="border-color:var(--sa-border);background:var(--sa-card);">
            <span class="flex size-11 items-center justify-center rounded-xl" style="background:color-mix(in oklch,var(--sa-danger) 10%,transparent);color:var(--sa-danger);">
                <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </span>
            <p class="mt-4 text-3xl font-bold" style="color:var(--sa-fg);">{{ number_format($stats['suspended']) }}</p>
            <p class="mt-1 text-sm" style="color:var(--sa-muted-fg);">Suspendus</p>
        </div>

        {{-- En attente --}}
        <div class="rounded-2xl border p-5 shadow-sm transition hover:shadow-md" style="border-color:var(--sa-border);background:var(--sa-card);">
            <span class="flex size-11 items-center justify-center rounded-xl" style="background:color-mix(in oklch,var(--sa-warning) 10%,transparent);color:var(--sa-warning);">
                <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </span>
            <p class="mt-4 text-3xl font-bold" style="color:var(--sa-fg);">{{ number_format($stats['pending']) }}</p>
            <p class="mt-1 text-sm" style="color:var(--sa-muted-fg);">En attente</p>
        </div>
    </div>

    {{-- Filters --}}
    @php
        $baseParams = array_filter(request()->only(['search', 'plan', 'verification']));
        $tabDefs = [
            ['key' => '',          'label' => 'Tous',       'count' => $stats['total']],
            ['key' => 'active',    'label' => 'Actifs',     'count' => $stats['active']],
            ['key' => 'suspended', 'label' => 'Suspendus',  'count' => $stats['suspended']],
            ['key' => 'pending',   'label' => 'En attente', 'count' => $stats['pending']],
        ];
    @endphp

    <div class="mb-4 flex flex-wrap items-center justify-between gap-3">

        {{-- Status Tabs (server-side navigation) --}}
        <div class="flex flex-wrap gap-1 rounded-xl border p-1" style="border-color:var(--sa-border);background:var(--sa-card);">
            @foreach($tabDefs as $tab)
                @php
                    $isActive = $tab['key'] === ''
                        ? !request()->filled('status')
                        : request('status') === $tab['key'];
                    $tabParams = $tab['key']
                        ? array_merge($baseParams, ['status' => $tab['key']])
                        : $baseParams;
                @endphp
                <a href="{{ route('super-admin.restaurants.index', $tabParams) }}"
                   class="flex items-center gap-2 rounded-lg px-3.5 py-2 text-sm font-medium transition-colors"
                   style="{{ $isActive ? 'background:var(--sa-primary);color:var(--sa-primary-fg);' : 'color:var(--sa-muted-fg);' }}">
                    {{ $tab['label'] }}
                    <span class="rounded-full px-1.5 text-xs font-semibold"
                          style="{{ $isActive ? 'background:rgba(255,255,255,0.2);color:var(--sa-primary-fg);' : 'background:color-mix(in oklch,var(--sa-muted) 80%,transparent);color:var(--sa-muted-fg);' }}">
                        {{ number_format($tab['count']) }}
                    </span>
                </a>
            @endforeach
        </div>

        {{-- Search + additional filters --}}
        <form method="GET" action="{{ route('super-admin.restaurants.index') }}" class="flex flex-wrap items-center gap-2">
            @if(request()->filled('status'))
                <input type="hidden" name="status" value="{{ request('status') }}">
            @endif
            <input type="text"
                   name="search"
                   value="{{ request('search') }}"
                   placeholder="Rechercher un restaurant..."
                   class="h-10 w-full rounded-lg px-3 text-sm outline-none transition sm:w-56"
                   style="border:1px solid var(--sa-border);background:var(--sa-card);color:var(--sa-fg);"
                   onfocus="this.style.borderColor='var(--sa-primary)'" onblur="this.style.borderColor='var(--sa-border)'">
            <select name="plan"
                    class="h-10 rounded-lg px-3 text-sm outline-none"
                    style="border:1px solid var(--sa-border);background:var(--sa-card);color:var(--sa-fg);">
                <option value="">Tous les plans</option>
                @foreach($plans as $plan)
                    <option value="{{ $plan->id }}" {{ request('plan') == $plan->id ? 'selected' : '' }}>{{ $plan->name }}</option>
                @endforeach
            </select>
            <select name="verification"
                    class="h-10 rounded-lg px-3 text-sm outline-none"
                    style="border:1px solid var(--sa-border);background:var(--sa-card);color:var(--sa-fg);">
                <option value="">RCCM</option>
                <option value="verified" {{ request('verification') === 'verified' ? 'selected' : '' }}>Vérifié</option>
                <option value="pending_verification" {{ request('verification') === 'pending_verification' ? 'selected' : '' }}>À vérifier</option>
                <option value="no_rccm" {{ request('verification') === 'no_rccm' ? 'selected' : '' }}>Sans RCCM</option>
            </select>
            <button type="submit"
                    class="inline-flex h-10 items-center justify-center gap-2 rounded-lg px-4 text-sm font-medium shadow-sm transition"
                    style="background:var(--sa-primary);color:var(--sa-primary-fg);">
                Filtrer
            </button>
            @if(request()->anyFilled(['search', 'plan', 'verification']))
                <a href="{{ route('super-admin.restaurants.index', request()->filled('status') ? ['status' => request('status')] : []) }}"
                   class="inline-flex h-10 items-center justify-center rounded-lg border px-3 text-sm transition"
                   style="border-color:var(--sa-border);background:var(--sa-card);color:var(--sa-muted-fg);"
                   title="Réinitialiser les filtres">
                    ×
                </a>
            @endif
        </form>
    </div>

    {{-- Status Badge Map --}}
    @php
        $statusMap = [
            'active'    => ['label' => 'Actif',      'bg' => 'color-mix(in oklch,var(--sa-success) 10%,transparent)', 'color' => 'var(--sa-success)',  'border' => 'color-mix(in oklch,var(--sa-success) 20%,transparent)'],
            'pending'   => ['label' => 'En attente', 'bg' => 'color-mix(in oklch,var(--sa-warning) 10%,transparent)', 'color' => 'var(--sa-warning)',  'border' => 'color-mix(in oklch,var(--sa-warning) 20%,transparent)'],
            'suspended' => ['label' => 'Suspendu',   'bg' => 'color-mix(in oklch,var(--sa-danger) 10%,transparent)',  'color' => 'var(--sa-danger)',   'border' => 'color-mix(in oklch,var(--sa-danger) 20%,transparent)'],
            'expired'   => ['label' => 'Expiré',     'bg' => 'color-mix(in oklch,var(--sa-muted) 50%,transparent)',   'color' => 'var(--sa-muted-fg)', 'border' => 'var(--sa-border)'],
        ];
        $badgeFallback = ['label' => '—', 'bg' => 'color-mix(in oklch,var(--sa-muted) 50%,transparent)', 'color' => 'var(--sa-muted-fg)', 'border' => 'var(--sa-border)'];
    @endphp

    {{-- Table --}}
    <div class="overflow-x-auto rounded-2xl border shadow-sm" style="border-color:var(--sa-border);background:var(--sa-card);">
        <table class="w-full min-w-[720px] text-sm">
            <thead>
                <tr style="border-bottom:1px solid var(--sa-border);">
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--sa-muted-fg);">Restaurant</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--sa-muted-fg);">Propriétaire</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--sa-muted-fg);">Plan</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--sa-muted-fg);">Statut</th>
                    <th class="px-5 py-3.5 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--sa-muted-fg);">Créé le</th>
                    <th class="px-5 py-3.5 text-right text-xs font-semibold uppercase tracking-wide" style="color:var(--sa-muted-fg);">Actions</th>
                </tr>
            </thead>
            <tbody>
                @forelse($restaurants as $restaurant)
                    @php
                        $sv    = $restaurant->status->value;
                        $badge = $statusMap[$sv] ?? $badgeFallback;
                    @endphp
                    <tr class="transition-colors" style="border-bottom:1px solid var(--sa-border);"
                        onmouseover="this.style.background='color-mix(in oklch,var(--sa-muted) 50%,transparent)'"
                        onmouseout="this.style.background='transparent'">

                        {{-- Restaurant name + avatar --}}
                        <td class="px-5 py-4">
                            <div class="flex items-center gap-3">
                                @if($restaurant->logo_path)
                                    <img src="{{ Storage::url($restaurant->logo_path) }}"
                                         alt="{{ $restaurant->name }}"
                                         class="size-10 shrink-0 rounded-full object-cover"
                                         style="border:1px solid var(--sa-border);">
                                @else
                                    <span class="flex size-10 shrink-0 items-center justify-center rounded-full text-sm font-semibold"
                                          style="background:color-mix(in oklch,var(--sa-primary) 10%,transparent);color:var(--sa-primary);">
                                        {{ strtoupper(substr($restaurant->name, 0, 2)) }}
                                    </span>
                                @endif
                                <div>
                                    <div class="flex items-center gap-1.5">
                                        <span class="font-medium" style="color:var(--sa-fg);">{{ $restaurant->name }}</span>
                                        @if($restaurant->is_verified)
                                            <span title="RCCM vérifié" style="color:var(--sa-info);">
                                                <svg class="size-3.5" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                </svg>
                                            </span>
                                        @elseif($restaurant->has_pending_verification)
                                            <span title="RCCM à vérifier" style="color:var(--sa-warning);">
                                                <svg class="size-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                                </svg>
                                            </span>
                                        @endif
                                    </div>
                                    @if($restaurant->city)
                                        <p class="text-xs" style="color:var(--sa-muted-fg);">{{ $restaurant->city }}</p>
                                    @endif
                                </div>
                            </div>
                        </td>

                        {{-- Owner --}}
                        <td class="px-5 py-4">
                            <p class="text-sm font-medium" style="color:var(--sa-fg);">{{ $restaurant->owner?->name ?? '—' }}</p>
                            <p class="text-xs" style="color:var(--sa-muted-fg);">{{ $restaurant->owner?->email ?? '' }}</p>
                        </td>

                        {{-- Plan --}}
                        <td class="px-5 py-4">
                            <span class="rounded-md px-2 py-0.5 text-xs font-medium"
                                  style="background:color-mix(in oklch,var(--sa-muted) 80%,transparent);color:var(--sa-muted-fg);">
                                {{ $restaurant->currentPlan?->name ?? 'Aucun' }}
                            </span>
                        </td>

                        {{-- Status Badge --}}
                        <td class="px-5 py-4">
                            <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs font-medium whitespace-nowrap"
                                  style="background:{{ $badge['bg'] }};color:{{ $badge['color'] }};border-color:{{ $badge['border'] }};">
                                <span class="size-1.5 rounded-full bg-current"></span>
                                {{ $badge['label'] }}
                            </span>
                        </td>

                        {{-- Date --}}
                        <td class="px-5 py-4 text-sm" style="color:var(--sa-muted-fg);">
                            {{ $restaurant->created_at->format('d M Y') }}
                        </td>

                        {{-- Actions --}}
                        <td class="px-5 py-4 text-right">
                            <div class="flex items-center justify-end gap-4">
                                <a href="{{ route('super-admin.restaurants.show', $restaurant) }}"
                                   class="text-sm font-medium transition"
                                   style="color:var(--sa-primary);">
                                    Voir →
                                </a>
                                <form method="POST" action="{{ route('super-admin.restaurants.destroy', $restaurant) }}" class="inline"
                                      onsubmit="return confirm('Supprimer « {{ addslashes($restaurant->name) }} » ? Cette action est irréversible.');">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-sm font-medium transition"
                                            style="color:var(--sa-danger);">
                                        Supprimer
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="6" class="px-5 py-16 text-center">
                            <svg class="mx-auto mb-3 size-12" style="color:color-mix(in oklch,var(--sa-muted-fg) 40%,transparent);"
                                 fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/>
                            </svg>
                            <p class="text-sm" style="color:var(--sa-muted-fg);">Aucun restaurant trouvé</p>
                        </td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($restaurants->hasPages())
        <div class="mt-6">
            {{ $restaurants->withQueryString()->links() }}
        </div>
    @endif

</x-layouts.admin-super>
