<?php

namespace App\Models;

use App\Enums\RestaurantStatus;
use App\Enums\SubscriptionStatus;
use App\Models\Traits\HasSlug;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Support\Facades\Storage;

class Restaurant extends Model
{
    use HasFactory, HasSlug, SoftDeletes;

    protected $fillable = [
        'name',
        'type',
        'company_name',
        'rccm',
        'rccm_document_path',
        'slug',
        'email',
        'phone',
        'description',
        'address',
        'city',
        'postal_code',
        'latitude',
        'longitude',
        'status',
        'validated_at',
        'suspended_at',
        'suspension_reason',
        'current_plan_id',
        'subscription_ends_at',
        'orders_blocked',
        'logo_path',
        'banner_path',
        'primary_color',
        'secondary_color',
        'currency',
        'timezone',
        'opening_hours',
        'min_order_amount',
        'delivery_fee',
        'delivery_radius_km',
        'tax_rate',
        'tax_included',
        'tax_name',
        'service_fee_rate',
        'service_fee_fixed',
        'service_fee_enabled',
        'estimated_prep_time',
        'lygos_api_key',
        'lygos_api_secret',
        'lygos_enabled',
        'settings',
        'verified_at',
        'verified_by',
        'delivery_enabled',
        'delivery_zones',
        'cash_on_delivery',
        'tagline',
    ];

    protected $casts = [
        'status' => RestaurantStatus::class,
        'validated_at' => 'datetime',
        'suspended_at' => 'datetime',
        'subscription_ends_at' => 'datetime',
        'orders_blocked' => 'boolean',
        'opening_hours' => 'array',
        'min_order_amount' => 'integer',
        'delivery_fee' => 'integer',
        'delivery_radius_km' => 'integer',
        'tax_rate' => 'decimal:2',
        'tax_included' => 'boolean',
        'service_fee_rate' => 'decimal:2',
        'service_fee_fixed' => 'integer',
        'service_fee_enabled' => 'boolean',
        'estimated_prep_time' => 'integer',
        'lygos_enabled' => 'boolean',
        'settings' => 'array',
        'verified_at' => 'datetime',
        'delivery_enabled' => 'boolean',
        'cash_on_delivery' => 'boolean',
    ];

