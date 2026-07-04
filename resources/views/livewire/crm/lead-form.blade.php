<div>
    @if($showModal)
    <div class="fixed inset-0 z-50 flex items-center justify-center p-4" x-data x-transition>
        {{-- Backdrop --}}
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" wire:click="$set('showModal', false)"></div>

        {{-- Modal --}}
        <div class="relative w-full max-w-lg bg-gray-900 rounded-2xl border border-gray-800 shadow-2xl overflow-hidden"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 scale-95"
             x-transition:enter-end="opacity-100 scale-100">
            {{-- Header --}}
            <div class="flex items-center justify-between p-5 border-b border-gray-800">
                <h2 class="text-lg font-semibold text-white">
                    {{ $lead ? 'Modifier le lead' : 'Nouveau lead' }}
                </h2>
                <button wire:click="$set('showModal', false)" class="text-gray-500 hover:text-white transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>

            {{-- Form --}}
            <form wire:submit="save" class="p-5 space-y-4">
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                    {{-- Restaurant name --}}
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-400 mb-1.5">Nom du restaurant *</label>
                        <input type="text" wire:model="restaurant_name"
                               class="w-full bg-gray-800 border border-gray-700 rounded-xl px-3.5 py-2.5 text-sm text-white placeholder-gray-500 focus:border-orange-500 focus:ring-1 focus:ring-orange-500/30 transition"
                               placeholder="Ex: Restaurant Le Délice">
                        @error('restaurant_name') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                    </div>

                    {{-- Manager --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-400 mb-1.5">Nom du gérant</label>
                        <input type="text" wire:model="manager_name"
                               class="w-full bg-gray-800 border border-gray-700 rounded-xl px-3.5 py-2.5 text-sm text-white placeholder-gray-500 focus:border-orange-500 focus:ring-1 focus:ring-orange-500/30 transition"
                               placeholder="Ex: Kouamé Jean">
                    </div>

                    {{-- Phone --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-400 mb-1.5">Téléphone</label>
                        <input type="tel" wire:model="phone"
                               class="w-full bg-gray-800 border border-gray-700 rounded-xl px-3.5 py-2.5 text-sm text-white placeholder-gray-500 focus:border-orange-500 focus:ring-1 focus:ring-orange-500/30 transition"
                               placeholder="+225 07 XX XX XX XX">
                    </div>

                    {{-- City --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-400 mb-1.5">Ville</label>
                        <select wire:model="city"
                                class="w-full bg-gray-800 border border-gray-700 rounded-xl px-3.5 py-2.5 text-sm text-white focus:border-orange-500 focus:ring-1 focus:ring-orange-500/30 transition">
                            <option value="">Sélectionner</option>
                            <option value="Abidjan">Abidjan</option>
                            <option value="Bouaké">Bouaké</option>
                            <option value="Yamoussoukro">Yamoussoukro</option>
                            <option value="San Pedro">San Pedro</option>
                            <option value="Daloa">Daloa</option>
                            <option value="Korhogo">Korhogo</option>
                        </select>
                    </div>

                    {{-- Source --}}
                    <div>
                        <label class="block text-xs font-medium text-gray-400 mb-1.5">Source</label>
                        <select wire:model="source"
                                class="w-full bg-gray-800 border border-gray-700 rounded-xl px-3.5 py-2.5 text-sm text-white focus:border-orange-500 focus:ring-1 focus:ring-orange-500/30 transition">
                            @foreach(\App\Enums\Crm\LeadSource::cases() as $src)
                                <option value="{{ $src->value }}">{{ $src->label() }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Address --}}
                    <div class="sm:col-span-2">
                        <label class="block text-xs font-medium text-gray-400 mb-1.5">Adresse</label>
                        <input type="text" wire:model="address"
                               class="w-full bg-gray-800 border border-gray-700 rounded-xl px-3.5 py-2.5 text-sm text-white placeholder-gray-500 focus:border-orange-500 focus:ring-1 focus:ring-orange-500/30 transition"
                               placeholder="Quartier, rue, repère...">
                    </div>
                </div>

                {{-- GPS capture button --}}
                <div x-data="{ capturing: false }" class="flex items-center gap-3">
                    <button type="button" @click="capturing = true; navigator.geolocation.getCurrentPosition(p => { $wire.set('latitude', p.coords.latitude); $wire.set('longitude', p.coords.longitude); capturing = false; }, () => capturing = false)"
                            class="flex items-center gap-2 px-3 py-2 bg-gray-800 border border-gray-700 rounded-xl text-xs text-gray-400 hover:text-white hover:border-gray-600 transition">
                        <svg class="w-4 h-4" :class="{ 'animate-pulse': capturing }" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        <span x-text="capturing ? 'Capture GPS...' : '{{ $latitude ? "GPS capturé ✓" : "Capturer position GPS" }}'"></span>
                    </button>
                    @if($latitude)
                    <span class="text-[10px] text-gray-600">{{ number_format($latitude, 4) }}, {{ number_format($longitude, 4) }}</span>
                    @endif
                </div>

                {{-- Actions --}}
                <div class="flex items-center justify-end gap-3 pt-4 border-t border-gray-800">
                    <button type="button" wire:click="$set('showModal', false)"
                            class="px-4 py-2.5 text-sm font-medium text-gray-400 hover:text-white rounded-xl hover:bg-gray-800 transition">
                        Annuler
                    </button>
                    <button type="submit"
                            class="px-5 py-2.5 bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium rounded-xl transition shadow-lg shadow-orange-500/20 active:scale-95"
                            wire:loading.attr="disabled"
                            wire:loading.class="opacity-50">
                        <span wire:loading.remove>{{ $lead ? 'Mettre à jour' : 'Créer le lead' }}</span>
                        <span wire:loading>
                            <svg class="w-4 h-4 animate-spin inline" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        </span>
                    </button>
                </div>
            </form>
        </div>
    </div>
    @endif
</div>
