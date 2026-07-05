<x-layouts.crm title="Mon Dashboard">
@php
    $user = auth()->user();

    $myLeadsCount = \App\Models\Crm\Lead::where('assigned_to', $user->id)
        ->where('created_at', '>=', now()->startOfMonth())
        ->count();

    $myConversions = \App\Models\Crm\Lead::where('assigned_to', $user->id)
        ->where('status', \App\Enums\Crm\LeadStatus::ACTIF)
        ->where('created_at', '>=', now()->startOfMonth())
        ->count();

    $conversionRate = $myLeadsCount > 0 ? round(($myConversions / $myLeadsCount) * 100) : 0;

    $wallet = \App\Models\Crm\Wallet::where('user_id', $user->id)->first();
    $balanceCents = $wallet?->balance_cents ?? 0;

    $userGrade = \App\Models\Crm\UserGrade::where('user_id', $user->id)->first();
    $grade = $userGrade?->current_grade ?? \App\Enums\Crm\Grade::ROOKIE;
    $totalConversions = $userGrade?->total_conversions ?? 0;
    $progressToNext = $userGrade?->progress_to_next ?? 0;

    $nextGrade = match($grade) {
        \App\Enums\Crm\Grade::ROOKIE   => \App\Enums\Crm\Grade::COMMANDO,
        \App\Enums\Crm\Grade::COMMANDO => \App\Enums\Crm\Grade::ELITE,
        \App\Enums\Crm\Grade::ELITE    => null,
    };
    $nextThreshold = $nextGrade?->minConversions() ?? 0;

    $recentLeads = \App\Models\Crm\Lead::where('assigned_to', $user->id)
        ->latest()
        ->take(5)
        ->get();
@endphp

