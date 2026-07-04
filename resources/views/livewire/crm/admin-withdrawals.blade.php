<div class="space-y-6" x-data="{ selectedId: null }">
    {{-- Stats cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-5 gap-3 lg:gap-4">
        <button wire:click="setFilter('all')"
                class="relative overflow-hidden rounded-xl lg:rounded-2xl border p-4 lg:p-5 text-left transition-all hover:scale-[1.02] active:scale-[0.98]
                       {{ $statusFilter === 'all' ? 'bg-gray-800 border-gray-700' : 'bg-gray-900 border-gray-800 hover:border-gray-700' }}">
            <p class="text-xs lg:text-sm text-gray-400 mb-1">Total</p>
            <p class="text-2xl lg:text-3xl font-bold text-white tabular-nums">
                {{ $this->stats['pending'] + $this->stats['approved'] + $this->stats['paid'] + $this->stats['rejected'] }}
            </p>
            @if($statusFilter === 'all')
            <div class="absolute bottom-0 right-0 w-20 h-20 bg-gray-700/20 rounded-full translate-x-8 translate-y-8"></div>
            @endif
        </button>

        <button wire:click="setFilter('pending')"
                class="relative overflow-hidden rounded-xl lg:rounded-2xl border p-4 lg:p-5 text-left transition-all hover:scale-[1.02] active:scale-[0.98]
                       {{ $statusFilter === 'pending' ? 'bg-amber-500/10 border-amber-500/30' : 'bg-gray-900 border-gray-800 hover:border-gray-700' }}">
            <p class="text-xs lg:text-sm {{ $statusFilter === 'pending' ? 'text-amber-400' : 'text-gray-400' }} mb-1">En attente</p>
            <p class="text-2xl lg:text-3xl font-bold {{ $statusFilter === 'pending' ? 'text-amber-400' : 'text-white' }} tabular-nums">
                {{ $this->stats['pending'] }}
            </p>
            <p class="text-[10px] lg:text-xs text-gray-500 mt-1">{{ number_format($this->stats['pending_amount_cents'] / 100, 0, ',', ' ') }} FCFA</p>
            @if($statusFilter === 'pending')
            <div class="absolute bottom-0 right-0 w-20 h-20 bg-amber-500/20 rounded-full translate-x-8 translate-y-8"></div>
            @endif
        </button>

        <button wire:click="setFilter('approved')"
                class="relative overflow-hidden rounded-xl lg:rounded-2xl border p-4 lg:p-5 text-left transition-all hover:scale-[1.02] active:scale-[0.98]
                       {{ $statusFilter === 'approved' ? 'bg-sky-500/10 border-sky-500/30' : 'bg-gray-900 border-gray-800 hover:border-gray-700' }}">
            <p class="text-xs lg:text-sm {{ $statusFilter === 'approved' ? 'text-sky-400' : 'text-gray-400' }} mb-1">Approuvés</p>
            <p class="text-2xl lg:text-3xl font-bold {{ $statusFilter === 'approved' ? 'text-sky-400' : 'text-white' }} tabular-nums">
                {{ $this->stats['approved'] }}
            </p>
            @if($statusFilter === 'approved')
            <div class="absolute bottom-0 right-0 w-20 h-20 bg-sky-500/20 rounded-full translate-x-8 translate-y-8"></div>
            @endif
        </button>

        <button wire:click="setFilter('paid')"
                class="relative overflow-hidden rounded-xl lg:rounded-2xl border p-4 lg:p-5 text-left transition-all hover:scale-[1.02] active:scale-[0.98]
                       {{ $statusFilter === 'paid' ? 'bg-emerald-500/10 border-emerald-500/30' : 'bg-gray-900 border-gray-800 hover:border-gray-700' }}">
            <p class="text-xs lg:text-sm {{ $statusFilter === 'paid' ? 'text-emerald-400' : 'text-gray-400' }} mb-1">Payés</p>
            <p class="text-2xl lg:text-3xl font-bold {{ $statusFilter === 'paid' ? 'text-emerald-400' : 'text-white' }} tabular-nums">
                {{ $this->stats['paid'] }}
            </p>
            @if($statusFilter === 'paid')
            <div class="absolute bottom-0 right-0 w-20 h-20 bg-emerald-500/20 rounded-full translate-x-8 translate-y-8"></div>
            @endif
        </button>

        <button wire:click="setFilter('rejected')"
                class="relative overflow-hidden rounded-xl lg:rounded-2xl border p-4 lg:p-5 text-left transition-all hover:scale-[1.02] active:scale-[0.98]
                       {{ $statusFilter === 'rejected' ? 'bg-red-500/10 border-red-500/30' : 'bg-gray-900 border-gray-800 hover:border-gray-700' }}">
            <p class="text-xs lg:text-sm {{ $statusFilter === 'rejected' ? 'text-red-400' : 'text-gray-400' }} mb-1">Rejetés</p>
            <p class="text-2xl lg:text-3xl font-bold {{ $statusFilter === 'rejected' ? 'text-red-400' : 'text-white' }} tabular-nums">
                {{ $this->stats['rejected'] }}
            </p>
            @if($statusFilter === 'rejected')
            <div class="absolute bottom-0 right-0 w-20 h-20 bg-red-500/20 rounded-full translate-x-8 translate-y-8"></div>
            @endif
        </button>
    </div>

    {{-- Loading overlay --}}
    <div wire:loading.delay class="fixed inset-0 z-40 bg-black/30 backdrop-blur-sm flex items-center justify-center">
        <div class="bg-gray-900 border border-gray-800 rounded-2xl p-6 shadow-2xl">
            <svg class="w-8 h-8 text-orange-500 animate-spin mx-auto" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <p class="text-sm text-gray-400 mt-3">Traitement...</p>
        </div>
    </div>

    {{-- Withdrawals list --}}
    <div class="rounded-2xl bg-gray-900 border border-gray-800/50 overflow-hidden">
        {{-- Desktop table --}}
        <div class="hidden lg:block overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-800/50 border-b border-gray-800">
                    <tr>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Agent</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Montant</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Méthode</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Statut</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Date</th>
                        <th class="px-6 py-4 text-left text-xs font-semibold text-gray-400 uppercase tracking-wider">Traité par</th>
                        <th class="px-6 py-4 text-right text-xs font-semibold text-gray-400 uppercase tracking-wider">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-800/50">
                    @forelse($this->withdrawals as $withdrawal)
                    <tr class="hover:bg-gray-800/30 transition"
                        x-data="{ show: false }"
                        x-init="setTimeout(() => show = true, {{ $loop->index * 30 }})"
                        x-show="show"
                        x-transition:enter="transition ease-out duration-300"
                        x-transition:enter-start="opacity-0 translate-y-2"
                        x-transition:enter-end="opacity-100 translate-y-0">
                        <td class="px-6 py-4">
                            <div class="flex items-center gap-3">
                                <img src="{{ $withdrawal->user->avatar_url }}" class="w-9 h-9 rounded-lg object-cover ring-2 ring-gray-800" alt="">
                                <div>
                                    <p class="text-sm font-medium text-gray-200">{{ $withdrawal->user->name }}</p>
                                    <p class="text-xs text-gray-500">{{ $withdrawal->user->email }}</p>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm font-semibold text-white tabular-nums">{{ $withdrawal->amount_formatted }}</p>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-gray-300">{{ $withdrawal->payment_method->label() }}</p>
                        </td>
                        <td class="px-6 py-4">
                            @php
                                $statusColors = [
                                    'pending' => ['bg' => 'bg-amber-500/10', 'text' => 'text-amber-400', 'border' => 'border-amber-500/30'],
                                    'approved' => ['bg' => 'bg-sky-500/10', 'text' => 'text-sky-400', 'border' => 'border-sky-500/30'],
                                    'paid' => ['bg' => 'bg-emerald-500/10', 'text' => 'text-emerald-400', 'border' => 'border-emerald-500/30'],
                                    'rejected' => ['bg' => 'bg-red-500/10', 'text' => 'text-red-400', 'border' => 'border-red-500/30'],
                                ];
                                $colors = $statusColors[$withdrawal->status->value] ?? ['bg' => 'bg-gray-500/10', 'text' => 'text-gray-400', 'border' => 'border-gray-500/30'];
                            @endphp
                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full border text-xs font-medium {{ $colors['bg'] }} {{ $colors['text'] }} {{ $colors['border'] }}">
                                <span class="w-1.5 h-1.5 rounded-full {{ str_replace('/10', '', $colors['bg']) }}"></span>
                                {{ $withdrawal->status->label() }}
                            </span>
                        </td>
                        <td class="px-6 py-4">
                            <p class="text-sm text-gray-300">{{ $withdrawal->created_at->format('d/m/Y H:i') }}</p>
                            @if($withdrawal->processed_at)
                            <p class="text-xs text-gray-500">Traité: {{ $withdrawal->processed_at->format('d/m/Y') }}</p>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            @if($withdrawal->processedByUser)
                            <p class="text-sm text-gray-300">{{ $withdrawal->processedByUser->name }}</p>
                            @else
                            <p class="text-xs text-gray-500">-</p>
                            @endif
                        </td>
                        <td class="px-6 py-4">
                            <div class="flex items-center justify-end gap-2">
                                @if($withdrawal->status === \App\Enums\Crm\WithdrawalStatus::PENDING)
                                <button wire:click="approve({{ $withdrawal->id }})"
                                        class="p-2 rounded-lg bg-sky-500/10 text-sky-400 hover:bg-sky-500/20 transition"
                                        title="Approuver">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                </button>
                                <button wire:click="openRejectModal({{ $withdrawal->id }})"
                                        class="p-2 rounded-lg bg-red-500/10 text-red-400 hover:bg-red-500/20 transition"
                                        title="Rejeter">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                                @elseif($withdrawal->status === \App\Enums\Crm\WithdrawalStatus::APPROVED)
                                <button wire:click="openPaymentModal({{ $withdrawal->id }})"
                                        class="px-3 py-1.5 rounded-lg bg-emerald-500/10 text-emerald-400 hover:bg-emerald-500/20 transition text-xs font-medium"
                                        title="Marquer comme payé">
                                    Marquer payé
                                </button>
                                <button wire:click="openRejectModal({{ $withdrawal->id }})"
                                        class="p-2 rounded-lg bg-red-500/10 text-red-400 hover:bg-red-500/20 transition"
                                        title="Rejeter">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                </button>
                                @elseif($withdrawal->status === \App\Enums\Crm\WithdrawalStatus::PAID)
                                <span class="text-xs text-gray-500">Réf: {{ $withdrawal->payment_reference }}</span>
                                @elseif($withdrawal->status === \App\Enums\Crm\WithdrawalStatus::REJECTED)
                                <span class="text-xs text-red-400/70" title="{{ $withdrawal->rejection_reason }}">Rejeté</span>
                                @endif
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="px-6 py-12 text-center">
                            <svg class="w-12 h-12 text-gray-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                            <p class="text-sm text-gray-400">Aucun retrait trouvé</p>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Mobile cards --}}
        <div class="lg:hidden divide-y divide-gray-800/50">
            @forelse($this->withdrawals as $withdrawal)
            <div class="p-4 hover:bg-gray-800/30 transition"
                 x-data="{ show: false }"
                 x-init="setTimeout(() => show = true, {{ $loop->index * 30 }})"
                 x-show="show"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0">
                <div class="flex items-start gap-3 mb-3">
                    <img src="{{ $withdrawal->user->avatar_url }}" class="w-10 h-10 rounded-lg object-cover ring-2 ring-gray-800" alt="">
                    <div class="flex-1 min-w-0">
                        <p class="text-sm font-medium text-gray-200 truncate">{{ $withdrawal->user->name }}</p>
                        <p class="text-xs text-gray-500 truncate">{{ $withdrawal->user->email }}</p>
                    </div>
                    @php
                        $statusColors = [
                            'pending' => ['bg' => 'bg-amber-500/10', 'text' => 'text-amber-400', 'border' => 'border-amber-500/30'],
                            'approved' => ['bg' => 'bg-sky-500/10', 'text' => 'text-sky-400', 'border' => 'border-sky-500/30'],
                            'paid' => ['bg' => 'bg-emerald-500/10', 'text' => 'text-emerald-400', 'border' => 'border-emerald-500/30'],
                            'rejected' => ['bg' => 'bg-red-500/10', 'text' => 'text-red-400', 'border' => 'border-red-500/30'],
                        ];
                        $colors = $statusColors[$withdrawal->status->value] ?? ['bg' => 'bg-gray-500/10', 'text' => 'text-gray-400', 'border' => 'border-gray-500/30'];
                    @endphp
                    <span class="inline-flex items-center gap-1.5 px-2 py-1 rounded-full border text-[10px] font-medium {{ $colors['bg'] }} {{ $colors['text'] }} {{ $colors['border'] }}">
                        <span class="w-1 h-1 rounded-full {{ str_replace('/10', '', $colors['bg']) }}"></span>
                        {{ $withdrawal->status->label() }}
                    </span>
                </div>

                <div class="space-y-2 mb-3">
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500">Montant</span>
                        <span class="text-sm font-semibold text-white tabular-nums">{{ $withdrawal->amount_formatted }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500">Méthode</span>
                        <span class="text-xs text-gray-300">{{ $withdrawal->payment_method->label() }}</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500">Date</span>
                        <span class="text-xs text-gray-300">{{ $withdrawal->created_at->format('d/m/Y H:i') }}</span>
                    </div>
                    @if($withdrawal->processedByUser)
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500">Traité par</span>
                        <span class="text-xs text-gray-300">{{ $withdrawal->processedByUser->name }}</span>
                    </div>
                    @endif
                    @if($withdrawal->status === \App\Enums\Crm\WithdrawalStatus::PAID && $withdrawal->payment_reference)
                    <div class="flex items-center justify-between">
                        <span class="text-xs text-gray-500">Référence</span>
                        <span class="text-xs text-gray-300">{{ $withdrawal->payment_reference }}</span>
                    </div>
                    @endif
                </div>

                @if($withdrawal->status === \App\Enums\Crm\WithdrawalStatus::PENDING)
                <div class="flex gap-2">
                    <button wire:click="approve({{ $withdrawal->id }})"
                            class="flex-1 py-2 rounded-lg bg-sky-500/10 text-sky-400 hover:bg-sky-500/20 transition text-xs font-medium">
                        Approuver
                    </button>
                    <button wire:click="openRejectModal({{ $withdrawal->id }})"
                            class="flex-1 py-2 rounded-lg bg-red-500/10 text-red-400 hover:bg-red-500/20 transition text-xs font-medium">
                        Rejeter
                    </button>
                </div>
                @elseif($withdrawal->status === \App\Enums\Crm\WithdrawalStatus::APPROVED)
                <div class="flex gap-2">
                    <button wire:click="openPaymentModal({{ $withdrawal->id }})"
                            class="flex-1 py-2 rounded-lg bg-emerald-500/10 text-emerald-400 hover:bg-emerald-500/20 transition text-xs font-medium">
                        Marquer comme payé
                    </button>
                    <button wire:click="openRejectModal({{ $withdrawal->id }})"
                            class="px-4 py-2 rounded-lg bg-red-500/10 text-red-400 hover:bg-red-500/20 transition text-xs font-medium">
                        Rejeter
                    </button>
                </div>
                @endif
            </div>
            @empty
            <div class="p-12 text-center">
                <svg class="w-12 h-12 text-gray-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/></svg>
                <p class="text-sm text-gray-400">Aucun retrait trouvé</p>
            </div>
            @endforelse
        </div>
    </div>

    {{-- Reject modal --}}
    @if($showRejectModal)
    <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4" x-data x-trap.noscroll="true">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" wire:click="$set('showRejectModal', false)"></div>
        <div class="relative w-full max-w-md bg-gray-900 rounded-2xl border border-gray-800 shadow-2xl p-6"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100">
            <h3 class="text-lg font-semibold text-white mb-4">Rejeter le retrait</h3>

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Motif du rejet</label>
                    <textarea wire:model="rejectionReason"
                              rows="4"
                              class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-sm text-white placeholder:text-gray-500 focus:border-orange-500 focus:ring-1 focus:ring-orange-500/30 resize-none"
                              placeholder="Ex: Document incomplet, informations invalides..."></textarea>
                    @error('rejectionReason') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                </div>

                <div class="flex gap-3">
                    <button wire:click="$set('showRejectModal', false)"
                            class="flex-1 py-2.5 bg-gray-800 hover:bg-gray-700 text-gray-300 rounded-xl transition text-sm font-medium">
                        Annuler
                    </button>
                    <button wire:click="reject"
                            class="flex-1 py-2.5 bg-red-500 hover:bg-red-600 text-white rounded-xl transition text-sm font-medium shadow-lg shadow-red-500/20 active:scale-[0.98]"
                            wire:loading.attr="disabled">
                        <span wire:loading.remove>Confirmer le rejet</span>
                        <span wire:loading class="flex items-center justify-center gap-2">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            Traitement...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Payment modal --}}
    @if($showPaymentModal)
    <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4" x-data x-trap.noscroll="true">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" wire:click="$set('showPaymentModal', false)"></div>
        <div class="relative w-full max-w-md bg-gray-900 rounded-2xl border border-gray-800 shadow-2xl p-6"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-4 sm:scale-95"
             x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100">
            <h3 class="text-lg font-semibold text-white mb-4">Marquer comme payé</h3>

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Référence de paiement</label>
                    <input type="text" wire:model="paymentReference"
                           class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-sm text-white placeholder:text-gray-500 focus:border-orange-500 focus:ring-1 focus:ring-orange-500/30"
                           placeholder="Ex: TXN20260704123456">
                    @error('paymentReference') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                    <p class="text-xs text-gray-500 mt-1">Numéro de transaction ou référence du paiement effectué</p>
                </div>

                <div class="flex gap-3">
                    <button wire:click="$set('showPaymentModal', false)"
                            class="flex-1 py-2.5 bg-gray-800 hover:bg-gray-700 text-gray-300 rounded-xl transition text-sm font-medium">
                        Annuler
                    </button>
                    <button wire:click="markPaid"
                            class="flex-1 py-2.5 bg-emerald-500 hover:bg-emerald-600 text-white rounded-xl transition text-sm font-medium shadow-lg shadow-emerald-500/20 active:scale-[0.98]"
                            wire:loading.attr="disabled">
                        <span wire:loading.remove>Confirmer le paiement</span>
                        <span wire:loading class="flex items-center justify-center gap-2">
                            <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                            Traitement...
                        </span>
                    </button>
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

@script
<script>
    $wire.on('toast', (event) => {
        const { message, type = 'info' } = event;
        const toast = document.createElement('div');
        toast.className = `fixed top-4 right-4 z-[100] max-w-xs px-4 py-3 rounded-2xl shadow-2xl text-sm font-medium transition-all duration-500 transform translate-y-[-8px] opacity-0 ${
            type === 'success' ? 'bg-emerald-500/95 text-white backdrop-blur' :
            type === 'error' ? 'bg-red-500/95 text-white backdrop-blur' :
            'bg-gray-800/95 text-gray-100 backdrop-blur'
        }`;
        toast.textContent = message;
        document.body.appendChild(toast);
        requestAnimationFrame(() => {
            toast.classList.remove('translate-y-[-8px]', 'opacity-0');
            toast.classList.add('translate-y-0', 'opacity-100');
        });
        setTimeout(() => {
            toast.classList.add('translate-y-[-8px]', 'opacity-0');
            setTimeout(() => toast.remove(), 500);
        }, 4000);
    });
</script>
@endscript
