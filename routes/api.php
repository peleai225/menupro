<?php

use App\Http\Controllers\Api\PayoutController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// API protégée par Sanctum
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/payouts/request', [PayoutController::class, 'requestPayout']);
    Route::get('/payouts/{payoutId}/status', [PayoutController::class, 'getPayoutStatus']);
    Route::get('/wallet/{restaurantId}/balance', [PayoutController::class, 'getWalletBalance']);

    Route::get('/me', function (Request $request) {
        return $request->user();
    });
});
