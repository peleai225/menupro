<?php

namespace App\Http\Middleware;

use App\Enums\UserRole;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCrmRole
{
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (!$request->user()) {
            return redirect()->route('login');
        }

        // Forcer le rechargement depuis la DB pour éviter les sessions avec ancien rôle
        $user = $request->user()->fresh();

        $allowedRoles = array_map(
            fn (string $role) => UserRole::tryFrom($role),
            $roles
        );

        if (!in_array($user->role, $allowedRoles)) {
            abort(403, 'Accès non autorisé.');
        }

        return $next($request);
    }
}
