<?php

namespace App\Services;

use App\Models\Order;
use App\Models\PaymentTransaction;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class FusionPayGateway
{
    public function getConfig(string $key, $default = null)
    {
        $map = [
            'api_url' => 'fusionpay_api_url',
            'private_key' => 'fusionpay_private_key',
            'app_url' => 'app_url',
        ];
        $settingKey = $map[$key] ?? $key;
        $value = SystemSetting::get($settingKey, config('fusionpay.' . $key, $default));

        return $value ?? config('fusionpay.' . $key, $default);
    }

    public function isEnabled(): bool
    {
        return (bool) SystemSetting::get('fusionpay_enabled', false);
    }

    public function isConfigured(): bool
    {
        return !empty($this->getConfig('api_url'));
    }

    /**
     * Initie un paiement FusionPay et retourne l'URL de redirection.
     */
    public function initPayment(Order $order): array
    {
        if (!$this->isEnabled() || !$this->isConfigured()) {
            throw new \Exception('FusionPay n\'est pas activé. Configurez-le dans les paramètres super admin.');
        }

        $amount = (int) max(1, floor($order->total));
        $appUrl = rtrim($this->getConfig('app_url') ?: config('app.url'), '/');

        $phone = preg_replace('/\D/', '', $order->customer_phone ?? '');
        if (str_starts_with($phone, '225')) {
            $phone = substr($phone, 3);
        }
        $numeroSend = $phone ?: '00000000';

        $payload = [
            'totalPrice' => $amount,
            'article' => [['commande' => $amount]],
            'numeroSend' => $numeroSend,
            'nomclient' => $order->customer_name,
            'personal_Info' => [
                ['orderId' => $order->id, 'restaurantId' => $order->restaurant_id],
            ],
            'return_url' => $appUrl . '/r/' . $order->restaurant->slug . '/commande/' . $order->id . '/success',
            'webhook_url' => $appUrl . '/webhooks/fusionpay/payment',
        ];

        Log::channel('payments')->info('FusionPay initPayment request', [
            'order_id' => $order->id,
            'amount' => $amount,
        ]);

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
        ])->post($this->getConfig('api_url'), $payload);

        $data = $response->json();

        if (!$response->successful() || empty($data['statut']) || empty($data['url'])) {
            Log::channel('payments')->error('FusionPay initPayment failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'order_id' => $order->id,
            ]);
            throw new \Exception('Erreur FusionPay: ' . ($data['message'] ?? $response->body()));
        }

        $tokenPay = $data['token'] ?? null;
        if (!$tokenPay) {
            throw new \Exception('FusionPay n\'a pas retourné de token.');
        }

        $paymentUrl = $data['url'] ?? null;
        if (empty($paymentUrl) || str_contains($paymentUrl, 'undefined')) {
            $paymentUrl = rtrim(config('fusionpay.payin_page_url', 'https://www.pay.moneyfusion.net/pay'), '/') . '/' . $tokenPay;
            Log::channel('payments')->warning('FusionPay: URL invalide ou "undefined" reçue, reconstruction avec token', [
                'original_url' => $data['url'] ?? null,
                'reconstructed_url' => $paymentUrl,
            ]);
        }

        PaymentTransaction::create([
            'order_id' => $order->id,
            'restaurant_id' => $order->restaurant_id,
            'gateway' => 'fusionpay',
            'gateway_transaction_id' => $tokenPay,
            'amount' => $amount,
            'status' => 'PENDING',
            'metadata' => ['request' => $payload],
        ]);

        Log::channel('payments')->info('FusionPay initPayment success', [
            'order_id' => $order->id,
            'token' => $tokenPay,
        ]);

        return [
            'payment_url' => $paymentUrl,
            'transaction_id' => $tokenPay,
        ];
    }

    /**
     * Vérifie le statut d'une transaction auprès de FusionPay.
     */
    public function verifyPayment(string $tokenPay): array
    {
        Log::channel('payments')->info('FusionPay verifyPayment request', ['token' => $tokenPay]);

        $response = Http::get(config('fusionpay.payin_verify_url') . '/' . $tokenPay);
        $data = $response->json();

        Log::channel('payments')->info('FusionPay verifyPayment response', [
            'token' => $tokenPay,
            'statut' => $data['data']['statut'] ?? null,
        ]);

        return $data;
    }
}
