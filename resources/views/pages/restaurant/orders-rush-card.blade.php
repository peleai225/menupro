@php
    $statusColors = [
        'paid' => 'bg-emerald-100 text-emerald-700 border-emerald-200',
        'confirmed' => 'bg-blue-100 text-blue-700 border-blue-200',
        'preparing' => 'bg-amber-100 text-amber-700 border-amber-200',
        'ready' => 'bg-purple-100 text-purple-700 border-purple-200',
    ];
    $statusColor = $statusColors[$order->status->value] ?? 'bg-neutral-100 text-neutral-700 border-neutral-200';
@endphp

<div class="card p-4 hover:shadow-md transition-shadow" id="order-{{ $order->id }}">
    <div class="flex items-start justify-between gap-4">
        <div class="flex-1 min-w-0">
            <div class="flex items-center gap-3 mb-2">
                <span class="font-mono text-sm font-bold text-neutral-900">#{{ $order->reference }}</span>
                <span class="px-2 py-0.5 rounded text-xs font-medium border {{ $statusColor }}">
                    {{ $order->status->label() }}
                </span>
                <span class="text-xs text-neutral-500">{{ $order->created_at->diffForHumans() }}</span>
            </div>
            <div class="text-sm font-medium text-neutral-900 mb-1">
                {{ $order->customer_name ?? 'Client' }} • {{ $order->items->count() }} article(s)
            </div>
            <div class="text-xs text-neutral-500 line-clamp-2 mb-2">
                @foreach($order->items->take(3) as $item)
                    {{ $item->quantity }}x {{ $item->dish_name }}@if(!$loop->last), @endif
                @endforeach
                @if($order->items->count() > 3)
                    <span class="text-neutral-400">+{{ $order->items->count() - 3 }} autre(s)</span>
                @endif
            </div>
            <div class="text-lg font-bold text-primary-600">
                {{ number_format($order->total, 0, ',', ' ') }} F
            </div>
        </div>
        <div class="flex flex-col gap-2">
            @if($order->status === \App\Enums\OrderStatus::PAID)
                <form action="{{ route('restaurant.orders.rush.confirm', $order) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="btn-primary btn-sm whitespace-nowrap w-full">
                        ✓ Confirmer
                    </button>
                </form>
                <form action="{{ route('restaurant.orders.rush.prepare', $order) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="btn-secondary btn-sm whitespace-nowrap w-full">
                        🍳 Préparer
                    </button>
                </form>
            @elseif($order->status === \App\Enums\OrderStatus::CONFIRMED)
                <form action="{{ route('restaurant.orders.rush.prepare', $order) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="btn-primary btn-sm whitespace-nowrap w-full">
                        🍳 Préparer
                    </button>
                </form>
            @elseif($order->status === \App\Enums\OrderStatus::PREPARING)
                <form action="{{ route('restaurant.orders.rush.ready', $order) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="btn-primary btn-sm whitespace-nowrap w-full">
                        ✓ Prête
                    </button>
                </form>
            @elseif($order->status === \App\Enums\OrderStatus::READY)
                <form action="{{ route('restaurant.orders.rush.complete', $order) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="btn-primary btn-sm whitespace-nowrap w-full">
                        ✓ Terminer
                    </button>
                </form>
            @endif
            <a href="{{ route('restaurant.orders.show', $order) }}" 
               class="btn-ghost btn-sm text-center whitespace-nowrap">
                Détails
            </a>
        </div>
    </div>
</div>
