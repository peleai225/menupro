<?php

namespace App\Support;

use App\Models\Restaurant;
use App\Models\User;
use App\Services\PlanLimiter;

/**
 * Onglets du tableau de bord restaurant selon le plan et l'état de l'abonnement.
 *
 * Source unique pour la barre latérale, le menu mobile et la barre du bas
 * (components/layouts/admin-restaurant). Les droits viennent de config('plans.features')
 * via Restaurant::hasFeature() (essai = plan Pro) :
 *  - fonction incluse     → état « ok » (lien normal) ;
 *  - fonction non incluse → état « locked » : cadenas + « Passer au plan X » (page des offres) ;
 *  - abonnement expiré / en attente de paiement → seuls Abonnement et Paramètres restent.
 * Les onglets réservés au gérant sont masqués (pas verrouillés) pour les employés.
 */
class RestaurantNavigation
{
    public const STATE_OK = 'ok';
    public const STATE_LOCKED = 'locked';

    /** Icônes (attribut d du <path> SVG, heroicons outline). */
    public const ICONS = [
        'dashboard' => 'M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6',
        'categories' => 'M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10',
        'dishes' => 'M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253',
        'qrcode' => 'M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.01M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z',
        'building' => 'M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4',
        'users' => 'M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z',
        'orders' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01',
        'pos' => 'M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z',
        'calendar' => 'M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z',
        'star' => 'M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z',
        'chart' => 'M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z',
        'report' => 'M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z',
        'promo' => 'M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z',
        'clipboard' => 'M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2',
        'box' => 'M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4',
        'alert' => 'M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z',
        'card' => 'M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z',
        'receipt' => 'M9 7h6m0 10v-5m-3 5h.01M9 17h.01M9 14h.01M12 14h.01M15 11h.01M12 11h.01M9 11h.01M7 21h10a2 2 0 002-2V5a2 2 0 00-2-2H7a2 2 0 00-2 2v14a2 2 0 002 2z',
        'cog' => 'M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z M15 12a3 3 0 11-6 0 3 3 0 016 0z',
        'user' => 'M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z',
        'lock' => 'M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z',
    ];

