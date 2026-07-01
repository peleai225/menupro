<?php

namespace App\Services;

use App\Models\SystemSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FcmService
{
    private const FCM_URL = 'https://fcm.googleapis.com/fcm/send';

    private function serverKey(): ?string
    {
        return SystemSetting::get('firebase_server_key', config('services.firebase.server_key', null));
    }

    /**
     * Envoyer une notification push à un seul token.
     */
    public function sendToToken(string $fcmToken, string $title, string $body, array $data = []): bool
    {
        $key = $this->serverKey();
        if (!$key) {
            Log::warning('[FCM] Clé serveur Firebase non configurée.');
            return false;
        }

        try {
            $response = Http::withHeaders([
                'Authorization' => 'key=' . $key,
                'Content-Type'  => 'application/json',
            ])->post(self::FCM_URL, [
                'to' => $fcmToken,
                'notification' => [
                    'title' => $title,
                    'body'  => $body,
                    'sound' => 'default',
                ],
                'data' => $data,
                'priority' => 'high',
            ]);

            if ($response->successful()) {
                $result = $response->json();
                if (($result['failure'] ?? 0) > 0) {
                    Log::warning('[FCM] Token invalide ou erreur.', ['token' => substr($fcmToken, 0, 20), 'result' => $result]);
                    return false;
                }
                return true;
            }

            Log::error('[FCM] Erreur HTTP.', ['status' => $response->status(), 'body' => $response->body()]);
            return false;
        } catch (\Exception $e) {
            Log::error('[FCM] Exception.', ['message' => $e->getMessage()]);
            return false;
        }
    }

    /**
     * Envoyer à plusieurs tokens (multicast, max 1000).
     */
    public function sendToMultiple(array $tokens, string $title, string $body, array $data = []): array
    {
        $key = $this->serverKey();
        if (!$key || empty($tokens)) {
            return ['success' => 0, 'failure' => count($tokens)];
        }

        $chunks = array_chunk($tokens, 1000);
        $totals = ['success' => 0, 'failure' => 0];

        foreach ($chunks as $chunk) {
            try {
                $response = Http::withHeaders([
                    'Authorization' => 'key=' . $key,
                    'Content-Type'  => 'application/json',
                ])->post(self::FCM_URL, [
                    'registration_ids' => $chunk,
                    'notification' => [
                        'title' => $title,
                        'body'  => $body,
                        'sound' => 'default',
                    ],
                    'data' => $data,
                    'priority' => 'high',
                ]);

                if ($response->successful()) {
                    $result = $response->json();
                    $totals['success'] += $result['success'] ?? 0;
                    $totals['failure'] += $result['failure'] ?? 0;
                }
            } catch (\Exception $e) {
                Log::error('[FCM] Exception multicast.', ['message' => $e->getMessage()]);
                $totals['failure'] += count($chunk);
            }
        }

        return $totals;
    }

    public function isConfigured(): bool
    {
        return !empty($this->serverKey());
    }
}
