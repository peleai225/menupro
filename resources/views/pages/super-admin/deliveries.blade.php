<x-layouts.admin-super title="Livraisons">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-neutral-900">Livraisons en cours</h1>
            <p class="text-neutral-500 text-sm mt-1">Suivi global des livraisons tous restaurants confondus</p>
        </div>
        <div class="flex items-center gap-3">
            <div class="px-3 py-1.5 bg-emerald-50 border border-emerald-200 rounded-lg">
                <span class="text-sm font-medium text-emerald-700">{{ $todayDelivered }} livrées aujourd'hui</span>
            </div>
        </div>
    </div>

    <!-- Status Summary -->
    <div class="grid grid-cols-2 sm:grid-cols-5 gap-3 mb-6">
        @php
            $statuses = [
                'pending' => ['label' => 'En attente', 'color' => 'neutral'],
                'assigned' => ['label' => 'Assignées', 'color' => 'blue'],
                'heading_to_restaurant' => ['label' => 'Vers resto', 'color' => 'indigo'],
                'picked_up' => ['label' => 'Récupérées', 'color' => 'amber'],
                'delivering' => ['label' => 'En livraison', 'color' => 'purple'],
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
                <a href="{{ route('super-admin.deliveries.index') }}" class="h-10 px-4 flex items-center bg-neutral-100 text-neutral-600 rounded-lg font-medium hover:bg-neutral-200 transition-colors">
                    Réinitialiser
                </a>
            @endif
        </form>
    </div>

    <!-- Deliveries Table -->
    <div class="bg-white rounded-xl border border-neutral-200 overflow-hidden">
        @if($deliveries->isEmpty())
            <div class="p-12 text-center">
                <svg class="w-16 h-16 mx-auto text-neutral-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
                </svg>
                <p class="text-neutral-500 text-lg">Aucune livraison active</p>
                <p class="text-neutral-400 text-sm mt-1">Les livraisons en cours apparaîtront ici</p>
            </div>
        @else
            <div class="overflow-x-auto">
                <table class="w-full text-sm">
                    <thead class="bg-neutral-50 border-b border-neutral-200">
                        <tr>
                            <th class="text-left px-4 py-3 font-medium text-neutral-500">Commande</th>
                            <th class="text-left px-4 py-3 font-medium text-neutral-500">Restaurant</th>
                            <th class="text-left px-4 py-3 font-medium text-neutral-500">Livreur</th>
                            <th class="text-left px-4 py-3 font-medium text-neutral-500">Client</th>
                            <th class="text-left px-4 py-3 font-medium text-neutral-500">Adresse</th>
                            <th class="text-left px-4 py-3 font-medium text-neutral-500">Statut</th>
                            <th class="text-left px-4 py-3 font-medium text-neutral-500">Depuis</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-neutral-100">
                        @foreach($deliveries as $delivery)
                            <tr class="hover:bg-neutral-50 transition-colors">
                                <td class="px-4 py-3">
                                    <span class="font-mono font-medium text-neutral-900">{{ $delivery->order?->reference ?? '-' }}</span>
                                </td>
                                <td class="px-4 py-3 text-neutral-900">{{ $delivery->restaurant?->name ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    @if($delivery->driver)
                                        <span class="text-neutral-900">{{ $delivery->driver->name }}</span>
                                    @else
                                        <span class="text-neutral-400 italic">Non assigné</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-neutral-600">{{ $delivery->order?->customer_name ?? '-' }}</td>
                                <td class="px-4 py-3 text-neutral-500 max-w-[200px] truncate">{{ $delivery->delivery_address ?? '-' }}</td>
                                <td class="px-4 py-3">
                                    @php
                                        $colors = [
                                            'pending' => 'bg-neutral-100 text-neutral-700 border-neutral-200',
                                            'assigned' => 'bg-blue-50 text-blue-700 border-blue-200',
                                            'heading_to_restaurant' => 'bg-indigo-50 text-indigo-700 border-indigo-200',
                                            'picked_up' => 'bg-amber-50 text-amber-700 border-amber-200',
                                            'delivering' => 'bg-purple-50 text-purple-700 border-purple-200',
                                        ];
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium border {{ $colors[$delivery->status->value] ?? 'bg-neutral-100 text-neutral-600 border-neutral-200' }}">
                                        {{ $delivery->status->label() }}
                                    </span>
                                </td>
                                <td class="px-4 py-3 text-neutral-500">
                                    {{ $delivery->created_at->diffForHumans(short: true) }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="px-4 py-3 border-t border-neutral-200">
                {{ $deliveries->links() }}
            </div>
        @endif
    </div>
</x-layouts.admin-super>
