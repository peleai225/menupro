<?php

namespace App\Http\Controllers\SuperAdmin;

use App\Http\Controllers\Controller;
use App\Services\JekoGateway;
use Illuminate\Support\Facades\Http;
use Illuminate\View\View;

class JekoTestController extends Controller
{
    public function index(JekoGateway $gateway): View
    {
        $setting = \App\Models\SystemPaymentSetting::where('gateway', 'jeko_marketplace')->first();

        $results = [];

        if (!$setting) {
            return view('admin.jeko.test', ['results' => [], 'error' => 'jeko_marketplace non configuré dans les paramètres.']);
        }

        $apiKey   = $setting->getDecryptedApiKey();
        $apiKeyId = $setting->merchant_id;
        $baseUrl  = 'https://api.jeko.africa/partner_api';

        // Test 1 : GET /contacts (lecture simple, pas de Service Provider requis)
        try {
            $resp = Http::withHeaders([
                'X-API-KEY'    => $apiKey,
                'X-API-KEY-ID' => $apiKeyId,
                'Accept'       => 'application/json',
            ])->timeout(10)->get("{$baseUrl}/contacts");

            $results['contacts'] = [
                'label'  => 'GET /contacts',
                'status' => $resp->status(),
                'ok'     => $resp->successful(),
                'body'   => $resp->json() ?? $resp->body(),
            ];
        } catch (\Throwable $e) {
            $results['contacts'] = [
                'label'  => 'GET /contacts',
                'status' => 0,
                'ok'     => false,
                'body'   => $e->getMessage(),
            ];
        }

        // Test 2 : GET /payment_links (lecture, vérifie les droits paiement)
        try {
            $resp = Http::withHeaders([
                'X-API-KEY'    => $apiKey,
                'X-API-KEY-ID' => $apiKeyId,
                'Accept'       => 'application/json',
            ])->timeout(10)->get("{$baseUrl}/payment_links");

            $results['payment_links'] = [
                'label'  => 'GET /payment_links',
                'status' => $resp->status(),
                'ok'     => $resp->successful(),
                'body'   => $resp->json() ?? $resp->body(),
            ];
        } catch (\Throwable $e) {
            $results['payment_links'] = [
                'label'  => 'GET /payment_links',
                'status' => 0,
                'ok'     => false,
                'body'   => $e->getMessage(),
            ];
        }

        // Test 3 : POST /service_providers/business_onboarding — teste plusieurs catégories
        // Test Service Provider — deux formats de téléphone pour trouver le bon
        $phoneFormats = [
            '+2250700000000', // +225 + 10 chiffres (avec zéro initial)
            '+225700000000',  // +225 + 9 chiffres (sans zéro initial)
        ];
        foreach ($phoneFormats as $testPhone) {
            try {
                $resp = Http::withHeaders([
                    'X-API-KEY'    => $apiKey,
                    'X-API-KEY-ID' => $apiKeyId,
                    'Accept'       => 'application/json',
                ])->timeout(10)->post("{$baseUrl}/service_providers/business_onboarding", [
                    'owner'    => ['phone' => $testPhone, 'firstName' => 'Test', 'lastName' => 'MenuPro', 'sex' => 'M'],
                    'business' => ['name' => 'Test MenuPro ' . $testPhone, 'category' => 'retail'],
                ]);

                $results['phone_' . preg_replace('/\+/', '', $testPhone)] = [
                    'label'  => "POST /service_providers/... — phone: {$testPhone}",
                    'status' => $resp->status(),
                    'ok'     => $resp->successful() || $resp->status() === 409,
                    'body'   => $resp->json() ?? $resp->body(),
                ];

                if ($resp->successful() || $resp->status() === 409) {
                    break;
                }
            } catch (\Throwable $e) {
                $results['phone_' . preg_replace('/\+/', '', $testPhone)] = [
                    'label'  => "POST /service_providers/... — phone: {$testPhone}",
                    'status' => 0,
                    'ok'     => false,
                    'body'   => $e->getMessage(),
                ];
            }
        }

        return view('admin.jeko.test', compact('results'));
    }
}
