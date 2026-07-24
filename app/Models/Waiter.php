<?php
// app/Models/Waiter.php
namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Hash;

class Waiter extends Model
{
    use HasFactory;

    protected $fillable = [
        'restaurant_id', 'space_id', 'name', 'pin_hash', 'is_active', 'failed_attempts', 'locked_until',
    ];

    protected $casts = [
        'is_active'    => 'boolean',
        'locked_until' => 'datetime',
    ];

    protected $hidden = ['pin_hash'];

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function space(): BelongsTo
    {
        return $this->belongsTo(RestaurantSpace::class, 'space_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'waiter_id');
    }

    public function setPin(string $pin): void
    {
        $this->pin_hash = Hash::make($pin);
    }

    public function checkPin(string $pin): bool
    {
        return Hash::check($pin, $this->pin_hash);
    }

    public function isLocked(): bool
    {
        return $this->locked_until !== null && $this->locked_until->isFuture();
    }

    public function recordFailedAttempt(): void
    {
        $this->failed_attempts++;
        if ($this->failed_attempts >= 3) {
            $this->locked_until   = now()->addMinutes(5);
            $this->failed_attempts = 0;
        }
        $this->save();
    }

    public function resetAttempts(): void
    {
        $this->failed_attempts = 0;
        $this->locked_until   = null;
        $this->save();
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
