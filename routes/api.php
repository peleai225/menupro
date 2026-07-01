<?php

use App\Http\Controllers\Api\PayoutController;
use App\Http\Controllers\Api\V1\Admin\DriverManagementController;
use App\Http\Controllers\Api\V1\Admin\PlatformAnalyticsController;
use App\Http\Controllers\Api\V1\Admin\PlatformRestaurantController;
use App\Http\Controllers\Api\V1\Client\AddressController;
use App\Http\Controllers\Api\V1\Client\AuthController;
use App\Http\Controllers\Api\V1\Client\OrderController as ClientOrderController;
use App\Http\Controllers\Api\V1\Client\PaymentController;
use App\Http\Controllers\Api\V1\Client\RestaurantController;
use App\Http\Controllers\Api\V1\Driver\AuthController as DriverAuthController;
use App\Http\Controllers\Api\V1\Driver\DeliveryController;
use App\Http\Controllers\Api\V1\Driver\EarningsController;
use App\Http\Controllers\Api\V1\Restaurant\DeliveryOrderController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// API interne existante (Sanctum)
Route::middleware('auth:sanctum')->group(function () {
    Route::post('/payouts/request', [PayoutController::class, 'requestPayout']);
    Route::get('/payouts/{payoutId}/status', [PayoutController::class, 'getPayoutStatus']);
    Route::get('/wallet/{restaurantId}/balance', [PayoutController::class, 'getWalletBalance']);

    Route::get('/me', function (Request $request) {
        return $request->user();
    });
});

