<x-layouts.admin-super title="Tarifs par quartiers — {{ $city->name }}">
    <div class="space-y-6">

        {{-- Flash --}}
        @if(session('success'))
            <div class="flex items-center gap-3 px-4 py-3 rounded-xl border text-sm"
                 style="background:rgba(61,158,98,0.10);border-color:rgba(61,158,98,0.20);color:var(--sa-success);">
                <svg class="w-4 h-4 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                </svg>
                <span>{{ session('success') }}</span>
            </div>
        @endif

        {{-- Header --}}
        <div class="flex items-center gap-4">
            <a href="{{ route('super-admin.delivery-cities.show', $city) }}"
               class="p-2 rounded-lg transition-colors hover:opacity-70"
               style="color:var(--sa-muted-fg);">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <div>
                <h1 class="text-xl font-bold" style="color:var(--sa-fg);">Tarifs par quartiers</h1>
                <p class="text-sm mt-0.5" style="color:var(--sa-muted-fg);">
                    {{ $city->name }} — matrice de prix entre zones de livraison.
                    Si une case est vide, le calcul au km s'applique en fallback.
                </p>
            </div>
        </div>

        @if($zones->isEmpty())
            <div class="rounded-2xl border shadow-sm p-12 text-center" style="border-color:var(--sa-border);background:var(--sa-card);">
                <svg class="w-12 h-12 mx-auto mb-4 opacity-40" style="color:var(--sa-muted-fg);" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 13l4.553 2.276A1 1 0 0021 21.382V10.618a1 1 0 00-.553-.894L15 7m0 13V7m0 0L9 7"/>
                </svg>
                <p class="font-medium" style="color:var(--sa-fg);">Aucune zone active pour {{ $city->name }}</p>
                <p class="text-sm mt-1" style="color:var(--sa-muted-fg);">
                    Créez des zones de livraison pour cette ville avant de configurer la matrice de tarifs.
                </p>
                <a href="{{ route('super-admin.delivery-cities.show', $city) }}"
                   class="inline-block mt-4 h-10 px-5 leading-10 rounded-xl text-sm font-medium"
                   style="background:var(--sa-primary);color:var(--sa-primary-fg);">
                    Gérer les zones
                </a>
            </div>
        @else
            <form method="POST" action="{{ route('super-admin.delivery.zone-pricing.store', $city) }}">
                @csrf

                <div class="rounded-2xl border shadow-sm overflow-hidden" style="border-color:var(--sa-border);background:var(--sa-card);">

                    {{-- Légende --}}
                    <div class="px-6 py-4 border-b flex items-start gap-4" style="border-color:var(--sa-border);">
                        <div class="text-sm space-y-1" style="color:var(--sa-muted-fg);">
                            <p><strong style="color:var(--sa-fg);">Lignes</strong> = zone d'origine (restaurant)</p>
                            <p><strong style="color:var(--sa-fg);">Colonnes</strong> = zone de destination (client) — <em>Hors-zone</em> = client hors de toute zone</p>
                            <p>Laissez une case à <strong>0</strong> ou vide pour utiliser le calcul au km.</p>
                        </div>
                    </div>

                    {{-- Matrice --}}
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm border-collapse">
                            <thead>
                                <tr style="background:var(--sa-muted);">
                                    <th class="px-4 py-3 text-left font-semibold sticky left-0 z-10"
                                        style="background:var(--sa-muted);color:var(--sa-fg);border-right:1px solid var(--sa-border);">
                                        Depuis \ Vers
                                    </th>
                                    @foreach($zones as $toZone)
                                        <th class="px-4 py-3 text-center font-semibold whitespace-nowrap"
                                            style="color:var(--sa-fg);border-right:1px solid var(--sa-border);">
                                            {{ $toZone->name }}
                                        </th>
                                    @endforeach
                                    <th class="px-4 py-3 text-center font-semibold whitespace-nowrap"
                                        style="color:var(--sa-muted-fg);font-style:italic;">
                                        Hors-zone
                                    </th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($zones as $fromZone)
                                    <tr style="border-top:1px solid var(--sa-border);">
                                        {{-- Libellé ligne --}}
                                        <td class="px-4 py-3 font-medium sticky left-0 z-10 whitespace-nowrap"
                                            style="background:var(--sa-muted);color:var(--sa-fg);border-right:1px solid var(--sa-border);">
                                            {{ $fromZone->name }}
                                        </td>

                                        {{-- Cellules zone→zone --}}
                                        @foreach($zones as $toZone)
                                            @php
                                                $key = $fromZone->id . '_' . $toZone->id;
                                                $existing = $existingPrices->get($key)?->first();
                                                $value = $existing ? $existing->price_xof : '';
                                            @endphp
                                            <td class="px-3 py-2 text-center" style="border-right:1px solid var(--sa-border);">
                                                <input
                                                    type="number"
                                                    name="prices[{{ $fromZone->id }}][{{ $toZone->id }}]"
                                                    value="{{ $value }}"
                                                    min="0"
                                                    placeholder="—"
                                                    class="w-24 h-9 px-2 rounded-lg border text-center text-sm focus:outline-none focus:ring-2"
                                                    style="background:rgba(243,242,239,0.50);border-color:var(--sa-border);color:var(--sa-fg);"
                                                >
                                            </td>
                                        @endforeach

                                        {{-- Cellule fallback hors-zone --}}
                                        @php
                                            $fallbackKey = $fromZone->id . '_fallback';
                                            $fallbackExisting = $existingPrices->get($fallbackKey)?->first();
                                            $fallbackValue = $fallbackExisting ? $fallbackExisting->price_xof : '';
                                        @endphp
                                        <td class="px-3 py-2 text-center">
                                            <input
                                                type="number"
                                                name="prices[{{ $fromZone->id }}][fallback]"
                                                value="{{ $fallbackValue }}"
                                                min="0"
                                                placeholder="—"
                                                class="w-24 h-9 px-2 rounded-lg border text-center text-sm focus:outline-none focus:ring-2"
                                                style="background:rgba(243,242,239,0.50);border-color:rgba(107,101,96,0.30);color:var(--sa-muted-fg);"
                                            >
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    {{-- Footer --}}
                    <div class="px-6 py-4 border-t flex items-center justify-between gap-4" style="border-color:var(--sa-border);">
                        <p class="text-xs" style="color:var(--sa-muted-fg);">
                            Les prix sont en <strong>centimes XOF</strong> (ex : 150000 = 1 500 FCFA).
                            Les entrées à 0 ou vides ne sont pas enregistrées.
                        </p>
                        <button type="submit"
                                class="h-10 px-6 rounded-xl text-sm font-medium flex items-center gap-2 transition-opacity hover:opacity-90 shrink-0"
                                style="background:var(--sa-primary);color:var(--sa-primary-fg);">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Enregistrer les tarifs
                        </button>
                    </div>
                </div>
            </form>
        @endif
    </div>
</x-layouts.admin-super>
