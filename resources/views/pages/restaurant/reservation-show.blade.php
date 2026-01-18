<x-layouts.admin-restaurant title="Détails de la réservation">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <a href="{{ route('restaurant.reservations.index') }}" class="text-primary-600 hover:text-primary-700 text-sm font-medium mb-2 inline-flex items-center gap-1">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Retour aux réservations
            </a>
            <h1 class="text-2xl font-bold text-neutral-900">Détails de la réservation</h1>
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Customer Info -->
            <div class="card p-6">
                <h2 class="text-lg font-semibold text-neutral-900 mb-4">Informations client</h2>
                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-neutral-500">Nom</p>
                        <p class="font-medium text-neutral-900">{{ $reservation->customer_name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-neutral-500">Email</p>
                        <p class="font-medium text-neutral-900">{{ $reservation->customer_email }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-neutral-500">Téléphone</p>
                        <p class="font-medium text-neutral-900">{{ $reservation->customer_phone }}</p>
                    </div>
                </div>
            </div>

            <!-- Reservation Details -->
            <div class="card p-6">
                <h2 class="text-lg font-semibold text-neutral-900 mb-4">Détails de la réservation</h2>
                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-neutral-500">Date et heure</p>
                        <p class="font-medium text-neutral-900">
                            {{ $reservation->reservation_date->format('d/m/Y à H:i') }}
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-neutral-500">Nombre de personnes</p>
                        <p class="font-medium text-neutral-900">{{ $reservation->number_of_guests }} {{ $reservation->number_of_guests > 1 ? 'personnes' : 'personne' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-neutral-500">Statut</p>
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
                        <span class="badge {{ $statusColors[$reservation->status] }}">{{ $statusLabels[$reservation->status] }}</span>
                    </div>
                    @if($reservation->special_requests)
                        <div>
                            <p class="text-sm text-neutral-500">Demandes spéciales</p>
                            <p class="text-neutral-900 mt-1">{{ $reservation->special_requests }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Notes -->
            <div class="card p-6">
                <h2 class="text-lg font-semibold text-neutral-900 mb-4">Notes internes</h2>
                <form method="POST" action="{{ route('restaurant.reservations.status', $reservation) }}">
                    @csrf
                    @method('PATCH')
                    <textarea 
                        name="notes" 
                        rows="4" 
                        class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                        placeholder="Ajoutez des notes internes sur cette réservation..."
                    >{{ old('notes', $reservation->notes) }}</textarea>
                    <div class="mt-4 flex gap-3">
                        <button type="submit" name="status" value="{{ $reservation->status }}" class="btn btn-primary btn-sm">Enregistrer les notes</button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Actions Sidebar -->
        <div class="space-y-6">
            <!-- Status Actions -->
            <div class="card p-6">
                <h2 class="text-lg font-semibold text-neutral-900 mb-4">Actions</h2>
                <div class="space-y-3">
                    @if($reservation->status === 'pending')
                        <form method="POST" action="{{ route('restaurant.reservations.status', $reservation) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="confirmed">
                            <button type="submit" class="btn btn-primary w-full">Confirmer la réservation</button>
                        </form>
                        <form method="POST" action="{{ route('restaurant.reservations.status', $reservation) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="cancelled">
                            <button type="submit" class="btn btn-outline btn-danger w-full" onclick="return confirm('Êtes-vous sûr de vouloir annuler cette réservation ?')">Annuler</button>
                        </form>
                    @elseif($reservation->status === 'confirmed')
                        @if($reservation->reservation_date->isFuture())
                            <form method="POST" action="{{ route('restaurant.reservations.status', $reservation) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="completed">
                                <button type="submit" class="btn btn-primary w-full">Marquer comme complétée</button>
                            </form>
                        @endif
                        <form method="POST" action="{{ route('restaurant.reservations.status', $reservation) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="cancelled">
                            <button type="submit" class="btn btn-outline btn-danger w-full" onclick="return confirm('Êtes-vous sûr de vouloir annuler cette réservation ?')">Annuler</button>
                        </form>
                    @endif
                </div>
            </div>

            <!-- Reservation Info -->
            <div class="card p-6">
                <h2 class="text-lg font-semibold text-neutral-900 mb-4">Informations</h2>
                <div class="space-y-3 text-sm">
                    <div>
                        <p class="text-neutral-500">Créée le</p>
                        <p class="text-neutral-900">{{ $reservation->created_at->format('d/m/Y à H:i') }}</p>
                    </div>
                    @if($reservation->updated_at !== $reservation->created_at)
                        <div>
                            <p class="text-neutral-500">Modifiée le</p>
                            <p class="text-neutral-900">{{ $reservation->updated_at->format('d/m/Y à H:i') }}</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin-restaurant>

