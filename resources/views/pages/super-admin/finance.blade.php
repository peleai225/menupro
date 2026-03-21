<x-layouts.admin-super title="Gestion Financiere">
    <!-- Header -->
    <div class="mb-8">
        <h1 class="text-2xl font-bold text-neutral-900">Gestion Financiere</h1>
        <p class="text-neutral-500 mt-1">Vue d'ensemble des finances de la plateforme, wallets restaurants et commissions.</p>
    </div>

    <!-- Primary KPI Cards -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        <!-- Solde total wallets -->
        <div class="bg-white border border-neutral-200 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-neutral-500">Solde total wallets</p>
                    <p class="text-2xl font-bold text-neutral-900 mt-1">{{ number_format($stats['total_wallets_balance'], 0, ',', ' ') }} F</p>
                </div>
                <div class="w-12 h-12 bg-primary-50 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M3 14h18m-9-4v8m-7 0h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Total collecte -->
        <div class="bg-white border border-neutral-200 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-neutral-500">Total collecte</p>
                    <p class="text-2xl font-bold text-emerald-600 mt-1">{{ number_format($stats['total_collected'], 0, ',', ' ') }} F</p>
                </div>
                <div class="w-12 h-12 bg-emerald-50 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-emerald-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 11l5-5m0 0l5 5m-5-5v12"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Total retire -->
        <div class="bg-white border border-neutral-200 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-neutral-500">Total retire</p>
                    <p class="text-2xl font-bold text-orange-600 mt-1">{{ number_format($stats['total_withdrawn'], 0, ',', ' ') }} F</p>
                </div>
                <div class="w-12 h-12 bg-orange-50 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-orange-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 13l-5 5m0 0l-5-5m5 5V6"/>
                    </svg>
                </div>
            </div>
        </div>

        <!-- Commissions plateforme -->
        <div class="bg-white border border-neutral-200 rounded-2xl p-5 shadow-sm">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm font-medium text-neutral-500">Commissions plateforme</p>
                    <p class="text-2xl font-bold text-violet-600 mt-1">{{ number_format($stats['total_commissions'], 0, ',', ' ') }} F</p>
                </div>
                <div class="w-12 h-12 bg-violet-50 rounded-xl flex items-center justify-center">
                    <svg class="w-6 h-6 text-violet-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
        </div>
    </div>

    <!-- Secondary Stats -->
    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <div class="bg-white border border-neutral-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs font-medium text-neutral-400 uppercase tracking-wide">Commissions ce mois</p>
            <p class="text-lg font-bold text-neutral-900 mt-1">{{ number_format($stats['commissions_this_month'], 0, ',', ' ') }} F</p>
        </div>
        <div class="bg-white border border-neutral-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs font-medium text-neutral-400 uppercase tracking-wide">Retraits en attente</p>
            <p class="text-lg font-bold text-amber-600 mt-1">{{ $stats['pending_payouts'] }} <span class="text-sm font-normal text-neutral-400">retrait(s)</span></p>
        </div>
        <div class="bg-white border border-neutral-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs font-medium text-neutral-400 uppercase tracking-wide">Montant en attente</p>
            <p class="text-lg font-bold text-amber-600 mt-1">{{ number_format($stats['pending_payouts_amount'], 0, ',', ' ') }} F</p>
        </div>
        <div class="bg-white border border-neutral-200 rounded-xl p-4 shadow-sm">
            <p class="text-xs font-medium text-neutral-400 uppercase tracking-wide">Retraits completes</p>
            <p class="text-lg font-bold text-emerald-600 mt-1">{{ $stats['completed_payouts'] }}</p>
        </div>
    </div>

    <!-- Sub-navigation Tabs -->
    <div class="bg-white border border-neutral-200 rounded-xl mb-8 shadow-sm">
        <nav class="flex border-b border-neutral-200">
            <a href="{{ route('super-admin.finances.index') }}"
               class="px-6 py-3.5 text-sm font-semibold border-b-2 {{ request()->routeIs('super-admin.finances.index') ? 'border-primary-500 text-primary-600' : 'border-transparent text-neutral-500 hover:text-neutral-700 hover:border-neutral-300' }} transition-colors">
                Vue d'ensemble
            </a>
            <a href="{{ route('super-admin.finances.payouts') }}"
               class="px-6 py-3.5 text-sm font-semibold border-b-2 {{ request()->routeIs('super-admin.finances.payouts') ? 'border-primary-500 text-primary-600' : 'border-transparent text-neutral-500 hover:text-neutral-700 hover:border-neutral-300' }} transition-colors">
                Retraits
            </a>
            <a href="{{ route('super-admin.finances.commissions') }}"
               class="px-6 py-3.5 text-sm font-semibold border-b-2 {{ request()->routeIs('super-admin.finances.commissions') ? 'border-primary-500 text-primary-600' : 'border-transparent text-neutral-500 hover:text-neutral-700 hover:border-neutral-300' }} transition-colors">
                Commissions
            </a>
        </nav>
    </div>

    <div class="grid grid-cols-1 xl:grid-cols-3 gap-8">
        <!-- Main Content: Wallets Table -->
        <div class="xl:col-span-2">
            <!-- Search Filter -->
            <form method="GET" class="mb-4">
                <div class="relative">
                    <svg class="absolute left-3 top-1/2 -translate-y-1/2 w-5 h-5 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Rechercher un restaurant..."
                           class="w-full h-11 pl-10 pr-4 bg-white border border-neutral-200 rounded-xl text-neutral-900 placeholder-neutral-400 focus:outline-none focus:ring-2 focus:ring-primary-500/20 focus:border-primary-500 transition-all shadow-sm">
                </div>
            </form>

            <!-- Wallets Table -->
            <div class="bg-white border border-neutral-200 rounded-2xl overflow-hidden shadow-sm">
                <div class="px-6 py-4 border-b border-neutral-100 flex items-center justify-between">
                    <h3 class="font-semibold text-neutral-900">Wallets Restaurants</h3>
                    <div class="flex items-center gap-2">
                        <a href="?sort=balance_desc{{ request('search') ? '&search=' . request('search') : '' }}" class="text-xs px-2.5 py-1 rounded-lg {{ request('sort', 'balance_desc') === 'balance_desc' ? 'bg-primary-50 text-primary-600 font-medium' : 'text-neutral-500 hover:bg-neutral-50' }} transition-colors">Solde desc</a>
                        <a href="?sort=balance_asc{{ request('search') ? '&search=' . request('search') : '' }}" class="text-xs px-2.5 py-1 rounded-lg {{ request('sort') === 'balance_asc' ? 'bg-primary-50 text-primary-600 font-medium' : 'text-neutral-500 hover:bg-neutral-50' }} transition-colors">Solde asc</a>
                    </div>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead class="bg-neutral-50 border-b border-neutral-100">
                            <tr>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 uppercase tracking-wide">Restaurant</th>
                                <th class="px-6 py-3 text-right text-xs font-semibold text-neutral-500 uppercase tracking-wide">Solde</th>
                                <th class="px-6 py-3 text-left text-xs font-semibold text-neutral-500 uppercase tracking-wide">Telephone</th>
                                <th class="px-6 py-3 text-center text-xs font-semibold text-neutral-500 uppercase tracking-wide">Actions</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-neutral-100">
                            @forelse($wallets as $wallet)
                                <tr class="hover:bg-neutral-50 transition-colors">
                                    <td class="px-6 py-4">
                                        <span class="font-medium text-neutral-900">{{ $wallet->restaurant->name ?? 'N/A' }}</span>
                                    </td>
                                    <td class="px-6 py-4 text-right">
                                        <span class="font-semibold text-neutral-900">{{ number_format($wallet->balance, 0, ',', ' ') }} F</span>
                                    </td>
                                    <td class="px-6 py-4">
                                        @if($wallet->phone)
                                            <span class="text-neutral-600 text-sm">{{ $wallet->prefix }}{{ $wallet->phone }}</span>
                                        @else
                                            <span class="text-neutral-400 text-sm">--</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 text-center">
                                        @if($wallet->restaurant)
                                            <a href="{{ route('super-admin.restaurants.show', $wallet->restaurant) }}" class="text-primary-600 hover:text-primary-700 text-sm font-medium transition-colors">
                                                Voir
                                            </a>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="px-6 py-12 text-center text-neutral-400">
                                        Aucun wallet trouve.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- Pagination -->
                @if($wallets->hasPages())
                    <div class="px-6 py-4 border-t border-neutral-100">
                        {{ $wallets->links() }}
                    </div>
                @endif
            </div>
        </div>

        <!-- Sidebar: Recent Activity -->
        <div class="space-y-6">
            <!-- Recent Payouts -->
            <div class="bg-white border border-neutral-200 rounded-2xl shadow-sm">
                <div class="px-5 py-4 border-b border-neutral-100 flex items-center justify-between">
                    <h3 class="font-semibold text-neutral-900 text-sm">Derniers retraits</h3>
                    <a href="{{ route('super-admin.finances.payouts') }}" class="text-xs text-primary-600 hover:text-primary-700 font-medium">Voir tout</a>
                </div>
                <div class="divide-y divide-neutral-50">
                    @forelse($recentPayouts as $payout)
                        <div class="px-5 py-3.5">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-sm font-medium text-neutral-900">{{ $payout->restaurant->name ?? 'N/A' }}</span>
                                @php
                                    $payoutStatusStyles = [
                                        'pending' => 'bg-amber-50 text-amber-700 border-amber-200',
                                        'completed' => 'bg-emerald-50 text-emerald-700 border-emerald-200',
                                        'failed' => 'bg-red-50 text-red-700 border-red-200',
                                    ];
                                    $payoutStatusLabels = ['pending' => 'En attente', 'completed' => 'Complete', 'failed' => 'Echoue'];
                                @endphp
                                <span class="px-2 py-0.5 rounded-md text-[10px] font-semibold border {{ $payoutStatusStyles[$payout->status] ?? 'bg-neutral-50 text-neutral-600 border-neutral-200' }}">
                                    {{ $payoutStatusLabels[$payout->status] ?? ucfirst($payout->status) }}
                                </span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-neutral-400">{{ $payout->gateway }} &middot; {{ $payout->created_at->format('d/m H:i') }}</span>
                                <span class="text-sm font-semibold text-neutral-900">{{ number_format($payout->amount, 0, ',', ' ') }} F</span>
                            </div>
                        </div>
                    @empty
                        <div class="px-5 py-8 text-center text-neutral-400 text-sm">
                            Aucun retrait recent.
                        </div>
                    @endforelse
                </div>
            </div>

            <!-- Recent Commissions -->
            <div class="bg-white border border-neutral-200 rounded-2xl shadow-sm">
                <div class="px-5 py-4 border-b border-neutral-100 flex items-center justify-between">
                    <h3 class="font-semibold text-neutral-900 text-sm">Dernieres commissions</h3>
                    <a href="{{ route('super-admin.finances.commissions') }}" class="text-xs text-primary-600 hover:text-primary-700 font-medium">Voir tout</a>
                </div>
                <div class="divide-y divide-neutral-50">
                    @forelse($recentCommissions as $commission)
                        <div class="px-5 py-3.5">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-sm font-medium text-neutral-900">{{ $commission->restaurant->name ?? 'N/A' }}</span>
                                <span class="text-sm font-semibold text-violet-600">{{ number_format($commission->amount, 0, ',', ' ') }} F</span>
                            </div>
                            <div class="flex items-center justify-between">
                                <span class="text-xs text-neutral-400">
                                    Commande #{{ $commission->order->id ?? 'N/A' }}
                                </span>
                                <span class="text-xs text-neutral-400">{{ $commission->created_at->format('d/m H:i') }}</span>
                            </div>
                        </div>
                    @empty
                        <div class="px-5 py-8 text-center text-neutral-400 text-sm">
                            Aucune commission recente.
                        </div>
                    @endforelse
                </div>
            </div>
        </div>
    </div>
</x-layouts.admin-super>
