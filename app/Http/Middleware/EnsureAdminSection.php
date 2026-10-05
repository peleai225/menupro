<?php

namespace App\Http\Middleware;

use App\Support\AdminSections;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAdminSection
{
    /**
     * Restreint l'accès aux sections back-office selon admin_permissions.
     * Posé APRÈS 'super.admin' : l'utilisateur est déjà un super admin ici.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Admin complet : accès total (bypass historique).
        if ($user && $user->isFullAdmin()) {
            return $next($request);
        }

        $routeName = $request->route()?->getName();

        // Données peu sensibles (compteurs sidebar, notifications) dont
        // dépend l'affichage de la sidebar elle-même : accessible à tout
        // super admin, quelle que soit sa section.
        if ($user && AdminSections::isAnyAdminRoute($routeName)) {
            return $next($request);
        }

        $section = AdminSections::sectionForRouteName($routeName);

        // Section réservée (null) ou non accordée → 403.
        if ($section === null || !$user?->canAdminSection($section)) {
            abort(403, 'Accès réservé.');
        }

        return $next($request);
    }
}
