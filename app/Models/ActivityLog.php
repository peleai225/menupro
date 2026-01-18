<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

class ActivityLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'restaurant_id',
        'user_id',
        'action',
        'subject_type',
        'subject_id',
        'description',
        'properties',
        'ip_address',
        'user_agent',
    ];

    protected $casts = [
        'properties' => 'array',
    ];

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function subject(): MorphTo
    {
        return $this->morphTo();
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    public function scopeForRestaurant($query, int $restaurantId)
    {
        return $query->where('restaurant_id', $restaurantId);
    }

    public function scopeForUser($query, int $userId)
    {
        return $query->where('user_id', $userId);
    }

    public function scopeByAction($query, string $action)
    {
        return $query->where('action', $action);
    }

    public function scopeForSubject($query, string $type, int $id)
    {
        return $query->where('subject_type', $type)->where('subject_id', $id);
    }

    public function scopeRecent($query, int $limit = 50)
    {
        return $query->latest()->limit($limit);
    }

    // =========================================================================
    // ACCESSORS
    // =========================================================================

    public function getSubjectNameAttribute(): string
    {
        if (!$this->subject) {
            return 'Élément supprimé';
        }

        return $this->subject->name ?? $this->subject->title ?? "#{$this->subject_id}";
    }

    public function getActionLabelAttribute(): string
    {
        return match ($this->action) {
            'created' => 'Création',
            'updated' => 'Modification',
            'deleted' => 'Suppression',
            'restored' => 'Restauration',
            'login' => 'Connexion',
            'logout' => 'Déconnexion',
            'validated' => 'Validation',
            'suspended' => 'Suspension',
            'activated' => 'Activation',
            default => ucfirst($this->action),
        };
    }

    public function getActionColorAttribute(): string
    {
        return match ($this->action) {
            'created' => 'success',
            'updated' => 'info',
            'deleted' => 'error',
            'restored' => 'warning',
            'login' => 'primary',
            'logout' => 'neutral',
            'validated' => 'success',
            'suspended' => 'error',
            'activated' => 'success',
            default => 'neutral',
        };
    }

    public function getOldValuesAttribute(): array
    {
        return $this->properties['old'] ?? [];
    }

    public function getNewValuesAttribute(): array
    {
        return $this->properties['new'] ?? [];
    }

    public function getChangesAttribute(): array
    {
        $changes = [];
        $old = $this->old_values;
        $new = $this->new_values;

        foreach ($new as $key => $value) {
            if (!isset($old[$key]) || $old[$key] !== $value) {
                $changes[$key] = [
                    'old' => $old[$key] ?? null,
                    'new' => $value,
                ];
            }
        }

        return $changes;
    }

    // =========================================================================
    // STATIC METHODS
    // =========================================================================

    /**
     * Log an activity
     */
    public static function log(
        string $action,
        ?Model $subject = null,
        ?string $description = null,
        array $properties = [],
        ?int $restaurantId = null
    ): static {
        return static::create([
            'restaurant_id' => $restaurantId ?? $subject?->restaurant_id ?? auth()->user()?->restaurant_id,
            'user_id' => auth()->id(),
            'action' => $action,
            'subject_type' => $subject ? get_class($subject) : null,
            'subject_id' => $subject?->id,
            'description' => $description,
            'properties' => $properties,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
        ]);
    }

    /**
     * Log a model change
     */
    public static function logChange(Model $model, string $action, array $oldValues = []): static
    {
        $properties = [];
        
        if ($action === 'updated' && !empty($oldValues)) {
            $properties['old'] = $oldValues;
            $properties['new'] = $model->getChanges();
        } elseif ($action === 'created') {
            $properties['new'] = $model->toArray();
        } elseif ($action === 'deleted') {
            $properties['old'] = $model->toArray();
        }

        return static::log($action, $model, null, $properties);
    }
}

