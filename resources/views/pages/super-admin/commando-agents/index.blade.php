<x-layouts.admin-super title="Agents Commando">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold" style="color:var(--sa-fg);">Agents Commando</h1>
            <p class="mt-1" style="color:var(--sa-muted-fg);">Inscriptions et vérification des agents.</p>
        </div>
    </div>

    {{-- KPI Row 1 : agents --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-4">
        <a href="{{ route('super-admin.commando.agents.index') }}"
           class="border shadow-sm rounded-xl p-4 hover:border-orange-500/50 transition-colors" style="background:var(--sa-card);border-color:var(--sa-border);">
            <p class="text-xs font-medium uppercase tracking-wide mb-1" style="color:var(--sa-muted-fg);">Total agents</p>
            <p class="text-2xl font-bold" style="color:var(--sa-fg);">{{ $stats['total'] }}</p>
        </a>
        <a href="{{ route('super-admin.commando.agents.index', ['status' => 'valide']) }}"
           class="border shadow-sm rounded-xl p-4 hover:border-green-500/50 transition-colors" style="background:var(--sa-card);border-color:var(--sa-border);">
            <p class="text-xs font-medium uppercase tracking-wide mb-1" style="color:var(--sa-muted-fg);">Agents valides</p>
            <p class="text-2xl font-bold text-green-400">{{ $stats['validated'] }}</p>
        </a>
        <a href="{{ route('super-admin.commando.agents.index', ['status' => 'pending_review']) }}"
           class="border shadow-sm rounded-xl p-4 hover:border-amber-500/50 transition-colors" style="background:var(--sa-card);border-color:var(--sa-border);">
            <p class="text-xs font-medium uppercase tracking-wide mb-1" style="color:var(--sa-muted-fg);">En attente</p>
            <p class="text-2xl font-bold text-amber-400">{{ $stats['pending'] }}</p>
        </a>
        <a href="{{ route('super-admin.commando.agents.index', ['status' => 'banni']) }}"
           class="border shadow-sm rounded-xl p-4 hover:border-red-500/50 transition-colors" style="background:var(--sa-card);border-color:var(--sa-border);">
            <p class="text-xs font-medium uppercase tracking-wide mb-1" style="color:var(--sa-muted-fg);">Bannis</p>
            <p class="text-2xl font-bold text-red-500">{{ $stats['banned'] }}</p>
        </a>
    </div>

    {{-- KPI Row 2 : finances & activité --}}
    <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-6">
        <div class="border shadow-sm rounded-xl p-4" style="background:var(--sa-card);border-color:var(--sa-border);">
            <p class="text-xs font-medium uppercase tracking-wide mb-1" style="color:var(--sa-muted-fg);">Commissions versées</p>
            <p class="text-xl font-bold text-green-400">{{ number_format($stats['total_commissions_fcfa'], 0, ',', ' ') }}</p>
            <p class="text-xs mt-0.5" style="color:var(--sa-muted-fg);">FCFA</p>
        </div>
        <div class="border shadow-sm rounded-xl p-4" style="background:var(--sa-card);border-color:var(--sa-border);">
            <p class="text-xs font-medium uppercase tracking-wide mb-1" style="color:var(--sa-muted-fg);">Retraits en attente</p>
            <p class="text-xl font-bold text-amber-400">{{ number_format($stats['total_pending_withdrawal_fcfa'], 0, ',', ' ') }}</p>
            <p class="text-xs mt-0.5" style="color:var(--sa-muted-fg);">FCFA</p>
        </div>
        <div class="border shadow-sm rounded-xl p-4" style="background:var(--sa-card);border-color:var(--sa-border);">
            <p class="text-xs font-medium uppercase tracking-wide mb-1" style="color:var(--sa-muted-fg);">Restaurants actifs parrainés</p>
            <p class="text-xl font-bold text-orange-400">{{ $stats['restaurants_referred_active'] }}</p>
        </div>
        <div class="border shadow-sm rounded-xl p-4" style="background:var(--sa-card);border-color:var(--sa-border);">
            <p class="text-xs font-medium uppercase tracking-wide mb-1" style="color:var(--sa-muted-fg);">Nouveaux ce mois</p>
            <p class="text-xl font-bold" style="color:var(--sa-fg);">{{ $stats['new_agents_this_month'] }}</p>
        </div>
    </div>

    {{-- Top 5 agents --}}
    @if($stats['top_agents']->isNotEmpty())
    <div class="border shadow-sm rounded-xl overflow-hidden mb-6" style="background:var(--sa-card);border-color:var(--sa-border);">
        <div class="px-6 py-4" style="border-bottom:1px solid var(--sa-border);">
            <h2 class="text-base font-semibold" style="color:var(--sa-fg);">Top 5 agents — restaurants parrainés</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full min-w-[500px]">
                <thead style="background:var(--sa-muted);">
                    <tr>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase" style="color:var(--sa-muted-fg);">#</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase" style="color:var(--sa-muted-fg);">Agent</th>
                        <th class="px-5 py-3 text-left text-xs font-semibold uppercase" style="color:var(--sa-muted-fg);">Badge ID</th>
                        <th class="px-5 py-3 text-center text-xs font-semibold uppercase" style="color:var(--sa-muted-fg);">Restaurants</th>
                        <th class="px-5 py-3 text-right text-xs font-semibold uppercase" style="color:var(--sa-muted-fg);">Solde (FCFA)</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($stats['top_agents'] as $i => $topAgent)
                    <tr style="border-top:1px solid var(--sa-border);">
                        <td class="px-5 py-3 text-sm font-bold" style="color:var(--sa-muted-fg);">{{ $i + 1 }}</td>
                        <td class="px-5 py-3">
                            <a href="{{ route('super-admin.commando.agents.show', $topAgent) }}"
                               class="font-medium hover:underline" style="color:var(--sa-fg);">
                                {{ $topAgent->full_name }}
                            </a>
                        </td>
                        <td class="px-5 py-3 font-mono text-xs" style="color:var(--sa-muted-fg);">{{ $topAgent->badge_id_display }}</td>
                        <td class="px-5 py-3 text-center">
                            <span class="px-2 py-0.5 rounded-lg text-xs font-semibold" style="background:rgba(249,115,22,0.15);color:var(--sa-warning);">
                                {{ $topAgent->referred_restaurants_count }}
                            </span>
                        </td>
                        <td class="px-5 py-3 text-right font-medium" style="color:var(--sa-fg);">{{ number_format($topAgent->balance, 0, ',', ' ') }}</td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    <form method="GET" class="border shadow-sm rounded-xl p-4 mb-6" style="background:var(--sa-card);border-color:var(--sa-border);">
        <div class="flex flex-col sm:flex-row gap-4">
            <select name="status" class="h-10 px-4 border rounded-lg" style="background:var(--sa-muted);border-color:var(--sa-border);color:var(--sa-fg);">
                <option value="">Tous les statuts</option>
                <option value="shadow" {{ request('status') === 'shadow' ? 'selected' : '' }}>Shadow</option>
                <option value="pending_review" {{ request('status') === 'pending_review' ? 'selected' : '' }}>En attente</option>
                <option value="valide" {{ request('status') === 'valide' ? 'selected' : '' }}>Valide</option>
                <option value="rejete" {{ request('status') === 'rejete' ? 'selected' : '' }}>Rejeté</option>
                <option value="banni" {{ request('status') === 'banni' ? 'selected' : '' }}>Banni</option>
            </select>
            <input type="text" name="city" value="{{ request('city') }}" placeholder="Ville..."
                   class="h-10 px-4 border rounded-lg" style="background:var(--sa-muted);border-color:var(--sa-border);color:var(--sa-fg);">
            <button type="submit" class="h-10 px-6 bg-primary-500 rounded-lg font-medium hover:bg-primary-600" style="color:var(--sa-fg);">Filtrer</button>
            <a href="{{ route('super-admin.commando.agents.export', request()->only(['status', 'city'])) }}"
               class="inline-flex h-10 items-center justify-center gap-2 rounded-lg px-4 text-sm font-medium shadow-sm transition"
               style="background:var(--sa-success);color:#fff;">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Export CSV
            </a>
            <a href="{{ route('super-admin.commando.agents.export-commissions') }}"
               class="inline-flex h-10 items-center justify-center gap-2 rounded-lg px-4 text-sm font-medium shadow-sm transition"
               style="background:rgba(61,158,98,0.15);color:var(--sa-success);border:1px solid rgba(61,158,98,0.3);">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Commissions CSV
            </a>
        </div>
    </form>

    <div class="border shadow-sm rounded-xl overflow-hidden" style="background:var(--sa-card);border-color:var(--sa-border);">
        <div class="overflow-x-auto">
            <table class="w-full min-w-[600px]">
                <thead style="background:var(--sa-muted);border-bottom:1px solid var(--sa-border);">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase" style="color:var(--sa-muted-fg);">Agent</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase" style="color:var(--sa-muted-fg);">WhatsApp</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase" style="color:var(--sa-muted-fg);">Ville</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase" style="color:var(--sa-muted-fg);">Statut</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold uppercase" style="color:var(--sa-muted-fg);">Inscrit le</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold uppercase" style="color:var(--sa-muted-fg);">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($agents as $a)
                        @php
                            $status = $a->status_verification;
                            $badgeStyle = match(true) {
                                $status->value === 'pending_review' => 'background:rgba(217,119,6,0.15);color:var(--sa-warning);',
                                $status->value === 'valide' => 'background:rgba(61,158,98,0.15);color:var(--sa-success);',
                                in_array($status->value, ['rejete', 'banni']) => 'background:rgba(220,38,38,0.15);color:var(--sa-danger);',
                                default => 'background:rgba(107,101,96,0.15);color:var(--sa-muted-fg);',
                            };
                        @endphp
                        <tr style="border-bottom:1px solid var(--sa-border);">
                            <td class="px-6 py-4">
                                <span class="font-medium" style="color:var(--sa-fg);">{{ $a->full_name }}</span>
                            </td>
                            <td class="px-6 py-4" style="color:var(--sa-muted-fg);">{{ $a->whatsapp }}</td>
                            <td class="px-6 py-4" style="color:var(--sa-muted-fg);">{{ $a->city ?? '–' }}</td>
                            <td class="px-6 py-4">
                                <span class="px-2 py-1 rounded-lg text-xs font-medium" style="{{ $badgeStyle }}">
                                    {{ $status->label() }}
                                </span>
                            </td>
                            <td class="px-6 py-4 text-sm" style="color:var(--sa-muted-fg);">{{ $a->created_at->format('d/m/Y H:i') }}</td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-3">
                                    <a href="{{ route('super-admin.commando.agents.show', $a) }}"
                                       class="text-primary-400 hover:text-primary-300 text-sm font-medium">Voir</a>
                                    <form method="POST" action="{{ route('super-admin.commando.agents.destroy', $a) }}" class="inline"
                                          onsubmit="return confirm('Supprimer définitivement cet agent ? Cette action est irréversible.');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-red-600 hover:text-red-300 text-sm font-medium">Supprimer</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="px-6 py-12 text-center text-sm" style="color:var(--sa-muted-fg);">Aucun agent.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($agents->hasPages())
            <div class="px-6 py-4" style="border-top:1px solid var(--sa-border);">
                {{ $agents->links() }}
            </div>
        @endif
    </div>
</x-layouts.admin-super>
