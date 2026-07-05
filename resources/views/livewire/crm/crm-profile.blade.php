<div class="max-w-2xl mx-auto space-y-6">

    {{-- Avatar --}}
    <div class="bg-gray-900 rounded-2xl border border-gray-800 p-6">
        <h2 class="text-base font-semibold text-white mb-5">Photo de profil</h2>
        <div class="flex items-center gap-5">
            <div class="relative shrink-0">
                <img src="{{ auth()->user()->avatar_url }}"
                     class="w-20 h-20 rounded-2xl object-cover ring-2 ring-gray-700" alt="avatar">
                @if($this->photo)
                <div class="absolute inset-0 rounded-2xl overflow-hidden">
                    <img src="{{ $this->photo->temporaryUrl() }}" class="w-full h-full object-cover">
                </div>
                @endif
            </div>
            <div class="flex-1">
                <label class="block">
                    <span class="sr-only">Choisir une photo</span>
                    <input type="file" wire:model="photo" accept="image/*"
                           class="block w-full text-sm text-gray-400
                                  file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0
                                  file:text-sm file:font-medium file:bg-orange-500/10 file:text-orange-400
                                  hover:file:bg-orange-500/20 cursor-pointer transition">
                </label>
                @error('photo') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                @if($this->photo)
                <button wire:click="uploadPhoto"
                        wire:loading.attr="disabled"
                        class="mt-3 px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium rounded-xl transition">
                    <span wire:loading.remove wire:target="uploadPhoto">Enregistrer la photo</span>
                    <span wire:loading wire:target="uploadPhoto">Envoi...</span>
                </button>
                @endif
                <p class="text-xs text-gray-600 mt-2">JPG, PNG ou WebP · max 3 Mo</p>
            </div>
        </div>
    </div>

    {{-- Info perso --}}
    <div class="bg-gray-900 rounded-2xl border border-gray-800 p-6">
        <h2 class="text-base font-semibold text-white mb-5">Informations personnelles</h2>

        @if($profile)
        <div class="mb-4 px-3 py-2 rounded-xl text-xs font-medium inline-flex items-center gap-1.5
            @if($profile->verification_status === 'valide') bg-emerald-500/10 text-emerald-400 border border-emerald-500/20
            @elseif($profile->verification_status === 'banni') bg-red-500/10 text-red-400 border border-red-500/20
            @else bg-amber-500/10 text-amber-400 border border-amber-500/20 @endif">
            <span class="w-1.5 h-1.5 rounded-full
                @if($profile->verification_status === 'valide') bg-emerald-400
                @elseif($profile->verification_status === 'banni') bg-red-400
                @else bg-amber-400 @endif"></span>
            @if($profile->verification_status === 'valide') Compte vérifié
            @elseif($profile->verification_status === 'banni') Compte suspendu
            @else En attente de vérification @endif
            @if($profile->badge_id)
            <span class="text-gray-500 ml-1">· {{ $profile->badge_id }}</span>
            @endif
        </div>
        @endif

        <form wire:submit="saveProfile" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1.5">Nom complet</label>
                <input type="text" wire:model="name"
                       class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-2.5 text-white placeholder-gray-500 focus:border-orange-500 focus:ring-1 focus:ring-orange-500/50 transition">
                @error('name') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1.5">Numéro WhatsApp</label>
                <input type="tel" wire:model="phone"
                       class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-2.5 text-white placeholder-gray-500 focus:border-orange-500 focus:ring-1 focus:ring-orange-500/50 transition">
                @error('phone') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                <p class="text-xs text-gray-600 mt-1">Ce numéro sert aussi à vous contacter.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1.5">Ville</label>
                    <input type="text" wire:model="city" placeholder="Abidjan, Bouaké..."
                           class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-2.5 text-white placeholder-gray-500 focus:border-orange-500 focus:ring-1 focus:ring-orange-500/50 transition">
                    @error('city') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-1.5">Statut / métier principal</label>
                    <input type="text" wire:model="statut_metier" placeholder="Ex: Étudiant, Commerçant..."
                           class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-2.5 text-white placeholder-gray-500 focus:border-orange-500 focus:ring-1 focus:ring-orange-500/50 transition">
                </div>
            </div>

            <div class="pt-2 flex items-center gap-3">
                <button type="submit"
                        wire:loading.attr="disabled"
                        class="px-5 py-2.5 bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold rounded-xl transition shadow-lg shadow-orange-500/20">
                    <span wire:loading.remove wire:target="saveProfile">Enregistrer</span>
                    <span wire:loading wire:target="saveProfile">Sauvegarde...</span>
                </button>
                @if($profileSaved)
                <span class="text-emerald-400 text-sm flex items-center gap-1" wire:poll.3s="$set('profileSaved', false)">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    Sauvegardé
                </span>
                @endif
            </div>
        </form>
    </div>

    {{-- Changer mot de passe --}}
    <div class="bg-gray-900 rounded-2xl border border-gray-800 p-6">
        <h2 class="text-base font-semibold text-white mb-1">Changer le mot de passe</h2>
        <p class="text-sm text-gray-500 mb-5">Minimum 6 caractères. Vous serez déconnecté après modification.</p>

        <form wire:submit="savePassword" class="space-y-4">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1.5">Mot de passe actuel</label>
                <input type="password" wire:model="current_password" autocomplete="current-password"
                       class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-2.5 text-white placeholder-gray-500 focus:border-orange-500 focus:ring-1 focus:ring-orange-500/50 transition">
                @error('current_password') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1.5">Nouveau mot de passe</label>
                <input type="password" wire:model="new_password" autocomplete="new-password"
                       class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-2.5 text-white placeholder-gray-500 focus:border-orange-500 focus:ring-1 focus:ring-orange-500/50 transition">
                @error('new_password') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1.5">Confirmer le nouveau mot de passe</label>
                <input type="password" wire:model="new_password_confirmation" autocomplete="new-password"
                       class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-2.5 text-white placeholder-gray-500 focus:border-orange-500 focus:ring-1 focus:ring-orange-500/50 transition">
            </div>

            <div class="pt-2 flex items-center gap-3">
                <button type="submit"
                        wire:loading.attr="disabled"
                        class="px-5 py-2.5 bg-gray-700 hover:bg-gray-600 text-white text-sm font-semibold rounded-xl border border-gray-600 transition">
                    <span wire:loading.remove wire:target="savePassword">Modifier le mot de passe</span>
                    <span wire:loading wire:target="savePassword">Modification...</span>
                </button>
                @if($passwordSaved)
                <span class="text-emerald-400 text-sm flex items-center gap-1">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                    Mot de passe modifié
                </span>
                @endif
            </div>
        </form>
    </div>

    {{-- Infos lecture seule --}}
    @if($profile)
    <div class="bg-gray-900/50 rounded-2xl border border-gray-800/50 p-6">
        <h2 class="text-sm font-semibold text-gray-500 uppercase tracking-wider mb-4">Informations du compte</h2>
        <div class="space-y-3 text-sm">
            <div class="flex justify-between">
                <span class="text-gray-500">Badge ID</span>
                <span class="text-gray-300 font-mono">{{ $profile->badge_id ?? '—' }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Email (système)</span>
                <span class="text-gray-300">{{ auth()->user()->email }}</span>
            </div>
            <div class="flex justify-between">
                <span class="text-gray-500">Membre depuis</span>
                <span class="text-gray-300">{{ auth()->user()->created_at->translatedFormat('d F Y') }}</span>
            </div>
            @if($profile->approved_at)
            <div class="flex justify-between">
                <span class="text-gray-500">Vérifié le</span>
                <span class="text-gray-300">{{ $profile->approved_at->translatedFormat('d F Y') }}</span>
            </div>
            @endif
        </div>
    </div>
    @endif
</div>
