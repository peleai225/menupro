@php
    $restaurant = auth()->user()->restaurant;
    $subscription = $restaurant?->activeSubscription;
@endphp

<x-layouts.admin-restaurant title="Board cuisine" :restaurant="$restaurant" :subscription="$subscription">
    <div class="space-y-6">
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-2xl font-bold text-neutral-900">Board cuisine</h1>
                <p class="text-neutral-500 mt-1">
                    Visualisez les commandes en temps réel pour la cuisine.
                </p>
            </div>
            <div class="flex items-center gap-2 text-sm text-neutral-500">
                <span class="w-2 h-2 rounded-full bg-emerald-500 animate-pulse"></span>
                <span>Auto‑refresh Livewire (aucune action nécessaire)</span>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-6">
            {{-- Nouvelle commandes (payées / confirmées) --}}
            <div class="bg-white rounded-2xl shadow-sm border border-neutral-100 flex flex-col max-h-[80vh]">
                <div class="px-4 py-3 border-b border-neutral-100 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                        <h2 class="font-semibold text-neutral-900">Nouvelles</h2>
                    </div>
                    <span class="text-xs text-neutral-500">{{ count($orders['new'] ?? []) }} commande(s)</span>
                </div>
                <div class="p-3 space-y-3 overflow-y-auto">
                    @forelse($orders['new'] ?? [] as $order)
                        <div class="bg-emerald-50 border border-emerald-100 rounded-xl p-3 space-y-1">
                            <div class="flex items-center justify-between">
                                <span class="font-mono text-xs font-semibold text-emerald-900">#{{ $order->reference }}</span>
                                <span class="text-[11px] text-neutral-500">{{ $order->created_at->format('H:i') }}</span>
                            </div>
                            <div class="text-sm font-medium text-neutral-900">
                                {{ $order->customer_name ?? 'Client' }} • {{ $order->items_count }} plat(s)
                            </div>
                            <div class="text-xs text-neutral-500">
                                @foreach($order->items as $item)
                                    {{ $item->quantity }}x {{ $item->dish_name }}@if(!$loop->last), @endif
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-neutral-400 text-center py-6">Aucune commande nouvelle</p>
                    @endforelse
                </div>
            </div>

            {{-- En préparation --}}
            <div class="bg-white rounded-2xl shadow-sm border border-neutral-100 flex flex-col max-h-[80vh]">
                <div class="px-4 py-3 border-b border-neutral-100 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                        <h2 class="font-semibold text-neutral-900">En préparation</h2>
                    </div>
                    <span class="text-xs text-neutral-500">{{ count($orders['preparing'] ?? []) }} commande(s)</span>
                </div>
                <div class="p-3 space-y-3 overflow-y-auto">
                    @forelse($orders['preparing'] ?? [] as $order)
                        <div class="bg-amber-50 border border-amber-100 rounded-xl p-3 space-y-1">
                            <div class="flex items-center justify-between">
                                <span class="font-mono text-xs font-semibold text-amber-900">#{{ $order->reference }}</span>
                                <span class="text-[11px] text-neutral-500">{{ $order->created_at->format('H:i') }}</span>
                            </div>
                            <div class="text-sm font-medium text-neutral-900">
                                {{ $order->customer_name ?? 'Client' }} • {{ $order->items_count }} plat(s)
                            </div>
                            <div class="text-xs text-neutral-500">
                                @foreach($order->items as $item)
                                    {{ $item->quantity }}x {{ $item->dish_name }}@if(!$loop->last), @endif
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-neutral-400 text-center py-6">Aucune commande en préparation</p>
                    @endforelse
                </div>
            </div>

            {{-- Prêtes --}}
            <div class="bg-white rounded-2xl shadow-sm border border-neutral-100 flex flex-col max-h-[80vh]">
                <div class="px-4 py-3 border-b border-neutral-100 flex items-center justify-between">
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 rounded-full bg-blue-500"></span>
                        <h2 class="font-semibold text-neutral-900">Prêtes</h2>
                    </div>
                    <span class="text-xs text-neutral-500">{{ count($orders['ready'] ?? []) }} commande(s)</span>
                </div>
                <div class="p-3 space-y-3 overflow-y-auto">
                    @forelse($orders['ready'] ?? [] as $order)
                        <div class="bg-blue-50 border border-blue-100 rounded-xl p-3 space-y-1">
                            <div class="flex items-center justify-between">
                                <span class="font-mono text-xs font-semibold text-blue-900">#{{ $order->reference }}</span>
                                <span class="text-[11px] text-neutral-500">{{ $order->created_at->format('H:i') }}</span>
                            </div>
                            <div class="text-sm font-medium text-neutral-900">
                                {{ $order->customer_name ?? 'Client' }} • {{ $order->items_count }} plat(s)
                            </div>
                            <div class="text-xs text-neutral-500">
                                @foreach($order->items as $item)
                                    {{ $item->quantity }}x {{ $item->dish_name }}@if(!$loop->last), @endif
                                @endforeach
                            </div>
                        </div>
                    @empty
                        <p class="text-xs text-neutral-400 text-center py-6">Aucune commande prête</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin-restaurant>


