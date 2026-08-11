<div class="space-y-6">
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
        <div>
            <h1 class="text-xl sm:text-2xl font-bold text-neutral-900">Livraisons</h1>
            <p class="text-neutral-600 hidden sm:block">Suivez et assignez les livraisons en cours</p>
        </div>
        <a href="{{ route('restaurant.delivery-drivers') }}" class="btn bg-neutral-100 hover:bg-neutral-200 text-neutral-700 px-4 py-2.5 flex items-center gap-2 min-h-[52px] touch-manipulation">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Gerer les livreurs
        </a>
    </div>

    {{-- Pending Orders (Ready for delivery) --}}
    @if($this->pendingOrders->count() > 0)
        <div class="card p-5 border-2 border-orange-200 bg-orange-50/50">
            <h2 class="font-bold text-neutral-900 mb-3 flex items-center gap-2">
                <svg class="w-5 h-5 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                Commandes pretes a livrer ({{ $this->pendingOrders->count() }})
            </h2>
            <div class="space-y-2">
                @foreach($this->pendingOrders as $order)
                    <div class="flex flex-col sm:flex-row sm:items-center justify-between bg-white rounded-xl p-3 border border-orange-100 gap-2">
                        <div>
                            <span class="font-semibold text-neutral-900">#{{ $order->reference }}</span>
                            <span class="text-neutral-500 mx-2">•</span>
                            <span class="text-neutral-600">{{ $order->customer_name }}</span>
                            <p class="text-sm text-neutral-500 mt-0.5">{{ $order->delivery_address }}</p>
                        </div>
                        <button wire:click="openAssign({{ $order->id }})" class="btn btn-primary px-4 py-2 text-sm flex items-center gap-2 min-h-[52px] touch-manipulation self-start sm:self-center flex-shrink-0">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            Assigner
                        </button>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    {{-- Filter Tabs --}}
    <div class="flex flex-wrap gap-2">
        <button wire:click="$set('filter', 'active')" class="px-4 py-2.5 rounded-lg text-sm font-medium transition min-h-[44px] touch-manipulation {{ $filter === 'active' ? 'bg-primary-500 text-white' : 'bg-neutral-100 text-neutral-600 hover:bg-neutral-200' }}">
            En cours
        </button>
        <button wire:click="$set('filter', 'completed')" class="px-4 py-2.5 rounded-lg text-sm font-medium transition min-h-[44px] touch-manipulation {{ $filter === 'completed' ? 'bg-primary-500 text-white' : 'bg-neutral-100 text-neutral-600 hover:bg-neutral-200' }}">
            Terminees
        </button>
        <button wire:click="$set('filter', 'all')" class="px-4 py-2.5 rounded-lg text-sm font-medium transition min-h-[44px] touch-manipulation {{ $filter === 'all' ? 'bg-primary-500 text-white' : 'bg-neutral-100 text-neutral-600 hover:bg-neutral-200' }}">
            Toutes
        </button>
    </div>

    {{-- Deliveries List --}}
    <div class="space-y-3">
        @forelse($this->deliveries as $delivery)
            <div class="card p-3 sm:p-4">
                <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-2">
                    <div class="flex items-center gap-3 sm:gap-4">
                        {{-- Status Badge --}}
                        <span class="inline-flex items-center px-3 py-1.5 rounded-full text-xs font-medium
                            {{ match($delivery->status->color()) {
                                'green' => 'bg-green-100 text-green-700',
                                'blue' => 'bg-blue-100 text-blue-700',
                                'indigo' => 'bg-indigo-100 text-indigo-700',
                                'orange' => 'bg-orange-100 text-orange-700',
                                'purple' => 'bg-purple-100 text-purple-700',
                                'red' => 'bg-red-100 text-red-700',
                                default => 'bg-gray-100 text-gray-700',
                            } }}">
                            {{ $delivery->status->label() }}
                        </span>

                        <div>
                            <div class="flex items-center gap-2">
                                <span class="font-semibold text-neutral-900">#{{ $delivery->order->reference }}</span>
                                <span class="text-neutral-400">→</span>
                                <span class="text-neutral-600">{{ $delivery->order->customer_name }}</span>
                            </div>
                            <p class="text-sm text-neutral-500">{{ $delivery->delivery_address }}</p>
                        </div>
                    </div>

                    <div class="text-right flex-shrink-0">
                        @if($delivery->driver)
                            <p class="font-medium text-neutral-900">{{ $delivery->driver->name }}</p>
                            <p class="text-xs text-neutral-500">{{ $delivery->driver->phone }}</p>
                        @else
                            <span class="text-sm text-neutral-400">Pas de livreur</span>
                        @endif
                    </div>
                </div>

                @if($delivery->assigned_at)
                    <div class="flex items-center gap-4 mt-3 pt-3 border-t border-neutral-100 text-xs text-neutral-500">
                        <span>Assigne: {{ $delivery->assigned_at->format('H:i') }}</span>
                        @if($delivery->picked_up_at)
                            <span>Recupere: {{ $delivery->picked_up_at->format('H:i') }}</span>
                        @endif
                        @if($delivery->delivered_at)
                            <span class="text-green-600 font-medium">Livre: {{ $delivery->delivered_at->format('H:i') }}</span>
                        @endif
                    </div>
                @endif
            </div>
        @empty
            <div class="text-center py-12">
                <svg class="w-16 h-16 mx-auto text-neutral-300 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 16V6a1 1 0 00-1-1H4a1 1 0 00-1 1v10a1 1 0 001 1h1m8-1a1 1 0 01-1 1H9m4-1V8a1 1 0 011-1h2.586a1 1 0 01.707.293l3.414 3.414a1 1 0 01.293.707V16a1 1 0 01-1 1h-1m-6-1a1 1 0 001 1h1M5 17a2 2 0 104 0m-4 0a2 2 0 114 0m6 0a2 2 0 104 0m-4 0a2 2 0 114 0"/>
                </svg>
                <p class="text-neutral-500 text-lg">Aucune livraison {{ $filter === 'active' ? 'en cours' : '' }}</p>
            </div>
        @endforelse
    </div>

    {{-- Assign Modal --}}
    @if($showAssignModal)
        <div class="fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4" wire:click.self="$set('showAssignModal', false)">
            <div class="bg-white rounded-2xl w-full max-w-md flex flex-col" style="max-height: min(90vh, calc(100vh - 80px))">
                <div class="flex-shrink-0 px-6 pt-6 pb-4 border-b border-neutral-100">
                <h2 class="text-lg font-bold text-neutral-900">Assigner un livreur</h2>
                </div>
                <div class="flex-1 overflow-y-auto p-6">
                @if($this->availableDrivers->count() > 0)
                    <div class="space-y-2">
                        @foreach($this->availableDrivers as $driver)
                            <label class="flex items-center gap-3 p-3 rounded-xl border-2 cursor-pointer transition
                                {{ $assignDriverId == $driver->id ? 'border-primary-500 bg-primary-50' : 'border-neutral-200 hover:border-neutral-300' }}">
                                <input type="radio" wire:model="assignDriverId" value="{{ $driver->id }}" class="text-primary-500">
                                <div class="flex-1">
                                    <p class="font-medium text-neutral-900">{{ $driver->name }}</p>
                                    <p class="text-xs text-neutral-500">{{ ucfirst($driver->vehicle_type) }} • {{ $driver->phone }}</p>
                                </div>
                                <span class="text-xs text-neutral-400">{{ $driver->total_deliveries }} courses</span>
                            </label>
                        @endforeach
                    </div>
                @else
                    <div class="text-center py-6">
                        <p class="text-neutral-500">Aucun livreur disponible</p>
                        <a href="{{ route('restaurant.delivery-drivers') }}" class="text-primary-500 text-sm mt-2 inline-block hover:underline">Ajouter un livreur</a>
                    </div>
                @endif
                </div>
                @if($this->availableDrivers->count() > 0)
                <div class="flex-shrink-0 p-6 border-t border-neutral-100">
                    <div class="flex items-center gap-3">
                        <button type="button" wire:click="$set('showAssignModal', false)" class="flex-1 btn bg-neutral-100 hover:bg-neutral-200 text-neutral-700 py-2.5 min-h-[52px] touch-manipulation">
                            Annuler
                        </button>
                        <button type="button" wire:click="assignDriver" class="flex-1 btn btn-primary py-2.5 min-h-[52px] touch-manipulation" {{ !$assignDriverId ? 'disabled' : '' }}>
                            Assigner
                        </button>
                    </div>
                </div>
                @endif
            </div>
        </div>
    @endif
</div>
