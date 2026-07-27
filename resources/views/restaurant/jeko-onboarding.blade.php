<x-layouts.admin-restaurant title="Intégration Jeko">

    {{-- Flash messages --}}
    @if(session('success'))
        <div class="mb-6 p-4 bg-emerald-50 border border-emerald-200 rounded-2xl flex items-start gap-3">
            <svg class="w-5 h-5 text-emerald-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-sm text-emerald-700 font-medium">{{ session('success') }}</p>
        </div>
    @endif

    @if(session('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-2xl flex items-start gap-3">
            <svg class="w-5 h-5 text-red-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <p class="text-sm text-red-700 font-medium">{{ session('error') }}</p>
        </div>
    @endif

    <div class="max-w-2xl">
        {{-- Header --}}
        <div class="mb-8">
            <h1 class="text-2xl font-bold text-neutral-900">Paiements Jeko</h1>
            <p class="text-neutral-500 mt-1">Acceptez les paiements mobile money directement depuis votre menu.</p>
        </div>

        @if($subMerchant)
            {{-- Status card --}}
            @php
                $color = $subMerchant->status->color();
                $colorMap = [
                    'yellow' => ['bg' => 'bg-yellow-50', 'border' => 'border-yellow-200', 'text' => 'text-yellow-700', 'badge' => 'bg-yellow-100 text-yellow-800'],
                    'blue'   => ['bg' => 'bg-blue-50',   'border' => 'border-blue-200',   'text' => 'text-blue-700',   'badge' => 'bg-blue-100 text-blue-800'],
                    'red'    => ['bg' => 'bg-red-50',     'border' => 'border-red-200',     'text' => 'text-red-700',   'badge' => 'bg-red-100 text-red-800'],
                    'green'  => ['bg' => 'bg-emerald-50', 'border' => 'border-emerald-200', 'text' => 'text-emerald-700', 'badge' => 'bg-emerald-100 text-emerald-800'],
                ];
                $c = $colorMap[$color] ?? $colorMap['blue'];
            @endphp

            <div class="card p-6 {{ $c['bg'] }} {{ $c['border'] }} border-2">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <div class="flex items-center gap-3 mb-2">
                            <h2 class="text-lg font-semibold text-neutral-900">Demande d'intégration</h2>
                            <span class="px-2.5 py-0.5 rounded-full text-xs font-bold {{ $c['badge'] }}">
                                {{ $subMerchant->status->label() }}
                            </span>
                        </div>
                        <p class="{{ $c['text'] }} text-sm">
                            @if($subMerchant->isPending())
                                Votre demande est en cours d'examen par l'équipe MenuPro. Vous serez notifié dès validation.
                            @elseif($subMerchant->isApproved())
                                Votre demande a été approuvée. L'intégration technique est en cours de finalisation.
                            @elseif($subMerchant->isRejected())
                                Votre demande a été rejetée.
                                @if($subMerchant->rejected_reason)
                                    Motif : <strong>{{ $subMerchant->rejected_reason }}</strong>
                                @endif
                                Contactez le support pour plus d'informations.
                            @elseif($subMerchant->isIntegrated())
                                Votre restaurant est intégré à Jeko et peut accepter les paiements mobile money.
                            @endif
                        </p>
                    </div>
                </div>

                <dl class="mt-5 grid grid-cols-1 sm:grid-cols-2 gap-4 pt-5 border-t border-neutral-200">
                    <div>
                        <dt class="text-xs font-medium text-neutral-500 uppercase tracking-wide">Nom légal</dt>
                        <dd class="mt-1 text-sm font-semibold text-neutral-900">{{ $subMerchant->legal_name }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-neutral-500 uppercase tracking-wide">Type d'activité</dt>
                        <dd class="mt-1 text-sm font-semibold text-neutral-900">{{ $subMerchant->business_type }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-neutral-500 uppercase tracking-wide">Mobile Money</dt>
                        <dd class="mt-1 text-sm font-semibold text-neutral-900">{{ $subMerchant->mobile_money }}</dd>
                    </div>
                    <div>
                        <dt class="text-xs font-medium text-neutral-500 uppercase tracking-wide">Opérateur</dt>
                        <dd class="mt-1 text-sm font-semibold text-neutral-900">{{ $subMerchant->mobile_money_operator->label() }}</dd>
                    </div>
                    @if($subMerchant->email)
                    <div>
                        <dt class="text-xs font-medium text-neutral-500 uppercase tracking-wide">Email</dt>
                        <dd class="mt-1 text-sm font-semibold text-neutral-900">{{ $subMerchant->email }}</dd>
                    </div>
                    @endif
                    <div>
                        <dt class="text-xs font-medium text-neutral-500 uppercase tracking-wide">Demande soumise le</dt>
                        <dd class="mt-1 text-sm font-semibold text-neutral-900">{{ $subMerchant->created_at->locale('fr')->isoFormat('D MMMM YYYY') }}</dd>
                    </div>
                </dl>
            </div>

        @else
            {{-- Intro card --}}
            <div class="card p-6 mb-6 bg-gradient-to-br from-primary-50 to-blue-50 border border-primary-200">
                <div class="flex items-start gap-4">
                    <div class="w-10 h-10 rounded-xl bg-primary-100 flex items-center justify-center flex-shrink-0">
                        <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-base font-bold text-neutral-900 mb-1">Activez les paiements mobile money</h2>
                        <p class="text-sm text-neutral-600">
                            Remplissez ce formulaire pour demander l'intégration Jeko. L'équipe MenuPro examinera votre dossier sous 24–48 h.
                            Une fois approuvé, vos clients pourront payer directement depuis le menu numérique.
                        </p>
                    </div>
                </div>
            </div>

            {{-- Onboarding form --}}
            <form method="POST" action="{{ route('restaurant.jeko.submit') }}" class="card p-6 space-y-5">
                @csrf

                {{-- Legal name --}}
                <div>
                    <label for="legal_name" class="block text-sm font-medium text-neutral-700 mb-2">
                        Nom légal / Raison sociale <span class="text-red-500">*</span>
                    </label>
                    <input
                        type="text"
                        id="legal_name"
                        name="legal_name"
                        value="{{ old('legal_name', $restaurant->name) }}"
                        placeholder="Ex : Restaurant Le Délice SARL"
                        class="w-full h-12 px-4 bg-white border @error('legal_name') border-red-400 @else border-neutral-200 @enderror rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500"
                    >
                    @error('legal_name')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Business type --}}
                <div>
                    <label for="business_type" class="block text-sm font-medium text-neutral-700 mb-2">
                        Type d'activité
                    </label>
                    <input
                        type="text"
                        id="business_type"
                        name="business_type"
                        value="{{ old('business_type', 'restaurant') }}"
                        placeholder="Ex : restaurant, fast-food, traiteur..."
                        class="w-full h-12 px-4 bg-white border @error('business_type') border-red-400 @else border-neutral-200 @enderror rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500"
                    >
                    @error('business_type')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Mobile Money (phone + operator in a grid) --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-5">
                    <div>
                        <label for="mobile_money" class="block text-sm font-medium text-neutral-700 mb-2">
                            Numéro Mobile Money <span class="text-red-500">*</span>
                        </label>
                        <input
                            type="tel"
                            id="mobile_money"
                            name="mobile_money"
                            value="{{ old('mobile_money') }}"
                            placeholder="Ex : 0707000000 ou +2250707000000"
                            class="w-full h-12 px-4 bg-white border @error('mobile_money') border-red-400 @else border-neutral-200 @enderror rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500"
                        >
                        @error('mobile_money')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="mobile_money_operator" class="block text-sm font-medium text-neutral-700 mb-2">
                            Opérateur <span class="text-red-500">*</span>
                        </label>
                        <select
                            id="mobile_money_operator"
                            name="mobile_money_operator"
                            class="w-full h-12 px-4 bg-white border @error('mobile_money_operator') border-red-400 @else border-neutral-200 @enderror rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500"
                        >
                            <option value="">Choisir un opérateur</option>
                            @foreach($operators as $operator)
                                <option value="{{ $operator->value }}" {{ old('mobile_money_operator') === $operator->value ? 'selected' : '' }}>
                                    {{ $operator->label() }}
                                </option>
                            @endforeach
                        </select>
                        @error('mobile_money_operator')
                            <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                {{-- Email --}}
                <div>
                    <label for="email" class="block text-sm font-medium text-neutral-700 mb-2">
                        Email de contact
                        <span class="text-neutral-400 font-normal">(optionnel — utilise l'email du restaurant par défaut)</span>
                    </label>
                    <input
                        type="email"
                        id="email"
                        name="email"
                        value="{{ old('email', $restaurant->email) }}"
                        placeholder="contact@monrestaurant.ci"
                        class="w-full h-12 px-4 bg-white border @error('email') border-red-400 @else border-neutral-200 @enderror rounded-xl focus:outline-none focus:ring-2 focus:ring-primary-500"
                    >
                    @error('email')
                        <p class="mt-1 text-xs text-red-600">{{ $message }}</p>
                    @enderror
                </div>

                <div class="pt-2 flex justify-end">
                    <button type="submit" class="btn btn-primary">
                        Soumettre ma demande
                    </button>
                </div>
            </form>
        @endif
    </div>

</x-layouts.admin-restaurant>
