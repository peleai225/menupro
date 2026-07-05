<div class="space-y-4 lg:space-y-6"
     wire:poll.30s
     x-data="kanbanBoard()">

    {{-- Poll indicator --}}
    <div wire:loading.delay wire:target="refreshLeads"
         class="fixed top-20 left-1/2 -translate-x-1/2 z-50 px-4 py-2 bg-orange-500/90 text-white text-xs rounded-full shadow-lg backdrop-blur-sm animate-pulse">
        🔄 Actualisation...
    </div>

    {{-- Filters bar - Stacked on mobile, row on desktop --}}
    <div class="flex flex-col sm:flex-row sm:items-center gap-2 sm:gap-3">
        <select wire:model.live="filterCity"
                class="bg-gray-900 border border-gray-700 rounded-xl px-3 py-2 text-sm text-gray-300 focus:border-orange-500 focus:ring-1 focus:ring-orange-500/30 transition">
            <option value="">Toutes les villes</option>
            <option value="Abidjan">Abidjan</option>
            <option value="Bouaké">Bouaké</option>
            <option value="Yamoussoukro">Yamoussoukro</option>
            <option value="San Pedro">San Pedro</option>
            <option value="Daloa">Daloa</option>
        </select>

        <select wire:model.live="filterSource"
                class="bg-gray-900 border border-gray-700 rounded-xl px-3 py-2 text-sm text-gray-300 focus:border-orange-500 focus:ring-1 focus:ring-orange-500/30 transition">
            <option value="">Toutes sources</option>
            @foreach(\App\Enums\Crm\LeadSource::cases() as $source)
                <option value="{{ $source->value }}">{{ $source->label() }}</option>
            @endforeach
        </select>

        @if(in_array(auth()->user()->role->value, ['super_admin', 'team_leader']))
        <select wire:model.live="filterTeam"
                class="bg-gray-900 border border-gray-700 rounded-xl px-3 py-2 text-sm text-gray-300 focus:border-orange-500 focus:ring-1 focus:ring-orange-500/30 transition">
            <option value="">Toutes les équipes</option>
            @foreach(\App\Models\Crm\Team::orderBy('name')->get() as $team)
                <option value="{{ $team->id }}">{{ $team->name }}</option>
            @endforeach
        </select>
        @endif

        <select wire:model.live="filterPlan"
                class="bg-gray-900 border border-gray-700 rounded-xl px-3 py-2 text-sm text-gray-300 focus:border-orange-500 focus:ring-1 focus:ring-orange-500/30 transition">
            <option value="">Tous les plans</option>
            @foreach(\App\Enums\Crm\SubscriptionPlan::cases() as $plan)
                <option value="{{ $plan->value }}">{{ $plan->shortLabel() }}</option>
            @endforeach
        </select>

        {{-- Desktop button (hidden on mobile) --}}
        <button wire:click="$dispatch('open-lead-form')"
                class="hidden lg:inline-flex ml-auto px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium rounded-xl transition-all shadow-lg shadow-orange-500/20 hover:shadow-orange-500/40 active:scale-95">
            + Nouveau lead
        </button>
    </div>

    {{-- Mobile FAB (visible only on mobile) --}}
    <button wire:click="$dispatch('open-lead-form')"
            class="lg:hidden fixed bottom-6 right-6 z-40 w-14 h-14 bg-gradient-to-br from-orange-500 to-orange-600 hover:from-orange-600 hover:to-orange-700 text-white rounded-full shadow-2xl shadow-orange-500/40 flex items-center justify-center active:scale-90 transition-all duration-200">
        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"/>
        </svg>
    </button>

    {{-- DESKTOP VIEW: Horizontal Kanban (lg+) --}}
    <div class="hidden lg:flex gap-4 overflow-x-auto pb-4 -mx-4 px-4"
         x-init="init()">
        @foreach($this->columns as $column)
        <div class="flex-shrink-0 w-80" data-status="{{ $column['status']->value }}">
            {{-- Column header --}}
            <div class="flex items-center justify-between mb-3 px-1">
                <div class="flex items-center gap-2">
                    <span class="w-2.5 h-2.5 rounded-full" style="background: {{ $column['status']->color() }}"></span>
                    <h3 class="text-sm font-semibold text-gray-200">{{ $column['status']->label() }}</h3>
                </div>
                <span class="text-xs font-medium text-gray-500 bg-gray-800 px-2.5 py-1 rounded-full">
                    {{ $column['leads']->count() }}
                </span>
            </div>

            {{-- Cards container --}}
            <div class="space-y-2.5 min-h-[200px] p-2.5 rounded-2xl bg-gray-900/50 border border-gray-800/50 transition-all"
                 data-status="{{ $column['status']->value }}"
                 x-on:dragover.prevent="onDragOver($event)"
                 x-on:drop="onDrop($event, '{{ $column['status']->value }}')"
                 x-on:dragleave="onDragLeave($event)">
                @foreach($column['leads'] as $lead)
                    @include('livewire.crm.partials.lead-card', ['lead' => $lead, 'compact' => false])
                @endforeach
            </div>
        </div>
        @endforeach
    </div>

    {{-- MOBILE VIEW: Vertical grouped list (<lg) --}}
    <div class="lg:hidden space-y-3">
        @foreach($this->columns as $column)
        <div class="bg-gray-900/30 rounded-2xl border border-gray-800/60 overflow-hidden transition-all"
             x-data="{ open: {{ $loop->first ? 'true' : 'false' }} }">
            {{-- Section header - collapsible --}}
            <button @click="open = !open"
                    class="w-full flex items-center justify-between p-4 hover:bg-gray-900/50 transition-colors">
                <div class="flex items-center gap-2.5">
                    <span class="w-3 h-3 rounded-full flex-shrink-0" style="background: {{ $column['status']->color() }}"></span>
                    <h3 class="text-sm font-semibold text-gray-200">{{ $column['status']->label() }}</h3>
                    <span class="text-xs font-medium text-gray-500 bg-gray-800 px-2 py-0.5 rounded-full">
                        {{ $column['leads']->count() }}
                    </span>
                </div>
                <svg class="w-5 h-5 text-gray-500 transition-transform duration-200"
                     :class="open ? 'rotate-180' : ''"
                     fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </button>

            {{-- Cards list --}}
            <div x-show="open"
                 x-transition:enter="transition ease-out duration-200"
                 x-transition:enter-start="opacity-0 -translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 x-transition:leave="transition ease-in duration-150"
                 x-transition:leave-start="opacity-100 translate-y-0"
                 x-transition:leave-end="opacity-0 -translate-y-2"
                 class="px-3 pb-3 space-y-2">
                @forelse($column['leads'] as $lead)
                    @include('livewire.crm.partials.lead-card', ['lead' => $lead, 'compact' => true])
                @empty
                    <p class="text-center text-xs text-gray-600 py-6">Aucun lead</p>
                @endforelse
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

    init() {
        // Optional: Add pull-to-refresh on mobile
        if (window.innerWidth < 1024) {
            this.initPullToRefresh();
        }
    },

    initPullToRefresh() {
        let startY = 0;
        let pulling = false;

        document.addEventListener('touchstart', (e) => {
            if (window.scrollY === 0) {
                startY = e.touches[0].clientY;
                pulling = true;
            }
        });

        document.addEventListener('touchmove', (e) => {
            if (!pulling) return;
            const currentY = e.touches[0].clientY;
            const diff = currentY - startY;
            if (diff > 80) {
                pulling = false;
                $wire.$refresh();
            }
        });

        document.addEventListener('touchend', () => {
            pulling = false;
        });
    },

    onDragStart(event, leadId) {
        this.draggedId = leadId;
        event.dataTransfer.effectAllowed = 'move';
        event.target.classList.add('opacity-50', 'scale-95');
    },

    onDragOver(event) {
        event.dataTransfer.dropEffect = 'move';
        event.currentTarget.classList.add('ring-2', 'ring-orange-500/30', 'bg-orange-500/5');
    },

    onDragLeave(event) {
        if (event.currentTarget.contains(event.relatedTarget)) return;
        event.currentTarget.classList.remove('ring-2', 'ring-orange-500/30', 'bg-orange-500/5');
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
