<!-- Modify Order Modal -->
<div id="modify-order-modal" 
     class="fixed inset-0 z-50 hidden overflow-y-auto"
     style="display: none;"
     x-data="{ show: false }"
     x-show="show"
     x-cloak
     x-init="
        window.openModifyModal = () => { 
            show = true; 
            document.getElementById('modify-order-modal').classList.remove('hidden');
        };
        window.closeModifyModal = () => { 
            show = false; 
            document.getElementById('modify-order-modal').classList.add('hidden');
        };
     "
     @keydown.escape.window="show = false; document.getElementById('modify-order-modal').classList.add('hidden');"
     @click.away="if ($event.target.classList.contains('fixed')) { show = false; document.getElementById('modify-order-modal').classList.add('hidden'); }">
    
    <!-- Backdrop -->
    <div class="fixed inset-0 bg-black/50 backdrop-blur-sm transition-opacity" 
         @click="show = false"></div>

    <!-- Modal Content -->
    <div class="relative w-full max-w-4xl mx-auto my-8 bg-white rounded-2xl shadow-2xl max-h-[90vh] overflow-y-auto"
         x-transition:enter="transition ease-out duration-300"
         x-transition:enter-start="opacity-0 scale-95"
         x-transition:enter-end="opacity-100 scale-100">
        
        <!-- Header -->
        <div class="sticky top-0 bg-white p-6 border-b border-neutral-100 z-10">
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-xl font-bold text-neutral-900">Modifier la commande #{{ $order->reference }}</h2>
                    <p class="text-sm text-neutral-500 mt-1">Ajoutez, retirez ou modifiez les articles</p>
                </div>
                <button @click="show = false" class="p-2 hover:bg-neutral-100 rounded-lg">
                    <svg class="w-6 h-6 text-neutral-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                    </svg>
                </button>
            </div>
        </div>

        <!-- Body -->
        <div class="p-6 space-y-6">
            <!-- Add Item Section -->
            <div>
                <h3 class="text-lg font-semibold text-neutral-900 mb-4">Ajouter un article</h3>
                <form id="add-item-form" class="space-y-4">
                    @csrf
                    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-neutral-700 mb-2">Plat</label>
                            <select name="dish_id" 
                                    id="dish-select"
                                    class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                    required>
                                <option value="">Sélectionner un plat...</option>
                                @foreach($availableDishes ?? [] as $dish)
                                    <option value="{{ $dish->id }}" data-price="{{ $dish->price }}">
                                        {{ $dish->name }} - {{ number_format($dish->price, 0, ',', ' ') }} F
                                    </option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-2">Quantité</label>
                            <input type="number" 
                                   name="quantity" 
                                   value="1" 
                                   min="1" 
                                   max="99"
                                   class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                   required>
                        </div>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-neutral-700 mb-2">Instructions spéciales (optionnel)</label>
                        <textarea name="special_instructions" 
                                  rows="2"
                                  class="w-full px-4 py-2 border border-neutral-300 rounded-lg focus:ring-2 focus:ring-primary-500 focus:border-transparent"
                                  placeholder="Ex: Pas trop pimenté..."></textarea>
                    </div>
                    <button type="submit" 
                            class="btn btn-primary w-full">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Ajouter l'article
                    </button>
                </form>
            </div>

            <!-- Current Items -->
            <div>
                <h3 class="text-lg font-semibold text-neutral-900 mb-4">Articles actuels</h3>
                <div class="space-y-3" id="modal-items-list">
                    @foreach($order->items as $item)
                        <div class="p-4 border border-neutral-200 rounded-lg flex items-center justify-between" data-item-id="{{ $item->id }}">
                            <div class="flex-1">
                                <p class="font-medium text-neutral-900">{{ $item->dish_name }}</p>
                                <p class="text-sm text-neutral-500">
                                    {{ number_format($item->unit_price, 0, ',', ' ') }} F × 
                                    <input type="number" 
                                           value="{{ $item->quantity }}" 
                                           min="1" 
                                           max="99"
                                           data-item-id="{{ $item->id }}"
                                           onchange="updateItemQuantity({{ $item->id }}, this.value)"
                                           class="w-16 px-2 py-1 border border-neutral-300 rounded text-sm">
                                </p>
                            </div>
                            <div class="flex items-center gap-4">
                                <span class="font-bold text-neutral-900">
                                    {{ number_format($item->total_price, 0, ',', ' ') }} F
                                </span>
                                <button 
                                    onclick="removeItemFromModal({{ $item->id }})"
                                    class="p-2 text-red-600 hover:bg-red-50 rounded-lg transition-colors"
                                    title="Retirer">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="sticky bottom-0 bg-neutral-50 p-6 border-t border-neutral-100 flex items-center justify-between">
            <div>
                <p class="text-sm text-neutral-500">Nouveau total</p>
                <p class="text-2xl font-bold text-primary-600" id="modal-total">
                    {{ number_format($order->total, 0, ',', ' ') }} F
                </p>
            </div>
            <div class="flex gap-3">
                <button @click="show = false" class="btn btn-ghost">
                    Annuler
                </button>
                <button onclick="closeModifyModal(); window.location.reload();" class="btn btn-primary">
                    Terminer
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    // orderId et csrfToken sont déjà déclarés dans order-show.blade.php (parent)
    (function() {
        const orderId = {{ $order->id }};
        const csrfToken = '{{ csrf_token() }}';

    // Add item form
    document.getElementById('add-item-form').addEventListener('submit', async (e) => {
        e.preventDefault();
        
        const formData = new FormData(e.target);
        const data = {
            dish_id: formData.get('dish_id'),
            quantity: parseInt(formData.get('quantity')),
            special_instructions: formData.get('special_instructions'),
        };

        try {
            const response = await fetch(`/dashboard/commandes/${orderId}/items`, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify(data),
            });

            const result = await response.json();

            if (result.success) {
                // Reload page to show new item
                window.location.reload();
            } else {
                alert(result.message || 'Erreur lors de l\'ajout.');
            }
        } catch (error) {
            alert('Erreur lors de l\'ajout.');
            console.error(error);
        }
    });

    // Update item quantity
    async function updateItemQuantity(itemId, quantity) {
        if (quantity <= 0) {
            if (confirm('Mettre la quantité à 0 supprimera l\'article. Continuer ?')) {
                await removeItemFromModal(itemId);
            } else {
                // Restore previous value
                const input = document.querySelector(`input[data-item-id="${itemId}"]`);
                input.value = input.getAttribute('data-previous-value') || 1;
            }
            return;
        }

        try {
            const response = await fetch(`/dashboard/commandes/${orderId}/items/${itemId}`, {
                method: 'PATCH',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ quantity: parseInt(quantity) }),
            });

            const result = await response.json();

            if (result.success) {
                // Update item display
                const itemDiv = document.querySelector(`[data-item-id="${itemId}"]`);
                const priceSpan = itemDiv.querySelector('.font-bold');
                priceSpan.textContent = new Intl.NumberFormat('fr-FR').format(result.item.total_price) + ' F';
                
                // Update modal total
                document.getElementById('modal-total').textContent = 
                    new Intl.NumberFormat('fr-FR').format(result.order.total) + ' F';
            } else {
                alert(result.message || 'Erreur lors de la mise à jour.');
                // Restore previous value
                const input = document.querySelector(`input[data-item-id="${itemId}"]`);
                input.value = input.getAttribute('data-previous-value') || 1;
            }
        } catch (error) {
            alert('Erreur lors de la mise à jour.');
            console.error(error);
        }
    }

    // Remove item from modal
    async function removeItemFromModal(itemId) {
        if (!confirm('Êtes-vous sûr de vouloir retirer cet article ?')) {
            return;
        }

        try {
            const response = await fetch(`/dashboard/commandes/${orderId}/items/${itemId}`, {
                method: 'DELETE',
                headers: {
                    'X-CSRF-TOKEN': csrfToken,
                    'Accept': 'application/json',
                },
            });

            const result = await response.json();

            if (result.success) {
                // Remove from DOM
                document.querySelector(`[data-item-id="${itemId}"]`).remove();
                
                // Update modal total
                document.getElementById('modal-total').textContent = 
                    new Intl.NumberFormat('fr-FR').format(result.order.total) + ' F';
            } else {
                alert(result.message || 'Erreur lors de la suppression.');
            }
        } catch (error) {
            alert('Erreur lors de la suppression.');
            console.error(error);
        }
    }

    // Store previous values for inputs
    document.querySelectorAll('input[type="number"][data-item-id]').forEach(input => {
        input.addEventListener('focus', function() {
            this.setAttribute('data-previous-value', this.value);
        });
    });

    // Exposer pour les handlers inline (onclick, onchange)
    window.updateItemQuantity = updateItemQuantity;
    window.removeItemFromModal = removeItemFromModal;
    })();
</script>
