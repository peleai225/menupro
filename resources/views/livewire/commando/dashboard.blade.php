<div>
    {{-- === CLOCHE NOTIFICATIONS === --}}
    <div class="flex justify-end mb-4"
         x-data="{ open: false }"
         @click.outside="open = false">
        <button @click="open = !open"
                wire:click="$refresh"
                class="relative p-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            @if($this->unreadNotificationsCount > 0)
                <span class="absolute -top-1 -right-1 w-4 h-4 rounded-full bg-red-500 text-white text-[10px] font-bold flex items-center justify-center">
                    {{ $this->unreadNotificationsCount > 9 ? '9+' : $this->unreadNotificationsCount }}
                </span>
            @endif
        </button>

        <div x-show="open" x-transition
             class="absolute mt-10 right-0 w-80 rounded-2xl border border-slate-700 bg-[#0f172a] shadow-2xl z-50 overflow-hidden">
            <div class="flex items-center justify-between px-4 py-3 border-b border-slate-700/60">
                <span class="text-white font-semibold text-sm">Notifications</span>
                @if($this->unreadNotificationsCount > 0)
                    <button wire:click="markAllNotificationsRead"
                            class="text-xs text-orange-400 hover:text-orange-300 transition">
                        Tout marquer lu
                    </button>
                @endif
            </div>
            <div class="max-h-80 overflow-y-auto divide-y divide-slate-700/40">
                @forelse($this->recentNotifications as $notif)
                    @php
                        $data = $notif->data;
                        $type = $data['type'] ?? '';
                        $isUnread = is_null($notif->read_at);
                        $iconClass = match($type) {
                            'commission_credited'   => 'text-emerald-400 bg-emerald-500/15',
                            'commando_agent_approved' => 'text-emerald-400 bg-emerald-500/15',
                            'account_banned'        => 'text-red-400 bg-red-500/15',
                            'withdrawal_paid'       => 'text-blue-400 bg-blue-500/15',
                            'withdrawal_rejected'   => 'text-amber-400 bg-amber-500/15',
                            default                 => 'text-slate-400 bg-slate-700/40',
                        };
                    @endphp
                    <button wire:click="markNotificationRead('{{ $notif->id }}')"
                            class="w-full flex items-start gap-3 px-4 py-3 hover:bg-slate-800/60 transition text-left {{ $isUnread ? 'bg-slate-800/30' : '' }}">
                        <div class="w-8 h-8 rounded-xl flex items-center justify-center shrink-0 mt-0.5 {{ $iconClass }}">
                            @if($type === 'commission_credited')
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            @elseif($type === 'commando_agent_approved')
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            @elseif($type === 'account_banned')
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            @elseif(in_array($type, ['withdrawal_paid', 'withdrawal_rejected']))
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                            @else
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            @endif
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-white text-xs font-medium">{{ $data['title'] ?? 'Notification' }}</p>
                            <p class="text-slate-400 text-[11px] mt-0.5 line-clamp-2">{{ $data['message'] ?? '' }}</p>
                            <p class="text-slate-600 text-[10px] mt-1">{{ $notif->created_at->diffForHumans() }}</p>
                        </div>
                        @if($isUnread)
                            <span class="w-2 h-2 rounded-full bg-orange-400 shrink-0 mt-2"></span>
                        @endif
                    </button>
                @empty
                    <div class="px-4 py-6 text-center text-slate-500 text-sm">Aucune notification</div>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Toast --}}
    @if($successMessage)
        <div class="mb-6 flex items-center gap-3 px-4 py-3 rounded-2xl bg-emerald-500/10 border border-emerald-500/20 text-emerald-400 text-sm"
             x-data="{ show: true }" x-show="show" x-transition>
            <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <span class="flex-1">{{ $successMessage }}</span>
            <button @click="show = false; $wire.set('successMessage', null)" class="opacity-60 hover:opacity-100">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
            </button>
        </div>
    @endif

    {{-- Profil en attente de validation --}}
    @if($agent->status_verification->value === 'pending_review')
        <div class="mb-6 rounded-2xl border border-amber-500/20 bg-amber-500/8 p-5">
            <div class="flex items-start gap-3">
                <div class="w-9 h-9 rounded-xl bg-amber-500/15 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-4.5 h-4.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-amber-300 font-semibold text-sm">Activation en cours</p>
                    <p class="text-slate-400 text-xs mt-1">L'équipe active votre compte sous 24h. Vous serez notifié par WhatsApp.</p>
                </div>
            </div>
        </div>
    @endif

    {{-- Dossier rejeté --}}
    @if($agent->status_verification->value === 'rejete')
        <div class="mb-6 rounded-2xl border border-red-500/20 bg-red-500/8 p-5">
            <div class="flex items-start gap-3">
                <div class="w-9 h-9 rounded-xl bg-red-500/15 flex items-center justify-center shrink-0 mt-0.5">
                    <svg class="w-4.5 h-4.5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </div>
                <div>
                    <p class="text-red-300 font-semibold text-sm">Compte non activé</p>
                    @if($agent->rejection_reason)
                        <p class="text-slate-400 text-xs mt-1">{{ $agent->rejection_reason }}</p>
                    @endif
                    <p class="text-slate-500 text-xs mt-1">Contactez l'équipe MenuPro par WhatsApp pour régulariser.</p>
                </div>
            </div>
        </div>
    @endif

    {{-- === SECTION PROFIL === --}}
    <section class="mb-6" x-data="{ editMode: false, previewUrl: null }" x-init="$wire.on('photoUploaded', () => { previewUrl = null })">
        <div class="flex items-center gap-4">
            {{-- Photo --}}
            <div class="relative shrink-0">
                <img x-show="previewUrl" :src="previewUrl" class="w-16 h-16 rounded-2xl object-cover border-2 border-slate-700">
                <img x-show="!previewUrl" src="{{ $agent->photo_url }}" class="w-16 h-16 rounded-2xl object-cover border-2 border-slate-700" wire:key="photo-{{ $agent->photo_path ?? 'none' }}">
                <label class="absolute -bottom-1 -right-1 w-6 h-6 rounded-lg bg-slate-700 hover:bg-slate-600 border border-slate-600 flex items-center justify-center cursor-pointer transition">
                    <svg class="w-3 h-3 text-slate-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    <input type="file" wire:model="photo" accept=".jpg,.jpeg,.png" class="hidden"
                           @change="previewUrl = $event.target.files[0] ? URL.createObjectURL($event.target.files[0]) : null">
                </label>
            </div>
            {{-- Infos --}}
            <div class="flex-1 min-w-0">
                <h2 class="font-bold text-white text-base truncate">{{ $agent->full_name }}</h2>
                <p class="text-slate-400 text-sm">{{ $agent->city ?? 'Ville non définie' }}</p>
                <div class="mt-1">
                    @if($agent->status_verification->value === 'valide')
                        <span class="inline-flex items-center gap-1 text-[11px] font-medium text-emerald-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-emerald-400 animate-pulse"></span>
                            Agent actif
                        </span>
                    @else
                        <span class="inline-flex items-center gap-1 text-[11px] font-medium text-amber-400">
                            <span class="w-1.5 h-1.5 rounded-full bg-amber-400"></span>
                            En attente d'activation
                        </span>
                    @endif
                </div>
            </div>
            {{-- Bouton éditer --}}
            <button @click="editMode = !editMode" class="shrink-0 p-2 rounded-xl bg-slate-800 hover:bg-slate-700 text-slate-400 hover:text-white transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
            </button>
        </div>

        {{-- Save photo (si photo uploadée) --}}
        @if($photo)
            <div class="mt-3 flex gap-2">
                <button wire:click="uploadPhoto" wire:loading.attr="disabled"
                        class="flex-1 h-9 rounded-xl bg-orange-500 hover:bg-orange-600 text-white text-xs font-semibold transition">
                    <span wire:loading.remove wire:target="uploadPhoto">Enregistrer la photo</span>
                    <span wire:loading wire:target="uploadPhoto">Envoi...</span>
                </button>
                <button wire:click="$set('photo', null)" @click="previewUrl = null"
                        class="h-9 px-3 rounded-xl border border-slate-700 text-slate-400 hover:text-white text-xs transition">
                    Annuler
                </button>
            </div>
            @error('photo') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
        @endif

        {{-- Formulaire édition --}}
        <div x-show="editMode" x-collapse class="mt-4">
            <form wire:submit="updateProfile" class="flex gap-2">
                <input type="text" wire:model="profile_city" placeholder="Ville"
                       class="flex-1 h-10 px-3 rounded-xl border border-slate-700 bg-slate-800 text-white text-sm placeholder-slate-500 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition">
                <input type="text" wire:model="profile_whatsapp" placeholder="WhatsApp"
                       class="flex-1 h-10 px-3 rounded-xl border border-slate-700 bg-slate-800 text-white text-sm placeholder-slate-500 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition">
                <button type="submit" class="h-10 px-4 rounded-xl bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold transition shrink-0">
                    Sauver
                </button>
            </form>
            @error('profile_whatsapp') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
    </section>

    @if($agent->canAccessParrainage())

    {{-- === STATS === --}}
    <div class="grid grid-cols-2 gap-3 mb-6" id="wallet">
        {{-- Solde --}}
        <div class="rounded-2xl border border-slate-700/60 bg-slate-800/40 p-4">
            <p class="text-slate-500 text-xs font-medium mb-2">Solde commissions</p>
            <p class="text-2xl font-bold text-white tabular-nums">
                {{ number_format($agent->balance, 0, ',', ' ') }}
                <span class="text-sm text-slate-400 font-normal">F</span>
            </p>
            <button type="button" wire:click="$set('showWithdrawalModal', true)"
                    @if($agent->balance_cents < 100) disabled @endif
                    class="mt-3 w-full h-8 rounded-xl text-xs font-semibold bg-orange-500/15 text-orange-400 border border-orange-500/25 hover:bg-orange-500/25 disabled:opacity-30 transition">
                Demander un retrait
            </button>
        </div>

        {{-- Restaurants parrainés --}}
        <div class="rounded-2xl border border-slate-700/60 bg-slate-800/40 p-4" id="performance">
            <p class="text-slate-500 text-xs font-medium mb-2">Restaurants actifs</p>
            <p class="text-2xl font-bold text-white tabular-nums">
                {{ $referredRestaurants->count() }}
            </p>
            <p class="text-slate-500 text-xs mt-1">
                @if($referredRestaurants->count() === 0)
                    Partagez votre lien pour commencer
                @elseif($referredRestaurants->count() === 1)
                    1 restaurant sur MenuPro
                @else
                    {{ $referredRestaurants->count() }} restaurants sur MenuPro
                @endif
            </p>
        </div>
    </div>

    {{-- === LIEN DE PARRAINAGE === --}}
    <section class="mb-6" id="lien">
        <h3 class="text-white font-semibold text-sm mb-3">Mon lien de parrainage</h3>
        <div class="rounded-2xl border border-slate-700/60 bg-slate-800/40 p-4">
            <p class="text-slate-400 text-xs mb-3">Partagez ce lien pour qu'un restaurant s'inscrive sous votre compte et vous rapporte une commission.</p>
            <div class="flex gap-2 mb-3" x-data="{ copied: false }">
                <input type="text" readonly value="{{ $agent->parrainage_url }}"
                       class="flex-1 min-w-0 h-10 rounded-xl border border-slate-700 bg-slate-900/60 text-slate-300 text-xs px-3 truncate">
                <button @click="navigator.clipboard.writeText('{{ $agent->parrainage_url }}'); copied = true; setTimeout(() => copied = false, 2000)"
                        class="h-10 px-4 rounded-xl bg-slate-700 hover:bg-slate-600 text-white text-xs font-semibold transition shrink-0 min-w-[72px]">
                    <span x-show="!copied">Copier</span>
                    <span x-show="copied" class="text-emerald-400">Copié ✓</span>
                </button>
            </div>
            <a href="https://wa.me/?text={{ urlencode('Rejoins MenuPro et crée la vitrine digitale de ton restaurant : ' . $agent->parrainage_url) }}" target="_blank"
               class="flex items-center justify-center gap-2 w-full h-10 rounded-xl bg-emerald-600/15 border border-emerald-600/25 text-emerald-400 text-xs font-semibold hover:bg-emerald-600/25 transition">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                Partager sur WhatsApp
            </a>
        </div>
    </section>

    {{-- === RESTAURANTS PARRAINÉS === --}}
    @if($referredRestaurants->isNotEmpty())
    <section class="mb-6">
        <h3 class="text-white font-semibold text-sm mb-3">Restaurants parrainés</h3>
        <div class="space-y-2">
            @foreach($referredRestaurants as $restaurant)
                @php
                    if ($restaurant['is_truly_active']) {
                        $badgeClass = 'bg-emerald-500/10 text-emerald-400';
                        $badgeLabel = 'Actif';
                    } elseif ($restaurant['is_trial']) {
                        $badgeClass = 'bg-blue-500/10 text-blue-400';
                        $badgeLabel = 'Trial';
                    } elseif ($restaurant['status'] === 'suspended') {
                        $badgeClass = 'bg-amber-500/10 text-amber-400';
                        $badgeLabel = 'Suspendu';
                    } elseif ($restaurant['status'] === 'expired') {
                        $badgeClass = 'bg-red-500/10 text-red-400';
                        $badgeLabel = 'Expiré';
                    } else {
                        $badgeClass = 'bg-slate-700/40 text-slate-400';
                        $badgeLabel = 'En attente';
                    }
                @endphp
                <div class="flex items-center gap-3 px-4 py-3 rounded-2xl border border-slate-700/60 bg-slate-800/40">
                    <div class="w-8 h-8 rounded-xl bg-emerald-500/15 flex items-center justify-center shrink-0">
                        <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-2 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                    </div>
                    <div class="flex-1 min-w-0">
                        <p class="text-white text-sm font-medium truncate">{{ $restaurant['name'] }}</p>
                        <p class="text-slate-500 text-xs">
                            {{ $restaurant['plan'] }}
                            @if($restaurant['subscription_expires_at'])
                                · expire {{ $restaurant['subscription_expires_at'] }}
                            @endif
                        </p>
                        <p class="text-slate-600 text-[11px]">Inscrit le {{ $restaurant['created_at'] }}</p>
                    </div>
                    <span class="text-[10px] font-semibold px-2 py-0.5 rounded-lg {{ $badgeClass }}">
                        {{ $badgeLabel }}
                    </span>
                </div>
            @endforeach
        </div>
    </section>
    @endif

    {{-- === BADGE PDF === --}}
    <section class="mb-6">
        <h3 class="text-white font-semibold text-sm mb-3">Mon badge agent</h3>
        <div class="rounded-2xl border border-slate-700/60 bg-slate-800/40 p-4 flex items-center gap-4">
            <div class="w-12 h-12 rounded-2xl bg-orange-500/15 flex items-center justify-center shrink-0">
                <svg class="w-6 h-6 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H5a2 2 0 00-2 2v9a2 2 0 002 2h14a2 2 0 002-2V8a2 2 0 00-2-2h-5m-4 0V5a2 2 0 114 0v1m-4 0a2 2 0 104 0m-5 8a2 2 0 100-4 2 2 0 000 4z"/></svg>
            </div>
            <div class="flex-1 min-w-0">
                <p class="text-white text-sm font-semibold">Badge officiel</p>
                <p class="text-slate-400 text-xs mt-0.5">{{ $agent->badge_id }} · MenuPro</p>
            </div>
            <a href="{{ route('commando.card.download.pdf') }}" target="_blank"
               class="shrink-0 h-9 px-4 rounded-xl bg-orange-500 hover:bg-orange-600 text-white text-xs font-semibold transition flex items-center gap-1.5">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                Télécharger PDF
            </a>
        </div>
    </section>

    {{-- === HISTORIQUE TRANSACTIONS === --}}
    @if($walletHistory->isNotEmpty())
    <section class="mb-6" x-data="{ open: false }">
        <button @click="open = !open" class="w-full flex items-center justify-between mb-3">
            <h3 class="text-white font-semibold text-sm">Historique des paiements</h3>
            <svg :class="open ? 'rotate-180' : ''" class="w-4 h-4 text-slate-500 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>
        <div x-show="open" x-collapse>
            <div class="space-y-1.5">
                @foreach($walletHistory as $tx)
                    <div class="flex items-center justify-between px-4 py-3 rounded-2xl border border-slate-700/40 bg-slate-800/30">
                        <div>
                            <p class="text-white text-xs font-medium">{{ $tx->type->label() }}</p>
                            <p class="text-slate-500 text-[11px]">{{ $tx->created_at->format('d/m/Y') }}</p>
                        </div>
                        <span class="text-sm font-bold {{ $tx->amount_cents > 0 ? 'text-emerald-400' : 'text-red-400' }}">
                            {{ $tx->amount_cents > 0 ? '+' : '' }}{{ number_format($tx->amount_cents / 100, 0, ',', ' ') }} F
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
    </section>
    @endif

    @endif {{-- /canAccessParrainage --}}

    {{-- Leaderboard --}}
    <div class="bg-slate-800/40 rounded-2xl p-6 shadow-sm border border-slate-700/60 mb-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="font-semibold text-white flex items-center gap-2">
                🏆 Classement agents
            </h3>
            @if($this->myRank > 0)
            <span class="text-sm text-orange-400 font-medium">Votre rang : #{{ $this->myRank }}</span>
            @endif
        </div>
        <div class="space-y-3">
            @foreach($this->leaderboard as $index => $entry)
            <div class="flex items-center gap-3 {{ $entry['is_me'] ? 'bg-orange-500/10 rounded-lg p-2 -mx-2' : '' }}">
                <span class="text-lg font-bold {{ $index === 0 ? 'text-yellow-400' : ($index === 1 ? 'text-slate-400' : ($index === 2 ? 'text-amber-600' : 'text-slate-500')) }} w-6 text-center">
                    {{ $index === 0 ? '🥇' : ($index === 1 ? '🥈' : ($index === 2 ? '🥉' : '#' . ($index + 1))) }}
                </span>
                <div class="flex-1 min-w-0">
                    <p class="text-sm font-medium text-white truncate">
                        {{ $entry['name'] }}
                        @if($entry['is_me']) <span class="text-orange-400 text-xs">(vous)</span> @endif
                    </p>
                    <p class="text-xs text-slate-500">{{ $entry['city'] ?? '—' }} · {{ $entry['grade'] }}</p>
                </div>
                <span class="text-sm font-bold text-slate-300">{{ $entry['count'] }} <span class="text-xs text-slate-500">restaurants</span></span>
            </div>
            @endforeach
        </div>
    </div>

    {{-- Support --}}
    <div class="bg-gradient-to-r from-orange-500 to-orange-600 rounded-2xl p-6 text-white mb-6">
        <h3 class="font-semibold text-lg mb-2">Besoin d'aide ?</h3>
        <p class="text-orange-100 text-sm mb-4">Notre équipe support est disponible du lundi au vendredi, 8h-18h.</p>
        <div class="flex flex-wrap gap-3">
            <a href="https://wa.me/{{ preg_replace('/\D/', '', config('support.whatsapp', '2250000000000')) }}"
               target="_blank"
               class="flex items-center gap-2 bg-white/20 hover:bg-white/30 rounded-lg px-4 py-2 text-sm font-medium transition">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M17.472 14.382c-.297-.149-1.758-.867-2.03-.967-.273-.099-.471-.148-.67.15-.197.297-.767.966-.94 1.164-.173.199-.347.223-.644.075-.297-.15-1.255-.463-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.298-.347.446-.52.149-.174.198-.298.298-.497.099-.198.05-.371-.025-.52-.075-.149-.669-1.612-.916-2.207-.242-.579-.487-.5-.669-.51-.173-.008-.371-.01-.57-.01-.198 0-.52.074-.792.372-.272.297-1.04 1.016-1.04 2.479 0 1.462 1.065 2.875 1.213 3.074.149.198 2.096 3.2 5.077 4.487.709.306 1.262.489 1.694.625.712.227 1.36.195 1.871.118.571-.085 1.758-.719 2.006-1.413.248-.694.248-1.289.173-1.413-.074-.124-.272-.198-.57-.347m-5.421 7.403h-.004a9.87 9.87 0 01-5.031-1.378l-.361-.214-3.741.982.998-3.648-.235-.374a9.86 9.86 0 01-1.51-5.26c.001-5.45 4.436-9.884 9.888-9.884 2.64 0 5.122 1.03 6.988 2.898a9.825 9.825 0 012.893 6.994c-.003 5.45-4.437 9.884-9.885 9.884m8.413-18.297A11.815 11.815 0 0012.05 0C5.495 0 .16 5.335.157 11.892c0 2.096.547 4.142 1.588 5.945L.057 24l6.305-1.654a11.882 11.882 0 005.683 1.448h.005c6.554 0 11.89-5.335 11.893-11.893a11.821 11.821 0 00-3.48-8.413z"/></svg>
                WhatsApp Support
            </a>
            <a href="mailto:{{ config('support.email', 'support@menupro.ci') }}"
               class="flex items-center gap-2 bg-white/20 hover:bg-white/30 rounded-lg px-4 py-2 text-sm font-medium transition">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/></svg>
                Email Support
            </a>
        </div>
    </div>

    {{-- === MODAL RETRAIT === --}}
    @if($showWithdrawalModal)
        <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4 bg-black/70 backdrop-blur-sm"
             @click.self="$wire.set('showWithdrawalModal', false)">
            <div class="w-full sm:max-w-sm rounded-2xl border border-slate-700 bg-[#0f172a] p-6 shadow-2xl">
                <h3 class="text-white font-bold text-base mb-1">Demande de retrait</h3>
                <p class="text-slate-400 text-sm mb-4">Solde disponible : <span class="text-white font-semibold">{{ number_format($agent->balance, 0, ',', ' ') }} FCFA</span></p>

                {{-- Montant --}}
                <input type="number" wire:model="withdrawalAmount" placeholder="Montant en FCFA" min="1"
                       class="w-full h-11 px-4 rounded-xl border border-slate-700 bg-slate-800 text-white text-sm placeholder-slate-500 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 mb-1 transition">
                @error('withdrawalAmount') <p class="text-red-400 text-xs mb-2">{{ $message }}</p> @enderror

                {{-- Mode de paiement --}}
                <div class="mt-3">
                    <label class="block text-slate-400 text-xs mb-1">Mode de paiement</label>
                    <select wire:model.live="withdrawalMethod"
                            class="w-full h-11 px-4 rounded-xl border border-slate-700 bg-slate-800 text-white text-sm focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition">
                        <option value="wave">Wave</option>
                        <option value="orange_money">Orange Money</option>
                        <option value="mtn_money">MTN Money</option>
                    </select>
                    @error('withdrawalMethod') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                {{-- Numéro --}}
                <div class="mt-3">
                    <label class="block text-slate-400 text-xs mb-1">
                        Numéro
                        @if($withdrawalMethod === 'wave') Wave
                        @elseif($withdrawalMethod === 'orange_money') Orange Money
                        @else MTN Money
                        @endif
                    </label>
                    <input type="tel" wire:model="withdrawalPhone" placeholder="+225 07 XX XX XX XX"
                           class="w-full h-11 px-4 rounded-xl border border-slate-700 bg-slate-800 text-white text-sm placeholder-slate-500 focus:ring-2 focus:ring-orange-500 focus:border-orange-500 transition">
                    @error('withdrawalPhone') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="flex gap-2 mt-4">
                    <button wire:click="$set('showWithdrawalModal', false)"
                            class="flex-1 h-11 rounded-xl border border-slate-700 text-slate-300 text-sm font-medium hover:bg-slate-800 transition">
                        Annuler
                    </button>
                    <button wire:click="requestWithdrawal"
                            class="flex-1 h-11 rounded-xl bg-orange-500 hover:bg-orange-600 text-white text-sm font-bold transition">
                        Envoyer
                    </button>
                </div>
            </div>
        </div>
    @endif
</div>
