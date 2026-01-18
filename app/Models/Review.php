<?php

namespace App\Models;

use App\Models\Traits\BelongsToRestaurant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Review extends Model
{
    use HasFactory, BelongsToRestaurant;

    protected $fillable = [
        'restaurant_id',
        'order_id',
        'customer_name',
        'customer_email',
        'rating',
        'comment',
        'is_approved',
        'is_visible',
        'response',
        'responded_at',
        'metadata',
    ];

    protected $casts = [
        'rating' => 'integer',
        'is_approved' => 'boolean',
        'is_visible' => 'boolean',
        'responded_at' => 'datetime',
        'metadata' => 'array',
    ];

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    public function scopeApproved($query)
    {
        return $query->where('is_approved', true);
    }

    public function scopeVisible($query)
    {
        return $query->where('is_visible', true);
    }

    public function scopePending($query)
    {
        return $query->where('is_approved', false);
    }

    public function scopeByRating($query, int $rating)
    {
        return $query->where('rating', $rating);
    }

    // =========================================================================
    // METHODS
    // =========================================================================

    public function approve(): void
    {
        $this->update([
            'is_approved' => true,
            'is_visible' => true,
        ]);
    }

    public function reject(): void
    {
        $this->update([
            'is_approved' => false,
            'is_visible' => false,
        ]);
    }

    public function respond(string $response): void
    {
        $this->update([
            'response' => $response,
            'responded_at' => now(),
        ]);
    }

    public function getStarsAttribute(): string
    {
        return str_repeat('★', $this->rating) . str_repeat('☆', 5 - $this->rating);
    }
}

