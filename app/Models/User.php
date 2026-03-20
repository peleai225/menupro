<?php

namespace App\Models;

use App\Enums\UserRole;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'role',
        'restaurant_id',
        'avatar_path',
        'is_active',
        'last_login_at',
        'welcome_token',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'is_active' => 'boolean',
        ];
    }

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    public function stockMovements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function commandoAgent(): HasOne
    {
        return $this->hasOne(CommandoAgent::class);
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeSuperAdmins($query)
    {
        return $query->where('role', UserRole::SUPER_ADMIN);
    }

    public function scopeRestaurantAdmins($query)
    {
        return $query->where('role', UserRole::RESTAURANT_ADMIN);
    }

    public function scopeEmployees($query)
    {
        return $query->where('role', UserRole::EMPLOYEE);
    }

    public function scopeCommandoAgents($query)
    {
        return $query->where('role', UserRole::COMMANDO_AGENT);
    }

    public function scopeForRestaurant($query, int $restaurantId)
    {
        return $query->where('restaurant_id', $restaurantId);
    }

    // =========================================================================
    // ACCESSORS
    // =========================================================================

    public function getAvatarUrlAttribute(): string
    {
        if ($this->avatar_path) {
            return Storage::url($this->avatar_path);
        }
        
        // Return UI Avatars URL as fallback
        $name = urlencode($this->name);
        return "https://ui-avatars.com/api/?name={$name}&background=f97316&color=fff";
    }

    public function getInitialsAttribute(): string
    {
        $words = explode(' ', $this->name);
        $initials = '';
        
        foreach (array_slice($words, 0, 2) as $word) {
            $initials .= strtoupper(substr($word, 0, 1));
        }
        
        return $initials ?: 'U';
    }

    public function getFirstNameAttribute(): string
    {
        return explode(' ', $this->name)[0];
    }

    // =========================================================================
    // AUTHORIZATION HELPERS
    // =========================================================================

    public function isSuperAdmin(): bool
    {
        return $this->role === UserRole::SUPER_ADMIN;
    }

    public function isRestaurantAdmin(): bool
    {
        return $this->role === UserRole::RESTAURANT_ADMIN;
    }

    public function isEmployee(): bool
    {
        return $this->role === UserRole::EMPLOYEE;
    }

    public function isCommandoAgent(): bool
    {
        return $this->role === UserRole::COMMANDO_AGENT;
    }

    public function belongsToRestaurant(int $restaurantId): bool
    {
        // Cast to int: en production, MySQL/PDO peut renvoyer restaurant_id en string
        return $this->restaurant_id !== null && (int) $this->restaurant_id === (int) $restaurantId;
    }

    public function canAccessRestaurant(int $restaurantId): bool
    {
        if ($this->isSuperAdmin()) {
            return true;
        }
        
        return $this->belongsToRestaurant($restaurantId);
    }

    public function canManageRestaurant(): bool
    {
        return $this->isSuperAdmin() || $this->isRestaurantAdmin();
    }

    public function canManageOrders(): bool
    {
        return $this->isSuperAdmin() || $this->isRestaurantAdmin() || $this->isEmployee();
    }

    // =========================================================================
    // METHODS
    // =========================================================================

    /**
     * Update last login timestamp
     */
    public function updateLastLogin(): void
    {
        $this->update(['last_login_at' => now()]);
    }

    /**
     * Get dashboard route based on role
     */
    public function getDashboardRoute(): string
    {
        return match ($this->role) {
            UserRole::SUPER_ADMIN => 'super-admin.dashboard',
            UserRole::RESTAURANT_ADMIN, UserRole::EMPLOYEE => 'restaurant.dashboard',
            UserRole::COMMANDO_AGENT => 'commando.dashboard',
        };
    }
}
