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
            <div class="card p-6" x-data="{ showCancelModal: false }">
                <h2 class="text-lg font-semibold text-neutral-900 mb-4">Actions</h2>
                <div class="space-y-3">
                    @if($reservation->status === 'pending')
                        <form method="POST" action="{{ route('restaurant.reservations.status', $reservation) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="confirmed">
                            <button type="submit" class="btn btn-primary w-full flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                Confirmer la réservation
                            </button>
                        </form>
                        <button @click="showCancelModal = true" class="btn btn-outline w-full flex items-center justify-center gap-2" style="border-color: #ef4444; color: #ef4444;">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Annuler la réservation
                        </button>
                    @elseif($reservation->status === 'confirmed')
                        @if($reservation->reservation_date->isPast() || $reservation->reservation_date->isToday())
                            <form method="POST" action="{{ route('restaurant.reservations.status', $reservation) }}">
                                @csrf
                                @method('PATCH')
                                <input type="hidden" name="status" value="completed">
                                <button type="submit" class="btn btn-primary w-full flex items-center justify-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                    Marquer comme complétée
                                </button>
                            </form>
                        @endif
                        <button @click="showCancelModal = true" class="btn btn-outline w-full flex items-center justify-center gap-2" style="border-color: #ef4444; color: #ef4444;">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                            Annuler la réservation
                        </button>
                    @elseif($reservation->status === 'cancelled')
                        <div class="p-4 bg-red-50 rounded-lg border border-red-200">
                            <p class="text-sm text-red-700 font-medium">Cette réservation a été annulée.</p>
                        </div>
                    @elseif($reservation->status === 'completed')
                        <div class="p-4 bg-green-50 rounded-lg border border-green-200">
                            <p class="text-sm text-green-700 font-medium">Cette réservation est terminée.</p>
                        </div>
                    @endif
                </div>

                <!-- Cancel Modal -->
                <div x-show="showCancelModal" 
                     x-transition:enter="transition ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="transition ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 z-50 flex items-center justify-center p-4" 
                     style="background: rgba(0,0,0,0.5);"
                     x-cloak>
                    <div class="bg-white rounded-2xl shadow-xl max-w-md w-full p-6"
                         @click.away="showCancelModal = false">
                        <h3 class="text-lg font-bold text-neutral-900 mb-4">Annuler la réservation</h3>
                        <p class="text-neutral-600 mb-4">
                            Êtes-vous sûr de vouloir annuler la réservation de <strong>{{ $reservation->customer_name }}</strong> 
                            pour le <strong>{{ $reservation->reservation_date->format('d/m/Y à H:i') }}</strong> ?
                        </p>
                        <p class="text-sm text-neutral-500 mb-4">Le client sera notifié par email de cette annulation.</p>
                        
                        <form method="POST" action="{{ route('restaurant.reservations.status', $reservation) }}">
                            @csrf
                            @method('PATCH')
                            <input type="hidden" name="status" value="cancelled">
                            
                            <div class="mb-4">
                                <label class="block text-sm font-medium text-neutral-700 mb-2">
                                    Raison de l'annulation (optionnel)
                                </label>
                                <textarea name="cancellation_reason" 
                                          rows="3" 
                                          class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"
                                          placeholder="Ex: Restaurant complet, fermeture exceptionnelle..."></textarea>
                                <p class="text-xs text-neutral-500 mt-1">Cette raison sera incluse dans l'email envoyé au client.</p>
                            </div>
                            
                            <div class="flex gap-3">
                                <button type="button" @click="showCancelModal = false" class="btn btn-ghost flex-1">
                                    Annuler
                                </button>
                                <button type="submit" class="btn flex-1" style="background: #ef4444; color: white;">
                                    Confirmer l'annulation
                                </button>
                            </div>
                        </form>
                    </div>
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

