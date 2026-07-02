<x-layouts.admin-super title="Dashboard">

    {{-- ── Page Intro ─────────────────────────────────────────────────────── --}}
    <div class="flex flex-wrap items-start justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold" style="color:var(--sa-fg);">Dashboard</h1>
            <p class="mt-1 text-sm" style="color:var(--sa-muted-fg);">Vue d'ensemble de la plateforme MenuPro</p>
        </div>
        <div class="flex items-center gap-2" x-data="liveDashboard()" x-init="startPolling()">
            <span class="inline-flex items-center gap-2 rounded-full border px-3 py-1.5 text-sm font-medium"
                  style="border-color:rgba(61,158,98,0.20);background:rgba(61,158,98,0.10);color:var(--sa-success);">
                <span class="w-2 h-2 rounded-full animate-pulse" style="background:var(--sa-success);"></span>
                Live
                <span class="text-[10px] opacity-70" x-text="lastUpdate"></span>
            </span>
            <button @click="toggleLive()"
                    :style="isLive ? 'background:var(--sa-success);color:#fff;' : 'background:var(--sa-muted);color:var(--sa-muted-fg);'"
                    class="flex w-9 h-9 items-center justify-center rounded-lg transition"
                    aria-label="Rafraîchir">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
            </button>
        </div>
    </div>

    {{-- ── KPI Cards ────────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">

        {{-- Restaurants actifs --}}
        <div class="rounded-2xl border p-5 shadow-sm transition hover:shadow-md"
             style="border-color:var(--sa-border);background:var(--sa-card);">
            <div class="flex items-start justify-between">
                <span class="flex w-11 h-11 items-center justify-center rounded-xl"
                      style="background:rgba(194,98,31,0.10);color:var(--sa-primary);">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/>
                    </svg>
                </span>
                <span class="rounded-full px-2 py-0.5 text-xs font-semibold"
                      style="background:rgba(61,158,98,0.10);color:var(--sa-success);">
                    +{{ $stats['restaurants']['total'] }}
                </span>
            </div>
            <p class="mt-4 text-3xl font-bold" style="color:var(--sa-fg);">{{ number_format($stats['restaurants']['active']) }}</p>
            <p class="mt-1 text-sm" style="color:var(--sa-muted-fg);">Restaurants actifs</p>
        </div>

        {{-- Revenus mensuels --}}
        <div class="rounded-2xl border p-5 shadow-sm transition hover:shadow-md"
             style="border-color:var(--sa-border);background:var(--sa-card);">
            <div class="flex items-start justify-between">
                <span class="flex w-11 h-11 items-center justify-center rounded-xl"
                      style="background:rgba(61,158,98,0.10);color:var(--sa-success);">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
                <span class="rounded-full px-2 py-0.5 text-xs font-semibold flex items-center gap-1"
                      style="background:rgba(61,158,98,0.10);color:var(--sa-success);">
                    <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                    MRR
                </span>
            </div>
            <p class="mt-4 text-3xl font-bold" style="color:var(--sa-fg);">
                @if($stats['revenue']['this_month'] >= 1000000)
                    {{ number_format($stats['revenue']['this_month'] / 1000000, 1, ',', ' ') }}M
                @else
                    {{ number_format($stats['revenue']['this_month'] / 1000, 0, ',', ' ') }}K
                @endif
                <span class="text-sm font-normal" style="color:var(--sa-muted-fg);">FCFA</span>
            </p>
            <p class="mt-1 text-sm" style="color:var(--sa-muted-fg);">Revenus ce mois</p>
        </div>

        {{-- Commandes --}}
        <div class="rounded-2xl border p-5 shadow-sm transition hover:shadow-md"
             style="border-color:var(--sa-border);background:var(--sa-card);">
            <div class="flex items-start justify-between">
                <span class="flex w-11 h-11 items-center justify-center rounded-xl"
                      style="background:rgba(59,111,212,0.10);color:var(--sa-info);">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </span>
                <span class="rounded-full px-2 py-0.5 text-xs font-semibold"
                      style="background:rgba(59,111,212,0.10);color:var(--sa-info);">
                    +{{ number_format($stats['orders']['this_month']) }}
                </span>
            </div>
            <p class="mt-4 text-3xl font-bold" style="color:var(--sa-fg);">
                @if($stats['orders']['total'] >= 1000)
                    {{ number_format($stats['orders']['total'] / 1000, 1, ',', ' ') }}K
                @else
                    {{ number_format($stats['orders']['total']) }}
                @endif
            </p>
            <p class="mt-1 text-sm" style="color:var(--sa-muted-fg);">Commandes totales</p>
        </div>

        {{-- En attente --}}
        <div class="rounded-2xl border p-5 shadow-sm transition hover:shadow-md"
             style="border-color:{{ $stats['restaurants']['pending'] > 0 ? 'rgba(217,119,6,0.30)' : 'var(--sa-border)' }};background:{{ $stats['restaurants']['pending'] > 0 ? 'rgba(217,119,6,0.05)' : 'var(--sa-card)' }};">
            <div class="flex items-start justify-between">
                <span class="flex w-11 h-11 items-center justify-center rounded-xl"
                      style="background:rgba(217,119,6,0.10);color:var(--sa-warning);">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
                @if($stats['restaurants']['pending'] > 0)
                    <span class="w-2 h-2 rounded-full animate-pulse" style="background:var(--sa-warning);margin-top:0.35rem;"></span>
                @else
                    <span class="rounded-full px-2 py-0.5 text-xs font-semibold"
                          style="background:var(--sa-muted);color:var(--sa-muted-fg);">OK</span>
                @endif
            </div>
            <p class="mt-4 text-3xl font-bold" style="color:var(--sa-fg);">{{ $stats['restaurants']['pending'] }}</p>
            <p class="mt-1 text-sm font-{{ $stats['restaurants']['pending'] > 0 ? 'semibold' : 'normal' }}"
               style="color:{{ $stats['restaurants']['pending'] > 0 ? 'var(--sa-warning)' : 'var(--sa-muted-fg)' }};">
                {{ $stats['restaurants']['pending'] > 0 ? 'En attente de validation' : 'Aucun en attente' }}
            </p>
        </div>
    </div>

    {{-- ── Charts Section ───────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-5 gap-6 mb-6">

        {{-- Line Chart: Commandes & Revenus (7 jours) — 60% --}}
        <div class="lg:col-span-3 rounded-2xl border p-5 shadow-sm"
             style="border-color:var(--sa-border);background:var(--sa-card);">
            <div class="mb-5 flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold" style="color:var(--sa-fg);">Commandes &amp; Revenus</h2>
                    <p class="text-sm" style="color:var(--sa-muted-fg);">7 derniers jours</p>
                </div>
            </div>
            <div class="relative" style="height:220px;">
                <canvas id="ordersChart"></canvas>
            </div>
        </div>

        {{-- Donut Chart: Commandes par statut — 40% --}}
        <div class="lg:col-span-2 rounded-2xl border p-5 shadow-sm"
             style="border-color:var(--sa-border);background:var(--sa-card);">
            <div class="mb-5 flex items-start justify-between gap-4">
                <div>
                    <h2 class="text-lg font-semibold" style="color:var(--sa-fg);">Commandes par statut</h2>
                    <p class="text-sm" style="color:var(--sa-muted-fg);">Toutes les commandes</p>
                </div>
            </div>
            <div class="relative" style="height:220px;">
                <canvas id="statusChart"></canvas>
            </div>
        </div>
    </div>

    {{-- ── Main Grid ────────────────────────────────────────────────────────── --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">

        {{-- Left: Recent + Pending Restaurants --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Restaurants récents --}}
            <div class="rounded-2xl border shadow-sm overflow-hidden"
                 style="border-color:var(--sa-border);background:var(--sa-card);">
                <div class="px-5 py-4 flex items-start justify-between gap-4"
                     style="border-bottom:1px solid var(--sa-border);">
                    <div>
                        <h2 class="text-lg font-semibold" style="color:var(--sa-fg);">Restaurants récents</h2>
                        <p class="text-sm" style="color:var(--sa-muted-fg);">Derniers ajouts sur la plateforme</p>
                    </div>
                    <a href="{{ route('super-admin.restaurants.index') }}"
                       class="inline-flex items-center gap-1 text-sm font-medium shrink-0 mt-0.5"
                       style="color:var(--sa-primary);">
                        Voir tout
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>

                <ul class="flex flex-col divide-y" style="--tw-divide-opacity:1;border-color:var(--sa-border);">
                    @forelse($recentRestaurants as $restaurant)
                        @php
                            $statusStyles = [
                                'active'    => ['bg' => 'rgba(61,158,98,0.10)', 'fg' => 'var(--sa-success)'],
                                'pending'   => ['bg' => 'rgba(217,119,6,0.10)', 'fg' => 'var(--sa-warning)'],
                                'suspended' => ['bg' => 'rgba(220,38,38,0.10)',  'fg' => 'var(--sa-danger)'],
                                'expired'   => ['bg' => 'var(--sa-muted)', 'fg' => 'var(--sa-muted-fg)'],
                            ];
                            $statusLabels = [
                                'active'    => 'Actif',
                                'pending'   => 'En attente',
                                'suspended' => 'Suspendu',
                                'expired'   => 'Expiré',
                            ];
                            $sv = $restaurant->status->value;
                            $sBg = $statusStyles[$sv]['bg'] ?? 'var(--sa-muted)';
                            $sFg = $statusStyles[$sv]['fg'] ?? 'var(--sa-muted-fg)';
                            $sLabel = $statusLabels[$sv] ?? $sv;
                        @endphp
                        <li>
                            <a href="{{ route('super-admin.restaurants.show', $restaurant) }}"
                               class="flex flex-wrap items-center justify-between gap-3 px-5 py-3.5 transition"
                               onmouseover="this.style.background='var(--sa-muted)'"
                               onmouseout="this.style.background='transparent'">
                                <div class="flex items-center gap-3 min-w-0">
                                    @if($restaurant->logo_path)
                                        <img src="{{ Storage::url($restaurant->logo_path) }}"
                                             alt="{{ $restaurant->name }}"
                                             class="w-10 h-10 rounded-full object-cover flex-shrink-0"
                                             style="border:1px solid var(--sa-border);">
                                    @else
                                        <span class="flex w-10 h-10 items-center justify-center rounded-full text-sm font-bold flex-shrink-0"
                                              style="background:rgba(194,98,31,0.10);color:var(--sa-primary);">
                                            {{ strtoupper(substr($restaurant->name, 0, 2)) }}
                                        </span>
                                    @endif
                                    <div class="min-w-0">
                                        <p class="truncate font-medium" style="color:var(--sa-fg);">{{ $restaurant->name }}</p>
                                        <p class="truncate text-sm" style="color:var(--sa-muted-fg);">
                                            {{ $restaurant->owner?->name ?? 'N/A' }} · {{ $restaurant->created_at->format('d M') }}
                                        </p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="rounded-md px-2 py-0.5 text-xs font-medium"
                                          style="background:var(--sa-muted);color:var(--sa-muted-fg);">
                                        {{ $restaurant->currentPlan?->name ?? 'Aucun' }}
                                    </span>
                                    <span class="rounded-full px-2 py-0.5 text-xs font-semibold"
                                          style="background:{{ $sBg }};color:{{ $sFg }};">
                                        {{ $sLabel }}
                                    </span>
                                </div>
                            </a>
                        </li>
                    @empty
                        <li class="p-8 text-center">
                            <svg class="w-10 h-10 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                                 style="color:var(--sa-muted-fg);opacity:.4;">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/>
                            </svg>
                            <p class="text-sm" style="color:var(--sa-muted-fg);">Aucun restaurant récent</p>
                        </li>
                    @endforelse
                </ul>
            </div>

            {{-- Pending Restaurants --}}
            @if($pendingRestaurants->isNotEmpty())
                <div class="rounded-2xl border shadow-sm overflow-hidden"
                     style="border-color:rgba(217,119,6,0.30);background:var(--sa-card);">
                    <div class="px-5 py-4 flex items-center gap-2"
                         style="border-bottom:1px solid rgba(217,119,6,0.15);background:rgba(217,119,6,0.05);">
                        <span class="w-2 h-2 rounded-full animate-pulse" style="background:var(--sa-warning);"></span>
                        <h2 class="font-semibold text-sm" style="color:var(--sa-fg);">
                            {{ $pendingRestaurants->count() }} restaurant(s) en attente de validation
                        </h2>
                    </div>
                    <div class="flex flex-col divide-y" style="border-color:var(--sa-border);">
                        @foreach($pendingRestaurants as $restaurant)
                            <div class="flex flex-wrap items-center justify-between gap-3 px-5 py-3.5 transition"
                                 onmouseover="this.style.background='rgba(217,119,6,0.05)'"
                                 onmouseout="this.style.background='transparent'">
                                <div class="flex items-center gap-3 min-w-0">
                                    @if($restaurant->logo_path)
                                        <img src="{{ Storage::url($restaurant->logo_path) }}"
                                             alt="{{ $restaurant->name }}"
                                             class="w-9 h-9 rounded-full object-cover flex-shrink-0"
                                             style="border:1px solid rgba(217,119,6,0.30);">
                                    @else
                                        <span class="flex w-9 h-9 items-center justify-center rounded-full text-sm font-bold flex-shrink-0"
                                              style="background:rgba(217,119,6,0.10);color:var(--sa-warning);">
                                            {{ strtoupper(substr($restaurant->name, 0, 2)) }}
                                        </span>
                                    @endif
                                    <div class="min-w-0">
                                        <p class="text-sm font-medium truncate" style="color:var(--sa-fg);">{{ $restaurant->name }}</p>
                                        <p class="text-xs truncate" style="color:var(--sa-muted-fg);">{{ $restaurant->owner?->email ?? 'N/A' }}</p>
                                    </div>
                                </div>
                                <a href="{{ route('super-admin.restaurants.show', $restaurant) }}"
                                   class="px-3 py-1.5 rounded-lg text-xs font-semibold shadow-sm text-white transition"
                                   style="background:var(--sa-warning);"
                                   onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
                                    Examiner
                                </a>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- ── Right Column ─────────────────────────────────────────────────── --}}
        <div class="space-y-6">

            {{-- Revenue by Plan --}}
            <div class="rounded-2xl border p-5 shadow-sm"
                 style="border-color:var(--sa-border);background:var(--sa-card);">
                <div class="mb-5">
                    <h2 class="text-lg font-semibold" style="color:var(--sa-fg);">Revenus par plan</h2>
                    <p class="text-sm" style="color:var(--sa-muted-fg);">Ce mois-ci</p>
                </div>
                <div class="space-y-4">
                    @php $barColors = ['var(--sa-primary)', 'var(--sa-success)', 'var(--sa-info)']; @endphp
                    @forelse($revenueByPlan as $plan)
                        @php
                            $maxRevenue = $revenueByPlan->max('total') ?: 1;
                            $percent = ($plan->total / $maxRevenue) * 100;
                        @endphp
                        <div>
                            <div class="flex items-center justify-between mb-1.5">
                                <span class="text-xs font-medium" style="color:var(--sa-fg);">{{ $plan->name }}</span>
                                <span class="text-xs font-semibold" style="color:var(--sa-fg);">{{ number_format($plan->total, 0, ',', ' ') }} F</span>
                            </div>
                            <div class="h-1.5 rounded-full overflow-hidden" style="background:var(--sa-muted);">
                                <div class="h-full rounded-full transition-all duration-500"
                                     style="width:{{ $percent }}%;background:{{ $barColors[$loop->index % 3] }};"></div>
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-center py-4" style="color:var(--sa-muted-fg);">Aucune donnée ce mois</p>
                    @endforelse
                </div>
            </div>

            {{-- Expiring Subscriptions --}}
            @if($expiringSubscriptions->isNotEmpty())
                <div class="rounded-2xl border p-5 shadow-sm"
                     style="border-color:rgba(220,38,38,0.30);background:var(--sa-card);">
                    <div class="mb-5 flex items-start justify-between gap-4">
                        <div>
                            <h2 class="text-lg font-semibold" style="color:var(--sa-fg);">Expirent bientôt</h2>
                            <p class="text-sm" style="color:var(--sa-muted-fg);">Abonnements à renouveler</p>
                        </div>
                        <span class="flex w-9 h-9 items-center justify-center rounded-xl flex-shrink-0"
                              style="background:rgba(220,38,38,0.10);color:var(--sa-danger);">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                        </span>
                    </div>
                    <div class="space-y-2.5">
                        @foreach($expiringSubscriptions as $subscription)
                            <div class="flex items-center justify-between p-3 rounded-xl"
                                 style="background:rgba(220,38,38,0.05);border:1px solid rgba(220,38,38,0.15);">
                                <div class="min-w-0">
                                    <p class="text-xs font-medium truncate" style="color:var(--sa-fg);">{{ $subscription->restaurant->name }}</p>
                                    <p class="text-[10px]" style="color:var(--sa-muted-fg);">{{ $subscription->plan->name }}</p>
                                </div>
                                <span class="text-[10px] font-semibold whitespace-nowrap ml-2" style="color:var(--sa-danger);">
                                    {{ $subscription->ends_at->diffForHumans() }}
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Top Restaurants --}}
            @if($topRestaurants->isNotEmpty())
                <div class="rounded-2xl border p-5 shadow-sm"
                     style="border-color:var(--sa-border);background:var(--sa-card);">
                    <div class="mb-5">
                        <h2 class="text-lg font-semibold" style="color:var(--sa-fg);">Top restaurants ce mois</h2>
                        <p class="text-sm" style="color:var(--sa-muted-fg);">Par volume de commandes</p>
                    </div>
                    <div class="space-y-2.5">
                        @foreach($topRestaurants as $index => $restaurant)
                            <div class="flex items-center gap-3 p-2.5 rounded-xl"
                                 style="{{ $index === 0 ? 'background:rgba(217,119,6,0.08);border:1px solid rgba(217,119,6,0.20);' : 'background:transparent;' }}">
                                <span class="flex w-7 h-7 items-center justify-center rounded-lg font-bold text-xs flex-shrink-0"
                                      style="{{ $index === 0 ? 'background:rgba(217,119,6,0.15);color:var(--sa-warning);' : ($index === 1 ? 'background:var(--sa-muted);color:var(--sa-muted-fg);' : ($index === 2 ? 'background:rgba(217,119,6,0.10);color:var(--sa-warning);' : 'background:var(--sa-muted);color:var(--sa-muted-fg);')) }}">
                                    {{ $index + 1 }}
                                </span>
                                <div class="flex-1 min-w-0">
                                    <p class="text-xs font-medium truncate" style="color:var(--sa-fg);">{{ $restaurant->name }}</p>
                                    <p class="text-[10px]" style="color:var(--sa-muted-fg);">{{ $restaurant->orders_count }} cmd</p>
                                </div>
                                <span class="text-xs font-bold whitespace-nowrap" style="color:var(--sa-success);">
                                    {{ number_format($restaurant->revenue, 0, ',', ' ') }} F
                                </span>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif

            {{-- Quick Actions --}}
            <div class="rounded-2xl border p-5 shadow-sm"
                 style="border-color:var(--sa-border);background:var(--sa-card);">
                <div class="mb-5">
                    <h2 class="text-lg font-semibold" style="color:var(--sa-fg);">Actions rapides</h2>
                    <p class="text-sm" style="color:var(--sa-muted-fg);">Accès directs</p>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <a href="{{ route('super-admin.restaurants.index') }}"
                       class="group flex flex-col items-center gap-2 rounded-xl border p-4 text-center transition"
                       style="border-color:var(--sa-border);background:var(--sa-bg);"
                       onmouseover="this.style.borderColor='rgba(194,98,31,0.40)';this.style.background='var(--sa-muted)';"
                       onmouseout="this.style.borderColor='var(--sa-border)';this.style.background='var(--sa-bg)';">
                        <svg class="w-6 h-6 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                             style="color:var(--sa-muted-fg);"
                             onmouseover="this.style.color='var(--sa-primary)'" onmouseout="this.style.color='var(--sa-muted-fg)'">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5"/>
                        </svg>
                        <span class="text-sm font-medium" style="color:var(--sa-fg);">Restaurants</span>
                    </a>
                    <a href="{{ route('super-admin.plans.index') }}"
                       class="group flex flex-col items-center gap-2 rounded-xl border p-4 text-center transition"
                       style="border-color:var(--sa-border);background:var(--sa-bg);"
                       onmouseover="this.style.borderColor='rgba(194,98,31,0.40)';this.style.background='var(--sa-muted)';"
                       onmouseout="this.style.borderColor='var(--sa-border)';this.style.background='var(--sa-bg)';">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                             style="color:var(--sa-muted-fg);">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                        </svg>
                        <span class="text-sm font-medium" style="color:var(--sa-fg);">Plans</span>
                    </a>
                    <a href="{{ route('super-admin.utilisateurs.index') }}"
                       class="group flex flex-col items-center gap-2 rounded-xl border p-4 text-center transition"
                       style="border-color:var(--sa-border);background:var(--sa-bg);"
                       onmouseover="this.style.borderColor='rgba(194,98,31,0.40)';this.style.background='var(--sa-muted)';"
                       onmouseout="this.style.borderColor='var(--sa-border)';this.style.background='var(--sa-bg)';">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                             style="color:var(--sa-muted-fg);">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197"/>
                        </svg>
                        <span class="text-sm font-medium" style="color:var(--sa-fg);">Utilisateurs</span>
                    </a>
                    <a href="{{ route('super-admin.stats') }}"
                       class="group flex flex-col items-center gap-2 rounded-xl border p-4 text-center transition"
                       style="border-color:var(--sa-border);background:var(--sa-bg);"
                       onmouseover="this.style.borderColor='rgba(194,98,31,0.40)';this.style.background='var(--sa-muted)';"
                       onmouseout="this.style.borderColor='var(--sa-border)';this.style.background='var(--sa-bg)';">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                             style="color:var(--sa-muted-fg);">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        <span class="text-sm font-medium" style="color:var(--sa-fg);">Statistiques</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- ── Live Orders Section ──────────────────────────────────────────────── --}}
    <div class="mt-6" x-data="liveOrders()" x-init="startPolling()">
        <div class="rounded-2xl overflow-hidden shadow-lg" style="background:var(--sa-sidebar);">

            <div class="px-5 py-4 flex flex-wrap items-center justify-between gap-3"
                 style="border-bottom:1px solid var(--sa-sidebar-border);">
                <h2 class="font-semibold text-sm flex items-center gap-2.5" style="color:var(--sa-sidebar-fg);">
                    <span class="relative flex w-2.5 h-2.5">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75"
                              style="background:var(--sa-success);"></span>
                        <span class="relative inline-flex rounded-full w-2.5 h-2.5"
                              style="background:var(--sa-success);"></span>
                    </span>
                    Commandes en temps réel
                </h2>
                <div class="flex items-center gap-4 text-xs">
                    <span style="color:var(--sa-sidebar-accent);">
                        <span class="font-semibold" style="color:var(--sa-sidebar-fg);" x-text="stats.orders_today"></span> commandes
                    </span>
                    <span class="font-semibold" style="color:var(--sa-success);">
                        <span x-text="formatCurrency(stats.revenue_today)"></span> F
                    </span>
                </div>
            </div>

            {{-- Live Orders Feed --}}
            <div class="divide-y max-h-80 overflow-y-auto" style="border-color:var(--sa-sidebar-border);">
                <template x-for="order in orders" :key="order.id">
                    <div class="px-5 py-3 transition" style="border-color:var(--sa-sidebar-border);"
                         onmouseover="this.style.background='rgba(255,255,255,.04)'" onmouseout="this.style.background='transparent'">
                        <div class="flex items-center justify-between gap-3">
                            <div class="flex items-center gap-3 min-w-0">
                                <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0"
                                     :class="{
                                        'bg-amber-500/15 text-amber-400': order.status === 'pending',
                                        'bg-blue-500/15 text-blue-400': order.status === 'confirmed',
                                        'bg-violet-500/15 text-violet-400': order.status === 'preparing',
                                        'bg-emerald-500/15 text-emerald-400': order.status === 'ready' || order.status === 'completed',
                                        'bg-red-500/15 text-red-400': order.status === 'cancelled',
                                     }">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                                    </svg>
                                </div>
                                <div class="min-w-0">
                                    <p class="text-sm truncate" style="color:var(--sa-sidebar-fg);">
                                        <span class="font-mono text-xs" style="color:var(--sa-sidebar-accent);">#</span><span x-text="order.reference"></span>
                                    </p>
                                    <p class="text-xs truncate" style="color:var(--sa-sidebar-accent);" x-text="order.restaurant"></p>
                                </div>
                            </div>
                            <div class="flex items-center gap-3 flex-shrink-0">
                                <span class="px-2 py-0.5 rounded text-[10px] font-semibold"
                                      :class="{
                                        'bg-amber-500/15 text-amber-400': order.status === 'pending',
                                        'bg-blue-500/15 text-blue-400': order.status === 'confirmed',
                                        'bg-violet-500/15 text-violet-400': order.status === 'preparing',
                                        'bg-emerald-500/15 text-emerald-400': order.status === 'ready' || order.status === 'completed',
                                        'bg-red-500/15 text-red-400': order.status === 'cancelled',
                                      }"
                                      x-text="order.status_label"></span>
                                <span class="text-xs font-semibold" style="color:var(--sa-sidebar-fg);" x-text="formatCurrency(order.total) + ' F'"></span>
                                <span class="text-[10px]" style="color:var(--sa-sidebar-accent);" x-text="order.created_at"></span>
                            </div>
                        </div>
                    </div>
                </template>

                <div x-show="orders.length === 0" class="p-8 text-center">
                    <svg class="w-8 h-8 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"
                         style="color:var(--sa-sidebar-accent);opacity:.4;">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/>
                    </svg>
                    <p class="text-xs" style="color:var(--sa-sidebar-accent);">Les nouvelles commandes apparaîtront ici</p>
                </div>
            </div>

            {{-- Stats Footer --}}
            <div class="px-5 py-3" style="border-top:1px solid var(--sa-sidebar-border);background:rgba(0,0,0,.2);">
                <div class="grid grid-cols-4 gap-4 text-center">
                    <div>
                        <p class="text-lg font-bold" style="color:var(--sa-sidebar-fg);" x-text="stats.pending_orders || 0"></p>
                        <p class="text-[10px]" style="color:var(--sa-sidebar-accent);">En attente</p>
                    </div>
                    <div>
                        <p class="text-lg font-bold" style="color:var(--sa-success);" x-text="stats.active_restaurants || 0"></p>
                        <p class="text-[10px]" style="color:var(--sa-sidebar-accent);">Actifs</p>
                    </div>
                    <div>
                        <p class="text-lg font-bold" style="color:var(--sa-info);" x-text="stats.new_registrations_today || 0"></p>
                        <p class="text-[10px]" style="color:var(--sa-sidebar-accent);">Nouveaux</p>
                    </div>
                    <div>
                        <p class="text-lg font-bold" style="color:var(--sa-primary);" x-text="stats.orders_today || 0"></p>
                        <p class="text-[10px]" style="color:var(--sa-sidebar-accent);">Aujourd'hui</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
    <script src="https://unpkg.com/chart.js@4.4.0/dist/chart.umd.min.js"></script>
    <script>
        function liveDashboard() {
            return {
                isLive: true,
                lastUpdate: '',
                interval: null,
                startPolling() {
                    this.lastUpdate = new Date().toLocaleTimeString('fr-FR', {hour:'2-digit',minute:'2-digit'});
                    this.interval = setInterval(() => {
                        if (this.isLive) {
                            this.lastUpdate = new Date().toLocaleTimeString('fr-FR', {hour:'2-digit',minute:'2-digit'});
                        }
                    }, 5000);
                },
                toggleLive() {
                    this.isLive = !this.isLive;
                }
            }
        }

        function liveOrders() {
            return {
                orders: [],
                stats: {
                    orders_today: {{ $stats['orders']['today'] ?? 0 }},
                    revenue_today: 0,
                    pending_orders: 0,
                    active_restaurants: {{ $stats['restaurants']['active'] ?? 0 }},
                    new_registrations_today: 0,
                },
                interval: null,
                startPolling() {
                    this.fetchData();
                    this.interval = setInterval(() => this.fetchData(), 60000);
                },
                async fetchData() {
                    try {
                        const response = await fetch('{{ route("super-admin.api.live-stats") }}');
                        const data = await response.json();
                        this.orders = data.recent_orders || [];
                        this.stats = data.stats || this.stats;
                    } catch (error) {
                        console.error('Error fetching live data:', error);
                    }
                },
                formatCurrency(amount) {
                    return new Intl.NumberFormat('fr-FR').format(amount || 0);
                }
            }
        }

        // --- Chart.js : données injectées depuis le controller ---
        const ordersChartData = @json($ordersByDay);
        const statusChartData = @json($ordersByStatus);

        document.addEventListener('DOMContentLoaded', function () {
            // Graphique 1 — Courbe commandes & revenus (double axe Y)
            const ordersCtx = document.getElementById('ordersChart');
            if (ordersCtx) {
                new Chart(ordersCtx, {
                    type: 'line',
                    data: {
                        labels: ordersChartData.labels,
                        datasets: [
                            {
                                label: 'Commandes',
                                data: ordersChartData.counts,
                                borderColor: '#3b82f6',
                                backgroundColor: 'rgba(59, 130, 246, 0.08)',
                                borderWidth: 2,
                                pointBackgroundColor: '#3b82f6',
                                pointRadius: 4,
                                pointHoverRadius: 6,
                                tension: 0.4,
                                fill: false,
                                yAxisID: 'yLeft',
                            },
                            {
                                label: 'Revenus (FCFA)',
                                data: ordersChartData.revenues,
                                borderColor: '#10b981',
                                backgroundColor: 'rgba(16, 185, 129, 0.08)',
                                borderWidth: 2,
                                pointBackgroundColor: '#10b981',
                                pointRadius: 4,
                                pointHoverRadius: 6,
                                tension: 0.4,
                                fill: true,
                                yAxisID: 'yRight',
                            },
                        ],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        interaction: { mode: 'index', intersect: false },
                        plugins: {
                            legend: {
                                display: true,
                                position: 'bottom',
                                labels: {
                                    color: '#6b7280',
                                    font: { size: 11, family: 'inherit' },
                                    boxWidth: 12,
                                    padding: 12,
                                },
                            },
                            tooltip: {
                                callbacks: {
                                    label: function (ctx) {
                                        if (ctx.dataset.yAxisID === 'yRight') {
                                            return ' ' + ctx.dataset.label + ': ' + new Intl.NumberFormat('fr-FR').format(ctx.raw) + ' F';
                                        }
                                        return ' ' + ctx.dataset.label + ': ' + ctx.raw;
                                    },
                                },
                            },
                        },
                        scales: {
                            yLeft: {
                                type: 'linear',
                                position: 'left',
                                beginAtZero: true,
                                ticks: { color: '#6b7280', font: { size: 11, family: 'inherit' }, precision: 0 },
                                grid: { color: 'rgba(209, 213, 219, 0.4)' },
                                title: { display: false },
                            },
                            yRight: {
                                type: 'linear',
                                position: 'right',
                                beginAtZero: true,
                                ticks: {
                                    color: '#10b981',
                                    font: { size: 11, family: 'inherit' },
                                    callback: function (v) {
                                        return v >= 1000 ? (v / 1000).toFixed(0) + 'K' : v;
                                    },
                                },
                                grid: { drawOnChartArea: false },
                            },
                            x: {
                                ticks: { color: '#6b7280', font: { size: 11, family: 'inherit' } },
                                grid: { color: 'rgba(209, 213, 219, 0.4)' },
                            },
                        },
                    },
                });
            }

            // Graphique 2 — Donut commandes par statut
            const statusCtx = document.getElementById('statusChart');
            if (statusCtx) {
                const statusColorMap = {
                    'En attente':       '#f59e0b',
                    'Confirmée':        '#3b82f6',
                    'En préparation':   '#8b5cf6',
                    'Prête':            '#06b6d4',
                    'Livrée':           '#10b981',
                    'Terminée':         '#059669',
                    'Annulée':          '#ef4444',
                };
                const bgColors = statusChartData.labels.map(function (l) {
                    return statusColorMap[l] || '#9ca3af';
                });

                new Chart(statusCtx, {
                    type: 'doughnut',
                    data: {
                        labels: statusChartData.labels,
                        datasets: [{
                            data: statusChartData.counts,
                            backgroundColor: bgColors,
                            borderWidth: 2,
                            borderColor: '#ffffff',
                            hoverBorderColor: '#ffffff',
                            hoverOffset: 6,
                        }],
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        cutout: '65%',
                        plugins: {
                            legend: {
                                display: true,
                                position: 'bottom',
                                labels: {
                                    color: '#6b7280',
                                    font: { size: 11, family: 'inherit' },
                                    boxWidth: 10,
                                    padding: 10,
                                },
                            },
                            tooltip: {
                                callbacks: {
                                    label: function (ctx) {
                                        const total = ctx.dataset.data.reduce(function (a, b) { return a + b; }, 0);
                                        const pct = total > 0 ? Math.round(ctx.raw / total * 100) : 0;
                                        return ' ' + ctx.label + ': ' + ctx.raw + ' (' + pct + '%)';
                                    },
                                },
                            },
                        },
                    },
                });
            }
        });
    </script>
    @endpush
</x-layouts.admin-super>
