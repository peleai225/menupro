<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MoneyFusionGateway
{
    protected ?string $apiUrl = null;
    protected ?string $apiKey = null;

    public function __construct()
    {
        // Chargement lazy — pas d'appel DB au boot
    }

    protected function getApiUrl(): string
    {
        if ($this->apiUrl === null) {
            $this->apiUrl = SystemSetting::get('moneyfusion_api_url', config('moneyfusion.api_url', ''));
        }

        return $this->apiUrl;
    }

    protected function getApiKey(): string
    {
        if ($this->apiKey === null) {
            $this->apiKey = SystemSetting::get('moneyfusion_api_key', config('moneyfusion.api_key', ''));
        }

        return $this->apiKey;
    }

    public function isConfigured(): bool
    {
        return !empty($this->getApiUrl());
    }

    /**
     * Initie un paiement pour un abonnement.
     */
    public function createPayment(Subscription $subscription, string $returnUrl): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'error' => 'MoneyFusion non configuré'];
        }

        $restaurant = $subscription->restaurant;
        $plan = $subscription->plan;
        $amount = (int) $subscription->amount_paid;

        if ($amount < 1) {
            return ['success' => false, 'error' => 'Montant insuffisant'];
        }

        $headers = ['Content-Type' => 'application/json'];
        if (!empty($this->getApiKey())) {
            $headers['Authorization'] = 'Bearer ' . $this->getApiKey();
        }

        $webhookUrl = route('webhooks.moneyfusion');

        try {
            $response = Http::withHeaders($headers)
                ->timeout(30)
                ->post($this->getApiUrl(), [
                    'totalPrice' => $amount,
                    'article' => [
                        ["Abonnement {$plan->name}" => $amount],
                    ],
                    'numeroSend' => $restaurant->phone ?? '',
                    'nomclient' => $restaurant->name,
                    'personal_Info' => [
                        [
                            'subscription_id' => $subscription->id,
                            'restaurant_id' => $restaurant->id,
                        ],
                    ],
                    'return_url' => $returnUrl,
                    'webhook_url' => $webhookUrl,
                ]);

            if ($response->successful()) {
                $data = $response->json();

                if ($data['statut'] === true) {
                    // Tronquer le token pour éviter de logger un identifiant d'activation en clair
                    $tokenPreview = substr($data['token'] ?? '', 0, 8) . '...';
                    Log::channel('payments')->info('MoneyFusion payment initiated', [
                        'subscription_id' => $subscription->id,
                        'token_prefix'    => $tokenPreview,
                    ]);

                    return [
                        'success' => true,
                        'token' => $data['token'],
                        'payment_url' => $data['url'],
                    ];
                }

                return ['success' => false, 'error' => $data['message'] ?? 'Erreur MoneyFusion'];
            }

            Log::channel('payments')->error('MoneyFusion HTTP error', [
                'subscription_id' => $subscription->id,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return ['success' => false, 'error' => "Erreur HTTP {$response->status()}"];

        } catch (\Exception $e) {
            Log::channel('payments')->error('MoneyFusion exception', [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage(),
            ]);

            return ['success' => false, 'error' => 'Impossible de contacter MoneyFusion'];
        }
    }

    /**
     * Vérifie le statut d'un paiement via son token.
     * GET https://www.pay.moneyfusion.net/paiementNotif/{token}
     */
    public function verifyPayment(string $token): array
    {
        try {
            $response = Http::timeout(15)
                ->get("https://www.pay.moneyfusion.net/paiementNotif/{$token}");

            if ($response->successful()) {
                $data = $response->json();
                $status = $data['data']['statut'] ?? 'unknown';

                return [
                    'success' => true,
                    'paid' => $status === 'paid',
                    'status' => $status,
                    'data' => $data['data'] ?? [],
                ];
            }

            return ['success' => false, 'error' => "HTTP {$response->status()}"];

        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
