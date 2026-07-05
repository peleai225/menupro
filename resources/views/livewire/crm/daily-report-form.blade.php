<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between flex-wrap gap-3">
        <div>
            <h2 class="text-lg font-semibold text-white">Mon rapport du {{ now()->translatedFormat('d F Y') }}</h2>
            <p class="text-sm text-gray-400 mt-0.5">{{ now()->translatedFormat('l') }}</p>
        </div>
        @if($todayReport?->submitted_at)
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-emerald-500/10 text-emerald-400 border border-emerald-500/20">
                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/></svg>
                Soumis à {{ $todayReport->submitted_at->format('H:i') }}
            </span>
        @else
            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-medium bg-gray-800 text-gray-400 border border-gray-700">
                Non soumis
            </span>
        @endif
    </div>

    {{-- Auto-calculated stats (read-only) --}}
    <div class="grid grid-cols-2 gap-3">
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-4">
            <p class="text-xs text-gray-400 uppercase tracking-wider">Leads créés aujourd'hui</p>
            <p class="text-2xl font-bold text-orange-400 mt-1">{{ $this->newLeadsCount }}</p>
            <p class="text-[10px] text-gray-600 mt-0.5">auto-calculé</p>
        </div>
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-4">
            <p class="text-xs text-gray-400 uppercase tracking-wider">Conversions aujourd'hui</p>
            <p class="text-2xl font-bold text-emerald-400 mt-1">{{ $this->conversionsCount }}</p>
            <p class="text-[10px] text-gray-600 mt-0.5">auto-calculé</p>
        </div>
    </div>

    {{-- If submitted and not editing, show summary --}}
    @if($todayReport && !$editing)
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-5 space-y-4">
            <div class="grid grid-cols-2 sm:grid-cols-3 gap-4">
                <div>
                    <p class="text-xs text-gray-500">Visites terrain</p>
                    <p class="text-lg font-semibold text-white">{{ $todayReport->visits_count }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Démos effectuées</p>
                    <p class="text-lg font-semibold text-white">{{ $todayReport->demos_count }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Zone couverte</p>
                    <p class="text-sm font-medium text-gray-300">{{ $todayReport->zone_covered ?? '-' }}</p>
                </div>
            </div>

            @if($todayReport->obstacles)
                <div>
                    <p class="text-xs text-gray-500 mb-1">Obstacles</p>
                    <p class="text-sm text-gray-300">{{ $todayReport->obstacles }}</p>
                </div>
            @endif

            @if($todayReport->notes)
                <div>
                    <p class="text-xs text-gray-500 mb-1">Observations</p>
                    <p class="text-sm text-gray-300">{{ $todayReport->notes }}</p>
                </div>
            @endif

            @if($todayReport->is_reviewed)
                <div class="flex items-center gap-2 pt-2 border-t border-gray-800">
                    <svg class="w-4 h-4 text-emerald-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                    <span class="text-xs text-emerald-400">Validé par le manager</span>
                </div>
            @endif

            <div class="pt-2 border-t border-gray-800 flex items-center justify-between">
                <p class="text-xs text-gray-500">Rapport soumis à {{ $todayReport->submitted_at?->format('H:i') }}</p>
                <button wire:click="startEditing" class="px-4 py-2 bg-gray-800 hover:bg-gray-700 text-gray-300 text-sm font-medium rounded-xl border border-gray-700 transition">
                    Modifier
                </button>
            </div>
        </div>
    @else
        {{-- Form --}}
        <form wire:submit="submit" class="bg-gray-900 border border-gray-800 rounded-2xl p-5 space-y-5">

            {{-- Visits count with +/- buttons --}}
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Nombre de visites terrain</label>
                <div class="flex items-center gap-3">
                    <button type="button" wire:click="decrementVisits"
                            class="w-10 h-10 flex items-center justify-center bg-gray-800 hover:bg-gray-700 text-gray-300 rounded-xl border border-gray-700 transition text-lg font-bold">−</button>
                    <input type="number" wire:model="visits_count" min="0" max="999"
                           class="flex-1 bg-gray-800 border border-gray-700 rounded-xl px-4 py-2.5 text-white text-center placeholder-gray-500 focus:border-orange-500 focus:ring-1 focus:ring-orange-500/50 transition">
                    <button type="button" wire:click="incrementVisits"
                            class="w-10 h-10 flex items-center justify-center bg-gray-800 hover:bg-gray-700 text-gray-300 rounded-xl border border-gray-700 transition text-lg font-bold">+</button>
                </div>
                @error('visits_count') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Demos count with +/- buttons --}}
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Nombre de démos effectuées</label>
                <div class="flex items-center gap-3">
                    <button type="button" wire:click="decrementDemos"
                            class="w-10 h-10 flex items-center justify-center bg-gray-800 hover:bg-gray-700 text-gray-300 rounded-xl border border-gray-700 transition text-lg font-bold">−</button>
                    <input type="number" wire:model="demos_count" min="0" max="999"
                           class="flex-1 bg-gray-800 border border-gray-700 rounded-xl px-4 py-2.5 text-white text-center placeholder-gray-500 focus:border-orange-500 focus:ring-1 focus:ring-orange-500/50 transition">
                    <button type="button" wire:click="incrementDemos"
                            class="w-10 h-10 flex items-center justify-center bg-gray-800 hover:bg-gray-700 text-gray-300 rounded-xl border border-gray-700 transition text-lg font-bold">+</button>
                </div>
                @error('demos_count') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Zone covered --}}
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Zone / quartier visité</label>
                <input type="text" wire:model="zone_covered" placeholder="Ex: Plateau, Zone 4, Centre-ville..."
                       class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-2.5 text-white placeholder-gray-500 focus:border-orange-500 focus:ring-1 focus:ring-orange-500/50 transition">
                @error('zone_covered') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Obstacles --}}
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Difficultés rencontrées</label>
                <textarea wire:model="obstacles" rows="3"
                          placeholder="Pas de signal, zone embouteillée, refus fréquents..."
                          class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-2.5 text-white placeholder-gray-500 focus:border-orange-500 focus:ring-1 focus:ring-orange-500/50 transition resize-none"></textarea>
                @error('obstacles') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Notes --}}
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Observations libres</label>
                <textarea wire:model="notes" rows="3"
                          placeholder="Commentaires, opportunités repérées, retours terrain..."
                          class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-2.5 text-white placeholder-gray-500 focus:border-orange-500 focus:ring-1 focus:ring-orange-500/50 transition resize-none"></textarea>
                @error('notes') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>

            {{-- Photos terrain --}}
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">
                    Photos terrain
                    <span class="text-gray-500 font-normal text-xs ml-1">(optionnel · max 5 photos · 5 Mo chacune)</span>
                </label>
                <label class="flex flex-col items-center justify-center w-full h-28 border-2 border-dashed border-gray-700 rounded-xl cursor-pointer hover:border-orange-500/50 hover:bg-orange-500/5 transition">
                    <svg class="w-7 h-7 text-gray-600 mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <span class="text-xs text-gray-500">Cliquez ou glissez vos photos</span>
                    <input type="file" wire:model="uploadedPhotos" multiple accept="image/*" class="hidden">
                </label>
                @error('uploadedPhotos.*') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror

                {{-- Preview des photos sélectionnées --}}
                @if(count($uploadedPhotos) > 0)
                <div class="flex flex-wrap gap-2 mt-3">
                    @foreach($uploadedPhotos as $i => $photo)
                    <div class="relative w-20 h-20 rounded-xl overflow-hidden border border-gray-700">
                        <img src="{{ $photo->temporaryUrl() }}" class="w-full h-full object-cover">
                        <button type="button" wire:click="$set('uploadedPhotos.{{ $i }}', null)"
                                class="absolute top-1 right-1 w-5 h-5 bg-black/70 text-white rounded-full text-xs flex items-center justify-center hover:bg-red-500 transition">×</button>
                    </div>
                    @endforeach
                </div>
                @endif

                {{-- Photos déjà sauvegardées --}}
                @if($todayReport && count($todayReport->photos ?? []) > 0)
                <div class="mt-3">
                    <p class="text-xs text-gray-500 mb-2">Photos déjà soumises :</p>
                    <div class="flex flex-wrap gap-2">
                        @foreach($todayReport->photos as $photoPath)
                        <a href="{{ Storage::url($photoPath) }}" target="_blank" class="block w-20 h-20 rounded-xl overflow-hidden border border-gray-700 hover:border-orange-500 transition">
                            <img src="{{ Storage::url($photoPath) }}" class="w-full h-full object-cover">
                        </a>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>

            {{-- Submit button --}}
            <button type="submit"
                    wire:loading.attr="disabled"
                    wire:loading.class="opacity-60 cursor-wait"
                    class="w-full px-4 py-3.5 bg-orange-500 hover:bg-orange-600 active:bg-orange-700 text-white font-semibold rounded-xl transition shadow-lg shadow-orange-500/20">
                <span wire:loading.remove>
                    <svg class="w-4 h-4 inline-block mr-1.5 -mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    {{ $todayReport ? 'Mettre à jour mon rapport' : 'Soumettre mon rapport' }}
                </span>
                <span wire:loading class="flex items-center justify-center gap-2">
                    <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/></svg>
                    Envoi en cours...
                </span>
            </button>
        </form>
    @endif

    {{-- Toast on success --}}
    @script
    <script>
        $wire.on('reportSubmitted', () => {
            if (window.showToast) {
                window.showToast('Rapport soumis avec succès !', 'success');
            }
        });
    </script>
    @endscript
</div>
