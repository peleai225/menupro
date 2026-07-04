<div class="space-y-6" wire:poll.30s>
    {{-- Stats Row --}}
    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-5 gap-3">
        @php $stats = $this->stats; @endphp

        <button wire:click="setStatusFilter('')"
                class="rounded-xl border p-4 text-left transition-all hover:scale-[1.02] active:scale-95
                       {{ !$statusFilter ? 'bg-gray-800 border-gray-700' : 'bg-gray-900 border-gray-800 hover:border-gray-700' }}">
            <p class="text-xs text-gray-400 mb-1">Aujourd'hui</p>
            <p class="text-2xl font-bold text-white tabular-nums">{{ $stats['today'] }}</p>
        </button>

        <button wire:click="setStatusFilter('planifiee')"
                class="rounded-xl border p-4 text-left transition-all hover:scale-[1.02] active:scale-95
                       {{ $statusFilter === 'planifiee' ? 'bg-blue-500/10 border-blue-500/30' : 'bg-gray-900 border-gray-800 hover:border-gray-700' }}">
            <p class="text-xs {{ $statusFilter === 'planifiee' ? 'text-blue-400' : 'text-gray-400' }} mb-1">Planifiées</p>
            <p class="text-2xl font-bold {{ $statusFilter === 'planifiee' ? 'text-blue-400' : 'text-white' }} tabular-nums">{{ $stats['planifiee'] }}</p>
        </button>

        <button wire:click="setStatusFilter('en_cours')"
                class="rounded-xl border p-4 text-left transition-all hover:scale-[1.02] active:scale-95
                       {{ $statusFilter === 'en_cours' ? 'bg-amber-500/10 border-amber-500/30' : 'bg-gray-900 border-gray-800 hover:border-gray-700' }}">
            <p class="text-xs {{ $statusFilter === 'en_cours' ? 'text-amber-400' : 'text-gray-400' }} mb-1">En cours</p>
            <p class="text-2xl font-bold {{ $statusFilter === 'en_cours' ? 'text-amber-400' : 'text-white' }} tabular-nums">{{ $stats['en_cours'] }}</p>
        </button>

        <button wire:click="setStatusFilter('terminee')"
                class="rounded-xl border p-4 text-left transition-all hover:scale-[1.02] active:scale-95
                       {{ $statusFilter === 'terminee' ? 'bg-emerald-500/10 border-emerald-500/30' : 'bg-gray-900 border-gray-800 hover:border-gray-700' }}">
            <p class="text-xs {{ $statusFilter === 'terminee' ? 'text-emerald-400' : 'text-gray-400' }} mb-1">Terminées</p>
            <p class="text-2xl font-bold {{ $statusFilter === 'terminee' ? 'text-emerald-400' : 'text-white' }} tabular-nums">{{ $stats['terminee'] }}</p>
        </button>

        <button wire:click="setStatusFilter('probleme')"
                class="rounded-xl border p-4 text-left transition-all hover:scale-[1.02] active:scale-95
                       {{ $statusFilter === 'probleme' ? 'bg-red-500/10 border-red-500/30' : 'bg-gray-900 border-gray-800 hover:border-gray-700' }}">
            <p class="text-xs {{ $statusFilter === 'probleme' ? 'text-red-400' : 'text-gray-400' }} mb-1">Problèmes</p>
            <p class="text-2xl font-bold {{ $statusFilter === 'probleme' ? 'text-red-400' : 'text-white' }} tabular-nums">{{ $stats['probleme'] }}</p>
        </button>
    </div>

    {{-- Filters --}}
    <div class="flex flex-col sm:flex-row gap-3">
        <select wire:model.live="dateFilter"
                class="bg-gray-900 border border-gray-700 rounded-xl px-3 py-2 text-sm text-gray-300 focus:border-orange-500 focus:ring-1 focus:ring-orange-500/30">
            <option value="all">Toutes les dates</option>
            <option value="today">Aujourd'hui</option>
            <option value="week">Cette semaine</option>
            <option value="overdue">En retard</option>
        </select>

        @if(in_array(auth()->user()->role->value, ['super_admin', 'team_leader']))
        <select wire:model.live="technicianFilter"
                class="bg-gray-900 border border-gray-700 rounded-xl px-3 py-2 text-sm text-gray-300 focus:border-orange-500 focus:ring-1 focus:ring-orange-500/30">
            <option value="">Tous les techniciens</option>
            @foreach($this->technicians as $tech)
                <option value="{{ $tech->id }}">{{ $tech->name }}</option>
            @endforeach
        </select>
        @endif
    </div>

    {{-- Installation List --}}
    <div class="space-y-3">
        @forelse($this->installations as $installation)
        <div class="bg-gray-900 rounded-2xl border border-gray-800/60 p-4 lg:p-5 hover:border-gray-700 transition-all"
             wire:key="install-{{ $installation->id }}">
            <div class="flex flex-col lg:flex-row lg:items-center gap-4">
                {{-- Left: Info --}}
                <div class="flex-1 min-w-0">
                    <div class="flex items-center gap-2 mb-2">
                        {{-- Status badge --}}
                        @php
                            $statusColors = [
                                'planifiee' => 'bg-blue-500/10 text-blue-400 border-blue-500/30',
                                'en_cours' => 'bg-amber-500/10 text-amber-400 border-amber-500/30',
                                'terminee' => 'bg-emerald-500/10 text-emerald-400 border-emerald-500/30',
                                'probleme' => 'bg-red-500/10 text-red-400 border-red-500/30',
                                'annulee' => 'bg-gray-500/10 text-gray-400 border-gray-500/30',
                            ];
                        @endphp
                        <span class="px-2.5 py-0.5 rounded-full text-[11px] font-medium border {{ $statusColors[$installation->status->value] ?? '' }}">
                            {{ $installation->status->label() }}
                        </span>

                        @if($installation->scheduled_at && $installation->status === \App\Enums\Crm\InstallationStatus::PLANIFIEE && $installation->scheduled_at->isPast())
                        <span class="px-2 py-0.5 rounded-full text-[10px] font-medium bg-red-500/20 text-red-400 border border-red-500/30 animate-pulse">
                            EN RETARD
                        </span>
                        @endif
                    </div>

                    <h3 class="text-sm font-semibold text-gray-100 truncate">
                        {{ $installation->lead?->restaurant_name ?? $installation->restaurant?->name ?? 'Restaurant' }}
                    </h3>

                    <div class="flex flex-wrap items-center gap-x-4 gap-y-1 mt-2 text-xs text-gray-500">
                        @if($installation->scheduled_at)
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                            </svg>
                            {{ $installation->scheduled_at->format('d/m/Y à H:i') }}
                        </span>
                        @endif

                        @if($installation->technician)
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            {{ $installation->technician->name }}
                        </span>
                        @endif

                        @if($installation->lead?->city)
                        <span class="flex items-center gap-1">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                            </svg>
                            {{ $installation->lead->city }}
                        </span>
                        @endif

                        @if($installation->lead?->phone)
                        <a href="tel:{{ $installation->lead->phone }}" class="flex items-center gap-1 text-emerald-500 hover:text-emerald-400 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                            </svg>
                            {{ $installation->lead->phone }}
                        </a>
                        @endif
                    </div>

                    @if($installation->equipment && count($installation->equipment) > 0)
                    <div class="flex flex-wrap gap-1.5 mt-2">
                        @foreach($installation->equipment as $equip)
                        <span class="text-[10px] px-2 py-0.5 rounded bg-gray-800 text-gray-400 border border-gray-700">{{ $equip }}</span>
                        @endforeach
                    </div>
                    @endif

                    @if($installation->notes)
                    <p class="text-xs text-gray-500 mt-2 line-clamp-1">{{ $installation->notes }}</p>
                    @endif

                    @if($installation->rating)
                    <div class="flex items-center gap-0.5 mt-2">
                        @for($i = 1; $i <= 5; $i++)
                        <svg class="w-3.5 h-3.5 {{ $i <= $installation->rating ? 'text-amber-400' : 'text-gray-700' }}" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        @endfor
                    </div>
                    @endif
                </div>

                {{-- Right: Actions --}}
                <div class="flex items-center gap-2 flex-shrink-0">
                    @if($installation->status === \App\Enums\Crm\InstallationStatus::PLANIFIEE)
                        <button wire:click="startInstallation({{ $installation->id }})"
                                wire:loading.attr="disabled"
                                class="px-3 py-2 text-xs font-medium rounded-xl bg-amber-500/10 text-amber-400 border border-amber-500/30 hover:bg-amber-500/20 transition active:scale-95">
                            <span wire:loading.remove wire:target="startInstallation({{ $installation->id }})">Démarrer</span>
                            <span wire:loading wire:target="startInstallation({{ $installation->id }})">...</span>
                        </button>
                        @if(in_array(auth()->user()->role->value, ['super_admin', 'team_leader']))
                        <button wire:click="cancelInstallation({{ $installation->id }})"
                                wire:confirm="Annuler cette installation ?"
                                class="px-3 py-2 text-xs font-medium rounded-xl bg-gray-800 text-gray-400 border border-gray-700 hover:bg-gray-700 transition active:scale-95">
                            Annuler
                        </button>
                        @endif
                    @elseif($installation->status === \App\Enums\Crm\InstallationStatus::EN_COURS)
                        <button wire:click="openCompleteModal({{ $installation->id }})"
                                class="px-3 py-2 text-xs font-medium rounded-xl bg-emerald-500/10 text-emerald-400 border border-emerald-500/30 hover:bg-emerald-500/20 transition active:scale-95">
                            Terminer
                        </button>
                        <button wire:click="reportProblem({{ $installation->id }})"
                                wire:confirm="Signaler un problème sur cette installation ?"
                                class="px-3 py-2 text-xs font-medium rounded-xl bg-red-500/10 text-red-400 border border-red-500/30 hover:bg-red-500/20 transition active:scale-95">
                            Problème
                        </button>
                    @elseif($installation->status === \App\Enums\Crm\InstallationStatus::PROBLEME)
                        @if(in_array(auth()->user()->role->value, ['super_admin', 'team_leader']))
                        <button wire:click="reschedule({{ $installation->id }})"
                                class="px-3 py-2 text-xs font-medium rounded-xl bg-blue-500/10 text-blue-400 border border-blue-500/30 hover:bg-blue-500/20 transition active:scale-95">
                            Replanifier
                        </button>
                        @endif
                    @elseif($installation->status === \App\Enums\Crm\InstallationStatus::TERMINEE)
                        <span class="px-3 py-2 text-xs text-emerald-400">
                            <svg class="w-4 h-4 inline" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Complétée
                        </span>
                    @endif
                </div>
            </div>
        </div>
        @empty
        <div class="text-center py-16">
            <svg class="w-16 h-16 mx-auto text-gray-700 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M19.428 15.428a2 2 0 00-1.022-.547l-2.387-.477a6 6 0 00-3.86.517l-.318.158a6 6 0 01-3.86.517L6.05 15.21a2 2 0 00-1.806.547M8 4h8l-1 1v5.172a2 2 0 00.586 1.414l5 5c1.26 1.26.367 3.414-1.415 3.414H4.828c-1.782 0-2.674-2.154-1.414-3.414l5-5A2 2 0 009 10.172V5L8 4z"/>
            </svg>
            <p class="text-gray-500 text-sm">Aucune installation trouvée</p>
            <p class="text-gray-600 text-xs mt-1">Les installations apparaissent quand un lead est converti</p>
        </div>
        @endforelse
    </div>

    {{-- Complete Modal --}}
    @if($activeInstallationId)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm"
         x-data x-transition>
        <div class="w-full max-w-md bg-gray-900 rounded-2xl border border-gray-800 p-6 shadow-2xl">
            <h3 class="text-lg font-semibold text-white mb-4">Terminer l'installation</h3>

            {{-- Rating --}}
            <div class="mb-4">
                <label class="text-sm text-gray-400 mb-2 block">Note qualité (optionnel)</label>
                <div class="flex gap-2">
                    @for($i = 1; $i <= 5; $i++)
                    <button wire:click="$set('ratingValue', {{ $i }})"
                            class="w-10 h-10 rounded-xl border transition-all {{ $ratingValue >= $i ? 'bg-amber-500/20 border-amber-500/50 text-amber-400' : 'bg-gray-800 border-gray-700 text-gray-600 hover:border-gray-600' }}">
                        <svg class="w-5 h-5 mx-auto" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                    </button>
                    @endfor
                </div>
            </div>

            {{-- Notes --}}
            <div class="mb-6">
                <label class="text-sm text-gray-400 mb-2 block">Notes (optionnel)</label>
                <textarea wire:model="notes" rows="3"
                          class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-sm text-gray-200 focus:border-orange-500 focus:ring-1 focus:ring-orange-500/30 resize-none"
                          placeholder="Remarques, équipements installés..."></textarea>
            </div>

            <div class="flex gap-3">
                <button wire:click="$set('activeInstallationId', null)"
                        class="flex-1 px-4 py-2.5 text-sm font-medium rounded-xl bg-gray-800 text-gray-300 border border-gray-700 hover:bg-gray-700 transition">
                    Annuler
                </button>
                <button wire:click="completeInstallation"
                        wire:loading.attr="disabled"
                        class="flex-1 px-4 py-2.5 text-sm font-medium rounded-xl bg-emerald-500 text-white hover:bg-emerald-600 transition active:scale-95">
                    <span wire:loading.remove wire:target="completeInstallation">Confirmer</span>
                    <span wire:loading wire:target="completeInstallation">Traitement...</span>
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
