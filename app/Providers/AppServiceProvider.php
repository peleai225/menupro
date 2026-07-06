<?php

namespace App\Providers;

use App\Models\Category;
use App\Models\CommandoAgent;
use App\Models\Dish;
use App\Models\Ingredient;
use App\Models\Order;
use App\Models\Reservation;
use App\Models\Restaurant;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Support\Facades\View;
use App\Observers\ActivityObserver;
use App\Observers\AdminNotificationObserver;
use App\Observers\OrderWhatsAppObserver;
use App\Observers\SubscriptionAdminObserver;
use App\Policies\CategoryPolicy;
use App\Policies\DishPolicy;
use App\Policies\IngredientPolicy;
use App\Policies\OrderPolicy;
use App\Policies\ReservationPolicy;
use App\Policies\RestaurantPolicy;
use App\Policies\UserPolicy;
use App\Events\DriverLocationUpdated;
use App\Events\NewDeliveryAvailable;
use App\Events\OrderStatusChanged;
use App\Listeners\BroadcastDriverLocation;
use App\Listeners\NotifyCustomerOnOrderStatusChange;
use App\Listeners\NotifyDriversOnNewDelivery;
use App\Services\DeliveryPricingService;
use App\Services\DriverAssignmentService;
use App\Services\GeocodingService;
use App\Services\MediaUploader;
use App\Services\PlanLimiter;
use App\Services\StockManager;
use App\Services\WalletService;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     */
    protected array $policies = [
        Restaurant::class => RestaurantPolicy::class,
        Category::class => CategoryPolicy::class,
        Dish::class => DishPolicy::class,
        Order::class => OrderPolicy::class,
        Ingredient::class => IngredientPolicy::class,
        User::class => UserPolicy::class,
        Reservation::class => ReservationPolicy::class,
    ];

    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Register services as singletons
        $this->app->singleton(MediaUploader::class);
        $this->app->singleton(PlanLimiter::class);
        $this->app->singleton(StockManager::class);
        $this->app->singleton(WalletService::class);
        $this->app->singleton(GeocodingService::class);
        $this->app->singleton(DeliveryPricingService::class);
        $this->app->singleton(DriverAssignmentService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force APP_URL and HTTPS for correct payment redirect URLs
        if (config('app.env') === 'production' && $appUrl = config('app.url')) {
            URL::forceRootUrl($appUrl);
            URL::forceScheme('https');
        }

        // Register policies
        foreach ($this->policies as $model => $policy) {
            Gate::policy($model, $policy);
        }

        // Define super admin gate
        Gate::before(function (User $user, string $ability) {
            // Super admins can do everything
            if ($user->isSuperAdmin()) {
                return true;
            }
            
            return null; // Let the policy decide
        });

        // Register activity observers for important models
        $this->registerActivityObservers();

        // Share sidebar counts with super-admin layout (pending restaurants + pending Commando agents)
        // Cache 60s pour éviter 2 COUNT à chaque rendu de page super-admin sans utilité temps-réel
        View::composer('components.layouts.admin-super', function ($view) {
            $view->with('pendingCommandoAgents',
                \Illuminate\Support\Facades\Cache::remember('pending_commando_agents', 60,
                    fn() => CommandoAgent::pendingReview()->count()
                )
            );
            $view->with('pendingRestaurants',
                \Illuminate\Support\Facades\Cache::remember('pending_restaurants', 60,
                    fn() => \App\Models\Restaurant::withoutGlobalScopes()
                        ->where('status', \App\Enums\RestaurantStatus::PENDING)
                        ->count()
                )
            );
        });

        // Invalider le cache des compteurs sidebar quand le statut d'un restaurant change
        \App\Models\Restaurant::updated(function (\App\Models\Restaurant $restaurant) {
            if ($restaurant->isDirty('status')) {
                \Illuminate\Support\Facades\Cache::forget('pending_restaurants');
            }
        });
        \App\Models\Restaurant::created(function () {
            \Illuminate\Support\Facades\Cache::forget('pending_restaurants');
        });

        // Invalider le cache des CommandoAgents en attente quand leur statut change
        CommandoAgent::updated(function (CommandoAgent $agent) {
            if ($agent->isDirty('status')) {
                \Illuminate\Support\Facades\Cache::forget('pending_commando_agents');
            }
        });

        // Load dynamic mail configuration from SystemSetting
        $this->loadDynamicMailConfig();

        // Platform delivery events
        Event::listen(NewDeliveryAvailable::class, NotifyDriversOnNewDelivery::class);
        Event::listen(OrderStatusChanged::class, NotifyCustomerOnOrderStatusChange::class);
        // DriverLocationUpdated implémente ShouldBroadcast directement — le listener BroadcastDriverLocation est superflu
        // Event::listen(DriverLocationUpdated::class, BroadcastDriverLocation::class);
    }

    /**
     * Register activity observers for important models.
     */
    protected function registerActivityObservers(): void
    {
        $modelsToObserve = [
            Restaurant::class,
            Order::class,
            Subscription::class,
            Category::class,
            Dish::class,
        ];

        foreach ($modelsToObserve as $model) {
            $model::observe(ActivityObserver::class);
        }

        Order::observe(OrderWhatsAppObserver::class);
        Order::observe(\App\Observers\OrderCustomerNotifyObserver::class);

        Restaurant::observe(AdminNotificationObserver::class);
        Subscription::observe(SubscriptionAdminObserver::class);

        Dish::observe(\App\Observers\MenuCacheObserver::class);
        Category::observe(\App\Observers\MenuCacheObserver::class);
    }

    /**
     * Load mail configuration dynamically from SystemSetting.
     * This allows Super Admin to configure SMTP from the interface.
     */
    protected function loadDynamicMailConfig(): void
    {
        try {
            $smtpConfig = Cache::remember('system.smtp_config', 300, function () {
                return [
                    'host'         => \App\Models\SystemSetting::get('smtp_host', ''),
                    'port'         => (int) \App\Models\SystemSetting::get('smtp_port', env('MAIL_PORT', 587)),
                    'username'     => \App\Models\SystemSetting::get('smtp_username', env('MAIL_USERNAME')),
                    'password'     => \App\Models\SystemSetting::get('smtp_password', env('MAIL_PASSWORD')),
                    'encryption'   => \App\Models\SystemSetting::get('smtp_encryption', env('MAIL_ENCRYPTION', 'tls')),
                    'from_address' => \App\Models\SystemSetting::get('smtp_from_address', env('MAIL_FROM_ADDRESS', 'hello@example.com')),
                    'from_name'    => \App\Models\SystemSetting::get('smtp_from_name', env('MAIL_FROM_NAME', config('app.name'))),
                ];
            });

            if (!empty($smtpConfig['host'])) {
                // Override mail configuration with database values
                config([
                    'mail.mailers.smtp.host'       => $smtpConfig['host'],
                    'mail.mailers.smtp.port'       => $smtpConfig['port'],
                    'mail.mailers.smtp.encryption' => $smtpConfig['encryption'],
                    'mail.mailers.smtp.username'   => $smtpConfig['username'],
                    'mail.mailers.smtp.password'   => $smtpConfig['password'],
                    'mail.from.address'            => $smtpConfig['from_address'],
                    'mail.from.name'               => $smtpConfig['from_name'],
                ]);

                // If SMTP is configured, use it as default mailer
                config(['mail.default' => 'smtp']);
            }
        } catch (\Exception $e) {
            // If SystemSetting table doesn't exist or there's an error, fall back to .env
            \Log::warning('Could not load dynamic mail config: ' . $e->getMessage());
        }
    }
}
