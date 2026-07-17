<div class="relative flex items-center gap-2"
     x-data="{
         open: @entangle('showDropdown'),
         soundEnabled: localStorage.getItem('notificationSound') !== 'false',
         showNewBadge: false,
         showServiceAlert: false,
         audioCtx: null,
         audioUnlocked: false,
         init() {
             this.requestNotificationPermission();
             this.unlockAudio();
             this.setupVisibilityPause();
         },
         setupVisibilityPause() {
             document.addEventListener('visibilitychange', () => {
                 if (document.hidden) {
                     window._livewirePollingPaused = true;
                 } else {
                     window._livewirePollingPaused = false;
                 }
             });
         },
         unlockAudio() {
             const unlock = () => {
                 if (this.audioUnlocked) return;
                 this.audioCtx = new (window.AudioContext || window.webkitAudioContext)();
                 if (this.audioCtx.state === 'suspended') this.audioCtx.resume();
                 this.audioUnlocked = true;
                 document.removeEventListener('click', unlock);
                 document.removeEventListener('touchstart', unlock);
             };
             document.addEventListener('click', unlock, { once: true });
             document.addEventListener('touchstart', unlock, { once: true });
         },
         onNewNotification() {
             this.showNewBadge = true;
             setTimeout(() => this.showNewBadge = false, 5000);
             if (this.soundEnabled) this.playNotificationSound();
             this.showBrowserNotification('Nouvelle commande !', 'Vous avez reçu une nouvelle commande');
         },
         onNewServiceRequest() {
             this.showServiceAlert = true;
             if (this.soundEnabled) this.playServiceAlertSound();
             this.showBrowserNotification('Appel client !', 'Un client demande de l\'aide');
         },
         playNotificationSound() {
             if (!this.audioUnlocked || !this.audioCtx) return;
             try {
                 if (this.audioCtx.state === 'suspended') this.audioCtx.resume();
                 const ctx = this.audioCtx;
                 [880, 1100, 1320, 880].forEach(function(freq, i) {
                     setTimeout(function() {
                         const o = ctx.createOscillator(), g = ctx.createGain();
                         o.connect(g); g.connect(ctx.destination);
                         o.frequency.value = freq;
                         o.type = i === 3 ? 'triangle' : 'sine';
                         const d = i === 3 ? 0.5 : 0.28;
                         g.gain.setValueAtTime(i === 3 ? 0.22 : 0.35, ctx.currentTime);
                         g.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + d);
                         o.start(ctx.currentTime); o.stop(ctx.currentTime + d);
                     }, i * 130);
                 });
             } catch (e) {}
         },
         playServiceAlertSound() {
             if (!this.audioUnlocked || !this.audioCtx) return;
             try {
                 if (this.audioCtx.state === 'suspended') this.audioCtx.resume();
                 const ctx = this.audioCtx;
                 [660, 880, 660, 880].forEach(function(freq, i) {
                     setTimeout(function() {
                         const o = ctx.createOscillator(), g = ctx.createGain();
                         o.connect(g); g.connect(ctx.destination);
                         o.frequency.value = freq;
                         o.type = 'sine';
                         g.gain.setValueAtTime(0.4, ctx.currentTime);
                         g.gain.exponentialRampToValueAtTime(0.001, ctx.currentTime + 0.25);
                         o.start(ctx.currentTime); o.stop(ctx.currentTime + 0.25);
                     }, i * 150);
                 });
             } catch (e) {}
         },
         showBrowserNotification(title, body) {
             if ('Notification' in window && Notification.permission === 'granted') {
                 new Notification(title, { body: body, icon: '/favicon.svg', tag: title });
             }
         },
         toggleSound() {
             this.soundEnabled = !this.soundEnabled;
             localStorage.setItem('notificationSound', this.soundEnabled);
             if (this.soundEnabled) this.playNotificationSound();
         },
         requestNotificationPermission() {
             if ('Notification' in window && Notification.permission === 'default') {
                 Notification.requestPermission();
             }
         }
     }"
     @click.away="open = false"
     @new-notification-arrived.window="onNewNotification()"
     @new-service-request.window="onNewServiceRequest()"
     @open-notifications.window="open = true"
     wire:poll.10s="checkForNewNotifications">

    {{-- Bouton appels clients --}}
    @if($serviceRequests->count() > 0)
    <div x-data="{ srOpen: false }" class="relative flex-shrink-0">
        <button @click="srOpen = !srOpen"
                class="flex items-center gap-1.5 px-2.5 py-1.5 bg-violet-600 hover:bg-violet-700 text-white rounded-lg transition-colors animate-pulse"
                title="{{ $serviceRequests->count() }} appel(s) client">
            <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            <span class="text-xs font-bold hidden sm:inline">Appels</span>
            <span class="w-5 h-5 bg-white text-violet-700 text-xs font-bold rounded-full flex items-center justify-center flex-shrink-0">{{ $serviceRequests->count() }}</span>
        </button>

        <div x-show="srOpen"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             @click.outside="srOpen = false"
             class="fixed sm:absolute left-4 right-4 sm:left-auto sm:right-0 top-14 sm:top-full sm:mt-2 sm:w-80 bg-white rounded-xl shadow-2xl border border-violet-200 overflow-hidden max-h-[75vh] sm:max-h-80 overflow-y-auto"
             style="z-index: 9999;"
             x-cloak>
            <div class="p-3 bg-violet-50 border-b border-violet-100 flex items-center gap-2">
                <div class="w-7 h-7 bg-violet-500 rounded-lg flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                </div>
                <div>
                    <p class="font-bold text-violet-900 text-sm">Appels du personnel</p>
                    <p class="text-xs text-violet-600">{{ $serviceRequests->count() }} en attente</p>
                </div>
            </div>
            <div class="divide-y divide-neutral-100 max-h-72 overflow-y-auto">
                @foreach($serviceRequests as $req)
                <div class="flex items-center gap-3 p-3 hover:bg-neutral-50" wire:key="sr-top-{{ $req->id }}">
                    <div class="w-9 h-9 bg-violet-100 rounded-lg flex items-center justify-center text-lg flex-shrink-0">
                        {{ $req->typeIcon() }}
                    </div>
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-1.5 flex-wrap">
                            <span class="font-semibold text-sm text-neutral-900">{{ $req->table_number }}</span>
                            <span class="text-xs text-violet-700 font-medium">{{ $req->typeLabel() }}</span>
                        </div>
                        @if($req->notes)
                            <p class="text-xs text-neutral-500 truncate">"{{ $req->notes }}"</p>
                        @endif
                        <p class="text-xs text-neutral-400 mt-0.5">{{ $req->created_at->diffForHumans(['locale' => 'fr']) }}</p>
                    </div>
                    <button wire:click="markServiceRequestDone({{ $req->id }})"
                            wire:loading.attr="disabled"
                            class="flex-shrink-0 px-3 py-1.5 bg-violet-600 hover:bg-violet-700 text-white text-xs font-bold rounded-lg transition-colors">
                        <span wire:loading.remove wire:target="markServiceRequestDone({{ $req->id }})">✓ OK</span>
                        <span wire:loading wire:target="markServiceRequestDone({{ $req->id }})">...</span>
                    </button>
                </div>
                @endforeach
            </div>
        </div>
    </div>
    @endif

    {{-- Cloche notifications — wrapper dédié pour positionnement correct --}}
    <div class="relative flex-shrink-0">
        {{-- Badge "Nouvelle commande" --}}
        <div x-show="showNewBadge"
             x-transition:enter="transition ease-out duration-300"
             x-transition:enter-start="opacity-0 -translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-200"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="absolute -top-8 left-1/2 -translate-x-1/2 whitespace-nowrap pointer-events-none"
             style="z-index: 9999;"
             x-cloak>
            <div class="bg-primary-500 text-white text-xs font-bold px-3 py-1 rounded-full shadow-lg animate-pulse">
                Nouvelle commande !
            </div>
        </div>

        {{-- Bouton cloche --}}
        <button @click="open = !open"
                class="relative p-2.5 rounded-lg hover:bg-neutral-100 transition-colors min-h-[44px] min-w-[44px] flex items-center justify-center"
                :class="{ 'bg-primary-50': showNewBadge }">
            <svg class="w-6 h-6 text-neutral-600" :class="{ 'text-primary-500': showNewBadge }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
            </svg>
            @if($this->unreadCount > 0)
                <span class="absolute top-0.5 right-0.5 min-w-[18px] h-[18px] bg-red-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center px-1">
                    {{ $this->unreadCount > 99 ? '99+' : $this->unreadCount }}
                </span>
            @endif
        </button>

        {{-- Dropdown notifications --}}
        <div x-show="open"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-1"
             x-transition:enter-end="opacity-100 translate-y-0"
             x-transition:leave="transition ease-in duration-150"
             x-transition:leave-start="opacity-100"
             x-transition:leave-end="opacity-0"
             class="fixed sm:absolute left-4 right-4 sm:left-auto sm:right-0 top-14 sm:top-full sm:mt-2 sm:w-96 bg-white rounded-xl shadow-2xl border border-neutral-200 max-h-[75vh] sm:max-h-[600px] overflow-hidden flex flex-col"
             style="z-index: 9999;"
             x-cloak>
        <!-- Header -->
        <div class="p-4 border-b border-neutral-200">
            <div class="flex items-center justify-between mb-2">
                <h3 class="text-lg font-bold text-neutral-900">Notifications</h3>
                @if($this->unreadCount > 0)
                    <button wire:click="markAllAsRead" 
                            class="text-sm text-primary-600 hover:text-primary-700 font-medium">
                        Tout marquer comme lu
                    </button>
                @endif
            </div>
            <!-- Sound Toggle -->
            <div class="flex items-center justify-between text-sm">
                <span class="text-neutral-500">Son des notifications</span>
                <button @click="toggleSound()" 
                        class="p-1.5 rounded-lg transition-colors"
                        :class="soundEnabled ? 'bg-primary-100 text-primary-600' : 'bg-neutral-100 text-neutral-400'"
                        :title="soundEnabled ? 'Désactiver le son' : 'Activer le son'">
                    <svg x-show="soundEnabled" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.536 8.464a5 5 0 010 7.072m2.828-9.9a9 9 0 010 12.728M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/>
                    </svg>
                    <svg x-show="!soundEnabled" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-cloak>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.586 15H4a1 1 0 01-1-1v-4a1 1 0 011-1h1.586l4.707-4.707C10.923 3.663 12 4.109 12 5v14c0 .891-1.077 1.337-1.707.707L5.586 15z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Notifications List -->
        <div class="overflow-y-auto flex-1">
            @if($notifications->count() > 0)
                <div class="divide-y divide-neutral-100">
                    @foreach($notifications as $notification)
                        @php
                            $data = $notification->data;
                            $isRead = $notification->read_at !== null;
                        @endphp
                        <div wire:key="notification-{{ $notification->id }}"
                             class="p-4 hover:bg-neutral-50 transition-colors {{ !$isRead ? 'bg-primary-50/30' : '' }}">
                            <div class="flex items-start gap-3">
                                <!-- Icon -->
                                <div class="flex-shrink-0 mt-0.5">
                                    @if($data['type'] ?? null === 'new_order')
                                        <div class="w-10 h-10 bg-primary-100 rounded-full flex items-center justify-center">
                                            <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                                            </svg>
                                        </div>
                                    @elseif($data['type'] ?? null === 'new_reservation')
                                        <div class="w-10 h-10 bg-green-100 rounded-full flex items-center justify-center">
                                            <svg class="w-5 h-5 text-green-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                            </svg>
                                        </div>
                                    @elseif($data['type'] ?? null === 'low_stock')
                                        <div class="w-10 h-10 bg-accent-100 rounded-full flex items-center justify-center">
                                            <svg class="w-5 h-5 text-accent-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                            </svg>
                                        </div>
                                    @elseif($data['type'] ?? null === 'subscription_expiring' || ($data['type'] ?? null === 'subscription_expired'))
                                        <div class="w-10 h-10 bg-secondary-100 rounded-full flex items-center justify-center">
                                            <svg class="w-5 h-5 text-secondary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/>
                                            </svg>
                                        </div>
                                    @else
                                        <div class="w-10 h-10 bg-neutral-100 rounded-full flex items-center justify-center">
                                            <svg class="w-5 h-5 text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                            </svg>
                                        </div>
                                    @endif
                                </div>

                                <!-- Content -->
                                <div class="flex-1 min-w-0">
                                    <p class="text-sm font-medium text-neutral-900 mb-1">
                                        {{ $data['message'] ?? 'Notification' }}
                                    </p>
                                    <p class="text-xs text-neutral-500">
                                        {{ $notification->created_at->locale('fr')->diffForHumans() }}
                                    </p>
                                    
                                    <!-- Action Link -->
                                    @if(isset($data['order_id']) && $data['type'] === 'new_order')
                                        <a href="{{ route('restaurant.orders.show', $data['order_id']) }}" 
                                           wire:click="markAsRead('{{ $notification->id }}')"
                                           class="text-xs text-primary-600 hover:text-primary-700 font-medium mt-2 inline-block">
                                            Voir la commande →
                                        </a>
                                    @elseif(isset($data['reservation_id']) && $data['type'] === 'new_reservation')
                                        <a href="{{ route('restaurant.reservations.show', $data['reservation_id']) }}" 
                                           wire:click="markAsRead('{{ $notification->id }}')"
                                           class="text-xs text-primary-600 hover:text-primary-700 font-medium mt-2 inline-block">
                                            Voir la réservation →
                                        </a>
                                    @elseif($data['type'] === 'low_stock')
                                        <a href="{{ route('restaurant.stock.alerts') }}" 
                                           wire:click="markAsRead('{{ $notification->id }}')"
                                           class="text-xs text-primary-600 hover:text-primary-700 font-medium mt-2 inline-block">
                                            Voir le stock →
                                        </a>
                                    @elseif($data['type'] === 'subscription_expiring' || $data['type'] === 'subscription_expired')
                                        <a href="{{ route('restaurant.subscription') }}" 
                                           wire:click="markAsRead('{{ $notification->id }}')"
                                           class="text-xs text-primary-600 hover:text-primary-700 font-medium mt-2 inline-block">
                                            Voir l'abonnement →
                                        </a>
                                    @endif
                                </div>

                                <!-- Actions -->
                                <div class="flex-shrink-0 flex items-center gap-1">
                                    @if(!$isRead)
                                        <button wire:click="markAsRead('{{ $notification->id }}')" 
                                                class="p-1 text-neutral-400 hover:text-neutral-600 transition-colors"
                                                title="Marquer comme lu">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                            </svg>
                                        </button>
                                    @endif
                                    <button wire:click="delete('{{ $notification->id }}')" 
                                            class="p-1 text-neutral-400 hover:text-red-600 transition-colors"
                                            title="Supprimer">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    @endforeach
                </div>
            @else
                <div class="p-8 text-center">
                    <svg class="w-12 h-12 text-neutral-300 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                    </svg>
                    <p class="text-neutral-500 text-sm">Aucune notification</p>
                </div>
            @endif
        </div>

        <!-- Footer -->
        @if($notifications->hasPages())
            <div class="p-4 border-t border-neutral-200">
                {{ $notifications->links('pagination::simple-tailwind') }}
            </div>
        @endif
    </div>{{-- /dropdown notifications --}}
    </div>{{-- /wrapper cloche --}}
</div>{{-- /composant racine --}}

