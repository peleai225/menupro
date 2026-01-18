<x-layouts.admin-super title="Plans & Abonnements">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-white">Plans & Abonnements</h1>
            <p class="text-neutral-400 mt-1">Gérez les plans tarifaires de la plateforme.</p>
        </div>
        <button onclick="document.getElementById('addPlanModal').classList.remove('hidden')" class="btn btn-primary">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Nouveau plan
        </button>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="mb-6 p-4 bg-secondary-500/20 border border-secondary-500/30 rounded-xl text-secondary-400">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 p-4 bg-red-500/20 border border-red-500/30 rounded-xl text-red-400">
            {{ session('error') }}
        </div>
    @endif

    <!-- Plans Grid -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
        @foreach($plans as $plan)
            @php
                $colors = [
                    0 => 'from-blue-500 to-blue-600',
                    1 => 'from-primary-500 to-primary-600',
                    2 => 'from-accent-500 to-accent-600',
                ];
            @endphp
            <div class="bg-neutral-800/50 border border-neutral-700 rounded-2xl p-6 {{ !$plan->is_active ? 'opacity-50' : '' }}">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-gradient-to-br {{ $colors[$loop->index % 3] }} rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                        </svg>
                    </div>
                    <div class="flex items-center gap-2">
                        @if($plan->is_featured)
                            <span class="badge bg-secondary-500/20 text-secondary-400">Populaire</span>
                        @endif
                        @if(!$plan->is_active)
                            <span class="badge bg-red-500/20 text-red-400">Inactif</span>
                        @endif
                        <a href="{{ route('super-admin.plans.edit', $plan) }}" class="p-2 hover:bg-neutral-700 rounded-lg text-neutral-400">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </a>
                    </div>
                </div>
                <h3 class="text-xl font-bold text-white mb-1">{{ $plan->name }}</h3>
                <p class="text-2xl font-bold text-white mb-4">{{ number_format($plan->price, 0, ',', ' ') }} <span class="text-lg font-normal text-neutral-400">F/mois</span></p>
                
                @if($plan->description)
                    <p class="text-sm text-neutral-500 mb-4">{{ $plan->description }}</p>
                @endif
                
                <div class="space-y-2 text-sm border-t border-neutral-700 pt-4">
                    <div class="flex items-center justify-between">
                        <span class="text-neutral-400">Restaurants abonnés</span>
                        <span class="text-white font-medium">{{ number_format($plan->restaurants_count ?? 0) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-neutral-400">Max plats</span>
                        <span class="text-white font-medium">{{ $plan->max_dishes ?? '∞' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-neutral-400">Max catégories</span>
                        <span class="text-white font-medium">{{ $plan->max_categories ?? '∞' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-neutral-400">Équipe</span>
                        <span class="text-white font-medium">{{ $plan->team_members ?? $plan->max_employees ?? 1 }} utilisateurs</span>
                    </div>
                </div>

                <!-- Features -->
                <div class="mt-4 pt-4 border-t border-neutral-700 space-y-2">
                    @if($plan->has_stock_management)
                        <div class="flex items-center gap-2 text-secondary-400 text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Gestion du stock
                        </div>
                    @endif
                    @if($plan->has_analytics)
                        <div class="flex items-center gap-2 text-secondary-400 text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Statistiques avancées
                        </div>
                    @endif
                    @if($plan->has_priority_support ?? $plan->priority_support ?? false)
                        <div class="flex items-center gap-2 text-secondary-400 text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Support prioritaire
                        </div>
                    @endif
                </div>
            </div>
        @endforeach
    </div>

    <!-- Add Plan Modal -->
    <div id="addPlanModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
        <div class="flex min-h-screen items-center justify-center p-4">
            <div class="fixed inset-0 bg-black/70" onclick="document.getElementById('addPlanModal').classList.add('hidden')"></div>
            <div class="relative w-full max-w-lg bg-neutral-800 border border-neutral-700 rounded-2xl shadow-xl max-h-[90vh] overflow-y-auto">
                <div class="sticky top-0 p-6 border-b border-neutral-700 bg-neutral-800 z-10">
                    <h2 class="text-xl font-bold text-white">Nouveau plan</h2>
                </div>
                <form method="POST" action="{{ route('super-admin.plans.store') }}" class="p-6 space-y-4">
                    @csrf
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-neutral-300 mb-2">Nom du plan *</label>
                            <input type="text" name="name" required class="w-full h-10 px-4 bg-neutral-700 border border-neutral-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-300 mb-2">Prix (FCFA) *</label>
                            <input type="number" name="price" required min="0" class="w-full h-10 px-4 bg-neutral-700 border border-neutral-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-300 mb-2">Durée (jours) *</label>
                            <input type="number" name="duration_days" required min="1" value="30" class="w-full h-10 px-4 bg-neutral-700 border border-neutral-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-300 mb-2">Max plats *</label>
                            <input type="number" name="max_dishes" required min="1" class="w-full h-10 px-4 bg-neutral-700 border border-neutral-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-300 mb-2">Max catégories *</label>
                            <input type="number" name="max_categories" required min="1" class="w-full h-10 px-4 bg-neutral-700 border border-neutral-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-300 mb-2">Max employés *</label>
                            <input type="number" name="max_employees" required min="1" value="1" class="w-full h-10 px-4 bg-neutral-700 border border-neutral-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-300 mb-2">Max commandes/mois</label>
                            <input type="number" name="max_orders_per_month" min="1" placeholder="Illimité" class="w-full h-10 px-4 bg-neutral-700 border border-neutral-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-neutral-300 mb-2">Description</label>
                            <textarea name="description" rows="2" class="w-full px-4 py-2 bg-neutral-700 border border-neutral-600 rounded-lg text-white focus:outline-none focus:ring-2 focus:ring-primary-500"></textarea>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 pt-4 border-t border-neutral-700">
                        <label class="flex items-center gap-3 p-3 bg-neutral-700/50 rounded-lg cursor-pointer">
                            <input type="checkbox" name="has_delivery" value="1" class="w-4 h-4 text-primary-500 rounded focus:ring-primary-500">
                            <span class="text-sm text-neutral-300">Livraison</span>
                        </label>
                        <label class="flex items-center gap-3 p-3 bg-neutral-700/50 rounded-lg cursor-pointer">
                            <input type="checkbox" name="has_stock_management" value="1" class="w-4 h-4 text-primary-500 rounded focus:ring-primary-500">
                            <span class="text-sm text-neutral-300">Gestion stock</span>
                        </label>
                        <label class="flex items-center gap-3 p-3 bg-neutral-700/50 rounded-lg cursor-pointer">
                            <input type="checkbox" name="has_analytics" value="1" class="w-4 h-4 text-primary-500 rounded focus:ring-primary-500">
                            <span class="text-sm text-neutral-300">Statistiques</span>
                        </label>
                        <label class="flex items-center gap-3 p-3 bg-neutral-700/50 rounded-lg cursor-pointer">
                            <input type="checkbox" name="has_priority_support" value="1" class="w-4 h-4 text-primary-500 rounded focus:ring-primary-500">
                            <span class="text-sm text-neutral-300">Support prioritaire</span>
                        </label>
                        <label class="flex items-center gap-3 p-3 bg-neutral-700/50 rounded-lg cursor-pointer col-span-2">
                            <input type="checkbox" name="is_featured" value="1" class="w-4 h-4 text-primary-500 rounded focus:ring-primary-500">
                            <span class="text-sm text-neutral-300">Plan mis en avant (populaire)</span>
                        </label>
                    </div>

                    <div class="flex gap-3 pt-4">
                        <button type="button" onclick="document.getElementById('addPlanModal').classList.add('hidden')" class="flex-1 h-10 px-4 bg-neutral-700 text-white rounded-lg font-medium hover:bg-neutral-600 transition-colors">
                            Annuler
                        </button>
                        <button type="submit" class="flex-1 h-10 px-4 bg-primary-500 text-white rounded-lg font-medium hover:bg-primary-600 transition-colors">
                            Créer le plan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</x-layouts.admin-super>
