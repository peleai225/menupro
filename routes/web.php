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
use App\Http\Controllers\Webhook\FusionPayPaymentWebhook;
use App\Http\Controllers\Webhook\WaveWebhookController;
use App\Http\Controllers\Webhook\FusionPayPayoutWebhook;
use App\Http\Controllers\Webhook\GeniusPayWebhookController;
use App\Http\Controllers\Webhook\LygosWebhookController;
use App\Http\Controllers\Webhook\MenuProHubWebhookController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public Routes (Marketing)
|--------------------------------------------------------------------------
*/

Route::get('/sitemap.xml', [\App\Http\Controllers\Public\SitemapController::class, 'index'])->name('sitemap');
Route::get('/robots.txt', [\App\Http\Controllers\Public\SitemapController::class, 'robots'])->name('robots');
Route::get('/', [\App\Http\Controllers\Public\HomeController::class, 'index'])->name('home');
Route::get('/tarifs', fn () => view('pages.public.pricing'))->name('pricing');
Route::get('/contact', [\App\Http\Controllers\Public\ContactController::class, 'index'])->name('contact');
Route::post('/contact', [\App\Http\Controllers\Public\ContactController::class, 'send'])->name('contact.send')->middleware('throttle:5,1');
Route::get('/faq', fn () => view('pages.public.faq'))->name('faq');
Route::get('/conditions', fn () => view('pages.public.legal.terms'))->name('terms');
Route::get('/confidentialite', fn () => view('pages.public.legal.privacy'))->name('privacy');
Route::get('/mentions-legales', fn () => view('pages.public.legal.mentions'))->name('mentions-legales');
Route::get('/test-geocoding', [\App\Http\Controllers\Public\GeocodingTestController::class, 'index'])->name('geocoding.test');

/*
|--------------------------------------------------------------------------
| MenuPro Commando - Vérification publique (scan QR)
|--------------------------------------------------------------------------
*/
Route::get('/verify/{uuid}', [\App\Http\Controllers\Commando\AgentVerificationController::class, 'show'])->name('commando.verify');

/*
|--------------------------------------------------------------------------
| MenuPro Commando - Inscription agents (public)
|--------------------------------------------------------------------------
*/
Route::prefix('commando')->name('commando.')->group(function () {
    Route::get('/inscription', \App\Livewire\Commando\RegisterStep1::class)->name('register.step1');
    Route::get('/inscription/complete/{agent}', \App\Livewire\Commando\RegisterStep2::class)
        ->middleware('signed')
        ->name('register.step2');
    Route::get('/inscription/success', fn () => view('pages.commando.register-success'))->name('register.success');
    // Bienvenue agent : définir mot de passe (lien envoyé après approbation)
    Route::get('/bienvenue', [\App\Http\Controllers\Commando\CommandoWelcomeController::class, 'show'])->name('welcome');
    Route::post('/bienvenue', [\App\Http\Controllers\Commando\CommandoWelcomeController::class, 'store'])->name('welcome.store');
});

// Route pour rafraîchir le token CSRF (évite l'erreur 419)
Route::get('/csrf-token', function () {
    return response()->json(['token' => csrf_token()]);
})->name('csrf.token');

