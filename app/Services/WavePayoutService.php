<?php

namespace App\Services;

use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class WavePayoutService
{
    public function __construct(
        protected WaveSignatureService $signatureService,
    ) {
    }

    protected function baseUrl(): string
    {
        return rtrim((string) config('wave.base_url'), '/');
    }

    protected function apiKey(): string
    {
        // Priorité au paramètre défini dans le backoffice super-admin
        $apiKey = \App\Models\SystemSetting::get('wave_api_key', config('wave.api_key'));
        if (!$apiKey) {
            throw new \RuntimeException('Clé API Wave manquante (WAVE_API_KEY).');
        }

        return $apiKey;
    }

    /**
     * Vérifie un bénéficiaire avant payout (within_limits + name_match).
     *
     * @return array{within_limits:bool|null,name_match:?string,national_id_match?:?string}
     */
    public function verifyRecipient(string $mobile, string $name, int $amount): array
    {
        $payload = [
            'amount' => (string) $amount,
            'currency' => 'XOF',
            'mobile' => $mobile,
            'name' => $name,
        ];

        $body = json_encode($payload, JSON_THROW_ON_ERROR);
        $signature = $this->signatureService->generateSignature($body);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey(),
                'Wave-Signature' => $signature,
                'Content-Type' => 'application/json',
            ])
                ->withBody($body, 'application/json')
                ->post($this->baseUrl() . '/v1/verify_recipient/');

            if ($response->failed()) {
                $response->throw();
            }

            $data = $response->json();
            if (!is_array($data)) {
                throw new \RuntimeException('Réponse Wave invalide pour verify_recipient.');
            }

            return $data;
        } catch (RequestException $e) {
            throw new \RuntimeException(
                'Erreur Wave verify_recipient : ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Envoie un payout Wave.
     *
     * Règles :
     * - appelle verifyRecipient avant l’envoi
     * - gère erreurs top-level ET payout_error sur l’objet retourné
     *
     * @param  array{
     *     restaurant_id:int,
     *     amount:int,
     *     mobile:string,
     *     recipient_name:string,
     *     client_reference?:string,
     *     payment_reason?:string,
     *     idempotency_key:string
     * }  $params
     * @return array{raw:array,status:string,has_error:bool}
     */
    public function sendPayout(array $params): array
    {
        $amount = (int) $params['amount'];
        if ($amount <= 0) {
            throw new \InvalidArgumentException('Le montant du payout doit être strictement positif.');
        }

        // 1) Vérifier le bénéficiaire
        $verify = $this->verifyRecipient(
            $params['mobile'],
            $params['recipient_name'],
            $amount
        );

        if (isset($verify['within_limits']) && $verify['within_limits'] === false) {
            throw new \DomainException(
                'Le bénéficiaire ne peut pas recevoir ce montant (limite atteinte ou dépassée).'
            );
        }

        // Optionnel : on peut renforcer sur name_match si besoin

        // 2) Envoi du payout
        $payload = [
            'currency' => 'XOF',
            'receive_amount' => (string) $amount,
            'mobile' => $params['mobile'],
            'name' => $params['recipient_name'],
        ];

        if (!empty($params['client_reference'])) {
            $payload['client_reference'] = $params['client_reference'];
        }

        if (!empty($params['payment_reason'])) {
            $payload['payment_reason'] = $params['payment_reason'];
        }

        $body = json_encode($payload, JSON_THROW_ON_ERROR);
        $signature = $this->signatureService->generateSignature($body);

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey(),
                'Wave-Signature' => $signature,
                'Idempotency-Key' => $params['idempotency_key'],
                'Content-Type' => 'application/json',
            ])
                ->withBody($body, 'application/json')
                ->post($this->baseUrl() . '/v1/payout');

            if ($response->failed()) {
                // Erreur top-level
                $response->throw();
            }

            $data = $response->json();
            if (!is_array($data)) {
                throw new \RuntimeException('Réponse Wave invalide pour payout.');
            }

            $hasError = isset($data['payout_error']) && !empty($data['payout_error']);

            return [
                'raw' => $data,
                'status' => $data['status'] ?? 'processing',
                'has_error' => $hasError,
            ];
        } catch (RequestException $e) {
            throw new \RuntimeException(
                'Erreur Wave payout : ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Récupère un payout par ID Wave.
     *
     * @return array{raw:array,status:string}
     */
    public function getPayout(string $payoutId): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey(),
            ])->get($this->baseUrl() . '/v1/payout/' . $payoutId);

            if ($response->failed()) {
                $response->throw();
            }

            $data = $response->json();
            if (!is_array($data)) {
                throw new \RuntimeException('Réponse Wave invalide pour get payout.');
            }

            return [
                'raw' => $data,
                'status' => $data['status'] ?? 'processing',
            ];
        } catch (RequestException $e) {
            throw new \RuntimeException(
                'Erreur Wave get payout : ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Reverse un payout existant (dans la fenêtre de 3 jours).
     */
    public function reversePayout(string $payoutId, string $idempotencyKey): bool
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey(),
                'Idempotency-Key' => $idempotencyKey,
            ])->post($this->baseUrl() . '/v1/payout/' . $payoutId . '/reverse');

            if ($response->status() === 200) {
                return true;
            }

            if ($response->failed()) {
                $response->throw();
            }

            return false;
        } catch (RequestException $e) {
            throw new \RuntimeException(
                'Erreur Wave reverse payout : ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Envoie un batch de payouts.
     *
     * @param  array<int,array{currency:string,receive_amount:string,name:string,mobile:string,client_reference?:string,payment_reason?:string}>  $payouts
     * @return array{batch_id:string,raw:array}
     */
    public function sendPayoutBatch(array $payouts): array
    {
        $payload = [
            'payouts' => $payouts,
        ];

        $body = json_encode($payload, JSON_THROW_ON_ERROR);
        $signature = $this->signatureService->generateSignature($body);

        $idempotencyKey = 'BATCH-' . Str::uuid()->toString();

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey(),
                'Wave-Signature' => $signature,
                'Idempotency-Key' => $idempotencyKey,
                'Content-Type' => 'application/json',
            ])
                ->withBody($body, 'application/json')
                ->post($this->baseUrl() . '/v1/payout-batch');

            if ($response->failed()) {
                $response->throw();
            }

            $data = $response->json();
            if (!is_array($data) || empty($data['id'])) {
                throw new \RuntimeException('Réponse Wave invalide pour payout-batch.');
            }

            return [
                'batch_id' => $data['id'],
                'raw' => $data,
            ];
        } catch (RequestException $e) {
            throw new \RuntimeException(
                'Erreur Wave payout-batch : ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }

    /**
     * Récupère un batch de payouts.
     *
     * @return array{raw:array,status:string}
     */
    public function getPayoutBatch(string $batchId): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey(),
            ])->get($this->baseUrl() . '/v1/payout-batch/' . $batchId);

            if ($response->failed()) {
                $response->throw();
            }

            $data = $response->json();
            if (!is_array($data)) {
                throw new \RuntimeException('Réponse Wave invalide pour get payout-batch.');
            }

            return [
                'raw' => $data,
                'status' => $data['status'] ?? 'processing',
            ];
        } catch (RequestException $e) {
            throw new \RuntimeException(
                'Erreur Wave get payout-batch : ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }
}

