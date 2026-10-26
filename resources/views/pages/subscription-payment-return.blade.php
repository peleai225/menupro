<x-layouts.app title="Paiement de l'abonnement" manifestUrl="manifest-admin.json">
    @php
        $planName = $subscription->plan?->name ?? 'MenuPro';
        $amount = number_format((int) $subscription->amount_paid, 0, ',', ' ');
        $operatorLabel = \App\Http\Controllers\Restaurant\SubscriptionController::OPERATORS[$subscription->payment_metadata['jeko_operator'] ?? ''] ?? 'Mobile Money';
        $dashboardUrl = route('restaurant.subscription');
    @endphp

    <div class="min-h-screen bg-neutral-50 flex items-center justify-center px-4 py-10"
         x-data="{
            state: @js($state),
            endsAt: null,
            tries: 0,
            init() { if (this.state === 'pending') this.poll(); },
            poll() {
                if (this.tries++ > 60) { this.state = 'slow'; return; }
                fetch(@js($statusUrl), { headers: { 'Accept': 'application/json' }, cache: 'no-store' })
                    .then(r => r.ok ? r.json() : null)
                    .then(d => {
                        if (d && d.state && d.state !== 'pending') { this.state = d.state; this.endsAt = d.ends_at; return; }
                        setTimeout(() => this.poll(), 3000);
                    })
                    .catch(() => setTimeout(() => this.poll(), 5000));
            }
         }">
        <div class="w-full max-w-md bg-white rounded-3xl shadow-sm border border-neutral-200 p-6 text-center" data-payment-state="{{ $state }}">
            <img src="{{ asset('images/logo-menupro.png') }}" alt="MenuPro" class="h-8 w-auto mx-auto mb-6">

            {{-- En attente de la confirmation Jeko (webhook) --}}
            <div x-show="state === 'pending'" @if($state !== 'pending') x-cloak @endif>
                <div class="w-14 h-14 mx-auto mb-4 rounded-full border-4 border-primary-200 border-t-primary-600 animate-spin" aria-hidden="true"></div>
                <h1 class="text-xl font-bold text-neutral-900 mb-2">Confirmation du paiement…</h1>
                <p class="text-sm text-neutral-600">Nous attendons la confirmation de {{ $operatorLabel }} pour votre abonnement <strong>{{ $planName }}</strong> ({{ $amount }} F). Cela prend en général moins d'une minute.</p>
                <p class="text-xs text-neutral-500 mt-3">Vous pouvez laisser cette page ouverte : elle se met à jour toute seule.</p>
            </div>

            {{-- Confirmation lente --}}
            <div x-show="state === 'slow'" x-cloak>
                <h1 class="text-xl font-bold text-neutral-900 mb-2">Confirmation en cours</h1>
                <p class="text-sm text-neutral-600">La confirmation prend plus de temps que prévu. Si vous avez reçu le SMS de débit, votre abonnement sera activé automatiquement : inutile de payer une deuxième fois.</p>
            </div>

            {{-- Paiement confirmé --}}
            <div x-show="state === 'active'" @if($state !== 'active') x-cloak @endif>
                <div class="w-14 h-14 mx-auto mb-4 rounded-full bg-emerald-100 flex items-center justify-center">
                    <svg class="w-8 h-8 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"/></svg>
                </div>
                <h1 class="text-xl font-bold text-neutral-900 mb-2">Paiement confirmé !</h1>
                <p class="text-sm text-neutral-600">Votre abonnement <strong>{{ $planName }}</strong> est actif<span x-show="endsAt" x-text="' jusqu\'au ' + endsAt"></span>@if($state === 'active' && $subscription->ends_at) jusqu'au {{ $subscription->ends_at->locale('fr')->isoFormat('D MMMM YYYY') }}@endif. Merci !</p>
            </div>

            {{-- Paiement non abouti --}}
            <div x-show="state === 'failed'" @if($state !== 'failed') x-cloak @endif>
                <div class="w-14 h-14 mx-auto mb-4 rounded-full bg-red-100 flex items-center justify-center">
                    <svg class="w-8 h-8 text-red-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" aria-hidden="true"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M6 18L18 6M6 6l12 12"/></svg>
                </div>
                <h1 class="text-xl font-bold text-neutral-900 mb-2">Le paiement n'a pas abouti</h1>
                <p class="text-sm text-neutral-600">Aucun montant n'a été validé pour votre abonnement {{ $planName }}. Causes fréquentes : solde {{ $operatorLabel }} insuffisant, code de confirmation non saisi à temps, ou paiement annulé.</p>
                <p class="text-sm text-neutral-600 mt-2">Vous pouvez réessayer, avec le même opérateur ou un autre.</p>
            </div>

            <div class="mt-6 space-y-3">
                <a href="{{ $dashboardUrl }}"
                   class="flex items-center justify-center w-full min-h-[48px] rounded-xl bg-primary-600 hover:bg-primary-700 text-white font-bold">
                    <span x-text="state === 'failed' ? 'Réessayer le paiement' : 'Aller à mon abonnement'">{{ $state === 'failed' ? 'Réessayer le paiement' : 'Aller à mon abonnement' }}</span>
                </a>
                @unless($isOwner)
                    <p class="text-xs text-neutral-500">Si une page de connexion s'affiche, connectez-vous avec votre numéro : votre paiement est bien enregistré.</p>
                @endunless
                <a href="{{ route('contact') }}" class="block text-sm text-neutral-500 underline min-h-[44px] leading-[44px]">Besoin d'aide ? Contacter le support</a>
            </div>
        </div>
    </div>

    @include('partials.payment-error-logger')
</x-layouts.app>
