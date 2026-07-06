<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
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
        $demoRestaurantIds = Restaurant::where('is_demo', true)->pluck('id');

        // Key metrics (excluding demo accounts) — consolidated queries to reduce DB round-trips

        // 1 requête au lieu de 4 pour les restaurants
        $restaurantStats = DB::table('restaurants')
            ->where('is_demo', false)
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as active,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = ? THEN 1 ELSE 0 END) as expired
            ", [
                RestaurantStatus::ACTIVE->value,
                RestaurantStatus::PENDING->value,
                RestaurantStatus::EXPIRED->value,
            ])
            ->first();

        // 1 requête au lieu de 5 pour les commandes + revenus
        $orderStats = DB::table('orders')
            ->when($demoRestaurantIds->isNotEmpty(), fn($q) => $q->whereNotIn('restaurant_id', $demoRestaurantIds))
            ->selectRaw("
                COUNT(*) as total,
                SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as today,
                SUM(CASE WHEN MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW()) THEN 1 ELSE 0 END) as this_month,
                SUM(CASE WHEN payment_status = ? THEN total ELSE 0 END) as revenue_total,
                SUM(CASE WHEN payment_status = ? AND MONTH(created_at) = MONTH(NOW()) AND YEAR(created_at) = YEAR(NOW()) THEN total ELSE 0 END) as revenue_month
            ", [PaymentStatus::COMPLETED->value, PaymentStatus::COMPLETED->value])
            ->first();

        $stats = [
            'restaurants' => [
                'total'   => (int) ($restaurantStats->total ?? 0),
                'active'  => (int) ($restaurantStats->active ?? 0),
                'pending' => (int) ($restaurantStats->pending ?? 0),
                'expired' => (int) ($restaurantStats->expired ?? 0),
            ],
            'users' => User::when($demoRestaurantIds->isNotEmpty(), fn($q) => $q->whereNotIn('restaurant_id', $demoRestaurantIds))->count(),
            'orders' => [
                'total'      => (int) ($orderStats->total ?? 0),
                'today'      => (int) ($orderStats->today ?? 0),
                'this_month' => (int) ($orderStats->this_month ?? 0),
            ],
            'revenue' => [
                'total'      => (float) ($orderStats->revenue_total ?? 0),
                'this_month' => (float) ($orderStats->revenue_month ?? 0),
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

        // Top restaurants by orders this month (excluding demo)
        $topRestaurants = Order::withoutGlobalScope('restaurant')
            ->join('restaurants', 'orders.restaurant_id', '=', 'restaurants.id')
            ->where('restaurants.is_demo', false)
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

        // Orders by day (last 7 days) for line chart
        $ordersByDayRaw = DB::table('orders')
            ->selectRaw('DATE(created_at) as day, COUNT(*) as count, SUM(total) as revenue')
            ->whereNotIn('restaurant_id', $demoRestaurantIds)
            ->where('created_at', '>=', now()->subDays(6)->startOfDay())
            ->groupBy('day')
            ->orderBy('day')
            ->get();

        $daysFr = ['Dim', 'Lun', 'Mar', 'Mer', 'Jeu', 'Ven', 'Sam'];
        $dataMap = $ordersByDayRaw->keyBy('day');
        $ordersByDay = ['labels' => [], 'counts' => [], 'revenues' => []];

        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->toDateString();
            $dayOfWeek = (int) now()->subDays($i)->format('w');
            $ordersByDay['labels'][] = $daysFr[$dayOfWeek];
            $dayData = $dataMap->get($date);
            $ordersByDay['counts'][] = $dayData ? (int) $dayData->count : 0;
            $ordersByDay['revenues'][] = $dayData ? (float) $dayData->revenue : 0.0;
        }

        // Orders by status (all time) for donut chart
        $ordersByStatusRaw = DB::table('orders')
            ->selectRaw('status, COUNT(*) as count')
            ->whereNotIn('restaurant_id', $demoRestaurantIds)
            ->groupBy('status')
            ->get();

        $statusLabelsMap = [
            'pending'   => 'En attente',
            'confirmed' => 'Confirmée',
            'preparing' => 'En préparation',
            'ready'     => 'Prête',
            'delivered' => 'Livrée',
            'completed' => 'Terminée',
            'cancelled' => 'Annulée',
        ];

        $ordersByStatus = ['labels' => [], 'counts' => []];
        foreach ($ordersByStatusRaw as $item) {
            $ordersByStatus['labels'][] = $statusLabelsMap[$item->status] ?? ucfirst($item->status);
            $ordersByStatus['counts'][] = (int) $item->count;
        }

        return view('pages.super-admin.dashboard', compact(
            'stats',
            'recentRestaurants',
            'pendingRestaurants',
            'expiringSubscriptions',
            'revenueByPlan',
            'topRestaurants',
            'ordersByDay',
            'ordersByStatus'
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
            'hero_image' => \App\Models\SystemSetting::get('hero_image', ''),
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
            'facebook_pixel_id' => \App\Models\SystemSetting::get('facebook_pixel_id', ''),
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
            'moneyfusion_api_url' => \App\Models\SystemSetting::get('moneyfusion_api_url', config('moneyfusion.api_url', '')),
            'moneyfusion_api_key' => \App\Models\SystemSetting::get('moneyfusion_api_key', config('moneyfusion.api_key', '')),
            // Wave CI
            'wave_api_key' => \App\Models\SystemSetting::get('wave_api_key', config('wave.api_key', '')),
            'wave_webhook_secret' => \App\Models\SystemSetting::get('wave_webhook_secret', config('wave.webhook_secret', '')),
            // WhatsApp Business API
            'whatsapp_enabled' => \App\Models\SystemSetting::get('whatsapp_enabled', config('services.whatsapp.enabled', false)),
            'whatsapp_phone_id' => \App\Models\SystemSetting::get('whatsapp_phone_id', config('services.whatsapp.phone_id', '')),
            'whatsapp_api_key' => \App\Models\SystemSetting::get('whatsapp_api_key', config('services.whatsapp.api_key', '')),
            // Mapbox
            'mapbox_public_token' => \App\Models\SystemSetting::get('mapbox_public_token', config('services.mapbox.public_token', '')),
            'mapbox_style' => \App\Models\SystemSetting::get('mapbox_style', 'streets-v12'),
            // Firebase FCM
            'firebase_server_key' => \App\Models\SystemSetting::get('firebase_server_key', ''),
            'firebase_project_id' => \App\Models\SystemSetting::get('firebase_project_id', ''),
            'firebase_service_account_json' => \App\Models\SystemSetting::get('firebase_service_account_json', ''),
            'firebase_api_key' => \App\Models\SystemSetting::get('firebase_api_key', ''),
            'firebase_app_id' => \App\Models\SystemSetting::get('firebase_app_id', ''),
            'firebase_sender_id' => \App\Models\SystemSetting::get('firebase_sender_id', ''),
            'firebase_vapid_key' => \App\Models\SystemSetting::get('firebase_vapid_key', ''),
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
            'app_url' => ['nullable', 'string', function($a,$v,$f){ if($v && !filter_var($v,FILTER_VALIDATE_URL)) $f('Format URL invalide.'); }],
            'contact_email' => ['nullable', 'email'],
            'contact_phone' => ['nullable', 'string', 'max:30'],
            'maintenance_mode' => ['boolean'],
            'registrations_open' => ['boolean'],
            'geoapify_api_key' => ['nullable', 'string'],
            'elevenlabs_api_key' => ['nullable', 'string', 'max:255'],
            'elevenlabs_voice_id' => ['nullable', 'string', 'max:255'],
            // MoneyFusion
            'moneyfusion_api_url' => ['nullable', 'string', function($a,$v,$f){ if($v && !filter_var($v,FILTER_VALIDATE_URL)) $f('Format URL invalide.'); }],
            'moneyfusion_api_key' => ['nullable', 'string'],
            // Wave CI
            'wave_api_key' => ['nullable', 'string'],
            'wave_webhook_secret' => ['nullable', 'string'],
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
            'facebook_pixel_id' => ['nullable', 'string', 'max:50'],
            'google_analytics_id' => ['nullable', 'string', 'max:50'],
            // WhatsApp Business API
            'whatsapp_enabled' => ['boolean'],
            'whatsapp_phone_id' => ['nullable', 'string', 'max:255'],
            'whatsapp_api_key' => ['nullable', 'string'],
            // Mapbox
            'mapbox_public_token' => ['nullable', 'string'],
            'mapbox_style' => ['nullable', 'string', 'in:streets-v12,light-v11,dark-v11,satellite-v9,navigation-day-v1,navigation-night-v1'],
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

        // WhatsApp Business API
        if ($request->has('whatsapp_enabled')) {
            \App\Models\SystemSetting::set('whatsapp_enabled', $request->boolean('whatsapp_enabled'), 'boolean', 'Activer les notifications WhatsApp');
        }
        if ($request->filled('whatsapp_phone_id')) {
            \App\Models\SystemSetting::set('whatsapp_phone_id', $request->whatsapp_phone_id, 'string', 'Phone Number ID WhatsApp Business');
        }
        if ($request->filled('whatsapp_api_key')) {
            \App\Models\SystemSetting::set('whatsapp_api_key', $request->whatsapp_api_key, 'string', 'Token d\'accès permanent WhatsApp Business API');
        }
        // Mapbox
        if ($request->filled('mapbox_public_token')) {
            \App\Models\SystemSetting::set('mapbox_public_token', $request->mapbox_public_token, 'string', 'Token public Mapbox (cartes app livraison)');
        }
        if ($request->filled('mapbox_style')) {
            \App\Models\SystemSetting::set('mapbox_style', $request->mapbox_style, 'string', 'Style de carte Mapbox');
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
    }

    /**
     * Get live stats for real-time dashboard.
     */
    public function liveStats()
    {
        $demoRestaurantIds = Restaurant::where('is_demo', true)->pluck('id');

        // Recent orders (last 5 minutes, excluding demo)
        $recentOrders = Order::withoutGlobalScope('restaurant')
            ->with('restaurant:id,name')
            ->whereNotIn('restaurant_id', $demoRestaurantIds)
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

        // Live stats (excluding demo)
        $stats = [
            'orders_today' => Order::withoutGlobalScope('restaurant')->whereNotIn('restaurant_id', $demoRestaurantIds)->whereDate('created_at', today())->count(),
            'revenue_today' => Order::withoutGlobalScope('restaurant')
                ->whereNotIn('restaurant_id', $demoRestaurantIds)
                ->where('payment_status', 'completed')
                ->whereDate('paid_at', today())
                ->sum('total'),
            'pending_orders' => Order::withoutGlobalScope('restaurant')
                ->whereNotIn('restaurant_id', $demoRestaurantIds)
                ->whereIn('status', ['pending', 'confirmed', 'preparing'])
                ->count(),
            'active_restaurants' => Restaurant::notDemo()->where('status', 'active')->count(),
            'new_registrations_today' => User::when($demoRestaurantIds->isNotEmpty(), fn($q) => $q->whereNotIn('restaurant_id', $demoRestaurantIds))->whereDate('created_at', today())->count(),
        ];

        // Orders by status (for pie chart)
        $ordersByStatus = Order::withoutGlobalScope('restaurant')
            ->whereDate('created_at', today())
            ->select('status', DB::raw('count(*) as count'))
            ->groupBy('status')
            ->get()
            ->mapWithKeys(fn($item) => [$item->status => $item->count]);

        // Hourly orders (for line chart) — 1 requête GROUP BY au lieu de 24 requêtes séparées
        $hourlyRaw = DB::table('orders')
            ->when($demoRestaurantIds->isNotEmpty(), fn($q) => $q->whereNotIn('restaurant_id', $demoRestaurantIds))
            ->whereDate('created_at', today())
            ->selectRaw('HOUR(created_at) as hour, COUNT(*) as count')
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

}