/*
|--------------------------------------------------------------------------
| Auth Routes
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    // Login
    Route::get('/connexion', [LoginController::class, 'create'])->name('login');
    Route::post('/connexion', [LoginController::class, 'store'])->middleware('throttle:5,1')->name('login.post');

    // Register
    Route::get('/inscription', [RegisterController::class, 'create'])->name('register');
    Route::post('/inscription', [RegisterController::class, 'store'])->middleware('throttle:3,1')->name('register.post');

    // Password Reset
    Route::get('/mot-de-passe-oublie', [PasswordResetController::class, 'requestForm'])->name('password.request');
    Route::post('/mot-de-passe-oublie', [PasswordResetController::class, 'sendResetLink'])->middleware('throttle:3,1')->name('password.email');
    Route::get('/reinitialiser-mot-de-passe/{token}', [PasswordResetController::class, 'resetForm'])->name('password.reset');
    Route::post('/reinitialiser-mot-de-passe', [PasswordResetController::class, 'reset'])->middleware('throttle:5,1')->name('password.update');
});

// Register payment callbacks - user is authenticated after registration
Route::middleware('auth')->group(function () {
    Route::get('/inscription/paiement/{subscription}/succes', [RegisterController::class, 'paymentSuccess'])->name('register.payment.success');
    Route::get('/inscription/paiement/{subscription}/annule', [RegisterController::class, 'paymentCancel'])->name('register.payment.cancel');
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
    ->middleware(['auth', 'verified', 'set.restaurant.scope'])
    ->group(function () {
        // Dashboard (Livewire) - tous
        Route::get('/', \App\Livewire\Restaurant\Dashboard::class)->name('dashboard');

        // POS - Mode Caisse - tous
        Route::get('pos', \App\Livewire\Restaurant\POS::class)->name('pos');
        
        // Categories - admin uniquement
        Route::middleware('restaurant.admin')->group(function () {
            Route::get('categories', \App\Livewire\Restaurant\Categories::class)->name('categories.index');
            Route::resource('categories', CategoryController::class)->except(['index']);
            Route::post('categories/reorder', [CategoryController::class, 'reorder'])->name('categories.reorder');
        });
        
        // Dishes - plats index et modifier pour tous, create/destroy/reorder/featured admin
        Route::get('plats', \App\Livewire\Restaurant\Dishes::class)->name('plats.index');
        Route::get('plats/nouveau', \App\Livewire\Restaurant\DishForm::class)->name('plats.create')->middleware('restaurant.admin');
        Route::get('plats/{dish}/modifier', \App\Livewire\Restaurant\DishForm::class)->name('plats.edit');
        Route::delete('plats/{dish}', [DishController::class, 'destroy'])->name('plats.destroy')->middleware('restaurant.admin');
        Route::post('plats/reorder', [DishController::class, 'reorder'])->name('dishes.reorder')->middleware('restaurant.admin');
        Route::patch('plats/{dish}/toggle', [DishController::class, 'toggleAvailability'])->name('dishes.toggle');
        Route::patch('plats/{dish}/featured', [DishController::class, 'toggleFeatured'])->name('dishes.featured')->middleware('restaurant.admin');
        
        // Orders (Livewire) - tous (employés gèrent les commandes)
        Route::get('commandes', \App\Livewire\Restaurant\Orders::class)->name('orders');
        Route::get('commandes/board', [OrderController::class, 'board'])->name('orders.board');
        Route::get('commandes/kanban', [\App\Http\Controllers\Restaurant\OrderBoardController::class, 'index'])->name('orders.kanban');
        Route::get('commandes/kanban/data', [\App\Http\Controllers\Restaurant\OrderBoardController::class, 'data'])->name('orders.kanban.data');
        Route::patch('commandes/{order}/kanban/status', [\App\Http\Controllers\Restaurant\OrderBoardController::class, 'updateStatus'])->name('orders.kanban.status');
        Route::get('commandes/rush', [\App\Http\Controllers\Restaurant\OrderRushController::class, 'index'])->name('orders.rush');
        Route::get('commandes/rush/data', [\App\Http\Controllers\Restaurant\OrderRushController::class, 'data'])->name('orders.rush.data');
        Route::post('commandes/{order}/rush/confirm', [\App\Http\Controllers\Restaurant\OrderRushController::class, 'confirm'])->name('orders.rush.confirm');
        Route::post('commandes/{order}/rush/prepare', [\App\Http\Controllers\Restaurant\OrderRushController::class, 'startPreparing'])->name('orders.rush.prepare');
        Route::post('commandes/{order}/rush/ready', [\App\Http\Controllers\Restaurant\OrderRushController::class, 'markReady'])->name('orders.rush.ready');
        Route::post('commandes/{order}/rush/complete', [\App\Http\Controllers\Restaurant\OrderRushController::class, 'complete'])->name('orders.rush.complete');
        Route::get('commandes/{order}', [OrderController::class, 'show'])->name('orders.show');
        Route::patch('commandes/{order}/status', [OrderController::class, 'updateStatus'])->name('orders.status');
        Route::get('commandes/{order}/print', [OrderController::class, 'print'])->name('orders.print');
        Route::post('commandes/{order}/cancel', [OrderController::class, 'cancel'])->name('orders.cancel');
        Route::post('commandes/{order}/refund', [OrderController::class, 'refund'])->name('orders.refund');
        
        // Order Modifications
        Route::prefix('commandes/{order}')->name('orders.')->group(function () {
            Route::post('items', [\App\Http\Controllers\Restaurant\OrderModificationController::class, 'addItem'])->name('items.add');
            Route::delete('items/{item}', [\App\Http\Controllers\Restaurant\OrderModificationController::class, 'removeItem'])->name('items.remove');
            Route::patch('items/{item}', [\App\Http\Controllers\Restaurant\OrderModificationController::class, 'updateItem'])->name('items.update');
        });

        // Customers - admin uniquement
        Route::middleware('restaurant.admin')->group(function () {
            Route::get('clients', [CustomerController::class, 'index'])->name('customers');
            Route::get('clients/export', [CustomerController::class, 'export'])->name('customers.export');
            Route::get('clients/{customer}', [CustomerController::class, 'show'])->name('customers.show');
        });
        
        // Subscription - admin uniquement
        Route::middleware('restaurant.admin')->group(function () {
            Route::get('abonnement', [SubscriptionController::class, 'index'])->name('subscription');
            Route::get('abonnement/changer', [SubscriptionController::class, 'plans'])->name('subscription.plans');
            Route::post('abonnement/changer', [SubscriptionController::class, 'change'])->name('subscription.change');
            Route::post('abonnement/{subscription}/reprendre', [SubscriptionController::class, 'retryPayment'])->name('subscription.retry');
            Route::get('abonnement/factures', [SubscriptionController::class, 'invoices'])->name('subscription.invoices');
            Route::post('abonnement/convert-trial', [SubscriptionController::class, 'convertTrial'])->name('subscription.convertTrial');
            Route::get('abonnement/{subscription}/success', [SubscriptionController::class, 'success'])->name('subscription.success');
            Route::post('abonnement/{subscription}/cancel', [SubscriptionController::class, 'cancel'])->name('subscription.cancel');
        });
        
        // Settings (Livewire) - admin uniquement
        Route::get('parametres', \App\Livewire\Restaurant\Settings::class)->name('settings')->middleware('restaurant.admin');
        
        // QR Code
        Route::get('qr-code', [App\Http\Controllers\Restaurant\QRCodeController::class, 'index'])->name('qrcode');
        Route::post('qr-code/tables', [App\Http\Controllers\Restaurant\QRCodeController::class, 'updateTables'])->name('qrcode.update-tables');
        Route::get('qr-code/tables/download', [App\Http\Controllers\Restaurant\QRCodeController::class, 'downloadTableQR'])->name('qrcode.download-tables');
        Route::get('qr-code/social/download', [App\Http\Controllers\Restaurant\QRCodeController::class, 'downloadSocialCard'])->name('qrcode.download-social');
        Route::get('qr-code/tables/{tableNumber}/preview', [App\Http\Controllers\Restaurant\QRCodeController::class, 'previewTableQR'])->name('qrcode.preview-table');
        
        // Promo Codes, Analytics, Reports, Reviews, Taxes - admin uniquement
        Route::middleware('restaurant.admin')->group(function () {
            Route::get('codes-promo', \App\Livewire\Restaurant\PromoCodes::class)->name('promo-codes');
            Route::get('statistiques', \App\Livewire\Restaurant\Analytics::class)->name('analytics');
            Route::get('rapports', \App\Livewire\Restaurant\Reports::class)->name('reports');
            Route::get('avis', \App\Livewire\Restaurant\Reviews::class)->name('reviews');
            Route::get('taxes-frais', \App\Livewire\Restaurant\TaxesAndFees::class)->name('taxes-fees');
        });
        
        // Stock Management
        Route::prefix('stock')->name('stock.')->group(function () {
            // Ingredient Categories & Suppliers & Rapport - admin uniquement
            Route::middleware('restaurant.admin')->group(function () {
                Route::resource('categories-ingredients', IngredientCategoryController::class)
                    ->parameters(['categories-ingredients' => 'ingredientCategory']);
                Route::resource('fournisseurs', SupplierController::class)->parameters(['fournisseurs' => 'supplier']);
                Route::post('fournisseurs/{supplier}/link-ingredient', [SupplierController::class, 'linkIngredient'])->name('suppliers.link-ingredient');
                Route::delete('fournisseurs/{supplier}/ingredients/{ingredient}', [SupplierController::class, 'unlinkIngredient'])->name('suppliers.unlink-ingredient');
                Route::get('rapport', [IngredientController::class, 'report'])->name('report');
            });
            
            // Ingredients - tous (policies gèrent create/update/delete)
            Route::resource('ingredients', IngredientController::class);
            Route::post('ingredients/{ingredient}/entry', [IngredientController::class, 'addStock'])->name('ingredients.entry');
            Route::post('ingredients/{ingredient}/exit', [IngredientController::class, 'removeStock'])->name('ingredients.exit');
            Route::post('ingredients/{ingredient}/adjustment', [IngredientController::class, 'adjust'])->name('ingredients.adjustment');
            Route::post('ingredients/{ingredient}/waste', [IngredientController::class, 'recordWaste'])->name('ingredients.waste');
            Route::get('ingredients/{ingredient}/movements', [IngredientController::class, 'movements'])->name('ingredients.movements');
            Route::get('alertes', [IngredientController::class, 'alerts'])->name('alerts');
            Route::get('mise-a-jour', \App\Livewire\Restaurant\BulkStockUpdate::class)->name('bulk-update');
        });
        
        // Team Management - admin uniquement
        Route::get('equipe', \App\Livewire\Restaurant\Team::class)->name('team')->middleware('restaurant.admin');
        
        // Reservations - admin uniquement
        Route::middleware('restaurant.admin')->group(function () {
            Route::get('reservations', [\App\Http\Controllers\Restaurant\ReservationController::class, 'index'])->name('reservations.index');
            Route::get('reservations/{reservation}', [\App\Http\Controllers\Restaurant\ReservationController::class, 'show'])->name('reservations.show');
            Route::patch('reservations/{reservation}/status', [\App\Http\Controllers\Restaurant\ReservationController::class, 'updateStatus'])->name('reservations.status');
        });
    });

/*
|--------------------------------------------------------------------------
| Super Admin Routes
|--------------------------------------------------------------------------
*/

