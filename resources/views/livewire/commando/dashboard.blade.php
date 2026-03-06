<div wire:poll.30s="refreshAgent">
        @if($successMessage)
            <div class="mb-4 p-4 bg-emerald-500/20 border border-emerald-500/50 rounded-2xl text-emerald-400 text-sm flex items-center justify-between gap-3"
                 x-data="{ show: true }" x-show="show" x-transition>
                <span>{{ $successMessage }}</span>
                <button type="button" @click="show = false; $wire.set('successMessage', null)" class="text-emerald-400 hover:text-white shrink-0">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
        @endif

        @if($agent->status_verification->value === 'shadow')
            @livewire('commando.complete-profile')
        @endif

        @if($agent->status_verification->value === 'rejete')
            <div class="mb-6 p-5 rounded-2xl border border-red-500/40 bg-red-500/10">
                <h2 class="font-semibold text-red-200 mb-2">Votre dossier n'a pas été retenu</h2>
                @if($agent->rejection_reason)
                    <p class="text-red-300/90 text-sm mb-3">{{ $agent->rejection_reason }}</p>
                @endif
                <p class="text-red-200/80 text-sm">Vous pouvez soumettre une <strong>nouvelle pièce d'identité</strong> (plus lisible ou sous un autre format) ci-dessous. L'équipe la reverra sous peu.</p>
            </div>
            @livewire('commando.complete-profile')
        @endif

        {{-- Photo de profil : tous les agents --}}
        <section class="mb-6 rounded-2xl border border-slate-700/50 bg-slate-800/40 backdrop-blur-xl p-6 shadow-xl shadow-black/20">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 rounded-2xl bg-slate-700/50 flex items-center justify-center border border-slate-600">
                    <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                </div>
                <div>
                    <h2 class="font-bold text-white text-lg">Photo de profil</h2>
                    <p class="text-slate-400 text-sm">Elle apparaît sur votre carte agent. JPG ou PNG, max 2 Mo.</p>
                </div>
            </div>
            <div class="flex flex-col sm:flex-row items-start gap-4" x-data="{ previewUrl: null }" x-init="$wire.on('photoUploaded', () => { previewUrl = null })">
                <div class="shrink-0 relative w-24 h-24">
                    <img x-show="previewUrl" x-cloak :src="previewUrl" alt="Aperçu" class="absolute inset-0 w-24 h-24 rounded-2xl object-cover border-2 border-slate-600">
                    <img x-show="!previewUrl" src="{{ $agent->photo_url }}" alt="Photo de profil" class="w-24 h-24 rounded-2xl object-cover border-2 border-slate-600" wire:key="photo-{{ $agent->photo_path ?? 'none' }}">
                </div>
                <form wire:submit="uploadPhoto" class="flex-1 w-full space-y-2">
                    <input type="file" wire:model="photo" accept=".jpg,.jpeg,.png"
                           class="w-full text-sm text-slate-400 file:mr-3 file:py-2 file:px-4 file:rounded-xl file:border-0 file:bg-orange-500 file:text-white file:font-medium file:cursor-pointer"
                           @change="previewUrl = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null">
                    @error('photo') <p class="text-red-400 text-sm">{{ $message }}</p> @enderror
                    <button type="submit" wire:loading.attr="disabled"
                            class="px-4 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium disabled:opacity-50">
                        <span wire:loading.remove>Mettre à jour la photo</span>
                        <span wire:loading>Envoi…</span>
                    </button>
                </form>
            </div>
        </section>

        {{-- Coordonnées (ville, WhatsApp) modifiables --}}
        <section class="mb-6 rounded-2xl border border-slate-700/50 bg-slate-800/40 backdrop-blur-xl p-6 shadow-xl shadow-black/20">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-12 h-12 rounded-2xl bg-slate-700/50 flex items-center justify-center border border-slate-600">
                    <svg class="w-6 h-6 text-slate-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/></svg>
                </div>
                <div>
                    <h2 class="font-bold text-white text-lg">Coordonnées</h2>
                    <p class="text-slate-400 text-sm">Ville et numéro WhatsApp (affichés sur votre carte).</p>
                </div>
            </div>
            <form wire:submit="updateProfile" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                <div>
                    <label for="profile_city" class="block text-slate-400 text-sm mb-1">Ville</label>
                    <input type="text" id="profile_city" wire:model="profile_city" placeholder="Ex. Abidjan"
                           class="w-full h-11 px-4 rounded-xl border border-slate-600 bg-slate-800 text-white placeholder-slate-500 focus:ring-2 focus:ring-orange-500 transition">
                    @error('profile_city') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div>
                    <label for="profile_whatsapp" class="block text-slate-400 text-sm mb-1">WhatsApp <span class="text-red-400">*</span></label>
                    <input type="text" id="profile_whatsapp" wire:model="profile_whatsapp" placeholder="+225 07 00 00 00 00"
                           class="w-full h-11 px-4 rounded-xl border border-slate-600 bg-slate-800 text-white placeholder-slate-500 focus:ring-2 focus:ring-orange-500 transition">
                    @error('profile_whatsapp') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>
                <div class="sm:col-span-2">
                    <button type="submit" wire:loading.attr="disabled"
                            class="px-4 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium disabled:opacity-50">
                        <span wire:loading.remove>Enregistrer les coordonnées</span>
                        <span wire:loading>Enregistrement…</span>
                    </button>
                </div>
            </form>
        </section>

        @if($agent->canAccessParrainage())
            {{-- Layout 2 colonnes : gauche = Wallet + Performance | droite = Déploiement opérationnel --}}
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6">
                {{-- Colonne gauche : Wallet + Objectif Performance --}}
                <div class="lg:col-span-1 space-y-6">
                    {{-- SOLDE PORTEFEUILLE --}}
                    <section id="wallet" class="rounded-2xl border border-slate-700/50 bg-slate-800/40 backdrop-blur-xl p-6 shadow-xl shadow-black/20">
                        <h2 class="text-slate-400 text-xs font-semibold uppercase tracking-wider mb-3">Solde portefeuille</h2>
                        <p class="text-3xl font-bold text-orange-500 tabular-nums mb-4">{{ number_format($agent->balance, 0, ',', ' ') }} <span class="text-lg font-medium text-slate-400">CFA</span></p>
                        <button type="button" wire:click="$set('showWithdrawalModal', true)"
                                @if($agent->balance_cents < 100) disabled @endif
                                class="w-full py-3 rounded-xl font-semibold bg-orange-500 hover:bg-orange-600 disabled:opacity-50 disabled:cursor-not-allowed text-white transition flex items-center justify-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2h-2m-4-1V7a2 2 0 012-2h2a2 2 0 012 2v1m0 16a2 2 0 01-2 2h-2a2 2 0 01-2-2V7a2 2 0 012-2h2a2 2 0 012 2v7a2 2 0 01-2 2z"/></svg>
                            Retrait rapide
                        </button>
                        <div class="mt-4 pt-4 border-t border-slate-700/50">
                            <p class="text-slate-500 text-xs uppercase tracking-wider mb-2">Historique</p>
                            <div class="space-y-2 max-h-32 overflow-y-auto">
                                @forelse($walletHistory as $tx)
                                    <div class="flex items-center justify-between py-1.5 px-2 rounded-lg bg-slate-900/60 text-sm">
                                        <span class="text-slate-400 truncate">{{ $tx->type->label() }}</span>
                                        <span class="@if($tx->amount_cents > 0) text-emerald-400 @else text-slate-500 @endif font-medium shrink-0">@if($tx->amount_cents > 0)+@endif{{ number_format($tx->amount_cents / 100, 0, ',', ' ') }}</span>
                                    </div>
                                @empty
                                    <p class="text-slate-500 text-xs py-2">Aucune transaction</p>
                                @endforelse
                            </div>
                        </div>
                    </section>

                    {{-- Objectif Performance --}}
                    <section id="performance" class="rounded-2xl border border-slate-700/50 bg-slate-800/40 backdrop-blur-xl p-6 shadow-xl shadow-black/20">
                        <h2 class="font-semibold text-white mb-0.5">Objectif Performance</h2>
                        <p class="text-slate-400 text-sm mb-4">Cycle mensuel en cours</p>
                        @php
                            $referredCount = $agent->referredRestaurants()->count();
                            $progress = $monthlyTarget > 0 ? min(100, (int) round(($monthlySignatures / $monthlyTarget) * 100)) : 0;
                            $remaining = max(0, $monthlyTarget - $monthlySignatures);
                        @endphp
                        <div class="flex items-center gap-4">
                            <div class="relative w-16 h-16 shrink-0">
                                <svg class="w-16 h-16 -rotate-90" viewBox="0 0 36 36">
                                    <path class="text-slate-700" stroke="currentColor" stroke-width="2" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                                    <path class="text-orange-500" stroke="currentColor" stroke-width="2" stroke-dasharray="{{ $progress }}, 100" stroke-linecap="round" fill="none" d="M18 2.0845 a 15.9155 15.9155 0 0 1 0 31.831 a 15.9155 15.9155 0 0 1 0 -31.831"/>
                                </svg>
                                <span class="absolute inset-0 flex items-center justify-center text-xs font-bold text-white">{{ $progress }}%</span>
                            </div>
                            <div class="min-w-0">
                                <p class="text-lg font-bold text-white">{{ $monthlySignatures }}/{{ $monthlyTarget }} <span class="text-slate-400 font-normal text-sm">restaurants</span></p>
                                @if($remaining > 0)
                                    <p class="text-orange-400/90 text-xs mt-1 bg-orange-500/10 rounded-lg px-2 py-1 inline-block">Reste {{ $remaining }} signature{{ $remaining > 1 ? 's' : '' }} pour l'objectif</p>
                                @else
                                    <p class="text-emerald-400 text-xs mt-1">Objectif atteint</p>
                                @endif
                            </div>
                        </div>
                    </section>
                </div>

                {{-- Colonne droite : Déploiement opérationnel --}}
                <div class="lg:col-span-2">
                    <section id="deploiement" class="rounded-2xl border border-slate-700/50 bg-slate-800/40 backdrop-blur-xl p-6 shadow-xl shadow-black/20">
                        <div class="flex items-center justify-between gap-3 mb-4">
                            <div class="flex items-center gap-2">
                                <div class="w-10 h-10 rounded-xl bg-orange-500/20 flex items-center justify-center border border-orange-500/30">
                                    <svg class="w-5 h-5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                                </div>
                                <h2 class="font-bold text-white text-lg">Déploiement opérationnel</h2>
                            </div>
                        </div>
                        <form wire:submit="addDeployment" class="space-y-3 mb-4 p-4 rounded-2xl bg-slate-900/80 border border-slate-700">
                            <input type="text" wire:model="deploy_restaurant_name" placeholder="Nom du restaurant *"
                                   class="w-full h-11 px-4 rounded-xl border border-slate-600 bg-slate-800 text-white placeholder-slate-500 focus:ring-2 focus:ring-orange-500 transition">
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                <input type="text" wire:model="deploy_manager_name" placeholder="Gérant"
                                       class="h-11 px-4 rounded-xl border border-slate-600 bg-slate-800 text-white placeholder-slate-500 focus:ring-2 focus:ring-orange-500 transition">
                                <input type="text" wire:model="deploy_phone" placeholder="Téléphone"
                                       class="h-11 px-4 rounded-xl border border-slate-600 bg-slate-800 text-white placeholder-slate-500 focus:ring-2 focus:ring-orange-500 transition">
                            </div>
                            <div class="flex gap-2">
                                <button type="button" @click="navigator.geolocation.getCurrentPosition((p) => { $wire.set('deploy_lat', p.coords.latitude); $wire.set('deploy_lng', p.coords.longitude); })" class="px-3 py-2 rounded-xl border border-slate-600 text-slate-400 hover:text-white text-sm">📍 Géolocaliser</button>
                                <button type="submit" class="flex-1 py-2.5 rounded-xl font-semibold bg-orange-500 hover:bg-orange-600 text-white text-sm transition">Enregistrer</button>
                            </div>
                            @error('deploy_restaurant_name') <p class="text-red-400 text-xs">{{ $message }}</p> @enderror
                        </form>
                        <p class="text-slate-400 text-xs mb-3">Restaurants inscrits via votre lien (automatique) et prospects ajoutés à la main.</p>
                        <div class="space-y-2">
                            @forelse($deploymentItems as $d)
                                <div class="flex items-center gap-3 py-3 px-4 rounded-xl bg-slate-900/60 border border-slate-700/50 hover:border-slate-600 transition">
                                    <div class="w-10 h-10 rounded-lg bg-slate-700/80 flex items-center justify-center text-slate-400 shrink-0">
                                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                    </div>
                                    <div class="flex-1 min-w-0">
                                        <p class="font-medium text-white truncate">{{ $d->name }}</p>
                                        <p class="text-slate-500 text-xs truncate">{{ $d->subtitle }}{{ isset($d->subtitle_extra) && $d->subtitle_extra ? ' • ' . $d->subtitle_extra : '' }}</p>
                                    </div>
                                    <span class="shrink-0 px-2.5 py-1 rounded-lg text-xs font-semibold
                                        @if($d->status === 'actif') bg-emerald-500/20 text-emerald-400 border border-emerald-500/40
                                        @elseif($d->status === 'en_attente_paiement') bg-sky-500/20 text-sky-400 border border-sky-500/40
                                        @else bg-amber-500/20 text-amber-400 border border-amber-500/40
                                        @endif">
                                        @if($d->status === 'actif') ACTIF
                                        @elseif($d->status === 'en_negociation') EN NÉGO
                                        @else EN ATTENTE
                                        @endif
                                    </span>
                                    <svg class="w-5 h-5 text-slate-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                                </div>
                            @empty
                                <p class="text-slate-500 text-sm py-6 text-center">Aucun déploiement. Partagez votre lien de parrainage ou ajoutez un prospect ci-dessus.</p>
                            @endforelse
                        </div>
                    </section>
                </div>
            </div>

            {{-- Lien parrainage + Carte --}}
            <section class="mb-6 rounded-2xl border border-slate-700/50 bg-slate-800/40 backdrop-blur-xl p-6 shadow-xl shadow-black/20">
                <div class="flex items-center gap-3 mb-3">
                    <div class="w-10 h-10 rounded-xl bg-orange-500/20 flex items-center justify-center">
                        <svg class="w-5 h-5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                    </div>
                    <div>
                        <h2 class="font-semibold text-white">Lien de parrainage</h2>
                        <p class="text-slate-400 text-sm">Partagez pour inviter des restaurateurs.</p>
                    </div>
                </div>
                <div class="flex gap-2" x-data="{ copied: false }">
                    <input type="text" readonly value="{{ $agent->parrainage_url }}" class="flex-1 rounded-xl border border-slate-600 bg-slate-900 text-slate-300 text-sm px-4 py-2.5">
                    <button type="button" @click="navigator.clipboard.writeText('{{ $agent->parrainage_url }}'); copied = true; setTimeout(() => copied = false, 2000)" class="px-4 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium transition shrink-0">
                        <span x-text="copied ? 'Copié !' : 'Copier'">Copier</span>
                    </button>
                </div>
            </section>

            {{-- Ma carte : visible uniquement si agent vérifié (valide) --}}
            <section class="rounded-2xl border border-slate-700/50 bg-slate-800/40 backdrop-blur-xl p-6 shadow-xl shadow-black/20">
                <div class="mb-4">
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-xl bg-sky-500/20 flex items-center justify-center shrink-0">
                            <svg class="w-5 h-5 text-sky-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4zm0 0c1.306 0 2.417.835 2.83 2M9 14a3.001 3.001 0 00-2.83 2M15 11h3m-3 4h2"/></svg>
                        </div>
                        <div>
                            <div class="flex items-center gap-2">
                                <h2 class="font-semibold text-white">Ma carte agent</h2>
                                <span class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg bg-emerald-500/20 text-emerald-400 border border-emerald-500/40 text-xs font-medium">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                                    Agent vérifié
                                </span>
                            </div>
                            <p class="text-slate-400 text-sm">Carte digitale vérifiable (QR).</p>
                        </div>
                    </div>
                    <div class="mt-3">
                        <a href="{{ route('commando.card') }}" target="_blank" rel="noopener" class="inline-flex items-center justify-center gap-2 px-4 py-2.5 rounded-xl bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium transition whitespace-nowrap">
                            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                            Ouvrir en plein écran
                        </a>
                    </div>
                </div>
                <div class="rounded-2xl overflow-hidden border border-slate-700 bg-slate-900">
                    <iframe src="{{ route('commando.card') }}?embed=1" title="Carte agent" class="w-full h-[480px] md:h-[420px]" sandbox="allow-same-origin"></iframe>
                </div>
                <p class="text-slate-500 text-xs mt-2 text-center">Votre carte telle qu’elle apparaît aux restaurateurs. Utilisez « Ouvrir en plein écran » pour la partager ou l’imprimer.</p>
            </section>
        @elseif($agent->status_verification->value === 'pending_review')
            <section class="rounded-2xl border border-amber-500/30 bg-amber-500/10 p-6">
                <p class="text-amber-200 text-sm">
                    Votre dossier est en cours de vérification. Vous aurez accès au wallet, aux déploiements et à votre carte une fois validé.
                </p>
            </section>
        @endif

    {{-- Modal Demande de retrait --}}
    @if($showWithdrawalModal)
        <div class="fixed inset-0 z-30 flex items-center justify-center p-4 bg-black/60 backdrop-blur-sm" x-data x-show="true" x-transition @click="$wire.set('showWithdrawalModal', false)">
            <div class="w-full max-w-sm rounded-2xl border border-slate-700 bg-slate-900 p-6 shadow-2xl" @click.stop wire:key="withdrawal-modal">
                <h3 class="text-lg font-bold text-white mb-4">Demander un retrait</h3>
                <p class="text-slate-400 text-sm mb-4">Solde : {{ number_format($agent->balance, 0, ',', ' ') }} FCFA</p>
                <input type="number" wire:model="withdrawalAmount" placeholder="Montant (FCFA)" min="1" step="1"
                       class="w-full h-12 px-4 rounded-xl border border-slate-600 bg-slate-800 text-white placeholder-slate-500 focus:ring-2 focus:ring-orange-500 mb-2">
                @error('withdrawalAmount') <p class="text-red-400 text-sm mb-2">{{ $message }}</p> @enderror
                <div class="flex gap-2">
                    <button type="button" wire:click="$set('showWithdrawalModal', false)" class="flex-1 py-3 rounded-xl border border-slate-600 text-slate-300 hover:bg-slate-800 transition">Annuler</button>
                    <button type="button" wire:click="requestWithdrawal" class="flex-1 py-3 rounded-xl bg-orange-500 hover:bg-orange-600 text-white font-semibold transition">Envoyer</button>
                </div>
            </div>
        </div>
    @endif
</div>
