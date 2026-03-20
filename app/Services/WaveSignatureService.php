<?php

namespace App\Services;

use Carbon\CarbonImmutable;

class WaveSignatureService
{
    /**
    * Génère l'en-tête Wave-Signature pour une requête sortante.
    *
    * @param  string  $body  Corps brut JSON de la requête
    */
    public function generateSignature(string $body): string
    {
        // Priorité au secret configuré dans le backoffice
        $signingSecret = \App\Models\SystemSetting::get('wave_signing_secret', config('wave.signing_secret'));

        if (!$signingSecret) {
            throw new \RuntimeException('Wave signing secret manquant (WAVE_SIGNING_SECRET).');
        }

        $timestamp = time();
        $payload = $timestamp . $body;
        $signature = hash_hmac('sha256', $payload, $signingSecret);

        return "t={$timestamp},v1={$signature}";
    }

    /**
    * Vérifie la signature d’un webhook Wave.
    *
    * @param  string  $payload  Corps brut reçu (JSON)
    * @param  string|null  $header  Valeur de l’en-tête Wave-Signature
    */
    public function verifyWebhookSignature(string $payload, ?string $header): bool
    {
        if (empty($header)) {
            return false;
        }

        $parts = $this->parseHeader($header);
        if (!$parts || empty($parts['t']) || empty($parts['v1'])) {
            return false;
        }

        $timestamp = (int) $parts['t'];
        $providedSignature = $parts['v1'];

        // Rejeter si timestamp trop éloigné (> 5 min)
        $now = CarbonImmutable::now()->timestamp;
        if (abs($now - $timestamp) > 300) {
            return false;
        }

        $signingSecret = \App\Models\SystemSetting::get('wave_signing_secret', config('wave.signing_secret'));
        if (!$signingSecret) {
            return false;
        }

        $payloadToSign = $timestamp . $payload;
        $expectedSignature = hash_hmac('sha256', $payloadToSign, $signingSecret);

        return hash_equals($expectedSignature, $providedSignature);
    }

    /**
    * Parse l’en-tête Wave-Signature "t=...,v1=...".
    *
    * @return array{t:string|null,v1:string|null}|null
    */
    protected function parseHeader(string $header): ?array
    {
        $result = ['t' => null, 'v1' => null];

        foreach (explode(',', $header) as $part) {
            [$key, $value] = array_map('trim', explode('=', $part, 2) + [null, null]);

            if ($key === 't') {
                $result['t'] = $value;
            } elseif ($key === 'v1') {
                $result['v1'] = $value;
            }
        }

        if ($result['t'] === null || $result['v1'] === null) {
            return null;
        }

        return $result;
    }
}

