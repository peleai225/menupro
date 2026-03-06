<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Restaurant;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class LygosGateway
{
    protected string $baseUrl;
    protected ?string $apiKey = null;
    protected ?string $apiSecret = null;
    protected bool $isPlatformMode = false;

    public function __construct()
    {
        $this->baseUrl = config('services.lygos.base_url', 'https://api.lygosapp.com/v1');
    }

    /**
     * Set credentials for a specific restaurant
     */
    public function forRestaurant(Restaurant $restaurant): static
    {
        $this->isPlatformMode = false;
        $this->apiKey = $restaurant->getLygosApiKey();
        $this->apiSecret = $restaurant->getLygosApiSecret();

        return $this;
    }

    /**
     * Set credentials for platform (super admin) - used for subscriptions
     */
    public function forPlatform(): static
    {
        $this->isPlatformMode = true;
        $this->apiKey = \App\Models\SystemSetting::get('lygos_api_key', '');
        $this->apiSecret = \App\Models\SystemSetting::get('lygos_webhook_secret', '');

        return $this;
    }

    /**
     * Check if Lygos is configured
     * Note: According to Lygos documentation, only api-key is required for authentication
     * En mode plateforme : vérifie aussi lygos_enabled pour activer/désactiver Lygos
     */
    public function isConfigured(): bool
    {
        if ($this->isPlatformMode && !\App\Models\SystemSetting::get('lygos_enabled', true)) {
            return false;
        }

        return !empty($this->apiKey);
    }

    /**
     * Create a payment session for an order
     * According to Lygos documentation: POST /v1/gateway
     * Required: amount, shop_name
     * Optional: message, success_url, failure_url, order_id
     */
    public function createPayment(Order $order, string $returnUrl, string $cancelUrl): array
    {
        if (!$this->isConfigured()) {
            throw new \Exception('Lygos API credentials not configured');
        }

        $payload = [
            'amount' => (int) $order->total, // Required: integer
            'shop_name' => $order->restaurant->name, // Required: string
            'message' => "Commande {$order->reference}", // Optional
            'success_url' => $returnUrl, // Optional
            'failure_url' => $cancelUrl, // Optional
            'order_id' => $order->reference, // Optional: used to track the order
        ];

        return $this->createPaymentSession($payload, [
            'order_id' => $order->id,
            'restaurant_id' => $order->restaurant_id,
            'type' => 'order',
        ]);
    }

    /**
     * Create a payment session for a subscription
     * According to Lygos documentation: POST /v1/gateway
     */
    public function createSubscriptionPayment(\App\Models\Subscription $subscription, string $returnUrl, string $cancelUrl): array
    {
        if (!$this->isConfigured()) {
            throw new \Exception('Lygos API credentials not configured');
        }

        $restaurant = $subscription->restaurant;
        $plan = $subscription->plan;
        $reference = 'SUB-' . $subscription->id . '-' . now()->format('Ymd');

        $payload = [
            'amount' => (int) $subscription->amount_paid, // Required: integer
            'shop_name' => $restaurant->name, // Required: string
            'message' => "Abonnement {$plan->name} - {$restaurant->name}", // Optional
            'success_url' => $returnUrl, // Optional
            'failure_url' => $cancelUrl, // Optional
            'order_id' => $reference, // Optional: used to track the subscription
        ];

        return $this->createPaymentSession($payload, [
            'subscription_id' => $subscription->id,
            'restaurant_id' => $restaurant->id,
            'plan_id' => $plan->id,
            'type' => 'subscription',
        ]);
    }

    /**
     * Create a payment session for registration (public method)
     */
    public function createRegistrationPayment(array $payload, array $metadata = []): array
    {
        return $this->createPaymentSession($payload, $metadata);
    }

    /**
     * Create payment session (internal method)
     * According to Lygos documentation: POST /v1/gateway
     * Response contains: id, amount, currency, shop_name, user_id, creation_date, link, message, order_id, success_url, failure_url
     */
    protected function createPaymentSession(array $payload, array $metadata = []): array
    {
        try {
            $response = Http::withHeaders($this->getHeaders())
                ->timeout(30)
                ->post("{$this->baseUrl}/gateway", $payload);

            if ($response->successful()) {
                $data = $response->json();
                
                $type = $metadata['type'] ?? 'unknown';
                $reference = $payload['order_id'] ?? 'unknown';
                
                Log::channel('payments')->info('Lygos payment created', [
                    'type' => $type,
                    'reference' => $reference,
                    'payment_id' => $data['id'] ?? null,
                    'link' => $data['link'] ?? null,
                    'metadata' => $metadata,
                ]);

                return [
                    'success' => true,
                    'payment_id' => $data['id'] ?? null,
                    'payment_url' => $data['link'] ?? null, // Lygos returns 'link' not 'payment_url'
                    'expires_at' => isset($data['creation_date']) ? $data['creation_date'] : null,
                ];
            }

            Log::channel('payments')->error('Lygos payment creation failed', [
                'type' => $metadata['type'] ?? 'unknown',
                'reference' => $payload['order_id'] ?? 'unknown',
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            $errorMessage = $response->json('message') ?? $response->json('error') ?? 'Erreur lors de la création du paiement';
            
            return [
                'success' => false,
                'error' => $errorMessage,
            ];

        } catch (\Exception $e) {
            Log::channel('payments')->error('Lygos payment exception', [
                'type' => $metadata['type'] ?? 'unknown',
                'reference' => $payload['order_id'] ?? 'unknown',
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Impossible de contacter le service de paiement',
            ];
        }
    }

    /**
     * Verify payment status using order_id
     * According to Lygos documentation: GET /v1/gateway/payin/{order_id}
     * Returns: { "order_id": "...", "status": "..." }
     */
    public function verifyPayment(string $orderId): array
    {
        if (!$this->isConfigured()) {
            throw new \Exception('Lygos API credentials not configured');
        }

        try {
            $response = Http::withHeaders($this->getHeaders())
                ->timeout(30)
                ->get("{$this->baseUrl}/gateway/payin/" . urlencode($orderId));

            if ($response->successful()) {
                $data = $response->json();
                
                // Lygos returns: { "order_id": "...", "status": "..." }
                $status = $data['status'] ?? 'unknown';
                
                return [
                    'success' => true,
                    'status' => $status,
                    'paid' => in_array(strtolower($status), ['completed', 'success', 'paid']),
                    'order_id' => $data['order_id'] ?? $orderId,
                ];
            }

            return [
                'success' => false,
                'error' => $response->json('message') ?? $response->json('error') ?? 'Erreur lors de la vérification',
            ];

        } catch (\Exception $e) {
            Log::channel('payments')->error('Lygos verify exception', [
                'order_id' => $orderId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Impossible de vérifier le paiement',
            ];
        }
    }

    /**
     * Initiate a refund
     */
    public function refund(string $paymentId, int $amount = null, string $reason = null): array
    {
        if (!$this->isConfigured()) {
            throw new \Exception('Lygos API credentials not configured');
        }

        $payload = array_filter([
            'amount' => $amount,
            'reason' => $reason,
        ]);

        try {
            $response = Http::withHeaders($this->getHeaders())
                ->timeout(30)
                ->post("{$this->baseUrl}/v1/payments/{$paymentId}/refund", $payload);

            if ($response->successful()) {
                $data = $response->json();
                
                Log::channel('payments')->info('Lygos refund initiated', [
                    'payment_id' => $paymentId,
                    'refund_id' => $data['refund_id'] ?? null,
                    'amount' => $amount,
                ]);

                return [
                    'success' => true,
                    'refund_id' => $data['refund_id'],
                    'status' => $data['status'],
                ];
            }

            return [
                'success' => false,
                'error' => $response->json('message') ?? 'Erreur lors du remboursement',
            ];

        } catch (\Exception $e) {
            Log::channel('payments')->error('Lygos refund exception', [
                'payment_id' => $paymentId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Impossible de traiter le remboursement',
            ];
        }
    }

    /**
     * Verify webhook signature
     */
    public function verifyWebhookSignature(string $payload, string $signature): bool
    {
        if (!$this->apiSecret) {
            return false;
        }

        $expectedSignature = hash_hmac('sha256', $payload, $this->apiSecret);
        
        return hash_equals($expectedSignature, $signature);
    }

    /**
     * Get API headers
     * According to Lygos documentation: authentication via 'api-key' header
     */
    protected function getHeaders(): array
    {
        return [
            'api-key' => $this->apiKey,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }
}

