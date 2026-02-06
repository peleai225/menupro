<x-layouts.admin-restaurant title="Changer de plan">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-neutral-900">Changer de plan</h1>
        <p class="text-neutral-600 mt-2">Sélectionnez un nouveau plan pour votre restaurant</p>
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
        <div class="mb-6 p-6 bg-gradient-to-r from-blue-50 to-indigo-50 border-2 border-blue-300 rounded-xl">
            <div class="flex items-start gap-4">
                <svg class="w-8 h-8 text-blue-600 flex-shrink-0 mt-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
                <div>
                    <h3 class="text-lg font-bold text-blue-900 mb-2">Convertir votre essai gratuit</h3>
                    <p class="text-blue-800">
                        Vous êtes actuellement en essai gratuit. Choisissez un plan ci-dessous pour convertir votre essai en abonnement payant.
                        @if($subscription->ends_at->isFuture())
                            Il vous reste <strong>{{ $restaurant->days_until_expiration ?? 0 }} jour(s)</strong> d'essai.
                        @endif
                    </p>
                </div>
            </div>
        </div>
    @endif

    <div class="card p-6">
        <p class="text-neutral-600 mb-6">
            @if($isTrial)
                Vous êtes actuellement en <strong>essai gratuit</strong> du plan {{ $currentPlan?->name ?? 'MenuPro' }}.
            @else
                Vous êtes actuellement sur le plan <strong>{{ $currentPlan?->name ?? 'Aucun' }}</strong>.
            @endif
        </p>

        <div class="space-y-4">
            @foreach($plans as $plan)
                <div class="border border-neutral-200 rounded-lg p-6 hover:border-primary-500 transition-colors">
                    <div class="flex items-center justify-between">
                        <div>
                            <h3 class="text-xl font-bold text-neutral-900">{{ $plan->name }}</h3>
                            <p class="text-neutral-600 mt-1">{{ $plan->description }}</p>
                            <p class="text-2xl font-bold text-primary-600 mt-2">
                                {{ number_format($plan->price, 0, ',', ' ') }} F/mois
                            </p>
                        </div>
                        <form method="POST" 
                              action="{{ $isTrial ? route('restaurant.subscription.convertTrial') : route('restaurant.subscription.change') }}" 
                              class="ml-4">
                            @csrf
                            <input type="hidden" name="plan" value="{{ $plan->slug }}">
                            <button type="submit" class="btn btn-primary" 
                                @if($currentPlan && $currentPlan->id === $plan->id && !$isTrial) disabled @endif>
                                @if($isTrial)
                                    Convertir maintenant
                                @elseif($currentPlan && $currentPlan->id === $plan->id)
                                    Plan actuel
                                @else
                                    Choisir ce plan
                                @endif
                            </button>
                        </form>
                    </div>
                </div>
            @endforeach
        </div>
    </div>
</x-layouts.admin-restaurant>
