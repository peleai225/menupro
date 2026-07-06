<div class="space-y-6">
    {{-- Period selector --}}
    <div class="flex items-center gap-2">
        @foreach(['week' => 'Semaine', 'month' => 'Mois', 'quarter' => 'Trimestre'] as $key => $label)
        <button wire:click="$set('period', '{{ $key }}')"
                class="px-3 py-1.5 text-xs font-medium rounded-lg transition
                       {{ $period === $key ? 'bg-orange-500/10 text-orange-400 border border-orange-500/20' : 'text-gray-500 hover:text-gray-300 border border-transparent' }}">
            {{ $label }}
        </button>
        @endforeach
    </div>

    {{-- Stats grid --}}
    @php $stats = $this->stats; @endphp
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3">
        <div class="p-4 rounded-xl bg-gray-900 border border-gray-800/50">
            <p class="text-2xl font-bold text-white tabular-nums">{{ $stats['total_leads'] }}</p>
            <p class="text-xs text-gray-500">Leads créés</p>
        </div>
        <div class="p-4 rounded-xl bg-gray-900 border border-gray-800/50">
            <p class="text-2xl font-bold text-emerald-400 tabular-nums">{{ $stats['converted'] }}</p>
            <p class="text-xs text-gray-500">Convertis</p>
        </div>
        <div class="p-4 rounded-xl bg-gray-900 border border-gray-800/50">
            <p class="text-2xl font-bold text-sky-400 tabular-nums">{{ $stats['in_pipeline'] }}</p>
            <p class="text-xs text-gray-500">En pipeline</p>
        </div>
        <div class="p-4 rounded-xl bg-gray-900 border border-gray-800/50">
            <p class="text-2xl font-bold text-red-400 tabular-nums">{{ $stats['lost'] }}</p>
            <p class="text-xs text-gray-500">Perdus</p>
        </div>
        <div class="p-4 rounded-xl bg-gray-900 border border-gray-800/50">
            <p class="text-2xl font-bold text-amber-400 tabular-nums">{{ $stats['conversion_rate'] }}%</p>
            <p class="text-xs text-gray-500">Taux conversion</p>
        </div>
    </div>

    {{-- Weekly activity chart --}}
    <div class="rounded-2xl bg-gray-900 border border-gray-800/50 p-6">
        <h3 class="text-sm font-semibold text-gray-300 mb-4">Activité des 7 derniers jours</h3>
        <div class="flex items-end gap-2 h-40">
            @foreach($this->weeklyData as $day)
            <div class="flex-1 flex flex-col items-center gap-1">
                <div class="w-full flex flex-col justify-end h-28 gap-0.5">
                    @if($day['leads'] > 0)
                    <div class="w-full bg-sky-500/60 rounded-t transition-all duration-500"
                         style="height: {{ min(100, ($day['leads'] / max(1, collect($this->weeklyData)->max('leads'))) * 100) }}%"></div>
                    @endif
                    @if($day['converted'] > 0)
                    <div class="w-full bg-emerald-500/60 rounded-b transition-all duration-500"
                         style="height: {{ min(50, ($day['converted'] / max(1, collect($this->weeklyData)->max('leads'))) * 100) }}%"></div>
                    @endif
                </div>
                <span class="text-[10px] text-gray-500">{{ $day['label'] }}</span>
            </div>
            @endforeach
        </div>
        <div class="flex items-center gap-4 mt-4">
            <span class="flex items-center gap-1.5 text-xs text-gray-500"><span class="w-2.5 h-2.5 rounded bg-sky-500/60"></span> Leads</span>
            <span class="flex items-center gap-1.5 text-xs text-gray-500"><span class="w-2.5 h-2.5 rounded bg-emerald-500/60"></span> Convertis</span>
        </div>
    </div>

    {{-- Objectif mensuel individuel --}}
    @php
        $target = $this->monthlyTarget;
        $converted = $this->stats['converted'];
        $progress = $target > 0 ? min(100, (int) round(($converted / $target) * 100)) : 0;
    @endphp
    <div class="rounded-2xl bg-gray-900 border border-gray-800/50 p-6">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-semibold text-gray-300">Objectif du mois</h3>
            <span class="text-xs font-medium {{ $progress >= 100 ? 'text-emerald-400' : 'text-gray-400' }}">
                {{ $converted }} / {{ $target }} conversions
            </span>
        </div>
        <div class="h-3 rounded-full bg-gray-800 overflow-hidden">
            <div class="h-full rounded-full transition-all duration-1000 ease-out
                        {{ $progress >= 100 ? 'bg-emerald-500' : ($progress >= 70 ? 'bg-orange-500' : 'bg-sky-500') }}"
                 style="width: {{ $progress }}%"></div>
        </div>
        <p class="text-xs text-gray-500 mt-2">{{ $progress }}% de l'objectif atteint ce mois-ci</p>
    </div>

    {{-- Grade progression --}}
    @if($this->grade)
    <div class="rounded-2xl bg-gray-900 border border-gray-800/50 p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-gray-300">Grade</h3>
            <span class="px-2.5 py-1 text-xs font-bold rounded-lg bg-{{ $this->grade->current_grade->color() }}-500/10 text-{{ $this->grade->current_grade->color() }}-400 border border-{{ $this->grade->current_grade->color() }}-500/20">
                {{ $this->grade->current_grade->title() }}
            </span>
        </div>
        <div class="space-y-2">
            <div class="flex items-center justify-between text-xs text-gray-500">
                <span>{{ $this->grade->total_conversions }} conversions</span>
                <span>{{ $this->grade->progress_to_next }}%</span>
            </div>
            <div class="h-3 rounded-full bg-gray-800 overflow-hidden">
                <div class="h-full rounded-full bg-gradient-to-r from-{{ $this->grade->current_grade->color() }}-500 to-{{ $this->grade->current_grade->color() }}-400 transition-all duration-1000 ease-out"
                     style="width: {{ $this->grade->progress_to_next }}%"></div>
            </div>
        </div>
    </div>
    @endif
</div>
