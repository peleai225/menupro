<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\PromoBanner;
use App\Models\Restaurant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PromoBannerController extends Controller
{
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
