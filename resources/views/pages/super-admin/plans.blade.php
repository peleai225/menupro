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
    <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6 mb-8">
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
                        <button type="button" onclick="document.getElementById('editPlanModal-{{ $plan->id }}').classList.remove('hidden')" class="p-2 rounded-lg" style="color:var(--sa-muted-fg);" title="Modifier">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                            </svg>
                        </button>
                    </div>
                </div>
                @php
                    $limit = fn ($v) => \App\Models\Plan::isUnlimitedValue($v) ? '∞' : number_format($v, 0, ',', ' ');
                @endphp
                <h3 class="text-xl font-bold mb-1" style="color:var(--sa-fg);">{{ $plan->name }} <span class="text-xs font-normal" style="color:var(--sa-muted-fg);">({{ $plan->slug }})</span></h3>
                <p class="text-2xl font-bold mb-1" style="color:var(--sa-fg);">
                    @if($plan->is_on_quote)
                        <span class="text-base font-normal" style="color:var(--sa-muted-fg);">Sur devis, à partir de</span>
                    @endif
                    {{ number_format($plan->price, 0, ',', ' ') }} <span class="text-lg font-normal" style="color:var(--sa-muted-fg);">F/mois</span>
                </p>
                @if($plan->weekly_price)
                    <p class="text-sm mb-3" style="color:var(--sa-muted-fg);">ou {{ number_format($plan->weekly_price, 0, ',', ' ') }} F/semaine</p>
                @endif
                <p class="text-xs mb-4" style="color:var(--sa-muted-fg);">Commission livraison : {{ rtrim(rtrim(number_format($plan->commissionRate(), 2, ',', ''), '0'), ',') }} %</p>

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
                        <span class="font-medium" style="color:var(--sa-fg);">{{ $limit($plan->max_dishes) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span style="color:var(--sa-muted-fg);">Max catégories</span>
                        <span class="font-medium" style="color:var(--sa-fg);">{{ $limit($plan->max_categories) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span style="color:var(--sa-muted-fg);">Employés</span>
                        <span class="font-medium" style="color:var(--sa-fg);">{{ (int) $plan->max_employees === 0 ? 'Gérant seul' : $limit($plan->max_employees) }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span style="color:var(--sa-muted-fg);">Quota commandes/mois (souple)</span>
                        <span class="font-medium" style="color:var(--sa-fg);">{{ $limit($plan->max_orders_per_month) }}</span>
                    </div>
                </div>

                <!-- Features -->
                <div class="mt-4 pt-4 border-t space-y-2" style="border-color:var(--sa-border);">
                    @foreach([
                        'has_delivery' => 'Livraison MenuPro',
                        'has_stock_management' => 'Stock simple',
                        'has_ingredient_stock' => 'Stock par ingrédient & rentabilité',
                        'has_analytics' => 'Statistiques',
                        'has_hotel_rooms' => 'Chambres d\'hôtel',
                        'has_multi_spaces' => 'Multi-espaces',
                        'has_priority_support' => 'Support prioritaire',
                    ] as $field => $label)
                        @if($plan->{$field})
                            <div class="flex items-center gap-2 text-emerald-700 text-sm">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                </svg>
                                {{ $label }}
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>

            <!-- Edit Plan Modal -->
            <div id="editPlanModal-{{ $plan->id }}" class="hidden fixed inset-0 z-50 overflow-y-auto">
                <div class="flex min-h-screen items-center justify-center p-4">
                    <div class="fixed inset-0 bg-black/70" onclick="document.getElementById('editPlanModal-{{ $plan->id }}').classList.add('hidden')"></div>
                    <div class="relative w-full max-w-lg border rounded-2xl shadow-xl max-h-[90vh] overflow-y-auto" style="background:var(--sa-card);border-color:var(--sa-border);">
                        <div class="sticky top-0 p-6 border-b z-10" style="border-color:var(--sa-border);background:var(--sa-card);">
                            <h2 class="text-xl font-bold" style="color:var(--sa-fg);">Modifier le plan {{ $plan->name }}</h2>
                            <p class="text-xs mt-1" style="color:var(--sa-muted-fg);">Les abonnements en cours et leurs montants ne sont pas modifiés.</p>
                        </div>
                        <form method="POST" action="{{ route('super-admin.plans.update', $plan) }}" class="p-6 space-y-4">
                            @csrf
                            @method('PUT')
                            @include('pages.super-admin.plans._form', ['plan' => $plan])
                            <div class="flex gap-3 pt-4">
                                <button type="button" onclick="document.getElementById('editPlanModal-{{ $plan->id }}').classList.add('hidden')" class="flex-1 h-10 px-4 rounded-lg font-medium transition-colors" style="background:var(--sa-muted);color:var(--sa-fg);">
                                    Annuler
                                </button>
                                <button type="submit" class="flex-1 h-10 px-4 bg-primary-500 text-white rounded-lg font-medium hover:bg-primary-600 transition-colors">
                                    Enregistrer
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    @if($errors->any())
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700 text-sm">
            @foreach($errors->all() as $error)
                <p>{{ $error }}</p>
            @endforeach
        </div>
    @endif

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
                    @include('pages.super-admin.plans._form', ['plan' => null])

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
