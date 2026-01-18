<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Reservation extends Model
{
    use HasFactory;

    protected $fillable = [
        'restaurant_id',
        'customer_name',
        'customer_email',
        'customer_phone',
        'number_of_guests',
        'reservation_date',
        'special_requests',
        'status',
        'notes',
    ];

    protected $casts = [
        'reservation_date' => 'datetime',
    ];

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    public function scopePending($query)
    {
        return $query->where('status', 'pending');
    }

    public function scopeConfirmed($query)
    {
        return $query->where('status', 'confirmed');
    }

    public function scopeCancelled($query)
    {
        return $query->where('status', 'cancelled');
    }

    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeUpcoming($query)
    {
        return $query->where('reservation_date', '>=', now());
    }

    public function scopeForRestaurant($query, int $restaurantId)
    {
        return $query->where('restaurant_id', $restaurantId);
    }

    // =========================================================================
    // ACCESSORS
    // =========================================================================

    public function getIsPendingAttribute(): bool
    {
        return $this->status === 'pending';
    }

    public function getIsConfirmedAttribute(): bool
    {
        return $this->status === 'confirmed';
    }

    public function getIsCancelledAttribute(): bool
    {
        return $this->status === 'cancelled';
    }

    public function getIsCompletedAttribute(): bool
    {
        return $this->status === 'completed';
    }

    public function getIsUpcomingAttribute(): bool
    {
        return $this->reservation_date >= now();
    }

    // =========================================================================
    // METHODS
    // =========================================================================

    public function confirm(): void
    {
        $this->update(['status' => 'confirmed']);
    }

    public function cancel(string $reason = null): void
    {
        $this->update([
            'status' => 'cancelled',
            'notes' => $reason ? ($this->notes ? $this->notes . "\n\nAnnulé: " . $reason : "Annulé: " . $reason) : $this->notes,
        ]);
    }

    public function complete(): void
    {
        $this->update(['status' => 'completed']);
    }
}
