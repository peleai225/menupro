<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\ServiceProvider;

class RateLimitServiceProvider extends ServiceProvider
{
    public function boot(): void
    {
        // Auth client — empêcher le brute-force
        // Double limiteur : par IP (5/min) + par compte (5 tentatives / 15 min)
        // Le limiteur par compte protège même si l'attaquant tourne ses IPs.
        RateLimiter::for('api.auth', function (Request $request) {
            return [
                Limit::perMinute(5)->by($request->ip())
                    ->response(fn() => response()->json([
                        'message' => 'Trop de tentatives depuis votre adresse. Réessayez dans 1 minute.',
                    ], 429)),
                Limit::perMinutes(15, 5)->by('login:' . ($request->input('email') ?? $request->ip()))
                    ->response(fn() => response()->json([
                        'message' => 'Trop de tentatives sur ce compte. Réessayez dans 15 minutes.',
                    ], 429)),
            ];
        });

        // Inscription — une seule par IP par heure
        RateLimiter::for('api.register', function (Request $request) {
            return Limit::perHour(5)->by($request->ip())
                ->response(fn() => response()->json([
                    'message' => 'Trop d\'inscriptions depuis cette adresse. Réessayez dans 1 heure.',
                ], 429));
        });

        // Commandes client — max 20 commandes/heure par client
        RateLimiter::for('api.orders', function (Request $request) {
            $key = $request->user()
                ? 'customer:' . $request->user()->id
                : $request->ip();

            return Limit::perHour(20)->by($key)
                ->response(fn() => response()->json([
                    'message' => 'Limite de commandes atteinte. Réessayez dans 1 heure.',
                ], 429));
        });

        // Paiement — max 5 tentatives par commande / par IP
        RateLimiter::for('api.payment', function (Request $request) {
            $key = $request->user()
                ? 'pay:' . $request->user()->id
                : $request->ip();

            return Limit::perMinutes(5, 5)->by($key)
                ->response(fn() => response()->json([
                    'message' => 'Trop de tentatives de paiement. Attendez 5 minutes.',
                ], 429));
        });

        // Position GPS livreur — 1 appel / 5 secondes (app envoie toutes les 10s)
        RateLimiter::for('api.driver.location', function (Request $request) {
            return Limit::perMinute(30)->by('driver:' . ($request->user()?->id ?? $request->ip()))
                ->response(fn() => response()->json([
                    'message' => 'Mise à jour GPS trop fréquente.',
                ], 429));
        });

        // API publique restaurants/menu — lecture seule, assez permissif
        RateLimiter::for('api.public', function (Request $request) {
            return Limit::perMinute(120)->by($request->ip())
                ->response(fn() => response()->json([
                    'message' => 'Trop de requêtes. Réessayez dans une minute.',
                ], 429));
        });

        // APIs admin — protégées, limite haute
        RateLimiter::for('api.admin', function (Request $request) {
            return Limit::perMinute(60)->by($request->user()?->id ?? $request->ip());
        });

        // Virements livreur — max 3 par jour
        RateLimiter::for('api.payout', function (Request $request) {
            return Limit::perDay(3)->by('payout:' . ($request->user()?->id ?? $request->ip()))
                ->response(fn() => response()->json([
                    'message' => 'Limite de virements journaliers atteinte (3/jour).',
                ], 429));
        });
    }
}
