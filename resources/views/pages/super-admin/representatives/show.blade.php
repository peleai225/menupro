@php
    $fmt = fn ($v) => number_format((int) $v, 0, ',', ' ') . ' F';
    $inputStyle = 'background:var(--sa-bg);border-color:var(--sa-border);color:var(--sa-fg);';
    $card = 'rounded-xl p-5 border shadow-sm';
    $cardStyle = 'background:var(--sa-card);border-color:var(--sa-border);';
@endphp
<x-layouts.admin-super title="Représentant — {{ $profile->user?->name }}">
    <div class="flex items-center gap-3 mb-4">
        <a href="{{ route('super-admin.representatives.index') }}" style="color:var(--sa-muted-fg);" aria-label="Retour aux représentants">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <h1 class="text-2xl font-bold" style="color:var(--sa-fg);">{{ $profile->user?->name }}</h1>
        <span class="text-sm" style="color:var(--sa-muted-fg);">Représentant · {{ $profile->city }}</span>
    </div>

    @include('pages.super-admin.representatives._nav')

    <div class="grid grid-cols-2 md:grid-cols-5 gap-3 mb-6">
        @foreach([
            ['Restaurants attribués', $current['restaurants_attributed'], null],
            ['Actifs', $current['restaurants_active'], null],
            ['Abonnements encaissés (mois)', $fmt($current['subscription_collected']), 'Part ' . $fmt($current['subscription_share']) . ' (' . $current['subscription_rate'] . ' %)'],
            ['Commission livraisons (mois)', $fmt($current['delivery_commission']), 'Part ' . $fmt($current['delivery_share']) . ' (' . $current['delivery_rate'] . ' %)'],
            ['Wallet CRM', $fmt(intdiv((int) ($wallet?->balance_cents ?? 0), 100)), 'Total gagné ' . $fmt(intdiv((int) ($wallet?->total_earned_cents ?? 0), 100))],
        ] as [$label, $value, $hint])
            <div class="{{ $card }}" style="{{ $cardStyle }}">
                <p class="text-xs font-medium" style="color:var(--sa-muted-fg);">{{ $label }}</p>
                <p class="text-xl font-bold mt-1 tabular-nums" style="color:var(--sa-fg);">{{ $value }}</p>
                @if($hint)<p class="text-[11px] mt-1" style="color:var(--sa-muted-fg);">{{ $hint }}</p>@endif
            </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            {{-- Objectif trimestriel --}}
            <div class="{{ $card }}" style="{{ $cardStyle }}">
                <div class="flex items-center justify-between mb-2">
                    <p class="text-sm font-semibold" style="color:var(--sa-fg);">Objectif {{ $quarter['label'] }}</p>
                    <p class="text-sm tabular-nums" style="color:var(--sa-fg);">{{ $quarter['achieved'] }} / {{ $quarter['target'] }} restaurants payants</p>
                </div>
                <div class="h-3 rounded-full" style="background:var(--sa-bg);">
                    <div class="h-3 rounded-full" style="width:{{ $quarter['percent'] }}%;background:#f97316;"></div>
                </div>
            </div>

            {{-- Restaurants attribués --}}
            <div class="border rounded-xl shadow-sm overflow-x-auto" style="{{ $cardStyle }}">
                <p class="px-4 pt-4 text-sm font-semibold" style="color:var(--sa-fg);">Restaurants attribués — mois en cours</p>
                <table class="min-w-full text-sm mt-2">
                    <thead style="background:var(--sa-bg);">
                        <tr>
                            @foreach(['Restaurant', 'Attribution', 'Statut', 'Abonnements', 'Comm. livraisons', 'Part'] as $h)
                                <th class="px-4 py-2 text-left text-xs font-bold uppercase" style="color:var(--sa-muted-fg);">{{ $h }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($current['restaurants'] as $r)
                            <tr class="border-t" style="border-color:var(--sa-border);">
                                <td class="px-4 py-2"><a href="{{ route('super-admin.restaurants.show', $r['slug'] ?? $r['restaurant_id']) }}" class="hover:underline" style="color:var(--sa-fg);">{{ $r['name'] }}</a></td>
                                <td class="px-4 py-2 text-xs" style="color:var(--sa-fg);">{{ $r['source'] === 'lien' ? 'Lien de parrainage' : 'Ville exclusive' }}</td>
                                <td class="px-4 py-2 text-xs" style="color:var(--sa-fg);">{{ \App\Enums\RestaurantStatus::tryFrom((string) $r['status'])?->label() ?? $r['status'] }}</td>
                                <td class="px-4 py-2 tabular-nums" style="color:var(--sa-fg);">{{ $fmt($r['subscription_collected']) }}</td>
                                <td class="px-4 py-2 tabular-nums" style="color:var(--sa-fg);">{{ $fmt($r['delivery_commission']) }}</td>
                                <td class="px-4 py-2 tabular-nums font-semibold" style="color:#16a34a;">{{ $fmt($r['share']) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="6" class="px-4 py-8 text-center" style="color:var(--sa-muted-fg);">Aucun restaurant attribué pour l'instant.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Relevés mensuels --}}
            <div class="border rounded-xl shadow-sm overflow-x-auto" style="{{ $cardStyle }}">
                <div class="flex flex-wrap items-center justify-between gap-3 px-4 pt-4">
                    <p class="text-sm font-semibold" style="color:var(--sa-fg);">Relevés mensuels</p>
                    <div class="flex flex-wrap items-center gap-2">
                        <form method="POST" action="{{ route('super-admin.representatives.compute', $profile) }}" class="flex items-center gap-2">
                            @csrf
                            <input type="month" name="month" value="{{ now()->subMonthNoOverflow()->format('Y-m') }}" max="{{ now()->format('Y-m') }}" class="h-9 px-2 rounded-lg border text-sm" style="{{ $inputStyle }}">
                            <button class="h-9 px-3 rounded-lg text-sm font-medium text-white" style="background:#4f46e5;">Calculer / verser</button>
                        </form>
                        <a href="{{ route('super-admin.representatives.export', ['representative' => $profile->id]) }}" class="h-9 px-3 inline-flex items-center rounded-lg border text-sm" style="{{ $inputStyle }}">Export CSV</a>
                    </div>
                </div>
                <table class="min-w-full text-sm mt-3">
                    <thead style="background:var(--sa-bg);">
                        <tr>
                            @foreach(['Mois', 'Restaurants', 'Payants', 'Abonnements', 'Comm. livraisons', 'Part', 'Versé'] as $h)
                                <th class="px-4 py-2 text-left text-xs font-bold uppercase" style="color:var(--sa-muted-fg);">{{ $h }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($statements as $s)
                            <tr class="border-t" style="border-color:var(--sa-border);">
                                <td class="px-4 py-2 font-medium" style="color:var(--sa-fg);">{{ $s->month }}</td>
                                <td class="px-4 py-2 tabular-nums" style="color:var(--sa-fg);">{{ $s->restaurants_attributed }}</td>
                                <td class="px-4 py-2 tabular-nums" style="color:var(--sa-fg);">{{ $s->restaurants_paying }}</td>
                                <td class="px-4 py-2 tabular-nums" style="color:var(--sa-fg);">{{ $fmt($s->subscription_collected) }}</td>
                                <td class="px-4 py-2 tabular-nums" style="color:var(--sa-fg);">{{ $fmt($s->delivery_commission) }}</td>
                                <td class="px-4 py-2 tabular-nums font-semibold" style="color:var(--sa-fg);">{{ $fmt($s->total_share) }}</td>
                                <td class="px-4 py-2 tabular-nums" style="color:#16a34a;">{{ $fmt(intdiv((int) $s->credited_cents, 100)) }}</td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-4 py-8 text-center" style="color:var(--sa-muted-fg);">Aucun relevé calculé.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="space-y-6">
            <form method="POST" action="{{ route('super-admin.representatives.update', $profile) }}" class="{{ $card }} space-y-3" style="{{ $cardStyle }}">
                @csrf @method('PUT')
                <p class="text-xs font-bold uppercase" style="color:var(--sa-muted-fg);">Réglages</p>
                <label class="block text-xs font-medium" style="color:var(--sa-muted-fg);">Ville (territoire)
                    <input name="city" value="{{ old('city', $profile->city) }}" required class="mt-1 w-full h-10 px-3 rounded-xl border text-sm" style="{{ $inputStyle }}">
                </label>
                <div class="grid grid-cols-2 gap-3">
                    <label class="block text-xs font-medium" style="color:var(--sa-muted-fg);">% abonnements
                        <input name="subscription_rate" type="number" step="0.01" min="0" max="100" value="{{ old('subscription_rate', $profile->subscription_rate) }}" required class="mt-1 w-full h-10 px-3 rounded-xl border text-sm" style="{{ $inputStyle }}">
                    </label>
                    <label class="block text-xs font-medium" style="color:var(--sa-muted-fg);">% livraisons
                        <input name="delivery_rate" type="number" step="0.01" min="0" max="100" value="{{ old('delivery_rate', $profile->delivery_rate) }}" required class="mt-1 w-full h-10 px-3 rounded-xl border text-sm" style="{{ $inputStyle }}">
                    </label>
                </div>
                <div class="grid grid-cols-2 gap-3">
                    <label class="block text-xs font-medium" style="color:var(--sa-muted-fg);">Fin d'essai
                        <input name="trial_ends_at" type="date" value="{{ old('trial_ends_at', $profile->trial_ends_at?->toDateString()) }}" class="mt-1 w-full h-10 px-3 rounded-xl border text-sm" style="{{ $inputStyle }}">
                    </label>
                    <label class="block text-xs font-medium" style="color:var(--sa-muted-fg);">Objectif / trimestre
                        <input name="quarterly_target" type="number" min="0" value="{{ old('quarterly_target', $profile->quarterly_target) }}" required class="mt-1 w-full h-10 px-3 rounded-xl border text-sm" style="{{ $inputStyle }}">
                    </label>
                </div>
                <label class="flex items-center gap-2 text-sm" style="color:var(--sa-fg);">
                    <input type="checkbox" name="exclusivity_active" value="1" @checked(old('exclusivity_active', $profile->exclusivity_active))>
                    Exclusivité de la ville active
                </label>
                @if($profile->exclusivity_granted_at)
                    <p class="text-xs" style="color:var(--sa-muted-fg);">Accordée le {{ $profile->exclusivity_granted_at->format('d/m/Y') }} : les restaurants de {{ $profile->city }} comptent à partir de cette date.</p>
                @endif
                <button class="w-full h-10 rounded-xl text-sm font-medium text-white" style="background:#f97316;">Enregistrer</button>
            </form>

            <div class="{{ $card }} text-sm space-y-2" style="{{ $cardStyle }}">
                <p class="text-xs font-bold uppercase" style="color:var(--sa-muted-fg);">Compte</p>
                <p style="color:var(--sa-fg);">Identifiant : {{ $profile->user?->email }}</p>
                <p style="color:var(--sa-fg);">Téléphone : {{ $profile->user?->phone }}</p>
                <p class="break-all" style="color:var(--sa-fg);">Lien de parrainage : <a href="{{ $profile->referral_url }}" class="underline" style="color:#f97316;">{{ $profile->referral_url }}</a></p>
                @if($profile->application)
                    <a href="{{ route('super-admin.partner-applications.show', $profile->application) }}" class="underline" style="color:var(--sa-muted-fg);">Voir la candidature</a>
                @endif
                <p class="text-xs pt-2" style="color:var(--sa-muted-fg);">Les retraits se valident dans <a href="{{ route('crm.admin.withdrawals') }}" class="underline">CRM › Retraits</a>.</p>
            </div>
        </div>
    </div>
</x-layouts.admin-super>
