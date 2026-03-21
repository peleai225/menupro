<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Register middleware aliases
        $middleware->alias([
            'set.restaurant.scope' => \App\Http\Middleware\SetRestaurantScope::class,
            'restaurant.active' => \App\Http\Middleware\EnsureRestaurantActive::class,
            'restaurant.admin' => \App\Http\Middleware\EnsureRestaurantAdmin::class,
            'has.restaurant' => \App\Http\Middleware\EnsureUserHasRestaurant::class,
            'super.admin' => \App\Http\Middleware\EnsureSuperAdmin::class,
            'commando.agent' => \App\Http\Middleware\EnsureCommandoAgent::class,
            'subscription' => \App\Http\Middleware\CheckSubscription::class,
            'track.login' => \App\Http\Middleware\TrackLastLogin::class,
        ]);

        // Append to web middleware group
        $middleware->web(append: [
            \App\Http\Middleware\TrackLastLogin::class,
            \App\Http\Middleware\SecurityHeaders::class,
            // \App\Http\Middleware\CleanJsonResponse::class, // Temporarily disabled - causes issues
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Handle UTF-8 encoding errors gracefully
        $exceptions->render(function (\InvalidArgumentException $e, $request) {
            if (str_contains($e->getMessage(), 'UTF-8') || str_contains($e->getMessage(), 'Malformed')) {
                \Log::error('UTF-8 encoding error', [
                    'message' => $e->getMessage(),
                    'path' => $request->path(),
                    'trace' => $e->getTraceAsString()
                ]);
                
                // If it's a Livewire request, return a proper JSON response
                if ($request->header('X-Livewire')) {
                    return response()->json([
                        'error' => 'Une erreur de codage est survenue. Veuillez réessayer.',
                        'skip' => true
                    ], 500);
                }
                
                // For regular requests, return a view
                return response()->view('errors.utf8', [], 500);
            }
        });
    })->create();
