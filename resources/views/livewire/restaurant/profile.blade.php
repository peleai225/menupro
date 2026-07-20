<div class="max-w-lg mx-auto py-8 px-4">

    <div class="mb-8">
        <h1 class="text-2xl font-bold text-neutral-900">Mon profil</h1>
        <p class="text-neutral-500 mt-1">Gérez vos informations personnelles et votre mot de passe.</p>
    </div>

    {{-- Infos --}}
    <div class="card p-6 mb-6">
        <div class="flex items-center gap-4">
            <div class="w-14 h-14 rounded-full bg-primary-100 flex items-center justify-center text-primary-600 font-bold text-xl flex-shrink-0">
                {{ strtoupper(substr(auth()->user()->name, 0, 1)) }}
            </div>
            <div>
                <p class="font-bold text-neutral-900 text-lg">{{ auth()->user()->name }}</p>
                <p class="text-neutral-500 text-sm">{{ auth()->user()->email }}</p>
                <span class="inline-block mt-1 px-2 py-0.5 rounded-full text-xs font-semibold
                    {{ auth()->user()->role->value === 'restaurant_admin' ? 'bg-primary-100 text-primary-700' : 'bg-neutral-100 text-neutral-600' }}">
                    {{ auth()->user()->role->value === 'restaurant_admin' ? 'Administrateur' : 'Employé' }}
                </span>
            </div>
        </div>
    </div>

    {{-- Succès --}}
    @if(session('success'))
        <div class="mb-6 bg-emerald-50 border border-emerald-200 text-emerald-700 px-4 py-3 rounded-xl text-sm flex items-center gap-2">
            <svg class="w-5 h-5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            {{ session('success') }}
        </div>
    @endif

    {{-- Changer mot de passe --}}
    <div class="card p-6">
        <h2 class="text-base font-bold text-neutral-900 mb-5 flex items-center gap-2">
            <svg class="w-5 h-5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
            </svg>
            Changer mon mot de passe
        </h2>

        <form wire:submit="changePassword" class="space-y-4">
            <div>
                <label class="label">Mot de passe actuel</label>
                <input type="password"
                       wire:model="current_password"
                       class="input @error('current_password') border-red-400 @enderror"
                       autocomplete="current-password">
                @error('current_password')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="label">Nouveau mot de passe</label>
                <input type="password"
                       wire:model="new_password"
                       class="input @error('new_password') border-red-400 @enderror"
                       autocomplete="new-password">
                @error('new_password')
                    <p class="text-red-500 text-xs mt-1">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label class="label">Confirmer le nouveau mot de passe</label>
                <input type="password"
                       wire:model="new_password_confirmation"
                       class="input"
                       autocomplete="new-password">
            </div>

            <button type="submit"
                    wire:loading.attr="disabled"
                    class="btn btn-primary w-full flex items-center justify-center gap-2">
                <span wire:loading.remove>Enregistrer le nouveau mot de passe</span>
                <span wire:loading class="flex items-center gap-2">
                    <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                    </svg>
                    Enregistrement...
                </span>
            </button>
        </form>
    </div>

</div>