    protected $hidden = [
        'lygos_api_key',
        'lygos_api_secret',
    ];

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function currentPlan(): BelongsTo
    {
        return $this->belongsTo(Plan::class, 'current_plan_id');
    }

    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class);
    }

    public function activeSubscription(): HasOne
    {
        return $this->hasOne(Subscription::class)
            ->whereIn('status', [SubscriptionStatus::ACTIVE, SubscriptionStatus::TRIAL])
            ->latest();
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function owner(): HasOne
    {
        return $this->hasOne(User::class)
            ->where('role', 'restaurant_admin')
            ->oldest();
    }

    public function verifiedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class);
    }

    public function dishes(): HasMany
    {
        return $this->hasMany(Dish::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function ingredients(): HasMany
    {
        return $this->hasMany(Ingredient::class);
    }

    public function ingredientCategories(): HasMany
    {
        return $this->hasMany(IngredientCategory::class);
    }

    public function suppliers(): HasMany
    {
        return $this->hasMany(Supplier::class);
    }

    public function promoCodes(): HasMany
    {
        return $this->hasMany(PromoCode::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function notificationSettings(): HasOne
    {
        return $this->hasOne(NotificationSetting::class);
    }

    public function reviews(): HasMany
    {
        return $this->hasMany(Review::class);
    }

    public function reservations(): HasMany
    {
        return $this->hasMany(Reservation::class);
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    public function scopeActive($query)
    {
        return $query->where('status', RestaurantStatus::ACTIVE);
    }

    public function scopePending($query)
    {
        return $query->where('status', RestaurantStatus::PENDING);
    }

    public function scopeSuspended($query)
    {
        return $query->where('status', RestaurantStatus::SUSPENDED);
    }

    public function scopeExpired($query)
    {
        return $query->where('status', RestaurantStatus::EXPIRED);
    }

    public function scopeCanAcceptOrders($query)
    {
        return $query->where('status', RestaurantStatus::ACTIVE)
            ->where('orders_blocked', false);
    }

    public function scopeExpiringWithinDays($query, int $days)
    {
        return $query->where('subscription_ends_at', '<=', now()->addDays($days))
            ->where('subscription_ends_at', '>', now());
    }

    public function scopeOfType($query, string $type)
    {
        return $query->where('type', $type);
    }

    // =========================================================================
    // ACCESSORS
    // =========================================================================

    public function getLogoUrlAttribute(): ?string
    {
        if (!$this->logo_path) {
            return null;
        }
        return Storage::url($this->logo_path);
    }

    public function getBannerUrlAttribute(): ?string
    {
        if (!$this->banner_path) {
            return null;
        }
        return Storage::url($this->banner_path);
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status === RestaurantStatus::ACTIVE;
    }

    public function getCanAcceptOrdersAttribute(): bool
    {
        return $this->status->canAcceptOrders() && !$this->orders_blocked;
    }

    public function getIsSubscriptionExpiredAttribute(): bool
    {
        return $this->subscription_ends_at && $this->subscription_ends_at->isPast();
    }

    public function getDaysUntilExpirationAttribute(): ?int
    {
        if (!$this->subscription_ends_at) {
            return null;
        }
        return max(0, now()->diffInDays($this->subscription_ends_at, false));
    }

    public function getPublicUrlAttribute(): string
    {
        return route('r.menu', $this->slug);
    }

    /**
     * Check if the restaurant has been verified by admin (RCCM validated)
     */
    public function getIsVerifiedAttribute(): bool
    {
        return !is_null($this->verified_at);
    }

    /**
     * Check if the restaurant has RCCM documents pending verification
     */
    public function getHasPendingVerificationAttribute(): bool
    {
        return !empty($this->rccm) && !empty($this->rccm_document_path) && is_null($this->verified_at);
    }

    // =========================================================================
    // METHODS
    // =========================================================================

    /**
     * Validate the restaurant
     */
    public function validate(): bool
    {
        $this->status = RestaurantStatus::ACTIVE;
        $this->validated_at = now();
        $this->suspended_at = null;
        $this->suspension_reason = null;
        $this->orders_blocked = false;
        
        return $this->save();
    }

    /**
     * Suspend the restaurant
     */
    public function suspend(string $reason = null): bool
    {
        $this->status = RestaurantStatus::SUSPENDED;
        $this->suspended_at = now();
        $this->suspension_reason = $reason;
        $this->orders_blocked = true;
        
        return $this->save();
    }

    /**
     * Mark as expired
     */
    public function markAsExpired(): bool
    {
        $this->status = RestaurantStatus::EXPIRED;
        $this->orders_blocked = true;
        
        return $this->save();
    }

    /**
     * Check if restaurant can create more of a resource type
     */
    public function canCreate(string $type): bool
    {
        if (!$this->currentPlan) {
            return false;
        }

        $counts = [
            'dishes' => $this->dishes()->count(),
            'categories' => $this->categories()->count(),
            'employees' => $this->users()->where('role', 'employee')->count(),
        ];

        $current = $counts[$type] ?? 0;
        
        return !$this->currentPlan->isLimitReached($type, $current);
    }

    /**
     * Get remaining quota for a resource type
     */
    public function getRemainingQuota(string $type): int
    {
        if (!$this->currentPlan) {
            return 0;
        }

        $limits = [
            'dishes' => $this->currentPlan->max_dishes,
            'categories' => $this->currentPlan->max_categories,
            'employees' => $this->currentPlan->max_employees,
        ];

        $counts = [
            'dishes' => $this->dishes()->count(),
            'categories' => $this->categories()->count(),
            'employees' => $this->users()->where('role', 'employee')->count(),
        ];

        $limit = $limits[$type] ?? 0;
        $current = $counts[$type] ?? 0;

        return max(0, $limit - $current);
    }

    /**
     * Check if restaurant has a specific feature
     */
    public function hasFeature(string $feature): bool
    {
        return $this->currentPlan?->hasFeature($feature) ?? false;
    }

    /**
     * Get decrypted Lygos API key
     */
    public function getLygosApiKey(): ?string
    {
        if (!$this->lygos_api_key) {
            return null;
        }
        try {
            return decrypt($this->lygos_api_key);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            // If decryption fails (invalid key or corrupted data), return null
            return null;
        }
    }

    /**
     * Get decrypted Lygos API secret
     */
    public function getLygosApiSecret(): ?string
    {
        if (!$this->lygos_api_secret) {
            return null;
        }
        try {
            return decrypt($this->lygos_api_secret);
        } catch (\Illuminate\Contracts\Encryption\DecryptException $e) {
            // If decryption fails (invalid key or corrupted data), return null
            return null;
        }
    }

    /**
     * Set encrypted Lygos API key
     */
    public function setLygosApiKeyAttribute($value): void
    {
        $this->attributes['lygos_api_key'] = $value ? encrypt($value) : null;
    }

    /**
     * Set encrypted Lygos API secret
     */
    public function setLygosApiSecretAttribute($value): void
    {
        $this->attributes['lygos_api_secret'] = $value ? encrypt($value) : null;
    }

    /**
     * Check if restaurant is open now
     */
    public function isOpenNow(): bool
    {
        if (!$this->opening_hours) {
            return true; // Default to open if no hours set
        }

        $now = now()->setTimezone($this->timezone ?? 'Africa/Abidjan');
        $dayOfWeek = strtolower($now->format('l'));
        
        $todayHours = $this->opening_hours[$dayOfWeek] ?? null;
        
        // Check if restaurant is closed for this day
        // Support both 'is_open' => true and 'closed' => false formats
        if (!$todayHours) {
            return false;
        }
        
        $isOpenToday = $todayHours['is_open'] ?? !($todayHours['closed'] ?? false);
        if (!$isOpenToday) {
            return false;
        }

        $openTime = $todayHours['open'] ?? '00:00';
        $closeTime = $todayHours['close'] ?? '23:59';

        // Normalize time format (ensure HH:ii format)
        $openTime = $this->normalizeTime($openTime);
        $closeTime = $this->normalizeTime($closeTime);
        
        $currentTime = $now->format('H:i');

        // Handle case where close time is after midnight (e.g., 01:00)
        // If close time is less than open time, it means it closes the next day
        if ($closeTime < $openTime) {
            // Restaurant is open if current time >= open time OR current time <= close time
            return $currentTime >= $openTime || $currentTime <= $closeTime;
        }

        // Normal case: open time < close time (same day)
        return $currentTime >= $openTime && $currentTime <= $closeTime;
    }

    /**
     * Normalize time format to HH:ii
     */
    private function normalizeTime(string $time): string
    {
        // Remove any whitespace
        $time = trim($time);
        
        // If time is in format "H:i" (single digit hour), convert to "HH:ii"
        if (preg_match('/^(\d{1,2}):(\d{2})$/', $time, $matches)) {
            $hour = str_pad($matches[1], 2, '0', STR_PAD_LEFT);
            $minute = $matches[2];
            return "{$hour}:{$minute}";
        }
        
        return $time;
    }

    /**
     * Get next opening time
     */
    public function getNextOpeningTime(): ?string
    {
        if (!$this->opening_hours) {
            return null;
        }

        $now = now()->setTimezone($this->timezone ?? 'Africa/Abidjan');
        $days = ['monday', 'tuesday', 'wednesday', 'thursday', 'friday', 'saturday', 'sunday'];
        $currentDayIndex = array_search(strtolower($now->format('l')), $days);
        
        if ($currentDayIndex === false) {
            return null;
        }
        
        // Check next 7 days
        for ($i = 0; $i < 7; $i++) {
            $dayIndex = ($currentDayIndex + $i) % 7;
            $day = $days[$dayIndex];
            $hours = $this->opening_hours[$day] ?? null;
            
            if ($hours && ($hours['is_open'] ?? false)) {
                $openTime = $hours['open'] ?? '00:00';
                
                if ($i === 0) {
                    // Today - check if we're before opening time
                    if ($now->format('H:i') < $openTime) {
                        return 'Aujourd\'hui à ' . $openTime;
                    }
                    // If we're past opening time today, continue to next day
                    continue;
                } else {
                    // Future day
                    $futureDate = $now->copy()->addDays($i);
                    $dayName = match($day) {
                        'monday' => 'Lundi',
                        'tuesday' => 'Mardi',
                        'wednesday' => 'Mercredi',
                        'thursday' => 'Jeudi',
                        'friday' => 'Vendredi',
                        'saturday' => 'Samedi',
                        'sunday' => 'Dimanche',
                        default => $day
                    };
                    
                    if ($i === 1) {
                        return 'Demain (' . $dayName . ') à ' . $openTime;
                    } else {
                        return $dayName . ' ' . $futureDate->format('d/m') . ' à ' . $openTime;
                    }
                }
            }
        }
        
        return null;
    }
}

