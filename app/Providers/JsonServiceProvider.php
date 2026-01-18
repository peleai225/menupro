<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Http\JsonResponse;

class JsonServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        // Override JsonResponse to use UTF-8 ignore flag
        $this->app->bind(JsonResponse::class, function ($app, $params) {
            $data = $params['data'] ?? null;
            $status = $params['status'] ?? 200;
            $headers = $params['headers'] ?? [];
            $options = $params['options'] ?? 0;
            
            // Add JSON_INVALID_UTF8_IGNORE flag if available
            if (defined('JSON_INVALID_UTF8_IGNORE')) {
                $options |= JSON_INVALID_UTF8_IGNORE;
            }
            
            return new JsonResponse($data, $status, $headers, $options);
        });
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Set default JSON encoding options
        if (function_exists('json_encode')) {
            // This will be used by Laravel's JsonResponse
            config(['app.json_options' => JSON_UNESCAPED_UNICODE | JSON_INVALID_UTF8_IGNORE]);
        }
    }
}

