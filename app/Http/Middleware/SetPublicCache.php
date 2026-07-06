<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SetPublicCache
{
    public function handle(Request $request, Closure $next, int $maxAge = 300): Response
    {
        $response = $next($request);

        // Uniquement sur les réponses HTML 200 (pas les redirects, erreurs, etc.)
        if ($response->getStatusCode() === 200 && !$request->user()) {
            $response->headers->set('Cache-Control', "public, max-age={$maxAge}, s-maxage={$maxAge}");
        }

        return $response;
    }
}
