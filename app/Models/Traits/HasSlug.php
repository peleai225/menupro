<?php

namespace App\Models\Traits;

use Illuminate\Support\Str;

trait HasSlug
{
    /**
     * Boot the trait
     */
    protected static function bootHasSlug(): void
    {
        static::creating(function ($model) {
            if (empty($model->slug)) {
                $model->slug = $model->generateUniqueSlug();
            }
        });

        static::updating(function ($model) {
            // Regenerate slug if name changed and slug wasn't manually set
            $slugSource = $model->getSlugSource();
            if ($model->isDirty($slugSource) && !$model->isDirty('slug')) {
                $model->slug = $model->generateUniqueSlug();
            }
        });
    }

    /**
     * Get the source field for the slug
     */
    protected function getSlugSource(): string
    {
        return property_exists($this, 'slugSource') ? $this->slugSource : 'name';
    }

    /**
     * Generate a unique slug
     */
    protected function generateUniqueSlug(): string
    {
        $source = $this->getSlugSource();
        $slug = Str::slug($this->{$source});
        $originalSlug = $slug;
        $count = 1;

        // Check for uniqueness within scope
        while ($this->slugExists($slug)) {
            $slug = $originalSlug . '-' . $count;
            $count++;
        }

        return $slug;
    }

    /**
     * Check if slug exists (scoped by restaurant if applicable)
     */
    protected function slugExists(string $slug): bool
    {
        $query = static::withoutGlobalScopes()->where('slug', $slug);

        // If model belongs to restaurant, scope by restaurant
        if (method_exists($this, 'restaurant') && $this->restaurant_id) {
            $query->where('restaurant_id', $this->restaurant_id);
        }

        // Exclude current model if updating
        if ($this->exists) {
            $query->where('id', '!=', $this->id);
        }

        return $query->exists();
    }

    /**
     * Get the route key name for model binding
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}

