<x-layouts.crm title="Mon espace">
    <div class="space-y-8">
        {{-- Quick stats --}}
        @livewire('crm.performance-chart')

        {{-- Quick lead creation --}}
        <div class="flex items-center justify-between">
            <h2 class="text-lg font-semibold text-white">Mes leads</h2>
            <a href="{{ route('crm.leads.index') }}"
               class="text-xs text-orange-400 hover:text-orange-300 transition">Voir le pipeline →</a>
        </div>
    </div>
</x-layouts.crm>
