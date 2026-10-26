<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

/**
 * Campagne QR (bâche, affiche…) : lien court suivi /q/{code}.
 */
class QrCampaign extends Model
{
    public const TARGET_CLIENT_APP = 'client_app';
    public const TARGET_RESTAURANT_SIGNUP = 'restaurant_signup';
    public const TARGET_REPRESENTATIVE_PAGE = 'representative_page';
    public const TARGET_INTERNAL_URL = 'internal_url';

    public const TARGET_LABELS = [
        self::TARGET_CLIENT_APP => 'App client',
        self::TARGET_RESTAURANT_SIGNUP => 'Inscription restaurant',
        self::TARGET_REPRESENTATIVE_PAGE => 'Page « Devenir représentant »',
        self::TARGET_INTERNAL_URL => 'URL interne libre',
    ];

    /** Clé de session portant la campagne d'origine jusqu'à l'inscription. */
    public const SESSION_KEY = 'qr_campaign_id';

    protected $fillable = [
        'code', 'name', 'target_type', 'target_path', 'city', 'location',
        'is_active', 'scans_count', 'created_by',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'scans_count' => 'integer',
    ];

    protected static function booted(): void
    {
        static::creating(function (self $campaign) {
            if (!$campaign->code) {
                $campaign->code = self::generateCode();
            }
        });
    }

    /** Code court lisible (sans caractères ambigus 0/O, 1/l/I). */
    public static function generateCode(int $length = 6): string
    {
        $alphabet = 'abcdefghjkmnpqrstuvwxyz23456789';
        do {
            $code = '';
            for ($i = 0; $i < $length; $i++) {
                $code .= $alphabet[random_int(0, strlen($alphabet) - 1)];
            }
        } while (self::where('code', $code)->exists());

        return $code;
    }

    public function scans(): HasMany
    {
        return $this->hasMany(QrCampaignScan::class);
    }

    public function restaurants(): HasMany
    {
        return $this->hasMany(Restaurant::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getShortUrlAttribute(): string
    {
        return route('qr.redirect', $this->code);
    }

    public function getTargetLabelAttribute(): string
    {
        return self::TARGET_LABELS[$this->target_type] ?? $this->target_type;
    }

    /**
     * URL de destination, avec paramètres utm pour l'analyse (source = qr, campagne = code).
     */
    public function destinationUrl(): string
    {
        $base = match ($this->target_type) {
            self::TARGET_CLIENT_APP => (string) config('apps.client_url'),
            self::TARGET_RESTAURANT_SIGNUP => route('register'),
            self::TARGET_REPRESENTATIVE_PAGE => route('representative.program'),
            default => url('/' . ltrim((string) $this->target_path, '/')),
        };

        $separator = str_contains($base, '?') ? '&' : '?';

        return $base . $separator . http_build_query([
            'utm_source' => 'qr',
            'utm_medium' => 'affichage',
            'utm_campaign' => $this->code,
        ]);
    }

    /**
     * Chemin interne sûr : commence par « / », pas de schéma ni d'hôte (pas de redirection ouverte).
     */
    public static function isSafeInternalPath(?string $path): bool
    {
        if ($path === null || $path === '' || !str_starts_with($path, '/')) {
            return false;
        }
        if (str_starts_with($path, '//') || str_contains($path, '\\')) {
            return false;
        }

        return !preg_match('#^/[^/]*:#', $path) && !Str::contains($path, ["\n", "\r"]);
    }
}
