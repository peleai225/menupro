<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class SanitizeApiInput
{
    // Champs qui contiennent des mots de passe ou tokens — ne pas toucher
    private const SKIP_FIELDS = ['password', 'password_confirmation', 'token', 'fcm_token', 'secret'];

    public function handle(Request $request, Closure $next): Response
    {
        if ($request->isJson() || $request->expectsJson()) {
            $cleaned = $this->sanitize($request->all());
            $request->replace($cleaned);
        }

        return $next($request);
    }

    private function sanitize(array $data): array
    {
        $result = [];

        foreach ($data as $key => $value) {
            if (in_array($key, self::SKIP_FIELDS, true)) {
                $result[$key] = $value;
                continue;
            }

            if (is_array($value)) {
                $result[$key] = $this->sanitize($value);
            } elseif (is_string($value)) {
                // Strip les balises HTML et trim
                $result[$key] = trim(strip_tags($value));
            } else {
                $result[$key] = $value;
            }
        }

        return $result;
    }
}
