<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class RestaurantSpace extends Model
{
    use HasFactory;
    protected $fillable = [
        'restaurant_id', 'name', 'color', 'description', 'is_active', 'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function dishes(): HasMany
    {
        return $this->hasMany(Dish::class, 'space_id');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class, 'space_id');
    }

    public function ingredients(): HasMany
    {
        return $this->hasMany(Ingredient::class, 'space_id');
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }
}
