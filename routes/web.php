<?php

use App\Http\Controllers\Auth\EmailVerificationController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\PasswordResetController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Public\CheckoutController;
use App\Http\Controllers\Public\MenuController;
use App\Http\Controllers\Public\OrderStatusController;
use App\Http\Controllers\Restaurant\CategoryController;
use App\Http\Controllers\Restaurant\CustomerController;
use App\Http\Controllers\Restaurant\DashboardController;
use App\Http\Controllers\Restaurant\DishController;
use App\Http\Controllers\Restaurant\IngredientCategoryController;
use App\Http\Controllers\Restaurant\IngredientController;
use App\Http\Controllers\Restaurant\OrderController;
use App\Http\Controllers\Restaurant\SettingsController;
use App\Http\Controllers\Restaurant\SubscriptionController;
use App\Http\Controllers\Restaurant\SupplierController;
use App\Http\Controllers\SuperAdmin\ActivityController;
use App\Http\Controllers\SuperAdmin\DashboardController as SuperAdminDashboardController;
use App\Http\Controllers\SuperAdmin\PlanController;
use App\Http\Controllers\SuperAdmin\RestaurantController;
use App\Http\Controllers\SuperAdmin\StatsController;
use App\Http\Controllers\SuperAdmin\UserController;
use App\Http\Controllers\Webhook\LygosWebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes (Marketing)
|--------------------------------------------------------------------------
*/

Route::get('/', [\App\Http\Controllers\Public\HomeController::class, 'index'])->name('home');
Route::get('/tarifs', fn () => view('pages.public.pricing'))->name('pricing');
Route::get('/conditions', fn () => view('pages.public.legal.terms'))->name('terms');
Route::get('/confidentialite', fn () => view('pages.public.legal.privacy'))->name('privacy');

/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    // Login
    Route::get('/connexion', [LoginController::class, 'create'])->name('login');
    Route::post('/connexion', [LoginController::class, 'store'])->name('login.post');
    
    // Register
    Route::get('/inscription', [RegisterController::class, 'create'])->name('register');
    Route::post('/inscription', [RegisterController::class, 'store'])->name('register.post');
    
    // Password Reset
    Route::get('/mot-de-passe-oublie', [PasswordResetController::class, 'requestForm'])->name('password.request');
    Route::post('/mot-de-passe-oublie', [PasswordResetController::class, 'sendResetLink'])->name('password.email');
    Route::get('/reinitialiser-mot-de-passe/{token}', [PasswordResetController::class, 'resetForm'])->name('password.reset');
    Route::post('/reinitialiser-mot-de-passe', [PasswordResetController::class, 'reset'])->name('password.update');
});

// Email verification (authenticated users)
Route::middleware('auth')->group(function () {
    Route::get('/email/verifier', [EmailVerificationController::class, 'notice'])->name('verification.notice');
    Route::get('/email/verifier/{id}/{hash}', [EmailVerificationController::class, 'verify'])
        ->middleware('signed')
        ->name('verification.verify');
    Route::post('/email/renvoyer', [EmailVerificationController::class, 'send'])
        ->middleware('throttle:6,1')
        ->name('verification.send');
});

Route::post('/logout', [LoginController::class, 'destroy'])->name('logout');

/*
|--------------------------------------------------------------------------
| Restaurant Admin Routes
|--------------------------------------------------------------------------
*/

