<?php

namespace App\Services;

use App\Models\Subscription;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MoneyFusionGateway
{
    protected string $baseUrl;
    protected string $apiKey;
    protected string $secretKey;
    protected string $currency;

    public function __construct()
    {
        $this->baseUrl = config('moneyfusion.base_url', 'https://api.moneyfusion.net');
        $this->apiKey = SystemSetting::get('moneyfusion_api_key', config('moneyfusion.api_key', ''));
        $this->secretKey = SystemSetting::get('moneyfusion_secret_key', config('moneyfusion.secret_key', ''));
        $this->currency = config('moneyfusion.currency', 'XOF');
    }

    public function isConfigured(): bool
    {
        return !empty($this->apiKey) && !empty($this->secretKey);
    }

    /**
     * Initie un paiement pour un abonnement.
     * Doc: POST /api/v1/payment/init
     */
    public function createPayment(Subscription $subscription, string $returnUrl, string $cancelUrl): array
    {
        if (!$this->isConfigured()) {
            return ['success' => false, 'error' => 'MoneyFusion non configuré'];
        }

        $restaurant = $subscription->restaurant;
        $plan = $subscription->plan;
        $amount = (int) $subscription->amount_paid;

        if ($amount < 100) {
            return ['success' => false, 'error' => 'Montant insuffisant'];
        }

        $reference = "SUB-{$subscription->id}-" . now()->timestamp;

        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
                'Content-Type' => 'application/json',
            ])
                ->timeout(30)
                ->post("{$this->baseUrl}/api/v1/payment/init", [
                    'totalPrice' => $amount,
                    'article' => [
                        [
                            'name' => "Abonnement {$plan->name} - {$restaurant->name}",
                            'quantity' => 1,
                            'unitPrice' => $amount,
                        ],
                    ],
                    'personal_Info' => [
                        [
                            'name' => $restaurant->user->name ?? $restaurant->name,
                            'email' => $restaurant->user->email ?? '',
                            'contact' => $restaurant->phone ?? '',
                        ],
                    ],
                    'currency' => $this->currency,
                    'reference' => $reference,
                    'return_url' => $returnUrl,
                    'cancel_url' => $cancelUrl,
                ]);

            if ($response->successful()) {
                $data = $response->json();
                $status = $data['statut'] ?? null;

                if ($status === true || $status === 'true') {
                    Log::channel('payments')->info('MoneyFusion payment initiated', [
                        'subscription_id' => $subscription->id,
                        'reference' => $reference,
                        'token' => $data['token'] ?? null,
                    ]);

                    return [
                        'success' => true,
                        'payment_url' => $data['url'] ?? null,
                        'token' => $data['token'] ?? null,
                        'reference' => $reference,
                    ];
                }

                Log::channel('payments')->error('MoneyFusion init failed', [
                    'subscription_id' => $subscription->id,
                    'response' => $data,
                ]);

                return [
                    'success' => false,
                    'error' => $data['message'] ?? 'Erreur MoneyFusion',
                ];
            }

            Log::channel('payments')->error('MoneyFusion HTTP error', [
                'subscription_id' => $subscription->id,
                'status' => $response->status(),
                'body' => $response->body(),
            ]);

            return [
                'success' => false,
                'error' => "Erreur HTTP {$response->status()}",
            ];
        } catch (\Exception $e) {
            Log::channel('payments')->error('MoneyFusion exception', [
                'subscription_id' => $subscription->id,
                'error' => $e->getMessage(),
            ]);

            return [
                'success' => false,
                'error' => 'Impossible de contacter MoneyFusion',
            ];
        }
    }

    /**
     * Vérifie le statut d'un paiement via son token.
     * Doc: GET /api/v1/payment/verify/{token}
     */
    public function verifyPayment(string $token): array
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => "Bearer {$this->apiKey}",
            ])
                ->timeout(15)
                ->get("{$this->baseUrl}/api/v1/payment/verify/{$token}");

            if ($response->successful()) {
                $data = $response->json();

                return [
                    'success' => true,
                    'paid' => ($data['statut'] ?? '') === 'paid',
                    'status' => $data['statut'] ?? 'unknown',
                    'data' => $data,
                ];
            }

            return ['success' => false, 'error' => "HTTP {$response->status()}"];
        } catch (\Exception $e) {
            return ['success' => false, 'error' => $e->getMessage()];
        }
    }
}
