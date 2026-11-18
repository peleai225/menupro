<?php

use App\Models\Restaurant;
use App\Support\BusinessType;

if (!function_exists('business_label')) {
    /**
     * Vocabulaire selon le type de commerce : business_label('dishes') → « Plats » ou « Articles ».
     * Clés : config('business_types.vocabulary'). Sans commerce : celui de l'utilisateur connecté.
     */
    function business_label(string $key, Restaurant|string|null $business = null): string
    {
        return BusinessType::label($key, $business);
    }
}
