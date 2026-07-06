<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class SystemSetting extends Model
{
    protected $fillable = [
        'key',
        'value',
        'type',
        'description',
    ];

    /**
     * Get a setting value by key.
     * Le résultat est mis en cache 5 minutes pour éviter une requête SQL par appel
     * (81 occurrences dans l'application, ~40 dans settings() seul).
     */
    public static function get(string $key, $default = null)
    {
        $setting = Cache::remember('system_setting_' . $key, 300, function () use ($key) {
            return static::where('key', $key)->first();
        });

        if (!$setting) {
            return $default;
        }

        return match ($setting->type) {
            'boolean' => filter_var($setting->value, FILTER_VALIDATE_BOOLEAN),
            'integer' => (int) $setting->value,
            'json' => json_decode($setting->value, true),
            default => $setting->value,
        };
    }

    /**
     * Set a setting value by key.
     */
    public static function set(string $key, $value, string $type = 'string', string $description = null): self
    {
        $setting = static::firstOrNew(['key' => $key]);

        $setting->value = match ($type) {
            'boolean' => $value ? '1' : '0',
            'integer' => (string) $value,
            'json' => json_encode($value),
            default => (string) $value,
        };

        $setting->type = $type;
        if ($description) {
            $setting->description = $description;
        }

        $setting->save();

        // Invalider le cache pour cette clé après écriture
        Cache::forget('system_setting_' . $key);

        return $setting;
    }

    /**
     * Check if a setting exists.
     */
    public static function has(string $key): bool
    {
        // Réutilise le cache — si l'entrée est cachée et non-null, elle existe
        $cached = Cache::get('system_setting_' . $key);
        if ($cached !== null) {
            return true;
        }

        return static::where('key', $key)->exists();
    }
}
