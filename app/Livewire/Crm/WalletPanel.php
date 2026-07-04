<?php

namespace App\Livewire\Crm;

use App\Enums\Crm\PaymentMethod;
use App\Enums\Crm\WithdrawalStatus;
use App\Models\Crm\Commission;
use App\Models\Crm\Wallet;
use App\Models\Crm\Withdrawal;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class WalletPanel extends Component
{
    public int $withdrawAmount = 0;
    public string $paymentMethod = 'wave';
    public bool $showWithdrawModal = false;

    #[On('echo:crm.user.{userId},commission.credited')]
    public function refreshWallet(): void
    {
        unset($this->wallet);
    }

    #[Computed]
    public function wallet(): ?Wallet
    {
        return Wallet::firstOrCreate(
            ['user_id' => auth()->id()],
            ['balance_cents' => 0, 'total_earned_cents' => 0, 'total_withdrawn_cents' => 0]
        );
    }

    #[Computed]
    public function recentCommissions()
    {
        return Commission::where('user_id', auth()->id())
            ->latest()
            ->limit(10)
            ->get();
    }

    #[Computed]
    public function pendingWithdrawals()
    {
        return Withdrawal::where('user_id', auth()->id())
            ->pending()
            ->get();
    }

    public function getUserIdProperty(): int
    {
        return auth()->id();
    }

    public function requestWithdrawal(): void
    {
        $amountCents = $this->withdrawAmount * 100;

        if (!$this->wallet->canWithdraw($amountCents)) {
            $this->addError('withdrawAmount', 'Solde insuffisant ou montant minimum non atteint.');
            return;
        }

        $weeklyCount = Withdrawal::where('user_id', auth()->id())
            ->where('created_at', '>=', now()->subWeek())
            ->count();

        if ($weeklyCount >= config('crm.withdrawal.max_per_week')) {
            $this->addError('withdrawAmount', 'Limite de retraits hebdomadaires atteinte.');
            return;
        }

        Withdrawal::create([
            'wallet_id' => $this->wallet->id,
            'user_id' => auth()->id(),
            'amount_cents' => $amountCents,
            'status' => WithdrawalStatus::PENDING,
            'payment_method' => $this->paymentMethod,
        ]);

        $this->showWithdrawModal = false;
        $this->withdrawAmount = 0;
        $this->dispatch('withdrawal-requested');
    }

    public function render()
    {
        return view('livewire.crm.wallet-panel');
    }
}
