<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\OrderStatus;
use App\Enums\RestaurantStatus;
use App\Http\Controllers\Controller;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\Subscription;
use App\Models\User;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        // Key metrics
        $stats = [
            'restaurants' => [
                'total' => Restaurant::count(),
                'active' => Restaurant::where('status', RestaurantStatus::ACTIVE)->count(),
                'pending' => Restaurant::where('status', RestaurantStatus::PENDING)->count(),
                'expired' => Restaurant::where('status', RestaurantStatus::EXPIRED)->count(),
            ],
            'users' => User::count(),
            'orders' => [
                'total' => Order::withoutGlobalScope('restaurant')->count(),
                'today' => Order::withoutGlobalScope('restaurant')->whereDate('created_at', today())->count(),
                'this_month' => Order::withoutGlobalScope('restaurant')
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->count(),
            ],
            'revenue' => [
                'total' => Order::withoutGlobalScope('restaurant')
                    ->where('payment_status', 'completed')
                    ->sum('total'),
                'this_month' => Order::withoutGlobalScope('restaurant')
                    ->where('payment_status', 'completed')
                    ->whereMonth('created_at', now()->month)
                    ->whereYear('created_at', now()->year)
                    ->sum('total'),
            ],
        ];

        // Recent restaurants
        $recentRestaurants = Restaurant::with('currentPlan')
            ->latest()
            ->limit(5)
            ->get();

        // Pending validations
        $pendingRestaurants = Restaurant::where('status', RestaurantStatus::PENDING)
            ->with('owner')
            ->latest()
            ->limit(10)
            ->get();

        // Expiring subscriptions (next 7 days)
        $expiringSubscriptions = Subscription::where('status', 'active')
            ->where('ends_at', '<=', now()->addDays(7))
            ->where('ends_at', '>', now())
            ->with('restaurant', 'plan')
            ->orderBy('ends_at')
            ->limit(10)
            ->get();

        // Revenue by plan (this month)
        $revenueByPlan = Subscription::query()
            ->join('plans', 'subscriptions.plan_id', '=', 'plans.id')
            ->where('subscriptions.status', 'active')
            ->whereMonth('subscriptions.created_at', now()->month)
            ->select('plans.name', DB::raw('SUM(subscriptions.amount_paid) as total'))
            ->groupBy('plans.name')
            ->get();

        // Top restaurants by orders this month
        $topRestaurants = Order::withoutGlobalScope('restaurant')
            ->join('restaurants', 'orders.restaurant_id', '=', 'restaurants.id')
            ->where('orders.payment_status', 'completed')
            ->whereMonth('orders.created_at', now()->month)
            ->select(
                'restaurants.id',
                'restaurants.name',
                'restaurants.slug',
                DB::raw('COUNT(*) as orders_count'),
                DB::raw('SUM(orders.total) as revenue')
            )
            ->groupBy('restaurants.id', 'restaurants.name', 'restaurants.slug')
            ->orderByDesc('revenue')
            ->limit(5)
            ->get();

        return view('pages.super-admin.dashboard', compact(
            'stats',
            'recentRestaurants',
            'pendingRestaurants',
            'expiringSubscriptions',
            'revenueByPlan',
            'topRestaurants'
        ));
    }

    /**
     * Display system settings.
     */
    public function settings(): View
    {
        // Get default values from config or use sensible defaults
        $defaultAppName = config('app.name') ?: 'MenuPro';
        $defaultAppUrl = config('app.url') ?: 'http://127.0.0.1:8000';
        $defaultContactEmail = config('mail.from.address') ?: 'contact@menupro.ci';
        
        // Ensure values are never empty
        $appName = \App\Models\SystemSetting::get('app_name', $defaultAppName);
        $appUrl = \App\Models\SystemSetting::get('app_url', $defaultAppUrl);
        $contactEmail = \App\Models\SystemSetting::get('contact_email', $defaultContactEmail);
        
        $settings = [
            'app_name' => !empty($appName) ? $appName : $defaultAppName,
            'app_url' => !empty($appUrl) ? $appUrl : $defaultAppUrl,
            'contact_email' => !empty($contactEmail) ? $contactEmail : $defaultContactEmail,
            'maintenance_mode' => \App\Models\SystemSetting::get('maintenance_mode', false),
            'registrations_open' => \App\Models\SystemSetting::get('registrations_open', true),
            'lygos_enabled' => \App\Models\SystemSetting::get('lygos_enabled', true),
            'lygos_api_key' => \App\Models\SystemSetting::get('lygos_api_key', ''),
            'lygos_webhook_secret' => \App\Models\SystemSetting::get('lygos_webhook_secret', ''),
            'lygos_mode' => \App\Models\SystemSetting::get('lygos_mode', 'live'),
            'geniuspay_api_key' => \App\Models\SystemSetting::get('geniuspay_api_key', ''),
            'geniuspay_api_secret' => \App\Models\SystemSetting::get('geniuspay_api_secret', ''),
            'geniuspay_webhook_secret' => \App\Models\SystemSetting::get('geniuspay_webhook_secret', ''),
            'geniuspay_mode' => \App\Models\SystemSetting::get('geniuspay_mode', 'sandbox'),
            'geoapify_api_key' => \App\Models\SystemSetting::get('geoapify_api_key', ''),
            'smtp_host' => \App\Models\SystemSetting::get('smtp_host', config('mail.mailers.smtp.host', '')),
            'smtp_port' => \App\Models\SystemSetting::get('smtp_port', config('mail.mailers.smtp.port', '587')),
            'smtp_encryption' => \App\Models\SystemSetting::get('smtp_encryption', config('mail.mailers.smtp.encryption', 'tls')),
            'smtp_username' => \App\Models\SystemSetting::get('smtp_username', config('mail.mailers.smtp.username', '')),
            'smtp_from_address' => \App\Models\SystemSetting::get('smtp_from_address', config('mail.from.address', '')),
            'smtp_from_name' => \App\Models\SystemSetting::get('smtp_from_name', config('mail.from.name', '')),
            'require_2fa' => \App\Models\SystemSetting::get('require_2fa', false),
            'log_logins' => \App\Models\SystemSetting::get('log_logins', true),
            'logo' => \App\Models\SystemSetting::get('logo', ''),
            'favicon' => \App\Models\SystemSetting::get('favicon', ''),
            'hero_image' => \App\Models\SystemSetting::get('hero_image', ''),
            'social_facebook' => \App\Models\SystemSetting::get('social_facebook', ''),
            'social_twitter' => \App\Models\SystemSetting::get('social_twitter', ''),
            'social_instagram' => \App\Models\SystemSetting::get('social_instagram', ''),
            'social_linkedin' => \App\Models\SystemSetting::get('social_linkedin', ''),
            'footer_text' => \App\Models\SystemSetting::get('footer_text', '© ' . date('Y') . ' MenuPro. Tous droits réservés.'),
            // Commando – commissions agents (priorité au backoffice, sinon config)
            'commando_commission_cents_first_payment' => \App\Models\SystemSetting::has('commando_commission_cents_first_payment')
                ? (int) \App\Models\SystemSetting::get('commando_commission_cents_first_payment', config('commando.commission_cents_first_payment', 500000))
                : (int) config('commando.commission_cents_first_payment', 500000),
            'commando_commission_only_first_payment' => \App\Models\SystemSetting::has('commando_commission_only_first_payment')
                ? \App\Models\SystemSetting::get('commando_commission_only_first_payment', true)
                : config('commando.commission_only_first_payment', true),
        ];

        return view('pages.super-admin.settings', compact('settings'));
    }

    /**
     * Update system settings.
     */
    public function updateSettings(Request $request): RedirectResponse
    {
        // Get current values or defaults
        $defaultAppName = config('app.name') ?: 'MenuPro';
        $defaultAppUrl = config('app.url') ?: 'http://127.0.0.1:8000';
        $defaultContactEmail = config('mail.from.address') ?: 'contact@menupro.ci';
        
        $request->validate([
            'app_name' => ['nullable', 'string', 'max:255'],
            'app_url' => ['nullable', 'url'],
            'contact_email' => ['nullable', 'email'],
            'contact_phone' => ['nullable', 'string', 'max:30'],
            'maintenance_mode' => ['boolean'],
            'registrations_open' => ['boolean'],
            'lygos_api_key' => ['nullable', 'string'],
            'lygos_webhook_secret' => ['nullable', 'string'],
            'lygos_mode' => ['nullable', 'in:test,live'],
            'geniuspay_api_key' => ['nullable', 'string'],
            'geniuspay_api_secret' => ['nullable', 'string'],
            'geniuspay_webhook_secret' => ['nullable', 'string'],
            'geniuspay_mode' => ['nullable', 'in:sandbox,live'],
            'geoapify_api_key' => ['nullable', 'string'],
            'smtp_host' => ['nullable', 'string', 'max:255'],
            'smtp_port' => ['nullable', 'integer', 'min:1', 'max:65535'],
            'smtp_encryption' => ['nullable', 'string', 'in:tls,ssl,'],
            'smtp_username' => ['nullable', 'string', 'max:255'],
            'smtp_password' => ['nullable', 'string', 'max:255'],
            'smtp_from_address' => ['nullable', 'email'],
            'smtp_from_name' => ['nullable', 'string', 'max:255'],
            'require_2fa' => ['boolean'],
            'log_logins' => ['boolean'],
            'logo' => ['nullable', 'image', 'max:2048'],
            'favicon' => ['nullable', 'image', 'max:512'],
            'hero_image' => ['nullable', 'image', 'max:5120'],
            'social_facebook' => ['nullable', 'url'],
            'social_twitter' => ['nullable', 'url'],
            'social_instagram' => ['nullable', 'url'],
            'social_linkedin' => ['nullable', 'url'],
            'footer_text' => ['nullable', 'string', 'max:500'],
            'commando_commission_fcfa_first_payment' => ['nullable', 'numeric', 'min:0'],
            'commando_commission_only_first_payment' => ['boolean'],
        ]);

        // Save settings (only if provided, otherwise keep existing or use defaults)
        if ($request->filled('app_name')) {
            \App\Models\SystemSetting::set('app_name', $request->app_name, 'string', 'Nom de la plateforme');
        } elseif (!\App\Models\SystemSetting::has('app_name')) {
            \App\Models\SystemSetting::set('app_name', $defaultAppName, 'string', 'Nom de la plateforme');
        }
        
        if ($request->filled('app_url')) {
            \App\Models\SystemSetting::set('app_url', $request->app_url, 'string', 'URL de base');
        } elseif (!\App\Models\SystemSetting::has('app_url')) {
            \App\Models\SystemSetting::set('app_url', $defaultAppUrl, 'string', 'URL de base');
        }
        
        if ($request->filled('contact_email')) {
            \App\Models\SystemSetting::set('contact_email', $request->contact_email, 'string', 'Email de contact');
        } elseif (!\App\Models\SystemSetting::has('contact_email')) {
            \App\Models\SystemSetting::set('contact_email', $defaultContactEmail, 'string', 'Email de contact');
        }
        
        // Contact phone (always save, even if empty to allow clearing)
        \App\Models\SystemSetting::set('contact_phone', $request->contact_phone ?? '', 'string', 'Téléphone de contact');
        
        \App\Models\SystemSetting::set('maintenance_mode', $request->boolean('maintenance_mode'), 'boolean', 'Mode maintenance');
        \App\Models\SystemSetting::set('registrations_open', $request->boolean('registrations_open'), 'boolean', 'Inscriptions ouvertes');
        if ($request->has('lygos_api_key')) {
            \App\Models\SystemSetting::set('lygos_enabled', $request->boolean('lygos_enabled'), 'boolean', 'Activer Lygos (abonnements)');
        }
        if ($request->filled('lygos_api_key')) {
            \App\Models\SystemSetting::set('lygos_api_key', $request->lygos_api_key, 'string', 'Clé API Lygos');
        }
        if ($request->filled('lygos_webhook_secret')) {
            \App\Models\SystemSetting::set('lygos_webhook_secret', $request->lygos_webhook_secret, 'string', 'Secret webhook Lygos');
        }
        if ($request->filled('lygos_mode')) {
            \App\Models\SystemSetting::set('lygos_mode', $request->lygos_mode, 'string', 'Mode Lygos (test/live)');
        }
        if ($request->filled('geniuspay_api_key')) {
            \App\Models\SystemSetting::set('geniuspay_api_key', $request->geniuspay_api_key, 'string', 'Clé API GeniusPay');
        }
        if ($request->filled('geniuspay_api_secret')) {
            \App\Models\SystemSetting::set('geniuspay_api_secret', $request->geniuspay_api_secret, 'string', 'Secret API GeniusPay');
        }
        if ($request->filled('geniuspay_webhook_secret')) {
            \App\Models\SystemSetting::set('geniuspay_webhook_secret', $request->geniuspay_webhook_secret, 'string', 'Secret webhook GeniusPay');
        }
        if ($request->filled('geniuspay_mode')) {
            \App\Models\SystemSetting::set('geniuspay_mode', $request->geniuspay_mode, 'string', 'Mode GeniusPay (sandbox/live)');
        }
        if ($request->filled('geoapify_api_key')) {
            \App\Models\SystemSetting::set('geoapify_api_key', $request->geoapify_api_key, 'string', 'Clé API Geoapify (géocodage d\'adresses)');
        }
        // SMTP Configuration
        \App\Models\SystemSetting::set('smtp_host', $request->smtp_host ?? '', 'string', 'Serveur SMTP');
        \App\Models\SystemSetting::set('smtp_port', $request->smtp_port ?? 587, 'integer', 'Port SMTP');
        \App\Models\SystemSetting::set('smtp_encryption', $request->smtp_encryption ?? 'tls', 'string', 'Chiffrement SMTP');
        \App\Models\SystemSetting::set('smtp_username', $request->smtp_username ?? '', 'string', 'Nom d\'utilisateur SMTP');
        if ($request->filled('smtp_password')) {
            \App\Models\SystemSetting::set('smtp_password', $request->smtp_password, 'string', 'Mot de passe SMTP');
        }
        \App\Models\SystemSetting::set('smtp_from_address', $request->smtp_from_address ?? '', 'string', 'Email expéditeur');
        \App\Models\SystemSetting::set('smtp_from_name', $request->smtp_from_name ?? '', 'string', 'Nom expéditeur');
        \App\Models\SystemSetting::set('require_2fa', $request->boolean('require_2fa'), 'boolean', 'Double authentification obligatoire');
        \App\Models\SystemSetting::set('log_logins', $request->boolean('log_logins'), 'boolean', 'Log des connexions');

        // Handle logo upload
        if ($request->hasFile('logo')) {
            $logoPath = $request->file('logo')->store('system', 'public');
            // Delete old logo if exists
            $oldLogo = \App\Models\SystemSetting::get('logo', '');
            if ($oldLogo && Storage::disk('public')->exists($oldLogo)) {
                Storage::disk('public')->delete($oldLogo);
            }
            \App\Models\SystemSetting::set('logo', $logoPath, 'string', 'Logo de la plateforme');
        }

        // Handle favicon upload
        if ($request->hasFile('favicon')) {
            $faviconPath = $request->file('favicon')->store('system', 'public');
            // Delete old favicon if exists
            $oldFavicon = \App\Models\SystemSetting::get('favicon', '');
            if ($oldFavicon && Storage::disk('public')->exists($oldFavicon)) {
                Storage::disk('public')->delete($oldFavicon);
            }
            \App\Models\SystemSetting::set('favicon', $faviconPath, 'string', 'Favicon de la plateforme');
        }

        // Handle hero image upload
        if ($request->hasFile('hero_image')) {
            $heroImagePath = $request->file('hero_image')->store('system', 'public');
            // Delete old hero image if exists
            $oldHeroImage = \App\Models\SystemSetting::get('hero_image', '');
            if ($oldHeroImage && Storage::disk('public')->exists($oldHeroImage)) {
                Storage::disk('public')->delete($oldHeroImage);
            }
            \App\Models\SystemSetting::set('hero_image', $heroImagePath, 'string', 'Image hero de la page d\'accueil');
        }

        // Social links (save even if empty to allow clearing)
        \App\Models\SystemSetting::set('social_facebook', $request->social_facebook ?? '', 'string', 'Lien Facebook');
        \App\Models\SystemSetting::set('social_twitter', $request->social_twitter ?? '', 'string', 'Lien Twitter');
        \App\Models\SystemSetting::set('social_instagram', $request->social_instagram ?? '', 'string', 'Lien Instagram');
        \App\Models\SystemSetting::set('social_linkedin', $request->social_linkedin ?? '', 'string', 'Lien LinkedIn');
        
        if ($request->filled('footer_text')) {
            \App\Models\SystemSetting::set('footer_text', $request->footer_text, 'string', 'Texte du footer');
        }

        // Commando – commissions (sauvegardés si présents dans la requête, montant saisi en FCFA → stocké en centimes)
        if ($request->has('commando_commission_fcfa_first_payment')) {
            $fcfa = (float) ($request->commando_commission_fcfa_first_payment ?: 0);
            \App\Models\SystemSetting::set(
                'commando_commission_cents_first_payment',
                (int) round($fcfa * 100),
                'integer',
                'Commission (centimes) versée à l\'agent au premier paiement d\'un restaurant parrainé'
            );
        }
        if ($request->has('commando_commission_only_first_payment')) {
            \App\Models\SystemSetting::set(
                'commando_commission_only_first_payment',
                $request->boolean('commando_commission_only_first_payment'),
                'boolean',
                'Commission uniquement au premier paiement par restaurant parrainé'
            );
        }

        return back()->with('success', 'Paramètres mis à jour avec succès.');
    }

    /**
     * Get live stats for real-time dashboard.
     */
    public function liveStats()
    {
        // Recent orders (last 5 minutes)
        $recentOrders = Order::withoutGlobalScope('restaurant')
            ->with('restaurant:id,name')
            ->where('created_at', '>=', now()->subMinutes(5))
            ->latest()
            ->limit(10)
            ->get()
            ->map(fn($order) => [
                'id' => $order->id,
                'reference' => $order->reference,
                'restaurant' => $order->restaurant?->name ?? 'N/A',
                'total' => $order->total,
                'status' => $order->status->value,
                'status_label' => $order->status->label(),
                'type' => $order->type->value,
                'created_at' => $order->created_at->format('H:i:s'),
            ]);

        // Live stats
        $stats = [
            'orders_today' => Order::withoutGlobalScope('restaurant')->whereDate('created_at', today())->count(),
            'revenue_today' => Order::withoutGlobalScope('restaurant')
                ->where('payment_status', 'completed')
                ->whereDate('paid_at', today())
                ->sum('total'),
            'pending_orders' => Order::withoutGlobalScope('restaurant')
                ->whereIn('status', ['pending', 'confirmed', 'preparing'])
                ->count(),
            'active_restaurants' => Restaurant::where('status', 'active')->count(),
            'new_registrations_today' => User::whereDate('created_at', today())->count(),
        ];

        // Orders by status (for pie chart)
        $ordersByStatus = Order::withoutGlobalScope('restaurant')
            ->whereDate('created_at', today())
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->mapWithKeys(fn($item) => [$item->status => $item->count]);

        // Hourly orders (for line chart)
        $hourlyOrders = [];
        for ($i = 0; $i < 24; $i++) {
            $count = Order::withoutGlobalScope('restaurant')
                ->whereDate('created_at', today())
                ->whereRaw('HOUR(created_at) = ?', [$i])
                ->count();
            $hourlyOrders[] = ['hour' => sprintf('%02d:00', $i), 'count' => $count];
        }

        return response()->json([
            'stats' => $stats,
            'recent_orders' => $recentOrders,
            'orders_by_status' => $ordersByStatus,
            'hourly_orders' => $hourlyOrders,
            'timestamp' => now()->format('H:i:s'),
        ]);
    }
}

