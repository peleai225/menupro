<?php
// app/Http/Controllers/Api/V1/Driver/CashController.php
namespace App\Http\Controllers\Api\V1\Driver;

use App\Http\Controllers\Controller;
use App\Models\DriverCashDebt;
use App\Models\DriverCashRemittance;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CashController extends Controller
{
    /**
     * Solde des dettes cash du livreur envers les restaurants.
     */
    public function balance(Request $request): JsonResponse
    {
        $driver = $request->user()->deliveryDriver;
        $debts  = DriverCashDebt::pendingForDriver($driver->id);

        return response()->json([
            'total_owed_xof' => DriverCashDebt::totalPendingForDriver($driver->id),
            'debts' => $debts->map(fn($d) => [
                'id'              => $d->id,
                'restaurant_name' => $d->restaurant->name ?? '—',
                'order_ref'       => $d->order->reference ?? '—',
                'amount_xof'      => $d->amount_xof,
                'created_at'      => $d->created_at?->toDateTimeString(),
            ])->values(),
        ]);
    }

    /**
     * Le livreur déclare avoir reversé l'argent au restaurant.
     */
    public function storeRemittance(Request $request): JsonResponse
    {
        $data = $request->validate([
            'debt_id'        => 'required|integer|exists:driver_cash_debts,id',
            'amount_xof'     => 'required|integer|min:1',
            'method'         => 'required|in:wave,orange_money,mtn_money,moov_money,cash',
            'wave_reference' => 'nullable|string|max:100',
            'note'           => 'nullable|string|max:500',
        ]);

        $driver = $request->user()->deliveryDriver;

        $debt = DriverCashDebt::where('id', $data['debt_id'])
            ->where('driver_id', $driver->id)
            ->where('status', 'pending')
            ->firstOrFail();

        $remittance = DriverCashRemittance::create([
            'driver_id'      => $driver->id,
            'restaurant_id'  => $debt->restaurant_id,
            'debt_id'        => $debt->id,
            'amount_xof'     => (int) $data['amount_xof'],
            'method'         => $data['method'],
            'wave_reference' => $data['wave_reference'] ?? null,
            'status'         => 'pending',
            'note'           => $data['note'] ?? null,
        ]);

        // Notifier le restaurant (si notifications push configurées)
        try {
            $restaurant = $debt->restaurant;
            if ($restaurant) {
                \Illuminate\Support\Facades\Log::channel('payments')->info('Driver declared cash remittance', [
                    'driver_id'      => $driver->id,
                    'restaurant_id'  => $debt->restaurant_id,
                    'amount_xof'     => $remittance->amount_xof,
                    'method'         => $remittance->method,
                    'wave_reference' => $remittance->wave_reference,
                ]);
            }
        } catch (\Throwable $e) {
            // Notification non critique
        }

        return response()->json([
            'message'      => 'Reversement déclaré. En attente de confirmation du restaurant.',
            'remittance_id' => $remittance->id,
        ], 201);
    }

    /**
     * Historique des reversements déclarés.
     */
    public function remittances(Request $request): JsonResponse
    {
        $driver      = $request->user()->deliveryDriver;
        $remittances = DriverCashRemittance::where('driver_id', $driver->id)
            ->with(['restaurant:id,name', 'debt:id,amount_xof'])
            ->latest()
            ->paginate(20);

        return response()->json($remittances);
    }
}
