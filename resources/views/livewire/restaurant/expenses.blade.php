<div x-data="{ loaded: false }" x-init="setTimeout(() => loaded = true, 100)">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <h1 class="text-2xl font-bold text-neutral-900">Dépenses & Rentabilité</h1>
            <p class="text-sm text-neutral-500 mt-1">Suivez vos charges et votre rentabilité réelle</p>
        </div>
        <div class="flex items-center gap-3">
            <select wire:model.live="period" class="text-sm border-neutral-300 rounded-lg focus:ring-primary-500 focus:border-primary-500">
                <option value="week">Cette semaine</option>
                <option value="month">Ce mois</option>
                <option value="last_month">Mois dernier</option>
                <option value="year">Cette année</option>
            </select>
            <button wire:click="openModal()" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition text-sm font-medium">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6"/></svg>
                Ajouter
            </button>
        </div>
    </div>

    <!-- P&L Summary Cards -->
    @php $s = $this->summary; @endphp
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 mb-8">
        <!-- CA Brut -->
        <div class="bg-white rounded-xl border border-neutral-200 p-4">
            <p class="text-xs font-medium text-neutral-500 uppercase tracking-wider">CA Brut</p>
            <p class="text-2xl font-bold text-neutral-900 mt-1">{{ number_format($s['gross_revenue'], 0, ',', ' ') }} <span class="text-sm font-medium text-neutral-400">F</span></p>
            <p class="text-xs text-neutral-400 mt-1">{{ $s['orders_count'] ?? 0 }} commandes</p>
        </div>

        <!-- Total Dépenses -->
        <div class="bg-white rounded-xl border border-neutral-200 p-4">
            <p class="text-xs font-medium text-neutral-500 uppercase tracking-wider">Dépenses</p>
            <p class="text-2xl font-bold text-red-600 mt-1">{{ number_format($s['total_expenses'], 0, ',', ' ') }} <span class="text-sm font-medium text-red-300">F</span></p>
            <p class="text-xs text-neutral-400 mt-1">{{ $s['by_category']->count() ?? 0 }} catégories</p>
        </div>

        <!-- Profit Net -->
        <div class="bg-white rounded-xl border border-neutral-200 p-4">
            <p class="text-xs font-medium text-neutral-500 uppercase tracking-wider">Profit net</p>
            <p class="text-2xl font-bold {{ $s['profit'] >= 0 ? 'text-emerald-600' : 'text-red-600' }} mt-1">{{ number_format($s['profit'], 0, ',', ' ') }} <span class="text-sm font-medium {{ $s['profit'] >= 0 ? 'text-emerald-300' : 'text-red-300' }}">F</span></p>
            <p class="text-xs text-neutral-400 mt-1">Après commission + dépenses</p>
        </div>

        <!-- Marge -->
        <div class="bg-white rounded-xl border border-neutral-200 p-4">
            <p class="text-xs font-medium text-neutral-500 uppercase tracking-wider">Marge</p>
            <p class="text-2xl font-bold {{ $s['margin'] >= 0 ? 'text-emerald-600' : 'text-red-600' }} mt-1">{{ $s['margin'] }} <span class="text-sm font-medium {{ $s['margin'] >= 0 ? 'text-emerald-300' : 'text-red-300' }}">%</span></p>
            <p class="text-xs text-neutral-400 mt-1">Profit / CA</p>
        </div>
    </div>

    <!-- Breakdown by Category -->
    @if($s['by_category']->isNotEmpty())
        <div class="bg-white rounded-xl border border-neutral-200 p-5 mb-8">
            <h3 class="text-sm font-semibold text-neutral-700 mb-4">Répartition des dépenses</h3>
            <div class="space-y-3">
                @foreach($s['by_category'] as $cat)
                    <div class="flex items-center gap-3">
                        <div class="w-8 h-8 rounded-lg flex items-center justify-center flex-shrink-0" style="background-color: {{ $cat['category']->color() }}15;">
                            <svg class="w-4 h-4" style="color: {{ $cat['category']->color() }};" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $cat['category']->icon() }}"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center justify-between mb-1">
                                <span class="text-sm font-medium text-neutral-700">{{ $cat['category']->label() }}</span>
                                <span class="text-sm font-semibold text-neutral-900">{{ number_format($cat['total'], 0, ',', ' ') }} F</span>
                            </div>
                            <div class="h-2 bg-neutral-100 rounded-full overflow-hidden">
                                <div class="h-full rounded-full" style="width: {{ $cat['percent'] }}%; background-color: {{ $cat['category']->color() }};"></div>
                            </div>
                        </div>
                        <span class="text-xs text-neutral-400 w-10 text-right">{{ $cat['percent'] }}%</span>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Filter -->
    <div class="flex items-center gap-3 mb-4">
        <select wire:model.live="categoryFilter" class="text-sm border-neutral-300 rounded-lg focus:ring-primary-500 focus:border-primary-500">
            <option value="">Toutes les catégories</option>
            @foreach(\App\Enums\ExpenseCategory::cases() as $cat)
                <option value="{{ $cat->value }}">{{ $cat->label() }}</option>
            @endforeach
        </select>
    </div>

    <!-- Expenses List -->
    <div class="bg-white rounded-xl border border-neutral-200 overflow-hidden">
        @if($this->expenses->isEmpty())
            <div class="text-center py-16">
                <div class="w-16 h-16 bg-neutral-100 rounded-full flex items-center justify-center mx-auto mb-4">
                    <svg class="w-8 h-8 text-neutral-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 14l6-6m-5.5.5h.01m4.99 5h.01M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16l3.5-2 3.5 2 3.5-2 3.5 2z"/>
                    </svg>
                </div>
                <h3 class="text-lg font-semibold text-neutral-900 mb-1">Aucune dépense</h3>
                <p class="text-sm text-neutral-500 mb-4">Commencez à enregistrer vos charges pour suivre votre rentabilité</p>
                <button wire:click="openModal()" class="inline-flex items-center gap-2 px-4 py-2 bg-primary-600 text-white rounded-lg hover:bg-primary-700 transition text-sm font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m6-6H6"/></svg>
                    Ajouter une dépense
                </button>
            </div>
        @else
            <div class="divide-y divide-neutral-100">
                @foreach($this->expenses as $expense)
                    <div class="flex items-center gap-4 px-5 py-4 hover:bg-neutral-50 transition">
                        <div class="w-10 h-10 rounded-lg flex items-center justify-center flex-shrink-0" style="background-color: {{ $expense->category->color() }}15;">
                            <svg class="w-5 h-5" style="color: {{ $expense->category->color() }};" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="{{ $expense->category->icon() }}"/>
                            </svg>
                        </div>
                        <div class="flex-1 min-w-0">
                            <p class="text-sm font-medium text-neutral-900 truncate">{{ $expense->description }}</p>
                            <div class="flex items-center gap-2 mt-0.5">
                                <span class="text-xs text-neutral-500">{{ $expense->category->label() }}</span>
                                @if($expense->supplier)
                                    <span class="text-xs text-neutral-400">· {{ $expense->supplier }}</span>
                                @endif
                                @if($expense->is_recurring)
                                    <span class="inline-flex items-center px-1.5 py-0.5 bg-blue-50 text-blue-600 rounded text-[10px] font-medium">Récurrent</span>
                                @endif
                            </div>
                        </div>
                        <div class="text-right flex-shrink-0">
                            <p class="text-sm font-semibold text-red-600">-{{ number_format($expense->amount, 0, ',', ' ') }} F</p>
                            <p class="text-xs text-neutral-400 mt-0.5">{{ $expense->expense_date->format('d/m/Y') }}</p>
                        </div>
                        <div class="flex items-center gap-1 flex-shrink-0">
                            <button wire:click="openModal({{ $expense->id }})" class="p-1.5 text-neutral-400 hover:text-primary-600 rounded-lg hover:bg-primary-50 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                            </button>
                            <button wire:click="delete({{ $expense->id }})" wire:confirm="Supprimer cette dépense ?" class="p-1.5 text-neutral-400 hover:text-red-600 rounded-lg hover:bg-red-50 transition">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                            </button>
                        </div>
                    </div>
                @endforeach
            </div>
            <div class="px-5 py-3 border-t border-neutral-100">
                {{ $this->expenses->links() }}
            </div>
        @endif
    </div>

    <!-- Add/Edit Modal -->
    @if($showModal)
        <div class="fixed inset-0 z-50 flex items-center justify-center p-4" x-transition>
            <div class="fixed inset-0 bg-black/50" wire:click="closeModal"></div>
            <div class="relative w-full max-w-lg bg-white rounded-2xl shadow-2xl max-h-[90vh] overflow-y-auto">
                <div class="sticky top-0 bg-white border-b border-neutral-200 px-6 py-4 flex items-center justify-between rounded-t-2xl z-10">
                    <h3 class="text-lg font-bold text-neutral-900">{{ $editingId ? 'Modifier' : 'Nouvelle' }} dépense</h3>
                    <button wire:click="closeModal" class="p-1.5 rounded-lg hover:bg-neutral-100 text-neutral-500 transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <form wire:submit="save" class="p-6 space-y-4">
                    <div class="grid grid-cols-2 gap-4">
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-neutral-700 mb-1">Catégorie *</label>
                            <select wire:model="category" class="w-full border-neutral-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 text-sm">
                                <option value="">Choisir...</option>
                                @foreach(\App\Enums\ExpenseCategory::cases() as $cat)
                                    <option value="{{ $cat->value }}">{{ $cat->label() }}</option>
                                @endforeach
                            </select>
                            @error('category') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-neutral-700 mb-1">Description *</label>
                            <input type="text" wire:model="description" class="w-full border-neutral-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 text-sm" placeholder="Ex: Achat poulet marché">
                            @error('description') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-1">Montant (F CFA) *</label>
                            <input type="number" wire:model="amount" min="1" class="w-full border-neutral-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 text-sm" placeholder="15000">
                            @error('amount') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-1">Date *</label>
                            <input type="date" wire:model="expense_date" class="w-full border-neutral-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 text-sm">
                            @error('expense_date') <p class="text-xs text-red-500 mt-1">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-1">Fournisseur</label>
                            <input type="text" wire:model="supplier" class="w-full border-neutral-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 text-sm" placeholder="Nom du fournisseur">
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-neutral-700 mb-1">Référence</label>
                            <input type="text" wire:model="reference" class="w-full border-neutral-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 text-sm" placeholder="N° facture">
                        </div>
                        <div class="col-span-2">
                            <label class="flex items-center gap-2 cursor-pointer">
                                <input type="checkbox" wire:model.live="is_recurring" class="rounded border-neutral-300 text-primary-600 focus:ring-primary-500">
                                <span class="text-sm text-neutral-700">Dépense récurrente</span>
                            </label>
                        </div>
                        @if($is_recurring)
                            <div class="col-span-2">
                                <label class="block text-sm font-medium text-neutral-700 mb-1">Fréquence</label>
                                <select wire:model="recurrence_period" class="w-full border-neutral-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 text-sm">
                                    <option value="">Choisir...</option>
                                    <option value="daily">Quotidien</option>
                                    <option value="weekly">Hebdomadaire</option>
                                    <option value="monthly">Mensuel</option>
                                    <option value="yearly">Annuel</option>
                                </select>
                            </div>
                        @endif
                        <div class="col-span-2">
                            <label class="block text-sm font-medium text-neutral-700 mb-1">Notes</label>
                            <textarea wire:model="notes" rows="2" class="w-full border-neutral-300 rounded-lg focus:ring-primary-500 focus:border-primary-500 text-sm resize-none" placeholder="Détails supplémentaires..."></textarea>
                        </div>
                    </div>
                    <div class="flex gap-3 pt-2">
                        <button type="button" wire:click="closeModal" class="flex-1 px-4 py-2.5 border border-neutral-300 text-neutral-700 rounded-lg font-medium hover:bg-neutral-50 transition text-sm">
                            Annuler
                        </button>
                        <button type="submit" class="flex-1 px-4 py-2.5 bg-primary-600 text-white rounded-lg font-medium hover:bg-primary-700 transition text-sm">
                            {{ $editingId ? 'Enregistrer' : 'Ajouter' }}
                        </button>
                    </div>
                </form>
            </div>
        </div>
    @endif
</div>
