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

        $section = AdminSections::sectionForRouteName($request->route()?->getName());

        // Section réservée (null) ou non accordée → 403.
        if ($section === null || !$user?->canAdminSection($section)) {
            abort(403, 'Accès réservé.');
        }

        return $next($request);
    }
}
