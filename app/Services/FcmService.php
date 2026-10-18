<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Http\Client\Pool;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmService
{
    // FCM HTTP v1 — la seule API encore active (l'API legacy est coupée par Google depuis juin 2024)
    private const FCM_V1_URL = 'https://fcm.googleapis.com/v1/projects/%s/messages:send';
    private const OAUTH_URL  = 'https://oauth2.googleapis.com/token';

    // Nombre de requêtes FCM envoyées en parallèle
    private const POOL_SIZE = 25;

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

    public function isConfigured(): bool
    {
        return $this->isV1Configured();
    }

    public function isV1Configured(): bool
    {
        return !empty($this->projectId()) && !empty($this->serviceAccountJson());
    }

    /**
     * Envoyer à un seul token.
     *
     * @param  array  $options  link (URL https ouverte au clic), tag, require_interaction, icon, ttl
     */
    public function sendToToken(string $fcmToken, string $title, string $body, array $data = [], array $options = []): bool
    {
        $result = $this->sendToMultiple([$fcmToken], $title, $body, $data, $options);
        return $result['success'] > 0;
    }

    /**
     * Envoyer à plusieurs tokens. Les tokens refusés par Firebase (appareil désinstallé,
     * permission retirée) sont supprimés de la base pour ne plus être ciblés.
     *
     * @return array{success: int, failure: int}
     */
    public function sendToMultiple(array $tokens, string $title, string $body, array $data = [], array $options = []): array
    {
        $tokens = array_values(array_unique(array_filter($tokens)));
        if (empty($tokens)) {
            return ['success' => 0, 'failure' => 0];
        }

        if (!$this->isV1Configured()) {
            Log::warning('[FCM] Firebase HTTP v1 non configuré (project_id + service_account_json).');
            return ['success' => 0, 'failure' => count($tokens)];
        }

        return $this->sendViaV1($tokens, $title, $body, $data, $options);
    }

    private function buildPayload(string $token, string $title, string $body, array $data, array $options): array
    {
        $webNotification = array_filter([
            'icon'               => $options['icon'] ?? null,
            'badge'              => $options['icon'] ?? null,
            'tag'                => $options['tag'] ?? null,
            'renotify'           => isset($options['tag']) ? true : null,
            'requireInteraction' => ($options['require_interaction'] ?? false) ?: null,
            'vibrate'            => ($options['require_interaction'] ?? false) ? [500, 250, 500, 250, 500] : null,
        ], fn ($v) => $v !== null);

        $webpush = [
            // Urgency high : le navigateur réveille l'appareil même en veille
            'headers' => [
                'Urgency' => 'high',
                'TTL'     => (string) ($options['ttl'] ?? 3600),
            ],
        ];
        if ($webNotification) {
            $webpush['notification'] = $webNotification;
        }
        if (!empty($options['link']) && str_starts_with($options['link'], 'https://')) {
            $webpush['fcm_options'] = ['link' => $options['link']];
        }

        $message = [
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
            'webpush' => $webpush,
        ];

        if (!empty($data)) {
            $message['data'] = array_map('strval', $data);
        }

        return ['message' => $message];
    }

    private function sendViaV1(array $tokens, string $title, string $body, array $data, array $options): array
    {
        $accessToken = $this->getAccessToken();
        if (!$accessToken) {
            return ['success' => 0, 'failure' => count($tokens)];
        }

        $url = sprintf(self::FCM_V1_URL, $this->projectId());
        $totals = ['success' => 0, 'failure' => 0];
        $invalidTokens = [];

        // FCM v1 n'accepte qu'un token par requête : on parallélise par paquets
        foreach (array_chunk($tokens, self::POOL_SIZE) as $chunk) {
            $responses = Http::pool(function (Pool $pool) use ($chunk, $accessToken, $url, $title, $body, $data, $options) {
                foreach ($chunk as $i => $token) {
                    $pool->as((string) $i)
                        ->withToken($accessToken)
                        ->timeout(10)
                        ->post($url, $this->buildPayload($token, $title, $body, $data, $options));
                }
            });

            foreach ($chunk as $i => $token) {
                $response = $responses[(string) $i] ?? null;

                if ($response instanceof Response && $response->successful()) {
                    $totals['success']++;
                    continue;
                }

                $totals['failure']++;

                if (!$response instanceof Response) {
                    Log::error('[FCM v1] Exception.', [
                        'message' => $response instanceof \Throwable ? $response->getMessage() : 'réponse vide',
                    ]);
                    continue;
                }

                if ($this->isInvalidTokenError($response)) {
                    $invalidTokens[] = $token;
                    continue;
                }

                Log::warning('[FCM v1] Échec token.', [
                    'token'  => substr($token, 0, 20),
                    'status' => $response->status(),
                    'body'   => $response->json(),
                ]);
            }
        }

        if ($invalidTokens) {
            $this->purgeTokens($invalidTokens);
        }

        return $totals;
    }

    /**
     * Token définitivement inutilisable : application désinstallée, permission retirée, token malformé.
     */
    private function isInvalidTokenError(Response $response): bool
    {
        $error = $response->json('error') ?? [];
        $codes = collect($error['details'] ?? [])->pluck('errorCode')->filter()->all();

        if (in_array('UNREGISTERED', $codes, true) || $response->status() === 404) {
            return true;
        }

        return in_array('INVALID_ARGUMENT', $codes, true)
            && str_contains(strtolower($error['message'] ?? ''), 'registration token');
    }

    private function purgeTokens(array $tokens): void
    {
        try {
            DB::table('customers')->whereIn('fcm_token', $tokens)->update(['fcm_token' => null]);
            DB::table('delivery_drivers')->whereIn('fcm_token', $tokens)->update(['fcm_token' => null]);
            DB::table('push_tokens')->whereIn('token', $tokens)->delete();

            Log::info('[FCM v1] Tokens invalides supprimés.', ['count' => count($tokens)]);
        } catch (\Throwable $e) {
            Log::warning('[FCM v1] Nettoyage des tokens impossible.', ['message' => $e->getMessage()]);
        }
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
}

if (!function_exists('base64url_encode')) {
    function base64url_encode(string $data): string
    {
        return rtrim(strtr(base64_encode($data), '+/', '-_'), '=');
    }
}