    /**
     * Définition des onglets, dans l'ordre d'affichage.
     * feature : clé de config('plans.features') (null = tous les plans) ;
     * admin   : réservé au gérant ; limited : reste ouvert quand l'abonnement a expiré.
     */
    protected static function definitions(): array
    {
        return [
            ['section' => null, 'key' => 'dashboard', 'label' => 'Dashboard', 'route' => 'restaurant.dashboard', 'active' => 'restaurant.dashboard', 'icon' => 'dashboard', 'id' => 'tour-dashboard'],

            ['section' => 'Menu', 'key' => 'categories', 'label' => 'Catégories', 'route' => 'restaurant.categories.index', 'active' => 'restaurant.categories*', 'icon' => 'categories', 'admin' => true, 'id' => 'tour-categories'],
            ['section' => 'Menu', 'key' => 'dishes', 'label' => 'Plats', 'route' => 'restaurant.plats.index', 'active' => 'restaurant.plats*', 'icon' => 'dishes', 'admin' => true, 'id' => 'tour-plats'],
            ['section' => 'Menu', 'key' => 'qrcode', 'label' => 'QR Code', 'route' => 'restaurant.qrcode', 'active' => 'restaurant.qrcode*', 'icon' => 'qrcode', 'id' => 'tour-qrcode'],
            ['section' => 'Menu', 'key' => 'rooms', 'label' => 'Chambres hôtel', 'route' => 'restaurant.rooms.index', 'active' => 'restaurant.rooms*', 'icon' => 'building', 'feature' => 'hotel_rooms'],
            ['section' => 'Menu', 'key' => 'spaces', 'label' => 'Espaces', 'route' => 'restaurant.spaces', 'active' => 'restaurant.spaces', 'icon' => 'categories', 'feature' => 'multi_spaces', 'admin' => true],
            ['section' => 'Menu', 'key' => 'waiters', 'label' => 'Serveurs', 'route' => 'restaurant.waiters', 'active' => 'restaurant.waiters', 'icon' => 'users', 'feature' => 'multi_spaces', 'admin' => true, 'hide_locked' => true],

            ['section' => 'Commerce', 'key' => 'orders', 'label' => 'Commandes', 'route' => 'restaurant.orders', 'active' => 'restaurant.orders*', 'icon' => 'orders', 'id' => 'tour-commandes'],
            ['section' => 'Commerce', 'key' => 'pos', 'label' => 'Caisse (POS)', 'route' => 'restaurant.pos', 'active' => 'restaurant.pos*', 'icon' => 'pos', 'feature' => 'pos', 'id' => 'tour-pos'],
            ['section' => 'Commerce', 'key' => 'customers', 'label' => 'Clients', 'route' => 'restaurant.customers', 'active' => 'restaurant.customers*', 'icon' => 'users', 'feature' => 'customers', 'admin' => true, 'id' => 'tour-clients'],
            ['section' => 'Commerce', 'key' => 'reservations', 'label' => 'Réservations', 'route' => 'restaurant.reservations.index', 'active' => 'restaurant.reservations*', 'icon' => 'calendar', 'feature' => 'reservations', 'admin' => true, 'badge' => 'reservations'],
            ['section' => 'Commerce', 'key' => 'reviews', 'label' => 'Avis', 'route' => 'restaurant.reviews', 'active' => 'restaurant.reviews*', 'icon' => 'star', 'feature' => 'reviews', 'admin' => true],

            ['section' => 'Pilotage', 'key' => 'analytics', 'label' => 'Statistiques', 'route' => 'restaurant.analytics', 'active' => 'restaurant.analytics*', 'icon' => 'chart', 'feature' => 'analytics', 'admin' => true, 'id' => 'tour-statistiques'],
            ['section' => 'Pilotage', 'key' => 'reports', 'label' => 'Rapports', 'route' => 'restaurant.reports', 'active' => 'restaurant.reports*', 'icon' => 'report', 'feature' => 'analytics', 'admin' => true],
            ['section' => 'Pilotage', 'key' => 'promo-codes', 'label' => 'Codes Promo', 'route' => 'restaurant.promo-codes', 'active' => 'restaurant.promo-codes*', 'icon' => 'promo', 'feature' => 'analytics', 'admin' => true, 'id' => 'tour-codes-promo'],

            ['section' => 'Stock', 'key' => 'stock-daily', 'label' => 'Stock journalier', 'route' => 'restaurant.stock.daily', 'active' => 'restaurant.stock.daily', 'icon' => 'clipboard', 'feature' => 'stock', 'id' => 'tour-stock'],
            ['section' => 'Stock', 'key' => 'ingredients', 'label' => 'Ingrédients', 'route' => 'restaurant.stock.ingredients.index', 'active' => 'restaurant.stock.ingredients*', 'icon' => 'box', 'feature' => 'ingredient_stock'],
            ['section' => 'Stock', 'key' => 'suppliers', 'label' => 'Fournisseurs', 'route' => 'restaurant.stock.fournisseurs.index', 'active' => 'restaurant.stock.fournisseurs*', 'icon' => 'building', 'feature' => 'ingredient_stock', 'admin' => true, 'hide_locked' => true],
            ['section' => 'Stock', 'key' => 'stock-alerts', 'label' => 'Alertes stock', 'route' => 'restaurant.stock.alerts', 'active' => 'restaurant.stock.alerts*', 'icon' => 'alert', 'feature' => 'ingredient_stock', 'badge' => 'low_stock', 'hide_locked' => true],

            ['section' => 'Équipe', 'key' => 'team', 'label' => 'Équipe', 'route' => 'restaurant.team', 'active' => 'restaurant.team*', 'icon' => 'users', 'feature' => 'team', 'admin' => true],

            ['section' => 'Paramètres', 'key' => 'subscription', 'label' => 'Abonnement', 'route' => 'restaurant.subscription', 'active' => 'restaurant.subscription*', 'icon' => 'card', 'admin' => true, 'limited' => true, 'id' => 'tour-abonnement'],
            ['section' => 'Paramètres', 'key' => 'taxes', 'label' => 'Taxes & Frais', 'route' => 'restaurant.taxes-fees', 'active' => 'restaurant.taxes-fees*', 'icon' => 'receipt', 'feature' => 'taxes', 'admin' => true, 'hide_locked' => true],
            ['section' => 'Paramètres', 'key' => 'jeko', 'label' => 'Paiement Jeko', 'route' => 'restaurant.jeko.onboarding', 'active' => 'restaurant.jeko*', 'icon' => 'card', 'admin' => true],
            ['section' => 'Paramètres', 'key' => 'settings', 'label' => 'Paramètres', 'route' => 'restaurant.settings', 'active' => 'restaurant.settings*', 'icon' => 'cog', 'admin' => true, 'limited' => true, 'id' => 'tour-parametres'],
        ];
    }

