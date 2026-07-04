<div class="space-y-6" wire:poll.30s>
    {{-- Filters bar --}}
    <div class="flex flex-wrap items-center gap-3">
        <select wire:model.live="filterCity"
                class="bg-gray-900 border border-gray-700 rounded-xl px-3 py-2 text-sm text-gray-300 focus:border-orange-500 focus:ring-1 focus:ring-orange-500/30">
            <option value="">Toutes les villes</option>
            <option value="Abidjan">Abidjan</option>
            <option value="Bouaké">Bouaké</option>
            <option value="Yamoussoukro">Yamoussoukro</option>
            <option value="San Pedro">San Pedro</option>
            <option value="Daloa">Daloa</option>
        </select>

        <select wire:model.live="filterSource"
                class="bg-gray-900 border border-gray-700 rounded-xl px-3 py-2 text-sm text-gray-300 focus:border-orange-500 focus:ring-1 focus:ring-orange-500/30">
            <option value="">Toutes sources</option>
            @foreach(\App\Enums\Crm\LeadSource::cases() as $source)
                <option value="{{ $source->value }}">{{ $source->label() }}</option>
            @endforeach
        </select>

        <button wire:click="$dispatch('open-lead-form')"
                class="ml-auto px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium rounded-xl transition-all shadow-lg shadow-orange-500/20 hover:shadow-orange-500/40 active:scale-95">
            + Nouveau lead
        </button>
    </div>

    {{-- Kanban Board --}}
    <div class="flex gap-4 overflow-x-auto pb-4 -mx-4 px-4 snap-x snap-mandatory lg:snap-none"
         x-data="kanbanBoard()"
         x-init="init()">
        @foreach($this->columns as $column)
        <div class="flex-shrink-0 w-72 snap-start" data-status="{{ $column['status']->value }}">
            {{-- Column header --}}
            <div class="flex items-center justify-between mb-3 px-1">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full bg-{{ $column['status']->color() }}-500"></span>
                    <h3 class="text-sm font-semibold text-gray-200">{{ $column['status']->label() }}</h3>
                </div>
                <span class="text-xs font-medium text-gray-500 bg-gray-800 px-2 py-0.5 rounded-full">
                    {{ $column['leads']->count() }}
                </span>
            </div>

            {{-- Cards container --}}
            <div class="space-y-2.5 min-h-[200px] p-2 rounded-2xl bg-gray-900/50 border border-gray-800/50"
                 data-status="{{ $column['status']->value }}"
                 x-on:dragover.prevent="onDragOver($event)"
                 x-on:drop="onDrop($event, '{{ $column['status']->value }}')">
                @foreach($column['leads'] as $lead)
                <div class="group relative p-3.5 bg-gray-900 rounded-xl border border-gray-800/80 hover:border-gray-700 transition-all duration-150 cursor-grab active:cursor-grabbing shadow-sm hover:shadow-md hover:shadow-black/20"
                     draggable="true"
                     x-on:dragstart="onDragStart($event, {{ $lead->id }})"
                     data-lead-id="{{ $lead->id }}"
                     wire:key="lead-{{ $lead->id }}">
                    {{-- Lead card content --}}
                    <div class="flex items-start justify-between gap-2 mb-2">
                        <h4 class="text-sm font-medium text-gray-100 truncate">{{ $lead->restaurant_name }}</h4>
                        <span class="shrink-0 w-5 h-5 rounded-full bg-{{ $lead->source->value === 'referral' ? 'purple' : 'gray' }}-500/20 flex items-center justify-center">
                            <span class="text-[10px]">{{ substr($lead->source->label(), 0, 1) }}</span>
                        </span>
                    </div>

                    @if($lead->manager_name)
                    <p class="text-xs text-gray-500 mb-1.5">{{ $lead->manager_name }}</p>
                    @endif

                    <div class="flex items-center justify-between mt-3">
                        <div class="flex items-center gap-1.5">
                            @if($lead->phone)
                            <a href="tel:{{ $lead->phone }}" class="text-gray-500 hover:text-emerald-400 transition" onclick="event.stopPropagation()">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                            </a>
                            @endif
                            @if($lead->city)
                            <span class="text-[10px] text-gray-600 bg-gray-800 px-1.5 py-0.5 rounded">{{ $lead->city }}</span>
                            @endif
                        </div>

                        <div class="flex items-center gap-1">
                            @if($lead->days_in_pipeline > 7)
                            <span class="text-[10px] text-amber-500">{{ $lead->days_in_pipeline }}j</span>
                            @endif
                            @if($lead->assignedUser)
                            <img src="{{ $lead->assignedUser->avatar_url }}" class="w-5 h-5 rounded-md object-cover border border-gray-700" title="{{ $lead->assignedUser->name }}">
                            @endif
                        </div>
                    </div>

                    {{-- Score indicator --}}
                    @if($lead->score > 0)
                    <div class="absolute top-3 right-3 opacity-0 group-hover:opacity-100 transition">
                        <span class="text-[10px] font-bold {{ $lead->score >= 70 ? 'text-emerald-400' : ($lead->score >= 40 ? 'text-amber-400' : 'text-gray-500') }}">
                            {{ $lead->score }}%
                        </span>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>

    {{-- Lead form modal --}}
    @livewire('crm.lead-form')
</div>

@script
<script>
Alpine.data('kanbanBoard', () => ({
    draggedId: null,

    init() {},

    onDragStart(event, leadId) {
        this.draggedId = leadId;
        event.dataTransfer.effectAllowed = 'move';
        event.target.classList.add('opacity-50', 'scale-95');
    },

    onDragOver(event) {
        event.dataTransfer.dropEffect = 'move';
        event.currentTarget.classList.add('ring-2', 'ring-orange-500/30', 'bg-orange-500/5');
    },

    onDrop(event, newStatus) {
        event.currentTarget.classList.remove('ring-2', 'ring-orange-500/30', 'bg-orange-500/5');
        document.querySelectorAll('.opacity-50').forEach(el => el.classList.remove('opacity-50', 'scale-95'));

        if (this.draggedId) {
            $wire.moveToStatus(this.draggedId, newStatus);
            this.draggedId = null;
        }
    }
}));
</script>
@endscript
