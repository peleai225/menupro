<div class="relative" 
     x-data="{ 
         open: @entangle('showDropdown'),
         soundEnabled: localStorage.getItem('notificationSound') !== 'false',
         showNewBadge: false,
         onNewNotification() {
             console.log('New notification received!');
             // Show visual badge animation
             this.showNewBadge = true;
             setTimeout(() => this.showNewBadge = false, 5000);
             
             // Play notification sound if enabled
             if (this.soundEnabled) {
                 this.playNotificationSound();
             }
             
             // Show browser notification if permitted
             this.showBrowserNotification();
         },
         playNotificationSound() {
             // Create and play a notification sound using Web Audio API
             try {
                 const audioContext = new (window.AudioContext || window.webkitAudioContext)();
                 
                 // First chime
                 const osc1 = audioContext.createOscillator();
                 const gain1 = audioContext.createGain();
                 osc1.connect(gain1);
                 gain1.connect(audioContext.destination);
                 osc1.frequency.value = 880;
                 gain1.gain.setValueAtTime(0.3, audioContext.currentTime);
                 gain1.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.3);
                 osc1.start(audioContext.currentTime);
                 osc1.stop(audioContext.currentTime + 0.3);
                 
                 // Second chime (higher)
                 const osc2 = audioContext.createOscillator();
                 const gain2 = audioContext.createGain();
                 osc2.connect(gain2);
                 gain2.connect(audioContext.destination);
                 osc2.frequency.value = 1174.66;
                 gain2.gain.setValueAtTime(0, audioContext.currentTime);
                 gain2.gain.setValueAtTime(0.3, audioContext.currentTime + 0.15);
                 gain2.gain.exponentialRampToValueAtTime(0.01, audioContext.currentTime + 0.5);
                 osc2.start(audioContext.currentTime + 0.15);
                 osc2.stop(audioContext.currentTime + 0.5);
                 
                 console.log('Sound played!');
             } catch (e) {
                 console.log('Audio playback not available:', e);
             }
         },
         showBrowserNotification() {
             if ('Notification' in window && Notification.permission === 'granted') {
                 new Notification('Nouvelle commande !', {
                     body: 'Vous avez reçu une nouvelle commande',
                     icon: '/favicon.svg',
                     tag: 'new-order'
                 });
             }
         },
         toggleSound() {
             this.soundEnabled = !this.soundEnabled;
             localStorage.setItem('notificationSound', this.soundEnabled);
             if (this.soundEnabled) {
                 this.playNotificationSound();
             }
         },
         requestNotificationPermission() {
             if ('Notification' in window && Notification.permission === 'default') {
                 Notification.requestPermission();
             }
         }
     }" 
     x-init="requestNotificationPermission()"
     @click.away="open = false" 
     @new-notification-arrived.window="onNewNotification()"
     wire:poll.5s="checkForNewNotifications">
    <!-- New Notification Alert Badge -->
    <div x-show="showNewBadge"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 -translate-y-2"
         x-transition:enter-end="opacity-100 translate-y-0"
         x-transition:leave="transition ease-in duration-200"
         x-transition:leave-start="opacity-100"
         x-transition:leave-end="opacity-0"
         class="absolute -top-2 right-0 transform translate-x-full whitespace-nowrap z-50"
         x-cloak>
        <div class="bg-primary-500 text-white text-xs font-bold px-3 py-1.5 rounded-full shadow-lg animate-pulse flex items-center gap-1.5">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
            </svg>
            Nouvelle commande !
        </div>
    </div>

    <!-- Notification Bell -->
    <button @click="open = !open" 
            class="relative p-2.5 rounded-lg hover:bg-neutral-100 transition-colors min-h-[44px] min-w-[44px] flex items-center justify-center"
            :class="{ 'animate-bounce': showNewBadge }">
        <svg class="w-6 h-6 text-neutral-600" :class="{ 'text-primary-500': showNewBadge }" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        @if($this->unreadCount > 0)
            <span class="absolute top-0 right-0 w-5 h-5 bg-red-500 text-white text-xs font-bold rounded-full flex items-center justify-center"
                  :class="{ 'animate-ping-once': showNewBadge }">
                {{ $this->unreadCount > 99 ? '99+' : $this->unreadCount }}
            </span>
        @endif
    </button>

    <!-- Dropdown -->
    <div x-show="open"
         x-transition:enter="transition ease-out duration-200"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100"
         x-transition:leave="transition ease-in duration-150"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         class="absolute right-0 mt-2 w-[calc(100vw-2rem)] max-w-96 sm:w-96 bg-white rounded-xl shadow-xl border border-neutral-200 z-50 max-h-[70vh] sm:max-h-[600px] overflow-hidden flex flex-col"
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
    </div>
</div>

