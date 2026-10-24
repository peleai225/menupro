<?php

namespace App\Models;

use App\Enums\RestaurantStatus;
use App\Enums\SubscriptionStatus;
use App\Models\Traits\BelongsToRestaurant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\DB;

class Subscription extends Model
{
    use HasFactory, BelongsToRestaurant;

    protected $fillable = [
        'restaurant_id',
        'plan_id',
        'status',
        'is_trial',
        'trial_days',
        'starts_at',
        'ends_at',
        'amount_paid',
        'payment_reference',
        'payment_method',
        'payment_metadata',
        'paid_at', // Date d'encaissement confirmé (webhook / callback de paiement)
        'reminder_sent_at',
        'expired_notification_sent_at',
        'billing_period', // 'monthly', 'quarterly', 'semiannual', 'annual'
        'discount_percentage', // Pourcentage de réduction (ex: 15 pour 15%)
    ];

    protected $casts = [
        'status' => SubscriptionStatus::class,
        'is_trial' => 'boolean',
        'trial_days' => 'integer',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'amount_paid' => 'integer',
        'payment_metadata' => 'array',
        'paid_at' => 'datetime',
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

    /**
     * Le restaurant a-t-il un autre abonnement (payant ou essai) encore valide ?
     * Sert aux jobs d'expiration : un ancien abonnement en doublon ne doit pas bloquer
     * un restaurant qui a renouvelé.
     */
    public static function hasOtherValidFor(int $restaurantId, int $exceptSubscriptionId): bool
    {
        return static::withoutGlobalScopes()
            ->where('restaurant_id', $restaurantId)
            ->where('id', '!=', $exceptSubscriptionId)
            ->whereIn('status', [SubscriptionStatus::ACTIVE, SubscriptionStatus::TRIAL])
            ->where('ends_at', '>', now())
            ->exists();
    }

    /**
     * Active cet abonnement comme UNIQUE abonnement en cours du restaurant.
     *
     * Point d'entrée commun à toutes les activations (webhooks de paiement, callback de
     * paiement, prolongation super-admin, validation d'un restaurant) :
     *  1. applique $attributes (statut ACTIVE par défaut, sauf essai TRIAL qu'on démarre) ;
     *  2. clôt les autres abonnements active/trial du restaurant (status expired +
     *     expired_notification_sent_at pour que les jobs n'envoient pas de mail
     *     « abonnement expiré » sur un doublon) — les doublons faussaient les indicateurs ;
     *  3. aligne le restaurant (plan courant, date de fin, status ACTIVE, commandes
     *     débloquées). Un restaurant SUSPENDU (décision admin) ou EN ATTENTE de validation
     *     garde son statut : un paiement ne lève pas une suspension et ne court-circuite
     *     pas la validation.
     */
    public function activateExclusively(array $attributes = []): self
    {
        DB::transaction(function () use ($attributes) {
            $this->fill($attributes);

            if (!in_array($this->status, [SubscriptionStatus::ACTIVE, SubscriptionStatus::TRIAL], true)) {
                $this->status = SubscriptionStatus::ACTIVE;
            }

            $this->save();

            // Mise à jour en masse : pas d'observer → pas de notification admin
            // « abonnement expiré » pour un simple doublon remplacé.
            static::withoutGlobalScopes()
                ->where('restaurant_id', $this->restaurant_id)
                ->where('id', '!=', $this->id)
                ->whereIn('status', [SubscriptionStatus::ACTIVE->value, SubscriptionStatus::TRIAL->value])
                ->update([
                    'status' => SubscriptionStatus::EXPIRED->value,
                    'expired_notification_sent_at' => now(),
                    'updated_at' => now(),
                ]);

            $restaurant = Restaurant::withTrashed()->find($this->restaurant_id);

            if ($restaurant) {
                $restaurantData = [
                    'current_plan_id' => $this->plan_id,
                    'subscription_ends_at' => $this->ends_at,
                ];

                if (!in_array($restaurant->status, [RestaurantStatus::SUSPENDED, RestaurantStatus::PENDING], true)) {
                    $restaurantData['status'] = RestaurantStatus::ACTIVE;
                    $restaurantData['orders_blocked'] = false;
                }

                $restaurant->update($restaurantData);
            }
        });

        return $this;
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    public function scopeActive($query)
    {
        return $query->whereIn('status', [SubscriptionStatus::ACTIVE, SubscriptionStatus::TRIAL]);
    }

    public function scopeTrial($query)
    {
        return $query->where(function ($q) {
            $q->where('status', SubscriptionStatus::TRIAL)
              ->orWhere('is_trial', true);
        });
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
        // Essai gelé (starts_at/ends_at null, en attente de validation admin) :
        // jamais considéré actif.
        return $this->status === SubscriptionStatus::ACTIVE
            && $this->ends_at !== null
            && $this->ends_at->isFuture();
    }

    public function getIsExpiredAttribute(): bool
    {
        if ($this->ends_at === null) {
            return false;
        }

        return $this->status === SubscriptionStatus::EXPIRED || $this->ends_at->isPast();
    }

    public function getDaysRemainingAttribute(): int
    {
        if ($this->ends_at === null) {
            return 0;
        }

        return max(0, now()->diffInDays($this->ends_at, false));
    }

    public function getFormattedAmountAttribute(): string
    {
        return number_format($this->amount_paid, 0, ',', ' ') . ' FCFA';
    }

    public function getDurationDaysAttribute(): int
    {
        if ($this->starts_at === null || $this->ends_at === null) {
            return 0;
        }

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
        return in_array($this->status, [SubscriptionStatus::ACTIVE, SubscriptionStatus::TRIAL]) 
            && $this->ends_at 
            && $this->ends_at->isFuture();
    }

    /**
     * Check if subscription is a trial
     */
    public function isTrial(): bool
    {
        return $this->is_trial || $this->status === SubscriptionStatus::TRIAL;
    }

    /**
     * Convert trial to paid subscription
     */
    public function convertToPaid(array $paymentData = []): bool
    {
        return $this->update([
            'status' => SubscriptionStatus::ACTIVE,
            'is_trial' => false,
            'amount_paid' => $paymentData['amount'] ?? $this->amount_paid,
            'payment_reference' => $paymentData['reference'] ?? $this->payment_reference,
            'payment_method' => $paymentData['method'] ?? $this->payment_method,
            'payment_metadata' => array_merge($this->payment_metadata ?? [], $paymentData['metadata'] ?? []),
        ]);
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
            'quarterly' => ['months' => 3, 'discount' => 10],
            'semiannual' => ['months' => 6, 'discount' => 15],
            'annual' => ['months' => 12, 'discount' => 20],
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

