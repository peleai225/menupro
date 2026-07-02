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
            <h1 class="text-2xl font-bold" style="color:var(--sa-fg);">Détail de l'abonnement #{{ $subscription->id }}</h1>
            <p class="mt-1" style="color:var(--sa-muted-fg);">Informations complètes sur cet abonnement.</p>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Main Info -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Subscription Details -->
            <div class="border shadow-sm rounded-xl p-6" style="background:var(--sa-card);border-color:var(--sa-border);">
                <h2 class="text-lg font-semibold mb-4" style="color:var(--sa-fg);">Informations de l'abonnement</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm" style="color:var(--sa-muted-fg);">Statut</p>
                        <p class="mt-1">
                            @if($subscription->status->value === 'active')
                                <span class="badge bg-emerald-50 text-emerald-700">Actif</span>
                            @elseif($subscription->status->value === 'expired')
                                <span class="badge bg-red-50 text-red-700">Expiré</span>
                            @else
                                <span class="badge bg-amber-50 text-amber-700">En attente</span>
                            @endif
                        </p>
                    </div>
                    <div>
                        <p class="text-sm" style="color:var(--sa-muted-fg);">Plan</p>
                        <p class="mt-1 font-medium" style="color:var(--sa-fg);">{{ $subscription->plan->name }}</p>
                    </div>
                    <div>
                        <p class="text-sm" style="color:var(--sa-muted-fg);">Période de facturation</p>
                        <p class="mt-1 capitalize" style="color:var(--sa-fg);">{{ $subscription->billing_period ?? 'N/A' }}</p>
                    </div>
                    <div>
                        <p class="text-sm" style="color:var(--sa-muted-fg);">Réduction</p>
                        <p class="mt-1" style="color:var(--sa-fg);">{{ $subscription->discount_percentage ?? 0 }}%</p>
                    </div>
                    <div>
                        <p class="text-sm" style="color:var(--sa-muted-fg);">Date de début</p>
                        <p class="mt-1" style="color:var(--sa-fg);">{{ $subscription->starts_at->format('d/m/Y H:i') }}</p>
                    </div>
                    <div>
                        <p class="text-sm" style="color:var(--sa-muted-fg);">Date de fin</p>
                        <p class="mt-1" style="color:var(--sa-fg);">{{ $subscription->ends_at->format('d/m/Y H:i') }}</p>
                    </div>
                    @if($subscription->status->value === 'active')
                        <div>
                            <p class="text-sm" style="color:var(--sa-muted-fg);">Jours restants</p>
                            <p class="mt-1" style="color:var(--sa-fg);">{{ $subscription->ends_at->diffInDays(now()) }} jour(s)</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Payment Info -->
            <div class="border shadow-sm rounded-xl p-6" style="background:var(--sa-card);border-color:var(--sa-border);">
                <h2 class="text-lg font-semibold mb-4" style="color:var(--sa-fg);">Informations de paiement</h2>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <p class="text-sm" style="color:var(--sa-muted-fg);">Montant payé</p>
                        <p class="mt-1 text-xl font-bold text-emerald-600">{{ number_format($subscription->amount_paid, 0, ',', ' ') }} FCFA</p>
                    </div>
                    @if($subscription->addons->isNotEmpty())
                        <div>
                            <p class="text-sm" style="color:var(--sa-muted-fg);">Add-ons</p>
                            <p class="mt-1" style="color:var(--sa-fg);">{{ $subscription->addons->count() }} add-on(s)</p>
                        </div>
                    @endif
                    @if($subscription->payment_reference)
                        <div>
                            <p class="text-sm" style="color:var(--sa-muted-fg);">Référence</p>
                            <p class="mt-1 font-mono text-sm" style="color:var(--sa-fg);">{{ $subscription->payment_reference }}</p>
                        </div>
                    @endif
                    @if($subscription->payment_method)
                        <div>
                            <p class="text-sm" style="color:var(--sa-muted-fg);">Méthode</p>
                            <p class="mt-1 capitalize" style="color:var(--sa-fg);">{{ $subscription->payment_method }}</p>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Add-ons -->
            @if($subscription->addons->isNotEmpty())
            <div class="border shadow-sm rounded-xl p-6" style="background:var(--sa-card);border-color:var(--sa-border);">
                <h2 class="text-lg font-semibold mb-4" style="color:var(--sa-fg);">Add-ons</h2>
                <div class="space-y-3">
                    @foreach($subscription->addons as $addon)
                        <div class="flex items-center justify-between p-3 rounded-lg" style="background:var(--sa-muted);">
                            <div>
                                <p class="font-medium" style="color:var(--sa-fg);">{{ $addon->name }}</p>
                                <p class="text-sm" style="color:var(--sa-muted-fg);">{{ $addon->description }}</p>
                            </div>
                            <p class="font-medium" style="color:var(--sa-fg);">{{ number_format($addon->price, 0, ',', ' ') }} FCFA/mois</p>
                        </div>
                    @endforeach
                </div>
            </div>
            @endif
        </div>

        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Restaurant Info -->
            <div class="border shadow-sm rounded-xl p-6" style="background:var(--sa-card);border-color:var(--sa-border);">
                <h2 class="text-lg font-semibold mb-4" style="color:var(--sa-fg);">Restaurant</h2>
                <div class="space-y-3">
                    <div>
                        <p class="text-sm" style="color:var(--sa-muted-fg);">Nom</p>
                        <p class="mt-1 font-medium" style="color:var(--sa-fg);">{{ $subscription->restaurant->name }}</p>
                    </div>
                    <div>
                        <p class="text-sm" style="color:var(--sa-muted-fg);">Email</p>
                        <p class="mt-1" style="color:var(--sa-fg);">{{ $subscription->restaurant->email }}</p>
                    </div>
                    <div>
                        <p class="text-sm" style="color:var(--sa-muted-fg);">Téléphone</p>
                        <p class="mt-1" style="color:var(--sa-fg);">{{ $subscription->restaurant->phone ?? 'N/A' }}</p>
                    </div>
                    <div class="pt-3 border-t" style="border-color:var(--sa-border);">
                        <a href="{{ route('super-admin.restaurants.show', $subscription->restaurant) }}"
                           class="btn btn-primary btn-sm w-full">
                            Voir le restaurant
                        </a>
                    </div>
                </div>
            </div>

            <!-- Payment History -->
            <div class="border shadow-sm rounded-xl p-6" style="background:var(--sa-card);border-color:var(--sa-border);">
                <h2 class="text-lg font-semibold mb-4" style="color:var(--sa-fg);">Historique des paiements</h2>
                <div class="space-y-3 max-h-96 overflow-y-auto">
                    @forelse($paymentHistory as $payment)
                        <div class="p-3 rounded-lg" style="background:var(--sa-muted);">
                            <div class="flex items-center justify-between mb-2">
                                <p class="text-sm font-medium" style="color:var(--sa-fg);">{{ $payment->plan->name }}</p>
                                <p class="text-sm text-emerald-600">{{ number_format($payment->amount_paid, 0, ',', ' ') }} F</p>
                            </div>
                            <div class="flex items-center justify-between">
                                <p class="text-xs" style="color:var(--sa-muted-fg);">{{ $payment->created_at->format('d/m/Y') }}</p>
                                @if($payment->status->value === 'active')
                                    <span class="badge bg-emerald-50 text-emerald-700 text-xs">Actif</span>
                                @elseif($payment->status->value === 'expired')
                                    <span class="badge bg-red-50 text-red-700 text-xs">Expiré</span>
                                @else
                                    <span class="badge bg-amber-50 text-amber-700 text-xs">En attente</span>
                                @endif
                            </div>
                        </div>
                    @empty
                        <p class="text-sm" style="color:var(--sa-muted-fg);">Aucun historique disponible.</p>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin-super>
