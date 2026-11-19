<div>
    @php
        $field = 'w-full h-11 px-4 bg-white border border-neutral-200 rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500 text-sm';
        $label = 'block text-sm font-medium text-neutral-700 mb-1';
        $sizes = config('parcels.sizes');
    @endphp

    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-neutral-900">Envoyer un colis</h1>
            <p class="text-neutral-500 mt-1 hidden sm:block">Un livreur MenuPro ramasse le colis à votre commerce et le remet à votre client avec un code à 4 chiffres.</p>
        </div>
    </div>

    @if(session('success'))
        <div class="mb-4 rounded-xl bg-secondary-50 border border-secondary-200 text-secondary-800 px-4 py-3 text-sm">{{ session('success') }}</div>
    @endif
    @if(session('error'))
        <div class="mb-4 rounded-xl bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">{{ session('error') }}</div>
    @endif

    <div class="grid gap-6 lg:grid-cols-5">
        {{-- Formulaire --}}
        <form wire:submit="send" class="card p-4 sm:p-6 lg:col-span-3 space-y-4">
            @error('form')<div class="rounded-xl bg-red-50 border border-red-200 text-red-700 px-4 py-3 text-sm">{{ $message }}</div>@enderror

            <div>
                <label class="{{ $label }}">Adresse de ramassage</label>
                <input wire:model="pickup_address" class="{{ $field }}" maxlength="300">
                @error('pickup_address')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                @if(!$pickup_lat)
                    <input wire:model.live.debounce.500ms="pickup_coords" class="{{ $field }} mt-2" placeholder="Position GPS du commerce (ex. 6.8770, -6.4502)">
                @endif
                @error('pickup_lat')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="{{ $label }}">Adresse du destinataire</label>
                @if($dropoff_address)
                    <div class="flex items-center justify-between gap-2 rounded-xl bg-neutral-50 px-4 py-2 text-sm">
                        <span class="text-neutral-800">{{ $dropoff_address }}</span>
                        <button type="button" wire:click="$set('dropoff_address', '')" class="text-xs text-neutral-500 underline">Changer</button>
                    </div>
                @else
                    <div class="flex gap-2">
                        <input wire:model="dropoff_search" wire:keydown.enter.prevent="searchDropoff" class="{{ $field }}" placeholder="Quartier, rue, repère…">
                        <button type="button" wire:click="searchDropoff" class="btn btn-outline whitespace-nowrap px-4 rounded-xl border border-neutral-200 text-sm">Chercher</button>
                    </div>
                    @error('dropoff_search')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                    @if($dropoff_results)
                        <ul class="mt-2 divide-y divide-neutral-100 rounded-xl border border-neutral-200 text-sm">
                            @foreach($dropoff_results as $i => $r)
                                <li><button type="button" wire:click="chooseDropoff({{ $i }})" class="w-full text-left px-4 py-2 hover:bg-neutral-50">{{ $r['display_name'] }}</button></li>
                            @endforeach
                        </ul>
                    @endif
                    <input wire:model.live.debounce.500ms="dropoff_coords" class="{{ $field }} mt-2" placeholder="… ou collez la position GPS partagée par le client">
                @endif
                @error('dropoff_lat')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                @error('dropoff_address')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="grid gap-4 sm:grid-cols-2">
                <div>
                    <label class="{{ $label }}">Nom du destinataire</label>
                    <input wire:model="recipient_name" class="{{ $field }}" maxlength="120">
                    @error('recipient_name')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="{{ $label }}">Téléphone du destinataire</label>
                    <input wire:model="recipient_phone" type="tel" class="{{ $field }}" placeholder="07 00 00 00 00">
                    @error('recipient_phone')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="{{ $label }}">Votre téléphone (le livreur vous appelle)</label>
                    <input wire:model="sender_phone" type="tel" class="{{ $field }}">
                    @error('sender_phone')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="{{ $label }}">Taille du colis</label>
                    <select wire:model.live="size" class="{{ $field }}">
                        @foreach($sizes as $key => $sizeLabel)
                            <option value="{{ $key }}">{{ $sizeLabel }}</option>
                        @endforeach
                    </select>
                </div>
            </div>

            <div>
                <label class="{{ $label }}">Contenu (courte description)</label>
                <input wire:model="description" class="{{ $field }}" maxlength="160" placeholder="Ex. 2 pagnes dans un sachet">
                @error('description')<p class="text-xs text-red-600 mt-1">{{ $message }}</p>@enderror
            </div>

            <div>
                <label class="{{ $label }}">Note pour le livreur (facultatif)</label>
                <input wire:model="note" class="{{ $field }}" maxlength="300">
            </div>

            <div>
                <span class="{{ $label }}">Paiement de la course</span>
                <div class="flex flex-wrap gap-4 text-sm">
                    <label class="inline-flex items-center gap-2"><input type="radio" wire:model.live="payment_method" value="cash"> Espèces au livreur (au ramassage)</label>
                    <label class="inline-flex items-center gap-2"><input type="radio" wire:model.live="payment_method" value="jeko"> Mobile Money</label>
                </div>
                @if($payment_method === 'jeko')
                    <select wire:model="jeko_operator" class="{{ $field }} mt-2 sm:w-60">
                        <option value="wave">Wave</option>
                        <option value="orange">Orange Money</option>
                        <option value="mtn">MTN MoMo</option>
                        <option value="moov">Moov Money</option>
                    </select>
                @endif
            </div>

            <div class="rounded-xl bg-neutral-50 px-4 py-3 text-sm">
                @if($quote)
                    <span class="font-semibold text-neutral-900">{{ number_format($quote['price'], 0, ',', ' ') }} F</span>
                    <span class="text-neutral-500">· {{ number_format($quote['distance_km'], 1, ',', ' ') }} km · {{ $quote['city'] }}</span>
                @elseif($quoteError)
                    <span class="text-red-600">{{ $quoteError }}</span>
                @else
                    <span class="text-neutral-500">Le prix s'affiche dès que les deux adresses sont connues.</span>
                @endif
            </div>

            <button type="submit" class="btn btn-primary w-full h-12 rounded-xl" wire:loading.attr="disabled">
                <span wire:loading.remove wire:target="send">Commander la course</span>
                <span wire:loading wire:target="send">Envoi…</span>
            </button>
        </form>

        {{-- Mes colis --}}
        <div class="lg:col-span-2 space-y-3">
            <h2 class="text-lg font-bold text-neutral-900">Mes colis</h2>
            @forelse($this->parcels as $p)
                <div class="card p-4 text-sm" wire:key="parcel-{{ $p->id }}">
                    <div class="flex items-center justify-between gap-2">
                        <span class="font-mono font-semibold text-neutral-900">{{ $p->reference }}</span>
                        <span class="text-xs rounded-full px-2 py-0.5 {{ $p->status->isFinal() ? 'bg-neutral-100 text-neutral-600' : 'bg-primary-50 text-primary-700' }}">{{ $p->status->label() }}</span>
                    </div>
                    <p class="mt-1 text-neutral-600">{{ $p->recipient_name }} · {{ \Illuminate\Support\Str::limit($p->dropoff_address, 60) }}</p>
                    <p class="text-neutral-500">{{ number_format($p->price, 0, ',', ' ') }} F · {{ $p->payment_method === 'cash' ? 'espèces' : 'Mobile Money' }}{{ $p->driver ? ' · livreur ' . $p->driver->name : '' }}</p>
                    @unless($p->status->isFinal())
                        <p class="mt-2 text-neutral-800">Code de remise à donner au destinataire : <strong class="font-mono text-base">{{ $p->handover_code }}</strong></p>
                        <div class="mt-1 flex items-center gap-2" x-data="{ copied: false }">
                            <input readonly value="{{ $p->publicTrackingUrl() }}" class="flex-1 text-xs bg-neutral-50 border border-neutral-200 rounded-lg px-2 py-1">
                            <button type="button" class="text-xs text-primary-700 underline" @click="navigator.clipboard.writeText('{{ $p->publicTrackingUrl() }}'); copied = true">
                                <span x-show="!copied">Copier le lien de suivi</span><span x-show="copied">Copié</span>
                            </button>
                        </div>
                    @endunless
                    <div class="mt-2 flex gap-3">
                        @if($p->status === \App\Enums\ParcelStatus::PENDING_PAYMENT && $p->isJeko())
                            <button wire:click="pay('{{ $p->reference }}')" class="text-xs font-medium text-primary-700 underline">Payer</button>
                        @endif
                        @if($p->status->isCancellableBySender())
                            <button wire:click="cancel('{{ $p->reference }}')" wire:confirm="Annuler ce colis ?" class="text-xs text-red-600 underline">Annuler</button>
                        @endif
                    </div>
                </div>
            @empty
                <div class="card p-6 text-center text-neutral-500 text-sm">Aucun colis envoyé pour l'instant.</div>
            @endforelse
            <div>{{ $this->parcels->links() }}</div>
        </div>
    </div>
</div>
