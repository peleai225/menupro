<x-layouts.admin-super title="Détail Abonnement">
    <!-- Header -->
    <div class="flex items-center justify-between mb-8">
        <div>
            <a href="{{ route('super-admin.subscriptions.index') }}" class="text-primary-400 hover:text-primary-300 text-sm mb-2 inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Retour à la liste
            </a>
            <h1 class="text-2xl font-bold text-white">Détail de l'abonnement #{{ $subscription->id }}</h1>
            <p class="text-neutral-400 mt-1">Informations complètes sur cet abonnement.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Subscription Details -->
            <div class="bg-neutral-800/50 border border-neutral-700 rounded-xl p-6">
                <h2 class="text-lg font-semibold text-white mb-4">Informations de l'abonnement</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-neutral-400">Statut</p>
                        <p class="mt-1">
                            @if($subscription->status->value === 'active')
                                <span class="badge bg-secondary-500/20 text-secondary-400">Actif</span>
                            @elseif($subscription->status->value === 'expired')
                                <span class="badge bg-red-500/20 text-red-400">Expiré</span>
                            @else
                                <span class="badge bg-yellow-500/20 text-yellow-400">En attente</span>
                            @endif
                        </p>
                    </div>
                    <div>
                        <p class="text-sm text-neutral-400">Plan</p>
                        <p class="mt-1 text-white font-medium">{{ $subscription->plan->name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-neutral-400">Période de facturation</p>
                        <p class="mt-1 text-white capitalize">{{ $subscription->billing_period ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-neutral-400">Réduction</p>
                        <p class="mt-1 text-white">{{ $subscription->discount_percentage ?? 0 }}%</p>
                    </div>
                    <div>
                        <p class="text-sm text-neutral-400">Date de début</p>
                        <p class="mt-1 text-white">{{ $subscription->starts_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-neutral-400">Date de fin</p>
                        <p class="mt-1 text-white">{{ $subscription->ends_at->format('d/m/Y H:i') }}</p>
                    </div>
                    @if($subscription->status->value === 'active')
                        <div>
                            <p class="text-sm text-neutral-400">Jours restants</p>
                            <p class="mt-1 text-white">{{ $subscription->ends_at->diffInDays(now()) }} jour(s)</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Payment Info -->
            <div class="bg-neutral-800/50 border border-neutral-700 rounded-xl p-6">
                <h2 class="text-lg font-semibold text-white mb-4">Informations de paiement</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm text-neutral-400">Montant payé</p>
                        <p class="mt-1 text-xl font-bold text-green-400">{{ number_format($subscription->amount_paid, 0, ',', ' ') }} FCFA</p>
                    </div>
                    @if($subscription->addons->isNotEmpty())
                        <div>
                            <p class="text-sm text-neutral-400">Add-ons</p>
                            <p class="mt-1 text-white">{{ $subscription->addons->count() }} add-on(s)</p>
                        </div>
                    @endif
                    @if($subscription->payment_reference)
                        <div>
                            <p class="text-sm text-neutral-400">Référence</p>
                            <p class="mt-1 text-white font-mono text-sm">{{ $subscription->payment_reference }}</p>
                        </div>
                    @endif
                    @if($subscription->payment_method)
                        <div>
                            <p class="text-sm text-neutral-400">Méthode</p>
                            <p class="mt-1 text-white capitalize">{{ $subscription->payment_method }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Add-ons -->
            @if($subscription->addons->isNotEmpty())
            <div class="bg-neutral-800/50 border border-neutral-700 rounded-xl p-6">
                <h2 class="text-lg font-semibold text-white mb-4">Add-ons</h2>
                <div class="space-y-3">
                    @foreach($subscription->addons as $addon)
                        <div class="flex items-center justify-between p-3 bg-neutral-700/50 rounded-lg">
                            <div>
                                <p class="text-white font-medium">{{ $addon->name }}</p>
                                <p class="text-sm text-neutral-400">{{ $addon->description }}</p>
                            </div>
                            <p class="text-white font-medium">{{ number_format($addon->price, 0, ',', ' ') }} FCFA/mois</p>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Restaurant Info -->
            <div class="bg-neutral-800/50 border border-neutral-700 rounded-xl p-6">
                <h2 class="text-lg font-semibold text-white mb-4">Restaurant</h2>
                <div class="space-y-3">
                    <div>
                        <p class="text-sm text-neutral-400">Nom</p>
                        <p class="mt-1 text-white font-medium">{{ $subscription->restaurant->name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-neutral-400">Email</p>
                        <p class="mt-1 text-white">{{ $subscription->restaurant->email }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-neutral-400">Téléphone</p>
                        <p class="mt-1 text-white">{{ $subscription->restaurant->phone ?? 'N/A' }}</p>
                    </div>
                    <div class="pt-3 border-t border-neutral-700">
                        <a href="{{ route('super-admin.restaurants.show', $subscription->restaurant) }}" 
                           class="btn btn-primary btn-sm w-full">
                            Voir le restaurant
                        </a>
                    </div>
                </div>
            </div>

            <!-- Payment History -->
            <div class="bg-neutral-800/50 border border-neutral-700 rounded-xl p-6">
                <h2 class="text-lg font-semibold text-white mb-4">Historique des paiements</h2>
                <div class="space-y-3 max-h-96 overflow-y-auto">
                    @forelse($paymentHistory as $payment)
                        <div class="p-3 bg-neutral-700/50 rounded-lg">
                            <div class="flex items-center justify-between mb-2">
                                <p class="text-sm font-medium text-white">{{ $payment->plan->name }}</p>
                                <p class="text-sm text-green-400">{{ number_format($payment->amount_paid, 0, ',', ' ') }} F</p>
                            </div>
                            <div class="flex items-center justify-between">
                                <p class="text-xs text-neutral-400">{{ $payment->created_at->format('d/m/Y') }}</p>
                                @if($payment->status->value === 'active')
                                    <span class="badge bg-secondary-500/20 text-secondary-400 text-xs">Actif</span>
                                @elseif($payment->status->value === 'expired')
                                    <span class="badge bg-red-500/20 text-red-400 text-xs">Expiré</span>
                                @else
                                    <span class="badge bg-yellow-500/20 text-yellow-400 text-xs">En attente</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-sm text-neutral-400">Aucun historique disponible.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin-super>
