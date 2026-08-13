<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\PromoBanner;
use App\Models\Restaurant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PromoBannerController extends Controller
{
    /**
     * Bannières globales de la plateforme (sans restaurant_id).
     * GET /api/v1/banners — public, pas d'auth requise.
     */
    public function indexGlobal(): JsonResponse
    {
        $banners = PromoBanner::active()
            ->whereNull('restaurant_id')
            ->orderBy('sort_order')
            ->get()
            ->map(fn (PromoBanner $b) => [
                'id'          => $b->id,
                'title'       => $b->title,
                'subtitle'    => $b->subtitle,
                'image_url'   => $b->image_path ? $b->image_url : null,
                'color_start' => $b->color_start ?? '#F97316',
                'color_end'   => $b->color_end   ?? '#EA580C',
                'action_url'  => $b->link_value,
                'is_active'   => $b->is_active,
                'sort_order'  => $b->sort_order,
            ]);

        return response()->json(['data' => $banners]);
    }

    public function index(Request $request, int $restaurantId): JsonResponse
    {
        if (!Restaurant::where('id', $restaurantId)->exists()) {
            return response()->json(['message' => 'Restaurant introuvable.'], 404);
        }

        $banners = PromoBanner::active()
            ->forRestaurant($restaurantId)
            ->orderBy('sort_order')
            ->get()
            ->map(fn (PromoBanner $b) => [
                'id'         => $b->id,
                'title'      => $b->title,
                'subtitle'   => $b->subtitle,
                'image_url'  => $b->image_url,
                'link_type'  => $b->link_type,
                'link_value' => $b->link_value,
                'cta_label'  => $b->cta_label,
            ]);

        return response()->json(['data' => $banners]);
    }
}
