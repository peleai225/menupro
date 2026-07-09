<div>
@if($show)
<div class="fixed inset-0 bg-black/70 z-50 flex items-center justify-center p-4"
     wire:click.self="close">
    <div class="bg-gray-900 border border-gray-800 rounded-2xl w-full max-w-lg max-h-[90vh] overflow-y-auto">
        <div class="p-6">
            {{-- Header --}}
            <div class="flex items-center justify-between mb-6">
                <div>
                    <h2 class="text-lg font-bold text-white">Inscrire le restaurant</h2>
                    <p class="text-xs text-gray-500 mt-0.5">Créez le compte du restaurant directement</p>
                </div>
                <button wire:click="close" class="text-gray-500 hover:text-white transition p-1 rounded-lg hover:bg-gray-800">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>

            @if($success)
            {{-- Succès --}}
            <div class="text-center py-6">
                <div class="w-16 h-16 bg-emerald-500/10 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <h3 class="text-white font-bold text-lg mb-2">Restaurant inscrit !</h3>
                <p class="text-gray-400 text-sm mb-4">Le compte a été créé et le lead est passé en <span class="text-emerald-400 font-medium">Signature</span>.</p>
                <p class="text-gray-500 text-xs mb-6">Le restaurant peut maintenant se connecter et payer son abonnement. Dès le paiement, votre commission sera créditée automatiquement.</p>
                <button wire:click="close" class="px-6 py-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium rounded-xl transition">
                    Fermer
                </button>
            </div>
            @else
            {{-- Onglets : Inscrire ou Lien --}}
            <div x-data="{ tab: 'form' }" class="space-y-5">
                <div class="flex gap-2 p-1 bg-gray-800 rounded-xl">
                    <button @click="tab = 'form'"
                            :class="tab === 'form' ? 'bg-gray-700 text-white' : 'text-gray-500 hover:text-gray-300'"
                            class="flex-1 py-2 text-xs font-medium rounded-lg transition">
                        Inscrire maintenant
                    </button>
                    <button @click="tab = 'link'"
                            :class="tab === 'link' ? 'bg-gray-700 text-white' : 'text-gray-500 hover:text-gray-300'"
                            class="flex-1 py-2 text-xs font-medium rounded-lg transition">
                        Envoyer le lien
                    </button>
                </div>

                {{-- TAB: Formulaire d'inscription --}}
                <div x-show="tab === 'form'" class="space-y-4">
                    <div class="grid grid-cols-1 gap-4">
                        <div>
                            <label class="block text-xs text-gray-400 mb-1">Nom du restaurant *</label>
                            <input wire:model="restaurant_name" type="text"
                                   class="w-full bg-gray-800 border border-gray-700 rounded-xl px-3 py-2.5 text-sm text-white placeholder-gray-600 focus:border-orange-500 focus:ring-1 focus:ring-orange-500/30 transition"
                                   placeholder="Ex: Chez Aminata">
                            @error('restaurant_name') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label class="block text-xs text-gray-400 mb-1">Type *</label>
                            <select wire:model="restaurant_type"
                                    class="w-full bg-gray-800 border border-gray-700 rounded-xl px-3 py-2.5 text-sm text-white focus:border-orange-500 focus:ring-1 focus:ring-orange-500/30 transition">
                                <option value="restaurant">Restaurant</option>
                                <option value="bar">Bar</option>
                                <option value="brasserie">Brasserie</option>
                                <option value="maquis">Maquis</option>
                                <option value="traiteur">Traiteur</option>
                                <option value="cafe">Café</option>
                                <option value="food_truck">Food Truck</option>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs text-gray-400 mb-1">Téléphone *</label>
                                <input wire:model="phone" type="text"
                                       class="w-full bg-gray-800 border border-gray-700 rounded-xl px-3 py-2.5 text-sm text-white placeholder-gray-600 focus:border-orange-500 focus:ring-1 focus:ring-orange-500/30 transition"
                                       placeholder="07 00 00 00 00">
                                @error('phone') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                            </div>
                            <div>
                                <label class="block text-xs text-gray-400 mb-1">Ville</label>
                                <select wire:model="city"
                                        class="w-full bg-gray-800 border border-gray-700 rounded-xl px-3 py-2.5 text-sm text-white focus:border-orange-500 focus:ring-1 focus:ring-orange-500/30 transition">
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

                        <div class="border-t border-gray-800 pt-4">
                            <p class="text-xs text-gray-500 mb-3">Compte du propriétaire</p>
                            <div class="grid grid-cols-1 gap-3">
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1">Nom du propriétaire *</label>
                                    <input wire:model="owner_name" type="text"
                                           class="w-full bg-gray-800 border border-gray-700 rounded-xl px-3 py-2.5 text-sm text-white placeholder-gray-600 focus:border-orange-500 focus:ring-1 focus:ring-orange-500/30 transition"
                                           placeholder="Nom complet">
                                    @error('owner_name') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1">Email *</label>
                                    <input wire:model="email" type="email"
                                           class="w-full bg-gray-800 border border-gray-700 rounded-xl px-3 py-2.5 text-sm text-white placeholder-gray-600 focus:border-orange-500 focus:ring-1 focus:ring-orange-500/30 transition"
                                           placeholder="email@exemple.com">
                                    @error('email') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                                <div>
                                    <label class="block text-xs text-gray-400 mb-1">Mot de passe *</label>
                                    <input wire:model="password" type="password"
                                           class="w-full bg-gray-800 border border-gray-700 rounded-xl px-3 py-2.5 text-sm text-white placeholder-gray-600 focus:border-orange-500 focus:ring-1 focus:ring-orange-500/30 transition"
                                           placeholder="Min. 6 caractères">
                                    @error('password') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="flex gap-3 pt-2">
                        <button wire:click="close"
                                class="flex-1 py-2.5 bg-gray-800 hover:bg-gray-700 text-gray-300 text-sm font-medium rounded-xl transition">
                            Annuler
                        </button>
                        <button wire:click="register" wire:loading.attr="disabled"
                                class="flex-1 py-2.5 bg-orange-500 hover:bg-orange-600 disabled:opacity-50 text-white text-sm font-medium rounded-xl transition active:scale-95">
                            <span wire:loading.remove wire:target="register">Créer le compte</span>
                            <span wire:loading wire:target="register">Création...</span>
                        </button>
                    </div>
                </div>

                {{-- TAB: Lien d'inscription --}}
                <div x-show="tab === 'link'" class="space-y-5">
                    <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-4 text-sm text-gray-400">
                        <p>Générez un lien unique à envoyer au restaurant (WhatsApp, SMS). En cliquant sur ce lien, le restaurant accède directement au formulaire d'inscription pré-associé à votre compte.</p>
                        <p class="mt-2 text-emerald-400">Dès qu'il s'inscrit, le lead passe automatiquement en <strong>Signature</strong>.</p>
                    </div>

                    <button wire:click="generateLink" wire:loading.attr="disabled"
                            class="w-full py-3 bg-orange-500 hover:bg-orange-600 disabled:opacity-50 text-white text-sm font-medium rounded-xl transition active:scale-95">
                        <span wire:loading.remove wire:target="generateLink">Générer le lien</span>
                        <span wire:loading wire:target="generateLink">Génération...</span>
                    </button>

                    @if($registrationLink)
                    <div x-data="{ copied: false }" class="space-y-2">
                        <label class="block text-xs text-gray-400">Lien d'inscription</label>
                        <div class="flex gap-2">
                            <input type="text" value="{{ $registrationLink }}" readonly
                                   class="flex-1 bg-gray-800 border border-gray-700 rounded-xl px-3 py-2.5 text-xs text-gray-300 truncate">
                            <button @click="navigator.clipboard.writeText('{{ $registrationLink }}').then(() => { copied = true; setTimeout(() => copied = false, 2000) })"
                                    class="px-3 py-2.5 bg-gray-700 hover:bg-gray-600 text-white rounded-xl transition flex-shrink-0">
                                <span x-show="!copied">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                                    </svg>
                                </span>
                                <span x-show="copied" class="text-emerald-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                </span>
                            </button>
                        </div>

                        {{-- Bouton WhatsApp --}}
                        @if($this->viewingLeadPhone())
                        <a href="https://wa.me/{{ preg_replace('/\D/', '', $this->viewingLeadPhone()) }}?text={{ urlencode('Bonjour ! Voici votre lien pour créer votre compte MenuPro : ' . $registrationLink) }}"
                           target="_blank"
                           class="flex items-center justify-center gap-2 w-full py-2.5 bg-[#25D366]/10 hover:bg-[#25D366]/20 text-[#25D366] text-sm font-medium rounded-xl transition border border-[#25D366]/30">
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
