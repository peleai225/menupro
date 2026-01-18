<?php

namespace App\Models;

use App\Models\Traits\BelongsToRestaurant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PromoCode extends Model
{
    use HasFactory, BelongsToRestaurant;

    protected $fillable = [
        'restaurant_id',
        'code',
        'description',
        'discount_type',
        'discount_value',
        'min_order_amount',
        'max_discount_amount',
        'max_uses',
        'max_uses_per_customer',
        'current_uses',
        'starts_at',
        'expires_at',
        'is_active',
    ];

    protected $casts = [
        'discount_value' => 'integer',
        'min_order_amount' => 'integer',
        'max_discount_amount' => 'integer',
        'max_uses' => 'integer',
        'max_uses_per_customer' => 'integer',
        'current_uses' => 'integer',
        'starts_at' => 'datetime',
        'expires_at' => 'datetime',
        'is_active' => 'boolean',
    ];

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function uses(): HasMany
    {
        return $this->hasMany(PromoCodeUse::class);
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeValid($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')
                  ->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('expires_at')
                  ->orWhere('expires_at', '>', now());
            })
            ->where(function ($q) {
                $q->whereNull('max_uses')
                  ->orWhereColumn('current_uses', '<', 'max_uses');
            });
    }

    public function scopeByCode($query, string $code)
    {
        return $query->where('code', strtoupper($code));
    }

    // =========================================================================
    // ACCESSORS
    // =========================================================================

    public function getIsValidAttribute(): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return false;
        }

        if ($this->max_uses && $this->current_uses >= $this->max_uses) {
            return false;
        }

        return true;
    }

    public function getDiscountLabelAttribute(): string
    {
        if ($this->discount_type === 'percentage') {
            return $this->discount_value . '%';
        }
        return number_format($this->discount_value, 0, ',', ' ') . ' FCFA';
    }

    public function getRemainingUsesAttribute(): ?int
    {
        if (!$this->max_uses) {
            return null;
        }
        return max(0, $this->max_uses - $this->current_uses);
    }

    // =========================================================================
    // METHODS
    // =========================================================================

    /**
     * Check if code can be used by customer
     */
    public function canBeUsedBy(string $email): bool
    {
        if (!$this->is_valid) {
            return false;
        }

        $customerUses = $this->uses()->where('customer_email', $email)->count();
        
        return $customerUses < $this->max_uses_per_customer;
    }

    /**
     * Calculate discount for order amount
     */
    public function calculateDiscount(int $orderAmount): int
    {
        if (!$this->is_valid) {
            return 0;
        }

        if ($this->min_order_amount && $orderAmount < $this->min_order_amount) {
            return 0;
        }

        $discount = match ($this->discount_type) {
            'percentage' => (int) round($orderAmount * $this->discount_value / 100),
            'fixed' => $this->discount_value,
            default => 0,
        };

        // Apply max discount cap for percentage
        if ($this->discount_type === 'percentage' && $this->max_discount_amount) {
            $discount = min($discount, $this->max_discount_amount);
        }

        return min($discount, $orderAmount);
    }

    /**
     * Apply promo code to order
     */
    public function applyToOrder(Order $order, string $customerEmail): int
    {
        $discount = $this->calculateDiscount($order->subtotal);

        if ($discount > 0) {
            $this->uses()->create([
                'order_id' => $order->id,
                'customer_email' => $customerEmail,
                'discount_applied' => $discount,
            ]);

            $this->increment('current_uses');
        }

        return $discount;
    }

    /**
     * Get validation error message
     */
    public function getValidationError(int $orderAmount = 0, string $email = null): ?string
    {
        if (!$this->is_active) {
            return 'Ce code promo n\'est plus actif.';
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return 'Ce code promo n\'est pas encore valide.';
        }

        if ($this->expires_at && $this->expires_at->isPast()) {
            return 'Ce code promo a expiré.';
        }

        if ($this->max_uses && $this->current_uses >= $this->max_uses) {
            return 'Ce code promo a atteint sa limite d\'utilisation.';
        }

        if ($this->min_order_amount && $orderAmount < $this->min_order_amount) {
            $min = number_format($this->min_order_amount, 0, ',', ' ');
            return "Commande minimum de {$min} FCFA requise.";
        }

        if ($email) {
            $customerUses = $this->uses()->where('customer_email', $email)->count();
            if ($customerUses >= $this->max_uses_per_customer) {
                return 'Vous avez déjà utilisé ce code promo.';
            }
        }

        return null;
    }
}

