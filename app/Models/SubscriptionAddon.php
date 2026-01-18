<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SubscriptionAddon extends Model
{
    use HasFactory;

    protected $fillable = [
        'subscription_id',
        'addon_type',
        'name',
        'price',
        'metadata',
    ];

    protected $casts = [
        'price' => 'integer',
        'metadata' => 'array',
    ];

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function subscription(): BelongsTo
    {
        return $this->belongsTo(Subscription::class);
    }

    // =========================================================================
    // ACCESSORS
    // =========================================================================

    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->price, 0, ',', ' ') . ' FCFA';
    }

    // =========================================================================
    // CONSTANTS
    // =========================================================================

    public const TYPE_PRIORITY_SUPPORT = 'priority_support';
    public const TYPE_CUSTOM_DOMAIN = 'custom_domain';
    public const TYPE_EXTRA_EMPLOYEES = 'extra_employees';
    public const TYPE_EXTRA_DISHES = 'extra_dishes';

    public static function getAvailableAddons(): array
    {
        return [
            self::TYPE_PRIORITY_SUPPORT => [
                'name' => 'Support Prioritaire',
                'price' => 5000, // 5 000 FCFA/mois
                'description' => 'Réponse garantie sous 2h, support prioritaire par email et téléphone',
            ],
            self::TYPE_CUSTOM_DOMAIN => [
                'name' => 'Domaine Personnalisé',
                'price' => 3000, // 3 000 FCFA/mois
                'description' => 'Utilisez votre propre nom de domaine (ex: www.monrestaurant.com)',
            ],
            self::TYPE_EXTRA_EMPLOYEES => [
                'name' => 'Employés Supplémentaires',
                'price' => 2000, // 2 000 FCFA/employé/mois
                'description' => 'Ajoutez des employés au-delà de la limite de base (5 employés)',
            ],
            self::TYPE_EXTRA_DISHES => [
                'name' => 'Plats Supplémentaires',
                'price' => 500, // 500 FCFA/10 plats/mois
                'description' => 'Ajoutez des plats au-delà de la limite de base (100 plats)',
            ],
        ];
    }
}
