<?php

namespace App\Models;

use App\Models\Traits\BelongsToRestaurant;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DishOptionGroup extends Model
{
    use HasFactory, BelongsToRestaurant;

    protected $fillable = [
        'restaurant_id',
        'name',
        'is_required',
        'min_selections',
        'max_selections',
        'sort_order',
        'is_active',
    ];

    protected $casts = [
        'is_required' => 'boolean',
        'min_selections' => 'integer',
        'max_selections' => 'integer',
        'sort_order' => 'integer',
        'is_active' => 'boolean',
    ];

    // =========================================================================
    // RELATIONSHIPS
    // =========================================================================

    public function options(): HasMany
    {
        return $this->hasMany(DishOption::class, 'option_group_id');
    }

    public function activeOptions(): HasMany
    {
        return $this->hasMany(DishOption::class, 'option_group_id')
            ->where('is_active', true)
            ->orderBy('sort_order');
    }

    public function dishes(): BelongsToMany
    {
        return $this->belongsToMany(Dish::class, 'dish_option_group', 'option_group_id', 'dish_id');
    }

    // =========================================================================
    // SCOPES
    // =========================================================================

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeOrdered($query)
    {
        return $query->orderBy('sort_order')->orderBy('name');
    }

    // =========================================================================
    // METHODS
    // =========================================================================

    /**
     * Validate selection count
     */
    public function validateSelection(int $count): bool
    {
        if ($this->is_required && $count < $this->min_selections) {
            return false;
        }
        
        if ($count > $this->max_selections) {
            return false;
        }
        
        return true;
    }

    /**
     * Get validation message
     */
    public function getValidationMessage(): string
    {
        if ($this->is_required) {
            if ($this->min_selections === $this->max_selections) {
                return "Sélectionnez exactement {$this->min_selections} option(s)";
            }
            return "Sélectionnez entre {$this->min_selections} et {$this->max_selections} option(s)";
        }
        
        return "Sélectionnez jusqu'à {$this->max_selections} option(s) (facultatif)";
    }
}

