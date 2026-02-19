<?php

namespace App\Models;

use App\Enums\CommissionTransactionStatus;
use App\Enums\CommissionTransactionType;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CommandoCommissionTransaction extends Model
{
    protected $table = 'commando_commission_transactions';

    protected $fillable = [
        'commando_agent_id',
        'type',
        'status',
        'amount_cents',
        'description',
        'meta',
        'processed_at',
    ];

    protected function casts(): array
    {
        return [
            'type' => CommissionTransactionType::class,
            'status' => CommissionTransactionStatus::class,
            'processed_at' => 'datetime',
            'meta' => 'array',
        ];
    }

    public function commandoAgent(): BelongsTo
    {
        return $this->belongsTo(CommandoAgent::class);
    }

    public function getAmountAttribute(): float
    {
        return $this->amount_cents / 100;
    }
}
