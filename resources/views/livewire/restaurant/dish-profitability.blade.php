<div>
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-neutral-900">Rentabilité des plats</h1>
            <p class="text-sm text-neutral-500 mt-1">Coût, marge et profit par plat</p>
        </div>
        <div class="flex items-center gap-3">
            <select wire:model.live="period" class="text-sm border-neutral-300 rounded-lg focus:ring-primary-500 focus:border-primary-500">
                <option value="7">7 derniers jours</option>
                <option value="30">30 derniers jours</option>
                <option value="90">3 mois</option>
                <option value="365">1 an</option>
            </select>
            <select wire:model.live="sort" class="text-sm border-neutral-300 rounded-lg focus:ring-primary-500 focus:border-primary-500">
                <option value="margin_desc">Marge (haute → basse)</option>
                <option value="margin_asc">Marge (basse → haute)</option>
                <option value="profit_desc">Profit total</option>
                <option value="sold_desc">Plus vendus</option>
                <option value="cost_desc">Coût le plus élevé</option>
            </select>
        </div>
    </div>

    <!-- Summary Cards -->
    @php $t = $this->totals; @endphp
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-xl border border-neutral-200 p-4">
            <p class="text-xs font-medium text-neutral-500 uppercase tracking-wider">CA plats</p>
            <p class="text-2xl font-bold text-neutral-900 mt-1">{{ number_format($t['total_revenue'], 0, ',', ' ') }} <span class="text-sm text-neutral-400">F</span></p>
        </div>
        <div class="bg-white rounded-xl border border-neutral-200 p-4">
            <p class="text-xs font-medium text-neutral-500 uppercase tracking-wider">Coût matières</p>
            <p class="text-2xl font-bold text-red-600 mt-1">{{ number_format($t['total_food_cost'], 0, ',', ' ') }} <span class="text-sm text-red-300">F</span></p>
        </div>
        <div class="bg-white rounded-xl border border-neutral-200 p-4">
            <p class="text-xs font-medium text-neutral-500 uppercase tracking-wider">Marge brute moy.</p>
            <p class="text-2xl font-bold {{ $t['avg_margin'] >= 60 ? 'text-emerald-600' : ($t['avg_margin'] >= 40 ? 'text-yellow-600' : 'text-red-600') }} mt-1">{{ $t['avg_margin'] }} <span class="text-sm">%</span></p>
        </div>
        <div class="bg-white rounded-xl border border-neutral-200 p-4">
            <p class="text-xs font-medium text-neutral-500 uppercase tracking-wider">Profit matières</p>
            <p class="text-2xl font-bold text-emerald-600 mt-1">{{ number_format($t['total_profit'], 0, ',', ' ') }} <span class="text-sm text-emerald-300">F</span></p>
        </div>
    </div>

    <!-- Warning: dishes without cost -->
    @if($t['dishes_without_cost'] > 0)
        <div class="bg-amber-50 border border-amber-200 rounded-xl p-4 mb-6 flex items-start gap-3">
            <svg class="w-5 h-5 text-amber-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
            </svg>
            <div>
                <p class="text-sm font-medium text-amber-800">{{ $t['dishes_without_cost'] }} plat(s) sans coût ingrédient</p>
                <p class="text-xs text-amber-600 mt-0.5">Ajoutez les ingrédients à ces plats pour calculer leur marge réelle.</p>
            </div>
        </div>
    @endif

    <!-- Profitability Table -->
    <div class="bg-white rounded-xl border border-neutral-200 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full text-sm">
                <thead class="bg-neutral-50 border-b border-neutral-200">
                    <tr>
                        <th class="text-left px-5 py-3 font-semibold text-neutral-600">Plat</th>
                        <th class="text-right px-4 py-3 font-semibold text-neutral-600">Prix</th>
                        <th class="text-right px-4 py-3 font-semibold text-neutral-600">Coût</th>
                        <th class="text-right px-4 py-3 font-semibold text-neutral-600">Marge</th>
                        <th class="text-right px-4 py-3 font-semibold text-neutral-600">Vendus</th>
                        <th class="text-right px-5 py-3 font-semibold text-neutral-600">Profit total</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100">
                    @forelse($this->dishes as $dish)
                        <tr class="hover:bg-neutral-50 transition">
                            <td class="px-5 py-3">
                                <div class="flex items-center gap-3">
                                    @if($dish->image_path)
                                        <img src="{{ Storage::url($dish->image_path) }}" alt="{{ $dish->name }}" class="w-9 h-9 rounded-lg object-cover flex-shrink-0">
                                    @else
                                        <div class="w-9 h-9 rounded-lg bg-neutral-100 flex items-center justify-center flex-shrink-0">
                                            <svg class="w-4 h-4 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14"/></svg>
                                        </div>
                                    @endif
                                    <div class="min-w-0">
                                        <p class="font-medium text-neutral-900 truncate">{{ $dish->name }}</p>
                                        @if($dish->food_cost === 0)
                                            <p class="text-[10px] text-amber-600 font-medium">Pas d'ingrédients</p>
                                        @endif
                                    </div>
                                </div>
                            </td>
                            <td class="text-right px-4 py-3 font-medium text-neutral-900">{{ number_format($dish->price, 0, ',', ' ') }} F</td>
                            <td class="text-right px-4 py-3 text-red-600">{{ $dish->food_cost > 0 ? number_format($dish->food_cost, 0, ',', ' ') . ' F' : '—' }}</td>
                            <td class="text-right px-4 py-3">
                                @if($dish->food_cost > 0)
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-semibold
                                        {{ $dish->margin_percent >= 60 ? 'bg-emerald-50 text-emerald-700' : ($dish->margin_percent >= 40 ? 'bg-yellow-50 text-yellow-700' : 'bg-red-50 text-red-700') }}">
                                        {{ $dish->margin_percent }}%
                                    </span>
                                @else
                                    <span class="text-neutral-400">—</span>
                                @endif
                            </td>
                            <td class="text-right px-4 py-3 text-neutral-700">{{ $dish->total_sold }}</td>
                            <td class="text-right px-5 py-3 font-semibold {{ $dish->total_profit >= 0 ? 'text-emerald-600' : 'text-red-600' }}">
                                {{ $dish->total_sold > 0 ? number_format($dish->total_profit, 0, ',', ' ') . ' F' : '—' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="6" class="text-center py-12 text-neutral-500">Aucun plat actif</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    <!-- Legend -->
    <div class="flex flex-wrap items-center gap-4 mt-4 text-xs text-neutral-500">
        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-emerald-500"></span> Marge > 60% (excellente)</span>
        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-yellow-500"></span> Marge 40-60% (correcte)</span>
        <span class="flex items-center gap-1.5"><span class="w-2.5 h-2.5 rounded-full bg-red-500"></span> Marge < 40% (attention)</span>
    </div>
</div>
