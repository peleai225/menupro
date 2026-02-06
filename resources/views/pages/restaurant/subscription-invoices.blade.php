<x-layouts.admin-restaurant title="Factures">
    <div class="mb-6">
        <h1 class="text-2xl font-bold text-neutral-900">Factures d'abonnement</h1>
        <p class="text-neutral-600 mt-2">Historique de vos factures d'abonnement</p>
    </div>

    <div class="card p-6">
        @if($invoices->count() > 0)
            <div class="overflow-x-auto">
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
