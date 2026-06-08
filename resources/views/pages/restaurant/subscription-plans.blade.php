<x-layouts.admin-restaurant title="Changer de plan">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-neutral-900">Changer de plan</h1>
        <p class="text-neutral-600 mt-2">Choisissez le plan adapte a votre restaurant</p>
    </div>

    @if(session('success'))
        <div class="mb-6 p-4 bg-green-50 border border-green-200 rounded-xl text-green-700">
            {{ session('success') }}
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700">
            {{ session('error') }}
        </div>
    @endif

    @php
        $isTrial = $subscription && $subscription->isTrial();
    @endphp

    @if($isTrial)
        <div class="mb-6 p-5 bg-gradient-to-r from-blue-50 to-indigo-50 border-2 border-blue-300 rounded-xl">
            <div class="flex items-start gap-4">
                <svg class="w-7 h-7 text-blue-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <h3 class="text-base font-bold text-blue-900 mb-1">Essai gratuit en cours</h3>
                    <p class="text-sm text-blue-800">
                        @if($subscription->ends_at->isFuture())
                            Il vous reste <strong>{{ $restaurant->days_until_expiration ?? 0 }} jour(s)</strong> d'essai.
                        @endif
                        Choisissez un plan ci-dessous pour activer votre abonnement.
                    </p>
                </div>
            </div>
        </div>
    @endif

    <!-- Billing period selector -->
    <div x-data="{ period: 'monthly' }" class="space-y-6">
        <div class="card p-4">
            <p class="text-sm font-medium text-neutral-700 mb-3">Periode de facturation :</p>
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-2">
                <button @click="period = 'monthly'" :class="period === 'monthly' ? 'border-primary-500 bg-primary-50 text-primary-700' : 'border-neutral-200 text-neutral-600 hover:border-neutral-300'" class="px-3 py-2 rounded-lg border text-sm font-medium transition-all text-center">
                    Mensuel
                </button>
                <button @click="period = 'quarterly'" :class="period === 'quarterly' ? 'border-primary-500 bg-primary-50 text-primary-700' : 'border-neutral-200 text-neutral-600 hover:border-neutral-300'" class="px-3 py-2 rounded-lg border text-sm font-medium transition-all text-center">
                    Trimestriel <span class="text-secondary-600 font-bold">-10%</span>
                </button>
                <button @click="period = 'semiannual'" :class="period === 'semiannual' ? 'border-primary-500 bg-primary-50 text-primary-700' : 'border-neutral-200 text-neutral-600 hover:border-neutral-300'" class="px-3 py-2 rounded-lg border text-sm font-medium transition-all text-center">
                    Semestriel <span class="text-secondary-600 font-bold">-15%</span>
                </button>
                <button @click="period = 'annual'" :class="period === 'annual' ? 'border-primary-500 bg-primary-50 text-primary-700' : 'border-neutral-200 text-neutral-600 hover:border-neutral-300'" class="px-3 py-2 rounded-lg border text-sm font-medium transition-all text-center">
                    Annuel <span class="text-secondary-600 font-bold">-20%</span>
                </button>
            </div>
        </div>

        <!-- Plans list -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            @foreach($plans as $plan)
                @php
                    $isCurrentPlan = $currentPlan && $currentPlan->id === $plan->id && !$isTrial;
                    $planColors = match($plan->slug) {
                        'essentiel' => ['border' => 'border-neutral-200 hover:border-primary-300', 'badge' => 'bg-neutral-100 text-neutral-700', 'price' => 'text-neutral-900'],
                        'pro' => ['border' => 'border-primary-200 hover:border-primary-400 ring-2 ring-primary-100', 'badge' => 'bg-primary-50 text-primary-700', 'price' => 'text-primary-600'],
                        'business' => ['border' => 'border-amber-200 hover:border-amber-400', 'badge' => 'bg-amber-50 text-amber-700', 'price' => 'text-amber-600'],
                        default => ['border' => 'border-neutral-200', 'badge' => 'bg-neutral-100 text-neutral-700', 'price' => 'text-neutral-900'],
                    };
                @endphp
                <div class="card p-5 {{ $planColors['border'] }} transition-all relative {{ $isCurrentPlan ? 'opacity-70' : '' }}">
                    @if($plan->is_featured)
                        <div class="absolute -top-2.5 left-4">
                            <span class="bg-primary-500 text-white text-[10px] font-bold px-2.5 py-1 rounded-full">POPULAIRE</span>
                        </div>
                    @endif

                    <div class="mb-4">
                        <h3 class="text-lg font-bold text-neutral-900">{{ $plan->name }}</h3>
                        <p class="text-xs text-neutral-500 mt-1">{{ $plan->description }}</p>
                    </div>

                    <!-- Dynamic pricing -->
                    <div class="mb-4">
                        <div class="flex items-baseline gap-1">
                            <span class="text-2xl font-bold {{ $planColors['price'] }}"
                                  x-text="formatPrice({{ $plan->price }}, period)"></span>
                            <span class="text-xs text-neutral-500" x-text="periodLabel(period)"></span>
                        </div>
                        <template x-if="period !== 'monthly'">
                            <p class="text-xs text-secondary-600 mt-1" x-text="'soit ' + formatMonthly({{ $plan->price }}, period) + ' F/mois'"></p>
                        </template>
                    </div>

                    <!-- Key limits -->
                    <div class="space-y-1.5 mb-4 text-sm text-neutral-600">
                        <div class="flex items-center gap-2">
                            <svg class="w-3.5 h-3.5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            <span>{{ $plan->max_dishes >= 9999 ? 'Plats illimites' : $plan->max_dishes . ' plats' }}</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-3.5 h-3.5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            <span>{{ $plan->max_employees }} employe(s)</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <svg class="w-3.5 h-3.5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            <span>{{ $plan->max_orders_per_month >= 9999 ? 'Commandes illimitees' : number_format($plan->max_orders_per_month, 0, ',', ' ') . ' cmd/mois' }}</span>
                        </div>
                        @if($plan->has_delivery)
                        <div class="flex items-center gap-2">
                            <svg class="w-3.5 h-3.5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            <span>Livraison</span>
                        </div>
                        @endif
                        @if($plan->has_stock_management)
                        <div class="flex items-center gap-2">
                            <svg class="w-3.5 h-3.5 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            <span>Gestion stock</span>
                        </div>
                        @endif
                        @if($plan->has_priority_support)
                        <div class="flex items-center gap-2">
                            <svg class="w-3.5 h-3.5 text-amber-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                            <span>Support prioritaire</span>
                        </div>
                        @endif
                    </div>

                    <!-- Action -->
                    <form method="POST"
                          action="{{ $isTrial ? route('restaurant.subscription.convertTrial') : route('restaurant.subscription.change') }}">
                        @csrf
                        <input type="hidden" name="plan" value="{{ $plan->slug }}">
                        <input type="hidden" name="billing_period" x-bind:value="period">
                        <button type="submit"
                                class="w-full py-2.5 rounded-lg text-sm font-bold transition-all {{ $isCurrentPlan ? 'bg-neutral-100 text-neutral-400 cursor-not-allowed' : ($plan->is_featured ? 'bg-primary-500 hover:bg-primary-600 text-white' : 'bg-neutral-900 hover:bg-neutral-800 text-white') }}"
                                {{ $isCurrentPlan ? 'disabled' : '' }}>
                            @if($isCurrentPlan)
                                Plan actuel
                            @elseif($isTrial)
                                Activer ce plan
                            @else
                                Choisir ce plan
                            @endif
                        </button>
                    </form>
                </div>
            @endforeach
        </div>
    </div>

    @push('scripts')
    <script>
        function formatPrice(monthlyPrice, period) {
            const discounts = { monthly: 0, quarterly: 10, semiannual: 15, annual: 20 };
            const months = { monthly: 1, quarterly: 3, semiannual: 6, annual: 12 };
            const m = months[period] || 1;
            const d = discounts[period] || 0;
            const total = monthlyPrice * m;
            const final_price = Math.round((total * (1 - d / 100)) / 100) * 100;
            return new Intl.NumberFormat('fr-FR').format(final_price) + ' F';
        }

        function formatMonthly(monthlyPrice, period) {
            const discounts = { monthly: 0, quarterly: 10, semiannual: 15, annual: 20 };
            const months = { monthly: 1, quarterly: 3, semiannual: 6, annual: 12 };
            const m = months[period] || 1;
            const d = discounts[period] || 0;
            const total = monthlyPrice * m;
            const final_price = Math.round((total * (1 - d / 100)) / 100) * 100;
            return new Intl.NumberFormat('fr-FR').format(Math.round(final_price / m));
        }

        function periodLabel(period) {
            const labels = { monthly: '/mois', quarterly: '/trimestre', semiannual: '/semestre', annual: '/an' };
            return labels[period] || '/mois';
        }
    </script>
    @endpush
</x-layouts.admin-restaurant>
