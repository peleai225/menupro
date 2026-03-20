<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Services\MenuProHubService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class MenuProHubWebhookController extends Controller
{
    public function __construct(
        protected MenuProHubService $hubService
    ) {}

    /**
     * Verify payment from SMS Gateway webhook.
     * Expected payload: { "from": "+2250701234567", "text": "Vous avez reçu 5000 FCFA...", "restaurant_id": 1 (optional) }
     */
    public function verifyPayment(Request $request): JsonResponse
    {
        $secret = \App\Models\SystemSetting::get('menupo_hub_webhook_secret', config('services.menupo_hub.webhook_secret', ''));
        if ($secret) {
            $signature = $request->header('X-Webhook-Signature') ?? $request->header('X-Signature');
            $payload = $request->getContent();
            $expected = hash_hmac('sha256', $payload, $secret);
            if (!$signature || !hash_equals($expected, $signature)) {
                Log::channel('payments')->warning('MenuPro Hub webhook: invalid signature');
                return response()->json(['error' => 'Unauthorized'], 401);
            }
        }

        $from = $request->input('from') ?? $request->input('sender') ?? $request->input('phone');
        $text = $request->input('text') ?? $request->input('body') ?? $request->input('message') ?? '';
        $restaurantId = $request->input('restaurant_id');

        if (empty($text)) {
            Log::channel('payments')->warning('MenuPro Hub webhook: missing text');
            return response()->json(['error' => 'Missing text'], 400);
        }

        $parsed = $this->hubService->parsePaymentSms($text, $from);
        if (!$parsed) {
            Log::channel('payments')->info('MenuPro Hub webhook: could not parse SMS', ['text' => substr($text, 0, 100)]);
            return response()->json(['error' => 'Could not parse payment from SMS'], 400);
        }

        $order = $this->hubService->findMatchingOrder(
            $parsed['amount'],
            $parsed['sender_phone'] ?: null,
            $restaurantId ? (int) $restaurantId : null
        );

        if (!$order) {
            Log::channel('payments')->info('MenuPro Hub webhook: no matching order', [
                'amount' => $parsed['amount'],
                'sender' => $parsed['sender_phone'],
            ]);
            return response()->json(['error' => 'No matching order found'], 404);
        }

        // Detect payment method from order (restaurant config) or SMS content
        $paymentMethod = $this->detectPaymentMethod($order, $text);

        $this->hubService->verifyAndMarkPaid($order, $paymentMethod, $from);

        return response()->json([
            'success' => true,
            'order_id' => $order->id,
            'reference' => $order->reference,
            'amount' => $parsed['amount'],
        ]);
    }

    private function detectPaymentMethod($order, string $text): string
    {
        $text = strtolower($text);
        if (str_contains($text, 'wave') || str_contains($text, 'wave')) {
            return 'wave';
        }
        if (str_contains($text, 'orange') || str_contains($text, 'om') || str_contains($text, 'orange money')) {
            return 'orange';
        }
        if (str_contains($text, 'mtn') || str_contains($text, 'momo')) {
            return 'mtn';
        }
        if (str_contains($text, 'moov') || str_contains($text, 'moov money')) {
            return 'moov';
        }

        return $order->payment_method ?? 'wave';
    }
}
