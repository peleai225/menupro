<x-layouts.admin-super title="Clients">
    <div class="space-y-6">

        {{-- Stats --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            {{-- Clients totaux --}}
            <div class="rounded-2xl border p-5 shadow-sm transition hover:shadow-md" style="border-color:var(--sa-border);background:var(--sa-card);">
                <div class="flex items-start justify-between">
                    <span class="flex w-11 h-11 items-center justify-center rounded-xl" style="background:color-mix(in oklch,var(--sa-primary) 10%,transparent);color:var(--sa-primary);">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 21v-2a4 4 0 00-4-4H5a4 4 0 00-4 4v2"/><circle cx="9" cy="7" r="4"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M23 21v-2a4 4 0 00-3-3.87"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 3.13a4 4 0 010 7.75"/></svg>
                    </span>
                </div>
                <p class="mt-4 text-3xl font-bold" style="color:var(--sa-fg);">{{ $stats['total'] }}</p>
                <p class="mt-1 text-sm" style="color:var(--sa-muted-fg);">Clients totaux</p>
            </div>
            {{-- Clients actifs --}}
            <div class="rounded-2xl border p-5 shadow-sm transition hover:shadow-md" style="border-color:var(--sa-border);background:var(--sa-card);">
                <div class="flex items-start justify-between">
                    <span class="flex w-11 h-11 items-center justify-center rounded-xl" style="background:color-mix(in oklch,var(--sa-success) 10%,transparent);color:var(--sa-success);">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                    </span>
                </div>
                <p class="mt-4 text-3xl font-bold" style="color:var(--sa-fg);">{{ $stats['active'] }}</p>
                <p class="mt-1 text-sm" style="color:var(--sa-muted-fg);">Clients actifs</p>
            </div>
            {{-- Commandes cumulées --}}
            <div class="rounded-2xl border p-5 shadow-sm transition hover:shadow-md" style="border-color:var(--sa-border);background:var(--sa-card);">
                <div class="flex items-start justify-between">
                    <span class="flex w-11 h-11 items-center justify-center rounded-xl" style="background:color-mix(in oklch,var(--sa-info) 10%,transparent);color:var(--sa-info);">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"/></svg>
                    </span>
                </div>
                <p class="mt-4 text-3xl font-bold" style="color:var(--sa-fg);">{{ $stats['ordered_today'] }}</p>
                <p class="mt-1 text-sm" style="color:var(--sa-muted-fg);">Commandes cumulées</p>
            </div>
            {{-- Chiffre d'affaires --}}
            <div class="rounded-2xl border p-5 shadow-sm transition hover:shadow-md" style="border-color:var(--sa-border);background:var(--sa-card);">
                <div class="flex items-start justify-between">
                    <span class="flex w-11 h-11 items-center justify-center rounded-xl" style="background:color-mix(in oklch,var(--sa-warning) 10%,transparent);color:var(--sa-warning);">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                    </span>
                </div>
                <p class="mt-4 text-3xl font-bold" style="color:var(--sa-fg);">{{ $stats['new_this_month'] }}</p>
                <p class="mt-1 text-sm" style="color:var(--sa-muted-fg);">Chiffre d'affaires</p>
            </div>
        </div>

        {{-- FilterTabs + Search --}}
        <div class="flex flex-wrap items-center justify-between gap-3">
            {{-- Tabs --}}
            <div class="flex items-center gap-1 rounded-xl p-1" style="background:color-mix(in oklch,var(--sa-muted) 50%,transparent);">
                @php $currentStatus = request('status', 'tous'); @endphp
                @foreach([['key'=>'tous','label'=>'Tous'],['key'=>'active','label'=>'Actifs'],['key'=>'inactive','label'=>'Inactifs']] as $tab)
                <a href="{{ request()->fullUrlWithQuery(['status' => $tab['key'] === 'tous' ? null : $tab['key'], 'page' => null]) }}"
                   class="px-4 py-1.5 rounded-lg text-sm font-medium transition-colors"
                   style="{{ ($currentStatus === $tab['key'] || ($tab['key']==='tous' && !request('status'))) ? 'background:var(--sa-card);color:var(--sa-fg);box-shadow:0 1px 3px color-mix(in oklch,var(--sa-fg) 10%,transparent);' : 'color:var(--sa-muted-fg);' }}">
                    {{ $tab['label'] }}
                </a>
                @endforeach
            </div>
            {{-- Search + city filter --}}
            <form method="GET" class="flex items-center gap-2">
                @if(request('status'))<input type="hidden" name="status" value="{{ request('status') }}">@endif
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-4 h-4 pointer-events-none" style="color:var(--sa-muted-fg);" fill="none" stroke="currentColor" viewBox="0 0 24 24"><circle cx="11" cy="11" r="8"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-4.35-4.35"/></svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher un client..."
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
            </form>
        </div>

        {{-- Table --}}
        <div class="overflow-x-auto rounded-2xl border shadow-sm" style="border-color:var(--sa-border);background:var(--sa-card);">
            <table class="w-full text-sm">
                <thead>
                    <tr style="border-bottom:1px solid var(--sa-border);">
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--sa-muted-fg);">Client</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--sa-muted-fg);">Téléphone</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--sa-muted-fg);">Ville</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--sa-muted-fg);">Commandes</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--sa-muted-fg);">Total dépensé</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--sa-muted-fg);">Dernière commande</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--sa-muted-fg);">Statut</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase tracking-wide" style="color:var(--sa-muted-fg);">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($customers as $customer)
                    <tr class="transition-colors" style="border-bottom:1px solid var(--sa-border);"
                        onmouseover="this.style.background='color-mix(in oklch,var(--sa-muted) 50%,transparent)'"
                        onmouseout="this.style.background='transparent'">
                        <td class="px-5 py-3.5">
                            <div class="flex items-center gap-3">
                                <span class="flex w-9 h-9 items-center justify-center rounded-xl text-sm font-bold text-white flex-shrink-0"
                                      style="background:linear-gradient(135deg,var(--sa-primary),color-mix(in oklch,var(--sa-primary) 70%,var(--sa-info)));">
                                    {{ strtoupper(substr($customer->user->name ?? '?', 0, 1)) }}
                                </span>
                                <div>
                                    <p class="font-semibold" style="color:var(--sa-fg);">{{ $customer->user->name ?? '—' }}</p>
                                    <p class="text-xs" style="color:var(--sa-muted-fg);">{{ $customer->user->email ?? '—' }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-5 py-3.5" style="color:var(--sa-muted-fg);">{{ $customer->phone ?? '—' }}</td>
                        <td class="px-5 py-3.5" style="color:var(--sa-muted-fg);">{{ $customer->city ?? '—' }}</td>
                        <td class="px-5 py-3.5 font-medium" style="color:var(--sa-fg);">{{ $customer->total_orders ?? 0 }}</td>
                        <td class="px-5 py-3.5 font-medium" style="color:var(--sa-fg);">
                            {{ number_format($customer->total_spent ?? 0) }} FCFA
                        </td>
                        <td class="px-5 py-3.5 text-xs" style="color:var(--sa-muted-fg);">
                            {{ $customer->last_order_at ? $customer->last_order_at->diffForHumans() : '—' }}
                        </td>
                        <td class="px-5 py-3.5">
                            @if($customer->is_active)
                                <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs font-medium whitespace-nowrap"
                                      style="background:color-mix(in oklch,var(--sa-success) 10%,transparent);color:var(--sa-success);border-color:color-mix(in oklch,var(--sa-success) 20%,transparent);">
                                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span>Actif
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1.5 rounded-full border px-2.5 py-0.5 text-xs font-medium whitespace-nowrap"
                                      style="background:color-mix(in oklch,var(--sa-muted-fg) 10%,transparent);color:var(--sa-muted-fg);border-color:color-mix(in oklch,var(--sa-muted-fg) 20%,transparent);">
                                    <span class="w-1.5 h-1.5 rounded-full bg-current"></span>Inactif
                                </span>
                            @endif
                        </td>
                        <td class="px-5 py-3.5">
                            <a href="{{ route('super-admin.customers.show', $customer->id) }}"
                               class="inline-flex items-center gap-1.5 rounded-lg px-3 py-1.5 text-xs font-medium transition-colors"
                               style="background:color-mix(in oklch,var(--sa-muted) 50%,transparent);color:var(--sa-fg);"
                               onmouseover="this.style.background='color-mix(in oklch,var(--sa-primary) 10%,transparent)';this.style.color='var(--sa-primary)';"
                               onmouseout="this.style.background='color-mix(in oklch,var(--sa-muted) 50%,transparent)';this.style.color='var(--sa-fg)';">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                Voir
                            </a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="px-5 py-12 text-center text-sm" style="color:var(--sa-muted-fg);">Aucun client trouvé.</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
            @if($customers->hasPages())
                <div class="px-5 py-3" style="border-top:1px solid var(--sa-border);">{{ $customers->links() }}</div>
            @endif
        </div>
    </div>
</x-layouts.admin-super>
