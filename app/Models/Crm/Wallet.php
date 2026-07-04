<?php

namespace App\Models\Crm;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Wallet extends Model
{
    protected $table = 'crm_wallets';

    protected $fillable = [
        'user_id',
        'balance_cents',
        'total_earned_cents',
        'total_withdrawn_cents',
    ];

    protected function casts(): array
    {
        return [
            'balance_cents' => 'integer',
            'total_earned_cents' => 'integer',
            'total_withdrawn_cents' => 'integer',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function commissions(): HasMany
    {
        return $this->hasMany(Commission::class, 'wallet_id');
    }

    public function withdrawals(): HasMany
    {
        return $this->hasMany(Withdrawal::class, 'wallet_id');
    }

    public function credit(int $amountCents): void
    {
        $this->increment('balance_cents', $amountCents);
        $this->increment('total_earned_cents', $amountCents);
    }

    public function debit(int $amountCents): void
    {
        $this->decrement('balance_cents', $amountCents);
        $this->increment('total_withdrawn_cents', $amountCents);
    }

    public function getBalanceAttribute(): float
    {
        return $this->balance_cents / 100;
    }

    public function getBalanceFormattedAttribute(): string
    {
        return number_format($this->balance_cents / 100, 0, ',', ' ') . ' FCFA';
    }

    public function getTotalEarnedFormattedAttribute(): string
    {
        return number_format($this->total_earned_cents / 100, 0, ',', ' ') . ' FCFA';
    }

    public function canWithdraw(int $amountCents): bool
    {
        return $this->balance_cents >= $amountCents
            && $amountCents >= config('crm.withdrawal.min_amount_cents');
    }
}
