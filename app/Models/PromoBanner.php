<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Facades\Storage;

class PromoBanner extends Model
{
    protected $fillable = [
        'restaurant_id',
        'title',
        'subtitle',
        'image_path',
        'link_type',
        'link_value',
        'cta_label',
        'is_active',
        'starts_at',
        'ends_at',
        'sort_order',
        'created_by',
    ];

    protected $casts = [
        'is_active'  => 'boolean',
        'starts_at'  => 'datetime',
        'ends_at'    => 'datetime',
        'sort_order' => 'integer',
    ];

    public function restaurant(): BelongsTo
    {
        return $this->belongsTo(Restaurant::class);
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function getImageUrlAttribute(): string
    {
        return Storage::disk('public')->url($this->image_path);
    }

    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()));
    }

    public function scopeForRestaurant($query, int $restaurantId)
    {
        return $query->where(fn ($q) => $q
            ->whereNull('restaurant_id')
            ->orWhere('restaurant_id', $restaurantId)
        );
    }
}
