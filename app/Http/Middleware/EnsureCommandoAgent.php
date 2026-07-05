<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureCommandoAgent
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if (!$user) {
            return redirect()->route('login');
        }

        // Accept both commando_agent (legacy) and commercial (new merged role)
        if (!$user->isCommandoAgent() && !$user->isCommercial()) {
            return redirect()->route($user->getDashboardRoute())
                ->with('error', 'Accès réservé aux agents Commando.');
        }

        return $next($request);
    }
}
