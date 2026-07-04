<div x-data="{
    chartInstance: null,
    initChart() {
        const ctx = this.$refs.activityChart?.getContext('2d');
        if (!ctx) return;
        if (this.chartInstance) this.chartInstance.destroy();

        const data = $wire.chartData;
        this.chartInstance = new Chart(ctx, {
            type: 'line',
            data: {
                labels: data.labels,
                datasets: [
                    {
                        label: 'Leads',
                        data: data.leads,
                        borderColor: '#38bdf8',
                        backgroundColor: 'rgba(56,189,248,0.08)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 3,
                        pointHoverRadius: 6,
                    },
                    {
                        label: 'Conversions',
                        data: data.conversions,
                        borderColor: '#34d399',
                        backgroundColor: 'rgba(52,211,153,0.08)',
                        fill: true,
                        tension: 0.4,
                        pointRadius: 3,
                        pointHoverRadius: 6,
                    },
                    {
                        label: 'Revenus (FCFA)',
                        data: data.revenue,
                        borderColor: '#fbbf24',
                        backgroundColor: 'rgba(251,191,36,0.05)',
                        fill: false,
                        tension: 0.4,
                        pointRadius: 2,
                        yAxisID: 'y1',
                    }
                ]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: { mode: 'index', intersect: false },
                plugins: {
                    legend: { position: 'top', labels: { color: '#9ca3af', font: { size: 11 }, usePointStyle: true, pointStyle: 'circle' } },
                    tooltip: { backgroundColor: '#1f2937', titleColor: '#f3f4f6', bodyColor: '#d1d5db', borderColor: '#374151', borderWidth: 1 }
                },
                scales: {
                    x: { grid: { color: 'rgba(75,85,99,0.2)' }, ticks: { color: '#6b7280', font: { size: 10 } } },
                    y: { position: 'left', grid: { color: 'rgba(75,85,99,0.2)' }, ticks: { color: '#6b7280', font: { size: 10 } }, beginAtZero: true },
                    y1: { position: 'right', grid: { drawOnChartArea: false }, ticks: { color: '#fbbf24', font: { size: 10 } }, beginAtZero: true }
                }
            }
        });
    }
}"
x-init="$nextTick(() => initChart())"
@period-changed.window="$nextTick(() => initChart())"
wire:poll.30s
class="space-y-6">

    {{-- Header + Period Filter --}}
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
        <div>
            <h1 class="text-xl font-bold text-white">Dashboard CRM</h1>
            <p class="text-sm text-gray-500 mt-0.5">Vue d'ensemble de l'activité commerciale</p>
        </div>
        <div class="flex items-center gap-1 p-1 rounded-xl bg-gray-800/50 border border-gray-700/50">
            @foreach(['week' => 'Semaine', 'month' => 'Mois', 'quarter' => 'Trimestre', 'year' => 'Année'] as $key => $label)
                <button wire:click="setPeriod('{{ $key }}')"
                        @click="$dispatch('period-changed')"
                        class="px-3 py-1.5 text-xs font-medium rounded-lg transition-all duration-200
                            {{ $period === $key ? 'bg-white/10 text-white shadow-sm' : 'text-gray-400 hover:text-gray-200' }}">
                    {{ $label }}
                </button>
            @endforeach
        </div>
    </div>

    {{-- KPI Cards with animated counters --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 xl:grid-cols-5 gap-4" wire:loading.class="opacity-60">
        @php $kpis = $this->kpis; @endphp

        <div class="p-5 rounded-2xl bg-gradient-to-br from-sky-500/10 to-sky-600/5 border border-sky-500/20 hover:border-sky-400/40 transition-all duration-300 group">
            <div class="flex items-center justify-between mb-3">
                <span class="w-10 h-10 rounded-xl bg-sky-500/15 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-5 h-5 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </span>
                <span class="text-xs text-sky-400/70 font-medium">{{ $period === 'week' ? 'cette sem.' : ($period === 'month' ? 'ce mois' : ($period === 'quarter' ? 'ce trim.' : 'cette année')) }}</span>
            </div>
            <p class="text-2xl font-bold text-white tabular-nums">{{ number_format($kpis['total_leads']) }}</p>
            <p class="text-xs text-gray-500 mt-1">Nouveaux leads</p>
        </div>

        <div class="p-5 rounded-2xl bg-gradient-to-br from-emerald-500/10 to-emerald-600/5 border border-emerald-500/20 hover:border-emerald-400/40 transition-all duration-300 group">
            <div class="flex items-center justify-between mb-3">
                <span class="w-10 h-10 rounded-xl bg-emerald-500/15 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
                <span class="px-2 py-0.5 text-[10px] font-bold rounded-full {{ $kpis['conversion_rate'] >= 20 ? 'bg-emerald-500/20 text-emerald-400' : 'bg-amber-500/20 text-amber-400' }}">
                    {{ $kpis['conversion_rate'] }}%
                </span>
            </div>
            <p class="text-2xl font-bold text-white tabular-nums">{{ number_format($kpis['converted']) }}</p>
            <p class="text-xs text-gray-500 mt-1">Convertis</p>
        </div>

        <div class="p-5 rounded-2xl bg-gradient-to-br from-amber-500/10 to-amber-600/5 border border-amber-500/20 hover:border-amber-400/40 transition-all duration-300 group">
            <div class="flex items-center justify-between mb-3">
                <span class="w-10 h-10 rounded-xl bg-amber-500/15 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
            </div>
            <p class="text-2xl font-bold text-white tabular-nums">{{ number_format($kpis['revenue_cents'] / 100, 0, ',', ' ') }}</p>
            <p class="text-xs text-gray-500 mt-1">FCFA revenus</p>
        </div>

        <div class="p-5 rounded-2xl bg-gradient-to-br from-violet-500/10 to-violet-600/5 border border-violet-500/20 hover:border-violet-400/40 transition-all duration-300 group">
            <div class="flex items-center justify-between mb-3">
                <span class="w-10 h-10 rounded-xl bg-violet-500/15 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-5 h-5 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </span>
            </div>
            <p class="text-2xl font-bold text-white tabular-nums">{{ $kpis['active_agents'] }}</p>
            <p class="text-xs text-gray-500 mt-1">Agents actifs</p>
        </div>

        <div class="p-5 rounded-2xl bg-gradient-to-br from-orange-500/10 to-orange-600/5 border border-orange-500/20 hover:border-orange-400/40 transition-all duration-300 group">
            <div class="flex items-center justify-between mb-3">
                <span class="w-10 h-10 rounded-xl bg-orange-500/15 flex items-center justify-center group-hover:scale-110 transition-transform duration-300">
                    <svg class="w-5 h-5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                </span>
            </div>
            <p class="text-2xl font-bold text-white tabular-nums">{{ $kpis['installations_done'] }}</p>
            <p class="text-xs text-gray-500 mt-1">Installations finies</p>
        </div>
    </div>

    {{-- Activity Chart --}}
    <div class="rounded-2xl bg-gray-900/80 border border-gray-800/50 p-6 backdrop-blur">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-gray-300">Activité</h3>
            <div class="flex items-center gap-4 text-[10px] text-gray-500">
                <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-sky-400"></span> Leads</span>
                <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-emerald-400"></span> Conversions</span>
                <span class="flex items-center gap-1"><span class="w-2 h-2 rounded-full bg-amber-400"></span> Revenus</span>
            </div>
        </div>
        <div class="h-64 relative">
            <canvas x-ref="activityChart"></canvas>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Pipeline Funnel --}}
        <div class="lg:col-span-2 rounded-2xl bg-gray-900/80 border border-gray-800/50 p-6 backdrop-blur">
            <h3 class="text-sm font-semibold text-gray-300 mb-5">Pipeline — Entonnoir de conversion</h3>
            <div class="space-y-3">
                @php $maxCount = max(1, collect($this->funnel)->max('count')); @endphp
                @foreach($this->funnel as $i => $item)
                <div class="flex items-center gap-3 group" x-data="{ show: false }" x-init="setTimeout(() => show = true, {{ $i * 80 }})" x-show="show" x-transition:enter="transition ease-out duration-500" x-transition:enter-start="opacity-0 translate-x-4" x-transition:enter-end="opacity-100 translate-x-0">
                    <span class="w-28 text-xs text-gray-400 text-right truncate">{{ $item['status']->label() }}</span>
                    <div class="flex-1 h-9 rounded-xl bg-gray-800/60 overflow-hidden relative">
                        <div class="h-full rounded-xl transition-all duration-1000 ease-out"
                             style="width: {{ ($item['count'] / $maxCount) * 100 }}%; background: linear-gradient(135deg, {{ $item['status']->color() }}40, {{ $item['status']->color() }}20);">
                        </div>
                        <span class="absolute inset-y-0 left-3 flex items-center text-xs font-bold text-white/90">
                            {{ $item['count'] }}
                        </span>
                        <span class="absolute inset-y-0 right-3 flex items-center text-[10px] text-gray-500 opacity-0 group-hover:opacity-100 transition">
                            {{ $maxCount > 0 ? round(($item['count'] / $maxCount) * 100) : 0 }}%
                        </span>
                    </div>
                </div>
                @endforeach
            </div>
        </div>

        {{-- Top Performers --}}
        <div class="rounded-2xl bg-gray-900/80 border border-gray-800/50 p-6 backdrop-blur">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-sm font-semibold text-gray-300">Top Performers</h3>
                <span class="text-[10px] text-gray-500 uppercase tracking-wider">conversions</span>
            </div>
            <div class="space-y-3">
                @forelse($this->topPerformers->take(7) as $performer)
                <div class="flex items-center gap-3 p-2 rounded-xl hover:bg-white/5 transition-colors duration-200">
                    <span class="w-6 h-6 rounded-full flex items-center justify-center text-[10px] font-bold
                        {{ $loop->index === 0 ? 'bg-amber-500/20 text-amber-400' : ($loop->index === 1 ? 'bg-gray-400/20 text-gray-300' : ($loop->index === 2 ? 'bg-orange-500/20 text-orange-400' : 'bg-gray-800 text-gray-500')) }}">
                        {{ $loop->iteration }}
                    </span>
                    <img src="{{ $performer->avatar_url }}" class="w-8 h-8 rounded-xl object-cover ring-2 ring-gray-800" alt="">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-gray-200 truncate font-medium">{{ $performer->name }}</p>
                    </div>
                    <div class="flex items-center gap-1.5">
                        <span class="text-sm font-bold text-emerald-400 tabular-nums">{{ $performer->conversions_count }}</span>
                        <svg class="w-3.5 h-3.5 text-emerald-400/60" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                    </div>
                </div>
                @empty
                <p class="text-sm text-gray-500 text-center py-4">Aucun agent actif</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Teams + Recent Activity + Alerts --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Teams --}}
        <div class="rounded-2xl bg-gray-900/80 border border-gray-800/50 p-6 backdrop-blur">
            <h3 class="text-sm font-semibold text-gray-300 mb-5">Equipes</h3>
            <div class="space-y-3">
                @forelse($this->teams as $team)
                <div class="p-3.5 rounded-xl border border-gray-800/60 hover:border-gray-700/80 transition-all duration-200 hover:bg-white/[0.02]">
                    <div class="flex items-center justify-between mb-2.5">
                        <div>
                            <span class="text-sm font-medium text-gray-200">{{ $team->name }}</span>
                            @if($team->leader)
                            <span class="text-[10px] text-gray-500 ml-2">{{ $team->leader->name }}</span>
                            @endif
                        </div>
                        <span class="text-[10px] px-2 py-0.5 rounded-full bg-gray-800 text-gray-400">{{ $team->zone }}</span>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="flex-1 h-2 rounded-full bg-gray-800 overflow-hidden">
                            @php $progress = $team->monthly_target > 0 ? min(100, round(($team->converted_count / $team->monthly_target) * 100)) : 0; @endphp
                            <div class="h-full rounded-full transition-all duration-700 {{ $progress >= 80 ? 'bg-gradient-to-r from-emerald-500 to-emerald-400' : ($progress >= 50 ? 'bg-gradient-to-r from-amber-500 to-amber-400' : 'bg-gradient-to-r from-orange-500 to-orange-400') }}"
                                 style="width: {{ $progress }}%"></div>
                        </div>
                        <span class="text-xs text-gray-400 tabular-nums font-medium">{{ $team->converted_count }}/{{ $team->monthly_target }}</span>
                    </div>
                </div>
                @empty
                <p class="text-sm text-gray-500 text-center py-4">Aucune equipe</p>
                @endforelse
            </div>
        </div>

        {{-- Recent Activity --}}
        <div class="rounded-2xl bg-gray-900/80 border border-gray-800/50 p-6 backdrop-blur">
            <h3 class="text-sm font-semibold text-gray-300 mb-5">Derniers leads</h3>
            <div class="space-y-2">
                @forelse($this->recentActivity as $lead)
                <div class="flex items-center gap-3 p-2.5 rounded-xl hover:bg-white/5 transition-colors">
                    <span class="w-2 h-2 rounded-full flex-shrink-0" style="background: {{ $lead->status->color() }}"></span>
                    <div class="flex-1 min-w-0">
                        <p class="text-sm text-gray-200 truncate">{{ $lead->restaurant_name }}</p>
                        <p class="text-[10px] text-gray-500">{{ $lead->assignedUser?->name ?? 'Non assigné' }} · {{ $lead->created_at->diffForHumans() }}</p>
                    </div>
                    <span class="px-2 py-0.5 text-[9px] font-medium rounded-full" style="background: {{ $lead->status->color() }}20; color: {{ $lead->status->color() }}">
                        {{ $lead->status->label() }}
                    </span>
                </div>
                @empty
                <p class="text-sm text-gray-500 text-center py-4">Aucun lead</p>
                @endforelse
            </div>
        </div>

        {{-- Alerts & Actions --}}
        <div class="rounded-2xl bg-gray-900/80 border border-gray-800/50 p-6 backdrop-blur">
            <h3 class="text-sm font-semibold text-gray-300 mb-5">Actions requises</h3>
            <div class="space-y-3">
                @if($kpis['pending_withdrawals'] > 0)
                <a href="{{ route('crm.admin.withdrawals') }}"
                   class="flex items-center gap-3 p-3.5 rounded-xl border border-amber-500/20 bg-amber-500/5 hover:bg-amber-500/10 transition-all duration-200 group">
                    <span class="w-9 h-9 rounded-xl bg-amber-500/15 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <svg class="w-4.5 h-4.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    </span>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-200">{{ $kpis['pending_withdrawals'] }} retrait(s)</p>
                        <p class="text-[10px] text-gray-500">En attente de validation</p>
                    </div>
                    <svg class="w-4 h-4 text-gray-600 group-hover:text-gray-400 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
                @endif

                @if($kpis['installations_pending'] > 0)
                <a href="{{ route('crm.installations.index') }}"
                   class="flex items-center gap-3 p-3.5 rounded-xl border border-blue-500/20 bg-blue-500/5 hover:bg-blue-500/10 transition-all duration-200 group">
                    <span class="w-9 h-9 rounded-xl bg-blue-500/15 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <svg class="w-4.5 h-4.5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </span>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-200">{{ $kpis['installations_pending'] }} installation(s)</p>
                        <p class="text-[10px] text-gray-500">A planifier</p>
                    </div>
                    <svg class="w-4 h-4 text-gray-600 group-hover:text-gray-400 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
                @endif

                @if($kpis['in_pipeline'] > 0)
                <a href="{{ route('crm.leads.index') }}"
                   class="flex items-center gap-3 p-3.5 rounded-xl border border-sky-500/20 bg-sky-500/5 hover:bg-sky-500/10 transition-all duration-200 group">
                    <span class="w-9 h-9 rounded-xl bg-sky-500/15 flex items-center justify-center group-hover:scale-110 transition-transform">
                        <svg class="w-4.5 h-4.5 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    </span>
                    <div class="flex-1">
                        <p class="text-sm font-medium text-gray-200">{{ $kpis['in_pipeline'] }} leads actifs</p>
                        <p class="text-[10px] text-gray-500">Dans le pipeline</p>
                    </div>
                    <svg class="w-4 h-4 text-gray-600 group-hover:text-gray-400 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
                @endif

                @if($kpis['pending_withdrawals'] === 0 && $kpis['installations_pending'] === 0)
                <div class="text-center py-8">
                    <span class="w-12 h-12 mx-auto rounded-2xl bg-emerald-500/10 flex items-center justify-center mb-3">
                        <svg class="w-6 h-6 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                    </span>
                    <p class="text-sm text-gray-400">Tout est a jour</p>
                </div>
                @endif
            </div>
        </div>
    </div>
</div>

{{-- Chart.js CDN --}}
@script
<script>
    if (!document.querySelector('script[src*="chart.js"]')) {
        const s = document.createElement('script');
        s.src = 'https://cdn.jsdelivr.net/npm/chart.js@4/dist/chart.umd.min.js';
        s.onload = () => $wire.dispatch('period-changed');
        document.head.appendChild(s);
    }
</script>
@endscript
