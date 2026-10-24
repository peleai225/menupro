<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\OrderStatus;
use App\Enums\RestaurantStatus;
use App\Http\Controllers\Controller;
use App\Models\Restaurant;
use App\Models\User;
use App\Services\PlatformMetrics;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(PlatformMetrics $metrics): View
    {
        // Toutes les métriques passent par PlatformMetrics : restaurants démo et supprimés
        // exclus, montants en FCFA, bornes de dates calculées en PHP (portable MySQL/SQLite).
        $todayStart = now()->startOfDay();
        $monthStart = now()->startOfMonth();

        // Restaurants : répartition calculée sur les abonnements valides (et non sur
        // restaurants.status qui comptait les essais comme actifs et n'expirait jamais un essai).
        $restaurantCounts = $metrics->restaurantSubscriptionCounts();

        // Volume de commandes : commandes réellement passées (hors brouillon / attente de
        // paiement) ; les annulées sont affichées à part.
        $placedOrders = $metrics->placedOrders();

        $stats = [
            'restaurants' => [
                'total'   => $restaurantCounts['total'],
                // Actifs = abonnement payant OU essai encore valide (ends_at > maintenant),
                // restaurants non démo et non supprimés
                'active'  => $restaurantCounts['paying'] + $restaurantCounts['trial'],
                'paying'  => $restaurantCounts['paying'],
                'trial'   => $restaurantCounts['trial'],
                'pending' => $restaurantCounts['pending'],
                'expired' => $restaurantCounts['expired'],
            ],
            'users' => $this->realUsers($metrics)->count(),
            'orders' => [
                'total'      => (clone $placedOrders)->count(),
                'cancelled'  => (clone $placedOrders)->whereIn('orders.status', [OrderStatus::CANCELLED, OrderStatus::REFUNDED])->count(),
                'today'      => (clone $placedOrders)->where('orders.created_at', '>=', $todayStart)->count(),
                'this_month' => (clone $placedOrders)->where('orders.created_at', '>=', $monthStart)->count(),
            ],
            'revenue' => [
                // GMV : volume des commandes payées (argent des restaurants, pas celui de MenuPro)
                'gmv_total'      => $metrics->gmv(),
                'gmv_this_month' => $metrics->gmv($monthStart),
                // MRR : revenu récurrent mensuel des abonnements MenuPro
                'mrr'            => $metrics->mrr(),
            ],
        ];

        // Recent restaurants
        $recentRestaurants = $metrics->restaurants()
            ->with(['currentPlan', 'owner'])
            ->latest()
            ->limit(5)
            ->get();

        // Pending validations
        $pendingRestaurants = $metrics->restaurants()
            ->where('status', RestaurantStatus::PENDING)
            ->with('owner')
            ->latest()
            ->limit(10)
            ->get();

        // Expirent bientôt : dernier abonnement valide de chaque restaurant (essais inclus)
        $expiringSubscriptions = $metrics->expiringSubscriptions(7, 10);

        // Revenus par plan (ce mois) : abonnements réellement encaissés depuis le 1er du mois
        $revenueByPlan = $metrics->collectedSubscriptionsBetween($monthStart, now())
            ->join('plans', 'subscriptions.plan_id', '=', 'plans.id')
            ->select('plans.name', DB::raw('SUM(subscriptions.amount_paid) as total'))
            ->groupBy('plans.name')
            ->orderByDesc('total')
            ->get();

        // Top restaurants du mois par CA (commandes payées non annulées, sur paid_at)
        $topRevenue = $metrics->revenueOrders()
            ->where('orders.paid_at', '>=', $monthStart)
            ->select('orders.restaurant_id', DB::raw('COUNT(*) as orders_count'), DB::raw('SUM(orders.total) as revenue'))
            ->groupBy('orders.restaurant_id')
            ->orderByDesc('revenue')
            ->limit(5)
            ->get();
        $topNames = Restaurant::whereIn('id', $topRevenue->pluck('restaurant_id'))->get(['id', 'name', 'slug'])->keyBy('id');
        $topRestaurants = $topRevenue->map(fn ($row) => (object) [
            'id'           => $row->restaurant_id,
            'name'         => $topNames[$row->restaurant_id]->name ?? '—',
            'slug'         => $topNames[$row->restaurant_id]->slug ?? null,
            'orders_count' => (int) $row->orders_count,
            'revenue'      => (int) $row->revenue,
        ]);

        // Courbe 7 jours : commandes passées (par date de création) et CA (par date de paiement)
        $chartStart = now()->subDays(6)->startOfDay();
        $countsByDay = $metrics->placedOrders()
            ->where('orders.created_at', '>=', $chartStart)
            ->selectRaw('DATE(orders.created_at) as day, COUNT(*) as count')
            ->groupBy('day')
            ->pluck('count', 'day');
        $revenueByDay = $metrics->revenueOrders()
            ->where('orders.paid_at', '>=', $chartStart)
            ->selectRaw('DATE(orders.paid_at) as day, SUM(orders.total) as revenue')
            ->groupBy('day')
            ->pluck('revenue', 'day');

        $daysFr = ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'];
        $ordersByDay = ['labels' => [], 'counts' => [], 'revenues' => []];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $dayOfWeek = (int) now()->subDays($i)->format('w');
            $ordersByDay['labels'][] = $daysFr[$dayOfWeek];
            $ordersByDay['counts'][] = (int) ($countsByDay[$date] ?? 0);
            $ordersByDay['revenues'][] = (float) ($revenueByDay[$date] ?? 0);
        }

        // Delivery driver stats (cash flow)
        $driverStats = [
            'total_drivers'       => \App\Models\DeliveryDriver::count(),
            // En ligne = disponible ET position reçue il y a moins de 10 min
            'online_drivers'      => \App\Models\DeliveryDriver::where('is_available', true)
                ->where('location_updated_at', '>=', now()->subMinutes(PlatformMetrics::DRIVER_ONLINE_MINUTES))
                ->count(),
            'cash_pending_orders' => DB::table('orders')->where('payout_status', 'cash_pending')->count(),
            'cash_pending_amount' => (int) DB::table('orders')->where('payout_status', 'cash_pending')->sum('total'),
            'cash_debts_total'    => (int) DB::table('driver_cash_debts')->where('status', 'pending')->sum('amount_xof'),
            'remittances_pending' => DB::table('driver_cash_remittances')->where('status', 'pending')->count(),
        ];

        // Donut : commandes passées par statut réel, libellés français de l'enum
        $ordersByStatusRaw = $metrics->placedOrders()
            ->selectRaw('orders.status as status_value, COUNT(*) as count')
            ->groupBy('orders.status')
            ->pluck('count', 'status_value');

        $ordersByStatus = ['labels' => [], 'counts' => [], 'statuses' => []];
        foreach (OrderStatus::cases() as $status) {
            $count = (int) ($ordersByStatusRaw[$status->value] ?? 0);
            if ($count === 0) {
                continue;
            }
            $ordersByStatus['labels'][] = $status->label();
            $ordersByStatus['counts'][] = $count;
            $ordersByStatus['statuses'][] = $status->value;
        }

        return view('pages.super-admin.dashboard', compact(
            'stats',
            'recentRestaurants',
            'pendingRestaurants',
            'expiringSubscriptions',
            'revenueByPlan',
            'topRestaurants',
            'ordersByDay',
            'ordersByStatus',
            'driverStats'
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
            'geoapify_api_key' => \App\Models\SystemSetting::get('geoapify_api_key', ''),
            'elevenlabs_api_key' => \App\Models\SystemSetting::get('elevenlabs_api_key', ''),
            'elevenlabs_voice_id' => \App\Models\SystemSetting::get('elevenlabs_voice_id', ''),
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
            'hero_image'   => \App\Models\SystemSetting::get('hero_image', ''),
            'hero_image_2' => \App\Models\SystemSetting::get('hero_image_2', ''),
            'hero_image_3' => \App\Models\SystemSetting::get('hero_image_3', ''),
            'apk_download_url' => \App\Models\SystemSetting::get('apk_download_url', ''),
            'social_facebook' => \App\Models\SystemSetting::get('social_facebook', ''),
            'social_twitter' => \App\Models\SystemSetting::get('social_twitter', ''),
            'social_instagram' => \App\Models\SystemSetting::get('social_instagram', ''),
            'social_linkedin' => \App\Models\SystemSetting::get('social_linkedin', ''),
            'footer_text' => \App\Models\SystemSetting::get('footer_text', '© ' . date('Y') . ' MenuPro. Tous droits réservés.'),
            'home_videos' => \App\Models\SystemSetting::get('home_videos', []),
            // Marketing – bannière + Facebook Pixel + Google Analytics
            'banner_enabled' => \App\Models\SystemSetting::get('banner_enabled', false),
            'banner_text' => \App\Models\SystemSetting::get('banner_text', ''),
            'banner_link' => \App\Models\SystemSetting::get('banner_link', ''),
            'banner_color' => \App\Models\SystemSetting::get('banner_color', 'primary'),
            'facebook_app_id'    => \App\Models\SystemSetting::get('facebook_app_id', ''),
            'facebook_pixel_id'  => \App\Models\SystemSetting::get('facebook_pixel_id', ''),
            'google_analytics_id' => \App\Models\SystemSetting::get('google_analytics_id', ''),
            // Commando – commissions agents (priorité au backoffice, sinon config)
            'commando_commission_cents_first_payment' => \App\Models\SystemSetting::has('commando_commission_cents_first_payment')
                ? (int) \App\Models\SystemSetting::get('commando_commission_cents_first_payment', config('commando.commission_cents_first_payment', 500000))
                : (int) config('commando.commission_cents_first_payment', 500000),
            'commando_commission_only_first_payment' => \App\Models\SystemSetting::has('commando_commission_only_first_payment')
                ? \App\Models\SystemSetting::get('commando_commission_only_first_payment', true)
                : config('commando.commission_only_first_payment', true),
            // Commissions par grade (ROOKIE/COMMANDO/ELITE)
            'commando_commission_rookie_cents'   => \App\Models\SystemSetting::has('commando_commission_rookie_cents')
                ? (int) \App\Models\SystemSetting::get('commando_commission_rookie_cents', 300000)
                : null,
            'commando_commission_commando_cents' => \App\Models\SystemSetting::has('commando_commission_commando_cents')
                ? (int) \App\Models\SystemSetting::get('commando_commission_commando_cents', 500000)
                : null,
            'commando_commission_elite_cents'    => \App\Models\SystemSetting::has('commando_commission_elite_cents')
                ? (int) \App\Models\SystemSetting::get('commando_commission_elite_cents', 700000)
                : null,
            // MoneyFusion
            'moneyfusion_active'  => (bool) \App\Models\SystemSetting::get('moneyfusion_active', true),
            'moneyfusion_api_url' => \App\Models\SystemSetting::get('moneyfusion_api_url', config('moneyfusion.api_url', '')),
            'moneyfusion_api_key' => \App\Models\SystemSetting::get('moneyfusion_api_key', config('moneyfusion.api_key', '')),
            // Wave CI
            'wave_api_key' => \App\Models\SystemSetting::get('wave_api_key', config('wave.api_key', '')),
            'wave_webhook_secret' => \App\Models\SystemSetting::get('wave_webhook_secret', config('wave.webhook_secret', '')),
            // WhatsApp Business API (Meta)
            'whatsapp_enabled' => \App\Models\SystemSetting::get('whatsapp_enabled', config('services.whatsapp.enabled', false)),
            'whatsapp_phone_id' => \App\Models\SystemSetting::get('whatsapp_phone_id', config('services.whatsapp.phone_id', '')),
            'whatsapp_api_key' => \App\Models\SystemSetting::get('whatsapp_api_key', config('services.whatsapp.api_key', '')),
            // Twilio WhatsApp (OTP + notifications)
            'twilio_sid' => \App\Models\SystemSetting::get('twilio_sid', config('twilio.sid', '')),
            'twilio_auth_token' => \App\Models\SystemSetting::get('twilio_auth_token', config('twilio.token', '')),
            'twilio_whatsapp_from' => \App\Models\SystemSetting::get('twilio_whatsapp_from', config('twilio.whatsapp_from', 'whatsapp:+14155238886')),
            // Mapbox
            'mapbox_public_token' => \App\Models\SystemSetting::get('mapbox_public_token', config('services.mapbox.public_token', '')),
            'mapbox_style' => \App\Models\SystemSetting::get('mapbox_style', 'streets-v12'),
            // Pusher (temps réel — app livreur)
            'pusher_app_id'  => \App\Models\SystemSetting::get('pusher_app_id', ''),
            'pusher_key'     => \App\Models\SystemSetting::get('pusher_key', ''),
            'pusher_secret'  => \App\Models\SystemSetting::get('pusher_secret', ''),
            'pusher_cluster' => \App\Models\SystemSetting::get('pusher_cluster', 'ap2'),
            // Firebase FCM
            'firebase_server_key' => \App\Models\SystemSetting::get('firebase_server_key', ''),
            'firebase_project_id' => \App\Models\SystemSetting::get('firebase_project_id', ''),
            'firebase_service_account_json' => \App\Models\SystemSetting::get('firebase_service_account_json', ''),
            'firebase_api_key' => \App\Models\SystemSetting::get('firebase_api_key', ''),
            'firebase_app_id' => \App\Models\SystemSetting::get('firebase_app_id', ''),
            'firebase_sender_id' => \App\Models\SystemSetting::get('firebase_sender_id', ''),
            'firebase_vapid_key' => \App\Models\SystemSetting::get('firebase_vapid_key', ''),
        ];

        $paymentSettings = \App\Models\SystemPaymentSetting::orderBy('gateway')->get()->keyBy('gateway');

        return view('pages.super-admin.settings', compact('settings', 'paymentSettings'));
    }

    /**
     * Delete one of the 3 hero image slots.
     */
    public function deleteHeroSlot(int $slot): RedirectResponse
    {
        $key  = $slot === 1 ? 'hero_image' : "hero_image_{$slot}";
        $path = \App\Models\SystemSetting::get($key, '');
        if ($path && Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
        \App\Models\SystemSetting::set($key, '', 'string', 'Image hero page d\'accueil');

        return back()->with('success', "Image hero {$slot} supprimée avec succès.");
    }

    /**
     * Upload Android APK to public/downloads/menupro.apk.
     */
    public function uploadApk(\Illuminate\Http\Request $request): RedirectResponse
    {
        $request->validate([
            'apk_file' => ['required', 'file', 'max:153600'], // 150 MB max
        ], [
            'apk_file.required' => 'Veuillez sélectionner un fichier APK.',
            'apk_file.max'      => 'Le fichier ne doit pas dépasser 150 Mo.',
        ]);

        if (strtolower($request->file('apk_file')->getClientOriginalExtension()) !== 'apk') {
            return back()->withErrors(['apk_file' => 'Le fichier doit être un APK Android (.apk).'])->withFragment('apk');
        }

        $dest = public_path('downloads');
        if (! is_dir($dest)) {
            mkdir($dest, 0755, true);
        }

        $request->file('apk_file')->move($dest, 'menupro.apk');

        \App\Models\SystemSetting::set('apk_uploaded_at', now()->toIso8601String(), 'string', 'Date dernier upload APK');
        \App\Models\SystemSetting::set('apk_original_name', $request->file('apk_file')->getClientOriginalName(), 'string', 'Nom original APK');

        return back()->with('success', 'APK mis en ligne avec succès. Les utilisateurs Android peuvent maintenant le télécharger.')->withFragment('apk');
    }

    /**
     * Delete hero image.
     */
    public function deleteHeroImage(): RedirectResponse
    {
        $heroImage = \App\Models\SystemSetting::get('hero_image', '');
        if ($heroImage && Storage::disk('public')->exists($heroImage)) {
            Storage::disk('public')->delete($heroImage);
        }
        \App\Models\SystemSetting::set('hero_image', '', 'string', 'Image hero de la page d\'accueil');

        return back()->with('success', 'Image hero supprimée avec succès.');
    }

    /**
     * Update system settings.
     */
    public function updateSettings(Request $request): RedirectResponse
    {
        try {
        // Get current values or defaults
        $defaultAppName = config('app.name') ?: 'MenuPro';
        $defaultAppUrl = config('app.url') ?: 'http://127.0.0.1:8000';
        $defaultContactEmail = config('mail.from.address') ?: 'contact@menupro.ci';
        
        $request->validate([
            'app_name' => ['nullable', 'string', 'max:255'],
            'app_url' => ['nullable', 'string', function($a,$v,$f){ if($v && !filter_var($v,FILTER_VALIDATE_URL)) $f('Format URL invalide.'); }],
            'contact_email' => ['nullable', 'email'],
            'contact_phone' => ['nullable', 'string', 'max:30'],
            'maintenance_mode' => ['boolean'],
            'registrations_open' => ['boolean'],
            'geoapify_api_key' => ['nullable', 'string'],
            'elevenlabs_api_key' => ['nullable', 'string', 'max:255'],
            'elevenlabs_voice_id' => ['nullable', 'string', 'max:255'],
            // MoneyFusion
            'moneyfusion_active'  => ['boolean'],
            'moneyfusion_api_url' => ['nullable', 'string', function($a,$v,$f){ if($v && !filter_var($v,FILTER_VALIDATE_URL)) $f('Format URL invalide.'); }],
            'moneyfusion_api_key' => ['nullable', 'string'],
            // Wave CI
            'wave_api_key' => ['nullable', 'string'],
            'wave_webhook_secret' => ['nullable', 'string'],
            // Jeko Marketplace
            'jeko_marketplace_api_key'        => ['nullable', 'string', 'max:1000'],
            'jeko_marketplace_api_key_id'     => ['nullable', 'string', 'max:255'],
            'jeko_marketplace_store_id'       => ['nullable', 'string', 'max:255'],
            'jeko_marketplace_webhook_secret' => ['nullable', 'string', 'max:1000'],
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
            'hero_image_1' => ['nullable', 'image', 'max:5120'],
            'hero_image_2' => ['nullable', 'image', 'max:5120'],
            'hero_image_3' => ['nullable', 'image', 'max:5120'],
            'apk_download_url' => ['nullable', 'string', 'max:1000'],
            'social_facebook' => ['nullable', 'string'],
            'social_twitter' => ['nullable', 'string'],
            'social_instagram' => ['nullable', 'string'],
            'social_linkedin' => ['nullable', 'string'],
            'footer_text' => ['nullable', 'string', 'max:500'],
            'home_videos' => ['nullable', 'array'],
            'home_videos.*.title' => ['nullable', 'string', 'max:255'],
            'home_videos.*.url' => ['nullable', 'string', 'max:500'],
            'home_videos.*.description' => ['nullable', 'string', 'max:500'],
            'commando_commission_fcfa_first_payment' => ['nullable', 'numeric', 'min:0'],
            'commando_commission_only_first_payment' => ['boolean'],
            'commando_commission_rookie_fcfa'   => ['nullable', 'numeric', 'min:0'],
            'commando_commission_commando_fcfa' => ['nullable', 'numeric', 'min:0'],
            'commando_commission_elite_fcfa'    => ['nullable', 'numeric', 'min:0'],
            // Marketing
            'banner_enabled' => ['boolean'],
            'banner_text' => ['nullable', 'string', 'max:255'],
            'banner_link' => ['nullable', 'string'],
            'banner_color' => ['nullable', 'string', 'in:primary,success,warning,dark'],
            'facebook_app_id'    => ['nullable', 'string', 'max:30'],
            'facebook_pixel_id'  => ['nullable', 'string', 'max:50'],
            'google_analytics_id' => ['nullable', 'string', 'max:50'],
            // WhatsApp Business API (Meta)
            'whatsapp_enabled' => ['boolean'],
            'whatsapp_phone_id' => ['nullable', 'string', 'max:255'],
            'whatsapp_api_key' => ['nullable', 'string'],
            // Twilio
            'twilio_sid' => ['nullable', 'string', 'max:100'],
            'twilio_auth_token' => ['nullable', 'string'],
            'twilio_whatsapp_from' => ['nullable', 'string', 'max:50'],
            // Mapbox
            'mapbox_public_token' => ['nullable', 'string'],
            'mapbox_style' => ['nullable', 'string', 'in:streets-v12,light-v11,dark-v11,satellite-v9,navigation-day-v1,navigation-night-v1'],
            // Pusher
            'pusher_app_id'  => ['nullable', 'string', 'max:50'],
            'pusher_key'     => ['nullable', 'string', 'max:100'],
            'pusher_secret'  => ['nullable', 'string', 'max:100'],
            'pusher_cluster' => ['nullable', 'string', 'max:20'],
            // Firebase FCM
            'firebase_server_key' => ['nullable', 'string'],
            'firebase_project_id' => ['nullable', 'string', 'max:100'],
            'firebase_service_account_json' => ['nullable', 'string'],
            'firebase_api_key' => ['nullable', 'string', 'max:255'],
            'firebase_app_id' => ['nullable', 'string', 'max:255'],
            'firebase_sender_id' => ['nullable', 'string', 'max:100'],
            'firebase_vapid_key' => ['nullable', 'string', 'max:255'],
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
        if ($request->filled('geoapify_api_key')) {
            \App\Models\SystemSetting::set('geoapify_api_key', $request->geoapify_api_key, 'string', 'Clé API Geoapify (géocodage d\'adresses)');
        }
        // MoneyFusion
        \App\Models\SystemSetting::set('moneyfusion_active', $request->boolean('moneyfusion_active'), 'boolean', 'MoneyFusion activé');
        if ($request->filled('moneyfusion_api_url')) {
            \App\Models\SystemSetting::set('moneyfusion_api_url', $request->moneyfusion_api_url, 'string', 'URL API MoneyFusion (depuis le dashboard)');
        }
        if ($request->filled('moneyfusion_api_key')) {
            \App\Models\SystemSetting::set('moneyfusion_api_key', $request->moneyfusion_api_key, 'string', 'Clé API MoneyFusion');
        }
        // ElevenLabs TTS (synthèse vocale KDS)
        if ($request->filled('elevenlabs_api_key')) {
            \App\Models\SystemSetting::set('elevenlabs_api_key', $request->elevenlabs_api_key, 'string', 'Clé API ElevenLabs (synthèse vocale KDS)');
        }
        if ($request->filled('elevenlabs_voice_id')) {
            \App\Models\SystemSetting::set('elevenlabs_voice_id', $request->elevenlabs_voice_id, 'string', 'ID de la voix ElevenLabs (KDS)');
        }

        // Wave CI
        if ($request->filled('wave_api_key')) {
            \App\Models\SystemSetting::set('wave_api_key', $request->wave_api_key, 'string', 'Clé API Wave (Bearer token)');
        }
        if ($request->filled('wave_webhook_secret')) {
            \App\Models\SystemSetting::set('wave_webhook_secret', $request->wave_webhook_secret, 'string', 'Secret de signature des webhooks Wave');
        }

        // Jeko Marketplace
        $jekoFields = array_filter([
            'jeko_marketplace_api_key'        => $request->input('jeko_marketplace_api_key'),
            'jeko_marketplace_api_key_id'     => $request->input('jeko_marketplace_api_key_id'),
            'jeko_marketplace_store_id'       => $request->input('jeko_marketplace_store_id'),
            'jeko_marketplace_webhook_secret' => $request->input('jeko_marketplace_webhook_secret'),
        ]);
        if (!empty($jekoFields)) {
            $jekoSetting = \App\Models\SystemPaymentSetting::firstOrCreate(
                ['gateway' => 'jeko_marketplace'],
                ['mode' => 'production', 'is_active' => false]
            );
            if (!empty($jekoFields['jeko_marketplace_api_key'])) {
                $jekoSetting->setEncryptedApiKey($jekoFields['jeko_marketplace_api_key']);
            }
            if (array_key_exists('jeko_marketplace_api_key_id', $jekoFields)) {
                $jekoSetting->merchant_id = $jekoFields['jeko_marketplace_api_key_id'] ?: null;
            }
            if (array_key_exists('jeko_marketplace_store_id', $jekoFields)) {
                $config = $jekoSetting->config ?? [];
                $config['store_id'] = $jekoFields['jeko_marketplace_store_id'] ?: null;
                $jekoSetting->config = $config;
            }
            if (!empty($jekoFields['jeko_marketplace_webhook_secret'])) {
                $jekoSetting->setEncryptedWebhookSecret($jekoFields['jeko_marketplace_webhook_secret']);
            }
            $jekoSetting->save();
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

        // Handle hero images upload (slots 1, 2, 3)
        $heroSlots = ['hero_image_1' => 'hero_image', 'hero_image_2' => 'hero_image_2', 'hero_image_3' => 'hero_image_3'];
        foreach ($heroSlots as $inputName => $settingKey) {
            if ($request->hasFile($inputName)) {
                $newPath = $request->file($inputName)->store('system', 'public');
                $oldPath = \App\Models\SystemSetting::get($settingKey, '');
                if ($oldPath && Storage::disk('public')->exists($oldPath)) {
                    Storage::disk('public')->delete($oldPath);
                }
                \App\Models\SystemSetting::set($settingKey, $newPath, 'string', 'Image hero page d\'accueil');
            }
        }

        // APK download URL (lien externe Google Drive / GitHub / etc.)
        \App\Models\SystemSetting::set('apk_download_url', $request->apk_download_url ?? '', 'string', 'URL téléchargement APK Android');

        // Social links (save even if empty to allow clearing)
        \App\Models\SystemSetting::set('social_facebook', $request->social_facebook ?? '', 'string', 'Lien Facebook');
        \App\Models\SystemSetting::set('social_twitter', $request->social_twitter ?? '', 'string', 'Lien Twitter');
        \App\Models\SystemSetting::set('social_instagram', $request->social_instagram ?? '', 'string', 'Lien Instagram');
        \App\Models\SystemSetting::set('social_linkedin', $request->social_linkedin ?? '', 'string', 'Lien LinkedIn');
        
        if ($request->filled('footer_text')) {
            \App\Models\SystemSetting::set('footer_text', $request->footer_text, 'string', 'Texte du footer');
        }

        // Vidéos page d'accueil (tableau titre, url, description)
        if ($request->has('home_videos')) {
            $raw = $request->home_videos ?? [];
            $videos = [];
            foreach (is_array($raw) ? $raw : [] as $v) {
                $url = trim($v['url'] ?? '');
                if ($url !== '') {
                    $videos[] = [
                        'title' => trim($v['title'] ?? '') ?: 'Vidéo',
                        'url' => $url,
                        'description' => trim($v['description'] ?? ''),
                    ];
                }
            }
            \App\Models\SystemSetting::set('home_videos', $videos, 'json', 'Vidéos tutoriels de la page d\'accueil');
        }

        // WhatsApp Business API (Meta)
        if ($request->has('whatsapp_enabled')) {
            \App\Models\SystemSetting::set('whatsapp_enabled', $request->boolean('whatsapp_enabled'), 'boolean', 'Activer les notifications WhatsApp');
        }
        if ($request->filled('whatsapp_phone_id')) {
            \App\Models\SystemSetting::set('whatsapp_phone_id', $request->whatsapp_phone_id, 'string', 'Phone Number ID WhatsApp Business');
        }
        if ($request->filled('whatsapp_api_key')) {
            \App\Models\SystemSetting::set('whatsapp_api_key', $request->whatsapp_api_key, 'string', 'Token d\'accès permanent WhatsApp Business API');
        }
        // Twilio WhatsApp
        if ($request->filled('twilio_sid')) {
            \App\Models\SystemSetting::set('twilio_sid', $request->twilio_sid, 'string', 'Twilio Account SID');
        }
        if ($request->filled('twilio_auth_token')) {
            \App\Models\SystemSetting::set('twilio_auth_token', $request->twilio_auth_token, 'string', 'Twilio Auth Token');
        }
        if ($request->filled('twilio_whatsapp_from')) {
            \App\Models\SystemSetting::set('twilio_whatsapp_from', $request->twilio_whatsapp_from, 'string', 'Numéro expéditeur Twilio WhatsApp');
        }
        // Mapbox
        if ($request->filled('mapbox_public_token')) {
            \App\Models\SystemSetting::set('mapbox_public_token', $request->mapbox_public_token, 'string', 'Token public Mapbox (cartes app livraison)');
        }
        if ($request->filled('mapbox_style')) {
            \App\Models\SystemSetting::set('mapbox_style', $request->mapbox_style, 'string', 'Style de carte Mapbox');
        }
        // Pusher
        if ($request->filled('pusher_app_id')) {
            \App\Models\SystemSetting::set('pusher_app_id', $request->pusher_app_id, 'string', 'Pusher App ID');
        }
        if ($request->filled('pusher_key')) {
            \App\Models\SystemSetting::set('pusher_key', $request->pusher_key, 'string', 'Pusher App Key (public)');
        }
        if ($request->filled('pusher_secret')) {
            \App\Models\SystemSetting::set('pusher_secret', $request->pusher_secret, 'string', 'Pusher App Secret');
        }
        if ($request->filled('pusher_cluster')) {
            \App\Models\SystemSetting::set('pusher_cluster', $request->pusher_cluster, 'string', 'Pusher Cluster');
        }
        // Vider les caches Pusher pour que le nouveau config prenne effet immédiatement
        if ($request->filled('pusher_key') || $request->filled('pusher_secret') || $request->filled('pusher_cluster')) {
            \Illuminate\Support\Facades\Cache::forget('system.pusher_config');
            \Illuminate\Support\Facades\Cache::forget('platform.public_config');
        }

        // Firebase FCM
        if ($request->filled('firebase_server_key')) {
            \App\Models\SystemSetting::set('firebase_server_key', $request->firebase_server_key, 'string', 'Clé serveur Firebase legacy (désactivée)');
        }
        if ($request->filled('firebase_project_id')) {
            \App\Models\SystemSetting::set('firebase_project_id', $request->firebase_project_id, 'string', 'Firebase Project ID (FCM v1)');
        }
        if ($request->filled('firebase_service_account_json')) {
            $decoded = json_decode($request->firebase_service_account_json, true);
            if (is_array($decoded) && isset($decoded['client_email'], $decoded['private_key'])) {
                \App\Models\SystemSetting::set('firebase_service_account_json', $request->firebase_service_account_json, 'string', 'Service Account JSON Firebase (FCM v1)');
            }
        }
        if ($request->filled('firebase_api_key')) {
            \App\Models\SystemSetting::set('firebase_api_key', $request->firebase_api_key, 'string', 'Firebase API Key (Web)');
        }
        if ($request->filled('firebase_app_id')) {
            \App\Models\SystemSetting::set('firebase_app_id', $request->firebase_app_id, 'string', 'Firebase App ID');
        }
        if ($request->filled('firebase_sender_id')) {
            \App\Models\SystemSetting::set('firebase_sender_id', $request->firebase_sender_id, 'string', 'Firebase Sender ID (Messaging)');
        }
        if ($request->filled('firebase_vapid_key')) {
            \App\Models\SystemSetting::set('firebase_vapid_key', $request->firebase_vapid_key, 'string', 'VAPID Public Key (Web Push)');
        }

        // Marketing – bannière promotionnelle + Facebook Pixel + Google Analytics
        if ($request->has('banner_enabled')) {
            \App\Models\SystemSetting::set('banner_enabled', $request->boolean('banner_enabled'), 'boolean', 'Activer la bannière promotionnelle');
            \App\Models\SystemSetting::set('banner_text', $request->banner_text ?? '', 'string', 'Texte de la bannière');
            \App\Models\SystemSetting::set('banner_link', $request->banner_link ?? '', 'string', 'Lien de la bannière');
            \App\Models\SystemSetting::set('banner_color', $request->banner_color ?? 'primary', 'string', 'Couleur de la bannière');
        }
        if ($request->has('facebook_app_id')) {
            \App\Models\SystemSetting::set('facebook_app_id', $request->facebook_app_id ?? '', 'string', 'ID application Facebook (og:)');
        }
        if ($request->has('facebook_pixel_id')) {
            \App\Models\SystemSetting::set('facebook_pixel_id', $request->facebook_pixel_id ?? '', 'string', 'ID du Facebook Pixel');
        }
        if ($request->has('google_analytics_id')) {
            \App\Models\SystemSetting::set('google_analytics_id', $request->google_analytics_id ?? '', 'string', 'ID Google Analytics GA4');
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
        // Commissions par grade (montant saisi en FCFA → stocké en centimes)
        if ($request->has('commando_commission_rookie_fcfa')) {
            \App\Models\SystemSetting::set(
                'commando_commission_rookie_cents',
                (int) round((float) ($request->commando_commission_rookie_fcfa ?: 0) * 100),
                'integer',
                'Commission (centimes) versée à un agent ROOKIE pour chaque parrainage'
            );
        }
        if ($request->has('commando_commission_commando_fcfa')) {
            \App\Models\SystemSetting::set(
                'commando_commission_commando_cents',
                (int) round((float) ($request->commando_commission_commando_fcfa ?: 0) * 100),
                'integer',
                'Commission (centimes) versée à un agent COMMANDO pour chaque parrainage'
            );
        }
        if ($request->has('commando_commission_elite_fcfa')) {
            \App\Models\SystemSetting::set(
                'commando_commission_elite_cents',
                (int) round((float) ($request->commando_commission_elite_fcfa ?: 0) * 100),
                'integer',
                'Commission (centimes) versée à un agent ELITE pour chaque parrainage'
            );
        }

        if ($request->wantsJson()) {
            return response()->json(['message' => 'Paramètres mis à jour avec succès.']);
        }

        return back()->with('success', 'Paramètres mis à jour avec succès.');

        } catch (\Illuminate\Validation\ValidationException $e) {
            \Log::warning('updateSettings validation failed', [
                'errors' => $e->errors(),
                'input_keys' => array_keys(request()->all()),
            ]);
            throw $e;
        } catch (\Exception $e) {
            \Log::error('updateSettings error: ' . $e->getMessage(), ['trace' => $e->getTraceAsString()]);
            return back()->with('error', 'Erreur : ' . $e->getMessage())->withInput();
        }
    }

    /**
     * Get live stats for real-time dashboard.
     */
    public function liveStats(PlatformMetrics $metrics)
    {
        $todayStart = now()->startOfDay();

        // Commandes récentes (5 dernières minutes, hors démo, hors brouillons)
        $recentOrders = $metrics->placedOrders()
            ->with('restaurant:id,name')
            ->where('orders.created_at', '>=', now()->subMinutes(5))
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

        // Live stats (hors démo) — mêmes définitions que le dashboard
        $stats = [
            'orders_today' => $metrics->placedOrders()->where('orders.created_at', '>=', $todayStart)->count(),
            'revenue_today' => $metrics->gmv($todayStart),
            // En cours = payée → en livraison, limitées aux 48 dernières heures
            'pending_orders' => $metrics->inProgressOrders()->count(),
            'stuck_orders' => $metrics->stuckOrders()->count(),
            'active_restaurants' => $metrics->activeRestaurantsCount(),
            // Nouveaux utilisateurs du jour, y compris ceux sans restaurant (clients, livreurs…)
            'new_registrations_today' => $this->realUsers($metrics)->where('created_at', '>=', $todayStart)->count(),
        ];

        // Orders by status (for pie chart)
        $ordersByStatus = $metrics->placedOrders()
            ->where('orders.created_at', '>=', $todayStart)
            ->selectRaw('orders.status as status_value, COUNT(*) as count')
            ->groupBy('orders.status')
            ->pluck('count', 'status_value');

        // Hourly orders (for line chart) — 1 requête GROUP BY, expression d'heure portable
        $hourlyRaw = $metrics->placedOrders()
            ->where('orders.created_at', '>=', $todayStart)
            ->selectRaw($metrics->hourExpression('orders.created_at') . ' as hour, COUNT(*) as count')
            ->groupBy('hour')
            ->pluck('count', 'hour');

        $hourlyOrders = [];
        for ($i = 0; $i < 24; $i++) {
            $hourlyOrders[] = ['hour' => sprintf('%02d:00', $i), 'count' => (int) ($hourlyRaw[$i] ?? 0)];
        }

        return response()->json([
            'stats' => $stats,
            'recent_orders' => $recentOrders,
            'orders_by_status' => $ordersByStatus,
            'hourly_orders' => $hourlyOrders,
            'timestamp' => now()->format('H:i:s'),
        ]);
    }

    public function testWhatsapp(Request $request): \Illuminate\Http\JsonResponse
    {
        $request->validate([
            'phone' => ['required', 'string'],
        ]);

        try {
            $ok = app(\App\Services\WhatsAppService::class)->send(
                $request->phone,
                "✅ *Test MenuPro* — Le service WhatsApp est bien configuré ! Vous recevez ce message depuis le backoffice."
            );

            if ($ok) {
                return response()->json(['success' => true, 'message' => 'Message envoyé avec succès sur ' . $request->phone]);
            }

            return response()->json(['success' => false, 'message' => 'Envoi échoué — vérifiez les credentials et les logs Laravel.'], 422);
        } catch (\Throwable $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    /**
     * Utilisateurs hors comptes démo : ceux sans restaurant (clients, livreurs, admins)
     * sont inclus — un whereNotIn('restaurant_id', …) seul les excluait (NULL NOT IN).
     */
    private function realUsers(PlatformMetrics $metrics)
    {
        return User::where(function ($q) use ($metrics) {
            $q->whereNull('restaurant_id')
              ->orWhere(fn ($r) => $metrics->onlyRealRestaurants($r));
        });
    }
}

