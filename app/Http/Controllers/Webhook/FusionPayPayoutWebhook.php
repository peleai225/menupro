<?php

namespace App\Http\Controllers\Webhook;

use App\Http\Controllers\Controller;
use App\Models\PayoutTransaction;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class FusionPayPayoutWebhook extends Controller
{
    public function __invoke(Request $request): JsonResponse
    {
        Log::channel('payments')->info('FusionPay payout webhook received', [
            'all' => $request->all(),
        ]);

        $tokenPay = $request->input('tokenPay');
        $event = $request->input('event');

        if (!$tokenPay) {
            Log::channel('payments')->warning('FusionPay payout webhook: missing tokenPay');
            return response()->json(['status' => 'ok'], 200);
        }

        $payout = PayoutTransaction::where('gateway', 'fusionpay')
            ->where('gateway_transaction_id', $tokenPay)
            ->first();

        if (!$payout) {
            Log::channel('payments')->warning('FusionPay payout webhook: payout not found', ['tokenPay' => $tokenPay]);
            return response()->json(['status' => 'ok'], 200);
        }

        if ($event === 'payout.session.completed') {
            $payout->update(['status' => 'VAL']);
            Log::channel('payments')->info('FusionPay payout webhook: completed', [
                'payout_id' => $payout->id,
                'tokenPay' => $tokenPay,
            ]);
        } elseif ($event === 'payout.session.cancelled') {
            DB::transaction(function () use ($payout) {
                $payout->update(['status' => 'REJ']);
                $payout->restaurantWallet->increment('balance', $payout->amount);
            });
            Log::channel('payments')->warning('FusionPay payout webhook: cancelled (refunded wallet)', [
                'payout_id' => $payout->id,
                'amount' => $payout->amount,
            ]);
        }

        return response()->json(['status' => 'ok'], 200);
    }
}
