<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Announcement extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'content',
        'type', // info, warning, success, danger
        'target', // all, active, trial, expired
        'is_active',
        'starts_at',
        'ends_at',
        'created_by',
        'is_dismissible',
        'show_on_dashboard',
        'send_email',
        'email_sent_at',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_dismissible' => 'boolean',
        'show_on_dashboard' => 'boolean',
        'send_email' => 'boolean',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'email_sent_at' => 'datetime',
    ];

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function dismissals()
    {
        return $this->hasMany(AnnouncementDismissal::class);
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('starts_at')->orWhere('starts_at', '<=', now());
            })
            ->where(function ($q) {
                $q->whereNull('ends_at')->orWhere('ends_at', '>=', now());
            });
    }

    public function scopeForDashboard($query)
    {
        return $query->where('show_on_dashboard', true);
    }

    // =========================================================================
    // HELPERS
    // =========================================================================

    public function getTypeColorAttribute(): string
    {
        return match($this->type) {
            'warning' => 'yellow',
            'success' => 'green',
            'danger' => 'red',
            default => 'blue',
        };
    }

    public function getTypeIconAttribute(): string
    {
        return match($this->type) {
            'warning' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
            'success' => 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z',
            'danger' => 'M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
            default => 'M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        };
    }

    public function isVisibleFor(Restaurant $restaurant): bool
    {
        if (!$this->is_active) {
            return false;
        }

        if ($this->starts_at && $this->starts_at->isFuture()) {
            return false;
        }

        if ($this->ends_at && $this->ends_at->isPast()) {
            return false;
        }

        return match($this->target) {
            'all' => true,
            'active' => $restaurant->status->value === 'active',
            'trial' => $restaurant->isOnTrial(),
            'expired' => $restaurant->status->value === 'expired',
            default => true,
        };
    }

    public function isDismissedBy(User $user): bool
    {
        return $this->dismissals()->where('user_id', $user->id)->exists();
    }
}
