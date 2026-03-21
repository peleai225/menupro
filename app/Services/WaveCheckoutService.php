<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PaymentTransaction;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Str;

class WaveCheckoutService
{
    public function __construct(
        protected WaveSignatureService $signatureService,
    ) {
    }

    /**
     * Crée une session de paiement Wave Checkout pour une commande.
     *
     * @param  array{amount:int,restaurant_id:int,order_id:int,currency?:string,client_reference?:string,success_url?:string,error_url?:string}  $params
     * @return array{wave_launch_url:string,checkout_id?:string,raw:array}
     *
     * @throws \Throwable
     */
    public function createSession(array $params): array
    {
        $baseUrl = rtrim((string) config('wave.base_url'), '/');
        // Priorité aux paramètres configurés dans le backoffice, sinon .env
        $apiKey = \App\Models\SystemSetting::get('wave_api_key', config('wave.api_key'));

        if (!$apiKey) {
            throw new \RuntimeException('Clé API Wave manquante (WAVE_API_KEY).');
        }

        $currency = $params['currency'] ?? 'XOF';

        // Wave attend un montant entier sous forme de string (pas de décimales).
        $amountInt = (int) $params['amount'];
        if ($amountInt <= 0) {
            throw new \InvalidArgumentException('Le montant Wave doit être strictement positif.');
        }

        $clientReference = $params['client_reference']
            ?? sprintf('ORDER-%d-%d-%s', $params['restaurant_id'], $params['order_id'], Str::uuid()->toString());

        $payload = [
            'amount' => (string) $amountInt,
            'currency' => $currency,
            'client_reference' => $clientReference,
            'success_url' => $params['success_url'] ?? config('wave.success_url'),
            'error_url' => $params['error_url'] ?? config('wave.error_url'),
        ];

        // Paiement direct vers le compte Wave Business du restaurant.
        // Si le restaurant a configuré son Wave Merchant ID, les fonds lui
        // sont versés directement (pas de transit par le compte plateforme).
        // La commission est ensuite facturée via l'abonnement.
        if (!empty($params['merchant_id'])) {
            $payload['merchant_id'] = (string) $params['merchant_id'];
        }

        $body = json_encode($payload, JSON_THROW_ON_ERROR);

        $signature = $this->signatureService->generateSignature($body);

        $idempotencyKey = sprintf(
            'CHECKOUT-%d-%d-%s',
            $params['restaurant_id'],
            $params['order_id'],
            Str::uuid()->toString()
        );

        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $apiKey,
                'Wave-Signature' => $signature,
                'Idempotency-Key' => $idempotencyKey,
                'Content-Type' => 'application/json',
            ])
                ->withBody($body, 'application/json')
                ->post($baseUrl . '/v1/checkout/sessions');

            if ($response->failed()) {
                // Erreur top-level (validation / auth / autre)
                $response->throw();
            }

            $data = $response->json();

            if (!is_array($data)) {
                throw new \RuntimeException('Réponse Wave invalide pour la création de session Checkout.');
            }

            $launchUrl = $data['launch_url'] ?? $data['checkout_url'] ?? null;
            if (!$launchUrl) {
                throw new \RuntimeException('URL de lancement Wave introuvable dans la réponse.');
            }

            return [
                'wave_launch_url' => $launchUrl,
                'checkout_id' => $data['id'] ?? null,
                'raw' => $data,
            ];
        } catch (RequestException $e) {
            throw new \RuntimeException(
                'Erreur lors de la création de la session Wave Checkout : ' . $e->getMessage(),
                $e->getCode(),
                $e
            );
        }
    }
}

