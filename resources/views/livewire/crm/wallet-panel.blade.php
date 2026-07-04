<div class="space-y-6" wire:poll.60s>
    {{-- Balance card --}}
    <div class="relative overflow-hidden rounded-2xl bg-gradient-to-br from-gray-900 via-gray-900 to-gray-800 border border-gray-800 p-6">
        <div class="absolute top-0 right-0 w-40 h-40 bg-orange-500/5 rounded-full -translate-y-1/2 translate-x-1/2"></div>
        <div class="absolute bottom-0 left-0 w-32 h-32 bg-orange-500/5 rounded-full translate-y-1/2 -translate-x-1/2"></div>

        <p class="text-sm text-gray-400 mb-1">Solde disponible</p>
        <p class="text-3xl font-bold text-white mb-4 tabular-nums">
            {{ $this->wallet->balance_formatted }}
        </p>

        <div class="flex items-center gap-6">
            <div>
                <p class="text-[10px] uppercase tracking-wider text-gray-500">Total gagné</p>
                <p class="text-sm font-semibold text-emerald-400">{{ $this->wallet->total_earned_formatted }}</p>
            </div>
            <div>
                <p class="text-[10px] uppercase tracking-wider text-gray-500">Total retiré</p>
                <p class="text-sm font-semibold text-gray-400">{{ number_format($this->wallet->total_withdrawn_cents / 100, 0, ',', ' ') }} FCFA</p>
            </div>
        </div>

        <button wire:click="$set('showWithdrawModal', true)"
                class="mt-5 w-full py-3 bg-orange-500 hover:bg-orange-600 text-white text-sm font-semibold rounded-xl transition shadow-lg shadow-orange-500/20 active:scale-[0.98]">
            Demander un retrait
        </button>
    </div>

    {{-- Pending withdrawals --}}
    @if($this->pendingWithdrawals->isNotEmpty())
    <div class="rounded-xl border border-amber-500/20 bg-amber-500/5 p-4">
        <p class="text-sm font-medium text-amber-400 mb-2">Retraits en attente</p>
        @foreach($this->pendingWithdrawals as $w)
        <div class="flex items-center justify-between py-2 border-t border-amber-500/10 first:border-0">
            <span class="text-sm text-gray-300">{{ $w->amount_formatted }}</span>
            <span class="text-xs text-amber-500/70">{{ $w->created_at->diffForHumans() }}</span>
        </div>
        @endforeach
    </div>
    @endif

    {{-- Recent commissions --}}
    <div>
        <h3 class="text-sm font-semibold text-gray-300 mb-3">Dernières commissions</h3>
        <div class="space-y-2">
            @forelse($this->recentCommissions as $commission)
            <div class="flex items-center gap-3 p-3 rounded-xl bg-gray-900/50 border border-gray-800/50 hover:border-gray-700 transition"
                 x-data="{ show: false }" x-init="setTimeout(() => show = true, {{ $loop->index * 50 }})" x-show="show"
                 x-transition:enter="transition ease-out duration-300"
                 x-transition:enter-start="opacity-0 translate-y-2"
                 x-transition:enter-end="opacity-100 translate-y-0">
                <span class="w-8 h-8 rounded-lg bg-emerald-500/10 flex items-center justify-center">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v12m-3-2.818l.879.659c1.171.879 3.07.879 4.242 0 1.172-.879 1.172-2.303 0-3.182C13.536 12.219 12.768 12 12 12c-.725 0-1.45-.22-2.003-.659-1.106-.879-1.106-2.303 0-3.182s2.9-.879 4.006 0l.415.33M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </span>
                <div class="flex-1 min-w-0">
                    <p class="text-sm text-gray-200 truncate">{{ $commission->description }}</p>
                    <p class="text-xs text-gray-500">{{ $commission->type->label() }} · {{ $commission->created_at->diffForHumans() }}</p>
                </div>
                <span class="text-sm font-semibold text-emerald-400 shrink-0">+{{ $commission->amount_formatted }}</span>
            </div>
            @empty
            <div class="text-center py-8">
                <p class="text-sm text-gray-500">Aucune commission pour le moment</p>
                <p class="text-xs text-gray-600 mt-1">Convertissez des leads pour gagner des commissions</p>
            </div>
            @endforelse
        </div>
    </div>

    {{-- Withdraw modal --}}
    @if($showWithdrawModal)
    <div class="fixed inset-0 z-50 flex items-end sm:items-center justify-center p-4">
        <div class="absolute inset-0 bg-black/60 backdrop-blur-sm" wire:click="$set('showWithdrawModal', false)"></div>
        <div class="relative w-full max-w-sm bg-gray-900 rounded-2xl border border-gray-800 shadow-2xl p-6"
             x-transition:enter="transition ease-out duration-200"
             x-transition:enter-start="opacity-0 translate-y-4"
             x-transition:enter-end="opacity-100 translate-y-0">
            <h3 class="text-lg font-semibold text-white mb-4">Demande de retrait</h3>

            <div class="space-y-4">
                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Montant (FCFA)</label>
                    <input type="number" wire:model="withdrawAmount" min="5000" step="1000"
                           class="w-full bg-gray-800 border border-gray-700 rounded-xl px-4 py-3 text-lg font-semibold text-white text-center focus:border-orange-500 focus:ring-1 focus:ring-orange-500/30"
                           placeholder="10 000">
                    @error('withdrawAmount') <p class="text-xs text-red-400 mt-1">{{ $message }}</p> @enderror
                    <p class="text-xs text-gray-500 mt-1">Min: 5 000 FCFA · Solde: {{ $this->wallet->balance_formatted }}</p>
                </div>

                <div>
                    <label class="block text-xs font-medium text-gray-400 mb-1.5">Mode de paiement</label>
                    <div class="grid grid-cols-2 gap-2">
                        @foreach(['wave' => 'Wave', 'orange_money' => 'Orange Money', 'mtn' => 'MTN MoMo', 'moov' => 'Moov'] as $key => $label)
                        <label class="flex items-center gap-2 p-3 rounded-xl border cursor-pointer transition
                                      {{ $paymentMethod === $key ? 'border-orange-500 bg-orange-500/5' : 'border-gray-700 hover:border-gray-600' }}">
                            <input type="radio" wire:model="paymentMethod" value="{{ $key }}" class="hidden">
                            <span class="text-sm {{ $paymentMethod === $key ? 'text-orange-400 font-medium' : 'text-gray-400' }}">{{ $label }}</span>
                        </label>
                        @endforeach
                    </div>
                </div>

                <button wire:click="requestWithdrawal"
                        class="w-full py-3 bg-orange-500 hover:bg-orange-600 text-white font-semibold rounded-xl transition shadow-lg shadow-orange-500/20 active:scale-[0.98]"
                        wire:loading.attr="disabled">
                    <span wire:loading.remove>Confirmer le retrait</span>
                    <span wire:loading class="flex items-center justify-center gap-2">
                        <svg class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path></svg>
                        Traitement...
                    </span>
                </button>
            </div>
        </div>
    </div>
    @endif
</div>
