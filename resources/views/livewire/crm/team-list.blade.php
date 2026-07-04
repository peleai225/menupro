<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-white">Équipes</h2>
            <p class="text-xs text-gray-500 mt-0.5">Performance et composition des équipes terrain</p>
        </div>
    </div>

    {{-- Teams Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
        @forelse($this->teams as $team)
        <div class="bg-gray-900 rounded-2xl border border-gray-800/60 p-5 hover:border-gray-700 transition-all"
             wire:key="team-{{ $team->id }}">
            {{-- Team Header --}}
            <div class="flex items-center justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl bg-orange-500/10 flex items-center justify-center">
                        <svg class="w-5 h-5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-100">{{ $team->name }}</h3>
                        @if($team->zone)
                        <p class="text-xs text-gray-500">{{ $team->zone }}</p>
                        @endif
                    </div>
                </div>
                <span class="text-xs px-2 py-1 rounded-full bg-gray-800 text-gray-400 border border-gray-700">
                    {{ $team->members->count() }} membres
                </span>
            </div>

            {{-- Leader --}}
            @if($team->leader)
            <div class="flex items-center gap-2 mb-4 px-3 py-2 rounded-xl bg-gray-800/50 border border-gray-700/50">
                <img src="{{ $team->leader->avatar_url }}" class="w-6 h-6 rounded-lg object-cover" alt="">
                <span class="text-xs text-gray-300">{{ $team->leader->name }}</span>
                <span class="text-[10px] px-1.5 py-0.5 rounded bg-violet-500/10 text-violet-400 border border-violet-500/30 ml-auto">Chef</span>
            </div>
            @endif

            {{-- Progress --}}
            <div class="mb-4">
                <div class="flex items-center justify-between text-xs mb-1.5">
                    <span class="text-gray-500">Objectif mensuel</span>
                    <span class="text-gray-300 font-medium">{{ $team->converted_count }} / {{ $team->monthly_target }}</span>
                </div>
                <div class="h-2 bg-gray-800 rounded-full overflow-hidden">
                    @php $progress = $team->monthly_target > 0 ? min(100, round(($team->converted_count / $team->monthly_target) * 100)) : 0; @endphp
                    <div class="h-full rounded-full transition-all duration-500 {{ $progress >= 80 ? 'bg-emerald-500' : ($progress >= 50 ? 'bg-amber-500' : 'bg-orange-500') }}"
                         style="width: {{ $progress }}%"></div>
                </div>
            </div>

            {{-- Stats Row --}}
            <div class="grid grid-cols-3 gap-3">
                <div class="text-center p-2.5 rounded-xl bg-gray-800/30">
                    <p class="text-lg font-bold text-white tabular-nums">{{ $team->total_leads_count }}</p>
                    <p class="text-[10px] text-gray-500">Total leads</p>
                </div>
                <div class="text-center p-2.5 rounded-xl bg-gray-800/30">
                    <p class="text-lg font-bold text-amber-400 tabular-nums">{{ $team->active_leads_count }}</p>
                    <p class="text-[10px] text-gray-500">Pipeline</p>
                </div>
                <div class="text-center p-2.5 rounded-xl bg-gray-800/30">
                    <p class="text-lg font-bold text-emerald-400 tabular-nums">{{ $team->converted_count }}</p>
                    <p class="text-[10px] text-gray-500">Conversions</p>
                </div>
            </div>

            {{-- Members Preview --}}
            @if($team->members->count() > 0)
            <div class="mt-4 pt-4 border-t border-gray-800/50">
                <div class="flex items-center -space-x-2">
                    @foreach($team->members->take(6) as $member)
                    <img src="{{ $member->avatar_url }}"
                         class="w-7 h-7 rounded-lg border-2 border-gray-900 object-cover"
                         title="{{ $member->name }}"
                         alt="{{ $member->name }}">
                    @endforeach
                    @if($team->members->count() > 6)
                    <span class="w-7 h-7 rounded-lg border-2 border-gray-900 bg-gray-800 flex items-center justify-center text-[10px] text-gray-400 font-medium">
                        +{{ $team->members->count() - 6 }}
                    </span>
                    @endif
                </div>
            </div>
            @endif
        </div>
        @empty
        <div class="col-span-full text-center py-16">
            <svg class="w-16 h-16 mx-auto text-gray-700 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <p class="text-gray-500 text-sm">Aucune équipe active</p>
        </div>
        @endforelse
    </div>
</div>
