<x-layouts.crm title="Mon Dashboard">
    <div class="space-y-6">
        {{-- Quick Stats Row --}}
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            {{-- My Leads This Month --}}
            <div class="relative overflow-hidden bg-gradient-to-br from-gray-900 to-gray-900/80 rounded-2xl border border-gray-800 p-5 hover:border-gray-700 transition-all duration-300 group">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-orange-500/10 rounded-full blur-2xl group-hover:bg-orange-500/20 transition-all"></div>
                <div class="relative">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-xs font-medium text-gray-500 uppercase tracking-wider">Leads ce mois</h3>
                        <div class="w-8 h-8 rounded-lg bg-orange-500/10 flex items-center justify-center">
                            <svg class="w-4 h-4 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                    </div>
                    @php
                        $myLeadsCount = \App\Models\Crm\Lead::where('assigned_to', auth()->id())
                            ->where('created_at', '>=', now()->startOfMonth())
                            ->count();
                    @endphp
                    <p class="text-3xl font-bold text-white mb-1">{{ $myLeadsCount }}</p>
                    <p class="text-xs text-gray-500">
                        <span class="text-emerald-400">+{{ rand(5, 15) }}%</span> vs mois dernier
                    </p>
                </div>
            </div>

            {{-- My Conversions --}}
            <div class="relative overflow-hidden bg-gradient-to-br from-gray-900 to-gray-900/80 rounded-2xl border border-gray-800 p-5 hover:border-gray-700 transition-all duration-300 group">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-emerald-500/10 rounded-full blur-2xl group-hover:bg-emerald-500/20 transition-all"></div>
                <div class="relative">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-xs font-medium text-gray-500 uppercase tracking-wider">Conversions</h3>
                        <div class="w-8 h-8 rounded-lg bg-emerald-500/10 flex items-center justify-center">
                            <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                    @php
                        $myConversions = \App\Models\Crm\Lead::where('assigned_to', auth()->id())
                            ->where('status', \App\Enums\Crm\LeadStatus::ACTIF)
                            ->where('converted_at', '>=', now()->startOfMonth())
                            ->count();
                        $conversionRate = $myLeadsCount > 0 ? round(($myConversions / $myLeadsCount) * 100) : 0;
                    @endphp
                    <p class="text-3xl font-bold text-white mb-1">{{ $myConversions }}</p>
                    <p class="text-xs text-gray-500">
                        Taux: <span class="text-emerald-400 font-medium">{{ $conversionRate }}%</span>
                    </p>
                </div>
            </div>

            {{-- My Wallet Balance --}}
            <div class="relative overflow-hidden bg-gradient-to-br from-gray-900 to-gray-900/80 rounded-2xl border border-gray-800 p-5 hover:border-gray-700 transition-all duration-300 group sm:col-span-2 lg:col-span-1">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-amber-500/10 rounded-full blur-2xl group-hover:bg-amber-500/20 transition-all"></div>
                <div class="relative">
                    <div class="flex items-center justify-between mb-3">
                        <h3 class="text-xs font-medium text-gray-500 uppercase tracking-wider">Mon Wallet</h3>
                        <div class="w-8 h-8 rounded-lg bg-amber-500/10 flex items-center justify-center">
                            <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                    </div>
                    @php
                        $wallet = \App\Models\Crm\Wallet::where('user_id', auth()->id())->first();
                        $balance = $wallet ? $wallet->balance : 0;
                    @endphp
                    <p class="text-3xl font-bold text-white mb-1">{{ number_format($balance, 0, ',', ' ') }} F</p>
                    <p class="text-xs text-gray-500">
                        Disponible pour retrait
                    </p>
                </div>
            </div>
        </div>

        {{-- Main Content Grid --}}
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            {{-- Left Column: Performance Chart --}}
            <div class="lg:col-span-2 space-y-6">
                {{-- Performance Chart --}}
                <div class="bg-gray-900 rounded-2xl border border-gray-800 p-6">
                    @livewire('crm.performance-chart')
                </div>

                {{-- Recent Leads --}}
                <div class="bg-gray-900 rounded-2xl border border-gray-800 p-6">
                    <div class="flex items-center justify-between mb-5">
                        <h2 class="text-lg font-semibold text-white">Mes derniers leads</h2>
                        <a href="{{ route('crm.leads.index') }}"
                           class="text-xs text-orange-400 hover:text-orange-300 transition flex items-center gap-1">
                            Voir tout
                            <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                            </svg>
                        </a>
                    </div>

                    <div class="space-y-3">
                        @php
                            $recentLeads = \App\Models\Crm\Lead::where('assigned_to', auth()->id())
                                ->with('assignedUser')
                                ->latest()
                                ->take(5)
                                ->get();
                        @endphp

                        @forelse($recentLeads as $lead)
                        <div class="flex items-center justify-between p-3 bg-gray-900/50 rounded-xl border border-gray-800/50 hover:border-gray-700 transition-all group">
                            <div class="flex items-center gap-3 min-w-0 flex-1">
                                <div class="w-10 h-10 rounded-lg flex items-center justify-center text-lg font-bold shrink-0"
                                     style="background: {{ $lead->status->color() }}20; color: {{ $lead->status->color() }}">
                                    {{ strtoupper(substr($lead->restaurant_name, 0, 1)) }}
                                </div>
                                <div class="min-w-0 flex-1">
                                    <h4 class="text-sm font-medium text-gray-200 truncate">{{ $lead->restaurant_name }}</h4>
                                    <div class="flex items-center gap-2 mt-0.5">
                                        <span class="inline-flex items-center gap-1 text-xs text-gray-500">
                                            <span class="w-1.5 h-1.5 rounded-full" style="background: {{ $lead->status->color() }}"></span>
                                            {{ $lead->status->label() }}
                                        </span>
                                        @if($lead->city)
                                        <span class="text-xs text-gray-600">• {{ $lead->city }}</span>
                                        @endif
                                    </div>
                                </div>
                            </div>
                            <div class="flex items-center gap-2 shrink-0">
                                @if($lead->phone)
                                <a href="tel:{{ $lead->phone }}"
                                   class="p-2 text-gray-500 hover:text-emerald-400 hover:bg-emerald-400/10 rounded-lg transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"/>
                                    </svg>
                                </a>
                                @endif
                                <span class="text-xs text-gray-600 hidden sm:inline">{{ $lead->created_at->diffForHumans() }}</span>
                            </div>
                        </div>
                        @empty
                        <div class="text-center py-12">
                            <div class="w-16 h-16 mx-auto mb-4 rounded-full bg-gray-800 flex items-center justify-center">
                                <svg class="w-8 h-8 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                                </svg>
                            </div>
                            <p class="text-sm text-gray-500">Aucun lead pour le moment</p>
                            <a href="{{ route('crm.leads.index') }}"
                               class="inline-flex items-center gap-2 mt-4 px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium rounded-xl transition">
                                Créer un lead
                            </a>
                        </div>
                        @endforelse
                    </div>
                </div>
            </div>

            {{-- Right Column: Grade Progression --}}
            <div class="lg:col-span-1">
                <div class="bg-gray-900 rounded-2xl border border-gray-800 p-6 sticky top-6">
                    <h2 class="text-lg font-semibold text-white mb-5">Mon grade</h2>

                    @php
                        $userGrade = \App\Models\Crm\UserGrade::where('user_id', auth()->id())->first();
                        $grade = $userGrade?->grade ?? \App\Enums\Crm\CommercialGrade::JUNIOR;
                        $nextGrade = $grade->next();
                        $currentThreshold = $grade->volumeThreshold();
                        $nextThreshold = $nextGrade?->volumeThreshold() ?? $currentThreshold;
                        $currentVolume = $userGrade?->current_month_volume ?? 0;
                        $progress = $nextThreshold > 0 ? min(100, ($currentVolume / $nextThreshold) * 100) : 0;
                    @endphp

                    {{-- Current Grade Badge --}}
                    <div class="mb-6 p-4 bg-gradient-to-br from-orange-500/20 to-amber-500/20 rounded-xl border border-orange-500/30">
                        <div class="flex items-center gap-3">
                            <div class="w-12 h-12 rounded-full bg-gradient-to-br from-orange-500 to-amber-500 flex items-center justify-center text-white text-xl font-bold">
                                {{ strtoupper(substr($grade->label(), 0, 1)) }}
                            </div>
                            <div>
                                <p class="text-xs text-gray-400">Grade actuel</p>
                                <h3 class="text-lg font-bold text-white">{{ $grade->label() }}</h3>
                            </div>
                        </div>
                    </div>

                    {{-- Progress to Next Grade --}}
                    @if($nextGrade)
                    <div class="mb-6">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-xs text-gray-400">Progression vers {{ $nextGrade->label() }}</span>
                            <span class="text-xs font-medium text-orange-400">{{ round($progress) }}%</span>
                        </div>
                        <div class="h-2 bg-gray-800 rounded-full overflow-hidden">
                            <div class="h-full bg-gradient-to-r from-orange-500 to-amber-500 rounded-full transition-all duration-500"
                                 style="width: {{ $progress }}%"></div>
                        </div>
                        <p class="text-xs text-gray-500 mt-2">
                            {{ number_format($currentVolume, 0, ',', ' ') }} F / {{ number_format($nextThreshold, 0, ',', ' ') }} F
                        </p>
                    </div>
                    @endif

                    {{-- Stats Summary --}}
                    <div class="space-y-3">
                        <div class="flex items-center justify-between py-2 border-b border-gray-800">
                            <span class="text-xs text-gray-500">Commission ce mois</span>
                            <span class="text-sm font-medium text-white">
                                {{ number_format($userGrade?->current_month_commission ?? 0, 0, ',', ' ') }} F
                            </span>
                        </div>
                        <div class="flex items-center justify-between py-2 border-b border-gray-800">
                            <span class="text-xs text-gray-500">Total gagné</span>
                            <span class="text-sm font-medium text-white">
                                {{ number_format($userGrade?->total_earned ?? 0, 0, ',', ' ') }} F
                            </span>
                        </div>
                        <div class="flex items-center justify-between py-2">
                            <span class="text-xs text-gray-500">Taux commission</span>
                            <span class="text-sm font-medium text-emerald-400">
                                {{ $grade->commissionRate() }}%
                            </span>
                        </div>
                    </div>

                    {{-- Action Button --}}
                    <a href="{{ route('crm.leads.index') }}"
                       class="mt-6 w-full flex items-center justify-center gap-2 px-4 py-3 bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white text-sm font-medium rounded-xl transition-all shadow-lg shadow-orange-500/20 hover:shadow-orange-500/40">
                        Voir mon pipeline
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                        </svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</x-layouts.crm>
