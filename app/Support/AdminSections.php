<?php

namespace App\Support;

class AdminSections
{
    /** Clés de sections délégables (cases à cocher). */
    public const DELEGABLE = [
        'restaurants', 'orders', 'deliveries', 'subscriptions',
        'crm', 'announcements', 'customers', 'finance',
    ];

    public const LABELS = [
        'restaurants'   => 'Restaurants (valider inscriptions)',
        'orders'        => 'Commandes',
        'deliveries'    => 'Livraison & Livreurs',
        'subscriptions' => 'Abonnements & Plans',
        'crm'           => 'CRM & Ambassadeurs',
        'announcements' => 'Annonces & Promos',
        'customers'     => 'Clients',
        'finance'       => 'Finances (transactions, reversements)',
    ];

    /** Route d'atterrissage par section (première section autorisée). */
    public const LANDING = [
        'restaurants'   => 'super-admin.restaurants.index',
        'orders'        => 'super-admin.orders.index',
        'deliveries'    => 'super-admin.deliveries.index',
        'subscriptions' => 'super-admin.subscriptions.index',
        'crm'           => 'super-admin.commando.agents.index',
        'announcements' => 'super-admin.announcements.index',
        'customers'     => 'super-admin.customers.index',
        'finance'       => 'super-admin.finances.index',
    ];

    /**
     * Actions réservées aux admins complets même si leur segment correspond
     * à une section déléguée (ex. "restaurants"). impersoner un owner,
     * offrir du temps d'abonnement gratuit et supprimer un restaurant sont
     * plus sensibles que "valider une inscription" — jamais délégables.
     */
    private const RESERVED_ROUTES = [
        'restaurants.impersonate',
        'restaurants.extend-subscription',
        'restaurants.destroy',
    ];

    /**
     * Routes dont le nom complet (hors préfixe super-admin.) ne suit pas la
     * convention "premier segment = section" — ex. les flux /api/* qui
     * alimentent une page déjà rattachée à une section. Vérifiées avant
     * ROUTE_MAP.
     */
    private const ROUTE_NAME_OVERRIDES = [
        'api.live-orders'     => 'orders',
        'api.live-deliveries' => 'deliveries',
    ];

    /**
     * Routes accessibles à TOUT super admin (complet ou restreint), quelle
     * que soit sa section : données peu sensibles (compteurs, notifications)
     * dont dépend l'affichage de la sidebar elle-même.
     */
    private const ANY_ADMIN_ROUTES = [
        'api.sidebar-badges',
        'api.notifications',
        'api.notifications.mark-read',
    ];

    /** Premier segment du nom de route admin → clé de section. Absent ⇒ réservé admin complet. */
    private const ROUTE_MAP = [
        'restaurants'     => 'restaurants',
        'orders'          => 'orders',
        'deliveries'      => 'deliveries',
        'drivers'         => 'deliveries',
        'delivery-cities' => 'deliveries',
        'delivery-zones'  => 'deliveries',
        'delivery'        => 'deliveries',
        'subscriptions'   => 'subscriptions',
        'plans'           => 'subscriptions',
        'commando'        => 'crm',
        'announcements'   => 'announcements',
        'promo-banners'   => 'announcements',
        'push'            => 'announcements',
        'customers'       => 'customers',
        'transactions'    => 'finance',
        'finances'        => 'finance',
    ];

    /**
     * Retourne la clé de section d'un nom de route admin,
     * ou null si la route est réservée aux admins complets / inconnue.
     */
    public static function sectionForRouteName(?string $routeName): ?string
    {
        if (!$routeName || !str_starts_with($routeName, 'super-admin.')) {
            return null;
        }

        $rest = substr($routeName, strlen('super-admin.'));

        if (in_array($rest, self::RESERVED_ROUTES, true)) {
            return null;
        }

        if (isset(self::ROUTE_NAME_OVERRIDES[$rest])) {
            return self::ROUTE_NAME_OVERRIDES[$rest];
        }

        $segment = explode('.', $rest)[0];

        return self::ROUTE_MAP[$segment] ?? null;
    }

    /**
     * Route accessible à tout super admin, complet ou restreint, sans
     * vérification de section (données peu sensibles).
     */
    public static function isAnyAdminRoute(?string $routeName): bool
    {
        if (!$routeName || !str_starts_with($routeName, 'super-admin.')) {
            return false;
        }

        $rest = substr($routeName, strlen('super-admin.'));

        return in_array($rest, self::ANY_ADMIN_ROUTES, true);
    }
}
