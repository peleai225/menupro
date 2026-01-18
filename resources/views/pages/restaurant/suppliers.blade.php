<x-layouts.admin-restaurant title="Fournisseurs">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-2xl font-bold text-neutral-900">Fournisseurs</h1>
            <p class="text-neutral-500 mt-1">Gérez vos fournisseurs et leurs informations.</p>
        </div>
        <button onclick="document.getElementById('addSupplierModal').classList.remove('hidden')" class="btn btn-primary">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
            </svg>
            Ajouter un fournisseur
        </button>
    </div>

    <!-- Flash Messages -->
    @if(session('success'))
        <div class="mb-6 p-4 bg-secondary-50 border border-secondary-200 rounded-xl text-secondary-700">
            {{ session('success') }}
        </div>
    @endif
    @if(session('error'))
        <div class="mb-6 p-4 bg-red-50 border border-red-200 rounded-xl text-red-700">
            {{ session('error') }}
        </div>
    @endif

    <!-- Suppliers Table -->
    <div class="card overflow-hidden">
        <div class="table-responsive">
            <table class="w-full min-w-[600px]">
                <thead class="bg-neutral-50 border-b border-neutral-200">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-500 uppercase tracking-wider">Fournisseur</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-500 uppercase tracking-wider">Contact</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-500 uppercase tracking-wider">Ingrédients</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-neutral-500 uppercase tracking-wider">Statut</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-neutral-500 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-neutral-100">
                    @forelse($suppliers as $supplier)
                        <tr class="hover:bg-neutral-50 transition-colors">
                            <td class="px-6 py-4">
                                <div>
                                    <p class="font-medium text-neutral-900">{{ $supplier->name }}</p>
                                    @if($supplier->city)
                                        <p class="text-sm text-neutral-500">{{ $supplier->city }}</p>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <div class="space-y-1">
                                    @if($supplier->contact_name)
                                        <p class="text-sm text-neutral-600">{{ $supplier->contact_name }}</p>
                                    @endif
                                    @if($supplier->email)
                                        <p class="text-sm text-neutral-500">{{ $supplier->email }}</p>
                                    @endif
                                    @if($supplier->phone)
                                        <p class="text-sm text-neutral-500">{{ $supplier->phone }}</p>
                                    @endif
                                </div>
                            </td>
                            <td class="px-6 py-4">
                                <span class="text-neutral-600">{{ $supplier->ingredients_count }} ingrédient(s)</span>
                            </td>
                            <td class="px-6 py-4">
                                @if($supplier->is_active ?? true)
                                    <span class="badge badge-success">Actif</span>
                                @else
                                    <span class="badge bg-neutral-100 text-neutral-600">Inactif</span>
                                @endif
                            </td>
                            <td class="px-6 py-4 text-right">
                                <div class="flex items-center justify-end gap-2">
                                    <a href="{{ route('restaurant.stock.fournisseurs.show', $supplier) }}" 
                                       class="p-2 hover:bg-neutral-100 rounded-lg transition-colors"
                                       title="Voir détails">
                                        <svg class="w-5 h-5 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                                        </svg>
                                    </a>
                                    <button onclick="editSupplier({{ $supplier->id }})" 
                                            class="p-2 hover:bg-neutral-100 rounded-lg transition-colors"
                                            title="Modifier">
                                        <svg class="w-5 h-5 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                        </svg>
                                    </button>
                                    <form method="POST" action="{{ route('restaurant.stock.fournisseurs.destroy', $supplier) }}" 
                                          onsubmit="return confirm('Êtes-vous sûr de vouloir supprimer ce fournisseur ?')"
                                          class="inline">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="p-2 hover:bg-red-50 rounded-lg transition-colors" title="Supprimer">
                                            <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                            </svg>
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="px-6 py-12 text-center">
                                <div class="w-16 h-16 bg-neutral-100 rounded-full flex items-center justify-center mx-auto mb-4">
                                    <svg class="w-8 h-8 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                    </svg>
                                </div>
                                <p class="text-neutral-500 mb-4">Aucun fournisseur enregistré</p>
                                <button onclick="document.getElementById('addSupplierModal').classList.remove('hidden')" class="btn btn-primary">
                                    Ajouter votre premier fournisseur
                                </button>
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($suppliers->hasPages())
            <div class="px-6 py-4 border-t border-neutral-200">
                {{ $suppliers->links() }}
            </div>
        @endif
    </div>

    <!-- Add Supplier Modal -->
    <div id="addSupplierModal" class="hidden fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black/50" onclick="document.getElementById('addSupplierModal').classList.add('hidden')"></div>
            <div class="relative bg-white rounded-2xl shadow-xl max-w-2xl w-full p-6 max-h-[90vh] overflow-y-auto">
                <h2 class="text-2xl font-bold text-neutral-900 mb-6">Nouveau fournisseur</h2>
                <form method="POST" action="{{ route('restaurant.stock.fournisseurs.store') }}">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-neutral-700 mb-2">Nom du fournisseur <span class="text-red-500">*</span></label>
                            <input type="text" name="name" required 
                                   class="w-full h-12 px-4 bg-neutral-50 border border-neutral-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">Personne de contact</label>
                            <input type="text" name="contact_name" 
                                   class="w-full h-12 px-4 bg-neutral-50 border border-neutral-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">Email</label>
                            <input type="email" name="email" 
                                   class="w-full h-12 px-4 bg-neutral-50 border border-neutral-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">Téléphone</label>
                            <input type="tel" name="phone" 
                                   class="w-full h-12 px-4 bg-neutral-50 border border-neutral-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-neutral-700 mb-2">Adresse</label>
                            <input type="text" name="address" 
                                   class="w-full h-12 px-4 bg-neutral-50 border border-neutral-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">Ville</label>
                            <input type="text" name="city" 
                                   class="w-full h-12 px-4 bg-neutral-50 border border-neutral-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">Montant minimum de commande</label>
                            <input type="number" name="min_order_amount" min="0" 
                                   class="w-full h-12 px-4 bg-neutral-50 border border-neutral-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">Délai de livraison (jours)</label>
                            <input type="number" name="delivery_days" min="0" 
                                   class="w-full h-12 px-4 bg-neutral-50 border border-neutral-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">Conditions de paiement</label>
                            <input type="text" name="payment_terms" 
                                   class="w-full h-12 px-4 bg-neutral-50 border border-neutral-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500">
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-neutral-700 mb-2">Notes</label>
                            <textarea name="notes" rows="3" 
                                      class="w-full px-4 py-3 bg-neutral-50 border border-neutral-200 rounded-lg focus:outline-none focus:ring-2 focus:ring-primary-500"></textarea>
                        </div>
                    </div>
                    <div class="flex items-center gap-3 mt-6">
                        <button type="submit" class="btn btn-primary flex-1">Créer</button>
                        <button type="button" onclick="document.getElementById('addSupplierModal').classList.add('hidden')" class="btn btn-outline flex-1">Annuler</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            function editSupplier(id) {
                // TODO: Implement edit functionality
                window.location.href = '{{ route('restaurant.stock.fournisseurs.show', ':id') }}'.replace(':id', id);
            }
        </script>
    @endpush
</x-layouts.admin-restaurant>
