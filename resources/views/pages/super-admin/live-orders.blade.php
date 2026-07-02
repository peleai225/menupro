<x-layouts.admin-super title="Commandes en cours">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-neutral-900">Commandes en cours</h1>
            <p class="text-neutral-500 text-sm mt-1">Vue temps réel de toutes les commandes actives</p>
        </div>
        <div class="flex items-center gap-2" x-data="{ refreshing: false }" >
            <button @click="refreshing = true; setTimeout(() => location.reload(), 100)"
                    class="btn btn-outline border-neutral-300 text-neutral-600 hover:bg-neutral-100">
                <svg class="w-4 h-4 mr-1" :class="refreshing && 'animate-spin'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Actualiser
            </button>
        </div>
    </div>

    <!-- Status Summary -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-6">
        @php
            $statuses = [
                'paid' => ['label' => 'Payées', 'color' => 'blue'],
                'confirmed' => ['label' => 'Confirmées', 'color' => 'indigo'],
                'preparing' => ['label' => 'En préparation', 'color' => 'amber'],
                'ready' => ['label' => 'Prêtes', 'color' => 'emerald'],
                'delivering' => ['label' => 'En livraison', 'color' => 'cyan'],
            ];
        @endphp
        @foreach($statuses as $key => $info)
            <div class="bg-white rounded-xl border border-neutral-200 p-4 text-center">
                <p class="text-2xl font-bold text-{{ $info['color'] }}-600">{{ $statusCounts[$key] ?? 0 }}</p>
                <p class="text-xs text-neutral-500 mt-1">{{ $info['label'] }}</p>
            </div>
        @endforeach
    </div>

    <!-- Filters -->
    <div class="bg-white rounded-xl border border-neutral-200 p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-3 items-end">
            <div class="flex-1 min-w-[180px]">
                <label class="block text-xs font-medium text-neutral-500 mb-1">Restaurant</label>
                <select name="restaurant_id" class="w-full h-10 px-3 bg-neutral-50 border border-neutral-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500">
                    <option value="">Tous</option>
                    @foreach($restaurants as $r)
                        <option value="{{ $r->id }}" {{ request('restaurant_id') == $r->id ? 'selected' : '' }}>{{ $r->name }}</option>
                    @endforeach
                </select>
            </div>
            <div class="min-w-[150px]">
                <label class="block text-xs font-medium text-neutral-500 mb-1">Statut</label>
                <select name="status" class="w-full h-10 px-3 bg-neutral-50 border border-neutral-300 rounded-lg text-sm focus:ring-2 focus:ring-primary-500">
                    <option value="">Tous</option>
                    @foreach($statuses as $key => $info)
                        <option value="{{ $key }}" {{ request('status') == $key ? 'selected' : '' }}>{{ $info['label'] }}</option>
                    @endforeach
                </select>
            </div>
            <button type="submit" class="h-10 px-4 bg-primary-500 text-white rounded-lg font-medium hover:bg-primary-600 transition-colors">
                Filtrer
            </button>
            @if(request()->hasAny(['restaurant_id', 'status']))
                <a href="{{ route('super-admin.orders.live') }}" class="h-10 px-4 flex items-center bg-neutral-100 text-neutral-600 rounded-lg font-medium hover:bg-neutral-200 transition-colors">
                    Réinitialiser
                </a>
            @endif
        </form>
    </div>

    <!-- Orders Table -->
    <div class="bg-white rounded-xl border border-neutral-200 overflow-hidden">
        @if($orders->isEmpty())
            <div class="p-12 text-center">
                <svg class="w-16 h-16 mx-auto text-neutral-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                </svg>
                <p class="text-neutral-500 text-lg">Aucune commande active</p>
                <p class="text-neutral-400 text-sm mt-1">Les commandes en cours apparaîtront ici</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-neutral-50 border-b border-neutral-200">
                        <tr>
                            <th class="text-left px-4 py-3 font-medium text-neutral-500">Ref</th>
                            <th class="text-left px-4 py-3 font-medium text-neutral-500">Restaurant</th>
                            <th class="text-left px-4 py-3 font-medium text-neutral-500">Client</th>
                            <th class="text-left px-4 py-3 font-medium text-neutral-500">Montant</th>
                            <th class="text-left px-4 py-3 font-medium text-neutral-500">Statut</th>
                            <th class="text-left px-4 py-3 font-medium text-neutral-500">Heure</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100">
                        @foreach($orders as $order)
                            <tr class="hover:bg-neutral-50 transition-colors">
                                <td class="px-4 py-3">
                                    <span class="font-mono font-medium text-neutral-900">{{ $order->reference }}</span>
                                </td>
                                <td class="px-4 py-3">
                                    <span class="text-neutral-900">{{ $order->restaurant?->name ?? '-' }}</span>
                                    @if($order->restaurant?->is_demo)
                                        <span class="ml-1 text-xs bg-purple-100 text-purple-600 px-1.5 py-0.5 rounded">démo</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-neutral-600">{{ $order->customer_name ?? '-' }}</td>
                                <td class="px-4 py-3 font-semibold text-neutral-900">{{ number_format($order->total, 0, ',', ' ') }} F</td>
                                <td class="px-4 py-3">
                                    @php
                                        $colors = [
                                            'paid' => 'bg-blue-50 text-blue-700 border-blue-200',
                                            'confirmed' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                            'preparing' => 'bg-amber-50 text-amber-700 border-amber-200',
                                            'ready' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                            'delivering' => 'bg-cyan-50 text-cyan-700 border-cyan-200',
                                        ];
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $colors[$order->status->value] ?? 'bg-neutral-100 text-neutral-600 border-neutral-200' }}">
                                        {{ $order->status->label() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-neutral-500">
                                    {{ $order->created_at->format('H:i') }}
                                    <span class="text-xs text-neutral-400 ml-1">{{ $order->created_at->diffForHumans(short: true) }}</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-neutral-200">
                {{ $orders->links() }}
            </div>
        @endif
    </div>
</x-layouts.admin-super>
