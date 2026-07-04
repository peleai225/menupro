<x-layouts.crm title="Mes installations">
    <div class="space-y-6">
        {{-- Today's schedule --}}
        <div class="rounded-2xl bg-gray-900 border border-gray-800/50 p-6">
            <h2 class="text-lg font-semibold text-white mb-4">Aujourd'hui</h2>
            <p class="text-sm text-gray-400">Vos installations planifiées apparaîtront ici.</p>
        </div>

        {{-- Wallet --}}
        @livewire('crm.wallet-panel')
    </div>
</x-layouts.crm>
