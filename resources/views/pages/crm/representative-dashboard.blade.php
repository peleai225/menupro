@php
    $fmt = fn ($v) => number_format((int) $v, 0, ',', ' ') . ' F';
    $monthLabel = now()->translatedFormat('F Y');
@endphp
<x-layouts.crm title="Mon territoire — {{ $profile->city }}">
<div class="space-y-6">
    {{-- En-tête territoire --}}
    <div class="bg-gray-900 rounded-2xl border border-gray-800 p-5 flex flex-col md:flex-row md:items-center md:justify-between gap-4">
        <div>
            <p class="text-xs uppercase tracking-wider text-gray-500 font-medium">Représentant de ville</p>
            <h2 class="text-2xl font-bold text-white mt-1">{{ $profile->city }}</h2>
            <p class="text-sm text-gray-400 mt-1">
                {{ $profile->subscription_rate }} % des abonnements · {{ $profile->delivery_rate }} % de la commission MenuPro sur les livraisons
                @if($profile->exclusivity_active) · <span class="text-emerald-400 font-semibold">Exclusivité active</span>@endif
            </p>
            @if($profile->trial_ends_at)
                <p class="text-xs text-gray-500 mt-1">
                    {{ $profile->isInTrial() ? 'Période d\'essai jusqu\'au' : 'Période d\'essai terminée le' }} {{ $profile->trial_ends_at->format('d/m/Y') }}
                </p>
            @endif
        </div>
        <div class="md:text-right">
            <p class="text-xs text-gray-500 mb-1">Votre lien de parrainage</p>
            <div class="flex items-center gap-2" x-data="{ copied: false }">
                <input readonly value="{{ $profile->referral_url }}" class="w-72 max-w-full bg-gray-950 border border-gray-800 rounded-lg px-3 py-2 text-xs text-gray-300">
                <button type="button" class="px-3 py-2 rounded-lg bg-orange-500 hover:bg-orange-600 text-white text-xs font-semibold"
                        @click="navigator.clipboard.writeText('{{ $profile->referral_url }}'); copied = true; setTimeout(() => copied = false, 2000)"
                        x-text="copied ? 'Copié' : 'Copier'">Copier</button>
            </div>
        </div>
    </div>

    {{-- Chiffres du mois --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        @foreach([
            ['Restaurants attribués', $current['restaurants_attributed'], $current['restaurants_active'] . ' actif(s)'],
            ['Abonnements encaissés', $fmt($current['subscription_collected']), $monthLabel],
            ['Commission livraisons', $fmt($current['delivery_commission']), $monthLabel],
            ['Ma part du mois', $fmt($current['total_share']), 'Solde wallet : ' . $wallet->balance_formatted],
        ] as [$label, $value, $hint])
            <div class="bg-gray-900 rounded-2xl border border-gray-800 p-5">
                <h3 class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-2">{{ $label }}</h3>
                <p class="text-2xl font-bold text-white">{{ $value }}</p>
                <p class="text-xs text-gray-500 mt-1">{{ $hint }}</p>
            </div>
        @endforeach
    </div>

    {{-- Objectif trimestriel --}}
    <div class="bg-gray-900 rounded-2xl border border-gray-800 p-5">
        <div class="flex items-center justify-between mb-3">
            <h3 class="text-sm font-semibold text-white">Objectif {{ $quarter['label'] }}</h3>
            <span class="text-sm text-gray-300">{{ $quarter['achieved'] }} / {{ $quarter['target'] }} restaurants payants</span>
        </div>
        <div class="h-3 rounded-full bg-gray-800 overflow-hidden">
            <div class="h-3 rounded-full bg-gradient-to-r from-orange-500 to-amber-400" style="width:{{ $quarter['percent'] }}%"></div>
        </div>
        <p class="text-xs text-gray-500 mt-2">Atteindre vos objectifs ouvre la voie à l'exclusivité de votre ville.</p>
    </div>

    {{-- Restaurants attribués --}}
    <div class="bg-gray-900 rounded-2xl border border-gray-800 overflow-x-auto">
        <div class="px-5 pt-5 pb-3"><h3 class="text-sm font-semibold text-white">Mes restaurants — {{ $monthLabel }}</h3></div>
        <table class="min-w-full text-sm">
            <thead class="bg-gray-950/60">
                <tr>
                    @foreach(['Restaurant', 'Attribution', 'Statut', 'Abonnements', 'Comm. livraisons', 'Ma part'] as $h)
                        <th class="px-5 py-2 text-left text-xs font-semibold text-gray-500 uppercase">{{ $h }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800">
                @forelse($current['restaurants'] as $r)
                    <tr>
                        <td class="px-5 py-3 text-gray-200">{{ $r['name'] }}</td>
                        <td class="px-5 py-3 text-xs text-gray-400">{{ $r['source'] === 'lien' ? 'Mon lien' : 'Ville exclusive' }}</td>
                        <td class="px-5 py-3 text-xs text-gray-400">{{ \App\Enums\RestaurantStatus::tryFrom((string) $r['status'])?->label() ?? $r['status'] }}</td>
                        <td class="px-5 py-3 text-gray-300 tabular-nums">{{ $fmt($r['subscription_collected']) }}</td>
                        <td class="px-5 py-3 text-gray-300 tabular-nums">{{ $fmt($r['delivery_commission']) }}</td>
                        <td class="px-5 py-3 text-emerald-400 font-semibold tabular-nums">{{ $fmt($r['share']) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-8 text-center text-gray-500">Aucun restaurant pour l'instant : partagez votre lien de parrainage.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Relevés --}}
    <div class="bg-gray-900 rounded-2xl border border-gray-800 overflow-x-auto">
        <div class="px-5 pt-5 pb-3 flex items-center justify-between">
            <h3 class="text-sm font-semibold text-white">Mes relevés mensuels</h3>
            <div class="flex gap-2">
                <a href="{{ route('crm.representative.export') }}" class="px-3 py-1.5 rounded-lg border border-gray-700 text-xs text-gray-300 hover:border-orange-500">Exporter (CSV)</a>
                <a href="{{ route('crm.wallet') }}" class="px-3 py-1.5 rounded-lg bg-orange-500 hover:bg-orange-600 text-xs text-white font-semibold">Demander un retrait</a>
            </div>
        </div>
        <table class="min-w-full text-sm">
            <thead class="bg-gray-950/60">
                <tr>
                    @foreach(['Mois', 'Restaurants', 'Abonnements', 'Comm. livraisons', 'Ma part', 'Versé au wallet'] as $h)
                        <th class="px-5 py-2 text-left text-xs font-semibold text-gray-500 uppercase">{{ $h }}</th>
                    @endforeach
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-800">
                @forelse($statements as $s)
                    <tr>
                        <td class="px-5 py-3 text-gray-200">{{ \Illuminate\Support\Carbon::createFromFormat('Y-m', $s->month)->translatedFormat('F Y') }}</td>
                        <td class="px-5 py-3 text-gray-300 tabular-nums">{{ $s->restaurants_attributed }}</td>
                        <td class="px-5 py-3 text-gray-300 tabular-nums">{{ $fmt($s->subscription_collected) }}</td>
                        <td class="px-5 py-3 text-gray-300 tabular-nums">{{ $fmt($s->delivery_commission) }}</td>
                        <td class="px-5 py-3 text-white font-semibold tabular-nums">{{ $fmt($s->total_share) }}</td>
                        <td class="px-5 py-3 text-emerald-400 tabular-nums">{{ $fmt(intdiv((int) $s->credited_cents, 100)) }}</td>
                    </tr>
                @empty
                    <tr><td colspan="6" class="px-5 py-8 text-center text-gray-500">Votre premier relevé sera calculé au début du mois prochain.</td></tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <p class="text-xs text-gray-600">Vous ne manipulez jamais d'argent : les restaurants paient MenuPro directement. Votre part est calculée chaque mois à partir du journal des transactions et versée sur votre wallet.</p>
</div>
</x-layouts.crm>
