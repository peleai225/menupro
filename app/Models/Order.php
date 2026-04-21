<?php

namespace App\Models;

use App\Enums\OrderStatus;
use App\Enums\OrderType;
use App\Enums\PaymentStatus;
use App\Models\Traits\BelongsToRestaurant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Str;

class Order extends Model
{
    use HasFactory, BelongsToRestaurant;

    protected $fillable = [
        'restaurant_id',
        'reference',
        'tracking_token',
        'customer_name',
        'customer_email',
        'customer_phone',
        'type',
        'status',
        'subtotal',
        'delivery_fee',
        'discount_amount',
        'tax_amount',
        'service_fee',
        'total',
        'delivery_address',
        'delivery_city',
        'delivery_latitude',
        'delivery_longitude',
        'delivery_instructions',
        'scheduled_at',
        'table_number',
        'payment_status',
        'payment_method',
        'payment_reference',
        'payment_metadata',
        'paid_at',
        'customer_notes',
        'internal_notes',
        'estimated_prep_time',
        'confirmed_at',
        'preparing_at',
        'ready_at',
        'completed_at',
        'cancelled_at',
        'cancellation_reason',
    ];

    protected $casts = [
        'type' => OrderType::class,
        'status' => OrderStatus::class,
        'payment_status' => PaymentStatus::class,
        'subtotal' => 'integer',
        'delivery_fee' => 'integer',
        'discount_amount' => 'integer',
        'tax_amount' => 'integer',
        'service_fee' => 'integer',
        'total' => 'integer',
        'payment_metadata' => 'array',
        'scheduled_at' => 'datetime',
        'paid_at' => 'datetime',
        'confirmed_at' => 'datetime',
        'preparing_at' => 'datetime',
        'ready_at' => 'datetime',
        'completed_at' => 'datetime',
        'cancelled_at' => 'datetime',
        'estimated_prep_time' => 'integer',
    ];

