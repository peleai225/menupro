<?php

namespace App\Models\Crm;

use App\Enums\Crm\CommissionStatus;
use App\Enums\Crm\CommissionType;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class Commission extends Model
{
    protected $table = 'crm_commissions';

    protected $fillable = [
        'wallet_id',
        'user_id',
        'type',
        'status',
        'amount_cents',
        'source_type',
        'source_id',
        'description',
        'metadata',
        'validated_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => CommissionType::class,
            'status' => CommissionStatus::class,
            'amount_cents' => 'integer',
            'metadata' => 'array',
            'validated_at' => 'datetime',
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

    public function source(): MorphTo
    {
        return $this->morphTo();
    }

    public function scopePending($query)
    {
        return $query->where('status', CommissionStatus::PENDING);
    }

    public function scopeValidated($query)
    {
        return $query->where('status', CommissionStatus::VALIDATED);
    }

    public function getAmountFormattedAttribute(): string
    {
        return number_format($this->amount_cents / 100, 0, ',', ' ') . ' FCFA';
    }

    public function validate(): void
    {
        $this->update([
            'status' => CommissionStatus::VALIDATED,
            'validated_at' => now(),
        ]);

        $this->wallet->credit($this->amount_cents);
    }
}