/*
|--------------------------------------------------------------------------
| MenuPro Commando - Dashboard Agent (authentifié)
|--------------------------------------------------------------------------
*/
Route::prefix('commando')->name('commando.')->middleware(['auth', 'verified', 'commando.agent'])->group(function () {
    Route::get('/dashboard', \App\Livewire\Commando\Dashboard::class)->name('dashboard');
    Route::get('/carte', [\App\Http\Controllers\Commando\AgentDashboardController::class, 'card'])->name('card');
    Route::get('/carte/download/pdf', [\App\Http\Controllers\Commando\AgentDashboardController::class, 'downloadPdf'])->name('card.download.pdf');
});

Route::prefix('admin')
    ->name('super-admin.')
    ->middleware(['auth', 'verified', 'super.admin'])
    ->group(function () {
        // Dashboard
        Route::get('/', [SuperAdminDashboardController::class, 'index'])->name('dashboard');

        // MenuPro Commando - Gestion des agents
        Route::prefix('commando')->name('commando.')->group(function () {
            Route::get('/agents', [\App\Http\Controllers\SuperAdmin\CommandoAgentController::class, 'index'])->name('agents.index');
            Route::get('/agents/{agent}', [\App\Http\Controllers\SuperAdmin\CommandoAgentController::class, 'show'])->name('agents.show');
            Route::post('/agents/{agent}/approve', [\App\Http\Controllers\SuperAdmin\CommandoAgentController::class, 'approve'])->name('agents.approve');
            Route::post('/agents/{agent}/reject', [\App\Http\Controllers\SuperAdmin\CommandoAgentController::class, 'reject'])->name('agents.reject');
            Route::post('/agents/{agent}/ban', [\App\Http\Controllers\SuperAdmin\CommandoAgentController::class, 'ban'])->name('agents.ban');
            Route::post('/agents/{agent}/commission', [\App\Http\Controllers\SuperAdmin\CommandoAgentController::class, 'addCommission'])->name('agents.commission');
            Route::post('/agents/{agent}/transactions/{transaction}/withdrawal-pay', [\App\Http\Controllers\SuperAdmin\CommandoAgentController::class, 'withdrawalPay'])->name('agents.withdrawal.pay');
            Route::post('/agents/{agent}/transactions/{transaction}/withdrawal-reject', [\App\Http\Controllers\SuperAdmin\CommandoAgentController::class, 'withdrawalReject'])->name('agents.withdrawal.reject');
            Route::delete('/agents/{agent}', [\App\Http\Controllers\SuperAdmin\CommandoAgentController::class, 'destroy'])->name('agents.destroy');
        });
        
        // Restaurants Management
        Route::get('restaurants/export', [RestaurantController::class, 'export'])->name('restaurants.export');
        Route::resource('restaurants', RestaurantController::class)->only(['index', 'show', 'update', 'destroy']);
        Route::post('restaurants/{restaurant}/approve', [RestaurantController::class, 'approve'])->name('restaurants.approve');
        Route::post('restaurants/{restaurant}/reject', [RestaurantController::class, 'reject'])->name('restaurants.reject');
        Route::post('restaurants/{restaurant}/suspend', [RestaurantController::class, 'suspend'])->name('restaurants.suspend');
        Route::post('restaurants/{restaurant}/reactivate', [RestaurantController::class, 'reactivate'])->name('restaurants.reactivate');
        Route::post('restaurants/{restaurant}/impersonate', [RestaurantController::class, 'impersonate'])->name('restaurants.impersonate');
        Route::post('restaurants/{restaurant}/extend-subscription', [RestaurantController::class, 'extendSubscription'])->name('restaurants.extend-subscription');
        Route::post('restaurants/{restaurant}/verify', [RestaurantController::class, 'verify'])->name('restaurants.verify');
        Route::post('restaurants/{restaurant}/unverify', [RestaurantController::class, 'unverify'])->name('restaurants.unverify');
        Route::post('restaurants/{restaurant}/add-commission', [RestaurantController::class, 'addCommission'])->name('restaurants.add-commission');
        
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
        
        // Transactions
        Route::get('transactions', [\App\Http\Controllers\SuperAdmin\TransactionController::class, 'index'])->name('transactions.index');
        Route::get('transactions/export', [\App\Http\Controllers\SuperAdmin\TransactionController::class, 'export'])->name('transactions.export');

        // Finances (Wallets, Payouts, Commissions)
        Route::get('finances', [\App\Http\Controllers\SuperAdmin\FinanceController::class, 'index'])->name('finances.index');
        Route::get('finances/retraits', [\App\Http\Controllers\SuperAdmin\FinanceController::class, 'payouts'])->name('finances.payouts');
        Route::get('finances/commissions', [\App\Http\Controllers\SuperAdmin\FinanceController::class, 'commissions'])->name('finances.commissions');
        
        // Announcements
        Route::resource('annonces', \App\Http\Controllers\SuperAdmin\AnnouncementController::class)->parameters(['annonces' => 'announcement'])->names('announcements');
        Route::post('annonces/{announcement}/send-emails', [\App\Http\Controllers\SuperAdmin\AnnouncementController::class, 'sendEmails'])->name('announcements.send-emails');
        
        // Live Dashboard API
        Route::get('api/live-stats', [SuperAdminDashboardController::class, 'liveStats'])->name('api.live-stats');
        // Sidebar badges + notifications (polling)
        Route::get('api/sidebar-badges', [\App\Http\Controllers\SuperAdmin\SidebarApiController::class, 'badges'])->name('api.sidebar-badges');
        Route::get('api/notifications', [\App\Http\Controllers\SuperAdmin\NotificationController::class, 'index'])->name('api.notifications');
        Route::post('api/notifications/mark-read', [\App\Http\Controllers\SuperAdmin\NotificationController::class, 'markAsRead'])->name('api.notifications.mark-read');
        
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
    
    // Order Status (secured with tracking token)
    Route::get('/commande/{token}', [OrderStatusController::class, 'show'])->name('order.status');
    Route::get('/commande/{token}/json', [OrderStatusController::class, 'status'])->name('order.status.json');
    
    // Reviews (secured with tracking token)
    Route::get('/commande/{token}/avis', [\App\Http\Controllers\Public\ReviewController::class, 'create'])->name('review.create');
    Route::post('/commande/{token}/avis', [\App\Http\Controllers\Public\ReviewController::class, 'store'])->name('review.store');
    
    // Reservations
    Route::post('/reservations', [\App\Http\Controllers\Public\ReservationController::class, 'store'])->name('reservations.store');
    
    // Payment Callbacks
    Route::get('/commande/{order}/success', [CheckoutController::class, 'success'])->name('order.success');
    Route::get('/commande/{order}/cancel', [CheckoutController::class, 'cancel'])->name('order.cancel');
    
    // Order Modifications (Public - secured with tracking token)
    Route::prefix('commande/{token}')->name('order.')->group(function () {
        Route::post('items', [\App\Http\Controllers\Public\OrderModificationController::class, 'addItem'])->name('items.add');
        Route::delete('items/{item}', [\App\Http\Controllers\Public\OrderModificationController::class, 'removeItem'])->name('items.remove');
        Route::patch('items/{item}', [\App\Http\Controllers\Public\OrderModificationController::class, 'updateItem'])->name('items.update');
    });
});

