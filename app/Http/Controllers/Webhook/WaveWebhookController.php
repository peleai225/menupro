<?php

namespace App\Http\Controllers\Webhook;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\PaymentTransaction;
use App\Services\WalletService;
use App\Services\WaveSignatureService;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class WaveWebhookController extends Controller
{
    public function __construct(
        protected WaveSignatureService $signatureService,
        protected WalletService $walletService,
    ) {
    }

    /**
     * Webhook Wave pour la complétion d'une session Checkout.
     *
     * Attendu : un événement de type "checkout.session.completed"
     * contenant au minimum un champ client_reference utilisable
     * pour retrouver la PaymentTransaction.
     */
    public function handleCheckout(Request $request): Response
    {
        $payload = $request->getContent();
        $signature = $request->header('Wave-Signature');

        if (!$this->signatureService->verifyWebhookSignature($payload, $signature)) {
            Log::warning('Wave webhook signature invalid', ['payload' => $payload]);

            return response('Invalid signature', 401);
        }

        $data = json_decode($payload, true);
        if (!is_array($data)) {
            Log::warning('Wave webhook invalid JSON payload', ['payload' => $payload]);

            return response('Invalid payload', 400);
        }

        $type = $data['type'] ?? null;
        if ($type !== 'checkout.session.completed') {
            // On acknowledge quand même pour éviter des retries infinis,
            // même si l’événement ne nous intéresse pas.
            return response('Ignored', 200);
        }

        $object = $data['data']['object'] ?? [];
        $clientReference = $object['client_reference'] ?? null;
        $wavePaymentId = $object['payment_id'] ?? null;
        $waveCheckoutId = $object['id'] ?? null;

        if (!$clientReference) {
            Log::warning('Wave webhook missing client_reference', ['event' => $data]);

            return response('OK', 200);
        }

        /** @var PaymentTransaction|null $payment */
        $payment = PaymentTransaction::query()
            ->where('client_reference', $clientReference)
            ->where('status', PaymentStatus::PENDING->value)
            ->first();

        if (!$payment) {
            Log::info('Wave webhook payment not found or already processed', [
                'client_reference' => $clientReference,
            ]);

            return response('OK', 200);
        }

        $payment->status = PaymentStatus::COMPLETED->value;
        if ($wavePaymentId) {
            $payment->wave_payment_id = $wavePaymentId;
        }
        if ($waveCheckoutId) {
            $payment->wave_checkout_id = $waveCheckoutId;
        }
        $payment->metadata = array_merge($payment->metadata ?? [], ['wave_event' => $data]);
        $payment->save();

        // Créditer le wallet virtuel MenuPro pour ce restaurant
        $this->walletService->creditWallet($payment->restaurant_id, $payment->id);

        return response('OK', 200);
    }
}

