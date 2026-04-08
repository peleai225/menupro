<div>
    <!-- Trigger Button -->
    <button wire:click="openModal" class="btn btn-outline flex items-center gap-2">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
        </svg>
        Importer des ingrédients
    </button>

    <!-- Modal -->
    @if($showModal)
    <div class="fixed inset-0 z-50 overflow-y-auto" x-data x-init="document.body.classList.add('overflow-hidden')" x-on:remove="document.body.classList.remove('overflow-hidden')">
        <div class="flex min-h-screen items-start justify-center p-4 pt-16">
            <div class="fixed inset-0 bg-black/50" wire:click="closeModal"></div>
            <div class="relative w-full max-w-2xl bg-white rounded-2xl shadow-xl">
                <!-- Header -->
                <div class="p-6 border-b border-neutral-100">
                    <div class="flex items-center justify-between">
                        <div>
                            <h2 class="text-xl font-bold text-neutral-900">Importer des ingrédients</h2>
                            <p class="text-sm text-neutral-500 mt-1">Sélectionnez les ingrédients courants pour votre restaurant</p>
                        </div>
                        <button wire:click="closeModal" class="p-2 hover:bg-neutral-100 rounded-lg">
                            <svg class="w-5 h-5 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Quick actions -->
                    <div class="flex items-center gap-3 mt-4">
                        <button wire:click="selectAll" class="text-sm text-primary-600 hover:text-primary-700 font-medium">
                            Tout sélectionner
                        </button>
                        <span class="text-neutral-300">|</span>
                        <button wire:click="deselectAll" class="text-sm text-neutral-500 hover:text-neutral-700 font-medium">
                            Tout désélectionner
                        </button>
                        <span class="ml-auto text-sm text-neutral-500">
                            {{ count($selected) }} sélectionné(s)
                        </span>
                    </div>
                </div>

                <!-- Result Message -->
                @if($importResult)
                    @php
                        [$type, $msg] = explode(':', $importResult, 2);
                    @endphp
                    <div class="mx-6 mt-4 p-3 rounded-lg text-sm {{ $type === 'success' ? 'bg-green-50 text-green-700 border border-green-200' : ($type === 'error' ? 'bg-red-50 text-red-700 border border-red-200' : 'bg-blue-50 text-blue-700 border border-blue-200') }}">
                        {{ $msg }}
                    </div>
                @endif

                <!-- Categories & Ingredients -->
                <div class="p-6 max-h-[50vh] overflow-y-auto space-y-6">
                    @foreach($library as $categoryName => $ingredients)
                        @php
                            $categoryKeys = array_map(fn($i) => $categoryName . '::' . $i['name'], $ingredients);
                            $allCatSelected = count(array_intersect($categoryKeys, $selected)) === count($categoryKeys);
                            $someCatSelected = count(array_intersect($categoryKeys, $selected)) > 0;
                            $catColors = [
                                'Viandes & Poissons' => 'bg-red-100 text-red-700',
                                'Féculents & Céréales' => 'bg-amber-100 text-amber-700',
                                'Légumes & Condiments' => 'bg-green-100 text-green-700',
                                'Huiles & Assaisonnements' => 'bg-orange-100 text-orange-700',
                                'Boissons' => 'bg-blue-100 text-blue-700',
                            ];
                            $catColor = $catColors[$categoryName] ?? 'bg-neutral-100 text-neutral-700';
                        @endphp
                        <div>
                            <!-- Category Header -->
                            <button wire:click="toggleCategory('{{ $categoryName }}')" type="button"
                                    class="w-full flex items-center justify-between p-3 rounded-xl hover:bg-neutral-50 transition-colors">
                                <div class="flex items-center gap-3">
                                    <span class="px-3 py-1 rounded-lg text-xs font-bold {{ $catColor }}">
                                        {{ $categoryName }}
                                    </span>
                                    <span class="text-xs text-neutral-400">{{ count($ingredients) }} éléments</span>
                                </div>
                                <div class="flex items-center gap-2">
                                    @if($allCatSelected)
                                        <span class="text-xs text-green-600 font-medium">Tout sélectionné</span>
                                    @elseif($someCatSelected)
                                        <span class="text-xs text-primary-600 font-medium">Partiel</span>
                                    @endif
                                    <div class="w-5 h-5 rounded border-2 flex items-center justify-center {{ $allCatSelected ? 'bg-primary-500 border-primary-500' : ($someCatSelected ? 'bg-primary-100 border-primary-300' : 'border-neutral-300') }}">
                                        @if($allCatSelected)
                                            <svg class="w-3 h-3 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/></svg>
                                        @elseif($someCatSelected)
                                            <div class="w-2 h-2 bg-primary-500 rounded-sm"></div>
                                        @endif
                                    </div>
                                </div>
                            </button>

                            <!-- Ingredients Grid -->
                            <div class="grid grid-cols-2 gap-2 mt-2 pl-2">
                                @foreach($ingredients as $ingredient)
                                    @php $key = $categoryName . '::' . $ingredient['name']; @endphp
                                    <label class="flex items-center gap-3 p-2.5 rounded-lg cursor-pointer transition-colors {{ in_array($key, $selected) ? 'bg-primary-50 border border-primary-200' : 'hover:bg-neutral-50 border border-transparent' }}">
                                        <input type="checkbox"
                                               wire:click="toggleItem('{{ $key }}')"
                                               {{ in_array($key, $selected) ? 'checked' : '' }}
                                               class="w-4 h-4 text-primary-500 rounded focus:ring-primary-500 border-neutral-300">
                                        <div class="flex-1 min-w-0">
                                            <p class="text-sm font-medium text-neutral-800 truncate">{{ $ingredient['name'] }}</p>
                                            <p class="text-xs text-neutral-400">{{ $ingredient['unit'] }} · seuil: {{ $ingredient['min'] }}</p>
                                        </div>
                                    </label>
                                @endforeach
                            </div>
                        </div>
                    @endforeach
                </div>

                <!-- Footer -->
                <div class="p-6 border-t border-neutral-100 flex items-center justify-between">
                    <button wire:click="closeModal" class="btn btn-outline px-6">
                        {{ str_starts_with($importResult, 'success:') ? 'Fermer' : 'Annuler' }}
                    </button>
                    @if(!str_starts_with($importResult ?: '', 'success:'))
                        <button wire:click="import"
                                wire:loading.attr="disabled"
                                class="btn btn-primary px-6 flex items-center gap-2"
                                {{ empty($selected) ? 'disabled' : '' }}>
                            <span wire:loading.remove wire:target="import">
                                Importer {{ count($selected) }} ingrédient(s)
                            </span>
                            <span wire:loading wire:target="import" class="flex items-center gap-2">
                                <svg class="animate-spin h-4 w-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                </svg>
                                Importation...
                            </span>
                        </button>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif
</div>
