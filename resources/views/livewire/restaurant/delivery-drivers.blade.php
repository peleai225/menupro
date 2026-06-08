<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-neutral-900">Livreurs</h1>
            <p class="text-neutral-600">Gerez votre equipe de livraison</p>
        </div>
        <button wire:click="create" class="btn btn-primary px-4 py-2.5 flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Ajouter un livreur
        </button>
    </div>

    {{-- Drivers List --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($this->drivers as $driver)
            <div class="card p-5 {{ !$driver->is_active ? 'opacity-60' : '' }}">
                <div class="flex items-start justify-between mb-3">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-full bg-primary-100 text-primary-600 flex items-center justify-center font-bold">
                            {{ strtoupper(substr($driver->name, 0, 1)) }}
                        </div>
                        <div>
                            <h3 class="font-semibold text-neutral-900">{{ $driver->name }}</h3>
                            <p class="text-sm text-neutral-500">{{ $driver->phone }}</p>
                        </div>
                    </div>
                    <span class="inline-flex items-center gap-1 px-2 py-1 rounded-full text-xs font-medium {{ $driver->is_available ? 'bg-green-100 text-green-700' : 'bg-gray-100 text-gray-600' }}">
                        <span class="w-1.5 h-1.5 rounded-full {{ $driver->is_available ? 'bg-green-500' : 'bg-gray-400' }}"></span>
                        {{ $driver->is_available ? 'Disponible' : 'En course' }}
                    </span>
                </div>

                <div class="flex items-center gap-4 text-sm text-neutral-600 mb-4">
                    <span class="flex items-center gap-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        {{ ucfirst($driver->vehicle_type) }}
                    </span>
                    @if($driver->vehicle_plate)
                        <span class="bg-neutral-100 px-2 py-0.5 rounded font-mono text-xs">{{ $driver->vehicle_plate }}</span>
                    @endif
                    <span class="ml-auto">{{ $driver->total_deliveries }} livraisons</span>
                </div>

                {{-- Driver Link --}}
                <div class="bg-neutral-50 rounded-lg p-3 mb-3">
                    <label class="text-xs font-medium text-neutral-500 mb-1 block">Lien livreur (a envoyer par SMS/WhatsApp)</label>
                    <div class="flex items-center gap-2">
                        <input type="text" readonly value="{{ url('/livreur/' . $driver->token) }}" class="input text-xs flex-1 bg-white font-mono">
                        <button type="button" onclick="navigator.clipboard.writeText('{{ url('/livreur/' . $driver->token) }}')" class="p-2 rounded bg-primary-500 text-white hover:bg-primary-600 transition">
                            <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="flex items-center gap-2">
                    <button wire:click="edit({{ $driver->id }})" class="flex-1 btn bg-neutral-100 hover:bg-neutral-200 text-neutral-700 py-2 text-sm">
                        Modifier
                    </button>
                    <button wire:click="toggleActive({{ $driver->id }})" class="flex-1 btn {{ $driver->is_active ? 'bg-red-50 hover:bg-red-100 text-red-600' : 'bg-green-50 hover:bg-green-100 text-green-600' }} py-2 text-sm">
                        {{ $driver->is_active ? 'Desactiver' : 'Activer' }}
                    </button>
                </div>
            </div>
        @empty
            <div class="col-span-full text-center py-12">
                <svg class="w-16 h-16 mx-auto text-neutral-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <p class="text-neutral-500 text-lg">Aucun livreur</p>
                <p class="text-neutral-400 text-sm mt-1">Ajoutez vos livreurs pour gerer les livraisons</p>
            </div>
        @endforelse
    </div>

    {{-- Modal --}}
    @if($showModal)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" wire:click.self="$set('showModal', false)">
            <div class="bg-white rounded-2xl w-full max-w-md p-6">
                <h2 class="text-lg font-bold text-neutral-900 mb-4">
                    {{ $editingId ? 'Modifier le livreur' : 'Nouveau livreur' }}
                </h2>

                <form wire:submit="save" class="space-y-4">
                    <div>
                        <label class="text-sm font-medium text-neutral-700">Nom complet</label>
                        <input type="text" wire:model="name" class="input mt-1" placeholder="Kouassi Jean">
                        @error('name') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div>
                        <label class="text-sm font-medium text-neutral-700">Telephone</label>
                        <input type="tel" wire:model="phone" class="input mt-1" placeholder="07 XX XX XX XX">
                        @error('phone') <span class="text-red-500 text-xs">{{ $message }}</span> @enderror
                    </div>

                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="text-sm font-medium text-neutral-700">Vehicule</label>
                            <select wire:model="vehicle_type" class="input mt-1">
                                <option value="moto">Moto</option>
                                <option value="velo">Velo</option>
                                <option value="voiture">Voiture</option>
                                <option value="a_pied">A pied</option>
                            </select>
                        </div>
                        <div>
                            <label class="text-sm font-medium text-neutral-700">Plaque (optionnel)</label>
                            <input type="text" wire:model="vehicle_plate" class="input mt-1" placeholder="AB 1234">
                        </div>
                    </div>

                    <div class="flex items-center gap-3 pt-2">
                        <button type="button" wire:click="$set('showModal', false)" class="flex-1 btn bg-neutral-100 hover:bg-neutral-200 text-neutral-700 py-2.5">
                            Annuler
                        </button>
                        <button type="submit" class="flex-1 btn btn-primary py-2.5">
                            {{ $editingId ? 'Enregistrer' : 'Ajouter' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
