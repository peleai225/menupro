<x-layouts.admin-super title="Abonnements">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-white">Gestion des Abonnements</h1>
            <p class="text-neutral-400 mt-1">Suivez tous les abonnements et paiements de la plateforme.</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('super-admin.subscriptions.export', request()->all()) }}" 
               class="btn btn-ghost btn-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Exporter CSV
            </a>
        </div>
    </div>

    <!-- Stats -->
    <div class="grid grid-cols-2 lg:grid-cols-7 gap-4 mb-6">
        <div class="bg-neutral-800/50 border border-neutral-700 rounded-xl p-4">
            <p class="text-sm text-neutral-400">Total</p>
            <p class="text-2xl font-bold text-white">{{ number_format($stats['total']) }}</p>
        </div>
        <div class="bg-neutral-800/50 border border-neutral-700 rounded-xl p-4">
            <p class="text-sm text-neutral-400">Actifs</p>
            <p class="text-2xl font-bold text-secondary-400">{{ number_format($stats['active']) }}</p>
        </div>
        <div class="bg-neutral-800/50 border border-neutral-700 rounded-xl p-4">
            <p class="text-sm text-neutral-400">Expirés</p>
            <p class="text-2xl font-bold text-red-400">{{ number_format($stats['expired']) }}</p>
        </div>
        <div class="bg-neutral-800/50 border border-neutral-700 rounded-xl p-4">
            <p class="text-sm text-neutral-400">En attente</p>
            <p class="text-2xl font-bold text-yellow-400">{{ number_format($stats['pending']) }}</p>
        </div>
        <div class="bg-neutral-800/50 border border-neutral-700 rounded-xl p-4">
            <p class="text-sm text-neutral-400">Expirent bientôt</p>
            <p class="text-2xl font-bold text-orange-400">{{ number_format($stats['expiring_soon']) }}</p>
        </div>
        <div class="bg-neutral-800/50 border border-neutral-700 rounded-xl p-4">
            <p class="text-sm text-neutral-400">Revenus totaux</p>
            <p class="text-lg font-bold text-green-400">{{ number_format($stats['total_revenue'], 0, ',', ' ') }} F</p>
        </div>
        <div class="bg-neutral-800/50 border border-neutral-700 rounded-xl p-4">
            <p class="text-sm text-neutral-400">Revenus ce mois</p>
            <p class="text-lg font-bold text-green-400">{{ number_format($stats['monthly_revenue'], 0, ',', ' ') }} F</p>
        </div>
    </div>

    <!-- Retention Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        <div class="bg-neutral-800/50 border border-neutral-700 rounded-xl p-4">
            <p class="text-sm text-neutral-400">Durée moyenne</p>
            <p class="text-xl font-bold text-white">
                {{ $retentionStats['average_duration'] ? round($retentionStats['average_duration']) . ' jours' : 'N/A' }}
            </p>
        </div>
        <div class="bg-neutral-800/50 border border-neutral-700 rounded-xl p-4">
            <p class="text-sm text-neutral-400">Taux de renouvellement</p>
            <p class="text-xl font-bold text-secondary-400">{{ $retentionStats['renewal_rate'] }}%</p>
        </div>
        <div class="bg-neutral-800/50 border border-neutral-700 rounded-xl p-4">
            <p class="text-sm text-neutral-400">Taux de désabonnement</p>
            <p class="text-xl font-bold text-red-400">{{ $retentionStats['churn_rate'] }}%</p>
        </div>
    </div>

    <!-- Filters -->
    <form method="GET" class="bg-neutral-800/50 border border-neutral-700 rounded-xl p-4 mb-6">
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
            <div>
                <label class="block text-sm font-medium text-neutral-300 mb-2">Recherche</label>
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" 
                           name="search" 
                           value="{{ request('search') }}"
                           placeholder="Restaurant, email, référence..."
                           class="w-full pl-10 pr-4 py-2 bg-neutral-700 border border-neutral-600 rounded-lg text-white placeholder-neutral-500 focus:outline-none focus:ring-2 focus:ring-primary-500">
                </div>
            </div>
            <div>
                <label class="block text-sm font-medium text-neutral-300 mb-2">Statut</label>
                <select name="status" class="w-full px-4 py-2 bg-neutral-700 border border-neutral-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="">Tous les statuts</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Actif</option>
                    <option value="expired" {{ request('status') === 'expired' ? 'selected' : '' }}>Expiré</option>
                    <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>En attente</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-neutral-300 mb-2">Plan</label>
                <select name="plan" class="w-full px-4 py-2 bg-neutral-700 border border-neutral-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="">Tous les plans</option>
                    @foreach($plans as $plan)
                        <option value="{{ $plan->id }}" {{ request('plan') == $plan->id ? 'selected' : '' }}>{{ $plan->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-neutral-300 mb-2">Période de facturation</label>
                <select name="billing_period" class="w-full px-4 py-2 bg-neutral-700 border border-neutral-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="">Toutes les périodes</option>
                    <option value="monthly" {{ request('billing_period') === 'monthly' ? 'selected' : '' }}>Mensuel</option>
                    <option value="quarterly" {{ request('billing_period') === 'quarterly' ? 'selected' : '' }}>Trimestriel</option>
                    <option value="semiannual" {{ request('billing_period') === 'semiannual' ? 'selected' : '' }}>Semestriel</option>
                    <option value="annual" {{ request('billing_period') === 'annual' ? 'selected' : '' }}>Annuel</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-neutral-300 mb-2">Restaurant</label>
                <select name="restaurant" class="w-full px-4 py-2 bg-neutral-700 border border-neutral-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="">Tous les restaurants</option>
                    @foreach($restaurants as $restaurant)
                        <option value="{{ $restaurant->id }}" {{ request('restaurant') == $restaurant->id ? 'selected' : '' }}>{{ $restaurant->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-neutral-300 mb-2">Expiration</label>
                <select name="expiring" class="w-full px-4 py-2 bg-neutral-700 border border-neutral-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                    <option value="">Tous</option>
                    <option value="soon" {{ request('expiring') === 'soon' ? 'selected' : '' }}>Expirent bientôt</option>
                    <option value="expired" {{ request('expiring') === 'expired' ? 'selected' : '' }}>Déjà expirés</option>
                </select>
            </div>
            <div>
                <label class="block text-sm font-medium text-neutral-300 mb-2">Date de début</label>
                <input type="date" 
                       name="date_from" 
                       value="{{ request('date_from') }}"
                       class="w-full px-4 py-2 bg-neutral-700 border border-neutral-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-neutral-300 mb-2">Date de fin</label>
                <input type="date" 
                       name="date_to" 
                       value="{{ request('date_to') }}"
                       class="w-full px-4 py-2 bg-neutral-700 border border-neutral-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
            </div>
        </div>
        <div class="flex items-center gap-3 mt-4">
            <button type="submit" class="btn btn-primary btn-sm">Filtrer</button>
            <a href="{{ route('super-admin.subscriptions.index') }}" class="btn btn-ghost btn-sm">Réinitialiser</a>
        </div>
    </form>

    <!-- Revenue by Period -->
    @if($revenueByPeriod->isNotEmpty())
    <div class="bg-neutral-800/50 border border-neutral-700 rounded-xl p-4 mb-6">
        <h3 class="text-lg font-semibold text-white mb-4">Revenus par période</h3>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach($revenueByPeriod as $period)
                <div class="text-center">
                    <p class="text-sm text-neutral-400 capitalize">{{ $period->billing_period ?? 'N/A' }}</p>
                    <p class="text-xl font-bold text-green-400">{{ number_format($period->total, 0, ',', ' ') }} F</p>
                    <p class="text-xs text-neutral-500">{{ $period->count }} abonnement(s)</p>
                </div>
            @endforeach
        </div>
    </div>
    @endif

    <!-- Subscriptions Table -->
    <div class="bg-neutral-800/50 border border-neutral-700 rounded-xl overflow-hidden">
        <div class="table-responsive">
            <table class="w-full min-w-[600px]">
                <thead class="bg-neutral-900/50">
                    <tr>
                        <th class="px-6 py-3 text-left text-xs font-medium text-neutral-400 uppercase tracking-wider">Restaurant</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-neutral-400 uppercase tracking-wider">Plan</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-neutral-400 uppercase tracking-wider">Statut</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-neutral-400 uppercase tracking-wider">Période</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-neutral-400 uppercase tracking-wider">Montant</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-neutral-400 uppercase tracking-wider">Dates</th>
                        <th class="px-6 py-3 text-left text-xs font-medium text-neutral-400 uppercase tracking-wider">Paiement</th>
                        <th class="px-6 py-3 text-right text-xs font-medium text-neutral-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-700">
                    @forelse($subscriptions as $subscription)
                        <tr class="hover:bg-neutral-700/30 transition-colors">
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($subscription->restaurant)
                                    <div class="flex items-center gap-3">
                                        @if($subscription->restaurant->logo_path)
                                            <img src="{{ Storage::url($subscription->restaurant->logo_path) }}" 
                                                 alt="{{ $subscription->restaurant->name }}" 
                                                 class="w-10 h-10 rounded-xl object-cover border border-neutral-600">
                                        @else
                                            <div class="w-10 h-10 bg-gradient-to-br from-primary-500 to-accent-500 rounded-xl flex items-center justify-center text-white font-bold text-sm">
                                                {{ strtoupper(substr($subscription->restaurant->name, 0, 2)) }}
                                            </div>
                                        @endif
                                        <div>
                                            <a href="{{ route('super-admin.restaurants.show', $subscription->restaurant) }}" class="text-sm font-medium text-white hover:text-primary-400 transition-colors">
                                                {{ $subscription->restaurant->name }}
                                            </a>
                                            <div class="text-xs text-neutral-400">{{ $subscription->restaurant->owner?->email ?? 'N/A' }}</div>
                                        </div>
                                    </div>
                                @else
                                    <span class="text-sm text-neutral-500 italic">Restaurant supprimé</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <span class="text-sm text-neutral-300">{{ $subscription->plan?->name ?? 'N/A' }}</span>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                @if($subscription->status->value === 'active')
                                    <span class="badge bg-secondary-500/20 text-secondary-400">Actif</span>
                                @elseif($subscription->status->value === 'expired')
                                    <span class="badge bg-red-500/20 text-red-400">Expiré</span>
                                @else
                                    <span class="badge bg-yellow-500/20 text-yellow-400">En attente</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-neutral-300 capitalize">{{ $subscription->billing_period ?? 'N/A' }}</div>
                                @if($subscription->discount_percentage)
                                    <div class="text-xs text-green-400">-{{ $subscription->discount_percentage }}%</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm font-medium text-white">{{ number_format($subscription->amount_paid, 0, ',', ' ') }} FCFA</div>
                                @if($subscription->addons->isNotEmpty())
                                    <div class="text-xs text-neutral-400">+ {{ $subscription->addons->count() }} add-on(s)</div>
                                @endif
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-neutral-300">
                                    <div>Début: {{ $subscription->starts_at->format('d/m/Y') }}</div>
                                    <div>Fin: {{ $subscription->ends_at->format('d/m/Y') }}</div>
                                    @if($subscription->status->value === 'active')
                                        <div class="text-xs {{ $subscription->ends_at->diffInDays(now()) <= 7 ? 'text-orange-400' : 'text-neutral-400' }}">
                                            {{ $subscription->ends_at->diffInDays(now()) }} jour(s) restant(s)
                                        </div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap">
                                <div class="text-sm text-neutral-300">
                                    @if($subscription->payment_reference)
                                        <div class="text-xs font-mono">{{ Str::limit($subscription->payment_reference, 15) }}</div>
                                    @endif
                                    @if($subscription->payment_method)
                                        <div class="text-xs text-neutral-400 capitalize">{{ $subscription->payment_method }}</div>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4 whitespace-nowrap text-right">
                                <a href="{{ route('super-admin.subscriptions.show', $subscription) }}" 
                                   class="text-primary-400 hover:text-primary-300 text-sm font-medium">
                                    Voir détails
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="px-6 py-12 text-center">
                                <div class="text-neutral-400">Aucun abonnement trouvé.</div>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        
        <!-- Pagination -->
        @if($subscriptions->hasPages())
            <div class="px-6 py-4 border-t border-neutral-700">
                {{ $subscriptions->links() }}
            </div>
        @endif
    </div>
</x-layouts.admin-super>
