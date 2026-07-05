<div class="space-y-6" wire:poll.30s>

    {{-- Edit Agent Modal --}}
    @if($showEditModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
         x-data
         x-on:keydown.escape.window="$wire.showEditModal = false">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" wire:click="$set('showEditModal', false)"></div>
        <div class="relative bg-gray-900 rounded-2xl border border-gray-700 p-6 w-full max-w-md shadow-2xl">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-base font-semibold text-white">Modifier l'agent</h3>
                <button wire:click="$set('showEditModal', false)" class="text-gray-500 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <p class="text-sm text-gray-400 mb-5">{{ $editingAgentName }}</p>

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Rôle</label>
                    <select wire:model="editingRole"
                            class="w-full px-3 py-2.5 rounded-xl bg-gray-800/50 border border-gray-700/50 text-white focus:outline-none focus:ring-2 focus:ring-orange-500/50 transition-all">
                        <option value="commercial">Commercial (terrain)</option>
                        <option value="technician">Technicien (installation)</option>
                        <option value="team_leader">Team Leader (chef d'équipe)</option>
                    </select>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Équipe</label>
                    <select wire:model="editingTeamId"
                            class="w-full px-3 py-2.5 rounded-xl bg-gray-800/50 border border-gray-700/50 text-white focus:outline-none focus:ring-2 focus:ring-orange-500/50 transition-all">
                        <option value="">— Sans équipe —</option>
                        @foreach($this->teams as $team)
                            <option value="{{ $team->id }}">{{ $team->name }}</option>
                        @endforeach
                    </select>
                    @if($editingRole === 'team_leader')
                    <p class="text-xs text-amber-400 mt-1.5">En sélectionnant Team Leader, cet agent sera automatiquement défini comme chef de l'équipe choisie.</p>
                    @endif
                </div>
            </div>

            <div class="flex gap-3 mt-6">
                <button wire:click="$set('showEditModal', false)"
                        class="flex-1 px-4 py-2.5 rounded-xl border border-gray-700 text-sm text-gray-300 hover:bg-gray-800 transition-all">
                    Annuler
                </button>
                <button wire:click="saveAgentChanges" wire:loading.attr="disabled"
                        class="flex-1 px-4 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-sm font-medium text-white transition-all disabled:opacity-50">
                    <span wire:loading.remove wire:target="saveAgentChanges">Enregistrer</span>
                    <span wire:loading wire:target="saveAgentChanges">Enregistrement...</span>
                </button>
            </div>
        </div>
    </div>
    @endif
    {{-- Flash Messages --}}
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)"
             class="rounded-xl bg-emerald-500/10 border border-emerald-500/30 p-4 flex items-center gap-3">
            <svg class="w-5 h-5 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-sm text-emerald-300">{{ session('message') }}</p>
        </div>
    @endif

    {{-- Stats Row --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @php $stats = $this->stats; @endphp

        <div class="p-5 rounded-2xl bg-gradient-to-br from-violet-500/10 to-violet-600/5 border border-violet-500/20 hover:border-violet-400/40 transition-all duration-300">
            <div class="flex items-center justify-between mb-3">
                <span class="w-10 h-10 rounded-xl bg-violet-500/15 flex items-center justify-center">
                    <svg class="w-5 h-5 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </span>
            </div>
            <p class="text-2xl font-bold text-white tabular-nums">{{ number_format($stats['total_agents']) }}</p>
            <p class="text-xs text-gray-500 mt-1">Total agents</p>
        </div>

        <div class="p-5 rounded-2xl bg-gradient-to-br from-emerald-500/10 to-emerald-600/5 border border-emerald-500/20 hover:border-emerald-400/40 transition-all duration-300">
            <div class="flex items-center justify-between mb-3">
                <span class="w-10 h-10 rounded-xl bg-emerald-500/15 flex items-center justify-center">
                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
            </div>
            <p class="text-2xl font-bold text-white tabular-nums">{{ number_format($stats['verified']) }}</p>
            <p class="text-xs text-gray-500 mt-1">Agents vérifiés</p>
        </div>

        <div class="p-5 rounded-2xl bg-gradient-to-br from-amber-500/10 to-amber-600/5 border border-amber-500/20 hover:border-amber-400/40 transition-all duration-300">
            <div class="flex items-center justify-between mb-3">
                <span class="w-10 h-10 rounded-xl bg-amber-500/15 flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </span>
            </div>
            <p class="text-2xl font-bold text-white tabular-nums">{{ number_format($stats['pending_verification']) }}</p>
            <p class="text-xs text-gray-500 mt-1">En attente</p>
        </div>

        <div class="p-5 rounded-2xl bg-gradient-to-br from-sky-500/10 to-sky-600/5 border border-sky-500/20 hover:border-sky-400/40 transition-all duration-300">
            <div class="flex items-center justify-between mb-3">
                <span class="w-10 h-10 rounded-xl bg-sky-500/15 flex items-center justify-center">
                    <svg class="w-5 h-5 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </span>
            </div>
            <p class="text-2xl font-bold text-white tabular-nums">{{ number_format($stats['active_this_week']) }}</p>
            <p class="text-xs text-gray-500 mt-1">Actifs cette semaine</p>
        </div>
    </div>

    {{-- Filters & Search --}}
    <div class="rounded-2xl bg-gray-900 border border-gray-800/50 p-6">
        <div class="flex flex-col lg:flex-row gap-4">
            {{-- Search --}}
            <div class="flex-1">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text"
                           wire:model.live.debounce.300ms="search"
                           placeholder="Rechercher par nom, email, téléphone..."
                           class="w-full pl-10 pr-4 py-2.5 rounded-xl bg-gray-800/50 border border-gray-700/50 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-orange-500/50 focus:border-orange-500/50 transition-all duration-200">
                </div>
            </div>

            {{-- Filter Row --}}
            <div class="flex flex-wrap gap-2">
                {{-- Role Filter --}}
                <select wire:model.live="roleFilter"
                        class="px-3 py-2 rounded-lg bg-gray-800/50 border border-gray-700/50 text-sm text-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500/50 transition-all">
                    <option value="">Tous les rôles</option>
                    <option value="commercial">Commercial</option>
                    <option value="technician">Technicien</option>
                    <option value="team_leader">Team Leader</option>
                </select>

                {{-- City Filter --}}
                <select wire:model.live="cityFilter"
                        class="px-3 py-2 rounded-lg bg-gray-800/50 border border-gray-700/50 text-sm text-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500/50 transition-all">
                    <option value="">Toutes les villes</option>
                    @foreach($this->cities as $city)
                        <option value="{{ $city }}">{{ $city }}</option>
                    @endforeach
                </select>

                {{-- Grade Filter --}}
                <select wire:model.live="gradeFilter"
                        class="px-3 py-2 rounded-lg bg-gray-800/50 border border-gray-700/50 text-sm text-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500/50 transition-all">
                    <option value="">Tous les grades</option>
                    <option value="rookie">Rookie</option>
                    <option value="commando">Commando</option>
                    <option value="elite">Elite</option>
                </select>

                {{-- Verification Filter --}}
                <select wire:model.live="verificationFilter"
                        class="px-3 py-2 rounded-lg bg-gray-800/50 border border-gray-700/50 text-sm text-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500/50 transition-all">
                    <option value="">Tous les statuts</option>
                    <option value="pending">En attente</option>
                    <option value="verified">Vérifié</option>
                    <option value="rejected">Rejeté</option>
                </select>

                {{-- Team Filter --}}
                <select wire:model.live="teamFilter"
                        class="px-3 py-2 rounded-lg bg-gray-800/50 border border-gray-700/50 text-sm text-gray-300 focus:outline-none focus:ring-2 focus:ring-orange-500/50 transition-all">
                    <option value="">Toutes les équipes</option>
                    @foreach($this->teams as $team)
                        <option value="{{ $team->id }}">{{ $team->name }}</option>
                    @endforeach
                </select>

                @if($search || $roleFilter || $cityFilter || $gradeFilter || $verificationFilter || $teamFilter)
                    <button wire:click="clearFilters"
                            class="px-3 py-2 rounded-lg bg-red-500/10 border border-red-500/30 text-sm text-red-400 hover:bg-red-500/20 transition-all">
                        Réinitialiser
                    </button>
                @endif
            </div>
        </div>
    </div>

    {{-- Agents List - Desktop Table View --}}
    <div class="hidden lg:block rounded-2xl bg-gray-900 border border-gray-800/50 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-800/50 border-b border-gray-800">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Agent</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Rôle</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Ville</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Grade</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Vérification</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Équipe</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Conversions</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800/50">
                    @forelse($this->agents as $agent)
                        <tr class="hover:bg-white/[0.02] transition-colors" x-data="{ showActions: false }">
                            {{-- Agent Info --}}
                            <td class="px-6 py-4">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $agent->avatar_url }}"
                                         class="w-10 h-10 rounded-xl object-cover ring-2 ring-gray-800"
                                         alt="{{ $agent->name }}">
                                    <div>
                                        <p class="text-sm font-medium text-white">{{ $agent->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $agent->email }}</p>
                                    </div>
                                </div>
                            </td>

                            {{-- Role --}}
                            <td class="px-6 py-4">
                                <span class="px-2.5 py-1 text-xs font-medium rounded-lg
                                    {{ $agent->role->value === 'commercial' ? 'bg-orange-500/10 text-orange-400 border border-orange-500/20' : '' }}
                                    {{ $agent->role->value === 'technician' ? 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20' : '' }}
                                    {{ $agent->role->value === 'team_leader' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : '' }}">
                                    {{ $agent->role->label() }}
                                </span>
                            </td>

                            {{-- City --}}
                            <td class="px-6 py-4">
                                <span class="text-sm text-gray-400">
                                    {{ $agent->commercialProfile?->city ?? $agent->technicianProfile?->zone_geographique ?? '-' }}
                                </span>
                            </td>

                            {{-- Grade --}}
                            <td class="px-6 py-4">
                                @if($agent->crmGrade)
                                    @php $grade = $agent->crmGrade->current_grade; @endphp
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-bold rounded-lg
                                        {{ $grade->value === 'rookie' ? 'bg-slate-500/10 text-slate-400 border border-slate-500/20' : '' }}
                                        {{ $grade->value === 'commando' ? 'bg-orange-500/10 text-orange-400 border border-orange-500/20' : '' }}
                                        {{ $grade->value === 'elite' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : '' }}">
                                        {{ $grade->label() }}
                                    </span>
                                @else
                                    <span class="text-xs text-gray-600">-</span>
                                @endif
                            </td>

                            {{-- Verification Status --}}
                            <td class="px-6 py-4">
                                @if($agent->commercialProfile)
                                    @php $status = $agent->commercialProfile->verification_status; @endphp
                                    <span class="inline-flex items-center gap-1.5 px-2.5 py-1 text-xs font-medium rounded-lg
                                        {{ $status === 'pending' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : '' }}
                                        {{ $status === 'verified' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : '' }}
                                        {{ $status === 'rejected' ? 'bg-red-500/10 text-red-400 border border-red-500/20' : '' }}">
                                        @if($status === 'pending')
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/></svg>
                                            En attente
                                        @elseif($status === 'verified')
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                                            Vérifié
                                        @else
                                            <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/></svg>
                                            Rejeté
                                        @endif
                                    </span>
                                @else
                                    <span class="text-xs text-gray-600">-</span>
                                @endif
                            </td>

                            {{-- Team --}}
                            <td class="px-6 py-4">
                                <span class="text-sm text-gray-400">
                                    {{ $agent->commercialProfile?->team?->name ?? $agent->technicianProfile?->team?->name ?? '-' }}
                                </span>
                            </td>

                            {{-- Conversions --}}
                            <td class="px-6 py-4">
                                <span class="text-sm font-bold text-emerald-400 tabular-nums">
                                    {{ $agent->crmLeadsAssigned->count() }}
                                </span>
                            </td>

                            {{-- Actions --}}
                            <td class="px-6 py-4">
                                <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                                    <button @click="open = !open"
                                            class="p-2 rounded-lg hover:bg-white/5 transition-colors">
                                        <svg class="w-5 h-5 text-gray-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                                        </svg>
                                    </button>

                                    <div x-show="open"
                                         x-transition:enter="transition ease-out duration-100"
                                         x-transition:enter-start="opacity-0 scale-95"
                                         x-transition:enter-end="opacity-100 scale-100"
                                         x-transition:leave="transition ease-in duration-75"
                                         x-transition:leave-start="opacity-100 scale-100"
                                         x-transition:leave-end="opacity-0 scale-95"
                                         class="absolute right-0 mt-2 w-56 rounded-xl bg-gray-800 border border-gray-700 shadow-xl z-50 overflow-hidden">

                                        @if($agent->commercialProfile && $agent->commercialProfile->verification_status === 'pending')
                                            <button wire:click="verifyAgent({{ $agent->id }})"
                                                    class="w-full px-4 py-2.5 text-left text-sm text-emerald-400 hover:bg-emerald-500/10 transition-colors flex items-center gap-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                Vérifier l'agent
                                            </button>
                                            <button wire:click="rejectAgent({{ $agent->id }})"
                                                    class="w-full px-4 py-2.5 text-left text-sm text-red-400 hover:bg-red-500/10 transition-colors flex items-center gap-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                Rejeter
                                            </button>
                                            <div class="h-px bg-gray-700"></div>
                                        @endif

                                        <button wire:click="openEditModal({{ $agent->id }})"
                                                class="w-full px-4 py-2.5 text-left text-sm text-violet-400 hover:bg-violet-500/10 transition-colors flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                            Modifier rôle / équipe
                                        </button>

                                        <button wire:click="toggleActiveStatus({{ $agent->id }})"
                                                class="w-full px-4 py-2.5 text-left text-sm text-gray-300 hover:bg-white/5 transition-colors flex items-center gap-2">
                                            @if($agent->is_active)
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                                                Désactiver
                                            @else
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                                Activer
                                            @endif
                                        </button>

                                        <a href="{{ route('crm.admin.agents.show', $agent->id) }}"
                                           class="w-full px-4 py-2.5 text-left text-sm text-gray-300 hover:bg-white/5 transition-colors flex items-center gap-2">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                            Voir le profil
                                        </a>
                                    </div>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <svg class="w-12 h-12 text-gray-700 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                </svg>
                                <p class="text-gray-500">Aucun agent trouvé</p>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Agents List - Mobile Card View --}}
    <div class="lg:hidden space-y-4">
        @forelse($this->agents as $agent)
            <div class="rounded-2xl bg-gray-900 border border-gray-800/50 p-5 space-y-4">
                {{-- Agent Header --}}
                <div class="flex items-start gap-3">
                    <img src="{{ $agent->avatar_url }}"
                         class="w-14 h-14 rounded-xl object-cover ring-2 ring-gray-800"
                         alt="{{ $agent->name }}">
                    <div class="flex-1 min-w-0">
                        <h3 class="text-base font-semibold text-white truncate">{{ $agent->name }}</h3>
                        <p class="text-xs text-gray-500">{{ $agent->email }}</p>
                        <div class="flex flex-wrap gap-2 mt-2">
                            <span class="px-2 py-0.5 text-xs font-medium rounded-lg
                                {{ $agent->role->value === 'commercial' ? 'bg-orange-500/10 text-orange-400 border border-orange-500/20' : '' }}
                                {{ $agent->role->value === 'technician' ? 'bg-cyan-500/10 text-cyan-400 border border-cyan-500/20' : '' }}
                                {{ $agent->role->value === 'team_leader' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : '' }}">
                                {{ $agent->role->label() }}
                            </span>
                            @if($agent->crmGrade)
                                @php $grade = $agent->crmGrade->current_grade; @endphp
                                <span class="px-2 py-0.5 text-xs font-bold rounded-lg
                                    {{ $grade->value === 'rookie' ? 'bg-slate-500/10 text-slate-400 border border-slate-500/20' : '' }}
                                    {{ $grade->value === 'commando' ? 'bg-orange-500/10 text-orange-400 border border-orange-500/20' : '' }}
                                    {{ $grade->value === 'elite' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : '' }}">
                                    {{ $grade->label() }}
                                </span>
                            @endif
                        </div>
                    </div>
                </div>

                {{-- Agent Details --}}
                <div class="grid grid-cols-2 gap-4 pt-4 border-t border-gray-800">
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Ville</p>
                        <p class="text-sm text-gray-300">
                            {{ $agent->commercialProfile?->city ?? $agent->technicianProfile?->zone_geographique ?? '-' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Équipe</p>
                        <p class="text-sm text-gray-300 truncate">
                            {{ $agent->commercialProfile?->team?->name ?? $agent->technicianProfile?->team?->name ?? '-' }}
                        </p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Vérification</p>
                        @if($agent->commercialProfile)
                            @php $status = $agent->commercialProfile->verification_status; @endphp
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 text-xs font-medium rounded-lg
                                {{ $status === 'pending' ? 'bg-amber-500/10 text-amber-400 border border-amber-500/20' : '' }}
                                {{ $status === 'verified' ? 'bg-emerald-500/10 text-emerald-400 border border-emerald-500/20' : '' }}
                                {{ $status === 'rejected' ? 'bg-red-500/10 text-red-400 border border-red-500/20' : '' }}">
                                @if($status === 'pending') En attente
                                @elseif($status === 'verified') Vérifié
                                @else Rejeté
                                @endif
                            </span>
                        @else
                            <span class="text-sm text-gray-600">-</span>
                        @endif
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Conversions</p>
                        <p class="text-sm font-bold text-emerald-400 tabular-nums">
                            {{ $agent->crmLeadsAssigned->count() }}
                        </p>
                    </div>
                </div>

                {{-- Actions --}}
                <div class="flex gap-2 pt-4 border-t border-gray-800 flex-wrap">
                    @if($agent->commercialProfile && $agent->commercialProfile->verification_status === 'pending')
                        <button wire:click="verifyAgent({{ $agent->id }})"
                                class="flex-1 px-3 py-2 text-xs font-medium rounded-lg bg-emerald-500/10 text-emerald-400 border border-emerald-500/20 hover:bg-emerald-500/20 transition-all">
                            Vérifier
                        </button>
                        <button wire:click="rejectAgent({{ $agent->id }})"
                                class="flex-1 px-3 py-2 text-xs font-medium rounded-lg bg-red-500/10 text-red-400 border border-red-500/20 hover:bg-red-500/20 transition-all">
                            Rejeter
                        </button>
                    @endif
                    <button wire:click="openEditModal({{ $agent->id }})"
                            class="flex-1 px-3 py-2 text-xs font-medium rounded-lg bg-violet-500/10 text-violet-400 border border-violet-500/20 hover:bg-violet-500/20 transition-all">
                        Rôle/Équipe
                    </button>
                    <a href="{{ route('crm.admin.agents.show', $agent->id) }}"
                       class="flex-1 px-3 py-2 text-xs font-medium rounded-lg bg-gray-800/50 text-gray-300 border border-gray-700/50 hover:bg-gray-800 transition-all text-center">
                        Voir profil
                    </a>
                </div>
            </div>
        @empty
            <div class="rounded-2xl bg-gray-900 border border-gray-800/50 p-12 text-center">
                <svg class="w-12 h-12 text-gray-700 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
                <p class="text-gray-500">Aucun agent trouvé</p>
            </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    <div class="flex justify-center">
        {{ $this->agents->links() }}
    </div>
</div>
