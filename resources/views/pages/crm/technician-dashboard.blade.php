<x-layouts.crm title="Mes installations">
    <div class="space-y-6">
        {{-- Quick Stats --}}
        @php
            $todayCount = \App\Models\Crm\Installation::forTechnician(auth()->id())->today()->count();
            $enCours = \App\Models\Crm\Installation::forTechnician(auth()->id())->where('status', 'en_cours')->count();
            $doneMonth = \App\Models\Crm\Installation::forTechnician(auth()->id())
                ->where('status', 'terminee')
                ->where('completed_at', '>=', now()->startOfMonth())
                ->count();
        @endphp
        <div class="grid grid-cols-3 gap-3">
            <div class="bg-gray-900 rounded-2xl border border-gray-800/60 p-4 text-center">
                <p class="text-2xl font-bold text-white tabular-nums">{{ $todayCount }}</p>
                <p class="text-[10px] text-gray-500 mt-1">Aujourd'hui</p>
            </div>
            <div class="bg-gray-900 rounded-2xl border border-amber-500/20 p-4 text-center">
                <p class="text-2xl font-bold text-amber-400 tabular-nums">{{ $enCours }}</p>
                <p class="text-[10px] text-gray-500 mt-1">En cours</p>
            </div>
            <div class="bg-gray-900 rounded-2xl border border-emerald-500/20 p-4 text-center">
                <p class="text-2xl font-bold text-emerald-400 tabular-nums">{{ $doneMonth }}</p>
                <p class="text-[10px] text-gray-500 mt-1">Ce mois</p>
            </div>
        </div>

        {{-- Installations list --}}
        @livewire('crm.installation-list')

        {{-- Wallet --}}
        @livewire('crm.wallet-panel')
    </div>
</x-layouts.crm>
