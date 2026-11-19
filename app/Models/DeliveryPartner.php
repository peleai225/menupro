<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Partenaire externe qui crée des colis par API (Authorization: Bearer <clé>).
 * La clé n'est jamais stockée en clair : SHA-256 (recherche indexée) + préfixe affichable.
 */
class DeliveryPartner extends Model
{
    public const KEY_PREFIX = 'mpk_';

    protected $fillable = [
        'name', 'is_active', 'cities', 'contact_name', 'contact_phone', 'contact_email',
        'api_key_hash', 'api_key_prefix', 'api_key_generated_at', 'last_used_at',
        'webhook_url', 'webhook_secret', 'discount_percent', 'pricing',
        'rate_limit_per_minute', 'notes',
    ];

    protected $hidden = ['api_key_hash', 'webhook_secret'];

    protected $casts = [
        'is_active' => 'boolean',
        'cities' => 'array',
        'pricing' => 'array',
        'discount_percent' => 'float',
        'webhook_secret' => 'encrypted',
        'api_key_generated_at' => 'datetime',
        'last_used_at' => 'datetime',
        'rate_limit_per_minute' => 'integer',
    ];

    public function parcels(): HasMany
    {
        return $this->hasMany(Parcel::class);
    }

    public function webhookLogs(): HasMany
    {
        return $this->hasMany(DeliveryPartnerWebhookLog::class);
    }

    public static function hashKey(string $plainKey): string
    {
        return hash('sha256', $plainKey);
    }

    /**
     * Génère (ou régénère) la clé d'API. Retourne la clé en clair : à afficher une seule fois.
     */
    public function generateApiKey(): string
    {
        $plain = self::KEY_PREFIX . Str::random(48);

        $this->forceFill([
            'api_key_hash' => self::hashKey($plain),
            'api_key_prefix' => substr($plain, 0, 12),
            'api_key_generated_at' => now(),
        ])->save();

        return $plain;
    }

    public function generateWebhookSecret(): string
    {
        $secret = 'whsec_' . Str::random(40);
        $this->forceFill(['webhook_secret' => $secret])->save();

        return $secret;
    }

    public static function findByApiKey(?string $plainKey): ?self
    {
        if (!$plainKey || !str_starts_with($plainKey, self::KEY_PREFIX)) {
            return null;
        }

        return static::where('api_key_hash', self::hashKey($plainKey))->first();
    }

    public function servesCity(string $city): bool
    {
        $cities = array_filter((array) $this->cities);

        if (!$cities) {
            return true;
        }

        $city = mb_strtolower(trim($city));

        return collect($cities)->contains(fn ($c) => mb_strtolower(trim((string) $c)) === $city);
    }

    /** Signature HMAC-SHA256 (hex) d'un corps de webhook. */
    public function sign(string $body): string
    {
        return hash_hmac('sha256', $body, (string) $this->webhook_secret);
    }
}
