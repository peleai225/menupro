<?php

namespace App\Models;

use App\Enums\Crm\AgentStatus;
use App\Enums\UserRole;
use App\Models\Crm\CommercialProfile;
use App\Models\Crm\Commission;
use App\Models\Crm\Installation;
use App\Models\Crm\Lead;
use App\Models\Crm\PerformanceSnapshot;
use App\Models\Crm\Team;
use App\Models\Crm\TechnicianProfile;
use App\Models\Crm\UserGrade;
use App\Models\Crm\Wallet;
use App\Models\Crm\Withdrawal;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, HasApiTokens;

    protected $fillable = [
        'name',
        'email',
        'phone',
        'password',
        'avatar_path',
        'is_active',
        'agent_status',
        'last_login_at',
        'welcome_token',
        'welcome_token_expires_at',
        'city',
    ];

    /**
     * Champs protégés contre le mass-assignment.
     * `role` et `restaurant_id` doivent être assignés explicitement ($user->role = ...).
     */
    protected $guarded = ['role', 'restaurant_id'];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'last_login_at' => 'datetime',
            'welcome_token_expires_at' => 'datetime',
            'password' => 'hashed',
            'role' => UserRole::class,
            'agent_status' => AgentStatus::class,
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

    public function customer(): HasOne
    {
        return $this->hasOne(Customer::class);
    }

    public function deliveryDriver(): HasOne
    {
        return $this->hasOne(DeliveryDriver::class);
    }

    // =========================================================================
    // CRM RELATIONSHIPS
    // =========================================================================

    public function commercialProfile(): HasOne
    {
        return $this->hasOne(CommercialProfile::class);
    }

    public function technicianProfile(): HasOne
    {
        return $this->hasOne(TechnicianProfile::class);
    }

    public function crmWallet(): HasOne
    {
        return $this->hasOne(Wallet::class);
    }

    public function crmGrade(): HasOne
    {
        return $this->hasOne(UserGrade::class);
    }

    public function crmLeadsAssigned(): HasMany
    {
        return $this->hasMany(Lead::class, 'assigned_to');
    }

    public function crmInstallations(): HasMany
    {
        return $this->hasMany(Installation::class, 'technician_id');
    }

    public function crmCommissions(): HasMany
    {
        return $this->hasMany(Commission::class);
    }

    public function crmWithdrawals(): HasMany
    {
        return $this->hasMany(Withdrawal::class);
    }

    public function crmPerformanceSnapshots(): HasMany
    {
        return $this->hasMany(PerformanceSnapshot::class);
    }

    public function crmTeams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'crm_team_members')
            ->withPivot('role_in_team', 'joined_at');
    }

    public function ledTeams(): HasMany
    {
        return $this->hasMany(Team::class, 'leader_id');
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

    public function isCustomer(): bool
    {
        return $this->role === UserRole::CUSTOMER;
    }

    public function isDeliveryDriver(): bool
    {
        return $this->role === UserRole::DELIVERY_DRIVER;
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
            UserRole::COMMERCIAL, UserRole::TECHNICIAN, UserRole::TEAM_LEADER => 'crm.dashboard',
            default => 'home',
        };
    }

    public function isCrmUser(): bool
    {
        return in_array($this->role, [
            UserRole::COMMERCIAL,
            UserRole::TECHNICIAN,
            UserRole::TEAM_LEADER,
        ]);
    }

    public function isCommercial(): bool
    {
        return $this->role === UserRole::COMMERCIAL;
    }

    public function isTechnician(): bool
    {
        return $this->role === UserRole::TECHNICIAN;
    }

    public function isTeamLeader(): bool
    {
        return $this->role === UserRole::TEAM_LEADER;
    }
}
