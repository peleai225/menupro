<div class="order-card bg-white border border-neutral-200 rounded-lg p-3 cursor-move hover:shadow-md transition-shadow" 
     data-order-id="{{ $order->id }}">
    <div class="flex items-center justify-between mb-2">
        <span class="font-mono text-xs font-semibold text-neutral-900">#{{ $order->reference }}</span>
        <span class="text-xs text-neutral-500">{{ $order->created_at->format('H:i') }}</span>
    </div>
    <div class="text-sm font-medium text-neutral-900 mb-1">
        {{ $order->customer_name ?? 'Client' }} • {{ $order->items->count() }} article(s)
    </div>
    <div class="text-xs text-neutral-500 mb-2 line-clamp-2">
        @foreach($order->items->take(3) as $item)
            {{ $item->quantity }}x {{ $item->dish_name }}@if(!$loop->last), @endif
        @endforeach
        @if($order->items->count() > 3)
            <span class="text-neutral-400">+{{ $order->items->count() - 3 }} autre(s)</span>
        @endif
    </div>
    <div class="flex items-center justify-between">
        <span class="text-sm font-bold text-primary-600">{{ number_format($order->total, 0, ',', ' ') }} F</span>
        <a href="{{ route('restaurant.orders.show', $order) }}" 
           class="text-xs text-primary-600 hover:underline"
           onclick="event.stopPropagation()">Voir</a>
    </div>
    @if($order->type->requiresAddress() && $order->delivery_address)
        <div class="mt-2 pt-2 border-t border-neutral-100">
            <div class="flex items-start gap-1 text-xs text-neutral-500">
                <svg class="w-3 h-3 mt-0.5 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
                <span class="line-clamp-1">{{ Str::limit($order->delivery_address, 30) }}</span>
            </div>
        </div>
    @endif
</div>
