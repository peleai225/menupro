<?php

namespace App\Models\Crm;

use App\Enums\Crm\PaymentMethod;
use App\Enums\Crm\WithdrawalStatus;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class Withdrawal extends Model
{
    protected $table = 'crm_withdrawals';

    protected $fillable = [
        'wallet_id',
        'user_id',
        'amount_cents',
        'status',
        'payment_method',
        'payment_reference',
        'processed_by',
        'processed_at',
        'rejection_reason',
    ];

    protected function casts(): array
    {
        return [
            'status' => WithdrawalStatus::class,
            'payment_method' => PaymentMethod::class,
            'amount_cents' => 'integer',
            'processed_at' => 'datetime',
        ];
    }

    public function wallet(): BelongsTo
    {
        return $this->belongsTo(Wallet::class, 'wallet_id');
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function processedByUser(): BelongsTo
    {
        return $this->belongsTo(User::class, 'processed_by');
    }

    public function scopePending($query)
    {
        return $query->where('status', WithdrawalStatus::PENDING);
    }

    public function getAmountFormattedAttribute(): string
    {
        return number_format($this->amount_cents / 100, 0, ',', ' ') . ' FCFA';
    }

    public function approve(int $processedBy): void
    {
        $this->update([
            'status' => WithdrawalStatus::APPROVED,
            'processed_by' => $processedBy,
        ]);
    }

    public function markPaid(string $reference): void
    {
        DB::transaction(function () use ($reference) {
            $withdrawal = static::lockForUpdate()->findOrFail($this->id);

            if ($withdrawal->status === WithdrawalStatus::PAID) return;

            $withdrawal->update([
                'status' => WithdrawalStatus::PAID,
                'payment_reference' => $reference,
                'processed_at' => now(),
            ]);

            Wallet::lockForUpdate()->find($withdrawal->wallet_id)
                ?->increment('total_withdrawn_cents', $withdrawal->amount_cents);
        });

        $this->refresh();
    }

    public function reject(int $processedBy, string $reason): void
    {
        $this->update([
            'status' => WithdrawalStatus::REJECTED,
            'processed_by' => $processedBy,
            'processed_at' => now(),
            'rejection_reason' => $reason,
        ]);

        // Rembourser le solde (déjà débité à la demande)
        $this->wallet->increment('balance_cents', $this->amount_cents);
    }
}