    // =========================================================================
    // BOOT
    // =========================================================================

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($order) {
            if (empty($order->reference)) {
                $order->reference = static::generateReference();
            }
            if (empty($order->tracking_token)) {
                $order->tracking_token = static::generateTrackingToken();
            }
        });
    }

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function review(): HasOne
    {
        return $this->hasOne(Review::class);
    }

    public function commissionLog(): HasOne
    {
        return $this->hasOne(CommissionLog::class);
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    public function scopeStatus($query, OrderStatus $status)
    {
        return $query->where('status', $status);
    }

    public function scopeActive($query)
    {
        return $query->whereIn('status', [
            OrderStatus::PAID,
            OrderStatus::CONFIRMED,
            OrderStatus::PREPARING,
            OrderStatus::READY,
            OrderStatus::DELIVERING,
        ]);
    }

    public function scopePending($query)
    {
        return $query->whereIn('status', [
            OrderStatus::DRAFT,
            OrderStatus::PENDING_PAYMENT,
        ]);
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', OrderStatus::COMPLETED);
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', OrderStatus::CANCELLED);
    }

    public function scopeToday($query)
    {
        return $query->whereDate('created_at', today());
    }

    public function scopeThisWeek($query)
    {
        return $query->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()]);
    }

    public function scopeThisMonth($query)
    {
        return $query->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);
    }

    public function scopePaid($query)
    {
        return $query->where('payment_status', PaymentStatus::COMPLETED);
    }

    public function scopeByType($query, OrderType $type)
    {
        return $query->where('type', $type);
    }

    public function scopeSearch($query, string $term)
    {
        return $query->where(function ($q) use ($term) {
            $q->where('reference', 'like', "%{$term}%")
              ->orWhere('customer_name', 'like', "%{$term}%")
              ->orWhere('customer_email', 'like', "%{$term}%")
              ->orWhere('customer_phone', 'like', "%{$term}%");
        });
    }

    // =========================================================================
    // ACCESSORS
    // =========================================================================

    public function getFormattedTotalAttribute(): string
    {
        return number_format($this->total, 0, ',', ' ') . ' FCFA';
    }

    public function getFormattedSubtotalAttribute(): string
    {
        return number_format($this->subtotal, 0, ',', ' ') . ' FCFA';
    }

    public function getItemsCountAttribute(): int
    {
        return $this->items->sum('quantity');
    }

    public function getIsPaidAttribute(): bool
    {
        return $this->payment_status === PaymentStatus::COMPLETED;
    }

    public function getCanBeCancelledAttribute(): bool
    {
        return $this->status->canBeCancelled();
    }

    public function getCanBeEditedAttribute(): bool
    {
        return $this->status->canBeEdited();
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->status->isActive();
    }

    public function getIsFinalAttribute(): bool
    {
        return $this->status->isFinal();
    }

    public function getEstimatedReadyAtAttribute(): ?\Carbon\Carbon
    {
        if (!$this->estimated_prep_time || !$this->confirmed_at) {
            return null;
        }
        return $this->confirmed_at->addMinutes($this->estimated_prep_time);
    }

    // =========================================================================
    // METHODS
    // =========================================================================

    /**
     * Generate unique reference
     */
    public static function generateReference(): string
    {
        $prefix = 'CMD';
        $date = now()->format('ymd');
        $random = strtoupper(Str::random(4));
        
        return "{$prefix}-{$date}-{$random}";
    }

    /**
     * Generate unique tracking token (secure, unguessable)
     */
    public static function generateTrackingToken(): string
    {
        return Str::random(32);
    }

    /**
     * Transition to new status
     */
    public function transitionTo(OrderStatus $newStatus): bool
    {
        if (!$this->status->canTransitionTo($newStatus)) {
            return false;
        }

        $this->status = $newStatus;
        
        // Set timestamps
        match ($newStatus) {
            OrderStatus::CONFIRMED => $this->confirmed_at = now(),
            OrderStatus::PREPARING => $this->preparing_at = now(),
            OrderStatus::READY => $this->ready_at = now(),
            OrderStatus::COMPLETED => $this->completed_at = now(),
            OrderStatus::CANCELLED => $this->cancelled_at = now(),
            default => null,
        };

        return $this->save();
    }

    /**
     * Mark as paid
     */
    public function markAsPaid(array $paymentData = []): bool
    {
        $this->payment_status = PaymentStatus::COMPLETED;
        $this->payment_reference = $paymentData['reference'] ?? null;
        $this->payment_method = $paymentData['method'] ?? null;
        $this->payment_metadata = $paymentData['metadata'] ?? null;
        $this->paid_at = now();
        $this->status = OrderStatus::PAID;
        
        return $this->save();
    }

    /**
     * Mark order as refunded
     */
    public function markAsRefunded(array $refundData = []): bool
    {
        $this->payment_status = PaymentStatus::REFUNDED;
        $this->payment_metadata = array_merge(
            $this->payment_metadata ?? [],
            [
                'refunded_at' => now(),
                'refund_data' => $refundData,
            ]
        );
        
        return $this->save();
    }

    /**
     * Cancel order
     */
    public function cancel(string $reason = null): bool
    {
        if (!$this->can_be_cancelled) {
            return false;
        }

        $this->status = OrderStatus::CANCELLED;
        $this->cancelled_at = now();
        $this->cancellation_reason = $reason;
        
        return $this->save();
    }

    /**
     * Calculate totals from items
     */
    public function calculateTotals(): void
    {
        $this->subtotal = $this->items->sum('total_price');
        
        // Calculate tax
        $this->tax_amount = $this->calculateTax();
        
        // Calculate service fee
        $this->service_fee = $this->calculateServiceFee();
        
        $this->total = $this->subtotal 
            + $this->delivery_fee 
            - $this->discount_amount 
            + $this->tax_amount 
            + $this->service_fee;
    }

    /**
     * Calculate tax amount
     */
    public function calculateTax(): int
    {
        $restaurant = $this->restaurant;
        
        if (!$restaurant || !$restaurant->tax_rate || $restaurant->tax_rate <= 0) {
            return 0;
        }

        $baseAmount = $this->subtotal + $this->delivery_fee - $this->discount_amount;
        
        // If tax is included, extract it from the base amount
        if ($restaurant->tax_included) {
            $taxAmount = (int) round($baseAmount * ($restaurant->tax_rate / (100 + $restaurant->tax_rate)));
        } else {
            // Tax is added on top
            $taxAmount = (int) round($baseAmount * ($restaurant->tax_rate / 100));
        }
        
        return $taxAmount;
    }

    /**
     * Calculate service fee
     */
    public function calculateServiceFee(): int
    {
        $restaurant = $this->restaurant;
        
        if (!$restaurant || !$restaurant->service_fee_enabled) {
            return 0;
        }

        $baseAmount = $this->subtotal + $this->delivery_fee - $this->discount_amount;
        
        $feeAmount = 0;
        
        // Percentage fee
        if ($restaurant->service_fee_rate > 0) {
            $feeAmount += (int) round($baseAmount * ($restaurant->service_fee_rate / 100));
        }
        
        // Fixed fee
        if ($restaurant->service_fee_fixed > 0) {
            $feeAmount += $restaurant->service_fee_fixed;
        }
        
        return $feeAmount;
    }

    /**
     * Add item to order
     */
    public function addItem(Dish $dish, int $quantity = 1, array $options = [], string $instructions = null): OrderItem
    {
        $optionsPrice = 0;
        foreach ($options as $option) {
            $optionsPrice += $option['price_adjustment'] ?? 0;
        }

        $unitPrice = $dish->price + $optionsPrice;
        $totalPrice = $unitPrice * $quantity;

        $item = $this->items()->create([
            'dish_id' => $dish->id,
            'dish_name' => $dish->name,
            'unit_price' => $unitPrice,
            'quantity' => $quantity,
            'total_price' => $totalPrice,
            'selected_options' => $options,
            'options_price' => $optionsPrice,
            'special_instructions' => $instructions,
        ]);

        $this->calculateTotals();
        $this->save();

        return $item;
    }

    /**
     * Remove item from order
     */
    public function removeItem(OrderItem $item): bool
    {
        if (!$this->status->canBeModifiedByManager()) {
            return false;
        }

        $item->delete();
        $this->calculateTotals();
        $this->save();

        return true;
    }

    /**
     * Update item quantity in order
     */
    public function updateItem(OrderItem $item, int $quantity): bool
    {
        if (!$this->status->canBeModifiedByManager()) {
            return false;
        }

        if ($quantity <= 0) {
            return $this->removeItem($item);
        }

        return $item->updateQuantity($quantity);
    }

    /**
     * Check if order can be modified by manager
     */
    public function getCanBeModifiedByManagerAttribute(): bool
    {
        return $this->status->canBeModifiedByManager();
    }

    /**
     * Check if order can be modified by customer
     * Customers can modify until 5 minutes after payment OR before confirmation
     */
    public function canBeModifiedByCustomer(): bool
    {
        if (!$this->status->canBeModifiedByCustomer()) {
            return false;
        }

        // If paid, check if less than 5 minutes have passed
        if ($this->status === OrderStatus::PAID && $this->paid_at) {
            $minutesSincePayment = $this->paid_at->diffInMinutes(now());
            return $minutesSincePayment <= 5;
        }

        return true;
    }

    /**
     * Get remaining time for customer modification (in minutes)
     */
    public function getRemainingModificationTimeAttribute(): ?int
    {
        if ($this->status === OrderStatus::PAID && $this->paid_at) {
            $minutesSincePayment = $this->paid_at->diffInMinutes(now());
            $remaining = 5 - $minutesSincePayment;
            return max(0, $remaining);
        }

        return null;
    }
}

