<?php

namespace App\Http\Controllers\Webhook;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Models\PaymentTransaction;
use App\Notifications\NewOrderNotification;
use App\Services\FusionPayGateway;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FusionPayPaymentWebhook extends Controller
{
    public function __invoke(Request $request, FusionPayGateway $gateway, WalletService $walletService): JsonResponse
    {
        Log::channel('payments')->info('FusionPay payment webhook received', [
            'all' => $request->all(),
        ]);

        $tokenPay = $request->input('tokenPay');
        $event = $request->input('event');

        if (!$tokenPay) {
            Log::channel('payments')->warning('FusionPay webhook: missing tokenPay');
            return response()->json(['status' => 'ok'], 200);
        }

        $paymentTransaction = PaymentTransaction::where('gateway', 'fusionpay')
            ->where('gateway_transaction_id', $tokenPay)
            ->first();

        if (!$paymentTransaction) {
            Log::channel('payments')->warning('FusionPay webhook: transaction not found', ['tokenPay' => $tokenPay]);
            return response()->json(['status' => 'ok'], 200);
        }

        if ($paymentTransaction->status === PaymentStatus::COMPLETED->value) {
            Log::channel('payments')->info('FusionPay webhook: already processed (idempotence)', ['tokenPay' => $tokenPay]);
            return response()->json(['status' => 'ok'], 200);
        }

        if ($event === 'payin.session.completed') {
            $verify = $gateway->verifyPayment($tokenPay);
            $data = $verify['data'] ?? [];
            $status = $data['statut'] ?? null;

            if ($status === 'paid') {
                DB::transaction(function () use ($paymentTransaction, $data, $walletService) {
                    $paymentTransaction->update([
                        'status'   => PaymentStatus::COMPLETED->value,
                        'metadata' => array_merge($paymentTransaction->metadata ?? [], ['webhook' => $data]),
                    ]);

                    $order = $paymentTransaction->order;
                    $order->markAsPaid([
                        'reference' => $paymentTransaction->gateway_transaction_id,
                        'method'    => 'fusionpay',
                        'metadata'  => $data,
                    ]);

                    // Créditer le wallet avec déduction de commission (cohérent avec Wave/FusionPay)
                    $walletService->creditWallet($paymentTransaction->restaurant_id, $paymentTransaction->id);

                    $order->restaurant->users()
                        ->whereIn('role', [\App\Enums\UserRole::RESTAURANT_ADMIN, \App\Enums\UserRole::EMPLOYEE])
                        ->each(fn ($user) => $user->notify(new NewOrderNotification($order)));
                });

                Log::channel('payments')->info('FusionPay webhook: payment completed', [
                    'order_id' => $paymentTransaction->order_id,
                    'tokenPay' => $tokenPay,
                ]);
            }
        } elseif ($event === 'payin.session.cancelled') {
            $paymentTransaction->update(['status' => PaymentStatus::FAILED->value]);
            $paymentTransaction->order->update([
                'payment_status' => \App\Enums\PaymentStatus::FAILED,
            ]);
            Log::channel('payments')->info('FusionPay webhook: payment cancelled', [
                'order_id' => $paymentTransaction->order_id,
                'tokenPay' => $tokenPay,
            ]);
        }

        return response()->json(['status' => 'ok'], 200);
    }
}
