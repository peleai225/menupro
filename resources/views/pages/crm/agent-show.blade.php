<x-layouts.crm title="Détails Agent">
    <div class="space-y-6">
        {{-- Agent Header --}}
        <div class="rounded-2xl bg-gray-900 border border-gray-800/50 p-6">
            <div class="flex items-start gap-6">
                <img src="{{ $agent->avatar_url }}"
                     class="w-24 h-24 rounded-2xl object-cover ring-4 ring-gray-800"
                     alt="{{ $agent->name }}">
                <div class="flex-1">
                    <div class="flex items-start justify-between">
                        <div>
                            <h1 class="text-2xl font-bold text-white">{{ $agent->name }}</h1>
                            <p class="text-gray-400 mt-1">{{ $agent->email }}</p>
                            <p class="text-gray-500 text-sm mt-0.5">{{ $agent->phone }}</p>
                        </div>
                        <div class="flex gap-2">
                            <span class="px-3 py-1.5 text-sm font-medium rounded-lg
                                {{ $agent->role->value === 'commercial' ? 'bg-orange-500/10 text-orange-400 border border-orange-500/20' : '' }}
                                {{ $agent->role->value === 'technician' ? 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20' : '' }}
                                {{ $agent->role->value === 'team_leader' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : '' }}">
                                {{ $agent->role->label() }}
                            </span>
                            @if($agent->crmGrade)
                                @php $grade = $agent->crmGrade->current_grade; @endphp
                                <span class="px-3 py-1.5 text-sm font-bold rounded-lg
                                    {{ $grade->value === 'rookie' ? 'bg-slate-500/10 text-slate-400 border border-slate-500/20' : '' }}
                                    {{ $grade->value === 'commando' ? 'bg-orange-500/10 text-orange-400 border border-orange-500/20' : '' }}
                                    {{ $grade->value === 'elite' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : '' }}">
                                    {{ $grade->label() }}
                                </span>
                            @endif
                        </div>
                    </div>
                    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mt-6">
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Ville</p>
                            <p class="text-sm text-gray-300">
                                {{ $agent->commercialProfile?->city ?? $agent->technicianProfile?->zone_geographique ?? '-' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Équipe</p>
                            <p class="text-sm text-gray-300">
                                {{ $agent->commercialProfile?->team?->name ?? $agent->technicianProfile?->team?->name ?? '-' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Badge ID</p>
                            <p class="text-sm text-gray-300 font-mono">
                                {{ $agent->commercialProfile?->badge_id ?? '-' }}
                            </p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 mb-1">Dernière connexion</p>
                            <p class="text-sm text-gray-300">
                                {{ $agent->last_login_at ? $agent->last_login_at->diffForHumans() : 'Jamais' }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        {{-- Stats Cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="p-5 rounded-2xl bg-gradient-to-br from-emerald-500/10 to-emerald-600/5 border border-emerald-500/20">
                <p class="text-xs text-gray-500 mb-2">Conversions</p>
                <p class="text-3xl font-bold text-white tabular-nums">{{ $agent->crmGrade?->total_conversions ?? 0 }}</p>
            </div>
            <div class="p-5 rounded-2xl bg-gradient-to-br from-sky-500/10 to-sky-600/5 border border-sky-500/20">
                <p class="text-xs text-gray-500 mb-2">Leads assignés</p>
                <p class="text-3xl font-bold text-white tabular-nums">{{ $agent->crmLeadsAssigned->count() }}</p>
            </div>
            <div class="p-5 rounded-2xl bg-gradient-to-br from-orange-500/10 to-orange-600/5 border border-orange-500/20">
                <p class="text-xs text-gray-500 mb-2">Installations</p>
                <p class="text-3xl font-bold text-white tabular-nums">{{ $agent->crmInstallations->count() }}</p>
            </div>
            <div class="p-5 rounded-2xl bg-gradient-to-br from-amber-500/10 to-amber-600/5 border border-amber-500/20">
                <p class="text-xs text-gray-500 mb-2">Solde wallet (FCFA)</p>
                <p class="text-3xl font-bold text-white tabular-nums">
                    {{ number_format(($agent->crmWallet?->balance_cents ?? 0) / 100, 0, ',', ' ') }}
                </p>
            </div>
        </div>

        {{-- Back Button --}}
        <div>
            <a href="{{ route('crm.admin.agents') }}"
               class="inline-flex items-center gap-2 px-4 py-2 rounded-lg bg-gray-800 border border-gray-700 text-gray-300 hover:bg-gray-700 transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
                Retour aux agents
            </a>
        </div>
    </div>
</x-layouts.crm>
