<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Crypt;

class SystemPaymentSetting extends Model
{
    protected $fillable = [
        'gateway',
        'is_active',
        'mode',
        'api_key',
        'webhook_secret',
        'merchant_id',
        'config',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'config' => 'array',
    ];

    /**
     * Récupère la clé API déchiffrée.
     */
    public function getDecryptedApiKey(): ?string
    {
        if (empty($this->api_key)) {
            return null;
        }

        try {
            return Crypt::decryptString($this->api_key);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Stocke la clé API chiffrée.
     */
    public function setEncryptedApiKey(string $key): void
    {
        $this->api_key = Crypt::encryptString($key);
    }

    /**
     * Récupère le webhook secret déchiffré.
     */
    public function getDecryptedWebhookSecret(): ?string
    {
        if (empty($this->webhook_secret)) {
            return null;
        }

        try {
            return Crypt::decryptString($this->webhook_secret);
        } catch (\Exception $e) {
            return null;
        }
    }

    /**
     * Stocke le webhook secret chiffré.
     */
    public function setEncryptedWebhookSecret(string $secret): void
    {
        $this->webhook_secret = Crypt::encryptString($secret);
    }

    /**
     * Vérifie si le gateway est actif.
     */
    public function isActive(): bool
    {
        return $this->is_active && !empty($this->api_key);
    }

    /**
     * Vérifie si le mode sandbox est actif.
     */
    public function isSandbox(): bool
    {
        return $this->mode === 'sandbox';
    }
}
