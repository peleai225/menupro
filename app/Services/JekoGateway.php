<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Restaurant;
use App\Models\Subscription;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class JekoGateway
{
    protected string $baseUrl = 'https://api.jeko.africa';
    protected ?string $apiKey = null;
    protected ?string $apiKeyId = null;
    protected ?string $webhookSecret = null;
    protected bool $isPlatformMode = false;

    public function forRestaurant(Restaurant $restaurant): static
    {
        $this->isPlatformMode = false;
        $this->apiKey = $restaurant->getJekoApiKey();
        $this->apiKeyId = $restaurant->getJekoApiKeyId();
        $this->webhookSecret = $restaurant->getJekoWebhookSecret();

        return $this;
    }

    public function forPlatform(): static
    {
        $this->isPlatformMode = true;
        $this->apiKey = SystemSetting::get('jeko_api_key', '');
        $this->apiKeyId = SystemSetting::get('jeko_api_key_id', '');
        $this->webhookSecret = SystemSetting::get('jeko_webhook_secret', '');

        return $this;
    }

    public function getPlatformStoreId(): ?string
    {
        return SystemSetting::get('jeko_store_id', '') ?: null;
    }

    public function isConfigured(): bool
    {
        if ($this->isPlatformMode && !SystemSetting::get('jeko_enabled', false)) {
            return false;
        }

        return !empty($this->apiKey) && !empty($this->apiKeyId);
    }

    /**
     * Crée un lien de paiement (payment_link) pour une commande.
     * Le client est redirigé vers https://pay.jeko.africa/c/{code}
     * et choisit son moyen de paiement (Wave, Orange, MTN, Moov, Djamo, carte).
     */
    public function createOrderPayment(Order $order, string $storeId): array
    {
        if (!$this->isConfigured()) {
            throw new \Exception('Jeko API credentials not configured');
        }

        // Jeko payment_links : montant en centimes (1 XOF = 100 centimes), minimum 10 000 centimes (100 XOF)
        $amountCents = (int) round($order->total * 100);
        if ($amountCents < 10000) {
            throw new \Exception("Montant Jeko insuffisant : {$amountCents} centimes (minimum 10 000)");
        }

        $payload = [
            'storeId' => $storeId,
            'title' => "Commande {$order->reference} - {$order->restaurant->name}",
            'amountCents' => $amountCents,
            'currency' => 'XOF',
            'allowMultiplePayments' => false,
        ];

        return $this->createPaymentLink($payload, [
            'order_id' => $order->id,
            'restaurant_id' => $order->restaurant_id,
            'type' => 'order',
        ]);
    }

    /**
     * Crée un lien de paiement pour un abonnement.
     */
    public function createSubscriptionPayment(Subscription $subscription, string $storeId): array
    {
        if (!$this->isConfigured()) {
            throw new \Exception('Jeko API credentials not configured');
        }

        $restaurant = $subscription->restaurant;
        $plan = $subscription->plan;
        $amountCents = (int) round($subscription->amount_paid * 100);
        if ($amountCents < 10000) {
            throw new \Exception("Montant Jeko insuffisant pour abonnement : {$amountCents} centimes (minimum 10 000)");
        }

        $payload = [
            'storeId' => $storeId,
            'title' => "Abonnement {$plan->name} - {$restaurant->name}",
            'amountCents' => $amountCents,
            'currency' => 'XOF',
            'allowMultiplePayments' => false,
        ];

        return $this->createPaymentLink($payload, [
            'subscription_id' => $subscription->id,
            'restaurant_id' => $restaurant->id,
            'plan_id' => $plan->id,
            'type' => 'subscription',
        ]);
    }

    /**
     * Crée un lien de paiement via POST /partner_api/payment_links.
     * Réponse : { id, storeId, title, amount, allowMultiplePayments, canReceivePayments, link }
     */
    protected function createPaymentLink(array $payload, array $metadata = []): array
    {
        try {
            $response = Http::withHeaders($this->getHeaders())
                ->timeout(30)
                ->post("{$this->baseUrl}/partner_api/payment_links", $payload);

            if ($response->successful()) {
                $data = $response->json();

                Log::channel('payments')->info('Jeko payment link created', [
                    'type' => $metadata['type'] ?? 'unknown',
                    'payment_link_id' => $data['id'] ?? null,
                    'payment_url' => $data['link'] ?? null,
                    'metadata' => $metadata,
                ]);

                return [
                    'success' => true,
                    'payment_id' => $data['id'] ?? null,
                    'payment_url' => $data['link'] ?? null,
                    'can_receive_payments' => $data['canReceivePayments'] ?? true,
                ];
            }

            $errorBody = $response->json();
            $errorMessage = $errorBody['message'] ?? $response->body();

            Log::channel('payments')->error('Jeko payment link creation failed', [
                'type' => $metadata['type'] ?? 'unknown',
                'status' => $response->status(),
                'body' => $errorBody,
            ]);

            return [
                'success' => false,
                'error' => $errorMessage,
            ];
        } catch (\Exception $e) {
            Log::channel('payments')->error('Jeko payment link exception', [
                'type' => $metadata['type'] ?? 'unknown',
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Impossible de contacter le service de paiement Jeko',
            ];
        }
    }

    /**
     * Vérifie le statut d'un lien de paiement via GET /partner_api/payment_links/{id}.
     * Retourne canReceivePayments = false si le paiement a été effectué (lien usage unique).
     */
    public function verifyPaymentLink(string $paymentLinkId): array
    {
        if (!$this->isConfigured()) {
            throw new \Exception('Jeko API credentials not configured');
        }

        try {
            $response = Http::withHeaders($this->getHeaders())
                ->timeout(30)
                ->get("{$this->baseUrl}/partner_api/payment_links/{$paymentLinkId}");

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'success' => true,
                    'payment_link_id' => $data['id'] ?? $paymentLinkId,
                    'paid' => isset($data['canReceivePayments']) && !$data['canReceivePayments'],
                    'can_receive_payments' => $data['canReceivePayments'] ?? true,
                    'data' => $data,
                ];
            }

            return [
                'success' => false,
                'error' => $response->json('message') ?? 'Erreur lors de la vérification',
            ];
        } catch (\Exception $e) {
            Log::channel('payments')->error('Jeko verify payment link exception', [
                'payment_link_id' => $paymentLinkId,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Impossible de vérifier le paiement',
            ];
        }
    }

    /**
     * Liste les magasins disponibles pour ce compte.
     * GET /partner_api/stores
     */
    public function getStores(): array
    {
        try {
            $response = Http::withHeaders($this->getHeaders())
                ->timeout(30)
                ->get("{$this->baseUrl}/partner_api/stores");

            if ($response->successful()) {
                return [
                    'success' => true,
                    'stores' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'error' => $response->json('message') ?? 'Erreur lors de la récupération des magasins',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Vérifie la signature HMAC-SHA256 d'un webhook.
     * Header: Jeko-Signature contient le HMAC du raw body.
     */
    public function verifyWebhookSignature(string $rawPayload, string $signature): bool
    {
        if (!$this->webhookSecret) {
            return false;
        }

        $expected = hash_hmac('sha256', $rawPayload, $this->webhookSecret);

        return hash_equals($expected, $signature);
    }

    protected function getHeaders(): array
    {
        return [
            'X-API-KEY' => $this->apiKey,
            'X-API-KEY-ID' => $this->apiKeyId,
            'Content-Type' => 'application/json',
            'Accept' => 'application/json',
        ];
    }
}
