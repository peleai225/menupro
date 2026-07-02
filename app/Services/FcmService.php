<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmService
{
    // FCM v1 (HTTP v1 API) — actuel, recommandé
    private const FCM_V1_URL = 'https://fcm.googleapis.com/v1/projects/%s/messages:send';
    private const OAUTH_URL  = 'https://oauth2.googleapis.com/token';

    // Legacy API (désactivée par Google en juin 2024 — gardée en fallback d'urgence)
    private const FCM_LEGACY_URL = 'https://fcm.googleapis.com/fcm/send';

    private function projectId(): ?string
    {
        return SystemSetting::get('firebase_project_id', config('services.firebase.project_id', null));
    }

    private function serviceAccountJson(): ?array
    {
        $json = SystemSetting::get('firebase_service_account_json', null);
        if (!$json) return null;
        $decoded = json_decode($json, true);
        return is_array($decoded) ? $decoded : null;
    }

    private function serverKey(): ?string
    {
        return SystemSetting::get('firebase_server_key', config('services.firebase.server_key', null));
    }

    public function isConfigured(): bool
    {
        return ($this->projectId() && $this->serviceAccountJson()) || !empty($this->serverKey());
    }

    public function isV1Configured(): bool
    {
        return !empty($this->projectId()) && !empty($this->serviceAccountJson());
    }

    /**
     * Envoyer à un seul token.
     */
    public function sendToToken(string $fcmToken, string $title, string $body, array $data = []): bool
    {
        $result = $this->sendToMultiple([$fcmToken], $title, $body, $data);
        return $result['success'] > 0;
    }

    /**
     * Envoyer à plusieurs tokens.
     */
    public function sendToMultiple(array $tokens, string $title, string $body, array $data = []): array
    {
        $tokens = array_values(array_filter($tokens));
        if (empty($tokens)) {
            return ['success' => 0, 'failure' => 0];
        }

        if ($this->isV1Configured()) {
            return $this->sendViaV1($tokens, $title, $body, $data);
        }

        $key = $this->serverKey();
        if ($key) {
            Log::warning('[FCM] Utilisation de l\'API legacy (désactivée depuis juin 2024). Migrez vers FCM v1.');
            return $this->sendViaLegacy($tokens, $title, $body, $data, $key);
        }

        Log::warning('[FCM] Aucune configuration Firebase trouvée.');
        return ['success' => 0, 'failure' => count($tokens)];
    }

    // --- FCM v1 ---

    private function sendViaV1(array $tokens, string $title, string $body, array $data): array
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return ['success' => 0, 'failure' => count($tokens)];
        }

        $projectId = $this->projectId();
        $url = sprintf(self::FCM_V1_URL, $projectId);
        $totals = ['success' => 0, 'failure' => 0];

        // FCM v1 ne supporte qu'un token à la fois (pas de multicast comme legacy)
        foreach ($tokens as $token) {
            try {
                $payload = [
                    'message' => [
                        'token' => $token,
                        'notification' => [
                            'title' => $title,
                            'body'  => $body,
                        ],
                        'android' => [
                            'priority' => 'high',
                            'notification' => ['sound' => 'default'],
                        ],
                        'apns' => [
                            'payload' => ['aps' => ['sound' => 'default']],
                        ],
                    ],
                ];

                if (!empty($data)) {
                    $payload['message']['data'] = array_map('strval', $data);
                }

                $response = Http::withToken($accessToken)
                    ->timeout(10)
                    ->post($url, $payload);

                if ($response->successful()) {
                    $totals['success']++;
                } else {
                    $totals['failure']++;
                    Log::warning('[FCM v1] Échec token.', [
                        'token' => substr($token, 0, 20),
                        'status' => $response->status(),
                        'body' => $response->json(),
                    ]);
                }
            } catch (\Throwable $e) {
                $totals['failure']++;
                Log::error('[FCM v1] Exception.', ['message' => $e->getMessage()]);
            }
        }

        return $totals;
    }

    private function getAccessToken(): ?string
    {
        $sa = $this->serviceAccountJson();
        if (!$sa) return null;

        $cacheKey = 'fcm:access_token';
        if ($cached = cache($cacheKey)) return $cached;

        try {
            $now = time();
            $exp = $now + 3600;

            $header  = base64url_encode(json_encode(['alg' => 'RS256', 'typ' => 'JWT']));
            $payload = base64url_encode(json_encode([
                'iss'   => $sa['client_email'],
                'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
                'aud'   => self::OAUTH_URL,
                'iat'   => $now,
                'exp'   => $exp,
            ]));

            $signingInput = $header . '.' . $payload;
            $privateKey = openssl_pkey_get_private($sa['private_key']);
            if (!$privateKey) {
                Log::error('[FCM v1] Clé privée Service Account invalide.');
                return null;
            }

            openssl_sign($signingInput, $signature, $privateKey, OPENSSL_ALGO_SHA256);
            $jwt = $signingInput . '.' . base64url_encode($signature);

            $response = Http::asForm()->timeout(10)->post(self::OAUTH_URL, [
                'grant_type' => 'urn:ietf:params:oauth:grant-type:jwt-bearer',
                'assertion'  => $jwt,
            ]);

            if (!$response->successful()) {
                Log::error('[FCM v1] Échec obtention access token.', ['body' => $response->json()]);
                return null;
            }

            $token = $response->json('access_token');
            $expiresIn = $response->json('expires_in', 3600);
            cache([$cacheKey => $token], $expiresIn - 60);

            return $token;
        } catch (\Throwable $e) {
            Log::error('[FCM v1] Exception JWT.', ['message' => $e->getMessage()]);
            return null;
        }
    }

    // --- Legacy (désactivée, fallback d'urgence) ---

    private function sendViaLegacy(array $tokens, string $title, string $body, array $data, string $key): array
    {
        $chunks = array_chunk($tokens, 1000);
        $totals = ['success' => 0, 'failure' => 0];

        foreach ($chunks as $chunk) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'key=' . $key,
                    'Content-Type'  => 'application/json',
                ])->timeout(10)->post(self::FCM_LEGACY_URL, [
                    'registration_ids' => $chunk,
                    'notification' => ['title' => $title, 'body' => $body, 'sound' => 'default'],
                    'data' => $data,
                    'priority' => 'high',
                ]);

                if ($response->successful()) {
                    $result = $response->json();
                    $totals['success'] += $result['success'] ?? 0;
                    $totals['failure'] += $result['failure'] ?? 0;
                } else {
                    $totals['failure'] += count($chunk);
                    Log::error('[FCM legacy] Erreur HTTP.', ['status' => $response->status()]);
                }
            } catch (\Throwable $e) {
                $totals['failure'] += count($chunk);
                Log::error('[FCM legacy] Exception.', ['message' => $e->getMessage()]);
            }
        }

        return $totals;
    }
}

if (!function_exists('base64url_encode')) {
    function base64url_encode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