Route::prefix('dashboard')
    ->name('restaurant.')
    ->middleware(['auth', 'verified'])
    ->group(function () {
        // Dashboard (Livewire)
        Route::get('/', \App\Livewire\Restaurant\Dashboard::class)->name('dashboard');
        
        // Categories (Livewire)
        Route::get('categories', \App\Livewire\Restaurant\Categories::class)->name('categories.index');
        Route::resource('categories', CategoryController::class)->except(['index']);
        Route::post('categories/reorder', [CategoryController::class, 'reorder'])->name('categories.reorder');
        
        // Dishes (Livewire)
        Route::get('plats', \App\Livewire\Restaurant\Dishes::class)->name('plats.index');
        Route::get('plats/nouveau', \App\Livewire\Restaurant\DishForm::class)->name('plats.create');
        Route::get('plats/{dish}/modifier', \App\Livewire\Restaurant\DishForm::class)->name('plats.edit');
        Route::delete('plats/{dish}', [DishController::class, 'destroy'])->name('plats.destroy');
        Route::post('plats/reorder', [DishController::class, 'reorder'])->name('dishes.reorder');
        Route::patch('plats/{dish}/toggle', [DishController::class, 'toggleAvailability'])->name('dishes.toggle');
        Route::patch('plats/{dish}/featured', [DishController::class, 'toggleFeatured'])->name('dishes.featured');
        
        // Orders (Livewire)
        Route::get('commandes', \App\Livewire\Restaurant\Orders::class)->name('orders');
        Route::get('commandes/board', [OrderController::class, 'board'])->name('orders.board');
        Route::get('commandes/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::patch('commandes/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');
        Route::get('commandes/{order}/print', [OrderController::class, 'print'])->name('orders.print');
        Route::post('commandes/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
        Route::post('commandes/{order}/refund', [OrderController::class, 'refund'])->name('orders.refund');
        
        // Customers
        Route::get('clients', [CustomerController::class, 'index'])->name('customers');
        Route::get('clients/export', [CustomerController::class, 'export'])->name('customers.export');
        Route::get('clients/{customer}', [CustomerController::class, 'show'])->name('customers.show');
        
        // Subscription
        Route::get('abonnement', [SubscriptionController::class, 'index'])->name('subscription');
        Route::get('abonnement/changer', [SubscriptionController::class, 'plans'])->name('subscription.plans');
        Route::post('abonnement/changer', [SubscriptionController::class, 'change'])->name('subscription.change');
        Route::get('abonnement/factures', [SubscriptionController::class, 'invoices'])->name('subscription.invoices');
        Route::get('abonnement/{subscription}/success', [SubscriptionController::class, 'success'])->name('subscription.success');
        Route::get('abonnement/{subscription}/cancel', [SubscriptionController::class, 'cancel'])->name('subscription.cancel');
        
        // Settings (Livewire)
        Route::get('parametres', \App\Livewire\Restaurant\Settings::class)->name('settings');
        
        // QR Code
        Route::get('qr-code', [App\Http\Controllers\Restaurant\QRCodeController::class, 'index'])->name('qrcode');
        
        // Promo Codes (Livewire)
        Route::get('codes-promo', \App\Livewire\Restaurant\PromoCodes::class)->name('promo-codes');
        
        // Analytics (Livewire)
        Route::get('statistiques', \App\Livewire\Restaurant\Analytics::class)->name('analytics');
        
        // Reports (Livewire)
        Route::get('rapports', \App\Livewire\Restaurant\Reports::class)->name('reports');
        
        // Reviews (Livewire)
        Route::get('avis', \App\Livewire\Restaurant\Reviews::class)->name('reviews');
        
        // Taxes & Fees (Livewire)
        Route::get('taxes-frais', \App\Livewire\Restaurant\TaxesAndFees::class)->name('taxes-fees');
        
        // Stock Management
        Route::prefix('stock')->name('stock.')->group(function () {
            // Ingredient Categories
            Route::resource('categories-ingredients', IngredientCategoryController::class)
                ->parameters(['categories-ingredients' => 'ingredientCategory']);
            
            // Ingredients
            Route::resource('ingredients', IngredientController::class);
            Route::post('ingredients/{ingredient}/entry', [IngredientController::class, 'addStock'])->name('ingredients.entry');
            Route::post('ingredients/{ingredient}/exit', [IngredientController::class, 'removeStock'])->name('ingredients.exit');
            Route::post('ingredients/{ingredient}/adjustment', [IngredientController::class, 'adjust'])->name('ingredients.adjustment');
            Route::post('ingredients/{ingredient}/waste', [IngredientController::class, 'recordWaste'])->name('ingredients.waste');
            Route::get('ingredients/{ingredient}/movements', [IngredientController::class, 'movements'])->name('ingredients.movements');
            
            // Suppliers
            Route::resource('fournisseurs', SupplierController::class)->parameters(['fournisseurs' => 'supplier']);
            
            // Stock Reports
            Route::get('rapport', [IngredientController::class, 'report'])->name('report');
            Route::get('alertes', [IngredientController::class, 'alerts'])->name('alerts');
        });
        
        // Team Management (if enabled by plan)
        Route::middleware('subscription:team_members')->group(function () {
            Route::get('equipe', \App\Livewire\Restaurant\Team::class)->name('team');
        });
        
        // Reservations
        Route::get('reservations', [\App\Http\Controllers\Restaurant\ReservationController::class, 'index'])->name('reservations.index');
        Route::get('reservations/{reservation}', [\App\Http\Controllers\Restaurant\ReservationController::class, 'show'])->name('reservations.show');
        Route::patch('reservations/{reservation}/status', [\App\Http\Controllers\Restaurant\ReservationController::class, 'updateStatus'])->name('reservations.status');
    });

/*
|--------------------------------------------------------------------------
| Super Admin Routes
|--------------------------------------------------------------------------
*/

Route::prefix('admin')
    ->name('super-admin.')
    ->middleware(['auth', 'verified', 'super.admin'])
    ->group(function () {
        // Dashboard
        Route::get('/', [SuperAdminDashboardController::class, 'index'])->name('dashboard');
        
        // Restaurants Management
        Route::resource('restaurants', RestaurantController::class)->only(['index', 'show', 'update', 'destroy']);
        Route::post('restaurants/{restaurant}/approve', [RestaurantController::class, 'approve'])->name('restaurants.approve');
        Route::post('restaurants/{restaurant}/reject', [RestaurantController::class, 'reject'])->name('restaurants.reject');
        Route::post('restaurants/{restaurant}/suspend', [RestaurantController::class, 'suspend'])->name('restaurants.suspend');
        Route::post('restaurants/{restaurant}/reactivate', [RestaurantController::class, 'reactivate'])->name('restaurants.reactivate');
        Route::post('restaurants/{restaurant}/impersonate', [RestaurantController::class, 'impersonate'])->name('restaurants.impersonate');
        Route::post('restaurants/{restaurant}/extend-subscription', [RestaurantController::class, 'extendSubscription'])->name('restaurants.extend-subscription');
        Route::post('restaurants/{restaurant}/verify', [RestaurantController::class, 'verify'])->name('restaurants.verify');
        Route::post('restaurants/{restaurant}/unverify', [RestaurantController::class, 'unverify'])->name('restaurants.unverify');
        
        // Subscriptions Management
        Route::get('abonnements', [\App\Http\Controllers\SuperAdmin\SubscriptionController::class, 'index'])->name('subscriptions.index');
        Route::get('abonnements/{subscription}', [\App\Http\Controllers\SuperAdmin\SubscriptionController::class, 'show'])->name('subscriptions.show');
        Route::get('abonnements/export/csv', [\App\Http\Controllers\SuperAdmin\SubscriptionController::class, 'export'])->name('subscriptions.export');
        
        // Plans Management
        Route::resource('plans', PlanController::class);
        Route::post('plans/reorder', [PlanController::class, 'reorder'])->name('plans.reorder');
        
        // Users Management
        Route::resource('utilisateurs', UserController::class)->parameters(['utilisateurs' => 'user']);
        Route::post('utilisateurs/{user}/suspend', [UserController::class, 'suspend'])->name('users.suspend');
        Route::post('utilisateurs/{user}/reactivate', [UserController::class, 'reactivate'])->name('users.reactivate');
        Route::post('utilisateurs/{user}/reset-password', [UserController::class, 'resetPassword'])->name('users.reset-password');
        Route::post('utilisateurs/{user}/toggle', [UserController::class, 'toggle'])->name('users.toggle');
        Route::post('utilisateurs/{user}/change-role', [UserController::class, 'changeRole'])->name('users.change-role');
        
        // Statistics
        Route::get('statistiques', [StatsController::class, 'index'])->name('stats');
        Route::get('statistiques/revenue', [StatsController::class, 'revenue'])->name('stats.revenue');
        Route::get('statistiques/growth', [StatsController::class, 'growth'])->name('stats.growth');
        Route::get('statistiques/export', [StatsController::class, 'export'])->name('stats.export');
        
        // Activity Logs
        Route::get('activite', [ActivityController::class, 'index'])->name('activity');
        Route::get('activite/{log}', [ActivityController::class, 'show'])->name('activity.show');
        Route::get('activite/export', [ActivityController::class, 'export'])->name('activity.export');
        
        // System Settings
        Route::get('parametres', [SuperAdminDashboardController::class, 'settings'])->name('settings');
        Route::post('parametres', [SuperAdminDashboardController::class, 'updateSettings'])->name('settings.update');
    });

/*
|--------------------------------------------------------------------------
| Restaurant Public Routes (Client Ordering)
|--------------------------------------------------------------------------
*/

Route::prefix('r/{slug}')->name('r.')->group(function () {
    // Menu (Livewire)
    Route::get('/', \App\Livewire\Public\RestaurantMenu::class)->name('menu');
    
    // Checkout (Livewire)
    Route::get('/commander', \App\Livewire\Public\Checkout::class)->name('checkout');
    
    // Order Status
    Route::get('/commande/{order}', [OrderStatusController::class, 'show'])->name('order.status');
    Route::get('/commande/{order}/json', [OrderStatusController::class, 'status'])->name('order.status.json');
    
    // Reviews
    Route::get('/commande/{order}/avis', [\App\Http\Controllers\Public\ReviewController::class, 'create'])->name('review.create');
    Route::post('/commande/{order}/avis', [\App\Http\Controllers\Public\ReviewController::class, 'store'])->name('review.store');
    
    // Reservations
    Route::post('/reservations', [\App\Http\Controllers\Public\ReservationController::class, 'store'])->name('reservations.store');
    
    // Payment Callbacks
    Route::get('/commande/{order}/success', [CheckoutController::class, 'success'])->name('order.success');
    Route::get('/commande/{order}/cancel', [CheckoutController::class, 'cancel'])->name('order.cancel');
});

/*
|--------------------------------------------------------------------------
| Webhook Routes
|--------------------------------------------------------------------------
*/

Route::prefix('webhooks')->withoutMiddleware(['web'])->group(function () {
    Route::post('/lygos', [LygosWebhookController::class, 'handle'])->name('webhooks.lygos');
});
