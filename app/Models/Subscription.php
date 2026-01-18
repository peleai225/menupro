<?php

namespace App\Models;

use App\Enums\SubscriptionStatus;
use App\Models\Traits\BelongsToRestaurant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Subscription extends Model
{
    use HasFactory, BelongsToRestaurant;

    protected $fillable = [
        'restaurant_id',
        'plan_id',
        'status',
        'starts_at',
        'ends_at',
        'amount_paid',
        'payment_reference',
        'payment_method',
        'payment_metadata',
        'reminder_sent_at',
        'expired_notification_sent_at',
        'billing_period', // 'monthly', 'quarterly', 'semiannual', 'annual'
        'discount_percentage', // Pourcentage de réduction (ex: 15 pour 15%)
    ];

    protected $casts = [
        'status' => SubscriptionStatus::class,
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'amount_paid' => 'integer',
        'payment_metadata' => 'array',
        'reminder_sent_at' => 'datetime',
        'expired_notification_sent_at' => 'datetime',
        'discount_percentage' => 'integer',
    ];

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function plan(): BelongsTo
    {
        return $this->belongsTo(Plan::class);
    }

    public function addons()
    {
        return $this->hasMany(SubscriptionAddon::class);
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    public function scopeActive($query)
    {
        return $query->where('status', SubscriptionStatus::ACTIVE);
    }

    public function scopeExpired($query)
    {
        return $query->where('status', SubscriptionStatus::EXPIRED);
    }

    public function scopeExpiring($query)
    {
        return $query->where('status', SubscriptionStatus::ACTIVE)
            ->where('ends_at', '<=', now()->addDays(7));
    }

    public function scopeNeedsReminder($query)
    {
        return $query->where('status', SubscriptionStatus::ACTIVE)
            ->where('ends_at', '<=', now()->addDays(7))
            ->where('ends_at', '>', now())
            ->whereNull('reminder_sent_at');
    }

    public function scopeNeedsExpiration($query)
    {
        return $query->where('status', SubscriptionStatus::ACTIVE)
            ->where('ends_at', '<', now());
    }

    // =========================================================================
    // ACCESSORS
    // =========================================================================

    public function getIsActiveAttribute(): bool
    {
        return $this->status === SubscriptionStatus::ACTIVE && $this->ends_at->isFuture();
    }

    public function getIsExpiredAttribute(): bool
    {
        return $this->status === SubscriptionStatus::EXPIRED || $this->ends_at->isPast();
    }

    public function getDaysRemainingAttribute(): int
    {
        return max(0, now()->diffInDays($this->ends_at, false));
    }

    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount_paid, 0, ',', ' ') . ' FCFA';
    }

    public function getDurationDaysAttribute(): int
    {
        return $this->starts_at->diffInDays($this->ends_at);
    }

    // =========================================================================
    // METHODS
    // =========================================================================

    /**
     * Check if subscription is active
     */
    public function isActive(): bool
    {
        return $this->status === SubscriptionStatus::ACTIVE && $this->ends_at && $this->ends_at->isFuture();
    }

    /**
     * Mark as expired
     */
    public function markAsExpired(): bool
    {
        $this->status = SubscriptionStatus::EXPIRED;
        return $this->save();
    }

    /**
     * Mark reminder as sent
     */
    public function markReminderSent(): bool
    {
        $this->reminder_sent_at = now();
        return $this->save();
    }

    /**
     * Mark expired notification as sent
     */
    public function markExpiredNotificationSent(): bool
    {
        $this->expired_notification_sent_at = now();
        return $this->save();
    }

    /**
     * Renew subscription with new plan
     */
    public function renew(Plan $plan, array $paymentData = []): Subscription
    {
        return static::create([
            'restaurant_id' => $this->restaurant_id,
            'plan_id' => $plan->id,
            'status' => SubscriptionStatus::ACTIVE,
            'starts_at' => now(),
            'ends_at' => now()->addDays($plan->duration_days),
            'amount_paid' => $plan->price,
            'payment_reference' => $paymentData['reference'] ?? null,
            'payment_method' => $paymentData['method'] ?? null,
            'payment_metadata' => $paymentData['metadata'] ?? null,
            'billing_period' => $paymentData['billing_period'] ?? 'monthly',
            'discount_percentage' => $paymentData['discount_percentage'] ?? 0,
        ]);
    }

    /**
     * Calculate total price including add-ons and discount
     */
    public function getTotalPriceAttribute(): int
    {
        $basePrice = $this->amount_paid;
        $addonsPrice = $this->addons()->sum('price');
        $total = $basePrice + $addonsPrice;
        
        if ($this->discount_percentage > 0) {
            $discount = ($total * $this->discount_percentage) / 100;
            $total = $total - $discount;
        }
        
        return (int) $total;
    }

    /**
     * Get formatted total price
     */
    public function getFormattedTotalPriceAttribute(): string
    {
        return number_format($this->total_price, 0, ',', ' ') . ' FCFA';
    }

    /**
     * Calculate price with discount for a given billing period
     */
    public static function calculatePriceWithDiscount(int $basePrice, string $billingPeriod): array
    {
        $periods = [
            'monthly' => ['months' => 1, 'discount' => 0],
            'quarterly' => ['months' => 3, 'discount' => 7],
            'semiannual' => ['months' => 6, 'discount' => 13],
            'annual' => ['months' => 12, 'discount' => 15],
        ];

        $period = $periods[$billingPeriod] ?? $periods['monthly'];
        $totalPrice = $basePrice * $period['months'];
        $discountAmount = ($totalPrice * $period['discount']) / 100;
        $finalPrice = $totalPrice - $discountAmount;

        return [
            'base_price' => $basePrice,
            'billing_period' => $billingPeriod,
            'months' => $period['months'],
            'total_before_discount' => $totalPrice,
            'discount_percentage' => $period['discount'],
            'discount_amount' => (int) $discountAmount,
            'final_price' => (int) $finalPrice,
            'monthly_equivalent' => (int) ($finalPrice / $period['months']),
        ];
    }
}

