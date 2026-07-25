<?php

namespace App\Models;

use App\Enums\JekoSubMerchantStatus;
use App\Enums\MobileMoneyOperator;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Crypt;
use Illuminate\Support\Facades\Log;

class JekoSubMerchant extends Model
{
    protected $fillable = [
        'restaurant_id',
        'status',
        'legal_name',
        'business_type',
        'mobile_money',
        'mobile_money_operator',
        'email',
        'jeko_merchant_id',
        'jeko_store_id',
        'jeko_wallet_id',
        'approved_by',
        'approved_at',
        'rejected_reason',
        'integration_metadata',
    ];

    protected $casts = [
        'status' => JekoSubMerchantStatus::class,
        'mobile_money_operator' => MobileMoneyOperator::class,
        'approved_at' => 'datetime',
        'integration_metadata' => 'array',
    ];

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function approver(): BelongsTo
    {
        return $this->belongsTo(User::class, 'approved_by');
    }

    public function isPending(): bool
    {
        return $this->status === JekoSubMerchantStatus::PENDING;
    }

    public function isApproved(): bool
    {
        return $this->status === JekoSubMerchantStatus::APPROVED;
    }

    public function isRejected(): bool
    {
        return $this->status === JekoSubMerchantStatus::REJECTED;
    }

    public function isIntegrated(): bool
    {
        return $this->status === JekoSubMerchantStatus::INTEGRATED && !empty($this->jeko_merchant_id);
    }

    public function canReceivePayments(): bool
    {
        return $this->isIntegrated();
    }

    public function getDecryptedApiKey(): ?string
    {
        if (empty($this->jeko_api_key)) {
            return null;
        }

        try {
            return Crypt::decryptString($this->jeko_api_key);
        } catch (\Exception $e) {
            Log::channel('payments')->error(
                'JekoSubMerchant: failed to decrypt jeko_api_key',
                ['id' => $this->id, 'exception' => $e->getMessage()]
            );
            return null;
        }
    }

    public function setEncryptedApiKey(string $key): void
    {
        $this->jeko_api_key = Crypt::encryptString($key);
    }
}
