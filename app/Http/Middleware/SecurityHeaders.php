<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-XSS-Protection', '1; mode=block');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'geolocation=(self), microphone=(), camera=(), payment=(), usb=()');
        $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        $response->headers->set('Content-Security-Policy', "default-src 'self' https://menupro.ci https://www.menupro.ci; script-src 'self' https://menupro.ci https://www.menupro.ci 'unsafe-inline' 'unsafe-eval' https://fonts.googleapis.com https://fonts.bunny.net https://connect.facebook.net https://www.googletagmanager.com https://unpkg.com https://cdn.jsdelivr.net https://cdnjs.cloudflare.com; style-src 'self' https://menupro.ci https://www.menupro.ci 'unsafe-inline' https://fonts.googleapis.com https://fonts.bunny.net https://unpkg.com https://cdn.jsdelivr.net; img-src 'self' https://menupro.ci https://www.menupro.ci data: blob: https:; font-src 'self' https://fonts.gstatic.com https://fonts.bunny.net; connect-src 'self' wss: https:; frame-src 'self' https://www.facebook.com https://www.youtube.com https://youtube.com; manifest-src 'self' https://menupro.ci https://www.menupro.ci; frame-ancestors 'self';");

        return $response;
    }
}
