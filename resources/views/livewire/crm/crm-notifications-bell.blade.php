<div class="relative" wire:poll.30s="refresh" x-data="{ open: @entangle('open') }">

    {{-- Bell button --}}
    <button wire:click="toggleOpen"
            class="relative p-2 text-gray-400 hover:text-white transition rounded-xl hover:bg-gray-800/50"
            title="Notifications">
        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                  d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
        </svg>
        @if($unreadCount > 0)
        <span class="absolute -top-0.5 -right-0.5 min-w-[18px] h-[18px] bg-orange-500 text-white text-[10px] font-bold rounded-full flex items-center justify-center px-1 animate-pulse"
              wire:key="badge-{{ $unreadCount }}">
            {{ $unreadCount > 99 ? '99+' : $unreadCount }}
        </span>
        @endif
    </button>

    {{-- Dropdown panel --}}
    <div x-show="open"
         x-transition:enter="transition ease-out duration-150"
         x-transition:enter-start="opacity-0 scale-95 translate-y-1"
         x-transition:enter-end="opacity-100 scale-100 translate-y-0"
         x-transition:leave="transition ease-in duration-100"
         x-transition:leave-start="opacity-100 scale-100"
         x-transition:leave-end="opacity-0 scale-95"
         @click.outside="open = false; $wire.open = false"
         class="absolute right-0 mt-2 w-80 sm:w-96 bg-gray-900 border border-gray-800 rounded-2xl shadow-2xl shadow-black/40 z-50 overflow-hidden"
         style="display: none">

        {{-- Header --}}
        <div class="flex items-center justify-between px-4 py-3 border-b border-gray-800">
            <h3 class="text-sm font-semibold text-white">Notifications</h3>
            @if($unreadCount > 0)
            <button wire:click="markAllRead" class="text-xs text-orange-400 hover:text-orange-300 transition">
                Tout marquer lu
            </button>
            @endif
        </div>

        {{-- List --}}
        <div class="max-h-80 overflow-y-auto divide-y divide-gray-800/60 scrollbar-thin">
            @forelse($this->notifications as $notif)
            @php $data = $notif->data; @endphp
            <div wire:key="notif-{{ $notif->id }}"
                 class="flex items-start gap-3 px-4 py-3 hover:bg-gray-800/40 transition-colors cursor-default
                        {{ $notif->read_at ? 'opacity-60' : '' }}">
                {{-- Icon --}}
                <div class="mt-0.5 flex-shrink-0 w-8 h-8 rounded-xl flex items-center justify-center
                            {{ ($data['type'] ?? '') === 'commission_credited' ? 'bg-emerald-500/20 text-emerald-400'
                              : (($data['type'] ?? '') === 'lead_assigned' ? 'bg-blue-500/20 text-blue-400'
                              : 'bg-orange-500/20 text-orange-400') }}">
                    @if(($data['type'] ?? '') === 'commission_credited')
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    @elseif(($data['type'] ?? '') === 'lead_assigned')
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                    @else
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                              d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    @endif
                </div>

                {{-- Content --}}
                <div class="flex-1 min-w-0">
                    <p class="text-xs font-semibold text-gray-200">{{ $data['title'] ?? 'Notification' }}</p>
                    <p class="text-xs text-gray-400 mt-0.5 line-clamp-2">{{ $data['body'] ?? '' }}</p>
                    @if(!empty($data['amount_formatted']))
                    <span class="inline-block mt-1 text-xs font-bold text-emerald-400">
                        + {{ $data['amount_formatted'] }}
                    </span>
                    @endif
                    <p class="text-[10px] text-gray-600 mt-1">
                        {{ $notif->created_at->diffForHumans() }}
                    </p>
                </div>

                {{-- Unread dot --}}
                @if(!$notif->read_at)
                <button wire:click="markRead('{{ $notif->id }}')"
                        class="flex-shrink-0 mt-1 w-2 h-2 bg-orange-500 rounded-full hover:bg-gray-600 transition-colors"
                        title="Marquer comme lu">
                </button>
                @endif
            </div>
            @empty
            <div class="py-10 text-center">
                <svg class="w-10 h-10 mx-auto text-gray-700 mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5"
                          d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                </svg>
                <p class="text-sm text-gray-500">Aucune notification</p>
            </div>
            @endforelse
        </div>
    </div>
</div>
