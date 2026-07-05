<?php

namespace App\Http\Middleware;

use App\Enums\Crm\AgentStatus;
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

        // Vérifier le statut — seuls les agents actifs (ou manager) peuvent travailler
        $status = $user->agent_status;
        if ($status && !$status->canLogin()) {
            auth()->logout();
            $request->session()->invalidate();
            abort(403, 'Votre compte est suspendu ou désactivé. Contactez votre manager.');
        }

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
