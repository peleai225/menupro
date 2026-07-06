<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\PayoutTransaction;
use App\Models\RestaurantWallet;
use App\Services\WalletService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PayoutController extends Controller
{
    public function __construct(
        protected WalletService $walletService,
    ) {
    }

    public function requestPayout(Request $request): JsonResponse
    {
        return response()->json([
            'error' => 'Le service de retrait n\'est pas encore disponible. Contactez le support.',
        ], 503);
    }

    public function getPayoutStatus(Request $request, string $payoutId): JsonResponse
    {
        $payout = PayoutTransaction::query()->findOrFail($payoutId);

        $user = $request->user();
        if (!$user->isSuperAdmin() && (int) $payout->restaurant_id !== (int) $user->restaurant_id) {
            abort(403, 'Accès non autorisé.');
        }

        return response()->json([
            'id' => $payout->id,
            'status' => $payout->status,
            'payout_error' => $payout->payout_error,
        ]);
    }

    public function getWalletBalance(int $restaurantId): JsonResponse
    {
        $user = request()->user();
        if (!$user || (int) $user->restaurant_id !== (int) $restaurantId) {
            return response()->json(['error' => 'Accès refusé pour ce restaurant.'], 403);
        }

        $balance = $this->walletService->getBalance($restaurantId);

        return response()->json($balance);
    }
}
