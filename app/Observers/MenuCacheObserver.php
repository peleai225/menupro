<?php

namespace App\Observers;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;

class MenuCacheObserver
{
    public function saved(Model $model): void
    {
        $this->clearMenuCache($model);
    }

    public function deleted(Model $model): void
    {
        $this->clearMenuCache($model);
    }

    protected function clearMenuCache(Model $model): void
    {
        $restaurantId = $model->restaurant_id;
        if (!$restaurantId) {
            return;
        }

        $restaurant = $model->restaurant;
        $slug = $restaurant?->slug;

        Cache::forget("menu.restaurant.{$slug}");
        Cache::forget("menu.{$restaurantId}.featured");
        Cache::forget("menu.{$restaurantId}.new_dishes");

        if (method_exists($model, 'category_id') || isset($model->category_id)) {
            Cache::forget("menu.{$restaurantId}.category.{$model->category_id}");
        }
    }
}