/*
|--------------------------------------------------------------------------
| Geocoding API Routes (Public)
|--------------------------------------------------------------------------
*/

Route::prefix('api/geocoding')->name('api.geocoding.')->group(function () {
    Route::get('/search', [\App\Http\Controllers\Public\GeocodingController::class, 'search'])->name('search');
    Route::get('/reverse', [\App\Http\Controllers\Public\GeocodingController::class, 'reverse'])->name('reverse');
});

/*
|--------------------------------------------------------------------------
| Webhook Routes
|--------------------------------------------------------------------------
*/

Route::prefix('webhooks')->withoutMiddleware(['web'])->group(function () {
    Route::post('/lygos', [LygosWebhookController::class, 'handle'])->name('webhooks.lygos');
    Route::post('/geniuspay', [GeniusPayWebhookController::class, 'handle'])->name('webhooks.geniuspay');
    Route::post('/menupo-hub/verify-payment', [MenuProHubWebhookController::class, 'verifyPayment'])->name('webhooks.menupo-hub.verify');
    Route::post('/wave/checkout', [WaveWebhookController::class, 'handleCheckout'])->name('webhooks.wave.checkout');
    Route::post('/fusionpay/payment', FusionPayPaymentWebhook::class)->name('webhooks.fusionpay.payment');
    Route::post('/fusionpay/payout', FusionPayPayoutWebhook::class)->name('webhooks.fusionpay.payout');
});

