<x-layouts.admin-restaurant title="Factures">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-neutral-900">Factures d'abonnement</h1>
        <p class="text-neutral-600 mt-2">Historique de vos factures d'abonnement</p>
    </div>

    <div class="card p-6">
        @if($invoices->count() > 0)
            {{-- Card stack mobile --}}
            <div class="block lg:hidden space-y-3 px-0 py-3">
                @foreach($invoices as $invoice)
                <div class="bg-white rounded-2xl border border-neutral-200 p-4">
                    <div class="flex items-center justify-between mb-2">
                        <span class="font-semibold text-sm">{{ $invoice->payment_reference ?? '#'.$invoice->id }}</span>
                        <span class="text-xs px-2 py-1 rounded-full bg-green-100 text-green-700">Actif</span>
                    </div>
                    <p class="text-xs text-neutral-500 mb-1">{{ $invoice->created_at->format('d/m/Y') }} · {{ $invoice->plan->name }}</p>
                    <p class="text-sm font-semibold text-neutral-900">{{ number_format($invoice->amount_paid, 0, ',', ' ') }} F</p>
                </div>
                @endforeach
            </div>

            {{-- Table desktop --}}
            <div class="hidden lg:block overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-neutral-200">
                            <th class="text-left py-3 px-4 font-semibold text-neutral-700">Date</th>
                            <th class="text-left py-3 px-4 font-semibold text-neutral-700">Plan</th>
                            <th class="text-left py-3 px-4 font-semibold text-neutral-700">Montant</th>
                            <th class="text-left py-3 px-4 font-semibold text-neutral-700">Référence</th>
                            <th class="text-left py-3 px-4 font-semibold text-neutral-700">Statut</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($invoices as $invoice)
                            <tr class="border-b border-neutral-100 hover:bg-neutral-50">
                                <td class="py-3 px-4">{{ $invoice->created_at->format('d/m/Y') }}</td>
                                <td class="py-3 px-4">{{ $invoice->plan->name }}</td>
                                <td class="py-3 px-4 font-semibold">
                                    {{ number_format($invoice->amount_paid, 0, ',', ' ') }} F
                                </td>
                                <td class="py-3 px-4 text-sm text-neutral-600">
                                    {{ $invoice->payment_reference ?? 'N/A' }}
                                </td>
                                <td class="py-3 px-4">
                                    <span class="badge badge-success">Actif</span>
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <div class="mt-6">
                {{ $invoices->links() }}
            </div>
        @else
            <div class="text-center py-12">
                <p class="text-neutral-600">Aucune facture disponible.</p>
            </div>
        @endif
    </div>
</x-layouts.admin-restaurant>
