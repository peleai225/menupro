<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureRestaurantAdmin
{
    /**
     * Restrict access to restaurant admin and super admin only.
     * Employees are redirected to dashboard with an error message.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        if ($user->isSuperAdmin() || $user->isRestaurantAdmin()) {
            return $next($request);
        }

        if ($user->isEmployee()) {
            return redirect()->route('restaurant.dashboard')
                ->with('error', 'Vous n\'avez pas les droits pour accéder à cette page.');
        }

        abort(403, 'Accès refusé.');
    }
}
