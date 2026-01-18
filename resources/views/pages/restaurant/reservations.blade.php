<x-layouts.admin-restaurant title="Réservations">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-neutral-900">Réservations</h1>
            <p class="text-neutral-500 mt-1">Gérez toutes vos réservations de table.</p>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <div class="card p-4">
            <p class="text-sm text-neutral-500">En attente</p>
            <p class="text-2xl font-bold text-neutral-900">{{ $stats['pending'] }}</p>
        </div>
        <div class="card p-4">
            <p class="text-sm text-neutral-500">Confirmées</p>
            <p class="text-2xl font-bold text-neutral-900">{{ $stats['confirmed'] }}</p>
        </div>
        <div class="card p-4">
            <p class="text-sm text-neutral-500">Aujourd'hui</p>
            <p class="text-2xl font-bold text-neutral-900">{{ $stats['today'] }}</p>
        </div>
        <div class="card p-4">
            <p class="text-sm text-neutral-500">À venir</p>
            <p class="text-2xl font-bold text-neutral-900">{{ $stats['upcoming'] }}</p>
        </div>
    </div>

    <!-- Filters -->
    <div class="card p-4 mb-6">
        <form method="GET" action="{{ route('restaurant.reservations.index') }}" class="flex flex-wrap gap-3">
            <select name="status" class="px-4 py-2 bg-white border border-neutral-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
                <option value="">Tous les statuts</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En attente</option>
                <option value="confirmed" {{ request('status') === 'confirmed' ? 'selected' : '' }}>Confirmées</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Annulées</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Complétées</option>
            </select>
            <input type="date" name="date" value="{{ request('date') }}" class="px-4 py-2 bg-white border border-neutral-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-primary-500">
            <button type="submit" class="btn btn-primary btn-sm">Filtrer</button>
            @if(request('status') || request('date'))
                <a href="{{ route('restaurant.reservations.index') }}" class="btn btn-ghost btn-sm">Réinitialiser</a>
            @endif
        </form>
    </div>

    <!-- Reservations List -->
    @if($reservations->count() > 0)
        <div class="space-y-4">
            @foreach($reservations as $reservation)
                <div class="card p-4 hover:shadow-md transition-shadow">
                    <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                        <div class="flex items-start gap-4">
                            <div class="w-12 h-12 bg-primary-100 rounded-full flex items-center justify-center flex-shrink-0">
                                <svg class="w-6 h-6 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            <div class="flex-1">
                                <div class="flex items-center gap-2 mb-1">
                                    <span class="font-bold text-neutral-900">{{ $reservation->customer_name }}</span>
                                    @php
                                        $statusColors = [
                                            'pending' => 'bg-amber-100 text-amber-700',
                                            'confirmed' => 'bg-emerald-100 text-emerald-700',
                                            'cancelled' => 'bg-red-100 text-red-700',
                                            'completed' => 'bg-neutral-100 text-neutral-700'
                                        ];
                                        $statusLabels = [
                                            'pending' => 'En attente',
                                            'confirmed' => 'Confirmée',
                                            'cancelled' => 'Annulée',
                                            'completed' => 'Complétée'
                                        ];
                                    @endphp
                                    <span class="badge {{ $statusColors[$reservation->status] }} text-xs">{{ $statusLabels[$reservation->status] }}</span>
                                </div>
                                <p class="text-neutral-700">{{ $reservation->customer_email }}</p>
                                <p class="text-sm text-neutral-500">
                                    {{ $reservation->customer_phone }} · 
                                    {{ $reservation->number_of_guests }} {{ $reservation->number_of_guests > 1 ? 'personnes' : 'personne' }} · 
                                    {{ $reservation->reservation_date->format('d/m/Y à H:i') }}
                                </p>
                                @if($reservation->special_requests)
                                    <p class="text-sm text-neutral-600 mt-2 italic">"{{ Str::limit($reservation->special_requests, 100) }}"</p>
                                @endif
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <a href="{{ route('restaurant.reservations.show', $reservation) }}" class="btn btn-ghost btn-sm">
                                Voir détails
                            </a>
                            @if($reservation->status === 'pending')
                                <form method="POST" action="{{ route('restaurant.reservations.status', $reservation) }}" class="inline">
                                    @csrf
                                    @method('PATCH')
                                    <input type="hidden" name="status" value="confirmed">
                                    <button type="submit" class="btn btn-primary btn-sm">Confirmer</button>
                                </form>
                            @endif
                        </div>
                    </div>
                </div>
            @endforeach
        </div>

        <!-- Pagination -->
        <div class="mt-6">
            {{ $reservations->links() }}
        </div>
    @else
        <div class="card p-12 text-center">
            <div class="w-24 h-24 bg-neutral-100 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-12 h-12 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-neutral-900 mb-2">Aucune réservation</h3>
            <p class="text-neutral-500">Aucune réservation ne correspond à vos critères de recherche.</p>
        </div>
    @endif
</x-layouts.admin-restaurant>

