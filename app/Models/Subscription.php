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
        'billing_period', // 'weekly' (Stand uniquement), 'monthly', 'quarterly', 'semiannual', 'annual'
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
     * Période réelle d'un abonnement au moment où il est payé : un renouvellement anticipé
     * démarre à la fin de l'abonnement PAYANT encore en cours (les jours restants ne sont
     * pas perdus) ; sinon maintenant. Un essai en cours n'est pas prolongé (paiement = fin
     * de l'essai). Durée = celle de la période de facturation ($days pour une prolongation).
     *
     * @return array{starts_at: \Carbon\Carbon, ends_at: \Carbon\Carbon}
     */
    public function paidPeriodAttributes(?int $days = null): array
    {
        $currentEnd = static::withoutGlobalScopes()
            ->where('restaurant_id', $this->restaurant_id)
            ->when($this->exists, fn ($q) => $q->where('id', '!=', $this->id))
            ->where('status', SubscriptionStatus::ACTIVE)
            ->where('is_trial', false)
            ->where('ends_at', '>', now())
            ->max('ends_at');

        $start = $currentEnd ? \Carbon\Carbon::parse($currentEnd) : now();

        return [
            'starts_at' => $start,
            'ends_at' => $start->copy()->addDays($days ?? static::durationDaysFor($this->billing_period)),
        ];
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

                // Changement de plan payant : l'ancien prix verrouillé ne suit pas (un essai ne
                // compte pas comme changement, il n'altère pas le tarif d'un ancien abonné).
                if ($this->status !== SubscriptionStatus::TRIAL
                    && $restaurant->locked_price !== null
                    && (int) $restaurant->current_plan_id !== (int) $this->plan_id) {
                    $restaurantData['locked_price'] = null;
                }

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

    public function scopeNeedsReminder($query, int $daysBeforeExpiry = 7)
    {
        return $query->where('status', SubscriptionStatus::ACTIVE)
            ->where('ends_at', '>', now())
            ->whereNull('reminder_sent_at')
            ->where(function ($q) use ($daysBeforeExpiry) {
                // Abonnement de 7 jours : un rappel J-7 partirait dès le paiement → rappel J-1
                $weeklyDays = static::reminderDaysFor('weekly');
                $q->where(function ($w) use ($weeklyDays) {
                    $w->where('billing_period', 'weekly')
                      ->where('ends_at', '<=', now()->addDays($weeklyDays));
                })->orWhere(function ($m) use ($daysBeforeExpiry) {
                    $m->where(fn ($p) => $p->whereNull('billing_period')->orWhere('billing_period', '!=', 'weekly'))
                      ->where('ends_at', '<=', now()->addDays($daysBeforeExpiry));
                });
            });
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
        $billingPeriod = $paymentData['billing_period'] ?? 'monthly';
        // Prix verrouillé éventuel (anciens abonnés) pris en compte par quote()
        $quote = static::quote($plan, $billingPeriod, Restaurant::find($this->restaurant_id));

        return static::create([
            'restaurant_id' => $this->restaurant_id,
            'plan_id' => $plan->id,
            'status' => SubscriptionStatus::ACTIVE,
            'starts_at' => now(),
            'ends_at' => now()->addDays($quote['duration_days']),
            'amount_paid' => $quote['final_price'],
            'payment_reference' => $paymentData['reference'] ?? null,
            'payment_method' => $paymentData['method'] ?? null,
            'payment_metadata' => $paymentData['metadata'] ?? null,
            'billing_period' => $billingPeriod,
            'discount_percentage' => $quote['discount_percentage'],
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

    // =========================================================================
    // PÉRIODES DE FACTURATION & PRIX (source unique : config/plans.php)
    // =========================================================================

    /**
     * Périodes de facturation connues : ['weekly' => ['label', 'days', 'months', 'discount'], …]
     */
    public static function billingPeriods(): array
    {
        return config('plans.billing_periods', []);
    }

    /**
     * Règle de validation Laravel « in: » des périodes de facturation.
     */
    public static function billingPeriodRule(): string
    {
        return 'in:' . implode(',', array_keys(static::billingPeriods()));
    }

    /**
     * Durée (jours) d'une période de facturation. Inconnue → mensuel (30 j).
     */
    public static function durationDaysFor(?string $billingPeriod): int
    {
        $periods = static::billingPeriods();

        return (int) ($periods[$billingPeriod]['days'] ?? $periods['monthly']['days'] ?? 30);
    }

    /**
     * Nombre de mois facturés (fraction pour l'hebdomadaire) — sert au prorata des options.
     */
    public static function monthsFor(?string $billingPeriod): float
    {
        return $billingPeriod === 'weekly'
            ? static::durationDaysFor('weekly') / 30
            : (float) (static::billingPeriods()[$billingPeriod]['months'] ?? 1);
    }

    /**
     * Calculate price with discount for a given billing period.
     *
     * $basePrice est le prix MENSUEL, sauf pour 'weekly' où c'est le prix de la SEMAINE
     * (sans remise). Préférer quote() qui choisit la bonne base (hebdo, prix verrouillé…).
     */
    public static function calculatePriceWithDiscount(int $basePrice, string $billingPeriod): array
    {
        $periods = static::billingPeriods();

        if (!isset($periods[$billingPeriod])) {
            $billingPeriod = 'monthly';
        }

        $period = $periods[$billingPeriod];
        $isWeekly = $billingPeriod === 'weekly';

        $totalPrice = $isWeekly ? $basePrice : $basePrice * $period['months'];
        $discountAmount = ($totalPrice * $period['discount']) / 100;
        $finalPrice = $totalPrice - $discountAmount;

        return [
            'base_price' => $basePrice,
            'billing_period' => $billingPeriod,
            'months' => $period['months'],
            'duration_days' => (int) $period['days'],
            'total_before_discount' => $totalPrice,
            'discount_percentage' => $period['discount'],
            'discount_amount' => (int) $discountAmount,
            'final_price' => (int) $finalPrice,
            'monthly_equivalent' => $isWeekly
                ? (int) round($finalPrice * 30 / $period['days'])
                : (int) ($finalPrice / $period['months']),
        ];
    }

    /**
     * Prix à payer pour un plan et une période, pour un restaurant donné.
     *
     *  - 'weekly' : uniquement pour les plans qui l'autorisent (Stand), prix plans.weekly_price ;
     *  - sinon : prix verrouillé du restaurant (restaurants.locked_price, anciens abonnés) s'il
     *    s'applique à ce plan, à défaut le prix du plan ; remises de période appliquées.
     *
     * @throws \InvalidArgumentException période hebdomadaire demandée pour un plan qui ne l'autorise pas
     */
    public static function quote(Plan $plan, string $billingPeriod, ?Restaurant $restaurant = null): array
    {
        if ($billingPeriod === 'weekly') {
            if (!$plan->supportsWeeklyBilling()) {
                throw new \InvalidArgumentException("Le plan {$plan->name} ne peut pas être payé à la semaine.");
            }

            return static::calculatePriceWithDiscount((int) $plan->weekly_price, 'weekly')
                + ['locked_price_applied' => false];
        }

        $lockedPrice = $restaurant?->lockedPriceFor($plan);

        return static::calculatePriceWithDiscount($lockedPrice ?? (int) $plan->price, $billingPeriod)
            + ['locked_price_applied' => $lockedPrice !== null];
    }

    /**
     * Rappel de fin d'abonnement : J-1 pour un abonnement hebdomadaire, J-7 sinon.
     */
    public static function reminderDaysFor(?string $billingPeriod): int
    {
        return $billingPeriod === 'weekly' ? 1 : 7;
    }
}

