<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiSecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'DENY');
        $response->headers->set('Cache-Control', 'no-store, no-cache, must-revalidate');
        $response->headers->set('Pragma', 'no-cache');

        // Ajouter un Request-ID pour le tracing des erreurs
        $requestId = $request->header('X-Request-ID', \Illuminate\Support\Str::uuid()->toString());
        $response->headers->set('X-Request-ID', $requestId);

        return $response;
    }
}
