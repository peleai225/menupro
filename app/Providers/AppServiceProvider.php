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
use App\Observers\OrderWhatsAppObserver;
use App\Policies\CategoryPolicy;
use App\Policies\DishPolicy;
use App\Policies\IngredientPolicy;
use App\Policies\OrderPolicy;
use App\Policies\ReservationPolicy;
use App\Policies\RestaurantPolicy;
use App\Policies\UserPolicy;
use App\Services\LygosGateway;
use App\Services\MediaUploader;
use App\Services\PlanLimiter;
use App\Services\StockManager;
use App\Services\WalletService;
use App\Services\WaveCheckoutService;
use App\Services\WavePayoutService;
use App\Services\WaveSignatureService;
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
        $this->app->singleton(LygosGateway::class);
        $this->app->singleton(PlanLimiter::class);
        $this->app->singleton(StockManager::class);
        $this->app->singleton(WaveSignatureService::class);
        $this->app->singleton(WaveCheckoutService::class);
        $this->app->singleton(WavePayoutService::class);
        $this->app->singleton(WalletService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force APP_URL for correct payment redirect URLs (Lygos, Wave)
        if (config('app.env') === 'production' && $appUrl = config('app.url')) {
            URL::forceRootUrl($appUrl);
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
        View::composer('components.layouts.admin-super', function ($view) {
            $view->with('pendingCommandoAgents', CommandoAgent::pendingReview()->count());
            $view->with('pendingRestaurants', \App\Models\Restaurant::withoutGlobalScopes()->where('status', \App\Enums\RestaurantStatus::PENDING)->count());
        });

        // Load dynamic mail configuration from SystemSetting
        $this->loadDynamicMailConfig();
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
    }

    /**
     * Load mail configuration dynamically from SystemSetting.
     * This allows Super Admin to configure SMTP from the interface.
     */
    protected function loadDynamicMailConfig(): void
    {
        try {
            // Only override if we have SMTP settings in database
            $smtpHost = \App\Models\SystemSetting::get('smtp_host', '');
            
            if (!empty($smtpHost)) {
                // Override mail configuration with database values
                config([
                    'mail.mailers.smtp.host' => $smtpHost,
                    'mail.mailers.smtp.port' => (int) \App\Models\SystemSetting::get('smtp_port', env('MAIL_PORT', 587)),
                    'mail.mailers.smtp.encryption' => \App\Models\SystemSetting::get('smtp_encryption', env('MAIL_ENCRYPTION', 'tls')),
                    'mail.mailers.smtp.username' => \App\Models\SystemSetting::get('smtp_username', env('MAIL_USERNAME')),
                    'mail.mailers.smtp.password' => \App\Models\SystemSetting::get('smtp_password', env('MAIL_PASSWORD')),
                    'mail.from.address' => \App\Models\SystemSetting::get('smtp_from_address', env('MAIL_FROM_ADDRESS', 'hello@example.com')),
                    'mail.from.name' => \App\Models\SystemSetting::get('smtp_from_name', env('MAIL_FROM_NAME', 'Example')),
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
