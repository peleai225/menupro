<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackLastLogin
{
    /**
     * Handle an incoming request.
     *
     * Track the user's last login time.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if ($user = $request->user()) {
            // Only update if not updated in the last 5 minutes
            if (!$user->last_login_at || $user->last_login_at->diffInMinutes(now()) >= 5) {
                $user->updateLastLogin();
            }
        }

        return $next($request);
    }
}

