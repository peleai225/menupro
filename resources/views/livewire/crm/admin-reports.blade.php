<div class="space-y-6">
    {{-- Stats row --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-4">
            <p class="text-xs text-gray-400 uppercase tracking-wider">Soumis aujourd'hui</p>
            <p class="text-2xl font-bold text-white mt-1">{{ $this->stats['today_submitted'] }}</p>
        </div>
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-4">
            <p class="text-xs text-gray-400 uppercase tracking-wider">Taux soumission</p>
            <p class="text-2xl font-bold text-orange-400 mt-1">{{ $this->stats['submission_rate'] }}%</p>
            <p class="text-[10px] text-gray-500">{{ $this->stats['today_submitted'] }}/{{ $this->stats['active_agents'] }} agents</p>
        </div>
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-4">
            <p class="text-xs text-gray-400 uppercase tracking-wider">En attente review</p>
            <p class="text-2xl font-bold text-amber-400 mt-1">{{ $this->stats['pending_review'] }}</p>
        </div>
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-4">
            <p class="text-xs text-gray-400 uppercase tracking-wider">Sans rapport</p>
            <p class="text-2xl font-bold text-red-400 mt-1">{{ $this->agentsWithoutReport->count() }}</p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="flex flex-col sm:flex-row gap-3">
        <input type="text" wire:model.live.debounce.300ms="filterAgent" placeholder="Rechercher un agent..."
               class="flex-1 bg-gray-900 border border-gray-800 rounded-xl px-4 py-2.5 text-sm text-white placeholder-gray-500 focus:border-orange-500 focus:ring-1 focus:ring-orange-500/50 transition">
        <input type="date" wire:model.live="filterDate"
               class="bg-gray-900 border border-gray-800 rounded-xl px-4 py-2.5 text-sm text-white focus:border-orange-500 focus:ring-1 focus:ring-orange-500/50 transition">
        <select wire:model.live="filterStatus"
                class="bg-gray-900 border border-gray-800 rounded-xl px-4 py-2.5 text-sm text-white focus:border-orange-500 focus:ring-1 focus:ring-orange-500/50 transition">
            <option value="">Tous</option>
            <option value="pending">En attente</option>
            <option value="reviewed">Valides</option>
        </select>
    </div>

    {{-- Reports list --}}
    <div class="space-y-3">
        @forelse($this->reports as $report)
            <div class="bg-gray-900 border border-gray-800 rounded-2xl overflow-hidden transition hover:border-gray-700">
                {{-- Report row --}}
                <div class="flex items-center gap-3 p-4 cursor-pointer" wire:click="toggleExpand({{ $report->id }})">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 flex-wrap">
                            <span class="text-sm font-medium text-white">{{ $report->user->name ?? 'Agent' }}</span>
                            <span class="text-xs text-gray-500">{{ $report->report_date->format('d/m/Y') }}</span>
                        </div>
                        <div class="flex items-center gap-3 mt-1 text-xs text-gray-400">
                            <span>{{ $report->visits_count }} visites</span>
                            <span>{{ $report->demos_count }} demos</span>
                            @if($report->zone_covered)
                                <span class="text-gray-500">{{ $report->zone_covered }}</span>
                            @endif
                        </div>
                    </div>

                    <div class="flex items-center gap-2 shrink-0">
                        @if($report->is_reviewed)
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-full text-[10px] font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                                Vu
                            </span>
                        @else
                            <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-medium bg-amber-500/10 text-amber-400 border border-amber-500/20">
                                En attente
                            </span>
                        @endif
                        <svg class="w-4 h-4 text-gray-500 transition {{ $expandedReport === $report->id ? 'rotate-180' : '' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                    </div>
                </div>

                {{-- Expanded details --}}
                @if($expandedReport === $report->id)
                    <div class="border-t border-gray-800 p-4 bg-gray-950/50 space-y-3">
                        <div class="grid grid-cols-2 sm:grid-cols-4 gap-3">
                            <div>
                                <p class="text-[10px] text-gray-500 uppercase">Visites</p>
                                <p class="text-sm font-semibold text-white">{{ $report->visits_count }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] text-gray-500 uppercase">Leads</p>
                                <p class="text-sm font-semibold text-orange-400">{{ $report->new_leads_count }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] text-gray-500 uppercase">Demos</p>
                                <p class="text-sm font-semibold text-white">{{ $report->demos_count }}</p>
                            </div>
                            <div>
                                <p class="text-[10px] text-gray-500 uppercase">Conversions</p>
                                <p class="text-sm font-semibold text-emerald-400">{{ $report->conversions_count }}</p>
                            </div>
                        </div>

                        @if($report->zone_covered)
                            <div>
                                <p class="text-[10px] text-gray-500 uppercase mb-0.5">Zone</p>
                                <p class="text-sm text-gray-300">{{ $report->zone_covered }}</p>
                            </div>
                        @endif

                        @if($report->obstacles)
                            <div>
                                <p class="text-[10px] text-gray-500 uppercase mb-0.5">Obstacles</p>
                                <p class="text-sm text-gray-300">{{ $report->obstacles }}</p>
                            </div>
                        @endif

                        @if($report->notes)
                            <div>
                                <p class="text-[10px] text-gray-500 uppercase mb-0.5">Notes</p>
                                <p class="text-sm text-gray-300">{{ $report->notes }}</p>
                            </div>
                        @endif

                        <div class="flex items-center justify-between pt-2 border-t border-gray-800">
                            <p class="text-[10px] text-gray-500">
                                Soumis {{ $report->submitted_at?->diffForHumans() ?? '-' }}
                            </p>

                            @if(!$report->is_reviewed)
                                <button wire:click="markReviewed({{ $report->id }})"
                                        wire:loading.attr="disabled"
                                        class="px-3 py-1.5 bg-orange-500/10 hover:bg-orange-500/20 text-orange-400 text-xs font-medium rounded-lg border border-orange-500/20 transition">
                                    Marquer comme vu
                                </button>
                            @else
                                <p class="text-[10px] text-emerald-400">
                                    Vu par {{ $report->reviewedBy->name ?? 'admin' }} {{ $report->reviewed_at?->diffForHumans() }}
                                </p>
                            @endif
                        </div>
                    </div>
                @endif
            </div>
        @empty
            <div class="bg-gray-900 border border-gray-800 rounded-2xl p-8 text-center">
                <svg class="w-12 h-12 text-gray-700 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                <p class="text-gray-500 text-sm">Aucun rapport trouve.</p>
            </div>
        @endforelse

        {{-- Pagination --}}
        <div class="mt-4">
            {{ $this->reports->links() }}
        </div>
    </div>

    {{-- Agents without report (collapsible) --}}
    @if($this->agentsWithoutReport->isNotEmpty())
        <div x-data="{ open: false }" class="bg-red-500/5 border border-red-500/20 rounded-2xl overflow-hidden">
            <button @click="open = !open" class="w-full flex items-center justify-between p-4 text-left">
                <div class="flex items-center gap-2">
                    <svg class="w-4 h-4 text-red-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/></svg>
                    <span class="text-sm font-medium text-red-400">{{ $this->agentsWithoutReport->count() }} agents sans rapport aujourd'hui</span>
                </div>
                <svg class="w-4 h-4 text-red-400 transition" :class="open && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
            </button>
            <div x-show="open" x-collapse class="border-t border-red-500/10 px-4 pb-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2 mt-3">
                    @foreach($this->agentsWithoutReport as $agent)
                        <div class="flex items-center gap-2 px-3 py-2 bg-gray-900/50 rounded-xl">
                            <div class="w-2 h-2 rounded-full bg-red-400"></div>
                            <span class="text-sm text-gray-300 truncate">{{ $agent->name }}</span>
                            <span class="text-[10px] text-gray-500 ml-auto">{{ $agent->role }}</span>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
</div>
