<x-layouts.admin-super title="Livreurs">
    <div class="space-y-6">

        {{-- Stats --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Livreurs actifs --}}
            <div class="rounded-2xl border p-5 shadow-sm transition hover:shadow-md" style="border-color:var(--sa-border);background:var(--sa-card);">
                <div class="flex items-start justify-between">
                    <span class="flex w-11 h-11 items-center justify-center rounded-xl" style="background:rgba(194,98,31,0.10);color:var(--sa-primary);">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v4m0 0a4 4 0 100 8 4 4 0 000-8zM4 16s0-4 8-4 8 4 8 4"/><circle cx="12" cy="8" r="2" fill="currentColor"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 20h14"/></svg>
                    </span>
                </div>
                <p class="mt-4 text-3xl font-bold" style="color:var(--sa-fg);">{{ $stats['total'] }}</p>
                <p class="mt-1 text-sm" style="color:var(--sa-muted-fg);">Livreurs actifs</p>
            </div>
            {{-- Disponibles --}}
            <div class="rounded-2xl border p-5 shadow-sm transition hover:shadow-md" style="border-color:var(--sa-border);background:var(--sa-card);">
                <div class="flex items-start justify-between">
                    <span class="flex w-11 h-11 items-center justify-center rounded-xl" style="background:rgba(61,158,98,0.10);color:var(--sa-success);">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                </div>
                <p class="mt-4 text-3xl font-bold" style="color:var(--sa-fg);">{{ $stats['approved'] }}</p>
                <p class="mt-1 text-sm" style="color:var(--sa-muted-fg);">Disponibles</p>
            </div>
            {{-- En course --}}
            <div class="rounded-2xl border p-5 shadow-sm transition hover:shadow-md" style="border-color:var(--sa-border);background:var(--sa-card);">
                <div class="flex items-start justify-between">
                    <span class="flex w-11 h-11 items-center justify-center rounded-xl" style="background:rgba(59,111,212,0.10);color:var(--sa-info);">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><rect x="1" y="3" width="15" height="13" rx="1"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 8h4l3 5v3h-7V8zM5.5 21a1.5 1.5 0 100-3 1.5 1.5 0 000 3zM18.5 21a1.5 1.5 0 100-3 1.5 1.5 0 000 3z"/></svg>
                    </span>
                </div>
                <p class="mt-4 text-3xl font-bold" style="color:var(--sa-fg);">{{ $stats['pending'] }}</p>
                <p class="mt-1 text-sm" style="color:var(--sa-muted-fg);">En course</p>
            </div>
            {{-- Livraisons totales --}}
            <div class="rounded-2xl border p-5 shadow-sm transition hover:shadow-md" style="border-color:var(--sa-border);background:var(--sa-card);">
                <div class="flex items-start justify-between">
                    <span class="flex w-11 h-11 items-center justify-center rounded-xl" style="background:rgba(217,119,6,0.10);color:var(--sa-warning);">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                    </span>
                </div>
                <p class="mt-4 text-3xl font-bold" style="color:var(--sa-fg);">{{ $stats['online'] }}</p>
                <p class="mt-1 text-sm" style="color:var(--sa-muted-fg);">Livraisons totales</p>
            </div>
        </div>

        {{-- FilterTabs + Search --}}
        <div class="flex flex-wrap items-center justify-between gap-3">
            {{-- Tabs --}}
            <div class="flex items-center gap-1 rounded-xl p-1" style="background:rgba(243,242,239,0.60);">
                @php $currentStatus = request('status', 'tous'); @endphp
                @foreach([
                    ['key'=>'tous',     'label'=>'Tous'],
                    ['key'=>'online',   'label'=>'Disponibles'],
                    ['key'=>'approved', 'label'=>'En course'],
                    ['key'=>'rejected', 'label'=>'Hors ligne'],
                ] as $tab)
                <a href="{{ request()->fullUrlWithQuery(['status' => $tab['key'] === 'tous' ? null : $tab['key'], 'page' => null]) }}"
                   class="px-4 py-1.5 rounded-lg text-sm font-medium transition-colors"
                   style="{{ ($currentStatus === $tab['key'] || ($tab['key']==='tous' && !request('status'))) ? 'background:var(--sa-card);color:var(--sa-fg);box-shadow:0 1px 3px rgba(30,28,24,0.10);' : 'color:var(--sa-muted-fg);' }}">
                    {{ $tab['label'] }}
                </a>
                @endforeach
            </div>
            {{-- Search + city filter --}}
            <form method="GET" class="flex items-center gap-2">
                @if(request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 pointer-events-none" style="color:var(--sa-muted-fg);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher un livreur..."
                           class="h-9 pl-9 pr-4 rounded-xl border text-sm focus:outline-none"
                           style="background:var(--sa-card);border-color:var(--sa-border);color:var(--sa-fg);min-width:220px;">
                </div>
                <select name="city" class="h-9 px-3 rounded-xl border text-sm focus:outline-none" style="background:var(--sa-card);border-color:var(--sa-border);color:var(--sa-fg);">
                    <option value="">Toutes les villes</option>
                    @foreach($cities as $city)
                        <option value="{{ $city }}" {{ request('city') === $city ? 'selected' : '' }}>{{ $city }}</option>
                    @endforeach
                </select>
                <button type="submit" class="h-9 px-4 rounded-xl text-sm font-medium" style="background:var(--sa-primary);color:var(--sa-primary-fg);">Filtrer</button>
                @if(request()->anyFilled(['search','status','city']))
                    <a href="{{ route('super-admin.drivers.index') }}"
                       class="h-9 px-3 flex items-center rounded-xl border text-sm"
                       style="border-color:var(--sa-border);color:var(--sa-muted-fg);background:var(--sa-card);">Réinitialiser</a>
                @endif
            </form>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto rounded-2xl border shadow-sm" style="border-color:var(--sa-border);background:var(--sa-card);">
            <table class="w-full text-sm">
                <thead>
                    <tr style="border-bottom:1px solid var(--sa-border);">
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--sa-muted-fg);">Livreur</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--sa-muted-fg);">Téléphone</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--sa-muted-fg);">Zone</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--sa-muted-fg);">Véhicule</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--sa-muted-fg);">Livraisons</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--sa-muted-fg);">Note</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--sa-muted-fg);">Statut</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--sa-muted-fg);">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($drivers as $driver)
                    <tr class="transition-colors" style="border-bottom:1px solid var(--sa-border);"
                        onmouseover="this.style.background='rgba(243,242,239,0.60)'"
                        onmouseout="this.style.background='transparent'">
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-3">
                                <span class="flex w-9 h-9 items-center justify-center rounded-xl text-sm font-bold text-white flex-shrink-0"
                                      style="background:linear-gradient(135deg,var(--sa-primary),#7c5c9e);">
                                    {{ strtoupper(substr($driver->name, 0, 1)) }}
                                </span>
                                <div>
                                    <a href="{{ route('super-admin.drivers.show', $driver) }}"
                                       class="font-semibold hover:underline"
                                       style="color:var(--sa-fg);">{{ $driver->name }}</a>
                                    <p class="text-xs" style="color:var(--sa-muted-fg);">{{ $driver->phone }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3.5" style="color:var(--sa-muted-fg);">{{ $driver->phone ?? '—' }}</td>
                        <td class="px-5 py-3.5" style="color:var(--sa-muted-fg);">{{ $driver->city ?? '—' }}</td>
                        <td class="px-5 py-3.5" style="color:var(--sa-muted-fg);">{{ $driver->vehicle_type ?? '—' }}</td>
                        <td class="px-5 py-3.5 font-medium" style="color:var(--sa-fg);">{{ $driver->total_deliveries ?? 0 }}</td>
                        <td class="px-5 py-3.5">
                            @if($driver->rating)
                                <span class="inline-flex items-center gap-1 font-medium" style="color:var(--sa-fg);">
                                    <svg class="w-4 h-4" style="fill:var(--sa-warning);color:var(--sa-warning);" viewBox="0 0 24 24">
                                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                    </svg>
                                    {{ number_format($driver->rating, 1) }}
                                </span>
                            @else
                                <span style="color:var(--sa-muted-fg);">—</span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5">
                            @if($driver->verification_status === 'approved' && $driver->is_active && $driver->is_available)
                                <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs font-medium whitespace-nowrap"
                                      style="background:rgba(61,158,98,0.10);color:var(--sa-success);border-color:rgba(61,158,98,0.20);">
                                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span>Disponible
                                </span>
                            @elseif($driver->verification_status === 'approved' && $driver->is_active)
                                <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs font-medium whitespace-nowrap"
                                      style="background:rgba(59,111,212,0.10);color:var(--sa-info);border-color:rgba(59,111,212,0.20);">
                                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span>En course
                                </span>
                            @elseif($driver->verification_status === 'pending')
                                <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs font-medium whitespace-nowrap"
                                      style="background:rgba(217,119,6,0.10);color:var(--sa-warning);border-color:rgba(217,119,6,0.20);">
                                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span>En attente
                                </span>
                            @elseif($driver->verification_status === 'rejected')
                                <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs font-medium whitespace-nowrap"
                                      style="background:rgba(220,38,38,0.10);color:var(--sa-danger);border-color:rgba(220,38,38,0.20);">
                                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span>Rejeté
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs font-medium whitespace-nowrap"
                                      style="background:rgba(107,101,96,0.10);color:var(--sa-muted-fg);border-color:rgba(107,101,96,0.20);">
                                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span>Hors ligne
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-1">
                                <a href="{{ route('super-admin.drivers.show', $driver) }}"
                                   class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-medium transition-colors"
                                   style="background:rgba(243,242,239,0.60);color:var(--sa-fg);"
                                   onmouseover="this.style.background='rgba(194,98,31,0.10)';this.style.color='var(--sa-primary)';"
                                   onmouseout="this.style.background='rgba(243,242,239,0.60)';this.style.color='var(--sa-fg)';">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                    Voir
                                </a>
                                @if($driver->verification_status === 'pending')
                                    <form method="POST" action="{{ route('super-admin.drivers.approve', $driver) }}">@csrf
                                        <button type="submit" class="p-1.5 rounded-lg transition-colors"
                                                style="color:var(--sa-success);"
                                                onmouseover="this.style.background='rgba(61,158,98,0.10)'"
                                                onmouseout="this.style.background='transparent'"
                                                title="Approuver">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        </button>
                                    </form>
                                    <form method="POST" action="{{ route('super-admin.drivers.reject', $driver) }}">@csrf
                                        <button type="submit" class="p-1.5 rounded-lg transition-colors"
                                                style="color:var(--sa-danger);"
                                                onmouseover="this.style.background='rgba(220,38,38,0.10)'"
                                                onmouseout="this.style.background='transparent'"
                                                title="Rejeter">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        </button>
                                    </form>
                                @elseif($driver->is_active)
                                    <form method="POST" action="{{ route('super-admin.drivers.suspend', $driver) }}">@csrf
                                        <button type="submit" class="p-1.5 rounded-lg transition-colors"
                                                style="color:var(--sa-warning);"
                                                onmouseover="this.style.background='rgba(217,119,6,0.10)'"
                                                onmouseout="this.style.background='transparent'"
                                                title="Suspendre">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 9v6m4-6v6"/></svg>
                                        </button>
                                    </form>
                                @else
                                    <form method="POST" action="{{ route('super-admin.drivers.reactivate', $driver) }}">@csrf
                                        <button type="submit" class="p-1.5 rounded-lg transition-colors"
                                                style="color:var(--sa-success);"
                                                onmouseover="this.style.background='rgba(61,158,98,0.10)'"
                                                onmouseout="this.style.background='transparent'"
                                                title="Réactiver">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/></svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-5 py-12 text-center text-sm" style="color:var(--sa-muted-fg);">Aucun livreur trouvé.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            @if($drivers->hasPages())
                <div class="px-5 py-3" style="border-top:1px solid var(--sa-border);">{{ $drivers->links() }}</div>
            @endif
        </div>
    </div>
</x-layouts.admin-super>