<div class="space-y-6">
    {{-- Quick Stats --}}
    <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
        <div class="relative overflow-hidden bg-gray-900 rounded-2xl border border-gray-800 p-5 hover:border-gray-700 transition group">
            <div class="absolute -right-4 -top-4 w-24 h-24 bg-orange-500/10 rounded-full blur-2xl group-hover:bg-orange-500/20 transition-all"></div>
            <div class="relative">
                <div class="flex items-center justify-between mb-3">
                    <h3 class="text-xs font-medium text-gray-500 uppercase tracking-wider">Leads ce mois</h3>
                    <div class="w-8 h-8 rounded-lg bg-orange-500/10 flex items-center justify-center">
                        <svg class="w-4 h-4 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                    </div>
                </div>
                <p class="text-3xl font-bold text-white mb-1">{{ $myLeadsCount }}</p>
                <p class="text-xs text-gray-500">prospects ce mois</p>
            </div>
        </div>

        <div class="relative overflow-hidden bg-gray-900 rounded-2xl border border-gray-800 p-5 hover:border-gray-700 transition group">
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
                <p class="text-3xl font-bold text-white mb-1">{{ $myConversions }}</p>
                <p class="text-xs text-gray-500">Taux : <span class="text-emerald-400 font-medium">{{ $conversionRate }}%</span></p>
            </div>
        </div>

        <div class="relative overflow-hidden bg-gray-900 rounded-2xl border border-gray-800 p-5 hover:border-gray-700 transition group">
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
                <p class="text-3xl font-bold text-white mb-1">{{ number_format($balanceCents / 100, 0, ',', ' ') }} F</p>
                <p class="text-xs text-gray-500">Disponible pour retrait</p>
            </div>
        </div>
    </div>

    {{-- Main Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left: Chart + Recent Leads --}}
        <div class="lg:col-span-2 space-y-6">
            <div class="bg-gray-900 rounded-2xl border border-gray-800 p-6">
                @livewire('crm.performance-chart')
            </div>

            <div class="bg-gray-900 rounded-2xl border border-gray-800 p-6">
                <div class="flex items-center justify-between mb-5">
                    <h2 class="text-lg font-semibold text-white">Mes derniers leads</h2>
                    <a href="{{ route('crm.leads.index') }}" class="text-xs text-orange-400 hover:text-orange-300 transition flex items-center gap-1">
                        Voir tout
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                    </a>
                </div>

                <div class="space-y-3">
                    @forelse($recentLeads as $lead)
                    <div class="flex items-center justify-between p-3 bg-gray-800/50 rounded-xl border border-gray-700/50 hover:border-gray-600 transition">
                        <div class="flex items-center gap-3 min-w-0 flex-1">
                            <div class="w-10 h-10 rounded-lg flex items-center justify-center text-sm font-bold shrink-0"
                                 style="background: {{ $lead->status->color() }}20; color: {{ $lead->status->color() }}">
                                {{ strtoupper(substr($lead->restaurant_name, 0, 1)) }}
                            </div>
                            <div class="min-w-0 flex-1">
                                <h4 class="text-sm font-medium text-gray-200 truncate">{{ $lead->restaurant_name }}</h4>
                                <div class="flex items-center gap-2 mt-0.5">
                                    <span class="text-xs text-gray-500 flex items-center gap-1">
                                        <span class="w-1.5 h-1.5 rounded-full inline-block" style="background: {{ $lead->status->color() }}"></span>
                                        {{ $lead->status->label() }}
                                    </span>
                                    @if($lead->city)
                                    <span class="text-xs text-gray-600">• {{ $lead->city }}</span>
                                    @endif
                                </div>
                            </div>
                        </div>
                        <span class="text-xs text-gray-600 hidden sm:inline shrink-0">{{ $lead->created_at->diffForHumans() }}</span>
                    </div>
                    @empty
                    <div class="text-center py-10">
                        <div class="w-14 h-14 mx-auto mb-3 rounded-full bg-gray-800 flex items-center justify-center">
                            <svg class="w-7 h-7 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                            </svg>
                        </div>
                        <p class="text-sm text-gray-500 mb-3">Aucun lead pour le moment</p>
                        <a href="{{ route('crm.leads.index') }}"
                           class="inline-flex items-center gap-2 px-4 py-2 bg-orange-500 hover:bg-orange-600 text-white text-sm font-medium rounded-xl transition">
                            Créer mon premier lead
                        </a>
                    </div>
                    @endforelse
                </div>
            </div>
        </div>

        {{-- Right: Grade --}}
        <div class="lg:col-span-1">
            <div class="bg-gray-900 rounded-2xl border border-gray-800 p-6 sticky top-6">
                <h2 class="text-lg font-semibold text-white mb-5">Mon grade</h2>

                <div class="mb-5 p-4 rounded-xl border
                    @if($grade === \App\Enums\Crm\Grade::ELITE) bg-amber-500/10 border-amber-500/30
                    @elseif($grade === \App\Enums\Crm\Grade::COMMANDO) bg-orange-500/10 border-orange-500/30
                    @else bg-slate-800 border-slate-700 @endif">
                    <div class="flex items-center gap-3">
                        <div class="w-12 h-12 rounded-full flex items-center justify-center text-white text-xl font-bold
                            @if($grade === \App\Enums\Crm\Grade::ELITE) bg-gradient-to-br from-amber-400 to-yellow-500
                            @elseif($grade === \App\Enums\Crm\Grade::COMMANDO) bg-gradient-to-br from-orange-500 to-red-500
                            @else bg-gradient-to-br from-slate-600 to-slate-700 @endif">
                            {{ strtoupper(substr($grade->label(), 0, 1)) }}
                        </div>
                        <div>
                            <p class="text-xs text-gray-400">Grade actuel</p>
                            <h3 class="text-lg font-bold text-white">{{ $grade->label() }}</h3>
                            <p class="text-xs text-gray-500">{{ $grade->title() }}</p>
                        </div>
                    </div>
                </div>

                @if($nextGrade)
                <div class="mb-5">
                    <div class="flex items-center justify-between mb-2">
                        <span class="text-xs text-gray-400">Vers {{ $nextGrade->label() }}</span>
                        <span class="text-xs font-medium text-orange-400">{{ $totalConversions }}/{{ $nextThreshold }}</span>
                    </div>
                    <div class="h-2 bg-gray-800 rounded-full overflow-hidden">
                        <div class="h-full bg-gradient-to-r from-orange-500 to-amber-500 rounded-full transition-all duration-500"
                             style="width: {{ $progressToNext }}%"></div>
                    </div>
                    <p class="text-[11px] text-gray-600 mt-1">{{ $progressToNext }}% complété</p>
                </div>
                @else
                <div class="mb-5 p-3 bg-amber-500/10 border border-amber-500/20 rounded-xl text-center">
                    <p class="text-xs text-amber-400 font-medium">Grade maximum atteint !</p>
                </div>
                @endif

                <div class="space-y-2 border-t border-gray-800 pt-4 mb-5">
                    <div class="flex items-center justify-between py-1.5">
                        <span class="text-xs text-gray-500">Total conversions</span>
                        <span class="text-sm font-medium text-white">{{ $totalConversions }}</span>
                    </div>
                    <div class="flex items-center justify-between py-1.5">
                        <span class="text-xs text-gray-500">Total gagné</span>
                        <span class="text-sm font-medium text-white">{{ number_format(($wallet?->total_earned_cents ?? 0) / 100, 0, ',', ' ') }} F</span>
                    </div>
                    <div class="flex items-center justify-between py-1.5">
                        <span class="text-xs text-gray-500">Total retiré</span>
                        <span class="text-sm font-medium text-white">{{ number_format(($wallet?->total_withdrawn_cents ?? 0) / 100, 0, ',', ' ') }} F</span>
                    </div>
                </div>

                <a href="{{ route('crm.leads.index') }}"
                   class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-gradient-to-r from-orange-500 to-amber-500 hover:from-orange-600 hover:to-amber-600 text-white text-sm font-semibold rounded-xl transition shadow-lg shadow-orange-500/20">
                    Mon pipeline
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>

                <a href="{{ route('crm.report') }}"
                   class="mt-3 w-full flex items-center justify-center gap-2 px-4 py-2.5 bg-gray-800 hover:bg-gray-700 text-gray-300 text-sm font-medium rounded-xl border border-gray-700 transition">
                    <svg class="w-4 h-4 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Rapport terrain du jour
                </a>
            </div>
        </div>
    </div>
</div>
</x-layouts.crm>