/*
|--------------------------------------------------------------------------
| API v1 — Plateforme de livraison
| Middlewares globaux v1 : JSON forcé + sanitisation + headers sécurité
|--------------------------------------------------------------------------
*/
Route::prefix('v1')
    ->name('api.v1.')
    ->middleware(['api.json', 'api.sanitize', 'api.security'])
    ->group(function () {

    // -----------------------------------------------------------------------
    // CLIENT
    // -----------------------------------------------------------------------
    Route::prefix('client')->name('client.')->group(function () {

        // Auth publique — rate limit strict anti-brute-force
        Route::middleware('throttle:api.auth')->group(function () {
            Route::post('/auth/login', [AuthController::class, 'login'])->name('auth.login');
        });

        Route::middleware('throttle:api.register')->group(function () {
            Route::post('/auth/register', [AuthController::class, 'register'])->name('auth.register');
        });

        // Routes protégées client
        Route::middleware('auth:sanctum')->group(function () {

            Route::get('/auth/me',        [AuthController::class, 'me'])->name('auth.me');
            Route::post('/auth/logout',   [AuthController::class, 'logout'])->name('auth.logout');
            Route::patch('/auth/profile', [AuthController::class, 'updateProfile'])->name('auth.profile');

            // Adresses
            Route::get('/addresses',         [AddressController::class, 'index'])->name('addresses.index');
            Route::post('/addresses',        [AddressController::class, 'store'])->name('addresses.store');
            Route::patch('/addresses/{id}',  [AddressController::class, 'update'])->name('addresses.update');
            Route::delete('/addresses/{id}', [AddressController::class, 'destroy'])->name('addresses.destroy');

            // Commandes — rate limit par client
            Route::middleware('throttle:api.orders')->group(function () {
                Route::post('/orders', [ClientOrderController::class, 'store'])->name('orders.store');
            });

            Route::get('/orders/history',      [ClientOrderController::class, 'history'])->name('orders.history');
            Route::post('/orders/{id}/cancel', [ClientOrderController::class, 'cancel'])->name('orders.cancel');

            // Paiement — rate limit strict
            Route::middleware('throttle:api.payment')->group(function () {
                Route::post('/payment/{orderId}/initiate', [PaymentController::class, 'initiate'])->name('payment.initiate');
            });

            Route::get('/payment/{orderId}/status', [PaymentController::class, 'status'])->name('payment.status');
        });

        // Suivi public (sans auth)
        Route::middleware('throttle:api.public')->group(function () {
            Route::get('/orders/track/{token}', [ClientOrderController::class, 'track'])->name('orders.track');
        });

        // Callbacks Wave (sans auth, sans throttle — viennent de Wave)
        Route::get('/payment/success', [PaymentController::class, 'success'])->name('payment.success');
        Route::get('/payment/error',   [PaymentController::class, 'error'])->name('payment.error');
    });

    // -----------------------------------------------------------------------
    // LIVREUR
    // -----------------------------------------------------------------------
    Route::prefix('driver')->name('driver.')->group(function () {

        Route::middleware('throttle:api.auth')->group(function () {
            Route::post('/auth/login', [DriverAuthController::class, 'login'])->name('auth.login');
        });

        Route::middleware('throttle:api.register')->group(function () {
            Route::post('/auth/register', [DriverAuthController::class, 'register'])->name('auth.register');
        });

        Route::middleware(['auth:sanctum', 'delivery.driver'])->group(function () {

            Route::get('/auth/me',          [DriverAuthController::class, 'me'])->name('auth.me');
            Route::post('/auth/logout',     [DriverAuthController::class, 'logout'])->name('auth.logout');
            Route::patch('/auth/fcm-token', [DriverAuthController::class, 'updateFcmToken'])->name('auth.fcm');

            Route::post('/status', [DeliveryController::class, 'setStatus'])->name('status');

            // Position GPS — rate limit spécifique (30 req/min)
            Route::middleware('throttle:api.driver.location')->group(function () {
                Route::patch('/location', [DeliveryController::class, 'updateLocation'])->name('location');
            });

            Route::get('/deliveries/pending',       [DeliveryController::class, 'pendingOrders'])->name('deliveries.pending');
            Route::get('/deliveries/active',        [DeliveryController::class, 'activeDelivery'])->name('deliveries.active');
            Route::post('/deliveries/{id}/accept',  [DeliveryController::class, 'accept'])->name('deliveries.accept');
            Route::post('/deliveries/{id}/decline', [DeliveryController::class, 'decline'])->name('deliveries.decline');
            Route::patch('/deliveries/{id}/status', [DeliveryController::class, 'updateDeliveryStatus'])->name('deliveries.status');

            Route::get('/earnings',         [EarningsController::class, 'summary'])->name('earnings.summary');
            Route::get('/earnings/history', [EarningsController::class, 'history'])->name('earnings.history');

            // Virements — max 3/jour
            Route::middleware('throttle:api.payout')->group(function () {
                Route::post('/earnings/payout', [EarningsController::class, 'requestPayout'])->name('earnings.payout');
            });
        });
    });

    // -----------------------------------------------------------------------
    // RESTAURANT — Gestion commandes livraison
    // -----------------------------------------------------------------------
    Route::prefix('restaurant')->name('restaurant.')
        ->middleware(['auth:sanctum', 'has.restaurant'])
        ->group(function () {
            Route::get('/delivery/orders',               [DeliveryOrderController::class, 'pending'])->name('delivery.orders');
            Route::post('/delivery/orders/{id}/confirm', [DeliveryOrderController::class, 'confirm'])->name('delivery.confirm');
            Route::post('/delivery/orders/{id}/ready',   [DeliveryOrderController::class, 'markReady'])->name('delivery.ready');
            Route::get('/delivery/settings',             [DeliveryOrderController::class, 'getDeliverySettings'])->name('delivery.settings');
            Route::patch('/delivery/settings',           [DeliveryOrderController::class, 'updateDeliverySettings'])->name('delivery.settings.update');
        });

    // -----------------------------------------------------------------------
    // SUPER ADMIN
    // -----------------------------------------------------------------------
    Route::prefix('admin')->name('admin.')
        ->middleware(['auth:sanctum', 'super.admin', 'throttle:api.admin'])
        ->group(function () {

            // Livreurs
            Route::get('/drivers',                  [DriverManagementController::class, 'index'])->name('drivers.index');
            Route::get('/drivers/{id}',             [DriverManagementController::class, 'show'])->name('drivers.show');
            Route::post('/drivers/{id}/approve',    [DriverManagementController::class, 'approve'])->name('drivers.approve');
            Route::post('/drivers/{id}/reject',     [DriverManagementController::class, 'reject'])->name('drivers.reject');
            Route::post('/drivers/{id}/suspend',    [DriverManagementController::class, 'suspend'])->name('drivers.suspend');
            Route::post('/drivers/{id}/reactivate', [DriverManagementController::class, 'reactivate'])->name('drivers.reactivate');

            // Restaurants plateforme
            Route::get('/platform/restaurants',                    [PlatformRestaurantController::class, 'index'])->name('platform.restaurants.index');
            Route::post('/platform/restaurants/{id}/enable',       [PlatformRestaurantController::class, 'enable'])->name('platform.restaurants.enable');
            Route::post('/platform/restaurants/{id}/disable',      [PlatformRestaurantController::class, 'disable'])->name('platform.restaurants.disable');
            Route::patch('/platform/restaurants/{id}/commission',  [PlatformRestaurantController::class, 'updateCommission'])->name('platform.restaurants.commission');

            // Analytics
            Route::get('/analytics/dashboard',       [PlatformAnalyticsController::class, 'dashboard'])->name('analytics.dashboard');
            Route::get('/analytics/live-deliveries', [PlatformAnalyticsController::class, 'liveDeliveries'])->name('analytics.live');
            Route::get('/analytics/commissions',     [PlatformAnalyticsController::class, 'commissions'])->name('analytics.commissions');
            Route::get('/analytics/driver-earnings', [PlatformAnalyticsController::class, 'driverEarnings'])->name('analytics.earnings');
        });

    // -----------------------------------------------------------------------
    // RESTAURANTS — API publique (browsing)
    // -----------------------------------------------------------------------
    Route::prefix('restaurants')->name('restaurants.')
        ->middleware('throttle:api.public')
        ->group(function () {
            Route::get('/',                       [RestaurantController::class, 'index'])->name('index');
            Route::get('/nearby',                 [RestaurantController::class, 'nearby'])->name('nearby');
            Route::get('/{id}',                   [RestaurantController::class, 'show'])->name('show');
            Route::get('/{id}/menu',              [RestaurantController::class, 'menu'])->name('menu');
            Route::get('/{id}/delivery-estimate', [RestaurantController::class, 'estimateDelivery'])->name('delivery-estimate');
        });
});
