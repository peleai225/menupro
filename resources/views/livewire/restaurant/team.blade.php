<div>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-neutral-900">Équipe</h1>
            <p class="text-neutral-500 mt-1">Gérez les membres de votre équipe et leurs accès au système.</p>
        </div>
        @if($canAddMore)
            <button wire:click="openInviteModal" class="btn btn-primary px-6 py-3 flex items-center gap-2 hover:scale-105 active:scale-95 transition-all shadow-sm hover:shadow-md">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                </svg>
                Inviter un membre
            </button>
        @else
            <div class="bg-accent-50 border border-accent-200 rounded-xl px-4 py-3">
                <p class="text-sm text-accent-700">
                    <strong>Limite atteinte :</strong> Vous avez atteint la limite de {{ $maxTeam }} membre(s) pour votre plan actuel.
                </p>
            </div>
        @endif
    </div>

    <!-- Quota Info -->
    <div class="card p-6 mb-6">
        <div class="flex items-center justify-between">
            <div>
                <p class="text-sm font-medium text-neutral-600">Membres d'équipe</p>
                <p class="text-2xl font-bold text-neutral-900 mt-1">{{ $currentTeamCount }} / {{ $maxTeam }}</p>
            </div>
            <div class="w-32">
                <div class="h-2 bg-neutral-100 rounded-full overflow-hidden">
                    <div class="h-full bg-primary-500 rounded-full transition-all" style="width: {{ min(100, ($currentTeamCount / $maxTeam) * 100) }}%"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Search -->
    <div class="card p-6 mb-6">
        <input type="text" wire:model.live.debounce.300ms="search" 
               placeholder="Rechercher un membre..." 
               class="w-full h-12 px-4 bg-white border border-neutral-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500">
    </div>

    <!-- Team Members List -->
    @if($teamMembers->count() > 0)
        <div class="grid grid-cols-1 gap-4">
            @foreach($teamMembers as $member)
                <div class="card p-6 hover:shadow-lg transition-shadow">
                    <div class="flex items-center justify-between">
                        <div class="flex items-center gap-4 flex-1">
                            <!-- Avatar -->
                            <div class="w-14 h-14 bg-primary-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <span class="text-lg font-bold text-primary-600">
                                    {{ strtoupper(substr($member->first_name, 0, 1) . substr($member->last_name, 0, 1)) }}
                                </span>
                            </div>

                            <!-- Info -->
                            <div class="flex-1">
                                <div class="flex items-center gap-3 mb-1">
                                    <h3 class="text-lg font-bold text-neutral-900">
                                        {{ $member->first_name }} {{ $member->last_name }}
                                    </h3>
                                    @if($member->id === auth()->id())
                                        <span class="badge bg-primary-500 text-white px-3 py-1 rounded-full text-xs font-medium">Vous</span>
                                    @elseif($member->isRestaurantAdmin())
                                        <span class="badge bg-secondary-500 text-white px-3 py-1 rounded-full text-xs font-medium">Administrateur</span>
                                    @else
                                        <span class="badge bg-neutral-400 text-white px-3 py-1 rounded-full text-xs font-medium">Employé</span>
                                    @endif
                                    @if(!$member->is_active)
                                        <span class="badge bg-red-500 text-white px-3 py-1 rounded-full text-xs font-medium">Inactif</span>
                                    @endif
                                </div>
                                <p class="text-sm text-neutral-500 mb-1">{{ $member->email }}</p>
                                @if($member->phone)
                                    <p class="text-sm text-neutral-500">{{ $member->phone }}</p>
                                @endif
                                @if($member->last_login_at)
                                    <p class="text-xs text-neutral-400 mt-2">
                                        Dernière connexion : {{ $member->last_login_at->locale('fr')->diffForHumans() }}
                                    </p>
                                @else
                                    <p class="text-xs text-neutral-400 mt-2">Jamais connecté</p>
                                @endif
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center gap-2 ml-4">
                            @if($member->id !== auth()->id() && $member->id !== $member->restaurant->owner?->id)
                                <button wire:click="editUser({{ $member->id }})" 
                                        class="btn btn-secondary px-4 py-2 text-sm hover:bg-neutral-700 active:scale-95 transition-all disabled:opacity-50">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                    </svg>
                                </button>
                                <button wire:click="toggleActive({{ $member->id }})" 
                                        class="btn btn-secondary px-4 py-2 text-sm hover:bg-neutral-700 active:scale-95 transition-all disabled:opacity-50">
                                    @if($member->is_active)
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                        </svg>
                                    @else
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                        </svg>
                                    @endif
                                </button>
                                <button wire:click="removeUser({{ $member->id }})" 
                                        wire:confirm="Êtes-vous sûr de vouloir supprimer ce membre de l'équipe ?"
                                        class="btn btn-secondary px-4 py-2 text-sm hover:bg-red-600 hover:text-white active:scale-95 transition-all disabled:opacity-50">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $teamMembers->links() }}
        </div>
    @else
        <div class="card p-12 text-center">
            <svg class="w-16 h-16 text-neutral-300 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
            </svg>
            <h3 class="text-lg font-semibold text-neutral-900 mb-2">Aucun membre d'équipe</h3>
            <p class="text-neutral-500 mb-6">Invitez des membres de votre équipe pour collaborer sur la gestion de votre restaurant.</p>
            @if($canAddMore)
                <button wire:click="openInviteModal" class="btn btn-primary px-6 py-3 flex items-center gap-2 mx-auto shadow-sm hover:shadow-md transition-all">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                    </svg>
                    Inviter un membre
                </button>
            @endif
        </div>
    @endif

    <!-- Invite/Edit Modal -->
    @if($showInviteModal)
        <div x-data="{ show: @entangle('showInviteModal') }" 
             x-show="show" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0"
             x-transition:enter-end="opacity-100"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @keydown.escape.window="show = false; $wire.closeInviteModal()"
             class="fixed inset-0 bg-black/50 z-50 flex items-center justify-center p-4"
             x-cloak>
            <div x-show="show"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 scale-95"
                 x-transition:enter-end="opacity-100 scale-100"
                 x-transition:leave="transition ease-in duration-200"
                 x-transition:leave-start="opacity-100 scale-100"
                 x-transition:leave-end="opacity-0 scale-95"
                 @click.away="show = false; $wire.closeInviteModal()"
                 class="bg-white rounded-2xl shadow-xl max-w-lg w-full max-h-[90vh] overflow-y-auto">
                
                <!-- Header -->
                <div class="p-6 border-b border-neutral-200">
                    <div class="flex items-center justify-between">
                        <h2 class="text-xl font-bold text-neutral-900">
                            {{ $editingUser ? 'Modifier le membre' : 'Inviter un membre' }}
                        </h2>
                        <button wire:click="closeInviteModal" class="text-neutral-400 hover:text-neutral-600 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <!-- Form -->
                <form wire:submit.prevent="invite" class="p-6 space-y-6">
                    <!-- First Name -->
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 mb-2">
                            Prénom <span class="text-red-500">*</span>
                        </label>
                        <input type="text" wire:model="first_name" 
                               placeholder="Jean"
                               class="w-full h-12 px-4 bg-white border border-neutral-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 @error('first_name') border-red-500 @enderror">
                        @error('first_name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Last Name -->
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 mb-2">
                            Nom <span class="text-red-500">*</span>
                        </label>
                        <input type="text" wire:model="last_name" 
                               placeholder="Dupont"
                               class="w-full h-12 px-4 bg-white border border-neutral-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 @error('last_name') border-red-500 @enderror">
                        @error('last_name')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Email -->
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 mb-2">
                            Email <span class="text-red-500">*</span>
                        </label>
                        <input type="email" wire:model="email" 
                               placeholder="jean.dupont@example.com"
                               class="w-full h-12 px-4 bg-white border border-neutral-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 @error('email') border-red-500 @enderror">
                        @error('email')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                        @if(!$editingUser)
                            <p class="text-xs text-neutral-500 mt-1">Un email d'invitation sera envoyé à cette adresse</p>
                        @endif
                    </div>

                    <!-- Phone -->
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 mb-2">
                            Téléphone
                        </label>
                        <input type="tel" wire:model="phone" 
                               placeholder="+225 07 00 00 00 00"
                               class="w-full h-12 px-4 bg-white border border-neutral-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 @error('phone') border-red-500 @enderror">
                        @error('phone')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <!-- Role -->
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 mb-2">
                            Rôle <span class="text-red-500">*</span>
                        </label>
                        <select wire:model="role" 
                                class="w-full h-12 px-4 bg-white border border-neutral-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 @error('role') border-red-500 @enderror">
                            <option value="employee">Employé</option>
                            <option value="restaurant_admin">Administrateur</option>
                        </select>
                        @error('role')
                            <p class="text-red-500 text-sm mt-1">{{ $message }}</p>
                        @enderror
                        <p class="text-xs text-neutral-500 mt-1">
                            <strong>Employé :</strong> Accès limité aux fonctionnalités de base<br>
                            <strong>Administrateur :</strong> Accès complet à toutes les fonctionnalités
                        </p>
                    </div>

                    <!-- Actions -->
                    <div class="flex items-center justify-end gap-3 pt-4 border-t border-neutral-200">
                        <button type="button" wire:click="closeInviteModal" 
                                class="btn btn-secondary px-6 py-3 flex items-center gap-2 hover:bg-neutral-700 active:scale-95 transition-all disabled:opacity-50 shadow-sm hover:shadow-md">
                            Annuler
                        </button>
                        <button type="submit" 
                                class="btn btn-primary px-6 py-3 flex items-center gap-2 hover:bg-primary-600 active:scale-95 transition-all disabled:opacity-50 disabled:cursor-not-allowed shadow-sm hover:shadow-md">
                            {{ $editingUser ? 'Enregistrer' : 'Inviter' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif

    <!-- Flash Messages -->
    @if(session()->has('success'))
        <div x-data="{ show: true }" 
             x-show="show" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-2"
             x-init="setTimeout(() => show = false, 5000)"
             class="fixed bottom-4 right-4 bg-secondary-500 text-white px-6 py-3 rounded-xl shadow-lg z-50"
             x-cloak>
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        </div>
    @endif

    @if(session()->has('error'))
        <div x-data="{ show: true }" 
             x-show="show" 
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 translate-y-2"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100 translate-y-0"
             x-transition:leave-end="opacity-0 translate-y-2"
             x-init="setTimeout(() => show = false, 5000)"
             class="fixed bottom-4 right-4 bg-red-500 text-white px-6 py-3 rounded-xl shadow-lg z-50"
             x-cloak>
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <span>{{ session('error') }}</span>
            </div>
        </div>
    @endif
</div>

