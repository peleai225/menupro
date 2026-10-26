<?php

namespace App\Models;

use App\Models\Traits\HasSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use HasFactory, HasSlug;

    /**
     * Le slug d'un plan est un identifiant stable (code, seeder, inscription ?type=stand) :
     * renommer un plan ne doit jamais le changer.
     */
    protected bool $keepSlugOnRename = true;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'weekly_price', // Prix à la semaine (Stand uniquement, voir config('plans.weekly_plans'))
        'duration_days',
        'max_dishes',
        'max_categories',
        'max_employees',
        'max_orders_per_month', // Quota SOUPLE : alerte au restaurant, ne bloque jamais une commande
        'has_delivery',
        'has_stock_management', // Stock simple (stock journalier des plats)
        'has_ingredient_stock', // Stock par ingrédient + rentabilité par plat
        'has_analytics',
        'has_custom_domain',
        'has_priority_support',
        'has_hotel_rooms',
        'has_multi_spaces',
        'is_active',
        'is_featured',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'integer',
        'weekly_price' => 'integer',
        'duration_days' => 'integer',
        'max_dishes' => 'integer',
        'max_categories' => 'integer',
        'max_employees' => 'integer',
        'max_orders_per_month' => 'integer',
        'has_delivery' => 'boolean',
        'has_stock_management' => 'boolean',
        'has_ingredient_stock' => 'boolean',
        'has_analytics' => 'boolean',
        'has_custom_domain' => 'boolean',
        'has_priority_support' => 'boolean',
        'has_hotel_rooms' => 'boolean',
        'has_multi_spaces' => 'boolean',
        'is_active' => 'boolean',
        'is_featured' => 'boolean',
        'sort_order' => 'integer',
    ];

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function restaurants(): HasMany
    {
        return $this->hasMany(Restaurant::class, 'current_plan_id');
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('price');
    }

    // =========================================================================
    // ACCESSORS
    // =========================================================================

    /**
     * Get formatted price
     */
    public function getFormattedPriceAttribute(): string
    {
        return number_format($this->price, 0, ',', ' ') . ' FCFA';
    }

    /**
     * Get monthly price equivalent
     */
    public function getMonthlyPriceAttribute(): int
    {
        if ($this->duration_days <= 0) {
            return 0;
        }
        return (int) round($this->price / $this->duration_days * 30);
    }

    /**
     * Plan « sur devis » (Gold) : le prix stocké est un prix plancher, affiché « à partir de ».
     */
    public function getIsOnQuoteAttribute(): bool
    {
        return $this->slug === 'gold';
    }

    /**
     * Prix affiché : « 15 000 F/mois », ou « Sur devis, à partir de 75 000 F » pour Gold.
     */
    public function getPriceLabelAttribute(): string
    {
        $price = number_format($this->price, 0, ',', ' ');

        return $this->is_on_quote
            ? "Sur devis, à partir de {$price} F"
            : "{$price} F/mois";
    }

    /**
     * Get features list
     */
    public function getFeaturesAttribute(): array
    {
        $features = [];

        $features[] = self::isUnlimitedValue($this->max_dishes) ? 'Plats illimités' : $this->max_dishes . ' plats maximum';
        $features[] = self::isUnlimitedValue($this->max_categories) ? 'Catégories illimitées' : $this->max_categories . ' catégories maximum';
        $features[] = match (true) {
            self::isUnlimitedValue($this->max_employees) => 'Employés illimités',
            (int) $this->max_employees === 0 => '1 utilisateur (le gérant)',
            default => $this->max_employees . ' employé(s)',
        };

        $features[] = self::isUnlimitedValue($this->max_orders_per_month)
            ? 'Commandes illimitées'
            : number_format($this->max_orders_per_month, 0, ',', ' ') . ' commandes/mois conseillées (jamais bloquées)';

        if ($this->has_delivery) $features[] = 'Livraison MenuPro incluse';
        if ($this->has_stock_management) $features[] = 'Gestion de stock simple';
        if ($this->has_ingredient_stock) $features[] = 'Stock par ingrédient & rentabilité par plat';
        if ($this->has_analytics) $features[] = 'Statistiques';
        if ($this->has_hotel_rooms) $features[] = 'Chambres d\'hôtel';
        if ($this->has_multi_spaces) $features[] = 'Multi-espaces';
        if ($this->has_custom_domain) $features[] = 'Domaine personnalisé';
        if ($this->has_priority_support) $features[] = 'Support prioritaire';

        return $features;
    }

    // =========================================================================
    // METHODS
    // =========================================================================

    /**
     * Une limite est illimitée si elle est nulle ou >= 9999 (convention historique).
     * Attention : 0 signifie bien ZÉRO.
     */
    public static function isUnlimitedValue(?int $value): bool
    {
        return $value === null || $value >= (int) config('plans.unlimited_threshold', 9999);
    }

    /**
     * Le plan peut-il être payé à la semaine ?
     */
    public function supportsWeeklyBilling(): bool
    {
        return in_array($this->slug, config('plans.weekly_plans', []), true)
            && (int) $this->weekly_price > 0;
    }

    /**
     * Périodes de facturation proposées pour ce plan (clés de config('plans.billing_periods')).
     */
    public function availableBillingPeriods(): array
    {
        $periods = array_keys(config('plans.billing_periods', []));

        if (!$this->supportsWeeklyBilling()) {
            $periods = array_values(array_diff($periods, ['weekly']));
        }

        return $periods;
    }

    /**
     * Taux de commission plateforme (%) des commandes livrées via MenuPro pour ce plan.
     */
    public function commissionRate(): float
    {
        $config = config('plans.commission');

        return in_array($this->slug, $config['reduced_plans'] ?? [], true)
            ? (float) $config['reduced_rate']
            : (float) $config['default_rate'];
    }

    /**
     * Check if plan has a specific feature
     */
    public function hasFeature(string $feature): bool
    {
        $featureMap = [
            'delivery' => $this->has_delivery,
            'stock' => $this->has_stock_management,
            'ingredient_stock' => $this->has_ingredient_stock,
            'analytics' => $this->has_analytics,
            'custom_domain' => $this->has_custom_domain,
            'priority_support' => $this->has_priority_support,
            'hotel_rooms' => $this->has_hotel_rooms,
            'multi_spaces' => $this->has_multi_spaces,
        ];

        return (bool) ($featureMap[$feature] ?? false);
    }

    /**
     * Check if limit is reached
     */
    public function isLimitReached(string $type, int $current): bool
    {
        $limits = [
            'dishes' => $this->max_dishes,
            'categories' => $this->max_categories,
            'employees' => $this->max_employees,
        ];

        $limit = $limits[$type] ?? null;

        if (self::isUnlimitedValue($limit)) {
            return false;
        }

        return $current >= $limit;
    }
}
