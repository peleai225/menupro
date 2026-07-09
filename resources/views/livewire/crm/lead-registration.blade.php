<div>
@if($show)
<div class="fixed inset-0 bg-black/75 z-[60] flex items-end sm:items-center justify-center sm:p-4"
     x-data
     @click.self="$wire.close()">

    <div class="bg-gray-900 border border-gray-800 rounded-t-2xl sm:rounded-2xl w-full sm:max-w-lg max-h-[92vh] overflow-y-auto shadow-2xl"
         @click.stop>

        {{-- Drag handle mobile --}}
        <div class="flex justify-center pt-3 pb-1 sm:hidden">
            <div class="w-10 h-1 bg-gray-700 rounded-full"></div>
        </div>

        <div class="p-5 sm:p-6">

            {{-- Header --}}
            <div class="flex items-center justify-between mb-5">
                <div>
                    <h2 class="text-base font-bold text-white">Inscrire le restaurant</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Créez le compte directement ou envoyez un lien</p>
                </div>
                <button wire:click="close"
                        class="text-gray-500 hover:text-white transition p-1.5 rounded-lg hover:bg-gray-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            @if($success)
            {{-- Succès --}}
            <div class="text-center py-8">
                <div class="w-16 h-16 bg-emerald-500/10 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <h3 class="text-white font-bold text-lg mb-2">Restaurant inscrit !</h3>
                <p class="text-gray-400 text-sm mb-2">
                    Le compte a été créé. Le lead est passé en
                    <span class="text-emerald-400 font-medium">Signature</span>.
                </p>
                <p class="text-gray-500 text-xs mb-6">
                    Dès que le restaurant paie son abonnement, votre commission sera créditée automatiquement.
                </p>
                <button wire:click="close"
                        class="px-8 py-2.5 bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold rounded-xl transition active:scale-95">
                    Fermer
                </button>
            </div>

            @else
            {{-- Onglets --}}
            <div x-data="{ tab: 'form' }">

                <div class="flex gap-1.5 p-1 bg-gray-800/80 rounded-xl mb-5">
                    <button @click="tab = 'form'"
                            :class="tab === 'form' ? 'bg-orange-500 text-white shadow' : 'text-gray-400 hover:text-white'"
                            class="flex-1 py-2 text-xs font-semibold rounded-lg transition">
                        Inscrire maintenant
                    </button>
                    <button @click="tab = 'link'"
                            :class="tab === 'link' ? 'bg-orange-500 text-white shadow' : 'text-gray-400 hover:text-white'"
                            class="flex-1 py-2 text-xs font-semibold rounded-lg transition">
                        Envoyer le lien
                    </button>
                </div>

                {{-- ───── TAB FORMULAIRE ───── --}}
                <div x-show="tab === 'form'" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100">

                    @if($errors->any())
                    <div class="bg-red-500/10 border border-red-500/30 rounded-xl p-3 mb-4">
                        <ul class="space-y-1">
                            @foreach($errors->all() as $error)
                            <li class="text-xs text-red-400 flex items-start gap-1.5">
                                <span class="mt-0.5">•</span> {{ $error }}
                            </li>
                            @endforeach
                        </ul>
                    </div>
                    @endif

                    <div class="space-y-3">

                        {{-- Nom + Type --}}
                        <div>
                            <label class="block text-xs font-medium text-gray-400 mb-1.5">
                                Nom du restaurant <span class="text-red-400">*</span>
                            </label>
                            <input wire:model.defer="restaurant_name"
                                   type="text"
                                   autocomplete="off"
                                   class="w-full bg-gray-800 border @error('restaurant_name') border-red-500/60 @else border-gray-700 @enderror rounded-xl px-3.5 py-2.5 text-sm text-white placeholder-gray-600 focus:border-orange-500 focus:ring-1 focus:ring-orange-500/30 transition outline-none"
                                   placeholder="Ex: Chez Aminata">
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs font-medium text-gray-400 mb-1.5">
                                    Type <span class="text-red-400">*</span>
                                </label>
                                <select wire:model.defer="restaurant_type"
                                        class="w-full bg-gray-800 border border-gray-700 rounded-xl px-3 py-2.5 text-sm text-white focus:border-orange-500 focus:ring-1 focus:ring-orange-500/30 transition outline-none">
                                    <option value="restaurant">Restaurant</option>
                                    <option value="maquis">Maquis</option>
                                    <option value="bar">Bar</option>
                                    <option value="brasserie">Brasserie</option>
                                    <option value="traiteur">Traiteur</option>
                                    <option value="cafe">Café</option>
                                    <option value="food_truck">Food Truck</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-xs font-medium text-gray-400 mb-1.5">Ville</label>
                                <select wire:model.defer="city"
                                        class="w-full bg-gray-800 border border-gray-700 rounded-xl px-3 py-2.5 text-sm text-white focus:border-orange-500 focus:ring-1 focus:ring-orange-500/30 transition outline-none">
                                    <option value="">-- Ville --</option>
                                    <option value="Abidjan">Abidjan</option>
                                    <option value="Bouaké">Bouaké</option>
                                    <option value="Yamoussoukro">Yamoussoukro</option>
                                    <option value="San Pedro">San Pedro</option>
                                    <option value="Daloa">Daloa</option>
                                    <option value="Korhogo">Korhogo</option>
                                </select>
                            </div>
                        </div>

                        <div>
                            <label class="block text-xs font-medium text-gray-400 mb-1.5">
                                Téléphone <span class="text-red-400">*</span>
                            </label>
                            <input wire:model.defer="phone"
                                   type="tel"
                                   inputmode="tel"
                                   autocomplete="off"
                                   class="w-full bg-gray-800 border @error('phone') border-red-500/60 @else border-gray-700 @enderror rounded-xl px-3.5 py-2.5 text-sm text-white placeholder-gray-600 focus:border-orange-500 focus:ring-1 focus:ring-orange-500/30 transition outline-none"
                                   placeholder="07 00 00 00 00">
                        </div>

                        {{-- Séparateur compte --}}
                        <div class="border-t border-gray-800/80 pt-3 mt-1">
                            <p class="text-xs text-gray-500 font-medium mb-3 flex items-center gap-2">
                                <svg class="w-3.5 h-3.5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                                </svg>
                                Compte du propriétaire
                            </p>

                            <div class="space-y-3">
                                <div>
                                    <label class="block text-xs font-medium text-gray-400 mb-1.5">
                                        Nom complet <span class="text-red-400">*</span>
                                    </label>
                                    <input wire:model.defer="owner_name"
                                           type="text"
                                           autocomplete="off"
                                           class="w-full bg-gray-800 border @error('owner_name') border-red-500/60 @else border-gray-700 @enderror rounded-xl px-3.5 py-2.5 text-sm text-white placeholder-gray-600 focus:border-orange-500 focus:ring-1 focus:ring-orange-500/30 transition outline-none"
                                           placeholder="Prénom Nom">
                                </div>

                                <div>
                                    <label class="block text-xs font-medium text-gray-400 mb-1.5">
                                        Email <span class="text-red-400">*</span>
                                    </label>
                                    <input wire:model.defer="email"
                                           type="email"
                                           inputmode="email"
                                           autocomplete="off"
                                           class="w-full bg-gray-800 border @error('email') border-red-500/60 @else border-gray-700 @enderror rounded-xl px-3.5 py-2.5 text-sm text-white placeholder-gray-600 focus:border-orange-500 focus:ring-1 focus:ring-orange-500/30 transition outline-none"
                                           placeholder="email@exemple.com">
                                </div>

                                <div x-data="{ show: false }">
                                    <label class="block text-xs font-medium text-gray-400 mb-1.5">
                                        Mot de passe <span class="text-red-400">*</span>
                                    </label>
                                    <div class="relative">
                                        <input wire:model.defer="password"
                                               :type="show ? 'text' : 'password'"
                                               autocomplete="new-password"
                                               class="w-full bg-gray-800 border @error('password') border-red-500/60 @else border-gray-700 @enderror rounded-xl px-3.5 py-2.5 pr-10 text-sm text-white placeholder-gray-600 focus:border-orange-500 focus:ring-1 focus:ring-orange-500/30 transition outline-none"
                                               placeholder="Min. 6 caractères">
                                        <button type="button" @click="show = !show"
                                                class="absolute right-3 top-1/2 -translate-y-1/2 text-gray-500 hover:text-gray-300 transition">
                                            <svg x-show="!show" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                            </svg>
                                            <svg x-show="show" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21"/>
                                            </svg>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Boutons --}}
                    <div class="flex gap-3 mt-5">
                        <button wire:click="close"
                                class="flex-1 py-2.5 bg-gray-800 hover:bg-gray-700 text-gray-300 text-sm font-medium rounded-xl transition">
                            Annuler
                        </button>
                        <button wire:click="register"
                                wire:loading.attr="disabled"
                                wire:loading.class="opacity-60 cursor-not-allowed"
                                class="flex-1 py-2.5 bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold rounded-xl transition active:scale-95">
                            <span wire:loading.remove wire:target="register">Créer le compte</span>
                            <span wire:loading wire:target="register" class="flex items-center justify-center gap-2">
                                <svg class="animate-spin w-4 h-4" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"/>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"/>
                                </svg>
                                Création...
                            </span>
                        </button>
                    </div>
                </div>

                {{-- ───── TAB LIEN ───── --}}
                <div x-show="tab === 'link'" x-transition:enter="transition ease-out duration-150" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100"
                     class="space-y-4">

                    <div class="bg-gray-800/50 border border-gray-700/50 rounded-xl p-4">
                        <p class="text-sm text-gray-300 font-medium mb-1">Comment ça marche ?</p>
                        <ol class="space-y-1.5 mt-2">
                            <li class="flex items-start gap-2 text-xs text-gray-400">
                                <span class="w-4 h-4 rounded-full bg-orange-500/20 text-orange-400 text-[10px] font-bold flex items-center justify-center flex-shrink-0 mt-0.5">1</span>
                                Générez un lien unique ci-dessous
                            </li>
                            <li class="flex items-start gap-2 text-xs text-gray-400">
                                <span class="w-4 h-4 rounded-full bg-orange-500/20 text-orange-400 text-[10px] font-bold flex items-center justify-center flex-shrink-0 mt-0.5">2</span>
                                Envoyez-le au restaurant (WhatsApp, SMS...)
                            </li>
                            <li class="flex items-start gap-2 text-xs text-gray-400">
                                <span class="w-4 h-4 rounded-full bg-emerald-500/20 text-emerald-400 text-[10px] font-bold flex items-center justify-center flex-shrink-0 mt-0.5">3</span>
                                Le restaurant s'inscrit → lead passe en <strong class="text-emerald-400">Signature</strong> automatiquement
                            </li>
                        </ol>
                    </div>

                    <button wire:click="generateLink"
                            wire:loading.attr="disabled"
                            class="w-full py-3 bg-orange-500 hover:bg-orange-600 disabled:opacity-50 text-white text-sm font-semibold rounded-xl transition active:scale-95 flex items-center justify-center gap-2">
                        <span wire:loading.remove wire:target="generateLink">
                            <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                            </svg>
                            Générer le lien d'inscription
                        </span>
                        <span wire:loading wire:target="generateLink">Génération...</span>
                    </button>

                    @if($registrationLink)
                    <div x-data="{ copied: false }" class="space-y-3">
                        <div class="flex gap-2">
                            <input type="text"
                                   value="{{ $registrationLink }}"
                                   readonly
                                   class="flex-1 bg-gray-800 border border-gray-700 rounded-xl px-3 py-2.5 text-xs text-gray-300 truncate outline-none select-all">
                            <button @click="navigator.clipboard.writeText('{{ $registrationLink }}').then(() => { copied = true; setTimeout(() => copied = false, 2000) })"
                                    class="px-3 py-2.5 bg-gray-700 hover:bg-gray-600 text-white rounded-xl transition flex-shrink-0"
                                    :title="copied ? 'Copié !' : 'Copier'">
                                <svg x-show="!copied" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                </svg>
                                <svg x-show="copied" class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                            </button>
                        </div>

                        <p x-show="copied" x-transition class="text-xs text-emerald-400 text-center">Lien copié !</p>

                        {{-- WhatsApp --}}
                        @if($this->viewingLeadPhone())
                        <a href="https://wa.me/{{ preg_replace('/\D/', '', $this->viewingLeadPhone()) }}?text={{ urlencode('Bonjour ! Voici votre lien pour créer votre compte MenuPro : ' . $registrationLink) }}"
                           target="_blank"
                           class="flex items-center justify-center gap-2 w-full py-3 bg-[#25D366]/10 hover:bg-[#25D366]/20 text-[#25D366] text-sm font-semibold rounded-xl transition border border-[#25D366]/20 active:scale-95">
                            <svg class="w-4 h-4" viewBox="0 0 24 24" fill="currentColor">
                                <path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/>
                            </svg>
                            Envoyer sur WhatsApp
                        </a>
                        @endif
                    </div>
                    @endif
                </div>

            </div>
            @endif

        </div>
    </div>
</div>
@endif
</div>
