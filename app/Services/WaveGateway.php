<?php

namespace App\Services;

use App\Models\Order;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class WaveGateway
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $webhookSecret;
    protected string $currency;

    public function __construct()
    {
        $this->baseUrl = config('wave.base_url', 'https://api.wave.com');
        $this->apiKey = SystemSetting::get('wave_api_key', config('wave.api_key', ''));
        $this->webhookSecret = SystemSetting::get('wave_webhook_secret', config('wave.webhook_secret', ''));
        $this->currency = config('wave.currency', 'XOF');
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey);
    }

    /**
     * Crée une session de checkout Wave pour une commande.
     * Le client est redirigé vers wave_launch_url pour payer.
     */
    public function createCheckoutSession(Order $order, string $successUrl, string $errorUrl): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'error' => 'Wave API key not configured'];
        }

        $amount = (string) (int) $order->total;
        $clientReference = "ORDER-{$order->id}-{$order->reference}";

        try {
            $response = Http::withToken($this->apiKey)
                ->timeout(30)
                ->post("{$this->baseUrl}/v1/checkout/sessions", [
                    'amount' => $amount,
                    'currency' => $this->currency,
                    'client_reference' => $clientReference,
                    'success_url' => $successUrl,
                    'error_url' => $errorUrl,
                ]);

            if ($response->successful()) {
                $data = $response->json();

                Log::channel('payments')->info('Wave checkout session created', [
                    'order_id' => $order->id,
                    'checkout_id' => $data['id'] ?? null,
                    'wave_launch_url' => $data['wave_launch_url'] ?? null,
                ]);

                return [
                    'success' => true,
                    'checkout_id' => $data['id'],
                    'wave_launch_url' => $data['wave_launch_url'],
                    'checkout_status' => $data['checkout_status'] ?? 'open',
                ];
            }

            Log::channel('payments')->error('Wave checkout creation failed', [
                'order_id' => $order->id,
                'status' => $response->status(),
                'body' => $response->json(),
            ]);

            return [
                'success' => false,
                'error' => $response->json('message') ?? 'Erreur Wave checkout',
            ];
        } catch (\Exception $e) {
            Log::channel('payments')->error('Wave checkout exception', [
                'order_id' => $order->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Impossible de contacter Wave',
            ];
        }
    }

    /**
     * Récupère le statut d'une session de checkout.
     */
    public function getCheckoutSession(string $checkoutId): array
    {
        try {
            $response = Http::withToken($this->apiKey)
                ->timeout(15)
                ->get("{$this->baseUrl}/v1/checkout/sessions/{$checkoutId}");

            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()];
            }

            return ['success' => false, 'error' => $response->json('message') ?? 'Session introuvable'];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Effectue un payout (transfert d'argent) vers un numéro mobile.
     */
    public function payout(string $mobile, int $amount, string $recipientName = '', string $reason = '', string $clientReference = ''): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'error' => 'Wave API key not configured'];
        }

        if ($amount < 1) {
            return ['success' => false, 'error' => 'Montant invalide'];
        }

        $idempotencyKey = $clientReference ?: Str::uuid()->toString();

        $payload = [
            'currency' => $this->currency,
            'receive_amount' => (string) $amount,
            'mobile' => $mobile,
        ];

        if ($recipientName) {
            $payload['name'] = Str::limit($recipientName, 255);
        }
        if ($reason) {
            $payload['payment_reason'] = Str::limit($reason, 40);
        }
        if ($clientReference) {
            $payload['client_reference'] = Str::limit($clientReference, 255);
        }

        try {
            $response = Http::withToken($this->apiKey)
                ->withHeaders(['Idempotency-Key' => $idempotencyKey])
                ->timeout(60)
                ->post("{$this->baseUrl}/v1/payout", $payload);

            if ($response->successful()) {
                $data = $response->json();

                Log::channel('payments')->info('Wave payout succeeded', [
                    'payout_id' => $data['id'] ?? null,
                    'amount' => $amount,
                    'mobile' => $mobile,
                    'status' => $data['status'] ?? null,
                ]);

                return [
                    'success' => true,
                    'payout_id' => $data['id'],
                    'status' => $data['status'] ?? 'processing',
                    'fee' => $data['fee'] ?? 0,
                    'data' => $data,
                ];
            }

            $errorData = $response->json();
            Log::channel('payments')->error('Wave payout failed', [
                'status' => $response->status(),
                'mobile' => $mobile,
                'amount' => $amount,
                'error' => $errorData,
            ]);

            return [
                'success' => false,
                'error' => $errorData['message'] ?? "Erreur Wave (HTTP {$response->status()})",
                'data' => $errorData,
            ];
        } catch (\Exception $e) {
            Log::channel('payments')->error('Wave payout exception', [
                'mobile' => $mobile,
                'amount' => $amount,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Impossible de contacter Wave pour le payout',
            ];
        }
    }

    /**
     * Vérifie un destinataire avant payout.
     */
    public function verifyRecipient(string $mobile): array
    {
        try {
            $response = Http::withToken($this->apiKey)
                ->timeout(15)
                ->post("{$this->baseUrl}/v1/verify_recipient/", [
                    'mobile' => $mobile,
                ]);

            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()];
            }

            return ['success' => false, 'error' => $response->json('message') ?? 'Vérification impossible'];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Récupère le solde du wallet Wave Business.
     */
    public function getBalance(): array
    {
        try {
            $response = Http::withToken($this->apiKey)
                ->timeout(15)
                ->get("{$this->baseUrl}/v1/balance");

            if ($response->successful()) {
                return ['success' => true, 'data' => $response->json()];
            }

            return ['success' => false, 'error' => 'Impossible de récupérer le solde'];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Vérifie la signature d'un webhook Wave.
     * Wave envoie: Wave-Signature: t={timestamp},v1={hmac}
     */
    public function verifyWebhookSignature(string $rawPayload, string $signatureHeader): bool
    {
        if (empty($this->webhookSecret) || empty($signatureHeader)) {
            return false;
        }

        $parts = [];
        foreach (explode(',', $signatureHeader) as $part) {
            [$key, $value] = explode('=', $part, 2) + [null, null];
            if ($key && $value) {
                $parts[$key] = $value;
            }
        }

        $timestamp = $parts['t'] ?? null;
        $signature = $parts['v1'] ?? null;

        if (!$timestamp || !$signature) {
            return false;
        }

        // Vérifier que le timestamp n'est pas trop vieux (5 min)
        $age = abs(time() - (int) $timestamp);
        if ($age > 300) {
            Log::channel('payments')->warning('Wave webhook: timestamp too old', ['age' => $age]);
            return false;
        }

        $expected = hash_hmac('sha256', $timestamp . $rawPayload, $this->webhookSecret);

        return hash_equals($expected, $signature);
    }
}
