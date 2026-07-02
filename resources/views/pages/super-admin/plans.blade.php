<x-layouts.admin-super title="Plans & Abonnements">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold" style="color:var(--sa-fg);">Plans & Abonnements</h1>
            <p class="mt-1" style="color:var(--sa-muted-fg);">Gérez les plans tarifaires de la plateforme.</p>
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
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-xl text-emerald-700">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700">
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
            <div class="border rounded-2xl p-6 shadow-sm {{ !$plan->is_active ? 'opacity-60' : '' }}" style="background:var(--sa-card);border-color:var(--sa-border);">
                <div class="flex items-center justify-between mb-4">
                    <div class="w-12 h-12 bg-gradient-to-br {{ $colors[$loop->index % 3] }} rounded-xl flex items-center justify-center">
                        <svg class="w-6 h-6 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                        </svg>
                    </div>
                    <div class="flex items-center gap-2">
                        @if($plan->is_featured)
                            <span class="badge bg-emerald-50 text-emerald-700 border border-emerald-200">Populaire</span>
                        @endif
                        @if(!$plan->is_active)
                            <span class="badge bg-red-50 text-red-700 border border-red-200">Inactif</span>
                        @endif
                        <a href="{{ route('super-admin.plans.edit', $plan) }}" class="p-2 rounded-lg" style="color:var(--sa-muted-fg);">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </a>
                    </div>
                </div>
                <h3 class="text-xl font-bold mb-1" style="color:var(--sa-fg);">{{ $plan->name }}</h3>
                <p class="text-2xl font-bold mb-4" style="color:var(--sa-fg);">{{ number_format($plan->price, 0, ',', ' ') }} <span class="text-lg font-normal" style="color:var(--sa-muted-fg);">F/mois</span></p>

                @if($plan->description)
                    <p class="text-sm mb-4" style="color:var(--sa-muted-fg);">{{ $plan->description }}</p>
                @endif

                <div class="space-y-2 text-sm border-t pt-4" style="border-color:var(--sa-border);">
                    <div class="flex items-center justify-between">
                        <span style="color:var(--sa-muted-fg);">Restaurants abonnés</span>
                        <span class="font-medium" style="color:var(--sa-fg);">{{ number_format($plan->restaurants_count ?? 0) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span style="color:var(--sa-muted-fg);">Max plats</span>
                        <span class="font-medium" style="color:var(--sa-fg);">{{ $plan->max_dishes ?? '∞' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span style="color:var(--sa-muted-fg);">Max catégories</span>
                        <span class="font-medium" style="color:var(--sa-fg);">{{ $plan->max_categories ?? '∞' }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span style="color:var(--sa-muted-fg);">Équipe</span>
                        <span class="font-medium" style="color:var(--sa-fg);">{{ $plan->team_members ?? $plan->max_employees ?? 1 }} utilisateurs</span>
                    </div>
                </div>

                <!-- Features -->
                <div class="mt-4 pt-4 border-t space-y-2" style="border-color:var(--sa-border);">
                    @if($plan->has_stock_management)
                        <div class="flex items-center gap-2 text-emerald-700 text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Gestion du stock
                        </div>
                    @endif
                    @if($plan->has_analytics)
                        <div class="flex items-center gap-2 text-emerald-700 text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Statistiques avancées
                        </div>
                    @endif
                    @if($plan->has_priority_support ?? $plan->priority_support ?? false)
                        <div class="flex items-center gap-2 text-emerald-700 text-sm">
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
            <div class="relative w-full max-w-lg border rounded-2xl shadow-xl max-h-[90vh] overflow-y-auto" style="background:var(--sa-card);border-color:var(--sa-border);">
                <div class="sticky top-0 p-6 border-b z-10" style="border-color:var(--sa-border);background:var(--sa-card);">
                    <h2 class="text-xl font-bold" style="color:var(--sa-fg);">Nouveau plan</h2>
                </div>
                <form method="POST" action="{{ route('super-admin.plans.store') }}" class="p-6 space-y-4">
                    @csrf
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <label class="block text-sm font-medium mb-2" style="color:var(--sa-fg);">Nom du plan *</label>
                            <input type="text" name="name" required class="w-full h-10 px-4 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" style="background:var(--sa-card);border-color:var(--sa-border);color:var(--sa-fg);">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2" style="color:var(--sa-fg);">Prix (FCFA) *</label>
                            <input type="number" name="price" required min="0" class="w-full h-10 px-4 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" style="background:var(--sa-card);border-color:var(--sa-border);color:var(--sa-fg);">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2" style="color:var(--sa-fg);">Durée (jours) *</label>
                            <input type="number" name="duration_days" required min="1" value="30" class="w-full h-10 px-4 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" style="background:var(--sa-card);border-color:var(--sa-border);color:var(--sa-fg);">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2" style="color:var(--sa-fg);">Max plats *</label>
                            <input type="number" name="max_dishes" required min="1" class="w-full h-10 px-4 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" style="background:var(--sa-card);border-color:var(--sa-border);color:var(--sa-fg);">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2" style="color:var(--sa-fg);">Max catégories *</label>
                            <input type="number" name="max_categories" required min="1" class="w-full h-10 px-4 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" style="background:var(--sa-card);border-color:var(--sa-border);color:var(--sa-fg);">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2" style="color:var(--sa-fg);">Max employés *</label>
                            <input type="number" name="max_employees" required min="1" value="1" class="w-full h-10 px-4 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" style="background:var(--sa-card);border-color:var(--sa-border);color:var(--sa-fg);">
                        </div>
                        <div>
                            <label class="block text-sm font-medium mb-2" style="color:var(--sa-fg);">Max commandes/mois</label>
                            <input type="number" name="max_orders_per_month" min="1" placeholder="Illimité" class="w-full h-10 px-4 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" style="background:var(--sa-card);border-color:var(--sa-border);color:var(--sa-fg);">
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-medium mb-2" style="color:var(--sa-fg);">Description</label>
                            <textarea name="description" rows="2" class="w-full px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500" style="background:var(--sa-card);border-color:var(--sa-border);color:var(--sa-fg);"></textarea>
                        </div>
                    </div>

                    <div class="grid grid-cols-2 gap-4 pt-4 border-t" style="border-color:var(--sa-border);">
                        <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer" style="background:var(--sa-muted);border-color:var(--sa-border);">
                            <input type="checkbox" name="has_delivery" value="1" class="w-4 h-4 text-primary-500 rounded focus:ring-primary-500">
                            <span class="text-sm" style="color:var(--sa-fg);">Livraison</span>
                        </label>
                        <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer" style="background:var(--sa-muted);border-color:var(--sa-border);">
                            <input type="checkbox" name="has_stock_management" value="1" class="w-4 h-4 text-primary-500 rounded focus:ring-primary-500">
                            <span class="text-sm" style="color:var(--sa-fg);">Gestion stock</span>
                        </label>
                        <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer" style="background:var(--sa-muted);border-color:var(--sa-border);">
                            <input type="checkbox" name="has_analytics" value="1" class="w-4 h-4 text-primary-500 rounded focus:ring-primary-500">
                            <span class="text-sm" style="color:var(--sa-fg);">Statistiques</span>
                        </label>
                        <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer" style="background:var(--sa-muted);border-color:var(--sa-border);">
                            <input type="checkbox" name="has_priority_support" value="1" class="w-4 h-4 text-primary-500 rounded focus:ring-primary-500">
                            <span class="text-sm" style="color:var(--sa-fg);">Support prioritaire</span>
                        </label>
                        <label class="flex items-center gap-3 p-3 border rounded-lg cursor-pointer col-span-2" style="background:var(--sa-muted);border-color:var(--sa-border);">
                            <input type="checkbox" name="is_featured" value="1" class="w-4 h-4 text-primary-500 rounded focus:ring-primary-500">
                            <span class="text-sm" style="color:var(--sa-fg);">Plan mis en avant (populaire)</span>
                        </label>
                    </div>

                    <div class="flex gap-3 pt-4">
                        <button type="button" onclick="document.getElementById('addPlanModal').classList.add('hidden')" class="flex-1 h-10 px-4 rounded-lg font-medium transition-colors" style="background:var(--sa-muted);color:var(--sa-fg);">
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
