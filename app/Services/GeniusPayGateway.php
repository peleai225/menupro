<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Restaurant;
use App\Models\Subscription;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class GeniusPayGateway
{
    protected string $baseUrl;
    protected ?string $apiKey = null;
    protected ?string $apiSecret = null;

    public function __construct()
    {
        $this->baseUrl = config('services.geniuspay.base_url', 'https://pay.genius.ci/api/v1/merchant');
    }

    /**
     * Set credentials for a specific restaurant (commandes clients)
     */
    public function forRestaurant(Restaurant $restaurant): static
    {
        $this->apiKey = $restaurant->getGeniusPayApiKey();
        $this->apiSecret = $restaurant->getGeniusPayApiSecret();

        return $this;
    }

    /**
     * Set credentials for platform (super admin) - used for subscriptions
     */
    public function forPlatform(): static
    {
        $mode = \App\Models\SystemSetting::get('geniuspay_mode', 'sandbox');
        $this->apiKey = \App\Models\SystemSetting::get('geniuspay_api_key', '');
        $this->apiSecret = \App\Models\SystemSetting::get('geniuspay_api_secret', '');

        return $this;
    }

    /**
     * Check if GeniusPay is configured
     */
    public function isConfigured(): bool
    {
        return !empty($this->apiKey) && !empty($this->apiSecret);
    }

    /**
     * Create a payment session for a subscription
     * Uses GeniusPay checkout page (no payment_method = client chooses on GeniusPay page)
     */
    public function createSubscriptionPayment(Subscription $subscription, string $returnUrl, string $cancelUrl): array
    {
        if (!$this->isConfigured()) {
            throw new \Exception('GeniusPay API credentials not configured');
        }

        $restaurant = $subscription->restaurant;
        $plan = $subscription->plan;
        $reference = 'SUB-' . $subscription->id . '-' . now()->format('Ymd');

        $amount = max(200, (int) $subscription->amount_paid); // GeniusPay min: 200 XOF

        $payload = [
            'amount' => $amount,
            'currency' => 'XOF',
            'description' => "Abonnement {$plan->name} - {$restaurant->name}",
            'customer' => [
                'name' => $restaurant->name,
                'email' => $restaurant->email ?? '',
                'phone' => $this->normalizePhoneForGeniusPay($restaurant->phone ?? ''),
            ],
            'success_url' => $returnUrl,
            'error_url' => $cancelUrl,
            'metadata' => [
                'subscription_id' => $subscription->id,
                'restaurant_id' => $restaurant->id,
                'plan_id' => $plan->id,
                'type' => 'subscription',
                'order_id' => $reference,
            ],
        ];

        return $this->createPayment($payload);
    }

    /**
     * Create a payment session for an order (commandes clients)
     * Used when Lygos is not available for the restaurant
     */
    public function createOrderPayment(Order $order, string $returnUrl, string $cancelUrl): array
    {
        if (!$this->isConfigured()) {
            throw new \Exception('GeniusPay API credentials not configured');
        }

        $restaurant = $order->restaurant;
        $amount = max(200, (int) $order->total); // GeniusPay min: 200 XOF

        $customerPhone = $this->normalizePhoneForGeniusPay($order->customer_phone ?? '');
        $payload = [
            'amount' => $amount,
            'currency' => 'XOF',
            'description' => "Commande {$order->reference} - {$restaurant->name}",
            'customer' => [
                'name' => $order->customer_name,
                'email' => $order->customer_email ?? '',
                'phone' => $customerPhone,
            ],
            'success_url' => $returnUrl,
            'error_url' => $cancelUrl,
            'metadata' => [
                'order_id' => $order->id,
                'restaurant_id' => $restaurant->id,
                'type' => 'order',
                'reference' => $order->reference,
            ],
        ];

        // Option : paiement Wave direct (push dans l'app) au lieu du QR — utile si le scan QR ne déclenche pas le prélèvement
        if ($customerPhone && strlen($customerPhone) >= 12 && config('services.geniuspay.direct_wave', false)) {
            $payload['payment_method'] = 'wave';
        }
        return $this->createPayment($payload);
    }

    /**
     * Create payment session (internal)
     * POST /payments - without payment_method = checkout page
     * With payment_method=wave + valid phone = redirection directe Wave (prélèvement push)
     */
    protected function createPayment(array $payload): array
    {
        try {
            $response = Http::withHeaders($this->getHeaders())
                ->timeout(30)
                ->post("{$this->baseUrl}/payments", $payload);

            if ($response->successful()) {
                $json = $response->json();
                $data = $json['data'] ?? $json;

                $reference = $data['reference'] ?? $data['id'] ?? null;
                $checkoutUrl = $data['checkout_url'] ?? $data['payment_url'] ?? null;

                $environment = $data['environment'] ?? $json['environment'] ?? 'unknown';
                Log::channel('payments')->info('GeniusPay payment created', [
                    'reference' => $reference,
                    'environment' => $environment,
                    'checkout_url' => $checkoutUrl ? (str_contains($checkoutUrl, 'sandbox') ? '***sandbox***' : substr($checkoutUrl, 0, 50) . '...') : null,
                    'metadata' => $payload['metadata'] ?? [],
                ]);
                if (strtolower((string) $environment) === 'sandbox') {
                    Log::channel('payments')->warning('GeniusPay SANDBOX: aucun prélèvement réel. Passez en clés pk_live_/sk_live_ pour des vrais paiements.');
                }

                return [
                    'success' => true,
                    'payment_id' => $reference,
                    'payment_reference' => $reference,
                    'payment_url' => $checkoutUrl,
                    'expires_at' => $data['expires_at'] ?? null,
                ];
            }

            Log::channel('payments')->error('GeniusPay payment creation failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'metadata' => $payload['metadata'] ?? [],
            ]);

            $err = $response->json('error');
            $errorMessage = $response->json('detail')
                ?? $response->json('message')
                ?? (is_array($err) ? ($err['message'] ?? null) : $err)
                ?? 'Erreur lors de la création du paiement';

            return [
                'success' => false,
                'error' => $errorMessage,
            ];
        } catch (\Exception $e) {
            Log::channel('payments')->error('GeniusPay payment exception', [
                'metadata' => $payload['metadata'] ?? [],
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Impossible de contacter le service de paiement',
            ];
        }
    }

    /**
     * Verify payment status using reference
     * GET /payments/{reference}
     */
    public function verifyPayment(string $reference): array
    {
        if (!$this->isConfigured()) {
            throw new \Exception('GeniusPay API credentials not configured');
        }

        try {
            $response = Http::withHeaders($this->getHeaders())
                ->timeout(30)
                ->get("{$this->baseUrl}/payments/" . urlencode($reference));

            if ($response->successful()) {
                $json = $response->json();
                $data = $json['data'] ?? $json;
                $status = $data['status'] ?? 'unknown';

                return [
                    'success' => true,
                    'status' => $status,
                    'paid' => in_array(strtolower($status), ['completed', 'success', 'paid']),
                    'reference' => $data['reference'] ?? $reference,
                ];
            }

            $err = $response->json('error');
            $errorMsg = $response->json('detail')
                ?? $response->json('message')
                ?? (is_array($err) ? ($err['message'] ?? null) : $err)
                ?? 'Erreur lors de la vérification';
            return [
                'success' => false,
                'error' => $errorMsg,
            ];
        } catch (\Exception $e) {
            Log::channel('payments')->error('GeniusPay verify exception', [
                'reference' => $reference,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Impossible de vérifier le paiement',
            ];
        }
    }

    /**
     * Verify webhook signature
     * Format: HMAC-SHA256(timestamp + "." + json_payload, secret)
     */
    public function verifyWebhookSignature(string $payload, string $signature, string $timestamp): bool
    {
        $secret = \App\Models\SystemSetting::get('geniuspay_webhook_secret', '');
        if (!$secret) {
            return false;
        }

        $data = $timestamp . '.' . $payload;
        $expectedSignature = hash_hmac('sha256', $data, $secret);

        return hash_equals($expectedSignature, $signature);
    }

    protected function getHeaders(): array
    {
        return [
            'X-API-Key' => $this->apiKey,
            'X-API-Secret' => $this->apiSecret,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }

    /**
     * Normalize phone to +225XXXXXXXX for Wave/GeniusPay (Côte d'Ivoire).
     * Format requis pour les paiements Mobile Money.
     * En CI : 05 (MTN), 07 (Orange), 04 (Moov) — 9 chiffres après le 0.
     */
    protected function normalizePhoneForGeniusPay(string $phone): string
    {
        $phone = preg_replace('/\D/', '', trim($phone));
        if (empty($phone)) {
            return '';
        }
        // 8 chiffres commençant par 01 : souvent 05 oublié (ex: 01862640 → 0501862640)
        if (strlen($phone) === 8 && str_starts_with($phone, '01')) {
            $phone = '5' . $phone; // 01862640 → 501862640 → +225501862640
        }
        if (str_starts_with($phone, '225') && strlen($phone) >= 12) {
            return '+' . $phone;
        }
        if (str_starts_with($phone, '0') && strlen($phone) >= 9) {
            return '+225' . substr($phone, 1);
        }
        if (strlen($phone) >= 9) {
            return '+225' . $phone;
        }
        return '';
    }
}
