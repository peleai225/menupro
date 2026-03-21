<?php

use App\Http\Controllers\Api\PayoutController;
use App\Http\Controllers\Api\WavePaymentController;
use App\Http\Controllers\Webhook\WaveWebhookController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Ces routes exposent l’API Wave (Checkout + Payout) pour MenuPro.
| Elles sont séparées des routes web existantes (CinetPay, FusionPay, etc.).
|
*/

// Webhook Wave (pas d'auth middleware)
Route::post('/webhooks/wave/checkout', [WaveWebhookController::class, 'handleCheckout']);

// API protégée par Sanctum
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/payments/initiate', [WavePaymentController::class, 'initiatePayment']);

    Route::post('/payouts/request', [PayoutController::class, 'requestPayout']);
    Route::get('/payouts/{payoutId}/status', [PayoutController::class, 'getPayoutStatus']);
    Route::get('/wallet/{restaurantId}/balance', [PayoutController::class, 'getWalletBalance']);

    // Route de test simple (optionnelle)
    Route::get('/me', function (Request $request) {
        return $request->user();
    });
});

