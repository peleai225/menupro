<?php

namespace App\Models;

use App\Models\Traits\HasSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Plan extends Model
{
    use HasFactory, HasSlug;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'price',
        'duration_days',
        'max_dishes',
        'max_categories',
        'max_employees',
        'max_orders_per_month',
        'has_delivery',
        'has_stock_management',
        'has_analytics',
        'has_custom_domain',
        'has_priority_support',
        'is_active',
        'is_featured',
        'sort_order',
    ];

    protected $casts = [
        'price' => 'integer',
        'duration_days' => 'integer',
        'max_dishes' => 'integer',
        'max_categories' => 'integer',
        'max_employees' => 'integer',
        'max_orders_per_month' => 'integer',
        'has_delivery' => 'boolean',
        'has_stock_management' => 'boolean',
        'has_analytics' => 'boolean',
        'has_custom_domain' => 'boolean',
        'has_priority_support' => 'boolean',
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
     * Get features list
     */
    public function getFeaturesAttribute(): array
    {
        $features = [];

        $features[] = $this->max_dishes . ' plats maximum';
        $features[] = $this->max_categories . ' catégories maximum';
        $features[] = $this->max_employees . ' employé(s)';
        
        if ($this->max_orders_per_month) {
            $features[] = $this->max_orders_per_month . ' commandes/mois';
        } else {
            $features[] = 'Commandes illimitées';
        }

        if ($this->has_delivery) $features[] = 'Gestion livraison';
        if ($this->has_stock_management) $features[] = 'Gestion des stocks';
        if ($this->has_analytics) $features[] = 'Statistiques avancées';
        if ($this->has_custom_domain) $features[] = 'Domaine personnalisé';
        if ($this->has_priority_support) $features[] = 'Support prioritaire';

        return $features;
    }

    // =========================================================================
    // METHODS
    // =========================================================================

    /**
     * Check if plan has a specific feature
     */
    public function hasFeature(string $feature): bool
    {
        $featureMap = [
            'delivery' => $this->has_delivery,
            'stock' => $this->has_stock_management,
            'analytics' => $this->has_analytics,
            'custom_domain' => $this->has_custom_domain,
            'priority_support' => $this->has_priority_support,
        ];

        return $featureMap[$feature] ?? false;
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
        
        return $limit !== null && $current >= $limit;
    }
}

