<?php

namespace App\Http\Controllers\Api\V1\Driver;

use App\Http\Controllers\Controller;
use App\Models\DriverEarning;
use App\Services\WaveGateway;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class EarningsController extends Controller
{
    public function __construct(private WaveGateway $wave) {}

    /**
     * Gains du jour et de la semaine.
     */
    public function summary(Request $request): JsonResponse
    {
        $driver = $request->user()->deliveryDriver;

        $today = DriverEarning::where('driver_id', $driver->id)
            ->whereDate('created_at', today())
            ->selectRaw('COUNT(*) as deliveries, SUM(net_amount) as earnings')
            ->first();

        $week = DriverEarning::where('driver_id', $driver->id)
            ->whereBetween('created_at', [now()->startOfWeek(), now()->endOfWeek()])
            ->selectRaw('COUNT(*) as deliveries, SUM(net_amount) as earnings')
            ->first();

        $available = DriverEarning::where('driver_id', $driver->id)
            ->where('status', 'available')
            ->sum('net_amount');

        return response()->json([
            'available_balance' => (int) $available,
            'today' => [
                'deliveries' => (int) $today->deliveries,
                'earnings'   => (int) $today->earnings,
            ],
            'this_week' => [
                'deliveries' => (int) $week->deliveries,
                'earnings'   => (int) $week->earnings,
            ],
            'total_lifetime' => $driver->total_earnings_xof,
        ]);
    }

    /**
     * Historique paginé des gains.
     */
    public function history(Request $request): JsonResponse
    {
        $driver = $request->user()->deliveryDriver;

        $earnings = DriverEarning::where('driver_id', $driver->id)
            ->with('order:id,reference,created_at')
            ->latest()
            ->paginate(20);

        return response()->json([
            'data' => $earnings->map(fn($e) => [
                'id'           => $e->id,
                'order_ref'    => $e->order->reference,
                'gross_amount' => $e->gross_amount,
                'platform_cut' => $e->platform_cut,
                'net_amount'   => $e->net_amount,
                'status'       => $e->status,
                'paid_at'      => $e->paid_at,
                'created_at'   => $e->created_at,
            ]),
            'meta' => [
                'current_page' => $earnings->currentPage(),
                'last_page'    => $earnings->lastPage(),
                'total'        => $earnings->total(),
            ],
        ]);
    }

    /**
     * Demander un virement Mobile Money (Wave).
     */
    public function requestPayout(Request $request): JsonResponse
    {
        $data = $request->validate([
            'amount'         => 'required|integer|min:500',
            'mobile'         => 'required|string|max:20',
            'payment_method' => 'required|in:wave,orange_money,mtn_money',
        ]);

        $driver    = $request->user()->deliveryDriver;
        $available = (int) DriverEarning::where('driver_id', $driver->id)
            ->where('status', 'available')
            ->sum('net_amount');

        if ($data['amount'] > $available) {
            return response()->json([
                'message'   => 'Solde insuffisant.',
                'available' => $available,
            ], 422);
        }

        if ($data['payment_method'] !== 'wave') {
            return response()->json([
                'message' => 'Seul Wave est disponible pour le moment.',
            ], 422);
        }

        try {
            DB::transaction(function () use ($driver, $data) {
                // Marquer les gains comme paid
                DriverEarning::where('driver_id', $driver->id)
                    ->where('status', 'available')
                    ->orderBy('created_at')
                    ->limit($this->countEarningsUpTo($driver->id, $data['amount']))
                    ->update([
                        'status'         => 'paid',
                        'paid_at'        => now(),
                        'payment_method' => $data['payment_method'],
                    ]);
            });

            // Initier le virement Wave
            $payout = $this->wave->createPayout(
                amount: $data['amount'] / 100,
                mobile: $data['mobile'],
                recipientName: $driver->name,
                reference: 'DRIVER-' . $driver->id . '-' . now()->format('YmdHis'),
            );

            Log::info('Driver payout requested', [
                'driver_id' => $driver->id,
                'amount'    => $data['amount'],
            ]);

            return response()->json([
                'message'   => 'Virement en cours. Vous recevrez une confirmation Wave.',
                'amount'    => $data['amount'],
                'reference' => $payout['id'] ?? null,
            ]);
        } catch (\Throwable $e) {
            Log::error('Driver payout failed', ['driver_id' => $driver->id, 'error' => $e->getMessage()]);
            return response()->json(['message' => 'Le virement a échoué. Réessayez.'], 500);
        }
    }

    private function countEarningsUpTo(int $driverId, int $amount): int
    {
        $count = 0;
        $sum   = 0;

        DriverEarning::where('driver_id', $driverId)
            ->where('status', 'available')
            ->orderBy('created_at')
            ->each(function ($e) use (&$count, &$sum, $amount) {
                if ($sum >= $amount) return false;
                $sum += $e->net_amount;
                $count++;
            });

        return $count;
    }
}
