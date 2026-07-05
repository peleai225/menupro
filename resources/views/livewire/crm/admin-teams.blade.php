<div class="space-y-6">

    {{-- Flash Messages --}}
    @if (session()->has('message'))
        <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3500)"
             class="rounded-xl bg-emerald-500/10 border border-emerald-500/30 p-4 flex items-center gap-3">
            <svg class="w-5 h-5 text-emerald-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-sm text-emerald-300">{{ session('message') }}</p>
        </div>
    @endif

    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h2 class="text-lg font-semibold text-white">Gestion des équipes</h2>
            <p class="text-xs text-gray-500 mt-0.5">Créez des équipes, assignez un chef et gérez les membres</p>
        </div>
        <button wire:click="openCreateTeam"
                class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-sm font-medium text-white transition-all">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Nouvelle équipe
        </button>
    </div>

    {{-- Teams Grid --}}
    <div class="grid grid-cols-1 xl:grid-cols-2 gap-5">
        @forelse($this->teams as $team)
        <div class="bg-gray-900 rounded-2xl border {{ $team->is_active ? 'border-gray-800/60' : 'border-gray-800/30 opacity-60' }} p-5"
             wire:key="team-{{ $team->id }}">

            {{-- Team Header --}}
            <div class="flex items-start justify-between mb-4">
                <div class="flex items-center gap-3">
                    <div class="w-10 h-10 rounded-xl {{ $team->is_active ? 'bg-orange-500/10' : 'bg-gray-700/30' }} flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 {{ $team->is_active ? 'text-orange-400' : 'text-gray-600' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-sm font-semibold text-gray-100">{{ $team->name }}</h3>
                        @if($team->zone)
                        <p class="text-xs text-gray-500">{{ $team->zone }}</p>
                        @endif
                    </div>
                </div>
                <div class="flex items-center gap-2">
                    @if(!$team->is_active)
                    <span class="px-2 py-0.5 text-[10px] rounded-full bg-gray-700 text-gray-400">Inactive</span>
                    @endif
                    <div class="relative" x-data="{ open: false }" @click.outside="open = false">
                        <button @click="open = !open" class="p-1.5 rounded-lg hover:bg-white/5 transition-colors text-gray-500 hover:text-gray-300">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 6a2 2 0 110-4 2 2 0 010 4zM10 12a2 2 0 110-4 2 2 0 010 4zM10 18a2 2 0 110-4 2 2 0 010 4z"/>
                            </svg>
                        </button>
                        <div x-show="open" x-transition
                             class="absolute right-0 mt-1 w-48 rounded-xl bg-gray-800 border border-gray-700 shadow-xl z-20 overflow-hidden">
                            <button wire:click="openEditTeam({{ $team->id }})" @click="open = false"
                                    class="w-full px-4 py-2.5 text-left text-sm text-gray-300 hover:bg-white/5 transition-colors flex items-center gap-2">
                                <svg class="w-4 h-4 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                Modifier l'équipe
                            </button>
                            <button wire:click="openAddMember({{ $team->id }})" @click="open = false"
                                    class="w-full px-4 py-2.5 text-left text-sm text-gray-300 hover:bg-white/5 transition-colors flex items-center gap-2">
                                <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/></svg>
                                Ajouter un membre
                            </button>
                            <div class="h-px bg-gray-700"></div>
                            <button wire:click="confirmDeleteTeam({{ $team->id }})" @click="open = false"
                                    class="w-full px-4 py-2.5 text-left text-sm text-red-400 hover:bg-red-500/10 transition-colors flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                Supprimer
                            </button>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Leader --}}
            @if($team->leader)
            <div class="flex items-center gap-2 mb-4 px-3 py-2 rounded-xl bg-gray-800/50 border border-gray-700/50">
                <img src="{{ $team->leader->avatar_url }}" class="w-6 h-6 rounded-lg object-cover" alt="">
                <span class="text-xs text-gray-300 flex-1 truncate">{{ $team->leader->name }}</span>
                <span class="text-[10px] px-1.5 py-0.5 rounded bg-violet-500/10 text-violet-400 border border-violet-500/30">Chef</span>
            </div>
            @else
            <div class="flex items-center gap-2 mb-4 px-3 py-2 rounded-xl bg-amber-500/5 border border-amber-500/20">
                <svg class="w-4 h-4 text-amber-500 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                <span class="text-xs text-amber-500">Aucun chef d'équipe désigné</span>
            </div>
            @endif

            {{-- Progress --}}
            <div class="mb-4">
                <div class="flex items-center justify-between text-xs mb-1.5">
                    <span class="text-gray-500">Objectif mensuel</span>
                    <span class="text-gray-300 font-medium">{{ $team->converted_count }} / {{ $team->monthly_target }}</span>
                </div>
                <div class="h-1.5 bg-gray-800 rounded-full overflow-hidden">
                    @php $progress = $team->monthly_target > 0 ? min(100, round(($team->converted_count / $team->monthly_target) * 100)) : 0; @endphp
                    <div class="h-full rounded-full transition-all duration-500 {{ $progress >= 80 ? 'bg-emerald-500' : ($progress >= 50 ? 'bg-amber-500' : 'bg-orange-500') }}"
                         style="width: {{ $progress }}%"></div>
                </div>
            </div>

            {{-- Members --}}
            <div>
                <div class="flex items-center justify-between mb-2">
                    <p class="text-xs text-gray-500">Membres ({{ $team->members->count() }})</p>
                    <button wire:click="openAddMember({{ $team->id }})"
                            class="text-xs text-orange-400 hover:text-orange-300 transition-colors">
                        + Ajouter
                    </button>
                </div>

                @if($team->members->count() > 0)
                <div class="space-y-1.5 max-h-40 overflow-y-auto custom-scrollbar">
                    @foreach($team->members as $member)
                    <div class="flex items-center gap-2.5 px-2.5 py-1.5 rounded-lg hover:bg-gray-800/30 group transition-all">
                        <img src="{{ $member->avatar_url }}" class="w-6 h-6 rounded-lg object-cover flex-shrink-0" alt="">
                        <span class="text-xs text-gray-300 flex-1 truncate">{{ $member->name }}</span>
                        <span class="text-[10px] px-1.5 py-0.5 rounded flex-shrink-0
                            {{ $member->role->value === 'team_leader' ? 'bg-violet-500/10 text-violet-400' : '' }}
                            {{ $member->role->value === 'commercial' ? 'bg-orange-500/10 text-orange-400' : '' }}
                            {{ $member->role->value === 'technician' ? 'bg-cyan-500/10 text-cyan-400' : '' }}">
                            {{ $member->role->label() }}
                        </span>
                        <button wire:click="confirmRemoveMember({{ $member->id }}, {{ $team->id }})"
                                class="opacity-0 group-hover:opacity-100 transition-opacity text-red-500 hover:text-red-400 flex-shrink-0">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                    @endforeach
                </div>
                @else
                <p class="text-xs text-gray-600 text-center py-3">Aucun membre dans cette équipe</p>
                @endif
            </div>
        </div>
        @empty
        <div class="col-span-full text-center py-16 bg-gray-900 rounded-2xl border border-gray-800/50">
            <svg class="w-16 h-16 mx-auto text-gray-700 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <p class="text-gray-500 text-sm mb-4">Aucune équipe créée</p>
            <button wire:click="openCreateTeam"
                    class="inline-flex items-center gap-2 px-4 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-sm font-medium text-white transition-all">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Créer la première équipe
            </button>
        </div>
        @endforelse
    </div>

    {{-- =========== MODALS =========== --}}

    {{-- Create / Edit Team --}}
    @if($showTeamModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
         x-data x-on:keydown.escape.window="$wire.showTeamModal = false">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" wire:click="$set('showTeamModal', false)"></div>
        <div class="relative bg-gray-900 rounded-2xl border border-gray-700 p-6 w-full max-w-md shadow-2xl">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-base font-semibold text-white">
                    {{ $editingTeamId ? 'Modifier l\'équipe' : 'Nouvelle équipe' }}
                </h3>
                <button wire:click="$set('showTeamModal', false)" class="text-gray-500 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Nom de l'équipe *</label>
                    <input type="text" wire:model="teamName" placeholder="Ex: Équipe Abidjan Nord"
                           class="w-full px-3 py-2.5 rounded-xl bg-gray-800/50 border border-gray-700/50 text-white placeholder-gray-600 focus:outline-none focus:ring-2 focus:ring-orange-500/50 transition-all">
                    @error('teamName') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="grid grid-cols-2 gap-3">
                    <div>
                        <label class="block text-xs font-medium text-gray-400 mb-1.5">Zone / Secteur</label>
                        <input type="text" wire:model="teamZone" placeholder="Ex: Cocody, Plateau..."
                               class="w-full px-3 py-2.5 rounded-xl bg-gray-800/50 border border-gray-700/50 text-white placeholder-gray-600 focus:outline-none focus:ring-2 focus:ring-orange-500/50 transition-all">
                    </div>
                    <div>
                        <label class="block text-xs font-medium text-gray-400 mb-1.5">Objectif mensuel *</label>
                        <input type="number" wire:model="teamMonthlyTarget" min="1" placeholder="10"
                               class="w-full px-3 py-2.5 rounded-xl bg-gray-800/50 border border-gray-700/50 text-white placeholder-gray-600 focus:outline-none focus:ring-2 focus:ring-orange-500/50 transition-all">
                        @error('teamMonthlyTarget') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Chef d'équipe</label>
                    <select wire:model="teamLeaderId"
                            class="w-full px-3 py-2.5 rounded-xl bg-gray-800/50 border border-gray-700/50 text-white focus:outline-none focus:ring-2 focus:ring-orange-500/50 transition-all">
                        <option value="">— Sans chef pour l'instant —</option>
                        @foreach($this->availableLeaders as $leader)
                            <option value="{{ $leader->id }}">{{ $leader->name }} ({{ $leader->role->label() }})</option>
                        @endforeach
                    </select>
                    <p class="text-xs text-gray-600 mt-1">Le chef sera automatiquement promu Team Leader et ajouté à l'équipe.</p>
                </div>

                <div class="flex items-center gap-3">
                    <label class="relative inline-flex items-center cursor-pointer">
                        <input type="checkbox" wire:model="teamIsActive" class="sr-only peer">
                        <div class="w-10 h-5 bg-gray-700 rounded-full peer peer-checked:bg-orange-500 transition-colors after:content-[''] after:absolute after:top-0.5 after:left-0.5 after:bg-white after:rounded-full after:h-4 after:w-4 after:transition-transform peer-checked:after:translate-x-5"></div>
                    </label>
                    <span class="text-sm text-gray-300">Équipe active</span>
                </div>
            </div>

            <div class="flex gap-3 mt-6">
                <button wire:click="$set('showTeamModal', false)"
                        class="flex-1 px-4 py-2.5 rounded-xl border border-gray-700 text-sm text-gray-300 hover:bg-gray-800 transition-all">
                    Annuler
                </button>
                <button wire:click="saveTeam" wire:loading.attr="disabled" wire:target="saveTeam"
                        class="flex-1 px-4 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-sm font-medium text-white transition-all disabled:opacity-50">
                    <span wire:loading.remove wire:target="saveTeam">{{ $editingTeamId ? 'Enregistrer' : 'Créer l\'équipe' }}</span>
                    <span wire:loading wire:target="saveTeam">Enregistrement...</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Add Member Modal --}}
    @if($showMemberModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
         x-data x-on:keydown.escape.window="$wire.showMemberModal = false">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" wire:click="$set('showMemberModal', false)"></div>
        <div class="relative bg-gray-900 rounded-2xl border border-gray-700 p-6 w-full max-w-sm shadow-2xl">
            <div class="flex items-center justify-between mb-5">
                <h3 class="text-base font-semibold text-white">Ajouter un membre</h3>
                <button wire:click="$set('showMemberModal', false)" class="text-gray-500 hover:text-white transition-colors">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            <p class="text-sm text-gray-400 mb-4">Équipe : <span class="text-white font-medium">{{ $memberTeamName }}</span></p>

            <div>
                <label class="block text-xs font-medium text-gray-400 mb-1.5">Choisir un agent</label>
                <select wire:model="addMemberId"
                        class="w-full px-3 py-2.5 rounded-xl bg-gray-800/50 border border-gray-700/50 text-white focus:outline-none focus:ring-2 focus:ring-orange-500/50 transition-all">
                    <option value="">— Sélectionner un agent —</option>
                    @foreach($this->availableAgents as $agent)
                        <option value="{{ $agent->id }}">{{ $agent->name }} ({{ $agent->role->label() }})</option>
                    @endforeach
                </select>
                @error('addMemberId') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                @if($this->availableAgents->isEmpty())
                    <p class="text-xs text-gray-500 mt-2">Tous les agents sont déjà dans cette équipe.</p>
                @endif
            </div>

            <div class="flex gap-3 mt-5">
                <button wire:click="$set('showMemberModal', false)"
                        class="flex-1 px-4 py-2.5 rounded-xl border border-gray-700 text-sm text-gray-300 hover:bg-gray-800 transition-all">
                    Annuler
                </button>
                <button wire:click="addMember" wire:loading.attr="disabled" wire:target="addMember"
                        class="flex-1 px-4 py-2.5 rounded-xl bg-emerald-500 hover:bg-emerald-600 text-sm font-medium text-white transition-all disabled:opacity-50">
                    <span wire:loading.remove wire:target="addMember">Ajouter</span>
                    <span wire:loading wire:target="addMember">Ajout...</span>
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Confirm Remove Member --}}
    @if($showRemoveMember)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
         x-data x-on:keydown.escape.window="$wire.showRemoveMember = false">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" wire:click="$set('showRemoveMember', false)"></div>
        <div class="relative bg-gray-900 rounded-2xl border border-gray-700 p-6 w-full max-w-sm shadow-2xl text-center">
            <div class="w-12 h-12 rounded-2xl bg-red-500/10 flex items-center justify-center mx-auto mb-4">
                <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7a4 4 0 11-8 0 4 4 0 018 0zM9 14a6 6 0 00-6 6v1h12v-1a6 6 0 00-6-6zM21 12h-6"/>
                </svg>
            </div>
            <h3 class="text-base font-semibold text-white mb-2">Retirer ce membre ?</h3>
            <p class="text-sm text-gray-400 mb-5">L'agent sera retiré de l'équipe mais conserve son compte et ses données.</p>
            <div class="flex gap-3">
                <button wire:click="$set('showRemoveMember', false)"
                        class="flex-1 px-4 py-2.5 rounded-xl border border-gray-700 text-sm text-gray-300 hover:bg-gray-800 transition-all">
                    Annuler
                </button>
                <button wire:click="removeMember"
                        class="flex-1 px-4 py-2.5 rounded-xl bg-red-500 hover:bg-red-600 text-sm font-medium text-white transition-all">
                    Retirer
                </button>
            </div>
        </div>
    </div>
    @endif

    {{-- Confirm Delete Team --}}
    @if($showDeleteTeam)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4"
         x-data x-on:keydown.escape.window="$wire.showDeleteTeam = false">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" wire:click="$set('showDeleteTeam', false)"></div>
        <div class="relative bg-gray-900 rounded-2xl border border-red-500/20 p-6 w-full max-w-sm shadow-2xl text-center">
            <div class="w-12 h-12 rounded-2xl bg-red-500/10 flex items-center justify-center mx-auto mb-4">
                <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                </svg>
            </div>
            <h3 class="text-base font-semibold text-white mb-2">Supprimer l'équipe ?</h3>
            <p class="text-sm text-gray-400 mb-5">Les membres et leurs données sont conservés — seul le groupement est supprimé.</p>
            <div class="flex gap-3">
                <button wire:click="$set('showDeleteTeam', false)"
                        class="flex-1 px-4 py-2.5 rounded-xl border border-gray-700 text-sm text-gray-300 hover:bg-gray-800 transition-all">
                    Annuler
                </button>
                <button wire:click="deleteTeam"
                        class="flex-1 px-4 py-2.5 rounded-xl bg-red-500 hover:bg-red-600 text-sm font-medium text-white transition-all">
                    Supprimer
                </button>
            </div>
        </div>
    </div>
    @endif

</div>
