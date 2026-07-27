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
    protected string $apiKey;
    protected string $webhookSecret;
    protected string $currency;
    protected int $timeout;
    protected int $payoutTimeout;

    protected bool $isMarketplaceMode = false;
    protected ?Restaurant $restaurant = null;

    public function __construct()
    {
        $this->baseUrl = config('jeko.base_url');
        $this->currency = config('jeko.currency');
        $this->timeout = config('jeko.timeout');
        $this->payoutTimeout = config('jeko.payout_timeout');

        $this->apiKey = '';
        $this->webhookSecret = '';
    }

    /**
     * Configure le gateway en mode Marketplace (paiements restaurants).
     */
    public function forMarketplace(Restaurant $restaurant): static
    {
        $this->isMarketplaceMode = true;
        $this->restaurant = $restaurant;
        $this->loadConfig();

        return $this;
    }

    /**
     * Configure le gateway en mode Normal (paiements plateforme).
     */
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

        $setting = SystemPaymentSetting::where('gateway', $gateway)->where('is_active', true)->first();

        if ($setting) {
            $this->apiKey = $setting->getDecryptedApiKey() ?? '';
            $this->webhookSecret = $setting->getDecryptedWebhookSecret() ?? '';
        }
    }

    public function isConfigured(): bool
    {
        if (empty($this->apiKey)) {
            $this->loadConfig();
        }

        return !empty($this->apiKey);
    }

    /**
     * Crée une demande de paiement Jeko (Pay-in).
     */
    public function createPayment(Order|Subscription $entity, string $successUrl, string $errorUrl): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'error' => 'Jeko API key not configured'];
        }

        $amountFcfa = (int) ($entity instanceof Order ? $entity->total : $entity->amount_paid);

        $reference = $entity instanceof Order
            ? "ORDER-{$entity->id}-{$entity->reference}"
            : "SUB-{$entity->id}-" . now()->timestamp;

        try {
            $response = Http::withHeaders(['X-API-KEY-ID' => $this->apiKey])
                ->timeout($this->timeout)
                ->post("{$this->baseUrl}/demandes-de-paiement", [
                    'montant' => $amountFcfa,
                    'devise' => $this->currency,
                    'reference_client' => $reference,
                    'url_succes' => $successUrl,
                    'url_erreur' => $errorUrl,
                    'type' => 'redirect',
                ]);

            if ($response->successful()) {
                $data = $response->json();

                Log::channel('payments')->info('Jeko payment request created', [
                    'entity_type' => $entity instanceof Order ? 'order' : 'subscription',
                    'entity_id' => $entity->id,
                    'payment_id' => $data['id'] ?? null,
                    'amount' => $amountFcfa,
                ]);

                return [
                    'success' => true,
                    'payment_id' => $data['id'],
                    'payment_url' => $data['url_redirection'] ?? $data['url'],
                    'status' => $data['statut'] ?? 'pending',
                ];
            }

            Log::channel('payments')->error('Jeko payment request failed', [
                'entity_type' => $entity instanceof Order ? 'order' : 'subscription',
                'entity_id' => $entity->id,
                'status' => $response->status(),
                'body' => $response->json(),
            ]);

            return [
                'success' => false,
                'error' => $response->json('message') ?? 'Erreur Jeko payment request',
            ];
        } catch (\Exception $e) {
            Log::channel('payments')->error('Jeko payment request exception', [
                'entity_type' => $entity instanceof Order ? 'order' : 'subscription',
                'entity_id' => $entity->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Impossible de contacter Jeko',
            ];
        }
    }

    /**
     * Récupère le statut d'un paiement Jeko.
     */
    public function getPaymentStatus(string $paymentId): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'error' => 'Jeko API key not configured'];
        }

        try {
            $response = Http::withHeaders(['X-API-KEY-ID' => $this->apiKey])
                ->timeout(15)
                ->get("{$this->baseUrl}/demandes-de-paiement/{$paymentId}");

            if ($response->successful()) {
                return [
                    'success' => true,
                    'status' => $response->json('statut'),
                    'data' => $response->json(),
                ];
            }

            return [
                'success' => false,
                'error' => 'Paiement introuvable',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Effectue un payout (virement) vers un bénéficiaire.
     */
    public function payout(string $recipient, int $amount, string $recipientName = '', string $reason = '', string $reference = ''): array
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

        $amountFcfa = (int) $amount;
        $raison = $reason ?: 'Reversement commande MenuPro';

        $contact = $this->createContact($recipient);

        if (!$contact['success']) {
            return $contact;
        }

        $refClient = $reference;

        try {
            $response = Http::withHeaders(['X-API-KEY-ID' => $this->apiKey])
                ->timeout($this->payoutTimeout)
                ->post("{$this->baseUrl}/virements", [
                    'contact_id' => $contact['contact_id'],
                    'montant' => $amountFcfa,
                    'devise' => $this->currency,
                    'reference_client' => $refClient,
                    'raison' => $raison,
                ]);

            if ($response->successful()) {
                $data = $response->json();

                Log::channel('payments')->info('Jeko payout succeeded', [
                    'transfer_id' => $data['id'] ?? null,
                    'recipient' => $recipient,
                    'amount' => $amountFcfa,
                    'reference' => $refClient,
                ]);

                return [
                    'success' => true,
                    'transfer_id' => $data['id'],
                    'status' => $data['statut'] ?? 'processing',
                    'data' => $data,
                ];
            }

            $errorData = $response->json();

            Log::channel('payments')->error('Jeko payout failed', [
                'status' => $response->status(),
                'recipient' => $recipient,
                'amount' => $amountFcfa,
                'error' => $errorData,
            ]);

            return [
                'success' => false,
                'error' => $errorData['message'] ?? "Erreur Jeko payout (HTTP {$response->status()})",
                'data' => $errorData,
            ];
        } catch (\Exception $e) {
            Log::channel('payments')->error('Jeko payout exception', [
                'recipient' => $recipient,
                'amount' => $amountFcfa,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Impossible de contacter Jeko pour le payout',
            ];
        }
    }

    /**
     * Crée un contact bénéficiaire Jeko (pour payouts).
     */
    public function createContact(string $mobile): array
    {
        try {
            $response = Http::withHeaders(['X-API-KEY-ID' => $this->apiKey])
                ->timeout($this->timeout)
                ->post("{$this->baseUrl}/contacts", [
                    'type' => 'mobile_money',
                    'mobile' => $mobile,
                ]);

            if ($response->successful()) {
                return [
                    'success' => true,
                    'contact_id' => $response->json('id'),
                ];
            }

            if ($response->status() === 409 || $response->status() === 400) {
                return $this->getExistingContact($mobile);
            }

            return [
                'success' => false,
                'error' => $response->json('message') ?? 'Contact création échouée',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Récupère un contact existant par numéro mobile.
     */
    protected function getExistingContact(string $mobile): array
    {
        try {
            $response = Http::withHeaders(['X-API-KEY-ID' => $this->apiKey])
                ->timeout($this->timeout)
                ->get("{$this->baseUrl}/contacts", [
                    'mobile' => $mobile,
                ]);

            if ($response->successful()) {
                $contacts = $response->json('data') ?? [];

                if (!empty($contacts)) {
                    return [
                        'success' => true,
                        'contact_id' => $contacts[0]['id'],
                    ];
                }
            }

            return [
                'success' => false,
                'error' => 'Contact introuvable',
            ];
        } catch (\Exception $e) {
            return [
                'success' => false,
                'error' => $e->getMessage(),
            ];
        }
    }

    /**
     * Intègre un restaurant comme sous-marchand Jeko (Marketplace).
     */
    public function integrateSubMerchant(JekoSubMerchant $subMerchant): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'error' => 'Jeko Marketplace API key not configured'];
        }

        try {
            $response = Http::withHeaders(['X-API-KEY-ID' => $this->apiKey])
                ->timeout($this->timeout)
                ->post("{$this->baseUrl}/fournisseurs-de-services/integrer-entreprise", [
                    'nom_entreprise' => $subMerchant->legal_name,
                    'email' => $subMerchant->email,
                    'telephone' => $subMerchant->mobile_money,
                    'type_activite' => $subMerchant->business_type ?? 'restaurant',
                ]);

            if ($response->successful()) {
                $data = $response->json();

                Log::channel('payments')->info('Jeko sub-merchant integrated', [
                    'restaurant_id' => $subMerchant->restaurant_id,
                    'merchant_id' => $data['entreprise_id'] ?? null,
                    'store_id' => $data['magasin_id'] ?? null,
                ]);

                return [
                    'success' => true,
                    'merchant_id' => $data['entreprise_id'] ?? null,
                    'store_id'    => $data['magasin_id'] ?? null,
                    'wallet_id' => $data['portefeuille_id'] ?? null,
                ];
            }

            Log::channel('payments')->error('Jeko sub-merchant integration failed', [
                'restaurant_id' => $subMerchant->restaurant_id,
                'status' => $response->status(),
                'error' => $response->json(),
            ]);

            return [
                'success' => false,
                'error' => $response->json('message') ?? 'Intégration Jeko échouée',
            ];
        } catch (\Exception $e) {
            Log::channel('payments')->error('Jeko sub-merchant integration exception', [
                'restaurant_id' => $subMerchant->restaurant_id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Impossible de contacter Jeko',
            ];
        }
    }

    /**
     * Vérifie la signature HMAC d'un webhook Jeko.
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