    /**
     * Onglets résolus, à plat : chaque élément porte state, url, is_active, upgrade_plan…
     *
     * @return array<int, array<string, mixed>>
     */
    public static function items(?Restaurant $restaurant, ?User $user): array
    {
        if (!$restaurant || !$user) {
            return [];
        }

        $isAdmin = $user->canManageRestaurant();
        $limiter = (clone app(PlanLimiter::class))->forRestaurant($restaurant);
        $limited = $limiter->isAccessLimited();
        $items = [];

        foreach (static::definitions() as $def) {
            $def += ['admin' => false, 'feature' => null, 'limited' => false, 'hide_locked' => false, 'id' => null, 'badge' => null];

            if ($def['admin'] && !$isAdmin) {
                continue;
            }

            if ($limited && !$def['limited']) {
                continue;
            }

            $state = self::STATE_OK;
            $upgradePlan = null;

            if ($def['feature'] && !$restaurant->hasFeature($def['feature'])) {
                // Un employé ne peut pas changer de plan : onglet simplement masqué
                if ($def['hide_locked'] || !$isAdmin) {
                    continue;
                }
                $state = self::STATE_LOCKED;
                $upgradePlan = PlanLimiter::requiredPlanFor($def['feature']);
            }

            $items[] = array_merge($def, [
                'state' => $state,
                'upgrade_plan' => $upgradePlan,
                'url' => $state === self::STATE_LOCKED
                    ? route('restaurant.subscription.plans', array_filter(['plan' => $upgradePlan?->slug]))
                    : route($def['route']),
                'is_active' => $state === self::STATE_OK && request()->routeIs($def['active']),
                'icon_path' => self::ICONS[$def['icon']] ?? self::ICONS['dashboard'],
                'count' => $state === self::STATE_OK ? self::badgeCount($def['badge'], $restaurant) : 0,
            ]);
        }

        return $items;
    }

    /**
     * Onglets groupés par section (barre latérale), dans l'ordre.
     *
     * @return array<int, array{title: ?string, items: array}>
     */
    public static function sections(?Restaurant $restaurant, ?User $user): array
    {
        $sections = [];

        foreach (static::items($restaurant, $user) as $item) {
            $title = $item['section'];
            $last = array_key_last($sections);

            if ($last === null || $sections[$last]['title'] !== $title) {
                $sections[] = ['title' => $title, 'items' => []];
                $last = array_key_last($sections);
            }

            $sections[$last]['items'][] = $item;
        }

        return $sections;
    }

    protected static function badgeCount(?string $badge, Restaurant $restaurant): int
    {
        try {
            return match ($badge) {
                'reservations' => $restaurant->reservations()->pending()->count(),
                'low_stock' => $restaurant->ingredients()->whereColumn('current_quantity', '<=', 'min_quantity')->count(),
                default => 0,
            };
        } catch (\Throwable) {
            return 0;
        }
    }
}
