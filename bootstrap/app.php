<?php

use App\Providers\RateLimitServiceProvider;
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
        then: function () {
            Route::middleware('web')->group(base_path('routes/crm.php'));
        },
    )
    ->withProviders([
        RateLimitServiceProvider::class,
    ])
    ->withMiddleware(function (Middleware $middleware): void {
        // Register middleware aliases
        $middleware->alias([
            'public.cache' => \App\Http\Middleware\SetPublicCache::class,
            'set.restaurant.scope' => \App\Http\Middleware\SetRestaurantScope::class,
            'restaurant.active' => \App\Http\Middleware\EnsureRestaurantActive::class,
            'restaurant.admin' => \App\Http\Middleware\EnsureRestaurantAdmin::class,
            'has.restaurant' => \App\Http\Middleware\EnsureUserHasRestaurant::class,
            'super.admin' => \App\Http\Middleware\EnsureSuperAdmin::class,
            'commando.agent' => \App\Http\Middleware\EnsureCommandoAgent::class,
            'delivery.driver' => \App\Http\Middleware\EnsureDeliveryDriver::class,
            'crm.role' => \App\Http\Middleware\EnsureCrmRole::class,
            'api.json' => \App\Http\Middleware\ForceJsonResponse::class,
            'api.sanitize' => \App\Http\Middleware\SanitizeApiInput::class,
            'api.security' => \App\Http\Middleware\ApiSecurityHeaders::class,
            'subscription' => \App\Http\Middleware\CheckSubscription::class,
            'feature' => \App\Http\Middleware\CheckPlanFeature::class,
            'track.login' => \App\Http\Middleware\TrackLastLogin::class,
        ]);

        // Append to web middleware group
        $middleware->web(append: [
            \App\Http\Middleware\TrackLastLogin::class,
            \App\Http\Middleware\SecurityHeaders::class,
            \App\Http\Middleware\CleanJsonResponse::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {

        // Réponses JSON uniformes pour toutes les erreurs sur /api/v1/*
        $exceptions->render(function (\Throwable $e, $request) {
            if (!$request->is('api/v1/*')) {
                return null; // Laisser le handler par défaut gérer le reste
            }

            // HttpResponseException (rate limiter custom response, etc.) → retourner la réponse directement
            if ($e instanceof \Illuminate\Http\Exceptions\HttpResponseException) {
                return $e->getResponse();
            }

            if ($e instanceof \Illuminate\Validation\ValidationException) {
                return response()->json([
                    'message' => 'Données invalides.',
                    'errors'  => $e->errors(),
                ], 422);
            }

            if ($e instanceof \Illuminate\Auth\AuthenticationException) {
                return response()->json(['message' => 'Non authentifié.'], 401);
            }

            if ($e instanceof \Illuminate\Auth\Access\AuthorizationException) {
                return response()->json(['message' => 'Accès refusé.'], 403);
            }

            if ($e instanceof \Illuminate\Database\Eloquent\ModelNotFoundException) {
                return response()->json(['message' => 'Ressource introuvable.'], 404);
            }

            if ($e instanceof \Symfony\Component\HttpKernel\Exception\NotFoundHttpException) {
                return response()->json(['message' => 'Route introuvable.'], 404);
            }

            if ($e instanceof \Symfony\Component\HttpKernel\Exception\MethodNotAllowedHttpException) {
                return response()->json(['message' => 'Méthode HTTP non autorisée.'], 405);
            }

            if ($e instanceof \Symfony\Component\HttpKernel\Exception\TooManyRequestsHttpException) {
                return response()->json(['message' => 'Trop de requêtes. Réessayez plus tard.'], 429);
            }

            // Erreur serveur — ne pas exposer les détails en production
            \Log::error('API v1 error', [
                'message' => $e->getMessage(),
                'path'    => $request->path(),
                'trace'   => $e->getTraceAsString(),
            ]);

            return response()->json([
                'message' => 'Une erreur est survenue. Réessayez.',
            ], 500);
        });

        // UTF-8 — erreurs Livewire existantes
        $exceptions->render(function (\InvalidArgumentException $e, $request) {
            if (str_contains($e->getMessage(), 'UTF-8') || str_contains($e->getMessage(), 'Malformed')) {
                \Log::error('UTF-8 encoding error', [
                    'message' => $e->getMessage(),
                    'path'    => $request->path(),
                    'trace'   => $e->getTraceAsString(),
                ]);

                if ($request->header('X-Livewire')) {
                    return response()->json([
                        'error' => 'Une erreur de codage est survenue. Veuillez réessayer.',
                        'skip'  => true,
                    ], 500);
                }

                return response()->view('errors.utf8', [], 500);
            }
        });
    })->create();
