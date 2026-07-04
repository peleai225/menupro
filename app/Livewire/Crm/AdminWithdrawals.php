<?php

namespace App\Livewire\Crm;

use App\Enums\Crm\WithdrawalStatus;
use App\Models\Crm\Withdrawal;
use Illuminate\Support\Facades\DB;
use Livewire\Attributes\Computed;
use Livewire\Attributes\On;
use Livewire\Component;

class AdminWithdrawals extends Component
{
    public string $statusFilter = 'all';

    public bool $showRejectModal = false;
    public ?int $rejectWithdrawalId = null;
    public string $rejectionReason = '';

    public bool $showPaymentModal = false;
    public ?int $paymentWithdrawalId = null;
    public string $paymentReference = '';

    public function mount(): void
    {
        // Verify super_admin (belt and suspenders - middleware already checks)
        if (auth()->user()->role->value !== 'super_admin') {
            abort(403);
        }
    }

    #[On('withdrawal-processed')]
    public function refresh(): void
    {
        unset($this->withdrawals);
        unset($this->stats);
    }

    public function setFilter(string $status): void
    {
        $this->statusFilter = $status;
        unset($this->withdrawals);
    }

    #[Computed]
    public function stats(): array
    {
        return [
            'pending' => Withdrawal::where('status', WithdrawalStatus::PENDING)->count(),
            'approved' => Withdrawal::where('status', WithdrawalStatus::APPROVED)->count(),
            'paid' => Withdrawal::where('status', WithdrawalStatus::PAID)->count(),
            'rejected' => Withdrawal::where('status', WithdrawalStatus::REJECTED)->count(),
            'pending_amount_cents' => Withdrawal::where('status', WithdrawalStatus::PENDING)->sum('amount_cents'),
        ];
    }

    #[Computed]
    public function withdrawals()
    {
        $query = Withdrawal::with(['user', 'processedByUser'])
            ->latest();

        if ($this->statusFilter !== 'all') {
            $status = match ($this->statusFilter) {
                'pending' => WithdrawalStatus::PENDING,
                'approved' => WithdrawalStatus::APPROVED,
                'paid' => WithdrawalStatus::PAID,
                'rejected' => WithdrawalStatus::REJECTED,
                default => null,
            };

            if ($status) {
                $query->where('status', $status);
            }
        }

        return $query->limit(100)->get();
    }

    public function approve(int $withdrawalId): void
    {
        DB::transaction(function () use ($withdrawalId) {
            $withdrawal = Withdrawal::where('id', $withdrawalId)->lockForUpdate()->first();

            if (!$withdrawal) {
                $this->dispatch('toast', message: 'Retrait introuvable', type: 'error');
                return;
            }

            if ($withdrawal->status !== WithdrawalStatus::PENDING) {
                $this->dispatch('toast', message: 'Seuls les retraits en attente peuvent être approuvés', type: 'error');
                return;
            }

            $withdrawal->approve(auth()->id());

            $this->dispatch('toast', message: 'Retrait approuvé avec succès', type: 'success');
            $this->dispatch('withdrawal-processed');

            // Broadcast to user
            if (class_exists('\App\Events\Crm\WithdrawalApproved')) {
                event(new \App\Events\Crm\WithdrawalApproved($withdrawal));
            }
        });
    }

    public function openRejectModal(int $withdrawalId): void
    {
        $this->rejectWithdrawalId = $withdrawalId;
        $this->rejectionReason = '';
        $this->showRejectModal = true;
    }

    public function reject(): void
    {
        $this->validate([
            'rejectionReason' => 'required|min:5|max:500',
        ], [
            'rejectionReason.required' => 'Le motif du rejet est obligatoire',
            'rejectionReason.min' => 'Le motif doit contenir au moins 5 caractères',
            'rejectionReason.max' => 'Le motif ne peut pas dépasser 500 caractères',
        ]);

        DB::transaction(function () {
            $withdrawal = Withdrawal::where('id', $this->rejectWithdrawalId)->lockForUpdate()->first();

            if (!$withdrawal) {
                $this->dispatch('toast', message: 'Retrait introuvable', type: 'error');
                return;
            }

            if (!in_array($withdrawal->status, [WithdrawalStatus::PENDING, WithdrawalStatus::APPROVED])) {
                $this->dispatch('toast', message: 'Ce retrait ne peut pas être rejeté', type: 'error');
                return;
            }

            $withdrawal->reject(auth()->id(), $this->rejectionReason);

            $this->dispatch('toast', message: 'Retrait rejeté et fonds remboursés', type: 'success');
            $this->dispatch('withdrawal-processed');

            // Broadcast to user
            if (class_exists('\App\Events\Crm\WithdrawalRejected')) {
                event(new \App\Events\Crm\WithdrawalRejected($withdrawal));
            }
        });

        $this->showRejectModal = false;
        $this->rejectWithdrawalId = null;
        $this->rejectionReason = '';
    }

    public function openPaymentModal(int $withdrawalId): void
    {
        $withdrawal = Withdrawal::find($withdrawalId);

        if (!$withdrawal || $withdrawal->status !== WithdrawalStatus::APPROVED) {
            $this->dispatch('toast', message: 'Seuls les retraits approuvés peuvent être marqués comme payés', type: 'error');
            return;
        }

        $this->paymentWithdrawalId = $withdrawalId;
        $this->paymentReference = '';
        $this->showPaymentModal = true;
    }

    public function markPaid(): void
    {
        $this->validate([
            'paymentReference' => 'required|min:3|max:100',
        ], [
            'paymentReference.required' => 'La référence de paiement est obligatoire',
            'paymentReference.min' => 'La référence doit contenir au moins 3 caractères',
            'paymentReference.max' => 'La référence ne peut pas dépasser 100 caractères',
        ]);

        DB::transaction(function () {
            $withdrawal = Withdrawal::where('id', $this->paymentWithdrawalId)->lockForUpdate()->first();

            if (!$withdrawal) {
                $this->dispatch('toast', message: 'Retrait introuvable', type: 'error');
                return;
            }

            if ($withdrawal->status !== WithdrawalStatus::APPROVED) {
                $this->dispatch('toast', message: 'Seuls les retraits approuvés peuvent être marqués comme payés', type: 'error');
                return;
            }

            $withdrawal->markPaid($this->paymentReference);

            $this->dispatch('toast', message: 'Retrait marqué comme payé', type: 'success');
            $this->dispatch('withdrawal-processed');

            // Broadcast to user
            if (class_exists('\App\Events\Crm\WithdrawalPaid')) {
                event(new \App\Events\Crm\WithdrawalPaid($withdrawal));
            }
        });

        $this->showPaymentModal = false;
        $this->paymentWithdrawalId = null;
        $this->paymentReference = '';
    }

    public function render()
    {
        return view('livewire.crm.admin-withdrawals');
    }
}
