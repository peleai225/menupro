<div class="space-y-8" wire:poll.30s>
    {{-- KPI Row --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @php $kpis = $this->kpis; @endphp

        <div class="p-5 rounded-2xl bg-gray-900 border border-gray-800/50 hover:border-gray-700 transition group">
            <div class="flex items-center justify-between mb-3">
                <span class="w-9 h-9 rounded-xl bg-sky-500/10 flex items-center justify-center group-hover:scale-110 transition">
                    <svg class="w-4.5 h-4.5 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </span>
            </div>
            <p class="text-2xl font-bold text-white tabular-nums">{{ $kpis['total_leads'] }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Leads ce mois</p>
        </div>

        <div class="p-5 rounded-2xl bg-gray-900 border border-gray-800/50 hover:border-gray-700 transition group">
            <div class="flex items-center justify-between mb-3">
                <span class="w-9 h-9 rounded-xl bg-emerald-500/10 flex items-center justify-center group-hover:scale-110 transition">
                    <svg class="w-4.5 h-4.5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <p class="text-2xl font-bold text-white tabular-nums">{{ $kpis['converted'] }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Convertis</p>
        </div>

        <div class="p-5 rounded-2xl bg-gray-900 border border-gray-800/50 hover:border-gray-700 transition group">
            <div class="flex items-center justify-between mb-3">
                <span class="w-9 h-9 rounded-xl bg-orange-500/10 flex items-center justify-center group-hover:scale-110 transition">
                    <svg class="w-4.5 h-4.5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </span>
            </div>
            <p class="text-2xl font-bold text-white tabular-nums">{{ $kpis['active_agents'] }}</p>
            <p class="text-xs text-gray-500 mt-0.5">Agents actifs</p>
        </div>

        <div class="p-5 rounded-2xl bg-gray-900 border border-gray-800/50 hover:border-gray-700 transition group">
            <div class="flex items-center justify-between mb-3">
                <span class="w-9 h-9 rounded-xl bg-amber-500/10 flex items-center justify-center group-hover:scale-110 transition">
                    <svg class="w-4.5 h-4.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <p class="text-2xl font-bold text-white tabular-nums">{{ number_format($kpis['revenue_cents'] / 100, 0, ',', ' ') }}</p>
            <p class="text-xs text-gray-500 mt-0.5">FCFA ce mois</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Pipeline funnel --}}
        <div class="lg:col-span-2 rounded-2xl bg-gray-900 border border-gray-800/50 p-6">
            <h3 class="text-sm font-semibold text-gray-300 mb-4">Pipeline funnel</h3>
            <div class="space-y-2">
                @php
                    $maxCount = max(1, collect($this->funnel)->max('count'));
                @endphp
                @foreach($this->funnel as $item)
                <div class="flex items-center gap-3">
                    <span class="w-24 text-xs text-gray-400 text-right truncate">{{ $item['status']->label() }}</span>
                    <div class="flex-1 h-8 rounded-lg bg-gray-800/50 overflow-hidden relative">
                        <div class="h-full bg-{{ $item['status']->color() }}-500/30 rounded-lg transition-all duration-700 ease-out"
                             style="width: {{ ($item['count'] / $maxCount) * 100 }}%">
                        </div>
                        <span class="absolute inset-y-0 left-3 flex items-center text-xs font-semibold text-gray-200">
                            {{ $item['count'] }}
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Top performers --}}
        <div class="rounded-2xl bg-gray-900 border border-gray-800/50 p-6">
            <h3 class="text-sm font-semibold text-gray-300 mb-4">Top performers</h3>
            <div class="space-y-3">
                @foreach($this->topPerformers->take(5) as $performer)
                <div class="flex items-center gap-3">
                    <span class="w-5 text-xs font-bold {{ $loop->index === 0 ? 'text-amber-400' : ($loop->index === 1 ? 'text-gray-300' : 'text-gray-500') }}">
                        #{{ $loop->iteration }}
                    </span>
                    <img src="{{ $performer->avatar_url }}" class="w-7 h-7 rounded-lg object-cover" alt="">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-gray-200 truncate">{{ $performer->name }}</p>
                    </div>
                    <span class="text-sm font-semibold text-emerald-400 tabular-nums">{{ $performer->conversions_count }}</span>
                </div>
                @endforeach
            </div>
        </div>
    </div>

    {{-- Teams overview + Pending actions --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Teams --}}
        <div class="rounded-2xl bg-gray-900 border border-gray-800/50 p-6">
            <h3 class="text-sm font-semibold text-gray-300 mb-4">Équipes</h3>
            <div class="space-y-3">
                @foreach($this->teams as $team)
                <div class="p-3 rounded-xl border border-gray-800/50 hover:border-gray-700 transition">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-sm font-medium text-gray-200">{{ $team->name }}</span>
                        <span class="text-xs text-gray-500">{{ $team->zone }}</span>
                    </div>
                    <div class="flex items-center gap-2">
                        <div class="flex-1 h-2 rounded-full bg-gray-800 overflow-hidden">
                            <div class="h-full rounded-full bg-gradient-to-r from-orange-500 to-amber-400 transition-all duration-700"
                                 style="width: {{ $team->progress_percent }}%"></div>
                        </div>
                        <span class="text-xs text-gray-400 tabular-nums">{{ $team->active_leads_count }}</span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Pending actions --}}
        <div class="rounded-2xl bg-gray-900 border border-gray-800/50 p-6">
            <h3 class="text-sm font-semibold text-gray-300 mb-4">Actions en attente</h3>
            <div class="space-y-3">
                @if($kpis['pending_withdrawals'] > 0)
                <a href="{{ route('crm.admin.withdrawals') }}"
                   class="flex items-center gap-3 p-3 rounded-xl border border-amber-500/20 bg-amber-500/5 hover:bg-amber-500/10 transition">
                    <span class="w-8 h-8 rounded-lg bg-amber-500/10 flex items-center justify-center">
                        <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                    <div class="flex-1">
                        <p class="text-sm text-gray-200">{{ $kpis['pending_withdrawals'] }} retrait(s) en attente</p>
                    </div>
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
                @endif

                @if($kpis['installations_pending'] > 0)
                <a href="{{ route('crm.installations.index') }}"
                   class="flex items-center gap-3 p-3 rounded-xl border border-blue-500/20 bg-blue-500/5 hover:bg-blue-500/10 transition">
                    <span class="w-8 h-8 rounded-lg bg-blue-500/10 flex items-center justify-center">
                        <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </span>
                    <div class="flex-1">
                        <p class="text-sm text-gray-200">{{ $kpis['installations_pending'] }} installation(s) à planifier</p>
                    </div>
                    <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
                @endif
            </div>
        </div>
    </div>
</div>
