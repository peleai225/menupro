<?php

namespace App\Services;

use App\Contracts\PaymentGatewayInterface;
use App\Models\JekoSubMerchant;
use App\Models\Order;
use App\Models\Restaurant;
use App\Models\Subscription;
use App\Models\SystemPaymentSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class JekoGateway implements PaymentGatewayInterface
{
    protected string $baseUrl;
    protected string $apiKey    = '';
    protected string $apiKeyId  = '';
    protected string $webhookSecret = '';
    protected string $storeId   = '';
    protected string $currency;
    protected int $timeout;
    protected int $payoutTimeout;

    protected bool $isMarketplaceMode = false;
    protected ?Restaurant $restaurant = null;

    public function __construct()
    {
        $this->baseUrl      = 'https://api.jeko.africa/partner_api';
        $this->currency     = config('jeko.currency', 'XOF');
        $this->timeout      = (int) config('jeko.timeout', 30);
        $this->payoutTimeout = (int) config('jeko.payout_timeout', 60);
    }

    public function forMarketplace(Restaurant $restaurant): static
    {
        $this->isMarketplaceMode = true;
        $this->restaurant = $restaurant;
        $this->loadConfig();
        return $this;
    }

    public function forPlatform(): static
    {
        $this->isMarketplaceMode = false;
        $this->restaurant = null;
        $this->loadConfig();
        return $this;
    }

    protected function loadConfig(): void
    {
        $gateway = $this->isMarketplaceMode ? 'jeko_marketplace' : 'jeko_normal';

        $setting = SystemPaymentSetting::where('gateway', $gateway)
            ->where('is_active', true)
            ->first();

        if ($setting) {
            $this->apiKey       = $setting->getDecryptedApiKey() ?? '';
            $this->apiKeyId     = $setting->merchant_id ?? '';
            $this->webhookSecret = $setting->getDecryptedWebhookSecret() ?? '';
            $this->storeId      = $setting->config['store_id'] ?? '';
        }
    }

    public function isConfigured(): bool
    {
        if (empty($this->apiKey)) {
            $this->loadConfig();
        }
        return !empty($this->apiKey) && !empty($this->apiKeyId);
    }

    protected function client(): \Illuminate\Http\Client\PendingRequest
    {
        return Http::withHeaders([
            'X-API-KEY'    => $this->apiKey,
            'X-API-KEY-ID' => $this->apiKeyId,
        ])->acceptJson();
    }

    /**
     * Crée un lien de paiement Jeko (Payment Link).
     * Le client choisit son opérateur sur la page Jeko.
     * Montant en FCFA — converti en centimes pour l'API.
     */
    public function createPayment(Order|Subscription $entity, string $successUrl, string $errorUrl): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'error' => 'Jeko API key not configured'];
        }

        $amountCents = (int) ($entity instanceof Order ? $entity->total : $entity->amount_paid) * 100;

        $title = $entity instanceof Order
            ? "Commande #{$entity->id} — " . ($entity->restaurant->name ?? 'MenuPro')
            : "Abonnement MenuPro";

        try {
            $response = $this->client()
                ->timeout($this->timeout)
                ->post("{$this->baseUrl}/payment_links", [
                    'storeId'              => $this->storeId,
                    'title'                => mb_substr($title, 0, 255),
                    'amountCents'          => $amountCents,
                    'currency'             => $this->currency,
                    'allowMultiplePayments' => false,
                ]);

            if ($response->successful()) {
                $data = $response->json();

                Log::channel('payments')->info('Jeko payment link created', [
                    'entity_type'  => $entity instanceof Order ? 'order' : 'subscription',
                    'entity_id'    => $entity->id,
                    'link_id'      => $data['id'] ?? null,
                    'amount_cents' => $amountCents,
                ]);

                return [
                    'success'     => true,
                    'payment_id'  => $data['id'],
                    'payment_url' => $data['link'],
                    'status'      => 'pending',
                ];
            }

            Log::channel('payments')->error('Jeko payment link failed', [
                'entity_type' => $entity instanceof Order ? 'order' : 'subscription',
                'entity_id'   => $entity->id,
                'status'      => $response->status(),
                'body'        => $response->json(),
            ]);

            return [
                'success' => false,
                'error'   => $response->json('message') ?? 'Erreur création lien de paiement Jeko',
            ];
        } catch (\Exception $e) {
            Log::channel('payments')->error('Jeko payment link exception', [
                'entity_type' => $entity instanceof Order ? 'order' : 'subscription',
                'entity_id'   => $entity->id,
                'error'       => $e->getMessage(),
            ]);

            return ['success' => false, 'error' => 'Impossible de contacter Jeko'];
        }
    }

    /**
     * Récupère le statut d'une demande de paiement.
     */
    public function getPaymentStatus(string $paymentId): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'error' => 'Jeko API key not configured'];
        }

        try {
            $response = $this->client()
                ->timeout(15)
                ->get("{$this->baseUrl}/payment_links/{$paymentId}");

            if ($response->successful()) {
                $data = $response->json();
                // Pour un lien à usage unique : canReceivePayments=false = paiement complété
                $completed = isset($data['allowMultiplePayments'])
                    && $data['allowMultiplePayments'] === false
                    && ($data['canReceivePayments'] ?? true) === false;

                return [
                    'success'   => true,
                    'status'    => $completed ? 'success' : 'pending',
                    'data'      => $data,
                ];
            }

            return ['success' => false, 'error' => 'Paiement introuvable'];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Effectue un payout (virement) vers un bénéficiaire.
     * $amount en FCFA — converti en centimes pour l'API.
     */
    public function payout(string $recipient, int $amount, string $recipientName = '', string $reason = '', string $reference = '', string $paymentMethod = 'wave'): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'error' => 'Jeko API key not configured'];
        }

        if ($amount < 1) {
            return ['success' => false, 'error' => 'Montant trop faible (min 1 FCFA)'];
        }

        if (empty($reference)) {
            throw new \InvalidArgumentException('payout() reference must not be empty — it is the idempotency key');
        }

        $amountCents = $amount * 100;
        $description = $reason ?: 'Reversement commande MenuPro';

        $contact = $this->createContact($recipient, $recipientName ?: 'Restaurant MenuPro', $paymentMethod);

        if (!$contact['success']) {
            return $contact;
        }

        try {
            $response = $this->client()
                ->timeout($this->payoutTimeout)
                ->post("{$this->baseUrl}/transfers", [
                    'storeId'     => $this->storeId,
                    'contactId'   => $contact['contact_id'],
                    'amountCents' => $amountCents,
                    'currency'    => $this->currency,
                    'description' => $description,
                    'reference'   => $reference,
                ]);

            if ($response->successful()) {
                $data = $response->json();

                Log::channel('payments')->info('Jeko payout succeeded', [
                    'transfer_id' => $data['id'] ?? null,
                    'recipient'   => $recipient,
                    'amount_cents' => $amountCents,
                    'reference'   => $reference,
                ]);

                return [
                    'success'     => true,
                    'transfer_id' => $data['id'],
                    'status'      => $data['status'] ?? 'pending',
                    'data'        => $data,
                ];
            }

            $errorData = $response->json();

            Log::channel('payments')->error('Jeko payout failed', [
                'status'    => $response->status(),
                'recipient' => $recipient,
                'amount_cents' => $amountCents,
                'error'     => $errorData,
            ]);

            return [
                'success' => false,
                'error'   => $errorData['message'] ?? "Erreur Jeko payout (HTTP {$response->status()})",
                'data'    => $errorData,
            ];
        } catch (\Exception $e) {
            Log::channel('payments')->error('Jeko payout exception', [
                'recipient'   => $recipient,
                'amount_cents' => $amountCents,
                'error'       => $e->getMessage(),
            ]);

            return ['success' => false, 'error' => 'Impossible de contacter Jeko pour le payout'];
        }
    }

    /**
     * Crée un contact bénéficiaire Jeko (Mobile Money).
     */
    public function createContact(string $phone, string $name = '', string $paymentMethod = 'wave'): array
    {
        try {
            $response = $this->client()
                ->timeout($this->timeout)
                ->post("{$this->baseUrl}/contacts", [
                    'name'          => $name ?: 'Restaurant MenuPro',
                    'paymentMethod' => $paymentMethod,
                    'identifier'    => ['number' => $phone],
                ]);

            if ($response->successful()) {
                return [
                    'success'    => true,
                    'contact_id' => $response->json('id'),
                ];
            }

            // Contact déjà existant — récupérer depuis la liste
            if (in_array($response->status(), [409, 422])) {
                return $this->getExistingContact($phone);
            }

            return [
                'success' => false,
                'error'   => $response->json('message') ?? 'Contact création échouée',
            ];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    protected function getExistingContact(string $phone): array
    {
        try {
            $response = $this->client()
                ->timeout($this->timeout)
                ->get("{$this->baseUrl}/contacts");

            if ($response->successful()) {
                $contacts = $response->json() ?? [];

                foreach ($contacts as $contact) {
                    $identifier = $contact['identifier']['number'] ?? '';
                    if ($identifier === $phone) {
                        return ['success' => true, 'contact_id' => $contact['id']];
                    }
                }
            }

            return ['success' => false, 'error' => 'Contact introuvable'];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }

    /**
     * Intègre un restaurant comme sous-marchand Jeko (Service Provider API).
     */
    public function integrateSubMerchant(JekoSubMerchant $subMerchant): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'error' => 'Jeko Marketplace API key not configured'];
        }

        $restaurant = $subMerchant->restaurant;

        try {
            // Étape 1 : onboarder l'entreprise
            $onboardResponse = $this->client()
                ->timeout($this->timeout)
                ->post("{$this->baseUrl}/service_providers/business_onboarding", [
                    'owner' => [
                        'phone'     => $subMerchant->mobile_money,
                        'firstName' => $restaurant?->name ?? $subMerchant->legal_name,
                        'lastName'  => 'Restaurant',
                        'sex'       => 'M',
                    ],
                    'business' => [
                        'name'     => $subMerchant->legal_name,
                        'category' => $subMerchant->business_type ?? 'food_and_beverage',
                    ],
                ]);

            if (!$onboardResponse->successful()) {
                // 409 = déjà onboardé, continuer avec la création de clé si on a le businessId
                if ($onboardResponse->status() !== 409) {
                    Log::channel('payments')->error('Jeko onboarding failed', [
                        'restaurant_id' => $subMerchant->restaurant_id,
                        'status'        => $onboardResponse->status(),
                        'error'         => $onboardResponse->json(),
                    ]);
                    return [
                        'success' => false,
                        'error'   => $onboardResponse->json('message') ?? 'Onboarding Jeko échoué',
                    ];
                }
            }

            $onboardData = $onboardResponse->json();
            $businessId  = $onboardData['business']['id'] ?? null;

            if (!$businessId) {
                return ['success' => false, 'error' => 'Jeko onboarding: businessId manquant dans la réponse'];
            }

            // Étape 2 : créer une clé API pour ce marchand
            $keyResponse = $this->client()
                ->timeout($this->timeout)
                ->post("{$this->baseUrl}/service_providers/business_api_keys", [
                    'merchantBusinessId' => $businessId,
                    'name'               => 'MenuPro — ' . $subMerchant->legal_name,
                ]);

            if (!$keyResponse->successful()) {
                Log::channel('payments')->error('Jeko API key creation failed', [
                    'restaurant_id' => $subMerchant->restaurant_id,
                    'business_id'   => $businessId,
                    'status'        => $keyResponse->status(),
                    'error'         => $keyResponse->json(),
                ]);
                return [
                    'success' => false,
                    'error'   => $keyResponse->json('message') ?? 'Création clé API marchand échouée',
                ];
            }

            $keyData = $keyResponse->json();

            Log::channel('payments')->info('Jeko sub-merchant integrated', [
                'restaurant_id' => $subMerchant->restaurant_id,
                'business_id'   => $businessId,
                'key_id'        => $keyData['id'] ?? null,
            ]);

            return [
                'success'     => true,
                'merchant_id' => $businessId,
                'store_id'    => $onboardData['business']['id'] ?? $businessId,
                'api_key'     => $keyData['key'] ?? null,
                'api_key_id'  => $keyData['id'] ?? null,
            ];
        } catch (\Exception $e) {
            Log::channel('payments')->error('Jeko sub-merchant integration exception', [
                'restaurant_id' => $subMerchant->restaurant_id,
                'error'         => $e->getMessage(),
            ]);

            return ['success' => false, 'error' => 'Impossible de contacter Jeko'];
        }
    }

    /**
     * Vérifie la signature HMAC d'un webhook Jeko.
     * Header: Jeko-Signature (HMAC-SHA256 hex du raw body)
     */
    public function verifyWebhookSignature(string $rawPayload, string $signatureHeader): bool
    {
        if (empty($this->webhookSecret) || empty($signatureHeader)) {
            return false;
        }

        $expected = hash_hmac('sha256', $rawPayload, $this->webhookSecret);

        return hash_equals($expected, $signatureHeader);
    }
}
