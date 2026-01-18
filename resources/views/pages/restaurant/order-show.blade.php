<x-layouts.admin-restaurant title="Commande #{{ $order }}">
    <!-- Header -->
    <div class="flex items-center gap-4 mb-8">
        <a href="{{ route('restaurant.orders') }}" class="p-2 hover:bg-neutral-100 rounded-lg">
            <svg class="w-5 h-5 text-neutral-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16l-4-4m0 0l4-4m-4 4h18"/>
            </svg>
        </a>
        <div class="flex-1">
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-neutral-900">Commande #{{ $order }}</h1>
                <span class="badge bg-yellow-100 text-yellow-700">En attente</span>
            </div>
            <p class="text-neutral-500 mt-1">Reçue il y a 5 minutes</p>
        </div>
        <button class="btn btn-primary">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
            </svg>
            Marquer comme prêt
        </button>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Order Details -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Items -->
            <div class="card">
                <div class="p-6 border-b border-neutral-100">
                    <h2 class="text-lg font-semibold text-neutral-900">Articles commandés</h2>
                </div>
                <div class="divide-y divide-neutral-100">
                    @foreach([
                        ['name' => 'Poulet braisé', 'quantity' => 2, 'price' => '4 500', 'total' => '9 000'],
                        ['name' => 'Attiéké', 'quantity' => 2, 'price' => '500', 'total' => '1 000'],
                        ['name' => 'Jus de bissap', 'quantity' => 2, 'price' => '500', 'total' => '1 000'],
                    ] as $item)
                    <div class="p-4 flex items-center justify-between">
                        <div class="flex items-center gap-4">
                            <div class="w-16 h-16 bg-neutral-200 rounded-xl"></div>
                            <div>
                                <p class="font-medium text-neutral-900">{{ $item['name'] }}</p>
                                <p class="text-sm text-neutral-500">{{ $item['price'] }} F × {{ $item['quantity'] }}</p>
                            </div>
                        </div>
                        <span class="font-bold text-neutral-900">{{ $item['total'] }} F</span>
                    </div>
                    @endforeach
                </div>
                <div class="p-6 bg-neutral-50 border-t border-neutral-100">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-neutral-600">Sous-total</span>
                        <span class="font-medium text-neutral-900">11 000 F</span>
                    </div>
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-neutral-600">Frais de livraison</span>
                        <span class="font-medium text-neutral-900">1 500 F</span>
                    </div>
                    <div class="flex items-center justify-between pt-2 border-t border-neutral-200">
                        <span class="font-bold text-neutral-900">Total</span>
                        <span class="text-xl font-bold text-primary-600">12 500 F</span>
                    </div>
                </div>
            </div>

            <!-- Timeline -->
            <div class="card p-6">
                <h2 class="text-lg font-semibold text-neutral-900 mb-4">Historique</h2>
                <div class="space-y-4">
                    @foreach([
                        ['status' => 'Commande reçue', 'time' => '14:32', 'active' => true],
                        ['status' => 'En préparation', 'time' => '', 'active' => false],
                        ['status' => 'Prête', 'time' => '', 'active' => false],
                        ['status' => 'Livrée', 'time' => '', 'active' => false],
                    ] as $step)
                    <div class="flex items-center gap-4">
                        <div class="w-4 h-4 rounded-full {{ $step['active'] ? 'bg-primary-500' : 'bg-neutral-200' }}"></div>
                        <div class="flex-1">
                            <p class="{{ $step['active'] ? 'font-medium text-neutral-900' : 'text-neutral-400' }}">{{ $step['status'] }}</p>
                        </div>
                        @if($step['time'])
                            <span class="text-sm text-neutral-500">{{ $step['time'] }}</span>
                        @endif
                    </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Customer -->
            <div class="card p-6">
                <h2 class="text-lg font-semibold text-neutral-900 mb-4">Client</h2>
                <div class="flex items-center gap-4 mb-4">
                    <div class="w-12 h-12 bg-primary-100 rounded-full flex items-center justify-center">
                        <span class="text-lg font-bold text-primary-600">J</span>
                    </div>
                    <div>
                        <p class="font-medium text-neutral-900">Jean Kouassi</p>
                        <p class="text-sm text-neutral-500">Client régulier</p>
                    </div>
                </div>
                <div class="space-y-3">
                    <div class="flex items-center gap-3 text-neutral-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                        </svg>
                        <span>+225 07 00 00 01</span>
                    </div>
                    <div class="flex items-center gap-3 text-neutral-600">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <span>jean@email.com</span>
                    </div>
                </div>
            </div>

            <!-- Delivery -->
            <div class="card p-6">
                <h2 class="text-lg font-semibold text-neutral-900 mb-4">Livraison</h2>
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-neutral-400 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <div>
                        <p class="text-neutral-900">Cocody Angré</p>
                        <p class="text-sm text-neutral-500">8ème tranche, Star 12</p>
                    </div>
                </div>
            </div>

            <!-- Notes -->
            <div class="card p-6">
                <h2 class="text-lg font-semibold text-neutral-900 mb-4">Note du client</h2>
                <p class="text-neutral-600 italic">"Pas trop pimenté s'il vous plaît."</p>
            </div>

            <!-- Actions -->
            <div class="space-y-3">
                <button class="btn btn-success w-full">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Marquer comme prêt
                </button>
                <button class="btn btn-outline w-full">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"/>
                    </svg>
                    Imprimer le ticket
                </button>
                <button class="btn btn-ghost w-full text-red-600 hover:bg-red-50">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                    Annuler la commande
                </button>
            </div>
        </div>
    </div>
</x-layouts.admin-restaurant>

