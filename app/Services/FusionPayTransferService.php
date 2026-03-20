<?php

namespace App\Services;

use App\Models\PayoutTransaction;
use App\Models\Restaurant;
use App\Models\RestaurantWallet;
use App\Models\SystemSetting;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class FusionPayTransferService
{
    protected function getConfig(string $key, $default = null)
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

    public function isPayoutEnabled(): bool
    {
        return $this->isEnabled() && !empty($this->getConfig('private_key'));
    }

    /**
     * Détermine le withdraw_mode selon le préfixe et le numéro.
     * CI: 07/05 = MTN, 01/05 = Orange, Wave. Par défaut: mtn-ci.
     */
    protected function getWithdrawMode(string $phone, string $prefix = '225'): string
    {
        $countryCode = ($prefix === '225') ? 'ci' : (($prefix === '221') ? 'sn' : 'ci');
        $phone = preg_replace('/\D/', '', $phone);

        if (str_starts_with($phone, '07')) {
            return $countryCode === 'ci' ? 'mtn-ci' : 'mtn-' . $countryCode;
        }
        if (str_starts_with($phone, '05') && strlen($phone) === 10) {
            return $countryCode === 'ci' ? 'wave-ci' : 'wave-' . $countryCode;
        }
        if (str_starts_with($phone, '01') || str_starts_with($phone, '05')) {
            return $countryCode === 'ci' ? 'orange-money-ci' : 'orange-money-' . $countryCode;
        }

        return $countryCode === 'ci' ? 'mtn-ci' : 'mtn-' . $countryCode;
    }

    public function sendPayout(RestaurantWallet $wallet, float $amount): array
    {
        $amount = (int) max(100, floor($amount));

        $phone = preg_replace('/\D/', '', $wallet->phone ?? '');
        if (empty($phone) || strlen($phone) < 8) {
            throw new \Exception('Le numéro de téléphone du wallet est invalide. Configurez-le dans les paramètres du restaurant.');
        }

        if ($wallet->balance < $amount) {
            throw new \Exception('Solde insuffisant dans le wallet.');
        }

        $prefix = $wallet->prefix ?? '225';
        if (str_starts_with($phone, '225') || str_starts_with($phone, '221')) {
            $phone = substr($phone, 3);
        }
        $countryCode = ($prefix === '225') ? 'ci' : (($prefix === '221') ? 'sn' : 'ci');
        $withdrawMode = $this->getWithdrawMode($phone, $prefix);

        $appUrl = rtrim($this->getConfig('app_url') ?: config('app.url'), '/');

        $payload = [
            'countryCode' => $countryCode,
            'phone' => $phone,
            'amount' => $amount,
            'withdraw_mode' => $withdrawMode,
            'webhook_url' => $appUrl . '/webhooks/fusionpay/payout',
        ];

        Log::channel('payments')->info('FusionPay sendPayout request', [
            'wallet_id' => $wallet->id,
            'amount' => $amount,
            'phone' => $phone,
        ]);

        $response = Http::withHeaders([
            'Content-Type' => 'application/json',
            'moneyfusion-private-key' => $this->getConfig('private_key'),
        ])->post(config('fusionpay.payout_url'), $payload);

        $data = $response->json();

        if (!$response->successful() || empty($data['statut'])) {
            Log::channel('payments')->error('FusionPay sendPayout failed', [
                'status' => $response->status(),
                'body' => $response->body(),
                'wallet_id' => $wallet->id,
            ]);
            throw new \Exception('Erreur FusionPay: ' . ($data['message'] ?? $response->body()));
        }

        $tokenPay = $data['tokenPay'] ?? Str::random(12);

        DB::transaction(function () use ($wallet, $amount, $tokenPay) {
            $wallet->decrement('balance', $amount);

            PayoutTransaction::create([
                'restaurant_id' => $wallet->restaurant_id,
                'restaurant_wallet_id' => $wallet->id,
                'gateway' => 'fusionpay',
                'gateway_transaction_id' => $tokenPay,
                'client_transaction_id' => Str::uuid()->toString(),
                'amount' => $amount,
                'status' => 'NEW',
                'phone' => $wallet->phone,
            ]);
        });

        Log::channel('payments')->info('FusionPay sendPayout success', [
            'wallet_id' => $wallet->id,
            'tokenPay' => $tokenPay,
        ]);

        return $data;
    }
}
